<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClassRoom;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends BaseController
{
    protected function resolveTeacher(Request $request): Teacher
    {
        $user = $request->user();
        $teacher = $user->teacher ?: Teacher::where('user_id', $user->id)->first();

        if (! $teacher) {
            abort(403, 'Akun Anda belum ditautkan dengan data guru.');
        }

        return $teacher;
    }

    protected function ownedClass(Request $request, $class): ?ClassRoom
    {
        $teacher = $this->resolveTeacher($request);

        $class = $class instanceof ClassRoom ? $class : ClassRoom::findOrFail($class);

        if ($class->homeroom_teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak menjadi wali kelas kelas ini.');
        }

        return $class;
    }
}
