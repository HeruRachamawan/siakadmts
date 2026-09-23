<template>
  <div class="text-slate-900 font-inter text-[9px] leading-tight max-w-full print:leading-tight">
    <!-- 1. KOP SURAT RESMI MADRASAH (TABLE-BASED) -->
    <table class="w-full border-collapse" style="width: 100%; border-collapse: collapse; margin-bottom: 2px;">
      <tbody>
        <tr>
          <!-- Left: Official Logo Box -->
          <td style="width: 65px; min-width: 65px; max-width: 65px; vertical-align: middle; text-align: left; padding: 0 4px 4px 0;">
            <img
              v-if="schoolSetting?.logo_url"
              :src="getImageUrl(schoolSetting.logo_url)"
              style="width: 60px; height: 60px; max-width: 60px; max-height: 60px; object-fit: contain; display: block;"
              alt="Logo Madrasah"
            />
            <div v-else style="width: 55px; height: 55px; border-radius: 8px; background-color: #065f46; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; font-family: serif;">
              MTS
            </div>
          </td>

          <!-- Center: School Information -->
          <td style="text-align: center; vertical-align: middle; padding: 0 8px 4px 8px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #1e293b; line-height: 1.2;">
              {{ schoolSetting?.foundation_name || 'YAYASAN PENDIDIKAN ISLAM AL - HASANAH' }}
            </div>
            <div style="font-size: 15px; font-weight: 900; text-transform: uppercase; color: #020617; font-family: 'Lexend', system-ui, sans-serif; letter-spacing: 0.02em; margin-top: 1px; line-height: 1.2;">
              {{ schoolSetting?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
            </div>
            <div style="font-size: 9px; color: #334155; font-weight: 500; margin-top: 1px; line-height: 1.2;">
              {{ schoolSetting?.address || 'Jl. Ciapus Sukamakmur No.05, Kec. Ciomas, Kab. Bogor' }}
            </div>
            <div style="font-size: 8px; color: #475569; font-family: monospace; margin-top: 1px; line-height: 1.2;">
              Telp: {{ schoolSetting?.phone || '081617666017' }} &bull; Email: {{ schoolSetting?.email || 'mtsalhasanah.ciomas@gmail.com' }}
            </div>
          </td>

          <!-- Right: Exact Symmetrical Spacer (keeps center td dead-center) -->
          <td style="width: 65px; min-width: 65px; max-width: 65px; vertical-align: middle; padding: 0;"></td>
        </tr>
      </tbody>
    </table>

    <!-- Garis Ganda Resmi Kop Surat -->
    <div style="border-top: 2px solid #0f172a; border-bottom: 1px solid #0f172a; height: 3px; margin-bottom: 4px; width: 100%;"></div>

    <!-- 2. JUDUL LEMBAR LEDGER NILAI KELAS -->
    <div style="text-align: center; margin-bottom: 5px;">
      <div style="font-size: 12.5px; font-weight: 900; text-transform: uppercase; color: #020617; font-family: 'Lexend', system-ui, sans-serif; letter-spacing: 0.04em; line-height: 1.2;">
        <span style="text-decoration: underline; text-underline-offset: 2px;">LEDGER CAPAIAN NILAI SISWA (REKAP KELAS)</span>
      </div>
      <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #0f172a; letter-spacing: 0.03em; line-height: 1.2; margin-top: 1px;">
        ASESMEN SUMATIF TENGAH SEMESTER (ASTS) &bull; {{ rankType === 'original' ? 'NILAI ASLI / MURNI' : 'HASIL PENYESUAIAN WALI KELAS' }}
      </div>
      <p style="font-size: 8.5px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.03em; margin: 2px 0 0 0;">
        SEMESTER {{ (semester === 'genap' ? 'GENAP' : 'GANJIL') }} &bull; TAHUN PELAJARAN {{ academicYear?.year || '2026/2027' }}
      </p>
    </div>

    <!-- 3. INFORMASI KELAS & WALI KELAS (HEADER STRIP) -->
    <table style="width: 100%; border: 1px solid #94a3b8; border-radius: 4px; background-color: #f8fafc; margin-bottom: 5px; border-collapse: collapse; font-size: 8.5px; font-weight: 500;">
      <tbody>
        <tr>
          <td style="padding: 3px 8px; width: 33.33%; vertical-align: top;">
            <div><strong style="color: #475569;">Kelas / Rombel:</strong> <span style="font-weight: 900; color: #020617;">{{ formatClassName(classInfo?.name) }}</span></div>
            <div><strong style="color: #475569;">Tingkat / Fase:</strong> Tingkat {{ classInfo?.grade_level || '7' }} / Fase D</div>
          </td>
          <td style="padding: 3px 8px; width: 33.33%; vertical-align: top; border-left: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1;">
            <div><strong style="color: #475569;">Wali Kelas:</strong> <span style="font-weight: 800; color: #020617;">{{ classInfo?.homeroom_teacher?.full_name || classInfo?.homeroom_teacher_name || '-' }}</span></div>
            <div><strong style="color: #475569;">NIP / ID:</strong> {{ classInfo?.homeroom_teacher?.nip || '-' }}</div>
          </td>
          <td style="padding: 3px 8px; width: 33.33%; vertical-align: top;">
            <div><strong style="color: #475569;">Total Siswa:</strong> <span style="font-weight: 800;">{{ sortedStudents.length }} Peserta Didik</span></div>
            <div><strong style="color: #475569;">Rata-Rata Kelas:</strong> <span style="font-weight: 900; color: #065f46;">{{ Number(classAverageScore || 0).toFixed(2) }}</span></div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- 4. TABEL MATRIKS LEDGER NILAI LENGKAP -->
    <div style="margin-bottom: 4px; width: 100%; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #0f172a; font-size: 8px; line-height: 1.15; table-layout: auto;">
        <thead>
          <tr style="background-color: #f1f5f9; color: #020617; font-weight: 900; text-transform: uppercase; text-align: center; border-bottom: 1.5px solid #0f172a;">
            <th style="border: 1px solid #0f172a; padding: 3px 1px; width: 22px;">No</th>
            <th style="border: 1px solid #0f172a; padding: 3px 2px; width: 62px;">NISN</th>
            <th style="border: 1px solid #0f172a; padding: 3px 4px; text-align: left; min-width: 130px;">Nama Lengkap Siswa</th>
            <th style="border: 1px solid #0f172a; padding: 3px 1px; width: 18px; text-align: center;">L/P</th>

            <!-- Kolom Seluruh Mata Pelajaran -->
            <th
              v-for="sbj in subjects"
              :key="'ledger-th-'+sbj.id"
              style="border: 1px solid #0f172a; padding: 3px 1px; text-align: center; min-width: 28px;"
            >
              <div style="font-weight: 900; font-size: 8px;">{{ sbj.code || getShortCode(sbj.name) }}</div>
              <div style="font-size: 6.5px; font-weight: normal; color: #475569;">{{ sbj.passing_grade || 75 }}</div>
            </th>

            <th style="border: 1px solid #0f172a; padding: 3px 2px; width: 34px; text-align: center; background-color: #f8fafc;">Total</th>
            <th style="border: 1px solid #0f172a; padding: 3px 2px; width: 34px; text-align: center; background-color: #ecfdf5;">Rata</th>
            <th style="border: 1px solid #0f172a; padding: 3px 2px; width: 28px; text-align: center; background-color: #fffbeb;">Pkt</th>
            
            <!-- Kehadiran -->
            <th style="border: 1px solid #0f172a; padding: 3px 1px; width: 14px; text-align: center;" title="Sakit">S</th>
            <th style="border: 1px solid #0f172a; padding: 3px 1px; width: 14px; text-align: center;" title="Izin">I</th>
            <th style="border: 1px solid #0f172a; padding: 3px 1px; width: 14px; text-align: center;" title="Alpa">A</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(st, sIdx) in sortedStudents"
            :key="'ledger-row-'+st.student_id"
            :style="{ backgroundColor: sIdx < 3 ? '#fffdf5' : (sIdx % 2 === 1 ? '#f8fafc' : '#ffffff') }"
            style="border-bottom: 1px solid #cbd5e1;"
          >
            <!-- No -->
            <td style="border: 1px solid #94a3b8; padding: 1.5px 1px; text-align: center; font-weight: bold; color: #475569;">
              {{ sIdx + 1 }}
            </td>

            <!-- NISN -->
            <td style="border: 1px solid #94a3b8; padding: 1.5px 2px; text-align: center; font-family: monospace; font-size: 7.5px; color: #475569;">
              {{ st.nisn || st.nis || '-' }}
            </td>

            <!-- Nama Siswa -->
            <td style="border: 1px solid #94a3b8; padding: 1.5px 4px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
              {{ st.full_name }}
            </td>

            <!-- L/P -->
            <td style="border: 1px solid #94a3b8; padding: 1.5px 1px; text-align: center; font-weight: bold; color: #475569; font-size: 7.5px;">
              {{ st.gender === 'L' ? 'L' : (st.gender === 'P' ? 'P' : '-') }}
            </td>

            <!-- Nilai per Mapel -->
            <td
              v-for="sbj in subjects"
              :key="'ledger-cell-'+st.student_id+'-'+sbj.id"
              style="border: 1px solid #94a3b8; padding: 1.5px 1px; text-align: center; font-family: monospace; font-size: 8px;"
              :style="{
                fontWeight: st.scores?.[sbj.id] ? 'bold' : 'normal',
                color: st.scores?.[sbj.id] ? (Number(st.scores[sbj.id].score) < (sbj.passing_grade || 75) ? '#b91c1c' : '#0f172a') : '#94a3b8',
                backgroundColor: st.scores?.[sbj.id] && Number(st.scores[sbj.id].score) < (sbj.passing_grade || 75) ? '#fef2f2' : 'inherit'
              }"
            >
              {{ st.scores?.[sbj.id]?.score !== undefined && st.scores?.[sbj.id]?.score !== null ? Math.round(Number(st.scores[sbj.id].score)) : '-' }}
            </td>

            <!-- Total -->
            <td style="border: 1px solid #94a3b8; padding: 1.5px 2px; text-align: center; font-family: monospace; font-weight: 900; color: #0f172a; background-color: #f8fafc;">
              {{ Math.round(Number(st.total_score) || 0) }}
            </td>

            <!-- Rata-rata -->
            <td style="border: 1px solid #94a3b8; padding: 1.5px 2px; text-align: center; font-family: monospace; font-weight: 900; color: #065f46; background-color: #ecfdf5;">
              {{ Number(st.average_score || 0).toFixed(1) }}
            </td>

            <!-- Peringkat -->
            <td
              style="border: 1px solid #94a3b8; padding: 1.5px 2px; text-align: center; font-family: monospace; font-weight: 900; font-size: 8.5px;"
              :style="{
                color: sIdx < 3 ? '#b45309' : '#020617',
                backgroundColor: sIdx < 3 ? '#fef3c7' : 'inherit'
              }"
            >
              {{ st.display_rank }}
            </td>

            <!-- Kehadiran S / I / A -->
            <td style="border: 1px solid #94a3b8; padding: 1.5px 1px; text-align: center; font-family: monospace; font-size: 7.5px;">
              {{ st.sick_count || 0 }}
            </td>
            <td style="border: 1px solid #94a3b8; padding: 1.5px 1px; text-align: center; font-family: monospace; font-size: 7.5px;">
              {{ st.permission_count || 0 }}
            </td>
            <td style="border: 1px solid #94a3b8; padding: 1.5px 1px; text-align: center; font-family: monospace; font-size: 7.5px;" :style="{ color: (st.unexcused_count || 0) > 0 ? '#dc2626' : 'inherit' }">
              {{ st.unexcused_count || 0 }}
            </td>
          </tr>

          <tr v-if="sortedStudents.length === 0">
            <td :colspan="8 + subjects.length" style="border: 1px solid #94a3b8; padding: 10px; text-align: center; color: #94a3b8; font-style: italic;">
              Tidak ada data peserta didik pada kelas ini.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 5. TITIMANGSA & TANDA TANGAN RESMI -->
    <div style="padding-top: 3px; page-break-inside: avoid; break-inside: avoid;">
      <div style="text-align: right; font-size: 8.5px; font-weight: 700; color: #1e293b; padding-right: 12px; margin-bottom: 2px;">
        <span>{{ city || 'Bogor' }}, {{ issuedDate || '........................' }}</span>
      </div>

      <table style="width: 100%; text-align: center; font-size: 8.5px; border-collapse: collapse;">
        <tbody>
          <tr>
            <!-- Wali Kelas -->
            <td style="width: 50%; vertical-align: top; padding: 0 24px;">
              <div>
                <p style="font-weight: 700; color: #334155; margin: 0;">Wali Kelas,</p>
                <p style="font-weight: 700; color: #0f172a; margin: 0;">{{ formatClassName(classInfo?.name) }}</p>
              </div>
              <div style="height: 28px;"></div>
              <div>
                <p style="font-weight: 900; color: #020617; text-decoration: underline; margin: 0;">
                  {{ classInfo?.homeroom_teacher?.full_name || classInfo?.homeroom_teacher_name || '............................................' }}
                </p>
                <p style="font-size: 7.5px; color: #475569; margin: 1px 0 0 0; font-family: monospace;">
                  NIP: {{ classInfo?.homeroom_teacher?.nip || '-' }}
                </p>
              </div>
            </td>

            <!-- Kepala Madrasah -->
            <td style="width: 50%; vertical-align: top; padding: 0 24px;">
              <div>
                <p style="font-weight: 700; color: #334155; margin: 0;">Mengetahui,</p>
                <p style="font-weight: 700; color: #0f172a; margin: 0;">Kepala Madrasah</p>
              </div>
              <div style="height: 28px;"></div>
              <div>
                <p style="font-weight: 900; color: #020617; text-decoration: underline; margin: 0;">
                  {{ schoolSetting?.principal_name || 'H. Umar Usman Ali, S.Pd, S.Pd.I' }}
                </p>
                <p style="font-size: 7.5px; color: #475569; margin: 1px 0 0 0; font-family: monospace;">
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
  subjects: {
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

function getShortCode(name) {
  if (!name) return '-';
  const clean = name.replace(/\(.*\)/g, '').trim();
  const words = clean.split(/\s+/);
  if (words.length === 1) return words[0].slice(0, 4).toUpperCase();
  return words.map(w => w[0]).join('').slice(0, 4).toUpperCase();
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

  return list.map((st, idx) => ({
    ...st,
    display_rank: idx + 1,
  }));
});

const classAverageScore = computed(() => {
  if (!props.students || props.students.length === 0) return 0;
  const total = props.students.reduce((acc, s) => acc + (Number(s.average_score) || 0), 0);
  return total / props.students.length;
});
</script>
