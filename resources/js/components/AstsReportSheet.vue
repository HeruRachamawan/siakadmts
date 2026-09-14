<template>
  <div class="space-y-4 text-slate-900 font-inter text-[11px] leading-relaxed max-w-full">
    <!-- 1. KOP SURAT RESMI MADRASAH -->
    <div class="border-b-4 border-double border-slate-900 pb-3 flex items-center gap-4">
      <div class="w-16 h-16 flex-shrink-0 flex items-center justify-center p-1">
        <img
          v-if="report?.school_setting?.logo_url"
          :src="getImageUrl(report.school_setting.logo_url)"
          class="w-full h-full object-contain"
          alt="Logo Sekolah"
        />
        <div v-else class="w-14 h-14 rounded-xl bg-emerald-800 text-white flex items-center justify-center font-black text-xl font-serif">
          MTS
        </div>
      </div>

      <div class="flex-1 text-center pr-10 sm:pr-14">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest leading-tight">
          {{ report?.school_setting?.foundation_name || 'YAYASAN PENDIDIKAN ISLAM' }}
        </h3>
        <h1 class="text-base sm:text-lg font-black text-slate-950 uppercase font-lexend tracking-wider mt-0.5 leading-tight">
          {{ report?.school_setting?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
        </h1>
        <p class="text-[10px] text-slate-600 font-medium mt-0.5">
          {{ report?.school_setting?.address || 'Jl. Raya Ciomas No. 123, Kabupaten Bogor' }}
        </p>
        <p class="text-[9px] text-slate-500 font-mono">
          Telp: {{ report?.school_setting?.phone || '(0251) 8631234' }} &bull; Email: {{ report?.school_setting?.email || 'info@mtsalhasanah.sch.id' }}
        </p>
      </div>
    </div>

    <!-- 2. JUDUL LEMBAR RAPOR ASTS -->
    <div class="text-center space-y-0.5 pt-1">
      <h2 class="text-sm font-black uppercase text-slate-950 font-lexend tracking-wider underline">
        LAPORAN HASIL BELAJAR ASESMEN SUMATIF TENGAH SEMESTER (ASTS)
      </h2>
      <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">
        SEMESTER {{ report?.semester_label?.toUpperCase() || 'GANJIL' }} &bull; TAHUN PELAJARAN {{ report?.academic_year || '2026/2027' }}
      </p>
    </div>

    <!-- 3. IDENTITAS PESERTA DIDIK -->
    <div class="grid grid-cols-2 gap-x-8 gap-y-1 bg-slate-50/80 p-3 rounded-xl border border-slate-200 text-xs font-medium">
      <div class="space-y-1">
        <div class="flex">
          <span class="w-32 font-bold text-slate-500">Nama Peserta Didik</span>
          <span class="font-extrabold text-slate-900 font-lexend">: {{ report?.student?.full_name || '-' }}</span>
        </div>
        <div class="flex">
          <span class="w-32 font-bold text-slate-500">NISN / NIS</span>
          <span class="font-bold text-slate-800 font-mono">: {{ report?.student?.nisn || '-' }} / {{ report?.student?.nis || '-' }}</span>
        </div>
      </div>

      <div class="space-y-1">
        <div class="flex">
          <span class="w-28 font-bold text-slate-500">Kelas / Rombel</span>
          <span class="font-extrabold text-slate-900">: Kelas {{ report?.student?.class_name || '-' }}</span>
        </div>
        <div class="flex">
          <span class="w-28 font-bold text-slate-500">Fase / Semester</span>
          <span class="font-bold text-slate-800">: Fase D / {{ report?.semester === 'genap' ? 'Genap (Dua)' : 'Ganjil (Satu)' }}</span>
        </div>
      </div>
    </div>

    <!-- 4. TABEL CAPAIAN HASIL BELAJAR ASTS -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse border border-slate-900 text-xs">
        <thead>
          <tr class="bg-slate-100 text-slate-950 font-black uppercase text-center border-b border-slate-900 text-[10.5px]">
            <th class="border border-slate-900 p-2 w-8">No</th>
            <th class="border border-slate-900 p-2 text-left">Mata Pelajaran</th>
            <th class="border border-slate-900 p-2 w-16">KKTP / KKM</th>
            <th class="border border-slate-900 p-2 w-20">Nilai ASTS</th>
            <th class="border border-slate-900 p-2 w-16">Predikat</th>
            <th class="border border-slate-900 p-2 min-w-[200px] text-left">Capaian Kompetensi / Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <!-- KELOMPOK A -->
          <tr class="bg-slate-50 font-black text-slate-900 text-[11px]">
            <td colspan="6" class="border border-slate-900 px-3 py-1.5 uppercase tracking-wide">
              Kelompok A (Pendidikan Agama Islam & Wajib Madrasah)
            </td>
          </tr>
          <tr
            v-for="(sbj, i) in report?.subjects_group_a || []"
            :key="'a-'+sbj.subject_id"
            class="border-b border-slate-300"
          >
            <td class="border border-slate-400 p-1.5 text-center font-bold text-slate-600">{{ i + 1 }}</td>
            <td class="border border-slate-400 p-1.5 font-bold text-slate-900">{{ sbj.name }}</td>
            <td class="border border-slate-400 p-1.5 text-center font-mono font-bold text-slate-600">{{ sbj.kkm }}</td>
            <td class="border border-slate-400 p-1.5 text-center font-mono font-black" :class="sbj.score !== null && sbj.score >= sbj.kkm ? 'text-slate-950' : 'text-rose-700'">
              {{ sbj.score !== null ? sbj.score : '-' }}
            </td>
            <td class="border border-slate-400 p-1.5 text-center font-black text-slate-800">{{ sbj.predicate || '-' }}</td>
            <td class="border border-slate-400 p-1.5 text-[10.5px] leading-tight text-slate-700">{{ sbj.description || '-' }}</td>
          </tr>

          <!-- KELOMPOK B -->
          <tr class="bg-slate-50 font-black text-slate-900 text-[11px]">
            <td colspan="6" class="border border-slate-900 px-3 py-1.5 uppercase tracking-wide">
              Kelompok B (Mata Pelajaran Umum & Muatan Lokal)
            </td>
          </tr>
          <tr
            v-for="(sbj, i) in report?.subjects_group_b || []"
            :key="'b-'+sbj.subject_id"
            class="border-b border-slate-300"
          >
            <td class="border border-slate-400 p-1.5 text-center font-bold text-slate-600">{{ i + 1 }}</td>
            <td class="border border-slate-400 p-1.5 font-bold text-slate-900">{{ sbj.name }}</td>
            <td class="border border-slate-400 p-1.5 text-center font-mono font-bold text-slate-600">{{ sbj.kkm }}</td>
            <td class="border border-slate-400 p-1.5 text-center font-mono font-black" :class="sbj.score !== null && sbj.score >= sbj.kkm ? 'text-slate-950' : 'text-rose-700'">
              {{ sbj.score !== null ? sbj.score : '-' }}
            </td>
            <td class="border border-slate-400 p-1.5 text-center font-black text-slate-800">{{ sbj.predicate || '-' }}</td>
            <td class="border border-slate-400 p-1.5 text-[10.5px] leading-tight text-slate-700">{{ sbj.description || '-' }}</td>
          </tr>

          <!-- REKAP TOTAL & RATA-RATA -->
          <tr class="bg-slate-100 font-bold border-t-2 border-slate-900 text-[11px]">
            <td colspan="3" class="border border-slate-900 p-2 text-right uppercase tracking-wider">Jumlah Nilai Keseluruhan</td>
            <td class="border border-slate-900 p-2 text-center font-mono font-black text-slate-950 text-xs">{{ report?.total_score || 0 }}</td>
            <td colspan="2" class="border border-slate-900 p-2 text-slate-500 text-[10px] italic">Total perolehan nilai ASTS</td>
          </tr>
          <tr class="bg-slate-100 font-bold border-t border-slate-400 text-[11px]">
            <td colspan="3" class="border border-slate-900 p-2 text-right uppercase tracking-wider">Rata-Rata Nilai</td>
            <td class="border border-slate-900 p-2 text-center font-mono font-black text-emerald-800 text-xs">{{ report?.average_score || 0 }}</td>
            <td colspan="2" class="border border-slate-900 p-2 text-slate-500 text-[10px] italic">Rata-rata capaian tengah semester</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 5. KETIDAKHADIRAN & CATATAN WALI KELAS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
      <!-- Tabel Ketidakhadiran -->
      <div class="sm:col-span-1">
        <table class="w-full text-xs border-collapse border border-slate-900">
          <thead>
            <tr class="bg-slate-100 text-center font-black uppercase text-[10px] border-b border-slate-900">
              <th colspan="2" class="p-1.5 border border-slate-900">Rekapitulasi Kehadiran</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="p-1.5 border border-slate-400 font-medium">1. Sakit (S)</td>
              <td class="p-1.5 border border-slate-400 text-center font-mono font-bold">{{ report?.attendance?.sick || 0 }} hari</td>
            </tr>
            <tr>
              <td class="p-1.5 border border-slate-400 font-medium">2. Izin (I)</td>
              <td class="p-1.5 border border-slate-400 text-center font-mono font-bold">{{ report?.attendance?.permission || 0 }} hari</td>
            </tr>
            <tr>
              <td class="p-1.5 border border-slate-400 font-medium">3. Tanpa Keterangan (A)</td>
              <td class="p-1.5 border border-slate-400 text-center font-mono font-bold">{{ report?.attendance?.unexcused || 0 }} hari</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Catatan Wali Kelas -->
      <div class="sm:col-span-2 p-3 border border-slate-900 rounded-lg flex flex-col justify-between space-y-1">
        <span class="block font-bold uppercase text-[10px] text-slate-800 tracking-wider">
          Catatan & Motivasi Perkembangan Belajar Wali Kelas:
        </span>
        <p class="text-xs text-slate-800 italic leading-relaxed flex-1">
          "{{ report?.homeroom_notes || 'Tingkatkan terus ketekunan belajar, kedisiplinan beribadah, dan keaktifan di madrasah.' }}"
        </p>
      </div>
    </div>

    <!-- 6. TITIMANGSA & BLOK TANDA TANGAN RESMI -->
    <div class="pt-6 space-y-2 break-inside-avoid">
      <div class="text-right text-xs font-bold text-slate-800 pr-4">
        {{ report?.city || 'Bogor' }}, {{ report?.issued_date || '........................' }}
      </div>

      <div class="grid grid-cols-3 text-center text-xs gap-4 pt-2">
        <!-- Orang Tua -->
        <div class="space-y-16">
          <div>
            <p class="font-bold text-slate-700">Mengetahui,</p>
            <p class="font-bold text-slate-900">Orang Tua / Wali Siswa</p>
          </div>
          <div class="border-b border-slate-900 mx-4"></div>
        </div>

        <!-- Wali Kelas -->
        <div class="space-y-16">
          <div>
            <p class="font-bold text-slate-700">Wali Kelas,</p>
            <p class="font-bold text-slate-900">Kelas {{ report?.student?.class_name || '' }}</p>
          </div>
          <div>
            <p class="font-black text-slate-950 underline">{{ report?.student?.homeroom_teacher_name || '............................................' }}</p>
            <p class="text-[10px] text-slate-600 font-mono">NIP. {{ report?.student?.homeroom_teacher_nip || '-' }}</p>
          </div>
        </div>

        <!-- Kepala Madrasah -->
        <div class="space-y-16">
          <div>
            <p class="font-bold text-slate-700">Mengetahui,</p>
            <p class="font-bold text-slate-900">Kepala Madrasah</p>
          </div>
          <div>
            <p class="font-black text-slate-950 underline">{{ report?.school_setting?.principal_name || '............................................' }}</p>
            <p class="text-[10px] text-slate-600 font-mono">NIP. {{ report?.school_setting?.principal_nip || '-' }}</p>
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
});

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
  return `/storage/${path.replace(/^\/?storage\//, '')}`;
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
