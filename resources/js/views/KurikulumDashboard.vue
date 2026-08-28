<template>
  <div class="space-y-5 sm:space-y-6 font-inter pb-16 max-w-7xl mx-auto px-1 sm:px-2">
    
    <!-- 1. RESPONSIVE HERO BANNER (Waka Kurikulum) -->
    <div class="relative bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white rounded-2xl sm:rounded-3xl shadow-xl shadow-emerald-700/15 overflow-hidden border border-emerald-500/40">
      <!-- Subtle Background Mesh Grid & Glow -->
      <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.18)_1px,transparent_1px)] [background-size:22px_22px] opacity-60 pointer-events-none"></div>
      <div class="absolute -top-16 -right-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Banner Content -->
      <div class="relative z-10 p-4 sm:p-7 md:p-8 flex flex-col lg:flex-row lg:items-center justify-between gap-5 sm:gap-6">
        <div class="flex items-start sm:items-center gap-3.5 sm:gap-5">
          <!-- Photo Frame -->
          <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-white/15 backdrop-blur-md rounded-2xl border-2 border-white/30 p-1 flex items-center justify-center flex-shrink-0 overflow-hidden relative shadow-lg">
            <img
              v-if="userPhoto"
              :src="getImageUrl(userPhoto)"
              class="w-full h-full object-cover rounded-xl shadow-inner"
              alt="Foto Profil"
            />
            <div v-else class="w-full h-full rounded-xl bg-emerald-800 flex items-center justify-center text-white font-black text-2xl sm:text-3xl uppercase">
              {{ (auth.user?.name || 'K').charAt(0) }}
            </div>
            <!-- Online status indicator -->
            <span class="absolute bottom-1 right-1 w-3 h-3 sm:w-3.5 sm:h-3.5 bg-emerald-300 border-2 border-emerald-800 rounded-full shadow-xs"></span>
          </div>

          <!-- Profile Details -->
          <div class="space-y-1 sm:space-y-1.5 min-w-0">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 sm:px-3 sm:py-1 bg-white/20 backdrop-blur-md text-white rounded-full text-[10px] sm:text-[11px] font-bold border border-white/30 shadow-xs">
                <GraduationCap class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-200" />
                <span class="truncate max-w-[200px] sm:max-w-none">Waka Kurikulum &bull; {{ auth.user?.teacher?.nip ? `NIP: ${auth.user.teacher.nip}` : `@${auth.user?.username}` }}</span>
              </span>
              <span class="inline-flex items-center gap-1 px-2.5 py-0.5 sm:px-3 sm:py-1 bg-amber-300/20 backdrop-blur-md text-amber-100 rounded-full text-[10px] sm:text-[11px] font-bold border border-amber-300/40">
                <Clock class="w-3 h-3 text-amber-300" />
                <span>{{ currentTimeFormatted }} WIB</span>
              </span>
            </div>

            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight text-white uppercase leading-tight font-lexend truncate">
              {{ auth.user?.name || 'Waka Kurikulum' }}
            </h1>
            <p class="text-emerald-100 text-xs sm:text-sm font-normal max-w-xl leading-relaxed hidden sm:block">
              Monitoring distribusi jadwal pelajaran harian, beban mengajar guru, kalender akademik, dan rekapitulasi nilai siswa secara akurat.
            </p>
          </div>
        </div>

        <!-- Quick Action Shortcuts (Responsive Grid in Mobile, Row in Desktop) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:flex lg:flex-col xl:flex-row gap-2 sm:gap-2.5 flex-shrink-0 pt-2 lg:pt-0 border-t border-white/15 lg:border-t-0">
          <RouterLink
            to="/admin/schedules"
            class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md transition-all active:scale-95 cursor-pointer"
          >
            <Calendar class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            <span>Master Jadwal</span>
          </RouterLink>

          <RouterLink
            to="/admin/grades"
            class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md transition-all active:scale-95 shadow-xs cursor-pointer"
          >
            <Award class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            <span>Rekap Nilai</span>
          </RouterLink>

          <RouterLink
            to="/admin/profile"
            class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold bg-teal-900/80 hover:bg-teal-900 text-white border border-teal-400/40 backdrop-blur-md transition-all active:scale-95 shadow-xs cursor-pointer"
          >
            <UserCircle class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            <span>Biodata Diri</span>
          </RouterLink>

          <RouterLink
            to="/admin/print-center"
            class="inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md transition-all active:scale-95 shadow-xs cursor-pointer"
          >
            <Printer class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
            <span>Pusat Cetak</span>
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- 2. QUICK BENTO METRIC CARDS (Responsive Grid) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-4">
      <RouterLink to="/admin/schedules" class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-blue-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Jadwal Aktif</span>
          <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
            <Calendar class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
          </div>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1 sm:mt-2 font-lexend">{{ stats.schedules_count || 0 }}</p>
        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Sesi KBM aktif</p>
      </RouterLink>

      <RouterLink to="/admin/subjects" class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-indigo-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Mata Pelajaran</span>
          <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
            <BookOpen class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
          </div>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1 sm:mt-2 font-lexend">{{ stats.subjects_count || 0 }}</p>
        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Mapel kurikulum</p>
      </RouterLink>

      <RouterLink to="/admin/teachers" class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-teal-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Guru Pengampu</span>
          <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
            <Users class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
          </div>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1 sm:mt-2 font-lexend">{{ stats.teachers_count || 0 }}</p>
        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Guru aktif</p>
      </RouterLink>

      <RouterLink to="/admin/classes" class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-amber-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Rombel Kelas</span>
          <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
            <Building2 class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
          </div>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1 sm:mt-2 font-lexend">{{ stats.classes_count || 0 }}</p>
        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Kelas terdaftar</p>
      </RouterLink>

      <RouterLink to="/kurikulum/letters" class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all group col-span-2 sm:col-span-1">
        <div class="flex items-center justify-between">
          <span class="text-[11px] sm:text-xs font-semibold text-slate-500 truncate">Agenda Surat</span>
          <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform flex-shrink-0">
            <FileText class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
          </div>
        </div>
        <p class="text-xl sm:text-2xl font-black text-slate-900 mt-1 sm:mt-2 font-lexend">{{ stats.total_letters || 0 }}</p>
        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">Surat Masuk & Keluar</p>
      </RouterLink>
    </div>

    <!-- 3. LIVE KBM SESSION MONITOR (Sedang Berlangsung Sekarang) -->
    <div v-if="selectedDay === currentTodayDay && currentLiveSlot" class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-lg border border-slate-700/50 space-y-3.5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-2 border-b border-slate-700/60 pb-3">
        <div class="flex items-center gap-2 sm:gap-2.5">
          <span class="relative flex h-2.5 w-2.5 sm:h-3 sm:w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 sm:h-3 sm:w-3 bg-emerald-500"></span>
          </span>
          <h3 class="text-xs sm:text-sm md:text-base font-black tracking-wide uppercase font-lexend flex items-center gap-1.5 sm:gap-2">
            <span>KBM SEDANG BERLANGSUNG</span>
            <span class="px-2 py-0.5 rounded-lg text-[10px] sm:text-xs font-mono font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
              {{ currentLiveSlot.timeSlot }} WIB
            </span>
          </h3>
        </div>
        <p class="text-[11px] sm:text-xs text-slate-400 font-medium">Status Real-Time Jam KBM Saat Ini</p>
      </div>

      <!-- Live Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
        <div
          v-for="item in currentLiveSlot.items"
          :key="'live-'+item.id"
          :class="[
            item.is_activity
              ? 'bg-amber-900/30 border-amber-500/40 text-amber-200'
              : 'bg-slate-800/80 border-slate-700/80 text-white',
            'p-3 sm:p-3.5 rounded-2xl border flex items-center justify-between gap-3 shadow-inner'
          ]"
        >
          <div class="space-y-1 min-w-0 flex-1">
            <div class="flex items-center gap-1.5 sm:gap-2">
              <span class="px-2 py-0.5 rounded-md text-[9px] sm:text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex-shrink-0">
                {{ item.class_room?.name || item.classRoom?.name || 'Semua Kelas' }}
              </span>
              <span v-if="item.is_activity" class="text-xs font-bold text-amber-300 truncate">
                ⭐ {{ item.activity_name }}
              </span>
              <span v-else class="text-xs font-bold truncate">
                {{ item.subject?.name || 'Pelajaran' }}
              </span>
            </div>
            <p v-if="!item.is_activity && item.teacher" class="text-[11px] sm:text-xs text-slate-300 truncate flex items-center gap-1.5">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
              <span class="truncate">Guru: <strong>{{ item.teacher?.full_name }}</strong></span>
            </p>
          </div>

          <a
            v-if="!item.is_activity && item.teacher?.phone"
            :href="`https://wa.me/${formatWaNumber(item.teacher.phone)}?text=Assalamu'alaikum%20Bapak/Ibu%20${encodeURIComponent(item.teacher.full_name)},%20mohon%20konfirmasi%20KBM%20mapel%20${encodeURIComponent(item.subject?.name || '')}%20di%20kelas%20${encodeURIComponent(item.class_room?.name || item.classRoom?.name || '')}`"
            target="_blank"
            class="min-w-[40px] min-h-[40px] p-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl transition-all shadow-md flex items-center justify-center flex-shrink-0 cursor-pointer active:scale-95"
            title="Hubungi Guru via WhatsApp"
          >
            <Phone class="w-4 h-4" />
          </a>
        </div>
      </div>
    </div>

    <!-- 4. MASTER DAILY SCHEDULE TIMETABLE (Jadwal KBM Harian) -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden space-y-4 sm:space-y-6 p-4 sm:p-6 md:p-7">
      
      <!-- Header with Title, Mode Switcher & Actions -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 sm:gap-4 border-b border-slate-100 pb-4 sm:pb-5">
        <div class="space-y-1">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold border border-emerald-100 shadow-2xs flex-shrink-0">
              <CalendarDays class="w-4 h-4 sm:w-5 sm:h-5" />
            </div>
            <div>
              <h2 class="text-base sm:text-lg md:text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2 font-lexend">
                <span>Jadwal KBM & Mengajar Harian</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                  {{ selectedDayLabel }}
                </span>
              </h2>
              <p class="text-[11px] sm:text-xs text-slate-500 font-normal">
                Alur waktu jam mengajar guru, istirahat, dan kegiatan resmi madrasah.
              </p>
            </div>
          </div>
        </div>

        <!-- View Switcher & Master Jadwal Link -->
        <div class="flex items-center gap-2 flex-wrap">
          <!-- View Switcher: Timeline vs Matriks Rombel (Desktop & Tablet) -->
          <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/70">
            <button
              @click="viewMode = 'timeline'"
              :class="[
                viewMode === 'timeline'
                  ? 'bg-white text-emerald-800 shadow-xs font-bold'
                  : 'text-slate-600 hover:text-slate-900 font-medium',
                'px-2.5 sm:px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer'
              ]"
              title="Tampilan Garis Waktu Jam Urut"
            >
              <Clock class="w-3.5 h-3.5" />
              <span>Timeline Jam</span>
            </button>
            <button
              @click="viewMode = 'matrix'"
              :class="[
                viewMode === 'matrix'
                  ? 'bg-white text-emerald-800 shadow-xs font-bold'
                  : 'text-slate-600 hover:text-slate-900 font-medium',
                'px-2.5 sm:px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer'
              ]"
              title="Tampilan Kolom Per Kelas"
            >
              <Columns3 class="w-3.5 h-3.5" />
              <span>Kolom Kelas</span>
            </button>
          </div>

          <RouterLink
            to="/admin/schedules"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors shadow-2xs cursor-pointer"
          >
            <Calendar class="w-3.5 h-3.5 text-slate-500" />
            <span class="hidden sm:inline">Kelola Master</span>
            <span class="sm:hidden">Master</span>
            <span>&rarr;</span>
          </RouterLink>
        </div>
      </div>

      <!-- Touch-Friendly Day Selector Pills (Smooth Horizontal Swipe) -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-none snap-x touch-pan-x">
        <button
          v-for="day in availableDays"
          :key="day.id"
          @click="selectedDay = day.id"
          :class="[
            selectedDay === day.id
              ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/20 ring-2 ring-emerald-600/30'
              : 'bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold border border-slate-200/80',
            'px-3.5 sm:px-4 py-2 rounded-xl text-xs whitespace-nowrap cursor-pointer transition-all flex items-center gap-1.5 sm:gap-2 flex-shrink-0 snap-start active:scale-95'
          ]"
        >
          <span v-if="day.id === currentTodayDay" class="w-2 h-2 rounded-full" :class="selectedDay === day.id ? 'bg-amber-300 animate-pulse' : 'bg-emerald-500'"></span>
          <span>{{ day.name }}</span>
          <span v-if="day.id === currentTodayDay" class="text-[9px] sm:text-[10px] font-bold px-1.5 py-0.2 rounded-md" :class="selectedDay === day.id ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'">
            Hari Ini
          </span>
        </button>
      </div>

      <!-- Filter Bar & Daily Metrics Overview (Responsive Stack) -->
      <div class="bg-slate-50/80 p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
        <!-- Search & Class Dropdown -->
        <div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-2.5 w-full lg:w-auto">
          <div class="relative w-full sm:w-64">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="scheduleSearch"
              type="text"
              placeholder="Cari guru, mapel, ruang..."
              class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>

          <div class="relative w-full sm:w-48">
            <select
              v-model="selectedClassFilter"
              class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
              <option value="">Semua Rombel Kelas</option>
              <option v-for="cls in classList" :key="cls.id" :value="cls.id">
                {{ cls.name }}
              </option>
            </select>
          </div>

          <button
            @click="loadSchedules"
            class="p-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer self-end sm:self-auto"
            title="Segarkan Jadwal"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loadingSchedules }" />
          </button>
        </div>

        <!-- Quick Metrics Pills -->
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap text-[11px] sm:text-xs text-slate-600 font-medium">
          <div class="inline-flex items-center gap-1.5 bg-white px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
            <Users class="w-3.5 h-3.5 text-emerald-600" />
            <span>Guru: <strong class="text-slate-900 font-bold">{{ dailyMetrics.teachersCount }}</strong></span>
          </div>
          <div class="inline-flex items-center gap-1.5 bg-white px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
            <BookOpen class="w-3.5 h-3.5 text-blue-600" />
            <span>Total: <strong class="text-slate-900 font-bold">{{ dailyMetrics.sessionsCount }}</strong> Sesi</span>
          </div>
          <div class="inline-flex items-center gap-1.5 bg-white px-2.5 sm:px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
            <Building2 class="w-3.5 h-3.5 text-amber-600" />
            <span>Rombel: <strong class="text-slate-900 font-bold">{{ dailyMetrics.classesCount }}</strong></span>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loadingSchedules" class="py-14 text-center text-slate-400 space-y-2">
        <RefreshCw class="w-7 h-7 animate-spin mx-auto text-emerald-600" />
        <p class="text-xs font-semibold">Memuat jadwal KBM hari {{ selectedDayLabel }}...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredGroupedSchedules.length === 0" class="py-12 text-center text-slate-400 space-y-3 bg-slate-50/50 rounded-2xl sm:rounded-3xl border border-dashed border-slate-200">
        <Calendar class="w-9 h-9 sm:w-10 sm:h-10 mx-auto text-slate-300" />
        <div class="space-y-1">
          <p class="text-sm font-bold text-slate-700">Belum Ada Jadwal KBM pada Hari {{ selectedDayLabel }}</p>
          <p class="text-xs text-slate-400">Tidak ada sesi pelajaran yang cocok dengan filter yang dipilih.</p>
        </div>
        <RouterLink
          to="/admin/schedules"
          class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all cursor-pointer"
        >
          <span>+ Tambah Jadwal Pelajaran</span>
        </RouterLink>
      </div>

      <!-- VIEW MODE A: TIMELINE CHRONOLOGICAL VIEW (Highly Optimized for Mobile & Desktop) -->
      <div v-else-if="viewMode === 'timeline'" class="space-y-4 sm:space-y-6">
        <div
          v-for="group in filteredGroupedSchedules"
          :key="group.timeSlot"
          class="space-y-2.5 sm:space-y-3"
        >
          <!-- SPECIAL BREAK / PRAYER RIBBON (Istirahat / Sholat / Upacara) -->
          <div
            v-if="isFullBreakOrActivitySlot(group)"
            :class="[
              getSlotBannerStyle(group).wrapper,
              'p-3 sm:p-4 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 shadow-xs'
            ]"
          >
            <div class="flex items-center gap-2.5 sm:gap-3">
              <div :class="[getSlotBannerStyle(group).badge, 'w-8 h-8 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center font-bold flex-shrink-0 shadow-xs']">
                <component :is="getSlotBannerStyle(group).iconComponent" class="w-4 h-4 sm:w-5 sm:h-5" />
              </div>
              <div class="min-w-0">
                <h4 :class="[getSlotBannerStyle(group).titleColor, 'text-xs sm:text-sm font-black font-lexend tracking-wide uppercase truncate']">
                  {{ group.items[0]?.activity_name || 'ISTIRAHAT' }}
                </h4>
                <p :class="[getSlotBannerStyle(group).subColor, 'text-[11px] sm:text-xs font-medium truncate']">
                  Berlaku untuk seluruh siswa & dewan guru madrasah
                </p>
              </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
              <span :class="[getSlotBannerStyle(group).timeBadge, 'px-2.5 sm:px-3 py-1 rounded-xl text-[11px] sm:text-xs font-mono font-bold tracking-tight shadow-2xs']">
                ⏱️ {{ group.timeSlot }} WIB
              </span>
              <span class="text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-lg bg-white/70 text-slate-700 border border-slate-200/60">
                Semua Kelas
              </span>
            </div>
          </div>

          <!-- REGULAR TIMELINE GROUP (Pelajaran KBM & Sesi Campuran) -->
          <template v-else>
            <!-- Time Slot Badge Header -->
            <div class="flex items-center gap-2.5 sm:gap-3 pt-1">
              <div
                :class="[
                  isSlotLive(group) ? 'bg-emerald-600 text-white ring-2 ring-emerald-400' : 'bg-slate-900 text-white',
                  'inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 rounded-xl text-[11px] sm:text-xs font-bold font-mono tracking-tight shadow-xs transition-colors'
                ]"
              >
                <Clock class="w-3 h-3 sm:w-3.5 sm:h-3.5" :class="isSlotLive(group) ? 'text-amber-300 animate-spin' : 'text-emerald-400'" />
                <span>{{ group.timeSlot }} WIB</span>
                <span v-if="isSlotLive(group)" class="text-[9px] sm:text-[10px] font-sans font-black bg-amber-400 text-slate-950 px-1 py-0.2 rounded-md">LIVE</span>
              </div>
              <div class="h-px bg-slate-200 flex-1"></div>
              <span class="text-[10px] sm:text-[11px] font-semibold text-slate-400">
                {{ group.items.length }} Rombel
              </span>
            </div>

            <!-- Schedule Grid Cards for this Time Slot (1 Col on Mobile, 2 on MD, 3 on LG) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3.5">
              <div
                v-for="sch in group.items"
                :key="sch.id"
                :class="[
                  sch.is_activity 
                    ? 'bg-amber-50/70 border-amber-200/80 shadow-xs' 
                    : 'bg-white border-slate-200/90 shadow-2xs hover:shadow-md hover:border-emerald-300',
                  'p-3.5 sm:p-4 rounded-2xl border transition-all space-y-2.5 sm:space-y-3 relative overflow-hidden group'
                ]"
              >
                <!-- Top Header: Subject Badge & Class Badge -->
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0 flex-1">
                    <span
                      v-if="sch.is_activity"
                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[9px] sm:text-[10px] font-extrabold bg-amber-200 text-amber-900 uppercase"
                    >
                      ⭐ {{ sch.activity_name || 'Kegiatan' }}
                    </span>
                    <div v-else class="space-y-0.5">
                      <span class="inline-block px-2 py-0.5 rounded-lg text-[9px] sm:text-[10px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/70 uppercase tracking-wide">
                        {{ sch.subject?.code || 'MAPEL' }}
                      </span>
                      <h4 class="text-xs sm:text-sm font-bold text-slate-900 line-clamp-1 group-hover:text-emerald-700 transition-colors">
                        {{ sch.subject?.name || sch.activity_name || 'Pelajaran' }}
                      </h4>
                    </div>
                  </div>

                  <span class="inline-flex items-center gap-1 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-xl text-[11px] sm:text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-100 whitespace-nowrap shadow-2xs flex-shrink-0">
                    <Building2 class="w-3 h-3 text-indigo-500" />
                    <span>{{ sch.class_room?.name || sch.classRoom?.name || 'Kelas' }}</span>
                  </span>
                </div>

                <!-- Teacher Identity & Info -->
                <div v-if="!sch.is_activity && sch.teacher" class="flex items-center gap-2.5 sm:gap-3 pt-1 border-t border-slate-100">
                  <!-- Teacher Photo Avatar -->
                  <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center flex-shrink-0 relative shadow-inner">
                    <img
                      v-if="sch.teacher?.photo_url || sch.teacher?.photo"
                      :src="getImageUrl(sch.teacher.photo_url || sch.teacher.photo)"
                      class="w-full h-full object-cover"
                      alt="Foto Guru"
                    />
                    <div v-else class="w-full h-full bg-emerald-700 text-white font-bold text-xs flex items-center justify-center">
                      {{ sch.teacher?.full_name?.charAt(0) || 'G' }}
                    </div>
                  </div>

                  <!-- Teacher Name, NIP, Phone Action -->
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-900 truncate leading-snug">
                      {{ sch.teacher?.full_name || '-' }}
                    </p>
                    <p class="text-[10px] text-slate-500 font-mono truncate">
                      {{ sch.teacher?.nip ? `NIP: ${sch.teacher.nip}` : 'Tenaga Pendidik' }}
                    </p>
                  </div>

                  <!-- WhatsApp Quick Action Button (Touch-Friendly min 36px) -->
                  <a
                    v-if="sch.teacher?.phone"
                    :href="`https://wa.me/${formatWaNumber(sch.teacher.phone)}?text=Assalamu'alaikum%20Bapak/Ibu%20${encodeURIComponent(sch.teacher.full_name)},%20mohon%20konfirmasi%20KBM%20mapel%20${encodeURIComponent(sch.subject?.name || '')}%20di%20kelas%20${encodeURIComponent(sch.class_room?.name || sch.classRoom?.name || '')}`"
                    target="_blank"
                    class="min-w-[36px] min-h-[36px] p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl transition-colors cursor-pointer shadow-2xs flex items-center justify-center flex-shrink-0 active:scale-95"
                    title="Hubungi Guru via WhatsApp"
                  >
                    <Phone class="w-3.5 h-3.5" />
                  </a>
                </div>

                <!-- Footer: Room & Time Info -->
                <div class="flex items-center justify-between text-[10px] sm:text-[11px] text-slate-500 pt-0.5">
                  <span class="flex items-center gap-1 text-slate-600 truncate">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                    <span>Ruang: <strong>{{ sch.room || 'Ruang Kelas' }}</strong></span>
                  </span>
                  <span class="font-mono text-slate-400 flex-shrink-0">
                    {{ sch.start_time?.substring(0, 5) }} - {{ sch.end_time?.substring(0, 5) }}
                  </span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- VIEW MODE B: SIDE-BY-SIDE MATRIX COLUMNS (Per Rombel Kelas) -->
      <div v-else-if="viewMode === 'matrix'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 items-start">
        <div
          v-for="cls in filteredClassList"
          :key="'matrix-cls-'+cls.id"
          class="bg-slate-50/70 rounded-2xl sm:rounded-3xl border border-slate-200/80 overflow-hidden shadow-xs space-y-2.5 sm:space-y-3 p-3.5 sm:p-4"
        >
          <!-- Class Column Header -->
          <div class="flex items-center justify-between pb-2.5 sm:pb-3 border-b border-slate-200">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-emerald-600 text-white font-black text-xs flex items-center justify-center shadow-xs flex-shrink-0">
                {{ cls.name.charAt(0) }}
              </div>
              <h3 class="text-xs sm:text-sm font-black text-slate-900 font-lexend truncate">Kelas {{ cls.name }}</h3>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 bg-white border border-slate-200 rounded-md text-slate-500 flex-shrink-0">
              {{ getClassSchedules(cls.id).length }} Sesi
            </span>
          </div>

          <!-- Class Schedule Cards -->
          <div class="space-y-2">
            <div
              v-for="sch in getClassSchedules(cls.id)"
              :key="'m-sch-'+sch.id"
              :class="[
                sch.is_activity
                  ? 'bg-amber-50 border-amber-200'
                  : 'bg-white border-slate-200/80 shadow-2xs hover:border-emerald-300',
                'p-2.5 sm:p-3 rounded-2xl border transition-all space-y-1.5'
              ]"
            >
              <div class="flex items-center justify-between text-[10px] sm:text-[11px]">
                <span class="font-mono font-bold text-slate-600 bg-slate-100 px-1.5 sm:px-2 py-0.5 rounded-md">
                  {{ sch.start_time?.substring(0, 5) }} - {{ sch.end_time?.substring(0, 5) }}
                </span>
                <span v-if="sch.is_activity" class="text-[9px] sm:text-[10px] font-black text-amber-800 bg-amber-200/80 px-1.5 py-0.5 rounded-md">
                  KEGIATAN
                </span>
                <span v-else class="text-[9px] sm:text-[10px] font-black text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded-md">
                  {{ sch.subject?.code || 'MAPEL' }}
                </span>
              </div>

              <div>
                <p class="text-xs font-bold text-slate-900 line-clamp-1">
                  {{ sch.subject?.name || sch.activity_name }}
                </p>
                <p v-if="!sch.is_activity && sch.teacher" class="text-[10px] sm:text-[11px] text-slate-500 truncate mt-0.5">
                  👤 {{ sch.teacher?.full_name }}
                </p>
              </div>
            </div>

            <div v-if="getClassSchedules(cls.id).length === 0" class="py-6 text-center text-slate-400 text-xs font-medium">
              Belum ada jadwal untuk kelas ini
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. LIVE ACADEMIC MANAGEMENT SECTIONS (Bento Bawah) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
      <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 font-lexend">Distribusi Jadwal Pelajaran</h3>
            <p class="text-[11px] sm:text-xs text-slate-500">Pengaturan matriks jadwal KBM harian per kelas.</p>
          </div>
          <RouterLink to="/admin/schedules" class="text-xs font-bold text-emerald-600 hover:underline">
            Kelola Jadwal &rarr;
          </RouterLink>
        </div>
        <div class="p-3.5 sm:p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2 text-slate-600 leading-relaxed">
          <p>Fitur untuk Waka Kurikulum mengatur pembagian jam mengajar, hari, mata pelajaran, dan guru pengampu secara presisi serta otomatis mencegah jadwal bentrok antar ruang/guru.</p>
        </div>
      </div>

      <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-xs space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 font-lexend">Kalender Akademik Madrasah</h3>
            <p class="text-[11px] sm:text-xs text-slate-500">Agenda kegiatan akademik semester berjalan.</p>
          </div>
          <RouterLink to="/admin/calendar-events" class="text-xs font-bold text-indigo-600 hover:underline">
            Lihat Kalender &rarr;
          </RouterLink>
        </div>
        <div class="p-3.5 sm:p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2 text-slate-600 leading-relaxed">
          <p>Pantau jadwal Asesmen Sumatif, Penilaian Tengah Semester (PTS), Penilaian Akhir Semester (PAS), agenda ujian madrasah, serta kalender libur semester.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  GraduationCap,
  Calendar,
  Award,
  BookOpen,
  Users,
  Building2,
  FileText,
  CalendarDays,
  Clock,
  Search,
  RefreshCw,
  Phone,
  Printer,
  UserCircle,
  Columns3,
  Coffee,
  Flag,
  Sparkles,
  MoonStar
} from 'lucide-vue-next';
import { api } from '../api';

