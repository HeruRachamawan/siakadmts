<template>
  <div class="space-y-6 font-inter">
    <!-- Header Card -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Input & Pengolahan Nilai Siswa</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Kelola komponen nilai dinamis, tentukan batas KKTP/KKM, dan perhitung statistik secara otomatis.</p>
        </div>
      </div>

      <div v-if="activeYear" class="flex items-center gap-2 bg-indigo-50 border border-indigo-100 px-4 py-2 rounded-2xl flex-shrink-0">
        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 animate-pulse"></span>
        <span class="text-xs font-bold text-indigo-900">
          T.A. Aktif: <strong>{{ activeYear.year }} ({{ activeYear.semester === 'odd' ? 'Ganjil' : 'Genap' }})</strong>
        </span>
      </div>
    </div>

    <!-- Filter & KKTP/KKM Control Bar -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
      <!-- Select Subject -->
      <div class="space-y-1.5">
        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          <span>Mata Pelajaran</span>
          <span class="text-red-500">*</span>
        </label>
        <select
          v-model="selectedSubject"
          @change="fetchStudents"
          class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all cursor-pointer"
        >
          <option value="">-- Pilih Mata Pelajaran --</option>
          <option v-for="sbj in subjects" :key="sbj.id" :value="sbj.id">
            {{ sbj.name }} (Kode: {{ sbj.code || '-' }})
          </option>
        </select>
      </div>

      <!-- Select Class -->
      <div class="space-y-1.5">
        <label class="block text-xs font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          <span>Kelas Mengajar</span>
          <span class="text-red-500">*</span>
        </label>
        <select
          v-model="selectedClass"
          @change="fetchStudents"
          class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all cursor-pointer"
        >
          <option value="">-- Pilih Kelas --</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">
            Kelas {{ cls.name }} (Tingkat {{ cls.grade_level }})
          </option>
        </select>
      </div>

      <!-- KKTP / KKM Setting Field -->
      <div class="space-y-1.5">
        <label class="block text-xs font-extrabold text-amber-600 uppercase tracking-wider flex items-center justify-between">
          <span>Target KKTP / KKM</span>
          <span class="text-[10px] text-slate-400 font-medium">(Batas Ketuntasan)</span>
        </label>
        <div class="relative">
          <input
            v-model.number="kkmTarget"
            type="number"
            min="0"
            max="100"
            placeholder="75"
            class="w-full bg-amber-50/50 border border-amber-200 rounded-2xl pl-4 pr-12 py-3 text-xs font-black text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-400 transition-all font-lexend"
          />
          <span class="absolute right-4 top-3 text-xs font-black text-amber-600">POIN</span>
        </div>
      </div>
    </div>

    <!-- Stats Bar (Visible when class & subject selected) -->
    <div v-if="selectedSubject && selectedClass && students.length > 0" class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rata-Rata Kelas</span>
        <span class="text-2xl font-black text-slate-800 font-lexend mt-1">{{ stats.avg }}</span>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Skor Tertinggi</span>
        <span class="text-2xl font-black text-emerald-600 font-lexend mt-1">{{ stats.max }}</span>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col justify-between">
        <span class="text-[10px] font-bold text-red-600 uppercase tracking-widest">Skor Terendah</span>
        <span class="text-2xl font-black text-red-600 font-lexend mt-1">{{ stats.min }}</span>
      </div>

      <div class="bg-emerald-50/60 rounded-2xl p-4 border border-emerald-100 shadow-sm flex flex-col justify-between">
        <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-widest">Tuntas (≥ {{ kkmTarget }})</span>
        <span class="text-2xl font-black text-emerald-700 font-lexend mt-1">{{ stats.passedCount }} <span class="text-xs font-semibold">Siswa</span></span>
      </div>

      <div class="bg-red-50/60 rounded-2xl p-4 border border-red-100 shadow-sm flex flex-col justify-between col-span-2 md:col-span-1">
        <span class="text-[10px] font-bold text-red-700 uppercase tracking-widest">Remidi (< {{ kkmTarget }})</span>
        <span class="text-2xl font-black text-red-700 font-lexend mt-1">{{ stats.remedialCount }} <span class="text-xs font-semibold">Siswa</span></span>
      </div>
    </div>

    <!-- Main Table Entry -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <!-- Toolbar Header: Dynamic Component & Weight Actions -->
      <div v-if="selectedSubject && selectedClass && students.length > 0" class="px-8 py-4 bg-slate-50/60 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap text-xs font-bold text-slate-600">
          <span class="text-slate-400 font-semibold">Komponen Nilai:</span>
          <span
            v-for="comp in activeComponents"
            :key="comp.id"
            class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-slate-700 shadow-2xs font-lexend text-[11px]"
          >
            {{ comp.name }} (<strong>{{ comp.weight }}%</strong>)
          </span>
          <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
            Total Bobot: {{ totalWeight }}%
          </span>
        </div>

        <div class="flex items-center gap-2">
          <!-- Button Add Component -->
          <button
            type="button"
            @click="openComponentModal"
            class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-1.5 cursor-pointer shadow-sm shadow-indigo-600/20"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>+ Tambah / Atur Komponen Nilai</span>
          </button>

          <!-- Button Auto-Fill KKTP -->
          <button
            type="button"
            @click="setAutoDefaultScore"
            class="px-3.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-xl text-xs border border-amber-200 transition-all flex items-center gap-1.5 cursor-pointer shadow-sm"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Auto-Fill KKTP ({{ kkmTarget }})</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-16 text-slate-400 text-xs font-medium">
        <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
        Memuat data nilai siswa...
      </div>

      <!-- Empty Select Warning -->
      <div v-else-if="!selectedSubject || !selectedClass" class="text-center py-16 text-slate-400 text-xs font-semibold space-y-2">
        <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-2">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <p>Silakan pilih <strong>Mata Pelajaran</strong> dan <strong>Kelas Mengajar</strong> di atas untuk mengolah nilai.</p>
      </div>

      <!-- Table Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-12">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA SISWA</th>

              <!-- Dynamic Component Headers -->
              <th
                v-for="comp in activeComponents"
                :key="comp.id"
                class="px-4 py-4 text-[10px] font-extrabold text-slate-700 uppercase tracking-widest text-center"
              >
                {{ comp.short_name || comp.name }} ({{ comp.weight }}%)
              </th>

              <th class="px-6 py-4 text-[10px] font-bold text-indigo-600 uppercase tracking-widest text-center w-36">NILAI AKHIR</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">STATUS KKTP/KKM</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr
              v-for="(student, index) in students"
              :key="student.student_id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ index + 1 }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-200 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                    <img v-if="student.photo_url && typeof student.photo_url === 'string' && student.photo_url.length > 5" :src="student.photo_url" alt="Photo" class="w-full h-full object-cover" />
                    <div v-else :class="student.gender === 'L' ? 'bg-blue-600' : 'bg-pink-600'" class="w-full h-full flex items-center justify-center">
                      {{ getInitials(student.full_name) }}
                    </div>
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-800 block font-lexend">{{ student.full_name }}</span>
                    <span class="text-[10px] text-slate-400 font-mono">NISN: {{ student.nisn || '-' }}</span>
                  </div>
                </div>
              </td>

              <!-- Dynamic Component Inputs -->
              <td
                v-for="comp in activeComponents"
                :key="comp.id"
                class="px-3 py-4 text-center"
              >
                <input
                  v-model.number="student.scores[comp.id]"
                  type="number"
                  min="0"
                  max="100"
                  step="0.5"
                  class="w-20 bg-slate-50 border border-slate-200 rounded-xl px-2 py-1.5 text-center text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                />
              </td>

              <!-- Live Final Score -->
              <td class="px-6 py-4 text-center">
                <span class="text-sm font-black text-indigo-700 font-lexend">
                  {{ calculateFinalScore(student).toFixed(2) }}
                </span>
              </td>

              <!-- KKTP / KKM Status Badge -->
              <td class="px-6 py-4 text-center">
                <span
                  :class="[
                    calculateFinalScore(student) >= kkmTarget
                      ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                      : 'bg-red-50 text-red-700 border-red-200',
                    'px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border shadow-2xs inline-flex items-center gap-1'
                  ]"
                >
                  <span>{{ calculateFinalScore(student) >= kkmTarget ? '🟢 TUNTAS' : '🔴 REMIDI' }}</span>
                  <span class="text-[9px] font-normal text-slate-400">({{ getPredicateLetter(calculateFinalScore(student)) }})</span>
                </span>
              </td>
            </tr>

            <tr v-if="!students.length">
              <td :colspan="3 + activeComponents.length" class="px-6 py-12 text-center text-slate-400 text-xs font-semibold">
                Tidak ada siswa terdaftar di kelas ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Save Action Bar -->
      <div v-if="selectedSubject && selectedClass && students.length > 0" class="px-8 py-5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
        <div class="text-xs font-bold text-slate-500">
          Target KKTP/KKM: <strong class="text-amber-800">{{ kkmTarget }} Poin</strong> • {{ students.length }} Siswa Siap Disimpan
        </div>

        <button
          @click="submitGrades"
          :disabled="submitting"
          class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-lg shadow-emerald-600/20 flex items-center gap-2 cursor-pointer disabled:opacity-50"
        >
          <svg v-if="submitting" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          <span>{{ submitting ? 'Menyimpan...' : 'Simpan Semua Nilai' }}</span>
        </button>
      </div>
    </div>

    <!-- Modal Pengaturan Komponen Nilai Kustom -->
    <div v-if="showCompModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-xl overflow-hidden border border-slate-100 transform transition-all">
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <h2 class="text-lg font-black text-slate-800 font-lexend uppercase tracking-wider">Atur Komponen & Jenis Nilai</h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Tambah jenis nilai baru dan sesuaikan bobot persentase (%) komponen.</p>
          </div>
          <button @click="showCompModal = false" class="w-9 h-9 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors border border-slate-100 shadow-sm cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto">
          <!-- Preset Buttons -->
          <div class="space-y-2">
            <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wide">Pilih Templat Cepat:</label>
            <div class="flex items-center gap-2 flex-wrap">
              <button
                type="button"
                @click="applyPreset('standard')"
                class="px-3 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition-all cursor-pointer"
              >
                Standar (Tugas 30%, UTS 35%, UAS 35%)
              </button>
              <button
                type="button"
                @click="applyPreset('multi_task')"
                class="px-3 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition-all cursor-pointer"
              >
                Multi-Tugas (Tugas 1 (15%), Tugas 2 (15%), UTS (35%), UAS (35%))
              </button>
            </div>
          </div>

          <!-- Component List -->
          <div class="space-y-3">
            <div v-for="(comp, idx) in modalComponents" :key="comp.id" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
              <div class="flex items-center justify-between gap-3">
                <span class="text-xs font-black text-slate-700">Komponen #{{ idx + 1 }}</span>
                <button
                  v-if="modalComponents.length > 1"
                  type="button"
                  @click="removeComponentRow(idx)"
                  class="text-xs font-bold text-red-500 hover:text-red-700 cursor-pointer"
                >
                  Hapus
                </button>
              </div>

              <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                  <label class="block text-[10px] font-bold text-slate-400 uppercase">Nama Jenis Nilai</label>
                  <input
                    v-model="comp.name"
                    type="text"
                    placeholder="Misal: Tugas 2 / Kuis / Praktikum"
                    class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-400 uppercase">Bobot (%)</label>
                  <input
                    v-model.number="comp.weight"
                    type="number"
                    min="0"
                    max="100"
                    class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 text-center focus:outline-none focus:ring-2 focus:ring-indigo-400"
                  />
                </div>
              </div>
            </div>
          </div>

          <button
            type="button"
            @click="addComponentRow"
            class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-2xl border border-dashed border-slate-300 transition-all flex items-center justify-center gap-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>+ Tambah Komponen Nilai Baru</span>
          </button>
        </div>

        <div class="px-8 py-5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
          <div class="text-xs font-bold" :class="modalTotalWeight === 100 ? 'text-emerald-600' : 'text-amber-600'">
            Total Bobot: {{ modalTotalWeight }}% {{ modalTotalWeight !== 100 ? '(Disarankan Pas 100%)' : '✓ Pas' }}
          </div>

          <div class="flex items-center gap-3">
            <button @click="showCompModal = false" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer">
              Batal
            </button>
            <button @click="saveComponents" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-indigo-600/20 cursor-pointer">
              Terapkan Komponen
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';

