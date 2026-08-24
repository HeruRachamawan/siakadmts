<template>
  <div class="space-y-6 font-inter">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-11 h-11 bg-emerald-600 text-white rounded-xl border border-emerald-500 flex items-center justify-center flex-shrink-0 shadow-md shadow-emerald-600/20">
          <Layers class="w-5 h-5 text-white" />
        </div>
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-lexend">{{ title }}</h1>
          <p class="text-xs text-slate-500 font-normal mt-0.5">Kelola data {{ title.toLowerCase() }} sekolah di sini.</p>
        </div>
      </div>
      
      <button 
        @click="openCreateModal" 
        class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-600/20 cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Data</span>
      </button>
    </div>

    <!-- Data Table Card (Shadcn Table Style) -->
    <div class="shadcn-card overflow-hidden">
      <div v-if="loading" class="text-center py-16 flex flex-col items-center justify-center space-y-3">
        <div class="animate-spin h-7 w-7 border-2 border-emerald-600 border-t-transparent rounded-full"></div>
        <span class="text-xs text-slate-500 font-medium">Memuat data {{ title.toLowerCase() }}...</span>
      </div>

      <div v-else-if="data.length === 0" class="text-center py-16 space-y-3">
        <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center mx-auto border border-slate-200/60">
          <FolderOpen class="w-6 h-6" />
        </div>
        <p class="text-xs text-slate-500 font-normal">Belum ada data {{ title.toLowerCase() }}.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/75 border-b border-slate-200/80">
              <th v-for="col in columns" :key="col.field" class="py-3.5 px-5 font-bold text-slate-600 text-[11px] uppercase tracking-wider whitespace-nowrap">{{ col.label }}</th>
              <th class="py-3.5 px-5 font-bold text-slate-600 text-[11px] text-right uppercase tracking-wider whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="row in data" :key="row.id" class="hover:bg-slate-50/60 transition-colors group">
              <td v-for="col in columns" :key="col.field" class="py-3.5 px-5 text-xs text-slate-700">
                <!-- Image column -->
                <span v-if="col.field === 'image' || col.field === 'photo_url' || col.field === 'foto'" class="inline-block w-12 h-12 rounded-xl bg-slate-100 overflow-hidden border border-slate-200 shadow-2xs">
                  <img v-if="row[col.field]" :src="getImageUrl(row[col.field])" class="w-full h-full object-cover" :alt="title" />
                  <div v-else class="w-full h-full flex items-center justify-center text-slate-300 text-[10px]">No Pic</div>
                </span>
                
                <!-- Status column -->
                <span v-else-if="col.field === 'status'">
                  <span :class="row[col.field] === 'published' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border inline-block">
                    {{ row[col.field] }}
                  </span>
                </span>

                <!-- Long text / Description column -->
                <span v-else-if="col.field === 'description' || col.field === 'content'" class="line-clamp-2 max-w-xs text-slate-600 leading-relaxed">
                  {{ getNested(row, col.field) }}
                </span>

                <!-- Default text column -->
                <span v-else class="font-medium text-slate-800">{{ getNested(row, col.field) || '-' }}</span>
              </td>
              <td class="py-3.5 px-5">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    @click="edit(row)" 
                    class="p-2 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-xl border border-emerald-200 transition-all cursor-pointer shadow-2xs" 
                    title="Edit Data"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>
                  <button 
                    @click="remove(row)" 
                    class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-200 transition-all cursor-pointer shadow-2xs" 
                    title="Hapus Data"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <CrudForm
      v-if="showForm"
      :fields="activeFormFields"
      :title="editing ? 'Edit ' + title : 'Tambah ' + title"
      :model="editing || {}"
      :saving="saving"
      @close="showForm = false"
      @save="save"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import CrudForm from './CrudForm.vue';
import {
  Layers,
  Plus,
  Pencil,
  Trash2,
  FolderOpen
} from 'lucide-vue-next';

const props = defineProps({
  title: { type: String, required: true },
  endpoint: { type: String, default: '' },
  resource: { type: String, default: '' },
  columns: { type: Array, required: true },
  formFields: { type: Array, default: null },
  fields: { type: Array, default: null },
});

const toast = useToast();
const confirm = useConfirm();
const data = ref([]);
const loading = ref(false);
const saving = ref(false);
const showForm = ref(false);
const editing = ref(null);

// Resolve API Endpoint dynamically whether passed as endpoint or resource
const apiEndpoint = computed(() => {
  let ep = props.endpoint || props.resource || '';
  if (!ep) return '';
  // Normalize leading slash
  if (ep.startsWith('/')) ep = ep.substring(1);
  // Ensure admin prefix if it's an admin resource and missing it
  if (!ep.startsWith('admin/') && !ep.startsWith('auth/') && !ep.startsWith('teacher/') && !ep.startsWith('student/') && !ep.startsWith('public/')) {
    ep = 'admin/' + ep;
  }
  return ep;
});

// Resolve Form Fields dynamically whether passed as formFields or fields
const activeFormFields = computed(() => {
  return props.formFields || props.fields || [];
});

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path;
}

async function load() {
  if (!apiEndpoint.value) return;
  loading.value = true;
  try {
    const res = await api.get(apiEndpoint.value);
    if (Array.isArray(res.data)) {
      data.value = res.data;
    } else if (res.data && Array.isArray(res.data.data)) {
      data.value = res.data.data;
    } else if (res.data && typeof res.data === 'object') {
      const foundArray = Object.values(res.data).find(v => Array.isArray(v));
      data.value = foundArray || [];
    } else {
      data.value = [];
    }
  } catch (err) {
    console.error('Failed to load CRUD data:', err);
    toast.error('Gagal memuat data ' + props.title);
  } finally {
    loading.value = false;
  }
}

function getNested(obj, path) {
  if (!obj || !path) return '';
  return path.split('.').reduce((o, i) => (o ? o[i] : null), obj);
}

function openCreateModal() {
  editing.value = null;
  showForm.value = true;
}

function edit(item) {
  editing.value = { ...item };
  showForm.value = true;
}

async function remove(item) {
  const ok = await confirm.danger({
    title: 'Hapus Data ' + props.title,
    message: `Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.`,
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal'
  });
  if (!ok) return;

  try {
    await api.delete(`${apiEndpoint.value}/${item.id}`);
    toast.success('Data berhasil dihapus');
    load();
  } catch (err) {
    toast.error('Gagal menghapus data');
  }
}

async function save(formData, isFormData = false) {
  saving.value = true;
  try {
    const targetUrl = apiEndpoint.value;
    if (editing.value?.id) {
      if (isFormData) {
        formData.append('_method', 'PUT');
        await api.post(`${targetUrl}/${editing.value.id}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
      } else {
        await api.put(`${targetUrl}/${editing.value.id}`, formData);
      }
      toast.success('Data berhasil diperbarui');
    } else {
      if (isFormData) {
        await api.post(targetUrl, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
      } else {
        await api.post(targetUrl, formData);
      }
      toast.success('Data berhasil ditambahkan');
    }
    showForm.value = false;
    editing.value = null;
    load();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data');
  } finally {
    saving.value = false;
  }
}

watch(() => apiEndpoint.value, () => {
  load();
});

onMounted(load);
</script>
