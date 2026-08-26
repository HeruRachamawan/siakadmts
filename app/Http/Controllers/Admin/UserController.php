<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query
            ->with(['teacher', 'student'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->success($this->paginate($users));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'operator', 'kurikulum', 'teacher', 'student'])],
        ]);

        $email = $request->email ?: ($request->username . '@siakadmts.local');

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return $this->success($user, 'Pengguna berhasil dibuat', 201);
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
            'role' => ['sometimes', 'required', Rule::in(['admin', 'operator', 'kurikulum', 'teacher', 'student'])],
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->filled('username') && !$request->filled('email')) {
            $user->email = $request->username . '@siakadmts.local';
        }

        $user->fill($request->only(['name', 'username', 'role']));
        $user->save();

        return $this->success($user, 'Data pengguna berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return $this->success(null, 'Pengguna dihapus');
    }
}
