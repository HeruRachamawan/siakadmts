<?php

namespace App\Http\Controllers\Teacher;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class GradeController extends TeacherController
{
    public function options(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        $teacherSubjects = $teacher->subjects;
        if ($teacherSubjects->isEmpty()) {
            $teacherSubjects = Subject::orderBy('name')->get();
        }

        $classes = ClassRoom::orderBy('name')->get();

        $activeYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();

        return $this->success([
            'subjects' => $teacherSubjects,
            'classes' => $classes,
            'active_academic_year' => $activeYear,
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
        ]);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id ?? AcademicYear::orderBy('id', 'desc')->value('id'));

        $class = ClassRoom::findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);

        $students = Student::where('class_id', $classId)
            ->with('user')
            ->orderBy('full_name')
            ->get();

        $existingGrades = Grade::whereIn('student_id', $students->pluck('id'))
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $yearId)
            ->get()
            ->keyBy('student_id');

        $reportData = $students->map(function (Student $s) use ($existingGrades) {
            $g = $existingGrades->get($s->id);
            return [
                'student_id' => $s->id,
                'full_name' => $s->full_name,
                'nisn' => $s->nisn,
                'nis' => $s->nis,
                'gender' => $s->gender,
                'photo_url' => $s->photo_url,
                'score_assignment' => $g ? (float) $g->score_assignment : 0,
                'score_uts' => $g ? (float) $g->score_uts : 0,
                'score_uas' => $g ? (float) $g->score_uas : 0,
                'custom_scores' => $g ? ($g->custom_scores ?? []) : [],
                'final_score' => $g ? (float) $g->final_score : 0,
            ];
        });

        return $this->success([
            'class' => $class,
            'subject' => $subject,
            'passing_grade' => $subject->passing_grade ?? 75,
            'academic_year_id' => $yearId,
            'students' => $reportData,
        ]);
    }

    public function storeBatch(Request $request)
    {
        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'passing_grade' => ['nullable', 'integer', 'min:0', 'max:100'],
            'grades' => ['required', 'array'],
            'grades.*.student_id' => ['required', 'exists:students,id'],
            'grades.*.score_assignment' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.score_uts' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.score_uas' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.custom_scores' => ['nullable', 'array'],
        ]);

        $subjectId = $request->input('subject_id');
        $academicYearId = $request->input('academic_year_id');

        if ($request->filled('passing_grade')) {
            Subject::where('id', $subjectId)->update(['passing_grade' => $request->input('passing_grade')]);
        }

        foreach ($request->input('grades') as $item) {
            Grade::updateOrCreate(
                [
                    'student_id' => $item['student_id'],
                    'subject_id' => $subjectId,
                    'academic_year_id' => $academicYearId,
                ],
                [
                    'score_assignment' => $item['score_assignment'] ?? 0,
                    'score_uts' => $item['score_uts'] ?? 0,
                    'score_uas' => $item['score_uas'] ?? 0,
                    'custom_scores' => $item['custom_scores'] ?? null,
                ]
            );
        }

        return $this->success(null, 'Seluruh nilai siswa & KKTP/KKM berhasil disimpan');
    }
}
