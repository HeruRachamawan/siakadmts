<template>
  <div v-if="show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-6 no-print">
    <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden border border-slate-100 transform transition-all animate-in fade-in zoom-in-95 duration-200">
      
      <!-- Modal Header -->
      <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-amber-500/10 via-amber-50 to-transparent">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/20 flex-shrink-0">
            <Trophy class="w-5 h-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend uppercase tracking-wider">Atur Peringkat Siswa</h2>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                {{ localRankList.length }} Siswa
              </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
              Kelas {{ className }} • Semester {{ semester === 'genap' ? 'Genap' : 'Ganjil' }}
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

      <!-- Modal Body: Rank List -->
      <div class="p-4 sm:p-6 overflow-y-auto space-y-4 flex-1">
        <!-- Petunjuk Ramah Guru -->
        <div class="p-3.5 bg-gradient-to-r from-amber-50 to-orange-50/50 rounded-2xl border border-amber-200/80 text-xs text-amber-950 space-y-2">
          <div class="flex items-start gap-2.5">
            <div class="w-5 h-5 rounded-full bg-amber-200/70 text-amber-800 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold text-xs">
              💡
            </div>
            <div class="leading-relaxed space-y-1">
              <p class="font-bold text-slate-900">Panduan Mudah Mengatur Posisi Juara:</p>
              <p class="text-slate-600 text-[11px]">
                Gunakan tombol panah <strong>⬆ (Naik)</strong> atau <strong>⬇ (Turun)</strong> untuk menukar posisi ranking siswa dengan cepat dan rapi. Sistem akan otomatis memastikan <strong>tidak ada nomor peringkat yang kembar</strong>.
              </p>
            </div>
          </div>
        </div>

        <!-- Alert Peringatan Jika Ada Peringkat Kembar -->
        <div v-if="hasDuplicateRank" class="p-3.5 bg-rose-50 rounded-2xl border border-rose-200 text-xs text-rose-800 flex items-start gap-3 shadow-xs">
          <AlertTriangle class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" />
          <div class="flex-1">
            <span class="font-black block">Perhatian: Ditemukan Nomor Peringkat Kembar!</span>
            <p class="text-[11px] text-rose-700 mt-0.5 leading-relaxed">
              Nomor peringkat <strong>#{{ duplicateRanks.join(', #') }}</strong> digunakan oleh lebih dari satu siswa. Setiap siswa wajib memiliki peringkat unik.
            </p>
            <button
              type="button"
              @click="autoSequenceRanks"
              class="mt-2 px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-[10px] transition-colors inline-flex items-center gap-1 cursor-pointer"
            >
              <Sparkles class="w-3 h-3" />
              <span>Rapikan Otomatis (1 s/d {{ localRankList.length }})</span>
            </button>
          </div>
        </div>

        <!-- Tabel Peringkat Siswa Interaktif -->
        <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-2xs">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-[10px] font-black uppercase text-slate-400 border-b border-slate-200">
              <tr>
                <th class="p-3 w-16 text-center">Urutan</th>
                <th class="p-3 w-20 text-center">Tukar Posisi</th>
                <th class="p-3">Nama Lengkap Siswa</th>
                <th class="p-3 w-24 text-center bg-blue-50/50 text-blue-900 border-x border-slate-200/60">Total Nilai</th>
                <th class="p-3 w-24 text-center bg-emerald-50/50 text-emerald-900 border-r border-slate-200/60">Rata-Rata</th>
                <th class="p-3 w-28 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr
                v-for="(item, idx) in localRankList"
                :key="'rank-item-' + item.student_id"
                class="transition-colors hover:bg-slate-50/80"
                :class="{
                  'bg-amber-50/60': item.rank === 1 && item.rank === item.calculated_rank,
                  'bg-slate-50/60': item.rank === 2 && item.rank === item.calculated_rank,
                  'bg-orange-50/40': item.rank === 3 && item.rank === item.calculated_rank,
                  'bg-emerald-50/40': typeof item.calculated_rank === 'number' && item.rank < item.calculated_rank,
                  'bg-rose-50/40': typeof item.calculated_rank === 'number' && item.rank > item.calculated_rank,
                  'ring-2 ring-rose-400 bg-rose-50/70': duplicateRanks.includes(parseInt(item.rank))
                }"
              >
                <!-- Badge & Input Nomor Peringkat -->
                <td class="p-2.5 text-center">
                  <div class="flex items-center justify-center gap-1">
                    <span v-if="item.rank === 1" class="text-sm" title="Juara 1">🥇</span>
                    <span v-else-if="item.rank === 2" class="text-sm" title="Juara 2">🥈</span>
                    <span v-else-if="item.rank === 3" class="text-sm" title="Juara 3">🥉</span>
                    
                    <input
                      v-model.number="item.rank"
                      @change="sortListByRankInputs"
                      type="number"
                      min="1"
                      :max="localRankList.length"
                      class="w-12 text-center py-1 px-1 bg-white border rounded-lg font-black font-mono text-xs shadow-2xs focus:ring-2"
                      :class="duplicateRanks.includes(parseInt(item.rank)) 
                        ? 'border-rose-400 text-rose-700 focus:ring-rose-400 bg-rose-50/50' 
                        : 'border-slate-300 text-slate-800 focus:ring-amber-400'"
                    />
                  </div>
                </td>

                <!-- Tombol Naikkan / Turunkan Posisi -->
                <td class="p-2 text-center">
                  <div class="inline-flex items-center gap-1 bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                    <button
                      type="button"
                      @click="moveStudentRank(idx, -1)"
                      :disabled="idx === 0"
                      title="Naikkan 1 posisi ke atas"
                      class="w-6 h-6 flex items-center justify-center rounded bg-white hover:bg-amber-50 text-slate-600 hover:text-amber-700 disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-slate-600 cursor-pointer transition-all shadow-2xs"
                    >
                      <ArrowUp class="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      @click="moveStudentRank(idx, 1)"
                      :disabled="idx === localRankList.length - 1"
                      title="Turunkan 1 posisi ke bawah"
                      class="w-6 h-6 flex items-center justify-center rounded bg-white hover:bg-amber-50 text-slate-600 hover:text-amber-700 disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-slate-600 cursor-pointer transition-all shadow-2xs"
                    >
                      <ArrowDown class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>

                <!-- Nama & Identitas Siswa -->
                <td class="p-3">
                  <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-800 font-lexend">{{ item.full_name }}</span>
                  </div>
                  <div class="text-[10px] text-slate-400 font-mono">NISN: {{ item.nisn || '-' }}</div>
                </td>

                <!-- Total Nilai Keseluruhan -->
                <td class="p-3 text-center font-black font-mono text-slate-800 bg-blue-50/20 border-x border-slate-100">
                  {{ item.total_score !== undefined ? Math.round(Number(item.total_score)) : '-' }}
                </td>

                <!-- Rata-rata Nilai -->
                <td class="p-3 text-center font-bold font-mono text-emerald-700 bg-emerald-50/20 border-r border-slate-100">
                  {{ Number(item.average_score || 0).toFixed(2) }}
                </td>

                <!-- Status Peringkat -->
                <td class="p-3 text-center">
                  <span
                    v-if="typeof item.calculated_rank === 'number' && item.rank < item.calculated_rank"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs whitespace-nowrap"
                    title="Siswa dinaikkan posisinya lebih tinggi dari ranking aslinya"
                  >
                    <span>▲ Naik {{ item.calculated_rank - item.rank }}</span>
                    <span class="text-emerald-600 font-semibold">(Murni: #{{ item.calculated_rank }})</span>
                  </span>

                  <span
                    v-else-if="typeof item.calculated_rank === 'number' && item.rank > item.calculated_rank"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300 shadow-2xs whitespace-nowrap"
                    title="Siswa diturunkan posisinya lebih rendah dari ranking aslinya"
                  >
                    <span>▼ Turun {{ item.rank - item.calculated_rank }}</span>
                    <span class="text-rose-600 font-semibold">(Murni: #{{ item.calculated_rank }})</span>
                  </span>

                  <span v-else class="text-slate-400 text-[10.5px] font-medium whitespace-nowrap">
                    Sesuai Nilai {{ item.calculated_rank !== '-' ? '(#' + item.calculated_rank + ')' : '' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Peringatan Cerdas Jika Urutan Peringkat Berlawanan dengan Rata-Rata -->
        <div
          v-if="hasInvertedRank && !adjustScores"
          class="p-3.5 bg-amber-50 rounded-2xl border border-amber-300 text-xs text-amber-900 flex items-start gap-3 shadow-xs"
        >
          <AlertTriangle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
          <div class="space-y-1">
            <span class="font-black block">💡 Rekomendasi: Urutan Ranking Belum Sejalan dengan Rata-Rata</span>
            <p class="text-[11px] text-amber-800 leading-relaxed">
              Anda menempatkan siswa pada ranking lebih tinggi padahal nilai rata-ratanya lebih rendah. Agar tidak tampak janggal di cetakan rapor resmi siswa, disarankan <strong>mencentang opsi "Selaraskan Nilai Siswa"</strong> di bawah agar nilai mapel disesuaikan secara proporsional.
            </p>
          </div>
        </div>

        <!-- Opsi Tambahan: Selaraskan Nilai Siswa -->
        <div class="p-4 bg-slate-50/90 rounded-2xl border border-slate-200 flex items-start gap-3">
          <input
            id="adjust-scores-checkbox"
            v-model="adjustScores"
            type="checkbox"
            class="mt-1 w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer flex-shrink-0"
          />
          <label for="adjust-scores-checkbox" class="text-xs cursor-pointer select-none">
            <span class="font-black text-slate-900 block">⚡ Opsional: Selaraskan nilai rata-rata secara wajar & bertahap (kisaran 78 - 81)</span>
            <span class="text-slate-500 text-[11px] block mt-0.5 leading-relaxed">
              Jika dicentang, nilai disesuaikan secara proporsional dan alami mengikuti peringkat (juara teratas berkisar 79–81, tanpa lonjakan ekstrem ke 90-an). Jika tidak dicentang, nilai rapor siswa tetap apa adanya (hanya nomor ranking yang berubah).
            </span>
          </label>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="p-4 sm:p-5 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-3 bg-slate-50/50">
        <button
          type="button"
          @click="$emit('reset-default')"
          :disabled="saving"
          class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
          title="Kembalikan semua peringkat siswa murni berdasarkan rata-rata nilai asli rapor"
        >
          <RotateCcw class="w-3.5 h-3.5" />
          <span>Kembalikan ke Ranking Otomatis</span>
        </button>

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
            :disabled="saving || hasDuplicateRank"
            class="px-5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl text-xs transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-center"
          >
            <Check class="w-4 h-4" />
            <span>{{ saving ? 'Menyimpan...' : 'Simpan Peringkat' }}</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Trophy, X, ArrowUp, ArrowDown, AlertTriangle, Sparkles, RotateCcw, Check } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  className: { type: String, default: '' },
  semester: { type: String, default: 'ganjil' },
  students: { type: Array, default: () => [] },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'save', 'reset-default']);

const localRankList = ref([]);
const adjustScores = ref(false);

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      initRankList();
    }
  },
  { immediate: true }
);

