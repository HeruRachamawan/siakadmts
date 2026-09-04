<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_package_id',
        'question_number',
        'question_type',
        'correct_answer',
        'score_weight',
    ];

    protected $casts = [
        'question_number' => 'integer',
        'score_weight' => 'float',
    ];

    public function examPackage()
    {
        return $this->belongsTo(ExamPackage::class);
    }
}
