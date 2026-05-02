<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassScheduleController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherProfileController;
use App\Http\Controllers\ScheduleApiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/teacher/profile/credentials', [TeacherProfileController::class, 'updateCredentials'])
    ->middleware(['auth', 'role:teacher'])
    ->name('teacher.profile.credentials');

// API Routes
Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    Route::get('/schedules/today', [ScheduleApiController::class, 'todaySchedule'])->name('schedules.today');
    Route::get('/classes/upcoming', [ScheduleApiController::class, 'upcomingClasses'])->name('classes.upcoming');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    Route::resource('students', StudentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('teachers', TeacherController::class)->only(['index', 'store', 'destroy']);
    Route::patch('/teachers/{teacher}/status', [TeacherController::class, 'updateStatus'])->name('teachers.status');
    Route::post('/teachers/{teacher}/restore', [TeacherController::class, 'restore'])->name('teachers.restore');
    Route::post('/teachers/{teacher}/reset-password', [TeacherController::class, 'resetPassword'])->name('teachers.reset-password');
    Route::post('/curriculum/subjects', [CurriculumController::class, 'storeSubject'])->name('curriculum.subjects.store');
    Route::put('/curriculum/subjects/{curriculumSubject}', [CurriculumController::class, 'updateSubject'])->name('curriculum.subjects.update');
    Route::delete('/curriculum/subjects/{curriculumSubject}', [CurriculumController::class, 'destroySubject'])->name('curriculum.subjects.destroy');
    Route::resource('curriculum', CurriculumController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('schedules', ClassScheduleController::class)->only(['index', 'store', 'destroy']);
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/student/{student}/print', [ReportController::class, 'studentPrint'])->name('reports.student.print');
    Route::get('/reports/section/print', [ReportController::class, 'sectionPrint'])->name('reports.section.print');
    Route::get('/audit-trail', [AuditLogController::class, 'index'])->name('audit.index');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'teacher'])->name('dashboard');
    Route::get('/attendance/take', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/take', [AttendanceController::class, 'store'])->name('attendance.store');
});
