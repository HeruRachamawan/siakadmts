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
          @click="openSlotConfigModal"
          class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm flex items-center gap-2 cursor-pointer active:scale-95"
          title="Atur Jam Pelajaran, Durasi, dan Slot Waktu Matriks (Senin, Selasa-Sabtu, Jumat)"
        >
          <Clock class="w-4 h-4" />
          <span>Atur Jam Pelajaran</span>
        </button>

        <button
          @click="syncOfficialActivities"
          :disabled="syncingActivities"
          class="px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-800 font-bold rounded-xl text-xs hover:bg-amber-100 transition-colors flex items-center gap-2 shadow-sm cursor-pointer disabled:opacity-50"
          title="Sinkronkan Otomatis Jam Upacara, Istirahat & Sholat Dzuhur untuk Semua Kelas"
        >
          <span v-if="!syncingActivities">✨ Sinkronkan Kegiatan Resmi</span>
          <span v-else class="flex items-center gap-1.5">
            <svg class="animate-spin h-3.5 w-3.5 text-amber-700" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
            Menyinkronkan...
          </span>
        </button>

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

    <!-- Tab Switcher: Jadwal Utama vs Jadwal Lokal -->
    <div class="bg-white rounded-2xl p-3 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
      <div class="flex items-center gap-2 p-1 bg-slate-100/90 rounded-xl">
        <button
          type="button"
          @click="setScheduleType('utama')"
          :class="activeScheduleType === 'utama' ? 'bg-white text-emerald-800 font-extrabold shadow-sm' : 'text-slate-500 hover:text-slate-800 font-bold'"
          class="px-5 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
        >
          <span class="text-sm">🏫</span>
          <span>Jadwal Utama</span>
          <span class="text-[10px] px-2 py-0.5 rounded-md font-bold" :class="activeScheduleType === 'utama' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
            {{ mainClassesCount }} Kelas
          </span>
        </button>

        <button
          type="button"
          @click="setScheduleType('lokal')"
          :class="activeScheduleType === 'lokal' ? 'bg-white text-teal-800 font-extrabold shadow-sm' : 'text-slate-500 hover:text-slate-800 font-bold'"
          class="px-5 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
        >
          <span class="text-sm">📍</span>
          <span>Jadwal Lokal</span>
          <span class="text-[10px] px-2 py-0.5 rounded-md font-bold" :class="activeScheduleType === 'lokal' ? 'bg-teal-100 text-teal-800' : 'bg-slate-200 text-slate-600'">
            {{ lokalClassesCount }} Kelas (7, 8, 9A, 9B)
          </span>
        </button>
      </div>

      <div class="flex items-center gap-3 text-xs text-slate-500 font-medium px-1">
        <span v-if="activeScheduleType === 'utama'" class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span>Jadwal KBM <b>Utama</b> dengan jam pelajaran standar madrasah.</span>
        </span>
        <div v-else class="flex items-center gap-2">
          <span class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
            <span>Jadwal KBM <b>Lokal</b> khusus <b>Kelas 7, 8, 9A, dan 9B</b> dengan slot jam mandiri.</span>
          </span>
          <button
            type="button"
            @click="openLokalClassPicker"
            class="px-2 py-1 bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold rounded-lg text-[10px] border border-teal-200 transition-colors cursor-pointer"
            title="Ubah pilihan 4 kelas untuk Jadwal Lokal jika diperlukan"
          >
            ⚙️ Sesuaikan 4 Kelas
          </button>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[180px]">
        <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Filter Kelas</label>
        <select v-model="selectedClass" @change="fetchSchedules" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer">
          <option value="">-- Semua Kelas ({{ activeScheduleType === 'lokal' ? 'Jadwal Lokal' : 'Jadwal Utama' }}) --</option>
          <option v-for="cls in scheduleActiveClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
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
            <h3 class="font-bold text-sm sm:text-base text-white">Matriks {{ activeScheduleType === 'lokal' ? 'Jadwal Lokal (4 Kelas)' : 'Jadwal Utama' }}: Hari {{ getActiveDayName() }}</h3>
            <p class="text-xs text-slate-400 font-normal">{{ activeScheduleType === 'lokal' ? 'Tampilan jadwal KBM khusus Kelas 7, 8, 9A, dan 9B' : 'Tampilan jadwal pelajaran visual per kelas reguler' }}</p>
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
                    <div
                      class="text-[11px] font-medium truncate max-w-[140px] mt-1 flex items-center gap-1 justify-center"
                      :class="getYaspinScheduleItem(activeYaspinDay, cls.id, slot).teacher ? 'text-emerald-700' : 'text-slate-400 italic'"
                    >
                      <svg class="w-3 h-3 flex-shrink-0" :class="getYaspinScheduleItem(activeYaspinDay, cls.id, slot).teacher ? 'text-emerald-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                      <span>{{ getYaspinScheduleItem(activeYaspinDay, cls.id, slot).teacher?.full_name || 'Belum Ditentukan' }}</span>
                    </div>

                    <!-- JP Duration Badge if spans multiple slots -->
                    <div v-if="getScheduleJpBadge(activeYaspinDay, getYaspinScheduleItem(activeYaspinDay, cls.id, slot))" class="mt-1">
                      <span class="text-[9px] px-1.5 py-0.2 bg-emerald-50 border border-emerald-200/80 text-emerald-800 rounded font-bold uppercase tracking-tight">
                        {{ getScheduleJpBadge(activeYaspinDay, getYaspinScheduleItem(activeYaspinDay, cls.id, slot)) }}
                      </span>
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
            <!-- Quick Activity Presets -->
            <div class="space-y-1">
              <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Template Cepat Kegiatan Sekolah:</label>
              <div class="flex flex-wrap gap-1.5">
                <button
                  type="button"
                  @click="applyActivityPreset('Upacara Bendera', 'upacara', '07:00', '07:45')"
                  class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                >
                  🇮🇩 Upacara
                </button>
                <button
                  type="button"
                  @click="applyActivityPreset('Istirahat', 'istirahat', '09:40', '10:10')"
                  class="px-2.5 py-1 bg-amber-100 hover:bg-amber-200 text-amber-900 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                >
                  ☕ Istirahat
                </button>
                <button
                  type="button"
                  @click="applyActivityPreset('Sholat Dzuhur Berjamaah', 'religi', '11:50', '12:30')"
                  class="px-2.5 py-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-900 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                >
                  🕌 Sholat Dzuhur
                </button>
                <button
                  type="button"
                  @click="applyActivityPreset('Sholat Dhuha & Tadarus', 'religi', '07:00', '07:30')"
                  class="px-2.5 py-1 bg-teal-100 hover:bg-teal-200 text-teal-900 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                >
                  📿 Dhuha & Tadarus
                </button>
                <button
                  type="button"
                  @click="applyActivityPreset('Senam Pagi & Kebersihan', 'kokurikuler', '07:00', '07:45')"
                  class="px-2.5 py-1 bg-blue-100 hover:bg-blue-200 text-blue-900 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                >
                  🏃 Senam Pagi
                </button>
              </div>
            </div>

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
                  <option :value="null">-- Semua Kelas ({{ activeScheduleType === 'lokal' ? 'Jadwal Lokal' : 'Seluruh Sekolah' }}) --</option>
                  <option v-for="cls in scheduleActiveClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
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
                  <option v-for="cls in scheduleActiveClasses" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
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
              <div class="flex items-center justify-between">
                <label class="block text-[11px] font-bold text-slate-600 uppercase">Guru Pengajar</label>
                <span class="text-[10px] text-slate-400 font-medium">(Opsional)</span>
              </div>
              <select v-model="form.teacher_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 cursor-pointer">
                <option value="">-- Tanpa Guru / Belum Ditentukan --</option>
                <option v-for="tcher in teachers" :key="tcher.id" :value="tcher.id">{{ tcher.full_name }}</option>
              </select>
              <p class="text-[10px] text-slate-400">Pilih guru pengajar, atau biarkan kosong jika pengajar masih tentatif / belum ditentukan.</p>
            </div>
          </template>

          <!-- Multi-JP Quick Selector (Pilihan Durasi Jam Pelajaran) -->
          <div v-if="!form.is_activity" class="bg-emerald-50/70 border border-emerald-200/90 rounded-2xl p-4 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-1.5">
                <Clock class="w-4 h-4 text-emerald-700" />
                <span class="text-xs font-bold text-emerald-900">Durasi Jam Pelajaran (JP)</span>
              </div>
              <span v-if="currentJpSummary" class="text-[11px] font-bold text-emerald-700 bg-white px-2.5 py-0.5 rounded-lg border border-emerald-200 shadow-2xs">
                {{ currentJpSummary }}
              </span>
            </div>

            <!-- Pill Buttons: 1 JP, 2 JP, 3 JP, 4 JP -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pilih Cepat Durasi:</label>
                <span class="text-[10px] text-slate-400">Otomatis hitung jam selesai</span>
              </div>
              <div class="flex items-center gap-2 flex-wrap">
                <button
                  v-for="jp in availableJpOptions"
                  :key="jp.count"
                  type="button"
                  @click="applyJpDuration(jp.count)"
                  :class="[
                    selectedJpCount === jp.count 
                      ? 'bg-emerald-600 text-white shadow-sm font-bold ring-2 ring-emerald-600/30' 
                      : 'bg-white text-slate-700 border border-slate-200 hover:border-emerald-400 hover:text-emerald-700 font-semibold'
                  ]"
                  class="px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-1 active:scale-95"
                >
                  <span>{{ jp.label }}</span>
                  <span class="text-[10px] opacity-80 font-normal">({{ jp.endTime }})</span>
                </button>
              </div>
            </div>

            <!-- Rentang Dropdown Jam Ke- -->
            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-emerald-200/60">
              <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase">Mulai Dari Jam Ke-</label>
                <select 
                  :value="currentStartSlotKey" 
                  @change="onStartSlotChange($event.target.value)"
                  class="w-full bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
                >
                  <option v-for="s in currentDayKbmSlots" :key="'start-'+s.no" :value="s.no">
                    Jam {{ s.no }} ({{ s.start }} - {{ s.end }})
                  </option>
                </select>
              </div>
              <div class="space-y-1">
                <label class="block text-[10px] font-bold text-slate-500 uppercase">Sampai Jam Ke-</label>
                <select 
                  :value="currentEndSlotKey" 
                  @change="onEndSlotChange($event.target.value)"
                  class="w-full bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
                >
                  <option v-for="s in availableEndSlots" :key="'end-'+s.no" :value="s.no">
                    Jam {{ s.no }} (Selesai {{ s.end }})
                  </option>
                </select>
              </div>
            </div>
          </div>

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
          <p v-if="!form.is_activity" class="text-[10px] text-slate-400 -mt-2">
            💡 Jam Mulai & Selesai otomatis disesuaikan dari alokasi JP di atas, atau bisa diketik manual jika ada jam khusus.
          </p>

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
            </div>
            <div>
              <p>{{ getTodayDateFormatted() }}</p>
              <p class="font-bold">Waka Kurikulum / Akademik</p>
              <div class="h-16"></div>
              <p class="font-bold underline">( ............................................ )</p>
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

    <!-- MODAL: PENGATURAN JAM PELAJARAN & SLOT WAKTU (KURIKULUM) -->
    <div v-if="showSlotConfigModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white w-full max-w-4xl rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="px-5 sm:px-6 py-4 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white flex items-center justify-between border-b border-slate-800 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-400 flex-shrink-0">
              <Clock class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-bold text-base sm:text-lg text-white">Pengaturan Jam Pelajaran & Slot Waktu</h3>
              <p class="text-xs text-slate-300 font-normal">Atur jam mulai, selesai, istirahat, dan kegiatan harian pada matriks jadwal madrasah.</p>
            </div>
          </div>
          <button @click="showSlotConfigModal = false" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Schedule Group Switcher inside modal (Utama vs Lokal) -->
        <div class="px-5 sm:px-6 pt-3 pb-2.5 bg-slate-100/90 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2 flex-shrink-0">
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kategori Jadwal:</span>
            <div class="flex p-1 bg-white rounded-xl border border-slate-200 shadow-2xs">
              <button
                type="button"
                @click="switchConfigGroup('utama')"
                :class="configActiveGroupTab === 'utama' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                class="px-3.5 py-1 text-xs rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
              >
                <span>🏫 Jadwal Utama</span>
              </button>
              <button
                type="button"
                @click="switchConfigGroup('lokal')"
                :class="configActiveGroupTab === 'lokal' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
                class="px-3.5 py-1 text-xs rounded-lg transition-all cursor-pointer flex items-center gap-1.5"
              >
                <span>📍 Jadwal Lokal (4 Kelas)</span>
              </button>
            </div>
          </div>

          <span class="text-[11px] text-slate-500 font-medium">
            Mengatur slot waktu untuk: <strong :class="configActiveGroupTab === 'lokal' ? 'text-teal-700' : 'text-emerald-700'">{{ configActiveGroupTab === 'lokal' ? 'Jadwal Lokal (Kelas 7, 8, 9A, 9B)' : 'Jadwal Utama' }}</strong>
          </span>
        </div>

        <!-- Day Tabs Switcher inside modal -->
        <div class="px-5 sm:px-6 pt-4 pb-2 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 flex-shrink-0">
          <div class="flex p-1 bg-slate-200/80 rounded-xl gap-1 overflow-x-auto max-w-full">
            <button
              type="button"
              @click="configActiveDayTab = 'senin'"
              :class="configActiveDayTab === 'senin' ? 'bg-white text-emerald-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
              class="px-3.5 py-1.5 text-xs rounded-lg transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5"
            >
              <span>🇮🇩 Hari Senin</span>
              <span class="text-[10px] px-1.5 py-0.2 rounded-md font-bold" :class="configActiveDayTab === 'senin' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300/60 text-slate-600'">
                {{ editingSlots.senin?.length || 0 }}
              </span>
            </button>
            <button
              type="button"
              @click="configActiveDayTab = 'selasa_sabtu'"
              :class="configActiveDayTab === 'selasa_sabtu' ? 'bg-white text-emerald-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
              class="px-3.5 py-1.5 text-xs rounded-lg transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5"
            >
              <span>📅 Selasa – Kamis & Sabtu</span>
              <span class="text-[10px] px-1.5 py-0.2 rounded-md font-bold" :class="configActiveDayTab === 'selasa_sabtu' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300/60 text-slate-600'">
                {{ editingSlots.selasa_sabtu?.length || 0 }}
              </span>
            </button>
            <button
              type="button"
              @click="configActiveDayTab = 'jumat'"
              :class="configActiveDayTab === 'jumat' ? 'bg-white text-emerald-800 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900 font-medium'"
              class="px-3.5 py-1.5 text-xs rounded-lg transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5"
            >
              <span>🕌 Hari Jumat</span>
              <span class="text-[10px] px-1.5 py-0.2 rounded-md font-bold" :class="configActiveDayTab === 'jumat' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-300/60 text-slate-600'">
                {{ editingSlots.jumat?.length || 0 }}
              </span>
            </button>
          </div>

          <button
            type="button"
            @click="addNewSlot"
            class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer whitespace-nowrap active:scale-95"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>Tambah Jam / Baris Slot</span>
          </button>
        </div>

        <!-- Table of Slots (Scrollable Body) -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-3">
          <div class="bg-amber-50 border border-amber-200 text-amber-900 text-xs p-3 rounded-xl flex items-start gap-2.5">
            <AlertCircle class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
            <p>
              Tipe <b>KBM</b> akan menjadi baris kotak jadwal per kelas. Tipe <b>Kegiatan Bersama</b> (seperti Upacara/Tadarus/Sholat) dan <b>Istirahat</b> akan membentang otomatis ke seluruh kelas.
            </p>
          </div>

          <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs border-collapse min-w-[650px]">
              <thead>
                <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                  <th class="py-2.5 px-3 w-20 text-center">Jam Ke-</th>
                  <th class="py-2.5 px-3 w-32">Jam Mulai</th>
                  <th class="py-2.5 px-3 w-32">Jam Selesai</th>
                  <th class="py-2.5 px-3 w-48">Tipe Slot</th>
                  <th class="py-2.5 px-3">Keterangan / Nama Kegiatan</th>
                  <th class="py-2.5 px-3 w-16 text-center">Hapus</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="!currentDayEditingSlots || currentDayEditingSlots.length === 0">
                  <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                    Belum ada slot waktu untuk hari ini. Klik tombol "Tambah Jam / Baris Slot" di atas.
                  </td>
                </tr>
                <tr v-for="(slot, idx) in currentDayEditingSlots" :key="idx" class="hover:bg-slate-50/70 transition-colors">
                  <!-- No -->
                  <td class="py-2 px-3 text-center">
                    <input
                      v-model="slot.no"
                      type="text"
                      class="w-14 text-center px-2 py-1 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                      placeholder="0"
                    />
                  </td>

                  <!-- Jam Mulai -->
                  <td class="py-2 px-3">
                    <input
                      v-model="slot.start"
                      type="text"
                      class="w-full px-2.5 py-1 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                      placeholder="07.00"
                    />
                  </td>

                  <!-- Jam Selesai -->
                  <td class="py-2 px-3">
                    <input
                      v-model="slot.end"
                      type="text"
                      class="w-full px-2.5 py-1 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                      placeholder="07.30"
                    />
                  </td>

                  <!-- Tipe Slot -->
                  <td class="py-2 px-3">
                    <select
                      :value="getSlotType(slot)"
                      @change="onSlotTypeChange(slot, $event.target.value)"
                      class="w-full px-2.5 py-1 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                      <option value="slot">KBM (Pelajaran Kelas)</option>
                      <option value="break">Istirahat</option>
                      <option value="general">Kegiatan Bersama</option>
                    </select>
                  </td>

                  <!-- Keterangan / Nama Kegiatan -->
                  <td class="py-2 px-3">
                    <input
                      v-model="slot.title"
                      type="text"
                      :disabled="!slot.isGeneral && !slot.isBreak"
                      :placeholder="slot.isSlot ? '(Otomatis per mata pelajaran)' : 'Contoh: UPACARA BENDERA / ISTIRAHAT'"
                      class="w-full px-2.5 py-1 bg-white border border-slate-300 rounded-lg text-xs text-slate-800 disabled:bg-slate-100 disabled:text-slate-400 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                  </td>

                  <!-- Aksi Hapus -->
                  <td class="py-2 px-3 text-center">
                    <button
                      type="button"
                      @click="removeSlot(idx)"
                      class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                      title="Hapus baris ini"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-5 sm:px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 flex-shrink-0">
          <button
            type="button"
            @click="resetSlotConfigToDefault"
            class="px-4 py-2 text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <RotateCcw class="w-3.5 h-3.5" />
            <span>Reset ke Standar Madrasah</span>
          </button>

          <div class="flex items-center gap-2 justify-end">
            <button
              type="button"
              @click="showSlotConfigModal = false"
              class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="saveSlotConfig"
              :disabled="savingSlots"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <Save class="w-3.5 h-3.5" />
              <span>{{ savingSlots ? 'Menyimpan...' : 'Simpan Pengaturan Waktu' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: SESUAIKAN 4 KELAS JADWAL LOKAL -->
    <div v-if="showLokalClassPicker" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh] animate-in fade-in zoom-in-95 duration-150">
        <div class="px-5 py-4 bg-gradient-to-r from-teal-900 via-teal-800 to-slate-900 text-white flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <span class="text-xl">📍</span>
            <div>
              <h3 class="font-bold text-sm sm:text-base text-white">Sesuaikan Kelas Jadwal Lokal</h3>
              <p class="text-[11px] text-teal-200">Pilih kelas yang masuk ke dalam kategori Jadwal Lokal.</p>
            </div>
          </div>
          <button @click="showLokalClassPicker = false" class="p-1.5 text-teal-300 hover:text-white rounded-lg hover:bg-white/10 transition-colors cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="p-5 overflow-y-auto space-y-3 flex-1">
          <p class="text-xs text-slate-600">
            Centang kelas yang menggunakan <b>Jadwal Lokal</b> (default otomatis: <b>Kelas 7, 8, 9A, dan 9B</b>).
          </p>

          <div class="grid grid-cols-1 gap-2 pt-1">
            <label
              v-for="cls in classes"
              :key="'lokal-pick-' + cls.id"
              class="flex items-center justify-between p-3 rounded-xl border transition-all cursor-pointer select-none"
              :class="customLokalClassIds.includes(cls.id) ? 'bg-teal-50/80 border-teal-300 text-teal-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'"
            >
              <div class="flex items-center gap-3">
                <input
                  type="checkbox"
                  :checked="customLokalClassIds.includes(cls.id)"
                  @change="toggleLokalClassId(cls.id)"
                  class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 cursor-pointer"
                />
                <span class="text-xs font-bold">{{ cls.name }}</span>
                <span v-if="cls.name === '7' || cls.name === '8'" class="text-[10px] px-2 py-0.5 rounded-md bg-teal-100 text-teal-800 font-semibold">
                  Kelas Jadwal Lokal
                </span>
                <span v-else-if="cls.name === '9A' || cls.name === '9B'" class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-semibold">
                  Kelas Utama & Lokal
                </span>
                <span v-else class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 text-slate-600 font-normal">
                  Kelas Jadwal Utama
                </span>
              </div>
              <span v-if="customLokalClassIds.includes(cls.id)" class="text-[10px] px-2.5 py-0.5 rounded-md bg-teal-200/80 text-teal-900 font-bold">
                ✓ Aktif di Jadwal Lokal
              </span>
            </label>
          </div>
        </div>

        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-2">
          <button
            type="button"
            @click="resetLokalClassIds"
            class="px-3.5 py-1.5 text-xs text-slate-600 hover:text-rose-600 font-semibold cursor-pointer"
          >
            Reset ke Otomatis
          </button>
          <button
            type="button"
            @click="showLokalClassPicker = false"
            class="px-5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl shadow-sm cursor-pointer"
          >
            Selesai
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
import { Clock, Plus, Trash2, RotateCcw, Save, X, AlertCircle } from 'lucide-vue-next';

const toast = useToast();
const { confirm } = useConfirm();

const schedules = ref([]);
const classes = ref([]);
const subjects = ref([]);
const teachers = ref([]);
const settings = ref({});

const loading = ref(true);
const submitting = ref(false);
const syncingActivities = ref(false);
const showModal = ref(false);
const showPrintModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const conflictError = ref('');

const syncOfficialActivities = async () => {
  const isConfirmed = await confirm({
    title: 'Sinkronkan Kegiatan Resmi Madrasah?',
    message: 'Sistem akan otomatis mengatur jadwal Upacara Bendera, Tadarus, Istirahat, dan Sholat Dzuhur/Jumat untuk SEMUA KELAS sesuai standar waktu kurikulum.',
    confirmText: 'Ya, Sinkronkan Sekarang',
    cancelText: 'Batal',
    type: 'primary'
  });

  if (!isConfirmed) return;

  syncingActivities.value = true;
  try {
    const res = await api.post('admin/schedules/generate-general-activities');
    toast.success(res?.message || 'Kegiatan resmi madrasah berhasil disinkronkan!');
    await fetchSchedules();
  } catch (err) {
    console.error('Error syncing activities:', err);
    toast.error('Gagal menyinkronkan kegiatan resmi.');
  } finally {
    syncingActivities.value = false;
  }
};

const selectedClass = ref('');
const selectedTeacher = ref('');
const filterType = ref('all');
const activeYaspinDay = ref('senin');

// ================= DUAL SCHEDULE SYSTEM (UTAMA & LOKAL) =================
const activeScheduleType = ref('utama'); // 'utama' | 'lokal'
const configActiveGroupTab = ref('utama'); // 'utama' | 'lokal'
const showLokalClassPicker = ref(false);

const customLokalClassIds = ref(
  JSON.parse(localStorage.getItem('siakad_lokal_class_ids') || '[]')
);

const isLokalClass = (cls) => {
  if (!cls) return false;
  if (customLokalClassIds.value && customLokalClassIds.value.length > 0) {
    return customLokalClassIds.value.includes(cls.id);
  }
  const name = (cls.name || '').trim();
  const lower = name.toLowerCase();

  // Standalone 7: "7" or "Kelas 7" (exact, without letter suffix A, B, C, D)
  const isClass7 = (name === '7' || lower === 'kelas 7');

  // Standalone 8: "8" or "Kelas 8" (exact, without letter suffix A, B, C, D)
  const isClass8 = (name === '8' || lower === 'kelas 8');

  // Class 9A
  const isClass9A = (name === '9A' || name === '9a' || lower === 'kelas 9a' || lower === 'ix-a' || lower === 'ix a');

  // Class 9B
  const isClass9B = (name === '9B' || name === '9b' || lower === 'kelas 9b' || lower === 'ix-b' || lower === 'ix b');

  return isClass7 || isClass8 || isClass9A || isClass9B;
};

const isUtamaClass = (cls) => {
  if (!cls) return false;
  const name = (cls.name || '').trim();
  const lower = name.toLowerCase();

  // Standalone 7 and 8 are specifically for Jadwal Lokal, keep regular classes in Jadwal Utama
  if (name === '7' || lower === 'kelas 7') return false;
  if (name === '8' || lower === 'kelas 8') return false;

  return true;
};

const scheduleActiveClasses = computed(() => {
  if (activeScheduleType.value === 'lokal') {
    const lokal = classes.value.filter(isLokalClass);
    return lokal.sort((a, b) => (a.name || '').localeCompare(b.name || '', undefined, { numeric: true }));
  }
  const utama = classes.value.filter(isUtamaClass);
  return utama.sort((a, b) => (a.name || '').localeCompare(b.name || '', undefined, { numeric: true }));
});

const mainClassesCount = computed(() => classes.value.filter(isUtamaClass).length);
const lokalClassesCount = computed(() => scheduleActiveClasses.value.length);

const setScheduleType = (type) => {
  activeScheduleType.value = type;
  selectedClass.value = '';
  fetchSchedules();
};

const openLokalClassPicker = () => {
  if (!customLokalClassIds.value || customLokalClassIds.value.length === 0) {
    customLokalClassIds.value = classes.value.filter(isLokalClass).map(c => c.id);
  }
  showLokalClassPicker.value = true;
};

const toggleLokalClassId = (classId) => {
  const idx = customLokalClassIds.value.indexOf(classId);
  if (idx > -1) {
    customLokalClassIds.value.splice(idx, 1);
  } else {
    customLokalClassIds.value.push(classId);
  }
  localStorage.setItem('siakad_lokal_class_ids', JSON.stringify(customLokalClassIds.value));
};

const resetLokalClassIds = () => {
  localStorage.removeItem('siakad_lokal_class_ids');
  const matched = classes.value.filter(c => {
    const name = (c.name || '').trim();
    const lower = name.toLowerCase();
    return name === '7' || lower === 'kelas 7' ||
           name === '8' || lower === 'kelas 8' ||
           name === '9A' || name === '9a' || lower === 'kelas 9a' ||
           name === '9B' || name === '9b' || lower === 'kelas 9b';
  }).map(c => c.id);
  customLokalClassIds.value = matched;
  localStorage.setItem('siakad_lokal_class_ids', JSON.stringify(matched));
  toast.success('Pilihan kelas Jadwal Lokal diset ke: Kelas 7, 8, 9A, dan 9B');
  showLokalClassPicker.value = false;
};

const daysList = [
  { key: 'senin', name: 'Senin' },
  { key: 'selasa', name: 'Selasa' },
  { key: 'rabu', name: 'Rabu' },
  { key: 'kamis', name: 'Kamis' },
  { key: 'jumat', name: 'Jumat' },
  { key: 'sabtu', name: 'Sabtu' },
];

// Default slots for Jadwal Utama
const defaultSeninSlots = [
  { no: '0', start: '07.00', end: '07.30', isGeneral: true, title: 'UPACARA BENDERA' },
  { no: '1', start: '07.30', end: '07.50', isGeneral: true, title: "TADARUSAN AL-QUR'AN" },
  { no: '2', start: '07.50', end: '08.30', isSlot: true, title: '' },
  { no: '3', start: '08.30', end: '09.10', isSlot: true, title: '' },
  { no: '4', start: '09.10', end: '09.50', isSlot: true, title: '' },
  { no: '5', start: '09.50', end: '10.30', isSlot: true, title: '' },
  { no: '6', start: '10.30', end: '11.00', isBreak: true, title: 'ISTIRAHAT' },
  { no: '7', start: '11.00', end: '11.40', isSlot: true, title: '' },
  { no: '8', start: '11.40', end: '12.20', isSlot: true, title: '' },
  { no: '9', start: '12.20', end: '12.40', isGeneral: true, title: "SHALAT DZUHUR BERJAMA'AH" },
];

const defaultSelasaSabtuSlots = [
  { no: '0', start: '07.00', end: '07.30', isGeneral: true, title: "TADARUSAN AL-QUR'AN" },
  { no: '1', start: '07.30', end: '08.10', isSlot: true, title: '' },
  { no: '2', start: '08.10', end: '08.50', isSlot: true, title: '' },
  { no: '3', start: '08.50', end: '09.30', isSlot: true, title: '' },
  { no: '4', start: '09.30', end: '10.10', isSlot: true, title: '' },
  { no: '5', start: '10.10', end: '10.40', isBreak: true, title: 'ISTIRAHAT' },
  { no: '6', start: '10.40', end: '11.20', isSlot: true, title: '' },
  { no: '7', start: '11.20', end: '12.00', isSlot: true, title: '' },
  { no: '8', start: '12.00', end: '12.20', isGeneral: true, title: "SHALAT DZUHUR BERJAMA'AH" },
];

const defaultJumatSlots = [
  { no: '0', start: '07.00', end: '07.45', isGeneral: true, title: 'SHOLAT DHUHA & YASINAN' },
  { no: '1', start: '07.45', end: '08.25', isSlot: true, title: '' },
  { no: '2', start: '08.25', end: '09.05', isSlot: true, title: '' },
  { no: '3', start: '09.05', end: '09.45', isSlot: true, title: '' },
  { no: '4', start: '09.45', end: '10.15', isBreak: true, title: "ISTIRAHAT JUM'AT" },
  { no: '5', start: '10.15', end: '10.55', isSlot: true, title: '' },
  { no: '6', start: '11.00', end: '12.30', isGeneral: true, title: "SHALAT JUM'AT BERJAMA'AH" },
];

// Default slots for Jadwal Lokal (4 Kelas: 7, 8, 9A, 9B)
const defaultSeninSlotsLokal = [
  { no: '0', start: '07.00', end: '07.30', isGeneral: true, title: 'UPACARA BENDERA' },
  { no: '1', start: '07.30', end: '08.00', isGeneral: true, title: "TADARUSAN AL-QUR'AN" },
  { no: '2', start: '08.00', end: '08.40', isSlot: true, title: '' },
  { no: '3', start: '08.40', end: '09.20', isSlot: true, title: '' },
  { no: '4', start: '09.20', end: '10.00', isSlot: true, title: '' },
  { no: '5', start: '10.00', end: '10.30', isBreak: true, title: 'ISTIRAHAT' },
  { no: '6', start: '10.30', end: '11.10', isSlot: true, title: '' },
  { no: '7', start: '11.10', end: '11.50', isSlot: true, title: '' },
  { no: '8', start: '11.50', end: '12.30', isSlot: true, title: '' },
  { no: '9', start: '12.30', end: '13.00', isGeneral: true, title: "SHALAT DZUHUR BERJAMA'AH" },
];

const defaultSelasaSabtuSlotsLokal = [
  { no: '0', start: '07.00', end: '07.30', isGeneral: true, title: "TADARUSAN AL-QUR'AN" },
  { no: '1', start: '07.30', end: '08.10', isSlot: true, title: '' },
  { no: '2', start: '08.10', end: '08.50', isSlot: true, title: '' },
  { no: '3', start: '08.50', end: '09.30', isSlot: true, title: '' },
  { no: '4', start: '09.30', end: '10.00', isBreak: true, title: 'ISTIRAHAT' },
  { no: '5', start: '10.00', end: '10.40', isSlot: true, title: '' },
  { no: '6', start: '10.40', end: '11.20', isSlot: true, title: '' },
  { no: '7', start: '11.20', end: '12.00', isSlot: true, title: '' },
  { no: '8', start: '12.00', end: '12.30', isGeneral: true, title: "SHALAT DZUHUR BERJAMA'AH" },
];

const defaultJumatSlotsLokal = [
  { no: '0', start: '07.00', end: '07.45', isGeneral: true, title: 'SHOLAT DHUHA & YASINAN' },
  { no: '1', start: '07.45', end: '08.25', isSlot: true, title: '' },
  { no: '2', start: '08.25', end: '09.05', isSlot: true, title: '' },
  { no: '3', start: '09.05', end: '09.45', isSlot: true, title: '' },
  { no: '4', start: '09.45', end: '10.15', isBreak: true, title: "ISTIRAHAT JUM'AT" },
  { no: '5', start: '10.15', end: '10.55', isSlot: true, title: '' },
  { no: '6', start: '11.00', end: '12.30', isGeneral: true, title: "SHALAT JUM'AT BERJAMA'AH" },
];

const slotsDataUtama = ref({
  senin: JSON.parse(JSON.stringify(defaultSeninSlots)),
  selasa_sabtu: JSON.parse(JSON.stringify(defaultSelasaSabtuSlots)),
  jumat: JSON.parse(JSON.stringify(defaultJumatSlots)),
});

const slotsDataLokal = ref({
  senin: JSON.parse(JSON.stringify(defaultSeninSlotsLokal)),
  selasa_sabtu: JSON.parse(JSON.stringify(defaultSelasaSabtuSlotsLokal)),
  jumat: JSON.parse(JSON.stringify(defaultJumatSlotsLokal)),
});

const slotsData = computed(() => {
  return activeScheduleType.value === 'lokal' ? slotsDataLokal.value : slotsDataUtama.value;
});

const activeYaspinSlots = computed(() => {
  if (activeYaspinDay.value === 'senin') return slotsData.value.senin || [];
  if (activeYaspinDay.value === 'jumat') return slotsData.value.jumat || [];
  return slotsData.value.selasa_sabtu || [];
});

// Slot Configuration Modal State & Functions
const showSlotConfigModal = ref(false);
const configActiveDayTab = ref('senin');
const editingSlots = ref({
  senin: [],
  selasa_sabtu: [],
  jumat: []
});
const savingSlots = ref(false);

const currentDayEditingSlots = computed(() => {
  return editingSlots.value[configActiveDayTab.value] || [];
});

function openSlotConfigModal() {
  configActiveGroupTab.value = activeScheduleType.value;
  const source = configActiveGroupTab.value === 'lokal' ? slotsDataLokal.value : slotsDataUtama.value;
  editingSlots.value = JSON.parse(JSON.stringify(source));
  configActiveDayTab.value = activeYaspinDay.value === 'senin' ? 'senin' : (activeYaspinDay.value === 'jumat' ? 'jumat' : 'selasa_sabtu');
  showSlotConfigModal.value = true;
}

function switchConfigGroup(group) {
  configActiveGroupTab.value = group;
  const source = group === 'lokal' ? slotsDataLokal.value : slotsDataUtama.value;
  editingSlots.value = JSON.parse(JSON.stringify(source));
}

function addNewSlot() {
  const currentList = editingSlots.value[configActiveDayTab.value];
  const nextNo = String(currentList.length);
  currentList.push({
    no: nextNo,
    start: '12.20',
    end: '13.00',
    isSlot: true,
    isGeneral: false,
    isBreak: false,
    title: ''
  });
}

function removeSlot(index) {
  editingSlots.value[configActiveDayTab.value].splice(index, 1);
}

function onSlotTypeChange(slot, type) {
  if (type === 'break') {
    slot.isBreak = true;
    slot.isGeneral = false;
    slot.isSlot = false;
    if (!slot.title) slot.title = 'ISTIRAHAT';
  } else if (type === 'general') {
    slot.isGeneral = true;
    slot.isBreak = false;
    slot.isSlot = false;
    if (!slot.title) slot.title = 'KEGIATAN BERSAMA';
  } else {
    slot.isSlot = true;
    slot.isBreak = false;
    slot.isGeneral = false;
    slot.title = '';
  }
}

function getSlotType(slot) {
  if (slot.isBreak) return 'break';
  if (slot.isGeneral) return 'general';
  return 'slot';
}

async function fetchTimeSlots() {
  try {
    const [resUtama, resLokal] = await Promise.all([
      api.get('admin/schedules/time-slots', { group: 'utama' }).catch(() => null),
      api.get('admin/schedules/time-slots', { group: 'lokal' }).catch(() => null),
    ]);
    const dUtama = resUtama?.data?.data || resUtama?.data;
    if (dUtama && (dUtama.senin || dUtama.selasa_sabtu || dUtama.jumat)) {
      slotsDataUtama.value = {
        senin: dUtama.senin || defaultSeninSlots,
        selasa_sabtu: dUtama.selasa_sabtu || defaultSelasaSabtuSlots,
        jumat: dUtama.jumat || defaultJumatSlots,
      };
    }
    const dLokal = resLokal?.data?.data || resLokal?.data;
    if (dLokal && (dLokal.senin || dLokal.selasa_sabtu || dLokal.jumat)) {
      slotsDataLokal.value = {
        senin: dLokal.senin || defaultSeninSlotsLokal,
        selasa_sabtu: dLokal.selasa_sabtu || defaultSelasaSabtuSlotsLokal,
        jumat: dLokal.jumat || defaultJumatSlotsLokal,
      };
    }
  } catch (err) {
    console.warn('Could not load custom time slots, using defaults', err);
  }
}

async function saveSlotConfig() {
  savingSlots.value = true;
  try {
    const payload = {
      group: configActiveGroupTab.value,
      senin: editingSlots.value.senin || [],
      selasa_sabtu: editingSlots.value.selasa_sabtu || [],
      jumat: editingSlots.value.jumat || [],
      slots: editingSlots.value,
    };
    await api.post('admin/schedules/time-slots', payload);
    if (configActiveGroupTab.value === 'lokal') {
      slotsDataLokal.value = JSON.parse(JSON.stringify(editingSlots.value));
    } else {
      slotsDataUtama.value = JSON.parse(JSON.stringify(editingSlots.value));
    }
    const groupName = configActiveGroupTab.value === 'lokal' ? 'Jadwal Lokal' : 'Jadwal Utama';
    toast.success(`Pengaturan slot waktu ${groupName} berhasil disimpan!`);
    showSlotConfigModal.value = false;
  } catch (err) {
    console.error('Error saving slot config', err);
    toast.error('Gagal menyimpan pengaturan slot waktu.');
  } finally {
    savingSlots.value = false;
  }
}

async function resetSlotConfigToDefault() {
  const groupName = configActiveGroupTab.value === 'lokal' ? 'Jadwal Lokal' : 'Jadwal Utama';
  const isConfirmed = await confirm({
    title: `Reset Slot Waktu ${groupName}?`,
    message: `Semua kustomisasi jam pelajaran ${groupName} akan dikembalikan ke pengaturan bawaan standar madrasah.`,
    confirmText: 'Ya, Kembalikan ke Standar',
    cancelText: 'Batal',
    type: 'danger'
  });
  if (!isConfirmed) return;

  try {
    const res = await api.post('admin/schedules/reset-time-slots', { group: configActiveGroupTab.value });
    const d = res?.data?.data || res?.data;
    if (configActiveGroupTab.value === 'lokal') {
      slotsDataLokal.value = {
        senin: d?.senin || defaultSeninSlotsLokal,
        selasa_sabtu: d?.selasa_sabtu || defaultSelasaSabtuSlotsLokal,
        jumat: d?.jumat || defaultJumatSlotsLokal,
      };
      editingSlots.value = JSON.parse(JSON.stringify(slotsDataLokal.value));
    } else {
      slotsDataUtama.value = {
        senin: d?.senin || defaultSeninSlots,
        selasa_sabtu: d?.selasa_sabtu || defaultSelasaSabtuSlots,
        jumat: d?.jumat || defaultJumatSlots,
      };
      editingSlots.value = JSON.parse(JSON.stringify(slotsDataUtama.value));
    }
    toast.success(`Slot waktu ${groupName} berhasil dikembalikan ke standar madrasah!`);
  } catch (err) {
    console.error('Error resetting slots', err);
    toast.error('Gagal mereset slot waktu.');
  }
}

const filteredClasses = computed(() => {
  const base = scheduleActiveClasses.value;
  if (!selectedClass.value) return base;
  return base.filter(c => c.id == selectedClass.value);
});

const getActiveDayName = () => {
  const d = daysList.find(day => day.key === activeYaspinDay.value);
  return d ? d.name : 'Senin';
};

const getDaySlots = (dayKey) => {
  const k = (dayKey || '').toLowerCase();
  if (k === 'senin') return slotsData.value.senin || defaultSeninSlots;
  if (k === 'jumat') return slotsData.value.jumat || defaultJumatSlots;
  return slotsData.value.selasa_sabtu || defaultSelasaSabtuSlots;
};

const getDayKbmSlots = (dayKey) => {
  return getDaySlots(dayKey).filter(s => !s.isGeneral && !s.isBreak);
};

const selectedJpCount = ref(2);

const form = reactive({
  is_activity: false,
  activity_name: '',
  activity_type: 'upacara',
  class_id: null,
  subject_id: '',
  teacher_id: '',
  day: 'senin',
  start_time: '07:00',
  end_time: '08:00',
  room: 'utama',
});

const currentDayKbmSlots = computed(() => {
  return getDayKbmSlots(form.day || 'senin');
});

const currentStartSlotIndex = computed(() => {
  const slots = currentDayKbmSlots.value;
  if (!slots.length) return -1;
  const current = (form.start_time || '').replace('.', ':').trim();
  const idx = slots.findIndex(s => s.start.replace('.', ':') === current);
  return idx !== -1 ? idx : 0;
});

const currentStartSlotKey = computed(() => {
  const idx = currentStartSlotIndex.value;
  if (idx !== -1 && currentDayKbmSlots.value[idx]) {
    return currentDayKbmSlots.value[idx].no;
  }
  return currentDayKbmSlots.value[0]?.no || '';
});

const availableEndSlots = computed(() => {
  const slots = currentDayKbmSlots.value;
  const startIdx = currentStartSlotIndex.value;
  if (startIdx === -1) return slots;
  return slots.slice(startIdx);
});

const currentEndSlotKey = computed(() => {
  const currentEnd = (form.end_time || '').replace('.', ':').trim();
  const slot = currentDayKbmSlots.value.find(s => s.end.replace('.', ':') === currentEnd);
  return slot ? slot.no : '';
});

const availableJpOptions = computed(() => {
  const slots = currentDayKbmSlots.value;
  const startIdx = currentStartSlotIndex.value;
  if (startIdx === -1 || !slots.length) return [];
  
  const options = [];
  const maxJp = Math.min(4, slots.length - startIdx);
  
  for (let jp = 1; jp <= maxJp; jp++) {
    const endSlot = slots[startIdx + jp - 1];
    if (endSlot) {
      options.push({
        count: jp,
        label: `${jp} JP`,
        endTime: endSlot.end.replace('.', ':'),
        slotNo: endSlot.no,
      });
    }
  }
  return options;
});

const currentJpSummary = computed(() => {
  if (form.is_activity) return '';
  const start = form.start_time;
  const end = form.end_time;
  if (!start || !end) return '';
  
  const slots = currentDayKbmSlots.value;
  const covered = slots.filter(s => {
    const sStart = s.start.replace('.', ':');
    const sEnd = s.end.replace('.', ':');
    return (start < sEnd && end > sStart);
  });
  
  if (covered.length > 0) {
    const firstNo = covered[0].no;
    const lastNo = covered[covered.length - 1].no;
    const rangeText = firstNo === lastNo ? `Jam ${firstNo}` : `Jam ${firstNo} - ${lastNo}`;
    return `${covered.length} JP (${rangeText})`;
  }
  return `${start} - ${end}`;
});

const applyJpDuration = (count) => {
  selectedJpCount.value = count;
  const slots = currentDayKbmSlots.value;
  const startIdx = currentStartSlotIndex.value;
  if (startIdx === -1 || !slots.length) return;
  
  const targetIdx = Math.min(startIdx + count - 1, slots.length - 1);
  const targetSlot = slots[targetIdx];
  if (targetSlot) {
    form.end_time = targetSlot.end.replace('.', ':');
  }
};

const onStartSlotChange = (slotNo) => {
  const slots = currentDayKbmSlots.value;
  const foundIdx = slots.findIndex(s => s.no === slotNo);
  if (foundIdx !== -1) {
    const newStart = slots[foundIdx];
    form.start_time = newStart.start.replace('.', ':');
    applyJpDuration(selectedJpCount.value || 2);
  }
};

const onEndSlotChange = (slotNo) => {
  const slots = currentDayKbmSlots.value;
  const foundIdx = slots.findIndex(s => s.no === slotNo);
  if (foundIdx !== -1) {
    const target = slots[foundIdx];
    form.end_time = target.end.replace('.', ':');
    const startIdx = currentStartSlotIndex.value;
    if (startIdx !== -1 && foundIdx >= startIdx) {
      selectedJpCount.value = foundIdx - startIdx + 1;
    } else {
      selectedJpCount.value = 'custom';
    }
  }
};

const getScheduleJpBadge = (dayKey, item) => {
  if (!item || item.is_activity) return null;
  const kbmSlots = getDayKbmSlots(dayKey);
  const coveredSlots = kbmSlots.filter(s => {
    const sStart = s.start.replace('.', ':');
    const sEnd = s.end.replace('.', ':');
    return (item.start_time < sEnd && item.end_time > sStart);
  });
  if (coveredSlots.length > 1) {
    return `${coveredSlots.length} JP`;
  }
  return null;
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
  form.room = activeScheduleType.value;
  const startFormatted = slot.start.replace('.', ':');
  form.start_time = startFormatted;
  
  const kbm = getDayKbmSlots(dayKey);
  const idx = kbm.findIndex(s => s.no === slot.no || s.start.replace('.', ':') === startFormatted);
  if (idx !== -1 && idx + 1 < kbm.length) {
    form.end_time = kbm[idx + 1].end.replace('.', ':');
    selectedJpCount.value = 2;
  } else {
    form.end_time = slot.end.replace('.', ':');
    selectedJpCount.value = 1;
  }
};

const applyActivityPreset = (name, type, start, end) => {
  form.activity_name = name;
  form.activity_type = type;
  form.class_id = null; // Applies to All Classes
  form.room = activeScheduleType.value;
  if (start) form.start_time = start;
  if (end) form.end_time = end;
};

const fetchSchedules = async () => {
  loading.value = true;
  try {
    const params = {
      room: activeScheduleType.value
    };
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
    // Ensure classes 7 & 8 exist in DB for Jadwal Lokal
    await api.post('admin/schedules/ensure-lokal-classes').catch(() => null);

    const [cRes, sRes, tRes, setRes] = await Promise.all([
      api.get('admin/classes', { all: true, per_page: 500 }).catch(() => null),
      api.get('admin/subjects', { all: true, per_page: 500 }).catch(() => null),
      api.get('admin/teachers', { all: true, per_page: 500 }).catch(() => null),
      api.get('settings').catch(() => null),
    ]);

    const extractItems = (res) => {
      if (!res) return [];
      if (Array.isArray(res.data)) return res.data;
      if (Array.isArray(res.data?.data)) return res.data.data;
      if (Array.isArray(res)) return res;
      return [];
    };

    classes.value = extractItems(cRes);
    subjects.value = extractItems(sRes).sort((a, b) => (a.name || '').localeCompare(b.name || '', 'id'));
    teachers.value = extractItems(tRes).sort((a, b) => (a.full_name || '').localeCompare(b.full_name || '', 'id'));
    settings.value = setRes?.data || {};

    // Auto-align customLokalClassIds to exactly 7, 8, 9A, 9B if not properly set to 4 classes
    if (!customLokalClassIds.value || customLokalClassIds.value.length !== 4) {
      const autoLokal = classes.value.filter(c => {
        const n = (c.name || '').trim().toLowerCase();
        return n === '7' || n === 'kelas 7' || n === '8' || n === 'kelas 8' || n === '9a' || n === 'kelas 9a' || n === '9b' || n === 'kelas 9b';
      });
      if (autoLokal.length >= 4) {
        customLokalClassIds.value = autoLokal.map(c => c.id);
        localStorage.setItem('siakad_lokal_class_ids', JSON.stringify(customLokalClassIds.value));
      }
    }
  } catch (err) {
    console.error('Failed to load dropdown options:', err);
  }
};

onMounted(() => {
  fetchTimeSlots();
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
  form.room = activeScheduleType.value;

  if (selectedClass.value) {
    form.class_id = selectedClass.value;
  } else if (!isActivityMode && activeScheduleType.value === 'lokal' && scheduleActiveClasses.value.length > 0) {
    form.class_id = scheduleActiveClasses.value[0].id;
  } else {
    form.class_id = null;
  }

  form.subject_id = '';
  form.teacher_id = '';
  form.day = targetDayKey || activeYaspinDay.value || 'senin';

  if (!isActivityMode) {
    const kbm = getDayKbmSlots(form.day);
    if (kbm.length > 0) {
      form.start_time = kbm[0].start.replace('.', ':');
      if (kbm.length > 1) {
        form.end_time = kbm[1].end.replace('.', ':');
        selectedJpCount.value = 2;
      } else {
        form.end_time = kbm[0].end.replace('.', ':');
        selectedJpCount.value = 1;
      }
    } else {
      form.start_time = '07:00';
      form.end_time = '08:00';
      selectedJpCount.value = 1;
    }
  } else {
    form.start_time = '07:00';
    form.end_time = '07:30';
  }

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
  form.room = item.room || activeScheduleType.value;

  if (!item.is_activity) {
    const kbm = getDayKbmSlots(item.day);
    const covered = kbm.filter(s => {
      const sStart = s.start.replace('.', ':');
      const sEnd = s.end.replace('.', ':');
      return (item.start_time < sEnd && item.end_time > sStart);
    });
    selectedJpCount.value = covered.length > 0 ? covered.length : 'custom';
  }

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
      room: form.room || activeScheduleType.value,
    };

    if (form.is_activity) {
      payload.activity_name = form.activity_name;
      payload.activity_type = form.activity_type;
      payload.class_id = form.class_id || null;
    } else {
      payload.class_id = form.class_id;
      payload.subject_id = form.subject_id;
      payload.teacher_id = form.teacher_id || null;
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