function initRankList() {
  const sorted = [...props.students].sort((a, b) => {
    const rankA = typeof a.rank === 'number' ? a.rank : 9999;
    const rankB = typeof b.rank === 'number' ? b.rank : 9999;
    if (rankA !== rankB) return rankA - rankB;
    const avgA = Number(a.average_score) || 0;
    const avgB = Number(b.average_score) || 0;
    if (avgB !== avgA) return avgB - avgA;
    return (a.full_name || '').localeCompare(b.full_name || '');
  });

  localRankList.value = sorted.map((st, idx) => ({
    student_id: st.student_id,
    full_name: st.full_name,
    nisn: st.nisn,
    total_score: st.total_score || 0,
    average_score: st.average_score,
    calculated_rank: st.calculated_rank || (idx + 1),
    rank: idx + 1,
  }));

  adjustScores.value = false;
}

function moveStudentRank(index, direction) {
  const targetIndex = index + direction;
  if (targetIndex < 0 || targetIndex >= localRankList.value.length) return;

  const currentList = [...localRankList.value];
  const itemToMove = currentList[index];
  currentList.splice(index, 1);
  currentList.splice(targetIndex, 0, itemToMove);

  currentList.forEach((item, idx) => {
    item.rank = idx + 1;
  });

  localRankList.value = currentList;
}

