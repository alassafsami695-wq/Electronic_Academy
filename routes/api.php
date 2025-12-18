<?php

use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TeacherProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PathController;
use App\Http\Controllers\Api\JobListingController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Teacher\CourseController;
use App\Http\Controllers\Api\Teacher\LessonController;
use App\Http\Controllers\Api\Teacher\CommentController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\LessonQuestionController;
use App\Http\Controllers\Api\Teacher\PurchaseController;

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

// ------------------------- LOGOUT -------------------------
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// ------------------------- ADMIN ROUTES -------------------------
Route::middleware(['auth:sanctum','is.Admin'])->prefix('admin')->group(function () {

    // Users
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);

    // Students
    Route::post('students', [UserController::class, 'storeStudent']);
    Route::post('students/{student}/update', [UserController::class, 'updateStudent']);
    Route::delete('students/{student}', [UserController::class, 'destroyStudent']);

    // Teachers
    Route::post('teachers', [UserController::class, 'storeTeacher']);
    Route::post('teachers/{teacher}/update', [UserController::class, 'updateTeacher']);
    Route::delete('teachers/{teacher}', [UserController::class, 'destroyTeacher']);

    // Admins
    Route::post('admins', [UserController::class, 'storeAdmin']);
    Route::post('admins/{admin}/update', [UserController::class, 'updateAdmin']);
    Route::delete('admins/{admin}', [UserController::class, 'destroyAdmin']);

    // Paths
    Route::post('paths', [UserController::class, 'storePath']);
    Route::post('paths/{path}/update', [UserController::class, 'updatePath']);
    Route::delete('paths/{path}', [UserController::class, 'destroyPath']);

    // Comments
    Route::delete('comments/{comment}', [UserController::class, 'destroyComment']);
});

// ------------------------- TEACHER ROUTES -------------------------
Route::middleware(['auth:sanctum','is.Teacher'])->prefix('teacher')->group(function () {

    // Teacher Profile
    Route::get('profile', [TeacherProfileController::class, 'show']);
    Route::post('profile/update', [TeacherProfileController::class, 'update']);

    // Paths
    Route::post('paths', [PathController::class, 'store']);
    Route::post('paths/{path}/update', [PathController::class, 'update']);

    // Courses
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::post('courses', [CourseController::class, 'store']);
    Route::post('courses/{course}/update', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);

    // Lessons
    Route::get('courses/{course}/lessons', [LessonController::class, 'index']);
    Route::get('courses/{course}/lessons/{lesson}', [LessonController::class, 'show']);
    Route::post('courses/{course}/lessons', [LessonController::class, 'store']);
    Route::post('courses/{course}/lessons/{lesson}/update', [LessonController::class, 'update']);
    Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy']);

    // AI Questions
    Route::post('lessons/{lesson}/questions/generate', [LessonQuestionController::class, 'generateAndStore']);
    Route::post('lessons/{lesson}/questions/store', [LessonQuestionController::class, 'store']);
    Route::post('lessons/{lesson}/questions/submit', [LessonQuestionController::class, 'submitAnswers']);
});

// ------------------------- ADMIN OR TEACHER ROUTES -------------------------
Route::middleware(['auth:sanctum','is.AdminOrTeacher'])->group(function () {
    Route::get('teachers/{teacher}/courses', [UserController::class, 'teacherCourses']);
    Route::delete('courses/{course}', [UserController::class, 'destroyCourse']);
});

// ------------------------- AUTHENTICATED USER ROUTES -------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Comments
    Route::get('comments', [CommentController::class, 'index']);
    Route::get('comments/{id}', [CommentController::class, 'show']);
    Route::post('comments', [CommentController::class, 'store']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    // شراء كورس
    Route::post('courses/{course}/purchase', [PurchaseController::class, 'purchaseCourse']);

    // قائمة مشترياتي
    Route::get('my-courses', [CourseController::class, 'myCourses']);

    // إكمال درس
    Route::post('lessons/{lesson}/complete', [LessonController::class, 'completeLesson']);

    // User Profile
    Route::get('profile', [ProfileController::class, 'show']);
    Route::post('profile/update', [ProfileController::class, 'update']);

    // Student Questions
    Route::get('lessons/{lesson}/questions', [LessonQuestionController::class, 'getQuestions']);
});

// ------------------------- JOB ROUTES -------------------------
Route::prefix('job-listings')->group(function () {
    Route::get('/', [JobListingController::class, 'index']);
    Route::get('/{job}', [JobListingController::class, 'show']);
    Route::post('/', [JobListingController::class, 'store']);
    Route::delete('/{job}', [JobListingController::class, 'destroy']);
});
// ------------------------- PUBLIC COURSE INFO -------------------------
Route::get('/courses/{course}', [CourseController::class, 'publicShow']);
