<template>
  <div class="space-y-6 font-sans">
    <!-- Vibrant Emerald Hero Banner (Operator TU & Persuratan) -->
    <div class="relative bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white rounded-2xl shadow-lg shadow-emerald-700/20 overflow-hidden border border-emerald-500/40">
      <!-- Subtle Background Mesh Grid & Glow -->
      <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.18)_1px,transparent_1px)] [background-size:22px_22px] opacity-60 pointer-events-none"></div>
      <div class="absolute -top-16 -right-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>

      <!-- Banner Content -->
      <div class="relative z-10 p-4 sm:p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-5">
          <!-- Photo Frame -->
          <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-white/15 backdrop-blur-md rounded-2xl border border-white/30 p-1 flex items-center justify-center flex-shrink-0 overflow-hidden relative shadow-md">
            <img
              v-if="userPhoto"
              :src="getImageUrl(userPhoto)"
              class="w-full h-full object-cover rounded-xl shadow-inner"
              alt="Foto Profil"
            />
            <div v-else class="w-full h-full rounded-xl bg-emerald-800 flex items-center justify-center text-white font-bold text-xl sm:text-2xl uppercase">
              {{ (auth.user?.name || 'O').charAt(0) }}
            </div>
            <!-- Online status indicator -->
            <span class="absolute bottom-1 right-1 w-3 h-3 sm:w-3.5 sm:h-3.5 bg-emerald-300 border-2 border-emerald-800 rounded-full shadow-xs"></span>
          </div>

          <!-- Profile Details -->
          <div class="space-y-1.5 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-full text-[10px] sm:text-[11px] font-bold border border-white/30 shadow-xs">
                <FileText class="w-3.5 h-3.5 text-emerald-200" />
                <span>Tata Usaha &bull; @{{ auth.user?.username }}</span>
              </span>
              <span class="inline-flex items-center gap-1 px-2.5 sm:px-3 py-1 bg-amber-300/20 backdrop-blur-md text-amber-100 rounded-full text-[10px] sm:text-[11px] font-bold border border-amber-300/40">
                <span>T.A. 2026/2027</span>
              </span>
            </div>

            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight text-white uppercase leading-tight drop-shadow-xs truncate">
              {{ auth.user?.name || 'Operator TU' }}
            </h1>
            <p class="text-emerald-100 text-xs sm:text-sm font-normal max-w-xl leading-relaxed">
              Ringkasan aktivitas tata usaha, agenda persuratan madrasah, dan arsip dokumen kependidikan secara terintegrasi.
            </p>
          </div>
        </div>

        <!-- Quick Action Shortcuts -->
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap md:flex-col lg:flex-row gap-2 sm:gap-2.5 flex-shrink-0">
          <RouterLink
            to="/operator/letters"
            class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md transition-all active:scale-95 text-center"
          >
            <Inbox class="w-4 h-4 flex-shrink-0" />
            <span class="truncate">Buku Agenda</span>
          </RouterLink>

          <RouterLink
            to="/admin/print-center"
            class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md transition-all active:scale-95 shadow-xs text-center"
          >
            <Printer class="w-4 h-4 flex-shrink-0" />
            <span class="truncate">Pusat Cetak</span>
          </RouterLink>

          <RouterLink
            to="/admin/students"
            class="inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold bg-teal-900/80 hover:bg-teal-900 text-white border border-teal-400/40 backdrop-blur-md transition-all active:scale-95 shadow-xs text-center"
          >
            <Users class="w-4 h-4 flex-shrink-0" />
            <span class="truncate">Data Siswa</span>
          </RouterLink>

          <button
            v-if="auth.isDualRole"
            @click="switchToTeacher"
            class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold bg-emerald-500/30 hover:bg-emerald-500/50 text-white border border-emerald-300/40 backdrop-blur-md transition-all active:scale-95 shadow-xs cursor-pointer text-center"
          >
            <GraduationCap class="w-4 h-4 text-emerald-200 flex-shrink-0" />
            <span class="truncate">Mode Guru &rarr;</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 4 Bento Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Surat Masuk -->
      <RouterLink to="/operator/letters?tab=incoming" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all group flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between gap-2">
            <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Surat Masuk</span>
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
              <Inbox class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <p class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900">{{ stats.total_incoming || 0 }}</p>
            <span class="text-[10px] sm:text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-md">Agenda</span>
          </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[10px] sm:text-[11px]">
          <span class="text-slate-400 truncate">Bulan ini</span>
          <span class="font-bold text-slate-700">{{ stats.this_month_incoming || 0 }} surat</span>
        </div>
      </RouterLink>

      <!-- Card 2: Surat Keluar -->
      <RouterLink to="/operator/letters?tab=outgoing" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-indigo-400 hover:shadow-md transition-all group flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between gap-2">
            <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Surat Keluar</span>
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
              <Send class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <p class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900">{{ stats.total_outgoing || 0 }}</p>
            <span class="text-[10px] sm:text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded-md">Terbit</span>
          </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[10px] sm:text-[11px]">
          <span class="text-slate-400 truncate">Bulan ini</span>
          <span class="font-bold text-slate-700">{{ stats.this_month_outgoing || 0 }} surat</span>
        </div>
      </RouterLink>

      <!-- Card 3: Siswa Aktif -->
      <RouterLink to="/admin/students" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-teal-400 hover:shadow-md transition-all group flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between gap-2">
            <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Siswa Aktif</span>
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
              <Users class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <p class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900">{{ stats.total_students || 0 }}</p>
            <span class="text-[10px] sm:text-[11px] font-semibold text-teal-600 bg-teal-50 px-1.5 py-0.5 rounded-md">Terdaftar</span>
          </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[10px] sm:text-[11px]">
          <span class="text-slate-400 truncate">L: {{ genderStats.L }} | P: {{ genderStats.P }}</span>
          <span class="font-bold text-slate-700">{{ stats.total_classes || 0 }} Rombel</span>
        </div>
      </RouterLink>

      <!-- Card 4: Dewan Guru & Staf -->
      <RouterLink to="/admin/teachers" class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-amber-400 hover:shadow-md transition-all group flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between gap-2">
            <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider truncate">Dewan Guru</span>
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
              <UserCheck class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-2 flex items-baseline gap-2">
            <p class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900">{{ stats.total_teachers || 0 }}</p>
            <span class="text-[10px] sm:text-[11px] font-semibold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-md">Aktif</span>
          </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[10px] sm:text-[11px]">
          <span class="text-slate-400 truncate">Status</span>
          <span class="font-bold text-emerald-600">Terverifikasi</span>
        </div>
      </RouterLink>
    </div>

    <!-- Charts & Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <!-- Monthly Letter Activity Bar Chart (8 Cols on Desktop) -->
      <div class="lg:col-span-8 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
        <div>
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div>
              <h2 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
                <BarChart3 class="w-4 h-4 text-emerald-600" />
                Tren Aktivitas Persuratan Madrasah
              </h2>
              <p class="text-xs text-slate-500 mt-0.5">Frekuensi volume surat masuk dan surat keluar tahun kalender {{ currentYear }}</p>
            </div>
            <!-- Chart Legend -->
            <div class="flex items-center gap-3 text-xs font-medium text-slate-600 self-start sm:self-auto">
              <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-emerald-500 inline-block"></span>
                <span>Surat Masuk</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-indigo-500 inline-block"></span>
                <span>Surat Keluar</span>
              </div>
            </div>
          </div>

          <!-- SVG Grouped Bar Chart -->
          <div class="mt-6 relative">
            <div v-if="loadingLetters" class="py-16 text-center text-slate-400 text-sm flex items-center justify-center gap-2">
              <span class="inline-block w-4 h-4 border-2 border-emerald-500 border-t-transparent rounded-full animate-spin"></span>
              <span>Memuat data grafik persuratan...</span>
            </div>

            <div v-else class="w-full overflow-x-auto pb-2">
              <div class="min-w-[480px]">
                <svg viewBox="0 0 650 200" class="w-full h-48 sm:h-56 overflow-visible" preserveAspectRatio="none">
                  <!-- Horizontal Grid Lines -->
                  <line x1="40" y1="20" x2="630" y2="20" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                  <text x="32" y="24" text-anchor="end" font-size="10" fill="#94a3b8">{{ maxMonthlyVal }}</text>

                  <line x1="40" y1="80" x2="630" y2="80" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                  <text x="32" y="84" text-anchor="end" font-size="10" fill="#94a3b8">{{ Math.round(maxMonthlyVal * 0.66) }}</text>

                  <line x1="40" y1="140" x2="630" y2="140" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                  <text x="32" y="144" text-anchor="end" font-size="10" fill="#94a3b8">{{ Math.round(maxMonthlyVal * 0.33) }}</text>

                  <line x1="40" y1="180" x2="630" y2="180" stroke="#cbd5e1" stroke-width="1" />
                  <text x="32" y="184" text-anchor="end" font-size="10" fill="#94a3b8">0</text>

                  <!-- Month Bars -->
                  <g v-for="(item, idx) in monthlyTrends" :key="idx">
                    <!-- Month label -->
                    <text
                      :x="80 + idx * 45"
                      y="196"
                      text-anchor="middle"
                      font-size="10"
                      font-weight="600"
                      fill="#64748b"
                    >
                      {{ item.shortMonth }}
                    </text>

                    <!-- Incoming Bar (Emerald) -->
                    <rect
                      :x="66 + idx * 45"
                      :y="180 - (item.incoming / maxMonthlyVal) * 160"
                      width="12"
                      :height="Math.max(2, (item.incoming / maxMonthlyVal) * 160)"
                      rx="3"
                      class="fill-emerald-500 hover:fill-emerald-600 transition-all cursor-pointer"
                    >
                      <title>{{ item.monthName }}: {{ item.incoming }} Surat Masuk</title>
                    </rect>

                    <!-- Outgoing Bar (Indigo) -->
                    <rect
                      :x="82 + idx * 45"
                      :y="180 - (item.outgoing / maxMonthlyVal) * 160"
                      width="12"
                      :height="Math.max(2, (item.outgoing / maxMonthlyVal) * 160)"
                      rx="3"
                      class="fill-indigo-500 hover:fill-indigo-600 transition-all cursor-pointer"
                    >
                      <title>{{ item.monthName }}: {{ item.outgoing }} Surat Keluar</title>
                    </rect>

                    <!-- Value above bar if active month -->
                    <text
                      v-if="item.incoming > 0"
                      :x="72 + idx * 45"
                      :y="Math.max(15, 175 - (item.incoming / maxMonthlyVal) * 160)"
                      text-anchor="middle"
                      font-size="9"
                      font-weight="bold"
                      fill="#059669"
                    >
                      {{ item.incoming }}
                    </text>
                  </g>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-2">
          <div class="flex items-center gap-1.5">
            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
            <span>Rekapitulasi otomatis diselaraskan dengan buku agenda persuratan madrasah.</span>
          </div>
          <RouterLink to="/operator/letters" class="text-emerald-600 hover:text-emerald-700 font-bold inline-flex items-center gap-1">
            <span>Buka Seluruh Agenda</span>
            <ChevronRight class="w-3.5 h-3.5" />
          </RouterLink>
        </div>
      </div>

      <!-- Donut Chart / Category & Demographic Breakdown (4 Cols) -->
      <div class="lg:col-span-4 space-y-5">
        <!-- Letter Categories Widget -->
        <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
          <h2 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2 mb-1">
            <PieChart class="w-4 h-4 text-indigo-600" />
            Distribusi Kategori Surat
          </h2>
          <p class="text-xs text-slate-500 mb-4">Komposisi jenis surat yang dikelola TU</p>

          <div class="flex items-center justify-center my-3">
            <!-- Mini SVG Donut Chart -->
            <div class="relative w-36 h-36 flex items-center justify-center">
              <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                <!-- Background track -->
                <circle cx="18" cy="18" r="14" fill="none" stroke="#f1f5f9" stroke-width="4.5" />
                <!-- Category Segments -->
                <circle
                  v-for="(seg, idx) in categorySegments"
                  :key="idx"
                  cx="18"
                  cy="18"
                  r="14"
                  fill="none"
                  :stroke="seg.color"
                  stroke-width="4.5"
                  :stroke-dasharray="`${seg.dash} ${100 - seg.dash}`"
                  :stroke-dashoffset="seg.offset"
                  stroke-linecap="round"
                  class="transition-all duration-500"
                />
              </svg>
              <!-- Center Metric -->
              <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span class="text-xl font-black text-slate-800">{{ totalLettersCount }}</span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total</span>
              </div>
            </div>
          </div>

          <!-- Category Legend List -->
          <div class="space-y-2 mt-4 text-xs">
            <div v-for="(cat, idx) in categoryList" :key="idx" class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: cat.color }"></span>
                <span class="text-slate-600 font-medium">{{ cat.label }}</span>
              </div>
              <span class="font-bold text-slate-800">{{ cat.count }} ({{ cat.pct }}%)</span>
            </div>
          </div>
        </div>

        <!-- Student Demographic Breakdown -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Demografi Siswa</h3>
            <span class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-md">Rasio Gender</span>
          </div>

          <!-- Progress Bar L vs P -->
          <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden flex">
            <div
              class="h-full bg-blue-500 transition-all duration-500"
              :style="{ width: `${genderPctL}%` }"
              title="Laki-laki"
            ></div>
            <div
              class="h-full bg-rose-400 transition-all duration-500"
              :style="{ width: `${genderPctP}%` }"
              title="Perempuan"
            ></div>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs pt-1">
            <div class="bg-blue-50/70 p-2 rounded-xl border border-blue-100 flex items-center justify-between">
              <span class="text-blue-700 font-medium">Laki-laki</span>
              <span class="font-bold text-blue-900">{{ genderStats.L }} ({{ genderPctL }}%)</span>
            </div>
            <div class="bg-rose-50/70 p-2 rounded-xl border border-rose-100 flex items-center justify-between">
              <span class="text-rose-700 font-medium">Perempuan</span>
              <span class="font-bold text-rose-900">{{ genderStats.P }} ({{ genderPctP }}%)</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Letters Mini-List & Quick Operator Shortcuts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <!-- 5 Recent Letters Table/Cards (8 Cols) -->
      <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
              <Clock class="w-4 h-4 text-emerald-600" />
              Agenda Persuratan Terkini
            </h2>
            <p class="text-xs text-slate-500">5 arsip persuratan terakhir yang dicatat dalam sistem</p>
          </div>
          <RouterLink
            to="/operator/letters"
            class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1"
          >
            <span>Lihat Semua</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </RouterLink>
        </div>

        <div v-if="loadingLetters" class="p-8 text-center text-slate-400 text-sm">
          Memuat data surat...
        </div>

        <div v-else-if="recentLetters.length === 0" class="p-8 text-center">
          <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
            <Inbox class="w-6 h-6" />
          </div>
          <p class="text-sm font-semibold text-slate-600">Belum ada surat yang tercatat</p>
          <p class="text-xs text-slate-400 mt-0.5">Mulai catat surat masuk atau buat surat keluar baru</p>
          <RouterLink
            to="/operator/letters"
            class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition-all"
          >
            <PlusCircle class="w-3.5 h-3.5" />
            <span>Tambah Surat Sekarang</span>
          </RouterLink>
        </div>

        <!-- Responsive Table / Card List -->
        <div v-else class="divide-y divide-slate-100">
          <div
            v-for="letter in recentLetters"
            :key="letter.id"
            class="p-3.5 sm:p-4 hover:bg-slate-50/70 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div class="flex items-start gap-3 min-w-0">
              <!-- Type Icon -->
              <div
                class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5"
                :class="letter.type === 'incoming' ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600'"
              >
                <Inbox v-if="letter.type === 'incoming'" class="w-4 h-4" />
                <Send v-else class="w-4 h-4" />
              </div>

              <!-- Info -->
              <div class="min-w-0 space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <span
                    class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider"
                    :class="letter.type === 'incoming' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800'"
                  >
                    {{ letter.type === 'incoming' ? 'Surat Masuk' : 'Surat Keluar' }}
                  </span>
                  <span class="text-xs font-semibold text-slate-700 truncate max-w-[200px] sm:max-w-xs">
                    {{ letter.reference_number || 'Tanpa No. Surat' }}
                  </span>
                  <span v-if="letter.agenda_number" class="text-[11px] text-slate-400">
                    (Agenda #{{ letter.agenda_number }})
                  </span>
                </div>

                <p class="text-xs font-bold text-slate-900 truncate">
                  {{ letter.subject || 'Tanpa Perihal' }}
                </p>

                <div class="flex items-center gap-3 text-[11px] text-slate-500 flex-wrap">
                  <span v-if="letter.type === 'incoming'">Dari: <b class="text-slate-700 font-medium">{{ letter.sender || '-' }}</b></span>
                  <span v-else>Kepada: <b class="text-slate-700 font-medium">{{ letter.recipient || '-' }}</b></span>
                  <span>&bull;</span>
                  <span>{{ formatDate(letter.letter_date) }}</span>
                </div>
              </div>
            </div>

            <!-- Status Badge & Action -->
            <div class="flex items-center justify-between sm:justify-end gap-2.5 flex-shrink-0 pl-12 sm:pl-0">
              <span
                class="px-2.5 py-1 rounded-full text-[10px] font-bold capitalize"
                :class="getStatusBadgeClass(letter.status)"
              >
                {{ formatStatus(letter.status) }}
              </span>
              <RouterLink
                :to="`/operator/letters?tab=${letter.type}&search=${encodeURIComponent(letter.reference_number || letter.agenda_number || '')}`"
                class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-100 transition-colors"
                title="Buka detail surat"
              >
                <ChevronRight class="w-4 h-4" />
              </RouterLink>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Operational Shortcuts (4 Cols) -->
      <div class="lg:col-span-4 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
        <div>
          <h2 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
            <Sparkles class="w-4 h-4 text-amber-500" />
            Aksi Cepat Tata Usaha
          </h2>
          <p class="text-xs text-slate-500 mt-0.5">Pintasan tugas operasional rutin & layanan surat</p>
        </div>

        <div class="space-y-2.5">
          <!-- Shortcut 1: Catat Surat Masuk -->
          <RouterLink
            to="/operator/letters?tab=incoming"
            class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-emerald-50 hover:border-emerald-200 transition-all group"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                <Inbox class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs font-bold text-slate-800 group-hover:text-emerald-900">Agenda Surat Masuk</p>
                <p class="text-[10px] text-slate-400">Catat surat masuk & lembar disposisi</p>
              </div>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 group-hover:text-emerald-600" />
          </RouterLink>

          <!-- Shortcut 2: Surat Keluar -->
          <RouterLink
            to="/operator/letters?tab=outgoing"
            class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-indigo-50 hover:border-indigo-200 transition-all group"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                <Send class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs font-bold text-slate-800 group-hover:text-indigo-900">Terbitkan Surat Keluar</p>
                <p class="text-[10px] text-slate-400">Registrasi penomoran surat resmi</p>
              </div>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 group-hover:text-indigo-600" />
          </RouterLink>

          <!-- Shortcut 3: Surat Keterangan Siswa -->
          <RouterLink
            to="/operator/letters?tab=student_cert"
            class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-teal-50 hover:border-teal-200 transition-all group"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                <Award class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs font-bold text-slate-800 group-hover:text-teal-900">Surat Ket. Siswa Aktif</p>
                <p class="text-[10px] text-slate-400">Cetak otomatis format kop madrasah</p>
              </div>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 group-hover:text-teal-600" />
          </RouterLink>

          <!-- Shortcut 4: Rekap & Cetak Agenda -->
          <RouterLink
            to="/operator/letters?tab=agenda_print"
            class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-amber-50 hover:border-amber-200 transition-all group"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                <Printer class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs font-bold text-slate-800 group-hover:text-amber-900">Cetak Agenda & Arsip</p>
                <p class="text-[10px] text-slate-400">Format buku agenda dinas periode</p>
              </div>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 group-hover:text-amber-600" />
          </RouterLink>

          <!-- Shortcut 5: Manajemen Data Siswa -->
          <RouterLink
            to="/admin/students"
            class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/60 hover:bg-slate-100 transition-all group"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                <Users class="w-4 h-4" />
              </div>
              <div>
                <p class="text-xs font-bold text-slate-800">Master Data Siswa</p>
                <p class="text-[10px] text-slate-400">Kelola biodata, NISN, & mutasi</p>
              </div>
            </div>
            <ChevronRight class="w-4 h-4 text-slate-400 group-hover:text-slate-600" />
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  FileText,
  Inbox,
  Send,
  Printer,
  Users,
  UserCheck,
  GraduationCap,
  PlusCircle,
  ArrowRight,
  Clock,
  CheckCircle2,
  PieChart,
  BarChart3,
  Award,
  Sparkles,
  ChevronRight
} from 'lucide-vue-next';
import { api } from '../api';

