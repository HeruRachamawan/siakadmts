<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        if (!$activeYear) {
            $activeYear = AcademicYear::orderBy('id', 'desc')->first();
        }

        $principal = null;
        if (!empty($settings['principal_teacher_id'])) {
            $principal = \App\Models\Teacher::find($settings['principal_teacher_id']);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'app_name' => $settings['app_name'] ?? 'SMK NEGERI 1 CONTOH',
                'app_tagline' => $settings['app_tagline'] ?? 'Sistem Informasi Manajemen Siswa',
                'school_address' => $settings['school_address'] ?? 'Jl. Pendidikan No. 123, Kota Belajar',
                'app_logo' => (!empty($settings['app_logo'])) ? asset('storage/' . $settings['app_logo']) : null,
                'academic_year_id' => $settings['academic_year_id'] ?? ($activeYear?->id ?? null),
                'active_academic_year' => $activeYear,
                'principal_teacher_id' => $settings['principal_teacher_id'] ?? null,
                'principal_name' => $principal?->full_name ?? ($settings['principal_name'] ?? null),
                'principal_nip' => $principal?->nip ?? ($settings['principal_nip'] ?? null),
            ]
        ]);
    }
}