const auth = useAuthStore();
const stats = ref({});
const livePhoto = ref(null);
const viewMode = ref('timeline'); // 'timeline' or 'matrix'

// Schedules state
const schedules = ref([]);
const classList = ref([]);
const loadingSchedules = ref(false);
const scheduleSearch = ref('');
const selectedClassFilter = ref('');

// Clock state
const now = ref(new Date());
let timer = null;

const currentTimeFormatted = computed(() => {
  const h = String(now.value.getHours()).padStart(2, '0');
  const m = String(now.value.getMinutes()).padStart(2, '0');
  const s = String(now.value.getSeconds()).padStart(2, '0');
  return `${h}:${m}:${s}`;
});

const currentHourMin = computed(() => {
  const h = String(now.value.getHours()).padStart(2, '0');
  const m = String(now.value.getMinutes()).padStart(2, '0');
  return `${h}:${m}`;
});

const availableDays = [
  { id: 'senin', name: 'Senin' },
  { id: 'selasa', name: 'Selasa' },
  { id: 'rabu', name: 'Rabu' },
  { id: 'kamis', name: 'Kamis' },
  { id: 'jumat', name: 'Jumat' },
  { id: 'sabtu', name: 'Sabtu' },
];

function getTodayDayKey() {
  const dayIndex = new Date().getDay();
  const map = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
  const todayKey = map[dayIndex] || 'senin';
  return todayKey === 'minggu' ? 'senin' : todayKey;
}

