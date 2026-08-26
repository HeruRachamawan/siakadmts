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
        isCollapsed ? 'w-[68px]' : 'w-[240px]',
        isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full sm:translate-x-0',
        'bg-white border-r border-slate-200/80 flex flex-col transition-all duration-200 z-40 flex-shrink-0 fixed sm:relative h-full shadow-xs no-print'
      ]"
    >
      <!-- Logo Area -->
      <div class="h-16 flex items-center border-b border-slate-200/80 flex-shrink-0 bg-white"
           :class="isCollapsed ? 'justify-center px-0' : 'px-4'">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center shadow-xs border border-emerald-700/50 flex-shrink-0 overflow-hidden p-1.5">
            <img 
              v-if="appSettings?.app_logo && !logoError" 
              :src="resolveImageUrl(appSettings.app_logo)" 
              @error="logoError = true"
              class="w-full h-full object-contain filter drop-shadow" 
              alt="Logo" 
            />
            <School v-else class="w-5 h-5 text-white" />
          </div>
          <Transition name="label-fade">
            <div v-if="!isCollapsed" class="flex flex-col overflow-hidden">
              <span class="font-extrabold text-sm text-slate-900 tracking-wider leading-none truncate max-w-[145px] uppercase">{{ appSettings?.app_name || 'PORTAL' }}</span>
              <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider mt-1 whitespace-nowrap">
                {{ user?.role }} &bull; T.A. 26/27
              </span>
            </div>
          </Transition>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 py-3 overflow-y-auto custom-scrollbar-dark"
           :class="isCollapsed ? 'px-2 space-y-1' : 'px-3 space-y-1'">

        <!-- 1. MENU UTAMA -->
        <div v-if="!isCollapsed" class="nav-section">Utama</div>
        <div v-else class="my-1 border-t border-slate-200/50"></div>

        <!-- Dashboard -->
        <RouterLink
          :to="`/${user?.role === 'admin' ? 'admin' : (user?.role === 'operator' ? 'operator' : (user?.role === 'kurikulum' ? 'kurikulum' : (user?.role === 'teacher' ? 'teacher' : 'student')))}/dashboard`"
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

        <!-- 2. ADMINISTRASI & PERSURATAN (Admin, Operator TU, & Waka Kurikulum) -->
        <template v-if="user?.role === 'admin' || user?.role === 'operator' || user?.role === 'kurikulum'">
          <div v-if="!isCollapsed" class="nav-section">Administrasi & Persuratan</div>
          <div v-else class="my-1 border-t border-slate-200/50"></div>

          <!-- Buku Agenda Surat Masuk & Keluar -->
          <RouterLink
            :to="user?.role === 'admin' ? '/admin/letters' : (user?.role === 'kurikulum' ? '/kurikulum/letters' : '/operator/letters')"
            :title="isCollapsed ? 'Buku Agenda Persuratan' : ''"
            class="nav-link bg-emerald-50/60 text-emerald-800 border border-emerald-200/80 font-bold"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <FileText class="w-4 h-4 text-emerald-600 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Buku Agenda Surat</span>
            </Transition>
          </RouterLink>

          <!-- Pusat Cetak Dokumen (Admin & Operator TU) -->
          <RouterLink
            v-if="user?.role === 'admin' || user?.role === 'operator'"
            to="/admin/print-center"
            :title="isCollapsed ? 'Pusat Cetak Dokumen' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Printer class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Pusat Cetak Dokumen</span>
            </Transition>
          </RouterLink>
        </template>

        <!-- 3. KURIKULUM & AKADEMIK (Admin & Kurikulum) -->
        <template v-if="user?.role === 'kurikulum'">
          <div v-if="!isCollapsed" class="nav-section">Kurikulum & KBM</div>
          <div v-else class="my-1 border-t border-slate-200/50"></div>

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
            to="/admin/grades"
            :title="isCollapsed ? 'Rekap Nilai Siswa' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Award class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Rekap Nilai Siswa</span>
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

          <RouterLink
            to="/admin/teacher-presensi-monitoring"
            :title="isCollapsed ? 'Monitoring Absen Guru' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <MapPin class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Monitoring Absen Guru</span>
            </Transition>
          </RouterLink>
        </template>

        <!-- 4. DATA MASTER (Admin, Operator, Kurikulum & Wali Kelas) -->
        <template v-if="user?.role === 'admin' || user?.role === 'operator' || user?.role === 'kurikulum' || (user?.role === 'teacher' && isHomeroomTeacher)">
          <div v-if="!isCollapsed" class="nav-section">Data Master</div>
          <div v-else class="my-1 border-t border-slate-200/50"></div>

          <!-- Data Siswa -->
          <RouterLink
            :to="`/${user?.role === 'teacher' ? 'teacher' : 'admin'}/students`"
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

          <!-- Data Guru -->
          <RouterLink
            v-if="user?.role === 'admin' || user?.role === 'operator' || user?.role === 'kurikulum'"
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
            v-if="user?.role === 'admin' || user?.role === 'kurikulum'"
            to="/admin/classes"
            :title="isCollapsed ? 'Manajemen Kelas' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Building2 class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Manajemen Kelas</span>
            </Transition>
          </RouterLink>

          <!-- Tahun Ajaran -->
          <RouterLink
            v-if="user?.role === 'admin' || user?.role === 'kurikulum'"
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

          <!-- Penerimaan Siswa Baru (PPDB) -->
          <RouterLink
            v-if="user?.role === 'admin' || user?.role === 'operator'"
            to="/admin/ppdb"
            :title="isCollapsed ? 'Penerimaan Siswa (PPDB)' : ''"
            class="nav-link bg-emerald-50/50 text-emerald-800 border border-emerald-200/60 font-semibold"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <UserPlus class="w-4 h-4 flex-shrink-0 text-emerald-600" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Penerimaan Siswa (PPDB)</span>
            </Transition>
          </RouterLink>
        </template>

        <!-- 3. PRESENSI & KEHADIRAN GURU (Khusus Peran Teacher) -->
        <template v-if="user?.role === 'teacher'">
          <div v-if="!isCollapsed" class="nav-section">Kehadiran Guru (GPS)</div>
          <div v-else class="my-1 border-t border-slate-200/50"></div>

          <!-- Absensi Saya GPS -->
          <RouterLink
            to="/teacher/presensi"
            :title="isCollapsed ? 'Absensi Saya (GPS)' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <MapPin class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Absensi Saya (GPS)</span>
            </Transition>
          </RouterLink>

          <!-- Rekap Absensi Saya -->
          <RouterLink
            to="/teacher/presensi-recap"
            :title="isCollapsed ? 'Rekap Absensi Saya' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <FileSpreadsheet class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Rekap Absensi Saya</span>
            </Transition>
          </RouterLink>

          <!-- 4. AKADEMIK & PEMBELAJARAN GURU -->
          <div v-if="!isCollapsed" class="nav-section">Akademik & Kelas</div>
          <div v-else class="my-1 border-t border-slate-200/50"></div>

          <!-- Absensi Harian Kelas (Khusus Wali Kelas) -->
          <RouterLink
            v-if="isHomeroomTeacher"
            to="/teacher/homeroom-attendance"
            :title="isCollapsed ? 'Absensi Harian Kelas' : ''"
            class="nav-link bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-bold"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <ClipboardList class="w-4 h-4 text-emerald-600 flex-shrink-0" />
            <Transition name="label-fade">
              <div v-if="!isCollapsed" class="flex items-center justify-between w-full">
                <span class="text-sm whitespace-nowrap overflow-hidden">Absensi Harian</span>
                <span class="px-1.5 py-0.2 bg-emerald-600 text-white rounded-md text-[9px] font-black uppercase">Wali</span>
              </div>
            </Transition>
          </RouterLink>

          <!-- Input Presensi Kelas -->
          <RouterLink
            to="/teacher/attendance"
            :title="isCollapsed ? 'Input Presensi Kelas' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <ClipboardCheck class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Presensi Mapel</span>
            </Transition>
          </RouterLink>

          <!-- Input Nilai -->
          <RouterLink
            to="/teacher/grades"
            :title="isCollapsed ? 'Input Nilai' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Award class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Nilai & Transkrip</span>
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
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Kalender Akademik</span>
            </Transition>
          </RouterLink>

          <!-- Panitia PPDB (Jika Ditugaskan) -->
          <RouterLink
            v-if="user?.teacher?.is_ppdb_committee || isPpdbCommittee"
            to="/teacher/ppdb"
            :title="isCollapsed ? 'Panitia PPDB' : ''"
            class="nav-link bg-emerald-50/50 text-emerald-800 border border-emerald-200/60 font-semibold"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <UserPlus class="w-4 h-4 flex-shrink-0 text-emerald-600" />
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
        </template>

        <!-- Admin Monitoring Links -->
        <template v-if="user?.role === 'admin'">
          <div v-if="!isCollapsed" class="nav-section">Monitoring & Rekap</div>
          <div v-else class="my-1 border-t border-slate-200/50"></div>

          <!-- Monitoring Absensi Siswa Harian -->
          <RouterLink
            to="/admin/daily-student-attendance"
            :title="isCollapsed ? 'Monitoring Absensi Siswa' : ''"
            class="nav-link bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-bold"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <ClipboardList class="w-4 h-4 text-emerald-600 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Monitoring Siswa</span>
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
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Rekap Presensi Siswa</span>
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
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Jadwal & Kegiatan</span>
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
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Monitoring Absen Guru</span>
            </Transition>
          </RouterLink>
        </template>

        <!-- Student Links -->
        <template v-if="user?.role === 'student'">
          <div v-if="!isCollapsed" class="nav-section">Akademik Siswa</div>
          <div v-else class="my-1 border-t border-slate-200/50"></div>

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
            :title="isCollapsed ? 'Kehadiran Saya' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <ClipboardCheck class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade"><span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Kehadiran Saya</span></Transition>
          </RouterLink>

          <RouterLink
            to="/student/transcript"
            :title="isCollapsed ? 'Transkrip Nilai' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Award class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade"><span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Transkrip Nilai</span></Transition>
          </RouterLink>
        </template>

        <!-- 4. MANAJEMEN WEBSITE (Admin) -->
        <template v-if="user?.role === 'admin'">
          <div v-if="!isCollapsed" class="nav-section">Manajemen Website</div>
          <div v-else class="my-1 border-t border-slate-200/50"></div>

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
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Berita & Artikel</span>
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
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Galeri Foto</span>
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
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Prestasi Siswa</span>
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
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Sarana Prasarana</span>
            </Transition>
          </RouterLink>

          <!-- 5. PENGATURAN & CETAK (Admin) -->
          <div class="nav-section">Sistem & Laporan</div>

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

          <!-- Pusat Cetak -->
          <RouterLink
            to="/admin/print-center"
            :title="isCollapsed ? 'Pusat Cetak' : ''"
            class="nav-link"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Printer class="w-4 h-4 flex-shrink-0" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Pusat Cetak</span>
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
                <span class="text-sm whitespace-nowrap overflow-hidden">Permintaan Reset</span>
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
            class="nav-link bg-indigo-50/50 text-indigo-900 border border-indigo-200/60 font-semibold"
            :class="isCollapsed ? 'justify-center' : ''"
            active-class="nav-link-active"
          >
            <Users class="w-4 h-4 flex-shrink-0 text-indigo-600" />
            <Transition name="label-fade">
              <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Manajemen Pengguna</span>
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
        </template>
      </nav>

      <!-- Bottom: Logout -->
      <div class="border-t border-slate-200/50 flex-shrink-0"
           :class="isCollapsed ? 'p-2 space-y-1' : 'p-3 space-y-1'">
        <button
          @click="$emit('logout')"
          :title="isCollapsed ? 'Keluar Aplikasi' : ''"
          class="flex items-center gap-3 px-3 py-2 text-slate-500 font-medium rounded-lg transition-all duration-150 hover:text-red-600 hover:bg-red-50 cursor-pointer w-full text-left"
          :class="isCollapsed ? 'justify-center' : ''"
        >
          <LogOut class="w-4 h-4 flex-shrink-0" />
          <Transition name="label-fade">
            <span v-if="!isCollapsed" class="text-sm whitespace-nowrap overflow-hidden">Keluar Aplikasi</span>
          </Transition>
        </button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
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
  Users
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  appSettings: Object,
  isCollapsed: Boolean,
  isMobileSidebarOpen: Boolean,
  isHomeroomTeacher: Boolean,
  isPpdbCommittee: Boolean,
  pendingResetRequestsCount: {
    type: Number,
    default: 0
  },
  getImageUrl: Function
});

defineEmits(['close-mobile-sidebar', 'logout', 'open-reset-requests']);

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
