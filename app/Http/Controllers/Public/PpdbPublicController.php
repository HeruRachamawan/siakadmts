<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Api\BaseController;
use App\Models\AcademicYear;
use App\Models\PpdbApplicant;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PpdbPublicController extends BaseController
{
    /**
     * Get active academic years for PPDB
     */
    public function getInfo()
    {
        $activeYear = AcademicYear::where('is_active', true)->first() ?: AcademicYear::latest('id')->first();
        $allYears = AcademicYear::orderByDesc('year')->get();
        $settings = Setting::pluck('value', 'key')->all();

        return $this->success([
            'active_academic_year' => $activeYear,
            'academic_years' => $allYears,
            'school_name' => $settings['app_name'] ?? 'MTs AL - HASANAH',
            'school_address' => $settings['school_address'] ?? '',
            'school_phone' => $settings['school_phone'] ?? '',
            'school_logo' => $settings['app_logo'] ?? '',
        ]);
    }

    /**
     * Submit public PPDB registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'phone' => ['required', 'string', 'max:30'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_job' => ['nullable', 'string', 'max:100'],
            'father_phone' => ['nullable', 'string', 'max:30'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_job' => ['nullable', 'string', 'max:100'],
            'mother_phone' => ['nullable', 'string', 'max:30'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'photo' => ['nullable', 'image', 'max:3072'],
            'family_card_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'certificate_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        // Determine academic year
        $yearId = $request->academic_year_id;
        if (! $yearId) {
            $activeYear = AcademicYear::where('is_active', true)->first() ?: AcademicYear::latest('id')->first();
            $yearId = $activeYear?->id;
        }

        // Generate unique registration number PPDB-YYYY-XXXX
        $currentYear = date('Y');
        $countThisYear = PpdbApplicant::whereYear('created_at', $currentYear)->count() + 1;
        $registrationNumber = sprintf('PPDB-%s-%04d', $currentYear, $countThisYear);

        // Ensure uniqueness
        while (PpdbApplicant::where('registration_number', $registrationNumber)->exists()) {
            $countThisYear++;
            $registrationNumber = sprintf('PPDB-%s-%04d', $currentYear, $countThisYear);
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('ppdb/photos', 'public');
        }

        $kkPath = null;
        if ($request->hasFile('family_card_file')) {
            $kkPath = $request->file('family_card_file')->store('ppdb/family_cards', 'public');
        }

        $certPath = null;
        if ($request->hasFile('certificate_file')) {
            $certPath = $request->file('certificate_file')->store('ppdb/certificates', 'public');
        }

        $applicant = PpdbApplicant::create([
            'registration_number' => $registrationNumber,
            'academic_year_id' => $yearId,
            'full_name' => $request->full_name,
            'nisn' => $request->nisn,
            'nik' => $request->nik,
            'gender' => $request->gender,
            'birth_place' => $request->birth_place,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
            'phone' => $request->phone,
            'previous_school' => $request->previous_school,
            'father_name' => $request->father_name,
            'father_job' => $request->father_job,
            'father_phone' => $request->father_phone,
            'mother_name' => $request->mother_name,
            'mother_job' => $request->mother_job,
            'mother_phone' => $request->mother_phone,
            'guardian_name' => $request->guardian_name,
            'guardian_phone' => $request->guardian_phone,
            'photo' => $photoPath,
            'family_card_file' => $kkPath,
            'certificate_file' => $certPath,
            'status' => 'pending',
        ]);

        return $this->success([
            'applicant' => $applicant->load('academicYear'),
            'registration_number' => $applicant->registration_number,
        ], 'Pendaftaran PPDB berhasil dikirim! Simpan nomor registrasi Anda.');
    }

    /**
     * Check applicant status by registration number, NISN, or NIK
     */
    public function checkStatus($keyword)
    {
        $keyword = trim($keyword);
        if (empty($keyword)) {
            return $this->error('Masukkan Nomor Registrasi, NISN, atau NIK untuk melacak status.', 400);
        }

        $applicant = PpdbApplicant::with(['academicYear', 'enrolledClass'])
            ->where('registration_number', $keyword)
            ->orWhere('nisn', $keyword)
            ->orWhere('nik', $keyword)
            ->first();

        if (! $applicant) {
            return $this->error('Data pendaftaran tidak ditemukan. Pastikan Nomor Registrasi atau NISN sudah benar.', 404);
        }

        return $this->success([
            'applicant' => $applicant,
        ]);
    }
}
