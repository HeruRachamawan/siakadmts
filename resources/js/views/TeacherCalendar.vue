<template>
  <div class="space-y-6 font-inter">
    <!-- Header Card -->
    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100/80 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
      <div class="space-y-2 z-10">
        <div class="inline-flex items-center gap-2 px-3.5 py-1 bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs font-bold rounded-full">
          <UserCheck class="w-3.5 h-3.5 text-emerald-600" />
          <span>Kalender Akademik</span>
          <span class="text-emerald-300">•</span>
          <span>{{ teacherName || 'Guru Pengajar' }}</span>
        </div>
        <h1 class="text-2xl font-black text-slate-800 font-lexend uppercase tracking-wider">Kalender & Rekap Mengajar</h1>
        <p class="text-xs text-slate-500 font-medium max-w-xl">
          Pantau jadwal mengajar per bulan serta agenda penting sekolah (ujian, libur, kegiatan) secara terstruktur.
        </p>
      </div>

      <!-- Month & Year Controls -->
      <div class="flex flex-wrap items-center gap-3 z-10">
        <div class="bg-slate-900 text-white rounded-2xl p-2 px-4 shadow-lg flex items-center gap-3">
          <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-400 font-lexend">Bulan:</span>
          <select
            v-model="selectedMonth"
            class="bg-slate-800 text-white font-bold text-xs rounded-xl px-3 py-1.5 border border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
          >
            <option v-for="m in monthsList" :key="m.num" :value="m.num">{{ m.name }}</option>
          </select>
          <select
            v-model="selectedYear"
            class="bg-slate-800 text-white font-bold text-xs rounded-xl px-3 py-1.5 border border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
          >
            <option v-for="y in yearsList" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Monthly Summary Stats Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100/90 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center text-emerald-600 font-black shadow-xs flex-shrink-0">
          <Clock class="w-6 h-6 text-emerald-600" />
        </div>
        <div>
          <span class="text-2xl font-black text-slate-800 font-lexend block">{{ totalMonthlyHours }} Jam</span>
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estimasi Pelajaran / Bulan</span>
        </div>
      </div>

      <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100/90 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-teal-50 border border-teal-200/80 flex items-center justify-center text-teal-600 font-black shadow-xs flex-shrink-0">
          <Calendar class="w-6 h-6 text-teal-600" />
        </div>
        <div>
          <span class="text-2xl font-black text-slate-800 font-lexend block">{{ activeTeachingDaysCount }} Hari</span>
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hari Efektif Mengajar / Mgg</span>
        </div>
      </div>

      <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100/90 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200/80 flex items-center justify-center text-amber-600 font-black shadow-xs flex-shrink-0">
          <Building2 class="w-6 h-6 text-amber-600" />
        </div>
        <div>
          <span class="text-2xl font-black text-slate-800 font-lexend block">{{ classBreakdown.length }} Kelas</span>
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kelas Yang Diajar</span>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-[2.5rem] p-12 text-center text-slate-400 text-xs font-medium border border-slate-100">
      <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-3"></div>
      Memuat data kalender & mengajar...
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left 2 Cols: Monthly Teaching Schedule & Breakdown -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Class Teaching Hours Breakdown -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100/80 p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
              <h3 class="font-lexend font-black text-base uppercase tracking-wider text-slate-800">
                Rincian Jam Mengajar Per Kelas
              </h3>
              <p class="text-xs text-slate-400 font-medium mt-0.5">Bulan {{ currentMonthName }} {{ selectedYear }}</p>
            </div>
            <span class="px-3.5 py-1.5 bg-emerald-50 text-emerald-800 text-xs font-bold rounded-full border border-emerald-200 flex items-center gap-1.5">
              <Clock class="w-3.5 h-3.5 text-emerald-600" />
              <span>{{ totalWeeklySlots }} Sesi / Minggu</span>
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="cls in classBreakdown"
              :key="cls.className"
              class="p-4 rounded-2xl border bg-slate-50/50 border-slate-200/80 flex items-center justify-between gap-3 hover:border-emerald-300 hover:bg-emerald-50/20 transition-all"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-black text-sm flex items-center justify-center shadow-xs flex-shrink-0">
                  <Building2 class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h4 class="font-black text-sm text-slate-800 font-lexend">{{ cls.className }}</h4>
                  <span class="text-[11px] text-emerald-700 font-bold">{{ cls.subjectName }}</span>
                </div>
              </div>

              <div class="text-right">
                <span class="text-sm font-black text-slate-900 block font-mono">~{{ cls.monthlyHours }} Jam</span>
                <span class="text-[10px] text-slate-400 font-bold uppercase">/ Bulan</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Weekly Schedule Slots Summary -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100/80 p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
              <h3 class="font-lexend font-black text-base uppercase tracking-wider text-slate-800">
                Jadwal Mengajar Mingguan
              </h3>
              <p class="text-xs text-slate-400 font-medium mt-0.5">Hari dan Jam Rutin Pembelajaran</p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-inter border-collapse">
              <thead>
                <tr class="bg-slate-50 text-slate-600 font-black border-b border-slate-200">
                  <th class="p-3 font-lexend uppercase text-[10px]">Hari</th>
                  <th class="p-3 font-lexend uppercase text-[10px]">Waktu</th>
                  <th class="p-3 font-lexend uppercase text-[10px]">Kelas</th>
                  <th class="p-3 font-lexend uppercase text-[10px]">Mata Pelajaran</th>
                  <th class="p-3 font-lexend uppercase text-[10px]">Ruang</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in teachingSchedules" :key="item.id" class="hover:bg-emerald-50/30 transition-colors">
                  <td class="p-3 font-bold text-emerald-700 uppercase">{{ item.day }}</td>
                  <td class="p-3 font-mono font-bold text-slate-800">
                    <Clock class="w-3 h-3 text-emerald-500 inline mr-1" />
                    <span>{{ item.start_time }} - {{ item.end_time }}</span>
                  </td>
                  <td class="p-3 font-extrabold text-slate-900">
                    <Building2 class="w-3 h-3 text-emerald-600 inline mr-1" />
                    <span>{{ formatClassName(item) }}</span>
                  </td>
                  <td class="p-3 font-bold text-slate-700">
                    <BookOpen class="w-3 h-3 text-teal-500 inline mr-1" />
                    <span>{{ item.subject?.name || '-' }}</span>
                  </td>
                  <td class="p-3 font-semibold text-slate-500">{{ item.room || 'Gedung Utama' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right 1 Col: School Academic Calendar Events -->
      <div class="space-y-6">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100/80 p-6 space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 font-black text-sm flex items-center justify-center">
                <Megaphone class="w-4 h-4 text-emerald-600" />
              </div>
              <h3 class="font-lexend font-black text-sm uppercase tracking-wider text-slate-800">
                Agenda Sekolah Bulan Ini
              </h3>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ currentMonthName }}</span>
          </div>

          <!-- Calendar Events List -->
          <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
            <div v-if="filteredMonthEvents.length === 0" class="py-8 text-center text-slate-400 text-xs font-medium border-2 border-dashed border-slate-200/80 rounded-2xl">
              Tidak ada agenda khusus di bulan {{ currentMonthName }} {{ selectedYear }}
            </div>

            <div
              v-for="ev in filteredMonthEvents"
              :key="ev.id"
              class="p-4 rounded-2xl border bg-white border-slate-200/70 shadow-2xs space-y-2 hover:border-emerald-300 transition-all"
            >
              <div class="flex items-center justify-between gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider" :style="{ backgroundColor: (colorHexMap[ev.color] || '#10b981') + '20', color: colorHexMap[ev.color] || '#059669' }">
                  {{ ev.type || 'Agenda' }}
                </span>
                <span class="text-[11px] font-mono font-bold text-slate-500 flex items-center gap-1">
                  <Calendar class="w-3 h-3 text-slate-400" />
                  <span>{{ formatDateRange(ev.start_date, ev.end_date) }}</span>
                </span>
              </div>

              <h4 class="font-black text-xs text-slate-800 leading-snug">
                {{ ev.title }}
              </h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  UserCheck,
  Clock,
  Calendar,
  Building2,
  BookOpen,
  Megaphone,
} from 'lucide-vue-next';

