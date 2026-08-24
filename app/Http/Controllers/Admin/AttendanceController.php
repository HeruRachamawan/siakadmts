<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends BaseController
{
    public function index(Request $request)
    {
        $query = Attendance::with(['student', 'classRoom.academicYear']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        $attendances = $query->orderBy('date', 'desc')
            ->paginate($request->get('per_page', 15));

        return $this->success($this->paginate($attendances));
    }

    public function show(Attendance $attendance)
    {
        return $this->success($attendance->load(['student', 'classRoom.academicYear']));
    }
}
