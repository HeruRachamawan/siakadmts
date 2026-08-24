<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('id');
            $table->date('end_date')->nullable()->after('start_date');
        });

        // Copy existing 'date' values to start_date and end_date if 'date' column exists
        if (Schema::hasColumn('calendar_events', 'date')) {
            DB::statement("UPDATE calendar_events SET start_date = date, end_date = date WHERE start_date IS NULL");
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->dropColumn('date');
            });
        }
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->date('date')->nullable();
        });

        DB::statement("UPDATE calendar_events SET date = start_date WHERE date IS NULL");

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
