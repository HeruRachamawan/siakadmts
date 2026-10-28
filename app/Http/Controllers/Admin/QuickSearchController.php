<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuickSearchController extends Controller
{
    /**
     * Search students and teachers quickly for Command Palette (Ctrl+K).
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim(strip_tags((string) $request->input('q', '')));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'students' => [],
                    'teachers' => [],
                ],
            ]);
        }

        // 1. Search Students (Max 6)
        $students = Student::with('classRoom:id,name,grade_level')
            ->where(function ($q) use ($query) {
                $q->where('full_name', 'like', "%{$query}%")
                    ->orWhere('nisn', 'like', "%{$query}%")
                    ->orWhere('nis', 'like', "%{$query}%");
            })
            ->limit(6)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'title' => $s->full_name,
                    'subtitle' => 'NISN: ' . ($s->nisn ?: '-') . ' • Kelas ' . ($s->classRoom?->name ?? '-'),
                    'class_name' => $s->classRoom?->name ?? '-',
                    'nisn' => $s->nisn ?: '-',
                    'gender' => $s->gender,
                    'status' => $s->status,
                    'photo_url' => $s->photo_url,
                    'type' => 'student',
                    'route' => '/admin/students',
                ];
            });

        // 2. Search Teachers (Max 6)
        $teachers = Teacher::with('subjects:id,name,code')
            ->where(function ($q) use ($query) {
                $q->where('full_name', 'like', "%{$query}%")
                    ->orWhere('nip', 'like', "%{$query}%")
                    ->orWhere('position', 'like', "%{$query}%");
            })
            ->limit(6)
            ->get()
            ->map(function ($t) {
                $subjects = $t->subjects->pluck('name')->filter()->join(', ');
                $sub = ($t->position ?: 'Guru');
                if (!empty($subjects)) {
                    $sub .= ' (' . $subjects . ')';
                }

                return [
                    'id' => $t->id,
                    'title' => $t->full_name,
                    'subtitle' => $sub . ' • NIP: ' . ($t->nip ?: '-'),
                    'position' => $t->position ?: 'Guru',
                    'gender' => $t->gender,
                    'photo_url' => $t->photo_url,
                    'type' => 'teacher',
                    'route' => '/admin/teachers',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'students' => $students,
                'teachers' => $teachers,
            ],
        ]);
    }
}
