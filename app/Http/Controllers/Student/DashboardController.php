<?php

namespace App\Http\Controllers\Student;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Grade;
use Illuminate\Http\Request;

class DashboardController extends StudentController
{
    public function __invoke(Request $request)
    {
        $student = $this->resolveStudent($request);

        $summary = $student->attendances()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $today = now()->toDateString();
        $todayAttendance = $student->attendances()->whereDate('date', $today)->value('status');

        $average = $student->grades()->avg('final_score');

        return $this->success([
            'student' => $student->only(['id', 'full_name', 'nisn', 'nis']),
            'class' => $student->classRoom ? $student->classRoom->only(['id', 'name', 'grade_level']) : null,
            'attendance_summary' => $summary,
            'attendance_today' => $todayAttendance,
            'grade_average' => round((float) $average, 2),
        ]);
    }
}
