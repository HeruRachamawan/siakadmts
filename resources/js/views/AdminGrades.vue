<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend tracking-wide uppercase">Rekap Nilai Siswa</h1>
          <p class="text-xs text-slate-400 font-medium">{{ grades.length }} data nilai tercatat</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <button
          @click="exportExcelFile('grades')"
          :disabled="exportingExcel"
          class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 disabled:opacity-50 transition-colors shadow-sm cursor-pointer"
        >
          <svg v-if="exportingExcel" class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
          <svg v-else class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span>{{ exportingExcel ? 'Mengekspor...' : 'Export Excel' }}</span>
        </button>
        <button
          @click="showImportModal = true"
          class="flex items-center gap-2 px-4 py-2.5 bg-purple-50 border border-purple-200 text-purple-700 font-bold rounded-xl text-xs hover:bg-purple-100 transition-colors shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
          <span>Import Excel</span>
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <div v-if="loading" class="text-center py-16">
        <div class="inline-flex w-10 h-10 border-4 border-slate-100 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
        <p class="text-sm font-semibold text-slate-400">Memuat rekap nilai...</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA SISWA</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">MATA PELAJARAN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">TUGAS (30%)</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">UTS (30%)</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">UAS (40%)</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">NILAI AKHIR</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(row, i) in grades" :key="row.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-4 text-sm font-bold text-slate-400">{{ i + 1 }}</td>
              <td class="px-6 py-4">
                <div class="font-extrabold text-slate-800 text-sm">{{ row.student?.full_name || row.student_name || '-' }}</div>
                <div class="text-[11px] font-mono text-slate-400">NISN: {{ row.student?.nisn || '-' }}</div>
              </td>
              <td class="px-6 py-4 text-sm font-bold text-slate-700">{{ row.subject?.name || row.subject_name || '-' }}</td>
              <td class="px-6 py-4 text-center font-mono font-semibold text-slate-600">{{ row.assignment_score ?? row.score_assignment ?? '-' }}</td>
              <td class="px-6 py-4 text-center font-mono font-semibold text-slate-600">{{ row.mid_score ?? row.score_uts ?? '-' }}</td>
              <td class="px-6 py-4 text-center font-mono font-semibold text-slate-600">{{ row.final_score ?? row.score_uas ?? '-' }}</td>
              <td class="px-6 py-4 text-center">
                <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-black font-mono text-xs rounded-lg border border-indigo-100">
                  {{ row.final_grade ?? average(row) }}
                </span>
              </td>
            </tr>
            <tr v-if="!grades.length">
              <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm font-semibold">
                Belum ada data nilai tercatat.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Excel Import Modal -->
    <ExcelImportModal
      :show="showImportModal"
      type="grades"
      title="Nilai Siswa"
      @close="showImportModal = false"
      @success="loadGrades"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from '../composables/useToast';

const toast = useToast();
const loading = ref(true);
const exportingExcel = ref(false);
const grades = ref([]);
const showImportModal = ref(false);

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
    toast.success('Data Excel berhasil diekspor!');
  } catch (err) {
    console.error(err);
    toast.error('Gagal mengekspor file Excel');
  } finally {
    exportingExcel.value = false;
  }
};

async function loadGrades() {
  loading.value = true;
  try {
    const res = await api.get('admin/grades');
    const data = res.data?.data || res.data || [];
    grades.value = Array.isArray(data) ? data : data.data || [];
  } catch {
    grades.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(loadGrades);

function average(g) {
  const a = Number(g.assignment_score ?? g.score_assignment ?? 0);
  const b = Number(g.mid_score ?? g.score_uts ?? 0);
  const c = Number(g.final_score ?? g.score_uas ?? 0);
  return ((a * 0.3) + (b * 0.3) + (c * 0.4)).toFixed(1);
}
</script>
