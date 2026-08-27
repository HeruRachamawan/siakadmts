<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $query = User::query();
        $scope = $request->input('scope', 'staff'); // 'staff' | 'all'

        if ($scope === 'staff') {
            $query->whereIn('role', ['admin', 'operator', 'kurikulum', 'bendahara', 'kepala_sekolah']);
        }

        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Summary counts for staff roles
        $summary = [
            'total_staff' => User::whereIn('role', ['admin', 'operator', 'kurikulum', 'bendahara', 'kepala_sekolah'])->count(),
            'admin' => User::where('role', 'admin')->count(),
            'kepala_sekolah' => User::where('role', 'kepala_sekolah')->count(),
            'operator' => User::where('role', 'operator')->count(),
            'kurikulum' => User::where('role', 'kurikulum')->count(),
            'bendahara' => User::where('role', 'bendahara')->count(),
            'all_users' => User::count(),
        ];

        $users = $query
            ->with(['teacher', 'student'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        $data = $this->paginate($users);
        $data['summary'] = $summary;

        return $this->success($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'operator', 'kurikulum', 'bendahara', 'kepala_sekolah', 'teacher', 'student'])],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $email = $request->email ?: ($request->username . '@siakadmts.local');

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->filled('teacher_id')) {
            $teacher = Teacher::find($request->teacher_id);
            if ($teacher) {
                $teacher->user_id = $user->id;
                $teacher->save();
            }
        }

        return $this->success($user, 'Akun staf/pengguna berhasil dibuat', 201);
    }

    public function show(User $user)
    {
        return $this->success($user->load(['teacher', 'student']));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['sometimes', 'required', Rule::in(['admin', 'operator', 'kurikulum', 'bendahara', 'kepala_sekolah', 'teacher', 'student'])],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->filled('username') && !$request->filled('email')) {
            $user->email = $request->username . '@siakadmts.local';
        }

        $user->fill($request->only(['name', 'username', 'role']));
        $user->save();

        if ($request->has('teacher_id')) {
            // unlink previous if any
            Teacher::where('user_id', $user->id)->update(['user_id' => null]);
            if ($request->filled('teacher_id')) {
                $teacher = Teacher::find($request->teacher_id);
                if ($teacher) {
                    $teacher->user_id = $user->id;
                    $teacher->save();
                }
            }
        }

        return $this->success($user, 'Data pengguna berhasil diperbarui');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return $this->error('Anda tidak dapat menghapus akun Anda sendiri.', 422);
        }

        // If linked to teacher or student, detach user_id rather than cascading deletion
        Teacher::where('user_id', $user->id)->update(['user_id' => null]);

        $user->delete();

        return $this->success(null, 'Pengguna berhasil dihapus');
    }
}
