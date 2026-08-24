<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    protected $fillable = [
        'teacher_id',
        'date',
        'check_in_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_distance_meters',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_distance_meters',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_in_distance_meters' => 'integer',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
        'check_out_distance_meters' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
