<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

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
            'schedules' => \App\Models\Schedule::count(),
            'grades' => Grade::count(),
            'attendances' => Attendance::count(),
            'calendar_events' => \App\Models\CalendarEvent::orderBy('start_date', 'asc')->get(),
            'active_academic_year' => optional(\App\Models\AcademicYear::active()->first())->only('id', 'year', 'semester'),
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
}
