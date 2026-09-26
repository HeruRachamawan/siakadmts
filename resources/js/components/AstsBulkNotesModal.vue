<template>
  <div v-if="show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-4 md:p-6 no-print">
    <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-2xl w-full max-w-5xl max-h-[92vh] flex flex-col overflow-hidden border border-slate-100 transform transition-all animate-in fade-in zoom-in-95 duration-200">
      
      <!-- Header Modal -->
      <div class="px-5 py-4 sm:px-6 sm:py-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-purple-500/10 via-indigo-50 to-transparent flex-shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-md shadow-purple-600/20">
            <ClipboardList class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend uppercase tracking-wider">
                Catatan Wali Kelas & Presensi 1 Kelas
              </h2>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-100 text-purple-800">
                {{ localStudents.length }} Siswa
              </span>
            </div>
            <p class="text-[11px] sm:text-xs text-slate-500 font-medium mt-0.5">
              Kelas {{ className }} • Semester {{ semester === 'ganjil' ? 'Ganjil' : 'Genap' }} • TA {{ academicYearName || '-' }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="$emit('close')"
          class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 cursor-pointer transition-colors"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Toolbar: Quick Bulk Template Motivasi & Search -->
      <div class="p-3 sm:p-4 bg-slate-50/80 border-b border-slate-100 space-y-3 flex-shrink-0">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          
          <!-- Quick Template Selector & Bulk Apply -->
          <div class="space-y-1.5 flex-1">
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-black uppercase tracking-wider text-purple-900 bg-purple-100/70 px-2 py-0.5 rounded">
                ⚡ Opsi Template Cepat
              </span>
              <span class="text-[11px] text-slate-500 font-medium">Pilih kalimat motivasi di bawah:</span>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
              <button
                type="button"
                v-for="(tip, tIdx) in templates"
                :key="'bulk-tip-' + tIdx"
                @click="selectedTemplate = tip"
                :class="selectedTemplate === tip ? 'bg-purple-600 text-white border-purple-600 shadow-xs' : 'bg-white text-slate-700 hover:bg-purple-50 border-slate-200'"
                class="px-2.5 py-1.5 rounded-xl border text-[11px] font-medium transition-all text-left cursor-pointer flex items-start gap-1.5"
              >
                <span class="text-xs mt-0.5" :class="selectedTemplate === tip ? 'text-amber-200' : 'text-purple-600'">💬</span>
                <span class="leading-tight truncate">{{ tip }}</span>
              </button>
            </div>
          </div>

          <!-- Action Buttons for Template Fill & Search -->
          <div class="flex flex-col sm:flex-row lg:flex-col justify-end gap-2 lg:min-w-[240px]">
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="applyBulkTemplate(true)"
                class="flex-1 px-3 py-1.5 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl text-[11px] transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-95"
                title="Terapkan kalimat motivasi terpilih ke seluruh siswa di kelas ini"
              >
                <Check class="w-3.5 h-3.5 text-purple-200" />
                <span>Terapkan ke Semua</span>
              </button>

              <button
                type="button"
                @click="applyBulkTemplate(false)"
                class="px-2.5 py-1.5 bg-purple-100 hover:bg-purple-200 text-purple-900 font-bold rounded-xl text-[11px] transition-all flex items-center justify-center gap-1 cursor-pointer"
                title="Hanya isi siswa yang catatannya masih kosong"
              >
                <span>Hanya yg Kosong</span>
              </button>
            </div>

            <!-- Search filter inside modal -->
            <div class="relative">
              <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari siswa dalam kelas..."
                class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Body: Scrollable Table of Students -->
      <div class="overflow-y-auto flex-1 p-3 sm:p-5">
        <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-2xs">
          <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500 border-b border-slate-200 sticky top-0 z-10">
              <tr>
                <th class="px-3 py-3 w-10 text-center">No</th>
                <th class="px-3 py-3 min-w-[160px] sm:min-w-[180px]">Nama Siswa</th>
                <th class="px-2 py-3 w-16 text-center" title="Sakit (S)">Sakit (S)</th>
                <th class="px-2 py-3 w-16 text-center" title="Izin (I)">Izin (I)</th>
                <th class="px-2 py-3 w-16 text-center" title="Alpa (A)">Alpa (A)</th>
                <th class="px-3 py-3 min-w-[260px]">Catatan Perkembangan & Motivasi</th>
                <th class="px-2 py-3 w-28 text-center">Pilihan Cepat</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="(st, idx) in filteredStudents"
                :key="'bulk-row-' + st.student_id"
                class="hover:bg-purple-50/30 transition-colors"
              >
                <td class="px-3 py-2.5 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-3 py-2.5">
                  <div class="font-bold text-slate-900 leading-tight">{{ st.full_name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono mt-0.5">NISN: {{ st.nisn || '-' }}</div>
                </td>
                
                <!-- Kehadiran: Sakit -->
                <td class="px-2 py-2 text-center">
                  <input
                    v-model.number="st.sick_count"
                    type="number"
                    min="0"
                    class="w-14 text-center text-xs font-black text-slate-800 bg-slate-50 focus:bg-white border border-slate-300 rounded-lg py-1 focus:ring-2 focus:ring-purple-400 font-mono"
                  />
                </td>

                <!-- Kehadiran: Izin -->
                <td class="px-2 py-2 text-center">
                  <input
                    v-model.number="st.permission_count"
                    type="number"
                    min="0"
                    class="w-14 text-center text-xs font-black text-slate-800 bg-slate-50 focus:bg-white border border-slate-300 rounded-lg py-1 focus:ring-2 focus:ring-purple-400 font-mono"
                  />
                </td>

                <!-- Kehadiran: Alpa -->
                <td class="px-2 py-2 text-center">
                  <input
                    v-model.number="st.unexcused_count"
                    type="number"
                    min="0"
                    class="w-14 text-center text-xs font-black text-slate-800 bg-slate-50 focus:bg-white border border-slate-300 rounded-lg py-1 focus:ring-2 focus:ring-purple-400 font-mono"
                  />
                </td>

                <!-- Catatan Motivasi -->
                <td class="px-3 py-2">
                  <textarea
                    v-model="st.homeroom_notes"
                    rows="2"
                    placeholder="Ketik atau pilih kalimat motivasi..."
                    class="w-full bg-slate-50 focus:bg-white border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 focus:ring-2 focus:ring-purple-400 focus:outline-none transition-all"
                  ></textarea>
                </td>

                <!-- Tombol Pilihan Template Cepat Per Baris -->
                <td class="px-2 py-2 text-center align-middle">
                  <div class="flex flex-col gap-1">
                    <button
                      type="button"
                      @click="st.homeroom_notes = selectedTemplate"
                      class="px-2 py-1 bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 font-bold rounded-lg text-[10px] transition-colors cursor-pointer text-center"
                      title="Terapkan template yang sedang terpilih"
                    >
                      Pakai Terpilih
                    </button>
                    <button
                      type="button"
                      @click="st.homeroom_notes = ''"
                      v-if="st.homeroom_notes"
                      class="px-2 py-0.5 text-slate-400 hover:text-rose-600 text-[9px] font-medium transition-colors cursor-pointer"
                    >
                      Kosongkan
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredStudents.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-slate-400 font-medium">
                  Tidak ditemukan siswa yang cocok dengan pencarian.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Footer Modal -->
      <div class="px-5 py-3.5 sm:px-6 sm:py-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3 bg-slate-50/70 flex-shrink-0">
        <div class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-purple-500"></span>
          <span>Total <strong>{{ localStudents.length }}</strong> siswa akan diperbarui secara bersamaan.</span>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-xl text-xs border border-slate-200 transition-colors cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="handleSave"
            :disabled="saving || !localStudents.length"
            class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95"
          >
            <Check v-if="!saving" class="w-4 h-4" />
            <span>{{ saving ? 'Menyimpan Catatan...' : 'Simpan Catatan Semua Siswa' }}</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { ClipboardList, X, Search, Check } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  className: { type: String, default: '' },
  semester: { type: String, default: 'ganjil' },
  academicYearName: { type: String, default: '' },
  students: { type: Array, default: () => [] },
  templates: {
    type: Array,
    default: () => [
      'Pertahankan prestasimu dan terus tingkatkan ketekunan belajar di madrasah.',
      'Tingkatkan terus ketekunan belajar, kedisiplinan beribadah, dan keaktifan di kelas.',
      'Perbanyak latihan soal mandiri dan lebih aktif bertanya saat pembelajaran.',
      'Semangat belajar terus ditingkatkan, kurangi waktu bermain, dan jaga kesehatan.',
    ],
  },
  initialSearch: { type: String, default: '' },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'save', 'toast-success']);

const localStudents = ref([]);
const searchQuery = ref('');
const selectedTemplate = ref(props.templates[0]);

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      localStudents.value = props.students.map(st => ({
        student_id: st.student_id,
        full_name: st.full_name,
        nisn: st.nisn,
        gender: st.gender,
        sick_count: st.sick_count !== undefined ? Number(st.sick_count) : (st.attendance?.sick || 0),
        permission_count: st.permission_count !== undefined ? Number(st.permission_count) : (st.attendance?.permission || 0),
        unexcused_count: st.unexcused_count !== undefined ? Number(st.unexcused_count) : (st.attendance?.unexcused || 0),
        homeroom_notes: st.homeroom_notes || '',
      }));
      searchQuery.value = props.initialSearch || '';
      selectedTemplate.value = props.templates[0];
    }
  },
  { immediate: true }
);

const filteredStudents = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return localStudents.value;
  return localStudents.value.filter(st =>
    st.full_name?.toLowerCase().includes(q) ||
    st.nisn?.toLowerCase().includes(q)
  );
});

function applyBulkTemplate(overrideExisting = true) {
  if (!selectedTemplate.value) return;
  let count = 0;
  localStudents.value.forEach(st => {
    if (overrideExisting || !st.homeroom_notes || !st.homeroom_notes.trim()) {
      st.homeroom_notes = selectedTemplate.value;
      count++;
    }
  });
  emit('toast-success', overrideExisting 
    ? 'Kalimat motivasi berhasil diterapkan ke seluruh siswa!' 
    : `Kalimat motivasi diterapkan ke ${count} siswa yang catatannya masih kosong.`
  );
}

function handleSave() {
  emit('save', localStudents.value.map(st => ({
    student_id: st.student_id,
    sick_count: Number(st.sick_count) || 0,
    permission_count: Number(st.permission_count) || 0,
    unexcused_count: Number(st.unexcused_count) || 0,
    homeroom_notes: st.homeroom_notes || '',
  })));
}
</script>
