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
        Schema::create('graduation_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('report_weight', 5, 2)->default(60.00); // Bobot Rata-rata Rapor (misal 60%)
            $table->decimal('exam_weight', 5, 2)->default(40.00);   // Bobot Nilai Ujian (misal 40%)
            $table->decimal('theory_weight_default', 5, 2)->default(50.00);   // Bobot Teori/Tulis Ujian (50%)
            $table->decimal('practice_weight_default', 5, 2)->default(50.00); // Bobot Praktik Ujian (50%)
            $table->json('practical_subject_ids')->nullable(); // ID mapel yang memiliki ujian praktik
            $table->json('custom_subject_weights')->nullable(); // Pengaturan bobot custom per mapel
            $table->string('date_place', 100)->default('Bogor'); // Kota titimangsa ijazah
            $table->date('graduation_date')->nullable(); // Tanggal Ijazah/Kelulusan
            $table->timestamps();
        });

        Schema::create('graduation_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->decimal('sem_1', 5, 2)->nullable(); // Semester 1 (Kls 7 Ganjil)
            $table->decimal('sem_2', 5, 2)->nullable(); // Semester 2 (Kls 7 Genap)
            $table->decimal('sem_3', 5, 2)->nullable(); // Semester 3 (Kls 8 Ganjil)
            $table->decimal('sem_4', 5, 2)->nullable(); // Semester 4 (Kls 8 Genap)
            $table->decimal('sem_5', 5, 2)->nullable(); // Semester 5 (Kls 9 Ganjil)
            $table->decimal('sem_6', 5, 2)->nullable(); // Semester 6 (Kls 9 Genap)
            $table->decimal('avg_semester', 5, 2)->nullable(); // Rata-rata 6 semester
            $table->decimal('exam_theory', 5, 2)->nullable();   // Nilai Ujian Tulis
            $table->decimal('exam_practice', 5, 2)->nullable(); // Nilai Ujian Praktik
            $table->decimal('final_exam_score', 5, 2)->nullable(); // Nilai Ujian Madrasah (Kombinasi Tulis & Praktik)
            $table->decimal('final_grade', 5, 2)->nullable();      // Nilai Akhir Ijazah / PDUM
            $table->timestamps();

            $table->unique(['student_id', 'subject_id'], 'grad_student_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduation_grades');
        Schema::dropIfExists('graduation_settings');
    }
};
