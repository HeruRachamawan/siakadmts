<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Http\Request;

class AlumniController extends BaseController
{
    /**
     * Menampilkan daftar buku induk alumni
     */
    public function index(Request $request)
    {
        $query = Student::where('status', 'lulus')
            ->with(['classHistories']);

        if ($request->filled('graduation_year') && $request->input('graduation_year') !== 'all') {
            $query->where('graduation_year', $request->input('graduation_year'));
        }

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%")
                    ->orWhere('certificate_number', 'like', "%{$q}%")
                    ->orWhere('skl_number', 'like', "%{$q}%");
            });
        }

        // Statistik alumni
        $totalAlumni = Student::where('status', 'lulus')->count();
        $totalMale = Student::where('status', 'lulus')->where('gender', 'L')->count();
        $totalFemale = Student::where('status', 'lulus')->where('gender', 'P')->count();
        $graduationYears = Student::where('status', 'lulus')
            ->whereNotNull('graduation_year')
            ->distinct()
            ->orderBy('graduation_year', 'desc')
            ->pluck('graduation_year');

        $perPage = (int) $request->get('per_page', 15);
        $perPage = max(1, min($perPage, 500));

        $alumni = $query->orderBy('graduation_year', 'desc')
            ->orderBy('full_name', 'asc')
            ->paginate($perPage);

        return $this->success([
            'data' => $alumni->items(),
            'pagination' => [
                'current_page' => $alumni->currentPage(),
                'last_page' => $alumni->lastPage(),
                'per_page' => $alumni->perPage(),
                'total' => $alumni->total(),
            ],
            'statistics' => [
                'total' => $totalAlumni,
                'male' => $totalMale,
                'female' => $totalFemale,
                'graduation_years' => $graduationYears,
            ],
        ]);
    }

    /**
     * Memperbarui informasi alumni (nomor ijazah, SKL, no telp, catatan/sekolah lanjutan)
     */
    public function update(Request $request, $id)
    {
        $student = Student::where('status', 'lulus')->findOrFail($id);

        $request->validate([
            'graduation_year' => 'nullable|string|max:20',
            'graduation_date' => 'nullable|date',
            'certificate_number' => 'nullable|string|max:100',
            'skl_number' => 'nullable|string|max:100',
            'parent_phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'alumni_notes' => 'nullable|string|max:1000',
        ]);

        $student->update($request->only([
            'graduation_year',
            'graduation_date',
            'certificate_number',
            'skl_number',
            'parent_phone',
            'address',
            'alumni_notes',
        ]));

        return $this->success($student, 'Data alumni berhasil diperbarui');
    }

    /**
     * Membatalkan status alumni dan mengembalikan siswa ke status aktif (jika salah lulus)
     */
    public function revertToActive(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $student = Student::where('status', 'lulus')->findOrFail($id);
        $targetClass = ClassRoom::findOrFail($request->input('class_id'));

        $student->update([
            'status' => 'aktif',
            'class_id' => $targetClass->id,
            'graduation_year' => null,
            'graduation_date' => null,
        ]);

        StudentClassHistory::create([
            'student_id' => $student->id,
            'class_id' => $targetClass->id,
            'action' => 'pindah_kelas',
            'from_class_name' => 'Alumni (Dibatalkan)',
            'to_class_name' => $targetClass->name,
            'notes' => 'Status alumni dibatalkan dan dikembalikan ke siswa aktif.',
            'created_by' => auth()->id(),
        ]);

        return $this->success($student, "Siswa {$student->full_name} berhasil dikembalikan ke Kelas {$targetClass->name}");
    }
}
