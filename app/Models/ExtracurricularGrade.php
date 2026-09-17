<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtracurricularGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'student_id',
        'academic_year_id',
        'semester',
        'grade',
        'description',
        'graded_by_teacher_id',
    ];

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function gradedByTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'graded_by_teacher_id');
    }

    /**
     * Helper get default predicate label
     */
    public function getGradeLabelAttribute(): string
    {
        return match($this->grade) {
            'A' => 'Sangat Baik',
            'B' => 'Baik',
            'C' => 'Cukup',
            default => '-'
        };
    }
}
