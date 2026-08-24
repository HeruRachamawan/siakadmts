<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends StudentController
{
    public function show(Request $request)
    {
        $student = $this->resolveStudent($request);

        return $this->success([
            'user' => [
                'id' => $student->user->id ?? null,
                'name' => $student->user->name ?? null,
                'username' => $student->user->username ?? null,
                'email' => $student->user->email ?? null,
                'role' => $student->user->role ?? 'student',
            ],
            'student' => $student,
            'class' => $student->classRoom ? $student->classRoom->load('academicYear', 'homeroomTeacher') : null,
        ]);
    }

    public function update(Request $request)
    {
        $student = $this->resolveStudent($request);

        $request->validate([
            'full_name'        => ['sometimes', 'required', 'string', 'max:255'],
            'nik'              => ['nullable', 'string', 'max:20'],
            'gender'           => ['sometimes', 'required', Rule::in(['L', 'P'])],
            'birth_place'      => ['nullable', 'string', 'max:255'],
            'birth_date'       => ['nullable', 'date'],
            'address'          => ['nullable', 'string'],
            'parent_phone'     => ['nullable', 'string', 'max:30'],
            'previous_school'  => ['nullable', 'string', 'max:255'],
            'mother_name'      => ['nullable', 'string', 'max:255'],
            'mother_status'    => ['nullable', Rule::in(['hidup', 'meninggal', 'tidak_diketahui', 'pisah', 'lainnya'])],
            'mother_nik'       => ['nullable', 'string', 'max:255'],
            'mother_job'       => ['nullable', 'string', 'max:255'],
            'mother_income'    => ['nullable', 'string', 'max:255'],
            'father_name'      => ['nullable', 'string', 'max:255'],
            'father_status'    => ['nullable', Rule::in(['hidup', 'meninggal', 'tidak_diketahui', 'pisah', 'lainnya'])],
            'father_nik'       => ['nullable', 'string', 'max:255'],
            'father_job'       => ['nullable', 'string', 'max:255'],
            'father_income'    => ['nullable', 'string', 'max:255'],
            'guardian_name'    => ['nullable', 'string', 'max:255'],
            'guardian_relation'=> ['nullable', 'string', 'max:255'],
            'guardian_nik'     => ['nullable', 'string', 'max:255'],
            'guardian_job'     => ['nullable', 'string', 'max:255'],
            'guardian_phone'   => ['nullable', 'string', 'max:30'],
            'guardian_income'  => ['nullable', 'string', 'max:255'],
            'photo'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $updateData = $request->only([
            'full_name', 'nik', 'gender', 'birth_place', 'birth_date', 'address', 'parent_phone', 'previous_school',
            'mother_name', 'mother_status', 'mother_nik', 'mother_job', 'mother_income',
            'father_name', 'father_status', 'father_nik', 'father_job', 'father_income',
            'guardian_name', 'guardian_relation', 'guardian_nik', 'guardian_job', 'guardian_phone', 'guardian_income',
        ]);

        if ($request->hasFile('photo')) {
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student->update($updateData);

        if ($student->user && $request->filled('full_name')) {
            $student->user->update(['name' => $request->full_name]);
        }

        return $this->success($student->fresh()->load(['user', 'classRoom.academicYear']), 'Profil siswa berhasil diperbarui');
    }
}
