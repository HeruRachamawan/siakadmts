<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPromotionController extends BaseController
{
    /**
     * Mendapatkan daftar siswa dalam kelas untuk proses kenaikan kelas / kelulusan
     */
    public function getCandidates(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        $class = ClassRoom::with(['academicYear', 'homeroomTeacher'])->findOrFail($request->input('class_id'));

        // Ambil siswa resmi yang terdaftar di Rombel Utama
        $students = Student::where('class_id', $class->id)
            ->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereIn('status', ['aktif', 'tinggal_kelas']);
            })
            ->orderBy('full_name')
            ->get();

        $isFinalGrade = in_array((string)$class->grade_level, ['9', 'IX']);

        $candidateData = $students->map(function ($student) use ($isFinalGrade) {
            return [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'full_name' => $student->full_name,
                'gender' => $student->gender,
                'current_status' => $student->status ?? 'aktif',
                'photo_url' => $student->photo_url,
                'default_action' => $isFinalGrade ? 'lulus' : 'naik_kelas',
            ];
        });

        // Ambil kelas tujuan yang tersedia (termasuk kelas tingkat global 7, 8, 9 maupun rombel spesifik)
        $allClasses = ClassRoom::with('academicYear')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        return $this->success([
            'source_class' => [
                'id' => $class->id,
                'name' => $class->name,
                'grade_level' => $class->grade_level,
                'academic_year' => $class->academicYear?->name ?? '-',
                'is_final_grade' => $isFinalGrade,
            ],
            'candidates' => $candidateData,
            'available_classes' => $allClasses,
        ]);
    }

    /**
     * Memproses kenaikan kelas, tinggal kelas, atau kelulusan massal
     */
    public function processPromotions(Request $request)
    {
        $request->validate([
            'source_class_id' => 'required|exists:classes,id',
            'promotions' => 'required|array|min:1',
            'promotions.*.student_id' => 'required|exists:students,id',
            'promotions.*.action' => 'required|in:naik_kelas,tinggal_kelas,lulus,mutasi,keluar',
            'promotions.*.target_class_id' => 'nullable|exists:classes,id',
            'promotions.*.notes' => 'nullable|string|max:500',
            'graduation_year' => 'nullable|string|max:20',
            'graduation_date' => 'nullable|date',
            'target_academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $sourceClass = ClassRoom::findOrFail($request->input('source_class_id'));
        $targetAcademicYearId = $request->input('target_academic_year_id') 
            ?? AcademicYear::where('is_active', true)->value('id');

        $activeYear = AcademicYear::find($targetAcademicYearId);
        $graduationYear = $request->input('graduation_year') ?? ($activeYear?->year ?? date('Y') . '/' . (date('Y') + 1));
        $graduationDate = $request->input('graduation_date') ?? now()->toDateString();

        $processedCount = 0;
        $actionCounts = [
            'naik_kelas' => 0,
            'tinggal_kelas' => 0,
            'lulus' => 0,
            'mutasi' => 0,
            'keluar' => 0,
        ];

        DB::beginTransaction();
        try {
            foreach ($request->input('promotions') as $item) {
                $student = Student::findOrFail($item['student_id']);
                $action = $item['action'];
                $targetClassId = $item['target_class_id'] ?? null;
                $notes = $item['notes'] ?? null;

                $targetClass = $targetClassId ? ClassRoom::find($targetClassId) : null;
                $toClassName = $targetClass?->name;

                if ($action === 'lulus') {
                    $student->update([
                        'status' => 'lulus',
                        'class_id' => null,
                        'lokal_class_id' => null,
                        'graduation_year' => $graduationYear,
                        'graduation_date' => $graduationDate,
                        'alumni_notes' => $notes,
                    ]);
                    $toClassName = 'Alumni / Lulus (' . $graduationYear . ')';
                } elseif ($action === 'naik_kelas') {
                    if (!$targetClassId) {
                        throw new \Exception("Kelas tujuan wajib dipilih untuk siswa {$student->full_name} yang naik kelas.");
                    }
                    $student->update([
                        'status' => 'aktif',
                        'class_id' => $targetClassId,
                        'lokal_class_id' => null, // Bersihkan rombel lokal lama, siap disetting ulang di tahun ajaran baru
                    ]);
                } elseif ($action === 'tinggal_kelas') {
                    $student->update([
                        'status' => 'tinggal_kelas',
                        'class_id' => $targetClassId ?? $sourceClass->id,
                        'lokal_class_id' => null,
                    ]);
                    $toClassName = $targetClass?->name ?? ($sourceClass->name . ' (Tinggal Kelas)');
                } elseif (in_array($action, ['mutasi', 'keluar'])) {
                    $student->update([
                        'status' => $action,
                        'class_id' => null,
                        'lokal_class_id' => null,
                        'alumni_notes' => $notes,
                    ]);
                    $toClassName = ucfirst($action);
                }

                // Catat di Riwayat Kelas Siswa
                StudentClassHistory::create([
                    'student_id' => $student->id,
                    'class_id' => $targetClassId ?? $sourceClass->id,
                    'academic_year_id' => $targetAcademicYearId,
                    'action' => $action,
                    'from_class_name' => $sourceClass->name,
                    'to_class_name' => $toClassName,
                    'notes' => $notes,
                    'created_by' => auth()->id(),
                ]);

                $processedCount++;
                if (isset($actionCounts[$action])) {
                    $actionCounts[$action]++;
                }
            }

            DB::commit();

            return $this->success([
                'processed_count' => $processedCount,
                'breakdown' => $actionCounts,
            ], "Berhasil memproses {$processedCount} siswa (Naik: {$actionCounts['naik_kelas']}, Tinggal: {$actionCounts['tinggal_kelas']}, Lulus: {$actionCounts['lulus']}).");

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 422);
        }
    }
}
