<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Teacher\CourseController;
use App\Http\Controllers\Api\Teacher\LessonController;
use Illuminate\Support\Facades\Mail;

// Login
Route::post('/login', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // إذا لم يتم التحقق من الإيميل
    if (!$user->is_email_verified) {

        $code = rand(100000, 999999);

        $user->email_verification_code = $code;
        $user->save();

        Mail::to($user->email)->send(new \App\Mail\VerificationCodeMail($user));

        return response()->json([
            'message' => 'Verification code sent to your email.',
            'requires_verification' => true
        ]);
    }

    // إذا كان الإيميل مفعلاً → تسجيل الدخول عادي
    $token = $user->createToken('API Token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer'
    ]);
});


// Verify Email Route
Route::post('/verify-email', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'code'  => 'required|numeric',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    if ($user->email_verification_code != $request->code) {
        return response()->json(['message' => 'Invalid verification code'], 400);
    }

    $user->is_email_verified = true;
    $user->email_verification_code = null;
    $user->save();

    return response()->json(['message' => 'Email verified successfully']);
});


// Admin Routes
Route::middleware(['auth:sanctum','is.admin'])->prefix('admin')->group(function () {
    Route::post('users/teacher', [UserController::class, 'storeTeacher']);
    Route::post('users/admin', [UserController::class, 'storeAdmin']);
});

// Teacher Routes
Route::middleware(['auth:sanctum','is.teacher'])->prefix('teacher')->group(function () {
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::post('courses', [CourseController::class, 'store']);
    Route::put('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);

    Route::post('courses/{course}/lessons', [LessonController::class, 'store']);
    Route::put('courses/{course}/lessons/{lesson}', [LessonController::class, 'update']);
    Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy']);
});
