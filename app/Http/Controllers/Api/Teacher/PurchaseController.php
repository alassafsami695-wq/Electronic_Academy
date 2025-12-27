<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Http\Resources\CourseResource;
use Illuminate\Support\Facades\DB;
use App\Notifications\CoursePurchasedNotification;
use Exception;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->middleware('auth:sanctum');
        $this->walletService = $walletService;
    }

    /**
     * شراء كورس أو أكثر (سلة المشتريات)
     */
    public function purchaseCourses(Request $request)
    {
        $request->validate([
            'course_ids'   => 'required|array|min:1',
            'course_ids.*' => 'exists:courses,id'
        ]);

        $user = auth()->user();
        // جلب الكورسات المطلوبة
        $courses = Course::whereIn('id', $request->course_ids)->get();
        
        $totalPrice = 0;
        $coursesToBuy = [];

        // التحقق الدقيق من الكورسات غير المشترك بها
        foreach ($courses as $course) {
            // فحص جدول الربط مباشرة لضمان عدم وجود اشتراك قديم
            $isAlreadyEnrolled = DB::table('course_user')
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->exists();

            if (!$isAlreadyEnrolled) {
                $totalPrice += (float) $course->price;
                $coursesToBuy[] = $course;
            } else {
                Log::info("User {$user->id} is already enrolled in course {$course->id}");
            }
        }

        if (empty($coursesToBuy)) {
            return response()->json([
                'status' => 'info',
                'message' => 'أنت مشترك بالفعل في كل الكورسات المختارة، يمكنك العثور عليها في قائمة مشترياتي'
            ], 200); // تغيير الكود لـ 200 ليكون منطقياً
        }

        // تحقق من الرصيد
        if (!$user->wallet || $user->wallet->balance < $totalPrice) {
            return response()->json([
                'message' => 'رصيدك غير كافٍ. المطلوب: ' . $totalPrice . '، رصيدك الحالي: ' . ($user->wallet->balance ?? 0)
            ], 400);
        }

        try {
            DB::transaction(function () use ($user, $coursesToBuy) {
                foreach ($coursesToBuy as $course) {
                    
                    // خصم ثمن الكورس
                    $this->walletService->debit($user, (float) $course->price, "شراء الكورس: " . $course->title);

                    // توزيع الأرباح (80% مدرس، 20% أدمن)
                    $teacherShare = $course->price * 0.80;
                    $adminShare   = $course->price * 0.20;

                    if ($course->teacher && $course->teacher->wallet) {
                        $course->teacher->wallet->increment('balance', $teacherShare);
                    }

                    $superAdmin = User::where('is_super_admin', true)->first();
                    if ($superAdmin && $superAdmin->wallet) {
                        $superAdmin->wallet->increment('balance', $adminShare);
                    }

                    // تفعيل الاشتراك
                    $user->enrolledCourses()->attach($course->id);
                    $course->increment('sales_count');

                    // إرسال الإشعارات
                    try {
                        $user->notify(new CoursePurchasedNotification($course, $user));
                    } catch (Exception $e) {
                        Log::error("Notification Error: " . $e->getMessage());
                    }
                }
            });

            return response()->json(['message' => 'تم شراء السلة بنجاح وإضافتها إلى حسابك'], 200);

        } catch (Exception $e) {
            Log::error("Purchase Transaction Failed: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء المعالجة: ' . $e->getMessage()], 400);
        }
    }
}