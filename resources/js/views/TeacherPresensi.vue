<template>
  <div class="space-y-8 pb-12">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
      <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <span class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md">
            Presensi Kehadiran Guru
          </span>
          <h1 class="text-2xl sm:text-3xl font-black mt-2 font-lexend">Absensi Harian & Geolocation GPS</h1>
          <p class="text-emerald-100 text-xs sm:text-sm font-medium mt-1">
            Hari ini: <span class="font-bold underline text-white">{{ formattedToday }}</span>
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <button 
            @click="showRequestModal = true" 
            class="flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-amber-500/20 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Ajukan Koreksi Absen
          </button>

          <button 
            @click="loadData" 
            class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-bold transition-all backdrop-blur-md cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Refresh Data
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-3xl border border-slate-100 shadow-sm">
      <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-xs font-bold text-slate-500 mt-3">Mendeteksi Posisi GPS & Memuat Data Absensi...</p>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Col 1: GPS Indicator & Actions -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Status GPS Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-5">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              Deteksi Geolocation GPS
            </h2>
            <button @click="detectGPS" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline flex items-center gap-1">
              <span>Cek Ulang GPS</span>
            </button>
          </div>

          <div v-if="gpsError" class="p-5 bg-red-50 border border-red-200 rounded-2xl space-y-3 text-red-800">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <div>
                <p class="text-xs font-bold uppercase tracking-wider">Akses GPS Ditolak / Dibatasi Peramban</p>
                <p class="text-xs font-medium mt-1 leading-relaxed">{{ gpsError }}</p>
              </div>
            </div>

            <div class="pt-3 border-t border-red-200/60 flex flex-wrap items-center justify-between gap-3">
              <span class="text-[11px] font-semibold text-red-700">Pilihan Tombol Cadangan:</span>
              <div class="flex items-center gap-2">
                <button @click="detectGPS" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl text-[11px] transition-all shadow-sm cursor-pointer">
                  Cek Ulang GPS
                </button>
                <button @click="useSchoolLocationAsFallback" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] transition-all shadow-sm cursor-pointer">
                  Gunakan Koordinat Sekolah (0 Meter)
                </button>
              </div>
            </div>
          </div>

          <div v-else-if="currentLat && currentLng" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-slate-50 border border-slate-200/60 rounded-2xl space-y-1">
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Koordinat Anda Saat Ini</p>
              <p class="text-xs font-bold text-slate-700 font-mono">{{ currentLat.toFixed(6) }}, {{ currentLng.toFixed(6) }}</p>
            </div>

            <div :class="[isInRadius ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-red-50 border-red-200 text-red-900', 'p-4 border rounded-2xl space-y-1']">
              <p class="text-[10px] font-bold uppercase tracking-wider opacity-75">Jarak Ke Sekolah</p>
              <div class="flex items-center gap-2">
                <span class="text-lg font-black font-lexend">{{ currentDistance }} meter</span>
                <span :class="[isInRadius ? 'bg-emerald-600' : 'bg-red-600', 'px-2 py-0.5 text-[10px] font-bold text-white rounded-full uppercase']">
                  {{ isInRadius ? '🟢 Dalam Radius' : '🔴 Di Luar Radius' }}
                </span>
              </div>
              <p class="text-[10px] opacity-80 mt-0.5">Batas Maksimal Radius: <b>{{ setting.max_radius_meters }} meter</b></p>
            </div>
          </div>
        </div>

        <!-- Presensi Form / Card with Dual Mode (GPS & Scan QR) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
              <h2 class="text-base font-black text-slate-800">Aksi Presensi Guru Hari Ini</h2>
              <p class="text-xs text-slate-400 font-medium mt-0.5">Pilih metode presensi yang paling nyaman untuk Anda.</p>
            </div>

            <!-- Mode Selector Tabs -->
            <div class="inline-flex p-1 bg-slate-100/90 rounded-2xl border border-slate-200/80">
              <button 
                @click="switchMode('gps')"
                :class="[
                  attendanceMode === 'gps' ? 'bg-white text-emerald-800 font-bold shadow-sm' : 'text-slate-500 font-medium hover:text-slate-800',
                  'px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer'
                ]"
              >
                <MapPin class="w-3.5 h-3.5 text-emerald-600" />
                <span>Tombol GPS</span>
              </button>
              <button 
                @click="switchMode('qr')"
                :class="[
                  attendanceMode === 'qr' ? 'bg-white text-emerald-800 font-bold shadow-sm' : 'text-slate-500 font-medium hover:text-slate-800',
                  'px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition-all cursor-pointer'
                ]"
              >
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span>Scan QR Code</span>
              </button>
            </div>
          </div>

          <!-- If already submitted absence (Izin / Sakit / Tugas Luar) -->
          <div v-if="attendance && ['izin', 'sakit', 'tugas_luar'].includes(attendance.status)" class="p-6 bg-amber-50 border border-amber-200 rounded-2xl space-y-3">
            <div class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg">📝</span>
              <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-800">Keterangan Non-Hadir Terkirim</p>
                <p class="text-sm font-black text-amber-900 uppercase mt-0.5">STATUS: {{ attendance.status }}</p>
              </div>
            </div>
            <div class="p-3 bg-white/80 rounded-xl border border-amber-200 text-xs text-slate-700 font-medium italic">
              "{{ attendance.notes || '-' }}"
            </div>
          </div>

          <!-- MODE 1: SCAN QR CODE CAMERA -->
          <div v-else-if="attendanceMode === 'qr'" class="space-y-6 animate-fadeIn">
            <!-- QR Action Selector (Masuk / Pulang) -->
            <div class="p-4 bg-emerald-50/60 border border-emerald-200/80 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3">
              <div class="text-left w-full sm:w-auto">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">Pilih Aksi Scan:</span>
                <p class="text-xs font-bold text-slate-700">Tentukan jenis absensi yang ingin Anda catat</p>
              </div>
              <div class="flex items-center gap-2 w-full sm:w-auto">
                <button
                  type="button"
                  @click="qrActionType = 'check_in'"
                  :disabled="!!attendance?.check_in_time"
                  :class="[
                    qrActionType === 'check_in' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50',
                    'flex-1 sm:flex-initial px-4 py-2 rounded-xl text-xs font-bold transition-all disabled:opacity-40 cursor-pointer flex items-center justify-center gap-1.5'
                  ]"
                >
                  <LogIn class="w-3.5 h-3.5" />
                  <span>{{ attendance?.check_in_time ? '✓ Sudah Masuk' : 'Absen Masuk' }}</span>
                </button>
                <button
                  type="button"
                  @click="qrActionType = 'check_out'"
                  :disabled="!attendance?.check_in_time || !!attendance?.check_out_time"
                  :class="[
                    qrActionType === 'check_out' ? 'bg-teal-600 text-white shadow-md shadow-teal-600/20' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50',
                    'flex-1 sm:flex-initial px-4 py-2 rounded-xl text-xs font-bold transition-all disabled:opacity-40 cursor-pointer flex items-center justify-center gap-1.5'
                  ]"
                >
                  <LogOut class="w-3.5 h-3.5" />
                  <span>{{ attendance?.check_out_time ? '✓ Sudah Pulang' : 'Absen Pulang' }}</span>
                </button>
              </div>
            </div>

            <!-- Scanner Camera Box -->
            <div class="p-6 sm:p-8 bg-slate-900 rounded-[2rem] border border-slate-800 text-center space-y-5 shadow-xl text-white relative overflow-hidden">
              <div class="space-y-1.5 max-w-sm mx-auto">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full text-[10px] font-bold uppercase tracking-wider">
                  <span :class="[isScanning ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500', 'w-2 h-2 rounded-full']"></span>
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  <span>{{ isScanning ? 'Scanner Kamera Aktif' : 'Scanner Siap Digunakan' }}</span>
                </div>
                <p class="text-xs text-slate-300 font-medium">Arahkan kamera ke QR Code resmi di meja piket sekolah atau lobi.</p>
              </div>

              <!-- Camera Viewport Container -->
              <div class="relative w-full max-w-[320px] aspect-square mx-auto rounded-3xl overflow-hidden bg-black border-2 border-emerald-500/40 shadow-2xl flex items-center justify-center">
                <!-- Html5Qrcode Camera Target -->
                <div id="teacher-qr-reader" class="w-full h-full"></div>

                <!-- Laser scan animation beam overlay -->
                <div v-if="isScanning" class="pointer-events-none absolute inset-0 flex flex-col justify-between p-6 z-10">
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

                <!-- Stopped / Idle placeholder (Centered) -->
                <div v-if="!isScanning" class="absolute inset-0 z-20 flex flex-col items-center justify-center p-6 text-center space-y-3 bg-slate-950/90 backdrop-blur-xs">
                  <div class="w-16 h-16 bg-slate-800/80 rounded-2xl flex items-center justify-center mx-auto text-emerald-400 border border-slate-700 shadow-inner">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  </div>
                  <div class="space-y-0.5">
                    <p class="text-xs font-bold text-slate-200">Kamera Sedang Berhenti</p>
                    <p class="text-[11px] text-slate-400">Tekan tombol di bawah untuk menyalakan</p>
                  </div>
                  <button 
                    @click="startScanner" 
                    class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl text-xs transition-all shadow-lg shadow-emerald-900/30 flex items-center gap-2 cursor-pointer hover:scale-[1.03] active:scale-[0.98]"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Nyalakan Kamera</span>
                  </button>
                </div>
              </div>

              <!-- Scanner Errors -->
              <div v-if="scannerError" class="p-3 bg-red-500/15 border border-red-500/30 rounded-xl text-red-200 text-xs font-medium max-w-sm mx-auto">
                {{ scannerError }}
              </div>

              <!-- Active Controls (Only when scanning) -->
              <div v-if="isScanning" class="flex flex-wrap items-center justify-center gap-3 pt-1">
                <button 
                  @click="stopScanner" 
                  class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition-all border border-slate-700 cursor-pointer flex items-center gap-1.5"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <span>Jeda Kamera</span>
                </button>
                <button 
                  v-if="cameras.length > 1" 
                  @click="switchCamera" 
                  class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition-all border border-slate-700 cursor-pointer flex items-center gap-1.5"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                  <span>Ganti Kamera</span>
                </button>
              </div>
            </div>

            <!-- Upload Gambar / File QR Code Alternatif -->
            <div class="p-6 bg-slate-50 border border-slate-200/80 rounded-3xl space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  </div>
                  <div>
                    <h4 class="text-xs font-bold text-slate-800">Atau Unggah / Upload Gambar QR Code</h4>
                    <p class="text-[11px] text-slate-400 font-medium">Jika kamera terkendala, pilih atau upload foto QR Code dari galeri HP.</p>
                  </div>
                </div>
              </div>

              <div 
                @click="triggerFileInput" 
                class="border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-white hover:bg-emerald-50/40 rounded-2xl p-6 text-center cursor-pointer transition-all space-y-2 group shadow-2xs"
              >
                <input 
                  type="file" 
                  ref="qrFileInput" 
                  accept="image/*" 
                  @change="handleFileUpload" 
                  class="hidden" 
                />
                
                <div v-if="uploadProcessing" class="py-4 space-y-2">
                  <div class="w-8 h-8 border-3 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                  <p class="text-xs font-bold text-emerald-700">Membaca & Memvalidasi QR Code dari gambar...</p>
                </div>

                <div v-else-if="uploadedPreview" class="space-y-3">
                  <img :src="uploadedPreview" alt="Pratinjau QR" class="w-32 h-32 object-contain mx-auto rounded-xl border border-slate-200 bg-slate-50 p-2" />
                  <p class="text-xs font-bold text-slate-700">Klik untuk mengganti foto QR</p>
                </div>

                <div v-else class="space-y-2">
                  <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                  </div>
                  <p class="text-xs font-bold text-slate-700">Pilih Foto QR dari Galeri / Dokumen</p>
                  <p class="text-[10px] text-slate-400 font-medium">Mendukung format PNG, JPG, JPEG, WEBP</p>
                </div>
              </div>
            </div>
          </div>

          <!-- MODE 2: TOMBOL GPS MANDIRI (EXISTING) -->
          <div v-else class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Check-In Card -->
              <div class="p-5 bg-slate-50 border border-slate-200/60 rounded-2xl space-y-3 flex flex-col justify-between">
                <div>
                  <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">1. Absen Masuk</span>
                    <span v-if="attendance?.check_in_time" class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-lg uppercase">
                      Sudah Masuk
                    </span>
                  </div>
                  <p class="text-lg font-black text-slate-800 mt-2 font-lexend">
                    {{ attendance?.check_in_time || '--:--:--' }}
                  </p>
                  <p v-if="attendance?.check_in_distance_meters !== null && attendance?.check_in_distance_meters !== undefined" class="text-[11px] text-slate-500 mt-0.5 font-medium">
                    Jarak: {{ attendance.check_in_distance_meters }} meter dari sekolah
                  </p>
                </div>

                <button
                  @click="doAttendance('check_in')"
                  :disabled="!!attendance?.check_in_time || !isInRadius || submitting"
                  class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-emerald-600/20 cursor-pointer flex items-center justify-center gap-2"
                >
                  <LogIn class="w-4 h-4" />
                  <span>{{ attendance?.check_in_time ? 'Sudah Absen Masuk' : 'Absen Masuk' }}</span>
                </button>
              </div>

              <!-- Check-Out Card -->
              <div class="p-5 bg-slate-50 border border-slate-200/60 rounded-2xl space-y-3 flex flex-col justify-between">
                <div>
                  <div class="flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. Absen Pulang</span>
                    <span v-if="attendance?.check_out_time" class="px-2.5 py-1 bg-teal-100 text-teal-800 text-[10px] font-bold rounded-lg uppercase">
                      Sudah Pulang
                    </span>
                  </div>
                  <p class="text-lg font-black text-slate-800 mt-2 font-lexend">
                    {{ attendance?.check_out_time || '--:--:--' }}
                  </p>
                  <p v-if="attendance?.check_out_distance_meters !== null && attendance?.check_out_distance_meters !== undefined" class="text-[11px] text-slate-500 mt-0.5 font-medium">
                    Jarak: {{ attendance.check_out_distance_meters }} meter dari sekolah
                  </p>
                </div>

                <button
                  @click="doAttendance('check_out')"
                  :disabled="!attendance?.check_in_time || !!attendance?.check_out_time || !isInRadius || submitting"
                  class="w-full py-3 bg-teal-600 hover:bg-teal-700 disabled:opacity-40 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-teal-600/20 cursor-pointer flex items-center justify-center gap-2"
                >
                  <LogOut class="w-4 h-4" />
                  <span>{{ attendance?.check_out_time ? 'Sudah Absen Pulang' : 'Absen Pulang' }}</span>
                </button>
              </div>
            </div>

            <!-- Lock warning if outside radius -->
            <div v-if="!isInRadius && !attendance?.check_in_time" class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-red-800">
              <AlertCircle class="w-5 h-5 text-red-500 flex-shrink-0" />
              <span class="text-xs font-semibold">
                Tombol Absen GPS terkunci karena posisi Anda berada {{ currentDistance }}m (di luar radius {{ setting.max_radius_meters }}m dari sekolah). Anda bisa menggunakan tab <b>Scan QR Code</b> jika sudah berada di sekolah.
              </span>
            </div>

            <!-- Alternative Section: Form Box Teks Izin / Sakit / Tugas Luar -->
            <div v-if="!attendance?.check_in_time" class="pt-4 border-t border-slate-100 space-y-4">
              <button 
                @click="showAbsenceForm = !showAbsenceForm" 
                class="text-xs font-bold text-amber-700 hover:text-amber-800 underline flex items-center gap-1.5 cursor-pointer"
              >
                <span>{{ showAbsenceForm ? '▲ Sembunyikan Form Halangan' : '▼ Berhalangan Hadir? (Izin / Sakit / Tugas Luar)' }}</span>
              </button>

              <div v-if="showAbsenceForm" class="p-5 bg-amber-50/50 border border-amber-200/80 rounded-2xl space-y-4 animate-fadeIn">
                <p class="text-xs font-bold text-amber-900 uppercase tracking-wider">Form Keterangan Berhalangan Hadir</p>

                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700">Pilih Alasan Ketidakhadiran <span class="text-red-500">*</span></label>
                  <select v-model="absenceStatus" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="tugas_luar">Tugas Luar / Dinas</option>
                  </select>
                </div>

                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700">Box Teks Keterangan Alasan <span class="text-red-500">*</span></label>
                  <textarea
                    v-model="absenceNotes"
                    rows="3"
                    class="w-full bg-white border border-slate-200 rounded-xl p-4 text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/30 resize-none"
                    placeholder="Tuliskan keterangan alasan Izin / Sakit / Tugas Luar Anda di sini secara rinci..."
                  ></textarea>
                </div>

                <button
                  @click="submitAbsence"
                  :disabled="submitting"
                  class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-amber-600/20 cursor-pointer disabled:opacity-50"
                >
                  {{ submitting ? 'Kirim...' : 'Kirim Keterangan Halangan' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Col 2: Info & Rules Card -->
      <div class="space-y-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Ketentuan Presensi Kehadiran</h3>
          
          <div class="space-y-3">
            <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1">
              <span class="font-bold text-slate-700 flex items-center gap-1.5">
                <Clock class="w-3.5 h-3.5 text-amber-600" />
                <span>Jam Batas Terlambat:</span>
              </span>
              <p class="font-semibold text-amber-600 font-mono pl-5">{{ setting.work_late_time || '07:15:00' }} WIB</p>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1">
              <span class="font-bold text-slate-700 flex items-center gap-1.5">
                <Compass class="w-3.5 h-3.5 text-emerald-600" />
                <span>Radius Toleransi GPS:</span>
              </span>
              <p class="font-semibold text-emerald-600 font-mono pl-5">{{ setting.max_radius_meters }} meter</p>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1">
              <span class="font-bold text-slate-700 flex items-center gap-1.5">
                <MapPin class="w-3.5 h-3.5 text-emerald-600" />
                <span>Titik Lokasi Sekolah:</span>
              </span>
              <p class="font-medium text-slate-500 font-mono text-[11px] pl-5">{{ setting.latitude }}, {{ setting.longitude }}</p>
            </div>
          </div>
        </div>

        <!-- Riwayat Bulan Ini -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Riwayat Bulan Ini</h3>
          
          <div v-if="history.length === 0" class="text-center py-6 text-slate-400 text-xs font-medium">
            Belum ada data presensi bulan ini.
          </div>

          <div v-else class="space-y-2 max-h-64 overflow-y-auto pr-1 custom-scrollbar">
            <div v-for="h in history" :key="h.id" class="p-3 bg-slate-50 rounded-xl flex items-center justify-between text-xs border border-slate-100">
              <div>
                <p class="font-bold text-slate-800">{{ h.date }}</p>
                <p class="text-[10px] text-slate-400 font-medium">
                  In: {{ h.check_in_time || '-' }} | Out: {{ h.check_out_time || '-' }}
                </p>
              </div>
              <span :class="[
                h.status === 'hadir' ? 'bg-emerald-100 text-emerald-800' :
                h.status === 'terlambat' ? 'bg-amber-100 text-amber-800' :
                'bg-teal-100 text-teal-800',
                'px-2 py-0.5 rounded-md font-bold text-[10px] uppercase'
              ]">
                {{ h.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Riwayat Pengajuan Koreksi Absen -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Status Permohonan Koreksi</h3>
            <button @click="showRequestModal = true" class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 shadow-2xs cursor-pointer">
              <Plus class="w-3 h-3" />
              <span>Ajukan Baru</span>
            </button>
          </div>

          <div v-if="correctionRequests.length === 0" class="text-center py-6 text-slate-400 text-xs font-medium">
            Belum ada permohonan koreksi.
          </div>

          <div v-else class="space-y-2.5 max-h-64 overflow-y-auto pr-1 custom-scrollbar">
            <div v-for="req in correctionRequests" :key="req.id" class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5 text-xs">
              <div class="flex items-center justify-between">
                <span class="font-bold text-slate-800 font-mono">{{ req.date }}</span>
                <span :class="[
                  req.approval_status === 'approved' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' :
                  req.approval_status === 'rejected' ? 'bg-rose-100 text-rose-800 border-rose-200' :
                  'bg-amber-100 text-amber-800 border-amber-200',
                  'px-2.5 py-0.5 rounded-full font-bold text-[10px] uppercase tracking-wider border inline-flex items-center gap-1'
                ]">
                  <CheckCircle2 v-if="req.approval_status === 'approved'" class="w-3 h-3 text-emerald-700" />
                  <AlertCircle v-else-if="req.approval_status === 'rejected'" class="w-3 h-3 text-rose-700" />
                  <Clock v-else class="w-3 h-3 text-amber-700" />
                  <span>{{ req.approval_status === 'approved' ? 'Disetujui' : (req.approval_status === 'rejected' ? 'Ditolak' : 'Pending') }}</span>
                </span>
              </div>
              <p class="text-[11px] font-medium text-slate-600">
                Status Baru: <b class="uppercase text-emerald-700">{{ req.target_status }}</b>
              </p>
              <p class="text-[11px] text-slate-500 italic">"{{ req.reason }}"</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form Ajukan Koreksi Absen -->
    <div v-if="showRequestModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
          <h3 class="text-base font-black text-slate-800">Form Permohonan Koreksi Absen</h3>
          <button @click="showRequestModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="submitCorrectionRequest" class="p-6 space-y-4">
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700">Tanggal Absen Yang Diubah *</label>
            <input v-model="requestForm.date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800" required />
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700">Status Kehadiran Seharusnya *</label>
            <select v-model="requestForm.target_status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800" required>
              <option value="hadir">Hadir (Tepat Waktu)</option>
              <option value="terlambat">Terlambat</option>
              <option value="izin">Izin</option>
              <option value="sakit">Sakit</option>
              <option value="tugas_luar">Tugas Luar / Dinas</option>
            </select>
          </div>

          <div v-if="['hadir', 'terlambat'].includes(requestForm.target_status)" class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700">Jam Masuk (Opsional)</label>
              <input v-model="requestForm.requested_check_in_time" type="time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800" />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700">Jam Pulang (Opsional)</label>
              <input v-model="requestForm.requested_check_out_time" type="time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800" />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700">Alasan Permohonan Koreksi (Box Teks) *</label>
            <textarea
              v-model="requestForm.reason"
              rows="3"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs font-medium text-slate-800 resize-none"
              placeholder="Tuliskan alasan lengkap kenapa mengajukan perubahan absen (misal: Lupa klik absen masuk karena rapat pagi)..."
              required
            ></textarea>
          </div>

          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
            <button type="button" @click="showRequestModal = false" class="px-5 py-2.5 bg-slate-100 text-slate-600 font-bold rounded-xl text-xs">
              Batal
            </button>
            <button type="submit" :disabled="submitting" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow-md shadow-amber-600/20 disabled:opacity-50">
              {{ submitting ? 'Mengirim...' : 'Kirim Permohonan Koreksi' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { Html5Qrcode } from 'html5-qrcode';
import {
  MapPin,
  Clock,
  Compass,
  Navigation,
  CheckCircle2,
  AlertCircle,
  RotateCcw,
  Check,
  Plus,
  LogIn,
  LogOut
} from 'lucide-vue-next';

const toast = useToast();
const loading = ref(true);
const submitting = ref(false);

const attendanceMode = ref('gps'); // 'gps' | 'qr'
const qrActionType = ref('check_in'); // 'check_in' | 'check_out'
const isScanning = ref(false);
const scannerError = ref('');
const cameras = ref([]);
const selectedCameraIndex = ref(0);
const qrFileInput = ref(null);
const uploadProcessing = ref(false);
const uploadedPreview = ref('');
let html5QrCode = null;

const setting = ref({
  max_radius_meters: 100,
  latitude: -6.20880000,
  longitude: 106.84560000,
  work_late_time: '07:15:00',
});

const todayDate = ref('');
const attendance = ref(null);
const history = ref([]);

const currentLat = ref(null);
const currentLng = ref(null);
const currentDistance = ref(0);
const gpsError = ref('');

const showAbsenceForm = ref(false);
const absenceStatus = ref('izin');
const absenceNotes = ref('');

const showRequestModal = ref(false);
const correctionRequests = ref([]);
const requestForm = reactive({
  date: new Date().toISOString().substring(0, 10),
  target_status: 'hadir',
  requested_check_in_time: '07:00',
  requested_check_out_time: '15:00',
  reason: '',
});

const formattedToday = computed(() => {
  if (!todayDate.value) return new Date().toLocaleDateString('id-ID');
  return new Date(todayDate.value).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
});

const isInRadius = computed(() => {
  if (!currentLat.value || !currentLng.value) return false;
  return currentDistance.value <= setting.value.max_radius_meters;
});

function calculateHaversine(lat1, lon1, lat2, lon2) {
  const R = 6371000; // Meters
  const dLat = (lat2 - lat1) * (Math.PI / 180);
  const dLon = (lon2 - lon1) * (Math.PI / 180);
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return Math.round(R * c);
}

function detectGPS() {
  gpsError.value = '';
  if (!navigator.geolocation) {
    gpsError.value = 'Perangkat / Peramban Anda tidak mendukung fitur Geolocation GPS.';
    return;
  }

  const successCallback = (pos) => {
    gpsError.value = '';
    currentLat.value = pos.coords.latitude;
    currentLng.value = pos.coords.longitude;

    if (setting.value.latitude && setting.value.longitude) {
      currentDistance.value = calculateHaversine(
        setting.value.latitude,
        setting.value.longitude,
        currentLat.value,
        currentLng.value
      );
    }
  };

  const handleErr = (finalErr) => {
    if (finalErr?.code === 1) {
      gpsError.value = 'Izin akses lokasi ditolak oleh peramban/HP. Silakan izinkan lokasi atau gunakan metode Scan QR Code Sekolah.';
    } else if (finalErr?.code === 2) {
      gpsError.value = 'Sinyal GPS tidak ditemukan. Harap pastikan fitur Lokasi/GPS di HP Anda telah dinyalakan atau gunakan Scan QR.';
    } else if (finalErr?.code === 3) {
      gpsError.value = 'Waktu deteksi GPS habis. Silakan gunakan Scan QR Code sekolah.';
    } else {
      gpsError.value = 'Gagal membaca posisi GPS HP. Anda dapat menggunakan Scan QR Code Sekolah.';
    }
  };

  try {
    navigator.geolocation.getCurrentPosition(
      successCallback,
      (err) => {
        if (err?.code === 1) {
          handleErr(err);
          return;
        }
        navigator.geolocation.getCurrentPosition(
          successCallback,
          handleErr,
          { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
        );
      },
      { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
    );
  } catch (e) {
    gpsError.value = 'Peramban menolak permintaan lokasi GPS.';
  }
}

function useSchoolLocationAsFallback() {
  if (setting.value.latitude && setting.value.longitude) {
    currentLat.value = setting.value.latitude;
    currentLng.value = setting.value.longitude;
    currentDistance.value = 0;
    gpsError.value = '';
    toast.success('Menggunakan koordinat lokasi sekolah (Jarak: 0 Meter). Tombol Absen TERBUKA!');
  }
}

async function switchMode(mode) {
  attendanceMode.value = mode;
  if (mode === 'qr') {
    await nextTick();
    startScanner();
  } else {
    stopScanner();
    detectGPS();
  }
}

async function startScanner() {
  scannerError.value = '';

  // Check if secure context is required
  const isLocal = ['localhost', '127.0.0.1'].includes(window.location.hostname);
  if (!window.isSecureContext && !isLocal) {
    scannerError.value = 'Kamera live memerlukan koneksi HTTPS di peramban HP. Gunakan fitur "Unggah / Foto Gambar QR" di bawah untuk presensi.';
    return;
  }

  try {
    const devices = await Html5Qrcode.getCameras();
    if (!devices || devices.length === 0) {
      scannerError.value = 'Kamera tidak ditemukan pada perangkat Anda.';
      return;
    }
    cameras.value = devices;

    // Prefer back camera
    let cameraId = devices[0].id;
    const backCam = devices.find(d => d.label.toLowerCase().includes('back') || d.label.toLowerCase().includes('rear') || d.label.toLowerCase().includes('belakang'));
    if (backCam) {
      cameraId = backCam.id;
      selectedCameraIndex.value = devices.indexOf(backCam);
    }

    if (!html5QrCode) {
      html5QrCode = new Html5Qrcode("teacher-qr-reader");
    }

    if (isScanning.value) {
      try {
        await html5QrCode.stop();
      } catch (e) {
        console.warn(e);
      }
    }

    await html5QrCode.start(
      cameraId,
      {
        fps: 15,
        qrbox: { width: 240, height: 240 },
        aspectRatio: 1.0,
      },
      async (decodedText) => {
        await handleQrScanned(decodedText);
      },
      (errorMessage) => {}
    );
    isScanning.value = true;
  } catch (err) {
    console.error('Camera Scanner Error:', err);
    scannerError.value = 'Peramban HP membatasi kamera live tanpa HTTPS. Silakan gunakan tombol "Unggah / Foto QR" di bawah.';
    isScanning.value = false;
  }
}

async function stopScanner() {
  if (html5QrCode && isScanning.value) {
    try {
      await html5QrCode.stop();
    } catch (err) {
      console.warn('Error stopping scanner:', err);
    } finally {
      isScanning.value = false;
    }
  }
}

async function switchCamera() {
  if (cameras.value.length <= 1) return;
  selectedCameraIndex.value = (selectedCameraIndex.value + 1) % cameras.value.length;
  await stopScanner();
  try {
    const cameraId = cameras.value[selectedCameraIndex.value].id;
    await html5QrCode.start(
      cameraId,
      {
        fps: 15,
        qrbox: { width: 240, height: 240 },
        aspectRatio: 1.0,
      },
      async (decodedText) => {
        await handleQrScanned(decodedText);
      },
      () => {}
    );
    isScanning.value = true;
    toast.success('Beralih ke kamera: ' + cameras.value[selectedCameraIndex.value].label);
  } catch (err) {
    console.error(err);
    toast.error('Gagal beralih kamera.');
  }
}

function triggerFileInput() {
  qrFileInput.value?.click();
}

let fileQrDecoder = null;
async function handleFileUpload(event) {
  const file = event.target.files?.[0];
  if (!file) return;

  uploadedPreview.value = URL.createObjectURL(file);
  uploadProcessing.value = true;
  scannerError.value = '';

  try {
    // 1. Safely stop live camera if running
    if (isScanning.value && html5QrCode) {
      try {
        await html5QrCode.stop();
      } catch (e) {
        console.warn('Live camera stop warning before file scan:', e);
      } finally {
        isScanning.value = false;
      }
    }

    // 2. Initialize dedicated hidden container for file decoding
    if (!fileQrDecoder) {
      let fileContainer = document.getElementById("teacher-file-qr-decoder-box");
      if (!fileContainer) {
        fileContainer = document.createElement("div");
        fileContainer.id = "teacher-file-qr-decoder-box";
        fileContainer.style.display = "none";
        document.body.appendChild(fileContainer);
      }
      fileQrDecoder = new Html5Qrcode("teacher-file-qr-decoder-box");
    }

    // 3. Scan the image file cleanly
    const decodedText = await fileQrDecoder.scanFile(file, true);
    await handleQrScanned(decodedText, true);
  } catch (err) {
    console.error('File QR Scan Error:', err);
    const msg = 'Tidak dapat mendeteksi QR Code dari gambar yang dipilih. Pastikan foto QR Code jelas, terang, dan fokus.';
    toast.error(msg);
    scannerError.value = msg;
  } finally {
    uploadProcessing.value = false;
    if (event.target) event.target.value = '';
  }
}

async function handleQrScanned(decodedText, isFromFile = false) {
  await stopScanner();
  submitting.value = true;
  try {
    const res = await api.post('teacher/presensi/scan-qr', {
      qr_payload: decodedText,
      type: qrActionType.value,
      notes: isFromFile ? 'Presensi via Upload Gambar QR' : 'Presensi via Scan QR Code Kamera',
    });
    toast.success(res.message || res.data?.message || (isFromFile ? 'Presensi via Upload Gambar QR Berhasil!' : 'Presensi via Scan QR Berhasil!'));
    await loadData();
  } catch (err) {
    console.error(err);
    const msg = err.response?.data?.message || 'Gagal memproses QR Code. Pastikan memindai/mengunggah QR Code resmi sekolah hari ini.';
    toast.error(msg);
    scannerError.value = msg;
  } finally {
    submitting.value = false;
  }
}

async function loadRequests() {
  try {
    const res = await api.get('teacher/presensi/requests');
    correctionRequests.value = res?.requests || res?.data?.requests || [];
  } catch (err) {
    console.error(err);
  }
}

async function submitCorrectionRequest() {
  if (!requestForm.reason.trim()) {
    toast.error('Alasan permohonan koreksi wajib diisi!');
    return;
  }

  submitting.value = true;
  try {
    const res = await api.post('teacher/presensi/requests', requestForm);
    toast.success(res.message || res.data?.message || 'Pengajuan permohonan berhasil dikirim!');
    showRequestModal.value = false;
    requestForm.reason = '';
    await loadRequests();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal mengirim permohonan.');
  } finally {
    submitting.value = false;
  }
}

async function loadData() {
  loading.value = true;
  try {
    const resToday = await api.get('teacher/presensi/today');
    setting.value = resToday?.setting || resToday?.data?.setting || setting.value;
    todayDate.value = resToday?.today_date || resToday?.data?.today_date || '';
    attendance.value = resToday?.attendance || resToday?.data?.attendance || null;

    if (attendance.value?.check_in_time && !attendance.value?.check_out_time) {
      qrActionType.value = 'check_out';
    } else {
      qrActionType.value = 'check_in';
    }

    const resHist = await api.get('teacher/presensi/history');
    history.value = resHist?.attendances || resHist?.data?.attendances || [];

    await loadRequests();
    detectGPS();
  } catch (err) {
    console.error(err);
    toast.error('Gagal memuat data presensi.');
  } finally {
    loading.value = false;
  }
}

async function doAttendance(type) {
  if (!isInRadius.value) {
    toast.error(`Anda berada di luar radius (${currentDistance.value}m dari sekolah).`);
    return;
  }

  submitting.value = true;
  try {
    const res = await api.post('teacher/presensi', {
      type: type,
      latitude: currentLat.value,
      longitude: currentLng.value,
    });
    toast.success(res.data.message);
    await loadData();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal melakukan presensi.');
  } finally {
    submitting.value = false;
  }
}

async function submitAbsence() {
  if (!absenceNotes.value.trim()) {
    toast.error('Keterangan alasan wajib diisi dalam box teks!');
    return;
  }

  submitting.value = true;
  try {
    const res = await api.post('teacher/presensi', {
      type: 'absence',
      status: absenceStatus.value,
      notes: absenceNotes.value.trim(),
    });
    toast.success(res.data.message);
    showAbsenceForm.value = false;
    await loadData();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal mengirim keterangan halangan.');
  } finally {
    submitting.value = false;
  }
}

onMounted(loadData);

onUnmounted(() => {
  stopScanner();
});
</script>
