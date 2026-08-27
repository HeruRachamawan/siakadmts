<template>
  <div class="space-y-8 pb-12">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 rounded-[2rem] p-6 sm:p-8 text-white shadow-xl flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 border border-emerald-900/30 relative overflow-hidden">
      <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
      
      <!-- Title Section -->
      <div class="space-y-2 relative z-10 max-w-xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-full text-xs font-bold uppercase tracking-wider border border-emerald-500/30 backdrop-blur-sm">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>Monitoring Kehadiran & Geolocation Guru</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black font-lexend text-white tracking-wide">
          Presensi & Pengaturan Lokasi
        </h1>
        <p class="text-slate-400 text-xs sm:text-sm font-medium leading-relaxed">
          Atur radius pusat sekolah, cetak QR Code harian, atau scan ID Card guru di meja piket secara real-time.
        </p>
      </div>

      <!-- Action Buttons Toolbar -->
      <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full xl:w-auto relative z-10">
        <!-- 1. Scanner Kartu Guru (Piket) -->
        <button 
          @click="openTeacherCardScanner" 
          class="flex-1 sm:flex-none h-11 px-4.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-2xl text-xs font-bold transition-all shadow-lg shadow-emerald-900/30 flex items-center justify-center gap-2.5 cursor-pointer border border-emerald-400/30 hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap"
          title="Buka Scanner Cepat Kartu Guru di Meja Piket"
        >
          <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
          <span>Scanner Kartu Piket</span>
        </button>

        <!-- 2. Tampilkan / Cetak QR Sekolah -->
        <button 
          @click="openQrModal" 
          class="flex-1 sm:flex-none h-11 px-4.5 bg-slate-800/90 hover:bg-slate-700/90 text-emerald-300 hover:text-white rounded-2xl text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2.5 cursor-pointer border border-emerald-500/30 hover:border-emerald-400/50 hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap backdrop-blur-sm"
          title="Tampilkan atau Cetak QR Code Presensi Harian"
        >
          <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
          <span>QR Code Sekolah</span>
        </button>

        <!-- 3. Setting Radius & Lokasi -->
        <button 
          @click="showSettingsModal = true" 
          class="flex-1 sm:flex-none h-11 px-4.5 bg-slate-800/60 hover:bg-slate-700/80 text-slate-300 hover:text-white rounded-2xl text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2.5 cursor-pointer border border-slate-700 hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap backdrop-blur-sm"
          title="Pengaturan Titik Koordinat GPS dan Radius Presensi"
        >
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>Atur Radius & GPS</span>
        </button>

        <!-- 4. Set Hari Libur & PHBI -->
        <button 
          @click="openHolidaysModal" 
          class="flex-1 sm:flex-none h-11 px-4.5 bg-gradient-to-r from-indigo-700 to-purple-700 hover:from-indigo-600 hover:to-purple-600 text-white rounded-2xl text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2.5 cursor-pointer border border-indigo-400/40 hover:scale-[1.02] active:scale-[0.98] whitespace-nowrap"
          title="Kelola Hari Libur Mingguan, Libur Nasional, dan PHBI"
        >
          <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span>Set Hari Libur & PHBI</span>
        </button>
      </div>
    </div>

    <!-- Floating Banner Info Hari Libur (Jika tanggal yang dipilih adalah hari libur) -->
    <div v-if="holidayInfo.is_holiday" class="bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 rounded-3xl p-6 text-white shadow-xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5 border border-purple-400/40 relative overflow-hidden">
      <div class="flex items-center gap-4 relative z-10">
        <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-3xl flex-shrink-0 backdrop-blur-md border border-white/20 shadow-inner">
          🏖️
        </div>
        <div class="space-y-1">
          <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider shadow-xs">
              {{ holidayInfo.holiday_type === 'weekly_holiday' ? 'HARI LIBUR MINGGUAN' : 'HARI LIBUR NASIONAL / PHBI' }}
            </span>
            <span class="text-xs font-semibold text-purple-200 font-mono">{{ selectedDate }}</span>
          </div>
          <h3 class="text-xl font-black font-lexend text-white">{{ holidayInfo.holiday_name || 'Hari Libur Resmi' }}</h3>
          <p class="text-xs text-purple-200/90 leading-relaxed">
            Pada tanggal ini seluruh dewan guru <strong class="text-white">tidak diwajibkan melakukan presensi</strong> dan tidak dihitung alpa/terlambat.
          </p>
        </div>
      </div>
      <button
        @click="openHolidaysModal"
        class="px-5 py-2.5 bg-white text-purple-950 hover:bg-purple-50 rounded-xl text-xs font-black transition-all shadow-md active:scale-95 cursor-pointer whitespace-nowrap flex-shrink-0 relative z-10"
      >
        Kelola Kalender Libur &rarr;
      </button>
    </div>

    <!-- Date Picker & Filter Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-3 w-full sm:w-auto">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex-shrink-0">Pilih Tanggal Presensi:</label>
        <input
          type="date"
          v-model="selectedDate"
          @change="loadMonitoring"
          class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
        />
      </div>

      <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
        <span>Lokasi Sekolah: <b>{{ setting.latitude }}, {{ setting.longitude }}</b></span>
        <span>•</span>
        <span>Max Radius: <b class="text-emerald-600">{{ setting.max_radius_meters }}m</b></span>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-4">
      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Guru</p>
        <p class="text-2xl font-black text-slate-800 font-lexend">{{ summary.total_teachers || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Hadir Tepat Waktu</p>
        <p class="text-2xl font-black text-emerald-600 font-lexend">{{ summary.hadir || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Terlambat</p>
        <p class="text-2xl font-black text-amber-600 font-lexend">{{ summary.terlambat || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-teal-600 uppercase tracking-wider">Izin</p>
        <p class="text-2xl font-black text-teal-600 font-lexend">{{ summary.izin || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Sakit</p>
        <p class="text-2xl font-black text-rose-600 font-lexend">{{ summary.sakit || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Libur Sekolah</p>
        <p class="text-2xl font-black text-indigo-600 font-lexend">{{ summary.libur || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Belum Absen</p>
        <p class="text-2xl font-black text-slate-700 font-lexend">{{ summary.belum_absen || 0 }}</p>
      </div>
    </div>

    <!-- Monitoring Table Card -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h2 class="text-base font-black text-slate-800 font-lexend">Daftar Kehadiran Seluruh Guru</h2>
          <p class="text-xs text-slate-400 mt-0.5">Status kehadiran, waktu check-in/out, & jarak radius ke sekolah.</p>
        </div>

        <div class="flex items-center gap-3">
          <button 
            @click="loadMonitoring" 
            class="p-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl border border-slate-200 transition-all cursor-pointer"
            title="Refresh Data"
          >
            <svg class="w-4 h-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider text-[10px] border-b border-slate-100">
              <th class="px-6 py-4">Guru</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Masuk</th>
              <th class="px-6 py-4">Pulang</th>
              <th class="px-6 py-4">Keterangan</th>
              <th class="px-6 py-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="t in teachers" :key="t.teacher_id" class="hover:bg-slate-50/60 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <img :src="getImageUrl(t.photo_url)" class="w-9 h-9 rounded-full object-cover border border-slate-200" alt="Guru" />
                  <div>
                    <p class="font-bold text-slate-800">{{ t.full_name }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">NIP: {{ t.nip || '-' }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="[
                  t.status === 'hadir' ? 'bg-emerald-100 text-emerald-800' :
                  t.status === 'terlambat' ? 'bg-amber-100 text-amber-800' :
                  t.status === 'izin' ? 'bg-teal-100 text-teal-800' :
                  t.status === 'sakit' ? 'bg-rose-100 text-rose-800' :
                  t.status === 'tugas_luar' ? 'bg-cyan-100 text-cyan-800' :
                  t.status === 'libur' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200 font-bold' :
                  'bg-slate-100 text-slate-500',
                  'px-3 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider'
                ]">
                  {{ t.status === 'libur' ? '🏖️ Libur' : t.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="px-6 py-4 font-medium text-slate-700">
                <div v-if="t.check_in_time">
                  <p class="font-bold font-mono text-xs text-slate-800">{{ t.check_in_time }}</p>
                  <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">📍 {{ t.check_in_distance_meters }}m</p>
                </div>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="px-6 py-4 font-medium text-slate-700">
                <div v-if="t.check_out_time">
                  <p class="font-bold font-mono text-xs text-slate-800">{{ t.check_out_time }}</p>
                  <p class="text-[10px] text-teal-600 font-semibold mt-0.5">📍 {{ t.check_out_distance_meters }}m</p>
                </div>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="px-6 py-4">
                <div v-if="t.notes" class="p-2.5 bg-amber-50 border border-amber-200/80 rounded-xl text-[11px] text-amber-900 font-medium max-w-xs leading-relaxed">
                  "{{ t.notes }}"
                </div>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center gap-2">
                  <button
                    @click="openEditModal(t)"
                    title="Edit Absen Guru"
                    class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-xl text-xs transition-all border border-emerald-200/80 cursor-pointer flex items-center gap-1"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit</span>
                  </button>
                  <button
                    @click="confirmResetAttendance(t)"
                    title="Reset Absen Guru Ke Belum Absen"
                    class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded-xl text-xs transition-all border border-rose-200/80 cursor-pointer flex items-center gap-1"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reset</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Setting Radius & Koordinat -->
    <div v-if="showSettingsModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden max-h-[90vh] flex flex-col">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50 flex-shrink-0">
          <div>
            <h3 class="text-base font-black text-slate-800">Pengaturan Lokasi & Radius Sekolah</h3>
            <p class="text-xs text-slate-400 mt-0.5">Tentukan titik latitude, longitude & batas radius meter sekolah</p>
          </div>
          <button @click="showSettingsModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="saveSettings" class="p-6 space-y-4 overflow-y-auto custom-scrollbar">
          <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl space-y-2 text-xs">
            <p class="font-bold text-emerald-900">Pilih Cara Memasukkan Lokasi Pusat Sekolah:</p>
            <div class="flex flex-wrap items-center gap-2 pt-1">
              <button type="button" @click="fillCurrentLocation" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] transition-all cursor-pointer">
                📍 Gunakan GPS Saya
              </button>
              <button type="button" @click="fillFromGoogleMaps" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-[11px] transition-all cursor-pointer">
                🗺️ Ambil dari Google Maps Sekolah
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-600">Latitude Sekolah *</label>
              <input v-model="settingForm.latitude" type="number" step="any" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800" required />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-600">Longitude Sekolah *</label>
              <input v-model="settingForm.longitude" type="number" step="any" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800" required />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-600">Maksimal Radius Presensi (Meter) *</label>
            <input v-model.number="settingForm.max_radius_meters" type="number" min="1" max="500000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800" required />
            <p class="text-[10px] text-slate-400">Dapat diisi dari 1 meter hingga 500.000 meter (500 km) sesuai kebijakan sekolah.</p>
          </div>

          <!-- Interactive Google Maps Preview Box -->
          <div class="space-y-1.5 pt-2">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pratinjau Peta Interaktif Sekolah
              </label>
              <a
                :href="`https://www.google.com/maps?q=${settingForm.latitude},${settingForm.longitude}`"
                target="_blank"
                class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800 underline flex items-center gap-1"
              >
                <span>🗺️ Buka di Google Maps (Tab Baru)</span>
              </a>
            </div>

            <div class="relative w-full h-56 rounded-2xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
              <iframe
                v-if="settingForm.latitude && settingForm.longitude"
                :src="`https://maps.google.com/maps?q=${settingForm.latitude},${settingForm.longitude}&z=${dynamicMapZoom}&output=embed`"
                class="w-full h-full border-0 pointer-events-auto transition-all duration-500"
                loading="lazy"
                allowfullscreen
              ></iframe>

              <!-- Dynamic Green Circular Radius Overlay Effect -->
              <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                <div 
                  class="rounded-full bg-emerald-500/25 border-2 border-emerald-500 transition-all duration-500 ease-out shadow-[0_0_30px_rgba(16,185,129,0.6)] animate-pulse flex items-center justify-center"
                  :style="{
                    width: dynamicCirclePx + 'px',
                    height: dynamicCirclePx + 'px',
                  }"
                >
                  <span class="text-[9px] font-black text-emerald-950 bg-emerald-100/90 px-2 py-0.5 rounded-full shadow-xs backdrop-blur-xs font-mono">
                    {{ settingForm.max_radius_meters >= 1000 ? (settingForm.max_radius_meters / 1000) + ' km' : settingForm.max_radius_meters + ' m' }}
                  </span>
                </div>
              </div>
              
              <!-- Floating Overlay Radius Info Badge -->
              <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur-md px-3.5 py-2 rounded-xl shadow-md border border-emerald-200 flex items-center gap-2 text-xs">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></div>
                <span class="font-bold text-slate-800">Lingkaran Radius Aktif: <b class="text-emerald-600 font-mono">{{ settingForm.max_radius_meters }}m</b></span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-600">Jam Masuk</label>
              <input v-model="settingForm.work_start_time" type="time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400" />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-600">Batas Terlambat</label>
              <input v-model="settingForm.work_late_time" type="time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400" />
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
            <button type="button" @click="showSettingsModal = false" class="px-5 py-2 bg-slate-100 text-slate-600 font-bold rounded-xl text-xs">
              Batal
            </button>
            <button type="submit" :disabled="savingSettings" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md cursor-pointer">
              {{ savingSettings ? 'Menyimpan...' : 'Simpan Pengaturan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Peninjauan Permohonan Koreksi Absen -->
    <div v-if="showRequestsModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
          <div>
            <h3 class="text-base font-black text-slate-800">Daftar Permohonan Koreksi Absen Guru</h3>
            <p class="text-xs text-slate-400 mt-0.5">Tinjau, setujui, atau tolak permohonan koreksi absen dari Guru</p>
          </div>
          <button @click="showRequestsModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 custom-scrollbar">
          <div v-if="correctionRequests.length === 0" class="text-center py-12 text-slate-400 text-xs font-medium">
            Belum ada permohonan koreksi absen dari Guru.
          </div>

          <div v-else class="space-y-3">
            <div v-for="req in correctionRequests" :key="req.id" class="p-5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3">
              <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div class="flex items-center gap-3">
                  <img :src="getImageUrl(req.teacher?.photo_url)" class="w-10 h-10 rounded-full object-cover border border-slate-200" alt="Guru" />
                  <div>
                    <p class="font-black text-slate-800 text-xs sm:text-sm">{{ req.teacher?.full_name || 'Guru' }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">Tanggal Absen: <b>{{ req.date }}</b></p>
                  </div>
                </div>

                <span :class="[
                  req.approval_status === 'approved' ? 'bg-emerald-100 text-emerald-800' :
                  req.approval_status === 'rejected' ? 'bg-red-100 text-red-800' :
                  'bg-amber-100 text-amber-800',
                  'px-3 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider'
                ]">
                  {{ req.approval_status === 'approved' ? 'Disetujui 🟢' : (req.approval_status === 'rejected' ? 'Ditolak 🔴' : 'Pending ⏳') }}
                </span>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-white rounded-xl border border-slate-100 text-xs">
                <div>
                  <span class="text-[10px] text-slate-400 font-bold uppercase">Status Yang Diajukan:</span>
                  <p class="font-bold text-emerald-700 uppercase mt-0.5">{{ req.target_status }}</p>
                </div>
                <div>
                  <span class="text-[10px] text-slate-400 font-bold uppercase">Jam Masuk / Pulang:</span>
                  <p class="font-semibold text-slate-700 font-mono mt-0.5">
                    In: {{ req.requested_check_in_time || '-' }} | Out: {{ req.requested_check_out_time || '-' }}
                  </p>
                </div>
              </div>

              <div class="text-xs space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Alasan Permohonan:</span>
                <p class="p-3 bg-amber-50/70 border border-amber-200/60 rounded-xl font-medium text-amber-900 italic">
                  "{{ req.reason }}"
                </p>
              </div>

              <!-- Action buttons for Pending -->
              <div v-if="req.approval_status === 'pending'" class="pt-2 flex justify-end gap-2 border-t border-slate-200/60">
                <button
                  @click="processCorrection(req.id, 'reject')"
                  :disabled="processingReqId === req.id"
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm cursor-pointer disabled:opacity-50"
                >
                  Tolak 🔴
                </button>
                <button
                  @click="processCorrection(req.id, 'approve')"
                  :disabled="processingReqId === req.id"
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm cursor-pointer disabled:opacity-50"
                >
                  Setujui 🟢
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Edit Absensi Guru (Admin Direct Edit) -->
    <div v-if="showEditModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
          <div>
            <h3 class="text-base font-black text-slate-800">Edit Data Absen Guru</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ selectedTeacherForEdit?.full_name }} • {{ selectedDate }}</p>
          </div>
          <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="saveAttendanceEdit" class="p-6 space-y-4">
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-600">Status Kehadiran *</label>
            <select v-model="editForm.status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400" required>
              <option value="hadir">HADIR (TEPAT WAKTU)</option>
              <option value="terlambat">TERLAMBAT</option>
              <option value="izin">IZIN</option>
              <option value="sakit">SAKIT</option>
              <option value="tugas_luar">TUGAS LUAR / DINAS</option>
              <option value="belum_absen">BELUM ABSEN (RESET)</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3" v-if="editForm.status === 'hadir' || editForm.status === 'terlambat'">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-600">Jam Masuk</label>
              <input v-model="editForm.check_in_time" type="time" step="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold font-mono text-slate-800" />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-600">Jam Pulang</label>
              <input v-model="editForm.check_out_time" type="time" step="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold font-mono text-slate-800" />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-600">Catatan / Admin Notes</label>
            <textarea v-model="editForm.notes" rows="2" placeholder="Catatan perubahan oleh admin..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"></textarea>
          </div>

          <div class="pt-3 flex justify-end gap-3 border-t border-slate-100">
            <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-xl text-xs">
              Batal
            </button>
            <button type="submit" :disabled="savingEdit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md cursor-pointer">
              {{ savingEdit ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tampilkan & Cetak QR Code Presensi Hari Ini -->
    <div v-if="showQrModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 animate-slide-up flex flex-col">
        <!-- Modal Header -->
        <div class="p-6 pb-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/75">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <div>
              <h3 class="text-sm font-black text-slate-800 font-lexend">QR Code Presensi Resmi Guru</h3>
              <p class="text-[11px] text-slate-400 font-medium">Valid hanya untuk hari ini ({{ qrData?.today || selectedDate }})</p>
            </div>
          </div>
          <button @click="showQrModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer transition-colors">✕</button>
        </div>

        <!-- Modal Body / Printable Container -->
        <div id="school-qr-print-area" class="p-8 flex flex-col items-center text-center space-y-6">
          <div class="space-y-1">
            <span class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-black uppercase tracking-wider">
              {{ qrData?.school_name || 'MTs Al-Hasanah' }}
            </span>
            <h2 class="text-xl font-black text-slate-900 font-lexend mt-1 uppercase tracking-tight">SCAN PRESENSI KEHADIRAN</h2>
            <p class="text-xs text-slate-500 font-medium max-w-xs mx-auto">Arahkan kamera HP Anda dari menu Absensi Guru ke QR Code di bawah ini.</p>
          </div>

          <!-- QR Code Frame -->
          <div class="relative p-5 bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-emerald-500/10 rounded-3xl border-2 border-dashed border-emerald-400 shadow-inner flex flex-col items-center justify-center min-w-[280px] min-h-[280px]">
            <div v-if="loadingQr" class="flex flex-col items-center gap-2">
              <div class="w-8 h-8 border-3 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
              <span class="text-xs text-slate-400 font-bold">Membuat QR Code...</span>
            </div>

            <div v-else-if="qrDataUrl" class="space-y-3">
              <div class="bg-white p-3 rounded-2xl shadow-md border border-slate-100">
                <img :src="qrDataUrl" alt="QR Code Presensi" class="w-64 h-64 object-contain mx-auto" />
              </div>
              <div class="flex items-center justify-center gap-2 text-[10px] text-emerald-800 font-mono font-bold bg-white/90 px-3 py-1 rounded-full border border-emerald-200 shadow-2xs">
                <span>🔒 Token Harian Terenkripsi Aktif</span>
              </div>
            </div>
          </div>

          <!-- Live Clock & Today Info -->
          <div class="w-full grid grid-cols-2 gap-3 p-3.5 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
            <div class="text-left">
              <span class="text-[10px] font-bold text-slate-400 uppercase">Jam Live Sekolah:</span>
              <p class="font-black text-slate-800 font-mono text-sm mt-0.5">{{ liveClock }} WIB</p>
            </div>
            <div class="text-right">
              <span class="text-[10px] font-bold text-slate-400 uppercase">Batas Tepat Waktu:</span>
              <p class="font-bold text-emerald-700 font-mono text-sm mt-0.5">{{ setting.work_late_time || '07:15' }}</p>
            </div>
          </div>
        </div>

          <!-- Action Footer -->
          <div class="p-6 pt-0 flex gap-3">
            <button
              type="button"
              @click="printQrSheet"
              class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              <span>Cetak Lembar QR (Kertas A4 / Lobi)</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Scanner Presensi Kartu Guru (Meja Piket / Gerbang) -->
      <div v-if="showTeacherCardScannerModal" class="fixed inset-0 bg-slate-950/85 backdrop-blur-md flex items-center justify-center z-50 p-4">
        <div class="bg-slate-900 rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-800 animate-slide-up flex flex-col text-white">
          <!-- Header -->
          <div class="p-6 pb-4 border-b border-slate-800 flex justify-between items-center bg-slate-950/50">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
              </div>
              <div>
                <h3 class="text-sm font-black text-white font-lexend">Scanner Presensi Kartu Guru (Meja Piket)</h3>
                <p class="text-[11px] text-slate-400 font-medium">Arahkan ID Card Guru ke kamera untuk presensi cepat</p>
              </div>
            </div>
            <button @click="closeTeacherCardScanner" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-400 flex items-center justify-center cursor-pointer transition-colors">✕</button>
          </div>

          <!-- Body -->
          <div class="p-6 sm:p-8 space-y-5 text-center">
            <!-- Mode Selector -->
            <div class="inline-flex p-1 bg-slate-800/80 rounded-2xl border border-slate-700">
              <button 
                @click="piketActionMode = 'auto'"
                :class="[piketActionMode === 'auto' ? 'bg-emerald-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white', 'px-3.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer']"
              >
                🔄 Otomatis (Masuk/Pulang)
              </button>
              <button 
                @click="piketActionMode = 'check_in'"
                :class="[piketActionMode === 'check_in' ? 'bg-emerald-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white', 'px-3.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer']"
              >
                🟢 Khusus Masuk
              </button>
              <button 
                @click="piketActionMode = 'check_out'"
                :class="[piketActionMode === 'check_out' ? 'bg-teal-600 text-white font-bold shadow-md' : 'text-slate-400 hover:text-white', 'px-3.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer']"
              >
                🔵 Khusus Pulang
              </button>
            </div>

            <!-- Viewport Camera Box -->
            <div class="relative max-w-sm mx-auto overflow-hidden rounded-3xl bg-black border-2 border-emerald-500/40 aspect-square shadow-2xl flex items-center justify-center">
              <div id="piket-teacher-card-reader" class="w-full h-full"></div>

              <!-- Scanner Laser Beam Overlay -->
              <div v-if="isPiketScanning" class="pointer-events-none absolute inset-0 flex flex-col justify-between p-6">
                <div class="flex justify-between">
                  <div class="w-8 h-8 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl"></div>
                  <div class="w-8 h-8 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl"></div>
                </div>
                <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_15px_#34d399] animate-pulse"></div>
                <div class="flex justify-between">
                  <div class="w-8 h-8 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl"></div>
                  <div class="w-8 h-8 border-b-4 border-r-4 border-emerald-400 rounded-br-xl"></div>
                </div>
              </div>
            </div>

            <!-- Last Scanned Result Banner -->
            <div v-if="lastScannedTeacher" class="p-4 bg-emerald-950/80 border border-emerald-500/40 rounded-2xl flex items-center gap-4 text-left animate-fadeIn shadow-lg">
              <div class="w-12 h-12 rounded-xl bg-slate-800 border border-emerald-400/50 overflow-hidden flex-shrink-0">
                <img v-if="lastScannedTeacher.photo_url" :src="lastScannedTeacher.photo_url" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center font-bold text-white text-lg">
                  {{ lastScannedTeacher.full_name?.charAt(0) }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <span :class="[lastScannedType === 'check_in' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-teal-500/20 text-teal-300 border-teal-500/40', 'px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border']">
                    {{ lastScannedType === 'check_in' ? 'Absen Masuk' : 'Absen Pulang' }}
                  </span>
                  <span class="text-[10px] text-slate-400 font-mono">{{ lastScannedTime }} WIB</span>
                </div>
                <h4 class="text-sm font-bold text-white truncate mt-0.5">{{ lastScannedTeacher.full_name }}</h4>
                <p class="text-[11px] text-emerald-200 font-mono">NIP: {{ lastScannedTeacher.nip || '-' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    <!-- Modal Kelola Hari Libur & PHBI -->
    <div v-if="showHolidaysModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-3xl overflow-hidden max-h-[90vh] flex flex-col border border-slate-100 animate-slide-up">
        <!-- Header -->
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/75 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 border border-indigo-500/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
              <h3 class="text-base font-black text-slate-800 font-lexend">Manajemen Hari Libur & PHBI</h3>
              <p class="text-xs text-slate-400 font-medium">Atur libur mingguan madrasah dan kalender libur PHBI / Nasional</p>
            </div>
          </div>
          <button @click="showHolidaysModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer transition-colors">✕</button>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-b border-slate-100 bg-white px-6 pt-3 gap-3 flex-shrink-0">
          <button
            @click="holidayActiveTab = 'weekly'"
            :class="[holidayActiveTab === 'weekly' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2 pb-2.5' : 'text-slate-400 hover:text-slate-600 pb-2.5 font-semibold text-xs', 'text-xs transition-all cursor-pointer flex items-center gap-2']"
          >
            <span>🗓️ Hari Libur Mingguan</span>
          </button>
          <button
            @click="holidayActiveTab = 'events'"
            :class="[holidayActiveTab === 'events' ? 'border-indigo-600 text-indigo-600 font-bold border-b-2 pb-2.5' : 'text-slate-400 hover:text-slate-600 pb-2.5 font-semibold text-xs', 'text-xs transition-all cursor-pointer flex items-center gap-2']"
          >
            <span>🕌 Libur Nasional, PHBI & Kalender ({{ calendarEvents.length }})</span>
          </button>
        </div>

        <!-- Tab 1: Hari Libur Mingguan -->
        <div v-if="holidayActiveTab === 'weekly'" class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
          <div class="p-4 bg-indigo-50/70 border border-indigo-100 rounded-2xl space-y-2 text-xs">
            <p class="font-bold text-indigo-900">Pilih Skema Hari Kerja & Libur Rutin Setiap Pekan:</p>
            <p class="text-slate-600 leading-relaxed">Centang hari-hari yang menjadi hari libur tetap madrasah. Pada hari-hari tersebut, dewan guru otomatis tidak diwajibkan melakukan presensi harian.</p>
            <div class="flex flex-wrap items-center gap-2 pt-2">
              <span class="text-[11px] font-bold text-slate-500">Pilihan Cepat:</span>
              <button type="button" @click="setWeeklyPreset('6days')" class="px-2.5 py-1 bg-white hover:bg-indigo-100 text-indigo-800 font-bold rounded-lg border border-indigo-200 text-[10px] transition-all cursor-pointer">
                ⚡ 6 Hari Kerja (Libur Minggu Saja)
              </button>
              <button type="button" @click="setWeeklyPreset('5days')" class="px-2.5 py-1 bg-white hover:bg-indigo-100 text-indigo-800 font-bold rounded-lg border border-indigo-200 text-[10px] transition-all cursor-pointer">
                ⚡ 5 Hari Kerja (Libur Sabtu & Minggu)
              </button>
              <button type="button" @click="setWeeklyPreset('pesantren')" class="px-2.5 py-1 bg-white hover:bg-indigo-100 text-indigo-800 font-bold rounded-lg border border-indigo-200 text-[10px] transition-all cursor-pointer">
                ⚡ Libur Jumat & Minggu
              </button>
            </div>
          </div>

          <!-- Day Selection Checkbox Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <label
              v-for="d in availableWeekDays"
              :key="d.id"
              :class="[weeklyHolidays.includes(d.id) ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100', 'p-3.5 rounded-2xl border flex items-center justify-between cursor-pointer transition-all select-none']"
            >
              <div class="flex items-center gap-2.5">
                <input
                  type="checkbox"
                  :value="d.id"
                  v-model="weeklyHolidays"
                  class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer"
                />
                <span class="text-xs font-bold">{{ d.name }}</span>
              </div>
              <span class="text-[10px] uppercase font-black opacity-80">{{ weeklyHolidays.includes(d.id) ? 'LIBUR' : 'MASUK' }}</span>
            </label>
          </div>

          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
            <button
              type="button"
              @click="saveWeeklyHolidays"
              :disabled="savingWeekly"
              class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md transition-all cursor-pointer flex items-center gap-2"
            >
              <span>{{ savingWeekly ? 'Menyimpan...' : 'Simpan Hari Libur Mingguan' }}</span>
            </button>
          </div>
        </div>

        <!-- Tab 2: Libur Nasional, PHBI & Kalender -->
        <div v-if="holidayActiveTab === 'events'" class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
          <!-- Top 1-Click Sync Card -->
          <div class="p-5 bg-gradient-to-r from-emerald-900 to-teal-900 text-white rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-md border border-emerald-500/30">
            <div class="space-y-1">
              <span class="px-2.5 py-0.5 rounded-full bg-emerald-400/20 text-emerald-300 text-[10px] font-black uppercase border border-emerald-400/30">
                SINKRONISASI OTOMATIS T.A. 2026/2027
              </span>
              <h4 class="text-sm font-black font-lexend">Muat Libur Nasional, PHBI & Kalender Pendidikan</h4>
              <p class="text-xs text-emerald-100/80">Otomatis mengisi Maulid Nabi, Isra Mi'raj, Idul Fitri, Idul Adha, 17 Agustus, Hari Santri, Hari Guru, dan Libur Semester.</p>
            </div>
            <button
              type="button"
              @click="syncNationalAndPhbi"
              :disabled="syncingHolidays"
              class="px-4.5 py-2.5 bg-white text-emerald-900 hover:bg-emerald-50 font-black rounded-xl text-xs shadow-md transition-all active:scale-95 cursor-pointer whitespace-nowrap"
            >
              <span>{{ syncingHolidays ? 'Menyinkronkan...' : '⚡ Muat Otomatis Sekarang' }}</span>
            </button>
          </div>

          <!-- Form Tambah Hari Libur Kustom -->
          <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-3">
            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <span>+ Tambah Hari Libur Kustom / Khusus Madrasah</span>
            </h4>
            <form @submit.prevent="addCustomHoliday" class="space-y-3">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                  <label class="block text-[11px] font-bold text-slate-600">Tanggal Mulai *</label>
                  <input v-model="newHolidayForm.start_date" type="date" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800" required />
                </div>
                <div class="space-y-1">
                  <label class="block text-[11px] font-bold text-slate-600">Tanggal Selesai *</label>
                  <input v-model="newHolidayForm.end_date" type="date" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800" required />
                </div>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2 space-y-1">
                  <label class="block text-[11px] font-bold text-slate-600">Nama Hari Libur / Keterangan *</label>
                  <input v-model="newHolidayForm.title" type="text" placeholder="Contoh: Libur Cuti Bersama Madrasah" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800" required />
                </div>
                <div class="space-y-1">
                  <label class="block text-[11px] font-bold text-slate-600">Kategori</label>
                  <select v-model="newHolidayForm.type" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800">
                    <option value="holiday">Hari Libur (PHBI / Nasional)</option>
                    <option value="academic">Libur Semester / Akademik</option>
                    <option value="exam">Ujian / Evaluasi</option>
                    <option value="event">Kegiatan Khusus</option>
                  </select>
                </div>
              </div>
              <div class="flex justify-end pt-1">
                <button type="submit" :disabled="savingNewHoliday" class="px-4.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md transition-all cursor-pointer">
                  {{ savingNewHoliday ? 'Menyimpan...' : '+ Tambahkan ke Kalender' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Table of Holidays -->
          <div class="space-y-2">
            <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider">Daftar Hari Libur Terdaftar ({{ calendarEvents.length }} Hari)</h4>
            <div class="border border-slate-100 rounded-2xl overflow-hidden shadow-2xs max-h-72 overflow-y-auto custom-scrollbar">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold text-[10px] uppercase">
                  <tr>
                    <th class="px-4 py-3">Rentang Tanggal</th>
                    <th class="px-4 py-3">Nama Hari Libur / PHBI</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-for="ev in calendarEvents" :key="ev.id" class="hover:bg-slate-50/80">
                    <td class="px-4 py-2.5 font-mono font-bold text-slate-700 whitespace-nowrap">
                      {{ ev.start_date === ev.end_date ? ev.start_date : `${ev.start_date} s/d ${ev.end_date}` }}
                    </td>
                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ ev.title }}</td>
                    <td class="px-4 py-2.5 whitespace-nowrap">
                      <span :style="{ backgroundColor: (ev.color || '#10B981') + '22', color: ev.color || '#10B981' }" class="px-2.5 py-0.5 rounded-full font-bold text-[10px] uppercase">
                        {{ ev.type || 'holiday' }}
                      </span>
                    </td>
                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                      <button @click="removeHoliday(ev)" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Hapus Libur">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!calendarEvents.length">
                    <td colspan="4" class="text-center py-8 text-slate-400 text-xs">
                      Belum ada data hari libur. Klik "Muat Otomatis" di atas untuk mengisi kalender libur PHBI & Nasional.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import QRCode from 'qrcode';
import { Html5Qrcode } from 'html5-qrcode';

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const savingSettings = ref(false);
const showSettingsModal = ref(false);
const showEditModal = ref(false);
const savingEdit = ref(false);
const selectedTeacherForEdit = ref(null);

// Holiday Management State
const holidayInfo = ref({
  is_holiday: false,
  holiday_name: '',
  holiday_type: '',
  is_weekly_holiday: false,
});
const showHolidaysModal = ref(false);
const holidayActiveTab = ref('weekly'); // 'weekly' | 'events'
const weeklyHolidays = ref(['sunday']);
const calendarEvents = ref([]);
const savingWeekly = ref(false);
const syncingHolidays = ref(false);
const savingNewHoliday = ref(false);

const availableWeekDays = [
  { id: 'monday', name: 'Senin' },
  { id: 'tuesday', name: 'Selasa' },
  { id: 'wednesday', name: 'Rabu' },
  { id: 'thursday', name: 'Kamis' },
  { id: 'friday', name: 'Jumat' },
  { id: 'saturday', name: 'Sabtu' },
  { id: 'sunday', name: 'Minggu' },
];

const newHolidayForm = reactive({
  start_date: '',
  end_date: '',
  title: '',
  type: 'holiday',
  color: '#10B981',
});

function setWeeklyPreset(preset) {
  if (preset === '6days') {
    weeklyHolidays.value = ['sunday'];
  } else if (preset === '5days') {
    weeklyHolidays.value = ['saturday', 'sunday'];
  } else if (preset === 'pesantren') {
    weeklyHolidays.value = ['friday', 'sunday'];
  }
}

async function openHolidaysModal() {
  showHolidaysModal.value = true;
  try {
    const res = await api.get('admin/teacher-attendance-monitoring/holidays');
    weeklyHolidays.value = res?.weekly_holidays || ['sunday'];
    calendarEvents.value = res?.calendar_events || [];
  } catch (err) {
    console.error(err);
    toast.error('Gagal memuat data hari libur.');
  }
}

async function saveWeeklyHolidays() {
  savingWeekly.value = true;
  try {
    const res = await api.post('admin/teacher-attendance-monitoring/weekly-holidays', {
      weekly_holidays: weeklyHolidays.value,
    });
    toast.success(res.message || 'Hari libur mingguan berhasil disimpan!');
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal menyimpan hari libur mingguan.');
  } finally {
    savingWeekly.value = false;
  }
}

async function syncNationalAndPhbi() {
  const ok = await confirm({
    title: 'Sinkronisasi Hari Libur Nasional & PHBI',
    message: 'Apakah Anda ingin memuat otomatis seluruh daftar Hari Libur Nasional, PHBI, dan Libur Semester untuk Tahun Ajaran 2026/2027 ke Kalender Pendidikan?',
    confirmText: 'Ya, Muat Otomatis',
    cancelText: 'Batal',
    type: 'primary'
  });
  if (!ok) return;

  syncingHolidays.value = true;
  try {
    const res = await api.post('admin/teacher-attendance-monitoring/sync-holidays');
    toast.success(res.message || 'Berhasil menyinkronkan libur nasional & PHBI!');
    const hRes = await api.get('admin/teacher-attendance-monitoring/holidays');
    calendarEvents.value = hRes?.calendar_events || [];
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal menyinkronkan hari libur.');
  } finally {
    syncingHolidays.value = false;
  }
}

async function addCustomHoliday() {
  savingNewHoliday.value = true;
  try {
    const res = await api.post('admin/teacher-attendance-monitoring/holidays', newHolidayForm);
    toast.success(res.message || 'Hari libur berhasil ditambahkan!');
    newHolidayForm.title = '';
    newHolidayForm.start_date = '';
    newHolidayForm.end_date = '';
    const hRes = await api.get('admin/teacher-attendance-monitoring/holidays');
    calendarEvents.value = hRes?.calendar_events || [];
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal menambahkan hari libur.');
  } finally {
    savingNewHoliday.value = false;
  }
}

async function removeHoliday(ev) {
  const ok = await confirm({
    title: 'Hapus Hari Libur',
    message: `Apakah Anda yakin ingin menghapus hari libur "${ev.title}" dari kalender?`,
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal',
    type: 'danger'
  });
  if (!ok) return;

  try {
    const res = await api.delete(`admin/teacher-attendance-monitoring/holidays/${ev.id}`);
    toast.success(res.message || 'Hari libur dihapus.');
    const hRes = await api.get('admin/teacher-attendance-monitoring/holidays');
    calendarEvents.value = hRes?.calendar_events || [];
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    toast.error('Gagal menghapus hari libur.');
  }
}

const correctionRequests = ref([]);
const processingReqId = ref(null);

const showQrModal = ref(false);
const qrData = ref(null);
const qrDataUrl = ref('');
const loadingQr = ref(false);
const liveClock = ref('');
let clockTimer = null;

// Piket Teacher Card Scanner State
const showTeacherCardScannerModal = ref(false);
const isPiketScanning = ref(false);
const piketActionMode = ref('auto'); // 'auto' | 'check_in' | 'check_out'
const lastScannedTeacher = ref(null);
const lastScannedType = ref('');
const lastScannedTime = ref('');
let piketQrCodeScanner = null;

const editForm = reactive({
  teacher_id: null,
  date: '',
  status: 'hadir',
  check_in_time: '07:00:00',
  check_out_time: '15:00:00',
  notes: '',
});

const dynamicMapZoom = computed(() => {
  const r = Number(settingForm.max_radius_meters) || 100;
  if (r <= 50) return 19;
  if (r <= 150) return 18;
  if (r <= 350) return 17;
  if (r <= 700) return 16;
  if (r <= 1500) return 15;
  if (r <= 3500) return 14;
  if (r <= 7500) return 13;
  if (r <= 15000) return 12;
  if (r <= 35000) return 11;
  if (r <= 75000) return 10;
  if (r <= 150000) return 9;
  return 8;
});

const dynamicCirclePx = computed(() => {
  const r = Number(settingForm.max_radius_meters) || 100;
  if (r <= 50) return Math.min(190, Math.max(50, (r / 50) * 110));
  if (r <= 150) return Math.min(190, Math.max(50, (r / 150) * 120));
  if (r <= 350) return Math.min(190, Math.max(50, (r / 350) * 120));
  if (r <= 700) return Math.min(190, Math.max(50, (r / 700) * 120));
  if (r <= 1500) return Math.min(190, Math.max(50, (r / 1500) * 120));
  if (r <= 3500) return Math.min(190, Math.max(50, (r / 3500) * 120));
  if (r <= 7500) return Math.min(190, Math.max(50, (r / 7500) * 120));
  if (r <= 15000) return Math.min(190, Math.max(50, (r / 15000) * 120));
  if (r <= 35000) return Math.min(190, Math.max(50, (r / 35000) * 120));
  if (r <= 75000) return Math.min(190, Math.max(50, (r / 75000) * 120));
  return Math.min(190, Math.max(50, (r / 150000) * 120));
});

function openEditModal(t) {
  selectedTeacherForEdit.value = t;
  editForm.teacher_id = t.teacher_id;
  editForm.date = selectedDate.value;
  editForm.status = t.status || 'hadir';
  editForm.check_in_time = t.check_in_time || '07:00:00';
  editForm.check_out_time = t.check_out_time || '15:00:00';
  editForm.notes = t.notes || '';
  showEditModal.value = true;
}

async function saveAttendanceEdit() {
  savingEdit.value = true;
  try {
    const res = await api.post('admin/teacher-attendance-monitoring/update', editForm);
    toast.success(res.message || res.data?.message || 'Data presensi berhasil diperbarui!');
    showEditModal.value = false;
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal mengubah data presensi.');
  } finally {
    savingEdit.value = false;
  }
}

async function confirmResetAttendance(t) {
  const isConfirmed = await confirm({
    title: 'Reset Presensi Guru',
    message: `Apakah Anda yakin ingin me-reset status presensi "${t.full_name}" pada tanggal ${selectedDate.value} ke status "Belum Absen"?`,
    confirmText: 'Ya, Reset',
    cancelText: 'Batal',
    type: 'warning'
  });
  if (!isConfirmed) return;

  try {
    const res = await api.post('admin/teacher-attendance-monitoring/reset', {
      teacher_id: t.teacher_id,
      date: selectedDate.value,
    });
    toast.success(res.message || res.data?.message || 'Presensi berhasil di-reset!');
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    toast.error('Gagal me-reset data presensi.');
  }
}

const selectedDate = ref(new Date().toISOString().substring(0, 10));

const setting = ref({
  latitude: -6.20880000,
  longitude: 106.84560000,
  max_radius_meters: 100,
});

const summary = ref({
  total_teachers: 0,
  hadir: 0,
  terlambat: 0,
  izin: 0,
  sakit: 0,
  tugas_luar: 0,
  belum_absen: 0,
});

const teachers = ref([]);

const settingForm = reactive({
  latitude: -6.20880000,
  longitude: 106.84560000,
  max_radius_meters: 100,
  work_start_time: '07:00:00',
  work_late_time: '07:15:00',
  work_end_time: '15:00:00',
});

function getImageUrl(path) {
  if (!path) return 'https://ui-avatars.com/api/?name=Guru&background=4F46E5&color=fff';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return `/storage/${path.replace(/^\/?storage\//, '')}`;
}

async function loadMonitoring() {
  loading.value = true;
  try {
    const res = await api.get('admin/teacher-attendance-monitoring', { date: selectedDate.value });
    setting.value = res?.setting || res?.data?.setting || setting.value;
    summary.value = res?.summary || res?.data?.summary || summary.value;
    teachers.value = res?.teachers || res?.data?.teachers || [];
    holidayInfo.value = res?.holiday_info || res?.data?.holiday_info || { is_holiday: false };

    settingForm.latitude = setting.value.latitude;
    settingForm.longitude = setting.value.longitude;
    settingForm.max_radius_meters = setting.value.max_radius_meters;
    settingForm.work_start_time = setting.value.work_start_time || '07:00:00';
    settingForm.work_late_time = setting.value.work_late_time || '07:15:00';
    settingForm.work_end_time = setting.value.work_end_time || '15:00:00';
  } catch (err) {
    console.error(err);
    toast.error('Gagal memuat data monitoring presensi guru.');
  } finally {
    loading.value = false;
  }
}

function fillCurrentLocation() {
  if (!navigator.geolocation) {
    toast.error('GPS tidak didukung peramban.');
    return;
  }

  const success = (pos) => {
    settingForm.latitude = pos.coords.latitude;
    settingForm.longitude = pos.coords.longitude;
    toast.success('Lokasi GPS Anda berhasil dimasukkan!');
  };

  navigator.geolocation.getCurrentPosition(
    success,
    (err) => {
      console.warn('GPS High accuracy failed, trying fallback...', err);
      navigator.geolocation.getCurrentPosition(
        success,
        (finalErr) => {
          console.error(finalErr);
          toast.error('Gagal membaca GPS. Harap izinkan akses lokasi di peramban Anda.');
        },
        { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 }
      );
    },
    { enableHighAccuracy: true, timeout: 7000, maximumAge: 0 }
  );
}

function fillFromGoogleMaps() {
  if (setting.value?.maps_extracted_lat && setting.value?.maps_extracted_lng) {
    settingForm.latitude = setting.value.maps_extracted_lat;
    settingForm.longitude = setting.value.maps_extracted_lng;
    toast.success('Koordinat berhasil diselaraskan dari Google Maps Sekolah!');
  } else {
    toast.error('Belum ada link Google Maps terdeteksi di Pengaturan Aplikasi. Anda dapat menempelkan koordinat manual atau menekan tombol "Gunakan GPS Saya".');
  }
}

async function saveSettings() {
  savingSettings.value = true;
  try {
    await api.post('admin/school-settings', settingForm);
    toast.success('Pengaturan lokasi & radius sekolah berhasil diperbarui!');
    showSettingsModal.value = false;
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal menyimpan pengaturan.');
  } finally {
    savingSettings.value = false;
  }
}

async function loadRequests() {
  try {
    const res = await api.get('admin/teacher-attendance-requests');
    correctionRequests.value = res?.requests || res?.data?.requests || [];
  } catch (err) {
    console.error(err);
  }
}

async function processCorrection(id, action) {
  processingReqId.value = id;
  try {
    const res = await api.post(`admin/teacher-attendance-requests/${id}/process`, { action });
    toast.success(res.message || res.data?.message || 'Berhasil memproses permohonan!');
    await loadRequests();
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal memproses permohonan.');
  } finally {
    processingReqId.value = null;
  }
}

function updateClock() {
  const now = new Date();
  liveClock.value = now.toLocaleTimeString('id-ID', { hour12: false });
}

async function openQrModal() {
  showQrModal.value = true;
  loadingQr.value = true;
  updateClock();
  if (clockTimer) clearInterval(clockTimer);
  clockTimer = setInterval(updateClock, 1000);

  try {
    const res = await api.get('admin/teacher-presensi-qr');
    qrData.value = res?.data || res || {};
    if (qrData.value.qr_payload) {
      qrDataUrl.value = await QRCode.toDataURL(qrData.value.qr_payload, {
        width: 400,
        margin: 2,
        color: {
          dark: '#064E3B',
          light: '#FFFFFF'
        }
      });
    }
  } catch (err) {
    console.error('Failed to load QR code:', err);
    toast.error('Gagal memuat token QR Code sekolah.');
  } finally {
    loadingQr.value = false;
  }
}

function printQrSheet() {
  if (!qrDataUrl.value) return;
  const printWin = window.open('', '_blank', 'width=800,height=900');
  if (!printWin) {
    toast.error('Gagal membuka jendela cetak. Pastikan pop-up tidak diblokir.');
    return;
  }

  const schoolName = qrData.value?.school_name || 'MTs Al-Hasanah';
  const schoolAddress = qrData.value?.school_address || '';
  const todayStr = qrData.value?.today || selectedDate.value;

  printWin.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Cetak QR Code Presensi Guru - ${schoolName}</title>
        <style>
          @page { size: A4 portrait; margin: 15mm; }
          body { font-family: system-ui, -apple-system, sans-serif; text-align: center; color: #1e293b; padding: 20px; }
          .header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 24px; }
          .title { font-size: 22px; font-weight: 900; text-transform: uppercase; margin: 0; color: #064e3b; letter-spacing: 1px; }
          .subtitle { font-size: 13px; color: #475569; margin-top: 4px; font-weight: 600; }
          .qr-box { border: 3px dashed #059669; border-radius: 24px; padding: 24px; display: inline-block; margin: 20px auto; background: #f0fdf4; }
          .qr-img { width: 320px; height: 320px; display: block; margin: 0 auto; background: white; padding: 12px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
          .token-badge { margin-top: 14px; display: inline-block; background: #d1fae5; color: #065f46; padding: 6px 16px; border-radius: 20px; font-size: 11px; font-family: monospace; font-weight: bold; }
          .instructions { margin-top: 24px; font-size: 12px; color: #334155; line-height: 1.6; max-width: 450px; margin-left: auto; margin-right: auto; }
          .footer { margin-top: 36px; border-top: 1px solid #e2e8f0; padding-top: 12px; font-size: 11px; color: #94a3b8; }
        </style>
      </head>
      <body>
        <div class="header">
          <h1 class="title">${schoolName}</h1>
          <p class="subtitle">${schoolAddress}</p>
        </div>

        <h2 style="font-size: 18px; font-weight: 800; text-transform: uppercase; color: #0f172a; margin-bottom: 6px;">
          QR CODE PRESENSI RESMI GURU
        </h2>
        <p style="font-size: 12px; color: #059669; font-weight: bold; margin: 0;">
          📅 Berlaku Hari Ini: ${todayStr}
        </p>

        <div class="qr-box">
          <img src="${qrDataUrl.value}" class="qr-img" />
          <div class="token-badge">🔒 VERIFIED OFFICIAL SCHOOL QR CODE</div>
        </div>

        <div class="instructions">
          <strong>PETUNJUK PRESENSI:</strong><br>
          1. Buka aplikasi di HP Anda lalu login ke <strong>Akun Guru</strong>.<br>
          2. Masuk ke menu <strong>Absensi Harian</strong> lalu pilih tab <strong>Scan QR Code</strong>.<br>
          3. Arahkan kamera HP ke QR Code di atas hingga berhasil tercatat.
        </div>

        <div class="footer">
          Dicetak otomatis oleh Sistem Manajemen Sekolah pada ${new Date().toLocaleString('id-ID')}
        </div>
      </body>
    </html>
  `);

  printWin.document.close();
  setTimeout(() => {
    printWin.focus();
    printWin.print();
  }, 500);
}

function playBeep() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.type = 'sine';
    osc.frequency.setValueAtTime(880, ctx.currentTime);
    gain.gain.setValueAtTime(0.25, ctx.currentTime);
    osc.start();
    osc.stop(ctx.currentTime + 0.18);
  } catch (e) {
    console.warn('Audio Context failed', e);
  }
}

async function openTeacherCardScanner() {
  showTeacherCardScannerModal.value = true;
  lastScannedTeacher.value = null;
  lastScannedType.value = '';
  lastScannedTime.value = '';
  await nextTick();
  setTimeout(() => {
    startPiketScanner();
  }, 150);
}

async function startPiketScanner() {
  try {
    const el = document.getElementById("piket-teacher-card-reader");
    if (!el) {
      console.warn("Retrying to find piket-teacher-card-reader element...");
      setTimeout(startPiketScanner, 200);
      return;
    }

    const devices = await Html5Qrcode.getCameras();
    if (!devices || devices.length === 0) {
      toast.error('Kamera tidak ditemukan.');
      return;
    }

    let cameraId = devices[0].id;
    const backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('rear'));
    if (backCam) cameraId = backCam.id;

    if (!piketQrCodeScanner) {
      piketQrCodeScanner = new Html5Qrcode("piket-teacher-card-reader");
    }

    if (isPiketScanning.value) {
      await piketQrCodeScanner.stop();
    }

    await piketQrCodeScanner.start(
      cameraId,
      { fps: 15, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
      async (decodedText) => {
        await handleTeacherCardScanned(decodedText);
      },
      () => {}
    );
    isPiketScanning.value = true;
  } catch (err) {
    console.error('Piket Scanner Error:', err);
    toast.error('Gagal menyalakan kamera piket. Pastikan izin kamera telah diberikan.');
  }
}

async function closeTeacherCardScanner() {
  if (piketQrCodeScanner && isPiketScanning.value) {
    try {
      await piketQrCodeScanner.stop();
    } catch (e) {
      console.warn(e);
    } finally {
      isPiketScanning.value = false;
    }
  }
  showTeacherCardScannerModal.value = false;
}

let isProcessingScan = false;
async function handleTeacherCardScanned(decodedText) {
  if (isProcessingScan) return;
  isProcessingScan = true;

  try {
    const res = await api.post('admin/teacher-attendance-monitoring/scan-teacher-card', {
      qr_payload: decodedText,
      action_type: piketActionMode.value,
    });

    playBeep();
    const data = res?.data || res || {};
    lastScannedTeacher.value = data.teacher || null;
    lastScannedType.value = data.type || 'check_in';
    lastScannedTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    toast.success(data.message || 'Presensi Guru Berhasil Tercatat!');
    await loadMonitoring();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Kartu Guru tidak valid atau sudah selesai presensi hari ini.';
    toast.error(msg);
  } finally {
    setTimeout(() => {
      isProcessingScan = false;
    }, 2200); // 2.2s cooldown before scanning next teacher
  }
}

onMounted(() => {
  loadMonitoring();
  loadRequests();
});

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer);
  if (piketQrCodeScanner && isPiketScanning.value) {
    piketQrCodeScanner.stop().catch(e => console.warn(e));
  }
});
</script>