const auth = useAuthStore();
const router = useRouter();

const stats = ref({});
const livePhoto = ref(null);
const loadingLetters = ref(false);
const allLetters = ref([]);
const recentLetters = ref([]);
const genderStats = ref({ L: 0, P: 0 });
const currentYear = new Date().getFullYear();

function switchToTeacher() {
  auth.switchRole('teacher');
  router.push('/teacher/dashboard');
}

const userPhoto = computed(() => {
  return livePhoto.value || auth.user?.teacher?.photo_url || auth.user?.teacher?.photo || auth.user?.photo_url || auth.user?.photo || auth.user?.avatar || null;
});

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  if (path.startsWith('data:image')) return path;
  const clean = path.startsWith('/') ? path : `/${path}`;
  if (clean.startsWith('/storage/')) return clean;
  return `/storage/${path.replace(/^\/+/, '')}`;
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
  } catch {
    return dateStr;
  }
}

function formatStatus(status) {
  switch (status) {
    case 'archived': return 'Diarsipkan';
    case 'dispositioned': return 'Didisposisi';
    case 'sent': return 'Terkirim';
    case 'draft': return 'Draf';
    case 'pending': return 'Menunggu';
    default: return status || 'Tercatat';
  }
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'archived': return 'bg-emerald-100 text-emerald-800';
    case 'dispositioned': return 'bg-purple-100 text-purple-800';
    case 'sent': return 'bg-blue-100 text-blue-800';
    case 'draft': return 'bg-amber-100 text-amber-800';
    case 'pending': return 'bg-rose-100 text-rose-800';
    default: return 'bg-slate-100 text-slate-700';
  }
}

