<template>
  <div class="space-y-6 font-inter">

    <!-- Fresh Vibrant Emerald Hero Banner (Teacher Profile & Subjects) -->
    <div class="relative bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white rounded-2xl shadow-lg shadow-emerald-700/20 overflow-hidden border border-emerald-500/40">
      <!-- Subtle Background Mesh Grid & Glow -->
      <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.18)_1px,transparent_1px)] [background-size:22px_22px] opacity-60 pointer-events-none"></div>
      <div class="absolute -top-16 -right-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>

      <!-- Banner Content -->
      <div class="relative z-10 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
          
          <!-- Teacher Photo Frame -->
          <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white/15 backdrop-blur-md rounded-2xl border border-white/30 p-1 flex items-center justify-center flex-shrink-0 overflow-hidden relative group shadow-md">
            <img v-if="teacherInfo.photo_url && typeof teacherInfo.photo_url === 'string' && teacherInfo.photo_url.length > 5" :src="getImageUrl(teacherInfo.photo_url)" class="w-full h-full object-cover rounded-xl shadow-inner" alt="Foto Guru" />
            <div v-else class="w-full h-full rounded-xl bg-emerald-800 flex items-center justify-center text-white font-bold text-2xl uppercase">
              {{ (teacherInfo.full_name || user?.name || 'G').charAt(0) }}
            </div>
            <!-- Online status indicator -->
            <span class="absolute bottom-1 right-1 w-3.5 h-3.5 bg-emerald-300 border-2 border-emerald-800 rounded-full shadow-xs"></span>
          </div>

          <!-- Teacher Profile Info -->
          <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-full text-[11px] font-bold border border-white/30 shadow-xs">
                <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Portal Guru &bull; NIP: {{ teacherInfo.nip || '-' }}</span>
              </span>

              <!-- Jabatan Badge -->
              <span v-if="teacherInfo.position" class="inline-flex items-center gap-1 px-3 py-1 bg-amber-300/20 backdrop-blur-md text-amber-100 rounded-full text-[11px] font-bold border border-amber-300/40">
                <span>Jabatan: {{ teacherInfo.position }}</span>
              </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white uppercase leading-tight drop-shadow-xs">
              {{ teacherInfo.full_name || user?.name }}
            </h1>

            <!-- Mata Pelajaran Yang Diampu -->
            <div class="flex items-center gap-2 flex-wrap pt-0.5">
              <span class="text-xs font-semibold text-emerald-100">Mapel Diampu:</span>
              <template v-if="teacherInfo.subjects && teacherInfo.subjects.length > 0">
                <span
                  v-for="(subj, idx) in teacherInfo.subjects"
                  :key="idx"
                  class="px-2.5 py-0.5 bg-white/20 text-white text-xs font-semibold rounded-lg border border-white/30 backdrop-blur-md"
                >
                  {{ subj }}
                </span>
              </template>
              <span v-else class="text-xs text-emerald-200/80 italic">
                Belum Ditentukan
              </span>
            </div>
          </div>
        </div>

        <!-- School Name & Active TA Badge Right -->
        <div class="flex flex-col sm:flex-row items-end justify-center gap-3 flex-shrink-0">
          <button
            v-if="auth.isDualRole"
            @click="switchToStaff"
            class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-900/70 hover:bg-indigo-900 text-white text-xs font-bold rounded-xl border border-indigo-400/40 backdrop-blur-md shadow-xs transition-all active:scale-95 cursor-pointer"
            :title="`Beralih ke Dashboard ${auth.primaryRole === 'kurikulum' ? 'Kurikulum' : 'Operator TU'}`"
          >
            <Building2 class="w-4 h-4 text-indigo-200" />
            <span>Mode {{ auth.primaryRole === 'kurikulum' ? 'Kurikulum' : 'Operator TU' }} &rarr;</span>
          </button>
          <div class="hidden lg:flex flex-col items-end border-l border-white/20 pl-4 space-y-1">
            <span class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">{{ appSettings?.app_name || 'MTs AL - HASANAH' }}</span>
            <span class="px-3.5 py-1 bg-white/20 text-white text-xs font-bold rounded-xl border border-white/30 backdrop-blur-md shadow-xs">T.A. 2026 / 2027</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Attendance Card Widget (Presensi Harian Cepat GPS) -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-6 space-y-5">
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-4">
        <div class="flex items-center gap-3.5">
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 font-bold border border-emerald-200/80 shadow-2xs">
            <MapPin class="w-5 h-5 text-emerald-600" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-bold text-slate-900 tracking-tight">Presensi Kehadiran Hari Ini</h2>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                <span>GPS Live</span>
              </span>
            </div>
            <p class="text-xs text-slate-500 font-normal mt-0.5">
              Hari ini: <b class="text-slate-700 font-medium">{{ formattedToday }}</b>
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
          <RouterLink to="/teacher/presensi-recap" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shadow-2xs">
            <FileSpreadsheet class="w-4 h-4 text-emerald-700" />
            <span>Rekap Saya</span>
          </RouterLink>
          <RouterLink to="/teacher/presensi" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
            <Navigation class="w-4 h-4" />
            <span>Halaman GPS Lanjutan</span>
          </RouterLink>
        </div>
      </div>

      <!-- Quick Attendance Action Area -->
      <div v-if="loadingAttendance" class="py-6 text-center text-xs text-slate-400 font-medium">
        Mendeteksi koordinat lokasi GPS & status presensi...
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-5 items-center">
        <!-- Status Box -->
        <div class="md:col-span-2 bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 space-y-3">
          <div class="flex items-center justify-between flex-wrap gap-2">
            <span class="text-xs font-medium text-slate-500">Status Absensi Saat Ini:</span>
            <span :class="[
              todayAttendance?.status === 'hadir' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' :
              todayAttendance?.status === 'terlambat' ? 'bg-amber-100 text-amber-800 border-amber-200' :
              todayAttendance?.status === 'izin' ? 'bg-blue-100 text-blue-800 border-blue-200' :
              todayAttendance?.status === 'sakit' ? 'bg-rose-100 text-rose-800 border-rose-200' :
              'bg-slate-200 text-slate-700 border-slate-300',
              'px-2.5 py-0.5 rounded-full text-xs uppercase font-bold border'
            ]">
              {{ todayAttendance ? todayAttendance.status.replace('_', ' ') : 'Belum Absen Masuk' }}
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 pt-0.5">
            <div class="p-3 bg-white rounded-xl border border-slate-200/80 text-xs shadow-2xs">
              <span class="text-[10px] text-slate-400 font-bold uppercase">Jam Masuk:</span>
              <p class="font-bold text-slate-900 font-mono text-sm mt-0.5">
                {{ todayAttendance?.check_in_time || '-' }}
              </p>
            </div>
            <div class="p-3 bg-white rounded-xl border border-slate-200/80 text-xs shadow-2xs">
              <span class="text-[10px] text-slate-400 font-bold uppercase">Jam Pulang:</span>
              <p class="font-bold text-slate-900 font-mono text-sm mt-0.5">
                {{ todayAttendance?.check_out_time || '-' }}
              </p>
            </div>
          </div>

          <!-- GPS Distance Notice -->
          <div class="text-xs flex items-center justify-between text-slate-500 pt-0.5">
            <span class="flex items-center gap-1">
              <MapPin class="w-3.5 h-3.5 text-slate-400" />
              <span>Jarak GPS Sekolah: <b class="text-slate-900 font-mono font-bold">{{ currentDistance }} meter</b></span>
            </span>
            <span v-if="inRadius" class="inline-flex items-center gap-1 text-emerald-700 font-bold">
              <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
              <span>Dalam Radius</span>
            </span>
            <span v-else class="inline-flex items-center gap-1 text-amber-700 font-bold">
              <AlertCircle class="w-3.5 h-3.5 text-amber-600" />
              <span>Di Luar Radius</span>
            </span>
          </div>
        </div>

        <!-- Action Button -->
        <div class="flex flex-col gap-2.5">
          <button
            v-if="!todayAttendance?.check_in_time"
            @click="submitQuickAttendance"
            :disabled="submittingAttendance"
            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-98"
          >
            <Navigation class="w-4 h-4" />
            <span>{{ submittingAttendance ? 'Memproses...' : 'ABSEN MASUK (GPS)' }}</span>
          </button>

          <button
            v-else-if="!todayAttendance?.check_out_time"
            @click="submitQuickAttendance"
            :disabled="submittingAttendance"
            class="w-full py-3 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-700/20 transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-98"
          >
            <Clock class="w-4 h-4" />
            <span>{{ submittingAttendance ? 'Memproses...' : 'ABSEN PULANG (GPS)' }}</span>
          </button>

          <div v-else class="p-3 bg-emerald-50 border border-emerald-200/80 rounded-xl text-center text-emerald-800 text-xs font-medium space-y-0.5 shadow-2xs">
            <p class="font-bold flex items-center justify-center gap-1 text-emerald-800">
              <CheckCircle2 class="w-4 h-4 text-emerald-600" />
              <span>Presensi Hari Ini Selesai</span>
            </p>
            <p class="text-[11px] text-emerald-600">Terima kasih atas dedikasi Bapak/Ibu Guru!</p>
          </div>

          <RouterLink to="/teacher/presensi" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs text-center transition-all">
            Ajukan Koreksi / Fallback GPS
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Shadcn Stat Cards Grid (Unified Emerald Icon Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      
      <!-- CASE A: Jika Guru Adalah Wali Kelas -->
      <template v-if="stats.classesCount > 0">
        <!-- Stat 1: Total Siswa Binaan -->
        <div class="shadcn-card p-5 flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Siswa Binaan</p>
            <p class="text-2xl font-bold tracking-tight text-slate-900">
              {{ loading ? '—' : stats.studentsCount }}
            </p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center flex-shrink-0 shadow-2xs">
            <Users class="w-5 h-5" />
          </div>
        </div>

        <!-- Stat 2: Kelas Diampu -->
        <div class="shadcn-card p-5 flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Wali Kelas Di</p>
            <p class="text-2xl font-bold tracking-tight text-slate-900">
              {{ loading ? '—' : stats.classesCount }} Kelas
            </p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center flex-shrink-0 shadow-2xs">
            <Building2 class="w-5 h-5" />
          </div>
        </div>

        <!-- Stat 3: Total Jadwal Mengajar -->
        <div class="shadcn-card p-5 flex items-center justify-between">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal Mengajar</p>
            <p class="text-2xl font-bold tracking-tight text-slate-900">
              {{ loading ? '—' : stats.schedulesCount }}
            </p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center flex-shrink-0 shadow-2xs">
            <CalendarCheck class="w-5 h-5" />
          </div>
        </div>
      </template>

      <!-- CASE B: Jika Guru Bukan Wali Kelas (Tampilan Seimbang 3 Kartu Shadcn) -->
      <template v-else>
        <!-- Stat 1: Total Jadwal Mengajar -->
        <RouterLink to="/teacher/schedules" class="shadcn-card p-5 flex items-center justify-between cursor-pointer hover:border-emerald-300 hover:shadow-md transition-all">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jadwal Mengajar</p>
            <p class="text-2xl font-bold tracking-tight text-slate-900">
              {{ loading ? '—' : stats.schedulesCount }}
            </p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center flex-shrink-0 shadow-2xs">
            <CalendarCheck class="w-5 h-5" />
          </div>
        </RouterLink>

        <!-- Stat 2: Rekap Kehadiran Saya -->
        <RouterLink to="/teacher/presensi-recap" class="shadcn-card p-5 flex items-center justify-between cursor-pointer hover:border-emerald-300 hover:shadow-md transition-all">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rekap Absensi Saya</p>
            <p class="text-base font-bold tracking-tight text-slate-900">Laporan Bulanan</p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center flex-shrink-0 shadow-2xs">
            <FileSpreadsheet class="w-5 h-5" />
          </div>
        </RouterLink>

        <!-- Stat 3: Kalender Akademik -->
        <RouterLink to="/teacher/calendar" class="shadcn-card p-5 flex items-center justify-between cursor-pointer hover:border-emerald-300 hover:shadow-md transition-all">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kalender Akademik</p>
            <p class="text-base font-bold tracking-tight text-slate-900">Agenda Sekolah</p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center flex-shrink-0 shadow-2xs">
            <Calendar class="w-5 h-5" />
          </div>
        </RouterLink>
      </template>

    </div>

    <!-- Kelas Diampu / List Section (HANYA DITAMPILKAN JIKA GURU ADALAH WALI KELAS) -->
    <div v-if="stats.classesCount > 0" class="bg-white rounded-[2.5rem] shadow-[0_4px_25px_rgb(0,0,0,0.03)] border border-slate-100 p-8">
      <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          </div>
          <div>
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider font-lexend">Daftar Kelas Sebagai Wali Kelas</h3>
            <p class="text-xs text-slate-400 font-medium">Kelas yang berada di bawah bimbingan Anda</p>
          </div>
        </div>
        <RouterLink to="/teacher/students" class="px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5">
          <span>Kelola Siswa</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </RouterLink>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 text-slate-400">
              <th class="pb-3 text-[10px] font-black uppercase tracking-widest">NAMA KELAS</th>
              <th class="pb-3 text-[10px] font-black uppercase tracking-widest">TAHUN AJARAN</th>
              <th class="pb-3 text-[10px] font-black uppercase tracking-widest text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-if="loading">
              <td colspan="3" class="py-8 text-center text-xs text-slate-400 font-medium">Memuat data kelas...</td>
            </tr>
            <template v-else>
              <tr v-for="cls in classesList" :key="cls.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="py-4 text-xs font-bold text-slate-800 flex items-center gap-3">
                  <span class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 font-bold text-xs flex items-center justify-center">
                    {{ cls.name.charAt(0) }}
                  </span>
                  <span>{{ cls.name }}</span>
                </td>
                <td class="py-4 text-xs font-semibold text-slate-600">
                  {{ cls.academicYear ? (cls.academicYear.year + ' - ' + (cls.academicYear.semester === 'odd' ? 'Ganjil' : 'Genap')) : '-' }}
                </td>
                <td class="py-4 text-right">
                  <RouterLink :to="`/teacher/students?class_id=${cls.id}`" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-bold rounded-lg transition-colors">
                    Lihat Data Siswa
                  </RouterLink>
                </td>
              </tr>
              <tr v-if="classesList.length === 0">
                <td colspan="3" class="py-8 text-center text-xs text-slate-400 font-medium">Anda belum ditugaskan sebagai Wali Kelas.</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '../stores/auth';
import { useToast } from '../composables/useToast';
import { api } from '../api';
import {
  Users,
  Building2,
  CalendarCheck,
  MapPin,
  Calendar,
  FileSpreadsheet,
  Clock,
  CheckCircle2,
  AlertCircle,
  XCircle,
  Camera,
  Navigation
} from 'lucide-vue-next';

const toast = useToast();
const auth = useAuthStore();
const router = useRouter();
const { user } = storeToRefs(auth);

function switchToStaff() {
  const target = auth.primaryRole || 'operator';
  auth.switchRole(target);
  if (target === 'kurikulum') {
    router.push('/kurikulum/dashboard');
  } else if (target === 'operator') {
    router.push('/operator/dashboard');
  } else {
    router.push('/admin/dashboard');
  }
}

const loading = ref(true);
const loadingAttendance = ref(true);
const submittingAttendance = ref(false);
const appSettings = ref({});

const teacherInfo = reactive({
  full_name: '',
  nip: '',
  position: '',
  photo_url: null,
  subjects: [],
});

const stats = reactive({
  classesCount: 0,
  studentsCount: 0,
  schedulesCount: 0,
});

const classesList = ref([]);
const todayAttendance = ref(null);
const setting = ref(null);
const currentLat = ref(null);
const currentLng = ref(null);
const currentDistance = ref(0);

const formattedToday = computed(() => {
  return new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
});

const inRadius = computed(() => {
  if (!setting.value) return true;
  return currentDistance.value <= (setting.value.max_radius_meters || 100);
});

function getImageUrl(path) {
  if (!path) return '';
  if (typeof path !== 'string') return '';
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path.replace(/^\//, '');
}

function calculateHaversine(lat1, lon1, lat2, lon2) {
  const R = 6371000;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return Math.round(R * c);
}

function detectLocation() {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      currentLat.value = pos.coords.latitude;
      currentLng.value = pos.coords.longitude;
      if (setting.value?.latitude && setting.value?.longitude) {
        currentDistance.value = calculateHaversine(
          currentLat.value,
          currentLng.value,
          setting.value.latitude,
          setting.value.longitude
        );
      }
    },
    () => {
      if (setting.value?.latitude && setting.value?.longitude) {
        currentLat.value = setting.value.latitude;
        currentLng.value = setting.value.longitude;
        currentDistance.value = 0;
      }
    },
    { enableHighAccuracy: true, timeout: 5000 }
  );
}

