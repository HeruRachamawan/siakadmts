<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend tracking-wide uppercase">Manajemen Kelas</h1>
          <p class="text-xs text-slate-400 font-medium">{{ classes.length }} kelas terdaftar</p>
        </div>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <!-- Active Academic Year Badge & Bulk Update -->
        <div v-if="activeYear" class="flex items-center gap-2 bg-emerald-50 border border-emerald-200/80 px-3.5 py-2 rounded-xl">
          <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
          <span class="text-xs font-bold text-emerald-900">
            T.A. Aktif: <strong>{{ activeYear.year }} ({{ activeYear.semester === 'odd' ? 'Ganjil' : 'Genap' }})</strong>
          </span>
          <button
            @click="syncClassesToActiveYear"
            title="Update Tahun Ajaran seluruh kelas ke Tahun Ajaran Aktif saat ini"
            class="ml-2 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black rounded-lg transition-colors cursor-pointer shadow-sm shadow-emerald-600/20"
          >
            Update Semua Kelas
          </button>
        </div>
      </div>
    </div>

    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
      <!-- Search -->
      <div class="relative flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari nama kelas atau wali kelas..."
          class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
        />
        <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
      </div>

      <!-- Add Button -->
      <button
        @click="showForm = true; editing = null"
        class="flex items-center gap-2 px-5 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm flex-shrink-0 cursor-pointer"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
        </svg>
        Tambah Kelas
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <!-- Loading -->
      <div v-if="loading" class="text-center py-16">
        <div class="inline-flex w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p class="text-sm font-semibold text-slate-400">Memuat data kelas...</p>
      </div>

      <!-- Table Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA KELAS</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">TINGKAT</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">WALI KELAS</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">TAHUN AJARAN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr
              v-for="(row, index) in filteredClasses"
              :key="row.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-6 py-4 text-sm font-bold text-slate-400">{{ index + 1 }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <!-- Class Icon Badge -->
                  <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100/80 flex items-center justify-center flex-shrink-0 text-emerald-700 font-black text-sm">
                    {{ (row.name || '?').charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-800">{{ row.name || '-' }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="px-3 py-1.5 bg-teal-50 border border-teal-100/60 text-teal-800 text-[11px] font-bold rounded-lg inline-block">
                  Kelas {{ row.grade_level || '-' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2.5" v-if="row.homeroom_teacher?.full_name || row.homeroomTeacher?.full_name">
                  <div class="w-8 h-8 rounded-full overflow-hidden bg-emerald-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm border border-emerald-200">
                    <img
                      v-if="(row.homeroom_teacher?.photo_url || row.homeroomTeacher?.photo_url) && typeof (row.homeroom_teacher?.photo_url || row.homeroomTeacher?.photo_url) === 'string' && (row.homeroom_teacher?.photo_url || row.homeroomTeacher?.photo_url).length > 5"
                      :src="row.homeroom_teacher?.photo_url || row.homeroomTeacher?.photo_url"
                      alt="Photo"
                      class="w-full h-full object-cover"
                    />
                    <span v-else>
                      {{ (row.homeroom_teacher?.full_name || row.homeroomTeacher?.full_name || '').split(' ').slice(0,2).map(n=>n[0]).join('').toUpperCase() }}
                    </span>
                  </div>
                  <span class="text-sm font-bold text-slate-700">
                    {{ row.homeroom_teacher?.full_name || row.homeroomTeacher?.full_name }}
                  </span>
                </div>
                <span v-else class="text-sm text-slate-400 italic">Belum ditentukan</span>
              </td>
              <td class="px-6 py-4">
                <span class="px-3 py-1.5 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-lg">
                  {{ row.academic_year?.year || row.academicYear?.year || '-' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <!-- Edit -->
                  <button
                    @click="edit(row)"
                    title="Edit Kelas"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>
                  <!-- Delete -->
                  <button
                    @click="remove(row)"
                    title="Hapus Kelas"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="!filteredClasses.length">
              <td colspan="6" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                  <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                  </div>
                  <p class="text-sm font-semibold">Tidak ada data kelas ditemukan.</p>
                  <p class="text-xs">Tambah kelas baru dengan tombol di atas.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <ClassForm
      v-if="showForm"
      :title="editing ? 'Edit Kelas' : 'Tambah Kelas'"
      :model="editing || {}"
      @close="showForm = false"
      @save="save"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import ClassForm from '../components/ClassForm.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import { Pencil, Trash2 } from 'lucide-vue-next';

const toast = useToast();
const { confirm } = useConfirm();

const loading = ref(true);
const showForm = ref(false);
const editing = ref(null);
const classes = ref([]);
const searchQuery = ref('');

const filteredClasses = computed(() => {
  if (!searchQuery.value) return classes.value;
  const q = searchQuery.value.toLowerCase();
  return classes.value.filter(c =>
    c.name?.toLowerCase().includes(q) ||
    c.homeroom_teacher?.full_name?.toLowerCase().includes(q) ||
    c.homeroomTeacher?.full_name?.toLowerCase().includes(q)
  );
});

const activeYear = ref(null);

onMounted(() => {
  loadActiveYear();
  load();
});

async function loadActiveYear() {
  try {
    const res = await api.get('settings');
    activeYear.value = res.data?.active_academic_year || null;
  } catch {}
}

async function syncClassesToActiveYear() {
  if (!activeYear.value) return;

  const isConfirmed = await confirm({
    title: 'Update Tahun Ajaran Seluruh Kelas',
    message: `Apakah Anda yakin ingin memperbarui Tahun Ajaran seluruh kelas menjadi "${activeYear.value.year} (${activeYear.value.semester === 'odd' ? 'Ganjil' : 'Genap'})"?`,
    type: 'warning',
    confirmText: 'Ya, Update Sekarang',
  });

  if (!isConfirmed) return;

  loading.value = true;
  try {
    const updatePromises = classes.value.map(c => 
      api.put(`admin/classes/${c.id}`, { academic_year_id: activeYear.value.id })
    );
    await Promise.all(updatePromises);
    toast.success('Tahun ajaran seluruh kelas berhasil diperbarui!');
    load();
  } catch {
    toast.error('Gagal memperbarui tahun ajaran kelas.');
  } finally {
    loading.value = false;
  }
}

async function load() {
  loading.value = true;
  try {
    const res = await api.get('admin/classes');
    classes.value = res.data?.data || res.data || [];
  } catch {
    classes.value = [];
  } finally {
    loading.value = false;
  }
}

function edit(row) {
  editing.value = { ...row };
  showForm.value = true;
}

async function remove(row) {
  const isConfirmed = await confirm({
    title: 'Hapus Data Kelas',
    message: `Apakah Anda yakin ingin menghapus kelas "${row.name}"?`,
    type: 'danger',
    confirmText: 'Ya, Hapus',
  });

  if (isConfirmed) {
    try {
      await api.del('admin/classes/' + row.id);
      toast.success('Kelas berhasil dihapus');
      load();
    } catch {
      toast.error('Gagal menghapus kelas');
    }
  }
}

function save(model) {
  const payload = { ...model };
  delete payload.id;
  const url = editing.value ? 'admin/classes/' + editing.value.id : 'admin/classes';
  const fn = editing.value ? api.put : api.post;
  fn(url, payload).then(() => {
    toast.success(editing.value ? 'Kelas berhasil diperbarui' : 'Kelas berhasil ditambahkan');
    showForm.value = false;
    editing.value = null;
    load();
  }).catch(() => {
    toast.error('Gagal menyimpan kelas');
  });
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
