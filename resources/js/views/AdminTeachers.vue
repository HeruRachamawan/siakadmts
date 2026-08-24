<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend tracking-wide uppercase">Data Guru</h1>
          <p class="text-xs text-slate-400 font-medium">{{ totalRecords }} data ditemukan</p>
        </div>
      </div>
    </div>

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
      <!-- Per Page -->
      <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-sm">
        <select v-model.number="selectedPerPage" class="bg-transparent border-none p-0 text-slate-700 font-semibold focus:ring-0 cursor-pointer text-sm pr-1" @change="onPerPageChange">
          <option :value="10">10 Baris</option>
          <option :value="20">20 Baris</option>
          <option :value="50">50 Baris</option>
          <option :value="-1">Semua</option>
        </select>
        <svg class="w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
      </div>

      <!-- Search -->
      <div class="relative flex-1">
        <input v-model="searchQuery" type="text" placeholder="Cari nama atau NIP..." class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all" @input="onSearchInput" />
        <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <button
          @click="exportExcelFile('teachers')"
          :disabled="exportingExcel"
          class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 disabled:opacity-50 transition-colors shadow-sm cursor-pointer"
        >
          <svg v-if="exportingExcel" class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
          <svg v-else class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span>{{ exportingExcel ? 'Mengekspor...' : 'Export Excel' }}</span>
        </button>
        <button
          @click="showImportModal = true"
          class="flex items-center gap-2 px-4 py-2.5 bg-teal-50 border border-teal-200 text-teal-700 font-bold rounded-xl text-xs hover:bg-teal-100 transition-colors shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
          <span>Import Excel</span>
        </button>
        <button @click="showForm = true; editing = null" class="flex items-center gap-2 px-5 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors shadow-sm cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"></path></svg>
          <span>Tambah Guru</span>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <div v-if="loading" class="text-center py-16">
        <div class="inline-flex w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p class="text-sm font-semibold text-slate-400">Memuat data guru...</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA GURU</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NIP</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">MAPEL & KELAS DIAMPU</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(row, index) in teachers" :key="row.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 text-sm font-bold text-slate-400">
                {{ selectedPerPage === -1 ? index + 1 : (currentPage - 1) * selectedPerPage + index + 1 }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <!-- Avatar / Photo -->
                  <div class="w-10 h-10 rounded-full overflow-hidden bg-emerald-100 flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm border border-emerald-100">
                    <img v-if="row.photo_url" :src="row.photo_url" alt="Photo" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full bg-emerald-600 flex items-center justify-center">
                      {{ (row.full_name || row.user?.name || '?').split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase() }}
                    </div>
                  </div>
                  <div>
                    <span class="text-sm font-bold text-slate-800 block">{{ row.full_name || row.user?.name || '-' }}</span>
                    <span v-if="row.position" class="inline-block mt-0.5 px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-md border border-slate-200">
                      {{ row.position }}
                    </span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm font-medium text-slate-500 font-mono">{{ row.nip || '-' }}</td>
              <td class="px-6 py-4">
                <div v-if="getSubjectClassGrouped(row).length > 0" class="flex flex-wrap gap-2">
                  <div v-for="grp in getSubjectClassGrouped(row)" :key="grp.subject_name" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-100 text-xs">
                    <span class="font-black text-emerald-800">{{ grp.subject_name }}</span>
                    <span class="font-bold text-teal-700 bg-white px-1.5 py-0.5 rounded-md border border-teal-100 text-[10px]">{{ grp.class_names }}</span>
                  </div>
                </div>
                <span v-else class="text-slate-400 text-xs italic">- Belum ada mapel -</span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-1.5">
                  <button @click="viewDetail(row)" title="Lihat Detail" class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer">
                    <Eye class="w-3.5 h-3.5" />
                  </button>
                  <button @click="resetPassword(row)" title="Reset Password" class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer">
                    <Key class="w-3.5 h-3.5" />
                  </button>
                  <button @click="edit(row)" title="Edit" class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer">
                    <Pencil class="w-3.5 h-3.5" />
                  </button>
                  <button @click="remove(row)" title="Hapus" class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!teachers.length">
              <td colspan="6" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                  <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                  </div>
                  <p class="text-sm font-semibold">Tidak ada data guru ditemukan.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1 && selectedPerPage !== -1" class="flex items-center justify-between px-2">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Halaman {{ currentPage }} dari {{ totalPages }}</p>
      <nav class="flex items-center gap-1">
        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1 || loading" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 disabled:opacity-30 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <template v-for="page in visiblePages" :key="page">
          <button v-if="page !== '...'" @click="goToPage(page)" :class="currentPage === page ? 'bg-[#111827] text-white font-bold border-[#111827]' : 'text-slate-500 hover:bg-white hover:border-slate-200 font-medium'" class="w-9 h-9 flex items-center justify-center rounded-xl text-sm transition-all border border-transparent">{{ page }}</button>
          <span v-else class="w-9 h-9 flex items-center justify-center text-slate-400 text-sm">...</span>
        </template>
        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages || loading" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 disabled:opacity-30 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      </nav>
    </div>

    <!-- Modal Form -->
    <TeacherForm
      v-if="showForm"
      :title="editing ? 'Edit Guru' : 'Tambah Guru'"
      :model="editing || {}"
      @close="showForm = false"
      @save="save"
    />

    <!-- Detail Modal -->
    <TeacherDetailModal
      v-if="showDetail"
      :teacher="detailTeacher"
      @close="showDetail = false"
      @reset-password="(t) => { showDetail = false; resetPassword(t); }"
      @edit="(t) => { showDetail = false; edit(t); }"
    />

    <!-- Excel Import Modal -->
    <ExcelImportModal
      :show="showImportModal"
      type="teachers"
      title="Guru"
      @close="showImportModal = false"
      @success="load"
    />
  </div>
</template>


<script setup>
import { ref, onMounted, computed } from 'vue';
import { api } from '../api';
import TeacherForm from '../components/TeacherForm.vue';
import TeacherDetailModal from '../components/TeacherDetailModal.vue';
import ExcelImportModal from '../components/ExcelImportModal.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import { Eye, Key, Pencil, Trash2 } from 'lucide-vue-next';

const { success, error: showError } = useToast();
const { confirm } = useConfirm();

const loading = ref(true);
const exportingExcel = ref(false);

const exportExcelFile = async (type) => {
  exportingExcel.value = true;
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`/api/admin/excel/export/${type}`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json',
      },
    });
    if (!response.ok) throw new Error('Gagal mengekspor file Excel');
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Data_Export_${type}_YASPIN.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    success('Data Excel berhasil diekspor!');
  } catch (err) {
    console.error(err);
    showError('Gagal mengekspor file Excel');
  } finally {
    exportingExcel.value = false;
  }
};
const showForm = ref(false);
const showDetail = ref(false);
const showImportModal = ref(false);
const editing = ref(null);
const detailTeacher = ref(null);
const teachers = ref([]);

