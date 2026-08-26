<template>
  <div class="space-y-6 font-sans">
    <!-- Fresh Vibrant Emerald Hero Banner (Waka Kurikulum) -->
    <div class="relative bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 text-white rounded-2xl shadow-lg shadow-emerald-700/20 overflow-hidden border border-emerald-500/40">
      <!-- Subtle Background Mesh Grid & Glow -->
      <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.18)_1px,transparent_1px)] [background-size:22px_22px] opacity-60 pointer-events-none"></div>
      <div class="absolute -top-16 -right-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>

      <!-- Banner Content -->
      <div class="relative z-10 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
          <!-- Photo Frame -->
          <div class="w-20 h-20 sm:w-24 sm:h-24 bg-white/15 backdrop-blur-md rounded-2xl border border-white/30 p-1 flex items-center justify-center flex-shrink-0 overflow-hidden relative shadow-md">
            <img
              v-if="userPhoto"
              :src="getImageUrl(userPhoto)"
              class="w-full h-full object-cover rounded-xl shadow-inner"
              alt="Foto Profil"
            />
            <div v-else class="w-full h-full rounded-xl bg-emerald-800 flex items-center justify-center text-white font-bold text-2xl uppercase">
              {{ (auth.user?.name || 'K').charAt(0) }}
            </div>
            <!-- Online status indicator -->
            <span class="absolute bottom-1 right-1 w-3.5 h-3.5 bg-emerald-300 border-2 border-emerald-800 rounded-full shadow-xs"></span>
          </div>

          <!-- Profile Details -->
          <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-full text-[11px] font-bold border border-white/30 shadow-xs">
                <GraduationCap class="w-3.5 h-3.5 text-emerald-200" />
                <span>Waka Kurikulum & Akademik &bull; {{ auth.user?.teacher?.nip ? `NIP: ${auth.user.teacher.nip}` : `@${auth.user?.username}` }}</span>
              </span>
              <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-300/20 backdrop-blur-md text-amber-100 rounded-full text-[11px] font-bold border border-amber-300/40">
                <span>T.A. 2026/2027</span>
              </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white uppercase leading-tight drop-shadow-xs">
              {{ auth.user?.name || 'Waka Kurikulum' }}
            </h1>
            <p class="text-emerald-100 text-xs sm:text-sm font-normal max-w-xl leading-relaxed">
              Kelola distribusi jadwal pelajaran, mata pelajaran, beban mengajar guru, kalender akademik, dan rekapitulasi nilai siswa secara akurat.
            </p>
          </div>
        </div>

        <!-- Quick Action Shortcuts -->
        <div class="flex flex-wrap md:flex-col lg:flex-row gap-2.5 flex-shrink-0">
          <RouterLink
            to="/admin/schedules"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md transition-all active:scale-95"
          >
            <Calendar class="w-4 h-4" />
            <span>Jadwal Pelajaran</span>
          </RouterLink>

          <RouterLink
            to="/admin/grades"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md transition-all active:scale-95 shadow-xs"
          >
            <Award class="w-4 h-4" />
            <span>Rekap Nilai</span>
          </RouterLink>

          <RouterLink
            to="/kurikulum/letters"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-teal-900/80 hover:bg-teal-900 text-white border border-teal-400/40 backdrop-blur-md transition-all active:scale-95 shadow-xs"
          >
            <FileText class="w-4 h-4" />
            <span>Agenda Surat</span>
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Quick Bento Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <RouterLink to="/admin/schedules" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-blue-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Jadwal Aktif</span>
          <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <Calendar class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.schedules_count || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Sesi KBM terjadwal</p>
      </RouterLink>

      <RouterLink to="/admin/subjects" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-indigo-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Mata Pelajaran</span>
          <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <BookOpen class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.subjects_count || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Kurikulum madrasah</p>
      </RouterLink>

      <RouterLink to="/admin/teachers" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-teal-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Guru Pengampu</span>
          <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <Users class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.teachers_count || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Tenaga pengajar aktif</p>
      </RouterLink>

      <RouterLink to="/admin/classes" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-amber-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Rombel Kelas</span>
          <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <Building2 class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.classes_count || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Kelas terdaftar</p>
      </RouterLink>

      <RouterLink to="/kurikulum/letters" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Agenda Surat</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <FileText class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.total_letters || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Surat Masuk & Keluar</p>
      </RouterLink>
    </div>

    <!-- Live Academic Management Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-slate-900">Distribusi Jadwal Pelajaran</h3>
            <p class="text-xs text-slate-500">Pengaturan jadwal KBM harian per kelas.</p>
          </div>
          <RouterLink to="/admin/schedules" class="text-xs font-bold text-blue-600 hover:underline">
            Kelola Jadwal &rarr;
          </RouterLink>
        </div>
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2 text-slate-600">
          <p>Fitur untuk Waka Kurikulum mengatur pembagian jam mengajar, hari, mata pelajaran, dan guru pengampu secara presisi.</p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-slate-900">Kalender Akademik</h3>
            <p class="text-xs text-slate-500">Agenda kegiatan akademik semester ini.</p>
          </div>
          <RouterLink to="/admin/calendar-events" class="text-xs font-bold text-indigo-600 hover:underline">
            Lihat Kalender &rarr;
          </RouterLink>
        </div>
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2 text-slate-600">
          <p>Pantau jadwal Penilaian Tengah Semester (PTS), Penilaian Akhir Semester (PAS), serta libur madrasah.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  GraduationCap,
  Calendar,
  Award,
  BookOpen,
  Users,
  Building2,
  FileText
} from 'lucide-vue-next';
import { api } from '../api';

const auth = useAuthStore();
const stats = ref({});

const userPhoto = computed(() => {
  return auth.user?.teacher?.photo_url || auth.user?.teacher?.photo || auth.user?.avatar || null;
});

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  if (path.startsWith('data:image')) return path;
  const clean = path.startsWith('/') ? path : `/${path}`;
  if (clean.startsWith('/storage/')) return clean;
  return `/storage/${path.replace(/^\/+/, '')}`;
}

async function loadKurikulumStats() {
  try {
    const [dashRes, letterRes] = await Promise.all([
      api.get('admin/dashboard').catch(() => null),
      api.get('admin/letters').catch(() => null)
    ]);
    const d = dashRes?.data?.data || dashRes?.data || dashRes || {};
    const l = letterRes?.data?.stats || letterRes?.stats || letterRes?.data || {};
    stats.value = {
      schedules_count: d.schedules || 0,
      subjects_count: d.subjects || 0,
      teachers_count: d.teachers || 0,
      classes_count: d.classes || 0,
      students_count: d.students || 0,
      grades_count: d.grades || 0,
      total_letters: (l.total_incoming || 0) + (l.total_outgoing || 0),
    };
  } catch (err) {
    console.error('Failed to load kurikulum stats', err);
  }
}

onMounted(() => {
  loadKurikulumStats();
});
</script>
