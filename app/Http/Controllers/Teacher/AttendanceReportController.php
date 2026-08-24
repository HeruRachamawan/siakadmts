<?php

namespace App\Http\Controllers\Teacher;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceReportController extends TeacherController
{
    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        // Fetch classes where this teacher is homeroom teacher
        $homeroomClasses = ClassRoom::where('homeroom_teacher_id', $teacher->id)->get();
        $homeroomClassIds = $homeroomClasses->pluck('id')->toArray();

        $classId = $request->input('class_id');
        if ($classId && !in_array($classId, $homeroomClassIds)) {
            $classId = null; // invalid for this teacher
        }

        $targetClassIds = $classId ? [$classId] : $homeroomClassIds;

        $month = $request->input('month', now()->format('Y-m'));
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
        $query = Student::whereIn('class_id', $targetClassIds)->with(['classRoom.academicYear']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('full_name')->get();

        // Fetch attendance records in range
        $attendances = Attendance::whereIn('class_id', $targetClassIds)
            ->whereBetween('date', [$from, $to])
            ->get();

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
                'classes' => $homeroomClasses,
            ]
        ]);
    }
}
