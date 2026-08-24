<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends BaseController
{
    public function index(Request $request)
    {
        $query = Grade::with(['student', 'subject', 'academicYear']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->input('academic_year_id'));
        }

        $grades = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->success($this->paginate($grades));
    }

    public function show(Grade $grade)
    {
        return $this->success($grade->load(['student.classRoom.academicYear', 'subject', 'academicYear']));
    }
}
