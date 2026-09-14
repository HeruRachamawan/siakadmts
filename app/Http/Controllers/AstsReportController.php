<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AstsReport;
use App\Models\AstsSubjectScore;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ExamPackage;
use App\Models\ExamSubmission;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AstsReportController extends Controller
{
    /**
     * Get options: classes, subjects, active academic year, school settings.
     */
    public function options(Request $request)
    {
        $classes = ClassRoom::withCount('students')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $subjects = Subject::orderBy('name')->get();

        $activeYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();

        $schoolSetting = SchoolSetting::first();
        $rawSettings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'classes' => $classes,
                'subjects' => $subjects,
                'active_academic_year' => $activeYear,
                'school_setting' => $schoolSetting,
                'settings' => $rawSettings,
            ]
        ]);
    }

    /**
     * Ledger matrix for entire class.
     */
    public function ledger(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'semester' => 'nullable|string|in:ganjil,genap',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $classId = $request->class_id;
        $semester = $request->input('semester', 'ganjil');

        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id ?? AcademicYear::orderBy('id', 'desc')->value('id'));

        $class = ClassRoom::with('homeroomTeacher')->findOrFail($classId);

        // All students in this class
        $students = Student::where('class_id', $classId)
            ->orderBy('full_name')
            ->get();

        $studentIds = $students->pluck('id');

        // All subjects taught in madrasah
        $subjects = Subject::orderBy('name')->get();

        // Existing ASTS Scores
        $scores = AstsSubjectScore::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $yearId)
            ->where('semester', $semester)
            ->with(['examPackage'])
            ->get();

        // Existing ASTS Reports (Notes & Attendance overrides)
        $reports = AstsReport::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $yearId)
            ->where('semester', $semester)
            ->get()
            ->keyBy('student_id');

        // Auto attendance from attendances table
        $attendanceData = Attendance::whereIn('student_id', $studentIds)
            ->select('student_id', 'status', DB::raw('count(*) as count'))
            ->groupBy('student_id', 'status')
            ->get();

        $attMap = [];
        foreach ($attendanceData as $att) {
            $attMap[$att->student_id][$att->status] = $att->count;
        }

        // Map scores by student_id and subject_id
        $scoreMap = [];
        $subjectStatusMap = [];
        foreach ($subjects as $sbj) {
            $subjectStatusMap[$sbj->id] = [
                'subject_id' => $sbj->id,
                'subject_name' => $sbj->name,
                'subject_code' => $sbj->code,
                'has_scores' => false,
                'synced_count' => 0,
                'last_synced_at' => null,
            ];
        }

        foreach ($scores as $sc) {
            $scoreMap[$sc->student_id][$sc->subject_id] = [
                'score' => floatval($sc->score),
                'kkm' => floatval($sc->kkm),
                'predicate' => $sc->predicate,
                'description' => $sc->description,
                'exam_package_id' => $sc->exam_package_id,
                'synced_at' => $sc->synced_at ? $sc->synced_at->format('d/m/Y H:i') : null,
            ];

            if (isset($subjectStatusMap[$sc->subject_id])) {
                $subjectStatusMap[$sc->subject_id]['has_scores'] = true;
                $subjectStatusMap[$sc->subject_id]['synced_count']++;
                if ($sc->synced_at) {
                    $subjectStatusMap[$sc->subject_id]['last_synced_at'] = $sc->synced_at->format('d/m/Y H:i');
                }
            }
        }

        // Build student ledger rows
        $ledgerStudents = [];
        foreach ($students as $idx => $student) {
            $studentScores = [];
            $totalScore = 0;
            $subjectCountWithScore = 0;

            foreach ($subjects as $sbj) {
                $val = $scoreMap[$student->id][$sbj->id] ?? null;
                $studentScores[$sbj->id] = $val;
                if ($val !== null && isset($val['score'])) {
                    $totalScore += $val['score'];
                    $subjectCountWithScore++;
                }
            }

            $avgScore = $subjectCountWithScore > 0 ? round($totalScore / $subjectCountWithScore, 2) : 0;

            $report = $reports->get($student->id);
            $rawAtt = $attMap[$student->id] ?? [];

            $ledgerStudents[] = [
                'student_id' => $student->id,
                'full_name' => $student->full_name,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'gender' => $student->gender,
                'scores' => $studentScores,
                'total_score' => $totalScore,
                'average_score' => $avgScore,
                'subjects_filled_count' => $subjectCountWithScore,
                'homeroom_notes' => $report?->homeroom_notes ?? '',
                'sick_count' => $report?->sick_count ?? ($rawAtt['sakit'] ?? 0),
                'permission_count' => $report?->permission_count ?? ($rawAtt['izin'] ?? 0),
                'unexcused_count' => $report?->unexcused_count ?? ($rawAtt['alpa'] ?? 0),
            ];
        }

        // Calculate rankings based on average_score
        usort($ledgerStudents, function ($a, $b) {
            return $b['average_score'] <=> $a['average_score'];
        });

        foreach ($ledgerStudents as $rank => &$sData) {
            $sData['rank'] = $sData['average_score'] > 0 ? ($rank + 1) : '-';
        }
        unset($sData);

        // Sort back by full_name for clean alphabetical presentation
        usort($ledgerStudents, function ($a, $b) {
            return strcasecmp($a['full_name'], $b['full_name']);
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'class' => $class,
                'semester' => $semester,
                'academic_year_id' => $yearId,
                'subjects' => $subjects,
                'subject_statuses' => array_values($subjectStatusMap),
                'students' => $ledgerStudents,
                'total_students' => count($ledgerStudents),
            ]
        ]);
    }

    /**
     * Auto-pull all STS exam package final scores for this class & semester into ASTS.
     */
    public function autoPullClassScores(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'semester' => 'nullable|string|in:ganjil,genap',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $classId = $request->class_id;
        $semester = $request->input('semester', 'ganjil');

        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id ?? AcademicYear::orderBy('id', 'desc')->value('id'));

        // Find all STS exam packages for this class
        $exams = ExamPackage::where('class_room_id', $classId)
            ->where(function ($q) use ($semester) {
                $q->where('exam_type', 'sts')
                  ->orWhere('title', 'like', '%ASTS%')
                  ->orWhere('title', 'like', '%STS%')
                  ->orWhere('title', 'like', '%Tengah Semester%');
            })
            ->where('semester', $semester)
            ->where('academic_year_id', $yearId)
            ->with(['submissions', 'subject'])
            ->get();

        if ($exams->isEmpty()) {
            return response()->json([
                'status' => 'warning',
                'message' => "Tidak ditemukan paket ujian STS/ASTS untuk Kelas ini pada Semester " . ucfirst($semester) . ". Silakan pastikan paket ujian sudah dibuat di modul Koreksi Ujian.",
                'synced_subjects_count' => 0,
                'synced_scores_count' => 0,
            ]);
        }

        $totalScoresSynced = 0;
        $syncedSubjects = [];

        DB::beginTransaction();
        try {
            foreach ($exams as $exam) {
                foreach ($exam->submissions as $sub) {
                    $finalScore = $sub->remedial_score !== null ? floatval($sub->remedial_score) : floatval($sub->total_score);

                    $kkm = floatval($exam->kkm ?? 75);
                    $predicate = 'D';
                    if ($finalScore >= 90) {
                        $predicate = 'A';
                    } elseif ($finalScore >= 80) {
                        $predicate = 'B';
                    } elseif ($finalScore >= $kkm) {
                        $predicate = 'C';
                    }

                    $description = $finalScore >= $kkm
                        ? 'Menunjukkan penguasaan kompetensi yang tuntas pada asesmen tengah semester.'
                        : 'Perlu peningkatan dan pendampingan materi lebih lanjut.';

                    AstsSubjectScore::updateOrCreate(
                        [
                            'student_id' => $sub->student_id,
                            'subject_id' => $exam->subject_id,
                            'academic_year_id' => $yearId,
                            'semester' => $semester,
                        ],
                        [
                            'score' => $finalScore,
                            'kkm' => $kkm,
                            'predicate' => $predicate,
                            'description' => $description,
                            'exam_package_id' => $exam->id,
                            'synced_at' => now(),
                        ]
                    );

                    $totalScoresSynced++;
                }

                $syncedSubjects[] = $exam->subject?->name ?? 'Mapel #' . $exam->subject_id;
            }

            DB::commit();

            $uniqueSubjects = array_unique($syncedSubjects);

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menarik {$totalScoresSynced} nilai dari " . count($uniqueSubjects) . " mata pelajaran ke Rapor ASTS Semester " . ucfirst($semester) . "!",
                'synced_subjects_count' => count($uniqueSubjects),
                'synced_scores_count' => $totalScoresSynced,
                'subjects' => $uniqueSubjects,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menarik nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save homeroom notes & attendance overrides.
     */
    public function saveNotes(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'semester' => 'required|string|in:ganjil,genap',
            'academic_year_id' => 'required|exists:academic_years,id',
            'homeroom_notes' => 'nullable|string',
            'sick_count' => 'nullable|integer|min:0',
            'permission_count' => 'nullable|integer|min:0',
            'unexcused_count' => 'nullable|integer|min:0',
        ]);

        $report = AstsReport::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'academic_year_id' => $request->academic_year_id,
                'semester' => $request->semester,
            ],
            [
                'homeroom_notes' => $request->homeroom_notes,
                'sick_count' => $request->sick_count,
                'permission_count' => $request->permission_count,
                'unexcused_count' => $request->unexcused_count,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Catatan wali kelas dan presensi berhasil disimpan!',
            'data' => $report
        ]);
    }

    /**
     * Single student report card for print.
     */
    public function studentReport(Request $request, $studentId)
    {
        $semester = $request->input('semester', 'ganjil');

        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id ?? AcademicYear::orderBy('id', 'desc')->value('id'));

        $student = Student::with(['classRoom.homeroomTeacher'])->findOrFail($studentId);
        $academicYear = AcademicYear::find($yearId) ?? $activeYear;

        $report = $this->buildSingleReportData($student, $semester, $academicYear);

        return response()->json([
            'status' => 'success',
            'data' => $report
        ]);
    }

    /**
     * Batch reports for an entire class (for multi-page print).
     */
    public function batchClassReport(Request $request, $classId)
    {
        $semester = $request->input('semester', 'ganjil');

        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id ?? AcademicYear::orderBy('id', 'desc')->value('id'));

        $class = ClassRoom::with('homeroomTeacher')->findOrFail($classId);
        $students = Student::where('class_id', $classId)->orderBy('full_name')->get();
        $academicYear = AcademicYear::find($yearId) ?? $activeYear;

        $reports = [];
        foreach ($students as $student) {
            $student->setRelation('classRoom', $class);
            $reports[] = $this->buildSingleReportData($student, $semester, $academicYear);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'class' => $class,
                'semester' => $semester,
                'academic_year' => $academicYear,
                'reports' => $reports,
                'total_students' => count($reports),
            ]
        ]);
    }

    /**
     * Helper to assemble single student printable report structure.
     */
    private function buildSingleReportData(Student $student, string $semester, ?AcademicYear $academicYear): array
    {
        $yearId = $academicYear?->id;

        // Fetch scores
        $scores = AstsSubjectScore::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->where('semester', $semester)
            ->with('subject')
            ->get();

        // Fetch subjects
        $allSubjects = Subject::orderBy('name')->get();

        // PAI / Keagamaan keywords
        $paiKeywords = ['qur', 'hadis', 'akidah', 'akhlak', 'fikih', 'fiqih', 'ski', 'sejarah kebudayaan', 'arab'];

        $groupA = []; // Kelompok A: Agama / PAI & Wajib
        $groupB = []; // Kelompok B: Umum & Muatan Lokal

        $scoreMap = $scores->keyBy('subject_id');

        $totalScore = 0;
        $countScore = 0;

        foreach ($allSubjects as $sbj) {
            $sc = $scoreMap->get($sbj->id);
            $scoreVal = $sc ? floatval($sc->score) : null;
            $kkmVal = $sc ? floatval($sc->kkm) : floatval($sbj->passing_grade ?? 75);
            $predicateVal = $sc?->predicate ?? ($scoreVal !== null ? ($scoreVal >= 90 ? 'A' : ($scoreVal >= 80 ? 'B' : ($scoreVal >= $kkmVal ? 'C' : 'D'))) : '-');
            $descVal = $sc?->description ?? ($scoreVal !== null ? ($scoreVal >= $kkmVal ? 'Tercapai dengan baik.' : 'Perlu bimbingan lanjutan.') : '-');

            if ($scoreVal !== null) {
                $totalScore += $scoreVal;
                $countScore++;
            }

            $sbjItem = [
                'subject_id' => $sbj->id,
                'name' => $sbj->name,
                'code' => $sbj->code,
                'kkm' => $kkmVal,
                'score' => $scoreVal,
                'predicate' => $predicateVal,
                'description' => $descVal,
                'is_passed' => $scoreVal !== null && $scoreVal >= $kkmVal,
            ];

            $lower = strtolower($sbj->name);
            $isPai = false;
            foreach ($paiKeywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $isPai = true;
                    break;
                }
            }

            if ($isPai) {
                $groupA[] = $sbjItem;
            } else {
                $groupB[] = $sbjItem;
            }
        }

        // Homeroom notes & attendance
        $astsReport = AstsReport::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->where('semester', $semester)
            ->first();

        // Fallback auto attendance
        $rawAtt = Attendance::where('student_id', $student->id)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $sickCount = $astsReport?->sick_count ?? ($rawAtt['sakit'] ?? 0);
        $permissionCount = $astsReport?->permission_count ?? ($rawAtt['izin'] ?? 0);
        $unexcusedCount = $astsReport?->unexcused_count ?? ($rawAtt['alpa'] ?? 0);

        $schoolSetting = SchoolSetting::first();
        $rawSettings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

        $avg = $countScore > 0 ? round($totalScore / $countScore, 2) : 0;

        return [
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'gender' => $student->gender,
                'class_name' => $student->classRoom?->name ?? '-',
                'homeroom_teacher_name' => $student->classRoom?->homeroomTeacher?->full_name ?? '-',
                'homeroom_teacher_nip' => $student->classRoom?->homeroomTeacher?->nip ?? '-',
            ],
            'academic_year' => $academicYear?->year ?? '2026/2027',
            'semester' => $semester,
            'semester_label' => $semester === 'genap' ? 'Genap (Dua)' : 'Ganjil (Satu)',
            'subjects_group_a' => $groupA,
            'subjects_group_b' => $groupB,
            'total_score' => $totalScore,
            'average_score' => $avg,
            'attendance' => [
                'sick' => $sickCount,
                'permission' => $permissionCount,
                'unexcused' => $unexcusedCount,
            ],
            'homeroom_notes' => $astsReport?->homeroom_notes ?? 'Tingkatkan terus semangat belajar, ketekunan ibadah, dan keaktifan dalam kegiatan madrasah.',
            'school_setting' => [
                'school_name' => $schoolSetting?->school_name ?? ($rawSettings['school_name'] ?? 'MADRASAH TSANAWIYAH AL-HASANAH'),
                'foundation_name' => $rawSettings['school_foundation'] ?? 'YAYASAN PENDIDIKAN ISLAM',
                'address' => $schoolSetting?->address ?? ($rawSettings['school_address'] ?? 'Jl. Raya Ciomas No. 123, Kab. Bogor'),
                'phone' => $schoolSetting?->phone ?? ($rawSettings['school_phone'] ?? '(0251) 8631234'),
                'email' => $schoolSetting?->email ?? ($rawSettings['school_email'] ?? 'info@mtsalhasanah.sch.id'),
                'principal_name' => $schoolSetting?->principal_name ?? ($rawSettings['principal_name'] ?? 'H. Ahmad Fauzi, M.Pd.I'),
                'principal_nip' => $schoolSetting?->principal_nip ?? ($rawSettings['principal_nip'] ?? '197508152005011004'),
                'logo_url' => $schoolSetting?->logo_url ?? ($rawSettings['app_logo'] ?? null),
            ],
            'issued_date' => now()->translatedFormat('d F Y'),
            'city' => 'Bogor',
        ];
    }
}
