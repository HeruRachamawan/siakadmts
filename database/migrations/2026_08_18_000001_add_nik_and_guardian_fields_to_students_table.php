<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'nik')) {
                $table->string('nik', 20)->nullable()->after('nisn');
            }

            if (!Schema::hasColumn('students', 'guardian_name')) {
                $table->string('guardian_name')->nullable()->after('father_income');
                $table->string('guardian_relation')->nullable()->after('guardian_name');
                $table->string('guardian_nik', 20)->nullable()->after('guardian_relation');
                $table->string('guardian_job')->nullable()->after('guardian_nik');
                $table->string('guardian_phone', 30)->nullable()->after('guardian_job');
                $table->string('guardian_income')->nullable()->after('guardian_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('students', 'nik')) $cols[] = 'nik';
            if (Schema::hasColumn('students', 'guardian_name')) $cols[] = 'guardian_name';
            if (Schema::hasColumn('students', 'guardian_relation')) $cols[] = 'guardian_relation';
            if (Schema::hasColumn('students', 'guardian_nik')) $cols[] = 'guardian_nik';
            if (Schema::hasColumn('students', 'guardian_job')) $cols[] = 'guardian_job';
            if (Schema::hasColumn('students', 'guardian_phone')) $cols[] = 'guardian_phone';
            if (Schema::hasColumn('students', 'guardian_income')) $cols[] = 'guardian_income';

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
