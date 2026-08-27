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

    <!-- LIVE DAILY TEACHING SCHEDULE & KBM MONITORING MATRIX -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden space-y-6 p-6 sm:p-7">
      
      <!-- Header with Title & Quick Action -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
        <div class="space-y-1">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold border border-emerald-100 shadow-2xs">
              <CalendarDays class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Jadwal KBM & Guru Mengajar Harian</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                  {{ selectedDayLabel }}
                </span>
              </h2>
              <p class="text-xs text-slate-500 font-normal">
                Pantau pembagian jam mengajar guru, mata pelajaran, dan ruangan kelas secara real-time.
              </p>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <RouterLink
            to="/admin/schedules"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors shadow-2xs"
          >
            <Calendar class="w-3.5 h-3.5 text-slate-500" />
            <span>Kelola Master Jadwal &rarr;</span>
          </RouterLink>
        </div>
      </div>

      <!-- Day Selector Pills -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <button
          v-for="day in availableDays"
          :key="day.id"
          @click="selectedDay = day.id"
          :class="[
            selectedDay === day.id
              ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/20 ring-2 ring-emerald-600/30'
              : 'bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold border border-slate-200/80',
            'px-4 py-2 rounded-xl text-xs whitespace-nowrap cursor-pointer transition-all flex items-center gap-2'
          ]"
        >
          <span v-if="day.id === currentTodayDay" class="w-2 h-2 rounded-full" :class="selectedDay === day.id ? 'bg-amber-300 animate-pulse' : 'bg-emerald-500'"></span>
          <span>{{ day.name }}</span>
          <span v-if="day.id === currentTodayDay" class="text-[10px] font-bold px-1.5 py-0.2 rounded-md" :class="selectedDay === day.id ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800'">
            Hari Ini
          </span>
        </button>
      </div>

      <!-- Filter Bar & Daily Metrics Overview -->
      <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-200/80 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Search & Class Dropdown -->
        <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full lg:w-auto">
          <div class="relative w-full sm:w-64">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="scheduleSearch"
              type="text"
              placeholder="Cari guru, mapel, atau ruang..."
              class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>

          <div class="relative w-full sm:w-48">
            <select
              v-model="selectedClassFilter"
              class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
              <option value="">Semua Rombel Kelas</option>
              <option v-for="cls in classList" :key="cls.id" :value="cls.id">
                {{ cls.name }}
              </option>
            </select>
          </div>

          <button
            @click="loadSchedules"
            class="p-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 rounded-xl transition-colors cursor-pointer"
            title="Segarkan Jadwal"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loadingSchedules }" />
          </button>
        </div>

        <!-- Quick Metrics Pills -->
        <div class="flex items-center gap-3 flex-wrap text-xs text-slate-600 font-medium">
          <div class="inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
            <Users class="w-3.5 h-3.5 text-emerald-600" />
            <span>Guru Bertugas: <strong class="text-slate-900 font-bold">{{ dailyMetrics.teachersCount }}</strong></span>
          </div>
          <div class="inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
            <BookOpen class="w-3.5 h-3.5 text-blue-600" />
            <span>Total Jam KBM: <strong class="text-slate-900 font-bold">{{ dailyMetrics.sessionsCount }}</strong> Sesi</span>
          </div>
          <div class="inline-flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
            <Building2 class="w-3.5 h-3.5 text-amber-600" />
            <span>Rombel Aktif: <strong class="text-slate-900 font-bold">{{ dailyMetrics.classesCount }}</strong> Kelas</span>
          </div>
        </div>
      </div>

      <!-- Schedule Timetable Cards Grouped by Time Slots -->
      <div v-if="loadingSchedules" class="py-12 text-center text-slate-400 space-y-2">
        <RefreshCw class="w-6 h-6 animate-spin mx-auto text-emerald-600" />
        <p class="text-xs">Memuat jadwal KBM hari {{ selectedDayLabel }}...</p>
      </div>

      <div v-else-if="filteredGroupedSchedules.length === 0" class="py-12 text-center text-slate-400 space-y-3 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
        <Calendar class="w-10 h-10 mx-auto text-slate-300" />
        <div class="space-y-1">
          <p class="text-sm font-bold text-slate-700">Belum Ada Jadwal KBM pada Hari {{ selectedDayLabel }}</p>
          <p class="text-xs text-slate-400">Tidak ada sesi pelajaran atau kegiatan yang cocok dengan filter yang dipilih.</p>
        </div>
        <RouterLink
          to="/admin/schedules"
          class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all"
        >
          <span>+ Tambah Jadwal Pelajaran</span>
        </RouterLink>
      </div>

      <div v-else class="space-y-6">
        <div
          v-for="group in filteredGroupedSchedules"
          :key="group.timeSlot"
          class="space-y-3"
        >
          <!-- Time Slot Badge Header -->
          <div class="flex items-center gap-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-900 text-white rounded-xl text-xs font-bold font-mono tracking-tight shadow-xs">
              <Clock class="w-3.5 h-3.5 text-emerald-400" />
              <span>{{ group.timeSlot }} WIB</span>
            </div>
            <div class="h-px bg-slate-200 flex-1"></div>
            <span class="text-[11px] font-semibold text-slate-400">
              {{ group.items.length }} Sesi Belajar
            </span>
          </div>

          <!-- Schedule Grid Cards for this Time Slot -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
            <div
              v-for="sch in group.items"
              :key="sch.id"
              :class="[
                sch.is_activity 
                  ? 'bg-amber-50/70 border-amber-200/80 shadow-xs' 
                  : 'bg-white border-slate-200/90 shadow-2xs hover:shadow-md hover:border-emerald-300',
                'p-4 rounded-2xl border transition-all space-y-3 relative overflow-hidden group'
              ]"
            >
              <!-- Top Header: Subject Badge & Class Badge -->
              <div class="flex items-start justify-between gap-2">
                <div>
                  <span
                    v-if="sch.is_activity"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold bg-amber-200 text-amber-900 uppercase"
                  >
                    ⭐ {{ sch.activity_name || 'Kegiatan Umum' }}
                  </span>
                  <div v-else class="space-y-0.5">
                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200/70 uppercase tracking-wide">
                      {{ sch.subject?.code || 'MAPEL' }}
                    </span>
                    <h4 class="text-sm font-bold text-slate-900 line-clamp-1 group-hover:text-emerald-700 transition-colors">
                      {{ sch.subject?.name || sch.activity_name || 'Pelajaran' }}
                    </h4>
                  </div>
                </div>

                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-100 whitespace-nowrap shadow-2xs">
                  <Building2 class="w-3 h-3 text-indigo-500" />
                  <span>{{ sch.class_room?.name || sch.classRoom?.name || 'Semua Kelas' }}</span>
                </span>
              </div>

              <!-- Teacher Identity & Info -->
              <div v-if="!sch.is_activity && sch.teacher" class="flex items-center gap-3 pt-1 border-t border-slate-100">
                <!-- Teacher Photo Avatar -->
                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center flex-shrink-0 relative shadow-inner">
                  <img
                    v-if="sch.teacher?.photo_url || sch.teacher?.photo"
                    :src="getImageUrl(sch.teacher.photo_url || sch.teacher.photo)"
                    class="w-full h-full object-cover"
                    alt="Foto Guru"
                  />
                  <div v-else class="w-full h-full bg-emerald-700 text-white font-bold text-xs flex items-center justify-center">
                    {{ sch.teacher?.full_name?.charAt(0) || 'G' }}
                  </div>
                </div>

                <!-- Teacher Name, NIP, Phone Action -->
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-bold text-slate-900 truncate leading-snug">
                    {{ sch.teacher?.full_name || '-' }}
                  </p>
                  <p class="text-[10px] text-slate-500 font-mono truncate">
                    {{ sch.teacher?.nip ? `NIP: ${sch.teacher.nip}` : 'Tenaga Pendidik' }}
                  </p>
                </div>

                <!-- WhatsApp Quick Action Button -->
                <a
                  v-if="sch.teacher?.phone"
                  :href="`https://wa.me/${formatWaNumber(sch.teacher.phone)}?text=Assalamu'alaikum%20Bapak/Ibu%20${encodeURIComponent(sch.teacher.full_name)},%20mohon%20konfirmasi%20KBM%20mapel%20${encodeURIComponent(sch.subject?.name || '')}%20di%20kelas%20${encodeURIComponent(sch.class_room?.name || sch.classRoom?.name || '')}`"
                  target="_blank"
                  class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg transition-colors cursor-pointer shadow-2xs"
                  title="Hubungi Guru via WhatsApp"
                >
                  <Phone class="w-3.5 h-3.5" />
                </a>
              </div>

              <!-- Footer: Room & Sesi Info -->
              <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                <span class="flex items-center gap-1 text-slate-600">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                  Ruang: <strong>{{ sch.room || 'Ruang Kelas' }}</strong>
                </span>
                <span class="font-mono text-slate-400">
                  {{ sch.start_time?.substring(0, 5) }} - {{ sch.end_time?.substring(0, 5) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
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
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2 text-slate-600 leading-relaxed">
          <p>Fitur untuk Waka Kurikulum mengatur pembagian jam mengajar, hari, mata pelajaran, dan guru pengampu secara presisi serta otomatis mencegah jadwal bentrok.</p>
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
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2 text-slate-600 leading-relaxed">
          <p>Pantau jadwal Penilaian Tengah Semester (PTS), Penilaian Akhir Semester (PAS), agenda ujian madrasah, serta kalender libur semester.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  GraduationCap,
  Calendar,
  Award,
  BookOpen,
  Users,
  Building2,
  FileText,
  CalendarDays,
  Clock,
  Search,
  RefreshCw,
  Phone
} from 'lucide-vue-next';
import { api } from '../api';

const auth = useAuthStore();
const stats = ref({});
const livePhoto = ref(null);

// Schedules state
const schedules = ref([]);
const classList = ref([]);
const loadingSchedules = ref(false);
const scheduleSearch = ref('');
const selectedClassFilter = ref('');

const availableDays = [
  { id: 'senin', name: 'Senin' },
  { id: 'selasa', name: 'Selasa' },
  { id: 'rabu', name: 'Rabu' },
  { id: 'kamis', name: 'Kamis' },
  { id: 'jumat', name: 'Jumat' },
  { id: 'sabtu', name: 'Sabtu' },
];

function getTodayDayKey() {
  const dayIndex = new Date().getDay();
  const map = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
  const todayKey = map[dayIndex] || 'senin';
  return todayKey === 'minggu' ? 'senin' : todayKey;
}

const currentTodayDay = ref(getTodayDayKey());
const selectedDay = ref(getTodayDayKey());

const selectedDayLabel = computed(() => {
  const found = availableDays.find(d => d.id === selectedDay.value);
  return found ? found.name : 'Senin';
});

const userPhoto = computed(() => {
  return livePhoto.value || auth.user?.teacher?.photo_url || auth.user?.teacher?.photo || auth.user?.photo_url || auth.user?.photo || auth.user?.avatar || null;
});

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  if (path.startsWith('data:image')) return path;
  const clean = path.startsWith('/') ? path : `/${path}`;
  if (clean.startsWith('/storage/')) return clean;
  return `/storage/${path.replace(/^\/+/, '')}`;
}

