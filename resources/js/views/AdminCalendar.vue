<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-black font-lexend text-slate-800 tracking-tight">Kalender Akademik</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola agenda tahun ajaran sekolah berdasarkan bulan.</p>
      </div>
      <div class="text-[10px] font-black text-indigo-700 bg-indigo-50 border border-indigo-100 px-4 py-2 rounded-xl tracking-widest uppercase">
        Tahun Ajaran Aktif
      </div>
    </div>

    <!-- Month Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="month in months" :key="month.id" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col h-full group hover:shadow-md hover:border-indigo-100 transition-all">
        <!-- Month Header -->
        <div class="flex items-center justify-between border-b border-slate-50 pb-3 mb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center font-bold text-sm shadow-inner group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
              {{ month.short }}
            </div>
            <h2 class="font-bold text-slate-800 font-lexend">{{ month.name }}</h2>
          </div>
          <button @click="openAddModal(month.num)" class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          </button>
        </div>

        <!-- Events List -->
        <div class="flex-1 overflow-y-auto space-y-2 min-h-[100px]">
          <template v-if="eventsByMonth[month.num] && eventsByMonth[month.num].length > 0">
            <div v-for="event in eventsByMonth[month.num]" :key="event.id" class="p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 transition-colors cursor-pointer group/item relative overflow-hidden" @click="openEditModal(event)">
              <div :style="{ backgroundColor: colorHexMap[event.color] || '#64748b' }" class="absolute left-0 top-0 bottom-0 w-1"></div>
              <div class="pl-2">
                <p class="text-xs font-bold text-slate-800 line-clamp-1">{{ event.title }}</p>
                <div class="flex items-center justify-between mt-1">
                  <span class="text-[10px] text-slate-500 font-medium">{{ formatDateRange(event.start_date, event.end_date) }}</span>
                  <span :style="{ backgroundColor: (colorHexMap[event.color] || '#64748b') + '25', color: colorHexMap[event.color] || '#64748b' }" class="text-[9px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider">{{ event.type }}</span>
                </div>
              </div>
            </div>
          </template>
          <div v-else class="flex flex-col items-center justify-center h-full text-center opacity-50 py-4">
            <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kosong</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <Transition name="modal-fade">
      <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-black text-slate-800 font-lexend tracking-tight">{{ isEditing ? 'Edit Agenda' : 'Tambah Agenda Baru' }}</h3>
            <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors p-1 rounded-lg hover:bg-rose-50">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          
          <form @submit.prevent="submitForm" class="p-6 space-y-5">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                <input type="date" v-model="form.start_date" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-xs font-medium text-slate-700">
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tanggal Selesai</label>
                <input type="date" v-model="form.end_date" required :min="form.start_date" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-xs font-medium text-slate-700">
              </div>
            </div>
            
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Acara</label>
              <input type="text" v-model="form.title" required placeholder="Contoh: Ujian Tengah Semester" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kategori / Jenis Acara</label>
              <input type="text" v-model="form.type" required placeholder="Contoh: Rapat Guru, Porseni, Libur Semester" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Warna Latar</label>
              <div class="flex flex-wrap gap-3">
                <button v-for="color in colorPalette" :key="color.value" type="button" @click="form.color = color.value"
                        class="w-8 h-8 rounded-full border-2 transition-transform hover:scale-110 flex items-center justify-center shadow-sm"
                        :class="[form.color === color.value ? 'border-slate-800 scale-110 ring-2 ring-indigo-500/30' : 'border-transparent']"
                        :style="{ backgroundColor: colorHexMap[color.value] || '#64748b' }">
                  <svg v-if="form.color === color.value" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </button>
              </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
              <button v-if="isEditing" type="button" @click="deleteEvent" :disabled="isSaving" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 text-sm font-bold rounded-xl transition-colors">
                Hapus
              </button>
              <div class="flex-1"></div>
              <button type="button" @click="closeModal" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold rounded-xl transition-colors">
                Batal
              </button>
              <button type="submit" :disabled="isSaving" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shadow-lg shadow-indigo-200 flex items-center gap-2">
                <svg v-if="isSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Data' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();
const events = ref([]);

// Months (Academic Year: July to June)
const months = [
  { id: 7, num: '07', name: 'Juli', short: 'Jul' },
  { id: 8, num: '08', name: 'Agustus', short: 'Agu' },
  { id: 9, num: '09', name: 'September', short: 'Sep' },
  { id: 10, num: '10', name: 'Oktober', short: 'Okt' },
  { id: 11, num: '11', name: 'November', short: 'Nov' },
  { id: 12, num: '12', name: 'Desember', short: 'Des' },
  { id: 1, num: '01', name: 'Januari', short: 'Jan' },
  { id: 2, num: '02', name: 'Februari', short: 'Feb' },
  { id: 3, num: '03', name: 'Maret', short: 'Mar' },
  { id: 4, num: '04', name: 'April', short: 'Apr' },
  { id: 5, num: '05', name: 'Mei', short: 'Mei' },
  { id: 6, num: '06', name: 'Juni', short: 'Jun' },
];

