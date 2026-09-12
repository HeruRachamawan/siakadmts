<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends BaseController
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'classRoom.academicYear']);

        if ($request->filled('lokal_class_id')) {
            $query->where('lokal_class_id', $request->input('lokal_class_id'));
        } elseif ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $targetClass = ClassRoom::find($classId);
            if ($targetClass && in_array($targetClass->name, ['7', '8'])) {
                $query->where(function ($q) use ($classId) {
                    $q->where('lokal_class_id', $classId)
                      ->orWhere('class_id', $classId);
                });
            } else {
                $query->where('class_id', $classId);
            }
        }

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($qq) use ($q) {
                $qq->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $students = $query->orderBy('full_name')
            ->paginate($request->get('per_page', 15));

        return $this->success($this->paginate($students));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nisn' => ['required', 'string', 'max:255', 'unique:students,nisn'],
            'nis' => ['required', 'string', 'max:255', 'unique:students,nis'],
            'nik' => ['nullable', 'string', 'max:20'],
            'no_kk' => ['nullable', 'string', 'max:20'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'child_number' => ['nullable', 'integer', 'min:1', 'max:50'],
            'siblings_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'hobby' => ['nullable', 'string', 'max:100'],
            'aspiration' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_status' => ['nullable', Rule::in(['hidup', 'meninggal', 'tidak_diketahui', 'pisah', 'lainnya'])],
            'mother_nik' => ['nullable', 'string', 'max:255'],
            'mother_job' => ['nullable', 'string', 'max:255'],
            'mother_income' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_status' => ['nullable', Rule::in(['hidup', 'meninggal', 'tidak_diketahui', 'pisah', 'lainnya'])],
            'father_nik' => ['nullable', 'string', 'max:255'],
            'father_job' => ['nullable', 'string', 'max:255'],
            'father_income' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_relation' => ['nullable', 'string', 'max:255'],
            'guardian_nik' => ['nullable', 'string', 'max:255'],
            'guardian_job' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_income' => ['nullable', 'string', 'max:255'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'photo'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        // Auto-generate username/password/email from NISN
        $username = $request->nisn;
        $originalUsername = $username;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '_' . $i++;
        }

        $email = $request->nisn . '@student.example.com';
        $originalEmail = $email;
        $j = 1;
        while (User::where('email', $email)->exists()) {
            $email = $originalEmail . '_' . $j++;
        }

        $user = User::create([
            'name' => $request->full_name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($request->nisn),
            'role' => 'student',
        ]);

        $studentData = $request->only([
            'nisn', 'nis', 'nik', 'no_kk', 'full_name', 'gender', 'birth_place', 'birth_date', 'religion',
            'child_number', 'siblings_count', 'hobby', 'aspiration', 'address', 'parent_phone', 'class_id',
            'mother_name', 'mother_status', 'mother_nik', 'mother_job', 'mother_income',
            'father_name', 'father_status', 'father_nik', 'father_job', 'father_income',
            'guardian_name', 'guardian_relation', 'guardian_nik', 'guardian_job', 'guardian_phone', 'guardian_income',
            'previous_school',
        ]);

        if ($request->hasFile('photo')) {
            $studentData['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student = $user->student()->create($studentData);

        return $this->success($student->load('user', 'classRoom'), 'Siswa dibuat dengan kredensial otomatis', 201);
    }

    public function show(Student $student)
    {
        return $this->success($student->load(['user', 'classRoom.academicYear', 'grades']));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nisn' => ['sometimes', 'string', 'max:255', Rule::unique('students', 'nisn')->ignore($student->id)],
            'nis' => ['sometimes', 'string', 'max:255', Rule::unique('students', 'nis')->ignore($student->id)],
            'nik' => ['nullable', 'string', 'max:20'],
            'no_kk' => ['nullable', 'string', 'max:20'],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'gender' => ['sometimes', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:30'],
            'child_number' => ['nullable', 'integer', 'min:1', 'max:50'],
            'siblings_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'hobby' => ['nullable', 'string', 'max:100'],
            'aspiration' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_status' => ['nullable', Rule::in(['hidup', 'meninggal', 'tidak_diketahui', 'pisah', 'lainnya'])],
            'mother_nik' => ['nullable', 'string', 'max:255'],
            'mother_job' => ['nullable', 'string', 'max:255'],
            'mother_income' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_status' => ['nullable', Rule::in(['hidup', 'meninggal', 'tidak_diketahui', 'pisah', 'lainnya'])],
            'father_nik' => ['nullable', 'string', 'max:255'],
            'father_job' => ['nullable', 'string', 'max:255'],
            'father_income' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_relation' => ['nullable', 'string', 'max:255'],
            'guardian_nik' => ['nullable', 'string', 'max:255'],
            'guardian_job' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_income' => ['nullable', 'string', 'max:255'],
            'previous_school' => ['nullable', 'string', 'max:255'],
            'photo'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $updateData = $request->only([
            'nisn', 'nis', 'nik', 'no_kk', 'full_name', 'gender', 'birth_place', 'birth_date', 'religion',
            'child_number', 'siblings_count', 'hobby', 'aspiration', 'address', 'parent_phone', 'class_id',
            'mother_name', 'mother_status', 'mother_nik', 'mother_job', 'mother_income',
            'father_name', 'father_status', 'father_nik', 'father_job', 'father_income',
            'guardian_name', 'guardian_relation', 'guardian_nik', 'guardian_job', 'guardian_phone', 'guardian_income',
            'previous_school',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student->update($updateData);

        return $this->success($student->fresh()->load('user', 'classRoom'));
    }

    public function destroy(Student $student)
    {
        $user = $student->user;
        $student->delete();
        $user?->delete();

        return $this->success(null, 'Siswa dihapus');
    }

    public function assignClass(Request $request, Student $student)
    {
        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
        ]);

        $student->update(['class_id' => $request->input('class_id')]);

        return $this->success($student->load('classRoom'), 'Siswa berhasil diplotting ke kelas');
    }

    public function resetCredentials(Student $student)
    {
        $student->user->update([
            'password' => Hash::make($student->nisn),
        ]);

        return $this->success(['reset' => true], 'Kredensial direset ke NISN');
    }

    public function template()
    {
        $options = new \OpenSpout\Writer\XLSX\Options();
        $writer = new \OpenSpout\Writer\XLSX\Writer($options);
        
        $tempPath = tempnam(sys_get_temp_dir(), 'siswa_');
        $writer->openToFile($tempPath);

        // Header style
        $style = (new \OpenSpout\Common\Entity\Style\Style())->withFontBold(true);
        
        // Baris 1: Header Kolom
        $headers = [
            'NISN (10 Digit)*', 'NIS*', 'NIK Siswa (16 Digit)*', 'No KK (16 Digit)', 'Nama Lengkap*', 'Gender (L/P)*', 'Nama Kelas*',
            'Tempat Lahir*', 'Tanggal Lahir (YYYY-MM-DD)*', 'Agama', 'Anak Ke', 'Jumlah Saudara', 'Hobi', 'Cita-cita',
            'Sekolah Asal', 'Alamat Lengkap*', 'No HP / WA Ortu (Isi - jika tidak ada)*',
            'Nama Ayah', 'Status Ayah (hidup/meninggal/tidak_diketahui)', 'NIK Ayah', 'Pekerjaan Ayah', 'Penghasilan Ayah',
            'Nama Ibu', 'Status Ibu (hidup/meninggal/tidak_diketahui)', 'NIK Ibu', 'Pekerjaan Ibu', 'Penghasilan Ibu',
            'Nama Wali', 'Hubungan Wali', 'NIK Wali', 'Pekerjaan Wali', 'No HP Wali', 'Penghasilan Wali'
        ];
        
        $writer->addRow(\OpenSpout\Common\Entity\Row::fromValuesWithStyle($headers, $style));
        
        // Baris 2: Contoh Format Pengisian (using explicit strings)
        $sampleData = [
            '0051234567', '2026001', '3201011505100001', '3201010101100005', 'Ahmad Rizky Pratama', 'L', '7A',
            'Bandung', '2010-05-15', 'Islam', '1', '2', 'Membaca', 'Guru',
            'SDN 01 Ciwidey', 'Jl. Raya Ciwidey No. 10 RT 01/02', '081234567890',
            'Sukardi', 'hidup', '3201010101750001', 'Wiraswasta', '2.500.001 - 3.500.000',
            'Siti Aminah', 'hidup', '3201010101800002', 'Ibu Rumah Tangga', 'dibawah 800.000',
            '', '', '', '', '', ''
        ];
        
        $sampleCells = array_map(function($data) {
            return \OpenSpout\Common\Entity\Cell::fromValue((string)$data);
        }, $sampleData);
        $writer->addRow(new \OpenSpout\Common\Entity\Row($sampleCells));
        
        $writer->close();

        return response()->download($tempPath, 'template_siswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240']
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());
        
        try {
            if (in_array($extension, ['csv', 'txt'])) {
                $options = new \OpenSpout\Reader\CSV\Options();
                $options->FIELD_DELIMITER = ',';
                $reader = new \OpenSpout\Reader\CSV\Reader($options);
            } else {
                $options = new \OpenSpout\Reader\XLSX\Options();
                $reader = new \OpenSpout\Reader\XLSX\Reader($options);
            }
            $reader->open($path);
        } catch (\Exception $e) {
            return $this->error('Gagal membaca file Excel/CSV: ' . $e->getMessage(), 500);
        }
        
        $success = 0;
        $failed = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $isFirstRow = true;
                $headersMap = [];

                foreach ($sheet->getRowIterator() as $rowObj) {
                    if ($isFirstRow) {
                        $rawHeaders = $rowObj->toArray();
                        foreach ($rawHeaders as $idx => $hName) {
                            $clean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string)$hName));
                            if (!empty($clean)) {
                                $headersMap[$clean] = $idx;
                            }
                        }
                        $isFirstRow = false;
                        continue; // Skip header
                    }

                    $row = $rowObj->toArray();
                    
                    // Skip completely empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    $row = array_pad($row, 40, null);
                    
                    // Get cell values safely
                    $getValue = function($val) {
                        if ($val instanceof \DateTimeInterface) return $val->format('Y-m-d');
                        return trim((string)($val ?? ''));
                    };

                    $getCol = function(array $keys, $fallbackIdx) use ($headersMap, $getValue, $row) {
                        foreach ($keys as $k) {
                            if (isset($headersMap[$k]) && isset($row[$headersMap[$k]])) {
                                return $getValue($row[$headersMap[$k]]);
                            }
                        }
                        return isset($row[$fallbackIdx]) ? $getValue($row[$fallbackIdx]) : '';
                    };
                    
                    $nisn = $getCol(['nisn10digit', 'nisn'], 0);
                    $nis = $getCol(['nis'], 1);
                    $nik = $getCol(['niksiswa16digit', 'niksiswa', 'nik'], 2);
                    $noKk = $getCol(['nokk16digit', 'nokk', 'kartukeluarga'], 3);
                    $nama = $getCol(['namalengkap', 'nama'], 4);
                    $gender = strtoupper($getCol(['genderlp', 'gender', 'jeniskelamin'], 5));
                    $className = $getCol(['namakelas', 'kelas'], 6);

                    // If old template where column 3 is Nama and 4 is Gender
                    if (empty($nama) && !empty($noKk) && in_array(strtoupper($gender), ['L', 'P'])) {
                        // Looks like new template
                    } elseif (in_array(strtoupper($nama), ['L', 'P'])) {
                        // Detected old template without No KK: col 2=nik, col 3=nama, col 4=gender, col 5=kelas
                        $gender = strtoupper($nama);
                        $nama = $noKk;
                        $noKk = null;
                        $className = $getCol([], 5);
                    }
                    
                    if (empty($nisn) || empty($nama) || !in_array($gender, ['L', 'P'])) {
                        $failed++;
                        continue;
                    }

                    if (Student::where('nisn', $nisn)->exists()) {
                        $failed++;
                        continue;
                    }

                    $classId = null;
                    if (!empty($className)) {
                        $class = \App\Models\ClassRoom::where('name', $className)->orWhere('name', 'LIKE', "%{$className}%")->first();
                        if ($class) {
                            $classId = $class->id;
                        }
                    }

                    $birthPlace = $getCol(['tempatlahir'], 7) ?: 'Bandung';
                    $birthDate = $getCol(['tanggallahiryyyymmdd', 'tanggallahir'], 8) ?: '2010-01-01';
                    $religion = $getCol(['agama'], 9) ?: 'Islam';
                    $childNumber = $getCol(['anakke'], 10);
                    $siblingsCount = $getCol(['jumlahsaudara'], 11);
                    $hobby = $getCol(['hobi'], 12);
                    $aspiration = $getCol(['citacita'], 13);
                    $prevSchool = $getCol(['sekolahasal'], 14);
                    $address = $getCol(['alamatlengkap', 'alamat'], 15) ?: '-';
                    $parentPhone = $getCol(['nohpwaortuisijikatidakada', 'nohpwaortu', 'nohportu', 'nohp'], 16) ?: '-';

                    $username = $nisn;
                    if (User::where('username', $username)->exists()) {
                        $username = $username . '_' . rand(100, 999);
                    }

                    $email = $nisn . '@mtsalhasanah.sch.id';
                    if (User::where('email', $email)->exists()) {
                        $email = $nisn . '_' . rand(100, 999) . '@mtsalhasanah.sch.id';
                    }

                    $user = User::create([
                        'name' => $nama,
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make($nisn), // Password uses NISN
                        'role' => 'student',
                    ]);

                    $user->student()->create([
                        'nisn' => $nisn,
                        'nis' => $nis ?: $nisn,
                        'nik' => $nik ?: null,
                        'no_kk' => $noKk ?: null,
                        'full_name' => $nama,
                        'gender' => $gender,
                        'class_id' => $classId,
                        'birth_place' => $birthPlace,
                        'birth_date' => $birthDate,
                        'religion' => $religion,
                        'child_number' => is_numeric($childNumber) ? (int)$childNumber : null,
                        'siblings_count' => is_numeric($siblingsCount) ? (int)$siblingsCount : null,
                        'hobby' => $hobby ?: null,
                        'aspiration' => $aspiration ?: null,
                        'previous_school' => $prevSchool ?: null,
                        'address' => $address,
                        'parent_phone' => $parentPhone,
                        
                        'father_name' => $getCol(['namaayah'], 17),
                        'father_status' => strtolower($getCol(['statusayahhidupmeninggaltidakketahui', 'statusayah'], 18)) ?: 'hidup',
                        'father_nik' => $getCol(['nikayah'], 19),
                        'father_job' => $getCol(['pekerjaanayah'], 20),
                        'father_income' => $getCol(['penghasilanayah'], 21),
                        
                        'mother_name' => $getCol(['namaibu'], 22),
                        'mother_status' => strtolower($getCol(['statusibuhidupmeninggaltidakketahui', 'statusibu'], 23)) ?: 'hidup',
                        'mother_nik' => $getCol(['nikibu'], 24),
                        'mother_job' => $getCol(['pekerjaanibu'], 25),
                        'mother_income' => $getCol(['penghasilanibu'], 26),

                        'guardian_name' => $getCol(['namawali'], 27),
                        'guardian_relation' => $getCol(['hubunganwali'], 28),
                        'guardian_nik' => $getCol(['nikwali'], 29),
                        'guardian_job' => $getCol(['pekerjaanwali'], 30),
                        'guardian_phone' => $getCol(['nohpwali'], 31),
                        'guardian_income' => $getCol(['penghasilanwali'], 32),
                    ]);

                    $success++;
                }
                break; // Only process first sheet
            }
            $reader->close();
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            if (isset($reader)) $reader->close();
            \Illuminate\Support\Facades\DB::rollBack();
            return $this->error('Gagal mengimpor data: ' . $e->getMessage(), 500);
        }
        
        return $this->success(
            ['success' => $success, 'failed' => $failed],
            "Berhasil mengimpor $success siswa. Gagal: $failed"
        );
    }

    public function impersonate(Request $request, Student $student)
    {
        // Strict security: Only Super Admin (role: admin) can impersonate
        if ($request->user()->role !== 'admin') {
            return $this->error('Akses ditolak. Fitur Login Sebagai Siswa hanya dapat digunakan oleh Super Admin.', 403);
        }

        $user = $student->user;
        if (! $user) {
            $user = User::where('username', $student->nisn)
                ->orWhere('username', $student->nis)
                ->first();
            if ($user) {
                $student->user_id = $user->id;
                $student->save();
            } else {
                // Auto create user account for student if not exists yet
                $username = !empty($student->nisn) ? $student->nisn : (!empty($student->nis) ? $student->nis : 'siswa_' . $student->id);
                $user = User::create([
                    'name' => $student->full_name,
                    'username' => $username,
                    'email' => $username . '@siakad.mts',
                    'password' => Hash::make($username),
                    'role' => 'student',
                ]);
                $student->user_id = $user->id;
                $student->save();
            }
        }

        // Generate Sanctum token for target student user
        $token = $user->createToken('impersonation-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => \App\Http\Controllers\Auth\AuthController::formatUserPayload($user),
            'impersonated' => true,
        ], "Berhasil masuk sebagai siswa {$student->full_name}");
    }

    /**
     * Transfer one or multiple students to another class.
     */
    public function transferClass(Request $request)
    {
        $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
            'target_class_id' => ['required', 'exists:classes,id'],
        ]);

        $targetClass = ClassRoom::findOrFail($request->input('target_class_id'));
        $studentIds = $request->input('student_ids');

        $count = Student::whereIn('id', $studentIds)
            ->update(['class_id' => $targetClass->id]);

        return $this->success([
            'updated_count' => $count,
            'target_class' => [
                'id' => $targetClass->id,
                'name' => $targetClass->name,
                'grade_level' => $targetClass->grade_level,
            ],
        ], "Berhasil memindahkan {$count} siswa ke kelas {$targetClass->name}");
    }
}
