<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstsSubjectScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'academic_year_id',
        'semester',
        'score',
        'kkm',
        'predicate',
        'description',
        'exam_package_id',
        'synced_at',
    ];

    protected $casts = [
        'score' => 'float',
        'kkm' => 'float',
        'synced_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function examPackage()
    {
        return $this->belongsTo(ExamPackage::class);
    }
}
