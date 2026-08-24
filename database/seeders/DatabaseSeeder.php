<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Sekolah',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $teacherUser = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Guru Matematika',
                'username' => '1987654321', // NUPTK/NIP
                'password' => Hash::make('1987654321'),
                'role' => 'teacher',
            ]
        );

        $teacher = Teacher::firstOrCreate(['user_id' => $teacherUser->id], [
            'nip' => '1987654321',
            'full_name' => 'Guru Matematika',
            'gender' => 'L',
            'phone' => '081234567890',
        ]);

        $studentUser = User::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Budi Santoso',
                'username' => '0000000001', // NISN
                'password' => Hash::make('0000000001'),
                'role' => 'student',
            ]
        );

        $student = Student::firstOrCreate(['user_id' => $studentUser->id], [
            'nisn' => '0000000001',
            'nis' => '00000001',
            'full_name' => 'Budi Santoso',
            'gender' => 'L',
            'birth_place' => 'Jakarta',
            'birth_date' => '2008-01-15',
            'address' => 'Jl. Merdeka No. 1',
            'parent_phone' => '081987654321',
            'mother_name' => 'Siti Aminah',
            'mother_status' => 'hidup',
            'mother_nik' => '3201010101010001',
            'mother_job' => 'Ibu Rumah Tangga',
            'mother_income' => '3.000.000',
            'father_name' => 'Sukardi',
            'father_status' => 'hidup',
            'father_nik' => '3201010101010002',
            'father_job' => 'Supir',
            'father_income' => '4.500.000',
            'previous_school' => 'SDN 01 Jakarta',
        ]);

        $year = AcademicYear::firstOrCreate(['year' => '2025/2026'], [
            'semester' => 'odd',
            'is_active' => true,
        ]);

        $class = ClassRoom::firstOrCreate(
            ['name' => '10-A', 'academic_year_id' => $year->id],
            [
                'homeroom_teacher_id' => $teacher->id,
                'grade_level' => '10',
            ]
        );

        if (! $student->class_id) {
            $student->update(['class_id' => $class->id]);
        }

        $math = Subject::firstOrCreate(['code' => 'MAT001'], ['name' => 'Matematika', 'description' => 'Mata pelajaran Matematika']);
        $english = Subject::firstOrCreate(['code' => 'ENG001'], ['name' => 'Bahasa Inggris', 'description' => 'Mata pelajaran Bahasa Inggris']);

        Grade::updateOrCreate(
            ['student_id' => $student->id, 'subject_id' => $math->id, 'academic_year_id' => $year->id],
            ['score_assignment' => 80, 'score_uts' => 85, 'score_uas' => 90]
        );
        Grade::updateOrCreate(
            ['student_id' => $student->id, 'subject_id' => $english->id, 'academic_year_id' => $year->id],
            ['score_assignment' => 70, 'score_uts' => 75, 'score_uas' => 80]
        );

        Attendance::firstOrCreate(
            ['student_id' => $student->id, 'class_id' => $class->id, 'date' => now()->toDateString()],
            ['status' => 'present']
        );

        $this->call([
            FacilitySeeder::class,
            NewsSeeder::class,
        ]);
    }
}
