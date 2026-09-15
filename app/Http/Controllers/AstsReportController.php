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
     * Helper to verify if the user has access to view/manage reports for the given class.
     * When accessed through teacher routes (or by teacher role), strictly verify homeroom ownership.
     * Staff routes (admin, operator, kurikulum, kepala_sekolah) have full access to all classes.
     */
    private function checkHomeroomAccess($user, int $classId, ?Request $request = null): bool
    {
        $isTeacherRoute = ($request && ($request->is('api/teacher/*') || $request->is('teacher/*'))) || $user?->role === 'teacher';

        // Staff route allows full access
        if (!$isTeacherRoute && in_array($user?->role, ['admin', 'operator', 'kurikulum', 'kepala_sekolah'])) {
            return true;
        }

        // On teacher route or for teacher role: strictly restricted to assigned homeroom class
        $teacher = $user ? ($user->teacher ?: \App\Models\Teacher::where('user_id', $user->id)->first()) : null;
        if (!$teacher) {
            return false;
        }
        $homeroomClass = ClassRoom::where('homeroom_teacher_id', $teacher->id)->first();
        return $homeroomClass && (int)$classId === (int)$homeroomClass->id;
    }

    /**
     * Get options: classes, subjects, active academic year, school settings.
     */
    public function options(Request $request)
    {
        $user = $request->user();
        $isTeacherRoute = $request->is('api/teacher/*') || $request->is('teacher/*') || $user?->role === 'teacher';
        $teacher = $user ? ($user->teacher ?: \App\Models\Teacher::where('user_id', $user->id)->first()) : null;
        $homeroomClassId = null;
        if ($teacher) {
            $homeroomClass = ClassRoom::where('homeroom_teacher_id', $teacher->id)->first();
            if ($homeroomClass) {
                $homeroomClassId = $homeroomClass->id;
            }
        }

        if ($isTeacherRoute) {
            if ($homeroomClassId) {
                $classes = ClassRoom::withCount('students')
                    ->where('id', $homeroomClassId)
                    ->get();
            } else {
                $classes = collect();
            }
        } else {
            $classes = ClassRoom::withCount('students')
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get();
        }

        $rawSubjects = Subject::all();
        $subjects = $this->sortSubjectsAccordingToStructure($rawSubjects);

        $activeYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();

        $schoolSetting = SchoolSetting::first();
        $rawSettings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

        $defaultCity = $rawSettings['asts_issued_city'] ?? 'Bogor';
        $defaultDate = $rawSettings['asts_issued_date'] ?? now()->format('Y-m-d');

        return response()->json([
            'status' => 'success',
            'data' => [
                'classes' => $classes,
                'subjects' => $subjects,
                'active_academic_year' => $activeYear,
                'school_setting' => $schoolSetting,
                'settings' => $rawSettings,
                'homeroom_class_id' => $homeroomClassId,
                'is_homeroom_only' => (!$isStaff && $user?->role === 'teacher'),
                'default_titimangsa' => [
                    'city' => $defaultCity,
                    'issued_date' => $defaultDate,
                ],
            ]
        ]);
    }

    /**
     * Helper to classify and sort subjects according to official Madrasah Tsanawiyah curriculum structure.
     */
    private function sortSubjectsAccordingToStructure($subjects)
    {
        return $subjects->sort(function ($a, $b) {
            return $this->getSubjectWeight($a->name) <=> $this->getSubjectWeight($b->name);
        })->values();
    }

    /**
     * Helper to compute order weight for subjects.
     */
    private function getSubjectWeight(string $name): int
    {
        $lower = strtolower($name);

        // 3. Muatan Lokal (Mulok) - Prioritaskan cek BTQ / Baca Tulis Al-Qur'an lebih dulu
        // agar tidak tertangkap oleh kata 'qur' pada Al-Qur'an Hadis PAI
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
     * Ledger matrix for a class: all students x all subjects with their ASTS scores.
     */
    public function ledger(Request $request)
    {
        $classId = $request->input('class_id');
        $semester = $request->input('semester', 'ganjil');

        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id ?? AcademicYear::orderBy('id', 'desc')->value('id'));

        if (!$classId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan pilih kelas terlebih dahulu.'
            ], 400);
        }

        if (!$this->checkHomeroomAccess($request->user(), (int)$classId, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya memiliki izin untuk mengelola Rapor ASTS kelas binaan Anda.'
            ], 403);
        }

        $class = ClassRoom::with('homeroomTeacher')->findOrFail($classId);

        // All students in this class
        $students = Student::where('class_id', $classId)
            ->orderBy('full_name')
            ->get();

        $studentIds = $students->pluck('id');

        // All subjects taught in madrasah, ordered by official structure
        $rawSubjects = Subject::all();
        $subjects = $this->sortSubjectsAccordingToStructure($rawSubjects);

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
            $calcRank = $sData['average_score'] > 0 ? ($rank + 1) : '-';
            $sData['calculated_rank'] = $calcRank;

            $report = $reports->get($sData['student_id']);
            $manualRank = $report?->manual_rank;

            if (!empty($manualRank)) {
                $sData['rank'] = (int) $manualRank;
                $sData['is_manual_rank'] = true;
            } else {
                $sData['rank'] = $calcRank;
                $sData['is_manual_rank'] = false;
            }
        }
        unset($sData);

        // Compute class average
        $totalAverages = array_sum(array_column($ledgerStudents, 'average_score'));
        $classAverageScore = count($ledgerStudents) > 0 ? round($totalAverages / count($ledgerStudents), 2) : 0;

        // Sort back by full_name for clean presentation
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
                'class_average_score' => $classAverageScore,
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
        $scoreSource = $request->input('score_source', 'final'); // 'final' (Nilai Jadi / Rapor) | 'raw' (Nilai Asli / Murni)

        if (!$this->checkHomeroomAccess($request->user(), (int)$classId, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya memiliki izin untuk mengelola Rapor ASTS kelas binaan Anda.'
            ], 403);
        }

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
                    if ($scoreSource === 'raw') {
                        // Nilai Asli (Skor Murni pengerjaan ujian tanpa remedial)
                        $finalScore = floatval($sub->total_score);
                    } else {
                        // Nilai Jadi (Standar Rapor Bebas Remedial: prioritaskan remedial_score)
                        $finalScore = $sub->remedial_score !== null ? floatval($sub->remedial_score) : floatval($sub->total_score);
                    }

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
            $sourceLabel = $scoreSource === 'raw' ? 'Nilai Asli (Murni)' : 'Nilai Jadi (Standar Rapor)';

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menarik {$totalScoresSynced} data {$sourceLabel} dari " . count($uniqueSubjects) . " mata pelajaran ke Rapor ASTS Semester " . ucfirst($semester) . "!",
                'synced_subjects_count' => count($uniqueSubjects),
                'synced_scores_count' => $totalScoresSynced,
                'subjects' => $uniqueSubjects,
                'score_source' => $scoreSource,
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

        $student = Student::findOrFail($request->student_id);
        if (!$this->checkHomeroomAccess($request->user(), (int)$student->class_id, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya memiliki izin untuk mengelola Rapor ASTS kelas binaan Anda.'
            ], 403);
        }

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
     * Save titimangsa (issuing place & date) for report cards.
     */
    public function saveTitimangsa(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'semester' => 'required|string|in:ganjil,genap',
            'city' => 'required|string|max:100',
            'issued_date' => 'required|date',
        ]);

        $classId = $request->input('class_id');
        $semester = $request->input('semester');
        $city = trim($request->input('city'));
        $issuedDate = $request->input('issued_date'); // Y-m-d

        // Save class+semester specific keys
        if ($classId) {
            \App\Models\Setting::updateOrCreate(['key' => "asts_issued_city_{$classId}_{$semester}"], ['value' => $city]);
            \App\Models\Setting::updateOrCreate(['key' => "asts_issued_date_{$classId}_{$semester}"], ['value' => $issuedDate]);
        }

        // Also update default fallback keys
        \App\Models\Setting::updateOrCreate(['key' => 'asts_issued_city'], ['value' => $city]);
        \App\Models\Setting::updateOrCreate(['key' => 'asts_issued_date'], ['value' => $issuedDate]);

        $formatted = \Carbon\Carbon::parse($issuedDate)->locale('id')->translatedFormat('d F Y');

        return response()->json([
            'status' => 'success',
            'message' => "Titimangsa berhasil disimpan: {$city}, {$formatted}",
            'data' => [
                'city' => $city,
                'issued_date' => $formatted,
                'raw_issued_date' => $issuedDate,
            ]
        ]);
    }

    /**
     * Adjust student rankings with optional smart score adjustment.
     */
    public function adjustRanks(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'semester' => 'required|string|in:ganjil,genap',
            'academic_year_id' => 'required|exists:academic_years,id',
            'ranks' => 'required|array|min:1',
            'ranks.*.student_id' => 'required|exists:students,id',
            'ranks.*.rank' => 'required|integer|min:1',
            'adjust_scores' => 'nullable|boolean',
        ]);

        $classId = $request->input('class_id');
        $semester = $request->input('semester');
        $academicYearId = $request->input('academic_year_id');
        $rankList = $request->input('ranks');
        $shouldAdjustScores = (bool) $request->input('adjust_scores', false);

        if (!$this->checkHomeroomAccess($request->user(), (int)$classId, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya memiliki izin untuk mengelola Rapor ASTS kelas binaan Anda.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // 1. Update manual_rank on asts_reports
            foreach ($rankList as $item) {
                AstsReport::updateOrCreate(
                    [
                        'student_id' => $item['student_id'],
                        'academic_year_id' => $academicYearId,
                        'semester' => $semester,
                    ],
                    [
                        'manual_rank' => (int) $item['rank'],
                    ]
                );
            }

            // 2. If smart score adjustment is requested:
            // Adjust subject scores proportionally so that higher ranked students maintain higher or equal averages
            if ($shouldAdjustScores) {
                // Fetch all scores for this class, year, and semester
                $studentIds = array_column($rankList, 'student_id');
                $scores = AstsSubjectScore::whereIn('student_id', $studentIds)
                    ->where('academic_year_id', $academicYearId)
                    ->where('semester', $semester)
                    ->get()
                    ->groupBy('student_id');

                // Sort rankList ascending by desired rank: 1, 2, 3...
                usort($rankList, function ($a, $b) {
                    return $a['rank'] <=> $b['rank'];
                });

                $totalRanks = count($rankList);
                // Baseline target range: rank 1 gets ~93, rank last gets ~75
                $topTarget = 93.0;
                $bottomTarget = max(74.0, $topTarget - ($totalRanks * 1.5));
                $step = $totalRanks > 1 ? ($topTarget - $bottomTarget) / ($totalRanks - 1) : 0;

                foreach ($rankList as $idx => $rItem) {
                    $sId = $rItem['student_id'];
                    $sScores = $scores->get($sId, collect());

                    if ($sScores->isEmpty()) {
                        continue;
                    }

                    $currentAvg = $sScores->avg('score') ?: 75.0;
                    $targetAvg = round($topTarget - ($idx * $step), 1);
                    $factor = $currentAvg > 0 ? ($targetAvg / $currentAvg) : 1.0;

                    foreach ($sScores as $sc) {
                        $newScore = round(max(50, min(99, $sc->score * $factor)), 1);
                        $kkm = $sc->kkm ?? 75;
                        $predicate = $newScore >= 90 ? 'A' : ($newScore >= 80 ? 'B' : ($newScore >= $kkm ? 'C' : 'D'));
                        $description = $newScore >= $kkm ? 'Tercapai dengan sangat memuaskan.' : 'Perlu bimbingan dan peningkatan ketekunan.';

                        $sc->update([
                            'score' => $newScore,
                            'predicate' => $predicate,
                            'description' => $description,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $shouldAdjustScores 
                    ? 'Peringkat siswa dan nilai mata pelajaran berhasil diselaraskan!' 
                    : 'Peringkat manual siswa berhasil diperbarui!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui peringkat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset student rankings back to automatic calculation.
     */
    public function resetRanks(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'semester' => 'required|string|in:ganjil,genap',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $classId = $request->input('class_id');
        $semester = $request->input('semester');
        $academicYearId = $request->input('academic_year_id');

        if (!$this->checkHomeroomAccess($request->user(), (int)$classId, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya memiliki izin untuk mengelola Rapor ASTS kelas binaan Anda.'
            ], 403);
        }

        $studentIds = Student::where('class_id', $classId)->pluck('id');

        AstsReport::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $academicYearId)
            ->where('semester', $semester)
            ->update(['manual_rank' => null]);

        return response()->json([
            'status' => 'success',
            'message' => 'Peringkat siswa telah dikembalikan ke perhitungan otomatis berdasarkan rata-rata nilai!'
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

        if (!$this->checkHomeroomAccess($request->user(), (int)$student->class_id, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya memiliki izin untuk mengelola Rapor ASTS kelas binaan Anda.'
            ], 403);
        }

        $academicYear = AcademicYear::find($yearId) ?? $activeYear;

        // Fetch all students in the same class to accurately determine rank and class average
        $classStudents = Student::where('class_id', $student->class_id)->get();
        $rankData = $this->calculateClassRankings($classStudents, $student->class_id, $semester, $academicYear?->id);

        $report = $this->buildSingleReportData($student, $semester, $academicYear, $rankData);

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

        if (!$this->checkHomeroomAccess($request->user(), (int)$classId, $request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda hanya memiliki izin untuk mengelola Rapor ASTS kelas binaan Anda.'
            ], 403);
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id ?? AcademicYear::orderBy('id', 'desc')->value('id'));

        $class = ClassRoom::with('homeroomTeacher')->findOrFail($classId);
        $students = Student::where('class_id', $classId)->orderBy('full_name')->get();
        $academicYear = AcademicYear::find($yearId) ?? $activeYear;

        $rankData = $this->calculateClassRankings($students, $classId, $semester, $academicYear?->id);

        $reports = [];
        foreach ($students as $student) {
            $student->setRelation('classRoom', $class);
            $reports[] = $this->buildSingleReportData($student, $semester, $academicYear, $rankData);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'class' => $class,
                'semester' => $semester,
                'academic_year' => $academicYear,
                'reports' => $reports,
                'total_students' => count($reports),
                'class_average_score' => $rankData['class_average_score'] ?? 0,
            ]
        ]);
    }

    /**
     * Helper to compute rankings and class average score for a classroom.
     */
    private function calculateClassRankings($students, $classId, string $semester, ?int $yearId): array
    {
        if ($students->isEmpty()) {
            return [
                'ranks' => [],
                'total_students' => 0,
                'class_average_score' => 0,
            ];
        }

        $studentIds = $students->pluck('id');

        $scores = AstsSubjectScore::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $yearId)
            ->where('semester', $semester)
            ->get()
            ->groupBy('student_id');

        $reports = AstsReport::whereIn('student_id', $studentIds)
            ->where('academic_year_id', $yearId)
            ->where('semester', $semester)
            ->get()
            ->keyBy('student_id');

        $studentAverages = [];
        foreach ($students as $s) {
            $sScores = $scores->get($s->id, collect());
            $avg = $sScores->isNotEmpty() ? round($sScores->avg('score'), 2) : 0;
            $studentAverages[] = [
                'student_id' => $s->id,
                'average_score' => $avg,
            ];
        }

        // Sort descending by average
        usort($studentAverages, function ($a, $b) {
            return $b['average_score'] <=> $a['average_score'];
        });

        $ranks = [];
        $totalAvgs = 0;
        foreach ($studentAverages as $pos => $item) {
            $sId = $item['student_id'];
            $totalAvgs += $item['average_score'];
            $calcRank = $item['average_score'] > 0 ? ($pos + 1) : '-';
            $manualRank = $reports->get($sId)?->manual_rank;

            $ranks[$sId] = [
                'rank' => !empty($manualRank) ? (int) $manualRank : $calcRank,
                'calculated_rank' => $calcRank,
                'is_manual_rank' => !empty($manualRank),
                'average_score' => $item['average_score'],
            ];
        }

        $count = count($students);
        $classAvg = $count > 0 ? round($totalAvgs / $count, 2) : 0;

        return [
            'ranks' => $ranks,
            'total_students' => $count,
            'class_average_score' => $classAvg,
        ];
    }

    /**
     * Helper to assemble single student printable report structure.
     */
    private function buildSingleReportData(Student $student, string $semester, ?AcademicYear $academicYear, ?array $rankData = null): array
    {
        $yearId = $academicYear?->id;

        // If rankData wasn't passed, calculate it for this student's class
        if ($rankData === null && $student->class_id) {
            $classStudents = Student::where('class_id', $student->class_id)->get();
            $rankData = $this->calculateClassRankings($classStudents, $student->class_id, $semester, $yearId);
        }

        $sRankInfo = $rankData['ranks'][$student->id] ?? [
            'rank' => '-',
            'calculated_rank' => '-',
            'is_manual_rank' => false,
        ];

        // Fetch scores
        $scores = AstsSubjectScore::where('student_id', $student->id)
            ->where('academic_year_id', $yearId)
            ->where('semester', $semester)
            ->with('subject')
            ->get();

        // Fetch subjects sorted by official structure
        $rawSubjects = Subject::all();
        $allSubjects = $this->sortSubjectsAccordingToStructure($rawSubjects);

        $scoreMap = $scores->keyBy('subject_id');

        $totalScore = 0;
        $countScore = 0;

        $paiSubjects = [];
        $generalSubjectsA = [];
        $groupB = [];
        $mulokSubjects = [];

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

            $weight = $this->getSubjectWeight($sbj->name);

            if ($weight >= 10 && $weight <= 19) {
                // 1. PAI Sub-items: Al-Qur'an Hadis, Akidah Akhlak, Fikih, SKI
                $paiSubjects[] = $sbjItem;
            } elseif ($weight >= 20 && $weight <= 100) {
                // 2. Kelompok Wajib A Umum: PPKn, B.Indo, B.Arab, MTK, IPA, IPS, B.Inggris
                $generalSubjectsA[] = $sbjItem;
            } elseif ($weight >= 110 && $weight <= 200) {
                // 3. Kelompok Wajib B: Seni Budaya, PJOK, Informatika
                $groupB[] = $sbjItem;
            } else {
                // 4. Pilihan Mulok: Bahasa Sunda, BTQ, dll.
                $mulokSubjects[] = $sbjItem;
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
        $principalTeacher = !empty($rawSettings['principal_teacher_id']) 
            ? \App\Models\Teacher::find($rawSettings['principal_teacher_id']) 
            : \App\Models\Teacher::where('position', 'like', '%Kepala%')->first();

        $avg = $countScore > 0 ? round($totalScore / $countScore, 2) : 0;

        // Titimangsa (Tempat & Tanggal Terbit Rapor)
        $cityKey = $student->class_id ? "asts_issued_city_{$student->class_id}_{$semester}" : null;
        $dateKey = $student->class_id ? "asts_issued_date_{$student->class_id}_{$semester}" : null;

        $cityVal = ($cityKey && !empty($rawSettings[$cityKey])) 
            ? $rawSettings[$cityKey] 
            : ($rawSettings['asts_issued_city'] ?? 'Bogor');

        $dateVal = ($dateKey && !empty($rawSettings[$dateKey])) 
            ? $rawSettings[$dateKey] 
            : ($rawSettings['asts_issued_date'] ?? null);

        $formattedDate = $dateVal 
            ? \Carbon\Carbon::parse($dateVal)->locale('id')->translatedFormat('d F Y') 
            : now()->locale('id')->translatedFormat('d F Y');

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
            'semester_label' => $semester === 'genap' ? 'Genap' : 'Ganjil',
            'subjects_pai' => $paiSubjects,
            'subjects_group_a' => $generalSubjectsA,
            'subjects_group_b' => $groupB,
            'subjects_mulok' => $mulokSubjects,
            'total_score' => $totalScore,
            'average_score' => $avg,
            'rank' => $sRankInfo['rank'],
            'calculated_rank' => $sRankInfo['calculated_rank'],
            'is_manual_rank' => $sRankInfo['is_manual_rank'],
            'total_students' => $rankData['total_students'] ?? 0,
            'class_average_score' => $rankData['class_average_score'] ?? 0,
            'attendance' => [
                'sick' => $sickCount,
                'permission' => $permissionCount,
                'unexcused' => $unexcusedCount,
            ],
            'homeroom_notes' => $astsReport?->homeroom_notes ?? 'Tingkatkan terus semangat belajar, ketekunan ibadah, dan keaktifan dalam kegiatan madrasah.',
            'school_setting' => [
                'school_name' => $rawSettings['app_name'] ?? ($schoolSetting?->school_name ?? 'MTs AL - HASANAH'),
                'foundation_name' => $rawSettings['school_foundation'] ?? 'YAYASAN PENDIDIKAN ISLAM AL - HASANAH',
                'address' => $rawSettings['school_address'] ?? ($schoolSetting?->address ?? 'Jl. Ciapus Sukamakmur No.05, Kec. Ciomas, Kab. Bogor'),
                'phone' => $rawSettings['school_phone'] ?? '081617666017',
                'email' => $rawSettings['school_email'] ?? 'mtsalhasanah.ciomas@gmail.com',
                'principal_name' => $principalTeacher?->full_name ?? 'H. Umar Usman Ali, S.Pd, S.Pd.I',
                'principal_nip' => $principalTeacher?->nip ?? '-',
                'logo_url' => $rawSettings['app_logo'] ?? ($schoolSetting?->logo_url ?? null),
            ],
            'issued_date' => $formattedDate,
            'raw_issued_date' => $dateVal ?: now()->format('Y-m-d'),
            'city' => $cityVal,
        ];
    }
}
