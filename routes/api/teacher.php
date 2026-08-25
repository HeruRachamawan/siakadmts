<?php

use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\GradeController as TeacherGradeController;
use App\Http\Controllers\Teacher\ScheduleController as TeacherScheduleController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\TranscriptController as TeacherTranscriptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:teacher,admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'show'])->name('profile.show');
    Route::post('profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile', [\App\Http\Controllers\Teacher\ProfileController::class, 'update']);
    Route::get('dashboard', TeacherDashboardController::class);
    Route::get('schedules', [TeacherScheduleController::class, 'index'])->name('schedules.index');
    Route::get('calendar-events', [\App\Http\Controllers\Admin\CalendarEventController::class, 'index']);
    Route::get('classes', function () {
        $teacher = request()->user()->teacher;

        return response()->json(
            \App\Models\ClassRoom::where('homeroom_teacher_id', $teacher->id)
                ->with(['academicYear', 'homeroomTeacher'])
                ->get()
        );
    })->name('classes');

    Route::get('students', [TeacherStudentController::class, 'index'])->name('students.index');
    Route::get('students/{student}', [TeacherStudentController::class, 'show'])->name('students.show');
    Route::get('students/{student}/transcript', [TeacherTranscriptController::class, 'transcript'])
        ->name('students.transcript');
    Route::get('attendance-options', [TeacherAttendanceController::class, 'options']);
    Route::get('attendance', [TeacherAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/{class}', [TeacherAttendanceController::class, 'index']);
    Route::get('attendance-reports', [\App\Http\Controllers\Teacher\AttendanceReportController::class, 'index']);
    Route::get('homeroom-attendance', [\App\Http\Controllers\Teacher\HomeroomAttendanceController::class, 'index']);
    Route::post('homeroom-attendance', [\App\Http\Controllers\Teacher\HomeroomAttendanceController::class, 'store']);
    Route::get('grade-options', [TeacherGradeController::class, 'options']);
    Route::get('grades', [TeacherGradeController::class, 'index'])->name('grades.index');
    Route::post('grades/batch', [TeacherGradeController::class, 'storeBatch'])->name('grades.batch');
    Route::post('grades', [TeacherGradeController::class, 'store'])->name('grades.store');
    Route::get('grades/{grade}', [TeacherGradeController::class, 'show'])->name('grades.show');
    Route::put('grades/{grade}', [TeacherGradeController::class, 'update'])->name('grades.update');
    Route::delete('grades/{grade}', [TeacherGradeController::class, 'destroy'])->name('grades.destroy');

    // Teacher Daily Presensi Geolocation, QR Code & Requests
    Route::get('presensi/today', [\App\Http\Controllers\Teacher\TeacherAttendanceController::class, 'today']);
    Route::post('presensi', [\App\Http\Controllers\Teacher\TeacherAttendanceController::class, 'store']);
    Route::post('presensi/scan-qr', [\App\Http\Controllers\Teacher\TeacherAttendanceController::class, 'scanQr']);
    Route::get('presensi/history', [\App\Http\Controllers\Teacher\TeacherAttendanceController::class, 'history']);
    Route::get('presensi/requests', [\App\Http\Controllers\Teacher\TeacherAttendanceController::class, 'requests']);
    Route::post('presensi/requests', [\App\Http\Controllers\Teacher\TeacherAttendanceController::class, 'submitRequest']);
    Route::get('presensi/recap', [\App\Http\Controllers\Teacher\TeacherAttendanceController::class, 'recap']);

    // PPDB Committee Access for Assigned Teachers
    Route::get('ppdb', [\App\Http\Controllers\Admin\PpdbController::class, 'index']);
    Route::get('ppdb/{id}', [\App\Http\Controllers\Admin\PpdbController::class, 'show']);
    Route::post('ppdb/{id}/process', [\App\Http\Controllers\Admin\PpdbController::class, 'process']);
    Route::post('ppdb/{id}/enroll', [\App\Http\Controllers\Admin\PpdbController::class, 'enroll']);
});
