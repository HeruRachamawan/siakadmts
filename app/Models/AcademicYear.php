<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = ['year', 'semester', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['name'];

    public function getNameAttribute(): string
    {
        return $this->year ?? '';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'academic_year_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'academic_year_id');
    }

    /**
     * Resolve the requested academic year from header (X-Academic-Year-Id) or request parameters,
     * falling back to the active year, or latest record.
     */
    public static function getSelectedOrActive(?\Illuminate\Http\Request $request = null): ?self
    {
        $req = $request ?? request();
        $requestedId = null;

        if ($req) {
            $requestedId = $req->header('X-Academic-Year-Id')
                ?? $req->input('academic_year_id')
                ?? $req->input('selected_academic_year_id');
        }

        if ($requestedId && is_numeric($requestedId)) {
            $year = self::find((int)$requestedId);
            if ($year) {
                return $year;
            }
        }

        return self::where('is_active', true)->first() ?? self::orderBy('id', 'desc')->first();
    }

    /**
     * Resolve the ID of the selected or active academic year.
     */
    public static function getSelectedOrActiveId(?\Illuminate\Http\Request $request = null): ?int
    {
        return self::getSelectedOrActive($request)?->id;
    }
}
