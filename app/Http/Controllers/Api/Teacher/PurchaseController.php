<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Http\Resources\CourseResource;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->middleware('auth:sanctum');
        $this->walletService = $walletService;
    }

    public function purchaseCourse(Request $request, Course $course)
    {
        $user = auth()->user();

        // 1. تحقق إذا كان الطالب مشترك مسبقًا
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'أنت مشترك بالفعل في هذا الكورس'], 400);
        }

        // 2. التحقق من وجود رصيد كافٍ قبل بدء العملية
        if (!$user->wallet || $user->wallet->balance < $course->price) {
            return response()->json(['message' => 'فشل الاشتراك: رصيد محفظتك غير كافٍ'], 400);
        }

        try {
            // استخدام Transaction لضمان تنفيذ العملية المالية بالكامل أو إلغائها بالكامل
            $result = DB::transaction(function () use ($user, $course) {
                
                // أ. خصم كامل ثمن الكورس من محفظة الطالب
                $this->walletService->debit(
                    $user,
                    (float) $course->price,
                    "شراء الكورس: " . $course->title
                );

                // ب. حساب تقسيم الأرباح (80% للأستاذ، 20% للسوبر أدمن/المنصة)
                $teacherShare = $course->price * 0.80;
                $adminShare   = $course->price * 0.20;

                // ج. تحويل حصة الأستاذ (صاحب الكورس)
                if ($course->teacher && $course->teacher->wallet) {
                    $course->teacher->wallet->increment('balance', $teacherShare);
                }

                // د. تحويل حصة السوبر أدمن (المنصة)
                $superAdmin = User::where('is_super_admin', true)->first();
                if ($superAdmin && $superAdmin->wallet) {
                    $superAdmin->wallet->increment('balance', $adminShare);
                }

                // هـ. ربط الطالب بالكورس
                $user->enrolledCourses()->syncWithoutDetaching([$course->id]);

                return true;
            });

            // تحميل العلاقات المطلوبة للرد
            $course->load(['teacher', 'path', 'lessons']);

            return response()->json([
                'message' => 'تم الاشتراك بنجاح وتوزيع الأرباح',
                'course'  => new CourseResource($course)
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'فشل الاشتراك: ' . $e->getMessage()
            ], 400);
        }
    }
}