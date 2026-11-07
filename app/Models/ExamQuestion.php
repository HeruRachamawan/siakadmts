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
        'question_text',
        'question_image',
        'options',
        'correct_answer',
        'score_weight',
        'material',
        'basic_competency',
        'indicator',
        'cognitive_level',
        'difficulty_level',
        'rubric',
        'telaah_materi',
        'telaah_konstruksi',
        'telaah_bahasa',
        'telaah_status',
        'telaah_notes',
    ];

    protected $casts = [
        'question_number' => 'integer',
        'score_weight' => 'float',
        'options' => 'array',
        'telaah_materi' => 'array',
        'telaah_konstruksi' => 'array',
        'telaah_bahasa' => 'array',
    ];

    public function examPackage()
    {
        return $this->belongsTo(ExamPackage::class);
    }
}
