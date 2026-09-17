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

        $studentCount = Student::where(function ($q) use ($classIds) {
            $q->whereIn('class_id', $classIds)
              ->orWhereIn('lokal_class_id', $classIds);
        })->count();
        $classCount = count($classIds);

        $today = now()->toDateString();
        $attendanceToday = Attendance::whereIn('class_id', $classIds)->whereDate('date', $today)->count();
        $presentToday = Attendance::whereIn('class_id', $classIds)->whereDate('date', $today)
            ->where('status', 'present')->count();
        $alphaToday = Attendance::whereIn('class_id', $classIds)->whereDate('date', $today)
            ->where('status', 'alpha')->count();

        $schedulesCount = \App\Models\Schedule::where('teacher_id', $teacher->id)->count();

        $homeroomClasses = ClassRoom::where('homeroom_teacher_id', $teacher->id)->pluck('name')->toArray();

        // Extracurriculars guided by this teacher
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first() ?? \App\Models\AcademicYear::orderBy('id', 'desc')->first();
        $myEkskuls = \App\Models\Extracurricular::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $ekskulData = [];
        $totalActiveStudents = \App\Models\Student::count();

        foreach ($myEkskuls as $ek) {
            $totalParticipants = 0;
            if ($ek->is_mandatory) {
                $totalParticipants = $totalActiveStudents;
            } else {
                $totalParticipants = \App\Models\ExtracurricularMember::where('extracurricular_id', $ek->id)
                    ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                    ->count();
            }

            $gradedCount = \App\Models\ExtracurricularGrade::where('extracurricular_id', $ek->id)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->whereNotNull('grade')
                ->where('grade', '!=', '')
                ->count();

            $pendingCount = max(0, $totalParticipants - $gradedCount);
            $progressPercent = $totalParticipants > 0 ? round(($gradedCount / $totalParticipants) * 100) : 0;

            $ekskulData[] = [
                'id' => $ek->id,
                'name' => $ek->name,
                'code' => $ek->code,
                'is_mandatory' => (bool) $ek->is_mandatory,
                'schedule_day' => $ek->schedule_day,
                'schedule_time' => $ek->schedule_time,
                'description' => $ek->description,
                'total_participants' => $totalParticipants,
                'graded_count' => $gradedCount,
                'pending_count' => $pendingCount,
                'progress_percent' => $progressPercent,
            ];
        }

        $position = $teacher->position;
        if (!$position || trim($position) === '') {
            $rolesList = [];
            if (!empty($homeroomClasses)) {
                $rolesList[] = 'Wali Kelas ' . implode(', ', $homeroomClasses);
            }
            if (!empty($ekskulData)) {
                $ekNames = array_column($ekskulData, 'name');
                $rolesList[] = 'Pembina ' . implode(', ', $ekNames);
            }
            if (!empty($rolesList)) {
                $position = implode(' & ', $rolesList);
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
            'extracurriculars' => $ekskulData,
            'is_advisor' => count($ekskulData) > 0,
            'attendance_today' => [
                'total' => $attendanceToday,
                'present' => $presentToday,
                'alpha' => $alphaToday,
            ],
        ]);
    }
}
