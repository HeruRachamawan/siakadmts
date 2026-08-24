<template>
  <div class="space-y-6 font-inter">
    <!-- Top Action Bar (No Print) -->
    <div class="no-print bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-black text-slate-900 font-lexend">Rekapitulasi Kehadiran Siswa</h1>
        <p class="text-xs text-slate-500 mt-1">Riwayat presensi dan kehadiran harian di kelas.</p>
      </div>

      <button
        @click="triggerPrint"
        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-2 shadow-md shadow-emerald-600/20 cursor-pointer w-fit"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        <span>Cetak Rekap Kehadiran</span>
      </button>
    </div>

    <!-- Printable Content Container -->
    <div id="print-student-attendance-area" class="bg-white p-6 sm:p-10 rounded-3xl shadow-sm border border-slate-100 space-y-6">
      <!-- Kop Surat Resmi (Visible on Print) -->
      <div class="hidden print:block border-b-4 border-double border-slate-900 pb-4">
        <div class="flex items-center gap-5">
          <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
            <img v-if="appSettings?.app_logo" :src="getImageUrl(appSettings.app_logo)" class="w-full h-full object-contain" alt="Logo Sekolah" />
            <div v-else class="w-14 h-14 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-lg">MTS</div>
          </div>
          <div class="text-center flex-1 pr-14">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest">{{ appSettings?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM' }}</h3>
            <h1 class="text-xl font-black text-slate-900 uppercase font-lexend tracking-tight mt-0.5">{{ appSettings?.app_name || 'MADRASAH TSANAWIYAH AL-HASANAH' }}</h1>
            <p class="text-xs text-slate-600 font-medium mt-0.5">{{ appSettings?.school_address || 'Jl. Raya Ciwidey No. 123, Kab. Bandung' }}</p>
            <p class="text-[10px] text-slate-500 font-mono">
              Telp: {{ appSettings?.school_phone || '(022) 1234567' }} &bull; Email: {{ appSettings?.school_email || 'info@mtsalhasanah.sch.id' }}
            </p>
          </div>
        </div>

        <div class="text-center space-y-1 pt-4 pb-2">
          <h2 class="text-base font-black uppercase text-slate-900 font-lexend tracking-wider underline">
            LAPORAN REKAPITULASI KEHADIRAN SISWA
          </h2>
          <p class="text-xs font-bold text-slate-600">
            TAHUN PELAJARAN 2026/2027
          </p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12 text-slate-400 text-sm font-medium">
        Memuat data rekap kehadiran...
      </div>

      <!-- Attendance Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs font-inter border border-slate-300">
          <thead>
            <tr class="bg-slate-100 text-slate-900 font-black border-b border-slate-300 uppercase text-[11px] tracking-wider">
              <th class="p-3 w-12 text-center border border-slate-300">No</th>
              <th class="p-3 w-36 text-center border border-slate-300">Tanggal</th>
              <th class="p-3 w-36 text-center border border-slate-300">Kelas</th>
              <th class="p-3 border border-slate-300">Mata Pelajaran</th>
              <th class="p-3 w-36 text-center border border-slate-300">Status Kehadiran</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(att, idx) in attendances" :key="att.id" class="border-b border-slate-200 hover:bg-slate-50/60">
              <td class="p-3 text-center font-bold text-slate-500 border border-slate-300">{{ idx + 1 }}</td>
              <td class="p-3 text-center font-mono font-semibold text-slate-800 border border-slate-300">{{ att.date }}</td>
              <td class="p-3 text-center font-bold text-slate-700 border border-slate-300">{{ att.class?.name || att.class_id || '-' }}</td>
              <td class="p-3 text-slate-800 font-medium border border-slate-300">{{ att.subject?.name || '-' }}</td>
              <td class="p-3 text-center border border-slate-300">
                <span :class="[
                  att.status === 'present' || att.status === 'hadir' ? 'bg-emerald-100 text-emerald-800' :
                  att.status === 'permission' || att.status === 'izin' ? 'bg-indigo-100 text-indigo-800' :
                  att.status === 'sick' || att.status === 'sakit' ? 'bg-rose-100 text-rose-800' :
                  'bg-slate-100 text-slate-700',
                  'px-2.5 py-0.5 rounded-full font-black text-[10px] uppercase tracking-wider'
                ]">
                  {{ formatStatus(att.status) }}
                </span>
              </td>
            </tr>
            <tr v-if="attendances.length === 0">
              <td colspan="5" class="p-8 text-center text-slate-400 font-medium">Belum ada riwayat kehadiran yang tercatat.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Signature Footer (Visible on Print) -->
      <div class="hidden print:grid grid-cols-2 text-center text-xs font-semibold text-slate-700 pt-12">
        <div>
          <p>Mengetahui,</p>
          <p class="font-bold">Orang Tua / Wali Siswa</p>
          <div class="h-20"></div>
          <p class="font-bold underline">( ............................................ )</p>
        </div>
        <div>
          <p>{{ getTodayDateFormatted() }}</p>
          <p class="font-bold">Wali Kelas</p>
          <div class="h-20"></div>
          <p class="font-bold underline">( ............................................ )</p>
          <p class="text-[10px] text-slate-500 font-mono">NIP: -</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../api';

