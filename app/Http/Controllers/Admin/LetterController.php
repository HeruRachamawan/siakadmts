<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Letter;
use App\Models\Setting;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LetterController extends BaseController
{
    /**
     * Display a listing of letters (incoming or outgoing)
     */
    public function index(Request $request)
    {
        // Auto-heal legacy/mismatched records: ensure AG-SK (Surat Keluar) and AG-SM (Surat Masuk) are properly typed
        Letter::where('agenda_number', 'like', 'AG-SK-%')->where('type', '!=', 'outgoing')->update(['type' => 'outgoing']);
        Letter::where('agenda_number', 'like', 'AG-SM-%')->where('type', '!=', 'incoming')->update(['type' => 'incoming']);

        $query = Letter::with(['creator:id,name', 'student:id,full_name,nisn,nis,class_id', 'student.classRoom:id,name']);

        // Filter type
        if ($request->filled('type') && in_array($request->type, ['incoming', 'outgoing'])) {
            $query->where('type', $request->type);
        }

        // Filter status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('letter_date', [$request->start_date, $request->end_date]);
        }

        // Search query
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('reference_number', 'like', "%{$s}%")
                    ->orWhere('agenda_number', 'like', "%{$s}%")
                    ->orWhere('sender', 'like', "%{$s}%")
                    ->orWhere('recipient', 'like', "%{$s}%")
                    ->orWhere('subject', 'like', "%{$s}%")
                    ->orWhere('disposition_to', 'like', "%{$s}%");
            });
        }

        $sortDirection = $request->input('direction', 'asc');
        $letters = $query->orderBy('agenda_number', $sortDirection)->orderBy('id', $sortDirection)->paginate($request->input('per_page', 15));

        // Latest letters recorded for hint references
        $lastOutgoing = Letter::where('type', 'outgoing')->whereNotNull('reference_number')->where('reference_number', '!=', '')->orderByDesc('id')->first();
        $lastIncoming = Letter::where('type', 'incoming')->whereNotNull('reference_number')->where('reference_number', '!=', '')->orderByDesc('id')->first();
        $lastAgendaIncoming = Letter::where('type', 'incoming')->orderByDesc('id')->value('agenda_number');
        $lastAgendaOutgoing = Letter::where('type', 'outgoing')->orderByDesc('id')->value('agenda_number');

        // Aggregate statistics & hints
        $stats = [
            'total_incoming' => Letter::where('type', 'incoming')->count(),
            'total_outgoing' => Letter::where('type', 'outgoing')->count(),
            'pending_disposition' => Letter::where('type', 'incoming')->where('status', 'pending')->count(),
            'this_month_incoming' => Letter::where('type', 'incoming')->whereMonth('letter_date', Carbon::now()->month)->whereYear('letter_date', Carbon::now()->year)->count(),
            'this_month_outgoing' => Letter::where('type', 'outgoing')->whereMonth('letter_date', Carbon::now()->month)->whereYear('letter_date', Carbon::now()->year)->count(),
            'last_outgoing_number' => $lastOutgoing?->reference_number,
            'last_outgoing_subject' => $lastOutgoing?->subject,
            'last_outgoing_date' => $lastOutgoing?->letter_date ? Carbon::parse($lastOutgoing->letter_date)->translatedFormat('d F Y') : null,
            'last_incoming_number' => $lastIncoming?->reference_number,
            'last_incoming_subject' => $lastIncoming?->subject,
            'last_incoming_date' => $lastIncoming?->letter_date ? Carbon::parse($lastIncoming->letter_date)->translatedFormat('d F Y') : null,
            'last_agenda_incoming' => $lastAgendaIncoming,
            'last_agenda_outgoing' => $lastAgendaOutgoing,
        ];

        return $this->success([
            'letters' => $letters,
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created letter
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:incoming,outgoing'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'sender' => ['nullable', 'string', 'max:255'],
            'recipient' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:500'],
            'letter_date' => ['required', 'date'],
            'received_date' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // Max 10MB
            'disposition_to' => ['nullable', 'string', 'max:255'],
            'disposition_notes' => ['nullable', 'string'],
            'student_id' => ['nullable', 'exists:students,id'],
        ]);

        $year = Carbon::parse($request->letter_date)->year;
        $typePrefix = $request->type === 'incoming' ? 'SM' : 'SK';
        
        // Auto-generate unique annual Agenda Number (e.g. AG-SM-2026-0001)
        $countThisYear = Letter::where('type', $request->type)->whereYear('letter_date', $year)->count() + 1;
        $agendaNumber = sprintf('AG-%s-%s-%04d', $typePrefix, $year, $countThisYear);

        // Ensure uniqueness
        while (Letter::where('agenda_number', $agendaNumber)->exists()) {
            $countThisYear++;
            $agendaNumber = sprintf('AG-%s-%s-%04d', $typePrefix, $year, $countThisYear);
        }

        // Auto-generate outgoing reference number if empty (e.g. 001/MTs.AH/PP.00.5/VIII/2026)
        $refNumber = $request->reference_number;
        if ($request->type === 'outgoing' && empty($refNumber)) {
            $romanMonths = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            $monthRoman = $romanMonths[Carbon::parse($request->letter_date)->month] ?? 'I';
            $refNumber = sprintf('%03d/MTs.AH/PP.00.5/%s/%s', $countThisYear, $monthRoman, $year);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('letters/' . $request->type, 'public');
        }

        $status = 'pending';
        if ($request->filled('disposition_to') || $request->filled('disposition_notes')) {
            $status = 'dispositioned';
        }

        $letter = Letter::create([
            'type' => $request->type,
            'agenda_number' => $agendaNumber,
            'reference_number' => $refNumber,
            'sender' => $request->sender ?: ($request->type === 'outgoing' ? 'MTs Al-Hasanah' : 'Pengirim Eksternal'),
            'recipient' => $request->recipient ?: ($request->type === 'incoming' ? 'Kepala Madrasah' : 'Penerima Surat'),
            'subject' => $request->subject,
            'letter_date' => $request->letter_date,
            'received_date' => $request->received_date ?: ($request->type === 'incoming' ? Carbon::today()->toDateString() : null),
            'category' => $request->category ?: 'Dinas',
            'file_path' => $filePath,
            'disposition_to' => $request->disposition_to,
            'disposition_notes' => $request->disposition_notes,
            'disposition_date' => $request->filled('disposition_to') ? Carbon::today()->toDateString() : null,
            'status' => $status,
            'created_by' => auth()->id(),
            'student_id' => $request->student_id,
        ]);

        return $this->success($letter->load(['creator', 'student']), 'Data surat berhasil dicatat dalam buku agenda!');
    }

    /**
     * Display single letter details
     */
    public function show($id)
    {
        $letter = Letter::with(['creator', 'student', 'student.classRoom'])->findOrFail($id);
        return $this->success($letter);
    }

    /**
     * Update letter details
     */
    public function update(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);

        $request->validate([
            'reference_number' => ['nullable', 'string', 'max:255'],
            'sender' => ['nullable', 'string', 'max:255'],
            'recipient' => ['nullable', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:500'],
            'letter_date' => ['required', 'date'],
            'received_date' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,dispositioned,processed,archived'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'disposition_to' => ['nullable', 'string', 'max:255'],
            'disposition_notes' => ['nullable', 'string'],
        ]);

        $filePath = $letter->file_path;
        if ($request->hasFile('file')) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file')->store('letters/' . $letter->type, 'public');
        }

        $letter->update([
            'reference_number' => $request->reference_number ?: $letter->reference_number,
            'sender' => $request->sender ?: $letter->sender,
            'recipient' => $request->recipient ?: $letter->recipient,
            'subject' => $request->subject,
            'letter_date' => $request->letter_date,
            'received_date' => $request->received_date,
            'category' => $request->category ?: $letter->category,
            'status' => $request->status ?: $letter->status,
            'file_path' => $filePath,
            'disposition_to' => $request->disposition_to,
            'disposition_notes' => $request->disposition_notes,
            'disposition_date' => $request->filled('disposition_to') ? ($letter->disposition_date ?: Carbon::today()->toDateString()) : null,
        ]);

        return $this->success($letter->fresh(['creator', 'student']), 'Perubahan data surat berhasil disimpan.');
    }

    /**
     * Update disposition for incoming letter
     */
    public function updateDisposition(Request $request, $id)
    {
        $letter = Letter::findOrFail($id);

        $request->validate([
            'disposition_to' => ['required', 'string', 'max:255'],
            'disposition_notes' => ['required', 'string'],
        ]);

        $letter->update([
            'disposition_to' => $request->disposition_to,
            'disposition_notes' => $request->disposition_notes,
            'disposition_date' => Carbon::today()->toDateString(),
            'status' => 'dispositioned',
        ]);

        return $this->success($letter, 'Lembar disposisi surat berhasil diperbarui!');
    }

    /**
     * Generate official "Surat Keterangan Aktif Siswa" (One-Click Generator)
     */
    public function generateStudentCertificate(Request $request)
    {
        $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'purpose' => ['nullable', 'string', 'max:255'], // Keperluan surat
            'letter_date' => ['nullable', 'date'],
        ]);

        $student = Student::with('classRoom')->findOrFail($request->student_id);
        $settings = Setting::pluck('value', 'key')->all();
        
        $date = $request->letter_date ?: Carbon::today()->toDateString();
        $year = Carbon::parse($date)->year;
        $monthRoman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][Carbon::parse($date)->month];

        // Generate auto outgoing number
        $count = Letter::where('type', 'outgoing')->whereYear('letter_date', $year)->count() + 1;
        $refNumber = sprintf('%03d/MTs.AH/PP.00.5/%s/%s', $count, $monthRoman, $year);
        $agendaNumber = sprintf('AG-SK-%s-%04d', $year, $count);

        $purpose = $request->purpose ?: 'Persyaratan Beasiswa / Tunjangan Pendidikan';
        $subject = "Surat Keterangan Siswa Aktif - {$student->full_name}";

        // Record in outgoing letters
        $letter = Letter::create([
            'type' => 'outgoing',
            'agenda_number' => $agendaNumber,
            'reference_number' => $refNumber,
            'sender' => $settings['app_name'] ?? 'MTs Al-Hasanah',
            'recipient' => "Orang Tua / Wali dari {$student->full_name}",
            'subject' => $subject,
            'letter_date' => $date,
            'category' => 'Keterangan',
            'status' => 'processed',
            'created_by' => auth()->id(),
            'student_id' => $student->id,
            'disposition_notes' => "Diterbitkan untuk keperluan: {$purpose}",
        ]);

        return $this->success([
            'letter' => $letter,
            'student' => $student,
            'settings' => $settings,
            'purpose' => $purpose,
        ], 'Surat Keterangan Aktif Siswa berhasil diterbitkan dan dicatat dalam agenda surat keluar!');
    }

    /**
     * Delete letter record & physical file
     */
    public function destroy($id)
    {
        $letter = Letter::findOrFail($id);

        if ($letter->file_path && Storage::disk('public')->exists($letter->file_path)) {
            Storage::disk('public')->delete($letter->file_path);
        }

        $letter->delete();

        return $this->success(null, 'Surat berhasil dihapus dari buku agenda.');
    }
}
