<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraduationGrade extends Model
{
    use HasFactory;

    protected $table = 'graduation_grades';

    protected $fillable = [
        'student_id',
        'subject_id',
        'sem_1',
        'sem_2',
        'sem_3',
        'sem_4',
        'sem_5',
        'sem_6',
        'avg_semester',
        'exam_theory',
        'exam_practice',
        'final_exam_score',
        'final_grade',
    ];

    protected $casts = [
        'sem_1' => 'float',
        'sem_2' => 'float',
        'sem_3' => 'float',
        'sem_4' => 'float',
        'sem_5' => 'float',
        'sem_6' => 'float',
        'avg_semester' => 'float',
        'exam_theory' => 'float',
        'exam_practice' => 'float',
        'final_exam_score' => 'float',
        'final_grade' => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Compute average semester, exam score, and final graduation score based on graduation settings.
     */
    public function recalculateScores(?GraduationSetting $setting = null): void
    {
        if (!$setting) {
            $setting = GraduationSetting::getActiveSettings();
        }

        // 1. Calculate Average Semester (Sem 1 to 6)
        $semScores = [];
        foreach (['sem_1', 'sem_2', 'sem_3', 'sem_4', 'sem_5', 'sem_6'] as $field) {
            if ($this->{$field} !== null && is_numeric($this->{$field})) {
                $semScores[] = floatval($this->{$field});
            }
        }

        if (count($semScores) > 0) {
            // Standard PDUM typically divides by total filled or by 6
            $this->avg_semester = round(array_sum($semScores) / count($semScores), 2);
        } else {
            $this->avg_semester = null;
        }

        // 2. Determine exam score (pertahankan jika diisi manual, atau hitung otomatis jika ada teori/praktik)
        $theory = $this->exam_theory !== null ? floatval($this->exam_theory) : null;
        $practice = $this->exam_practice !== null ? floatval($this->exam_practice) : null;

        if ($this->final_exam_score === null || $this->final_exam_score === '') {
            $practicalIds = is_array($setting->practical_subject_ids) ? $setting->practical_subject_ids : [];
            $isPractical = in_array((int)$this->subject_id, array_map('intval', $practicalIds));

            if ($isPractical) {
                $theoryWeight = floatval($setting->theory_weight_default ?? 50);
                $practiceWeight = floatval($setting->practice_weight_default ?? 50);

                // Check custom weights for this subject if configured
                if (is_array($setting->custom_subject_weights) && isset($setting->custom_subject_weights[$this->subject_id])) {
                    $c = $setting->custom_subject_weights[$this->subject_id];
                    $theoryWeight = floatval($c['theory_weight'] ?? $theoryWeight);
                    $practiceWeight = floatval($c['practice_weight'] ?? $practiceWeight);
                }

                $totalWeight = $theoryWeight + $practiceWeight;
                if ($totalWeight > 0 && ($theory !== null || $practice !== null)) {
                    $tVal = ($theory ?? 0) * ($theoryWeight / $totalWeight);
                    $pVal = ($practice ?? 0) * ($practiceWeight / $totalWeight);
                    $this->final_exam_score = round($tVal + $pVal, 2);
                } else {
                    $this->final_exam_score = null;
                }
            } else {
                $this->final_exam_score = $theory !== null ? round($theory, 2) : null;
            }
        } else {
            $this->final_exam_score = round(floatval($this->final_exam_score), 2);
        }

        // 3. Final Graduation / Ijazah Score
        // Final Grade = (Report Weight * avg_semester) + (Exam Weight * final_exam_score)
        $repWeight = floatval($setting->report_weight ?? 60);
        $exWeight = floatval($setting->exam_weight ?? 40);
        $totalFinalWeight = $repWeight + $exWeight;

        if ($totalFinalWeight > 0 && ($this->avg_semester !== null || $this->final_exam_score !== null)) {
            $avgVal = ($this->avg_semester ?? 0) * ($repWeight / $totalFinalWeight);
            $examVal = ($this->final_exam_score ?? 0) * ($exWeight / $totalFinalWeight);
            $this->final_grade = round($avgVal + $examVal, 2);
        } else {
            $this->final_grade = null;
        }
    }
}
