<?php

use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\GradeController as StudentGradeController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\StudentFinanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', StudentDashboardController::class);
    Route::get('profile', [StudentProfileController::class, 'show'])->name('profile.show');
    Route::post('profile', [StudentProfileController::class, 'update'])->name('profile.update');
    Route::put('profile', [StudentProfileController::class, 'update']);
    Route::get('attendances', [StudentAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('grades', [StudentGradeController::class, 'index'])->name('grades.index');
    Route::get('grades/{grade}', [StudentGradeController::class, 'show'])->name('grades.show');
    Route::get('transcript', [StudentGradeController::class, 'transcript'])->name('transcript');

    // Buku Kendali SPP & Bukti Kwitansi Siswa
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('card', [StudentFinanceController::class, 'card']);
        Route::get('receipt/{invoice_number}', [StudentFinanceController::class, 'receipt']);
    });
});