const toast = useToast();
const loading = ref(true);
const teacherName = ref('');
const schedules = ref([]);
const calendarEvents = ref([]);

const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(new Date().getFullYear());

const monthsList = [
  { num: 1, name: 'Januari' },
  { num: 2, name: 'Februari' },
  { num: 3, name: 'Maret' },
  { num: 4, name: 'April' },
  { num: 5, name: 'Mei' },
  { num: 6, name: 'Juni' },
  { num: 7, name: 'Juli' },
  { num: 8, name: 'Agustus' },
  { num: 9, name: 'September' },
  { num: 10, name: 'Oktober' },
  { num: 11, name: 'November' },
  { num: 12, name: 'Desember' },
];

const yearsList = [2025, 2026, 2027];

const colorHexMap = {
  indigo: '#6366f1',
  emerald: '#10b981',
  amber: '#f59e0b',
  rose: '#f43f5e',
  purple: '#8b5cf6',
  sky: '#0284c7',
};

const currentMonthName = computed(() => {
  const m = monthsList.find(item => item.num === selectedMonth.value);
  return m ? m.name : '';
});

const teachingSchedules = computed(() => {
  return schedules.value.filter(s => !s.is_activity);
});

const totalWeeklySlots = computed(() => {
  return teachingSchedules.value.length;
});

