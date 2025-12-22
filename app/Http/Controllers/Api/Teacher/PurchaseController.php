<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Http\Resources\CourseResource;
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

        // تحقق إذا كان الطالب مشترك مسبقًا
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'أنت مشترك بالفعل في هذا الكورس'], 400);
        }

        try {
            // تنفيذ عملية الخصم من المحفظة
            $this->walletService->debit(
                $user,
                (float) $course->price,
                "شراء الكورس: " . $course->title
            );

            // ربط الطالب بالكورس (مع حماية إضافية)
            $user->enrolledCourses()->syncWithoutDetaching([$course->id]);

            // تحميل العلاقات المطلوبة
            $course->load(['teacher', 'path', 'lessons']);

            return response()->json([
                'message' => 'تم الاشتراك بنجاح',
                'course'  => new CourseResource($course)
            ], 200);

        } catch (Exception $e) {
            // إرجاع رسالة الخطأ سواء كانت "رصيد غير كافٍ" أو أي خطأ آخر
            return response()->json([
                'message' => 'فشل الاشتراك: ' . $e->getMessage()
            ], 400);
        }
    }
}