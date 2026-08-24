<template>
  <div class="space-y-6 font-inter">
    <!-- Top Header & Actions -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Rekap & Monitoring Presensi</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Laporan rekapitulasi kehadiran siswa, serta pemantauan aktivitas mengabsen guru.</p>
        </div>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <div v-if="activeTab === 'students'" class="relative inline-block text-left">
          <div class="flex items-center gap-2">
            <button
              @click="exportExcel"
              class="px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 transition-colors flex items-center gap-2 shadow-sm cursor-pointer"
            >
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <span>Export Excel (.xlsx)</span>
            </button>

            <button
              @click="exportCsv"
              class="px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl text-xs hover:bg-slate-50 transition-colors flex items-center gap-1.5 shadow-sm cursor-pointer"
              title="Unduh file format CSV"
            >
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              <span>CSV</span>
            </button>

            <button
              @click="openPrintModal"
              class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md shadow-emerald-600/20 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              <span>Cetak Laporan Rekap</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tab Switcher -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-1">
      <button
        @click="activeTab = 'students'"
        :class="[
          activeTab === 'students'
            ? 'bg-emerald-600 text-white font-black shadow-md shadow-emerald-600/20'
            : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200',
          'px-5 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer'
        ]"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        <span>Rekapitulasi Presensi Siswa</span>
      </button>

      <button
        v-if="!isTeacherRole"
        @click="switchTabToTeachers"
        :class="[
          activeTab === 'teachers'
            ? 'bg-emerald-600 text-white font-black shadow-md shadow-emerald-600/20'
            : 'bg-white text-slate-600 hover:bg-slate-100 font-bold border border-slate-200',
          'px-5 py-2.5 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer'
        ]"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        <span>Monitoring Absen Guru</span>
      </button>
    </div>

    <!-- ================= TAB 1: REKAP SISWA ================= -->
    <div v-if="activeTab === 'students'" class="space-y-6">
      <!-- Analytics Stat Cards Grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rata-rata Hadir</span>
          <div class="mt-2 flex items-baseline justify-between">
            <span class="text-2xl font-black text-slate-800 font-lexend">{{ summary.average_percentage || 0 }}%</span>
            <span :class="[summary.average_percentage >= 85 ? 'text-emerald-500 bg-emerald-50' : 'text-amber-500 bg-amber-50', 'text-[10px] font-bold px-2 py-0.5 rounded-full']">
              {{ summary.average_percentage >= 85 ? 'Baik' : 'Cukup' }}
            </span>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
          <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Hadir (H)</span>
          <div class="mt-2 flex items-baseline justify-between">
            <span class="text-2xl font-black text-emerald-600 font-lexend">{{ summary.total_present || 0 }}</span>
            <span class="text-[10px] text-slate-400 font-medium">Slot</span>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
          <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">Sakit (S)</span>
          <div class="mt-2 flex items-baseline justify-between">
            <span class="text-2xl font-black text-blue-600 font-lexend">{{ summary.total_sick || 0 }}</span>
            <span class="text-[10px] text-slate-400 font-medium">Slot</span>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
          <span class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">Izin (I)</span>
          <div class="mt-2 flex items-baseline justify-between">
            <span class="text-2xl font-black text-amber-600 font-lexend">{{ summary.total_permission || 0 }}</span>
            <span class="text-[10px] text-slate-400 font-medium">Slot</span>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
          <span class="text-[10px] font-bold text-red-600 uppercase tracking-widest">Alpa (A)</span>
          <div class="mt-2 flex items-baseline justify-between">
            <span class="text-2xl font-black text-red-600 font-lexend">{{ summary.total_alpha || 0 }}</span>
            <span class="text-[10px] text-slate-400 font-medium">Slot</span>
          </div>
        </div>

        <div
          @click="toggleHighAlphaFilter"
          :class="[
            filterHighAlphaOnly
              ? 'bg-red-600 text-white border-red-600 ring-2 ring-red-300 shadow-md'
              : (summary.high_alpha_students_count > 0 ? 'bg-red-50 border-red-200 cursor-pointer hover:bg-red-100' : 'bg-white border-slate-100 cursor-pointer hover:bg-slate-50'),
            'rounded-2xl p-4 border shadow-sm flex flex-col justify-between transition-all select-none'
          ]"
          :title="filterHighAlphaOnly ? 'Klik untuk tampilkan semua siswa' : 'Klik untuk filter hanya siswa peringatan alpa'"
        >
          <div class="flex items-center justify-between">
            <span :class="filterHighAlphaOnly ? 'text-red-100' : 'text-red-700'" class="text-[10px] font-bold uppercase tracking-widest">Peringatan Alpa</span>
            <span v-if="filterHighAlphaOnly" class="text-[9px] bg-white/20 px-1.5 py-0.5 rounded font-bold">Aktif</span>
          </div>
          <div class="mt-2 flex items-baseline justify-between">
            <span :class="filterHighAlphaOnly ? 'text-white' : 'text-red-700'" class="text-2xl font-black font-lexend">{{ summary.high_alpha_students_count || 0 }}</span>
            <span :class="filterHighAlphaOnly ? 'text-red-100' : 'text-red-600'" class="text-[10px] font-extrabold">Siswa (≥3 A)</span>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-end gap-4">
        <!-- Filter Mode Selector -->
        <div>
          <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Mode Periode</label>
          <div class="flex bg-slate-100 p-1 rounded-xl">
            <button
              @click="setFilterMode('month')"
              :class="filterMode === 'month' ? 'bg-white text-slate-800 font-bold shadow-sm' : 'text-slate-500 font-semibold hover:text-slate-800'"
              class="px-3 py-1.5 text-xs rounded-lg transition-all cursor-pointer"
            >
              Bulanan
            </button>
            <button
              @click="setFilterMode('range')"
              :class="filterMode === 'range' ? 'bg-white text-slate-800 font-bold shadow-sm' : 'text-slate-500 font-semibold hover:text-slate-800'"
              class="px-3 py-1.5 text-xs rounded-lg transition-all cursor-pointer"
            >
              Rentang Tanggal
            </button>
          </div>
        </div>

        <!-- Month Input -->
        <div v-if="filterMode === 'month'" class="w-44">
          <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Bulan Rekapitulasi</label>
          <input
            v-model="selectedMonth"
            type="month"
            @change="fetchReport"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
          />
        </div>

        <!-- Range Inputs -->
        <template v-else>
          <div class="w-36">
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Dari Tanggal</label>
            <input
              v-model="startDate"
              type="date"
              @change="fetchReport"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
            />
          </div>
          <div class="w-36">
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Sampai Tanggal</label>
            <input
              v-model="endDate"
              type="date"
              @change="fetchReport"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
            />
          </div>
        </template>

        <!-- Class Filter -->
        <div class="w-52">
          <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Filter Kelas</label>
          <select
            v-model="selectedClass"
            @change="fetchReport"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer"
          >
            <option value="">-- Semua Kelas --</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
          </select>
        </div>

        <!-- Student Search -->
        <div class="flex-1 min-w-[200px]">
          <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Cari Siswa</label>
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama, NISN, atau NIS..."
              @input="onSearchInput"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
            />
            <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
          </div>
        </div>

        <!-- Reset Filter Button -->
        <button
          v-if="filterHighAlphaOnly || searchQuery || selectedClass"
          @click="resetFilters"
          class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-colors cursor-pointer"
          title="Reset semua filter"
        >
          Reset Filter
        </button>
      </div>

      <!-- Active Filter Banner (if filtered) -->
      <div v-if="filterHighAlphaOnly" class="bg-red-50 border border-red-200 rounded-2xl px-4 py-3 flex items-center justify-between text-xs text-red-800">
        <div class="flex items-center gap-2">
          <span class="font-bold">⚠️ Mode Filter Aktif:</span>
          <span>Menampilkan {{ displayedStudents.length }} siswa dengan peringatan alpa (≥ 3 Alpa).</span>
        </div>
        <button @click="filterHighAlphaOnly = false" class="text-red-700 font-bold hover:underline cursor-pointer">
          Tampilkan Semua Siswa
        </button>
      </div>

      <!-- Table Report -->
      <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
        <div v-if="loading" class="text-center py-16 text-slate-400 text-xs font-medium">
          <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
          Memuat data rekapitulasi presensi...
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50/50">
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA SISWA</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NISN / NIS</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">KELAS</th>
                <th class="px-4 py-4 text-[10px] font-bold text-emerald-600 uppercase tracking-widest text-center">H</th>
                <th class="px-4 py-4 text-[10px] font-bold text-blue-600 uppercase tracking-widest text-center">S</th>
                <th class="px-4 py-4 text-[10px] font-bold text-amber-600 uppercase tracking-widest text-center">I</th>
                <th class="px-4 py-4 text-[10px] font-bold text-red-600 uppercase tracking-widest text-center">A</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">% KEHADIRAN</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">STATUS</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr
                v-for="(row, index) in displayedStudents"
                :key="row.student_id"
                :class="[row.high_alpha_alert ? 'bg-red-50/40 hover:bg-red-50/70' : 'hover:bg-slate-50/70', 'transition-colors']"
              >
                <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-200 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                      <img v-if="row.photo_url && typeof row.photo_url === 'string' && row.photo_url.length > 5" :src="getImageUrl(row.photo_url)" alt="Photo" class="w-full h-full object-cover" />
                      <div v-else :class="row.gender === 'L' ? 'bg-blue-400' : 'bg-pink-400'" class="w-full h-full flex items-center justify-center">
                        {{ getInitials(row.full_name) }}
                      </div>
                    </div>
                    <span class="text-xs font-bold text-slate-800">{{ row.full_name }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-xs font-mono font-bold text-slate-700">{{ row.nisn || '-' }}</span>
                  <span class="text-[10px] text-slate-400 block">NIS: {{ row.nis || '-' }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2.5 py-1 bg-teal-50 border border-teal-100/60 text-teal-800 text-[10px] font-bold rounded-md">
                    {{ row.class_name }}
                  </span>
                </td>

                <td class="px-4 py-4 text-center font-bold text-xs text-emerald-600">{{ row.present }}</td>
                <td class="px-4 py-4 text-center font-bold text-xs text-blue-600">{{ row.sick }}</td>
                <td class="px-4 py-4 text-center font-bold text-xs text-amber-600">{{ row.permission }}</td>
                <td :class="[row.alpha >= 3 ? 'text-red-700 font-black' : 'text-red-500 font-bold', 'px-4 py-4 text-center text-xs']">
                  {{ row.alpha }}
                </td>

                <td class="px-6 py-4">
                  <div class="w-36 mx-auto">
                    <div class="flex items-center justify-between text-[11px] font-extrabold mb-1">
                      <span :class="getPercentageTextColor(row.percentage, row.total_days)">{{ row.total_days > 0 ? row.percentage + '%' : '0%' }}</span>
                      <span class="text-[9px] text-slate-400 font-normal">({{ row.total_days }} Hari)</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                      <div
                        :class="getPercentageBgColor(row.percentage, row.total_days)"
                        class="h-full rounded-full transition-all duration-500"
                        :style="{ width: `${row.total_days > 0 ? Math.min(row.percentage, 100) : 0}%` }"
                      ></div>
                    </div>
                  </div>
                </td>

                <td class="px-6 py-4 text-center">
                  <span
                    v-if="row.total_days === 0"
                    class="px-2.5 py-1 bg-slate-100 text-slate-500 text-[10px] font-bold rounded-lg border border-slate-200"
                  >
                    Belum Ada Data
                  </span>
                  <span
                    v-else-if="row.high_alpha_alert"
                    class="px-2.5 py-1 bg-red-100 text-red-700 text-[10px] font-black rounded-lg uppercase tracking-wider inline-flex items-center gap-1 shadow-sm border border-red-200"
                  >
                    ⚠️ Perhatian Alpa
                  </span>
                  <span
                    v-else-if="row.percentage >= 85"
                    class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-lg border border-emerald-100"
                  >
                    Sangat Baik
                  </span>
                  <span
                    v-else-if="row.percentage >= 70"
                    class="px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-lg border border-amber-100"
                  >
                    Cukup
                  </span>
                  <span
                    v-else
                    class="px-2.5 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded-lg border border-red-100"
                  >
                    Kurang
                  </span>
                </td>
              </tr>

              <tr v-if="!displayedStudents.length">
                <td colspan="10" class="px-6 py-16 text-center text-slate-400 text-xs font-semibold">
                  Tidak ada data rekap presensi ditemukan untuk periode dan filter ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= TAB 2: MONITORING ABSEN GURU ================= -->
    <div v-if="activeTab === 'teachers'" class="space-y-6">
      <!-- Summary Cards for Teacher Monitoring -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Total Guru</span>
            <p class="text-2xl font-black text-slate-800 font-lexend mt-1">{{ monitoringSummary.total_teachers || 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          </div>
        </div>

        <div class="bg-emerald-50/60 rounded-2xl p-5 border border-emerald-100 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-[10px] font-extrabold text-emerald-700 uppercase tracking-widest">Sudah Mengabsen Hari Ini</span>
            <p class="text-2xl font-black text-emerald-700 font-lexend mt-1">{{ monitoringSummary.submitted_today_count || 0 }} <span class="text-xs font-semibold text-emerald-600">Guru</span></p>
          </div>
          <div class="w-12 h-12 bg-emerald-500 text-white rounded-2xl flex items-center justify-center shadow-md shadow-emerald-500/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Total Sesi Mengabsen</span>
            <p class="text-2xl font-black text-slate-800 font-lexend mt-1">{{ monitoringSummary.total_sessions_month || 0 }} <span class="text-xs font-semibold text-slate-400">Sesi</span></p>
          </div>
          <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
          </div>
        </div>
      </div>

      <!-- Filter Bar for Teachers -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center gap-4">
        <div class="w-48">
          <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Bulan Monitoring</label>
          <input
            v-model="monitoringMonth"
            type="month"
            @change="fetchMonitoring"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
          />
        </div>

        <div class="flex-1 min-w-[200px]">
          <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Cari Guru</label>
          <div class="relative">
            <input
              v-model="monitoringSearch"
              type="text"
              placeholder="Cari nama guru atau NIP..."
              @input="onMonitoringSearchInput"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
            />
            <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
          </div>
        </div>
      </div>

      <!-- Teacher Cards Grid -->
      <div v-if="monitoringLoading" class="text-center py-16 text-slate-400 text-xs font-medium">
        <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
        Memuat aktivitas mengabsen guru...
      </div>

      <div v-else-if="teachersMonitoring.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="teacher in teachersMonitoring"
          :key="teacher.teacher_id"
          class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all flex flex-col justify-between space-y-4"
        >
          <!-- Header Guru Card -->
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-2xl overflow-hidden bg-slate-200 flex items-center justify-center text-white text-sm font-black flex-shrink-0 shadow-sm">
                <img v-if="teacher.photo_url && typeof teacher.photo_url === 'string' && teacher.photo_url.length > 5" :src="getImageUrl(teacher.photo_url)" alt="Photo" class="w-full h-full object-cover" />
                <div v-else :class="teacher.gender === 'L' ? 'bg-blue-600' : 'bg-pink-600'" class="w-full h-full flex items-center justify-center">
                  {{ getInitials(teacher.full_name) }}
                </div>
              </div>
              <div>
                <h3 class="text-sm font-bold text-slate-800 font-lexend line-clamp-1">{{ teacher.full_name }}</h3>
                <span class="text-[11px] font-mono text-slate-400 font-semibold block mt-0.5">NIP: {{ teacher.nip || '-' }}</span>
              </div>
            </div>

            <!-- Status Hari Ini Badge -->
            <span
              v-if="teacher.submitted_today"
              class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[9px] font-extrabold rounded-lg uppercase tracking-wider border border-emerald-100 flex-shrink-0"
            >
              🟢 Sudah Absen
            </span>
            <span
              v-else
              class="px-2.5 py-1 bg-slate-100 text-slate-500 text-[9px] font-extrabold rounded-lg uppercase tracking-wider border border-slate-200 flex-shrink-0"
            >
              ⚪ Belum Absen
            </span>
          </div>

          <!-- Badges Matpel yang diampu -->
          <div class="space-y-1.5">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Mata Pelajaran</span>
            <div class="flex flex-wrap gap-1.5">
              <span
                v-for="sbj in teacher.subjects"
                :key="sbj"
                class="px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-100 text-[10px] font-bold rounded-lg"
              >
                {{ sbj }}
              </span>
              <span v-if="!teacher.subjects.length" class="text-xs text-slate-400 italic">Mata Pelajaran Umum</span>
            </div>
          </div>

          <!-- Bottom Action & Session Count -->
          <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500">
              <strong class="text-slate-800 font-lexend text-sm">{{ teacher.total_sessions }}</strong> Sesi Absen
            </span>

            <button
              @click="openTeacherJournalModal(teacher)"
              class="px-4 py-2 bg-emerald-50 hover:bg-emerald-600 text-emerald-800 hover:text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-1.5 cursor-pointer"
            >
              <span>Jurnal Absen</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>
      </div>

      <div v-else class="bg-white rounded-3xl p-16 text-center text-slate-400 text-xs font-semibold border border-slate-100">
        Tidak ada data guru ditemukan untuk filter ini.
      </div>
    </div>

    <!-- Print Preview Modal -->
    <div v-if="showPrintModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-[2rem] max-w-4xl w-full p-8 shadow-2xl space-y-6 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-black text-slate-800 font-lexend uppercase">Cetak Laporan Rekapitulasi Presensi</h3>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Pratinjau laporan resmi siap cetak ke printer / PDF.</p>
          </div>
          <button @click="showPrintModal = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400 cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div id="print-area" class="flex-1 overflow-y-auto p-6 bg-white border border-slate-200 rounded-2xl text-slate-800 space-y-6">
          <!-- Kop Surat -->
          <div class="flex items-center gap-5 pb-4 border-b-2 border-slate-800">
            <img v-if="settings.app_logo" :src="settings.app_logo" class="w-16 h-16 object-contain flex-shrink-0" alt="Logo" />
            <div v-else class="w-16 h-16 bg-slate-900 text-white font-black text-xl rounded-xl flex items-center justify-center flex-shrink-0">
              SCH
            </div>
            <div class="flex-1 text-center">
              <h2 class="text-lg font-black font-lexend uppercase tracking-wider">{{ settings.app_name || 'SISTEM AKADEMIK SEKOLAH / MADRASAH' }}</h2>
              <p class="text-xs text-slate-600 font-medium">{{ settings.app_tagline || 'Laporan Rekapitulasi Kehadiran Siswa' }}</p>
              <p class="text-[10px] text-slate-500 font-mono mt-0.5">{{ settings.school_address || 'Jl. Pendidikan No. 123, Indonesia' }}</p>
            </div>
          </div>

          <div class="text-center space-y-1">
            <h3 class="text-sm font-black uppercase font-lexend tracking-wide">LAPORAN REKAPITULASI PRESENSI SISWA</h3>
            <p class="text-xs font-bold text-slate-600">
              Periode: {{ getPeriodLabel() }}
            </p>
            <p class="text-[11px] text-slate-500 font-medium">Kelas: {{ getSelectedClassName() }} | Total Siswa: {{ displayedStudents.length }}</p>
          </div>

          <table class="w-full text-xs text-left border-collapse border border-slate-300">
            <thead>
              <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-300">
                <th class="border border-slate-300 px-3 py-2 text-center w-10">NO</th>
                <th class="border border-slate-300 px-3 py-2">NAMA SISWA</th>
                <th class="border border-slate-300 px-3 py-2">NISN</th>
                <th class="border border-slate-300 px-3 py-2">KELAS</th>
                <th class="border border-slate-300 px-2 py-2 text-center">H</th>
                <th class="border border-slate-300 px-2 py-2 text-center">S</th>
                <th class="border border-slate-300 px-2 py-2 text-center">I</th>
                <th class="border border-slate-300 px-2 py-2 text-center">A</th>
                <th class="border border-slate-300 px-3 py-2 text-center">% HADIR</th>
                <th class="border border-slate-300 px-3 py-2 text-center">STATUS</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, idx) in displayedStudents" :key="item.student_id" class="border-b border-slate-200">
                <td class="border border-slate-300 px-3 py-1.5 text-center font-bold">{{ idx + 1 }}</td>
                <td class="border border-slate-300 px-3 py-1.5 font-bold">{{ item.full_name }}</td>
                <td class="border border-slate-300 px-3 py-1.5 font-mono">{{ item.nisn || '-' }}</td>
                <td class="border border-slate-300 px-3 py-1.5">{{ item.class_name }}</td>
                <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-emerald-700">{{ item.present }}</td>
                <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-blue-700">{{ item.sick }}</td>
                <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-amber-700">{{ item.permission }}</td>
                <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-red-700">{{ item.alpha }}</td>
                <td class="border border-slate-300 px-3 py-1.5 text-center font-extrabold">{{ item.percentage }}%</td>
                <td class="border border-slate-300 px-3 py-1.5 text-center text-[10px] font-semibold">
                  {{ item.high_alpha_alert ? 'Peringatan Alpa' : (item.percentage >= 85 ? 'Sangat Baik' : (item.percentage >= 70 ? 'Cukup' : 'Kurang')) }}
                </td>
              </tr>
              <tr v-if="!displayedStudents.length">
                <td colspan="10" class="border border-slate-300 px-4 py-8 text-center text-slate-400">
                  Tidak ada data siswa untuk dicetak.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Tanda Tangan Resmi -->
          <div class="pt-8 grid grid-cols-2 text-center text-xs font-semibold">
            <div>
              <p>Mengetahui,</p>
              <p class="font-bold">Kepala Sekolah / Madrasah</p>
              <div class="h-16"></div>
              <p class="font-bold underline">{{ settings.principal_name || '............................................' }}</p>
              <p class="text-[10px] text-slate-500 font-mono">NIP: {{ settings.principal_nip || '-' }}</p>
            </div>
            <div>
              <p>{{ getTodayDateFormatted() }}</p>
              <p class="font-bold">Wali Kelas {{ getSelectedClassName() !== 'Semua Kelas' ? getSelectedClassName() : '' }}</p>
              <div class="h-16"></div>
              <p class="font-bold underline">{{ getHomeroomTeacherName() }}</p>
              <p class="text-[10px] text-slate-500 font-mono">NIP: {{ getHomeroomTeacherNip() }}</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button @click="showPrintModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
            Tutup
          </button>
          <button @click="triggerPrint" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-emerald-600/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Sekarang</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Teacher Journal Detail Modal -->
    <div v-if="selectedTeacherForJournal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-[2rem] max-w-3xl w-full p-7 shadow-2xl space-y-5 max-h-[90vh] flex flex-col">
        <!-- Header Modal -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-200 flex items-center justify-center text-white text-xs font-black flex-shrink-0">
              <img v-if="selectedTeacherForJournal.photo_url && typeof selectedTeacherForJournal.photo_url === 'string' && selectedTeacherForJournal.photo_url.length > 5" :src="getImageUrl(selectedTeacherForJournal.photo_url)" class="w-full h-full object-cover" />
              <div v-else :class="selectedTeacherForJournal.gender === 'L' ? 'bg-blue-600' : 'bg-pink-600'" class="w-full h-full flex items-center justify-center">
                {{ getInitials(selectedTeacherForJournal.full_name) }}
              </div>
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800 font-lexend">{{ selectedTeacherForJournal.full_name }}</h3>
              <p class="text-xs text-slate-400 font-medium">Jurnal Aktivitas Mengabsen Siswa (Periode: {{ monitoringMonth }})</p>
            </div>
          </div>
          <button @click="selectedTeacherForJournal = null" class="p-2 hover:bg-slate-100 rounded-full text-slate-400 cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Journal Entries List -->
        <div class="flex-1 overflow-y-auto space-y-3 pr-1">
          <div
            v-for="(session, idx) in selectedTeacherForJournal.journal_entries"
            :key="idx"
            class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-[10px] font-black rounded-md uppercase">
                  {{ session.class_name }}
                </span>
                <span class="text-xs font-bold text-slate-800">
                  {{ session.subject_name }}
                </span>
              </div>
              <p class="text-[11px] font-semibold text-slate-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Tanggal: <strong class="text-slate-700">{{ session.date }}</strong>
              </p>
            </div>

            <!-- Breakdown Counters -->
            <div class="flex items-center gap-2">
              <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-lg">
                {{ session.present }} H
              </span>
              <span class="px-2 py-1 bg-blue-100 text-blue-800 text-[10px] font-bold rounded-lg">
                {{ session.sick }} S
              </span>
              <span class="px-2 py-1 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-lg">
                {{ session.permission }} I
              </span>
              <span class="px-2 py-1 bg-red-100 text-red-800 text-[10px] font-bold rounded-lg">
                {{ session.alpha }} A
              </span>
              <span class="px-2.5 py-1 bg-emerald-600 text-white text-[10px] font-black rounded-lg uppercase flex items-center gap-1 shadow-sm">
                ✓ Selesai
              </span>
            </div>
          </div>

          <div v-if="!selectedTeacherForJournal.journal_entries?.length" class="text-center py-12 text-slate-400 text-xs font-semibold">
            Belum ada aktivitas mengabsen yang tercatat untuk guru ini pada periode {{ monitoringMonth }}.
          </div>
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
          <button @click="selectedTeacherForJournal = null" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import * as XLSX from 'xlsx';
import { api } from '../api';
import { useToast } from '../composables/useToast';

const route = useRoute();
const toast = useToast();

const isTeacherRole = computed(() => route.path.startsWith('/teacher'));
const activeTab = ref('students'); // 'students' | 'teachers'

const loading = ref(true);
const showPrintModal = ref(false);

// Filter States
const filterMode = ref('month'); // 'month' | 'range'
const selectedMonth = ref(new Date().toISOString().substring(0, 7));
const startDate = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().substring(0, 10));
const endDate = ref(new Date().toISOString().substring(0, 10));
const selectedClass = ref('');
const searchQuery = ref('');
const filterHighAlphaOnly = ref(false);

const summary = reactive({
  average_percentage: 0,
  total_present: 0,
  total_sick: 0,
  total_permission: 0,
  total_alpha: 0,
  high_alpha_students_count: 0,
  period: {},
});

const studentsReport = ref([]);
const classes = ref([]);
const settings = ref({});

// Monitoring State
const monitoringLoading = ref(false);
const monitoringMonth = ref(new Date().toISOString().substring(0, 7));
const monitoringSearch = ref('');
const teachersMonitoring = ref([]);
const selectedTeacherForJournal = ref(null);
const monitoringSummary = reactive({
  total_teachers: 0,
  submitted_today_count: 0,
  total_sessions_month: 0,
});

const displayedStudents = computed(() => {
  if (!filterHighAlphaOnly.value) return studentsReport.value;
  return studentsReport.value.filter(s => s.alpha >= 3 || s.high_alpha_alert);
});

const toggleHighAlphaFilter = () => {
  filterHighAlphaOnly.value = !filterHighAlphaOnly.value;
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedClass.value = '';
  filterHighAlphaOnly.value = false;
  fetchReport();
};

const setFilterMode = (mode) => {
  filterMode.value = mode;
  fetchReport();
};

const getImageUrl = (path) => {
  if (!path) return '';
  if (typeof path !== 'string') return '';
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path.replace(/^\//, '');
};

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function getPercentageBgColor(pct, totalDays = 1) {
  if (totalDays === 0) return 'bg-slate-300';
  if (pct >= 85) return 'bg-emerald-500';
  if (pct >= 70) return 'bg-amber-500';
  return 'bg-red-500';
}

function getPercentageTextColor(pct, totalDays = 1) {
  if (totalDays === 0) return 'text-slate-400';
  if (pct >= 85) return 'text-emerald-600';
  if (pct >= 70) return 'text-amber-600';
  return 'text-red-600';
}

const fetchReport = async () => {
  loading.value = true;
  try {
    const endpoint = isTeacherRole.value ? 'teacher/attendance-reports' : 'admin/attendance-reports';
    const params = {};

    if (filterMode.value === 'range') {
      if (startDate.value) params.start_date = startDate.value;
      if (endDate.value) params.end_date = endDate.value;
    } else {
      params.month = selectedMonth.value;
    }

    if (selectedClass.value) params.class_id = selectedClass.value;
    if (searchQuery.value) params.search = searchQuery.value;

    const res = await api.get(endpoint, params);
    const data = res?.data || {};

    Object.assign(summary, data.summary || {});
    studentsReport.value = data.students || [];
    classes.value = data.classes || [];
  } catch (err) {
    console.error('Failed to load attendance report:', err);
    toast.error('Gagal memuat rekapitulasi presensi');
  } finally {
    loading.value = false;
  }
};

const fetchMonitoring = async () => {
  monitoringLoading.value = true;
  try {
    const params = {
      month: monitoringMonth.value,
    };
    if (monitoringSearch.value) params.search = monitoringSearch.value;

    const res = await api.get('admin/attendance-monitoring', params);
    const data = res?.data || {};

    Object.assign(monitoringSummary, data.summary || {});
    teachersMonitoring.value = data.teachers || [];
  } catch (err) {
    console.error('Failed to load attendance monitoring:', err);
    toast.error('Gagal memuat monitoring absen guru');
  } finally {
    monitoringLoading.value = false;
  }
};

const switchTabToTeachers = () => {
  activeTab.value = 'teachers';
  fetchMonitoring();
};

let monitoringSearchTimeout = null;
const onMonitoringSearchInput = () => {
  clearTimeout(monitoringSearchTimeout);
  monitoringSearchTimeout = setTimeout(() => {
    fetchMonitoring();
  }, 400);
};

const openTeacherJournalModal = (teacher) => {
  selectedTeacherForJournal.value = teacher;
};

const fetchSettings = async () => {
  try {
    const res = await api.get('settings');
    settings.value = res?.data || {};
  } catch {}
};

let searchTimeout = null;
const onSearchInput = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchReport();
  }, 400);
};

