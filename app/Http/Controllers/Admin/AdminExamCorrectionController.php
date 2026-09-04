<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\ExamPackage;
use App\Models\ExamSubmission;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExamCorrectionController extends Controller
{
    /**
     * List all exams across all teachers with rich filtering.
     */
    public function index(Request $request)
    {
        $query = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher'])
            ->withCount(['questions', 'submissions']);

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('subject', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('teacher', fn($tq) => $tq->where('name', 'like', "%{$search}%"));
            });
        }

        $exams = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        $exams->getCollection()->transform(function ($exam) {
            $subs = ExamSubmission::where('exam_package_id', $exam->id)->get();
            $exam->avg_score = $subs->count() > 0 ? round($subs->avg('total_score'), 2) : 0;
            $exam->passed_count = $subs->where('is_passed', true)->count();
            $exam->remedial_count = $subs->where('is_passed', false)->count();
            $exam->total_students = $subs->count();
            return $exam;
        });

        return response()->json([
            'status' => 'success',
            'data' => $exams
        ]);
    }

    /**
     * Global Overview & Compliance Statistics for Madrasah.
     */
    public function summary(Request $request)
    {
        $totalExams = ExamPackage::count();
        $totalSubmissions = ExamSubmission::count();
        $avgScore = $totalSubmissions > 0 ? round(ExamSubmission::avg('total_score'), 2) : 0;
        $passedCount = ExamSubmission::where('is_passed', true)->count();
        $passRate = $totalSubmissions > 0 ? round(($passedCount / $totalSubmissions) * 100, 1) : 0;

        // Breakdown by Exam Type
        $byType = ExamPackage::selectRaw('exam_type, count(*) as count')
            ->groupBy('exam_type')
            ->pluck('count', 'exam_type');

        // Teacher Compliance: Teachers who have created at least one exam vs total teachers
        $totalTeachers = Teacher::count();
        $activeExamTeacherIds = ExamPackage::distinct()->pluck('teacher_id')->filter()->toArray();
        $teachersWithExamsCount = count($activeExamTeacherIds);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_exams' => $totalExams,
                'total_submissions' => $totalSubmissions,
                'avg_score' => $avgScore,
                'passed_count' => $passedCount,
                'remedial_count' => $totalSubmissions - $passedCount,
                'pass_rate' => $passRate,
                'by_type' => $byType,
                'teachers_stats' => [
                    'total_teachers' => $totalTeachers,
                    'active_teachers_count' => $teachersWithExamsCount,
                    'compliance_percentage' => $totalTeachers > 0 ? round(($teachersWithExamsCount / $totalTeachers) * 100, 1) : 0,
                ]
            ]
        ]);
    }

    /**
     * Destroy / Delete an exam package from Admin.
     */
    public function destroy($id)
    {
        $exam = ExamPackage::findOrFail($id);
        $exam->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Paket ujian berhasil dihapus dari sistem oleh Admin!'
        ]);
    }

    /**
     * Export consolidated summary of all exams across the madrasah into Excel.
     */
    public function exportAll(Request $request)
    {
        $exams = ExamPackage::with(['classRoom', 'subject', 'teacher', 'academicYear'])
            ->withCount('submissions')
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monitoring Asesmen Madrasah');

        $headers = [
            'No', 'Judul Ujian', 'Mata Pelajaran', 'Kelas', 'Guru Pengampu',
            'Tipe Ujian', 'Semester', 'KKM', 'Jml Soal', 'Jml Siswa Mengikuti',
            'Rata-rata Nilai', 'Siswa Tuntas', 'Siswa Remedial', 'Status'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastCol}1")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1E293B'); // Slate 800
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->getColor()->setARGB('FFFFFFFF');

        $rowNum = 2;
        $no = 1;
        foreach ($exams as $exam) {
            $subs = ExamSubmission::where('exam_package_id', $exam->id)->get();
            $avg = $subs->count() > 0 ? round($subs->avg('total_score'), 2) : 0;
            $passed = $subs->where('is_passed', true)->count();
            $remedial = $subs->count() - $passed;

            $rowData = [
                $no++,
                $exam->title,
                $exam->subject?->name ?? '-',
                $exam->classRoom?->name ?? '-',
                $exam->teacher?->name ?? '-',
                strtoupper($exam->exam_type),
                ucfirst($exam->semester),
                $exam->kkm,
                $exam->total_questions,
                $subs->count(),
                $avg,
                $passed,
                $remedial,
                strtoupper($exam->status),
            ];

            $sheet->fromArray([$rowData], null, "A{$rowNum}");
            $rowNum++;
        }

        foreach ($sheet->getColumnIterator() as $col) {
            $sheet->getColumnDimension($col->getColumnIndex())->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Monitoring_Asesmen_Madrasah_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
