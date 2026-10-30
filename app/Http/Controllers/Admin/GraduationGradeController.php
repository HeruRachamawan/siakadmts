<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClassRoom;
use App\Models\GraduationGrade;
use App\Models\GraduationSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\User;
use App\Http\Controllers\AstsReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class GraduationGradeController extends BaseController
{
    /**
     * Get graduation weight settings and subject reference list.
     */
    public function getSettings()
    {
        $setting = GraduationSetting::getActiveSettings();
        $allSubjects = $this->sortSubjectsAccordingToStructure(Subject::all(['id', 'name', 'code']));

        $activeSubjectIds = is_array($setting->active_subject_ids) ? array_map('intval', $setting->active_subject_ids) : null;
        $activeSubjects = $activeSubjectIds !== null
            ? $allSubjects->filter(fn($s) => in_array((int)$s->id, $activeSubjectIds))->values()
            : $allSubjects;

        return $this->success([
            'setting' => $setting,
            'subjects' => $activeSubjects,
            'all_subjects' => $allSubjects,
        ]);
    }

    /**
     * Update graduation weight and exam configuration.
     */
    public function saveSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_weight' => 'required|numeric|min:0|max:100',
            'exam_weight' => 'required|numeric|min:0|max:100',
            'theory_weight_default' => 'required|numeric|min:0|max:100',
            'practice_weight_default' => 'required|numeric|min:0|max:100',
            'active_subject_ids' => 'nullable|array',
            'active_subject_ids.*' => 'integer|exists:subjects,id',
            'practical_subject_ids' => 'nullable|array',
            'practical_subject_ids.*' => 'integer|exists:subjects,id',
            'custom_subject_weights' => 'nullable|array',
            'date_place' => 'nullable|string|max:100',
            'graduation_date' => 'nullable|date',
            'certificate_number_prefix' => 'nullable|string|max:150',
            'principal_name' => 'nullable|string|max:150',
            'principal_nip' => 'nullable|string|max:50',
            'nsm' => 'nullable|string|max:50',
            'npsn' => 'nullable|string|max:50',
            'show_student_photo' => 'nullable|boolean',
            'academic_year' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi pengaturan gagal', 422, $validator->errors());
        }

        $validated = $validator->validated();

        $setting = GraduationSetting::getActiveSettings();
        $setting->update([
            'report_weight' => $validated['report_weight'],
            'exam_weight' => $validated['exam_weight'],
            'theory_weight_default' => $validated['theory_weight_default'],
            'practice_weight_default' => $validated['practice_weight_default'],
            'active_subject_ids' => $validated['active_subject_ids'] ?? null,
            'practical_subject_ids' => $validated['practical_subject_ids'] ?? [],
            'custom_subject_weights' => $validated['custom_subject_weights'] ?? [],
            'date_place' => $validated['date_place'] ?? 'Bogor',
            'graduation_date' => $validated['graduation_date'] ?? now()->toDateString(),
            'certificate_number_prefix' => $validated['certificate_number_prefix'] ?? 'MTs.10.01/PP.01.1/',
            'principal_name' => $validated['principal_name'] ?? null,
            'principal_nip' => $validated['principal_nip'] ?? null,
            'nsm' => $validated['nsm'] ?? '121232010012',
            'npsn' => $validated['npsn'] ?? '20277259',
            'show_student_photo' => isset($validated['show_student_photo']) ? (bool)$validated['show_student_photo'] : true,
            'academic_year' => $validated['academic_year'] ?? '2026/2027',
        ]);

        // Auto-recalculate all existing graduation grades with new weights in chunked transaction
        DB::transaction(function () use ($setting) {
            GraduationGrade::chunk(200, function ($grades) use ($setting) {
                foreach ($grades as $g) {
                    $g->recalculateScores($setting);
                    $g->save();
                }
            });
        });

        return $this->success($setting, 'Pengaturan bobot & mapel aktif kelulusan berhasil diperbarui.');
    }

    /**
     * Retrieve Class 9 students and their 6-semester + exam recap grades.
     */
    public function index(Request $request)
    {
        // 1. Identify Grade 9 classes
        $grade9Classes = ClassRoom::where(function ($q) {
            $q->where('grade_level', '9')
              ->orWhere('name', 'like', '9%')
              ->orWhere('name', 'like', 'IX%');
        })->orderBy('name')->get(['id', 'name', 'grade_level']);

        $classIds = $grade9Classes->pluck('id')->toArray();

        // Filter by specific class if selected
        $selectedClassId = $request->query('class_id');
        if ($selectedClassId && in_array((int)$selectedClassId, $classIds)) {
            $targetClassIds = [(int)$selectedClassId];
        } else {
            $targetClassIds = $classIds;
        }

        // 2. Fetch Grade 9 Students
        $studentsQuery = Student::where(function ($q) use ($targetClassIds) {
            $q->whereIn('class_id', $targetClassIds)
              ->orWhereIn('lokal_class_id', $targetClassIds);
        })->with(['classRoom:id,name', 'lokalClassRoom:id,name'])
          ->orderBy('full_name');

        $students = $studentsQuery->get();
        $studentIds = $students->pluck('id')->toArray();

        // 3. Settings & Active Subjects list (Diurutkan sesuai standar Kurikulum MTs Kemenag)
        $setting = GraduationSetting::getActiveSettings();
        $allSubjects = $this->sortSubjectsAccordingToStructure(Subject::all(['id', 'name', 'code']));

        $activeSubjectIds = is_array($setting->active_subject_ids) ? array_map('intval', $setting->active_subject_ids) : null;
        $activeSubjects = $activeSubjectIds !== null
            ? $allSubjects->filter(fn($s) => in_array((int)$s->id, $activeSubjectIds))->values()
            : $allSubjects;

        // 4. Grades
        $gradesQuery = GraduationGrade::whereIn('student_id', $studentIds);
        if ($request->filled('subject_id')) {
            $gradesQuery->where('subject_id', (int)$request->query('subject_id'));
        }
        $grades = $gradesQuery->get();

        // 5. School Identity
        $schoolSetting = AstsReportController::getOfficialSchoolSetting();

        return $this->success([
            'classes' => $grade9Classes,
            'subjects' => $activeSubjects,
            'all_subjects' => $allSubjects,
            'students' => $students,
            'grades' => $grades,
            'setting' => $setting,
            'school_setting' => $schoolSetting,
        ]);
    }

    /**
     * Save/update semester and exam scores for students.
     */
    public function saveGrades(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|integer|exists:students,id',
            'grades.*.subject_id' => 'required|integer|exists:subjects,id',
            'grades.*.sem_1' => 'nullable|numeric|min:0|max:100',
            'grades.*.sem_2' => 'nullable|numeric|min:0|max:100',
            'grades.*.sem_3' => 'nullable|numeric|min:0|max:100',
            'grades.*.sem_4' => 'nullable|numeric|min:0|max:100',
            'grades.*.sem_5' => 'nullable|numeric|min:0|max:100',
            'grades.*.sem_6' => 'nullable|numeric|min:0|max:100',
            'grades.*.exam_theory' => 'nullable|numeric|min:0|max:100',
            'grades.*.exam_practice' => 'nullable|numeric|min:0|max:100',
            'grades.*.final_exam_score' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error('Format data nilai tidak valid', 422, $validator->errors());
        }

        $items = $request->input('grades');
        $setting = GraduationSetting::getActiveSettings();
        $savedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $record = GraduationGrade::firstOrNew([
                    'student_id' => (int)$item['student_id'],
                    'subject_id' => (int)$item['subject_id'],
                ]);

                $record->sem_1 = isset($item['sem_1']) && $item['sem_1'] !== '' ? floatval($item['sem_1']) : null;
                $record->sem_2 = isset($item['sem_2']) && $item['sem_2'] !== '' ? floatval($item['sem_2']) : null;
                $record->sem_3 = isset($item['sem_3']) && $item['sem_3'] !== '' ? floatval($item['sem_3']) : null;
                $record->sem_4 = isset($item['sem_4']) && $item['sem_4'] !== '' ? floatval($item['sem_4']) : null;
                $record->sem_5 = isset($item['sem_5']) && $item['sem_5'] !== '' ? floatval($item['sem_5']) : null;
                $record->sem_6 = isset($item['sem_6']) && $item['sem_6'] !== '' ? floatval($item['sem_6']) : null;
                $record->exam_theory = isset($item['exam_theory']) && $item['exam_theory'] !== '' ? floatval($item['exam_theory']) : null;
                $record->exam_practice = isset($item['exam_practice']) && $item['exam_practice'] !== '' ? floatval($item['exam_practice']) : null;

                if (array_key_exists('final_exam_score', $item)) {
                    $record->final_exam_score = ($item['final_exam_score'] !== '' && $item['final_exam_score'] !== null) ? floatval($item['final_exam_score']) : null;
                }

                $record->recalculateScores($setting);
                $record->save();
                $savedCount++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Gagal menyimpan nilai: ' . $e->getMessage(), 500);
        }

        return $this->success([
            'saved_count' => $savedCount,
        ], "Berhasil menyimpan {$savedCount} baris nilai rekap ijazah.");
    }

    /**
     * Auto-pull grades from existing SIAKAD grades database for Class 9 students.
     */
    public function autoPull(Request $request)
    {
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');

        // Target students
        $studentsQuery = Student::query();
        if ($classId) {
            $studentsQuery->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhere('lokal_class_id', $classId);
            });
        } else {
            $grade9Classes = ClassRoom::where('grade_level', '9')
                ->orWhere('name', 'like', '9%')
                ->orWhere('name', 'like', 'IX%')
                ->pluck('id');
            $studentsQuery->where(function ($q) use ($grade9Classes) {
                $q->whereIn('class_id', $grade9Classes)
                  ->orWhereIn('lokal_class_id', $grade9Classes);
            });
        }

        $students = $studentsQuery->get(['id', 'full_name']);
        if ($students->isEmpty()) {
            return $this->error('Tidak ada siswa kelas 9 yang ditemukan.', 404);
        }

        $studentIds = $students->pluck('id')->toArray();
        $setting = GraduationSetting::getActiveSettings();

        // Fetch subjects
        $subjectsQuery = Subject::query();
        if ($subjectId) {
            $subjectsQuery->where('id', $subjectId);
        }
        $subjects = $subjectsQuery->get(['id', 'name']);

        $pulledCount = 0;

        DB::beginTransaction();
        try {
            foreach ($studentIds as $sId) {
                foreach ($subjects as $sub) {
                    $record = GraduationGrade::firstOrNew([
                        'student_id' => $sId,
                        'subject_id' => $sub->id,
                    ]);

                    // Pull final scores from Grade table if available
                    // By joining academic years to determine semester
                    $existingGrades = Grade::with('academicYear')
                        ->where('student_id', $sId)
                        ->where('subject_id', $sub->id)
                        ->get();

                    foreach ($existingGrades as $eg) {
                        $sem = strtolower($eg->academicYear?->semester ?? '');
                        $score = $eg->final_score ?? $eg->calculateFinalScore();
                        if ($score > 0) {
                            // Map to semester 5 or 6 (since they are currently in grade 9)
                            if ($sem === 'ganjil' && empty($record->sem_5)) {
                                $record->sem_5 = $score;
                            } elseif ($sem === 'genap' && empty($record->sem_6)) {
                                $record->sem_6 = $score;
                            }
                        }
                    }

                    $record->recalculateScores($setting);
                    $record->save();
                    $pulledCount++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Gagal menarik nilai otomatis: ' . $e->getMessage(), 500);
        }

        return $this->success([
            'pulled_count' => $pulledCount,
        ], "Sinkronisasi berhasil! Berhasil memproses {$pulledCount} data nilai siswa.");
    }

    /**
     * Export graduation recap to standard PDUM / Excel-compatible format.
     */
    public function exportPdum(Request $request)
    {
        $classId = $request->query('class_id');
        $grade9Classes = ClassRoom::where('grade_level', '9')
            ->orWhere('name', 'like', '9%')
            ->orWhere('name', 'like', 'IX%')
            ->pluck('id');

        $studentsQuery = Student::with(['classRoom:id,name', 'lokalClassRoom:id,name']);
        if ($classId) {
            $studentsQuery->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)->orWhere('lokal_class_id', $classId);
            });
        } else {
            $studentsQuery->where(function ($q) use ($grade9Classes) {
                $q->whereIn('class_id', $grade9Classes)
                  ->orWhereIn('lokal_class_id', $grade9Classes);
            });
        }

        $students = $studentsQuery->orderBy('full_name')->get();
        $setting = GraduationSetting::getActiveSettings();
        $allSubjects = $this->sortSubjectsAccordingToStructure(Subject::all());
        $activeSubjectIds = is_array($setting->active_subject_ids) ? array_map('intval', $setting->active_subject_ids) : null;
        $subjects = $activeSubjectIds !== null
            ? $allSubjects->filter(fn($s) => in_array((int)$s->id, $activeSubjectIds))->values()
            : $allSubjects;
        $grades = GraduationGrade::whereIn('student_id', $studentIds)->get();

        // Organize grades map: [student_id][subject_id] => GraduationGrade
        $gradeMap = [];
        foreach ($grades as $g) {
            $gradeMap[$g->student_id][$g->subject_id] = $g;
        }

        $filename = 'rekap_nilai_ijazah_pdum_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($students, $subjects, $gradeMap, $setting) {
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header line
            $headerRow = [
                'NO',
                'NISN',
                'NIS',
                'NAMA SISWA',
                'KELAS',
                'MATA PELAJARAN',
                'SEM 1',
                'SEM 2',
                'SEM 3',
                'SEM 4',
                'SEM 5',
                'SEM 6',
                'RATA-RATA RAPOR',
                'UJIAN TULIS',
                'UJIAN PRAKTIK',
                'NILAI UJIAN',
                'NILAI IJAZAH',
            ];
            fputcsv($output, $headerRow);

            $no = 1;
            foreach ($students as $stu) {
                $className = $stu->classRoom?->name ?? ($stu->lokalClassRoom?->name ?? '-');
                foreach ($subjects as $sub) {
                    $g = $gradeMap[$stu->id][$sub->id] ?? null;

                    fputcsv($output, [
                        $no++,
                        $stu->nisn ?? '-',
                        $stu->nis ?? '-',
                        $stu->full_name,
                        $className,
                        $sub->name,
                        $g?->sem_1 ?? '',
                        $g?->sem_2 ?? '',
                        $g?->sem_3 ?? '',
                        $g?->sem_4 ?? '',
                        $g?->sem_5 ?? '',
                        $g?->sem_6 ?? '',
                        $g?->avg_semester ?? '',
                        $g?->exam_theory ?? '',
                        $g?->exam_practice ?? '',
                        $g?->final_exam_score ?? '',
                        $g?->final_grade ?? '',
                    ]);
                }
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tambah data siswa khusus jenjang Kelas 9.
     */
    public function storeStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'nisn' => 'required|string|max:50|unique:students,nisn',
            'nis' => 'required|string|max:50|unique:students,nis',
            'gender' => 'required|in:L,P',
            'class_id' => 'required|integer|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi data siswa gagal', 422, $validator->errors());
        }

        $cls = ClassRoom::find($request->class_id);
        if (!$cls || ($cls->grade_level !== '9' && !str_starts_with($cls->name, '9') && !str_starts_with($cls->name, 'IX'))) {
            return $this->error('Rombel yang dipilih harus jenjang Kelas 9.', 422);
        }

        DB::beginTransaction();
        try {
            // Auto generate user account for student
            $username = $request->nisn;
            $originalUsername = $username;
            $i = 1;
            while (User::where('username', $username)->exists()) {
                $username = $originalUsername . '_' . $i++;
            }

            $email = $request->nisn . '@student.example.com';
            $originalEmail = $email;
            $j = 1;
            while (User::where('email', $email)->exists()) {
                $email = $originalEmail . '_' . $j++;
            }

            $user = User::create([
                'name' => $request->full_name,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($request->nisn),
                'role' => 'student',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'class_id' => $cls->id,
                'nisn' => $request->nisn,
                'nis' => $request->nis,
                'full_name' => $request->full_name,
                'gender' => $request->gender,
            ]);

            DB::commit();

            $student->load(['classRoom:id,name', 'lokalClassRoom:id,name']);

            return $this->success($student, "Siswa {$student->full_name} berhasil ditambahkan ke {$cls->name}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Gagal menambahkan siswa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Hapus data siswa kelas 9 dari sistem.
     */
    public function deleteStudent($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return $this->error('Data siswa tidak ditemukan.', 404);
        }

        DB::beginTransaction();
        try {
            GraduationGrade::where('student_id', $student->id)->delete();
            if ($student->user) {
                $student->user->delete();
            }
            $student->delete();
            DB::commit();

            return $this->success(null, "Data siswa berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Gagal menghapus siswa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper to classify and sort subjects according to official Madrasah Tsanawiyah curriculum structure.
     */
    private function sortSubjectsAccordingToStructure($subjects)
    {
        return $subjects->sort(function ($a, $b) {
            $weightA = $this->getSubjectWeight($a->name);
            $weightB = $this->getSubjectWeight($b->name);
            if ($weightA === $weightB) {
                return strcmp($a->name, $b->name);
            }
            return $weightA <=> $weightB;
        })->values();
    }

    /**
     * Helper to compute order weight for subjects.
     */
    private function getSubjectWeight(string $name): int
    {
        $lower = strtolower($name);

        // 3. Muatan Lokal (Mulok) - Prioritaskan cek BTQ / Baca Tulis Al-Qur'an lebih dulu
        if (str_contains($lower, 'baca tulis') || str_contains($lower, 'btq') || str_contains($lower, 'bta') || str_contains($lower, 'tahfidz')) return 220;
        if (str_contains($lower, 'sunda') || str_contains($lower, 'jawa') || str_contains($lower, 'daerah') || str_contains($lower, 'mulok')) return 210;

        // 1. Kelompok Wajib A - Pendidikan Agama Islam (PAI)
        if (str_contains($lower, 'hadis') || str_contains($lower, 'hadits') || (str_contains($lower, 'qur') && !str_contains($lower, 'baca'))) return 10;
        if (str_contains($lower, 'akidah') || str_contains($lower, 'akhlak')) return 11;
        if (str_contains($lower, 'fikih') || str_contains($lower, 'fiqih')) return 12;
        if (str_contains($lower, 'sejarah kebudayaan') || str_contains($lower, 'ski')) return 13;

        // Kelompok Wajib A - Mata Pelajaran Umum
        if (str_contains($lower, 'pancasila') || str_contains($lower, 'pkn') || str_contains($lower, 'kewarganegaraan')) return 20;
        if (str_contains($lower, 'bahasa indonesia') || $lower === 'indonesia') return 30;
        if (str_contains($lower, 'bahasa arab') || $lower === 'arab') return 40;
        if (str_contains($lower, 'matematika') || str_contains($lower, 'mtk')) return 50;
        if (str_contains($lower, 'alam') || str_contains($lower, 'ipa')) return 60;
        if (str_contains($lower, 'sosial') || str_contains($lower, 'ips')) return 70;
        if (str_contains($lower, 'inggris') || str_contains($lower, 'english')) return 80;

        // 2. Kelompok Wajib B
        if (str_contains($lower, 'seni') || str_contains($lower, 'prakarya') || str_contains($lower, 'sbdp')) return 110;
        if (str_contains($lower, 'jasmani') || str_contains($lower, 'olahraga') || str_contains($lower, 'pjok') || str_contains($lower, 'penjas')) return 120;
        if (str_contains($lower, 'informatika') || str_contains($lower, 'komputer') || str_contains($lower, 'tik')) return 130;

        return 300;
    }

    /**
     * Batch generate serial numbers for Certificate (Ijazah) and SKL.
     */
    public function generateSerialNumbers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'nullable|integer|exists:classes,id',
            'cert_prefix' => 'nullable|string|max:100',
            'cert_start_number' => 'required|integer|min:1',
            'cert_digits' => 'required|integer|min:1|max:8',
            'skl_prefix' => 'nullable|string|max:100',
            'skl_start_number' => 'nullable|integer|min:1',
            'order_by' => 'required|in:full_name,nis,nisn',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $classId = $request->input('class_id');
        $certPrefix = $request->input('cert_prefix', 'MTs.10.01/PP.01.1/');
        $certStart = (int) $request->input('cert_start_number', 1);
        $certDigits = (int) $request->input('cert_digits', 4);
        $sklPrefix = $request->input('skl_prefix', 'MTs.AH/PP.01.1/SKL/' . date('Y') . '/');
        $sklStart = (int) $request->input('skl_start_number', 1);
        $orderBy = $request->input('order_by', 'full_name');

        // Identify Grade 9 classes
        $grade9Classes = ClassRoom::where(function ($q) {
            $q->where('grade_level', '9')
              ->orWhere('name', 'like', '9%')
              ->orWhere('name', 'like', 'IX%');
        })->pluck('id')->toArray();

        $query = Student::query();
        if ($classId) {
            $query->where(function ($q) use ($classId) {
                $q->where('class_id', $classId)
                  ->orWhere('lokal_class_id', $classId);
            });
        } else {
            $query->where(function ($q) use ($grade9Classes) {
                $q->whereIn('class_id', $grade9Classes)
                  ->orWhereIn('lokal_class_id', $grade9Classes);
            });
        }

        $students = $query->orderBy($orderBy)->get();

        DB::beginTransaction();
        try {
            $currentCert = $certStart;
            $currentSkl = $sklStart;

            foreach ($students as $stu) {
                $formattedCertNo = $certPrefix . str_pad((string)$currentCert, $certDigits, '0', STR_PAD_LEFT);
                $formattedSklNo = $sklPrefix . str_pad((string)$currentSkl, 3, '0', STR_PAD_LEFT);

                $stu->certificate_number = $formattedCertNo;
                $stu->skl_number = $formattedSklNo;
                $stu->save();

                $currentCert++;
                $currentSkl++;
            }

            DB::commit();
            return $this->success([
                'updated_count' => $students->count(),
            ], "Berhasil men-generate nomor seri Ijazah & SKL untuk {$students->count()} siswa.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Gagal men-generate nomor seri: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update individual student certificate serial number.
     */
    public function updateStudentSerialNumber(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'certificate_number' => 'nullable|string|max:150',
            'skl_number' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $student->certificate_number = $request->input('certificate_number');
        if ($request->has('skl_number')) {
            $student->skl_number = $request->input('skl_number');
        }
        $student->save();

        return $this->success($student, 'Nomor seri siswa berhasil diperbarui.');
    }

    /**
     * Download Excel Template for Graduation Grades (Per Semester or Exam).
     */
    public function downloadTemplate(Request $request)
    {
        $semesterKey = $request->query('semester', 'sem_1');
        $classId = $request->query('class_id');

        $semLabels = [
            'sem_1' => 'Semester 1 (Kls 7 Ganjil)',
            'sem_2' => 'Semester 2 (Kls 7 Genap)',
            'sem_3' => 'Semester 3 (Kls 8 Ganjil)',
            'sem_4' => 'Semester 4 (Kls 8 Genap)',
            'sem_5' => 'Semester 5 (Kls 9 Ganjil)',
            'sem_6' => 'Semester 6 (Kls 9 Genap)',
            'exam'  => 'Ujian Madrasah (Teori & Praktik)',
            'all'   => 'Leger Lengkap (Semester 1-6 & Ujian)',
        ];
        $semLabel = $semLabels[$semesterKey] ?? 'Semester 1';

        // 1. Identify Grade 9 classes
        $grade9Classes = ClassRoom::where(function ($q) {
            $q->where('grade_level', '9')
              ->orWhere('name', 'like', '9%')
              ->orWhere('name', 'like', 'IX%');
        })->orderBy('name')->pluck('id')->toArray();

        $targetClassIds = ($classId && in_array((int)$classId, $grade9Classes))
            ? [(int)$classId]
            : $grade9Classes;

        // 2. Fetch Grade 9 Students
        $students = Student::where(function ($q) use ($targetClassIds) {
            $q->whereIn('class_id', $targetClassIds)
              ->orWhereIn('lokal_class_id', $targetClassIds);
        })->with(['classRoom:id,name', 'lokalClassRoom:id,name'])
          ->orderBy('full_name')
          ->get();

        // 3. Active Subjects
        $setting = GraduationSetting::getActiveSettings();
        $allSubjects = $this->sortSubjectsAccordingToStructure(Subject::all(['id', 'name', 'code']));
        $activeSubjectIds = is_array($setting->active_subject_ids) ? array_map('intval', $setting->active_subject_ids) : null;
        $subjects = $activeSubjectIds !== null
            ? $allSubjects->filter(fn($s) => in_array((int)$s->id, $activeSubjectIds))->values()
            : $allSubjects;

        $practicalIds = is_array($setting->practical_subject_ids) ? array_map('intval', $setting->practical_subject_ids) : [];

        // 4. Existing grades mapping for pre-fill
        $studentIds = $students->pluck('id')->toArray();
        $existingGrades = GraduationGrade::whereIn('student_id', $studentIds)->get();
        $gradeMap = [];
        foreach ($existingGrades as $g) {
            $gradeMap[$g->student_id][$g->subject_id] = $g;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Nilai');

        // Row 1: Judul
        $sheet->setCellValue('A1', 'TEMPLATE NILAI IJAZAH & PDUM MADRASAH - ' . strtoupper($semLabel));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('064E3B'));

        // Row 2: Petunjuk
        $sheet->setCellValue('A2', 'Petunjuk: Isikan nilai angka 0 s.d. 100 pada kolom mata pelajaran. Jangan mengubah nilai pada kolom NISN, NIS, dan NAMA SISWA.');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Row 3: Header Tabel
        $headers = ['NO', 'NISN', 'NIS', 'NAMA SISWA', 'KELAS'];

        if ($semesterKey === 'exam') {
            foreach ($subjects as $sub) {
                $isPractical = in_array((int)$sub->id, $practicalIds);
                if ($isPractical) {
                    $headers[] = "[{$sub->code}] {$sub->name} (TEORI)";
                    $headers[] = "[{$sub->code}] {$sub->name} (PRAKTIK)";
                } else {
                    $headers[] = "[{$sub->code}] {$sub->name} (NILAI AKHIR)";
                }
            }
        } elseif ($semesterKey === 'all') {
            foreach ($subjects as $sub) {
                for ($s = 1; $s <= 6; $s++) {
                    $headers[] = "[{$sub->code}] {$sub->name} (S{$s})";
                }
                $headers[] = "[{$sub->code}] {$sub->name} (UJIAN)";
            }
        } else {
            foreach ($subjects as $sub) {
                $headers[] = "[{$sub->code}] {$sub->name}";
            }
        }

        $sheet->fromArray([$headers], null, 'A3');

        // Row 4+: Data Siswa
        $rows = [];
        foreach ($students as $idx => $stu) {
            $row = [
                $idx + 1,
                (string)$stu->nisn,
                (string)($stu->nis ?: '-'),
                $stu->full_name,
                $stu->classRoom?->name ?? '9',
            ];

            if ($semesterKey === 'exam') {
                foreach ($subjects as $sub) {
                    $isPractical = in_array((int)$sub->id, $practicalIds);
                    $existing = $gradeMap[$stu->id][$sub->id] ?? null;
                    if ($isPractical) {
                        $row[] = ($existing && $existing->exam_theory !== null) ? $existing->exam_theory : '';
                        $row[] = ($existing && $existing->exam_practice !== null) ? $existing->exam_practice : '';
                    } else {
                        $row[] = ($existing && $existing->final_exam_score !== null) ? $existing->final_exam_score : '';
                    }
                }
            } elseif ($semesterKey === 'all') {
                foreach ($subjects as $sub) {
                    $existing = $gradeMap[$stu->id][$sub->id] ?? null;
                    for ($s = 1; $s <= 6; $s++) {
                        $field = "sem_{$s}";
                        $row[] = ($existing && $existing->{$field} !== null) ? $existing->{$field} : '';
                    }
                    $row[] = ($existing && $existing->final_exam_score !== null) ? $existing->final_exam_score : '';
                }
            } else {
                foreach ($subjects as $sub) {
                    $existing = $gradeMap[$stu->id][$sub->id] ?? null;
                    $row[] = ($existing && $existing->{$semesterKey} !== null) ? $existing->{$semesterKey} : '';
                }
            }
            $rows[] = $row;
        }

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A4');
        }

        // Explicitly set NISN and NIS as text strings to prevent Excel scientific notation or leading zero loss
        foreach ($students as $idx => $stu) {
            $rNum = $idx + 4;
            $sheet->setCellValueExplicit("B{$rNum}", (string)$stu->nisn, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$rNum}", (string)($stu->nis ?: '-'), DataType::TYPE_STRING);
        }

        // Styling & Layout
        $lastCol = $sheet->getHighestColumn();
        $lastRow = max(4, $sheet->getHighestRow());

        // Row 1: Merged Title
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '064E3B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);

        // Row 2: Merged Instructions
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
        ]);

        // Row 3: Header Row
        $sheet->getRowDimension(3)->setRowHeight(38);
        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Rows 4+: Data Row Height & Alternating Zebra Striping
        for ($r = 4; $r <= $lastRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(22);
            if ($r % 2 === 1) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                ]);
            }
        }

        // Alignments & Number Formats
        $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B4:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B4:C{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet->getStyle("D4:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
        $sheet->getStyle("E4:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A4:{$lastCol}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Thin Borders
        $sheet->getStyle("A3:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        // Explicit Column Widths (Prevent oversized column A)
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(35);
        $sheet->getColumnDimension('E')->setWidth(12);

        $startColIdx = Coordinate::columnIndexFromString('F');
        $endColIdx = Coordinate::columnIndexFromString($lastCol);
        for ($ci = $startColIdx; $ci <= $endColIdx; $ci++) {
            $colLetter = Coordinate::stringFromColumnIndex($ci);
            $sheet->getColumnDimension($colLetter)->setWidth(18);
        }

        // Freeze Panes at F4: Keeps student info (A-E) and headers (1-3) pinned when scrolling
        $sheet->freezePane('F4');

        // Explicitly keep gridlines visible
        $sheet->setShowGridLines(true);

        $writer = new Xlsx($spreadsheet);
        $cleanSem = preg_replace('/[^a-zA-Z0-9_-]/', '_', $semesterKey);
        $fileName = "Template_Nilai_Ijazah_{$cleanSem}.xlsx";

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    /**
     * Import Excel file containing graduation grades.
     */
    public function importGrades(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:15360',
            'semester' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $file = $request->file('file');
        $forcedSemester = $request->input('semester');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return $this->error('Gagal membaca file Excel: ' . $e->getMessage(), 422);
        }

        if (empty($data) || count($data) < 3) {
            return $this->error('File Excel kosong atau format tidak sesuai template.', 422);
        }

        // Find header row (the row containing NISN or NAMA SISWA)
        $headerRowIdx = null;
        $headerRow = [];
        foreach ($data as $idx => $row) {
            $rowStr = strtoupper(implode(' ', array_map('strval', $row)));
            if (str_contains($rowStr, 'NISN') && (str_contains($rowStr, 'NAMA') || str_contains($rowStr, 'SISWA'))) {
                $headerRowIdx = $idx;
                $headerRow = $row;
                break;
            }
        }

        if ($headerRowIdx === null) {
            return $this->error('Baris header kolom tidak ditemukan. Pastikan kolom NISN dan NAMA SISWA tersedia sesuai template.', 422);
        }

        $allSubjects = Subject::all(['id', 'name', 'code']);
        $setting = GraduationSetting::getActiveSettings();

        $nisnCol = null;
        $nisCol = null;
        $nameCol = null;
        $subjectCols = [];

        foreach ($headerRow as $cIdx => $rawHeader) {
            $h = strtoupper(trim((string)$rawHeader));
            if (empty($h)) continue;

            if ($h === 'NISN' || str_contains($h, 'NISN')) {
                $nisnCol = $cIdx;
            } elseif ($h === 'NIS' || $h === 'N.I.S') {
                $nisCol = $cIdx;
            } elseif (str_contains($h, 'NAMA')) {
                $nameCol = $cIdx;
            } else {
                foreach ($allSubjects as $sub) {
                    $codeUpper = strtoupper(trim($sub->code));
                    $nameUpper = strtoupper(trim($sub->name));

                    $matchByCode = !empty($codeUpper) && str_contains($h, $codeUpper);
                    $matchByName = str_contains($h, $nameUpper);

                    if ($matchByCode || $matchByName) {
                        $targetField = $forcedSemester ?: 'sem_1';

                        if (str_contains($h, '(TEORI)')) {
                            $targetField = 'exam_theory';
                        } elseif (str_contains($h, '(PRAKTIK)')) {
                            $targetField = 'exam_practice';
                        } elseif (str_contains($h, '(NILAI AKHIR)') || str_contains($h, '(UJIAN)')) {
                            $targetField = 'final_exam_score';
                        } elseif (preg_match('/\(S([1-6])\)/i', $h, $m)) {
                            $targetField = "sem_{$m[1]}";
                        } elseif ($forcedSemester === 'exam') {
                            $practicalIds = is_array($setting->practical_subject_ids) ? array_map('intval', $setting->practical_subject_ids) : [];
                            if (in_array((int)$sub->id, $practicalIds)) {
                                $targetField = 'exam_theory';
                            } else {
                                $targetField = 'final_exam_score';
                            }
                        }

                        $subjectCols[] = [
                            'col_idx' => $cIdx,
                            'subject_id' => $sub->id,
                            'target_field' => $targetField,
                            'subject_name' => $sub->name,
                        ];
                        break;
                    }
                }
            }
        }

        if (empty($subjectCols)) {
            return $this->error('Kolom mata pelajaran tidak terdeteksi. Gunakan template resmi yang telah diunduh dari sistem.', 422);
        }

        $grade9Students = Student::all(['id', 'nisn', 'nis', 'full_name']);
        $studentsByNisn = [];
        $studentsByNis = [];
        $studentsByName = [];

        foreach ($grade9Students as $stu) {
            if (!empty($stu->nisn)) $studentsByNisn[trim($stu->nisn)] = $stu;
            if (!empty($stu->nis)) $studentsByNis[trim($stu->nis)] = $stu;
            $studentsByName[strtolower(trim($stu->full_name))] = $stu;
        }

        $importedStudentsCount = 0;
        $updatedGradesCount = 0;
        $studentRecordsToRecalc = [];

        DB::beginTransaction();
        try {
            for ($r = $headerRowIdx + 1; $r < count($data); $r++) {
                $row = $data[$r];
                $rawNisn = $nisnCol !== null ? trim((string)($row[$nisnCol] ?? '')) : '';
                $rawNis = $nisCol !== null ? trim((string)($row[$nisCol] ?? '')) : '';
                $rawName = $nameCol !== null ? trim((string)($row[$nameCol] ?? '')) : '';

                if (empty($rawNisn) && empty($rawNis) && empty($rawName)) {
                    continue;
                }

                $student = null;
                if (!empty($rawNisn) && isset($studentsByNisn[$rawNisn])) {
                    $student = $studentsByNisn[$rawNisn];
                } elseif (!empty($rawNis) && isset($studentsByNis[$rawNis])) {
                    $student = $studentsByNis[$rawNis];
                } elseif (!empty($rawName) && isset($studentsByName[strtolower($rawName)])) {
                    $student = $studentsByName[strtolower($rawName)];
                }

                if (!$student) {
                    continue;
                }

                $studentUpdated = false;

                foreach ($subjectCols as $sc) {
                    $val = $row[$sc['col_idx']] ?? null;
                    if ($val === null || $val === '') continue;

                    $valStr = str_replace(',', '.', trim((string)$val));
                    if (!is_numeric($valStr)) continue;

                    $numVal = floatval($valStr);
                    if ($numVal < 0 || $numVal > 100) continue;

                    $record = GraduationGrade::firstOrNew([
                        'student_id' => $student->id,
                        'subject_id' => $sc['subject_id'],
                    ]);

                    $field = $sc['target_field'];
                    $record->{$field} = $numVal;

                    $record->save();
                    $studentRecordsToRecalc[$record->id] = $record;
                    $updatedGradesCount++;
                    $studentUpdated = true;
                }

                if ($studentUpdated) {
                    $importedStudentsCount++;
                }
            }

            foreach ($studentRecordsToRecalc as $rec) {
                $rec->recalculateScores($setting);
                $rec->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Gagal memproses data import: ' . $e->getMessage(), 500);
        }

        if ($importedStudentsCount === 0) {
            return $this->error('Tidak ada nilai valid yang berhasil diimpor. Pastikan NISN siswa dan kolom nilai terisi dengan angka 0-100.', 422);
        }

        return $this->success([
            'imported_students' => $importedStudentsCount,
            'updated_grades' => $updatedGradesCount,
        ], "Alhamdulillah, berhasil mengimpor {$updatedGradesCount} nilai mata pelajaran untuk {$importedStudentsCount} siswa!");
    }
}