const toast = useToast();

const loading = ref(false);
const submitting = ref(false);
const showCompModal = ref(false);

const subjects = ref([]);
const classes = ref([]);
const activeYear = ref(null);
const students = ref([]);

const selectedSubject = ref('');
const selectedClass = ref('');
const kkmTarget = ref(75);

const activeComponents = ref([
  { id: 'assignment', name: 'Tugas', short_name: 'TUGAS', weight: 30 },
  { id: 'uts', name: 'UTS', short_name: 'UTS', weight: 35 },
  { id: 'uas', name: 'UAS', short_name: 'UAS', weight: 35 },
]);

const modalComponents = ref([]);

const totalWeight = computed(() => {
  return activeComponents.value.reduce((sum, c) => sum + (Number(c.weight) || 0), 0);
});

const modalTotalWeight = computed(() => {
  return modalComponents.value.reduce((sum, c) => sum + (Number(c.weight) || 0), 0);
});

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function calculateFinalScore(student) {
  let sum = 0;
  let weightSum = 0;
  activeComponents.value.forEach(comp => {
    const score = Number(student.scores[comp.id]) || 0;
    const w = Number(comp.weight) || 0;
    sum += (score * w) / 100;
    weightSum += w;
  });
  return Math.round(sum * 100) / 100;
}

