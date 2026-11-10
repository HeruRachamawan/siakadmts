<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\ExamPackage;
use App\Models\ExamQuestion;
use App\Models\SchoolSetting;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KurikulumExamReviewController extends Controller
{
    /**
     * Overview & monitoring statistics for Kurikulum.
     */
    public function index(Request $request)
    {
        $query = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher.user', 'reviewedBy'])
            ->withCount([
                'questions',
                'questions as pg_count' => function ($q) {
                    $q->where('question_type', 'pg');
                },
                'questions as pg_complex_count' => function ($q) {
                    $q->where('question_type', 'pg_complex');
                },
                'questions as true_false_count' => function ($q) {
                    $q->where('question_type', 'true_false');
                },
                'questions as matching_count' => function ($q) {
                    $q->where('question_type', 'matching');
                },
                'questions as short_answer_count' => function ($q) {
                    $q->where('question_type', 'short_answer');
                },
                'questions as essay_count' => function ($q) {
                    $q->where('question_type', 'essay');
                },
                'questions as reviewed_valid_count' => function ($q) {
                    $q->where('telaah_status', 'layak');
                },
                'questions as reviewed_revision_count' => function ($q) {
                    $q->where('telaah_status', 'revisi');
                }
            ]);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('status_kurikulum')) {
            $query->where('status_kurikulum', $request->status_kurikulum);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->class_room_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('subject', function ($s) use ($search) {
                        $s->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('teacher.user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Summary cards calculation
        $baseStats = ExamPackage::query();
        if ($request->filled('academic_year_id')) {
            $baseStats->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('semester')) {
            $baseStats->where('semester', $request->semester);
        }

        $summary = [
            'total_packages' => (clone $baseStats)->count(),
            'draft' => (clone $baseStats)->where('status_kurikulum', 'draft')->count(),
            'submitted' => (clone $baseStats)->where('status_kurikulum', 'submitted')->count(),
            'approved' => (clone $baseStats)->where('status_kurikulum', 'approved')->count(),
            'revision_needed' => (clone $baseStats)->where('status_kurikulum', 'revision_needed')->count(),
        ];

        $packages = $query->orderByRaw("FIELD(status_kurikulum, 'submitted', 'revision_needed', 'draft', 'approved')")
            ->orderBy('updated_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'data' => $packages
        ]);
    }

    /**
     * Detail exam package for review.
     */
    public function show($id)
    {
        $package = ExamPackage::with([
            'classRoom',
            'subject',
            'academicYear',
            'teacher.user',
            'reviewedBy',
            'questions' => function ($q) {
                $q->orderBy('question_number', 'asc');
            }
        ])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $package
        ]);
    }

    /**
     * Save question review / telaah item by Kurikulum.
     */
    public function reviewQuestion(Request $request, $packageId, $questionId)
    {
        $package = ExamPackage::findOrFail($packageId);
        $question = ExamQuestion::where('exam_package_id', $package->id)->findOrFail($questionId);

        $validated = $request->validate([
            'telaah_status' => 'required|string|in:layak,revisi,tolak',
            'telaah_notes' => 'nullable|string|max:1000',
            'telaah_materi' => 'nullable|array',
            'telaah_konstruksi' => 'nullable|array',
            'telaah_bahasa' => 'nullable|array',
        ]);

        $question->update([
            'telaah_status' => $validated['telaah_status'],
            'telaah_notes' => $validated['telaah_notes'] ?? null,
            'telaah_materi' => $validated['telaah_materi'] ?? $question->telaah_materi,
            'telaah_konstruksi' => $validated['telaah_konstruksi'] ?? $question->telaah_konstruksi,
            'telaah_bahasa' => $validated['telaah_bahasa'] ?? $question->telaah_bahasa,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Hasil telaah soal nomor {$question->question_number} berhasil disimpan.",
            'data' => $question
        ]);
    }

    /**
     * Submit overall decision by Kurikulum (Approve or Request Revision).
     */
    public function submitDecision(Request $request, $packageId)
    {
        $package = ExamPackage::with('questions')->findOrFail($packageId);

        $validated = $request->validate([
            'status_kurikulum' => 'required|string|in:approved,revision_needed',
            'review_notes' => 'nullable|string|max:2000',
        ]);

        if ($validated['status_kurikulum'] === 'revision_needed' && empty($validated['review_notes'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Harap berikan catatan revisi agar guru dapat mengetahui bagian mana yang perlu diperbaiki.'
            ], 422);
        }

        $package->update([
            'status_kurikulum' => $validated['status_kurikulum'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            // When approved by kurikulum, package is ready for students/exam
            'status' => $validated['status_kurikulum'] === 'approved' ? 'active' : 'draft',
        ]);

        $statusMsg = $validated['status_kurikulum'] === 'approved'
            ? 'Paket soal berhasil disetujui (Approved) dan siap dicetak/diujikan.'
            : 'Paket soal dikembalikan ke guru pengampu untuk direvisi sesuai catatan telaah.';

        return response()->json([
            'status' => 'success',
            'message' => $statusMsg,
            'data' => $package->fresh(['reviewedBy', 'teacher.user', 'subject', 'classRoom'])
        ]);
    }

    /**
     * Print Ready: Naskah Soal Siswa (1 or 2 columns paper-saving).
     */
    public function printNaskah(Request $request, $packageId)
    {
        $package = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher.user', 'questions' => function ($q) {
            $q->orderBy('question_number', 'asc');
        }])->findOrFail($packageId);

        $school = SchoolSetting::getSetting();
        $layout = $request->query('layout', '2col'); // '1col' or '2col'
        $showKeys = $request->boolean('show_keys', false);

        $allQuestions = $package->questions;
        $pgQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'pg');
        $pgComplexQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'pg_complex');
        $trueFalseQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'true_false');
        $matchingQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'matching');
        $shortAnswerQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'short_answer');
        $essayQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'essay');

        return view('prints.exam_naskah', compact(
            'package', 'school', 'layout', 'showKeys', 'allQuestions',
            'pgQuestions', 'pgComplexQuestions', 'trueFalseQuestions',
            'matchingQuestions', 'shortAnswerQuestions', 'essayQuestions'
        ));
    }

    /**
     * Print Ready: Kisi-Kisi Soal Resmi Standar Kemenag.
     */
    public function printKisiKisi(Request $request, $packageId)
    {
        $package = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher.user', 'reviewedBy', 'questions' => function ($q) {
            $q->orderBy('question_number', 'asc');
        }])->findOrFail($packageId);

        $school = SchoolSetting::getSetting();

        return view('prints.exam_kisi_kisi', compact('package', 'school'));
    }

    /**
     * Print Ready: Kartu Soal Resmi.
     */
    public function printKartuSoal(Request $request, $packageId)
    {
        $package = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher.user', 'questions' => function ($q) {
            $q->orderBy('question_number', 'asc');
        }])->findOrFail($packageId);

        $school = SchoolSetting::getSetting();

        return view('prints.exam_kartu_soal', compact('package', 'school'));
    }

    /**
     * Print Ready: Instrumen & Berita Acara Telaah Soal.
     */
    public function printTelaah(Request $request, $packageId)
    {
        $package = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher.user', 'reviewedBy', 'questions' => function ($q) {
            $q->orderBy('question_number', 'asc');
        }])->findOrFail($packageId);

        $school = SchoolSetting::getSetting();

        return view('prints.exam_telaah', compact('package', 'school'));
    }

    /**
     * Print Ready: Kunci Jawaban & Rubrik Penskoran.
     */
    public function printKunci(Request $request, $packageId)
    {
        $package = ExamPackage::with(['classRoom', 'subject', 'academicYear', 'teacher.user', 'questions' => function ($q) {
            $q->orderBy('question_number', 'asc');
        }])->findOrFail($packageId);

        $school = SchoolSetting::getSetting();
        $allQuestions = $package->questions;
        $pgQuestions = $allQuestions->filter(fn($q) => in_array($q->question_type, ['pg', 'pg_complex']));
        $trueFalseQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'true_false');
        $matchingQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'matching');
        $shortAnswerQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'short_answer');
        $essayQuestions = $allQuestions->filter(fn($q) => $q->question_type === 'essay');

        return view('prints.exam_kunci', compact(
            'package', 'school', 'allQuestions',
            'pgQuestions', 'trueFalseQuestions', 'matchingQuestions',
            'shortAnswerQuestions', 'essayQuestions'
        ));
    }
}
