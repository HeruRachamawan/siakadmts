<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->decimal('score_assignment', 5, 2)->default(0);
            $table->decimal('score_uts', 5, 2)->default(0);
            $table->decimal('score_uas', 5, 2)->default(0);
            $table->decimal('final_score', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'academic_year_id']);
            $table->index(['student_id', 'academic_year_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
