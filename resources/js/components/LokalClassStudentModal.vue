<template>
  <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-xs font-inter animate-in fade-in duration-150">
    <div class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-150">
      <!-- Modal Header -->
      <div class="px-6 py-5 bg-gradient-to-r from-teal-900 via-teal-800 to-slate-900 text-white flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center font-black text-lg text-white shadow-inner">
            {{ classRoom?.name || '?' }}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h3 class="font-extrabold text-base sm:text-lg text-white font-lexend">
                Kelola Anggota Siswa: Kelas {{ classRoom?.name }}
              </h3>
              <span class="text-[10px] px-2 py-0.5 rounded-full bg-teal-400/20 border border-teal-300/40 text-teal-200 font-bold uppercase tracking-wider">
                Jadwal Lokal
              </span>
            </div>
            <p class="text-xs text-teal-200/90 mt-0.5 font-medium">
              Pilih siswa dari rombel utama untuk dimasukkan ke dalam kelompok belajar jadwal lokal ini.
            </p>
          </div>
        </div>

        <button
          type="button"
          @click="emit('close')"
          class="p-2 text-teal-200 hover:text-white rounded-xl hover:bg-white/10 transition-colors cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Info Banner -->
      <div class="px-6 py-3 bg-teal-50/80 border-b border-teal-100/80 flex items-center gap-2.5 text-xs text-teal-900 flex-shrink-0">
        <span class="text-base flex-shrink-0">💡</span>
        <p class="font-medium">
          <strong>Aman & Fleksibel:</strong> Siswa yang dipilih ke dalam Kelas Lokal ini <span class="underline decoration-teal-500 font-bold">tetap aman terdaftar di Rombel Utama aslinya</span> (misal: 7A atau 7B). Rombel induk tidak akan berubah atau terhapus.
        </p>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
        <!-- LOADING SKELETON -->
        <div v-if="loading" class="text-center py-16">
          <div class="inline-flex w-10 h-10 border-4 border-slate-100 border-t-teal-600 rounded-full animate-spin mb-4"></div>
          <p class="text-sm font-semibold text-slate-500">Memuat data siswa kandidat...</p>
        </div>

        <template v-else>
          <!-- 1. BULK SELECTION BY ROMBEL UTAMA -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                  1. Pilih Cepat Seluruh Siswa per Rombel Asal
                </h4>
              </div>
              <span class="text-[11px] text-slate-400 font-medium">Klik tombol untuk centang/batal semua siswa di rombel tersebut</span>
            </div>

            <div v-if="originClasses.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
              <div
                v-for="orig in originClasses"
                :key="'orig-' + orig.id"
                class="p-4 rounded-2xl border transition-all cursor-pointer select-none flex flex-col justify-between gap-3 shadow-2xs"
                :class="isAllClassSelected(orig.id) ? 'bg-teal-50/90 border-teal-300 ring-2 ring-teal-500/20' : 'bg-slate-50/80 border-slate-200/80 hover:bg-slate-100/80'"
                @click="toggleClassSelection(orig.id)"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div
                      class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-xs"
                      :class="isAllClassSelected(orig.id) ? 'bg-teal-600 text-white' : 'bg-slate-200 text-slate-700'"
                    >
                      {{ orig.name }}
                    </div>
                    <div>
                      <h5 class="text-xs font-black text-slate-800">Kelas {{ orig.name }}</h5>
                      <p class="text-[10px] text-slate-400 font-medium">{{ getClassStudentsCount(orig.id) }} Total Siswa</p>
                    </div>
                  </div>

                  <span
                    class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-full"
                    :class="isAllClassSelected(orig.id) ? 'bg-teal-200/80 text-teal-900' : 'bg-slate-200/80 text-slate-600'"
                  >
                    {{ getSelectedCountForClass(orig.id) }} / {{ getClassStudentsCount(orig.id) }}
                  </span>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 text-[11px]">
                  <span v-if="isAllClassSelected(orig.id)" class="text-teal-700 font-bold flex items-center gap-1">
                    <Check class="w-3.5 h-3.5" />
                    <span>Semua Terpilih</span>
                  </span>
                  <span v-else-if="getSelectedCountForClass(orig.id) > 0" class="text-amber-700 font-bold">
                    <span>Sebagian Terpilih</span>
                  </span>
                  <span v-else class="text-slate-400 font-medium">
                    <span>Belum Dipilih</span>
                  </span>

                  <span class="text-[10px] font-bold text-teal-700 hover:underline">
                    {{ isAllClassSelected(orig.id) ? 'Batal Pilih' : 'Pilih Semua' }} →
                  </span>
                </div>
              </div>
            </div>

            <div v-else class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-center text-xs text-slate-500">
              Tidak ada rombel reguler terpisah pada tingkat ini. Anda dapat memilih siswa langsung dari daftar di bawah.
            </div>
          </div>

          <!-- 2. INDIVIDUAL SELECTION & SEARCH -->
          <div class="space-y-3 pt-2 border-t border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider">
                  2. Pilih Satuan / Pilah Siswa Tertentu ({{ filteredStudents.length }} Siswa Tampil)
                </h4>
              </div>

              <!-- Action buttons for visible students -->
              <div class="flex items-center gap-2 flex-wrap">
                <button
                  type="button"
                  @click="selectAllVisible"
                  class="px-2.5 py-1 bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                >
                  Centang Semua Tampil
                </button>
                <button
                  type="button"
                  @click="deselectAllVisible"
                  class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 border border-slate-200 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                >
                  Kosongkan Tampil
                </button>
              </div>
            </div>

            <!-- Search & Rombel Filter -->
            <div class="flex flex-col sm:flex-row items-center gap-2.5">
              <!-- Search Bar -->
              <div class="relative flex-1 w-full">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari nama siswa atau NISN..."
                  class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition-all font-medium"
                />
                <Search class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400" />
              </div>

              <!-- Filter Rombel Asal -->
              <div v-if="originClasses.length > 1" class="w-full sm:w-auto">
                <select
                  v-model="selectedOriginFilter"
                  class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 cursor-pointer"
                >
                  <option value="">Semua Rombel Asal</option>
                  <option v-for="orig in originClasses" :key="'opt-' + orig.id" :value="orig.id">
                    Rombel {{ orig.name }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Student List Checklist Table -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-2xs">
              <div class="max-h-72 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                <div
                  v-for="st in filteredStudents"
                  :key="'st-' + st.id"
                  @click="toggleStudent(st.id)"
                  class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-teal-50/40 transition-colors cursor-pointer select-none"
                  :class="selectedStudentIds.includes(st.id) ? 'bg-teal-50/30' : ''"
                >
                  <div class="flex items-center gap-3">
                    <input
                      type="checkbox"
                      :checked="selectedStudentIds.includes(st.id)"
                      @click.stop="toggleStudent(st.id)"
                      class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 cursor-pointer"
                    />

                    <!-- Avatar Initials -->
                    <div
                      class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0"
                      :class="st.gender === 'P' ? 'bg-pink-400' : 'bg-blue-400'"
                    >
                      {{ getInitials(st.full_name) }}
                    </div>

                    <div>
                      <h5 class="text-xs font-bold text-slate-800 leading-tight">
                        {{ st.full_name }}
                      </h5>
                      <p class="text-[10px] text-slate-400 font-mono">
                        NISN: {{ st.nisn || '-' }} &bull; NIS: {{ st.nis || '-' }}
                      </p>
                    </div>
                  </div>

                  <!-- Origin Rombel Badge -->
                  <div class="flex items-center gap-2">
                    <span class="text-[10px] px-2 py-0.5 rounded-md font-bold bg-slate-100 text-slate-600 border border-slate-200">
                      Rombel {{ st.origin_class_name }}
                    </span>
                    <span
                      v-if="selectedStudentIds.includes(st.id)"
                      class="text-[10px] font-extrabold text-teal-700 bg-teal-100/80 px-2 py-0.5 rounded-md border border-teal-200 hidden sm:inline-block"
                    >
                      ✓ Terpilih
                    </span>
                  </div>
                </div>

                <!-- Empty filter state -->
                <div v-if="!filteredStudents.length" class="py-10 text-center text-xs text-slate-400">
                  Tidak ada siswa yang sesuai kriteria pencarian / filter.
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 flex-shrink-0">
        <!-- Counter Info -->
        <div class="flex items-center gap-2 text-xs text-slate-700 font-medium">
          <div class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></div>
          <span>
            Total Terpilih: <strong class="text-teal-800 text-sm font-black">{{ selectedStudentIds.length }}</strong> dari {{ students.length }} siswa
          </span>
        </div>

        <!-- Buttons -->
        <div class="flex items-center gap-2.5 justify-end">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition-colors cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="saveAssignments"
            :disabled="saving || loading"
            class="px-5 py-2 bg-teal-700 hover:bg-teal-800 disabled:opacity-50 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-teal-700/20 flex items-center justify-center gap-2 cursor-pointer active:scale-95"
          >
            <svg v-if="saving" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle>
            </svg>
            <span>{{ saving ? 'Menyimpan Anggota...' : 'Simpan Anggota Siswa' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { X, Search, Check } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  classRoom: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close', 'saved']);
const toast = useToast();

const loading = ref(false);
const saving = ref(false);
const originClasses = ref([]);
const students = ref([]);
const selectedStudentIds = ref([]);
const searchQuery = ref('');
const selectedOriginFilter = ref('');

// Watch modal visibility to load data
watch(() => props.show, (newVal) => {
  if (newVal && props.classRoom?.id) {
    loadCandidateStudents();
  }
});

async function loadCandidateStudents() {
  if (!props.classRoom?.id) return;
  loading.value = true;
  searchQuery.value = '';
  selectedOriginFilter.value = '';

  try {
    const res = await api.get(`admin/classes/${props.classRoom.id}/candidate-students`);
    const data = res?.data || {};
    originClasses.value = data.origin_classes || [];
    students.value = data.students || [];

    // Pre-select students that already belong to this lokal class
    selectedStudentIds.value = students.value
      .filter(s => s.is_selected || s.lokal_class_id == props.classRoom.id)
      .map(s => s.id);
  } catch (err) {
    console.error('Failed to load candidate students:', err);
    toast.error('Gagal memuat daftar siswa untuk kelas lokal');
  } finally {
    loading.value = false;
  }
}

// Bulk Class Helpers
function getClassStudentsCount(originClassId) {
  return students.value.filter(s => s.origin_class_id == originClassId).length;
}

function getSelectedCountForClass(originClassId) {
  const classStudentIds = students.value
    .filter(s => s.origin_class_id == originClassId)
    .map(s => s.id);
  return classStudentIds.filter(id => selectedStudentIds.value.includes(id)).length;
}

function isAllClassSelected(originClassId) {
  const classStudentIds = students.value
    .filter(s => s.origin_class_id == originClassId)
    .map(s => s.id);
  if (!classStudentIds.length) return false;
  return classStudentIds.every(id => selectedStudentIds.value.includes(id));
}

function toggleClassSelection(originClassId) {
  const classStudentIds = students.value
    .filter(s => s.origin_class_id == originClassId)
    .map(s => s.id);
  if (!classStudentIds.length) return;

  if (isAllClassSelected(originClassId)) {
    // Deselect all from this class
    selectedStudentIds.value = selectedStudentIds.value.filter(id => !classStudentIds.includes(id));
  } else {
    // Select all from this class
    const set = new Set([...selectedStudentIds.value, ...classStudentIds]);
    selectedStudentIds.value = Array.from(set);
  }
}

// Individual Helpers
function toggleStudent(studentId) {
  const idx = selectedStudentIds.value.indexOf(studentId);
  if (idx > -1) {
    selectedStudentIds.value.splice(idx, 1);
  } else {
    selectedStudentIds.value.push(studentId);
  }
}

const filteredStudents = computed(() => {
  let list = students.value;
  if (selectedOriginFilter.value) {
    list = list.filter(s => s.origin_class_id == selectedOriginFilter.value);
  }
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(s =>
      (s.full_name || '').toLowerCase().includes(q) ||
      (s.nisn || '').toLowerCase().includes(q) ||
      (s.nis || '').toLowerCase().includes(q)
    );
  }
  return list;
});

function selectAllVisible() {
  const visibleIds = filteredStudents.value.map(s => s.id);
  const set = new Set([...selectedStudentIds.value, ...visibleIds]);
  selectedStudentIds.value = Array.from(set);
}

function deselectAllVisible() {
  const visibleIds = new Set(filteredStudents.value.map(s => s.id));
  selectedStudentIds.value = selectedStudentIds.value.filter(id => !visibleIds.has(id));
}

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

async function saveAssignments() {
  if (!props.classRoom?.id) return;
  saving.value = true;

  try {
    const res = await api.post(`admin/classes/${props.classRoom.id}/assign-lokal-students`, {
      student_ids: selectedStudentIds.value,
    });
    const updatedCount = res?.data?.count ?? selectedStudentIds.value.length;
    toast.success(`Berhasil menyimpan ${updatedCount} siswa untuk Kelas ${props.classRoom.name}!`);
    emit('saved', { classId: props.classRoom.id, count: updatedCount });
    emit('close');
  } catch (err) {
    console.error('Failed to assign lokal students:', err);
    toast.error(err.response?.data?.message || 'Gagal menyimpan anggota kelas lokal');
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 8px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
