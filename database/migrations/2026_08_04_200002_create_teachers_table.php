<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('nip')->unique();
            $table->string('full_name');
            $table->enum('gender', ['L', 'P']);
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'nip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
