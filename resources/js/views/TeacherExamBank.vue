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
        class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between"
      >
        <!-- Card Header -->
        <div class="p-5 border-b border-slate-100 rounded-t-2xl">
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
        <div class="p-4 bg-white border-t border-slate-100 flex flex-col gap-2 rounded-b-2xl">
          <!-- Primary Actions -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="openEditor(pkg.id)"
              class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-2xs transition"
            >
              <Edit3 class="w-3.5 h-3.5" />
              <span>Kelola Soal & Kisi-Kisi</span>
            </button>
            <button
              type="button"
              @click="openImportModal(pkg)"
              class="p-2 text-slate-600 hover:text-emerald-700 bg-slate-100 hover:bg-emerald-50 rounded-xl transition"
              title="Import dari Excel"
            >
              <Upload class="w-4 h-4" />
            </button>
          </div>

          <!-- Secondary Toolbar: Cetak & Status Workflow -->
          <div class="flex items-center justify-between pt-1 border-t border-slate-50 gap-2">
            <!-- Print Dropdown (opens upwards with left-0 to prevent card clipping) -->
            <div class="relative group">
              <button
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition"
              >
                <Printer class="w-3.5 h-3.5 text-slate-600" />
                <span>Cetak</span>
                <ChevronDown class="w-3 h-3 text-slate-400 group-hover:rotate-180 transition duration-200" />
              </button>
              <div class="hidden group-hover:block absolute left-0 bottom-full mb-1.5 w-56 bg-white border border-slate-200 rounded-xl shadow-xl z-30 py-1.5 text-xs text-slate-700 divide-y divide-slate-100">
                <div class="py-1">
                  <a :href="`/prints/exam/${pkg.id}/naskah?layout=2col`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition">
                    <span>📄</span>
                    <span>Naskah 2 Kolom (Hemat)</span>
                  </a>
                  <a :href="`/prints/exam/${pkg.id}/naskah?layout=1col`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                    <span>📄</span>
                    <span>Naskah 1 Kolom</span>
                  </a>
                </div>
                <div class="py-1">
                  <a :href="`/prints/exam/${pkg.id}/kisi-kisi`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                    <span>📊</span>
                    <span>Kisi-Kisi Resmi</span>
                  </a>
                  <a :href="`/prints/exam/${pkg.id}/kartu-soal`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                    <span>🗂️</span>
                    <span>Kartu Soal</span>
                  </a>
                </div>
                <div class="py-1">
                  <a :href="`/prints/exam/${pkg.id}/telaah`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                    <span>📝</span>
                    <span>Berita Acara Telaah</span>
                  </a>
                  <a :href="`/prints/exam/${pkg.id}/kunci`" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                    <span>🔑</span>
                    <span>Kunci & Rubrik Nilai</span>
                  </a>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-1.5">
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

    <!-- FULLSCREEN QUESTION & KISI-KISI STUDIO WORKSPACE -->
    <div v-if="activePackage" class="fixed inset-0 z-50 bg-slate-900 flex flex-col overflow-hidden">
      <!-- 1. STUDIO TOP NAVBAR -->
      <header class="h-14 px-3 sm:px-6 bg-slate-900 text-white flex items-center justify-between shrink-0 border-b border-slate-800 z-20">
        <!-- Left: Back Button & Package Title Info -->
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
          <button
            type="button"
            @click="closeEditor"
            class="px-2.5 py-1.5 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700 transition flex items-center gap-1.5 shrink-0"
            title="Kembali ke Daftar Paket Soal"
          >
            <ArrowLeft class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Kembali</span>
          </button>
          <div class="h-5 w-px bg-slate-750 hidden sm:block"></div>
          <div class="p-1.5 bg-emerald-500/20 text-emerald-400 rounded-lg shrink-0 hidden sm:block">
            <BookOpen class="w-4 h-4" />
          </div>
          <div class="min-w-0">
            <h2 class="text-xs sm:text-sm font-bold truncate">{{ activePackage.title }}</h2>
            <p class="text-[10px] sm:text-[11px] text-slate-400 truncate">
              {{ activePackage.subject?.name }} &bull; Kelas {{ activePackage.class_room?.name }} &bull; {{ activePackage.academic_phase || 'Fase D' }}
            </p>
          </div>
        </div>

        <!-- Center: Dynamic Progress Bar Pill (Desktop) -->
        <div class="hidden xl:flex items-center gap-3 px-3.5 py-1.5 bg-slate-800/80 border border-slate-700 rounded-xl text-xs">
          <span class="text-slate-400 text-[11px]">Progres:</span>
          <div class="w-24 2xl:w-32 bg-slate-750 h-2 rounded-full overflow-hidden">
            <div
              class="bg-emerald-500 h-full rounded-full transition-all duration-300"
              :style="{ width: progressPercent + '%' }"
            ></div>
          </div>
          <span class="font-bold text-emerald-400 text-[11px]">
            {{ completedQuestionsCount }}/{{ activeQuestions.length }} Lengkap ({{ progressPercent }}%)
          </span>
        </div>

        <!-- Right: Actions (Preview, Toggle, Save, Close) -->
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
          <!-- Toggle Panel Kisi-Kisi on Desktop -->
          <button
            type="button"
            @click="showRightPane = !showRightPane"
            class="hidden lg:flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-lg border transition"
            :class="showRightPane ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-slate-800 text-slate-400 border-slate-700 hover:text-white'"
            title="Tampilkan / Sembunyikan Panel Kisi-Kisi"
          >
            <TableProperties class="w-3.5 h-3.5" />
            <span class="hidden xl:inline">Kisi-Kisi</span>
          </button>

          <!-- Quick Print Preview (Desktop) -->
          <div class="hidden md:flex items-center gap-1">
            <button
              type="button"
              @click="previewNaskah"
              class="px-2.5 py-1.5 text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition flex items-center gap-1"
              title="Preview Naskah Soal 2 Kolom"
            >
              <Eye class="w-3.5 h-3.5 text-slate-400" />
              <span class="hidden xl:inline">Naskah</span>
            </button>
            <button
              type="button"
              @click="previewKisiKisi"
              class="px-2.5 py-1.5 text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition flex items-center gap-1"
              title="Preview Format Kisi-Kisi Standar Kemenag"
            >
              <TableProperties class="w-3.5 h-3.5 text-slate-400" />
              <span class="hidden xl:inline">Kisi-Kisi</span>
            </button>
            <button
              type="button"
              @click="previewTelaah"
              class="px-2.5 py-1.5 text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition flex items-center gap-1"
              title="Preview Instrumen Telaah Soal HOTS"
            >
              <ClipboardCheck class="w-3.5 h-3.5 text-slate-400" />
              <span class="hidden xl:inline">Telaah</span>
            </button>
          </div>

          <!-- Auto-Fill All Kisi-Kisi Button -->
          <button
            type="button"
            @click="autoGenerateAllKisiKisi"
            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 text-xs font-bold rounded-lg shadow-sm transition"
            title="Otomatis rumuskan Kisi-Kisi (TP, Materi, Indikator, Level Kognitif) untuk semua nomor soal"
          >
            <Sparkles class="w-3.5 h-3.5 text-slate-950" />
            <span>Auto-Kisi</span>
          </button>

          <!-- Save All Button -->
          <button
            type="button"
            @click="saveAllQuestions"
            :disabled="savingQuestions"
            class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition disabled:opacity-50"
            title="Simpan Semua (Ctrl+S)"
          >
            <Save class="w-3.5 h-3.5" />
            <span>{{ savingQuestions ? 'Menyimpan...' : 'Simpan Semua' }}</span>
          </button>

          <!-- Close Icon -->
          <button
            type="button"
            @click="closeEditor"
            class="p-1.5 text-slate-400 hover:text-white rounded-lg transition"
            title="Tutup Studio"
          >
            <X class="w-5 h-5" />
          </button>
        </div>
      </header>

      <!-- 1.1 MOBILE SUBNAV / TAB BAR (< lg only) -->
      <div class="lg:hidden flex items-center bg-slate-900 border-b border-slate-800 px-3 py-1.5 gap-1.5 shrink-0 z-10">
        <button
          type="button"
          @click="mobileStudioTab = 'soal'"
          class="flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5"
          :class="mobileStudioTab === 'soal' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-800 text-slate-300 hover:bg-slate-750'"
        >
          <Edit3 class="w-3.5 h-3.5" />
          <span>1. Naskah</span>
        </button>
        <button
          type="button"
          @click="mobileStudioTab = 'kisi'"
          class="flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5"
          :class="mobileStudioTab === 'kisi' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-800 text-slate-300 hover:bg-slate-750'"
        >
          <TableProperties class="w-3.5 h-3.5" />
          <span>2. Kisi-Kisi</span>
        </button>
        <button
          type="button"
          @click="mobileStudioTab = 'nomor'"
          class="flex-1 py-1.5 px-2 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5"
          :class="mobileStudioTab === 'nomor' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-800 text-slate-300 hover:bg-slate-750'"
        >
          <span class="w-2 h-2 rounded-full" :class="isQuestionComplete(activeQuestions[currentQuestionIndex]) ? 'bg-emerald-400' : 'bg-amber-400'"></span>
          <span>3. No. {{ currentQuestionIndex + 1 }}</span>
        </button>
      </div>

      <!-- 2. STUDIO WORKSPACE BODY (3 Panes: Nav Sidebar, Center Question Form, Right Kisi-Kisi Studio) -->
      <div class="flex-1 flex overflow-hidden bg-slate-100">
        <!-- PANE 1: LEFT NAVIGATION (Nomor Soal) - Desktop only (hidden on < lg) -->
        <aside
          v-if="showLeftSidebar"
          class="hidden lg:flex w-56 xl:w-60 bg-white border-r border-slate-200 shrink-0 flex-col justify-between overflow-hidden shadow-2xs transition-all duration-200"
        >
          <!-- Sidebar Header -->
          <div class="p-3.5 border-b border-slate-100 space-y-2.5">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                Nomor Soal
              </span>
              <div class="flex items-center gap-1">
                <button
                  type="button"
                  @click="addNewQuestion"
                  class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[11px] font-bold rounded-md border border-emerald-200 flex items-center gap-1 transition"
                  title="Tambah 1 nomor soal di akhir"
                >
                  <Plus class="w-3 h-3" /> Tambah
                </button>
                <button
                  type="button"
                  @click="showLeftSidebar = false"
                  class="p-1 text-slate-400 hover:text-slate-600 rounded hover:bg-slate-100 transition"
                  title="Ciutkan Panel Nomor Soal"
                >
                  <ChevronLeft class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <!-- Quick Filter Tabs & Batch Button -->
            <div class="space-y-1.5">
              <div class="grid grid-cols-2 gap-1 p-0.5 bg-slate-100 rounded-lg text-[11px]">
                <button
                  type="button"
                  @click="questionFilter = 'all'"
                  class="py-1 font-semibold rounded-md transition text-center"
                  :class="questionFilter === 'all' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                >
                  Semua ({{ activeQuestions.length }})
                </button>
                <button
                  type="button"
                  @click="questionFilter = 'incomplete'"
                  class="py-1 font-semibold rounded-md transition text-center"
                  :class="questionFilter === 'incomplete' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-500 hover:text-amber-700'"
                >
                  Belum ({{ activeQuestions.length - completedQuestionsCount }})
                </button>
              </div>

              <button
                type="button"
                @click="openBatchTypeModal"
                class="w-full py-1 px-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[11px] font-bold rounded-lg border border-indigo-200 flex items-center justify-center gap-1.5 transition shadow-2xs cursor-pointer"
                title="Atur tipe soal untuk rentang nomor tertentu sekaligus (misal No. 31-35 Benar/Salah)"
              >
                <Zap class="w-3.5 h-3.5 text-indigo-600 shrink-0" />
                <span>Atur Rentang Tipe Soal</span>
              </button>
            </div>
          </div>

          <!-- Number Grid -->
          <div class="flex-1 overflow-y-auto p-3">
            <div class="grid grid-cols-4 gap-1.5">
              <button
                v-for="(q, idx) in activeQuestions"
                :key="q.question_number"
                type="button"
                v-show="questionFilter === 'all' || !isQuestionComplete(q)"
                @click="currentQuestionIndex = idx"
                class="h-11 rounded-lg text-xs font-bold border transition flex flex-col items-center justify-center relative select-none"
                :class="[
                  currentQuestionIndex === idx
                    ? 'bg-slate-900 text-white border-slate-900 ring-2 ring-indigo-500 shadow-xs'
                    : isQuestionComplete(q)
                      ? 'bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100'
                      : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'
                ]"
              >
                <span class="text-[12px] leading-tight">{{ q.question_number }}</span>
                <span
                  class="text-[8.5px] font-extrabold uppercase mt-0.5 tracking-tighter"
                  :class="[
                    currentQuestionIndex === idx
                      ? 'text-indigo-300'
                      : isQuestionComplete(q)
                        ? 'text-emerald-600'
                        : 'text-slate-400'
                  ]"
                >
                  {{ getShortTypeLabel(q.question_type) }}
                </span>
              </button>
            </div>
          </div>

          <!-- Sidebar Footer / Legend -->
          <div class="p-3 border-t border-slate-100 bg-slate-50/60 text-[11px] text-slate-500 space-y-1">
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
              <span>Hijau: Lengkap & Siap Cetak</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-slate-300 shrink-0"></span>
              <span>Putih: Belum Lengkap</span>
            </div>
          </div>
        </aside>

        <!-- Collapsed Mini Sidebar Button (Desktop only when collapsed) -->
        <div v-else class="hidden lg:flex flex-col items-center py-3 px-1.5 bg-white border-r border-slate-200 shrink-0">
          <button
            type="button"
            @click="showLeftSidebar = true"
            class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
            title="Buka Panel Nomor Soal"
          >
            <ChevronRight class="w-4 h-4" />
          </button>
          <span class="[writing-mode:vertical-lr] rotate-180 text-[11px] font-bold text-slate-400 tracking-wider mt-4">
            No. Soal
          </span>
        </div>

        <!-- MOBILE VIEW FOR TAB 3: DAFTAR NOMOR SOAL (lg:hidden) -->
        <div
          v-if="mobileStudioTab === 'nomor'"
          class="flex-1 lg:hidden flex flex-col justify-between overflow-hidden bg-white"
        >
          <div class="p-4 border-b border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                Daftar Nomor Soal ({{ activeQuestions.length }})
              </span>
              <button
                type="button"
                @click="addNewQuestion"
                class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 flex items-center gap-1 transition"
              >
                <Plus class="w-3.5 h-3.5" /> Tambah Soal
              </button>
            </div>

            <!-- Quick Filter Tabs & Batch Button -->
            <div class="space-y-2">
              <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-xl text-xs">
                <button
                  type="button"
                  @click="questionFilter = 'all'"
                  class="py-1.5 font-bold rounded-lg transition text-center"
                  :class="questionFilter === 'all' ? 'bg-white text-slate-800 shadow-2xs' : 'text-slate-500'"
                >
                  Semua ({{ activeQuestions.length }})
                </button>
                <button
                  type="button"
                  @click="questionFilter = 'incomplete'"
                  class="py-1.5 font-bold rounded-lg transition text-center"
                  :class="questionFilter === 'incomplete' ? 'bg-white text-amber-700 shadow-2xs' : 'text-slate-500'"
                >
                  Belum Lengkap ({{ activeQuestions.length - completedQuestionsCount }})
                </button>
              </div>

              <button
                type="button"
                @click="openBatchTypeModal"
                class="w-full py-2 px-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 flex items-center justify-center gap-1.5 transition"
              >
                <Zap class="w-4 h-4 text-indigo-600" />
                <span>Atur Rentang Tipe Soal (Batch)</span>
              </button>
            </div>
          </div>

          <!-- Number Grid Mobile (5 cols) -->
          <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-5 sm:grid-cols-6 gap-2">
              <button
                v-for="(q, idx) in activeQuestions"
                :key="q.question_number"
                type="button"
                v-show="questionFilter === 'all' || !isQuestionComplete(q)"
                @click="currentQuestionIndex = idx; mobileStudioTab = 'soal'"
                class="h-14 rounded-xl text-xs font-bold border transition flex flex-col items-center justify-center relative select-none"
                :class="[
                  currentQuestionIndex === idx
                    ? 'bg-slate-900 text-white border-slate-900 ring-2 ring-indigo-500 shadow-xs'
                    : isQuestionComplete(q)
                      ? 'bg-emerald-50 text-emerald-800 border-emerald-300 hover:bg-emerald-100'
                      : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'
                ]"
              >
                <span class="text-sm font-bold leading-tight">{{ q.question_number }}</span>
                <span
                  class="text-[9px] font-extrabold uppercase mt-0.5 tracking-tighter"
                  :class="[
                    currentQuestionIndex === idx
                      ? 'text-indigo-300'
                      : isQuestionComplete(q)
                        ? 'text-emerald-600'
                        : 'text-slate-400'
                  ]"
                >
                  {{ getShortTypeLabel(q.question_type) }}
                </span>
              </button>
            </div>
          </div>

          <!-- Legend -->
          <div class="p-3.5 border-t border-slate-100 bg-slate-50 text-xs text-slate-500 flex items-center justify-around">
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
              <span>Hijau: Lengkap</span>
            </div>
            <div class="flex items-center gap-1.5">
              <span class="w-2.5 h-2.5 rounded-full bg-slate-300 shrink-0"></span>
              <span>Putih: Belum Lengkap</span>
            </div>
          </div>
        </div>

        <!-- PANE 2: CENTER (Naskah Soal & Pilihan Jawaban AKM) -->
        <main
          v-if="activeQuestions[currentQuestionIndex]"
          class="flex-1 overflow-y-auto p-3.5 sm:p-6 space-y-4"
          :class="{ 'hidden lg:block': mobileStudioTab !== 'soal' }"
        >
          <!-- Card 1: Question Meta Header -->
          <div class="bg-white p-3.5 sm:p-4 rounded-xl border border-slate-200 shadow-2xs flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
              <span class="px-2.5 py-1 bg-slate-900 text-white font-bold text-xs rounded-lg shadow-2xs shrink-0">
                Soal No. {{ activeQuestions[currentQuestionIndex].question_number }}
              </span>
              <select
                v-model="activeQuestions[currentQuestionIndex].question_type"
                @change="handleTypeChange(activeQuestions[currentQuestionIndex])"
                class="form-select py-1 px-2.5 text-xs font-bold text-slate-800 bg-slate-50 border-slate-300 rounded-lg max-w-[240px] sm:max-w-none"
              >
                <option value="pg">Pilihan Ganda (PG Tunggal)</option>
                <option value="pg_complex">Pilihan Ganda Kompleks (Multi Kunci)</option>
                <option value="true_false">Benar / Salah (True / False)</option>
                <option value="matching">Menjodohkan (Matching)</option>
                <option value="short_answer">Isian Singkat (Short Answer)</option>
                <option value="essay">Uraian / Essay</option>
              </select>

              <button
                type="button"
                @click="openBatchTypeModal"
                class="px-2.5 py-1 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 flex items-center gap-1 transition shrink-0 cursor-pointer"
                title="Atur tipe soal untuk rentang nomor tertentu sekaligus (misal No. 31-35 Benar/Salah)"
              >
                <Zap class="w-3.5 h-3.5 text-indigo-600" />
                <span class="hidden sm:inline">Atur Rentang Tipe</span>
              </button>
            </div>

            <div class="flex items-center gap-2.5 ml-auto sm:ml-0">
              <div class="flex items-center gap-1.5">
                <label class="text-xs text-slate-500 font-medium">Bobot:</label>
                <input
                  type="number"
                  v-model.number="activeQuestions[currentQuestionIndex].score_weight"
                  step="0.5"
                  min="0.5"
                  class="form-input w-16 sm:w-20 py-1 text-xs text-center font-bold bg-slate-50 border-slate-300 rounded-lg"
                />
              </div>

              <button
                type="button"
                @click="removeQuestion(currentQuestionIndex)"
                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                title="Hapus butir soal ini"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Card 2: Question Narration & Stimulus -->
          <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-2xs space-y-3">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Teks Soal / Narasi Stimulus *
              </label>
              <!-- Upload Image Trigger -->
              <div>
                <input
                  type="file"
                  ref="stimulusFileInput"
                  accept="image/*"
                  class="hidden"
                  @change="handleQuestionImageUpload($event, activeQuestions[currentQuestionIndex])"
                />
                <button
                  type="button"
                  @click="triggerStimulusUpload"
                  :disabled="uploadingImage"
                  class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition disabled:opacity-50"
                  title="Klik untuk memilih file gambar stimulus"
                >
                  <Image class="w-3.5 h-3.5" />
                  <span class="text-[11px] sm:text-xs">
                    {{ uploadingImage ? 'Mengunggah...' : (activeQuestions[currentQuestionIndex].question_image ? 'Ganti Gambar' : '+ Unggah Gambar') }}
                  </span>
                </button>
              </div>
            </div>

            <QuestionRichEditor
              v-model="activeQuestions[currentQuestionIndex].question_text"
              placeholder="Tuliskan rumusan butir soal secara jelas, lugas, dan tegas di sini..."
            />

            <!-- Image Preview -->
            <div v-if="activeQuestions[currentQuestionIndex].question_image" class="relative inline-block border border-slate-200 rounded-lg p-1 bg-slate-50">
              <img :src="getStorageUrl(activeQuestions[currentQuestionIndex].question_image)" class="max-h-36 sm:max-h-48 rounded object-contain" alt="Stimulus" />
              <button
                type="button"
                @click="activeQuestions[currentQuestionIndex].question_image = null"
                class="absolute -top-2 -right-2 p-1 bg-rose-600 text-white rounded-full shadow hover:bg-rose-700 transition"
                title="Hapus gambar"
              >
                <X class="w-3 h-3" />
              </button>
            </div>
          </div>

          <!-- Card 3: Pilihan Jawaban & Kunci Berdasarkan Tipe AKM -->
          <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-2xs space-y-3">
            <!-- 1. PG TUNGGAL -->
            <div v-if="activeQuestions[currentQuestionIndex].question_type === 'pg'" class="space-y-2">
              <div class="text-xs font-bold text-slate-700 flex flex-wrap items-center justify-between gap-1 pb-1 border-b border-slate-100">
                <span class="flex items-center gap-1.5">
                  <span>PILIHAN JAWABAN ({{ getOptionRangeLabel(activeQuestions[currentQuestionIndex]) }}):</span>
                  <span class="text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full font-medium hidden sm:inline">💡 Bisa copy sekaligus dari Word, nomor otomatis bersih!</span>
                </span>
                <div class="flex items-center gap-2">
                  <button
                    v-if="canAddOption(activeQuestions[currentQuestionIndex])"
                    type="button"
                    @click="addOption(activeQuestions[currentQuestionIndex])"
                    class="px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold flex items-center gap-1 transition"
                    title="Tambah Opsi Pilihan Jawaban"
                  >
                    <Plus class="w-3 h-3" /> Tambah Opsi
                  </button>
                  <span class="text-[11px] text-emerald-600 font-semibold hidden md:inline">Klik lingkaran huruf untuk memilih 1 Kunci</span>
                </div>
              </div>

              <div
                v-for="opt in getOptionKeys(activeQuestions[currentQuestionIndex])"
                :key="opt"
                class="flex items-center gap-2 p-2 rounded-xl border transition group"
                :class="activeQuestions[currentQuestionIndex].correct_answer === opt ? 'bg-emerald-50/70 border-emerald-300' : 'bg-slate-50 border-slate-200'"
              >
                <button
                  type="button"
                  @click="activeQuestions[currentQuestionIndex].correct_answer = opt"
                  class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 transition"
                  :class="activeQuestions[currentQuestionIndex].correct_answer === opt ? 'bg-emerald-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-300 hover:border-emerald-500'"
                  :title="'Pilih ' + opt + ' sebagai Kunci Jawaban'"
                >
                  {{ opt }}
                </button>
                <input
                  type="text"
                  v-model="ensureOptions(activeQuestions[currentQuestionIndex])[opt]"
                  @paste="handleOptionPaste($event, activeQuestions[currentQuestionIndex], opt)"
                  @blur="ensureOptions(activeQuestions[currentQuestionIndex])[opt] = cleanOptionPrefix(ensureOptions(activeQuestions[currentQuestionIndex])[opt])"
                  :placeholder="'Tulis isi pilihan ' + opt + '...'"
                  class="form-input flex-1 py-1.5 text-xs bg-white min-w-0"
                  :title="'Ketik pilihan ' + opt + ' atau paste (bisa paste opsi sekaligus dari Word)'"
                />
                <span v-if="activeQuestions[currentQuestionIndex].correct_answer === opt" class="text-[11px] font-bold text-emerald-700 px-1.5 py-0.5 bg-emerald-100 rounded shrink-0">
                  ✓ KUNCI
                </span>
                <button
                  v-if="canRemoveOption(activeQuestions[currentQuestionIndex], opt)"
                  type="button"
                  @click="removeOption(activeQuestions[currentQuestionIndex], opt)"
                  class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition shrink-0"
                  :title="'Hapus Opsi ' + opt"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <!-- 2. PG KOMPLEKS (Multi Kunci) -->
            <div v-else-if="activeQuestions[currentQuestionIndex].question_type === 'pg_complex'" class="space-y-2">
              <div class="text-xs font-bold text-slate-700 flex flex-wrap items-center justify-between gap-1 pb-1 border-b border-slate-100">
                <span class="flex items-center gap-1.5">
                  <span class="text-indigo-900 font-bold">PILIHAN GANDA KOMPLEKS ({{ getOptionRangeLabel(activeQuestions[currentQuestionIndex]) }}):</span>
                  <span class="text-[10px] text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded-full font-medium hidden sm:inline">💡 Bisa copy sekaligus dari Word, nomor otomatis bersih!</span>
                </span>
                <div class="flex items-center gap-2">
                  <button
                    v-if="canAddOption(activeQuestions[currentQuestionIndex])"
                    type="button"
                    @click="addOption(activeQuestions[currentQuestionIndex])"
                    class="px-2 py-0.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold flex items-center gap-1 transition"
                    title="Tambah Opsi Pilihan Ganda Kompleks"
                  >
                    <Plus class="w-3 h-3" /> Tambah Opsi
                  </button>
                  <span class="text-[11px] text-indigo-600 font-semibold hidden md:inline">Klik kotak huruf untuk memilih beberapa kunci</span>
                </div>
              </div>

              <div
                v-for="opt in getOptionKeys(activeQuestions[currentQuestionIndex])"
                :key="opt"
                class="flex items-center gap-2 p-2 rounded-xl border transition group"
                :class="isComplexKeySelected(activeQuestions[currentQuestionIndex], opt) ? 'bg-indigo-50/70 border-indigo-300' : 'bg-slate-50 border-slate-200'"
              >
                <button
                  type="button"
                  @click="toggleComplexKey(activeQuestions[currentQuestionIndex], opt)"
                  class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 transition"
                  :class="isComplexKeySelected(activeQuestions[currentQuestionIndex], opt) ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-300 hover:border-indigo-500'"
                >
                  {{ opt }}
                </button>
                <input
                  type="text"
                  v-model="ensureOptions(activeQuestions[currentQuestionIndex])[opt]"
                  @paste="handleOptionPaste($event, activeQuestions[currentQuestionIndex], opt)"
                  @blur="ensureOptions(activeQuestions[currentQuestionIndex])[opt] = cleanOptionPrefix(ensureOptions(activeQuestions[currentQuestionIndex])[opt])"
                  :placeholder="'Tulis opsi / pernyataan ' + opt + '...'"
                  class="form-input flex-1 py-1.5 text-xs bg-white min-w-0"
                  :title="'Ketik opsi ' + opt + ' atau paste (bisa paste opsi sekaligus dari Word)'"
                />
                <span v-if="isComplexKeySelected(activeQuestions[currentQuestionIndex], opt)" class="text-[11px] font-bold text-indigo-700 px-1.5 py-0.5 bg-indigo-100 rounded shrink-0">
                  ✓ KUNCI
                </span>
                <button
                  v-if="canRemoveOption(activeQuestions[currentQuestionIndex], opt)"
                  type="button"
                  @click="removeOption(activeQuestions[currentQuestionIndex], opt)"
                  class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition shrink-0"
                  :title="'Hapus Opsi ' + opt"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
              <div class="text-xs text-slate-400 italic pt-1">
                Kunci Terpilih: <strong class="text-indigo-600">{{ activeQuestions[currentQuestionIndex].correct_answer || '(Belum ada kunci dipilih)' }}</strong>
              </div>
            </div>

            <!-- 3. BENAR / SALAH (Dual Mode: Mandiri Per-Nomor vs Matriks AKM) -->
            <div v-else-if="activeQuestions[currentQuestionIndex].question_type === 'true_false'" class="space-y-3">
              <!-- Switcher Header -->
              <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-purple-900 uppercase tracking-wide">Kunci Benar / Salah:</span>
                  <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                    :class="isTfSingleMode(activeQuestions[currentQuestionIndex]) ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600'"
                  >
                    {{ isTfSingleMode(activeQuestions[currentQuestionIndex]) ? 'Mode Per-Nomor (Tunggal)' : 'Mode Tabel Matriks (AKM)' }}
                  </span>
                </div>
                <!-- Dual Mode Toggle -->
                <div class="inline-flex items-center bg-slate-100 p-0.5 rounded-lg border border-slate-200 text-xs">
                  <button
                    type="button"
                    @click="setTfMode(activeQuestions[currentQuestionIndex], 'single')"
                    class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1.5"
                    :class="isTfSingleMode(activeQuestions[currentQuestionIndex]) ? 'bg-white text-purple-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                  >
                    <span>1 Soal 1 Kunci (Standar)</span>
                  </button>
                  <button
                    type="button"
                    @click="setTfMode(activeQuestions[currentQuestionIndex], 'matrix')"
                    class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1.5"
                    :class="!isTfSingleMode(activeQuestions[currentQuestionIndex]) ? 'bg-white text-purple-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                  >
                    <span>Tabel Matriks (1 Soal Banyak Pernyataan)</span>
                  </button>
                </div>
              </div>

              <!-- MODE A: SINGLE (1 Soal = 1 Kunci, misal No. 31 s/d 35) -->
              <div v-if="isTfSingleMode(activeQuestions[currentQuestionIndex])" class="space-y-3">
                <p class="text-xs text-slate-500">
                  Tuliskan bunyi pernyataan pada kotak <strong>Teks Soal / Narasi Stimulus</strong> di atas, lalu tentukan apakah pernyataan tersebut bernilai <strong>Benar (B)</strong> atau <strong>Salah (S)</strong>:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-lg">
                  <!-- Tombol BENAR -->
                  <button
                    type="button"
                    @click="setSingleTfKey(activeQuestions[currentQuestionIndex], 'B')"
                    class="p-3.5 rounded-xl border-2 text-left transition flex items-center gap-3 cursor-pointer"
                    :class="activeQuestions[currentQuestionIndex].correct_answer === 'B'
                      ? 'border-emerald-500 bg-emerald-50/80 text-emerald-900 shadow-sm ring-2 ring-emerald-400/30'
                      : 'border-slate-200 bg-white hover:border-emerald-300 hover:bg-slate-50 text-slate-700'"
                  >
                    <div
                      class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm shrink-0 transition"
                      :class="activeQuestions[currentQuestionIndex].correct_answer === 'B' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500 border border-slate-300'"
                    >
                      B
                    </div>
                    <div class="min-w-0">
                      <div class="font-bold text-sm">BENAR (B)</div>
                      <div class="text-[11px] text-slate-500">Pernyataan bernilai tepat / valid</div>
                    </div>
                  </button>

                  <!-- Tombol SALAH -->
                  <button
                    type="button"
                    @click="setSingleTfKey(activeQuestions[currentQuestionIndex], 'S')"
                    class="p-3.5 rounded-xl border-2 text-left transition flex items-center gap-3 cursor-pointer"
                    :class="activeQuestions[currentQuestionIndex].correct_answer === 'S'
                      ? 'border-rose-500 bg-rose-50/80 text-rose-900 shadow-sm ring-2 ring-rose-400/30'
                      : 'border-slate-200 bg-white hover:border-rose-300 hover:bg-slate-50 text-slate-700'"
                  >
                    <div
                      class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm shrink-0 transition"
                      :class="activeQuestions[currentQuestionIndex].correct_answer === 'S' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-500 border border-slate-300'"
                    >
                      S
                    </div>
                    <div class="min-w-0">
                      <div class="font-bold text-sm">SALAH (S)</div>
                      <div class="text-[11px] text-slate-500">Pernyataan bernilai keliru / tidak valid</div>
                    </div>
                  </button>
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-500 pt-1">
                  <span>Kunci Terpilih Soal No. {{ activeQuestions[currentQuestionIndex].question_number }}:</span>
                  <strong
                    class="px-2 py-0.5 rounded font-bold"
                    :class="activeQuestions[currentQuestionIndex].correct_answer === 'B' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                  >
                    {{ activeQuestions[currentQuestionIndex].correct_answer === 'B' ? '✓ BENAR (B)' : (activeQuestions[currentQuestionIndex].correct_answer === 'S' ? '✗ SALAH (S)' : 'Belum dipilih') }}
                  </strong>
                  <span class="text-slate-400">| Bobot: {{ activeQuestions[currentQuestionIndex].score_weight || 1 }} Poin</span>
                </div>
              </div>

              <!-- MODE B: MATRIX (1 Soal = Banyak Baris Pernyataan AKM) -->
              <div v-else class="space-y-3">
                <div class="flex items-center justify-between pb-1">
                  <span class="text-xs font-semibold text-slate-600">Daftar Baris Pernyataan dalam Soal No. {{ activeQuestions[currentQuestionIndex].question_number }}:</span>
                  <button
                    type="button"
                    @click="addTfStatement(activeQuestions[currentQuestionIndex])"
                    class="px-2.5 py-1 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold rounded-lg border border-purple-200 flex items-center gap-1 transition"
                  >
                    <Plus class="w-3.5 h-3.5" /> Tambah Pernyataan
                  </button>
                </div>

                <div class="space-y-2">
                  <div
                    v-for="(st, sIdx) in ensureTrueFalse(activeQuestions[currentQuestionIndex])"
                    :key="sIdx"
                    class="flex flex-wrap sm:flex-nowrap items-center gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl"
                  >
                    <span class="w-5 text-center font-bold text-xs text-slate-500 shrink-0">{{ sIdx + 1 }}.</span>
                    <input
                      type="text"
                      v-model="st.text"
                      @paste="handleGenericTextPaste($event, st, 'text')"
                      @blur="st.text = cleanOptionPrefix(st.text)"
                      :placeholder="'Tulis pernyataan ' + (sIdx + 1) + '...'"
                      class="form-input flex-1 py-1.5 text-xs bg-white min-w-[180px]"
                    />
                    <!-- Toggle Benar / Salah -->
                    <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shrink-0 ml-auto sm:ml-0">
                      <button
                        type="button"
                        @click="st.answer = 'B'; syncTfAnswer(activeQuestions[currentQuestionIndex])"
                        class="px-2.5 py-1 text-xs font-bold rounded-md transition"
                        :class="st.answer === 'B' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                      >
                        Benar
                      </button>
                      <button
                        type="button"
                        @click="st.answer = 'S'; syncTfAnswer(activeQuestions[currentQuestionIndex])"
                        class="px-2.5 py-1 text-xs font-bold rounded-md transition"
                        :class="st.answer === 'S' ? 'bg-rose-600 text-white' : 'text-slate-600 hover:bg-slate-100'"
                      >
                        Salah
                      </button>
                    </div>
                    <button
                      type="button"
                      @click="removeTfStatement(activeQuestions[currentQuestionIndex], sIdx)"
                      class="p-1.5 text-slate-400 hover:text-rose-600 shrink-0"
                      title="Hapus baris pernyataan"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
                <div class="text-xs text-slate-400 italic">
                  Kunci Matriks: <strong class="text-purple-600">{{ activeQuestions[currentQuestionIndex].correct_answer }}</strong>
                </div>
              </div>
            </div>

            <!-- 4. MENJODOHKAN (Dual Mode: Mandiri Per-Nomor vs Matriks AKM) -->
            <div v-else-if="activeQuestions[currentQuestionIndex].question_type === 'matching'" class="space-y-3">
              <!-- Switcher Header -->
              <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-teal-900 uppercase tracking-wide">Pasangan Menjodohkan:</span>
                  <span
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                    :class="isMatchingSingleMode(activeQuestions[currentQuestionIndex]) ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-600'"
                  >
                    {{ isMatchingSingleMode(activeQuestions[currentQuestionIndex]) ? 'Mode Per-Nomor (Tunggal)' : 'Mode Tabel Matriks (AKM)' }}
                  </span>
                </div>
                <!-- Dual Mode Toggle -->
                <div class="inline-flex items-center bg-slate-100 p-0.5 rounded-lg border border-slate-200 text-xs">
                  <button
                    type="button"
                    @click="setMatchingMode(activeQuestions[currentQuestionIndex], 'single')"
                    class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1.5"
                    :class="isMatchingSingleMode(activeQuestions[currentQuestionIndex]) ? 'bg-white text-teal-800 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                  >
                    <span>1 Soal 1 Pasangan (Standar No. {{ activeQuestions[currentQuestionIndex].question_number }})</span>
                  </button>
                  <button
                    type="button"
                    @click="setMatchingMode(activeQuestions[currentQuestionIndex], 'matrix')"
                    class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1.5"
                    :class="!isMatchingSingleMode(activeQuestions[currentQuestionIndex]) ? 'bg-white text-teal-800 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                  >
                    <span>Tabel Matriks (1 Soal Banyak Pasangan)</span>
                  </button>
                </div>
              </div>

              <!-- MODE A: SINGLE (1 Soal = 1 Pasangan Menjodohkan, misal No. 36 s/d 40) -->
              <div v-if="isMatchingSingleMode(activeQuestions[currentQuestionIndex])" class="space-y-3">
                <div class="p-3 bg-teal-50/60 border border-teal-200 rounded-xl space-y-2">
                  <div class="text-xs text-teal-900 font-semibold flex items-center gap-1.5">
                    <span>💡 <strong>Cara Pengisian Soal No. {{ activeQuestions[currentQuestionIndex].question_number }}:</strong></span>
                  </div>
                  <ul class="text-[11px] text-teal-800 list-disc list-inside space-y-0.5">
                    <li><strong>Kolom Kiri (Premis):</strong> Tuliskan istilah atau pernyataan pada kotak <strong>Teks Soal / Narasi Stimulus</strong> di atas (misal: <em>Mouse (Tetikus)</em>).</li>
                    <li><strong>Kolom Kanan (Pasangan Jawaban):</strong> Masukkan teks pilihan jawaban pasangannya dan tentukan huruf kuncinya di bawah ini:</li>
                  </ul>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                  <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                      Teks Jawaban Pasangan (Kolom Kanan) *
                    </label>
                    <input
                      type="text"
                      v-model="ensureSingleMatching(activeQuestions[currentQuestionIndex]).right_text"
                      @paste="handleGenericTextPaste($event, ensureSingleMatching(activeQuestions[currentQuestionIndex]), 'right_text')"
                      @blur="ensureSingleMatching(activeQuestions[currentQuestionIndex]).right_text = cleanOptionPrefix(ensureSingleMatching(activeQuestions[currentQuestionIndex]).right_text)"
                      placeholder="Contoh: Perangkat penunjuk untuk menggerakkan kursor di layar..."
                      class="form-input text-xs bg-white w-full font-medium"
                    />
                  </div>

                  <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                      Pilih Huruf Kunci Pasangan (Kunci Benar di Kolom Kanan):
                    </label>
                    <div class="flex flex-wrap items-center gap-1.5">
                      <button
                        v-for="letter in ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']"
                        :key="letter"
                        type="button"
                        @click="setSingleMatchingKey(activeQuestions[currentQuestionIndex], letter)"
                        class="w-9 h-8 rounded-lg font-bold text-xs transition border flex items-center justify-center cursor-pointer"
                        :class="activeQuestions[currentQuestionIndex].correct_answer === letter
                          ? 'bg-teal-600 text-white border-teal-600 shadow-sm ring-2 ring-teal-400/30'
                          : 'bg-white text-slate-700 border-slate-300 hover:border-teal-400 hover:bg-teal-50/50'"
                      >
                        {{ letter }}
                      </button>
                    </div>
                  </div>

                  <div class="pt-2 border-t border-slate-200/70 flex flex-wrap items-center justify-between text-xs text-slate-500">
                    <div>
                      Kunci Terpilih Soal No. {{ activeQuestions[currentQuestionIndex].question_number }}:
                      <strong class="text-teal-700 text-sm font-bold ml-1">
                        → Pilihan {{ activeQuestions[currentQuestionIndex].correct_answer || 'A' }}
                      </strong>
                    </div>
                    <div class="text-[11px] text-slate-400 italic">
                      * Saat dicetak, nomor 36-40 otomatis digabung menjadi satu tabel penjodohan rapi.
                    </div>
                  </div>
                </div>
              </div>

              <!-- MODE B: MATRIX (1 Soal = Banyak Pasangan AKM) -->
              <div v-else class="space-y-3">
                <div class="flex items-center justify-between pb-1">
                  <span class="text-xs font-semibold text-slate-600">Daftar Pasangan dalam Butir Soal No. {{ activeQuestions[currentQuestionIndex].question_number }}:</span>
                  <button
                    type="button"
                    @click="addMatchingPair(activeQuestions[currentQuestionIndex])"
                    class="px-2.5 py-1 bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-bold rounded-lg border border-teal-200 flex items-center gap-1 transition"
                  >
                    <Plus class="w-3.5 h-3.5" /> Tambah Pasangan
                  </button>
                </div>

                <div class="space-y-2">
                  <div
                    v-for="(pair, pIdx) in ensureMatching(activeQuestions[currentQuestionIndex])"
                    :key="pIdx"
                    class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center p-2.5 bg-slate-50 border border-slate-200 rounded-xl"
                  >
                    <div class="sm:col-span-5">
                      <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Kolom Kiri (Premis {{ pIdx + 1 }})</label>
                      <input
                        type="text"
                        v-model="pair.left"
                        @paste="handleGenericTextPaste($event, pair, 'left')"
                        @blur="pair.left = cleanOptionPrefix(pair.left)"
                        :placeholder="'Pernyataan ' + (pIdx + 1)"
                        class="form-input py-1.5 text-xs bg-white w-full"
                      />
                    </div>
                    <div class="sm:col-span-5">
                      <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Kolom Kanan (Pilihan {{ String.fromCharCode(65 + pIdx) }})</label>
                      <input
                        type="text"
                        v-model="pair.right"
                        @paste="handleGenericTextPaste($event, pair, 'right')"
                        @blur="pair.right = cleanOptionPrefix(pair.right)"
                        :placeholder="'Jawaban ' + String.fromCharCode(65 + pIdx)"
                        class="form-input py-1.5 text-xs bg-white w-full"
                      />
                    </div>
                    <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-1.5 pt-1 sm:pt-3">
                      <select
                        v-model="pair.key"
                        @change="syncMatchingAnswer(activeQuestions[currentQuestionIndex])"
                        class="form-select py-1 text-xs font-bold w-20 sm:w-16 bg-white"
                        title="Kunci Pasangan"
                      >
                        <option
                          v-for="(_, oIdx) in ensureMatching(activeQuestions[currentQuestionIndex])"
                          :key="oIdx"
                          :value="String.fromCharCode(65 + oIdx)"
                        >
                          → {{ String.fromCharCode(65 + oIdx) }}
                        </option>
                      </select>
                      <button
                        type="button"
                        @click="removeMatchingPair(activeQuestions[currentQuestionIndex], pIdx)"
                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
                <div class="text-xs text-slate-400 italic">
                  Kunci Pasangan: <strong class="text-teal-600">{{ activeQuestions[currentQuestionIndex].correct_answer }}</strong>
                </div>
              </div>
            </div>

            <!-- 5. ISIAN SINGKAT -->
            <div v-else-if="activeQuestions[currentQuestionIndex].question_type === 'short_answer'" class="space-y-2">
              <label class="block text-xs font-bold text-amber-900">Kunci Jawaban Singkat / Pasti *</label>
              <input
                type="text"
                v-model="activeQuestions[currentQuestionIndex].correct_answer"
                placeholder="Contoh: 14 atau Fotosintesis atau Raden Patah"
                class="form-input text-xs font-bold text-slate-800"
              />
              <p class="text-[11px] text-slate-400">Tuliskan kata kunci jawaban eksak yang diharapkan diisi siswa.</p>
            </div>

            <!-- 6. URAIAN / ESSAY -->
            <div v-else class="space-y-2">
              <label class="block text-xs font-bold text-blue-900">Kunci Jawaban / Pedoman Penskoran Uraian</label>
              <textarea
                v-model="activeQuestions[currentQuestionIndex].correct_answer"
                rows="3"
                placeholder="Tuliskan kata kunci jawaban yang diharapkan dan kriteria penskoran..."
                class="form-textarea text-xs w-full"
              ></textarea>
            </div>
          </div>
        </main>

        <!-- PANE 3: RIGHT (Studio Pemetaan Kisi-Kisi Butir Soal) -->
        <aside
          v-if="activeQuestions[currentQuestionIndex] && showRightPane"
          class="w-full lg:w-80 xl:w-96 2xl:w-[420px] bg-white border-l border-slate-200 shrink-0 overflow-y-auto p-4 sm:p-5 space-y-4 shadow-2xs transition-all duration-200"
          :class="{ 'hidden lg:block': mobileStudioTab !== 'kisi' }"
        >
          <!-- Kisi-Kisi Header -->
          <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
            <div class="flex items-center gap-2 min-w-0">
              <div class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg shrink-0">
                <TableProperties class="w-4 h-4" />
              </div>
              <div class="min-w-0">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider truncate">Pemetaan Kisi-Kisi Butir Soal</h4>
                <p class="text-[11px] text-slate-400 truncate">Terintegrasi langsung ke Format Cetak Resmi</p>
              </div>
            </div>

            <!-- Single Question Auto-Generate Button -->
            <button
              type="button"
              @click="autoGenerateSingleKisiKisi(currentQuestionIndex)"
              class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-[11px] font-bold rounded-lg shadow-2xs transition"
              title="Otomatis rumuskan TP, Materi, Indikator, dan Level Kognitif dari teks soal ini"
            >
              <Sparkles class="w-3.5 h-3.5 text-amber-300" />
              <span>Auto-Fill</span>
            </button>
          </div>

          <!-- Quick Inherit from previous question -->
          <div
            v-if="currentQuestionIndex > 0 && (!activeQuestions[currentQuestionIndex].material || !activeQuestions[currentQuestionIndex].basic_competency)"
            class="flex items-center justify-between p-2 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-800"
          >
            <span class="truncate">Samakan materi dengan Soal No. {{ activeQuestions[currentQuestionIndex - 1].question_number }}?</span>
            <button
              type="button"
              @click="copyKisiFromPrev(currentQuestionIndex)"
              class="shrink-0 font-bold text-amber-700 hover:text-amber-900 underline ml-2 cursor-pointer"
            >
              Salin TP & Materi
            </button>
          </div>

          <!-- Form Fields Kisi-Kisi (Urutan Sinkron dengan Kolom Tabel Cetak Kisi-Kisi) -->
          <div class="space-y-3 text-xs">
            <!-- 1. Tujuan Pembelajaran (TP) / CP (Kolom 2 Cetak) -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tujuan Pembelajaran (TP) / CP *</label>
              <input
                type="text"
                v-model="activeQuestions[currentQuestionIndex].basic_competency"
                placeholder="Contoh: Peserta didik mampu memahami antarmuka berbasis grafis (GUI)"
                class="form-input text-xs"
              />
            </div>

            <!-- 2. Materi Pokok (Kolom 3 Cetak) -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Materi Pokok / Topik *</label>
              <input
                type="text"
                v-model="activeQuestions[currentQuestionIndex].material"
                placeholder="Contoh: Antarmuka Pengguna (GUI), Operasi Aljabar, dll."
                class="form-input text-xs"
              />
            </div>

            <!-- 3. Indikator Soal (Kolom 4 Cetak) -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Indikator Soal *</label>
              <textarea
                v-model="activeQuestions[currentQuestionIndex].indicator"
                rows="3"
                placeholder="Contoh: Disajikan deskripsi tentang antarmuka berbasis ikon/grafis, peserta didik dapat menentukan kepanjangan dari GUI."
                class="form-textarea text-xs"
              ></textarea>
            </div>

            <!-- 4. Level Kognitif (Kolom 5 Cetak) -->
            <div>
              <label class="block font-bold text-slate-700 mb-1">Level Kognitif (Standar BSNP/Kemenag) *</label>
              <select
                v-model="activeQuestions[currentQuestionIndex].cognitive_level"
                @change="onCognitiveLevelChange(currentQuestionIndex)"
                class="form-select text-xs font-medium"
              >
                <option v-for="l in options.cognitive_levels" :key="l.value" :value="l.value">{{ l.label }}</option>
              </select>
            </div>

            <!-- 5. Tingkat Kesukaran (Untuk Kartu Soal & Analisis Butir) -->
            <div>
              <label class="block font-bold text-slate-700 mb-1.5">Estimasi Tingkat Kesukaran</label>
              <div class="grid grid-cols-3 gap-1.5">
                <button
                  type="button"
                  v-for="diff in [{ val: 'mudah', lbl: 'Mudah' }, { val: 'sedang', lbl: 'Sedang' }, { val: 'sukar', lbl: 'Sukar' }]"
                  :key="diff.val"
                  @click="onDifficultyChange(currentQuestionIndex, diff.val)"
                  class="py-1.5 text-xs font-bold rounded-lg border transition text-center"
                  :class="activeQuestions[currentQuestionIndex].difficulty_level === diff.val ? 'bg-indigo-600 text-white border-indigo-600 shadow-2xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
                >
                  {{ diff.lbl }}
                </button>
              </div>
            </div>
          </div>

          <!-- Quality Guide Card (Checklist Mandiri Guru) -->
          <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5 text-[11px] text-slate-600">
            <div class="font-bold text-slate-700 flex items-center gap-1">
              <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" /> Kaidah Penulisan Butir
            </div>
            <p class="text-slate-500 leading-relaxed">
              Pastikan rumusan soal tidak menimbulkan penafsiran ganda, pokok soal dirumuskan dengan tegas, dan pilihan jawaban homogen.
            </p>
          </div>
        </aside>
      </div>

      <!-- 3. STUDIO STICKY FOOTER NAVIGATION -->
      <footer class="h-14 px-3 sm:px-6 bg-white border-t border-slate-200 flex items-center justify-between shrink-0 shadow-md z-20">
        <!-- Previous Question -->
        <button
          type="button"
          @click="prevQuestion"
          :disabled="currentQuestionIndex === 0"
          class="px-2.5 sm:px-4 py-2 border border-slate-200 text-slate-700 disabled:opacity-30 rounded-xl text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-1 sm:gap-1.5"
        >
          <ChevronLeft class="w-4 h-4" />
          <span class="hidden sm:inline">Soal Sebelumnya</span>
          <span class="sm:hidden">Prev</span>
        </button>

        <!-- Current Question Info -->
        <!-- Desktop: Text Counter -->
        <div class="hidden sm:block text-xs font-medium text-slate-500">
          Nomor <strong class="text-slate-900">{{ currentQuestionIndex + 1 }}</strong> dari {{ activeQuestions.length }} Butir Soal
        </div>
        <!-- Mobile: Clickable Indicator that switches to Tab 3 (Nomor Soal) -->
        <button
          type="button"
          @click="mobileStudioTab = 'nomor'"
          class="sm:hidden px-3 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg text-xs font-bold text-slate-800 flex items-center gap-1 transition"
        >
          <span>No. {{ currentQuestionIndex + 1 }}/{{ activeQuestions.length }}</span>
          <ChevronDown class="w-3.5 h-3.5 text-slate-500" />
        </button>

        <!-- Next Question -->
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="nextQuestion"
            :disabled="currentQuestionIndex === activeQuestions.length - 1"
            class="px-2.5 sm:px-4 py-2 bg-slate-900 text-white disabled:opacity-30 rounded-xl text-xs font-semibold hover:bg-slate-800 transition flex items-center gap-1 sm:gap-1.5 shadow-xs"
          >
            <span class="hidden sm:inline">Soal Berikutnya</span>
            <span class="sm:hidden">Next</span>
            <ChevronRight class="w-4 h-4" />
          </button>
        </div>
      </footer>

      <!-- Modal Batch Set Tipe Soal (Rentang Nomor) -->
      <div
        v-if="showBatchModal"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
      >
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 overflow-hidden animate-in fade-in zoom-in-95 duration-150 my-8">
          <!-- Header -->
          <div class="px-6 py-4 text-white flex items-center justify-between" style="background: linear-gradient(135deg, #3730a3, #4338ca);">
            <div class="flex items-center gap-2.5">
              <div class="p-1.5 bg-white/10 rounded-lg">
                <Zap class="w-5 h-5 text-amber-300" />
              </div>
              <div>
                <h3 class="text-sm font-bold">Atur Rentang Tipe Soal (Batch)</h3>
                <p class="text-[11px] text-indigo-200">Ubah tipe & bobot untuk beberapa nomor soal sekaligus</p>
              </div>
            </div>
            <button
              type="button"
              @click="showBatchModal = false"
              class="p-1 rounded-lg hover:bg-white/10 text-white/80 hover:text-white transition cursor-pointer"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Body -->
          <form @submit.prevent="applyBatchType" class="p-6 space-y-4">
            <!-- Preset Cepat -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Preset Populer 1-Klik:</label>
              <div class="flex flex-wrap gap-1.5">
                <button
                  type="button"
                  @click="applyBatchPreset(31, 35, 'true_false', 1.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 31 && batchForm.toNumber === 35 && batchForm.targetType === 'true_false'
                    ? 'bg-purple-100 text-purple-800 border-purple-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200'"
                >
                  <span>🎯 No. 31–35: Benar / Salah</span>
                </button>

                <button
                  type="button"
                  @click="applyBatchPreset(36, 40, 'matching', 1.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 36 && batchForm.toNumber === 40 && batchForm.targetType === 'matching'
                    ? 'bg-teal-100 text-teal-800 border-teal-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200'"
                >
                  <span>🔗 No. 36–40: Menjodohkan</span>
                </button>

                <button
                  type="button"
                  @click="applyBatchPreset(41, 45, 'essay', 2.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 41 && batchForm.toNumber === 45 && batchForm.targetType === 'essay'
                    ? 'bg-blue-100 text-blue-800 border-blue-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200'"
                >
                  <span>📝 No. 41–45: Uraian / Essay</span>
                </button>

                <button
                  type="button"
                  @click="applyBatchPreset(1, 30, 'pg', 1.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 1 && batchForm.toNumber === 30 && batchForm.targetType === 'pg'
                    ? 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200'"
                >
                  <span>🔘 No. 1–30: PG Tunggal</span>
                </button>

                <button
                  type="button"
                  @click="applyBatchPreset(21, 30, 'pg_complex', 1.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 21 && batchForm.toNumber === 30 && batchForm.targetType === 'pg_complex'
                    ? 'bg-indigo-100 text-indigo-800 border-indigo-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200'"
                >
                  <span>🔲 No. 21–30: PG Kompleks</span>
                </button>
              </div>
            </div>

            <!-- Input Rentang Nomor -->
            <div class="grid grid-cols-2 gap-3 pt-1">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Dari Nomor Soal *</label>
                <input
                  type="number"
                  v-model.number="batchForm.fromNumber"
                  min="1"
                  :max="activeQuestions.length"
                  required
                  class="form-input font-bold text-center text-sm"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Nomor Soal *</label>
                <input
                  type="number"
                  v-model.number="batchForm.toNumber"
                  :min="batchForm.fromNumber"
                  :max="activeQuestions.length"
                  required
                  class="form-input font-bold text-center text-sm"
                />
              </div>
            </div>

            <!-- Tipe Soal Target -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Ubah Menjadi Tipe Soal *</label>
              <select v-model="batchForm.targetType" class="form-select font-bold text-xs" required>
                <option value="true_false">Benar / Salah (Mode Mandiri 1 Kunci - Standar No. 31-35)</option>
                <option value="matching">Menjodohkan (Mode Mandiri 1 Pasangan - Standar No. 36-40)</option>
                <option value="pg">Pilihan Ganda (PG Tunggal Opsi A-D)</option>
                <option value="pg_complex">Pilihan Ganda Kompleks (Multi Kunci)</option>
                <option value="short_answer">Isian Singkat (Short Answer)</option>
                <option value="essay">Uraian / Essay</option>
              </select>
            </div>

            <!-- Bobot Nilai -->
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Bobot Nilai per Butir Soal *</label>
              <div class="flex items-center gap-2">
                <input
                  type="number"
                  v-model.number="batchForm.scoreWeight"
                  step="0.5"
                  min="0.5"
                  max="100"
                  required
                  class="form-input w-28 font-bold text-center text-xs"
                />
                <span class="text-xs text-slate-500 font-medium">Poin per nomor (Total bobot rentang: <strong>{{ Math.max(0, (batchForm.toNumber - batchForm.fromNumber + 1)) * (batchForm.scoreWeight || 0) }} Poin</strong>)</span>
              </div>
            </div>

            <!-- Preview Ringkasan -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1 text-slate-600">
              <div class="font-bold text-slate-800 flex items-center gap-1.5">
                <span>📋 Ringkasan Perubahan:</span>
              </div>
              <p>
                Akan mengubah total <strong class="text-indigo-700">{{ Math.max(0, (batchForm.toNumber - batchForm.fromNumber + 1)) }} butir soal</strong> (Nomor <strong>{{ batchForm.fromNumber }}</strong> s/d <strong>{{ batchForm.toNumber }}</strong>) menjadi tipe <strong class="text-slate-900">{{ getQuestionTypeLabel(batchForm.targetType) }}</strong> dengan bobot <strong class="text-emerald-700">{{ batchForm.scoreWeight }} poin</strong>.
              </p>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
              <button
                type="button"
                @click="showBatchModal = false"
                class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-50 transition cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm flex items-center gap-1.5 transition cursor-pointer"
              >
                <Zap class="w-3.5 h-3.5 text-amber-300" />
                <span>Terapkan ke Nomor {{ batchForm.fromNumber }} s/d {{ batchForm.toNumber }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import api, { getImageUrl } from '../api';
import QuestionRichEditor from '../components/QuestionRichEditor.vue';
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
  TableProperties,
  ArrowLeft,
  CheckCircle2,
  Eye,
  ChevronLeft,
  ChevronRight,
  Zap,
  ClipboardCheck,
  Sparkles
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

// Fullscreen Studio Workspace
const activePackage = ref(null);
const activeQuestions = ref([]);
const currentQuestionIndex = ref(0);
const savingQuestions = ref(false);
const mobileStudioTab = ref('soal');
const questionFilter = ref('all');
const showLeftSidebar = ref(true);
const showRightPane = ref(true);

const completedQuestionsCount = computed(() => {
  return activeQuestions.value.filter(q => q.correct_answer && q.indicator && q.question_text).length;
});

const progressPercent = computed(() => {
  if (!activeQuestions.value.length) return 0;
  return Math.round((completedQuestionsCount.value / activeQuestions.value.length) * 100);
});

const isQuestionComplete = (q) => {
  if (!q.correct_answer || !q.indicator) return false;
  const clean = q.question_text ? q.question_text.replace(/<[^>]*>/g, '').trim() : '';
  const hasMediaOrTable = q.question_text ? (q.question_text.includes('<img') || q.question_text.includes('<table')) : false;
  return clean.length > 0 || hasMediaOrTable;
};

const getShortTypeLabel = (type) => {
  switch (type) {
    case 'pg': return 'PG';
    case 'pg_complex': return 'PGK';
    case 'true_false': return 'B/S';
    case 'matching': return 'JOD';
    case 'short_answer': return 'ISI';
    case 'essay': return 'ESS';
    default: return 'PG';
  }
};

// Batch Type Setter State & Methods
const showBatchModal = ref(false);
const batchForm = reactive({
  fromNumber: 31,
  toNumber: 35,
  targetType: 'true_false',
  scoreWeight: 1.0,
});

const openBatchTypeModal = () => {
  const currNum = activeQuestions.value[currentQuestionIndex.value]?.question_number || 1;
  if (currNum >= 41) {
    batchForm.fromNumber = 41;
    batchForm.toNumber = Math.min(45, activeQuestions.value.length);
    batchForm.targetType = 'essay';
    batchForm.scoreWeight = 2.0;
  } else if (currNum >= 36) {
    batchForm.fromNumber = 36;
    batchForm.toNumber = Math.min(40, activeQuestions.value.length);
    batchForm.targetType = 'matching';
    batchForm.scoreWeight = 1.0;
  } else if (currNum >= 31) {
    batchForm.fromNumber = 31;
    batchForm.toNumber = Math.min(35, activeQuestions.value.length);
    batchForm.targetType = 'true_false';
    batchForm.scoreWeight = 1.0;
  } else if (currNum >= 21) {
    batchForm.fromNumber = 21;
    batchForm.toNumber = Math.min(30, activeQuestions.value.length);
    batchForm.targetType = 'pg_complex';
    batchForm.scoreWeight = 1.0;
  } else {
    batchForm.fromNumber = 1;
    batchForm.toNumber = Math.min(30, activeQuestions.value.length);
    batchForm.targetType = 'pg';
    batchForm.scoreWeight = 1.0;
  }
  showBatchModal.value = true;
};

const applyBatchPreset = (from, to, type, weight = 1.0) => {
  batchForm.fromNumber = from;
  batchForm.toNumber = Math.min(to, activeQuestions.value.length);
  batchForm.targetType = type;
  batchForm.scoreWeight = weight;
};

const getQuestionTypeLabel = (type) => {
  switch (type) {
    case 'pg': return 'Pilihan Ganda (PG Tunggal)';
    case 'pg_complex': return 'Pilihan Ganda Kompleks (PGK)';
    case 'true_false': return 'Benar / Salah (Mode Mandiri)';
    case 'matching': return 'Menjodohkan (Mode Mandiri)';
    case 'short_answer': return 'Isian Singkat';
    case 'essay': return 'Uraian / Essay';
    default: return type;
  }
};

const applyBatchType = () => {
  const from = Math.max(1, parseInt(batchForm.fromNumber) || 1);
  const to = Math.min(activeQuestions.value.length, parseInt(batchForm.toNumber) || from);

  if (from > to) {
    alert('Nomor awal tidak boleh lebih besar dari nomor akhir!');
    return;
  }

  let count = 0;
  activeQuestions.value.forEach(q => {
    if (q.question_number >= from && q.question_number <= to) {
      q.question_type = batchForm.targetType;
      if (batchForm.scoreWeight !== undefined && batchForm.scoreWeight !== null) {
        q.score_weight = Number(batchForm.scoreWeight) || 1.0;
      }

      if (q.question_type === 'true_false') {
        q.options = { mode: 'single', answer: q.correct_answer || 'B' };
        if (!q.correct_answer || (q.correct_answer !== 'B' && q.correct_answer !== 'S')) {
          q.correct_answer = 'B';
        }
      } else if (q.question_type === 'matching') {
        const single = ensureSingleMatching(q);
        q.options = {
          mode: 'single',
          right_text: single.right_text || '',
          key: single.key || 'A'
        };
        if (!q.correct_answer) {
          q.correct_answer = q.options.key || 'A';
        }
      } else if (q.question_type === 'pg') {
        ensureOptions(q);
      } else if (q.question_type === 'pg_complex') {
        ensureOptions(q);
      }
      count++;
    }
  });

  // Switch to first affected question
  const targetIdx = activeQuestions.value.findIndex(q => q.question_number === from);
  if (targetIdx !== -1) {
    currentQuestionIndex.value = targetIdx;
  }

  showBatchModal.value = false;
  alert(`Berhasil! ${count} butir soal (Nomor ${from} s/d ${to}) telah diubah menjadi tipe "${getQuestionTypeLabel(batchForm.targetType)}" dengan bobot ${batchForm.scoreWeight} poin.`);
};

const removeQuestion = (idx) => {
  if (activeQuestions.value.length <= 1) {
    alert('Paket soal harus memiliki minimal 1 butir soal.');
    return;
  }
  const qNum = activeQuestions.value[idx].question_number;
  if (!confirm(`Hapus butir soal nomor ${qNum}? Nomor soal setelahnya akan otomatis diurutkan ulang.`)) return;
  activeQuestions.value.splice(idx, 1);
  activeQuestions.value.forEach((q, i) => {
    q.question_number = i + 1;
  });
  if (currentQuestionIndex.value >= activeQuestions.value.length) {
    currentQuestionIndex.value = activeQuestions.value.length - 1;
  }
};

const previewNaskah = () => {
  if (!activePackage.value) return;
  window.open(`/prints/exam/${activePackage.value.id}/naskah?layout=2col`, '_blank');
};

const previewKisiKisi = () => {
  if (!activePackage.value) return;
  window.open(`/prints/exam/${activePackage.value.id}/kisi-kisi`, '_blank');
};

const previewTelaah = () => {
  if (!activePackage.value) return;
  window.open(`/prints/exam/${activePackage.value.id}/telaah`, '_blank');
};

// Smart Auto-Generate Kisi-Kisi Engine
const analyzeQuestionContent = (q, prevQ = null) => {
  const cleanHtml = (q.question_text || '').replace(/<[^>]*>/g, ' ').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();
  const lowerText = cleanHtml.toLowerCase();

  // 1. Determine Topic / Material
  let topic = q.material || '';
  if (!topic) {
    if (lowerText.match(/gui|grafis|antarmuka grafis|ikon|icon|simbol visual/)) {
      topic = 'Antarmuka Pengguna (GUI)';
    } else if (lowerText.match(/cli|command line|baris perintah/)) {
      topic = 'Interaksi Pengguna (CLI)';
    } else if (lowerText.match(/vui|voice|suara|siri|alexa|google assistant|bixby/)) {
      topic = 'Voice User Interface (VUI)';
    } else if (lowerText.match(/remote|kendali jarak jauh|alat pengendali/)) {
      topic = 'Alat Pengendali Perangkat';
    } else if (lowerText.match(/kalkulator|calculator|\b2\s*\+\s*7/)) {
      topic = 'Aplikasi Perhitungan Sederhana';
    } else if (lowerText.match(/windows|jendela/)) {
      topic = 'Manajemen Jendela Aplikasi';
    } else if (lowerText.match(/file explorer|folder|direktori|map dokumen|pengelolaan file/)) {
      topic = 'Pengelolaan File dan Folder';
    } else if (lowerText.match(/hard disk|flash drive|flashdisk|kartu memori|media simpan/)) {
      topic = 'Media Penyimpanan Elektronik';
    } else if (lowerText.match(/ekstensi|\.jpg|\.png|\.doc|\.docx|format file/)) {
      topic = 'Format dan Ekstensi File';
    } else if (lowerText.match(/finder|macos|macintosh/)) {
      topic = 'Pengelolaan File di Berbagai OS';
    } else if (lowerText.match(/ctrl-c|ctrl-v|shortcut|tombol pintas|salin|tempel/)) {
      topic = 'Tombol Pintas (Shortcut)';
    } else if (lowerText.match(/peramban|browser|chrome|firefox|safari|edge/)) {
      topic = 'Peramban Web (Web Browser)';
    } else if (lowerText.match(/bookmark|penanda halaman|favorit/)) {
      topic = 'Fitur Penanda (Bookmark)';
    } else if (lowerText.match(/mesin pencari|search engine|google|bing|yahoo/)) {
      topic = 'Mesin Pencari (Search Engine)';
    } else if (lowerText.match(/kata kunci|keyword|operator and|operator logika/)) {
      topic = 'Teknik Pencarian Internet';
    } else if (lowerText.match(/surel|email|gmail|surat elektronik|inbox|spam/)) {
      topic = 'Surel (Electronic Mail)';
    } else if (lowerText.match(/keyboard|papan tombol|function keys|f1|f12|qwerty/)) {
      topic = 'Perangkat Keras (Keyboard)';
    } else if (lowerText.match(/ram|rom|random access memory|volatile|mainboard/)) {
      topic = 'Memori Komputer (RAM dan ROM)';
    } else if (lowerText.match(/cloud storage|penyimpanan awan/)) {
      topic = 'Penyimpanan Awan (Cloud Storage)';
    } else if (lowerText.match(/mouse|tetikus|cpu|central processing|monitor|printer|input devices|output devices|hardware/)) {
      topic = 'Perangkat Keras Komputer';
    } else if (lowerText.match(/software|perangkat lunak/)) {
      topic = 'Perangkat Lunak Komputer';
    } else if (prevQ && prevQ.material) {
      topic = prevQ.material;
    } else {
      const subjectName = activePackage.value?.subject?.name || 'Informatika';
      topic = 'Materi Pokok ' + subjectName;
    }
  }

  // 2. Determine Indikator Soal
  let indicator = q.indicator || '';
  if (!indicator) {
    const qType = q.question_type || 'pg';
    if (qType === 'pg') {
      if (lowerText.match(/kepanjangan|singkatan|arti/)) {
        indicator = `Disajikan deskripsi tentang ${topic}, peserta didik dapat menentukan kepanjangan atau definisinya secara tepat.`;
      } else if (lowerText.match(/dinamakan|disebut|istilah|pengertian/)) {
        indicator = `Disajikan pengertian atau simbol visual pada ${topic}, peserta didik dapat mengidentifikasi istilah yang tepat.`;
      } else if (lowerText.match(/mengapa|alasan|keunggulan|keuntungan/)) {
        indicator = `Disajikan permasalahan terkait ${topic}, peserta didik dapat menjelaskan karakteristik/keunggulannya dengan tepat.`;
      } else if (lowerText.match(/langkah|cara|prosedur|menu|perintah/)) {
        indicator = `Disajikan skenario aktivitas pada ${topic}, peserta didik dapat menentukan langkah menu yang digunakan.`;
      } else if (lowerText.match(/hitung|hasil|kalkulasi/)) {
        indicator = `Disajikan studi kasus operasi perhitungan pada ${topic}, peserta didik dapat menentukan hasil kalkulasi yang tepat.`;
      } else if (lowerText.match(/contoh|macam|daftar/)) {
        indicator = `Disajikan daftar pilihan, peserta didik dapat menentukan contoh media atau perangkat terkait ${topic} dengan benar.`;
      } else {
        indicator = `Disajikan pertanyaan mengenai ${topic}, peserta didik dapat menentukan jawaban yang tepat.`;
      }
    } else if (qType === 'pg_complex') {
      indicator = `Disajikan ragam pernyataan terkait ${topic}, peserta didik dapat memilih beberapa pilihan/pernyataan yang benar.`;
    } else if (qType === 'true_false') {
      indicator = `Disajikan pernyataan mengenai ${topic}, peserta didik dapat memvalidasi kebenaran pernyataan tersebut secara tepat.`;
    } else if (qType === 'matching') {
      indicator = `Disajikan komponen atau istilah ${topic}, peserta didik dapat menjodohkan dengan fungsi atau pasangannya yang sesuai.`;
    } else if (qType === 'short_answer') {
      indicator = `Disajikan pertanyaan ringkas terkait ${topic}, peserta didik dapat menuliskan jawaban yang tepat.`;
    } else if (qType === 'essay') {
      indicator = `Disajikan pertanyaan konseptual terkait ${topic}, peserta didik dapat menjelaskan konsep dan penerapannya secara jelas dan terperinci.`;
    }
  }

  // 3. Determine Tujuan Pembelajaran (TP) / CP
  let tp = q.basic_competency || '';
  if (!tp) {
    if (prevQ && prevQ.basic_competency && prevQ.material === topic) {
      tp = prevQ.basic_competency;
    } else {
      tp = `Peserta didik mampu memahami dan menganalisis konsep ${topic} beserta penerapannya.`;
    }
  }

  // 4. Determine Level Kognitif
  let cognitive = q.cognitive_level || '';
  if (!cognitive) {
    const qType = q.question_type || 'pg';
    if (qType === 'pg_complex' || lowerText.match(/mengapa|analisis|faktor|kendala|perbedaan nasib|keunggulan dibanding/)) {
      cognitive = 'L3';
    } else if (qType === 'matching' || qType === 'true_false' || lowerText.match(/langkah|cara|prosedur|shortcut|fungsi|bagaimana|hitung|membedakan/)) {
      cognitive = 'L2';
    } else {
      cognitive = 'L1';
    }
  }

  // 5. Determine Tingkat Kesukaran
  let diff = q.difficulty_level || '';
  if (!diff) {
    if (cognitive === 'L3') diff = 'sukar';
    else if (cognitive === 'L2') diff = 'sedang';
    else diff = 'mudah';
  }

  return {
    material: topic,
    indicator: indicator,
    basic_competency: tp,
    cognitive_level: cognitive,
    difficulty_level: diff,
  };
};

const autoGenerateSingleKisiKisi = (index) => {
  const q = activeQuestions.value[index];
  if (!q) return;
  const prevQ = index > 0 ? activeQuestions.value[index - 1] : null;
  const res = analyzeQuestionContent(q, prevQ);

  q.material = res.material;
  q.indicator = res.indicator;
  q.basic_competency = res.basic_competency;
  q.cognitive_level = res.cognitive_level;
  q.difficulty_level = res.difficulty_level;
};

const autoGenerateAllKisiKisi = () => {
  if (!activeQuestions.value || activeQuestions.value.length === 0) return;

  const countEmpty = activeQuestions.value.filter(q => !q.indicator || !q.material || !q.basic_competency).length;
  const confirmMsg = countEmpty > 0
    ? `Generate kisi-kisi otomatis untuk ${countEmpty} butir soal yang kisi-kisinya belum lengkap?`
    : `Semua butir soal sudah memiliki kisi-kisi. Apakah Anda ingin meng-generate ulang kisi-kisi untuk seluruh ${activeQuestions.value.length} butir soal?`;

  if (!confirm(confirmMsg)) return;

  let generated = 0;
  for (let i = 0; i < activeQuestions.value.length; i++) {
    const q = activeQuestions.value[i];
    const prevQ = i > 0 ? activeQuestions.value[i - 1] : null;
    const res = analyzeQuestionContent(q, prevQ);

    if (!q.material || countEmpty === 0) q.material = res.material;
    if (!q.indicator || countEmpty === 0) q.indicator = res.indicator;
    if (!q.basic_competency || countEmpty === 0) q.basic_competency = res.basic_competency;
    if (!q.cognitive_level || countEmpty === 0) q.cognitive_level = res.cognitive_level;
    if (!q.difficulty_level || countEmpty === 0) q.difficulty_level = res.difficulty_level;
    generated++;
  }

  alert(`Alhamdulillah! Kisi-kisi untuk ${generated} butir soal berhasil diisi otomatis. Jangan lupa klik tombol "Simpan Semua".`);
};

const copyKisiFromPrev = (index) => {
  if (index <= 0) return;
  const curQ = activeQuestions.value[index];
  const prevQ = activeQuestions.value[index - 1];
  if (!curQ || !prevQ) return;

  curQ.basic_competency = prevQ.basic_competency || curQ.basic_competency;
  curQ.material = prevQ.material || curQ.material;
  if (!curQ.cognitive_level && prevQ.cognitive_level) curQ.cognitive_level = prevQ.cognitive_level;
  if (!curQ.difficulty_level && prevQ.difficulty_level) curQ.difficulty_level = prevQ.difficulty_level;
};

const onCognitiveLevelChange = (idx) => {
  const q = activeQuestions.value[idx];
  if (!q) return;
  if (q.cognitive_level === 'L3') q.difficulty_level = 'sukar';
  else if (q.cognitive_level === 'L2') q.difficulty_level = 'sedang';
  else if (q.cognitive_level === 'L1') q.difficulty_level = 'mudah';
};

const onDifficultyChange = (idx, val) => {
  const q = activeQuestions.value[idx];
  if (!q) return;
  q.difficulty_level = val;
  if (val === 'sukar' && q.cognitive_level !== 'L3') q.cognitive_level = 'L3';
  else if (val === 'sedang' && q.cognitive_level !== 'L2') q.cognitive_level = 'L2';
  else if (val === 'mudah' && q.cognitive_level !== 'L1') q.cognitive_level = 'L1';
};

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

const ALL_OPTION_LETTERS = ['A', 'B', 'C', 'D', 'E'];

const ensureOptions = (q) => {
  if (!q.options || typeof q.options !== 'object' || Array.isArray(q.options)) {
    q.options = { A: '', B: '', C: '', D: '' };
  } else {
    // If q has legacy empty E and E is not in correct_answer, clean it up so it defaults to A-D for MTs
    if (q.options.E !== undefined && q.options.E === '' && (!q.correct_answer || !q.correct_answer.includes('E'))) {
      delete q.options.E;
    }
  }
  return q.options;
};

const getOptionKeys = (q) => {
  const opts = ensureOptions(q);
  const keys = Object.keys(opts)
    .filter(k => ALL_OPTION_LETTERS.includes(k))
    .sort();
  if (keys.length === 0) {
    ['A', 'B', 'C', 'D'].forEach(k => { opts[k] = ''; });
    return ['A', 'B', 'C', 'D'];
  }
  return keys;
};

const getOptionRangeLabel = (q) => {
  const keys = getOptionKeys(q);
  if (keys.length === 0) return 'A - D';
  return `${keys[0]} - ${keys[keys.length - 1]}`;
};

const canAddOption = (q) => {
  const keys = getOptionKeys(q);
  return keys.length < ALL_OPTION_LETTERS.length;
};

const canRemoveOption = (q, opt) => {
  const keys = getOptionKeys(q);
  // Keep minimum 2 options (A, B) and only allow removing the last option in sequence (e.g. remove E, or remove D)
  return keys.length > 2 && opt === keys[keys.length - 1];
};

const addOption = (q) => {
  const opts = ensureOptions(q);
  const keys = getOptionKeys(q);
  const nextLetter = ALL_OPTION_LETTERS.find(l => !keys.includes(l));
  if (nextLetter) {
    opts[nextLetter] = '';
  }
};

const removeOption = (q, opt) => {
  const opts = ensureOptions(q);
  const keys = getOptionKeys(q);
  if (keys.length <= 2) {
    alert('Pilihan jawaban minimal harus memiliki 2 opsi!');
    return;
  }
  
  delete opts[opt];
  
  // Clean up correct_answer if this option was selected
  if (q.question_type === 'pg' && q.correct_answer === opt) {
    q.correct_answer = '';
  } else if (q.question_type === 'pg_complex' && q.correct_answer) {
    let currentKeys = q.correct_answer.split(',').map(k => k.trim()).filter(k => k !== opt && k !== '');
    q.correct_answer = currentKeys.join(',');
  }
};

// --- Word Paste & Option Prefix Sanitizers ---
const cleanOptionPrefix = (str) => {
  if (!str || typeof str !== 'string') return '';
  // Strips leading option prefixes: "a. ", "a.", "b. ", "B) ", "(b) ", "b: ", "b - ", "1. ", etc.
  return str.replace(/^(\(?[a-eA-E0-9]\)?[\.\:\-\)]\s*)/, '').trim();
};

