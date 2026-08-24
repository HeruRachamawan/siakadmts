<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    public function show()
    {
        $setting = SchoolSetting::getSetting();
        
        // Try to sync with Google Maps embed/link from App Settings if present
        $embed = \App\Models\Setting::where('key', 'google_maps_embed')->value('value');
        $mapsLink = \App\Models\Setting::where('key', 'google_maps_link')->value('value');
        $fullText = ($embed ?? '') . ' ' . ($mapsLink ?? '');

        $extractedLat = null;
        $extractedLng = null;

        if (preg_match('/!2d([-?\d.]+)!3d([-?\d.]+)/', $fullText, $matches)) {
            $extractedLng = (float)$matches[1];
            $extractedLat = (float)$matches[2];
        } elseif (preg_match('/@([-?\d.]+),([-?\d.]+)/', $fullText, $matches)) {
            $extractedLat = (float)$matches[1];
            $extractedLng = (float)$matches[2];
        }

        if ($extractedLat && $extractedLng) {
            $setting->maps_extracted_lat = $extractedLat;
            $setting->maps_extracted_lng = $extractedLng;
        }

        return response()->json($setting);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'school_name' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'max_radius_meters' => 'required|integer|min:1|max:500000',
            'work_start_time' => 'nullable|string',
            'work_late_time' => 'nullable|string',
            'work_end_time' => 'nullable|string',
        ]);

        $setting = SchoolSetting::getSetting();
        $setting->update($validated);

        return response()->json([
            'message' => 'Pengaturan lokasi & radius sekolah berhasil diperbarui!',
            'data' => $setting,
        ]);
    }
}