const duplicateRanks = computed(() => {
  const counts = {};
  const duplicates = new Set();
  localRankList.value.forEach(item => {
    const r = parseInt(item.rank);
    if (!isNaN(r)) {
      counts[r] = (counts[r] || 0) + 1;
      if (counts[r] > 1) {
        duplicates.add(r);
      }
    }
  });
  return Array.from(duplicates);
});

const hasDuplicateRank = computed(() => duplicateRanks.value.length > 0);

const hasInvertedRank = computed(() => {
  if (localRankList.value.length < 2) return false;
  const sorted = [...localRankList.value].sort((a, b) => (parseInt(a.rank) || 9999) - (parseInt(b.rank) || 9999));
  for (let i = 0; i < sorted.length - 1; i++) {
    const avgCurrent = Number(sorted[i].average_score) || 0;
    const avgNext = Number(sorted[i + 1].average_score) || 0;
    if (avgCurrent < avgNext) {
      return true;
    }
  }
  return false;
});

function autoSequenceRanks() {
  localRankList.value.forEach((item, idx) => {
    item.rank = idx + 1;
  });
}

function sortListByRankInputs() {
  localRankList.value = [...localRankList.value].sort((a, b) => {
    const rA = parseInt(a.rank) || 9999;
    const rB = parseInt(b.rank) || 9999;
    return rA - rB;
  });
}

function handleSave() {
  emit('save', {
    ranks: localRankList.value.map(item => ({
      student_id: item.student_id,
      rank: parseInt(item.rank) || 1,
    })),
    adjust_scores: adjustScores.value,
  });
}
</script>