function formatWaNumber(phone) {
  if (!phone) return '';
  let clean = phone.replace(/\D/g, '');
  if (clean.startsWith('0')) {
    clean = '62' + clean.slice(1);
  }
  return clean;
}

// Group schedules by time slot and apply filters
const filteredGroupedSchedules = computed(() => {
  const query = scheduleSearch.value.trim().toLowerCase();
  const classFilter = selectedClassFilter.value;

  const filtered = schedules.value.filter(s => {
    // Filter by class
    if (classFilter && (s.class_id != classFilter && s.class_room?.id != classFilter && s.classRoom?.id != classFilter)) {
      return false;
    }

    // Filter by search keyword
    if (query) {
      const matchSubject = s.subject?.name?.toLowerCase().includes(query) || s.subject?.code?.toLowerCase().includes(query);
      const matchTeacher = s.teacher?.full_name?.toLowerCase().includes(query) || s.teacher?.nip?.toLowerCase().includes(query);
      const matchClass = (s.class_room?.name || s.classRoom?.name || '').toLowerCase().includes(query);
      const matchRoom = (s.room || '').toLowerCase().includes(query);
      const matchActivity = (s.activity_name || '').toLowerCase().includes(query);
      return matchSubject || matchTeacher || matchClass || matchRoom || matchActivity;
    }

    return true;
  });

  // Group by Start - End Time
  const groupsMap = {};
  filtered.forEach(item => {
    const slot = `${(item.start_time || '00:00').substring(0, 5)} - ${(item.end_time || '00:00').substring(0, 5)}`;
    if (!groupsMap[slot]) {
      groupsMap[slot] = {
        timeSlot: slot,
        startTime: item.start_time || '00:00',
        items: []
      };
    }
    groupsMap[slot].items.push(item);
  });

  // Sort groups chronologically by start time
  return Object.values(groupsMap).sort((a, b) => a.startTime.localeCompare(b.startTime));
});

