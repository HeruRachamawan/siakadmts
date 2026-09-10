<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'no_kk')) {
                $table->string('no_kk', 20)->nullable()->after('nik');
            }
            if (!Schema::hasColumn('students', 'religion')) {
                $table->string('religion', 30)->default('Islam')->nullable()->after('birth_date');
            }
            if (!Schema::hasColumn('students', 'child_number')) {
                $table->unsignedSmallInteger('child_number')->nullable()->after('religion');
            }
            if (!Schema::hasColumn('students', 'siblings_count')) {
                $table->unsignedSmallInteger('siblings_count')->nullable()->after('child_number');
            }
            if (!Schema::hasColumn('students', 'hobby')) {
                $table->string('hobby', 100)->nullable()->after('siblings_count');
            }
            if (!Schema::hasColumn('students', 'aspiration')) {
                $table->string('aspiration', 100)->nullable()->after('hobby');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $cols = [];
            foreach (['no_kk', 'religion', 'child_number', 'siblings_count', 'hobby', 'aspiration'] as $c) {
                if (Schema::hasColumn('students', $c)) {
                    $cols[] = $c;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
