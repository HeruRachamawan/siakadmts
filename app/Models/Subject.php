<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['code', 'name', 'description', 'passing_grade'];

    protected $casts = [
        'passing_grade' => 'integer',
    ];

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function teachers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Teacher::class);
    }
}
