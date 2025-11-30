<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PathController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Teacher\CourseController;
use App\Http\Controllers\Api\Teacher\LessonController;
use App\Http\Controllers\Api\CommentController;

// ------------------------- PATHS -------------------------
Route::get('/paths', [PathController::class, 'index']);
Route::get('/paths/{path}', [PathController::class, 'show']);

// ------------------------- AUTH -------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login'); 
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

// ------------------------- LOGOUT -------------------------
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// ------------------------- ADMIN ROUTES -------------------------
Route::middleware(['auth:sanctum','is.Admin'])->prefix('admin')->group(function () {
    // قائمة المستخدمين
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);

    // إدارة الطلاب
    Route::post('students', [UserController::class, 'storeStudent']);
    Route::put('students/{student}', [UserController::class, 'updateStudent']);
    Route::delete('students/{student}', [UserController::class, 'destroyUser']);

    // إدارة الأساتذة
    Route::post('teachers', [UserController::class, 'storeTeacher']);
    Route::put('teachers/{teacher}', [UserController::class, 'updateTeacher']);
    Route::delete('teachers/{teacher}', [UserController::class, 'destroyUser']);

    // إدارة الأدمن
    Route::post('admins', [UserController::class, 'storeAdmin']);
    Route::put('admins/{admin}', [UserController::class, 'updateAdmin']);
    Route::delete('admins/{admin}', [UserController::class, 'destroyUser']);

    // إدارة المسارات
    Route::post('paths', [UserController::class, 'storePath']);
    Route::put('paths/{path}', [UserController::class, 'updatePath']);
    Route::delete('paths/{path}', [UserController::class, 'destroyPath']);

    // إدارة التعليقات
    Route::delete('comments/{comment}', [UserController::class, 'destroyComment']);
});

// ------------------------- TEACHER ROUTES -------------------------
Route::middleware(['auth:sanctum','is.Teacher'])->prefix('teacher')->group(function () {
    // Paths
    Route::post('paths', [PathController::class, 'store']);
    Route::put('paths/{path}', [PathController::class, 'update']);

    // Courses
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::post('courses', [CourseController::class, 'store']);
    Route::put('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);

    // Lessons
    Route::get('courses/{course}/lessons/{lesson}', [LessonController::class, 'show']); 
    Route::post('courses/{course}/lessons', [LessonController::class, 'store']);
    Route::put('courses/{course}/lessons/{lesson}', [LessonController::class, 'update']);
    Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy']);

    // Lesson Comments
    Route::get('courses/{course}/lessons/{lesson}/comments', [LessonController::class, 'comments']); 
    Route::post('courses/{course}/lessons/{lesson}/comments', [LessonController::class, 'addComment']);
    Route::delete('courses/{course}/lessons/{lesson}/comments/{comment}', [LessonController::class, 'deleteComment']); 
});

// ------------------------- ADMIN OR TEACHER ROUTES -------------------------
Route::middleware(['auth:sanctum','is.AdminOrTeacher'])->group(function () {
    // كورسات الأستاذ
    Route::get('teachers/{teacher}/courses', [UserController::class, 'teacherCourses']);
    Route::delete('courses/{course}', [UserController::class, 'destroyCourse']);
});

// ------------------------- COMMENTS ROUTES -------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('comments', [CommentController::class, 'index']); 
    Route::post('comments', [CommentController::class, 'store']); 
    Route::put('comments/{comment}', [CommentController::class, 'update']); 
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']); 
});

// ------------------------- JOB ROUTES -------------------------
Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']);
    Route::get('/{job}', [JobController::class, 'show']);
    Route::post('/', [JobController::class, 'store']);
    Route::put('/{job}', [JobController::class, 'update']);
    Route::delete('/{job}', [JobController::class, 'destroy']);
});
