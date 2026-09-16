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
            // Try auto-linking by nip or name
            if ($user) {
                $byNip = Teacher::where('nip', $user->username)->first();
                if ($byNip) {
                    $byNip->update(['user_id' => $user->id]);
                    return $byNip;
                }
                $byName = Teacher::where('full_name', $user->name)->first();
                if ($byName) {
                    $byName->update(['user_id' => $user->id]);
                    return $byName;
                }
            }

            // Fallback for administrative/management roles
            if ($user && in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah'])) {
                $fallback = Teacher::first();
                if ($fallback) {
                    return $fallback;
                }
            }

            // Auto-create teacher profile for user with teacher role
            if ($user && $user->role === 'teacher') {
                return Teacher::create([
                    'user_id' => $user->id,
                    'full_name' => $user->name,
                    'nip' => $user->username,
                ]);
            }

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
