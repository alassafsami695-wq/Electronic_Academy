<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Http\Resources\CourseResource;

class PurchaseController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->middleware('auth:sanctum');
        $this->walletService = $walletService;
    }

    // شراء كورس من قبل المستخدم الحالي
    public function purchaseCourse(Request $request, Course $course)
    {
        $user = auth()->user();

        // تحقق إذا كان الطالب مشترك مسبقًا
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'أنت مشترك بالفعل في هذا الكورس'], 400);
        }

        try {
            // خصم من المحفظة
            $this->walletService->debit(
                $user,
                $course->price,
                "شراء الكورس: " . $course->title,
                $course->id
            );

            // ربط الطالب بالكورس (مع حماية من التكرار)
            if (!$user->enrolledCourses()->where('course_id', $course->id)->exists()) {
                $user->enrolledCourses()->attach($course->id);
            }

            // تحميل العلاقات المطلوبة
            $course->load(['teacher', 'path', 'lessons']);

            // ✅ رجع الكورس مع تفاصيله الكاملة
            return response()->json([
                'message' => 'تم الاشتراك بنجاح',
                'course'  => new CourseResource($course)
            ], 200);

        } catch (\Exception $e) {
            // ✅ رسالة واضحة عند الفشل
            return response()->json([
                'message' => 'فشل الاشتراك: ' . $e->getMessage()
            ], 400);
        }
    }
}
