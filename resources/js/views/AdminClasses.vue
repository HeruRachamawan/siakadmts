<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0 text-white">
          <School class="w-6 h-6" />
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend tracking-wide uppercase">Manajemen Kelas</h1>
          <p class="text-xs text-slate-500 font-medium mt-0.5">Kelola data rombongan belajar reguler & kelas khusus Jadwal Lokal madrasah.</p>
        </div>
      </div>

      <div class="flex items-center gap-3 flex-wrap">
        <!-- Active Academic Year Badge & Bulk Update -->
        <div v-if="activeYear" class="flex items-center gap-2 bg-emerald-50/90 border border-emerald-200/80 px-3.5 py-2 rounded-xl shadow-2xs">
          <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
          <span class="text-xs font-bold text-emerald-900">
            T.A. Aktif: <strong>{{ activeYear.year }} ({{ activeYear.semester === 'odd' ? 'Ganjil' : 'Genap' }})</strong>
          </span>
          <button
            @click="syncClassesToActiveYear"
            title="Update Tahun Ajaran seluruh kelas ke Tahun Ajaran Aktif saat ini"
            class="ml-2 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black rounded-lg transition-colors cursor-pointer shadow-xs"
          >
            Update Semua Kelas
          </button>
        </div>
      </div>
    </div>

    <!-- Tab Switcher: Kelas Utama vs Kelas Lokal -->
    <div class="bg-white rounded-2xl p-3 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
      <!-- Tabs -->
      <div class="flex items-center gap-2 p-1 bg-slate-100/90 rounded-xl">
        <button
          type="button"
          @click="activeTab = 'utama'"
          :class="activeTab === 'utama' ? 'bg-white text-emerald-800 font-extrabold shadow-sm' : 'text-slate-500 hover:text-slate-800 font-bold'"
          class="px-5 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
        >
          <span class="text-sm">🏫</span>
          <span>Kelas Utama (Rombel Reguler)</span>
          <span
            class="text-[10px] px-2 py-0.5 rounded-md font-bold"
            :class="activeTab === 'utama' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'"
          >
            {{ utamaCount }} Kelas
          </span>
        </button>

        <button
          type="button"
          @click="activeTab = 'lokal'"
          :class="activeTab === 'lokal' ? 'bg-white text-teal-800 font-extrabold shadow-sm' : 'text-slate-500 hover:text-slate-800 font-bold'"
          class="px-5 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
        >
          <span class="text-sm">📍</span>
          <span>Kelas Jadwal Lokal</span>
          <span
            class="text-[10px] px-2 py-0.5 rounded-md font-bold"
            :class="activeTab === 'lokal' ? 'bg-teal-100 text-teal-800' : 'bg-slate-200 text-slate-600'"
          >
            {{ lokalCount }} Kelas (7, 8, 9A, 9B)
          </span>
        </button>
      </div>

      <!-- Tab Information Note -->
      <div class="text-xs text-slate-500 font-medium px-1 flex items-center gap-2">
        <span v-if="activeTab === 'utama'" class="flex items-center gap-1.5 text-slate-600">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span>Rombongan belajar nyata tempat siswa terdaftar (7A, 7B, 8A, 8B, 9A, 9B).</span>
        </span>
        <span v-else class="flex items-center gap-1.5 text-teal-700 font-medium">
          <span class="w-2 h-2 rounded-full bg-teal-500"></span>
          <span>4 Kelas khusus pemetaan Jadwal Lokal madrasah (7, 8, 9A, 9B).</span>
        </span>
      </div>
    </div>

    <!-- Action Bar: Search, View Toggle, Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <!-- Search Input -->
      <div class="relative flex-1 max-w-lg">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="activeTab === 'utama' ? 'Cari rombel reguler atau nama wali kelas...' : 'Cari kelas jadwal lokal...'"
          class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm text-slate-700 shadow-2xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-medium"
        />
        <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
      </div>

      <!-- Right Controls: View Switcher & Add Button -->
      <div class="flex items-center gap-2.5 flex-shrink-0">
        <!-- View Mode Switcher (Grid vs Table) -->
        <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-2xs">
          <button
            type="button"
            @click="viewMode = 'grid'"
            :class="viewMode === 'grid' ? 'bg-[#111827] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'"
            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
            title="Tampilan Kartu Grid"
          >
            <LayoutGrid class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Grid</span>
          </button>
          <button
            type="button"
            @click="viewMode = 'table'"
            :class="viewMode === 'table' ? 'bg-[#111827] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'"
            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer"
            title="Tampilan Tabel Baris"
          >
            <List class="w-3.5 h-3.5" />
            <span class="hidden sm:inline">Tabel</span>
          </button>
        </div>

        <!-- Add Button -->
        <button
          @click="openAddForm"
          class="flex items-center gap-2 px-4 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl text-xs sm:text-sm transition-all shadow-sm cursor-pointer active:scale-95"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Kelas</span>
        </button>
      </div>
    </div>

    <!-- LOADING STATE -->
    <div v-if="loading" class="text-center py-20 bg-white rounded-[2rem] border border-slate-100 shadow-sm">
      <div class="inline-flex w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
      <p class="text-sm font-semibold text-slate-500">Memuat data kelas...</p>
    </div>

    <template v-else>
      <!-- EMPTY STATE -->
      <div v-if="!filteredClasses.length" class="text-center py-20 bg-white rounded-[2rem] border border-slate-100 shadow-sm">
        <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-400">
          <School class="w-8 h-8 opacity-50" />
        </div>
        <h3 class="text-base font-bold text-slate-700">Tidak ada data kelas ditemukan</h3>
        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
          {{ searchQuery ? 'Tidak ada kelas yang cocok dengan kata kunci pencarian.' : 'Belum ada data kelas pada kategori ini.' }}
        </p>
      </div>

      <!-- 1. GRID VIEW (DEFAULT) -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-5">
        <div
          v-for="cls in filteredClasses"
          :key="'grid-' + cls.id"
          class="group bg-white rounded-[1.75rem] border border-slate-200/80 hover:border-slate-300 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden"
        >
          <!-- Card Header & Badge -->
          <div
            class="p-5 pb-4 border-b border-slate-100 bg-gradient-to-r"
            :class="getGradeTheme(cls.grade_level).bgGrad"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-3">
                <div
                  class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-lg tracking-wider shadow-md transition-transform group-hover:scale-105"
                  :class="getGradeTheme(cls.grade_level).iconBg"
                >
                  {{ cls.name }}
                </div>
                <div>
                  <h3 class="text-lg font-black text-slate-800 font-lexend tracking-tight">
                    Kelas {{ cls.name }}
                  </h3>
                  <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-md border" :class="getGradeTheme(cls.grade_level).badgeBg">
                      Tingkat {{ cls.grade_level || '-' }}
                    </span>
                    <span
                      v-if="isLokalOnly(cls)"
                      class="text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-teal-100 text-teal-800 border border-teal-200"
                    >
                      📍 Jadwal Lokal
                    </span>
                    <span
                      v-else-if="isBothUtamaAndLokal(cls)"
                      class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200"
                    >
                      Utama & Lokal
                    </span>
                  </div>
                </div>
              </div>

              <!-- Quick Action Dropdown / Buttons -->
              <div class="flex items-center gap-1">
                <button
                  @click="edit(cls)"
                  title="Edit Kelas"
                  class="w-8 h-8 rounded-xl flex items-center justify-center bg-white/90 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200 transition-all shadow-2xs cursor-pointer"
                >
                  <Pencil class="w-3.5 h-3.5" />
                </button>
                <button
                  @click="remove(cls)"
                  title="Hapus Kelas"
                  class="w-8 h-8 rounded-xl flex items-center justify-center bg-white/90 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200 transition-all shadow-2xs cursor-pointer"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Card Body: Wali Kelas & Info -->
          <div class="p-5 flex-1 space-y-4">
            <!-- Wali Kelas Block -->
            <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-100 space-y-1.5">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Wali Kelas</span>
              
              <div v-if="cls.homeroom_teacher?.full_name || cls.homeroomTeacher?.full_name" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-800 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm border-2 border-white">
                  <img
                    v-if="getTeacherPhoto(cls)"
                    :src="getTeacherPhoto(cls)"
                    alt="Foto Wali Kelas"
                    class="w-full h-full object-cover"
                  />
                  <span v-else>
                    {{ getTeacherInitials(cls) }}
                  </span>
                </div>
                <div class="overflow-hidden">
                  <h4 class="text-xs font-black text-slate-800 truncate" :title="getTeacherName(cls)">
                    {{ getTeacherName(cls) }}
                  </h4>
                  <p class="text-[11px] text-slate-500 font-mono truncate">
                    NIP: {{ getTeacherNip(cls) }}
                  </p>
                </div>
              </div>

              <div v-else class="flex items-center justify-between py-1">
                <span class="text-xs text-slate-400 italic">Belum ada wali kelas</span>
                <button
                  type="button"
                  @click="edit(cls)"
                  class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer"
                >
                  + Atur Wali
                </button>
              </div>
            </div>

            <!-- Stats Metrics: Siswa & Tahun Ajaran -->
            <div class="grid grid-cols-2 gap-2.5 text-center">
              <div class="p-3 bg-slate-50/80 rounded-2xl border border-slate-100 flex flex-col items-center justify-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                  <Users class="w-3 h-3 text-slate-400" />
                  <span>Siswa</span>
                </span>
                <span class="text-base font-black text-slate-800 font-lexend mt-0.5">
                  {{ isLokalOnly(cls) ? (cls.lokal_students_count ?? 0) : (cls.students_count ?? cls.students?.length ?? 0) }}
                </span>
                <span class="text-[10px] text-slate-400 font-medium">Terdaftar</span>
              </div>

              <div class="p-3 bg-slate-50/80 rounded-2xl border border-slate-100 flex flex-col items-center justify-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                  <Calendar class="w-3 h-3 text-slate-400" />
                  <span>T.A.</span>
                </span>
                <span class="text-xs font-black text-slate-700 font-mono mt-0.5 truncate max-w-[110px]">
                  {{ cls.academic_year?.year || cls.academicYear?.year || '-' }}
                </span>
                <span class="text-[10px] text-slate-400 font-medium">
                  {{ (cls.academic_year?.semester || cls.academicYear?.semester) === 'odd' ? 'Ganjil' : 'Genap' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Card Footer: Lihat Siswa & Atur Siswa -->
          <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
            <button
              v-if="activeTab === 'lokal' || isLokalClass(cls)"
              type="button"
              @click="openManageStudents(cls)"
              class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-[11px] transition-all shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95"
              title="Pilih siswa dari rombel utama untuk dimasukkan ke kelas lokal ini"
            >
              <Users class="w-3.5 h-3.5" />
              <span>Atur Siswa ({{ cls.lokal_students_count ?? 0 }})</span>
            </button>
            <span v-else class="text-[11px] text-slate-400 font-medium">
              ID Kelas: <span class="font-mono font-bold text-slate-600">#{{ cls.id }}</span>
            </span>

            <router-link
              :to="{ path: '/admin/students', query: (isLokalOnly(cls) ? { lokal_class_id: cls.id } : { class_id: cls.id }) }"
              class="font-bold text-slate-700 hover:text-emerald-700 flex items-center gap-1 group/link transition-colors cursor-pointer"
            >
              <span>Lihat Siswa</span>
              <ArrowRight class="w-3.5 h-3.5 transition-transform group-hover/link:translate-x-0.5" />
            </router-link>
          </div>
        </div>
      </div>

      <!-- 2. TABLE VIEW -->
      <div v-else class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="border-b border-slate-100 bg-slate-50/60">
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA KELAS</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">TINGKAT</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">WALI KELAS</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">JUMLAH SISWA</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">TAHUN AJARAN</th>
                <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">AKSI</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr
                v-for="(row, index) in filteredClasses"
                :key="'tbl-' + row.id"
                class="hover:bg-slate-50/70 transition-colors"
              >
                <td class="px-6 py-4 text-sm font-bold text-slate-400">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 font-black text-sm"
                      :class="getGradeTheme(row.grade_level).iconBg"
                    >
                      {{ row.name }}
                    </div>
                    <div>
                      <p class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>{{ row.name || '-' }}</span>
                        <span
                          v-if="isLokalOnly(row)"
                          class="text-[10px] font-extrabold px-1.5 py-0.2 rounded bg-teal-100 text-teal-800"
                        >
                          Lokal
                        </span>
                      </p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="px-2.5 py-1 text-[11px] font-bold rounded-lg inline-block border"
                    :class="getGradeTheme(row.grade_level).badgeBg"
                  >
                    Kelas {{ row.grade_level || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2.5" v-if="row.homeroom_teacher?.full_name || row.homeroomTeacher?.full_name">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-800 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-sm border border-slate-200">
                      <img
                        v-if="getTeacherPhoto(row)"
                        :src="getTeacherPhoto(row)"
                        alt="Photo"
                        class="w-full h-full object-cover"
                      />
                      <span v-else>
                        {{ getTeacherInitials(row) }}
                      </span>
                    </div>
                    <div>
                      <p class="text-xs font-bold text-slate-800">{{ getTeacherName(row) }}</p>
                      <p class="text-[10px] text-slate-400 font-mono">{{ getTeacherNip(row) }}</p>
                    </div>
                  </div>
                  <span v-else class="text-xs text-slate-400 italic">Belum ditentukan</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold font-mono">
                    <Users class="w-3 h-3 text-slate-400" />
                    <span>
                      {{ (activeTab === 'lokal' || isLokalOnly(row)) ? (row.lokal_students_count ?? 0) : (row.students_count ?? row.students?.length ?? 0) }} Siswa
                    </span>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-3 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-lg font-mono">
                    {{ row.academic_year?.year || row.academicYear?.year || '-' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button
                      v-if="activeTab === 'lokal' || isLokalClass(row)"
                      @click="openManageStudents(row)"
                      title="Atur Anggota Siswa Kelas Lokal"
                      class="px-2.5 py-1.5 rounded-xl flex items-center gap-1 bg-teal-50 text-teal-700 hover:bg-teal-100 hover:border-teal-300 border border-teal-200 transition-all shadow-2xs cursor-pointer text-xs font-bold"
                    >
                      <Users class="w-3.5 h-3.5" />
                      <span>Atur Siswa</span>
                    </button>
                    <button
                      @click="edit(row)"
                      title="Edit Kelas"
                      class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                    >
                      <Pencil class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="remove(row)"
                      title="Hapus Kelas"
                      class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <!-- Modal Form Tambah/Edit Kelas -->
    <ClassForm
      v-if="showForm"
      :title="editing ? 'Edit Kelas' : 'Tambah Kelas'"
      :model="editing || {}"
      @close="showForm = false"
      @save="save"
    />

    <!-- Modal Kelola Anggota Siswa Kelas Lokal -->
    <LokalClassStudentModal
      :show="showLokalStudentModal"
      :class-room="targetLokalClass || {}"
      @close="showLokalStudentModal = false"
      @saved="onLokalStudentsSaved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { api } from '../api';
import ClassForm from '../components/ClassForm.vue';
import LokalClassStudentModal from '../components/LokalClassStudentModal.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import {
  School,
  LayoutGrid,
  List,
  Plus,
  Pencil,
  Trash2,
  Users,
  Calendar,
  ArrowRight,
} from 'lucide-vue-next';

const toast = useToast();
const { confirm } = useConfirm();

const loading = ref(true);
const showForm = ref(false);
const editing = ref(null);
const classes = ref([]);
const searchQuery = ref('');
const activeTab = ref('utama'); // 'utama' | 'lokal'
const viewMode = ref('grid'); // 'grid' | 'table'
const activeYear = ref(null);
const showLokalStudentModal = ref(false);
const targetLokalClass = ref(null);

function openManageStudents(cls) {
  targetLokalClass.value = cls;
  showLokalStudentModal.value = true;
}

function onLokalStudentsSaved({ classId, count }) {
  const found = classes.value.find(c => c.id === classId);
  if (found) {
    found.lokal_students_count = count;
  }
  load();
}

// Kelas Khusus Jadwal Lokal: 7, 8, 9A, 9B
const isLokalClass = (cls) => {
  if (!cls) return false;
  const name = (cls.name || '').trim();
  const lower = name.toLowerCase();

  const isClass7 = (name === '7' || lower === 'kelas 7');
  const isClass8 = (name === '8' || lower === 'kelas 8');
  const isClass9A = (name === '9A' || name === '9a' || lower === 'kelas 9a' || lower === '9-a' || lower === 'ix-a' || lower === 'ix a');
  const isClass9B = (name === '9B' || name === '9b' || lower === 'kelas 9b' || lower === '9-b' || lower === 'ix-b' || lower === 'ix b');

  return isClass7 || isClass8 || isClass9A || isClass9B;
};

// Kelas Rombel Reguler Utama (Menyembunyikan kelas 7 dan 8 standalone yang hanya untuk jadwal lokal)
const isUtamaClass = (cls) => {
  if (!cls) return false;
  const name = (cls.name || '').trim();
  const lower = name.toLowerCase();

  if (name === '7' || lower === 'kelas 7') return false;
  if (name === '8' || lower === 'kelas 8') return false;

  return true;
};

const isLokalOnly = (cls) => {
  const name = (cls?.name || '').trim();
  const lower = name.toLowerCase();
  return name === '7' || lower === 'kelas 7' || name === '8' || lower === 'kelas 8';
};

const isBothUtamaAndLokal = (cls) => {
  const name = (cls?.name || '').trim().toLowerCase();
  return name === '9a' || name === 'kelas 9a' || name === '9b' || name === 'kelas 9b';
};

const sortClasses = (list) => {
  return [...list].sort((a, b) => (a.name || '').localeCompare(b.name || '', undefined, { numeric: true }));
};

const currentTabClasses = computed(() => {
  let list = [];
  if (activeTab.value === 'lokal') {
    list = classes.value.filter(isLokalClass);
  } else {
    list = classes.value.filter(isUtamaClass);
  }
  return sortClasses(list);
});

const filteredClasses = computed(() => {
  const base = currentTabClasses.value;
  if (!searchQuery.value) return base;
  const q = searchQuery.value.toLowerCase();
  return base.filter(c =>
    c.name?.toLowerCase().includes(q) ||
    c.homeroom_teacher?.full_name?.toLowerCase().includes(q) ||
    c.homeroomTeacher?.full_name?.toLowerCase().includes(q)
  );
});

const utamaCount = computed(() => classes.value.filter(isUtamaClass).length);
const lokalCount = computed(() => classes.value.filter(isLokalClass).length);

// Visual Theme Generator by Grade Level
const getGradeTheme = (gradeLevel) => {
  const g = String(gradeLevel || '').trim();
  if (g.includes('7')) {
    return {
      bgGrad: 'from-emerald-500/10 via-teal-500/5 to-transparent',
      badgeBg: 'bg-emerald-50 text-emerald-800 border-emerald-200',
      iconBg: 'bg-emerald-600 text-white shadow-emerald-600/20',
      accentColor: 'text-emerald-700',
    };
  }
  if (g.includes('8')) {
    return {
      bgGrad: 'from-sky-500/10 via-blue-500/5 to-transparent',
      badgeBg: 'bg-sky-50 text-sky-800 border-sky-200',
      iconBg: 'bg-sky-600 text-white shadow-sky-600/20',
      accentColor: 'text-sky-700',
    };
  }
  if (g.includes('9')) {
    return {
      bgGrad: 'from-indigo-500/10 via-purple-500/5 to-transparent',
      badgeBg: 'bg-indigo-50 text-indigo-800 border-indigo-200',
      iconBg: 'bg-indigo-600 text-white shadow-indigo-600/20',
      accentColor: 'text-indigo-700',
    };
  }
  return {
    bgGrad: 'from-slate-500/10 via-slate-500/5 to-transparent',
    badgeBg: 'bg-slate-100 text-slate-700 border-slate-200',
    iconBg: 'bg-slate-700 text-white shadow-slate-700/20',
    accentColor: 'text-slate-700',
  };
};

const getTeacherName = (cls) => {
  return cls.homeroom_teacher?.full_name || cls.homeroomTeacher?.full_name || '-';
};

const getTeacherNip = (cls) => {
  return cls.homeroom_teacher?.nip || cls.homeroomTeacher?.nip || '-';
};

const getTeacherPhoto = (cls) => {
  const photo = cls.homeroom_teacher?.photo_url || cls.homeroomTeacher?.photo_url;
  return (typeof photo === 'string' && photo.length > 5) ? photo : null;
};

const getTeacherInitials = (cls) => {
  const name = getTeacherName(cls);
  if (!name || name === '-') return '?';
  return name.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase();
};

onMounted(() => {
  loadActiveYear();
  load();
});

async function loadActiveYear() {
  try {
    const res = await api.get('settings');
    activeYear.value = res.data?.active_academic_year || null;
  } catch {}
}

async function syncClassesToActiveYear() {
  if (!activeYear.value) return;

  const isConfirmed = await confirm({
    title: 'Update Tahun Ajaran Seluruh Kelas',
    message: `Apakah Anda yakin ingin memperbarui Tahun Ajaran seluruh kelas menjadi "${activeYear.value.year} (${activeYear.value.semester === 'odd' ? 'Ganjil' : 'Genap'})"?`,
    type: 'warning',
    confirmText: 'Ya, Update Sekarang',
  });

  if (!isConfirmed) return;

  loading.value = true;
  try {
    const updatePromises = classes.value.map(c => 
      api.put(`admin/classes/${c.id}`, { academic_year_id: activeYear.value.id })
    );
    await Promise.all(updatePromises);
    toast.success('Tahun ajaran seluruh kelas berhasil diperbarui!');
    load();
  } catch {
    toast.error('Gagal memperbarui tahun ajaran kelas.');
  } finally {
    loading.value = false;
  }
}

async function load() {
  loading.value = true;
  try {
    const res = await api.get('admin/classes', { all: true, per_page: 500 });
    const data = res.data?.data || res.data || [];
    classes.value = Array.isArray(data) ? data : data.data || [];
  } catch {
    classes.value = [];
  } finally {
    loading.value = false;
  }
}

function openAddForm() {
  editing.value = null;
  showForm.value = true;
}

function edit(row) {
  editing.value = { ...row };
  showForm.value = true;
}

async function remove(row) {
  const isConfirmed = await confirm({
    title: 'Hapus Data Kelas',
    message: `Apakah Anda yakin ingin menghapus kelas "${row.name}"? Siswa di kelas ini akan kehilangan asosiasi kelas.`,
    type: 'danger',
    confirmText: 'Ya, Hapus',
  });

  if (isConfirmed) {
    try {
      await api.del('admin/classes/' + row.id);
      toast.success(`Kelas "${row.name}" berhasil dihapus`);
      load();
    } catch {
      toast.error('Gagal menghapus kelas');
    }
  }
}

function save(model) {
  const payload = { ...model };
  delete payload.id;
  const url = editing.value ? 'admin/classes/' + editing.value.id : 'admin/classes';
  const fn = editing.value ? api.put : api.post;
  fn(url, payload).then(() => {
    toast.success(editing.value ? 'Kelas berhasil diperbarui' : 'Kelas berhasil ditambahkan');
    showForm.value = false;
    editing.value = null;
    load();
  }).catch(() => {
    toast.error('Gagal menyimpan kelas');
  });
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
