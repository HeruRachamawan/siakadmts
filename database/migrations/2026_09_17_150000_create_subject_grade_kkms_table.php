<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_grade_kkms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->string('grade_level', 10); // '7', '8', '9'
            $table->decimal('kkm', 5, 2)->default(75.00);
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subject_id', 'grade_level', 'academic_year_id'], 'subject_grade_year_unique');
            $table->index(['grade_level', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_grade_kkms');
    }
};