function getPredicateLetter(score) {
  if (score >= 90) return 'A';
  if (score >= 80) return 'B';
  if (score >= 75) return 'C';
  return 'D';
}

const stats = computed(() => {
  if (!students.value.length) {
    return { avg: 0, max: 0, min: 0, passedCount: 0, remedialCount: 0 };
  }

  const scores = students.value.map(s => calculateFinalScore(s));
  const sum = scores.reduce((a, b) => a + b, 0);
  const avg = (sum / scores.length).toFixed(1);
  const max = Math.max(...scores).toFixed(1);
  const min = Math.min(...scores).toFixed(1);
  const passedCount = scores.filter(s => s >= kkmTarget.value).length;
  const remedialCount = scores.filter(s => s < kkmTarget.value).length;

  return { avg, max, min, passedCount, remedialCount };
});

const loadOptions = async () => {
  try {
    const res = await api.get('teacher/grade-options');
    const data = res?.data || res || {};
    subjects.value = data.subjects || [];
    classes.value = data.classes || [];
    activeYear.value = data.active_academic_year || null;
  } catch (err) {
    console.error('Failed to load grade options:', err);
    toast.error('Gagal memuat opsi mata pelajaran dan kelas');
  }
};

const fetchStudents = async () => {
  if (!selectedSubject.value || !selectedClass.value) {
    students.value = [];
    return;
  }

  loading.value = true;
  try {
    const res = await api.get('teacher/grades', {
      subject_id: selectedSubject.value,
      class_id: selectedClass.value,
      academic_year_id: activeYear.value?.id,
    });
    const data = res?.data || res || {};
    
    if (data.passing_grade) {
      kkmTarget.value = Number(data.passing_grade);
    }

    students.value = (data.students || []).map(s => {
      const scoresObj = {};
      if (s.custom_scores && Array.isArray(s.custom_scores) && s.custom_scores.length > 0) {
        s.custom_scores.forEach(cs => {
          scoresObj[cs.id] = cs.score || 0;
        });
      } else {
        scoresObj['assignment'] = s.score_assignment || 0;
        scoresObj['uts'] = s.score_uts || 0;
        scoresObj['uas'] = s.score_uas || 0;
      }

      return {
        ...s,
        scores: scoresObj,
      };
    });
  } catch (err) {
    console.error('Failed to load student grades:', err);
    toast.error('Gagal memuat daftar nilai siswa');
  } finally {
    loading.value = false;
  }
};

