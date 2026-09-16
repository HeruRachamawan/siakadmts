<?php

namespace App\Http\Controllers\Teacher;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends TeacherController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isStaff = $user && in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah']);

        if ($isStaff) {
            $query = Student::with(['classRoom.academicYear', 'lokalClassRoom']);
        } else {
            $teacher = $this->resolveTeacher($request);
            $ids = ClassRoom::where('homeroom_teacher_id', $teacher->id)->pluck('id')->toArray();
            $query = Student::where(function ($q) use ($ids) {
                $q->whereIn('class_id', $ids)
                  ->orWhereIn('lokal_class_id', $ids);
            })->with(['classRoom.academicYear', 'lokalClassRoom']);
        }

        if ($request->filled('class_id')) {
            $cid = $request->input('class_id');
            $query->where(function ($q) use ($cid) {
                $q->where('class_id', $cid)
                  ->orWhere('lokal_class_id', $cid);
            });
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
        $user = $request->user();
        $isStaff = $user && in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah']);

        if (! $isStaff) {
            $teacher = $this->resolveTeacher($request);
            $ids = ClassRoom::where('homeroom_teacher_id', $teacher->id)->pluck('id')->toArray();

            if (! in_array($student->class_id, $ids) && ! in_array($student->lokal_class_id, $ids)) {
                abort(403, 'Siswa ini tidak berada di kelas yang Anda ampu.');
            }
        }

        return $this->success($student->load([
            'classRoom.academicYear',
            'lokalClassRoom',
            'grades.subject',
            'attendances',
        ]));
    }
}
