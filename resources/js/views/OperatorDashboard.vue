<template>
  <div class="space-y-6 font-sans">
    <!-- Top Welcome Banner -->
    <div class="relative overflow-hidden bg-gradient-to-r from-teal-900 via-emerald-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-emerald-800/40">
      <div class="relative z-10 space-y-2">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold backdrop-blur-md">
          <FileText class="w-3.5 h-3.5" />
          <span>Ruang Kerja Tata Usaha & Operator Madrasah</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
          Selamat Bertugas, {{ auth.user?.name || 'Operator TU' }}! 📂
        </h1>
        <p class="text-xs sm:text-sm text-emerald-100/80 max-w-2xl">
          Kelola agenda persuratan masuk/keluar, penerbitan surat aktif siswa, verifikasi arsip dokumen, dan cetak dokumen madrasah secara terpadu.
        </p>
      </div>

      <!-- Quick Action Shortcuts -->
      <div class="relative z-10 flex flex-wrap gap-2.5 pt-4">
        <RouterLink
          to="/operator/letters"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md transition-all active:scale-95"
        >
          <Inbox class="w-4 h-4" />
          <span>Buku Agenda Persuratan</span>
        </RouterLink>

        <RouterLink
          to="/admin/print-center"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md transition-all active:scale-95"
        >
          <Printer class="w-4 h-4" />
          <span>Pusat Cetak Dokumen</span>
        </RouterLink>

        <RouterLink
          to="/admin/students"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md transition-all active:scale-95"
        >
          <Users class="w-4 h-4" />
          <span>Data Siswa</span>
        </RouterLink>
      </div>

      <div class="absolute right-0 top-0 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Quick Bento Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <RouterLink to="/operator/letters" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Surat Masuk</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <Inbox class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.total_incoming || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Surat tercatat dalam agenda</p>
      </RouterLink>

      <RouterLink to="/operator/letters" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-indigo-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Surat Keluar</span>
          <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <Send class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.total_outgoing || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Nomor surat resmi terbit</p>
      </RouterLink>

      <RouterLink to="/admin/students" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-teal-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Data Siswa Aktif</span>
          <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <Users class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.total_students || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Siswa terdaftar di kelas</p>
      </RouterLink>

      <RouterLink to="/admin/teachers" class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-amber-400 hover:shadow-md transition-all group">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Dewan Guru & Staf</span>
          <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
            <UserCheck class="w-4 h-4" />
          </div>
        </div>
        <p class="text-2xl font-black text-slate-900 mt-2">{{ stats.total_teachers || 0 }}</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Pendidik aktif</p>
      </RouterLink>
    </div>

    <!-- Live Letters Component Embedded -->
    <AdminLetters />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  FileText,
  Inbox,
  Send,
  Printer,
  Users,
  UserCheck
} from 'lucide-vue-next';
import { api } from '../api';
import AdminLetters from './AdminLetters.vue';

const auth = useAuthStore();
const stats = ref({});

async function loadStats() {
  try {
    const [letterRes, studentRes, teacherRes] = await Promise.all([
      api.get('admin/letters'),
      api.get('admin/students'),
      api.get('admin/teachers')
    ]);
    stats.value = {
      ...(letterRes?.data?.stats || letterRes?.stats || {}),
      total_students: studentRes?.data?.students?.total || studentRes?.total || 0,
      total_teachers: teacherRes?.data?.teachers?.total || teacherRes?.total || 0,
    };
  } catch (err) {
    console.error('Failed loading stats', err);
  }
}

onMounted(() => {
  loadStats();
});
</script>