async function loadTodayAttendance() {
  loadingAttendance.value = true;
  try {
    const res = await api.get('teacher/presensi/today');
    const data = res?.data || res;
    todayAttendance.value = data.attendance || null;
    setting.value = data.setting || null;

    detectLocation();
  } catch (err) {
    console.error('Failed to load today attendance', err);
  } finally {
    loadingAttendance.value = false;
  }
}

async function submitQuickAttendance() {
  submittingAttendance.value = true;
  try {
    const payload = {
      latitude: currentLat.value || setting.value?.latitude,
      longitude: currentLng.value || setting.value?.longitude,
      status: 'hadir',
    };
    const res = await api.post('teacher/presensi', payload);
    toast.success(res.message || res.data?.message || 'Presensi berhasil dicatat!');
    await loadTodayAttendance();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal melakukan presensi.');
  } finally {
    submittingAttendance.value = false;
  }
}

onMounted(async () => {
  try {
    const [settRes, dashRes, clsRes] = await Promise.all([
      api.get('/settings').catch(() => null),
      api.get('teacher/dashboard').catch(() => null),
      api.get('teacher/classes').catch(() => null),
    ]);

    if (settRes?.data) {
      appSettings.value = settRes.data;
    }

    if (dashRes?.data) {
      const data = dashRes.data;
      if (data.teacher) {
        teacherInfo.full_name = data.teacher.full_name || user.value?.name || '';
        teacherInfo.nip = data.teacher.nip || '';
        teacherInfo.position = data.teacher.position || '';
        teacherInfo.photo_url = data.teacher.photo_url || null;
        teacherInfo.subjects = data.teacher.subjects || [];
      }
      stats.classesCount = data.classes_count || 0;
      stats.studentsCount = data.students_count || 0;
      stats.schedulesCount = data.schedules_count || 0;
    }

    if (clsRes) {
      classesList.value = Array.isArray(clsRes) ? clsRes : (clsRes.data || []);
    }

    const isHomeroom = (stats.classesCount > 0 || classesList.value.length > 0);
    localStorage.setItem('is_homeroom_teacher', isHomeroom ? 'true' : 'false');

    await loadTodayAttendance();

  } catch (err) {
    console.error('Error fetching teacher dashboard data:', err);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
