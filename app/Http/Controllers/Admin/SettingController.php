<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();

        if (!empty($settings['app_logo'])) {
            $settings['app_logo_url'] = asset('storage/' . ltrim(preg_replace('/^(\/?storage\/)+/', '', $settings['app_logo']), '/'));
        }

        $settings['academic_year_id'] = $settings['academic_year_id'] ?? ($activeYear?->id ?? null);
        $settings['active_academic_year'] = $activeYear;

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'nullable|string|max:255',
            'app_tagline' => 'nullable|string|max:255',
            'school_address' => 'nullable|string',
            'principal_teacher_id' => 'nullable|exists:teachers,id',
            'principal_message' => 'nullable|string',
            'principal_description' => 'nullable|string',
            'school_vision' => 'nullable|string',
            'school_mission' => 'nullable|string',
            'school_accreditation' => 'nullable|string|max:50',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'hero_title' => 'nullable|string|max:255',
            'hero_background' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'google_maps_embed' => 'nullable|string',
            'google_maps_link' => 'nullable|string',
            'school_phone' => 'nullable|string|max:100',
            'school_email' => 'nullable|string|max:255',
        ]);

        $settings = $request->only([
            'app_name', 'app_tagline', 'school_address', 'academic_year_id',
            'principal_teacher_id', 'principal_message', 'principal_description', 'school_vision', 'school_mission',
            'hero_title', 'school_accreditation',
            'google_maps_embed', 'google_maps_link', 'school_phone', 'school_email'
        ]);

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
        }

        if ($request->filled('academic_year_id')) {
            \App\Models\AcademicYear::query()->update(['is_active' => false]);
            \App\Models\AcademicYear::where('id', $request->academic_year_id)->update(['is_active' => true]);
        }

        if ($request->hasFile('app_logo')) {
            $oldLogo = Setting::where('key', 'app_logo')->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }

            $path = $request->file('app_logo')->store('settings', 'public');
            Setting::updateOrCreate(
                ['key' => 'app_logo'],
                ['value' => $path]
            );
        }

        if ($request->hasFile('hero_background')) {
            $oldBg = Setting::where('key', 'hero_background')->first();
            if ($oldBg && $oldBg->value) {
                Storage::disk('public')->delete($oldBg->value);
            }

            $path = $request->file('hero_background')->store('settings', 'public');
            Setting::updateOrCreate(
                ['key' => 'hero_background'],
                ['value' => $path]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('public_website_data');

        return response()->json([
            'status' => 'success',
            'message' => 'Pengaturan berhasil diperbarui'
        ]);
    }
}
