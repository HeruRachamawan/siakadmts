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
        Schema::table('exam_packages', function (Blueprint $table) {
            $table->string('target_grade', 10)->nullable()->after('class_room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_packages', function (Blueprint $table) {
            $table->dropColumn('target_grade');
        });
    }
};
