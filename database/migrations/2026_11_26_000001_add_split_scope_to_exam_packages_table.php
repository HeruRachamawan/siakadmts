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
        Schema::table('exam_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_packages', 'source_package_id')) {
                $table->unsignedBigInteger('source_package_id')->nullable()->after('id');
                $table->index('source_package_id');
            }
            if (!Schema::hasColumn('exam_packages', 'package_scope')) {
                $table->string('package_scope', 30)->default('bank_and_correction')->after('status');
                $table->index('package_scope');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_packages', function (Blueprint $table) {
            if (Schema::hasColumn('exam_packages', 'package_scope')) {
                $table->dropIndex(['package_scope']);
                $table->dropColumn('package_scope');
            }
            if (Schema::hasColumn('exam_packages', 'source_package_id')) {
                $table->dropIndex(['source_package_id']);
                $table->dropColumn('source_package_id');
            }
        });
    }
};
