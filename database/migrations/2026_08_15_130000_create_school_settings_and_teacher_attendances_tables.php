<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('school_settings')) {
            Schema::create('school_settings', function (Blueprint $table) {
                $table->id();
                $table->string('school_name')->default('YASPIN');
                $table->decimal('latitude', 10, 8)->nullable()->default(-6.20880000);
                $table->decimal('longitude', 11, 8)->nullable()->default(106.84560000);
                $table->integer('max_radius_meters')->default(100);
                $table->time('work_start_time')->default('07:00:00');
                $table->time('work_late_time')->default('07:15:00');
                $table->time('work_end_time')->default('15:00:00');
                $table->timestamps();
            });

            // Insert initial default setting
            DB::table('school_settings')->insert([
                'school_name' => 'SMK YASPIN',
                'latitude' => -6.20880000,
                'longitude' => 106.84560000,
                'max_radius_meters' => 100,
                'work_start_time' => '07:00:00',
                'work_late_time' => '07:15:00',
                'work_end_time' => '15:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('teacher_attendances')) {
            Schema::create('teacher_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
                $table->date('date');
                $table->time('check_in_time')->nullable();
                $table->decimal('check_in_latitude', 10, 8)->nullable();
                $table->decimal('check_in_longitude', 11, 8)->nullable();
                $table->integer('check_in_distance_meters')->nullable();

                $table->time('check_out_time')->nullable();
                $table->decimal('check_out_latitude', 10, 8)->nullable();
                $table->decimal('check_out_longitude', 11, 8)->nullable();
                $table->integer('check_out_distance_meters')->nullable();

                $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'tugas_luar'])->default('hadir');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['teacher_id', 'date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
        Schema::dropIfExists('school_settings');
    }
};
