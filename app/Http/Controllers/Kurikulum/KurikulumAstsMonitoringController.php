<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;
use App\Models\SubjectGradeKkm;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\AstsReport;
use App\Models\AstsSubjectScore;

class KurikulumAstsMonitoringController extends Controller
{
    /**
     * Helper to compute sort weight for subjects in curriculum structure.
     */
    private function getSubjectWeight(string $name): int
    {
        $lower = strtolower($name);
        if (str_contains($lower, 'baca tulis') || str_contains($lower, 'btq') || str_contains($lower, 'bta') || str_contains($lower, 'tahfidz')) return 220;
        if (str_contains($lower, 'sunda') || str_contains($lower, 'jawa') || str_contains($lower, 'daerah') || str_contains($lower, 'mulok')) return 210;

        // Kelompok Wajib A - Pendidikan Agama Islam (PAI)
        if (str_contains($lower, 'hadis') || str_contains($lower, 'hadits') || (str_contains($lower, 'qur') && !str_contains($lower, 'baca'))) return 10;
        if (str_contains($lower, 'akidah') || str_contains($lower, 'akhlak')) return 11;
        if (str_contains($lower, 'fikih') || str_contains($lower, 'fiqih')) return 12;
        if (str_contains($lower, 'sejarah kebudayaan') || str_contains($lower, 'ski')) return 13;

        // Kelompok Wajib A - Mapel Umum
        if (str_contains($lower, 'pancasila') || str_contains($lower, 'pkn') || str_contains($lower, 'kewarganegaraan')) return 20;
        if (str_contains($lower, 'bahasa indonesia') || $lower === 'indonesia') return 30;
        if (str_contains($lower, 'bahasa arab') || $lower === 'arab') return 40;
        if (str_contains($lower, 'matematika') || str_contains($lower, 'mtk')) return 50;
        if (str_contains($lower, 'alam') || str_contains($lower, 'ipa')) return 60;
        if (str_contains($lower, 'sosial') || str_contains($lower, 'ips')) return 70;
        if (str_contains($lower, 'inggris') || str_contains($lower, 'english')) return 80;

        // Kelompok Wajib B
        if (str_contains($lower, 'seni') || str_contains($lower, 'prakarya') || str_contains($lower, 'sbdp')) return 110;
        if (str_contains($lower, 'jasmani') || str_contains($lower, 'olahraga') || str_contains($lower, 'pjok') || str_contains($lower, 'penjas')) return 120;
        if (str_contains($lower, 'informatika') || str_contains($lower, 'komputer') || str_contains($lower, 'tik')) return 130;

        return 300;
    }

    /**
     * Get the full matrix of KKTP / KKM per Tingkat (7, 8, 9) for all subjects.
     */
    public function getKktpMatrix(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id);

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        $rawSubjects = Subject::all();
        $sortedSubjects = $rawSubjects->sort(function ($a, $b) {
            return $this->getSubjectWeight($a->name) <=> $this->getSubjectWeight($b->name);
        })->values();

        // Fetch existing KKM records for this academic year (or fallback to null year)
        $existingKkms = SubjectGradeKkm::where(function ($q) use ($yearId) {
            if ($yearId) {
                $q->where('academic_year_id', $yearId)
                  ->orWhereNull('academic_year_id');
            } else {
                $q->whereNull('academic_year_id');
            }
        })->get();

        // Group by subject_id and grade_level, prioritizing specific academic_year_id
        $kkmLookup = [];
        foreach ($existingKkms as $row) {
            $sId = $row->subject_id;
            $grade = (string) $row->grade_level;

            // If we don't have a value yet or this one has specific yearId, set it
            if (!isset($kkmLookup[$sId][$grade]) || $row->academic_year_id == $yearId) {
                $kkmLookup[$sId][$grade] = floatval($row->kkm);
            }
        }

