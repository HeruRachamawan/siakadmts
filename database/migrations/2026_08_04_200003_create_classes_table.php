<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('grade_level'); // e.g. 10
            $table->timestamps();

            $table->index(['academic_year_id', 'homeroom_teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
