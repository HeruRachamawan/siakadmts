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

    <!-- Header Right: Clock + Reset Notifications + Change Password + Profile -->
    <div class="flex items-center gap-3">
      <!-- Live School Clock Badge -->
      <div class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 bg-slate-50 rounded-xl border border-slate-200/80 text-slate-700 text-xs font-bold shadow-2xs font-mono">
        <Clock class="w-3.5 h-3.5 text-emerald-600" />
        <span>{{ currentTime }}</span>
      </div>

      <!-- Notification Bell for Admin & Teacher -->
      <div v-if="user?.role === 'admin' || user?.role === 'teacher'" class="relative">
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
            <span class="inline-block text-[9px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-extrabold uppercase mt-0.5">{{ user?.role }}</span>
          </div>
          <ChevronDown class="w-3.5 h-3.5 text-slate-400 hidden sm:block transition-transform duration-200" :class="{ 'rotate-180': showProfileDropdown }" />
        </button>

        <!-- Dropdown Popup -->
        <div
          v-if="showProfileDropdown"
          class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 animate-slide-up"
          @click="showProfileDropdown = false"
        >
          <div class="px-4 py-2.5 border-b border-slate-100">
            <p class="text-xs font-black text-slate-800 truncate">{{ user?.name }}</p>
            <p class="text-[10px] text-slate-400 truncate">{{ user?.email || user?.username }}</p>
          </div>

          <div class="py-1">
            <RouterLink
              :to="`/${user?.role === 'admin' ? 'admin' : (user?.role === 'teacher' ? 'teacher' : 'student')}/profile`"
              class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors"
            >
              <UserCircle class="w-4 h-4 text-emerald-600" />
              <span>Profil Saya</span>
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
import {
  Clock,
  Bell,
  ChevronDown,
  UserCircle,
  Key,
  LogOut,
  Menu
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

const showProfileDropdown = ref(false);

defineEmits(['toggle-sidebar', 'open-notifications', 'open-change-password', 'logout']);
</script>
