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
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return $this->success($user, 'Pengguna dibuat', 201);
    }

    public function show(User $user)
    {
        return $this->success($user->load(['teacher', 'student']));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'role' => ['sometimes', 'required', Rule::in(['admin', 'teacher', 'student'])],
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->fill($request->only(['name', 'email', 'role']));
        $user->save();

        return $this->success($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return $this->success(null, 'Pengguna dihapus');
    }
}
