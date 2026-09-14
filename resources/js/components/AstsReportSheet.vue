<template>
  <div class="space-y-2 text-slate-900 font-inter text-[10px] leading-tight max-w-full">
    <!-- 1. KOP SURAT RESMI MADRASAH -->
    <div class="border-b-2 border-double border-slate-900 pb-1.5 flex items-center gap-2 sm:gap-3">
      <div class="w-12 h-12 sm:w-14 sm:h-14 flex-shrink-0 flex items-center justify-center p-0.5">
        <img
          v-if="report?.school_setting?.logo_url"
          :src="getImageUrl(report.school_setting.logo_url)"
          class="w-full h-full object-contain"
          alt="Logo Sekolah"
        />
        <div v-else class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-800 text-white flex items-center justify-center font-black text-base sm:text-lg font-serif">
          MTS
        </div>
      </div>

      <div class="flex-1 text-center pr-0 sm:pr-8">
        <h3 class="text-[10px] sm:text-[11px] font-bold text-slate-700 uppercase tracking-wider leading-tight">
          {{ report?.school_setting?.foundation_name || 'YAYASAN PENDIDIKAN ISLAM AL - HASANAH' }}
        </h3>
        <h1 class="text-xs sm:text-base font-black text-slate-950 uppercase font-lexend tracking-wide mt-0.5 leading-tight">
          {{ report?.school_setting?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
        </h1>
        <p class="text-[8.5px] sm:text-[9.5px] text-slate-600 font-medium mt-0.5">
          {{ report?.school_setting?.address || 'Jl. Ciapus Sukamakmur No.05, Kec. Ciomas, Kab. Bogor' }}
        </p>
        <p class="text-[8px] sm:text-[8.5px] text-slate-500 font-mono">
          Telp: {{ report?.school_setting?.phone || '081617666017' }} &bull; Email: {{ report?.school_setting?.email || 'mtsalhasanah.ciomas@gmail.com' }}
        </p>
      </div>
    </div>

    <!-- 2. JUDUL LEMBAR RAPOR ASTS -->
    <div class="text-center space-y-0.5 pt-0.5">
      <h2 class="text-xs sm:text-sm font-black uppercase text-slate-950 font-lexend tracking-wide underline">
        LAPORAN HASIL BELAJAR ASESMEN SUMATIF TENGAH SEMESTER (ASTS)
      </h2>
      <p class="text-[9.5px] sm:text-[10px] font-bold text-slate-700 uppercase tracking-wide">
        SEMESTER {{ report?.semester_label?.toUpperCase() || 'GANJIL' }} &bull; TAHUN PELAJARAN {{ report?.academic_year || '2026/2027' }}
      </p>
    </div>

    <!-- 3. IDENTITAS PESERTA DIDIK -->
    <div class="relative flex flex-col sm:grid sm:grid-cols-2 gap-x-6 gap-y-1 bg-slate-50/80 p-2 sm:p-2.5 rounded-lg border border-slate-300 text-[10px] sm:text-[10.5px] font-medium print:bg-transparent print:grid print:grid-cols-2">
      <!-- Left Info -->
      <div class="space-y-0.5">
        <div class="flex">
          <span class="w-28 font-bold text-slate-600">Nama Peserta Didik</span>
          <span class="font-extrabold text-slate-900 font-lexend truncate">: {{ report?.student?.full_name || '-' }}</span>
        </div>
        <div class="flex">
          <span class="w-28 font-bold text-slate-600">NISN / NIS</span>
          <span class="font-bold text-slate-800 font-mono">: {{ report?.student?.nisn || '-' }} / {{ report?.student?.nis || '-' }}</span>
        </div>
      </div>

      <!-- Right Info -->
      <div class="space-y-0.5">
        <div class="flex">
          <span class="w-24 font-bold text-slate-600">Kelas / Rombel</span>
          <span class="font-extrabold text-slate-900">: Kelas {{ report?.student?.class_name || '-' }}</span>
        </div>
        <div class="flex">
          <span class="w-24 font-bold text-slate-600">Fase / Semester</span>
          <span class="font-bold text-slate-800">: Fase D / {{ report?.semester === 'genap' ? 'Genap (Dua)' : 'Ganjil (Satu)' }}</span>
        </div>
      </div>

      <!-- Rank Badge (adaptive on mobile and print) -->
      <div v-if="report?.rank && report?.rank !== '-'" class="sm:absolute right-2 top-1.5 self-start flex items-center gap-1 px-2 py-0.5 bg-amber-50 border border-amber-300 rounded text-amber-900 shadow-2xs print:border-slate-400 print:bg-transparent mt-1 sm:mt-0">
        <span class="text-[10px]">🏆</span>
        <span class="text-[9.5px] sm:text-[10px] font-black uppercase tracking-wider font-lexend">
          Peringkat {{ report.rank }}
        </span>
        <span v-if="report?.total_students" class="text-[9px] font-bold text-amber-800 print:text-slate-700">
          / {{ report.total_students }}
        </span>
      </div>
    </div>

    <!-- 4. TABEL CAPAIAN HASIL BELAJAR ASTS -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse border border-slate-900 text-[10px]">
        <thead>
          <tr class="bg-slate-100 text-slate-950 font-black uppercase text-center border-b border-slate-900 text-[9.5px]">
            <th class="border border-slate-900 p-1 w-7">No</th>
            <th class="border border-slate-900 p-1 text-left">Mata Pelajaran</th>
            <th class="border border-slate-900 p-1 w-14 text-center">KKTP</th>
            <th class="border border-slate-900 p-1 w-16 text-center">Nilai ASTS</th>
            <th class="border border-slate-900 p-1 w-12 text-center">Predikat</th>
            <th class="border border-slate-900 p-1 min-w-[190px] text-left">Capaian Kompetensi / Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <!-- 1. KELOMPOK WAJIB A -->
          <tr class="bg-slate-100/90 font-black text-slate-950 text-[10px]">
            <td colspan="6" class="border border-slate-900 px-2 py-0.5 uppercase tracking-wide">
              Kelompok Wajib A
            </td>
          </tr>

          <!-- PAI Header Item 1 -->
          <tr v-if="report?.subjects_pai && report.subjects_pai.length" class="bg-slate-50/70 font-bold text-slate-900 text-[10px]">
            <td class="border border-slate-400 p-1 text-center font-bold text-slate-700">1</td>
            <td colspan="5" class="border border-slate-400 p-1 font-black text-slate-900">
              Pendidikan Agama Islam
            </td>
          </tr>

          <!-- PAI Sub-items: a, b, c, d -->
          <tr
            v-for="(sbj, i) in report?.subjects_pai || []"
            :key="'pai-'+sbj.subject_id"
            class="border-b border-slate-300"
          >
            <td class="border border-slate-400 p-1 text-center font-bold text-slate-600"></td>
            <td class="border border-slate-400 p-1 text-slate-900 pl-4 font-medium">
              <span class="font-bold">{{ ['a', 'b', 'c', 'd', 'e', 'f'][i] || '-' }}.</span>
              <span class="ml-1.5">{{ sbj.name }}</span>
            </td>
            <td class="border border-slate-400 p-1 text-center font-mono font-bold text-slate-600">{{ sbj.kkm }}</td>
            <td class="border border-slate-400 p-1 text-center font-mono font-black" :class="sbj.score !== null && sbj.score >= sbj.kkm ? 'text-slate-950' : 'text-rose-700'">
              {{ sbj.score !== null ? sbj.score : '-' }}
            </td>
            <td class="border border-slate-400 p-1 text-center font-black text-slate-800">{{ sbj.predicate || '-' }}</td>
            <td class="border border-slate-400 p-1 text-[9px] leading-tight text-slate-700">{{ sbj.description || '-' }}</td>
          </tr>

          <!-- Kelompok Wajib A: General Subjects (2, 3, 4, 5, 6, 7, 8) -->
          <tr
            v-for="(sbj, i) in report?.subjects_group_a || []"
            :key="'ga-'+sbj.subject_id"
            class="border-b border-slate-300"
          >
            <td class="border border-slate-400 p-1 text-center font-bold text-slate-700">{{ i + 2 }}</td>
            <td class="border border-slate-400 p-1 font-bold text-slate-900">{{ sbj.name }}</td>
            <td class="border border-slate-400 p-1 text-center font-mono font-bold text-slate-600">{{ sbj.kkm }}</td>
            <td class="border border-slate-400 p-1 text-center font-mono font-black" :class="sbj.score !== null && sbj.score >= sbj.kkm ? 'text-slate-950' : 'text-rose-700'">
              {{ sbj.score !== null ? sbj.score : '-' }}
            </td>
            <td class="border border-slate-400 p-1 text-center font-black text-slate-800">{{ sbj.predicate || '-' }}</td>
            <td class="border border-slate-400 p-1 text-[9px] leading-tight text-slate-700">{{ sbj.description || '-' }}</td>
          </tr>

          <!-- 2. KELOMPOK WAJIB B -->
          <tr v-if="report?.subjects_group_b && report.subjects_group_b.length" class="bg-slate-100/90 font-black text-slate-950 text-[10px]">
            <td colspan="6" class="border border-slate-900 px-2 py-0.5 uppercase tracking-wide">
              Kelompok Wajib B
            </td>
          </tr>
          <tr
            v-for="(sbj, i) in report?.subjects_group_b || []"
            :key="'gb-'+sbj.subject_id"
            class="border-b border-slate-300"
          >
            <td class="border border-slate-400 p-1 text-center font-bold text-slate-700">{{ ['i', 'ii', 'iii', 'iv', 'v'][i] || (i + 1) }}.</td>
            <td class="border border-slate-400 p-1 font-bold text-slate-900">{{ sbj.name }}</td>
            <td class="border border-slate-400 p-1 text-center font-mono font-bold text-slate-600">{{ sbj.kkm }}</td>
            <td class="border border-slate-400 p-1 text-center font-mono font-black" :class="sbj.score !== null && sbj.score >= sbj.kkm ? 'text-slate-950' : 'text-rose-700'">
              {{ sbj.score !== null ? sbj.score : '-' }}
            </td>
            <td class="border border-slate-400 p-1 text-center font-black text-slate-800">{{ sbj.predicate || '-' }}</td>
            <td class="border border-slate-400 p-1 text-[9px] leading-tight text-slate-700">{{ sbj.description || '-' }}</td>
          </tr>

          <!-- 3. MUATAN LOKAL (MULOK) -->
          <tr v-if="report?.subjects_mulok && report.subjects_mulok.length" class="bg-slate-100/90 font-black text-slate-950 text-[10px]">
            <td colspan="6" class="border border-slate-900 px-2 py-0.5 uppercase tracking-wide">
              Muatan Lokal
            </td>
          </tr>
          <tr
            v-for="(sbj, i) in report?.subjects_mulok || []"
            :key="'mulok-'+sbj.subject_id"
            class="border-b border-slate-300"
          >
            <td class="border border-slate-400 p-1 text-center font-bold text-slate-700">{{ i + 1 }}</td>
            <td class="border border-slate-400 p-1 font-bold text-slate-900">{{ sbj.name }}</td>
            <td class="border border-slate-400 p-1 text-center font-mono font-bold text-slate-600">{{ sbj.kkm }}</td>
            <td class="border border-slate-400 p-1 text-center font-mono font-black" :class="sbj.score !== null && sbj.score >= sbj.kkm ? 'text-slate-950' : 'text-rose-700'">
              {{ sbj.score !== null ? sbj.score : '-' }}
            </td>
            <td class="border border-slate-400 p-1 text-center font-black text-slate-800">{{ sbj.predicate || '-' }}</td>
            <td class="border border-slate-400 p-1 text-[9px] leading-tight text-slate-700">{{ sbj.description || '-' }}</td>
          </tr>

          <!-- REKAP TOTAL & RATA-RATA & PERINGKAT -->
          <tr class="bg-slate-100 font-bold border-t-2 border-slate-900 text-[10px]">
            <td colspan="3" class="border border-slate-900 p-1 text-right uppercase tracking-wider">Jumlah Nilai Keseluruhan</td>
            <td class="border border-slate-900 p-1 text-center font-mono font-black text-slate-950 text-[11px]">{{ report?.total_score || 0 }}</td>
            <td colspan="2" class="border border-slate-900 p-1 text-slate-500 text-[9px] italic">Total perolehan nilai ASTS</td>
          </tr>
          <tr class="bg-slate-100 font-bold border-t border-slate-400 text-[10px]">
            <td colspan="3" class="border border-slate-900 p-1 text-right uppercase tracking-wider">Rata-Rata Nilai Siswa</td>
            <td class="border border-slate-900 p-1 text-center font-mono font-black text-emerald-800 text-[11px]">{{ report?.average_score || 0 }}</td>
            <td colspan="2" class="border border-slate-900 p-1 text-slate-500 text-[9px] italic">Rata-rata capaian tengah semester</td>
          </tr>
          <tr class="bg-amber-50/40 print:bg-transparent font-bold border-t border-slate-400 text-[10px]">
            <td colspan="3" class="border border-slate-900 p-1 text-right uppercase tracking-wider text-amber-950 print:text-slate-950">
              Peringkat di Kelas
            </td>
            <td class="border border-slate-900 p-1 text-center font-mono font-black text-amber-900 print:text-slate-950 text-[11px]">
              {{ report?.rank || '-' }}
            </td>
            <td colspan="2" class="border border-slate-900 p-1 text-slate-700 text-[9.5px]">
              <span class="font-bold">Peringkat ke-{{ report?.rank || '-' }}</span> dari <span class="font-bold">{{ report?.total_students || '-' }}</span> siswa
              <span v-if="report?.class_average_score" class="text-slate-500"> (Rata-rata Kelas: <strong class="text-slate-800">{{ report.class_average_score }}</strong>)</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 5. KETIDAKHADIRAN & CATATAN WALI KELAS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-0.5">
      <!-- Tabel Ketidakhadiran -->
      <div class="sm:col-span-1 relative group">
        <table class="w-full text-[10px] border-collapse border border-slate-900">
          <thead>
            <tr class="bg-slate-100 text-center font-black uppercase text-[9px] border-b border-slate-900">
              <th colspan="2" class="p-1 border border-slate-900">
                <div class="flex items-center justify-between px-1">
                  <span>Rekapitulasi Kehadiran</span>
                  <button
                    v-if="allowEdit"
                    type="button"
                    @click="$emit('edit-notes')"
                    class="no-print text-indigo-700 hover:text-indigo-950 font-bold text-[8.5px] cursor-pointer"
                    title="Ubah angka kehadiran"
                  >
                    ✏️ Edit
                  </button>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="p-1 border border-slate-400 font-medium">1. Sakit (S)</td>
              <td class="p-1 border border-slate-400 text-center font-mono font-bold">{{ report?.attendance?.sick || 0 }} hari</td>
            </tr>
            <tr>
              <td class="p-1 border border-slate-400 font-medium">2. Izin (I)</td>
              <td class="p-1 border border-slate-400 text-center font-mono font-bold">{{ report?.attendance?.permission || 0 }} hari</td>
            </tr>
            <tr>
              <td class="p-1 border border-slate-400 font-medium">3. Tanpa Keterangan (A)</td>
              <td class="p-1 border border-slate-400 text-center font-mono font-bold">{{ report?.attendance?.unexcused || 0 }} hari</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Catatan Wali Kelas -->
      <div class="sm:col-span-2 p-2 border border-slate-900 rounded flex flex-col justify-between space-y-0.5">
        <div class="flex items-center justify-between">
          <span class="block font-bold uppercase text-[9px] text-slate-800 tracking-wider">
            Catatan & Motivasi Perkembangan Belajar Wali Kelas:
          </span>
          <button
            v-if="allowEdit"
            type="button"
            @click="$emit('edit-notes')"
            class="no-print px-2 py-0.5 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-[9px] font-bold flex items-center gap-1 cursor-pointer transition-all shadow-2xs"
            title="Edit kehadiran dan catatan motivasi wali kelas untuk siswa ini"
          >
            <span>✏️ Edit Catatan & Absensi</span>
          </button>
        </div>
        <p class="text-[10px] text-slate-800 italic leading-snug flex-1">
          "{{ report?.homeroom_notes || 'Tingkatkan terus ketekunan belajar, kedisiplinan beribadah, dan keaktifan di madrasah.' }}"
        </p>
      </div>
    </div>

    <!-- 6. TITIMANGSA & BLOK TANDA TANGAN RESMI -->
    <div class="pt-2 space-y-1 break-inside-avoid">
      <div class="text-right text-[10.5px] font-bold text-slate-800 pr-2 flex items-center justify-end gap-1.5">
        <span>{{ report?.city || 'Bogor' }}, {{ report?.issued_date || '........................' }}</span>
        <button
          v-if="allowEdit"
          type="button"
          @click="$emit('edit-titimangsa')"
          class="no-print text-amber-700 hover:text-amber-950 font-bold text-[8.5px] cursor-pointer"
          title="Ubah Tempat dan Tanggal Titimangsa Rapor"
        >
          ✏️ Edit Titimangsa
        </button>
      </div>

      <div class="grid grid-cols-3 text-center text-[10px] gap-2 pt-1">
        <!-- Orang Tua -->
        <div class="space-y-9">
          <div>
            <p class="font-bold text-slate-700">Mengetahui,</p>
            <p class="font-bold text-slate-900">Orang Tua / Wali Siswa</p>
          </div>
          <div class="border-b border-slate-900 mx-4"></div>
        </div>

        <!-- Wali Kelas -->
        <div class="space-y-9">
          <div>
            <p class="font-bold text-slate-700">Wali Kelas,</p>
            <p class="font-bold text-slate-900">Kelas {{ report?.student?.class_name || '' }}</p>
          </div>
          <div>
            <p class="font-black text-slate-950 underline">{{ report?.student?.homeroom_teacher_name || '............................................' }}</p>
            <p class="text-[9px] text-slate-600 font-mono">NIP. {{ report?.student?.homeroom_teacher_nip || '-' }}</p>
          </div>
        </div>

        <!-- Kepala Madrasah -->
        <div class="space-y-9">
          <div>
            <p class="font-bold text-slate-700">Mengetahui,</p>
            <p class="font-bold text-slate-900">Kepala Madrasah</p>
          </div>
          <div>
            <p class="font-black text-slate-950 underline">{{ report?.school_setting?.principal_name || '............................................' }}</p>
            <p class="text-[9px] text-slate-600 font-mono">NIP. {{ report?.school_setting?.principal_nip || '-' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  report: {
    type: Object,
    required: true,
  },
  allowEdit: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['edit-notes', 'edit-titimangsa']);

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
  const clean = path.replace(/^\/?storage\//, '').replace(/^\//, '');
  return `/storage/${clean}`;
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