const searchQuery = ref('');
const currentPage = ref(1);
const selectedPerPage = ref(10); // default 10 baris
const totalPages = ref(1);
const totalRecords = ref(0);

onMounted(load);

async function load() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    if (selectedPerPage.value !== -1) {
      params.set('page', currentPage.value);
      params.set('per_page', selectedPerPage.value);
    } else {
      params.set('per_page', 9999); // ambil semua
    }

    if (searchQuery.value) params.append('search', searchQuery.value);

    const res = await api.get(`admin/teachers?${params.toString()}`);
    const rawData = res.data?.data || res.data || [];
    teachers.value = Array.isArray(rawData) ? rawData : rawData.data || [];

    const meta = res.data?.meta;
    totalPages.value = meta?.last_page || 1;
    totalRecords.value = meta?.total || teachers.value.length;
    currentPage.value = meta?.current_page || 1;
  } catch {
    teachers.value = [];
    totalPages.value = 1;
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

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  load();
}

const visiblePages = computed(() => {
  if (totalPages.value <= 1) return [];

  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, currentPage.value + 2);

  if (start > 1) pages.push('...');
  for (let i = start; i <= end; i++) pages.push(i);
  if (end < totalPages.value) pages.push('...');

  return pages;
});

function getSubjectClassGrouped(row) {
  const assignments = row.subject_classes || row.subjectClasses;
  if (assignments && assignments.length > 0) {
    const grouped = {};
    assignments.forEach(sc => {
      const subName = sc.subject?.name || 'Mapel';
      const clsName = sc.class_room?.name || sc.classRoom?.name || '';
      if (!grouped[subName]) grouped[subName] = [];
      if (clsName && !grouped[subName].includes(clsName)) {
        grouped[subName].push(clsName);
      }
    });
    return Object.keys(grouped).map(subName => ({
      subject_name: subName,
      class_names: grouped[subName].length > 0 ? grouped[subName].join(', ') : 'Semua Kelas'
    }));
  }

  if (row.subjects && row.subjects.length > 0) {
    const clsNames = (row.teaching_classes || row.teachingClasses || []).map(c => c.name).join(', ') || 'Semua Kelas';
    return row.subjects.map(s => ({
      subject_name: s.name,
      class_names: clsNames
    }));
  }

  return [];
}

function viewDetail(row) {
  detailTeacher.value = row;
  showDetail.value = true;
}

function edit(row) {
  editing.value = { ...row };
  showForm.value = true;
}

async function remove(row) {
  const teacherName = row.full_name || row.user?.name || 'Guru';
  const isConfirmed = await confirm({
    title: 'Hapus Data Guru',
    message: `Apakah Anda yakin ingin menghapus data guru ${teacherName}?`,
    type: 'danger',
    confirmText: 'Ya, Hapus',
  });

  if (isConfirmed) {
    try {
      await api.del('admin/teachers/' + row.id);
      success('Data guru berhasil dihapus.');
      load();
    } catch (err) {
      showError(err.response?.data?.message || 'Gagal menghapus data guru.');
    }
  }
}

async function resetPassword(row) {
  const teacherName = row.full_name || row.user?.name || 'Guru';
  const isConfirmed = await confirm({
    title: 'Reset Password Guru',
    message: `Apakah Anda yakin ingin mereset password ${teacherName} menjadi NUPTK?`,
    type: 'warning',
    confirmText: 'Ya, Reset Password',
  });

  if (!isConfirmed) return;
  try {
    await api.post(`admin/teachers/${row.id}/reset-credentials`);
    success('Password berhasil direset ke NUPTK');
    load();
  } catch (err) {
    showError(err.response?.data?.message || 'Gagal mereset password');
  }
}

function save({ payload, isFormData }) {
  const isEditing = !!editing.value;
  const url = isEditing ? `admin/teachers/${editing.value.id}` : 'admin/teachers';
  const fn = isFormData
    ? (isEditing ? api.putForm : api.postForm)
    : (isEditing ? api.put : api.post);

  fn(url, payload).then(() => {
    showForm.value = false;
    editing.value = null;
    load();
    success(isEditing ? 'Data guru berhasil diperbarui.' : 'Guru baru berhasil ditambahkan.');
  }).catch((err) => {
    console.error('Save failed:', err);
    showError(err.response?.data?.message || (isEditing ? 'Gagal memperbarui data guru.' : 'Gagal menyimpan guru baru.'));
  });
}
</script>
