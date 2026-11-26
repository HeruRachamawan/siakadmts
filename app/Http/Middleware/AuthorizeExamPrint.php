<?php

namespace App\Http\Middleware;

use App\Models\ExamPackage;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthorizeExamPrint
{
    /**
     * Handle an incoming request for exam print documents.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Resolve User (via Web Session or Sanctum Bearer/Query Token)
        $user = Auth::user();

        if (! $user) {
            $token = $request->bearerToken() ?: $request->query('token');
            if ($token) {
                $accessToken = PersonalAccessToken::findToken($token);
                if ($accessToken && (! $accessToken->expires_at || $accessToken->expires_at->isFuture())) {
                    $user = $accessToken->tokenable;
                    Auth::setUser($user);
                }
            }
        }

        if (! $user) {
            abort(403, 'Akses ditolak. Silakan login ke akun SIAKAD Anda untuk mengakses dokumen ujian ini.');
        }

        // 2. Reject students strictly
        if ($user->role === 'student') {
            abort(403, 'Akses ditolak. Siswa dilarang keras mengakses dokumen naskah soal dan kunci jawaban.');
        }

        // 3. Allowed staff & administrative roles
        $managementRoles = ['admin', 'kurikulum', 'kepala_sekolah', 'operator'];
        if (in_array($user->role, $managementRoles, true)) {
            return $next($request);
        }

        // 4. If teacher, verify package ownership
        if ($user->role === 'teacher') {
            $packageId = $request->route('id');
            if ($packageId) {
                $package = ExamPackage::find($packageId);
                $teacher = $user->teacher;

                if (! $package || ! $teacher || (int) $package->teacher_id !== (int) $teacher->id) {
                    abort(403, 'Akses ditolak. Anda hanya memiliki izin untuk mengakses dokumen paket ujian milik Anda sendiri.');
                }
            }
            return $next($request);
        }

        abort(403, 'Akses ditolak. Akun Anda tidak memiliki wewenang untuk membuka dokumen ini.');
    }
}
