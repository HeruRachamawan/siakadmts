<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id', 'class_id', 'lokal_class_id', 'nisn', 'nis', 'nik', 'no_kk', 'full_name', 'gender',
        'birth_place', 'birth_date', 'religion', 'child_number', 'siblings_count',
        'hobby', 'aspiration', 'address', 'parent_phone',
        'mother_name', 'mother_status', 'mother_nik', 'mother_job', 'mother_income',
        'father_name', 'father_status', 'father_nik', 'father_job', 'father_income',
        'guardian_name', 'guardian_relation', 'guardian_nik', 'guardian_job', 'guardian_phone', 'guardian_income',
        'previous_school', 'photo',
    ];

    protected $appends = ['photo_url', 'class_name'];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
        'child_number' => 'integer',
        'siblings_count' => 'integer',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if (empty($this->photo) || !is_string($this->photo) || trim($this->photo) === '') {
            return null;
        }
        $p = trim($this->photo);
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://') || str_starts_with($p, 'data:')) {
            return $p;
        }
        return asset('storage/' . ltrim(preg_replace('/^(\/?storage\/)+/', '', $p), '/'));
    }

    public function getClassNameAttribute(): ?string
    {
        return $this->classRoom?->name ?? '-';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function lokalClassRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'lokal_class_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
