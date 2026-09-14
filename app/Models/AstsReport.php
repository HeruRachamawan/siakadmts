<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstsReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester',
        'homeroom_notes',
        'sick_count',
        'permission_count',
        'unexcused_count',
        'manual_rank',
        'calculated_rank',
    ];

    protected $casts = [
        'sick_count' => 'integer',
        'permission_count' => 'integer',
        'unexcused_count' => 'integer',
        'manual_rank' => 'integer',
        'calculated_rank' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
