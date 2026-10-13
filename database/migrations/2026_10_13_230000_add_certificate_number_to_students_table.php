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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'certificate_number')) {
                $table->string('certificate_number', 150)->nullable()->after('photo');
            }
            if (!Schema::hasColumn('students', 'skl_number')) {
                $table->string('skl_number', 150)->nullable()->after('certificate_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'certificate_number')) {
                $table->dropColumn('certificate_number');
            }
            if (Schema::hasColumn('students', 'skl_number')) {
                $table->dropColumn('skl_number');
            }
        });
    }
};
