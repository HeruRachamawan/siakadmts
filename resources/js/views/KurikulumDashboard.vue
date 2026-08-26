<template>
  <div class="space-y-6 font-sans">
    <!-- Top Welcome Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-indigo-800/40">
      <div class="relative z-10 space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-300 text-xs font-semibold backdrop-blur-md">
          <GraduationCap class="w-3.5 h-3.5" />
          <span>Ruang Kerja Waka Kurikulum & Akademik</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
          Selamat Datang, {{ auth.user?.name || 'Waka Kurikulum' }}! 📚
        </h1>
        <p class="text-xs sm:text-sm text-indigo-100/80 max-w-2xl">
          Kelola distribusi jadwal pelajaran, mata pelajaran, beban mengajar guru, kalender akademik, dan rekapitulasi nilai siswa secara akurat.
        </p>
      </div>

      <!-- Quick Action Shortcuts -->
      <div class="relative z-10 flex flex-wrap gap-2.5 pt-4">
        <RouterLink
          to="/admin/schedules"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md transition-all active:scale-95"
        >
          <Calendar class="w-4 h-4" />
          <span>Jadwal Pelajaran</span>
        </RouterLink>

        <RouterLink
          to="/admin/grades"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md transition-all active:scale-95"
        >
          <Award class="w-4 h-4" />
          <span>Rekap Nilai Siswa</span>
        </RouterLink>

        <RouterLink
          to="/admin/subjects"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md transition-all active:scale-95"
        >
          <BookOpen class="w-4 h-4" />
          <span>Mata Pelajaran</span>
        </RouterLink>

        <RouterLink
          to="/kurikulum/letters"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-md transition-all active:scale-95"
        >
          <FileText class="w-4 h-4 text-slate-950" />
          <span>Buku Agenda Surat</span>
        </RouterLink>
      </div>

      <div class="absolute right-0 top-0 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
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
import { ref, onMounted } from 'vue';
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
