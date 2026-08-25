<template>
  <div class="space-y-6 font-inter text-slate-800">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider mb-1">
          <UserPlus class="w-3.5 h-3.5 text-emerald-600" />
          <span>Manajemen PPDB Online</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Penerimaan Siswa Baru</h1>
        <p class="text-xs text-slate-500 font-normal">Kelola verifikasi berkas, penilaian seleksi, dan pendaftaran ke kelas aktif.</p>
      </div>

      <div class="flex items-center gap-2">
        <a
          href="/ppdb"
          target="_blank"
          class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer"
        >
          <ExternalLink class="w-4 h-4 text-slate-400" />
          <span>Buka Form Publik</span>
        </a>
        <button
          @click="fetchData"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5 cursor-pointer"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''" />
          <span>Segarkan Data</span>
        </button>
      </div>
    </div>

    <!-- Mode Selector for Admin (Pendaftar vs Pengaturan Panitia vs Jadwal) -->
    <div v-if="isAdmin" class="border-b border-slate-200 flex items-center gap-4">
      <button
        @click="activeAdminTab = 'applicants'"
        class="pb-3 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeAdminTab === 'applicants' ? 'text-emerald-700 border-b-2 border-emerald-600' : 'text-slate-500 hover:text-slate-800'"
      >
        <span>Daftar Calon Siswa ({{ stats.total || 0 }})</span>
      </button>
      <button
        @click="activeAdminTab = 'committee'; fetchCommitteeTeachers()"
        class="pb-3 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeAdminTab === 'committee' ? 'text-emerald-700 border-b-2 border-emerald-600' : 'text-slate-500 hover:text-slate-800'"
      >
        <span>Penugasan Panitia Guru</span>
      </button>
      <button
        @click="activeAdminTab = 'settings'; fetchPpdbSettings()"
        class="pb-3 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeAdminTab === 'settings' ? 'text-emerald-700 border-b-2 border-emerald-600' : 'text-slate-500 hover:text-slate-800'"
      >
        <span>⚙️ Jadwal & Status Pendaftaran</span>
      </button>
    </div>

    <!-- SECTION 1: APPLICANTS MANAGEMENT -->
    <div v-if="activeAdminTab === 'applicants'" class="space-y-6">
      
      <!-- Metric Stats Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-1">
          <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Pendaftar</p>
          <p class="text-2xl font-black text-slate-900">{{ stats.total || 0 }}</p>
        </div>
        <div class="bg-amber-50/60 p-4 rounded-2xl border border-amber-200 shadow-xs space-y-1">
          <p class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider">Menunggu Verifikasi</p>
          <p class="text-2xl font-black text-amber-900">{{ stats.pending || 0 }}</p>
        </div>
        <div class="bg-sky-50/60 p-4 rounded-2xl border border-sky-200 shadow-xs space-y-1">
          <p class="text-[11px] font-semibold text-sky-700 uppercase tracking-wider">Berkas Terverifikasi</p>
          <p class="text-2xl font-black text-sky-900">{{ stats.verified || 0 }}</p>
        </div>
        <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-200 shadow-xs space-y-1">
          <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Diterima / Lulus</p>
          <p class="text-2xl font-black text-emerald-900">{{ stats.accepted || 0 }}</p>
        </div>
        <div class="bg-rose-50/60 p-4 rounded-2xl border border-rose-200 shadow-xs space-y-1">
          <p class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider">Tidak Lulus</p>
          <p class="text-2xl font-black text-rose-900">{{ stats.rejected || 0 }}</p>
        </div>
        <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-200 shadow-xs space-y-1">
          <p class="text-[11px] font-semibold text-indigo-700 uppercase tracking-wider">Siswa Aktif Masuk</p>
          <p class="text-2xl font-black text-indigo-900">{{ stats.enrolled || 0 }}</p>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-3">
        
        <!-- Status Tabs / Dropdown -->
        <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
          <button
            v-for="st in statusFilters"
            :key="st.value"
            @click="selectedStatus = st.value; fetchData()"
            :class="[
              selectedStatus === st.value ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
              'px-3 py-1.5 rounded-xl text-xs whitespace-nowrap transition-all cursor-pointer'
            ]"
          >
            {{ st.label }}
          </button>
        </div>

        <!-- Search Bar -->
        <div class="relative w-full md:w-72">
          <input
            v-model="searchQuery"
            @input="debouncedSearch"
            type="text"
            placeholder="Cari nama, no reg, NISN, SD asal..."
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 pl-9 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-emerald-500 transition-all"
          />
          <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        </div>
      </div>

      <!-- Applicants Data Table -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                <th class="py-3.5 px-4">Calon Siswa</th>
                <th class="py-3.5 px-4">No. Registrasi & NISN</th>
                <th class="py-3.5 px-4">Sekolah Asal</th>
                <th class="py-3.5 px-4">Kontak Orang Tua</th>
                <th class="py-3.5 px-4 text-center">Nilai Seleksi</th>
                <th class="py-3.5 px-4 text-center">Status</th>
                <th class="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="loading">
                <td colspan="7" class="py-12 text-center text-slate-400">Memuat data calon siswa...</td>
              </tr>
              <tr v-else-if="applicants.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada calon siswa pada kriteria ini.</td>
              </tr>
              <tr v-for="app in applicants" :key="app.id" class="hover:bg-slate-50/70 transition-colors">
                
                <!-- Calon Siswa -->
                <td class="py-3.5 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0 text-emerald-700 font-bold overflow-hidden">
                      <img v-if="app.photo_url" :src="app.photo_url" class="w-full h-full object-cover" />
                      <span v-else>{{ app.full_name?.charAt(0) }}</span>
                    </div>
                    <div>
                      <div class="font-bold text-slate-900 hover:text-emerald-700 cursor-pointer" @click="openDetail(app)">
                        {{ app.full_name }}
                      </div>
                      <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                        <span>{{ app.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        <span>&bull;</span>
                        <span>{{ app.birth_place || '-' }}</span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- No Reg & NISN -->
                <td class="py-3.5 px-4">
                  <div class="font-bold text-slate-900 font-mono">{{ app.registration_number }}</div>
                  <div class="text-[11px] text-slate-400">NISN: {{ app.nisn || '-' }}</div>
                </td>

                <!-- Asal Sekolah -->
                <td class="py-3.5 px-4 font-medium text-slate-700">
                  {{ app.previous_school || '-' }}
                </td>

                <!-- Kontak Orang Tua -->
                <td class="py-3.5 px-4">
                  <div class="font-semibold text-slate-800">{{ app.father_name || app.mother_name || 'Orang Tua' }}</div>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[11px] text-slate-500 font-mono">{{ app.phone }}</span>
                    <a
                      v-if="app.phone"
                      :href="`https://wa.me/${cleanPhone(app.phone)}?text=Halo%20Bapak/Ibu%20Wali%20dari%20${encodeURIComponent(app.full_name)},%20kami%20dari%20Panitia%20PPDB%20MTs%20Al-Hasanah.`"
                      target="_blank"
                      class="text-emerald-600 hover:text-emerald-800 p-0.5 rounded hover:bg-emerald-50"
                      title="Kirim Pesan WhatsApp"
                    >
                      <MessageSquare class="w-3.5 h-3.5" />
                    </a>
                  </div>
                </td>

                <!-- Nilai Seleksi -->
                <td class="py-3.5 px-4 text-center">
                  <span v-if="app.test_score !== null" class="font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                    {{ app.test_score }}
                  </span>
                  <span v-else class="text-slate-300">-</span>
                </td>

                <!-- Status -->
                <td class="py-3.5 px-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border" :class="app.status_badge_class">
                    {{ app.status_label }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-4 text-right">
                  <div class="flex items-center justify-end gap-1.5">
                    <button
                      @click="openDetail(app)"
                      class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-all cursor-pointer"
                    >
                      Review
                    </button>
                    <button
                      v-if="app.status === 'accepted'"
                      @click="openEnrollModal(app)"
                      class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center gap-1"
                    >
                      <UserCheck class="w-3.5 h-3.5" />
                      <span>Masukkan Kelas</span>
                    </button>
                  </div>
                </td>

              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- SECTION 2: COMMITTEE TEACHERS ASSIGNMENT (ADMIN ONLY) -->
    <div v-if="activeAdminTab === 'committee' && isAdmin" class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
      <div>
        <h3 class="text-base font-bold text-slate-900">Penugasan Panitia PPDB Guru</h3>
        <p class="text-xs text-slate-500">Guru yang diaktifkan sebagai panitia PPDB akan otomatis memiliki akses menu verifikasi dan seleksi calon siswa baru.</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="t in committeeTeachers"
          :key="t.id"
          class="p-4 rounded-2xl border transition-all flex items-center justify-between gap-3"
          :class="t.is_ppdb_committee ? 'bg-emerald-50/60 border-emerald-300 shadow-xs' : 'bg-slate-50/50 border-slate-200'"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-slate-200 flex items-center justify-center font-bold text-slate-700 flex-shrink-0 overflow-hidden">
              <img v-if="t.photo_url" :src="t.photo_url" class="w-full h-full object-cover" />
              <span v-else>{{ t.full_name?.charAt(0) }}</span>
            </div>
            <div class="min-w-0">
              <h4 class="font-bold text-xs text-slate-900 truncate">{{ t.full_name }}</h4>
              <p class="text-[11px] text-slate-400">{{ t.position || 'Guru Pengampu' }}</p>
            </div>
          </div>

          <button
            type="button"
            @click="toggleCommittee(t)"
            :class="[
              t.is_ppdb_committee ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600 hover:bg-slate-300',
              'px-3 py-1.5 rounded-xl text-[11px] font-bold transition-all cursor-pointer flex-shrink-0'
            ]"
          >
            {{ t.is_ppdb_committee ? 'Panitia Aktif ✓' : '+ Jadikan Panitia' }}
          </button>
        </div>
      </div>
    </div>

    <!-- SECTION 3: PPDB SCHEDULE & OPEN/CLOSE SETTINGS (ADMIN ONLY) -->
    <div v-if="activeAdminTab === 'settings' && isAdmin" class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-6 max-w-3xl">
      <div class="border-b border-slate-100 pb-4">
        <h3 class="text-base font-bold text-slate-900">Pengaturan Periode & Buka/Tutup PPDB</h3>
        <p class="text-xs text-slate-500">Atur saklar buka/tutup pendaftaran manual atau tetapkan jadwal rentang tanggal dan kuota siswa baru.</p>
      </div>

      <form @submit.prevent="savePpdbSettings" class="space-y-6">
        
        <!-- Master Status Toggle Card -->
        <div class="p-5 rounded-2xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
             :class="settingsForm.ppdb_is_open ? 'bg-emerald-50/70 border-emerald-300' : 'bg-rose-50/70 border-rose-300'">
          <div class="space-y-1">
            <div class="text-xs font-bold uppercase tracking-wider" :class="settingsForm.ppdb_is_open ? 'text-emerald-800' : 'text-rose-800'">
              Status Utama Pendaftaran: {{ settingsForm.ppdb_is_open ? '🟢 DIBUKA (Aktif)' : '🔴 DITUTUP (Nonaktif)' }}
            </div>
            <p class="text-xs text-slate-600">
              {{ settingsForm.ppdb_is_open ? 'Formulir online dapat diakses dan menerima pendaftaran baru dari publik.' : 'Formulir pendaftaran publik dikunci. Calon siswa hanya bisa mengecek status pengumuman.' }}
            </p>
          </div>

          <button
            type="button"
            @click="settingsForm.ppdb_is_open = !settingsForm.ppdb_is_open"
            class="px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition-all cursor-pointer flex-shrink-0"
            :class="settingsForm.ppdb_is_open ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white'"
          >
            {{ settingsForm.ppdb_is_open ? 'Tutup Pendaftaran' : 'Buka Pendaftaran' }}
          </button>
        </div>

        <!-- Details Inputs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2 space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Nama Gelombang / Periode</label>
            <input v-model="settingsForm.ppdb_batch_name" type="text" placeholder="Contoh: Gelombang 1 / Jalur Prestasi..." class="form-input text-xs" />
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Tanggal Mulai Buka (Opsional)</label>
            <input v-model="settingsForm.ppdb_start_date" type="date" class="form-input text-xs" />
            <p class="text-[10px] text-slate-400">Kosongkan jika pendaftaran langsung dibuka.</p>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Tanggal Batas Akhir Tutup (Opsional)</label>
            <input v-model="settingsForm.ppdb_end_date" type="date" class="form-input text-xs" />
            <p class="text-[10px] text-slate-400">Otomatis tertutup jika tanggal ini lewat.</p>
          </div>

          <div class="sm:col-span-2 space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Batas Kuota Maksimal Pendaftar (Opsional)</label>
            <input v-model="settingsForm.ppdb_quota" type="number" min="1" placeholder="Contoh: 120 (Kosongkan jika tanpa batas kuota)" class="form-input text-xs" />
            <p class="text-[10px] text-slate-400">Saat ini terdaftar: {{ stats.total || 0 }} calon siswa.</p>
          </div>

          <div class="sm:col-span-2 space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Pesan Pengumuman Saat Ditutup</label>
            <textarea
              v-model="settingsForm.ppdb_closed_message"
              rows="3"
              placeholder="Tuliskan pesan penjelasan untuk calon wali murid saat pendaftaran ditutup..."
              class="form-input text-xs"
            ></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
          <button
            type="submit"
            :disabled="savingSettings"
            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer disabled:opacity-50"
          >
            {{ savingSettings ? 'Menyimpan Pengaturan...' : 'Simpan Pengaturan PPDB' }}
          </button>
        </div>
      </form>
    </div>

    <!-- DETAIL & VERIFICATION MODAL -->
    <Transition name="fade">
      <div v-if="selectedApplicant" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 animate-scale-up">
          
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
              <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Review Calon Siswa</div>
              <h3 class="text-lg font-black text-slate-900">{{ selectedApplicant.full_name }}</h3>
            </div>
            <button @click="selectedApplicant = null" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer font-bold">&times;</button>
          </div>

          <!-- Document Thumbnails -->
          <div class="grid grid-cols-3 gap-3">
            <div class="p-3 rounded-xl border border-slate-200 text-center space-y-1.5 bg-slate-50">
              <span class="text-[10px] font-bold text-slate-400 uppercase block">Pas Foto</span>
              <a v-if="selectedApplicant.photo_url" :href="selectedApplicant.photo_url" target="_blank" class="text-xs font-bold text-emerald-700 hover:underline">Lihat Foto ↗</a>
              <span v-else class="text-xs text-slate-400">Tidak ada</span>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 text-center space-y-1.5 bg-slate-50">
              <span class="text-[10px] font-bold text-slate-400 uppercase block">Kartu Keluarga</span>
              <a v-if="selectedApplicant.family_card_url" :href="selectedApplicant.family_card_url" target="_blank" class="text-xs font-bold text-sky-700 hover:underline">Lihat KK ↗</a>
              <span v-else class="text-xs text-slate-400">Tidak ada</span>
            </div>
            <div class="p-3 rounded-xl border border-slate-200 text-center space-y-1.5 bg-slate-50">
              <span class="text-[10px] font-bold text-slate-400 uppercase block">Ijazah / SKL</span>
              <a v-if="selectedApplicant.certificate_url" :href="selectedApplicant.certificate_url" target="_blank" class="text-xs font-bold text-amber-700 hover:underline">Lihat SKL ↗</a>
              <span v-else class="text-xs text-slate-400">Tidak ada</span>
            </div>
          </div>

          <!-- Detail Fields -->
          <div class="grid grid-cols-2 gap-3.5 text-xs bg-slate-50/60 p-4 rounded-2xl border border-slate-200/60">
            <div>
              <span class="text-slate-400 block">No. Registrasi</span>
              <span class="font-bold text-slate-900 font-mono">{{ selectedApplicant.registration_number }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">NISN / NIK</span>
              <span class="font-semibold text-slate-800 font-mono">{{ selectedApplicant.nisn || '-' }} / {{ selectedApplicant.nik || '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">Tempat, Tgl Lahir</span>
              <span class="font-medium text-slate-700">{{ selectedApplicant.birth_place || '-' }}, {{ selectedApplicant.birth_date || '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">Sekolah Asal</span>
              <span class="font-medium text-slate-700">{{ selectedApplicant.previous_school || '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">Nama Orang Tua</span>
              <span class="font-medium text-slate-700">Ayah: {{ selectedApplicant.father_name || '-' }} &bull; Ibu: {{ selectedApplicant.mother_name || '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">No. WhatsApp / HP</span>
              <span class="font-bold text-emerald-700 font-mono">{{ selectedApplicant.phone || '-' }}</span>
            </div>
            <div class="col-span-2">
              <span class="text-slate-400 block">Alamat</span>
              <span class="text-slate-700">{{ selectedApplicant.address || '-' }}</span>
            </div>
          </div>

          <!-- Review Form -->
          <form @submit.prevent="saveReview" class="space-y-4 pt-2 border-t border-slate-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Status Seleksi</label>
                <select v-model="reviewForm.status" class="form-input text-xs font-semibold" required>
                  <option value="pending">Menunggu Verifikasi</option>
                  <option value="verified">Berkas Terverifikasi</option>
                  <option value="accepted">Diterima / Lulus Seleksi</option>
                  <option value="rejected">Tidak Lulus</option>
                </select>
              </div>

              <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">Nilai Tes Seleksi / Wawancara (0 - 100)</label>
                <input v-model="reviewForm.test_score" type="number" step="0.1" min="0" max="100" placeholder="Contoh: 85.5" class="form-input text-xs" />
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700">Catatan Panitia</label>
              <textarea v-model="reviewForm.notes" rows="2" placeholder="Catatan hasil tes Al-Quran, berkas yang kurang, dll..." class="form-input text-xs"></textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
              <button
                v-if="isAdmin"
                type="button"
                @click="deleteApplicant(selectedApplicant.id)"
                class="text-xs text-rose-600 hover:underline font-semibold cursor-pointer"
              >
                Hapus Data
              </button>
              <div class="flex items-center gap-2 ml-auto">
                <button
                  type="button"
                  @click="selectedApplicant = null"
                  class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl cursor-pointer"
                >
                  Tutup
                </button>
                <button
                  type="submit"
                  :disabled="savingReview"
                  class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer disabled:opacity-50"
                >
                  {{ savingReview ? 'Menyimpan...' : 'Simpan Status' }}
                </button>
              </div>
            </div>
          </form>

        </div>
      </div>
    </Transition>

    <!-- ENROLL / MASUKKAN KE KELAS MODAL -->
    <Transition name="fade">
      <div v-if="enrollApplicant" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl border border-slate-200 animate-scale-up">
          <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
              <UserCheck class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900">Daftarkan ke Siswa Aktif</h3>
              <p class="text-[11px] text-slate-500">Pilih rombongan belajar kelas awal untuk siswa ini.</p>
            </div>
          </div>

          <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1">
            <div><span class="text-slate-400">Nama Siswa:</span> <strong class="text-slate-800">{{ enrollApplicant.full_name }}</strong></div>
            <div><span class="text-slate-400">NISN / No. Reg:</span> <span class="font-mono text-slate-700">{{ enrollApplicant.nisn || enrollApplicant.registration_number }}</span></div>
          </div>

          <form @submit.prevent="submitEnroll" class="space-y-4">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700">Pilih Rombel Kelas <span class="text-rose-500">*</span></label>
              <select v-model="enrollClassId" class="form-input text-xs font-semibold" required>
                <option value="" disabled>-- Pilih Rombel Kelas 7 / Awal --</option>
                <option v-for="c in classrooms" :key="c.id" :value="c.id">
                  Kelas {{ c.name }} ({{ c.academic_year?.name || 'Aktif' }})
                </option>
              </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
              <button
                type="button"
                @click="enrollApplicant = null"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="enrolling || !enrollClassId"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer disabled:opacity-50"
              >
                {{ enrolling ? 'Mendaftarkan...' : 'Konfirmasi & Masukkan Kelas' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { api } from '../api';
import { useAuthStore } from '../stores/auth';
import { useToast } from '../composables/useToast';
import {
  UserPlus,
  RefreshCw,
  ExternalLink,
  Search,
  MessageSquare,
  UserCheck
} from 'lucide-vue-next';

const authStore = useAuthStore();
const toast = useToast();

const isAdmin = computed(() => authStore.user?.role === 'admin');
const activeAdminTab = ref('applicants');

const loading = ref(false);
const applicants = ref([]);
const stats = ref({});
const classrooms = ref([]);
const academicYears = ref([]);
const committeeTeachers = ref([]);

const selectedStatus = ref('all');
const searchQuery = ref('');

const statusFilters = [
  { label: 'Semua Status', value: 'all' },
  { label: 'Menunggu', value: 'pending' },
  { label: 'Terverifikasi', value: 'verified' },
  { label: 'Diterima', value: 'accepted' },
  { label: 'Tidak Lolos', value: 'rejected' },
  { label: 'Siswa Aktif', value: 'enrolled' },
];

const selectedApplicant = ref(null);
const reviewForm = reactive({
  status: 'pending',
  test_score: null,
  notes: '',
});
const savingReview = ref(false);

const enrollApplicant = ref(null);
const enrollClassId = ref('');
const enrolling = ref(false);

let debounceTimer = null;
function debouncedSearch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchData();
  }, 350);
}

function cleanPhone(phone) {
  if (!phone) return '';
  let clean = phone.replace(/[^0-9]/g, '');
  if (clean.startsWith('0')) clean = '62' + clean.slice(1);
  return clean;
}

async function fetchData() {
  loading.value = true;
  try {
    const endpoint = isAdmin.value ? '/admin/ppdb' : '/teacher/ppdb';
    const params = {};
    if (selectedStatus.value !== 'all') params.status = selectedStatus.value;
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim();

    const res = await api.get(endpoint, { params });
    const d = res.data || res;
    applicants.value = d.applicants?.data || d.applicants || [];
    stats.value = d.stats || {};
    classrooms.value = d.classrooms || [];
    academicYears.value = d.academic_years || [];
  } catch (err) {
    toast.error('Gagal memuat data calon siswa PPDB.');
  } finally {
    loading.value = false;
  }
}

function openDetail(app) {
  selectedApplicant.value = app;
  reviewForm.status = app.status || 'pending';
  reviewForm.test_score = app.test_score;
  reviewForm.notes = app.notes || '';
}

async function saveReview() {
  if (!selectedApplicant.value) return;
  savingReview.value = true;
  try {
    const endpoint = isAdmin.value ? `/admin/ppdb/${selectedApplicant.value.id}/process` : `/teacher/ppdb/${selectedApplicant.value.id}/process`;
    await api.post(endpoint, reviewForm);
    toast.success('Status calon siswa berhasil diperbarui!');
    selectedApplicant.value = null;
    await fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal memperbarui status seleksi.');
  } finally {
    savingReview.value = false;
  }
}

function openEnrollModal(app) {
  enrollApplicant.value = app;
  enrollClassId.value = '';
}

async function submitEnroll() {
  if (!enrollApplicant.value || !enrollClassId.value) return;
  enrolling.value = true;
  try {
    const endpoint = isAdmin.value ? `/admin/ppdb/${enrollApplicant.value.id}/enroll` : `/teacher/ppdb/${enrollApplicant.value.id}/enroll`;
    await api.post(endpoint, { class_id: enrollClassId.value });
    toast.success(`Siswa ${enrollApplicant.value.full_name} berhasil didaftarkan ke kelas!`);
    enrollApplicant.value = null;
    await fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mendaftarkan calon siswa ke kelas.');
  } finally {
    enrolling.value = false;
  }
}

async function deleteApplicant(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus data calon siswa ini?')) return;
  try {
    await api.delete(`/admin/ppdb/${id}`);
    toast.success('Data calon siswa berhasil dihapus.');
    selectedApplicant.value = null;
    await fetchData();
  } catch (err) {
    toast.error('Gagal menghapus data.');
  }
}

async function fetchCommitteeTeachers() {
  try {
    const res = await api.get('/admin/ppdb-teachers-committee');
    committeeTeachers.value = res.data || res || [];
  } catch (err) {
    toast.error('Gagal memuat data guru panitia.');
  }
}

async function toggleCommittee(t) {
  try {
    const res = await api.post(`/admin/ppdb-teachers-committee/${t.id}/toggle`, {
      is_ppdb_committee: !t.is_ppdb_committee
    });
    t.is_ppdb_committee = !t.is_ppdb_committee;
    toast.success(res.data?.message || res.message || 'Status panitia diperbarui.');
  } catch (err) {
    toast.error('Gagal memperbarui status panitia guru.');
  }
}

// PPDB Schedule & Settings
const settingsForm = reactive({
  ppdb_is_open: true,
  ppdb_batch_name: 'Gelombang 1',
  ppdb_start_date: '',
  ppdb_end_date: '',
  ppdb_quota: null,
  ppdb_closed_message: '',
});
const savingSettings = ref(false);

async function fetchPpdbSettings() {
  try {
    const res = await api.get('/admin/ppdb/settings');
    const d = res.data || res || {};
    settingsForm.ppdb_is_open = d.is_open_manual !== undefined ? !!d.is_open_manual : (d.is_open !== undefined ? !!d.is_open : true);
    settingsForm.ppdb_batch_name = d.batch_name || 'Gelombang 1';
    settingsForm.ppdb_start_date = d.start_date || '';
    settingsForm.ppdb_end_date = d.end_date || '';
    settingsForm.ppdb_quota = d.quota || null;
    settingsForm.ppdb_closed_message = d.closed_message || '';
  } catch (err) {
    console.error('Failed to load PPDB settings', err);
  }
}

async function savePpdbSettings() {
  savingSettings.value = true;
  try {
    const res = await api.post('/admin/ppdb/settings', settingsForm);
    toast.success(res.data?.message || res.message || 'Pengaturan PPDB berhasil disimpan!');
    await fetchPpdbSettings();
    await fetchData();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan pengaturan PPDB.');
  } finally {
    savingSettings.value = false;
  }
}

onMounted(() => {
  fetchData();
  if (isAdmin.value) {
    fetchPpdbSettings();
  }
});
</script>

<style scoped>
.form-input {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #cbd5e1;
  padding: 0.5rem 0.75rem;
  background-color: #ffffff;
  transition: all 0.2s ease;
}
.form-input:focus {
  border-color: #059669;
  outline: none;
  box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.15);
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
