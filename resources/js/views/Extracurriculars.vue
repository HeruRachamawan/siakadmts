<template>
  <div class="space-y-6 font-inter pb-12">
    <!-- 1. Header Banner (Madrasah Emerald Gradient) -->
    <div class="relative bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 text-white rounded-2xl sm:rounded-[2rem] p-5 sm:p-7 shadow-sm overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5 border border-emerald-700/60">
      <div class="absolute right-0 top-0 bottom-0 w-96 bg-radial from-emerald-500/10 to-transparent pointer-events-none"></div>

      <div class="relative z-10 flex items-center gap-4">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/10 rounded-2xl border border-white/20 p-2.5 flex items-center justify-center shadow-inner flex-shrink-0">
          <Tent class="w-7 h-7 sm:w-8 sm:h-8 text-emerald-300" />
        </div>
        <div>
          <div class="flex items-center gap-2 mb-1 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
              Pengembangan Diri & Karakter
            </span>
            <span v-if="isAdvisor" class="px-2 py-0.5 bg-amber-400/20 text-amber-200 border border-amber-300/30 rounded-full text-[10px] font-bold">
              Pembina Ekskul
            </span>
          </div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white font-lexend">
            {{ isAdvisorView ? 'Penilaian Ekstrakurikuler Madrasah' : 'Manajemen & Penilaian Ekstrakurikuler' }}
          </h1>
          <p class="text-xs text-emerald-100/90 font-medium mt-0.5">
            Penilaian predikat (A, B, C) untuk kegiatan Pramuka & ekskul pilihan siswa yang otomatis terhubung ke Rapor ASTS.
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="relative z-10 flex items-center gap-2 self-start md:self-auto flex-wrap">
        <button
          v-if="canManageMaster"
          type="button"
          @click="openCreateModal"
          class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-emerald-950/20 flex items-center gap-2 cursor-pointer border border-emerald-400/40"
        >
          <PlusCircle class="w-4 h-4" />
          <span>Tambah Ekskul Baru</span>
        </button>
      </div>
    </div>

    <!-- 2. Navigasi Mode: Daftar Ekskul vs Lembar Penilaian -->
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3 flex-wrap">
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="activeView = 'list'"
          :class="[
            activeView === 'list'
              ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 font-bold'
              : 'bg-white hover:bg-slate-50 text-slate-700 font-semibold border border-slate-200',
            'px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer'
          ]"
        >
          <Layers class="w-3.5 h-3.5" />
          <span>Daftar Kegiatan ({{ extracurriculars.length }})</span>
        </button>

        <button
          v-if="selectedEkskul"
          type="button"
          @click="activeView = 'grading'"
          :class="[
            activeView === 'grading'
              ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 font-bold'
              : 'bg-white hover:bg-slate-50 text-slate-700 font-semibold border border-slate-200',
            'px-4 py-2 rounded-xl text-xs transition-all flex items-center gap-2 cursor-pointer'
          ]"
        >
          <CheckCircle2 class="w-3.5 h-3.5" />
          <span>Lembar Nilai: {{ selectedEkskul.name }}</span>
        </button>
      </div>

      <!-- Quick Filter Search for List -->
      <div v-if="activeView === 'list'" class="w-full sm:w-64 relative">
        <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama atau pembina..."
          class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none shadow-2xs"
        />
      </div>
    </div>

    <!-- ================= VIEW 1: DAFTAR EKSTRAKURIKULER ================= -->
    <div v-if="activeView === 'list'" class="space-y-4">
      <div v-if="loadingEkskuls" class="py-16 text-center text-slate-400">
        <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-2"></div>
        <p class="text-xs font-medium">Memuat data ekstrakurikuler...</p>
      </div>

      <div v-else-if="filteredEkskuls.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200/80 shadow-2xs">
        <Tent class="w-12 h-12 text-slate-300 mx-auto mb-3" />
        <h3 class="text-sm font-bold text-slate-700 font-lexend">
          {{ isAdvisorView ? 'Belum Ada Ekstrakurikuler yang Dibina' : 'Belum Ada Ekstrakurikuler' }}
        </h3>
        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
          {{ isAdvisorView 
              ? 'Akun Anda belum ditugaskan membina kegiatan ekstrakurikuler. Hubungi Administrator madrasah untuk penugasan.' 
              : 'Silakan tambahkan data kegiatan ekstrakurikuler baru untuk memulai penilaian predikat siswa.' }}
        </p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="ekskul in filteredEkskuls"
          :key="'ekskul-card-'+ekskul.id"
          class="bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-300 shadow-2xs hover:shadow-md transition-all duration-200 flex flex-col justify-between overflow-hidden group"
        >
          <div class="p-5 space-y-3.5">
            <!-- Top Tags -->
            <div class="flex items-center justify-between gap-2">
              <span
                v-if="ekskul.is_mandatory"
                class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1"
              >
                <span>⭐</span> Wajib Seluruh Siswa
              </span>
              <span
                v-else
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"
              >
                Pilihan Minat Bakat
              </span>

              <span :class="ekskul.is_active ? 'bg-emerald-500' : 'bg-slate-300'" class="w-2 h-2 rounded-full" :title="ekskul.is_active ? 'Aktif' : 'Non-aktif'"></span>
            </div>

            <!-- Title & Code -->
            <div>
              <h3 class="text-base font-black text-slate-800 font-lexend group-hover:text-emerald-700 transition-colors">
                {{ ekskul.name }}
              </h3>
              <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                {{ ekskul.description || 'Kegiatan ekstrakurikuler untuk membina bakat, minat, dan kepemimpinan santri madrasah.' }}
              </p>
            </div>

            <!-- Details Block -->
            <div class="bg-slate-50/80 rounded-xl p-3 border border-slate-100 text-xs space-y-1.5 font-medium text-slate-600">
              <div class="flex items-center justify-between">
                <span class="text-slate-400 text-[11px]">Guru Pembina:</span>
                <span class="font-bold text-slate-800 truncate max-w-[160px]">{{ ekskul.teacher?.full_name || 'Belum Ditentukan' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-slate-400 text-[11px]">Jadwal Latihan:</span>
                <span class="font-semibold text-slate-700">{{ ekskul.schedule_day || '-' }} {{ ekskul.schedule_time ? `(${ekskul.schedule_time})` : '' }}</span>
              </div>
            </div>
          </div>

          <!-- Bottom Action Bar -->
          <div class="px-5 py-3 bg-slate-50/90 border-t border-slate-100 flex items-center justify-between gap-2">
            <button
              type="button"
              @click="openGradingForEkskul(ekskul)"
              class="flex-1 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all shadow-2xs cursor-pointer"
            >
              <Award class="w-3.5 h-3.5" />
              <span>Input Nilai (A/B/C)</span>
            </button>

            <!-- Edit/Delete Action for Staff -->
            <div v-if="canManageMaster" class="flex items-center gap-1">
              <button
                type="button"
                @click="openEditModal(ekskul)"
                class="p-2 text-slate-500 hover:text-emerald-700 hover:bg-white rounded-lg transition-all cursor-pointer"
                title="Edit Informasi Ekskul"
              >
                <Pencil class="w-3.5 h-3.5" />
              </button>
              <button
                v-if="!ekskul.is_mandatory"
                type="button"
                @click="confirmDeleteEkskul(ekskul)"
                class="p-2 text-slate-500 hover:text-rose-600 hover:bg-white rounded-lg transition-all cursor-pointer"
                title="Hapus Ekskul"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= VIEW 2: LEMBAR PENILAIAN PREDIKAT (A, B, C) ================= -->
    <div v-else-if="activeView === 'grading' && selectedEkskul" class="space-y-4">
      <!-- Filter Bar & Context Controls -->
      <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200/80 space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="activeView = 'list'"
                class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-500 transition-colors cursor-pointer"
                title="Kembali ke Daftar Ekskul"
              >
                <ArrowLeft class="w-4 h-4" />
              </button>
              <h2 class="text-base font-black text-slate-800 font-lexend flex items-center gap-2">
                <span>Penilaian: {{ selectedEkskul.name }}</span>
                <span v-if="selectedEkskul.is_mandatory" class="text-[10px] px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-bold">Wajib</span>
              </h2>
            </div>
            <p class="text-xs text-slate-500 mt-0.5 ml-7">
              Pembina: <strong class="text-slate-800">{{ selectedEkskul.teacher?.full_name || '-' }}</strong> &bull;
              Nilai yang disimpan akan otomatis tertera pada Lembar Rapor ASTS siswa.
            </p>
          </div>

          <!-- Save Button -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="saveAllGrades"
              :disabled="savingGrades || gradingStudents.length === 0"
              class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 disabled:opacity-50 text-white font-black rounded-xl text-xs transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 cursor-pointer"
            >
              <Check class="w-4 h-4" />
              <span>{{ savingGrades ? 'Menyimpan...' : 'Simpan Semua Nilai Rapor' }}</span>
            </button>
          </div>
        </div>

        <!-- Filter Dropdowns (Kelas, Semester, Tahun) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-100">
          <div>
            <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Filter Rombel / Kelas</label>
            <select
              v-model="gradingFilterClass"
              @change="fetchGradingSheet"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-400 focus:outline-none cursor-pointer"
            >
              <option value="">-- Semua Kelas ({{ gradingClasses.length }} Rombel) --</option>
              <option v-for="cls in gradingClasses" :key="'cls-opt-'+cls.id" :value="cls.id">
                {{ cls.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Semester Rapor</label>
            <select
              v-model="gradingSemester"
              @change="fetchGradingSheet"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-400 focus:outline-none cursor-pointer"
            >
              <option value="ganjil">Semester Ganjil</option>
              <option value="genap">Semester Genap</option>
            </select>
          </div>

          <div>
            <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1">Aksi Cepat Nilai Massal</label>
            <div class="flex items-center gap-1.5">
              <button
                type="button"
                @click="setBulkGrade('A')"
                class="flex-1 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-black text-xs rounded-xl transition-all cursor-pointer"
                title="Atur semua siswa menjadi predikat A"
              >
                Set Semua A
              </button>
              <button
                type="button"
                @click="setBulkGrade('B')"
                class="flex-1 py-2 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-800 font-black text-xs rounded-xl transition-all cursor-pointer"
                title="Atur semua siswa menjadi predikat B"
              >
                Set Semua B
              </button>
              <button
                type="button"
                @click="setBulkGrade(null)"
                class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition-all cursor-pointer"
                title="Kosongkan seluruh nilai"
              >
                Reset
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Grading Sheet -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="p-3.5 sm:p-4 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between gap-2 flex-wrap">
          <span class="text-xs font-bold text-slate-700">
            Daftar Siswa Peserta ({{ gradingStudents.length }} Siswa)
          </span>
          <span class="text-[11px] text-slate-400">
            Predikat: <strong class="text-emerald-700">A (Sangat Baik)</strong> &bull; <strong class="text-blue-700">B (Baik)</strong> &bull; <strong class="text-amber-700">C (Cukup)</strong>
          </span>
        </div>

        <div v-if="loadingGrading" class="py-16 text-center text-slate-400">
          <div class="animate-spin h-7 w-7 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-2"></div>
          <p class="text-xs font-medium">Memuat lembar penilaian siswa...</p>
        </div>

        <div v-else-if="gradingStudents.length === 0" class="py-16 text-center text-slate-400 text-xs">
          Tidak ada data siswa pada filter ini.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-700 border-collapse">
            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-200">
              <tr>
                <th class="px-3 py-3 w-10 text-center">No</th>
                <th class="px-4 py-3 min-w-[180px]">Nama Lengkap Siswa</th>
                <th class="px-3 py-3 w-24 text-center">Kelas</th>
                <th class="px-3 py-3 w-48 text-center">Pilihan Predikat</th>
                <th class="px-4 py-3 min-w-[280px]">Capaian / Catatan Perkembangan (Untuk Rapor)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="(st, sIdx) in gradingStudents"
                :key="'grade-row-'+st.student_id"
                :class="st.grade ? 'bg-white' : 'bg-slate-50/30'"
                class="hover:bg-emerald-50/30 transition-colors"
              >
                <td class="px-3 py-3 text-center font-mono font-bold text-slate-400">{{ sIdx + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="font-black text-slate-800 font-lexend">{{ st.full_name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">NISN: {{ st.nisn || '-' }}</div>
                </td>
                <td class="px-3 py-3 text-center">
                  <span class="px-2 py-0.5 bg-slate-100 rounded-md font-bold text-slate-700 text-[11px]">
                    {{ st.class_name }}
                  </span>
                </td>

                <!-- Predicate Buttons / Selector -->
                <td class="px-3 py-3 text-center">
                  <div class="inline-flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl border border-slate-200">
                    <button
                      type="button"
                      @click="setStudentGrade(st, 'A')"
                      :class="st.grade === 'A' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'text-slate-600 hover:bg-white'"
                      class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    >
                      A
                    </button>
                    <button
                      type="button"
                      @click="setStudentGrade(st, 'B')"
                      :class="st.grade === 'B' ? 'bg-blue-600 text-white font-black shadow-xs' : 'text-slate-600 hover:bg-white'"
                      class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    >
                      B
                    </button>
                    <button
                      type="button"
                      @click="setStudentGrade(st, 'C')"
                      :class="st.grade === 'C' ? 'bg-amber-600 text-white font-black shadow-xs' : 'text-slate-600 hover:bg-white'"
                      class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                    >
                      C
                    </button>
                    <button
                      type="button"
                      @click="setStudentGrade(st, null)"
                      :class="!st.grade ? 'text-slate-400 font-bold' : 'text-slate-400 hover:text-rose-600'"
                      class="px-1.5 py-1 rounded-lg text-[10px] transition-all cursor-pointer"
                      title="Kosongkan nilai siswa ini"
                    >
                      ✕
                    </button>
                  </div>
                </td>

                <!-- Description Input -->
                <td class="px-4 py-2.5">
                  <input
                    v-model="st.description"
                    type="text"
                    placeholder="Keterangan capaian siswa..."
                    class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ================= MODAL TAMBAH / EDIT EKSTRAKURIKULER ================= -->
    <div
      v-if="showFormModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
      <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-base font-black text-slate-800 font-lexend">
            {{ isEditing ? 'Edit Informasi Ekstrakurikuler' : 'Tambah Ekstrakurikuler Baru' }}
          </h3>
          <button @click="showFormModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-3.5 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Kegiatan Ekstrakurikuler <span class="text-rose-500">*</span></label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Contoh: Pendidikan Kepramukaan, PMR, Futsal, Tahfidz"
              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Kode Singkat</label>
              <input
                v-model="form.code"
                type="text"
                placeholder="PRAMUKA / PMR"
                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none uppercase"
              />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Guru Pembina Utama</label>
              <select
                v-model="form.teacher_id"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none cursor-pointer"
              >
                <option :value="null">-- Belum Dipilih --</option>
                <option v-for="t in teachersList" :key="'t-opt-'+t.id" :value="t.id">
                  {{ t.full_name }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Hari Latihan</label>
              <input
                v-model="form.schedule_day"
                type="text"
                placeholder="Jumat / Sabtu"
                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-700 mb-1">Waktu Latihan</label>
              <input
                v-model="form.schedule_time"
                type="text"
                placeholder="14:00 - 16:00"
                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Deskripsi Kegiatan</label>
            <textarea
              v-model="form.description"
              rows="2"
              placeholder="Deskripsi singkat tujuan & aktivitas kegiatan..."
              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none"
            ></textarea>
          </div>

          <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200/80">
            <input
              id="chk-mandatory"
              v-model="form.is_mandatory"
              type="checkbox"
              class="w-4 h-4 text-emerald-600 rounded cursor-pointer"
            />
            <label for="chk-mandatory" class="font-bold text-slate-800 cursor-pointer">
              Ekskul Wajib Seluruh Siswa (Contoh: Pramuka)
              <span class="block font-normal text-slate-400 text-[11px]">Seluruh siswa madrasah otomatis menjadi peserta di lembar penilaian.</span>
            </label>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="showFormModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="submittingForm"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md shadow-emerald-600/20 cursor-pointer"
            >
              {{ submittingForm ? 'Menyimpan...' : (isEditing ? 'Perbarui Ekskul' : 'Simpan Ekskul') }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { api } from '../api';
import { useAuthStore } from '../stores/auth';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import {
  Tent,
  PlusCircle,
  Pencil,
  Trash2,
  Award,
  Layers,
  Search,
  Check,
  CheckCircle2,
  ArrowLeft
} from 'lucide-vue-next';

const toast = useToast();
const { confirm } = useConfirm();
const auth = useAuthStore();
const route = useRoute();

const activeView = ref('list'); // 'list' | 'grading'
const loadingEkskuls = ref(false);
const extracurriculars = ref([]);
const teachersList = ref([]);
const searchQuery = ref('');

// Grading Sheet State
const selectedEkskul = ref(null);
const loadingGrading = ref(false);
const savingGrades = ref(false);
const gradingStudents = ref([]);
const gradingClasses = ref([]);
const gradingSemester = ref('ganjil');
const gradingFilterClass = ref('');
const activeYear = ref(null);

// Form Modal State
const showFormModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const submittingForm = ref(false);

const form = reactive({
  name: '',
  code: '',
  description: '',
  teacher_id: null,
  is_mandatory: false,
  schedule_day: '',
  schedule_time: '',
  is_active: true,
});

const isAdvisorView = computed(() => route.path.startsWith('/teacher/'));
const canManageMaster = computed(() => ['admin', 'kurikulum', 'operator', 'kepala_sekolah'].includes(auth.role));
const isAdvisor = computed(() => {
  if (auth.role !== 'teacher') return false;
  return Boolean(auth.user?.is_extracurricular_advisor);
});

const filteredEkskuls = computed(() => {
  if (!searchQuery.value) return extracurriculars.value;
  const q = searchQuery.value.toLowerCase();
  return extracurriculars.value.filter(e =>
    (e.name || '').toLowerCase().includes(q) ||
    (e.teacher?.full_name || '').toLowerCase().includes(q) ||
    (e.code || '').toLowerCase().includes(q)
  );
});

async function fetchExtracurriculars() {
  loadingEkskuls.value = true;
  try {
    const res = await api.get('/teacher/extracurriculars');
    extracurriculars.value = res?.data || res || [];
  } catch (err) {
    toast.error('Gagal memuat data ekstrakurikuler.');
  } finally {
    loadingEkskuls.value = false;
  }
}

async function fetchTeachers() {
  if (!canManageMaster.value) return;
  try {
    const res = await api.get('/admin/teachers', { all: true, per_page: -1 });
    if (Array.isArray(res)) {
      teachersList.value = res;
    } else if (Array.isArray(res?.data)) {
      teachersList.value = res.data;
    } else if (Array.isArray(res?.data?.data)) {
      teachersList.value = res.data.data;
    } else {
      teachersList.value = [];
    }
  } catch (e) {
    console.warn('Gagal memuat daftar guru pembina:', e);
  }
}

function openGradingForEkskul(ekskul) {
  selectedEkskul.value = ekskul;
  activeView.value = 'grading';
  fetchGradingSheet();
}

async function fetchGradingSheet() {
  if (!selectedEkskul.value) return;
  loadingGrading.value = true;
  try {
    const res = await api.get(`/teacher/extracurriculars/${selectedEkskul.value.id}/grading-sheet`, {
      semester: gradingSemester.value,
      class_id: gradingFilterClass.value || undefined,
    });
    const d = res?.data || res || {};
    gradingStudents.value = d.students || [];
    gradingClasses.value = d.classes || [];
    activeYear.value = d.academic_year || null;
  } catch (err) {
    toast.error('Gagal memuat lembar penilaian ekstrakurikuler.');
  } finally {
    loadingGrading.value = false;
  }
}

function setStudentGrade(student, gradeVal) {
  student.grade = gradeVal;
  if (gradeVal && !student.description) {
    student.description = getDefaultDescription(gradeVal, selectedEkskul.value?.name);
  }
}

function getDefaultDescription(grade, ekskulName) {
  const name = ekskulName || 'ekstrakurikuler';
  return matchGradeDesc(grade, name);
}

function matchGradeDesc(grade, name) {
  if (grade === 'A') return `Sangat aktif, bersemangat, dan menunjukkan kecakapan istimewa dalam kegiatan ${name}.`;
  if (grade === 'B') return `Aktif dan mampu mengikuti seluruh rangkaian kegiatan ${name} dengan baik.`;
  if (grade === 'C') return `Cukup aktif dalam kegiatan ${name}, perlu peningkatan kedisiplinan dan keaktifan.`;
  return `Mengikuti kegiatan ${name}.`;
}

function setBulkGrade(gradeVal) {
  gradingStudents.value.forEach(st => {
    st.grade = gradeVal;
    if (gradeVal) {
      st.description = getDefaultDescription(gradeVal, selectedEkskul.value?.name);
    } else {
      st.description = '';
    }
  });
  if (gradeVal) {
    toast.info(`Seluruh siswa diatur menjadi Predikat ${gradeVal}.`);
  } else {
    toast.info('Seluruh nilai direset.');
  }
}

async function saveAllGrades() {
  if (!selectedEkskul.value) return;
  savingGrades.value = true;
  try {
    const payload = {
      semester: gradingSemester.value,
      academic_year_id: activeYear.value?.id,
      grades: gradingStudents.value.map(st => ({
        student_id: st.student_id,
        grade: st.grade || null,
        description: st.description || null,
      })),
    };

    const res = await api.post(`/teacher/extracurriculars/${selectedEkskul.value.id}/grades`, payload);
    toast.success(res?.message || 'Nilai ekstrakurikuler berhasil disimpan!');
    await fetchGradingSheet();
  } catch (err) {
    toast.error(err?.response?.data?.message || 'Gagal menyimpan nilai ekstrakurikuler.');
  } finally {
    savingGrades.value = false;
  }
}

function openCreateModal() {
  isEditing.value = false;
  editingId.value = null;
  form.name = '';
  form.code = '';
  form.description = '';
  form.teacher_id = null;
  form.is_mandatory = false;
  form.schedule_day = '';
  form.schedule_time = '';
  form.is_active = true;
  showFormModal.value = true;
}

function openEditModal(ekskul) {
  isEditing.value = true;
  editingId.value = ekskul.id;
  form.name = ekskul.name;
  form.code = ekskul.code;
  form.description = ekskul.description;
  form.teacher_id = ekskul.teacher_id;
  form.is_mandatory = Boolean(ekskul.is_mandatory);
  form.schedule_day = ekskul.schedule_day;
  form.schedule_time = ekskul.schedule_time;
  form.is_active = ekskul.is_active;
  showFormModal.value = true;
}

async function submitForm() {
  submittingForm.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/admin/extracurriculars/${editingId.value}`, form);
      toast.success('Ekstrakurikuler berhasil diperbarui!');
    } else {
      await api.post('/admin/extracurriculars', form);
      toast.success('Ekstrakurikuler baru berhasil ditambahkan!');
    }
    showFormModal.value = false;
    await fetchExtracurriculars();
  } catch (err) {
    toast.error(err?.response?.data?.message || 'Gagal menyimpan data ekstrakurikuler.');
  } finally {
    submittingForm.value = false;
  }
}

async function confirmDeleteEkskul(ekskul) {
  const confirmed = await confirm({
    title: 'Hapus Ekstrakurikuler?',
    message: `Apakah Anda yakin ingin menghapus ekstrakurikuler '${ekskul.name}'? Seluruh riwayat nilai akan terhapus.`,
    confirmText: 'Ya, Hapus',
    confirmType: 'danger'
  });
  if (!confirmed) return;

  try {
    await api.delete(`/admin/extracurriculars/${ekskul.id}`);
    toast.success(`Ekstrakurikuler ${ekskul.name} berhasil dihapus.`);
    await fetchExtracurriculars();
  } catch (err) {
    toast.error('Gagal menghapus ekstrakurikuler.');
  }
}

onMounted(() => {
  fetchExtracurriculars();
  fetchTeachers();
});
</script>
