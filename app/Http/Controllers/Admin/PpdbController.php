<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\PpdbApplicant;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PpdbController extends BaseController
{
    /**
     * Check if authenticated user is admin or PPDB committee teacher
     */
    protected function authorizeAccess()
    {
        $user = auth()->user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'teacher') {
            $teacher = $user->teacher;
            if ($teacher && $teacher->is_ppdb_committee) {
                return true;
            }
        }

        abort(403, 'Akses khusus Admin dan Panitia PPDB');
    }

    /**
     * List all PPDB applicants with stats and filters
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $query = PpdbApplicant::with(['academicYear', 'verifier', 'enrolledClass', 'enrolledStudent'])
            ->latest('id');

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by Academic Year
        if ($request->filled('academic_year_id') && $request->academic_year_id !== 'all') {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Search Keyword
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', "%{$s}%")
                    ->orWhere('registration_number', 'like', "%{$s}%")
                    ->orWhere('nisn', 'like', "%{$s}%")
                    ->orWhere('previous_school', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('father_name', 'like', "%{$s}%")
                    ->orWhere('mother_name', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $applicants = $query->paginate($perPage);

        // Stats summary
        $stats = [
            'total' => PpdbApplicant::count(),
            'pending' => PpdbApplicant::where('status', 'pending')->count(),
            'verified' => PpdbApplicant::where('status', 'verified')->count(),
            'accepted' => PpdbApplicant::where('status', 'accepted')->count(),
            'rejected' => PpdbApplicant::where('status', 'rejected')->count(),
            'enrolled' => PpdbApplicant::where('status', 'enrolled')->count(),
        ];

        $academicYears = AcademicYear::orderByDesc('year')->get();
        $classrooms = ClassRoom::orderBy('name')->get();

        return $this->success([
            'applicants' => $applicants,
            'stats' => $stats,
            'academic_years' => $academicYears,
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Show applicant detail
     */
    public function show($id)
    {
        $this->authorizeAccess();

        $applicant = PpdbApplicant::with(['academicYear', 'verifier', 'enrolledClass', 'enrolledStudent.user'])
            ->findOrFail($id);

        return $this->success($applicant);
    }

    /**
     * Process applicant review (Status, Test Score, Notes)
     */
    public function process(Request $request, $id)
    {
        $this->authorizeAccess();

        $applicant = PpdbApplicant::findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:pending,verified,accepted,rejected,enrolled'],
            'test_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $applicant->update([
            'status' => $request->status,
            'test_score' => $request->filled('test_score') ? $request->test_score : $applicant->test_score,
            'notes' => $request->notes,
            'verified_by' => auth()->id(),
        ]);

        return $this->success($applicant->fresh(['academicYear', 'verifier', 'enrolledClass']), 'Status calon siswa berhasil diperbarui!');
    }

    /**
     * Enroll accepted applicant into active SIAKAD Student & Class
     */
    public function enroll(Request $request, $id)
    {
        $this->authorizeAccess();

        $applicant = PpdbApplicant::findOrFail($id);

        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'nis' => ['nullable', 'string', 'max:20'],
            'nisn' => ['nullable', 'string', 'max:20'],
        ]);

        if ($applicant->status === 'enrolled' && $applicant->enrolled_student_id) {
            return $this->error('Calon siswa ini sudah didaftarkan sebelumnya sebagai siswa aktif.', 422);
        }

        DB::beginTransaction();
        try {
            $nisn = trim($request->nisn ?: $applicant->nisn);
            $nis = trim($request->nis ?: $applicant->nisn);

            if (empty($nisn)) {
                $nisn = 'S' . date('Y') . sprintf('%04d', $applicant->id);
            }
            if (empty($nis)) {
                $nis = $nisn;
            }

            // Ensure username uniqueness
            $username = $nisn;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $nisn . '_' . $counter;
                $counter++;
            }

            // Create user account for student
            $user = User::create([
                'name' => $applicant->full_name,
                'username' => $username,
                'email' => Str::slug($applicant->full_name) . '.' . $applicant->id . '@siakad.sch.id',
                'password' => Hash::make($nisn), // Password default uses NISN
                'role' => 'student',
            ]);

            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'class_id' => $request->class_id,
                'full_name' => $applicant->full_name,
                'nisn' => $nisn,
                'nis' => $nis,
                'nik' => $applicant->nik,
                'gender' => $applicant->gender,
                'birth_place' => $applicant->birth_place,
                'birth_date' => $applicant->birth_date,
                'address' => $applicant->address,
                'parent_phone' => $applicant->phone ?: $applicant->father_phone ?: $applicant->mother_phone,
                'previous_school' => $applicant->previous_school,
                'father_name' => $applicant->father_name,
                'father_job' => $applicant->father_job,
                'father_phone' => $applicant->father_phone,
                'mother_name' => $applicant->mother_name,
                'mother_job' => $applicant->mother_job,
                'mother_phone' => $applicant->mother_phone,
                'guardian_name' => $applicant->guardian_name,
                'guardian_phone' => $applicant->guardian_phone,
                'photo' => $applicant->photo,
            ]);

            // Update applicant state
            $applicant->update([
                'status' => 'enrolled',
                'enrolled_student_id' => $student->id,
                'enrolled_class_id' => $request->class_id,
                'verified_by' => auth()->id(),
            ]);

            DB::commit();

            return $this->success([
                'student' => $student->load('classRoom'),
                'applicant' => $applicant->fresh(['enrolledClass', 'enrolledStudent']),
            ], "Siswa {$applicant->full_name} berhasil didaftarkan ke kelas aktif!");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Gagal mendaftarkan calon siswa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete applicant
     */
    public function destroy($id)
    {
        $this->authorizeAccess();

        $applicant = PpdbApplicant::findOrFail($id);

        if ($applicant->photo && Storage::disk('public')->exists($applicant->photo)) {
            Storage::disk('public')->delete($applicant->photo);
        }
        if ($applicant->family_card_file && Storage::disk('public')->exists($applicant->family_card_file)) {
            Storage::disk('public')->delete($applicant->family_card_file);
        }
        if ($applicant->certificate_file && Storage::disk('public')->exists($applicant->certificate_file)) {
            Storage::disk('public')->delete($applicant->certificate_file);
        }

        $applicant->delete();

        return $this->success(null, 'Data calon siswa berhasil dihapus.');
    }

    /**
     * Get list of teachers and their committee status
     */
    public function getTeachersCommittee()
    {
        $this->authorizeAccess();

        $teachers = Teacher::with('user')->orderBy('full_name')->get();
        return $this->success($teachers);
    }

    /**
     * Toggle teacher as PPDB Committee (Admin only)
     */
    public function toggleTeacherCommittee(Request $request, $teacherId)
    {
        if (auth()->user()->role !== 'admin') {
            return $this->error('Hanya Administrator yang dapat mengatur panitia PPDB.', 403);
        }

        $teacher = Teacher::findOrFail($teacherId);
        $teacher->is_ppdb_committee = (bool) $request->input('is_ppdb_committee', ! $teacher->is_ppdb_committee);
        $teacher->save();

        $status = $teacher->is_ppdb_committee ? 'ditetapkan sebagai Panitia PPDB' : 'dinonaktifkan dari Panitia PPDB';
        return $this->success($teacher, "Guru {$teacher->full_name} berhasil {$status}.");
    }

    /**
     * Get PPDB schedule, status, and quota settings
     */
    public function getSettings()
    {
        $this->authorizeAccess();

        $status = \App\Http\Controllers\Public\PpdbPublicController::getPpdbOpenStatus();
        return $this->success($status);
    }

    /**
     * Update PPDB schedule, status, and quota settings
     */
    public function updateSettings(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'ppdb_is_open' => ['required', 'boolean'],
            'ppdb_batch_name' => ['nullable', 'string', 'max:100'],
            'ppdb_start_date' => ['nullable', 'date'],
            'ppdb_end_date' => ['nullable', 'date'],
            'ppdb_quota' => ['nullable', 'integer', 'min:0'],
            'ppdb_closed_message' => ['nullable', 'string'],
        ]);

        $keys = [
            'ppdb_is_open' => $request->boolean('ppdb_is_open') ? '1' : '0',
            'ppdb_batch_name' => $request->ppdb_batch_name ?: 'Gelombang 1',
            'ppdb_start_date' => $request->ppdb_start_date ?: '',
            'ppdb_end_date' => $request->ppdb_end_date ?: '',
            'ppdb_quota' => $request->filled('ppdb_quota') ? (string)$request->ppdb_quota : '',
            'ppdb_closed_message' => $request->ppdb_closed_message ?: '',
        ];

        foreach ($keys as $k => $v) {
            \App\Models\Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        }

        $freshStatus = \App\Http\Controllers\Public\PpdbPublicController::getPpdbOpenStatus();
        return $this->success($freshStatus, 'Pengaturan periode dan status PPDB berhasil disimpan!');
    }
}
