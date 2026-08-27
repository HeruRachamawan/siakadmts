<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TeacherAttendanceMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $setting = SchoolSetting::getSetting();

        $carbonDate = Carbon::parse($date);
        $dayOfWeekEnglish = strtolower($carbonDate->format('l'));
        $weeklyHolidays = $setting->weekly_holidays ?: ['sunday'];
        
        $isWeeklyHoliday = in_array($dayOfWeekEnglish, array_map('strtolower', $weeklyHolidays));
        
        // Check CalendarEvent
        $calendarHoliday = \App\Models\CalendarEvent::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        $isHoliday = $isWeeklyHoliday || !is_null($calendarHoliday);
        $holidayName = null;
        $holidayType = null;

        if ($calendarHoliday) {
            $holidayName = $calendarHoliday->title;
            $holidayType = $calendarHoliday->type ?: 'holiday';
        } elseif ($isWeeklyHoliday) {
            $dayNamesIndo = [
                'sunday' => 'Minggu',
                'monday' => 'Senin',
                'tuesday' => 'Selasa',
                'wednesday' => 'Rabu',
                'thursday' => 'Kamis',
                'friday' => 'Jumat',
                'saturday' => 'Sabtu',
            ];
            $holidayName = 'Hari Libur Mingguan (' . ($dayNamesIndo[$dayOfWeekEnglish] ?? $dayOfWeekEnglish) . ')';
            $holidayType = 'weekly_holiday';
        }

        // Get all teachers ordered chronologically
        $teachers = Teacher::with('user')->orderBy('id', 'asc')->get();

        // Get attendances for selected date
        $attendances = TeacherAttendance::where('date', $date)->get()->keyBy('teacher_id');

        $summary = [
            'total_teachers' => $teachers->count(),
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'tugas_luar' => 0,
            'libur' => 0,
            'belum_absen' => 0,
        ];

        $list = [];

        foreach ($teachers as $index => $t) {
            $att = $attendances->get($t->id);
            
            if ($att) {
                $status = $att->status;
            } elseif ($isHoliday) {
                $status = 'libur';
            } else {
                $status = 'belum_absen';
            }

            if (isset($summary[$status])) {
                $summary[$status]++;
            } else {
                $summary['belum_absen']++;
            }

            $list[] = [
                'no' => $index + 1,
                'teacher_id' => $t->id,
                'full_name' => $t->full_name,
                'nip' => $t->nip ?: '-',
                'nuptk' => $t->nuptk ?: '-',
                'email' => $t->user->email ?? '-',
                'photo_url' => $t->photo_url ?: ($t->user->photo_url ?? null),
                'status' => $status,
                'check_in_time' => $att?->check_in_time ? Carbon::parse($att->check_in_time)->format('H:i:s') : null,
                'check_in_distance_meters' => $att?->check_in_distance_meters,
                'check_out_time' => $att?->check_out_time ? Carbon::parse($att->check_out_time)->format('H:i:s') : null,
                'check_out_distance_meters' => $att?->check_out_distance_meters,
                'notes' => $att?->notes ?? null,
            ];
        }

        return response()->json([
            'date' => $date,
            'setting' => $setting,
            'holiday_info' => [
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_type' => $holidayType,
                'is_weekly_holiday' => $isWeeklyHoliday,
            ],
            'summary' => $summary,
            'teachers' => $list,
        ]);
    }

    public function getHolidays()
    {
        $setting = SchoolSetting::getSetting();
        $holidays = \App\Models\CalendarEvent::orderBy('start_date', 'asc')->get();

        return response()->json([
            'weekly_holidays' => $setting->weekly_holidays ?: ['sunday'],
            'calendar_events' => $holidays,
        ]);
    }

    public function updateWeeklyHolidays(Request $request)
    {
        $request->validate([
            'weekly_holidays' => 'required|array',
            'weekly_holidays.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        $setting = SchoolSetting::getSetting();
        $setting->weekly_holidays = $request->input('weekly_holidays');
        $setting->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Hari libur mingguan berhasil diperbarui!',
            'weekly_holidays' => $setting->weekly_holidays,
        ]);
    }

    public function storeHoliday(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'color' => 'nullable|string|max:30',
        ]);

        if (empty($validated['color'])) {
            $validated['color'] = '#10B981';
        }

        $event = \App\Models\CalendarEvent::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => "Hari libur \"{$event->title}\" berhasil ditambahkan!",
            'data' => $event,
        ]);
    }

    public function deleteHoliday($id)
    {
        $event = \App\Models\CalendarEvent::findOrFail($id);
        $title = $event->title;
        $event->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Hari libur \"{$title}\" berhasil dihapus.",
        ]);
    }

    public function syncNationalAndPhbiHolidays()
    {
        $holidays = [
            // Tahun Ajaran 2026 / 2027
            ['start_date' => '2026-07-07', 'end_date' => '2026-07-18', 'title' => 'Libur Awal Tahun Ajaran Baru 2026/2027', 'type' => 'holiday', 'color' => '#6366F1'],
            ['start_date' => '2026-08-17', 'end_date' => '2026-08-17', 'title' => 'HUT Kemerdekaan Republik Indonesia Ke-81', 'type' => 'holiday', 'color' => '#EF4444'],
            ['start_date' => '2026-08-25', 'end_date' => '2026-08-25', 'title' => 'Maulid Nabi Muhammad SAW 1448 H (PHBI)', 'type' => 'holiday', 'color' => '#10B981'],
            ['start_date' => '2026-10-22', 'end_date' => '2026-10-22', 'title' => 'Hari Santri Nasional (PHBI / Nasional)', 'type' => 'holiday', 'color' => '#059669'],
            ['start_date' => '2026-11-25', 'end_date' => '2026-11-25', 'title' => 'Hari Guru Nasional & HUT PGRI', 'type' => 'holiday', 'color' => '#0284C7'],
            ['start_date' => '2026-12-21', 'end_date' => '2027-01-02', 'title' => 'Libur Akhir Semester Ganjil T.A. 2026/2027', 'type' => 'holiday', 'color' => '#8B5CF6'],
            ['start_date' => '2027-01-01', 'end_date' => '2027-01-01', 'title' => 'Tahun Baru Masehi 2027', 'type' => 'holiday', 'color' => '#3B82F6'],
            ['start_date' => '2027-01-03', 'end_date' => '2027-01-03', 'title' => 'Hari Amal Bakti (HAB) Kementerian Agama RI', 'type' => 'holiday', 'color' => '#10B981'],
            ['start_date' => '2027-02-05', 'end_date' => '2027-02-05', 'title' => 'Isra Mi\'raj Nabi Muhammad SAW 1448 H (PHBI)', 'type' => 'holiday', 'color' => '#10B981'],
            ['start_date' => '2027-02-06', 'end_date' => '2027-02-06', 'title' => 'Tahun Baru Imlek 2578 Kongzili', 'type' => 'holiday', 'color' => '#F59E0B'],
            ['start_date' => '2027-03-09', 'end_date' => '2027-03-09', 'title' => 'Hari Suci Nyepi Tahun Baru Saka 1949', 'type' => 'holiday', 'color' => '#D97706'],
            ['start_date' => '2027-03-10', 'end_date' => '2027-03-13', 'title' => 'Libur Awal Ramadhan 1448 H (PHBI)', 'type' => 'holiday', 'color' => '#059669'],
            ['start_date' => '2027-03-20', 'end_date' => '2027-03-28', 'title' => 'Hari Raya Idul Fitri 1448 H & Cuti Bersama (PHBI)', 'type' => 'holiday', 'color' => '#10B981'],
            ['start_date' => '2027-03-26', 'end_date' => '2027-03-26', 'title' => 'Wafat Isa Al Masih (Jumat Agung)', 'type' => 'holiday', 'color' => '#64748B'],
            ['start_date' => '2027-05-01', 'end_date' => '2027-05-01', 'title' => 'Hari Buruh Internasional (May Day)', 'type' => 'holiday', 'color' => '#EF4444'],
            ['start_date' => '2027-05-06', 'end_date' => '2027-05-06', 'title' => 'Kenaikan Isa Al Masih', 'type' => 'holiday', 'color' => '#64748B'],
            ['start_date' => '2027-05-20', 'end_date' => '2027-05-20', 'title' => 'Hari Raya Waisak 2571', 'type' => 'holiday', 'color' => '#F59E0B'],
            ['start_date' => '2027-05-27', 'end_date' => '2027-05-29', 'title' => 'Hari Raya Idul Adha 1448 H & Hari Tasyrik (PHBI)', 'type' => 'holiday', 'color' => '#10B981'],
            ['start_date' => '2027-06-01', 'end_date' => '2027-06-01', 'title' => 'Hari Lahir Pancasila', 'type' => 'holiday', 'color' => '#EF4444'],
            ['start_date' => '2027-06-16', 'end_date' => '2027-06-16', 'title' => 'Tahun Baru Islam 1449 Hijriyah (PHBI)', 'type' => 'holiday', 'color' => '#10B981'],
            ['start_date' => '2027-06-21', 'end_date' => '2027-07-10', 'title' => 'Libur Kenaikan Kelas / Akhir Tahun Pelajaran 2026/2027', 'type' => 'holiday', 'color' => '#8B5CF6'],
        ];

        $insertedCount = 0;
        foreach ($holidays as $h) {
            $exists = \App\Models\CalendarEvent::where('start_date', $h['start_date'])
                ->where('title', $h['title'])
                ->exists();

            if (! $exists) {
                \App\Models\CalendarEvent::create($h);
                $insertedCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menyinkronkan {$insertedCount} data Hari Libur Nasional & PHBI T.A. 2026/2027 ke Kalender Pendidikan!",
            'inserted_count' => $insertedCount,
            'total_holidays' => \App\Models\CalendarEvent::count(),
        ]);
    }

    public function getPendingRequests()
    {
        $requests = \App\Models\TeacherAttendanceRequest::with('teacher')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['requests' => $requests]);
    }

    public function processRequest(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $req = \App\Models\TeacherAttendanceRequest::findOrFail($id);

        if ($request->action === 'approve') {
            $req->approval_status = 'approved';
            $req->admin_notes = $request->admin_notes;
            $req->save();

            // Update or create TeacherAttendance for that teacher & date
            $att = TeacherAttendance::firstOrNew([
                'teacher_id' => $req->teacher_id,
                'date' => $req->date,
            ]);

            $att->status = $req->target_status;
            if ($req->requested_check_in_time) {
                $att->check_in_time = $req->requested_check_in_time;
            }
            if ($req->requested_check_out_time) {
                $att->check_out_time = $req->requested_check_out_time;
            }
            $att->notes = "Koreksi Disetujui: " . $req->reason;
            $att->save();

            return response()->json(['message' => 'Pengajuan koreksi berhasil DISETUJUI & data presensi telah diperbarui!']);
        } else {
            $req->approval_status = 'rejected';
            $req->admin_notes = $request->admin_notes;
            $req->save();

            return response()->json(['message' => 'Pengajuan koreksi DITOLAK.']);
        }
    }

    public function updateAttendance(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'status' => 'required|in:hadir,terlambat,izin,sakit,tugas_luar,belum_absen',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->status === 'belum_absen') {
            TeacherAttendance::where('teacher_id', $request->teacher_id)
                ->where('date', $request->date)
                ->delete();

            return response()->json(['message' => 'Status presensi guru berhasil di-reset ke Belum Absen!']);
        }

        $att = TeacherAttendance::firstOrNew([
            'teacher_id' => $request->teacher_id,
            'date' => $request->date,
        ]);

        $att->status = $request->status;
        $att->check_in_time = $request->check_in_time ?: null;
        $att->check_out_time = $request->check_out_time ?: null;
        $att->notes = $request->notes ? trim($request->notes) : null;
        $att->save();

        return response()->json(['message' => 'Data presensi guru berhasil diperbarui oleh Admin!']);
    }

    public function resetAttendance(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
        ]);

        TeacherAttendance::where('teacher_id', $request->teacher_id)
            ->where('date', $request->date)
            ->delete();

        return response()->json(['message' => 'Status presensi guru berhasil di-reset ke Belum Absen!']);
    }

    public function getSchoolQr(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $setting = SchoolSetting::getSetting();
        $appSettings = \App\Models\Setting::getSettings();
        
        $token = hash_hmac('sha256', "YASPIN-PRESENSI-{$today}-" . ($setting->latitude ?? '0') . '-' . ($setting->longitude ?? '0'), config('app.key'));
        $qrPayload = "YASPIN-PRESENSI|{$today}|{$token}";

        return response()->json([
            'today' => $today,
            'qr_payload' => $qrPayload,
            'token' => $token,
            'school_name' => $appSettings['app_name'] ?? 'MTS AL-HASANAH',
            'school_address' => $appSettings['school_address'] ?? 'Jl. Raya Ciwidey',
            'setting' => $setting,
        ]);
    }

    public function scanTeacherCard(Request $request)
    {
        $request->validate([
            'qr_payload' => 'required|string',
            'action_type' => 'nullable|in:auto,check_in,check_out',
        ]);

        $payload = trim($request->qr_payload);
        $parts = explode('|', $payload);

        if (count($parts) !== 4 || $parts[0] !== 'TEACHER-ID') {
            return response()->json(['message' => 'Format QR Code tidak valid atau bukan Kartu Tanda Guru resmi.'], 422);
        }

        $teacherId = $parts[1];
        $nip = $parts[2];
        $signature = $parts[3];

        $teacher = Teacher::with('user')->find($teacherId);
        if (!$teacher) {
            return response()->json(['message' => 'Data Guru tidak ditemukan di sistem.'], 404);
        }

        $expectedSig = hash_hmac('sha256', "TEACHER-CARD-{$teacher->id}-" . ($teacher->nip ?: 'NONIP'), config('app.key'));
        if (!hash_equals($expectedSig, $signature)) {
            return response()->json(['message' => 'Verifikasi keaslian Kartu Guru gagal. Pastikan memindai Kartu Guru resmi.'], 422);
        }

        $today = Carbon::today()->toDateString();
        $nowTime = Carbon::now()->toTimeString();
        $setting = SchoolSetting::getSetting();

        $att = TeacherAttendance::firstOrNew([
            'teacher_id' => $teacher->id,
            'date' => $today,
        ]);

        $actionType = $request->action_type ?? 'auto';

        // Decide check_in vs check_out
        if ($actionType === 'auto') {
            if (!$att->check_in_time) {
                $targetAction = 'check_in';
            } elseif (!$att->check_out_time) {
                $targetAction = 'check_out';
            } else {
                return response()->json([
                    'message' => "Guru {$teacher->full_name} sudah menyelesaikan Absen Masuk ({$att->check_in_time}) dan Pulang ({$att->check_out_time}) hari ini.",
                    'teacher' => [
                        'id' => $teacher->id,
                        'full_name' => $teacher->full_name,
                        'nip' => $teacher->nip ?: '-',
                        'photo_url' => $teacher->photo_url,
                        'position' => $teacher->position ?: 'Guru',
                    ],
                    'attendance' => $att,
                ], 422);
            }
        } else {
            $targetAction = $actionType;
        }

        if ($targetAction === 'check_in') {
            if ($att->check_in_time) {
                return response()->json(['message' => "Guru {$teacher->full_name} sudah melakukan Absen Masuk hari ini ({$att->check_in_time})."], 422);
            }

            $isLate = $nowTime > $setting->work_late_time;
            $att->check_in_time = $nowTime;
            $att->check_in_latitude = $setting->latitude;
            $att->check_in_longitude = $setting->longitude;
            $att->check_in_distance_meters = 0;
            $att->status = $isLate ? 'terlambat' : 'hadir';
            $att->notes = 'Presensi Scan Kartu Guru di Meja Piket';
            $att->save();

            $msg = $isLate 
                ? "Bapak/Ibu {$teacher->full_name} berhasil Masuk (Terlambat pada {$nowTime})." 
                : "Bapak/Ibu {$teacher->full_name} berhasil Masuk Tepat Waktu ({$nowTime}).";

            return response()->json([
                'type' => 'check_in',
                'message' => $msg,
                'teacher' => [
                    'id' => $teacher->id,
                    'full_name' => $teacher->full_name,
                    'nip' => $teacher->nip ?: '-',
                    'photo_url' => $teacher->photo_url,
                    'position' => $teacher->position ?: 'Guru',
                ],
                'attendance' => $att,
            ]);
        }

        if ($targetAction === 'check_out') {
            if (!$att->check_in_time && !in_array($att->status, ['hadir', 'terlambat'])) {
                return response()->json(['message' => "Guru {$teacher->full_name} belum melakukan Absen Masuk hari ini."], 422);
            }
            if ($att->check_out_time) {
                return response()->json(['message' => "Guru {$teacher->full_name} sudah melakukan Absen Pulang hari ini ({$att->check_out_time})."], 422);
            }

            $att->check_out_time = $nowTime;
            $att->check_out_latitude = $setting->latitude;
            $att->check_out_longitude = $setting->longitude;
            $att->check_out_distance_meters = 0;
            $att->notes = ($att->notes ? $att->notes . ' | ' : '') . 'Pulang Scan Kartu Piket';
            $att->save();

            return response()->json([
                'type' => 'check_out',
                'message' => "Bapak/Ibu {$teacher->full_name} berhasil Absen Pulang ({$nowTime}). Selamat beristirahat!",
                'teacher' => [
                    'id' => $teacher->id,
                    'full_name' => $teacher->full_name,
                    'nip' => $teacher->nip ?: '-',
                    'photo_url' => $teacher->photo_url,
                    'position' => $teacher->position ?: 'Guru',
                ],
                'attendance' => $att,
            ]);
        }

        return response()->json(['message' => 'Aksi presensi tidak valid.'], 400);
    }
}
