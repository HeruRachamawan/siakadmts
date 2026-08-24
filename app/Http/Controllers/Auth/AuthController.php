<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginInput = trim($request->input('username'));
        $throttleKey = Str::lower($loginInput) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return $this->error('Terlalu banyak percobaan login. Silakan coba lagi nanti.', 429);
        }

        // Find user by username, email, teacher NIP/NUPTK, or student NISN/NIS
        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->orWhereHas('teacher', function ($q) use ($loginInput) {
                $q->where('nip', $loginInput);
            })
            ->orWhereHas('student', function ($q) use ($loginInput) {
                $q->where('nisn', $loginInput)->orWhere('nis', $loginInput);
            })
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return $this->error('Username atau Password yang Anda masukkan salah!', 401);
        }

        auth()->login($user);
        RateLimiter::clear($throttleKey);

        $token = $user->createToken('web-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $this->formatUserPayload($user),
        ], 'Login berhasil');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'address' => ['required', 'string'],
            'parent_phone' => ['required', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        $user->student()->create([
            'nisn' => str_pad($user->id, 10, '0', STR_PAD_LEFT),
            'nis' => str_pad($user->id, 8, '0', STR_PAD_LEFT),
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'parent_phone' => $request->parent_phone,
        ]);

        $token = $user->createToken('web-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $this->formatUserPayload($user),
        ], 'Registrasi berhasil', 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logout berhasil');
    }

    public function me(Request $request)
    {
        if (! $request->user()) {
            return $this->error('Unauthenticated', 401);
        }

        return $this->success($this->formatUserPayload($request->user()));
    }

    private function formatUserPayload(User $user): array
    {
        $payload = $user->only(['id', 'name', 'email', 'username', 'role']);

        if ($user->role === 'teacher' && $user->teacher) {
            $teacher = $user->teacher;
            $homeroomClasses = $teacher->classes()->get(['id', 'name', 'grade_level']);

            $payload['teacher_id'] = $teacher->id;
            $payload['nip'] = $teacher->nip;
            $payload['full_name'] = $teacher->full_name;
            $payload['is_homeroom_teacher'] = $homeroomClasses->isNotEmpty();
            $payload['homeroom_classes'] = $homeroomClasses;
        }

        return $payload;
    }
}
