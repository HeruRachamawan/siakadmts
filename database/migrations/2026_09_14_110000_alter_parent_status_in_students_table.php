<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change father_status and mother_status to VARCHAR(50) so they can store 'tidak_diketahui' and other values without ENUM truncation
        DB::statement("ALTER TABLE `students` MODIFY COLUMN `father_status` VARCHAR(50) NULL DEFAULT 'hidup'");
        DB::statement("ALTER TABLE `students` MODIFY COLUMN `mother_status` VARCHAR(50) NULL DEFAULT 'hidup'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `students` MODIFY COLUMN `father_status` ENUM('hidup', 'meninggal', 'pisah', 'lainnya') NULL DEFAULT 'hidup'");
        DB::statement("ALTER TABLE `students` MODIFY COLUMN `mother_status` ENUM('hidup', 'meninggal', 'pisah', 'lainnya') NULL DEFAULT 'hidup'");
    }
};
