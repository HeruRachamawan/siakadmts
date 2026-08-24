<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends BaseController
{
    public function show(Request $request)
    {
        $user = $request->user();
        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at ? $user->created_at->format('d F Y') : '-',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($request->only(['name', 'username', 'email']));

        return $this->success($user->fresh(), 'Profil admin berhasil diperbarui');
    }
}
