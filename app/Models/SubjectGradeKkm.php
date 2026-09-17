<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectGradeKkm extends Model
{
    protected $table = 'subject_grade_kkms';

    protected $fillable = [
        'subject_id',
        'grade_level',
        'kkm',
        'academic_year_id',
    ];

    protected $casts = [
        'kkm' => 'float',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Resolve the effective KKTP / KKM for a subject in a specific grade level.
     * Priority order:
     * 1. Specific subject + grade_level + academic_year_id (if year provided)
     * 2. Specific subject + grade_level (without year restriction / year null)
     * 3. Fallback to Subject's default passing_grade
     * 4. Global default 75.00
     */
    public static function getEffectiveKkm(int $subjectId, ?string $gradeLevel = null, ?int $academicYearId = null): float
    {
        // Normalize grade level: strip non-numeric or extract standard '7', '8', '9'
        $normalizedGrade = null;
        if ($gradeLevel !== null) {
            $cleaned = trim((string) $gradeLevel);
            if (preg_match('/(7|8|9|VII|VIII|IX)/i', $cleaned, $m)) {
                $upper = strtoupper($m[1]);
                if ($upper === 'VII' || $upper === '7') $normalizedGrade = '7';
                elseif ($upper === 'VIII' || $upper === '8') $normalizedGrade = '8';
                elseif ($upper === 'IX' || $upper === '9') $normalizedGrade = '9';
                else $normalizedGrade = $m[1];
            } else {
                $normalizedGrade = $cleaned;
            }
        }

        if ($normalizedGrade !== null) {
            // 1. Try with academic_year_id if given
            if ($academicYearId) {
                $val = self::where('subject_id', $subjectId)
                    ->where('grade_level', $normalizedGrade)
                    ->where('academic_year_id', $academicYearId)
                    ->value('kkm');
                if ($val !== null) {
                    return floatval($val);
                }
            }

            // 2. Try with null academic_year_id or any match
            $val = self::where('subject_id', $subjectId)
                ->where('grade_level', $normalizedGrade)
                ->orderBy('academic_year_id', 'desc')
                ->value('kkm');
            if ($val !== null) {
                return floatval($val);
            }
        }

        // 3. Fallback to settings per grade level (default_kkm_7, default_kkm_8, default_kkm_9)
        if ($normalizedGrade !== null) {
            $rawSettings = \App\Models\Setting::where('key', 'exam_correction_settings')->value('value');
            if ($rawSettings) {
                $decoded = json_decode($rawSettings, true);
                $key = 'default_kkm_' . $normalizedGrade;
                if (!empty($decoded[$key])) {
                    return floatval($decoded[$key]);
                }
            }
        }

        // 4. Fallback to subject's general passing_grade
        $subject = Subject::find($subjectId);
        if ($subject && !empty($subject->passing_grade)) {
            return floatval($subject->passing_grade);
        }

        return 75.00;
    }
}