        $matrix = $sortedSubjects->map(function ($subject) use ($kkmLookup) {
            $defaultPassing = floatval($subject->passing_grade ?: 75);
            $lower = strtolower($subject->name);
            $isArab = str_contains($lower, 'bahasa arab') || str_contains($lower, 'arab') || strtolower($subject->code) === 'ba';

            $kkm7 = $kkmLookup[$subject->id]['7'] ?? ($isArab ? 70.00 : $defaultPassing);
            $kkm8 = $kkmLookup[$subject->id]['8'] ?? $defaultPassing;
            $kkm9 = $kkmLookup[$subject->id]['9'] ?? $defaultPassing;

            $weight = $this->getSubjectWeight($subject->name);
            $group = 'Kelompok B (Keterampilan/Seni)';
            if ($weight >= 10 && $weight <= 19) $group = 'Kelompok A (PAI)';
            elseif ($weight >= 20 && $weight <= 100) $group = 'Kelompok A (Umum)';
            elseif ($weight >= 200) $group = 'Muatan Lokal (Mulok)';

            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'group' => $group,
                'default_passing_grade' => $defaultPassing,
                'kkm_7' => floatval($kkm7),
                'kkm_8' => floatval($kkm8),
                'kkm_9' => floatval($kkm9),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'matrix' => $matrix,
                'academic_years' => $academicYears,
                'active_year' => $activeYear,
                'selected_year_id' => $yearId,
                'grades' => ['7', '8', '9'],
            ]
        ]);
    }

    /**
     * Batch save / update KKTP / KKM matrix per grade level.
     */
    public function saveKktpMatrix(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'items' => 'required|array|min:1',
            'items.*.subject_id' => 'required|exists:subjects,id',
            'items.*.kkm_7' => 'required|numeric|min:0|max:100',
            'items.*.kkm_8' => 'required|numeric|min:0|max:100',
            'items.*.kkm_9' => 'required|numeric|min:0|max:100',
        ]);

        $yearId = $request->input('academic_year_id');
        $items = $request->input('items');

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $subjectId = $item['subject_id'];
                $gradeMap = [
                    '7' => floatval($item['kkm_7']),
                    '8' => floatval($item['kkm_8']),
                    '9' => floatval($item['kkm_9']),
                ];

                foreach ($gradeMap as $grade => $kkmVal) {
                    // 1. Save for specified academic year
                    if ($yearId) {
                        SubjectGradeKkm::updateOrCreate(
                            [
                                'subject_id' => $subjectId,
                                'grade_level' => $grade,
                                'academic_year_id' => $yearId,
                            ],
                            [
                                'kkm' => $kkmVal,
                            ]
                        );
                    }

                    // 2. Also keep global fallback row synchronized
                    SubjectGradeKkm::updateOrCreate(
                        [
                            'subject_id' => $subjectId,
                            'grade_level' => $grade,
                            'academic_year_id' => null,
                        ],
                        [
                            'kkm' => $kkmVal,
                        ]
                    );
                }

                // If subject is Bahasa Arab and grade 7 is 70, keep subject passing_grade = 70 as well
                $sbj = Subject::find($subjectId);
                if ($sbj) {
                    $lower = strtolower($sbj->name);
                    if (str_contains($lower, 'bahasa arab') || str_contains($lower, 'arab') || strtolower($sbj->code) === 'ba') {
                        $sbj->passing_grade = (int) $gradeMap['7'];
                        $sbj->saveQuietly();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Matriks KKTP / KKM per tingkat (7, 8, 9) berhasil disimpan!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan matriks KKTP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Live Monitoring Overview of ASTS Report Progress for all classes.
     */
    public function getMonitoringOverview(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();
        $yearId = $request->input('academic_year_id', $activeYear?->id);
        $semester = $request->input('semester', 'ganjil');

        $totalSubjectsCount = Subject::count();

        // Get all classes
        $classes = ClassRoom::with(['homeroomTeacher'])
            ->withCount(['students'])
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        $classesMonitoring = [];
        $totalStudentsAll = 0;
        $completeClassesCount = 0;
        $partialClassesCount = 0;
        $emptyClassesCount = 0;
        $allClassAverages = [];

        foreach ($classes as $c) {
            // Count students (including local class mapping if applicable)
            $lokalCount = Student::where('lokal_class_id', $c->id)->count();
            $effectiveStudentsCount = ($lokalCount > 0) ? $lokalCount : $c->students_count;
            $totalStudentsAll += $effectiveStudentsCount;

            // Get students IDs
            $studentIds = Student::where(function ($q) use ($c) {
                $q->where('class_id', $c->id)
                  ->orWhere('lokal_class_id', $c->id);
            })->pluck('id');

            // Find deposited ASTS scores for these students
            $scores = AstsSubjectScore::whereIn('student_id', $studentIds)
                ->where('academic_year_id', $yearId)
                ->where('semester', $semester)
                ->get();

            $distinctSubjectsDeposited = $scores->pluck('subject_id')->unique()->count();
            $totalScoresCount = $scores->count();
            $avgClassScore = $scores->count() > 0 ? round($scores->avg('score'), 1) : 0;

            if ($avgClassScore > 0) {
                $allClassAverages[] = $avgClassScore;
            }

            // Status calculation
            $progressPercent = $totalSubjectsCount > 0 ? round(($distinctSubjectsDeposited / $totalSubjectsCount) * 100) : 0;
            if ($progressPercent >= 100 || ($totalSubjectsCount > 0 && $distinctSubjectsDeposited >= ($totalSubjectsCount - 1))) {
                $status = 'complete'; // All or almost all deposited
                $completeClassesCount++;
            } elseif ($distinctSubjectsDeposited > 0) {
                $status = 'partial';
                $partialClassesCount++;
            } else {
                $status = 'empty';
                $emptyClassesCount++;
            }

            // Titimangsa check from settings
            $hasTitimangsa = !empty(\App\Models\Setting::where('key', "asts_issued_date_{$c->id}_{$semester}")->value('value'))
                || !empty(\App\Models\Setting::where('key', 'asts_issued_date')->value('value'));

            $classesMonitoring[] = [
                'id' => $c->id,
                'name' => $c->name,
                'grade_level' => $c->grade_level,
                'homeroom_teacher' => $c->homeroomTeacher?->name ?? 'Belum Ditentukan',
                'students_count' => $effectiveStudentsCount,
                'total_subjects' => $totalSubjectsCount,
                'deposited_subjects' => $distinctSubjectsDeposited,
                'deposited_scores_count' => $totalScoresCount,
                'progress_percent' => $progressPercent,
                'class_avg_score' => $avgClassScore,
                'status' => $status,
                'has_titimangsa' => $hasTitimangsa,
            ];
        }

        $overallAverage = count($allClassAverages) > 0 ? round(array_sum($allClassAverages) / count($allClassAverages), 1) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'total_classes' => $classes->count(),
                    'complete_classes' => $completeClassesCount,
                    'partial_classes' => $partialClassesCount,
                    'empty_classes' => $emptyClassesCount,
                    'total_students' => $totalStudentsAll,
                    'total_subjects' => $totalSubjectsCount,
                    'school_average' => $overallAverage,
                ],
                'classes' => $classesMonitoring,
                'academic_years' => $academicYears,
                'active_year' => $activeYear,
                'selected_year_id' => $yearId,
                'selected_semester' => $semester,
            ]
        ]);
    }
}
