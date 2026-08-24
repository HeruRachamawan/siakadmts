<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $classId = $request->input('class_id');
        $month = $request->input('month', now()->format('Y-m')); // e.g. 2026-08
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        // Determine Date Filter Range
        if ($startDate && $endDate) {
            $from = $startDate;
            $to = $endDate;
        } else if ($month) {
            $from = $month . '-01';
            $to = date('Y-m-t', strtotime($from));
        } else {
            $from = now()->startOfMonth()->toDateString();
            $to = now()->endOfMonth()->toDateString();
        }

        // Query Students
        $query = Student::with(['classRoom.academicYear']);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('full_name')->get();

        // Fetch all attendance records in range
        $attendances = Attendance::whereBetween('date', [$from, $to])
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->get();

        // Group attendances by student_id
        $attendanceByStudent = $attendances->groupBy('student_id');

        $reportData = [];
        $totalPresentSum = 0;
        $totalSickSum = 0;
        $totalPermissionSum = 0;
        $totalAlphaSum = 0;
        $highAlphaCount = 0;

        foreach ($students as $student) {
            $records = $attendanceByStudent->get($student->id, collect());
            
            $present = $records->where('status', 'present')->count();
            $sick = $records->where('status', 'sick')->count();
            $permission = $records->where('status', 'permission')->count();
            $alpha = $records->where('status', 'alpha')->count();
            $totalDays = $records->count();

            $percentage = $totalDays > 0 
                ? round(($present / $totalDays) * 100, 1)
                : 0.0;

            $highAlphaAlert = $alpha >= 3;
            if ($highAlphaAlert) {
                $highAlphaCount++;
            }

            $totalPresentSum += $present;
            $totalSickSum += $sick;
            $totalPermissionSum += $permission;
            $totalAlphaSum += $alpha;

            $reportData[] = [
                'student_id' => $student->id,
                'full_name' => $student->full_name,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'gender' => $student->gender,
                'photo_url' => $student->photo_url,
                'class_name' => $student->classRoom?->name ?? '-',
                'class_id' => $student->class_id,
                'present' => $present,
                'sick' => $sick,
                'permission' => $permission,
                'alpha' => $alpha,
                'total_days' => $totalDays,
                'percentage' => $percentage,
                'high_alpha_alert' => $highAlphaAlert,
            ];
        }

        $totalRecordsOverall = count($attendances);
        $studentsWithData = array_filter($reportData, fn($r) => $r['total_days'] > 0);
        $avgPercentage = count($studentsWithData) > 0 
            ? round(array_sum(array_column($studentsWithData, 'percentage')) / count($studentsWithData), 1)
            : 0.0;

        $classesList = ClassRoom::with('homeroomTeacher:id,full_name,nip')->orderBy('name')->get(['id', 'name', 'grade_level', 'homeroom_teacher_id']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'period' => [
                        'from' => $from,
                        'to' => $to,
                        'month' => $month,
                    ],
                    'total_students' => count($reportData),
                    'total_attendance_logs' => $totalRecordsOverall,
                    'average_percentage' => $avgPercentage,
                    'total_present' => $totalPresentSum,
                    'total_sick' => $totalSickSum,
                    'total_permission' => $totalPermissionSum,
                    'total_alpha' => $totalAlphaSum,
                    'high_alpha_students_count' => $highAlphaCount,
                ],
                'students' => $reportData,
                'classes' => $classesList,
            ]
        ]);
    }

    public function monitoring(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $search = $request->input('search');
        $todayStr = now()->toDateString();

        $from = $month . '-01';
        $to = date('Y-m-t', strtotime($from));

        // Fetch teachers
        $teacherQuery = \App\Models\Teacher::with(['subjects', 'user']);
        if ($search) {
            $teacherQuery->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }
        $teachers = $teacherQuery->orderBy('id', 'asc')->get();

        // Fetch schedules mapping (class_id + subject_id => teacher_id)
        $schedules = \App\Models\Schedule::whereNotNull('teacher_id')
            ->get(['class_id', 'subject_id', 'teacher_id']);

        $scheduleMap = [];
        foreach ($schedules as $sch) {
            $scheduleMap[$sch->class_id . '_' . $sch->subject_id] = $sch->teacher_id;
        }

        // Fetch all classes homeroom mapping (class_id => homeroom_teacher_id)
        $homeroomMap = ClassRoom::pluck('homeroom_teacher_id', 'id')->toArray();

        // Fetch teacher-subject assignments mapping (subject_id => teacher_id)
        $teacherSubjectMap = Schema::hasTable('subject_teacher')
            ? DB::table('subject_teacher')->pluck('teacher_id', 'subject_id')->toArray()
            : [];

        // Fetch attendance logs in month
        $attendances = Attendance::with(['classRoom', 'subject'])
            ->whereBetween('date', [$from, $to])
            ->get();

        // Group attendances by (class_id + '_' + (subject_id ?: 0) + '_' + date)
        $sessions = [];
        foreach ($attendances as $att) {
            $dateStr = $att->date ? $att->date->format('Y-m-d') : '';
            $key = $att->class_id . '_' . ($att->subject_id ?: 0) . '_' . $dateStr;

            if (!isset($sessions[$key])) {
                // 1. Direct recorded teacher_id
                $teacherId = $att->teacher_id;

                // 2. Fallback: Schedule map
                if (!$teacherId && $att->subject_id && isset($scheduleMap[$att->class_id . '_' . $att->subject_id])) {
                    $teacherId = $scheduleMap[$att->class_id . '_' . $att->subject_id];
                }

                // 3. Fallback: Teacher assigned to subject
                if (!$teacherId && $att->subject_id && isset($teacherSubjectMap[$att->subject_id])) {
                    $teacherId = $teacherSubjectMap[$att->subject_id];
                }

                // 4. Fallback: Homeroom teacher of class
                if (!$teacherId && isset($homeroomMap[$att->class_id])) {
                    $teacherId = $homeroomMap[$att->class_id];
                }

                // 5. Ultimate fallback: Pick first available teacher if unassigned
                if (!$teacherId && count($teachers) > 0) {
                    $teacherId = $teachers->first()->id;
                }

                $sessions[$key] = [
                    'class_id' => $att->class_id,
                    'class_name' => $att->classRoom?->name ?? 'Kelas',
                    'subject_id' => $att->subject_id,
                    'subject_name' => $att->subject?->name ?? 'Presensi Umum',
                    'date' => $dateStr,
                    'teacher_id' => $teacherId,
                    'present' => 0,
                    'sick' => 0,
                    'permission' => 0,
                    'alpha' => 0,
                    'total_students' => 0,
                ];
            }

            $sessions[$key]['total_students']++;
            if ($att->status === 'present') $sessions[$key]['present']++;
            else if ($att->status === 'sick') $sessions[$key]['sick']++;
            else if ($att->status === 'permission') $sessions[$key]['permission']++;
            else if ($att->status === 'alpha') $sessions[$key]['alpha']++;
        }

        // Group sessions by teacher_id
        $sessionsByTeacher = [];
        foreach ($sessions as $s) {
            $tId = $s['teacher_id'];
            if ($tId) {
                $sessionsByTeacher[$tId][] = $s;
            }
        }

        $teachersMonitoringData = [];
        $totalSessionsCount = count($sessions);
        $submittedTodayTeacherCount = 0;

        foreach ($teachers as $t) {
            $teacherSessions = $sessionsByTeacher[$t->id] ?? [];
            $hasSubmittedToday = false;

            foreach ($teacherSessions as $ts) {
                if ($ts['date'] === $todayStr) {
                    $hasSubmittedToday = true;
                    break;
                }
            }

            if ($hasSubmittedToday) {
                $submittedTodayTeacherCount++;
            }

            $teachersMonitoringData[] = [
                'teacher_id' => $t->id,
                'full_name' => $t->full_name,
                'nip' => $t->nip,
                'gender' => $t->gender,
                'phone' => $t->phone,
                'photo_url' => $t->photo_url ?: $t->user?->photo_url,
                'subjects' => $t->subjects->pluck('name')->toArray(),
                'submitted_today' => $hasSubmittedToday,
                'total_sessions' => count($teacherSessions),
                'journal_entries' => array_values($teacherSessions),
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => [
                    'month' => $month,
                    'total_teachers' => count($teachers),
                    'submitted_today_count' => $submittedTodayTeacherCount,
                    'total_sessions_month' => $totalSessionsCount,
                ],
                'teachers' => $teachersMonitoringData,
            ]
        ]);
    }
}
