<?php

namespace App\Http\Controllers\Teacher;

use App\Models\ClassRoom;
use Illuminate\Http\Request;

class ClassController extends TeacherController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isStaff = $user && in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah']);

        if ($isStaff) {
            $classes = ClassRoom::with(['academicYear', 'homeroomTeacher'])->get();
            return response()->json($classes);
        }

        $teacher = $this->resolveTeacher($request);

        if (!$teacher) {
            return response()->json([]);
        }

        $classes = ClassRoom::where('homeroom_teacher_id', $teacher->id)
            ->with(['academicYear', 'homeroomTeacher'])
            ->get();

        return response()->json($classes);
    }
}
