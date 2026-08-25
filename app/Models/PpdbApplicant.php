<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpdbApplicant extends Model
{
    protected $fillable = [
        'registration_number',
        'academic_year_id',
        'full_name',
        'nisn',
        'nik',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'phone',
        'previous_school',
        'father_name',
        'father_job',
        'father_phone',
        'mother_name',
        'mother_job',
        'mother_phone',
        'guardian_name',
        'guardian_phone',
        'photo',
        'family_card_file',
        'certificate_file',
        'status',
        'test_score',
        'notes',
        'verified_by',
        'enrolled_student_id',
        'enrolled_class_id',
    ];

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
        'test_score' => 'decimal:2',
    ];

    protected $appends = [
        'photo_url',
        'family_card_url',
        'certificate_url',
        'status_label',
        'status_badge_class',
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

    public function getFamilyCardUrlAttribute(): ?string
    {
        if (empty($this->family_card_file) || !is_string($this->family_card_file) || trim($this->family_card_file) === '') {
            return null;
        }
        $p = trim($this->family_card_file);
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
            return $p;
        }
        return asset('storage/' . ltrim(preg_replace('/^(\/?storage\/)+/', '', $p), '/'));
    }

    public function getCertificateUrlAttribute(): ?string
    {
        if (empty($this->certificate_file) || !is_string($this->certificate_file) || trim($this->certificate_file) === '') {
            return null;
        }
        $p = trim($this->certificate_file);
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
            return $p;
        }
        return asset('storage/' . ltrim(preg_replace('/^(\/?storage\/)+/', '', $p), '/'));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Berkas Terverifikasi',
            'accepted' => 'Diterima / Lulus Seleksi',
            'rejected' => 'Tidak Lulus',
            'enrolled' => 'Siswa Aktif Terdaftar',
            default => 'Menunggu',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'verified' => 'bg-sky-50 text-sky-700 border-sky-200',
            'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'enrolled' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function enrolledStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'enrolled_student_id');
    }

    public function enrolledClass(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'enrolled_class_id');
    }
}
