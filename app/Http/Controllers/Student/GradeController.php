<?php

namespace App\Http\Controllers\Student;

use App\Models\AcademicYear;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class GradeController extends StudentController
{
    public function index(Request $request)
    {
        $student = $this->resolveStudent($request);

        $yearId = $request->input('academic_year_id');

        $grades = $student->grades()->with('subject', 'academicYear');

        if ($yearId) {
            $grades->where('academic_year_id', $yearId);
        }

        $grades = $grades->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        $summary = $grades->getCollection()
            ->groupBy(fn ($g) => $g->academicYear->year . ' (' . $g->academicYear->semester . ')')
            ->map(fn ($group) => [
                'count' => $group->count(),
                'average' => round($group->avg('final_score'), 2),
            ]);

        return $this->success([
            'summary' => $summary,
            'overall_average' => round($student->grades()->avg('final_score'), 2),
            'grades' => $grades->items(),
            'meta' => [
                'current_page' => $grades->currentPage(),
                'last_page' => $grades->lastPage(),
                'per_page' => $grades->perPage(),
                'total' => $grades->total(),
            ],
        ]);
    }

    public function show(Request $request, $gradeId)
    {
        $student = $this->resolveStudent($request);

        $grade = $student->grades()->with('subject', 'academicYear')->findOrFail($gradeId);

        return $this->success($grade);
    }

    public function transcript(Request $request)
    {
        $student = $this->resolveStudent($request);

        $yearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->value('id'));
        $year = $yearId ? AcademicYear::find($yearId) : null;

        $grades = $student->grades()->where('academic_year_id', $yearId)->with('subject')->get();

        $attendanceSummary = $student->attendances()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $data = [
            'student' => $student->load('classRoom.academicYear'),
            'academic_year' => $year,
            'grades' => $grades,
            'subjects_count' => $grades->count(),
            'overall_average' => round((float) $grades->avg('final_score'), 2),
            'attendance_summary' => $attendanceSummary,
        ];

        if ($request->boolean('pdf')) {
            $pdf = Pdf::loadView('reports.transcript', $data)->setPaper('a4', 'portrait');

            return $pdf->download('transkrip-' . $student->nisn . '.pdf');
        }

        return $this->success($data);
    }
}
