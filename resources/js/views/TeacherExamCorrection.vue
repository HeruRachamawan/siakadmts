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
            <option v-for="c in classes" :key="c.id" :value="c.id">Kelas {{ c.name }}</option>
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
              <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">⚡ Input Kunci Jawaban Cepat (Deret Huruf)</label>
              <p class="text-xs text-slate-500 font-medium">Ketik atau paste deretan kunci jawaban sekaligus (misal: <code class="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-teal-700 font-mono font-bold">ABCDABCDAB...</code>)</p>
            </div>
            <span class="text-xs font-mono font-bold px-3 py-1 rounded-xl bg-white border border-slate-200 text-teal-700">
              {{ quickKeyInput.length }} / {{ activeExam.total_questions }} Karakter
            </span>
          </div>

          <div class="flex items-center gap-2">
            <input
              v-model="quickKeyInput"
              type="text"
              :maxlength="activeExam.total_questions"
              placeholder="Contoh: ABCDEABCDA..."
              class="w-full bg-white border border-slate-300 rounded-xl px-4 py-2.5 text-sm font-mono font-bold text-slate-800 tracking-widest focus:ring-2 focus:ring-teal-400 uppercase"
              @input="onQuickKeyInput"
            />
            <button
              @click="applyQuickKeys"
              class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs flex-shrink-0 transition-all shadow-sm cursor-pointer"
            >
              Terapkan ke Kisi-kisi
            </button>
          </div>
        </div>

        <!-- Interactive Question Cards Grid -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Kisi-kisi Butir Soal ({{ activeQuestions.length }} Nomor)</h3>
            <span class="text-xs text-slate-400 font-medium">Pilih opsi A / B / C / D / E untuk masing-masing butir soal</span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-10 gap-2.5">
            <div
              v-for="q in activeQuestions"
              :key="q.id || q.question_number"
              class="p-2.5 rounded-2xl border transition-all text-center space-y-2"
              :class="q.correct_answer ? 'bg-teal-50/60 border-teal-200' : 'bg-slate-50 border-slate-200'"
            >
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-black font-lexend text-slate-700">No. {{ q.question_number }}</span>
                <span v-if="q.question_type === 'essay'" class="text-[9px] font-black px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">Essay</span>
              </div>

              <!-- Options Buttons -->
              <div v-if="q.question_type !== 'essay'" class="grid grid-cols-4 gap-1">
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

              <div v-else class="space-y-1">
                <input
                  v-model.number="q.score_weight"
                  type="number"
                  placeholder="Maks Skor"
                  class="w-full bg-white border border-slate-200 rounded-lg px-1.5 py-1 text-center text-xs font-bold text-slate-800"
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
            <h3 class="text-xs font-black text-teal-950 uppercase tracking-wider">🎯 Mode Koreksi Cepat (Matriks Jawaban Siswa)</h3>
            <p class="text-xs text-teal-700 mt-0.5 font-medium">Ketik deretan jawaban siswa di baris masing-masing (misal: <code class="bg-white px-1 py-0.5 rounded font-mono font-bold">ABCD...</code>). Skor dan status kelulusan langsung dihitung saat tombol simpan diklik.</p>
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
                <th class="px-4 py-3.5">Deret Jawaban Siswa ({{ activeExam.total_questions }} Karakter)</th>
                <th class="px-4 py-3.5 text-center">Benar / Salah</th>
                <th class="px-4 py-3.5 text-center">Nilai Akhir</th>
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
                    <input
                      v-model="student.answer_string"
                      type="text"
                      :maxlength="activeExam.total_questions"
                      placeholder="Ketik jawaban siswa... misal: ABCDE..."
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 tracking-widest uppercase focus:ring-2 focus:ring-teal-400"
                    />
                    <span class="text-[10px] font-mono text-slate-400 font-bold flex-shrink-0 w-12 text-right">
                      {{ (student.answer_string || '').length }}/{{ activeExam.total_questions }}
                    </span>
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
                <td class="px-4 py-3 text-center">
                  <span
                    v-if="student.has_submitted"
                    :class="student.is_passed ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                    class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider border shadow-2xs inline-block"
                  >
                    {{ student.is_passed ? '🟢 TUNTAS' : '🔴 REMEDIAL' }}
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
                <option v-for="c in classes" :key="c.id" :value="c.id">Kelas {{ c.name }} (Tingkat {{ c.grade_level }})</option>
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

          <!-- Total Questions & KKM -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Jumlah Soal *</label>
              <input
                v-model.number="examForm.total_questions"
                type="number"
                min="1"
                max="100"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 text-center focus:ring-2 focus:ring-teal-400"
              />
            </div>

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
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Bobot PG (%) *</label>
              <input
                v-model.number="examForm.pg_weight"
                type="number"
                min="0"
                max="100"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 text-center focus:ring-2 focus:ring-teal-400"
              />
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
  X
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
  total_questions: 20,
  kkm: 75,
  pg_weight: 70,
  essay_weight: 30,
  quick_keys: ''
});

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

