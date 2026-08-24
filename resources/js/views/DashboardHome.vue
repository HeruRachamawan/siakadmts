<template>
  <div v-if="auth.loading && auth.token" class="text-center py-10 text-gray-500">
    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
    <p class="mt-2">Memuat...</p>
  </div>
  <div v-else class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
      <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
      <p class="text-gray-500 mt-1">Selamat datang kembali, {{ user?.name || 'Pengguna' }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="(stat, index) in statsCards" :key="index" class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 flex items-center gap-4 hover:-translate-y-1 transition-transform">
        <div :class="`w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 ${stat.bgColor} ${stat.textColor}`">
          <component :is="stat.icon" class="w-7 h-7" />
        </div>
        <div>
          <div class="text-3xl font-black font-lexend text-slate-800">{{ dashboardData?.[stat.key] || 0 }}</div>
          <div class="text-sm font-bold text-slate-500 uppercase tracking-wide">{{ stat.label }}</div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Chart -->
      <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
        <h3 class="text-xl font-bold font-lexend text-slate-800 mb-6">Distribusi Siswa (Gender)</h3>
        <div class="h-64 flex items-center justify-center">
          <Pie v-if="chartData.datasets[0].data.length" :data="chartData" :options="chartOptions" />
          <div v-else class="text-slate-400">Memuat grafik...</div>
        </div>
      </div>

      <!-- Timeline -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
        <h3 class="text-xl font-bold font-lexend text-slate-800 mb-6">Aktivitas Terbaru</h3>
        <div class="space-y-6">
          <div v-for="(activity, index) in dashboardData?.recent_activities" :key="index" class="flex gap-4 relative">
            <div v-if="index !== dashboardData.recent_activities.length - 1" class="absolute left-5 top-10 bottom-0 w-0.5 bg-slate-100"></div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0 relative z-10 text-emerald-600">
              <svg v-if="activity.icon === 'user'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>
            </div>
            <div>
              <p class="font-bold text-slate-800 text-sm">{{ activity.title }}</p>
              <p class="text-xs text-slate-500 mt-1">{{ activity.description }}</p>
            </div>
          </div>
          <div v-if="!dashboardData?.recent_activities?.length" class="text-slate-400 text-sm text-center py-4">Belum ada aktivitas.</div>
        </div>
      </div>

      <!-- Academic Calendar -->
      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 lg:col-span-3">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold font-lexend text-slate-800">Kalender Akademik</h3>
          <div class="text-sm font-bold text-emerald-700 bg-emerald-50 px-4 py-2 rounded-lg border border-emerald-100">{{ currentMonthName }} {{ currentYear }}</div>
        </div>
        <div class="grid grid-cols-7 gap-2">
          <div v-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day" class="text-center text-xs font-bold text-slate-400 py-2 uppercase tracking-wider">
            {{ day }}
          </div>
          <div v-for="blank in firstDayOfMonth" :key="'blank-'+blank" class="h-16 md:h-24 rounded-xl border border-transparent"></div>
          <div v-for="date in daysInMonth" :key="'date-'+date" 
            class="h-16 md:h-24 rounded-xl border border-slate-100 p-2 flex flex-col hover:border-emerald-300 hover:shadow-md transition-all cursor-pointer group"
            :class="{'bg-emerald-50 border-emerald-200': isToday(date)}">
            <span class="text-sm font-bold w-7 h-7 flex items-center justify-center rounded-full group-hover:bg-emerald-600 group-hover:text-white transition-colors" :class="isToday(date) ? 'bg-emerald-600 text-white' : 'text-slate-700'">
              {{ date }}
            </span>
            <div v-if="date % 5 === 0" class="mt-auto hidden md:block text-[10px] bg-emerald-100 text-emerald-700 font-bold px-2 py-1 rounded truncate">Ujian</div>
            <div v-else-if="date % 7 === 0" class="mt-auto hidden md:block text-[10px] bg-rose-100 text-rose-700 font-bold px-2 py-1 rounded truncate">Libur</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, shallowRef } from 'vue';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '../stores/auth';
import { api } from '../api';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import { Pie } from 'vue-chartjs';

ChartJS.register(ArcElement, Tooltip, Legend);

const auth = useAuthStore();
const { user } = storeToRefs(auth);
const dashboardData = ref(null);

// Calendar logic
const today = new Date();
const currentMonth = today.getMonth();
const currentYear = today.getFullYear();
const currentMonthName = new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(today);
const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
const firstDayOfMonth = new Date(currentYear, currentMonth, 1).getDay() === 0 ? 6 : new Date(currentYear, currentMonth, 1).getDay() - 1;

const isToday = (date) => {
  return date === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear();
};

const chartData = computed(() => ({
  labels: ['Laki-Laki', 'Perempuan'],
  datasets: [
    {
      backgroundColor: ['#6366f1', '#ec4899'],
      data: [
        dashboardData.value?.student_gender_stats?.L || 0,
        dashboardData.value?.student_gender_stats?.P || 0
      ]
    }
  ]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
};

const IconUsers = shallowRef({
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>`
});
const IconBook = shallowRef({
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" /></svg>`
});

const statsCards = [
  { key: 'students', label: 'Siswa', icon: IconUsers, bgColor: 'bg-indigo-100', textColor: 'text-indigo-600' },
  { key: 'teachers', label: 'Guru', icon: IconUsers, bgColor: 'bg-emerald-100', textColor: 'text-emerald-600' },
  { key: 'classes', label: 'Kelas', icon: IconBook, bgColor: 'bg-amber-100', textColor: 'text-amber-600' },
  { key: 'subjects', label: 'Mata Pelajaran', icon: IconBook, bgColor: 'bg-rose-100', textColor: 'text-rose-600' },
];

onMounted(async () => {
  try {
    const res = await api.get('admin/dashboard');
    dashboardData.value = res.data?.data || res.data;
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
  }
});
</script>