// Gender stats percentages
const genderPctL = computed(() => {
  const total = (genderStats.value.L || 0) + (genderStats.value.P || 0);
  if (!total) return 50;
  return Math.round((genderStats.value.L / total) * 100);
});

const genderPctP = computed(() => {
  const total = (genderStats.value.L || 0) + (genderStats.value.P || 0);
  if (!total) return 50;
  return 100 - genderPctL.value;
});

// Monthly trends computation for 12 months of the year
const monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const monthlyTrends = computed(() => {
  const months = monthNames.map((name, i) => ({
    monthIdx: i,
    monthName: name,
    shortMonth: name.substring(0, 3),
    incoming: 0,
    outgoing: 0
  }));

  allLetters.value.forEach(letter => {
    if (!letter.letter_date) return;
    const d = new Date(letter.letter_date);
    if (isNaN(d.getTime())) return;
    const m = d.getMonth();
    if (m >= 0 && m < 12) {
      if (letter.type === 'incoming') months[m].incoming++;
      else if (letter.type === 'outgoing') months[m].outgoing++;
    }
  });

  return months;
});

const maxMonthlyVal = computed(() => {
  let max = 5;
  monthlyTrends.value.forEach(m => {
    if (m.incoming > max) max = m.incoming;
    if (m.outgoing > max) max = m.outgoing;
  });
  // Round up to nice multiple of 5
  return Math.ceil(max / 5) * 5;
});

