<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;

class AttendanceController extends StudentController
{
    public function index(Request $request)
    {
        $student = $this->resolveStudent($request);

        $summary = $student->attendances()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $query = $student->attendances()->orderBy('date', 'desc');

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        $list = $query->paginate($request->get('per_page', 15));

        return $this->success([
            'summary' => $summary,
            'attendance' => $list->items(),
            'meta' => [
                'current_page' => $list->currentPage(),
                'last_page' => $list->lastPage(),
                'per_page' => $list->perPage(),
                'total' => $list->total(),
            ],
        ]);
    }
}
