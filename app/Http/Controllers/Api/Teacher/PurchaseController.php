<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Services\WalletService;

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
            return response()->json(['message' => 'User already owns this course'], 400);
        }

        try {
            // تأكد من توقيع الدالة debit في WalletService — هنا نفترض (user, amount, description, reference_id)
            $this->walletService->debit(
                $user,
                $course->price,
                "Course Purchase: " . $course->title,
                $course->id
            );

            $user->enrolledCourses()->attach($course->id);

            return response()->json(['message' => 'Course purchased and enrolled successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 402);
        }
    }
}
