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
        // 1. Master Ekstrakurikuler
        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete(); // Pembina utama
            $table->boolean('is_mandatory')->default(false); // True untuk Pramuka (Wajib bagi seluruh siswa)
            $table->string('schedule_day', 30)->nullable(); // Misal: Sabtu
            $table->string('schedule_time', 50)->nullable(); // Misal: 14:00 - 16:00
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Anggota Ekstrakurikuler
        Schema::create('extracurricular_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->date('joined_date')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['extracurricular_id', 'student_id', 'academic_year_id'], 'extracurricular_member_unique');
        });

        // 3. Penilaian Kategori Predikat (A, B, C) Ekstrakurikuler
        Schema::create('extracurricular_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('semester', 10); // 'ganjil' atau 'genap'
            $table->enum('grade', ['A', 'B', 'C'])->default('B'); // A (Sangat Baik), B (Baik), C (Cukup)
            $table->text('description')->nullable(); // Keterangan capaian perkembangan
            $table->foreignId('graded_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();

            $table->unique(['extracurricular_id', 'student_id', 'academic_year_id', 'semester'], 'extracurricular_grade_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_grades');
        Schema::dropIfExists('extracurricular_members');
        Schema::dropIfExists('extracurriculars');
    }
};
