<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grades') && !Schema::hasColumn('grades', 'custom_scores')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->json('custom_scores')->nullable()->after('score_uas');
            });
        }

        if (Schema::hasTable('subjects') && !Schema::hasColumn('subjects', 'passing_grade')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->integer('passing_grade')->default(75)->after('code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('grades') && Schema::hasColumn('grades', 'custom_scores')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->dropColumn('custom_scores');
            });
        }

        if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'passing_grade')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropColumn('passing_grade');
            });
        }
    }
};
