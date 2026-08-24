<template>
  <div class="space-y-6 font-inter">
    <!-- Top Header Card -->
    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100/80 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
      <div class="space-y-2 z-10">
        <div class="inline-flex items-center gap-2 px-3.5 py-1 bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs font-bold rounded-full">
          <UserCheck class="w-3.5 h-3.5 text-emerald-600" />
          <span>Akun Guru</span>
          <span class="text-emerald-300">•</span>
          <span>{{ teacherName || 'Guru Pengajar' }}</span>
        </div>
        <h1 class="text-2xl font-black text-slate-800 font-lexend uppercase tracking-wider">Jadwal Mengajar Saya</h1>
        <p class="text-xs text-slate-500 font-medium max-w-xl">
          Daftar ringkas hari dan jam mengajar Anda di sekolah secara terstruktur.
        </p>
      </div>

      <!-- Quick Summary Stats -->
      <div class="flex items-center gap-4 z-10">
        <div class="bg-slate-900 text-white rounded-2xl p-4 min-w-[130px] text-center shadow-lg shadow-slate-900/10">
          <span class="text-2xl font-black font-lexend block text-emerald-400">{{ totalTeachingSlots }}</span>
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Sesi / Mgg</span>
        </div>

        <div class="bg-emerald-600 text-white rounded-2xl p-4 min-w-[130px] text-center shadow-lg shadow-emerald-600/20">
          <span class="text-2xl font-black font-lexend block text-white">{{ todaySlotsCount }}</span>
          <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-100">Sesi Hari Ini</span>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-[2.5rem] p-12 text-center text-slate-400 text-xs font-medium border border-slate-100">
      <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-3"></div>
      Memuat jadwal mengajar...
    </div>

    <!-- Cards Grid per Day -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="day in daysList"
        :key="day.key"
        :class="[
          isToday(day.key)
            ? 'bg-white border-2 border-emerald-500 shadow-xl shadow-emerald-500/10 ring-4 ring-emerald-500/10'
            : 'bg-white border border-slate-100/90 shadow-sm',
          'rounded-[2.5rem] overflow-hidden flex flex-col transition-all duration-300'
        ]"
      >
        <!-- Day Card Header -->
        <div
          :class="[
            isToday(day.key)
              ? 'bg-emerald-600 text-white'
              : 'bg-[#111827] text-white',
            'px-6 py-4 flex items-center justify-between gap-3'
          ]"
        >
          <div class="flex items-center gap-2.5">
            <span class="w-2.5 h-2.5 rounded-full" :class="isToday(day.key) ? 'bg-amber-300 animate-pulse' : 'bg-emerald-400'"></span>
            <h3 class="font-lexend font-black uppercase text-sm tracking-wider">{{ day.name }}</h3>
            <span
              v-if="isToday(day.key)"
              class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-900 text-[9px] font-black uppercase tracking-wider shadow-xs"
            >
              HARI INI
            </span>
          </div>

          <span class="text-[10px] font-black px-2.5 py-1 bg-white/10 rounded-full text-white/90">
            {{ getTeacherDaySchedules(day.key).length }} Jam Mengajar
          </span>
        </div>

        <!-- Schedule Items List -->
        <div class="p-6 flex-1 space-y-3 bg-slate-50/40">
          <!-- Empty State for Day -->
          <div
            v-if="getTeacherDaySchedules(day.key).length === 0"
            class="py-10 text-center text-slate-400 text-xs font-medium border-2 border-dashed border-slate-200/80 rounded-2xl flex flex-col items-center justify-center gap-1.5"
          >
            <Clock class="w-7 h-7 text-slate-300" />
            <span class="font-bold text-slate-500">Tidak Ada Jam Mengajar</span>
            <span class="text-[10px] text-slate-400">Jam Bebas / Persiapan Materi</span>
          </div>

          <!-- Schedule Item Cards -->
          <div
            v-for="item in getTeacherDaySchedules(day.key)"
            :key="item.id"
            class="p-4 rounded-2xl border bg-white border-slate-200/70 shadow-2xs hover:border-emerald-300 hover:shadow-md transition-all space-y-2.5"
          >
            <!-- Time Badge & Class Badge -->
            <div class="flex items-center justify-between gap-2">
              <span class="px-2.5 py-1 bg-slate-900 text-white font-mono text-[11px] font-extrabold rounded-lg shadow-2xs flex items-center gap-1.5">
                <Clock class="w-3 h-3 text-emerald-300" />
                <span>{{ item.start_time }} - {{ item.end_time }}</span>
              </span>

              <span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 font-lexend text-xs font-black rounded-lg border border-emerald-200 flex items-center gap-1.5">
                <Building2 class="w-3 h-3 text-emerald-600" />
                <span>{{ getClassName(item) }}</span>
              </span>
            </div>

            <!-- Subject & Details -->
            <div class="pt-1">
              <h4 class="font-extrabold text-sm text-slate-900 uppercase tracking-wide leading-snug">
                {{ item.subject?.name || item.activity_name }}
              </h4>
              
              <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                <span v-if="item.room" class="flex items-center gap-1 text-slate-600 font-semibold">
                  <MapPin class="w-3 h-3 text-slate-400" />
                  <span>Ruang: {{ item.room }}</span>
                </span>
                <span v-else class="text-slate-400">Gedung Utama</span>

                <span v-if="getDurationBadge(item)" class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-black border border-amber-200">
                  {{ getDurationBadge(item) }}
                </span>
              </div>
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
  Building2,
  MapPin,
} from 'lucide-vue-next';

const toast = useToast();
const loading = ref(true);
const teacherName = ref('');
const schedules = ref([]);

const daysList = [
  { key: 'senin', name: 'Senin' },
  { key: 'selasa', name: 'Selasa' },
  { key: 'rabu', name: 'Rabu' },
  { key: 'kamis', name: 'Kamis' },
  { key: 'jumat', name: 'Jumat' },
  { key: 'sabtu', name: 'Sabtu' },
];

const totalTeachingSlots = computed(() => {
  return schedules.value.filter(s => !s.is_activity).length;
});

const todaySlotsCount = computed(() => {
  const dayNames = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
  const todayKey = dayNames[new Date().getDay()];
  return schedules.value.filter(s => s.day?.toLowerCase() === todayKey && !s.is_activity).length;
});

function isToday(dayKey) {
  const dayNames = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
  const todayKey = dayNames[new Date().getDay()];
  return dayKey === todayKey;
}

function getTeacherDaySchedules(dayKey) {
  return schedules.value.filter(s => {
    return s.day?.toLowerCase() === dayKey && !s.is_activity;
  });
}

function getClassName(item) {
  const name = item.class_room?.name || item.classRoom?.name || item.class?.name;
  return name ? `Kelas ${name}` : 'Semua Kelas';
}

function getDurationBadge(item) {
  if (!item?.start_time || !item?.end_time) return null;
  const [sh, sm] = item.start_time.split(':').map(Number);
  const [eh, em] = item.end_time.split(':').map(Number);
  const diffMinutes = (eh * 60 + em) - (sh * 60 + sm);
  if (diffMinutes >= 120) return '3 Jam Blok';
  if (diffMinutes >= 80) return '2 Jam Ganda';
  return null;
}

const fetchSchedules = async () => {
  loading.value = true;
  try {
    const res = await api.get('/teacher/schedules');
    const data = res?.data || res || {};
    teacherName.value = data.teacher?.full_name || '';
    schedules.value = data.schedules || [];
  } catch (err) {
    toast.error('Gagal memuat jadwal mengajar guru.');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchSchedules();
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
