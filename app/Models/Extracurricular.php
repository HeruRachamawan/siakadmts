<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Extracurricular extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'teacher_id',
        'is_mandatory',
        'schedule_day',
        'schedule_time',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ExtracurricularMember::class, 'extracurricular_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'extracurricular_members', 'extracurricular_id', 'student_id')
            ->withPivot(['academic_year_id', 'joined_date', 'notes'])
            ->withTimestamps();
    }

    public function grades(): HasMany
    {
        return $this->hasMany(ExtracurricularGrade::class, 'extracurricular_id');
    }
}
