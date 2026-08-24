<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Tambahkan kolom-kolom yang mungkin belum ada
            if (!Schema::hasColumn('students', 'birth_place')) {
                $table->string('birth_place')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('students', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('birth_place');
            }
            if (!Schema::hasColumn('students', 'address')) {
                $table->text('address')->nullable()->after('birth_date');
            }
            if (!Schema::hasColumn('students', 'parent_phone')) {
                $table->string('parent_phone')->nullable()->after('address');
            }
            if (!Schema::hasColumn('students', 'previous_school')) {
                $table->string('previous_school')->nullable()->after('parent_phone');
            }
            if (!Schema::hasColumn('students', 'photo_url')) {
                $table->string('photo_url')->nullable()->after('previous_school');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['birth_place', 'birth_date', 'address', 'parent_phone', 'previous_school', 'photo_url']);
        });
    }
};
