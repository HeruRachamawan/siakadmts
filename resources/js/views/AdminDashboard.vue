<template>
  <div class="space-y-6 font-inter">

    <!-- Fresh Vibrant Emerald Hero Banner (Portal Utama Administrator) -->
    <div class="relative bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white rounded-2xl shadow-lg shadow-emerald-700/20 overflow-hidden border border-emerald-500/40">
      <!-- Subtle Background Mesh Grid & Glow -->
      <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.18)_1px,transparent_1px)] [background-size:22px_22px] opacity-60 pointer-events-none"></div>
      <div class="absolute -top-16 -right-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>
      
      <!-- Content -->
      <div class="relative z-10 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
          <!-- Logo Frame -->
          <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white/15 backdrop-blur-md rounded-2xl border border-white/30 p-2 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-md">
            <img v-if="appSettings?.app_logo" :src="getImageUrl(appSettings.app_logo)" class="w-full h-full object-contain filter drop-shadow" alt="Logo Sekolah" />
            <svg v-else class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
          </div>
          
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-full text-[11px] font-bold border border-white/30 shadow-xs">
              <span class="w-2 h-2 rounded-full bg-emerald-200 animate-pulse"></span>
              <span>Portal Utama Administrator</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight uppercase text-white leading-tight drop-shadow-xs">
              {{ appSettings?.app_name || 'MTs AL - HASANAH' }}
            </h1>
            <p class="text-emerald-50 text-xs sm:text-sm max-w-xl font-normal leading-relaxed">
              {{ appSettings?.app_tagline || 'Pusat kendali utama. Kelola data siswa, guru, mata pelajaran, dan kelas secara terintegrasi.' }}
            </p>
          </div>
        </div>

        <!-- Quick Info Badge Right -->
        <div class="hidden lg:flex flex-col items-end justify-center border-l border-white/20 pl-6 space-y-1.5 flex-shrink-0">
          <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Tahun Ajaran Aktif</span>
          <span class="px-3.5 py-1.5 bg-white/20 text-white text-xs font-bold rounded-xl border border-white/30 backdrop-blur-md shadow-xs">2026 / 2027</span>
        </div>
      </div>
    </div>

    <!-- Shadcn Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="shadcn-card p-5 flex items-center justify-between cursor-pointer hover:border-slate-300 transition-all"
        @click="$router.push(stat.link)"
      >
        <div class="space-y-1">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ stat.label }}</p>
          <p class="text-2xl font-bold tracking-tight text-slate-900">
            {{ loadingStats ? '—' : stat.value }}
          </p>
        </div>
        <div :class="[stat.iconBg, 'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 border']">
          <component :is="stat.iconComp" class="w-5 h-5" />
        </div>
      </div>
    </div>

    <!-- Recent Students Table Section -->
    <div class="shadcn-card p-6 space-y-6">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold border border-indigo-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          </div>
          <div>
            <h3 class="text-sm font-semibold text-slate-900 tracking-tight">Siswa Terdaftar Terbaru</h3>
            <p class="text-xs text-slate-500 font-normal">5 siswa yang baru diinputkan ke dalam sistem</p>
          </div>
        </div>
        <RouterLink to="/admin/students" class="btn btn-secondary">
          <span>Lihat Semua Data</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </RouterLink>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-200/80 text-slate-500 bg-slate-50/80">
              <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wider">SISWA</th>
              <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wider">NISN / NIS</th>
              <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wider">KELAS</th>
              <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wider text-right">GENDER</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="loadingStats">
              <td colspan="4" class="py-8 text-center text-xs text-slate-400 font-medium">Memuat data terbaru...</td>
            </tr>
            <template v-else>
              <tr v-for="student in recentStudents" :key="student.id" class="hover:bg-slate-50 transition-colors">
                <td class="py-3 px-4 flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 font-semibold text-xs flex items-center justify-center flex-shrink-0 border border-slate-200">
                    {{ (student.full_name || 'S').charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <span class="text-xs font-semibold text-slate-900 block">{{ student.full_name }}</span>
                    <span class="text-[10px] text-slate-400 font-normal">{{ student.birth_place || '-' }}</span>
                  </div>
                </td>
                <td class="py-3 px-4 text-xs font-medium text-slate-700">
                  <span>{{ student.nisn || '-' }}</span>
                  <span class="text-slate-300 px-1">/</span>
                  <span class="text-slate-400 font-normal">{{ student.nis || '-' }}</span>
                </td>
                <td class="py-3 px-4">
                  <span class="badge badge-success text-[11px]">
                    {{ student.classRoom?.name || 'Belum diatur' }}
                  </span>
                </td>
                <td class="py-3 px-4 text-right">
                  <span :class="[student.gender === 'L' ? 'badge badge-primary' : 'badge badge-secondary', 'text-[10px]']">
                    {{ student.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                  </span>
                </td>
              </tr>
              <tr v-if="recentStudents.length === 0">
                <td colspan="4" class="py-8 text-center text-xs text-slate-400 font-medium">Belum ada data siswa terdaftar.</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Chart & Timeline Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Chart -->
      <div class="lg:col-span-2 shadcn-card p-6">
        <h3 class="text-sm font-semibold text-slate-900 tracking-tight mb-5 border-b border-slate-100 pb-3">Distribusi Siswa (Gender)</h3>
        <div class="h-64 flex items-center justify-center">
          <Pie v-if="chartData.datasets[0].data[0] || chartData.datasets[0].data[1]" :data="chartData" :options="chartOptions" />
          <div v-else class="text-slate-400 text-xs font-medium uppercase tracking-wider">Memuat grafik...</div>
        </div>
      </div>

      <!-- Timeline (Recent Activities) -->
      <div class="shadcn-card p-6">
        <h3 class="text-sm font-semibold text-slate-900 tracking-tight mb-5 border-b border-slate-100 pb-3">Aktivitas Terbaru</h3>
        <div class="space-y-5">
          <div v-for="(activity, index) in recentActivities" :key="index" class="flex gap-3.5 relative">
            <div v-if="index !== recentActivities.length - 1" class="absolute left-3.5 top-7 bottom-[-20px] w-[1px] bg-emerald-100"></div>
            <div class="w-7 h-7 rounded-full bg-emerald-50 border border-emerald-200/80 flex items-center justify-center flex-shrink-0 relative z-10 text-emerald-700 shadow-2xs">
              <User v-if="activity.icon === 'user'" class="w-3.5 h-3.5 text-emerald-600" />
              <Building2 v-else class="w-3.5 h-3.5 text-emerald-600" />
            </div>
            <div>
              <p class="font-semibold text-slate-900 text-xs">{{ activity.title }}</p>
              <p class="text-[11px] text-slate-500 mt-0.5 font-normal">{{ activity.description }}</p>
            </div>
          </div>
          <div v-if="!recentActivities.length" class="text-slate-400 text-xs text-center py-4 font-normal">Belum ada aktivitas.</div>
        </div>
      </div>
    </div>

    <!-- Academic Calendar -->
    <div class="shadcn-card p-8 animate-slide-up" style="animation-delay: 0.4s;">
      <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
        <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider font-lexend">Kalender Akademik</h3>
        <div class="text-[10px] font-black text-emerald-700 bg-emerald-50 border border-emerald-100 px-4 py-2 rounded-xl tracking-widest uppercase">{{ currentMonthName }} {{ currentYear }}</div>
      </div>
      <div class="grid grid-cols-7 gap-3">
        <div v-for="(day, index) in ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']" :key="day" 
             class="text-center text-[10px] font-black py-2 uppercase tracking-widest rounded-lg"
             :class="index >= 5 ? 'text-rose-500 bg-rose-50/50' : 'text-slate-500 bg-slate-50/50'">
          {{ day.substring(0, 3) }}
        </div>
        <div v-for="blank in firstDayOfMonth" :key="'blank-'+blank" class="h-20 sm:h-28 rounded-2xl border border-transparent"></div>
        <div v-for="date in daysInMonth" :key="'date-'+date" 
          class="h-20 sm:h-28 rounded-2xl border p-2 sm:p-3 flex flex-col hover:shadow-md transition-all cursor-pointer group"
          :class="getCellBackgroundClass(date)"
          :style="getCellStyle(date)">
          <span class="text-xs font-black w-6 h-6 sm:w-8 sm:h-8 flex items-center justify-center rounded-full transition-colors" :class="getDateNumberClass(date)">
            {{ date }}
          </span>
          <div v-if="getEventsForDate(date).length > 0" class="mt-auto hidden sm:block space-y-1">
            <div v-for="ev in getEventsForDate(date).slice(0, 2)" :key="ev.id" 
                 class="text-[9px] font-extrabold uppercase tracking-widest px-2 py-1 rounded-lg truncate shadow-sm"
                 :style="{ backgroundColor: (colorHexMap[ev.color] || '#8b5cf6'), color: '#ffffff' }">
              {{ ev.title }}
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, markRaw } from 'vue';
import { api } from '../api';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'vue-chartjs';
import {
  Users,
  UserCheck,
  BookOpen,
  Building2,
  ChevronRight,
  User,
  GraduationCap
} from 'lucide-vue-next';

ChartJS.register(ArcElement, Tooltip, Legend);

const stats = ref([]);
const loadingStats = ref(true);
const recentStudents = ref([]);
const recentActivities = ref([]);
const genderStats = ref({ L: 0, P: 0 });
const appSettings = ref({});
const calendarEvents = ref([]);

const getImageUrl = (path) => {
  if (!path) return '';
  if (typeof path !== 'string') return '';
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path.replace(/^\//, '');
};

const getCount = (res) => {
  if (!res) return 0;
  const payload = res.data !== undefined ? res.data : res;
  if (payload?.meta?.total !== undefined) return payload.meta.total;
  if (Array.isArray(payload?.data)) return payload.data.length;
  if (Array.isArray(payload)) return payload.length;
  return 0;
};

onMounted(async () => {
  localStorage.removeItem('admin_dashboard_cache');

  try {
    const [dashboardRes, settingsRes] = await Promise.all([
      api.get('admin/dashboard').catch(() => null),
      api.get('/settings').catch(() => null),
    ]);

    if (settingsRes) {
      const s = settingsRes.data || settingsRes;
      if (s && typeof s === 'object') {
        appSettings.value = s;
      }
    }

    let dbData = dashboardRes?.data?.data || dashboardRes?.data || {};
    recentActivities.value = dbData.recent_activities || [];
    recentStudents.value = dbData.recent_students || [];
    genderStats.value = dbData.student_gender_stats || { L: 0, P: 0 };
    calendarEvents.value = dbData.calendar_events || [];

    const newStats = [
      { label: 'Total Siswa', value: dbData.students || 0, iconComp: markRaw(Users), iconBg: 'bg-indigo-50 text-indigo-600 border-indigo-100', link: '/admin/students' },
      { label: 'Total Guru', value: dbData.teachers || 0, iconComp: markRaw(UserCheck), iconBg: 'bg-purple-50 text-purple-600 border-purple-100', link: '/admin/teachers' },
      { label: 'Mata Pelajaran', value: dbData.subjects || 0, iconComp: markRaw(BookOpen), iconBg: 'bg-emerald-50 text-emerald-600 border-emerald-100', link: '/admin/subjects' },
      { label: 'Jumlah Kelas', value: dbData.classes || 0, iconComp: markRaw(Building2), iconBg: 'bg-sky-50 text-sky-600 border-sky-100', link: '/admin/classes' },
    ];
    stats.value = newStats;

    // Save cache for next 0ms load
    try {
      localStorage.setItem('admin_dashboard_cache', JSON.stringify({
        appSettings: appSettings.value,
        recentActivities: recentActivities.value,
        recentStudents: recentStudents.value,
        genderStats: genderStats.value,
        calendarEvents: calendarEvents.value,
        stats: newStats
      }));
    } catch (e) {}
  } catch (e) {
    console.error('Error fetching dashboard stats:', e);
  } finally {
    loadingStats.value = false;
  }
});

// Chart Logic (Shadcn Emerald Palette)
const chartData = computed(() => ({
  labels: ['Laki-Laki', 'Perempuan'],
  datasets: [
    {
      backgroundColor: ['#059669', '#34d399'],
      hoverBackgroundColor: ['#047857', '#10b981'],
      borderWidth: 2,
      borderColor: '#ffffff',
      data: [genderStats.value.L, genderStats.value.P]
    }
  ]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: { 
        font: { family: "'Plus Jakarta Sans', 'Inter', sans-serif", size: 11, weight: '600' },
        color: '#334155',
        usePointStyle: true,
        padding: 16
      }
    }
  }
};

// Calendar Logic
const today = new Date();
const currentMonth = today.getMonth();
const currentYear = today.getFullYear();
const currentMonthName = new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(today);
const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
const firstDayOfMonth = new Date(currentYear, currentMonth, 1).getDay() === 0 ? 6 : new Date(currentYear, currentMonth, 1).getDay() - 1;

const isToday = (date) => {
  return date === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear();
};

const colorHexMap = {
  'emerald-500': '#10b981',
  'rose-500': '#f43f5e',
  'violet-500': '#8b5cf6',
  'blue-500': '#3b82f6',
  'amber-500': '#f59e0b',
  'teal-500': '#14b8a6',
  'pink-500': '#ec4899',
  'cyan-500': '#06b6d4',
  'slate-500': '#64748b',
};

const getEventsForDate = (date) => {
  const targetDateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
  return calendarEvents.value.filter(ev => {
    const start = ev.start_date || ev.date;
    const end = ev.end_date || start;
    return targetDateStr >= start && targetDateStr <= end;
  });
};

const getCellBackgroundClass = (date) => {
  if (isToday(date)) return 'border-indigo-400 ring-2 ring-indigo-500/20';
  return 'border-slate-100 hover:border-indigo-300';
};

const getCellStyle = (date) => {
  const events = getEventsForDate(date);
  if (!events || events.length === 0) return {};
  if (events.length === 1) {
    const hex = colorHexMap[events[0].color] || '#8b5cf6';
    return { backgroundColor: hex + '15', borderColor: hex + '40' };
  }
  // 2+ events -> Split Dual Color
  const hex1 = colorHexMap[events[0].color] || '#10b981';
  const hex2 = colorHexMap[events[1].color] || '#f43f5e';
  return {
    background: `linear-gradient(135deg, ${hex1} 50%, ${hex2} 50%)`,
    borderColor: '#94a3b8'
  };
};

const getDateNumberClass = (date) => {
  const events = getEventsForDate(date);
  if (isToday(date)) return 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30';
  if (events.length >= 2) return 'bg-slate-900/60 text-white backdrop-blur-sm';
  if (events.length === 1) {
    const colorClass = events[0].color || 'violet-500';
    return `text-${colorClass.replace('500', '700')} font-black`;
  }
  return 'text-slate-700';
};

const getEventColor = (type) => {
  if (type === 'exam') return 'bg-emerald-100 text-emerald-700';
  if (type === 'holiday') return 'bg-rose-100 text-rose-700';
  return 'bg-violet-100 text-violet-700';
};
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
