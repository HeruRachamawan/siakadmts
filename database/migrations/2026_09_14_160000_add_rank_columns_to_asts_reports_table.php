<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asts_reports', function (Blueprint $table) {
            $table->integer('manual_rank')->nullable()->after('unexcused_count');
            $table->integer('calculated_rank')->nullable()->after('manual_rank');
        });
    }

    public function down(): void
    {
        Schema::table('asts_reports', function (Blueprint $table) {
            $table->dropColumn(['manual_rank', 'calculated_rank']);
        });
    }
};
