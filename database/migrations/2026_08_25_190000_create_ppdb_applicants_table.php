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
        Schema::create('ppdb_applicants', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            
            // Student Biodata
            $table->string('full_name');
            $table->string('nisn', 20)->nullable()->index();
            $table->string('nik', 20)->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('previous_school')->nullable(); // SD/MI Asal
            
            // Parent & Guardian Information
            $table->string('father_name')->nullable();
            $table->string('father_job')->nullable();
            $table->string('father_phone', 30)->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_job')->nullable();
            $table->string('mother_phone', 30)->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 30)->nullable();
            
            // Document Attachments
            $table->string('photo')->nullable();
            $table->string('family_card_file')->nullable();
            $table->string('certificate_file')->nullable();
            
            // Status & Verification Workflow
            // pending: baru mendaftar
            // verified: berkas diverifikasi panitia
            // accepted: lulus seleksi / diterima
            // rejected: tidak lulus / ditolak
            // enrolled: sudah didaftar-ulangkan ke data siswa aktif
            $table->enum('status', ['pending', 'verified', 'accepted', 'rejected', 'enrolled'])->default('pending')->index();
            $table->decimal('test_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Enrolled Reference
            $table->foreignId('enrolled_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('enrolled_class_id')->nullable()->constrained('classes')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_applicants');
    }
};
