<template>
  <div class="space-y-6 font-inter">

    <!-- Fresh Vibrant Emerald Hero Banner -->
    <div class="relative bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white rounded-2xl shadow-lg shadow-emerald-700/20 overflow-hidden border border-emerald-500/40">
      <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.18)_1px,transparent_1px)] [background-size:22px_22px] opacity-60 pointer-events-none"></div>
      <div class="absolute -top-16 -right-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>
      
      <div class="relative z-10 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
          <div class="w-14 h-14 bg-white/15 backdrop-blur-md rounded-2xl border border-white/30 p-2 flex items-center justify-center flex-shrink-0 text-white shadow-md">
            <GraduationCap class="w-7 h-7 text-white" />
          </div>
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-full text-[11px] font-bold border border-white/30 shadow-xs">
              <span class="w-2 h-2 rounded-full bg-emerald-200 animate-pulse"></span>
              <span>Portal Akademik Siswa</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white drop-shadow-xs">
              Selamat Datang, {{ user?.name }}!
            </h1>
            <p class="text-emerald-50 text-xs sm:text-sm font-normal">
              Pantau rekapitulasi kehadiran dan nilai akademik kamu secara real-time.
            </p>
          </div>
        </div>

        <div class="hidden lg:flex flex-col items-end justify-center border-l border-white/20 pl-6 space-y-1.5 flex-shrink-0">
          <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Status Siswa</span>
          <span class="px-3.5 py-1.5 bg-white/20 text-white text-xs font-bold rounded-xl border border-white/30 backdrop-blur-md shadow-xs">Aktif</span>
        </div>
      </div>
    </div>

    <!-- Shadcn Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <!-- Rata-rata Nilai -->
      <div class="shadcn-card p-5 flex items-center justify-between">
        <div class="space-y-1">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Nilai</p>
          <p class="text-2xl font-bold tracking-tight text-slate-900">{{ avgGrade }}.0</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center flex-shrink-0">
          <GraduationCap class="w-5 h-5" />
        </div>
      </div>

      <!-- Kehadiran -->
      <div class="shadcn-card p-5 flex items-center justify-between">
        <div class="space-y-1">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kehadiran</p>
          <p class="text-2xl font-bold tracking-tight text-slate-900">{{ attendanceRate }}%</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center flex-shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
      </div>

      <!-- Kelas -->
      <div class="shadcn-card p-5 flex items-center justify-between">
        <div class="space-y-1">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kelas</p>
          <p class="text-2xl font-bold tracking-tight text-slate-900">{{ currentClass }}</p>
        </div>
        <div class="w-10 h-10 rounded-lg bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center flex-shrink-0">
          <Building2 class="w-5 h-5" />
        </div>
      </div>
    </div>

    <!-- Quick Shortcuts -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
      <RouterLink to="/student/attendances" class="shadcn-card p-5 flex items-center justify-between hover:border-emerald-300 transition-all cursor-pointer">
        <div class="space-y-1">
          <p class="text-sm font-semibold text-slate-900">Rekap Kehadiran</p>
          <p class="text-xs text-slate-500">Lihat rincian riwayat presensi harian kamu</p>
        </div>
        <ChevronRight class="w-5 h-5 text-slate-400" />
      </RouterLink>

      <RouterLink to="/student/transcript" class="shadcn-card p-5 flex items-center justify-between hover:border-teal-300 transition-all cursor-pointer">
        <div class="space-y-1">
          <p class="text-sm font-semibold text-slate-900">Transkrip Nilai</p>
          <p class="text-xs text-slate-500">Lihat nilai tugas, UTS, dan UAS semester ini</p>
        </div>
        <ChevronRight class="w-5 h-5 text-slate-400" />
      </RouterLink>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '../stores/auth';
import { api } from '../api';
import {
  GraduationCap,
  CheckCircle2,
  Building2,
  ChevronRight
} from 'lucide-vue-next';

const auth = useAuthStore();
const { user } = storeToRefs(auth);
const grades = ref([]);
const attendances = ref([]);
const currentClass = ref('-');

onMounted(async () => {
  try {
    grades.value = (await api.get('student/grades')).data || [];
  } catch { grades.value = []; }
  try {
    attendances.value = (await api.get('student/attendances')).data || [];
  } catch { attendances.value = []; }
});

const avgGrade = computed(() => {
  if (!grades.value.length) return 0;
  const total = grades.value.reduce((sum, g) => sum + (g.score_assignment + g.score_uts + g.score_uas) / 3, 0);
  return Math.round(total / grades.value.length);
});

const attendanceRate = computed(() => {
  if (!attendances.value.length) return 0;
  const present = attendances.value.filter(a => a.status === 'present').length;
  return Math.round((present / attendances.value.length) * 100);
});
</script>
