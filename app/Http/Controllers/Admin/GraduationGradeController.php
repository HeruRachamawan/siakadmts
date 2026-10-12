<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClassRoom;
use App\Models\GraduationGrade;
use App\Models\GraduationSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Http\Controllers\AstsReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GraduationGradeController extends BaseController
{
    /**
     * Get graduation weight settings and subject reference list.
     */
    public function getSettings()
    {
        $setting = GraduationSetting::getActiveSettings();
        $subjects = Subject::orderBy('name')->get(['id', 'name', 'code']);

        return $this->success([
            'setting' => $setting,
            'subjects' => $subjects,
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
            'practical_subject_ids' => 'nullable|array',
            'practical_subject_ids.*' => 'integer|exists:subjects,id',
            'custom_subject_weights' => 'nullable|array',
            'date_place' => 'nullable|string|max:100',
            'graduation_date' => 'nullable|date',
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
            'practical_subject_ids' => $validated['practical_subject_ids'] ?? [],
            'custom_subject_weights' => $validated['custom_subject_weights'] ?? [],
            'date_place' => $validated['date_place'] ?? 'Bogor',
            'graduation_date' => $validated['graduation_date'] ?? now()->toDateString(),
        ]);

        // Auto-recalculate all existing graduation grades with new weights
        $grades = GraduationGrade::all();
        foreach ($grades as $g) {
            $g->recalculateScores($setting);
            $g->save();
        }

        return $this->success($setting, 'Pengaturan bobot kelulusan & ijazah berhasil diperbarui.');
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

        // 3. Subjects list
        $subjects = Subject::orderBy('name')->get(['id', 'name', 'code']);

        // 4. Grades
        $gradesQuery = GraduationGrade::whereIn('student_id', $studentIds);
        if ($request->filled('subject_id')) {
            $gradesQuery->where('subject_id', (int)$request->query('subject_id'));
        }
        $grades = $gradesQuery->get();

        // 5. Settings & School Identity
        $setting = GraduationSetting::getActiveSettings();
        $schoolSetting = AstsReportController::getOfficialSchoolSetting();

        return $this->success([
            'classes' => $grade9Classes,
            'subjects' => $subjects,
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
        $studentIds = $students->pluck('id')->toArray();
        $subjects = Subject::orderBy('name')->get();
        $grades = GraduationGrade::whereIn('student_id', $studentIds)->get();

        $setting = GraduationSetting::getActiveSettings();

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
}
