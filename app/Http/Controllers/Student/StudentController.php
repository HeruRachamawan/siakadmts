<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Api\BaseController;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends BaseController
{
    protected function resolveStudent(Request $request): Student
    {
        $student = $request->user()->student;

        if (! $student) {
            abort(403, 'Akun Anda bukan siswa.');
        }

        return $student;
    }
}
