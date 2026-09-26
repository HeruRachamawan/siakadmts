<template>
  <div class="relative w-full bg-[#032218] border-b border-emerald-900/60 pt-24 pb-14 sm:pt-32 sm:pb-20 overflow-hidden font-inter text-white">
    
    <!-- Subtle Elegant Grid Pattern (Warm & Academic, Not Sci-Fi) -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#064e3b15_1px,transparent_1px),linear-gradient(to_bottom,#064e3b15_1px,transparent_1px)] bg-[size:4rem_4rem] pointer-events-none opacity-70"></div>
    
    <!-- Soft Organic Gradient Wash -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-emerald-600/10 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-teal-500/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Split Layout: Left Content & Right Academy Card -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
        
        <!-- LEFT COLUMN: Authentic School Proposition & CTA -->
        <div class="lg:col-span-7 text-left space-y-6">
          
          <!-- Official Accreditation & Academic Year Badge -->
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-900/70 border border-emerald-700/60 text-emerald-200 text-xs font-semibold shadow-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span class="tracking-wider uppercase text-[11px] font-bold text-white">{{ accreditationText }}</span>
            <span class="text-emerald-500">&bull;</span>
            <span class="text-emerald-300 font-medium">T.A. 2026/2027</span>
          </div>

          <!-- Main Human-Centered Headline -->
          <div class="space-y-3">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-[1.18] font-lexend">
              Mendidik Generasi Qur'ani, <br class="hidden sm:inline" />
              <span class="text-emerald-300 font-extrabold">Berakhlak Mulia & Unggul</span> di Era Digital.
            </h1>
            <p class="text-xs sm:text-base text-emerald-100/80 max-w-2xl font-normal leading-relaxed">
              Selamat datang di portal resmi <strong>{{ settings.app_name || 'MTs Al - Hasanah' }}</strong>. Lembaga pendidikan madrasah terpadu yang memadukan penguatan karakter keislaman, tahfidzul qur'an, dan sains teknologi modern.
            </p>
          </div>

          <!-- 3 Key Value Pills (Human & Tangible) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-emerald-950/60 border border-emerald-800/60 text-emerald-100 text-xs">
              <BookOpen class="w-4 h-4 text-emerald-400 flex-shrink-0" />
              <span class="font-medium">Tahfidz & Bimbingan Ibadah</span>
            </div>
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-emerald-950/60 border border-emerald-800/60 text-emerald-100 text-xs">
              <GraduationCap class="w-4 h-4 text-emerald-400 flex-shrink-0" />
              <span class="font-medium">Kurikulum Merdeka & Kemenag</span>
            </div>
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-emerald-950/60 border border-emerald-800/60 text-emerald-100 text-xs">
              <Sparkles class="w-4 h-4 text-emerald-400 flex-shrink-0" />
              <span class="font-medium">Ujian CBT & Rapor Digital</span>
            </div>
          </div>

          <!-- Action Buttons (Clear & Purposeful) -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
            <RouterLink
              to="/ppdb"
              class="inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl text-xs sm:text-sm font-black bg-gradient-to-r from-amber-400 to-amber-300 hover:from-amber-300 hover:to-amber-200 text-slate-950 shadow-md shadow-amber-400/20 transition-all duration-200 active:scale-95 cursor-pointer"
            >
              <UserPlus class="w-4 h-4 text-slate-950" />
              <span>Daftar Siswa Baru (PPDB Online)</span>
              <ArrowRight class="w-4 h-4 text-slate-950" />
            </RouterLink>

            <a
              href="#profil"
              class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl text-xs sm:text-sm font-bold bg-emerald-900/60 hover:bg-emerald-900/90 text-white border border-emerald-700/60 transition-all duration-200 active:scale-95 cursor-pointer"
            >
              <span>Profil & Visi Misi</span>
              <ChevronRight class="w-4 h-4 text-emerald-300" />
            </a>
          </div>

        </div>

        <!-- RIGHT COLUMN: Prestigious Academic Card with Real Metrics -->
        <div class="lg:col-span-5">
          <div class="bg-gradient-to-b from-emerald-900/40 to-emerald-950/80 rounded-3xl border border-emerald-700/60 p-5 sm:p-7 shadow-2xl relative space-y-6">
            
            <!-- Card Header: Madrasah Identity -->
            <div class="flex items-center justify-between border-b border-emerald-800/80 pb-4">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white p-2 flex items-center justify-center shadow-md">
                  <img v-if="settings.app_logo" :src="getStorageUrl(settings.app_logo)" class="w-full h-full object-contain" alt="Logo Madrasah" />
                  <School v-else class="w-6 h-6 text-emerald-800" />
                </div>
                <div>
                  <h3 class="text-sm font-black text-white uppercase tracking-wider font-lexend">{{ settings.app_name || 'MTs Al - Hasanah' }}</h3>
                  <p class="text-[11px] text-emerald-300 font-medium">NSM / NPSN Resmi Kemenag</p>
                </div>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 uppercase">
                Aktif
              </span>
            </div>

            <!-- Stats Showcase in Clean Academic Grid -->
            <div class="grid grid-cols-3 gap-3">
              <div class="bg-emerald-950/80 rounded-2xl border border-emerald-800/60 p-3.5 text-center">
                <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Siswa Aktif</p>
                <p class="text-2xl font-black text-white font-lexend mt-1">{{ animatedStats.students }}</p>
                <p class="text-[9px] text-emerald-300/60 mt-0.5">Peserta Didik</p>
              </div>

              <div class="bg-emerald-950/80 rounded-2xl border border-emerald-800/60 p-3.5 text-center">
                <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Dewan Guru</p>
                <p class="text-2xl font-black text-white font-lexend mt-1">{{ animatedStats.teachers }}</p>
                <p class="text-[9px] text-emerald-300/60 mt-0.5">Pendidik & Staf</p>
              </div>

              <div class="bg-emerald-950/80 rounded-2xl border border-emerald-800/60 p-3.5 text-center">
                <p class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Rombel</p>
                <p class="text-2xl font-black text-white font-lexend mt-1">{{ animatedStats.classes }}</p>
                <p class="text-[9px] text-emerald-300/60 mt-0.5">Ruang Kelas</p>
              </div>
            </div>

            <!-- Operational & Location Detail -->
            <div class="space-y-2.5 text-xs text-emerald-100/90 bg-emerald-950/60 rounded-2xl border border-emerald-800/60 p-4">
              <div class="flex items-start gap-2.5">
                <MapPin class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" />
                <span class="leading-relaxed">{{ currentAddress }}</span>
              </div>
              <div class="flex items-center gap-2.5 pt-1 border-t border-emerald-900/80">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-medium text-[11px] text-emerald-200">KBM Reguler: Senin &ndash; Sabtu | Pukul 07.00 &ndash; 15.00 WIB</span>
              </div>
            </div>

            <!-- Portal Login Quick Access for Teachers & Students -->
            <div class="pt-1">
              <RouterLink
                to="/login"
                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-emerald-700/80 hover:bg-emerald-600 text-white font-bold text-xs transition-colors shadow-sm cursor-pointer"
              >
                <span>Masuk ke Portal Akademik (Siswa & Guru)</span>
                <ArrowRight class="w-3.5 h-3.5" />
              </RouterLink>
            </div>

          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import {
  Users,
  Award,
  BookOpen,
  ArrowRight,
  ChevronRight,
  School,
  GraduationCap,
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

function getStorageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) {
    return path;
  }
  const cleanPath = path.replace(/^\/?storage\//, '').replace(/^\//, '');
  return `/storage/${cleanPath}`;
}

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
});

watch(
  () => props.stats,
  () => {
    runCountUp();
  },
  { deep: true }
);
</script>
