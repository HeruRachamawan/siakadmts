<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AchievementController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\GradeController as AdminGradeController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin,operator,kurikulum,kepala_sekolah'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile.show');
    Route::post('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update']);
    Route::get('dashboard', AdminDashboardController::class);
    Route::get('kepala-sekolah/dashboard', [AdminDashboardController::class, 'kepalaSekolah']);
    
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index']);
    Route::post('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update']);

    Route::get('password-reset-requests', [\App\Http\Controllers\Auth\PasswordController::class, 'getResetRequests']);
    Route::post('password-reset-requests/{id}/approve', [\App\Http\Controllers\Auth\PasswordController::class, 'approveReset']);
    Route::post('password-reset-requests/{id}/reject', [\App\Http\Controllers\Auth\PasswordController::class, 'rejectReset']);

    Route::get('excel/template/{type}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'downloadTemplate']);
    Route::post('excel/preview/{type}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'previewImport']);
    Route::post('excel/import/{type}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'processImport']);
    Route::get('excel/export/{type}', [\App\Http\Controllers\Admin\ExcelImportExportController::class, 'exportExcel']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('academic-years', AcademicYearController::class)
        ->parameters(['academic-years' => 'academic_year']);
    Route::apiResource('teachers', TeacherController::class);
    Route::post('teachers/{teacher}/reset-credentials', [TeacherController::class, 'resetCredentials'])
        ->name('teachers.reset-credentials');
    Route::post('teachers/{teacher}/impersonate', [TeacherController::class, 'impersonate'])
        ->name('teachers.impersonate');
    Route::apiResource('classes', ClassController::class);
    Route::post('classes/{class}/assign-students', [ClassController::class, 'assignStudents'])
        ->name('classes.assign-students');
    Route::apiResource('subjects', SubjectController::class);
    Route::apiResource('facilities', \App\Http\Controllers\Admin\FacilityController::class);
    Route::apiResource('achievements', AchievementController::class);
    Route::apiResource('calendar-events', \App\Http\Controllers\Admin\CalendarEventController::class);
    Route::get('students/template', [AdminStudentController::class, 'template'])->name('students.template');
    Route::post('students/import', [AdminStudentController::class, 'import'])->name('students.import');
    Route::apiResource('students', AdminStudentController::class);
    Route::post('students/{student}/reset-credentials', [AdminStudentController::class, 'resetCredentials'])
        ->name('students.reset-credentials');
    Route::post('students/{student}/assign-class', [AdminStudentController::class, 'assignClass'])
        ->name('students.assign-class');
    Route::post('students/{student}/impersonate', [AdminStudentController::class, 'impersonate'])
        ->name('students.impersonate');
    Route::apiResource('grades', AdminGradeController::class)->only(['index', 'show']);
    Route::get('attendance-reports', [\App\Http\Controllers\Admin\AttendanceReportController::class, 'index']);
    Route::get('attendance-monitoring', [\App\Http\Controllers\Admin\AttendanceReportController::class, 'monitoring']);
    Route::get('daily-student-attendance', [\App\Http\Controllers\Admin\DailyAttendanceMonitoringController::class, 'index']);
    Route::apiResource('schedules', \App\Http\Controllers\Admin\ScheduleController::class);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('galleries', GalleryController::class);
    Route::apiResource('facilities', \App\Http\Controllers\Admin\FacilityController::class);

    // Teacher Attendance & Radius Settings (Admin)
    Route::get('school-settings', [\App\Http\Controllers\Admin\SchoolSettingController::class, 'show']);
    Route::post('school-settings', [\App\Http\Controllers\Admin\SchoolSettingController::class, 'update']);
    Route::get('teacher-attendance-monitoring', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'index']);
    Route::post('teacher-attendance-monitoring/update', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'updateAttendance']);
    Route::post('teacher-attendance-monitoring/reset', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'resetAttendance']);
    Route::get('teacher-attendance-requests', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'getPendingRequests']);
    Route::post('teacher-attendance-requests/{id}/process', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'processRequest']);
    Route::get('teacher-presensi-qr', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'getSchoolQr']);
    Route::post('teacher-attendance-monitoring/scan-teacher-card', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'scanTeacherCard']);
    Route::get('teacher-attendance-monitoring/holidays', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'getHolidays']);
    Route::post('teacher-attendance-monitoring/weekly-holidays', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'updateWeeklyHolidays']);
    Route::post('teacher-attendance-monitoring/holidays', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'storeHoliday']);
    Route::delete('teacher-attendance-monitoring/holidays/{id}', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'deleteHoliday']);
    Route::post('teacher-attendance-monitoring/sync-holidays', [\App\Http\Controllers\Admin\TeacherAttendanceMonitoringController::class, 'syncNationalAndPhbiHolidays']);

    // PPDB Management Routes (Admin & Panitia)
    Route::get('ppdb', [\App\Http\Controllers\Admin\PpdbController::class, 'index']);
    Route::get('ppdb/settings', [\App\Http\Controllers\Admin\PpdbController::class, 'getSettings']);
    Route::post('ppdb/settings', [\App\Http\Controllers\Admin\PpdbController::class, 'updateSettings']);
    Route::get('ppdb/{id}', [\App\Http\Controllers\Admin\PpdbController::class, 'show']);
    Route::post('ppdb/{id}/process', [\App\Http\Controllers\Admin\PpdbController::class, 'process']);
    Route::post('ppdb/{id}/enroll', [\App\Http\Controllers\Admin\PpdbController::class, 'enroll']);
    Route::delete('ppdb/{id}', [\App\Http\Controllers\Admin\PpdbController::class, 'destroy']);
    Route::get('ppdb-teachers-committee', [\App\Http\Controllers\Admin\PpdbController::class, 'getTeachersCommittee']);
    Route::post('ppdb-teachers-committee/{id}/toggle', [\App\Http\Controllers\Admin\PpdbController::class, 'toggleTeacherCommittee']);

    // Persuratan (Surat Masuk & Surat Keluar)
    Route::get('letters', [\App\Http\Controllers\Admin\LetterController::class, 'index']);
    Route::post('letters', [\App\Http\Controllers\Admin\LetterController::class, 'store']);
    Route::get('letters/{id}', [\App\Http\Controllers\Admin\LetterController::class, 'show']);
    Route::post('letters/{id}', [\App\Http\Controllers\Admin\LetterController::class, 'update']);
    Route::post('letters/{id}/disposition', [\App\Http\Controllers\Admin\LetterController::class, 'updateDisposition']);
    Route::post('letters/generate-certificate', [\App\Http\Controllers\Admin\LetterController::class, 'generateStudentCertificate']);
    Route::delete('letters/{id}', [\App\Http\Controllers\Admin\LetterController::class, 'destroy']);
});
