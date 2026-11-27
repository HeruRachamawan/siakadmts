<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ExamPackage extends Model
{
    use HasFactory;

    protected static ?bool $splitColumnsExist = null;

    protected $fillable = [
        'source_package_id',
        'teacher_id',
        'class_room_id',
        'target_grade',
        'subject_id',
        'academic_year_id',
        'title',
        'exam_type',
        'semester',
        'total_questions',
        'kkm',
        'pg_weight',
        'essay_weight',
        'status',
        'package_scope',
        'status_kurikulum',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'academic_phase',
        'curriculum_type',
        'description',
        'execution_date',
    ];

    public static function hasSplitColumns(): bool
    {
        if (static::$splitColumnsExist === null) {
            static::$splitColumnsExist = Schema::hasColumn('exam_packages', 'package_scope')
                && Schema::hasColumn('exam_packages', 'source_package_id');
        }
        return static::$splitColumnsExist;
    }

    /**
     * Merge teacher-specified question type order with default fallback types.
     *
     * @param array<int, string>|null $requestedOrder
     * @param array<int, string> $defaultOrder
     * @return array<int, string>
     */
    public static function resolveOrderedTypes(?array $requestedOrder, array $defaultOrder): array
    {
        $validSet = array_flip($defaultOrder);
        $ordered = [];
        foreach (is_array($requestedOrder) ? $requestedOrder : [] as $type) {
            if (isset($validSet[$type]) && !in_array($type, $ordered, true)) {
                $ordered[] = $type;
            }
        }
        foreach ($defaultOrder as $type) {
            if (!in_array($type, $ordered, true)) {
                $ordered[] = $type;
            }
        }
        return $ordered;
    }

    public function sourcePackage()
    {
        return $this->belongsTo(ExamPackage::class, 'source_package_id');
    }

    public function correctionChildren()
    {
        return $this->hasMany(ExamPackage::class, 'source_package_id');
    }

    protected $casts = [
        'total_questions' => 'integer',
        'kkm' => 'float',
        'pg_weight' => 'float',
        'essay_weight' => 'float',
        'reviewed_at' => 'datetime',
    ];

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('question_number');
    }

    public function submissions()
    {
        return $this->hasMany(ExamSubmission::class);
    }
}
