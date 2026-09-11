<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\ClassRoom;
use App\Models\Subject;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = Schedule::with(['classRoom', 'subject', 'teacher']);

        if ($request->filled('class_id')) {
            $query->where(function($q) use ($request) {
                $q->where('class_id', $request->class_id)
                  ->orWhereNull('class_id'); // Includes general all-class activities
            });
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('day')) {
            $query->where('day', strtolower($request->day));
        }

        if ($request->filled('is_activity')) {
            $query->where('is_activity', filter_var($request->is_activity, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('room')) {
            if ($request->room === 'lokal') {
                $query->where('room', 'lokal');
            } else {
                $query->where(function($q) {
                    $q->where('room', 'utama')->orWhereNull('room');
                });
            }
        }

        // Filter out any corrupted nighttime hours (e.g. 22:31)
        $query->where('start_time', '<=', '18:00');

        $schedules = $query->orderBy('day')->orderBy('start_time')->get();

        return response()->json([
            'status' => 'success',
            'data' => $schedules
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSchedule($request);

        // Perform Conflict Validation
        $conflict = $this->checkConflicts($validated);
        if ($conflict) {
            return response()->json([
                'status' => 'error',
                'message' => $conflict
            ], 422);
        }

        $schedule = Schedule::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil ditambahkan',
            'data' => $schedule->load(['classRoom', 'subject', 'teacher'])
        ], 201);
    }

    public function show(Schedule $schedule)
    {
        return response()->json([
            'status' => 'success',
            'data' => $schedule->load(['classRoom', 'subject', 'teacher'])
        ]);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validated = $this->validateSchedule($request);

        // Perform Conflict Validation (excluding current schedule)
        $conflict = $this->checkConflicts($validated, $schedule->id);
        if ($conflict) {
            return response()->json([
                'status' => 'error',
                'message' => $conflict
            ], 422);
        }

        $schedule->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil diperbarui',
            'data' => $schedule->load(['classRoom', 'subject', 'teacher'])
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal berhasil dihapus'
        ]);
    }

    public function generateGeneralActivities(Request $request)
    {
        $classes = ClassRoom::all();

        $activities = [
            // SENIN
            ['day' => 'senin', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => 'UPACARA BENDERA', 'activity_type' => 'upacara'],
            ['day' => 'senin', 'start_time' => '07:30', 'end_time' => '07:50', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'senin', 'start_time' => '10:30', 'end_time' => '11:00', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'senin', 'start_time' => '12:20', 'end_time' => '12:40', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],

            // SELASA
            ['day' => 'selasa', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'selasa', 'start_time' => '10:10', 'end_time' => '10:40', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'selasa', 'start_time' => '12:00', 'end_time' => '12:20', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],

            // RABU
            ['day' => 'rabu', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'rabu', 'start_time' => '10:10', 'end_time' => '10:40', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'rabu', 'start_time' => '12:00', 'end_time' => '12:20', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],

            // KAMIS
            ['day' => 'kamis', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'kamis', 'start_time' => '10:10', 'end_time' => '10:40', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'kamis', 'start_time' => '12:00', 'end_time' => '12:20', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],

            // JUMAT
            ['day' => 'jumat', 'start_time' => '07:00', 'end_time' => '07:45', 'activity_name' => "SHOLAT DHUHA & YASINAN", 'activity_type' => 'religi'],
            ['day' => 'jumat', 'start_time' => '09:45', 'end_time' => '10:15', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'jumat', 'start_time' => '11:00', 'end_time' => '12:30', 'activity_name' => "SHALAT JUM'AT BERJAMA'AH", 'activity_type' => 'religi'],

            // SABTU
            ['day' => 'sabtu', 'start_time' => '07:00', 'end_time' => '07:30', 'activity_name' => "TADARUSAN AL-QUR'AN", 'activity_type' => 'religi'],
            ['day' => 'sabtu', 'start_time' => '10:10', 'end_time' => '10:40', 'activity_name' => 'ISTIRAHAT', 'activity_type' => 'istirahat'],
            ['day' => 'sabtu', 'start_time' => '12:00', 'end_time' => '12:20', 'activity_name' => "SHALAT DZUHUR BERJAMA'AH", 'activity_type' => 'religi'],
        ];

        // First clean up corrupt entries like 22:31 or duplicate activity entries
        Schedule::where('is_activity', true)
            ->where(function($q) {
                $q->where('start_time', '>', '18:00')
                  ->orWhere('activity_name', 'like', '%ISTIRAHAT%')
                  ->orWhere('activity_name', 'like', '%istirahat%')
                  ->orWhere('activity_name', 'like', '%UPACARA%')
                  ->orWhere('activity_name', 'like', '%upacara%')
                  ->orWhere('activity_name', 'like', '%SHALAT%')
                  ->orWhere('activity_name', 'like', '%sholat%')
                  ->orWhere('activity_name', 'like', '%TADARUS%');
            })
            ->delete();

        $created = 0;
        foreach ($activities as $act) {
            if ($classes->count() > 0) {
                foreach ($classes as $cls) {
                    Schedule::create([
                        'day' => $act['day'],
                        'start_time' => $act['start_time'],
                        'end_time' => $act['end_time'],
                        'activity_name' => $act['activity_name'],
                        'activity_type' => $act['activity_type'],
                        'is_activity' => true,
                        'class_id' => $cls->id,
                    ]);
                    $created++;
                }
            } else {
                Schedule::create([
                    'day' => $act['day'],
                    'start_time' => $act['start_time'],
                    'end_time' => $act['end_time'],
                    'activity_name' => $act['activity_name'],
                    'activity_type' => $act['activity_type'],
                    'is_activity' => true,
                    'class_id' => null,
                ]);
                $created++;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Berhasil menyinkronkan {$created} jadwal kegiatan resmi (Upacara, Istirahat, & Sholat) untuk seluruh kelas!",
        ]);
    }

    private function validateSchedule(Request $request)
    {
        return $request->validate([
            'is_activity' => 'required|boolean',
            'activity_name' => 'nullable|required_if:is_activity,true|string|max:255',
            'activity_type' => 'nullable|required_if:is_activity,true|in:upacara,religi,ekstrakurikuler,kokurikuler,istirahat,lainnya',
            'class_id' => 'nullable|required_if:is_activity,false|exists:classes,id',
            'subject_id' => 'nullable|required_if:is_activity,false|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'day' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'room' => 'nullable|string|max:100',
        ]);

        if (empty($validated['teacher_id'])) {
            $validated['teacher_id'] = null;
        }

        return $validated;
    }

    /**
     * Core Anti-Conflict Checker Engine
     */
    private function checkConflicts(array $data, $ignoreId = null)
    {
        $day = strtolower($data['day']);
        $start = $data['start_time'];
        $end = $data['end_time'];
        $isActivity = $data['is_activity'] ?? false;
        $classId = $data['class_id'] ?? null;
        $teacherId = $data['teacher_id'] ?? null;
        $room = ($data['room'] ?? 'utama') === 'lokal' ? 'lokal' : 'utama';

        // Helper time overlap condition: Two intervals [startA, endA) and [startB, endB) overlap iff startA < endB AND endA > startB
        $overlapCondition = function ($q) use ($start, $end) {
            $q->where('start_time', '<', $end)
              ->where('end_time', '>', $start);
        };

        // Helper room condition to separate Jadwal Utama and Jadwal Lokal
        $roomCondition = function ($q) use ($room) {
            if ($room === 'lokal') {
                $q->where('room', 'lokal');
            } else {
                $q->where(function ($sq) {
                    $sq->where('room', 'utama')->orWhereNull('room');
                });
            }
        };

        // 1. Check General All-Classes Activity Conflict
        // If an activity for ALL classes (class_id = null) exists in this time slot
        $generalActivity = Schedule::where('day', $day)
            ->whereNull('class_id')
            ->where('is_activity', true)
            ->where($roomCondition)
            ->where($overlapCondition)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->first();

        if ($generalActivity) {
            $actName = $generalActivity->activity_name ?? 'Kegiatan Umum';
            return "Bentrok Kegiatan: Slot waktu ($start - $end) telah terisi kegiatan '$actName' di kategori jadwal ini.";
        }

        // If trying to add a General Activity for ALL classes, check if any class has a schedule at this time
        if ($isActivity && empty($classId)) {
            $existingSchedule = Schedule::where('day', $day)
                ->where($roomCondition)
                ->where($overlapCondition)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->first();

            if ($existingSchedule) {
                return "Gagal Menambahkan Kegiatan: Sudah ada jadwal pelajaran/kegiatan lain pada hari " . ucfirst($day) . " jam $start - $end.";
            }
        }

        // 2. Check Teacher Conflict (if teacher_id is specified)
        if ($teacherId) {
            $teacherConflict = Schedule::where('day', $day)
                ->where('teacher_id', $teacherId)
                ->where($roomCondition)
                ->where($overlapCondition)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->with(['classRoom', 'subject'])
                ->first();

            if ($teacherConflict) {
                $teacher = Teacher::find($teacherId);
                $teacherName = $teacher ? $teacher->full_name : 'Guru';
                $className = $teacherConflict->classRoom ? $teacherConflict->classRoom->name : 'Kelas lain';
                $subjectName = $teacherConflict->subject ? $teacherConflict->subject->name : 'Pelajaran';
                $cStart = $teacherConflict->start_time;
                $cEnd = $teacherConflict->end_time;

                return "Bentrok Jadwal Guru! $teacherName sudah mengajar $subjectName di $className pada hari " . ucfirst($day) . " jam $cStart - $cEnd.";
            }
        }

        // 3. Check Class Conflict (if class_id is specified)
        if ($classId) {
            $classConflict = Schedule::where('day', $day)
                ->where('class_id', $classId)
                ->where($roomCondition)
                ->where($overlapCondition)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->with(['subject', 'teacher'])
                ->first();

            if ($classConflict) {
                $classObj = ClassRoom::find($classId);
                $className = $classObj ? $classObj->name : 'Kelas';
                $cStart = $classConflict->start_time;
                $cEnd = $classConflict->end_time;
                
                $title = $classConflict->is_activity 
                    ? "kegiatan '" . ($classConflict->activity_name ?? 'Kegiatan') . "'"
                    : "pelajaran '" . ($classConflict->subject->name ?? 'Pelajaran') . "'";

                return "Bentrok Jadwal Kelas! $className sudah memiliki $title pada hari " . ucfirst($day) . " jam $cStart - $cEnd.";
            }
        }

        return null; // No conflict found!
    }

    public static function getDefaultTimeSlots(): array
    {
        return [
            'senin' => [
                ['no' => '0', 'start' => '07.00', 'end' => '07.30', 'isGeneral' => true, 'title' => 'UPACARA BENDERA'],
                ['no' => '1', 'start' => '07.30', 'end' => '07.50', 'isGeneral' => true, 'title' => "TADARUSAN AL-QUR'AN"],
                ['no' => '2', 'start' => '07.50', 'end' => '08.30', 'isSlot' => true, 'title' => ''],
                ['no' => '3', 'start' => '08.30', 'end' => '09.10', 'isSlot' => true, 'title' => ''],
                ['no' => '4', 'start' => '09.10', 'end' => '09.50', 'isSlot' => true, 'title' => ''],
                ['no' => '5', 'start' => '09.50', 'end' => '10.30', 'isSlot' => true, 'title' => ''],
                ['no' => '6', 'start' => '10.30', 'end' => '11.00', 'isBreak' => true, 'title' => 'ISTIRAHAT'],
                ['no' => '7', 'start' => '11.00', 'end' => '11.40', 'isSlot' => true, 'title' => ''],
                ['no' => '8', 'start' => '11.40', 'end' => '12.20', 'isSlot' => true, 'title' => ''],
                ['no' => '9', 'start' => '12.20', 'end' => '12.40', 'isGeneral' => true, 'title' => "SHALAT DZUHUR BERJAMA'AH"],
            ],
            'selasa_sabtu' => [
                ['no' => '0', 'start' => '07.00', 'end' => '07.30', 'isGeneral' => true, 'title' => "TADARUSAN AL-QUR'AN"],
                ['no' => '1', 'start' => '07.30', 'end' => '08.10', 'isSlot' => true, 'title' => ''],
                ['no' => '2', 'start' => '08.10', 'end' => '08.50', 'isSlot' => true, 'title' => ''],
                ['no' => '3', 'start' => '08.50', 'end' => '09.30', 'isSlot' => true, 'title' => ''],
                ['no' => '4', 'start' => '09.30', 'end' => '10.10', 'isSlot' => true, 'title' => ''],
                ['no' => '5', 'start' => '10.10', 'end' => '10.40', 'isBreak' => true, 'title' => 'ISTIRAHAT'],
                ['no' => '6', 'start' => '10.40', 'end' => '11.20', 'isSlot' => true, 'title' => ''],
                ['no' => '7', 'start' => '11.20', 'end' => '12.00', 'isSlot' => true, 'title' => ''],
                ['no' => '8', 'start' => '12.00', 'end' => '12.20', 'isGeneral' => true, 'title' => "SHALAT DZUHUR BERJAMA'AH"],
            ],
            'jumat' => [
                ['no' => '0', 'start' => '07.00', 'end' => '07.45', 'isGeneral' => true, 'title' => 'SHOLAT DHUHA & YASINAN'],
                ['no' => '1', 'start' => '07.45', 'end' => '08.25', 'isSlot' => true, 'title' => ''],
                ['no' => '2', 'start' => '08.25', 'end' => '09.05', 'isSlot' => true, 'title' => ''],
                ['no' => '3', 'start' => '09.05', 'end' => '09.45', 'isSlot' => true, 'title' => ''],
                ['no' => '4', 'start' => '09.45', 'end' => '10.15', 'isBreak' => true, 'title' => "ISTIRAHAT JUM'AT"],
                ['no' => '5', 'start' => '10.15', 'end' => '10.55', 'isSlot' => true, 'title' => ''],
                ['no' => '6', 'start' => '11.00', 'end' => '12.30', 'isGeneral' => true, 'title' => "SHALAT JUM'AT BERJAMA'AH"],
            ],
        ];
    }

    public static function getDefaultTimeSlotsLokal(): array
    {
        return [
            'senin' => [
                ['no' => '1', 'start' => '07.30', 'end' => '08.10', 'isSlot' => true, 'title' => ''],
                ['no' => '2', 'start' => '08.10', 'end' => '08.50', 'isSlot' => true, 'title' => ''],
                ['no' => '3', 'start' => '08.50', 'end' => '09.30', 'isSlot' => true, 'title' => ''],
                ['no' => '4', 'start' => '09.30', 'end' => '10.10', 'isSlot' => true, 'title' => ''],
                ['no' => '5', 'start' => '10.10', 'end' => '10.40', 'isBreak' => true, 'title' => 'ISTIRAHAT'],
                ['no' => '6', 'start' => '10.40', 'end' => '11.20', 'isSlot' => true, 'title' => ''],
                ['no' => '7', 'start' => '11.20', 'end' => '12.00', 'isSlot' => true, 'title' => ''],
                ['no' => '8', 'start' => '12.00', 'end' => '12.30', 'isGeneral' => true, 'title' => "SHALAT DZUHUR BERJAMA'AH"],
            ],
            'selasa_sabtu' => [
                ['no' => '1', 'start' => '07.30', 'end' => '08.10', 'isSlot' => true, 'title' => ''],
                ['no' => '2', 'start' => '08.10', 'end' => '08.50', 'isSlot' => true, 'title' => ''],
                ['no' => '3', 'start' => '08.50', 'end' => '09.30', 'isSlot' => true, 'title' => ''],
                ['no' => '4', 'start' => '09.30', 'end' => '10.10', 'isSlot' => true, 'title' => ''],
                ['no' => '5', 'start' => '10.10', 'end' => '10.40', 'isBreak' => true, 'title' => 'ISTIRAHAT'],
                ['no' => '6', 'start' => '10.40', 'end' => '11.20', 'isSlot' => true, 'title' => ''],
                ['no' => '7', 'start' => '11.20', 'end' => '12.00', 'isSlot' => true, 'title' => ''],
                ['no' => '8', 'start' => '12.00', 'end' => '12.30', 'isGeneral' => true, 'title' => "SHALAT DZUHUR BERJAMA'AH"],
            ],
            'jumat' => [
                ['no' => '1', 'start' => '07.30', 'end' => '08.10', 'isSlot' => true, 'title' => ''],
                ['no' => '2', 'start' => '08.10', 'end' => '08.50', 'isSlot' => true, 'title' => ''],
                ['no' => '3', 'start' => '08.50', 'end' => '09.30', 'isSlot' => true, 'title' => ''],
                ['no' => '4', 'start' => '09.30', 'end' => '10.00', 'isBreak' => true, 'title' => "ISTIRAHAT JUM'AT"],
                ['no' => '5', 'start' => '10.00', 'end' => '10.40', 'isSlot' => true, 'title' => ''],
                ['no' => '6', 'start' => '11.00', 'end' => '12.30', 'isGeneral' => true, 'title' => "SHALAT JUM'AT BERJAMA'AH"],
            ],
        ];
    }

    public function getTimeSlots(Request $request)
    {
        $group = $request->input('group', 'utama');
        $settingKey = ($group === 'lokal') ? 'schedule_time_slots_lokal' : 'schedule_time_slots';
        $defaults = ($group === 'lokal') ? self::getDefaultTimeSlotsLokal() : self::getDefaultTimeSlots();

        $raw = \App\Models\Setting::where('key', $settingKey)->value('value');

        if ($raw) {
            $saved = json_decode($raw, true);
            if (is_array($saved)) {
                return response()->json([
                    'status' => 'success',
                    'group' => $group,
                    'data' => [
                        'senin' => $saved['senin'] ?? $defaults['senin'],
                        'selasa_sabtu' => $saved['selasa_sabtu'] ?? $defaults['selasa_sabtu'],
                        'jumat' => $saved['jumat'] ?? $defaults['jumat'],
                    ]
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'group' => $group,
            'data' => $defaults
        ]);
    }

    public function saveTimeSlots(Request $request)
    {
        $group = $request->input('group', 'utama');
        if (!in_array($group, ['utama', 'lokal'])) {
            $group = 'utama';
        }

        // Support both nested under 'slots' or flat root level
        $slots = $request->input('slots');
        if (is_array($slots)) {
            $senin = $slots['senin'] ?? $request->input('senin');
            $selasaSabtu = $slots['selasa_sabtu'] ?? $request->input('selasa_sabtu');
            $jumat = $slots['jumat'] ?? $request->input('jumat');
        } else {
            $senin = $request->input('senin');
            $selasaSabtu = $request->input('selasa_sabtu');
            $jumat = $request->input('jumat');
        }

        if (!is_array($senin) || !is_array($selasaSabtu) || !is_array($jumat)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format data slot waktu tidak valid. Memerlukan slot senin, selasa_sabtu, dan jumat.'
            ], 422);
        }

        $settingKey = ($group === 'lokal') ? 'schedule_time_slots_lokal' : 'schedule_time_slots';

        $dataToSave = [
            'senin' => array_values($senin),
            'selasa_sabtu' => array_values($selasaSabtu),
            'jumat' => array_values($jumat),
        ];

        \App\Models\Setting::updateOrCreate(
            ['key' => $settingKey],
            ['value' => json_encode($dataToSave)]
        );

        return response()->json([
            'status' => 'success',
            'group' => $group,
            'message' => 'Pengaturan slot waktu jadwal ' . ($group === 'lokal' ? 'lokal' : 'utama') . ' berhasil disimpan',
            'data' => $dataToSave
        ]);
    }

    public function resetTimeSlots(Request $request)
    {
        $group = $request->input('group', 'utama');
        $settingKey = ($group === 'lokal') ? 'schedule_time_slots_lokal' : 'schedule_time_slots';
        $defaults = ($group === 'lokal') ? self::getDefaultTimeSlotsLokal() : self::getDefaultTimeSlots();

        \App\Models\Setting::where('key', $settingKey)->delete();

        return response()->json([
            'status' => 'success',
            'group' => $group,
            'message' => 'Slot waktu berhasil dikembalikan ke standar madrasah',
            'data' => $defaults
        ]);
    }
}
