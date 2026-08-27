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
        'weekly_holidays',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'max_radius_meters' => 'integer',
        'weekly_holidays' => 'array',
    ];

    public static function getSetting()
    {
        $setting = static::firstOrCreate([], [
            'school_name' => 'MTs AL - HASANAH',
            'latitude' => -6.20880000,
            'longitude' => 106.84560000,
            'max_radius_meters' => 100,
            'work_start_time' => '07:00:00',
            'work_late_time' => '07:15:00',
            'work_end_time' => '15:00:00',
            'weekly_holidays' => ['sunday'],
        ]);

        if (empty($setting->weekly_holidays)) {
            $setting->weekly_holidays = ['sunday'];
        }

        return $setting;
    }
}
