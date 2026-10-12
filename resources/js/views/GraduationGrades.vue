<template>
  <div class="space-y-4 sm:space-y-6 font-inter print-container pb-12">
    <!-- TOP HEADER (No Print) -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] p-4 sm:p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
      <div class="flex items-start sm:items-center gap-3">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-indigo-600 via-emerald-600 to-teal-700 text-white flex items-center justify-center shadow-lg shadow-emerald-600/20 flex-shrink-0 mt-0.5 sm:mt-0">
          <GraduationCap class="w-5 h-5 sm:w-6 sm:h-6" />
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <h1 class="text-base sm:text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Rekap Nilai Ijazah & PDUM (Kelas 9)</h1>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
              Semester 1 s.d. 6 + Ujian
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
              Acuan PDUM Kemenag
            </span>
          </div>
          <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 font-medium leading-relaxed">
            Pengelolaan nilai rapor 6 semester, nilai ujian (tulis & praktik), pembobotan nilai akhir, cetak transkrip ijazah, dan ekspor data PDUM.
          </p>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
        <button
          type="button"
          @click="openSettingsModal"
          class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer shadow-2xs"
          title="Pengaturan Bobot Rapor, Ujian, & Mapel Praktik"
        >
          <Sliders class="w-4 h-4 text-slate-500" />
          <span>Atur Bobot</span>
        </button>

        <button
          type="button"
          @click="openAddSemesterGradesModal"
          class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5 cursor-pointer"
          title="Input & Tambah Nilai Semester 1 s.d. 6 atau Ujian"
        >
          <PlusCircle class="w-4 h-4" />
          <span>+ Tambah Nilai Semester</span>
        </button>

        <button
          type="button"
          @click="handleExportPdum"
          class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 text-xs font-bold rounded-xl border border-indigo-200 transition-colors flex items-center gap-1.5 cursor-pointer shadow-2xs"
          title="Unduh File Format PDUM Madrasah (Excel / CSV)"
        >
          <Download class="w-4 h-4 text-indigo-600" />
          <span>Ekspor PDUM</span>
        </button>

        <button
          type="button"
          v-if="activeTab === 'subject'"
          @click="saveCurrentGrades"
          :disabled="isSaving || !hasUnsavedChanges"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:shadow-none"
        >
          <Save class="w-4 h-4" />
          <span>{{ isSaving ? 'Menyimpan...' : (hasUnsavedChanges ? 'Simpan Nilai' : 'Tersimpan') }}</span>
        </button>
      </div>
    </div>

    <!-- FILTER & TABS BAR (No Print) -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
      <!-- Mode Tabs -->
      <div class="flex items-center gap-2">
        <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl border border-slate-200/60 shadow-2xs">
          <button
            type="button"
            @click="activeTab = 'subject'"
            :class="activeTab === 'subject' ? 'bg-white text-emerald-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <Table class="w-3.5 h-3.5" />
            <span>Input Per Mapel</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'transcripts'"
            :class="activeTab === 'transcripts' ? 'bg-white text-indigo-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <BookOpen class="w-3.5 h-3.5" />
            <span>Buku Transkrip Siswa</span>
          </button>
        </div>
      </div>

      <!-- Filters: Class & Subject (if in subject mode) -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Class filter -->
        <div class="flex items-center gap-2">
          <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Rombel:</label>
          <select
            v-model="selectedClassId"
            @change="fetchData"
            class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 cursor-pointer"
          >
            <option value="">Semua Rombel Kelas 9</option>
            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
              Kelas {{ cls.name }}
            </option>
          </select>
        </div>

        <!-- Subject filter (only for subject tab) -->
        <div v-if="activeTab === 'subject'" class="flex items-center gap-2">
          <label class="text-[11px] font-black text-slate-500 uppercase tracking-wider">Mata Pelajaran:</label>
          <select
            v-model="selectedSubjectId"
            @change="onSubjectChange"
            class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 cursor-pointer max-w-[200px] sm:max-w-[280px]"
          >
            <option v-for="sub in subjects" :key="sub.id" :value="sub.id">
              {{ sub.name }} {{ isSubjectPractical(sub.id) ? '🔬 (Ada Praktik)' : '' }}
            </option>
          </select>
        </div>

        <!-- Active Weight summary badge -->
        <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600">
          <span class="text-slate-400">Bobot:</span>
          <span class="font-bold text-emerald-700">Rapor {{ settings?.report_weight || 60 }}%</span>
          <span class="text-slate-300">&bull;</span>
          <span class="font-bold text-indigo-700">Ujian {{ settings?.exam_weight || 40 }}%</span>
        </div>
      </div>
    </div>

    <!-- TAB 1: SPREADSHEET INPUT PER MAPEL -->
    <div v-if="activeTab === 'subject'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden no-print">
      <!-- Subject Header Card -->
      <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
            <BookOpenCheck class="w-5 h-5 text-emerald-700" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-sm sm:text-base font-black text-slate-800">{{ currentSubject?.name || 'Mata Pelajaran' }}</h2>
              <span
                v-if="isCurrentSubjectPractical"
                class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200"
              >
                🔬 Ujian Praktik Aktif ({{ settings?.practice_weight_default || 50 }}%)
              </span>
              <span v-else class="px-2 py-0.5 rounded text-[10px] font-bold text-slate-400 bg-slate-100 border border-slate-200">
                Ujian Teori Saja
              </span>
            </div>
            <p class="text-[11px] text-slate-500 font-medium">
              Total {{ students.length }} siswa kelas 9 &bull; Nilai R-Rapor, Nilai Ujian, dan Nilai Ijazah dihitung otomatis realtime.
            </p>
          </div>
        </div>

        <div v-if="hasUnsavedChanges" class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold flex items-center gap-1.5 animate-pulse">
          <AlertCircle class="w-3.5 h-3.5 text-amber-600" />
          <span>Ada perubahan belum disimpan!</span>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="p-12 text-center text-slate-500 font-medium space-y-2">
        <RefreshCw class="w-8 h-8 animate-spin mx-auto text-emerald-600 mb-2" />
        <p>Memuat rekap data nilai siswa...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="students.length === 0" class="p-12 text-center text-slate-400 space-y-2">
        <Users class="w-10 h-10 mx-auto text-slate-300" />
        <p class="font-bold text-slate-600">Belum Ada Siswa di Kelas 9</p>
        <p class="text-xs max-w-md mx-auto text-slate-400">
          Saat ini belum ada data siswa yang terdaftar di rombel kelas 9. Silakan tetapkan siswa ke kelas 9 di menu Data Master Siswa.
        </p>
      </div>

      <!-- Table Matrix -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-black uppercase tracking-wider text-slate-600 text-center">
              <th rowspan="2" class="py-3 px-3 w-10 text-center border-r border-slate-200/60">No</th>
              <th rowspan="2" class="py-3 px-3 text-left min-w-[200px] border-r border-slate-200/60">Nama Siswa & NISN</th>
              <th rowspan="2" class="py-3 px-2 w-20 border-r border-slate-200/60">Kelas</th>
              <th colspan="7" class="py-2 px-2 bg-emerald-50/60 text-emerald-800 border-r border-slate-200/60">
                Nilai Rapor (Semester 1 s.d. 6)
              </th>
              <th :colspan="isCurrentSubjectPractical ? 3 : 2" class="py-2 px-2 bg-indigo-50/60 text-indigo-800 border-r border-slate-200/60">
                Nilai Ujian Madrasah
              </th>
              <th rowspan="2" class="py-3 px-3 w-24 bg-amber-50/80 text-amber-900 border-l border-amber-200">
                Nilai Ijazah (PDUM)
              </th>
            </tr>
            <tr class="bg-slate-50 text-[10px] font-bold text-slate-500 text-center border-b border-slate-200/80">
              <th class="py-2 px-1.5 w-14 bg-emerald-50/30">Sem 1</th>
              <th class="py-2 px-1.5 w-14 bg-emerald-50/30">Sem 2</th>
              <th class="py-2 px-1.5 w-14 bg-emerald-50/30">Sem 3</th>
              <th class="py-2 px-1.5 w-14 bg-emerald-50/30">Sem 4</th>
              <th class="py-2 px-1.5 w-14 bg-emerald-50/30">Sem 5</th>
              <th class="py-2 px-1.5 w-14 bg-emerald-50/30">Sem 6</th>
              <th class="py-2 px-1.5 w-16 bg-emerald-100/60 text-emerald-900 font-black border-r border-slate-200/60">R-Rapor</th>

              <th class="py-2 px-1.5 w-16 bg-indigo-50/30">Tulis</th>
              <th v-if="isCurrentSubjectPractical" class="py-2 px-1.5 w-16 bg-purple-50/30 text-purple-900">Praktik</th>
              <th class="py-2 px-1.5 w-16 bg-indigo-100/60 text-indigo-900 font-black border-r border-slate-200/60">N-Ujian</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(stu, idx) in students"
              :key="stu.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <td class="py-2.5 px-3 text-center text-slate-400 font-mono font-bold border-r border-slate-100">
                {{ idx + 1 }}
              </td>
              <td class="py-2.5 px-3 border-r border-slate-100">
                <div class="font-bold text-slate-800 text-[13px] leading-tight">{{ stu.full_name }}</div>
                <div class="text-[10px] text-slate-400 font-mono mt-0.5">NISN: {{ stu.nisn || '-' }} &bull; NIS: {{ stu.nis || '-' }}</div>
              </td>
              <td class="py-2.5 px-2 text-center font-bold text-slate-600 border-r border-slate-100">
                {{ stu.class_name }}
              </td>

              <!-- Sem 1 to 6 Input Fields -->
              <td class="p-1 bg-emerald-50/10">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).sem_1"
                  @input="recalculateStudent(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-slate-200 rounded text-xs font-semibold focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                  placeholder="-"
                />
              </td>
              <td class="p-1 bg-emerald-50/10">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).sem_2"
                  @input="recalculateStudent(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-slate-200 rounded text-xs font-semibold focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                  placeholder="-"
                />
              </td>
              <td class="p-1 bg-emerald-50/10">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).sem_3"
                  @input="recalculateStudent(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-slate-200 rounded text-xs font-semibold focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                  placeholder="-"
                />
              </td>
              <td class="p-1 bg-emerald-50/10">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).sem_4"
                  @input="recalculateStudent(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-slate-200 rounded text-xs font-semibold focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                  placeholder="-"
                />
              </td>
              <td class="p-1 bg-emerald-50/10">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).sem_5"
                  @input="recalculateStudent(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-slate-200 rounded text-xs font-semibold focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                  placeholder="-"
                />
              </td>
              <td class="p-1 bg-emerald-50/10">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).sem_6"
                  @input="recalculateStudent(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-slate-200 rounded text-xs font-semibold focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                  placeholder="-"
                />
              </td>

              <!-- Rata-rata Rapor (Calculated) -->
              <td class="py-2.5 px-2 text-center font-black font-mono text-emerald-800 bg-emerald-50/40 border-r border-slate-100">
                {{ formatScore(getStudentRecord(stu.id).avg_semester) }}
              </td>

              <!-- Ujian Tulis -->
              <td class="p-1 bg-indigo-50/10">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).exam_theory"
                  @input="recalculateStudent(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-slate-200 rounded text-xs font-semibold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  placeholder="-"
                />
              </td>

              <!-- Ujian Praktik (if applicable) -->
              <td v-if="isCurrentSubjectPractical" class="p-1 bg-purple-50/10">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).exam_practice"
                  @input="recalculateStudent(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-purple-200 rounded text-xs font-semibold focus:border-purple-500 focus:ring-1 focus:ring-purple-500 focus:outline-none"
                  placeholder="-"
                />
              </td>

              <!-- Nilai Ujian (Calculated) -->
              <td class="py-2.5 px-2 text-center font-black font-mono text-indigo-800 bg-indigo-50/40 border-r border-slate-100">
                {{ formatScore(getStudentRecord(stu.id).final_exam_score) }}
              </td>

              <!-- Nilai Ijazah (Calculated) -->
              <td class="py-2.5 px-2 text-center font-black font-mono text-amber-900 bg-amber-50/70 border-l border-amber-100">
                <span class="px-2 py-0.5 rounded bg-amber-100/80 border border-amber-200">
                  {{ formatScore(getStudentRecord(stu.id).final_grade) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: TRANSKRIP SISWA (BUKU NILAI KOLEKTIF) -->
    <div v-if="activeTab === 'transcripts'" class="space-y-4 no-print">
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h2 class="text-sm sm:text-base font-black text-slate-800">Daftar Transkrip Nilai Siswa Kelas 9</h2>
          <p class="text-[11px] text-slate-500">
            Klik tombol "Lihat Transkrip" untuk melihat rincian seluruh mata pelajaran dan mencetak lembar nilai ijazah/SKL resmi.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="stu in students"
          :key="stu.id"
          class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between"
        >
          <div>
            <div class="flex items-start justify-between gap-2">
              <div>
                <h3 class="font-bold text-slate-800 text-sm leading-snug">{{ stu.full_name }}</h3>
                <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                  NISN: {{ stu.nisn || '-' }} &bull; Kelas: {{ stu.class_name }}
                </p>
              </div>
              <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                {{ getStudentCompletedSubjectCount(stu.id) }}/{{ subjects.length }} Mapel
              </span>
            </div>

            <!-- Quick score indicators -->
            <div class="grid grid-cols-3 gap-2 my-3 p-2 bg-slate-50 rounded-xl text-center border border-slate-100">
              <div>
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Rata Rapor</span>
                <span class="text-xs font-black font-mono text-emerald-700">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'avg_semester')) }}
                </span>
              </div>
              <div>
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Rata Ujian</span>
                <span class="text-xs font-black font-mono text-indigo-700">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'final_exam_score')) }}
                </span>
              </div>
              <div>
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Rata Ijazah</span>
                <span class="text-xs font-black font-mono text-amber-800">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'final_grade')) }}
                </span>
              </div>
            </div>
          </div>

          <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="openPrintModal(stu)"
              class="w-full py-1.5 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-200 transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
            >
              <Printer class="w-3.5 h-3.5 text-emerald-600" />
              <span>Cetak Transkrip / SKL</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 1: SETTINGS BOBOT & MAPEL PRAKTIK -->
    <div
      v-if="showSettingsModal"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
    >
      <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
              <Sliders class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-black text-slate-800">Pengaturan Bobot & Mapel Praktik</h3>
              <p class="text-xs text-slate-400">Atur bobot persentase nilai akhir ijazah dan kriteria ujian praktik.</p>
            </div>
          </div>
          <button @click="showSettingsModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="saveSettings" class="space-y-4">
          <!-- Bobot Nilai Akhir Ijazah -->
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60 space-y-3">
            <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
              <span>1. Bobot Nilai Ijazah (Total Wajib 100%)</span>
            </h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Bobot Rapor (S1-S6)</label>
                <div class="relative">
                  <input
                    type="number"
                    step="1"
                    min="0"
                    max="100"
                    v-model.number="tempSettings.report_weight"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:outline-emerald-500"
                  />
                  <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                </div>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Bobot Ujian Madrasah</label>
                <div class="relative">
                  <input
                    type="number"
                    step="1"
                    min="0"
                    max="100"
                    v-model.number="tempSettings.exam_weight"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:outline-emerald-500"
                  />
                  <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                </div>
              </div>
            </div>
            <div class="text-[11px] font-medium" :class="(tempSettings.report_weight + tempSettings.exam_weight === 100) ? 'text-emerald-600' : 'text-rose-600 font-bold'">
              Total: {{ tempSettings.report_weight + tempSettings.exam_weight }}% {{ (tempSettings.report_weight + tempSettings.exam_weight === 100) ? '✓ Sesuai standar' : '⚠️ Harus pas 100%' }}
            </div>
          </div>

          <!-- Bobot Ujian Madrasah (Tulis vs Praktik) -->
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60 space-y-3">
            <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider">
              2. Bobot Nilai Ujian (Untuk Mapel yang Berpraktik)
            </h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Bobot Ujian Tulis</label>
                <div class="relative">
                  <input
                    type="number"
                    step="1"
                    min="0"
                    max="100"
                    v-model.number="tempSettings.theory_weight_default"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:outline-emerald-500"
                  />
                  <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                </div>
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Bobot Ujian Praktik</label>
                <div class="relative">
                  <input
                    type="number"
                    step="1"
                    min="0"
                    max="100"
                    v-model.number="tempSettings.practice_weight_default"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-800 focus:outline-emerald-500"
                  />
                  <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Checklist Mapel yang Memiliki Praktik -->
          <div class="space-y-2">
            <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider">
              3. Mata Pelajaran yang Ada Ujian Praktik
            </label>
            <div class="max-h-48 overflow-y-auto p-2 border border-slate-200 rounded-xl grid grid-cols-2 gap-2 bg-slate-50/50">
              <label
                v-for="sub in subjects"
                :key="sub.id"
                class="flex items-center gap-2 p-1.5 bg-white rounded-lg border border-slate-100 text-xs font-medium cursor-pointer hover:bg-slate-50"
              >
                <input
                  type="checkbox"
                  :value="sub.id"
                  v-model="tempSettings.practical_subject_ids"
                  class="rounded text-emerald-600 focus:ring-emerald-500"
                />
                <span class="truncate">{{ sub.name }}</span>
              </label>
            </div>
          </div>

          <!-- Titimangsa -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[11px] font-bold text-slate-500 mb-1">Kota Titimangsa</label>
              <input
                type="text"
                v-model="tempSettings.date_place"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500"
                placeholder="Contoh: Bogor"
              />
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-500 mb-1">Tanggal Kelulusan / Ijazah</label>
              <input
                type="date"
                v-model="tempSettings.graduation_date"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500"
              />
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="showSettingsModal = false"
              class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-200 transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSavingSettings"
              class="px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 shadow-md transition-colors cursor-pointer disabled:opacity-60"
            >
              {{ isSavingSettings ? 'Menyimpan...' : 'Simpan Pengaturan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: CETAK TRANSKRIP NILAI IJAZAH / SKL (PRINT READY) -->
    <div
      v-if="showPrintModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 overflow-y-auto no-print"
    >
      <div class="bg-white rounded-2xl max-w-4xl w-full p-4 sm:p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[95vh] flex flex-col">
        <!-- Modal Toolbar -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-shrink-0">
          <div class="flex items-center gap-2">
            <Printer class="w-5 h-5 text-emerald-600" />
            <h3 class="font-bold text-slate-800 text-sm">Pratinjau Lembar Nilai Ijazah Resmi</h3>
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="triggerPrint"
              class="px-4 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition-colors flex items-center gap-1.5 shadow-sm cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Sekarang (Ctrl + P)</span>
            </button>
            <button
              type="button"
              @click="showPrintModal = false"
              class="p-1.5 text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Print Sheet Content Container -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 bg-slate-50 border border-slate-200 rounded-xl">
          <div id="print-area" class="bg-white p-6 sm:p-8 rounded-lg shadow-sm max-w-[210mm] mx-auto text-black font-serif leading-normal">
            <!-- Kop Madrasah Resmi -->
            <div class="text-center border-b-2 border-double border-black pb-3 mb-4">
              <h2 class="text-sm font-bold tracking-wider uppercase leading-tight">{{ schoolSetting?.foundation_name || 'YAYASAN PENDIDIKAN ISLAM AL-HASANAH' }}</h2>
              <h1 class="text-base sm:text-lg font-black tracking-wider uppercase leading-tight mt-0.5">{{ schoolSetting?.school_name || 'MADRASAH TSANAWIYAH AL-HASANAH' }}</h1>
              <p class="text-[10px] font-sans text-slate-600 mt-1">
                Alamat: {{ schoolSetting?.address || 'Jl. Ciapus Sukamakmur No.05, Kec. Ciomas, Kab. Bogor' }} &bull; Telp: {{ schoolSetting?.phone || '081617666017' }} &bull; Email: {{ schoolSetting?.email || 'mtsalhasanah.ciomas@gmail.com' }}
              </p>
            </div>

            <!-- Judul Dokumen -->
            <div class="text-center my-4">
              <h3 class="text-sm font-bold uppercase tracking-wider underline">TRANSKRIP NILAI REKAPITULASI IJAZAH</h3>
              <p class="text-[11px] font-sans mt-0.5">Nomor: MTs.AH/PP.01.1/SKL/{{ new Date().getFullYear() }}</p>
            </div>

            <!-- Identitas Siswa -->
            <div class="text-xs font-sans mb-4 grid grid-cols-2 gap-x-6 gap-y-1">
              <div class="flex"><span class="w-28 font-semibold">Nama Siswa</span><span>: <strong>{{ activePrintStudent?.full_name }}</strong></span></div>
              <div class="flex"><span class="w-28 font-semibold">NISN</span><span>: {{ activePrintStudent?.nisn || '-' }}</span></div>
              <div class="flex"><span class="w-28 font-semibold">Tempat, Tgl Lahir</span><span>: {{ activePrintStudent?.birth_place || '-' }}, {{ activePrintStudent?.birth_date || '-' }}</span></div>
              <div class="flex"><span class="w-28 font-semibold">Nomor Induk (NIS)</span><span>: {{ activePrintStudent?.nis || '-' }}</span></div>
              <div class="flex"><span class="w-28 font-semibold">Madrasah Asal</span><span>: {{ schoolSetting?.school_name || 'MTs Al-Hasanah' }}</span></div>
              <div class="flex"><span class="w-28 font-semibold">Tahun Pelajaran</span><span>: 2026/2027</span></div>
            </div>

            <!-- Tabel Nilai Ijazah -->
            <table class="w-full text-[11px] font-sans border-collapse border border-black mb-4">
              <thead>
                <tr class="bg-slate-100 text-center font-bold">
                  <th class="border border-black p-1.5 w-8">No</th>
                  <th class="border border-black p-1.5 text-left">Mata Pelajaran</th>
                  <th class="border border-black p-1.5 w-24">Rata-rata Rapor<br/><span class="text-[9px] font-normal">(S1 s.d. S6)</span></th>
                  <th class="border border-black p-1.5 w-24">Ujian Madrasah<br/><span class="text-[9px] font-normal">(UM / AM)</span></th>
                  <th class="border border-black p-1.5 w-24">Nilai Ijazah<br/><span class="text-[9px] font-normal">(Nilai Akhir)</span></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(sub, i) in subjects" :key="sub.id">
                  <td class="border border-black p-1 text-center font-mono">{{ i + 1 }}</td>
                  <td class="border border-black p-1 font-medium">{{ sub.name }}</td>
                  <td class="border border-black p-1 text-center font-mono font-semibold">
                    {{ formatScore(getStudentGradeRecord(activePrintStudent?.id, sub.id)?.avg_semester) }}
                  </td>
                  <td class="border border-black p-1 text-center font-mono font-semibold">
                    {{ formatScore(getStudentGradeRecord(activePrintStudent?.id, sub.id)?.final_exam_score) }}
                  </td>
                  <td class="border border-black p-1 text-center font-mono font-bold">
                    {{ formatScore(getStudentGradeRecord(activePrintStudent?.id, sub.id)?.final_grade) }}
                  </td>
                </tr>
                <!-- Rata-rata Total -->
                <tr class="bg-slate-100 font-bold">
                  <td colspan="2" class="border border-black p-1.5 text-center">RATA-RATA KESELURUHAN</td>
                  <td class="border border-black p-1.5 text-center font-mono">
                    {{ formatScore(getStudentOverallAverage(activePrintStudent?.id, 'avg_semester')) }}
                  </td>
                  <td class="border border-black p-1.5 text-center font-mono">
                    {{ formatScore(getStudentOverallAverage(activePrintStudent?.id, 'final_exam_score')) }}
                  </td>
                  <td class="border border-black p-1.5 text-center font-mono text-black font-black">
                    {{ formatScore(getStudentOverallAverage(activePrintStudent?.id, 'final_grade')) }}
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Titimangsa & Tanda Tangan -->
            <div class="flex justify-between items-end text-xs font-sans mt-8 pt-4">
              <div class="text-center w-40 border border-slate-300 p-2 rounded text-[10px] text-slate-500">
                <p>Pas Foto 3x4</p>
                <div class="h-24 flex items-center justify-center font-bold text-slate-300">FOTO</div>
              </div>

              <div class="text-center min-w-[200px]">
                <p>{{ settings?.date_place || 'Bogor' }}, {{ formatDateIndo(settings?.graduation_date) }}</p>
                <p class="font-bold mt-1">Kepala Madrasah,</p>
                <div class="h-20"></div>
                <p class="font-bold underline text-sm">{{ schoolSetting?.principal_name || 'H. Umar Usman Ali, S.Pd, S.Pd.I' }}</p>
                <p class="text-[10px]">NIP. {{ schoolSetting?.principal_nip || '-' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 3: INPUT / TAMBAH NILAI SEMESTER & MAPEL -->
    <div
      v-if="showAddSemesterModal"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 overflow-y-auto no-print"
    >
      <div class="bg-white rounded-3xl max-w-2xl w-full p-4 sm:p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] flex flex-col animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
              <PlusCircle class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-black text-slate-800">Tambah & Input Nilai Semester</h3>
              <p class="text-xs text-slate-400">Pilih tahun ajaran, semester, dan mata pelajaran yang akan diberi nilai.</p>
            </div>
          </div>
          <button @click="showAddSemesterModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer p-1">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form Parameter -->
        <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-2xl grid grid-cols-1 sm:grid-cols-3 gap-3 flex-shrink-0">
          <!-- 1. Tahun Pelajaran -->
          <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tahun Pelajaran</label>
            <input
              type="text"
              v-model="semesterForm.academic_year"
              placeholder="Contoh: 2024/2025"
              class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 font-mono"
            />
          </div>

          <!-- 2. Target Semester / Ujian -->
          <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Target Semester / Ujian</label>
            <select
              v-model="semesterForm.semester_target"
              @change="loadSemesterFormScores"
              class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 cursor-pointer"
            >
              <option value="sem_1">Semester 1 (Kls 7 Ganjil)</option>
              <option value="sem_2">Semester 2 (Kls 7 Genap)</option>
              <option value="sem_3">Semester 3 (Kls 8 Ganjil)</option>
              <option value="sem_4">Semester 4 (Kls 8 Genap)</option>
              <option value="sem_5">Semester 5 (Kls 9 Ganjil)</option>
              <option value="sem_6">Semester 6 (Kls 9 Genap)</option>
              <option value="exam_theory">Ujian Madrasah (Tulis)</option>
              <option value="exam_practice">Ujian Madrasah (Praktik)</option>
            </select>
          </div>

          <!-- 3. Rombel -->
          <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1">Rombel Kelas 9</label>
            <select
              v-model="semesterForm.class_id"
              @change="loadSemesterFormScores"
              class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 cursor-pointer"
            >
              <option value="">Semua Rombel ({{ students.length }} Siswa)</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                Kelas {{ cls.name }}
              </option>
            </select>
          </div>

          <!-- 4. Pilihan Mata Pelajaran (Full Row) -->
          <div class="sm:col-span-3">
            <label class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center justify-between">
              <span>Mata Pelajaran yang Dimunculkan & Dinilai:</span>
              <span v-if="isModalSubjectPractical" class="text-[10px] text-purple-700 font-bold bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                🔬 Mapel memiliki Ujian Praktik
              </span>
            </label>
            <select
              v-model="semesterForm.subject_id"
              @change="loadSemesterFormScores"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 cursor-pointer"
            >
              <option value="" disabled>-- Pilih Mata Pelajaran --</option>
              <option v-for="sub in subjects" :key="sub.id" :value="sub.id">
                {{ sub.name }} ({{ sub.code || '-' }}) {{ isSubjectPractical(sub.id) ? '🔬 (Ada Praktik)' : '' }}
              </option>
            </select>
          </div>
        </div>

        <!-- Quick Fill Toolbar -->
        <div class="flex items-center justify-between gap-2 p-2 bg-emerald-50/50 border border-emerald-100 rounded-xl text-xs flex-shrink-0">
          <span class="text-[11px] font-bold text-emerald-900">
            Daftar Siswa: <strong>{{ modalStudentList.length }}</strong> siswa
          </span>
          <div class="flex items-center gap-1.5">
            <span class="text-[10px] font-bold text-slate-500">Isi Serentak:</span>
            <input
              type="number"
              step="0.01"
              min="0"
              max="100"
              v-model="semesterForm.quick_score"
              placeholder="0-100"
              class="w-16 px-2 py-1 bg-white border border-slate-200 rounded text-center text-xs font-bold text-slate-800"
            />
            <button
              type="button"
              @click="applyQuickScore"
              class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-[11px] cursor-pointer transition-colors"
            >
              Terapkan
            </button>
          </div>
        </div>

        <!-- Table Input Siswa (Scrollable) -->
        <div class="overflow-y-auto flex-1 border border-slate-200 rounded-2xl">
          <table class="w-full text-xs text-left border-collapse">
            <thead class="sticky top-0 bg-slate-100 border-b border-slate-200 text-[11px] font-black text-slate-600 uppercase">
              <tr>
                <th class="py-2 px-3 w-10 text-center">No</th>
                <th class="py-2 px-3 text-left">Nama Siswa</th>
                <th class="py-2 px-2 text-center w-20">Kelas</th>
                <th class="py-2 px-3 text-center w-28 bg-emerald-100/70 text-emerald-900">
                  Nilai (0-100)
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr v-for="(stu, idx) in modalStudentList" :key="stu.id" class="hover:bg-slate-50/80">
                <td class="py-2 px-3 text-center text-slate-400 font-mono font-bold">{{ idx + 1 }}</td>
                <td class="py-2 px-3">
                  <div class="font-bold text-slate-800">{{ stu.full_name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">NISN: {{ stu.nisn || '-' }}</div>
                </td>
                <td class="py-2 px-2 text-center font-bold text-slate-600">{{ stu.class_name }}</td>
                <td class="p-1.5 bg-emerald-50/20 text-center">
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    v-model.number="semesterForm.scores[stu.id]"
                    class="w-full py-1 px-2 text-center bg-white border border-slate-200 rounded text-xs font-bold text-slate-800 focus:border-emerald-500 focus:outline-none"
                    placeholder="-"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer Actions -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between flex-shrink-0">
          <p class="text-[11px] text-slate-400">
            Nilai akan langsung masuk ke tabel rekap ijazah utama dan dihitung otomatis.
          </p>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showAddSemesterModal = false"
              class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-200 transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="saveSemesterGrades"
              :disabled="isSavingSemesterGrades"
              class="px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 shadow-md transition-colors cursor-pointer disabled:opacity-60 flex items-center gap-1.5"
            >
              <Save class="w-3.5 h-3.5" />
              <span>{{ isSavingSemesterGrades ? 'Menyimpan Nilai...' : 'Simpan Nilai Semester' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import {
  GraduationCap,
  Sliders,
  RefreshCw,
  Download,
  Save,
  Table,
  BookOpen,
  BookOpenCheck,
  Printer,
  Users,
  AlertCircle,
  X,
  PlusCircle
} from 'lucide-vue-next';

// State
const activeTab = ref('subject'); // 'subject' or 'transcripts'
const isLoading = ref(false);
const isSaving = ref(false);
const isPulling = ref(false);
const hasUnsavedChanges = ref(false);

const classes = ref([]);
const subjects = ref([]);
const students = ref([]);
const grades = ref([]);
const settings = ref(null);
const schoolSetting = ref(null);

const selectedClassId = ref('');
const selectedSubjectId = ref('');

// Add Semester Grades Modal State
const showAddSemesterModal = ref(false);
const isSavingSemesterGrades = ref(false);
const semesterForm = ref({
  academic_year: '2026/2027',
  semester_target: 'sem_1',
  class_id: '',
  subject_id: '',
  quick_score: '',
  scores: {},
});

const modalStudentList = computed(() => {
  if (!semesterForm.value.class_id) return students.value;
  return students.value.filter(s => s.class_id === Number(semesterForm.value.class_id) || s.lokal_class_id === Number(semesterForm.value.class_id));
});

const isModalSubjectPractical = computed(() => {
  return isSubjectPractical(semesterForm.value.subject_id);
});

// Grade matrix in memory: studentGradeMap[student_id][subject_id] = record
const gradeMatrix = ref({});

// Settings Modal State
const showSettingsModal = ref(false);
const isSavingSettings = ref(false);
const tempSettings = ref({
  report_weight: 60,
  exam_weight: 40,
  theory_weight_default: 50,
  practice_weight_default: 50,
  practical_subject_ids: [],
  date_place: 'Bogor',
  graduation_date: '',
});

// Print Modal State
const showPrintModal = ref(false);
const activePrintStudent = ref(null);

const currentSubject = computed(() => {
  if (!selectedSubjectId.value && subjects.value.length > 0) {
    return subjects.value[0];
  }
  return subjects.value.find(s => s.id === Number(selectedSubjectId.value)) || subjects.value[0] || null;
});

const isCurrentSubjectPractical = computed(() => {
  return isSubjectPractical(selectedSubjectId.value);
});

function isSubjectPractical(subId) {
  if (!settings.value?.practical_subject_ids) return false;
  const ids = settings.value.practical_subject_ids.map(Number);
  return ids.includes(Number(subId));
}

// Fetch all initial data
async function fetchData() {
  isLoading.value = true;
  try {
    const params = {};
    if (selectedClassId.value) params.class_id = selectedClassId.value;

    const res = await axios.get('/api/admin/graduation-grades', { params });
    if (res.data?.success) {
      const d = res.data.data;
      classes.value = d.classes || [];
      subjects.value = d.subjects || [];
      students.value = d.students || [];
      grades.value = d.grades || [];
      settings.value = d.setting || null;
      schoolSetting.value = d.school_setting || null;

      // Select first subject if not set or invalid
      if (subjects.value.length > 0) {
        const hasMatch = subjects.value.some(s => s.id === Number(selectedSubjectId.value));
        if (!hasMatch) {
          selectedSubjectId.value = subjects.value[0].id;
        }
      }

      // Populate matrix
      populateMatrix();
      hasUnsavedChanges.value = false;
    }
  } catch (err) {
    console.error('Gagal mengambil data rekap ijazah:', err);
  } finally {
    isLoading.value = false;
  }
}

function populateMatrix() {
  const map = {};
  for (const g of grades.value) {
    if (!map[g.student_id]) map[g.student_id] = {};
    map[g.student_id][g.subject_id] = { ...g };
  }
  gradeMatrix.value = map;
}

function getStudentRecord(studentId) {
  const subId = selectedSubjectId.value;
  if (!gradeMatrix.value[studentId]) {
    gradeMatrix.value[studentId] = {};
  }
  if (!gradeMatrix.value[studentId][subId]) {
    gradeMatrix.value[studentId][subId] = {
      student_id: studentId,
      subject_id: subId,
      sem_1: null,
      sem_2: null,
      sem_3: null,
      sem_4: null,
      sem_5: null,
      sem_6: null,
      avg_semester: null,
      exam_theory: null,
      exam_practice: null,
      final_exam_score: null,
      final_grade: null,
    };
  }
  return gradeMatrix.value[studentId][subId];
}

function getStudentGradeRecord(studentId, subjectId) {
  return gradeMatrix.value[studentId]?.[subjectId] || null;
}

// Auto recalculate client-side
function recalculateStudent(studentId) {
  hasUnsavedChanges.value = true;
  const rec = getStudentRecord(studentId);
  const st = settings.value || { report_weight: 60, exam_weight: 40, theory_weight_default: 50, practice_weight_default: 50 };

  // 1. Avg Semester
  const semVals = [];
  ['sem_1', 'sem_2', 'sem_3', 'sem_4', 'sem_5', 'sem_6'].forEach(k => {
    if (rec[k] !== null && rec[k] !== '' && !isNaN(rec[k])) {
      semVals.push(parseFloat(rec[k]));
    }
  });

  if (semVals.length > 0) {
    rec.avg_semester = Number((semVals.reduce((a, b) => a + b, 0) / semVals.length).toFixed(2));
  } else {
    rec.avg_semester = null;
  }

  // 2. Exam score
  const isPrac = isCurrentSubjectPractical.value;
  const theory = (rec.exam_theory !== null && rec.exam_theory !== '') ? parseFloat(rec.exam_theory) : null;
  const prac = (rec.exam_practice !== null && rec.exam_practice !== '') ? parseFloat(rec.exam_practice) : null;

  if (isPrac) {
    const tWeight = parseFloat(st.theory_weight_default || 50);
    const pWeight = parseFloat(st.practice_weight_default || 50);
    const totW = tWeight + pWeight;
    if (totW > 0 && (theory !== null || prac !== null)) {
      const tVal = (theory || 0) * (tWeight / totW);
      const pVal = (prac || 0) * (pWeight / totW);
      rec.final_exam_score = Number((tVal + pVal).toFixed(2));
    } else {
      rec.final_exam_score = null;
    }
  } else {
    rec.final_exam_score = theory !== null ? Number(theory.toFixed(2)) : null;
  }

  // 3. Final grade
  const rWeight = parseFloat(st.report_weight || 60);
  const eWeight = parseFloat(st.exam_weight || 40);
  const totFW = rWeight + eWeight;

  if (totFW > 0 && (rec.avg_semester !== null || rec.final_exam_score !== null)) {
    const rPart = (rec.avg_semester || 0) * (rWeight / totFW);
    const ePart = (rec.final_exam_score || 0) * (eWeight / totFW);
    rec.final_grade = Number((rPart + ePart).toFixed(2));
  } else {
    rec.final_grade = null;
  }
}

function onSubjectChange() {
  // When switching subject, keep uncommitted edits or fetch
}

// Save Current Subject Grades
async function saveCurrentGrades() {
  if (isSaving.value) return;
  isSaving.value = true;
  try {
    const subId = selectedSubjectId.value;
    const payload = [];

    for (const stu of students.value) {
      const rec = getStudentRecord(stu.id);
      payload.push({
        student_id: stu.id,
        subject_id: subId,
        sem_1: rec.sem_1,
        sem_2: rec.sem_2,
        sem_3: rec.sem_3,
        sem_4: rec.sem_4,
        sem_5: rec.sem_5,
        sem_6: rec.sem_6,
        exam_theory: rec.exam_theory,
        exam_practice: rec.exam_practice,
      });
    }

    const res = await axios.post('/api/admin/graduation-grades/save', { grades: payload });
    if (res.data?.success) {
      hasUnsavedChanges.value = false;
      // Refresh
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menyimpan nilai:', err);
  } finally {
    isSaving.value = false;
  }
}

// Auto pull from database
async function handleAutoPull() {
  if (isPulling.value) return;
  isPulling.value = true;
  try {
    const res = await axios.post('/api/admin/graduation-grades/auto-pull', {
      class_id: selectedClassId.value || null,
      subject_id: selectedSubjectId.value || null,
    });
    if (res.data?.success) {
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menarik nilai otomatis:', err);
  } finally {
    isPulling.value = false;
  }
}

// Input & Tambah Nilai Semester Modal Methods
function openAddSemesterGradesModal() {
  semesterForm.value.class_id = selectedClassId.value || '';
  if (!semesterForm.value.subject_id || !subjects.value.some(s => s.id === Number(semesterForm.value.subject_id))) {
    semesterForm.value.subject_id = selectedSubjectId.value || (subjects.value[0]?.id || '');
  }
  semesterForm.value.quick_score = '';
  loadSemesterFormScores();
  showAddSemesterModal.value = true;
}

function loadSemesterFormScores() {
  const subId = Number(semesterForm.value.subject_id);
  const target = semesterForm.value.semester_target;
  const map = {};
  for (const stu of modalStudentList.value) {
    const rec = gradeMatrix.value[stu.id]?.[subId];
    map[stu.id] = rec && rec[target] !== null && rec[target] !== undefined ? rec[target] : '';
  }
  semesterForm.value.scores = map;
}

function applyQuickScore() {
  if (semesterForm.value.quick_score === '' || isNaN(semesterForm.value.quick_score)) return;
  const val = parseFloat(semesterForm.value.quick_score);
  for (const stu of modalStudentList.value) {
    semesterForm.value.scores[stu.id] = val;
  }
}

async function saveSemesterGrades() {
  if (isSavingSemesterGrades.value) return;
  isSavingSemesterGrades.value = true;
  try {
    const subId = Number(semesterForm.value.subject_id);
    const target = semesterForm.value.semester_target;
    const payload = [];

    for (const stu of modalStudentList.value) {
      const scoreVal = semesterForm.value.scores[stu.id];
      const numVal = (scoreVal !== '' && scoreVal !== null && !isNaN(scoreVal)) ? parseFloat(scoreVal) : null;
      
      const existing = getStudentGradeRecord(stu.id, subId) || {};
      payload.push({
        student_id: stu.id,
        subject_id: subId,
        sem_1: target === 'sem_1' ? numVal : (existing.sem_1 ?? null),
        sem_2: target === 'sem_2' ? numVal : (existing.sem_2 ?? null),
        sem_3: target === 'sem_3' ? numVal : (existing.sem_3 ?? null),
        sem_4: target === 'sem_4' ? numVal : (existing.sem_4 ?? null),
        sem_5: target === 'sem_5' ? numVal : (existing.sem_5 ?? null),
        sem_6: target === 'sem_6' ? numVal : (existing.sem_6 ?? null),
        exam_theory: target === 'exam_theory' ? numVal : (existing.exam_theory ?? null),
        exam_practice: target === 'exam_practice' ? numVal : (existing.exam_practice ?? null),
      });
    }

    const res = await axios.post('/api/admin/graduation-grades/save', { grades: payload });
    if (res.data?.success) {
      showAddSemesterModal.value = false;
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menyimpan nilai semester:', err);
    alert('Gagal menyimpan nilai semester: ' + (err.response?.data?.message || err.message));
  } finally {
    isSavingSemesterGrades.value = false;
  }
}

// Export PDUM
function handleExportPdum() {
  let url = '/api/admin/graduation-grades/export-pdum';
  if (selectedClassId.value) {
    url += `?class_id=${selectedClassId.value}`;
  }
  window.open(url, '_blank');
}

// Open Settings
function openSettingsModal() {
  if (settings.value) {
    tempSettings.value = {
      report_weight: settings.value.report_weight || 60,
      exam_weight: settings.value.exam_weight || 40,
      theory_weight_default: settings.value.theory_weight_default || 50,
      practice_weight_default: settings.value.practice_weight_default || 50,
      practical_subject_ids: Array.isArray(settings.value.practical_subject_ids)
        ? [...settings.value.practical_subject_ids]
        : [],
      date_place: settings.value.date_place || 'Bogor',
      graduation_date: settings.value.graduation_date || '',
    };
  }
  showSettingsModal.value = true;
}

async function saveSettings() {
  if (isSavingSettings.value) return;
  isSavingSettings.value = true;
  try {
    const res = await axios.post('/api/admin/graduation-grades/settings', tempSettings.value);
    if (res.data?.success) {
      showSettingsModal.value = false;
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menyimpan pengaturan bobot:', err);
  } finally {
    isSavingSettings.value = false;
  }
}

// Print Modal
function openPrintModal(student) {
  activePrintStudent.value = student;
  showPrintModal.value = true;
}

function triggerPrint() {
  window.print();
}

// Summary helpers
function getStudentCompletedSubjectCount(studentId) {
  const map = gradeMatrix.value[studentId] || {};
  return Object.values(map).filter(r => r.final_grade !== null && r.final_grade > 0).length;
}

function getStudentOverallAverage(studentId, field) {
  const map = gradeMatrix.value[studentId] || {};
  const vals = [];
  for (const sub of subjects.value) {
    const rec = map[sub.id];
    if (rec && rec[field] !== null && !isNaN(rec[field])) {
      vals.push(parseFloat(rec[field]));
    }
  }
  if (vals.length === 0) return null;
  return Number((vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(2));
}

function formatScore(val) {
  if (val === null || val === undefined || isNaN(val)) return '-';
  return Number(val).toFixed(2).replace(/\.00$/, '');
}

function formatDateIndo(dateStr) {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
  } catch {
    return dateStr;
  }
}

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
@media print {
  body * {
    visibility: hidden;
  }
  #print-area, #print-area * {
    visibility: visible;
  }
  #print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    margin: 0;
    padding: 0;
    box-shadow: none;
    border: none;
  }
  .no-print {
    display: none !important;
  }
}
</style>
