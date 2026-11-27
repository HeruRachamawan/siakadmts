<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRoom extends Model
{
    protected $table = 'classes';

    protected $fillable = ['homeroom_teacher_id', 'academic_year_id', 'name', 'grade_level'];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function lokalStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'lokal_class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function teachers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_teacher', 'class_id', 'teacher_id');
    }

    /**
     * Check whether this class represents a grade-level Jadwal Lokal bucket (e.g. "7", "8", "Kelas 7", "Kelas 8").
     */
    public function isLokalTingkatBucket(): bool
    {
        $raw = trim((string) $this->name);
        $clean = strtolower(preg_replace('/^(kelas|kls)\s*/i', '', $raw));
        return in_array($clean, ['7', '8'], true);
    }

    /**
     * Resolve all class IDs whose students belong to this class.
     * For grade-level Lokal buckets (Kelas 7 / Kelas 8), combines all rombels in that grade_level (e.g. 8A + 8B).
     */
    public function resolveStudentScopeClassIds(): array
    {
        if (!$this->isLokalTingkatBucket() || empty($this->grade_level)) {
            return [$this->id];
        }

        $query = static::where('grade_level', $this->grade_level);
        if (!empty($this->academic_year_id)) {
            $sameYearIds = (clone $query)
                ->where('academic_year_id', $this->academic_year_id)
                ->pluck('id')
                ->all();
            if (count($sameYearIds) > 1) {
                $ids = $sameYearIds;
            } else {
                $ids = $query->pluck('id')->all();
            }
        } else {
            $ids = $query->pluck('id')->all();
        }

        if (!in_array($this->id, $ids, true)) {
            $ids[] = $this->id;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * Query active students belonging to this class (or combined grade rombels for Lokal Tingkat 7/8).
     */
    public function resolvedStudentsQuery(bool $onlyActive = true)
    {
        $scopeIds = $this->resolveStudentScopeClassIds();
        $query = Student::query();

        if ($onlyActive) {
            $query->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['lulus', 'mutasi', 'keluar', 'alumni', 'nonaktif', 'inactive']);
            });
        }

        return $query->where(function ($q) use ($scopeIds) {
            $q->whereIn('class_id', $scopeIds)
              ->orWhereIn('lokal_class_id', $scopeIds);
        });
    }

    /**
     * Count active resolved students for this class.
     */
    public function resolvedStudentsCount(bool $onlyActive = true): int
    {
        return $this->resolvedStudentsQuery($onlyActive)->count();
    }
}
