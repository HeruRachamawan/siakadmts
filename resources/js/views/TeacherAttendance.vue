<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Presensi Kehadiran Siswa</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Input kehadiran siswa berbasis mata pelajaran dan kelas mengajar secara presisi.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="students.length > 0"
          @click="setAllPresent"
          class="px-4 py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          <span>Set Semua Hadir</span>
        </button>
      </div>
    </div>

    <!-- Options Selector Card -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-3 gap-5">
      <!-- 1. Mata Pelajaran -->
      <div class="space-y-1.5">
        <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          Mata Pelajaran
        </label>
        <select
          v-model="selectedSubject"
          @change="loadStudents"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
        >
          <option value="">-- Tanpa Matpel (Presensi Umum) --</option>
          <option v-for="sbj in subjects" :key="sbj.id" :value="sbj.id">{{ sbj.name }} ({{ sbj.code || '-' }})</option>
        </select>
      </div>

      <!-- 2. Pilih Kelas -->
      <div class="space-y-1.5">
        <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          Pilih Kelas Mengajar <span class="text-red-500">*</span>
        </label>
        <select
          v-model="selectedClass"
          @change="loadStudents"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
        >
          <option value="">-- Pilih Kelas --</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }} (Tingkat {{ cls.grade_level }})</option>
        </select>
      </div>

      <!-- 3. Tanggal -->
      <div class="space-y-1.5">
        <label class="block text-[11px] font-extrabold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Tanggal Presensi <span class="text-red-500">*</span>
        </label>
        <input
          v-model="selectedDate"
          type="date"
          @change="loadStudents"
          class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400"
        />
      </div>
    </div>

    <!-- Live Counter Summary Badges -->
    <div v-if="selectedClass && students.length > 0" class="grid grid-cols-2 sm:grid-cols-5 gap-3">
      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm text-center">
        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Total Siswa</span>
        <p class="text-xl font-black text-slate-800 font-lexend mt-1">{{ students.length }}</p>
      </div>

      <div class="bg-emerald-50/60 rounded-2xl p-4 border border-emerald-100 text-center">
        <span class="text-[10px] font-extrabold text-emerald-700 uppercase tracking-widest">Hadir (H)</span>
        <p class="text-xl font-black text-emerald-700 font-lexend mt-1">{{ countStatus('present') }}</p>
      </div>

      <div class="bg-blue-50/60 rounded-2xl p-4 border border-blue-100 text-center">
        <span class="text-[10px] font-extrabold text-blue-700 uppercase tracking-widest">Sakit (S)</span>
        <p class="text-xl font-black text-blue-700 font-lexend mt-1">{{ countStatus('sick') }}</p>
      </div>

      <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-100 text-center">
        <span class="text-[10px] font-extrabold text-amber-700 uppercase tracking-widest">Izin (I)</span>
        <p class="text-xl font-black text-amber-700 font-lexend mt-1">{{ countStatus('permission') }}</p>
      </div>

      <div class="bg-red-50/60 rounded-2xl p-4 border border-red-100 text-center col-span-2 sm:col-span-1">
        <span class="text-[10px] font-extrabold text-red-700 uppercase tracking-widest">Alpa (A)</span>
        <p class="text-xl font-black text-red-700 font-lexend mt-1">{{ countStatus('alpha') }}</p>
      </div>
    </div>

    <!-- Attendance Sheet Container -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-16 text-slate-400 text-xs font-medium">
        <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
        Memuat lembar presensi siswa...
      </div>

      <!-- No Class Selected State -->
      <div v-else-if="!selectedClass" class="text-center py-16 text-slate-400">
        <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3 border border-emerald-100">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <p class="text-sm font-bold text-slate-700">Silakan Pilih Kelas & Mata Pelajaran</p>
        <p class="text-xs text-slate-400 mt-0.5">Pilih kelas di atas untuk mulai mengisi presensi kehadiran siswa.</p>
      </div>

      <!-- Table Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest w-12">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">SISWA</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NISN / NIS</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">STATUS KEHADIRAN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">CATATAN / KETERANGAN</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(student, index) in students" :key="student.student_id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ index + 1 }}</td>
              
              <!-- Siswa -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-200 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm">
                    <img v-if="student.photo_url" :src="student.photo_url" alt="Photo" class="w-full h-full object-cover" />
                    <div v-else :class="student.gender === 'L' ? 'bg-blue-400' : 'bg-pink-400'" class="w-full h-full flex items-center justify-center">
                      {{ getInitials(student.full_name) }}
                    </div>
                  </div>
                  <span class="text-sm font-bold text-slate-800">{{ student.full_name }}</span>
                </div>
              </td>

              <!-- NISN / NIS -->
              <td class="px-6 py-4">
                <span class="text-xs font-mono font-bold text-slate-700 block">{{ student.nisn || '-' }}</span>
                <span class="text-[10px] text-slate-400 block">NIS: {{ student.nis || '-' }}</span>
              </td>

              <!-- Pill Buttons for Status (H / S / I / A) -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- Hadir -->
                  <button
                    type="button"
                    @click="student.status = 'present'"
                    :class="[
                      student.status === 'present'
                        ? 'bg-emerald-600 text-white font-black shadow-md shadow-emerald-500/20 scale-105'
                        : 'bg-slate-100 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 font-bold',
                      'px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer'
                    ]"
                  >
                    Hadir (H)
                  </button>

                  <!-- Sakit -->
                  <button
                    type="button"
                    @click="student.status = 'sick'"
                    :class="[
                      student.status === 'sick'
                        ? 'bg-blue-600 text-white font-black shadow-md shadow-blue-500/20 scale-105'
                        : 'bg-slate-100 text-slate-500 hover:bg-blue-50 hover:text-blue-700 font-bold',
                      'px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer'
                    ]"
                  >
                    Sakit (S)
                  </button>

                  <!-- Izin -->
                  <button
                    type="button"
                    @click="student.status = 'permission'"
                    :class="[
                      student.status === 'permission'
                        ? 'bg-amber-500 text-white font-black shadow-md shadow-amber-500/20 scale-105'
                        : 'bg-slate-100 text-slate-500 hover:bg-amber-50 hover:text-amber-700 font-bold',
                      'px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer'
                    ]"
                  >
                    Izin (I)
                  </button>

                  <!-- Alpa -->
                  <button
                    type="button"
                    @click="student.status = 'alpha'"
                    :class="[
                      student.status === 'alpha'
                        ? 'bg-red-600 text-white font-black shadow-md shadow-red-500/20 scale-105'
                        : 'bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-700 font-bold',
                      'px-3 py-1.5 rounded-xl text-xs transition-all cursor-pointer'
                    ]"
                  >
                    Alpa (A)
                  </button>
                </div>
              </td>

              <!-- Catatan -->
              <td class="px-6 py-4">
                <input
                  v-model="student.note"
                  type="text"
                  placeholder="Keterangan opsional..."
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 font-medium"
                />
              </td>
            </tr>

            <tr v-if="!students.length">
              <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs font-semibold">
                Tidak ada siswa terdaftar di kelas ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Bottom Save Action Bar -->
      <div v-if="selectedClass && students.length > 0" class="px-8 py-5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500">
          {{ students.length }} Siswa Siap Disimpan
        </span>

        <button
          @click="submitAttendance"
          :disabled="submitting"
          class="px-7 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-all shadow-lg flex items-center gap-2 cursor-pointer disabled:opacity-50"
        >
          <svg v-if="submitting" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
          <span>{{ submitting ? 'Menyimpan Presensi...' : 'Simpan Presensi Siswa' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';

const toast = useToast();

const loading = ref(false);
const submitting = ref(false);

const subjects = ref([]);
const classes = ref([]);
const students = ref([]);

const selectedSubject = ref('');
const selectedClass = ref('');
const selectedDate = ref(new Date().toISOString().substring(0, 10)); // YYYY-MM-DD

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function countStatus(statusKey) {
  return students.value.filter(s => s.status === statusKey).length;
}

function setAllPresent() {
  students.value.forEach(s => {
    s.status = 'present';
  });
  toast.success('Semua siswa diset Hadir (H)');
}

const fetchOptions = async () => {
  try {
    const res = await api.get('teacher/attendance-options');
    const data = res?.data || res || {};
    subjects.value = data.subjects || [];
    classes.value = data.classes || [];

    if (!subjects.value.length) {
      const sRes = await api.get('admin/subjects').catch(() => null);
      subjects.value = sRes?.data?.data || sRes?.data || [];
    }
    if (!classes.value.length) {
      const cRes = await api.get('admin/classes').catch(() => null);
      classes.value = cRes?.data?.data || cRes?.data || [];
    }
  } catch (err) {
    console.error('Failed to load attendance options:', err);
    try {
      const [sRes, cRes] = await Promise.all([
        api.get('admin/subjects').catch(() => null),
        api.get('admin/classes').catch(() => null),
      ]);
      subjects.value = sRes?.data?.data || sRes?.data || [];
      classes.value = cRes?.data?.data || cRes?.data || [];
    } catch {}
  }
};

const loadStudents = async () => {
  if (!selectedClass.value) {
    students.value = [];
    return;
  }

  loading.value = true;
  try {
    const params = {
      class_id: selectedClass.value,
      date: selectedDate.value,
    };
    if (selectedSubject.value) params.subject_id = selectedSubject.value;

    const res = await api.get('teacher/attendance', params);
    const data = res?.data || {};

    students.value = (data.students || []).map(s => ({
      student_id: s.student_id,
      full_name: s.full_name,
      nisn: s.nisn,
      nis: s.nis,
      gender: s.gender,
      photo_url: s.photo_url,
      status: s.status || 'present',
      note: s.note || '',
    }));
  } catch (err) {
    console.error('Failed to load students for attendance:', err);
    toast.error('Gagal memuat daftar siswa');
    students.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchOptions();
});

const submitAttendance = async () => {
  if (!selectedClass.value) {
    toast.error('Pilih kelas mengajar terlebih dahulu');
    return;
  }

  submitting.value = true;
  try {
    const payload = {
      class_id: selectedClass.value,
      subject_id: selectedSubject.value || null,
      date: selectedDate.value,
      attendances: students.value.map(s => ({
        student_id: s.student_id,
        status: s.status,
        note: s.note,
      })),
    };

    await api.post('teacher/attendance', payload);
    toast.success('Presensi siswa berhasil disimpan');
  } catch (err) {
    console.error('Failed to save attendance:', err);
    toast.error('Gagal menyimpan presensi siswa');
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
