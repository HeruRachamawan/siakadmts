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
          title="Pengaturan Bobot Rapor, Ujian, & Blanko Ijazah"
        >
          <Sliders class="w-4 h-4 text-slate-500" />
          <span>Atur Bobot & Titimangsa</span>
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
          @click="activeTab = 'transcripts'"
          class="px-3.5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-black rounded-xl shadow-md shadow-purple-600/20 transition-all flex items-center gap-1.5 cursor-pointer"
          title="Buka Menu Cetak Ijazah & Transkrip Nilai Siswa (2 Lembar Standar Kemenag)"
        >
          <Printer class="w-4 h-4" />
          <span>Cetak Ijazah & Transkrip</span>
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
          @click="openImportModal(inputSemesterKey)"
          class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-xl border border-emerald-200 transition-colors flex items-center gap-1.5 cursor-pointer shadow-2xs"
          title="Import Nilai Excel Massal (Per Semester atau Leger Lengkap)"
        >
          <Upload class="w-4 h-4 text-emerald-600" />
          <span>Import Excel</span>
        </button>

        <button
          type="button"
          v-if="activeTab === 'semester'"
          @click="saveSemesterTabGrades"
          :disabled="isSaving || !hasUnsavedChanges"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:shadow-none"
        >
          <Save class="w-4 h-4" />
          <span>{{ isSaving ? 'Menyimpan...' : (hasUnsavedChanges ? `Simpan Nilai ${currentInputSemester.label}` : 'Tersimpan') }}</span>
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
        <div class="grid grid-cols-2 sm:grid-cols-4 p-1 bg-slate-100 rounded-xl border border-slate-200/60 shadow-2xs gap-1 sm:gap-0">
          <button
            type="button"
            @click="activeTab = 'semester'"
            :class="activeTab === 'semester' ? 'bg-white text-emerald-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <Calendar class="w-3.5 h-3.5 text-emerald-600" />
            <span>Input Per Semester</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'subject'"
            :class="activeTab === 'subject' ? 'bg-white text-teal-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <Table class="w-3.5 h-3.5 text-teal-600" />
            <span>Input Per Mapel</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'ledger'"
            :class="activeTab === 'ledger' ? 'bg-white text-indigo-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <FileSpreadsheet class="w-3.5 h-3.5 text-indigo-600" />
            <span>Leger Semua Mapel</span>
          </button>
          <button
            type="button"
            @click="activeTab = 'transcripts'"
            :class="activeTab === 'transcripts' ? 'bg-white text-purple-900 font-black shadow-xs ring-1 ring-purple-200' : 'text-slate-600 hover:text-slate-900 font-bold'"
            class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <GraduationCap class="w-3.5 h-3.5 text-purple-600" />
            <span>Cetak Ijazah & Transkrip</span>
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

    <!-- TAB 1: SPREADSHEET INPUT PER SEMESTER (SEMUA MAPEL) -->
    <div v-if="activeTab === 'semester'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden no-print">
      <!-- Semester Header Card -->
      <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold flex-shrink-0">
            <Calendar class="w-5 h-5 text-emerald-700" />
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend uppercase tracking-wider">
                Input Nilai {{ currentInputSemester.label }}
              </h2>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                {{ currentInputSemester.kls }} &bull; TP {{ currentInputSemester.tp }}
              </span>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200">
                {{ subjects.length }} Mapel Aktif
              </span>
              <span v-if="inputSemesterKey === 'exam'" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                🔬 {{ practicalSubjectCount }} Mapel Praktik
              </span>
            </div>
            <p class="text-[11px] text-slate-500 font-medium mt-0.5">
              Input nilai siswa kelas 9 untuk seluruh mata pelajaran pada {{ currentInputSemester.label }}. Nilai terakumulasi otomatis ke Leger dan Transkrip.
            </p>
          </div>
        </div>

        <!-- Quick actions -->
        <div class="flex items-center gap-2 flex-wrap flex-shrink-0">
          <div v-if="hasUnsavedChanges" class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold flex items-center gap-1.5 animate-pulse">
            <AlertCircle class="w-3.5 h-3.5 text-amber-600" />
            <span>Ada perubahan belum disimpan!</span>
          </div>

          <!-- Tombol Download Template Excel -->
          <button
            type="button"
            @click="downloadExcelTemplate(inputSemesterKey)"
            :disabled="isDownloadingTemplate"
            class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer shadow-2xs disabled:opacity-50"
            :title="`Unduh template Excel resmi untuk ${currentInputSemester.label}`"
          >
            <Download v-if="!isDownloadingTemplate" class="w-3.5 h-3.5 text-emerald-600" />
            <RefreshCw v-else class="w-3.5 h-3.5 animate-spin text-emerald-600" />
            <span class="hidden sm:inline">Template Excel</span>
          </button>

          <!-- Tombol Import Excel -->
          <button
            type="button"
            @click="openImportModal(inputSemesterKey)"
            class="px-3.5 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white text-xs font-bold rounded-xl transition-all shadow-sm shadow-emerald-600/20 flex items-center gap-1.5 cursor-pointer"
            :title="`Upload & Import Nilai Excel untuk ${currentInputSemester.label}`"
          >
            <Upload class="w-3.5 h-3.5" />
            <span>Import Excel</span>
          </button>

          <!-- Quick Fill -->
          <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-2 py-1">
            <span class="text-[11px] font-bold text-slate-500 hidden sm:inline">Isi Cepat:</span>
            <input
              type="number"
              v-model="semesterQuickScore"
              placeholder="KKM/Nilai"
              class="w-20 px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-center focus:outline-emerald-500"
            />
            <button
              type="button"
              @click="applySemesterQuickFill()"
              class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-lg transition-colors cursor-pointer"
              title="Isi nilai ke seluruh kolom mapel yang masih kosong"
            >
              Isi Kosong
            </button>
          </div>

          <!-- Save Button -->
          <button
            type="button"
            @click="saveSemesterTabGrades"
            :disabled="isSaving || !hasUnsavedChanges"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:shadow-none"
          >
            <Save class="w-4 h-4" />
            <span>{{ isSaving ? 'Menyimpan...' : (hasUnsavedChanges ? `Simpan Nilai ${currentInputSemester.label}` : 'Tersimpan') }}</span>
          </button>
        </div>
      </div>

      <!-- Semester Selector Pills Strip -->
      <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center gap-2 overflow-x-auto">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex-shrink-0 mr-1 flex items-center gap-1">
          <Calendar class="w-3.5 h-3.5 text-slate-400" />
          Pilih Semester:
        </span>
        <button
          v-for="sem in inputSemesterList"
          :key="'input-sem-' + sem.key"
          type="button"
          @click="inputSemesterKey = sem.key"
          class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1.5 cursor-pointer flex-shrink-0 border"
          :class="inputSemesterKey === sem.key
            ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs'
            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'"
        >
          <span>{{ sem.icon }}</span>
          <span>{{ sem.label }}</span>
          <span class="text-[10px] opacity-75">({{ sem.kls }})</span>
        </button>
      </div>

      <!-- Loading / Empty / Table -->
      <div v-if="isLoading" class="p-12 text-center text-slate-500 font-medium space-y-2">
        <RefreshCw class="w-8 h-8 animate-spin mx-auto text-emerald-600 mb-2" />
        <p>Memuat data nilai...</p>
      </div>
      <div v-else-if="students.length === 0" class="p-12 text-center text-slate-400 space-y-2">
        <Users class="w-10 h-10 mx-auto text-slate-300" />
        <p class="font-bold text-slate-600">Belum Ada Siswa di Kelas 9</p>
      </div>
      <div v-else class="overflow-x-auto max-h-[72vh]">
        <table class="w-full text-xs text-left border-collapse">
          <thead class="sticky top-0 z-20 bg-slate-100/95 backdrop-blur-xs border-b border-slate-200 text-[11px] font-black text-slate-600 uppercase tracking-wider text-center">
            <tr>
              <th rowspan="2" class="py-3 px-3 w-10 text-center border-r border-slate-200/80 sticky left-0 z-30 bg-slate-100">No</th>
              <th rowspan="2" class="py-3 px-3 text-left min-w-[200px] border-r border-slate-200/80 sticky left-10 z-30 bg-slate-100">Nama Siswa & NISN</th>
              <th rowspan="2" class="py-3 px-2 w-14 border-r border-slate-200/80 sticky left-[240px] z-30 bg-slate-100">Kelas</th>
              <th :colspan="subjects.length" class="py-2.5 px-2 bg-emerald-50 text-emerald-900 border-r border-slate-200/80 font-black">
                NILAI {{ currentInputSemester.label.toUpperCase() }} (SELURUH MATA PELAJARAN)
              </th>
              <th rowspan="2" class="py-3 px-2 w-20 bg-indigo-50 text-indigo-900 border-r border-slate-200/80">Total</th>
              <th rowspan="2" class="py-3 px-2 w-20 bg-amber-50 text-amber-900 border-r border-slate-200/80">Rata-Rata</th>
              <th rowspan="2" class="py-3 px-2 w-24 bg-slate-100">Kelengkapan</th>
            </tr>
            <tr class="text-[10px] font-bold text-slate-600 bg-slate-50 border-b border-slate-200">
              <th
                v-for="sub in subjects"
                :key="'th-input-sub-' + sub.id"
                class="py-2 px-1 min-w-[68px] border-r border-slate-200/60"
                :title="sub.name"
              >
                <div class="font-bold truncate max-w-[85px] mx-auto">{{ sub.code || sub.name.substring(0, 8) }}</div>
                <div v-if="inputSemesterKey === 'exam' && isSubjectPractical(sub.id)" class="text-[9px] text-purple-700 font-normal">
                  🔬 T/P/Akhir
                </div>
                <div v-else-if="isSubjectPractical(sub.id)" class="text-[9px] text-purple-700 font-normal">🔬 Praktik</div>
                <div v-else class="text-[9px] text-slate-400 font-normal">📝 Teori</div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr
              v-for="(stu, idx) in students"
              :key="'input-sem-row-' + stu.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <td class="py-2 px-3 text-center text-slate-400 font-mono font-bold border-r border-slate-100 sticky left-0 bg-white z-10">
                {{ idx + 1 }}
              </td>
              <td class="py-2 px-3 border-r border-slate-100 sticky left-10 bg-white z-10">
                <div class="font-bold text-slate-800 text-[13px] leading-tight truncate max-w-[200px]">{{ stu.full_name }}</div>
                <div class="text-[10px] text-slate-400 font-mono mt-0.5">NISN: {{ stu.nisn || '-' }}</div>
              </td>
              <td class="py-2 px-2 text-center font-bold text-slate-600 border-r border-slate-100 sticky left-[240px] bg-white z-10">
                {{ stu.class_name }}
              </td>

              <!-- Subject Inputs -->
              <td
                v-for="sub in subjects"
                :key="'input-cell-' + stu.id + '-' + sub.id"
                class="py-1.5 px-1 text-center border-r border-slate-100"
              >
                <!-- IF REGULAR SEMESTER (sem_1 s.d sem_6) -->
                <template v-if="inputSemesterKey !== 'exam'">
                  <input
                    type="number"
                    min="0"
                    max="100"
                    step="any"
                    v-model.number="getStudentRecordBySubject(stu.id, sub.id)[inputSemesterKey]"
                    @input="onSemesterGradeInput(stu.id, sub.id)"
                    class="w-14 text-center font-mono font-bold text-xs py-1 px-1 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all"
                    :class="getStudentRecordBySubject(stu.id, sub.id)[inputSemesterKey] ? 'bg-white text-slate-800 font-bold' : 'bg-slate-50/50 text-slate-400'"
                    placeholder="-"
                  />
                </template>

                <!-- IF EXAM (Ujian Madrasah) -->
                <template v-else>
                  <!-- Mapel dengan Praktik -->
                  <div v-if="isSubjectPractical(sub.id)" class="flex items-center justify-center gap-0.5">
                    <input
                      type="number"
                      min="0"
                      max="100"
                      v-model.number="getStudentRecordBySubject(stu.id, sub.id).exam_theory"
                      @input="onSemesterGradeInput(stu.id, sub.id)"
                      placeholder="T"
                      title="Ujian Tulis (Teori)"
                      class="w-9 text-center font-mono text-[11px] py-1 px-0.5 rounded border border-slate-200 focus:border-emerald-500 focus:outline-none"
                    />
                    <input
                      type="number"
                      min="0"
                      max="100"
                      v-model.number="getStudentRecordBySubject(stu.id, sub.id).exam_practice"
                      @input="onSemesterGradeInput(stu.id, sub.id)"
                      placeholder="P"
                      title="Ujian Praktik"
                      class="w-9 text-center font-mono text-[11px] py-1 px-0.5 rounded border border-purple-200 bg-purple-50/30 focus:border-purple-500 focus:outline-none"
                    />
                    <input
                      type="number"
                      min="0"
                      max="100"
                      v-model.number="getStudentRecordBySubject(stu.id, sub.id).final_exam_score"
                      @input="onSemesterGradeInput(stu.id, sub.id)"
                      placeholder="Akhir"
                      title="N-Ujian (Nilai Akhir Ujian)"
                      class="w-10 text-center font-mono font-black text-[11px] py-1 px-0.5 rounded border border-indigo-200 bg-indigo-50/60 text-indigo-900 focus:border-indigo-500 focus:outline-none"
                    />
                  </div>
                  <!-- Mapel Teori Murni -->
                  <div v-else>
                    <input
                      type="number"
                      min="0"
                      max="100"
                      step="any"
                      v-model.number="getStudentRecordBySubject(stu.id, sub.id).final_exam_score"
                      @input="onSemesterGradeInput(stu.id, sub.id)"
                      class="w-14 text-center font-mono font-bold text-xs py-1 px-1 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all"
                      :class="getStudentRecordBySubject(stu.id, sub.id).final_exam_score ? 'bg-white text-slate-800' : 'bg-slate-50/50 text-slate-400'"
                      placeholder="-"
                    />
                  </div>
                </template>
              </td>

              <!-- Total Score for Selected Semester -->
              <td class="py-2 px-2 text-center font-mono font-black text-indigo-900 bg-indigo-50/40 border-r border-slate-100">
                {{ formatScore(getStudentSemesterTotal(stu.id, inputSemesterKey)) }}
              </td>

              <!-- Average Score for Selected Semester -->
              <td class="py-2 px-2 text-center font-mono font-black text-amber-950 bg-amber-50/60 border-r border-slate-100">
                <span class="px-2 py-0.5 rounded bg-amber-100/90 text-amber-900">
                  {{ formatScore(getStudentSemesterAverage(stu.id, inputSemesterKey)) }}
                </span>
              </td>

              <!-- Completed Count -->
              <td class="py-2 px-2 text-center border-r border-slate-100">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="getStudentCompletedSubjectCount(stu.id, inputSemesterKey === 'exam' ? 'final_exam_score' : inputSemesterKey) === subjects.length ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                >
                  {{ getStudentCompletedSubjectCount(stu.id, inputSemesterKey === 'exam' ? 'final_exam_score' : inputSemesterKey) }}/{{ subjects.length }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: SPREADSHEET INPUT PER MAPEL -->
    <div v-if="activeTab === 'subject'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden no-print">
      <!-- Subject Header Card -->
      <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold flex-shrink-0">
            <BookOpenCheck class="w-5 h-5 text-emerald-700" />
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="text-sm sm:text-base font-black text-slate-800">{{ currentSubject?.name || 'Mata Pelajaran' }}</h2>
              <span
                v-if="isCurrentSubjectPractical"
                class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200"
              >
                🔬 Ujian Praktik & Tulis Aktif (Praktik {{ settings?.practice_weight_default || 50 }}% &bull; Tulis {{ settings?.theory_weight_default || 50 }}%)
              </span>
              <span v-else class="px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 bg-slate-100 border border-slate-200">
                📝 Ujian Teori Saja (Tulis 100%)
              </span>
            </div>
            <p class="text-[11px] text-slate-500 font-medium mt-0.5">
              Total {{ students.length }} siswa kelas 9 &bull; Rapor 6 Semester + Nilai Ujian &bull; Nilai Ijazah dihitung otomatis realtime.
            </p>
          </div>
        </div>

        <div v-if="hasUnsavedChanges" class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold flex items-center gap-1.5 animate-pulse flex-shrink-0">
          <AlertCircle class="w-3.5 h-3.5 text-amber-600" />
          <span>Ada perubahan belum disimpan!</span>
        </div>
      </div>

      <!-- Quick Subject Switcher Strip -->
      <div class="px-4 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center gap-1.5 overflow-x-auto">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex-shrink-0 mr-1">
          Pilih Mapel:
        </span>
        <button
          v-for="sub in subjects"
          :key="'strip-' + sub.id"
          type="button"
          @click="selectedSubjectId = sub.id; onSubjectChange()"
          class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1 cursor-pointer flex-shrink-0 border"
          :class="Number(selectedSubjectId) === sub.id 
            ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' 
            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100'"
        >
          <span>{{ sub.name }}</span>
          <span v-if="isSubjectPractical(sub.id)" class="text-[10px]" title="Ada Ujian Praktik">🔬</span>
        </button>
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
              <th rowspan="2" class="py-3 px-2 w-16 border-r border-slate-200/60">Kelas</th>
              <th colspan="7" class="py-2 px-2 bg-emerald-50/60 text-emerald-800 border-r border-slate-200/60">
                Nilai Rapor (Semester 1 s.d. 6)
              </th>
              <th :colspan="isCurrentSubjectPractical ? 3 : 2" class="py-2 px-2 bg-indigo-50/60 text-indigo-800 border-r border-slate-200/60">
                Nilai Ujian Madrasah
              </th>
              <th rowspan="2" class="py-3 px-3 w-28 bg-amber-50/80 text-amber-900 border-l border-amber-200">
                <div>Nilai Ijazah</div>
                <div class="text-[8px] font-semibold text-amber-700 uppercase tracking-tight">PDUM Kemenag</div>
                <div class="text-[8px] font-mono text-slate-400 font-normal">R {{ settings?.report_weight || 60 }}% : U {{ settings?.exam_weight || 40 }}%</div>
              </th>
            </tr>
            <tr class="bg-slate-50 text-[10px] font-bold text-slate-500 text-center border-b border-slate-200/80">
              <th class="py-2 px-1.5 w-16 bg-emerald-50/40 border-r border-slate-200/40">
                <div class="font-black text-slate-700">Sem 1</div>
                <div class="text-[9px] font-medium text-slate-500">Kls 7 Ganjil</div>
                <div class="text-[8px] font-mono text-emerald-700">2024/2025</div>
              </th>
              <th class="py-2 px-1.5 w-16 bg-emerald-50/40 border-r border-slate-200/40">
                <div class="font-black text-slate-700">Sem 2</div>
                <div class="text-[9px] font-medium text-slate-500">Kls 7 Genap</div>
                <div class="text-[8px] font-mono text-emerald-700">2024/2025</div>
              </th>
              <th class="py-2 px-1.5 w-16 bg-emerald-50/40 border-r border-slate-200/40">
                <div class="font-black text-slate-700">Sem 3</div>
                <div class="text-[9px] font-medium text-slate-500">Kls 8 Ganjil</div>
                <div class="text-[8px] font-mono text-emerald-700">2025/2026</div>
              </th>
              <th class="py-2 px-1.5 w-16 bg-emerald-50/40 border-r border-slate-200/40">
                <div class="font-black text-slate-700">Sem 4</div>
                <div class="text-[9px] font-medium text-slate-500">Kls 8 Genap</div>
                <div class="text-[8px] font-mono text-emerald-700">2025/2026</div>
              </th>
              <th class="py-2 px-1.5 w-16 bg-emerald-50/40 border-r border-slate-200/40">
                <div class="font-black text-slate-700">Sem 5</div>
                <div class="text-[9px] font-medium text-slate-500">Kls 9 Ganjil</div>
                <div class="text-[8px] font-mono text-emerald-700">2026/2027</div>
              </th>
              <th class="py-2 px-1.5 w-16 bg-emerald-50/40 border-r border-slate-200/40">
                <div class="font-black text-slate-700">Sem 6</div>
                <div class="text-[9px] font-medium text-slate-500">Kls 9 Genap</div>
                <div class="text-[8px] font-mono text-emerald-700">2026/2027</div>
              </th>
              <th class="py-2 px-1.5 w-16 bg-emerald-100/60 text-emerald-900 font-black border-r border-slate-200/60">
                <div>R-Rapor</div>
                <div class="text-[8px] font-normal text-emerald-700">Rerata S1-6</div>
              </th>

              <th class="py-2 px-1.5 w-16 bg-indigo-50/30 border-r border-slate-200/40">
                <div class="font-bold text-slate-700">Tulis</div>
                <div class="text-[8px] font-normal text-slate-400">Teori</div>
              </th>
              <th v-if="isCurrentSubjectPractical" class="py-2 px-1.5 w-16 bg-purple-50/30 text-purple-900 border-r border-slate-200/40">
                <div class="font-bold text-purple-800">Praktik</div>
                <div class="text-[8px] font-normal text-purple-600">{{ settings?.practice_weight_default || 50 }}%</div>
              </th>
              <th class="py-2 px-1.5 w-16 bg-indigo-100/60 text-indigo-900 font-black border-r border-slate-200/60">
                <div>N-Ujian</div>
                <div class="text-[8px] font-normal text-indigo-700">Nilai Akhir</div>
              </th>
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

              <!-- Nilai Ujian (N-Ujian Bisa Diketik Bebas) -->
              <td class="p-1 bg-indigo-50/20 border-r border-slate-100">
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model.number="getStudentRecord(stu.id).final_exam_score"
                  @input="onFinalExamScoreInput(stu.id)"
                  class="w-full text-center py-1.5 px-1 bg-white border border-indigo-200 rounded text-xs font-bold text-indigo-900 focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 focus:outline-none"
                  placeholder="-"
                  title="Nilai Ujian Madrasah (Bisa diisi langsung atau otomatis dari Tulis/Praktik)"
                />
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

    <!-- TAB 2: LEGER REKAPITULASI SEMUA MAPEL (KOLEKTIF) -->
    <div v-if="activeTab === 'ledger'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden no-print space-y-0">
      <!-- Ledger Header & Toolbar -->
      <div class="p-4 sm:p-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend uppercase tracking-wider">
              {{ currentLedgerModeObj.title }}
            </h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide bg-teal-50 text-teal-800 border border-teal-200">
              {{ subjects.length }} Mapel Aktif
            </span>
            <span v-if="ledgerMode === 'final_grade' || ledgerMode.includes('exam')" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
              🔬 {{ practicalSubjectCount }} Mapel Praktik
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
              Mode: {{ currentLedgerModeObj.shortLabel }}
            </span>
          </div>
          <p class="text-[11px] text-slate-500 font-medium mt-1">
            {{ currentLedgerModeObj.sub }} &bull; Total {{ filteredLedgerStudents.length }} siswa kelas 9.
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap flex-shrink-0">
          <!-- Dropdown Mode Leger -->
          <div class="flex items-center gap-1.5">
            <span class="text-[11px] font-bold text-slate-500 flex items-center gap-1">
              <Filter class="w-3.5 h-3.5 text-slate-400" />
              Pilihan Leger:
            </span>
            <select
              v-model="ledgerMode"
              class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 cursor-pointer shadow-2xs"
            >
              <optgroup label="Ijazah & Akumulasi">
                <option value="final_grade">🎓 Nilai Akhir Ijazah (PDUM)</option>
                <option value="avg_semester">📊 R-Rapor (Rata-Rata Sem 1-6)</option>
              </optgroup>
              <optgroup label="Per Semester Rapor">
                <option value="sem_1">📘 Semester 1 (Kls 7 Ganjil 2024/2025)</option>
                <option value="sem_2">📘 Semester 2 (Kls 7 Genap 2024/2025)</option>
                <option value="sem_3">📙 Semester 3 (Kls 8 Ganjil 2025/2026)</option>
                <option value="sem_4">📙 Semester 4 (Kls 8 Genap 2025/2026)</option>
                <option value="sem_5">📕 Semester 5 (Kls 9 Ganjil 2026/2027)</option>
                <option value="sem_6">📕 Semester 6 (Kls 9 Genap 2026/2027)</option>
              </optgroup>
              <optgroup label="Ujian Madrasah">
                <option value="final_exam_score">📝 Nilai Ujian Madrasah (N-Ujian)</option>
                <option value="exam_theory">✍️ Nilai Ujian Tulis (Teori)</option>
                <option value="exam_practice">🔬 Nilai Ujian Praktik</option>
              </optgroup>
            </select>
          </div>

          <!-- Search box -->
          <div class="relative">
            <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
            <input
              type="text"
              v-model="ledgerSearch"
              placeholder="Cari nama / NISN..."
              class="pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 w-36 sm:w-48"
            />
          </div>

          <!-- Export CSV Leger Button -->
          <button
            type="button"
            @click="exportLedgerCsv"
            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer"
            title="Unduh data tabel leger yang sedang tampil ke file CSV"
          >
            <Download class="w-3.5 h-3.5" />
            <span>Ekspor CSV</span>
          </button>
        </div>
      </div>

      <!-- Quick Semester & Mode Selector Strip (Pills) -->
      <div class="px-4 py-2.5 bg-slate-50/90 border-b border-slate-200/80 flex items-center gap-1.5 overflow-x-auto">
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex-shrink-0 mr-1 flex items-center gap-1">
          <Calendar class="w-3.5 h-3.5 text-slate-400" />
          Pilih Semester / Data:
        </span>
        <button
          v-for="mode in ledgerModes"
          :key="'ledger-tab-' + mode.id"
          type="button"
          @click="ledgerMode = mode.id"
          class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all whitespace-nowrap flex items-center gap-1 cursor-pointer flex-shrink-0 border"
          :class="ledgerMode === mode.id
            ? 'bg-emerald-700 text-white border-emerald-700 shadow-xs'
            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100 hover:text-slate-800'"
          :title="mode.sub"
        >
          <span>{{ mode.shortLabel }}</span>
        </button>
      </div>

      <!-- Info Strip Bobot & Keterangan Aktif -->
      <div class="p-3 bg-slate-50/70 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
        <div class="flex items-center gap-2 flex-wrap text-slate-600">
          <span class="font-bold text-slate-700">Data Terpilih:</span>
          <span class="px-2 py-0.5 bg-emerald-100/90 text-emerald-900 rounded font-bold text-[11px]">
            {{ currentLedgerModeObj.title }}
          </span>
          <span class="text-slate-300">&bull;</span>
          <span class="text-slate-500 text-[11px]">
            {{ currentLedgerModeObj.sub }}
          </span>
          <template v-if="ledgerMode === 'final_grade'">
            <span class="text-slate-300">&bull;</span>
            <span class="text-slate-500 text-[11px]">
              Formula: Rapor ({{ settings?.report_weight || 60 }}%) + Ujian ({{ settings?.exam_weight || 40 }}%)
            </span>
          </template>
        </div>
        <div class="text-[11px] font-bold text-slate-500">
          Total: <strong class="text-slate-800">{{ filteredLedgerStudents.length }}</strong> siswa
        </div>
      </div>

      <!-- Table Matrix Leger -->
      <div class="overflow-x-auto max-h-[70vh]">
        <table class="w-full text-xs text-left border-collapse">
          <thead class="sticky top-0 z-20 bg-slate-100/95 backdrop-blur-xs border-b border-slate-200 text-[11px] font-black text-slate-600 uppercase tracking-wider text-center">
            <tr>
              <th rowspan="2" class="py-3 px-3 w-10 text-center border-r border-slate-200/80 sticky left-0 z-30 bg-slate-100">No</th>
              <th rowspan="2" class="py-3 px-3 text-left min-w-[200px] border-r border-slate-200/80 sticky left-10 z-30 bg-slate-100">
                Nama Siswa & NISN
              </th>
              <th rowspan="2" class="py-3 px-2 w-16 border-r border-slate-200/80 sticky left-[240px] z-30 bg-slate-100">Kelas</th>
              <th :colspan="subjects.length" class="py-2.5 px-2 bg-emerald-50 text-emerald-900 border-r border-slate-200/80 font-black">
                {{ currentLedgerModeObj.title.toUpperCase() }} (SELURUH MATA PELAJARAN)
              </th>
              <th rowspan="2" class="py-3 px-2 w-20 bg-indigo-50 text-indigo-900 border-r border-slate-200/80">Total</th>
              <th rowspan="2" class="py-3 px-2 w-20 bg-amber-50 text-amber-900 border-r border-slate-200/80">Rata-Rata</th>
              <th rowspan="2" class="py-3 px-2 w-24 bg-slate-100 border-r border-slate-200/80">Kelengkapan</th>
              <th rowspan="2" class="py-3 px-2 w-16 bg-slate-100">Aksi</th>
            </tr>
            <tr class="text-[10px] font-bold text-slate-600 bg-slate-50 border-b border-slate-200">
              <th
                v-for="sub in subjects"
                :key="'th-ledger-' + sub.id"
                class="py-2 px-2 min-w-[65px] border-r border-slate-200/60"
                :title="sub.name"
              >
                <div class="font-bold truncate max-w-[80px]">{{ sub.code || sub.name.substring(0, 8) }}</div>
                <div v-if="isSubjectPractical(sub.id)" class="text-[9px] text-purple-700 font-normal">🔬 Praktik</div>
                <div v-else class="text-[9px] text-slate-400 font-normal">📝 Teori</div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr
              v-for="(stu, idx) in filteredLedgerStudents"
              :key="'ledger-row-' + stu.id"
              class="hover:bg-slate-50/80 transition-colors"
            >
              <td class="py-2.5 px-3 text-center text-slate-400 font-mono font-bold border-r border-slate-100 sticky left-0 bg-white z-10">
                {{ idx + 1 }}
              </td>
              <td class="py-2.5 px-3 border-r border-slate-100 sticky left-10 bg-white z-10">
                <div class="font-bold text-slate-800 text-[13px] leading-tight truncate max-w-[200px]">{{ stu.full_name }}</div>
                <div class="text-[10px] text-slate-400 font-mono mt-0.5">NISN: {{ stu.nisn || '-' }}</div>
              </td>
              <td class="py-2.5 px-2 text-center font-bold text-slate-600 border-r border-slate-100 sticky left-[240px] bg-white z-10">
                {{ stu.class_name }}
              </td>

              <!-- Subject Grades based on active ledgerMode -->
              <td
                v-for="sub in subjects"
                :key="'ledger-cell-' + stu.id + '-' + sub.id"
                class="py-2 px-1 text-center font-mono font-bold text-xs border-r border-slate-100"
                :class="getStudentGradeRecord(stu.id, sub.id)?.[ledgerMode] !== null && getStudentGradeRecord(stu.id, sub.id)?.[ledgerMode] !== undefined && getStudentGradeRecord(stu.id, sub.id)?.[ledgerMode] !== '' ? 'text-slate-800' : 'text-slate-300'"
              >
                <span
                  v-if="getStudentGradeRecord(stu.id, sub.id)?.[ledgerMode] !== null && getStudentGradeRecord(stu.id, sub.id)?.[ledgerMode] !== undefined && getStudentGradeRecord(stu.id, sub.id)?.[ledgerMode] !== ''"
                  class="px-1.5 py-0.5 rounded"
                  :class="parseFloat(getStudentGradeRecord(stu.id, sub.id)?.[ledgerMode]) >= 75 ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-50 text-slate-700'"
                >
                  {{ formatScore(getStudentGradeRecord(stu.id, sub.id)?.[ledgerMode]) }}
                </span>
                <span v-else class="text-slate-300">-</span>
              </td>

              <!-- Total based on active ledgerMode -->
              <td class="py-2 px-2 text-center font-mono font-black text-indigo-900 bg-indigo-50/40 border-r border-slate-100">
                {{ formatScore(getStudentTotalScore(stu.id, ledgerMode)) }}
              </td>

              <!-- Average based on active ledgerMode -->
              <td class="py-2 px-2 text-center font-mono font-black text-amber-950 bg-amber-50/60 border-r border-slate-100">
                <span class="px-2 py-0.5 rounded bg-amber-100/90 text-amber-900">
                  {{ formatScore(getStudentOverallAverage(stu.id, ledgerMode)) }}
                </span>
              </td>

              <!-- Kelengkapan based on active ledgerMode -->
              <td class="py-2 px-2 text-center border-r border-slate-100">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                  :class="getStudentCompletedSubjectCount(stu.id, ledgerMode) === subjects.length ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                >
                  {{ getStudentCompletedSubjectCount(stu.id, ledgerMode) }}/{{ subjects.length }}
                </span>
              </td>

              <!-- Action -->
              <td class="py-2 px-2 text-center">
                <button
                  type="button"
                  @click="openPrintModal(stu)"
                  class="px-2 py-1 bg-slate-100 hover:bg-indigo-50 text-indigo-700 font-bold rounded text-[11px] transition-colors cursor-pointer"
                  title="Lihat & Cetak Transkrip Nilai Siswa"
                >
                  Cetak
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 4: PUSAT CETAK IJAZAH & TRANSKRIP NILAI RESMI KEMENAG RI -->
    <div v-if="activeTab === 'transcripts'" class="space-y-4 no-print">
      <!-- Banner Header -->
      <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 rounded-3xl p-5 sm:p-6 text-white shadow-lg border border-purple-800/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1.5 max-w-2xl">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-500/20 text-purple-200 border border-purple-400/30">
              Format Resmi Kemenag RI
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
              2 Lembar: Muka & Belakang
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-400/30">
              + SKL Sementara
            </span>
          </div>
          <h2 class="text-base sm:text-xl font-black font-lexend tracking-wide flex items-center gap-2">
            <GraduationCap class="w-6 h-6 text-purple-400 flex-shrink-0" />
            <span>Pusat Cetak Ijazah, Transkrip Nilai & SKL (Kelas 9)</span>
          </h2>
          <p class="text-xs text-purple-200/80 leading-relaxed">
            Kelola pencetakan Ijazah resmi (Lembar 1: Muka & Lembar 2: Daftar Nilai) serta Surat Keterangan Lulus (SKL) sementara. Dilengkapi validasi kesiapan data, nomor seri otomatis, dan cetak massal 1 kelas.
          </p>
        </div>

        <!-- Tombol Aksi di Banner -->
        <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
          <!-- Tombol Cetak Massal Rombel -->
          <div class="relative group">
            <button
              type="button"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-black rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>🖨️ Cetak Massal (1 Rombel)</span>
            </button>
            <!-- Dropdown Menu Cetak Massal -->
            <div class="absolute right-0 top-full mt-1 hidden group-hover:block z-30 w-64 bg-white rounded-2xl shadow-xl border border-slate-200 p-2 text-slate-800 space-y-1 animate-in fade-in zoom-in-95">
              <div class="px-2 py-1 text-[10px] font-black uppercase text-slate-400 border-b border-slate-100 mb-1">
                Pilih Format Cetak Massal ({{ filteredTranscriptStudents.length }} Siswa)
              </div>
              <button
                type="button"
                @click="openBatchPrintModal('both')"
                class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold hover:bg-emerald-50 hover:text-emerald-800 transition-colors flex items-center gap-2 cursor-pointer"
              >
                <span>📑 Cetak 2 Lembar Lengkap</span>
              </button>
              <button
                type="button"
                @click="openBatchPrintModal('page1')"
                class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold hover:bg-slate-100 hover:text-slate-800 transition-colors flex items-center gap-2 cursor-pointer"
              >
                <span>📄 Cetak Lembar 1 (Muka) Saja</span>
              </button>
              <button
                type="button"
                @click="openBatchPrintModal('page2')"
                class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold hover:bg-purple-50 hover:text-purple-800 transition-colors flex items-center gap-2 cursor-pointer"
              >
                <span>📊 Cetak Lembar 2 (Nilai) Saja</span>
              </button>
              <button
                type="button"
                @click="openBatchPrintModal('skl')"
                class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold hover:bg-amber-50 hover:text-amber-800 transition-colors flex items-center gap-2 cursor-pointer border-t border-slate-100"
              >
                <span>📜 Cetak SKL Sementara Saja</span>
              </button>
            </div>
          </div>

          <button
            type="button"
            @click="openSettingsModal"
            class="px-4 py-2 bg-white/10 hover:bg-white/20 active:bg-white/30 text-white text-xs font-bold rounded-xl border border-white/20 transition-all flex items-center gap-2 cursor-pointer shadow-sm"
          >
            <Sliders class="w-4 h-4 text-purple-300" />
            <span>Atur Seri & Titimangsa</span>
          </button>
        </div>
      </div>

      <!-- Filter Kesiapan Data & Pencarian -->
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
        <!-- Baris 1: Status Kesiapan Cetak (Pre-flight Validation Filter) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
          <div class="flex items-center gap-1.5 flex-wrap">
            <span class="text-[11px] font-black text-slate-500 uppercase tracking-wider mr-1">Status Kesiapan:</span>
            <button
              type="button"
              @click="readinessFilter = 'all'"
              class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="readinessFilter === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            >
              <span>Semua Siswa</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20">{{ students.length }}</span>
            </button>
            <button
              type="button"
              @click="readinessFilter = 'ready'"
              class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="readinessFilter === 'ready' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200'"
            >
              <span>🟢 Siap Cetak (Lengkap)</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-700/20 font-mono">{{ countReady }}</span>
            </button>
            <button
              type="button"
              @click="readinessFilter = 'warning'"
              class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="readinessFilter === 'warning' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200'"
              title="Data dan nilai sudah 100% terisi, tetapi foto digital belum diupload (siap foto fisik)"
            >
              <span>🟡 Foto Fisik</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-700/20 font-mono">{{ countWarning }}</span>
            </button>
            <button
              type="button"
              @click="readinessFilter = 'incomplete'"
              class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="readinessFilter === 'incomplete' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200'"
              title="Nilai atau data identitas siswa belum lengkap"
            >
              <span>🔴 Data Belum Lengkap</span>
              <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-700/20 font-mono">{{ countIncomplete }}</span>
            </button>
          </div>

          <!-- Switcher Kartu vs Tabel -->
          <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/60 text-xs flex-shrink-0">
            <button
              type="button"
              @click="transcriptViewMode = 'cards'"
              class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer"
              :class="transcriptViewMode === 'cards' ? 'bg-white text-purple-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
            >
              Kartu
            </button>
            <button
              type="button"
              @click="transcriptViewMode = 'table'"
              class="px-2.5 py-1 rounded-lg font-bold transition-all cursor-pointer"
              :class="transcriptViewMode === 'table' ? 'bg-white text-purple-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
            >
              Tabel
            </button>
          </div>
        </div>

        <!-- Baris 2: Search Box & Ringkasan -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="relative w-full max-w-md">
            <Search class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" />
            <input
              type="text"
              v-model="transcriptSearch"
              placeholder="Cari siswa berdasarkan nama, NISN, NIS, atau no seri..."
              class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 placeholder-slate-400 focus:outline-purple-500"
            />
          </div>

          <div class="text-xs text-slate-500 font-medium">
            Menampilkan: <strong class="text-purple-700 font-bold">{{ filteredTranscriptStudents.length }}</strong> dari {{ students.length }} siswa
          </div>
        </div>
      </div>

      <!-- VIEW MODE 1: KARTU SISWA (CARDS) -->
      <div v-if="transcriptViewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="stu in filteredTranscriptStudents"
          :key="'card-' + stu.id"
          class="bg-white rounded-2xl p-4 sm:p-5 border shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4"
          :class="getStudentValidation(stu).status === 'incomplete' ? 'border-rose-200 bg-rose-50/10' : 'border-slate-100'"
        >
          <div>
            <!-- Status Badge Kesiapan -->
            <div class="flex items-center justify-between gap-2 mb-3">
              <span
                class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider flex items-center gap-1"
                :class="{
                  'bg-emerald-50 text-emerald-800 border border-emerald-200': getStudentValidation(stu).status === 'ready',
                  'bg-amber-50 text-amber-800 border border-amber-200': getStudentValidation(stu).status === 'warning',
                  'bg-rose-50 text-rose-800 border border-rose-200': getStudentValidation(stu).status === 'incomplete',
                }"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="{
                  'bg-emerald-500': getStudentValidation(stu).status === 'ready',
                  'bg-amber-500': getStudentValidation(stu).status === 'warning',
                  'bg-rose-500': getStudentValidation(stu).status === 'incomplete',
                }"></span>
                <span>
                  {{ getStudentValidation(stu).status === 'ready' ? 'Siap Cetak 100%' : (getStudentValidation(stu).status === 'warning' ? 'Siap (Foto Fisik)' : 'Belum Lengkap') }}
                </span>
              </span>

              <!-- Nomor Seri Ijazah Badge -->
              <span class="text-[10px] font-mono text-slate-500 font-bold truncate max-w-[150px]" :title="stu.certificate_number">
                {{ stu.certificate_number ? stu.certificate_number : 'No. Seri: (Otomatis)' }}
              </span>
            </div>

            <!-- Header Siswa -->
            <div class="flex items-start gap-3">
              <div class="w-12 h-14 rounded-lg bg-slate-100 border border-slate-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                <img
                  v-if="stu.photo_url"
                  :src="stu.photo_url"
                  :alt="stu.full_name"
                  class="w-full h-full object-cover"
                />
                <span v-else class="text-[9px] font-bold text-slate-400 text-center leading-tight">
                  FOTO<br/>3x4
                </span>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-slate-800 text-sm leading-snug truncate" :title="stu.full_name">
                  {{ stu.full_name }}
                </h3>
                <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                  NISN: <span class="font-bold text-slate-700">{{ stu.nisn || '-' }}</span> &bull; NIS: {{ stu.nis || '-' }}
                </p>

                <!-- Nomor Blanko & Quick Edit Button -->
                <div class="mt-1 flex items-center justify-between gap-1 p-1.5 bg-slate-50 rounded-lg border border-slate-100 text-[10px]">
                  <div class="font-mono truncate" :title="stu.certificate_number || 'Belum diisi (Otomatis)'">
                    <span class="text-slate-400">Blanko:</span>
                    <span class="font-bold text-slate-700 ml-1">{{ stu.certificate_number || 'Otomatis' }}</span>
                  </div>
                  <button
                    type="button"
                    @click="openEditSerialModal(stu)"
                    class="p-1 text-slate-400 hover:text-emerald-700 hover:bg-white rounded transition-colors cursor-pointer flex-shrink-0"
                    title="Edit Nomor Ijazah & SKL Siswa Ini"
                  >
                    <Pencil class="w-3 h-3" />
                  </button>
                </div>

                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                  <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700">
                    Kelas {{ stu.class_name }}
                  </span>
                  <span
                    class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                    :class="getStudentCompletedSubjectCount(stu.id) === subjects.length ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                  >
                    {{ getStudentCompletedSubjectCount(stu.id) }}/{{ subjects.length }} Mapel Terisi
                  </span>
                </div>
              </div>
            </div>

            <!-- Peringatan jika data belum lengkap -->
            <div v-if="getStudentValidation(stu).status === 'incomplete'" class="mt-2.5 p-2 bg-rose-50 border border-rose-200 rounded-xl text-[10px] text-rose-800">
              <span class="font-bold">⚠️ Belum lengkap:</span> {{ getStudentValidation(stu).missing.join(', ') }}
            </div>

            <!-- Ringkasan Nilai Siswa -->
            <div class="grid grid-cols-3 gap-2 mt-3 p-2.5 bg-slate-50 rounded-xl text-center border border-slate-100">
              <div>
                <span class="block text-[9px] font-bold text-slate-400 uppercase">R-Rapor</span>
                <span class="text-xs font-black font-mono text-emerald-700">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'avg_semester')) }}
                </span>
              </div>
              <div>
                <span class="block text-[9px] font-bold text-slate-400 uppercase">N-Ujian</span>
                <span class="text-xs font-black font-mono text-indigo-700">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'final_exam_score')) }}
                </span>
              </div>
              <div>
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Nilai Ijazah</span>
                <span class="text-xs font-black font-mono text-purple-800">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'final_grade')) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Tombol Aksi Cetak Ijazah -->
          <div class="pt-3 border-t border-slate-100 space-y-2">
            <!-- Tombol Utama: Cetak 2 Lembar -->
            <button
              type="button"
              @click="openPrintModal(stu, 'both')"
              class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer"
              title="Cetak Ijazah Lengkap Bolak-Balik (Lembar 1 & Lembar 2)"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Ijazah (2 Lembar Lengkap)</span>
            </button>

            <!-- Tombol Pilihan Per Lembar & SKL -->
            <div class="grid grid-cols-3 gap-1.5">
              <button
                type="button"
                @click="openPrintModal(stu, 'page1')"
                class="py-1.5 px-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold rounded-lg border border-slate-200 transition-colors flex items-center justify-center gap-1 cursor-pointer"
                title="Cetak Lembar Muka (Identitas Siswa) Saja"
              >
                <span>📄 Muka</span>
              </button>
              <button
                type="button"
                @click="openPrintModal(stu, 'page2')"
                class="py-1.5 px-1 bg-purple-50 hover:bg-purple-100 text-purple-800 text-[10px] font-bold rounded-lg border border-purple-200 transition-colors flex items-center justify-center gap-1 cursor-pointer"
                title="Cetak Lembar Belakang (Daftar Nilai) Saja"
              >
                <span>📊 Nilai</span>
              </button>
              <button
                type="button"
                @click="openPrintModal(stu, 'skl')"
                class="py-1.5 px-1 bg-amber-50 hover:bg-amber-100 text-amber-800 text-[10px] font-bold rounded-lg border border-amber-200 transition-colors flex items-center justify-center gap-1 cursor-pointer"
                title="Cetak Surat Keterangan Lulus (SKL) Sementara"
              >
                <span>📜 SKL</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- VIEW MODE 2: TABEL SISWA (TABLE) -->
      <div v-else class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left border-collapse">
            <thead class="bg-slate-100/80 border-b border-slate-200 text-[11px] font-black text-slate-600 uppercase">
              <tr>
                <th class="py-3 px-3 text-center w-10">No</th>
                <th class="py-3 px-3 text-left">Nama Siswa</th>
                <th class="py-3 px-2 text-center w-28">NISN / NIS</th>
                <th class="py-3 px-2 text-center w-20">Kelas</th>
                <th class="py-3 px-2 text-center w-28">Status Kesiapan</th>
                <th class="py-3 px-2 text-center w-20">R-Rapor</th>
                <th class="py-3 px-2 text-center w-20">N-Ujian</th>
                <th class="py-3 px-2 text-center w-20 bg-purple-50 text-purple-900">Nilai Akhir</th>
                <th class="py-3 px-3 text-center w-64">Aksi Cetak</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="(stu, idx) in filteredTranscriptStudents"
                :key="'tbl-' + stu.id"
                class="hover:bg-slate-50 transition-colors"
                :class="getStudentValidation(stu).status === 'incomplete' ? 'bg-rose-50/20' : ''"
              >
                <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="py-2.5 px-3">
                  <div class="font-bold text-slate-800">{{ stu.full_name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1.5 mt-0.5">
                    <span>{{ stu.certificate_number || 'No Seri: Otomatis' }}</span>
                    <button
                      type="button"
                      @click="openEditSerialModal(stu)"
                      class="text-slate-400 hover:text-emerald-700 transition-colors cursor-pointer"
                      title="Edit Nomor Ijazah & SKL Siswa Ini"
                    >
                      <Pencil class="w-3 h-3" />
                    </button>
                  </div>
                </td>
                <td class="py-2.5 px-2 text-center font-mono text-slate-600">
                  <div>{{ stu.nisn || '-' }}</div>
                  <div class="text-[10px] text-slate-400">{{ stu.nis || '-' }}</div>
                </td>
                <td class="py-2.5 px-2 text-center font-bold text-slate-700">{{ stu.class_name }}</td>
                <td class="py-2.5 px-2 text-center">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap"
                    :class="{
                      'bg-emerald-100 text-emerald-800': getStudentValidation(stu).status === 'ready',
                      'bg-amber-100 text-amber-800': getStudentValidation(stu).status === 'warning',
                      'bg-rose-100 text-rose-800': getStudentValidation(stu).status === 'incomplete',
                    }"
                  >
                    {{ getStudentValidation(stu).status === 'ready' ? '🟢 Siap' : (getStudentValidation(stu).status === 'warning' ? '🟡 Foto Fisik' : '🔴 Kurang') }}
                  </span>
                </td>
                <td class="py-2.5 px-2 text-center font-mono font-bold text-emerald-700">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'avg_semester')) }}
                </td>
                <td class="py-2.5 px-2 text-center font-mono font-bold text-indigo-700">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'final_exam_score')) }}
                </td>
                <td class="py-2.5 px-2 text-center font-mono font-black text-purple-900 bg-purple-50/40">
                  {{ formatScore(getStudentOverallAverage(stu.id, 'final_grade')) }}
                </td>
                <td class="py-2.5 px-3 text-center">
                  <div class="flex items-center justify-center gap-1 flex-wrap">
                    <button
                      type="button"
                      @click="openPrintModal(stu, 'both')"
                      class="px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[10px] transition-colors flex items-center gap-1 cursor-pointer shadow-xs"
                      title="Cetak 2 Lembar Lengkap"
                    >
                      <Printer class="w-3 h-3" />
                      <span>2 Lembar</span>
                    </button>
                    <button
                      type="button"
                      @click="openPrintModal(stu, 'page1')"
                      class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[10px] transition-colors cursor-pointer"
                      title="Cetak Lembar 1 Muka"
                    >
                      Muka
                    </button>
                    <button
                      type="button"
                      @click="openPrintModal(stu, 'page2')"
                      class="px-2 py-1 bg-purple-50 hover:bg-purple-100 text-purple-800 font-bold rounded-lg text-[10px] transition-colors cursor-pointer"
                      title="Cetak Lembar 2 Nilai"
                    >
                      Nilai
                    </button>
                    <button
                      type="button"
                      @click="openPrintModal(stu, 'skl')"
                      class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-lg text-[10px] transition-colors cursor-pointer"
                      title="Cetak SKL Sementara"
                    >
                      SKL
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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

          <!-- 3. Checklist Mapel yang Digunakan (Aktif) untuk Ijazah & PDUM -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider">
                3. Mata Pelajaran yang Digunakan (Ijazah & PDUM)
              </label>
              <div class="flex items-center gap-2 text-xs">
                <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                  {{ tempSettings.active_subject_ids.length }} dari {{ allSubjects.length }} Aktif
                </span>
                <button
                  type="button"
                  @click="selectAllActiveSubjects"
                  class="text-[11px] font-bold text-indigo-600 hover:underline cursor-pointer"
                >
                  Pilih Semua
                </button>
                <span class="text-slate-300">&bull;</span>
                <button
                  type="button"
                  @click="deselectAllActiveSubjects"
                  class="text-[11px] font-bold text-slate-500 hover:underline cursor-pointer"
                >
                  Batal Semua
                </button>
              </div>
            </div>
            <p class="text-[11px] text-slate-400">
              Hilangkan centang jika mata pelajaran tidak diajarkan atau tidak diikutsertakan ke dalam transkrip nilai ijazah.
            </p>
            <div class="max-h-48 overflow-y-auto p-2 border border-slate-200 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50/50">
              <label
                v-for="sub in allSubjects"
                :key="'active-' + sub.id"
                class="flex items-center gap-2 p-1.5 bg-white rounded-lg border border-slate-100 text-xs font-medium cursor-pointer hover:bg-slate-50 transition-colors"
                :class="tempSettings.active_subject_ids.includes(sub.id) ? 'border-emerald-200 bg-emerald-50/20' : 'opacity-60'"
              >
                <input
                  type="checkbox"
                  :value="sub.id"
                  v-model="tempSettings.active_subject_ids"
                  class="rounded text-emerald-600 focus:ring-emerald-500"
                />
                <span class="truncate font-semibold text-slate-800">{{ sub.name }}</span>
              </label>
            </div>
          </div>

          <!-- 4. Checklist Mapel yang Memiliki Praktik -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider">
                4. Mata Pelajaran yang Ada Ujian Praktik
              </label>
              <span class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">
                {{ tempSettings.practical_subject_ids.length }} Mapel Praktik
              </span>
            </div>
            <p class="text-[11px] text-slate-400">
              Hanya menampilkan mapel yang aktif digunakan di atas.
            </p>
            <div class="max-h-44 overflow-y-auto p-2 border border-slate-200 rounded-xl grid grid-cols-1 sm:grid-cols-2 gap-2 bg-slate-50/50">
              <label
                v-for="sub in allSubjects.filter(s => tempSettings.active_subject_ids.includes(s.id))"
                :key="'prac-' + sub.id"
                class="flex items-center gap-2 p-1.5 bg-white rounded-lg border border-slate-100 text-xs font-medium cursor-pointer hover:bg-slate-50"
              >
                <input
                  type="checkbox"
                  :value="sub.id"
                  v-model="tempSettings.practical_subject_ids"
                  class="rounded text-purple-600 focus:ring-purple-500"
                />
                <span class="truncate font-semibold text-slate-700">{{ sub.name }}</span>
              </label>
            </div>
          </div>

          <!-- 5. Pengaturan Titimangsa & Format Blanko Ijazah Resmi -->
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/60 space-y-3">
            <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center justify-between">
              <span>5. Titimangsa & Blanko Ijazah (Standar Kemenag)</span>
              <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                Format 2 Lembar
              </span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Prefix / Format No. Seri Ijazah</label>
                <input
                  type="text"
                  v-model="tempSettings.certificate_number_prefix"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 font-mono"
                  placeholder="Contoh: MTs.10.01/PP.01.1/"
                />
                <span class="text-[10px] text-slate-400 mt-0.5 block">Diikuti nomor seri / NISN siswa di belakangnya.</span>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1 flex items-center justify-between">
                  <span>Tahun Pelajaran Kelulusan (Kelas 9)</span>
                  <span class="text-[9px] text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded font-bold border border-emerald-200">
                    Acuan Induk 3 Tahun
                  </span>
                </label>
                <input
                  type="text"
                  v-model="tempSettings.academic_year"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 font-mono"
                  placeholder="Contoh: 2026/2027"
                />
                <!-- Box Preview Kronologi 3 Tahun Belajar -->
                <div class="mt-2 p-2 bg-white rounded-xl border border-slate-200/80 text-[10px] space-y-1 shadow-2xs">
                  <div class="font-bold text-slate-700 flex items-center justify-between">
                    <span>Otomatisasi Kronologi 6 Semester:</span>
                    <span class="text-[9px] text-slate-400">3 Tahun Tempuh</span>
                  </div>
                  <div class="flex items-center justify-between text-slate-600 border-t border-slate-100 pt-1">
                    <span>&bull; Kelas 7 (Sem 1 & 2):</span>
                    <span class="font-mono font-bold text-blue-700">{{ calculateAcademicYearForSemester('sem_1', tempSettings.academic_year) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-slate-600">
                    <span>&bull; Kelas 8 (Sem 3 & 4):</span>
                    <span class="font-mono font-bold text-amber-700">{{ calculateAcademicYearForSemester('sem_3', tempSettings.academic_year) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-slate-600">
                    <span>&bull; Kelas 9 (Sem 5, 6, & Ujian):</span>
                    <span class="font-mono font-bold text-emerald-700">{{ calculateAcademicYearForSemester('sem_5', tempSettings.academic_year) }}</span>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Nomor Statistik Madrasah (NSM)</label>
                <input
                  type="text"
                  v-model="tempSettings.nsm"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 font-mono"
                  placeholder="Contoh: 121232010012"
                />
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Nomor Pokok Sekolah Nasional (NPSN)</label>
                <input
                  type="text"
                  v-model="tempSettings.npsn"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 font-mono"
                  placeholder="Contoh: 20277259"
                />
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Nama Kepala Madrasah</label>
                <input
                  type="text"
                  v-model="tempSettings.principal_name"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500"
                  placeholder="Nama Lengkap & Gelar Kepala Madrasah"
                />
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-500 mb-1">NIP Kepala Madrasah</label>
                <input
                  type="text"
                  v-model="tempSettings.principal_nip"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 font-mono"
                  placeholder="Isi '-' jika bukan PNS/non-NIP"
                />
              </div>

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

            <!-- Toggle Pas Foto Siswa -->
            <div class="pt-2 border-t border-slate-200/60">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="tempSettings.show_student_photo"
                  class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4"
                />
                <span class="text-xs font-bold text-slate-700">Tampilkan Pas Foto Siswa di Lembar 1 Ijazah</span>
              </label>
              <p class="text-[11px] text-slate-400 ml-6 mt-0.5">
                Jika dicentang, foto profil siswa pada sistem akan dimuat di lembar 1. Jika tidak dicentang, akan dicetak kotak pas foto 3x4 kosong bertuliskan cap tiga jari (untuk foto fisik).
              </p>
            </div>
          </div>

          <!-- 6. Penomoran Seri Ijazah & SKL Otomatis (Batch Auto-Numbering) -->
          <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-200/80 space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-black text-purple-900 uppercase tracking-wider">
                6. Penomoran Seri Ijazah & SKL Otomatis
              </h4>
              <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full border border-purple-200">
                Auto-Generate
              </span>
            </div>
            <p class="text-[11px] text-slate-500">
              Generate nomor seri blanko ijazah fisik dan nomor surat SKL secara massal berurutan ke seluruh siswa kelas 9 yang ada di database.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Nomor Awal Blanko</label>
                <input
                  type="number"
                  min="1"
                  v-model.number="serialForm.cert_start"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 font-mono"
                  placeholder="Contoh: 1"
                />
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Digit Nomor</label>
                <select
                  v-model.number="serialForm.cert_digits"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800"
                >
                  <option :value="3">3 Digit (001, 002...)</option>
                  <option :value="4">4 Digit (0001, 0002...)</option>
                  <option :value="6">6 Digit (000001...)</option>
                </select>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Urutkan Siswa</label>
                <select
                  v-model="serialForm.order_by"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800"
                >
                  <option value="full_name">Nama Siswa (A - Z)</option>
                  <option value="nis">Nomor Induk (NIS)</option>
                  <option value="nisn">NISN Siswa</option>
                </select>
              </div>
            </div>

            <div class="pt-2 flex justify-end">
              <button
                type="button"
                @click="generateSerialNumbers"
                :disabled="isGeneratingSerial"
                class="px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
              >
                <span>{{ isGeneratingSerial ? 'Men-generate...' : '⚡ Terapkan Penomoran Seri Sekarang' }}</span>
              </button>
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

    <!-- MODAL 2: CETAK BLANKO IJAZAH 2 LEMBAR RESMI (STANDAR KEMENAG RI) -->
    <div
      v-if="showPrintModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 overflow-y-auto no-print"
    >
      <div class="bg-white rounded-3xl max-w-5xl w-full p-4 sm:p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[95vh] flex flex-col">
        <!-- Modal Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 gap-3 flex-shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
              <Printer class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-black text-slate-800 text-sm sm:text-base leading-snug">
                <span v-if="isBatchPrint">
                  Pratinjau Cetak Massal ({{ printStudents.length }} Siswa &bull; Kelas {{ selectedClassName || 'Semua' }})
                </span>
                <span v-else>
                  Pratinjau Cetak Ijazah & SKL &mdash; {{ activePrintStudent?.full_name || 'Madrasah Tsanawiyah' }}
                </span>
              </h3>
              <p class="text-[11px] text-slate-400">
                Format resmi Kemenag RI &bull; Ukuran A4 Portrait &bull;
                <span v-if="printMode === 'both'">2 Lembar Lengkap (Muka & Belakang)</span>
                <span v-else-if="printMode === 'page1'">Lembar 1 (Muka / Identitas Siswa)</span>
                <span v-else-if="printMode === 'page2'">Lembar 2 (Belakang / Daftar Nilai)</span>
                <span v-else-if="printMode === 'skl'">Surat Keterangan Lulus (SKL) Sementara</span>
              </p>
            </div>
          </div>

          <!-- Mode Cetak Tabs & Actions -->
          <div class="flex flex-wrap items-center gap-2">
            <!-- Mode Switcher -->
            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/60 text-xs font-bold">
              <button
                type="button"
                @click="printMode = 'both'"
                class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
                :class="printMode === 'both' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
              >
                <span>📑 2 Lembar (Lengkap)</span>
              </button>
              <button
                type="button"
                @click="printMode = 'page1'"
                class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
                :class="printMode === 'page1' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
              >
                <span>📄 Lembar 1 (Muka)</span>
              </button>
              <button
                type="button"
                @click="printMode = 'page2'"
                class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
                :class="printMode === 'page2' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
              >
                <span>📊 Lembar 2 (Nilai)</span>
              </button>
              <button
                type="button"
                @click="printMode = 'skl'"
                class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
                :class="printMode === 'skl' ? 'bg-white text-emerald-800 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
              >
                <span>📜 SKL Sementara</span>
              </button>
            </div>

            <button
              type="button"
              @click="triggerPrint"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5 shadow-md shadow-emerald-600/20 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Sekarang (Ctrl + P)</span>
            </button>

            <button
              type="button"
              @click="showPrintModal = false"
              class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Print Sheet Content Container -->
        <div class="flex-1 overflow-y-auto p-2 sm:p-6 bg-slate-100/70 border border-slate-200 rounded-2xl">
          <!-- Panduan Pengaturan Printer di Browser (No-Print) -->
          <div class="no-print max-w-[210mm] mx-auto mb-4 p-3.5 bg-sky-50 border border-sky-200 rounded-2xl text-xs text-sky-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-start gap-2.5">
              <div class="w-7 h-7 rounded-xl bg-sky-200/80 text-sky-800 flex items-center justify-center flex-shrink-0 font-bold text-sm">
                💡
              </div>
              <div>
                <div class="font-black text-sky-950">Tips Pengaturan Dialog Printer (Browser Print Ctrl+P):</div>
                <div class="text-[11px] text-sky-800 mt-0.5 flex flex-wrap items-center gap-x-4 gap-y-1">
                  <span>&bull; <strong>Ukuran Kertas:</strong> A4</span>
                  <span>&bull; <strong>Margin:</strong> Minimum / None</span>
                  <span>&bull; <strong>Skala:</strong> 100% (Default)</span>
                  <span>&bull; <strong>Centang Opsi:</strong> <span class="font-bold underline">"Background Graphics" / Grafik Latar Belakang</span></span>
                  <span>&bull; <strong>Hilangkan centang:</strong> "Headers & Footers"</span>
                </div>
              </div>
            </div>
            <button
              type="button"
              @click="triggerPrint"
              class="px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold text-xs flex-shrink-0 shadow-xs flex items-center gap-1.5 cursor-pointer transition-colors"
            >
              <Printer class="w-3.5 h-3.5" />
              <span>Buka Dialog Cetak</span>
            </button>
          </div>

          <div id="print-area" class="max-w-[210mm] mx-auto space-y-6 text-black">

            <!-- Loop Seluruh Siswa yang Dipilih untuk Dicetak (Single atau Batch 1 Rombel) -->
            <div
              v-for="(printStu, pIdx) in printStudents"
              :key="'print-stu-' + printStu.id"
              class="print-student-wrapper"
            >
              <!-- Pre-flight Validation Banner (Tampak di layar untuk verifikasi admin, otomatis tersembunyi saat diprint / no-print) -->
              <div
                v-if="!getStudentValidation(printStu).isComplete"
                class="no-print mb-4 p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-xs text-amber-900 shadow-xs flex items-start justify-between gap-3"
              >
                <div class="flex items-start gap-2.5">
                  <AlertTriangle class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
                  <div>
                    <div class="font-black text-amber-950 flex items-center gap-2">
                      <span>Verifikasi Data Siswa: {{ printStu.full_name }}</span>
                      <span class="text-[10px] bg-amber-200/80 text-amber-900 font-bold px-2 py-0.5 rounded-full">
                        {{ getStudentValidation(printStu).badgeText }}
                      </span>
                    </div>
                    <div class="text-[11px] text-amber-800 mt-1 space-y-0.5">
                      <div v-for="(reason, rIdx) in getStudentValidation(printStu).reasons" :key="'reason-' + rIdx">
                        &bull; {{ reason }}
                      </div>
                    </div>
                  </div>
                </div>
                <span class="text-[10px] font-mono font-bold text-amber-700 bg-white px-2 py-1 rounded-lg border border-amber-200 flex-shrink-0">
                  NISN: {{ printStu.nisn || '-' }}
                </span>
              </div>

              <!-- ========================================================================= -->
              <!-- LEMBAR 1: HALAMAN DEPAN (SERTIFIKAT IJAZAH MADRASAH TSANAWIYAH)           -->
              <!-- ========================================================================= -->
              <div
                v-if="printMode === 'both' || printMode === 'page1'"
                class="sheet-page bg-white p-8 sm:p-12 shadow-sm rounded-lg border-4 border-slate-800 relative font-serif"
                style="min-height: 275mm;"
              >
                <!-- Inner Ornate Double Border -->
                <div class="border-2 border-slate-700 p-6 sm:p-8 h-full flex flex-col justify-between relative">
                  
                  <!-- Corner Ornaments (CSS Corners) -->
                  <div class="absolute top-1 left-1 w-4 h-4 border-t-2 border-l-2 border-slate-800"></div>
                  <div class="absolute top-1 right-1 w-4 h-4 border-t-2 border-r-2 border-slate-800"></div>
                  <div class="absolute bottom-1 left-1 w-4 h-4 border-b-2 border-l-2 border-slate-800"></div>
                  <div class="absolute bottom-1 right-1 w-4 h-4 border-b-2 border-r-2 border-slate-800"></div>

                  <!-- Bagian Atas: Garuda Pancasila & Kop Surat Ijazah -->
                  <div class="text-center space-y-2">
                    <!-- Lambang Garuda Pancasila SVG Resmi -->
                    <div class="flex justify-center mb-1">
                      <svg class="w-16 h-16 sm:w-20 sm:h-20 text-amber-700" viewBox="0 0 100 100" fill="currentColor">
                        <path d="M50 4 C48 10 44 14 38 18 C32 10 24 10 14 14 C18 24 22 30 20 42 C14 38 8 36 2 38 C6 48 12 56 22 60 C18 64 12 68 8 74 C16 76 24 74 32 68 C30 76 30 84 34 92 C38 90 42 86 46 80 C48 84 50 88 50 92 C50 88 52 84 54 80 C58 86 62 90 66 92 C70 84 70 76 68 68 C76 74 84 76 92 74 C88 68 82 64 78 60 C88 56 94 48 98 38 C92 36 86 38 80 42 C78 30 82 24 86 14 C76 10 68 10 62 18 C56 14 52 10 50 4 Z" fill="#996515" />
                        <circle cx="50" cy="46" r="14" fill="#B8860B" />
                        <path d="M42 46 L58 46 M50 38 L50 54" stroke="#ffffff" stroke-width="2.5" />
                      </svg>
                    </div>

                    <h3 class="text-xs sm:text-sm font-bold tracking-[0.2em] uppercase text-slate-800">
                      KEMENTERIAN AGAMA REPUBLIK INDONESIA
                    </h3>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-[0.25em] uppercase text-black font-serif pt-1">
                      IJAZAH
                    </h1>
                    <h2 class="text-sm sm:text-base font-bold tracking-[0.15em] uppercase text-slate-800">
                      MADRASAH TSANAWIYAH
                    </h2>
                    <p class="text-xs sm:text-sm font-bold tracking-wider font-sans">
                      TAHUN PELAJARAN {{ settings?.academic_year || '2026/2027' }}
                    </p>
                    <p class="text-xs font-mono font-bold tracking-wider text-slate-700 pt-1">
                      Nomor: {{ printStu?.certificate_number || ((settings?.certificate_number_prefix || 'MTs.10.01/PP.01.1/') + (printStu?.nisn || printStu?.nis || '001')) }}
                    </p>
                  </div>

                  <!-- Bagian Tengah: Kalimat Pengantar & Biodata Siswa -->
                  <div class="my-6 space-y-4 text-xs sm:text-[13px] leading-relaxed">
                    <p class="text-justify indent-8">
                      Yang bertanda tangan di bawah ini, Kepala Madrasah Tsanawiyah
                      <strong>{{ schoolSetting?.school_name || 'Al-Hasanah' }}</strong>, menerangkan bahwa:
                    </p>

                    <!-- Biodata Siswa Table -->
                    <div class="pl-4 sm:pl-8 space-y-1.5 font-sans">
                      <div class="flex items-start">
                        <span class="w-48 sm:w-56 font-serif">nama</span>
                        <span class="w-4 text-center">:</span>
                        <span class="flex-1 font-bold font-serif uppercase tracking-wide text-sm sm:text-base">{{ printStu?.full_name }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="w-48 sm:w-56 font-serif">tempat dan tanggal lahir</span>
                        <span class="w-4 text-center">:</span>
                        <span class="flex-1 font-serif">{{ printStu?.birth_place || '-' }}, {{ formatDateIndo(printStu?.birth_date) }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="w-48 sm:w-56 font-serif">nama orang tua / wali</span>
                        <span class="w-4 text-center">:</span>
                        <span class="flex-1 font-serif">{{ printStu?.father_name || printStu?.mother_name || '-' }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="w-48 sm:w-56 font-serif">nomor induk siswa</span>
                        <span class="w-4 text-center">:</span>
                        <span class="flex-1 font-mono font-semibold">{{ printStu?.nis || '-' }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="w-48 sm:w-56 font-serif">nomor induk siswa nasional</span>
                        <span class="w-4 text-center">:</span>
                        <span class="flex-1 font-mono font-semibold">{{ printStu?.nisn || '-' }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="w-48 sm:w-56 font-serif">nomor pokok sekolah nasional</span>
                        <span class="w-4 text-center">:</span>
                        <span class="flex-1 font-mono font-semibold">{{ settings?.npsn || '20277259' }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="w-48 sm:w-56 font-serif">nomor statistik madrasah</span>
                        <span class="w-4 text-center">:</span>
                        <span class="flex-1 font-mono font-semibold">{{ settings?.nsm || '121232010012' }}</span>
                      </div>
                      <div class="flex items-start">
                        <span class="w-48 sm:w-56 font-serif">madrasah asal</span>
                        <span class="w-4 text-center">:</span>
                        <span class="flex-1 font-serif font-semibold">{{ schoolSetting?.school_name || 'MTs Al-Hasanah' }}</span>
                      </div>
                    </div>

                    <!-- Pernyataan Kelulusan -->
                    <div class="text-center pt-3 pb-1 space-y-2">
                      <div class="inline-block px-8 py-1.5 border-2 border-black font-serif font-black text-xl sm:text-2xl tracking-[0.3em] uppercase">
                        L U L U S
                      </div>
                      <p class="text-center text-xs sm:text-[13px] pt-1">
                        dari Madrasah Tsanawiyah setelah memenuhi seluruh kriteria kelulusan sesuai dengan peraturan perundang-undangan.
                      </p>
                    </div>
                  </div>

                  <!-- Bagian Bawah: Pas Foto & Titimangsa Pengesahan -->
                  <div class="pt-4 flex justify-between items-end text-xs sm:text-[13px]">
                    <!-- Pas Foto 3x4 Siswa -->
                    <div class="text-center w-36 sm:w-40 flex flex-col items-center">
                      <div
                        v-if="settings?.show_student_photo && printStu?.photo_url"
                        class="w-[3cm] h-[4cm] border-2 border-black p-0.5 bg-white shadow-xs overflow-hidden flex items-center justify-center relative"
                      >
                        <img
                          :src="printStu.photo_url"
                          :alt="printStu.full_name"
                          class="w-full h-full object-cover"
                        />
                      </div>
                      <div
                        v-else
                        class="w-[3cm] h-[4cm] border-2 border-dashed border-slate-400 bg-slate-50 flex flex-col items-center justify-center p-2 text-center select-none"
                      >
                        <span class="font-bold font-sans text-xs text-slate-700">PAS FOTO</span>
                        <span class="font-mono text-[11px] text-slate-600 font-bold">3 x 4 cm</span>
                        <span class="text-[8px] font-sans text-slate-400 mt-2 leading-tight">Cap tiga jari kiri menempel foto</span>
                      </div>
                      <p class="text-[9px] font-sans text-slate-500 mt-1 italic text-center">
                        (Cap Tiga Jari Kiri)
                      </p>
                    </div>

                    <!-- Titimangsa & Tanda Tangan Kepala Madrasah -->
                    <div class="text-center min-w-[220px] sm:min-w-[260px] space-y-1">
                      <p class="font-serif">
                        {{ settings?.date_place || 'Bogor' }}, {{ formatDateIndo(settings?.graduation_date) }}
                      </p>
                      <p class="font-bold font-serif">Kepala Madrasah,</p>
                      <div class="h-20 sm:h-24 flex items-center justify-center">
                        <!-- Ruang tanda tangan & stempel madrasah -->
                      </div>
                      <p class="font-bold font-serif underline text-sm sm:text-base leading-snug">
                        {{ settings?.principal_name || schoolSetting?.principal_name || 'H. Umar Usman Ali, S.Pd, S.Pd.I' }}
                      </p>
                      <p class="text-xs font-mono">
                        NIP. {{ settings?.principal_nip || schoolSetting?.principal_nip || '-' }}
                      </p>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Page Break Divider Antara Lembar 1 dan Lembar 2 (Hanya Saat Pratinjau 2 Lembar Lengkap) -->
              <div
                v-if="printMode === 'both'"
                class="page-break my-6 py-2 border-b-2 border-dashed border-slate-300 no-print flex items-center justify-center text-xs font-sans text-slate-400 uppercase tracking-widest font-bold"
              >
                --- Halaman 2: Lembar Belakang (Daftar Nilai Ijazah - {{ printStu.full_name }}) ---
              </div>

              <!-- ========================================================================= -->
              <!-- LEMBAR 2: HALAMAN BELAKANG (TRANSKRIP DAFTAR NILAI IJAZAH STANDAR KEMENAG) -->
              <!-- ========================================================================= -->
              <div
                v-if="printMode === 'both' || printMode === 'page2'"
                class="sheet-page bg-white p-8 sm:p-12 shadow-sm rounded-lg border border-slate-300 relative font-serif"
                style="min-height: 275mm;"
              >
                <!-- Kop Judul Transkrip Nilai -->
                <div class="text-center border-b-2 border-black pb-3 mb-4 space-y-0.5">
                  <h3 class="text-xs sm:text-sm font-bold tracking-wider uppercase text-slate-800">
                    KEMENTERIAN AGAMA REPUBLIK INDONESIA
                  </h3>
                  <h1 class="text-lg sm:text-xl font-black tracking-wider uppercase text-black font-serif">
                    DAFTAR NILAI MADRASAH TSANAWIYAH
                  </h1>
                  <p class="text-xs font-bold tracking-wider font-sans">
                    TAHUN PELAJARAN {{ settings?.academic_year || '2026/2027' }}
                  </p>
                  <p class="text-[11px] font-mono text-slate-700">
                    Nomor: {{ printStu?.certificate_number || ((settings?.certificate_number_prefix || 'MTs.10.01/PP.01.1/') + (printStu?.nisn || printStu?.nis || '001')) }}
                  </p>
                </div>

                <!-- Biodata Siswa Singkat -->
                <div class="text-xs sm:text-[12px] font-sans mb-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1">
                  <div class="flex">
                    <span class="w-32 font-serif">Nama Siswa</span>
                    <span class="w-3 text-center">:</span>
                    <span class="font-bold uppercase font-serif">{{ printStu?.full_name }}</span>
                  </div>
                  <div class="flex">
                    <span class="w-32 font-serif">Nomor Induk Siswa (NIS)</span>
                    <span class="w-3 text-center">:</span>
                    <span class="font-mono font-semibold">{{ printStu?.nis || '-' }}</span>
                  </div>
                  <div class="flex">
                    <span class="w-32 font-serif">Tempat, Tanggal Lahir</span>
                    <span class="w-3 text-center">:</span>
                    <span class="font-serif">{{ printStu?.birth_place || '-' }}, {{ formatDateIndo(printStu?.birth_date) }}</span>
                  </div>
                  <div class="flex">
                    <span class="w-32 font-serif">Nomor Induk Siswa Nasional</span>
                    <span class="w-3 text-center">:</span>
                    <span class="font-mono font-semibold">{{ printStu?.nisn || '-' }}</span>
                  </div>
                  <div class="flex sm:col-span-2">
                    <span class="w-32 font-serif">Madrasah Asal</span>
                    <span class="w-3 text-center">:</span>
                    <span class="font-serif font-semibold">{{ schoolSetting?.school_name || 'MTs Al-Hasanah' }}</span>
                  </div>
                </div>

                <!-- Tabel Transkrip Nilai Ijazah Resmi (Struktur Kurikulum Kemenag) -->
                <table class="w-full text-xs font-sans border-collapse border border-black mb-4">
                  <thead>
                    <tr class="bg-slate-100 text-center font-bold">
                      <th rowspan="2" class="border border-black p-1.5 w-8">No</th>
                      <th rowspan="2" class="border border-black p-1.5 text-left">Mata Pelajaran</th>
                      <th rowspan="2" class="border border-black p-1.5 w-24">Rata-rata Rapor<br/><span class="text-[9px] font-normal">(S1 s.d. S6)</span></th>
                      <th rowspan="2" class="border border-black p-1.5 w-24">Ujian Madrasah<br/><span class="text-[9px] font-normal">(UM / AM)</span></th>
                      <th colspan="2" class="border border-black p-1.5">Nilai Ijazah</th>
                    </tr>
                    <tr class="bg-slate-100 text-center font-bold">
                      <th class="border border-black p-1 w-16">Angka</th>
                      <th class="border border-black p-1 text-left px-2">Terbilang (Huruf)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(sub, i) in subjects" :key="'print-sub-' + sub.id">
                      <td class="border border-black p-1 text-center font-mono">{{ i + 1 }}</td>
                      <td class="border border-black p-1 font-medium">{{ sub.name }}</td>
                      <td class="border border-black p-1 text-center font-mono font-semibold">
                        {{ formatScore(getStudentGradeRecord(printStu?.id, sub.id)?.avg_semester) }}
                      </td>
                      <td class="border border-black p-1 text-center font-mono font-semibold">
                        {{ formatScore(getStudentGradeRecord(printStu?.id, sub.id)?.final_exam_score) }}
                      </td>
                      <td class="border border-black p-1 text-center font-mono font-bold">
                        {{ formatScore(getStudentGradeRecord(printStu?.id, sub.id)?.final_grade) }}
                      </td>
                      <td class="border border-black p-1 italic text-[11px] px-2 capitalize">
                        {{ terbilangScore(getStudentGradeRecord(printStu?.id, sub.id)?.final_grade) }}
                      </td>
                    </tr>

                    <!-- Rata-rata Total Keseluruhan -->
                    <tr class="bg-slate-100 font-bold border-t-2 border-black">
                      <td colspan="2" class="border border-black p-2 text-center uppercase tracking-wide">
                        RATA-RATA KESELURUHAN
                      </td>
                      <td class="border border-black p-2 text-center font-mono">
                        {{ formatScore(getStudentOverallAverage(printStu?.id, 'avg_semester')) }}
                      </td>
                      <td class="border border-black p-2 text-center font-mono">
                        {{ formatScore(getStudentOverallAverage(printStu?.id, 'final_exam_score')) }}
                      </td>
                      <td class="border border-black p-2 text-center font-mono font-black">
                        {{ formatScore(getStudentOverallAverage(printStu?.id, 'final_grade')) }}
                      </td>
                      <td class="border border-black p-2 italic text-[11px] px-2 capitalize font-semibold">
                        {{ terbilangScore(getStudentOverallAverage(printStu?.id, 'final_grade')) }}
                      </td>
                    </tr>
                  </tbody>
                </table>

                <!-- Titimangsa & Tanda Tangan Pengesahan -->
                <div class="flex justify-end text-xs sm:text-[12px] font-sans mt-6 pt-2">
                  <div class="text-center min-w-[220px] sm:min-w-[260px] space-y-1">
                    <p class="font-serif">
                      {{ settings?.date_place || 'Bogor' }}, {{ formatDateIndo(settings?.graduation_date) }}
                    </p>
                    <p class="font-bold font-serif">Kepala Madrasah,</p>
                    <div class="h-20 sm:h-24 flex items-center justify-center">
                      <!-- Ruang tanda tangan & cap stempel -->
                    </div>
                    <p class="font-bold font-serif underline text-sm sm:text-base leading-snug">
                      {{ settings?.principal_name || schoolSetting?.principal_name || 'H. Umar Usman Ali, S.Pd, S.Pd.I' }}
                    </p>
                    <p class="text-xs font-mono">
                      NIP. {{ settings?.principal_nip || schoolSetting?.principal_nip || '-' }}
                    </p>
                  </div>
                </div>

              </div>

              <!-- ========================================================================= -->
              <!-- LEMBAR 3: SURAT KETERANGAN LULUS (SKL) SEMENTARA RESMI                     -->
              <!-- ========================================================================= -->
              <div
                v-if="printMode === 'skl'"
                class="sheet-page bg-white p-8 sm:p-12 shadow-sm rounded-lg border border-slate-300 relative font-serif"
                style="min-height: 275mm;"
              >
                <!-- Kop Surat Resmi Madrasah -->
                <div class="flex items-center gap-4 pb-3 border-b-4 border-double border-black mb-4">
                  <!-- Logo Madrasah -->
                  <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
                    <img
                      v-if="schoolSetting?.logo_url"
                      :src="schoolSetting.logo_url"
                      alt="Logo Madrasah"
                      class="max-h-20 max-w-20 object-contain"
                    />
                    <div v-else class="w-16 h-16 rounded-full border-2 border-emerald-800 flex items-center justify-center text-emerald-800 font-bold text-xl font-sans">
                      MTs
                    </div>
                  </div>

                  <!-- Teks Header Kop Surat -->
                  <div class="flex-1 text-center font-sans">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">
                      {{ schoolSetting?.foundation_name || 'YAYASAN PENDIDIKAN ISLAM AL-HASANAH' }}
                    </h4>
                    <h2 class="text-lg sm:text-xl font-black uppercase tracking-wide text-black font-serif py-0.5">
                      MADRASAH TSANAWIYAH {{ schoolSetting?.school_name?.toUpperCase() || 'AL-HASANAH' }}
                    </h2>
                    <p class="text-[11px] font-bold text-slate-700">
                      NSM: {{ settings?.nsm || '121232010012' }} &bull; NPSN: {{ settings?.npsn || '20277259' }} &bull; TERAKREDITASI "A"
                    </p>
                    <p class="text-[10px] text-slate-600 mt-0.5 leading-tight">
                      {{ schoolSetting?.address || 'Jl. Raya Ciampea No. 12, Ciampea, Kab. Bogor, Jawa Barat 16620' }}
                    </p>
                    <p class="text-[10px] text-slate-600">
                      Telp: {{ schoolSetting?.phone || '(0251) 8621234' }} &bull; Email: {{ schoolSetting?.email || 'mtsalhasanah@kemenag.go.id' }}
                    </p>
                  </div>
                </div>

                <!-- Judul Surat Keterangan Lulus -->
                <div class="text-center space-y-1 mb-4">
                  <h1 class="text-base sm:text-lg font-black tracking-widest uppercase font-serif underline">
                    SURAT KETERANGAN LULUS (SKL)
                  </h1>
                  <p class="text-xs font-sans font-bold text-slate-800">
                    TAHUN PELAJARAN {{ settings?.academic_year || '2026/2027' }}
                  </p>
                  <p class="text-xs font-mono font-bold text-slate-700">
                    Nomor: {{ printStu?.skl_number || ('MTs.AH/PP.01.1/SKL/' + (settings?.academic_year?.split('/')[0] || '2026') + '/' + (printStu?.nis || printStu?.nisn || '001')) }}
                  </p>
                </div>

                <!-- Pengantar SKL -->
                <div class="text-xs sm:text-[13px] leading-relaxed space-y-3 mb-4">
                  <p class="text-justify indent-6">
                    Yang bertanda tangan di bawah ini, Kepala Madrasah Tsanawiyah
                    <strong>{{ schoolSetting?.school_name || 'Al-Hasanah' }}</strong> menerangkan dengan sesungguhnya bahwa:
                  </p>

                  <!-- Biodata Siswa SKL -->
                  <div class="pl-4 sm:pl-8 space-y-1 font-sans text-xs">
                    <div class="flex">
                      <span class="w-48 font-serif">Nama Lengkap</span>
                      <span class="w-3 text-center">:</span>
                      <span class="font-bold uppercase font-serif">{{ printStu?.full_name }}</span>
                    </div>
                    <div class="flex">
                      <span class="w-48 font-serif">Tempat, Tanggal Lahir</span>
                      <span class="w-3 text-center">:</span>
                      <span class="font-serif">{{ printStu?.birth_place || '-' }}, {{ formatDateIndo(printStu?.birth_date) }}</span>
                    </div>
                    <div class="flex">
                      <span class="w-48 font-serif">Nama Orang Tua / Wali</span>
                      <span class="w-3 text-center">:</span>
                      <span class="font-serif">{{ printStu?.father_name || printStu?.mother_name || '-' }}</span>
                    </div>
                    <div class="flex">
                      <span class="w-48 font-serif">Nomor Induk Siswa (NIS)</span>
                      <span class="w-3 text-center">:</span>
                      <span class="font-mono font-semibold">{{ printStu?.nis || '-' }}</span>
                    </div>
                    <div class="flex">
                      <span class="w-48 font-serif">Nomor Induk Siswa Nasional (NISN)</span>
                      <span class="w-3 text-center">:</span>
                      <span class="font-mono font-semibold">{{ printStu?.nisn || '-' }}</span>
                    </div>
                    <div class="flex">
                      <span class="w-48 font-serif">Madrasah Asal</span>
                      <span class="w-3 text-center">:</span>
                      <span class="font-serif font-semibold">{{ schoolSetting?.school_name || 'MTs Al-Hasanah' }}</span>
                    </div>
                  </div>

                  <!-- Pernyataan Keputusan Kelulusan -->
                  <div class="text-center pt-2 pb-1 space-y-1.5">
                    <p class="text-justify indent-6 text-xs">
                      Berdasarkan kriteria kelulusan peserta didik yang ditetapkan serta hasil rapat pleno Dewan Guru Madrasah Tsanawiyah
                      {{ schoolSetting?.school_name || 'Al-Hasanah' }} pada tanggal {{ formatDateIndo(settings?.graduation_date) }}, siswa tersebut di atas dinyatakan:
                    </p>
                    <div class="inline-block px-10 py-1 border-2 border-black font-serif font-black text-xl tracking-[0.3em] uppercase bg-slate-50">
                      L U L U S
                    </div>
                  </div>
                </div>

                <!-- Rangkuman Nilai SKL -->
                <div class="mb-4">
                  <div class="text-[11px] font-bold font-sans uppercase mb-1 tracking-wider text-slate-800">
                    Daftar Nilai Capaian Hasil Belajar:
                  </div>
                  <table class="w-full text-xs font-sans border-collapse border border-black">
                    <thead>
                      <tr class="bg-slate-100 text-center font-bold">
                        <th class="border border-black p-1 w-8">No</th>
                        <th class="border border-black p-1 text-left px-2">Mata Pelajaran</th>
                        <th class="border border-black p-1 w-24">Rata-rata Rapor</th>
                        <th class="border border-black p-1 w-24">Ujian Madrasah</th>
                        <th class="border border-black p-1 w-24">Nilai Akhir (SKL)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(sub, i) in subjects" :key="'skl-sub-' + sub.id">
                        <td class="border border-black p-1 text-center font-mono text-[11px]">{{ i + 1 }}</td>
                        <td class="border border-black p-1 font-medium text-[11px] px-2">{{ sub.name }}</td>
                        <td class="border border-black p-1 text-center font-mono text-[11px]">
                          {{ formatScore(getStudentGradeRecord(printStu?.id, sub.id)?.avg_semester) }}
                        </td>
                        <td class="border border-black p-1 text-center font-mono text-[11px]">
                          {{ formatScore(getStudentGradeRecord(printStu?.id, sub.id)?.final_exam_score) }}
                        </td>
                        <td class="border border-black p-1 text-center font-mono text-[11px] font-bold">
                          {{ formatScore(getStudentGradeRecord(printStu?.id, sub.id)?.final_grade) }}
                        </td>
                      </tr>
                      <tr class="bg-slate-100 font-bold border-t-2 border-black">
                        <td colspan="2" class="border border-black p-1 text-center uppercase tracking-wider text-[11px]">
                          RATA-RATA KESELURUHAN
                        </td>
                        <td class="border border-black p-1 text-center font-mono text-[11px]">
                          {{ formatScore(getStudentOverallAverage(printStu?.id, 'avg_semester')) }}
                        </td>
                        <td class="border border-black p-1 text-center font-mono text-[11px]">
                          {{ formatScore(getStudentOverallAverage(printStu?.id, 'final_exam_score')) }}
                        </td>
                        <td class="border border-black p-1 text-center font-mono text-[11px] font-black">
                          {{ formatScore(getStudentOverallAverage(printStu?.id, 'final_grade')) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Catatan Ketentuan SKL -->
                <p class="text-[10px] text-justify font-sans text-slate-600 italic leading-snug mb-4">
                  Catatan: Surat Keterangan Lulus (SKL) ini bersifat resmi dan berlaku sementara sebagai pengganti ijazah asli untuk keperluan melanjutkan pendidikan ke jenjang MA/SMA/SMK atau keperluan administrasi lainnya sampai dengan diterbitkannya blanko Ijazah resmi dari Kementerian Agama RI.
                </p>

                <!-- Titimangsa & Pengesahan SKL -->
                <div class="flex justify-between items-end text-xs font-sans pt-2">
                  <!-- Pas Foto Siswa -->
                  <div class="text-center w-32 flex flex-col items-center">
                    <div
                      v-if="settings?.show_student_photo && printStu?.photo_url"
                      class="w-[2.8cm] h-[3.8cm] border-2 border-black p-0.5 bg-white overflow-hidden flex items-center justify-center relative"
                    >
                      <img
                        :src="printStu.photo_url"
                        :alt="printStu.full_name"
                        class="w-full h-full object-cover"
                      />
                    </div>
                    <div
                      v-else
                      class="w-[2.8cm] h-[3.8cm] border-2 border-dashed border-slate-400 bg-slate-50 flex flex-col items-center justify-center p-2 text-center"
                    >
                      <span class="font-bold text-[11px] text-slate-700">PAS FOTO</span>
                      <span class="font-mono text-[10px] text-slate-500 font-bold">3 x 4 cm</span>
                      <span class="text-[8px] text-slate-400 mt-1">Cap stempel sekolah</span>
                    </div>
                  </div>

                  <!-- QR Code Validasi Keaslian Dokumen SKL -->
                  <div class="text-center flex flex-col items-center justify-center">
                    <div v-if="sklQrCodes[printStu?.id]" class="w-[2.3cm] h-[2.3cm] p-1 bg-white border border-black flex items-center justify-center shadow-2xs">
                      <img :src="sklQrCodes[printStu?.id]" alt="QR Validasi SKL" class="w-full h-full object-contain" />
                    </div>
                    <div v-else class="w-[2.3cm] h-[2.3cm] border border-dashed border-slate-400 flex items-center justify-center text-[9px] text-slate-400">
                      QR Code
                    </div>
                    <span class="text-[8.5px] font-mono font-bold text-slate-800 mt-1 uppercase tracking-tighter">Validasi Dokumen</span>
                    <span class="text-[7.5px] text-slate-500 font-sans">Scan untuk cek keaslian</span>
                  </div>

                  <!-- Tanda Tangan Kepala Madrasah -->
                  <div class="text-center min-w-[220px] sm:min-w-[260px] space-y-1">
                    <p class="font-serif">
                      {{ settings?.date_place || 'Bogor' }}, {{ formatDateIndo(settings?.graduation_date) }}
                    </p>
                    <p class="font-bold font-serif">Kepala Madrasah,</p>
                    <div style="height: 55px; min-height: 55px;" class="flex items-center justify-center">
                      &nbsp;<!-- Ruang tanda tangan & stempel madrasah -->
                    </div>
                    <p class="font-bold font-serif underline text-sm sm:text-base leading-snug">
                      {{ settings?.principal_name || schoolSetting?.principal_name || 'H. Umar Usman Ali, S.Pd, S.Pd.I' }}
                    </p>
                    <p class="text-xs font-mono">
                      NIP. {{ settings?.principal_nip || schoolSetting?.principal_nip || '-' }}
                    </p>
                  </div>
                </div>

              </div>

              <!-- Page Break Antar Siswa jika Batch Print (Otomatis ganti halaman di printer) -->
              <div
                v-if="pIdx < printStudents.length - 1"
                class="page-break my-8 py-3 border-b-4 border-dashed border-emerald-400 no-print flex items-center justify-center text-xs font-sans text-emerald-800 uppercase tracking-widest font-black bg-emerald-50 rounded-xl"
              >
                ✂️ BATAS PEMISAH CETAK SISWA BERIKUTNYA: {{ printStudents[pIdx + 1]?.full_name }} (Halaman Baru Otomatis)
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
          <!-- 1. Target Semester / Ujian (Pilihan Utama) -->
          <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center justify-between">
              <span>Target Semester / Ujian</span>
              <span
                class="text-[9px] font-bold px-1.5 py-0.5 rounded border"
                :class="getSemesterCohortInfo(semesterForm.semester_target).badgeClass"
              >
                {{ getSemesterCohortInfo(semesterForm.semester_target).level }} &bull; {{ getSemesterCohortInfo(semesterForm.semester_target).offset }}
              </span>
            </label>
            <select
              v-model="semesterForm.semester_target"
              @change="onSemesterTargetChanged"
              class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 cursor-pointer shadow-2xs"
            >
              <option value="sem_1">Semester 1 (Kls 7 Ganjil)</option>
              <option value="sem_2">Semester 2 (Kls 7 Genap)</option>
              <option value="sem_3">Semester 3 (Kls 8 Ganjil)</option>
              <option value="sem_4">Semester 4 (Kls 8 Genap)</option>
              <option value="sem_5">Semester 5 (Kls 9 Ganjil)</option>
              <option value="sem_6">Semester 6 (Kls 9 Genap)</option>
              <option value="final_exam_score">Nilai Ujian Madrasah (N-Ujian Langsung)</option>
              <option value="exam_theory">Ujian Madrasah (Tulis)</option>
              <option value="exam_practice">Ujian Madrasah (Praktik)</option>
            </select>
          </div>

          <!-- 2. Tahun Pelajaran (Otomatis Terisi & Bebas Ubah) -->
          <div>
            <label class="block text-[11px] font-bold text-slate-600 mb-1 flex items-center justify-between">
              <span>Tahun Pelajaran</span>
              <span class="text-[9px] text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                ✨ Otomatis
              </span>
            </label>
            <input
              type="text"
              v-model="semesterForm.academic_year"
              placeholder="Contoh: 2024/2025"
              class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 font-mono shadow-2xs"
            />
            <!-- Quick Preset Pills Tahun Ajaran -->
            <div class="flex items-center gap-1 mt-1 text-[9px] font-mono">
              <span class="text-slate-400 font-sans text-[9px]">Pilihan:</span>
              <button
                type="button"
                @click="semesterForm.academic_year = calculateAcademicYearForSemester('sem_1')"
                class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors cursor-pointer"
                :class="semesterForm.academic_year === calculateAcademicYearForSemester('sem_1') ? 'bg-blue-100 text-blue-800 font-bold' : ''"
                title="Tahun Ajaran Kelas 7"
              >
                {{ calculateAcademicYearForSemester('sem_1') }}
              </button>
              <button
                type="button"
                @click="semesterForm.academic_year = calculateAcademicYearForSemester('sem_3')"
                class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors cursor-pointer"
                :class="semesterForm.academic_year === calculateAcademicYearForSemester('sem_3') ? 'bg-amber-100 text-amber-800 font-bold' : ''"
                title="Tahun Ajaran Kelas 8"
              >
                {{ calculateAcademicYearForSemester('sem_3') }}
              </button>
              <button
                type="button"
                @click="semesterForm.academic_year = calculateAcademicYearForSemester('sem_5')"
                class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors cursor-pointer"
                :class="semesterForm.academic_year === calculateAcademicYearForSemester('sem_5') ? 'bg-emerald-100 text-emerald-800 font-bold' : ''"
                title="Tahun Ajaran Kelas 9"
              >
                {{ calculateAcademicYearForSemester('sem_5') }}
              </button>
            </div>
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
              @click="saveModalSemesterGrades"
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

    <!-- MODAL 4: EDIT NOMOR SERI IJAZAH & SKL SISWA TUNGGAL -->
    <div
      v-if="showEditSerialModal && editingStudent"
      class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto no-print"
    >
      <div class="bg-white rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
              <Pencil class="w-4 h-4" />
            </div>
            <div>
              <h3 class="font-bold text-slate-800 text-sm">Edit Nomor Ijazah & SKL</h3>
              <p class="text-[11px] text-slate-400 truncate max-w-[220px]">{{ editingStudent.full_name }}</p>
            </div>
          </div>
          <button
            type="button"
            @click="showEditSerialModal = false"
            class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 cursor-pointer"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-3.5 text-xs">
          <!-- Info Siswa -->
          <div class="p-2.5 bg-slate-50 border border-slate-200/60 rounded-xl space-y-1 text-[11px]">
            <div class="flex justify-between">
              <span class="text-slate-500">Nama Siswa:</span>
              <span class="font-bold text-slate-800">{{ editingStudent.full_name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">NISN / NIS:</span>
              <span class="font-mono text-slate-700 font-semibold">{{ editingStudent.nisn || '-' }} / {{ editingStudent.nis || '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">Rombel Kelas:</span>
              <span class="font-bold text-emerald-700">Kelas {{ editingStudent.class_name }}</span>
            </div>
          </div>

          <!-- Input Nomor Blanko Ijazah Fisik -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">
              Nomor Seri Blanko Ijazah Fisik
            </label>
            <input
              type="text"
              v-model="editSerialForm.certificate_number"
              placeholder="Contoh: MTs.10.01/PP.01.1/0001"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono text-xs font-bold text-slate-800 focus:outline-emerald-500 focus:border-emerald-500"
            />
            <p class="text-[10px] text-slate-400 mt-1 leading-tight">
              Gunakan fitur ini jika terdapat blanko fisik yang rusak/keliru sehingga nomor blanko siswa melompat dari urutan.
            </p>
          </div>

          <!-- Input Nomor SKL -->
          <div>
            <label class="block font-bold text-slate-700 mb-1">
              Nomor Surat Keterangan Lulus (SKL)
            </label>
            <input
              type="text"
              v-model="editSerialForm.skl_number"
              placeholder="Contoh: MTs.AH/PP.01.1/SKL/2026/001"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl font-mono text-xs font-bold text-slate-800 focus:outline-emerald-500 focus:border-emerald-500"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
          <button
            type="button"
            @click="showEditSerialModal = false"
            class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveStudentSerial"
            :disabled="isSavingStudentSerial"
            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition-colors cursor-pointer disabled:opacity-60 flex items-center gap-1.5"
          >
            <Save class="w-3.5 h-3.5" />
            <span>{{ isSavingStudentSerial ? 'Menyimpan...' : 'Simpan Nomor Seri' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL 4: IMPORT NILAI EXCEL MASSAL -->
    <div
      v-if="showImportModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto no-print"
    >
      <div :class="importStep === 2 ? 'max-w-3xl' : 'max-w-xl'" class="bg-white rounded-3xl w-full p-5 sm:p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150 transition-all max-h-[92vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center shadow-xs">
              <Upload class="w-5 h-5 text-emerald-700" />
            </div>
            <div>
              <h3 class="text-base font-black text-slate-800 font-lexend">
                {{ importStep === 2 ? 'Pratinjau & Verifikasi File Excel' : 'Import Nilai Excel Massal' }}
              </h3>
              <p class="text-xs text-slate-400 font-medium">
                {{ importStep === 2 ? 'Periksa ringkasan data siswa sebelum disimpan ke database.' : 'Unggah file Excel nilai ijazah untuk seluruh siswa sekaligus.' }}
              </p>
            </div>
          </div>
          <button
            @click="showImportModal = false"
            class="text-slate-400 hover:text-slate-600 rounded-full p-1.5 hover:bg-slate-100 transition-colors cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- STEP 1: Pilih File & Target Semester -->
        <div v-if="importStep === 1" class="space-y-4 overflow-y-auto pr-1">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Target Semester yang Diimpor:</label>
            <select
              v-model="importSemesterTarget"
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-emerald-500 focus:bg-white transition-all cursor-pointer shadow-2xs"
            >
              <option value="sem_1">📘 Semester 1 (Kls 7 Ganjil - TP 2024/2025)</option>
              <option value="sem_2">📘 Semester 2 (Kls 7 Genap - TP 2024/2025)</option>
              <option value="sem_3">📙 Semester 3 (Kls 8 Ganjil - TP 2025/2026)</option>
              <option value="sem_4">📙 Semester 4 (Kls 8 Genap - TP 2025/2026)</option>
              <option value="sem_5">📕 Semester 5 (Kls 9 Ganjil - TP 2026/2027)</option>
              <option value="sem_6">📕 Semester 6 (Kls 9 Genap - TP 2026/2027)</option>
              <option value="exam">📝 Nilai Ujian Madrasah (Teori & Praktik)</option>
              <option value="all">📊 Leger Komplit (Semua Semester 1 s.d. 6 + Ujian)</option>
            </select>
            <div class="flex items-center justify-between mt-1.5 text-[11px]">
              <span class="text-slate-500">Belum punya format Excel?</span>
              <button
                type="button"
                @click="downloadExcelTemplate(importSemesterTarget)"
                class="text-emerald-700 hover:text-emerald-800 font-bold hover:underline flex items-center gap-1 cursor-pointer"
              >
                <Download class="w-3 h-3" />
                <span>Unduh Template {{ importSemesterTarget === 'all' ? 'Leger' : 'Semester Ini' }}</span>
              </button>
            </div>
          </div>

          <!-- Dropzone Upload Area -->
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">File Excel Nilai (.xlsx / .xls / .csv):</label>
            <div
              @click="$refs.fileInputRef?.click()"
              class="border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50/70 hover:bg-emerald-50/30 rounded-2xl p-6 text-center cursor-pointer transition-all space-y-2 group"
            >
              <input
                ref="fileInputRef"
                type="file"
                accept=".xlsx, .xls, .csv"
                class="hidden"
                @change="onImportFileSelected"
              />
              <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-slate-200/80 flex items-center justify-center mx-auto text-emerald-600 group-hover:scale-110 transition-transform">
                <FileUp class="w-6 h-6" />
              </div>
              <div v-if="!selectedImportFile">
                <p class="text-xs font-bold text-slate-700">Klik di sini untuk memilih file Excel</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Mendukung format .xlsx, .xls, atau .csv (Maksimal 15 MB)</p>
              </div>
              <div v-else class="space-y-1">
                <p class="text-xs font-black text-emerald-800 flex items-center justify-center gap-1.5">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600" />
                  <span>{{ selectedImportFile.name }}</span>
                </p>
                <p class="text-[10px] text-slate-500 font-mono">
                  Ukuran: {{ (selectedImportFile.size / 1024).toFixed(1) }} KB
                </p>
              </div>
            </div>
          </div>

          <!-- Info Box Petunjuk -->
          <div class="p-3 bg-amber-50/70 border border-amber-200/70 rounded-xl text-[11px] text-amber-900 leading-relaxed space-y-1">
            <p class="font-bold flex items-center gap-1">
              <span>💡</span>
              <span>Tips Penting:</span>
            </p>
            <ul class="list-disc list-inside text-amber-800 space-y-0.5 pl-1 text-[10.5px]">
              <li>Sistem mencocokkan siswa berdasarkan <strong>NISN</strong> atau <strong>NIS</strong> secara otomatis.</li>
              <li>Nilai yang diisi harus angka di rentang <strong>0 s.d. 100</strong>.</li>
              <li>Kolom mapel yang dibiarkan kosong di Excel tidak akan menghapus nilai yang sudah tersimpan sebelumnya.</li>
            </ul>
          </div>
        </div>

        <!-- STEP 2: Pratinjau & Verifikasi Hasil -->
        <div v-else-if="importStep === 2" class="space-y-4 overflow-y-auto pr-1">
          <!-- Summary Badges -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-center">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Baris</span>
              <span class="text-lg font-black text-slate-800 font-mono">{{ previewResult?.total_rows || 0 }}</span>
            </div>
            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-center">
              <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Siswa Dikenali</span>
              <span class="text-lg font-black text-emerald-800 font-mono">✅ {{ previewResult?.matched_count || 0 }}</span>
            </div>
            <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-center">
              <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block">Tidak Cocok</span>
              <span class="text-lg font-black text-rose-800 font-mono">
                {{ previewResult?.unmatched_count > 0 ? `⚠️ ${previewResult?.unmatched_count}` : '0' }}
              </span>
            </div>
            <div class="p-3 rounded-2xl bg-indigo-50 border border-indigo-200 text-center">
              <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider block">Mapel Terbaca</span>
              <span class="text-lg font-black text-indigo-800 font-mono">📚 {{ previewResult?.detected_subjects?.length || 0 }}</span>
            </div>
          </div>

          <!-- Unmatched Warning Alert -->
          <div v-if="previewResult?.unmatched_items?.length" class="p-3 bg-amber-50 border border-amber-200 rounded-2xl space-y-1.5 text-xs">
            <div class="flex items-center gap-1.5 text-amber-900 font-bold">
              <AlertTriangle class="w-4 h-4 text-amber-600 flex-shrink-0" />
              <span>Perhatian: {{ previewResult?.unmatched_count }} Baris Siswa Tidak Dikenali di Database</span>
            </div>
            <p class="text-[11px] text-amber-800 leading-relaxed">
              Baris berikut akan dilewati saat proses import karena NISN/Nama tidak cocok dengan database kelas 9:
            </p>
            <div class="max-h-24 overflow-y-auto space-y-1 pt-1 font-mono text-[10px] text-amber-950">
              <div v-for="item in previewResult.unmatched_items" :key="'unmatched-' + item.row" class="px-2 py-1 bg-white/70 rounded-lg flex items-center justify-between">
                <span>Baris #{{ item.row }}: {{ item.name }} (NISN: {{ item.nisn }})</span>
                <span class="text-rose-600 font-bold">Tidak Terdaftar</span>
              </div>
            </div>
          </div>

          <!-- Sample Table Preview -->
          <div class="space-y-1.5">
            <span class="text-xs font-bold text-slate-700 block">Sampel Siswa & Nilai Terdeteksi:</span>
            <div class="border border-slate-200 rounded-2xl overflow-hidden max-h-56 overflow-y-auto">
              <table class="w-full text-xs text-left">
                <thead class="bg-slate-100 text-[10.5px] uppercase font-bold text-slate-600 sticky top-0">
                  <tr>
                    <th class="p-2.5">Siswa</th>
                    <th class="p-2.5">NISN</th>
                    <th class="p-2.5">Kelas</th>
                    <th class="p-2.5 text-center">Mapel Terisi</th>
                    <th class="p-2.5">Cuplikan Nilai</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="stu in previewResult?.sample_students" :key="'prev-' + stu.id" class="hover:bg-slate-50/80">
                    <td class="p-2.5 font-bold text-slate-800">{{ stu.name }}</td>
                    <td class="p-2.5 font-mono text-slate-600 text-[11px]">{{ stu.nisn }}</td>
                    <td class="p-2.5 text-slate-600">{{ stu.class_name }}</td>
                    <td class="p-2.5 text-center">
                      <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        {{ stu.valid_scores_count }} Mapel
                      </span>
                    </td>
                    <td class="p-2.5">
                      <div class="flex items-center gap-1.5 flex-wrap">
                        <span
                          v-for="(score, code) in stu.sample_scores"
                          :key="code"
                          class="px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded text-[9.5px] font-mono"
                        >
                          {{ code }}: <b>{{ score }}</b>
                        </span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between gap-2.5 pt-3 border-t border-slate-100 flex-shrink-0">
          <div>
            <button
              v-if="importStep === 2"
              type="button"
              @click="importStep = 1"
              class="px-3.5 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer transition-colors flex items-center gap-1.5"
            >
              <span>&larr; Ganti File</span>
            </button>
            <span v-else class="text-[11px] text-slate-400">Pastikan file sudah sesuai template.</span>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="showImportModal = false"
              class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl cursor-pointer transition-colors"
            >
              Tutup
            </button>

            <!-- Step 1 Button: Periksa & Pratinjau -->
            <button
              v-if="importStep === 1"
              type="button"
              @click="handlePreviewImport"
              :disabled="!selectedImportFile || isPreviewing"
              class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:shadow-none"
            >
              <RefreshCw v-if="isPreviewing" class="w-4 h-4 animate-spin" />
              <Eye v-else class="w-4 h-4" />
              <span>{{ isPreviewing ? 'Memverifikasi File...' : 'Periksa & Pratinjau File' }}</span>
            </button>

            <!-- Step 2 Button: Konfirmasi & Simpan -->
            <button
              v-else
              type="button"
              @click="submitImportExcel"
              :disabled="!previewResult?.can_import || isImporting"
              class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 active:scale-95 text-white text-xs font-black rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:shadow-none"
            >
              <RefreshCw v-if="isImporting" class="w-4 h-4 animate-spin" />
              <CheckCircle2 v-else class="w-4 h-4" />
              <span>{{ isImporting ? 'Menyimpan Nilai...' : `Konfirmasi & Simpan (${previewResult?.matched_count || 0} Siswa)` }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import QRCode from 'qrcode';
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
  AlertTriangle,
  X,
  PlusCircle,
  FileSpreadsheet,
  Search,
  Calendar,
  Filter,
  Pencil,
  Upload,
  FileUp,
  FileDown,
  CheckCircle2,
  Eye,
  Check
} from 'lucide-vue-next';

// State
const toast = useToast();
const activeTab = ref('semester'); // 'semester', 'subject', 'ledger', or 'transcripts'
const isLoading = ref(false);
const isSaving = ref(false);
const isPulling = ref(false);
const hasUnsavedChanges = ref(false);

const classes = ref([]);
const subjects = ref([]);
const allSubjects = ref([]);
const students = ref([]);
const grades = ref([]);
const settings = ref(null);
const schoolSetting = ref(null);

const selectedClassId = ref('');
const selectedSubjectId = ref('');
const ledgerSearch = ref('');

// Input Per Semester State
const inputSemesterKey = ref('sem_1');
const inputSemesterList = [
  { key: 'sem_1', label: 'Semester 1', kls: 'Kls 7 Ganjil', tp: '2024/2025', icon: '📘' },
  { key: 'sem_2', label: 'Semester 2', kls: 'Kls 7 Genap', tp: '2024/2025', icon: '📘' },
  { key: 'sem_3', label: 'Semester 3', kls: 'Kls 8 Ganjil', tp: '2025/2026', icon: '📙' },
  { key: 'sem_4', label: 'Semester 4', kls: 'Kls 8 Genap', tp: '2025/2026', icon: '📙' },
  { key: 'sem_5', label: 'Semester 5', kls: 'Kls 9 Ganjil', tp: '2026/2027', icon: '📕' },
  { key: 'sem_6', label: 'Semester 6', kls: 'Kls 9 Genap', tp: '2026/2027', icon: '📕' },
  { key: 'exam', label: 'Ujian Madrasah', kls: 'Nilai Ujian', tp: '2026/2027', icon: '📝' },
];

const currentInputSemester = computed(() => {
  return inputSemesterList.find(s => s.key === inputSemesterKey.value) || inputSemesterList[0];
});

const semesterQuickScore = ref('');

// ================= IMPORT & TEMPLATE EXCEL =================
const showImportModal = ref(false);
const isDownloadingTemplate = ref(false);
const isImporting = ref(false);
const isPreviewing = ref(false);
const selectedImportFile = ref(null);
const fileInputRef = ref(null);
const importSemesterTarget = ref('sem_1');
const importStep = ref(1); // 1: Select File, 2: Preview & Confirm
const previewResult = ref(null);

function openImportModal(semesterKey) {
  importSemesterTarget.value = semesterKey || inputSemesterKey.value || 'sem_1';
  selectedImportFile.value = null;
  importStep.value = 1;
  previewResult.value = null;
  if (fileInputRef.value) fileInputRef.value.value = '';
  showImportModal.value = true;
}

function onImportFileSelected(event) {
  const file = event.target.files?.[0];
  if (file) {
    selectedImportFile.value = file;
    importStep.value = 1;
    previewResult.value = null;
  }
}

async function handlePreviewImport() {
  if (!selectedImportFile.value) {
    toast.error('Pilih file Excel (.xlsx / .xls) terlebih dahulu');
    return;
  }

  isPreviewing.value = true;
  try {
    const token = localStorage.getItem('token');
    const formData = new FormData();
    formData.append('file', selectedImportFile.value);
    formData.append('semester', importSemesterTarget.value);

    const response = await fetch('/api/admin/graduation-grades/preview-import', {
      method: 'POST',
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/json',
      },
      body: formData,
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Gagal memverifikasi file Excel');
    }

    previewResult.value = result.data;
    importStep.value = 2;
    toast.success('Verifikasi file Excel selesai! Silakan periksa pratinjau nilai.');
  } catch (err) {
    console.error(err);
    toast.error('Verifikasi gagal: ' + (err.message || 'Format file tidak sesuai'));
  } finally {
    isPreviewing.value = false;
  }
}

async function downloadExcelTemplate(semesterKey) {
  const sem = semesterKey || inputSemesterKey.value || 'sem_1';
  isDownloadingTemplate.value = true;
  try {
    const token = localStorage.getItem('token');
    const params = new URLSearchParams();
    params.set('semester', sem);
    if (selectedClassId.value) {
      params.set('class_id', selectedClassId.value);
    }

    const response = await fetch(`/api/admin/graduation-grades/template?${params.toString()}`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json',
      },
    });

    if (!response.ok) {
      const errJson = await response.json().catch(() => null);
      throw new Error(errJson?.message || 'Gagal mengunduh template Excel');
    }

    let fileName = `Template_Nilai_Ijazah_${sem}.xlsx`;
    const disposition = response.headers.get('Content-Disposition');
    if (disposition && disposition.includes('filename=')) {
      const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
      if (match && match[1]) {
        fileName = match[1].replace(/['"]/g, '').trim();
      }
    }

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    toast.success(`Template ${fileName} berhasil diunduh!`);
  } catch (err) {
    console.error(err);
    toast.error('Gagal mengunduh template: ' + (err.message || 'Terjadi kesalahan'));
  } finally {
    isDownloadingTemplate.value = false;
  }
}

async function submitImportExcel() {
  if (!selectedImportFile.value) {
    toast.error('Pilih file Excel (.xlsx / .xls) terlebih dahulu');
    return;
  }

  isImporting.value = true;
  try {
    const token = localStorage.getItem('token');
    const formData = new FormData();
    formData.append('file', selectedImportFile.value);
    formData.append('semester', importSemesterTarget.value);

    const response = await fetch('/api/admin/graduation-grades/import', {
      method: 'POST',
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/json',
      },
      body: formData,
    });

    const result = await response.json();
    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Gagal mengimpor file Excel');
    }

    toast.success(result.message || 'Nilai berhasil diimpor!');
    showImportModal.value = false;
    selectedImportFile.value = null;

    // Reload data so table updates immediately
    await fetchData();
  } catch (err) {
    console.error(err);
    toast.error('Gagal mengimpor: ' + (err.message || 'Format file tidak sesuai'));
  } finally {
    isImporting.value = false;
  }
}

// Leger View Mode Selector
const ledgerMode = ref('final_grade');
const ledgerModes = [
  { id: 'final_grade', shortLabel: 'Nilai Akhir Ijazah', title: 'Leger Rekapitulasi Nilai Akhir Ijazah (PDUM)', sub: 'Akumulasi Rapor Semester 1 s.d. 6 (60%) + Ujian Madrasah (40%)', group: 'Ijazah & PDUM' },
  { id: 'avg_semester', shortLabel: 'R-Rapor (Sem 1-6)', title: 'Leger Rata-Rata Rapor 6 Semester', sub: 'Rata-rata Nilai Rapor Semester 1 s.d. Semester 6', group: 'Ijazah & PDUM' },
  { id: 'sem_1', shortLabel: 'Sem 1', title: 'Leger Nilai Rapor Semester 1', sub: 'Kelas 7 Ganjil - Tahun Pelajaran 2024/2025', group: 'Per Semester' },
  { id: 'sem_2', shortLabel: 'Sem 2', title: 'Leger Nilai Rapor Semester 2', sub: 'Kelas 7 Genap - Tahun Pelajaran 2024/2025', group: 'Per Semester' },
  { id: 'sem_3', shortLabel: 'Sem 3', title: 'Leger Nilai Rapor Semester 3', sub: 'Kelas 8 Ganjil - Tahun Pelajaran 2025/2026', group: 'Per Semester' },
  { id: 'sem_4', shortLabel: 'Sem 4', title: 'Leger Nilai Rapor Semester 4', sub: 'Kelas 8 Genap - Tahun Pelajaran 2025/2026', group: 'Per Semester' },
  { id: 'sem_5', shortLabel: 'Sem 5', title: 'Leger Nilai Rapor Semester 5', sub: 'Kelas 9 Ganjil - Tahun Pelajaran 2026/2027', group: 'Per Semester' },
  { id: 'sem_6', shortLabel: 'Sem 6', title: 'Leger Nilai Rapor Semester 6', sub: 'Kelas 9 Genap - Tahun Pelajaran 2026/2027', group: 'Per Semester' },
  { id: 'final_exam_score', shortLabel: 'N-Ujian', title: 'Leger Nilai Akhir Ujian Madrasah (N-Ujian)', sub: 'Nilai Ujian Madrasah Akhir Jenjang (Teori & Praktik)', group: 'Ujian' },
  { id: 'exam_theory', shortLabel: 'Ujian Tulis', title: 'Leger Nilai Ujian Madrasah (Teori/Tulis)', sub: 'Nilai Tes Tertulis Ujian Madrasah', group: 'Ujian' },
  { id: 'exam_practice', shortLabel: 'Ujian Praktik', title: 'Leger Nilai Ujian Madrasah (Praktik)', sub: 'Nilai Ujian Praktik untuk Mapel Terkait', group: 'Ujian' },
];

const currentLedgerModeObj = computed(() => {
  return ledgerModes.find(m => m.id === ledgerMode.value) || ledgerModes[0];
});

// Auto-Calculate Academic Year based on graduation year and semester target
function calculateAcademicYearForSemester(targetSemester, baseYear) {
  const defaultYear = baseYear || settings.value?.academic_year || '2026/2027';
  if (!defaultYear || !defaultYear.includes('/')) return defaultYear;
  const parts = defaultYear.split('/');
  const start = parseInt(parts[0], 10);
  const end = parseInt(parts[1], 10);
  if (isNaN(start) || isNaN(end)) return defaultYear;

  // Semester 1 & 2 -> Angkatan Kelas 7 (Mundur 2 Tahun)
  if (targetSemester === 'sem_1' || targetSemester === 'sem_2') {
    return `${start - 2}/${end - 2}`;
  }
  // Semester 3 & 4 -> Angkatan Kelas 8 (Mundur 1 Tahun)
  if (targetSemester === 'sem_3' || targetSemester === 'sem_4') {
    return `${start - 1}/${end - 1}`;
  }
  // Semester 5, 6, & Ujian -> Angkatan Kelas 9 (Tahun Berjalan Kelulusan)
  return `${start}/${end}`;
}

function getSemesterCohortInfo(targetSemester) {
  if (targetSemester === 'sem_1' || targetSemester === 'sem_2') {
    return { level: 'Kelas 7', offset: 'Mundur 2 Thn', badgeClass: 'bg-blue-50 text-blue-700 border-blue-200' };
  }
  if (targetSemester === 'sem_3' || targetSemester === 'sem_4') {
    return { level: 'Kelas 8', offset: 'Mundur 1 Thn', badgeClass: 'bg-amber-50 text-amber-700 border-amber-200' };
  }
  return { level: 'Kelas 9', offset: 'Thn Berjalan', badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
}

// Add Semester Grades Modal State
const showAddSemesterModal = ref(false);
const isSavingSemesterGrades = ref(false);
const semesterForm = ref({
  academic_year: '2024/2025',
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

const practicalSubjectCount = computed(() => {
  return subjects.value.filter(s => isSubjectPractical(s.id)).length;
});

const filteredLedgerStudents = computed(() => {
  if (!ledgerSearch.value.trim()) return students.value;
  const q = ledgerSearch.value.toLowerCase().trim();
  return students.value.filter(s =>
    (s.full_name && s.full_name.toLowerCase().includes(q)) ||
    (s.nisn && s.nisn.toLowerCase().includes(q)) ||
    (s.class_name && s.class_name.toLowerCase().includes(q))
  );
});

// Cetak Ijazah & Transkrip Tab State
const transcriptSearch = ref('');
const transcriptViewMode = ref('cards'); // 'cards' or 'table'
const readinessFilter = ref('all'); // 'all', 'ready', 'warning', 'incomplete'

// Validasi Kesiapan Data Siswa untuk Cetak
function getStudentValidation(stu) {
  const missing = [];
  if (!stu.full_name) missing.push('Nama belum diisi');
  if (!stu.nisn || String(stu.nisn).trim().length < 6) missing.push('NISN belum valid/kosong');
  if (!stu.birth_place || !stu.birth_date) missing.push('Tempat/Tgl Lahir belum lengkap');
  if (!stu.father_name && !stu.mother_name) missing.push('Nama Orang Tua belum ada');

  const compCount = getStudentCompletedSubjectCount(stu.id, 'final_grade');
  const totalSub = subjects.value.length;
  if (compCount < totalSub) {
    missing.push(`${totalSub - compCount} mapel belum ada nilai akhir`);
  }

  const hasPhoto = Boolean(stu.photo_url);

  let status = 'ready'; // 🟢 Siap Cetak
  if (missing.length > 0) {
    status = 'incomplete'; // 🔴 Belum Lengkap
  } else if (!hasPhoto) {
    status = 'warning'; // 🟡 Lengkap tanpa foto profil digital (foto fisik)
  }

  return {
    status,
    missing,
    hasPhoto,
    isComplete: missing.length === 0,
    compCount,
    totalSub,
  };
}

const countReady = computed(() => students.value.filter(s => getStudentValidation(s).status === 'ready').length);
const countWarning = computed(() => students.value.filter(s => getStudentValidation(s).status === 'warning').length);
const countIncomplete = computed(() => students.value.filter(s => getStudentValidation(s).status === 'incomplete').length);

const filteredTranscriptStudents = computed(() => {
  let list = students.value;

  if (readinessFilter.value !== 'all') {
    list = list.filter(s => getStudentValidation(s).status === readinessFilter.value);
  }

  if (transcriptSearch.value.trim()) {
    const q = transcriptSearch.value.toLowerCase().trim();
    list = list.filter(s =>
      (s.full_name && s.full_name.toLowerCase().includes(q)) ||
      (s.nisn && s.nisn.toLowerCase().includes(q)) ||
      (s.nis && s.nis.toLowerCase().includes(q)) ||
      (s.certificate_number && s.certificate_number.toLowerCase().includes(q)) ||
      (s.class_name && s.class_name.toLowerCase().includes(q))
    );
  }

  return list;
});

// Batch Print State
const isBatchPrint = ref(false);
const batchPrintStudents = ref([]);
const printStudents = computed(() => {
  if (isBatchPrint.value) {
    return batchPrintStudents.value;
  }
  return activePrintStudent.value ? [activePrintStudent.value] : [];
});

// Auto-Numbering Serial State
const isGeneratingSerial = ref(false);
const serialForm = ref({
  cert_start: 1,
  cert_digits: 4,
  skl_start: 1,
  order_by: 'full_name',
});

// Single Student Serial Edit Modal State
const showEditSerialModal = ref(false);
const editingStudent = ref(null);
const isSavingStudentSerial = ref(false);
const editSerialForm = ref({
  certificate_number: '',
  skl_number: '',
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
  active_subject_ids: [],
  practical_subject_ids: [],
  date_place: 'Bogor',
  graduation_date: '',
  certificate_number_prefix: 'MTs.10.01/PP.01.1/',
  principal_name: '',
  principal_nip: '',
  nsm: '121232010012',
  npsn: '20277259',
  show_student_photo: true,
  academic_year: '2026/2027',
});

// Print Modal State
const showPrintModal = ref(false);
const printMode = ref('both'); // 'both' (2 Lembar), 'page1' (Muka), 'page2' (Nilai), 'skl' (SKL Sementara)
const activePrintStudent = ref(null);

// Terbilang Nilai Helper
function terbilangAngka(n) {
  const angka = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
  n = Math.floor(n);
  if (n < 12) return angka[n];
  if (n < 20) return terbilangAngka(n - 10) + ' Belas';
  if (n < 100) return terbilangAngka(Math.floor(n / 10)) + ' Puluh' + (n % 10 !== 0 ? ' ' + angka[n % 10] : '');
  if (n === 100) return 'Seratus';
  return String(n);
}

function terbilangScore(val) {
  if (val === null || val === undefined || val === '' || isNaN(val)) return '-';
  const num = parseFloat(val);
  if (isNaN(num)) return '-';

  const bulat = Math.floor(num);
  const teksBulat = bulat === 0 ? 'Nol' : terbilangAngka(bulat);

  const str = num.toFixed(2);
  const parts = str.split('.');
  if (parts.length > 1 && parseInt(parts[1], 10) > 0) {
    const digit1 = parseInt(parts[1][0], 10);
    const digit2 = parseInt(parts[1][1], 10);
    const satuanArr = ['Nol', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan'];
    return `${teksBulat} Koma ${satuanArr[digit1]} ${satuanArr[digit2]}`;
  }
  return teksBulat;
}

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

    const res = await api.get('/admin/graduation-grades', { params });
    if (res?.success) {
      const d = res.data;
      classes.value = d.classes || [];
      subjects.value = d.subjects || [];
      allSubjects.value = d.all_subjects || d.subjects || [];
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
    toast.error('Gagal memuat data rekap ijazah: ' + (err.response?.data?.message || err.message));
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

function getStudentRecordBySubject(studentId, subId) {
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

  // 2. Exam score (hanya auto-hitung jika ada input di Tulis atau Praktik)
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
    }
  } else if (theory !== null) {
    rec.final_exam_score = Number(theory.toFixed(2));
  }

  // 3. Final grade
  calculateFinalGrade(rec, st);
}

function onFinalExamScoreInput(studentId) {
  hasUnsavedChanges.value = true;
  const rec = getStudentRecord(studentId);
  const st = settings.value || { report_weight: 60, exam_weight: 40 };
  calculateFinalGrade(rec, st);
}

function calculateFinalGrade(rec, st) {
  const rWeight = parseFloat(st.report_weight || 60);
  const eWeight = parseFloat(st.exam_weight || 40);
  const totFW = rWeight + eWeight;

  const examVal = (rec.final_exam_score !== null && rec.final_exam_score !== '' && !isNaN(rec.final_exam_score))
    ? parseFloat(rec.final_exam_score)
    : null;

  if (totFW > 0 && (rec.avg_semester !== null || examVal !== null)) {
    const rPart = (rec.avg_semester || 0) * (rWeight / totFW);
    const ePart = (examVal || 0) * (eWeight / totFW);
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
        final_exam_score: rec.final_exam_score,
      });
    }

    const res = await api.post('/admin/graduation-grades/save', { grades: payload });
    if (res?.success) {
      hasUnsavedChanges.value = false;
      toast.success(res.message || 'Nilai mata pelajaran berhasil disimpan');
      // Refresh
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menyimpan nilai:', err);
    toast.error('Gagal menyimpan nilai: ' + (err.response?.data?.message || err.message));
  } finally {
    isSaving.value = false;
  }
}

// Auto pull from database
async function handleAutoPull() {
  if (isPulling.value) return;
  isPulling.value = true;
  try {
    const res = await api.post('/admin/graduation-grades/auto-pull', {
      class_id: selectedClassId.value || null,
      subject_id: selectedSubjectId.value || null,
    });
    if (res?.success) {
      toast.success(res.message || 'Berhasil menarik nilai otomatis');
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menarik nilai otomatis:', err);
    toast.error('Gagal menarik nilai otomatis: ' + (err.response?.data?.message || err.message));
  } finally {
    isPulling.value = false;
  }
}

// Input & Tambah Nilai Semester Modal Methods
function openAddSemesterGradesModal() {
  if (students.value.length === 0 && !isLoading.value) {
    fetchData().then(() => {
      setupAndOpenModal();
    });
    return;
  }
  setupAndOpenModal();
}

function setupAndOpenModal() {
  semesterForm.value.class_id = selectedClassId.value || '';
  if (!semesterForm.value.subject_id || !subjects.value.some(s => s.id === Number(semesterForm.value.subject_id))) {
    semesterForm.value.subject_id = selectedSubjectId.value || (subjects.value[0]?.id || '');
  }
  semesterForm.value.quick_score = '';
  // Otomatis sinkronkan tahun pelajaran sesuai target semester
  semesterForm.value.academic_year = calculateAcademicYearForSemester(
    semesterForm.value.semester_target,
    settings.value?.academic_year || '2026/2027'
  );
  loadSemesterFormScores();
  showAddSemesterModal.value = true;
}

function onSemesterTargetChanged() {
  // Otomatis ubah tahun pelajaran saat semester target berganti
  semesterForm.value.academic_year = calculateAcademicYearForSemester(
    semesterForm.value.semester_target,
    settings.value?.academic_year || '2026/2027'
  );
  loadSemesterFormScores();
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

async function saveModalSemesterGrades() {
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
        final_exam_score: target === 'final_exam_score' ? numVal : (existing.final_exam_score ?? null),
      });
    }

    const res = await api.post('/admin/graduation-grades/save', { grades: payload });
    if (res?.success) {
      showAddSemesterModal.value = false;
      toast.success(res.message || 'Nilai semester berhasil disimpan');
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menyimpan nilai semester:', err);
    toast.error('Gagal menyimpan nilai semester: ' + (err.response?.data?.message || err.message));
  } finally {
    isSavingSemesterGrades.value = false;
  }
}

// Input Per Semester Tab Functions
function onSemesterGradeInput(studentId, subjectId) {
  hasUnsavedChanges.value = true;
  const rec = getStudentRecordBySubject(studentId, subjectId);
  const st = settings.value || { report_weight: 60, exam_weight: 40, theory_weight_default: 50, practice_weight_default: 50 };

  // 1. Avg Semester
  const semVals = [];
  ['sem_1', 'sem_2', 'sem_3', 'sem_4', 'sem_5', 'sem_6'].forEach(k => {
    if (rec[k] !== null && rec[k] !== '' && !isNaN(rec[k])) {
      semVals.push(parseFloat(rec[k]));
    }
  });
  rec.avg_semester = semVals.length > 0 ? Number((semVals.reduce((a, b) => a + b, 0) / semVals.length).toFixed(2)) : null;

  // 2. Exam score
  if (inputSemesterKey.value === 'exam') {
    const isPrac = isSubjectPractical(subjectId);
    const theory = (rec.exam_theory !== null && rec.exam_theory !== '') ? parseFloat(rec.exam_theory) : null;
    const prac = (rec.exam_practice !== null && rec.exam_practice !== '') ? parseFloat(rec.exam_practice) : null;
    if (isPrac) {
      const tWeight = parseFloat(st.theory_weight_default || 50);
      const pWeight = parseFloat(st.practice_weight_default || 50);
      const totW = tWeight + pWeight;
      if (totW > 0 && (theory !== null || prac !== null)) {
        rec.final_exam_score = Number(((theory || 0) * (tWeight / totW) + (prac || 0) * (pWeight / totW)).toFixed(2));
      }
    } else if (theory !== null) {
      rec.final_exam_score = Number(theory.toFixed(2));
    }
  }

  // 3. Final grade
  calculateFinalGrade(rec, st);
}

function getStudentSemesterTotal(studentId, semKey) {
  const targetField = semKey === 'exam' ? 'final_exam_score' : semKey;
  return getStudentTotalScore(studentId, targetField);
}

function getStudentSemesterAverage(studentId, semKey) {
  const targetField = semKey === 'exam' ? 'final_exam_score' : semKey;
  return getStudentOverallAverage(studentId, targetField);
}

function applySemesterQuickFill(studentId = null) {
  if (!semesterQuickScore.value || isNaN(semesterQuickScore.value)) {
    toast.error('Masukkan angka nilai yang valid');
    return;
  }
  const val = parseFloat(semesterQuickScore.value);
  const targetStudents = studentId ? students.value.filter(s => s.id === studentId) : students.value;
  let filled = 0;
  const semKey = inputSemesterKey.value;

  targetStudents.forEach(stu => {
    subjects.value.forEach(sub => {
      const rec = getStudentRecordBySubject(stu.id, sub.id);
      if (semKey !== 'exam') {
        if (rec[semKey] === null || rec[semKey] === '' || rec[semKey] === undefined) {
          rec[semKey] = val;
          onSemesterGradeInput(stu.id, sub.id);
          filled++;
        }
      } else {
        if (rec.final_exam_score === null || rec.final_exam_score === '' || rec.final_exam_score === undefined) {
          rec.final_exam_score = val;
          rec.exam_theory = val;
          if (isSubjectPractical(sub.id)) {
            rec.exam_practice = val;
          }
          onSemesterGradeInput(stu.id, sub.id);
          filled++;
        }
      }
    });
  });
  hasUnsavedChanges.value = true;
  toast.success(`Berhasil mengisi ${filled} nilai yang kosong dengan ${val}`);
}

async function saveSemesterTabGrades() {
  if (isSaving.value) return;
  isSaving.value = true;
  try {
    const payload = [];
    for (const stu of students.value) {
      for (const sub of subjects.value) {
        const rec = getStudentRecordBySubject(stu.id, sub.id);
        payload.push({
          student_id: stu.id,
          subject_id: sub.id,
          sem_1: rec.sem_1,
          sem_2: rec.sem_2,
          sem_3: rec.sem_3,
          sem_4: rec.sem_4,
          sem_5: rec.sem_5,
          sem_6: rec.sem_6,
          exam_theory: rec.exam_theory,
          exam_practice: rec.exam_practice,
          final_exam_score: rec.final_exam_score,
        });
      }
    }
    const res = await api.post('/admin/graduation-grades/save', { grades: payload });
    if (res?.success) {
      hasUnsavedChanges.value = false;
      toast.success(res.message || `Nilai ${currentInputSemester.value.label} berhasil disimpan`);
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menyimpan nilai semester:', err);
    toast.error('Gagal menyimpan nilai: ' + (err.response?.data?.message || err.message));
  } finally {
    isSaving.value = false;
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
      report_weight: settings.value.report_weight ?? 60,
      exam_weight: settings.value.exam_weight ?? 40,
      theory_weight_default: settings.value.theory_weight_default ?? 50,
      practice_weight_default: settings.value.practice_weight_default ?? 50,
      active_subject_ids: Array.isArray(settings.value.active_subject_ids)
        ? [...settings.value.active_subject_ids]
        : allSubjects.value.map(s => s.id),
      practical_subject_ids: Array.isArray(settings.value.practical_subject_ids)
        ? [...settings.value.practical_subject_ids]
        : [],
      date_place: settings.value.date_place || 'Bogor',
      graduation_date: settings.value.graduation_date || '',
      certificate_number_prefix: settings.value.certificate_number_prefix || 'MTs.10.01/PP.01.1/',
      principal_name: settings.value.principal_name || schoolSetting.value?.principal_name || '',
      principal_nip: settings.value.principal_nip || schoolSetting.value?.principal_nip || '',
      nsm: settings.value.nsm || '121232010012',
      npsn: settings.value.npsn || '20277259',
      show_student_photo: settings.value.show_student_photo !== undefined ? Boolean(settings.value.show_student_photo) : true,
      academic_year: settings.value.academic_year || '2026/2027',
    };
  }
  showSettingsModal.value = true;
}

function selectAllActiveSubjects() {
  tempSettings.value.active_subject_ids = allSubjects.value.map(s => s.id);
}

function deselectAllActiveSubjects() {
  tempSettings.value.active_subject_ids = [];
}

async function saveSettings() {
  if (isSavingSettings.value) return;
  isSavingSettings.value = true;
  try {
    const res = await api.post('/admin/graduation-grades/settings', tempSettings.value);
    if (res?.success) {
      showSettingsModal.value = false;
      toast.success(res.message || 'Pengaturan bobot berhasil diperbarui');
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal menyimpan pengaturan bobot:', err);
    toast.error('Gagal menyimpan pengaturan bobot: ' + (err.response?.data?.message || err.message));
  } finally {
    isSavingSettings.value = false;
  }
}

// QR Code Validasi SKL Digital
const sklQrCodes = ref({});

async function generateSklQr(student) {
  if (!student || !student.id) return;
  if (sklQrCodes.value[student.id]) return;

  const academicYear = settings.value?.academic_year || '2026/2027';
  const yearStart = academicYear.split('/')[0] || '2026';
  const noSkl = student.skl_number || `MTs.AH/PP.01.1/SKL/${yearStart}/${student.nis || student.nisn || '001'}`;
  const payload = `VERIFIKASI RESMI SKL\nMadrasah: ${schoolSetting.value?.school_name || 'MTs Al-Hasanah'}\nNo. SKL: ${noSkl}\nNama: ${student.full_name}\nNISN: ${student.nisn || '-'}\nNIS: ${student.nis || '-'}\nStatus: LULUS\nTitimangsa: ${settings.value?.graduation_date || '-'}`;

  try {
    const url = await QRCode.toDataURL(payload, {
      width: 140,
      margin: 1,
      color: { dark: '#000000', light: '#ffffff' },
    });
    sklQrCodes.value[student.id] = url;
  } catch (err) {
    console.error('QR code generation failed:', err);
  }
}

// Print Modal Individual
function openPrintModal(student, mode = 'both') {
  isBatchPrint.value = false;
  activePrintStudent.value = student;
  printMode.value = mode;
  if (student) generateSklQr(student);
  showPrintModal.value = true;
}

// Batch Print Modal (Satu Kelas Sekaligus)
function openBatchPrintModal(mode = 'both') {
  if (!filteredTranscriptStudents.value.length) {
    toast.error('Tidak ada siswa yang dipilih untuk dicetak massal.');
    return;
  }
  isBatchPrint.value = true;
  batchPrintStudents.value = [...filteredTranscriptStudents.value];
  printMode.value = mode;
  batchPrintStudents.value.forEach(stu => generateSklQr(stu));
  showPrintModal.value = true;
}

// Auto-Numbering Serial Ijazah & SKL
async function generateSerialNumbers() {
  if (isGeneratingSerial.value) return;
  isGeneratingSerial.value = true;
  try {
    const res = await api.post('/admin/graduation-grades/generate-serial-numbers', {
      class_id: selectedClassId.value || null,
      cert_prefix: tempSettings.value.certificate_number_prefix || 'MTs.10.01/PP.01.1/',
      cert_start_number: serialForm.value.cert_start,
      cert_digits: serialForm.value.cert_digits,
      skl_prefix: 'MTs.AH/PP.01.1/SKL/' + (tempSettings.value.academic_year?.split('/')[0] || new Date().getFullYear()) + '/',
      skl_start_number: serialForm.value.skl_start,
      order_by: serialForm.value.order_by,
    });
    if (res?.success) {
      toast.success(res.message || 'Nomor seri Ijazah & SKL berhasil di-generate.');
      await fetchData();
    }
  } catch (err) {
    console.error('Gagal generate nomor seri:', err);
    toast.error('Gagal generate nomor seri: ' + (err.response?.data?.message || err.message));
  } finally {
    isGeneratingSerial.value = false;
  }
}

// Quick Edit Serial Number for Single Student
function openEditSerialModal(student) {
  editingStudent.value = student;
  editSerialForm.value = {
    certificate_number: student.certificate_number || '',
    skl_number: student.skl_number || '',
  };
  showEditSerialModal.value = true;
}

async function saveStudentSerial() {
  if (!editingStudent.value) return;
  isSavingStudentSerial.value = true;
  try {
    const res = await api.put(`/admin/graduation-grades/students/${editingStudent.value.id}/serial-number`, {
      certificate_number: editSerialForm.value.certificate_number,
      skl_number: editSerialForm.value.skl_number,
    });
    if (res?.success) {
      toast.success(res.message || 'Nomor seri siswa berhasil diperbarui.');
      // Update local reactive student state
      const target = students.value.find(s => s.id === editingStudent.value.id);
      if (target) {
        target.certificate_number = editSerialForm.value.certificate_number;
        target.skl_number = editSerialForm.value.skl_number;
      }
      showEditSerialModal.value = false;
    }
  } catch (err) {
    console.error('Gagal memperbarui nomor seri siswa:', err);
    toast.error('Gagal memperbarui nomor seri: ' + (err.response?.data?.message || err.message));
  } finally {
    isSavingStudentSerial.value = false;
  }
}

function triggerPrint() {
  window.print();
}

// Summary helpers
function getStudentCompletedSubjectCount(studentId, field = 'final_grade') {
  const map = gradeMatrix.value[studentId] || {};
  return Object.values(map).filter(r => r && r[field] !== null && r[field] !== undefined && r[field] !== '' && !isNaN(r[field]) && parseFloat(r[field]) > 0).length;
}

function getStudentTotalScore(studentId, field = 'final_grade') {
  const map = gradeMatrix.value[studentId] || {};
  let total = 0;
  let count = 0;
  for (const sub of subjects.value) {
    const rec = map[sub.id];
    if (rec && rec[field] !== null && rec[field] !== undefined && rec[field] !== '' && !isNaN(rec[field])) {
      total += parseFloat(rec[field]);
      count++;
    }
  }
  return count > 0 ? Number(total.toFixed(2)) : null;
}

function getStudentOverallAverage(studentId, field = 'final_grade') {
  const map = gradeMatrix.value[studentId] || {};
  const vals = [];
  for (const sub of subjects.value) {
    const rec = map[sub.id];
    if (rec && rec[field] !== null && rec[field] !== undefined && rec[field] !== '' && !isNaN(rec[field])) {
      vals.push(parseFloat(rec[field]));
    }
  }
  if (vals.length === 0) return null;
  return Number((vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(2));
}

// Export Leger CSV for active mode
function exportLedgerCsv() {
  if (!filteredLedgerStudents.value.length) {
    toast.error('Tidak ada data siswa untuk diekspor');
    return;
  }

  const mode = ledgerMode.value;
  const modeObj = currentLedgerModeObj.value;

  const headerCols = ['No', 'Nama Siswa', 'NISN', 'Kelas'];
  subjects.value.forEach(s => {
    headerCols.push(`"${(s.name || s.code).replace(/"/g, '""')}"`);
  });
  headerCols.push('Total Nilai', 'Rata-Rata', 'Kelengkapan');

  const rows = [headerCols.join(',')];

  filteredLedgerStudents.value.forEach((stu, idx) => {
    const row = [
      idx + 1,
      `"${(stu.full_name || '').replace(/"/g, '""')}"`,
      `"${(stu.nisn || '').replace(/"/g, '""')}"`,
      `"${(stu.class_name || '').replace(/"/g, '""')}"`
    ];

    subjects.value.forEach(sub => {
      const rec = getStudentGradeRecord(stu.id, sub.id);
      const val = rec && rec[mode] !== null && rec[mode] !== undefined && rec[mode] !== '' ? rec[mode] : '';
      row.push(val);
    });

    const tot = getStudentTotalScore(stu.id, mode);
    const avg = getStudentOverallAverage(stu.id, mode);
    const comp = `${getStudentCompletedSubjectCount(stu.id, mode)}/${subjects.value.length}`;

    row.push(tot !== null ? tot : '');
    row.push(avg !== null ? avg : '');
    row.push(`"${comp}"`);

    rows.push(row.join(','));
  });

  const csvContent = '\uFEFF' + rows.join('\r\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.setAttribute('href', url);
  const cleanName = (modeObj.shortLabel || 'Leger').replace(/[^a-zA-Z0-9_-]/g, '_');
  link.setAttribute('download', `Leger_${cleanName}_Kelas9.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  toast.success(`Berhasil mengekspor Leger (${modeObj.shortLabel}) ke CSV`);
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
  @page {
    size: A4 portrait;
    margin: 10mm 12mm 10mm 12mm;
  }
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
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    border: none !important;
    background: transparent !important;
  }
  .no-print {
    display: none !important;
  }
  .page-break {
    page-break-after: always !important;
    break-after: page !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    display: block !important;
    visibility: hidden !important;
  }
  .sheet-page {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    margin-bottom: 0 !important;
    width: 100% !important;
  }
}
</style>