const handleOptionPaste = (e, q, currentOpt) => {
  const clipboardData = e.clipboardData || window.clipboardData;
  if (!clipboardData) return;
  const text = clipboardData.getData('text/plain') || '';
  if (!text) return;

  const lines = text.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
  const opts = ensureOptions(q);

  if (lines.length > 1) {
    // Multi-line paste from Word: automatically distribute across options (A, B, C, D, E)
    e.preventDefault();
    const letters = ['A', 'B', 'C', 'D', 'E'];
    const startIndex = letters.indexOf(currentOpt);
    if (startIndex !== -1) {
      lines.forEach((line, idx) => {
        const targetLetter = letters[startIndex + idx];
        if (targetLetter) {
          opts[targetLetter] = cleanOptionPrefix(line);
        }
      });
    }
  } else {
    // Single line paste: if it has an option prefix, strip it automatically!
    if (/^(\(?[a-eA-E0-9]\)?[\.\:\-\)]\s*)/.test(text.trim())) {
      e.preventDefault();
      opts[currentOpt] = cleanOptionPrefix(text.trim());
    }
  }
};

const handleGenericTextPaste = (e, obj, field) => {
  const clipboardData = e.clipboardData || window.clipboardData;
  if (!clipboardData) return;
  const text = clipboardData.getData('text/plain') || '';
  if (!text) return;

  if (/^(\(?[a-eA-E0-9]\)?[\.\:\-\)]\s*)/.test(text.trim())) {
    e.preventDefault();
    obj[field] = cleanOptionPrefix(text.trim());
  }
};

