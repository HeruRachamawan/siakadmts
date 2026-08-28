<template>
  <header class="h-16 flex items-center justify-between px-6 border-b border-slate-200/80 bg-white/95 backdrop-blur-md sticky top-0 z-30 shadow-2xs no-print">
    <div class="flex items-center gap-3">
      <button 
        @click="$emit('toggle-sidebar')"
        class="p-2 -ml-2 rounded-xl text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 active:scale-95 transition-all cursor-pointer border border-transparent hover:border-emerald-200"
      >
        <Menu class="w-5 h-5" />
      </button>
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-500 hidden sm:inline-block"></span>
        <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight font-lexend">
          {{ currentRouteName }}
        </h2>
      </div>
    </div>

    <!-- Header Right: Clock + Role Switcher + Reset Notifications + Profile -->
    <div class="flex items-center gap-2.5 sm:gap-3">
      <!-- Live School Clock Badge -->
      <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 bg-slate-50 rounded-xl border border-slate-200/80 text-slate-700 text-xs font-bold shadow-2xs font-mono">
        <Clock class="w-3.5 h-3.5 text-emerald-600" />
        <span>{{ currentTime }}</span>
      </div>

      <!-- DUAL-ROLE QUICK SWITCH BUTTON -->
      <div v-if="auth.isDualRole" class="flex items-center">
        <!-- If currently in Staff / Operator / Kurikulum mode -> button to switch to Teacher -->
        <button
          v-if="auth.role !== 'teacher'"
          @click="handleSwitchRole('teacher')"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-500 hover:to-teal-600 text-white text-xs font-bold shadow-md shadow-emerald-700/20 active:scale-95 transition-all cursor-pointer border border-emerald-400/40"
          title="Beralih ke Ruang Guru (Presensi GPS, Nilai, & Jadwal)"
        >
          <GraduationCap class="w-3.5 h-3.5 text-emerald-200" />
          <span class="hidden sm:inline">Mode Guru</span>
          <ArrowRightLeft class="w-3 h-3 text-emerald-200 opacity-80" />
        </button>

        <!-- If currently in Teacher mode -> button to switch back to Staff / Operator / Kurikulum -->
        <button
          v-else
          @click="handleSwitchRole(auth.primaryRole || 'operator')"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-700 hover:from-indigo-500 hover:to-blue-600 text-white text-xs font-bold shadow-md shadow-indigo-700/20 active:scale-95 transition-all cursor-pointer border border-indigo-400/40"
          :title="`Beralih ke Ruang ${auth.primaryRole === 'kurikulum' ? 'Kurikulum' : 'Tata Usaha / Operator'}`"
        >
          <Building2 class="w-3.5 h-3.5 text-indigo-200" />
          <span class="hidden sm:inline">Mode {{ auth.primaryRole === 'kurikulum' ? 'Kurikulum' : 'Operator TU' }}</span>
          <ArrowRightLeft class="w-3 h-3 text-indigo-200 opacity-80" />
        </button>
      </div>

      <!-- Notification Bell for Admin & Teacher -->
      <div v-if="auth.role === 'admin' || auth.role === 'teacher'" class="relative">
        <button
          @click="$emit('open-notifications')"
          title="Notifikasi Masuk"
          class="w-9 h-9 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 flex items-center justify-center relative transition-all cursor-pointer border border-slate-200/80 shadow-2xs hover:border-emerald-200"
        >
          <Bell class="w-4 h-4" />
          <span
            v-if="totalNotificationsCount > 0"
            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 bg-rose-600 text-white rounded-full text-[10px] font-black flex items-center justify-center shadow-xs animate-pulse"
          >
            {{ totalNotificationsCount }}
          </span>
        </button>
      </div>

      <!-- User Profile Avatar & Dropdown -->
      <div class="relative">
        <button
          @click="showProfileDropdown = !showProfileDropdown"
          class="flex items-center gap-2.5 p-1.5 rounded-2xl hover:bg-slate-50 transition-all cursor-pointer select-none border border-slate-200/80 bg-white shadow-2xs hover:border-emerald-300"
        >
          <div class="w-8 h-8 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-black text-xs shadow-xs">
            {{ (user?.name || user?.username || 'A').charAt(0).toUpperCase() }}
          </div>
          <div class="hidden sm:block text-left pr-1">
            <p class="text-xs font-bold text-slate-800 leading-tight max-w-[120px] truncate">{{ user?.name || user?.username }}</p>
            <span class="inline-block text-[9px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-extrabold uppercase mt-0.5">{{ auth.role }}</span>
          </div>
          <ChevronDown class="w-3.5 h-3.5 text-slate-400 hidden sm:block transition-transform duration-200" :class="{ 'rotate-180': showProfileDropdown }" />
        </button>

        <!-- Dropdown Popup -->
        <div
          v-if="showProfileDropdown"
          class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 animate-slide-up"
          @click="showProfileDropdown = false"
        >
          <div class="px-4 py-2.5 border-b border-slate-100">
            <p class="text-xs font-black text-slate-800 truncate">{{ user?.name }}</p>
            <p class="text-[10px] text-slate-400 truncate">{{ user?.email || user?.username }}</p>
          </div>

          <!-- Dual-Role Switcher Option inside Dropdown -->
          <div v-if="auth.isDualRole" class="p-2 border-b border-slate-100">
            <button
              v-if="auth.role !== 'teacher'"
              @click.stop="handleSwitchRole('teacher')"
              class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-emerald-800 bg-emerald-50/80 hover:bg-emerald-100 rounded-xl transition-all cursor-pointer text-left border border-emerald-200/80"
            >
              <div class="flex items-center gap-2">
                <GraduationCap class="w-4 h-4 text-emerald-600" />
                <span>Mode Guru</span>
              </div>
              <ArrowRightLeft class="w-3.5 h-3.5 text-emerald-600" />
            </button>
            <button
              v-else
              @click.stop="handleSwitchRole(auth.primaryRole || 'operator')"
              class="w-full flex items-center justify-between px-3 py-2 text-xs font-bold text-indigo-800 bg-indigo-50/80 hover:bg-indigo-100 rounded-xl transition-all cursor-pointer text-left border border-indigo-200/80"
            >
              <div class="flex items-center gap-2">
                <Building2 class="w-4 h-4 text-indigo-600" />
                <span>Mode {{ auth.primaryRole === 'kurikulum' ? 'Kurikulum' : 'Operator TU' }}</span>
              </div>
              <ArrowRightLeft class="w-3.5 h-3.5 text-indigo-600" />
            </button>
          </div>

          <div class="py-1">
            <RouterLink
              :to="auth.role === 'student' ? '/student/profile' : (auth.role === 'teacher' ? '/teacher/profile' : '/admin/profile')"
              class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors"
            >
              <UserCircle class="w-4 h-4 text-emerald-600" />
              <span>Profil & Biodata Diri</span>
            </RouterLink>

            <button
              @click="$emit('open-change-password')"
              class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors cursor-pointer text-left"
            >
              <Key class="w-4 h-4 text-amber-500" />
              <span>Ubah Password</span>
            </button>
          </div>

          <div class="border-t border-slate-100 pt-1">
            <button
              @click="$emit('logout')"
              class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer text-left"
            >
              <LogOut class="w-4 h-4 text-rose-500" />
              <span>Keluar Aplikasi</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import {
  Clock,
  Bell,
  ChevronDown,
  UserCircle,
  Key,
  LogOut,
  Menu,
  GraduationCap,
  Building2,
  ArrowRightLeft
} from 'lucide-vue-next';

defineProps({
  user: Object,
  currentTime: String,
  currentRouteName: String,
  totalNotificationsCount: {
    type: Number,
    default: 0
  }
});

const auth = useAuthStore();
const router = useRouter();
const showProfileDropdown = ref(false);

function handleSwitchRole(targetRole) {
  auth.switchRole(targetRole);
  showProfileDropdown.value = false;
  if (targetRole === 'teacher') {
    router.push('/teacher/dashboard');
  } else if (targetRole === 'operator') {
    router.push('/operator/dashboard');
  } else if (targetRole === 'kurikulum') {
    router.push('/kurikulum/dashboard');
  } else if (targetRole === 'admin') {
    router.push('/admin/dashboard');
  }
}

defineEmits(['toggle-sidebar', 'open-notifications', 'open-change-password', 'logout']);
</script>

