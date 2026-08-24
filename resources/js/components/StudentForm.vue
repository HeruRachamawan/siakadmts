<template>
  <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-4xl max-h-full flex flex-col overflow-hidden">
      <!-- Modal Header -->
      <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-white z-10">
        <div>
          <h2 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">{{ title }}</h2>
          <p class="text-xs text-slate-400 font-medium mt-0.5">Isi seluruh kolom yang ditandai bintang merah (<span class="text-red-500 font-bold">*</span>) wajib diisi.</p>
        </div>
        <button @click="emit('close')" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <form ref="formRef" @submit.prevent="onSubmit" class="flex-1 overflow-y-auto p-8 space-y-8 custom-scrollbar">
        <!-- Form Header Alert Banner -->
        <div v-if="hasErrors" class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 text-red-800 shadow-sm animate-shake">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-xs font-bold uppercase tracking-wider">Formulir Belum Lengkap!</p>
            <p class="text-xs font-medium mt-0.5">Terdapat <span class="font-bold underline">{{ errorCount }} kolom wajib diisi</span> yang masih kosong atau belum valid. Silakan periksa kolom yang ditandai merah di bawah ini.</p>
          </div>
        </div>

        <!-- Photo Upload -->
        <div class="flex items-center gap-6">
          <div class="relative flex-shrink-0">
            <div class="w-24 h-24 rounded-2xl overflow-hidden bg-slate-100 border-2 border-dashed border-slate-200 flex items-center justify-center">
              <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="Foto Siswa" />
              <div v-else class="flex flex-col items-center gap-1 text-slate-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="text-[9px] font-bold">FOTO</span>
              </div>
            </div>
            <button type="button" @click="$refs.photoInput.click()" class="absolute -bottom-2 -right-2 w-7 h-7 rounded-full bg-emerald-500 text-white shadow-md flex items-center justify-center hover:bg-emerald-600 transition-colors cursor-pointer">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14M5 12h14"/></svg>
            </button>
            <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
          </div>
          <div>
            <p class="text-sm font-bold text-slate-700">Foto Siswa</p>
            <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, GIF, WebP. Maks 2MB.</p>
            <button v-if="photoPreview" type="button" @click="clearPhoto" class="mt-2 text-[11px] font-bold text-red-500 hover:text-red-600 transition-colors cursor-pointer">Hapus Foto</button>
          </div>
        </div>

        <div class="border-t border-slate-100"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
          <!-- Data Utama -->
          <div class="md:col-span-2">
            <h3 class="text-[13px] font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
              Data Siswa Utama
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
              <!-- Nama Lengkap -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
                  Nama Lengkap <span class="text-red-500 font-bold">*</span>
                </label>
                <input
                  v-model="form.full_name"
                  type="text"
                  @input="clearError('full_name')"
                  :class="[
                    errors.full_name ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
                  ]"
                  placeholder="Masukkan nama lengkap siswa"
                />
                <p v-if="errors.full_name" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.full_name }}
                </p>
              </div>

              <!-- Jenis Kelamin -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
                  Jenis Kelamin <span class="text-red-500 font-bold">*</span>
                </label>
                <select
                  v-model="form.gender"
                  @change="clearError('gender')"
                  :class="[
                    errors.gender ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
                  ]"
                >
                  <option value="">Pilih Jenis Kelamin...</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
                <p v-if="errors.gender" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.gender }}
                </p>
              </div>

              <!-- NIK Siswa -->
              <div class="space-y-1.5">
                <label class="flex justify-between items-end">
                  <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                    NIK Siswa (KTP / KK) <span class="text-red-500 font-bold">*</span>
                  </span>
                  <span class="text-[10px] font-semibold text-slate-400">16 digit angka</span>
                </label>
                <input
                  v-model="form.nik"
                  type="text"
                  inputmode="numeric"
                  maxlength="16"
                  @input="form.nik=form.nik.replace(/[^0-9]/g,''); clearError('nik')"
                  :class="[
                    errors.nik ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium font-mono'
                  ]"
                  placeholder="Contoh: 3201012304080001"
                />
                <p v-if="errors.nik" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.nik }}
                </p>
              </div>

              <!-- NIS -->
              <div class="space-y-1.5">
                <label class="flex justify-between items-end">
                  <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                    NIS <span class="text-red-500 font-bold">*</span>
                  </span>
                  <span class="text-[10px] font-semibold text-slate-400">1-16 digit angka</span>
                </label>
                <input
                  v-model="form.nis"
                  type="text"
                  inputmode="numeric"
                  maxlength="16"
                  @input="form.nis=form.nis.replace(/[^0-9]/g,''); clearError('nis')"
                  :class="[
                    errors.nis ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium font-mono'
                  ]"
                  placeholder="Contoh: 2026001"
                />
                <p v-if="errors.nis" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.nis }}
                </p>
              </div>

              <!-- NISN -->
              <div class="space-y-1.5">
                <label class="flex justify-between items-end">
                  <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                    NISN <span class="text-red-500 font-bold">*</span>
                  </span>
                  <span class="text-[10px] font-semibold text-slate-400">10 digit angka</span>
                </label>
                <input
                  v-model="form.nisn"
                  type="text"
                  inputmode="numeric"
                  maxlength="10"
                  @input="form.nisn=form.nisn.replace(/[^0-9]/g,''); clearError('nisn')"
                  :class="[
                    errors.nisn ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium font-mono'
                  ]"
                  placeholder="Contoh: 0051234567"
                />
                <p v-if="errors.nisn" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.nisn }}
                </p>
              </div>

              <!-- Kelas -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
                  Kelas <span class="text-red-500 font-bold">*</span>
                </label>
                <select
                  v-model="form.class_id"
                  @change="clearError('class_id')"
                  :class="[
                    errors.class_id ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
                  ]"
                >
                  <option value="">Pilih Kelas...</option>
                  <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                    {{ cls.name }} - Tingkat {{ cls.grade_level }}
                  </option>
                </select>
                <p v-if="errors.class_id" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.class_id }}
                </p>
              </div>

              <!-- Tempat Lahir -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
                  Tempat Lahir <span class="text-red-500 font-bold">*</span>
                </label>
                <input
                  v-model="form.birth_place"
                  type="text"
                  @input="clearError('birth_place')"
                  :class="[
                    errors.birth_place ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
                  ]"
                  placeholder="Kota / Kabupaten Lahir"
                />
                <p v-if="errors.birth_place" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.birth_place }}
                </p>
              </div>

              <!-- Tanggal Lahir -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
                  Tanggal Lahir <span class="text-red-500 font-bold">*</span>
                </label>
                <input
                  v-model="form.birth_date"
                  type="date"
                  @change="clearError('birth_date')"
                  :class="[
                    errors.birth_date ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
                  ]"
                />
                <p v-if="errors.birth_date" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.birth_date }}
                </p>
              </div>

              <!-- Sekolah Asal -->
              <div class="md:col-span-2 space-y-1.5">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Sekolah Asal</label>
                <input v-model="form.previous_school" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium" placeholder="Contoh: SMPN 1 Jakarta" />
              </div>

              <!-- Alamat Lengkap -->
              <div class="md:col-span-2 space-y-1.5">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
                  Alamat Lengkap Siswa <span class="text-red-500 font-bold">*</span>
                </label>
                <textarea
                  v-model="form.address"
                  rows="3"
                  @input="clearError('address')"
                  :class="[
                    errors.address ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                    'w-full border rounded-xl px-4 py-3 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium resize-none'
                  ]"
                  placeholder="Alamat domisili lengkap siswa"
                ></textarea>
                <p v-if="errors.address" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.address }}
                </p>
              </div>
            </div>
          </div>
          
          <div class="md:col-span-2 border-t border-slate-100 my-2"></div>

          <!-- Informasi Orang Tua -->
          <div class="md:col-span-2 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <h3 class="text-[13px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                  Informasi Orang Tua (Ayah & Ibu)
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Status orang tua mempengaruhi pengaturan data wali otomatis.</p>
              </div>

              <div class="space-y-1.5 w-full sm:w-64">
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">+62</span>
                  <input
                    v-model="form.parent_phone"
                    type="text"
                    @input="clearError('parent_phone')"
                    :class="[
                      errors.parent_phone ? 'border-red-500 bg-red-50/40' : 'border-slate-200 bg-slate-50',
                      'w-full border rounded-xl pl-12 pr-4 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 transition-all font-medium'
                    ]"
                    placeholder="No. WA Orang Tua *"
                  />
                </div>
                <p v-if="errors.parent_phone" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                  <span>🔴</span> {{ errors.parent_phone }}
                </p>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-8 bg-slate-50 rounded-2xl p-6 border border-slate-100">
              <!-- Ayah -->
              <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                  <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span>👨</span> Data Ayah Kandung
                  </h4>
                  <span v-if="form.father_status === 'meninggal'" class="text-[10px] font-bold px-2 py-0.5 bg-rose-100 text-rose-700 rounded-md">Meninggal</span>
                  <span v-else-if="form.father_status === 'tidak_diketahui'" class="text-[10px] font-bold px-2 py-0.5 bg-slate-200 text-slate-700 rounded-md">Tidak Diketahui</span>
                </div>
                
                <div class="space-y-1.5">
                  <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Nama Ayah</label>
                  <input v-model="form.father_name" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium" placeholder="Nama lengkap ayah" />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Status Keberadaan</label>
                    <select v-model="form.father_status" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium" @change="onParentStatusChange('father')">
                      <option value="hidup">Hidup</option>
                      <option value="meninggal">Meninggal</option>
                      <option value="tidak_diketahui">Tidak Diketahui</option>
                      <option value="pisah">Pisah / Cerai</option>
                      <option value="lainnya">Lainnya</option>
                    </select>
                  </div>
                  <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">NIK Ayah (16 Digit)</label>
                    <input v-model="form.father_nik" type="text" inputmode="numeric" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-50 disabled:bg-slate-100 font-mono" maxlength="16" @input="form.father_nik=form.father_nik.replace(/[^0-9]/g,'')" :disabled="isParentUnavailable(form.father_status)" placeholder="16 digit NIK" />
                  </div>
                </div>
                
                <div class="space-y-1.5">
                  <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Pekerjaan Ayah</label>
                  <select v-model="form.father_job" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-50 disabled:bg-slate-100" :disabled="isParentUnavailable(form.father_status)">
                    <option value="">Pilih Pekerjaan...</option>
                    <option v-for="job in jobOptions" :key="job" :value="job">{{ job }}</option>
                  </select>
                </div>
                
                <div class="space-y-1.5">
                  <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Penghasilan / Bulan</label>
                  <select v-model="form.father_income" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-50 disabled:bg-slate-100" :disabled="isParentUnavailable(form.father_status)">
                    <option value="">Pilih Rentang Penghasilan...</option>
                    <option v-for="income in incomeOptions" :key="income" :value="income">{{ income }}</option>
                  </select>
                </div>
              </div>

              <!-- Ibu -->
              <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                  <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span>👩</span> Data Ibu Kandung
                  </h4>
                  <span v-if="form.mother_status === 'meninggal'" class="text-[10px] font-bold px-2 py-0.5 bg-rose-100 text-rose-700 rounded-md">Meninggal</span>
                  <span v-else-if="form.mother_status === 'tidak_diketahui'" class="text-[10px] font-bold px-2 py-0.5 bg-slate-200 text-slate-700 rounded-md">Tidak Diketahui</span>
                </div>
                
                <div class="space-y-1.5">
                  <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Nama Ibu</label>
                  <input v-model="form.mother_name" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium" placeholder="Nama lengkap ibu" />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Status Keberadaan</label>
                    <select v-model="form.mother_status" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium" @change="onParentStatusChange('mother')">
                      <option value="hidup">Hidup</option>
                      <option value="meninggal">Meninggal</option>
                      <option value="tidak_diketahui">Tidak Diketahui</option>
                      <option value="pisah">Pisah / Cerai</option>
                      <option value="lainnya">Lainnya</option>
                    </select>
                  </div>
                  <div class="space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">NIK Ibu (16 Digit)</label>
                    <input v-model="form.mother_nik" type="text" inputmode="numeric" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-50 disabled:bg-slate-100 font-mono" maxlength="16" @input="form.mother_nik=form.mother_nik.replace(/[^0-9]/g,'')" :disabled="isParentUnavailable(form.mother_status)" placeholder="16 digit NIK" />
                  </div>
                </div>
                
                <div class="space-y-1.5">
                  <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Pekerjaan Ibu</label>
                  <select v-model="form.mother_job" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-50 disabled:bg-slate-100" :disabled="isParentUnavailable(form.mother_status)">
                    <option value="">Pilih Pekerjaan...</option>
                    <option v-for="job in jobOptions" :key="job" :value="job">{{ job }}</option>
                  </select>
                </div>
                
                <div class="space-y-1.5">
                  <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Penghasilan / Bulan</label>
                  <select v-model="form.mother_income" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-50 disabled:bg-slate-100" :disabled="isParentUnavailable(form.mother_status)">
                    <option value="">Pilih Rentang Penghasilan...</option>
                    <option v-for="income in incomeOptions" :key="income" :value="income">{{ income }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="md:col-span-2 border-t border-slate-100 my-2"></div>

          <!-- Informasi Data Wali (Smart Guardian Autofill) -->
          <div class="md:col-span-2 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <h3 class="text-[13px] font-bold text-emerald-800 uppercase tracking-widest flex items-center gap-2">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  Informasi Data Wali Siswa
                </h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Sistem secara otomatis menyesuaikan data wali berdasarkan status keberadaan orang tua.</p>
              </div>

              <!-- Mode Selector Tabs -->
              <div class="flex items-center p-1 bg-slate-100 rounded-xl border border-slate-200/80">
                <button
                  type="button"
                  @click="setGuardianMode('father')"
                  :disabled="isParentUnavailable(form.father_status)"
                  :class="[
                    guardianMode === 'father' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium',
                    'px-3 py-1.5 rounded-lg text-xs transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer'
                  ]"
                >
                  Sama dg Ayah
                </button>
                <button
                  type="button"
                  @click="setGuardianMode('mother')"
                  :disabled="isParentUnavailable(form.mother_status)"
                  :class="[
                    guardianMode === 'mother' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium',
                    'px-3 py-1.5 rounded-lg text-xs transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer'
                  ]"
                >
                  Sama dg Ibu
                </button>
                <button
                  type="button"
                  @click="setGuardianMode('other')"
                  :class="[
                    guardianMode === 'other' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium',
                    'px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer'
                  ]"
                >
                  Wali Lainnya (Manual)
                </button>
              </div>
            </div>

            <!-- Smart Feedback Notification -->
            <div v-if="guardianFeedbackNote" class="p-3 bg-emerald-50/80 border border-emerald-200/80 rounded-xl flex items-center gap-2 text-xs text-emerald-800 font-medium">
              <span class="text-base">💡</span>
              <span>{{ guardianFeedbackNote }}</span>
            </div>

            <div class="bg-emerald-50/30 rounded-2xl p-6 border border-emerald-100 space-y-5">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <!-- Nama Wali -->
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                    Nama Lengkap Wali <span class="text-red-500 font-bold">*</span>
                  </label>
                  <input
                    v-model="form.guardian_name"
                    type="text"
                    @input="clearError('guardian_name')"
                    :class="[
                      errors.guardian_name ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-white focus:ring-emerald-400/30 focus:border-emerald-400',
                      'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
                    ]"
                    placeholder="Nama lengkap wali siswa"
                  />
                  <p v-if="errors.guardian_name" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                    <span>🔴</span> {{ errors.guardian_name }}
                  </p>
                </div>

                <!-- Hubungan dengan Siswa -->
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                    Hubungan dg Siswa <span class="text-red-500 font-bold">*</span>
                  </label>
                  <select
                    v-model="form.guardian_relation"
                    @change="clearError('guardian_relation')"
                    :class="[
                      errors.guardian_relation ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-white focus:ring-emerald-400/30 focus:border-emerald-400',
                      'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
                    ]"
                  >
                    <option value="">Pilih Hubungan...</option>
                    <option value="Ayah Kandung">Ayah Kandung</option>
                    <option value="Ibu Kandung">Ibu Kandung</option>
                    <option value="Kakek">Kakek</option>
                    <option value="Nenek">Nenek</option>
                    <option value="Paman">Paman</option>
                    <option value="Bibi">Bibi</option>
                    <option value="Kakak Kandung">Kakak Kandung</option>
                    <option value="Wali Panti / Pengasuh">Wali Panti / Pengasuh</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                  <p v-if="errors.guardian_relation" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
                    <span>🔴</span> {{ errors.guardian_relation }}
                  </p>
                </div>

                <!-- NIK Wali -->
                <div class="space-y-1.5">
                  <label class="flex justify-between items-end">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">NIK Wali (16 Digit)</span>
                    <span class="text-[10px] font-semibold text-slate-400">16 digit angka</span>
                  </label>
                  <input
                    v-model="form.guardian_nik"
                    type="text"
                    inputmode="numeric"
                    maxlength="16"
                    @input="form.guardian_nik=form.guardian_nik.replace(/[^0-9]/g,'')"
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium font-mono"
                    placeholder="Contoh: 3201012304750002"
                  />
                </div>

                <!-- Pekerjaan Wali -->
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Pekerjaan Wali</label>
                  <select
                    v-model="form.guardian_job"
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium"
                  >
                    <option value="">Pilih Pekerjaan...</option>
                    <option v-for="job in jobOptions" :key="job" :value="job">{{ job }}</option>
                  </select>
                </div>

                <!-- No HP / WA Wali -->
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">No. HP / WhatsApp Wali</label>
                  <input
                    v-model="form.guardian_phone"
                    type="text"
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium"
                    placeholder="Contoh: 081234567890"
                  />
                </div>

                <!-- Penghasilan Wali -->
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Penghasilan Wali / Bulan</label>
                  <select
                    v-model="form.guardian_income"
                    class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium"
                  >
                    <option value="">Pilih Rentang Penghasilan...</option>
                    <option v-for="income in incomeOptions" :key="income" :value="income">{{ income }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Action Buttons -->
      <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 z-10">
        <button @click="emit('close')" type="button" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors shadow-xs text-sm cursor-pointer">
          Batal
        </button>
        <button type="button" @click="onSubmit" :disabled="loading" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 text-sm disabled:opacity-50 cursor-pointer">
          <svg v-if="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle>
          </svg>
          <span v-if="loading">Menyimpan...</span>
          <span v-else>Simpan Data Siswa</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { api } from '../api';

const props = defineProps({
  title: { type: String, required: true },
  model: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close', 'save']);
const loading = ref(false);
const classes = ref([]);
const errors = reactive({});
const formRef = ref(null);
const guardianMode = ref('father'); // 'father' | 'mother' | 'other'

const errorCount = computed(() => Object.keys(errors).length);
const hasErrors = computed(() => errorCount.value > 0);

function clearError(field) {
  if (errors[field]) {
    delete errors[field];
  }
}

const jobOptions = ref([
  'Tidak Bekerja',
  'Pensiunan',
  'PNS',
  'TNI/Polisi',
  'Guru/Dosen',
  'Wiraswasta',
  'Pengacara/Jaksa/Hakim/Notaris',
  'Seniman/Pelukis/Artis/Sejenis',
  'Dokter/Bidan/Perawat',
  'Pilot/Pramugara',
  'Pedagang',
  'Petani/Peternak',
  'Nelayan',
  'Buruh (Tani/Pabrik/Bangunan)',
  'Sopir/Masinis/Kondektur',
  'Politikus',
  'Lainnya',
]);

const incomeOptions = ref([
  'dibawah 800.000',
  '800.001 - 1.200.000',
  '1.200.001 - 1.800.000',
  '1.800.001 - 2.500.000',
  '2.500.001 - 3.500.000',
  '3.500.001 - 4.800.000',
  '4.800.001 - 6.500.000',
  '6.500.001 - 10.000.000',
  '10.000.001 - 20.000.000',
  'diatas 20.000.001',
]);

const form = reactive({
  nis: '',
  nisn: '',
  nik: '',
  full_name: '',
  gender: '',
  birth_place: '',
  birth_date: '',
  address: '',
  parent_phone: '',
  class_id: '',
  photo_url: '',
  previous_school: '',
  mother_name: '',
  mother_status: 'hidup',
  mother_nik: '',
  mother_job: '',
  mother_income: '',
  father_name: '',
  father_status: 'hidup',
  father_nik: '',
  father_job: '',
  father_income: '',
  guardian_name: '',
  guardian_relation: '',
  guardian_nik: '',
  guardian_job: '',
  guardian_phone: '',
  guardian_income: '',
});

const photoFile = ref(null);
const photoPreview = ref(null);

function onPhotoChange(e) {
  const file = e.target.files[0];
  if (!file) return;

  photoFile.value = file;
  photoPreview.value = URL.createObjectURL(file);
}

function clearPhoto() {
  photoFile.value = null;
  photoPreview.value = null;
  form.photo_url = '';
  if (document.querySelector('input[type="file"]')) {
    document.querySelector('input[type="file"]').value = '';
  }
}

function isParentUnavailable(status) {
  return status === 'meninggal' || status === 'tidak_diketahui';
}

const guardianFeedbackNote = computed(() => {
  const fUn = isParentUnavailable(form.father_status);
  const mUn = isParentUnavailable(form.mother_status);

  if (fUn && mUn) {
    return 'Kedua orang tua berstatus Meninggal / Tidak Diketahui (Yatim Piatu). Data wali dibuka dalam mode manual.';
  }
  if (fUn && !mUn) {
    return 'Ayah berstatus Meninggal / Tidak Diketahui. Data wali otomatis disinkronkan dengan Data Ibu.';
  }
  if (!fUn && mUn) {
    return 'Ibu berstatus Meninggal / Tidak Diketahui. Data wali otomatis disinkronkan dengan Data Ayah.';
  }
  if (guardianMode.value === 'father') {
    return 'Data wali tersinkronisasi otomatis dengan Data Ayah.';
  }
  if (guardianMode.value === 'mother') {
    return 'Data wali tersinkronisasi otomatis dengan Data Ibu.';
  }
  return 'Mode pengisian data wali secara manual (Wali Lainnya / Kerabat).';
});

function syncGuardianData() {
  if (guardianMode.value === 'father') {
    form.guardian_name = form.father_name || '';
    form.guardian_relation = 'Ayah Kandung';
    form.guardian_nik = form.father_nik || '';
    form.guardian_job = form.father_job || '';
    form.guardian_phone = form.parent_phone || '';
    form.guardian_income = form.father_income || '';
  } else if (guardianMode.value === 'mother') {
    form.guardian_name = form.mother_name || '';
    form.guardian_relation = 'Ibu Kandung';
    form.guardian_nik = form.mother_nik || '';
    form.guardian_job = form.mother_job || '';
    form.guardian_phone = form.parent_phone || '';
    form.guardian_income = form.mother_income || '';
  }
}

function setGuardianMode(mode) {
  guardianMode.value = mode;
  if (mode === 'other') {
    if (['Ayah Kandung', 'Ibu Kandung'].includes(form.guardian_relation)) {
      form.guardian_relation = '';
    }
  } else {
    syncGuardianData();
  }
}

function onParentStatusChange(type) {
  const prefix = type + '_';
  const status = form[prefix + 'status'];

  if (isParentUnavailable(status)) {
    form[prefix + 'nik'] = '';
    form[prefix + 'job'] = '';
    form[prefix + 'income'] = '';
  }

  // Evaluate Smart Guardian Autofill
  const fUn = isParentUnavailable(form.father_status);
  const mUn = isParentUnavailable(form.mother_status);

  if (fUn && mUn) {
    guardianMode.value = 'other';
    if (['Ayah Kandung', 'Ibu Kandung'].includes(form.guardian_relation)) {
      form.guardian_relation = '';
    }
  } else if (fUn && !mUn) {
    guardianMode.value = 'mother';
    syncGuardianData();
  } else if (!fUn && mUn) {
    guardianMode.value = 'father';
    syncGuardianData();
  } else {
    if (guardianMode.value === 'father' || guardianMode.value === 'mother') {
      syncGuardianData();
    }
  }
}

// Watch parent name/job/income changes when in synced mode
watch(
  () => [
    form.father_name, form.father_nik, form.father_job, form.father_income,
    form.mother_name, form.mother_nik, form.mother_job, form.mother_income,
    form.parent_phone
  ],
  () => {
    if (guardianMode.value === 'father' || guardianMode.value === 'mother') {
      syncGuardianData();
    }
  }
);

function validateForm() {
  Object.keys(errors).forEach(key => delete errors[key]);

  if (!form.full_name?.trim()) errors.full_name = 'Nama lengkap siswa wajib diisi';
  if (!form.gender) errors.gender = 'Jenis kelamin wajib dipilih';
  
  if (!form.nik?.trim()) {
    errors.nik = 'NIK siswa wajib diisi';
  } else if (!/^\d{16}$/.test(form.nik.trim())) {
    errors.nik = 'NIK harus tepat 16 digit angka';
  }

  if (!form.nis?.trim()) {
    errors.nis = 'NIS wajib diisi';
  } else if (!/^\d{1,16}$/.test(form.nis.trim())) {
    errors.nis = 'NIS harus berupa 1-16 digit angka';
  }

  if (!form.nisn?.trim()) {
    errors.nisn = 'NISN wajib diisi';
  } else if (!/^\d{10}$/.test(form.nisn.trim())) {
    errors.nisn = 'NISN harus tepat 10 digit angka';
  }

  if (!form.birth_place?.trim()) errors.birth_place = 'Tempat lahir wajib diisi';
  if (!form.birth_date) errors.birth_date = 'Tanggal lahir wajib diisi';
  if (!form.class_id) errors.class_id = 'Kelas wajib dipilih';
  if (!form.address?.trim()) errors.address = 'Alamat lengkap siswa wajib diisi';
  if (!form.parent_phone?.trim()) errors.parent_phone = 'No. HP orang tua wajib diisi';

  if (!form.guardian_name?.trim()) errors.guardian_name = 'Nama wali wajib diisi';
  if (!form.guardian_relation) errors.guardian_relation = 'Hubungan wali dengan siswa wajib dipilih';

  return Object.keys(errors).length === 0;
}

watch(
  () => props.model,
  (val) => {
    Object.keys(errors).forEach(key => delete errors[key]);
    
    form.nis = val.nis || '';
    form.nisn = val.nisn || '';
    form.nik = val.nik || '';
    form.full_name = val.full_name || '';
    form.gender = val.gender || '';
    form.birth_place = val.birth_place || '';
    form.birth_date = val.birth_date ? String(val.birth_date).substring(0, 10) : '';
    form.address = val.address || '';
    form.parent_phone = val.parent_phone || '';
    form.class_id = val.class_id ? String(val.class_id) : (val.class_room?.id ? String(val.class_room.id) : (val.classRoom?.id ? String(val.classRoom.id) : ''));
    form.photo_url = val.photo_url || '';
    form.previous_school = val.previous_school || '';

    form.father_status = val.father_status || 'hidup';
    form.mother_status = val.mother_status || 'hidup';

    ['mother_name', 'mother_nik', 'mother_job', 'mother_income',
     'father_name', 'father_nik', 'father_job', 'father_income',
     'guardian_name', 'guardian_relation', 'guardian_nik', 'guardian_job', 'guardian_phone', 'guardian_income'].forEach((key) => {
      form[key] = val[key] || '';
    });

    // Detect existing guardian mode
    if (form.guardian_relation === 'Ibu Kandung') {
      guardianMode.value = 'mother';
    } else if (form.guardian_relation === 'Ayah Kandung') {
      guardianMode.value = 'father';
    } else if (val.id && form.guardian_name) {
      guardianMode.value = 'other';
    } else {
      onParentStatusChange('father');
    }

    if (val.photo_url) {
      photoPreview.value = val.photo_url;
    } else {
      photoPreview.value = null;
    }
    photoFile.value = null;
  },
  { immediate: true }
);

onMounted(async () => {
  try {
    const res = await api.get('admin/classes');
    classes.value = res.data?.data || res.data || [];
  } catch {
    classes.value = [];
  }
});

function onSubmit() {
  const isValid = validateForm();

  if (!isValid) {
    if (formRef.value) {
      formRef.value.scrollTop = 0;
    }
    return;
  }

  loading.value = true;
  
  const useFormData = !!photoFile.value;
  const payload = useFormData ? new FormData() : {};

  const appendData = (key, value) => {
    if (useFormData) {
      payload.append(key, value);
    } else {
      payload[key] = value;
    }
  };

  const fields = [
    'nis', 'nisn', 'nik', 'full_name', 'gender', 'birth_place', 'birth_date',
    'address', 'parent_phone', 'class_id', 'previous_school',
    'mother_name', 'mother_status', 'mother_nik', 'mother_job', 'mother_income',
    'father_name', 'father_status', 'father_nik', 'father_job', 'father_income',
    'guardian_name', 'guardian_relation', 'guardian_nik', 'guardian_job', 'guardian_phone', 'guardian_income',
  ];

  fields.forEach((key) => {
    appendData(key, form[key] || '');
  });

  if (photoFile.value) {
    payload.append('photo', photoFile.value);
  }

  emit('save', { payload, isFormData: useFormData });
  loading.value = false;
}
</script>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-5px); }
  40%, 80% { transform: translateX(5px); }
}
.animate-shake {
  animation: shake 0.4s ease-in-out;
}
</style>