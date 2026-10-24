<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('status', 30)->default('aktif')->after('parent_phone'); // 'aktif', 'lulus', 'tinggal_kelas', 'mutasi', 'keluar'
            $table->string('graduation_year', 20)->nullable()->after('status'); // e.g. '2025/2026'
            $table->date('graduation_date')->nullable()->after('graduation_year');
            $table->text('alumni_notes')->nullable()->after('graduation_date');

            $table->index(['status', 'graduation_year']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['status', 'graduation_year']);
            $table->dropColumn(['status', 'graduation_year', 'graduation_date', 'alumni_notes']);
        });
    }
};