const loading = ref(true);
const attendances = ref([]);
const appSettings = ref({});

const getImageUrl = (path) => {
  if (!path) return '';
  if (typeof path !== 'string') return '';
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path.replace(/^\//, '');
};

const getTodayDateFormatted = () => {
  const d = new Date();
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return `${appSettings.value?.school_city || 'Bandung'}, ${d.toLocaleDateString('id-ID', options)}`;
};

function formatStatus(status) {
  if (status === 'present' || status === 'hadir') return 'Hadir';
  if (status === 'permission' || status === 'izin') return 'Izin';
  if (status === 'sick' || status === 'sakit') return 'Sakit';
  if (status === 'absent' || status === 'alpha') return 'Alpha';
  return status || '-';
}

onMounted(async () => {
  try {
    const [attRes, setRes] = await Promise.all([
      api.get('student/attendances').catch(() => ({ data: [] })),
      api.get('/settings').catch(() => null)
    ]);
    attendances.value = attRes?.data || [];
    if (setRes?.data) appSettings.value = setRes.data;
  } catch {
    attendances.value = [];
  } finally {
    loading.value = false;
  }
});

function triggerPrint() {
  const printElem = document.getElementById('print-student-attendance-area');
  if (!printElem) {
    window.print();
    return;
  }

  const content = printElem.innerHTML;
  const printWindow = window.open('', '_blank', 'width=1100,height=800');
  if (!printWindow) {
    window.print();
    return;
  }

  printWindow.document.open();
  printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekap Kehadiran Siswa - ${appSettings.value?.app_name || 'Sekolah'}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 10mm 12mm 10mm 12mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: #ffffff !important;
      color: #0f172a;
      padding: 10px;
      font-size: 11px;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .font-black { font-weight: 900; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .uppercase { text-transform: uppercase; }
    .underline { text-decoration: underline; }
    
    .flex { display: flex; align-items: center; }
    .justify-between { justify-content: space-between; }
    .items-center { align-items: center; }
    .gap-5 { gap: 20px; }
    .grid { display: grid; }
    .grid-cols-2 { grid-template-columns: 1fr 1fr; }
    .space-y-1 > * + * { margin-top: 4px; }
    .space-y-6 > * + * { margin-top: 20px; }
    
    .border-b-4 { border-bottom: 4px solid #0f172a; }
    .border-double { border-bottom-style: double; }
    .pb-4 { padding-bottom: 12px; }
    .pt-4 { padding-top: 12px; }
    .pt-12 { padding-top: 35px; }
    .pr-14 { padding-right: 45px; }
    
    .w-14 { width: 56px; }
    .h-14 { height: 56px; }
    .w-16 { width: 64px; }
    .h-16 { height: 64px; }
    .h-20 { height: 70px; }
    .object-contain { object-fit: contain; }
    img { max-width: 100%; height: auto; }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      font-size: 10px;
    }
    table, th, td {
      border: 1px solid #334155;
    }
    th {
      background-color: #f1f5f9 !important;
      color: #0f172a;
      font-weight: 800;
      padding: 6px 8px;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    td {
      padding: 6px 8px;
      color: #1e293b;
    }
    
    .bg-slate-100 { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
    .bg-emerald-100 { background-color: #d1fae5 !important; color: #065f46 !important; -webkit-print-color-adjust: exact; }
    .bg-indigo-100 { background-color: #e0e7ff !important; color: #3730a3 !important; -webkit-print-color-adjust: exact; }
    .bg-rose-100 { background-color: #ffe4e6 !important; color: #9f1239 !important; -webkit-print-color-adjust: exact; }
    
    .hidden { display: block !important; }
  </style>
</head>
<body>
  ${content}
</body>
</html>`);
  printWindow.document.close();

  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 400);
}
</script>

<style scoped>
@media print {
  .no-print {
    display: none !important;
  }
  #print-student-attendance-area {
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    box-shadow: none !important;
  }
}
</style>
