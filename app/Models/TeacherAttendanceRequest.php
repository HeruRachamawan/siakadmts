<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'date',
        'target_status',
        'requested_check_in_time',
        'requested_check_out_time',
        'reason',
        'approval_status',
        'admin_notes',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
