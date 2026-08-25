<template>
  <div class="min-h-screen bg-slate-50 font-inter text-slate-800 flex flex-col justify-between selection:bg-emerald-500 selection:text-white">
    
    <!-- Navbar Header -->
    <header class="bg-[#032218] border-b border-emerald-900/80 sticky top-0 z-40 text-white backdrop-blur-md">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between">
        <RouterLink to="/" class="flex items-center gap-3 group">
          <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-900/80 border border-emerald-700/60 flex items-center justify-center p-1.5 shadow-inner group-hover:scale-105 transition-transform">
            <img v-if="info?.school_logo" :src="getStorageUrl(info.school_logo)" class="w-full h-full object-contain" alt="Logo" />
            <School v-else class="w-5 h-5 text-emerald-300" />
          </div>
          <div class="flex flex-col">
            <span class="font-extrabold text-sm sm:text-base tracking-wider uppercase group-hover:text-emerald-300 transition-colors">
              {{ info?.school_name || 'MTs AL - HASANAH' }}
            </span>
            <span class="text-[10px] text-emerald-400 font-semibold uppercase tracking-wider">
              Portal PPDB Online
            </span>
          </div>
        </RouterLink>

        <div class="flex items-center gap-3">
          <RouterLink
            to="/"
            class="text-xs font-semibold text-emerald-200 hover:text-white hover:bg-emerald-900/60 px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5"
          >
            <ArrowLeft class="w-4 h-4" />
            <span>Kembali ke Beranda</span>
          </RouterLink>
          <RouterLink
            to="/login"
            class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl shadow-md shadow-emerald-600/20 transition-all hover:scale-105"
          >
            <LogIn class="w-3.5 h-3.5" />
            <span>Login SIAKAD</span>
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-12 w-full flex-grow space-y-8">
      
      <!-- Top Banner Headline -->
      <div class="text-center max-w-2xl mx-auto space-y-2.5">
        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold uppercase tracking-wider">
          <Sparkles class="w-3.5 h-3.5 text-emerald-600" />
          <span>Tahun Ajaran {{ info?.active_academic_year?.name || info?.active_academic_year?.year || '2026/2027' }}</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
          Penerimaan Peserta Didik Baru (PPDB)
        </h1>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
          Daftarkan putra-putri Anda secara online dengan mudah, cepat, dan transparan melalui portal resmi madrasah.
        </p>
      </div>

      <!-- Mode Selector Tabs -->
      <div class="max-w-md mx-auto p-1 bg-slate-200/80 rounded-2xl flex items-center gap-1 border border-slate-300/60 shadow-inner">
        <button
          type="button"
          @click="activeTab = 'register'"
          :class="[
            activeTab === 'register' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium',
            'flex-1 py-2.5 text-xs rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer'
          ]"
        >
          <UserPlus class="w-4 h-4 text-emerald-600" />
          <span>Formulir Pendaftaran</span>
        </button>
        <button
          type="button"
          @click="activeTab = 'status'"
          :class="[
            activeTab === 'status' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium',
            'flex-1 py-2.5 text-xs rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer'
          ]"
        >
          <Search class="w-4 h-4 text-emerald-600" />
          <span>Cek Status & Pengumuman</span>
        </button>
      </div>

      <!-- PPDB CLOSED ANNOUNCEMENT BANNER (IF REGISTRATION CLOSED) -->
      <div v-if="activeTab === 'register' && info?.ppdb_status && !info?.ppdb_status?.is_open" class="bg-white rounded-3xl border border-rose-200 shadow-xl p-8 sm:p-12 text-center space-y-5 animate-fade-in">
        <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-inner">
          <AlertCircle class="w-8 h-8" />
        </div>
        <div class="space-y-2 max-w-lg mx-auto">
          <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider">
            Pendaftaran Sedang Ditutup
          </div>
          <h2 class="text-xl sm:text-2xl font-black text-slate-900">
            {{ info?.ppdb_status?.status_reason || 'Pendaftaran PPDB Belum Dibuka' }}
          </h2>
          <p class="text-xs sm:text-sm text-slate-600 leading-relaxed font-normal">
            {{ info?.ppdb_status?.closed_message || 'Pendaftaran Peserta Didik Baru saat ini belum dibuka / telah ditutup. Silakan pantau terus website resmi madrasah untuk informasi periode berikutnya.' }}
          </p>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
          <button
            type="button"
            @click="activeTab = 'status'"
            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer"
          >
            <Search class="w-4 h-4" />
            <span>Cek Status Siswa yang Sudah Mendaftar</span>
          </button>
          <a
            v-if="info?.school_phone"
            :href="`https://wa.me/${cleanPhone(info.school_phone)}?text=Halo%20Panitia%20PPDB%20MTs%20Al-Hasanah,%20saya%20ingin%20bertanya%20jadwal%20pendaftaran%20siswa%20baru.`"
            target="_blank"
            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all flex items-center gap-1.5 cursor-pointer"
          >
            <span>Hubungi Panitia via WhatsApp</span>
          </a>
        </div>
      </div>

      <!-- TAB 1: FORMULIR PENDAFTARAN (WIZARD STEPS) -->
      <div v-else-if="activeTab === 'register'" class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        
        <!-- Batch & Quota Indicator -->
        <div v-if="info?.ppdb_status" class="bg-emerald-950 text-emerald-100 px-6 py-3 flex flex-col sm:flex-row items-center justify-between text-xs font-medium gap-2">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Periode: <strong>{{ info.ppdb_status.batch_name }}</strong></span>
            <span v-if="info.ppdb_status.start_date && info.ppdb_status.end_date" class="text-emerald-300/80">
              ({{ info.ppdb_status.start_date }} s/d {{ info.ppdb_status.end_date }})
            </span>
          </div>
          <div v-if="info.ppdb_status.quota" class="text-emerald-300 text-[11px]">
            Kuota Tersedia: {{ info.ppdb_status.total_applicants }} / {{ info.ppdb_status.quota }} Siswa
          </div>
        </div>

        <!-- Step Indicators -->
        <div class="grid grid-cols-3 border-b border-slate-100 bg-slate-50/70 p-3 sm:p-4 text-center text-xs font-semibold text-slate-500">
          <div :class="currentStep >= 1 ? 'text-emerald-700 font-bold' : ''" class="flex items-center justify-center gap-2">
            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs" :class="currentStep >= 1 ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-200 text-slate-600'">1</span>
            <span class="hidden sm:inline">Biodata Siswa</span>
          </div>
          <div :class="currentStep >= 2 ? 'text-emerald-700 font-bold' : ''" class="flex items-center justify-center gap-2">
            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs" :class="currentStep >= 2 ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-200 text-slate-600'">2</span>
            <span class="hidden sm:inline">Orang Tua & Asal</span>
          </div>
          <div :class="currentStep >= 3 ? 'text-emerald-700 font-bold' : ''" class="flex items-center justify-center gap-2">
            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs" :class="currentStep >= 3 ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-200 text-slate-600'">3</span>
            <span class="hidden sm:inline">Berkas & Selesai</span>
          </div>
        </div>

        <form @submit.prevent="submitRegistration" class="p-6 sm:p-10 space-y-6">
          
          <!-- STEP 1: BIODATA CALON SISWA -->
          <div v-show="currentStep === 1" class="space-y-5 animate-fade-in">
            <div class="border-b border-slate-100 pb-3">
              <h3 class="text-base font-bold text-slate-900">Data Pribadi Calon Siswa</h3>
              <p class="text-xs text-slate-500">Isi data sesuai dengan yang tertera pada Akta Kelahiran atau Kartu Keluarga.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2 space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                <input v-model="form.full_name" type="text" placeholder="Masukkan nama lengkap calon siswa..." class="form-input text-xs" required />
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">NISN (Nomor Induk Siswa Nasional)</label>
                <input v-model="form.nisn" type="text" placeholder="10 digit NISN dari SD/MI..." class="form-input text-xs" />
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">NIK (Nomor Induk Kependudukan)</label>
                <input v-model="form.nik" type="text" placeholder="16 digit NIK pada Kartu Keluarga..." class="form-input text-xs" />
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Jenis Kelamin <span class="text-rose-500">*</span></label>
                <select v-model="form.gender" class="form-input text-xs" required>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">No. WhatsApp / HP Aktif <span class="text-rose-500">*</span></label>
                <input v-model="form.phone" type="tel" placeholder="Contoh: 081234567890" class="form-input text-xs" required />
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Tempat Lahir</label>
                <input v-model="form.birth_place" type="text" placeholder="Kota/Kabupaten lahir..." class="form-input text-xs" />
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Tanggal Lahir</label>
                <input v-model="form.birth_date" type="date" class="form-input text-xs" />
              </div>

              <div class="sm:col-span-2 space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Alamat Tempat Tinggal</label>
                <textarea v-model="form.address" rows="2" placeholder="Nama jalan, RT/RW, Desa/Kelurahan, Kecamatan..." class="form-input text-xs"></textarea>
              </div>
            </div>
          <div class="flex justify-end pt-4 border-t border-slate-100">
              <button
                type="button"
                @click="goToStep(2)"
                class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
              >
                <span>Lanjut ke Data Orang Tua</span>
                <ArrowRight class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- STEP 2: ORANG TUA & SEKOLAH ASAL -->
          <div v-show="currentStep === 2" class="space-y-5 animate-fade-in">
            <div class="border-b border-slate-100 pb-3">
              <h3 class="text-base font-bold text-slate-900">Data Orang Tua & Sekolah Asal</h3>
              <p class="text-xs text-slate-500">Informasi sekolah asal SD/MI serta data ayah, ibu, atau wali murid.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2 space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Sekolah Asal (SD/MI) <span class="text-rose-500">*</span></label>
                <input v-model="form.previous_school" type="text" placeholder="Contoh: SDN 1 Kedungwuni / MI Al-Falah..." class="form-input text-xs" required />
              </div>

              <!-- Data Ayah -->
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap Ayah</label>
                <input v-model="form.father_name" type="text" placeholder="Nama ayah kandung..." class="form-input text-xs" />
              </div>
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Pekerjaan Ayah</label>
                <input v-model="form.father_job" type="text" placeholder="PNS, Wiraswasta, Petani, dll..." class="form-input text-xs" />
              </div>

              <!-- Data Ibu -->
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Nama Lengkap Ibu</label>
                <input v-model="form.mother_name" type="text" placeholder="Nama ibu kandung..." class="form-input text-xs" />
              </div>
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Pekerjaan Ibu</label>
                <input v-model="form.mother_job" type="text" placeholder="IRT, Guru, Pedagang, dll..." class="form-input text-xs" />
              </div>

              <!-- Data Wali (Opsional) -->
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Nama Wali (Jika Tinggal Bersama Wali)</label>
                <input v-model="form.guardian_name" type="text" placeholder="Nama wali murid..." class="form-input text-xs" />
              </div>
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">No. HP / WA Wali</label>
                <input v-model="form.guardian_phone" type="tel" placeholder="Nomor kontak wali..." class="form-input text-xs" />
              </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100">
              <button
                type="button"
                @click="currentStep = 1"
                class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all text-center cursor-pointer"
              >
                Kembali
              </button>
              <button
                type="button"
                @click="currentStep = 3"
                class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
              >
                <span>Lanjut ke Upload Berkas</span>
                <ArrowRight class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- STEP 3: UPLOAD BERKAS & SELESAI -->
          <div v-show="currentStep === 3" class="space-y-5 animate-fade-in">
            <div class="border-b border-slate-100 pb-3">
              <h3 class="text-base font-bold text-slate-900">Upload Dokumen Pendukung & Konfirmasi</h3>
              <p class="text-xs text-slate-500">Unggah foto calon siswa dan dokumen Kartu Keluarga / Ijazah (Format: JPG, PNG, PDF maks. 5MB).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Pas Foto -->
              <div class="p-4 border border-dashed border-slate-300 rounded-2xl text-center space-y-3 bg-slate-50/50 hover:bg-white transition-colors">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto">
                  <Camera class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="text-xs font-bold text-slate-800">Pas Foto Calon Siswa</h4>
                  <p class="text-[11px] text-slate-400">Foto formal background merah/biru</p>
                </div>
                <input type="file" accept="image/*" @change="e => files.photo = e.target.files[0]" class="text-xs text-slate-500 w-full" />
              </div>

              <!-- Kartu Keluarga -->
              <div class="p-4 border border-dashed border-slate-300 rounded-2xl text-center space-y-3 bg-slate-50/50 hover:bg-white transition-colors">
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto">
                  <FileText class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="text-xs font-bold text-slate-800">Kartu Keluarga (KK)</h4>
                  <p class="text-[11px] text-slate-400">Scan / Foto Kartu Keluarga</p>
                </div>
                <input type="file" accept="image/*,application/pdf" @change="e => files.family_card = e.target.files[0]" class="text-xs text-slate-500 w-full" />
              </div>

              <!-- Ijazah / SKL -->
              <div class="p-4 border border-dashed border-slate-300 rounded-2xl text-center space-y-3 bg-slate-50/50 hover:bg-white transition-colors">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                  <GraduationCap class="w-5 h-5" />
                </div>
                <div>
                  <h4 class="text-xs font-bold text-slate-800">Ijazah / SKL SD/MI</h4>
                  <p class="text-[11px] text-slate-400">Surat Keterangan Lulus / Ijazah</p>
                </div>
                <input type="file" accept="image/*,application/pdf" @change="e => files.certificate = e.target.files[0]" class="text-xs text-slate-500 w-full" />
              </div>
            </div>

            <!-- Agreement Checkbox -->
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex items-start gap-3">
              <input v-model="agreement" type="checkbox" id="ppdb-agree" class="mt-0.5 w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500" required />
              <label for="ppdb-agree" class="text-xs text-emerald-900 leading-relaxed font-medium">
                Saya menyatakan bahwa seluruh data yang diisikan dalam formulir PPDB ini adalah <strong>benar dan sesuai dengan dokumen aslinya</strong>.
              </label>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100">
              <button
                type="button"
                @click="currentStep = 2"
                class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all text-center cursor-pointer"
              >
                Kembali
              </button>
              <button
                type="submit"
                :disabled="submitting || !agreement"
                class="w-full sm:w-auto px-7 py-3.5 sm:py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <div v-if="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                <CheckCircle2 v-else class="w-4 h-4" />
                <span>{{ submitting ? 'Mengirim Pendaftaran...' : 'Kirim Pendaftaran Sekarang' }}</span>
              </button>
            </div>
          </div>

        </form>
      </div>

      <!-- TAB 2: CEK STATUS & PENGUMUMAN SELEKSI -->
      <div v-if="activeTab === 'status'" class="bg-white rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-10 space-y-8">
        <div class="text-center max-w-lg mx-auto space-y-2">
          <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto shadow-sm">
            <Search class="w-6 h-6" />
          </div>
          <h3 class="text-lg font-bold text-slate-900">Lacak Status Pendaftaran Calon Siswa</h3>
          <p class="text-xs text-slate-500">Masukkan Nomor Registrasi (misal: <code>PPDB-2026-0001</code>), NISN, atau NIK siswa untuk melihat pengumuman seleksi.</p>
        </div>

        <form @submit.prevent="searchStatus" class="max-w-md mx-auto flex items-center gap-2">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Ketik Nomor Registrasi / NISN..."
            class="form-input text-xs flex-1 uppercase"
            required
          />
          <button
            type="submit"
            :disabled="searching"
            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
          >
            <div v-if="searching" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <Search v-else class="w-3.5 h-3.5" />
            <span>Cari</span>
          </button>
        </form>

        <!-- Search Result Card -->
        <div v-if="searchResult" class="max-w-2xl mx-auto rounded-3xl border border-slate-200 overflow-hidden shadow-md bg-slate-50/50 space-y-6 p-6 animate-fade-in">
          
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div class="space-y-1">
              <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nomor Registrasi</div>
              <div class="text-lg font-extrabold text-slate-900 font-mono">{{ searchResult.registration_number }}</div>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border" :class="searchResult.status_badge_class">
              <span>● {{ searchResult.status_label }}</span>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
              <span class="text-slate-400 block font-medium">Nama Calon Siswa</span>
              <span class="font-bold text-slate-800 text-sm">{{ searchResult.full_name }}</span>
            </div>
            <div>
              <span class="text-slate-400 block font-medium">NISN / NIK</span>
              <span class="font-semibold text-slate-700 font-mono">{{ searchResult.nisn || '-' }} / {{ searchResult.nik || '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block font-medium">Sekolah Asal (SD/MI)</span>
              <span class="font-medium text-slate-700">{{ searchResult.previous_school || '-' }}</span>
            </div>
            <div>
              <span class="text-slate-400 block font-medium">Tahun Ajaran</span>
              <span class="font-medium text-slate-700">{{ searchResult.academic_year?.name || '2026/2027' }}</span>
            </div>
            <div v-if="searchResult.test_score !== null">
              <span class="text-slate-400 block font-medium">Nilai Seleksi / Tes Masuk</span>
              <span class="font-extrabold text-emerald-700 text-sm">{{ searchResult.test_score }}</span>
            </div>
            <div v-if="searchResult.enrolled_class">
              <span class="text-slate-400 block font-medium">Penempatan Rombel Kelas</span>
              <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                Kelas {{ searchResult.enrolled_class.name }}
              </span>
            </div>
          </div>

          <!-- Result Message Banner -->
          <div v-if="searchResult.status === 'accepted' || searchResult.status === 'enrolled'" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-1">
            <h4 class="font-bold text-xs flex items-center gap-1.5">
              <CheckCircle2 class="w-4 h-4 text-emerald-600" />
              <span>Selamat! Anda Dinyatakan LULUS SELEKSI</span>
            </h4>
            <p class="text-[11px] leading-relaxed text-emerald-800">
              Silakan mengunduh Kartu Bukti Registrasi dan menghubungi panitia madrasah untuk informasi daftar ulang dan pembagian kelas.
            </p>
          </div>
          <div v-else-if="searchResult.status === 'rejected'" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 space-y-1">
            <h4 class="font-bold text-xs flex items-center gap-1.5">
              <AlertCircle class="w-4 h-4 text-rose-600" />
              <span>Mohon Maaf, Anda Belum Lolos Seleksi</span>
            </h4>
            <p class="text-[11px] leading-relaxed text-rose-800">
              Terima kasih atas partisipasi dan minat Anda mendaftar di madrasah kami. Tetap semangat untuk melanjutkan ke jenjang berikutnya.
            </p>
          </div>
          <div v-else class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-1">
            <h4 class="font-bold text-xs flex items-center gap-1.5">
              <Clock class="w-4 h-4 text-amber-600" />
              <span>Berkas Pendaftaran Sedang Diverifikasi Panitia</span>
            </h4>
            <p class="text-[11px] leading-relaxed text-amber-800">
              Panitia PPDB sedang memverifikasi data dan dokumen pendaftaran Anda. Pengumuman hasil seleksi akan diumumkan pada halaman ini.
            </p>
          </div>

          <div class="flex justify-end pt-2">
            <button
              type="button"
              @click="printRegistrationCard(searchResult)"
              class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Bukti Pendaftaran</span>
            </button>
          </div>

        </div>

      </div>

    </main>

    <!-- Footer -->
    <footer class="bg-[#032218] text-emerald-200 py-8 border-t border-emerald-900/80 text-xs text-center font-inter">
      <div class="max-w-6xl mx-auto px-4 space-y-1">
        <p class="font-bold text-white uppercase">{{ info?.school_name || 'MTs AL - HASANAH' }} &bull; Panitia PPDB Online</p>
        <p class="text-emerald-400/80">{{ info?.school_address || 'Sistem Informasi Manajemen Madrasah Terpadu' }}</p>
      </div>
    </footer>

    <!-- SUCCESS REGISTRATION MODAL -->
    <Transition name="fade">
      <div v-if="registeredApplicant" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200 text-center animate-scale-up">
          <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-inner">
            <CheckCircle2 class="w-9 h-9" />
          </div>

          <div class="space-y-1.5">
            <h3 class="text-xl font-extrabold text-slate-900">Pendaftaran Berhasil Dikirim!</h3>
            <p class="text-xs text-slate-500">Simpan atau cetak nomor registrasi ini untuk melacak status kelulusan Anda.</p>
          </div>

          <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-1">
            <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Nomor Registrasi Anda</span>
            <div class="text-2xl font-black text-emerald-950 font-mono tracking-wide">{{ registeredApplicant.registration_number }}</div>
          </div>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <button
              type="button"
              @click="printRegistrationCard(registeredApplicant)"
              class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak Kartu Pendaftaran</span>
            </button>
            <button
              type="button"
              @click="registeredApplicant = null; activeTab = 'status'; searchQuery = registeredApplicant?.registration_number"
              class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all cursor-pointer"
            >
              Tutup & Cek Status
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../../api';
import { useToast } from '../../composables/useToast';
import {
  School,
  ArrowLeft,
  ArrowRight,
  Sparkles,
  UserPlus,
  Search,
  CheckCircle2,
  FileText,
  Camera,
  GraduationCap,
  AlertCircle,
  Clock,
  Printer,
  LogIn
} from 'lucide-vue-next';

const toast = useToast();
const info = ref(null);
const activeTab = ref('register');
const currentStep = ref(1);
const agreement = ref(false);
const submitting = ref(false);
const searching = ref(false);
const searchQuery = ref('');
const searchResult = ref(null);
const registeredApplicant = ref(null);

const form = reactive({
  full_name: '',
  nisn: '',
  nik: '',
  gender: 'L',
  birth_place: '',
  birth_date: '',
  address: '',
  phone: '',
  previous_school: '',
  father_name: '',
  father_job: '',
  father_phone: '',
  mother_name: '',
  mother_job: '',
  mother_phone: '',
  guardian_name: '',
  guardian_phone: '',
});

const files = reactive({
  photo: null,
  family_card: null,
  certificate: null,
});

function goToStep(step) {
  if (step === 2) {
    if (!form.full_name || !form.phone) {
      toast.error('Mohon lengkapi Nama Siswa dan No. HP/WA aktif.');
      return;
    }
  }
  currentStep.value = step;
}

function getStorageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
  const clean = path.replace(/^\/?storage\//, '').replace(/^\//, '');
  return `/storage/${clean}`;
}

function cleanPhone(phone) {
  if (!phone) return '';
  let clean = phone.replace(/[^0-9]/g, '');
  if (clean.startsWith('0')) clean = '62' + clean.slice(1);
  return clean;
}

async function submitRegistration() {
  if (!form.full_name || !form.phone) {
    toast.error('Nama Lengkap dan Nomor WhatsApp wajib diisi.');
    return;
  }

  submitting.value = true;
  try {
    const fd = new FormData();
    Object.keys(form).forEach(key => {
      if (form[key]) fd.append(key, form[key]);
    });
    if (files.photo) fd.append('photo', files.photo);
    if (files.family_card) fd.append('family_card_file', files.family_card);
    if (files.certificate) fd.append('certificate_file', files.certificate);

    const res = await api.post('/public/ppdb/register', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    registeredApplicant.value = res.data?.applicant || res.applicant;
    toast.success('Pendaftaran PPDB berhasil dikirim!');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengirim pendaftaran. Periksa kembali isian formulir.');
  } finally {
    submitting.value = false;
  }
}

async function searchStatus() {
  if (!searchQuery.value.trim()) return;
  searching.value = true;
  searchResult.value = null;
  try {
    const res = await api.get(`/public/ppdb/status/${encodeURIComponent(searchQuery.value.trim())}`);
    searchResult.value = res.data?.applicant || res.applicant;
  } catch (err) {
    toast.error(err.response?.data?.message || 'Nomor Registrasi atau NISN tidak ditemukan.');
  } finally {
    searching.value = false;
  }
}

function printRegistrationCard(app) {
  if (!app) return;
  const printWindow = window.open('', '_blank');
  const printContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Kartu Bukti Pendaftaran PPDB - ${app.registration_number}</title>
      <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 30px; color: #1e293b; }
        .header { text-align: center; border-bottom: 2px solid #047857; padding-bottom: 15px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #047857; text-transform: uppercase; font-size: 18px; }
        .header p { margin: 4px 0 0; font-size: 12px; color: #64748b; }
        .reg-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; text-align: center; border-radius: 8px; margin-bottom: 20px; }
        .reg-title { font-size: 11px; font-weight: bold; color: #166534; text-transform: uppercase; }
        .reg-num { font-size: 20px; font-weight: 900; color: #052e16; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 12px; }
        td { padding: 8px 6px; border-bottom: 1px solid #f1f5f9; }
        td.label { width: 35%; font-weight: bold; color: #475569; }
        .footer { margin-top: 30px; display: flex; justify-content: space-between; font-size: 11px; }
        .signature-box { text-align: center; width: 200px; }
        .signature-space { height: 60px; }
        @media print { button { display: none; } }
      </style>
    </head>
    <body>
      <div class="header">
        <h2>${info.value?.school_name || 'MTs AL - HASANAH'}</h2>
        <p>KARTU BUKTI PENDAFTARAN PESERTA DIDIK BARU (PPDB)</p>
        <p>${info.value?.school_address || ''}</p>
      </div>

      <div class="reg-box">
        <div class="reg-title">Nomor Registrasi PPDB</div>
        <div class="reg-num">${app.registration_number}</div>
      </div>

      <table>
        <tr><td class="label">Nama Lengkap Siswa</td><td>: <strong>${app.full_name}</strong></td></tr>
        <tr><td class="label">NISN / NIK</td><td>: ${app.nisn || '-'} / ${app.nik || '-'}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td>: ${app.gender === 'L' ? 'Laki-laki' : 'Perempuan'}</td></tr>
        <tr><td class="label">Tempat, Tgl Lahir</td><td>: ${app.birth_place || '-'}, ${app.birth_date || '-'}</td></tr>
        <tr><td class="label">Sekolah Asal</td><td>: ${app.previous_school || '-'}</td></tr>
        <tr><td class="label">Nama Orang Tua / Wali</td><td>: ${app.father_name || app.mother_name || app.guardian_name || '-'}</td></tr>
        <tr><td class="label">No. Telepon / WhatsApp</td><td>: ${app.phone || '-'}</td></tr>
        <tr><td class="label">Status Saat Ini</td><td>: <strong>${app.status_label || 'Menunggu Verifikasi'}</strong></td></tr>
      </table>

      <div class="footer">
        <div>
          <p>Dicetak pada: ${new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
          <p style="color: #64748b; font-size: 10px;">*Bawa kartu ini saat mengikuti verifikasi berkas & tes pemetaan di madrasah.</p>
        </div>
        <div class="signature-box">
          <p>Panitia PPDB Madrasah,</p>
          <div class="signature-space"></div>
          <p>( .................................................... )</p>
        </div>
      </div>

      <script>
        window.onload = function() { window.print(); }
      <\/script>
    </body>
    </html>
  `;
  printWindow.document.write(printContent);
  printWindow.document.close();
}

onMounted(async () => {
  try {
    const res = await api.get('/public/ppdb/info');
    info.value = res.data || res;
  } catch (err) {
    console.error('Failed to load PPDB info', err);
  }
});
</script>

<style scoped>
.form-input {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #cbd5e1;
  padding: 0.625rem 0.875rem;
  background-color: #ffffff;
  transition: all 0.2s ease;
}
.form-input:focus {
  border-color: #059669;
  outline: none;
  box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.15);
}

.animate-fade-in {
  animation: fadeIn 0.25s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
