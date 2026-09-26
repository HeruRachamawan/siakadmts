<template>
  <div class="space-y-5 font-inter pb-12">
    <!-- Header Card (Deep Madrasah Emerald - Institutional & Dignified) -->
    <div class="relative bg-gradient-to-r from-emerald-950 via-emerald-900 to-teal-950 text-white rounded-lg border border-emerald-800/80 p-5 sm:p-6 shadow-sm overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="absolute right-0 top-0 bottom-0 w-96 bg-radial from-emerald-500/10 to-transparent pointer-events-none"></div>

      <div class="relative z-10 flex items-center gap-3.5">
        <div class="w-12 h-12 bg-white/10 rounded-md border border-white/20 p-2 flex items-center justify-center shadow-inner flex-shrink-0">
          <FileCheck2 class="w-6 h-6 text-emerald-300" />
        </div>
        <div>
          <div class="flex items-center gap-2 mb-0.5">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
              Pusat Kendali Asesmen & Koreksi
            </span>
          </div>
          <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white uppercase font-sans">
            Monitoring Koreksi Ujian & Asesmen Madrasah
          </h1>
          <p class="text-emerald-200/80 text-xs mt-0.5 max-w-2xl font-normal">
            Pantau kepatuhan guru pengampu per mata pelajaran, distribusi nilai kelas, ketuntasan belajar, dan analisis butir soal terpusat.
          </p>
        </div>
      </div>

      <div class="relative z-10 flex items-center gap-2 flex-wrap">
        <button
          @click="openSettingsModal"
          class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-md text-xs font-semibold bg-white/10 text-white border border-white/20 hover:bg-white/20 transition-colors cursor-pointer"
        >
          <Settings class="w-4 h-4 text-emerald-200" />
          <span>Pengaturan Asesmen</span>
        </button>

        <button
          @click="exportAllMadrasah"
          class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md text-xs font-semibold bg-emerald-600 text-white border border-emerald-500 hover:bg-emerald-500 transition-colors shadow-2xs cursor-pointer"
        >
          <FileSpreadsheet class="w-4 h-4" />
          <span>Export Rekap 1 Madrasah (Excel)</span>
        </button>
      </div>
    </div>

    <!-- Overview Stats Bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
      <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-2xs flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center flex-shrink-0">
          <BookOpen class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Paket Ujian</span>
          <div class="text-lg font-bold text-slate-900 font-mono tabular-nums">{{ summary?.total_exams || 0 }} Paket</div>
        </div>
      </div>

      <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-2xs flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-md bg-blue-50 border border-blue-200 text-blue-700 flex items-center justify-center flex-shrink-0">
          <Award class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Madrasah</span>
          <div class="text-lg font-bold text-blue-700 font-mono tabular-nums">{{ summary?.avg_score || '0.00' }}</div>
        </div>
      </div>

      <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-2xs flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-md bg-teal-50 border border-teal-200 text-teal-700 flex items-center justify-center flex-shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tingkat Ketuntasan</span>
          <div class="text-lg font-bold text-teal-700 font-mono tabular-nums">{{ summary?.pass_rate || 0 }}%</div>
        </div>
      </div>

      <div class="bg-white rounded-lg p-4 border border-slate-200 shadow-2xs flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-md bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center flex-shrink-0">
          <Users class="w-5 h-5" />
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Kepatuhan Guru</span>
          <div class="text-lg font-bold text-purple-700 font-mono tabular-nums">{{ summary?.teachers_stats?.compliance_percentage || 0 }}%</div>
        </div>
      </div>
    </div>

    <!-- Filter & Action Controls -->
    <div class="bg-white rounded-lg p-4 shadow-2xs border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
      <div class="flex items-center gap-2 flex-wrap">
        <!-- Filter Kelas -->
        <select v-model="filterClass" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-1.5 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <option value="">Semua Kelas</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">
            Kelas {{ c.name }} ({{ c.students_count || 0 }} Siswa)
          </option>
        </select>

        <!-- Filter Mapel Spesifik -->
        <select v-model="filterSubject" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-1.5 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <option value="">Semua Mata Pelajaran ({{ subjects.length }})</option>
          <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>

        <!-- Filter Jenis Ujian -->
        <select v-model="filterType" class="bg-slate-50 border border-slate-200 rounded-md px-3 py-1.5 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
          <option value="">Semua Jenis Ujian</option>
          <option value="uh">Penilaian Harian (UH)</option>
          <option value="sts">Asesmen Sumatif Tengah Semester (ASTS)</option>
          <option value="sas">Asesmen Sumatif Akhir Semester (ASAS)</option>
          <option value="pat">Penilaian Akhir Tahun (PAT)</option>
          <option value="am">Asesmen Madrasah (AM)</option>
          <option value="quiz">Kuis / Latihan</option>
        </select>
      </div>

      <div class="flex items-center gap-2 w-full md:w-auto flex-wrap">
        <div class="relative flex-1 md:w-56">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari ujian, mapel, guru..."
            class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-8 pr-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"
          />
          <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" />
        </div>

        <!-- View Mode Switcher: Grid vs Table -->
        <div class="flex items-center bg-slate-100 p-0.5 rounded-lg border border-slate-200 flex-shrink-0">
          <button
            type="button"
            @click="viewMode = 'grid'"
            :class="viewMode === 'grid' ? 'bg-white text-emerald-800 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="px-2.5 py-1.5 rounded-md text-xs flex items-center gap-1.5 transition-all cursor-pointer"
            title="Tampilan Grid Kartu Asesmen (Modern & Rapi)"
          >
            <LayoutGrid class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Grid Kartu</span>
          </button>
          <button
            type="button"
            @click="viewMode = 'table'"
            :class="viewMode === 'table' ? 'bg-white text-emerald-800 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="px-2.5 py-1.5 rounded-md text-xs flex items-center gap-1.5 transition-all cursor-pointer"
            title="Tampilan Tabel Master Tunggal Bersih"
          >
            <List class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Tabel Master</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Data Counter & Information Bar -->
    <div v-if="!loading && filteredExams.length > 0" class="flex items-center justify-between text-xs text-slate-500 font-medium px-1">
      <div class="flex items-center gap-2">
        <span class="font-bold text-slate-800">{{ filteredExams.length }}</span>
        <span>Paket Ujian Ditemukan</span>
        <span v-if="filteredExams.length !== exams.length" class="text-slate-400">
          (Difilter dari total {{ exams.length }} paket)
        </span>
      </div>
      <div class="text-[11px] text-slate-400 font-mono hidden sm:block">
        Mode Aktif: <strong class="text-emerald-700 capitalize">{{ viewMode === 'grid' ? 'Grid Kartu Asesmen' : 'Tabel Master Tunggal' }}</strong>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="py-16 text-center bg-white rounded-2xl border border-slate-200 p-8 space-y-3">
      <div class="w-8 h-8 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      <p class="text-xs text-slate-500 font-medium">Memuat dan mengkalkulasi data asesmen madrasah...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredExams.length === 0" class="py-16 text-center bg-white rounded-2xl border border-slate-200 p-8 space-y-2">
      <FolderOpen class="w-10 h-10 text-slate-300 mx-auto" />
      <h3 class="text-sm font-bold text-slate-700">Tidak Ada Paket Ujian</h3>
      <p class="text-xs text-slate-400">Tidak ditemukan data ujian yang cocok dengan kriteria filter atau kata kunci pencarian.</p>
    </div>

    <!-- ==================== VIEW MODE 1: GRID KARTU (MODERN & ANTI PUSING - DEFAULT) ==================== -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div
        v-for="exam in filteredExams"
        :key="'grid-exam-' + exam.id"
        class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs hover:shadow-md hover:border-emerald-500/50 transition-all duration-200 flex flex-col justify-between overflow-hidden group"
      >
        <!-- Top Colored Accent Bar based on Exam Type -->
        <div
          class="h-1.5 w-full"
          :class="getExamTypeBarClass(exam.exam_type)"
        ></div>

        <div class="p-5 flex-1 flex flex-col justify-between">
          <!-- Card Header: Badges & Tags -->
          <div>
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border" :class="getExamTypeBadgeClass(exam.exam_type)">
                  {{ examTypeLabel(exam.exam_type) }}
                </span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 font-mono">
                  Kelas {{ exam.class_room?.name || '-' }}
                </span>
              </div>
              <div class="flex items-center gap-1 text-[10px] font-mono font-semibold text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-150 flex-shrink-0">
                <span>KKM {{ exam.kkm }}</span>
                <span>&bull;</span>
                <span>{{ exam.total_questions }} Soal</span>
              </div>
            </div>

            <!-- Subject & Title -->
            <div class="flex items-start gap-3 mt-3.5">
              <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-black text-sm uppercase flex-shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                {{ exam.subject?.name?.charAt(0) || 'U' }}
              </div>
              <div class="min-w-0 flex-1">
                <span class="text-[10.5px] font-extrabold uppercase tracking-wider text-emerald-700 block truncate">
                  {{ exam.subject?.name || 'Mata Pelajaran' }}
                </span>
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-800 transition-colors line-clamp-2 leading-snug mt-0.5">
                  {{ exam.title }}
                </h3>
              </div>
            </div>

            <!-- Guru Pengampu -->
            <div class="mt-3.5 py-2 px-3 bg-slate-50/80 rounded-xl border border-slate-100 flex items-center justify-between text-xs">
              <div class="flex items-center gap-2 min-w-0">
                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                  {{ exam.teacher?.name?.charAt(0) || 'G' }}
                </div>
                <div class="min-w-0">
                  <span class="text-[9px] text-slate-400 block leading-none font-medium">Guru Pengampu</span>
                  <span class="font-semibold text-slate-800 truncate block text-xs mt-0.5">{{ exam.teacher?.name || 'Belum Ditentukan' }}</span>
                </div>
              </div>
              <span v-if="exam.teacher?.nip" class="text-[9px] font-mono text-slate-400 flex-shrink-0">NIP: {{ exam.teacher.nip }}</span>
            </div>
          </div>

          <!-- Mini Performance Metrics (3 Columns) -->
          <div class="mt-4 pt-3.5 border-t border-slate-100">
            <div class="grid grid-cols-3 gap-2 text-center">
              <div class="bg-slate-50/80 rounded-xl p-2 border border-slate-100">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Siswa Ikut</span>
                <span class="text-xs font-black text-slate-800 font-mono tabular-nums mt-0.5 block">
                  {{ exam.submissions_count || 0 }} Siswa
                </span>
              </div>
              <div class="bg-slate-50/80 rounded-xl p-2 border border-slate-100">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Rata-rata</span>
                <span class="text-xs font-black font-mono tabular-nums mt-0.5 block" :class="Number(exam.avg_score) >= Number(exam.kkm) ? 'text-emerald-700' : 'text-amber-700'">
                  {{ exam.avg_score || '0.00' }}
                </span>
              </div>
              <div class="bg-slate-50/80 rounded-xl p-2 border border-slate-100">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Ketuntasan</span>
                <span class="text-xs font-black font-mono tabular-nums mt-0.5 block" :class="getPassPercent(exam) >= 75 ? 'text-emerald-700' : 'text-amber-700'">
                  {{ getPassPercent(exam) }}%
                </span>
              </div>
            </div>

            <!-- Progress Bar Ketuntasan -->
            <div class="mt-2.5">
              <div class="flex items-center justify-between text-[9px] font-medium text-slate-400 mb-1">
                <span class="text-emerald-700 font-bold font-mono">{{ exam.passed_count || 0 }} Tuntas</span>
                <span class="text-rose-600 font-bold font-mono">{{ exam.remedial_count || 0 }} Remedial</span>
              </div>
              <div class="w-full bg-rose-100 h-1.5 rounded-full overflow-hidden flex">
                <div class="bg-emerald-600 h-full rounded-full transition-all duration-300" :style="{ width: getPassPercent(exam) + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Footer Actions -->
        <div class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-2">
          <button
            @click="inspectExam(exam.id)"
            class="flex-1 py-2 px-3 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <Eye class="w-3.5 h-3.5" />
            <span>Detail Koreksi</span>
          </button>

          <button
            @click="openRecapPrint(exam.id)"
            title="Cetak Cepat Rekap Nilai Ujian"
            class="p-2 bg-white hover:bg-slate-100 text-slate-700 rounded-xl text-xs transition-colors flex items-center justify-center cursor-pointer border border-slate-200 shadow-2xs"
          >
            <Printer class="w-3.5 h-3.5" />
          </button>

          <button
            @click="downloadExcel(exam.id)"
            title="Export Excel Rekap Kelas"
            class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl text-xs transition-colors flex items-center justify-center cursor-pointer border border-emerald-200 shadow-2xs"
          >
            <Download class="w-3.5 h-3.5" />
          </button>

          <button
            @click="deleteExam(exam)"
            title="Hapus Paket Ujian"
            class="p-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs transition-colors flex items-center justify-center cursor-pointer border border-rose-200 shadow-2xs"
          >
            <Trash2 class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== VIEW MODE 2: TABEL MASTER TUNGGAL (BERSIH & FLAT) ==================== -->
    <div v-else-if="viewMode === 'table'" class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
            <tr>
              <th class="px-4 py-3.5 text-center w-12">No</th>
              <th class="px-4 py-3.5 min-w-[240px]">Judul Paket Ujian</th>
              <th class="px-4 py-3.5">Mata Pelajaran</th>
              <th class="px-4 py-3.5 text-center">Kelas</th>
              <th class="px-4 py-3.5">Guru Pengampu</th>
              <th class="px-4 py-3.5 text-center">Jml Siswa</th>
              <th class="px-4 py-3.5 text-center">Rata-rata</th>
              <th class="px-4 py-3.5 text-center min-w-[140px]">Ketuntasan</th>
              <th class="px-4 py-3.5 text-right min-w-[160px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(exam, idx) in filteredExams" :key="'table-exam-' + exam.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-4 py-3.5 text-center font-mono text-slate-400 font-bold">
                {{ idx + 1 }}
              </td>
              <td class="px-4 py-3.5">
                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ exam.title }}</div>
                <div class="flex items-center gap-1.5 mt-1">
                  <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border" :class="getExamTypeBadgeClass(exam.exam_type)">
                    {{ examTypeLabel(exam.exam_type) }}
                  </span>
                  <span class="text-[10px] text-slate-400 font-mono">KKM {{ exam.kkm }} &bull; {{ exam.total_questions }} Soal</span>
                </div>
              </td>
              <td class="px-4 py-3.5 font-bold text-emerald-800">
                {{ exam.subject?.name || '-' }}
              </td>
              <td class="px-4 py-3.5 text-center">
                <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-800 rounded-md text-xs font-semibold border border-slate-200/80 font-mono">
                  Kelas {{ exam.class_room?.name || '-' }}
                </span>
              </td>
              <td class="px-4 py-3.5">
                <div class="font-semibold text-slate-800">{{ exam.teacher?.name || '-' }}</div>
                <div v-if="exam.teacher?.nip" class="text-[10px] text-slate-400 font-mono">NIP: {{ exam.teacher?.nip }}</div>
              </td>
              <td class="px-4 py-3.5 text-center font-bold text-slate-700 font-mono tabular-nums">
                {{ exam.submissions_count || 0 }} Siswa
              </td>
              <td class="px-4 py-3.5 text-center">
                <span class="text-xs sm:text-sm font-bold font-mono tabular-nums" :class="Number(exam.avg_score) >= Number(exam.kkm) ? 'text-emerald-700' : 'text-amber-700'">
                  {{ exam.avg_score || '0.00' }}
                </span>
              </td>
              <td class="px-4 py-3.5 text-center">
                <div class="space-y-1">
                  <div class="text-[10px] font-semibold font-mono tabular-nums flex items-center justify-center gap-1">
                    <span class="text-emerald-700">{{ exam.passed_count || 0 }} Tuntas</span>
                    <span>/</span>
                    <span class="text-rose-700">{{ exam.remedial_count || 0 }} Rem</span>
                    <span class="text-slate-400">({{ getPassPercent(exam) }}%)</span>
                  </div>
                  <div class="w-24 mx-auto bg-rose-200 h-1.5 rounded-full overflow-hidden flex">
                    <div class="bg-emerald-600 h-full rounded-full" :style="{ width: getPassPercent(exam) + '%' }"></div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3.5 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    @click="inspectExam(exam.id)"
                    title="Lihat Detail & Opsi Cetak Lengkap"
                    class="btn btn-outline text-xs py-1 px-2.5"
                  >
                    <Eye class="w-3.5 h-3.5" />
                    <span>Detail</span>
                  </button>
                  <button
                    @click="openRecapPrint(exam.id)"
                    title="Cetak Cepat Rekap Nilai Ujian"
                    class="btn btn-outline text-xs p-1.5"
                  >
                    <Printer class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="downloadExcel(exam.id)"
                    title="Export Excel Rekap Kelas"
                    class="btn btn-outline text-xs p-1.5 text-emerald-800"
                  >
                    <Download class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="deleteExam(exam)"
                    title="Hapus Paket Ujian"
                    class="btn btn-danger text-xs p-1.5"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
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
          <!-- KKM / KKTP Standar Berdasarkan Tingkat (7, 8, 9) -->
          <div class="space-y-2.5 p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div>
                <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">Standar KKM / KKTP Per Tingkatan *</label>
                <p class="text-[11px] text-slate-500 font-medium">Batas kelulusan otomatis saat guru membuat paket ujian kelas 7, 8, atau 9.</p>
              </div>
              <router-link
                to="/admin/kurikulum/asts-monitoring"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/60 text-[11px] font-bold transition-colors self-start sm:self-auto"
                title="Buka Matriks Lengkap KKTP Per Mapel"
              >
                <span>Matriks Per Mapel</span>
                <ChevronRight class="w-3 h-3" />
              </router-link>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-1">
              <!-- Tingkat 7 -->
              <div class="bg-white p-3 rounded-xl border border-emerald-200 shadow-2xs space-y-1.5 focus-within:ring-2 focus-within:ring-emerald-400">
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-black text-emerald-700 uppercase tracking-wider">Tingkat VII</span>
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <input
                  v-model.number="settingsForm.default_kkm_7"
                  type="number"
                  min="0"
                  max="100"
                  required
                  class="w-full bg-emerald-50/40 border border-emerald-100 rounded-lg px-2 py-1.5 text-center text-sm font-black text-emerald-950 focus:outline-hidden"
                  placeholder="75"
                />
                <span class="block text-[9px] text-slate-400 text-center font-medium">Kelas 7</span>
              </div>

              <!-- Tingkat 8 -->
              <div class="bg-white p-3 rounded-xl border border-blue-200 shadow-2xs space-y-1.5 focus-within:ring-2 focus-within:ring-blue-400">
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-black text-blue-700 uppercase tracking-wider">Tingkat VIII</span>
                  <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <input
                  v-model.number="settingsForm.default_kkm_8"
                  type="number"
                  min="0"
                  max="100"
                  required
                  class="w-full bg-blue-50/40 border border-blue-100 rounded-lg px-2 py-1.5 text-center text-sm font-black text-blue-950 focus:outline-hidden"
                  placeholder="75"
                />
                <span class="block text-[9px] text-slate-400 text-center font-medium">Kelas 8</span>
              </div>

              <!-- Tingkat 9 -->
              <div class="bg-white p-3 rounded-xl border border-purple-200 shadow-2xs space-y-1.5 focus-within:ring-2 focus-within:ring-purple-400">
                <div class="flex items-center justify-between">
                  <span class="text-[10px] font-black text-purple-700 uppercase tracking-wider">Tingkat IX</span>
                  <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                </div>
                <input
                  v-model.number="settingsForm.default_kkm_9"
                  type="number"
                  min="0"
                  max="100"
                  required
                  class="w-full bg-purple-50/40 border border-purple-100 rounded-lg px-2 py-1.5 text-center text-sm font-black text-purple-950 focus:outline-hidden"
                  placeholder="75"
                />
                <span class="block text-[9px] text-slate-400 text-center font-medium">Kelas 9</span>
              </div>
            </div>
          </div>

          <!-- Opsi Pilihan Ganda -->
          <div class="space-y-1.5">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Opsi Pilihan Ganda *</label>
            <select
              v-model.number="settingsForm.default_options_count"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-teal-400"
            >
              <option :value="4">4 Pilihan (A, B, C, D) - Standar MTs</option>
              <option :value="5">5 Pilihan (A, B, C, D, E)</option>
            </select>
            <span class="text-[10px] text-slate-400 font-medium">Format pilihan pada lembar kisi-kisi saat guru membuat soal</span>
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
  FileCheck2,
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
  BarChart2,
  ChevronDown,
  ChevronUp,
  ChevronRight,
  FolderOpen,
  Layers,
  LayoutGrid,
  List
} from 'lucide-vue-next';

