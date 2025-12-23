<?php

use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TeacherProfileController;
use App\Http\Controllers\Api\FeatureController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PathController;
use App\Http\Controllers\Api\JobListingController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Teacher\CourseController;
use App\Http\Controllers\Api\Teacher\LessonController;
use App\Http\Controllers\Api\Teacher\CommentController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\LessonQuestionController;
use App\Http\Controllers\Api\Teacher\PurchaseController;
use App\Http\Controllers\PaymentController; 
use App\Http\Controllers\ContactSettingController; 

/*
|--------------------------------------------------------------------------
| المسارات العامة (Public Routes)
|--------------------------------------------------------------------------
*/

Route::get('ads', [AdvertisementController::class, 'index']); 
Route::get('features', [FeatureController::class, 'index']); 
Route::get('features/{id}', [FeatureController::class, 'show']);
Route::get('contact-settings', [ContactSettingController::class, 'index']);
Route::get('/teachers/{id}', [TeacherProfileController::class, 'publicShow']);

// 🔍 مسارات الكورسات العامة
Route::get('/paths', [PathController::class, 'index']);
Route::get('/paths/{path}', [PathController::class, 'show']);
Route::get('/paths/{path}/courses', [PathController::class, 'course']);
Route::get('/courses', [CourseController::class, 'index']); 
Route::get('/courses/best-selling', [CourseController::class, 'bestSelling']);
Route::get('/courses/{course}', [CourseController::class, 'publicShow']);

// ------------------------- AUTH -------------------------
Route::post('/register/student', [AuthController::class, 'registerStudent']); 
Route::post('/register/teacher', [AuthController::class, 'registerTeacher']); 
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

Route::post('/payments/callback', [PaymentController::class, 'handleCallback']); 

// ------------------------- JOBS (Public) -------------------------
Route::prefix('job-listings')->group(function () {
    Route::get('/', [JobListingController::class, 'index'])->name('job-listings.index');
    Route::get('/{job}', [JobListingController::class, 'show'])->name('job-listings.show');
});

/*
|--------------------------------------------------------------------------
| المسارات المحمية (Protected Routes)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard/stats', [DashboardController::class, 'index']);

    // ------------------------- ADMIN ROUTES -------------------------
    Route::middleware('is.Admin')->prefix('admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);

        Route::post('withdraw', [DashboardController::class, 'withdrawRevenue']);
        
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::post('admins', [UserController::class, 'storeAdmin']);
        Route::delete('users/{user}', [UserController::class, 'destroyUser']); 

        Route::post('paths', [UserController::class, 'storePath']);
        Route::post('paths/{path}/update', [UserController::class, 'updatePath']);
        Route::delete('paths/{path}', [UserController::class, 'destroyPath']);

        Route::post('ads', [AdvertisementController::class, 'store']);
        Route::post('ads/{id}/toggle-status', [AdvertisementController::class, 'toggleStatus']); 
        Route::delete('ads/{id}', [AdvertisementController::class, 'destroy']);
        
        Route::delete('comments/{comment}', [UserController::class, 'destroyComment']);
        Route::post('features', [FeatureController::class, 'store']); 
        Route::post('features/{feature}/update', [FeatureController::class, 'update']); 
        Route::delete('features/{feature}', [FeatureController::class, 'destroy']); 
        Route::post('contact-settings', [ContactSettingController::class, 'update']);

        // إدارة الوظائف (بإضافة الأسماء لضمان عمل الـ Resource)
        Route::post('job-listings', [JobListingController::class, 'store'])->name('admin.job-listings.store');
        Route::post('job-listings/{id}', [JobListingController::class, 'update'])->name('admin.job-listings.update');
        Route::delete('job-listings/{id}', [JobListingController::class, 'destroy'])->name('admin.job-listings.destroy');
    });

    // ------------------------- TEACHER ROUTES -------------------------
    Route::middleware('is.Teacher')->prefix('teacher')->group(function () {
        Route::get('profile', [TeacherProfileController::class, 'show']);
        Route::post('profile/update', [TeacherProfileController::class, 'update']);
        
        Route::get('courses', [CourseController::class, 'index']);
        Route::get('courses/{course}', [CourseController::class, 'show']);
        Route::post('courses', [CourseController::class, 'store']);
        Route::post('courses/{course}/update', [CourseController::class, 'update']);
        Route::delete('courses/{course}', [CourseController::class, 'destroy']);

        Route::get('courses/{course}/lessons', [LessonController::class, 'index']);
        Route::post('courses/{course}/lessons', [LessonController::class, 'store']);
        Route::post('courses/{course}/lessons/{lesson}/update', [LessonController::class, 'update']);
        Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy']);

        Route::post('lessons/{lesson}/questions/generate', [LessonQuestionController::class, 'generateAndStore']);
        Route::post('lessons/{lesson}/questions/store', [LessonQuestionController::class, 'store']);
    });

    // ------------------------- SHARED / STUDENT ROUTES -------------------------
    Route::get('comments', [CommentController::class, 'index']);
    Route::post('comments', [CommentController::class, 'store']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('deposit', [PaymentController::class, 'deposit']); 
    Route::get('simulate-payment/{order_id}', [PaymentController::class, 'simulateSuccess']); 
    Route::post('wallet/update', [PaymentController::class, 'updateWalletInfo']);
    
    Route::post('courses/{course}/purchase', [PurchaseController::class, 'purchaseCourse']);
    Route::get('my-courses', [UserController::class, 'getMyCourses']); 
    Route::post('lessons/{lesson}/complete', [LessonController::class, 'completeLesson']);
    Route::post('lessons/{lesson}/questions/submit', [LessonQuestionController::class, 'submitAnswers']);
    Route::get('lessons/{lesson}/questions', [LessonQuestionController::class, 'getQuestions']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile/update', [ProfileController::class, 'update']);
});