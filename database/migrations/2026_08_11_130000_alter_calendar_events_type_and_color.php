<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First drop the old type column which is an enum
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // Add the new free-text type (category) and color columns
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->string('type')->default('Kegiatan Khusus');
            $table->string('color', 20)->default('violet-500');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['type', 'color']);
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->enum('type', ['exam', 'holiday', 'event'])->default('event');
        });
    }
};
