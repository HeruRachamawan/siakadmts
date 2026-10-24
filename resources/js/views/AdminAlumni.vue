<template>
  <div class="space-y-6 font-sans">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
      <div>
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-800 text-xs font-semibold mb-2 border border-indigo-200/60">
          <GraduationCap class="w-3.5 h-3.5 text-indigo-600" />
          <span>Buku Induk Alumni Madrasah</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Direktori & Buku Induk Alumni</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
          Arsip abadi seluruh siswa yang telah menyelesaikan studi dan lulus dari madrasah beserta riwayat ijazah dan kontak.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <RouterLink
          to="/admin/promotions"
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors cursor-pointer shadow-xs"
        >
          <ArrowLeftRight class="w-4 h-4" />
          <span>Kenaikan & Kelulusan Kelas</span>
        </RouterLink>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Alumni</p>
        <div class="flex items-baseline justify-between mt-1">
          <p class="text-2xl font-black text-slate-900">{{ statistics.total || 0 }}</p>
          <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">Lulus</span>
        </div>
      </div>

      <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Laki-Laki</p>
        <div class="flex items-baseline justify-between mt-1">
          <p class="text-2xl font-black text-blue-700">{{ statistics.male || 0 }}</p>
          <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">Siswa (L)</span>
        </div>
      </div>

      <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Perempuan</p>
        <div class="flex items-baseline justify-between mt-1">
          <p class="text-2xl font-black text-pink-700">{{ statistics.female || 0 }}</p>
          <span class="text-xs font-bold text-pink-700 bg-pink-50 px-2 py-0.5 rounded-full border border-pink-100">Siswi (P)</span>
        </div>
      </div>

      <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Angkatan</p>
        <div class="flex items-baseline justify-between mt-1">
          <p class="text-2xl font-black text-emerald-700">{{ statistics.graduation_years?.length || 0 }}</p>
          <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Tahun</span>
        </div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
        <!-- Filter Angkatan -->
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-slate-500">Angkatan:</span>
          <select
            v-model="filterGraduationYear"
            @change="fetchAlumni(1)"
            class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 cursor-pointer focus:bg-white"
          >
            <option value="all">Semua Angkatan</option>
            <option v-for="yr in statistics.graduation_years" :key="yr" :value="yr">
              Tahun {{ yr }}
            </option>
          </select>
        </div>
      </div>

      <!-- Search Input -->
      <div class="relative w-full sm:w-72">
        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
        <input
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="Cari nama, NISN, no ijazah..."
          class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-500"
        />
      </div>
    </div>

    <!-- Table of Alumni -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin w-8 h-8 border-4 border-emerald-600 border-t-transparent rounded-full mb-3"></div>
        <p class="text-xs font-bold text-slate-600">Memuat buku induk alumni...</p>
      </div>

      <div v-else-if="alumniList.length > 0" class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-100/80 text-slate-600 font-bold border-b border-slate-200 text-[11px] uppercase tracking-wider">
              <th class="py-3 px-4 w-12 text-center">No</th>
              <th class="py-3 px-4">Nama Lengkap & NISN</th>
              <th class="py-3 px-4 text-center">L/P</th>
              <th class="py-3 px-4">Tahun & Tanggal Lulus</th>
              <th class="py-3 px-4">No. Ijazah & SKL</th>
              <th class="py-3 px-4">Kontak / Wali</th>
              <th class="py-3 px-4">Lanjutan Sekolah / Keterangan</th>
              <th class="py-3 px-4 text-center w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(al, idx) in alumniList" :key="al.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="py-3 px-4 text-center font-bold text-slate-500">
                {{ ((pagination.current_page - 1) * pagination.per_page) + idx + 1 }}
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 flex-shrink-0 flex items-center justify-center font-bold text-xs">
                    {{ al.full_name?.charAt(0) || 'A' }}
                  </div>
                  <div>
                    <p class="font-bold text-slate-900 leading-tight">{{ al.full_name }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">NISN: {{ al.nisn || '-' }} &bull; NIS: {{ al.nis || '-' }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 text-center">
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" :class="al.gender === 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'">
                  {{ al.gender }}
                </span>
              </td>
              <td class="py-3 px-4">
                <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[11px] mb-0.5">
                  {{ al.graduation_year || '-' }}
                </span>
                <p class="text-[10px] text-slate-400 font-medium">{{ al.graduation_date || 'Tanggal belum diisi' }}</p>
              </td>
              <td class="py-3 px-4 font-mono">
                <p class="text-xs font-semibold text-slate-800">
                  <span class="text-slate-400 text-[10px]">Ijazah:</span> {{ al.certificate_number || '-' }}
                </p>
                <p class="text-[10px] text-slate-500">
                  <span class="text-slate-400">SKL:</span> {{ al.skl_number || '-' }}
                </p>
              </td>
              <td class="py-3 px-4">
                <p class="text-xs font-semibold text-slate-800">{{ al.parent_phone || '-' }}</p>
                <p class="text-[10px] text-slate-400 truncate max-w-[140px]" :title="al.address">{{ al.address || '-' }}</p>
              </td>
              <td class="py-3 px-4">
                <p class="text-xs text-slate-700 max-w-[180px] truncate" :title="al.alumni_notes">
                  {{ al.alumni_notes || '-' }}
                </p>
              </td>
              <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    @click="openEditModal(al)"
                    class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer"
                    title="Edit Data Alumni"
                  >
                    <Edit3 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="openRevertModal(al)"
                    class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors cursor-pointer"
                    title="Batalkan Status Lulus (Kembalikan ke Siswa Aktif)"
                  >
                    <Undo2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center">
        <GraduationCap class="w-10 h-10 text-slate-300 mx-auto mb-2" />
        <h3 class="text-sm font-bold text-slate-800">Belum Ada Data Alumni</h3>
        <p class="text-xs text-slate-500 mt-1">Siswa kelas 9 yang diluluskan akan otomatis tersimpan di sini.</p>
      </div>

      <!-- Pagination Footer -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t border-slate-200 flex items-center justify-between">
        <span class="text-xs text-slate-500">
          Menampilkan {{ alumniList.length }} dari {{ pagination.total }} alumni
        </span>
        <div class="flex items-center gap-1">
          <button
            @click="fetchAlumni(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
          >
            Sebelumnya
          </button>
          <span class="text-xs font-bold px-2 text-slate-700">
            Hal {{ pagination.current_page }} / {{ pagination.last_page }}
          </span>
          <button
            @click="fetchAlumni(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-2.5 py-1 text-xs font-bold rounded-lg border border-slate-200 hover:bg-slate-50 disabled:opacity-40 cursor-pointer"
          >
            Berikutnya
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <Transition name="modal-fade">
      <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showEditModal = false"></div>
        <div class="relative bg-white rounded-2xl p-6 shadow-2xl w-full max-w-lg space-y-4 border border-slate-200 animate-slide-up z-10">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
              <h3 class="text-base font-bold text-slate-900">Perbarui Data Alumni</h3>
              <p class="text-xs text-slate-500">{{ editForm.full_name }}</p>
            </div>
            <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-700 cursor-pointer">
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="submitEdit" class="space-y-3.5">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Lulus</label>
                <input
                  v-model="editForm.graduation_year"
                  type="text"
                  placeholder="2025/2026"
                  class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Kelulusan</label>
                <input
                  v-model="editForm.graduation_date"
                  type="date"
                  class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white"
                />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Ijazah</label>
                <input
                  v-model="editForm.certificate_number"
                  type="text"
                  placeholder="DN-01/M-MTs/..."
                  class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor SKL</label>
                <input
                  v-model="editForm.skl_number"
                  type="text"
                  placeholder="SKL/MTs/2026/..."
                  class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">No. Telp / WhatsApp Alumni/Wali</label>
              <input
                v-model="editForm.parent_phone"
                type="text"
                placeholder="081234567890"
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Sekolah Lanjutan / Catatan Karier</label>
              <textarea
                v-model="editForm.alumni_notes"
                placeholder="Contoh: Melanjutkan ke MAN 1 Kota, MA Al-Hikmah, SMKN 2..."
                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold h-20 resize-none focus:bg-white"
              ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
              <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                Batal
              </button>
              <button type="submit" :disabled="submittingEdit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs cursor-pointer flex items-center gap-1.5">
                <span v-if="submittingEdit">Menyimpan...</span>
                <span v-else>Simpan Perubahan</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Revert Modal -->
    <Transition name="modal-fade">
      <div v-if="showRevertModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showRevertModal = false"></div>
        <div class="relative bg-white rounded-2xl p-6 shadow-2xl w-full max-w-md space-y-4 border border-slate-200 animate-slide-up z-10">
          <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
              <Undo2 class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-900">Batalkan Status Lulus</h3>
              <p class="text-xs text-slate-500">{{ targetRevertStudent?.full_name }}</p>
            </div>
          </div>

          <p class="text-xs text-slate-600 leading-relaxed">
            Siswa ini akan dikembalikan menjadi <strong>Siswa Aktif</strong>. Silakan pilih kelas tujuan penempatan siswa tersebut:
          </p>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Kelas Aktif <span class="text-rose-500">*</span></label>
            <select
              v-model="revertTargetClassId"
              class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 cursor-pointer"
            >
              <option value="" disabled>-- Pilih Rombel --</option>
              <option v-for="c in activeClasses" :key="c.id" :value="c.id">
                Kelas {{ c.name }} (Tingkat {{ c.grade_level }})
              </option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showRevertModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
              Batal
            </button>
            <button
              type="button"
              @click="submitRevert"
              :disabled="!revertTargetClassId || submittingRevert"
              class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs cursor-pointer disabled:opacity-50"
            >
              <span v-if="submittingRevert">Memproses...</span>
              <span v-else>Ya, Kembalikan ke Aktif</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  GraduationCap,
  ArrowLeftRight,
  Search,
  Edit3,
  Undo2,
  X
} from 'lucide-vue-next';

