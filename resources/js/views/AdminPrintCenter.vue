<template>
  <div class="space-y-6 font-inter print-container">
    <!-- Top Header & Tabs (Hidden on Print) -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
      <div>
        <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Pusat Pencetakan Dokumen</h1>
        <p class="text-xs text-slate-500 mt-1 font-medium">Cetak Jadwal Pelajaran, Data Siswa, Data Guru, Kartu Pelajar, dan Kalender Akademik dengan Kop Resmi.</p>
      </div>

      <!-- Navigation Tabs with Lucide Icons -->
      <div class="flex p-1 bg-slate-100 rounded-2xl border border-slate-200/60 flex-wrap gap-1">
        <!-- Tab 1: Jadwal -->
        <button
          @click="activeTab = 'schedule'"
          :class="[activeTab === 'schedule' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 font-semibold', 'px-3.5 py-2 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer']"
        >
          <CalendarDays class="w-4 h-4 text-emerald-600" />
          <span>Jadwal Pelajaran</span>
        </button>

        <!-- Tab 2: Data Siswa (NEW) -->
        <button
          @click="activeTab = 'students'"
          :class="[activeTab === 'students' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 font-semibold', 'px-3.5 py-2 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer']"
        >
          <GraduationCap class="w-4 h-4 text-emerald-600" />
          <span>Data Siswa</span>
        </button>

        <!-- Tab 3: Data Guru (NEW) -->
        <button
          @click="activeTab = 'teachers'"
          :class="[activeTab === 'teachers' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 font-semibold', 'px-3.5 py-2 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer']"
        >
          <UserCheck class="w-4 h-4 text-emerald-600" />
          <span>Data Guru</span>
        </button>

        <!-- Tab 4: Kartu Pelajar -->
        <button
          @click="activeTab = 'card'"
          :class="[activeTab === 'card' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 font-semibold', 'px-3.5 py-2 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer']"
        >
          <CreditCard class="w-4 h-4 text-emerald-600" />
          <span>Kartu Pelajar</span>
        </button>

        <!-- Tab 5: Kalender -->
        <button
          @click="activeTab = 'calendar'"
          :class="[activeTab === 'calendar' ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 font-semibold', 'px-3.5 py-2 rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer']"
        >
          <Calendar class="w-4 h-4 text-emerald-600" />
          <span>Kalender</span>
        </button>
      </div>
    </div>

    <!-- ==================== TAB 1: CETAK JADWAL PELAJARAN ==================== -->
    <div v-if="activeTab === 'schedule'" class="space-y-6">
      <!-- Filter Control (No Print) -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
        <div class="flex flex-wrap items-center gap-4">
          <div>
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Mode Pencetakan</label>
            <select v-model="schedulePrintMode" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
              <option value="single">Per Kelas Spesifik</option>
              <option value="all">Semua Kelas Sekaligus</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Ukuran Kertas</label>
            <select v-model="selectedPaperSize" @change="applyPaperSize" class="bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2 text-xs font-bold text-emerald-800 focus:outline-none cursor-pointer">
              <option value="F4">📜 F4 / Folio (215mm x 330mm) - Standar</option>
              <option value="A4">📄 A4 (210mm x 297mm)</option>
              <option value="Letter">✉️ Letter (216mm x 279mm)</option>
            </select>
          </div>

          <div v-if="schedulePrintMode === 'single'">
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Pilih Kelas</label>
            <select v-model="selectedClassId" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
              <option value="">-- Pilih Kelas --</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">Kelas {{ cls.name }}</option>
            </select>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="exportExcelSchedule"
            class="px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 transition-colors flex items-center gap-2 shadow-xs cursor-pointer"
          >
            <Download class="w-4 h-4 text-emerald-600" />
            <span>Export Excel</span>
          </button>
          <button
            @click="triggerPrint"
            class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md cursor-pointer"
          >
            <Printer class="w-4 h-4 text-emerald-400" />
            <span>Cetak Dokumen</span>
          </button>
        </div>
      </div>

      <!-- Schedule Table Previews (Clean Layout) -->
      <div id="print-schedule-area" class="space-y-12">
        <div v-for="cls in getTargetClassesForSchedule()" :key="cls.id" class="page-break bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-6">
          <!-- Official School Schedule Header -->
          <div class="flex items-center justify-between border-b-2 border-slate-900 pb-4">
            <div class="flex items-center gap-4">
              <img v-if="appSettings?.app_logo" :src="getImageUrl(appSettings.app_logo)" class="w-14 h-14 object-contain" alt="Logo" />
              <div>
                <h2 class="text-lg font-black font-lexend uppercase tracking-wider text-slate-900">{{ appSettings?.app_name || 'MTS AL-HASANAH' }}</h2>
                <p class="text-xs text-slate-600 font-semibold uppercase tracking-wide">JADWAL PELAJARAN TAHUN AJARAN {{ activeAcademicYear?.year || '2026/2027' }}</p>
                <p class="text-[10px] text-slate-400 font-mono">{{ appSettings?.school_address || 'Jl. Raya Ciwidey No. 123' }}</p>
              </div>
            </div>
            <div class="text-right">
              <span class="inline-block px-4 py-1.5 bg-emerald-600 text-white font-lexend font-black text-sm uppercase rounded-xl shadow-xs">
                KELAS {{ cls.name }}
              </span>
              <p class="text-[10px] text-slate-500 font-semibold mt-1">Wali Kelas: {{ cls.homeroom_teacher?.full_name || cls.homeroomTeacher?.full_name || '-' }}</p>
            </div>
          </div>

          <!-- Master Matriks Jadwal Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-center border-collapse text-xs font-inter border border-slate-300">
              <thead>
                <tr class="bg-slate-100 text-slate-800 font-black border-b border-slate-300">
                  <th class="p-2.5 w-12 border border-slate-300">JAM</th>
                  <th class="p-2.5 w-32 border border-slate-300">WAKTU</th>
                  <th class="p-2.5 border border-slate-300">SENIN</th>
                  <th class="p-2.5 border border-slate-300">SELASA</th>
                  <th class="p-2.5 border border-slate-300">RABU</th>
                  <th class="p-2.5 border border-slate-300">KAMIS</th>
                  <th class="p-2.5 border border-slate-300">JUMAT</th>
                  <th class="p-2.5 border border-slate-300">SABTU</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="slot in activeYaspinSlots" :key="slot.no" class="border-b border-slate-200">
                  <td class="p-2 font-bold bg-slate-50 border border-slate-300">{{ slot.no }}</td>
                  <td class="p-2 font-mono font-bold text-slate-700 bg-slate-50 border border-slate-300 text-[11px]">{{ slot.start }} - {{ slot.end }}</td>
                  
                  <td v-if="slot.isGeneral || slot.isBreak" colspan="6" class="p-2 font-black uppercase tracking-wider text-[11px] border border-slate-300" :class="slot.isBreak ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-900'">
                    {{ slot.title }}
                  </td>

                  <template v-else>
                    <td v-for="d in ['senin','selasa','rabu','kamis','jumat','sabtu']" :key="d" class="p-2 border border-slate-300 vertical-middle">
                      <div v-if="getSingleScheduleCell(cls.id, d, slot)" class="leading-snug">
                        <p class="font-bold text-slate-900 text-xs uppercase">{{ getSingleScheduleCell(cls.id, d, slot).subject?.name || getSingleScheduleCell(cls.id, d, slot).activity_name }}</p>
                        <p class="text-[10px] text-slate-500 font-medium mt-0.5">{{ getSingleScheduleCell(cls.id, d, slot).teacher?.full_name || '-' }}</p>
                      </div>
                      <span v-else class="text-slate-300 font-mono text-[10px]">-</span>
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Official Signature Block -->
          <div class="pt-6 grid grid-cols-2 text-center text-xs font-semibold text-slate-700">
            <div>
              <p>Mengetahui,</p>
              <p class="font-bold">Kepala Madrasah / Sekolah</p>
              <div class="h-16"></div>
              <p class="font-bold underline uppercase">{{ appSettings?.principal_name || '............................................' }}</p>
              <p class="text-[10px] text-slate-500 font-mono">NIP: {{ appSettings?.principal_nip || '-' }}</p>
            </div>
            <div>
              <p>{{ getTodayDateFormatted() }}</p>
              <p class="font-bold">Wali Kelas {{ cls.name }}</p>
              <div class="h-16"></div>
              <p class="font-bold underline uppercase">{{ cls.homeroom_teacher?.full_name || cls.homeroomTeacher?.full_name || '( ............................................ )' }}</p>
              <p class="text-[10px] text-slate-500 font-mono">NIP: {{ cls.homeroom_teacher?.nip || cls.homeroomTeacher?.nip || '-' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 2: CETAK DATA SISWA (NEW) ==================== -->
    <div v-if="activeTab === 'students'" class="space-y-6">
      <!-- Filter Control (No Print) -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
        <div class="flex flex-wrap items-center gap-4">
          <div>
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Filter Kelas</label>
            <select v-model="studentFilterClass" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
              <option value="">Semua Kelas ({{ students.length }} Siswa)</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">Kelas {{ cls.name }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Jenis Kelamin</label>
            <select v-model="studentFilterGender" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
              <option value="">Semua Gender</option>
              <option value="L">Laki-laki (L)</option>
              <option value="P">Perempuan (P)</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Ukuran Kertas</label>
            <select v-model="selectedPaperSize" @change="applyPaperSize" class="bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2 text-xs font-bold text-emerald-800 focus:outline-none cursor-pointer">
              <option value="F4">📜 F4 / Folio (215mm x 330mm)</option>
              <option value="A4">📄 A4 (210mm x 297mm)</option>
            </select>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="exportExcelStudents"
            class="px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 transition-colors flex items-center gap-2 shadow-xs cursor-pointer"
          >
            <Download class="w-4 h-4 text-emerald-600" />
            <span>Export Excel</span>
          </button>
          <button
            @click="triggerPrint"
            class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md cursor-pointer"
          >
            <Printer class="w-4 h-4 text-emerald-400" />
            <span>Cetak Dokumen</span>
          </button>
        </div>
      </div>

      <!-- Printable Students Document Area -->
      <div id="print-students-area" class="bg-white p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-6">
        <!-- Kop Surat Resmi Sekolah -->
        <div class="flex items-center gap-5 border-b-4 border-double border-slate-900 pb-4">
          <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
            <img v-if="appSettings?.app_logo" :src="getImageUrl(appSettings.app_logo)" class="w-full h-full object-contain" alt="Logo Sekolah" />
            <div v-else class="w-16 h-16 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-black text-xl">MTS</div>
          </div>
          <div class="text-center flex-1 pr-14">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest">{{ appSettings?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM' }}</h3>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 uppercase font-lexend tracking-tight mt-0.5">{{ appSettings?.app_name || 'MADRASAH TSANAWIYAH AL-HASANAH' }}</h1>
            <p class="text-xs text-slate-600 font-medium mt-1">{{ appSettings?.school_address || 'Jl. Raya Ciwidey No. 123, Kab. Bandung' }}</p>
            <p class="text-[11px] text-slate-500 font-mono mt-0.5">
              Telp: {{ appSettings?.school_phone || '(022) 1234567' }} &bull; Email: {{ appSettings?.school_email || 'info@mtsalhasanah.sch.id' }}
            </p>
          </div>
        </div>

        <!-- Document Title & Subtitle -->
        <div class="text-center space-y-1 py-2">
          <h2 class="text-base sm:text-lg font-black uppercase text-slate-900 font-lexend tracking-wider underline">
            DAFTAR REKAPITULASI DATA PESERTA DIDIK
          </h2>
          <p class="text-xs font-bold text-slate-600">
            TAHUN PELAJARAN {{ activeAcademicYear?.year || '2026/2027' }} &bull; 
            <span class="text-emerald-700 uppercase">{{ studentFilterClass ? `KELAS ${getClassName(studentFilterClass)}` : 'SEMUA KELAS' }}</span>
          </p>
          <p class="text-[10px] text-slate-400 font-medium">Total: {{ filteredPrintStudents.length }} Siswa Terdaftar</p>
        </div>

        <!-- Student Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs font-inter border border-slate-300">
            <thead>
              <tr class="bg-slate-100 text-slate-900 font-black border-b border-slate-300 uppercase text-[10px] tracking-wider">
                <th class="p-2.5 w-10 text-center border border-slate-300">NO</th>
                <th class="p-2.5 w-28 text-center border border-slate-300">NISN / NIS</th>
                <th class="p-2.5 border border-slate-300">NAMA LENGKAP SISWA</th>
                <th class="p-2.5 w-12 text-center border border-slate-300">L/P</th>
                <th class="p-2.5 w-20 text-center border border-slate-300">KELAS</th>
                <th class="p-2.5 border border-slate-300">TEMPAT, TGL LAHIR</th>
                <th class="p-2.5 border border-slate-300">NAMA ORANG TUA / WALI</th>
                <th class="p-2.5 w-28 text-center border border-slate-300">NO. TELEPON</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(st, idx) in filteredPrintStudents" :key="st.id" class="border-b border-slate-200 hover:bg-slate-50/60">
                <td class="p-2 text-center font-bold text-slate-500 border border-slate-300">{{ idx + 1 }}</td>
                <td class="p-2 text-center font-mono font-bold text-slate-700 border border-slate-300 text-[11px]">
                  {{ st.nisn || st.nis || '-' }}
                </td>
                <td class="p-2 font-bold text-slate-900 uppercase border border-slate-300">{{ st.full_name }}</td>
                <td class="p-2 text-center font-black border border-slate-300" :class="st.gender === 'L' ? 'text-blue-700' : 'text-pink-700'">
                  {{ st.gender || '-' }}
                </td>
                <td class="p-2 text-center font-bold border border-slate-300">
                  {{ st.classRoom?.name || st.class_name || '-' }}
                </td>
                <td class="p-2 text-slate-700 border border-slate-300">
                  {{ st.birth_place || '-' }}, {{ formatDateIndo(st.birth_date) }}
                </td>
                <td class="p-2 text-slate-700 border border-slate-300">
                  {{ st.father_name || st.mother_name || st.guardian_name || '-' }}
                </td>
                <td class="p-2 text-center font-mono text-[11px] border border-slate-300">
                  {{ st.parent_phone || '-' }}
                </td>
              </tr>
              <tr v-if="filteredPrintStudents.length === 0">
                <td colspan="8" class="p-8 text-center text-slate-400 font-medium">Tidak ada data siswa yang cocok dengan filter.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Official Signature Block -->
        <div class="pt-8 grid grid-cols-2 text-center text-xs font-semibold text-slate-700">
          <div>
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala Madrasah / Sekolah</p>
            <div class="h-20"></div>
            <p class="font-bold underline uppercase">{{ appSettings?.principal_name || '............................................' }}</p>
            <p class="text-[10px] text-slate-500 font-mono">NIP: {{ appSettings?.principal_nip || '-' }}</p>
          </div>
          <div>
            <p>{{ getTodayDateFormatted() }}</p>
            <p class="font-bold">Kepala Tata Usaha / Pendataan</p>
            <div class="h-20"></div>
            <p class="font-bold underline uppercase">( ............................................ )</p>
            <p class="text-[10px] text-slate-500 font-mono">NIP: -</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 3: CETAK DATA GURU (NEW) ==================== -->
    <div v-if="activeTab === 'teachers'" class="space-y-6">
      <!-- Filter Control (No Print) -->
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
        <div class="flex flex-wrap items-center gap-4">
          <div>
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Status / Jabatan</label>
            <select v-model="teacherFilterPosition" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
              <option value="">Semua Dewan Guru ({{ teachers.length }} Orang)</option>
              <option value="Guru Pengajar">Guru Pengajar</option>
              <option value="Wali Kelas">Wali Kelas</option>
              <option value="Kepala Sekolah">Kepala Sekolah / Pimpinan</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Ukuran Kertas</label>
            <select v-model="selectedPaperSize" @change="applyPaperSize" class="bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2 text-xs font-bold text-emerald-800 focus:outline-none cursor-pointer">
              <option value="F4">📜 F4 / Folio (215mm x 330mm)</option>
              <option value="A4">📄 A4 (210mm x 297mm)</option>
            </select>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="exportExcelTeachers"
            class="px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 transition-colors flex items-center gap-2 shadow-xs cursor-pointer"
          >
            <Download class="w-4 h-4 text-emerald-600" />
            <span>Export Excel</span>
          </button>
          <button
            @click="triggerPrint"
            class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md cursor-pointer"
          >
            <Printer class="w-4 h-4 text-emerald-400" />
            <span>Cetak Dokumen</span>
          </button>
        </div>
      </div>

      <!-- Printable Teachers Document Area -->
      <div id="print-teachers-area" class="bg-white p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-6">
        <!-- Kop Surat Resmi Sekolah -->
        <div class="flex items-center gap-5 border-b-4 border-double border-slate-900 pb-4">
          <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
            <img v-if="appSettings?.app_logo" :src="getImageUrl(appSettings.app_logo)" class="w-full h-full object-contain" alt="Logo Sekolah" />
            <div v-else class="w-16 h-16 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-black text-xl">MTS</div>
          </div>
          <div class="text-center flex-1 pr-14">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest">{{ appSettings?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM' }}</h3>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 uppercase font-lexend tracking-tight mt-0.5">{{ appSettings?.app_name || 'MADRASAH TSANAWIYAH AL-HASANAH' }}</h1>
            <p class="text-xs text-slate-600 font-medium mt-1">{{ appSettings?.school_address || 'Jl. Raya Ciwidey No. 123, Kab. Bandung' }}</p>
            <p class="text-[11px] text-slate-500 font-mono mt-0.5">
              Telp: {{ appSettings?.school_phone || '(022) 1234567' }} &bull; Email: {{ appSettings?.school_email || 'info@mtsalhasanah.sch.id' }}
            </p>
          </div>
        </div>

        <!-- Document Title & Subtitle -->
        <div class="text-center space-y-1 py-2">
          <h2 class="text-base sm:text-lg font-black uppercase text-slate-900 font-lexend tracking-wider underline">
            DAFTAR REKAPITULASI DEWAN GURU & TENAGA KEPENDIDIKAN
          </h2>
          <p class="text-xs font-bold text-slate-600">
            TAHUN PELAJARAN {{ activeAcademicYear?.year || '2026/2027' }} &bull; STATUS: PENDIDIK & TENAGA KEPENDIDIKAN
          </p>
          <p class="text-[10px] text-slate-400 font-medium">Total: {{ filteredPrintTeachers.length }} Guru / Staf Terdaftar</p>
        </div>

        <!-- Teacher Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs font-inter border border-slate-300">
            <thead>
              <tr class="bg-slate-100 text-slate-900 font-black border-b border-slate-300 uppercase text-[10px] tracking-wider">
                <th class="p-2.5 w-10 text-center border border-slate-300">NO</th>
                <th class="p-2.5 w-36 text-center border border-slate-300">NIP / NUPTK</th>
                <th class="p-2.5 border border-slate-300">NAMA LENGKAP & GELAR</th>
                <th class="p-2.5 w-12 text-center border border-slate-300">L/P</th>
                <th class="p-2.5 w-32 border border-slate-300">JABATAN</th>
                <th class="p-2.5 border border-slate-300">MAPEL DIAMPU</th>
                <th class="p-2.5 w-32 text-center border border-slate-300">NO. WHATSAPP</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(tch, idx) in filteredPrintTeachers" :key="tch.id" class="border-b border-slate-200 hover:bg-slate-50/60">
                <td class="p-2 text-center font-bold text-slate-500 border border-slate-300">{{ idx + 1 }}</td>
                <td class="p-2 text-center font-mono font-bold text-slate-700 border border-slate-300 text-[11px]">
                  {{ tch.nip || '-' }}
                </td>
                <td class="p-2 font-bold text-slate-900 uppercase border border-slate-300">{{ tch.full_name }}</td>
                <td class="p-2 text-center font-black border border-slate-300" :class="tch.gender === 'L' ? 'text-blue-700' : 'text-pink-700'">
                  {{ tch.gender || '-' }}
                </td>
                <td class="p-2 font-semibold text-slate-700 border border-slate-300">
                  {{ tch.position || 'Guru Pengajar' }}
                </td>
                <td class="p-2 text-slate-700 border border-slate-300">
                  <span v-if="tch.subjects && tch.subjects.length > 0">
                    {{ tch.subjects.map(s => s.name || s).join(', ') }}
                  </span>
                  <span v-else class="text-slate-400 italic text-[11px]">Umum / Kelas</span>
                </td>
                <td class="p-2 text-center font-mono text-[11px] border border-slate-300">
                  {{ tch.phone || '-' }}
                </td>
              </tr>
              <tr v-if="filteredPrintTeachers.length === 0">
                <td colspan="7" class="p-8 text-center text-slate-400 font-medium">Tidak ada data guru yang cocok dengan filter.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Official Signature Block -->
        <div class="pt-8 grid grid-cols-2 text-center text-xs font-semibold text-slate-700">
          <div>
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala Madrasah / Sekolah</p>
            <div class="h-20"></div>
            <p class="font-bold underline uppercase">{{ appSettings?.principal_name || '............................................' }}</p>
            <p class="text-[10px] text-slate-500 font-mono">NIP: {{ appSettings?.principal_nip || '-' }}</p>
          </div>
          <div>
            <p>{{ getTodayDateFormatted() }}</p>
            <p class="font-bold">Kepala Tata Usaha / Kepegawaian</p>
            <div class="h-20"></div>
            <p class="font-bold underline uppercase">( ............................................ )</p>
            <p class="text-[10px] text-slate-500 font-mono">NIP: -</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 4: CETAK KARTU PELAJAR ==================== -->
    <div v-if="activeTab === 'card'" class="space-y-6">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
        <div class="flex flex-wrap items-center gap-4">
          <div>
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Target Cetak</label>
            <select v-model="cardPrintTarget" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
              <option value="class">Per Kelas</option>
              <option value="single">Per Siswa Satuan</option>
            </select>
          </div>

          <div v-if="cardPrintTarget === 'class'">
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Pilih Kelas</label>
            <select v-model="cardSelectedClass" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer">
              <option value="">Semua Kelas ({{ students.length }} Siswa)</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">Kelas {{ cls.name }}</option>
            </select>
          </div>

          <div v-if="cardPrintTarget === 'single'">
            <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Pilih Siswa</label>
            <select v-model="cardSelectedStudentId" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none cursor-pointer max-w-xs">
              <option v-for="st in students" :key="st.id" :value="st.id">{{ st.full_name }} ({{ st.nisn || st.nis || '-' }})</option>
            </select>
          </div>
        </div>

        <button
          @click="triggerPrint"
          class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md cursor-pointer"
        >
          <Printer class="w-4 h-4 text-emerald-400" />
          <span>Cetak Kartu</span>
        </button>
      </div>

      <!-- ID Cards Grid Print Area -->
      <div id="print-card-area" class="id-cards-container grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="st in getStudentsForCardPrint()"
          :key="st.id"
          class="id-card-item page-break bg-gradient-to-br from-emerald-800 via-teal-900 to-slate-900 text-white p-5 rounded-3xl shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[220px] border border-emerald-500/30"
        >
          <!-- Subtle Card BG Pattern -->
          <div class="id-card-pattern absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.1)_1px,transparent_1px)] [background-size:16px_16px] opacity-40 pointer-events-none"></div>

          <!-- Card Header -->
          <div class="id-card-header relative z-10 flex items-center gap-3 border-b border-white/20 pb-3">
            <div class="id-card-logo w-10 h-10 bg-white rounded-xl p-1 flex items-center justify-center flex-shrink-0">
              <img v-if="appSettings?.app_logo" :src="getImageUrl(appSettings.app_logo)" class="w-full h-full object-contain" alt="Logo" />
              <div v-else class="text-emerald-800 font-black text-xs">MTS</div>
            </div>
            <div>
              <h3 class="id-card-title text-xs font-black uppercase tracking-wider text-white font-lexend leading-tight">{{ appSettings?.app_name || 'MTs AL-HASANAH' }}</h3>
              <p class="id-card-subtitle text-[9px] text-emerald-300 font-bold uppercase tracking-widest">KARTU TANDA PELAJAR</p>
            </div>
          </div>

          <!-- Card Body -->
          <div class="id-card-body relative z-10 flex items-center gap-3 py-3">
            <div class="id-card-photo w-16 h-20 bg-white/20 rounded-xl overflow-hidden border border-white/30 flex-shrink-0 flex items-center justify-center">
              <img v-if="st.photo_url" :src="st.photo_url" class="w-full h-full object-cover" alt="Foto Siswa" />
              <span v-else class="id-card-photo-placeholder text-white font-black text-xl">{{ st.full_name?.charAt(0) || 'S' }}</span>
            </div>
            <div class="id-card-info min-w-0 space-y-1">
              <p class="id-card-name text-xs font-black text-white truncate leading-tight uppercase">{{ st.full_name }}</p>
              <p class="id-card-nisn text-[10px] text-emerald-200 font-mono">NISN: {{ st.nisn || '-' }}</p>
              <p class="id-card-class text-[10px] text-slate-300 font-medium">Kelas: <strong class="text-white">{{ st.classRoom?.name || st.class_name || '-' }}</strong></p>
              <p class="id-card-ttl text-[9px] text-slate-400 truncate">{{ st.birth_place || '-' }}, {{ st.birth_date || '-' }}</p>
            </div>
          </div>

          <!-- Card Footer -->
          <div class="id-card-footer relative z-10 border-t border-white/20 pt-2 flex items-center justify-between text-[8px] text-emerald-200/80 font-mono">
            <span>Berlaku Selama Menjadi Siswa</span>
            <span>T.A. {{ activeAcademicYear?.year || '2026/2027' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 5: CETAK KALENDER ==================== -->
    <div v-if="activeTab === 'calendar'" class="space-y-6">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
        <div>
          <h2 class="text-sm font-black text-slate-800 uppercase font-lexend">Kalender Akademik Sekolah</h2>
          <p class="text-xs text-slate-500 font-medium">Tampilan matriks semester 1 & 2 lengkap dengan agenda dan tanggal libur.</p>
        </div>

        <button
          @click="triggerPrint"
          class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md cursor-pointer"
        >
          <Printer class="w-4 h-4 text-emerald-400" />
          <span>Cetak Kalender</span>
        </button>
      </div>

      <div id="print-calendar-area" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-6">
        <!-- Kop Surat Resmi (Visible on Print & Screen) -->
        <div class="flex items-center gap-5 border-b-4 border-double border-slate-900 pb-4">
          <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
            <img v-if="appSettings?.app_logo" :src="getImageUrl(appSettings.app_logo)" class="w-full h-full object-contain" alt="Logo" />
            <div v-else class="w-14 h-14 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-lg">MTS</div>
          </div>
          <div class="text-center flex-1 pr-14">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest">{{ appSettings?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM' }}</h3>
            <h1 class="text-xl font-black text-slate-900 uppercase font-lexend tracking-tight mt-0.5">{{ appSettings?.app_name || 'MADRASAH TSANAWIYAH AL-HASANAH' }}</h1>
            <p class="text-xs text-slate-600 font-medium mt-0.5">{{ appSettings?.school_address || 'Jl. Raya Ciwidey No. 123, Kab. Bandung' }}</p>
            <p class="text-[10px] text-slate-500 font-mono">
              Telp: {{ appSettings?.school_phone || '(022) 1234567' }} &bull; Email: {{ appSettings?.school_email || 'info@mtsalhasanah.sch.id' }}
            </p>
          </div>
        </div>

        <div class="text-center space-y-1 py-1">
          <h2 class="text-base font-black uppercase text-slate-900 font-lexend tracking-wider underline">
            KALENDER AKADEMIK & AGENDA PENDIDIKAN
          </h2>
          <p class="text-xs font-bold text-slate-600">
            TAHUN PELAJARAN {{ activeAcademicYear?.year || '2026/2027' }}
          </p>
        </div>

        <!-- 12 Months Grid (4 Columns x 3 Rows) -->
        <div class="calendar-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
          <div v-for="month in months" :key="month.id" class="calendar-month-card border border-slate-200 rounded-2xl p-2.5 bg-white shadow-xs">
            <h4 class="calendar-month-header text-[11px] font-black text-slate-900 text-center uppercase tracking-wider bg-slate-100 rounded-lg py-1 mb-1.5 font-lexend">
              {{ month.name }} {{ getCalendarDataForMonth(month).year }}
            </h4>
            <table class="calendar-table w-full text-center table-fixed border-collapse text-[9.5px]">
              <thead>
                <tr class="border-b border-slate-200">
                  <th class="font-bold text-slate-500 pb-0.5">Sen</th>
                  <th class="font-bold text-slate-500 pb-0.5">Sel</th>
                  <th class="font-bold text-slate-500 pb-0.5">Rab</th>
                  <th class="font-bold text-slate-500 pb-0.5">Kam</th>
                  <th class="font-bold text-slate-500 pb-0.5">Jum</th>
                  <th class="font-bold text-slate-500 pb-0.5">Sab</th>
                  <th class="font-bold text-rose-600 pb-0.5">Min</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="weekIndex in 6" :key="'w-'+weekIndex">
                  <td v-for="dayIndex in 7" :key="'d-'+dayIndex" class="p-0.5">
                    <template v-if="getDateForCell(month, weekIndex, dayIndex)">
                      <div
                        class="calendar-date-cell p-0.5 rounded font-bold"
                        :class="[dayIndex === 7 ? 'text-rose-600' : 'text-slate-800']"
                        :style="getCellPrintStyle(getCalendarDataForMonth(month).year, month.id, getDateForCell(month, weekIndex, dayIndex))"
                      >
                        {{ getDateForCell(month, weekIndex, dayIndex) }}
                      </div>
                    </template>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Agenda Kegiatan & Hari Libur -->
        <div v-if="calendarEvents.length > 0" class="calendar-agenda-section border border-slate-200 rounded-2xl p-4 bg-slate-50/50 space-y-2 mt-4">
          <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider font-lexend border-b border-slate-200 pb-1.5 flex items-center gap-2">
            <span>📌 Agenda Kegiatan Sekolah & Hari Libur:</span>
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-[10px]">
            <div
              v-for="ev in calendarEvents"
              :key="ev.id"
              class="flex items-start gap-2 p-1.5 rounded-lg bg-white border border-slate-100 shadow-2xs"
            >
              <span
                class="w-2.5 h-2.5 rounded-full flex-shrink-0 mt-0.5"
                :style="{ backgroundColor: colorHexMap[ev.color] || '#10b981' }"
              ></span>
              <div class="min-w-0">
                <span class="font-bold text-slate-900 block truncate">{{ ev.title }}</span>
                <span class="text-slate-500 font-mono text-[9px]">{{ ev.start_date }}{{ ev.end_date && ev.end_date !== ev.start_date ? ' s/d ' + ev.end_date : '' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tanda Tangan Footer -->
        <div class="grid grid-cols-2 text-center text-xs font-semibold text-slate-700 pt-6">
          <div>
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala Madrasah / Sekolah</p>
            <div class="h-16"></div>
            <p class="font-bold underline uppercase">{{ appSettings?.principal_name || 'H. UMAR USMAN ALI, S.PD, S.PDI' }}</p>
            <p class="text-[10px] text-slate-500 font-mono">NIP: {{ appSettings?.principal_nip || '-' }}</p>
          </div>
          <div>
            <p>{{ getTodayDateFormatted() }}</p>
            <p class="font-bold">Waka Kurikulum / Akademik</p>
            <div class="h-16"></div>
            <p class="font-bold underline">( ............................................ )</p>
            <p class="text-[10px] text-slate-500 font-mono">NIP: -</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import * as XLSX from 'xlsx';
import {
  CalendarDays,
  GraduationCap,
  UserCheck,
  CreditCard,
  Calendar,
  Download,
  Printer,
} from 'lucide-vue-next';

const activeTab = ref('schedule');

// Settings & Dropdowns
const appSettings = ref({});
const classes = ref([]);
const students = ref([]);
const teachers = ref([]);
const schedules = ref([]);
const calendarEvents = ref([]);

// Schedule Print Controls
const schedulePrintMode = ref('single');
const selectedClassId = ref('');
const selectedPaperSize = ref('F4');

// Student Print Controls (NEW)
const studentFilterClass = ref('');
const studentFilterGender = ref('');

// Teacher Print Controls (NEW)
const teacherFilterPosition = ref('');

// Card Print Controls
const cardPrintTarget = ref('class');
const cardSelectedClass = ref('');
const cardSelectedStudentId = ref('');

const months = [
  { id: 7, num: '07', name: 'Juli' },
  { id: 8, num: '08', name: 'Agustus' },
  { id: 9, num: '09', name: 'September' },
  { id: 10, num: '10', name: 'Oktober' },
  { id: 11, num: '11', name: 'November' },
  { id: 12, num: '12', name: 'Desember' },
  { id: 1, num: '01', name: 'Januari' },
  { id: 2, num: '02', name: 'Februari' },
  { id: 3, num: '03', name: 'Maret' },
  { id: 4, num: '04', name: 'April' },
  { id: 5, num: '05', name: 'Mei' },
  { id: 6, num: '06', name: 'Juni' },
];

const colorHexMap = {
  indigo: '#6366f1',
  emerald: '#10b981',
  amber: '#f59e0b',
  rose: '#f43f5e',
  purple: '#8b5cf6',
  sky: '#0284c7',
};

const activeAcademicYear = computed(() => {
  return appSettings.value?.active_academic_year || { year: '2026/2027', semester: 'odd' };
});

const activeYaspinSlots = [
  { no: '0', start: '07.00', end: '07.30', isGeneral: true, title: 'UPACARA / TADARUS' },
  { no: '1', start: '07.30', end: '08.10', isSlot: true },
  { no: '2', start: '08.10', end: '08.50', isSlot: true },
  { no: '3', start: '08.50', end: '09.30', isSlot: true },
  { no: '4', start: '09.30', end: '10.10', isSlot: true },
  { no: '5', start: '10.10', end: '10.40', isBreak: true, title: 'ISTIRAHAT' },
  { no: '6', start: '10.40', end: '11.20', isSlot: true },
  { no: '7', start: '11.20', end: '12.00', isSlot: true },
  { no: '8', start: '12.00', end: '12.30', isGeneral: true, title: 'SHALAT DZUHUR BERJAMA\'AH' },
];

const getImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path;
};

const getClassName = (clsId) => {
  const c = classes.value.find(item => item.id == clsId);
  return c ? c.name : '-';
};

const getTargetClassesForSchedule = () => {
  if (schedulePrintMode.value === 'single' && selectedClassId.value) {
    const c = classes.value.find(item => item.id == selectedClassId.value);
    return c ? [c] : classes.value;
  }
  return classes.value;
};

const getSingleScheduleCell = (classId, day, slot) => {
  return schedules.value.find(s => {
    const isSameClass = (s.class_id == classId || s.class_id === null);
    const isSameDay = s.day?.toLowerCase() === day.toLowerCase();
    if (!isSameClass || !isSameDay) return false;
    if (!s.start_time || !s.end_time) return false;
    const sNorm = s.start_time.replace('.', ':').substring(0, 5);
    const slotStart = slot.start.replace('.', ':');
    return sNorm === slotStart;
  }) || null;
};

// Filtered Students for Print Tab
const filteredPrintStudents = computed(() => {
  let list = students.value;
  if (studentFilterClass.value) {
    list = list.filter(s => s.class_id == studentFilterClass.value);
  }
  if (studentFilterGender.value) {
    list = list.filter(s => s.gender === studentFilterGender.value);
  }
  return list;
});

// Filtered Teachers for Print Tab
const filteredPrintTeachers = computed(() => {
  let list = teachers.value;
  if (teacherFilterPosition.value) {
    list = list.filter(t => t.position?.toLowerCase().includes(teacherFilterPosition.value.toLowerCase()));
  }
  return list;
});

const getStudentsForCardPrint = () => {
  if (cardPrintTarget.value === 'single') {
    const found = students.value.find(s => s.id == cardSelectedStudentId.value);
    return found ? [found] : students.value.slice(0, 1);
  }
  if (cardSelectedClass.value) {
    return students.value.filter(s => s.class_id == cardSelectedClass.value);
  }
  return students.value;
};

const getTodayDateFormatted = () => {
  const d = new Date();
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return `${appSettings.value?.school_city || 'Bandung'}, ${d.toLocaleDateString('id-ID', options)}`;
};

const formatDateIndo = (dStr) => {
  if (!dStr) return '-';
  const d = new Date(dStr);
  if (isNaN(d.getTime())) return dStr;
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const applyPaperSize = () => {
  const sizeMap = {
    'F4': { w: '215mm', h: '330mm' },
    'A4': { w: '210mm', h: '297mm' },
    'Letter': { w: '216mm', h: '279mm' },
  };
  const size = sizeMap[selectedPaperSize.value] || sizeMap['F4'];
  document.documentElement.style.setProperty('--paper-size-width', size.w);
  document.documentElement.style.setProperty('--paper-size-height', size.h);
};

const triggerPrint = () => {
  // SPECIAL HANDLING: ID Cards Grid Printing (Exact 86x54mm Card Dimensions & Color Preservation)
  if (activeTab.value === 'card') {
    const printElem = document.getElementById('print-card-area');
    if (!printElem) {
      window.print();
      return;
    }
    const content = printElem.innerHTML;
    const printWindow = window.open('', '_blank', 'width=1100,height=800');
    if (!printWindow) {
      window.print();
      return;
    }

    printWindow.document.open();
    printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Cetak Kartu Tanda Pelajar - ${appSettings.value?.app_name || 'Sekolah'}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 12mm 10mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: #ffffff !important;
      color: #0f172a;
      padding: 5px;
    }
    .print-instructions {
      text-align: center;
      font-size: 10px;
      font-weight: bold;
      color: #64748b;
      margin-bottom: 16px;
      padding-bottom: 8px;
      border-bottom: 1px dashed #cbd5e1;
    }
    .id-cards-container {
      display: grid !important;
      grid-template-columns: repeat(2, 86mm) !important;
      gap: 8mm 6mm !important;
      justify-content: center !important;
      padding: 0 !important;
    }
    .id-card-item {
      width: 86mm !important;
      height: 54mm !important;
      max-width: 86mm !important;
      max-height: 54mm !important;
      box-sizing: border-box !important;
      background: linear-gradient(135deg, #065f46 0%, #064e3b 60%, #0f172a 100%) !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      color: #ffffff !important;
      border-radius: 12px !important;
      border: 1px solid rgba(16, 185, 129, 0.5) !important;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15) !important;
      padding: 8px 12px !important;
      display: flex !important;
      flex-direction: column !important;
      justify-content: space-between !important;
      position: relative !important;
      overflow: hidden !important;
      page-break-inside: avoid !important;
      break-inside: avoid !important;
    }
    .id-card-pattern {
      position: absolute !important;
      inset: 0 !important;
      background-image: radial-gradient(rgba(255,255,255,0.12) 1px, transparent 1px) !important;
      background-size: 10px 10px !important;
      pointer-events: none !important;
    }
    .id-card-header {
      position: relative !important;
      z-index: 10 !important;
      display: flex !important;
      align-items: center !important;
      gap: 8px !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.25) !important;
      padding-bottom: 5px !important;
    }
    .id-card-logo {
      width: 28px !important;
      height: 28px !important;
      background: #ffffff !important;
      border-radius: 6px !important;
      padding: 2px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      flex-shrink: 0 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .id-card-logo img {
      width: 100% !important;
      height: 100% !important;
      object-fit: contain !important;
    }
    .id-card-title {
      font-size: 9px !important;
      font-weight: 900 !important;
      text-transform: uppercase !important;
      color: #ffffff !important;
      line-height: 1.1 !important;
      font-family: 'Lexend', sans-serif, system-ui !important;
    }
    .id-card-subtitle {
      font-size: 7px !important;
      font-weight: 800 !important;
      color: #6ee7b7 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
    }
    .id-card-body {
      position: relative !important;
      z-index: 10 !important;
      display: flex !important;
      align-items: center !important;
      gap: 10px !important;
      padding: 3px 0 !important;
      flex: 1 !important;
    }
    .id-card-photo {
      width: 36px !important;
      height: 46px !important;
      border-radius: 6px !important;
      background: rgba(255, 255, 255, 0.18) !important;
      border: 1px solid rgba(255, 255, 255, 0.35) !important;
      overflow: hidden !important;
      flex-shrink: 0 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .id-card-photo img {
      width: 100% !important;
      height: 100% !important;
      object-fit: cover !important;
    }
    .id-card-photo-placeholder {
      color: #ffffff !important;
      font-weight: 900 !important;
      font-size: 15px !important;
    }
    .id-card-info {
      flex: 1 !important;
      min-width: 0 !important;
      line-height: 1.25 !important;
    }
    .id-card-name {
      font-size: 9.5px !important;
      font-weight: 900 !important;
      color: #ffffff !important;
      text-transform: uppercase !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }
    .id-card-nisn {
      font-size: 8px !important;
      font-family: ui-monospace, SFMono-Regular, monospace !important;
      color: #a7f3d0 !important;
      margin-top: 1px !important;
    }
    .id-card-class {
      font-size: 7.5px !important;
      color: #cbd5e1 !important;
      margin-top: 1px !important;
    }
    .id-card-class strong {
      color: #ffffff !important;
      font-weight: 800 !important;
    }
    .id-card-ttl {
      font-size: 7px !important;
      color: #94a3b8 !important;
      margin-top: 1px !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }
    .id-card-footer {
      position: relative !important;
      z-index: 10 !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      border-top: 1px solid rgba(255, 255, 255, 0.2) !important;
      padding-top: 4px !important;
      font-size: 6.5px !important;
      font-family: ui-monospace, SFMono-Regular, monospace !important;
      color: rgba(167, 243, 208, 0.9) !important;
    }
  </style>
</head>
<body>
  <div class="print-instructions">
    ✂️ PETUNJUK CETAK KARTU: Gunakan kertas tebal / Glossy / Kertas Foto, skala 100%, lalu gunting sesuai ukuran standar ID Card (86 x 54 mm).
  </div>
  <div class="id-cards-container">
    ${content}
  </div>
</body>
</html>`);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 400);
    return;
  }

  // SPECIAL HANDLING: Kalender Akademik (4 Columns x 3 Rows Matrix + Agenda List + Signature)
  if (activeTab.value === 'calendar') {
    const printElem = document.getElementById('print-calendar-area');
    if (!printElem) {
      window.print();
      return;
    }
    const content = printElem.innerHTML;
    const printWindow = window.open('', '_blank', 'width=1100,height=800');
    if (!printWindow) {
      window.print();
      return;
    }

    const paperSizeName = selectedPaperSize.value === 'F4' ? '215mm 330mm' : (selectedPaperSize.value === 'A4' ? 'A4' : 'letter');

    printWindow.document.open();
    printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kalender Akademik - ${appSettings.value?.app_name || 'Sekolah'}</title>
  <style>
    @page {
      size: ${paperSizeName} landscape;
      margin: 8mm 10mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: #ffffff !important;
      color: #0f172a;
      padding: 6px;
      font-size: 11px;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .font-black { font-weight: 900; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .uppercase { text-transform: uppercase; }
    .underline { text-decoration: underline; }
    .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .flex { display: flex; align-items: center; }
    .flex-1 { flex: 1 1 0%; }
    .justify-between { justify-content: space-between; }
    .justify-center { justify-content: center; }
    .items-center { align-items: center; }
    .items-start { align-items: flex-start; }
    .gap-2 { gap: 8px; }
    .gap-3 { gap: 12px; }
    .gap-5 { gap: 20px; }
    .space-y-1 > * + * { margin-top: 4px; }
    .space-y-2 > * + * { margin-top: 8px; }
    .space-y-6 > * + * { margin-top: 16px; }

    .border-b-4 { border-bottom: 4px solid #0f172a; }
    .border-double { border-bottom-style: double; }
    .pb-4 { padding-bottom: 10px; }
    .py-1 { padding-top: 4px; padding-bottom: 4px; }
    .pt-6 { padding-top: 18px; }
    .pr-14 { padding-right: 45px; }

    .w-14 { width: 56px; }
    .h-14 { height: 56px; }
    .w-16 { width: 64px; }
    .h-16 { height: 64px; }
    .h-16 { height: 50px; }
    .object-contain { object-fit: contain; }
    img { max-width: 100%; height: auto; }

    /* Calendar 12-Month Matrix */
    .calendar-grid {
      display: grid !important;
      grid-template-columns: repeat(4, 1fr) !important;
      gap: 10px !important;
      margin-top: 10px !important;
    }
    .calendar-month-card {
      border: 1px solid #94a3b8 !important;
      border-radius: 8px !important;
      padding: 6px 8px !important;
      background: #ffffff !important;
      box-sizing: border-box !important;
    }
    .calendar-month-header {
      font-size: 10.5px !important;
      font-weight: 900 !important;
      text-align: center !important;
      text-transform: uppercase !important;
      background-color: #f1f5f9 !important;
      border-radius: 4px !important;
      padding: 3px 0 !important;
      margin-bottom: 4px !important;
      color: #0f172a !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .calendar-table {
      width: 100% !important;
      table-layout: fixed !important;
      border-collapse: collapse !important;
      font-size: 9px !important;
      text-align: center !important;
    }
    .calendar-table th {
      font-weight: 800 !important;
      color: #475569 !important;
      padding-bottom: 3px !important;
      border: none !important;
      background: transparent !important;
    }
    .calendar-table td {
      padding: 1.5px !important;
      border: none !important;
    }
    .calendar-date-cell {
      padding: 2px 0 !important;
      border-radius: 4px !important;
      font-weight: bold !important;
      font-size: 8.5px !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .text-rose-600 {
      color: #e11d48 !important;
    }
    .text-slate-800 {
      color: #1e293b !important;
    }

    /* Agenda Section */
    .calendar-agenda-section {
      border: 1px solid #94a3b8 !important;
      border-radius: 8px !important;
      padding: 8px 12px !important;
      background-color: #f8fafc !important;
      margin-top: 12px !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }
    .calendar-agenda-section h3 {
      font-size: 10px !important;
      font-weight: 900 !important;
      text-transform: uppercase !important;
      border-bottom: 1px solid #cbd5e1 !important;
      padding-bottom: 4px !important;
      margin-bottom: 6px !important;
      color: #0f172a !important;
    }
    .calendar-agenda-section .grid {
      display: grid !important;
      grid-template-columns: repeat(3, 1fr) !important;
      gap: 6px 12px !important;
    }
    .calendar-agenda-section .rounded-full {
      border-radius: 9999px !important;
      display: inline-block !important;
    }

    /* Signatures */
    .grid-cols-2 {
      display: grid !important;
      grid-template-columns: 1fr 1fr !important;
    }
  </style>
</head>
<body>
  ${content}
</body>
</html>`);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 400);
    return;
  }

  let targetId = 'print-schedule-area';
  let title = 'Jadwal Pelajaran';
  let isLandscape = true;

  if (activeTab.value === 'schedule') {
    targetId = 'print-schedule-area';
    title = 'Jadwal Pelajaran';
    isLandscape = true;
  } else if (activeTab.value === 'students') {
    targetId = 'print-students-area';
    title = 'Rekapitulasi Data Siswa';
    isLandscape = true;
  } else if (activeTab.value === 'teachers') {
    targetId = 'print-teachers-area';
    title = 'Rekapitulasi Data Dewan Guru';
    isLandscape = true;
  }

  const printElem = document.getElementById(targetId);
  if (!printElem) {
    window.print();
    return;
  }

  const content = printElem.innerHTML;
  const printWindow = window.open('', '_blank', 'width=1100,height=800');
  if (!printWindow) {
    window.print();
    return;
  }

  const orientation = isLandscape ? 'landscape' : 'portrait';
  const paperSizeName = selectedPaperSize.value === 'F4' ? '215mm 330mm' : (selectedPaperSize.value === 'A4' ? 'A4' : 'letter');

  printWindow.document.open();
  printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>${title} - ${appSettings.value?.app_name || 'Sekolah'}</title>
  <style>
    @page {
      size: ${paperSizeName} ${orientation};
      margin: 8mm 10mm 8mm 10mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: #ffffff !important;
      color: #0f172a;
      padding: 10px;
      font-size: 11px;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .font-semibold { font-weight: 600; }
    .font-black { font-weight: 900; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .uppercase { text-transform: uppercase; }
    .underline { text-decoration: underline; }
    
    .flex { display: flex; align-items: center; }
    .flex-1 { flex: 1 1 0%; }
    .justify-between { justify-content: space-between; }
    .justify-center { justify-content: center; }
    .items-center { align-items: center; }
    .gap-2 { gap: 8px; }
    .gap-3 { gap: 12px; }
    .gap-4 { gap: 16px; }
    .gap-5 { gap: 20px; }
    .grid { display: grid; }
    .grid-cols-2 { grid-template-columns: 1fr 1fr; }
    .space-y-1 > * + * { margin-top: 4px; }
    .space-y-2 > * + * { margin-top: 8px; }
    .space-y-6 > * + * { margin-top: 20px; }
    .space-y-12 > * + * { margin-top: 40px; }
    
    .border-b-2 { border-bottom: 2px solid #0f172a; }
    .border-b-4 { border-bottom: 4px solid #0f172a; }
    .border-double { border-bottom-style: double; }
    .pb-4 { padding-bottom: 12px; }
    .pt-8 { padding-top: 24px; }
    .py-2 { padding-top: 8px; padding-bottom: 8px; }
    .pr-14 { padding-right: 45px; }
    
    .w-14 { width: 56px; }
    .h-14 { height: 56px; }
    .w-16 { width: 64px; }
    .h-16 { height: 64px; }
    .w-20 { width: 80px; }
    .h-20 { height: 80px; }
    .object-contain { object-fit: contain; }
    img { max-width: 100%; height: auto; }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      font-size: 10px;
    }
    table, th, td {
      border: 1px solid #334155;
    }
    th {
      background-color: #f1f5f9 !important;
      color: #0f172a;
      font-weight: 800;
      padding: 6px 8px;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    td {
      padding: 5px 7px;
      color: #1e293b;
    }
    
    .bg-emerald-600 { background-color: #059669 !important; color: white !important; -webkit-print-color-adjust: exact; }
    .bg-emerald-100 { background-color: #d1fae5 !important; color: #065f46 !important; -webkit-print-color-adjust: exact; }
    .bg-amber-100 { background-color: #fef3c7 !important; color: #92400e !important; -webkit-print-color-adjust: exact; }
    .bg-slate-50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; }
    .bg-slate-100 { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
    .text-blue-700 { color: #1d4ed8; font-weight: bold; }
    .text-pink-700 { color: #be185d; font-weight: bold; }
    
    .page-break {
      page-break-after: always;
      break-after: page;
    }
  </style>
</head>
<body>
  ${content}
</body>
</html>`);
  printWindow.document.close();

  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 400);
};

const exportExcelSchedule = () => {
  const exportData = [];
  const targetClasses = getTargetClassesForSchedule();
  
  targetClasses.forEach(cls => {
    const clsSchedules = schedules.value.filter(s => s.class_id == cls.id || s.class_id === null);
    clsSchedules.forEach(item => {
      exportData.push({
        'Kelas': cls.name,
        'Hari': item.day,
        'Jam': `${item.start_time} - ${item.end_time}`,
        'Pelajaran/Kegiatan': item.is_activity ? item.activity_name : item.subject?.name,
        'Pengajar': item.is_activity ? '-' : item.teacher?.full_name || '-',
      });
    });
  });

  const ws = XLSX.utils.json_to_sheet(exportData);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Jadwal_Pelajaran');
  XLSX.writeFile(wb, `Jadwal_Pelajaran_${new Date().toISOString().substring(0, 10)}.xlsx`);
};

const exportExcelStudents = () => {
  const rows = [
    ['DAFTAR REKAPITULASI PESERTA DIDIK'],
    [`Sekolah: ${appSettings.value?.app_name || 'MTs Al-Hasanah'}`],
    [`Tahun Ajaran: ${activeAcademicYear.value?.year || '2026/2027'}`],
    [`Tanggal Cetak: ${getTodayDateFormatted()}`],
    [],
    ['NO', 'NISN', 'NIS', 'NAMA LENGKAP SISWA', 'L/P', 'KELAS', 'TEMPAT LAHIR', 'TANGGAL LAHIR', 'NAMA ORANG TUA/WALI', 'NO. HP ORTU']
  ];

  filteredPrintStudents.value.forEach((st, idx) => {
    rows.push([
      idx + 1,
      st.nisn || '-',
      st.nis || '-',
      st.full_name,
      st.gender || '-',
      st.classRoom?.name || st.class_name || '-',
      st.birth_place || '-',
      st.birth_date || '-',
      st.father_name || st.mother_name || st.guardian_name || '-',
      st.parent_phone || '-'
    ]);
  });

  const ws = XLSX.utils.aoa_to_sheet(rows);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Data Siswa');
  XLSX.writeFile(wb, `Data_Siswa_${new Date().toISOString().substring(0, 10)}.xlsx`);
};

const exportExcelTeachers = () => {
  const rows = [
    ['DAFTAR REKAPITULASI DEWAN GURU & TENAGA KEPENDIDIKAN'],
    [`Sekolah: ${appSettings.value?.app_name || 'MTs Al-Hasanah'}`],
    [`Tahun Ajaran: ${activeAcademicYear.value?.year || '2026/2027'}`],
    [`Tanggal Cetak: ${getTodayDateFormatted()}`],
    [],
    ['NO', 'NIP / NUPTK', 'NAMA LENGKAP & GELAR', 'L/P', 'JABATAN', 'MAPEL DIAMPU', 'NO. WHATSAPP']
  ];

  filteredPrintTeachers.value.forEach((tch, idx) => {
    const subjectsStr = (tch.subjects && tch.subjects.length > 0)
      ? tch.subjects.map(s => s.name || s).join(', ')
      : 'Umum / Kelas';

    rows.push([
      idx + 1,
      tch.nip || '-',
      tch.full_name,
      tch.gender || '-',
      tch.position || 'Guru Pengajar',
      subjectsStr,
      tch.phone || '-'
    ]);
  });

  const ws = XLSX.utils.aoa_to_sheet(rows);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Data Guru');
  XLSX.writeFile(wb, `Data_Guru_${new Date().toISOString().substring(0, 10)}.xlsx`);
};

// Calendar Helpers
const getCalendarDataForMonth = (monthData) => {
  const currentMonthIdx = new Date().getMonth();
  const currentYear = new Date().getFullYear();
  
  let targetYear = currentYear;
  if (currentMonthIdx < 6) {
    if (monthData.id >= 7) targetYear = currentYear - 1;
    else targetYear = currentYear;
  } else {
    if (monthData.id >= 7) targetYear = currentYear;
    else targetYear = currentYear + 1;
  }
  
  const daysInMonth = new Date(targetYear, monthData.id, 0).getDate();
  const firstDayObj = new Date(targetYear, monthData.id - 1, 1).getDay();
  const firstDay = firstDayObj === 0 ? 6 : firstDayObj - 1; // 0 = Senin, 6 = Minggu
  
  return { year: targetYear, daysInMonth, firstDay };
};

const getDateForCell = (monthData, weekIndex, dayIndex) => {
  const data = getCalendarDataForMonth(monthData);
  const cellIndex = ((weekIndex - 1) * 7) + (dayIndex - 1);
  const date = cellIndex - data.firstDay + 1;
  
  if (date > 0 && date <= data.daysInMonth) {
    return date;
  }
  return null;
};

const getEventsForDate = (dateStr) => {
  return calendarEvents.value.filter(ev => {
    const start = ev.start_date || ev.date;
    const end = ev.end_date || start;
    return dateStr >= start && dateStr <= end;
  });
};

const getCellPrintStyle = (year, monthNum, date) => {
  const dateStr = `${year}-${String(monthNum).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
  const events = getEventsForDate(dateStr);
  
  if (!events || events.length === 0) return { backgroundColor: '#ffffff', color: '#334155' };
  
  const hex = colorHexMap[events[0].color] || '#10b981';
  return { backgroundColor: hex + '20', color: hex, border: `1px solid ${hex}` };
};

onMounted(async () => {
  try {
    const [settRes, clsRes, stdRes, schRes, calRes, tchRes] = await Promise.all([
      api.get('/settings').catch(() => null),
      api.get('admin/classes').catch(() => null),
      api.get('admin/students?per_page=999').catch(() => null),
      api.get('admin/schedules').catch(() => null),
      api.get('admin/calendar-events?per_page=1000').catch(() => null),
      api.get('admin/teachers?per_page=999').catch(() => null),
    ]);

    if (settRes?.data) appSettings.value = settRes.data;
    classes.value = clsRes?.data?.data || clsRes?.data || [];
    
    const stdList = stdRes?.data?.data || stdRes?.data || [];
    students.value = Array.isArray(stdList) ? stdList : [];

    const tchList = tchRes?.data?.data || tchRes?.data || [];
    teachers.value = Array.isArray(tchList) ? tchList : [];

    if (students.value.length > 0) {
      cardSelectedStudentId.value = students.value[0].id;
    }

    schedules.value = schRes?.data || [];
    calendarEvents.value = calRes?.data?.data || calRes?.data || [];
  } catch (err) {
    console.error('Error initializing print center:', err);
  }
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }

@media print {
  .no-print {
    display: none !important;
  }
  .page-break {
    page-break-after: always;
    break-after: page;
  }
  body {
    background: white !important;
  }
  .print-container {
    padding: 0 !important;
    margin: 0 !important;
  }
  #print-schedule-area,
  #print-students-area,
  #print-teachers-area,
  #print-card-area,
  #print-calendar-area {
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
  }
}
</style>