const activeTeachingDaysCount = computed(() => {
  const daysSet = new Set(teachingSchedules.value.map(s => s.day?.toLowerCase()));
  return daysSet.size;
});

const totalMonthlyHours = computed(() => {
  return totalWeeklySlots.value * 4;
});

function formatClassName(item) {
  const name = item.class_room?.name || item.classRoom?.name || item.class?.name;
  if (!name) return 'Semua Kelas';
  return name.startsWith('Kelas') ? name : `Kelas ${name}`;
}

const classBreakdown = computed(() => {
  const map = {};
  teachingSchedules.value.forEach(s => {
    const rawName = s.class_room?.name || s.classRoom?.name || s.class?.name;
    const cName = rawName ? (rawName.startsWith('Kelas') ? rawName : `Kelas ${rawName}`) : 'Semua Kelas';
    const sName = s.subject?.name || 'Pelajaran';
    if (!map[cName]) {
      map[cName] = { className: cName, subjectName: sName, weeklySlots: 0 };
    }
    map[cName].weeklySlots += 1;
  });

  return Object.values(map).map(item => ({
    ...item,
    monthlyHours: item.weeklySlots * 4
  }));
});

const filteredMonthEvents = computed(() => {
  return calendarEvents.value.filter(ev => {
    if (!ev.start_date) return false;
    const dt = new Date(ev.start_date);
    return (dt.getMonth() + 1) === selectedMonth.value && dt.getFullYear() === selectedYear.value;
  });
});

function formatDateRange(start, end) {
  if (!start) return '';
  const d1 = new Date(start);
  const dateStr1 = `${d1.getDate()} ${monthsList[d1.getMonth()].name.slice(0, 3)}`;
  if (!end || start === end) return dateStr1;
  const d2 = new Date(end);
  const dateStr2 = `${d2.getDate()} ${monthsList[d2.getMonth()].name.slice(0, 3)}`;
  return `${dateStr1} - ${dateStr2}`;
}

const fetchData = async () => {
  loading.value = true;
  try {
    const [schRes, calRes] = await Promise.all([
      api.get('/teacher/schedules').catch(() => null),
      api.get('/teacher/calendar-events?per_page=1000').catch(() => null)
    ]);

    const schData = schRes?.data || schRes || {};
    teacherName.value = schData.teacher?.full_name || '';
    schedules.value = schData.schedules || [];
    calendarEvents.value = calRes?.data?.data || calRes?.data || [];
  } catch (err) {
    toast.error('Gagal memuat data kalender akademik.');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