// Category Distribution Computation
const totalLettersCount = computed(() => {
  return (stats.value.total_incoming || 0) + (stats.value.total_outgoing || 0);
});

const categoryList = computed(() => {
  const counts = {
    dinas: 0,
    keterangan_siswa: 0,
    undangan: 0,
    lainnya: 0
  };

  allLetters.value.forEach(l => {
    const cat = (l.category || '').toLowerCase();
    if (cat === 'dinas' || cat === 'kedinasan') counts.dinas++;
    else if (cat.includes('keterangan') || cat.includes('siswa')) counts.keterangan_siswa++;
    else if (cat.includes('undangan')) counts.undangan++;
    else counts.lainnya++;
  });

  const total = allLetters.value.length || 1;
  return [
    { label: 'Dinas / Resmi', count: counts.dinas, color: '#10b981', pct: Math.round((counts.dinas / total) * 100) },
    { label: 'Ket. Siswa', count: counts.keterangan_siswa, color: '#6366f1', pct: Math.round((counts.keterangan_siswa / total) * 100) },
    { label: 'Undangan', count: counts.undangan, color: '#f59e0b', pct: Math.round((counts.undangan / total) * 100) },
    { label: 'Lainnya', count: counts.lainnya, color: '#94a3b8', pct: Math.round((counts.lainnya / total) * 100) },
  ];
});