const currentTodayDay = ref(getTodayDayKey());
const selectedDay = ref(getTodayDayKey());

const selectedDayLabel = computed(() => {
  const found = availableDays.find(d => d.id === selectedDay.value);
  return found ? found.name : 'Senin';
});

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

function formatWaNumber(phone) {
  if (!phone) return '';
  let clean = phone.replace(/\D/g, '');
  if (clean.startsWith('0')) {
    clean = '62' + clean.slice(1);
  }
  return clean;
}

// Group schedules by time slot and apply filters
const filteredGroupedSchedules = computed(() => {
  const query = scheduleSearch.value.trim().toLowerCase();
  const classFilter = selectedClassFilter.value;

  // Flatten general activities into per-class items if classList exists and no class_id
  const expandedSchedules = [];
  schedules.value.forEach(s => {
    if (s.is_activity && !s.class_id && classList.value && classList.value.length > 0) {
      classList.value.forEach(cls => {
        expandedSchedules.push({
          ...s,
          id: `${s.id}-cls-${cls.id}`,
          class_id: cls.id,
          class_room: cls,
          classRoom: cls,
        });
      });
    } else {
      expandedSchedules.push(s);
    }
  });

  const filtered = expandedSchedules.filter(s => {
    if (classFilter && (s.class_id != classFilter && s.class_room?.id != classFilter && s.classRoom?.id != classFilter)) {
      return false;
    }

    if (query) {
      const matchSubject = s.subject?.name?.toLowerCase().includes(query) || s.subject?.code?.toLowerCase().includes(query);
      const matchTeacher = s.teacher?.full_name?.toLowerCase().includes(query) || s.teacher?.nip?.toLowerCase().includes(query);
      const matchClass = (s.class_room?.name || s.classRoom?.name || '').toLowerCase().includes(query);
      const matchRoom = (s.room || '').toLowerCase().includes(query);
      const matchActivity = (s.activity_name || '').toLowerCase().includes(query);
      return matchSubject || matchTeacher || matchClass || matchRoom || matchActivity;
    }

    return true;
  });

  // Group by Start - End Time
  const groupsMap = {};
  filtered.forEach(item => {
    const rawStart = (item.start_time || '00:00').substring(0, 5);
    const rawEnd = (item.end_time || '00:00').substring(0, 5);
    const slot = `${rawStart} - ${rawEnd}`;
    if (!groupsMap[slot]) {
      groupsMap[slot] = {
        timeSlot: slot,
        startTime: rawStart,
        endTime: rawEnd,
        items: []
      };
    }
    groupsMap[slot].items.push(item);
  });

  // Sort groups chronologically by start time (07:00 -> 08:30 -> 10:10 -> 12:00)
  return Object.values(groupsMap).sort((a, b) => a.startTime.localeCompare(b.startTime));
});