const toast = useToast();

const loading = ref(false);
const exams = ref([]);
const summary = ref(null);
const classes = ref([]);
const subjects = ref([]);

const viewMode = ref('grid'); // 'grid' (Kartu Modern) | 'table' (Tabel Master Tunggal)

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
  default_kkm_7: 75,
  default_kkm_8: 75,
  default_kkm_9: 75,
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
        api.get('/admin/classes', { all: true, per_page: 500 }),
        api.get('/admin/subjects', { all: true, per_page: 500 })
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

// State & helpers untuk Accordion Mata Pelajaran (Opsi A)
const expandedSubjects = ref({});

function isExpanded(id) {
  return expandedSubjects.value[id] !== false; // Default terbuka agar langsung terlihat
}

function toggleSubject(id) {
  expandedSubjects.value[id] = !isExpanded(id);
}

function expandAll() {
  groupedBySubject.value.forEach(g => {
    expandedSubjects.value[g.id] = true;
  });
}

function collapseAll() {
  groupedBySubject.value.forEach(g => {
    expandedSubjects.value[g.id] = false;
  });
}

// Mengelompokkan seluruh paket ujian yang difilter ke dalam rumpun Mata Pelajaran
const groupedBySubject = computed(() => {
  const groups = {};

  filteredExams.value.forEach(exam => {
    const subjectId = exam.subject_id || (exam.subject ? exam.subject.id : 'lainnya');
    const subjectName = exam.subject?.name || 'Mata Pelajaran Lainnya';

    if (!groups[subjectId]) {
      groups[subjectId] = {
        id: subjectId,
        name: subjectName,
        exams: [],
        teachers: new Set(),
        totalSubmissions: 0,
        totalPassed: 0,
        totalRemedial: 0,
        scoreSum: 0,
        scoredExamsCount: 0
      };
    }

    groups[subjectId].exams.push(exam);
    if (exam.teacher?.name) {
      groups[subjectId].teachers.add(exam.teacher.name);
    }
    const subs = Number(exam.submissions_count) || 0;
    const passed = Number(exam.passed_count) || 0;
    const remedial = Number(exam.remedial_count) || 0;
    const avg = parseFloat(exam.avg_score) || 0;

    groups[subjectId].totalSubmissions += subs;
    groups[subjectId].totalPassed += passed;
    groups[subjectId].totalRemedial += remedial;
    if (subs > 0 && avg > 0) {
      groups[subjectId].scoreSum += avg;
      groups[subjectId].scoredExamsCount += 1;
    }
  });

  return Object.values(groups).map(g => {
    const avgScore = g.scoredExamsCount > 0 ? (g.scoreSum / g.scoredExamsCount).toFixed(2) : '0.00';
    const totalSubs = g.totalSubmissions;
    const passRate = totalSubs > 0 ? Math.round((g.totalPassed / totalSubs) * 100) : 0;
    return {
      id: g.id,
      name: g.name,
      exams: g.exams,
      teachersList: Array.from(g.teachers),
      totalExams: g.exams.length,
      totalSubmissions: totalSubs,
      totalPassed: g.totalPassed,
      totalRemedial: g.totalRemedial,
      avgScore,
      passRate
    };
  }).sort((a, b) => a.name.localeCompare(b.name));
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

function getExamTypeBadgeClass(type) {
  const t = (type || '').toLowerCase();
  if (t === 'sts' || t === 'asts') return 'bg-emerald-50 text-emerald-800 border-emerald-200';
  if (t === 'sas' || t === 'asas') return 'bg-blue-50 text-blue-800 border-blue-200';
  if (t === 'am') return 'bg-purple-50 text-purple-800 border-purple-200';
  if (t === 'pat') return 'bg-indigo-50 text-indigo-800 border-indigo-200';
  if (t === 'uh') return 'bg-amber-50 text-amber-800 border-amber-200';
  return 'bg-slate-100 text-slate-700 border-slate-200';
}

function getExamTypeBarClass(type) {
  const t = (type || '').toLowerCase();
  if (t === 'sts' || t === 'asts') return 'bg-emerald-500';
  if (t === 'sas' || t === 'asas') return 'bg-blue-500';
  if (t === 'am') return 'bg-purple-500';
  if (t === 'pat') return 'bg-indigo-500';
  if (t === 'uh') return 'bg-amber-500';
  return 'bg-slate-400';
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
    const sData = res?.data?.data || res?.data || res;
    if (sData) {
      settingsForm.value = {
        ...settingsForm.value,
        ...sData,
        default_kkm_7: sData.default_kkm_7 ?? sData.default_kkm ?? 75,
        default_kkm_8: sData.default_kkm_8 ?? sData.default_kkm ?? 75,
        default_kkm_9: sData.default_kkm_9 ?? sData.default_kkm ?? 75,
      };
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