onMounted(async () => {
  await Promise.all([fetchExams(), fetchMeta()]);
});

async function fetchMeta() {
  try {
    const [clsRes, sbjRes] = await Promise.all([
      api.get('/teacher/classes'),
      api.get('/teacher/grade-options')
    ]);
    classes.value = clsRes.data?.data || clsRes.data || [];
    subjects.value = sbjRes.data?.subjects || [];
  } catch (err) {
    console.error('Failed to load classes or subjects:', err);
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
  examForm.class_room_id = classes.value[0]?.id || '';
  examForm.subject_id = subjects.value[0]?.id || '';
  examForm.exam_type = 'uh';
  examForm.semester = 'ganjil';
  examForm.total_questions = 20;
  examForm.kkm = 75;
  examForm.pg_weight = 70;
  examForm.essay_weight = 30;
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
    if (res.data?.data?.id) {
      openExamDetail(res.data.data.id);
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
    const data = res.data?.data;
    activeExam.value = data.exam;
    activeQuestions.value = data.questions || [];

    // Map students and construct their answer strings if already submitted
    activeStudents.value = (data.students || []).map(s => {
      let str = '';
      if (s.student_answers && typeof s.student_answers === 'object') {
        for (let i = 1; i <= activeExam.value.total_questions; i++) {
          str += s.student_answers[String(i)] || '';
        }
      }
      return {
        ...s,
        answer_string: str
      };
    });

    // Populate quickKeyInput from existing keys
    let keysStr = '';
    activeQuestions.value.forEach(q => {
      keysStr += q.correct_answer || '';
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
  for (let i = 0; i < activeQuestions.value.length; i++) {
    if (i < clean.length) {
      activeQuestions.value[i].correct_answer = clean[i];
    }
  }
  toast.success('Kunci jawaban deret huruf berhasil dipetakan ke kisi-kisi!');
}

async function saveAnswerKeys() {
  savingKeys.value = true;
  try {
    await api.post(`/teacher/exam-corrections/${activeExam.value.id}/keys`, {
      questions: activeQuestions.value
    });
    toast.success('Kunci jawaban berhasil disimpan & nilai diperbarui!');
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
    const submissionsPayload = activeStudents.value.map(s => ({
      student_id: s.id,
      answers: s.answer_string || ''
    }));

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
    analysisData.value = res.data?.data;
  } catch (err) {
    toast.error('Gagal memuat data analisis butir soal.');
  }
}

async function syncToGrades() {
  syncingGrades.value = true;
  try {
    const res = await api.post(`/teacher/exam-corrections/${activeExam.value.id}/sync-grades`);
    toast.success(res.data?.message || 'Nilai berhasil disinkronkan ke Buku Nilai!');
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
