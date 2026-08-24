<template>
  <div class="relative w-full bg-gradient-to-b from-[#032218] via-[#052e20] to-[#02150f] border-b border-emerald-900/60 pt-28 pb-16 sm:pt-36 sm:pb-24 overflow-hidden font-inter text-white">
    
    <!-- Subtle Emerald Dot Grid -->
    <div class="absolute inset-0 bg-[radial-gradient(rgba(16,185,129,0.18)_1px,transparent_1px)] [background-size:28px_28px] opacity-70 pointer-events-none"></div>
    
    <!-- Ambient Floating Glowing Mesh Orbs (Luxury Emerald Glow) -->
    <div class="absolute -top-24 left-1/4 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none animate-float-slow"></div>
    <div class="absolute top-1/3 right-10 w-96 h-96 bg-teal-400/15 rounded-full blur-3xl pointer-events-none animate-float-reverse"></div>
    <div class="absolute bottom-0 left-1/3 w-80 h-80 bg-emerald-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
      
      <!-- Top Announcement Badge (Deep Emerald Pill with Pulse) -->
      <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-950/80 border border-emerald-700/60 text-emerald-200 text-xs font-medium shadow-lg shadow-emerald-950/50 backdrop-blur-md transition-all hover:bg-emerald-900/80 hover:scale-105 duration-300">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="font-semibold text-emerald-100">{{ accreditationText }}</span>
        <span class="text-emerald-500">&bull;</span>
        <span class="text-emerald-300">Tahun Ajaran 2026/2027</span>
        <ArrowRight class="w-3.5 h-3.5 text-emerald-400 ml-0.5" />
      </div>

      <!-- Main Headline (Crisp White & Radiant Emerald Gradient) -->
      <div class="space-y-4 max-w-4xl mx-auto">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.15]">
          {{ settings.hero_title || 'SISTEM INFORMASI DAN MANAJEMEN DIGITAL' }} <br />
          <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-emerald-200 bg-clip-text text-transparent underline decoration-emerald-400/80 decoration-wavy decoration-2 underline-offset-8">
            {{ settings.app_name || 'MTs AL - HASANAH' }}
          </span>
        </h1>

        <!-- Subtitle -->
        <p class="text-base sm:text-lg text-emerald-100/90 max-w-2xl mx-auto font-normal leading-relaxed">
          {{ settings.app_tagline || 'Portal Madrasah Tsanawiyah Al - Hasanah Ciomas' }}
        </p>
      </div>

      <!-- Action Buttons (High Contrast Emerald Buttons) -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 pt-2">
        <RouterLink
          to="/login"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 active:scale-95 group cursor-pointer"
        >
          <LogIn class="w-4 h-4" />
          <span>Masuk ke Portal SIAKAD</span>
          <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
        </RouterLink>

        <a
          href="#profil"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-xs font-semibold bg-emerald-950/70 hover:bg-emerald-900/80 border border-emerald-700/70 text-emerald-100 shadow-md backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5 active:scale-95 cursor-pointer"
        >
          <span>Jelajahi Profil Madrasah</span>
          <ChevronRight class="w-4 h-4 text-emerald-400" />
        </a>
      </div>

      <!-- Shadcn Metric Bento Cards (Deep Emerald Glass) -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-8 max-w-3xl mx-auto">
        
        <!-- Card 1: Siswa -->
        <div class="bg-emerald-950/70 backdrop-blur-xl border border-emerald-800/60 rounded-2xl p-5 text-left flex items-center justify-between group hover:border-emerald-400 hover:shadow-xl hover:shadow-emerald-950/50 transition-all duration-300">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-emerald-300/90 uppercase tracking-wider">Siswa Terdaftar</p>
            <p class="text-3xl font-extrabold tracking-tight text-white group-hover:text-emerald-300 transition-colors">
              {{ animatedStats.students }}
            </p>
            <p class="text-[11px] text-emerald-200/70 font-normal">Siswa binaan aktif</p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-900/80 text-emerald-300 border border-emerald-700/80 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform shadow-inner">
            <Users class="w-5 h-5" />
          </div>
        </div>

        <!-- Card 2: Guru -->
        <div class="bg-emerald-950/70 backdrop-blur-xl border border-emerald-800/60 rounded-2xl p-5 text-left flex items-center justify-between group hover:border-emerald-400 hover:shadow-xl hover:shadow-emerald-950/50 transition-all duration-300">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-emerald-300/90 uppercase tracking-wider">Dewan Guru</p>
            <p class="text-3xl font-extrabold tracking-tight text-white group-hover:text-emerald-300 transition-colors">
              {{ animatedStats.teachers }}
            </p>
            <p class="text-[11px] text-emerald-200/70 font-normal">Tenaga pendidik & staf</p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-900/80 text-emerald-300 border border-emerald-700/80 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform shadow-inner">
            <UserCheck class="w-5 h-5" />
          </div>
        </div>

        <!-- Card 3: Kelas -->
        <div class="bg-emerald-950/70 backdrop-blur-xl border border-emerald-800/60 rounded-2xl p-5 text-left flex items-center justify-between group hover:border-emerald-400 hover:shadow-xl hover:shadow-emerald-950/50 transition-all duration-300">
          <div class="space-y-1">
            <p class="text-xs font-semibold text-emerald-300/90 uppercase tracking-wider">Rombel Kelas</p>
            <p class="text-3xl font-extrabold tracking-tight text-white group-hover:text-emerald-300 transition-colors">
              {{ animatedStats.classes }}
            </p>
            <p class="text-[11px] text-emerald-200/70 font-normal">Ruang belajar aktif</p>
          </div>
          <div class="w-11 h-11 rounded-xl bg-emerald-900/80 text-emerald-300 border border-emerald-700/80 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform shadow-inner">
            <Building2 class="w-5 h-5" />
          </div>
        </div>

      </div>

      <!-- Running Text (Teks Bergerak Marquee Alamat Madrasah) -->
      <div class="max-w-4xl mx-auto mt-8 border border-emerald-800/80 bg-emerald-950/80 backdrop-blur-md rounded-full py-2.5 px-4 flex items-center gap-3 overflow-hidden shadow-lg shadow-emerald-950/40">
        <div class="flex items-center gap-1.5 px-3.5 py-1 bg-emerald-600 text-white rounded-full text-[11px] font-bold flex-shrink-0 shadow-2xs">
          <MapPin class="w-3.5 h-3.5 text-emerald-200 animate-bounce" />
          <span>Alamat Madrasah</span>
        </div>
        <div class="overflow-hidden whitespace-nowrap w-full relative">
          <div class="inline-block animate-marquee text-emerald-100 text-xs font-medium tracking-wide">
            {{ currentAddress }} &nbsp;&bull;&nbsp; {{ currentAddress }}
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, watch, onMounted, computed } from 'vue';
import { RouterLink } from 'vue-router';
import {
  ArrowRight,
  ChevronRight,
  LogIn,
  Users,
  UserCheck,
  Building2,
  MapPin
} from 'lucide-vue-next';

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({})
  },
  stats: {
    type: Object,
    default: () => ({ students: 0, teachers: 0, classes: 0 })
  }
});

