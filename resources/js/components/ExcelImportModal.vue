<template>
  <Transition name="modal-fade">
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center p-4 font-inter">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="$emit('close')"></div>
      
      <div class="relative bg-white rounded-3xl p-6 shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col space-y-4 border border-slate-100 animate-slide-up">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center font-bold">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
              <h3 class="font-lexend font-black text-slate-800 text-lg uppercase tracking-wider">Import Excel Masal ({{ title }})</h3>
              <p class="text-xs text-slate-500 font-medium">Unggah file .xlsx untuk memasukkan data secara masal ke sistem</p>
            </div>
          </div>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>

        <!-- Step 1: Upload File & Download Template -->
        <div v-if="!previewData" class="space-y-4 py-4">
          <div class="flex items-center justify-between bg-purple-50/80 border border-purple-200/80 rounded-2xl p-4">
            <div class="space-y-0.5">
              <h4 class="font-extrabold text-sm text-purple-900 font-lexend">Belum punya format Excel?</h4>
              <p class="text-xs text-purple-700">Unduh template standar resmi agar format kolom sesuai saat diunggah.</p>
            </div>
            <button
              @click="downloadTemplate"
              :disabled="downloadingTemplate"
              class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center gap-2 cursor-pointer"
            >
              <svg v-if="downloadingTemplate" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              <span>{{ downloadingTemplate ? 'Mengunduh...' : 'Download Template .xlsx' }}</span>
            </button>
          </div>

          <!-- Drag and Drop Dropzone -->
          <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            :class="[
              isDragging ? 'border-emerald-500 bg-emerald-50/40' : 'border-slate-300 bg-slate-50/50',
              'border-2 border-dashed rounded-3xl p-10 text-center space-y-3 transition-all cursor-pointer hover:border-emerald-400'
            ]"
            @click="triggerFileSelect"
          >
            <input ref="fileInput" type="file" accept=".xlsx, .xls, .csv" class="hidden" @change="handleFileSelect" />
            
            <div class="w-16 h-16 rounded-3xl bg-white border border-slate-200 shadow-sm mx-auto flex items-center justify-center text-emerald-600">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            </div>

            <div>
              <h4 class="font-black text-sm text-slate-800 font-lexend">Pilih atau Tarik File Excel Ke Sini</h4>
              <p class="text-xs text-slate-400 mt-0.5">Mendukung format file .xlsx, .xls, atau .csv (Maksimal 10MB)</p>
            </div>
          </div>
        </div>

        <!-- Step 2: Interactive Table Preview & Validation -->
        <div v-else class="flex-1 flex flex-col min-h-0 space-y-3">
          <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-900 text-white p-4 rounded-2xl flex-shrink-0">
            <div class="flex items-center gap-4 text-xs font-bold">
              <span class="px-3 py-1 bg-slate-800 rounded-lg">📊 Total: <strong class="text-emerald-400 font-mono text-sm">{{ previewData.total_rows }}</strong></span>
              <span class="px-3 py-1 bg-emerald-950 text-emerald-300 rounded-lg border border-emerald-800">✅ Valid: <strong class="font-mono text-sm">{{ previewData.valid_count }}</strong></span>
              <span class="px-3 py-1 bg-rose-950 text-rose-300 rounded-lg border border-rose-800">❌ Error/Duplikat: <strong class="font-mono text-sm">{{ previewData.invalid_count }}</strong></span>
            </div>

            <button @click="previewData = null" class="text-xs text-slate-400 hover:text-white font-semibold underline">Ganti File</button>
          </div>

          <!-- Preview Table -->
          <div class="flex-1 overflow-auto border border-slate-200/80 rounded-2xl bg-white shadow-inner">
            <table class="w-full text-left text-xs font-inter border-collapse min-w-[700px]">
              <thead>
                <tr class="bg-slate-100 text-slate-700 font-black border-b border-slate-200 sticky top-0 z-10">
                  <th class="p-3 w-14 font-lexend uppercase text-[10px]">No</th>
                  <th class="p-3 font-lexend uppercase text-[10px]">Status</th>
                  <th v-if="type === 'students'" class="p-3 font-lexend uppercase text-[10px]">NISN / NIS</th>
                  <th v-if="type === 'teachers'" class="p-3 font-lexend uppercase text-[10px]">NIP / NUPTK</th>
                  <th v-if="type === 'grades'" class="p-3 font-lexend uppercase text-[10px]">NISN</th>
                  <th class="p-3 font-lexend uppercase text-[10px]">Nama Lengkap</th>
                  <th class="p-3 font-lexend uppercase text-[10px]">Keterangan / Validasi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="row in previewData.rows"
                  :key="row.row_num"
                  :class="row.is_valid ? 'hover:bg-emerald-50/20' : 'bg-rose-50/30 hover:bg-rose-50/50'"
                >
                  <td class="p-3 font-mono font-bold text-slate-400">{{ row.row_num }}</td>
                  <td class="p-3">
                    <span v-if="row.is_valid" class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-black text-[10px]">VALID</span>
                    <span v-else class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-black text-[10px]">ERROR</span>
                  </td>
                  <td v-if="type === 'students'" class="p-3 font-mono font-bold text-slate-800">{{ row.nisn }} / {{ row.nis || '-' }}</td>
                  <td v-if="type === 'teachers'" class="p-3 font-mono font-bold text-slate-800">{{ row.nip || '-' }} / {{ row.nuptk || '-' }}</td>
                  <td v-if="type === 'grades'" class="p-3 font-mono font-bold text-slate-800">{{ row.nisn }}</td>
                  <td class="p-3 font-extrabold text-slate-900">{{ row.full_name || row.student_name }}</td>
                  <td class="p-3 font-semibold text-xs">
                    <span v-if="row.is_valid" class="text-emerald-700">Siap diimpor</span>
                    <span v-else class="text-rose-600 font-bold">{{ row.errors.join(', ') }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 flex-shrink-0">
          <button @click="$emit('close')" class="btn btn-secondary text-xs px-5">Batal</button>
          
          <button
            v-if="previewData && previewData.valid_count > 0"
            @click="executeImport"
            :disabled="processingImport"
            class="btn bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs px-6 py-2.5 shadow-lg shadow-purple-600/30 flex items-center gap-2 cursor-pointer"
          >
            <svg v-if="processingImport" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
            <span v-if="processingImport">Memproses Import...</span>
            <span v-else>Proses Import {{ previewData.valid_count }} Data Valid</span>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';

const props = defineProps({
  show: Boolean,
  type: String, // 'students', 'teachers', 'grades'
  title: String,
});

const emit = defineEmits(['close', 'success']);

const toast = useToast();
const fileInput = ref(null);
const isDragging = ref(false);
const previewData = ref(null);
const processingImport = ref(false);
const downloadingTemplate = ref(false);

async function downloadTemplate() {
  downloadingTemplate.value = true;
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`/api/admin/excel/template/${props.type}`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Gagal mengunduh file template');
    }

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Template_Import_${props.type}_YASPIN.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    toast.success('Template Excel berhasil diunduh!');
  } catch (err) {
    console.error(err);
    toast.error('Gagal mengunduh template. Pastikan Anda sudah login.');
  } finally {
    downloadingTemplate.value = false;
  }
}

function triggerFileSelect() {
  fileInput.value?.click();
}

function handleFileSelect(e) {
  const files = e.target.files;
  if (files.length > 0) uploadAndPreview(files[0]);
}

function handleDrop(e) {
  isDragging.value = false;
  const files = e.dataTransfer.files;
  if (files.length > 0) uploadAndPreview(files[0]);
}

async function uploadAndPreview(file) {
  const formData = new FormData();
  formData.append('file', file);

  try {
    const res = await api.postForm(`/admin/excel/preview/${props.type}`, formData);
    previewData.value = res;
    toast.success('File Excel berhasil diurai. Silakan periksa validasi data!');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal membaca file Excel. Pastikan format kolom sesuai.');
  }
}

async function executeImport() {
  if (!previewData.value || previewData.value.valid_count === 0) return;
  processingImport.value = true;

  try {
    const res = await api.post(`/admin/excel/import/${props.type}`, {
      rows: previewData.value.rows,
    });
    toast.success(res.data?.message || 'Import data berhasil!');
    emit('success');
    emit('close');
  } catch (err) {
    toast.error('Gagal memproses import data ke database.');
  } finally {
    processingImport.value = false;
  }
}
</script>
