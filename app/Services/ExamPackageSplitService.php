<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\ExamPackage;
use App\Models\ExamQuestion;
use App\Models\ExamSubmission;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExamPackageSplitService
{
    /**
     * Resolve the assigned teacher for a specific class and subject from TeacherSubjectClass.
     */
    public function resolveTeacherForClassAndSubject(int $classId, int $subjectId, int $fallbackTeacherId): int
    {
        $assigned = TeacherSubjectClass::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->value('teacher_id');

        return $assigned ? (int) $assigned : $fallbackTeacherId;
    }

    /**
     * Check whether a ClassRoom represents a standalone lokal bucket (e.g. "7", "8", "Kelas 7 Lokal") rather than a regular rombel.
     */
    public function isStandaloneLokalBucket(ClassRoom $c): bool
    {
        $raw = trim((string) $c->name);
        $clean = strtolower(preg_replace('/^(kelas|kls)\s*/i', '', $raw));
        $lower = strtolower($raw);

        if (in_array($clean, ['7', '8', '9'], true)) {
            return true;
        }
        return str_contains($lower, 'lokal') || str_starts_with($lower, 'l-') || str_starts_with($lower, 'lok-');
    }

    /**
     * Resolve target classes for a Global grade level (e.g. "9" -> [9A, 9B]).
     */
    public function getGradeClasses(string $gradeLevel, ?array $selectedClassIds = null): Collection
    {
        $query = ClassRoom::where('grade_level', $gradeLevel)->orderBy('name');
        if (!empty($selectedClassIds)) {
            $query->whereIn('id', array_map('intval', $selectedClassIds));
            return $query->get();
        }

        $all = $query->get();
        $regular = $all->reject(fn(ClassRoom $c) => $this->isStandaloneLokalBucket($c))->values();
        return $regular->isNotEmpty() ? $regular : $all;
    }

    /**
     * Copy/sync questions from master package into a child correction package.
     */
    public function copyQuestionsToTarget(ExamPackage $master, ExamPackage $target): void
    {
        $masterQuestions = $master->questions()->orderBy('question_number')->get();
        $numbers = [];

        foreach ($masterQuestions as $mq) {
            $numbers[] = $mq->question_number;
            ExamQuestion::updateOrCreate(
                [
                    'exam_package_id' => $target->id,
                    'question_number' => $mq->question_number,
                ],
                [
                    'question_type' => $mq->question_type,
                    'question_text' => $mq->question_text,
                    'question_image' => $mq->question_image,
                    'options' => $mq->options,
                    'correct_answer' => $mq->correct_answer,
                    'score_weight' => $mq->score_weight,
                    'material' => $mq->material,
                    'basic_competency' => $mq->basic_competency,
                    'indicator' => $mq->indicator,
                    'cognitive_level' => $mq->cognitive_level,
                    'difficulty_level' => $mq->difficulty_level,
                    'rubric' => $mq->rubric,
                    'telaah_materi' => $mq->telaah_materi,
                    'telaah_konstruksi' => $mq->telaah_konstruksi,
                    'telaah_bahasa' => $mq->telaah_bahasa,
                    'telaah_status' => $mq->telaah_status,
                    'telaah_notes' => $mq->telaah_notes,
                ]
            );
        }

        if (!empty($numbers)) {
            ExamQuestion::where('exam_package_id', $target->id)
                ->whereNotIn('question_number', $numbers)
                ->delete();
        }
    }

    /**
     * Sync metadata & questions from a Master Bank Soal package to all linked correction_only children.
     */
    public function syncMasterToChildren(ExamPackage $master): int
    {
        if (!ExamPackage::hasSplitColumns()) {
            return 0;
        }

        $children = ExamPackage::where('source_package_id', $master->id)->get();
        if ($children->isEmpty()) {
            return 0;
        }

        $count = 0;
        foreach ($children as $child) {
            $child->update([
                'subject_id' => $master->subject_id,
                'academic_year_id' => $master->academic_year_id,
                'title' => $master->title,
                'exam_type' => $master->exam_type,
                'semester' => $master->semester,
                'total_questions' => $master->total_questions,
                'kkm' => $master->kkm,
                'pg_weight' => $master->pg_weight,
                'essay_weight' => $master->essay_weight,
                'academic_phase' => $master->academic_phase,
                'curriculum_type' => $master->curriculum_type,
                'description' => $master->description,
                'execution_date' => $master->execution_date,
            ]);
            $this->copyQuestionsToTarget($master, $child);
            $count++;
        }

        return $count;
    }

    /**
     * Split a Global Master package into separate per-class correction packages (e.g. 9A and 9B).
     *
     * @param array<int|string, int> $teacherMap Optional map [class_id => teacher_id]
     */
    public function splitMasterToClasses(
        ExamPackage $master,
        array $classIds,
        array $teacherMap = [],
        bool $migrateSubmissions = true,
        bool $hideMasterFromCorrection = true
    ): Collection {
        if (!ExamPackage::hasSplitColumns()) {
            throw new \RuntimeException('Kolom pemisahan paket belum tersedia. Harap jalankan php artisan migrate terlebih dahulu.');
        }

        $targetClasses = ClassRoom::whereIn('id', array_map('intval', $classIds))
            ->orderBy('name')
            ->get();

        if ($targetClasses->isEmpty()) {
            return collect();
        }

        return DB::transaction(function () use ($master, $targetClasses, $teacherMap, $migrateSubmissions, $hideMasterFromCorrection) {
            $createdOrUpdated = collect();

            if ($hideMasterFromCorrection && $master->package_scope !== 'bank_only') {
                $master->update(['package_scope' => 'bank_only']);
            }

            foreach ($targetClasses as $cls) {
                $assignedTeacherId = isset($teacherMap[$cls->id]) && $teacherMap[$cls->id]
                    ? (int) $teacherMap[$cls->id]
                    : $this->resolveTeacherForClassAndSubject((int) $cls->id, (int) $master->subject_id, (int) $master->teacher_id);

                $child = ExamPackage::where('source_package_id', $master->id)
                    ->where('class_room_id', $cls->id)
                    ->first();

                $attrs = [
                    'source_package_id' => $master->id,
                    'teacher_id' => $assignedTeacherId,
                    'class_room_id' => $cls->id,
                    'target_grade' => null,
                    'subject_id' => $master->subject_id,
                    'academic_year_id' => $master->academic_year_id,
                    'title' => $master->title,
                    'exam_type' => $master->exam_type,
                    'semester' => $master->semester,
                    'total_questions' => $master->total_questions,
                    'kkm' => $master->kkm,
                    'pg_weight' => $master->pg_weight,
                    'essay_weight' => $master->essay_weight,
                    'status' => $master->status === 'completed' ? 'completed' : 'draft',
                    'package_scope' => 'correction_only',
                    'status_kurikulum' => $master->status_kurikulum ?? 'draft',
                    'academic_phase' => $master->academic_phase ?? 'Fase D',
                    'curriculum_type' => $master->curriculum_type ?? 'merdeka',
                    'description' => $master->description,
                    'execution_date' => $master->execution_date,
                ];

                if ($child) {
                    $child->update($attrs);
                } else {
                    $child = ExamPackage::create($attrs);
                }

                $this->copyQuestionsToTarget($master, $child);

                if ($migrateSubmissions) {
                    $studentIds = Student::where('class_id', $cls->id)
                        ->orWhere('lokal_class_id', $cls->id)
                        ->pluck('id');

                    if ($studentIds->isNotEmpty()) {
                        ExamSubmission::where('exam_package_id', $master->id)
                            ->whereIn('student_id', $studentIds)
                            ->update(['exam_package_id' => $child->id]);
                    }
                }

                $createdOrUpdated->push($child->load(['classRoom', 'subject', 'teacher']));
            }

            return $createdOrUpdated;
        });
    }

    /**
     * Clone a Global Bank Soal package into a single-class correction package for a specific teacher.
     */
    public function cloneFromGlobalBankForTeacher(ExamPackage $master, int $classId, int $teacherId): ExamPackage
    {
        if (!ExamPackage::hasSplitColumns()) {
            throw new \RuntimeException('Kolom pemisahan paket belum tersedia. Harap jalankan php artisan migrate terlebih dahulu.');
        }

        return DB::transaction(function () use ($master, $classId, $teacherId) {
            $existing = ExamPackage::where('source_package_id', $master->id)
                ->where('class_room_id', $classId)
                ->where('teacher_id', $teacherId)
                ->first();

            if ($existing) {
                $this->copyQuestionsToTarget($master, $existing);
                return $existing->load(['classRoom', 'subject', 'teacher']);
            }

            $child = ExamPackage::create([
                'source_package_id' => $master->id,
                'teacher_id' => $teacherId,
                'class_room_id' => $classId,
                'target_grade' => null,
                'subject_id' => $master->subject_id,
                'academic_year_id' => $master->academic_year_id,
                'title' => $master->title,
                'exam_type' => $master->exam_type,
                'semester' => $master->semester,
                'total_questions' => $master->total_questions,
                'kkm' => $master->kkm,
                'pg_weight' => $master->pg_weight,
                'essay_weight' => $master->essay_weight,
                'status' => 'draft',
                'package_scope' => 'correction_only',
                'status_kurikulum' => $master->status_kurikulum ?? 'draft',
                'academic_phase' => $master->academic_phase ?? 'Fase D',
                'curriculum_type' => $master->curriculum_type ?? 'merdeka',
                'description' => $master->description,
                'execution_date' => $master->execution_date,
            ]);

            $this->copyQuestionsToTarget($master, $child);

            return $child->load(['classRoom', 'subject', 'teacher']);
        });
    }
}
