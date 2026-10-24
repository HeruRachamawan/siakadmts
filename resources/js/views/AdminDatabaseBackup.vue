<template>
  <div class="space-y-6 font-inter pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 bg-emerald-600 text-white rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-600/20">
          <Database class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900">Cadangan & Pemulihan Database</h1>
          <p class="text-xs text-slate-500 font-medium mt-0.5">
            Amankan seluruh data madrasah (siswa, guru, nilai, absensi) dengan cadangan mandiri atau pulihkan data kapan saja.
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-2.5">
        <button
          @click="showScheduleModal = true"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all border border-slate-200 shadow-2xs cursor-pointer"
        >
          <Clock class="w-4 h-4 text-slate-500" />
          <span>Jadwal Otomatis</span>
        </button>

        <button
          @click="triggerUploadModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-all border border-indigo-200 shadow-2xs cursor-pointer"
        >
          <UploadCloud class="w-4 h-4 text-indigo-600" />
          <span>Pulihkan dari File .SQL</span>
        </button>

        <button
          @click="createBackup"
          :disabled="isCreating"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold transition-all shadow-md shadow-emerald-600/20 disabled:opacity-50 cursor-pointer"
        >
          <div v-if="isCreating" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <HardDriveDownload v-else class="w-4 h-4" />
          <span>{{ isCreating ? 'Mencadangkan...' : 'Cadangkan Database Sekarang' }}</span>
        </button>
      </div>
    </div>

    <!-- Hidden file input for restore upload -->
    <input
      ref="fileInputRef"
      type="file"
      accept=".sql,.gz"
      class="hidden"
      @change="handleFileUpload"
    />

    <!-- Status Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Database Info -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
          <Server class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nama Database</p>
          <p class="text-sm font-black text-slate-800 font-mono mt-0.5 truncate max-w-[170px]" :title="dbInfo.database_name">
            {{ dbInfo.database_name || 'siakadmts' }}
          </p>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">{{ dbInfo.host }}:{{ dbInfo.port }}</p>
        </div>
      </div>

      <!-- Total Tables & Size -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
          <Layers class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Tabel & Ukuran</p>
          <p class="text-sm font-black text-slate-800 mt-0.5">
            {{ dbInfo.total_tables || 0 }} Tabel
          </p>
          <p class="text-[10px] text-emerald-600 font-bold mt-0.5">± {{ dbInfo.size_mb || 0 }} MB di Database</p>
        </div>
      </div>

      <!-- Backups Available -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center flex-shrink-0">
          <Archive class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Berkas Cadangan</p>
          <p class="text-sm font-black text-slate-800 mt-0.5">
            {{ backups.length }} Berkas Tersimpan
          </p>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">Total Ruang: {{ totalBackupSize }}</p>
        </div>
      </div>

      <!-- Last Backup Info -->
      <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center flex-shrink-0">
          <ShieldCheck class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cadangan Terakhir</p>
          <p class="text-xs font-bold text-slate-800 mt-0.5 truncate max-w-[170px]">
            {{ formatDate(dbInfo.last_backup_at) || 'Belum Ada' }}
          </p>
          <p class="text-[10px] font-bold mt-0.5 text-purple-600">
            Jadwal: {{ scheduleLabel(dbInfo.auto_backup_schedule) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Main Content Area: Table of Backups -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
          <FolderArchive class="w-5 h-5 text-emerald-600" />
          <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Daftar Berkas Cadangan Tersimpan</h2>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-700">
            {{ backups.length }}
          </span>
        </div>

        <button
          @click="fetchBackups"
          :disabled="loading"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors cursor-pointer self-start sm:self-auto"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="{'animate-spin': loading}" />
          <span>Segarkan Data</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin w-8 h-8 border-4 border-emerald-600 border-t-transparent rounded-full mb-3"></div>
        <p class="text-xs font-bold text-slate-600">Memeriksa daftar berkas cadangan...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="backups.length === 0" class="p-16 text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
          <Archive class="w-8 h-8" />
        </div>
        <h3 class="text-sm font-bold text-slate-800">Belum Ada Berkas Cadangan</h3>
        <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
          Klik tombol <strong>"Cadangkan Database Sekarang"</strong> di atas untuk membuat salinan data pertama Anda.
        </p>
      </div>

      <!-- Table View -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200/80 text-[11px] uppercase tracking-wider">
              <th class="py-3 px-4 w-12 text-center">No</th>
              <th class="py-3 px-4">Nama Berkas</th>
              <th class="py-3 px-4">Ukuran</th>
              <th class="py-3 px-4">Tipe Cadangan</th>
              <th class="py-3 px-4">Waktu Dibuat</th>
              <th class="py-3 px-4 text-center min-w-[200px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium">
            <tr
              v-for="(item, idx) in backups"
              :key="item.filename"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <td class="py-3.5 px-4 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
              <td class="py-3.5 px-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                    <FileCode2 class="w-4 h-4" />
                  </div>
                  <div>
                    <p class="font-bold text-slate-900 font-mono text-xs">{{ item.filename }}</p>
                    <p class="text-[10px] text-slate-400">
                      {{ item.filename.endsWith('.gz') ? 'Kompresi Gzip (.sql.gz)' : 'Plain SQL (.sql)' }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded-md bg-slate-100 font-mono text-[11px] font-bold text-slate-700">
                  {{ item.size_formatted }}
                </span>
              </td>
              <td class="py-3.5 px-4">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1"
                  :class="item.type === 'Otomatis' ? 'bg-purple-100 text-purple-800' : 'bg-emerald-100 text-emerald-800'"
                >
                  <Clock v-if="item.type === 'Otomatis'" class="w-3 h-3" />
                  <Sparkles v-else class="w-3 h-3" />
                  <span>{{ item.type }}</span>
                </span>
              </td>
              <td class="py-3.5 px-4 text-slate-600 font-mono text-xs">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="py-3.5 px-4 text-center">
                <div class="inline-flex items-center gap-1.5">
                  <!-- Download Button -->
                  <button
                    @click="downloadBackup(item)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors cursor-pointer"
                    title="Unduh file ke komputer"
                  >
                    <Download class="w-3.5 h-3.5 text-blue-600" />
                    <span>Unduh</span>
                  </button>

                  <!-- Restore Button -->
                  <button
                    @click="promptRestore(item)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition-colors cursor-pointer"
                    title="Pulihkan database dari file ini"
                  >
                    <RotateCcw class="w-3.5 h-3.5 text-amber-700" />
                    <span>Pulihkan</span>
                  </button>

                  <!-- Delete Button -->
                  <button
                    @click="promptDelete(item)"
                    class="p-1.5 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                    title="Hapus file cadangan ini"
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

    <!-- Restore Confirmation Modal -->
    <Transition name="modal-fade">
      <div v-if="showRestoreModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showRestoreModal = false"></div>
        <div class="relative bg-white rounded-2xl p-6 shadow-2xl w-full max-w-md space-y-4 border border-slate-200 z-10 animate-slide-up">
          <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center flex-shrink-0">
              <AlertTriangle class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm font-black text-slate-900">Konfirmasi Pemulihan Database</h3>
              <p class="text-[11px] text-slate-500">Tindakan ini memerlukan kehati-hatian tinggi</p>
            </div>
          </div>

          <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-2 leading-relaxed">
            <p class="font-bold flex items-center gap-1.5">
              <span>⚠️ Peringatan Kritis:</span>
            </p>
            <p>
              Memulihkan database akan <strong>menimpa seluruh data saat ini</strong> dengan data dari berkas:
              <br />
              <code class="bg-amber-200/60 px-1 py-0.5 rounded font-mono font-bold text-[11px]">{{ targetRestoreFile }}</code>
            </p>
            <p class="text-[11px] text-amber-800">
              Disarankan membuat cadangan data terbaru terlebih dahulu sebelum menjalankan pemulihan.
            </p>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">
              Ketik <span class="font-mono text-rose-600 font-black">PULIHKAN</span> untuk konfirmasi:
            </label>
            <input
              v-model="restoreConfirmText"
              type="text"
              placeholder="PULIHKAN"
              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold focus:bg-white focus:ring-2 focus:ring-amber-500"
            />
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button
              type="button"
              @click="showRestoreModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="executeRestore"
              :disabled="restoreConfirmText !== 'PULIHKAN' || isRestoring"
              class="px-5 py-2 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-amber-600/20 disabled:opacity-40 cursor-pointer flex items-center gap-2"
            >
              <div v-if="isRestoring" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <span>{{ isRestoring ? 'Memulihkan...' : 'Ya, Pulihkan Database' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Schedule Settings Modal -->
    <Transition name="modal-fade">
      <div v-if="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showScheduleModal = false"></div>
        <div class="relative bg-white rounded-2xl p-6 shadow-2xl w-full max-w-md space-y-4 border border-slate-200 z-10 animate-slide-up">
          <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center flex-shrink-0">
              <Clock class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-sm font-black text-slate-900">Jadwal Cadangan Otomatis</h3>
              <p class="text-[11px] text-slate-500">Atur rutinitas pencadangan berkala di server</p>
            </div>
          </div>

          <div class="space-y-4 text-xs">
            <div>
              <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                Frekuensi Cadangan Otomatis
              </label>
              <select
                v-model="scheduleForm.schedule"
                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-purple-500 cursor-pointer"
              >
                <option value="none">❌ Nonaktif (Hanya Cadangan Manual)</option>
                <option value="daily">⏰ Setiap Hari (Tengah Malam 00:00)</option>
                <option value="weekly">📅 Setiap Minggu (Minggu Malam)</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">
                Retensi Penyimpanan (Hapus Otomatis)
              </label>
              <select
                v-model.number="scheduleForm.retention_days"
                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-purple-500 cursor-pointer"
              >
                <option :value="7">Simpan Berkas 7 Hari Terakhir</option>
                <option :value="14">Simpan Berkas 14 Hari Terakhir</option>
                <option :value="30">Simpan Berkas 30 Hari Terakhir (Disarankan)</option>
                <option :value="90">Simpan Berkas 90 Hari (3 Bulan)</option>
              </select>
              <p class="text-[10px] text-slate-500 mt-1">
                Berkas cadangan yang lebih lama dari waktu di atas akan otomatis dibersihkan agar penyimpanan server tidak penuh.
              </p>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button
              type="button"
              @click="showScheduleModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="saveSchedule"
              :disabled="isSavingSchedule"
              class="px-5 py-2 bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-purple-600/20 cursor-pointer flex items-center gap-1.5"
            >
              <div v-if="isSavingSchedule" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <span>Simpan Pengaturan</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  Database,
  Server,
  Layers,
  Archive,
  ShieldCheck,
  Clock,
  Sparkles,
  RefreshCw,
  Download,
  RotateCcw,
  Trash2,
  AlertTriangle,
  UploadCloud,
  HardDriveDownload,
  FileCode2,
  FolderArchive
} from 'lucide-vue-next';

const toast = useToast();

const loading = ref(false);
const isCreating = ref(false);
const isRestoring = ref(false);
const isSavingSchedule = ref(false);

const dbInfo = ref({
  database_name: '',
  host: '127.0.0.1',
  port: 3306,
  total_tables: 0,
  size_mb: 0,
  last_backup_at: null,
  auto_backup_schedule: 'daily',
  retention_days: 30,
});

const backups = ref([]);

const fileInputRef = ref(null);
const uploadedFileToRestore = ref(null);

const showRestoreModal = ref(false);
const targetRestoreFile = ref('');
const restoreConfirmText = ref('');

const showScheduleModal = ref(false);
const scheduleForm = ref({
  schedule: 'daily',
  retention_days: 30,
});

const totalBackupSize = computed(() => {
  const total = backups.value.reduce((acc, curr) => acc + (curr.size_bytes || 0), 0);
  if (total === 0) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(total) / Math.log(1024));
  return (total / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
});

function scheduleLabel(sch) {
  if (sch === 'daily') return 'Harian (Tengah Malam)';
  if (sch === 'weekly') return 'Mingguan (Setiap Minggu)';
  return 'Nonaktif (Manual)';
}

function formatDate(dt) {
  if (!dt) return null;
  const d = new Date(dt);
  if (isNaN(d.getTime())) return dt;
  return d.toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

async function fetchBackups() {
  loading.value = true;
  try {
    const res = await api.get('admin/backups');
    dbInfo.value = res.data?.info || {};
    backups.value = res.data?.backups || [];
    scheduleForm.value.schedule = dbInfo.value.auto_backup_schedule || 'daily';
    scheduleForm.value.retention_days = dbInfo.value.retention_days || 30;
  } catch (err) {
    toast.error('Gagal memuat informasi cadangan database');
  } finally {
    loading.value = false;
  }
}

async function createBackup() {
  isCreating.value = true;
  try {
    const res = await api.post('admin/backups');
    toast.success(res.message || 'Cadangan database berhasil dibuat!');
    await fetchBackups();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal membuat cadangan database');
  } finally {
    isCreating.value = false;
  }
}

async function downloadBackup(item) {
  try {
    const res = await axios.get(`/api/admin/backups/${item.filename}/download`, {
      responseType: 'blob',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    const url = window.URL.createObjectURL(new Blob([res.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', item.filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    toast.success(`Mengunduh ${item.filename}`);
  } catch (err) {
    toast.error('Gagal mengunduh berkas cadangan');
  }
}

function promptDelete(item) {
  if (!confirm(`Yakin ingin menghapus berkas cadangan "${item.filename}"?`)) {
    return;
  }
  deleteBackup(item.filename);
}

async function deleteBackup(filename) {
  try {
    await api.delete(`admin/backups/${filename}`);
    toast.success('Berkas cadangan berhasil dihapus');
    backups.value = backups.value.filter(b => b.filename !== filename);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus berkas');
  }
}

function promptRestore(item) {
  targetRestoreFile.value = item.filename;
  uploadedFileToRestore.value = null;
  restoreConfirmText.value = '';
  showRestoreModal.value = true;
}

function triggerUploadModal() {
  fileInputRef.value?.click();
}

function handleFileUpload(e) {
  const file = e.target.files?.[0];
  if (!file) return;

  targetRestoreFile.value = file.name;
  uploadedFileToRestore.value = file;
  restoreConfirmText.value = '';
  showRestoreModal.value = true;

  // reset input so same file can be selected again
  e.target.value = '';
}

async function executeRestore() {
  if (restoreConfirmText.value !== 'PULIHKAN') return;
  isRestoring.value = true;
  try {
    if (uploadedFileToRestore.value) {
      const formData = new FormData();
      formData.append('file', uploadedFileToRestore.value);
      await api.postForm('admin/backups/restore', formData);
    } else {
      await api.post('admin/backups/restore', {
        filename: targetRestoreFile.value,
      });
    }

    toast.success('Database berhasil dipulihkan!');
    showRestoreModal.value = false;
    await fetchBackups();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal memulihkan database');
  } finally {
    isRestoring.value = false;
  }
}

async function saveSchedule() {
  isSavingSchedule.value = true;
  try {
    await api.post('admin/backups/schedule', scheduleForm.value);
    toast.success('Pengaturan jadwal cadangan berhasil disimpan');
    showScheduleModal.value = false;
    await fetchBackups();
  } catch (err) {
    toast.error('Gagal menyimpan pengaturan');
  } finally {
    isSavingSchedule.value = false;
  }
}

onMounted(() => {
  fetchBackups();
});
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.animate-slide-up {
  animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
