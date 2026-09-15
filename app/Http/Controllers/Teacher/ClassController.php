<?php

namespace App\Http\Controllers\Teacher;

use App\Models\ClassRoom;
use Illuminate\Http\Request;

class ClassController extends TeacherController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teacher = $user ? ($user->teacher ?: \App\Models\Teacher::where('user_id', $user->id)->first()) : null;

        if (!$teacher) {
            return response()->json([]);
        }

        $classes = ClassRoom::where('homeroom_teacher_id', $teacher->id)
            ->with(['academicYear', 'homeroomTeacher'])
            ->get();

        return response()->json($classes);
    }
}
