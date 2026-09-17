<template>
  <div class="min-h-screen bg-slate-50/50 pb-20 space-y-6 sm:space-y-8 font-sans">
    <!-- 1. TOP HEADER & HERO -->
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-800 via-teal-800 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-emerald-950/10">
      <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 text-[11px] font-bold tracking-wide uppercase">
            <Sliders class="w-3.5 h-3.5" />
            <span>Manajemen Standar Akademik & Rapor ASTS</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black font-lexend tracking-tight text-white">
            Monitoring Rapor ASTS & Standar KKTP
          </h1>
          <p class="text-xs sm:text-sm text-emerald-100/80 leading-relaxed font-medium">
            Supervisi pengumpulan nilai asesmen tengah semester dari seluruh guru pengampu dan konfigurasi standar KKM/KKTP mandiri per tingkat kelas (7, 8, dan 9).
          </p>
        </div>

        <!-- Action Quick Links -->
        <div class="flex flex-wrap items-center gap-3">
          <RouterLink
            to="/admin/asts-reports"
            class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs transition-all flex items-center gap-2 backdrop-blur-xs cursor-pointer"
          >
            <BookOpenCheck class="w-4 h-4 text-emerald-300" />
            <span>Buku Rapor ASTS</span>
          </RouterLink>

          <button
            @click="refreshCurrentTab"
            :disabled="loading"
            class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-slate-950 font-bold text-xs transition-all flex items-center gap-2 shadow-lg shadow-emerald-900/30 cursor-pointer disabled:opacity-50"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
            <span>Segarkan Data</span>
          </button>
        </div>
      </div>

      <!-- Decorative Background Glow -->
      <div class="absolute -right-16 -top-16 w-80 h-80 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-16 -bottom-16 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- 2. TAB NAVIGATION & GLOBAL FILTERS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-4">
      <!-- Tabs -->
      <div class="inline-flex p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/60 shadow-2xs self-start sm:self-auto">
        <button
          @click="activeTab = 'matrix'"
          class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
          :class="activeTab === 'matrix' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
        >
          <Grid class="w-4 h-4" :class="activeTab === 'matrix' ? 'text-emerald-600' : 'text-slate-400'" />
          <span>Matriks KKTP Per Tingkat</span>
          <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black bg-emerald-100 text-emerald-700">7, 8, 9</span>
        </button>

        <button
          @click="activeTab = 'monitoring'"
          class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer"
          :class="activeTab === 'monitoring' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
        >
          <Activity class="w-4 h-4" :class="activeTab === 'monitoring' ? 'text-emerald-600' : 'text-slate-400'" />
          <span>Live Monitoring Setoran</span>
          <span v-if="monitoringSummary.total_classes" class="px-1.5 py-0.5 rounded-md text-[10px] font-black bg-teal-100 text-teal-700">
            {{ monitoringSummary.complete_classes }}/{{ monitoringSummary.total_classes }} Selesai
          </span>
        </button>
      </div>

      <!-- Academic Year Filter -->
      <div class="flex items-center gap-3 self-end sm:self-auto">
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
          <Calendar class="w-3.5 h-3.5 text-slate-400" />
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tahun Ajaran:</span>
          <select
            v-model="selectedYearId"
            @change="handleFilterChange"
            class="bg-transparent text-xs font-bold text-slate-800 focus:outline-hidden cursor-pointer"
          >
            <option v-for="y in academicYears" :key="y.id" :value="y.id">
              {{ y.name }} {{ y.is_active ? '(Aktif)' : '' }}
            </option>
          </select>
        </div>

        <div v-if="activeTab === 'monitoring'" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
          <Layers class="w-3.5 h-3.5 text-slate-400" />
          <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Semester:</span>
          <select
            v-model="selectedSemester"
            @change="handleFilterChange"
            class="bg-transparent text-xs font-bold text-slate-800 focus:outline-hidden cursor-pointer"
          >
            <option value="ganjil">Ganjil (STS 1)</option>
            <option value="genap">Genap (STS 2)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: MATRIKS STANDAR KKTP / KKM PER TINGKAT                    -->
    <!-- ================================================================= -->
    <div v-if="activeTab === 'matrix'" class="space-y-6">
      <!-- Matrix Info & Quick Actions Banner -->
      <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-blue-50 border border-emerald-200/60 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-start gap-3.5">
          <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm mt-0.5">
            <Sliders class="w-4 h-4" />
          </div>
          <div class="space-y-1">
            <h3 class="text-xs sm:text-sm font-black text-slate-800">
              Konfigurasi Fleksibel KKTP / KKM Berdasarkan Tingkat
            </h3>
            <p class="text-[11px] sm:text-xs text-slate-600 font-medium leading-relaxed max-w-2xl">
              Nilai yang Anda atur di sini akan menjadi standar ketuntasan otomatis saat guru membuat paket koreksi ujian STS, serta penentu status tuntas (Tercapai / Belum Tercapai) pada cetak Rapor ASTS kelas 7, 8, dan 9.
            </p>
          </div>
        </div>

        <!-- Fast Presets & Save Button -->
        <div class="flex flex-wrap items-center gap-2 self-start md:self-center">
          <button
            type="button"
            @click="applyPreset(75)"
            class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-[11px] font-bold shadow-2xs transition-all cursor-pointer"
            title="Set semua mapel ke KKM 75"
          >
            Reset Semua = 75
          </button>
          <button
            type="button"
            @click="applyArabicPreset"
            class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-emerald-300 text-emerald-700 text-[11px] font-bold shadow-2xs transition-all cursor-pointer"
            title="Set Bahasa Arab Kelas 7 = 70, Kelas 8 & 9 = 75"
          >
            ⭐ Atur B. Arab Tkt 7 = 70
          </button>
          <button
            type="button"
            @click="saveMatrix"
            :disabled="savingMatrix"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-black shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <CheckCircle2 class="w-4 h-4" />
            <span>{{ savingMatrix ? 'Menyimpan...' : 'Simpan Perubahan Matriks' }}</span>
          </button>
        </div>
      </div>

      <!-- Matrix Table Card -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                <th class="py-3.5 px-4 w-12 text-center">No</th>
                <th class="py-3.5 px-4 w-28">Kode Mapel</th>
                <th class="py-3.5 px-4 min-w-[220px]">Mata Pelajaran & Kelompok</th>
                <th class="py-3.5 px-4 text-center w-36 bg-emerald-50/50 text-emerald-800 border-x border-emerald-100">
                  <div class="flex items-center justify-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Tingkat VII (7)</span>
                  </div>
                </th>
                <th class="py-3.5 px-4 text-center w-36 bg-blue-50/50 text-blue-800 border-r border-blue-100">
                  <div class="flex items-center justify-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>Tingkat VIII (8)</span>
                  </div>
                </th>
                <th class="py-3.5 px-4 text-center w-36 bg-purple-50/50 text-purple-800 border-r border-purple-100">
                  <div class="flex items-center justify-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    <span>Tingkat IX (9)</span>
                  </div>
                </th>
                <th class="py-3.5 px-4 text-center min-w-[160px]">Status & Catatan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
              <tr
                v-for="(row, idx) in kktpMatrix"
                :key="row.id"
                class="hover:bg-slate-50/60 transition-colors"
                :class="{ 'bg-amber-50/20': isSpecialSubject(row) }"
              >
                <td class="py-3 px-4 text-center text-slate-400 font-bold font-mono">
                  {{ idx + 1 }}
                </td>

                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono font-bold text-[11px] border border-slate-200">
                    {{ row.code || '-' }}
                  </span>
                </td>

                <td class="py-3 px-4">
                  <div class="flex flex-col">
                    <span class="font-bold text-slate-800 text-xs sm:text-[13px]">{{ row.name }}</span>
                    <span class="text-[10px] text-slate-400 font-semibold tracking-wide">
                      {{ row.group }}
                    </span>
                  </div>
                </td>

                <!-- KKTP Tingkat 7 -->
                <td class="py-2.5 px-3 text-center bg-emerald-50/20 border-x border-emerald-100/60">
                  <div class="inline-flex items-center justify-center">
                    <input
                      v-model.number="row.kkm_7"
                      type="number"
                      min="0"
                      max="100"
                      step="1"
                      class="w-20 text-center font-black text-xs sm:text-sm font-mono py-1.5 px-2 rounded-lg border focus:ring-2 focus:outline-hidden transition-all"
                      :class="row.kkm_7 < 75 ? 'border-amber-300 bg-amber-50 text-amber-900 focus:ring-amber-400 font-bold' : 'border-emerald-300 bg-white text-emerald-900 focus:ring-emerald-400'"
                    />
                  </div>
                </td>

                <!-- KKTP Tingkat 8 -->
                <td class="py-2.5 px-3 text-center bg-blue-50/20 border-r border-blue-100/60">
                  <div class="inline-flex items-center justify-center">
                    <input
                      v-model.number="row.kkm_8"
                      type="number"
                      min="0"
                      max="100"
                      step="1"
                      class="w-20 text-center font-black text-xs sm:text-sm font-mono py-1.5 px-2 rounded-lg border border-blue-300 bg-white text-blue-900 focus:ring-2 focus:ring-blue-400 focus:outline-hidden transition-all"
                    />
                  </div>
                </td>

                <!-- KKTP Tingkat 9 -->
                <td class="py-2.5 px-3 text-center bg-purple-50/20 border-r border-purple-100/60">
                  <div class="inline-flex items-center justify-center">
                    <input
                      v-model.number="row.kkm_9"
                      type="number"
                      min="0"
                      max="100"
                      step="1"
                      class="w-20 text-center font-black text-xs sm:text-sm font-mono py-1.5 px-2 rounded-lg border border-purple-300 bg-white text-purple-900 focus:ring-2 focus:ring-purple-400 focus:outline-hidden transition-all"
                    />
                  </div>
                </td>

                <!-- Keterangan -->
                <td class="py-3 px-4 text-center">
                  <span
                    v-if="row.kkm_7 !== row.kkm_8 || row.kkm_8 !== row.kkm_9"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200"
                  >
                    <Sparkles class="w-3 h-3 text-amber-600" />
                    <span>Diferensiasi Tingkat</span>
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"
                  >
                    <span>Seragam ({{ row.kkm_7 }})</span>
                  </span>
                </td>
              </tr>

              <tr v-if="kktpMatrix.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400">
                  <div class="flex flex-col items-center justify-center gap-2">
                    <Inbox class="w-8 h-8 text-slate-300" />
                    <p class="font-bold text-xs">Belum ada data mata pelajaran yang dimuat.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Card Footer -->
        <div class="p-4 bg-slate-50/70 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-[11px] text-slate-500 font-medium">
          <div class="flex items-center gap-2">
            <Info class="w-4 h-4 text-emerald-600 flex-shrink-0" />
            <span>Perubahan nilai akan langsung diaplikasikan ke live perhitungan Rapor ASTS dan Paket Koreksi Soal.</span>
          </div>
          <button
            type="button"
            @click="saveMatrix"
            :disabled="savingMatrix"
            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all cursor-pointer disabled:opacity-50 self-end sm:self-auto"
          >
            {{ savingMatrix ? 'Menyimpan...' : 'Simpan Pengaturan KKTP' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: LIVE MONITORING & SETORAN RAPOR ASTS                       -->
    <!-- ================================================================= -->
    <div v-if="activeTab === 'monitoring'" class="space-y-6">
      <!-- 4 Metric KPI Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Metric 1: Total Kelas -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
          <div class="space-y-1">
            <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Rombel</span>
            <p class="text-xl sm:text-2xl font-black text-slate-800 font-lexend">{{ monitoringSummary.total_classes || 0 }} Kelas</p>
            <p class="text-[10px] text-slate-500 font-medium">{{ monitoringSummary.total_students || 0 }} Peserta Didik</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
            <Building2 class="w-5 h-5" />
          </div>
        </div>

        <!-- Metric 2: Kelas Tuntas (100%) -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-emerald-200/80 shadow-2xs flex items-center justify-between bg-gradient-to-br from-white to-emerald-50/30">
          <div class="space-y-1">
            <span class="text-[10px] sm:text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Setoran Lengkap</span>
            <p class="text-xl sm:text-2xl font-black text-emerald-700 font-lexend">{{ monitoringSummary.complete_classes || 0 }} Kelas</p>
            <p class="text-[10px] text-emerald-600 font-medium">Siap Cetak Rapor</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
            <CheckCircle2 class="w-5 h-5" />
          </div>
        </div>

        <!-- Metric 3: Dalam Proses -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-amber-200/80 shadow-2xs flex items-center justify-between bg-gradient-to-br from-white to-amber-50/30">
          <div class="space-y-1">
            <span class="text-[10px] sm:text-[11px] font-bold text-amber-600 uppercase tracking-wider">Proses Setor</span>
            <p class="text-xl sm:text-2xl font-black text-amber-700 font-lexend">{{ monitoringSummary.partial_classes || 0 }} Kelas</p>
            <p class="text-[10px] text-amber-600 font-medium">{{ monitoringSummary.empty_classes || 0 }} Kelas belum ada nilai</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
            <Clock class="w-5 h-5" />
          </div>
        </div>

        <!-- Metric 4: Rata-rata Madrasah -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-teal-200/80 shadow-2xs flex items-center justify-between bg-gradient-to-br from-white to-teal-50/30">
          <div class="space-y-1">
            <span class="text-[10px] sm:text-[11px] font-bold text-teal-600 uppercase tracking-wider">Rata-rata ASTS</span>
            <p class="text-xl sm:text-2xl font-black text-teal-700 font-lexend">{{ monitoringSummary.school_average || '0.0' }}</p>
            <p class="text-[10px] text-teal-600 font-medium">Skala 0 - 100</p>
          </div>
          <div class="w-11 h-11 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center flex-shrink-0">
            <TrendingUp class="w-5 h-5" />
          </div>
        </div>
      </div>

      <!-- Filter by Grade Level Tabs -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl border border-slate-200/80">
          <button
            v-for="flt in gradeFilterOptions"
            :key="flt.value"
            @click="selectedGradeFilter = flt.value"
            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer"
            :class="selectedGradeFilter === flt.value ? 'bg-white text-emerald-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
          >
            {{ flt.label }}
          </button>
        </div>

        <div class="text-xs text-slate-500 font-medium">
          Menampilkan <strong>{{ filteredMonitoringClasses.length }}</strong> rombel kelas
        </div>
      </div>

      <!-- Classes Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <div
          v-for="cls in filteredMonitoringClasses"
          :key="cls.id"
          class="bg-white rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md hover:border-slate-300 transition-all p-5 flex flex-col justify-between space-y-4"
        >
          <!-- Class Header -->
          <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-3">
            <div class="space-y-0.5">
              <div class="flex items-center gap-2">
                <h3 class="text-base font-black text-slate-800 font-lexend">Kelas {{ cls.name }}</h3>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider" :class="getGradeBadgeClass(cls.grade_level)">
                  Tingkat {{ cls.grade_level }}
                </span>
              </div>
              <p class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                <User class="w-3.5 h-3.5 text-slate-400" />
                <span>Wali: <strong>{{ cls.homeroom_teacher }}</strong></span>
              </p>
            </div>

            <!-- Status Pill -->
            <span
              class="px-2.5 py-1 rounded-full text-[10px] font-black flex items-center gap-1"
              :class="{
                'bg-emerald-100 text-emerald-800 border border-emerald-200': cls.status === 'complete',
                'bg-amber-100 text-amber-800 border border-amber-200': cls.status === 'partial',
                'bg-slate-100 text-slate-600 border border-slate-200': cls.status === 'empty',
              }"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="{
                'bg-emerald-500': cls.status === 'complete',
                'bg-amber-500': cls.status === 'partial',
                'bg-slate-400': cls.status === 'empty',
              }"></span>
              <span>{{ cls.status === 'complete' ? 'Lengkap' : (cls.status === 'partial' ? 'Sebagian' : 'Belum Setor') }}</span>
            </span>
          </div>

          <!-- Progress Bar & Details -->
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="font-bold text-slate-600">Progres Nilai Mapel:</span>
              <span class="font-mono font-black text-slate-800">
                {{ cls.deposited_subjects }} / {{ cls.total_subjects }} Mapel ({{ cls.progress_percent }}%)
              </span>
            </div>

            <!-- Progress Bar Track -->
            <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="cls.progress_percent >= 100 ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : 'bg-gradient-to-r from-amber-400 to-emerald-500'"
                :style="{ width: `${Math.min(cls.progress_percent, 100)}%` }"
              ></div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-2 gap-2 pt-2 text-center text-xs">
              <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                <span class="block text-[10px] text-slate-400 font-bold uppercase">Siswa Terdaftar</span>
                <span class="font-mono font-black text-slate-800 text-sm">{{ cls.students_count }}</span>
              </div>
              <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                <span class="block text-[10px] text-slate-400 font-bold uppercase">Rata-rata Kelas</span>
                <span class="font-mono font-black text-sm" :class="cls.class_avg_score >= 75 ? 'text-emerald-600' : 'text-slate-800'">
                  {{ cls.class_avg_score || '0.0' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Actions Footer -->
          <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
              <FileCheck2 class="w-3.5 h-3.5" :class="cls.has_titimangsa ? 'text-emerald-600' : 'text-slate-300'" />
              <span>{{ cls.has_titimangsa ? 'Titimangsa Siap' : 'Titimangsa Kosong' }}</span>
            </div>

            <RouterLink
              :to="`/admin/asts-reports?class_id=${cls.id}&semester=${selectedSemester}&academic_year_id=${selectedYearId}`"
              class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 active:scale-95 text-emerald-700 font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer"
            >
              <span>Buka Ledger</span>
              <ChevronRight class="w-3.5 h-3.5" />
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from '../composables/useToast';
import { api } from '../api';
import {
  Sliders,
  BookOpenCheck,
  RefreshCw,
  Grid,
  Activity,
  Calendar,
  Layers,
  Sparkles,
  CheckCircle2,
  Inbox,
  Info,
  Building2,
  Clock,
  TrendingUp,
  User,
  FileCheck2,
  ChevronRight,
} from 'lucide-vue-next';

const toast = useToast();

const activeTab = ref('matrix'); // 'matrix' | 'monitoring'
const loading = ref(false);
const savingMatrix = ref(false);

const academicYears = ref([]);
const selectedYearId = ref(null);
const selectedSemester = ref('ganjil');
const selectedGradeFilter = ref('all'); // 'all', '7', '8', '9'

const kktpMatrix = ref([]);
const monitoringSummary = ref({});
const monitoringClasses = ref([]);

const gradeFilterOptions = [
  { value: 'all', label: 'Semua Tingkat' },
  { value: '7', label: 'Tingkat VII (7)' },
  { value: '8', label: 'Tingkat VIII (8)' },
  { value: '9', label: 'Tingkat IX (9)' },
];

function isSpecialSubject(row) {
  const lower = (row.name || '').toLowerCase();
  return lower.includes('arab') || row.code === 'BA' || row.kkm_7 !== row.kkm_8;
}

function getGradeBadgeClass(grade) {
  const g = String(grade);
  if (g.includes('7') || g.includes('VII')) return 'bg-emerald-100 text-emerald-800';
  if (g.includes('8') || g.includes('VIII')) return 'bg-blue-100 text-blue-800';
  if (g.includes('9') || g.includes('IX')) return 'bg-purple-100 text-purple-800';
  return 'bg-slate-100 text-slate-800';
}

const filteredMonitoringClasses = computed(() => {
  if (selectedGradeFilter.value === 'all') return monitoringClasses.value;
  return monitoringClasses.value.filter(c => {
    const raw = String(c.grade_level || '').trim();
    if (selectedGradeFilter.value === '7') return raw.includes('7') || raw.includes('VII');
    if (selectedGradeFilter.value === '8') return raw.includes('8') || raw.includes('VIII');
    if (selectedGradeFilter.value === '9') return raw.includes('9') || raw.includes('IX');
    return true;
  });
});

async function fetchKktpMatrix() {
  loading.value = true;
  try {
    const res = await api.get('/admin/kurikulum/kktp-matrix', {
      params: { academic_year_id: selectedYearId.value },
    });
    const d = res?.data?.data || res?.data;
    if (d) {
      kktpMatrix.value = d.matrix || [];
      academicYears.value = d.academic_years || [];
      if (!selectedYearId.value && d.active_year) {
        selectedYearId.value = d.active_year.id;
      }
    }
  } catch (err) {
    console.error('Failed to load KKTP matrix:', err);
    toast.error('Gagal memuat matriks KKTP: ' + (err?.response?.data?.message || err.message));
  } finally {
    loading.value = false;
  }
}

async function fetchMonitoringOverview() {
  loading.value = true;
  try {
    const res = await api.get('/admin/kurikulum/asts-monitoring', {
      params: {
        academic_year_id: selectedYearId.value,
        semester: selectedSemester.value,
      },
    });
    const d = res?.data?.data || res?.data;
    if (d) {
      monitoringSummary.value = d.summary || {};
      monitoringClasses.value = d.classes || [];
      academicYears.value = d.academic_years || [];
      if (!selectedYearId.value && d.active_year) {
        selectedYearId.value = d.active_year.id;
      }
    }
  } catch (err) {
    console.error('Failed to load ASTS monitoring:', err);
    toast.error('Gagal memuat monitoring setoran ASTS: ' + (err?.response?.data?.message || err.message));
  } finally {
    loading.value = false;
  }
}

function handleFilterChange() {
  if (activeTab.value === 'matrix') {
    fetchKktpMatrix();
  } else {
    fetchMonitoringOverview();
  }
}

function refreshCurrentTab() {
  handleFilterChange();
}

function applyPreset(val) {
  kktpMatrix.value.forEach(row => {
    row.kkm_7 = val;
    row.kkm_8 = val;
    row.kkm_9 = val;
  });
  toast.info(`Semua KKTP diubah menjadi ${val}. Jangan lupa klik "Simpan Perubahan"!`);
}

function applyArabicPreset() {
  kktpMatrix.value.forEach(row => {
    const lower = (row.name || '').toLowerCase();
    const isArab = lower.includes('arab') || row.code === 'BA';
    if (isArab) {
      row.kkm_7 = 70;
      row.kkm_8 = 75;
      row.kkm_9 = 75;
    }
  });
  toast.success('Standar Bahasa Arab berhasil diatur (Tingkat 7: 70, Tingkat 8: 75, Tingkat 9: 75). Klik "Simpan Perubahan"!');
}

async function saveMatrix() {
  savingMatrix.value = true;
  try {
    const payload = {
      academic_year_id: selectedYearId.value,
      items: kktpMatrix.value.map(row => ({
        subject_id: row.id,
        kkm_7: Number(row.kkm_7),
        kkm_8: Number(row.kkm_8),
        kkm_9: Number(row.kkm_9),
      })),
    };

    const res = await api.post('/admin/kurikulum/kktp-matrix', payload);
    toast.success(res?.data?.message || 'Matriks KKTP berhasil disimpan!');
    await fetchKktpMatrix();
  } catch (err) {
    console.error('Save matrix failed:', err);
    toast.error('Gagal menyimpan matriks: ' + (err?.response?.data?.message || err.message));
  } finally {
    savingMatrix.value = false;
  }
}

onMounted(() => {
  fetchKktpMatrix();
  fetchMonitoringOverview();
});
</script>