const toast = useToast();

const loading = ref(false);
const alumniList = ref([]);
const statistics = ref({ total: 0, male: 0, female: 0, graduation_years: [] });
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

const filterGraduationYear = ref('all');
const searchQuery = ref('');
let searchTimer = null;

const showEditModal = ref(false);
const submittingEdit = ref(false);
const editForm = ref({});

const showRevertModal = ref(false);
const submittingRevert = ref(false);
const targetRevertStudent = ref(null);
const revertTargetClassId = ref('');
const activeClasses = ref([]);

onMounted(async () => {
  await fetchAlumni(1);
  await fetchActiveClasses();
});

async function fetchAlumni(page = 1) {
  loading.value = true;
  try {
    let url = `admin/alumni?page=${page}&per_page=15`;
    if (filterGraduationYear.value && filterGraduationYear.value !== 'all') {
      url += `&graduation_year=${encodeURIComponent(filterGraduationYear.value)}`;
    }
    if (searchQuery.value) {
      url += `&search=${encodeURIComponent(searchQuery.value)}`;
    }

    const res = await api.get(url);
    const data = res.data;
    alumniList.value = data.data || [];
    pagination.value = data.pagination || {};
    statistics.value = data.statistics || {};
  } catch (err) {
    toast.error('Gagal memuat daftar alumni');
  } finally {
    loading.value = false;
  }
}

