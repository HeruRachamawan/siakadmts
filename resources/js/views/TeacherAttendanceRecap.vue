<template>
  <div class="space-y-8 pb-12">
    <!-- Header (Non-print) -->
    <div class="no-print bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <span class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md">
          Laporan Rekapitulasi Kehadiran
        </span>
        <h1 class="text-2xl sm:text-3xl font-black mt-2 font-lexend">Rekap Presensi Saya</h1>
        <p class="text-emerald-100 text-xs sm:text-sm mt-1">Laporan kehadiran bulanan mandiri lengkap dengan statistik dan cetak PDF.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button
          @click="printReport"
          class="flex items-center gap-2 px-4 py-2.5 bg-white text-emerald-800 hover:bg-emerald-50 rounded-xl text-xs font-bold transition-all shadow-md cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          Cetak Laporan / PDF
        </button>
      </div>
    </div>

    <!-- Filter Bar (Non-print) -->
    <div class="no-print bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-3 w-full sm:w-auto">
        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex-shrink-0">Pilih Periode Bulan:</label>
        <input
          type="month"
          v-model="selectedMonth"
          @change="loadRecap"
          class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
        />
      </div>

      <div class="text-xs text-slate-500 font-medium">
        Periode Terpilih: <b class="text-slate-800 font-bold">{{ recapData?.month_label || selectedMonth }}</b>
      </div>
    </div>

    <!-- Printable & Screen Content Container -->
    <div id="printable-recap" class="space-y-6 bg-white p-2 sm:p-6 rounded-3xl border border-slate-100 shadow-sm print:border-none print:shadow-none print:p-0">
      
      <!-- Print Header Resmi (Visible on Print) -->
      <div class="hidden print:block mb-6">
        <div class="flex items-center gap-5 border-b-4 border-double border-slate-900 pb-4">
          <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center">
            <img v-if="appSettings?.app_logo" :src="getImageUrl(appSettings.app_logo)" class="w-full h-full object-contain" alt="Logo" />
            <div v-else class="w-14 h-14 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-lg">MTS</div>
          </div>
          <div class="text-center flex-1 pr-14">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-widest">{{ appSettings?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM' }}</h3>
            <h1 class="text-xl font-black text-slate-900 uppercase font-lexend tracking-tight mt-0.5">{{ appSettings?.app_name || recapData?.school_name || 'MADRASAH TSANAWIYAH AL-HASANAH' }}</h1>
            <p class="text-xs text-slate-600 font-medium mt-0.5">{{ appSettings?.school_address || 'Jl. Raya Ciwidey No. 123, Kab. Bandung' }}</p>
            <p class="text-[10px] text-slate-500 font-mono">
              Telp: {{ appSettings?.school_phone || '(022) 1234567' }} &bull; Email: {{ appSettings?.school_email || 'info@mtsalhasanah.sch.id' }}
            </p>
          </div>
        </div>

        <div class="text-center space-y-1 py-3">
          <h2 class="text-base font-black uppercase text-slate-900 font-lexend tracking-wider underline">
            LAPORAN REKAPITULASI PRESENSI KEHADIRAN GURU
          </h2>
          <p class="text-xs font-bold text-slate-600">
            PERIODE BULAN: <span class="uppercase text-emerald-800">{{ recapData?.month_label || selectedMonth }}</span>
          </p>
        </div>
      </div>

      <!-- Identity Card -->
      <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200/60 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-4">
          <img :src="getImageUrl(recapData?.teacher?.photo_url)" class="w-14 h-14 rounded-2xl object-cover border-2 border-white shadow-md" alt="Photo" />
          <div>
            <h2 class="text-lg font-black text-slate-800 font-lexend">{{ recapData?.teacher?.full_name || '-' }}</h2>
            <p class="text-xs font-medium text-slate-500 mt-0.5">
              NIP: <span class="font-mono font-bold text-slate-700">{{ recapData?.teacher?.nip }}</span> • Jabatan: <b>{{ recapData?.teacher?.position }}</b>
            </p>
          </div>
        </div>

        <div class="bg-emerald-600 text-white px-5 py-3 rounded-2xl shadow-sm text-right">
          <p class="text-[10px] font-bold uppercase tracking-wider opacity-80">Tingkat Kehadiran</p>
          <p class="text-2xl font-black font-lexend mt-0.5">{{ recapData?.summary?.attendance_percentage || 0 }}%</p>
        </div>
      </div>

      <!-- Summary Stat Cards -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-emerald-50/60 border border-emerald-200/80 rounded-2xl p-4 space-y-1">
          <p class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Hadir Tepat Waktu</p>
          <p class="text-2xl font-black text-emerald-700 font-lexend">{{ recapData?.summary?.hadir || 0 }} Hari</p>
        </div>

        <div class="bg-amber-50/60 border border-amber-200/80 rounded-2xl p-4 space-y-1">
          <p class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">Terlambat</p>
          <p class="text-2xl font-black text-amber-700 font-lexend">{{ recapData?.summary?.terlambat || 0 }} Hari</p>
        </div>

        <div class="bg-indigo-50/60 border border-indigo-200/80 rounded-2xl p-4 space-y-1">
          <p class="text-[10px] font-bold text-indigo-800 uppercase tracking-wider">Izin</p>
          <p class="text-2xl font-black text-indigo-700 font-lexend">{{ recapData?.summary?.izin || 0 }} Hari</p>
        </div>

        <div class="bg-rose-50/60 border border-rose-200/80 rounded-2xl p-4 space-y-1">
          <p class="text-[10px] font-bold text-rose-800 uppercase tracking-wider">Sakit</p>
          <p class="text-2xl font-black text-rose-700 font-lexend">{{ recapData?.summary?.sakit || 0 }} Hari</p>
        </div>

        <div class="bg-cyan-50/60 border border-cyan-200/80 rounded-2xl p-4 space-y-1">
          <p class="text-[10px] font-bold text-cyan-800 uppercase tracking-wider">Tugas Luar / Dinas</p>
          <p class="text-2xl font-black text-cyan-700 font-lexend">{{ recapData?.summary?.tugas_luar || 0 }} Hari</p>
        </div>

        <div class="bg-slate-100 border border-slate-200 rounded-2xl p-4 space-y-1">
          <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Libur Akhir Pekan</p>
          <p class="text-2xl font-black text-slate-600 font-lexend">{{ recapData?.summary?.libur || 0 }} Hari</p>
        </div>
      </div>

      <!-- Detail Daily Attendance Table -->
      <div class="overflow-x-auto rounded-2xl border border-slate-100">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-100/80 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
            <tr>
              <th class="px-4 py-3 text-center w-12">Tgl</th>
              <th class="px-4 py-3">Hari</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Jam Masuk (GPS)</th>
              <th class="px-4 py-3">Jam Pulang (GPS)</th>
              <th class="px-4 py-3">Durasi Kerja</th>
              <th class="px-4 py-3">Keterangan / Notes</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr 
              v-for="d in recapData?.details" 
              :key="d.date" 
              :class="[d.is_weekend ? 'bg-slate-50/60 opacity-60' : 'hover:bg-slate-50/80']"
            >
              <td class="px-4 py-3 text-center font-bold font-mono text-slate-600">{{ d.day }}</td>
              <td class="px-4 py-3 font-semibold text-slate-700">{{ d.day_name }}</td>
              <td class="px-4 py-3">
                <span :class="[
                  d.status === 'hadir' ? 'bg-emerald-100 text-emerald-800' :
                  d.status === 'terlambat' ? 'bg-amber-100 text-amber-800' :
                  d.status === 'izin' ? 'bg-indigo-100 text-indigo-800' :
                  d.status === 'sakit' ? 'bg-rose-100 text-rose-800' :
                  d.status === 'tugas_luar' ? 'bg-cyan-100 text-cyan-800' :
                  d.status === 'libur' ? 'bg-slate-100 text-slate-400' :
                  'bg-slate-100 text-slate-400',
                  'px-2.5 py-0.5 rounded-full font-bold text-[10px] uppercase tracking-wider'
                ]">
                  {{ d.status.replace('_', ' ') }}
                </span>
              </td>
              <td class="px-4 py-3 font-medium">
                <div v-if="d.check_in_time">
                  <span class="font-bold font-mono text-slate-800">{{ d.check_in_time }}</span>
                  <span v-if="d.check_in_distance !== null" class="text-[10px] text-emerald-600 ml-1">({{ d.check_in_distance }}m)</span>
                </div>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="px-4 py-3 font-medium">
                <div v-if="d.check_out_time">
                  <span class="font-bold font-mono text-slate-800">{{ d.check_out_time }}</span>
                  <span v-if="d.check_out_distance !== null" class="text-[10px] text-purple-600 ml-1">({{ d.check_out_distance }}m)</span>
                </div>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td class="px-4 py-3 font-mono font-semibold text-slate-700">
                {{ d.work_duration }}
              </td>
              <td class="px-4 py-3 text-slate-600">
                {{ d.notes || '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Signature Footer (Visible only when printed) -->
      <div class="hidden print:flex justify-between items-end pt-12 text-xs">
        <div class="text-center w-48 space-y-12">
          <p>Guru Yang Bersangkutan,</p>
          <p class="font-bold underline">{{ recapData?.teacher?.full_name }}</p>
        </div>

        <div class="text-center w-48 space-y-12">
          <p>Mengetahui,<br/>Kepala Sekolah</p>
          <p class="font-bold underline">(...................................)</p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';

const toast = useToast();
const loading = ref(true);
const selectedMonth = ref(new Date().toISOString().substring(0, 7));
const recapData = ref(null);
const appSettings = ref({});

function getImageUrl(path) {
  if (!path) return 'https://ui-avatars.com/api/?name=Guru&background=059669&color=fff';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return `/storage/${path.replace(/^\/?storage\//, '')}`;
}

async function loadRecap() {
  loading.value = true;
  try {
    const [recapRes, setRes] = await Promise.all([
      api.get('teacher/presensi/recap', { month: selectedMonth.value }),
      api.get('/settings').catch(() => null)
    ]);
    recapData.value = recapRes || recapRes.data;
    if (setRes?.data) appSettings.value = setRes.data;
  } catch (err) {
    console.error(err);
    toast.error('Gagal memuat rekap presensi.');
  } finally {
    loading.value = false;
  }
}

function printReport() {
  const printElem = document.getElementById('printable-recap');
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
  <title>Rekap Presensi Guru - ${appSettings.value?.app_name || 'Sekolah'}</title>
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
    .font-semibold { font-weight: 600; }
    .font-black { font-weight: 900; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .uppercase { text-transform: uppercase; }
    .underline { text-decoration: underline; }
    
    .flex { display: flex; align-items: center; }
    .justify-between { justify-content: space-between; }
    .justify-center { justify-content: center; }
    .items-center { align-items: center; }
    .gap-4 { gap: 16px; }
    .gap-5 { gap: 20px; }
    .grid { display: grid; }
    .grid-cols-6 { grid-template-columns: repeat(6, 1fr); }
    .space-y-1 > * + * { margin-top: 4px; }
    .space-y-2 > * + * { margin-top: 8px; }
    .space-y-6 > * + * { margin-top: 20px; }
    .space-y-12 > * + * { margin-top: 40px; }
    
    .border-b-2 { border-bottom: 2px solid #0f172a; }
    .border-b-4 { border-bottom: 4px solid #0f172a; }
    .border-double { border-bottom-style: double; }
    .pb-4 { padding-bottom: 12px; }
    .pt-12 { padding-top: 35px; }
    .p-4 { padding: 10px; }
    .p-6 { padding: 14px; }
    .pr-14 { padding-right: 45px; }
    
    .w-14 { width: 56px; }
    .h-14 { height: 56px; }
    .w-16 { width: 64px; }
    .h-16 { height: 64px; }
    .w-48 { width: 180px; }
    .rounded-2xl { border-radius: 10px; }
    .border { border: 1px solid #cbd5e1; }
    .bg-slate-50 { background-color: #f8fafc; }
    .bg-emerald-600 { background-color: #059669 !important; color: white !important; -webkit-print-color-adjust: exact; }
    .object-cover { object-fit: cover; }
    img { max-width: 100%; height: auto; }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      font-size: 10px;
    }
    table, th, td {
      border: 1px solid #475569;
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
      padding: 5px 7px;
      color: #1e293b;
    }
    
    .bg-emerald-100 { background-color: #d1fae5 !important; color: #065f46 !important; -webkit-print-color-adjust: exact; }
    .bg-amber-100 { background-color: #fef3c7 !important; color: #92400e !important; -webkit-print-color-adjust: exact; }
    .bg-indigo-100 { background-color: #e0e7ff !important; color: #3730a3 !important; -webkit-print-color-adjust: exact; }
    .bg-rose-100 { background-color: #ffe4e6 !important; color: #9f1239 !important; -webkit-print-color-adjust: exact; }
    .bg-cyan-100 { background-color: #cffafe !important; color: #155e75 !important; -webkit-print-color-adjust: exact; }
    .bg-slate-100 { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; }
    
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

onMounted(loadRecap);
</script>

<style>
@media print {
  body {
    background: white !important;
    color: black !important;
  }
  .no-print {
    display: none !important;
  }
}
</style>
