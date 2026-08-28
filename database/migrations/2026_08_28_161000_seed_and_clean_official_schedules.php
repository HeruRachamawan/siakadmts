<?php

use App\Models\ClassRoom;
use App\Models\Schedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delete corrupt entries like 22:31 or duplicate break/prayer activities
        Schedule::where('is_activity', true)
            ->where(function($q) {
                $q->where('start_time', '>', '18:00')
                  ->orWhere('activity_name', 'like', '%ISTIRAHAT%')
                  ->orWhere('activity_name', 'like', '%istirahat%')
                  ->orWhere('activity_name', 'like', '%UPACARA%')
                  ->orWhere('activity_name', 'like', '%upacara%')
                  ->orWhere('activity_name', 'like', '%SHALAT%')
                  ->orWhere('activity_name', 'like', '%sholat%')
                  ->orWhere('activity_name', 'like', '%TADARUS%');
            })
            ->delete();

        // 2. Official standard schedules per day
        $activities = [
            // SENIN
            ['day' => 'senin', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => 'UPACARA BENDERA', 'activity_type' => 'upacara'],
            ['day' => 'senin', 'start_time' => '07:30', 'end_time' => '07:50', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'senin', 'start_time' => '10:30', 'end_time' => '11:00', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'senin', 'start_time' => '12:20', 'end_time' => '12:40', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],

            // SELASA
            ['day' => 'selasa', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'selasa', 'start_time' => '10:10', 'end_time' => '10:40', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'selasa', 'start_time' => '12:00', 'end_time' => '12:20', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],

            // RABU
            ['day' => 'rabu', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'rabu', 'start_time' => '10:10', 'end_time' => '10:40', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'rabu', 'start_time' => '12:00', 'end_time' => '12:20', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],

            // KAMIS
            ['day' => 'kamis', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'kamis', 'start_time' => '10:10', 'end_time' => '10:40', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'kamis', 'start_time' => '12:00', 'end_time' => '12:20', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],

            // JUMAT
            ['day' => 'jumat', 'start_time' => '07:00', 'end_time' => '07:45', 'activity_name' => "SHOLAT DHUHA & YASINAN", 'activity_type' => 'religi'],
            ['day' => 'jumat', 'start_time' => '09:45', 'end_time' => '10:15', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'jumat', 'start_time' => '11:00', 'end_time' => '12:30', 'activity_name' => "SHALAT JUM'AT BERJAMA'AH", 'activity_type' => 'religi'],

            // SABTU
            ['day' => 'sabtu', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'sabtu', 'start_time' => '10:10', 'end_time' => '10:40', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'sabtu', 'start_time' => '12:00', 'end_time' => '12:20', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],
        ];

        $classes = ClassRoom::all();

        foreach ($activities as $act) {
            if ($classes->count() > 0) {
                foreach ($classes as $cls) {
                    Schedule::create([
                        'day' => $act['day'],
                        'start_time' => $act['start_time'],
                        'end_time' => $act['end_time'],
                        'activity_name' => $act['activity_name'],
                        'activity_type' => $act['activity_type'],
                        'is_activity' => true,
                        'class_id' => $cls->id,
                    ]);
                }
            } else {
                Schedule::create([
                    'day' => $act['day'],
                    'start_time' => $act['start_time'],
                    'end_time' => $act['end_time'],
                    'activity_name' => $act['activity_name'],
                    'activity_type' => $act['activity_type'],
                    'is_activity' => true,
                    'class_id' => null,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schedule::where('is_activity', true)->delete();
    }
};
