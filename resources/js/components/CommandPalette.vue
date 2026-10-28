<template>
  <Transition name="palette-fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-start justify-center pt-12 sm:pt-20 px-3 sm:px-4 no-print"
      @keydown.down.prevent="navigateResults(1)"
      @keydown.up.prevent="navigateResults(-1)"
      @keydown.enter.prevent="selectCurrentResult"
      @keydown.esc.prevent="close"
    >
      <!-- Backdrop with subtle blur -->
      <div
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
        @click="close"
      ></div>

      <!-- Main Modal Container -->
      <div
        class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200/90 overflow-hidden flex flex-col max-h-[82vh] sm:max-h-[75vh] animate-scale-in font-inter z-10"
      >
        <!-- Search Input Bar -->
        <div class="relative flex items-center px-4 py-3.5 border-b border-slate-100 bg-white">
          <Search class="w-5 h-5 text-emerald-700 flex-shrink-0" />
          
          <input
            ref="searchInputRef"
            v-model="query"
            type="text"
            placeholder="Ketik untuk mencari menu, siswa, guru, atau aksi cepat..."
            class="w-full bg-transparent border-0 px-3 py-1 text-sm font-semibold text-slate-800 placeholder:text-slate-400 placeholder:font-normal focus:outline-none focus:ring-0"
            @input="handleInput"
          />

          <!-- Loading Spinner or Clear Button -->
          <div class="flex items-center gap-1.5 flex-shrink-0">
            <div
              v-if="searchingServer"
              class="animate-spin w-4 h-4 border-2 border-emerald-600 border-t-transparent rounded-full mr-1"
            ></div>
            <button
              v-else-if="query"
              type="button"
              @click="clearQuery"
              class="p-1 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
            >
              <X class="w-4 h-4" />
            </button>
            <kbd class="hidden sm:inline-flex items-center gap-0.5 px-2 py-0.5 text-[10px] font-bold text-slate-500 bg-slate-100 border border-slate-200 rounded-md shadow-2xs font-mono">
              ESC
            </kbd>
          </div>
        </div>

        <!-- Filter Tab Pills -->
        <div class="px-4 py-2 bg-slate-50/80 border-b border-slate-100 flex items-center gap-1.5 overflow-x-auto scrollbar-none text-xs">
          <button
            v-for="cat in categories"
            :key="cat.key"
            type="button"
            @click="selectedCategory = cat.key"
            class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition-all whitespace-nowrap cursor-pointer select-none"
            :class="selectedCategory === cat.key ? 'bg-slate-900 text-white shadow-2xs' : 'text-slate-600 hover:bg-slate-200/60 hover:text-slate-900'"
          >
            <span>{{ cat.label }}</span>
            <span
              v-if="cat.count !== undefined"
              class="ml-1 text-[9.5px] px-1 py-0.2 rounded-full"
              :class="selectedCategory === cat.key ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
            >
              {{ cat.count }}
            </span>
          </button>
        </div>

        <!-- Scrollable Results List -->
        <div
          ref="resultsListRef"
          class="flex-1 overflow-y-auto divide-y divide-slate-100 p-2 sm:p-3 space-y-4"
        >
          <!-- Empty State -->
          <div
            v-if="filteredTotalCount === 0"
            class="py-12 px-4 text-center space-y-2 text-slate-400"
          >
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 border border-slate-200">
              <SearchX class="w-6 h-6" />
            </div>
            <p class="text-xs font-bold text-slate-700">Tidak ada hasil yang cocok</p>
            <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
              Tidak ditemukan data untuk kata kunci "<span class="text-slate-700 font-semibold">{{ query }}</span>". Coba kata kunci lain atau pilih tab "Semua".
            </p>
          </div>

          <!-- Section 1: Menu & Navigasi -->
          <div v-if="filteredMenus.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
              <span>Menu Navigasi</span>
              <span class="text-emerald-700 font-mono">{{ filteredMenus.length }}</span>
            </div>
            <div
              v-for="(item, idx) in filteredMenus"
              :key="'menu-' + item.route"
              @click="handleSelect(item)"
              @mouseenter="setSelectedIndexByItem(item)"
              class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs transition-all cursor-pointer"
              :class="isSelected(item) ? 'bg-emerald-50 text-emerald-950 font-bold ring-1 ring-emerald-300 shadow-2xs' : 'text-slate-700 hover:bg-slate-50'"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
                  :class="isSelected(item) ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'"
                >
                  <component :is="item.icon" class="w-4 h-4" />
                </div>
                <div class="truncate">
                  <p class="truncate font-bold text-slate-800" :class="{ 'text-emerald-950': isSelected(item) }">
                    {{ item.title }}
                  </p>
                  <p class="text-[10px] text-slate-400 font-normal truncate">
                    {{ item.category }} • {{ item.route }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-[9.5px] px-1.5 py-0.5 rounded-md font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                  {{ item.category }}
                </span>
                <CornerDownLeft class="w-3.5 h-3.5 text-slate-400 transition-opacity" :class="isSelected(item) ? 'opacity-100 text-emerald-600' : 'opacity-0'" />
              </div>
            </div>
          </div>

          <!-- Section 2: Data Siswa (Live Search) -->
          <div v-if="filteredStudents.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
              <span>Data Siswa</span>
              <span class="text-blue-600 font-mono">{{ filteredStudents.length }}</span>
            </div>
            <div
              v-for="st in filteredStudents"
              :key="'student-' + st.id"
              @click="handleSelect(st)"
              @mouseenter="setSelectedIndexByItem(st)"
              class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs transition-all cursor-pointer"
              :class="isSelected(st) ? 'bg-blue-50 text-blue-950 font-bold ring-1 ring-blue-300 shadow-2xs' : 'text-slate-700 hover:bg-slate-50'"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center font-bold text-slate-600">
                  <img v-if="st.photo_url" :src="st.photo_url" class="w-full h-full object-cover" alt="Foto" />
                  <span v-else class="text-xs">{{ st.title?.charAt(0) || 'S' }}</span>
                </div>
                <div class="truncate">
                  <div class="flex items-center gap-2">
                    <p class="truncate font-bold text-slate-900" :class="{ 'text-blue-950': isSelected(st) }">
                      {{ st.title }}
                    </p>
                    <span
                      class="px-1.5 py-0.2 rounded text-[9px] font-bold shrink-0"
                      :class="st.gender === 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'"
                    >
                      {{ st.gender === 'L' ? 'L' : 'P' }}
                    </span>
                  </div>
                  <p class="text-[10px] text-slate-500 font-normal truncate">
                    {{ st.subtitle }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-[9.5px] px-2 py-0.5 rounded-md font-bold bg-blue-100 text-blue-800">
                  Siswa
                </span>
                <CornerDownLeft class="w-3.5 h-3.5 text-slate-400 transition-opacity" :class="isSelected(st) ? 'opacity-100 text-blue-600' : 'opacity-0'" />
              </div>
            </div>
          </div>

          <!-- Section 3: Data Guru (Live Search) -->
          <div v-if="filteredTeachers.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
              <span>Dewan Guru & Staf</span>
              <span class="text-teal-600 font-mono">{{ filteredTeachers.length }}</span>
            </div>
            <div
              v-for="tc in filteredTeachers"
              :key="'teacher-' + tc.id"
              @click="handleSelect(tc)"
              @mouseenter="setSelectedIndexByItem(tc)"
              class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs transition-all cursor-pointer"
              :class="isSelected(tc) ? 'bg-teal-50 text-teal-950 font-bold ring-1 ring-teal-300 shadow-2xs' : 'text-slate-700 hover:bg-slate-50'"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0 flex items-center justify-center font-bold text-slate-600">
                  <img v-if="tc.photo_url" :src="tc.photo_url" class="w-full h-full object-cover" alt="Foto" />
                  <span v-else class="text-xs">{{ tc.title?.charAt(0) || 'G' }}</span>
                </div>
                <div class="truncate">
                  <p class="truncate font-bold text-slate-900" :class="{ 'text-teal-950': isSelected(tc) }">
                    {{ tc.title }}
                  </p>
                  <p class="text-[10px] text-slate-500 font-normal truncate">
                    {{ tc.subtitle }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-[9.5px] px-2 py-0.5 rounded-md font-bold bg-teal-100 text-teal-800">
                  Guru
                </span>
                <CornerDownLeft class="w-3.5 h-3.5 text-slate-400 transition-opacity" :class="isSelected(tc) ? 'opacity-100 text-teal-600' : 'opacity-0'" />
              </div>
            </div>
          </div>

          <!-- Section 4: Aksi Cepat (Quick Actions) -->
          <div v-if="filteredActions.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
              <span>Aksi Sistem</span>
              <span class="text-purple-600 font-mono">{{ filteredActions.length }}</span>
            </div>
            <div
              v-for="act in filteredActions"
              :key="'action-' + act.key"
              @click="handleSelect(act)"
              @mouseenter="setSelectedIndexByItem(act)"
              class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs transition-all cursor-pointer"
              :class="isSelected(act) ? 'bg-purple-50 text-purple-950 font-bold ring-1 ring-purple-300 shadow-2xs' : 'text-slate-700 hover:bg-slate-50'"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
                  :class="isSelected(act) ? 'bg-purple-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'"
                >
                  <component :is="act.icon" class="w-4 h-4" />
                </div>
                <div class="truncate">
                  <p class="truncate font-bold text-slate-800" :class="{ 'text-purple-950': isSelected(act) }">
                    {{ act.title }}
                  </p>
                  <p class="text-[10px] text-slate-400 font-normal truncate">
                    {{ act.subtitle }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-[9.5px] px-2 py-0.5 rounded-md font-bold bg-purple-100 text-purple-800">
                  Aksi
                </span>
                <CornerDownLeft class="w-3.5 h-3.5 text-slate-400 transition-opacity" :class="isSelected(act) ? 'opacity-100 text-purple-600' : 'opacity-0'" />
              </div>
            </div>
          </div>
        </div>

        <!-- Palette Keyboard Guide Footer -->
        <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 font-medium">
          <div class="flex items-center gap-3 flex-wrap">
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600 font-mono text-[10px]">↑↓</kbd>
              <span>Navigasi</span>
            </span>
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600 font-mono text-[10px]">↵</kbd>
              <span>Pilih</span>
            </span>
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600 font-mono text-[10px]">Esc</kbd>
              <span>Tutup</span>
            </span>
          </div>

          <div class="text-[10px] font-bold text-emerald-700">
            SIAKAD Quick Search
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { api } from '../api';
import {
  Search,
  X,
  SearchX,
  CornerDownLeft,
  LayoutDashboard,
  Users,
  GraduationCap,
  Calendar,
  BookOpen,
  ClipboardList,
  Printer,
  Settings,
  Database,
  KeyRound,
  LogOut,
  Mail,
  UserCheck,
  Award,
  CheckCircle2,
  FileSpreadsheet,
} from 'lucide-vue-next';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'open-change-password', 'logout']);

const router = useRouter();
const auth = useAuthStore();

const searchInputRef = ref(null);
const resultsListRef = ref(null);
const query = ref('');
const selectedCategory = ref('all');
const selectedIndex = ref(0);
const searchingServer = ref(false);

const serverStudents = ref([]);
const serverTeachers = ref([]);

let searchTimeout = null;

// Categories definition
const categories = computed(() => [
  { key: 'all', label: 'Semua', count: filteredTotalCount.value },
  { key: 'menu', label: 'Menu', count: filteredMenus.value.length },
  { key: 'student', label: 'Siswa', count: filteredStudents.value.length },
  { key: 'teacher', label: 'Guru', count: filteredTeachers.value.length },
  { key: 'action', label: 'Aksi', count: filteredActions.value.length },
]);

// Master Menus Definition
const baseMenus = [
  { title: 'Dashboard Utama', category: 'Utama', route: '/dashboard', icon: LayoutDashboard, keywords: 'beranda home ringkasan statistik' },
  { title: 'Manajemen Data Siswa', category: 'Master', route: '/admin/students', icon: Users, keywords: 'murid siswa biodata kelas data' },
  { title: 'Manajemen Data Guru', category: 'Master', route: '/admin/teachers', icon: GraduationCap, keywords: 'guru dewan pendidik ustadz' },
  { title: 'Manajemen Kelas & Rombel', category: 'Master', route: '/admin/classes', icon: Users, keywords: 'kelas rombel ruangan utama lokal' },
  { title: 'Mata Pelajaran (Mapel)', category: 'Master', route: '/admin/subjects', icon: BookOpen, keywords: 'pelajaran kurikulum mapel materi' },
  { title: 'Jadwal Pelajaran', category: 'Akademik', route: '/admin/schedules', icon: Calendar, keywords: 'jadwal jam mengajar kbm' },
  { title: 'Presensi Harian Wali Kelas', category: 'Presensi', route: '/teacher/homeroom-attendance', icon: ClipboardList, keywords: 'absen kehadiran harian wali kelas siswa' },
  { title: 'Presensi Mata Pelajaran', category: 'Presensi', route: '/teacher/attendance', icon: ClipboardList, keywords: 'absen kelas mengajar matpel guru' },
  { title: 'Presensi Guru GPS & QR', category: 'Presensi', route: '/teacher/presensi', icon: UserCheck, keywords: 'absen guru datang pulang gps kamera' },
  { title: 'Monitoring Presensi Guru', category: 'Presensi', route: '/admin/teacher-presensi-monitoring', icon: UserCheck, keywords: 'pantau kehadiran guru staf harian' },
  { title: 'Input Nilai Siswa', category: 'Penilaian', route: '/teacher/grades', icon: Award, keywords: 'nilai harian sumatif formatif tugas' },
  { title: 'Koreksi Soal & Analisis Asesmen', category: 'Penilaian', route: '/teacher/exam-corrections', icon: CheckCircle2, keywords: 'ujian analisis butir capaian nilai soal' },
  { title: 'Rapor Tengah Semester (ASTS)', category: 'Penilaian', route: '/admin/asts-reports', icon: FileSpreadsheet, keywords: 'rapor tengah semester leger asts wali kelas' },
  { title: 'Kenaikan Kelas & Kelulusan', category: 'Kesiswaan', route: '/admin/promotions', icon: GraduationCap, keywords: 'naik kelas mutasi kelulusan tingkat' },
  { title: 'Buku Induk Alumni', category: 'Kesiswaan', route: '/admin/alumni', icon: GraduationCap, keywords: 'lulusan tamatan alumni arsip' },
  { title: 'Pusat Cetak Dokumen', category: 'Cetak', route: '/admin/print-center', icon: Printer, keywords: 'cetak kartu surat lembar leger raport pdf' },
  { title: 'Agenda Persuratan', category: 'Persuratan', route: '/admin/letters', icon: Mail, keywords: 'surat masuk keluar arsip nomor agenda' },
  { title: 'Cadangan & Pemulihan Database', category: 'Sistem', route: '/admin/database-backup', icon: Database, keywords: 'backup restore sql migrasi hosting dump' },
  { title: 'Pengaturan Sekolah / Profil Lembaga', category: 'Sistem', route: '/admin/settings', icon: Settings, keywords: 'setting nama logo madrasah yayasan kontak' },
  { title: 'Manajemen Pengguna & Akun', category: 'Sistem', route: '/admin/users', icon: KeyRound, keywords: 'akun role password user admin operator' },
];

// Quick Actions Definition
const quickActions = [
  { key: 'change-password', title: 'Ubah Password Akun', subtitle: 'Ganti password login mandiri', icon: KeyRound, type: 'action' },
  { key: 'my-profile', title: 'Profil Saya', subtitle: 'Lihat dan edit biodata akun', route: '/admin/profile', icon: Users, type: 'menu' },
  { key: 'logout', title: 'Keluar / Logout', subtitle: 'Keluar dari sesi aplikasi', icon: LogOut, type: 'action' },
];

// Filtered items
const filteredMenus = computed(() => {
  if (selectedCategory.value !== 'all' && selectedCategory.value !== 'menu') return [];
  const q = query.value.toLowerCase().trim();
  if (!q) return baseMenus.slice(0, 8);
  return baseMenus.filter(m => 
    m.title.toLowerCase().includes(q) ||
    m.category.toLowerCase().includes(q) ||
    m.keywords.toLowerCase().includes(q)
  );
});

const filteredStudents = computed(() => {
  if (selectedCategory.value !== 'all' && selectedCategory.value !== 'student') return [];
  return serverStudents.value;
});

const filteredTeachers = computed(() => {
  if (selectedCategory.value !== 'all' && selectedCategory.value !== 'teacher') return [];
  return serverTeachers.value;
});

const filteredActions = computed(() => {
  if (selectedCategory.value !== 'all' && selectedCategory.value !== 'action') return [];
  const q = query.value.toLowerCase().trim();
  if (!q) return quickActions;
  return quickActions.filter(a => 
    a.title.toLowerCase().includes(q) ||
    a.subtitle.toLowerCase().includes(q)
  );
});

// All combined flat array for keyboard navigation
const allVisibleItems = computed(() => {
  return [
    ...filteredMenus.value,
    ...filteredStudents.value,
    ...filteredTeachers.value,
    ...filteredActions.value,
  ];
});

const filteredTotalCount = computed(() => allVisibleItems.value.length);

const isSelected = (item) => {
  const current = allVisibleItems.value[selectedIndex.value];
  if (!current) return false;
  if (item.id && current.id) return item.id === current.id && item.type === current.type;
  if (item.route && current.route) return item.route === current.route;
  if (item.key && current.key) return item.key === current.key;
  return item.title === current.title;
};

const setSelectedIndexByItem = (item) => {
  const idx = allVisibleItems.value.findIndex(i => {
    if (item.id && i.id) return item.id === i.id && item.type === i.type;
    if (item.route && i.route) return item.route === i.route;
    if (item.key && i.key) return item.key === i.key;
    return item.title === i.title;
  });
  if (idx !== -1) selectedIndex.value = idx;
};

const navigateResults = (direction) => {
  const total = allVisibleItems.value.length;
  if (total === 0) return;
  selectedIndex.value = (selectedIndex.value + direction + total) % total;
};

const selectCurrentResult = () => {
  const item = allVisibleItems.value[selectedIndex.value];
  if (item) {
    handleSelect(item);
  }
};

const handleSelect = (item) => {
  close();
  if (item.key === 'change-password') {
    emit('open-change-password');
  } else if (item.key === 'logout') {
    emit('logout');
  } else if (item.route) {
    router.push(item.route);
  }
};

const handleInput = () => {
  selectedIndex.value = 0;
  clearTimeout(searchTimeout);

  const q = query.value.trim();
  if (q.length < 2) {
    serverStudents.value = [];
    serverTeachers.value = [];
    searchingServer.value = false;
    return;
  }

  searchingServer.value = true;
  searchTimeout = setTimeout(async () => {
    try {
      const res = await api.get('quick-search', { q });
      const data = res?.data?.data || res?.data || {};
      serverStudents.value = data.students || [];
      serverTeachers.value = data.teachers || [];
    } catch (err) {
      console.error('Quick search error:', err);
    } finally {
      searchingServer.value = false;
    }
  }, 250);
};

const clearQuery = () => {
  query.value = '';
  serverStudents.value = [];
  serverTeachers.value = [];
  selectedIndex.value = 0;
  searchInputRef.value?.focus();
};

const close = () => {
  emit('close');
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    selectedIndex.value = 0;
    nextTick(() => {
      searchInputRef.value?.focus();
    });
  } else {
    clearQuery();
  }
});
</script>

<style scoped>
.palette-fade-enter-active,
.palette-fade-leave-active {
  transition: opacity 0.15s ease-out;
}
.palette-fade-enter-from,
.palette-fade-leave-to {
  opacity: 0;
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.97) translateY(-8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
.animate-scale-in {
  animation: scaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
