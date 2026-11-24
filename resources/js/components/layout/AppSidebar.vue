<template>
  <div class="no-print">
    <!-- Mobile Overlay -->
    <div 
      v-if="isMobileSidebarOpen"
      @click="$emit('close-mobile-sidebar')"
      class="fixed inset-0 bg-slate-900/40 z-30 sm:hidden transition-opacity no-print"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        isCollapsed ? 'w-[68px]' : 'w-[260px] max-w-[85vw] sm:w-[240px]',
        isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0',
        'bg-white border-r border-slate-200/80 text-slate-700 flex flex-col transition-all duration-150 z-40 flex-shrink-0 fixed sm:relative h-full shadow-xs no-print'
      ]"
    >
      <!-- Logo Area -->
      <div class="h-16 flex items-center justify-between border-b border-slate-100 flex-shrink-0 bg-white"
           :class="isCollapsed ? 'justify-center px-0' : 'px-4'">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center shadow-xs border border-emerald-500/30 flex-shrink-0 overflow-hidden p-1">
            <img 
              v-if="appSettings?.app_logo && !logoError" 
              :src="resolveImageUrl(appSettings.app_logo)" 
              @error="logoError = true"
              class="w-full h-full object-contain" 
              alt="Logo" 
            />
            <School v-else class="w-4 h-4 text-white" />
          </div>
          <Transition name="label-fade">
            <div v-if="!isCollapsed" class="flex flex-col overflow-hidden">
              <span class="font-bold text-sm text-slate-900 tracking-tight leading-none truncate max-w-[130px] uppercase font-sans">{{ appSettings?.app_name || 'PORTAL' }}</span>
              <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mt-1 whitespace-nowrap">
                {{ currentRole }} &bull; T.A. 2026/2027
              </span>
            </div>
          </Transition>
        </div>

        <!-- Mobile Close Button (X) -->
        <button
          v-if="!isCollapsed"
          @click="$emit('close-mobile-sidebar')"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors sm:hidden cursor-pointer flex-shrink-0"
          title="Tutup Menu"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- DUAL-ROLE QUICK SWITCH CARD IN SIDEBAR -->
      <div v-if="auth.isDualRole && !isCollapsed" class="px-3 py-2 border-b border-slate-100 bg-slate-50/70 space-y-1.5 flex-shrink-0">
        <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 tracking-wider">
          <span>PERAN AKTIF</span>
          <span
            class="px-1.5 py-0.5 rounded-md text-[9px] font-bold uppercase"
            :class="currentRole === 'teacher' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-200/80 text-slate-700 border border-slate-300/80'"
          >
            {{ currentRole === 'teacher' ? 'Dewan Guru' : (auth.primaryRole === 'kurikulum' ? 'Kurikulum' : (auth.primaryRole === 'kepala_sekolah' ? 'Kepala Madrasah' : 'Operator TU')) }}
          </span>
        </div>
        <button
          @click="handleSidebarSwitchRole(currentRole === 'teacher' ? (auth.primaryRole || 'operator') : 'teacher')"
          class="w-full flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-colors cursor-pointer border bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-2xs"
        >
          <ArrowRightLeft class="w-3.5 h-3.5 text-slate-400" />
          <span>Ganti ke Mode {{ currentRole === 'teacher' ? (auth.primaryRole === 'kurikulum' ? 'Kurikulum' : (auth.primaryRole === 'kepala_sekolah' ? 'Kepala Madrasah' : 'Operator TU')) : 'Guru' }}</span>
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 py-3 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200"
           :class="isCollapsed ? 'px-2 space-y-1' : 'px-3 space-y-1'">

        <!-- 1. MENU UTAMA -->
        <div v-if="!isCollapsed" class="nav-section">Utama</div>
        <div v-else class="my-1 border-t border-slate-100"></div>

        <!-- Dashboard -->
        <RouterLink
          :to="`/${currentRole === 'admin' ? 'admin' : (currentRole === 'kepala_sekolah' ? 'kepala-sekolah' : (currentRole === 'operator' ? 'operator' : (currentRole === 'kurikulum' ? 'kurikulum' : (currentRole === 'teacher' ? 'teacher' : 'student'))))}/dashboard`"
          :title="isCollapsed ? 'Dashboard' : ''"
          class="nav-link"
          :class="isCollapsed ? 'justify-center' : ''"
          active-class="nav-link-active"
        >
          <LayoutDashboard class="w-4 h-4 flex-shrink-0" />
          <Transition name="label-fade">
            <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Dashboard</span>
          </Transition>
        </RouterLink>

        <!-- 2. ADMINISTRASI & PERSURATAN (Admin, Kepala Madrasah, Operator TU, Waka Kurikulum, & Bendahara) -->
        <template v-if="currentRole === 'admin' || currentRole === 'operator' || currentRole === 'kurikulum' || currentRole === 'kepala_sekolah' || currentRole === 'bendahara'">
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('administrasi')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('administrasi') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Administrasi</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('administrasi') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('administrasi')" class="space-y-0.5">
            <!-- Keuangan & Pembayaran SPP (Admin, Operator, Bendahara, Kepala Madrasah) -->
            <RouterLink
              v-if="currentRole === 'admin' || currentRole === 'operator' || currentRole === 'bendahara' || currentRole === 'kepala_sekolah'"
              to="/admin/finance"
              :title="isCollapsed ? 'Keuangan & SPP' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Wallet class="w-4 h-4 flex-shrink-0 text-emerald-600" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Keuangan & SPP</span>
              </Transition>
            </RouterLink>

            <!-- Buku Agenda Surat Masuk & Keluar -->
            <RouterLink
              v-if="currentRole === 'admin' || currentRole === 'operator' || currentRole === 'kurikulum' || currentRole === 'kepala_sekolah'"
              :to="currentRole === 'admin' ? '/admin/letters' : (currentRole === 'kurikulum' ? '/kurikulum/letters' : '/operator/letters')"
              :title="isCollapsed ? 'Buku Agenda Persuratan' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <FileText class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Persuratan</span>
              </Transition>
            </RouterLink>

            <!-- Pusat Cetak Dokumen (Admin, Operator TU, & Waka Kurikulum) -->
            <RouterLink
              to="/admin/print-center"
              :title="isCollapsed ? 'Pusat Cetak Dokumen' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Printer class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Pusat Cetak</span>
              </Transition>
            </RouterLink>

            <!-- Rekap Nilai Ijazah (PDUM) Khusus Kelas 9 -->
            <RouterLink
              to="/admin/graduation-grades"
              :title="isCollapsed ? 'Rekap Nilai Ijazah (PDUM)' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <GraduationCap class="w-4 h-4 flex-shrink-0 text-emerald-600" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Nilai Ijazah</span>
              </Transition>
            </RouterLink>
          </div>
        </template>

        <!-- 3. KURIKULUM & AKADEMIK (Admin & Kurikulum) -->
        <template v-if="currentRole === 'kurikulum'">
          <!-- A. Kurikulum & KBM -->
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('kurikulum_kbm')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('kurikulum_kbm') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Kurikulum & KBM</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('kurikulum_kbm') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('kurikulum_kbm')" class="space-y-0.5">
            <RouterLink
              to="/admin/schedules"
              :title="isCollapsed ? 'Jadwal Pelajaran' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <CalendarCheck class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Jadwal Pelajaran</span>
              </Transition>
            </RouterLink>

            <RouterLink
              to="/admin/subjects"
              :title="isCollapsed ? 'Mata Pelajaran' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <BookOpen class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Mata Pelajaran</span>
              </Transition>
            </RouterLink>

            <RouterLink
              to="/admin/calendar-events"
              :title="isCollapsed ? 'Kalender Akademik' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Calendar class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Kalender Akademik</span>
              </Transition>
            </RouterLink>
          </div>

          <!-- B. Asesmen & Nilai -->
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('kurikulum_asesmen')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('kurikulum_asesmen') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Asesmen & Nilai</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('kurikulum_asesmen') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('kurikulum_asesmen')" class="space-y-0.5">
            <!-- Telaah & Review Soal Kurikulum -->
            <RouterLink
              to="/kurikulum/exam-reviews"
              :title="isCollapsed ? 'Telaah & Review Soal' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <ClipboardCheck class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Review Soal</span>
              </Transition>
            </RouterLink>

            <RouterLink
              to="/admin/exam-corrections"
              :title="isCollapsed ? 'Monitoring Koreksi Ujian' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <FileCheck2 class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Koreksi Ujian</span>
              </Transition>
            </RouterLink>

            <RouterLink
              to="/admin/grades"
              :title="isCollapsed ? 'Rekap Nilai Siswa' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Award class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Rekap Nilai</span>
              </Transition>
            </RouterLink>

            <!-- Monitoring Leger Wali Kelas -->
            <RouterLink
              to="/admin/ledger-monitoring"
              :title="isCollapsed ? 'Monitoring Leger Wali Kelas' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <TableProperties class="w-4 h-4 flex-shrink-0 text-emerald-600" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Leger Nilai</span>
              </Transition>
            </RouterLink>

            <!-- Monitoring ASTS & Standar KKTP -->
            <RouterLink
              to="/kurikulum/asts-monitoring"
              :title="isCollapsed ? 'Monitoring ASTS & Standar KKTP' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Sliders class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Standar KKTP</span>
              </Transition>
            </RouterLink>
          </div>

          <!-- C. Supervisi Kehadiran -->
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('kurikulum_supervisi')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('kurikulum_supervisi') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Supervisi Guru</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('kurikulum_supervisi') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('kurikulum_supervisi')" class="space-y-0.5">
            <RouterLink
              to="/admin/teacher-presensi-monitoring"
              :title="isCollapsed ? 'Monitoring Presensi Guru' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <MapPin class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Presensi Guru</span>
              </Transition>
            </RouterLink>
          </div>
        </template>

        <!-- 4. DATA MASTER (Admin, Kepala Madrasah, Operator, Kurikulum) -->
        <template v-if="currentRole === 'admin' || currentRole === 'operator' || currentRole === 'kurikulum' || currentRole === 'kepala_sekolah'">
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('data_master')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('data_master') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Data Master</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('data_master') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('data_master')" class="space-y-0.5">
            <!-- Data Siswa -->
            <RouterLink
              to="/admin/students"
              :title="isCollapsed ? 'Data Siswa' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <GraduationCap class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Data Siswa</span>
              </Transition>
            </RouterLink>

            <!-- Kenaikan Kelas & Kelulusan -->
            <RouterLink
              to="/admin/promotions"
              :title="isCollapsed ? 'Kenaikan Kelas & Kelulusan' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <ArrowRightLeft class="w-4 h-4 flex-shrink-0 text-emerald-600" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Kenaikan Kelas</span>
              </Transition>
            </RouterLink>

            <!-- Buku Induk Alumni -->
            <RouterLink
              to="/admin/alumni"
              :title="isCollapsed ? 'Buku Induk Alumni' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <BookOpen class="w-4 h-4 flex-shrink-0 text-indigo-600" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Buku Alumni</span>
              </Transition>
            </RouterLink>

            <!-- Data Guru -->
            <RouterLink
              v-if="currentRole === 'admin' || currentRole === 'operator' || currentRole === 'kurikulum' || currentRole === 'kepala_sekolah'"
              to="/admin/teachers"
              :title="isCollapsed ? 'Data Guru' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <UserCheck class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Data Guru</span>
              </Transition>
            </RouterLink>

            <!-- Manajemen Kelas -->
            <RouterLink
              v-if="currentRole === 'admin' || currentRole === 'kurikulum' || currentRole === 'kepala_sekolah'"
              to="/admin/classes"
              :title="isCollapsed ? 'Manajemen Kelas' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Building2 class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Rombel & Kelas</span>
              </Transition>
            </RouterLink>

            <!-- Tahun Ajaran -->
            <RouterLink
              v-if="currentRole === 'admin' || currentRole === 'kurikulum' || currentRole === 'kepala_sekolah'"
              to="/admin/academic-years"
              :title="isCollapsed ? 'Tahun Ajaran' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <CalendarDays class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Tahun Ajaran</span>
              </Transition>
            </RouterLink>

            <!-- Ekstrakurikuler (Data Master & Penilaian) -->
            <RouterLink
              v-if="currentRole === 'admin' || currentRole === 'operator' || currentRole === 'kurikulum' || currentRole === 'kepala_sekolah'"
              to="/admin/extracurriculars"
              :title="isCollapsed ? 'Ekstrakurikuler' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Tent class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Ekstrakurikuler</span>
              </Transition>
            </RouterLink>

            <!-- Penerimaan Siswa Baru (PPDB) -->
            <RouterLink
              v-if="currentRole === 'admin' || currentRole === 'operator' || currentRole === 'kepala_sekolah'"
              to="/admin/ppdb"
              :title="isCollapsed ? 'Penerimaan Siswa (PPDB)' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <UserPlus class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">PPDB</span>
              </Transition>
            </RouterLink>
          </div>
        </template>

        <!-- 3. PRESENSI & KEHADIRAN GURU (Khusus Peran Teacher) -->
        <template v-if="currentRole === 'teacher'">
          <!-- A. PRESENSI & JADWAL GURU -->
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('teacher_presensi')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('teacher_presensi') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Presensi & Jadwal</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('teacher_presensi') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('teacher_presensi')" class="space-y-0.5">
            <!-- Absensi Saya GPS -->
            <RouterLink
              to="/teacher/presensi"
              :title="isCollapsed ? 'Presensi GPS' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <MapPin class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Presensi GPS</span>
              </Transition>
            </RouterLink>

            <!-- Rekap Absensi Saya -->
            <RouterLink
              to="/teacher/presensi-recap"
              :title="isCollapsed ? 'Rekap Presensi Saya' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <FileSpreadsheet class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Rekap Presensi</span>
              </Transition>
            </RouterLink>

            <!-- Jadwal Mengajar Saya -->
            <RouterLink
              to="/teacher/schedules"
              :title="isCollapsed ? 'Jadwal Mengajar' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Clock3 class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Jadwal Mengajar</span>
              </Transition>
            </RouterLink>

            <!-- Kalender Akademik -->
            <RouterLink
              to="/teacher/calendar"
              :title="isCollapsed ? 'Kalender Akademik' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Calendar class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Kalender</span>
              </Transition>
            </RouterLink>
          </div>

          <!-- B. PEMBELAJARAN & ASESMEN -->
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('teacher_pembelajaran')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('teacher_pembelajaran') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Pembelajaran</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('teacher_pembelajaran') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('teacher_pembelajaran')" class="space-y-0.5">
            <!-- Bank Soal & Kisi-Kisi Ujian -->
            <RouterLink
              v-if="isTeaching"
              to="/teacher/exam-bank"
              :title="isCollapsed ? 'Bank Soal' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <BookOpen class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Bank Soal</span>
              </Transition>
            </RouterLink>

            <!-- Input Presensi Kelas -->
            <RouterLink
              v-if="isTeaching"
              to="/teacher/attendance"
              :title="isCollapsed ? 'Presensi & Jurnal Kelas' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <ClipboardCheck class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Jurnal & Presensi</span>
              </Transition>
            </RouterLink>

            <!-- Koreksi Soal & Asesmen -->
            <RouterLink
              v-if="isTeaching"
              to="/teacher/exam-corrections"
              :title="isCollapsed ? 'Koreksi Ujian' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <FileCheck2 class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Koreksi Ujian</span>
              </Transition>
            </RouterLink>

            <!-- Input Nilai -->
            <RouterLink
              v-if="isTeaching"
              to="/teacher/grades"
              :title="isCollapsed ? 'Input Nilai Siswa' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Award class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Input Nilai</span>
              </Transition>
            </RouterLink>

            <!-- Ekstrakurikuler (Pembina Ekskul & Dewan Guru) -->
            <RouterLink
              to="/teacher/extracurriculars"
              :title="isCollapsed ? 'Ekstrakurikuler' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Tent class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <div v-if="!isCollapsed" class="flex items-center justify-between w-full">
                  <span class="text-sm whitespace-nowrap overflow-hidden">Ekstrakurikuler</span>
                  <span v-if="isExtracurricularAdvisor" class="px-1.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded text-[9px] font-bold uppercase">Pembina</span>
                </div>
              </Transition>
            </RouterLink>
          </div>

          <!-- C. RUANG WALI KELAS (Khusus Wali Kelas) -->
          <template v-if="isHomeroomTeacher">
            <button
              v-if="!isCollapsed"
              type="button"
              @click="toggleSection('teacher_homeroom')"
              class="nav-section-btn group"
            >
              <span class="flex items-center gap-2 truncate">
                <span
                  class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                  :class="isSectionOpen('teacher_homeroom') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
                ></span>
                <span class="truncate">Wali Kelas</span>
                <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[8px] font-extrabold uppercase">Wali</span>
              </span>
              <ChevronDown
                class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
                :class="{ '-rotate-90': !isSectionOpen('teacher_homeroom') }"
              />
            </button>
            <div v-else class="my-1 border-t border-slate-100"></div>

            <div v-show="isCollapsed || isSectionOpen('teacher_homeroom')" class="space-y-0.5">
              <!-- Absensi Harian Kelas (Khusus Wali Kelas) -->
              <RouterLink
                to="/teacher/homeroom-attendance"
                :title="isCollapsed ? 'Presensi Harian Kelas' : ''"
                class="nav-link"
                :class="isCollapsed ? 'justify-center' : ''"
                active-class="nav-link-active"
              >
                <ClipboardList class="w-4 h-4 flex-shrink-0" />
                <Transition name="label-fade">
                  <div v-if="!isCollapsed" class="flex items-center justify-between w-full">
                    <span class="text-sm whitespace-nowrap overflow-hidden">Presensi Harian</span>
                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold uppercase">Wali</span>
                  </div>
                </Transition>
              </RouterLink>

              <!-- Data Siswa Binaan (Khusus Wali Kelas) -->
              <RouterLink
                to="/teacher/students"
                :title="isCollapsed ? 'Data Siswa Binaan' : ''"
                class="nav-link"
                :class="isCollapsed ? 'justify-center' : ''"
                active-class="nav-link-active"
              >
                <Users class="w-4 h-4 flex-shrink-0" />
                <Transition name="label-fade">
                  <div v-if="!isCollapsed" class="flex items-center justify-between w-full">
                    <span class="text-sm whitespace-nowrap overflow-hidden">Siswa Binaan</span>
                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold uppercase">Wali</span>
                  </div>
                </Transition>
              </RouterLink>

              <!-- Rapor ASTS (Sumatif Tengah Semester - Khusus Wali Kelas) -->
              <RouterLink
                to="/teacher/asts-reports"
                :title="isCollapsed ? 'Rapor ASTS (Tengah Semester)' : ''"
                class="nav-link"
                :class="isCollapsed ? 'justify-center' : ''"
                active-class="nav-link-active"
              >
                <BookOpenCheck class="w-4 h-4 flex-shrink-0" />
                <Transition name="label-fade">
                  <div v-if="!isCollapsed" class="flex items-center justify-between w-full">
                    <span class="text-sm whitespace-nowrap overflow-hidden">Rapor ASTS</span>
                    <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded text-[9px] font-bold uppercase">Wali</span>
                  </div>
                </Transition>
              </RouterLink>
            </div>
          </template>

          <!-- D. TUGAS TAMBAHAN & PROFIL -->
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('teacher_extra')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('teacher_extra') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Panitia & Akun</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('teacher_extra') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('teacher_extra')" class="space-y-0.5">
            <!-- Panitia PPDB (Jika Ditugaskan) -->
            <RouterLink
              v-if="user?.teacher?.is_ppdb_committee || isPpdbCommittee"
              to="/teacher/ppdb"
              :title="isCollapsed ? 'Panitia PPDB' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <UserPlus class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Panitia PPDB</span>
              </Transition>
            </RouterLink>

            <!-- Profil Guru -->
            <RouterLink
              to="/teacher/profile"
              :title="isCollapsed ? 'Profil Guru' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <UserCircle class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Profil Guru</span>
              </Transition>
            </RouterLink>
          </div>
        </template>

        <!-- Monitoring & Rekap (Admin & Kepala Madrasah) -->
        <template v-if="currentRole === 'admin' || currentRole === 'kepala_sekolah'">
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('admin_supervisi')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('admin_supervisi') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Supervisi & Monitoring</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('admin_supervisi') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('admin_supervisi')" class="space-y-0.5">
            <!-- Monitoring Absensi Siswa Harian -->
            <RouterLink
              to="/admin/daily-student-attendance"
              :title="isCollapsed ? 'Monitoring Absensi Siswa' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <ClipboardList class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Monitoring Siswa</span>
              </Transition>
            </RouterLink>

            <!-- Telaah & Review Soal Kurikulum -->
            <RouterLink
              to="/kurikulum/exam-reviews"
              :title="isCollapsed ? 'Telaah & Review Soal' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <ClipboardCheck class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Review Soal</span>
              </Transition>
            </RouterLink>

            <!-- Monitoring Koreksi Ujian -->
            <RouterLink
              to="/admin/exam-corrections"
              :title="isCollapsed ? 'Monitoring Koreksi Ujian' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <FileCheck2 class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Koreksi Ujian</span>
              </Transition>
            </RouterLink>

            <!-- Rapor ASTS (Sumatif Tengah Semester) -->
            <RouterLink
              to="/admin/asts-reports"
              :title="isCollapsed ? 'Rapor ASTS (PTS)' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <BookOpenCheck class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Rapor ASTS</span>
              </Transition>
            </RouterLink>

            <!-- Monitoring Leger Wali Kelas -->
            <RouterLink
              to="/admin/ledger-monitoring"
              :title="isCollapsed ? 'Monitoring Leger Wali Kelas' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <TableProperties class="w-4 h-4 flex-shrink-0 text-emerald-600" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Leger Nilai</span>
              </Transition>
            </RouterLink>

            <!-- Monitoring ASTS & Standar KKTP -->
            <RouterLink
              to="/kurikulum/asts-monitoring"
              :title="isCollapsed ? 'Monitoring ASTS & Standar KKTP' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Sliders class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Standar KKTP</span>
              </Transition>
            </RouterLink>

            <!-- Rekap Presensi -->
            <RouterLink
              to="/admin/attendance-reports"
              :title="isCollapsed ? 'Rekap Presensi Siswa' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <FileSpreadsheet class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Rekap Presensi</span>
              </Transition>
            </RouterLink>

            <!-- Jadwal & Kegiatan -->
            <RouterLink
              to="/admin/schedules"
              :title="isCollapsed ? 'Jadwal & Kegiatan' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <CalendarCheck class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Jadwal KBM</span>
              </Transition>
            </RouterLink>

            <!-- Monitoring Presensi Guru -->
            <RouterLink
              to="/admin/teacher-presensi-monitoring"
              :title="isCollapsed ? 'Monitoring Presensi Guru' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <MapPin class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Presensi Guru</span>
              </Transition>
            </RouterLink>
          </div>
        </template>

        <!-- Student Links -->
        <template v-if="currentRole === 'student'">
          <div v-if="!isCollapsed" class="nav-section">Akademik Siswa</div>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <RouterLink
            to="/student/profile"
            :title="isCollapsed ? 'Profil Saya' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <UserCircle class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade"><span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Profil Saya</span></Transition>
          </RouterLink>

          <RouterLink
            to="/student/attendances"
            :title="isCollapsed ? 'Presensi Saya' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <ClipboardCheck class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade"><span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Presensi Saya</span></Transition>
          </RouterLink>

          <RouterLink
            to="/student/transcript"
            :title="isCollapsed ? 'Nilai & Rapor' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Award class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade"><span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Nilai & Rapor</span></Transition>
          </RouterLink>

          <!-- Buku SPP & Pembayaran Siswa -->
          <RouterLink
            to="/student/finance"
            :title="isCollapsed ? 'Tagihan SPP' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Wallet class="w-4 h-4 flex-shrink-0 text-emerald-600" />
            <Transition name="label-fade"><span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Tagihan SPP</span></Transition>
          </RouterLink>
        </template>

        <!-- 4. MANAJEMEN WEBSITE (Admin) -->
        <template v-if="currentRole === 'admin'">
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('admin_website')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('admin_website') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Website Sekolah</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('admin_website') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('admin_website')" class="space-y-0.5">
            <!-- Berita & Artikel -->
            <RouterLink
              to="/admin/posts"
              :title="isCollapsed ? 'Berita & Artikel' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Newspaper class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Berita</span>
              </Transition>
            </RouterLink>

            <!-- Galeri Foto -->
            <RouterLink
              to="/admin/galleries"
              :title="isCollapsed ? 'Galeri Foto' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <ImageIcon class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Galeri</span>
              </Transition>
            </RouterLink>

            <!-- Prestasi Siswa -->
            <RouterLink
              to="/admin/achievements"
              :title="isCollapsed ? 'Prestasi Siswa' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Trophy class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Prestasi</span>
              </Transition>
            </RouterLink>

            <!-- Sarana Prasarana -->
            <RouterLink
              to="/admin/facilities"
              :title="isCollapsed ? 'Sarana & Prasarana' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Warehouse class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Fasilitas</span>
              </Transition>
            </RouterLink>
          </div>

          <!-- 5. PENGATURAN & KEAMANAN (Admin) -->
          <button
            v-if="!isCollapsed"
            type="button"
            @click="toggleSection('admin_sistem')"
            class="nav-section-btn group"
          >
            <span class="flex items-center gap-2 truncate">
              <span
                class="w-1.5 h-1.5 rounded-full transition-colors duration-150 flex-shrink-0"
                :class="isSectionOpen('admin_sistem') ? 'bg-emerald-500 shadow-xs' : 'bg-slate-300 group-hover:bg-slate-400'"
              ></span>
              <span class="truncate">Pengaturan Sistem</span>
            </span>
            <ChevronDown
              class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200 flex-shrink-0"
              :class="{ '-rotate-90': !isSectionOpen('admin_sistem') }"
            />
          </button>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <div v-show="isCollapsed || isSectionOpen('admin_sistem')" class="space-y-0.5">
            <!-- Kalender Akademik -->
            <RouterLink
              to="/admin/calendar-events"
              :title="isCollapsed ? 'Kalender Akademik' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Calendar class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Kalender Akademik</span>
              </Transition>
            </RouterLink>

            <!-- Reset Password Requests -->
            <button
              @click="$emit('open-reset-requests')"
              :title="isCollapsed ? 'Permintaan Reset' : ''"
              class="nav-link w-full text-left cursor-pointer"
              :class="isCollapsed ? 'justify-center' : ''"
            >
              <div class="relative flex-shrink-0">
                <KeyRound class="w-4 h-4 text-purple-600" />
                <span v-if="pendingResetRequestsCount > 0 && isCollapsed" class="absolute -top-1 -right-1 w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
              </div>
              <Transition name="label-fade">
                <div v-if="!isCollapsed" class="flex items-center justify-between w-full">
                  <span class="text-sm whitespace-nowrap overflow-hidden">Reset Password</span>
                  <span v-if="pendingResetRequestsCount > 0" class="px-2 py-0.5 text-[10px] font-bold bg-rose-500 text-white rounded-full">
                    {{ pendingResetRequestsCount }}
                  </span>
                </div>
              </Transition>
            </button>

            <!-- Manajemen Pengguna (Akun Staf, Guru, & User) -->
            <RouterLink
              to="/admin/users"
              :title="isCollapsed ? 'Manajemen Pengguna' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Users class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Pengguna</span>
              </Transition>
            </RouterLink>

            <!-- Pengaturan Aplikasi -->
            <RouterLink
              to="/admin/settings"
              :title="isCollapsed ? 'Pengaturan' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Settings class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Pengaturan</span>
              </Transition>
            </RouterLink>

            <!-- Backup & Restore Database -->
            <RouterLink
              to="/admin/database-backup"
              :title="isCollapsed ? 'Backup Database' : ''"
              class="nav-link"
              :class="isCollapsed ? 'justify-center' : ''"
              active-class="nav-link-active"
            >
              <Database class="w-4 h-4 flex-shrink-0" />
              <Transition name="label-fade">
                <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Backup Data</span>
              </Transition>
            </RouterLink>
          </div>
        </template>

        <!-- 6. PROFIL & BIODATA STAF (Admin, Kurikulum, Operator, Kepala Madrasah, Bendahara) -->
        <template v-if="currentRole === 'admin' || currentRole === 'operator' || currentRole === 'kurikulum' || currentRole === 'kepala_sekolah' || currentRole === 'bendahara'">
          <div v-if="!isCollapsed" class="nav-section">Akun Saya</div>
          <div v-else class="my-1 border-t border-slate-100"></div>

          <RouterLink
            to="/admin/profile"
            :title="isCollapsed ? 'Profil & Biodata Diri' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <UserCircle class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Profil Staf</span>
            </Transition>
          </RouterLink>
        </template>
      </nav>

      <!-- Bottom: Logout -->
      <div class="border-t border-slate-100 flex-shrink-0 bg-slate-50/50"
           :class="isCollapsed ? 'p-2 space-y-1' : 'p-3 space-y-1'">
        <button
          @click="$emit('logout')"
          :title="isCollapsed ? 'Keluar Aplikasi' : ''"
          class="flex items-center gap-2.5 px-3 py-2 text-slate-500 font-medium text-xs rounded-xl transition-colors hover:text-rose-600 hover:bg-rose-50 cursor-pointer w-full text-left"
          :class="isCollapsed ? 'justify-center' : ''"
        >
          <LogOut class="w-4 h-4 flex-shrink-0" />
          <Transition name="label-fade">
            <span v-if="!isCollapsed" class="text-xs whitespace-nowrap overflow-hidden">Keluar Aplikasi</span>
          </Transition>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import {
  LayoutDashboard,
  UserCheck,
  Building2,
  BookOpen,
  CalendarDays,
  MapPin,
  FileSpreadsheet,
  CalendarCheck,
  Clock3,
  ClipboardList,
  ClipboardCheck,
  GraduationCap,
  Award,
  UserCircle,
  Newspaper,
  Image as ImageIcon,
  Trophy,
  Warehouse,
  Calendar,
  Printer,
  KeyRound,
  Settings,
  LogOut,
  School,
  UserPlus,
  FileText,
  Inbox,
  Send,
  Users,
  FileCheck2,
  BookOpenCheck,
  Sliders,
  TableProperties,
  Tent,
  ArrowRightLeft,
  ChevronDown,
  Database,
  Wallet,
  X
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  appSettings: Object,
  isCollapsed: Boolean,
  isMobileSidebarOpen: Boolean,
  isHomeroomTeacher: Boolean,
  isPpdbCommittee: Boolean,
  isExtracurricularAdvisor: Boolean,
  isTeaching: {
    type: Boolean,
    default: true
  },
  pendingResetRequestsCount: {
    type: Number,
    default: 0
  },
  getImageUrl: Function
});

