<?php

namespace App\Http\Controllers\Student;

use App\Models\AcademicYear;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Models\StudentBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentFinanceController extends StudentController
{
    /**
     * Get digital SPP card, bills summary, and transaction history for the logged-in student.
     */
    public function card(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        // Resolve active academic year
        $ayId = $request->input('academic_year_id');
        if (!$ayId || !is_numeric($ayId)) {
            $activeYear = AcademicYear::where('is_active', true)->first();
            $ayId = $activeYear?->id ?? AcademicYear::latest('id')->value('id') ?? 1;
        }

        $activeYear = AcademicYear::find($ayId);

        // Fetch student bills for this academic year
        $bills = StudentBill::with('paymentType')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ayId)
            ->get();

        $monthlyBills = $bills->filter(fn($b) => $b->type === 'monthly' || ($b->paymentType && $b->paymentType->type === 'monthly'))
            ->sortBy('month_order')
            ->values();

        $onceBills = $bills->filter(fn($b) => $b->type === 'once' || ($b->paymentType && $b->paymentType->type === 'once'))
            ->values();

        $academicMonths = [
            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
            5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
            9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni',
        ];

        // Format 12-month SPP matrix
        $formattedMonthly = [];
        foreach ($academicMonths as $order => $monthName) {
            $bill = $monthlyBills->firstWhere('month_order', $order);
            if ($bill) {
                $formattedMonthly[] = [
                    'id' => $bill->id,
                    'month_order' => $order,
                    'month' => $monthName,
                    'amount' => (float) $bill->amount,
                    'amount_paid' => (float) $bill->amount_paid,
                    'remaining' => (float) max(0, $bill->amount - $bill->amount_paid),
                    'status' => $bill->status,
                    'payment_type_name' => $bill->paymentType?->name ?? 'SPP Bulanan',
                ];
            } else {
                $formattedMonthly[] = [
                    'id' => null,
                    'month_order' => $order,
                    'month' => $monthName,
                    'amount' => 0,
                    'amount_paid' => 0,
                    'remaining' => 0,
                    'status' => 'unbilled',
                    'payment_type_name' => 'SPP Bulanan',
                ];
            }
        }

        $totalBilled = (float) $bills->sum('amount');
        $totalPaid = (float) $bills->sum('amount_paid');
        $totalRemaining = (float) $bills->sum(fn($b) => max(0, $b->amount - $b->amount_paid));

        // Riwayat transaksi pembayaran resmi siswa
        $transactions = PaymentTransaction::with(['paymentType:id,name', 'receiver:id,name'])
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ayId)
            ->latest('id')
            ->get()
            ->map(function ($tr) {
                return [
                    'id' => $tr->id,
                    'invoice_number' => $tr->invoice_number,
                    'payment_date' => $tr->payment_date ? $tr->payment_date->format('d/m/Y') : '-',
                    'amount_paid' => (float) $tr->amount_paid,
                    'payment_method' => strtoupper($tr->payment_method),
                    'payment_type_name' => $tr->paymentType?->name ?? 'Pembayaran SPP',
                    'receiver_name' => $tr->receiver?->name ?? 'Bendahara Madrasah',
                    'notes' => $tr->notes,
                ];
            });

        // Bank settings
        $settings = Setting::whereIn('key', [
            'school_bank_name',
            'school_bank_account',
            'school_bank_holder',
            'school_phone',
            'app_name',
        ])->pluck('value', 'key');

        return $this->success([
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'nisn' => $student->nisn,
                'nis' => $student->nis,
                'class_name' => $student->classRoom?->name ?? '-',
                'grade_level' => $student->classRoom?->grade_level ?? '-',
                'photo_url' => $student->photo_url,
            ],
            'academic_year' => [
                'id' => $activeYear?->id,
                'name' => $activeYear?->name ?? 'T.A. Berjalan',
            ],
            'monthly_bills' => $formattedMonthly,
            'once_bills' => $onceBills->map(function ($b) {
                return [
                    'id' => $b->id,
                    'payment_type_name' => $b->paymentType?->name ?? 'Tagihan Sekolah',
                    'amount' => (float) $b->amount,
                    'amount_paid' => (float) $b->amount_paid,
                    'remaining' => (float) max(0, $b->amount - $b->amount_paid),
                    'status' => $b->status,
                ];
            }),
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'total_remaining' => $totalRemaining,
            'transactions' => $transactions,
            'payment_info' => [
                'bank_name' => $settings['school_bank_name'] ?? 'Bank Syariah Indonesia (BSI)',
                'bank_account' => $settings['school_bank_account'] ?? '1234567890',
                'bank_holder' => $settings['school_bank_holder'] ?? 'MTs Al - Hasanah',
                'contact_phone' => $settings['school_phone'] ?? '',
                'school_name' => $settings['app_name'] ?? 'MTs Al - Hasanah',
            ],
        ]);
    }

    /**
     * Get official receipt data for printing by student/parent.
     */
    public function receipt(Request $request, string $invoiceNumber): JsonResponse
    {
        $student = $this->resolveStudent($request);

        $transactions = PaymentTransaction::with(['student.classRoom', 'paymentType', 'studentBill', 'receiver'])
            ->where('invoice_number', $invoiceNumber)
            ->where('student_id', $student->id)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bukti kwitansi tidak ditemukan atau bukan milik akun Anda.',
            ], 404);
        }

        $firstTr = $transactions->first();

        $items = $transactions->map(function ($tr) {
            $bill = $tr->studentBill;
            $monthInfo = ($bill && $bill->month) ? " (Bulan {$bill->month})" : '';
            return [
                'name' => ($tr->paymentType?->name ?? 'Pembayaran') . $monthInfo,
                'amount' => (float) $tr->amount_paid,
            ];
        });

        $totalPaid = (float) $transactions->sum('amount_paid');

        return response()->json([
            'status' => 'success',
            'data' => [
                'invoice_number' => $firstTr->invoice_number,
                'payment_date' => $firstTr->payment_date ? $firstTr->payment_date->format('d F Y') : now()->format('d F Y'),
                'payment_method' => strtoupper($firstTr->payment_method),
                'student' => [
                    'id' => $firstTr->student?->id,
                    'full_name' => $firstTr->student?->full_name,
                    'nisn' => $firstTr->student?->nisn,
                    'nis' => $firstTr->student?->nis,
                    'class_room' => [
                        'name' => $firstTr->student?->classRoom?->name ?? '-',
                    ],
                ],
                'receiver' => [
                    'name' => $firstTr->receiver?->name ?? 'Bendahara Madrasah',
                ],
                'items' => $items,
                'total_paid' => $totalPaid,
                'notes' => $firstTr->notes,
            ],
        ]);
    }
}
