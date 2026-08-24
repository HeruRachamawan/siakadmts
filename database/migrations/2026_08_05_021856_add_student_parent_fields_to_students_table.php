<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('mother_name')->nullable()->after('parent_phone');
            $table->enum('mother_status', ['hidup', 'meninggal', 'pisah', 'lainnya'])->nullable()->after('mother_name');
            $table->string('mother_nik')->nullable()->after('mother_status');
            $table->string('mother_job')->nullable()->after('mother_nik');
            $table->string('mother_income')->nullable()->after('mother_job');

            $table->string('father_name')->nullable()->after('mother_income');
            $table->enum('father_status', ['hidup', 'meninggal', 'pisah', 'lainnya'])->nullable()->after('father_name');
            $table->string('father_nik')->nullable()->after('father_status');
            $table->string('father_job')->nullable()->after('father_nik');
            $table->string('father_income')->nullable()->after('father_job');

            $table->string('previous_school')->nullable()->after('father_income');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'mother_name', 'mother_status', 'mother_nik', 'mother_job', 'mother_income',
                'father_name', 'father_status', 'father_nik', 'father_job', 'father_income',
                'previous_school',
            ]);
        });
    }
};