const sanitizeAllQuestionsBeforeSave = () => {
  if (!Array.isArray(activeQuestions.value)) return;
  activeQuestions.value.forEach(q => {
    // 1. Sanitize question_text
    if (q.question_text && typeof q.question_text === 'string') {
      q.question_text = q.question_text
        .replace(/text-indent\s*:\s*-[^;"]+;?/gi, 'text-indent: 0;')
        .replace(/margin-left\s*:\s*-[^;"]+;?/gi, 'margin-left: 0;');
    }
    // 2. Sanitize options for pg & pg_complex
    if (q.options && typeof q.options === 'object' && !Array.isArray(q.options) && ['pg', 'pg_complex'].includes(q.question_type)) {
      ['A', 'B', 'C', 'D', 'E'].forEach(letter => {
        if (q.options[letter]) {
          q.options[letter] = cleanOptionPrefix(q.options[letter]);
        }
      });
    }
    // 3. Sanitize true_false
    if (q.question_type === 'true_false') {
      if (isTfSingleMode(q)) {
        if (!q.correct_answer || (q.correct_answer !== 'B' && q.correct_answer !== 'S')) {
          q.correct_answer = 'B';
        }
        q.options = { mode: 'single', answer: q.correct_answer };
      } else if (q.options?.statements && Array.isArray(q.options.statements)) {
        q.options.mode = 'matrix';
        q.options.statements.forEach(st => {
          if (st.text) st.text = cleanOptionPrefix(st.text);
        });
        syncTfAnswer(q);
      }
    }
    // 4. Sanitize matching
    if (q.question_type === 'matching') {
      if (isMatchingSingleMode(q)) {
        const single = ensureSingleMatching(q);
        if (single.right_text) single.right_text = cleanOptionPrefix(single.right_text);
        q.correct_answer = single.key || 'A';
        q.options = {
          mode: 'single',
          right_text: single.right_text,
          key: single.key || 'A'
        };
      } else if (q.options?.pairs && Array.isArray(q.options.pairs)) {
        q.options.mode = 'matrix';
        q.options.pairs.forEach(pair => {
          if (pair.left) pair.left = cleanOptionPrefix(pair.left);
          if (pair.right) pair.right = cleanOptionPrefix(pair.right);
        });
        syncMatchingAnswer(q);
      }
    }
  });
};

const handleTypeChange = (q) => {
  if (q.question_type === 'true_false') {
    if (!q.correct_answer || (q.correct_answer !== 'B' && q.correct_answer !== 'S')) {
      q.correct_answer = 'B';
    }
    if (!q.options || typeof q.options !== 'object' || Array.isArray(q.options)) {
      q.options = { mode: 'single', answer: 'B' };
    }
  } else if (q.question_type === 'matching') {
    if (!q.correct_answer) {
      q.correct_answer = 'A';
    }
    ensureSingleMatching(q);
  } else if (q.question_type === 'pg') {
    ensureOptions(q);
  }
};

const isTfSingleMode = (q) => {
  if (!q) return true;
  if (q.options?.mode === 'matrix') return false;
  if (q.options?.mode === 'single') return true;
  if (Array.isArray(q.options?.statements) && q.options.statements.length > 1) return false;
  return true;
};

const setTfMode = (q, mode) => {
  if (!q.options || typeof q.options !== 'object' || Array.isArray(q.options)) {
    q.options = {};
  }
  q.options.mode = mode;
  if (mode === 'single') {
    if (q.correct_answer !== 'B' && q.correct_answer !== 'S') {
      q.correct_answer = 'B';
    }
    q.options.answer = q.correct_answer;
  } else {
    if (!Array.isArray(q.options.statements) || q.options.statements.length === 0) {
      q.options.statements = [
        { text: '', answer: 'B' },
        { text: '', answer: 'S' },
        { text: '', answer: 'B' }
      ];
    }
    syncTfAnswer(q);
  }
};

const setSingleTfKey = (q, ans) => {
  q.correct_answer = ans;
  if (!q.options || typeof q.options !== 'object' || Array.isArray(q.options)) {
    q.options = {};
  }
  q.options.mode = 'single';
  q.options.answer = ans;
};

const isMatchingSingleMode = (q) => {
  if (!q) return true;
  if (q.options?.mode === 'matrix') return false;
  if (q.options?.mode === 'single') return true;
  if (Array.isArray(q.options?.pairs) && q.options.pairs.length > 1) return false;
  return true;
};

const ensureSingleMatching = (q) => {
  if (!q.options || typeof q.options !== 'object' || Array.isArray(q.options)) {
    q.options = { mode: 'single', right_text: '', key: q.correct_answer || 'A' };
  }
  if (q.options.mode !== 'single') {
    q.options.mode = 'single';
  }
  if (q.options.right_text === undefined) {
    const firstPair = Array.isArray(q.options.pairs) && q.options.pairs.length > 0 ? q.options.pairs[0] : null;
    q.options.right_text = firstPair?.right || '';
    q.options.key = firstPair?.key || q.correct_answer || 'A';
    if ((!q.question_text || q.question_text.trim() === '') && firstPair?.left) {
      q.question_text = firstPair.left;
    }
  }
  if (!q.options.key) {
    q.options.key = q.correct_answer || 'A';
  }
  if (!q.correct_answer) {
    q.correct_answer = q.options.key || 'A';
  }
  return q.options;
};

const setMatchingMode = (q, mode) => {
  if (!q.options || typeof q.options !== 'object' || Array.isArray(q.options)) {
    q.options = {};
  }
  q.options.mode = mode;
  if (mode === 'single') {
    ensureSingleMatching(q);
  } else {
    if (!Array.isArray(q.options.pairs) || q.options.pairs.length === 0) {
      q.options.pairs = [
        { left: '', right: q.options.right_text || '', key: q.options.key || 'A' },
        { left: '', right: '', key: 'B' }
      ];
    }
    syncMatchingAnswer(q);
  }
};

const setSingleMatchingKey = (q, letter) => {
  const opts = ensureSingleMatching(q);
  opts.key = letter;
  q.correct_answer = letter;
};

// --- AKM Diversity Helpers ---
const toggleComplexKey = (q, opt) => {
  let keys = q.correct_answer ? q.correct_answer.split(',').map(k => k.trim()).filter(Boolean) : [];
  if (keys.includes(opt)) {
    keys = keys.filter(k => k !== opt);
  } else {
    keys.push(opt);
  }
  keys.sort();
  q.correct_answer = keys.join(',');
};

const isComplexKeySelected = (q, opt) => {
  if (!q.correct_answer) return false;
  const keys = q.correct_answer.split(',').map(k => k.trim());
  return keys.includes(opt);
};

const ensureTrueFalse = (q) => {
  if (!q.options || typeof q.options !== 'object' || !Array.isArray(q.options.statements)) {
    q.options = {
      statements: [
        { text: '', answer: 'B' },
        { text: '', answer: 'S' },
        { text: '', answer: 'B' }
      ]
    };
  }
  syncTfAnswer(q);
  return q.options.statements;
};

const syncTfAnswer = (q) => {
  if (q.options?.statements) {
    q.correct_answer = q.options.statements.map((s, idx) => `${idx + 1}:${s.answer}`).join(', ');
  }
};

const addTfStatement = (q) => {
  if (!q.options?.statements) ensureTrueFalse(q);
  q.options.statements.push({ text: '', answer: 'B' });
  syncTfAnswer(q);
};

const removeTfStatement = (q, idx) => {
  if (q.options?.statements && q.options.statements.length > 1) {
    q.options.statements.splice(idx, 1);
    syncTfAnswer(q);
  }
};

const ensureMatching = (q) => {
  if (!q.options || typeof q.options !== 'object' || !Array.isArray(q.options.pairs)) {
    q.options = {
      pairs: [
        { left: '', right: '', key: 'A' },
        { left: '', right: '', key: 'B' }
      ]
    };
  }
  syncMatchingAnswer(q);
  return q.options.pairs;
};

const syncMatchingAnswer = (q) => {
  if (q.options?.pairs) {
    q.correct_answer = q.options.pairs.map((p, idx) => `${idx + 1}-${p.key}`).join(', ');
  }
};

const addMatchingPair = (q) => {
  if (!q.options?.pairs) ensureMatching(q);
  const nextKey = String.fromCharCode(65 + q.options.pairs.length);
  q.options.pairs.push({ left: '', right: '', key: nextKey });
  syncMatchingAnswer(q);
};

const removeMatchingPair = (q, idx) => {
  if (q.options?.pairs && q.options.pairs.length > 1) {
    q.options.pairs.splice(idx, 1);
    syncMatchingAnswer(q);
  }
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
  sanitizeAllQuestionsBeforeSave();
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

const stimulusFileInput = ref(null);
const uploadingImage = ref(false);

const triggerStimulusUpload = () => {
  if (stimulusFileInput.value) {
    stimulusFileInput.value.value = '';
    stimulusFileInput.value.click();
  }
};

const handleQuestionImageUpload = async (event, question) => {
  const file = event.target.files[0];
  if (!file || !activePackage.value) return;

  uploadingImage.value = true;
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
  } finally {
    uploadingImage.value = false;
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

const handleKeyDown = (e) => {
  if (!activePackage.value) return;
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
    e.preventDefault();
    saveAllQuestions();
  }
};

onMounted(async () => {
  window.addEventListener('keydown', handleKeyDown);
  await fetchOptions();
  await fetchPackages();
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
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
