<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_package_id',
        'student_id',
        'student_answers',
        'essay_scores',
        'correct_pg_count',
        'wrong_pg_count',
        'pg_score',
        'essay_score',
        'total_score',
        'is_passed',
    ];

    protected $casts = [
        'student_answers' => 'array',
        'essay_scores' => 'array',
        'correct_pg_count' => 'integer',
        'wrong_pg_count' => 'integer',
        'pg_score' => 'float',
        'essay_score' => 'float',
        'total_score' => 'float',
        'is_passed' => 'boolean',
    ];

    public function examPackage()
    {
        return $this->belongsTo(ExamPackage::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
