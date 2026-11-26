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

    <!-- Interactive Assessment Metric Cards (Gaya Card Pengelompokan ASTS & ASAS) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- 1. Semua Paket -->
      <button
        type="button"
        @click="activeExamTypeTab = 'all'"
        class="p-4 rounded-2xl border text-left transition relative overflow-hidden group shadow-2xs"
        :class="activeExamTypeTab === 'all'
          ? 'bg-slate-900 text-white border-slate-900 ring-2 ring-emerald-500 shadow-md'
          : 'bg-white hover:bg-slate-50 text-slate-800 border-slate-200/80'"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider" :class="activeExamTypeTab === 'all' ? 'text-slate-300' : 'text-slate-500'">Semua Paket</span>
          <div class="p-2 rounded-xl" :class="activeExamTypeTab === 'all' ? 'bg-white/10 text-emerald-400' : 'bg-slate-100 text-slate-600'">
            <Layers class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black">{{ examTypeCounts.all }}</div>
        <div class="text-[11px] mt-1" :class="activeExamTypeTab === 'all' ? 'text-slate-300' : 'text-slate-400'">Total Seluruh Paket Soal</div>
      </button>

      <!-- 2. ASTS -->
      <button
        type="button"
        @click="activeExamTypeTab = 'sts'"
        class="p-4 rounded-2xl border text-left transition relative overflow-hidden group shadow-2xs"
        :class="activeExamTypeTab === 'sts'
          ? 'bg-indigo-600 text-white border-indigo-600 ring-2 ring-indigo-300 shadow-md'
          : 'bg-white hover:bg-indigo-50/30 text-slate-800 border-slate-200/80'"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider" :class="activeExamTypeTab === 'sts' ? 'text-indigo-100' : 'text-indigo-600'">Kelompok ASTS</span>
          <div class="p-2 rounded-xl" :class="activeExamTypeTab === 'sts' ? 'bg-white/10 text-white' : 'bg-indigo-50 text-indigo-600'">
            <BookOpen class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black">{{ examTypeCounts.sts }}</div>
        <div class="text-[11px] mt-1" :class="activeExamTypeTab === 'sts' ? 'text-indigo-100' : 'text-slate-400'">Sumatif Tengah Semester</div>
      </button>

      <!-- 3. ASAS -->
      <button
        type="button"
        @click="activeExamTypeTab = 'sas'"
        class="p-4 rounded-2xl border text-left transition relative overflow-hidden group shadow-2xs"
        :class="activeExamTypeTab === 'sas'
          ? 'bg-purple-600 text-white border-purple-600 ring-2 ring-purple-300 shadow-md'
          : 'bg-white hover:bg-purple-50/30 text-slate-800 border-slate-200/80'"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider" :class="activeExamTypeTab === 'sas' ? 'text-purple-100' : 'text-purple-600'">Kelompok ASAS</span>
          <div class="p-2 rounded-xl" :class="activeExamTypeTab === 'sas' ? 'bg-white/10 text-white' : 'bg-purple-50 text-purple-600'">
            <Award class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black">{{ examTypeCounts.sas }}</div>
        <div class="text-[11px] mt-1" :class="activeExamTypeTab === 'sas' ? 'text-purple-100' : 'text-slate-400'">Sumatif Akhir Semester</div>
      </button>

      <!-- 4. Formatif / Lainnya -->
      <button
        type="button"
        @click="activeExamTypeTab = (activeExamTypeTab === 'uh' ? 'other' : 'uh')"
        class="p-4 rounded-2xl border text-left transition relative overflow-hidden group shadow-2xs"
        :class="(activeExamTypeTab === 'uh' || activeExamTypeTab === 'other')
          ? 'bg-teal-600 text-white border-teal-600 ring-2 ring-teal-300 shadow-md'
          : 'bg-white hover:bg-teal-50/30 text-slate-800 border-slate-200/80'"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs font-bold uppercase tracking-wider" :class="(activeExamTypeTab === 'uh' || activeExamTypeTab === 'other') ? 'text-teal-100' : 'text-teal-600'">Formatif / Lainnya</span>
          <div class="p-2 rounded-xl" :class="(activeExamTypeTab === 'uh' || activeExamTypeTab === 'other') ? 'bg-white/10 text-white' : 'bg-teal-50 text-teal-600'">
            <Calendar class="w-4 h-4" />
          </div>
        </div>
        <div class="text-2xl sm:text-3xl font-black">{{ examTypeCounts.uh + examTypeCounts.other }}</div>
        <div class="text-[11px] mt-1" :class="(activeExamTypeTab === 'uh' || activeExamTypeTab === 'other') ? 'text-teal-100' : 'text-slate-400'">Formatif, AM & Lainnya</div>
      </button>
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

    <div v-else class="space-y-8">
      <div
        v-for="grp in groupedPackages"
        :key="grp.key"
        class="space-y-4"
      >
        <!-- Group Section Header -->
        <div class="flex items-center justify-between pb-3 border-b-2" :class="grp.headerBorderClass">
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl shadow-2xs" :class="grp.iconBg">
              <component :is="grp.icon" class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2.5">
                <h2 class="text-base sm:text-lg font-black text-slate-800">{{ grp.label }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black shadow-2xs" :class="grp.badgeClass">
                  {{ grp.packages.length }} Paket
                </span>
              </div>
              <p class="text-xs text-slate-400 font-medium">{{ grp.subLabel }}</p>
            </div>
          </div>
          <button
            type="button"
            @click="openCreateModalWithExamType(grp.key)"
            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700 transition shadow-2xs"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Tambah {{ grp.shortLabel }}</span>
          </button>
        </div>

        <!-- Empty state within group if selected specifically and empty -->
        <div v-if="grp.packages.length === 0" class="bg-white rounded-2xl border border-dashed border-slate-200 p-8 text-center">
          <p class="text-xs text-slate-500 mb-3">Belum ada paket soal untuk kategori {{ grp.shortLabel }}.</p>
          <button
            type="button"
            @click="openCreateModalWithExamType(grp.key)"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition shadow-xs"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Buat Paket {{ grp.shortLabel }} Baru</span>
          </button>
        </div>

        <!-- Cards Grid for this group -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
          <div
            v-for="pkg in grp.packages"
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
                <span
                  class="text-[11px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-2xs"
                  :class="getExamTypeBadge(pkg.exam_type).class"
                >
                  {{ getExamTypeBadge(pkg.exam_type).label }}
                </span>
              </div>

              <h3 class="text-base font-bold text-slate-800 line-clamp-1 mb-1" :title="pkg.title">
                {{ pkg.title }}
              </h3>

              <div class="text-xs text-slate-500 space-y-1">
                <div class="flex items-center gap-1.5">
                  <GraduationCap class="w-3.5 h-3.5 text-slate-400" />
                  <span>
                    {{ pkg.subject?.name }} &bull; 
                    <span :class="pkg.target_grade ? 'font-bold text-indigo-700' : ''">
                      {{ pkg.target_grade ? ('Kelas ' + pkg.target_grade + ' (Global)') : ('Kelas ' + (pkg.class_room?.name || '')) }}
                    </span>
                  </span>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap">
                  <Calendar class="w-3.5 h-3.5 text-slate-400" />
                  <span>Semester {{ pkg.semester === 'ganjil' ? 'Ganjil' : 'Genap' }} ({{ pkg.academic_phase || 'Fase D' }})</span>
                  <span v-if="pkg.execution_date" class="text-slate-400">&bull; {{ pkg.execution_date }}</span>
                </div>
                <div v-if="pkg.target_grade" class="pt-1 flex items-center gap-1.5 flex-wrap">
                  <span
                    v-if="pkg.correction_children && pkg.correction_children.length > 0"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                  >
                    <CheckCircle2 class="w-3 h-3" />
                    Koreksi Terpisah: {{ pkg.correction_children.map(c => c.class_room?.name).filter(Boolean).join(', ') }}
                  </span>
                  <span
                    v-else-if="pkg.package_scope === 'bank_only'"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                  >
                    🔒 Hanya di Bank Soal
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"
                  >
                    🌐 Koreksi Gabungan Global
                  </span>
                  <button
                    type="button"
                    @click="openSplitModal(pkg)"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition cursor-pointer"
                    title="Atur atau pecah penyaluran ke Menu Koreksi per kelas (9A / 9B)"
                  >
                    <Layers class="w-3 h-3" />
                    {{ pkg.correction_children?.length ? 'Kelola Kelas Koreksi' : 'Pecah ke Koreksi' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Card Body / Dynamic Question Type Stats -->
            <div class="p-5 bg-slate-50/50 space-y-3">
              <div
                class="grid gap-2 text-center"
                :class="{
                  'grid-cols-1': getPackageQuestionStats(pkg).length === 1,
                  'grid-cols-2': getPackageQuestionStats(pkg).length === 2,
                  'grid-cols-3': getPackageQuestionStats(pkg).length === 3 || getPackageQuestionStats(pkg).length >= 5,
                  'grid-cols-2 sm:grid-cols-4': getPackageQuestionStats(pkg).length === 4
                }"
              >
                <div
                  v-for="st in getPackageQuestionStats(pkg)"
                  :key="st.key"
                  class="bg-white p-2.5 rounded-xl border border-slate-100 shadow-2xs flex flex-col justify-center min-w-0"
                >
                  <div class="text-[11px] text-slate-400 font-medium truncate" :title="st.label">{{ st.label }}</div>
                  <div class="text-base font-bold mt-0.5" :class="st.color">{{ st.count }}</div>
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
                      <a :href="getPrintUrl(pkg.id, 'naskah', { layout: '2col' })" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition">
                        <span>📄</span>
                        <span>Naskah 2 Kolom (Hemat)</span>
                      </a>
                      <a :href="getPrintUrl(pkg.id, 'naskah', { layout: '1col' })" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                        <span>📄</span>
                        <span>Naskah 1 Kolom</span>
                      </a>
                    </div>
                    <div class="py-1">
                      <a :href="getPrintUrl(pkg.id, 'kisi-kisi')" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                        <span>📊</span>
                        <span>Kisi-Kisi Resmi</span>
                      </a>
                      <a :href="getPrintUrl(pkg.id, 'kartu-soal')" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                        <span>🗂️</span>
                        <span>Kartu Soal</span>
                      </a>
                    </div>
                    <div class="py-1">
                      <a :href="getPrintUrl(pkg.id, 'telaah')" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                        <span>📝</span>
                        <span>Berita Acara Telaah</span>
                      </a>
                      <a :href="getPrintUrl(pkg.id, 'kunci')" target="_blank" class="flex items-center gap-2 px-3 py-1.5 hover:bg-emerald-50 hover:text-emerald-700 transition">
                        <span>🔑</span>
                        <span>Kunci & Rubrik Nilai</span>
                      </a>
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-1.5">
                  <button
                    type="button"
                    @click="openEditModal(pkg)"
                    class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                    title="Edit Info & Jenis Asesmen Paket"
                  >
                    <Settings class="w-4 h-4" />
                  </button>
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
      </div>
    </div>

    <!-- Modal Buat Paket Soal Baru -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs overflow-y-auto">
      <div class="bg-white w-full max-w-xl rounded-2xl shadow-xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] my-auto">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
          <h3 class="text-base font-bold text-slate-800">Buat Paket Soal Baru</h3>
          <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>
        <form @submit.prevent="createPackage" class="p-6 space-y-4 overflow-y-auto flex-1">
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
              <select v-model="createForm.target_selection" @change="syncCreateCorrectionClasses" required class="form-select text-xs font-medium">
                <option value="">Pilih Kelas Target</option>
                <optgroup v-if="globalTargetOptions.length" label="🌐 Tingkat Global (Soal Bersama / ASAS / ASTS)">
                  <option v-for="opt in globalTargetOptions" :key="opt.key" :value="opt.key">
                    {{ opt.label }}
                  </option>
                </optgroup>
                <optgroup v-if="specificTargetOptions.length" label="🏫 Rombel Spesifik (Khusus Guru / Kelas Tertentu)">
                  <option v-for="opt in specificTargetOptions" :key="opt.key" :value="opt.key">
                    {{ opt.label }}
                  </option>
                </optgroup>
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
          <!-- Bulan & Tahun Pelaksanaan Ujian (Titimangsa Cetak Dokumen) -->
          <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <Calendar class="w-3.5 h-3.5 text-indigo-600" />
                Bulan & Tahun Pelaksanaan Ujian
              </label>
              <span class="text-[10px] text-slate-500 font-medium">Titimangsa Tanda Tangan Cetak</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Bulan Pelaksanaan</label>
                <select v-model="createForm.execution_month" class="form-select text-xs">
                  <option v-for="m in monthOptions" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tahun Pelaksanaan</label>
                <input
                  type="number"
                  v-model.number="createForm.execution_year"
                  min="2020"
                  max="2035"
                  class="form-input text-xs"
                  placeholder="2026"
                />
              </div>
            </div>
            <p class="text-[10px] text-slate-400">
              Otomatis diterapkan ke Titimangsa Lembar Kisi-Kisi, Lembar Telaah, & Kunci Jawaban (contoh: <em>Bogor, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ createForm.execution_month }} {{ createForm.execution_year }}</em>).
            </p>
          </div>
          <!-- Pola & Komposisi Soal (Preset Cerdas) -->
          <div class="p-3.5 bg-gradient-to-br from-indigo-50/80 to-slate-50 border border-indigo-100 rounded-xl space-y-2.5">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-indigo-950 flex items-center gap-1.5">
                <Sparkles class="w-3.5 h-3.5 text-indigo-600" />
                Pola & Komposisi Tipe Soal
              </label>
              <span class="text-[10px] font-bold text-indigo-600 bg-white px-2 py-0.5 rounded-full border border-indigo-200 shadow-2xs">
                Otomatisasi Tipe & Bobot
              </span>
            </div>
            
            <select
              v-model="createForm.composition_preset"
              @change="handleCompositionPresetChange"
              class="form-select font-bold text-xs bg-white border-indigo-200 text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
            >
              <option value="standard_mts_45">🎯 Standar Baku MTs (45 Butir - Template Resmi ASAS)</option>
              <option value="custom_distribution">✏️ Kustom Pembagian per Tipe Soal (Atur Bebas Jumlah Tiap Tipe)</option>
              <option value="classic_pg_essay">📝 Standar Sederhana (Hanya PG & Uraian)</option>
              <option value="custom">⚙️ Polos / Bebas (Tanpa Pembagian Tipe di Awal)</option>
            </select>

            <!-- Preview Khusus Standar MTs 45 Butir -->
            <div v-if="createForm.composition_preset === 'standard_mts_45'" class="bg-white p-3 rounded-lg border border-indigo-100 text-[11px] text-slate-700 space-y-1.5 shadow-2xs">
              <div class="flex items-center justify-between font-bold text-indigo-950 border-b border-indigo-50 pb-1">
                <span>Rincian Struktur Paket Soal:</span>
                <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-[10px] font-bold">100 Poin Pas</span>
              </div>
              <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px] pt-0.5">
                <div>• <strong>No. 1–20</strong> : PG Tunggal (20 × 1 = 20 Poin)</div>
                <div>• <strong>No. 21–30</strong> : PG Kompleks (10 × 3 = 30 Poin)</div>
                <div>• <strong>No. 31–35</strong> : Benar / Salah (5 × 2 = 10 Poin)</div>
                <div>• <strong>No. 36–40</strong> : Menjodohkan (5 × 3 = 15 Poin)</div>
                <div class="col-span-2">• <strong>No. 41–45</strong> : Uraian / Essay (5 × 5 = 25 Poin)</div>
              </div>
              <p class="text-[10px] text-indigo-600 font-medium italic pt-1 border-t border-slate-100">
                ✨ Begitu paket dibuat, seluruh 45 nomor langsung memiliki tipe soal dan bobot nilai yang siap pakai.
              </p>
            </div>

            <!-- Builder Kustom Pembagian Butir & Poin per Tipe Soal -->
            <div v-if="createForm.composition_preset === 'custom_distribution'" class="bg-white p-3 rounded-xl border border-indigo-200 text-xs text-slate-700 space-y-2.5 shadow-2xs">
              <div class="flex items-center justify-between border-b border-indigo-50 pb-1.5">
                <span class="font-bold text-indigo-950 flex items-center gap-1.5">
                  <span>✏️ Tentukan Jumlah Butir & Poin Tiap Tipe:</span>
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="Math.abs(customTotalPoints - 100) < 0.01 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200'">
                  Total Skor: {{ customTotalPoints }} Poin
                </span>
              </div>

              <!-- Column Captions (Desktop) -->
              <div class="hidden sm:grid sm:grid-cols-[1fr_auto_auto_84px] gap-2 px-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">
                <span>Tipe Soal</span>
                <span class="w-[88px] text-center">Jumlah</span>
                <span class="w-[82px] text-center">Poin/Butir</span>
                <span class="text-right">Bobot Nilai</span>
              </div>

              <div class="space-y-1.5">
                <div
                  v-for="qt in customQuestionTypes"
                  :key="qt.key"
                  class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-2 bg-slate-50/80 rounded-lg border border-slate-100"
                >
                  <div class="flex items-center gap-2 min-w-0">
                    <span class="w-7 h-7 shrink-0 rounded-md font-bold text-[10px] flex items-center justify-center" :class="qt.badgeClass">{{ qt.badge }}</span>
                    <div class="min-w-0">
                      <div class="font-bold text-slate-800 text-[11px] truncate">{{ qt.label }}</div>
                      <div class="text-[10px] text-slate-400">
                        {{ qt.isEssay ? 'Kelompok Uraian' : 'Kelompok Objektif' }} &bull; Default {{ qt.defaultPoint }} pt
                      </div>
                    </div>
                  </div>
                  <div class="flex items-center justify-end gap-1.5 shrink-0">
                    <div class="flex items-center gap-1">
                      <input
                        type="number"
                        v-model.number="createForm.custom_counts[qt.key]"
                        min="0"
                        max="100"
                        class="form-input w-14 py-1 text-center font-bold text-xs"
                        @input="recalculateCustomDistribution"
                      />
                      <span class="text-[10px] text-slate-500">btr</span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-bold">&times;</span>
                    <div class="flex items-center gap-1">
                      <input
                        type="number"
                        v-model.number="createForm.custom_points[qt.key]"
                        min="0.5"
                        step="0.5"
                        max="100"
                        class="form-input w-14 py-1 text-center font-bold text-xs text-indigo-700"
                        @input="recalculateCustomDistribution"
                      />
                      <span class="text-[10px] text-slate-500">pt</span>
                    </div>
                    <div class="w-[84px] text-right">
                      <span
                        v-if="customTypeBreakdown[qt.key]?.count > 0"
                        class="inline-block px-2 py-0.5 rounded-md text-[10px] font-bold border"
                        :class="qt.isEssay ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                      >
                        {{ customTypeBreakdown[qt.key].subtotal }} pt ({{ customTypeBreakdown[qt.key].percent }}%)
                      </span>
                      <span v-else class="text-[10px] text-slate-300 font-medium">0 pt (0%)</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Live Summary Box -->
              <div class="p-2 bg-indigo-50/70 rounded-lg border border-indigo-100 text-[11px] space-y-1">
                <div class="flex items-center justify-between font-bold text-indigo-950">
                  <span>📊 Total Soal Otomatis:</span>
                  <span class="text-xs text-indigo-700 bg-white px-2 py-0.5 rounded border border-indigo-200">
                    {{ createForm.total_questions }} Butir Soal
                  </span>
                </div>
                <div class="text-[10px] text-slate-600 pt-0.5 leading-relaxed">
                  Struktur Nomor:
                  <span class="font-bold text-indigo-900">{{ customDistributionPlanText }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Butir Soal *</label>
              <input
                type="number"
                v-model.number="createForm.total_questions"
                min="1"
                max="100"
                :disabled="createForm.composition_preset === 'standard_mts_45' || createForm.composition_preset === 'custom_distribution'"
                required
                class="form-input"
                :class="createForm.composition_preset === 'standard_mts_45' || createForm.composition_preset === 'custom_distribution' ? 'bg-slate-100 text-slate-500 font-bold cursor-not-allowed' : ''"
              />
              <span v-if="createForm.composition_preset === 'standard_mts_45'" class="text-[10px] text-slate-400">Terkunci 45 butir (Standar MTs)</span>
              <span v-else-if="createForm.composition_preset === 'custom_distribution'" class="text-[10px] text-slate-400">Dihitung otomatis dari komposisi di atas</span>
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
              <label class="block text-xs font-bold text-slate-700 mb-1">
                {{ createForm.composition_preset === 'standard_mts_45' || createForm.composition_preset === 'custom_distribution' ? 'Rasio Objektif : Uraian' : 'Bobot Objektif : Uraian (%)' }}
              </label>
              <div class="flex items-center gap-2">
                <input
                  type="number"
                  v-model.number="createForm.pg_weight"
                  placeholder="Objektif"
                  class="form-input text-center"
                  :disabled="createForm.composition_preset === 'standard_mts_45' || createForm.composition_preset === 'custom_distribution'"
                  :class="createForm.composition_preset === 'standard_mts_45' || createForm.composition_preset === 'custom_distribution' ? 'bg-slate-100 text-slate-500 font-bold cursor-not-allowed' : ''"
                />
                <span class="text-xs text-slate-400">:</span>
                <input
                  type="number"
                  v-model.number="createForm.essay_weight"
                  placeholder="Uraian"
                  class="form-input text-center"
                  :disabled="createForm.composition_preset === 'standard_mts_45' || createForm.composition_preset === 'custom_distribution'"
                  :class="createForm.composition_preset === 'standard_mts_45' || createForm.composition_preset === 'custom_distribution' ? 'bg-slate-100 text-slate-500 font-bold cursor-not-allowed' : ''"
                />
              </div>
              <span v-if="createForm.composition_preset === 'standard_mts_45'" class="text-[10px] text-slate-400">75 Poin Objektif : 25 Poin Uraian</span>
              <span v-else-if="createForm.composition_preset === 'custom_distribution'" class="text-[10px] text-slate-400">{{ createForm.pg_weight }}% Objektif : {{ createForm.essay_weight }}% Uraian</span>
              <span v-else class="text-[10px] text-slate-400">Total persentase bobot = 100%</span>
            </div>
          </div>

          <!-- Penyaluran ke Menu Koreksi (Khusus Paket Global) -->
          <div v-if="isCreateGlobalSelected" class="p-3.5 bg-indigo-50/70 border border-indigo-200 rounded-xl space-y-2.5">
            <div class="flex items-start justify-between gap-2">
              <div>
                <label class="block text-xs font-bold text-indigo-900">
                  🌐 Penyaluran ke Menu Koreksi (Kelas {{ selectedCreateGrade }})
                </label>
                <p class="text-[11px] text-indigo-700/80 leading-relaxed mt-0.5">
                  Bank Soal tetap menjadi <strong>1 Master Global</strong> untuk telaah kurikulum. Pilih bagaimana paket ini muncul di Menu Koreksi:
                </p>
              </div>
            </div>

            <div class="space-y-1.5">
              <label class="flex items-start gap-2.5 p-2.5 rounded-lg border cursor-pointer transition"
                :class="createForm.correction_mode === 'split_selected' ? 'bg-white border-emerald-400 shadow-2xs' : 'bg-white/60 border-indigo-100 hover:bg-white'">
                <input type="radio" v-model="createForm.correction_mode" value="split_selected" class="mt-0.5 text-emerald-600 focus:ring-emerald-500" />
                <div class="flex-1 text-xs">
                  <div class="font-bold text-slate-800">Pecah per Kelas di Menu Koreksi (Disarankan)</div>
                  <div class="text-[11px] text-slate-500">Buat paket koreksi terpisah per rombel dan hubungkan otomatis ke guru pengampu masing-masing kelas.</div>
                  <div v-if="createForm.correction_mode === 'split_selected' && createGradeRombelClasses.length" class="mt-2 pt-2 border-t border-slate-100">
                    <div class="text-[10px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Pilih Rombel Kelas yang Ingin Langsung Diisi:</div>
                    <div class="flex flex-wrap gap-2">
                      <label
                        v-for="cls in createGradeRombelClasses"
                        :key="cls.id"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs font-bold cursor-pointer transition"
                        :class="createForm.correction_class_ids.includes(cls.id) ? 'bg-emerald-50 border-emerald-400 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-500'"
                      >
                        <input
                          type="checkbox"
                          :value="cls.id"
                          v-model="createForm.correction_class_ids"
                          class="rounded text-emerald-600 focus:ring-emerald-500"
                        />
                        <span>Kelas {{ cls.name }}</span>
                      </label>
                    </div>
                  </div>
                </div>
              </label>

              <label class="flex items-start gap-2.5 p-2 rounded-lg border cursor-pointer transition"
                :class="createForm.correction_mode === 'global_single' ? 'bg-white border-indigo-400 shadow-2xs' : 'bg-white/60 border-indigo-100 hover:bg-white'">
                <input type="radio" v-model="createForm.correction_mode" value="global_single" class="mt-0.5 text-indigo-600 focus:ring-indigo-500" />
                <div class="text-xs">
                  <div class="font-bold text-slate-800">1 Paket Koreksi Gabungan Global</div>
                  <div class="text-[11px] text-slate-500">Semua kelas tingkat {{ selectedCreateGrade }} berada dalam 1 paket koreksi (dapat dipecah nanti).</div>
                </div>
              </label>

              <label class="flex items-start gap-2.5 p-2 rounded-lg border cursor-pointer transition"
                :class="createForm.correction_mode === 'none' ? 'bg-white border-amber-400 shadow-2xs' : 'bg-white/60 border-indigo-100 hover:bg-white'">
                <input type="radio" v-model="createForm.correction_mode" value="none" class="mt-0.5 text-amber-600 focus:ring-amber-500" />
                <div class="text-xs">
                  <div class="font-bold text-slate-800">Simpan di Bank Soal Saja (Tarik Manual per Guru)</div>
                  <div class="text-[11px] text-slate-500">Tidak otomatis masuk Menu Koreksi; masing-masing guru menarik ke kelasnya lewat tombol "Tarik dari Bank Soal".</div>
                </div>
              </label>
            </div>
          </div>

          <div class="pt-3 sticky bottom-0 bg-white/95 backdrop-blur-xs flex justify-end gap-2 border-t border-slate-100 mt-4 -mx-6 -mb-6 p-6 shrink-0">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 cursor-pointer">
              Batal
            </button>
            <button type="submit" :disabled="submitting" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm cursor-pointer">
              {{ submitting ? 'Membuat...' : 'Buat Paket Soal' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Pecah / Salurkan Paket Global ke Menu Koreksi -->
    <div v-if="showSplitModal && splitTargetPkg" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
          <div>
            <h3 class="text-base font-bold text-slate-800">Salurkan ke Menu Koreksi</h3>
            <p class="text-xs text-slate-500 line-clamp-1">{{ splitTargetPkg.title }}</p>
          </div>
          <button @click="showSplitModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>
        <div class="p-6 space-y-4">
          <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-xs text-indigo-800 leading-relaxed">
            Pilih rombel kelas tingkat <strong>Kelas {{ splitTargetPkg.target_grade }}</strong> yang ingin dibuatkan paket koreksinya secara terpisah.
            <span v-if="splitTargetPkg.correction_children?.length" class="block mt-1 font-semibold text-emerald-700">
              Sudah tersalur ke: {{ splitTargetPkg.correction_children.map(c => c.class_room?.name).filter(Boolean).join(', ') }}
            </span>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Rombel Tujuan di Menu Koreksi:</label>
            <div class="flex flex-wrap gap-2">
              <label
                v-for="cls in splitModalClasses"
                :key="cls.id"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border text-xs font-bold cursor-pointer transition"
                :class="splitSelectedClassIds.includes(cls.id) ? 'bg-emerald-50 border-emerald-400 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-600'"
              >
                <input type="checkbox" :value="cls.id" v-model="splitSelectedClassIds" class="rounded text-emerald-600 focus:ring-emerald-500" />
                <span>Kelas {{ cls.name }}</span>
              </label>
            </div>
            <p v-if="splitModalClasses.length === 0" class="text-xs text-rose-600 mt-1">Tidak ditemukan daftar rombel untuk tingkat ini.</p>
          </div>
          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showSplitModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 cursor-pointer">
              Batal
            </button>
            <button
              type="button"
              @click="submitSplitToCorrection"
              :disabled="submittingSplit || splitSelectedClassIds.length === 0"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-xs cursor-pointer"
            >
              {{ submittingSplit ? 'Menyalurkan...' : 'Salurkan ke Menu Koreksi' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Edit Info & Jenis Asesmen Paket Soal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs overflow-y-auto">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] my-auto">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
              <Settings class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800">Edit Info & Jenis Asesmen</h3>
              <p class="text-xs text-slate-400">Sesuaikan judul, kategori ujian (ASTS/ASAS), kelas, dan semester</p>
            </div>
          </div>
          <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>
        <form @submit.prevent="updatePackageInfo" class="p-6 space-y-4 overflow-y-auto flex-1">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Judul Paket Soal *</label>
            <input type="text" v-model="editForm.title" required class="form-input" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Asesmen *</label>
              <select v-model="editForm.exam_type" required class="form-select font-semibold">
                <option v-for="t in options.exam_types" :key="t.value" :value="t.value">{{ t.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Semester *</label>
              <select v-model="editForm.semester" required class="form-select">
                <option value="ganjil">Ganjil</option>
                <option value="genap">Genap</option>
              </select>
            </div>
          </div>
          <!-- Bulan & Tahun Pelaksanaan Ujian (Titimangsa Cetak Dokumen) -->
          <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <Calendar class="w-3.5 h-3.5 text-indigo-600" />
                Bulan & Tahun Pelaksanaan Ujian
              </label>
              <span class="text-[10px] text-slate-500 font-medium">Titimangsa Tanda Tangan Cetak</span>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Bulan Pelaksanaan</label>
                <select v-model="editForm.execution_month" class="form-select text-xs">
                  <option v-for="m in monthOptions" :key="m" :value="m">{{ m }}</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tahun Pelaksanaan</label>
                <input
                  type="number"
                  v-model.number="editForm.execution_year"
                  min="2020"
                  max="2035"
                  class="form-input text-xs"
                  placeholder="2026"
                />
              </div>
            </div>
            <p class="text-[10px] text-slate-400">
              Otomatis diterapkan ke Titimangsa Lembar Kisi-Kisi, Lembar Telaah, & Kunci Jawaban (contoh: <em>Bogor, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ editForm.execution_month }} {{ editForm.execution_year }}</em>).
            </p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran *</label>
              <select v-model="editForm.subject_id" required class="form-select">
                <option v-for="s in options.subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Kelas Target *</label>
              <select v-model="editForm.target_selection" required class="form-select text-xs font-medium">
                <option value="">Pilih Kelas Target</option>
                <optgroup v-if="globalTargetOptions.length" label="🌐 Tingkat Global (Soal Bersama / ASAS / ASTS)">
                  <option v-for="opt in globalTargetOptions" :key="opt.key" :value="opt.key">
                    {{ opt.label }}
                  </option>
                </optgroup>
                <optgroup v-if="specificTargetOptions.length" label="🏫 Rombel Spesifik (Khusus Guru / Kelas Tertentu)">
                  <option v-for="opt in specificTargetOptions" :key="opt.key" :value="opt.key">
                    {{ opt.label }}
                  </option>
                </optgroup>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Butir Soal *</label>
              <input type="number" v-model.number="editForm.total_questions" min="1" max="100" required class="form-input" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">KKM / KKTP</label>
              <input type="number" v-model.number="editForm.kkm" min="0" max="100" class="form-input" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Kurikulum / Fase</label>
              <select v-model="editForm.academic_phase" class="form-select">
                <option value="Fase D">Fase D (Kurikulum Merdeka MTs)</option>
                <option value="K-13">Kurikulum 2013 (K13)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Bobot Objektif : Uraian (%)</label>
              <div class="flex items-center gap-2">
                <input type="number" v-model.number="editForm.pg_weight" placeholder="Objektif" class="form-input text-center" />
                <span class="text-xs text-slate-400">:</span>
                <input type="number" v-model.number="editForm.essay_weight" placeholder="Uraian" class="form-input text-center" />
              </div>
              <span class="text-[10px] text-slate-400">Total proporsi persentase harus 100%</span>
            </div>
          </div>
          <div class="pt-3 sticky bottom-0 bg-white/95 backdrop-blur-xs flex justify-end gap-2 border-t border-slate-100 mt-4 -mx-6 -mb-6 p-6 shrink-0">
            <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 cursor-pointer">
              Batal
            </button>
            <button type="submit" :disabled="submitting" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition cursor-pointer">
              {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
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
            title="Tampilkan / Sembunyikan Panel Samping Kisi-Kisi"
          >
            <TableProperties class="w-3.5 h-3.5" />
            <span class="hidden xl:inline">Panel Kisi-Kisi</span>
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
              title="Preview Instrumen Telaah Soal"
            >
              <ClipboardCheck class="w-3.5 h-3.5 text-slate-400" />
              <span class="hidden xl:inline">Telaah</span>
            </button>
            <button
              type="button"
              @click="previewKunci"
              class="px-2.5 py-1.5 text-xs font-medium bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg border border-slate-700 transition flex items-center gap-1"
              title="Preview Kunci Jawaban & Pedoman Penskoran"
            >
              <Key class="w-3.5 h-3.5 text-slate-400" />
              <span class="hidden xl:inline">Kunci</span>
            </button>
          </div>

          <!-- Auto-Fill All Kisi-Kisi Button -->
          <button
            type="button"
            @click="autoGenerateAllKisiKisi"
            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 text-xs font-bold rounded-lg shadow-sm transition cursor-pointer"
            title="Otomatis rumuskan Kisi-Kisi (TP, Materi, Indikator, Level Kognitif) untuk semua nomor soal"
          >
            <Sparkles class="w-3.5 h-3.5 text-slate-950" />
            <span>Auto-Kisi</span>
          </button>

          <!-- Reset All Kisi-Kisi Button -->
          <button
            type="button"
            @click="resetAllKisiKisi"
            class="hidden sm:inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 bg-slate-800 hover:bg-rose-950/70 text-slate-300 hover:text-rose-200 text-xs font-semibold rounded-lg border border-slate-700 hover:border-rose-700/50 shadow-sm transition cursor-pointer"
            title="Kosongkan isian kisi-kisi (TP, Materi, Indikator) pada semua nomor soal"
          >
            <RotateCcw class="w-3.5 h-3.5 text-slate-400 group-hover:text-rose-300" />
            <span>Reset Kisi</span>
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

            <!-- Actions: Reset & Auto-Generate -->
            <div class="flex items-center gap-1.5 shrink-0">
              <button
                type="button"
                @click="resetSingleKisiKisi(currentQuestionIndex)"
                class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-200 hover:border-rose-200 text-[11px] font-bold rounded-lg shadow-2xs transition cursor-pointer"
                title="Kosongkan isian TP, Materi, dan Indikator pada butir soal ini"
              >
                <RotateCcw class="w-3.5 h-3.5 text-slate-500" />
                <span>Reset</span>
              </button>

              <button
                type="button"
                @click="autoGenerateSingleKisiKisi(currentQuestionIndex)"
                class="inline-flex items-center gap-1 px-2.5 py-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-[11px] font-bold rounded-lg shadow-2xs transition cursor-pointer"
                title="Otomatis rumuskan TP, Materi, Indikator, dan Level Kognitif dari teks soal ini"
              >
                <Sparkles class="w-3.5 h-3.5 text-amber-300" />
                <span>Auto-Fill</span>
              </button>
            </div>
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
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-bold text-slate-700">Preset Standar & Cepat:</label>
                <span class="text-[10px] text-slate-400">Format Resmi MTs Al-Hasanah</span>
              </div>

              <!-- Master 1-Click Button untuk 45 Butir -->
              <button
                type="button"
                @click="applyFullMtsStandardPreset"
                class="w-full mb-2 py-2 px-3 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 rounded-xl border border-emerald-300 flex items-center justify-center gap-1.5 transition cursor-pointer shadow-2xs"
                title="Terapkan pembagian tipe dan bobot resmi (No 1-20 PG, 21-30 PGK, 31-35 B/S, 36-40 Jodoh, 41-45 Essay) total 100 poin"
              >
                <Sparkles class="w-4 h-4 text-emerald-600" />
                <span>⚡ Terapkan Format Standar Lengkap MTs (No 1–45: 5 Tipe Soal - 100 Poin)</span>
              </button>

              <div class="flex flex-wrap gap-1.5">
                <button
                  type="button"
                  @click="applyBatchPreset(1, 20, 'pg', 1.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 1 && batchForm.toNumber === 20 && batchForm.targetType === 'pg'
                    ? 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200'"
                >
                  <span>🔘 No. 1–20: PG Tunggal (1 Poin)</span>
                </button>

                <button
                  type="button"
                  @click="applyBatchPreset(21, 30, 'pg_complex', 3.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 21 && batchForm.toNumber === 30 && batchForm.targetType === 'pg_complex'
                    ? 'bg-indigo-100 text-indigo-800 border-indigo-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200'"
                >
                  <span>🔲 No. 21–30: PG Kompleks (3 Poin)</span>
                </button>

                <button
                  type="button"
                  @click="applyBatchPreset(31, 35, 'true_false', 2.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 31 && batchForm.toNumber === 35 && batchForm.targetType === 'true_false'
                    ? 'bg-purple-100 text-purple-800 border-purple-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200'"
                >
                  <span>🎯 No. 31–35: Benar / Salah (2 Poin)</span>
                </button>

                <button
                  type="button"
                  @click="applyBatchPreset(36, 40, 'matching', 3.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 36 && batchForm.toNumber === 40 && batchForm.targetType === 'matching'
                    ? 'bg-teal-100 text-teal-800 border-teal-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200'"
                >
                  <span>🔗 No. 36–40: Menjodohkan (3 Poin)</span>
                </button>

                <button
                  type="button"
                  @click="applyBatchPreset(41, 45, 'essay', 5.0)"
                  class="px-2.5 py-1 text-xs font-semibold rounded-lg border transition flex items-center gap-1.5 cursor-pointer"
                  :class="batchForm.fromNumber === 41 && batchForm.toNumber === 45 && batchForm.targetType === 'essay'
                    ? 'bg-blue-100 text-blue-800 border-blue-300 font-bold'
                    : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200'"
                >
                  <span>📝 No. 41–45: Uraian / Essay (5 Poin)</span>
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
              <select v-model="batchForm.targetType" @change="handleBatchTargetTypeChange" class="form-select font-bold text-xs" required>
                <option value="true_false">Benar / Salah (2 Poin - Standar No. 31-35)</option>
                <option value="matching">Menjodohkan (3 Poin - Standar No. 36-40)</option>
                <option value="pg">Pilihan Ganda (1 Poin - PG Tunggal Opsi A-D)</option>
                <option value="pg_complex">Pilihan Ganda Kompleks (3 Poin - Multi Kunci)</option>
                <option value="short_answer">Isian Singkat (2 Poin - Short Answer)</option>
                <option value="essay">Uraian / Essay (5 Poin)</option>
              </select>
            </div>

            <!-- Bobot Nilai -->
            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-bold text-slate-700">Bobot Nilai per Butir Soal *</label>
                <button
                  type="button"
                  @click="resetBatchWeightToDefault"
                  class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold underline cursor-pointer"
                >
                  Gunakan Bobot Standar
                </button>
              </div>
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

            <!-- Preview Ringkasan & Estimasi Total Paket -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-2 text-slate-600">
              <div class="font-bold text-slate-800 flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                  <span>📋 Ringkasan Perubahan:</span>
                </span>
                <span
                  class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                  :class="batchNewTotalPoints === 100 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300'"
                >
                  {{ batchNewTotalPoints === 100 ? '✅ Pas 100 Poin' : `Total Paket: ${batchNewTotalPoints} Poin` }}
                </span>
              </div>
              <p>
                Akan mengubah total <strong class="text-indigo-700">{{ Math.max(0, (batchForm.toNumber - batchForm.fromNumber + 1)) }} butir soal</strong> (Nomor <strong>{{ batchForm.fromNumber }}</strong> s/d <strong>{{ batchForm.toNumber }}</strong>) menjadi tipe <strong class="text-slate-900">{{ getQuestionTypeLabel(batchForm.targetType) }}</strong> dengan bobot <strong class="text-emerald-700">{{ batchForm.scoreWeight }} poin</strong>.
              </p>
              <div class="pt-1.5 border-t border-slate-200/80 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Estimasi Total Skor Seluruh Paket:</span>
                <div class="flex items-center gap-2">
                  <span class="text-slate-400 line-through">{{ batchCurrentTotalPoints }} Poin</span>
                  <span class="font-bold text-slate-700">➔</span>
                  <span class="font-black" :class="batchNewTotalPoints === 100 ? 'text-emerald-600' : 'text-amber-600'">
                    {{ batchNewTotalPoints }} Poin
                  </span>
                  <span v-if="batchNewTotalPoints !== 100" class="text-[10px] text-amber-600 font-semibold">
                    ({{ 100 - batchNewTotalPoints > 0 ? 'kurang ' + (100 - batchNewTotalPoints) : 'lebih ' + (batchNewTotalPoints - 100) }} poin)
                  </span>
                </div>
              </div>

              <!-- Rekomendasi Penyeimbang Cepat (Jika Belum Pas 100 Poin) -->
              <div
                v-if="batchNewTotalPoints !== 100 && rebalanceSuggestion"
                class="mt-2 p-2.5 bg-amber-50/90 border border-amber-200/90 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2.5"
              >
                <div class="space-y-0.5">
                  <div class="flex items-center gap-1.5 font-bold text-amber-900 text-xs">
                    <Sparkles class="w-3.5 h-3.5 text-amber-600 shrink-0" />
                    <span>Rekomendasi Penyeimbang (Auto-Rebalance):</span>
                  </div>
                  <p class="text-[11px] text-amber-800 leading-tight">
                    {{ rebalanceSuggestion.description }}
                  </p>
                </div>
                <button
                  type="button"
                  @click="applyRebalanceSuggestion"
                  class="shrink-0 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold text-[11px] rounded-lg shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer"
                >
                  <Zap class="w-3 h-3 text-amber-200" />
                  <span>{{ rebalanceSuggestion.buttonText }}</span>
                </button>
              </div>

              <!-- Notifikasi Rebalance Aktif -->
              <div
                v-else-if="activeRebalance"
                class="mt-2 p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-800"
              >
                <div class="flex items-center gap-1.5">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>
                    Penyeimbang aktif: <strong>{{ activeRebalance.label }}</strong> diset <strong>{{ activeRebalance.targetWeight }} poin</strong> agar total paket pas 100.
                  </span>
                </div>
                <button
                  type="button"
                  @click="activeRebalance = null"
                  class="text-[10px] text-emerald-700 hover:underline ml-2 cursor-pointer font-semibold shrink-0"
                >
                  Batalkan
                </button>
              </div>
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
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue';
import api, { getImageUrl } from '../api';
import QuestionRichEditor from '../components/QuestionRichEditor.vue';
import { useConfirm } from '../composables/useConfirm';
import { useToast } from '../composables/useToast';

const { confirm } = useConfirm();
const toast = useToast();
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
  Key,
  Sparkles,
  Layers,
  Award,
  Settings,
  RotateCcw
} from 'lucide-vue-next';

// Data State
const loading = ref(false);
const packages = ref([]);
const activeExamTypeTab = ref('all');

const examTypeCounts = computed(() => {
  const counts = {
    all: packages.value.length,
    sts: 0,
    sas: 0,
    uh: 0,
    other: 0,
  };
  packages.value.forEach(p => {
    if (p.exam_type === 'sts') counts.sts++;
    else if (p.exam_type === 'sas') counts.sas++;
    else if (p.exam_type === 'uh') counts.uh++;
    else counts.other++;
  });
  return counts;
});

const assessmentGroups = [
  {
    key: 'sts',
    label: 'Asesmen Sumatif Tengah Semester (ASTS)',
    shortLabel: 'ASTS',
    subLabel: 'Penilaian Sumatif Tengah Semester Ganjil / Genap',
    icon: BookOpen,
    iconBg: 'bg-indigo-100 text-indigo-700',
    headerBorderClass: 'border-indigo-500',
    badgeClass: 'bg-indigo-100 text-indigo-700 border border-indigo-200'
  },
  {
    key: 'sas',
    label: 'Asesmen Sumatif Akhir Semester (ASAS)',
    shortLabel: 'ASAS',
    subLabel: 'Penilaian Sumatif Akhir Semester (SAS / PAS)',
    icon: Award,
    iconBg: 'bg-purple-100 text-purple-700',
    headerBorderClass: 'border-purple-500',
    badgeClass: 'bg-purple-100 text-purple-700 border border-purple-200'
  },
  {
    key: 'uh',
    label: 'Formatif / Ulangan Harian (UH)',
    shortLabel: 'Formatif',
    subLabel: 'Penilaian Harian & Formatif Lingkup Materi',
    icon: Calendar,
    iconBg: 'bg-teal-100 text-teal-700',
    headerBorderClass: 'border-teal-500',
    badgeClass: 'bg-teal-100 text-teal-700 border border-teal-200'
  },
  {
    key: 'other',
    label: 'Asesmen Lainnya (AM / ASAT / Kuis)',
    shortLabel: 'Lainnya',
    subLabel: 'Asesmen Madrasah, Akhir Tahun, atau Kuis',
    icon: Layers,
    iconBg: 'bg-amber-100 text-amber-700',
    headerBorderClass: 'border-amber-500',
    badgeClass: 'bg-amber-100 text-amber-700 border border-amber-200'
  }
];

const groupedPackages = computed(() => {
  const groups = [];

  const targetGroups = activeExamTypeTab.value === 'all'
    ? assessmentGroups
    : assessmentGroups.filter(g => g.key === activeExamTypeTab.value);

  targetGroups.forEach(grp => {
    let pkgs = [];
    if (grp.key === 'sts') {
      pkgs = packages.value.filter(p => p.exam_type === 'sts');
    } else if (grp.key === 'sas') {
      pkgs = packages.value.filter(p => p.exam_type === 'sas');
    } else if (grp.key === 'uh') {
      pkgs = packages.value.filter(p => p.exam_type === 'uh');
    } else if (grp.key === 'other') {
      pkgs = packages.value.filter(p => !['sts', 'sas', 'uh'].includes(p.exam_type));
    }

    if (pkgs.length > 0 || activeExamTypeTab.value === grp.key) {
      groups.push({
        ...grp,
        packages: pkgs
      });
    }
  });

  return groups;
});

const getExamTypeBadge = (type) => {
  switch (type) {
    case 'sts':
      return { label: 'ASTS', class: 'bg-indigo-100 text-indigo-700 border border-indigo-200' };
    case 'sas':
      return { label: 'ASAS', class: 'bg-purple-100 text-purple-700 border border-purple-200' };
    case 'uh':
      return { label: 'Formatif / UH', class: 'bg-teal-100 text-teal-700 border border-teal-200' };
    case 'pat':
      return { label: 'ASAT / PAT', class: 'bg-amber-100 text-amber-700 border border-amber-200' };
    case 'am':
      return { label: 'Asesmen Madrasah', class: 'bg-emerald-100 text-emerald-700 border border-emerald-200' };
    case 'quiz':
      return { label: 'Kuis', class: 'bg-slate-100 text-slate-700 border border-slate-200' };
    default:
      return { label: (type || 'Ujian').toUpperCase(), class: 'bg-slate-100 text-slate-600 border border-slate-200' };
  }
};

const options = reactive({
  classes: [],
  target_classes: [],
  subjects: [],
  academic_years: [],
  active_year: null,
  exam_types: [],
  curriculum_types: [],
  cognitive_levels: [],
  difficulty_levels: []
});

const allTargetOptions = computed(() => {
  if (options.target_classes && options.target_classes.length > 0) {
    return options.target_classes;
  }
  const res = [];
  const classes = options.classes || [];
  const grades = [...new Set(classes.map(c => String(c.grade_level)).filter(Boolean))].sort();
  grades.forEach(g => {
    const rep = classes.find(c => String(c.grade_level) === g);
    const matching = classes.filter(c => String(c.grade_level) === g);
    const rNames = matching.map(c => c.name).join(', ');
    res.push({
      type: 'global',
      target_grade: g,
      class_room_id: rep ? rep.id : null,
      key: 'global_' + g,
      label: matching.length > 1 ? `Kelas ${g} (Global - Mencakup ${rNames})` : `Kelas ${g} (Global - Seluruh Rombel)`,
      short_label: `Kelas ${g} (Global)`,
      group: 'Tingkat Global (Soal Bersama / ASAS / ASTS)'
    });
  });
  classes.forEach(c => {
    res.push({
      type: 'specific',
      target_grade: null,
      class_room_id: c.id,
      key: 'class_' + c.id,
      label: `Kelas ${c.name} (Khusus Rombel ${c.name})`,
      short_label: `Kelas ${c.name}`,
      group: 'Rombel Spesifik (Khusus Guru / Kelas Tertentu)'
    });
  });
  return res;
});

const globalTargetOptions = computed(() => allTargetOptions.value.filter(o => o.type === 'global'));
const specificTargetOptions = computed(() => allTargetOptions.value.filter(o => o.type === 'specific'));

const filters = reactive({
  academic_year_id: '',
  subject_id: '',
  class_room_id: '',
  status_kurikulum: '',
  search: '',
  my_exams_only: true
});

// Modals & Helpers
const monthOptions = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const getCurrentMonthName = () => {
  return monthOptions[new Date().getMonth()] || 'November';
};

const getCurrentYear = () => {
  return new Date().getFullYear() || 2026;
};

const showCreateModal = ref(false);
const submitting = ref(false);
const createForm = reactive({
  title: '',
  subject_id: '',
  class_room_id: '',
  target_selection: '',
  target_grade: null,
  exam_type: 'sts',
  semester: 'ganjil',
  execution_month: getCurrentMonthName(),
  execution_year: getCurrentYear(),
  composition_preset: 'standard_mts_45',
  custom_counts: {
    pg: 20,
    pg_complex: 10,
    true_false: 5,
    matching: 5,
    short_answer: 0,
    essay: 5
  },
  custom_points: {
    pg: 1,
    pg_complex: 3,
    true_false: 2,
    matching: 3,
    short_answer: 2,
    essay: 5
  },
  total_questions: 45,
  kkm: 75,
  academic_phase: 'Fase D',
  pg_weight: 75,
  essay_weight: 25,
  correction_mode: 'split_selected',
  correction_class_ids: []
});

const isCreateGlobalSelected = computed(() => String(createForm.target_selection || '').startsWith('global_'));
const selectedCreateGrade = computed(() => {
  if (!isCreateGlobalSelected.value) return '';
  return String(createForm.target_selection).replace('global_', '');
});
const isStandaloneLokalClass = (c) => {
  const raw = String(c?.name || '').trim();
  const clean = raw.replace(/^(kelas|kls)\s*/i, '').trim().toLowerCase();
  const lower = raw.toLowerCase();
  return ['7', '8', '9'].includes(clean) || lower.includes('lokal') || lower.startsWith('l-') || lower.startsWith('lok-');
};
const getRombelsForGrade = (grade) => {
  if (!grade) return [];
  const all = (options.classes || []).filter(c => String(c.grade_level) === String(grade));
  const regular = all.filter(c => !isStandaloneLokalClass(c));
  return regular.length > 0 ? regular : all;
};
const createGradeRombelClasses = computed(() => getRombelsForGrade(selectedCreateGrade.value));

const syncCreateCorrectionClasses = () => {
  if (isCreateGlobalSelected.value) {
    createForm.correction_mode = 'split_selected';
    createForm.correction_class_ids = createGradeRombelClasses.value.map(c => c.id);
  } else {
    createForm.correction_mode = 'global_single';
    createForm.correction_class_ids = [];
  }
};

// Split existing global package to Menu Koreksi modal state
const showSplitModal = ref(false);
const splitTargetPkg = ref(null);
const splitSelectedClassIds = ref([]);
const submittingSplit = ref(false);
const splitModalClasses = computed(() => getRombelsForGrade(splitTargetPkg.value?.target_grade));

const openSplitModal = (pkg) => {
  splitTargetPkg.value = pkg;
  const available = getRombelsForGrade(pkg.target_grade);
  const existingIds = (pkg.correction_children || []).map(c => c.class_room_id);
  splitSelectedClassIds.value = existingIds.length > 0
    ? [...new Set(existingIds)]
    : available.map(c => c.id);
  showSplitModal.value = true;
};

const submitSplitToCorrection = async () => {
  if (!splitTargetPkg.value || splitSelectedClassIds.value.length === 0) return;
  submittingSplit.value = true;
  try {
    const res = await api.post(`/teacher/exam-corrections/${splitTargetPkg.value.id}/split-by-class`, {
      class_room_ids: splitSelectedClassIds.value,
      hide_master_from_correction: true
    });
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      toast.success(d.message || 'Paket berhasil disalurkan ke Menu Koreksi!');
      showSplitModal.value = false;
      await fetchPackages();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || err.message || 'Gagal menyalurkan paket ke Menu Koreksi.');
  } finally {
    submittingSplit.value = false;
  }
};

const customQuestionTypes = [
  { key: 'pg', badge: 'PG', shortLabel: 'PG', label: 'Pilihan Ganda Tunggal', defaultPoint: 1, badgeClass: 'bg-emerald-100 text-emerald-800', isEssay: false },
  { key: 'pg_complex', badge: 'PGK', shortLabel: 'PGK', label: 'Pilihan Ganda Kompleks', defaultPoint: 3, badgeClass: 'bg-indigo-100 text-indigo-800', isEssay: false },
  { key: 'true_false', badge: 'B/S', shortLabel: 'B/S', label: 'Benar / Salah', defaultPoint: 2, badgeClass: 'bg-purple-100 text-purple-800', isEssay: false },
  { key: 'matching', badge: 'JOD', shortLabel: 'Jodoh', label: 'Menjodohkan (Matching)', defaultPoint: 3, badgeClass: 'bg-teal-100 text-teal-800', isEssay: false },
  { key: 'short_answer', badge: 'ISI', shortLabel: 'Isian', label: 'Isian Singkat (Short Answer)', defaultPoint: 2, badgeClass: 'bg-amber-100 text-amber-800', isEssay: false },
  { key: 'essay', badge: 'ESS', shortLabel: 'Essay', label: 'Uraian / Essay', defaultPoint: 5, badgeClass: 'bg-blue-100 text-blue-800', isEssay: true },
];

const getParsedCustomRows = () => {
  return customQuestionTypes.map(qt => {
    const count = Math.max(0, parseInt(createForm.custom_counts?.[qt.key]) || 0);
    const rawPt = parseFloat(createForm.custom_points?.[qt.key]);
    const point = rawPt > 0 ? rawPt : qt.defaultPoint;
    const subtotal = Math.round(count * point * 100) / 100;
    return { ...qt, count, point, subtotal };
  });
};

const customTotalPoints = computed(() => {
  const sum = getParsedCustomRows().reduce((acc, r) => acc + r.subtotal, 0);
  return Math.round(sum * 100) / 100;
});

const customTypeBreakdown = computed(() => {
  const rows = getParsedCustomRows();
  const total = rows.reduce((acc, r) => acc + r.subtotal, 0);
  const map = {};
  rows.forEach(r => {
    map[r.key] = {
      ...r,
      percent: total > 0 ? Math.round((r.subtotal / total) * 1000) / 10 : 0,
    };
  });
  return map;
});

const recalculateCustomDistribution = () => {
  const rows = getParsedCustomRows();
  const totalQ = rows.reduce((acc, r) => acc + r.count, 0);
  createForm.total_questions = totalQ > 0 ? totalQ : 1;

  const objPoints = rows.filter(r => !r.isEssay).reduce((acc, r) => acc + r.subtotal, 0);
  const essayPoints = rows.filter(r => r.isEssay).reduce((acc, r) => acc + r.subtotal, 0);
  const totalPoints = objPoints + essayPoints;

  if (totalPoints > 0) {
    createForm.pg_weight = Math.round((objPoints / totalPoints) * 10000) / 100;
    createForm.essay_weight = Math.round((100 - createForm.pg_weight) * 100) / 100;
  }
};

const customDistributionPlanText = computed(() => {
  const rows = getParsedCustomRows();
  const segments = [];
  let curr = 1;
  rows.forEach(r => {
    if (r.count > 0) {
      segments.push(`No ${curr}–${curr + r.count - 1} ${r.shortLabel}`);
      curr += r.count;
    }
  });

  return segments.length > 0 ? segments.join(' • ') : 'Belum ada butir soal';
});

const handleCompositionPresetChange = () => {
  if (createForm.composition_preset === 'standard_mts_45') {
    createForm.total_questions = 45;
    createForm.pg_weight = 75;
    createForm.essay_weight = 25;
  } else if (createForm.composition_preset === 'custom_distribution') {
    recalculateCustomDistribution();
  } else if (createForm.composition_preset === 'classic_pg_essay') {
    if (createForm.total_questions === 45) {
      createForm.total_questions = 35;
    }
    createForm.pg_weight = 70;
    createForm.essay_weight = 30;
  }
};

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
  scoreWeight: 2.0,
});

const getDefaultScoreWeightForType = (type) => {
  switch (type) {
    case 'pg': return 1.0;
    case 'pg_complex': return 3.0;
    case 'true_false': return 2.0;
    case 'matching': return 3.0;
    case 'short_answer': return 2.0;
    case 'essay': return 5.0;
    default: return 1.0;
  }
};

const handleBatchTargetTypeChange = () => {
  batchForm.scoreWeight = getDefaultScoreWeightForType(batchForm.targetType);
};

const resetBatchWeightToDefault = () => {
  batchForm.scoreWeight = getDefaultScoreWeightForType(batchForm.targetType);
};

const batchCurrentTotalPoints = computed(() => {
  return activeQuestions.value.reduce((sum, q) => sum + (Number(q.score_weight) || 0), 0);
});

const activeRebalance = ref(null);

watch(
  () => [batchForm.fromNumber, batchForm.toNumber, batchForm.targetType],
  () => {
    activeRebalance.value = null;
  }
);

const batchNewTotalPoints = computed(() => {
  const from = Math.max(1, parseInt(batchForm.fromNumber) || 1);
  const to = Math.min(activeQuestions.value.length, parseInt(batchForm.toNumber) || from);
  const newWeight = Number(batchForm.scoreWeight) || 0;

  let total = 0;
  activeQuestions.value.forEach(q => {
    if (q.question_number >= from && q.question_number <= to) {
      total += newWeight;
    } else if (activeRebalance.value && activeRebalance.value.affectedNumbers?.includes(q.question_number)) {
      total += Number(activeRebalance.value.targetWeight);
    } else {
      total += (Number(q.score_weight) || 0);
    }
  });
  return Math.round(total * 10) / 10;
});

const rebalanceSuggestion = computed(() => {
  const from = Math.max(1, parseInt(batchForm.fromNumber) || 1);
  const to = Math.min(activeQuestions.value.length, parseInt(batchForm.toNumber) || from);
  const newWeight = Number(batchForm.scoreWeight) || 0;

  // Hitung total skor jika batch ini diterapkan tanpa penyeimbang
  let unadjustedTotal = 0;
  activeQuestions.value.forEach(q => {
    if (q.question_number >= from && q.question_number <= to) {
      unadjustedTotal += newWeight;
    } else {
      unadjustedTotal += (Number(q.score_weight) || 0);
    }
  });

  const diff = Math.round((unadjustedTotal - 100) * 10) / 10;
  if (diff === 0) return null;

  // 1. Cek apakah ada butir Essay di luar rentang yang sedang diubah
  const outsideEssays = activeQuestions.value.filter(q =>
    (q.question_number < from || q.question_number > to) && q.question_type === 'essay'
  );

  if (outsideEssays.length > 0) {
    const currentEssaySum = outsideEssays.reduce((sum, q) => sum + (Number(q.score_weight) || 0), 0);
    const targetEssaySum = currentEssaySum - diff;
    if (targetEssaySum > 0) {
      const newWeightPerEssay = Math.round((targetEssaySum / outsideEssays.length) * 10) / 10;
      if (newWeightPerEssay > 0) {
        const firstNum = outsideEssays[0].question_number;
        const lastNum = outsideEssays[outsideEssays.length - 1].question_number;
        const rangeText = outsideEssays.length === 1 ? `No. ${firstNum}` : `No. ${firstNum}–${lastNum}`;
        const currentW = outsideEssays[0].score_weight || 5;
        return {
          type: 'essay',
          rangeText,
          label: `Uraian / Essay (${rangeText})`,
          description: diff > 0
            ? `Turunkan bobot ${outsideEssays.length} butir Essay (${rangeText}) dari ${currentW} poin jadi ${newWeightPerEssay} poin (kurangi ${diff} poin)`
            : `Naikkan bobot ${outsideEssays.length} butir Essay (${rangeText}) dari ${currentW} poin jadi ${newWeightPerEssay} poin (tambah ${Math.abs(diff)} poin)`,
          targetWeight: newWeightPerEssay,
          targetCount: outsideEssays.length,
          affectedNumbers: outsideEssays.map(q => q.question_number),
          buttonText: `⚡ Seimbangkan via Essay (Jadikan ${newWeightPerEssay} Poin)`
        };
      }
    }
  }

  // 2. Jika tidak ada Essay di luar rentang, tawarkan menyesuaikan bobot rentang saat ini
  const countAffected = Math.max(1, to - from + 1);
  const outsideScoreSum = activeQuestions.value.reduce((sum, q) => {
    if (q.question_number >= from && q.question_number <= to) return sum;
    return sum + (Number(q.score_weight) || 0);
  }, 0);
  const neededForBatch = 100 - outsideScoreSum;
  if (neededForBatch > 0) {
    const newBatchWeight = Math.round((neededForBatch / countAffected) * 10) / 10;
    if (newBatchWeight > 0 && newBatchWeight !== newWeight) {
      return {
        type: 'batch',
        label: `Bobot Rentang No. ${from}–${to}`,
        description: `Ubah bobot rentang ini menjadi ${newBatchWeight} poin per butir agar total seluruh paket pas 100 poin.`,
        targetWeight: newBatchWeight,
        buttonText: `🎯 Set Bobot Rentang Ini Jadi ${newBatchWeight} Poin`
      };
    }
  }

  return null;
});

const applyRebalanceSuggestion = () => {
  if (!rebalanceSuggestion.value) return;
  if (rebalanceSuggestion.value.type === 'batch') {
    batchForm.scoreWeight = rebalanceSuggestion.value.targetWeight;
    activeRebalance.value = null;
  } else {
    activeRebalance.value = { ...rebalanceSuggestion.value };
  }
};

const openBatchTypeModal = () => {
  activeRebalance.value = null;
  const currNum = activeQuestions.value[currentQuestionIndex.value]?.question_number || 1;
  if (currNum >= 41) {
    batchForm.fromNumber = 41;
    batchForm.toNumber = Math.min(45, activeQuestions.value.length);
    batchForm.targetType = 'essay';
    batchForm.scoreWeight = 5.0;
  } else if (currNum >= 36) {
    batchForm.fromNumber = 36;
    batchForm.toNumber = Math.min(40, activeQuestions.value.length);
    batchForm.targetType = 'matching';
    batchForm.scoreWeight = 3.0;
  } else if (currNum >= 31) {
    batchForm.fromNumber = 31;
    batchForm.toNumber = Math.min(35, activeQuestions.value.length);
    batchForm.targetType = 'true_false';
    batchForm.scoreWeight = 2.0;
  } else if (currNum >= 21) {
    batchForm.fromNumber = 21;
    batchForm.toNumber = Math.min(30, activeQuestions.value.length);
    batchForm.targetType = 'pg_complex';
    batchForm.scoreWeight = 3.0;
  } else {
    batchForm.fromNumber = 1;
    batchForm.toNumber = Math.min(20, activeQuestions.value.length);
    batchForm.targetType = 'pg';
    batchForm.scoreWeight = 1.0;
  }
  showBatchModal.value = true;
};

const applyBatchPreset = (from, to, type, weight = 1.0) => {
  activeRebalance.value = null;
  batchForm.fromNumber = from;
  batchForm.toNumber = Math.min(to, activeQuestions.value.length);
  batchForm.targetType = type;
  batchForm.scoreWeight = weight;
};

const applyFullMtsStandardPreset = async () => {
  const isConfirmed = await confirm({
    title: 'Terapkan Format Standar MTs',
    message: 'Terapkan format standar resmi MTs Al-Hasanah ke seluruh butir soal paket ini?\n\n• No 1–20   : PG Tunggal (bobot 1 poin)\n• No 21–30 : PG Kompleks (bobot 3 poin)\n• No 31–35 : Benar / Salah (bobot 2 poin)\n• No 36–40 : Menjodohkan (bobot 3 poin)\n• No 41–45 : Uraian / Essay (bobot 5 poin)\n\nTotal Maksimal: 100 Poin Pas.',
    type: 'info',
    confirmText: 'Ya, Terapkan',
    cancelText: 'Batal'
  });
  if (!isConfirmed) return;

  activeQuestions.value.forEach(q => {
    const num = q.question_number;
    if (num <= 20) {
      q.question_type = 'pg';
      q.score_weight = 1.0;
      ensureOptions(q);
    } else if (num <= 30) {
      q.question_type = 'pg_complex';
      q.score_weight = 3.0;
      ensureOptions(q);
    } else if (num <= 35) {
      q.question_type = 'true_false';
      q.score_weight = 2.0;
      q.options = { mode: 'single', answer: q.correct_answer || 'B' };
      if (!q.correct_answer || (q.correct_answer !== 'B' && q.correct_answer !== 'S')) {
        q.correct_answer = 'B';
      }
    } else if (num <= 40) {
      q.question_type = 'matching';
      q.score_weight = 3.0;
      const single = ensureSingleMatching(q);
      q.options = {
        mode: 'single',
        right_text: single.right_text || '',
        key: single.key || 'A'
      };
      if (!q.correct_answer) {
        q.correct_answer = q.options.key || 'A';
      }
    } else if (num <= 45) {
      q.question_type = 'essay';
      q.score_weight = 5.0;
    }
  });

  showBatchModal.value = false;
  toast.success('Berhasil! Format standar resmi MTs Al-Hasanah (Total 100 Poin) telah diterapkan.');
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
    toast.error('Nomor awal tidak boleh lebih besar dari nomor akhir!');
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

  // Apply auto-rebalance if active
  let rebalanceMsg = '';
  if (activeRebalance.value && activeRebalance.value.type === 'essay') {
    activeQuestions.value.forEach(q => {
      if (activeRebalance.value.affectedNumbers?.includes(q.question_number)) {
        q.score_weight = Number(activeRebalance.value.targetWeight);
      }
    });
    rebalanceMsg = ` • Bobot Essay disesuaikan ke ${activeRebalance.value.targetWeight} poin agar pas 100 poin.`;
  }

  // Switch to first affected question
  const targetIdx = activeQuestions.value.findIndex(q => q.question_number === from);
  if (targetIdx !== -1) {
    currentQuestionIndex.value = targetIdx;
  }

  showBatchModal.value = false;
  toast.success(`Berhasil! ${count} butir soal (No ${from} s/d ${to}) telah diubah menjadi tipe "${getQuestionTypeLabel(batchForm.targetType)}"${rebalanceMsg}`);
  activeRebalance.value = null;
};

const removeQuestion = async (idx) => {
  if (activeQuestions.value.length <= 1) {
    toast.warning('Paket soal harus memiliki minimal 1 butir soal.');
    return;
  }
  const qNum = activeQuestions.value[idx].question_number;
  const isConfirmed = await confirm({
    title: 'Hapus Butir Soal',
    message: `Hapus butir soal nomor ${qNum}? Nomor soal setelahnya akan otomatis diurutkan ulang.`,
    type: 'danger',
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal'
  });
  if (!isConfirmed) return;
  activeQuestions.value.splice(idx, 1);
  activeQuestions.value.forEach((q, i) => {
    q.question_number = i + 1;
  });
  if (currentQuestionIndex.value >= activeQuestions.value.length) {
    currentQuestionIndex.value = activeQuestions.value.length - 1;
  }
  toast.success(`Butir soal nomor ${qNum} berhasil dihapus.`);
};

const getPrintUrl = (pkgId, type, params = {}) => {
  const token = localStorage.getItem('token') || '';
  const searchParams = new URLSearchParams(params);
  if (token) searchParams.set('token', token);
  const qs = searchParams.toString();
  return `/prints/exam/${pkgId}/${type}${qs ? `?${qs}` : ''}`;
};

const previewNaskah = () => {
  if (!activePackage.value) return;
  window.open(getPrintUrl(activePackage.value.id, 'naskah', { layout: '2col' }), '_blank');
};

const previewKisiKisi = () => {
  if (!activePackage.value) return;
  window.open(getPrintUrl(activePackage.value.id, 'kisi-kisi'), '_blank');
};

const previewTelaah = () => {
  if (!activePackage.value) return;
  window.open(getPrintUrl(activePackage.value.id, 'telaah'), '_blank');
};

const previewKunci = () => {
  if (!activePackage.value) return;
  window.open(getPrintUrl(activePackage.value.id, 'kunci'), '_blank');
};

// Smart Auto-Generate Kisi-Kisi Engine (Multi-Subject, Arabic/Quran/Hadith Aware & Natural NLP)
const analyzeQuestionContent = (q, prevQ = null) => {
  const rawOriginal = (q.question_text || '').trim();
  const cleanHtml = rawOriginal
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&quot;/g, '"')
    .replace(/&amp;/g, '&')
    .replace(/\s+/g, ' ')
    .trim();
  const lowerText = cleanHtml.toLowerCase();
  const currentSubject = (activePackage.value?.subject?.name || '').trim();
  const currentSubjectLower = currentSubject.toLowerCase();

  // Helper: Format string to Title Case
  const toTitleCase = (str) => {
    if (!str) return '';
    return str
      .toLowerCase()
      .split(/\s+/)
      .map(w => w.charAt(0).toUpperCase() + w.slice(1))
      .join(' ');
  };

  // 0A. DETEKSI TEKS & TULISAN ARAB (Unicode Arabic: \u0600-\u06FF, dll)
  const arabicRegex = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;
  const hasArabic = arabicRegex.test(rawOriginal);
  let arabicSnippet = '';
  if (hasArabic) {
    const arabicClauses = cleanHtml.match(/[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF\s،؛ـ\.]+/g);
    if (arabicClauses && arabicClauses.length > 0) {
      const validArabic = arabicClauses.map(w => w.trim()).filter(w => w.length >= 3);
      if (validArabic.length > 0) {
        let firstArab = validArabic[0];
        if (firstArab.length > 55) {
          firstArab = firstArab.substring(0, 52).trim() + '...';
        }
        arabicSnippet = firstArab;
      }
    }
  }

  // 0B. DETEKSI SITASI AL-QUR'AN (Nama Surah & Nomor Ayat)
  let quranCitation = null;
  const knownSurahs = 'al-fatihah|al-baqarah|ali [\'`]?imran|an-nisa|al-ma\'?idah|al-an\'?am|al-a\'?raf|al-anfal|at-taubah|yunus|hud|yusuf|ar-ra\'?d|ibrahim|al-hijr|an-nahl|al-isra|al-kahf|al-kahfi|maryam|thaha|ta ha|al-anbiya|al-hajj|al-mu\'?minun|an-nur|al-furqan|asy-syu\'?ara|an-naml|al-qashash|al-ankabut|ar-rum|luqman|as-sajdah|al-ahzab|saba|fatir|yasin|ya sin|ash-shaffat|shad|az-zumar|ghafir|fushshilat|asy-syura|az-zukhruf|ad-dukhan|al-jatsiyah|al-ahqaf|muhammad|al-fath|al-hujurat|qaf|adz-dzariyat|ath-thur|an-najm|al-qamar|ar-rahman|al-waqi\'?ah|al-hadid|al-mujadilah|al-hasyr|al-mumtahanah|ash-shaff|al-jumu\'?ah|al-munafiqun|at-taghabun|ath-thalaq|at-tahrim|al-mulk|al-qalam|al-haqqah|al-ma\'?arij|nuh|al-jinn|al-muzzammil|al-muddatstsir|al-qiyamah|al-insan|al-mursalat|an-naba|an-nazi\'?at|abasa|\'abasa|at-takwir|al-infithar|al-muthaffifin|al-insyiqaq|al-buruj|ath-thariq|al-a\'?la|al-ghasyiyah|al-fajr|al-balad|asy-syams|al-lail|adh-dhuha|ad-dhuha|al-insyirah|asy-syarh|at-tin|al-\'?alaq|al-qadr|al-bayyinah|az-zalzalah|al-\'?adiyat|al-qari\'?ah|at-takatsur|al-\'?ashr|al-humazah|al-fil|quraisy|al-ma\'?un|al-kautsar|al-kafirun|an-nashr|al-lahab|al-ikhlas|al-falaq|an-nas';
  
  const surahSpecificMatch = cleanHtml.match(new RegExp('(?:q\\.?s\\.?|surat|surah)?\\s*(' + knownSurahs + ')(?:\\s*(?:ayat|ay\\.|:|\\[?\\d+\\]?:?)\\s*(\\d+(?:\\s*[-–]\\s*\\d+)?))?', 'i'));
  const surahGenericMatch = cleanHtml.match(/(?:q\.?s\.?|surat|surah)\s+([a-zA-Z\s'\-]{3,25})(?:(?:\s*(?:ayat|ay\.|:)\s*|\s*\[?\d+\]?\s*:\s*)(\d+(?:\s*[-–]\s*\d+)?))?/i);

  if (surahSpecificMatch && surahSpecificMatch[1]) {
    const sName = toTitleCase(surahSpecificMatch[1].trim());
    const aNum = surahSpecificMatch[2] ? surahSpecificMatch[2].trim() : '';
    quranCitation = {
      surah: sName,
      ayat: aNum,
      fullRef: aNum ? `Q.S. ${sName} ayat ${aNum}` : `Q.S. ${sName}`
    };
  } else if (surahGenericMatch && surahGenericMatch[1]) {
    const rawS = surahGenericMatch[1].trim().replace(/\s+(ayat|adalah|berikut|menjelaskan).*$/i, '');
    const aNum = surahGenericMatch[2] ? surahGenericMatch[2].trim() : '';
    if (rawS && rawS.length >= 3) {
      const sName = toTitleCase(rawS);
      quranCitation = {
        surah: sName,
        ayat: aNum,
        fullRef: aNum ? `Q.S. ${sName} ayat ${aNum}` : `Q.S. ${sName}`
      };
    }
  }

  // 0C. DETEKSI SITASI HADITS
  let hadithCitation = null;
  const hadithMatch = cleanHtml.match(/(?:h\.?r\.?|hadits\s+riwayat|riwayat\s+hadits|hadis\s+riwayat)\s+([a-zA-Z\s,dan&]{3,35})/i);
  if (hadithMatch && hadithMatch[1]) {
    const rawPerawi = hadithMatch[1].trim().replace(/\s+(tentang|adalah|yang|bahwa|mengenai).*$/i, '');
    if (rawPerawi && rawPerawi.length >= 3) {
      hadithCitation = `H.R. ${toTitleCase(rawPerawi)}`;
    }
  }

  // Helper: Heuristic extraction of key subject matter directly from sentence (Bebas CP, Murni dari Soal Guru)
  const extractHeuristicSubject = (rawText, lower) => {
    if (!rawText || rawText.length < 5) return null;

    // Pola 1: Istilah dalam tanda kutip (misal "ikhtiar", 'الصَّبْرِ', «Al-Ghaffar»)
    const quoteMatch = rawText.match(/["'«“]([^"'»”]{3,45})["'»”]/);
    if (quoteMatch && quoteMatch[1] && !quoteMatch[1].match(/^(a|b|c|d|e|benar|salah)$/i)) {
      return toTitleCase(quoteMatch[1].trim());
    }

    // Pola 2: Target spesifik (fungsi/tujuan/hikmah/manfaat/rukun/syarat/pengertian/arti/definisi/hukum/ketentuan [dari/tentang] ...)
    const specificTargetMatch = rawText.match(/(?:fungsi|tujuan|manfaat|dampak|akibat|keutamaan|hikmah|ciri-ciri|ciri|karakteristik|rukun|syarat|pengertian|arti|definisi|hukum|ketentuan|tata\s+cara|larangan|bahaya)\s+(?:positif\s+|negatif\s+)?(?:yang\s+dirasakan\s+[a-zA-Z0-9'\s]+\s+)?(?:dari\s+|tentang\s+)?(?:perilaku\s+|sikap\s+|sifat\s+)?([a-zA-Z0-9\u0600-\u06FF\s\-_',]{3,45}?)(?:\s+(?:adalah|yaitu|merupakan|dalam|pada|di|[?:.;])|$)/i);
    if (specificTargetMatch && specificTargetMatch[1]) {
      let cand = specificTargetMatch[1].trim();
      cand = cand.replace(/(?:nya|pun)$/i, '').trim();
      if (cand.length >= 3 && !cand.match(/^(berikut|yang|bawah|atas|ini|itu)$/i)) {
        return toTitleCase(cand);
      }
    }

    // Pola 3: "tentang / mengenai / berkaitan dengan ..."
    const topicPatternMatch = rawText.match(/(?:tentang|mengenai|berkaitan\s+dengan|terkait)\s+([a-zA-Z0-9\u0600-\u06FF\s\-_',]{3,45}?)(?:\s+(?:adalah|yaitu|merupakan|dalam|pada|[?:.;])|$)/i);
    if (topicPatternMatch && topicPatternMatch[1]) {
      let cand = topicPatternMatch[1].trim();
      cand = cand.replace(/(?:nya|pun)$/i, '').trim();
      if (cand.length >= 3 && !cand.match(/^(hal|materi|soal|pernyataan)$/i)) {
        return toTitleCase(cand);
      }
    }

    // Pola 4: "sikap / perilaku / sifat / perbuatan ..."
    const akhlakMatch = rawText.match(/(?:sikap|perilaku|akhlak|perbuatan|sifat)\s+([a-zA-Z0-9\u0600-\u06FF\s\-_',]{3,35}?)(?:\s+(?:adalah|yaitu|merupakan|pada|dalam|[?:.;])|$)/i);
    if (akhlakMatch && akhlakMatch[1]) {
      let cand = akhlakMatch[1].trim();
      cand = cand.replace(/(?:nya|pun)$/i, '').trim();
      if (cand.length >= 3 && !cand.match(/^(tersebut|ini|itu|yang|mereka|dia|kita)$/i)) {
        return 'Perilaku ' + toTitleCase(cand);
      }
    }

    // Pola 5: Bersihkan kata-kata klise pembuka soal ujian
    let stripped = rawText
      .replace(/^(berikut\s+ini\s+yang\s+(bukan\s+)?merupakan|berikut\s+ini\s+yang\s+(bukan\s+)?termasuk|di\s+bawah\s+ini\s+yang\s+(bukan\s+)?termasuk|manakah\s+pernyataan\s+yang\s+tepat\s+mengenai|manakah\s+yang\s+(bukan\s+)?merupakan|perhatikan\s+(beberapa\s+)?(pernyataan|kutipan|ayat|hadits|tabel|gambar|data|kasus|contoh)\s+berikut(\s+ini)?[\s:,\.\-]*|salah\s+satu\s+(contoh|ciri|karakteristik|faktor|dampak|hikmah|keutamaan|tujuan|fungsi|bentuk)\s+(dari\s+)?|sikap\s+yang\s+mencerminkan|perilaku\s+yang\s+menunjukkan)\s*/i, '')
      .trim();

    // Ambil klausa inti kalimat sebelum kata penghubung (mendukung karakter Latin dan Arab \u0600-\u06FF)
    const clauseMatch = stripped.match(/^([a-zA-Z0-9\u0600-\u06FF\s\-_',]{4,55}?)(?:\s+(?:adalah|yaitu|merupakan|dinamakan|disebut|ialah|sebagai)|[?:.;]|$)/i);
    if (clauseMatch && clauseMatch[1]) {
      let core = clauseMatch[1].replace(/^(yang|pada|dalam|tentang|dari|untuk|dengan|bahwa)\s+/i, '').trim();
      const words = core.split(/\s+/).filter(w => w.length > 2);
      if (words.length >= 1 && words.length <= 6) {
        return toTitleCase(core);
      }
    }
    return null;
  };

  // 1. Determine Topic / Material
  let topic = q.material || '';
  let detectedDomain = 'umum';

  // Deteksi Domain Mapel
  if (currentSubjectLower.includes('akidah') || currentSubjectLower.includes('aqidah')) detectedDomain = 'akidah';
  else if (currentSubjectLower.includes('akhlak')) detectedDomain = 'akhlak';
  else if (currentSubjectLower.includes('fikih') || currentSubjectLower.includes('fiqih')) detectedDomain = 'fikih';
  else if (currentSubjectLower.includes('qur') || currentSubjectLower.includes('hadits') || currentSubjectLower.includes('hadis')) detectedDomain = 'qurdis';
  else if (currentSubjectLower.includes('sejarah') || currentSubjectLower.includes('ski')) detectedDomain = 'ski';
  else if (currentSubjectLower.includes('arab')) detectedDomain = 'bahasa_arab';
  else if (currentSubjectLower.includes('indonesia') || currentSubjectLower.includes('inggris')) detectedDomain = 'bahasa';
  else if (currentSubjectLower.includes('matematika')) detectedDomain = 'matematika';
  else if (currentSubjectLower.includes('ipa') || currentSubjectLower.includes('sains')) detectedDomain = 'sains';
  else if (currentSubjectLower.includes('komputer') || currentSubjectLower.includes('informatika')) detectedDomain = 'komputer';
  else if (currentSubjectLower.includes('pkn') || currentSubjectLower.includes('pancasila')) detectedDomain = 'sosial';

  if (!topic) {
    // 1A. JIKA ADA SITASI AL-QUR'AN
    if (quranCitation) {
      const extractedSub = extractHeuristicSubject(cleanHtml, lowerText);
      if (extractedSub && !extractedSub.toLowerCase().includes(quranCitation.surah.toLowerCase())) {
        topic = `${quranCitation.fullRef} (${extractedSub})`;
      } else {
        topic = quranCitation.fullRef;
      }
      detectedDomain = 'qurdis';
    // 1B. JIKA ADA SITASI HADITS
    } else if (hadithCitation) {
      const extractedSub = extractHeuristicSubject(cleanHtml, lowerText);
      if (extractedSub && !extractedSub.toLowerCase().includes('hadits')) {
        topic = `${hadithCitation} (${extractedSub})`;
      } else {
        topic = hadithCitation;
      }
      detectedDomain = 'qurdis';
    // 1C. JIKA MAPEL BAHASA ARAB ATAU MENGANDUNG HURUF ARAB
    } else if (detectedDomain === 'bahasa_arab' || (hasArabic && (currentSubjectLower.includes('pai') || currentSubjectLower.includes('agama') || currentSubjectLower.includes('arab')))) {
      if (lowerText.match(/arti|makna|terjemahan|artinya/)) {
        topic = arabicSnippet ? `Mufradat: ${arabicSnippet}` : 'Mufradat (Kosakata) Bahasa Arab';
      } else if (lowerText.match(/tarkib|kaidah|dhamir|fi'il|isim|mubtada|khabar|jar|huruf/)) {
        topic = 'Kaidah Bahasa Arab (Tarkib)';
      } else if (arabicSnippet) {
        topic = `Teks Arab: ${arabicSnippet}`;
      } else {
        topic = 'Ungkapan dan Kalimat Bahasa Arab';
      }
      detectedDomain = 'bahasa_arab';
    }
  }

  // --- KAMUS MATERI & ATURAN BERDASARKAN DOMAIN MAPEL ---
  const matchAkidah = () => {
    if (lowerText.match(/al-ghaffar|al ghaffar|al-ghofur|maha pengampun/)) return 'Asmaul Husna: Al-Ghaffar';
    if (lowerText.match(/al-'afuw|al afuw|al-afuww|maha pemaaf/)) return 'Asmaul Husna: Al-\'Afuw';
    if (lowerText.match(/al-basith|al basith|maha melapangkan/)) return 'Asmaul Husna: Al-Basith';
    if (lowerText.match(/an-nafi|an nafi|maha pemberi manfaat/)) return 'Asmaul Husna: An-Nafi\'';
    if (lowerText.match(/ar-ra'uf|ar rauf|ar-rauf|maha pengasih/)) return 'Asmaul Husna: Ar-Ra\'uf';
    if (lowerText.match(/al-barr|al barr|maha penderma/)) return 'Asmaul Husna: Al-Barr';
    if (lowerText.match(/al-hakim|al hakim|maha bijaksana/)) return 'Asmaul Husna: Al-Hakim';
    if (lowerText.match(/al-fattah|al fattah|maha pembuka rahmat/)) return 'Asmaul Husna: Al-Fattah';
    if (lowerText.match(/al-adl|al adl|al-'adl|maha adil/)) return 'Asmaul Husna: Al-\'Adl';
    if (lowerText.match(/al-hayyu|al-qayyum|al qayyum|al hayyu/)) return 'Asmaul Husna: Al-Hayyu dan Al-Qayyum';
    if (lowerText.match(/asmaul husna|nama-nama baik allah/)) return 'Memahami Asmaul Husna';
    if (lowerText.match(/sifat wajib|sifat mustahil|sifat jaiz|wujud|qidam|baqa|mukhalafatu|qiyamuhu|wahdaniyah|qudrat|iradat|sama'|bashar|kalam/)) return 'Sifat-Sifat Allah SWT';
    if (lowerText.match(/malaikat|jibril|mikail|israfil|izrail|munkar|nakir|raqib|atid|malik|ridwan/)) return 'Iman kepada Malaikat Allah SWT';
    if (lowerText.match(/kitab|taurat|zabur|injil|suhuf|al-qur'an/)) return 'Iman kepada Kitab-Kitab Allah SWT';
    if (lowerText.match(/ulul azmi|mukjizat|karamah|karomah|ma'unah|maunah|irhas/)) return 'Mukjizat dan Keteladanan Rasul Ulul Azmi';
    if (lowerText.match(/hari kiamat|hari akhir|kiamat sugra|kiamat kubra|yaumul ba'ats|yaumul hisab|yaumul mizan|yaumul jaza|barzakh|tanda-tanda kiamat/)) return 'Iman kepada Hari Akhir (Kiamat)';
    if (lowerText.match(/qada|qadar|takdir mubram|takdir muallaq/)) return 'Iman kepada Qada dan Qadar (Takdir)';
    if (lowerText.match(/rukun iman|rukun islam|ihsan/)) return 'Hakikat Iman, Islam, dan Ihsan';
    return null;
  };

  const matchAkhlak = () => {
    // 1. Akhlak Terpuji kepada Diri Sendiri (Inovatif, Kreatif, Kerja Keras, Produktif, Mandiri, Pantang Menyerah)
    if (lowerText.match(/inovatif|kreatif|kerja keras|produktif|mandiri|pantang menyerah|kegigihan/)) {
      if (lowerText.match(/inovatif/)) return 'Akhlak Terpuji: Perilaku Inovatif';
      if (lowerText.match(/kreatif/)) return 'Akhlak Terpuji: Perilaku Kreatif';
      if (lowerText.match(/kerja keras/)) return 'Akhlak Terpuji: Kerja Keras';
      if (lowerText.match(/produktif/)) return 'Akhlak Terpuji: Perilaku Produktif';
      if (lowerText.match(/mandiri/)) return 'Akhlak Terpuji: Sikap Mandiri';
      return 'Akhlak Terpuji (Kreatif, Inovatif, Kerja Keras, Produktif)';
    }

    // 2. Akhlak Terpuji Utama Lainnya
    if (lowerText.match(/tawakal|berserah diri kepada allah/)) return 'Sifat Tawakal (Akhlak Terpuji)';
    if (lowerText.match(/ikhtiar|berusaha sungguh-sungguh/)) return 'Sifat Ikhtiar (Akhlak Terpuji)';
    if (lowerText.match(/sabar|kesabaran/)) return 'Sifat Sabar (Akhlak Terpuji)';
    if (lowerText.match(/syukur|bersyukur|nikmat allah/)) return 'Sifat Syukur (Akhlak Terpuji)';
    if (lowerText.match(/qana'ah|qanaah|merasa cukup/)) return 'Sifat Qana\'ah (Akhlak Terpuji)';
    if (lowerText.match(/husnudzan|berprasangka baik/)) return 'Perilaku Husnudzan (Prasangka Baik)';
    if (lowerText.match(/tawadhu|rendah hati/)) return 'Sikap Tawadhu (Rendah Hati)';
    if (lowerText.match(/tasamuh|toleransi/)) return 'Sikap Tasamuh (Toleransi)';
    if (lowerText.match(/ta'awun|tolong menolong/)) return 'Sikap Ta\'awun (Tolong Menolong)';
    if (lowerText.match(/istiqamah|konsisten/)) return 'Sifat Istiqamah';
    if (lowerText.match(/jujur|kejujuran|siddiq|amanah|menepati janji/)) return 'Sifat Jujur dan Amanah';
    if (lowerText.match(/rasa malu|haya'/)) return 'Sifat Rasa Malu (Haya\') dalam Islam';
    if (lowerText.match(/pemaaf|memaafkan/)) return 'Sikap Pemaaf';

    // 3. Adab dan Hubungan Sosial
    if (lowerText.match(/birrul walidain|orang tua|berbakti kepada guru|menghormati guru/)) return 'Berbakti kepada Orang Tua dan Guru';
    if (lowerText.match(/adab bergaul|adab berteman|adab media sosial|adab berpakaian|adab makan|adab berjalan/)) return 'Adab Islam dalam Kehidupan Sehari-hari';

    // 4. Menghindari Akhlak Tercela (Mazmumah)
    if (lowerText.match(/ananiyah|egois|mementingkan diri/)) return 'Menghindari Perilaku Ananiyah (Egois)';
    if (lowerText.match(/putus asa|pesimis/)) return 'Menghindari Perilaku Putus Asa';
    if (lowerText.match(/gadab|marah|pemarah/)) return 'Menghindari Sifat Gadab (Pemarah)';
    if (lowerText.match(/tamak|serakah/)) return 'Menghindari Sifat Tamak (Serakah)';
    if (lowerText.match(/riya|pamer|sum'ah/)) return 'Menghindari Perilaku Riya\' dan Sum\'ah';
    if (lowerText.match(/nifaq|munafik|ciri-ciri orang munafik/)) return 'Menghindari Sifat Nifaq (Munafik)';
    if (lowerText.match(/hasad|dengki|iri hati/)) return 'Menghindari Perilaku Hasad (Dengki)';
    if (lowerText.match(/gibah|menggunjing/)) return 'Larangan Perilaku Gibah (Menggunjing)';
    if (lowerText.match(/fitnah|menuduh/)) return 'Bahaya Perilaku Fitnah';
    if (lowerText.match(/namimah|adu domba/)) return 'Larangan Perilaku Namimah (Adu Domba)';
    if (lowerText.match(/israf|tabdzir|boros|pemborosan/)) return 'Larangan Israf dan Tabdzir';
    if (lowerText.match(/bakhil|kikir|pelit/)) return 'Menghindari Sifat Bakhil dan Kikir';

    // 5. Keteladanan Tokoh dan Sahabat
    if (lowerText.match(/abu bakar/)) return 'Keteladanan Sahabat Abu Bakar Ash-Shiddiq';
    if (lowerText.match(/umar bin khattab/)) return 'Keteladanan Sahabat Umar bin Khattab';
    if (lowerText.match(/utsman bin affan/)) return 'Keteladanan Sahabat Utsman bin Affan';
    if (lowerText.match(/ali bin abi thalib/)) return 'Keteladanan Sahabat Ali bin Abi Thalib';
    if (lowerText.match(/bilal bin rabah/)) return 'Keteladanan Sahabat Bilal bin Rabah';
    if (lowerText.match(/luqman/)) return 'Keteladanan dan Nasihat Luqman Al-Hakim';
    if (lowerText.match(/ashabul kahfi/)) return 'Kisah Keteladanan Ashabul Kahfi';

    return null;
  };

  const matchFikih = () => {
    if (lowerText.match(/thaharah|bersuci|hadas|wudhu|tayamum|mandi wajib|najis/)) return 'Ketentuan Thaharah dan Bersuci';
    if (lowerText.match(/shalat fardhu|shalat wajib|rukun shalat|syarat shalat|batal shalat/)) return 'Ketentuan Shalat Fardhu';
    if (lowerText.match(/shalat berjamaah|makmum masbuq|imam shalat/)) return 'Ketentuan Shalat Berjamaah';
    if (lowerText.match(/shalat sunnah|tahajud|dhuha|witir|tarawih|rawatib|istikharah/)) return 'Ketentuan Shalat Sunnah';
    if (lowerText.match(/sujud sahwi|sujud tilawah|sujud syukur/)) return 'Sujud Sahwi, Tilawah, dan Syukur';
    if (lowerText.match(/shalat jum'at|khutbah jumat|shalat jumat/)) return 'Ketentuan Shalat Jum\'at';
    if (lowerText.match(/shalat jamak|shalat qashar|jamak takdim|jamak takhir/)) return 'Shalat Jamak dan Qashar';
    if (lowerText.match(/shalat jenazah|memandikan jenazah|mengafani|menguburkan/)) return 'Pengurusan dan Shalat Jenazah';
    if (lowerText.match(/zakat fitrah|zakat mal|nisab|haul|mustahiq/)) return 'Ketentuan Zakat dalam Islam';
    if (lowerText.match(/puasa ramadhan|puasa sunnah|syarat puasa|rukun puasa|kaffarah|fidyah/)) return 'Ketentuan Ibadah Puasa';
    if (lowerText.match(/haji|umrah|miqat|thawaf|tawaf|sa'i|wukuf|tahallul/)) return 'Ibadah Haji dan Umrah';
    if (lowerText.match(/makanan halal|minuman halal|makanan haram|bangkai|daging babi/)) return 'Makanan dan Minuman Halal & Haram';
    if (lowerText.match(/qurban|kurban|aqiqah|penyembelihan hewan/)) return 'Penyembelihan Hewan, Qurban, dan Aqiqah';
    if (lowerText.match(/jual beli|khiyar|riba|pinjam meminjam|sewa menyewa|ariyah|qardh|ijarah/)) return 'Ketentuan Muamalah Islam';
    return null;
  };

  const matchQurdis = () => {
    if (lowerText.match(/nun sukun|tanwin|idzhar|idgham|iqlab|ikhfa/)) return 'Hukum Tajwid: Nun Sukun dan Tanwin';
    if (lowerText.match(/mim sukun|ikhfa syafawi|idgham mimi|idzhar syafawi/)) return 'Hukum Tajwid: Mim Sukun';
    if (lowerText.match(/al-syamsiyah|al-qamariyah/)) return 'Hukum Bacaan Al-Syamsiyah dan Al-Qamariyah';
    if (lowerText.match(/qalqalah|sugra|kubra/)) return 'Hukum Bacaan Qalqalah';
    if (lowerText.match(/hukum mad|mad thabi'i|mad wajib|mad jaiz|mad aridh/)) return 'Hukum Bacaan Mad';
    if (lowerText.match(/menuntut ilmu/)) return 'Hadits tentang Kewajiban Menuntut Ilmu';
    if (lowerText.match(/silaturahmi/)) return 'Hadits tentang Menjaga Silaturahmi';
    if (lowerText.match(/menyayangi anak yatim/)) return 'Hadits tentang Menyayangi Anak Yatim';
    return null;
  };

  const matchSKI = () => {
    if (lowerText.match(/khulafaur rasyidin/)) return 'Kepemimpinan Khulafaur Rasyidin';
    if (lowerText.match(/daulah umayyah|damaskus|muawiyah/)) return 'Perkembangan Daulah Umayyah';
    if (lowerText.match(/daulah abbasiyah|baghdad|harun al-rasyid|baitul hikmah/)) return 'Perkembangan Daulah Abbasiyah';
    if (lowerText.match(/daulah ayyubiyah|shalahuddin/)) return 'Perkembangan Daulah Ayyubiyah';
    if (lowerText.match(/walisongo|sunan/)) return 'Peran Walisongo dalam Dakwah Islam';
    if (lowerText.match(/kerajaan islam|samudera pasai|demak|mataram islam|banten|ternate/)) return 'Kerajaan-Kerajaan Islam di Nusantara';
    return null;
  };

  const matchBahasaArab = () => {
    if (lowerText.match(/arti|makna|terjemahan|artinya/)) return arabicSnippet ? `Mufradat: ${arabicSnippet}` : 'Mufradat (Kosakata) Bahasa Arab';
    if (lowerText.match(/tarkib|kaidah|dhamir|fi'il|isim|mubtada|khabar|jar|huruf/)) return 'Kaidah Bahasa Arab (Tarkib)';
    if (arabicSnippet) return `Teks Arab: ${arabicSnippet}`;
    return 'Ungkapan dan Kalimat Bahasa Arab';
  };

  const matchMatematika = () => {
    if (lowerText.match(/pola bilangan|barisan aritmatika|deret/)) return 'Pola Bilangan dan Barisan';
    if (lowerText.match(/koordinat kartesius|sumbu x|sumbu y|kuadran/)) return 'Sistem Koordinat Kartesius';
    if (lowerText.match(/relasi dan fungsi|domain|kodomain|range/)) return 'Relasi dan Fungsi';
    if (lowerText.match(/persamaan garis lurus|gradien/)) return 'Persamaan Garis Lurus (Gradien)';
    if (lowerText.match(/spldv|persamaan linier dua variabel/)) return 'Sistem Persamaan Linier Dua Variabel (SPLDV)';
    if (lowerText.match(/teorema pythagoras|sisi miring|hipotenusa|tripel pythagoras/)) return 'Teorema Pythagoras';
    if (lowerText.match(/lingkaran|jari-jari|diameter|juring|tembereng|sudut pusat/)) return 'Unsur dan Perhitungan Lingkaran';
    if (lowerText.match(/bangun ruang|kubus|balok|prisma|limas|luas permukaan|volume bangun/)) return 'Bangun Ruang Sisi Datar';
    if (lowerText.match(/statistika|mean|median|modus|rata-rata|diagram/)) return 'Statistika dan Pengolahan Data';
    if (lowerText.match(/peluang|ruang sampel|titik sampel/)) return 'Teori Peluang';
    if (lowerText.match(/bilangan berpangkat|bentuk akar|eksponen/)) return 'Bilangan Berpangkat dan Bentuk Akar';
    if (lowerText.match(/persamaan kuadrat|diskriminan/)) return 'Persamaan dan Fungsi Kuadrat';
    if (lowerText.match(/transformasi geometri|translasi|refleksi|rotasi|dilatasi/)) return 'Transformasi Geometri';
    if (lowerText.match(/kesebangunan|kekongruenan|sebangun|kongruen/)) return 'Kesebangunan dan Kekongruenan';
    return null;
  };

  const matchSains = () => {
    if (lowerText.match(/fotosintesis|klorofil|reaksi terang|glukosa/)) return 'Proses Fotosintesis Tumbuhan';
    if (lowerText.match(/sistem gerak|tulang|sendi|otot|rangka/)) return 'Sistem Gerak pada Manusia';
    if (lowerText.match(/sistem pencernaan|enzim|lambung|usus halus/)) return 'Sistem Pencernaan Manusia';
    if (lowerText.match(/sistem peredaran darah|jantung|arteri|vena|sel darah/)) return 'Sistem Peredaran Darah Manusia';
    if (lowerText.match(/sistem pernapasan|alveolus|paru-paru|diafragma/)) return 'Sistem Pernapasan Manusia';
    if (lowerText.match(/sistem ekskresi|ginjal|nefron|urine|kulit/)) return 'Sistem Ekskresi Manusia';
    if (lowerText.match(/hukum newton|kelembaman|aksi reaksi|gaya gesek/)) return 'Gaya dan Hukum Gerak Newton';
    if (lowerText.match(/usaha dan pesawat sederhana|tuas|katrol|bidang miring/)) return 'Usaha dan Pesawat Sederhana';
    if (lowerText.match(/tekanan hidrostatis|hukum pascal|archimedes|terapung/)) return 'Tekanan Zat dan Penerapannya';
    if (lowerText.match(/getaran|gelombang|bunyi|frekuensi|amplitudo/)) return 'Getaran, Gelombang, dan Bunyi';
    if (lowerText.match(/cahaya|cermin|lensa|alat optik|mikroskop/)) return 'Cahaya dan Alat Optik';
    if (lowerText.match(/listrik statis|listrik dinamis|muatan|arus listrik|ohm/)) return 'Listrik Statis dan Dinamis';
    if (lowerText.match(/kemagnetan|induksi elektromagnetik|transformator/)) return 'Kemagnetan dan Induksi Elektromagnetik';
    if (lowerText.match(/tata surya|planet|rotasi|revolusi|gerhana/)) return 'Sistem Tata Surya';
    if (lowerText.match(/pewarisan sifat|gen|kromosom|monohibrid|dihibrid|mendel/)) return 'Pewarisan Sifat (Genetika)';
    if (lowerText.match(/bioteknologi|fermentasi|rekayasa genetika/)) return 'Penerapan Bioteknologi';
    return null;
  };

  const matchBahasa = () => {
    if (lowerText.match(/teks deskripsi|pancaindra/)) return 'Teks Deskripsi';
    if (lowerText.match(/cerita fantasi|alur cerita/)) return 'Teks Cerita Fantasi';
    if (lowerText.match(/teks prosedur|tahapan/)) return 'Teks Prosedur';
    if (lowerText.match(/laporan hasil observasi|lho/)) return 'Teks Laporan Hasil Observasi (LHO)';
    if (lowerText.match(/puisi|larik|bait|rima|majas/)) return 'Karya Sastra Puisi';
    if (lowerText.match(/teks berita|5w\+1h|adiksimba/)) return 'Teks Berita';
    if (lowerText.match(/iklan|slogan|poster/)) return 'Iklan, Slogan, dan Poster';
    if (lowerText.match(/teks eksposisi|tesis|argumentasi/)) return 'Teks Eksposisi';
    if (lowerText.match(/teks eksplanasi|sebab akibat/)) return 'Teks Eksplanasi';
    if (lowerText.match(/cerpen|cerita pendek|latar|tema/)) return 'Teks Cerita Pendek (Cerpen)';
    if (lowerText.match(/kalimat efektif|kata baku|ejaan|konjungsi/)) return 'Kaidah Kebahasaan dan Kalimat Efektif';
    return null;
  };

  const matchSosial = () => {
    if (lowerText.match(/pancasila|bpupki|ppki|sila ke/)) return 'Nilai-Nilai dan Sejarah Pancasila';
    if (lowerText.match(/norma kesusilaan|norma kesopanan|norma hukum|norma agama/)) return 'Norma dan Keadilan dalam Masyarakat';
    if (lowerText.match(/uud 1945|konstitusi|pembukaan uud/)) return 'Konstitusi dan UUD NRI Tahun 1945';
    if (lowerText.match(/bhinneka tunggal ika|keberagaman|sara/)) return 'Keberagaman dalam Bhinneka Tunggal Ika';
    if (lowerText.match(/demokrasi|kedaulatan rakyat|lembaga negara/)) return 'Kedaulatan dan Lembaga-Lembaga Negara';
    if (lowerText.match(/bela negara|cinta tanah air|nasionalisme/)) return 'Komitmen Bela Negara';
    return null;
  };

  const matchKomputer = () => {
    if (lowerText.match(/gui|antarmuka grafis|ikon visual/)) return 'Antarmuka Pengguna (GUI)';
    if (lowerText.match(/cli|command line|baris perintah/)) return 'Interaksi Pengguna (CLI)';
    if (lowerText.match(/vui|voice|suara|siri|alexa|google assistant/)) return 'Voice User Interface (VUI)';
    if (lowerText.match(/file explorer|folder|direktori|pengelolaan file/)) return 'Pengelolaan File dan Folder';
    if (lowerText.match(/hard disk|flash drive|flashdisk|kartu memori|media simpan/)) return 'Media Penyimpanan Elektronik';
    if (lowerText.match(/ekstensi|\.jpg|\.png|\.doc|\.docx|format file/)) return 'Format dan Ekstensi File';
    if (lowerText.match(/ctrl-c|ctrl-v|shortcut|tombol pintas/)) return 'Tombol Pintas (Shortcut)';
    if (lowerText.match(/peramban|browser|chrome|firefox|safari|edge/)) return 'Peramban Web (Web Browser)';
    if (lowerText.match(/mesin pencari|search engine|google|bing/)) return 'Mesin Pencari (Search Engine)';
    if (lowerText.match(/surel|email|gmail|inbox/)) return 'Surel (Electronic Mail)';
    if (lowerText.match(/keyboard|papan tombol/)) return 'Perangkat Keras (Keyboard)';
    // Gunakan word boundary \b agar tidak salah tangkap aroma / program / diagram
    if (lowerText.match(/\b(ram|rom|random access memory|volatile)\b/)) return 'Memori Komputer (RAM dan ROM)';
    if (lowerText.match(/cloud storage|penyimpanan awan/)) return 'Penyimpanan Awan (Cloud Storage)';
    if (lowerText.match(/mouse|tetikus|cpu|monitor|printer|hardware/)) return 'Perangkat Keras Komputer';
    if (lowerText.match(/software|perangkat lunak/)) return 'Perangkat Lunak Komputer';
    return null;
  };

  // --- DISPATCHER MATERI DENGAN ISOLASI MAPEL AMAN & PRIORITAS TINGGI ---
  if (!topic) {
    const isPAISubject = ['akidah', 'akhlak', 'fikih', 'qurdis', 'ski', 'bahasa_arab'].includes(detectedDomain) ||
      currentSubjectLower.includes('akidah') ||
      currentSubjectLower.includes('aqidah') ||
      currentSubjectLower.includes('akhlak') ||
      currentSubjectLower.includes('fikih') ||
      currentSubjectLower.includes('fiqih') ||
      currentSubjectLower.includes('qur') ||
      currentSubjectLower.includes('hadits') ||
      currentSubjectLower.includes('hadis') ||
      currentSubjectLower.includes('sejarah') ||
      currentSubjectLower.includes('ski') ||
      currentSubjectLower.includes('arab') ||
      currentSubjectLower.includes('pai') ||
      currentSubjectLower.includes('agama');

    if (isPAISubject) {
      // Prioritas 1: Sesuai Sub-Mapel PAI yang sedang aktif
      if (detectedDomain === 'akidah' || detectedDomain === 'akhlak' || currentSubjectLower.includes('akidah') || currentSubjectLower.includes('akhlak')) {
        topic = matchAkhlak() || matchAkidah();
      } else if (detectedDomain === 'fikih' || currentSubjectLower.includes('fikih') || currentSubjectLower.includes('fiqih')) {
        topic = matchFikih();
      } else if (detectedDomain === 'qurdis' || currentSubjectLower.includes('qur') || currentSubjectLower.includes('hadits')) {
        topic = matchQurdis();
      } else if (detectedDomain === 'ski' || currentSubjectLower.includes('ski') || currentSubjectLower.includes('sejarah')) {
        topic = matchSKI();
      } else if (detectedDomain === 'bahasa_arab' || currentSubjectLower.includes('arab')) {
        topic = matchBahasaArab();
      }

      // Prioritas 2: Rumpun PAI secara menyeluruh jika belum cocok di sub-mapel
      if (!topic) {
        topic = matchAkhlak() || matchAkidah() || matchFikih() || matchQurdis() || matchSKI() || matchBahasaArab();
      }

      // Pastikan domain tetap di rumpun PAI
      if (topic) {
        if (!['akidah', 'akhlak', 'fikih', 'qurdis', 'ski', 'bahasa_arab'].includes(detectedDomain)) {
          detectedDomain = 'akhlak';
        }
      }
    } else if (detectedDomain === 'komputer') {
      topic = matchKomputer();
    } else if (detectedDomain === 'matematika') {
      topic = matchMatematika();
    } else if (detectedDomain === 'sains') {
      topic = matchSains();
    } else if (detectedDomain === 'sosial') {
      topic = matchSosial();
    } else if (detectedDomain === 'bahasa') {
      topic = matchBahasa();
    } else {
      // Untuk mapel umum / tidak terklasifikasi, coba deteksi semua domain
      topic = matchKomputer() || matchMatematika() || matchSains() || matchSosial() || matchBahasa() || matchAkhlak() || matchAkidah() || matchFikih() || matchQurdis() || matchSKI();
    }

    // Heuristik Cerdas: Ekstraksi langsung dari kalimat soal jika belum menemukan topik spesifik
    if (!topic) {
      const extracted = extractHeuristicSubject(cleanHtml, lowerText);
      if (extracted) {
        topic = extracted;
      } else if (prevQ && prevQ.material) {
        topic = prevQ.material;
      } else if (currentSubject) {
        topic = 'Materi ' + currentSubject;
      } else {
        topic = 'Materi Pokok';
      }
    }
  }

  // 2. Determine Indikator Soal Kontekstual
  let indicator = q.indicator || '';
  if (!indicator) {
    const qType = q.question_type || 'pg';

    // 2A. JIKA SOAL MENAMPILKAN SITASI QURAN / HADITS / ARAB
    if (quranCitation) {
      let stimulus = 'Disajikan kutipan ' + quranCitation.fullRef;
      if (arabicSnippet) stimulus += ` (${arabicSnippet})`;

      if (lowerText.match(/kandungan|pesan|makna|isi|maksud|tentang/)) {
        indicator = `${stimulus}, peserta didik dapat menganalisis dan menentukan isi kandungannya dengan tepat.`;
      } else if (lowerText.match(/arti|terjemah|makna kata|lafal/)) {
        indicator = `${stimulus}, peserta didik dapat menentukan arti/terjemahan dari lafal yang dimaksud dengan tepat.`;
      } else if (lowerText.match(/tajwid|hukum bacaan|idzhar|ikhfa|mad|idgham/)) {
        indicator = `${stimulus}, peserta didik dapat mengidentifikasi hukum bacaan tajwid yang terdapat di dalamnya dengan benar.`;
      } else if (lowerText.match(/lanjutan|sambungan|melengkapi/)) {
        indicator = `${stimulus}, peserta didik dapat melengkapi potongan lafal ayat tersebut secara tepat.`;
      } else if (lowerText.match(/contoh|perilaku|penerapan|kehidupan/)) {
        indicator = `${stimulus}, peserta didik dapat menentukan contoh perilaku yang mencerminkan pengamalan ayat tersebut dalam kehidupan sehari-hari secara tepat.`;
      } else {
        indicator = `${stimulus}, peserta didik dapat menentukan jawaban yang tepat berkaitan dengan materi yang diujikan.`;
      }
    } else if (hadithCitation) {
      let stimulus = 'Disajikan hadits ' + hadithCitation;
      if (arabicSnippet) stimulus += ` (${arabicSnippet})`;
      indicator = `${stimulus}, peserta didik dapat menganalisis dan menentukan kandungan atau hikmah yang tersirat dengan tepat.`;
    } else if (detectedDomain === 'bahasa_arab' || hasArabic) {
      let stimulus = 'Disajikan teks atau kalimat berbahasa Arab' + (arabicSnippet ? ` (${arabicSnippet})` : '');
      if (lowerText.match(/arti|terjemah|makna/)) {
        indicator = `${stimulus}, peserta didik dapat menentukan arti kosakata (mufradat) yang tepat.`;
      } else if (lowerText.match(/tarkib|kaidah|dhamir|fi'il|isim|mubtada/)) {
        indicator = `${stimulus}, peserta didik dapat melengkapi kalimat menggunakan kaidah tata bahasa (tarkib) yang benar.`;
      } else {
        indicator = `${stimulus}, peserta didik dapat menentukan jawaban yang tepat mengenai teks tersebut.`;
      }
    } else {
      // Tentukan frasa pembuka stimulus yang tepat
      let stimulus = 'Disajikan pertanyaan';
      if (lowerText.match(/tabel|data berikut/)) stimulus = 'Disajikan tabel data';
      else if (lowerText.match(/gambar|foto|diagram/)) stimulus = 'Disajikan gambar ilustrasi';
      else if (lowerText.match(/contoh|perilaku|sikap|kasus|cerita|kisah/)) stimulus = 'Disajikan ilustrasi kasus atau perilaku';
      else if (lowerText.match(/pernyataan/)) stimulus = 'Disajikan beberapa pernyataan';

      if (qType === 'pg') {
        if (lowerText.match(/pengertian|definisi|arti|makna|istilah|disebut|dinamakan/)) {
          indicator = `${stimulus} terkait ${topic}, peserta didik dapat mengidentifikasi pengertian atau maknanya secara tepat.`;
        } else if (lowerText.match(/contoh|perilaku|penerapan|tindakan|cerminan|kehidupan/)) {
          indicator = `${stimulus}, peserta didik dapat menentukan contoh penerapan dari ${topic} dalam kehidupan nyata dengan tepat.`;
        } else if (lowerText.match(/ciri|karakteristik|tanda|sifat/)) {
          indicator = `${stimulus}, peserta didik dapat mengidentifikasi ciri-ciri atau tanda dari ${topic} dengan benar.`;
        } else if (lowerText.match(/hikmah|manfaat|keutamaan|tujuan|fungsi/)) {
          indicator = `${stimulus}, peserta didik dapat menjelaskan hikmah atau fungsi terkait ${topic} secara tepat.`;
        } else if (lowerText.match(/dampak|akibat|bahaya|larangan|tercela/)) {
          indicator = `${stimulus}, peserta didik dapat menganalisis dampak negatif atau larangan terkait ${topic} dengan benar.`;
        } else if (lowerText.match(/dalil|ayat|hadits|surat|riwayat/)) {
          indicator = `${stimulus}, peserta didik dapat menentukan kandungan dalil yang berkaitan dengan ${topic} secara tepat.`;
        } else if (lowerText.match(/hitung|hasil|kalkulasi|nilai|luas|volume|besar/)) {
          indicator = `${stimulus}, peserta didik dapat menghitung dan menentukan hasil akhir dari ${topic} dengan tepat.`;
        } else if (lowerText.match(/langkah|cara|prosedur|tahapan|urutan|syarat|rukun/)) {
          indicator = `${stimulus}, peserta didik dapat menentukan urutan tata cara/prosedur terkait ${topic} dengan benar.`;
        } else if (lowerText.match(/mengapa|alasan|keunggulan|faktor|penyebab|akibat/)) {
          indicator = `${stimulus} mengenai ${topic}, peserta didik dapat menganalisis alasan atau faktor penyebabnya dengan tepat.`;
        } else {
          indicator = `${stimulus} mengenai ${topic}, peserta didik dapat menentukan jawaban yang tepat.`;
        }
      } else if (qType === 'pg_complex') {
        indicator = `${stimulus} terkait ${topic}, peserta didik dapat menganalisis dan memilih beberapa pernyataan yang benar.`;
      } else if (qType === 'true_false') {
        indicator = `${stimulus} mengenai ${topic}, peserta didik dapat memvalidasi kebenaran pernyataan tersebut secara tepat.`;
      } else if (qType === 'matching') {
        indicator = `${stimulus} terkait ${topic}, peserta didik dapat menjodohkan konsep atau istilah dengan pasangannya yang sesuai.`;
      } else if (qType === 'short_answer') {
        indicator = `${stimulus} terkait ${topic}, peserta didik dapat menuliskan jawaban yang tepat secara ringkas.`;
      } else if (qType === 'essay') {
        indicator = `${stimulus} konseptual terkait ${topic}, peserta didik dapat menjelaskan, menganalisis, dan memberikan contoh penerapannya secara mendalam.`;
      }
    }
  }

  // 3. Determine Tujuan Pembelajaran (TP) / CP
  let tp = q.basic_competency || '';
  if (!tp) {
    if (prevQ && prevQ.basic_competency && prevQ.material === topic) {
      tp = prevQ.basic_competency;
    } else {
      if (detectedDomain === 'qurdis') {
        tp = `Peserta didik mampu membaca, memahami, dan menganalisis kandungan dalil ${topic} serta mengamalkannya dalam kehidupan beriman.`;
      } else if (detectedDomain === 'akidah') {
        tp = `Peserta didik mampu memahami, meyakini, dan meneladani nilai-nilai ${topic} dalam kehidupan sehari-hari.`;
      } else if (detectedDomain === 'akhlak') {
        tp = `Peserta didik mampu menganalisis, membiasakan, dan menerapkan perilaku terpuji terkait ${topic} dalam kehidupan sehari-hari.`;
      } else if (detectedDomain === 'fikih') {
        tp = `Peserta didik mampu memahami ketentuan hukum syariat dan mempraktikkan tata cara ibadah ${topic} secara benar.`;
      } else if (detectedDomain === 'ski') {
        tp = `Peserta didik mampu menganalisis peristiwa sejarah Islam dan meneladani nilai-nilai perjuangan ${topic}.`;
      } else if (detectedDomain === 'bahasa_arab') {
        tp = `Peserta didik mampu memahami kosakata (mufradat), kaidah struktur teks (tarkib), dan ungkapan komunikatif terkait ${topic}.`;
      } else if (detectedDomain === 'matematika' || detectedDomain === 'sains') {
        tp = `Peserta didik mampu memahami konsep, prinsip, dan menyelesaikan permasalahan kontekstual terkait ${topic}.`;
      } else if (detectedDomain === 'bahasa') {
        tp = `Peserta didik mampu menganalisis struktur, kaidah kebahasaan, dan menyajikan gagasan mengenai ${topic}.`;
      } else {
        tp = `Peserta didik mampu memahami dan menganalisis konsep ${topic} beserta penerapannya.`;
      }
    }
  }

  // 4. Determine Level Kognitif Cerdas
  let cognitive = 'L1';
  const qType = q.question_type || 'pg';

  if (
    qType === 'pg_complex' ||
    lowerText.match(/mengapa|analisis|evaluasi|simpulkan|faktor|kendala|dampak|solusi|perbedaan|membandingkan|kombinasi|strategi|merancang|buatlah|penyebab|akibat/i)
  ) {
    cognitive = 'L3';
  } else if (
    qType === 'matching' ||
    qType === 'true_false' ||
    lowerText.match(/langkah|cara|prosedur|fungsi|kegunaan|bagaimana|hitung|mengurutkan|terapkan|gunakan|tentukan|hasil dari|pasangkan|manfaat|contoh|perilaku|cerminan/i)
  ) {
    cognitive = 'L2';
  } else {
    cognitive = 'L1';
  }

  // 5. Determine Tingkat Kesukaran (selaras dengan level kognitif)
  let diff = 'mudah';
  if (cognitive === 'L3') {
    diff = 'sukar';
  } else if (cognitive === 'L2') {
    diff = 'sedang';
  } else {
    diff = 'mudah';
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
  toast.success(`Kisi-kisi soal No. ${q.question_number} berhasil dirumuskan!`);
};

const resetSingleKisiKisi = (index) => {
  const q = activeQuestions.value[index];
  if (!q) return;
  q.basic_competency = '';
  q.material = '';
  q.indicator = '';
  q.cognitive_level = 'L1';
  q.difficulty_level = 'mudah';
  toast.info(`Isian kisi-kisi soal No. ${q.question_number} dikosongkan.`);
};

const autoGenerateAllKisiKisi = async () => {
  if (!activeQuestions.value || activeQuestions.value.length === 0) return;

  const countEmpty = activeQuestions.value.filter(q => !q.indicator || !q.material || !q.basic_competency).length;
  const confirmMsg = countEmpty > 0
    ? `Generate kisi-kisi otomatis untuk ${countEmpty} butir soal yang kisi-kisinya belum lengkap? Level kognitif dan tingkat kesukaran akan dianalisis otomatis sesuai isi soal.`
    : `Semua butir soal sudah memiliki kisi-kisi. Apakah Anda ingin meng-generate ulang kisi-kisi, level kognitif, dan tingkat kesukaran untuk seluruh ${activeQuestions.value.length} butir soal?`;

  const isConfirmed = await confirm({
    title: 'Auto-Generate Kisi-Kisi',
    message: confirmMsg,
    type: 'info',
    confirmText: 'Ya, Generate Kisi-Kisi',
    cancelText: 'Batal'
  });
  if (!isConfirmed) return;

  let generated = 0;
  for (let i = 0; i < activeQuestions.value.length; i++) {
    const q = activeQuestions.value[i];
    const prevQ = i > 0 ? activeQuestions.value[i - 1] : null;
    const res = analyzeQuestionContent(q, prevQ);

    if (!q.material || countEmpty === 0) q.material = res.material;
    if (!q.indicator || countEmpty === 0) q.indicator = res.indicator;
    if (!q.basic_competency || countEmpty === 0) q.basic_competency = res.basic_competency;
    q.cognitive_level = res.cognitive_level;
    q.difficulty_level = res.difficulty_level;
    generated++;
  }

  toast.success(`Alhamdulillah! Kisi-kisi untuk ${generated} butir soal berhasil diisi otomatis. Jangan lupa klik tombol "Simpan Semua".`);
};

const resetAllKisiKisi = async () => {
  if (!activeQuestions.value || activeQuestions.value.length === 0) return;
  const isConfirmed = await confirm({
    title: 'Kosongkan Seluruh Kisi-Kisi',
    message: `Apakah Anda yakin ingin mengosongkan seluruh isian kisi-kisi (TP, Materi, dan Indikator) untuk seluruh ${activeQuestions.value.length} butir soal?`,
    type: 'warning',
    confirmText: 'Ya, Kosongkan',
    cancelText: 'Batal'
  });
  if (!isConfirmed) return;

  activeQuestions.value.forEach(q => {
    q.basic_competency = '';
    q.material = '';
    q.indicator = '';
    q.cognitive_level = 'L1';
    q.difficulty_level = 'mudah';
  });
  toast.info('Seluruh isian kisi-kisi berhasil dikosongkan. Klik "Simpan Semua" jika ingin menyimpan perubahan ini secara permanen.');
};

const copyKisiFromPrev = (index) => {
  if (index <= 0) return;
  const curQ = activeQuestions.value[index];
  const prevQ = activeQuestions.value[index - 1];
  if (!curQ || !prevQ) return;

  curQ.basic_competency = prevQ.basic_competency || curQ.basic_competency;
  curQ.material = prevQ.material || curQ.material;
  if (prevQ.cognitive_level) curQ.cognitive_level = prevQ.cognitive_level;
  if (prevQ.difficulty_level) curQ.difficulty_level = prevQ.difficulty_level;
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

const getPackageQuestionStats = (pkg) => {
  if (!pkg) return [];
  const stats = [
    { key: 'total', label: 'Total Soal', count: Number(pkg.questions_count) || 0, color: 'text-slate-800' },
  ];

  if (Number(pkg.pg_count) > 0) {
    stats.push({ key: 'pg', label: 'Pilihan Ganda', count: Number(pkg.pg_count), color: 'text-emerald-600' });
  }
  if (Number(pkg.pg_complex_count) > 0) {
    stats.push({ key: 'pg_complex', label: 'PG Kompleks', count: Number(pkg.pg_complex_count), color: 'text-indigo-600' });
  }
  if (Number(pkg.true_false_count) > 0) {
    stats.push({ key: 'true_false', label: 'Benar/Salah', count: Number(pkg.true_false_count), color: 'text-teal-600' });
  }
  if (Number(pkg.matching_count) > 0) {
    stats.push({ key: 'matching', label: 'Menjodohkan', count: Number(pkg.matching_count), color: 'text-purple-600' });
  }
  if (Number(pkg.short_answer_count) > 0) {
    stats.push({ key: 'short_answer', label: 'Isian Singkat', count: Number(pkg.short_answer_count), color: 'text-amber-600' });
  }
  if (Number(pkg.essay_count) > 0) {
    stats.push({ key: 'essay', label: 'Uraian/Essay', count: Number(pkg.essay_count), color: 'text-blue-600' });
  }

  // Safety net: check if there are unaccounted questions with unstandardized types
  const totalCount = Number(pkg.questions_count) || 0;
  const accounted = (Number(pkg.pg_count) || 0) +
    (Number(pkg.pg_complex_count) || 0) +
    (Number(pkg.true_false_count) || 0) +
    (Number(pkg.matching_count) || 0) +
    (Number(pkg.short_answer_count) || 0) +
    (Number(pkg.essay_count) || 0);

  const diff = totalCount - accounted;
  if (diff > 0 && stats.length > 1) {
    stats.push({ key: 'other', label: 'Lainnya', count: diff, color: 'text-slate-600' });
  }

  return stats;
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
  createForm.target_selection = globalTargetOptions.value[0]?.key || (options.classes[0]?.id ? 'class_' + options.classes[0].id : '');
  createForm.class_room_id = options.classes[0]?.id || '';
  createForm.target_grade = null;
  createForm.exam_type = 'sts';
  createForm.semester = 'ganjil';
  createForm.execution_month = getCurrentMonthName();
  createForm.execution_year = getCurrentYear();
  createForm.composition_preset = 'standard_mts_45';
  createForm.custom_counts = {
    pg: 20,
    pg_complex: 10,
    true_false: 5,
    matching: 5,
    short_answer: 0,
    essay: 5
  };
  createForm.custom_points = {
    pg: 1,
    pg_complex: 3,
    true_false: 2,
    matching: 3,
    short_answer: 2,
    essay: 5
  };
  createForm.total_questions = 45;
  createForm.kkm = 75;
  createForm.academic_phase = 'Fase D';
  createForm.pg_weight = 75;
  createForm.essay_weight = 25;
  syncCreateCorrectionClasses();
  showCreateModal.value = true;
};

const openCreateModalWithExamType = (typeKey) => {
  openCreateModal();
  if (['sts', 'sas', 'uh', 'pat', 'am', 'quiz'].includes(typeKey)) {
    createForm.exam_type = typeKey;
  }
};

// Edit Package Modal
const showEditModal = ref(false);
const editingPackageId = ref(null);
const editForm = reactive({
  title: '',
  subject_id: '',
  class_room_id: '',
  target_selection: '',
  target_grade: null,
  academic_year_id: '',
  exam_type: 'sts',
  semester: 'ganjil',
  execution_month: getCurrentMonthName(),
  execution_year: getCurrentYear(),
  total_questions: 20,
  kkm: 75,
  academic_phase: 'Fase D',
  pg_weight: 70,
  essay_weight: 30
});

const openEditModal = (pkg) => {
  editingPackageId.value = pkg.id;
  editForm.title = pkg.title || '';
  editForm.subject_id = pkg.subject_id || '';
  editForm.class_room_id = pkg.class_room_id || '';
  editForm.target_grade = pkg.target_grade || null;
  if (pkg.target_grade) {
    editForm.target_selection = 'global_' + pkg.target_grade;
  } else if (pkg.class_room_id) {
    editForm.target_selection = 'class_' + pkg.class_room_id;
  } else {
    editForm.target_selection = globalTargetOptions.value[0]?.key || '';
  }
  editForm.academic_year_id = pkg.academic_year_id || '';
  editForm.exam_type = pkg.exam_type || 'sts';
  editForm.semester = pkg.semester || 'ganjil';

  let execMonth = getCurrentMonthName();
  let execYear = getCurrentYear();
  if (pkg.execution_date) {
    const parts = pkg.execution_date.trim().split(/\s+/);
    if (parts.length >= 2) {
      execMonth = parts.slice(0, parts.length - 1).join(' ');
      execYear = parseInt(parts[parts.length - 1]) || getCurrentYear();
    } else if (parts.length === 1) {
      execMonth = parts[0];
    }
  }
  editForm.execution_month = execMonth;
  editForm.execution_year = execYear;

  editForm.total_questions = pkg.total_questions || 20;
  editForm.kkm = pkg.kkm || 75;
  editForm.academic_phase = pkg.academic_phase || 'Fase D';
  editForm.pg_weight = pkg.pg_weight ?? 70;
  editForm.essay_weight = pkg.essay_weight ?? 30;
  showEditModal.value = true;
};

const updatePackageInfo = async () => {
  if (!editingPackageId.value) return;
  submitting.value = true;
  try {
    const chosenOpt = allTargetOptions.value.find(o => o.key === editForm.target_selection);
    const resolvedClassId = chosenOpt ? chosenOpt.class_room_id : editForm.class_room_id;
    const resolvedTargetGrade = chosenOpt ? chosenOpt.target_grade : null;

    const payload = {
      ...editForm,
      class_room_id: resolvedClassId,
      target_grade: resolvedTargetGrade,
      execution_date: editForm.execution_month && editForm.execution_year
        ? `${editForm.execution_month} ${editForm.execution_year}`
        : null
    };
    const res = await api.put(`/teacher/exam-bank/${editingPackageId.value}`, payload);
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      showEditModal.value = false;
      await fetchPackages();
      toast.success('Informasi paket soal berhasil diperbarui!');
    }
  } catch (err) {
    toast.error(err.response?.data?.message || err.message || 'Gagal memperbarui paket soal.');
  } finally {
    submitting.value = false;
  }
};

const createPackage = async () => {
  if (isCreateGlobalSelected.value && createForm.correction_mode === 'split_selected' && (!createForm.correction_class_ids || createForm.correction_class_ids.length === 0)) {
    toast.error('Pilih minimal satu rombel kelas tujuan koreksi, atau pilih mode lain.');
    return;
  }
  submitting.value = true;
  try {
    const chosenOpt = allTargetOptions.value.find(o => o.key === createForm.target_selection);
    const resolvedClassId = chosenOpt ? chosenOpt.class_room_id : createForm.class_room_id;
    const resolvedTargetGrade = chosenOpt ? chosenOpt.target_grade : null;

    const payload = {
      ...createForm,
      class_room_id: resolvedClassId,
      target_grade: resolvedTargetGrade,
      execution_date: createForm.execution_month && createForm.execution_year
        ? `${createForm.execution_month} ${createForm.execution_year}`
        : null
    };
    const res = await api.post('/teacher/exam-bank', payload);
    const d = res.data || res;
    if (res.status === 'success' || d.status === 'success') {
      showCreateModal.value = false;
      await fetchPackages();
      const newId = d.data?.id || d.id;
      toast.success('Paket soal baru berhasil dibuat!');
      if (newId) openEditor(newId);
    }
  } catch (err) {
    toast.error(err.response?.data?.message || err.message || 'Gagal membuat paket soal.');
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
      // Clean up legacy questions where E is empty and not used as answer only once upon initial load
      activeQuestions.value.forEach(q => {
        if (q.options && typeof q.options === 'object' && !Array.isArray(q.options)) {
          if (q.options.E !== undefined && q.options.E === '' && (!q.correct_answer || !q.correct_answer.includes('E'))) {
            delete q.options.E;
          }
        }
      });
      currentQuestionIndex.value = 0;
    }
  } catch (err) {
    toast.error(err.response?.data?.message || err.message || 'Gagal memuat butir soal.');
  }
};

const closeEditor = () => {
  activePackage.value = null;
  activeQuestions.value = [];
  fetchPackages();
};

const ALL_OPTION_LETTERS = ['A', 'B', 'C', 'D', 'E'];

const ensureOptions = (q) => {
  if (!q.options || typeof q.options !== 'object' || Array.isArray(q.options) || q.options.mode !== undefined) {
    q.options = { A: '', B: '', C: '', D: '' };
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
    q.options = { ...opts, [nextLetter]: '' };
  }
};

const removeOption = (q, opt) => {
  const opts = ensureOptions(q);
  const keys = getOptionKeys(q);
  if (keys.length <= 2) {
    toast.warning('Pilihan jawaban minimal harus memiliki 2 opsi!');
    return;
  }
  
  delete opts[opt];
  q.options = { ...opts };
  
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
      if (q.options.E !== undefined && (!q.options.E || q.options.E.trim() === '') && (!q.correct_answer || !q.correct_answer.includes('E'))) {
        delete q.options.E;
      }
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
  } else if (q.question_type === 'pg' || q.question_type === 'pg_complex') {
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
      toast.success('Semua butir soal dan kisi-kisi berhasil disimpan!');
    }
  } catch (err) {
    toast.error(err.response?.data?.message || err.message || 'Gagal menyimpan butir soal.');
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
      toast.success('Gambar stimulus berhasil diunggah!');
    }
  } catch (err) {
    toast.error(err.response?.data?.message || err.message || 'Gagal mengunggah gambar stimulus.');
  } finally {
    uploadingImage.value = false;
  }
};

const getStorageUrl = (path) => {
  return getImageUrl(path);
};

const submitToKurikulum = async (pkg) => {
  const isConfirmed = await confirm({
    title: 'Ajukan ke Tim Kurikulum',
    message: `Ajukan paket soal "${pkg.title}" ke Tim Kurikulum untuk ditelaah dan diverifikasi?`,
    type: 'info',
    confirmText: 'Ya, Ajukan',
    cancelText: 'Batal'
  });
  if (!isConfirmed) return;

  try {
    const res = await api.post(`/teacher/exam-bank/${pkg.id}/submit-kurikulum`);
    if (res.data?.status === 'success') {
      toast.success(res.data.message || 'Paket soal berhasil diajukan ke Tim Kurikulum!');
      fetchPackages();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengajukan paket soal.');
  }
};

const deletePackage = async (pkg) => {
  const isConfirmed = await confirm({
    title: 'Hapus Paket Soal',
    message: `Hapus paket soal "${pkg.title}" beserta seluruh butir soalnya? Tindakan ini tidak dapat dibatalkan.`,
    type: 'danger',
    confirmText: 'Ya, Hapus Paket',
    cancelText: 'Batal'
  });
  if (!isConfirmed) return;

  try {
    const res = await api.delete(`/teacher/exam-bank/${pkg.id}`);
    if (res.data?.status === 'success') {
      toast.success('Paket soal berhasil dihapus!');
      fetchPackages();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus paket soal.');
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
      toast.success(res.data.message || 'File Excel butir soal berhasil diimpor!');
      showImportModal.value = false;
      fetchPackages();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengimpor file Excel.');
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
