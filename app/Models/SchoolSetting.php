<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_name',
        'latitude',
        'longitude',
        'max_radius_meters',
        'work_start_time',
        'work_late_time',
        'work_end_time',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'max_radius_meters' => 'integer',
    ];

    public static function getSetting()
    {
        return static::firstOrCreate([], [
            'school_name' => 'SMK YASPIN',
            'latitude' => -6.20880000,
            'longitude' => 106.84560000,
            'max_radius_meters' => 100,
            'work_start_time' => '07:00:00',
            'work_late_time' => '07:15:00',
            'work_end_time' => '15:00:00',
        ]);
    }
}
