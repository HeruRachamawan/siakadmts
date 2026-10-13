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
        Schema::table('graduation_settings', function (Blueprint $table) {
            $table->string('certificate_number_prefix', 150)->nullable()->default('MTs.10.01/PP.01.1/')->after('graduation_date');
            $table->string('principal_name', 150)->nullable()->after('certificate_number_prefix');
            $table->string('principal_nip', 50)->nullable()->after('principal_name');
            $table->string('nsm', 50)->nullable()->default('121232010012')->after('principal_nip');
            $table->string('npsn', 50)->nullable()->default('20277259')->after('nsm');
            $table->boolean('show_student_photo')->default(true)->after('npsn');
            $table->string('academic_year', 20)->default('2026/2027')->after('show_student_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduation_settings', function (Blueprint $table) {
            $table->dropColumn([
                'certificate_number_prefix',
                'principal_name',
                'principal_nip',
                'nsm',
                'npsn',
                'show_student_photo',
                'academic_year',
            ]);
        });
    }
};
