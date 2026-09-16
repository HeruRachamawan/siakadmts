<?php

namespace App\Http\Controllers\Teacher;

use App\Models\ClassRoom;
use Illuminate\Http\Request;

class ClassController extends TeacherController
{
    public function index(Request $request)
    {
        $teacher = null;
        try {
            $teacher = $this->resolveTeacher($request);
        } catch (\Throwable $e) {
            return response()->json([]);
        }

        if (!$teacher) {
            return response()->json([]);
        }

        $classes = ClassRoom::where('homeroom_teacher_id', $teacher->id)
            ->with(['academicYear', 'homeroomTeacher'])
            ->get();

        return response()->json($classes);
    }
}