// Live Slot detection
const currentLiveSlot = computed(() => {
  const time = currentHourMin.value;
  return filteredGroupedSchedules.value.find(g => g.startTime <= time && time < g.endTime);
});

function isSlotLive(group) {
  if (selectedDay.value !== currentTodayDay.value) return false;
  const time = currentHourMin.value;
  return group.startTime <= time && time < group.endTime;
}

function isFullBreakOrActivitySlot(group) {
  if (!group.items || group.items.length === 0) return false;
  return group.items.every(i => i.is_activity);
}

function getSlotBannerStyle(group) {
  const name = (group.items[0]?.activity_name || '').toLowerCase();
  if (name.includes('istirahat')) {
    return {
      iconComponent: Coffee,
      wrapper: 'bg-gradient-to-r from-amber-50 via-amber-100/70 to-yellow-50 border-amber-300/80',
      badge: 'bg-amber-400 text-amber-950',
      titleColor: 'text-amber-900',
      subColor: 'text-amber-700',
      timeBadge: 'bg-amber-900 text-amber-100',
    };
  }
  if (name.includes('upacara')) {
    return {
      iconComponent: Flag,
      wrapper: 'bg-gradient-to-r from-rose-50 via-red-100/60 to-rose-50 border-rose-300/80',
      badge: 'bg-rose-500 text-white',
      titleColor: 'text-rose-900',
      subColor: 'text-rose-700',
      timeBadge: 'bg-rose-900 text-rose-100',
    };
  }
  if (name.includes('sholat') || name.includes('shalat') || name.includes('tadarus') || name.includes('dhuha') || name.includes('yasin')) {
    return {
      iconComponent: MoonStar,
      wrapper: 'bg-gradient-to-r from-emerald-50 via-teal-100/60 to-emerald-50 border-emerald-300/80',
      badge: 'bg-emerald-600 text-white',
      titleColor: 'text-emerald-950',
      subColor: 'text-emerald-700',
      timeBadge: 'bg-emerald-900 text-emerald-100',
    };
  }
  return {
    iconComponent: Sparkles,
    wrapper: 'bg-slate-50 border-slate-200',
    badge: 'bg-slate-700 text-white',
    titleColor: 'text-slate-900',
    subColor: 'text-slate-500',
    timeBadge: 'bg-slate-800 text-white',
  };
}

