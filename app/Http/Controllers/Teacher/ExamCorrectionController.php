<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ExamPackage;
use App\Models\ExamQuestion;
use App\Models\ExamSubmission;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExamCorrectionController extends Controller
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
     * List all exam packages created by teacher or assigned to classes.
     */
    public function index(Request $request)
    {
        $teacher = $this->getTeacher($request);
        $user = $request->user();

        $query = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher'])
            ->withCount(['questions', 'submissions']);

        if ($teacher && $user->role !== 'admin' && $user->role !== 'super_admin') {
            $query->where('teacher_id', $teacher->id);
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        $exams = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        // Append average score & passed count
        $exams->getCollection()->transform(function ($exam) {
            $submissions = ExamSubmission::where('exam_package_id', $exam->id)->get();
            $exam->avg_score = $submissions->count() > 0 ? round($submissions->avg('total_score'), 2) : 0;
            $exam->passed_count = $submissions->where('is_passed', true)->count();
            return $exam;
        });

        return response()->json([
            'status' => 'success',
            'data' => $exams
        ]);
    }

    /**
     * Store new exam package and initialize question placeholders.
     */
    public function store(Request $request)
    {
        $teacher = $this->getTeacher($request);
        $validated = $request->validate([
            'class_room_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'title' => 'required|string|max:255',
            'exam_type' => 'required|string|in:uh,sts,sas,pat,am,quiz',
            'semester' => 'nullable|string|in:ganjil,genap',
            'total_questions' => 'required|integer|min:1|max:100',
            'kkm' => 'nullable|numeric|min:0|max:100',
            'pg_weight' => 'nullable|numeric|min:0|max:100',
            'essay_weight' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'quick_keys' => 'nullable|string', // Optional string of keys: e.g. "ABCDABCDAB..."
        ]);

        $teacherId = $teacher ? $teacher->id : ($request->user()->teacher_id ?? 1);

        // Fallback academic year if not provided
        if (empty($validated['academic_year_id'])) {
            $activeYear = AcademicYear::where('is_active', true)->first();
            $validated['academic_year_id'] = $activeYear ? $activeYear->id : null;
        }

        DB::beginTransaction();
        try {
            $exam = ExamPackage::create([
                'teacher_id' => $teacherId,
                'class_room_id' => $validated['class_room_id'],
                'subject_id' => $validated['subject_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'title' => $validated['title'],
                'exam_type' => $validated['exam_type'],
                'semester' => $validated['semester'] ?? 'ganjil',
                'total_questions' => $validated['total_questions'],
                'kkm' => $validated['kkm'] ?? 75.00,
                'pg_weight' => $validated['pg_weight'] ?? 70.00,
                'essay_weight' => $validated['essay_weight'] ?? 30.00,
                'status' => 'draft',
                'description' => $validated['description'] ?? null,
            ]);

            // Generate question placeholders
            $quickKeys = isset($validated['quick_keys']) ? strtoupper(trim(preg_replace('/\s+/', '', $validated['quick_keys']))) : '';
            $quickKeysLength = strlen($quickKeys);

            for ($i = 1; $i <= $exam->total_questions; $i++) {
                $key = ($i <= $quickKeysLength) ? substr($quickKeys, $i - 1, 1) : null;
                ExamQuestion::create([
                    'exam_package_id' => $exam->id,
                    'question_number' => $i,
                    'question_type' => 'pg',
                    'correct_answer' => $key,
                    'score_weight' => 1.00,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Paket ujian berhasil dibuat!',
                'data' => $exam->load(['classRoom', 'subject', 'questions'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat paket ujian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show exam detail, questions & answer keys, and students list with submissions.
     */
    public function show($id)
    {
        $exam = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher', 'questions'])
            ->findOrFail($id);

        // Fetch all active students in this classroom
        $students = Student::where('class_id', $exam->class_room_id)
            ->orderBy('full_name', 'asc')
            ->get();

        // Fetch existing submissions
        $submissions = ExamSubmission::where('exam_package_id', $exam->id)
            ->get()
            ->keyBy('student_id');

        // Map students with their submission status and answers
        $studentsData = $students->map(function ($student) use ($submissions) {
            $sub = $submissions->get($student->id);
            return [
                'id' => $student->id,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'name' => $student->full_name ?? $student->name ?? '-',
                'gender' => $student->gender,
                'submission_id' => $sub ? $sub->id : null,
                'student_answers' => $sub ? $sub->student_answers : [],
                'essay_scores' => $sub ? $sub->essay_scores : [],
                'correct_pg_count' => $sub ? $sub->correct_pg_count : 0,
                'wrong_pg_count' => $sub ? $sub->wrong_pg_count : 0,
                'pg_score' => $sub ? $sub->pg_score : 0,
                'essay_score' => $sub ? $sub->essay_score : 0,
                'total_score' => $sub ? $sub->total_score : 0,
                'is_passed' => $sub ? $sub->is_passed : false,
                'has_submitted' => $sub !== null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'exam' => $exam,
                'questions' => $exam->questions,
                'students' => $studentsData,
            ]
        ]);
    }

    /**
     * Update exam general settings.
     */
    public function update(Request $request, $id)
    {
        $exam = ExamPackage::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'exam_type' => 'required|string|in:uh,sts,sas,pat,am,quiz',
            'semester' => 'nullable|string|in:ganjil,genap',
            'kkm' => 'required|numeric|min:0|max:100',
            'pg_weight' => 'required|numeric|min:0|max:100',
            'essay_weight' => 'required|numeric|min:0|max:100',
            'status' => 'nullable|string|in:draft,active,completed',
            'description' => 'nullable|string',
        ]);

        $exam->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Informasi paket ujian berhasil diperbarui!',
            'data' => $exam
        ]);
    }

    /**
     * Delete exam package.
     */
    public function destroy($id)
    {
        $exam = ExamPackage::findOrFail($id);
        $exam->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Paket ujian berhasil dihapus!'
        ]);
    }

    /**
     * Save/Update answer keys and weights.
     */
    public function saveKeys(Request $request, $id)
    {
        $exam = ExamPackage::with('questions')->findOrFail($id);

        $request->validate([
            'questions' => 'nullable|array',
            'quick_keys' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($request->filled('quick_keys')) {
                $rawKeys = strtoupper(trim(preg_replace('/\s+/', '', $request->quick_keys)));
                $len = strlen($rawKeys);
                
                foreach ($exam->questions as $q) {
                    if ($q->question_number <= $len) {
                        $q->correct_answer = substr($rawKeys, $q->question_number - 1, 1);
                        $q->save();
                    }
                }
            } elseif ($request->has('questions')) {
                foreach ($request->questions as $item) {
                    ExamQuestion::where('exam_package_id', $exam->id)
                        ->where('question_number', $item['question_number'])
                        ->update([
                            'question_type' => $item['question_type'] ?? 'pg',
                            'correct_answer' => isset($item['correct_answer']) ? strtoupper(trim($item['correct_answer'])) : null,
                            'score_weight' => $item['score_weight'] ?? 1.00,
                        ]);
                }
            }

            // Automatically re-grade existing submissions with updated keys
            $this->regradeAllSubmissions($exam);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Kunci jawaban berhasil disimpan & nilai diperbarui otomatis!',
                'data' => $exam->fresh(['questions'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan kunci jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fast Auto-Grading for single or multiple students.
     */
    public function gradeSubmissions(Request $request, $id)
    {
        $exam = ExamPackage::with('questions')->findOrFail($id);

        $request->validate([
            'submissions' => 'required|array',
            'submissions.*.student_id' => 'required|exists:students,id',
            'submissions.*.answers' => 'nullable', // Array or String (e.g. "ABCD...")
            'submissions.*.essay_scores' => 'nullable|array',
        ]);

        $questions = $exam->questions->keyBy('question_number');
        $pgQuestions = $questions->filter(fn($q) => $q->question_type !== 'essay');
        $essayQuestions = $questions->filter(fn($q) => $q->question_type === 'essay');

        $totalPgWeight = $pgQuestions->sum('score_weight') ?: 1;
        $totalEssayMaxPoints = $essayQuestions->sum('score_weight') ?: 1;

        $results = [];

        DB::beginTransaction();
        try {
            foreach ($request->submissions as $subData) {
                $studentId = $subData['student_id'];
                $answersInput = $subData['answers'] ?? [];
                $essayScoresInput = $subData['essay_scores'] ?? [];

                // Normalize answers if passed as quick string (e.g. "ABCDA...")
                $answers = [];
                if (is_string($answersInput)) {
                    $cleanStr = strtoupper(trim(preg_replace('/\s+/', '', $answersInput)));
                    for ($i = 0; $i < strlen($cleanStr); $i++) {
                        $answers[(string)($i + 1)] = substr($cleanStr, $i, 1);
                    }
                } elseif (is_array($answersInput)) {
                    foreach ($answersInput as $k => $v) {
                        $answers[(string)$k] = is_string($v) ? strtoupper(trim($v)) : $v;
                    }
                }

                // Grade PG questions
                $correctPgCount = 0;
                $wrongPgCount = 0;
                $earnedPgPoints = 0;

                foreach ($pgQuestions as $num => $q) {
                    $studentAns = $answers[(string)$num] ?? null;
                    $correctAns = $q->correct_answer;

                    if ($correctAns && $studentAns && $studentAns === $correctAns) {
                        $correctPgCount++;
                        $earnedPgPoints += $q->score_weight;
                    } else {
                        $wrongPgCount++;
                    }
                }

                // PG Score out of 100
                $pgScore = $pgQuestions->count() > 0 ? round(($earnedPgPoints / $totalPgWeight) * 100, 2) : 100;

                // Essay Score out of 100
                $earnedEssayPoints = 0;
                if ($essayQuestions->count() > 0) {
                    foreach ($essayQuestions as $num => $q) {
                        $score = floatval($essayScoresInput[(string)$num] ?? 0);
                        $earnedEssayPoints += min($score, $q->score_weight);
                    }
                    $essayScore = round(($earnedEssayPoints / $totalEssayMaxPoints) * 100, 2);
                } else {
                    $essayScore = 0;
                }

                // Total Blended Score
                if ($essayQuestions->count() > 0 && $exam->essay_weight > 0) {
                    $pgPortion = ($pgScore * ($exam->pg_weight / 100));
                    $essayPortion = ($essayScore * ($exam->essay_weight / 100));
                    $totalScore = round($pgPortion + $essayPortion, 2);
                } else {
                    $totalScore = $pgScore;
                }

                $isPassed = $totalScore >= $exam->kkm;

                // Save or update submission
                $submission = ExamSubmission::updateOrCreate(
                    [
                        'exam_package_id' => $exam->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'student_answers' => $answers,
                        'essay_scores' => $essayScoresInput,
                        'correct_pg_count' => $correctPgCount,
                        'wrong_pg_count' => $wrongPgCount,
                        'pg_score' => $pgScore,
                        'essay_score' => $essayScore,
                        'total_score' => $totalScore,
                        'is_passed' => $isPassed,
                    ]
                );

                $results[] = $submission;
            }

            // Update exam status to completed if submissions exist
            if ($exam->status === 'draft') {
                $exam->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Koreksi instan berhasil diproses untuk ' . count($results) . ' siswa!',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses koreksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Item Analysis (Analisis Butir Soal, Tingkat Kesukaran & Daya Pembeda).
     */
    public function analysis($id)
    {
        $exam = ExamPackage::with(['questions', 'classRoom', 'subject', 'teacher'])->findOrFail($id);
        $submissions = ExamSubmission::with('student')
            ->where('exam_package_id', $exam->id)
            ->get();

        $totalSubmissions = $submissions->count();
        if ($totalSubmissions === 0) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'exam' => $exam,
                    'summary' => [
                        'total_students' => 0,
                        'avg_score' => 0,
                        'max_score' => 0,
                        'min_score' => 0,
                        'passed_count' => 0,
                        'remedial_count' => 0,
                        'pass_percentage' => 0,
                    ],
                    'questions_analysis' => []
                ]
            ]);
        }

        // Summary Statistics
        $avgScore = round($submissions->avg('total_score'), 2);
        $maxScore = $submissions->max('total_score');
        $minScore = $submissions->min('total_score');
        $passedCount = $submissions->where('is_passed', true)->count();
        $remedialCount = $totalSubmissions - $passedCount;
        $passPercentage = round(($passedCount / $totalSubmissions) * 100, 1);

        // Sort submissions for upper 27% and lower 27% groups (Daya Pembeda)
        $sortedSubs = $submissions->sortByDesc('total_score')->values();
        $groupSize = max(1, (int)round($totalSubmissions * 0.27));
        $upperGroup = $sortedSubs->take($groupSize);
        $lowerGroup = $sortedSubs->slice(-$groupSize);

        $questionsAnalysis = [];

        foreach ($exam->questions as $q) {
            $qNum = (string)$q->question_number;
            $correctAns = $q->correct_answer;
            $correctCount = 0;
            $optionCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'OTHER' => 0];

            foreach ($submissions as $sub) {
                $ans = $sub->student_answers[$qNum] ?? null;
                if ($ans) {
                    if (isset($optionCounts[$ans])) {
                        $optionCounts[$ans]++;
                    } else {
                        $optionCounts['OTHER']++;
                    }
                }
                if ($correctAns && $ans === $correctAns) {
                    $correctCount++;
                }
            }

            // Difficulty Index (Tingkat Kesukaran P = B / N)
            $difficultyIndex = round($correctCount / $totalSubmissions, 2);
            $difficultyCategory = 'Sedang';
            if ($difficultyIndex < 0.30) {
                $difficultyCategory = 'Sukar / Sulit';
            } elseif ($difficultyIndex > 0.70) {
                $difficultyCategory = 'Mudah';
            }

            // Discrimination Index (Daya Pembeda D = (Ba - Bb) / n)
            $upperCorrect = 0;
            foreach ($upperGroup as $uSub) {
                if (($uSub->student_answers[$qNum] ?? null) === $correctAns) $upperCorrect++;
            }
            $lowerCorrect = 0;
            foreach ($lowerGroup as $lSub) {
                if (($lSub->student_answers[$qNum] ?? null) === $correctAns) $lowerCorrect++;
            }

            $discriminationIndex = round(($upperCorrect - $lowerCorrect) / $groupSize, 2);
            $discriminationCategory = 'Cukup';
            if ($discriminationIndex >= 0.40) {
                $discriminationCategory = 'Sangat Baik';
            } elseif ($discriminationIndex >= 0.30) {
                $discriminationCategory = 'Baik';
            } elseif ($discriminationIndex >= 0.20) {
                $discriminationCategory = 'Cukup';
            } else {
                $discriminationCategory = 'Jelek / Perlu Direvisi';
            }

            $questionsAnalysis[] = [
                'question_number' => $q->question_number,
                'question_type' => $q->question_type,
                'correct_answer' => $correctAns,
                'correct_count' => $correctCount,
                'wrong_count' => $totalSubmissions - $correctCount,
                'difficulty_index' => $difficultyIndex,
                'difficulty_category' => $difficultyCategory,
                'discrimination_index' => $discriminationIndex,
                'discrimination_category' => $discriminationCategory,
                'options' => $optionCounts,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'exam' => $exam,
                'summary' => [
                    'total_students' => $totalSubmissions,
                    'avg_score' => $avgScore,
                    'max_score' => $maxScore,
                    'min_score' => $minScore,
                    'passed_count' => $passedCount,
                    'remedial_count' => $remedialCount,
                    'pass_percentage' => $passPercentage,
                ],
                'questions_analysis' => $questionsAnalysis,
            ]
        ]);
    }

    /**
     * 1-Click Sync exam scores to Grades table.
     */
    public function syncToGrades(Request $request, $id)
    {
        $exam = ExamPackage::findOrFail($id);
        $submissions = ExamSubmission::where('exam_package_id', $exam->id)->get();

        if ($submissions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada data nilai siswa untuk disinkronkan.'
            ], 422);
        }

        $targetField = 'score_assignment';
        if ($exam->exam_type === 'sts' || $exam->exam_type === 'uts') {
            $targetField = 'score_uts';
        } elseif ($exam->exam_type === 'sas' || $exam->exam_type === 'uas' || $exam->exam_type === 'pat' || $exam->exam_type === 'am') {
            $targetField = 'score_uas';
        }

        DB::beginTransaction();
        try {
            $syncedCount = 0;
            foreach ($submissions as $sub) {
                $grade = Grade::firstOrNew([
                    'student_id' => $sub->student_id,
                    'subject_id' => $exam->subject_id,
                    'academic_year_id' => $exam->academic_year_id,
                ]);

                $grade->{$targetField} = $sub->total_score;
                $grade->save();
                $syncedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menyinkronkan {$syncedCount} nilai ke Buku Nilai ({$targetField})!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyinkronkan nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel template or export scores.
     */
    public function exportExcel($id)
    {
        $exam = ExamPackage::with(['questions', 'classRoom', 'subject'])->findOrFail($id);
        $students = Student::where('class_id', $exam->class_room_id)
            ->orderBy('full_name', 'asc')
            ->get();

        $submissions = ExamSubmission::where('exam_package_id', $exam->id)
            ->get()
            ->keyBy('student_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai Ujian');

        // Headers
        $headers = ['No', 'NISN', 'NIS', 'Nama Siswa', 'L/P'];
        foreach ($exam->questions as $q) {
            $headers[] = 'No ' . $q->question_number;
        }
        $headers[] = 'Jml Benar';
        $headers[] = 'Jml Salah';
        $headers[] = 'Nilai Akhir';
        $headers[] = 'Status';

        $sheet->fromArray([$headers], null, 'A1');

        // Style Header
        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0D9488'); // Teal header
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->getColor()->setARGB('FFFFFFFF');

        // Rows
        $rowNum = 2;
        $no = 1;
        foreach ($students as $student) {
            $sub = $submissions->get($student->id);
            $rowData = [
                $no++,
                $student->nisn,
                $student->nis,
                $student->full_name ?? $student->name ?? '-',
                $student->gender,
            ];

            foreach ($exam->questions as $q) {
                $rowData[] = $sub ? ($sub->student_answers[(string)$q->question_number] ?? '') : '';
            }

            $rowData[] = $sub ? $sub->correct_pg_count : 0;
            $rowData[] = $sub ? $sub->wrong_pg_count : 0;
            $rowData[] = $sub ? $sub->total_score : 0;
            $rowData[] = $sub ? ($sub->is_passed ? 'TUNTAS' : 'REMEDIAL') : 'BELUM INPUT';

            $sheet->fromArray([$rowData], null, "A{$rowNum}");
            $rowNum++;
        }

        foreach ($sheet->getColumnIterator() as $col) {
            $sheet->getColumnDimension($col->getColumnIndex())->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Rekap_Ujian_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $exam->title) . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Regrade all submissions for an exam.
     */
    private function regradeAllSubmissions(ExamPackage $exam)
    {
        $submissions = ExamSubmission::where('exam_package_id', $exam->id)->get();
        if ($submissions->isEmpty()) return;

        $questions = $exam->questions->keyBy('question_number');
        $pgQuestions = $questions->filter(fn($q) => $q->question_type !== 'essay');
        $essayQuestions = $questions->filter(fn($q) => $q->question_type === 'essay');

        $totalPgWeight = $pgQuestions->sum('score_weight') ?: 1;
        $totalEssayMaxPoints = $essayQuestions->sum('score_weight') ?: 1;

        foreach ($submissions as $sub) {
            $answers = $sub->student_answers ?? [];
            $essayScoresInput = $sub->essay_scores ?? [];

            $correctPgCount = 0;
            $wrongPgCount = 0;
            $earnedPgPoints = 0;

            foreach ($pgQuestions as $num => $q) {
                $studentAns = $answers[(string)$num] ?? null;
                $correctAns = $q->correct_answer;

                if ($correctAns && $studentAns && $studentAns === $correctAns) {
                    $correctPgCount++;
                    $earnedPgPoints += $q->score_weight;
                } else {
                    $wrongPgCount++;
                }
            }

            $pgScore = $pgQuestions->count() > 0 ? round(($earnedPgPoints / $totalPgWeight) * 100, 2) : 100;

            $earnedEssayPoints = 0;
            if ($essayQuestions->count() > 0) {
                foreach ($essayQuestions as $num => $q) {
                    $score = floatval($essayScoresInput[(string)$num] ?? 0);
                    $earnedEssayPoints += min($score, $q->score_weight);
                }
                $essayScore = round(($earnedEssayPoints / $totalEssayMaxPoints) * 100, 2);
            } else {
                $essayScore = 0;
            }

            if ($essayQuestions->count() > 0 && $exam->essay_weight > 0) {
                $pgPortion = ($pgScore * ($exam->pg_weight / 100));
                $essayPortion = ($essayScore * ($exam->essay_weight / 100));
                $totalScore = round($pgPortion + $essayPortion, 2);
            } else {
                $totalScore = $pgScore;
            }

            $sub->update([
                'correct_pg_count' => $correctPgCount,
                'wrong_pg_count' => $wrongPgCount,
                'pg_score' => $pgScore,
                'essay_score' => $essayScore,
                'total_score' => $totalScore,
                'is_passed' => $totalScore >= $exam->kkm,
            ]);
        }
    }
}
