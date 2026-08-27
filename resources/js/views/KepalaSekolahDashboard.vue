<template>
  <div class="space-y-6 font-inter">
    <!-- Executive Royal Hero Banner (Kepala Madrasah) -->
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-emerald-950 text-white rounded-3xl shadow-xl shadow-slate-900/20 overflow-hidden border border-emerald-500/30">
      <!-- Background Mesh Glow -->
      <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.12)_1px,transparent_1px)] [background-size:24px_24px] opacity-60 pointer-events-none"></div>
      <div class="absolute -top-20 -right-20 w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Banner Content -->
      <div class="relative z-10 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
          <!-- Photo Frame -->
          <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white/10 backdrop-blur-md rounded-2xl border-2 border-amber-400/50 p-1 flex items-center justify-center flex-shrink-0 overflow-hidden relative shadow-xl">
            <img
              v-if="userPhoto"
              :src="getImageUrl(userPhoto)"
              class="w-full h-full object-cover rounded-xl"
              alt="Foto Kepala Madrasah"
            />
            <div v-else class="w-full h-full rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-slate-950 font-black text-2xl uppercase shadow-inner">
              {{ (auth.user?.name || 'K').charAt(0) }}
            </div>
            <!-- Online status indicator -->
            <span class="absolute bottom-1 right-1 w-3.5 h-3.5 bg-emerald-400 border-2 border-slate-950 rounded-full shadow-xs"></span>
          </div>

          <!-- Profile Details -->
          <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-400/20 backdrop-blur-md text-amber-300 rounded-full text-[11px] font-bold border border-amber-400/40 shadow-xs">
                <ShieldCheck class="w-3.5 h-3.5 text-amber-300" />
                <span>KEPALA MADRASAH &bull; {{ auth.user?.teacher?.nip ? `NIP: ${auth.user.teacher.nip}` : `@${auth.user?.username}` }}</span>
              </span>
              <span class="inline-flex items-center gap-1 px-3 py-1 bg-white/10 backdrop-blur-md text-emerald-200 rounded-full text-[11px] font-bold border border-white/20">
                <CalendarDays class="w-3 h-3 text-emerald-300" />
                <span>{{ overview.active_academic_year ? `T.A. ${overview.active_academic_year.year} (${overview.active_academic_year.semester})` : 'Tahun Ajaran Aktif' }}</span>
              </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white uppercase leading-tight font-lexend">
              {{ auth.user?.name || 'Kepala Madrasah' }}
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm font-normal max-w-2xl leading-relaxed">
              Dashboard Eksekutif: Pantau performa kehadiran guru & siswa secara real-time, capaian kurikulum, progres PPDB, serta agenda strategis madrasah.
            </p>
          </div>
        </div>

        <!-- Quick Executive Action Buttons -->
        <div class="flex flex-wrap md:flex-col lg:flex-row gap-2.5 flex-shrink-0">
          <RouterLink
            to="/admin/print-center"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md transition-all active:scale-95 cursor-pointer whitespace-nowrap"
          >
            <Printer class="w-4 h-4" />
            <span>Pusat Cetak Dokumen</span>
          </RouterLink>

          <RouterLink
            to="/admin/teacher-presensi-monitoring"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md transition-all active:scale-95 cursor-pointer whitespace-nowrap"
          >
            <MapPin class="w-4 h-4" />
            <span>Monitoring Absen Guru</span>
          </RouterLink>

          <RouterLink
            to="/admin/grades"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/15 hover:bg-white/25 text-white border border-white/20 backdrop-blur-md transition-all active:scale-95 cursor-pointer whitespace-nowrap shadow-xs"
          >
            <Award class="w-4 h-4" />
            <span>Rekap Nilai Siswa</span>
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Siswa -->
      <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm space-y-2 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Siswa Aktif</span>
          <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <GraduationCap class="w-5 h-5" />
          </div>
        </div>
        <p class="text-3xl font-black text-slate-800 font-lexend">{{ overview.total_students || 0 }}</p>
        <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-500 pt-1 border-t border-slate-50">
          <span>👦 L: {{ overview.students_male || 0 }}</span>
          <span>👧 P: {{ overview.students_female || 0 }}</span>
          <span>🏫 {{ overview.total_classes || 0 }} Rombel</span>
        </div>
      </div>

      <!-- Total Dewan Guru & Staf -->
      <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm space-y-2 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Guru & Tenaga Kependidikan</span>
          <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <UserCheck class="w-5 h-5" />
          </div>
        </div>
        <p class="text-3xl font-black text-slate-800 font-lexend">{{ overview.total_teachers || 0 }}</p>
        <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-500 pt-1 border-t border-slate-50">
          <span>👨‍🏫 {{ overview.total_teachers || 0 }} Guru</span>
          <span>🏢 {{ overview.total_staff || 0 }} Staf Jabatan</span>
        </div>
      </div>

      <!-- Presensi Guru Hari Ini -->
      <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm space-y-2 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kehadiran Guru Hari Ini</span>
          <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
            <Clock class="w-5 h-5" />
          </div>
        </div>
        <div class="flex items-baseline gap-2">
          <p class="text-3xl font-black text-teal-600 font-lexend">{{ teacherAttendance.attendance_rate || 0 }}%</p>
          <span v-if="teacherAttendance.is_holiday" class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-600 border border-rose-200">
            Libur
          </span>
        </div>
        <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-500 pt-1 border-t border-slate-50">
          <span class="text-emerald-600 font-bold">H: {{ teacherAttendance.hadir + teacherAttendance.terlambat }}</span>
          <span class="text-amber-600">I/S: {{ teacherAttendance.izin + teacherAttendance.sakit }}</span>
          <span class="text-rose-500">Belum: {{ teacherAttendance.belum_absen }}</span>
        </div>
      </div>

      <!-- Presensi Siswa Hari Ini -->
      <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm space-y-2 hover:shadow-md transition-all">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kehadiran Siswa Hari Ini</span>
          <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <Users class="w-5 h-5" />
          </div>
        </div>
        <p class="text-3xl font-black text-blue-600 font-lexend">{{ studentAttendance.attendance_rate || 0 }}%</p>
        <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-500 pt-1 border-t border-slate-50">
          <span class="text-emerald-600 font-bold">H: {{ studentAttendance.hadir }}</span>
          <span class="text-amber-600">I/S: {{ studentAttendance.izin + studentAttendance.sakit }}</span>
          <span class="text-rose-600">A: {{ studentAttendance.alpa }}</span>
        </div>
      </div>
    </div>

    <!-- Main Grid: Live Monitoring Presensi & Kinerja Kurikulum -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left 2 Cols: Real-time Attendance & Supervision -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Live Monitoring Dewan Guru Hari Ini -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-5">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
                <MapPin class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-black text-slate-800 font-lexend">Pengawasan Presensi Dewan Guru Hari Ini</h3>
                <p class="text-xs text-slate-400 font-medium">Status kedisiplinan dan kehadiran guru madrasah hari ini</p>
              </div>
            </div>

            <RouterLink 
              to="/admin/teacher-presensi-monitoring" 
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all cursor-pointer w-fit"
            >
              <span>Detail Monitoring</span>
              <ArrowRight class="w-3.5 h-3.5" />
            </RouterLink>
          </div>

          <!-- Holiday Banner if Today is Holiday -->
          <div v-if="teacherAttendance.is_holiday" class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-900 flex items-center gap-3">
            <Calendar class="w-5 h-5 text-amber-600 flex-shrink-0" />
            <div class="text-xs">
              <p class="font-bold">Hari Ini Ditetapkan Libur ({{ teacherAttendance.holiday_name }})</p>
              <p class="text-amber-700 text-[11px]">Presensi harian guru tidak diwajibkan.</p>
            </div>
          </div>

          <!-- Teacher Stats Badges Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-emerald-50/60 border border-emerald-100 rounded-2xl p-3.5 text-center space-y-1">
              <span class="text-[10px] font-bold text-emerald-700 uppercase">Tepat Waktu</span>
              <p class="text-2xl font-black text-emerald-800 font-lexend">{{ teacherAttendance.hadir || 0 }}</p>
            </div>

            <div class="bg-amber-50/60 border border-amber-100 rounded-2xl p-3.5 text-center space-y-1">
              <span class="text-[10px] font-bold text-amber-700 uppercase">Terlambat</span>
              <p class="text-2xl font-black text-amber-800 font-lexend">{{ teacherAttendance.terlambat || 0 }}</p>
            </div>

            <div class="bg-blue-50/60 border border-blue-100 rounded-2xl p-3.5 text-center space-y-1">
              <span class="text-[10px] font-bold text-blue-700 uppercase">Izin / Sakit</span>
              <p class="text-2xl font-black text-blue-800 font-lexend">{{ (teacherAttendance.izin || 0) + (teacherAttendance.sakit || 0) }}</p>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-2xl p-3.5 text-center space-y-1">
              <span class="text-[10px] font-bold text-purple-700 uppercase">Tugas Luar</span>
              <p class="text-2xl font-black text-purple-800 font-lexend">{{ teacherAttendance.tugas_luar || 0 }}</p>
            </div>

            <div class="bg-rose-50/60 border border-rose-100 rounded-2xl p-3.5 text-center space-y-1 col-span-2 sm:col-span-1">
              <span class="text-[10px] font-bold text-rose-700 uppercase">Belum Absen</span>
              <p class="text-2xl font-black text-rose-800 font-lexend">{{ teacherAttendance.belum_absen || 0 }}</p>
            </div>
          </div>
        </div>

        <!-- Supervisi Progres Penilaian & Kurikulum -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-5">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 border border-blue-500/20 flex items-center justify-center flex-shrink-0">
                <Award class="w-5 h-5" />
              </div>
              <div>
                <h3 class="text-base font-black text-slate-800 font-lexend">Supervisi Progres Penginputan Nilai</h3>
                <p class="text-xs text-slate-400 font-medium">Progres ketuntasan nilai raport siswa oleh guru pengajar</p>
              </div>
            </div>

            <RouterLink 
              to="/admin/grades" 
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition-all cursor-pointer"
            >
              <span>Rekap Nilai</span>
              <ArrowRight class="w-3.5 h-3.5" />
            </RouterLink>
          </div>

          <div class="space-y-3">
            <div class="flex items-center justify-between text-xs font-bold">
              <span class="text-slate-600">Total Nilai Terinput</span>
              <span class="text-blue-600 font-lexend text-sm">{{ gradingProgress.total_grades || 0 }} Nilai</span>
            </div>
            <!-- Progress Bar -->
            <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
              <div 
                class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-1000"
                :style="{ width: `${Math.min(100, Math.max(5, gradingProgress.percentage || 0))}%` }"
              ></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400">
              <span>Capaian Sistem</span>
              <span class="font-bold text-slate-700">{{ gradingProgress.percentage || 0 }}% Selesai</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 1 Col: PPDB, Letters, & Calendar -->
      <div class="space-y-6">
        <!-- Ringkasan PPDB & Persuratan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-black text-slate-800 font-lexend uppercase tracking-wider flex items-center gap-2">
              <BookOpen class="w-4 h-4 text-emerald-600" />
              <span>PPDB & Persuratan</span>
            </h3>
            <RouterLink to="/admin/ppdb" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Lihat PPDB</RouterLink>
          </div>

          <!-- PPDB Status -->
          <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100 space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-emerald-900">Pendaftar PPDB</span>
              <span class="text-sm font-black text-emerald-700 font-lexend">{{ ppdb.total || 0 }} / {{ ppdb.target_quota || 160 }}</span>
            </div>
            <div class="w-full h-2 bg-emerald-200/50 rounded-full overflow-hidden">
              <div class="h-full bg-emerald-600 rounded-full" :style="{ width: `${Math.min(100, ppdb.progress_percentage || 0)}%` }"></div>
            </div>
            <p class="text-[10px] text-emerald-700 font-medium">{{ ppdb.verified || 0 }} berkas terverifikasi &bull; {{ ppdb.accepted || 0 }} siswa diterima</p>
          </div>

          <!-- Letters Summary -->
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
            <span class="text-xs font-bold text-slate-700 block">Agenda Surat Bulan Ini</span>
            <div class="grid grid-cols-2 gap-2 text-center">
              <div class="bg-white p-2.5 rounded-xl border border-slate-200/60 shadow-2xs">
                <span class="text-[10px] font-bold text-slate-400">SURAT MASUK</span>
                <p class="text-lg font-black text-slate-800 font-lexend">{{ letters.incoming_month || 0 }}</p>
              </div>
              <div class="bg-white p-2.5 rounded-xl border border-slate-200/60 shadow-2xs">
                <span class="text-[10px] font-bold text-slate-400">SURAT KELUAR</span>
                <p class="text-lg font-black text-slate-800 font-lexend">{{ letters.outgoing_month || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Agenda Kegiatan Terdekat -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-black text-slate-800 font-lexend uppercase tracking-wider flex items-center gap-2">
              <Calendar class="w-4 h-4 text-purple-600" />
              <span>Agenda Pimpinan</span>
            </h3>
            <RouterLink to="/admin/calendar-events" class="text-xs font-bold text-purple-600 hover:text-purple-700">Semua</RouterLink>
          </div>

          <div v-if="upcomingEvents.length" class="space-y-2.5">
            <div 
              v-for="ev in upcomingEvents" 
              :key="ev.id"
              class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3 hover:bg-slate-100/80 transition-colors"
            >
              <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 font-bold text-xs flex flex-col items-center justify-center flex-shrink-0">
                <span class="text-[9px] uppercase font-black">{{ formatDateMonth(ev.start_date) }}</span>
                <span class="text-sm leading-none font-black">{{ formatDateDay(ev.start_date) }}</span>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-slate-800 truncate">{{ ev.title }}</p>
                <p class="text-[10px] text-slate-400 line-clamp-1">{{ ev.description || 'Agenda resmi madrasah' }}</p>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-6 text-slate-400 text-xs font-semibold">
            Tidak ada agenda terdekat saat ini.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { api, getImageUrl } from '../api';
import { useAuthStore } from '../stores/auth';
import { 
  ShieldCheck, 
  GraduationCap, 
  UserCheck, 
  MapPin, 
  Award, 
  Printer, 
  CalendarDays, 
  Calendar, 
  Clock, 
  Users, 
  BookOpen, 
  ArrowRight 
} from 'lucide-vue-next';

const auth = useAuthStore();
const loading = ref(true);

const overview = ref({});
const teacherAttendance = ref({});
const studentAttendance = ref({});
const ppdb = ref({});
const letters = ref({});
const gradingProgress = ref({});
const upcomingEvents = ref([]);
const recentAchievements = ref([]);

const userPhoto = computed(() => {
  return auth.user?.teacher?.photo || auth.user?.photo || null;
});

function formatDateMonth(d) {
  if (!d) return '';
  const date = new Date(d);
  return date.toLocaleString('id-ID', { month: 'short' });
}

function formatDateDay(d) {
  if (!d) return '';
  const date = new Date(d);
  return date.getDate();
}

async function loadDashboardData() {
  loading.value = true;
  try {
    const res = await api.get('admin/kepala-sekolah/dashboard');
    const d = res.data || res;
    overview.value = d.overview || {};
    teacherAttendance.value = d.teacher_attendance || {};
    studentAttendance.value = d.student_attendance || {};
    ppdb.value = d.ppdb || {};
    letters.value = d.letters || {};
    gradingProgress.value = d.grading_progress || {};
    upcomingEvents.value = d.upcoming_events || [];
    recentAchievements.value = d.recent_achievements || [];
  } catch (err) {
    console.error('Failed to load Kepala Sekolah dashboard data', err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadDashboardData();
});
</script>
