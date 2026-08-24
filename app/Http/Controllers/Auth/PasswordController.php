<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\PasswordResetRequest;

class PasswordController extends Controller
{
    /**
     * Change password for logged-in user.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama yang Anda masukkan tidak sesuai.'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password berhasil diperbarui!'
        ]);
    }

    /**
     * Public endpoint to submit a password reset request (from Login page).
     */
    public function requestReset(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
        ]);

        $identity = trim($request->identity);
        $foundUser = null;
        $role = null;
        $name = null;

        // Try searching user by username, email, or name
        $user = User::where('username', $identity)
            ->orWhere('email', $identity)
            ->orWhere('name', 'LIKE', "%{$identity}%")
            ->first();

        if ($user) {
            $foundUser = $user;
            $role = $user->role;
            $name = $user->name;
        } else {
            // Try searching teacher by NIP, NUPTK, or full_name
            $teacher = Teacher::where('nip', $identity)
                ->orWhere('nuptk', $identity)
                ->orWhere('full_name', 'LIKE', "%{$identity}%")
                ->first();
            if ($teacher && $teacher->user) {
                $foundUser = $teacher->user;
                $role = 'teacher';
                $name = $teacher->full_name;
            } else {
                // Try searching student by NISN, NIS, or full_name
                $student = Student::where('nisn', $identity)
                    ->orWhere('nis', $identity)
                    ->orWhere('full_name', 'LIKE', "%{$identity}%")
                    ->first();
                if ($student && $student->user) {
                    $foundUser = $student->user;
                    $role = 'student';
                    $name = $student->full_name;
                }
            }
        }

        // Check if there's already a pending request
        $existing = PasswordResetRequest::where('identity', $identity)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Permintaan reset password untuk identitas ini sudah diajukan dan sedang menunggu verifikasi Admin.'
            ]);
        }

        PasswordResetRequest::create([
            'identity' => $identity,
            'role' => $role ?? 'user',
            'name' => $name ?? $identity,
            'reason' => $request->input('reason', 'Lupa password akun'),
            'status' => 'pending',
            'user_id' => $foundUser ? $foundUser->id : null,
        ]);

        return response()->json([
            'message' => 'Permintaan reset password telah berhasil dikirim ke Admin!'
        ]);
    }

    /**
     * Admin: Get all pending password reset requests.
     */
    public function getResetRequests()
    {
        $requests = PasswordResetRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    /**
     * Admin: Approve & reset password to default/nisn/nip.
     */
    public function approveReset($id)
    {
        $req = PasswordResetRequest::findOrFail($id);

        if ($req->user) {
            $user = $req->user;
            $newPass = '12345678';

            if ($user->role === 'student' && $user->student) {
                $newPass = $user->student->nisn ?? '12345678';
            } elseif ($user->role === 'teacher' && $user->teacher) {
                $newPass = $user->teacher->nip ?? $user->teacher->nuptk ?? '12345678';
            }

            $user->password = Hash::make($newPass);
            $user->save();
        }

        $req->status = 'approved';
        $req->save();

        return response()->json([
            'message' => 'Password berhasil di-reset dan permohonan disetujui!'
        ]);
    }

    /**
     * Admin: Reject password reset request.
     */
    public function rejectReset($id)
    {
        $req = PasswordResetRequest::findOrFail($id);
        $req->status = 'rejected';
        $req->save();

        return response()->json([
            'message' => 'Permintaan reset password telah ditolak.'
        ]);
    }

    /**
     * Logged-in user: Get status of my password reset requests.
     */
    public function getMyResetRequests(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['data' => []]);
        }

        $requests = PasswordResetRequest::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('identity', $user->username)
                  ->orWhere('identity', $user->email);
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }
}
