<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends TeacherController
{
    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        $schedules = Schedule::with(['classRoom', 'subject', 'teacher'])
            ->where(function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                      ->orWhere(function ($q) {
                          $q->whereNull('class_id')
                            ->where('is_activity', true);
                      });
            })
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();

        // Extracurriculars guided by this teacher
        $extracurriculars = \App\Models\Extracurricular::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->withCount('members')
            ->get();

        return $this->success([
            'teacher' => [
                'id' => $teacher->id,
                'full_name' => $teacher->full_name,
                'nip' => $teacher->nip,
                'is_advisor' => (bool) $teacher->is_extracurricular_advisor,
            ],
            'schedules' => $schedules,
            'extracurriculars' => $extracurriculars,
        ]);
    }
}
