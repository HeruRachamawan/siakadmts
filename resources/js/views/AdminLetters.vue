<template>
  <div class="space-y-6 font-sans">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
      <div>
        <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 uppercase tracking-wider mb-1">
          <FileText class="w-4 h-4" />
          <span>Administrasi & Tata Usaha</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Persuratan & Arsip Digital</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Buku agenda surat masuk, surat keluar, lembar disposisi, dan generator surat keterangan siswa aktif.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2.5">
        <button
          @click="openAddModal('incoming')"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-600/25 transition-all active:scale-95 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>+ Surat Masuk</span>
        </button>

        <button
          @click="openAddModal('outgoing')"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm shadow-indigo-600/25 transition-all active:scale-95 cursor-pointer"
        >
          <Send class="w-4 h-4" />
          <span>+ Surat Keluar</span>
        </button>
      </div>
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-medium text-slate-500">Total Surat Masuk</p>
          <p class="text-2xl font-black text-slate-900 mt-1">{{ stats.total_incoming || 0 }}</p>
          <span class="text-[11px] text-emerald-600 font-semibold">{{ stats.this_month_incoming || 0 }} bulan ini</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
          <Inbox class="w-5 h-5" />
        </div>
      </div>

      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-medium text-slate-500">Total Surat Keluar</p>
          <p class="text-2xl font-black text-slate-900 mt-1">{{ stats.total_outgoing || 0 }}</p>
          <span class="text-[11px] text-indigo-600 font-semibold">{{ stats.this_month_outgoing || 0 }} bulan ini</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
          <Send class="w-5 h-5" />
        </div>
      </div>

      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-medium text-slate-500">Perlu Disposisi</p>
          <p class="text-2xl font-black text-amber-600 mt-1">{{ stats.pending_disposition || 0 }}</p>
          <span class="text-[11px] text-slate-400 font-normal">Menunggu arahan</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
          <AlertCircle class="w-5 h-5" />
        </div>
      </div>

      <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
          <p class="text-xs font-medium text-slate-500">Surat Keterangan</p>
          <p class="text-2xl font-black text-teal-600 mt-1">1 Klik</p>
          <span class="text-[11px] text-slate-400 font-normal">Cetak instan siswa</span>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
          <Award class="w-5 h-5" />
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 overflow-x-auto pb-1">
      <button
        @click="activeTab = 'incoming'"
        :class="activeTab === 'incoming' ? 'text-emerald-700 border-emerald-600 font-bold bg-emerald-50/70' : 'text-slate-500 border-transparent hover:text-slate-700'"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-colors whitespace-nowrap cursor-pointer"
      >
        <Inbox class="w-4 h-4" />
        <span>📥 Surat Masuk</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ stats.total_incoming || 0 }}</span>
      </button>

      <button
        @click="activeTab = 'outgoing'"
        :class="activeTab === 'outgoing' ? 'text-indigo-700 border-indigo-600 font-bold bg-indigo-50/70' : 'text-slate-500 border-transparent hover:text-slate-700'"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-colors whitespace-nowrap cursor-pointer"
      >
        <Send class="w-4 h-4" />
        <span>📤 Surat Keluar</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">{{ stats.total_outgoing || 0 }}</span>
      </button>

      <button
        @click="activeTab = 'student_cert'"
        :class="activeTab === 'student_cert' ? 'text-teal-700 border-teal-600 font-bold bg-teal-50/70' : 'text-slate-500 border-transparent hover:text-slate-700'"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-colors whitespace-nowrap cursor-pointer"
      >
        <FileCheck class="w-4 h-4" />
        <span>🎓 Generator Surat Siswa Aktif</span>
      </button>

      <button
        @click="activeTab = 'agenda_print'"
        :class="activeTab === 'agenda_print' ? 'text-slate-900 border-slate-700 font-bold bg-slate-100' : 'text-slate-500 border-transparent hover:text-slate-700'"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold rounded-t-xl border-b-2 transition-colors whitespace-nowrap cursor-pointer"
      >
        <Printer class="w-4 h-4" />
        <span>📑 Cetak Buku Agenda Resmi</span>
      </button>
    </div>

    <!-- TAB 1: SURAT MASUK -->
    <div v-if="activeTab === 'incoming'" class="space-y-4">
      <!-- Search & Filters -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="relative w-full sm:w-80">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="filters.search"
            @input="debouncedFetch"
            type="text"
            placeholder="Cari pengirim, nomor surat, perihal..."
            class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
          />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <select
            v-model="filters.status"
            @change="fetchLetters"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
          >
            <option value="all">Semua Status</option>
            <option value="pending">Belum Disposisi</option>
            <option value="dispositioned">Telah Disposisi</option>
            <option value="processed">Selesai</option>
          </select>

          <button
            @click="fetchLetters"
            class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer"
            title="Muat Ulang"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>

      <!-- Table View Desktop -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs min-w-[850px]">
            <thead>
              <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider whitespace-nowrap">
                <th class="py-3 px-4 w-28">No. Agenda</th>
                <th class="py-3 px-4 w-48">Nomor & Tanggal Surat</th>
                <th class="py-3 px-4 w-44">Asal Pengirim</th>
                <th class="py-3 px-4">Perihal</th>
                <th class="py-3 px-4 w-44">Disposisi</th>
                <th class="py-3 px-4 w-20 text-center">Berkas</th>
                <th class="py-3 px-4 w-36 text-center">Status</th>
                <th class="py-3 px-4 w-28 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="loading" class="text-center">
                <td colspan="8" class="py-8 text-slate-400">Memuat data surat masuk...</td>
              </tr>
              <tr v-else-if="letters.length === 0" class="text-center">
                <td colspan="8" class="py-10 text-slate-400">Belum ada catatan surat masuk.</td>
              </tr>
              <tr v-for="item in letters" :key="item.id" class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-emerald-800 whitespace-nowrap">{{ item.agenda_number }}</td>
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-slate-900 leading-snug">{{ item.reference_number || '-' }}</div>
                  <div class="text-[11px] text-slate-500 whitespace-nowrap mt-0.5">Tgl: {{ formatDate(item.letter_date) }}</div>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-800">{{ item.sender }}</td>
                <td class="py-3.5 px-4">
                  <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-700 rounded-md mb-1">{{ item.category }}</span>
                  <div class="line-clamp-2 text-slate-700 leading-snug">{{ item.subject }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <div v-if="item.disposition_to" class="space-y-0.5">
                    <span class="inline-flex items-center gap-1 font-bold text-sky-700 whitespace-nowrap">
                      <UserCheck class="w-3.5 h-3.5" />
                      {{ item.disposition_to }}
                    </span>
                    <p class="text-[11px] text-slate-500 italic line-clamp-1">"{{ item.disposition_notes || '-' }}"</p>
                  </div>
                  <span v-else class="text-slate-400 italic text-[11px]">Belum ada</span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <a
                    v-if="item.file_path"
                    :href="getStorageUrl(item.file_path)"
                    target="_blank"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors whitespace-nowrap"
                  >
                    <FileText class="w-3.5 h-3.5" />
                    <span>Lihat</span>
                  </a>
                  <span v-else class="text-slate-400 text-[11px]">-</span>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-bold border shadow-xs whitespace-nowrap leading-none" :class="item.status_badge_class">
                    {{ item.status_label }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="inline-flex items-center gap-1">
                    <button
                      @click="openDispositionModal(item)"
                      class="p-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg transition-colors cursor-pointer"
                      title="Isi / Cetak Lembar Disposisi"
                    >
                      <Share2 class="w-4 h-4" />
                    </button>
                    <button
                      @click="openEditModal(item)"
                      class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors cursor-pointer"
                      title="Edit Surat"
                    >
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteLetter(item)"
                      class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition-colors cursor-pointer"
                      title="Hapus"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Bar -->
        <div v-if="pagination.last_page > 1" class="px-6 py-3 bg-slate-50/80 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600">
          <div>
            Menampilkan halaman <span class="font-bold text-slate-900">{{ pagination.current_page }}</span> dari <span class="font-bold text-slate-900">{{ pagination.last_page }}</span> (Total {{ pagination.total }} surat)
          </div>
          <div class="flex items-center gap-1.5">
            <button
              :disabled="pagination.current_page <= 1"
              @click="fetchLetters(pagination.current_page - 1)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors shadow-2xs"
            >
              &larr; Sebelumnya
            </button>
            <span class="px-2 font-bold text-emerald-800">{{ pagination.current_page }}</span>
            <button
              :disabled="pagination.current_page >= pagination.last_page"
              @click="fetchLetters(pagination.current_page + 1)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors shadow-2xs"
            >
              Selanjutnya &rarr;
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 2: SURAT KELUAR -->
    <div v-if="activeTab === 'outgoing'" class="space-y-4">
      <!-- Search & Filters -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div class="relative w-full sm:w-80">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="filters.search"
            @input="debouncedFetch"
            type="text"
            placeholder="Cari nomor surat keluar, penerima, perihal..."
            class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
          <button
            @click="fetchLetters"
            class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer"
            title="Muat Ulang"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          </button>
        </div>
      </div>

      <!-- Table View Desktop -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider">
                <th class="py-3 px-4">No. Agenda</th>
                <th class="py-3 px-4">Nomor Surat Resmi</th>
                <th class="py-3 px-4">Tanggal Surat</th>
                <th class="py-3 px-4">Tujuan / Penerima</th>
                <th class="py-3 px-4">Perihal</th>
                <th class="py-3 px-4">Berkas Arsip</th>
                <th class="py-3 px-4 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="loading" class="text-center">
                <td colspan="7" class="py-8 text-slate-400">Memuat data surat keluar...</td>
              </tr>
              <tr v-else-if="letters.length === 0" class="text-center">
                <td colspan="7" class="py-10 text-slate-400">Belum ada catatan surat keluar.</td>
              </tr>
              <tr v-for="item in letters" :key="item.id" class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3.5 px-4 font-mono font-bold text-indigo-800">{{ item.agenda_number }}</td>
                <td class="py-3.5 px-4 font-semibold text-slate-900">{{ item.reference_number }}</td>
                <td class="py-3.5 px-4 text-slate-600">{{ formatDate(item.letter_date) }}</td>
                <td class="py-3.5 px-4 font-medium text-slate-800">{{ item.recipient }}</td>
                <td class="py-3.5 px-4 max-w-xs">
                  <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-indigo-50 text-indigo-700 rounded-md mb-1">{{ item.category }}</span>
                  <div class="line-clamp-2 text-slate-700">{{ item.subject }}</div>
                </td>
                <td class="py-3.5 px-4">
                  <a
                    v-if="item.file_path"
                    :href="getStorageUrl(item.file_path)"
                    target="_blank"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors"
                  >
                    <FileText class="w-3.5 h-3.5" />
                    <span>Arsip</span>
                  </a>
                  <span v-else class="text-slate-400 text-[11px]">-</span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <div class="inline-flex items-center gap-1">
                    <button
                      @click="openEditModal(item)"
                      class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors cursor-pointer"
                      title="Edit"
                    >
                      <Pencil class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteLetter(item)"
                      class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition-colors cursor-pointer"
                      title="Hapus"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Bar -->
        <div v-if="pagination.last_page > 1" class="px-6 py-3 bg-slate-50/80 border-t border-slate-200 flex items-center justify-between text-xs text-slate-600">
          <div>
            Menampilkan halaman <span class="font-bold text-slate-900">{{ pagination.current_page }}</span> dari <span class="font-bold text-slate-900">{{ pagination.last_page }}</span> (Total {{ pagination.total }} surat)
          </div>
          <div class="flex items-center gap-1.5">
            <button
              :disabled="pagination.current_page <= 1"
              @click="fetchLetters(pagination.current_page - 1)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors shadow-2xs"
            >
              &larr; Sebelumnya
            </button>
            <span class="px-2 font-bold text-indigo-800">{{ pagination.current_page }}</span>
            <button
              :disabled="pagination.current_page >= pagination.last_page"
              @click="fetchLetters(pagination.current_page + 1)"
              class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 font-semibold disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors shadow-2xs"
            >
              Selanjutnya &rarr;
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 3: GENERATOR SURAT SISWA AKTIF -->
    <div v-if="activeTab === 'student_cert'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
        <div>
          <h3 class="text-lg font-bold text-slate-900">Formulir Surat Keterangan Siswa</h3>
          <p class="text-xs text-slate-500 mt-1">Cari siswa aktif dan terbitkan surat keterangan resmi dengan nomor agenda otomatis.</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih Siswa Aktif <span class="text-rose-500">*</span></label>
            <select
              v-model="certForm.student_id"
              @change="onSelectCertStudent"
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-500"
            >
              <option value="">-- Pilih Siswa --</option>
              <option v-for="s in studentList" :key="s.id" :value="s.id">
                {{ s.full_name }} (NISN: {{ s.nisn || '-' }} | Kelas: {{ s.class_room?.name || s.classRoom?.name || '-' }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Keperluan Surat <span class="text-rose-500">*</span></label>
            <input
              v-model="certForm.purpose"
              type="text"
              placeholder="Contoh: Persyaratan Beasiswa PIP / Tunjangan Orang Tua"
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Surat</label>
            <input
              v-model="certForm.letter_date"
              type="date"
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-500"
            />
          </div>

          <button
            @click="generateCertificate"
            :disabled="!certForm.student_id || generatingCert"
            class="w-full flex items-center justify-center gap-2 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs shadow-md shadow-teal-600/25 transition-all disabled:opacity-50 cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>{{ generatingCert ? 'Menerbitkan...' : 'Terbitkan & Cetak Surat' }}</span>
          </button>
        </div>
      </div>

      <!-- Preview Printable Sheet -->
      <div class="lg:col-span-7 bg-white p-8 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between">
        <div id="printable-student-cert" class="p-4 sm:p-8 bg-white border border-slate-100 rounded-2xl text-slate-900 font-serif leading-relaxed text-sm">
          
          <!-- Kop Surat Madrasah -->
          <div class="text-center border-b-4 border-double border-slate-900 pb-3 mb-6">
            <h2 class="text-base font-bold uppercase tracking-wide">YAYASAN AL - HASANAH CIOMAS</h2>
            <h1 class="text-xl font-extrabold uppercase tracking-tight text-emerald-900">{{ appSettings.app_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}</h1>
            <p class="text-xs text-slate-600">{{ appSettings.school_address || 'Jl. Raya Ciomas No. 123, Kab. Bogor, Jawa Barat' }} &bull; Telp: {{ appSettings.school_phone || '0812-3456-7890' }}</p>
          </div>

          <!-- Judul Surat -->
          <div class="text-center space-y-1 my-6">
            <h3 class="text-base font-bold uppercase tracking-wide underline underline-offset-4">SURAT KETERANGAN AKTIF SISWA</h3>
            <p class="text-xs font-sans text-slate-600 font-semibold">Nomor: {{ previewCertData?.reference_number || '... / MTs.AH / PP.00.5 / VIII / 2026' }}</p>
          </div>

          <p class="text-xs sm:text-sm text-justify mb-4">
            Yang bertanda tangan di bawah ini, Kepala Madrasah Tsanawiyah Al - Hasanah Ciomas Kabupaten Bogor, menerangkan dengan sesungguhnya bahwa:
          </p>

          <!-- Biodata Siswa -->
          <table class="w-full text-xs sm:text-sm my-4 border-collapse font-sans">
            <tr>
              <td class="w-36 py-1 font-semibold">Nama Lengkap</td>
              <td class="w-4 py-1">:</td>
              <td class="py-1 font-bold text-slate-900 uppercase">{{ selectedStudentPreview?.full_name || '................................' }}</td>
            </tr>
            <tr>
              <td class="py-1 font-semibold">NIS / NISN</td>
              <td class="py-1">:</td>
              <td class="py-1">{{ selectedStudentPreview?.nis || '-' }} / {{ selectedStudentPreview?.nisn || '-' }}</td>
            </tr>
            <tr>
              <td class="py-1 font-semibold">Tempat, Tgl Lahir</td>
              <td class="py-1">:</td>
              <td class="py-1">{{ selectedStudentPreview?.pob || '-' }}, {{ formatDate(selectedStudentPreview?.dob) }}</td>
            </tr>
            <tr>
              <td class="py-1 font-semibold">Kelas</td>
              <td class="py-1">:</td>
              <td class="py-1 font-semibold">{{ selectedStudentPreview?.class_room?.name || selectedStudentPreview?.classRoom?.name || 'VII' }}</td>
            </tr>
            <tr>
              <td class="py-1 font-semibold">Nama Orang Tua / Wali</td>
              <td class="py-1">:</td>
              <td class="py-1">{{ selectedStudentPreview?.parent_name || selectedStudentPreview?.father_name || '-' }}</td>
            </tr>
          </table>

          <p class="text-xs sm:text-sm text-justify my-4">
            Adalah benar yang bersangkutan adalah <b>Peserta Didik Aktif</b> pada MTs Al - Hasanah Tahun Ajaran 2026/2027. Surat keterangan ini dibuat dan diberikan untuk keperluan: <b>{{ certForm.purpose || 'Persyaratan Beasiswa / Tunjangan Pendidikan' }}</b>.
          </p>

          <!-- Tanda Tangan -->
          <div class="flex justify-end mt-12 font-sans">
            <div class="text-center w-64 space-y-1">
              <p class="text-xs">Bogor, {{ formatDate(certForm.letter_date || new Date()) }}</p>
              <p class="text-xs font-semibold">Kepala Madrasah,</p>
              <div class="h-16"></div>
              <p class="text-xs font-bold underline">{{ appSettings.principal_name || 'Dr. H. Ahmad Fauzi, M.Pd.I.' }}</p>
              <p class="text-[10px] text-slate-500">NIP. {{ appSettings.principal_nip || '197508122005011002' }}</p>
            </div>
          </div>

        </div>

        <div class="mt-4 flex justify-end">
          <button
            @click="printDocument('printable-student-cert', 'Surat Keterangan Aktif Siswa')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-md cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak Surat (Print / PDF)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- TAB 4: CETAK BUKU AGENDA RESMI -->
    <div v-if="activeTab === 'agenda_print'" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
        <div>
          <h3 class="text-lg font-bold text-slate-900">Cetak Buku Agenda Surat</h3>
          <p class="text-xs text-slate-500 mt-0.5">Ekspor dan cetak buku agenda surat masuk & keluar untuk akreditasi dan arsip tata usaha.</p>
        </div>

        <div class="flex items-center gap-3">
          <select
            v-model="agendaPrintType"
            class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700"
          >
            <option value="incoming">Buku Agenda Surat Masuk</option>
            <option value="outgoing">Buku Agenda Surat Keluar</option>
          </select>

          <button
            @click="printDocument('printable-agenda-book', 'Buku Agenda Persuratan')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak Agenda</span>
          </button>
        </div>
      </div>

      <!-- Printable Table Container -->
      <div id="printable-agenda-book" class="p-6 bg-white border border-slate-200 rounded-2xl">
        <div class="text-center mb-6">
          <h2 class="text-base font-bold uppercase">{{ appSettings.app_name || 'MTs AL - HASANAH CIOMAS' }}</h2>
          <h1 class="text-lg font-extrabold uppercase text-slate-900">
            {{ agendaPrintType === 'incoming' ? 'BUKU AGENDA SURAT MASUK' : 'BUKU AGENDA SURAT KELUAR' }}
          </h1>
          <p class="text-xs text-slate-500">Tahun Ajaran 2026/2027</p>
        </div>

        <table class="w-full border-collapse border border-slate-400 text-xs">
          <thead>
            <tr class="bg-slate-100 text-slate-900 font-bold text-center">
              <th class="border border-slate-400 p-2 w-12">No</th>
              <th class="border border-slate-400 p-2">No. Agenda</th>
              <th class="border border-slate-400 p-2">Nomor Surat</th>
              <th class="border border-slate-400 p-2">Tanggal Surat</th>
              <th class="border border-slate-400 p-2">{{ agendaPrintType === 'incoming' ? 'Asal Pengirim' : 'Tujuan / Penerima' }}</th>
              <th class="border border-slate-400 p-2">Perihal / Isi Ringkas</th>
              <th class="border border-slate-400 p-2" v-if="agendaPrintType === 'incoming'">Disposisi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, idx) in letters" :key="item.id">
              <td class="border border-slate-400 p-2 text-center">{{ idx + 1 }}</td>
              <td class="border border-slate-400 p-2 font-mono font-bold">{{ item.agenda_number }}</td>
              <td class="border border-slate-400 p-2">{{ item.reference_number }}</td>
              <td class="border border-slate-400 p-2 text-center">{{ formatDate(item.letter_date) }}</td>
              <td class="border border-slate-400 p-2 font-medium">{{ agendaPrintType === 'incoming' ? item.sender : item.recipient }}</td>
              <td class="border border-slate-400 p-2">{{ item.subject }}</td>
              <td class="border border-slate-400 p-2" v-if="agendaPrintType === 'incoming'">
                <span v-if="item.disposition_to" class="font-semibold">{{ item.disposition_to }}:</span> {{ item.disposition_notes || '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL: TAMBAH / EDIT SURAT -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
      <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <h3 class="text-lg font-bold text-slate-900">
              {{ editingLetter ? 'Edit Data Surat' : (form.type === 'incoming' ? 'Catat Surat Masuk' : 'Catat Surat Keluar') }}
            </h3>
            <p class="text-xs text-slate-500">Isi kelengkapan data persuratan madrasah.</p>
          </div>
          <button @click="showModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitLetterForm" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Surat</label>
              <select v-model="form.type" :disabled="!!editingLetter" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="incoming">Surat Masuk</option>
                <option value="outgoing">Surat Keluar</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
              <select v-model="form.category" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                <option value="Dinas">Dinas / Resmi</option>
                <option value="Undangan">Undangan Kegiatan</option>
                <option value="Edaran">Surat Edaran</option>
                <option value="Keterangan">Surat Keterangan</option>
                <option value="Keputusan">Surat Keputusan (SK)</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">
                {{ form.type === 'incoming' ? 'Nomor Surat Asal' : 'Nomor Surat Resmi' }}
              </label>
              <input
                v-model="form.reference_number"
                type="text"
                :placeholder="form.type === 'incoming' ? 'Contoh: 123/Kemenag/2026' : 'Kosongkan untuk nomor otomatis'"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />

              <!-- Hint Note for Previous Letter Number -->
              <div v-if="form.type === 'outgoing' && stats.last_outgoing_number" class="mt-2 p-2.5 bg-amber-50/90 border border-amber-200/80 rounded-xl text-[11px] text-amber-900 leading-snug">
                <div class="flex items-center gap-1 font-bold text-amber-950">
                  <span>📌 Surat Keluar Terakhir:</span>
                  <span class="font-mono text-amber-900 underline">{{ stats.last_outgoing_number }}</span>
                </div>
                <div v-if="stats.last_outgoing_date" class="text-[10px] text-amber-800/90 mt-0.5">
                  Tgl Terbit: {{ stats.last_outgoing_date }}
                </div>
              </div>

              <div v-else-if="form.type === 'incoming' && stats.last_incoming_number" class="mt-2 p-2.5 bg-emerald-50/90 border border-emerald-200/80 rounded-xl text-[11px] text-emerald-900 leading-snug">
                <div class="flex items-center gap-1 font-bold text-emerald-950">
                  <span>📌 Surat Masuk Terakhir:</span>
                  <span class="font-mono text-emerald-900">{{ stats.last_incoming_number }}</span>
                </div>
                <div v-if="stats.last_agenda_incoming" class="text-[10px] text-emerald-800/90 mt-0.5">
                  No. Agenda: {{ stats.last_agenda_incoming }} &bull; Tgl: {{ stats.last_incoming_date || '-' }}
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Surat <span class="text-rose-500">*</span></label>
              <input
                v-model="form.letter_date"
                type="date"
                required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              {{ form.type === 'incoming' ? 'Asal Pengirim' : 'Tujuan / Penerima' }} <span class="text-rose-500">*</span>
            </label>
            <input
              v-if="form.type === 'incoming'"
              v-model="form.sender"
              type="text"
              required
              placeholder="Contoh: Kantor Kementerian Agama Kab. Bogor"
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
            <input
              v-else
              v-model="form.recipient"
              type="text"
              required
              placeholder="Contoh: Seluruh Dewan Guru & Wali Murid"
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Perihal / Isi Ringkas <span class="text-rose-500">*</span></label>
            <textarea
              v-model="form.subject"
              rows="2"
              required
              placeholder="Perihal pokok surat..."
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Berkas Scan/PDF (Maks 10MB)</label>
            <input
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              @change="handleFileUpload"
              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"
            />
          </div>

          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm disabled:opacity-50 cursor-pointer"
            >
              {{ submitting ? 'Menyimpan...' : 'Simpan Surat' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: LEMBAR DISPOSISI KEPALA MADRASAH -->
    <div v-if="showDispositionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
      <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Lembar Disposisi Kepala Madrasah</h3>
            <p class="text-xs text-slate-500">Instruksi tindak lanjut surat masuk.</p>
          </div>
          <button @click="showDispositionModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 text-xs space-y-1">
          <p><b>No. Agenda:</b> {{ selectedLetter?.agenda_number }}</p>
          <p><b>Pengirim:</b> {{ selectedLetter?.sender }}</p>
          <p><b>Perihal:</b> {{ selectedLetter?.subject }}</p>
        </div>

        <form @submit.prevent="submitDisposition" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Diteruskan Kepada (Pejabat / Posisi) <span class="text-rose-500">*</span></label>
            <select
              v-model="dispositionForm.disposition_to"
              required
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500"
            >
              <option value="">-- Pilih Penerima Disposisi --</option>
              <option value="Waka Kurikulum">Waka Kurikulum</option>
              <option value="Waka Kesiswaan">Waka Kesiswaan</option>
              <option value="Waka Sarana & Prasarana">Waka Sarana & Prasarana</option>
              <option value="Waka Humas">Waka Humas</option>
              <option value="Kepala Tata Usaha (TU)">Kepala Tata Usaha (TU)</option>
              <option value="Guru Bimbingan Konseling (BK)">Guru Bimbingan Konseling (BK)</option>
              <option value="Seluruh Dewan Guru">Seluruh Dewan Guru</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Instruksi / Catatan Kepala Madrasah <span class="text-rose-500">*</span></label>
            <textarea
              v-model="dispositionForm.disposition_notes"
              rows="3"
              required
              placeholder="Contoh: Mohon dipelajari dan ditindaklanjuti pada rapat dewan guru besok..."
              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500"
            ></textarea>
          </div>

          <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <button
              type="button"
              @click="printDispositionSlip"
              class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Lembar Disposisi</span>
            </button>

            <button
              type="submit"
              :disabled="submitting"
              class="px-5 py-2.5 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white shadow-sm disabled:opacity-50 cursor-pointer"
            >
              {{ submitting ? 'Menyimpan...' : 'Simpan Disposisi' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import {
  FileText,
  Plus,
  Send,
  Inbox,
  AlertCircle,
  Award,
  Search,
  RefreshCw,
  Share2,
  Pencil,
  Trash2,
  FileCheck,
  Printer,
  X,
  UserCheck
} from 'lucide-vue-next';
import { api } from '../api';
import { useToast } from '../composables/useToast';

const toast = useToast();

const activeTab = ref('incoming');
const loading = ref(false);
const submitting = ref(false);
const generatingCert = ref(false);
const agendaPrintType = ref('incoming');

const letters = ref([]);
const stats = ref({});
const studentList = ref([]);
const appSettings = ref({});

const filters = reactive({
  search: '',
  status: 'all',
  category: 'all'
});

const showModal = ref(false);
const editingLetter = ref(null);
const uploadedFile = ref(null);

const form = reactive({
  type: 'incoming',
  reference_number: '',
  sender: '',
  recipient: '',
  subject: '',
  letter_date: new Date().toISOString().split('T')[0],
  category: 'Dinas'
});

const showDispositionModal = ref(false);
const selectedLetter = ref(null);
const dispositionForm = reactive({
  disposition_to: '',
  disposition_notes: ''
});

const certForm = reactive({
  student_id: '',
  purpose: 'Persyaratan Beasiswa PIP / Tunjangan Pendidikan',
  letter_date: new Date().toISOString().split('T')[0]
});

const previewCertData = ref(null);

const selectedStudentPreview = computed(() => {
  if (!certForm.student_id) return null;
  return studentList.value.find(s => s.id == certForm.student_id);
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 15,
});

let debounceTimer = null;
function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchLetters(1);
  }, 350);
}

async function fetchLetters(page = 1) {
  loading.value = true;
  try {
    const params = {
      type: activeTab.value === 'outgoing' ? 'outgoing' : 'incoming',
      search: filters.search || undefined,
      status: filters.status !== 'all' ? filters.status : undefined,
      page: page,
    };
    const res = await api.get('admin/letters', { params });
    const data = res?.data || res;
    letters.value = data?.letters?.data || data?.letters || [];
    stats.value = data?.stats || {};
    if (data?.letters) {
      pagination.current_page = data.letters.current_page || 1;
      pagination.last_page = data.letters.last_page || 1;
      pagination.total = data.letters.total || letters.value.length;
      pagination.per_page = data.letters.per_page || 15;
    }
  } catch (error) {
    console.error('Error fetching letters:', error);
    toast.error('Gagal memuat data persuratan.');
  } finally {
    loading.value = false;
  }
}

async function loadInitialData() {
  try {
    const [publicRes, studentsRes] = await Promise.all([
      api.get('/public'),
      api.get('admin/students', { params: { per_page: 500 } })
    ]);
    appSettings.value = publicRes?.settings || publicRes || {};
    studentList.value = studentsRes?.data?.students?.data || studentsRes?.data || studentsRes || [];
  } catch (err) {
    console.error('Failed loading initial settings or students', err);
  }
}

function openAddModal(type = 'incoming') {
  editingLetter.value = null;
  uploadedFile.value = null;
  form.type = type;
  form.reference_number = '';
  form.sender = type === 'outgoing' ? (appSettings.value?.app_name || 'MTs Al-Hasanah') : '';
  form.recipient = type === 'incoming' ? 'Kepala Madrasah' : '';
  form.subject = '';
  form.letter_date = new Date().toISOString().split('T')[0];
  form.category = 'Dinas';
  showModal.value = true;
}

function openEditModal(item) {
  editingLetter.value = item;
  uploadedFile.value = null;
  form.type = item.type;
  form.reference_number = item.reference_number;
  form.sender = item.sender;
  form.recipient = item.recipient;
  form.subject = item.subject;
  form.letter_date = item.letter_date;
  form.category = item.category;
  showModal.value = true;
}

function handleFileUpload(e) {
  uploadedFile.value = e.target.files[0] || null;
}

async function submitLetterForm() {
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('type', form.type);
    formData.append('reference_number', form.reference_number || '');
    formData.append('sender', form.sender || '');
    formData.append('recipient', form.recipient || '');
    formData.append('subject', form.subject);
    formData.append('letter_date', form.letter_date);
    formData.append('category', form.category);
    if (uploadedFile.value) {
      formData.append('file', uploadedFile.value);
    }

    if (editingLetter.value) {
      await api.post(`admin/letters/${editingLetter.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Data surat berhasil diperbarui!');
    } else {
      await api.post('admin/letters', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Surat baru berhasil dicatat dalam buku agenda!');
    }

    showModal.value = false;
    fetchLetters();
  } catch (error) {
    console.error('Error submitting letter:', error);
    toast.error('Gagal menyimpan data surat.');
  } finally {
    submitting.value = false;
  }
}

function openDispositionModal(item) {
  selectedLetter.value = item;
  dispositionForm.disposition_to = item.disposition_to || '';
  dispositionForm.disposition_notes = item.disposition_notes || '';
  showDispositionModal.value = true;
}

async function submitDisposition() {
  if (!selectedLetter.value) return;
  submitting.value = true;
  try {
    await api.post(`admin/letters/${selectedLetter.value.id}/disposition`, dispositionForm);
    toast.success('Lembar disposisi berhasil disimpan!');
    showDispositionModal.value = false;
    fetchLetters();
  } catch (error) {
    console.error('Error submitting disposition:', error);
    toast.error('Gagal menyimpan disposisi.');
  } finally {
    submitting.value = false;
  }
}

async function generateCertificate() {
  if (!certForm.student_id) return;
  generatingCert.value = true;
  try {
    const res = await api.post('admin/letters/generate-certificate', certForm);
    const data = res?.data || res;
    previewCertData.value = data?.letter || {};
    toast.success('Surat Keterangan Aktif Siswa berhasil diterbitkan!');
    printDocument('printable-student-cert', 'Surat Keterangan Aktif Siswa');
  } catch (error) {
    console.error('Error generating cert:', error);
    toast.error('Gagal menerbitkan surat keterangan siswa.');
  } finally {
    generatingCert.value = false;
  }
}

function onSelectCertStudent() {
  // auto trigger preview update
}

async function deleteLetter(item) {
  if (!confirm(`Yakin ingin menghapus surat nomor agenda "${item.agenda_number}"?`)) return;
  try {
    await api.delete(`admin/letters/${item.id}`);
    toast.success('Surat berhasil dihapus.');
    fetchLetters();
  } catch (error) {
    console.error('Error deleting letter:', error);
    toast.error('Gagal menghapus surat.');
  }
}

function formatDate(d) {
  if (!d) return '-';
  const date = new Date(d);
  if (isNaN(date.getTime())) return d;
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
}

function getStorageUrl(path) {
  if (!path) return '#';
  if (path.startsWith('http')) return path;
  if (path.startsWith('/storage/')) return path;
  return '/storage/' + path.replace(/^\//, '');
}

function printDocument(elemId, title = 'Dokumen') {
  const elem = document.getElementById(elemId);
  if (!elem) return;
  const printWin = window.open('', '_blank');
  printWin.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>${title} - ${appSettings.value?.app_name || 'MTs Al-Hasanah'}</title>
        <style>
          @page { size: A4 portrait; margin: 15mm; }
          body { font-family: 'Times New Roman', serif; color: #000; margin: 0; padding: 10px; }
          table { width: 100%; border-collapse: collapse; }
          .text-center { text-align: center; }
          .text-justify { text-align: justify; }
          .font-bold { font-weight: bold; }
          .uppercase { text-transform: uppercase; }
        </style>
      </head>
      <body>
        ${elem.innerHTML}
      </body>
    </html>
  `);
  printWin.document.close();
  printWin.focus();
  setTimeout(() => {
    printWin.print();
    printWin.close();
  }, 400);
}

function printDispositionSlip() {
  if (!selectedLetter.value) return;
  const letter = selectedLetter.value;
  const printWin = window.open('', '_blank');
  printWin.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Lembar Disposisi - ${letter.agenda_number}</title>
        <style>
          @page { size: A5 portrait; margin: 10mm; }
          body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
          .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
          table { width: 100%; border-collapse: collapse; margin-top: 8px; }
          th, td { border: 1px solid #000; padding: 6px 8px; font-size: 11px; }
          .title { font-size: 14px; font-weight: bold; text-align: center; margin: 8px 0; }
        </style>
      </head>
      <body>
        <div class="header">
          <h3 style="margin:0">${appSettings.value?.app_name || 'MADRASAH TSANAWIYAH AL - HASANAH'}</h3>
          <p style="margin:2px 0 0 0;font-size:10px">${appSettings.value?.school_address || 'Jl. Raya Ciomas No. 123, Kab. Bogor'}</p>
        </div>
        <div class="title">LEMBAR DISPOSISI KEPALA MADRASAH</div>
        <table>
          <tr>
            <td width="30%"><b>No. Agenda</b></td>
            <td>${letter.agenda_number}</td>
          </tr>
          <tr>
            <td><b>No. Surat Asal</b></td>
            <td>${letter.reference_number || '-'}</td>
          </tr>
          <tr>
            <td><b>Tanggal Surat</b></td>
            <td>${formatDate(letter.letter_date)}</td>
          </tr>
          <tr>
            <td><b>Asal Pengirim</b></td>
            <td>${letter.sender}</td>
          </tr>
          <tr>
            <td><b>Perihal</b></td>
            <td>${letter.subject}</td>
          </tr>
          <tr>
            <td><b>Diteruskan Kepada</b></td>
            <td><b>${dispositionForm.disposition_to || letter.disposition_to || '-'}</b></td>
          </tr>
          <tr>
            <td colspan="2">
              <b>Instruksi / Catatan Kepala Madrasah:</b><br><br>
              ${dispositionForm.disposition_notes || letter.disposition_notes || '-'}<br><br><br>
            </td>
          </tr>
        </table>
        <div style="margin-top:20px;text-align:right">
          <p>Kepala Madrasah,</p>
          <br><br>
          <p><b>${appSettings.value?.principal_name || 'Kepala Madrasah'}</b></p>
        </div>
      </body>
    </html>
  `);
  printWin.document.close();
  printWin.focus();
  setTimeout(() => {
    printWin.print();
    printWin.close();
  }, 400);
}

onMounted(() => {
  fetchLetters();
  loadInitialData();
});
</script>
