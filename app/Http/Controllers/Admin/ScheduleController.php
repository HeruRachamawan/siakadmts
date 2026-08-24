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

    private function validateSchedule(Request $request)
    {
        return $request->validate([
            'is_activity' => 'required|boolean',
            'activity_name' => 'nullable|required_if:is_activity,true|string|max:255',
            'activity_type' => 'nullable|required_if:is_activity,true|in:upacara,religi,ekstrakurikuler,kokurikuler,istirahat,lainnya',
            'class_id' => 'nullable|required_if:is_activity,false|exists:classes,id',
            'subject_id' => 'nullable|required_if:is_activity,false|exists:subjects,id',
            'teacher_id' => 'nullable|required_if:is_activity,false|exists:teachers,id',
            'day' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'room' => 'nullable|string|max:100',
        ]);
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

        // Helper time overlap condition: Two intervals [startA, endA) and [startB, endB) overlap iff startA < endB AND endA > startB
        $overlapCondition = function ($q) use ($start, $end) {
            $q->where('start_time', '<', $end)
              ->where('end_time', '>', $start);
        };

        // 1. Check General All-Classes Activity Conflict
        // If an activity for ALL classes (class_id = null) exists in this time slot
        $generalActivity = Schedule::where('day', $day)
            ->whereNull('class_id')
            ->where('is_activity', true)
            ->where($overlapCondition)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->first();

        if ($generalActivity) {
            $actName = $generalActivity->activity_name ?? 'Kegiatan Umum';
            return "Bentrok Kegiatan Sekolah: Slot waktu ($start - $end) telah terisi kegiatan '$actName' untuk seluruh sekolah.";
        }

        // If trying to add a General Activity for ALL classes, check if any class has a schedule at this time
        if ($isActivity && empty($classId)) {
            $existingSchedule = Schedule::where('day', $day)
                ->where($overlapCondition)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->first();

            if ($existingSchedule) {
                return "Gagal Menambahkan Kegiatan Umum: Sudah ada jadwal pelajaran/kegiatan lain pada hari " . ucfirst($day) . " jam $start - $end.";
            }
        }

        // 2. Check Teacher Conflict (if teacher_id is specified)
        if ($teacherId) {
            $teacherConflict = Schedule::where('day', $day)
                ->where('teacher_id', $teacherId)
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
}