onMounted(() => {
  fetchReport();
  fetchSettings();
});

const openPrintModal = () => {
  showPrintModal.value = true;
};

const getSelectedClassName = () => {
  if (!selectedClass.value) return 'Semua Kelas';
  const cls = classes.value.find(c => c.id == selectedClass.value);
  return cls ? cls.name : 'Kelas';
};

const getHomeroomTeacherName = () => {
  if (!selectedClass.value) return '( ............................................ )';
  const cls = classes.value.find(c => c.id == selectedClass.value);
  if (cls && cls.homeroom_teacher) {
    return cls.homeroom_teacher.full_name;
  }
  return '( ............................................ )';
};

const getHomeroomTeacherNip = () => {
  if (!selectedClass.value) return '-';
  const cls = classes.value.find(c => c.id == selectedClass.value);
  return cls?.homeroom_teacher?.nip || '-';
};

const formatMonthName = (monthStr) => {
  if (!monthStr) return '';
  const date = new Date(monthStr + '-01');
  return date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
};

const getPeriodLabel = () => {
  if (filterMode.value === 'range') {
    return `${startDate.value || '-'} s.d. ${endDate.value || '-'}`;
  }
  return formatMonthName(selectedMonth.value);
};

const getTodayDateFormatted = () => {
  const d = new Date();
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

// ================= EXPORT XLSX (SHEETJS) =================
const exportExcel = () => {
  const dataset = displayedStudents.value;
  if (!dataset.length) {
    toast.error('Tidak ada data untuk diekspor ke Excel');
    return;
  }

  const schoolName = settings.value?.app_name || 'SEKOLAH / MADRASAH';
  const period = getPeriodLabel();
  const className = getSelectedClassName();

  // Excel rows
  const rows = [
    [schoolName.toUpperCase()],
    ['LAPORAN REKAPITULASI PRESENSI SISWA'],
    [`Periode: ${period} | Kelas: ${className}`],
    [`Dicetak Pada: ${new Date().toLocaleString('id-ID')}`],
    [],
    ['NO', 'NAMA SISWA', 'NISN', 'NIS', 'KELAS', 'HADIR (H)', 'SAKIT (S)', 'IZIN (I)', 'ALPA (A)', 'TOTAL HARI', '% KEHADIRAN', 'STATUS']
  ];

  dataset.forEach((row, idx) => {
    let statusStr = 'Cukup';
    if (row.total_days === 0) statusStr = 'Belum Ada Data';
    else if (row.alpha >= 3) statusStr = 'Peringatan Alpa (>=3)';
    else if (row.percentage >= 85) statusStr = 'Sangat Baik';
    else if (row.percentage < 70) statusStr = 'Kurang';

    rows.push([
      idx + 1,
      row.full_name,
      row.nisn ? String(row.nisn) : '-',
      row.nis ? String(row.nis) : '-',
      row.class_name,
      row.present,
      row.sick,
      row.permission,
      row.alpha,
      row.total_days,
      `${row.percentage}%`,
      statusStr
    ]);
  });

  const ws = XLSX.utils.aoa_to_sheet(rows);

  // Set column widths
  ws['!cols'] = [
    { wch: 6 },
    { wch: 30 },
    { wch: 18 },
    { wch: 14 },
    { wch: 16 },
    { wch: 10 },
    { wch: 10 },
    { wch: 10 },
    { wch: 10 },
    { wch: 12 },
    { wch: 15 },
    { wch: 22 }
  ];

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Rekap Presensi');

  const cleanMonth = (filterMode.value === 'range' ? `${startDate.value}_sd_${endDate.value}` : selectedMonth.value).replace(/[^a-zA-Z0-9_-]/g, '_');
  XLSX.writeFile(wb, `Rekap_Presensi_${cleanMonth}.xlsx`);
  toast.success('Laporan Excel berhasil diunduh!');
};

// ================= EXPORT CSV (UTF-8 BOM) =================
const exportCsv = () => {
  const dataset = displayedStudents.value;
  if (!dataset.length) {
    toast.error('Tidak ada data untuk diekspor');
    return;
  }

  // Use UTF-8 BOM so Excel opens with proper character encoding
  let csvContent = '\uFEFF';
  csvContent += 'NO;NAMA SISWA;NISN;NIS;KELAS;HADIR;SAKIT;IZIN;ALPA;TOTAL HARI;% KEHADIRAN\n';

  dataset.forEach((row, idx) => {
    csvContent += `${idx + 1};"${row.full_name.replace(/"/g, '""')}";'${row.nisn || ''};'${row.nis || ''};"${row.class_name}";${row.present};${row.sick};${row.permission};${row.alpha};${row.total_days};${row.percentage}%\n`;
  });

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', `rekap_presensi_${selectedMonth.value}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  toast.success('Laporan CSV berhasil diunduh!');
};

// ================= CLEAN PRINT (NO RELOAD / SPA SAFE) =================
const triggerPrint = () => {
  const printAreaEl = document.getElementById('print-area');
  if (!printAreaEl) return;

  const content = printAreaEl.innerHTML;
  const printWindow = window.open('', '_blank', 'width=900,height=700');
  if (!printWindow) {
    toast.error('Popup terblokir oleh browser. Izinkan popup untuk mencetak.');
    return;
  }

  printWindow.document.open();
  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="id">
      <head>
        <meta charset="utf-8">
        <title>Laporan Rekapitulasi Presensi</title>
        <style>
          @page {
            size: A4 portrait;
            margin: 15mm;
          }
          * {
            box-sizing: border-box;
          }
          body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 10px;
          }
          h2, h3, p {
            margin: 0 0 4px 0;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
          }
          th, td {
            border: 1px solid #64748b;
            padding: 5px 6px;
          }
          th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold { font-weight: bold; }
          .font-mono { font-family: monospace; }
          .underline { text-decoration: underline; }
          .border-b-2 { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
          .flex { display: flex; align-items: center; }
          .grid { display: grid; grid-template-columns: 1fr 1fr; }
          .gap-5 { gap: 20px; }
          .h-16 { height: 60px; }
          .pt-8 { padding-top: 25px; }
          img { max-height: 60px; max-width: 60px; }
        </style>
      </head>
      <body>
        ${content}
      </body>
    </html>
  `);
  printWindow.document.close();

  // Trigger print after resources load
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 400);
};
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
