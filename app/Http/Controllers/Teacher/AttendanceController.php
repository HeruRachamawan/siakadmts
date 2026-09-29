<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class AttendanceController extends TeacherController
{
    public function options(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        // Get subjects taught by teacher
        $teacherSubjects = $teacher->subjects;
        if ($teacherSubjects->isEmpty()) {
            $teacherSubjects = Subject::orderBy('name')->get();
        }

        // Return all classes with categorization for Utama (7A, 7B, 8A, 8B, 9A, 9B) & Lokal (7, 8, 9A, 9B)
        $classes = ClassRoom::withCount('students')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                $name = trim($c->name);
                $lower = strtolower($name);
                $isClass9A = in_array($name, ['9A', '9a', '9-A', '9-a']) || in_array($lower, ['kelas 9a', 'kelas 9-a', 'ix-a', 'ix a']);
                $isClass9B = in_array($name, ['9B', '9b', '9-B', '9-b']) || in_array($lower, ['kelas 9b', 'kelas 9-b', 'ix-b', 'ix b']);
                $isLokal = in_array($name, ['7', '8']) || in_array($lower, ['kelas 7', 'kelas 8']) ||
                    $isClass9A || $isClass9B ||
                    str_contains($lower, 'lokal') || str_starts_with($lower, 'l-') || str_starts_with($lower, 'lok-');

                $lokalCount = Student::where('lokal_class_id', $c->id)->count();
                $isLokalTingkat = in_array($name, ['7', '8']) || in_array($lower, ['kelas 7', 'kelas 8']);

                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'grade_level' => $c->grade_level,
                    'students_count' => ($lokalCount > 0 && $isLokalTingkat) ? $lokalCount : $c->students_count,
                    'lokal_students_count' => $lokalCount > 0 ? $lokalCount : $c->students_count,
                    'is_lokal' => $isLokal,
                ];
            });

        return $this->success([
            'subjects' => $teacherSubjects,
            'classes' => $classes,
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'date' => ['nullable', 'date'],
        ]);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $date = $request->input('date', now()->toDateString());

        $class = ClassRoom::findOrFail($classId);

        // Check if class is a lokal class tingkat (7 or 8)
        $className = trim($class->name);
        $isLokalTingkat = in_array($className, ['7', '8']) || in_array(strtolower($className), ['kelas 7', 'kelas 8']);
        $hasLokalStudents = Student::where('lokal_class_id', $classId)->exists();

        if ($isLokalTingkat && $hasLokalStudents) {
            $students = Student::where('lokal_class_id', $classId)
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'nisn', 'nis', 'gender', 'photo_url']);
        } else {
            $students = $class->students()->orderBy('full_name')->get(['id', 'full_name', 'nisn', 'nis', 'gender', 'photo_url']);
            // Fallback: if regular students count is 0 but has lokal students
            if ($students->isEmpty() && $hasLokalStudents) {
                $students = Student::where('lokal_class_id', $classId)
                    ->orderBy('full_name')
                    ->get(['id', 'full_name', 'nisn', 'nis', 'gender', 'photo_url']);
            }
        }

        $query = Attendance::where('class_id', $classId)
            ->whereDate('date', $date);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        } else {
            $query->whereNull('subject_id');
        }

        $attendances = $query->get()->keyBy('student_id');

        $list = $students->map(function (Student $s) use ($attendances, $date) {
            $att = $attendances[$s->id] ?? null;

            return [
                'student_id' => $s->id,
                'full_name' => $s->full_name,
                'nisn' => $s->nisn,
                'nis' => $s->nis,
                'gender' => $s->gender,
                'photo_url' => $s->photo_url,
                'status' => $att ? $att->status : 'present',
                'note' => $att ? $att->note : '',
                'date' => $date,
            ];
        });

        return $this->success([
            'class' => $class->only(['id', 'name', 'grade_level']),
            'subject_id' => $subjectId,
            'date' => $date,
            'students' => $list,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'exists:students,id'],
            'attendances.*.status' => ['required', 'in:present,sick,permission,alpha'],
            'attendances.*.note' => ['nullable', 'string'],
        ]);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id') ?: null;
        $date = $request->input('date');

        $teacher = $this->resolveTeacher($request);
        $teacherId = $teacher?->id;

        foreach ($request->input('attendances') as $att) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $att['student_id'],
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'date' => $date,
                ],
                [
                    'teacher_id' => $teacherId,
                    'status' => $att['status'],
                    'note' => $att['note'] ?? null,
                ]
            );
        }

        return $this->success(null, 'Presensi siswa berhasil disimpan');
    }

    /**
     * Get recent attendance session history recorded by this teacher.
     */
    public function history(Request $request)
    {
        $teacher = $this->resolveTeacher($request);
        $teacherId = $teacher?->id;

        $query = Attendance::with(['classRoom:id,name,grade_level', 'subject:id,name,code']);

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        // Group by class_id, subject_id, and date
        $sessions = $query->selectRaw('
                class_id,
                subject_id,
                date,
                MAX(updated_at) as last_updated_at,
                COUNT(*) as total_students,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = "sick" THEN 1 ELSE 0 END) as sick_count,
                SUM(CASE WHEN status = "permission" THEN 1 ELSE 0 END) as permission_count,
                SUM(CASE WHEN status = "alpha" THEN 1 ELSE 0 END) as alpha_count
            ')
            ->groupBy('class_id', 'subject_id', 'date')
            ->orderBy('date', 'desc')
            ->orderBy('last_updated_at', 'desc')
            ->limit(15)
            ->get();

        $result = $sessions->map(function ($s) {
            return [
                'class_id' => $s->class_id,
                'class_name' => $s->classRoom?->name ?? '-',
                'grade_level' => $s->classRoom?->grade_level ?? '-',
                'subject_id' => $s->subject_id,
                'subject_name' => $s->subject?->name ?? 'Presensi Umum (Non-Mapel)',
                'subject_code' => $s->subject?->code ?? '-',
                'date' => $s->date ? $s->date->toDateString() : null,
                'last_updated_at' => $s->last_updated_at ? \Carbon\Carbon::parse($s->last_updated_at)->diffForHumans() : '-',
                'total_students' => (int)$s->total_students,
                'present_count' => (int)$s->present_count,
                'sick_count' => (int)$s->sick_count,
                'permission_count' => (int)$s->permission_count,
                'alpha_count' => (int)$s->alpha_count,
            ];
        });

        return $this->success($result);
    }
}
