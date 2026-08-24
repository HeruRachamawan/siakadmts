<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends TeacherController
{
    protected function resolveTeacher(Request $request): Teacher
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher && $user->role === 'admin') {
            $teacher = Teacher::first();
        }
        if (!$teacher) {
            abort(404, 'Data profil guru tidak ditemukan.');
        }
        return $teacher;
    }

    public function show(Request $request)
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return $this->error('Data profil guru tidak ditemukan', 404);
        }

        return $this->success([
            'user' => [
                'id' => $teacher->user->id ?? null,
                'name' => $teacher->user->name ?? null,
                'username' => $teacher->user->username ?? null,
                'email' => $teacher->user->email ?? null,
                'role' => $teacher->user->role ?? 'teacher',
            ],
            'teacher' => $teacher->load(['subjects', 'classes.academicYear']),
        ]);
    }

    public function update(Request $request)
    {
        $teacher = $this->resolveTeacher($request);
        if (!$teacher) {
            return $this->error('Data profil guru tidak ditemukan', 404);
        }

        $request->validate([
            'full_name'    => ['sometimes', 'required', 'string', 'max:255'],
            'nuptk'        => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'nuptk')->ignore($teacher->id)],
            'nip'          => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'nip')->ignore($teacher->id)],
            'gender'       => ['nullable', Rule::in(['L', 'P'])],
            'birth_place'  => ['nullable', 'string', 'max:255'],
            'birth_date'   => ['nullable', 'date'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'address'      => ['nullable', 'string'],
            'position'     => ['nullable', 'string', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255'],
            'photo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $updateData = $request->only([
            'full_name', 'nuptk', 'nip', 'gender', 'birth_place', 'birth_date', 'phone', 'address', 'position'
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

        return $this->success($teacher->fresh()->load(['user', 'subjects', 'classes']), 'Profil guru berhasil diperbarui');
    }
}
