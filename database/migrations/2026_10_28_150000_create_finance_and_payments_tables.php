<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Pos Pembayaran / Jenis Tagihan
        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('name'); // e.g. "SPP Bulanan", "Uang Gedung", "Seragam"
            $table->enum('type', ['monthly', 'once'])->default('monthly');
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tagihan Per Siswa
        Schema::create('student_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained('payment_types')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('month', 20)->nullable(); // 'Juli', 'Agustus', ..., 'Juni'
            $table->unsignedTinyInteger('month_order')->default(1); // 1 = Juli, 12 = Juni
            $table->unsignedSmallInteger('year')->nullable(); // e.g. 2026
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'payment_type_id', 'academic_year_id', 'month'], 'student_bill_month_unique');
        });

        // 3. Riwayat Transaksi Kasir & Kwitansi
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('student_bill_id')->nullable()->constrained('student_bills')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('receiver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount_paid', 12, 2);
            $table->enum('payment_method', ['cash', 'transfer', 'qris'])->default('cash');
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('student_bills');
        Schema::dropIfExists('payment_types');
    }
};