// Daily Quick Metrics
const dailyMetrics = computed(() => {
  const uniqueTeachers = new Set();
  const uniqueClasses = new Set();
  let sessionsCount = 0;

  schedules.value.forEach(s => {
    if (s.teacher_id || s.teacher) {
      uniqueTeachers.add(s.teacher_id || s.teacher?.id);
    }
    if (s.class_id || s.class_room?.id || s.classRoom?.id) {
      uniqueClasses.add(s.class_id || s.class_room?.id || s.classRoom?.id);
    }
    sessionsCount++;
  });

  return {
    teachersCount: uniqueTeachers.size,
    classesCount: uniqueClasses.size,
    sessionsCount
  };
});

async function loadSchedules() {
  loadingSchedules.value = true;
  try {
    const res = await api.get('admin/schedules', { day: selectedDay.value });
    schedules.value = res?.data || res || [];
  } catch (err) {
    console.error('Failed to load schedules for day', selectedDay.value, err);
  } finally {
    loadingSchedules.value = false;
  }
}

watch(selectedDay, () => {
  loadSchedules();
});

async function loadKurikulumStats() {
  try {
    const [dashRes, letterRes, profileRes, classesRes] = await Promise.all([
      api.get('admin/dashboard').catch(() => null),
      api.get('admin/letters').catch(() => null),
      api.get('teacher/profile').catch(() => null),
      api.get('admin/classes').catch(() => null)
    ]);
    const d = dashRes?.data?.data || dashRes?.data || dashRes || {};
    const l = letterRes?.data?.stats || letterRes?.stats || letterRes?.data || {};
    const p = profileRes?.data?.data || profileRes?.data?.teacher || profileRes?.data || {};
    const cls = classesRes?.data?.data || classesRes?.data || classesRes || [];

    classList.value = Array.isArray(cls) ? cls : [];

    if (p.photo_url || p.photo) {
      livePhoto.value = p.photo_url || p.photo;
    }
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
  loadSchedules();
});
</script>

