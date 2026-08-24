<template>
  <div class="space-y-6 font-inter">
    <!-- Header Card -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20 flex-shrink-0">
          <CalendarDays class="w-5 h-5 text-white" />
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Manajemen Tahun Ajaran</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Kelola daftar tahun ajaran, semester ganjil/genap, dan status aktif sistem.</p>
        </div>
      </div>

      <button
        @click="openCreateModal"
        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors flex items-center gap-2 shadow-md shadow-emerald-600/20 cursor-pointer"
      >
        <Plus class="w-4 h-4" />
        <span>Tambah Tahun Ajaran</span>
      </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <div v-if="loading" class="text-center py-16 text-slate-400 text-xs font-medium">
        <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-3"></div>
        Memuat data tahun ajaran...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">TAHUN AJARAN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">SEMESTER</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">STATUS AKTIF</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr
              v-for="(row, index) in academicYears"
              :key="row.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-6 py-4 text-xs font-bold text-slate-400">{{ index + 1 }}</td>
              <td class="px-6 py-4">
                <span class="text-sm font-extrabold text-slate-800 font-lexend">{{ row.year }}</span>
              </td>
              <td class="px-6 py-4">
                <span :class="[row.semester === 'odd' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-teal-50 text-teal-800 border-teal-200', 'px-3 py-1 rounded-xl text-xs font-bold border']">
                  {{ row.semester === 'odd' ? 'Ganjil' : 'Genap' }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <button
                  v-if="!row.is_active"
                  @click="activateYear(row)"
                  class="px-3 py-1 bg-slate-100 hover:bg-emerald-600 text-slate-600 hover:text-white text-xs font-bold rounded-xl border border-slate-200 hover:border-emerald-600 transition-all cursor-pointer shadow-2xs"
                >
                  Set Aktif
                </button>
                <span
                  v-else
                  class="px-3 py-1 bg-emerald-50 text-emerald-800 text-xs font-black rounded-xl border border-emerald-200 shadow-2xs inline-flex items-center gap-1.5"
                >
                  <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                  AKTIF SEKARANG
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button
                    @click="edit(row)"
                    title="Edit Tahun Ajaran"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>

                  <button
                    @click="remove(row)"
                    title="Hapus"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!academicYears.length">
              <td colspan="5" class="px-6 py-16 text-center text-slate-400 text-xs font-semibold">
                Belum ada data tahun ajaran. Klik tombol Tambah Tahun Ajaran di atas.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Clean Modern Modal Popup -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all">
        <!-- Modal Header -->
        <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <h2 class="text-lg font-black text-slate-800 font-lexend uppercase tracking-wider">
              {{ editing ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran' }}
            </h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Isi rincian tahun dan semester akademik di bawah ini.</p>
          </div>
          <button @click="showModal = false" class="w-9 h-9 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors border border-slate-100 shadow-sm cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <!-- Modal Form Body -->
        <form @submit.prevent="save" class="p-8 space-y-6">
          <!-- Tahun Ajaran Input -->
          <div class="space-y-1.5">
            <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wide">
              Tahun Ajaran <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.year"
              type="text"
              required
              placeholder="Contoh: 2026/2027"
              class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
            />
            <p class="text-[10px] text-slate-400 font-medium">Format: TAHUN/TAHUN (misal 2025/2026 atau 2026/2027)</p>
          </div>

          <!-- Semester Select -->
          <div class="space-y-1.5">
            <label class="block text-xs font-extrabold text-slate-600 uppercase tracking-wide">
              Semester <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.semester"
              required
              class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all cursor-pointer"
            >
              <option value="odd">Ganjil</option>
              <option value="even">Genap</option>
            </select>
          </div>

          <!-- Checkbox Set Aktif -->
          <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl">
            <label class="flex items-center gap-3 cursor-pointer">
              <input
                type="checkbox"
                v-model="form.is_active"
                class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-400 focus:ring-offset-0 bg-white border-slate-300 cursor-pointer"
              />
              <div>
                <span class="text-xs font-extrabold text-slate-800 block">Set Sebagai Tahun Ajaran Aktif</span>
                <span class="text-[10px] text-slate-400 font-medium">Jika diaktifkan, tahun ajaran lain akan dinonaktifkan otomatis.</span>
              </div>
            </label>
          </div>

          <!-- Action Footer Buttons -->
          <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <button
              type="button"
              @click="showModal = false"
              class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="submitting || !form.year"
              class="px-7 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
              <span>{{ submitting ? 'Menyimpan...' : 'Simpan Tahun Ajaran' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import {
  CalendarDays,
  Plus,
  Pencil,
  Trash2,
} from 'lucide-vue-next';

const toast = useToast();
const { confirm } = useConfirm();

const loading = ref(true);
const submitting = ref(false);
const showModal = ref(false);
const editing = ref(null);
const academicYears = ref([]);

const form = reactive({
  year: '',
  semester: 'odd',
  is_active: false,
});

const load = async () => {
  loading.value = true;
  try {
    const res = await api.get('admin/academic-years?per_page=999');
    const rawData = res.data?.data || res.data || [];
    academicYears.value = Array.isArray(rawData) ? rawData : rawData.data || [];
  } catch (err) {
    console.error('Failed to load academic years:', err);
    academicYears.value = [];
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  load();
});

const openCreateModal = () => {
  editing.value = null;
  form.year = '';
  form.semester = 'odd';
  form.is_active = false;
  showModal.value = true;
};

const edit = (row) => {
  editing.value = { ...row };
  form.year = row.year || '';
  form.semester = row.semester || 'odd';
  form.is_active = !!row.is_active;
  showModal.value = true;
};

const activateYear = async (row) => {
  try {
    await api.put(`admin/academic-years/${row.id}`, { is_active: true });
    toast.success(`Tahun ajaran ${row.year} diaktifkan!`);
    load();
  } catch (err) {
    toast.error('Gagal mengaktifkan tahun ajaran.');
  }
};

const remove = async (row) => {
  const isConfirmed = await confirm({
    title: 'Hapus Tahun Ajaran',
    message: `Apakah Anda yakin ingin menghapus tahun ajaran "${row.year}"?`,
    type: 'danger',
    confirmText: 'Ya, Hapus',
  });

  if (isConfirmed) {
    try {
      await api.del('admin/academic-years/' + row.id);
      toast.success('Tahun ajaran berhasil dihapus.');
      load();
    } catch {
      toast.error('Gagal menghapus tahun ajaran.');
    }
  }
};

const save = async () => {
  if (!form.year?.trim()) return;

  submitting.value = true;
  const isEdit = !!editing.value;
  const url = isEdit ? `admin/academic-years/${editing.value.id}` : 'admin/academic-years';
  const fn = isEdit ? api.put : api.post;

  try {
    await fn(url, {
      year: form.year,
      semester: form.semester,
      is_active: form.is_active,
    });
    toast.success(isEdit ? 'Tahun ajaran berhasil diperbarui.' : 'Tahun ajaran baru berhasil ditambahkan.');
    showModal.value = false;
    load();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan tahun ajaran.');
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
