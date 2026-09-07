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
        Schema::table('exam_submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_submissions', 'remedial_score')) {
                $table->decimal('remedial_score', 5, 2)->nullable()->after('total_score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('exam_submissions', 'remedial_score')) {
                $table->dropColumn('remedial_score');
            }
        });
    }
};
