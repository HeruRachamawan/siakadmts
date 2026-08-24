<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend tracking-wide uppercase">Data Siswa Binaan</h1>
          <p class="text-xs text-slate-400 font-medium">{{ totalRecords }} siswa ditemukan di kelas binaan Anda</p>
        </div>
      </div>
    </div>

    <!-- Action Bar (Filters + Search) -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
      <!-- Per Page -->
      <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-600 shadow-sm">
        <select
          v-model.number="selectedPerPage"
          class="bg-transparent border-none p-0 text-slate-700 font-semibold focus:ring-0 cursor-pointer text-sm pr-1"
          @change="onPerPageChange"
        >
          <option :value="10">10 Baris</option>
          <option :value="20">20 Baris</option>
          <option :value="50">50 Baris</option>
          <option :value="-1">Semua</option>
        </select>
        <svg class="w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
      </div>

      <!-- Class Filter -->
      <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-600 shadow-sm">
        <select
          v-model="selectedClass"
          class="bg-transparent border-none p-0 text-slate-700 font-semibold focus:ring-0 cursor-pointer text-sm pr-1"
          @change="load"
        >
          <option value="">Semua Kelas Binaan</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
        </select>
        <svg class="w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
      </div>

      <!-- Search Box -->
      <div class="relative flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama, NISN, atau NIS siswa..."
          class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium"
          @input="onSearchInput"
        />
        <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <!-- Loading Skeleton -->
      <SkeletonTable v-if="loading" :columns="6" :rows="5" :avatar="true" class="py-4" />

      <!-- Table Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA SISWA</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NISN / NIS</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">JENIS KELAMIN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">KELAS</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(row, index) in students" :key="row.id" class="hover:bg-slate-50/70 transition-colors">
              <!-- NO -->
              <td class="px-6 py-4 text-sm font-bold text-slate-400">
                {{ selectedPerPage === -1 ? index + 1 : (currentPage - 1) * selectedPerPage + index + 1 }}
              </td>

              <!-- Nama + Avatar -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-200 flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm border border-slate-200">
                    <img v-if="row.photo_url" :src="row.photo_url" alt="Photo" class="w-full h-full object-cover" />
                    <div v-else :class="getAvatarColor(row.gender)" class="w-full h-full flex items-center justify-center">
                      {{ getInitials(row.full_name) }}
                    </div>
                  </div>
                  <div>
                    <span class="text-sm font-bold text-slate-800 block">{{ row.full_name || '-' }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">{{ row.birth_place || '-' }}</span>
                  </div>
                </div>
              </td>

              <!-- NISN / NIS -->
              <td class="px-6 py-4">
                <div class="text-sm font-bold text-slate-700 font-mono tracking-tight">
                  {{ row.nisn || '-' }}
                </div>
                <div class="text-xs text-slate-400 font-medium">
                  NIS: {{ row.nis || '-' }}
                </div>
              </td>

              <!-- Jenis Kelamin -->
              <td class="px-6 py-4">
                <span
                  v-if="row.gender === 'L'"
                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-600 border border-blue-100"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                  Laki-laki
                </span>
                <span
                  v-else-if="row.gender === 'P'"
                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-pink-50 text-pink-600 border border-pink-100"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                  Perempuan
                </span>
                <span v-else class="text-slate-400 text-xs font-medium">-</span>
              </td>

              <!-- Kelas -->
              <td class="px-6 py-4">
                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-100/60 inline-block">
                  {{ row.classRoom?.name || row.class_name || '-' }}
                </span>
              </td>

              <!-- Action Buttons -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <!-- Detail (Eye) -->
                  <button
                    @click="viewDetail(row)"
                    title="Lihat Detail Siswa"
                    class="w-8 h-8 rounded-full flex items-center justify-center bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm cursor-pointer"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="!students.length">
              <td colspan="6" class="px-6 py-16 text-center">
                <div v-if="!classes.length" class="flex flex-col items-center gap-2 text-slate-400">
                  <div class="w-16 h-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mb-2 border border-amber-100">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                  </div>
                  <p class="text-sm font-bold text-slate-700">Anda Belum Ditugaskan Sebagai Wali Kelas</p>
                  <p class="text-xs text-slate-500 max-w-sm">Data siswa hanya dapat diakses dan dikelola oleh Guru yang telah ditetapkan sebagai Wali Kelas oleh Admin.</p>
                </div>
                <div v-else class="flex flex-col items-center gap-2 text-slate-400">
                  <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                  </div>
                  <p class="text-sm font-semibold">Tidak ada data siswa ditemukan.</p>
                  <p class="text-xs">Coba ganti kata kunci pencarian atau pilih kelas binaan lainnya.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="!loading && totalPages > 1" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
        <span class="text-xs font-semibold text-slate-500">
          Halaman {{ currentPage }} dari {{ totalPages }}
        </span>

        <div class="flex items-center gap-1">
          <button
            @click="currentPage--; load()"
            :disabled="currentPage === 1"
            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors disabled:opacity-40"
          >
            &laquo; Prev
          </button>
          <button
            @click="currentPage++; load()"
            :disabled="currentPage >= totalPages"
            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition-colors disabled:opacity-40"
          >
            Next &raquo;
          </button>
        </div>
      </div>
    </div>

    <!-- Student Detail Modal -->
    <StudentDetailModal
      v-if="showDetail"
      :student="detailStudent"
      @close="showDetail = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { api } from '../api';
import StudentDetailModal from '../components/StudentDetailModal.vue';
import SkeletonTable from '../components/SkeletonTable.vue';

const route = useRoute();

const loading = ref(true);
const showDetail = ref(false);
const detailStudent = ref(null);
const students = ref([]);
const classes = ref([]);

const searchQuery = ref('');
const selectedClass = ref(route.query.class_id || '');
const currentPage = ref(1);
const selectedPerPage = ref(10);
const totalPages = ref(1);
const totalRecords = ref(0);

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function getAvatarColor(gender) {
  if (gender === 'L') return 'bg-blue-400';
  if (gender === 'P') return 'bg-pink-400';
  return 'bg-slate-400';
}

onMounted(async () => {
  await fetchClasses();
  await load();
});

async function fetchClasses() {
  try {
    const res = await api.get('teacher/classes');
    classes.value = res.data || [];
  } catch {
    classes.value = [];
  }
}

async function load() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (selectedPerPage.value !== -1) {
      params.set('page', currentPage.value);
      params.set('per_page', selectedPerPage.value);
    } else {
      params.set('per_page', 9999);
    }

    if (selectedClass.value) params.set('class_id', selectedClass.value);
    if (searchQuery.value) params.set('search', searchQuery.value);

    const res = await api.get(`teacher/students?${params.toString()}`);
    const dataPayload = res.data || {};
    
    let rawList = [];
    if (Array.isArray(dataPayload)) {
      rawList = dataPayload;
    } else if (Array.isArray(dataPayload.data)) {
      rawList = dataPayload.data;
    }

    students.value = rawList;

    const meta = dataPayload.meta;
    totalPages.value = meta?.last_page || 1;
    totalRecords.value = meta?.total || students.value.length;
  } catch (err) {
    console.error('Error loading teacher students:', err);
    students.value = [];
    totalRecords.value = 0;
  } finally {
    loading.value = false;
  }
}

let searchTimeout = null;
function onSearchInput() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    load();
  }, 400);
}

function onPerPageChange() {
  currentPage.value = 1;
  load();
}

function viewDetail(row) {
  detailStudent.value = row;
  showDetail.value = true;
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
