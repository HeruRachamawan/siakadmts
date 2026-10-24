<template>
  <div class="space-y-6 font-sans">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
      <div>
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-semibold mb-2 border border-emerald-200/60">
          <GraduationCap class="w-3.5 h-3.5 text-emerald-600" />
          <span>Siklus Akhir Tahun Ajaran</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Kenaikan Kelas & Kelulusan Siswa</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Proses massal kenaikan kelas untuk kelas 7 & 8, serta pemindahan otomatis siswa kelas 9 ke Buku Induk Alumni.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <RouterLink
          to="/admin/alumni"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors cursor-pointer border border-slate-200"
        >
          <BookOpen class="w-4 h-4 text-indigo-600" />
          <span>Buku Induk Alumni</span>
        </RouterLink>
      </div>
    </div>

    <!-- Filter & Configuration Panel -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Pilih Kelas Asal -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            1. Pilih Kelas Asal (Yang Akan Diproses) <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <select
              v-model="selectedClassId"
              @change="fetchCandidates"
              class="w-full pl-3 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all cursor-pointer"
            >
              <option value="" disabled>-- Pilih Rombel Kelas Asal --</option>
              <option v-for="c in classOptions" :key="c.id" :value="c.id">
                Kelas {{ c.name }} (Tingkat {{ c.grade_level }})
              </option>
            </select>
          </div>
        </div>

        <!-- Tahun Ajaran Kelulusan / Kenaikan -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            2. Tahun Ajaran Kelulusan
          </label>
          <input
            v-model="graduationYearInput"
            type="text"
            placeholder="Contoh: 2025/2026"
            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all"
          />
        </div>

        <!-- Tanggal Keputusan -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            3. Tanggal SK Keputusan
          </label>
          <input
            v-model="graduationDateInput"
            type="date"
            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all cursor-pointer"
          />
        </div>
      </div>
    </div>

    <!-- Active Class Data & Candidates Table -->
    <div v-if="loadingCandidates" class="bg-white p-12 rounded-2xl border border-slate-200/80 text-center shadow-xs">
      <div class="inline-block animate-spin w-8 h-8 border-4 border-emerald-600 border-t-transparent rounded-full mb-3"></div>
      <p class="text-xs font-bold text-slate-600">Memuat daftar siswa dan presensi...</p>
    </div>

    <div v-else-if="sourceClassData && candidates.length > 0" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <!-- Quick Action Toolbar Header -->
      <div class="p-5 border-b border-slate-200 bg-slate-50/70 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-sm shadow-xs">
            {{ sourceClassData.grade_level }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-base font-black text-slate-900">Kelas {{ sourceClassData.name }}</h2>
              <span class="px-2 py-0.5 rounded text-[11px] font-black" :class="sourceClassData.is_final_grade ? 'bg-indigo-100 text-indigo-900' : 'bg-emerald-100 text-emerald-900'">
                {{ sourceClassData.is_final_grade ? 'Tingkat Akhir (Calon Lulus)' : 'Kenaikan Kelas' }}
              </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">Total: {{ candidates.length }} Siswa Terdaftar</p>
          </div>
        </div>

        <!-- Mass Apply Controls -->
        <div class="flex flex-wrap items-center gap-2">
          <!-- Tombol Naikkan Semua ke Kelas -->
          <div v-if="!sourceClassData.is_final_grade" class="flex items-center gap-1.5 bg-white p-1 rounded-xl border border-slate-200 shadow-2xs">
            <span class="text-[11px] font-bold text-slate-500 pl-2">Naikkan Semua ke:</span>
            <select
              v-model="massTargetClassId"
              class="px-2 py-1 bg-slate-50 text-xs font-bold rounded-lg border border-slate-200 cursor-pointer"
            >
              <option value="">-- Pilih Kelas --</option>
              <option v-for="c in filteredTargetClasses" :key="c.id" :value="c.id">
                Kelas {{ c.name }}
              </option>
            </select>
            <button
              @click="applyMassAction('naik_kelas')"
              :disabled="!massTargetClassId"
              class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer disabled:opacity-40"
            >
              Terapkan
            </button>
          </div>

          <!-- Tombol Luluskan Semua untuk Kelas 9 -->
          <button
            v-if="sourceClassData.is_final_grade"
            @click="applyMassAction('lulus')"
            class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-colors cursor-pointer flex items-center gap-1.5"
          >
            <CheckCircle2 class="w-3.5 h-3.5" />
            <span>Set Semua LULUS</span>
          </button>
        </div>
      </div>

      <!-- Table of Students -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200 text-[11px] uppercase tracking-wider">
              <th class="py-3 px-4 w-12 text-center">No</th>
              <th class="py-3 px-4">Nama Siswa & NISN</th>
              <th class="py-3 px-4 text-center">L/P</th>
              <th class="py-3 px-4 text-center">Rekap Absensi (H / S / I / A)</th>
              <th class="py-3 px-4 min-w-[190px]">Keputusan Status</th>
              <th class="py-3 px-4 min-w-[170px]">Kelas Tujuan Baru</th>
              <th class="py-3 px-4 min-w-[180px]">Catatan / Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(st, idx) in candidates"
              :key="st.id"
              class="hover:bg-slate-50/80 transition-colors"
              :class="{
                'bg-emerald-50/30': st.action === 'naik_kelas',
                'bg-indigo-50/30': st.action === 'lulus',
                'bg-amber-50/30': st.action === 'tinggal_kelas',
              }"
            >
              <td class="py-3 px-4 text-center font-bold text-slate-500">{{ idx + 1 }}</td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-slate-200 flex-shrink-0 overflow-hidden border border-slate-300">
                    <img v-if="st.photo_url" :src="st.photo_url" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center font-black text-slate-500 text-xs">
                      {{ st.full_name.charAt(0) }}
                    </div>
                  </div>
                  <div>
                    <p class="font-bold text-slate-900 leading-tight">{{ st.full_name }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">NISN: {{ st.nisn || '-' }} &bull; NIS: {{ st.nis || '-' }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" :class="st.gender === 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'">
                  {{ st.gender }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-1 font-mono text-[11px] font-semibold">
                  <span class="text-emerald-700 font-bold" title="Hadir">{{ st.attendance_summary.hadir }}H</span>
                  <span class="text-slate-300">/</span>
                  <span class="text-blue-600" title="Sakit">{{ st.attendance_summary.sakit }}S</span>
                  <span class="text-slate-300">/</span>
                  <span class="text-amber-600" title="Izin">{{ st.attendance_summary.izin }}I</span>
                  <span class="text-slate-300">/</span>
                  <span class="text-rose-600 font-bold" :class="{'bg-rose-100 px-1 rounded': st.attendance_summary.alfa > 3}" title="Alfa">{{ st.attendance_summary.alfa }}A</span>
                </div>
              </td>
              <!-- Decision Action Radio/Select -->
              <td class="py-3 px-4">
                <select
                  v-model="st.action"
                  @change="handleActionChange(st)"
                  class="w-full px-2.5 py-1.5 rounded-lg border text-xs font-bold cursor-pointer transition-colors"
                  :class="{
                    'bg-emerald-100/70 border-emerald-300 text-emerald-900': st.action === 'naik_kelas',
                    'bg-indigo-100/70 border-indigo-300 text-indigo-900': st.action === 'lulus',
                    'bg-amber-100/70 border-amber-300 text-amber-900': st.action === 'tinggal_kelas',
                    'bg-slate-100 border-slate-300 text-slate-700': st.action === 'mutasi' || st.action === 'keluar',
                  }"
                >
                  <option value="naik_kelas">✅ Naik Kelas</option>
                  <option value="tinggal_kelas">⚠️ Tinggal Kelas</option>
                  <option value="lulus">🎓 Lulus (Alumni)</option>
                  <option value="mutasi">📦 Pindah / Mutasi</option>
                  <option value="keluar">❌ Keluar (D.O)</option>
                </select>
              </td>

              <!-- Target Class -->
              <td class="py-3 px-4">
                <div v-if="st.action === 'naik_kelas' || st.action === 'tinggal_kelas'">
                  <select
                    v-model="st.target_class_id"
                    class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-800 cursor-pointer focus:ring-1 focus:ring-emerald-500"
                  >
                    <option value="" disabled>-- Pilih Rombel --</option>
                    <option v-for="c in availableTargetClassesForStudent(st)" :key="c.id" :value="c.id">
                      Kelas {{ c.name }}
                    </option>
                  </select>
                </div>
                <div v-else-if="st.action === 'lulus'" class="text-indigo-800 font-bold text-xs flex items-center gap-1">
                  <GraduationCap class="w-3.5 h-3.5 text-indigo-600" />
                  <span>Buku Induk Alumni</span>
                </div>
                <div v-else class="text-slate-400 italic text-xs">
                  Tidak memiliki kelas
                </div>
              </td>

              <!-- Notes -->
              <td class="py-3 px-4">
                <input
                  v-model="st.notes"
                  type="text"
                  placeholder="Catatan wali kelas..."
                  class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:bg-white focus:ring-1 focus:ring-emerald-500"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Bottom Action Footer -->
      <div class="p-5 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="text-xs text-slate-600 font-medium">
          Ringkasan Tindakan: 
          <span class="font-bold text-emerald-700">{{ summaryCounts.naik_kelas }} Naik</span> &bull; 
          <span class="font-bold text-amber-700">{{ summaryCounts.tinggal_kelas }} Tinggal</span> &bull; 
          <span class="font-bold text-indigo-700">{{ summaryCounts.lulus }} Lulus</span>
        </div>

        <button
          @click="showConfirmModal = true"
          :disabled="isProcessing"
          class="w-full sm:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
        >
          <Save class="w-4 h-4" />
          <span>Simpan & Eksekusi Kenaikan Kelas</span>
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="selectedClassId && !loadingCandidates && candidates.length === 0" class="bg-white p-12 rounded-2xl border border-slate-200/80 text-center shadow-xs">
      <Users class="w-10 h-10 text-slate-300 mx-auto mb-2" />
      <h3 class="text-sm font-bold text-slate-800">Tidak Ada Siswa Aktif</h3>
      <p class="text-xs text-slate-500 mt-1">Kelas ini tidak memiliki siswa aktif untuk diproses.</p>
    </div>

    <!-- Initial Prompt -->
    <div v-else class="bg-white p-12 rounded-2xl border border-dashed border-slate-300 text-center">
      <GraduationCap class="w-12 h-12 text-slate-300 mx-auto mb-3" />
      <h3 class="text-base font-bold text-slate-800">Pilih Kelas Terlebih Dahulu</h3>
      <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
        Silakan pilih kelas asal di atas untuk memulai peninjauan absensi dan pemrosesan kenaikan kelas atau kelulusan siswa.
      </p>
    </div>

    <!-- Confirmation Modal -->
    <Transition name="modal-fade">
      <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showConfirmModal = false"></div>
        <div class="relative bg-white rounded-2xl p-6 shadow-2xl w-full max-w-lg space-y-4 border border-slate-200 animate-slide-up z-10">
          <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
              <AlertTriangle class="w-5 h-5 text-emerald-700" />
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-900">Konfirmasi Kenaikan & Kelulusan</h3>
              <p class="text-xs text-slate-500">Periksa kembali ringkasan eksekusi berikut</p>
            </div>
          </div>

          <div class="space-y-2.5 text-xs text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-200">
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-500">Kelas Asal:</span>
              <span class="font-bold text-slate-900">Kelas {{ sourceClassData?.name }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-500">Siswa Naik Kelas:</span>
              <span class="font-bold text-emerald-700">{{ summaryCounts.naik_kelas }} Siswa</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-500">Siswa Tinggal Kelas:</span>
              <span class="font-bold text-amber-700">{{ summaryCounts.tinggal_kelas }} Siswa</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-200/60">
              <span class="text-slate-500">Siswa Lulus (Alumni):</span>
              <span class="font-bold text-indigo-700">{{ summaryCounts.lulus }} Siswa</span>
            </div>
            <div class="flex justify-between py-1">
              <span class="text-slate-500">Tahun Kelulusan:</span>
              <span class="font-bold text-slate-900">{{ graduationYearInput }}</span>
            </div>
          </div>

          <p class="text-[11px] text-slate-500 leading-relaxed italic">
            * Riwayat kelas lama siswa akan otomatis dicatat pada buku riwayat akademik sekolah dan nilai tahun sebelumnya tetap aman 100%.
          </p>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button
              type="button"
              @click="showConfirmModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="executePromotions"
              :disabled="isProcessing"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 transition-colors cursor-pointer flex items-center gap-1.5"
            >
              <div v-if="isProcessing" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <span>{{ isProcessing ? 'Memproses...' : 'Ya, Eksekusi Sekarang' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  GraduationCap,
  BookOpen,
  Users,
  CheckCircle2,
  Save,
  AlertTriangle
} from 'lucide-vue-next';

const toast = useToast();

const classOptions = ref([]);
const selectedClassId = ref('');
const graduationYearInput = ref('');
const graduationDateInput = ref(new Date().toISOString().split('T')[0]);

const loadingCandidates = ref(false);
const sourceClassData = ref(null);
const candidates = ref([]);
const availableClasses = ref([]);
const massTargetClassId = ref('');

const isProcessing = ref(false);
const showConfirmModal = ref(false);

const summaryCounts = computed(() => {
  const counts = { naik_kelas: 0, tinggal_kelas: 0, lulus: 0, mutasi: 0, keluar: 0 };
  candidates.value.forEach(c => {
    if (counts[c.action] !== undefined) counts[c.action]++;
  });
  return counts;
});

const filteredTargetClasses = computed(() => {
  if (!sourceClassData.value) return availableClasses.value;
  const currentLevel = parseInt(sourceClassData.value.grade_level, 10);
  if (isNaN(currentLevel)) return availableClasses.value;
  // target next level (e.g. from 7 -> 8, from 8 -> 9)
  return availableClasses.value.filter(c => parseInt(c.grade_level, 10) === currentLevel + 1);
});

function availableTargetClassesForStudent(student) {
  if (student.action === 'tinggal_kelas') {
    // Keep in same grade level
    const currentLevel = parseInt(sourceClassData.value?.grade_level, 10);
    return availableClasses.value.filter(c => parseInt(c.grade_level, 10) === currentLevel);
  }
  return filteredTargetClasses.value.length > 0 ? filteredTargetClasses.value : availableClasses.value;
}

onMounted(async () => {
  await fetchClassOptions();
  const nowYear = new Date().getFullYear();
  graduationYearInput.value = `${nowYear}/${nowYear + 1}`;
});

async function fetchClassOptions() {
  try {
    const res = await api.get('admin/classes?all=true');
    classOptions.value = res.data || [];
  } catch (err) {
    toast.error('Gagal memuat daftar kelas');
  }
}

async function fetchCandidates() {
  if (!selectedClassId.value) return;
  loadingCandidates.value = true;
  try {
    const res = await api.get(`admin/promotions/candidates?class_id=${selectedClassId.value}`);
    const data = res.data;
    sourceClassData.value = data.source_class;
    availableClasses.value = data.available_classes || [];

    // Map candidates with initial selection
    candidates.value = (data.candidates || []).map(c => {
      const defaultAction = c.default_action || (sourceClassData.value.is_final_grade ? 'lulus' : 'naik_kelas');
      let targetClassId = '';

      if (defaultAction === 'naik_kelas' && filteredTargetClasses.value.length > 0) {
        targetClassId = filteredTargetClasses.value[0].id;
      }

      return {
        ...c,
        action: defaultAction,
        target_class_id: targetClassId,
        notes: '',
      };
    });

    if (filteredTargetClasses.value.length > 0) {
      massTargetClassId.value = filteredTargetClasses.value[0].id;
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengambil data siswa kelas');
  } finally {
    loadingCandidates.value = false;
  }
}

function handleActionChange(student) {
  if (student.action === 'naik_kelas') {
    if (filteredTargetClasses.value.length > 0) {
      student.target_class_id = filteredTargetClasses.value[0].id;
    }
  } else if (student.action === 'tinggal_kelas') {
    student.target_class_id = sourceClassData.value?.id;
  } else {
    student.target_class_id = null;
  }
}

function applyMassAction(action) {
  candidates.value.forEach(st => {
    st.action = action;
    if (action === 'naik_kelas') {
      st.target_class_id = massTargetClassId.value;
    } else if (action === 'lulus') {
      st.target_class_id = null;
    }
  });
  toast.success(`Berhasil menerapkan aksi massal: ${action === 'naik_kelas' ? 'Naik Kelas' : 'Lulus'}`);
}

async function executePromotions() {
  // Validate that students with 'naik_kelas' have target_class_id
  const missingClass = candidates.value.find(c => c.action === 'naik_kelas' && !c.target_class_id);
  if (missingClass) {
    toast.error(`Siswa "${missingClass.full_name}" belum ditentukan kelas tujuan barunya.`);
    showConfirmModal.value = false;
    return;
  }

  isProcessing.value = true;
  try {
    const payload = {
      source_class_id: selectedClassId.value,
      graduation_year: graduationYearInput.value,
      graduation_date: graduationDateInput.value,
      promotions: candidates.value.map(c => ({
        student_id: c.id,
        action: c.action,
        target_class_id: c.target_class_id || null,
        notes: c.notes || null,
      })),
    };

    const res = await api.post('admin/promotions/process', payload);
    toast.success(res.message || 'Kenaikan kelas dan kelulusan berhasil dieksekusi!');
    showConfirmModal.value = false;
    // Reload candidates or reset selection
    selectedClassId.value = '';
    candidates.value = [];
    sourceClassData.value = null;
    await fetchClassOptions();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal memproses kenaikan kelas');
  } finally {
    isProcessing.value = false;
  }
}
</script>
