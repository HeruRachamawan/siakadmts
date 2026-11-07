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
            $table->string('status_kurikulum', 30)->default('draft')->after('status'); // draft, submitted, revision_needed, approved
            $table->text('review_notes')->nullable()->after('status_kurikulum');
            $table->foreignId('reviewed_by')->nullable()->after('review_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->string('academic_phase', 20)->default('Fase D')->after('semester'); // Kurikulum Merdeka (Fase D untuk MTs/SMP)
            $table->string('curriculum_type', 20)->default('merdeka')->after('academic_phase'); // merdeka, k13
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->longText('question_text')->nullable()->after('question_type');
            $table->string('question_image', 255)->nullable()->after('question_text');
            $table->json('options')->nullable()->after('question_image'); // {"A": "...", "B": "...", "C": "...", "D": "..."}
            $table->string('material', 255)->nullable()->after('correct_answer'); // Materi Pokok / Lingkup Materi
            $table->text('basic_competency')->nullable()->after('material'); // Capaian Pembelajaran / Kompetensi Dasar (CP / KD)
            $table->text('indicator')->nullable()->after('basic_competency'); // Indikator Soal untuk Kisi-Kisi
            $table->string('cognitive_level', 10)->default('L1')->after('indicator'); // L1 (Pengetahuan/Pemahaman), L2 (Aplikasi), L3 (Penalaran/HOTS)
            $table->string('difficulty_level', 20)->default('sedang')->after('cognitive_level'); // mudah, sedang, sukar
            $table->text('rubric')->nullable()->after('difficulty_level'); // Pedoman penskoran essay
            $table->json('telaah_materi')->nullable()->after('rubric'); // Checklist aspek materi
            $table->json('telaah_konstruksi')->nullable()->after('telaah_materi'); // Checklist aspek konstruksi
            $table->json('telaah_bahasa')->nullable()->after('telaah_konstruksi'); // Checklist aspek bahasa
            $table->string('telaah_status', 20)->default('layak')->after('telaah_bahasa'); // layak, revisi, tolak
            $table->text('telaah_notes')->nullable()->after('telaah_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn([
                'question_text',
                'question_image',
                'options',
                'material',
                'basic_competency',
                'indicator',
                'cognitive_level',
                'difficulty_level',
                'rubric',
                'telaah_materi',
                'telaah_konstruksi',
                'telaah_bahasa',
                'telaah_status',
                'telaah_notes',
            ]);
        });

        Schema::table('exam_packages', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'status_kurikulum',
                'review_notes',
                'reviewed_by',
                'reviewed_at',
                'academic_phase',
                'curriculum_type',
            ]);
        });
    }
};