const openComponentModal = () => {
  modalComponents.value = activeComponents.value.map(c => ({ ...c }));
  showCompModal.value = true;
};

const addComponentRow = () => {
  const newId = 'comp_' + Date.now();
  modalComponents.value.push({
    id: newId,
    name: 'Komponen Baru',
    short_name: 'KOMP',
    weight: 10,
  });
};

const removeComponentRow = (idx) => {
  modalComponents.value.splice(idx, 1);
};

const applyPreset = (type) => {
  if (type === 'standard') {
    modalComponents.value = [
      { id: 'assignment', name: 'Tugas', short_name: 'TUGAS', weight: 30 },
      { id: 'uts', name: 'UTS', short_name: 'UTS', weight: 35 },
      { id: 'uas', name: 'UAS', short_name: 'UAS', weight: 35 },
    ];
  } else if (type === 'multi_task') {
    modalComponents.value = [
      { id: 'task_1', name: 'Tugas 1', short_name: 'TG1', weight: 15 },
      { id: 'task_2', name: 'Tugas 2', short_name: 'TG2', weight: 15 },
      { id: 'uts', name: 'UTS', short_name: 'UTS', weight: 35 },
      { id: 'uas', name: 'UAS', short_name: 'UAS', weight: 35 },
    ];
  }
};

