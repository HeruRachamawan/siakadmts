<template>
  <div class="space-y-6 font-inter">
    <!-- Top Header -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Jadwal Pelajaran & Kegiatan Sekolah</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Format Matriks Master Jadwal — Klik sel kotak <span class="font-bold text-emerald-600">+</span> pada tabel untuk menambah/mengedit jadwal.</p>
        </div>
      </div>

      <div class="flex items-center gap-2.5 flex-wrap">
        <button
          @click="exportExcelSchedules"
          class="px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 transition-colors flex items-center gap-2 shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span>Export Excel (.xlsx)</span>
        </button>

        <button
          @click="openPrintModal"
          class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl text-xs hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          <span>Cetak Jadwal</span>
        </button>

        <button
          @click="openModal(false, activeYaspinDay)"
          class="px-5 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
          <span>Tambah Jadwal</span>
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[180px]">
        <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Filter Kelas</label>
        <select v-model="selectedClass" @change="fetchSchedules" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer">
          <option value="">-- Semua Kelas --</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[180px]">
        <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Filter Guru Pengajar</label>
        <select v-model="selectedTeacher" @change="fetchSchedules" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer">
          <option value="">-- Semua Guru --</option>
          <option v-for="tcher in teachers" :key="tcher.id" :value="tcher.id">{{ tcher.full_name }}</option>
        </select>
      </div>

      <div class="flex-1 min-w-[160px]">
        <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Kategori</label>
        <select v-model="filterType" @change="fetchSchedules" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer">
          <option value="all">Semua (Pelajaran & Kegiatan)</option>
          <option value="subject">Pelajaran Saja</option>
          <option value="activity">Kegiatan / Eskul Saja</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-[2rem] p-16 text-center text-slate-400 text-xs font-medium border border-slate-100">
      <svg class="animate-spin h-8 w-8 text-emerald-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
      Memuat matriks jadwal pelajaran...
    </div>

    <!-- Master Timetable View -->
    <div v-else class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
      <!-- Day Switcher Sub-Header -->
      <div class="px-6 py-4 bg-slate-900 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-800">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          <div>
            <h3 class="font-bold text-sm sm:text-base text-white">Matriks Jadwal: Hari {{ getActiveDayName() }}</h3>
            <p class="text-xs text-slate-400 font-normal">Tampilan jadwal pelajaran visual per kelas</p>
          </div>
        </div>

        <!-- Day Tabs (Clean modern pills) -->
        <div class="flex p-1 bg-slate-800/90 rounded-xl border border-slate-700/80 overflow-x-auto max-w-full">
          <button
            v-for="day in daysList"
            :key="day.key"
            @click="activeYaspinDay = day.key"
            :class="[activeYaspinDay === day.key ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-400 hover:text-slate-200 font-medium', 'px-3.5 py-1.5 text-xs rounded-lg transition-all cursor-pointer whitespace-nowrap']"
          >
            {{ day.name }}
          </button>
        </div>
      </div>

      <!-- Master Table -->
      <div class="p-6 overflow-x-auto">
        <table class="w-full text-center border-collapse text-xs min-w-[700px]">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200/80 text-slate-600">
              <th class="p-3 w-12 font-bold text-slate-500 uppercase tracking-wider text-[11px]">No</th>
              <th class="p-3 w-40 font-bold text-slate-500 uppercase tracking-wider text-[11px]">
                Waktu <span class="text-emerald-700 font-semibold lowercase">({{ activeYaspinDay === 'senin' ? 'senin' : 'selasa - sabtu' }})</span>
              </th>
              <!-- Column for each class -->
              <th v-for="cls in filteredClasses" :key="cls.id" class="p-3 font-bold text-slate-800 border-l border-slate-200/70">
                <span class="inline-block px-3 py-1 bg-emerald-50 border border-emerald-200/80 text-emerald-800 rounded-lg text-xs font-bold">
                  {{ cls.name }}
                </span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="slot in activeYaspinSlots" :key="slot.no" class="hover:bg-slate-50/50 transition-colors">
              <!-- NO -->
              <td class="p-3 font-semibold text-slate-400 text-xs bg-slate-50/30">{{ slot.no }}</td>
              
              <!-- WAKTU -->
              <td class="p-3 font-semibold text-slate-700 text-xs tracking-tight" :class="slot.isBreak ? 'bg-amber-50/50 text-amber-900 font-bold' : (slot.isGeneral ? 'bg-emerald-50/30 text-emerald-900 font-bold' : 'bg-slate-50/20')">
                {{ slot.start }} - {{ slot.end }}
              </td>

              <!-- Merged General Event (Upacara, Tadarusan, Istirahat, Dzuhur) -->
              <td v-if="slot.isGeneral || slot.isBreak" :colspan="filteredClasses.length || 1" class="p-2.5 border-l border-slate-200/60">
                <div
                  class="py-2 px-4 rounded-xl font-bold text-xs text-center border transition-all"
                  :class="slot.isBreak ? 'bg-amber-50 text-amber-900 border-amber-200' : 'bg-emerald-50 text-emerald-900 border-emerald-200'"
                >
                  <span v-if="slot.isBreak">☕ </span>
                  <span v-else-if="slot.title.includes('UPACARA')">🇮🇩 </span>
                  <span v-else-if="slot.title.includes('TADARUSAN')">📖 </span>
                  <span v-else-if="slot.title.includes('DZUHUR')">🕌 </span>
                  <span>{{ slot.title }}</span>
                </div>
              </td>

              <!-- Class Slots -->
              <template v-else>
                <td v-for="cls in filteredClasses" :key="cls.id" class="p-2 border-l border-slate-200/60 h-20 vertical-middle relative">
                  <!-- Matching schedule item -->
                  <div
                    v-if="getYaspinScheduleItem(activeYaspinDay, cls.id, slot)"
                    class="w-full h-full p-2.5 rounded-xl border bg-white border-slate-200/80 shadow-2xs flex flex-col justify-center items-center group relative hover:border-emerald-400 hover:shadow-xs transition-all cursor-pointer"
                  >
                    <div class="font-bold text-xs text-slate-900 text-center leading-snug">
                      {{ getYaspinScheduleItem(activeYaspinDay, cls.id, slot).subject?.name || getYaspinScheduleItem(activeYaspinDay, cls.id, slot).activity_name }}
                    </div>
                    <div class="text-[11px] font-medium text-emerald-700 truncate max-w-[140px] mt-1 flex items-center gap-1 justify-center">
                      <svg class="w-3 h-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      <span>{{ getYaspinScheduleItem(activeYaspinDay, cls.id, slot).teacher?.full_name || '-' }}</span>
                    </div>

                    <!-- Quick action buttons on hover -->
                    <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-xs text-white flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl p-1 z-10">
                      <button @click.stop="editSchedule(getYaspinScheduleItem(activeYaspinDay, cls.id, slot))" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 rounded-lg text-[10px] font-bold shadow-xs cursor-pointer">Edit</button>
                      <button @click.stop="deleteSchedule(getYaspinScheduleItem(activeYaspinDay, cls.id, slot).id)" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 rounded-lg text-[10px] font-bold shadow-xs cursor-pointer">Hapus</button>
                    </div>
                  </div>

                  <!-- Empty cell button -->
                  <button
                    v-else
                    @click="openYaspinSlot(activeYaspinDay, cls.id, slot)"
                    class="w-full h-full min-h-[48px] rounded-xl border border-dashed border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/40 text-slate-300 hover:text-emerald-600 font-bold text-xs transition-all flex items-center justify-center cursor-pointer group/add"
                    title="Klik untuk mengisi jadwal"
                  >
                    <span class="group-hover/add:scale-125 transition-transform text-slate-400 group-hover/add:text-emerald-600 font-bold text-sm">+</span>
                  </button>
                </td>
              </template>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form (Create / Edit) -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-[2rem] max-w-lg w-full p-8 shadow-2xl space-y-6 relative overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-black text-slate-800 font-lexend uppercase">
              {{ isEditing ? 'Edit Jadwal' : (form.is_activity ? 'Tambah Kegiatan Sekolah' : 'Tambah Jadwal Pelajaran') }}
            </h3>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Sistem akan menolak otomatis bila terjadi bentrok waktu guru / kelas.</p>
          </div>
          <button @click="showModal = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400 cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Anti-Conflict Error Banner -->
        <div v-if="conflictError" class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-xs font-bold text-red-800">Peringatan Bentrok Waktu!</p>
            <p class="text-[11px] text-red-600 font-medium mt-0.5 leading-relaxed">{{ conflictError }}</p>
          </div>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <!-- Toggle Mode (Only when creating) -->
          <div v-if="!isEditing" class="flex p-1 bg-slate-100 rounded-xl mb-4">
            <button
              type="button"
              @click="form.is_activity = false"
              :class="[!form.is_activity ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 font-medium', 'flex-1 py-2 text-xs rounded-lg transition-colors cursor-pointer']"
            >
              Jadwal Pelajaran
            </button>
            <button
              type="button"
              @click="form.is_activity = true"
              :class="[form.is_activity ? 'bg-white text-emerald-700 shadow-sm font-bold' : 'text-slate-500 font-medium', 'flex-1 py-2 text-xs rounded-lg transition-colors cursor-pointer']"
            >
              Kegiatan / Eskul
            </button>
          </div>

          <!-- Activity Form Fields -->
          <template v-if="form.is_activity">
            <div class="space-y-1">
              <label class="block text-[11px] font-bold text-slate-600 uppercase">Nama Kegiatan</label>
              <input v-model="form.activity_name" type="text" placeholder="Contoh: Upacara Bendera, Sholat Dzuhur, Pramuka" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 font-medium" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="block text-[11px] font-bold text-slate-600 uppercase">Kategori</label>
                <select v-model="form.activity_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer" required>
                  <option value="upacara">Upacara</option>
                  <option value="religi">Religi / Sholat</option>
                  <option value="ekstrakurikuler">Ekstrakurikuler</option>
                  <option value="kokurikuler">Kokurikuler</option>
                  <option value="istirahat">Istirahat</option>
                  <option value="lainnya">Lainnya</option>
                </select>
              </div>

              <div class="space-y-1">
                <label class="block text-[11px] font-bold text-slate-600 uppercase">Target Kelas</label>
                <select v-model="form.class_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer">
                  <option :value="null">-- Semua Kelas (Seluruh Sekolah) --</option>
                  <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                </select>
              </div>
            </div>
          </template>

          <!-- Subject Form Fields -->
          <template v-else>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="block text-[11px] font-bold text-slate-600 uppercase">Pilih Kelas</label>
                <select v-model="form.class_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer" required>
                  <option value="">-- Pilih Kelas --</option>
                  <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                </select>
              </div>

              <div class="space-y-1">
                <label class="block text-[11px] font-bold text-slate-600 uppercase">Pilih Mata Pelajaran</label>
                <select v-model="form.subject_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer" required>
                  <option value="">-- Pilih Mapel --</option>
                  <option v-for="sbj in subjects" :key="sbj.id" :value="sbj.id">{{ sbj.name }}</option>
                </select>
              </div>
            </div>

            <div class="space-y-1">
              <label class="block text-[11px] font-bold text-slate-600 uppercase">Guru Pengajar</label>
              <select v-model="form.teacher_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer" required>
                <option value="">-- Pilih Guru --</option>
                <option v-for="tcher in teachers" :key="tcher.id" :value="tcher.id">{{ tcher.full_name }}</option>
              </select>
            </div>
          </template>

          <!-- Day & Time Row -->
          <div class="grid grid-cols-3 gap-3 pt-2 border-t border-slate-100">
            <div class="space-y-1">
              <label class="block text-[11px] font-bold text-slate-600 uppercase">Hari</label>
              <select v-model="form.day" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer" required>
                <option value="senin">Senin</option>
                <option value="selasa">Selasa</option>
                <option value="rabu">Rabu</option>
                <option value="kamis">Kamis</option>
                <option value="jumat">Jumat</option>
                <option value="sabtu">Sabtu</option>
              </select>
            </div>

            <div class="space-y-1">
              <label class="block text-[11px] font-bold text-slate-600 uppercase">Jam Mulai</label>
              <input v-model="form.start_time" type="time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" required />
            </div>

            <div class="space-y-1">
              <label class="block text-[11px] font-bold text-slate-600 uppercase">Jam Selesai</label>
              <input v-model="form.end_time" type="time" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400" required />
            </div>
          </div>

          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
            <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer">
              Batal
            </button>
            <button type="submit" :disabled="submitting" class="px-6 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-50">
              <svg v-if="submitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
              <span>{{ submitting ? 'Memvalidasi...' : 'Simpan Jadwal' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Print Modal Preview -->
    <div v-if="showPrintModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-[2rem] max-w-5xl w-full p-8 shadow-2xl space-y-6 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-black text-slate-800 font-lexend uppercase">Cetak Jadwal Pelajaran</h3>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Pratinjau jadwal pelajaran untuk dicetak atau disimpan ke PDF.</p>
          </div>
          <button @click="showPrintModal = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400 cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div id="schedule-print-area" class="flex-1 overflow-y-auto p-6 bg-white border border-slate-200 rounded-2xl text-slate-800 space-y-6">
          <!-- Kop Surat -->
          <div class="flex items-center gap-5 pb-4 border-b-2 border-slate-800">
            <img v-if="settings.app_logo" :src="settings.app_logo" class="w-16 h-16 object-contain flex-shrink-0" alt="Logo" />
            <div v-else class="w-16 h-16 bg-slate-900 text-white font-black text-xl rounded-xl flex items-center justify-center flex-shrink-0">
              SCH
            </div>
            <div class="flex-1 text-center">
              <h2 class="text-lg font-black font-lexend uppercase tracking-wider">{{ settings.app_name || 'SISTEM AKADEMIK SEKOLAH / MADRASAH' }}</h2>
              <p class="text-xs text-slate-600 font-medium">{{ settings.app_tagline || 'Jadwal Pelajaran & Kegiatan Akademik' }}</p>
              <p class="text-[10px] text-slate-500 font-mono mt-0.5">{{ settings.school_address || 'Jl. Pendidikan No. 123, Indonesia' }}</p>
            </div>
          </div>

          <div class="text-center space-y-1">
            <h3 class="text-sm font-black uppercase font-lexend tracking-wide">JADWAL PELAJARAN - HARI {{ getActiveDayName().toUpperCase() }}</h3>
            <p class="text-xs font-bold text-slate-600">Tahun Ajaran: {{ settings.active_academic_year?.name || 'Tahun Aktif' }}</p>
          </div>

          <!-- Print Table -->
          <table class="w-full text-xs text-center border-collapse border border-slate-400">
            <thead>
              <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-400">
                <th class="border border-slate-400 p-2 w-12">NO</th>
                <th class="border border-slate-400 p-2 w-32">WAKTU</th>
                <th v-for="cls in filteredClasses" :key="'print-'+cls.id" class="border border-slate-400 p-2">
                  {{ cls.name }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="slot in activeYaspinSlots" :key="'print-slot-'+slot.no" class="border-b border-slate-300">
                <td class="border border-slate-400 p-2 font-bold">{{ slot.no }}</td>
                <td class="border border-slate-400 p-2 font-mono font-bold">{{ slot.start }} - {{ slot.end }}</td>
                <td v-if="slot.isGeneral || slot.isBreak" :colspan="filteredClasses.length || 1" class="border border-slate-400 p-2 font-bold uppercase bg-slate-50">
                  {{ slot.title }}
                </td>
                <template v-else>
                  <td v-for="cls in filteredClasses" :key="'print-td-'+cls.id" class="border border-slate-400 p-2">
                    <template v-if="getYaspinScheduleItem(activeYaspinDay, cls.id, slot)">
                      <p class="font-bold">{{ getYaspinScheduleItem(activeYaspinDay, cls.id, slot).subject?.name || getYaspinScheduleItem(activeYaspinDay, cls.id, slot).activity_name }}</p>
                      <p class="text-[10px] text-slate-500">{{ getYaspinScheduleItem(activeYaspinDay, cls.id, slot).teacher?.full_name || '-' }}</p>
                    </template>
                    <span v-else class="text-slate-300">-</span>
                  </td>
                </template>
              </tr>
            </tbody>
          </table>

          <!-- Signature Block -->
          <div class="pt-8 grid grid-cols-2 text-center text-xs font-semibold">
            <div>
              <p>Mengetahui,</p>
              <p class="font-bold">Kepala Sekolah / Madrasah</p>
              <div class="h-16"></div>
              <p class="font-bold underline">{{ settings.principal_name || '............................................' }}</p>
              <p class="text-[10px] text-slate-500 font-mono">NIP: {{ settings.principal_nip || '-' }}</p>
            </div>
            <div>
              <p>{{ getTodayDateFormatted() }}</p>
              <p class="font-bold">Waka Kurikulum / Akademik</p>
              <div class="h-16"></div>
              <p class="font-bold underline">( ............................................ )</p>
              <p class="text-[10px] text-slate-500 font-mono">NIP: -</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button @click="showPrintModal = false" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
            Tutup
          </button>
          <button @click="triggerPrintSchedule" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-emerald-600/20 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Sekarang</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();

const schedules = ref([]);
const classes = ref([]);
const subjects = ref([]);
const teachers = ref([]);
const settings = ref({});

const loading = ref(true);
const submitting = ref(false);
const showModal = ref(false);
const showPrintModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const conflictError = ref('');

const selectedClass = ref('');
const selectedTeacher = ref('');
const filterType = ref('all');
const activeYaspinDay = ref('senin');

const daysList = [
  { key: 'senin', name: 'Senin' },
  { key: 'selasa', name: 'Selasa' },
  { key: 'rabu', name: 'Rabu' },
  { key: 'kamis', name: 'Kamis' },
  { key: 'jumat', name: 'Jumat' },
  { key: 'sabtu', name: 'Sabtu' },
];

const yaspinSeninSlots = [
  { no: '0', start: '07.00', end: '07.30', isGeneral: true, title: 'UPACARA BENDERA' },
  { no: '1', start: '07.30', end: '07.50', isGeneral: true, title: 'TADARUSAN AL-QUR\'AN' },
  { no: '2', start: '07.50', end: '08.30', isSlot: true },
  { no: '3', start: '08.30', end: '09.10', isSlot: true },
  { no: '4', start: '09.10', end: '09.50', isSlot: true },
  { no: '5', start: '09.50', end: '10.30', isSlot: true },
  { no: '6', start: '10.30', end: '11.00', isBreak: true, title: 'ISTIRAHAT' },
  { no: '7', start: '11.00', end: '11.40', isSlot: true },
  { no: '8', start: '11.40', end: '12.20', isSlot: true },
  { no: '9', start: '12.20', end: '12.40', isGeneral: true, title: 'SHALAT DZUHUR BERJAMA\'AH' },
];

const yaspinSelasaSabtuSlots = [
  { no: '0', start: '07.00', end: '07.30', isGeneral: true, title: 'TADARUSAN AL-QUR\'AN' },
  { no: '1', start: '07.30', end: '08.10', isSlot: true },
  { no: '2', start: '08.10', end: '08.50', isSlot: true },
  { no: '3', start: '08.50', end: '09.30', isSlot: true },
  { no: '4', start: '09.30', end: '10.10', isSlot: true },
  { no: '5', start: '10.10', end: '10.40', isBreak: true, title: 'ISTIRAHAT' },
  { no: '6', start: '10.40', end: '11.20', isSlot: true },
  { no: '7', start: '11.20', end: '12.00', isSlot: true },
  { no: '8', start: '12.00', end: '12.20', isGeneral: true, title: 'SHALAT DZUHUR BERJAMA\'AH' },
];

const activeYaspinSlots = computed(() => {
  return activeYaspinDay.value === 'senin' ? yaspinSeninSlots : yaspinSelasaSabtuSlots;
});

const filteredClasses = computed(() => {
  if (!selectedClass.value) return classes.value;
  return classes.value.filter(c => c.id == selectedClass.value);
});

const getActiveDayName = () => {
  const d = daysList.find(day => day.key === activeYaspinDay.value);
  return d ? d.name : 'Senin';
};

const getYaspinScheduleItem = (dayKey, classId, slot) => {
  const slotStart = slot.start.replace('.', ':');
  const slotEnd = slot.end.replace('.', ':');
  return schedules.value.find(s => {
    if (s.day?.toLowerCase() !== dayKey) return false;
    if (s.class_id && s.class_id != classId) return false;
    return (s.start_time < slotEnd && s.end_time > slotStart);
  });
};

const openYaspinSlot = (dayKey, classId, slot) => {
  openModal(false, dayKey);
  form.class_id = classId;
  form.start_time = slot.start.replace('.', ':');
  form.end_time = slot.end.replace('.', ':');
};

const form = reactive({
  is_activity: false,
  activity_name: '',
  activity_type: 'upacara',
  class_id: null,
  subject_id: '',
  teacher_id: '',
  day: 'senin',
  start_time: '07:30',
  end_time: '09:00',
});

const fetchSchedules = async () => {
  loading.value = true;
  try {
    const params = {};
    if (selectedClass.value) params.class_id = selectedClass.value;
    if (selectedTeacher.value) params.teacher_id = selectedTeacher.value;

    const res = await api.get('admin/schedules', params);
    let items = res?.data || [];

    if (filterType.value === 'subject') {
      items = items.filter(i => !i.is_activity);
    } else if (filterType.value === 'activity') {
      items = items.filter(i => i.is_activity);
    }

    schedules.value = items;
  } catch (error) {
    console.error('Failed to load schedules:', error);
    toast.error('Gagal memuat jadwal pelajaran');
  } finally {
    loading.value = false;
  }
};

const fetchDropdownData = async () => {
  try {
    const [cRes, sRes, tRes, setRes] = await Promise.all([
      api.get('admin/classes').catch(() => null),
      api.get('admin/subjects').catch(() => null),
      api.get('admin/teachers').catch(() => null),
      api.get('settings').catch(() => null),
    ]);

    classes.value = cRes?.data?.data || cRes?.data || [];
    subjects.value = sRes?.data?.data || sRes?.data || [];
    teachers.value = tRes?.data?.data || tRes?.data || [];
    settings.value = setRes?.data || {};
  } catch (err) {
    console.error('Failed to load dropdown options:', err);
  }
};

onMounted(() => {
  fetchSchedules();
  fetchDropdownData();
});

const openModal = (isActivityMode = false, targetDayKey = null) => {
  isEditing.value = false;
  editingId.value = null;
  conflictError.value = '';
  
  form.is_activity = isActivityMode;
  form.activity_name = isActivityMode ? 'Upacara Bendera' : '';
  form.activity_type = 'upacara';
  form.class_id = selectedClass.value || null;
  form.subject_id = '';
  form.teacher_id = '';
  form.day = targetDayKey || activeYaspinDay.value || 'senin';
  form.start_time = '07:30';
  form.end_time = '09:00';

  showModal.value = true;
};

const editSchedule = (item) => {
  isEditing.value = true;
  editingId.value = item.id;
  conflictError.value = '';

  form.is_activity = !!item.is_activity;
  form.activity_name = item.activity_name || '';
  form.activity_type = item.activity_type || 'upacara';
  form.class_id = item.class_id;
  form.subject_id = item.subject_id || '';
  form.teacher_id = item.teacher_id || '';
  form.day = item.day;
  form.start_time = item.start_time;
  form.end_time = item.end_time;

  showModal.value = true;
};

const submitForm = async () => {
  submitting.value = true;
  conflictError.value = '';

  try {
    const payload = {
      is_activity: form.is_activity,
      day: form.day,
      start_time: form.start_time,
      end_time: form.end_time,
    };

    if (form.is_activity) {
      payload.activity_name = form.activity_name;
      payload.activity_type = form.activity_type;
      payload.class_id = form.class_id || null;
    } else {
      payload.class_id = form.class_id;
      payload.subject_id = form.subject_id;
      payload.teacher_id = form.teacher_id;
    }

    if (isEditing.value) {
      await api.put(`admin/schedules/${editingId.value}`, payload);
      toast.success('Jadwal berhasil diperbarui');
    } else {
      await api.post('admin/schedules', payload);
      toast.success('Jadwal berhasil ditambahkan');
    }

    showModal.value = false;
    fetchSchedules();
  } catch (error) {
    if (error.response && error.response.status === 422) {
      conflictError.value = error.response.data?.message || 'Validasi bentrok jadwal gagal';
    } else {
      toast.error('Gagal menyimpan jadwal');
    }
  } finally {
    submitting.value = false;
  }
};

const deleteSchedule = async (id) => {
  const isConfirmed = await confirm({
    title: 'Hapus Jadwal',
    message: 'Apakah Anda yakin ingin menghapus slot jadwal ini?',
    type: 'danger',
    confirmText: 'Ya, Hapus',
  });

  if (!isConfirmed) return;
  try {
    await api.del(`admin/schedules/${id}`);
    toast.success('Jadwal berhasil dihapus');
    fetchSchedules();
  } catch (err) {
    toast.error('Gagal menghapus jadwal');
  }
};

const openPrintModal = () => {
  showPrintModal.value = true;
};

const getTodayDateFormatted = () => {
  const d = new Date();
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

// ================= EXPORT EXCEL SCHEDULES =================
const exportExcelSchedules = () => {
  if (!schedules.value.length) {
    toast.error('Tidak ada jadwal untuk diekspor');
    return;
  }

  const schoolName = settings.value?.app_name || 'SEKOLAH / MADRASAH';

  const rows = [
    [schoolName.toUpperCase()],
    ['DATA MASTER JADWAL PELAJARAN & KEGIATAN'],
    [`Dicetak Pada: ${new Date().toLocaleString('id-ID')}`],
    [],
    ['NO', 'HARI', 'WAKTU MULAI', 'WAKTU SELESAI', 'KELAS', 'MATA PELAJARAN / KEGIATAN', 'GURU PENGAJAR', 'TIPE']
  ];

  schedules.value.forEach((s, idx) => {
    rows.push([
      idx + 1,
      s.day ? s.day.toUpperCase() : '-',
      s.start_time || '-',
      s.end_time || '-',
      s.class_room?.name || 'Semua Kelas',
      s.is_activity ? (s.activity_name || 'Kegiatan') : (s.subject?.name || 'Mata Pelajaran'),
      s.teacher?.full_name || '-',
      s.is_activity ? 'Kegiatan Sekolah' : 'Pelajaran'
    ]);
  });

  const ws = XLSX.utils.aoa_to_sheet(rows);
  ws['!cols'] = [
    { wch: 6 },
    { wch: 12 },
    { wch: 14 },
    { wch: 14 },
    { wch: 18 },
    { wch: 32 },
    { wch: 28 },
    { wch: 20 },
  ];

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Jadwal Pelajaran');
  XLSX.writeFile(wb, `Master_Jadwal_Pelajaran_${new Date().toISOString().substring(0, 10)}.xlsx`);
  toast.success('Jadwal Pelajaran Excel berhasil diunduh!');
};

// ================= SAFE PRINT DIALOG =================
const triggerPrintSchedule = () => {
  const printAreaEl = document.getElementById('schedule-print-area');
  if (!printAreaEl) return;

  const content = printAreaEl.innerHTML;
  const printWindow = window.open('', '_blank', 'width=950,height=750');
  if (!printWindow) {
    toast.error('Popup terblokir oleh browser. Izinkan popup untuk mencetak.');
    return;
  }

  printWindow.document.open();
  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="id">
      <head>
        <meta charset="utf-8">
        <title>Jadwal Pelajaran</title>
        <style>
          @page {
            size: A4 landscape;
            margin: 12mm;
          }
          * { box-sizing: border-box; }
          body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 10px;
          }
          h2, h3, p { margin: 0 0 4px 0; }
          table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10px;
          }
          th, td {
            border: 1px solid #64748b;
            padding: 6px 8px;
            text-align: center;
          }
          th {
            background-color: #f1f5f9;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
          }
          .font-bold { font-weight: bold; }
          .font-mono { font-family: monospace; }
          .underline { text-decoration: underline; }
          .border-b-2 { border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
          .flex { display: flex; align-items: center; }
          .grid { display: grid; grid-template-columns: 1fr 1fr; }
          .h-16 { height: 50px; }
          .pt-8 { padding-top: 20px; }
          img { max-height: 50px; max-width: 50px; }
        </style>
      </head>
      <body>
        ${content}
      </body>
    </html>
  `);
  printWindow.document.close();

  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 400);
};
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
