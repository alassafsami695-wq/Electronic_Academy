<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController, ProfileController, AdvertisementController, 
    FeatureController, PathController, JobListingController,
    DashboardController, WishlistController, StatisticsController
};
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Teacher\{CourseController, LessonController, CommentController, PurchaseController};
use App\Http\Controllers\{LessonQuestionController, PaymentController, ContactSettingController};

/*
|--------------------------------------------------------------------------
| 1. المسارات العامة (Public Routes)
|--------------------------------------------------------------------------
*/
Route::get('ads', [AdvertisementController::class, 'index']); 
Route::get('features', [FeatureController::class, 'index']); 
Route::get('features/{id}', [FeatureController::class, 'show']);
Route::get('contact-settings', [ContactSettingController::class, 'index']);
Route::get('/teachers/{id}', [ProfileController::class, 'publicShow']);
Route::get('/paths', [PathController::class, 'index']);
Route::get('/paths/{path}', [PathController::class, 'show']);
Route::get('/paths/{path}/courses', [PathController::class, 'course']);
Route::get('/courses', [CourseController::class, 'index']); 
Route::get('/courses/best-selling', [CourseController::class, 'bestSelling']);
Route::get('/courses/{course}', [CourseController::class, 'publicShow']); 

Route::post('/register/student', [AuthController::class, 'registerStudent']); 
Route::post('/register/teacher', [AuthController::class, 'registerTeacher']); 
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/payments/callback', [PaymentController::class, 'handleCallback']); 

Route::prefix('job-listings')->group(function () {
    Route::get('/', [JobListingController::class, 'index'])->name('job-listings.index');
    Route::get('/{job}', [JobListingController::class, 'show'])->name('job-listings.show');
});

/*
|--------------------------------------------------------------------------
| 2. المسارات المحمية (Protected Routes)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- إدارة الحساب والملف الشخصي ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);
    Route::post('wallet/withdraw', [DashboardController::class, 'withdraw']);
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile/update', [ProfileController::class, 'update']);
    
    // --- إحصائيات مشتركة (أدمن + مدرس) ---
    // هذا المسار متاح لأي مستخدم مسجل دخول (بما في ذلك المدرس)
    Route::get('statistics/students', [StatisticsController::class, 'getStudentsStats']);

    // --- المحتوى التعليمي الخاص بالطالب ---
    Route::get('my-courses', [CourseController::class, 'myCourses']); 
    Route::get('courses/{course}', [CourseController::class, 'show']); 

    Route::get('wishlist', [WishlistController::class, 'index']);
    Route::post('wishlist/toggle', [WishlistController::class, 'toggleWishlist']);

    /*
    |--------------------------------------------------------------------------
    | 3. مسارات الأدمن (Admin Routes)
    |--------------------------------------------------------------------------
    */
    Route::middleware('is.Admin')->prefix('admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
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
        Route::post('job-listings', [JobListingController::class, 'store']);
        Route::post('job-listings/{id}', [JobListingController::class, 'update']);
        Route::delete('job-listings/{id}', [JobListingController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | 4. مسارات المدرس (Teacher Routes)
    |--------------------------------------------------------------------------
    */
    Route::middleware('is.Teacher')->prefix('teacher')->group(function () {
        // دوال إضافية للمدرس جلبناها من طلباتك السابقة
        Route::get('courses/exams', [CourseController::class, 'getCoursesWithExams']);
        Route::get('courses/details', [CourseController::class, 'getDetailedCourses']);

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

    /*
    |--------------------------------------------------------------------------
    | 5. مسارات التعليقات والمشتريات والدروس
    |--------------------------------------------------------------------------
    */
    Route::get('comments', [CommentController::class, 'index']); 
    Route::post('comments', [CommentController::class, 'store']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
    
    Route::post('deposit', [PaymentController::class, 'deposit']); 
    Route::get('simulate-payment/{order_id}', [PaymentController::class, 'simulateSuccess']); 
    Route::post('wallet/update', [PaymentController::class, 'updateWalletInfo']);
    
    Route::post('courses/purchase', [PurchaseController::class, 'purchaseCourses']);
    Route::post('courses/{course}/purchase', [PurchaseController::class, 'purchaseSingleCourse']);

    Route::post('lessons/{lesson}/complete', [LessonController::class, 'completeLesson']);
    Route::post('lessons/{lesson}/questions/submit', [LessonQuestionController::class, 'submitAnswers']);
    Route::get('lessons/{lesson}/questions', [LessonQuestionController::class, 'getQuestions']);
});