<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\AcademicYear;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelImportExportController extends Controller
{
    /**
     * Download Excel Template (.xlsx) for Students, Teachers, or Grades.
     * All columns exactly synchronized with the system Form Input fields.
     */
    public function downloadTemplate($type)
    {
        if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            if ($type === 'students') {
                $sheet->setTitle('Template Siswa');
                $headers = [
                    'NISN (10 Digit)*',
                    'NIS*',
                    'NIK Siswa (16 Digit)*',
                    'Nama Lengkap*',
                    'Gender (L/P)*',
                    'Kelas*',
                    'Tempat Lahir*',
                    'Tanggal Lahir (YYYY-MM-DD)*',
                    'Sekolah Asal',
                    'Alamat Lengkap*',
                    'No HP / WA Ortu*',
                    'Nama Ayah',
                    'Status Ayah (hidup/meninggal/tidak_diketahui)',
                    'NIK Ayah',
                    'Pekerjaan Ayah',
                    'Penghasilan Ayah',
                    'Nama Ibu',
                    'Status Ibu (hidup/meninggal/tidak_diketahui)',
                    'NIK Ibu',
                    'Pekerjaan Ibu',
                    'Penghasilan Ibu',
                    'Nama Wali',
                    'Hubungan Wali',
                    'NIK Wali',
                    'Pekerjaan Wali',
                    'No HP Wali',
                    'Penghasilan Wali',
                ];
                $sheet->fromArray([$headers], null, 'A1');

                $example = [
                    [
                        '0051234567', '2026001', '3201011505100001', 'Ahmad Rizky Pratama', 'L', '7A',
                        'Bandung', '2010-05-15', 'SDN 01 Ciwidey', 'Jl. Raya Ciwidey No. 10 RT 01/02', '081234567890',
                        'Sukardi', 'hidup', '3201010101750001', 'Wiraswasta', '2.500.001 - 3.500.000',
                        'Siti Aminah', 'hidup', '3201010101800002', 'Ibu Rumah Tangga', 'dibawah 800.000',
                        '', '', '', '', '', ''
                    ],
                    [
                        '0059876543', '2026002', '3201012008100002', 'Siti Nurhaliza Putri', 'P', '7B',
                        'Bandung', '2010-08-20', 'MI Al-Hasanah', 'Jl. Sukajadi No. 45', '081987654321',
                        'Budi Santoso', 'hidup', '3201010101720003', 'PNS', '4.800.001 - 6.500.000',
                        'Rina Wati', 'hidup', '3201010101780004', 'Guru/Dosen', '3.500.001 - 4.800.000',
                        '', '', '', '', '', ''
                    ],
                ];
                $sheet->fromArray($example, null, 'A2');

            } elseif ($type === 'teachers') {
                $sheet->setTitle('Template Guru');
                $headers = [
                    'NIP / NUPTK*',
                    'Nama Lengkap & Gelar*',
                    'Gender (L/P)*',
                    'No. WhatsApp / Telepon*',
                    'Email',
                    'Jabatan / Posisi',
                    'Mata Pelajaran yang Diampu (Pisahkan Koma)',
                ];
                $sheet->fromArray([$headers], null, 'A1');

                $example = [
                    ['198501012010011001', 'Budi Santoso, S.Pd', 'L', '08122334455', 'budi@mtsalhasanah.sch.id', 'Guru Pengajar', 'Matematika, IPA'],
                    ['199002022015022002', 'Hesti Fatimah, S.Pd', 'P', '08166778899', 'hesti@mtsalhasanah.sch.id', 'Wali Kelas 7A', 'Bahasa Indonesia'],
                ];
                $sheet->fromArray($example, null, 'A2');

            } elseif ($type === 'grades') {
                $sheet->setTitle('Template Nilai');
                $headers = [
                    'NISN*',
                    'Nama Siswa',
                    'Nama/Kode Mapel*',
                    'Nilai Tugas (0-100)*',
                    'Nilai UTS (0-100)*',
                    'Nilai UAS (0-100)*',
                ];
                $sheet->fromArray([$headers], null, 'A1');

                $example = [
                    ['0051234567', 'Ahmad Rizky Pratama', 'Matematika', 85, 80, 90],
                    ['0059876543', 'Siti Nurhaliza Putri', 'Matematika', 90, 88, 92],
                ];
                $sheet->fromArray($example, null, 'A2');
            } else {
                return response()->json(['message' => 'Tipe template tidak valid'], 400);
            }

            $lastCol = $sheet->getHighestColumn();
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            foreach ($sheet->getColumnIterator() as $column) {
                $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = "Template_Import_{$type}_YASPIN.xlsx";

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
                'Pragma' => 'public',
            ]);
        }

        // CSV Fallback
        $fileName = "Template_Import_{$type}_YASPIN.csv";
        return response()->streamDownload(function () use ($type) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");

            if ($type === 'students') {
                fputcsv($output, ['NISN (10 Digit)*', 'NIS*', 'NIK Siswa (16 Digit)*', 'Nama Lengkap*', 'Gender (L/P)*', 'Kelas*', 'Tempat Lahir*', 'Tanggal Lahir (YYYY-MM-DD)*', 'Sekolah Asal', 'Alamat Lengkap*', 'No HP / WA Ortu*', 'Nama Ayah', 'Status Ayah', 'NIK Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah', 'Nama Ibu', 'Status Ibu', 'NIK Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu', 'Nama Wali', 'Hubungan Wali', 'NIK Wali', 'Pekerjaan Wali', 'No HP Wali', 'Penghasilan Wali']);
                fputcsv($output, ['0051234567', '2026001', '3201011505100001', 'Ahmad Rizky Pratama', 'L', '7A', 'Bandung', '2010-05-15', 'SDN 01 Ciwidey', 'Jl. Raya Ciwidey No. 10', '081234567890', 'Sukardi', 'hidup', '3201010101750001', 'Wiraswasta', '2.500.001 - 3.500.000', 'Siti Aminah', 'hidup', '3201010101800002', 'Ibu Rumah Tangga', 'dibawah 800.000', '', '', '', '', '', '']);
            } elseif ($type === 'teachers') {
                fputcsv($output, ['NIP / NUPTK*', 'Nama Lengkap & Gelar*', 'Gender (L/P)*', 'No. WhatsApp / Telepon*', 'Email', 'Jabatan / Posisi', 'Mata Pelajaran yang Diampu (Pisahkan Koma)']);
                fputcsv($output, ['198501012010011001', 'Budi Santoso, S.Pd', 'L', '08122334455', 'budi@mtsalhasanah.sch.id', 'Guru Pengajar', 'Matematika, IPA']);
            } elseif ($type === 'grades') {
                fputcsv($output, ['NISN*', 'Nama Siswa', 'Nama/Kode Mapel*', 'Nilai Tugas (0-100)*', 'Nilai UTS (0-100)*', 'Nilai UAS (0-100)*']);
                fputcsv($output, ['0051234567', 'Ahmad Rizky Pratama', 'Matematika', '85', '80', '90']);
            }
            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
        ]);
    }

    /**
     * Preview uploaded Excel file with validation matching form schemas.
     */
    public function previewImport(Request $request, $type)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) <= 1) {
            return response()->json(['message' => 'File Excel kosong atau tidak memiliki data'], 422);
        }

        $dataRows = array_slice($rows, 1);
        $parsed = [];

        foreach ($dataRows as $index => $row) {
            if (empty(array_filter($row))) continue; // Skip empty rows

            $rowNum = $index + 2;
            $isValid = true;
            $errors = [];

            if ($type === 'students') {
                $nisn = trim((string)($row[0] ?? ''));
                $nis = trim((string)($row[1] ?? ''));
                $nik = trim((string)($row[2] ?? ''));
                $fullName = trim((string)($row[3] ?? ''));
                $gender = strtoupper(trim((string)($row[4] ?? '')));
                $className = trim((string)($row[5] ?? ''));
                $birthPlace = trim((string)($row[6] ?? ''));
                $birthDate = trim((string)($row[7] ?? ''));
                $previousSchool = trim((string)($row[8] ?? ''));
                $address = trim((string)($row[9] ?? ''));
                $parentPhone = trim((string)($row[10] ?? ''));

                // Validations
                if (empty($nisn)) { $isValid = false; $errors[] = 'NISN wajib diisi'; }
                if (empty($fullName)) { $isValid = false; $errors[] = 'Nama Lengkap wajib diisi'; }
                if (!in_array($gender, ['L', 'P'])) { $isValid = false; $errors[] = 'Gender harus L / P'; }

                if (!empty($nisn) && Student::where('nisn', $nisn)->exists()) {
                    $isValid = false;
                    $errors[] = "NISN {$nisn} sudah terdaftar";
                }

                // Resolve Class
                $classId = null;
                if (!empty($className)) {
                    $cls = ClassRoom::where('name', $className)->orWhere('name', 'LIKE', "%{$className}%")->first();
                    if ($cls) {
                        $classId = $cls->id;
                    }
                }

                $parsed[] = [
                    'row_num' => $rowNum,
                    'nisn' => $nisn,
                    'nis' => $nis ?: $nisn,
                    'nik' => $nik,
                    'full_name' => $fullName,
                    'gender' => $gender ?: 'L',
                    'class_name' => $className,
                    'class_id' => $classId,
                    'birth_place' => $birthPlace,
                    'birth_date' => $birthDate,
                    'previous_school' => $previousSchool,
                    'address' => $address,
                    'parent_phone' => $parentPhone,
                    'father_name' => trim((string)($row[11] ?? '')),
                    'father_status' => strtolower(trim((string)($row[12] ?? 'hidup'))),
                    'father_nik' => trim((string)($row[13] ?? '')),
                    'father_job' => trim((string)($row[14] ?? '')),
                    'father_income' => trim((string)($row[15] ?? '')),
                    'mother_name' => trim((string)($row[16] ?? '')),
                    'mother_status' => strtolower(trim((string)($row[17] ?? 'hidup'))),
                    'mother_nik' => trim((string)($row[18] ?? '')),
                    'mother_job' => trim((string)($row[19] ?? '')),
                    'mother_income' => trim((string)($row[20] ?? '')),
                    'guardian_name' => trim((string)($row[21] ?? '')),
                    'guardian_relation' => trim((string)($row[22] ?? '')),
                    'guardian_nik' => trim((string)($row[23] ?? '')),
                    'guardian_job' => trim((string)($row[24] ?? '')),
                    'guardian_phone' => trim((string)($row[25] ?? '')),
                    'guardian_income' => trim((string)($row[26] ?? '')),
                    'is_valid' => $isValid,
                    'errors' => $errors,
                ];

            } elseif ($type === 'teachers') {
                $nip = trim((string)($row[0] ?? ''));
                $fullName = trim((string)($row[1] ?? ''));
                $gender = strtoupper(trim((string)($row[2] ?? '')));
                $phone = trim((string)($row[3] ?? ''));
                $email = trim((string)($row[4] ?? ''));
                $position = trim((string)($row[5] ?? ''));
                $subjects = trim((string)($row[6] ?? ''));

                if (empty($fullName)) { $isValid = false; $errors[] = 'Nama Guru wajib diisi'; }
                if (empty($nip)) { $isValid = false; $errors[] = 'NIP / NUPTK wajib diisi'; }
                if (!in_array($gender, ['L', 'P'])) { $gender = 'L'; }

                if (!empty($nip) && Teacher::where('nip', $nip)->exists()) {
                    $isValid = false;
                    $errors[] = "NIP {$nip} sudah terdaftar";
                }

                $parsed[] = [
                    'row_num' => $rowNum,
                    'nip' => $nip,
                    'full_name' => $fullName,
                    'gender' => $gender,
                    'phone' => $phone,
                    'email' => $email,
                    'position' => $position ?: 'Guru Pengajar',
                    'subjects' => $subjects,
                    'is_valid' => $isValid,
                    'errors' => $errors,
                ];

            } elseif ($type === 'grades') {
                $nisn = trim((string)($row[0] ?? ''));
                $studentName = trim((string)($row[1] ?? ''));
                $subjectName = trim((string)($row[2] ?? ''));
                $assignment = (float)($row[3] ?? 0);
                $uts = (float)($row[4] ?? 0);
                $uas = (float)($row[5] ?? 0);

                $student = Student::where('nisn', $nisn)->first();
                if (!$student) { $isValid = false; $errors[] = "Siswa NISN {$nisn} tidak ditemukan"; }

                $subject = Subject::where('name', 'LIKE', "%{$subjectName}%")->orWhere('code', $subjectName)->first();
                if (!$subject) { $isValid = false; $errors[] = "Mapel '{$subjectName}' tidak ditemukan"; }

                $parsed[] = [
                    'row_num' => $rowNum,
                    'nisn' => $nisn,
                    'student_name' => $studentName,
                    'subject_name' => $subjectName,
                    'assignment_score' => $assignment,
                    'mid_score' => $uts,
                    'final_score' => $uas,
                    'is_valid' => $isValid,
                    'errors' => $errors,
                ];
            }
        }

        return response()->json([
            'total_rows' => count($parsed),
            'valid_count' => count(array_filter($parsed, fn($r) => $r['is_valid'])),
            'invalid_count' => count(array_filter($parsed, fn($r) => !$r['is_valid'])),
            'rows' => $parsed,
        ]);
    }

    /**
     * Process valid rows into database.
     */
    public function processImport(Request $request, $type)
    {
        $rows = $request->input('rows', []);
        $importedCount = 0;

        foreach ($rows as $row) {
            if (empty($row['is_valid'])) continue;

            if ($type === 'students') {
                $nisn = $row['nisn'];
                $username = $nisn;
                
                // Ensure unique username & email for User account
                if (User::where('username', $username)->exists()) {
                    $username = $username . '_' . rand(100, 999);
                }
                $email = "student_{$nisn}@mtsalhasanah.sch.id";
                if (User::where('email', $email)->exists()) {
                    $email = "student_{$nisn}_" . rand(100, 999) . "@mtsalhasanah.sch.id";
                }

                $user = User::create([
                    'name' => $row['full_name'],
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($nisn),
                    'role' => 'student',
                ]);

                // Resolve class_id if given by class_name
                $classId = $row['class_id'] ?? null;
                if (!$classId && !empty($row['class_name'])) {
                    $cls = ClassRoom::where('name', $row['class_name'])->orWhere('name', 'LIKE', "%{$row['class_name']}%")->first();
                    if ($cls) $classId = $cls->id;
                }

                Student::create([
                    'user_id' => $user->id,
                    'class_id' => $classId,
                    'nisn' => $nisn,
                    'nis' => $row['nis'] ?: $nisn,
                    'nik' => $row['nik'] ?: null,
                    'full_name' => $row['full_name'],
                    'gender' => in_array($row['gender'] ?? '', ['L', 'P']) ? $row['gender'] : 'L',
                    'birth_place' => $row['birth_place'] ?: 'Bandung',
                    'birth_date' => !empty($row['birth_date']) ? $row['birth_date'] : '2010-01-01',
                    'previous_school' => $row['previous_school'] ?? null,
                    'address' => $row['address'] ?: '-',
                    'parent_phone' => $row['parent_phone'] ?: '-',
                    'father_name' => $row['father_name'] ?? null,
                    'father_status' => $row['father_status'] ?: 'hidup',
                    'father_nik' => $row['father_nik'] ?? null,
                    'father_job' => $row['father_job'] ?? null,
                    'father_income' => $row['father_income'] ?? null,
                    'mother_name' => $row['mother_name'] ?? null,
                    'mother_status' => $row['mother_status'] ?: 'hidup',
                    'mother_nik' => $row['mother_nik'] ?? null,
                    'mother_job' => $row['mother_job'] ?? null,
                    'mother_income' => $row['mother_income'] ?? null,
                    'guardian_name' => $row['guardian_name'] ?? null,
                    'guardian_relation' => $row['guardian_relation'] ?? null,
                    'guardian_nik' => $row['guardian_nik'] ?? null,
                    'guardian_job' => $row['guardian_job'] ?? null,
                    'guardian_phone' => $row['guardian_phone'] ?? null,
                    'guardian_income' => $row['guardian_income'] ?? null,
                ]);
                $importedCount++;

            } elseif ($type === 'teachers') {
                $nip = $row['nip'] ?: (string)rand(10000000, 99999999);
                $username = $nip;
                if (User::where('username', $username)->exists()) {
                    $username = $username . '_' . rand(100, 999);
                }
                $email = !empty($row['email']) ? $row['email'] : "guru_{$nip}@mtsalhasanah.sch.id";
                if (User::where('email', $email)->exists()) {
                    $email = "guru_{$nip}_" . rand(100, 999) . "@mtsalhasanah.sch.id";
                }

                $user = User::create([
                    'name' => $row['full_name'],
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($nip),
                    'role' => 'teacher',
                ]);

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $row['nip'] ?: null,
                    'full_name' => $row['full_name'],
                    'gender' => in_array($row['gender'] ?? '', ['L', 'P']) ? $row['gender'] : 'L',
                    'phone' => $row['phone'] ?: '-',
                    'position' => $row['position'] ?: 'Guru Pengajar',
                ]);

                // Sync Subjects if provided in Excel
                if (!empty($row['subjects'])) {
                    $subjectNames = array_map('trim', explode(',', $row['subjects']));
                    $subjectIds = Subject::where(function($q) use ($subjectNames) {
                        foreach ($subjectNames as $sName) {
                            $q->orWhere('name', 'LIKE', "%{$sName}%");
                        }
                    })->pluck('id')->toArray();

                    if (!empty($subjectIds)) {
                        $teacher->subjects()->sync($subjectIds);
                    }
                }

                $importedCount++;

            } elseif ($type === 'grades') {
                $student = Student::where('nisn', $row['nisn'])->first();
                $subject = Subject::where('name', 'LIKE', "%{$row['subject_name']}%")->orWhere('code', $subjectName)->first();
                $academicYear = AcademicYear::where('is_active', true)->first() ?: AcademicYear::first();

                if ($student && $subject && $academicYear) {
                    Grade::updateOrCreate([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $academicYear->id,
                    ], [
                        'assignment_score' => $row['assignment_score'],
                        'mid_score' => $row['mid_score'],
                        'final_score' => $row['final_score'],
                        'final_grade' => round(($row['assignment_score'] * 0.3) + ($row['mid_score'] * 0.3) + ($row['final_score'] * 0.4), 1),
                    ]);
                    $importedCount++;
                }
            }
        }

        return response()->json([
            'message' => "Berhasil mengimpor {$importedCount} data ke database!",
            'imported_count' => $importedCount,
        ]);
    }

    /**
     * Export database records to formatted Excel (.xlsx).
     */
    public function exportExcel($type)
    {
        if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            if ($type === 'students') {
                $sheet->setTitle('Data Siswa');
                $headers = [
                    'NO', 'NISN', 'NIS', 'NIK SISWA', 'NAMA LENGKAP', 'GENDER', 'KELAS', 'TEMPAT LAHIR', 'TGL LAHIR', 'SEKOLAH ASAL', 'ALAMAT', 'NO HP ORTU',
                    'NAMA AYAH', 'STATUS AYAH', 'NIK AYAH', 'PEKERJAAN AYAH', 'PENGHASILAN AYAH',
                    'NAMA IBU', 'STATUS IBU', 'NIK IBU', 'PEKERJAAN IBU', 'PENGHASILAN IBU',
                    'NAMA WALI', 'HUBUNGAN WALI', 'NIK WALI', 'PEKERJAAN WALI', 'NO HP WALI', 'PENGHASILAN WALI'
                ];
                $sheet->fromArray([$headers], null, 'A1');

                $students = Student::with('classRoom')->orderBy('full_name')->get();
                $data = [];
                foreach ($students as $i => $s) {
                    $birthDateStr = '-';
                    if ($s->birth_date) {
                        $birthDateStr = ($s->birth_date instanceof \DateTimeInterface) ? $s->birth_date->format('Y-m-d') : (string)$s->birth_date;
                    }

                    $data[] = [
                        $i + 1,
                        (string)$s->nisn,
                        (string)$s->nis,
                        $s->nik ?: '-',
                        $s->full_name,
                        $s->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                        $s->classRoom ? $s->classRoom->name : ($s->class_name ?: '-'),
                        $s->birth_place,
                        $birthDateStr,
                        $s->previous_school ?: '-',
                        $s->address,
                        $s->parent_phone,
                        $s->father_name ?: '-',
                        $s->father_status ?: '-',
                        $s->father_nik ?: '-',
                        $s->father_job ?: '-',
                        $s->father_income ?: '-',
                        $s->mother_name ?: '-',
                        $s->mother_status ?: '-',
                        $s->mother_nik ?: '-',
                        $s->mother_job ?: '-',
                        $s->mother_income ?: '-',
                        $s->guardian_name ?: '-',
                        $s->guardian_relation ?: '-',
                        $s->guardian_nik ?: '-',
                        $s->guardian_job ?: '-',
                        $s->guardian_phone ?: '-',
                        $s->guardian_income ?: '-',
                    ];
                }
                $sheet->fromArray($data, null, 'A2');

            } elseif ($type === 'teachers') {
                $sheet->setTitle('Data Guru');
                $headers = ['NO', 'NIP / NUPTK', 'NAMA LENGKAP & GELAR', 'GENDER', 'NO. WHATSAPP', 'EMAIL', 'JABATAN / POSISI', 'MAPEL DIAMPU'];
                $sheet->fromArray([$headers], null, 'A1');

                $teachers = Teacher::with(['user', 'subjects'])->orderBy('full_name')->get();
                $data = [];
                foreach ($teachers as $i => $t) {
                    $subjectsStr = ($t->subjects && $t->subjects->count() > 0)
                        ? $t->subjects->pluck('name')->implode(', ')
                        : 'Umum / Kelas';

                    $data[] = [
                        $i + 1,
                        (string)($t->nip ?: '-'),
                        $t->full_name,
                        $t->gender === 'L' ? 'Laki-laki' : ($t->gender === 'P' ? 'Perempuan' : '-'),
                        $t->phone ?: '-',
                        $t->user ? $t->user->email : '-',
                        $t->position ?: 'Guru Pengajar',
                        $subjectsStr,
                    ];
                }
                $sheet->fromArray($data, null, 'A2');

            } elseif ($type === 'grades') {
                $sheet->setTitle('Rekap Nilai');
                $headers = ['NO', 'NISN', 'NAMA SISWA', 'MATA PELAJARAN', 'TUGAS', 'UTS', 'UAS', 'NILAI AKHIR'];
                $sheet->fromArray([$headers], null, 'A1');

                $grades = Grade::with(['student', 'subject'])->get();
                $data = [];
                foreach ($grades as $i => $g) {
                    $data[] = [
                        $i + 1,
                        $g->student ? $g->student->nisn : '-',
                        $g->student ? $g->student->full_name : '-',
                        $g->subject ? $g->subject->name : '-',
                        $g->assignment_score,
                        $g->mid_score,
                        $g->final_score,
                        $g->final_grade,
                    ];
                }
                $sheet->fromArray($data, null, 'A2');
            }

            // Apply Styles
            $lastCol = $sheet->getHighestColumn();
            $lastRow = $sheet->getHighestRow();

            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '064E3B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            if ($lastRow > 1) {
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
            }

            foreach ($sheet->getColumnIterator() as $column) {
                $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = "Data_Export_{$type}_YASPIN.xlsx";

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
                'Pragma' => 'public',
            ]);
        }

        // CSV Fallback
        $fileName = "Data_Export_{$type}_YASPIN.csv";
        return response()->streamDownload(function () use ($type) {
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");

            if ($type === 'students') {
                fputcsv($output, ['NO', 'NISN', 'NIS', 'NAMA LENGKAP', 'GENDER', 'TEMPAT LAHIR', 'TGL LAHIR', 'SEKOLAH ASAL', 'ALAMAT', 'NO HP ORTU', 'NAMA AYAH', 'STATUS AYAH', 'NIK AYAH', 'PEKERJAAN AYAH', 'PENGHASILAN AYAH', 'NAMA IBU', 'STATUS IBU', 'NIK IBU', 'PEKERJAAN IBU', 'PENGHASILAN IBU']);
                $students = Student::orderBy('full_name')->get();
                foreach ($students as $i => $s) {
                    $birthDateStr = $s->birth_date ? (($s->birth_date instanceof \DateTimeInterface) ? $s->birth_date->format('Y-m-d') : (string)$s->birth_date) : '-';
                    fputcsv($output, [
                        $i + 1, $s->nisn, $s->nis, $s->full_name, $s->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                        $s->birth_place, $birthDateStr, $s->previous_school ?: '-', $s->address, $s->parent_phone,
                        $s->father_name ?: '-', $s->father_status ?: '-', $s->father_nik ?: '-', $s->father_job ?: '-', $s->father_income ?: '-',
                        $s->mother_name ?: '-', $s->mother_status ?: '-', $s->mother_nik ?: '-', $s->mother_job ?: '-', $s->mother_income ?: '-'
                    ]);
                }
            } elseif ($type === 'teachers') {
                fputcsv($output, ['NO', 'NIP', 'NUPTK', 'NAMA LENGKAP', 'EMAIL', 'NO HP']);
                $teachers = Teacher::with('user')->orderBy('full_name')->get();
                foreach ($teachers as $i => $t) {
                    fputcsv($output, [$i + 1, $t->nip ?: '-', $t->nuptk ?: '-', $t->full_name, $t->user ? $t->user->email : '-', $t->phone ?: '-']);
                }
            } elseif ($type === 'grades') {
                fputcsv($output, ['NO', 'NISN', 'NAMA SISWA', 'MATA PELAJARAN', 'TUGAS', 'UTS', 'UAS', 'NILAI AKHIR']);
                $grades = Grade::with(['student', 'subject'])->get();
                foreach ($grades as $i => $g) {
                    fputcsv($output, [$i + 1, $g->student ? $g->student->nisn : '-', $g->student ? $g->student->full_name : '-', $g->subject ? $g->subject->name : '-', $g->assignment_score, $g->mid_score, $g->final_score, $g->final_grade]);
                }
            }

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate',
        ]);
    }
}
