<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\PaymentTransaction;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceController extends Controller
{
    /**
     * Get financial dashboard statistics & summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $ayId = $this->resolveAcademicYearId($request);

        $now = now();
        $thisMonthIncome = PaymentTransaction::whereMonth('payment_date', $now->month)
            ->whereYear('payment_date', $now->year)
            ->sum('amount_paid');

        $totalYearIncome = PaymentTransaction::where('academic_year_id', $ayId)->sum('amount_paid');

        $totalUnpaidAmount = StudentBill::where('academic_year_id', $ayId)
            ->where('status', '!=', 'paid')
            ->sum(DB::raw('amount - amount_paid'));

        $totalTransactionsCount = PaymentTransaction::where('academic_year_id', $ayId)->count();

        // Recent 6 transactions
        $recentTransactions = PaymentTransaction::with(['student:id,full_name,nisn,class_id', 'student.classRoom:id,name', 'studentBill.paymentType:id,name'])
            ->where('academic_year_id', $ayId)
            ->latest('id')
            ->limit(6)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'this_month_income' => (float) $thisMonthIncome,
                'total_year_income' => (float) $totalYearIncome,
                'total_unpaid_amount' => (float) $totalUnpaidAmount,
                'total_transactions_count' => $totalTransactionsCount,
                'recent_transactions' => $recentTransactions,
            ],
        ]);
    }

    /**
     * Get list of Payment Types (Pos Pembayaran).
     */
    public function getPaymentTypes(Request $request): JsonResponse
    {
        $ayId = $this->resolveAcademicYearId($request);

        $types = PaymentType::where('academic_year_id', $ayId)
            ->orWhereNull('academic_year_id')
            ->withCount('bills')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $types,
        ]);
    }

    /**
     * Store new Payment Type.
     */
    public function storePaymentType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:monthly,once',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $ayId = $validated['academic_year_id'] ?? $this->resolveAcademicYearId($request);
        $validated['academic_year_id'] = $ayId;

        $paymentType = PaymentType::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Pos tagihan berhasil ditambahkan',
            'data' => $paymentType,
        ]);
    }

    /**
     * Update Payment Type.
     */
    public function updatePaymentType(Request $request, $id): JsonResponse
    {
        $paymentType = PaymentType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:monthly,once',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $paymentType->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Pos tagihan berhasil diperbarui',
            'data' => $paymentType,
        ]);
    }

    /**
     * Delete Payment Type.
     */
    public function deletePaymentType($id): JsonResponse
    {
        $paymentType = PaymentType::findOrFail($id);
        
        // Cek jika sudah memiliki tagihan yang terbayar
        $hasPaidBills = StudentBill::where('payment_type_id', $id)
            ->where('amount_paid', '>', 0)
            ->exists();

        if ($hasPaidBills) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pos tagihan tidak dapat dihapus karena sudah ada siswa yang melakukan pembayaran.',
            ], 422);
        }

        $paymentType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pos tagihan berhasil dihapus',
        ]);
    }

    /**
     * Generate bills massal untuk kelas tertentu atau seluruh siswa aktif.
     */
    public function generateBills(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_type_id' => 'required|exists:payment_types,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'class_id' => 'nullable|exists:classes,id',
            'due_date' => 'nullable|date',
        ]);

        $ayId = $validated['academic_year_id'] ?? $this->resolveAcademicYearId($request);
        $paymentType = PaymentType::findOrFail($validated['payment_type_id']);

        // Query siswa aktif
        $studentQuery = Student::where('status', 'aktif');
        if (!empty($validated['class_id'])) {
            $studentQuery->where('class_id', $validated['class_id']);
        }

        $students = $studentQuery->get(['id', 'class_id']);
        if ($students->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ditemukan siswa aktif untuk digenerate tagihan.',
            ], 422);
        }

        $months = [
            1 => 'Juli',
            2 => 'Agustus',
            3 => 'September',
            4 => 'Oktober',
            5 => 'November',
            6 => 'Desember',
            7 => 'Januari',
            8 => 'Februari',
            9 => 'Maret',
            10 => 'April',
            11 => 'Mei',
            12 => 'Juni',
        ];

        $currentYear = (int) date('Y');
        $createdCount = 0;

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                if ($paymentType->type === 'monthly') {
                    foreach ($months as $order => $monthName) {
                        // Semester 1 (Juli-Des) = Tahun ajaran awal, Semester 2 (Jan-Jun) = Tahun berikutnya
                        $calYear = ($order >= 7) ? ($currentYear + 1) : $currentYear;

                        $bill = StudentBill::firstOrCreate(
                            [
                                'student_id' => $student->id,
                                'payment_type_id' => $paymentType->id,
                                'academic_year_id' => $ayId,
                                'month' => $monthName,
                            ],
                            [
                                'month_order' => $order,
                                'year' => $calYear,
                                'amount' => $paymentType->amount,
                                'amount_paid' => 0,
                                'status' => 'unpaid',
                                'due_date' => $validated['due_date'] ?? null,
                            ]
                        );

                        if ($bill->wasRecentlyCreated) {
                            $createdCount++;
                        }
                    }
                } else {
                    // Type 'once' (Sekali bayar e.g. Uang Gedung)
                    $bill = StudentBill::firstOrCreate(
                        [
                            'student_id' => $student->id,
                            'payment_type_id' => $paymentType->id,
                            'academic_year_id' => $ayId,
                            'month' => null,
                        ],
                        [
                            'month_order' => 1,
                            'year' => $currentYear,
                            'amount' => $paymentType->amount,
                            'amount_paid' => 0,
                            'status' => 'unpaid',
                            'due_date' => $validated['due_date'] ?? null,
                        ]
                    );

                    if ($bill->wasRecentlyCreated) {
                        $createdCount++;
                    }
                }
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil memproses tagihan! {$createdCount} data tagihan baru berhasil dibuat.",
                'data' => [
                    'created_count' => $createdCount,
                    'total_students' => $students->count(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat tagihan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student payment card (Buku Kendali 12 Bulan SPP & Tagihan Lainnya).
     */
    public function getStudentCard(Request $request, $studentId): JsonResponse
    {
        $ayId = $this->resolveAcademicYearId($request);
        $student = Student::with(['classRoom:id,name,grade_level'])->findOrFail($studentId);

        // Ambil seluruh tagihan siswa di tahun ajaran ini
        $bills = StudentBill::with(['paymentType'])
            ->where('student_id', $studentId)
            ->where('academic_year_id', $ayId)
            ->orderBy('month_order', 'asc')
            ->get();

        // Pisahkan menjadi Monthly Bills (SPP) dan Once Bills (Gedung/Seragam/dsb)
        $monthlyBills = $bills->filter(fn ($b) => $b->paymentType && $b->paymentType->type === 'monthly')->values();
        $onceBills = $bills->filter(fn ($b) => $b->paymentType && $b->paymentType->type === 'once')->values();

        $totalBilled = $bills->sum('amount');
        $totalPaid = $bills->sum('amount_paid');
        $totalRemaining = max(0, $totalBilled - $totalPaid);

        // Riwayat transaksi siswa
        $transactions = PaymentTransaction::with(['receiver:id,name', 'studentBill.paymentType:id,name'])
            ->where('student_id', $studentId)
            ->where('academic_year_id', $ayId)
            ->latest('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'student' => $student,
                'monthly_bills' => $monthlyBills,
                'once_bills' => $onceBills,
                'total_billed' => (float) $totalBilled,
                'total_paid' => (float) $totalPaid,
                'total_remaining' => (float) $totalRemaining,
                'transactions' => $transactions,
            ],
        ]);
    }

    /**
     * Bayar tagihan siswa (Proses kasir & generate kwitansi invoice).
     */
    public function payBills(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'bill_ids' => 'required|array|min:1',
            'bill_ids.*' => 'exists:student_bills,id',
            'payment_method' => 'required|in:cash,transfer,qris',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $ayId = $this->resolveAcademicYearId($request);
        $student = Student::with('classRoom')->findOrFail($validated['student_id']);
        $receiverUserId = auth()->id();

        // Generate satu nomor invoice bersama untuk transaksi ini
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $createdTransactions = [];
        $totalPaidInSession = 0;

        DB::beginTransaction();
        try {
            foreach ($validated['bill_ids'] as $billId) {
                $bill = StudentBill::with('paymentType')->where('student_id', $student->id)->findOrFail($billId);

                $remaining = (float) $bill->amount - (float) $bill->amount_paid;
                if ($remaining <= 0) {
                    continue; // sudah lunas
                }

                // Update bill
                $bill->amount_paid += $remaining;
                $bill->status = 'paid';
                $bill->save();

                // Create transaction
                $trx = PaymentTransaction::create([
                    'invoice_number' => $invoiceNumber,
                    'student_id' => $student->id,
                    'student_bill_id' => $bill->id,
                    'academic_year_id' => $ayId,
                    'receiver_user_id' => $receiverUserId,
                    'amount_paid' => $remaining,
                    'payment_method' => $validated['payment_method'],
                    'payment_date' => $validated['payment_date'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                $totalPaidInSession += $remaining;
                $createdTransactions[] = [
                    'id' => $trx->id,
                    'item_name' => ($bill->paymentType?->name ?? 'Tagihan') . ($bill->month ? " - {$bill->month}" : ''),
                    'amount' => $remaining,
                ];
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran berhasil disimpan! Kwitansi siap dicetak.',
                'data' => [
                    'invoice_number' => $invoiceNumber,
                    'student' => $student,
                    'total_paid' => $totalPaidInSession,
                    'payment_method' => $validated['payment_method'],
                    'payment_date' => $validated['payment_date'],
                    'items' => $createdTransactions,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Receipt / Kwitansi data untuk cetak bukti pembayaran.
     */
    public function getReceipt($invoiceNumber): JsonResponse
    {
        $transactions = PaymentTransaction::with([
            'student:id,full_name,nisn,nis,class_id',
            'student.classRoom:id,name',
            'studentBill.paymentType:id,name',
            'receiver:id,name',
            'academicYear:id,year,semester',
        ])
        ->where('invoice_number', $invoiceNumber)
        ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor kwitansi tidak ditemukan.',
            ], 404);
        }

        $first = $transactions->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'invoice_number' => $invoiceNumber,
                'student' => $first->student,
                'academic_year' => $first->academicYear,
                'receiver' => $first->receiver,
                'payment_date' => $first->payment_date,
                'payment_method' => $first->payment_method,
                'total_paid' => (float) $transactions->sum('amount_paid'),
                'items' => $transactions->map(fn ($t) => [
                    'name' => ($t->studentBill?->paymentType?->name ?? 'Pembayaran') . ($t->studentBill?->month ? " Bulan {$t->studentBill->month}" : ''),
                    'amount' => (float) $t->amount_paid,
                ]),
            ],
        ]);
    }

    /**
     * Laporan Kas & Rekapitulasi Tunggakan per Rombel Kelas.
     */
    public function getReports(Request $request): JsonResponse
    {
        $ayId = $this->resolveAcademicYearId($request);
        $classId = $request->input('class_id');

        // Query data siswa per kelas beserta ringkasan tagihan
        $studentQuery = Student::with(['classRoom:id,name,grade_level'])
            ->where('status', 'aktif');

        if (!empty($classId)) {
            $studentQuery->where('class_id', $classId);
        }

        $students = $studentQuery->get()->map(function ($st) use ($ayId) {
            $bills = StudentBill::where('student_id', $st->id)
                ->where('academic_year_id', $ayId)
                ->get();

            $totalBilled = (float) $bills->sum('amount');
            $totalPaid = (float) $bills->sum('amount_paid');
            $unpaidCount = $bills->where('status', '!=', 'paid')->count();

            return [
                'id' => $st->id,
                'full_name' => $st->full_name,
                'nisn' => $st->nisn ?: '-',
                'class_name' => $st->classRoom?->name ?? '-',
                'total_billed' => $totalBilled,
                'total_paid' => $totalPaid,
                'remaining' => max(0, $totalBilled - $totalPaid),
                'unpaid_count' => $unpaidCount,
                'status' => ($totalBilled > 0 && $totalBilled <= $totalPaid) ? 'lunas' : 'menunggak',
            ];
        });

        $totalKelasBilled = $students->sum('total_billed');
        $totalKelasPaid = $students->sum('total_paid');
        $totalKelasRemaining = $students->sum('remaining');
        $lunasCount = $students->where('status', 'lunas')->count();
        $nunggakCount = $students->where('status', 'menunggak')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'students' => $students,
                'summary' => [
                    'total_students' => $students->count(),
                    'total_billed' => $totalKelasBilled,
                    'total_paid' => $totalKelasPaid,
                    'total_remaining' => $totalKelasRemaining,
                    'lunas_count' => $lunasCount,
                    'nunggak_count' => $nunggakCount,
                ],
            ],
        ]);
    }

    /**
     * Resolve academic_year_id from request or fall back to active.
     */
    protected function resolveAcademicYearId(Request $request): int
    {
        $id = $request->input('academic_year_id');
        if ($id && is_numeric($id)) {
            return (int) $id;
        }

        $active = AcademicYear::where('is_active', true)->first();
        if ($active) {
            return (int) $active->id;
        }

        return (int) (AcademicYear::latest('id')->value('id') ?? 1);
    }
}
