<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teachers') && !Schema::hasColumn('teachers', 'position')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->string('position')->nullable()->after('phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'position')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropColumn('position');
            });
        }
    }
};
