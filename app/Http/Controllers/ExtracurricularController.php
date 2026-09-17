<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Extracurricular;
use App\Models\ExtracurricularGrade;
use App\Models\ExtracurricularMember;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtracurricularController extends Controller
{
    /**
     * Pastikan data default Pramuka (Wajib) ada di sistem jika belum ada.
     */
    private function ensureMandatoryPramukaExists(): Extracurricular
    {
        $pramuka = Extracurricular::where('is_mandatory', true)
            ->orWhere('name', 'like', '%Pramuka%')
            ->first();

        if (!$pramuka) {
            $pramuka = Extracurricular::create([
                'name' => 'Pendidikan Kepramukaan',
                'code' => 'PRAMUKA',
                'description' => 'Ekstrakurikuler Wajib Kepramukaan pembentukan karakter, kedisiplinan, dan kepemimpinan peserta didik madrasah.',
                'is_mandatory' => true,
                'schedule_day' => 'Jumat',
                'schedule_time' => '14:00 - 16:00',
                'is_active' => true,
            ]);
        }

        return $pramuka;
    }

    /**
     * List all extracurriculars (Admin, Kurikulum, Operator, Guru)
     */
    public function index(Request $request)
    {
        $this->ensureMandatoryPramukaExists();

        $user = $request->user();
        $isStaff = $user && in_array($user->role, ['admin', 'kurikulum', 'operator', 'kepala_sekolah']);
        $teacher = $user ? ($user->teacher ?: Teacher::where('user_id', $user->id)->first()) : null;

        $query = Extracurricular::with(['teacher'])->withCount('members');

        // Jika akun guru biasa/pembina (bukan staf admin), HANYA tampilkan ekskul yang dibina oleh guru tersebut
        if (!$isStaff && $teacher) {
            $query->where('teacher_id', $teacher->id);
        } elseif ($request->boolean('my_only') && $teacher) {
            $query->where('teacher_id', $teacher->id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $ekskuls = $query->orderBy('is_mandatory', 'desc')->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $ekskuls,
        ]);
    }

    /**
     * Create new extracurricular (Admin / Kurikulum / Operator)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_mandatory' => 'nullable|boolean',
            'schedule_day' => 'nullable|string|max:30',
            'schedule_time' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $ekskul = Extracurricular::create([
            'name' => $request->name,
            'code' => $request->code ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->name), 0, 8)),
            'description' => $request->description,
            'teacher_id' => $request->teacher_id,
            'is_mandatory' => (bool) $request->is_mandatory,
            'schedule_day' => $request->schedule_day,
            'schedule_time' => $request->schedule_time,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Ekstrakurikuler '{$ekskul->name}' berhasil ditambahkan!",
            'data' => $ekskul->load('teacher'),
        ], 201);
    }

    /**
     * Update extracurricular
     */
    public function update(Request $request, $id)
    {
        $ekskul = Extracurricular::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_mandatory' => 'nullable|boolean',
            'schedule_day' => 'nullable|string|max:30',
            'schedule_time' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $ekskul->update([
            'name' => $request->name,
            'code' => $request->code ?: $ekskul->code,
            'description' => $request->description,
            'teacher_id' => $request->teacher_id,
            'is_mandatory' => (bool) $request->is_mandatory,
            'schedule_day' => $request->schedule_day,
            'schedule_time' => $request->schedule_time,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $ekskul->is_active,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Ekstrakurikuler '{$ekskul->name}' berhasil diperbarui!",
            'data' => $ekskul->load('teacher'),
        ]);
    }

    /**
     * Delete extracurricular
     */
    public function destroy($id)
    {
        $ekskul = Extracurricular::findOrFail($id);
        $name = $ekskul->name;
        $ekskul->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Ekstrakurikuler '{$name}' berhasil dihapus!",
        ]);
    }

    /**
     * Get detail of an extracurricular with its members and options
     */
    public function show(Request $request, $id)
    {
        $ekskul = Extracurricular::with('teacher')->findOrFail($id);

        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::orderBy('id', 'desc')->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id);

        // Jika Pramuka Wajib, seluruh siswa aktif adalah peserta
        if ($ekskul->is_mandatory) {
            $studentsQuery = Student::where('status', 'aktif')->with(['classRoom']);
            if ($request->filled('class_id')) {
                $studentsQuery->where('class_id', $request->class_id);
            }
            $students = $studentsQuery->orderBy('full_name')->get();
        } else {
            // Ekskul pilihan: ambil dari extracurricular_members
            $members = ExtracurricularMember::where('extracurricular_id', $ekskul->id)
                ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                ->with(['student.classRoom'])
                ->get();
            $students = $members->pluck('student')->filter()->values();

            if ($request->filled('class_id')) {
                $students = $students->filter(fn($s) => $s->class_id == $request->class_id)->values();
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'extracurricular' => $ekskul,
                'students' => $students,
                'total_students' => count($students),
            ]
        ]);
    }

    /**
     * Get grading sheet for advisor
     */
    public function getGradingSheet(Request $request, $id)
    {
        $ekskul = Extracurricular::with('teacher')->findOrFail($id);

        $semester = $request->input('semester', 'ganjil');
        $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::orderBy('id', 'desc')->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id);
        $classId = $request->input('class_id');

        // Check advisor access (pembina ekskul ini, atau admin/kurikulum/operator)
        $user = $request->user();
        $isStaff = in_array($user->role, ['admin', 'kurikulum', 'operator', 'kepala_sekolah']);
        $teacher = $user->teacher ?: Teacher::where('user_id', $user->id)->first();
        $isAdvisor = $teacher && $ekskul->teacher_id == $teacher->id;

        if (!$isStaff && !$isAdvisor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menginput nilai pada ekstrakurikuler ini.',
            ], 403);
        }

        $classType = $request->input('class_type', 'utama'); // 'utama' | 'lokal'

        // Fetch eligible students
        if ($ekskul->is_mandatory) {
            $studentsQuery = Student::where('status', 'aktif')->with(['classRoom', 'lokalClassRoom']);
            if ($classId) {
                if ($classType === 'lokal') {
                    $studentsQuery->where('lokal_class_id', $classId);
                } else {
                    $studentsQuery->where('class_id', $classId);
                }
            }
            $students = $studentsQuery->orderBy('full_name')->get();
        } else {
            $membersQuery = ExtracurricularMember::where('extracurricular_id', $ekskul->id)
                ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
                ->with(['student.classRoom', 'student.lokalClassRoom']);

            $members = $membersQuery->get();
            $students = $members->pluck('student')->filter();

            if ($classId) {
                if ($classType === 'lokal') {
                    $students = $students->filter(fn($s) => $s->lokal_class_id == $classId);
                } else {
                    $students = $students->filter(fn($s) => $s->class_id == $classId);
                }
            }
            $students = $students->values();
        }

        $studentIds = $students->pluck('id');

        // Fetch existing grades
        $existingGrades = ExtracurricularGrade::where('extracurricular_id', $ekskul->id)
            ->where('academic_year_id', $yearId)
            ->where('semester', $semester)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        $sheetData = [];
        foreach ($students as $student) {
            $eg = $existingGrades->get($student->id);
            $sheetData[] = [
                'student_id' => $student->id,
                'full_name' => $student->full_name,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'gender' => $student->gender,
                'class_name' => $student->classRoom?->name ?? '-',
                'class_id' => $student->class_id,
                'lokal_class_name' => $student->lokalClassRoom?->name ?? null,
                'lokal_class_id' => $student->lokal_class_id,
                'grade' => $eg?->grade ?? null, // 'A', 'B', 'C' or null
                'description' => $eg?->description ?? '',
                'updated_at' => $eg?->updated_at?->translatedFormat('d M Y H:i'),
            ];
        }

        // Classes list with categorized groups (Utama & Lokal)
        $allClasses = ClassRoom::withCount(['students', 'lokalStudents'])->orderBy('grade_level')->orderBy('name')->get();

        $utamaClasses = [];
        $lokalClasses = [];

        foreach ($allClasses as $c) {
            $name = trim($c->name);
            $clean = strtolower(preg_replace('/^(kelas|kls)\s*/i', '', $name));

            // Standalone '7' and '8' are specifically for Jadwal Lokal
            $isStandaloneLokal = in_array($clean, ['7', '8']);
            // 9A / 9B / 9-A / 9-B dual function: regular rombel utama & kelompok lokal
            $isDualClass = in_array($clean, ['9a', '9-a', '9b', '9-b']);

            if ($isStandaloneLokal || $isDualClass) {
                $lokalClasses[] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'grade_level' => $c->grade_level,
                    'students_count' => $c->lokal_students_count > 0 ? $c->lokal_students_count : $c->students_count,
                    'type' => 'lokal',
                ];
            }

            // All regular classes (including 9-A, 9-B, 7A, 7B, 8A, 8B) are available as Rombel Utama
            if (!$isStandaloneLokal) {
                $utamaClasses[] = [
                    'id' => $c->id,
                    'name' => $c->name,
                    'grade_level' => $c->grade_level,
                    'students_count' => $c->students_count,
                    'type' => 'utama',
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'extracurricular' => $ekskul,
                'semester' => $semester,
                'academic_year' => $activeYear,
                'academic_years' => AcademicYear::orderBy('id', 'desc')->get(),
                'classes' => $allClasses,
                'utama_classes' => $utamaClasses,
                'lokal_classes' => $lokalClasses,
                'students' => $sheetData,
                'total_students' => count($sheetData),
            ]
        ]);
    }

    /**
     * Save grades for students in bulk
     */
    public function saveGrades(Request $request, $id)
    {
        $ekskul = Extracurricular::findOrFail($id);

        $request->validate([
            'semester' => 'required|string|in:ganjil,genap',
            'academic_year_id' => 'required|exists:academic_years,id',
            'grades' => 'required|array|min:1',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.grade' => 'nullable|in:A,B,C,null,""',
            'grades.*.description' => 'nullable|string',
        ]);

        $user = $request->user();
        $isStaff = in_array($user->role, ['admin', 'kurikulum', 'operator', 'kepala_sekolah']);
        $teacher = $user->teacher ?: Teacher::where('user_id', $user->id)->first();
        $isAdvisor = $teacher && $ekskul->teacher_id == $teacher->id;

        if (!$isStaff && !$isAdvisor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki wewenang untuk menyimpan nilai ekstrakurikuler ini.',
            ], 403);
        }

        $semester = $request->input('semester');
        $yearId = $request->input('academic_year_id');
        $gradesList = $request->input('grades');

        DB::beginTransaction();
        try {
            $savedCount = 0;
            $deletedCount = 0;

            foreach ($gradesList as $item) {
                $sId = $item['student_id'];
                $gradeVal = !empty($item['grade']) ? strtoupper(trim($item['grade'])) : null;
                $descVal = trim($item['description'] ?? '');

                if (empty($gradeVal)) {
                    // Jika dikosongkan, hapus penilaian lama
                    $deleted = ExtracurricularGrade::where('extracurricular_id', $ekskul->id)
                        ->where('student_id', $sId)
                        ->where('academic_year_id', $yearId)
                        ->where('semester', $semester)
                        ->delete();
                    if ($deleted) $deletedCount++;
                } else {
                    // Rekomendasi otomatis keterangan deskripsi jika kosong
                    if (empty($descVal)) {
                        $descVal = match ($gradeVal) {
                            'A' => "Sangat aktif, bersemangat, dan menunjukkan kecakapan istimewa dalam kegiatan {$ekskul->name}.",
                            'B' => "Aktif dan mampu mengikuti seluruh rangkaian kegiatan {$ekskul->name} dengan baik.",
                            'C' => "Cukup aktif dalam kegiatan {$ekskul->name}, perlu peningkatan kedisiplinan kehadiran.",
                            default => "Mengikuti kegiatan {$ekskul->name}."
                        };
                    }

                    ExtracurricularGrade::updateOrCreate(
                        [
                            'extracurricular_id' => $ekskul->id,
                            'student_id' => $sId,
                            'academic_year_id' => $yearId,
                            'semester' => $semester,
                        ],
                        [
                            'grade' => $gradeVal,
                            'description' => $descVal,
                            'graded_by_teacher_id' => $teacher?->id,
                        ]
                    );
                    $savedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menyimpan {$savedCount} nilai ekstrakurikuler {$ekskul->name}!",
                'saved_count' => $savedCount,
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add member students to an elective extracurricular
     */
    public function addMembers(Request $request, $id)
    {
        $ekskul = Extracurricular::findOrFail($id);

        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|exists:students,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id);

        $count = 0;
        foreach ($request->student_ids as $sId) {
            $m = ExtracurricularMember::firstOrCreate([
                'extracurricular_id' => $ekskul->id,
                'student_id' => $sId,
                'academic_year_id' => $yearId,
            ], [
                'joined_date' => now(),
            ]);
            if ($m->wasRecentlyCreated) $count++;
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menambahkan {$count} siswa ke ekstrakurikuler {$ekskul->name}!",
        ]);
    }

    /**
     * Remove member student from extracurricular
     */
    public function removeMember($id, $studentId)
    {
        $deleted = ExtracurricularMember::where('extracurricular_id', $id)
            ->where('student_id', $studentId)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Siswa berhasil dikeluarkan dari keanggotaan ekstrakurikuler.',
        ]);
    }
}
