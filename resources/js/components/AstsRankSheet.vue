<template>
  <div class="text-slate-900 font-inter text-[10px] leading-tight max-w-full print:leading-tight">
    <!-- 1. KOP SURAT RESMI MADRASAH (TABLE-BASED: 100% BULLETPROOF & DEAD-CENTERED) -->
    <table class="w-full border-collapse" style="width: 100%; border-collapse: collapse; margin-bottom: 2px;">
      <tbody>
        <tr>
          <!-- Left: Official Logo Box -->
          <td style="width: 72px; min-width: 72px; max-width: 72px; vertical-align: middle; text-align: left; padding: 0 4px 4px 0;">
            <img
              v-if="schoolSetting?.logo_url"
              :src="getImageUrl(schoolSetting.logo_url)"
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
              {{ schoolSetting?.foundation_name || 'YAYASAN PENDIDIKAN ISLAM AL - HASANAH' }}
            </div>
            <div style="font-size: 15.5px; font-weight: 900; text-transform: uppercase; color: #020617; font-family: 'Lexend', system-ui, sans-serif; letter-spacing: 0.02em; margin-top: 2px; line-height: 1.2;">
              {{ schoolSetting?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
            </div>
            <div style="font-size: 9.5px; color: #334155; font-weight: 500; margin-top: 2px; line-height: 1.2;">
              {{ schoolSetting?.address || 'Jl. Ciapus Sukamakmur No.05, Kec. Ciomas, Kab. Bogor' }}
            </div>
            <div style="font-size: 8.5px; color: #475569; font-family: monospace; margin-top: 2px; line-height: 1.2;">
              Telp: {{ schoolSetting?.phone || '081617666017' }} &bull; Email: {{ schoolSetting?.email || 'mtsalhasanah.ciomas@gmail.com' }}
            </div>
          </td>

          <!-- Right: Exact Symmetrical Spacer (keeps center td dead-center) -->
          <td style="width: 72px; min-width: 72px; max-width: 72px; vertical-align: middle; padding: 0;"></td>
        </tr>
      </tbody>
    </table>

    <!-- Garis Ganda Resmi Kop Surat (Double Border Kop) -->
    <div style="border-top: 2.5px solid #0f172a; border-bottom: 1px solid #0f172a; height: 3.5px; margin-bottom: 6px; width: 100%;"></div>

    <!-- 2. JUDUL LEMBAR DAFTAR PERINGKAT KELAS -->
    <div style="text-align: center; margin-bottom: 6px;">
      <div style="font-size: 13px; font-weight: 900; text-transform: uppercase; color: #020617; font-family: 'Lexend', system-ui, sans-serif; letter-spacing: 0.04em; line-height: 1.3;">
        <span style="text-decoration: underline; text-underline-offset: 2.5px;">DAFTAR PERINGKAT CAPAIAN BELAJAR SISWA</span>
      </div>
      <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #0f172a; letter-spacing: 0.03em; line-height: 1.3; margin-top: 1px;">
        ASESMEN SUMATIF TENGAH SEMESTER (ASTS) &bull; {{ rankType === 'original' ? 'NILAI ASLI / MURNI' : 'HASIL PENYESUAIAN WALI KELAS' }}
      </div>
      <p style="font-size: 9.5px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.03em; margin: 3px 0 0 0;">
        SEMESTER {{ (semester === 'genap' ? 'GENAP' : 'GANJIL') }} &bull; TAHUN PELAJARAN {{ academicYear?.year || '2026/2027' }}
      </p>
    </div>

    <!-- 3. INFORMASI KELAS & WALI KELAS -->
    <table style="width: 100%; border: 1px solid #94a3b8; border-radius: 6px; background-color: #f8fafc; margin-bottom: 6px; border-collapse: collapse; font-size: 9.5px; font-weight: 500;">
      <tbody>
        <tr>
          <td style="padding: 4px 8px; width: 50%; vertical-align: top;">
            <table style="width: 100%;">
              <tr>
                <td style="width: 110px; font-weight: 700; color: #475569; padding: 1px 0;">Kelas / Rombel</td>
                <td style="font-weight: 900; color: #020617; padding: 1px 0;">: {{ formatClassName(classInfo?.name) }}</td>
              </tr>
              <tr>
                <td style="font-weight: 700; color: #475569; padding: 1px 0;">Tingkat / Fase</td>
                <td style="font-weight: 700; color: #1e293b; padding: 1px 0;">: Tingkat {{ classInfo?.grade_level || '7' }} / Fase D</td>
              </tr>
              <tr>
                <td style="font-weight: 700; color: #475569; padding: 1px 0;">Total Siswa</td>
                <td style="font-weight: 700; color: #1e293b; padding: 1px 0;">: {{ sortedStudents.length }} Peserta Didik</td>
              </tr>
            </table>
          </td>
          <td style="padding: 4px 8px; width: 50%; vertical-align: top; border-left: 1px solid #cbd5e1;">
            <table style="width: 100%;">
              <tr>
                <td style="width: 110px; font-weight: 700; color: #475569; padding: 1px 0;">Wali Kelas</td>
                <td style="font-weight: 900; color: #020617; padding: 1px 0;">: {{ classInfo?.homeroom_teacher?.full_name || classInfo?.homeroom_teacher_name || '-' }}</td>
              </tr>
              <tr>
                <td style="font-weight: 700; color: #475569; padding: 1px 0;">NIP / Peg. ID</td>
                <td style="font-weight: 700; color: #1e293b; padding: 1px 0;">: {{ classInfo?.homeroom_teacher?.nip || '-' }}</td>
              </tr>
              <tr>
                <td style="font-weight: 700; color: #475569; padding: 1px 0;">Rata-Rata Kelas</td>
                <td style="font-weight: 900; color: #065f46; padding: 1px 0;">: {{ Number(classAverageScore || 0).toFixed(2) }}</td>
              </tr>
            </table>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- 4. TABEL DAFTAR PERINGKAT KELAS -->
    <div style="margin-bottom: 6px; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #0f172a; font-size: 9px; line-height: 1.2;">
        <thead>
          <tr style="background-color: #f1f5f9; color: #020617; font-weight: 900; text-transform: uppercase; text-align: center; border-bottom: 1.5px solid #0f172a;">
            <th style="border: 1px solid #0f172a; padding: 4px 2px; width: 8%;">Peringkat</th>
            <th style="border: 1px solid #0f172a; padding: 4px 4px; width: 14%;">NISN / NIS</th>
            <th style="border: 1px solid #0f172a; padding: 4px 6px; text-align: left; width: 34%;">Nama Lengkap Siswa</th>
            <th style="border: 1px solid #0f172a; padding: 4px 2px; width: 6%; text-align: center;">L/P</th>
            <th style="border: 1px solid #0f172a; padding: 4px 4px; width: 12%; text-align: center;">Jumlah Nilai</th>
            <th style="border: 1px solid #0f172a; padding: 4px 4px; width: 12%; text-align: center;">Rata-Rata</th>
            <th style="border: 1px solid #0f172a; padding: 4px 6px; text-align: center; width: 14%;">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(st, sIdx) in sortedStudents"
            :key="'rank-row-'+st.student_id"
            :style="{ backgroundColor: sIdx < 3 ? '#fffbeb' : (sIdx % 2 === 1 ? '#f8fafc' : '#ffffff') }"
            style="border-bottom: 1px solid #cbd5e1;"
          >
            <!-- Kolom Peringkat -->
            <td style="border: 1px solid #94a3b8; padding: 3px 2px; text-align: center; font-weight: 900; font-family: monospace; font-size: 10px;" :style="{ color: sIdx < 3 ? '#b45309' : '#020617' }">
              <span v-if="sIdx === 0">🥇 1</span>
              <span v-else-if="sIdx === 1">🥈 2</span>
              <span v-else-if="sIdx === 2">🥉 3</span>
              <span v-else>{{ st.display_rank }}</span>
            </td>

            <!-- Kolom NISN / NIS -->
            <td style="border: 1px solid #94a3b8; padding: 3px 4px; text-align: center; font-family: monospace; color: #475569;">
              {{ st.nisn || st.nis || '-' }}
            </td>

            <!-- Kolom Nama Siswa -->
            <td style="border: 1px solid #94a3b8; padding: 3px 6px; font-weight: 700; color: #0f172a;">
              {{ st.full_name }}
            </td>

            <!-- Kolom L/P -->
            <td style="border: 1px solid #94a3b8; padding: 3px 2px; text-align: center; font-weight: bold; color: #475569;">
              {{ st.gender === 'L' ? 'L' : (st.gender === 'P' ? 'P' : '-') }}
            </td>

            <!-- Kolom Jumlah Nilai -->
            <td style="border: 1px solid #94a3b8; padding: 3px 4px; text-align: center; font-family: monospace; font-weight: 900; color: #0f172a;">
              {{ Math.round(Number(st.total_score) || 0) }}
            </td>

            <!-- Kolom Rata-Rata -->
            <td style="border: 1px solid #94a3b8; padding: 3px 4px; text-align: center; font-family: monospace; font-weight: 900; color: #065f46; font-size: 9.5px;">
              {{ Number(st.average_score || 0).toFixed(2) }}
            </td>

            <!-- Kolom Keterangan / Capaian -->
            <td style="border: 1px solid #94a3b8; padding: 3px 4px; text-align: center; font-size: 8.5px; font-weight: 700;">
              <span v-if="sIdx === 0" style="color: #b45309;">Terbaik I</span>
              <span v-else-if="sIdx === 1" style="color: #b45309;">Terbaik II</span>
              <span v-else-if="sIdx === 2" style="color: #b45309;">Terbaik III</span>
              <span v-else-if="Number(st.average_score) >= 80" style="color: #047857;">Sangat Baik</span>
              <span v-else-if="Number(st.average_score) >= 75" style="color: #0f766e;">Baik</span>
              <span v-else style="color: #475569;">Perlu Bimbingan</span>
            </td>
          </tr>

          <tr v-if="sortedStudents.length === 0">
            <td colspan="7" style="border: 1px solid #94a3b8; padding: 12px; text-align: center; color: #94a3b8; font-style: italic;">
              Tidak ada data peserta didik pada kelas ini.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 5. TITIMANGSA & TANDA TANGAN RESMI -->
    <div style="padding-top: 4px; page-break-inside: avoid; break-inside: avoid;">
      <div style="text-align: right; font-size: 9.5px; font-weight: 700; color: #1e293b; padding-right: 8px; margin-bottom: 4px;">
        <span>{{ city || 'Bogor' }}, {{ issuedDate || '........................' }}</span>
      </div>

      <table style="width: 100%; text-align: center; font-size: 9.5px; border-collapse: collapse;">
        <tbody>
          <tr>
            <!-- Wali Kelas -->
            <td style="width: 50%; vertical-align: top; padding: 0 16px;">
              <div>
                <p style="font-weight: 700; color: #334155; margin: 0;">Wali Kelas,</p>
                <p style="font-weight: 700; color: #0f172a; margin: 0;">{{ formatClassName(classInfo?.name) }}</p>
              </div>
              <div style="height: 38px;"></div>
              <div>
                <p style="font-weight: 900; color: #020617; text-decoration: underline; margin: 0;">
                  {{ classInfo?.homeroom_teacher?.full_name || classInfo?.homeroom_teacher_name || '............................................' }}
                </p>
                <p style="font-size: 8.5px; color: #475569; margin: 2px 0 0 0; font-family: monospace;">
                  NIP: {{ classInfo?.homeroom_teacher?.nip || '-' }}
                </p>
              </div>
            </td>

            <!-- Kepala Madrasah -->
            <td style="width: 50%; vertical-align: top; padding: 0 16px;">
              <div>
                <p style="font-weight: 700; color: #334155; margin: 0;">Mengetahui,</p>
                <p style="font-weight: 700; color: #0f172a; margin: 0;">Kepala Madrasah</p>
              </div>
              <div style="height: 38px;"></div>
              <div>
                <p style="font-weight: 900; color: #020617; text-decoration: underline; margin: 0;">
                  {{ schoolSetting?.principal_name || 'H. Umar Usman Ali, S.Pd, S.Pd.I' }}
                </p>
                <p style="font-size: 8.5px; color: #475569; margin: 2px 0 0 0; font-family: monospace;">
                  NIP: {{ schoolSetting?.principal_nip || '-' }}
                </p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  students: {
    type: Array,
    default: () => [],
  },
  classInfo: {
    type: Object,
    default: () => ({}),
  },
  academicYear: {
    type: Object,
    default: () => ({}),
  },
  semester: {
    type: String,
    default: 'ganjil',
  },
  schoolSetting: {
    type: Object,
    default: () => ({}),
  },
  city: {
    type: String,
    default: 'Bogor',
  },
  issuedDate: {
    type: String,
    default: '',
  },
  rankType: {
    type: String,
    default: 'adjusted', // 'adjusted' | 'original'
  },
});

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
  const clean = path.replace(/^\/?storage\//, '').replace(/^\//, '');
  if (typeof window !== 'undefined' && window.location?.origin) {
    return `${window.location.origin}/storage/${clean}`;
  }
  return `/storage/${clean}`;
}

