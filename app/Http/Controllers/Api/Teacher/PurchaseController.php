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

    // -------------------------شراء كورس معين من قبل المستخدم الحالي------------------
     
    public function purchaseCourse(Request $request, Course $course)
    {
        $user = auth()->user();

        //------------------التحقق إذا كان المستخدم مسجل مسبقًا في الكورس-----------------
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'User already owns this course'], 400);
        }

        try {
            // -------------------------خصم سعر الكورس من المحفظة----------------------
            $this->walletService->debit(
                $user,
                $course->price,
                "Course Purchase: " . $course->title,
                $course->id
            );

            //--------------------------------- ربط المستخدم بالكورس (تسجيله)-----------------------
            $user->enrolledCourses()->attach($course->id);

            return response()->json(['message' => 'Course purchased and enrolled successfully!'], 200);
        } catch (\Exception $e) {
            //-------------------------- في حال فشل عملية الخصم (مثل رصيد غير كافي)------------------------
            return response()->json(['message' => $e->getMessage()], 402);
        }
    }
}
