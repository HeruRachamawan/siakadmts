<template>
  <div class="space-y-6 font-inter pb-12">
    <!-- Header Card -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 bg-teal-600 rounded-2xl flex items-center justify-center shadow-lg shadow-teal-500/20 flex-shrink-0">
          <CheckSquare class="w-6 h-6 text-white" />
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Koreksi Soal & Analisis Butir Soal</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Koreksi otomatis instan untuk Pilihan Ganda & Uraian, analisis kesukaran soal, serta sinkronisasi nilai ke rapor.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="!showCreateModal && !activeExam"
          @click="openCreateModal"
          class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-teal-600/20 flex items-center gap-2 cursor-pointer"
        >
          <PlusCircle class="w-4 h-4" />
          <span>Buat Paket Ujian Baru</span>
        </button>
      </div>
    </div>

    <!-- Overview Stats Bar (When not viewing an active exam) -->
    <div v-if="!activeExam" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
          <BookOpen class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Paket Ujian</span>
          <div class="text-lg font-black text-slate-800 font-lexend">{{ exams.length }} Paket</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
          <Award class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Nilai</span>
          <div class="text-lg font-black text-blue-600 font-lexend">{{ overallAvgScore }}</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa Tuntas</span>
          <div class="text-lg font-black text-emerald-600 font-lexend">{{ totalPassedStudents }} Siswa</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
          <AlertCircle class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Perlu Remedial</span>
          <div class="text-lg font-black text-rose-600 font-lexend">{{ totalRemedialStudents }} Siswa</div>
        </div>
      </div>
    </div>

    <!-- MAIN VIEW 1: Exam List Table -->
    <div v-if="!activeExam" class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-4">
      <!-- Filter Controls -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-100">
        <div class="flex items-center gap-2 flex-wrap">
          <select v-model="filterClass" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-teal-400">
            <option value="">Semua Kelas</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">
              Kelas {{ c.name }} ({{ c.students_count || 0 }} Siswa)
            </option>
          </select>

          <select v-model="filterSubject" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-teal-400">
            <option value="">Semua Mata Pelajaran</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>

          <select v-model="filterType" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-teal-400">
            <option value="">Semua Jenis Ujian</option>
            <option value="uh">Penilaian Harian (UH)</option>
            <option value="sts">Sumatif Tengah Semester (STS)</option>
            <option value="sas">Sumatif Akhir Semester (SAS)</option>
            <option value="pat">Penilaian Akhir Tahun (PAT)</option>
            <option value="am">Asesmen Madrasah (AM)</option>
            <option value="quiz">Kuis / Latihan</option>
          </select>
        </div>

        <div class="relative w-full md:w-64">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari judul ujian..."
            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-teal-400"
          />
          <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
        </div>
      </div>

      <!-- Exams Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
            <tr>
              <th class="px-4 py-3.5">Judul & Jenis Ujian</th>
              <th class="px-4 py-3.5">Mapel & Kelas</th>
              <th class="px-4 py-3.5 text-center">Jml Soal</th>
              <th class="px-4 py-3.5 text-center">KKM / Bobot</th>
              <th class="px-4 py-3.5 text-center">Progres Input</th>
              <th class="px-4 py-3.5 text-center">Rata-rata</th>
              <th class="px-4 py-3.5 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="exam in filteredExams" :key="exam.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-4 py-4">
                <div class="font-bold text-slate-800 text-sm font-lexend">{{ exam.title }}</div>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wide bg-teal-50 text-teal-700 border border-teal-100">
                    {{ examTypeLabel(exam.exam_type) }}
                  </span>
                  <span class="text-[10px] text-slate-400">Semester {{ exam.semester }}</span>
                </div>
              </td>
              <td class="px-4 py-4">
                <div class="font-bold text-slate-800">{{ exam.subject?.name || '-' }}</div>
                <div class="text-[11px] text-slate-400 font-medium">Kelas {{ exam.class_room?.name || '-' }}</div>
              </td>
              <td class="px-4 py-4 text-center font-bold text-slate-700">
                {{ exam.total_questions }} Soal
              </td>
              <td class="px-4 py-4 text-center">
                <div class="font-bold text-slate-800">KKM: {{ exam.kkm }}</div>
                <div class="text-[10px] text-slate-400">PG {{ exam.pg_weight }}% | Essay {{ exam.essay_weight }}%</div>
              </td>
              <td class="px-4 py-4 text-center">
                <span :class="exam.submissions_count > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'" class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold border">
                  {{ exam.submissions_count || 0 }} Siswa Terisi
                </span>
              </td>
              <td class="px-4 py-4 text-center">
                <span class="text-sm font-black font-lexend" :class="exam.avg_score >= exam.kkm ? 'text-emerald-600' : 'text-amber-600'">
                  {{ exam.avg_score || '0.00' }}
                </span>
              </td>
              <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    @click="openExamDetail(exam.id)"
                    class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer"
                  >
                    <Sliders class="w-3.5 h-3.5" />
                    <span>Buka & Koreksi</span>
                  </button>

                  <button
                    @click="downloadExcel(exam.id)"
                    title="Export Rekap Nilai Excel"
                    class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-colors cursor-pointer"
                  >
                    <Download class="w-4 h-4" />
                  </button>

                  <button
                    @click="confirmDelete(exam)"
                    title="Hapus Paket Ujian"
                    class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl transition-colors cursor-pointer"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredExams.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-slate-400 font-medium">
                Belum ada paket ujian yang dibuat. Silakan klik tombol "Buat Paket Ujian Baru" di atas.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MAIN VIEW 2: EXAM WORKSPACE (ACTIVE EXAM VIEW) -->
    <div v-if="activeExam" class="space-y-6">
      <!-- Active Exam Header Navigation -->
      <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <button
            @click="closeExamDetail"
            class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors cursor-pointer flex-shrink-0"
          >
            <ArrowLeft class="w-4 h-4" />
          </button>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-lg font-black text-slate-800 font-lexend">{{ activeExam.title }}</h2>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-teal-50 text-teal-700 border border-teal-100">
                {{ examTypeLabel(activeExam.exam_type) }}
              </span>
            </div>
            <p class="text-xs text-slate-400 font-medium">
              Mapel: <strong>{{ activeExam.subject?.name }}</strong> • Kelas: <strong>{{ activeExam.class_room?.name }}</strong> • KKM: <strong>{{ activeExam.kkm }}</strong> • {{ activeExam.total_questions }} Soal (PG {{ activeExam.pg_weight }}% | Essay {{ activeExam.essay_weight }}%)
            </p>
          </div>
        </div>

        <!-- Navigation Tabs & Actions -->
        <div class="flex items-center gap-2 flex-wrap">
          <div class="flex items-center gap-1.5 bg-slate-100 p-1.5 rounded-2xl">
            <button
              @click="activeTab = 'keys'"
              :class="activeTab === 'keys' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
              class="px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer flex items-center gap-1.5"
            >
              <KeyRound class="w-3.5 h-3.5" />
              <span>Kunci & Bobot</span>
            </button>
            <button
              @click="activeTab = 'grading'"
              :class="activeTab === 'grading' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
              class="px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer flex items-center gap-1.5"
            >
              <CheckSquare class="w-3.5 h-3.5" />
              <span>Koreksi Siswa ({{ activeStudents.length }})</span>
            </button>
            <button
              @click="fetchAnalysis"
              :class="activeTab === 'analysis' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
              class="px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer flex items-center gap-1.5"
            >
              <BarChart2 class="w-3.5 h-3.5" />
              <span>Analisis Butir Soal</span>
            </button>
            <button
              @click="activeTab = 'integration'"
              :class="activeTab === 'integration' ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
              class="px-4 py-2 rounded-xl text-xs font-black transition-all cursor-pointer flex items-center gap-1.5"
            >
              <Send class="w-3.5 h-3.5" />
              <span>Kirim Nilai & Ekspor</span>
            </button>
          </div>

          <button
            @click="openPrintPreview"
            type="button"
            class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-emerald-600/20 flex items-center gap-1.5 cursor-pointer flex-shrink-0"
            title="Cetak Lembar Rekap Capaian per Bentuk Soal (Konsep 1)"
          >
            <Printer class="w-4 h-4" />
            <span class="hidden sm:inline">Cetak Rekap (Print/PDF)</span>
            <span class="sm:hidden">Cetak</span>
          </button>
        </div>
      </div>

      <!-- SUB-TAB 1: KUNCI JAWABAN & BOBOT -->
      <div v-if="activeTab === 'keys'" class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-6">
        <!-- Quick String Input Bar -->
        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
            <div>
              <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">⚡ Input Kunci Jawaban Cepat (Deret Huruf PG Biasa)</label>
              <p class="text-xs text-slate-500 font-medium">Ketik atau paste deretan kunci jawaban pilihan ganda sekaligus (misal: <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-teal-700 font-mono font-bold">ABCDABCDAB...</code>). Soal kompleks/uraian tidak akan terpengaruh.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <button
                v-if="quickKeyInput.length > 0"
                type="button"
                @click="copyQuickKeysToClipboard"
                class="px-3 py-1 bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 rounded-xl text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                title="Salin deretan kunci PG bersih ke clipboard"
              >
                <Copy class="w-3.5 h-3.5" />
                <span>Salin Kunci PG</span>
              </button>
              <span class="text-xs font-mono font-bold px-3 py-1 rounded-xl bg-white border border-slate-200 text-teal-700">
                {{ quickKeyInput.length }} / {{ pgBiasaQuestionsCount }} Karakter PG Biasa
              </span>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <input
              v-model="quickKeyInput"
              type="text"
              :maxlength="pgBiasaQuestionsCount"
              :placeholder="pgBiasaQuestionsCount > 0 ? 'Contoh: ABCDEABCDA...' : 'Tidak ada butir soal PG biasa'"
              :disabled="pgBiasaQuestionsCount === 0"
              class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono font-bold text-slate-800 tracking-widest focus:ring-2 focus:ring-teal-400 uppercase disabled:opacity-50"
              @input="onQuickKeyInput"
            />
            <button
              @click="applyQuickKeys"
              :disabled="pgBiasaQuestionsCount === 0 || quickKeyInput.length === 0"
              class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs flex-shrink-0 transition-all shadow-sm cursor-pointer disabled:opacity-50"
            >
              Terapkan ke PG
            </button>
          </div>
        </div>

        <!-- Format & Question Types Info Bar (Menampilkan Format yang Dipilih Guru Sebelumnya) -->
        <div class="p-4 bg-white border border-slate-200/90 rounded-2xl shadow-xs space-y-3">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 flex-wrap">
              <span class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <CheckCircle2 class="w-4 h-4 text-teal-600" />
                <span>Format Soal Terpilih:</span>
              </span>
              <!-- Active types badges with counts according to teacher's creation -->
              <div class="flex items-center gap-1.5 flex-wrap">
                <span v-if="questionTypeSummary.pg > 0" class="px-2.5 py-1 rounded-xl bg-teal-50 text-teal-800 border border-teal-200 text-xs font-black flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                  {{ questionTypeSummary.pg }} PG Biasa
                </span>
                <span v-if="questionTypeSummary.pg_complex > 0" class="px-2.5 py-1 rounded-xl bg-purple-50 text-purple-800 border border-purple-200 text-xs font-black flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                  {{ questionTypeSummary.pg_complex }} PG Kompleks
                </span>
                <span v-if="questionTypeSummary.true_false > 0" class="px-2.5 py-1 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 text-xs font-black flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                  {{ questionTypeSummary.true_false }} Benar/Salah
                </span>
                <span v-if="questionTypeSummary.agree_disagree > 0" class="px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-800 border border-indigo-200 text-xs font-black flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                  {{ questionTypeSummary.agree_disagree }} Setuju/Tdk
                </span>
                <span v-if="questionTypeSummary.matching > 0" class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-black flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  {{ questionTypeSummary.matching }} Menjodohkan
                </span>
                <span v-if="questionTypeSummary.short_answer > 0" class="px-2.5 py-1 rounded-xl bg-blue-50 text-blue-800 border border-blue-200 text-xs font-black flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                  {{ questionTypeSummary.short_answer }} Isian Singkat
                </span>
                <span v-if="questionTypeSummary.essay > 0" class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-black flex items-center gap-1">
                  <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                  {{ questionTypeSummary.essay }} Uraian / Essay
                </span>
              </div>
            </div>

            <!-- Total and weights pill -->
            <div class="text-xs font-bold text-slate-600 flex items-center gap-2 flex-wrap">
              <span class="px-2.5 py-1 rounded-xl bg-teal-100/80 text-teal-800 font-black">
                {{ pgQuestionsCount }} Butir Objektif ({{ activeExam?.pg_weight || 70 }}%)
              </span>
              <span class="px-2.5 py-1 rounded-xl bg-amber-100/80 text-amber-800 font-black">
                {{ essayQuestionsCount }} Butir Uraian ({{ activeExam?.essay_weight || 30 }}%)
              </span>
            </div>
          </div>

          <!-- Secondary/Optional: Opsi Reset Cepat (Tidak Mencolok) -->
          <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
            <span class="text-slate-400 font-medium">
              💡 Butir soal di bawah sudah otomatis tersusun sesuai pilihan saat pembuatan ujian. Guru dapat mengubah tipe nomor tertentu lewat dropdown atau tombol di bawah:
            </span>
            <div class="flex items-center gap-1.5 flex-wrap">
              <button
                type="button"
                @click="setAllQuestionType('pg')"
                class="px-2 py-1 text-[10px] font-bold rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 transition-colors cursor-pointer"
                title="Reset seluruh nomor soal menjadi PG Biasa"
              >
                Reset Semua ke PG ({{ activeQuestions.length }})
              </button>
              <button
                v-if="activeQuestions.length >= 6"
                type="button"
                @click="setSplitFormat(activeQuestions.length - 5, 5)"
                class="px-2 py-1 text-[10px] font-bold rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 transition-colors cursor-pointer"
                title="Format cepat: PG + 5 Uraian"
              >
                Set PG + 5 Uraian
              </button>
            </div>
          </div>
        </div>

        <!-- Interactive Question Cards Grid -->
        <div class="space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
              <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Kisi-kisi Butir Soal & Kunci Jawaban ({{ activeQuestions.length }} Nomor)</h3>
              <span class="text-xs text-slate-400 font-medium">Ubah tipe tiap nomor dengan dropdown, tambah butir soal baru (+), atau hapus nomor yang tidak digunakan (🗑️).</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="relative">
                <button
                  type="button"
                  @click="showAddQuestionMenu = !showAddQuestionMenu"
                  class="px-3 py-1.5 text-xs font-bold rounded-xl bg-teal-600 hover:bg-teal-700 text-white flex items-center gap-1.5 shadow-sm transition-all cursor-pointer"
                >
                  <PlusCircle class="w-3.5 h-3.5" />
                  Tambah Butir Soal (+)
                </button>
                <div
                  v-if="showAddQuestionMenu"
                  class="absolute right-0 mt-1.5 w-52 bg-white border border-slate-200 rounded-2xl shadow-xl z-30 py-1.5 text-xs"
                >
                  <div class="px-3 py-1 text-[10px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-100">Pilih Bentuk Soal:</div>
                  <button type="button" @click="addQuestion('pg')" class="w-full text-left px-3 py-1.5 hover:bg-teal-50 text-slate-700 font-bold flex items-center justify-between cursor-pointer">
                    <span>1. Pilihan Ganda (PG)</span>
                    <span class="text-[10px] px-1.5 py-0.5 bg-teal-100 text-teal-800 rounded font-mono font-bold">+1</span>
                  </button>
                  <button type="button" @click="addQuestion('pg_complex')" class="w-full text-left px-3 py-1.5 hover:bg-purple-50 text-slate-700 font-bold flex items-center justify-between cursor-pointer">
                    <span>2. PG Kompleks</span>
                    <span class="text-[10px] px-1.5 py-0.5 bg-purple-100 text-purple-800 rounded font-mono font-bold">+1</span>
                  </button>
                  <button type="button" @click="addQuestion('true_false')" class="w-full text-left px-3 py-1.5 hover:bg-sky-50 text-slate-700 font-bold flex items-center justify-between cursor-pointer">
                    <span>3. Benar / Salah</span>
                    <span class="text-[10px] px-1.5 py-0.5 bg-sky-100 text-sky-800 rounded font-mono font-bold">+1</span>
                  </button>
                  <button type="button" @click="addQuestion('agree_disagree')" class="w-full text-left px-3 py-1.5 hover:bg-indigo-50 text-slate-700 font-bold flex items-center justify-between cursor-pointer">
                    <span>4. Setuju / Tdk</span>
                    <span class="text-[10px] px-1.5 py-0.5 bg-indigo-100 text-indigo-800 rounded font-mono font-bold">+1</span>
                  </button>
                  <button type="button" @click="addQuestion('matching')" class="w-full text-left px-3 py-1.5 hover:bg-emerald-50 text-slate-700 font-bold flex items-center justify-between cursor-pointer">
                    <span>5. Menjodohkan</span>
                    <span class="text-[10px] px-1.5 py-0.5 bg-emerald-100 text-emerald-800 rounded font-mono font-bold">+1</span>
                  </button>
                  <button type="button" @click="addQuestion('short_answer')" class="w-full text-left px-3 py-1.5 hover:bg-blue-50 text-slate-700 font-bold flex items-center justify-between cursor-pointer">
                    <span>6. Isian Singkat</span>
                    <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-800 rounded font-mono font-bold">+1</span>
                  </button>
                  <button type="button" @click="addQuestion('essay')" class="w-full text-left px-3 py-1.5 hover:bg-amber-50 text-slate-700 font-bold flex items-center justify-between cursor-pointer">
                    <span>7. Uraian / Essay</span>
                    <span class="text-[10px] px-1.5 py-0.5 bg-amber-100 text-amber-800 rounded font-mono font-bold">+1</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            <div
              v-for="q in activeQuestions"
              :key="q.id || q.question_number"
              class="p-3 rounded-2xl border transition-all text-center space-y-2.5 shadow-2xs relative group"
              :class="[
                q.question_type === 'essay' ? 'bg-amber-50/40 border-amber-200' :
                q.question_type === 'pg_complex' ? 'bg-purple-50/40 border-purple-200' :
                q.question_type === 'true_false' ? 'bg-sky-50/40 border-sky-200' :
                q.question_type === 'agree_disagree' ? 'bg-indigo-50/40 border-indigo-200' :
                q.question_type === 'matching' ? 'bg-emerald-50/40 border-emerald-200' :
                q.question_type === 'short_answer' ? 'bg-blue-50/40 border-blue-200' :
                (q.correct_answer ? 'bg-teal-50/60 border-teal-200' : 'bg-slate-50 border-slate-200')
              ]"
            >
              <!-- Card Header: Question Number, Type Selector & Delete Button -->
              <div class="flex items-center justify-between gap-1">
                <span class="text-xs font-black font-lexend text-slate-800">No. {{ q.question_number }}</span>
                <div class="flex items-center gap-1">
                  <select
                    v-model="q.question_type"
                    @change="onQuestionTypeChange(q)"
                    class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-lg border border-slate-200 bg-white text-slate-700 cursor-pointer focus:ring-1 focus:ring-teal-400"
                  >
                    <option value="pg">PG Biasa</option>
                    <option value="pg_complex">PG Kompleks</option>
                    <option value="true_false">Benar/Salah (B/S)</option>
                    <option value="agree_disagree">Setuju/Tdk (S/TS)</option>
                    <option value="matching">Menjodohkan</option>
                    <option value="short_answer">Isian Singkat</option>
                    <option value="essay">Uraian / Essay</option>
                  </select>
                  <button
                    type="button"
                    @click="removeQuestion(q.question_number)"
                    title="Hapus butir soal nomor ini"
                    class="w-5 h-5 flex items-center justify-center rounded-md text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                  >
                    <Trash2 class="w-3 h-3" />
                  </button>
                </div>
              </div>

              <!-- 1. PG Biasa (A, B, C, D) -->
              <div v-if="q.question_type === 'pg'" class="grid grid-cols-4 gap-1">
                <button
                  v-for="opt in ['A', 'B', 'C', 'D']"
                  :key="opt"
                  type="button"
                  @click="q.correct_answer = opt"
                  :class="q.correct_answer === opt ? 'bg-teal-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-xs rounded-lg transition-all cursor-pointer"
                >
                  {{ opt }}
                </button>
              </div>

              <!-- 2. PG Kompleks (Multi-Select A, B, C, D) -->
              <div v-else-if="q.question_type === 'pg_complex'" class="space-y-1">
                <div class="grid grid-cols-4 gap-1">
                  <button
                    v-for="opt in ['A', 'B', 'C', 'D']"
                    :key="opt"
                    type="button"
                    @click="toggleComplexOption(q, opt)"
                    :class="isComplexOptionSelected(q, opt) ? 'bg-purple-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                    class="h-7 text-xs rounded-lg transition-all cursor-pointer"
                  >
                    {{ opt }}
                  </button>
                </div>
                <div class="text-[9px] font-mono font-bold text-purple-700 truncate" title="Kunci Terpilih">
                  Kunci: {{ q.correct_answer || '(Pilih min 1)' }}
                </div>
              </div>

              <!-- 3. Benar / Salah (B / S) -->
              <div v-else-if="q.question_type === 'true_false'" class="grid grid-cols-2 gap-1">
                <button
                  type="button"
                  @click="q.correct_answer = 'B'"
                  :class="q.correct_answer === 'B' ? 'bg-sky-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-[10px] rounded-lg transition-all cursor-pointer"
                >
                  B (Benar)
                </button>
                <button
                  type="button"
                  @click="q.correct_answer = 'S'"
                  :class="q.correct_answer === 'S' ? 'bg-sky-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-[10px] rounded-lg transition-all cursor-pointer"
                >
                  S (Salah)
                </button>
              </div>

              <!-- 4. Setuju / Tidak Setuju (S / TS) -->
              <div v-else-if="q.question_type === 'agree_disagree'" class="grid grid-cols-2 gap-1">
                <button
                  type="button"
                  @click="q.correct_answer = 'S'"
                  :class="q.correct_answer === 'S' ? 'bg-indigo-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-[10px] rounded-lg transition-all cursor-pointer"
                >
                  S (Setuju)
                </button>
                <button
                  type="button"
                  @click="q.correct_answer = 'TS'"
                  :class="q.correct_answer === 'TS' ? 'bg-indigo-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-[10px] rounded-lg transition-all cursor-pointer"
                >
                  TS (Tdk)
                </button>
              </div>

              <!-- 5. Menjodohkan (Matching) -->
              <div v-else-if="q.question_type === 'matching'" class="space-y-1">
                <input
                  v-model="q.correct_answer"
                  type="text"
                  placeholder="1A,2C,3B"
                  class="w-full bg-white border border-emerald-300 rounded-lg px-2 py-1 text-center text-xs font-mono font-bold text-emerald-900 uppercase focus:ring-1 focus:ring-emerald-400"
                />
                <div class="text-[8px] text-emerald-700 font-medium">Format: 1A,2C,3B</div>
              </div>

              <!-- 6. Isian Singkat (Short Answer) -->
              <div v-else-if="q.question_type === 'short_answer'" class="space-y-1">
                <input
                  v-model="q.correct_answer"
                  type="text"
                  placeholder="Kunci teks..."
                  class="w-full bg-white border border-blue-300 rounded-lg px-2 py-1 text-center text-xs font-bold text-blue-900 focus:ring-1 focus:ring-blue-400"
                />
                <div class="text-[8px] text-blue-700 font-medium truncate" title="Gunakan | untuk alternatif sinonim">Pisahkan | utk opsi</div>
              </div>

              <!-- 7. Uraian / Essay -->
              <div v-else-if="q.question_type === 'essay'" class="space-y-1">
                <label class="block text-[8px] font-bold text-amber-700 uppercase">Maks Skor</label>
                <input
                  v-model.number="q.score_weight"
                  type="number"
                  min="1"
                  placeholder="Skor"
                  class="w-full bg-white border border-amber-300 rounded-lg px-1.5 py-1 text-center text-xs font-bold text-amber-900 focus:ring-1 focus:ring-amber-400"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Save Keys Button -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
          <span class="text-xs text-slate-400 font-medium">Perubahan kunci jawaban akan otomatis menghitung ulang nilai seluruh siswa.</span>
          <button
            @click="saveAnswerKeys"
            :disabled="savingKeys"
            class="px-8 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-teal-600/20 flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <Check class="w-4 h-4" />
            <span>{{ savingKeys ? 'Menyimpan & Menghitung Nilai...' : 'Simpan Kunci Jawaban' }}</span>
          </button>
        </div>
      </div>

      <!-- SUB-TAB 2: KOREKSI SISWA (FAST MATRIX) -->
      <div v-if="activeTab === 'grading'" class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-6">
        <!-- Action Toolbar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 sm:p-5 bg-teal-50/60 rounded-2xl border border-teal-100">
          <div class="space-y-1">
            <h3 class="text-xs sm:text-sm font-black text-teal-950 uppercase tracking-wider flex items-center gap-1.5">
              <span>🎯</span> Mode Koreksi Siswa (Cepat & Detail)
            </h3>
            <p class="text-[11px] sm:text-xs text-teal-700 font-medium leading-relaxed">
              Ketik deretan jawaban di kolom, atau klik tombol <strong class="text-teal-900 bg-white px-1.5 py-0.5 rounded border border-teal-200">Form Jawaban</strong>. Untuk siswa remedial, ketik nilai perbaikan pada kolom <strong class="text-teal-900">Nilai Remedial</strong> lalu klik <strong class="text-teal-900">Simpan & Hitung Koreksi</strong>.
            </p>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-shrink-0 w-full md:w-auto">
            <button
              @click="openPrintPreview"
              type="button"
              class="px-4 py-2.5 sm:py-3 bg-white hover:bg-slate-50 active:scale-98 text-slate-700 border border-slate-200 font-bold rounded-2xl text-xs transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto"
              title="Buka Lembar Cetak Rekap Capaian Kelas per Bentuk Soal (Konsep 1)"
            >
              <Printer class="w-4 h-4 text-emerald-600" />
              <span>Cetak Rekap Capaian</span>
            </button>

            <button
              type="button"
              @click="resetAllCorrections"
              class="px-4 py-2.5 sm:py-3 bg-white hover:bg-rose-50 active:scale-98 text-rose-600 hover:text-rose-700 border border-slate-200 hover:border-rose-200 font-bold rounded-2xl text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer flex-shrink-0 w-full sm:w-auto"
              title="Kosongkan seluruh koreksi dan nilai siswa pada ujian ini"
            >
              <RotateCcw class="w-4 h-4" />
              <span>Reset Semua</span>
            </button>

            <button
              @click="submitAllGrades"
              :disabled="gradingProcessing"
              class="px-6 sm:px-7 py-2.5 sm:py-3 bg-teal-600 hover:bg-teal-700 active:scale-98 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-teal-600/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 flex-shrink-0 w-full sm:w-auto"
            >
              <Zap class="w-4 h-4" />
              <span>{{ gradingProcessing ? 'Memproses Koreksi...' : 'Simpan & Hitung Koreksi' }}</span>
            </button>
          </div>
        </div>

        <!-- Student Answer Rows -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
              <tr>
                <th class="px-4 py-3.5 w-12 text-center">No</th>
                <th class="px-4 py-3.5 min-w-[160px] sm:min-w-[180px]">Nama Siswa</th>
                <th class="px-4 py-3.5 min-w-[340px] sm:min-w-[420px]">
                  <div>Jawaban PG Biasa ({{ pgBiasaQuestionsCount }} Butir)</div>
                  <span v-if="hasComplexQuestions" class="text-[9px] font-normal text-teal-700 normal-case block">
                    *Gunakan tombol "Form Jawaban" untuk PGK, Menjodohkan, B/S & Isian
                  </span>
                </th>
                <th v-if="essayQuestionsCount > 0" class="px-4 py-3.5 text-center min-w-[130px]">Nilai Uraian / Essay</th>
                <th class="px-4 py-3.5 text-center min-w-[90px]">Benar / Salah</th>
                <th class="px-4 py-3.5 text-center min-w-[80px]">Nilai Ujian</th>
                <th class="px-4 py-3.5 text-center min-w-[100px]">Nilai Remedial</th>
                <th class="px-4 py-3.5 text-center min-w-[90px]">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(student, idx) in activeStudents" :key="student.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="font-bold text-slate-800 font-lexend">{{ student.name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">NISN: {{ student.nisn || '-' }} • {{ student.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                </td>
                <td class="px-4 py-3 min-w-[340px] sm:min-w-[420px]">
                  <div class="flex items-center gap-1 sm:gap-1.5 flex-nowrap">
                    <button
                      type="button"
                      @click="openStudentModal(student, idx)"
                      class="px-2 sm:px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1 sm:gap-1.5 cursor-pointer flex-shrink-0 shadow-2xs active:scale-95"
                      :class="hasComplexQuestions ? 'bg-teal-600 hover:bg-teal-700 text-white shadow-teal-600/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200'"
                      :title="hasComplexQuestions ? 'Buka form interaktif untuk mengisi PGK, B/S, Menjodohkan, Isian' : 'Buka form jawaban lengkap'"
                    >
                      <FileText class="w-3.5 h-3.5" />
                      <span class="hidden sm:inline">{{ hasComplexQuestions ? 'Form Jawaban' : 'Detail' }}</span>
                      <span class="sm:hidden text-[11px]">Form</span>
                    </button>

                    <button
                      type="button"
                      @click="fillStudentWithKKM(student)"
                      class="p-1.5 sm:p-2 rounded-xl bg-slate-100 hover:bg-amber-50 active:scale-95 text-slate-500 hover:text-amber-600 border border-slate-200 hover:border-amber-200 transition-all cursor-pointer flex-shrink-0"
                      :title="`Isi jawaban siswa ini pas dengan KKM (${activeExam?.kkm || 75})`"
                    >
                      <Target class="w-3.5 h-3.5" />
                    </button>

                    <button
                      type="button"
                      @click="fillStudentWithAnswerKeys(student)"
                      class="p-1.5 sm:p-2 rounded-xl bg-slate-100 hover:bg-teal-50 active:scale-95 text-slate-500 hover:text-teal-700 border border-slate-200 hover:border-teal-200 transition-all cursor-pointer flex-shrink-0"
                      title="Salin 100% kunci jawaban lengkap ke siswa ini"
                    >
                      <Sparkles class="w-3.5 h-3.5" />
                    </button>

                    <button
                      type="button"
                      @click="resetStudentCorrection(student)"
                      class="p-1.5 sm:p-2 rounded-xl bg-slate-100 hover:bg-rose-50 active:scale-95 text-slate-500 hover:text-rose-600 border border-slate-200 hover:border-rose-200 transition-all cursor-pointer flex-shrink-0"
                      title="Kosongkan / Reset koreksi dan nilai siswa ini"
                    >
                      <RotateCcw class="w-3.5 h-3.5" />
                    </button>

                    <input
                      v-model="student.answer_string"
                      @input="onAnswerStringInput(student)"
                      type="text"
                      :maxlength="pgBiasaQuestionsCount"
                      :placeholder="pgBiasaQuestionsCount > 0 ? `Ketik ${pgBiasaQuestionsCount} jawaban PG...` : 'Gunakan Form Jawaban'"
                      :disabled="pgBiasaQuestionsCount === 0"
                      class="w-full min-w-[80px] bg-slate-50 border border-slate-200 rounded-xl px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs font-mono font-bold text-slate-800 tracking-widest uppercase focus:ring-2 focus:ring-teal-400 disabled:opacity-40"
                    />
                    <span class="text-[10px] font-mono text-slate-400 font-bold flex-shrink-0 w-10 sm:w-12 text-right">
                      {{ (student.answer_string || '').length }}/{{ pgBiasaQuestionsCount }}
                    </span>
                  </div>
                </td>
                <td v-if="essayQuestionsCount > 0" class="px-4 py-3 text-center">
                  <div class="flex items-center justify-center gap-1.5 flex-wrap">
                    <div v-for="eq in essayQuestions" :key="eq.id || eq.question_number" class="flex flex-col items-center">
                      <span class="text-[9px] font-bold text-slate-400">No.{{ eq.question_number }}</span>
                      <input
                        v-model.number="student.essay_scores[String(eq.question_number)]"
                        type="number"
                        min="0"
                        :max="eq.score_weight || 10"
                        step="any"
                        :placeholder="`0-${eq.score_weight || 10}`"
                        class="w-14 bg-white border border-slate-200 rounded-lg py-1 text-center text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400"
                      />
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-center">
                  <span v-if="student.has_submitted" class="font-bold text-slate-700">
                    <span class="text-emerald-600">{{ student.correct_pg_count }} B</span> /
                    <span class="text-rose-600">{{ student.wrong_pg_count }} S</span>
                  </span>
                  <span v-else class="text-slate-400 text-[11px]">-</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span v-if="student.has_submitted" class="text-sm font-black font-lexend" :class="student.total_score >= activeExam.kkm ? 'text-emerald-600' : 'text-rose-600'">
                    {{ student.total_score }}
                  </span>
                  <span v-else class="text-slate-400 text-[11px]">-</span>
                </td>

                <!-- Nilai Remedial Column -->
                <td class="px-4 py-3 text-center">
                  <div class="flex items-center justify-center">
                    <input
                      v-if="student.has_submitted"
                      v-model.number="student.remedial_score"
                      type="number"
                      min="0"
                      max="100"
                      :placeholder="student.total_score < activeExam.kkm ? '0-100' : '-'"
                      :class="[
                        (student.remedial_score && student.remedial_score >= activeExam.kkm) ? 'border-teal-400 bg-teal-50/60 text-teal-900 focus:ring-teal-400' :
                        (student.total_score < activeExam.kkm ? 'border-rose-300 bg-rose-50/30 text-rose-900 focus:ring-rose-400' : 'border-slate-200 bg-slate-50 text-slate-400')
                      ]"
                      class="w-16 border rounded-xl py-1 text-center text-xs font-black focus:ring-2 transition-all shadow-2xs"
                      :title="student.total_score < activeExam.kkm ? 'Ketik nilai setelah siswa selesai remedial' : 'Siswa sudah tuntas'"
                    />
                    <span v-else class="text-slate-400 text-[11px]">-</span>
                  </div>
                </td>

                <!-- Status Column -->
                <td class="px-4 py-3 text-center">
                  <span
                    v-if="student.has_submitted"
                    :class="[
                      (student.remedial_score && student.remedial_score >= activeExam.kkm) ? 'bg-teal-50 text-teal-800 border-teal-300' :
                      (student.total_score >= activeExam.kkm ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200')
                    ]"
                    class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border shadow-2xs inline-block"
                  >
                    {{ (student.remedial_score && student.remedial_score >= activeExam.kkm) ? '🟢 TUNTAS (REMEDIAL)' : (student.total_score >= activeExam.kkm ? '🟢 TUNTAS' : '🔴 REMEDIAL') }}
                  </span>
                  <span v-else class="text-slate-400 text-[10px] font-bold">BELUM INPUT</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SUB-TAB 3: ANALISIS BUTIR SOAL -->
      <div v-if="activeTab === 'analysis'" class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-6">
        <!-- Analysis Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" v-if="analysisData?.summary">
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Kelas</span>
            <div class="text-xl font-black text-teal-700 font-lexend">{{ analysisData.summary.avg_score }}</div>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nilai Tertinggi / Terendah</span>
            <div class="text-xl font-black text-slate-800 font-lexend">{{ analysisData.summary.max_score }} / {{ analysisData.summary.min_score }}</div>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa Tuntas / Remedial</span>
            <div class="text-xl font-black text-emerald-600 font-lexend">{{ analysisData.summary.passed_count }} / {{ analysisData.summary.remedial_count }}</div>
          </div>
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tingkat Ketuntasan</span>
            <div class="text-xl font-black text-blue-600 font-lexend">{{ analysisData.summary.pass_percentage }}%</div>
          </div>
        </div>

        <!-- Questions Difficulty Breakdown -->
        <div class="space-y-4">
          <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Tingkat Kesukaran & Daya Pembeda Soal</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="qa in analysisData?.questions_analysis"
              :key="qa.question_number"
              class="p-4 rounded-2xl border border-slate-100 bg-slate-50/50 space-y-3"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <span class="w-7 h-7 rounded-lg bg-teal-600 text-white font-black text-xs flex items-center justify-center font-lexend">
                    {{ qa.question_number }}
                  </span>
                  <span class="text-xs font-bold text-slate-700">Kunci: <strong class="text-teal-700">{{ qa.correct_answer || '-' }}</strong></span>
                </div>

                <div class="flex items-center gap-1.5">
                  <span
                    :class="[
                      qa.difficulty_category === 'Mudah' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                      qa.difficulty_category === 'Sedang' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                      'bg-rose-50 text-rose-700 border-rose-200'
                    ]"
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border"
                  >
                    {{ qa.difficulty_category }} (P: {{ qa.difficulty_index }})
                  </span>
                </div>
              </div>

              <!-- Progress bar of correct vs wrong -->
              <div class="space-y-1">
                <div class="flex justify-between text-[11px] font-bold">
                  <span class="text-emerald-700">Benar: {{ qa.correct_count }} Siswa</span>
                  <span class="text-rose-700">Salah: {{ qa.wrong_count }} Siswa</span>
                </div>
                <div class="w-full bg-rose-200 h-2 rounded-full overflow-hidden flex">
                  <div class="bg-emerald-500 h-full" :style="{ width: (qa.difficulty_index * 100) + '%' }"></div>
                </div>
              </div>

              <!-- Option distribution -->
              <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-500 pt-1 border-t border-slate-200/60">
                <span class="text-slate-400">Pilihan:</span>
                <span v-for="(cnt, opt) in qa.options" :key="opt" class="px-1.5 py-0.5 rounded bg-white border border-slate-200 font-mono">
                  {{ opt }}: {{ cnt }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SUB-TAB 4: SINKRONISASI NILAI & EKSPOR -->
      <div v-if="activeTab === 'integration'" class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- 1-Click Sync to Grades -->
          <div class="p-6 rounded-3xl bg-indigo-50/60 border border-indigo-100 space-y-4 flex flex-col justify-between">
            <div class="space-y-4">
              <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-600/20">
                <Send class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-indigo-950 font-lexend uppercase tracking-wider">Sinkronkan ke Buku Nilai / Rapor</h3>
                <p class="text-xs text-indigo-700 mt-1 font-medium">Kirim nilai hasil koreksi ujian ini secara otomatis ke modul Nilai Siswa (Gradebook) tanpa perlu menginput ulang secara manual.</p>
              </div>
            </div>

            <button
              @click="syncToGrades"
              :disabled="syncingGrades"
              class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-indigo-600/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <CheckCircle2 class="w-4 h-4" />
              <span>{{ syncingGrades ? 'Sedang Menyinkronkan...' : '1-Klik Kirim ke Buku Nilai' }}</span>
            </button>
          </div>

          <!-- Download Excel Report -->
          <div class="p-6 rounded-3xl bg-teal-50/60 border border-teal-100 space-y-4 flex flex-col justify-between">
            <div class="space-y-4">
              <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20">
                <FileSpreadsheet class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-teal-950 font-lexend uppercase tracking-wider">Download Rekap Nilai (Excel)</h3>
                <p class="text-xs text-teal-700 mt-1 font-medium">Unduh laporan lengkap berisikan daftar siswa, jawaban per nomor, perolehan nilai, serta status kelulusan dalam format file Excel (.xlsx).</p>
              </div>
            </div>

            <button
              @click="downloadExcel(activeExam.id)"
              class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-teal-600/20 flex items-center justify-center gap-2 cursor-pointer"
            >
              <Download class="w-4 h-4" />
              <span>Unduh Rekap Nilai Excel</span>
            </button>
          </div>

          <!-- Print Assessment Recap Sheet (Konsep 1) -->
          <div class="p-6 rounded-3xl bg-emerald-50/60 border border-emerald-100 space-y-4 flex flex-col justify-between">
            <div class="space-y-4">
              <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/20">
                <Printer class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-sm font-black text-emerald-950 font-lexend uppercase tracking-wider">Cetak & Ekspor Rekap Capaian</h3>
                <p class="text-xs text-emerald-700 mt-1 font-medium">Cetak lembar rekapitulasi nilai kolektif 1 kelas dengan rincian perolehan per bentuk soal, status KKM/KKTP, dan tanda tangan resmi ke PDF atau Word.</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <button
                @click="openPrintPreview"
                type="button"
                class="py-3 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <Printer class="w-4 h-4" />
                <span>Buka Lembar Cetak</span>
              </button>
              <button
                @click="exportToWord"
                type="button"
                class="py-3 px-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-blue-600/20 flex items-center justify-center gap-1.5 cursor-pointer"
                title="Unduh langsung dokumen Microsoft Word (.doc) dalam format lanskap resmi"
              >
                <FileText class="w-4 h-4" />
                <span>Unduh Word (.doc)</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CREATE EXAM MODAL -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-100 transform transition-all">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <h2 class="text-lg font-black text-slate-800 font-lexend uppercase tracking-wider">Buat Paket Ujian Baru</h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Tentukan kelas, mata pelajaran, jumlah soal, dan bobot penilaian.</p>
          </div>
          <button @click="showCreateModal = false" class="w-9 h-9 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors border border-slate-100 shadow-sm cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="createExam" class="p-8 space-y-4 max-h-[75vh] overflow-y-auto">
          <!-- Title -->
          <div class="space-y-1.5">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Judul Paket Ujian *</label>
            <input
              v-model="examForm.title"
              type="text"
              required
              placeholder="Contoh: STS Ganjil - IPA Terpadu Kelas 7"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400"
            />
          </div>

          <!-- Class & Subject -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Kelas *</label>
              <select v-model="examForm.class_room_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400">
                <option value="">-- Pilih Kelas --</option>
                <option v-for="c in classes" :key="c.id" :value="c.id">
                  Kelas {{ c.name }} (Tingkat {{ c.grade_level }}) — {{ c.students_count || 0 }} Siswa
                </option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Mata Pelajaran *</label>
              <select v-model="examForm.subject_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400">
                <option value="">-- Pilih Mata Pelajaran --</option>
                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
          </div>

          <!-- Exam Type & Semester -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Jenis Ujian *</label>
              <select v-model="examForm.exam_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400">
                <option value="uh">Penilaian Harian (UH)</option>
                <option value="sts">Sumatif Tengah Semester (STS)</option>
                <option value="sas">Sumatif Akhir Semester (SAS)</option>
                <option value="pat">Penilaian Akhir Tahun (PAT)</option>
                <option value="am">Asesmen Madrasah (AM)</option>
                <option value="quiz">Kuis / Latihan</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Semester *</label>
              <select v-model="examForm.semester" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400">
                <option value="ganjil">Semester Ganjil</option>
                <option value="genap">Semester Genap</option>
              </select>
            </div>
          </div>

          <!-- Question Composition Breakdown (Custom per Tipe Soal) -->
          <div class="p-5 bg-slate-50/80 border border-slate-200 rounded-3xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">🎯 Komposisi Bentuk Soal Sesuai Kebutuhan Mapel</label>
                <p class="text-[11px] text-slate-500 font-medium">Tentukan jumlah butir soal untuk tiap tipe yang diinginkan guru mapel.</p>
              </div>
              
              <!-- Quick Presets -->
              <div class="flex items-center gap-1.5 flex-wrap">
                <button
                  type="button"
                  @click="applyModalPreset('pg20')"
                  class="px-2.5 py-1 text-[10px] font-bold rounded-lg border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 shadow-2xs cursor-pointer"
                >
                  20 PG
                </button>
                <button
                  type="button"
                  @click="applyModalPreset('pg_essay')"
                  class="px-2.5 py-1 text-[10px] font-bold rounded-lg border bg-white hover:bg-slate-100 text-slate-700 border-slate-200 shadow-2xs cursor-pointer"
                >
                  15 PG + 5 Uraian
                </button>
                <button
                  type="button"
                  @click="applyModalPreset('akmi')"
                  class="px-2.5 py-1 text-[10px] font-bold rounded-lg border bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border-indigo-200 shadow-2xs cursor-pointer"
                >
                  ⭐ Standar AKMI / AM
                </button>
              </div>
            </div>

            <!-- 7 Question Type Counter Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
              <!-- 1. PG Biasa -->
              <div class="p-3 bg-white rounded-2xl border border-teal-200/80 space-y-1 text-center shadow-2xs">
                <span class="block text-[10px] font-black text-teal-800 uppercase">1. Pilihan Ganda (PG)</span>
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Jumlah</label>
                    <input
                      v-model.number="examForm.pg_count"
                      @input="updateTotalQuestions"
                      type="number"
                      min="0"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-slate-800 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Poin/Butir</label>
                    <input
                      v-model.number="examForm.pg_point"
                      type="number"
                      min="0.5"
                      step="0.5"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-teal-700 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                </div>
              </div>

              <!-- 2. PG Kompleks -->
              <div class="p-3 bg-white rounded-2xl border border-purple-200/80 space-y-1 text-center shadow-2xs">
                <span class="block text-[10px] font-black text-purple-800 uppercase">2. PG Kompleks</span>
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Jumlah</label>
                    <input
                      v-model.number="examForm.pg_complex_count"
                      @input="updateTotalQuestions"
                      type="number"
                      min="0"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-slate-800 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Poin/Butir</label>
                    <input
                      v-model.number="examForm.pg_complex_point"
                      type="number"
                      min="0.5"
                      step="0.5"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-purple-700 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                </div>
              </div>

              <!-- 3. Benar / Salah -->
              <div class="p-3 bg-white rounded-2xl border border-sky-200/80 space-y-1 text-center shadow-2xs">
                <span class="block text-[10px] font-black text-sky-800 uppercase">3. Benar / Salah</span>
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Jumlah</label>
                    <input
                      v-model.number="examForm.true_false_count"
                      @input="updateTotalQuestions"
                      type="number"
                      min="0"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-slate-800 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Poin/Butir</label>
                    <input
                      v-model.number="examForm.true_false_point"
                      type="number"
                      min="0.5"
                      step="0.5"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-sky-700 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                </div>
              </div>

              <!-- 4. Setuju / Tidak Setuju -->
              <div class="p-3 bg-white rounded-2xl border border-indigo-200/80 space-y-1 text-center shadow-2xs">
                <span class="block text-[10px] font-black text-indigo-800 uppercase">4. Setuju / Tdk</span>
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Jumlah</label>
                    <input
                      v-model.number="examForm.agree_disagree_count"
                      @input="updateTotalQuestions"
                      type="number"
                      min="0"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-slate-800 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Poin/Butir</label>
                    <input
                      v-model.number="examForm.agree_disagree_point"
                      type="number"
                      min="0.5"
                      step="0.5"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-indigo-700 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                </div>
              </div>

              <!-- 5. Menjodohkan -->
              <div class="p-3 bg-white rounded-2xl border border-emerald-200/80 space-y-1 text-center shadow-2xs">
                <span class="block text-[10px] font-black text-emerald-800 uppercase">5. Menjodohkan</span>
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Jumlah</label>
                    <input
                      v-model.number="examForm.matching_count"
                      @input="updateTotalQuestions"
                      type="number"
                      min="0"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-slate-800 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Poin/Butir</label>
                    <input
                      v-model.number="examForm.matching_point"
                      type="number"
                      min="0.5"
                      step="0.5"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-emerald-700 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                </div>
              </div>

              <!-- 6. Isian Singkat -->
              <div class="p-3 bg-white rounded-2xl border border-blue-200/80 space-y-1 text-center shadow-2xs">
                <span class="block text-[10px] font-black text-blue-800 uppercase">6. Isian Singkat</span>
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Jumlah</label>
                    <input
                      v-model.number="examForm.short_answer_count"
                      @input="updateTotalQuestions"
                      type="number"
                      min="0"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-slate-800 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Poin/Butir</label>
                    <input
                      v-model.number="examForm.short_answer_point"
                      type="number"
                      min="0.5"
                      step="0.5"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-blue-700 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                </div>
              </div>

              <!-- 7. Uraian / Essay -->
              <div class="p-3 bg-white rounded-2xl border border-amber-200/80 space-y-1 text-center shadow-2xs col-span-2 sm:col-span-1 md:col-span-2">
                <span class="block text-[10px] font-black text-amber-800 uppercase">7. Uraian / Essay</span>
                <div class="grid grid-cols-2 gap-1.5 pt-1">
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Jumlah</label>
                    <input
                      v-model.number="examForm.essay_count"
                      @input="updateTotalQuestions"
                      type="number"
                      min="0"
                      max="50"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-slate-800 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                  <div>
                    <label class="text-[9px] font-bold text-slate-500 uppercase block">Poin Maks/Butir</label>
                    <input
                      v-model.number="examForm.essay_point"
                      type="number"
                      min="1"
                      step="1"
                      max="100"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1 text-center text-xs font-black text-amber-700 focus:ring-2 focus:ring-teal-400"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Soal & Live Effective Breakdown Card -->
            <div class="p-3.5 rounded-2xl bg-white border border-slate-200 space-y-2.5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs font-bold">
                <div class="flex items-center gap-2">
                  <span class="px-2.5 py-1 rounded-xl bg-teal-600 text-white font-mono font-black">
                    Total: {{ examForm.total_questions }} Soal
                  </span>
                  <span class="text-slate-500 font-medium">
                    ({{ objectiveTotalCount }} Objektif + {{ examForm.essay_count || 0 }} Uraian)
                  </span>
                </div>
                <div class="text-slate-600">
                  Rasio Bobot: <strong class="text-teal-700">Objektif {{ examForm.pg_weight }}%</strong> | <strong class="text-amber-700">Uraian {{ examForm.essay_weight }}%</strong>
                </div>
              </div>

              <!-- Live Breakdown Per Question Type -->
              <div class="pt-2 border-t border-slate-100">
                <span class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1.5">
                  📊 Rincian Kontribusi Tiap Bentuk Soal ke Nilai Akhir (Skala 100%):
                </span>
                <div class="flex flex-wrap gap-1.5">
                  <template v-for="t in effectiveTypeWeights" :key="t.key">
                    <div
                      v-if="t.count > 0"
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-semibold bg-slate-50/80 border-slate-200"
                    >
                      <span class="font-bold text-slate-800">{{ t.label }}:</span>
                      <span class="text-slate-500">{{ t.count }} butir × {{ t.point }} pt = {{ t.totalPoints }} pt</span>
                      <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-teal-100 text-teal-800">
                        {{ t.effectivePercent }}% nilai
                      </span>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </div>

          <!-- KKM & Weights -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Batas KKM/KKTP *</label>
              <input
                v-model.number="examForm.kkm"
                type="number"
                min="0"
                max="100"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 text-center focus:ring-2 focus:ring-teal-400"
              />
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                Bobot Soal Objektif (%) *
              </label>
              <input
                v-model.number="examForm.pg_weight"
                type="number"
                min="0"
                max="100"
                required
                @input="onPgWeightInput"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 text-center focus:ring-2 focus:ring-teal-400"
              />
              <p class="text-[10px] text-slate-500 font-medium">Mencakup PG, PGK, B/S, S/TS, Menjodohkan, Isian</p>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">
                Bobot Soal Uraian (%) *
              </label>
              <input
                v-model.number="examForm.essay_weight"
                type="number"
                min="0"
                max="100"
                readonly
                class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-500 text-center cursor-not-allowed"
              />
              <p class="text-[10px] text-slate-500 font-medium">Otomatis (100% dikurangi Bobot Objektif)</p>
            </div>
          </div>

          <!-- Quick Keys String (Optional) -->
          <div class="space-y-1.5">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Kunci Jawaban Cepat (Opsional)</label>
            <input
              v-model="examForm.quick_keys"
              type="text"
              placeholder="Contoh: ABCDABCDABCD... (bisa diisi nanti)"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-mono font-bold text-slate-800 uppercase focus:ring-2 focus:ring-teal-400"
            />
          </div>

          <!-- Modal Action -->
          <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <button
              type="button"
              @click="showCreateModal = false"
              class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="creatingExam"
              class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-teal-600/20 cursor-pointer disabled:opacity-50"
            >
              {{ creatingExam ? 'Membuat...' : 'Buat Paket Ujian' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- STUDENT ANSWER DETAIL MODAL -->
    <div v-if="showStudentModal && selectedStudent" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-4xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
        <!-- Modal Header -->
        <div class="px-4 sm:px-8 py-4 sm:py-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-slate-50/70 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-teal-600/20 font-lexend flex-shrink-0">
              {{ selectedStudentIndex + 1 }}
            </div>
            <div>
              <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend">{{ selectedStudent.name }}</h2>
                <span class="text-[10px] sm:text-xs font-mono font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-700">NISN: {{ selectedStudent.nisn || '-' }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="selectedStudent.gender === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700'">
                  {{ selectedStudent.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                </span>
              </div>
              <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">Siswa ke-{{ selectedStudentIndex + 1 }} dari {{ activeStudents.length }} siswa • Kelas {{ activeExam?.class_room?.name }}</p>
            </div>
          </div>

          <div class="flex items-center gap-1.5 sm:gap-2 w-full sm:w-auto justify-between sm:justify-end">
            <button
              type="button"
              @click="prevStudent"
              :disabled="selectedStudentIndex <= 0"
              class="px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 active:scale-95 text-slate-700 text-xs font-bold transition-all disabled:opacity-30 cursor-pointer flex items-center gap-1 shadow-2xs"
            >
              <ChevronLeft class="w-4 h-4" />
              <span>Sebelumnya</span>
            </button>

            <button
              type="button"
              @click="nextStudent"
              :disabled="selectedStudentIndex >= activeStudents.length - 1"
              class="px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 active:scale-95 text-slate-700 text-xs font-bold transition-all disabled:opacity-30 cursor-pointer flex items-center gap-1 shadow-2xs"
            >
              <span>Berikutnya</span>
              <ChevronRight class="w-4 h-4" />
            </button>

            <button
              @click="closeStudentModal"
              class="w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors border border-slate-100 shadow-sm cursor-pointer ml-1 sm:ml-2"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Modal Content: Scrollable Grid of Questions -->
        <div class="p-6 overflow-y-auto space-y-4 flex-1">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <div
              v-for="q in activeQuestions"
              :key="q.id || q.question_number"
              class="p-3.5 rounded-2xl border transition-all space-y-2 bg-slate-50/70 border-slate-200"
            >
              <div class="flex items-center justify-between">
                <span class="text-xs font-black font-lexend text-slate-800">No. {{ q.question_number }}</span>
                <span class="text-[9px] px-2 py-0.5 rounded-full font-extrabold uppercase border bg-white text-slate-600">
                  {{ questionTypeLabel(q.question_type) }}
                </span>
              </div>

              <!-- Question Correct Answer Display for Teacher Reference -->
              <div class="text-[10px] text-slate-500 font-medium">
                Kunci: <strong class="text-teal-700 font-mono">{{ q.correct_answer || (q.question_type === 'essay' ? `Maks ${q.score_weight}` : '-') }}</strong>
              </div>

              <!-- Input Controls for Student's Answer -->
              <!-- 1. PG Biasa -->
              <div v-if="q.question_type === 'pg'" class="grid grid-cols-4 gap-1">
                <button
                  v-for="opt in ['A', 'B', 'C', 'D']"
                  :key="opt"
                  type="button"
                  @click="setStudentAnswer(selectedStudent, q.question_number, opt)"
                  :class="getStudentAnswer(selectedStudent, q.question_number) === opt ? 'bg-teal-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-xs rounded-lg transition-all cursor-pointer"
                >
                  {{ opt }}
                </button>
              </div>

              <!-- 2. PG Kompleks -->
              <div v-else-if="q.question_type === 'pg_complex'" class="space-y-1">
                <div class="grid grid-cols-4 gap-1">
                  <button
                    v-for="opt in ['A', 'B', 'C', 'D']"
                    :key="opt"
                    type="button"
                    @click="toggleStudentComplexOption(selectedStudent, q.question_number, opt)"
                    :class="isStudentComplexSelected(selectedStudent, q.question_number, opt) ? 'bg-purple-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                    class="h-7 text-xs rounded-lg transition-all cursor-pointer"
                  >
                    {{ opt }}
                  </button>
                </div>
                <div class="text-[9px] font-mono font-bold text-purple-700 truncate">
                  Jwb: {{ getStudentAnswer(selectedStudent, q.question_number) || '-' }}
                </div>
              </div>

              <!-- 3. Benar / Salah -->
              <div v-else-if="q.question_type === 'true_false'" class="grid grid-cols-2 gap-1">
                <button
                  type="button"
                  @click="setStudentAnswer(selectedStudent, q.question_number, 'B')"
                  :class="getStudentAnswer(selectedStudent, q.question_number) === 'B' ? 'bg-sky-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-[10px] rounded-lg transition-all cursor-pointer"
                >
                  Benar (B)
                </button>
                <button
                  type="button"
                  @click="setStudentAnswer(selectedStudent, q.question_number, 'S')"
                  :class="getStudentAnswer(selectedStudent, q.question_number) === 'S' ? 'bg-sky-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-[10px] rounded-lg transition-all cursor-pointer"
                >
                  Salah (S)
                </button>
              </div>

              <!-- 4. Setuju / Tidak Setuju -->
              <div v-else-if="q.question_type === 'agree_disagree'" class="grid grid-cols-2 gap-1">
                <button
                  type="button"
                  @click="setStudentAnswer(selectedStudent, q.question_number, 'S')"
                  :class="getStudentAnswer(selectedStudent, q.question_number) === 'S' ? 'bg-indigo-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-[10px] rounded-lg transition-all cursor-pointer"
                >
                  Setuju (S)
                </button>
                <button
                  type="button"
                  @click="setStudentAnswer(selectedStudent, q.question_number, 'TS')"
                  :class="getStudentAnswer(selectedStudent, q.question_number) === 'TS' ? 'bg-indigo-600 text-white shadow-sm font-black' : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200'"
                  class="h-7 text-[10px] rounded-lg transition-all cursor-pointer"
                >
                  Tidak (TS)
                </button>
              </div>

              <!-- 5. Menjodohkan -->
              <div v-else-if="q.question_type === 'matching'" class="space-y-1">
                <input
                  :value="getStudentAnswer(selectedStudent, q.question_number)"
                  @input="e => setStudentAnswer(selectedStudent, q.question_number, e.target.value.toUpperCase())"
                  type="text"
                  placeholder="1A,2C,3B"
                  class="w-full bg-white border border-emerald-300 rounded-lg px-2 py-1 text-center text-xs font-mono font-bold text-emerald-900 uppercase focus:ring-1 focus:ring-emerald-400"
                />
              </div>

              <!-- 6. Isian Singkat -->
              <div v-else-if="q.question_type === 'short_answer'" class="space-y-1">
                <input
                  :value="getStudentAnswer(selectedStudent, q.question_number)"
                  @input="e => setStudentAnswer(selectedStudent, q.question_number, e.target.value)"
                  type="text"
                  placeholder="Jawaban teks..."
                  class="w-full bg-white border border-blue-300 rounded-lg px-2 py-1 text-center text-xs font-bold text-blue-900 focus:ring-1 focus:ring-blue-400"
                />
              </div>

              <!-- 7. Uraian -->
              <div v-else-if="q.question_type === 'essay'" class="space-y-1">
                <div class="flex items-center justify-between text-[10px] font-bold text-amber-800">
                  <span>Nilai:</span>
                  <span>Maks: {{ q.score_weight || 10 }}</span>
                </div>
                <input
                  v-model.number="selectedStudent.essay_scores[String(q.question_number)]"
                  type="number"
                  min="0"
                  :max="q.score_weight || 10"
                  step="any"
                  :placeholder="`0-${q.score_weight || 10}`"
                  class="w-full bg-white border border-amber-300 rounded-lg px-2 py-1 text-center text-xs font-bold text-amber-900 focus:ring-1 focus:ring-amber-400"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-4 sm:px-8 py-3.5 sm:py-4 border-t border-slate-100 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3 bg-slate-50/50 flex-shrink-0">
          <span class="text-[11px] sm:text-xs text-slate-400 font-medium text-center md:text-left">
            Jawaban otomatis tersimpan ke draft. Klik "Simpan & Hitung Koreksi" di tabel utama untuk memproses nilai.
          </span>
          <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 w-full md:w-auto">
            <button
              type="button"
              @click="fillStudentWithKKM(selectedStudent)"
              class="px-3 sm:px-4 py-2 sm:py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs active:scale-95"
              :title="`Isi jawaban siswa ini otomatis pas dengan batas KKM (${activeExam?.kkm || 75})`"
            >
              <Target class="w-3.5 h-3.5 text-amber-600" />
              <span>Pas KKM ({{ activeExam?.kkm || 75 }})</span>
            </button>
            <button
              type="button"
              @click="fillStudentWithAnswerKeys(selectedStudent)"
              class="px-3 sm:px-4 py-2 sm:py-2.5 bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs active:scale-95"
              title="Salin 100% kunci jawaban lengkap (PG, PG Kompleks, Menjodohkan, Uraian) ke siswa ini"
            >
              <Sparkles class="w-3.5 h-3.5 text-teal-600" />
              <span class="truncate">Salin Kunci</span>
            </button>
            <button
              type="button"
              @click="resetStudentCorrection(selectedStudent)"
              class="px-3 sm:px-4 py-2 sm:py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs active:scale-95"
              title="Kosongkan seluruh koreksi dan nilai siswa ini"
            >
              <RotateCcw class="w-3.5 h-3.5 text-rose-600" />
              <span>Reset Siswa</span>
            </button>
            <button
              type="button"
              @click="closeStudentModal"
              class="px-4 sm:px-5 py-2 sm:py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer flex items-center justify-center active:scale-95"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- PRINT PREVIEW MODAL: REKAPITULASI NILAI CAPAIAN PER BENTUK SOAL (KONSEP 1) -->
    <div v-if="showPrintModal && activeExam" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[70] flex flex-col p-2 sm:p-6 overflow-hidden">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl mx-auto flex flex-col h-full max-h-full overflow-hidden border border-slate-200">
        <!-- Modal Toolbar Header (Pinned at Top, never overlaps content) -->
        <div class="no-print px-5 sm:px-8 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 flex-shrink-0 z-20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/20 flex-shrink-0">
              <Printer class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-sm font-black text-slate-800 font-lexend uppercase tracking-wider">Pratinjau Lembar Rekap Capaian per Bentuk Soal</h3>
                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Kertas Lanskap A4</span>
              </div>
              <p class="text-xs text-slate-500 font-medium">Format resmi madrasah. Tampilan di bawah ini adalah representasi nyata lembar cetak.</p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap justify-end">
            <button
              @click="printDocument"
              type="button"
              class="px-4 sm:px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak / Simpan PDF</span>
            </button>

            <button
              @click="exportToWord"
              type="button"
              class="px-4 sm:px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-blue-600/20 flex items-center gap-2 cursor-pointer"
              title="Unduh format dokumen Microsoft Word (.doc) dengan tata letak lanskap resmi"
            >
              <FileText class="w-4 h-4" />
              <span>Unduh Word (.doc)</span>
            </button>

            <button
              @click="showPrintModal = false"
              type="button"
              class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 active:scale-95 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>

        <!-- Canvas Area (Smooth scroll, content always starts at top y=0, perfectly centered) -->
        <div class="flex-1 overflow-auto bg-slate-200/90 p-4 sm:p-8 flex justify-start xl:justify-center items-start">
          <!-- The Printable Sheet Container -->
          <div id="printableRecapSheet" class="printable-recap-sheet bg-white w-[1120px] min-w-[1120px] p-8 sm:p-10 shadow-2xl border border-slate-300 text-slate-900 rounded-xl space-y-5 my-2">
            
            <!-- 1. KOP RESMI MADRASAH DENGAN LOGO RESMI -->
            <div class="flex items-center gap-5 border-b-4 border-double border-slate-900 pb-3">
              <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
                <img
                  v-if="schoolProfile?.app_logo_url || schoolProfile?.app_logo"
                  :src="schoolProfile?.app_logo_url || getImageUrl(schoolProfile?.app_logo)"
                  class="w-full h-full object-contain"
                  alt="Logo Madrasah"
                />
                <div v-else class="w-18 h-18 rounded-2xl bg-teal-800 text-white flex items-center justify-center font-black text-xl shadow-md">
                  MTS
                </div>
              </div>
              <div class="text-center flex-1 pr-6 sm:pr-14">
                <div class="text-xs sm:text-sm font-bold tracking-widest text-slate-700 uppercase">
                  {{ schoolProfile?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM AL-HASANAH' }}
                </div>
                <h1 class="text-xl sm:text-2xl font-black font-lexend uppercase tracking-wide text-slate-900 mt-0.5">
                  {{ schoolProfile?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
                </h1>
                <div class="text-[11px] sm:text-xs font-semibold text-slate-600 mt-0.5">
                  {{ schoolProfile?.school_tagline || 'Madrasah Tsanawiyah Al - Hasanah Ciomas' }} • Status: {{ schoolProfile?.school_accreditation || 'TERAKREDITASI A' }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-slate-600 mt-0.5">
                  {{ schoolProfile?.school_address || 'Jl. Ciapus Sukamakmur No.05, Ciomas, Bogor' }}
                </div>
                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                  Telp: {{ schoolProfile?.school_phone || '081617666017' }} • Email: {{ schoolProfile?.school_email || 'mtsalhasanah.ciomas@gmail.com' }}
                </div>
              </div>
            </div>

            <!-- 2. JUDUL LEMBAR REKAPITULASI -->
            <div class="text-center space-y-1">
              <h2 class="text-sm sm:text-base font-black uppercase tracking-wider text-slate-900 font-lexend underline decoration-slate-900 underline-offset-4">
                LEMBAR REKAPITULASI CAPAIAN NILAI ASESMEN PER BENTUK SOAL
              </h2>
              <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">
                {{ getExamTypeFullName(activeExam.exam_type) }} • SEMESTER {{ (activeExam.semester || 'ganjil').toUpperCase() }} • TAHUN PELAJARAN {{ activeExam.academic_year?.name || '2024/2025' }}
              </p>
            </div>

            <!-- 3. METADATA ASESMEN -->
            <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 text-xs font-medium border border-slate-300 rounded-lg p-3 bg-slate-50/70">
              <div class="space-y-1">
                <div class="flex"><span class="w-32 font-bold text-slate-700">Mata Pelajaran</span><span class="mr-2">:</span><strong class="text-slate-900">{{ activeExam.subject?.name || '-' }}</strong></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Kelas / Rombel</span><span class="mr-2">:</span><strong class="text-slate-900">Kelas {{ activeExam.class_room?.name || '-' }}</strong></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Guru Pengampu</span><span class="mr-2">:</span><span>{{ activeExam.teacher?.full_name || activeExam.teacher?.name || '-' }}</span></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Nama Paket Ujian</span><span class="mr-2">:</span><span>{{ activeExam.title }}</span></div>
              </div>
              <div class="space-y-1">
                <div class="flex"><span class="w-36 font-bold text-slate-700">Jenis Asesmen</span><span class="mr-2">:</span><strong class="text-slate-900">{{ getExamTypeFullName(activeExam.exam_type) }}</strong></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">KKM / KKTP</span><span class="mr-2">:</span><strong class="text-teal-900 bg-teal-100/70 px-2 py-0.5 rounded border border-teal-300">{{ activeExam.kkm }}</strong></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">Bobot Penilaian</span><span class="mr-2">:</span><span>Objektif: {{ activeExam.pg_weight }}% | Uraian: {{ activeExam.essay_weight }}%</span></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">Komposisi Soal</span><span class="mr-2">:</span><span class="font-semibold">{{ activeExam.total_questions }} Butir ({{ activeQuestionTypesList.map(t => `${t.count} ${t.label}`).join(', ') }})</span></div>
              </div>
            </div>

            <!-- 4. TABEL CAPAIAN PER BENTUK SOAL -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-[11px] border-collapse border border-slate-300 bg-white">
                <thead>
                  <tr class="bg-slate-100 text-slate-800 uppercase font-black tracking-wider text-center text-[10px]">
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-10">No</th>
                    <th rowspan="2" class="border border-slate-300 px-2.5 py-2 w-28">NISN</th>
                    <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left min-w-[180px]">Nama Siswa</th>
                    <th rowspan="2" class="border border-slate-300 px-1.5 py-2 w-10">L/P</th>
                    <!-- Dynamic Columns for each Active Question Type -->
                    <th :colspan="activeQuestionTypesList.length" class="border border-slate-300 px-3 py-1.5 bg-slate-200/80 text-teal-950 font-black">
                      Capaian Nilai per Bentuk Soal (Poin / Maks)
                    </th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-18">Nilai Asli</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-18">Nilai Rem.</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-18 bg-slate-150 font-black">Nilai Akhir</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-24">Keterangan</th>
                  </tr>
                  <tr class="bg-slate-50 text-slate-700 font-bold text-center text-[10px]">
                    <th
                      v-for="typeObj in activeQuestionTypesList"
                      :key="typeObj.key"
                      class="border border-slate-300 px-2 py-1.5 min-w-[80px]"
                    >
                      <div class="font-bold text-slate-800">{{ typeObj.label }}</div>
                      <div class="text-[9px] text-slate-500 font-normal">({{ typeObj.count }} Soal • Maks {{ typeObj.maxScore }})</div>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(student, idx) in activeStudents"
                    :key="student.id"
                    class="hover:bg-slate-50/50"
                  >
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-slate-500">{{ idx + 1 }}</td>
                    <td class="border border-slate-300 px-2.5 py-1.5 text-center font-mono text-slate-600">{{ student.nisn || '-' }}</td>
                    <td class="border border-slate-300 px-3 py-1.5 font-bold text-slate-800">{{ student.name }}</td>
                    <td class="border border-slate-300 px-1.5 py-1.5 text-center font-bold text-slate-600">{{ student.gender || '-' }}</td>

                    <!-- Scores per question type -->
                    <td
                      v-for="typeObj in activeQuestionTypesList"
                      :key="typeObj.key"
                      class="border border-slate-300 px-2 py-1.5 text-center"
                    >
                      <div class="font-bold text-slate-800">
                        {{ calculateStudentTypeScore(student, typeObj).earned }}
                      </div>
                      <div class="text-[9px] text-slate-500">
                        ({{ calculateStudentTypeScore(student, typeObj).percentage }}%)
                      </div>
                    </td>

                    <!-- Initial Exam Score -->
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-bold" :class="(student.total_score !== null && student.total_score < activeExam.kkm) ? 'text-rose-600' : 'text-slate-800'">
                      {{ student.total_score !== null ? student.total_score : '-' }}
                    </td>

                    <!-- Remedial Score -->
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-teal-700">
                      {{ (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? student.remedial_score : '-' }}
                    </td>

                    <!-- Final Grade -->
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-black text-slate-900 bg-slate-50/70">
                      {{ getStudentFinalGrade(student) }}
                    </td>

                    <!-- Status -->
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-black text-[10px]">
                      <span
                        v-if="getStudentPrintStatus(student) === 'TUNTAS'"
                        class="text-emerald-700"
                      >
                        TUNTAS
                      </span>
                      <span
                        v-else-if="getStudentPrintStatus(student) === 'TUNTAS (REM)'"
                        class="text-teal-700"
                      >
                        TUNTAS (REM)
                      </span>
                      <span
                        v-else-if="getStudentPrintStatus(student) === 'REMEDIAL'"
                        class="text-rose-600"
                      >
                        REMEDIAL
                      </span>
                      <span
                        v-else
                        class="text-slate-400"
                      >
                        BELUM UJIAN
                      </span>
                    </td>
                  </tr>

                  <tr v-if="activeStudents.length === 0">
                    <td :colspan="8 + activeQuestionTypesList.length" class="border border-slate-300 px-4 py-6 text-center text-slate-400">
                      Tidak ada data siswa pada kelas ini.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 5. STATISTIK KETUNTASAN KLASIKAL -->
            <div class="space-y-1.5">
              <div class="text-xs font-black text-slate-800 uppercase tracking-wider">Rekapitulasi Ketuntasan Klasikal:</div>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <div class="p-2 border border-slate-300 rounded bg-slate-50">
                  <span class="text-slate-500 block text-[10px]">Total Siswa Peserta</span>
                  <strong class="text-slate-800 text-sm">{{ printStats.participated }} / {{ printStats.total }} Siswa</strong>
                </div>
                <div class="p-2 border border-slate-300 rounded bg-slate-50">
                  <span class="text-slate-500 block text-[10px]">Tuntas (Murni + Rem)</span>
                  <strong class="text-emerald-700 text-sm">{{ printStats.totalPassed }} Siswa ({{ printStats.passPercentage }}%)</strong>
                </div>
                <div class="p-2 border border-slate-300 rounded bg-slate-50">
                  <span class="text-slate-500 block text-[10px]">Perlu Remedial</span>
                  <strong class="text-rose-600 text-sm">{{ printStats.remedialCount }} Siswa</strong>
                </div>
                <div class="p-2 border border-slate-300 rounded bg-slate-50">
                  <span class="text-slate-500 block text-[10px]">Rata-rata / Tertinggi / Terendah</span>
                  <strong class="text-slate-800 text-sm">{{ printStats.avgScore }} / {{ printStats.maxScore }} / {{ printStats.minScore }}</strong>
                </div>
              </div>
            </div>

            <!-- 6. LEMBAR TANDA TANGAN RESMI -->
            <div class="pt-6 text-xs text-slate-800">
              <div class="flex justify-end mb-4">
                <div>Ciomas, {{ getPrintDateFormatted() }}</div>
              </div>
              <div class="grid grid-cols-2 gap-8 text-center">
                <div>
                  <div class="font-bold">Mengetahui,</div>
                  <div>Kepala MTs Al - Hasanah</div>
                  <div class="h-20 flex items-center justify-center">
                    <!-- Space for stamp & sign -->
                  </div>
                  <div class="font-black text-slate-900 underline">{{ schoolProfile?.principal_name || 'Kepala Madrasah' }}</div>
                  <div class="text-[11px] text-slate-600 font-mono">NIP: {{ schoolProfile?.principal_nip || '-' }}</div>
                </div>

                <div>
                  <div class="font-bold">Guru Pengampu,</div>
                  <div>Mata Pelajaran {{ activeExam.subject?.name || '' }}</div>
                  <div class="h-20 flex items-center justify-center">
                    <!-- Space for sign -->
                  </div>
                  <div class="font-black text-slate-900 underline">{{ activeExam.teacher?.full_name || activeExam.teacher?.name || 'Guru Mata Pelajaran' }}</div>
                  <div class="text-[11px] text-slate-600 font-mono">NIP: {{ activeExam.teacher?.nip || '-' }}</div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import {
  CheckSquare,
  PlusCircle,
  BookOpen,
  Award,
  CheckCircle2,
  AlertCircle,
  Search,
  Sliders,
  Download,
  Trash2,
  ArrowLeft,
  KeyRound,
  BarChart2,
  Send,
  Zap,
  Check,
  FileSpreadsheet,
  X,
  FileText,
  ChevronLeft,
  ChevronRight,
  Sparkles,
  Target,
  Printer,
  Copy,
  RotateCcw
} from 'lucide-vue-next';

const toast = useToast();
const { confirm } = useConfirm();

const loading = ref(false);
const exams = ref([]);
const classes = ref([]);
const subjects = ref([]);

const filterClass = ref('');
const filterSubject = ref('');
const filterType = ref('');
const searchQuery = ref('');

const showPrintModal = ref(false);
const schoolProfile = ref(null);

const showCreateModal = ref(false);
const creatingExam = ref(false);
const examForm = reactive({
  title: '',
  class_room_id: '',
  subject_id: '',
  exam_type: 'uh',
  semester: 'ganjil',
  pg_count: 20,
  pg_point: 1,
  pg_complex_count: 0,
  pg_complex_point: 2,
  true_false_count: 0,
  true_false_point: 1,
  agree_disagree_count: 0,
  agree_disagree_point: 1,
  matching_count: 0,
  matching_point: 2,
  short_answer_count: 0,
  short_answer_point: 2,
  essay_count: 0,
  essay_point: 10,
  total_questions: 20,
  kkm: 75,
  pg_weight: 100,
  essay_weight: 0,
  quick_keys: ''
});

const objectiveTotalCount = computed(() => {
  return (Number(examForm.pg_count) || 0) +
    (Number(examForm.pg_complex_count) || 0) +
    (Number(examForm.true_false_count) || 0) +
    (Number(examForm.agree_disagree_count) || 0) +
    (Number(examForm.matching_count) || 0) +
    (Number(examForm.short_answer_count) || 0);
});

const effectiveTypeWeights = computed(() => {
  const types = [
    { key: 'pg', label: 'PG Biasa', count: Number(examForm.pg_count) || 0, point: Number(examForm.pg_point) || 1, isEssay: false },
    { key: 'pg_complex', label: 'PG Kompleks', count: Number(examForm.pg_complex_count) || 0, point: Number(examForm.pg_complex_point) || 2, isEssay: false },
    { key: 'true_false', label: 'Benar / Salah', count: Number(examForm.true_false_count) || 0, point: Number(examForm.true_false_point) || 1, isEssay: false },
    { key: 'agree_disagree', label: 'Setuju / Tdk', count: Number(examForm.agree_disagree_count) || 0, point: Number(examForm.agree_disagree_point) || 1, isEssay: false },
    { key: 'matching', label: 'Menjodohkan', count: Number(examForm.matching_count) || 0, point: Number(examForm.matching_point) || 2, isEssay: false },
    { key: 'short_answer', label: 'Isian Singkat', count: Number(examForm.short_answer_count) || 0, point: Number(examForm.short_answer_point) || 2, isEssay: false },
    { key: 'essay', label: 'Uraian / Essay', count: Number(examForm.essay_count) || 0, point: Number(examForm.essay_point) || 10, isEssay: true },
  ];

  const totalObjPoints = types.filter(t => !t.isEssay).reduce((sum, t) => sum + (t.count * t.point), 0);
  const pgWeight = Number(examForm.pg_weight) || 0;
  const essayWeight = Number(examForm.essay_weight) || 0;

  return types.map(t => {
    const totalPoints = t.count * t.point;
    let effectivePercent = 0;
    if (t.isEssay) {
      effectivePercent = t.count > 0 ? essayWeight : 0;
    } else {
      if (totalObjPoints > 0) {
        effectivePercent = Math.round(((totalPoints / totalObjPoints) * pgWeight) * 10) / 10;
      }
    }
    return {
      ...t,
      totalPoints,
      effectivePercent
    };
  });
});

function updateTotalQuestions() {
  const obj = objectiveTotalCount.value;
  const es = Number(examForm.essay_count) || 0;
  examForm.total_questions = Math.max(1, obj + es);

  if (es === 0) {
    examForm.pg_weight = 100;
    examForm.essay_weight = 0;
  } else if (examForm.pg_weight === 100) {
    examForm.pg_weight = 70;
    examForm.essay_weight = 30;
  }
}

function applyModalPreset(preset) {
  if (preset === 'pg20') {
    examForm.pg_count = 20;
    examForm.pg_point = 1;
    examForm.pg_complex_count = 0;
    examForm.true_false_count = 0;
    examForm.agree_disagree_count = 0;
    examForm.matching_count = 0;
    examForm.short_answer_count = 0;
    examForm.essay_count = 0;
  } else if (preset === 'pg_essay') {
    examForm.pg_count = 15;
    examForm.pg_point = 1;
    examForm.pg_complex_count = 0;
    examForm.true_false_count = 0;
    examForm.agree_disagree_count = 0;
    examForm.matching_count = 0;
    examForm.short_answer_count = 0;
    examForm.essay_count = 5;
    examForm.essay_point = 10;
  } else if (preset === 'akmi') {
    examForm.pg_count = 10;
    examForm.pg_point = 1;
    examForm.pg_complex_count = 3;
    examForm.pg_complex_point = 2;
    examForm.true_false_count = 2;
    examForm.true_false_point = 1;
    examForm.agree_disagree_count = 0;
    examForm.agree_disagree_point = 1;
    examForm.matching_count = 2;
    examForm.matching_point = 2;
    examForm.short_answer_count = 1;
    examForm.short_answer_point = 2;
    examForm.essay_count = 2;
    examForm.essay_point = 10;
  }
  updateTotalQuestions();
}

function onPgWeightInput() {
  const pg = Number(examForm.pg_weight) || 0;
  examForm.essay_weight = Math.max(0, 100 - pg);
}

// Active Exam Workspace States
const activeExam = ref(null);
const activeTab = ref('keys');
const activeQuestions = ref([]);
const activeStudents = ref([]);
const quickKeyInput = ref('');
const savingKeys = ref(false);
const gradingProcessing = ref(false);
const syncingGrades = ref(false);
const analysisData = ref(null);

const pgQuestions = computed(() => activeQuestions.value.filter(q => q.question_type !== 'essay'));
const essayQuestions = computed(() => activeQuestions.value.filter(q => q.question_type === 'essay'));
const pgQuestionsCount = computed(() => pgQuestions.value.length);
const essayQuestionsCount = computed(() => essayQuestions.value.length);
const totalEssayMaxScore = computed(() => essayQuestions.value.reduce((sum, q) => sum + (Number(q.score_weight) || 0), 0));

const pgBiasaQuestions = computed(() => activeQuestions.value.filter(q => q.question_type === 'pg'));
const pgBiasaQuestionsCount = computed(() => pgBiasaQuestions.value.length);

const hasComplexQuestions = computed(() => {
  return activeQuestions.value.some(q => ['pg_complex', 'true_false', 'agree_disagree', 'matching', 'short_answer'].includes(q.question_type));
});

const questionTypeSummary = computed(() => {
  const counts = {
    pg: 0,
    pg_complex: 0,
    true_false: 0,
    agree_disagree: 0,
    matching: 0,
    short_answer: 0,
    essay: 0
  };
  activeQuestions.value.forEach(q => {
    if (counts[q.question_type] !== undefined) {
      counts[q.question_type]++;
    } else {
      counts.pg++;
    }
  });
  return counts;
});

function questionTypeLabel(type) {
  const map = {
    pg: 'PG Biasa',
    pg_complex: 'PG Kompleks',
    true_false: 'Benar/Salah',
    agree_disagree: 'Setuju/Tdk',
    matching: 'Menjodohkan',
    short_answer: 'Isian Singkat',
    essay: 'Uraian'
  };
  return map[type] || 'PG';
}

const showAddQuestionMenu = ref(false);

function addQuestion(type = 'pg') {
  showAddQuestionMenu.value = false;
  const newNum = activeQuestions.value.length + 1;
  let defaultWeight = 1.00;
  if (type === 'essay') defaultWeight = 10.00;
  else if (['pg_complex', 'matching', 'short_answer'].includes(type)) defaultWeight = 2.00;

  activeQuestions.value.push({
    exam_package_id: activeExam.value?.id,
    question_number: newNum,
    question_type: type,
    correct_answer: null,
    score_weight: defaultWeight
  });

  if (activeExam.value) {
    activeExam.value.total_questions = activeQuestions.value.length;
  }
  toast.success(`Butir soal No. ${newNum} (${questionTypeLabel(type)}) berhasil ditambahkan! Silakan klik "Simpan Format & Kunci".`);
}

async function removeQuestion(qNum) {
  if (activeQuestions.value.length <= 1) {
    toast.error('Minimal harus ada 1 butir soal dalam paket ujian.');
    return;
  }
  const isConfirmed = await confirm({
    title: 'Hapus Butir Soal',
    message: `Apakah Anda yakin ingin menghapus butir soal No. ${qNum}? Penomoran soal lainnya akan diurutkan kembali secara otomatis.`,
    type: 'danger',
    confirmText: 'Ya, Hapus Soal',
    cancelText: 'Batal'
  });
  if (!isConfirmed) return;

  activeQuestions.value = activeQuestions.value.filter(q => q.question_number !== qNum);
  // Renumber sequentially
  activeQuestions.value.forEach((q, idx) => {
    q.question_number = idx + 1;
  });
  if (activeExam.value) {
    activeExam.value.total_questions = activeQuestions.value.length;
  }
  toast.info(`Nomor ${qNum} dihapus. Nomor soal diurutkan kembali (Total: ${activeQuestions.value.length} butir).`);
}

function onQuestionTypeChange(q) {
  if (q.question_type === 'essay') {
    q.correct_answer = null;
    if (!q.score_weight || q.score_weight <= 1) q.score_weight = 10.00;
  } else {
    if (!q.score_weight || q.score_weight > 1) q.score_weight = 1.00;
  }
}

function toggleComplexOption(q, opt) {
  let current = (q.correct_answer || '').split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
  if (current.includes(opt)) {
    current = current.filter(x => x !== opt);
  } else {
    current.push(opt);
  }
  current.sort();
  q.correct_answer = current.join(',');
}

function isComplexOptionSelected(q, opt) {
  if (!q.correct_answer) return false;
  const current = q.correct_answer.split(',').map(s => s.trim().toUpperCase());
  return current.includes(opt);
}

function setAllQuestionType(type) {
  activeQuestions.value.forEach(q => {
    q.question_type = type;
    if (type === 'essay') {
      q.correct_answer = null;
      if (!q.score_weight || q.score_weight <= 1) q.score_weight = 10.00;
    } else {
      q.score_weight = 1.00;
    }
  });
}

function applyAkmiPreset() {
  const total = activeQuestions.value.length;
  if (total <= 0) return;

  // Standar AKMI / Asesmen Madrasah:
  // ~45% PG, ~20% PGK, ~10% B/S, ~10% Menjodohkan, ~5% Isian Singkat, ~10% Uraian
  const pgLimit = Math.max(1, Math.round(total * 0.45));
  const pgkLimit = pgLimit + Math.max(1, Math.round(total * 0.20));
  const tfLimit = pgkLimit + Math.max(1, Math.round(total * 0.10));
  const matchLimit = tfLimit + Math.max(1, Math.round(total * 0.10));
  const saLimit = matchLimit + Math.max(1, Math.round(total * 0.05));

  activeQuestions.value.forEach(q => {
    const num = q.question_number;
    if (num <= pgLimit) {
      q.question_type = 'pg';
      q.score_weight = 1.0;
    } else if (num <= pgkLimit) {
      q.question_type = 'pg_complex';
      q.score_weight = 1.0;
    } else if (num <= tfLimit) {
      q.question_type = 'true_false';
      q.score_weight = 1.0;
    } else if (num <= matchLimit) {
      q.question_type = 'matching';
      q.score_weight = 1.0;
    } else if (num <= saLimit && num < total) {
      q.question_type = 'short_answer';
      q.score_weight = 1.0;
    } else {
      q.question_type = 'essay';
      q.score_weight = 10.0;
      q.correct_answer = null;
    }
  });

  toast.success('Format standar AKMI / Asesmen Madrasah berhasil diterapkan!');
}

function setSplitFormat(pgCount, essayCount) {
  activeQuestions.value.forEach(q => {
    if (q.question_number <= pgCount) {
      q.question_type = 'pg';
      q.score_weight = 1.00;
    } else {
      q.question_type = 'essay';
      q.correct_answer = null;
      if (!q.score_weight || q.score_weight <= 1) q.score_weight = 10.00;
    }
  });
}

// Student Answer Modal States & Handlers
const showStudentModal = ref(false);
const selectedStudent = ref(null);
const selectedStudentIndex = ref(0);

function openStudentModal(student, idx) {
  selectedStudent.value = student;
  selectedStudentIndex.value = idx;
  if (!student.student_answers) {
    student.student_answers = {};
  }
  showStudentModal.value = true;
}

function closeStudentModal() {
  if (selectedStudent.value) {
    syncStudentAnswerString(selectedStudent.value);
  }
  showStudentModal.value = false;
  selectedStudent.value = null;
}

function prevStudent() {
  if (selectedStudentIndex.value > 0) {
    if (selectedStudent.value) syncStudentAnswerString(selectedStudent.value);
    selectedStudentIndex.value--;
    selectedStudent.value = activeStudents.value[selectedStudentIndex.value];
    if (!selectedStudent.value.student_answers) selectedStudent.value.student_answers = {};
  }
}

function nextStudent() {
  if (selectedStudentIndex.value < activeStudents.value.length - 1) {
    if (selectedStudent.value) syncStudentAnswerString(selectedStudent.value);
    selectedStudentIndex.value++;
    selectedStudent.value = activeStudents.value[selectedStudentIndex.value];
    if (!selectedStudent.value.student_answers) selectedStudent.value.student_answers = {};
  }
}

function getStudentAnswer(student, qNum) {
  return student?.student_answers?.[String(qNum)] || '';
}

function setStudentAnswer(student, qNum, val) {
  if (!student.student_answers) student.student_answers = {};
  student.student_answers[String(qNum)] = val;
  syncStudentAnswerString(student);
}

function toggleStudentComplexOption(student, qNum, opt) {
  if (!student.student_answers) student.student_answers = {};
  const currentVal = student.student_answers[String(qNum)] || '';
  let parts = currentVal.split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
  if (parts.includes(opt)) {
    parts = parts.filter(x => x !== opt);
  } else {
    parts.push(opt);
  }
  parts.sort();
  student.student_answers[String(qNum)] = parts.join(',');
  syncStudentAnswerString(student);
}

function isStudentComplexSelected(student, qNum, opt) {
  const val = student?.student_answers?.[String(qNum)] || '';
  return val.split(',').map(s => s.trim().toUpperCase()).includes(opt);
}

function syncStudentAnswerString(student) {
  let str = '';
  const pgList = activeQuestions.value.filter(q => q.question_type === 'pg');
  pgList.forEach(q => {
    const val = student.student_answers?.[String(q.question_number)] || '';
    str += (val.length === 1 ? val : (val ? val[0] : ''));
  });
  student.answer_string = str;
}

function onAnswerStringInput(student) {
  // Strip all whitespace so copy-pasting strings with spaces doesn't shift question alignment!
  const raw = student.answer_string || '';
  const clean = raw.toUpperCase().replace(/\s+/g, '');
  student.answer_string = clean;

  const pgList = activeQuestions.value.filter(q => q.question_type === 'pg');
  if (!student.student_answers) student.student_answers = {};

  for (let i = 0; i < pgList.length; i++) {
    const qNum = String(pgList[i].question_number);
    if (i < clean.length) {
      student.student_answers[qNum] = clean[i];
    } else {
      // If user backspaced/cleared a character, remove single-letter answer for that PG question
      if (student.student_answers[qNum] && student.student_answers[qNum].length === 1) {
        delete student.student_answers[qNum];
      }
    }
  }
}

function fillStudentWithAnswerKeys(student) {
  if (!student) return;
  if (!student.student_answers) student.student_answers = {};
  if (!student.essay_scores) student.essay_scores = {};

  activeQuestions.value.forEach(q => {
    const qNum = String(q.question_number);
    if (q.question_type === 'essay') {
      student.essay_scores[qNum] = Number(q.score_weight || 10);
    } else {
      student.student_answers[qNum] = q.correct_answer || '';
    }
  });

  syncStudentAnswerString(student);
  toast.success(`Semua kunci jawaban berhasil disalin lengkap ke jawaban ${student.name}!`);
}

function getWrongAnswerForQuestion(q) {
  const correct = (q.correct_answer || '').trim();
  const type = q.question_type;

  if (type === 'pg') {
    const letters = ['A', 'B', 'C', 'D', 'E'];
    const wrong = letters.find(l => l !== correct.toUpperCase());
    return wrong || 'B';
  }

  if (type === 'true_false') {
    return correct.toUpperCase() === 'B' ? 'S' : 'B';
  }

  if (type === 'agree_disagree') {
    return correct.toUpperCase() === 'S' ? 'TS' : 'S';
  }

  if (type === 'pg_complex') {
    const letters = ['A', 'B', 'C', 'D', 'E'];
    const correctUpper = correct.toUpperCase();
    const wrong = letters.find(l => !correctUpper.includes(l));
    return wrong || 'E';
  }

  if (type === 'matching') {
    return '1-X';
  }

  if (type === 'short_answer') {
    return '-';
  }

  return 'X';
}

function findBestObjectiveSubset(questions, targetPoints) {
  if (!questions || questions.length === 0) return [];
  if (targetPoints <= 0) return [];

  const totalWeight = questions.reduce((sum, q) => sum + Number(q.score_weight || 1), 0);
  if (targetPoints >= totalWeight) {
    return questions.map((_, i) => i);
  }

  // Multiply weights by 100 to handle decimal weights safely
  const scale = 100;
  const targetScaled = Math.round(targetPoints * scale);
  const totalScaled = Math.round(totalWeight * scale);

  const dp = new Map();
  dp.set(0, []);

  for (let i = 0; i < questions.length; i++) {
    const w = Math.round(Number(questions[i].score_weight || 1) * scale);
    if (w <= 0) continue;
    const entries = Array.from(dp.entries());
    for (const [currW, indices] of entries) {
      const nextW = currW + w;
      if (nextW <= totalScaled && !dp.has(nextW)) {
        dp.set(nextW, [...indices, i]);
      }
    }
  }

  let bestW = -1;
  let minDiff = Infinity;

  for (const [w] of dp.entries()) {
    const diff = Math.abs(w - targetScaled);
    if (diff === 0) {
      bestW = w;
      break;
    }
    // Prefer w >= targetScaled so the score reaches/passes KKM
    const penalty = w >= targetScaled ? 0 : 0.05;
    if (diff + penalty < minDiff) {
      minDiff = diff + penalty;
      bestW = w;
    }
  }

  return dp.get(bestW) || [];
}

function fillStudentWithKKM(student) {
  if (!student) return;
  if (!student.student_answers) student.student_answers = {};
  if (!student.essay_scores) student.essay_scores = {};

  const kkm = Number(activeExam.value?.kkm ?? 75);
  const questions = activeQuestions.value || [];
  if (questions.length === 0) {
    toast.error('Belum ada butir soal ujian.');
    return;
  }

  const objQuestions = questions.filter(q => q.question_type !== 'essay');
  const essayQuestions = questions.filter(q => q.question_type === 'essay');

  const pgWeight = Number(activeExam.value?.pg_weight ?? 100);
  const essayWeight = Number(activeExam.value?.essay_weight ?? 0);

  const totalObjWeight = objQuestions.reduce((sum, q) => sum + Number(q.score_weight || 1), 0);
  const totalEssayWeight = essayQuestions.reduce((sum, q) => sum + Number(q.score_weight || 10), 0);

  // 1. Calculate best subset for Objective questions
  const targetObjPoints = (kkm / 100) * totalObjWeight;
  const bestSubsetIndices = new Set(findBestObjectiveSubset(objQuestions, targetObjPoints));

  let earnedObjPoints = 0;
  objQuestions.forEach((q, idx) => {
    const qNum = String(q.question_number);
    const weight = Number(q.score_weight || 1);
    if (bestSubsetIndices.has(idx)) {
      student.student_answers[qNum] = q.correct_answer || 'A';
      earnedObjPoints += weight;
    } else {
      student.student_answers[qNum] = getWrongAnswerForQuestion(q);
    }
  });

  const actualPgScore = totalObjWeight > 0 ? (earnedObjPoints / totalObjWeight) * 100 : 100;

  // 2. Adjust Essay scores to balance total blended score to match KKM exactly
  if (essayQuestions.length > 0 && essayWeight > 0) {
    // Formula: (actualPgScore * pgWeight / 100) + (targetEssayScore * essayWeight / 100) = kkm
    const targetEssayScore = (kkm - (actualPgScore * (pgWeight / 100))) / (essayWeight / 100);
    const targetEarnedEssay = Math.max(0, Math.min(totalEssayWeight, (targetEssayScore / 100) * totalEssayWeight));

    let remaining = Math.round(targetEarnedEssay * 100) / 100;
    essayQuestions.forEach((q, i) => {
      const qNum = String(q.question_number);
      const maxScore = Number(q.score_weight || 10);
      if (i === essayQuestions.length - 1) {
        student.essay_scores[qNum] = Math.max(0, Math.min(maxScore, Math.round(remaining * 100) / 100));
      } else {
        const val = Math.max(0, Math.min(maxScore, Math.round((targetEarnedEssay / totalEssayWeight) * maxScore * 100) / 100));
        student.essay_scores[qNum] = val;
        remaining = Math.round((remaining - val) * 100) / 100;
      }
    });
  } else if (essayQuestions.length > 0) {
    // If essayWeight is 0 but essay questions exist, set essay to proportional KKM
    essayQuestions.forEach(q => {
      const qNum = String(q.question_number);
      const maxScore = Number(q.score_weight || 10);
      student.essay_scores[qNum] = Math.round((kkm / 100) * maxScore * 100) / 100;
    });
  }

  syncStudentAnswerString(student);
  toast.success(`Jawaban ${student.name} berhasil diatur pas KKM (${kkm})!`);
}

async function resetStudentCorrection(student) {
  if (!student) return;
  const hasSavedSubmission = !!student.has_submitted || !!student.submission_id;
  const hasDraftAnswers = !!student.answer_string ||
    (student.student_answers && Object.keys(student.student_answers).length > 0) ||
    (student.essay_scores && Object.keys(student.essay_scores).length > 0) ||
    (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '');

  if (!hasSavedSubmission && !hasDraftAnswers) {
    toast.info(`Koreksi untuk ${student.name} sudah kosong.`);
    return;
  }

  const isConfirmed = await confirm({
    title: 'Kosongkan Koreksi Siswa',
    message: hasSavedSubmission
      ? `Kosongkan semua hasil koreksi dan nilai untuk "${student.name}"? Data lembar jawaban dan nilai siswa ini akan dihapus dari server.`
      : `Kosongkan draft jawaban untuk "${student.name}"?`,
    type: 'danger',
    confirmText: 'Ya, Kosongkan',
    cancelText: 'Batal'
  });

  if (!isConfirmed) return;

  if (hasSavedSubmission && activeExam.value?.id) {
    try {
      await api.post(`/teacher/exam-corrections/${activeExam.value.id}/reset-student/${student.id}`);
      toast.success(`Koreksi dan nilai ${student.name} berhasil dikosongkan!`);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Gagal mengosongkan koreksi siswa di server.');
      return;
    }
  } else {
    toast.success(`Draft jawaban ${student.name} berhasil dikosongkan!`);
  }

  // Clear local state
  student.has_submitted = false;
  student.submission_id = null;
  student.student_answers = {};
  student.answer_string = '';
  student.essay_scores = {};
  student.correct_pg_count = 0;
  student.wrong_pg_count = 0;
  student.pg_score = 0;
  student.essay_score = 0;
  student.total_score = null;
  student.remedial_score = null;
  student.is_passed = false;
}

async function resetAllCorrections() {
  if (!activeExam.value?.id) return;
  const isConfirmed = await confirm({
    title: 'Reset Semua Koreksi Siswa',
    message: `Apakah Anda yakin ingin mengosongkan/mereset SEMUA hasil koreksi siswa untuk ujian "${activeExam.value.title}"? Semua lembar jawaban dan nilai siswa pada ujian ini akan dihapus dari sistem.`,
    type: 'danger',
    confirmText: 'Ya, Reset Semua',
    cancelText: 'Batal'
  });

  if (!isConfirmed) return;

  try {
    await api.post(`/teacher/exam-corrections/${activeExam.value.id}/reset-all`);
    toast.success('Semua koreksi ujian berhasil dikosongkan!');
    await openExamDetail(activeExam.value.id);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mereset semua koreksi di server.');
  }
}

function copyQuickKeysToClipboard() {
  if (!quickKeyInput.value) return;
  navigator.clipboard.writeText(quickKeyInput.value).then(() => {
    toast.success('Deretan kunci jawaban PG berhasil disalin ke clipboard!');
  }).catch(() => {
    toast.info('Silakan salin manual dari kotak input.');
  });
}

onMounted(async () => {
  await Promise.all([fetchExams(), fetchMeta()]);
});

async function fetchMeta() {
  try {
    const res = await api.get('/teacher/exam-corrections/options');
    const optData = res?.data || res || {};
    classes.value = optData.classes || [];
    subjects.value = optData.subjects || [];

    if (optData.settings) {
      examForm.kkm = optData.settings.default_kkm ?? 75;
      examForm.pg_weight = optData.settings.default_pg_weight ?? 70;
      examForm.essay_weight = optData.settings.default_essay_weight ?? 30;
    }
  } catch (err) {
    console.error('Failed to load exam correction options:', err);
    try {
      const [clsRes, sbjRes] = await Promise.all([
        api.get('/teacher/classes'),
        api.get('/teacher/grade-options')
      ]);
      const gData = sbjRes?.data || sbjRes || {};
      const cData = clsRes?.data || clsRes || [];
      classes.value = gData.classes || cData.classes || (Array.isArray(cData) ? cData : []);
      subjects.value = gData.subjects || [];
    } catch (fallbackErr) {
      console.error(fallbackErr);
    }
  }
}

async function fetchExams() {
  loading.value = true;
  try {
    const res = await api.get('/teacher/exam-corrections');
    exams.value = res.data?.data?.data || res.data?.data || [];
  } catch (err) {
    toast.error('Gagal memuat daftar ujian.');
  } finally {
    loading.value = false;
  }
}

const filteredExams = computed(() => {
  return exams.value.filter(e => {
    if (filterClass.value && e.class_room_id !== filterClass.value) return false;
    if (filterSubject.value && e.subject_id !== filterSubject.value) return false;
    if (filterType.value && e.exam_type !== filterType.value) return false;
    if (searchQuery.value && !e.title.toLowerCase().includes(searchQuery.value.toLowerCase())) return false;
    return true;
  });
});

const overallAvgScore = computed(() => {
  if (!exams.value.length) return '0.0';
  const valid = exams.value.filter(e => e.avg_score > 0);
  if (!valid.length) return '0.0';
  const sum = valid.reduce((acc, e) => acc + Number(e.avg_score), 0);
  return (sum / valid.length).toFixed(1);
});

const totalPassedStudents = computed(() => {
  return exams.value.reduce((acc, e) => acc + (Number(e.passed_count) || 0), 0);
});

const totalRemedialStudents = computed(() => {
  return exams.value.reduce((acc, e) => {
    const totalSub = Number(e.submissions_count) || 0;
    const passed = Number(e.passed_count) || 0;
    return acc + Math.max(0, totalSub - passed);
  }, 0);
});

function examTypeLabel(type) {
  const map = {
    uh: 'Penilaian Harian',
    sts: 'Sumatif Tengah Smt',
    sas: 'Sumatif Akhir Smt',
    pat: 'PAT',
    am: 'Asesmen Madrasah',
    quiz: 'Kuis'
  };
  return map[type] || type?.toUpperCase() || '-';
}

function openCreateModal() {
  examForm.title = '';
  const classWithStudents = classes.value.find(c => (c.students_count || 0) > 0) || classes.value[0];
  examForm.class_room_id = classWithStudents?.id || '';
  examForm.subject_id = subjects.value[0]?.id || '';
  examForm.exam_type = 'uh';
  examForm.semester = 'ganjil';
  examForm.pg_count = 20;
  examForm.pg_point = 1;
  examForm.pg_complex_count = 0;
  examForm.pg_complex_point = 2;
  examForm.true_false_count = 0;
  examForm.true_false_point = 1;
  examForm.agree_disagree_count = 0;
  examForm.agree_disagree_point = 1;
  examForm.matching_count = 0;
  examForm.matching_point = 2;
  examForm.short_answer_count = 0;
  examForm.short_answer_point = 2;
  examForm.essay_count = 0;
  examForm.essay_point = 10;
  examForm.total_questions = 20;
  examForm.pg_weight = 100;
  examForm.essay_weight = 0;
  examForm.quick_keys = '';
  showCreateModal.value = true;
}

async function createExam() {
  creatingExam.value = true;
  try {
    const res = await api.post('/teacher/exam-corrections', examForm);
    toast.success('Paket ujian berhasil dibuat!');
    showCreateModal.value = false;
    await fetchExams();
    const createdId = res?.data?.id || res?.id;
    if (createdId) {
      openExamDetail(createdId);
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal membuat ujian.');
  } finally {
    creatingExam.value = false;
  }
}

async function openExamDetail(id) {
  loading.value = true;
  try {
    const res = await api.get(`/teacher/exam-corrections/${id}`);
    const data = res?.data || res || {};
    activeExam.value = data.exam;
    activeQuestions.value = data.questions || [];
    schoolProfile.value = data.school_profile || null;

    // Map students and construct their answer strings and essay scores
    activeStudents.value = (data.students || []).map(s => {
      const rawAnswers = (s.student_answers && typeof s.student_answers === 'object') ? { ...s.student_answers } : {};

      // Build answer_string strictly from single-choice PG Biasa questions
      let str = '';
      const pgList = (data.questions || []).filter(q => q.question_type === 'pg');
      pgList.forEach(q => {
        const val = rawAnswers[String(q.question_number)] || '';
        str += (val.length === 1 ? val : '');
      });

      const rawEssay = (s.essay_scores && typeof s.essay_scores === 'object') ? s.essay_scores : {};
      const essayScoresMap = {};
      Object.keys(rawEssay).forEach(k => {
        essayScoresMap[k] = Number(rawEssay[k]);
      });

      return {
        ...s,
        remedial_score: (s.remedial_score !== null && s.remedial_score !== undefined) ? Number(s.remedial_score) : null,
        student_answers: rawAnswers,
        answer_string: str,
        essay_scores: essayScoresMap
      };
    });

    // Populate quickKeyInput ONLY from single-letter PG Biasa questions
    let keysStr = '';
    activeQuestions.value.forEach(q => {
      if (q.question_type === 'pg') {
        keysStr += q.correct_answer || '';
      }
    });
    quickKeyInput.value = keysStr;

    activeTab.value = 'keys';
  } catch (err) {
    toast.error('Gagal memuat detail ujian.');
  } finally {
    loading.value = false;
  }
}

function closeExamDetail() {
  activeExam.value = null;
  fetchExams();
}

function onQuickKeyInput(e) {
  quickKeyInput.value = e.target.value.toUpperCase();
}

function applyQuickKeys() {
  const clean = quickKeyInput.value.toUpperCase().replace(/[^A-D]/g, '');
  quickKeyInput.value = clean;
  let keyIdx = 0;
  for (let i = 0; i < activeQuestions.value.length; i++) {
    if (activeQuestions.value[i].question_type === 'pg') {
      if (keyIdx < clean.length) {
        activeQuestions.value[i].correct_answer = clean[keyIdx];
        keyIdx++;
      }
    }
  }
  toast.success(`Kunci jawaban (${clean.length} butir) berhasil dipetakan ke butir soal PG Biasa!`);
}

async function saveAnswerKeys() {
  savingKeys.value = true;
  try {
    await api.post(`/teacher/exam-corrections/${activeExam.value.id}/keys`, {
      questions: activeQuestions.value
    });
    toast.success('Kunci jawaban & format tipe soal berhasil disimpan!');
    await openExamDetail(activeExam.value.id);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan kunci jawaban.');
  } finally {
    savingKeys.value = false;
  }
}

async function submitAllGrades() {
  gradingProcessing.value = true;
  try {
    const submissionsPayload = activeStudents.value.map(s => {
      const hasAnswersObj = s.student_answers && Object.keys(s.student_answers).length > 0;
      return {
        student_id: s.id,
        answers: hasAnswersObj ? s.student_answers : (s.answer_string || ''),
        essay_scores: s.essay_scores || {},
        remedial_score: (s.remedial_score !== null && s.remedial_score !== undefined && s.remedial_score !== '') ? Number(s.remedial_score) : null
      };
    });

    await api.post(`/teacher/exam-corrections/${activeExam.value.id}/grade`, {
      submissions: submissionsPayload
    });

    toast.success('Koreksi instan berhasil diproses!');
    await openExamDetail(activeExam.value.id);
    activeTab.value = 'grading';
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal memproses koreksi.');
  } finally {
    gradingProcessing.value = false;
  }
}

async function fetchAnalysis() {
  activeTab.value = 'analysis';
  try {
    const res = await api.get(`/teacher/exam-corrections/${activeExam.value.id}/analysis`);
    analysisData.value = res?.data || res;
  } catch (err) {
    toast.error('Gagal memuat data analisis butir soal.');
  }
}

async function syncToGrades() {
  syncingGrades.value = true;
  try {
    const res = await api.post(`/teacher/exam-corrections/${activeExam.value.id}/sync-grades`);
    toast.success(res?.message || res?.data?.message || 'Nilai berhasil disinkronkan ke Buku Nilai!');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyinkronkan nilai.');
  } finally {
    syncingGrades.value = false;
  }
}

function downloadExcel(id) {
  window.open(`/api/teacher/exam-corrections/${id}/export-excel`, '_blank');
}

async function confirmDelete(exam) {
  const isConfirmed = await confirm({
    title: 'Hapus Paket Ujian',
    message: `Apakah mase yakin ingin menghapus paket ujian "${exam.title}"? Semua butir soal, data lembar jawaban & nilai ujian ini akan ikut terhapus permanen.`,
    type: 'danger',
    confirmText: 'Ya, Hapus Paket Ujian',
    cancelText: 'Batal'
  });
  if (!isConfirmed) return;

  try {
    await api.delete(`/teacher/exam-corrections/${exam.id}`);
    toast.success('Paket ujian berhasil dihapus.');
    fetchExams();
  } catch (err) {
    toast.error('Gagal menghapus ujian.');
  }
}

// --- PRINT RECAP SHEET (KONSEP 1) HELPERS ---
const QUESTION_TYPE_LABELS = {
  pg: 'PG Biasa',
  pg_complex: 'PG Kompleks',
  true_false: 'Benar / Salah',
  agree_disagree: 'Setuju / Tidak',
  matching: 'Menjodohkan',
  short_answer: 'Isian Singkat',
  essay: 'Uraian / Essay'
};

const activeQuestionTypesList = computed(() => {
  if (!activeQuestions.value || activeQuestions.value.length === 0) return [];
  const typeMap = {};
  activeQuestions.value.forEach(q => {
    const t = q.question_type || 'pg';
    if (!typeMap[t]) {
      typeMap[t] = {
        key: t,
        label: QUESTION_TYPE_LABELS[t] || t,
        count: 0,
        maxScore: 0,
        questions: []
      };
    }
    typeMap[t].count++;
    typeMap[t].maxScore += Number(q.score_weight || 1);
    typeMap[t].questions.push(q);
  });
  return Object.values(typeMap);
});

function checkAnswerMatch(type, studentAns, correctAns) {
  if (studentAns === null || studentAns === undefined || correctAns === null || correctAns === undefined) return false;
  const cleanStudent = String(studentAns).trim();
  const cleanCorrect = String(correctAns).trim();
  if (!cleanStudent || !cleanCorrect) return false;

  if (type === 'pg' || type === 'true_false' || type === 'agree_disagree') {
    return cleanStudent.toUpperCase() === cleanCorrect.toUpperCase();
  }

  if (type === 'pg_complex') {
    const normalize = (str) => {
      let upper = str.toUpperCase();
      let parts = upper.includes(',') ? upper.split(',').map(s => s.trim()) : upper.replace(/\s+/g, '').split('');
      parts = parts.filter(Boolean);
      parts.sort();
      return Array.from(new Set(parts)).join(',');
    };
    return normalize(cleanStudent) === normalize(cleanCorrect);
  }

  if (type === 'matching') {
    const normalizePairs = (str) => {
      let clean = str.toUpperCase().replace(/\s+/g, '').replace(/[-:;]/g, '');
      let items = clean.split(',').filter(Boolean);
      items.sort();
      return items.join(',');
    };
    return normalizePairs(cleanStudent) === normalizePairs(cleanCorrect);
  }

  if (type === 'short_answer') {
    const studentNorm = cleanStudent.replace(/\s+/g, ' ').toLowerCase();
    const synonyms = cleanCorrect.split(/[|\/]/);
    for (const syn of synonyms) {
      if (studentNorm === syn.replace(/\s+/g, ' ').trim().toLowerCase()) {
        return true;
      }
    }
    return false;
  }

  return cleanStudent.toUpperCase() === cleanCorrect.toUpperCase();
}

function calculateStudentTypeScore(student, typeObj) {
  if (!student.has_submitted && !student.answer_string && Object.keys(student.student_answers || {}).length === 0) {
    return {
      earned: 0,
      max: typeObj.maxScore,
      percentage: 0,
      correctCount: 0,
      totalCount: typeObj.count
    };
  }

  let earned = 0;
  let correctCount = 0;

  typeObj.questions.forEach(q => {
    if (q.question_type === 'essay') {
      const sScore = Number(student.essay_scores?.[String(q.question_number)] || 0);
      const cap = Number(q.score_weight || 10);
      earned += Math.min(Math.max(0, sScore), cap);
    } else {
      const studentAns = student.student_answers?.[String(q.question_number)] ?? (student.answer_string ? student.answer_string[q.question_number - 1] : '');
      if (checkAnswerMatch(q.question_type, studentAns, q.correct_answer)) {
        earned += Number(q.score_weight || 1);
        correctCount++;
      }
    }
  });

  const max = typeObj.maxScore || 1;
  const percentage = Math.round((earned / max) * 100);

  return {
    earned: Math.round(earned * 10) / 10,
    max: Math.round(max * 10) / 10,
    percentage,
    correctCount,
    totalCount: typeObj.count
  };
}

function getStudentFinalGrade(student) {
  if (!student.has_submitted && !student.answer_string && Object.keys(student.student_answers || {}).length === 0 && student.total_score === null) {
    return '-';
  }
  const kkm = Number(activeExam.value?.kkm || 75);
  const rem = (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? Number(student.remedial_score) : null;
  const initial = Number(student.total_score || 0);

  if (rem !== null && rem >= kkm && initial < kkm) {
    return rem;
  }
  return initial;
}

function getStudentPrintStatus(student) {
  if (!student.has_submitted && !student.answer_string && Object.keys(student.student_answers || {}).length === 0 && student.total_score === null) {
    return 'Belum Ujian';
  }
  const kkm = Number(activeExam.value?.kkm || 75);
  const rem = (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? Number(student.remedial_score) : null;
  const initial = Number(student.total_score || 0);

  if (rem !== null && rem >= kkm && initial < kkm) {
    return 'TUNTAS (REM)';
  }
  if (initial >= kkm) {
    return 'TUNTAS';
  }
  return 'REMEDIAL';
}

function getPrintDateFormatted() {
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date().toLocaleDateString('id-ID', options);
}

function getExamTypeFullName(type) {
  const map = {
    uh: 'Penilaian Harian (UH)',
    sts: 'Sumatif Tengah Semester (STS)',
    sas: 'Sumatif Akhir Semester (SAS)',
    pat: 'Penilaian Akhir Tahun (PAT)',
    am: 'Asesmen Madrasah (AM)',
    quiz: 'Kuis / Latihan Harian'
  };
  return map[type] || 'Asesmen Pembelajaran';
}

const printStats = computed(() => {
  const students = activeStudents.value || [];
  if (students.length === 0) {
    return {
      total: 0,
      participated: 0,
      passedPure: 0,
      passedRemedial: 0,
      totalPassed: 0,
      remedialCount: 0,
      avgScore: 0,
      maxScore: 0,
      minScore: 0,
      passPercentage: 0
    };
  }

  const kkm = Number(activeExam.value?.kkm || 75);
  let passedPure = 0;
  let passedRemedial = 0;
  let remedialCount = 0;
  let totalScoreSum = 0;
  let maxScore = -1;
  let minScore = 999;
  let countedStudents = 0;

  students.forEach(s => {
    const initialScore = Number(s.total_score || 0);
    const hasRemedial = s.remedial_score !== null && s.remedial_score !== undefined && s.remedial_score !== '';
    const remScore = hasRemedial ? Number(s.remedial_score) : null;
    const effectiveGrade = (remScore !== null && remScore >= kkm) ? remScore : initialScore;

    if (s.has_submitted || s.answer_string || (s.student_answers && Object.keys(s.student_answers).length > 0) || s.total_score !== null) {
      countedStudents++;
      totalScoreSum += effectiveGrade;
      if (effectiveGrade > maxScore) maxScore = effectiveGrade;
      if (effectiveGrade < minScore) minScore = effectiveGrade;

      if (remScore !== null && remScore >= kkm && initialScore < kkm) {
        passedRemedial++;
      } else if (initialScore >= kkm) {
        passedPure++;
      } else {
        remedialCount++;
      }
    }
  });

  const totalPassed = passedPure + passedRemedial;
  const avg = countedStudents > 0 ? (totalScoreSum / countedStudents).toFixed(1) : 0;
  const passPct = countedStudents > 0 ? Math.round((totalPassed / countedStudents) * 100) : 0;

  return {
    total: students.length,
    participated: countedStudents,
    passedPure,
    passedRemedial,
    totalPassed,
    remedialCount,
    avgScore: avg,
    maxScore: maxScore >= 0 ? maxScore : 0,
    minScore: minScore <= 100 && minScore >= 0 ? minScore : 0,
    passPercentage: passPct
  };
});

function openPrintPreview() {
  if (!activeExam.value) {
    toast.error('Pilih paket ujian terlebih dahulu.');
    return;
  }
  showStudentModal.value = false;
  selectedStudent.value = null;
  showCreateModal.value = false;
  showPrintModal.value = true;
}

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
  return `/storage/${path.replace(/^\/?storage\//, '')}`;
}

function printDocument() {
  const printElem = document.getElementById('printableRecapSheet');
  if (!printElem) {
    window.print();
    return;
  }

  const content = printElem.innerHTML;
  const printWindow = window.open('', '_blank', 'width=1200,height=850');
  if (!printWindow) {
    window.print();
    return;
  }

  printWindow.document.open();
  printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Lembar Rekapitulasi Capaian Nilai Asesmen - ${activeExam.value?.title || 'Ujian'}</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 8mm 8mm 8mm 8mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: #ffffff !important;
      color: #0f172a;
      padding: 4px;
      font-size: 10px;
      line-height: 1.35;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .font-semibold { font-weight: 600; }
    .font-black { font-weight: 900; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .uppercase { text-transform: uppercase; }
    .underline { text-decoration: underline; }
    
    .flex { display: flex; }
    .flex-col { flex-direction: column; }
    .justify-between { justify-content: space-between; }
    .justify-center { justify-content: center; }
    .justify-end { justify-content: flex-end; }
    .items-center { align-items: center; }
    .items-start { align-items: flex-start; }
    .flex-1 { flex: 1 1 0%; }
    .flex-shrink-0 { flex-shrink: 0; }
    .gap-1 { gap: 4px; }
    .gap-1\\.5 { gap: 6px; }
    .gap-2 { gap: 8px; }
    .gap-3 { gap: 12px; }
    .gap-4 { gap: 16px; }
    .gap-5 { gap: 20px; }
    .gap-6 { gap: 24px; }
    .gap-8 { gap: 32px; }
    .gap-12 { gap: 48px; }
    .gap-x-8 { column-gap: 32px; }
    .gap-y-1\\.5 { row-gap: 6px; }
    .grid { display: grid; }
    .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    
    .w-full { width: 100%; }
    .w-8 { width: 32px; }
    .w-12 { width: 48px; }
    .w-14 { width: 56px; }
    .w-16 { width: 64px; }
    .w-18 { width: 72px; }
    .w-20 { width: 80px; }
    .w-24 { width: 96px; }
    .w-32 { width: 128px; }
    .w-36 { width: 144px; }
    .h-14 { height: 56px; }
    .h-16 { height: 64px; }
    .h-18 { height: 72px; }
    .h-20 { height: 80px; }
    
    .p-2 { padding: 8px; }
    .p-3 { padding: 12px; }
    .px-1\\.5 { padding-left: 6px; padding-right: 6px; }
    .px-2 { padding-left: 8px; padding-right: 8px; }
    .px-2\\.5 { padding-left: 10px; padding-right: 10px; }
    .px-3 { padding-left: 12px; padding-right: 12px; }
    .px-4 { padding-left: 16px; padding-right: 16px; }
    .py-1 { padding-top: 4px; padding-bottom: 4px; }
    .py-1\\.5 { padding-top: 6px; padding-bottom: 6px; }
    .py-2 { padding-top: 8px; padding-bottom: 8px; }
    .py-3 { padding-top: 12px; padding-bottom: 12px; }
    .py-6 { padding-top: 24px; padding-bottom: 24px; }
    .pb-2 { padding-bottom: 8px; }
    .pb-3 { padding-bottom: 12px; }
    .pt-2 { padding-top: 8px; }
    .pt-4 { padding-top: 16px; }
    .pt-6 { padding-top: 24px; }
    .pr-2 { padding-right: 8px; }
    .pr-6 { padding-right: 24px; }
    .pr-10 { padding-right: 40px; }
    .pr-14 { padding-right: 56px; }
    .mr-2 { margin-right: 8px; }
    .mb-1 { margin-bottom: 4px; }
    .mb-2 { margin-bottom: 8px; }
    .mb-4 { margin-bottom: 16px; }
    .mt-0\\.5 { margin-top: 2px; }
    .mt-1 { margin-top: 4px; }
    .mt-2 { margin-top: 8px; }
    
    .space-y-1 > * + * { margin-top: 4px; }
    .space-y-1\\.5 > * + * { margin-top: 6px; }
    .space-y-5 > * + * { margin-top: 20px; }
    
    .border { border: 1px solid #94a3b8; }
    .border-b { border-bottom: 1px solid #94a3b8; }
    .border-b-2 { border-bottom: 2px solid #0f172a; }
    .border-b-4 { border-bottom: 4px solid #0f172a; }
    .border-double { border-bottom-style: double; }
    .border-slate-200 { border-color: #e2e8f0; }
    .border-slate-300 { border-color: #cbd5e1; }
    .border-slate-900 { border-color: #0f172a; }
    .border-teal-200 { border-color: #99f6e4; }
    .border-teal-300 { border-color: #5eead4; }
    
    .rounded { border-radius: 4px; }
    .rounded-lg { border-radius: 8px; }
    .rounded-xl { border-radius: 12px; }
    .rounded-2xl { border-radius: 16px; }
    
    .bg-white { background-color: #ffffff; }
    .bg-slate-50 { background-color: #f8fafc !important; }
    .bg-slate-100 { background-color: #f1f5f9 !important; }
    .bg-slate-150 { background-color: #e6edf4 !important; }
    .bg-slate-200 { background-color: #e2e8f0 !important; }
    .bg-teal-50 { background-color: #f0fdfa !important; }
    .bg-teal-100 { background-color: #ccfbf1 !important; }
    .bg-teal-800 { background-color: #115e59 !important; color: white !important; }
    
    .text-white { color: #ffffff !important; }
    .text-slate-400 { color: #94a3b8; }
    .text-slate-500 { color: #64748b; }
    .text-slate-600 { color: #475569; }
    .text-slate-700 { color: #334155; }
    .text-slate-800 { color: #1e293b; }
    .text-slate-900 { color: #0f172a; }
    .text-emerald-700 { color: #047857; }
    .text-teal-700 { color: #0f766e; }
    .text-teal-800 { color: #115e59; }
    .text-teal-900 { color: #134e4a; }
    .text-rose-600 { color: #e11d48; }
    
    .text-xs { font-size: 10px; }
    .text-sm { font-size: 11px; }
    .text-base { font-size: 12px; }
    .text-lg { font-size: 14px; }
    .text-xl { font-size: 16px; }
    .text-2xl { font-size: 18px; }
    .text-\\[8px\\] { font-size: 8px; }
    .text-\\[9px\\] { font-size: 9px; }
    .text-\\[10px\\] { font-size: 10px; }
    .text-\\[11px\\] { font-size: 11px; }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      page-break-inside: auto;
    }
    tr {
      page-break-inside: avoid;
      page-break-after: auto;
    }
    th, td {
      border: 1px solid #94a3b8;
      padding: 3px 5px;
    }
    thead {
      display: table-header-group;
    }
    tfoot {
      display: table-footer-group;
    }
    img {
      max-width: 100%;
      height: auto;
      object-fit: contain;
    }
    .break-inside-avoid {
      page-break-inside: avoid;
      break-inside: avoid;
    }
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  </style>
</head>
<body>
  <div style="width: 100%; max-width: 100%;">
    ${content}
  </div>
</body>
</html>`);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 400);
}

function exportToWord() {
  if (!activeExam.value) {
    toast.error('Data ujian tidak ditemukan.');
    return;
  }

  // 1. Dapatkan Logo Madrasah (Base64 via Canvas jika memungkinkan, atau URL absolut)
  let logoImgHtml = '';
  try {
    const printImg = document.querySelector('#printableRecapSheet img');
    if (printImg && printImg.complete && printImg.naturalWidth > 0) {
      const canvas = document.createElement('canvas');
      canvas.width = printImg.naturalWidth;
      canvas.height = printImg.naturalHeight;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(printImg, 0, 0);
      const dataUrl = canvas.toDataURL('image/png');
      logoImgHtml = `<img src="${dataUrl}" width="75" height="75" style="width: 75px; height: 75px; object-fit: contain;" alt="Logo Madrasah" />`;
    }
  } catch (e) {
    console.warn('Canvas logo conversion fallback:', e);
  }

  if (!logoImgHtml) {
    const rawLogo = schoolProfile.value?.app_logo_url || schoolProfile.value?.app_logo;
    if (rawLogo) {
      const logoUrl = rawLogo.startsWith('http') ? rawLogo : (window.location.origin + getImageUrl(rawLogo));
      logoImgHtml = `<img src="${logoUrl}" width="75" height="75" style="width: 75px; height: 75px; object-fit: contain;" alt="Logo Madrasah" />`;
    } else {
      logoImgHtml = `<div style="width: 70px; height: 70px; line-height: 70px; text-align: center; background-color: #115e59; color: #ffffff; font-weight: 900; font-size: 16pt; border-radius: 8px;">MTS</div>`;
    }
  }

  // 2. Kolom Bentuk Soal Dinamis
  const qTypes = activeQuestionTypesList.value || [];
  let thQTypes = '';
  let thQTypesSub = '';

  if (qTypes.length > 0) {
    thQTypes = `<th colspan="${qTypes.length}" style="border: 1pt solid #94a3b8; padding: 4pt 3pt; background-color: #e2e8f0; color: #134e4a; font-weight: 900; font-size: 8.5pt; text-align: center;">Capaian Nilai per Bentuk Soal (Poin / Maks)</th>`;
    thQTypesSub = qTypes.map(t => `
      <th style="border: 1pt solid #94a3b8; padding: 3.5pt 2.5pt; background-color: #f8fafc; text-align: center; font-size: 8pt; min-width: 60pt;">
        <div style="font-weight: bold; color: #1e293b;">${t.label}</div>
        <div style="font-size: 7pt; color: #64748b; font-weight: normal;">(${t.count} Soal • Maks ${t.maxScore})</div>
      </th>
    `).join('');
  }

  // 3. Baris Data Siswa
  const students = activeStudents.value || [];
  const kkm = Number(activeExam.value?.kkm || 75);

  let rowsHtml = '';
  if (students.length === 0) {
    const totalCols = 8 + qTypes.length;
    rowsHtml = `<tr><td colspan="${totalCols}" align="center" style="border: 1pt solid #94a3b8; padding: 12pt; color: #94a3b8; font-style: italic;">Tidak ada data siswa pada kelas ini.</td></tr>`;
  } else {
    rowsHtml = students.map((student, idx) => {
      const typeScoresHtml = qTypes.map(t => {
        const score = calculateStudentTypeScore(student, t);
        return `
          <td align="center" style="border: 1pt solid #94a3b8; padding: 3pt 2pt; font-size: 8pt;">
            <div style="font-weight: bold; color: #1e293b;">${score.earned}</div>
            <div style="font-size: 7pt; color: #64748b;">(${score.percentage}%)</div>
          </td>
        `;
      }).join('');

      const initialScore = student.total_score !== null ? student.total_score : '-';
      const initialScoreColor = (student.total_score !== null && student.total_score < kkm) ? '#e11d48' : '#1e293b';
      const remScore = (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? student.remedial_score : '-';
      const finalGrade = getStudentFinalGrade(student);
      const status = getStudentPrintStatus(student);

      let statusHtml = '';
      if (status === 'TUNTAS') {
        statusHtml = '<b style="color: #047857;">TUNTAS</b>';
      } else if (status === 'TUNTAS (REM)') {
        statusHtml = '<b style="color: #0f766e;">TUNTAS (REM)</b>';
      } else if (status === 'REMEDIAL') {
        statusHtml = '<b style="color: #e11d48;">REMEDIAL</b>';
      } else {
        statusHtml = '<span style="color: #94a3b8;">BELUM UJIAN</span>';
      }

      return `
        <tr style="background-color: ${idx % 2 === 1 ? '#f8fafc' : '#ffffff'};">
          <td align="center" style="border: 1pt solid #94a3b8; padding: 3pt 2pt; font-weight: bold; color: #64748b; font-size: 8pt;">${idx + 1}</td>
          <td align="center" style="border: 1pt solid #94a3b8; padding: 3pt 2pt; font-family: monospace; font-size: 8pt; color: #475569;">${student.nisn || '-'}</td>
          <td align="left" style="border: 1pt solid #94a3b8; padding: 3pt 4pt; font-weight: bold; font-size: 8.5pt; color: #0f172a;">${student.name}</td>
          <td align="center" style="border: 1pt solid #94a3b8; padding: 3pt 2pt; font-weight: bold; font-size: 8pt; color: #475569;">${student.gender || '-'}</td>
          ${typeScoresHtml}
          <td align="center" style="border: 1pt solid #94a3b8; padding: 3pt 2pt; font-weight: bold; font-size: 8.5pt; color: ${initialScoreColor};">${initialScore}</td>
          <td align="center" style="border: 1pt solid #94a3b8; padding: 3pt 2pt; font-weight: bold; font-size: 8.5pt; color: #0f766e;">${remScore}</td>
          <td align="center" style="border: 1pt solid #94a3b8; padding: 3pt 2pt; font-weight: 900; font-size: 9pt; color: #0f172a; background-color: #f1f5f9;">${finalGrade}</td>
          <td align="center" style="border: 1pt solid #94a3b8; padding: 3pt 2pt; font-size: 7.5pt;">${statusHtml}</td>
        </tr>
      `;
    }).join('');
  }

  const komposisiStr = activeQuestionTypesList.value.map(t => `${t.count} ${t.label}`).join(', ');

  // 4. Dokumen HTML Lengkap Spesifik Microsoft Word (MSO Landscape A4)
  const wordContent = `
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8">
  <title>Lembar Rekapitulasi Capaian Nilai Asesmen - ${activeExam.value.title || 'Ujian'}</title>
  <!--[if gte mso 9]>
  <xml>
    <w:WordDocument>
      <w:View>Print</w:View>
      <w:Zoom>100</w:Zoom>
      <w:DoNotOptimizeForBrowser/>
    </w:WordDocument>
  </xml>
  <![endif]-->
  <style>
    @page Section1 {
      size: 841.9pt 595.3pt; /* A4 Landscape (29.7cm x 21.0cm) */
      mso-page-orientation: landscape;
      margin: 20.0pt 25.0pt 20.0pt 25.0pt;
    }
    div.Section1 {
      page: Section1;
      font-family: Arial, Helvetica, sans-serif;
      color: #0f172a;
    }
    table {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }
  </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #0f172a; margin: 0; padding: 0;">
  <div class="Section1">
    <!-- 1. KOP RESMI MADRASAH DENGAN LOGO RESMI -->
    <table width="100%" style="border-collapse: collapse; border: none; border-bottom: 3.5pt double #0f172a; margin-bottom: 8pt;">
      <tr>
        <td width="85" align="center" valign="middle" style="padding-right: 12pt; border: none;">
          ${logoImgHtml}
        </td>
        <td align="center" valign="middle" style="border: none; text-align: center;">
          <div style="font-size: 9pt; font-weight: bold; letter-spacing: 1.5pt; text-transform: uppercase; color: #475569;">
            ${schoolProfile.value?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM AL-HASANAH'}
          </div>
          <div style="font-size: 14.5pt; font-weight: 900; text-transform: uppercase; color: #0f172a; margin-top: 1pt;">
            ${schoolProfile.value?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH'}
          </div>
          <div style="font-size: 8.5pt; font-weight: 600; color: #475569; margin-top: 1pt;">
            ${schoolProfile.value?.school_tagline || 'Madrasah Tsanawiyah Al - Hasanah Ciomas'} • Status: ${schoolProfile.value?.school_accreditation || 'TERAKREDITASI A'}
          </div>
          <div style="font-size: 8pt; color: #475569; margin-top: 1pt;">
            ${schoolProfile.value?.school_address || 'Jl. Ciapus Sukamakmur No.05, Ciomas, Bogor'}
          </div>
          <div style="font-size: 7.5pt; color: #64748b; font-family: monospace; margin-top: 1pt;">
            Telp: ${schoolProfile.value?.school_phone || '081617666017'} • Email: ${schoolProfile.value?.school_email || 'mtsalhasanah.ciomas@gmail.com'}
          </div>
        </td>
      </tr>
    </table>

    <!-- 2. JUDUL LEMBAR REKAPITULASI -->
    <div style="text-align: center; margin-bottom: 8pt;">
      <div style="font-size: 11pt; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5pt; text-decoration: underline; color: #0f172a;">
        LEMBAR REKAPITULASI CAPAIAN NILAI ASESMEN PER BENTUK SOAL
      </div>
      <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #334155; margin-top: 2pt;">
        ${getExamTypeFullName(activeExam.value.exam_type)} • SEMESTER ${(activeExam.value.semester || 'ganjil').toUpperCase()} • TAHUN PELAJARAN ${activeExam.value.academic_year?.name || '2024/2025'}
      </div>
    </div>

    <!-- 3. METADATA ASESMEN -->
    <table width="100%" style="border-collapse: collapse; border: 1pt solid #cbd5e1; background-color: #f8fafc; margin-bottom: 8pt;">
      <tr>
        <td width="50%" valign="top" style="padding: 5pt 8pt; font-size: 8.5pt; color: #334155; line-height: 1.5; border: none;">
          <div><b>Mata Pelajaran :</b> <span style="font-weight: 900; color: #0f172a;">${activeExam.value.subject?.name || '-'}</span></div>
          <div><b>Kelas / Rombel :</b> <span style="font-weight: 900; color: #0f172a;">Kelas ${activeExam.value.class_room?.name || '-'}</span></div>
          <div><b>Guru Pengampu :</b> ${activeExam.value.teacher?.full_name || activeExam.value.teacher?.name || '-'}</div>
          <div><b>Nama Paket Ujian :</b> ${activeExam.value.title}</div>
        </td>
        <td width="50%" valign="top" style="padding: 5pt 8pt; font-size: 8.5pt; color: #334155; line-height: 1.5; border: none;">
          <div><b>Jenis Asesmen :</b> <span style="font-weight: 900; color: #0f172a;">${getExamTypeFullName(activeExam.value.exam_type)}</span></div>
          <div><b>KKM / KKTP :</b> <span style="font-weight: 900; color: #115e59; background-color: #ccfbf1; padding: 1pt 5pt; border: 1pt solid #5eead4;">${activeExam.value.kkm}</span></div>
          <div><b>Bobot Penilaian :</b> Objektif: ${activeExam.value.pg_weight}% | Uraian: ${activeExam.value.essay_weight}%</div>
          <div><b>Komposisi Soal :</b> ${activeExam.value.total_questions} Butir (${komposisiStr})</div>
        </td>
      </tr>
    </table>

    <!-- 4. TABEL CAPAIAN PER BENTUK SOAL -->
    <table width="100%" border="1" cellspacing="0" cellpadding="3" style="border-collapse: collapse; border: 1pt solid #94a3b8; margin-bottom: 8pt;">
      <thead>
        <tr style="background-color: #f1f5f9; color: #0f172a; text-transform: uppercase; font-size: 8pt; font-weight: bold; text-align: center;">
          <th rowspan="2" style="border: 1pt solid #94a3b8; padding: 4pt 2pt; width: 22pt;">No</th>
          <th rowspan="2" style="border: 1pt solid #94a3b8; padding: 4pt 2pt; width: 65pt;">NISN</th>
          <th rowspan="2" style="border: 1pt solid #94a3b8; padding: 4pt 4pt; text-align: left; min-width: 120pt;">Nama Siswa</th>
          <th rowspan="2" style="border: 1pt solid #94a3b8; padding: 4pt 2pt; width: 22pt;">L/P</th>
          ${thQTypes}
          <th rowspan="2" style="border: 1pt solid #94a3b8; padding: 4pt 2pt; width: 42pt;">Nilai Asli</th>
          <th rowspan="2" style="border: 1pt solid #94a3b8; padding: 4pt 2pt; width: 42pt;">Nilai Rem.</th>
          <th rowspan="2" style="border: 1pt solid #94a3b8; padding: 4pt 2pt; width: 45pt; background-color: #e2e8f0; font-weight: 900;">Nilai Akhir</th>
          <th rowspan="2" style="border: 1pt solid #94a3b8; padding: 4pt 2pt; width: 60pt;">Keterangan</th>
        </tr>
        ${thQTypesSub ? `<tr>${thQTypesSub}</tr>` : ''}
      </thead>
      <tbody>
        ${rowsHtml}
      </tbody>
    </table>

    <!-- 5. REKAPITULASI KETUNTASAN KLASIKAL -->
    <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #1e293b; margin-top: 6pt; margin-bottom: 3pt;">
      Rekapitulasi Ketuntasan Klasikal:
    </div>
    <table width="100%" border="1" cellspacing="0" cellpadding="4" style="border-collapse: collapse; border: 1pt solid #cbd5e1; margin-bottom: 10pt;">
      <tr>
        <td width="25%" style="border: 1pt solid #cbd5e1; background-color: #f8fafc; padding: 4pt 6pt; font-size: 8pt;">
          <span style="color: #64748b; font-size: 7.5pt; display: block;">Total Siswa Peserta</span>
          <b style="color: #1e293b; font-size: 9pt;">${printStats.value.participated} / ${printStats.value.total} Siswa</b>
        </td>
        <td width="25%" style="border: 1pt solid #cbd5e1; background-color: #f8fafc; padding: 4pt 6pt; font-size: 8pt;">
          <span style="color: #64748b; font-size: 7.5pt; display: block;">Tuntas (Murni + Rem)</span>
          <b style="color: #047857; font-size: 9pt;">${printStats.value.totalPassed} Siswa (${printStats.value.passPercentage}%)</b>
        </td>
        <td width="25%" style="border: 1pt solid #cbd5e1; background-color: #f8fafc; padding: 4pt 6pt; font-size: 8pt;">
          <span style="color: #64748b; font-size: 7.5pt; display: block;">Perlu Remedial</span>
          <b style="color: #e11d48; font-size: 9pt;">${printStats.value.remedialCount} Siswa</b>
        </td>
        <td width="25%" style="border: 1pt solid #cbd5e1; background-color: #f8fafc; padding: 4pt 6pt; font-size: 8pt;">
          <span style="color: #64748b; font-size: 7.5pt; display: block;">Rata-rata / Tertinggi / Terendah</span>
          <b style="color: #1e293b; font-size: 9pt;">${printStats.value.avgScore} / ${printStats.value.maxScore} / ${printStats.value.minScore}</b>
        </td>
      </tr>
    </table>

    <!-- 6. LEMBAR TANDA TANGAN RESMI -->
    <table width="100%" style="border-collapse: collapse; border: none; margin-top: 10pt;">
      <tr>
        <td colspan="2" align="right" style="border: none; padding-bottom: 8pt; font-size: 8.5pt; color: #1e293b;">
          Ciomas, ${getPrintDateFormatted()}
        </td>
      </tr>
      <tr>
        <td width="50%" align="center" valign="top" style="border: none; font-size: 8.5pt; color: #1e293b;">
          <b>Mengetahui,</b><br>
          Kepala MTs Al - Hasanah<br><br><br><br><br>
          <b style="text-decoration: underline; font-size: 9.5pt; color: #0f172a;">${schoolProfile.value?.principal_name || 'Kepala Madrasah'}</b><br>
          <span style="font-family: monospace; font-size: 8pt; color: #475569;">NIP: ${schoolProfile.value?.principal_nip || '-'}</span>
        </td>
        <td width="50%" align="center" valign="top" style="border: none; font-size: 8.5pt; color: #1e293b;">
          <b>Guru Pengampu,</b><br>
          Mata Pelajaran ${activeExam.value.subject?.name || ''}<br><br><br><br><br>
          <b style="text-decoration: underline; font-size: 9.5pt; color: #0f172a;">${activeExam.value.teacher?.full_name || activeExam.value.teacher?.name || 'Guru Mata Pelajaran'}</b><br>
          <span style="font-family: monospace; font-size: 8pt; color: #475569;">NIP: ${activeExam.value.teacher?.nip || '-'}</span>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
  `;

  // 5. Unduh Dokumen Word (.doc)
  try {
    const blob = new Blob(['\ufeff' + wordContent], {
      type: 'application/msword;charset=utf-8'
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    const safeMapel = (activeExam.value.subject?.name || 'Mapel').replace(/[^a-zA-Z0-9_-]/g, '_');
    const safeKelas = (activeExam.value.class_room?.name || 'Kelas').replace(/[^a-zA-Z0-9_-]/g, '_');
    a.href = url;
    a.download = `Rekap_Nilai_${safeMapel}_${safeKelas}.doc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    toast.success('Lembar rekap berhasil dikonversi dan diunduh ke format Word (.doc)');
  } catch (err) {
    console.error('Word export error:', err);
    toast.error('Gagal mengunduh file Word.');
  }
}
</script>
