<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('school_settings') && !Schema::hasColumn('school_settings', 'weekly_holidays')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->json('weekly_holidays')->nullable()->after('work_end_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('school_settings') && Schema::hasColumn('school_settings', 'weekly_holidays')) {
            Schema::table('school_settings', function (Blueprint $table) {
                $table->dropColumn('weekly_holidays');
            });
        }
    }
};
