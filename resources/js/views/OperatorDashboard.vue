<template>
  <div class="space-y-6 font-sans">
    <!-- Fresh Vibrant Emerald Hero Banner (Operator TU) -->
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
              {{ (auth.user?.name || 'O').charAt(0) }}
            </div>
            <!-- Online status indicator -->
            <span class="absolute bottom-1 right-1 w-3.5 h-3.5 bg-emerald-300 border-2 border-emerald-800 rounded-full shadow-xs"></span>
          </div>

          <!-- Profile Details -->
          <div class="space-y-1.5">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/20 backdrop-blur-md text-white rounded-full text-[11px] font-bold border border-white/30 shadow-xs">
                <FileText class="w-3.5 h-3.5 text-emerald-200" />
                <span>Tata Usaha & Operator &bull; @{{ auth.user?.username }}</span>
              </span>
              <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-300/20 backdrop-blur-md text-amber-100 rounded-full text-[11px] font-bold border border-amber-300/40">
                <span>T.A. 2026/2027</span>
              </span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white uppercase leading-tight drop-shadow-xs">
              {{ auth.user?.name || 'Operator TU' }}
            </h1>
            <p class="text-emerald-100 text-xs sm:text-sm font-normal max-w-xl leading-relaxed">
              Kelola agenda persuratan masuk/keluar, penerbitan surat aktif siswa, verifikasi arsip dokumen, dan cetak dokumen madrasah secara terpadu.
            </p>
          </div>
        </div>

        <!-- Quick Action Shortcuts -->
        <div class="flex flex-wrap md:flex-col lg:flex-row gap-2.5 flex-shrink-0">
          <RouterLink
            to="/operator/letters"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-md transition-all active:scale-95"
          >
            <Inbox class="w-4 h-4" />
            <span>Buku Agenda Surat</span>
          </RouterLink>

          <RouterLink
            to="/admin/print-center"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/20 hover:bg-white/30 text-white border border-white/30 backdrop-blur-md transition-all active:scale-95 shadow-xs"
          >
            <Printer class="w-4 h-4" />
            <span>Pusat Cetak</span>
          </RouterLink>

          <RouterLink
            to="/admin/students"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-teal-900/80 hover:bg-teal-900 text-white border border-teal-400/40 backdrop-blur-md transition-all active:scale-95 shadow-xs"
          >
            <Users class="w-4 h-4" />
            <span>Data Siswa</span>
          </RouterLink>
        </div>
      </div>
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
import { ref, computed, onMounted } from 'vue';
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

async function loadStats() {
  try {
    const [letterRes, dashRes] = await Promise.all([
      api.get('admin/letters').catch(() => null),
      api.get('admin/dashboard').catch(() => null)
    ]);
    const d = dashRes?.data?.data || dashRes?.data || dashRes || {};
    const l = letterRes?.data?.stats || letterRes?.stats || letterRes?.data || {};
    stats.value = {
      ...l,
      total_students: d.students || 0,
      total_teachers: d.teachers || 0,
      total_classes: d.classes || 0,
    };
  } catch (err) {
    console.error('Failed loading stats', err);
  }
}

onMounted(() => {
  loadStats();
});
</script>