const emit = defineEmits(['close-mobile-sidebar', 'logout', 'open-reset-requests']);

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

// --- Accordion Logic & Mapping ---
const openSections = ref({});

const routeSectionMap = {
  administrasi: ['/letters', '/print-center', '/graduation-grades', '/finance'],
  kurikulum_kbm: ['/admin/schedules', '/admin/subjects', '/admin/calendar-events'],
  kurikulum_asesmen: ['/kurikulum/exam-reviews', '/admin/exam-reviews', '/admin/exam-corrections', '/admin/grades', '/admin/ledger-monitoring', '/kurikulum/asts-monitoring'],
  kurikulum_supervisi: ['/admin/teacher-presensi-monitoring'],
  data_master: ['/admin/students', '/admin/promotions', '/admin/alumni', '/admin/teachers', '/admin/classes', '/admin/academic-years', '/admin/extracurriculars', '/admin/ppdb'],
  teacher_presensi: ['/teacher/presensi', '/teacher/presensi-recap', '/teacher/schedules', '/teacher/calendar'],
  teacher_pembelajaran: ['/teacher/exam-bank', '/teacher/attendance', '/teacher/exam-corrections', '/teacher/grades', '/teacher/extracurriculars'],
  teacher_homeroom: ['/teacher/homeroom-attendance', '/teacher/students', '/teacher/asts-reports'],
  teacher_extra: ['/teacher/ppdb', '/teacher/profile'],
  admin_supervisi: ['/kurikulum/exam-reviews', '/admin/exam-reviews', '/admin/daily-student-attendance', '/admin/exam-corrections', '/admin/asts-reports', '/admin/ledger-monitoring', '/kurikulum/asts-monitoring', '/admin/attendance-reports', '/admin/schedules', '/admin/teacher-presensi-monitoring'],
  admin_website: ['/admin/posts', '/admin/galleries', '/admin/achievements', '/admin/facilities'],
  admin_sistem: ['/admin/calendar-events', '/admin/users', '/admin/settings', '/admin/database-backup']
};