// Matrix View Helper
const filteredClassList = computed(() => {
  if (selectedClassFilter.value) {
    return classList.value.filter(c => c.id == selectedClassFilter.value);
  }
  return classList.value;
});

function getClassSchedules(classId) {
  const query = scheduleSearch.value.trim().toLowerCase();
  return schedules.value
    .filter(s => {
      if (s.class_id && s.class_id != classId) return false;
      if (query) {
        const matchSubject = s.subject?.name?.toLowerCase().includes(query) || s.subject?.code?.toLowerCase().includes(query);
        const matchTeacher = s.teacher?.full_name?.toLowerCase().includes(query) || s.teacher?.nip?.toLowerCase().includes(query);
        const matchActivity = (s.activity_name || '').toLowerCase().includes(query);
        return matchSubject || matchTeacher || matchActivity;
      }
      return true;
    })
    .sort((a, b) => (a.start_time || '').localeCompare(b.start_time || ''));
}

// Daily Quick Metrics
const dailyMetrics = computed(() => {
  const uniqueTeachers = new Set();
  const uniqueClasses = new Set();
  let sessionsCount = 0;

  schedules.value.forEach(s => {
    if (s.teacher_id || s.teacher) {
      uniqueTeachers.add(s.teacher_id || s.teacher?.id);
    }
    if (s.class_id || s.class_room?.id || s.classRoom?.id) {
      uniqueClasses.add(s.class_id || s.class_room?.id || s.classRoom?.id);
    }
    sessionsCount++;
  });

  return {
    teachersCount: uniqueTeachers.size,
    classesCount: uniqueClasses.size || classList.value.length || 0,
    sessionsCount
  };
});

