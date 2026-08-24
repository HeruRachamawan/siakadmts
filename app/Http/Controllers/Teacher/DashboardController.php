<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends TeacherController
{
    public function __invoke(Request $request)
    {
        $teacher = $this->resolveTeacher($request);
        $teacher->load('subjects');

        $classIds = ClassRoom::where('homeroom_teacher_id', $teacher->id)->pluck('id')->toArray();

        $studentCount = Student::whereIn('class_id', $classIds)->count();
        $classCount = count($classIds);

        $today = now()->toDateString();
        $attendanceToday = Attendance::whereIn('class_id', $classIds)->whereDate('date', $today)->count();
        $presentToday = Attendance::whereIn('class_id', $classIds)->whereDate('date', $today)
            ->where('status', 'present')->count();
        $alphaToday = Attendance::whereIn('class_id', $classIds)->whereDate('date', $today)
            ->where('status', 'alpha')->count();

        $schedulesCount = \App\Models\Schedule::where('teacher_id', $teacher->id)->count();

        $homeroomClasses = ClassRoom::where('homeroom_teacher_id', $teacher->id)->pluck('name')->toArray();

        $position = $teacher->position;
        if (!$position || trim($position) === '') {
            if (!empty($homeroomClasses)) {
                $position = 'Wali Kelas ' . implode(', ', $homeroomClasses);
            } else {
                $position = 'Guru Pengajar';
            }
        }

        return $this->success([
            'teacher' => [
                'id' => $teacher->id,
                'full_name' => $teacher->full_name,
                'nip' => $teacher->nip,
                'position' => $position,
                'photo_url' => $teacher->photo_url,
                'subjects' => $teacher->subjects->pluck('name')->toArray(),
            ],
            'classes_count' => $classCount,
            'students_count' => $studentCount,
            'schedules_count' => $schedulesCount,
            'attendance_today' => [
                'total' => $attendanceToday,
                'present' => $presentToday,
                'alpha' => $alphaToday,
            ],
        ]);
    }
}
