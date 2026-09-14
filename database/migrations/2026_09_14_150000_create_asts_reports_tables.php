<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asts_subject_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('semester', 10)->default('ganjil'); // ganjil, genap
            $table->decimal('score', 5, 2)->default(0.00);
            $table->decimal('kkm', 5, 2)->default(75.00);
            $table->string('predicate', 5)->nullable(); // A, B, C, D
            $table->text('description')->nullable();
            $table->foreignId('exam_package_id')->nullable()->constrained('exam_packages')->nullOnDelete();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'academic_year_id', 'semester'], 'asts_student_subject_year_sem_unique');
        });

        Schema::create('asts_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('semester', 10)->default('ganjil'); // ganjil, genap
            $table->text('homeroom_notes')->nullable();
            $table->integer('sick_count')->nullable();
            $table->integer('permission_count')->nullable();
            $table->integer('unexcused_count')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id', 'semester'], 'asts_report_student_year_sem_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asts_reports');
        Schema::dropIfExists('asts_subject_scores');
    }
};
