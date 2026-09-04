<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_room_id',
        'subject_id',
        'academic_year_id',
        'title',
        'exam_type',
        'semester',
        'total_questions',
        'kkm',
        'pg_weight',
        'essay_weight',
        'status',
        'description',
    ];

    protected $casts = [
        'total_questions' => 'integer',
        'kkm' => 'float',
        'pg_weight' => 'float',
        'essay_weight' => 'float',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('question_number');
    }

    public function submissions()
    {
        return $this->hasMany(ExamSubmission::class);
    }
}
