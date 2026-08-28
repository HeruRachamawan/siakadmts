<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends TeacherController
{
    protected function resolveTeacher(Request $request): ?Teacher
    {
        $user = $request->user();
        if (!$user) return null;

        $teacher = $user->teacher ?: Teacher::where('user_id', $user->id)->first();
        if (!$teacher && in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah'])) {
            $teacher = Teacher::first();
        }
        return $teacher;
    }

    public function show(Request $request)
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return $this->error('Data profil guru tidak ditemukan atau belum ditautkan.', 404);
        }

        $user = $teacher->user ?: $request->user();

        return $this->success([
            'user' => [
                'id' => $user->id ?? null,
                'name' => $user->name ?? $teacher->full_name,
                'username' => $user->username ?? $teacher->nip,
                'email' => $user->email ?? null,
                'role' => $user->role ?? 'teacher',
            ],
            'teacher' => $teacher->loadMissing(['subjects', 'classes.academicYear']),
        ]);
    }

    public function update(Request $request)
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return $this->error('Data profil guru tidak ditemukan atau belum ditautkan.', 404);
        }

        $request->validate([
            'full_name'    => ['sometimes', 'required', 'string', 'max:255'],
            'nip'          => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'nip')->ignore($teacher->id)],
            'gender'       => ['nullable', Rule::in(['L', 'P'])],
            'phone'        => ['nullable', 'string', 'max:30'],
            'position'     => ['nullable', 'string', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255'],
            'photo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $updateData = $request->only([
            'full_name', 'nip', 'gender', 'phone', 'position'
        ]);

        if ($request->hasFile('photo')) {
            if ($teacher->photo) {
                Storage::disk('public')->delete($teacher->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('teachers/photos', 'public');
        }

        $teacher->update($updateData);

        // Also update User record name / email if provided
        if ($teacher->user) {
            $userUpdates = [];
            if ($request->filled('full_name')) {
                $userUpdates['name'] = $request->full_name;
            }
            if ($request->filled('email')) {
                $userUpdates['email'] = $request->email;
            }
            if (!empty($userUpdates)) {
                $teacher->user->update($userUpdates);
            }
        }

        return $this->success($teacher->fresh()->loadMissing(['user', 'subjects', 'classes']), 'Profil guru berhasil diperbarui');
    }
}