const colorHexMap = {
  'emerald-500': '#10b981',
  'rose-500': '#f43f5e',
  'violet-500': '#8b5cf6',
  'blue-500': '#3b82f6',
  'amber-500': '#f59e0b',
  'teal-500': '#14b8a6',
  'pink-500': '#ec4899',
  'cyan-500': '#06b6d4',
  'slate-500': '#64748b',
};

const colorPalette = [
  { value: 'emerald-500' },
  { value: 'rose-500' },
  { value: 'violet-500' },
  { value: 'blue-500' },
  { value: 'amber-500' },
  { value: 'teal-500' },
  { value: 'pink-500' },
  { value: 'cyan-500' },
  { value: 'slate-500' },
];

const formatDateRange = (startStr, endStr) => {
  if (!startStr) return '';
  if (!endStr || startStr === endStr) {
    const d = new Date(startStr);
    return d.getDate() + ' ' + (months.find(m => m.id === (d.getMonth() + 1))?.short || '');
  }
  const d1 = new Date(startStr);
  const d2 = new Date(endStr);
  const m1 = months.find(m => m.id === (d1.getMonth() + 1))?.short || '';
  const m2 = months.find(m => m.id === (d2.getMonth() + 1))?.short || '';
  if (m1 === m2) {
    return `${d1.getDate()} - ${d2.getDate()} ${m1}`;
  }
  return `${d1.getDate()} ${m1} - ${d2.getDate()} ${m2}`;
};

// Fetch Events
const fetchEvents = async () => {
  try {
    const res = await api.get('/admin/calendar-events?per_page=1000');
    events.value = res.data.data || res.data;
  } catch (e) {
    toast.error('Gagal memuat data kalender');
  }
};

onMounted(fetchEvents);

// Group Events By Month
const eventsByMonth = computed(() => {
  const grouped = {};
  months.forEach(m => grouped[m.num] = []);

  events.value.forEach(event => {
    const startMonth = event.start_date ? event.start_date.split('-')[1] : null;
    const endMonth = event.end_date ? event.end_date.split('-')[1] : null;
    
    months.forEach(m => {
      if (startMonth === m.num || endMonth === m.num) {
        if (!grouped[m.num].some(e => e.id === event.id)) {
          grouped[m.num].push(event);
        }
      }
    });
  });

  Object.keys(grouped).forEach(m => {
    grouped[m].sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
  });

  return grouped;
});

// Modal Logic
const isModalOpen = ref(false);
const isEditing = ref(false);
const isSaving = ref(false);
const form = ref({ id: null, start_date: '', end_date: '', title: '', type: '', color: 'emerald-500' });

const openAddModal = (monthNum) => {
  isEditing.value = false;
  const year = new Date().getFullYear();
  const defaultDate = `${year}-${monthNum}-01`;
  form.value = { id: null, start_date: defaultDate, end_date: defaultDate, title: '', type: 'Kegiatan Internal', color: 'emerald-500' };
  isModalOpen.value = true;
};

const openEditModal = (event) => {
  isEditing.value = true;
  form.value = { ...event };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const submitForm = async () => {
  isSaving.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/admin/calendar-events/${form.value.id}`, form.value);
      toast.success('Agenda berhasil diperbarui');
    } else {
      await api.post('/admin/calendar-events', form.value);
      toast.success('Agenda baru berhasil ditambahkan');
    }
    closeModal();
    fetchEvents();
  } catch (e) {
    toast.error('Gagal menyimpan agenda');
  } finally {
    isSaving.value = false;
  }
};

const deleteEvent = async () => {
  const isConfirmed = await confirm({
    title: 'Hapus Agenda',
    message: 'Apakah Anda yakin ingin menghapus agenda ini? Data tidak dapat dikembalikan.',
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal',
    type: 'danger'
  });
  
  if (!isConfirmed) return;

  isSaving.value = true;
  try {
    await api.del(`/admin/calendar-events/${form.value.id}`);
    toast.success('Agenda berhasil dihapus');
    closeModal();
    fetchEvents();
  } catch (e) {
    toast.error('Gagal menghapus agenda');
  } finally {
    isSaving.value = false;
  }
};
</script>

<style scoped>
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
</style>