const accreditationText = computed(() => {
  const acc = props.settings.school_accreditation || 'Terakreditasi A';
  if (acc.toLowerCase().startsWith('akreditasi')) {
    return acc;
  }
  return `Akreditasi ${acc}`;
});

const currentAddress = computed(() => {
  return props.settings.school_address || 'Jl. Raya Ciomas No. 123, Kabupaten Bogor, Jawa Barat 16610';
});

const animatedStats = reactive({
  students: 0,
  teachers: 0,
  classes: 0
});

function animateValue(key, targetValue, duration = 1200) {
  if (!targetValue || isNaN(targetValue)) {
    animatedStats[key] = 0;
    return;
  }
  const start = 0;
  const end = parseInt(targetValue, 10);
  const startTime = performance.now();

  function update(currentTime) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    
    // Ease out cubic
    const easeProgress = 1 - Math.pow(1 - progress, 3);
    animatedStats[key] = Math.floor(start + (end - start) * easeProgress);

    if (progress < 1) {
      requestAnimationFrame(update);
    } else {
      animatedStats[key] = end;
    }
  }

  requestAnimationFrame(update);
}

function runCountUp() {
  animateValue('students', props.stats.students || 0);
  animateValue('teachers', props.stats.teachers || 0);
  animateValue('classes', props.stats.classes || 0);
}

onMounted(() => {
  runCountUp();
});

watch(
  () => props.stats,
  () => {
    runCountUp();
  },
  { deep: true }
);
</script>

<style scoped>
@keyframes marquee {
  0% { transform: translateX(0%); }
  100% { transform: translateX(-50%); }
}

.animate-marquee {
  display: inline-block;
  white-space: nowrap;
  animation: marquee 25s linear infinite;
}

.animate-marquee:hover {
  animation-play-state: paused;
}

@keyframes floatSlow {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-15px) scale(1.05); }
}

@keyframes floatReverse {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(15px) scale(0.95); }
}

.animate-float-slow {
  animation: floatSlow 8s ease-in-out infinite;
}

.animate-float-reverse {
  animation: floatReverse 10s ease-in-out infinite;
}
</style>
