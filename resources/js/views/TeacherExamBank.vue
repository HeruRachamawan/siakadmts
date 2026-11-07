<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
      <div class="flex items-center gap-3">
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
          <BookOpen class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-xl font-bold text-slate-800">Bank Soal & Kisi-Kisi Ujian</h1>
          <p class="text-sm text-slate-500">Penyusunan naskah soal, kunci jawaban, dan kisi-kisi terintegrasi otomatis untuk telaah kurikulum</p>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          @click="downloadTemplate"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition"
        >
          <FileSpreadsheet class="w-4 h-4 text-emerald-600" />
          <span>Template Excel</span>
        </button>
        <button
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition"
        >
          <Plus class="w-4 h-4" />
          <span>Buat Paket Soal</span>
        </button>
      </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tahun Ajaran</label>
          <select v-model="filters.academic_year_id" @change="fetchPackages" class="form-select">
            <option value="">Semua Tahun Ajaran</option>
            <option v-for="y in options.academic_years" :key="y.id" :value="y.id">{{ y.year }} ({{ y.semester }})</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Mata Pelajaran</label>
          <select v-model="filters.subject_id" @change="fetchPackages" class="form-select">
            <option value="">Semua Mata Pelajaran</option>
            <option v-for="s in options.subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kelas</label>
          <select v-model="filters.class_room_id" @change="fetchPackages" class="form-select">
            <option value="">Semua Kelas</option>
            <option v-for="c in options.classes" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status Kurikulum</label>
          <select v-model="filters.status_kurikulum" @change="fetchPackages" class="form-select">
            <option value="">Semua Status</option>
            <option value="draft">Draft / Belum Diajukan</option>
            <option value="submitted">Menunggu Telaah</option>
            <option value="revision_needed">Perlu Revisi</option>
            <option value="approved">Disetujui / Approved</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Pencarian</label>
          <div class="relative">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              v-model="filters.search"
              @keyup.enter="fetchPackages"
              placeholder="Cari judul soal..."
              class="form-input pl-9"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Package List -->
    <div v-if="loading" class="flex justify-center py-16">
      <div class="flex flex-col items-center gap-3">
        <div class="w-8 h-8 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-slate-500">Memuat paket soal...</p>
      </div>
    </div>

    <div v-else-if="packages.length === 0" class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-sm">
      <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3">
        <FileQuestion class="w-7 h-7" />
      </div>
      <h3 class="text-base font-bold text-slate-800">Belum Ada Paket Soal</h3>
      <p class="text-sm text-slate-500 max-w-md mx-auto mt-1 mb-5">
        Mulai buat paket soal dan susun kisi-kisi terintegrasi agar siap ditelaah kurikulum.
      </p>
      <button
        type="button"
        @click="openCreateModal"
        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition"
      >
        <Plus class="w-4 h-4" />
        <span>Buat Paket Soal Pertama</span>
      </button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      <div
        v-for="pkg in packages"
        :key="pkg.id"
        class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden"
      >
        <!-- Card Header -->
        <div class="p-5 border-b border-slate-100">
          <div class="flex items-start justify-between gap-3 mb-2">
            <span
              class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
              :class="getStatusBadgeClass(pkg.status_kurikulum)"
            >
              {{ getStatusLabel(pkg.status_kurikulum) }}
            </span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
              {{ pkg.exam_type }}
            </span>
          </div>

          <h3 class="text-base font-bold text-slate-800 line-clamp-1 mb-1" :title="pkg.title">
            {{ pkg.title }}
          </h3>

          <div class="text-xs text-slate-500 space-y-1">
            <div class="flex items-center gap-1.5">
              <GraduationCap class="w-3.5 h-3.5 text-slate-400" />
              <span>{{ pkg.subject?.name }} &bull; Kelas {{ pkg.class_room?.name }}</span>
            </div>
            <div class="flex items-center gap-1.5">
              <Calendar class="w-3.5 h-3.5 text-slate-400" />
              <span>Semester {{ pkg.semester === 'ganjil' ? 'Ganjil' : 'Genap' }} ({{ pkg.academic_phase || 'Fase D' }})</span>
            </div>
          </div>
        </div>

        <!-- Card Body / Stats -->
        <div class="p-5 bg-slate-50/50 space-y-3">
          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
              <div class="text-xs text-slate-400">Total Soal</div>
              <div class="text-base font-bold text-slate-800">{{ pkg.questions_count }}</div>
            </div>
            <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
              <div class="text-xs text-slate-400">Pilihan Ganda</div>
              <div class="text-base font-bold text-emerald-600">{{ pkg.pg_count }}</div>
            </div>
            <div class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs">
              <div class="text-xs text-slate-400">Uraian/Essay</div>
              <div class="text-base font-bold text-blue-600">{{ pkg.essay_count }}</div>
            </div>
          </div>

          <!-- Revision Notes alert if any -->
          <div v-if="pkg.status_kurikulum === 'revision_needed' && pkg.review_notes" class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
            <div class="font-bold flex items-center gap-1 mb-1">
              <AlertCircle class="w-3.5 h-3.5" />
              Catatan Telaah Kurikulum:
            </div>
            <p class="line-clamp-2 italic">{{ pkg.review_notes }}</p>
          </div>
        </div>

        <!-- Card Actions -->
        <div class="p-4 bg-white border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
          <div class="flex items-center gap-1.5">
            <button
              type="button"
              @click="openEditor(pkg.id)"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition"
            >
              <Edit3 class="w-3.5 h-3.5" />
              <span>Kelola Soal & Kisi-Kisi</span>
            </button>
            <button
              type="button"
              @click="openImportModal(pkg)"
              class="inline-flex items-center gap-1 p-1.5 text-slate-600 hover:text-emerald-700 bg-slate-100 hover:bg-emerald-50 rounded-lg transition"
              title="Import dari Excel"
            >
              <Upload class="w-4 h-4" />
            </button>
          </div>

          <div class="flex items-center gap-1">
            <!-- Print Dropdown -->
            <div class="relative group">
              <button
                type="button"
                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition"
              >
                <Printer class="w-3.5 h-3.5 text-slate-600" />
                <span>Cetak</span>
                <ChevronDown class="w-3 h-3" />
              </button>
              <div class="hidden group-hover:block absolute right-0 bottom-full mb-1 w-48 bg-white border border-slate-200 rounded-xl shadow-lg z-20 py-1.5 text-xs text-slate-700">
                <a :href="`/prints/exam/${pkg.id}/naskah?layout=2col`" target="_blank" class="block px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 font-medium">📄 Naskah 2 Kolom (Hemat)</a>
                <a :href="`/prints/exam/${pkg.id}/naskah?layout=1col`" target="_blank" class="block px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700">📄 Naskah 1 Kolom</a>
                <a :href="`/prints/exam/${pkg.id}/kisi-kisi`" target="_blank" class="block px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700">📊 Kisi-Kisi Resmi</a>
                <a :href="`/prints/exam/${pkg.id}/kartu-soal`" target="_blank" class="block px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700">🗂️ Kartu Soal</a>
                <a :href="`/prints/exam/${pkg.id}/telaah`" target="_blank" class="block px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700">📝 Berita Acara Telaah</a>
                <a :href="`/prints/exam/${pkg.id}/kunci`" target="_blank" class="block px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700">🔑 Kunci & Rubrik Nilai</a>
              </div>
            </div>

            <button
              v-if="pkg.status_kurikulum !== 'approved'"
              type="button"
              @click="submitToKurikulum(pkg)"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition"
              title="Ajukan ke Kurikulum"
            >
              <Send class="w-3.5 h-3.5" />
              <span>Ajukan</span>
            </button>
            <button
              v-if="pkg.status_kurikulum !== 'approved'"
              type="button"
              @click="deletePackage(pkg)"
              class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition"
              title="Hapus Paket Soal"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Buat Paket Soal Baru -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="text-base font-bold text-slate-800">Buat Paket Soal Baru</h3>
          <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>
        <form @submit.prevent="createPackage" class="p-6 space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Judul Paket Soal *</label>
            <input type="text" v-model="createForm.title" placeholder="Contoh: STS Ganjil Matematika Kelas 7" required class="form-input" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran *</label>
              <select v-model="createForm.subject_id" required class="form-select">
                <option value="">Pilih Mapel</option>
                <option v-for="s in options.subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Kelas Target *</label>
              <select v-model="createForm.class_room_id" required class="form-select">
                <option value="">Pilih Kelas</option>
                <option v-for="c in options.classes" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Ujian *</label>
              <select v-model="createForm.exam_type" required class="form-select">
                <option v-for="t in options.exam_types" :key="t.value" :value="t.value">{{ t.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Semester *</label>
              <select v-model="createForm.semester" required class="form-select">
                <option value="ganjil">Ganjil</option>
                <option value="genap">Genap</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Butir Soal *</label>
              <input type="number" v-model.number="createForm.total_questions" min="1" max="100" required class="form-input" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">KKM / KKTP</label>
              <input type="number" v-model.number="createForm.kkm" min="0" max="100" class="form-input" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Kurikulum / Fase</label>
              <select v-model="createForm.academic_phase" class="form-select">
                <option value="Fase D">Fase D (Kurikulum Merdeka MTs)</option>
                <option value="K-13">Kurikulum 2013 (K13)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Bobot PG : Essay (%)</label>
              <div class="flex items-center gap-2">
                <input type="number" v-model.number="createForm.pg_weight" placeholder="PG" class="form-input text-center" />
                <span class="text-xs text-slate-400">:</span>
                <input type="number" v-model.number="createForm.essay_weight" placeholder="Essay" class="form-input text-center" />
              </div>
            </div>
          </div>
          <div class="pt-2 flex justify-end gap-2">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50">
              Batal
            </button>
            <button type="submit" :disabled="submitting" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm">
              {{ submitting ? 'Membuat...' : 'Buat Paket Soal' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Upload Excel -->
    <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
          <h3 class="text-base font-bold text-slate-800">Import Soal dari Excel</h3>
          <button @click="showImportModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-xs text-slate-500">
            Pastikan format kolom sesuai dengan <strong>Template Excel Resmi</strong>. Teks soal, opsi A-D, kunci, materi, indikator, dan level kognitif akan langsung terisi.
          </p>
          <div class="border-2 border-dashed border-slate-200 hover:border-emerald-500 rounded-xl p-6 text-center cursor-pointer transition">
            <input type="file" ref="excelFileInput" accept=".xlsx, .xls" class="hidden" @change="handleExcelFileSelect" />
            <div @click="$refs.excelFileInput.click()" class="space-y-2">
              <UploadCloud class="w-8 h-8 text-emerald-600 mx-auto" />
              <div class="text-xs font-semibold text-slate-700">
                {{ selectedExcelFile ? selectedExcelFile.name : 'Klik untuk memilih file Excel (.xlsx)' }}
              </div>
              <div class="text-[11px] text-slate-400">Maksimal ukuran file 5 MB</div>
            </div>
          </div>
          <div class="pt-2 flex justify-end gap-2">
            <button type="button" @click="showImportModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50">
              Batal
            </button>
            <button
              type="button"
              @click="submitImportExcel"
              :disabled="!selectedExcelFile || importing"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-sm font-semibold shadow-sm"
            >
              {{ importing ? 'Mengimpor...' : 'Mulai Import' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- FULLSCREEN QUESTION & KISI-KISI EDITOR DRAWER -->
    <div v-if="activePackage" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex flex-col justify-end lg:justify-center p-0 lg:p-6 overflow-hidden">
      <div class="bg-white w-full h-full lg:max-w-6xl lg:h-[94vh] lg:rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        <!-- Editor Header -->
        <div class="px-6 py-3.5 bg-slate-900 text-white flex items-center justify-between shrink-0">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-emerald-500/20 text-emerald-400 rounded-lg">
              <BookOpen class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-base font-bold line-clamp-1">{{ activePackage.title }}</h2>
              <p class="text-xs text-slate-400">
                {{ activePackage.subject?.name }} &bull; Kelas {{ activePackage.class_room?.name }} &bull; {{ activeQuestions.length }} Butir Soal
              </p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="saveAllQuestions"
              :disabled="savingQuestions"
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition"
            >
              <Save class="w-4 h-4" />
              <span>{{ savingQuestions ? 'Menyimpan...' : 'Simpan Semua' }}</span>
            </button>
            <button
              type="button"
              @click="closeEditor"
              class="p-1.5 text-slate-400 hover:text-white rounded-lg transition"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Editor Content (2 Columns: Navigation Pills & Question Form) -->
        <div class="flex-1 flex flex-col lg:flex-row overflow-hidden bg-slate-50">
          <!-- Left: Question Number Nav Pills -->
          <div class="w-full lg:w-64 bg-white border-r border-slate-200 p-4 shrink-0 flex flex-col justify-between overflow-y-auto">
            <div>
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Nomor Soal</span>
                <button
                  type="button"
                  @click="addNewQuestion"
                  class="text-xs text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1"
                >
                  <Plus class="w-3.5 h-3.5" /> Tambah
                </button>
              </div>
              <div class="grid grid-cols-5 gap-2">
                <button
                  v-for="(q, idx) in activeQuestions"
                  :key="q.question_number"
                  type="button"
                  @click="currentQuestionIndex = idx"
                  class="h-10 rounded-xl text-xs font-bold border transition flex flex-col items-center justify-center relative"
                  :class="[
                    currentQuestionIndex === idx
                      ? 'bg-slate-900 text-white border-slate-900 shadow-sm'
                      : q.correct_answer && q.indicator
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                        : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'
                  ]"
                >
                  <span>{{ q.question_number }}</span>
                  <span v-if="q.question_type === 'pg' && q.correct_answer" class="text-[9px] font-bold text-emerald-600 uppercase">
                    {{ q.correct_answer }}
                  </span>
                  <span v-else-if="q.question_type === 'essay'" class="text-[9px] text-blue-600 font-semibold">
                    Ess
                  </span>
                </button>
              </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 space-y-1">
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span>Hijau: Soal & Kisi-kisi Terisi</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                <span>Putih: Belum Lengkap</span>
              </div>
            </div>
          </div>

          <!-- Right: Active Question Form -->
          <div v-if="activeQuestions[currentQuestionIndex]" class="flex-1 p-6 overflow-y-auto space-y-5">
            <!-- Question Meta Header -->
            <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
              <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-slate-900 text-white font-bold text-sm rounded-lg">
                  Soal No. {{ activeQuestions[currentQuestionIndex].question_number }}
                </span>
                <select v-model="activeQuestions[currentQuestionIndex].question_type" class="form-select py-1 text-xs font-bold">
                  <option value="pg">Pilihan Ganda (PG)</option>
                  <option value="essay">Uraian / Essay</option>
                </select>
              </div>
              <div class="flex items-center gap-2">
                <label class="text-xs text-slate-500 font-medium">Bobot Skor:</label>
                <input
                  type="number"
                  v-model.number="activeQuestions[currentQuestionIndex].score_weight"
                  step="0.5"
                  min="0.5"
                  class="form-input w-20 py-1 text-xs text-center font-bold"
                />
              </div>
            </div>

            <!-- Teks Soal & Stimulus -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-slate-700">Teks Soal / Pertanyaan *</label>
                <!-- Image upload trigger -->
                <div>
                  <input
                    type="file"
                    :ref="'qImage_' + activeQuestions[currentQuestionIndex].question_number"
                    accept="image/*"
                    class="hidden"
                    @change="handleQuestionImageUpload($event, activeQuestions[currentQuestionIndex])"
                  />
                  <button
                    type="button"
                    @click="$refs['qImage_' + activeQuestions[currentQuestionIndex].question_number][0]?.click()"
                    class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1"
                  >
                    <Image class="w-3.5 h-3.5" />
                    <span>{{ activeQuestions[currentQuestionIndex].question_image ? 'Ganti Stimulus Gambar' : '+ Unggah Gambar/Diagram' }}</span>
                  </button>
                </div>
              </div>

              <!-- Question text area -->
              <textarea
                v-model="activeQuestions[currentQuestionIndex].question_text"
                rows="4"
                placeholder="Tuliskan rumusan butir soal secara jelas dan tegas di sini..."
                class="form-textarea"
              ></textarea>

              <!-- Image Preview if any -->
              <div v-if="activeQuestions[currentQuestionIndex].question_image" class="relative inline-block border border-slate-200 rounded-lg p-1 bg-slate-50">
                <img :src="getStorageUrl(activeQuestions[currentQuestionIndex].question_image)" class="max-h-36 rounded" alt="Stimulus" />
                <button
                  type="button"
                  @click="activeQuestions[currentQuestionIndex].question_image = null"
                  class="absolute -top-2 -right-2 p-1 bg-rose-600 text-white rounded-full shadow"
                  title="Hapus gambar"
                >
                  <X class="w-3 h-3" />
                </button>
              </div>

              <!-- Options A, B, C, D (If PG) -->
              <div v-if="activeQuestions[currentQuestionIndex].question_type === 'pg'" class="space-y-2 pt-2">
                <div class="text-xs font-bold text-slate-600 mb-1 flex items-center justify-between">
                  <span>Pilihan Jawaban & Kunci:</span>
                  <span class="text-[11px] text-emerald-600 font-semibold">Klik lingkaran untuk memilih Kunci Benar</span>
                </div>

                <div
                  v-for="opt in ['A', 'B', 'C', 'D']"
                  :key="opt"
                  class="flex items-center gap-2 p-2 rounded-xl border transition"
                  :class="activeQuestions[currentQuestionIndex].correct_answer === opt ? 'bg-emerald-50/70 border-emerald-300' : 'bg-slate-50 border-slate-200'"
                >
                  <button
                    type="button"
                    @click="activeQuestions[currentQuestionIndex].correct_answer = opt"
                    class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 transition"
                    :class="activeQuestions[currentQuestionIndex].correct_answer === opt ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-300 hover:border-emerald-500'"
                  >
                    {{ opt }}
                  </button>
                  <input
                    type="text"
                    v-model="ensureOptions(activeQuestions[currentQuestionIndex])[opt]"
                    :placeholder="'Tulis pilihan ' + opt + '...'"
                    class="form-input flex-1 py-1.5 text-xs bg-white"
                  />
                  <span v-if="activeQuestions[currentQuestionIndex].correct_answer === opt" class="text-xs font-bold text-emerald-700 px-2 py-0.5 bg-emerald-100 rounded">
                    KUNCI
                  </span>
                </div>
              </div>

              <!-- Essay Rubric & Key (If Essay) -->
              <div v-else class="space-y-2 pt-2">
                <label class="block text-xs font-bold text-slate-700">Kunci Jawaban / Pedoman Penskoran Uraian</label>
                <textarea
                  v-model="activeQuestions[currentQuestionIndex].correct_answer"
                  rows="3"
                  placeholder="Tuliskan kata kunci jawaban yang diharapkan dan kriteria pemberian skor..."
                  class="form-textarea"
                ></textarea>
              </div>
            </div>

            <!-- Integrated Kisi-Kisi Card -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-2xs space-y-4">
              <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                <TableProperties class="w-4 h-4 text-emerald-600" />
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pemetaan Kisi-Kisi Butir Soal</h4>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-semibold text-slate-600 mb-1">Materi Pokok / Topik *</label>
                  <input
                    type="text"
                    v-model="activeQuestions[currentQuestionIndex].material"
                    placeholder="Contoh: Operasi Aljabar, Fiqih Thaharah, dll."
                    class="form-input text-xs"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-600 mb-1">Level Kognitif (Standar BSNP/Kemenag) *</label>
                  <select v-model="activeQuestions[currentQuestionIndex].cognitive_level" class="form-select text-xs">
                    <option v-for="l in options.cognitive_levels" :key="l.value" :value="l.value">{{ l.label }}</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Indikator Soal (Untuk Format Kisi-Kisi Resmi) *</label>
                <textarea
                  v-model="activeQuestions[currentQuestionIndex].indicator"
                  rows="2"
                  placeholder="Contoh: Disajikan tabel data, siswa dapat menganalisis dan menentukan rata-rata hitung dengan tepat."
                  class="form-textarea text-xs"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-semibold text-slate-600 mb-1">Capaian Pembelajaran (CP) / KD</label>
                  <input
                    type="text"
                    v-model="activeQuestions[currentQuestionIndex].basic_competency"
                    placeholder="Elemen CP / KD terkait"
                    class="form-input text-xs"
                  />
                </div>
                <div>
                  <label class="block text-xs font-semibold text-slate-600 mb-1">Estimasi Tingkat Kesukaran</label>
                  <select v-model="activeQuestions[currentQuestionIndex].difficulty_level" class="form-select text-xs">
                    <option value="mudah">Mudah</option>
                    <option value="sedang">Sedang</option>
                    <option value="sukar">Sukar</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Bottom Navigation Bar inside drawer -->
            <div class="flex items-center justify-between pt-2">
              <button
                type="button"
                @click="prevQuestion"
                :disabled="currentQuestionIndex === 0"
                class="px-4 py-2 border border-slate-200 text-slate-700 disabled:opacity-40 rounded-xl text-xs font-semibold hover:bg-slate-50 transition"
              >
                &larr; Soal Sebelumnya
              </button>
              <div class="text-xs text-slate-400">
                Nomor {{ currentQuestionIndex + 1 }} dari {{ activeQuestions.length }}
              </div>
              <button
                type="button"
                @click="nextQuestion"
                :disabled="currentQuestionIndex === activeQuestions.length - 1"
                class="px-4 py-2 bg-slate-900 text-white disabled:opacity-40 rounded-xl text-xs font-semibold hover:bg-slate-800 transition"
              >
                Soal Berikutnya &rarr;
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api, { getImageUrl } from '../api';
import {
  BookOpen,
  Plus,
  FileSpreadsheet,
  Search,
  FileQuestion,
  GraduationCap,
  Calendar,
  AlertCircle,
  Edit3,
  Upload,
  UploadCloud,
  Printer,
  ChevronDown,
  Send,
  Trash2,
  X,
  Save,
  Image,
  TableProperties
} from 'lucide-vue-next';

// Data State
const loading = ref(false);
const packages = ref([]);
const options = reactive({
  classes: [],
  subjects: [],
  academic_years: [],
  active_year: null,
  exam_types: [],
  curriculum_types: [],
  cognitive_levels: [],
  difficulty_levels: []
});

const filters = reactive({
  academic_year_id: '',
  subject_id: '',
  class_room_id: '',
  status_kurikulum: '',
  search: '',
  my_exams_only: true
});

// Modals
const showCreateModal = ref(false);
const submitting = ref(false);
const createForm = reactive({
  title: '',
  subject_id: '',
  class_room_id: '',
  exam_type: 'sts',
  semester: 'ganjil',
  total_questions: 20,
  kkm: 75,
  academic_phase: 'Fase D',
  pg_weight: 70,
  essay_weight: 30
});

// Excel Import
const showImportModal = ref(false);
const targetImportPackage = ref(null);
const selectedExcelFile = ref(null);
const importing = ref(false);

// Fullscreen Editor
const activePackage = ref(null);
const activeQuestions = ref([]);
const currentQuestionIndex = ref(0);
const savingQuestions = ref(false);

// Methods
const fetchOptions = async () => {
  try {
    const res = await api.get('/teacher/exam-bank/options');
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      Object.assign(options, d.data || d);
      if (options.active_year && !filters.academic_year_id) {
        filters.academic_year_id = options.active_year.id;
      }
    }
  } catch (err) {
    console.error('Error fetching options:', err);
  }
};

const fetchPackages = async () => {
  loading.value = true;
  try {
    const res = await api.get('/teacher/exam-bank', filters);
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      packages.value = d.data?.data || d.data || [];
    }
  } catch (err) {
    console.error('Error fetching packages:', err);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  createForm.title = '';
  createForm.subject_id = options.subjects[0]?.id || '';
  createForm.class_room_id = options.classes[0]?.id || '';
  createForm.exam_type = 'sts';
  createForm.semester = 'ganjil';
  createForm.total_questions = 20;
  createForm.kkm = 75;
  createForm.academic_phase = 'Fase D';
  showCreateModal.value = true;
};

const createPackage = async () => {
  submitting.value = true;
  try {
    const res = await api.post('/teacher/exam-bank', createForm);
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      showCreateModal.value = false;
      await fetchPackages();
      const newId = d.data?.id || d.id;
      if (newId) openEditor(newId);
    }
  } catch (err) {
    alert(err.response?.data?.message || err.message || 'Gagal membuat paket soal.');
  } finally {
    submitting.value = false;
  }
};

const openEditor = async (packageId) => {
  try {
    const res = await api.get(`/teacher/exam-bank/${packageId}`);
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      activePackage.value = d.data || d;
      activeQuestions.value = activePackage.value.questions || [];
      currentQuestionIndex.value = 0;
    }
  } catch (err) {
    alert(err.response?.data?.message || err.message || 'Gagal memuat butir soal.');
  }
};

const closeEditor = () => {
  activePackage.value = null;
  activeQuestions.value = [];
  fetchPackages();
};

const ensureOptions = (q) => {
  if (!q.options || typeof q.options !== 'object') {
    q.options = { A: '', B: '', C: '', D: '' };
  }
  return q.options;
};

const addNewQuestion = () => {
  const nextNum = activeQuestions.value.length + 1;
  activeQuestions.value.push({
    question_number: nextNum,
    question_type: 'pg',
    question_text: '',
    options: { A: '', B: '', C: '', D: '' },
    correct_answer: null,
    score_weight: 1.0,
    material: '',
    indicator: '',
    cognitive_level: 'L1',
    difficulty_level: 'sedang'
  });
  currentQuestionIndex.value = activeQuestions.value.length - 1;
};

const prevQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
  }
};

