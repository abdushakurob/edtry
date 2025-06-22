<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\LessonChunkController;

use Illuminate\Support\Facades\Auth;
//public routes
Route::get('/', function () {
    return view('home');
}); 
//auth routes
Route::get('/login', function () {
    return view('auth.loginpage');
})->name('login');
Route::get('/signup', function (){
    return view('auth.signuppage');
})->name('signup');

//auth api routes
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);

//RAG APIs
//Route to handle chunked data
Route::post('/api/chunked', [LessonChunkController::class, 'handleChunk']);

Route::post('/ask/ai', [LessonChunkController::class, 'askAI']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/api/user', function () {
        return Auth::user();
    });

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/courses', [DashboardController::class, 'courses']);
    Route::get('/dashboard/courses/{id}', [DashboardController::class, 'viewCourseDetails'])->where('id', '[0-9]+');

    //teacher role routes
    Route::middleware('role:teacher')->group(function () {
        Route::get('/dashboard/courses/create', [DashboardController::class, 'createCourse']);
        
        // API routes for teacher
        Route::prefix('api/teacher')->group(function() {
            // Course management
            Route::get('/courses', [DashboardController::class, 'getTeacherCourses']);
            Route::get('/courses/{id}', [DashboardController::class, 'getTeacherCourseDetails'])->where('id', '[0-9]+');
            Route::post('/courses', [DashboardController::class, 'createNewCourse']);
            Route::put('/courses/{id}', [DashboardController::class, 'updateCourse'])->where('id', '[0-9]+');
            Route::delete('/courses/{id}', [DashboardController::class, 'deleteCourse'])->where('id', '[0-9]+');
             
            // Student management
            Route::get('/courses/{id}/students', [DashboardController::class, 'getCourseStudents'])->where('id', '[0-9]+');
        });
    });

    //student role routes
    Route::middleware('role:student')->group(function () {
        // Dashboard page routes
        Route::get('/dashboard/my-courses', [DashboardController::class, 'allCourses'])->name('student.my-courses');
        Route::get('/dashboard/explore', [DashboardController::class, 'exploreCourses'])->name('student.explore');
        Route::get('/dashboard/lessons', [DashboardController::class, 'lessons'])->name('student.lessons');
        Route::get('/dashboard/lessons/{id}', [DashboardController::class, 'viewLesson'])->name('student.view-lesson');
        
        // API routes moved from api.php
        Route::prefix('api/student')->group(function() {
            // Course management
            Route::get('/courses', [DashboardController::class, 'getEnrolledCourses']);
            Route::get('/courses/{id}', [DashboardController::class, 'getCourseDetails']);
            Route::post('/courses/{id}/enroll', [DashboardController::class, 'enrollInCourse']);
            
            // Course discovery
            Route::get('/available-courses', [DashboardController::class, 'getAvailableCourses']);
            
            // Lesson management
            Route::get('/lessons/{id}', [DashboardController::class, 'getLessonDetails']);
            Route::post('/lessons/{id}/complete', [DashboardController::class, 'completeLesson']);
        });
    });
});