const saveComponents = () => {
  activeComponents.value = modalComponents.value.map(c => ({
    ...c,
    short_name: c.name.substring(0, 5).toUpperCase(),
  }));
  
  // Initialize missing score properties for students
  students.value.forEach(s => {
    activeComponents.value.forEach(c => {
      if (s.scores[c.id] === undefined) {
        s.scores[c.id] = 0;
      }
    });
  });

  showCompModal.value = false;
  toast.success('Komponen nilai berhasil diperbarui!');
};

const setAutoDefaultScore = () => {
  students.value.forEach(s => {
    activeComponents.value.forEach(c => {
      if (!s.scores[c.id]) {
        s.scores[c.id] = kkmTarget.value;
      }
    });
  });
  toast.success(`Nilai KKTP/KKM (${kkmTarget.value}) telah diterapkan!`);
};

const submitGrades = async () => {
  if (!selectedSubject.value || !selectedClass.value || !activeYear.value?.id) {
    toast.error('Data kelas, mata pelajaran, atau tahun ajaran belum lengkap');
    return;
  }

  submitting.value = true;
  try {
    const payload = {
      class_id: selectedClass.value,
      subject_id: selectedSubject.value,
      academic_year_id: activeYear.value.id,
      passing_grade: kkmTarget.value,
      grades: students.value.map(s => {
        const customArr = activeComponents.value.map(c => ({
          id: c.id,
          name: c.name,
          weight: c.weight,
          score: Number(s.scores[c.id]) || 0,
        }));

        return {
          student_id: s.student_id,
          score_assignment: Number(s.scores['assignment']) || 0,
          score_uts: Number(s.scores['uts']) || 0,
          score_uas: Number(s.scores['uas']) || 0,
          custom_scores: customArr,
        };
      }),
    };

    await api.post('teacher/grades/batch', payload);
    toast.success('Seluruh nilai siswa & target KKTP/KKM berhasil disimpan!');
    fetchStudents();
  } catch (err) {
    console.error('Failed to submit grades:', err);
    toast.error(err.response?.data?.message || 'Gagal menyimpan nilai siswa');
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  loadOptions();
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
