<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\Request;

class DailyAttendanceMonitoringController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $search = $request->input('search');

        // Fetch all classes with homeroom teacher & students
        $classes = ClassRoom::with(['homeroomTeacher.user', 'students'])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('homeroomTeacher', function ($qt) use ($search) {
                      $qt->where('full_name', 'like', "%{$search}%")
                         ->orWhere('nip', 'like', "%{$search}%");
                  });
            })
            ->orderBy('name', 'asc')
            ->get();

        // Fetch all daily attendances for this date (where subject_id is null)
        $attendances = Attendance::where('date', $date)
            ->whereNull('subject_id')
            ->get()
            ->groupBy('class_id');

        $classesData = [];
        $totalSchoolStudents = 0;
        $totalSchoolPresent = 0;
        $totalSchoolSick = 0;
        $totalSchoolPermission = 0;
        $totalSchoolAlpha = 0;
        $submittedClassesCount = 0;

        foreach ($classes as $class) {
            $classStudents = $class->students;
            $classStudentsCount = $classStudents->count();
            $totalSchoolStudents += $classStudentsCount;

            $classAttendances = $attendances->get($class->id, collect());
            $attendanceByStudent = $classAttendances->keyBy('student_id');
            $isSubmitted = $classAttendances->isNotEmpty();

            if ($isSubmitted) {
                $submittedClassesCount++;
            }

            $present = 0;
            $sick = 0;
            $permission = 0;
            $alpha = 0;

            $studentsDetail = [];
            foreach ($classStudents as $student) {
                $att = $attendanceByStudent->get($student->id);
                $status = $att ? $att->status : ($isSubmitted ? 'unrecorded' : 'unrecorded');
                $note = $att ? $att->note : '';

                if ($att) {
                    if ($att->status === 'present') $present++;
                    elseif ($att->status === 'sick') $sick++;
                    elseif ($att->status === 'permission') $permission++;
                    elseif ($att->status === 'alpha') $alpha++;
                }

                $studentsDetail[] = [
                    'student_id' => $student->id,
                    'full_name' => $student->full_name,
                    'nisn' => $student->nisn,
                    'nis' => $student->nis,
                    'gender' => $student->gender,
                    'photo_url' => $student->photo_url,
                    'status' => $status,
                    'note' => $note,
                ];
            }

            $totalRecorded = $present + $sick + $permission + $alpha;
            $percentage = $totalRecorded > 0 ? round(($present / $totalRecorded) * 100, 1) : 0.0;

            $totalSchoolPresent += $present;
            $totalSchoolSick += $sick;
            $totalSchoolPermission += $permission;
            $totalSchoolAlpha += $alpha;

            $latestRecord = $classAttendances->sortByDesc('updated_at')->first();
            $submissionTime = $latestRecord && $latestRecord->updated_at 
                ? $latestRecord->updated_at->format('H:i') 
                : null;

            $classesData[] = [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'grade_level' => $class->grade_level,
                'homeroom_teacher_name' => $class->homeroomTeacher?->full_name ?? 'Belum Ditentukan',
                'homeroom_teacher_nip' => $class->homeroomTeacher?->nip ?? '-',
                'homeroom_teacher_phone' => $class->homeroomTeacher?->phone ?? null,
                'homeroom_teacher_photo' => $class->homeroomTeacher?->photo_url ?? null,
                'is_submitted' => $isSubmitted,
                'submission_time' => $submissionTime,
                'students_count' => $classStudentsCount,
                'present' => $present,
                'sick' => $sick,
                'permission' => $permission,
                'alpha' => $alpha,
                'percentage' => $percentage,
                'students' => $studentsDetail,
            ];
        }

        $totalRecordedOverall = $totalSchoolPresent + $totalSchoolSick + $totalSchoolPermission + $totalSchoolAlpha;
        $overallPercentage = $totalRecordedOverall > 0 
            ? round(($totalSchoolPresent / $totalRecordedOverall) * 100, 1) 
            : 0.0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'date' => $date,
                'summary' => [
                    'total_classes' => count($classes),
                    'submitted_classes_count' => $submittedClassesCount,
                    'unsubmitted_classes_count' => count($classes) - $submittedClassesCount,
                    'total_students_school' => $totalSchoolStudents,
                    'total_present' => $totalSchoolPresent,
                    'total_sick' => $totalSchoolSick,
                    'total_permission' => $totalSchoolPermission,
                    'total_alpha' => $totalSchoolAlpha,
                    'overall_percentage' => $overallPercentage,
                ],
                'classes' => $classesData,
            ]
        ]);
    }
}
