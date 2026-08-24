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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::firstOrCreate(['year' => '2025/2026'], [
            'semester' => 'odd',
            'is_active' => true,
        ]);

        // 1. Create Subjects
        $subjectData = [
            ['code' => 'MAT01', 'name' => 'Matematika', 'description' => 'Mata pelajaran Matematika Wajib'],
            ['code' => 'BIN01', 'name' => 'Bahasa Indonesia', 'description' => 'Mata pelajaran Bahasa Indonesia'],
            ['code' => 'BIG01', 'name' => 'Bahasa Inggris', 'description' => 'Mata pelajaran Bahasa Inggris'],
            ['code' => 'FIS01', 'name' => 'Fisika', 'description' => 'Mata pelajaran Fisika IPA'],
            ['code' => 'BIO01', 'name' => 'Biologi', 'description' => 'Mata pelajaran Biologi IPA'],
            ['code' => 'KIM01', 'name' => 'Kimia', 'description' => 'Mata pelajaran Kimia IPA'],
            ['code' => 'INF01', 'name' => 'Informatika', 'description' => 'Mata pelajaran Pemrograman & Teknologi'],
            ['code' => 'SEJ01', 'name' => 'Sejarah', 'description' => 'Mata pelajaran Sejarah Indonesia'],
            ['code' => 'PJK01', 'name' => 'Penjaskes', 'description' => 'Mata pelajaran Olahraga & Kesehatan'],
            ['code' => 'PAI01', 'name' => 'Pendidikan Agama Islam', 'description' => 'Mata pelajaran Agama & Budi Pekerti'],
        ];

        $subjects = [];
        foreach ($subjectData as $sd) {
            $subjects[$sd['code']] = Subject::firstOrCreate(['code' => $sd['code']], $sd);
        }

        // 2. Create 10 Teachers
        $teachersData = [
            [
                'nip' => '197501012000031001',
                'full_name' => 'Drs. Ahmad Hidayat, M.Pd',
                'gender' => 'L',
                'phone' => '081234567801',
                'email' => 'ahmad.hidayat@sekolah.sch.id',
                'username' => '197501012000031001',
                'subject_code' => 'MAT01',
            ],
            [
                'nip' => '198203152006042002',
                'full_name' => 'Siti Rahmawati, S.Pd',
                'gender' => 'P',
                'phone' => '081234567802',
                'email' => 'siti.rahmawati@sekolah.sch.id',
                'username' => '198203152006042002',
                'subject_code' => 'BIN01',
            ],
            [
                'nip' => '198507202010011003',
                'full_name' => 'Bambang Pratama, S.Si',
                'gender' => 'L',
                'phone' => '081234567803',
                'email' => 'bambang.pratama@sekolah.sch.id',
                'username' => '198507202010011003',
                'subject_code' => 'FIS01',
            ],
            [
                'nip' => '198811122014022004',
                'full_name' => 'Dewi Lestari, M.Hum',
                'gender' => 'P',
                'phone' => '081234567804',
                'email' => 'dewi.lestari@sekolah.sch.id',
                'username' => '198811122014022004',
                'subject_code' => 'BIG01',
            ],
            [
                'nip' => '199004052018011005',
                'full_name' => 'Eko Prasetyo, S.Kom',
                'gender' => 'L',
                'phone' => '081234567805',
                'email' => 'eko.prasetyo@sekolah.sch.id',
                'username' => '199004052018011005',
                'subject_code' => 'INF01',
            ],
            [
                'nip' => '199209182019032006',
                'full_name' => 'Fitri Handayani, S.Pd',
                'gender' => 'P',
                'phone' => '081234567806',
                'email' => 'fitri.handayani@sekolah.sch.id',
                'username' => '199209182019032006',
                'subject_code' => 'BIO01',
            ],
            [
                'nip' => '199402282020121007',
                'full_name' => 'Hendra Gunawan, S.Pd',
                'gender' => 'L',
                'phone' => '081234567807',
                'email' => 'hendra.gunawan@sekolah.sch.id',
                'username' => '199402282020121007',
                'subject_code' => 'KIM01',
            ],
            [
                'nip' => '199506142022032008',
                'full_name' => 'Indah Kusuma, S.Pd',
                'gender' => 'P',
                'phone' => '081234567808',
                'email' => 'indah.kusuma@sekolah.sch.id',
                'username' => '199506142022032008',
                'subject_code' => 'SEJ01',
            ],
            [
                'nip' => '199308082021011009',
                'full_name' => 'Joko Susilo, S.Or',
                'gender' => 'L',
                'phone' => '081234567809',
                'email' => 'joko.susilo@sekolah.sch.id',
                'username' => '199308082021011009',
                'subject_code' => 'PJK01',
            ],
            [
                'nip' => '198910252015042010',
                'full_name' => 'Kartika Sari, M.Ag',
                'gender' => 'P',
                'phone' => '081234567810',
                'email' => 'kartika.sari@sekolah.sch.id',
                'username' => '198910252015042010',
                'subject_code' => 'PAI01',
            ],
        ];

        $createdTeachers = [];

        foreach ($teachersData as $td) {
            $user = User::firstOrCreate(
                ['username' => $td['username']],
                [
                    'name' => $td['full_name'],
                    'email' => $td['email'],
                    'password' => Hash::make($td['nip']), // password defaults to NIP
                    'role' => 'teacher',
                ]
            );

            $teacher = Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $td['nip'],
                    'full_name' => $td['full_name'],
                    'gender' => $td['gender'],
                    'phone' => $td['phone'],
                ]
            );

            if (isset($subjects[$td['subject_code']])) {
                $teacher->subjects()->syncWithoutDetaching([$subjects[$td['subject_code']]->id]);
            }

            $createdTeachers[] = $teacher;
        }

        // 3. Create Classes with Homeroom Teachers
        $classesData = [
            ['name' => '10-A', 'grade_level' => '10', 'homeroom_teacher' => $createdTeachers[0]], // Drs. Ahmad Hidayat
            ['name' => '10-B', 'grade_level' => '10', 'homeroom_teacher' => $createdTeachers[1]], // Siti Rahmawati
            ['name' => '11-A', 'grade_level' => '11', 'homeroom_teacher' => $createdTeachers[2]], // Bambang Pratama
            ['name' => '11-B', 'grade_level' => '11', 'homeroom_teacher' => $createdTeachers[3]], // Dewi Lestari
        ];

        $createdClasses = [];
        foreach ($classesData as $cd) {
            $class = ClassRoom::firstOrCreate(
                ['name' => $cd['name'], 'academic_year_id' => $year->id],
                [
                    'grade_level' => $cd['grade_level'],
                    'homeroom_teacher_id' => $cd['homeroom_teacher']->id,
                ]
            );
            $createdClasses[$cd['name']] = $class;
        }

        // 4. Create 10 Students
        $studentsData = [
            [
                'nisn' => '0061234001',
                'nis' => '20261001',
                'full_name' => 'Achmad Fauzi',
                'gender' => 'L',
                'class_name' => '10-A',
                'birth_place' => 'Jakarta',
                'birth_date' => '2009-03-12',
                'address' => 'Jl. Kebon Jeruk No. 12, Jakarta',
                'parent_phone' => '081299887701',
                'mother_name' => 'Siti Khadijah',
                'father_name' => 'Rahmat Hidayat',
            ],
            [
                'nisn' => '0061234002',
                'nis' => '20261002',
                'full_name' => 'Annisa Maharani',
                'gender' => 'P',
                'class_name' => '10-A',
                'birth_place' => 'Bandung',
                'birth_date' => '2009-05-20',
                'address' => 'Jl. Setiabudi No. 45, Bandung',
                'parent_phone' => '081299887702',
                'mother_name' => 'Dewi Safitri',
                'father_name' => 'Budi Santoso',
            ],
            [
                'nisn' => '0061234003',
                'nis' => '20261003',
                'full_name' => 'Bayu Wijaya',
                'gender' => 'L',
                'class_name' => '10-A',
                'birth_place' => 'Surabaya',
                'birth_date' => '2009-07-15',
                'address' => 'Jl. Pemuda No. 88, Surabaya',
                'parent_phone' => '081299887703',
                'mother_name' => 'Sri Wahyuni',
                'father_name' => 'Agus Wijaya',
            ],
            [
                'nisn' => '0061234004',
                'nis' => '20261004',
                'full_name' => 'Citra Kirana',
                'gender' => 'P',
                'class_name' => '10-B',
                'birth_place' => 'Yogyakarta',
                'birth_date' => '2009-02-10',
                'address' => 'Jl. Malioboro No. 23, Yogyakarta',
                'parent_phone' => '081299887704',
                'mother_name' => 'Endang Lestari',
                'father_name' => 'Hendra Kirana',
            ],
            [
                'nisn' => '0061234005',
                'nis' => '20261005',
                'full_name' => 'Dimas Anggara',
                'gender' => 'L',
                'class_name' => '10-B',
                'birth_place' => 'Semarang',
                'birth_date' => '2009-09-01',
                'address' => 'Jl. Pandanaran No. 10, Semarang',
                'parent_phone' => '081299887705',
                'mother_name' => 'Rina Astuti',
                'father_name' => 'Surya Anggara',
            ],
            [
                'nisn' => '0061234006',
                'nis' => '20261006',
                'full_name' => 'Eva Rosdiana',
                'gender' => 'P',
                'class_name' => '10-B',
                'birth_place' => 'Malang',
                'birth_date' => '2009-11-25',
                'address' => 'Jl. Ijen No. 56, Malang',
                'parent_phone' => '081299887706',
                'mother_name' => 'Nurul Hidayati',
                'father_name' => 'Dedi Rosadi',
            ],
            [
                'nisn' => '0061234007',
                'nis' => '20261101',
                'full_name' => 'Fajar Ramadhan',
                'gender' => 'L',
                'class_name' => '11-A',
                'birth_place' => 'Medan',
                'birth_date' => '2008-04-18',
                'address' => 'Jl. Gajah Mada No. 77, Medan',
                'parent_phone' => '081299887707',
                'mother_name' => 'Tiarma Siregar',
                'father_name' => 'Zulkarnain Ramadhan',
            ],
            [
                'nisn' => '0061234008',
                'nis' => '20261102',
                'full_name' => 'Gita Gutawa',
                'gender' => 'P',
                'class_name' => '11-A',
                'birth_place' => 'Palembang',
                'birth_date' => '2008-08-11',
                'address' => 'Jl. Ampera No. 34, Palembang',
                'parent_phone' => '081299887708',
                'mother_name' => 'Marlina',
                'father_name' => 'Erwin Gutawa',
            ],
            [
                'nisn' => '0061234009',
                'nis' => '20261103',
                'full_name' => 'Hadi Syahputra',
                'gender' => 'L',
                'class_name' => '11-B',
                'birth_place' => 'Makassar',
                'birth_date' => '2008-10-30',
                'address' => 'Jl. Losari No. 19, Makassar',
                'parent_phone' => '081299887709',
                'mother_name' => 'Hasnah',
                'father_name' => 'Syamsuddin',
            ],
            [
                'nisn' => '0061234010',
                'nis' => '20261104',
                'full_name' => 'Intan Nuraini',
                'gender' => 'P',
                'class_name' => '11-B',
                'birth_place' => 'Denpasar',
                'birth_date' => '2008-12-05',
                'address' => 'Jl. Diponegoro No. 89, Denpasar',
                'parent_phone' => '081299887710',
                'mother_name' => 'Ni Wayan Sari',
                'father_name' => 'I Made Nurjaya',
            ],
        ];

        $createdStudents = [];

        foreach ($studentsData as $sd) {
            $user = User::firstOrCreate(
                ['username' => $sd['nisn']],
                [
                    'name' => $sd['full_name'],
                    'email' => $sd['nisn'] . '@siswa.sekolah.sch.id',
                    'password' => Hash::make($sd['nis']), // password defaults to NIS
                    'role' => 'student',
                ]
            );

            $class = $createdClasses[$sd['class_name']] ?? null;

            $student = Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nisn' => $sd['nisn'],
                    'nis' => $sd['nis'],
                    'full_name' => $sd['full_name'],
                    'gender' => $sd['gender'],
                    'class_id' => $class?->id,
                    'birth_place' => $sd['birth_place'],
                    'birth_date' => $sd['birth_date'],
                    'address' => $sd['address'],
                    'parent_phone' => $sd['parent_phone'],
                    'mother_name' => $sd['mother_name'],
                    'mother_status' => 'hidup',
                    'mother_job' => 'Ibu Rumah Tangga',
                    'mother_income' => '3.500.000',
                    'father_name' => 'Bapak ' . $sd['full_name'],
                    'father_status' => 'hidup',
                    'father_job' => 'Wiraswasta',
                    'father_income' => '4.800.000',
                    'previous_school' => 'SMP Negeri 1',
                ]
            );

            $createdStudents[] = $student;
        }

        // 5. Seed Attendance Sample Data
        $statuses = ['present', 'present', 'present', 'present', 'sick', 'permission', 'alpha'];
        $dates = [
            now()->subDays(5)->toDateString(),
            now()->subDays(4)->toDateString(),
            now()->subDays(3)->toDateString(),
            now()->subDays(2)->toDateString(),
            now()->subDays(1)->toDateString(),
            now()->toDateString(),
        ];

        foreach ($createdStudents as $idx => $st) {
            foreach ($dates as $dIdx => $d) {
                // Vary status slightly for realistic analytics
                $status = $statuses[($idx + $dIdx) % count($statuses)];
                
                Attendance::firstOrCreate(
                    [
                        'student_id' => $st->id,
                        'class_id' => $st->class_id,
                        'date' => $d,
                    ],
                    [
                        'status' => $status,
                        'note' => $status === 'sick' ? 'Demam' : ($status === 'permission' ? 'Izin Keluarga' : null),
                    ]
                );
            }
        }
    }
}
