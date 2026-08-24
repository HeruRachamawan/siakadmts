<?php

namespace App\Http\Controllers\Teacher;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TranscriptController extends TeacherController
{
    public function __invoke(Request $request, Student $student)
    {
        $teacher = $this->resolveTeacher($request);

        $classIds = ClassRoom::where('homeroom_teacher_id', $teacher->id)
            ->pluck('id')->toArray();

        if (! in_array($student->class_id, $classIds)) {
            return $this->error('Siswa tidak berada di kelas yang Anda ampu', 403);
        }

        $yearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->value('id'));
        $year = AcademicYear::find($yearId);

        $grades = $student->grades()
            ->where('academic_year_id', $yearId)
            ->with('subject')->get();

        $attendanceSummary = $student->attendances()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $overallAverage = $grades->avg('final_score');

        $data = [
            'student' => $student->load('classRoom.academicYear'),
            'academic_year' => $year,
            'grades' => $grades,
            'subjects_count' => $grades->count(),
            'overall_average' => round($overallAverage, 2),
            'attendance_summary' => $attendanceSummary,
        ];

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('reports.transcript', $data)->setPaper('a4', 'portrait');

            return $pdf->download('transkrip-' . $student->nisn . '.pdf');
        }

        return $this->success($data);
    }
}
