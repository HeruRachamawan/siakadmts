<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeroomAttendanceController extends TeacherController
{
    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        // Fetch all classes in school
        $allClasses = ClassRoom::with(['academicYear'])
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'grade_level', 'academic_year_id', 'homeroom_teacher_id']);

        // Tag homeroom classes for this teacher
        $homeroomClasses = $allClasses->where('homeroom_teacher_id', $teacher->id)->values();
        $isHomeroomTeacher = $homeroomClasses->isNotEmpty();

        $classId = $request->input('class_id');
        
        // Priority for default selected class:
        // 1. Explicit class_id from request
        // 2. Teacher's homeroom class (if exists)
        // 3. First class that has students
        // 4. First class in school
        if ($classId) {
            $selectedClass = $allClasses->firstWhere('id', $classId) ?? $allClasses->first();
        } elseif ($isHomeroomTeacher) {
            // Check if teacher's homeroom class has students, or use the first homeroom class
            $selectedClass = $homeroomClasses->first();
            // If the homeroom class has 0 students but another class has students, provide the class list
        } else {
            $selectedClass = $allClasses->first();
        }

        if (!$selectedClass) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada kelas yang terdaftar di sistem.',
                'data' => [
                    'is_homeroom_teacher' => false,
                    'homeroom_classes' => [],
                    'available_classes' => [],
                    'students' => [],
                ]
            ], 404);
        }

        $date = $request->input('date', now()->toDateString());

        // Get all students in the selected class
        $students = Student::where('class_id', $selectedClass->id)
            ->orderBy('full_name', 'asc')
            ->get();

        // Fetch existing attendance records for this class and date (daily attendance with subject_id null)
        $attendances = Attendance::where('class_id', $selectedClass->id)
            ->where('date', $date)
            ->whereNull('subject_id')
            ->get()
            ->keyBy('student_id');

        $isSubmitted = $attendances->isNotEmpty();
        $studentList = [];
        $presentCount = 0;
        $sickCount = 0;
        $permissionCount = 0;
        $alphaCount = 0;

        foreach ($students as $student) {
            $record = $attendances->get($student->id);
            $status = $record ? $record->status : 'present';
            $note = $record ? $record->note : '';

            if ($record) {
                if ($record->status === 'present') $presentCount++;
                elseif ($record->status === 'sick') $sickCount++;
                elseif ($record->status === 'permission') $permissionCount++;
                elseif ($record->status === 'alpha') $alphaCount++;
            }

            $studentList[] = [
                'student_id' => $student->id,
                'full_name' => $student->full_name,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'gender' => $student->gender,
                'photo_url' => $student->photo_url,
                'status' => $status,
                'is_recorded' => !is_null($record),
                'note' => $note ?? '',
            ];
        }

        // Format available classes with labels
        $availableClasses = $allClasses->map(function ($c) use ($teacher) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'grade_level' => $c->grade_level,
                'is_my_homeroom' => ($c->homeroom_teacher_id == $teacher->id),
                'students_count' => Student::where('class_id', $c->id)->count(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'is_homeroom_teacher' => $isHomeroomTeacher,
                'selected_class' => [
                    'id' => $selectedClass->id,
                    'name' => $selectedClass->name,
                    'grade_level' => $selectedClass->grade_level,
                    'is_my_homeroom' => ($selectedClass->homeroom_teacher_id == $teacher->id),
                ],
                'homeroom_classes' => $homeroomClasses,
                'available_classes' => $availableClasses,
                'date' => $date,
                'is_submitted' => $isSubmitted,
                'summary' => [
                    'total_students' => count($students),
                    'present' => $presentCount,
                    'sick' => $sickCount,
                    'permission' => $permissionCount,
                    'alpha' => $alphaCount,
                    'recorded_count' => $attendances->count(),
                ],
                'students' => $studentList,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'exists:students,id'],
            'attendances.*.status' => ['required', Rule::in(['present', 'sick', 'permission', 'alpha'])],
            'attendances.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        $classId = $request->input('class_id');
        $date = $request->input('date');
        $records = $request->input('attendances');

        foreach ($records as $item) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $item['student_id'],
                    'class_id' => $classId,
                    'date' => $date,
                    'subject_id' => null, // Daily Homeroom Attendance
                ],
                [
                    'status' => $item['status'],
                    'note' => $item['note'] ?? null,
                    'teacher_id' => $teacher->id,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => "Presensi harian kelas untuk tanggal $date berhasil disimpan!"
        ]);
    }
}
