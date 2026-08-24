<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['title', 'student_name', 'level', 'year', 'description', 'image', 'status'];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        return (!empty($this->image) && is_string($this->image) && trim($this->image) !== '') ? url('storage/' . $this->image) : null;
    }
}