const nextQuestion = () => {
  if (currentQuestionIndex.value < activeQuestions.value.length - 1) {
    currentQuestionIndex.value++;
  }
};

const saveAllQuestions = async () => {
  if (!activePackage.value) return;
  savingQuestions.value = true;
  try {
    const res = await api.post(`/teacher/exam-bank/${activePackage.value.id}/questions`, {
      questions: activeQuestions.value
    });
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      alert('Semua butir soal dan kisi-kisi berhasil disimpan!');
    }
  } catch (err) {
    alert(err.response?.data?.message || err.message || 'Gagal menyimpan butir soal.');
  } finally {
    savingQuestions.value = false;
  }
};

const handleQuestionImageUpload = async (event, question) => {
  const file = event.target.files[0];
  if (!file || !activePackage.value) return;

  const formData = new FormData();
  formData.append('image', file);

  try {
    const res = await api.postForm(`/teacher/exam-bank/${activePackage.value.id}/upload-image`, formData);
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      question.question_image = d.filename || d.data?.filename;
    }
  } catch (err) {
    alert(err.response?.data?.message || err.message || 'Gagal mengunggah gambar stimulus.');
  }
};

const getStorageUrl = (path) => {
  return getImageUrl(path);
};

const submitToKurikulum = async (pkg) => {
  if (!confirm(`Ajukan paket soal "${pkg.title}" ke Tim Kurikulum untuk ditelaah dan diverifikasi?`)) return;
  try {
    const res = await api.post(`/teacher/exam-bank/${pkg.id}/submit-kurikulum`);
    if (res.data?.status === 'success') {
      alert(res.data.message);
      fetchPackages();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal mengajukan paket soal.');
  }
};

const deletePackage = async (pkg) => {
  if (!confirm(`Hapus paket soal "${pkg.title}" beserta seluruh butir soalnya?`)) return;
  try {
    const res = await api.delete(`/teacher/exam-bank/${pkg.id}`);
    if (res.data?.status === 'success') {
      fetchPackages();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus paket soal.');
  }
};

// Excel Template & Import
const downloadTemplate = () => {
  window.open('/api/teacher/exam-bank/template', '_blank');
};

const openImportModal = (pkg) => {
  targetImportPackage.value = pkg;
  selectedExcelFile.value = null;
  showImportModal.value = true;
};

const handleExcelFileSelect = (event) => {
  selectedExcelFile.value = event.target.files[0] || null;
};

const submitImportExcel = async () => {
  if (!selectedExcelFile.value || !targetImportPackage.value) return;
  importing.value = true;
  const formData = new FormData();
  formData.append('file', selectedExcelFile.value);

  try {
    const res = await api.post(`/teacher/exam-bank/${targetImportPackage.value.id}/import-excel`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    if (res.data?.status === 'success') {
      alert(res.data.message);
      showImportModal.value = false;
      fetchPackages();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal mengimpor file Excel.');
  } finally {
    importing.value = false;
  }
};

// Status Helpers
const getStatusLabel = (status) => {
  switch (status) {
    case 'submitted': return 'Menunggu Telaah';
    case 'revision_needed': return 'Perlu Revisi';
    case 'approved': return 'Disetujui Kurikulum';
    default: return 'Draft';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'submitted': return 'bg-amber-100 text-amber-800';
    case 'revision_needed': return 'bg-rose-100 text-rose-800';
    case 'approved': return 'bg-emerald-100 text-emerald-800';
    default: return 'bg-slate-100 text-slate-700';
  }
};

onMounted(async () => {
  await fetchOptions();
  await fetchPackages();
});
</script>

<style scoped>
.form-input, .form-select, .form-textarea {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}
</style>
