<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'agenda_number',
        'reference_number',
        'sender',
        'recipient',
        'subject',
        'letter_date',
        'received_date',
        'category',
        'file_path',
        'disposition_to',
        'disposition_notes',
        'disposition_date',
        'status',
        'created_by',
        'student_id',
    ];

    protected $casts = [
        'letter_date' => 'date:Y-m-d',
        'received_date' => 'date:Y-m-d',
        'disposition_date' => 'date:Y-m-d',
    ];

    protected $appends = [
        'file_url',
        'status_badge_class',
        'status_label',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        return Storage::url($this->file_path);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Belum Disposisi',
            'dispositioned' => 'Telah Disposisi',
            'processed' => 'Selesai Diproses',
            'archived' => 'Diarsipkan',
            default => 'Pending',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
            'dispositioned' => 'bg-sky-100 text-sky-800 border-sky-300',
            'processed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'archived' => 'bg-slate-100 text-slate-700 border-slate-300',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }
}
