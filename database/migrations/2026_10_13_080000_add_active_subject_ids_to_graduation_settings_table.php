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
            $table->json('active_subject_ids')->nullable()->after('practice_weight_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduation_settings', function (Blueprint $table) {
            $table->dropColumn('active_subject_ids');
        });
    }
};
