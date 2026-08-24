<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TeacherAttendanceController extends Controller
{
    private function getTeacher(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher && $user->role === 'admin') {
            $teacher = Teacher::first();
        }
        return $teacher;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius in meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (int) round($earthRadius * $c);
    }

    public function today(Request $request)
    {
        $teacher = $this->getTeacher($request);
        if (!$teacher) {
            return response()->json(['message' => 'Data guru tidak ditemukan.'], 404);
        }

        $today = Carbon::today()->toDateString();
        $setting = SchoolSetting::getSetting();
        $attendance = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'setting' => $setting,
            'today_date' => $today,
            'attendance' => $attendance,
            'teacher' => [
                'id' => $teacher->id,
                'full_name' => $teacher->full_name,
                'photo_url' => $teacher->photo_url,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $teacher = $this->getTeacher($request);
        if (!$teacher) {
            return response()->json(['message' => 'Data guru tidak ditemukan.'], 404);
        }

        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();
        $setting = SchoolSetting::getSetting();

        $request->validate([
            'type' => 'required|in:check_in,check_out,absence',
            'status' => 'nullable|in:hadir,terlambat,izin,sakit,tugas_luar',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);

        $type = $request->type;
        $status = $request->status;
        $notes = trim($request->notes ?? '');

        $attendance = TeacherAttendance::firstOrNew([
            'teacher_id' => $teacher->id,
            'date' => $today,
        ]);

        // Modus Izin / Sakit / Tugas Luar
        if ($type === 'absence') {
            if (!in_array($status, ['izin', 'sakit', 'tugas_luar'])) {
                return response()->json(['message' => 'Status ketidakhadiran tidak valid.'], 422);
            }
            if (empty($notes)) {
                return response()->json(['message' => 'Keterangan wajib diisi untuk Izin, Sakit, atau Tugas Luar.'], 422);
            }

            $attendance->status = $status;
            $attendance->notes = $notes;
            $attendance->save();

            return response()->json([
                'message' => 'Berhasil mengirim keterangan ' . strtoupper($status) . '!',
                'attendance' => $attendance,
            ]);
        }

        // Modus Hadir Masuk / Pulang (Check Geolocation Radius)
        $lat = $request->latitude;
        $lng = $request->longitude;

        if (!$lat || !$lng) {
            return response()->json(['message' => 'Lokasi GPS tidak terdeteksi. Harap aktifkan GPS HP/perangkat Anda.'], 422);
        }

        $distance = $this->calculateDistance($setting->latitude, $setting->longitude, $lat, $lng);

        if ($distance > $setting->max_radius_meters) {
            return response()->json([
                'message' => "Posisi Anda berada di luar radius sekolah ({$distance} meter). Maksimal radius adalah {$setting->max_radius_meters} meter.",
                'distance' => $distance,
                'max_radius' => $setting->max_radius_meters,
            ], 422);
        }

        if ($type === 'check_in') {
            if ($attendance->check_in_time) {
                return response()->json(['message' => 'Anda sudah melakukan Absen Masuk hari ini.'], 422);
            }

            $isLate = $nowTime > $setting->work_late_time;
            $attendance->check_in_time = $nowTime;
            $attendance->check_in_latitude = $lat;
            $attendance->check_in_longitude = $lng;
            $attendance->check_in_distance_meters = $distance;
            $attendance->status = $isLate ? 'terlambat' : 'hadir';
            if ($notes) $attendance->notes = $notes;
            $attendance->save();

            $msg = $isLate ? 'Absen Masuk berhasil (Terlambat).' : 'Absen Masuk berhasil tepat waktu!';
            return response()->json(['message' => $msg, 'attendance' => $attendance]);
        }

        if ($type === 'check_out') {
            if (!$attendance->check_in_time && !in_array($attendance->status, ['hadir', 'terlambat'])) {
                return response()->json(['message' => 'Anda belum melakukan Absen Masuk hari ini.'], 422);
            }
            if ($attendance->check_out_time) {
                return response()->json(['message' => 'Anda sudah melakukan Absen Pulang hari ini.'], 422);
            }

            $attendance->check_out_time = $nowTime;
            $attendance->check_out_latitude = $lat;
            $attendance->check_out_longitude = $lng;
            $attendance->check_out_distance_meters = $distance;
            $attendance->save();

            return response()->json(['message' => 'Absen Pulang berhasil! Selamat beristirahat.', 'attendance' => $attendance]);
        }

        return response()->json(['message' => 'Tipe absensi tidak valid.'], 400);
    }

    public function history(Request $request)
    {
        $teacher = $this->getTeacher($request);
        if (!$teacher) {
            return response()->json(['message' => 'Data guru tidak ditemukan.'], 404);
        }

        $month = $request->query('month', Carbon::now()->format('Y-m'));

        $attendances = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', 'LIKE', "{$month}%")
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'month' => $month,
            'attendances' => $attendances,
        ]);
    }

    public function requests(Request $request)
    {
        $teacher = $this->getTeacher($request);
        if (!$teacher) {
            return response()->json(['message' => 'Data guru tidak ditemukan.'], 404);
        }

        $requests = \App\Models\TeacherAttendanceRequest::where('teacher_id', $teacher->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['requests' => $requests]);
    }

    public function submitRequest(Request $request)
    {
        $teacher = $this->getTeacher($request);
        if (!$teacher) {
            return response()->json(['message' => 'Data guru tidak ditemukan.'], 404);
        }

        $request->validate([
            'date' => 'required|date',
            'target_status' => 'required|in:hadir,terlambat,izin,sakit,tugas_luar',
            'requested_check_in_time' => 'nullable',
            'requested_check_out_time' => 'nullable',
            'reason' => 'required|string|max:1000',
        ]);

        $req = \App\Models\TeacherAttendanceRequest::create([
            'teacher_id' => $teacher->id,
            'date' => $request->date,
            'target_status' => $request->target_status,
            'requested_check_in_time' => $request->requested_check_in_time ?: null,
            'requested_check_out_time' => $request->requested_check_out_time ?: null,
            'reason' => trim($request->reason),
            'approval_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan permohonan koreksi absen berhasil dikirim! Menunggu persetujuan Admin.',
            'request' => $req,
        ]);
    }

    public function recap(Request $request)
    {
        $teacher = $this->getTeacher($request);
        if (!$teacher) {
            return response()->json(['message' => 'Data guru tidak ditemukan.'], 404);
        }

        $monthStr = $request->query('month', Carbon::now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $monthStr);

        $daysInMonth = $carbonMonth->daysInMonth;
        $attendances = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', 'LIKE', "{$monthStr}%")
            ->get()
            ->keyBy('date');

        $summary = [
            'total_days' => $daysInMonth,
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'tugas_luar' => 0,
            'libur' => 0,
            'belum_absen' => 0,
            'total_present' => 0,
            'attendance_percentage' => 0,
        ];

        $details = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%s-%02d', $monthStr, $day);
            $dateCarbon = Carbon::parse($dateStr);
            $isWeekend = $dateCarbon->isWeekend();

            $att = $attendances->get($dateStr);
            $status = $att ? $att->status : ($isWeekend ? 'libur' : 'belum_absen');

            if (isset($summary[$status])) {
                $summary[$status]++;
            }

            if (in_array($status, ['hadir', 'terlambat'])) {
                $summary['total_present']++;
            }

            $workDuration = '-';
            if ($att && $att->check_in_time && $att->check_out_time) {
                $start = Carbon::parse($att->check_in_time);
                $end = Carbon::parse($att->check_out_time);
                $diffMinutes = $start->diffInMinutes($end);
                $hours = floor($diffMinutes / 60);
                $mins = $diffMinutes % 60;
                $workDuration = "{$hours}j {$mins}m";
            }

            $details[] = [
                'day' => $day,
                'date' => $dateStr,
                'day_name' => $dateCarbon->locale('id')->isoFormat('dddd'),
                'is_weekend' => $isWeekend,
                'status' => $status,
                'check_in_time' => $att?->check_in_time,
                'check_in_distance' => $att?->check_in_distance_meters,
                'check_out_time' => $att?->check_out_time,
                'check_out_distance' => $att?->check_out_distance_meters,
                'work_duration' => $workDuration,
                'notes' => $att?->notes ?? null,
            ];
        }

        $effectiveDays = max(1, $daysInMonth - $summary['libur']);
        $summary['attendance_percentage'] = round(($summary['total_present'] / $effectiveDays) * 100, 1);

        $schoolName = \App\Models\Setting::where('key', 'app_name')->value('value') ?? 'SEKOLAH';

        return response()->json([
            'month' => $monthStr,
            'month_label' => $carbonMonth->locale('id')->isoFormat('MMMM YYYY'),
            'teacher' => [
                'full_name' => $teacher->full_name,
                'nip' => $teacher->nip ?: '-',
                'position' => $teacher->position ?: 'Guru',
                'photo_url' => $teacher->photo_url,
            ],
            'school_name' => $schoolName,
            'summary' => $summary,
            'details' => $details,
        ]);
    }

    public function scanQr(Request $request)
    {
        $teacher = $this->getTeacher($request);
        if (!$teacher) {
            return response()->json(['message' => 'Data guru tidak ditemukan.'], 404);
        }

        $request->validate([
            'qr_payload' => 'required|string',
            'type' => 'required|in:check_in,check_out',
            'notes' => 'nullable|string|max:500',
        ]);

        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();
        $setting = SchoolSetting::getSetting();

        $payload = trim($request->qr_payload);
        $parts = explode('|', $payload);

        // Case A: Kartu Guru Personal (TEACHER-ID|{id}|{nip}|{signature})
        if (count($parts) === 4 && $parts[0] === 'TEACHER-ID') {
            $scannedTeacherId = $parts[1];
            $scannedNip = $parts[2];
            $scannedSig = $parts[3];

            if ((int)$scannedTeacherId !== (int)$teacher->id) {
                $otherTeacher = \App\Models\Teacher::find($scannedTeacherId);
                $name = $otherTeacher ? $otherTeacher->full_name : 'guru lain';
                return response()->json(['message' => "QR Code yang Anda pindai adalah milik {$name}. Harap pindai QR Code Presensi Sekolah atau Kartu Anda sendiri."], 422);
            }

            $expectedSig = hash_hmac('sha256', "TEACHER-CARD-{$teacher->id}-" . ($teacher->nip ?: 'NONIP'), config('app.key'));
            if (!hash_equals($expectedSig, $scannedSig)) {
                return response()->json(['message' => 'Verifikasi keaslian Kartu Guru gagal.'], 422);
            }
        } 
        // Case B: QR Code Harian Sekolah (YASPIN-PRESENSI|{date}|{token})
        elseif (count($parts) === 3 && $parts[0] === 'YASPIN-PRESENSI') {
            $qrDate = $parts[1];
            $qrToken = $parts[2];

            if ($qrDate !== $today) {
                return response()->json(['message' => "QR Code ini sudah kadaluarsa (Tanggal QR: {$qrDate}). Silakan gunakan QR Code hari ini ({$today})."], 422);
            }

            $expectedToken = hash_hmac('sha256', "YASPIN-PRESENSI-{$today}-" . ($setting->latitude ?? '0') . '-' . ($setting->longitude ?? '0'), config('app.key'));
            if (!hash_equals($expectedToken, $qrToken)) {
                return response()->json(['message' => 'Verifikasi token keamanan QR Code gagal. Pastikan memindai QR Code resmi sekolah hari ini.'], 422);
            }
        } else {
            return response()->json(['message' => 'Format QR Code tidak valid. Pastikan memindai QR Code Presensi Resmi Sekolah atau Kartu Guru Anda.'], 422);
        }

        $type = $request->type;
        $notes = trim($request->notes ?? '');

        $attendance = TeacherAttendance::firstOrNew([
            'teacher_id' => $teacher->id,
            'date' => $today,
        ]);

        if ($type === 'check_in') {
            if ($attendance->check_in_time) {
                return response()->json(['message' => 'Anda sudah melakukan Absen Masuk hari ini.'], 422);
            }

            $isLate = $nowTime > $setting->work_late_time;
            $attendance->check_in_time = $nowTime;
            $attendance->check_in_latitude = $setting->latitude;
            $attendance->check_in_longitude = $setting->longitude;
            $attendance->check_in_distance_meters = 0;
            $attendance->status = $isLate ? 'terlambat' : 'hadir';
            $attendance->notes = $notes ? ($notes . ' (Scan QR)') : 'Presensi via Scan QR Code Sekolah';
            $attendance->save();

            $msg = $isLate ? 'Absen Masuk via QR berhasil (Terlambat).' : 'Absen Masuk via QR berhasil tepat waktu!';
            return response()->json(['message' => $msg, 'attendance' => $attendance]);
        }

        if ($type === 'check_out') {
            if (!$attendance->check_in_time && !in_array($attendance->status, ['hadir', 'terlambat'])) {
                return response()->json(['message' => 'Anda belum melakukan Absen Masuk hari ini.'], 422);
            }
            if ($attendance->check_out_time) {
                return response()->json(['message' => 'Anda sudah melakukan Absen Pulang hari ini.'], 422);
            }

            $attendance->check_out_time = $nowTime;
            $attendance->check_out_latitude = $setting->latitude;
            $attendance->check_out_longitude = $setting->longitude;
            $attendance->check_out_distance_meters = 0;
            if ($notes) {
                $attendance->notes = ($attendance->notes ? $attendance->notes . ' | ' : '') . $notes . ' (Pulang Scan QR)';
            }
            $attendance->save();

            return response()->json(['message' => 'Absen Pulang via QR berhasil! Selamat beristirahat.', 'attendance' => $attendance]);
        }

        return response()->json(['message' => 'Tipe presensi tidak valid.'], 400);
    }
}
