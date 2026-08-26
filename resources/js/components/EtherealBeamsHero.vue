<template>
  <div class="relative w-full bg-gradient-to-b from-[#021811] via-[#04281e] to-[#02150f] border-b border-emerald-900/60 pt-24 pb-16 sm:pt-32 sm:pb-24 overflow-hidden font-outfit text-white">
    
    <!-- Subtle Modern Dot Matrix Background -->
    <div class="absolute inset-0 bg-[radial-gradient(rgba(16,185,129,0.15)_1px,transparent_1px)] [background-size:24px_24px] opacity-60 pointer-events-none"></div>
    
    <!-- Ambient Floating Aurora Orbs (Soft Luxury Emerald & Cyan Glow) -->
    <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[600px] h-[350px] bg-emerald-500/15 rounded-full blur-[120px] pointer-events-none animate-pulse-slow"></div>
    <div class="absolute top-1/4 left-10 w-80 h-80 bg-teal-400/10 rounded-full blur-3xl pointer-events-none animate-float-slow"></div>
    <div class="absolute top-1/3 right-10 w-96 h-96 bg-emerald-600/12 rounded-full blur-3xl pointer-events-none animate-float-reverse"></div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-7">
      
      <!-- 1. Top Modern Pill Badge (Interactive Pulsing Status) -->
      <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-emerald-950/80 border border-emerald-700/50 text-emerald-200 text-xs font-semibold shadow-lg shadow-emerald-950/60 backdrop-blur-md transition-all hover:bg-emerald-900/80 hover:border-emerald-500 hover:scale-105 duration-300">
        <span class="flex h-2 w-2 relative">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span class="tracking-wide text-emerald-100 uppercase text-[11px]">{{ accreditationText }}</span>
        <span class="text-emerald-600">&bull;</span>
        <span class="text-emerald-300 font-normal">Tahun Ajaran 2026/2027</span>
        <ArrowRight class="w-3.5 h-3.5 text-emerald-400" />
      </div>

      <!-- 2. Main Headline with Animated Shimmer Gradient Typography -->
      <div class="space-y-4 max-w-4xl mx-auto relative">
        
        <!-- Subtle Glow Layer Behind Headline -->
        <div class="absolute inset-0 blur-2xl bg-emerald-500/10 pointer-events-none rounded-full"></div>

        <h1 class="relative text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.12] sm:leading-[1.15]">
          <span class="block text-slate-100 font-extrabold uppercase tracking-tight text-xl sm:text-3xl lg:text-4xl text-slate-200/90 mb-1">
            SISTEM INFORMASI & MANAJEMEN DIGITAL
          </span>
          <span class="shimmer-text bg-gradient-to-r from-emerald-300 via-teal-100 to-emerald-400 bg-clip-text text-transparent inline-block">
            {{ settings.app_name || 'MTs AL - HASANAH' }}
          </span>
        </h1>

        <!-- Morphing Interactive Subtitle with Animated Rotating Highlights -->
        <div class="flex items-center justify-center min-h-[32px] pt-1">
          <Transition name="slide-fade" mode="out-in">
            <p :key="activeHighlightIndex" class="text-sm sm:text-base text-emerald-200/90 font-medium tracking-wide flex items-center gap-2">
              <Sparkles class="w-4 h-4 text-emerald-400 flex-shrink-0 animate-spin-slow" />
              <span>{{ rotatingFeatures[activeHighlightIndex] }}</span>
            </p>
          </Transition>
        </div>

        <p class="text-xs sm:text-sm text-emerald-100/70 max-w-xl mx-auto font-normal leading-relaxed">
          {{ settings.app_tagline || 'Pusat layanan akademik terpadu, presensi GPS realtime, dan pendaftaran siswa baru online.' }}
        </p>
      </div>

      <!-- 3. Minimalist High-Interaction Action Buttons -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
        <RouterLink
          to="/ppdb"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-2xl text-xs font-black bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-lg shadow-amber-400/25 transition-all duration-200 hover:-translate-y-0.5 active:scale-95 cursor-pointer"
        >
          <UserPlus class="w-4 h-4 text-slate-950" />
          <span>Daftar Siswa Baru (PPDB)</span>
          <ArrowRight class="w-4 h-4 text-slate-950" />
        </RouterLink>

        <a
          href="#profil"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-2xl text-xs font-semibold bg-emerald-950/70 hover:bg-emerald-900/80 border border-emerald-700/60 text-emerald-200 backdrop-blur-md transition-all duration-200 hover:-translate-y-0.5 active:scale-95 cursor-pointer"
        >
          <span>Jelajahi Profil Madrasah</span>
          <ChevronRight class="w-4 h-4 text-emerald-400" />
        </a>
      </div>

      <!-- 4. Interactive Minimalist Glass Bento Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-6 max-w-3xl mx-auto">
        
        <!-- Card 1: Siswa -->
        <div class="bg-emerald-950/60 backdrop-blur-xl border border-emerald-800/50 hover:border-emerald-400 rounded-2xl p-4 sm:p-5 text-left flex items-center justify-between group hover:shadow-xl hover:shadow-emerald-950/60 transition-all duration-300 cursor-pointer">
          <div class="space-y-0.5">
            <p class="text-[11px] font-semibold text-emerald-300/80 uppercase tracking-wider">Siswa Terdaftar</p>
            <p class="text-2xl sm:text-3xl font-black tracking-tight text-white group-hover:text-emerald-300 transition-colors">
              {{ animatedStats.students }}
            </p>
            <p class="text-[10px] text-emerald-200/60 font-normal">Siswa aktif terdaftar</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-emerald-900/60 text-emerald-300 border border-emerald-700/60 flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-inner">
            <Users class="w-4 h-4" />
          </div>
        </div>

        <!-- Card 2: Guru -->
        <div class="bg-emerald-950/60 backdrop-blur-xl border border-emerald-800/50 hover:border-emerald-400 rounded-2xl p-4 sm:p-5 text-left flex items-center justify-between group hover:shadow-xl hover:shadow-emerald-950/60 transition-all duration-300 cursor-pointer">
          <div class="space-y-0.5">
            <p class="text-[11px] font-semibold text-emerald-300/80 uppercase tracking-wider">Dewan Guru</p>
            <p class="text-2xl sm:text-3xl font-black tracking-tight text-white group-hover:text-emerald-300 transition-colors">
              {{ animatedStats.teachers }}
            </p>
            <p class="text-[10px] text-emerald-200/60 font-normal">Pendidik & staf</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-emerald-900/60 text-emerald-300 border border-emerald-700/60 flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-inner">
            <UserCheck class="w-4 h-4" />
          </div>
        </div>

        <!-- Card 3: Kelas -->
        <div class="bg-emerald-950/60 backdrop-blur-xl border border-emerald-800/50 hover:border-emerald-400 rounded-2xl p-4 sm:p-5 text-left flex items-center justify-between group hover:shadow-xl hover:shadow-emerald-950/60 transition-all duration-300 cursor-pointer">
          <div class="space-y-0.5">
            <p class="text-[11px] font-semibold text-emerald-300/80 uppercase tracking-wider">Rombel Kelas</p>
            <p class="text-2xl sm:text-3xl font-black tracking-tight text-white group-hover:text-emerald-300 transition-colors">
              {{ animatedStats.classes }}
            </p>
            <p class="text-[10px] text-emerald-200/60 font-normal">Ruang belajar aktif</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-emerald-900/60 text-emerald-300 border border-emerald-700/60 flex items-center justify-center flex-shrink-0 group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-inner">
            <Building2 class="w-4 h-4" />
          </div>
        </div>

      </div>

      <!-- 5. Running Marquee Address Line -->
      <div class="max-w-4xl mx-auto mt-6 border border-emerald-800/60 bg-emerald-950/70 backdrop-blur-md rounded-full py-2 px-4 flex items-center gap-3 overflow-hidden shadow-lg shadow-emerald-950/40">
        <div class="flex items-center gap-1 px-3 py-0.5 bg-emerald-600 text-white rounded-full text-[10px] font-bold flex-shrink-0 shadow-2xs">
          <MapPin class="w-3 h-3 text-emerald-200" />
          <span>Lokasi</span>
        </div>
        <div class="overflow-hidden whitespace-nowrap w-full relative">
          <div class="inline-block animate-marquee text-emerald-100/90 text-xs font-medium tracking-wide">
            {{ currentAddress }} &nbsp;&bull;&nbsp; {{ currentAddress }}
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onUnmounted, computed } from 'vue';
import { RouterLink } from 'vue-router';
import {
  ArrowRight,
  ChevronRight,
  LogIn,
  Users,
  UserCheck,
  Building2,
  MapPin,
  Sparkles,
  UserPlus
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

const rotatingFeatures = [
  'Presensi Realtime GPS & Pemindai QR Code Guru',
  'Penerimaan Peserta Didik Baru (PPDB) Online',
  'Transkrip & Rekap Nilai Akademik Terpadu',
  'Portal Pembelajaran Interaktif & Mutu Madrasah'
];
const activeHighlightIndex = ref(0);
let highlightTimer = null;

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
  highlightTimer = setInterval(() => {
    activeHighlightIndex.value = (activeHighlightIndex.value + 1) % rotatingFeatures.length;
  }, 3200);
});

onUnmounted(() => {
  if (highlightTimer) clearInterval(highlightTimer);
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

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.shimmer-text {
  background-size: 200% auto;
  animation: shimmer 5s linear infinite;
}

@keyframes spinSlow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin-slow {
  animation: spinSlow 12s linear infinite;
}

@keyframes pulseSlow {
  0%, 100% { opacity: 0.2; transform: scale(1); }
  50% { opacity: 0.35; transform: scale(1.08); }
}

.animate-pulse-slow {
  animation: pulseSlow 6s ease-in-out infinite;
}

@keyframes floatSlow {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-12px) scale(1.03); }
}

@keyframes floatReverse {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(12px) scale(0.97); }
}

.animate-float-slow {
  animation: floatSlow 8s ease-in-out infinite;
}
.animate-float-reverse {
  animation: floatReverse 10s ease-in-out infinite;
}

.slide-fade-enter-active {
  transition: all 0.35s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.25s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from {
  transform: translateY(10px);
  opacity: 0;
}
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}
</style>
