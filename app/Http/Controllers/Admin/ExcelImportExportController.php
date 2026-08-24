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
     */
    public function downloadTemplate($type)
    {
        if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            if ($type === 'students') {
                $sheet->setTitle('Template Siswa');
                $headers = ['NISN', 'NIS', 'Nama Lengkap', 'Gender (L/P)', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)', 'Sekolah Asal', 'Alamat', 'No HP Ortu', 'Nama Ayah', 'Status Ayah', 'NIK Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah', 'Nama Ibu', 'Status Ibu', 'NIK Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu'];
                $sheet->fromArray([$headers], null, 'A1');

                $example = [
                    ['1234567890', '2026001', 'Ahmad Rizky', 'L', 'Jakarta', '2010-05-15', 'SDN 01 Jakarta', 'Jl. Merdeka No. 10', '08123456789', 'Sukardi', 'hidup', '3201010101010002', 'Wiraswasta', '2.500.001 - 3.500.000', 'Siti Aminah', 'hidup', '3201010101010001', 'Ibu Rumah Tangga', 'dibawah 800.000'],
                    ['1234567891', '2026002', 'Siti Nurhaliza', 'P', 'Bandung', '2010-08-20', 'SDN 02 Bandung', 'Jl. Mawar No. 5', '08198765432', 'Budi Santoso', 'hidup', '3201010101010004', 'PNS', '4.800.001 - 6.500.000', 'Rina Wati', 'hidup', '3201010101010003', 'Guru/Dosen', '3.500.001 - 4.800.000'],
                ];
                $sheet->fromArray($example, null, 'A2');

            } elseif ($type === 'teachers') {
                $sheet->setTitle('Template Guru');
                $headers = ['NIP', 'NUPTK', 'Nama Lengkap', 'Email', 'No HP'];
                $sheet->fromArray([$headers], null, 'A1');

                $example = [
                    ['198501012010011001', '1234567890123456', 'Budi Santoso, S.Pd', 'budi@sekolah.sch.id', '08122334455'],
                    ['199002022015022002', '6543210987654321', 'Dewi Lestari, M.Pd', 'dewi@sekolah.sch.id', '08166778899'],
                ];
                $sheet->fromArray($example, null, 'A2');

            } elseif ($type === 'grades') {
                $sheet->setTitle('Template Nilai');
                $headers = ['NISN', 'Nama Siswa', 'Nama/Kode Mapel', 'Nilai Tugas (0-100)', 'Nilai UTS (0-100)', 'Nilai UAS (0-100)'];
                $sheet->fromArray([$headers], null, 'A1');

                $example = [
                    ['1234567890', 'Ahmad Rizky', 'Matematika', 85, 80, 90],
                    ['1234567891', 'Siti Nurhaliza', 'Matematika', 90, 88, 92],
                ];
                $sheet->fromArray($example, null, 'A2');
            } else {
                return response()->json(['message' => 'Tipe template tidak valid'], 400);
            }

            $lastCol = $sheet->getHighestColumn();
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            foreach (range('A', $lastCol) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = "Template_Import_{$type}_YASPIN.xlsx";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        }

        // CSV Fallback
        $fileName = "Template_Import_{$type}_YASPIN.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        if ($type === 'students') {
            fputcsv($output, ['NISN', 'NIS', 'Nama Lengkap', 'Gender (L/P)', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)', 'Sekolah Asal', 'Alamat', 'No HP Ortu', 'Nama Ayah', 'Status Ayah', 'NIK Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah', 'Nama Ibu', 'Status Ibu', 'NIK Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu']);
            fputcsv($output, ['1234567890', '2026001', 'Ahmad Rizky', 'L', 'Jakarta', '2010-05-15', 'SDN 01 Jakarta', 'Jl. Merdeka No. 10', '08123456789', 'Sukardi', 'hidup', '3201010101010002', 'Wiraswasta', '2.500.001 - 3.500.000', 'Siti Aminah', 'hidup', '3201010101010001', 'Ibu Rumah Tangga', 'dibawah 800.000']);
            fputcsv($output, ['1234567891', '2026002', 'Siti Nurhaliza', 'P', 'Bandung', '2010-08-20', 'SDN 02 Bandung', 'Jl. Mawar No. 5', '08198765432', 'Budi Santoso', 'hidup', '3201010101010004', 'PNS', '4.800.001 - 6.500.000', 'Rina Wati', 'hidup', '3201010101010003', 'Guru/Dosen', '3.500.001 - 4.800.000']);
        } elseif ($type === 'teachers') {
            fputcsv($output, ['NIP', 'NUPTK', 'Nama Lengkap', 'Email', 'No HP']);
            fputcsv($output, ['198501012010011001', '1234567890123456', 'Budi Santoso, S.Pd', 'budi@sekolah.sch.id', '08122334455']);
            fputcsv($output, ['199002022015022002', '6543210987654321', 'Dewi Lestari, M.Pd', 'dewi@sekolah.sch.id', '08166778899']);
        } elseif ($type === 'grades') {
            fputcsv($output, ['NISN', 'Nama Siswa', 'Nama/Kode Mapel', 'Nilai Tugas (0-100)', 'Nilai UTS (0-100)', 'Nilai UAS (0-100)']);
            fputcsv($output, ['1234567890', 'Ahmad Rizky', 'Matematika', 85, 80, 90]);
            fputcsv($output, ['1234567891', 'Siti Nurhaliza', 'Matematika', 90, 88, 92]);
        }
        fclose($output);
        exit;
    }

    /**
     * Preview uploaded Excel file with validation.
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

        $header = array_map('trim', $rows[0]);
        $dataRows = array_slice($rows, 1);
        $parsed = [];

        foreach ($dataRows as $index => $row) {
            if (empty(array_filter($row))) continue; // Skip empty rows

            $rowNum = $index + 2;
            $isValid = true;
            $errors = [];

            if ($type === 'students') {
                $nisn = trim($row[0] ?? '');
                $nis = trim($row[1] ?? '');
                $fullName = trim($row[2] ?? '');
                $gender = strtoupper(trim($row[3] ?? ''));

                if (empty($nisn)) { $isValid = false; $errors[] = 'NISN wajib diisi'; }
                if (empty($fullName)) { $isValid = false; $errors[] = 'Nama wajib diisi'; }
                if (!in_array($gender, ['L', 'P'])) { $isValid = false; $errors[] = 'Gender harus L atau P'; }

                if (Student::where('nisn', $nisn)->exists()) {
                    $isValid = false;
                    $errors[] = "NISN {$nisn} sudah terdaftar";
                }

                $parsed[] = [
                    'row_num' => $rowNum,
                    'nisn' => $nisn,
                    'nis' => $nis,
                    'full_name' => $fullName,
                    'gender' => $gender,
                    'birth_place' => trim($row[4] ?? ''),
                    'birth_date' => trim($row[5] ?? ''),
                    'previous_school' => trim($row[6] ?? ''),
                    'address' => trim($row[7] ?? ''),
                    'parent_phone' => trim($row[8] ?? ''),
                    'father_name' => trim($row[9] ?? ''),
                    'father_status' => strtolower(trim($row[10] ?? 'hidup')),
                    'father_nik' => trim($row[11] ?? ''),
                    'father_job' => trim($row[12] ?? ''),
                    'father_income' => trim($row[13] ?? ''),
                    'mother_name' => trim($row[14] ?? ''),
                    'mother_status' => strtolower(trim($row[15] ?? 'hidup')),
                    'mother_nik' => trim($row[16] ?? ''),
                    'mother_job' => trim($row[17] ?? ''),
                    'mother_income' => trim($row[18] ?? ''),
                    'is_valid' => $isValid,
                    'errors' => $errors,
                ];

            } elseif ($type === 'teachers') {
                $nip = trim($row[0] ?? '');
                $nuptk = trim($row[1] ?? '');
                $fullName = trim($row[2] ?? '');
                $email = trim($row[3] ?? '');

                if (empty($fullName)) { $isValid = false; $errors[] = 'Nama Guru wajib diisi'; }

                if (!empty($nip) && Teacher::where('nip', $nip)->exists()) {
                    $isValid = false;
                    $errors[] = "NIP {$nip} sudah terdaftar";
                }

                $parsed[] = [
                    'row_num' => $rowNum,
                    'nip' => $nip,
                    'nuptk' => $nuptk,
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => trim($row[4] ?? ''),
                    'is_valid' => $isValid,
                    'errors' => $errors,
                ];

            } elseif ($type === 'grades') {
                $nisn = trim($row[0] ?? '');
                $studentName = trim($row[1] ?? '');
                $subjectName = trim($row[2] ?? '');
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
                $user = User::create([
                    'name' => $row['full_name'],
                    'username' => $username,
                    'email' => "student_{$nisn}@school.id",
                    'password' => Hash::make($nisn),
                    'role' => 'student',
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'nisn' => $nisn,
                    'nis' => $row['nis'] ?: $nisn,
                    'full_name' => $row['full_name'],
                    'gender' => $row['gender'] ?: 'L',
                    'birth_place' => $row['birth_place'] ?: 'Jakarta',
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
                ]);
                $importedCount++;

            } elseif ($type === 'teachers') {
                $nip = $row['nip'] ?: rand(10000000, 99999999);
                $username = !empty($row['nip']) ? $row['nip'] : ('guru_' . rand(100, 999));
                $user = User::create([
                    'name' => $row['full_name'],
                    'username' => $username,
                    'email' => !empty($row['email']) ? $row['email'] : "teacher_{$nip}@school.id",
                    'password' => Hash::make($nip),
                    'role' => 'teacher',
                ]);

                Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $row['nip'] ?: null,
                    'nuptk' => $row['nuptk'] ?: null,
                    'full_name' => $row['full_name'],
                    'phone' => $row['phone'] ?: '-',
                ]);
                $importedCount++;

            } elseif ($type === 'grades') {
                $student = Student::where('nisn', $row['nisn'])->first();
                $subject = Subject::where('name', 'LIKE', "%{$row['subject_name']}%")->orWhere('code', $row['subject_name'])->first();
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
                $headers = ['NO', 'NISN', 'NIS', 'NAMA LENGKAP', 'GENDER', 'TEMPAT LAHIR', 'TGL LAHIR', 'SEKOLAH ASAL', 'ALAMAT', 'NO HP ORTU', 'NAMA AYAH', 'STATUS AYAH', 'NIK AYAH', 'PEKERJAAN AYAH', 'PENGHASILAN AYAH', 'NAMA IBU', 'STATUS IBU', 'NIK IBU', 'PEKERJAAN IBU', 'PENGHASILAN IBU'];
                $sheet->fromArray([$headers], null, 'A1');

                $students = Student::orderBy('full_name')->get();
                $data = [];
                foreach ($students as $i => $s) {
                    $data[] = [
                        $i + 1,
                        $s->nisn,
                        $s->nis,
                        $s->full_name,
                        $s->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                        $s->birth_place,
                        $s->birth_date,
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
                    ];
                }
                $sheet->fromArray($data, null, 'A2');

            } elseif ($type === 'teachers') {
                $sheet->setTitle('Data Guru');
                $headers = ['NO', 'NIP', 'NUPTK', 'NAMA LENGKAP', 'EMAIL', 'NO HP'];
                $sheet->fromArray([$headers], null, 'A1');

                $teachers = Teacher::with('user')->orderBy('full_name')->get();
                $data = [];
                foreach ($teachers as $i => $t) {
                    $data[] = [
                        $i + 1,
                        $t->nip ?: '-',
                        $t->nuptk ?: '-',
                        $t->full_name,
                        $t->user->email ?? '-',
                        $t->phone ?: '-',
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
                        $g->student->nisn ?? '-',
                        $g->student->full_name ?? '-',
                        $g->subject->name ?? '-',
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
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E1B4B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            if ($lastRow > 1) {
                $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
            }

            foreach (range('A', $lastCol) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $fileName = "Data_Export_{$type}_YASPIN.xlsx";

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        }

        // CSV Fallback
        $fileName = "Data_Export_{$type}_YASPIN.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");

        if ($type === 'students') {
            fputcsv($output, ['NO', 'NISN', 'NIS', 'NAMA LENGKAP', 'GENDER', 'TEMPAT LAHIR', 'TGL LAHIR', 'SEKOLAH ASAL', 'ALAMAT', 'NO HP ORTU', 'NAMA AYAH', 'STATUS AYAH', 'NIK AYAH', 'PEKERJAAN AYAH', 'PENGHASILAN AYAH', 'NAMA IBU', 'STATUS IBU', 'NIK IBU', 'PEKERJAAN IBU', 'PENGHASILAN IBU']);
            $students = Student::orderBy('full_name')->get();
            foreach ($students as $i => $s) {
                fputcsv($output, [
                    $i + 1, $s->nisn, $s->nis, $s->full_name, $s->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                    $s->birth_place, $s->birth_date, $s->previous_school ?: '-', $s->address, $s->parent_phone,
                    $s->father_name ?: '-', $s->father_status ?: '-', $s->father_nik ?: '-', $s->father_job ?: '-', $s->father_income ?: '-',
                    $s->mother_name ?: '-', $s->mother_status ?: '-', $s->mother_nik ?: '-', $s->mother_job ?: '-', $s->mother_income ?: '-'
                ]);
            }
        } elseif ($type === 'teachers') {
            fputcsv($output, ['NO', 'NIP', 'NUPTK', 'NAMA LENGKAP', 'EMAIL', 'NO HP']);
            $teachers = Teacher::with('user')->orderBy('full_name')->get();
            foreach ($teachers as $i => $t) {
                fputcsv($output, [$i + 1, $t->nip ?: '-', $t->nuptk ?: '-', $t->full_name, $t->user->email ?? '-', $t->phone ?: '-']);
            }
        } elseif ($type === 'grades') {
            fputcsv($output, ['NO', 'NISN', 'NAMA SISWA', 'MATA PELAJARAN', 'TUGAS', 'UTS', 'UAS', 'NILAI AKHIR']);
            $grades = Grade::with(['student', 'subject'])->get();
            foreach ($grades as $i => $g) {
                fputcsv($output, [$i + 1, $g->student->nisn ?? '-', $g->student->full_name ?? '-', $g->subject->name ?? '-', $g->assignment_score, $g->mid_score, $g->final_score, $g->final_grade]);
            }
        }

        fclose($output);
        exit;
    }
}
