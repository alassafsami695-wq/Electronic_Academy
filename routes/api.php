<?php

use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TeacherProfileController;
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

/*
|--------------------------------------------------------------------------
| المسارات العامة (لا تتطلب تسجيل دخول)
|--------------------------------------------------------------------------
*/

// ------------------------- OPENAI QUESTIONS -------------------------
Route::post('/generate-questions', [QuestionController::class, 'generate']);

// ------------------------- PUBLIC TEACHER PROFILE -------------------------
Route::get('/teachers/{id}', [TeacherProfileController::class, 'publicShow']);

// ------------------------- PATHS -------------------------
Route::get('/paths', [PathController::class, 'index']);
Route::get('/paths/{path}', [PathController::class, 'show']);
Route::get('/paths/{path}/courses', [PathController::class, 'course']);
Route::get('/courses/best-selling', [CourseController::class, 'bestSelling']);

// ------------------------- AUTH -------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

// ------------------------- PUBLIC COURSE & JOB ROUTES -------------------------
Route::get('/courses/{course}', [CourseController::class, 'publicShow']);
Route::post('/payments/callback', [PaymentController::class, 'handleCallback']); // الرابط الحقيقي لشام كاش

Route::prefix('job-listings')->group(function () {
    Route::get('/', [JobListingController::class, 'index']);
    Route::get('/{job}', [JobListingController::class, 'show']);
    Route::post('/', [JobListingController::class, 'store']);
    Route::delete('/{job}', [JobListingController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| المسارات المحمية (تتطلب تسجيل دخول Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // ------------------------- LOGOUT -------------------------
    Route::post('/logout', [AuthController::class, 'logout']);

    // ------------------------- ADMIN ROUTES -------------------------
    Route::middleware('is.Admin')->prefix('admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);

        Route::post('students', [UserController::class, 'storeStudent']);
        Route::post('students/{student}/update', [UserController::class, 'updateStudent']);
        Route::delete('students/{student}', [UserController::class, 'destroyStudent']);

        Route::post('teachers', [UserController::class, 'storeTeacher']);
        Route::post('teachers/{teacher}/update', [UserController::class, 'updateTeacher']);
        Route::delete('teachers/{teacher}', [UserController::class, 'destroyTeacher']);

        Route::post('admins', [UserController::class, 'storeAdmin']);
        Route::post('admins/{admin}/update', [UserController::class, 'updateAdmin']);
        Route::delete('admins/{admin}', [UserController::class, 'destroyAdmin']);

        Route::post('paths', [UserController::class, 'storePath']);
        Route::post('paths/{path}/update', [UserController::class, 'updatePath']);
        Route::delete('paths/{path}', [UserController::class, 'destroyPath']);

        Route::delete('comments/{comment}', [UserController::class, 'destroyComment']);

        // إدارة الاعلانات
        Route::post('ads', [AdvertisementController::class, 'store']);
        Route::delete('ads/{id}', [AdvertisementController::class, 'destroy']);
    });

    // ------------------------- TEACHER ROUTES -------------------------
    Route::middleware('is.Teacher')->prefix('teacher')->group(function () {
        Route::get('profile', [TeacherProfileController::class, 'show']);
        Route::post('profile/update', [TeacherProfileController::class, 'update']);
        
        Route::post('paths', [PathController::class, 'store']);
        Route::post('paths/{path}/update', [PathController::class, 'update']);

        Route::get('courses', [CourseController::class, 'index']);
        Route::get('courses/{course}', [CourseController::class, 'show']);
        Route::post('courses', [CourseController::class, 'store']);
        Route::post('courses/{course}/update', [CourseController::class, 'update']);
        Route::delete('courses/{course}', [CourseController::class, 'destroy']);

        Route::get('courses/{course}/lessons', [LessonController::class, 'index']);
        Route::get('courses/{course}/lessons/{lesson}', [LessonController::class, 'show']);
        Route::post('courses/{course}/lessons', [LessonController::class, 'store']);
        Route::post('courses/{course}/lessons/{lesson}/update', [LessonController::class, 'update']);
        Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy']);

        Route::post('lessons/{lesson}/questions/generate', [LessonQuestionController::class, 'generateAndStore']);
        Route::post('lessons/{lesson}/questions/store', [LessonQuestionController::class, 'store']);
        Route::post('lessons/{lesson}/questions/submit', [LessonQuestionController::class, 'submitAnswers']);
    });

    // ------------------------- ADMIN OR TEACHER ROUTES -------------------------
    Route::middleware('is.AdminOrTeacher')->group(function () {
        Route::get('teachers/{teacher}/courses', [UserController::class, 'teacherCourses']);
        Route::delete('courses/{course}', [UserController::class, 'destroyCourse']);
    });

    // ------------------------- AUTHENTICATED USER ROUTES (STUDENTS/ALL) -------------------------
    Route::group([], function () {
        // Comments
        Route::get('comments', [CommentController::class, 'index']);
        Route::get('comments/{id}', [CommentController::class, 'show']);
        Route::post('comments', [CommentController::class, 'store']);
        Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

        // الاعلانات
        Route::get('ads', [AdvertisementController::class, 'index']);

        // المدفوعات والمحفظة
        Route::post('deposit', [PaymentController::class, 'deposit']); // طلب شحن
        Route::get('simulate-payment/{order_id}', [PaymentController::class, 'simulateSuccess']); // للمحاكاة
        Route::post('wallet/update', [PaymentController::class, 'updateWalletInfo']);
        
        // الشراء والدروس
        Route::post('courses/{course}/purchase', [PurchaseController::class, 'purchaseCourse']);
        Route::get('my-courses', [CourseController::class, 'myCourses']);
        Route::post('lessons/{lesson}/complete', [LessonController::class, 'completeLesson']);

        // Profile
        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile/update', [ProfileController::class, 'update']);
        Route::get('lessons/{lesson}/questions', [LessonQuestionController::class, 'getQuestions']);
    });
});