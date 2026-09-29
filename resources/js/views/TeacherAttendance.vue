<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Presensi Kehadiran Siswa</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Input kehadiran siswa berbasis mata pelajaran dan kelas mengajar secara presisi.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="selectedClass"
          type="button"
          @click="resetSelection"
          class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors flex items-center gap-1.5 cursor-pointer shadow-2xs"
        >
          <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          <span>Kembali ke Riwayat</span>
        </button>

        <button
          v-if="students.length > 0"
          @click="setAllPresent"
          class="px-4 py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          <span>Set Semua Hadir</span>
        </button>
      </div>
    </div>

    <!-- Options Selector Card -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-5">
      <!-- 1. Mata Pelajaran -->
      <div class="space-y-1.5">
        <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          Mata Pelajaran
        </label>
        <select
          v-model="selectedSubject"
          @change="loadStudents"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
        >
          <option value="">-- Tanpa Matpel (Presensi Umum) --</option>
          <option v-for="sbj in subjects" :key="sbj.id" :value="sbj.id">{{ sbj.name }} ({{ sbj.code || '-' }})</option>
        </select>
      </div>

      <!-- 2. Pilih Kelas -->
      <div class="space-y-1.5">
        <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          Pilih Kelas Mengajar <span class="text-red-500">*</span>
        </label>
        <select
          v-model="selectedClass"
          @change="loadStudents"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
        >
          <option value="">-- Pilih Kelas Mengajar --</option>
          <optgroup v-if="utamaClasses.length > 0" label="🏫 Rombel Utama (Reguler: 7A, 7B, 8A, 8B, 9A, 9B)">
            <option v-for="cls in utamaClasses" :key="'cls-u-' + cls.id" :value="cls.id">
              🏫 Kelas {{ cls.name }} (Tingkat {{ cls.grade_level }}) — {{ cls.students_count || 0 }} Siswa
            </option>
          </optgroup>
          <optgroup v-if="lokalClasses.length > 0" label="📍 Kelompok / Jadwal Lokal (7, 8, 9A, 9B)">
            <option v-for="cls in lokalClasses" :key="'cls-l-' + cls.id" :value="cls.id">
              📍 Kelas {{ cls.name }} (Lokal Tingkat {{ cls.grade_level }}) — {{ cls.lokal_students_count || cls.students_count || 0 }} Siswa
            </option>
          </optgroup>
        </select>
      </div>

      <!-- 3. Tanggal -->
      <div class="space-y-1.5">
        <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Tanggal Presensi <span class="text-red-500">*</span>
        </label>
        <input
          v-model="selectedDate"
          type="date"
          @change="loadStudents"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400"
        />
      </div>
    </div>

    <!-- Live Counter Summary Badges -->
    <div v-if="selectedClass && students.length > 0" class="grid grid-cols-2 sm:grid-cols-5 gap-3">
      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Total Siswa</span>
        <p class="text-xl font-black text-slate-800 font-lexend mt-1">{{ students.length }}</p>
      </div>

      <div class="bg-emerald-50/60 rounded-2xl p-4 border border-emerald-100 text-center">
        <span class="text-[10px] font-extrabold text-emerald-700 uppercase tracking-widest">Hadir (H)</span>
        <p class="text-xl font-black text-emerald-700 font-lexend mt-1">{{ countStatus('present') }}</p>
      </div>

      <div class="bg-blue-50/60 rounded-2xl p-4 border border-blue-100 text-center">
        <span class="text-[10px] font-extrabold text-blue-700 uppercase tracking-widest">Sakit (S)</span>
        <p class="text-xl font-black text-blue-700 font-lexend mt-1">{{ countStatus('sick') }}</p>
      </div>

      <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-100 text-center">
        <span class="text-[10px] font-extrabold text-amber-700 uppercase tracking-widest">Izin (I)</span>
        <p class="text-xl font-black text-amber-700 font-lexend mt-1">{{ countStatus('permission') }}</p>
      </div>

      <div class="bg-red-50/60 rounded-2xl p-4 border border-red-100 text-center col-span-2 sm:col-span-1">
        <span class="text-[10px] font-extrabold text-red-700 uppercase tracking-widest">Alpa (A)</span>
        <p class="text-xl font-black text-red-700 font-lexend mt-1">{{ countStatus('alpha') }}</p>
      </div>
    </div>

    <!-- Attendance Sheet Container -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-16 text-slate-400 text-xs font-medium">
        <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
        Memuat lembar presensi siswa...
      </div>

      <!-- No Class Selected State -> Display Teacher Attendance History (Grid Cards Per Kelas) -->
      <div v-else-if="!selectedClass" class="p-6 sm:p-8 space-y-6">
        <!-- Top Toolbar Header -->
        <div class="p-6 bg-gradient-to-r from-emerald-50 via-teal-50/60 to-white rounded-3xl border border-emerald-100/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-600/20 flex-shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-sm font-black text-slate-800 font-lexend uppercase tracking-wider">Riwayat Sesi Mengabsen Berdasarkan Kelas</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                  {{ filteredHistory.length }} Sesi Terdata
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-0.5 font-medium">Klik pada kartu sesi untuk membuka & mengubah presensi, atau pilih filter kelas di bawah untuk melihat per rombel.</p>
            </div>
          </div>

          <button
            type="button"
            @click="fetchHistory"
            :disabled="loadingHistory"
            class="px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-all flex items-center gap-2 shadow-2xs self-start md:self-auto cursor-pointer"
          >
            <svg class="w-3.5 h-3.5 text-slate-500" :class="{ 'animate-spin': loadingHistory }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Segarkan Riwayat</span>
          </button>
        </div>

        <!-- Filter Bertingkat: Kelas Utama & Lokal -->
        <div v-if="attendanceHistory.length > 0" class="space-y-3 bg-slate-50/80 p-4 rounded-2xl border border-slate-200/90 shadow-2xs">
          <!-- 1. Kelas Utama Tab (Rombel Reguler: 7A, 7B, 8A, 8B, 9A, 9B) -->
          <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-thin">
            <span class="text-[11px] font-black uppercase tracking-wider text-slate-700 shrink-0 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              <span>Kelas Utama:</span>
            </span>
            <button
              type="button"
              v-for="uTab in historyUtamaTabs"
              :key="'tab-u-' + uTab.value"
              @click="selectUtamaTab(uTab.value)"
              class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer shadow-2xs"
              :class="activeCategory === 'utama' && selectedClassFilter === uTab.value ? 'bg-slate-900 text-white shadow-md shadow-slate-900/20' : 'bg-white hover:bg-slate-100 text-slate-600 border border-slate-200'"
            >
              <span>{{ uTab.label }}</span>
              <span
                class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                :class="activeCategory === 'utama' && selectedClassFilter === uTab.value ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'"
              >
                {{ uTab.count }}
              </span>
            </button>
          </div>

          <!-- 2. Lokal Tab (Kelompok / Lokal: 7, 8, 9A, 9B) -->
          <div v-if="historyLokalTabs.length > 0" class="flex items-center gap-2 overflow-x-auto pt-2 pb-1 scrollbar-thin border-t border-slate-200/80">
            <span class="text-[11px] font-black uppercase tracking-wider text-slate-700 shrink-0 flex items-center gap-1.5">
              <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              <span>Lokal:</span>
            </span>
            <button
              type="button"
              v-for="lTab in historyLokalTabs"
              :key="'tab-l-' + lTab.value"
              @click="selectLokalTab(lTab.value)"
              class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer shadow-2xs"
              :class="activeCategory === 'lokal' && selectedClassFilter === lTab.value ? 'bg-teal-700 text-white shadow-md shadow-teal-700/20' : 'bg-white hover:bg-teal-50 text-slate-600 border border-slate-200'"
            >
              <span>{{ lTab.label }}</span>
              <span
                class="px-1.5 py-0.2 rounded-full text-[10px] font-black"
                :class="activeCategory === 'lokal' && selectedClassFilter === lTab.value ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'"
              >
                {{ lTab.count }}
              </span>
            </button>
          </div>
        </div>

        <!-- History Loading -->
        <div v-if="loadingHistory" class="text-center py-16 text-slate-400 text-xs font-medium">
          <svg class="animate-spin h-7 w-7 text-emerald-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
          Memuat kartu riwayat mengabsen...
        </div>

        <!-- Empty History State -->
        <div v-else-if="!filteredHistory.length" class="text-center py-16 text-slate-400">
          <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-200">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <p class="text-xs font-bold text-slate-700">Tidak Ada Riwayat Pada Filter Ini</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Pilih tab kelas lain atau isi form presensi di atas untuk kelas ini.</p>
        </div>

        <!-- Modern Responsive Cards Grid per Kelas -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="hist in filteredHistory"
            :key="`${hist.class_id}-${hist.subject_id}-${hist.date}`"
            class="bg-white rounded-2xl border border-slate-200 hover:border-emerald-300 p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between group"
          >
            <!-- Card Header: Class & Subject Badge -->
            <div class="space-y-2">
              <div class="flex items-start justify-between gap-2">
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black border"
                  :class="isLokalClassName(hist.class_name) ? 'bg-teal-50 text-teal-800 border-teal-200' : 'bg-slate-100 text-slate-800 border-slate-200'"
                >
                  <svg v-if="!isLokalClassName(hist.class_name)" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                  <svg v-else class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                  <span>{{ isLokalClassName(hist.class_name) ? `📍 Lokal ${hist.class_name}` : `🏫 Kelas ${hist.class_name}` }} (Tkt {{ hist.grade_level }})</span>
                </span>

                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-black"
                  :class="hist.present_count === hist.total_students ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                >
                  {{ hist.present_count === hist.total_students ? '✓ Hadir 100%' : `${Math.round((hist.present_count / (hist.total_students || 1)) * 100)}% Kehadiran` }}
                </span>
              </div>

              <!-- Subject & Date -->
              <div>
                <h3 class="text-sm font-black text-slate-800 font-lexend group-hover:text-emerald-700 transition-colors">
                  {{ hist.subject_name }}
                </h3>
                <div class="flex items-center gap-1.5 mt-1 text-slate-500 text-xs">
                  <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  <span class="font-bold text-slate-700">{{ formatDateIndo(hist.date) }}</span>
                </div>
              </div>
            </div>

            <!-- Attendance Stats Progress Mini Grid -->
            <div class="my-4 pt-3.5 border-t border-slate-100 space-y-2">
              <div class="flex items-center justify-between text-[11px] font-bold text-slate-500">
                <span>Rincian Kehadiran:</span>
                <span class="font-mono font-black text-slate-700">{{ hist.total_students }} Siswa</span>
              </div>

              <div class="grid grid-cols-4 gap-1.5 text-center">
                <div class="p-1.5 rounded-xl bg-emerald-50 border border-emerald-100">
                  <span class="text-[9px] font-extrabold text-emerald-700 block uppercase">Hadir</span>
                  <span class="font-black text-xs font-mono text-emerald-800">{{ hist.present_count }}</span>
                </div>
                <div class="p-1.5 rounded-xl bg-blue-50 border border-blue-100">
                  <span class="text-[9px] font-extrabold text-blue-700 block uppercase">Sakit</span>
                  <span class="font-black text-xs font-mono text-blue-800">{{ hist.sick_count }}</span>
                </div>
                <div class="p-1.5 rounded-xl bg-amber-50 border border-amber-100">
                  <span class="text-[9px] font-extrabold text-amber-700 block uppercase">Izin</span>
                  <span class="font-black text-xs font-mono text-amber-800">{{ hist.permission_count }}</span>
                </div>
                <div class="p-1.5 rounded-xl bg-red-50 border border-red-100">
                  <span class="text-[9px] font-extrabold text-red-700 block uppercase">Alpa</span>
                  <span class="font-black text-xs font-mono text-red-800">{{ hist.alpha_count }}</span>
                </div>
              </div>

              <!-- Mini Progress Bar -->
              <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden flex mt-2">
                <div
                  :style="{ width: `${(hist.present_count / (hist.total_students || 1)) * 100}%` }"
                  class="bg-emerald-500 h-full"
                  title="Hadir"
                ></div>
                <div
                  :style="{ width: `${(hist.sick_count / (hist.total_students || 1)) * 100}%` }"
                  class="bg-blue-500 h-full"
                  title="Sakit"
                ></div>
                <div
                  :style="{ width: `${(hist.permission_count / (hist.total_students || 1)) * 100}%` }"
                  class="bg-amber-500 h-full"
                  title="Izin"
                ></div>
                <div
                  :style="{ width: `${(hist.alpha_count / (hist.total_students || 1)) * 100}%` }"
                  class="bg-red-500 h-full"
                  title="Alpa"
                ></div>
              </div>
            </div>

            <!-- Card Action Footer -->
            <div class="pt-2 flex items-center justify-between text-xs">
              <span class="text-[10px] text-slate-400 font-medium">
                {{ hist.last_updated_at }}
              </span>

              <button
                type="button"
                @click="openSessionFromHistory(hist)"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm shadow-emerald-600/20 flex items-center gap-1.5 cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Buka & Edit</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-12">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">SISWA</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NISN / NIS</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">STATUS KEHADIRAN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">CATATAN / KETERANGAN</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(student, index) in students" :key="student.student_id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ index + 1 }}</td>
              
              <!-- Siswa -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-200 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                    <img v-if="student.photo_url" :src="student.photo_url" alt="Photo" class="w-full h-full object-cover" />
                    <div v-else :class="student.gender === 'L' ? 'bg-blue-400' : 'bg-pink-400'" class="w-full h-full flex items-center justify-center">
                      {{ getInitials(student.full_name) }}
                    </div>
                  </div>
                  <span class="text-sm font-bold text-slate-800">{{ student.full_name }}</span>
                </div>
              </td>

              <!-- NISN / NIS -->
              <td class="px-6 py-4">
                <span class="text-xs font-mono font-bold text-slate-700 block">{{ student.nisn || '-' }}</span>
                <span class="text-[10px] text-slate-400 block">NIS: {{ student.nis || '-' }}</span>
              </td>

              <!-- Pill Buttons for Status (H / S / I / A) -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- Hadir -->
                  <button
                    type="button"
                    @click="student.status = 'present'"
                    :class="[
                      student.status === 'present'
                        ? 'bg-emerald-600 text-white font-black shadow-md shadow-emerald-500/20 scale-105'
                        : 'bg-slate-100 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 font-bold',
                      'px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer'
                    ]"
                  >
                    Hadir (H)
                  </button>

                  <!-- Sakit -->
                  <button
                    type="button"
                    @click="student.status = 'sick'"
                    :class="[
                      student.status === 'sick'
                        ? 'bg-blue-600 text-white font-black shadow-md shadow-blue-500/20 scale-105'
                        : 'bg-slate-100 text-slate-500 hover:bg-blue-50 hover:text-blue-700 font-bold',
                      'px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer'
                    ]"
                  >
                    Sakit (S)
                  </button>

                  <!-- Izin -->
                  <button
                    type="button"
                    @click="student.status = 'permission'"
                    :class="[
                      student.status === 'permission'
                        ? 'bg-amber-500 text-white font-black shadow-md shadow-amber-500/20 scale-105'
                        : 'bg-slate-100 text-slate-500 hover:bg-amber-50 hover:text-amber-700 font-bold',
                      'px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer'
                    ]"
                  >
                    Izin (I)
                  </button>

                  <!-- Alpa -->
                  <button
                    type="button"
                    @click="student.status = 'alpha'"
                    :class="[
                      student.status === 'alpha'
                        ? 'bg-red-600 text-white font-black shadow-md shadow-red-500/20 scale-105'
                        : 'bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-700 font-bold',
                      'px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer'
                    ]"
                  >
                    Alpa (A)
                  </button>
                </div>
              </td>

              <!-- Catatan -->
              <td class="px-6 py-4">
                <input
                  v-model="student.note"
                  type="text"
                  placeholder="Keterangan opsional..."
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 font-medium"
                />
              </td>
            </tr>

            <tr v-if="!students.length">
              <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs font-semibold">
                Tidak ada siswa terdaftar di kelas ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Bottom Save Action Bar -->
      <div v-if="selectedClass && students.length > 0" class="px-8 py-5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500">
          {{ students.length }} Siswa Siap Disimpan
        </span>

        <button
          @click="submitAttendance"
          :disabled="submitting"
          class="px-7 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-all shadow-lg flex items-center gap-2 cursor-pointer disabled:opacity-50"
        >
          <svg v-if="submitting" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
          <span>{{ submitting ? 'Menyimpan Presensi...' : 'Simpan Presensi Siswa' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';

const toast = useToast();

const loading = ref(false);
const submitting = ref(false);

const subjects = ref([]);
const classes = ref([]);
const students = ref([]);

const selectedSubject = ref('');
const selectedClass = ref('');
const selectedDate = ref(new Date().toISOString().substring(0, 10)); // YYYY-MM-DD

const attendanceHistory = ref([]);
const loadingHistory = ref(false);
const activeCategory = ref('utama'); // 'utama' | 'lokal'
const selectedClassFilter = ref('all'); // 'all' atau nama kelas spesifik (e.g., '7A', '8A', '7', '8', dsb.)

// Helper klasifikasi Kelas Utama vs Kelas Lokal
function isLokalClassName(name) {
  if (!name) return false;
  const match = (classes.value || []).find(c => c.name === name);
  if (match) return isLokalClass(match);

  const raw = String(name).trim();
  const lower = raw.toLowerCase();
  const clean = raw.replace(/^(kelas|kls)\s*/i, '').trim().toLowerCase();

  // Standalone numbers 7 and 8 are specifically for Jadwal Lokal
  if (clean === '7' || clean === '8' || lower === 'kelas 7' || lower === 'kelas 8') return true;
  // Explicit keywords
  if (lower.includes('lokal') || lower.startsWith('l-') || lower.startsWith('lok-')) return true;

  return false;
}

function isLokalClass(c) {
  if (!c) return false;
  const raw = String(c.name || '').trim();
  const lower = raw.toLowerCase();
  const clean = raw.replace(/^(kelas|kls)\s*/i, '').trim().toLowerCase();

  // Standalone numbers 7 and 8 are specifically for Jadwal Lokal
  if (clean === '7' || clean === '8' || lower === 'kelas 7' || lower === 'kelas 8') return true;

  // Kelas 9A & 9B bisa menjadi lokal jika ditandai is_lokal dari backend atau memiliki lokalStudents
  if (c.is_lokal === true) return true;

  // Custom lokal class ids from localStorage if set
  try {
    const customLokalIds = JSON.parse(localStorage.getItem('siakad_lokal_class_ids') || '[]');
    if (Array.isArray(customLokalIds) && customLokalIds.includes(c.id)) return true;
  } catch (e) {}

  // Explicit keywords
  if (lower.includes('lokal') || lower.startsWith('l-') || lower.startsWith('lok-')) return true;

  return false;
}

function isUtamaClass(c) {
  if (!c) return false;
  const raw = String(c.name || '').trim();
  const lower = raw.toLowerCase();
  const clean = raw.replace(/^(kelas|kls)\s*/i, '').trim().toLowerCase();

  // Standalone numbers 7 and 8 are strictly for Jadwal Lokal (NOT utama)
  if (clean === '7' || clean === '8' || lower === 'kelas 7' || lower === 'kelas 8') return false;

  // Explicit only lokal
  if (lower.includes('lokal-only') || lower.includes('jadwal-lokal-only')) return false;

  return true;
}

const utamaClasses = computed(() => {
  return (classes.value || []).filter(isUtamaClass);
});

const lokalClasses = computed(() => {
  return (classes.value || []).filter(isLokalClass);
});

// Helper memilih tab
const selectUtamaTab = (val) => {
  activeCategory.value = 'utama';
  selectedClassFilter.value = val;
};

const selectLokalTab = (val) => {
  activeCategory.value = 'lokal';
  selectedClassFilter.value = val;
};

// 1. Tab Kelas Utama (Rombel Reguler: 7A, 7B, 8A, 8B, 9A, 9B)
const historyUtamaTabs = computed(() => {
  if (!attendanceHistory.value.length) return [];

  // Sesi riwayat yang tergolong Kelas Utama
  const utamaSessions = attendanceHistory.value.filter(item => !isLokalClassName(item.class_name));

  const map = new Map();
  utamaSessions.forEach(item => {
    const name = String(item.class_name || '').trim();
    if (!map.has(name)) {
      map.set(name, {
        value: name,
        label: `Kelas ${name}`,
        grade: Number(item.grade_level) || 0,
        count: 0
      });
    }
    map.get(name).count += 1;
  });

  const list = Array.from(map.values()).sort((a, b) => {
    if (a.grade !== b.grade) return a.grade - b.grade;
    return a.value.localeCompare(b.value, undefined, { numeric: true });
  });

  return [
    { value: 'all', label: 'Semua Kelas Utama', count: utamaSessions.length },
    ...list
  ];
});

// 2. Tab Lokal (Jadwal Lokal: 7, 8, 9A, 9B)
const historyLokalTabs = computed(() => {
  if (!attendanceHistory.value.length) return [];

  // Sesi riwayat yang tergolong Kelas Lokal
  const lokalSessions = attendanceHistory.value.filter(item => isLokalClassName(item.class_name));

  const map = new Map();
  lokalSessions.forEach(item => {
    const name = String(item.class_name || '').trim();
    if (!map.has(name)) {
      map.set(name, {
        value: name,
        label: `Lokal ${name}`,
        grade: Number(item.grade_level) || 0,
        count: 0
      });
    }
    map.get(name).count += 1;
  });

  const list = Array.from(map.values()).sort((a, b) => {
    if (a.grade !== b.grade) return a.grade - b.grade;
    return a.value.localeCompare(b.value, undefined, { numeric: true });
  });

  return [
    { value: 'all', label: 'Semua Lokal', count: lokalSessions.length },
    ...list
  ];
});

// 3. Filter riwayat presensi berdasarkan kategori aktif (Utama / Lokal) dan kelas yang dipilih
const filteredHistory = computed(() => {
  return attendanceHistory.value.filter(item => {
    const isLokal = isLokalClassName(item.class_name);

    if (activeCategory.value === 'utama') {
      if (isLokal) return false;
      if (selectedClassFilter.value !== 'all') {
        return String(item.class_name).trim().toLowerCase() === selectedClassFilter.value.trim().toLowerCase();
      }
      return true;
    } else {
      // lokal
      if (!isLokal) return false;
      if (selectedClassFilter.value !== 'all') {
        return String(item.class_name).trim().toLowerCase() === selectedClassFilter.value.trim().toLowerCase();
      }
      return true;
    }
  });
});

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function countStatus(statusKey) {
  return students.value.filter(s => s.status === statusKey).length;
}

function setAllPresent() {
  students.value.forEach(s => {
    s.status = 'present';
  });
  toast.success('Semua siswa diset Hadir (H)');
}

const fetchOptions = async () => {
  try {
    const res = await api.get('teacher/attendance-options');
    const data = res?.data || res || {};
    subjects.value = data.subjects || [];
    classes.value = data.classes || [];

    if (!subjects.value.length) {
      const sRes = await api.get('admin/subjects').catch(() => null);
      subjects.value = sRes?.data?.data || sRes?.data || [];
    }
    if (!classes.value.length) {
      const cRes = await api.get('admin/classes').catch(() => null);
      classes.value = cRes?.data?.data || cRes?.data || [];
    }
  } catch (err) {
    console.error('Failed to load attendance options:', err);
    try {
      const [sRes, cRes] = await Promise.all([
        api.get('admin/subjects').catch(() => null),
        api.get('admin/classes').catch(() => null),
      ]);
      subjects.value = sRes?.data?.data || sRes?.data || [];
      classes.value = cRes?.data?.data || cRes?.data || [];
    } catch {}
  }
};

const loadStudents = async () => {
  if (!selectedClass.value) {
    students.value = [];
    return;
  }

  loading.value = true;
  try {
    const params = {
      class_id: selectedClass.value,
      date: selectedDate.value,
    };
    if (selectedSubject.value) params.subject_id = selectedSubject.value;

    const res = await api.get('teacher/attendance', params);
    const data = res?.data || {};

    students.value = (data.students || []).map(s => ({
      student_id: s.student_id,
      full_name: s.full_name,
      nisn: s.nisn,
      nis: s.nis,
      gender: s.gender,
      photo_url: s.photo_url,
      status: s.status || 'present',
      note: s.note || '',
    }));
  } catch (err) {
    console.error('Failed to load students for attendance:', err);
    toast.error('Gagal memuat daftar siswa');
    students.value = [];
  } finally {
    loading.value = false;
  }
};

const formatDateIndo = (dateStr) => {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
      weekday: 'long',
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
  } catch {
    return dateStr;
  }
};

const fetchHistory = async () => {
  loadingHistory.value = true;
  try {
    const res = await api.get('teacher/attendance/history');
    attendanceHistory.value = res?.data?.data || res?.data || [];
  } catch (err) {
    console.error('Failed to load attendance history:', err);
  } finally {
    loadingHistory.value = false;
  }
};

const openSessionFromHistory = async (session) => {
  if (!session) return;
  selectedClass.value = session.class_id;
  selectedSubject.value = session.subject_id || '';
  if (session.date) {
    selectedDate.value = session.date;
  }
  await loadStudents();
  toast.info(`Membuka sesi presensi ${session.subject_name} - Kelas ${session.class_name} (${session.date})`);
};

const resetSelection = () => {
  selectedClass.value = '';
  students.value = [];
  fetchHistory();
};

onMounted(async () => {
  await Promise.all([
    fetchOptions(),
    fetchHistory()
  ]);
});

const submitAttendance = async () => {
  if (!selectedClass.value) {
    toast.error('Pilih kelas mengajar terlebih dahulu');
    return;
  }

  submitting.value = true;
  try {
    const payload = {
      class_id: selectedClass.value,
      subject_id: selectedSubject.value || null,
      date: selectedDate.value,
      attendances: students.value.map(s => ({
        student_id: s.student_id,
        status: s.status,
        note: s.note,
      })),
    };

    await api.post('teacher/attendance', payload);
    toast.success('Presensi siswa berhasil disimpan');
    fetchHistory();
  } catch (err) {
    console.error('Failed to save attendance:', err);
    toast.error('Gagal menyimpan presensi siswa');
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
