<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassController extends BaseController
{
    public function index(Request $request)
    {
        $query = ClassRoom::with(['academicYear', 'homeroomTeacher', 'students']);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->input('academic_year_id'));
        }

        $classes = $query->orderBy('grade_level')
            ->paginate($request->get('per_page', 15));

        return $this->success($this->paginate($classes));
    }

    public function store(Request $request)
    {
        $request->validate([
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', 'max:255'],
        ]);

        $class = ClassRoom::create($request->only([
            'homeroom_teacher_id', 'academic_year_id', 'name', 'grade_level',
        ]));

        return $this->success($class, 'Kelas dibuat', 201);
    }

    public function show(ClassRoom $class)
    {
        return $this->success($class->load(['academicYear', 'homeroomTeacher', 'students']));
    }

    public function update(Request $request, ClassRoom $class)
    {
        $request->validate([
            'homeroom_teacher_id' => ['sometimes', 'nullable', 'exists:teachers,id'],
            'academic_year_id' => ['sometimes', 'required', 'exists:academic_years,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'grade_level' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $class->update($request->only([
            'homeroom_teacher_id', 'academic_year_id', 'name', 'grade_level',
        ]));

        return $this->success($class->load(['academicYear', 'homeroomTeacher']));
    }

    public function destroy(ClassRoom $class)
    {
        $class->delete();

        return $this->success(null, 'Kelas dihapus');
    }

    public function assignStudents(Request $request, ClassRoom $class)
    {
        $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:students,id'],
        ]);

        Student::whereIn('id', $request->input('student_ids'))->update(['class_id' => $class->id]);

        return $this->success($class->load('students'), 'Siswa berhasil di-plotting ke kelas');
    }
}
