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
        Schema::create('exam_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('class_room_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('title');
            $table->string('exam_type', 30)->default('uh'); // uh, sts, sas, pat, am, quiz
            $table->string('semester', 10)->default('ganjil'); // ganjil, genap
            $table->integer('total_questions')->default(20);
            $table->decimal('kkm', 5, 2)->default(75.00);
            $table->decimal('pg_weight', 5, 2)->default(70.00); // 70%
            $table->decimal('essay_weight', 5, 2)->default(30.00); // 30%
            $table->string('status', 20)->default('draft'); // draft, active, completed
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_package_id')->constrained('exam_packages')->onDelete('cascade');
            $table->integer('question_number');
            $table->string('question_type', 30)->default('pg'); // pg, pg_complex, true_false, matching, short_answer, essay
            $table->text('correct_answer')->nullable(); // e.g. "A", "A,C", "True", "Jakarta"
            $table->decimal('score_weight', 5, 2)->default(1.00);
            $table->timestamps();

            $table->unique(['exam_package_id', 'question_number']);
        });

        Schema::create('exam_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_package_id')->constrained('exam_packages')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->json('student_answers')->nullable(); // {"1": "A", "2": "B", ...}
            $table->json('essay_scores')->nullable(); // {"21": 10, "22": 8, ...}
            $table->integer('correct_pg_count')->default(0);
            $table->integer('wrong_pg_count')->default(0);
            $table->decimal('pg_score', 5, 2)->default(0.00);
            $table->decimal('essay_score', 5, 2)->default(0.00);
            $table->decimal('total_score', 5, 2)->default(0.00);
            $table->boolean('is_passed')->default(false);
            $table->timestamps();

            $table->unique(['exam_package_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_submissions');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exam_packages');
    }
};
