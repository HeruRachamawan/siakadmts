<template>
  <div class="space-y-6 font-inter">
    <!-- Top Header -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-slate-900/10 flex-shrink-0">
          <Activity class="w-6 h-6 text-emerald-400" />
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Monitoring Absensi Siswa Harian</h1>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">Pantau kepatuhan pengisian presensi oleh Wali Kelas dan statistik kehadiran harian secara real-time.</p>
        </div>
      </div>

      <!-- Action & Date Filter -->
      <div class="flex items-center gap-2.5 flex-wrap">
        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl p-1">
          <Calendar class="w-4 h-4 text-slate-400 ml-2" />
          <input
            v-model="selectedDate"
            @change="fetchMonitoringData"
            type="date"
            class="bg-transparent text-xs font-bold text-slate-700 px-2 py-1 focus:outline-none cursor-pointer"
          />
          <button
            type="button"
            @click="setToday"
            class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 transition-colors shadow-2xs cursor-pointer ml-1"
          >
            Hari Ini
          </button>
        </div>

        <button
          @click="exportExcelMonitoring"
          class="px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 transition-colors flex items-center gap-2 shadow-xs cursor-pointer"
        >
          <Download class="w-4 h-4 text-emerald-600" />
          <span>Export Excel</span>
        </button>

        <button
          @click="fetchMonitoringData"
          class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs transition-colors flex items-center justify-center cursor-pointer"
          title="Refresh Data"
        >
          <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
        </button>
      </div>
    </div>

    <!-- Summary Metrics Cards with Lucide Icons -->
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3.5">
      <!-- Card 1: Progres Kelas -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 col-span-2 flex items-center justify-between">
        <div>
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Progres Input Wali Kelas</p>
          <div class="flex items-baseline gap-1.5 mt-1">
            <span class="text-2xl font-black text-slate-900 font-lexend">{{ summary.submitted_classes_count }}</span>
            <span class="text-xs font-bold text-slate-400">/ {{ summary.total_classes }} Kelas Selesai</span>
          </div>
          <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
            <div
              class="bg-emerald-500 h-2 rounded-full transition-all duration-500"
              :style="{ width: `${submissionPercentage}%` }"
            ></div>
          </div>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center font-black text-sm flex-shrink-0">
          {{ submissionPercentage }}%
        </div>
      </div>

      <!-- Card 2: Total Siswa -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0">
          <Users class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa</p>
          <p class="text-xl font-black text-slate-800 font-lexend mt-0.5">{{ summary.total_students_school }}</p>
        </div>
      </div>

      <!-- Card 3: Hadir -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
          <CheckCircle2 class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Hadir ({{ summary.overall_percentage }}%)</p>
          <p class="text-xl font-black text-emerald-800 font-lexend mt-0.5">{{ summary.total_present }}</p>
        </div>
      </div>

      <!-- Card 4: Sakit / Izin -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
          <HeartPulse class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-sky-700 uppercase tracking-wider">Sakit + Izin</p>
          <p class="text-xl font-black text-sky-800 font-lexend mt-0.5">{{ summary.total_sick + summary.total_permission }}</p>
        </div>
      </div>

      <!-- Card 5: Alpa -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
          <XCircle class="w-5 h-5" />
        </div>
        <div>
          <p class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Alpa</p>
          <p class="text-xl font-black text-rose-800 font-lexend mt-0.5">{{ summary.total_alpha }}</p>
        </div>
      </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-2 flex-wrap">
        <button
          @click="statusFilter = 'all'"
          :class="[
            statusFilter === 'all' ? 'bg-[#111827] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
            'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer'
          ]"
        >
          Semua Kelas ({{ classes.length }})
        </button>

        <button
          @click="statusFilter = 'submitted'"
          :class="[
            statusFilter === 'submitted' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
            'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5'
          ]"
        >
          <span>Sudah Diabsen</span>
          <span class="px-1.5 py-0.2 bg-white/20 rounded-full text-[10px]">{{ summary.submitted_classes_count }}</span>
        </button>

        <button
          @click="statusFilter = 'unsubmitted'"
          :class="[
            statusFilter === 'unsubmitted' ? 'bg-rose-600 text-white shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100',
            'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5'
          ]"
        >
          <span>Belum Diabsen</span>
          <span class="px-1.5 py-0.2 bg-white/20 rounded-full text-[10px]">{{ summary.unsubmitted_classes_count }}</span>
        </button>
      </div>

      <div class="relative min-w-[220px]">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kelas atau nama wali kelas..."
          class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-1.5 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400/30"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white rounded-[2rem] p-16 text-center text-slate-400 text-xs font-medium border border-slate-100">
      <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-3"></div>
      Memuat status absensi siswa per kelas...
    </div>

    <!-- Classes Grid Matrix -->
    <div v-else-if="filteredClasses.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="cls in filteredClasses"
        :key="cls.class_id"
        class="bg-white rounded-3xl p-5 shadow-xs border transition-all duration-200 hover:shadow-md flex flex-col justify-between"
        :class="cls.is_submitted ? 'border-emerald-200/80 hover:border-emerald-400' : 'border-rose-200/80 hover:border-rose-400'"
      >
        <!-- Card Header -->
        <div class="space-y-3">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="flex items-center gap-2">
                <span class="text-base font-black text-slate-900 font-lexend">Kelas {{ cls.class_name }}</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                  Tingkat {{ cls.grade_level }}
                </span>
              </div>
              <p class="text-xs text-slate-400 mt-0.5 font-medium">{{ cls.students_count }} Siswa Terdaftar</p>
            </div>

            <!-- Status Badge -->
            <span
              class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase flex items-center gap-1.5 shadow-2xs"
              :class="cls.is_submitted ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200'"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="cls.is_submitted ? 'bg-emerald-600' : 'bg-rose-600 animate-pulse'"></span>
              <span>{{ cls.is_submitted ? 'Sudah Diabsen' : 'Belum Diabsen' }}</span>
            </span>
          </div>

          <!-- Homeroom Teacher Info -->
          <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                <UserCheck class="w-4 h-4" />
              </div>
              <div class="min-w-0">
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Wali Kelas</p>
                <p class="text-xs font-bold text-slate-800 truncate">{{ cls.homeroom_teacher_name }}</p>
              </div>
            </div>

            <!-- WhatsApp Chat Button if available -->
            <a
              v-if="cls.homeroom_teacher_phone"
              :href="`https://wa.me/${cleanPhone(cls.homeroom_teacher_phone)}`"
              target="_blank"
              class="p-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-xs transition-colors flex items-center justify-center cursor-pointer flex-shrink-0"
              title="Chat WhatsApp Wali Kelas"
            >
              <MessageCircle class="w-3.5 h-3.5" />
            </a>
          </div>

          <!-- Attendance Stats breakdown -->
          <div v-if="cls.is_submitted" class="grid grid-cols-4 gap-1.5 text-center">
            <div class="bg-emerald-50 p-2 rounded-xl border border-emerald-100">
              <p class="text-[9px] font-black text-emerald-800 uppercase">Hadir</p>
              <p class="text-sm font-black text-emerald-700 mt-0.5">{{ cls.present }}</p>
            </div>
            <div class="bg-sky-50 p-2 rounded-xl border border-sky-100">
              <p class="text-[9px] font-black text-sky-800 uppercase">Sakit</p>
              <p class="text-sm font-black text-sky-700 mt-0.5">{{ cls.sick }}</p>
            </div>
            <div class="bg-amber-50 p-2 rounded-xl border border-amber-100">
              <p class="text-[9px] font-black text-amber-800 uppercase">Izin</p>
              <p class="text-sm font-black text-amber-700 mt-0.5">{{ cls.permission }}</p>
            </div>
            <div class="bg-rose-50 p-2 rounded-xl border border-rose-100">
              <p class="text-[9px] font-black text-rose-800 uppercase">Alpa</p>
              <p class="text-sm font-black text-rose-700 mt-0.5">{{ cls.alpha }}</p>
            </div>
          </div>

          <div v-else class="p-3 bg-rose-50/50 rounded-2xl border border-rose-100 text-center text-xs text-rose-700 font-medium flex items-center justify-center gap-2">
            <AlertCircle class="w-4 h-4 text-rose-500 flex-shrink-0" />
            <span>Belum ada catatan absensi yang disimpan pada tanggal ini.</span>
          </div>
        </div>

        <!-- Card Footer Action -->
        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
          <div class="text-[10px] text-slate-400 font-mono">
            {{ cls.submission_time ? `Update: ${cls.submission_time} WIB` : 'Belum diisi' }}
          </div>

          <button
            @click="openDetailModal(cls)"
            class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs"
          >
            <span>Rincian Siswa</span>
            <ChevronRight class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-[2rem] p-16 text-center space-y-3 border border-slate-100">
      <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto">
        <Search class="w-7 h-7" />
      </div>
      <p class="text-sm font-bold text-slate-700">Tidak Ada Kelas yang Cocok</p>
      <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau filter status pengisian.</p>
    </div>

    <!-- Modal Detail Siswa Kelas -->
    <div v-if="showDetailModal && activeClassDetail" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-[2rem] max-w-3xl w-full p-6 shadow-2xl space-y-5 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <div class="flex items-center gap-2">
              <h3 class="text-lg font-black text-slate-800 font-lexend uppercase">Rincian Siswa Kelas {{ activeClassDetail.class_name }}</h3>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase" :class="activeClassDetail.is_submitted ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                {{ activeClassDetail.is_submitted ? 'Sudah Diabsen' : 'Belum Diabsen' }}
              </span>
            </div>
            <p class="text-xs text-slate-400 font-medium mt-0.5">
              Wali Kelas: <span class="font-bold text-slate-700">{{ activeClassDetail.homeroom_teacher_name }}</span> &bull; Tanggal: <span class="font-bold text-slate-700 font-mono">{{ selectedDate }}</span>
            </p>
          </div>
          <button @click="showDetailModal = false" class="p-2 hover:bg-slate-100 rounded-full text-slate-400 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Student List Table -->
        <div class="flex-1 overflow-y-auto pr-1">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100 uppercase tracking-wider text-[10px]">
                <th class="p-3 w-10 text-center">NO</th>
                <th class="p-3">NAMA SISWA</th>
                <th class="p-3 w-28 text-center">NISN</th>
                <th class="p-3 w-28 text-center">STATUS</th>
                <th class="p-3">CATATAN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="(st, idx) in activeClassDetail.students" :key="st.student_id" class="hover:bg-slate-50/60">
                <td class="p-3 text-center font-bold text-slate-400 text-xs">{{ idx + 1 }}</td>
                <td class="p-3">
                  <p class="font-bold text-slate-800">{{ st.full_name }}</p>
                  <span class="text-[10px] text-slate-400">{{ st.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                </td>
                <td class="p-3 text-center font-mono font-bold text-slate-600 text-xs">{{ st.nisn || '-' }}</td>
                <td class="p-3 text-center">
                  <span
                    class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase"
                    :class="[
                      st.status === 'present' ? 'bg-emerald-100 text-emerald-800' :
                      st.status === 'sick' ? 'bg-sky-100 text-sky-800' :
                      st.status === 'permission' ? 'bg-amber-100 text-amber-800' :
                      st.status === 'alpha' ? 'bg-rose-100 text-rose-800' :
                      'bg-slate-100 text-slate-500'
                    ]"
                  >
                    {{ st.status === 'present' ? 'Hadir' : (st.status === 'sick' ? 'Sakit' : (st.status === 'permission' ? 'Izin' : (st.status === 'alpha' ? 'Alpa' : 'Belum')) ) }}
                  </span>
                </td>
                <td class="p-3 text-slate-600 italic text-[11px]">{{ st.note || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
          <button @click="showDetailModal = false" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  Activity,
  Calendar,
  Download,
  RefreshCw,
  Users,
  CheckCircle2,
  HeartPulse,
  XCircle,
  Search,
  UserCheck,
  MessageCircle,
  AlertCircle,
  ChevronRight,
  X
} from 'lucide-vue-next';

const toast = useToast();

const loading = ref(true);
const selectedDate = ref(new Date().toISOString().substring(0, 10));
const statusFilter = ref('all');
const searchQuery = ref('');

const summary = ref({
  total_classes: 0,
  submitted_classes_count: 0,
  unsubmitted_classes_count: 0,
  total_students_school: 0,
  total_present: 0,
  total_sick: 0,
  total_permission: 0,
  total_alpha: 0,
  overall_percentage: 0,
});

const classes = ref([]);
const showDetailModal = ref(false);
const activeClassDetail = ref(null);

const submissionPercentage = computed(() => {
  if (!summary.value.total_classes) return 0;
  return Math.round((summary.value.submitted_classes_count / summary.value.total_classes) * 100);
});

const filteredClasses = computed(() => {
  let list = classes.value;

  if (statusFilter.value === 'submitted') {
    list = list.filter(c => c.is_submitted);
  } else if (statusFilter.value === 'unsubmitted') {
    list = list.filter(c => !c.is_submitted);
  }

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter(c =>
      c.class_name?.toLowerCase().includes(q) ||
      c.homeroom_teacher_name?.toLowerCase().includes(q)
    );
  }

  return list;
});

const setToday = () => {
  selectedDate.value = new Date().toISOString().substring(0, 10);
  fetchMonitoringData();
};

const cleanPhone = (phone) => {
  if (!phone) return '';
  let p = phone.replace(/\D/g, '');
  if (p.startsWith('0')) {
    p = '62' + p.substring(1);
  }
  return p;
};

const openDetailModal = (cls) => {
  activeClassDetail.value = cls;
  showDetailModal.value = true;
};

const fetchMonitoringData = async () => {
  loading.value = true;
  try {
    const res = await api.get('admin/daily-student-attendance', {
      date: selectedDate.value,
    });
    const data = (res && res.data) ? res.data : (res || {});

    summary.value = data.summary || {
      total_classes: 0,
      submitted_classes_count: 0,
      unsubmitted_classes_count: 0,
      total_students_school: 0,
      total_present: 0,
      total_sick: 0,
      total_permission: 0,
      total_alpha: 0,
      overall_percentage: 0,
    };

    classes.value = data.classes || [];
  } catch (err) {
    console.error('Failed to load daily monitoring data:', err);
    toast.error('Gagal memuat data monitoring absensi harian');
  } finally {
    loading.value = false;
  }
};

const exportExcelMonitoring = () => {
  if (classes.value.length === 0) {
    toast.error('Tidak ada data kelas untuk diekspor');
    return;
  }

  const rows = [
    ['LAPORAN MONITORING PRESENSI SISWA HARIAN'],
    [`Tanggal: ${selectedDate.value}`],
    [`Dicetak Pada: ${new Date().toLocaleString('id-ID')}`],
    [],
    ['NO', 'KELAS', 'TINGKAT', 'WALI KELAS', 'STATUS INPUT', 'TOTAL SISWA', 'HADIR', 'SAKIT', 'IZIN', 'ALPA', '% HADIR', 'WAKTU INPUT']
  ];

  classes.value.forEach((cls, idx) => {
    rows.push([
      idx + 1,
      cls.class_name,
      cls.grade_level,
      cls.homeroom_teacher_name,
      cls.is_submitted ? 'SUDAH DIABSEN' : 'BELUM DIABSEN',
      cls.students_count,
      cls.present,
      cls.sick,
      cls.permission,
      cls.alpha,
      `${cls.percentage}%`,
      cls.submission_time || '-'
    ]);
  });

  const ws = XLSX.utils.aoa_to_sheet(rows);
  ws['!cols'] = [
    { wch: 6 },
    { wch: 14 },
    { wch: 10 },
    { wch: 28 },
    { wch: 18 },
    { wch: 12 },
    { wch: 10 },
    { wch: 10 },
    { wch: 10 },
    { wch: 10 },
    { wch: 12 },
    { wch: 14 }
  ];

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Monitoring Harian');
  XLSX.writeFile(wb, `Monitoring_Absensi_Siswa_${selectedDate.value}.xlsx`);
  toast.success('File Excel Monitoring Harian berhasil diunduh!');
};

onMounted(() => {
  fetchMonitoringData();
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