function isRouteInSection(key) {
  const currentPath = route?.path || '';
  const paths = routeSectionMap[key] || [];
  return paths.some(p => currentPath === p || currentPath.startsWith(p + '/') || (p === '/letters' && currentPath.includes('/letters')));
}

function isSectionOpen(key) {
  if (openSections.value[key] !== undefined) {
    return openSections.value[key];
  }
  return isRouteInSection(key);
}

function toggleSection(key) {
  const current = isSectionOpen(key);
  if (!current) {
    // Smart Accordion: close other non-route sections to prevent endless scrolling
    for (const k of Object.keys(routeSectionMap)) {
      if (k !== key && !isRouteInSection(k)) {
        openSections.value[k] = false;
      }
    }
    openSections.value[key] = true;
  } else {
    openSections.value[key] = false;
  }
}

watch(() => route?.path, (newPath) => {
  if (!newPath) return;
  for (const [key, paths] of Object.entries(routeSectionMap)) {
    if (paths.some(p => newPath === p || newPath.startsWith(p + '/') || (p === '/letters' && newPath.includes('/letters')))) {
      openSections.value[key] = true;
    }
  }
}, { immediate: true });

const currentRole = computed(() => auth.role || props.user?.role);

function handleSidebarSwitchRole(targetRole) {
  auth.switchRole(targetRole);
  if (targetRole === 'teacher') {
    router.push('/teacher/dashboard');
  } else if (targetRole === 'operator') {
    router.push('/operator/dashboard');
  } else if (targetRole === 'kurikulum') {
    router.push('/kurikulum/dashboard');
  } else if (targetRole === 'admin') {
    router.push('/admin/dashboard');
  }
}

const logoError = ref(false);

watch(() => props.appSettings?.app_logo, () => {
  logoError.value = false;
});

const resolveImageUrl = (path) => {
  if (props.getImageUrl && typeof props.getImageUrl === 'function') {
    return props.getImageUrl(path);
  }
  if (!path) return '';
  if (typeof path !== 'string') return '';
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path.replace(/^\//, '');
};
</script>

