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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['incoming', 'outgoing'])->default('incoming');
            $table->string('agenda_number')->nullable()->index(); // e.g. AG-2026-0001
            $table->string('reference_number')->nullable()->index(); // Nomor surat resmi
            $table->string('sender')->nullable(); // Pengirim surat
            $table->string('recipient')->nullable(); // Penerima / Tujuan
            $table->string('subject'); // Perihal surat
            $table->date('letter_date'); // Tanggal surat
            $table->date('received_date')->nullable(); // Tanggal diterima (untuk surat masuk)
            $table->string('category')->default('Dinas'); // Dinas, Undangan, Edaran, Keterangan, Keputusan
            $table->string('file_path')->nullable(); // Path file scan PDF/JPG
            
            // Disposisi Kepala Madrasah
            $table->string('disposition_to')->nullable(); // Waka Kurikulum, Waka Kesiswaan, Wali Kelas, dll
            $table->text('disposition_notes')->nullable(); // Instruksi Kepala Madrasah
            $table->date('disposition_date')->nullable();
            
            $table->enum('status', ['pending', 'dispositioned', 'processed', 'archived'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete(); // Jika surat terkait siswa tertentu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