async function loadSchedules() {
  loadingSchedules.value = true;
  try {
    const res = await api.get('admin/schedules', { day: selectedDay.value });
    schedules.value = res?.data || res || [];
  } catch (err) {
    console.error('Failed to load schedules for day', selectedDay.value, err);
  } finally {
    loadingSchedules.value = false;
  }
}

watch(selectedDay, () => {
  loadSchedules();
});

async function loadKurikulumStats() {
  try {
    const [dashRes, letterRes, profileRes, classesRes] = await Promise.all([
      api.get('admin/dashboard').catch(() => null),
      api.get('admin/letters').catch(() => null),
      api.get('admin/profile').catch(() => null),
      api.get('admin/classes').catch(() => null)
    ]);
    const d = dashRes?.data?.data || dashRes?.data || dashRes || {};
    const l = letterRes?.data?.stats || letterRes?.stats || letterRes?.data || {};
    const p = profileRes?.data?.teacher || profileRes?.data?.user || profileRes?.teacher || profileRes?.user || {};
    const cls = classesRes?.data?.data || classesRes?.data || classesRes || [];

    classList.value = Array.isArray(cls) ? cls : [];

    if (p.photo_url || p.photo) {
      livePhoto.value = p.photo_url || p.photo;
    } else {
      livePhoto.value = null;
    }
    stats.value = {
      schedules_count: d.schedules || 0,
      subjects_count: d.subjects || 0,
      teachers_count: d.teachers || 0,
      classes_count: d.classes || 0,
      students_count: d.students || 0,
      grades_count: d.grades || 0,
      total_letters: (l.total_incoming || 0) + (l.total_outgoing || 0),
    };
  } catch (err) {
    console.error('Failed to load kurikulum stats', err);
  }
}

onMounted(() => {
  loadKurikulumStats();
  loadSchedules();
  timer = setInterval(() => {
    now.value = new Date();
  }, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
