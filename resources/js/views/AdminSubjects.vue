<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-[#10B981] rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend tracking-wide uppercase">Mata Pelajaran</h1>
          <p class="text-xs text-slate-400 font-medium">{{ subjects.length }} mata pelajaran terdaftar</p>
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
          placeholder="Cari nama atau kode mapel..."
          class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
        />
        <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
      </div>

      <!-- Add Button -->
      <button
        @click="showForm = true; editing = null"
        class="flex items-center gap-2 px-5 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl text-sm transition-colors shadow-sm flex-shrink-0"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/>
        </svg>
        Tambah Mapel
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <!-- Loading -->
      <div v-if="loading" class="text-center py-16">
        <div class="inline-flex w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p class="text-sm font-semibold text-slate-400">Memuat data mata pelajaran...</p>
      </div>

      <!-- Table Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA MATA PELAJARAN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">KODE</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">DESKRIPSI</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr
              v-for="(row, index) in filteredSubjects"
              :key="row.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-6 py-4 text-sm font-bold text-slate-400">{{ index + 1 }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <!-- Icon Badge -->
                  <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                  </div>
                  <span class="text-sm font-bold text-slate-800">{{ row.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-[11px] font-bold rounded-lg font-mono tracking-wider">
                  {{ row.code || '-' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-500 font-medium max-w-xs truncate">{{ row.description || '-' }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <!-- Edit -->
                  <button
                    @click="edit(row)"
                    title="Edit Mata Pelajaran"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>
                  <!-- Delete -->
                  <button
                    @click="remove(row)"
                    title="Hapus Mata Pelajaran"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="!filteredSubjects.length">
              <td colspan="5" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                  <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-2">
                    <BookOpen class="w-8 h-8 text-slate-300" />
                  </div>
                  <p class="font-bold text-slate-600">Tidak ada data mata pelajaran</p>
                  <p class="text-xs text-slate-400">Belum ada mata pelajaran yang ditambahkan.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form -->
    <SubjectForm
      v-if="showForm"
      :title="editing ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'"
      :model="editing || {}"
      @close="showForm = false"
      @save="save"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import SubjectForm from '../components/SubjectForm.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import { Pencil, Trash2, BookOpen } from 'lucide-vue-next';

const toast = useToast();
const { confirm } = useConfirm();

const loading = ref(true);
const showForm = ref(false);
const editing = ref(null);
const subjects = ref([]);
const searchQuery = ref('');

const filteredSubjects = computed(() => {
  if (!searchQuery.value) return subjects.value;
  const q = searchQuery.value.toLowerCase();
  return subjects.value.filter(s =>
    s.name?.toLowerCase().includes(q) || s.code?.toLowerCase().includes(q)
  );
});

onMounted(load);

async function load() {
  loading.value = true;
  try {
    const res = await api.get('admin/subjects');
    subjects.value = res.data?.data || res.data || [];
  } catch {
    subjects.value = [];
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
    title: 'Hapus Mata Pelajaran',
    message: `Apakah Anda yakin ingin menghapus mata pelajaran "${row.name}"?`,
    type: 'danger',
    confirmText: 'Ya, Hapus',
  });

  if (isConfirmed) {
    try {
      await api.del('admin/subjects/' + row.id);
      toast.success('Mata pelajaran berhasil dihapus');
      load();
    } catch {
      toast.error('Gagal menghapus mata pelajaran');
    }
  }
}

function save(model) {
  const payload = { ...model };
  delete payload.id;
  const url = editing.value ? 'admin/subjects/' + editing.value.id : 'admin/subjects';
  const fn = editing.value ? api.put : api.post;
  fn(url, payload).then(() => {
    toast.success(editing.value ? 'Mata pelajaran berhasil diperbarui' : 'Mata pelajaran berhasil ditambahkan');
    showForm.value = false;
    editing.value = null;
    load();
  }).catch(() => {
    toast.error('Gagal menyimpan mata pelajaran');
  });
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
