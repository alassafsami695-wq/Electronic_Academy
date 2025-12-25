<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Http\Resources\CourseResource;
use Illuminate\Support\Facades\DB;
use App\Notifications\CoursePurchasedNotification; // استدعاء الإشعار الجديد
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

        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'أنت مشترك بالفعل في هذا الكورس'], 400);
        }

        if (!$user->wallet || $user->wallet->balance < $course->price) {
            return response()->json(['message' => 'فشل الاشتراك: رصيد محفظتك غير كافٍ'], 400);
        }

        try {
            DB::transaction(function () use ($user, $course) {
                
                $this->walletService->debit(
                    $user,
                    (float) $course->price,
                    "شراء الكورس: " . $course->title
                );

                $teacherShare = $course->price * 0.80;
                $adminShare   = $course->price * 0.20;

                if ($course->teacher && $course->teacher->wallet) {
                    $course->teacher->wallet->increment('balance', $teacherShare);
                }

                $superAdmin = User::where('is_super_admin', true)->first();
                if ($superAdmin && $superAdmin->wallet) {
                    $superAdmin->wallet->increment('balance', $adminShare);
                }

                $user->enrolledCourses()->syncWithoutDetaching([$course->id]);
            });

            // ---- إرسال الإشعارات ----
            try {
                // 1. إشعار للطالب (تأكيد الشراء)
                $user->notify(new CoursePurchasedNotification($course, $user));

                // 2. إشعار للمدرس (تم شراء كورس خاص بك) - هذا ما يبحث عنه الاختبار
                if ($course->teacher) {
                    $course->teacher->notify(new CoursePurchasedNotification($course, $user));
                }
            } catch (Exception $notifyError) {
                \Log::error("فشل إرسال الإشعارات: " . $notifyError->getMessage());
            }
            // -----------------------

            $course->load(['teacher', 'path', 'lessons']);

            return response()->json([
                'message' => 'تم الاشتراك بنجاح، وتوزيع الأرباح وإرسال الإشعارات',
                'course'  => new CourseResource($course)
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'فشل الاشتراك: ' . $e->getMessage()
            ], 400);
        }
    }
}