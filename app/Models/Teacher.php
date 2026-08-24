<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = ['user_id', 'nip', 'full_name', 'gender', 'phone', 'position', 'photo'];

    protected $appends = ['photo_url', 'qr_card_payload'];

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

    public function getQrCardPayloadAttribute(): string
    {
        $sig = hash_hmac('sha256', "TEACHER-CARD-{$this->id}-" . ($this->nip ?: 'NONIP'), config('app.key'));
        return "TEACHER-ID|{$this->id}|" . ($this->nip ?: 'NONIP') . "|{$sig}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'homeroom_teacher_id');
    }

    public function subjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    public function teachingClasses(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_teacher', 'teacher_id', 'class_id');
    }

    public function subjectClasses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherSubjectClass::class, 'teacher_id');
    }

    public function attendanceRequests(): HasMany
    {
        return $this->hasMany(TeacherAttendanceRequest::class, 'teacher_id');
    }
}
