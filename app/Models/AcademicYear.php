<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = ['year', 'semester', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'academic_year_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'academic_year_id');
    }
}
