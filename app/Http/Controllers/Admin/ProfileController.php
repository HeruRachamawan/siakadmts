<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends BaseController
{
    public function show(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher ?: Teacher::where('user_id', $user->id)->first();

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'teacher_id' => $teacher?->id,
                'created_at' => $user->created_at ? $user->created_at->format('d F Y') : '-',
            ],
            'teacher' => $teacher ? $teacher->loadMissing(['subjects', 'classes.academicYear']) : null,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher ?: Teacher::where('user_id', $user->id)->first();

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'nip'      => ['nullable', 'string', 'max:50', $teacher ? Rule::unique('teachers', 'nip')->ignore($teacher->id) : 'nullable'],
            'gender'   => ['nullable', Rule::in(['L', 'P'])],
            'phone'    => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:255'],
            'photo'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $userUpdates = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userUpdates['password'] = Hash::make($request->password);
        }

        $user->update($userUpdates);

        // Update teacher biodata if teacher profile exists or create if needed
        if ($teacher || $request->filled('nip') || $request->filled('phone') || $request->hasFile('photo') || $request->filled('position')) {
            $teacherData = [
                'full_name' => $request->name,
                'gender' => $request->gender ?: ($teacher?->gender ?: 'L'),
                'phone' => $request->phone,
                'position' => $request->position,
            ];

            if ($request->filled('nip')) {
                $teacherData['nip'] = $request->nip;
            } elseif (!$teacher) {
                $teacherData['nip'] = $user->username ?: 'STAF-' . $user->id;
            }

            if ($request->hasFile('photo')) {
                if ($teacher && $teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }
                $teacherData['photo'] = $request->file('photo')->store('teachers/photos', 'public');
            }

            if ($teacher) {
                $teacher->update($teacherData);
            } else {
                $teacherData['user_id'] = $user->id;
                $teacher = Teacher::create($teacherData);
                $user->update(['teacher_id' => $teacher->id]);
            }
        }

        return $this->show($request);
    }
}