async function fetchActiveClasses() {
  try {
    const res = await api.get('admin/classes?all=true');
    activeClasses.value = res.data || [];
  } catch (err) {
    console.error('Failed to load classes', err);
  }
}

function handleSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    fetchAlumni(1);
  }, 400);
}

function openEditModal(alumni) {
  editForm.value = { ...alumni };
  showEditModal.value = true;
}

async function submitEdit() {
  submittingEdit.value = true;
  try {
    const payload = {
      graduation_year: editForm.value.graduation_year,
      graduation_date: editForm.value.graduation_date,
      certificate_number: editForm.value.certificate_number,
      skl_number: editForm.value.skl_number,
      parent_phone: editForm.value.parent_phone,
      address: editForm.value.address,
      alumni_notes: editForm.value.alumni_notes,
    };

    await api.put(`admin/alumni/${editForm.value.id}`, payload);
    toast.success('Data alumni berhasil diperbarui');
    showEditModal.value = false;
    await fetchAlumni(pagination.value.current_page);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan perubahan');
  } finally {
    submittingEdit.value = false;
  }
}

function openRevertModal(alumni) {
  targetRevertStudent.value = alumni;
  revertTargetClassId.value = '';
  showRevertModal.value = true;
}

async function submitRevert() {
  if (!revertTargetClassId.value) return;
  submittingRevert.value = true;
  try {
    const res = await api.post(`admin/alumni/${targetRevertStudent.value.id}/revert`, {
      class_id: revertTargetClassId.value,
    });
    toast.success(res.message || 'Status alumni berhasil dibatalkan dan dikembalikan ke siswa aktif.');
    showRevertModal.value = false;
    await fetchAlumni(pagination.value.current_page);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengembalikan siswa');
  } finally {
    submittingRevert.value = false;
  }
}
</script>
