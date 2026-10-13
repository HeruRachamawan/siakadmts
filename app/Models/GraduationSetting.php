<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraduationSetting extends Model
{
    use HasFactory;

    protected $table = 'graduation_settings';

    protected $fillable = [
        'report_weight',
        'exam_weight',
        'theory_weight_default',
        'practice_weight_default',
        'active_subject_ids',
        'practical_subject_ids',
        'custom_subject_weights',
        'date_place',
        'graduation_date',
        'certificate_number_prefix',
        'principal_name',
        'principal_nip',
        'nsm',
        'npsn',
        'show_student_photo',
        'academic_year',
    ];

    protected $casts = [
        'report_weight' => 'float',
        'exam_weight' => 'float',
        'theory_weight_default' => 'float',
        'practice_weight_default' => 'float',
        'active_subject_ids' => 'array',
        'practical_subject_ids' => 'array',
        'custom_subject_weights' => 'array',
        'graduation_date' => 'date:Y-m-d',
        'show_student_photo' => 'boolean',
    ];

    /**
     * Get or create the singleton graduation settings.
     */
    public static function getActiveSettings(): self
    {
        $setting = static::first();
        if (!$setting) {
            $setting = static::create([
                'report_weight' => 60.00,
                'exam_weight' => 40.00,
                'theory_weight_default' => 50.00,
                'practice_weight_default' => 50.00,
                'practical_subject_ids' => [],
                'custom_subject_weights' => [],
                'date_place' => 'Bogor',
                'graduation_date' => now()->toDateString(),
            ]);
        }
        return $setting;
    }
}
