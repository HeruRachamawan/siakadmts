<?php

namespace App\Http\Controllers\Teacher;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends TeacherController
{
    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        $ids = ClassRoom::where('homeroom_teacher_id', $teacher->id)->pluck('id')->toArray();

        $query = Student::whereIn('class_id', $ids)->with(['classRoom.academicYear']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($qq) use ($q) {
                $qq->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $students = $query->orderBy('full_name')->paginate($request->get('per_page', 15));

        return $this->success($this->paginate($students));
    }

    public function show(Request $request, Student $student)
    {
        $teacher = $this->resolveTeacher($request);

        $ids = ClassRoom::where('homeroom_teacher_id', $teacher->id)->pluck('id')->toArray();

        if (! in_array($student->class_id, $ids)) {
            abort(403, 'Siswa ini tidak berada di kelas yang Anda ampu.');
        }

        return $this->success($student->load([
            'classRoom.academicYear',
            'grades.subject',
            'attendances',
        ]));
    }
}
