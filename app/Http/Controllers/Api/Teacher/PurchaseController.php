<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use App\Notifications\CoursePurchasedNotification;
use Exception;

class PurchaseController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->middleware('auth:sanctum');
        $this->walletService = $walletService;
    }

    /**
     * شراء كورس واحد
     */
    public function purchaseSingleCourse(Course $course)
    {
        $user = auth()->user();

        // 1. فحص الاشتراك المسبق
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'أنت مشترك بالفعل في كورس: ' . $course->title], 400);
        }

        // 2. فحص الرصيد
        if (!$user->wallet || $user->wallet->balance < $course->price) {
            return response()->json(['message' => 'رصيد محفظتك غير كافٍ لشراء: ' . $course->title], 400);
        }

        try {
            return DB::transaction(function () use ($user, $course) {
                // تنفيذ العملية المالية
                $this->executeTransaction($user, $course);
                return response()->json(['message' => 'تم الاشتراك بنجاح في: ' . $course->title], 200);
            });
        } catch (Exception $e) {
            return response()->json(['message' => 'حدث خطأ أثناء المعالجة: ' . $e->getMessage()], 500);
        }
    }

    /**
     * شراء مجموعة كورسات (السلة)
     */
    public function purchaseCourses(Request $request)
    {
        $request->validate([
            'course_ids'   => 'required|array|min:1',
            'course_ids.*' => 'exists:courses,id'
        ]);

        $user = auth()->user();
        $courses = Course::whereIn('id', $request->course_ids)->get();
        $totalPrice = $courses->sum('price');

        // 1. فحص الرصيد الإجمالي قبل البدء
        if (!$user->wallet || $user->wallet->balance < $totalPrice) {
            return response()->json(['message' => 'رصيد المحفظة لا يكفي لشراء جميع الكورسات في السلة'], 400);
        }

        try {
            DB::transaction(function () use ($user, $courses) {
                foreach ($courses as $course) {
                    // تخطي الكورس إذا كان مشتركاً فيه مسبقاً
                    if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
                        continue;
                    }
                    $this->executeTransaction($user, $course);
                }
            });

            return response()->json(['message' => 'تم شراء الكورسات المحددة بنجاح'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'فشلت العملية: ' . $e->getMessage()], 500);
        }
    }

    /**
     * دالة داخلية لتنفيذ منطق الخصم والإيداع (Private Helper)
     */
    private function executeTransaction($user, $course)
    {
        // 1. خصم من الطالب
        $this->walletService->debit($user, (float) $course->price, "شراء الكورس: " . $course->title);

        // 2. حساب الأرباح
        $teacherShare = $course->price * 0.80;
        $adminShare   = $course->price * 0.20;

        // 3. إيداع للأستاذ
        if ($course->teacher && $course->teacher->wallet) {
            $course->teacher->wallet->increment('balance', $teacherShare);
        }

        // 4. إيداع للمنصة (Super Admin)
        $superAdmin = User::where('is_super_admin', true)->first();
        if ($superAdmin && $superAdmin->wallet) {
            $superAdmin->wallet->increment('balance', $adminShare);
        }

        // 5. ربط الطالب بالكورس وزيادة عداد المبيعات
        $user->enrolledCourses()->attach($course->id);
        $course->increment('sales_count');

        // 6. إرسال الإشعار
        $course->teacher->notify(new CoursePurchasedNotification($course, $user));
    }
}