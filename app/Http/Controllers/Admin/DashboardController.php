<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\Attendance;
use App\Models\CalendarEvent;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\Letter;
use App\Models\PpdbApplicant;
use App\Models\Schedule;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function __invoke()
    {
        $data = [
            'users' => User::count(),
            'teachers' => Teacher::count(),
            'students' => Student::count(),
            'classes' => ClassRoom::count(),
            'subjects' => Subject::count(),
            'schedules' => Schedule::count(),
            'grades' => Grade::count(),
            'attendances' => Attendance::count(),
            'calendar_events' => CalendarEvent::orderBy('start_date', 'asc')->get(),
            'active_academic_year' => optional(AcademicYear::active()->first())->only('id', 'year', 'semester'),
            'student_gender_stats' => [
                'L' => Student::where('gender', 'L')->count(),
                'P' => Student::where('gender', 'P')->count(),
            ],
            'recent_students' => Student::with('classRoom')->latest()->take(5)->get(),
            'recent_activities' => collect([])
                ->merge(Student::latest()->take(5)->get()->map(function($s) {
                    return [
                        'type' => 'student',
                        'title' => 'Siswa Baru: ' . $s->full_name,
                        'description' => 'Mendaftar pada ' . $s->created_at->format('d M Y'),
                        'time' => $s->created_at,
                        'icon' => 'user'
                    ];
                }))
                ->merge(ClassRoom::latest()->take(3)->get()->map(function($c) {
                    return [
                        'type' => 'class',
                        'title' => 'Kelas Baru: ' . $c->name,
                        'description' => 'Dibuat pada ' . $c->created_at->format('d M Y'),
                        'time' => $c->created_at,
                        'icon' => 'academic-cap'
                    ];
                }))
                ->sortByDesc('time')
                ->take(5)
                ->values(),
        ];

        return $this->success($data);
    }

    public function kepalaSekolah(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $setting = SchoolSetting::getSetting();

        // 1. Holiday Check
        $dayOfWeek = strtolower(Carbon::today()->format('l'));
        $isWeeklyHoliday = in_array($dayOfWeek, $setting->weekly_holidays ?? ['sunday']);
        $calendarHoliday = CalendarEvent::where('type', 'holiday')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();
        $isHoliday = $isWeeklyHoliday || !is_null($calendarHoliday);

        // 2. Teacher Attendance Stats Today
        $totalTeachers = Teacher::count();
        $teacherAttendances = TeacherAttendance::whereDate('date', $today)->get();
        $teacherHadir = $teacherAttendances->where('status', 'hadir')->count();
        $teacherTerlambat = $teacherAttendances->where('status', 'terlambat')->count();
        $teacherIzin = $teacherAttendances->where('status', 'izin')->count();
        $teacherSakit = $teacherAttendances->where('status', 'sakit')->count();
        $teacherTugasLuar = $teacherAttendances->where('status', 'tugas_luar')->count();
        $teacherTotalAttended = $teacherHadir + $teacherTerlambat + $teacherTugasLuar;
        $teacherBelumAbsen = max(0, $totalTeachers - $teacherAttendances->count());
        $teacherAttendanceRate = $totalTeachers > 0 ? round(($teacherTotalAttended / $totalTeachers) * 100, 1) : 0;

        // 3. Student Attendance Stats Today
        $totalStudents = Student::count();
        $studentAttendances = Attendance::whereDate('date', $today)->get();
        $studentHadir = $studentAttendances->where('status', 'H')->count();
        $studentSakit = $studentAttendances->where('status', 'S')->count();
        $studentIzin = $studentAttendances->where('status', 'I')->count();
        $studentAlpa = $studentAttendances->where('status', 'A')->count();
        $studentTotalLogged = $studentAttendances->count();
        $studentAttendanceRate = $studentTotalLogged > 0 ? round(($studentHadir / $studentTotalLogged) * 100, 1) : 0;

        // 4. PPDB Stats
        $ppdbTotal = PpdbApplicant::count();
        $ppdbVerified = PpdbApplicant::whereIn('status', ['verified', 'accepted'])->count();
        $ppdbAccepted = PpdbApplicant::where('status', 'accepted')->count();
        $ppdbQuota = 160;

        // 5. Letters Summary
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lettersIncoming = Letter::where('type', 'masuk')
            ->whereMonth('letter_date', $currentMonth)
            ->whereYear('letter_date', $currentYear)
            ->count();
        $lettersOutgoing = Letter::where('type', 'keluar')
            ->whereMonth('letter_date', $currentMonth)
            ->whereYear('letter_date', $currentYear)
            ->count();

        // 6. Grading Progress
        $totalGrades = Grade::count();
        $totalClasses = ClassRoom::count();
        $totalSubjects = Subject::count();
        $expectedGradesTotal = max(1, $totalStudents * $totalSubjects);
        $gradingPercentage = min(100, round(($totalGrades / $expectedGradesTotal) * 100, 1));

        // 7. Upcoming Calendar & Agenda
        $upcomingEvents = CalendarEvent::whereDate('start_date', '>=', $today)
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        // 8. Achievements
        $recentAchievements = Achievement::orderBy('created_at', 'desc')->take(4)->get();

        $data = [
            'overview' => [
                'total_students' => $totalStudents,
                'students_male' => Student::where('gender', 'L')->count(),
                'students_female' => Student::where('gender', 'P')->count(),
                'total_teachers' => $totalTeachers,
                'total_classes' => $totalClasses,
                'total_subjects' => $totalSubjects,
                'total_staff' => User::whereIn('role', ['admin', 'operator', 'kurikulum', 'bendahara', 'kepala_sekolah'])->count(),
                'active_academic_year' => optional(AcademicYear::active()->first())->only('id', 'year', 'semester'),
            ],
            'teacher_attendance' => [
                'total' => $totalTeachers,
                'hadir' => $teacherHadir,
                'terlambat' => $teacherTerlambat,
                'izin' => $teacherIzin,
                'sakit' => $teacherSakit,
                'tugas_luar' => $teacherTugasLuar,
                'belum_absen' => $teacherBelumAbsen,
                'attendance_rate' => $teacherAttendanceRate,
                'is_holiday' => $isHoliday,
                'holiday_name' => $isHoliday ? ($calendarHoliday ? $calendarHoliday->title : 'Hari Libur Rutin') : null,
            ],
            'student_attendance' => [
                'total_logged' => $studentTotalLogged,
                'hadir' => $studentHadir,
                'sakit' => $studentSakit,
                'izin' => $studentIzin,
                'alpa' => $studentAlpa,
                'attendance_rate' => $studentAttendanceRate,
            ],
            'ppdb' => [
                'total' => $ppdbTotal,
                'verified' => $ppdbVerified,
                'accepted' => $ppdbAccepted,
                'target_quota' => $ppdbQuota,
                'progress_percentage' => round(($ppdbTotal / $ppdbQuota) * 100, 1),
            ],
            'letters' => [
                'incoming_month' => $lettersIncoming,
                'outgoing_month' => $lettersOutgoing,
                'total_all' => Letter::count(),
            ],
            'grading_progress' => [
                'total_grades' => $totalGrades,
                'percentage' => $gradingPercentage,
            ],
            'upcoming_events' => $upcomingEvents,
            'recent_achievements' => $recentAchievements,
        ];

        return $this->success($data);
    }
}
