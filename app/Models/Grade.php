<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    public const WEIGHT_ASSIGNMENT = 0.30;
    public const WEIGHT_UTS = 0.35;
    public const WEIGHT_UAS = 0.35;

    protected $fillable = [
        'student_id', 'subject_id', 'academic_year_id',
        'score_assignment', 'score_uts', 'score_uas', 'custom_scores', 'final_score',
    ];

    protected $casts = [
        'score_assignment' => 'decimal:2',
        'score_uts' => 'decimal:2',
        'score_uas' => 'decimal:2',
        'final_score' => 'decimal:2',
        'custom_scores' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $grade) {
            $grade->final_score = $grade->calculateFinalScore();
        });
    }

    public function calculateFinalScore(): float
    {
        if (is_array($this->custom_scores) && !empty($this->custom_scores)) {
            $total = 0;
            $totalWeight = 0;
            foreach ($this->custom_scores as $comp) {
                $score = floatval($comp['score'] ?? 0);
                $weight = floatval($comp['weight'] ?? 0);
                $total += ($score * $weight) / 100;
                $totalWeight += $weight;
            }
            if ($totalWeight > 0) {
                return round($total, 2);
            }
        }

        return round(
            $this->score_assignment * self::WEIGHT_ASSIGNMENT
            + $this->score_uts * self::WEIGHT_UTS
            + $this->score_uas * self::WEIGHT_UAS,
            2
        );
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
