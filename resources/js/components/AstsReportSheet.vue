<template>
  <div class="text-slate-900 font-inter text-[10px] leading-tight max-w-full print:leading-tight">
    <!-- 1. KOP SURAT RESMI MADRASAH (TABLE-BASED: 100% BULLETPROOF & DEAD-CENTERED) -->
    <table class="w-full border-collapse" style="width: 100%; border-collapse: collapse; margin-bottom: 2px;">
      <tbody>
        <tr>
          <!-- Left: Official Logo Box -->
          <td style="width: 72px; min-width: 72px; max-width: 72px; vertical-align: middle; text-align: left; padding: 0 4px 4px 0;">
            <img
              v-if="report?.school_setting?.logo_url"
              :src="getImageUrl(report.school_setting.logo_url)"
              style="width: 68px; height: 68px; max-width: 68px; max-height: 68px; object-fit: contain; display: block;"
              alt="Logo Madrasah"
            />
            <div v-else style="width: 60px; height: 60px; border-radius: 8px; background-color: #065f46; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 18px; font-family: serif;">
              MTS
            </div>
          </td>

          <!-- Center: School Information -->
          <td style="text-align: center; vertical-align: middle; padding: 0 8px 4px 8px;">
            <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #1e293b; line-height: 1.2;">
              {{ report?.school_setting?.foundation_name || 'YAYASAN PENDIDIKAN ISLAM AL - HASANAH' }}
            </div>
            <div style="font-size: 15.5px; font-weight: 900; text-transform: uppercase; color: #020617; font-family: 'Lexend', system-ui, sans-serif; letter-spacing: 0.02em; margin-top: 2px; line-height: 1.2;">
              {{ report?.school_setting?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
            </div>
            <div style="font-size: 9.5px; color: #334155; font-weight: 500; margin-top: 2px; line-height: 1.2;">
              {{ report?.school_setting?.address || 'Jl. Ciapus Sukamakmur No.05, Kec. Ciomas, Kab. Bogor' }}
            </div>
            <div style="font-size: 8.5px; color: #475569; font-family: monospace; margin-top: 2px; line-height: 1.2;">
              Telp: {{ report?.school_setting?.phone || '081617666017' }} &bull; Email: {{ report?.school_setting?.email || 'mtsalhasanah.ciomas@gmail.com' }}
            </div>
          </td>

          <!-- Right: Exact Symmetrical Spacer (keeps center td dead-center) -->
          <td style="width: 72px; min-width: 72px; max-width: 72px; vertical-align: middle; padding: 0;"></td>
        </tr>
      </tbody>
    </table>

    <!-- Garis Ganda Resmi Kop Surat (Double Border Kop) -->
    <div style="border-top: 2.5px solid #0f172a; border-bottom: 1px solid #0f172a; height: 3.5px; margin-bottom: 6px; width: 100%;"></div>

    <!-- 2. JUDUL LEMBAR RAPOR ASTS -->
    <div style="text-align: center; margin-bottom: 6px;">
      <h2 style="font-size: 12px; font-weight: 900; text-transform: uppercase; color: #020617; font-family: 'Lexend', system-ui, sans-serif; letter-spacing: 0.03em; text-decoration: underline; margin: 0;">
        LAPORAN HASIL BELAJAR ASESMEN SUMATIF TENGAH SEMESTER (ASTS)
      </h2>
      <p style="font-size: 9.5px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.03em; margin: 2px 0 0 0;">
        SEMESTER {{ report?.semester_label?.toUpperCase() || 'GANJIL' }} &bull; TAHUN PELAJARAN {{ report?.academic_year || '2026/2027' }}
      </p>
    </div>

    <!-- 3. IDENTITAS PESERTA DIDIK (2-COLUMN TABLE FORMAT) -->
    <table class="identity-box" style="width: 100%; border: 1px solid #94a3b8; border-radius: 6px; background-color: #f8fafc; margin-bottom: 6px; border-collapse: collapse; font-size: 10px; font-weight: 500;">
      <tbody>
        <tr>
          <!-- Left Column -->
          <td style="width: 50%; vertical-align: top; padding: 5px 8px;">
            <table style="width: 100%; border-collapse: collapse;">
              <tr>
                <td style="width: 110px; font-weight: 700; color: #475569; padding: 1px 0;">Nama Peserta Didik</td>
                <td style="font-weight: 900; color: #020617; font-family: 'Lexend', system-ui, sans-serif; padding: 1px 0;">: {{ report?.student?.full_name || '-' }}</td>
              </tr>
              <tr>
                <td style="font-weight: 700; color: #475569; padding: 1px 0;">NISN / NIS</td>
                <td style="font-weight: 700; color: #1e293b; font-family: monospace; padding: 1px 0;">: {{ report?.student?.nisn || '-' }} / {{ report?.student?.nis || '-' }}</td>
              </tr>
            </table>
          </td>

          <!-- Right Column -->
          <td style="width: 50%; vertical-align: top; padding: 5px 8px; position: relative;">
            <table style="width: 100%; border-collapse: collapse;">
              <tr>
                <td style="width: 100px; font-weight: 700; color: #475569; padding: 1px 0;">Kelas / Rombel</td>
                <td style="font-weight: 900; color: #020617; padding: 1px 0;">: Kelas {{ report?.student?.class_name || '-' }}</td>
              </tr>
              <tr>
                <td style="font-weight: 700; color: #475569; padding: 1px 0;">Fase / Semester</td>
                <td style="font-weight: 700; color: #1e293b; padding: 1px 0;">: Fase D / {{ report?.semester === 'genap' ? 'Genap (Dua)' : 'Ganjil (Satu)' }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- 4. TABEL CAPAIAN HASIL BELAJAR ASTS -->
    <div class="overflow-x-auto" style="margin-bottom: 6px;">
      <table class="w-full text-left border-collapse border border-slate-900 text-[10px]" style="width: 100%; border-collapse: collapse; border: 1px solid #0f172a;">
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
          <tr class="bg-slate-100 font-black text-slate-950 text-[10px]">
            <td colspan="6" class="border border-slate-900 px-2 py-0.5 uppercase tracking-wide">
              Kelompok Wajib A
            </td>
          </tr>

          <!-- PAI Header Item 1 -->
          <tr v-if="report?.subjects_pai && report.subjects_pai.length" class="bg-slate-50 font-bold text-slate-900 text-[10px]">
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
          <tr v-if="report?.subjects_group_b && report.subjects_group_b.length" class="bg-slate-100 font-black text-slate-950 text-[10px]">
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
          <tr v-if="report?.subjects_mulok && report.subjects_mulok.length" class="bg-slate-100 font-black text-slate-950 text-[10px]">
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

    <!-- 5. KETIDAKHADIRAN & CATATAN WALI KELAS (TABLE FORMAT) -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 6px;">
      <tbody>
        <tr>
          <!-- Left: Kehadiran (32%) -->
          <td style="width: 32%; vertical-align: top; padding-right: 6px;">
            <table style="width: 100%; font-size: 10px; border-collapse: collapse; border: 1px solid #0f172a;">
              <thead>
                <tr style="background-color: #f1f5f9; text-align: center; font-weight: 900; text-transform: uppercase; font-size: 9px; border-bottom: 1px solid #0f172a;">
                  <th colspan="2" style="padding: 3px; border: 1px solid #0f172a;">
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 4px;">
                      <span>Rekapitulasi Kehadiran</span>
                      <button
                        v-if="allowEdit"
                        type="button"
                        @click="$emit('edit-notes')"
                        class="no-print"
                        style="color: #4338ca; font-weight: bold; font-size: 8.5px; cursor: pointer; border: none; background: transparent;"
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
                  <td style="padding: 2.5px 4px; border: 1px solid #94a3b8; font-weight: 500;">1. Sakit (S)</td>
                  <td style="padding: 2.5px 4px; border: 1px solid #94a3b8; text-align: center; font-family: monospace; font-weight: bold;">{{ report?.attendance?.sick || 0 }} hari</td>
                </tr>
                <tr>
                  <td style="padding: 2.5px 4px; border: 1px solid #94a3b8; font-weight: 500;">2. Izin (I)</td>
                  <td style="padding: 2.5px 4px; border: 1px solid #94a3b8; text-align: center; font-family: monospace; font-weight: bold;">{{ report?.attendance?.permission || 0 }} hari</td>
                </tr>
                <tr>
                  <td style="padding: 2.5px 4px; border: 1px solid #94a3b8; font-weight: 500;">3. Tanpa Keterangan (A)</td>
                  <td style="padding: 2.5px 4px; border: 1px solid #94a3b8; text-align: center; font-family: monospace; font-weight: bold;">{{ report?.attendance?.unexcused || 0 }} hari</td>
                </tr>
              </tbody>
            </table>
          </td>

          <!-- Right: Catatan Wali Kelas (68%) -->
          <td style="width: 68%; vertical-align: top; padding-left: 6px;">
            <div style="border: 1px solid #0f172a; border-radius: 4px; padding: 5px 8px; min-height: 80px; display: flex; flex-direction: column; justify-content: space-between;">
              <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 3px; margin-bottom: 3px;">
                <span style="font-weight: 800; text-transform: uppercase; font-size: 9px; color: #1e293b; letter-spacing: 0.03em;">
                  Catatan & Motivasi Perkembangan Belajar Wali Kelas:
                </span>
                <button
                  v-if="allowEdit"
                  type="button"
                  @click="$emit('edit-notes')"
                  class="no-print"
                  style="padding: 2px 6px; border-radius: 4px; background-color: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 8.5px; font-weight: bold; cursor: pointer;"
                  title="Edit kehadiran dan catatan motivasi wali kelas untuk siswa ini"
                >
                  ✏️ Edit Catatan & Absensi
                </button>
              </div>
              <p style="font-size: 9.5px; color: #1e293b; font-style: italic; line-height: 1.35; margin: 0; flex: 1;">
                "{{ report?.homeroom_notes || 'Tingkatkan terus ketekunan belajar, kedisiplinan beribadah, dan keaktifan di madrasah.' }}"
              </p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- 6. TITIMANGSA & BLOK TANDA TANGAN RESMI -->
    <div style="padding-top: 4px; page-break-inside: avoid; break-inside: avoid;">
      <div style="text-align: right; font-size: 10px; font-weight: 700; color: #1e293b; padding-right: 8px; margin-bottom: 4px;">
        <span>{{ report?.city || 'Bogor' }}, {{ report?.issued_date || '........................' }}</span>
        <button
          v-if="allowEdit"
          type="button"
          @click="$emit('edit-titimangsa')"
          class="no-print"
          style="color: #b45309; font-weight: bold; font-size: 8.5px; cursor: pointer; border: none; background: transparent; margin-left: 6px;"
          title="Ubah Tempat dan Tanggal Titimangsa Rapor"
        >
          ✏️ Edit Titimangsa
        </button>
      </div>

      <table style="width: 100%; text-align: center; font-size: 10px; border-collapse: collapse;">
        <tbody>
          <tr>
            <!-- Orang Tua -->
            <td style="width: 33.33%; vertical-align: top; padding: 0 8px;">
              <div>
                <p style="font-weight: 700; color: #334155; margin: 0;">Mengetahui,</p>
                <p style="font-weight: 700; color: #0f172a; margin: 0;">Orang Tua / Wali Siswa</p>
              </div>
              <div style="height: 48px;"></div>
              <div style="border-bottom: 1px solid #0f172a; width: 80%; margin: 0 auto;"></div>
            </td>

            <!-- Wali Kelas -->
            <td style="width: 33.33%; vertical-align: top; padding: 0 8px;">
              <div>
                <p style="font-weight: 700; color: #334155; margin: 0;">Wali Kelas,</p>
                <p style="font-weight: 700; color: #0f172a; margin: 0;">Kelas {{ report?.student?.class_name || '' }}</p>
              </div>
              <div style="height: 48px;"></div>
              <div>
                <p style="font-weight: 900; color: #020617; text-decoration: underline; margin: 0;">{{ report?.student?.homeroom_teacher_name || '............................................' }}</p>
                <p style="font-size: 9px; color: #475569; font-family: monospace; margin: 2px 0 0 0;">NIP. {{ report?.student?.homeroom_teacher_nip || '-' }}</p>
              </div>
            </td>

            <!-- Kepala Madrasah -->
            <td style="width: 33.33%; vertical-align: top; padding: 0 8px;">
              <div>
                <p style="font-weight: 700; color: #334155; margin: 0;">Mengetahui,</p>
                <p style="font-weight: 700; color: #0f172a; margin: 0;">Kepala Madrasah</p>
              </div>
              <div style="height: 48px;"></div>
              <div>
                <p style="font-weight: 900; color: #020617; text-decoration: underline; margin: 0;">{{ report?.school_setting?.principal_name || '............................................' }}</p>
                <p style="font-size: 9px; color: #475569; font-family: monospace; margin: 2px 0 0 0;">NIP. {{ report?.school_setting?.principal_nip || '-' }}</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
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
  if (typeof window !== 'undefined' && window.location?.origin) {
    return `${window.location.origin}/storage/${clean}`;
  }
  return `/storage/${clean}`;
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }

@media print {
  .no-print {
    display: none !important;
  }
}
</style>
