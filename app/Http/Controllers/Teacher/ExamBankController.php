<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\ExamPackage;
use App\Models\ExamQuestion;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamBankController extends Controller
{
    private function getTeacher(Request $request): ?Teacher
    {
        $user = $request->user();
        if ($user && $user->teacher) {
            return $user->teacher;
        }
        return null;
    }

    /**
     * Get list of exam packages for teacher (or all for admin/kurikulum).
     */
    public function index(Request $request)
    {
        $teacher = $this->getTeacher($request);
        $user = $request->user();
        $isPrivileged = in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah']);

        $query = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher.user'])
            ->withCount([
                'questions',
                'questions as pg_count' => function ($q) {
                    $q->where('question_type', 'pg');
                },
                'questions as essay_count' => function ($q) {
                    $q->where('question_type', 'essay');
                },
                'questions as completed_kisi_count' => function ($q) {
                    $q->whereNotNull('indicator')->where('indicator', '!=', '');
                }
            ]);

        // If regular teacher, only show own packages
        if (!$isPrivileged || ($request->has('my_exams_only') && $request->boolean('my_exams_only'))) {
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        } elseif ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('status_kurikulum')) {
            $query->where('status_kurikulum', $request->status_kurikulum);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('subject', function ($s) use ($search) {
                        $s->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $packages = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $packages
        ]);
    }

    /**
     * Get options for creating / filtering exam packages.
     */
    public function options(Request $request)
    {
        $teacher = $this->getTeacher($request);
        $user = $request->user();
        $isPrivileged = in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah']);

        $classes = ClassRoom::orderBy('grade_level')->orderBy('name')->get(['id', 'name', 'grade_level']);

        if (!$isPrivileged && $teacher && $teacher->subjects()->exists()) {
            $subjects = $teacher->subjects()->orderBy('name')->get(['subjects.id', 'subjects.name', 'subjects.code']);
        } else {
            $subjects = Subject::orderBy('name')->get(['id', 'name', 'code']);
        }

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $activeYear = AcademicYear::where('is_active', true)->first() ?? $academicYears->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'classes' => $classes,
                'subjects' => $subjects,
                'academic_years' => $academicYears,
                'active_year' => $activeYear,
                'exam_types' => [
                    ['value' => 'uh', 'label' => 'Ulangan Harian (UH) / Formatif'],
                    ['value' => 'sts', 'label' => 'Sumatif Tengah Semester (STS)'],
                    ['value' => 'sas', 'label' => 'Sumatif Akhir Semester (SAS / PAS)'],
                    ['value' => 'pat', 'label' => 'Penilaian Akhir Tahun (PAT)'],
                    ['value' => 'am', 'label' => 'Asesmen Madrasah (AM) / Ujian Akhir'],
                    ['value' => 'quiz', 'label' => 'Kuis / Latihan Soal'],
                ],
                'curriculum_types' => [
                    ['value' => 'merdeka', 'label' => 'Kurikulum Merdeka (Fase D)'],
                    ['value' => 'k13', 'label' => 'Kurikulum 2013 (K-13)'],
                ],
                'cognitive_levels' => [
                    ['value' => 'L1', 'label' => 'Level 1: C1-C2 (Pengetahuan & Pemahaman)'],
                    ['value' => 'L2', 'label' => 'Level 2: C3 (Aplikasi / Penerapan)'],
                    ['value' => 'L3', 'label' => 'Level 3: C4-C6 (Penalaran / HOTS)'],
                ],
                'difficulty_levels' => [
                    ['value' => 'mudah', 'label' => 'Mudah'],
                    ['value' => 'sedang', 'label' => 'Sedang'],
                    ['value' => 'sukar', 'label' => 'Sukar'],
                ],
            ]
        ]);
    }

    /**
     * Store new exam package.
     */
    public function store(Request $request)
    {
        $teacher = $this->getTeacher($request);
        $user = $request->user();
        $isPrivileged = in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah']);

        $validated = $request->validate([
            'class_room_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'title' => 'required|string|max:255',
            'exam_type' => 'required|string|in:uh,sts,sas,pat,am,quiz',
            'semester' => 'required|string|in:ganjil,genap',
            'total_questions' => 'required|integer|min:1|max:100',
            'kkm' => 'nullable|numeric|min:0|max:100',
            'pg_weight' => 'nullable|numeric|min:0|max:100',
            'essay_weight' => 'nullable|numeric|min:0|max:100',
            'academic_phase' => 'nullable|string|max:30',
            'curriculum_type' => 'nullable|string|in:merdeka,k13',
            'description' => 'nullable|string|max:1000',
            'teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $teacherId = null;
        if ($isPrivileged && $request->filled('teacher_id')) {
            $teacherId = $request->teacher_id;
        } elseif ($teacher) {
            $teacherId = $teacher->id;
        } else {
            // Fallback for admin if no teacher specified
            $teacherId = Teacher::first()?->id;
        }

        if (!$teacherId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru pengampu tidak ditemukan. Pastikan akun memiliki profil guru.'
            ], 422);
        }

        $activeYear = AcademicYear::where('is_active', true)->first();

        $package = ExamPackage::create([
            'teacher_id' => $teacherId,
            'class_room_id' => $validated['class_room_id'],
            'subject_id' => $validated['subject_id'],
            'academic_year_id' => $validated['academic_year_id'] ?? $activeYear?->id,
            'title' => $validated['title'],
            'exam_type' => $validated['exam_type'],
            'semester' => $validated['semester'],
            'total_questions' => $validated['total_questions'],
            'kkm' => $validated['kkm'] ?? 75.00,
            'pg_weight' => $validated['pg_weight'] ?? 70.00,
            'essay_weight' => $validated['essay_weight'] ?? 30.00,
            'academic_phase' => $validated['academic_phase'] ?? 'Fase D',
            'curriculum_type' => $validated['curriculum_type'] ?? 'merdeka',
            'status' => 'draft',
            'status_kurikulum' => 'draft',
            'description' => $validated['description'] ?? null,
        ]);

        // Pre-create blank question placeholders up to total_questions
        $existingCount = $package->questions()->count();
        if ($existingCount < $package->total_questions) {
            $newQuestions = [];
            for ($i = $existingCount + 1; $i <= $package->total_questions; $i++) {
                $newQuestions[] = [
                    'exam_package_id' => $package->id,
                    'question_number' => $i,
                    'question_type' => 'pg',
                    'question_text' => null,
                    'options' => json_encode(['A' => '', 'B' => '', 'C' => '', 'D' => '']),
                    'correct_answer' => null,
                    'score_weight' => 1.00,
                    'cognitive_level' => 'L1',
                    'difficulty_level' => 'sedang',
                    'telaah_status' => 'layak',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ExamQuestion::insert($newQuestions);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Paket soal berhasil dibuat. Silakan input butir soal & kisi-kisi.',
            'data' => $package->load(['classRoom', 'subject', 'academicYear'])
        ], 201);
    }

    /**
     * Show package detail with questions.
     */
    public function show(Request $request, $id)
    {
        $package = ExamPackage::with([
            'classRoom',
            'subject',
            'academicYear',
            'teacher.user',
            'reviewedBy',
            'questions' => function ($q) {
                $q->orderBy('question_number', 'asc');
            }
        ])->findOrFail($id);

        $this->authorizePackageAccess($request, $package);

        return response()->json([
            'status' => 'success',
            'data' => $package
        ]);
    }

    /**
     * Update package metadata.
     */
    public function update(Request $request, $id)
    {
        $package = ExamPackage::findOrFail($id);
        $this->authorizePackageAccess($request, $package);

        $validated = $request->validate([
            'class_room_id' => 'sometimes|required|exists:classes,id',
            'subject_id' => 'sometimes|required|exists:subjects,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'title' => 'sometimes|required|string|max:255',
            'exam_type' => 'sometimes|required|string|in:uh,sts,sas,pat,am,quiz',
            'semester' => 'sometimes|required|string|in:ganjil,genap',
            'total_questions' => 'sometimes|required|integer|min:1|max:100',
            'kkm' => 'nullable|numeric|min:0|max:100',
            'pg_weight' => 'nullable|numeric|min:0|max:100',
            'essay_weight' => 'nullable|numeric|min:0|max:100',
            'academic_phase' => 'nullable|string|max:30',
            'curriculum_type' => 'nullable|string|in:merdeka,k13',
            'description' => 'nullable|string|max:1000',
        ]);

        $package->update($validated);

        // Adjust questions count if total_questions increased
        $currentCount = $package->questions()->count();
        if ($package->total_questions > $currentCount) {
            $newQuestions = [];
            for ($i = $currentCount + 1; $i <= $package->total_questions; $i++) {
                $newQuestions[] = [
                    'exam_package_id' => $package->id,
                    'question_number' => $i,
                    'question_type' => 'pg',
                    'question_text' => null,
                    'options' => json_encode(['A' => '', 'B' => '', 'C' => '', 'D' => '']),
                    'correct_answer' => null,
                    'score_weight' => 1.00,
                    'cognitive_level' => 'L1',
                    'difficulty_level' => 'sedang',
                    'telaah_status' => 'layak',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            ExamQuestion::insert($newQuestions);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Informasi paket soal berhasil diperbarui.',
            'data' => $package->fresh(['classRoom', 'subject', 'academicYear'])
        ]);
    }

    /**
     * Delete exam package.
     */
    public function destroy(Request $request, $id)
    {
        $package = ExamPackage::findOrFail($id);
        $this->authorizePackageAccess($request, $package);

        if ($package->status_kurikulum === 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Paket soal yang telah disetujui kurikulum tidak dapat dihapus.'
            ], 422);
        }

        $package->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Paket soal berhasil dihapus.'
        ]);
    }

    /**
     * Save / bulk update questions with full kisi-kisi and keys.
     */
    public function saveQuestions(Request $request, $id)
    {
        $package = ExamPackage::findOrFail($id);
        $this->authorizePackageAccess($request, $package);

        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|integer',
            'questions.*.question_number' => 'required|integer|min:1|max:100',
            'questions.*.question_type' => 'required|string|in:pg,essay,pg_complex,true_false,matching,short_answer',
            'questions.*.question_text' => 'nullable|string',
            'questions.*.question_image' => 'nullable|string|max:255',
            'questions.*.options' => 'nullable',
            'questions.*.correct_answer' => 'nullable|string|max:2000',
            'questions.*.score_weight' => 'nullable|numeric|min:0.1',
            'questions.*.material' => 'nullable|string|max:255',
            'questions.*.basic_competency' => 'nullable|string|max:500',
            'questions.*.indicator' => 'nullable|string|max:1000',
            'questions.*.cognitive_level' => 'nullable|string|in:L1,L2,L3',
            'questions.*.difficulty_level' => 'nullable|string|in:mudah,sedang,sukar',
            'questions.*.rubric' => 'nullable|string',
            'questions.*.telaah_materi' => 'nullable|array',
            'questions.*.telaah_konstruksi' => 'nullable|array',
            'questions.*.telaah_bahasa' => 'nullable|array',
            'questions.*.telaah_status' => 'nullable|string|in:layak,revisi,tolak',
            'questions.*.telaah_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['questions'] as $qData) {
                // Default options structure if PG
                $options = $qData['options'] ?? null;
                if ($qData['question_type'] === 'pg' && empty($options)) {
                    $options = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
                }

                ExamQuestion::updateOrCreate(
                    [
                        'exam_package_id' => $package->id,
                        'question_number' => $qData['question_number'],
                    ],
                    [
                        'question_type' => $qData['question_type'],
                        'question_text' => $qData['question_text'] ?? null,
                        'question_image' => $qData['question_image'] ?? null,
                        'options' => $options,
                        'correct_answer' => !empty($qData['correct_answer'])
                            ? (in_array($qData['question_type'], ['essay', 'short_answer']) ? trim($qData['correct_answer']) : trim(strtoupper($qData['correct_answer'])))
                            : null,
                        'score_weight' => $qData['score_weight'] ?? 1.00,
                        'material' => $qData['material'] ?? null,
                        'basic_competency' => $qData['basic_competency'] ?? null,
                        'indicator' => $qData['indicator'] ?? null,
                        'cognitive_level' => $qData['cognitive_level'] ?? 'L1',
                        'difficulty_level' => $qData['difficulty_level'] ?? 'sedang',
                        'rubric' => $qData['rubric'] ?? null,
                        'telaah_materi' => $qData['telaah_materi'] ?? [
                            'sesuai_indikator' => true,
                            'materi_sesuai_fase' => true,
                            'kunci_hanya_satu' => true,
                            'pilihan_homogen' => true,
                        ],
                        'telaah_konstruksi' => $qData['telaah_konstruksi'] ?? [
                            'pokok_soal_tegas' => true,
                            'bebas_petunjuk_kunci' => true,
                            'bebas_negatif_ganda' => true,
                            'panjang_opsi_relatif_sama' => true,
                        ],
                        'telaah_bahasa' => $qData['telaah_bahasa'] ?? [
                            'bahasa_baku_eyd' => true,
                            'komunikatif_mudah_dipahami' => true,
                            'bebas_sara_dan_bias' => true,
                        ],
                        'telaah_status' => $qData['telaah_status'] ?? 'layak',
                        'telaah_notes' => $qData['telaah_notes'] ?? null,
                    ]
                );
            }

            // Sync total_questions count
            $maxNumber = max(array_column($validated['questions'], 'question_number'));
            if ($maxNumber > $package->total_questions) {
                $package->update(['total_questions' => $maxNumber]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Butir soal dan kisi-kisi berhasil disimpan.',
                'data' => $package->questions()->orderBy('question_number')->get()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload question stimulus image / diagram.
     */
    public function uploadImage(Request $request, $id)
    {
        $package = ExamPackage::findOrFail($id);
        $this->authorizePackageAccess($request, $package);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:3072'
        ]);

        $file = $request->file('image');
        $filename = 'soal_' . $package->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('exam-questions', $filename, 'public');

        return response()->json([
            'status' => 'success',
            'message' => 'Gambar berhasil diunggah.',
            'url' => Storage::url($path),
            'filename' => $path
        ]);
    }

    /**
     * Submit exam package to Kurikulum for review.
     */
    public function submitToKurikulum(Request $request, $id)
    {
        $package = ExamPackage::with('questions')->findOrFail($id);
        $this->authorizePackageAccess($request, $package);

        $questionsCount = $package->questions->count();
        if ($questionsCount === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada butir soal yang diisi pada paket ini.'
            ], 422);
        }

        // Validate basic readiness
        $missingKeys = [];
        $missingIndicators = [];
        foreach ($package->questions as $q) {
            if (in_array($q->question_type, ['pg', 'pg_complex', 'true_false', 'matching', 'short_answer']) && empty($q->correct_answer)) {
                $missingKeys[] = $q->question_number;
            }
            if (empty($q->indicator)) {
                $missingIndicators[] = $q->question_number;
            }
        }

        if (!empty($missingKeys)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kunci jawaban belum diisi pada soal nomor: ' . implode(', ', array_slice($missingKeys, 0, 10)) . (count($missingKeys) > 10 ? ' dst.' : ''),
            ], 422);
        }

        $package->update([
            'status_kurikulum' => 'submitted',
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Paket soal berhasil diajukan ke Tim Kurikulum untuk ditelaah dan diverifikasi.',
            'data' => $package->fresh()
        ]);
    }

    /**
     * Download Excel template for question & kisi-kisi bulk import.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Soal & Kisi-Kisi');

        // Header Title
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'TEMPLATE INPUT SOAL & KISI-KISI - MTs AL-HASANAH');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('064E3B'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'Petunjuk: Isi nomor, teks soal, opsi jawaban A-D, kunci (A/B/C/D), materi pokok, indikator, level kognitif (L1/L2/L3), dan kesukaran (mudah/sedang/sukar). Jangan ubah baris header!');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4B5563'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Columns definition
        $headers = [
            'A4' => ['No', 6],
            'B4' => ['Tipe Soal (pg/essay)', 16],
            'C4' => ['Teks Soal / Pertanyaan', 45],
            'D4' => ['Pilihan A', 25],
            'E4' => ['Pilihan B', 25],
            'F4' => ['Pilihan C', 25],
            'G4' => ['Pilihan D', 25],
            'H4' => ['Kunci Jawaban', 14],
            'I4' => ['Materi Pokok', 22],
            'J4' => ['Indikator Soal (Kisi-Kisi)', 35],
            'K4' => ['Level Kognitif (L1/L2/L3)', 18],
            'L4' => ['Kesukaran (mudah/sedang/sukar)', 20],
        ];

        foreach ($headers as $cell => [$title, $width]) {
            $sheet->setCellValue($cell, $title);
            $sheet->getColumnDimension(substr($cell, 0, 1))->setWidth($width);
        }

        // Header Styling
        $headerRange = 'A4:L4';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'], // Emerald 600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Sample Rows
        $sampleData = [
            [
                1, 'pg', 'Hasil dari operasi 25 + 15 x 2 adalah...',
                '55', '80', '65', '70',
                'A', 'Operasi Hitung Campuran',
                'Disajikan operasi hitung campuran bilangan bulat, siswa dapat menentukan hasil perhitungannya dengan benar.',
                'L1', 'mudah'
            ],
            [
                2, 'pg', 'Sebuah segitiga siku-siku memiliki panjang alas 6 cm dan tinggi 8 cm. Berapakah panjang sisi miringnya?',
                '10 cm', '12 cm', '14 cm', '16 cm',
                'A', 'Teorema Pythagoras',
                'Disajikan ukuran alas dan tinggi segitiga siku-siku, siswa dapat menghitung panjang sisi miring menggunakan teorema Pythagoras.',
                'L2', 'sedang'
            ],
            [
                3, 'essay', 'Jelaskan perbedaan antara rukun dan syarat sah dalam ibadah shalat!',
                '', '', '', '',
                'Rukun adalah bagian dari amalan shalat, sedangkan syarat sah adalah hal yang harus dipenuhi sebelum shalat.',
                'Fikih Ibadah',
                'Siswa dapat menganalisis perbedaan rukun dan syarat sah shalat dengan memberikan contoh konkret.',
                'L3', 'sukar'
            ],
        ];

        $rowIdx = 5;
        foreach ($sampleData as $row) {
            $colLetter = 'A';
            foreach ($row as $val) {
                $sheet->setCellValue("{$colLetter}{$rowIdx}", $val);
                $colLetter++;
            }
            // Alignments
            $sheet->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$rowIdx}:L{$rowIdx}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getRowDimension($rowIdx)->setRowHeight(24);
            $rowIdx++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Soal_dan_Kisi_Kisi_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Import questions & kisi-kisi from Excel.
     */
    public function importExcel(Request $request, $id)
    {
        $package = ExamPackage::findOrFail($id);
        $this->authorizePackageAccess($request, $package);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120'
        ]);

        $file = $request->file('file');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Starting data row (row 5 in template, index 4)
        $startRow = 4;
        $importedCount = 0;

        DB::beginTransaction();
        try {
            for ($i = $startRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                $qNumber = intval($row[0] ?? 0);
                if ($qNumber <= 0) {
                    continue;
                }

                $type = strtolower(trim($row[1] ?? 'pg'));
                if (!in_array($type, ['pg', 'essay', 'pg_complex'])) {
                    $type = 'pg';
                }

                $text = trim($row[2] ?? '');
                $optA = trim($row[3] ?? '');
                $optB = trim($row[4] ?? '');
                $optC = trim($row[5] ?? '');
                $optD = trim($row[6] ?? '');
                $key = trim(strtoupper($row[7] ?? ''));
                $material = trim($row[8] ?? '');
                $indicator = trim($row[9] ?? '');
                $level = strtoupper(trim($row[10] ?? 'L1'));
                if (!in_array($level, ['L1', 'L2', 'L3'])) {
                    $level = 'L1';
                }
                $diff = strtolower(trim($row[11] ?? 'sedang'));
                if (!in_array($diff, ['mudah', 'sedang', 'sukar'])) {
                    $diff = 'sedang';
                }

                ExamQuestion::updateOrCreate(
                    [
                        'exam_package_id' => $package->id,
                        'question_number' => $qNumber,
                    ],
                    [
                        'question_type' => $type,
                        'question_text' => $text ?: null,
                        'options' => $type === 'pg' ? [
                            'A' => $optA,
                            'B' => $optB,
                            'C' => $optC,
                            'D' => $optD,
                        ] : null,
                        'correct_answer' => $key ?: null,
                        'score_weight' => 1.00,
                        'material' => $material ?: null,
                        'indicator' => $indicator ?: null,
                        'cognitive_level' => $level,
                        'difficulty_level' => $diff,
                        'telaah_status' => 'layak',
                        'telaah_materi' => [
                            'sesuai_indikator' => true,
                            'materi_sesuai_fase' => true,
                            'kunci_hanya_satu' => true,
                            'pilihan_homogen' => true,
                        ],
                        'telaah_konstruksi' => [
                            'pokok_soal_tegas' => true,
                            'bebas_petunjuk_kunci' => true,
                            'bebas_negatif_ganda' => true,
                            'panjang_opsi_relatif_sama' => true,
                        ],
                        'telaah_bahasa' => [
                            'bahasa_baku_eyd' => true,
                            'komunikatif_mudah_dipahami' => true,
                            'bebas_sara_dan_bias' => true,
                        ],
                    ]
                );

                $importedCount++;
            }

            // Sync total_questions count
            $totalActual = $package->questions()->count();
            if ($totalActual > $package->total_questions) {
                $package->update(['total_questions' => $totalActual]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil mengimpor {$importedCount} butir soal dari Excel.",
                'data' => [
                    'imported_count' => $importedCount,
                    'total_questions' => $package->fresh()->total_questions,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengimpor file Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user is allowed to modify the package.
     */
    private function authorizePackageAccess(Request $request, ExamPackage $package)
    {
        $user = $request->user();
        if (in_array($user->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah'])) {
            return;
        }

        $teacher = $this->getTeacher($request);
        if (!$teacher || $package->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah paket soal guru lain.');
        }
    }
}
