<template>
  <div class="space-y-8 pb-16 font-inter max-w-5xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
      <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
        <!-- Photo Frame with Upload Trigger -->
        <div class="relative group">
          <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white/15 backdrop-blur-md border-2 border-white/30 overflow-hidden shadow-2xl flex items-center justify-center">
            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="Foto Siswa" />
            <div v-else class="text-4xl font-black text-white/80">
              {{ (form.full_name || user?.name || 'S').charAt(0).toUpperCase() }}
            </div>
          </div>
          <button
            type="button"
            @click="$refs.photoInput.click()"
            class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-400 text-white shadow-lg flex items-center justify-center transition-all cursor-pointer border-2 border-white"
            title="Ganti Foto Profil"
          >
            <Camera class="w-4 h-4" />
          </button>
          <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
        </div>

        <!-- Name & Badges -->
        <div class="text-center sm:text-left flex-1 space-y-2">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md">
            <GraduationCap class="w-3.5 h-3.5" />
            <span>Profil Pelajar Siswa</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black font-lexend text-white tracking-wide">
            {{ form.full_name || 'Nama Siswa' }}
          </h1>
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs font-medium text-emerald-100">
            <span class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">Kelas: {{ studentData?.class_name || studentData?.class_room?.name || studentData?.classRoom?.name || '-' }}</span>
            <span class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">NISN: {{ studentData?.nisn || '-' }}</span>
            <span class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">NIS: {{ studentData?.nis || '-' }}</span>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <button
            v-if="photoPreview && photoFile"
            type="button"
            @click="clearPhoto"
            class="px-3 py-1.5 bg-rose-500/80 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer backdrop-blur-sm shadow-sm"
          >
            Batal Ganti Foto
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-3xl border border-slate-100 shadow-sm">
      <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-xs font-bold text-slate-500 mt-3">Memuat Biodata Profil Siswa...</p>
    </div>

    <!-- Edit Form (Identik dengan Form Tambah / Edit Siswa di Admin) -->
    <form v-else ref="formRef" @submit.prevent="saveProfile" class="space-y-6">
      <!-- Error Alert Banner -->
      <div v-if="hasErrors" class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 text-red-800 shadow-sm animate-shake">
        <AlertCircle class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
        <div>
          <p class="text-xs font-bold uppercase tracking-wider">Formulir Belum Lengkap!</p>
          <p class="text-xs font-medium mt-0.5">
            Terdapat <span class="font-bold underline">{{ errorCount }} kolom wajib</span> yang masih kosong atau belum valid. Silakan periksa kolom yang ditandai merah di bawah.
          </p>
        </div>
      </div>

      <!-- 1. DATA SISWA UTAMA -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2 font-lexend">
            <User class="w-4 h-4 text-emerald-600" />
            <span>Data Siswa Utama</span>
          </h3>
          <span class="text-[11px] text-slate-400 font-semibold">* Wajib diisi</span>
        </div>

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
                'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium cursor-pointer'
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
              @input="form.nik = form.nik.replace(/[^0-9]/g, ''); clearError('nik')"
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

          <!-- No. Kartu Keluarga (KK) -->
          <div class="space-y-1.5">
            <label class="flex justify-between items-end">
              <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                No. Kartu Keluarga (KK)
              </span>
              <span class="text-[10px] font-semibold text-slate-400">16 digit angka</span>
            </label>
            <input
              v-model="form.no_kk"
              type="text"
              inputmode="numeric"
              maxlength="16"
              @input="form.no_kk = form.no_kk.replace(/[^0-9]/g, ''); clearError('no_kk')"
              :class="[
                errors.no_kk ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium font-mono'
              ]"
              placeholder="Contoh: 3201010101100005"
            />
            <p v-if="errors.no_kk" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
              <span>🔴</span> {{ errors.no_kk }}
            </p>
          </div>

          <!-- NIS (Resmi - Terdaftar) -->
          <div class="space-y-1.5">
            <label class="flex justify-between items-end">
              <span class="text-xs font-bold text-slate-600 uppercase tracking-wide flex items-center gap-1.5">
                <Lock class="w-3 h-3 text-slate-400" />
                <span>NIS Siswa</span>
              </span>
              <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">Ditetapkan Madrasah</span>
            </label>
            <input
              :value="studentData?.nis || '-'"
              type="text"
              readonly
              disabled
              class="w-full border border-slate-200 bg-slate-100/90 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-mono font-semibold cursor-not-allowed select-none"
            />
          </div>

          <!-- NISN (Resmi - Terdaftar) -->
          <div class="space-y-1.5">
            <label class="flex justify-between items-end">
              <span class="text-xs font-bold text-slate-600 uppercase tracking-wide flex items-center gap-1.5">
                <Lock class="w-3 h-3 text-slate-400" />
                <span>NISN Siswa</span>
              </span>
              <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">Ditetapkan Madrasah</span>
            </label>
            <input
              :value="studentData?.nisn || '-'"
              type="text"
              readonly
              disabled
              class="w-full border border-slate-200 bg-slate-100/90 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-mono font-semibold cursor-not-allowed select-none"
            />
          </div>

          <!-- Kelas (Resmi - Terdaftar) -->
          <div class="space-y-1.5 md:col-span-2 sm:col-span-2">
            <label class="flex justify-between items-end">
              <span class="text-xs font-bold text-slate-600 uppercase tracking-wide flex items-center gap-1.5">
                <Lock class="w-3 h-3 text-slate-400" />
                <span>Rombel / Ruang Kelas</span>
              </span>
              <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">Rombel Aktif</span>
            </label>
            <input
              :value="'Kelas ' + (studentData?.class_name || studentData?.class_room?.name || studentData?.classRoom?.name || '-')"
              type="text"
              readonly
              disabled
              class="w-full border border-slate-200 bg-slate-100/90 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-semibold cursor-not-allowed select-none"
            />
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

          <!-- Agama Siswa -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
              Agama <span class="text-red-500 font-bold">*</span>
            </label>
            <select
              v-model="form.religion"
              class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium cursor-pointer"
            >
              <option v-for="rel in religionOptions" :key="rel" :value="rel">{{ rel }}</option>
            </select>
          </div>

          <!-- Sekolah Asal -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Sekolah Asal</label>
            <input
              v-model="form.previous_school"
              type="text"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium"
              placeholder="Contoh: SMPN 1 Jakarta"
            />
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

      <!-- 2. POSISI KELUARGA & MINAT BAKAT SISWA -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2 font-lexend">
            <Sparkles class="w-4 h-4 text-emerald-600" />
            <span>Posisi Keluarga & Minat Bakat Siswa</span>
          </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 p-5 rounded-2xl border border-slate-100">
          <!-- Anak Ke -->
          <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide">Anak Ke-</label>
            <input
              v-model.number="form.child_number"
              type="number"
              min="1"
              max="30"
              class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-semibold"
              placeholder="Contoh: 1"
            />
          </div>

          <!-- Dari Jumlah Saudara -->
          <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide">Jumlah Saudara</label>
            <input
              v-model.number="form.siblings_count"
              type="number"
              min="0"
              max="30"
              class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-semibold"
              placeholder="Contoh: 2"
            />
          </div>

          <!-- Hobi Siswa -->
          <div class="space-y-1.5 sm:col-span-2 md:col-span-1">
            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide">Hobi</label>
            <select
              v-model="form.hobby"
              class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium cursor-pointer"
            >
              <option value="">-- Pilih Hobi --</option>
              <option v-for="item in hobbyOptions" :key="item" :value="item">{{ item }}</option>
              <option v-if="form.hobby && !hobbyOptions.includes(form.hobby)" :value="form.hobby">{{ form.hobby }}</option>
            </select>
          </div>

          <!-- Cita-cita Siswa -->
          <div class="space-y-1.5 sm:col-span-2 md:col-span-1">
            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wide">Cita - cita</label>
            <select
              v-model="form.aspiration"
              class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium cursor-pointer"
            >
              <option value="">-- Pilih Cita - cita --</option>
              <option v-for="item in aspirationOptions" :key="item" :value="item">{{ item }}</option>
              <option v-if="form.aspiration && !aspirationOptions.includes(form.aspiration)" :value="form.aspiration">{{ form.aspiration }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 3. INFORMASI ORANG TUA (AYAH & IBU) -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2 font-lexend">
              <Users class="w-4 h-4 text-emerald-600" />
              <span>Informasi Orang Tua (Ayah & Ibu)</span>
            </h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Status orang tua mempengaruhi pengaturan data wali otomatis.</p>
          </div>

          <!-- No WA Orang Tua & Toggle -->
          <div class="space-y-1.5 w-full sm:w-72">
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">+62</span>
              <input
                v-model="form.parent_phone"
                type="text"
                :disabled="form.has_no_phone"
                @input="clearError('parent_phone')"
                :class="[
                  form.has_no_phone ? 'border-slate-300 bg-slate-200/80 text-slate-500 cursor-not-allowed' : (errors.parent_phone ? 'border-red-500 bg-red-50/40' : 'border-slate-200 bg-slate-50'),
                  'w-full border rounded-xl pl-12 pr-4 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 transition-all font-medium'
                ]"
                :placeholder="form.has_no_phone ? 'Tidak Memiliki Telepon' : 'No. WA Orang Tua *'"
              />
            </div>
            <div class="flex items-center gap-2 pt-0.5">
              <input
                type="checkbox"
                id="has_no_phone"
                v-model="form.has_no_phone"
                @change="onToggleNoPhone"
                class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer"
              />
              <label for="has_no_phone" class="text-[11px] font-semibold text-slate-600 select-none cursor-pointer">
                Tidak memiliki nomor telepon
              </label>
            </div>
            <p v-if="errors.parent_phone && !form.has_no_phone" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
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
              <input
                v-model="form.father_name"
                type="text"
                class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium"
                placeholder="Nama lengkap ayah"
              />
              <p v-if="isParentUnavailable(form.father_status)" class="text-[10px] text-amber-600 font-semibold mt-0.5">
                💡 Cukup isi nama saja (Status: {{ form.father_status === 'meninggal' ? 'Meninggal Dunia' : 'Tidak Diketahui' }})
              </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Status Keberadaan</label>
                <select
                  v-model="form.father_status"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium cursor-pointer"
                  @change="onParentStatusChange('father')"
                >
                  <option value="hidup">Hidup</option>
                  <option value="meninggal">Meninggal</option>
                  <option value="tidak_diketahui">Tidak Diketahui</option>
                  <option value="pisah">Pisah / Cerai</option>
                  <option value="lainnya">Lainnya</option>
                </select>
              </div>
              <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">NIK Ayah (16 Digit)</label>
                <input
                  v-model="form.father_nik"
                  type="text"
                  inputmode="numeric"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-60 disabled:bg-slate-100/90 disabled:cursor-not-allowed font-mono"
                  maxlength="16"
                  @input="form.father_nik = form.father_nik.replace(/[^0-9]/g, '')"
                  :disabled="isParentUnavailable(form.father_status)"
                  :placeholder="isParentUnavailable(form.father_status) ? 'Tidak perlu diisi' : '16 digit NIK'"
                />
              </div>
            </div>

            <div class="space-y-1.5">
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Pekerjaan Ayah</label>
              <select
                v-model="form.father_job"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-60 disabled:bg-slate-100/90 disabled:cursor-not-allowed cursor-pointer"
                :disabled="isParentUnavailable(form.father_status)"
              >
                <option value="">{{ isParentUnavailable(form.father_status) ? 'Tidak Perlu Diisi' : 'Pilih Pekerjaan...' }}</option>
                <option v-for="job in jobOptions" :key="job" :value="job">{{ job }}</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Penghasilan / Bulan</label>
              <select
                v-model="form.father_income"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-60 disabled:bg-slate-100/90 disabled:cursor-not-allowed cursor-pointer"
                :disabled="isParentUnavailable(form.father_status)"
              >
                <option value="">{{ isParentUnavailable(form.father_status) ? 'Tidak Perlu Diisi' : 'Pilih Rentang Penghasilan...' }}</option>
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
              <input
                v-model="form.mother_name"
                type="text"
                class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium"
                placeholder="Nama lengkap ibu"
              />
              <p v-if="isParentUnavailable(form.mother_status)" class="text-[10px] text-amber-600 font-semibold mt-0.5">
                💡 Cukup isi nama saja (Status: {{ form.mother_status === 'meninggal' ? 'Meninggal Dunia' : 'Tidak Diketahui' }})
              </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Status Keberadaan</label>
                <select
                  v-model="form.mother_status"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium cursor-pointer"
                  @change="onParentStatusChange('mother')"
                >
                  <option value="hidup">Hidup</option>
                  <option value="meninggal">Meninggal</option>
                  <option value="tidak_diketahui">Tidak Diketahui</option>
                  <option value="pisah">Pisah / Cerai</option>
                  <option value="lainnya">Lainnya</option>
                </select>
              </div>
              <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">NIK Ibu (16 Digit)</label>
                <input
                  v-model="form.mother_nik"
                  type="text"
                  inputmode="numeric"
                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-60 disabled:bg-slate-100/90 disabled:cursor-not-allowed font-mono"
                  maxlength="16"
                  @input="form.mother_nik = form.mother_nik.replace(/[^0-9]/g, '')"
                  :disabled="isParentUnavailable(form.mother_status)"
                  :placeholder="isParentUnavailable(form.mother_status) ? 'Tidak perlu diisi' : '16 digit NIK'"
                />
              </div>
            </div>

            <div class="space-y-1.5">
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Pekerjaan Ibu</label>
              <select
                v-model="form.mother_job"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-60 disabled:bg-slate-100/90 disabled:cursor-not-allowed cursor-pointer"
                :disabled="isParentUnavailable(form.mother_status)"
              >
                <option value="">{{ isParentUnavailable(form.mother_status) ? 'Tidak Perlu Diisi' : 'Pilih Pekerjaan...' }}</option>
                <option v-for="job in jobOptions" :key="job" :value="job">{{ job }}</option>
              </select>
            </div>

            <div class="space-y-1.5">
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wide">Penghasilan / Bulan</label>
              <select
                v-model="form.mother_income"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium disabled:opacity-60 disabled:bg-slate-100/90 disabled:cursor-not-allowed cursor-pointer"
                :disabled="isParentUnavailable(form.mother_status)"
              >
                <option value="">{{ isParentUnavailable(form.mother_status) ? 'Tidak Perlu Diisi' : 'Pilih Rentang Penghasilan...' }}</option>
                <option v-for="income in incomeOptions" :key="income" :value="income">{{ income }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. INFORMASI DATA WALI SISWA (SMART GUARDIAN AUTOFILL) -->
      <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
          <div>
            <h3 class="text-sm font-black uppercase tracking-wider text-emerald-800 flex items-center gap-2 font-lexend">
              <ShieldCheck class="w-4 h-4 text-emerald-600" />
              <span>Informasi Data Wali Siswa</span>
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
              :title="isParentUnavailable(form.father_status) ? 'Ayah Meninggal / Tidak Diketahui' : 'Gunakan data ayah sebagai wali'"
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
              :title="isParentUnavailable(form.mother_status) ? 'Ibu Meninggal / Tidak Diketahui' : 'Gunakan data ibu sebagai wali'"
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
                <span v-if="guardianMode !== 'other'" class="text-[10px] text-emerald-600 font-semibold normal-case ml-1.5">
                  (Otomatis Data {{ guardianMode === 'father' ? 'Ayah' : 'Ibu' }})
                </span>
              </label>
              <input
                v-model="form.guardian_name"
                type="text"
                :readonly="guardianMode !== 'other'"
                @input="clearError('guardian_name')"
                :class="[
                  errors.guardian_name ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : (guardianMode !== 'other' ? 'border-slate-200 bg-slate-100/90 text-slate-700 cursor-not-allowed' : 'border-slate-200 bg-white focus:ring-emerald-400/30 focus:border-emerald-400'),
                  'w-full border rounded-xl px-4 py-2.5 text-sm transition-all font-medium'
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
                :disabled="guardianMode !== 'other'"
                @change="clearError('guardian_relation')"
                :class="[
                  errors.guardian_relation ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : (guardianMode !== 'other' ? 'border-slate-200 bg-slate-100/90 text-slate-700 cursor-not-allowed' : 'border-slate-200 bg-white focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer'),
                  'w-full border rounded-xl px-4 py-2.5 text-sm transition-all font-medium'
                ]"
              >
                <option value="">Pilih Hubungan...</option>
                <option v-if="guardianMode === 'father'" value="Ayah Kandung">Ayah Kandung</option>
                <option v-if="guardianMode === 'mother'" value="Ibu Kandung">Ibu Kandung</option>
                <option value="Kakek">Kakek</option>
                <option value="Nenek">Nenek</option>
                <option value="Paman">Paman</option>
                <option value="Bibi">Bibi</option>
                <option value="Kakak Kandung">Kakak Kandung</option>
                <option value="Saudara / Kerabat">Saudara / Kerabat</option>
                <option value="Wali Panti / Pengasuh">Wali Panti / Pengasuh</option>
                <option value="Lainnya">Lainnya</option>
                <option v-if="form.guardian_relation && !['Kakek', 'Nenek', 'Paman', 'Bibi', 'Kakak Kandung', 'Saudara / Kerabat', 'Wali Panti / Pengasuh', 'Lainnya', 'Ayah Kandung', 'Ibu Kandung'].includes(form.guardian_relation)" :value="form.guardian_relation">{{ form.guardian_relation }}</option>
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
                :readonly="guardianMode !== 'other'"
                @input="form.guardian_nik = form.guardian_nik.replace(/[^0-9]/g, '')"
                :class="[
                  guardianMode !== 'other' ? 'border-slate-200 bg-slate-100/90 text-slate-700 cursor-not-allowed' : 'border-slate-200 bg-white focus:ring-emerald-400/30 focus:border-emerald-400',
                  'w-full border rounded-xl px-4 py-2.5 text-sm transition-all font-medium font-mono'
                ]"
                placeholder="Contoh: 3201012304750002"
              />
            </div>

            <!-- Pekerjaan Wali -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Pekerjaan Wali</label>
              <select
                v-model="form.guardian_job"
                :disabled="guardianMode !== 'other'"
                :class="[
                  guardianMode !== 'other' ? 'border-slate-200 bg-slate-100/90 text-slate-700 cursor-not-allowed' : 'border-slate-200 bg-white focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer',
                  'w-full border rounded-xl px-4 py-2.5 text-sm transition-all font-medium'
                ]"
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
                :readonly="guardianMode !== 'other'"
                :class="[
                  guardianMode !== 'other' ? 'border-slate-200 bg-slate-100/90 text-slate-700 cursor-not-allowed' : 'border-slate-200 bg-white focus:ring-emerald-400/30 focus:border-emerald-400',
                  'w-full border rounded-xl px-4 py-2.5 text-sm transition-all font-medium'
                ]"
                placeholder="Contoh: 081234567890"
              />
            </div>

            <!-- Penghasilan Wali -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Penghasilan Wali / Bulan</label>
              <select
                v-model="form.guardian_income"
                :disabled="guardianMode !== 'other'"
                :class="[
                  guardianMode !== 'other' ? 'border-slate-200 bg-slate-100/90 text-slate-700 cursor-not-allowed' : 'border-slate-200 bg-white focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer',
                  'w-full border rounded-xl px-4 py-2.5 text-sm transition-all font-medium'
                ]"
              >
                <option value="">Pilih Rentang Penghasilan...</option>
                <option v-for="income in incomeOptions" :key="income" :value="income">{{ income }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Save Button Card -->
      <div class="sticky bottom-6 z-20 bg-white/95 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-xs text-slate-500 font-medium">
          <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
            <CheckCircle2 class="w-4 h-4" />
          </div>
          <div>
            <p class="font-bold text-slate-800">Pastikan Seluruh Data Sudah Sesuai</p>
            <p class="text-[11px] text-slate-400">Data profil akan tersinkronisasi otomatis dengan buku induk madrasah.</p>
          </div>
        </div>

        <div class="w-full sm:w-auto flex items-center gap-3">
          <button
            type="submit"
            :disabled="saving"
            class="w-full sm:w-auto px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-emerald-600/25 flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
          >
            <Save v-if="!saving" class="w-4 h-4" />
            <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span>{{ saving ? 'Menyimpan Profil...' : 'Simpan Profil Siswa' }}</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { useAuthStore } from '../stores/auth';
import {
  User,
  GraduationCap,
  Camera,
  Users,
  ShieldCheck,
  Lock,
  Save,
  Sparkles,
  AlertCircle,
  CheckCircle2,
} from 'lucide-vue-next';

const toast = useToast();
const auth = useAuthStore();
const loading = ref(true);
const saving = ref(false);
const user = ref(null);
const studentData = ref(null);
const formRef = ref(null);
const errors = reactive({});
const guardianMode = ref('father'); // 'father' | 'mother' | 'other'

const errorCount = computed(() => Object.keys(errors).length);
const hasErrors = computed(() => errorCount.value > 0);

function clearError(field) {
  if (errors[field]) {
    delete errors[field];
  }
}

const jobOptions = [
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
];

const incomeOptions = [
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
];

const religionOptions = [
  'Islam',
  'Kristen Protestan',
  'Katolik',
  'Hindu',
  'Buddha',
  'Khonghucu',
];

const hobbyOptions = [
  'Olahraga',
  'Kesenian',
  'Membaca',
  'Menulis',
  'Jalan-jalan',
  'Lainnya',
];

const aspirationOptions = [
  'PNS',
  'TNI/Polri',
  'Guru/Dosen',
  'Dokter',
  'Politikus',
  'Wiraswasta',
  'Seniman/Artis',
  'Ilmuwan',
  'Agamawan',
  'Lainnya',
];

const form = reactive({
  full_name: '',
  gender: '',
  nik: '',
  no_kk: '',
  birth_place: '',
  birth_date: '',
  religion: 'Islam',
  previous_school: '',
  address: '',
  child_number: '',
  siblings_count: '',
  hobby: '',
  aspiration: '',
  parent_phone: '',
  has_no_phone: false,
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

function onToggleNoPhone() {
  if (form.has_no_phone) {
    form.parent_phone = '-';
    clearError('parent_phone');
  } else {
    if (form.parent_phone === '-' || form.parent_phone === 'Tidak Memiliki Telepon') {
      form.parent_phone = '';
    }
  }
}

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
  photoPreview.value = studentData.value?.photo_url || null;
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
    return 'Kedua orang tua berstatus Meninggal / Tidak Diketahui. Data kedua orang tua cukup mengisi nama saja, dan data wali wajib diisi secara manual (Wali Lainnya / Kerabat).';
  }
  if (fUn && !mUn) {
    if (guardianMode.value === 'mother') {
      return 'Ayah berstatus Meninggal / Tidak Diketahui. Data wali otomatis disinkronkan dari Data Ibu.';
    }
    return 'Ayah berstatus Meninggal / Tidak Diketahui. Data wali diisi secara manual (Wali Lainnya / Kerabat).';
  }
  if (!fUn && mUn) {
    if (guardianMode.value === 'father') {
      return 'Ibu berstatus Meninggal / Tidak Diketahui. Data wali otomatis disinkronkan dari Data Ayah.';
    }
    return 'Ibu berstatus Meninggal / Tidak Diketahui. Data wali diisi secara manual (Wali Lainnya / Kerabat).';
  }
  if (guardianMode.value === 'father') {
    return 'Kedua orang tua lengkap. Data wali tersinkronisasi otomatis dengan Data Ayah.';
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
  const fUn = isParentUnavailable(form.father_status);
  const mUn = isParentUnavailable(form.mother_status);

  if (mode === 'father' && fUn) return;
  if (mode === 'mother' && mUn) return;

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
    if (guardianMode.value !== 'other' && guardianMode.value !== 'mother') {
      guardianMode.value = 'father';
    }
    syncGuardianData();
  }
}

// Watch parent name/job/income changes when in synced mode
watch(
  () => [
    form.father_name, form.father_nik, form.father_job, form.father_income,
    form.mother_name, form.mother_nik, form.mother_job, form.mother_income,
    form.parent_phone,
  ],
  () => {
    if (guardianMode.value === 'father' || guardianMode.value === 'mother') {
      syncGuardianData();
    }
  }
);

function validateForm() {
  Object.keys(errors).forEach((key) => delete errors[key]);

  if (!form.full_name?.trim()) errors.full_name = 'Nama lengkap siswa wajib diisi';
  if (!form.gender) errors.gender = 'Jenis kelamin wajib dipilih';

  if (!form.nik?.trim()) {
    errors.nik = 'NIK siswa wajib diisi';
  } else if (!/^\d{16}$/.test(form.nik.trim())) {
    errors.nik = 'NIK harus tepat 16 digit angka';
  }

  if (form.no_kk?.trim() && !/^\d{16}$/.test(form.no_kk.trim())) {
    errors.no_kk = 'No. KK harus berupa 16 digit angka';
  }

  if (!form.birth_place?.trim()) errors.birth_place = 'Tempat lahir wajib diisi';
  if (!form.birth_date) errors.birth_date = 'Tanggal lahir wajib diisi';
  if (!form.address?.trim()) errors.address = 'Alamat lengkap siswa wajib diisi';

  if (!form.has_no_phone) {
    if (!form.parent_phone?.trim() || form.parent_phone === '-') {
      errors.parent_phone = 'No. HP orang tua wajib diisi (centang jika tidak memiliki HP)';
    }
  }

  if (!form.guardian_name?.trim()) errors.guardian_name = 'Nama wali wajib diisi';
  if (!form.guardian_relation) errors.guardian_relation = 'Hubungan wali dengan siswa wajib dipilih';

  return Object.keys(errors).length === 0;
}

async function loadProfile() {
  loading.value = true;
  try {
    const res = await api.get('student/profile');
    const data = res?.data || res || {};
    user.value = data.user || {};
    studentData.value = data.student || {};

    const s = studentData.value;
    form.full_name = s.full_name || user.value.name || '';
    form.gender = s.gender || '';
    form.nik = s.nik || '';
    form.no_kk = s.no_kk || '';
    form.birth_place = s.birth_place || '';
    form.birth_date = s.birth_date ? String(s.birth_date).substring(0, 10) : '';
    form.religion = s.religion || 'Islam';
    form.previous_school = s.previous_school || '';
    form.address = s.address || '';
    form.child_number = s.child_number ?? '';
    form.siblings_count = s.siblings_count ?? '';
    form.hobby = s.hobby || '';
    form.aspiration = s.aspiration || '';
    form.parent_phone = s.parent_phone || '';
    form.has_no_phone = (s.parent_phone === '-' || s.parent_phone === 'Tidak Memiliki Telepon');

    form.father_name = s.father_name || '';
    form.father_status = s.father_status || 'hidup';
    form.father_nik = s.father_nik || '';
    form.father_job = s.father_job || '';
    form.father_income = s.father_income || '';

    form.mother_name = s.mother_name || '';
    form.mother_status = s.mother_status || 'hidup';
    form.mother_nik = s.mother_nik || '';
    form.mother_job = s.mother_job || '';
    form.mother_income = s.mother_income || '';

    form.guardian_name = s.guardian_name || '';
    form.guardian_relation = s.guardian_relation || '';
    form.guardian_nik = s.guardian_nik || '';
    form.guardian_job = s.guardian_job || '';
    form.guardian_phone = s.guardian_phone || '';
    form.guardian_income = s.guardian_income || '';

    // Smart Guardian Mode Setup
    const fUn = isParentUnavailable(form.father_status);
    const mUn = isParentUnavailable(form.mother_status);

    if (fUn && mUn) {
      guardianMode.value = 'other';
    } else if (form.guardian_relation === 'Ibu Kandung') {
      guardianMode.value = mUn ? (fUn ? 'other' : 'father') : 'mother';
    } else if (form.guardian_relation === 'Ayah Kandung') {
      guardianMode.value = fUn ? (mUn ? 'other' : 'mother') : 'father';
    } else if (s.guardian_name && form.guardian_relation) {
      guardianMode.value = 'other';
    } else {
      guardianMode.value = !fUn ? 'father' : (!mUn ? 'mother' : 'other');
      syncGuardianData();
    }

    photoPreview.value = s.photo_url || null;
    photoFile.value = null;
  } catch (err) {
    console.error('Error loading student profile:', err);
    toast.error('Gagal memuat profil siswa.');
  } finally {
    loading.value = false;
  }
}

async function saveProfile() {
  const isValid = validateForm();

  if (!isValid) {
    if (formRef.value) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    return;
  }

  saving.value = true;
  try {
    if (form.has_no_phone) {
      form.parent_phone = '-';
    }

    const fields = [
      'full_name', 'gender', 'nik', 'no_kk', 'birth_place', 'birth_date', 'religion',
      'previous_school', 'address', 'child_number', 'siblings_count', 'hobby', 'aspiration',
      'parent_phone',
      'father_name', 'father_status', 'father_nik', 'father_job', 'father_income',
      'mother_name', 'mother_status', 'mother_nik', 'mother_job', 'mother_income',
      'guardian_name', 'guardian_relation', 'guardian_nik', 'guardian_job', 'guardian_phone', 'guardian_income',
    ];

    const formData = new FormData();
    fields.forEach((key) => {
      const val = form[key];
      if (val !== null && val !== undefined) {
        formData.append(key, val);
      }
    });

    if (photoFile.value) {
      formData.append('photo', photoFile.value);
    }

    const res = await api.postForm('student/profile', formData);
    toast.success(res?.message || 'Profil berhasil diperbarui!');

    if (auth.user && form.full_name) {
      auth.user.name = form.full_name;
    }

    await loadProfile();
  } catch (err) {
    console.error('Error saving student profile:', err);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadProfile();
});
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
