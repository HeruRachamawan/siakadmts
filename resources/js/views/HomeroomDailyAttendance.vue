<template>
  <div class="space-y-6 font-inter">
    <!-- Top Header -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-600/20 flex-shrink-0">
          <ClipboardList class="w-6 h-6 text-white" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Absensi Harian Kelas</h1>
            <span v-if="selectedClassObj" class="px-3 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
              Kelas {{ selectedClassObj.name }}
            </span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Khusus Wali Kelas — Catat dan kelola kehadiran harian siswa kelas binaan Anda.</p>
        </div>
      </div>

      <!-- Date & Class Selector -->
      <div class="flex items-center gap-3 flex-wrap">
        <!-- Class Selector Dropdown -->
        <div class="min-w-[160px]">
          <select
            v-model="selectedClassId"
            @change="fetchAttendanceData"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
          >
            <option v-for="cls in availableClasses" :key="cls.id" :value="cls.id">
              {{ cls.is_my_homeroom ? '⭐ ' : '' }}Kelas {{ cls.name }} ({{ cls.students_count }} Siswa)
            </option>
          </select>
        </div>

        <!-- Date Picker with Today button -->
        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl p-1">
          <Calendar class="w-4 h-4 text-slate-400 ml-2" />
          <input
            v-model="selectedDate"
            @change="fetchAttendanceData"
            type="date"
            class="bg-transparent text-xs font-bold text-slate-700 px-2 py-1 focus:outline-none cursor-pointer"
          />
          <button
            type="button"
            @click="setToday"
            class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 transition-colors shadow-2xs cursor-pointer ml-1"
          >
            Hari Ini
          </button>
        </div>

        <!-- Save Button -->
        <button
          @click="saveAttendance"
          :disabled="saving || loading || students.length === 0"
          class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md shadow-emerald-600/20 cursor-pointer disabled:opacity-50"
        >
          <div v-if="saving" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
          <Save v-else class="w-4 h-4" />
          <span>{{ saving ? 'Menyimpan...' : 'Simpan Presensi' }}</span>
        </button>
      </div>
    </div>

    <!-- Status Alert Banner -->
    <div
      v-if="!loading"
      class="p-4 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all"
      :class="isSubmitted ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900' : 'bg-amber-50/80 border-amber-200 text-amber-900'"
    >
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white flex-shrink-0"
          :class="isSubmitted ? 'bg-emerald-600 shadow-sm shadow-emerald-600/30' : 'bg-amber-500 shadow-sm shadow-amber-500/30'"
        >
          <CheckCircle2 v-if="isSubmitted" class="w-5 h-5" />
          <AlertTriangle v-else class="w-5 h-5" />
        </div>
        <div>
          <p class="text-xs font-bold font-lexend uppercase tracking-wide">
            {{ isSubmitted ? 'Presensi Harian Sudah Disimpan' : 'Presensi Harian Belum Disimpan' }}
          </p>
          <p class="text-[11px] opacity-80 mt-0.5">
            {{ isSubmitted ? `Data kehadiran siswa untuk ${formatDisplayDate(selectedDate)} telah tersimpan. Anda tetap dapat memperbaruinya jika ada perubahan.` : `Silakan periksa kehadiran siswa untuk ${formatDisplayDate(selectedDate)} lalu klik 'Simpan Presensi'.` }}
          </p>
        </div>
      </div>

      <!-- Quick Action: Set All Present -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <button
          type="button"
          @click="setAllPresent"
          :disabled="students.length === 0"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm cursor-pointer disabled:opacity-50"
        >
          <Zap class="w-3.5 h-3.5" />
          <span>Set Semua Hadir</span>
        </button>
      </div>
    </div>

    <!-- Summary Stats Row with Lucide Icons -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
      <!-- Total Siswa -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0">
          <Users class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa</p>
          <p class="text-lg font-black text-slate-800 font-lexend mt-0.5">{{ summary.total_students }}</p>
        </div>
      </div>

      <!-- Hadir -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Hadir (H)</p>
          <p class="text-lg font-black text-emerald-800 font-lexend mt-0.5">{{ currentPresentCount }}</p>
        </div>
      </div>

      <!-- Sakit -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
          <HeartPulse class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-sky-700 uppercase tracking-wider">Sakit (S)</p>
          <p class="text-lg font-black text-sky-800 font-lexend mt-0.5">{{ currentSickCount }}</p>
        </div>
      </div>

      <!-- Izin -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
          <FileText class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Izin (I)</p>
          <p class="text-lg font-black text-amber-800 font-lexend mt-0.5">{{ currentPermissionCount }}</p>
        </div>
      </div>

      <!-- Alpa -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3 col-span-2 sm:col-span-1">
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
          <XCircle class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Alpa (A)</p>
          <p class="text-lg font-black text-rose-800 font-lexend mt-0.5">{{ currentAlphaCount }}</p>
        </div>
      </div>
    </div>

    <!-- Student Attendance Table Card -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
      <!-- Search & Class Info -->
      <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
        <div class="relative flex-1 min-w-[220px]">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama siswa atau NISN..."
            class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30"
          />
        </div>

        <div class="text-xs text-slate-400 font-medium">
          Menampilkan <span class="font-bold text-slate-700">{{ filteredStudents.length }}</span> dari <span class="font-bold text-slate-700">{{ students.length }}</span> siswa
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-16 text-center text-slate-400 text-xs font-medium">
        <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-3"></div>
        Memuat data siswa kelas...
      </div>

      <!-- Empty State -->
      <div v-else-if="students.length === 0" class="p-16 text-center space-y-3">
        <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto">
          <Users class="w-7 h-7" />
        </div>
        <p class="text-sm font-bold text-slate-700">Belum Ada Siswa di Kelas Ini</p>
        <p class="text-xs text-slate-400 max-w-sm mx-auto">
          Kelas ini belum memiliki siswa yang terdaftar. Anda dapat memilih kelas lain pada menu dropdown di atas.
        </p>
      </div>

      <!-- Table Body -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-100 uppercase tracking-wider text-[10px]">
              <th class="p-4 w-12 text-center">NO</th>
              <th class="p-4">SISWA</th>
              <th class="p-4 w-28 text-center">NISN / NIS</th>
              <th class="p-4 w-80 text-center">STATUS KEHADIRAN</th>
              <th class="p-4 min-w-[200px]">KETERANGAN / CATATAN</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(st, idx) in filteredStudents"
              :key="st.student_id"
              class="hover:bg-slate-50/60 transition-colors"
              :class="st.status === 'alpha' ? 'bg-rose-50/30' : (st.status === 'sick' ? 'bg-sky-50/20' : (st.status === 'permission' ? 'bg-amber-50/20' : ''))"
            >
              <!-- Number -->
              <td class="p-4 text-center font-bold text-slate-400 text-xs">{{ idx + 1 }}</td>

              <!-- Student Profile -->
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center">
                    <img v-if="st.photo_url" :src="st.photo_url" class="w-full h-full object-cover" alt="Foto" />
                    <span v-else class="font-black text-xs text-slate-600">{{ st.full_name?.charAt(0) || 'S' }}</span>
                  </div>
                  <div>
                    <p class="font-bold text-slate-900 text-xs">{{ st.full_name }}</p>
                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[9px] font-black uppercase" :class="st.gender === 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'">
                      {{ st.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- NISN / NIS -->
              <td class="p-4 text-center font-mono font-bold text-slate-600 text-xs">
                {{ st.nisn || st.nis || '-' }}
              </td>

              <!-- Radio Button Status Pill Selector with Lucide Icons -->
              <td class="p-4">
                <div class="flex items-center justify-center gap-1.5 p-1 bg-slate-100 rounded-xl">
                  <!-- Hadir -->
                  <button
                    type="button"
                    @click="st.status = 'present'"
                    :class="[
                      st.status === 'present' ? 'bg-emerald-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-slate-900',
                      'flex-1 py-1.5 px-2 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1'
                    ]"
                  >
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    <span>Hadir</span>
                  </button>

                  <!-- Sakit -->
                  <button
                    type="button"
                    @click="st.status = 'sick'"
                    :class="[
                      st.status === 'sick' ? 'bg-sky-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-slate-900',
                      'flex-1 py-1.5 px-2 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1'
                    ]"
                  >
                    <HeartPulse class="w-3.5 h-3.5" />
                    <span>Sakit</span>
                  </button>

                  <!-- Izin -->
                  <button
                    type="button"
                    @click="st.status = 'permission'"
                    :class="[
                      st.status === 'permission' ? 'bg-amber-500 text-white font-black shadow-xs' : 'text-slate-600 hover:text-slate-900',
                      'flex-1 py-1.5 px-2 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1'
                    ]"
                  >
                    <FileText class="w-3.5 h-3.5" />
                    <span>Izin</span>
                  </button>

                  <!-- Alpa -->
                  <button
                    type="button"
                    @click="st.status = 'alpha'"
                    :class="[
                      st.status === 'alpha' ? 'bg-rose-600 text-white font-black shadow-xs' : 'text-slate-600 hover:text-slate-900',
                      'flex-1 py-1.5 px-2 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1'
                    ]"
                  >
                    <XCircle class="w-3.5 h-3.5" />
                    <span>Alpa</span>
                  </button>
                </div>
              </td>

              <!-- Note / Keterangan -->
              <td class="p-4">
                <input
                  v-model="st.note"
                  type="text"
                  placeholder="Catatan (cth: Surat dokter terlampir)..."
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Bottom Footer Submit Bar -->
      <div class="p-5 bg-slate-50/80 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
        <div class="text-xs text-slate-500 font-medium">
          💡 Tips: Klik <span class="font-bold text-emerald-700">'Set Semua Hadir'</span> terlebih dahulu lalu sesuaikan siswa yang berhalangan hadir.
        </div>

        <button
          @click="saveAttendance"
          :disabled="saving || loading || students.length === 0"
          class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md shadow-emerald-600/20 cursor-pointer disabled:opacity-50"
        >
          <div v-if="saving" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
          <Save v-else class="w-4 h-4" />
          <span>{{ saving ? 'Menyimpan...' : 'Simpan Presensi Harian' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  ClipboardList,
  Users,
  CheckCircle2,
  HeartPulse,
  FileText,
  XCircle,
  Calendar,
  Zap,
  Save,
  Search,
  AlertTriangle,
} from 'lucide-vue-next';

const toast = useToast();

const loading = ref(true);
const saving = ref(false);

const homeroomClasses = ref([]);
const availableClasses = ref([]);
const selectedClassId = ref(null);
const selectedClassObj = ref(null);
const selectedDate = ref(new Date().toISOString().substring(0, 10));
const isSubmitted = ref(false);
const summary = ref({
  total_students: 0,
  present: 0,
  sick: 0,
  permission: 0,
  alpha: 0,
});
const students = ref([]);
const searchQuery = ref('');

const filteredStudents = computed(() => {
  if (!searchQuery.value) return students.value;
  const q = searchQuery.value.toLowerCase();
  return students.value.filter(s => 
    s.full_name?.toLowerCase().includes(q) ||
    s.nisn?.includes(q) ||
    s.nis?.includes(q)
  );
});

const currentPresentCount = computed(() => students.value.filter(s => s.status === 'present').length);
const currentSickCount = computed(() => students.value.filter(s => s.status === 'sick').length);
const currentPermissionCount = computed(() => students.value.filter(s => s.status === 'permission').length);
const currentAlphaCount = computed(() => students.value.filter(s => s.status === 'alpha').length);

const setToday = () => {
  selectedDate.value = new Date().toISOString().substring(0, 10);
  fetchAttendanceData();
};

const setAllPresent = () => {
  students.value.forEach(s => {
    s.status = 'present';
  });
  toast.success('Seluruh siswa disetel Hadir!');
};

const formatDisplayDate = (dStr) => {
  if (!dStr) return '';
  const d = new Date(dStr);
  return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const fetchAttendanceData = async () => {
  loading.value = true;
  try {
    const params = {
      date: selectedDate.value,
    };
    if (selectedClassId.value) {
      params.class_id = selectedClassId.value;
    }

    const res = await api.get('teacher/homeroom-attendance', params);
    const data = (res && res.data) ? res.data : (res || {});

    homeroomClasses.value = data.homeroom_classes || [];
    availableClasses.value = data.available_classes || [];
    selectedClassObj.value = data.selected_class || null;
    if (selectedClassObj.value && !selectedClassId.value) {
      selectedClassId.value = selectedClassObj.value.id;
    }

    isSubmitted.value = !!data.is_submitted;
    summary.value = data.summary || {
      total_students: 0,
      present: 0,
      sick: 0,
      permission: 0,
      alpha: 0,
    };

    students.value = (data.students || []).map(s => ({
      ...s,
      status: s.status || 'present',
      note: s.note || '',
    }));
  } catch (error) {
    console.error('Failed to load homeroom attendance:', error);
    toast.error(error.response?.data?.message || 'Gagal memuat data presensi harian kelas');
  } finally {
    loading.value = false;
  }
};

const saveAttendance = async () => {
  if (!selectedClassId.value || students.value.length === 0) {
    toast.error('Tidak ada data siswa untuk disimpan');
    return;
  }

  saving.value = true;
  try {
    const payload = {
      class_id: selectedClassId.value,
      date: selectedDate.value,
      attendances: students.value.map(s => ({
        student_id: s.student_id,
        status: s.status || 'present',
        note: s.note || null,
      })),
    };

    const res = await api.post('teacher/homeroom-attendance', payload);
    toast.success(res?.data?.message || 'Presensi harian kelas berhasil disimpan!');
    await fetchAttendanceData();
  } catch (error) {
    console.error('Failed to save attendance:', error);
    toast.error(error.response?.data?.message || 'Gagal menyimpan presensi harian');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchAttendanceData();
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
