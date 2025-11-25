<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PathController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Teacher\CourseController;
use App\Http\Controllers\Api\Teacher\LessonController;
use App\Http\Controllers\Api\TrackController;

// ------------------------- PATHS -------------------------
Route::get('/paths', [PathController::class, 'index']);
Route::get('/paths/{path}', [PathController::class, 'show']);

// ------------------------- TRACKS -------------------------
Route::get('/tracks', [TrackController::class, 'index']);          
Route::get('/tracks/{name}', [TrackController::class, 'show']);   
Route::post('/tracks', [TrackController::class, 'store']);        

// ------------------------- AUTH -------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

// ------------------------- LOGOUT -------------------------
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// ------------------------- ADMIN ROUTES -------------------------
Route::middleware(['auth:sanctum','is.admin'])->prefix('admin')->group(function () {
    Route::post('users/teacher', [UserController::class, 'storeTeacher']);
    Route::post('users/admin', [UserController::class, 'storeAdmin']);
});

// ------------------------- TEACHER ROUTES -------------------------
Route::middleware(['auth:sanctum','isTeacher'])->prefix('teacher')->group(function () {
    // Courses
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::post('courses', [CourseController::class, 'store']);
    Route::put('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);

    // Lessons
    Route::post('courses/{course}/lessons', [LessonController::class, 'store']);
    Route::put('courses/{course}/lessons/{lesson}', [LessonController::class, 'update']);
    Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy']);
});

// ------------------------- JOB ROUTES -------------------------
Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']);  
    Route::get('/{job}', [JobController::class, 'show']);      
    Route::post('/', [JobController::class, 'store']);         
    Route::put('/{job}', [JobController::class, 'update']);    
    Route::delete('/{job}', [JobController::class, 'destroy']); 
});
