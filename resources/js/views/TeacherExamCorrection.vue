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

        <!-- Navigation Tabs -->
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
      </div>

      <!-- SUB-TAB 1: KUNCI JAWABAN & BOBOT -->
      <div v-if="activeTab === 'keys'" class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-6">
        <!-- Quick String Input Bar -->
        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
            <div>
              <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">⚡ Input Kunci Jawaban Cepat (Deret Huruf PG)</label>
              <p class="text-xs text-slate-500 font-medium">Ketik atau paste deretan kunci jawaban pilihan ganda sekaligus (misal: <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-teal-700 font-mono font-bold">ABCDABCDAB...</code>)</p>
            </div>
            <span class="text-xs font-mono font-bold px-3 py-1 rounded-xl bg-white border border-slate-200 text-teal-700">
              {{ quickKeyInput.length }} / {{ pgQuestionsCount }} Karakter PG
            </span>
          </div>

          <div class="flex items-center gap-2">
            <input
              v-model="quickKeyInput"
              type="text"
              :maxlength="pgQuestionsCount"
              placeholder="Contoh: ABCDEABCDA..."
              class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono font-bold text-slate-800 tracking-widest focus:ring-2 focus:ring-teal-400 uppercase"
              @input="onQuickKeyInput"
            />
            <button
              @click="applyQuickKeys"
              class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs flex-shrink-0 transition-all shadow-sm cursor-pointer"
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
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 bg-teal-50/60 rounded-2xl border border-teal-100">
          <div>
            <h3 class="text-xs font-black text-teal-950 uppercase tracking-wider">🎯 Mode Koreksi Siswa (Cepat & Detail)</h3>
            <p class="text-xs text-teal-700 mt-0.5 font-medium">
              Ketik deretan jawaban di kolom, atau klik tombol <strong class="text-teal-900 bg-white px-1.5 py-0.5 rounded border border-teal-200">Form Jawaban</strong>. Untuk siswa remedial, ketik nilai perbaikan pada kolom <strong class="text-teal-900">Nilai Remedial</strong> lalu klik <strong class="text-teal-900">Simpan & Hitung Koreksi</strong>.
            </p>
          </div>

          <button
            @click="submitAllGrades"
            :disabled="gradingProcessing"
            class="px-7 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-teal-600/20 flex items-center gap-2 cursor-pointer disabled:opacity-50 flex-shrink-0"
          >
            <Zap class="w-4 h-4" />
            <span>{{ gradingProcessing ? 'Memproses Koreksi...' : 'Simpan & Hitung Koreksi' }}</span>
          </button>
        </div>

        <!-- Student Answer Rows -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
              <tr>
                <th class="px-4 py-3.5 w-12 text-center">No</th>
                <th class="px-4 py-3.5">Nama Siswa</th>
                <th class="px-4 py-3.5">Jawaban Siswa ({{ pgQuestionsCount }} Butir Objektif)</th>
                <th v-if="essayQuestionsCount > 0" class="px-4 py-3.5 text-center">Nilai Uraian / Essay</th>
                <th class="px-4 py-3.5 text-center">Benar / Salah</th>
                <th class="px-4 py-3.5 text-center">Nilai Ujian</th>
                <th class="px-4 py-3.5 text-center">Nilai Remedial</th>
                <th class="px-4 py-3.5 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(student, idx) in activeStudents" :key="student.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="font-bold text-slate-800 font-lexend">{{ student.name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">NISN: {{ student.nisn || '-' }} • {{ student.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      @click="openStudentModal(student, idx)"
                      class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer flex-shrink-0 shadow-2xs"
                      :class="hasComplexQuestions ? 'bg-teal-600 hover:bg-teal-700 text-white shadow-teal-600/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200'"
                      :title="hasComplexQuestions ? 'Buka form interaktif untuk mengisi PGK, B/S, Menjodohkan, Isian' : 'Buka form jawaban lengkap'"
                    >
                      <FileText class="w-3.5 h-3.5" />
                      <span>{{ hasComplexQuestions ? 'Form Jawaban' : 'Detail' }}</span>
                    </button>

                    <input
                      v-model="student.answer_string"
                      @input="onAnswerStringInput(student)"
                      type="text"
                      :maxlength="pgQuestionsCount"
                      :placeholder="pgQuestionsCount > 0 ? `Ketik ${pgQuestionsCount} jawaban... (ABCD...)` : 'Tidak ada soal objektif'"
                      :disabled="pgQuestionsCount === 0"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 tracking-widest uppercase focus:ring-2 focus:ring-teal-400 disabled:opacity-40"
                    />
                    <span class="text-[10px] font-mono text-slate-400 font-bold flex-shrink-0 w-12 text-right">
                      {{ (student.answer_string || '').length }}/{{ pgQuestionsCount }}
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- 1-Click Sync to Grades -->
          <div class="p-6 rounded-3xl bg-indigo-50/60 border border-indigo-100 space-y-4">
            <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-600/20">
              <Send class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm font-black text-indigo-950 font-lexend uppercase tracking-wider">Sinkronkan ke Buku Nilai / Rapor</h3>
              <p class="text-xs text-indigo-700 mt-1 font-medium">Kirim nilai hasil koreksi ujian ini secara otomatis ke modul Nilai Siswa (Gradebook) tanpa perlu menginput ulang secara manual.</p>
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
          <div class="p-6 rounded-3xl bg-teal-50/60 border border-teal-100 space-y-4">
            <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20">
              <FileSpreadsheet class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm font-black text-teal-950 font-lexend uppercase tracking-wider">Download Rekap Nilai (Excel)</h3>
              <p class="text-xs text-teal-700 mt-1 font-medium">Unduh laporan lengkap berisikan daftar siswa, jawaban per nomor, perolehan nilai, serta status kelulusan dalam format file Excel (.xlsx).</p>
            </div>

            <button
              @click="downloadExcel(activeExam.id)"
              class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-teal-600/20 flex items-center justify-center gap-2 cursor-pointer"
            >
              <Download class="w-4 h-4" />
              <span>Unduh Rekap Nilai Excel</span>
            </button>
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
        <div class="px-8 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/70 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-teal-600/20 font-lexend">
              {{ selectedStudentIndex + 1 }}
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-base font-black text-slate-800 font-lexend">{{ selectedStudent.name }}</h2>
                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-700">NISN: {{ selectedStudent.nisn || '-' }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="selectedStudent.gender === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700'">
                  {{ selectedStudent.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                </span>
              </div>
              <p class="text-xs text-slate-400 font-medium mt-0.5">Siswa ke-{{ selectedStudentIndex + 1 }} dari {{ activeStudents.length }} siswa • Kelas {{ activeExam?.class_room?.name }}</p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="prevStudent"
              :disabled="selectedStudentIndex <= 0"
              class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all disabled:opacity-30 cursor-pointer flex items-center gap-1 shadow-2xs"
            >
              <ChevronLeft class="w-4 h-4" />
              <span>Sebelumnya</span>
            </button>

            <button
              type="button"
              @click="nextStudent"
              :disabled="selectedStudentIndex >= activeStudents.length - 1"
              class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all disabled:opacity-30 cursor-pointer flex items-center gap-1 shadow-2xs"
            >
              <span>Berikutnya</span>
              <ChevronRight class="w-4 h-4" />
            </button>

            <button
              @click="closeStudentModal"
              class="w-9 h-9 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors border border-slate-100 shadow-sm cursor-pointer ml-2"
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
                  :placeholder="`0-${q.score_weight || 10}`"
                  class="w-full bg-white border border-amber-300 rounded-lg px-2 py-1 text-center text-xs font-bold text-amber-900 focus:ring-1 focus:ring-amber-400"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-8 py-4 border-t border-slate-100 flex justify-between items-center bg-slate-50/50 flex-shrink-0">
          <span class="text-xs text-slate-400 font-medium">Jawaban disimpan ke draft lembar koreksi. Klik "Simpan & Hitung Koreksi" untuk menghitung nilai.</span>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="closeStudentModal"
              class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Selesai & Tutup
            </button>
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
  Sparkles
} from 'lucide-vue-next';

const toast = useToast();

const loading = ref(false);
const exams = ref([]);
const classes = ref([]);
const subjects = ref([]);

const filterClass = ref('');
const filterSubject = ref('');
const filterType = ref('');
const searchQuery = ref('');

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

function removeQuestion(qNum) {
  if (activeQuestions.value.length <= 1) {
    toast.error('Minimal harus ada 1 butir soal dalam paket ujian.');
    return;
  }
  if (confirm(`Apakah Anda yakin ingin menghapus butir soal No. ${qNum}?`)) {
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
  for (let i = 1; i <= activeExam.value.total_questions; i++) {
    const val = student.student_answers?.[String(i)] || '';
    str += val;
  }
  student.answer_string = str;
}

function onAnswerStringInput(student) {
  const clean = (student.answer_string || '').toUpperCase();
  for (let i = 0; i < clean.length; i++) {
    if (i < activeExam.value.total_questions) {
      if (!student.student_answers) student.student_answers = {};
      student.student_answers[String(i + 1)] = clean[i];
    }
  }
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

    // Map students and construct their answer strings and essay scores
    activeStudents.value = (data.students || []).map(s => {
      let str = '';
      if (s.student_answers && typeof s.student_answers === 'object') {
        for (let i = 1; i <= activeExam.value.total_questions; i++) {
          str += s.student_answers[String(i)] || '';
        }
      }

      const rawEssay = (s.essay_scores && typeof s.essay_scores === 'object') ? s.essay_scores : {};
      const essayScoresMap = {};
      Object.keys(rawEssay).forEach(k => {
        essayScoresMap[k] = Number(rawEssay[k]);
      });

      const rawAnswers = (s.student_answers && typeof s.student_answers === 'object') ? { ...s.student_answers } : {};

      return {
        ...s,
        remedial_score: (s.remedial_score !== null && s.remedial_score !== undefined) ? Number(s.remedial_score) : null,
        student_answers: rawAnswers,
        answer_string: str,
        essay_scores: essayScoresMap
      };
    });

    // Populate quickKeyInput from existing keys of PG questions
    let keysStr = '';
    activeQuestions.value.forEach(q => {
      if (q.question_type !== 'essay') {
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
  const clean = quickKeyInput.value.toUpperCase();
  let keyIdx = 0;
  for (let i = 0; i < activeQuestions.value.length; i++) {
    if (activeQuestions.value[i].question_type !== 'essay') {
      if (keyIdx < clean.length) {
        activeQuestions.value[i].correct_answer = clean[keyIdx];
        keyIdx++;
      }
    }
  }
  toast.success('Kunci jawaban deret huruf berhasil dipetakan ke kisi-kisi PG!');
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
  if (confirm(`Apakah mase yakin ingin menghapus paket ujian "${exam.title}"? Semua data jawaban & nilai ujian ini akan ikut terhapus.`)) {
    try {
      await api.delete(`/teacher/exam-corrections/${exam.id}`);
      toast.success('Paket ujian berhasil dihapus.');
      fetchExams();
    } catch (err) {
      toast.error('Gagal menghapus ujian.');
    }
  }
}
</script>
