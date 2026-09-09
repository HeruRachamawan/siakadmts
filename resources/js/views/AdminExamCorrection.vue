<template>
  <div class="space-y-6 font-inter pb-12">
    <!-- Header Card -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 bg-slate-900 rounded-2xl flex items-center justify-center shadow-lg shadow-slate-900/20 flex-shrink-0">
          <CheckSquare class="w-6 h-6 text-teal-400" />
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Monitoring Koreksi Ujian & Asesmen Madrasah</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Pantau kepatuhan guru dalam mengoreksi ujian, rekapitulasi ketuntasan madrasah, dan analisis butir soal terpusat.</p>
        </div>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <button
          @click="openSettingsModal"
          class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-2xl text-xs transition-all border border-slate-200 shadow-xs flex items-center gap-2 cursor-pointer"
        >
          <Settings class="w-4 h-4 text-slate-600" />
          <span>Pengaturan Asesmen</span>
        </button>

        <button
          @click="exportAllMadrasah"
          class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-slate-900/20 flex items-center gap-2 cursor-pointer"
        >
          <FileSpreadsheet class="w-4 h-4 text-teal-400" />
          <span>Export Rekap 1 Madrasah (Excel)</span>
        </button>
      </div>
    </div>


    <!-- Overview Stats Bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
          <BookOpen class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Paket Ujian</span>
          <div class="text-lg font-black text-slate-800 font-lexend">{{ summary?.total_exams || 0 }} Paket</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
          <Award class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Madrasah</span>
          <div class="text-lg font-black text-blue-600 font-lexend">{{ summary?.avg_score || '0.00' }}</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tingkat Ketuntasan</span>
          <div class="text-lg font-black text-emerald-600 font-lexend">{{ summary?.pass_rate || 0 }}%</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
          <Users class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kepatuhan Guru</span>
          <div class="text-lg font-black text-purple-600 font-lexend">{{ summary?.teachers_stats?.compliance_percentage || 0 }}%</div>
        </div>
      </div>
    </div>

    <!-- Master Assessment Monitoring Table -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 space-y-4">
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
            <option value="sts">Asesmen Sumatif Tengah Semester (ASTS)</option>
            <option value="sas">Asesmen Sumatif Akhir Semester (ASAS)</option>
            <option value="pat">Penilaian Akhir Tahun (PAT)</option>
            <option value="am">Asesmen Madrasah (AM)</option>
            <option value="quiz">Kuis / Latihan</option>
          </select>
        </div>

        <div class="relative w-full md:w-64">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari ujian, mapel, guru..."
            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-teal-400"
          />
          <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
        </div>
      </div>

      <!-- Table Content -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-100">
            <tr>
              <th class="px-4 py-3.5">Judul Ujian</th>
              <th class="px-4 py-3.5">Guru Pengampu</th>
              <th class="px-4 py-3.5">Mapel & Kelas</th>
              <th class="px-4 py-3.5 text-center">Jml Siswa</th>
              <th class="px-4 py-3.5 text-center">Rata-rata Nilai</th>
              <th class="px-4 py-3.5 text-center">Ketuntasan (% Tuntas)</th>
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
                  <span class="text-[10px] text-slate-400 font-medium">KKM {{ exam.kkm }} • {{ exam.total_questions }} Soal</span>
                </div>
              </td>
              <td class="px-4 py-4">
                <div class="font-bold text-slate-800">{{ exam.teacher?.name || '-' }}</div>
                <div class="text-[10px] text-slate-400 font-mono">NIP: {{ exam.teacher?.nip || '-' }}</div>
              </td>
              <td class="px-4 py-4">
                <div class="font-bold text-slate-800">{{ exam.subject?.name || '-' }}</div>
                <div class="text-[11px] text-slate-400 font-medium">Kelas {{ exam.class_room?.name || '-' }}</div>
              </td>
              <td class="px-4 py-4 text-center font-bold text-slate-700">
                {{ exam.submissions_count || 0 }} Siswa
              </td>
              <td class="px-4 py-4 text-center">
                <span class="text-sm font-black font-lexend" :class="exam.avg_score >= exam.kkm ? 'text-emerald-600' : 'text-amber-600'">
                  {{ exam.avg_score || '0.00' }}
                </span>
              </td>
              <td class="px-4 py-4 text-center">
                <div class="space-y-1">
                  <div class="text-[10px] font-bold">
                    <span class="text-emerald-600">{{ exam.passed_count || 0 }} Tuntas</span> /
                    <span class="text-rose-600">{{ exam.remedial_count || 0 }} Remedial</span>
                  </div>
                  <div class="w-24 mx-auto bg-rose-200 h-1.5 rounded-full overflow-hidden flex">
                    <div class="bg-emerald-500 h-full" :style="{ width: getPassPercent(exam) + '%' }"></div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    @click="inspectExam(exam.id)"
                    title="Lihat Detail & Opsi Cetak Lengkap"
                    class="px-3 py-1.5 bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer"
                  >
                    <Eye class="w-3.5 h-3.5" />
                    <span>Detail</span>
                  </button>

                  <button
                    @click="openRecapPrint(exam.id)"
                    title="Cetak Cepat Rekap Nilai Ujian"
                    class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer"
                  >
                    <Printer class="w-4 h-4" />
                  </button>

                  <button
                    @click="downloadExcel(exam.id)"
                    title="Export Excel Rekap Kelas"
                    class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-colors cursor-pointer"
                  >
                    <Download class="w-4 h-4" />
                  </button>

                  <button
                    @click="deleteExam(exam)"
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
                Belum ada data paket ujian yang tercatat di madrasah.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- DETAIL INSPECTOR MODAL -->
    <div v-if="showDetailModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-4xl overflow-hidden border border-slate-100 transform transition-all">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-lg font-black text-slate-800 font-lexend uppercase tracking-wider">{{ selectedExamDetail?.title }}</h2>
              <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-teal-50 text-teal-700 border border-teal-100">
                {{ examTypeLabel(selectedExamDetail?.exam_type) }}
              </span>
            </div>
            <p class="text-xs text-slate-400 font-medium mt-0.5">
              Guru: <strong>{{ selectedExamDetail?.teacher?.name }}</strong> • Mapel: <strong>{{ selectedExamDetail?.subject?.name }}</strong> • Kelas: <strong>{{ selectedExamDetail?.class_room?.name }}</strong>
            </p>
          </div>
          <button @click="showDetailModal = false" class="w-9 h-9 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors border border-slate-100 shadow-sm cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-8 space-y-6 max-h-[75vh] overflow-y-auto">
          <!-- Summary Cards -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4" v-if="inspectAnalysis?.summary">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Nilai</span>
              <div class="text-xl font-black text-teal-700 font-lexend">{{ inspectAnalysis.summary.avg_score }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nilai Max / Min</span>
              <div class="text-xl font-black text-slate-800 font-lexend">{{ inspectAnalysis.summary.max_score }} / {{ inspectAnalysis.summary.min_score }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa Tuntas</span>
              <div class="text-xl font-black text-emerald-600 font-lexend">{{ inspectAnalysis.summary.passed_count }} Siswa</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">% Ketuntasan</span>
              <div class="text-xl font-black text-blue-600 font-lexend">{{ inspectAnalysis.summary.pass_percentage }}%</div>
            </div>
          </div>

          <!-- Question Analysis Breakdown -->
          <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Analisis Butir Soal (Tingkat Kesukaran)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div
                v-for="qa in inspectAnalysis?.questions_analysis"
                :key="qa.question_number"
                class="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/50 space-y-2"
              >
                <div class="flex items-center justify-between">
                  <span class="text-xs font-black text-slate-700 font-lexend">No. {{ qa.question_number }} (Kunci: <strong class="text-teal-700">{{ qa.correct_answer || '-' }}</strong>)</span>
                  <span
                    :class="[
                      qa.difficulty_category === 'Mudah' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                      qa.difficulty_category === 'Sedang' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                      'bg-rose-50 text-rose-700 border-rose-200'
                    ]"
                    class="px-2 py-0.5 rounded-full text-[9px] font-extrabold border"
                  >
                    {{ qa.difficulty_category }} (P: {{ qa.difficulty_index }})
                  </span>
                </div>

                <div class="w-full bg-rose-200 h-2 rounded-full overflow-hidden flex">
                  <div class="bg-emerald-500 h-full" :style="{ width: (qa.difficulty_index * 100) + '%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="px-6 sm:px-8 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-3">
          <div class="flex items-center gap-2 flex-wrap w-full md:w-auto">
            <!-- Tombol 1: Cetak Nilai Asli -->
            <button
              type="button"
              @click="openRecapPrint(selectedExamDetail?.id)"
              class="px-4 py-2 bg-slate-800 hover:bg-slate-900 active:scale-95 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer"
              title="Cetak lembar rekapitulasi nilai koreksi asli & arsip nilai murni"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Nilai Asli</span>
            </button>

            <!-- Tombol 2: Cetak Nilai Jadi (Rapor) -->
            <button
              type="button"
              @click="openAdjustedPrint(selectedExamDetail?.id)"
              class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm shadow-amber-500/20 cursor-pointer"
              title="Cetak lembar nilai jadi standar rapor bebas remedial (100% tuntas)"
            >
              <Award class="w-4 h-4" />
              <span>Cetak Nilai Jadi (Rapor)</span>
            </button>

            <!-- Tombol 3: Cetak Analisis & Grafik -->
            <button
              type="button"
              @click="openAnalysisPrint(selectedExamDetail?.id)"
              class="px-4 py-2 bg-teal-600 hover:bg-teal-700 active:scale-95 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm shadow-teal-600/20 cursor-pointer"
              title="Cetak laporan analisis butir soal lengkap dengan visual grafik daya serap"
            >
              <BarChart2 class="w-4 h-4" />
              <span>Cetak Analisis & Grafik</span>
            </button>

            <!-- Tombol 4: Download Excel -->
            <button
              type="button"
              @click="downloadExcel(selectedExamDetail?.id)"
              class="px-3.5 py-2 bg-emerald-100 hover:bg-emerald-200 active:scale-95 text-emerald-800 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer"
              title="Download rekap nilai kelas format Excel (.xlsx)"
            >
              <Download class="w-4 h-4" />
              <span>Excel</span>
            </button>
          </div>

          <button
            type="button"
            @click="showDetailModal = false"
            class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer w-full md:w-auto"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- SETTINGS MODAL (Super Admin & Kurikulum) -->
    <div v-if="showSettingsModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 transform transition-all">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <div class="flex items-center gap-2">
              <Settings class="w-5 h-5 text-slate-700" />
              <h2 class="text-lg font-black text-slate-800 font-lexend uppercase tracking-wider">Pengaturan Asesmen & Koreksi</h2>
            </div>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Kebijakan standar penilaian madrasah untuk Super Admin & Waka Kurikulum.</p>
          </div>
          <button @click="showSettingsModal = false" class="w-9 h-9 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors border border-slate-100 shadow-sm cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="saveSettings" class="p-8 space-y-5 max-h-[75vh] overflow-y-auto">
          <!-- Default KKM & Option Count -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">KKM/KKTP Standar *</label>
              <input
                v-model.number="settingsForm.default_kkm"
                type="number"
                min="0"
                max="100"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400 text-center"
              />
              <span class="text-[10px] text-slate-400 font-medium">Batas kelulusan default saat guru membuat ujian</span>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Opsi Pilihan Ganda *</label>
              <select
                v-model.number="settingsForm.default_options_count"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400"
              >
                <option :value="4">4 Pilihan (A, B, C, D) - Standar MTs</option>
                <option :value="5">5 Pilihan (A, B, C, D, E)</option>
              </select>
              <span class="text-[10px] text-slate-400 font-medium">Format pilihan pada lembar kisi-kisi</span>
            </div>
          </div>

          <!-- Default Weights -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Standar Bobot PG (%) *</label>
              <input
                v-model.number="settingsForm.default_pg_weight"
                type="number"
                min="0"
                max="100"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400 text-center"
              />
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Standar Bobot Essay (%) *</label>
              <input
                v-model.number="settingsForm.default_essay_weight"
                type="number"
                min="0"
                max="100"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400 text-center"
              />
            </div>
          </div>

          <!-- Lock/Open Correction Toggle -->
          <div class="p-4 rounded-2xl border transition-colors flex items-center justify-between" :class="settingsForm.is_correction_open ? 'bg-emerald-50/60 border-emerald-200' : 'bg-rose-50/60 border-rose-200'">
            <div>
              <span class="text-xs font-black uppercase tracking-wider" :class="settingsForm.is_correction_open ? 'text-emerald-900' : 'text-rose-900'">
                {{ settingsForm.is_correction_open ? '🟢 Akses Koreksi Siswa: DIBUKA' : '🔴 Akses Koreksi Siswa: DIKUNCI' }}
              </span>
              <p class="text-[11px] mt-0.5 font-medium" :class="settingsForm.is_correction_open ? 'text-emerald-700' : 'text-rose-700'">
                {{ settingsForm.is_correction_open ? 'Guru bebas menginput dan mengoreksi jawaban siswa.' : 'Form dikunci sementara. Guru tidak dapat mengubah nilai ujian.' }}
              </p>
            </div>
            <button
              type="button"
              @click="settingsForm.is_correction_open = !settingsForm.is_correction_open"
              :class="settingsForm.is_correction_open ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
              class="px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm cursor-pointer"
            >
              {{ settingsForm.is_correction_open ? 'Kunci Sekarang' : 'Buka Sekarang' }}
            </button>
          </div>

          <!-- Deadline -->
          <div class="space-y-1.5">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Batas Waktu Pengisian Nilai (Deadline)</label>
            <input
              v-model="settingsForm.correction_deadline"
              type="date"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400"
            />
            <span class="text-[10px] text-slate-400 font-medium">Batas tanggal bagi para guru untuk menyelesaikan penginputan nilai ujian</span>
          </div>

          <!-- Allow Direct Sync to Grades -->
          <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-200">
            <div>
              <span class="text-xs font-black text-slate-800 uppercase tracking-wider">Izinkan 1-Klik Kirim ke Buku Nilai</span>
              <p class="text-[11px] text-slate-500 font-medium mt-0.5">Memungkinkan guru langsung menyinkronkan hasil koreksi ke modul Rapor/Grades.</p>
            </div>
            <input
              v-model="settingsForm.allow_direct_sync"
              type="checkbox"
              class="w-5 h-5 rounded-lg text-teal-600 focus:ring-teal-400 cursor-pointer"
            />
          </div>

          <!-- Actions -->
          <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <button
              type="button"
              @click="showSettingsModal = false"
              class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="savingSettings"
              class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-all shadow-md cursor-pointer disabled:opacity-50"
            >
              {{ savingSettings ? 'Menyimpan...' : 'Simpan Pengaturan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- COMPONENT PRINT MODALS UNTUK KURIKULUM / ADMIN -->
    <ExamCorrectionPrintModals
      v-model:show-recap="showRecapPrintModal"
      v-model:show-adjusted="showAdjustedPrintModal"
      v-model:show-analysis="showAnalysisPrintModal"
      :exam="printExamData"
      :questions="printQuestions"
      :students="printStudents"
      :school-profile="printSchoolProfile"
      :analysis-data="printAnalysisData"
      id-prefix="admin"
    />
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import ExamCorrectionPrintModals from '../components/ExamCorrectionPrintModals.vue';
import {
  CheckSquare,
  BookOpen,
  Award,
  CheckCircle2,
  Users,
  Search,
  Download,
  Trash2,
  Eye,
  FileSpreadsheet,
  Settings,
  X,
  Printer,
  BarChart2
} from 'lucide-vue-next';

const toast = useToast();

const loading = ref(false);
const exams = ref([]);
const summary = ref(null);
const classes = ref([]);
const subjects = ref([]);

const filterClass = ref('');
const filterSubject = ref('');
const filterType = ref('');
const searchQuery = ref('');

const showDetailModal = ref(false);
const selectedExamDetail = ref(null);
const inspectAnalysis = ref(null);

// Print Preview States untuk Kurikulum & Admin
const showRecapPrintModal = ref(false);
const showAdjustedPrintModal = ref(false);
const showAnalysisPrintModal = ref(false);
const printExamData = ref(null);
const printQuestions = ref([]);
const printStudents = ref([]);
const printSchoolProfile = ref(null);
const printAnalysisData = ref(null);
const printLoading = ref(false);

const showSettingsModal = ref(false);
const savingSettings = ref(false);
const settingsForm = ref({
  default_kkm: 75,
  default_pg_weight: 70,
  default_essay_weight: 30,
  default_options_count: 4,
  is_correction_open: true,
  correction_deadline: '',
  allow_direct_sync: true
});


onMounted(async () => {
  await Promise.all([fetchExams(), fetchSummary(), fetchMeta()]);
});

async function fetchMeta() {
  try {
    const res = await api.get('/admin/exam-corrections/options');
    const optData = res?.data || res || {};
    classes.value = optData.classes || [];
    subjects.value = optData.subjects || [];
  } catch (err) {
    console.error('Failed to load exam correction options:', err);
    try {
      const [clsRes, sbjRes] = await Promise.all([
        api.get('/admin/classes'),
        api.get('/admin/subjects')
      ]);
      classes.value = clsRes?.data || clsRes || [];
      subjects.value = sbjRes?.data || sbjRes || [];
    } catch (fallbackErr) {
      console.error(fallbackErr);
    }
  }
}

async function fetchExams() {
  loading.value = true;
  try {
    const res = await api.get('/admin/exam-corrections');
    const list = res?.data?.data || res?.data || res || [];
    exams.value = Array.isArray(list) ? list : (list.data || []);
  } catch (err) {
    toast.error('Gagal memuat data monitoring ujian.');
  } finally {
    loading.value = false;
  }
}

async function fetchSummary() {
  try {
    const res = await api.get('/admin/exam-corrections/summary');
    summary.value = res?.data || res || null;
  } catch (err) {
    console.error('Failed to fetch summary:', err);
  }
}

const filteredExams = computed(() => {
  return exams.value.filter(e => {
    if (filterClass.value && e.class_room_id !== filterClass.value) return false;
    if (filterSubject.value && e.subject_id !== filterSubject.value) return false;
    if (filterType.value && e.exam_type !== filterType.value) return false;
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const matchTitle = e.title?.toLowerCase().includes(q);
      const matchSbj = e.subject?.name?.toLowerCase().includes(q);
      const matchTeacher = e.teacher?.name?.toLowerCase().includes(q);
      if (!matchTitle && !matchSbj && !matchTeacher) return false;
    }
    return true;
  });
});

function examTypeLabel(type) {
  const map = {
    uh: 'Penilaian Harian (UH)',
    sts: 'ASTS',
    sas: 'ASAS',
    asts: 'ASTS',
    asas: 'ASAS',
    pat: 'PAT',
    am: 'Asesmen Madrasah',
    quiz: 'Kuis'
  };
  return map[type] || type?.toUpperCase() || '-';
}

function getPassPercent(exam) {
  const total = Number(exam.submissions_count) || 0;
  const passed = Number(exam.passed_count) || 0;
  if (total === 0) return 0;
  return Math.round((passed / total) * 100);
}

async function inspectExam(id) {
  try {
    const [detailRes, analysisRes] = await Promise.all([
      api.get(`/teacher/exam-corrections/${id}`),
      api.get(`/teacher/exam-corrections/${id}/analysis`)
    ]);
    const dExam = detailRes?.data || detailRes || {};
    selectedExamDetail.value = dExam.exam || dExam;
    inspectAnalysis.value = analysisRes?.data || analysisRes;

    // Siapkan data cetak sekaligus
    printExamData.value = dExam.exam || dExam;
    printQuestions.value = dExam.questions || [];
    printStudents.value = dExam.students || [];
    printSchoolProfile.value = dExam.school_profile || null;
    printAnalysisData.value = analysisRes?.data || analysisRes || null;

    showDetailModal.value = true;
  } catch (err) {
    toast.error('Gagal memuat detail analisis ujian.');
  }
}

async function loadExamForPrint(id) {
  if (printExamData.value?.id === id && printStudents.value?.length > 0) {
    return true;
  }
  printLoading.value = true;
  try {
    const [detailRes, analysisRes] = await Promise.all([
      api.get(`/teacher/exam-corrections/${id}`),
      api.get(`/teacher/exam-corrections/${id}/analysis`)
    ]);
    const dExam = detailRes?.data || detailRes || {};
    printExamData.value = dExam.exam || dExam;
    printQuestions.value = dExam.questions || [];
    printStudents.value = dExam.students || [];
    printSchoolProfile.value = dExam.school_profile || null;
    printAnalysisData.value = analysisRes?.data || analysisRes || null;
    return true;
  } catch (err) {
    toast.error('Gagal memuat data dokumen cetak ujian.');
    return false;
  } finally {
    printLoading.value = false;
  }
}

async function openRecapPrint(id) {
  const targetId = id || selectedExamDetail.value?.id;
  if (!targetId) return;
  const ok = await loadExamForPrint(targetId);
  if (ok) showRecapPrintModal.value = true;
}

async function openAdjustedPrint(id) {
  const targetId = id || selectedExamDetail.value?.id;
  if (!targetId) return;
  const ok = await loadExamForPrint(targetId);
  if (ok) showAdjustedPrintModal.value = true;
}

async function openAnalysisPrint(id) {
  const targetId = id || selectedExamDetail.value?.id;
  if (!targetId) return;
  const ok = await loadExamForPrint(targetId);
  if (ok) showAnalysisPrintModal.value = true;
}

function downloadExcel(id) {
  window.open(`/api/teacher/exam-corrections/${id}/export-excel`, '_blank');
}

function exportAllMadrasah() {
  window.open('/api/admin/exam-corrections/export-all', '_blank');
}

async function deleteExam(exam) {
  if (confirm(`Apakah Anda yakin ingin menghapus paket ujian "${exam.title}" buatan guru ${exam.teacher?.name}?`)) {
    try {
      await api.delete(`/admin/exam-corrections/${exam.id}`);
      toast.success('Paket ujian berhasil dihapus oleh Admin.');
      await Promise.all([fetchExams(), fetchSummary()]);
    } catch (err) {
      toast.error('Gagal menghapus ujian.');
    }
  }
}

async function openSettingsModal() {
  try {
    const res = await api.get('/admin/exam-corrections/settings');
    const sData = res?.data || res;
    if (sData) {
      settingsForm.value = { ...settingsForm.value, ...sData };
    }
    showSettingsModal.value = true;
  } catch (err) {
    toast.error('Gagal memuat pengaturan asesmen.');
  }
}

async function saveSettings() {
  savingSettings.value = true;
  try {
    const res = await api.post('/admin/exam-corrections/settings', settingsForm.value);
    toast.success(res?.message || res?.data?.message || 'Pengaturan asesmen berhasil disimpan!');
    showSettingsModal.value = false;
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan pengaturan.');
  } finally {
    savingSettings.value = false;
  }
}

</script>