function formatClassName(name) {
  if (!name) return 'Kelas -';
  return name.toLowerCase().startsWith('kelas') ? name : `Kelas ${name}`;
}

const sortedStudents = computed(() => {
  const list = [...props.students];

  if (props.rankType === 'original') {
    // Sort strictly by average_score DESC, then total_score DESC, then name
    list.sort((a, b) => {
      const avgA = Number(a.average_score) || 0;
      const avgB = Number(b.average_score) || 0;
      if (avgB !== avgA) return avgB - avgA;
      const totA = Number(a.total_score) || 0;
      const totB = Number(b.total_score) || 0;
      if (totB !== totA) return totB - totA;
      return (a.full_name || '').localeCompare(b.full_name || '');
    });

    return list.map((st, idx) => ({
      ...st,
      display_rank: idx + 1,
    }));
  }

  // Adjusted mode: sort by st.rank (if number), otherwise by average
  list.sort((a, b) => {
    const rankA = typeof a.rank === 'number' ? a.rank : 9999;
    const rankB = typeof b.rank === 'number' ? b.rank : 9999;
    if (rankA !== rankB) return rankA - rankB;
    const avgA = Number(a.average_score) || 0;
    const avgB = Number(b.average_score) || 0;
    if (avgB !== avgA) return avgB - avgA;
    const totA = Number(a.total_score) || 0;
    const totB = Number(b.total_score) || 0;
    if (totB !== totA) return totB - totA;
    return (a.full_name || '').localeCompare(b.full_name || '');
  });

  // Always sequentially number ranks 1 to N without any missing/skipped numbers
  return list.map((st, idx) => ({
    ...st,
    display_rank: idx + 1,
    raw_manual_rank: st.rank,
  }));
});

const classAverageScore = computed(() => {
  if (!props.students || props.students.length === 0) return 0;
  const total = props.students.reduce((acc, s) => acc + (Number(s.average_score) || 0), 0);
  return total / props.students.length;
});
</script>
