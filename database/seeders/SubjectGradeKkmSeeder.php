<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\SubjectGradeKkm;
use App\Models\AcademicYear;

class SubjectGradeKkmSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = Subject::all();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $activeYear?->id;

        foreach ($subjects as $subject) {
            $lower = strtolower($subject->name);
            $isArab = str_contains($lower, 'bahasa arab') || str_contains($lower, 'arab') || strtolower($subject->code) === 'ba';
            $baseKkm = floatval($subject->passing_grade ?: 75);

            foreach (['7', '8', '9'] as $grade) {
                $targetKkm = $baseKkm;
                if ($isArab) {
                    $targetKkm = ($grade === '7') ? 70.00 : 75.00;
                }

                // Create or update default with null academic_year_id
                SubjectGradeKkm::updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'grade_level' => $grade,
                        'academic_year_id' => null,
                    ],
                    [
                        'kkm' => $targetKkm,
                    ]
                );

                // If active year exists, also record for this active year
                if ($yearId) {
                    SubjectGradeKkm::updateOrCreate(
                        [
                            'subject_id' => $subject->id,
                            'grade_level' => $grade,
                            'academic_year_id' => $yearId,
                        ],
                        [
                            'kkm' => $targetKkm,
                        ]
                    );
                }
            }
        }
    }
}