const categorySegments = computed(() => {
  let cumulativeOffset = 0;
  return categoryList.value.map(cat => {
    const dash = cat.pct;
    const offset = -cumulativeOffset;
    cumulativeOffset += dash;
    return {
      color: cat.color,
      dash,
      offset
    };
  });
});

async function loadData() {
  loadingLetters.value = true;
  try {
    const [lettersRes, dashRes, profileRes] = await Promise.all([
      api.get('admin/letters', { params: { per_page: 50, direction: 'desc' } }).catch(() => null),
      api.get('admin/dashboard').catch(() => null),
      api.get('admin/profile').catch(() => null)
    ]);

    const d = dashRes?.data?.data || dashRes?.data || dashRes || {};
    const l = lettersRes?.data?.stats || lettersRes?.stats || lettersRes?.data || {};
    const letterItems = lettersRes?.data?.data?.data || lettersRes?.data?.data || lettersRes?.data || [];
    const p = profileRes?.data?.teacher || profileRes?.data?.user || profileRes?.teacher || profileRes?.user || {};

    if (p.photo_url || p.photo) {
      livePhoto.value = p.photo_url || p.photo;
    } else {
      livePhoto.value = null;
    }

    if (Array.isArray(letterItems)) {
      allLetters.value = letterItems;
      recentLetters.value = letterItems.slice(0, 5);
    }

    if (d.student_gender_stats) {
      genderStats.value = {
        L: d.student_gender_stats.L || 0,
        P: d.student_gender_stats.P || 0
      };
    }

    stats.value = {
      ...l,
      total_students: d.students || 0,
      total_teachers: d.teachers || 0,
      total_classes: d.classes || 0,
    };
  } catch (err) {
    console.error('Failed loading operator dashboard data', err);
  } finally {
    loadingLetters.value = false;
  }
}

onMounted(() => {
  loadData();
});
</script>
