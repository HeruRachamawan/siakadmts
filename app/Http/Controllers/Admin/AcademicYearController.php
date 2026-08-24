<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends BaseController
{
    public function index(Request $request)
    {
        $years = AcademicYear::orderBy('year', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->success($this->paginate($years));
    }

    public function store(Request $request)
    {
        $request->validate([
            'year' => ['required', 'string', 'max:255', 'unique:academic_years,year'],
            'semester' => ['required', 'in:odd,even'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data = $request->only(['year', 'semester', 'is_active']);
        $data['is_active'] = $data['is_active'] ?? false;

        if ($data['is_active']) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        $year = AcademicYear::create($data);

        return $this->success($year, 'Tahun ajaran dibuat', 201);
    }

    public function show(AcademicYear $academicYear)
    {
        return $this->success($academicYear);
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'year' => ['sometimes', 'required', 'string', 'max:255', "unique:academic_years,year,{$academicYear->id}"],
            'semester' => ['sometimes', 'required', 'in:odd,even'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($request->boolean('is_active', false) && ! $academicYear->is_active) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        $academicYear->update($request->only(['year', 'semester', 'is_active']));

        return $this->success($academicYear);
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return $this->success(null, 'Tahun ajaran dihapus');
    }

    public function setActive(Request $request, AcademicYear $academicYear)
    {
        AcademicYear::where('is_active', true)->update(['is_active' => false]);
        $academicYear->update(['is_active' => true]);

        return $this->success($academicYear, 'Tahun ajaran diaktifkan');
    }
}
