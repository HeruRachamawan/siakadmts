<template>
  <div
    class="text-slate-900 font-inter leading-tight max-w-full print:leading-tight"
    :style="{ fontSize: baseFontSize, boxSizing: 'border-box' }"
  >
    <!-- 1. KOP SURAT RESMI MADRASAH (TABLE-BASED) -->
    <table class="w-full border-collapse" :style="{ width: '100%', borderCollapse: 'collapse', marginBottom: kopMarginBottom }">
      <tbody>
        <tr>
          <!-- Left: Official Logo Box -->
          <td :style="{ width: `${logoTdWidth}px`, minWidth: `${logoTdWidth}px`, maxWidth: `${logoTdWidth}px`, verticalAlign: 'middle', textAlign: 'left', padding: '0 4px 2px 0' }">
            <img
              v-if="schoolSetting?.logo_url"
              :src="getImageUrl(schoolSetting.logo_url)"
              :style="{ width: `${logoSize}px`, height: `${logoSize}px`, maxWidth: `${logoSize}px`, maxHeight: `${logoSize}px`, objectFit: 'contain', display: 'block' }"
              alt="Logo Madrasah"
            />
            <div
              v-else
              :style="{ width: `${logoSize}px`, height: `${logoSize}px`, borderRadius: '6px', backgroundColor: '#065f46', color: '#ffffff', display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: '900', fontSize: `${logoSize * 0.3}px`, fontFamily: 'serif' }"
            >
              MTS
            </div>
          </td>

          <!-- Center: School Information -->
          <td style="text-align: center; vertical-align: middle; padding: 0 4px 2px 4px;">
            <div :style="{ fontSize: kopFoundationSize, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.04em', color: '#1e293b', lineHeight: '1.15' }">
              {{ schoolSetting?.foundation_name || 'YAYASAN PENDIDIKAN ISLAM AL - HASANAH' }}
            </div>
            <div :style="{ fontSize: kopSchoolSize, fontWeight: '900', textTransform: 'uppercase', color: '#020617', fontFamily: '\'Lexend\', system-ui, sans-serif', letterSpacing: '0.02em', marginTop: '1px', lineHeight: '1.15' }">
              {{ schoolSetting?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
            </div>
            <div :style="{ fontSize: kopAddressSize, color: '#334155', fontWeight: '500', marginTop: '1px', lineHeight: '1.15' }">
              {{ schoolSetting?.address || 'Jl. Ciapus Sukamakmur No.05, Kec. Ciomas, Kab. Bogor' }}
            </div>
            <div :style="{ fontSize: kopPhoneSize, color: '#475569', fontFamily: 'monospace', marginTop: '1px', lineHeight: '1.15' }">
              Telp: {{ schoolSetting?.phone || '081617666017' }} &bull; Email: {{ schoolSetting?.email || 'mtsalhasanah.ciomas@gmail.com' }}
            </div>
          </td>

          <!-- Right: Exact Symmetrical Spacer (keeps center td dead-center) -->
          <td :style="{ width: `${logoTdWidth}px`, minWidth: `${logoTdWidth}px`, maxWidth: `${logoTdWidth}px`, verticalAlign: 'middle', padding: '0' }"></td>
        </tr>
      </tbody>
    </table>

    <!-- Garis Ganda Resmi Kop Surat -->
    <div :style="{ borderTop: '2px solid #0f172a', borderBottom: '1px solid #0f172a', height: '2px', marginBottom: garisGandaMargin, width: '100%' }"></div>

    <!-- 2. JUDUL LEMBAR LEDGER NILAI KELAS -->
    <div :style="{ textAlign: 'center', marginBottom: judulMarginBottom }">
      <div :style="{ fontSize: titleMainSize, fontWeight: '900', textTransform: 'uppercase', color: '#020617', fontFamily: '\'Lexend\', system-ui, sans-serif', letterSpacing: '0.03em', lineHeight: '1.15' }">
        <span style="text-decoration: underline; text-underline-offset: 2px;">LEDGER CAPAIAN NILAI SISWA (REKAP KELAS)</span>
      </div>
      <div :style="{ fontSize: titleSubSize, fontWeight: '800', textTransform: 'uppercase', color: '#0f172a', letterSpacing: '0.02em', lineHeight: '1.15', marginTop: '1px' }">
        ASESMEN SUMATIF TENGAH SEMESTER (ASTS) &bull; {{ rankType === 'original' ? 'PERINGKAT OTOMATIS MURNI' : 'HASIL PENYESUAIAN WALI KELAS' }}
      </div>
      <p :style="{ fontSize: titleYearSize, fontWeight: '700', color: '#334155', textTransform: 'uppercase', letterSpacing: '0.02em', margin: '1px 0 0 0', lineHeight: '1.15' }">
        SEMESTER {{ (semester === 'genap' ? 'GENAP' : 'GANJIL') }} &bull; TAHUN PELAJARAN {{ academicYear?.year || '2026/2027' }}
      </p>
    </div>

    <!-- 3. INFORMASI KELAS & WALI KELAS (HEADER STRIP) -->
    <table :style="{ width: '100%', border: '1px solid #94a3b8', borderRadius: '3px', backgroundColor: '#f8fafc', marginBottom: infoStripMarginBottom, borderCollapse: 'collapse', fontSize: infoStripFontSize, fontWeight: '500' }">
      <tbody>
        <tr>
          <td :style="{ padding: infoStripPadding, width: '33.33%', verticalAlign: 'top' }">
            <div><strong style="color: #475569;">Kelas / Rombel:</strong> <span style="font-weight: 900; color: #020617;">{{ formatClassName(classInfo?.name) }}</span></div>
            <div><strong style="color: #475569;">Tingkat / Fase:</strong> Tingkat {{ classInfo?.grade_level || '7' }} / Fase D</div>
          </td>
          <td :style="{ padding: infoStripPadding, width: '33.33%', verticalAlign: 'top', borderLeft: '1px solid #cbd5e1', borderRight: '1px solid #cbd5e1' }">
            <div><strong style="color: #475569;">Wali Kelas:</strong> <span style="font-weight: 800; color: #020617;">{{ classInfo?.homeroom_teacher?.full_name || classInfo?.homeroom_teacher_name || '-' }}</span></div>
            <div><strong style="color: #475569;">NIP / ID:</strong> {{ classInfo?.homeroom_teacher?.nip || '-' }}</div>
          </td>
          <td :style="{ padding: infoStripPadding, width: '33.33%', verticalAlign: 'top' }">
            <div><strong style="color: #475569;">Total Siswa:</strong> <span style="font-weight: 800;">{{ sortedStudents.length }} Peserta Didik</span></div>
            <div><strong style="color: #475569;">Rata-Rata Kelas:</strong> <span style="font-weight: 900; color: #065f46;">{{ Number(classAverageScore || 0).toFixed(2) }}</span></div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- 4. TABEL MATRIKS LEDGER NILAI LENGKAP -->
    <div style="margin-bottom: 2px; width: 100%;">
      <table
        :style="{
          width: '100%',
          borderCollapse: 'collapse',
          border: '1.5px solid #0f172a',
          fontSize: tableFontSize,
          lineHeight: tableLineHeight,
          tableLayout: 'auto'
        }"
      >
        <thead>
          <tr style="background-color: #f1f5f9; color: #020617; font-weight: 900; text-transform: uppercase; text-align: center; border-bottom: 1.5px solid #0f172a;">
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 1px`, width: `${noColWidth}px` }">No</th>
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 1px`, width: `${nisnColWidth}px` }">NISN</th>
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 3px`, textAlign: 'left', minWidth: `${nameMinWidth}px` }">Nama Lengkap Siswa</th>
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 1px`, width: `${lpColWidth}px`, textAlign: 'center' }">L/P</th>

            <!-- Kolom Seluruh Mata Pelajaran -->
            <th
              v-for="sbj in subjects"
              :key="'ledger-th-'+sbj.id"
              :style="{
                border: '1px solid #0f172a',
                padding: `${thPaddingY} 0.5px`,
                textAlign: 'center',
                minWidth: `${mapelColMinWidth}px`
              }"
            >
              <div :style="{ fontWeight: '900', fontSize: mapelCodeFontSize }">{{ sbj.code || getShortCode(sbj.name) }}</div>
              <div :style="{ fontSize: kktpFontSize, fontWeight: 'normal', color: '#475569' }">{{ sbj.passing_grade || 75 }}</div>
            </th>

            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 1px`, width: `${totalColWidth}px`, textAlign: 'center', backgroundColor: '#f8fafc' }">Total</th>
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 1px`, width: `${rataColWidth}px`, textAlign: 'center', backgroundColor: '#ecfdf5' }">Rata</th>
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 1px`, width: `${pktColWidth}px`, textAlign: 'center', backgroundColor: '#fffbeb' }">Pkt</th>

            <!-- Kehadiran -->
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 0.5px`, width: `${attColWidth}px`, textAlign: 'center' }" title="Sakit">S</th>
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 0.5px`, width: `${attColWidth}px`, textAlign: 'center' }" title="Izin">I</th>
            <th :style="{ border: '1px solid #0f172a', padding: `${thPaddingY} 0.5px`, width: `${attColWidth}px`, textAlign: 'center' }" title="Alpa">A</th>
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
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 1px`, textAlign: 'center', fontWeight: 'bold', color: '#475569' }">
              {{ sIdx + 1 }}
            </td>

            <!-- NISN -->
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 1px`, textAlign: 'center', fontFamily: 'monospace', fontSize: nisnFontSize, color: '#475569' }">
              {{ st.nisn || st.nis || '-' }}
            </td>

            <!-- Nama Siswa -->
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 3px`, fontWeight: '700', color: '#0f172a', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis', maxWidth: `${nameMaxWidth}px`, fontSize: nameFontSize }">
              {{ st.full_name }}
            </td>

            <!-- L/P -->
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 1px`, textAlign: 'center', fontWeight: 'bold', color: '#475569', fontSize: lpFontSize }">
              {{ st.gender === 'L' ? 'L' : (st.gender === 'P' ? 'P' : '-') }}
            </td>

            <!-- Nilai per Mapel -->
            <td
              v-for="sbj in subjects"
              :key="'ledger-cell-'+st.student_id+'-'+sbj.id"
              :style="{
                border: '1px solid #94a3b8',
                padding: `${rowPaddingY} 0.5px`,
                textAlign: 'center',
                fontFamily: 'monospace',
                fontSize: mapelScoreFontSize,
                fontWeight: st.scores?.[sbj.id] ? 'bold' : 'normal',
                color: st.scores?.[sbj.id] ? (Number(st.scores[sbj.id].score) < (sbj.passing_grade || 75) ? '#b91c1c' : '#0f172a') : '#94a3b8',
                backgroundColor: st.scores?.[sbj.id] && Number(st.scores[sbj.id].score) < (sbj.passing_grade || 75) ? '#fef2f2' : 'inherit'
              }"
            >
              {{ st.scores?.[sbj.id]?.score !== undefined && st.scores?.[sbj.id]?.score !== null ? Math.round(Number(st.scores[sbj.id].score)) : '-' }}
            </td>

            <!-- Total -->
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 1px`, textAlign: 'center', fontFamily: 'monospace', fontWeight: '900', color: '#0f172a', backgroundColor: '#f8fafc', fontSize: mapelScoreFontSize }">
              {{ Math.round(Number(st.total_score) || 0) }}
            </td>

            <!-- Rata-rata -->
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 1px`, textAlign: 'center', fontFamily: 'monospace', fontWeight: '900', color: '#065f46', backgroundColor: '#ecfdf5', fontSize: mapelScoreFontSize }">
              {{ Number(st.average_score || 0).toFixed(1) }}
            </td>

            <!-- Peringkat -->
            <td
              :style="{
                border: '1px solid #94a3b8',
                padding: `${rowPaddingY} 1px`,
                textAlign: 'center',
                fontFamily: 'monospace',
                fontWeight: '900',
                fontSize: rankFontSize,
                color: sIdx < 3 ? '#b45309' : '#020617',
                backgroundColor: sIdx < 3 ? '#fef3c7' : 'inherit'
              }"
            >
              {{ st.display_rank }}
            </td>

            <!-- Kehadiran S / I / A -->
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 0.5px`, textAlign: 'center', fontFamily: 'monospace', fontSize: attFontSize }">
              {{ st.sick_count || 0 }}
            </td>
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 0.5px`, textAlign: 'center', fontFamily: 'monospace', fontSize: attFontSize }">
              {{ st.permission_count || 0 }}
            </td>
            <td :style="{ border: '1px solid #94a3b8', padding: `${rowPaddingY} 0.5px`, textAlign: 'center', fontFamily: 'monospace', fontSize: attFontSize, color: (st.unexcused_count || 0) > 0 ? '#dc2626' : 'inherit' }">
              {{ st.unexcused_count || 0 }}
            </td>
          </tr>

          <!-- Row Rekapitulasi Rata-Rata Kelas -->
          <tr v-if="sortedStudents.length > 0" style="background-color: #f1f5f9; font-weight: 900; border-top: 1.5px solid #0f172a;">
            <td colspan="4" :style="{ border: '1px solid #0f172a', padding: `${rowPaddingY} 3px`, textAlign: 'center', textTransform: 'uppercase', fontSize: footerAvgFontSize, color: '#0f172a' }">
              RATA-RATA KELAS
            </td>

            <!-- Rata-rata per Mapel -->
            <td
              v-for="sbj in subjects"
              :key="'ledger-avg-'+sbj.id"
              :style="{ border: '1px solid #0f172a', padding: `${rowPaddingY} 0.5px`, textAlign: 'center', fontFamily: 'monospace', fontSize: mapelScoreFontSize, fontWeight: '900', color: '#065f46' }"
            >
              {{ getSubjectAverage(sbj.id) }}
            </td>

            <!-- Total, Rata-rata, Pkt -->
            <td :style="{ border: '1px solid #0f172a', padding: `${rowPaddingY} 1px`, textAlign: 'center', fontFamily: 'monospace', fontSize: mapelScoreFontSize, fontWeight: '900', color: '#0f172a', backgroundColor: '#f8fafc' }">
              {{ classAverageTotal }}
            </td>
            <td :style="{ border: '1px solid #0f172a', padding: `${rowPaddingY} 1px`, textAlign: 'center', fontFamily: 'monospace', fontSize: mapelScoreFontSize, fontWeight: '900', color: '#065f46', backgroundColor: '#ecfdf5' }">
              {{ Number(classAverageScore || 0).toFixed(1) }}
            </td>
            <td :style="{ border: '1px solid #0f172a', padding: `${rowPaddingY} 1px`, textAlign: 'center', color: '#64748b', fontSize: mapelScoreFontSize }">
              -
            </td>

            <!-- Total Kehadiran Kelas S/I/A -->
            <td :style="{ border: '1px solid #0f172a', padding: `${rowPaddingY} 0.5px`, textAlign: 'center', fontFamily: 'monospace', fontSize: attFontSize, fontWeight: 'bold', color: '#334155' }">
              {{ totalSick }}
            </td>
            <td :style="{ border: '1px solid #0f172a', padding: `${rowPaddingY} 0.5px`, textAlign: 'center', fontFamily: 'monospace', fontSize: attFontSize, fontWeight: 'bold', color: '#334155' }">
              {{ totalPermission }}
            </td>
            <td :style="{ border: '1px solid #0f172a', padding: `${rowPaddingY} 0.5px`, textAlign: 'center', fontFamily: 'monospace', fontSize: attFontSize, fontWeight: 'bold', color: '#dc2626' }">
              {{ totalUnexcused }}
            </td>
          </tr>

          <tr v-if="sortedStudents.length === 0">
            <td :colspan="8 + subjects.length" style="border: 1px solid #94a3b8; padding: 8px; text-align: center; color: #94a3b8; font-style: italic;">
              Tidak ada data peserta didik pada kelas ini.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 5. TITIMANGSA & TANDA TANGAN RESMI -->
    <div style="padding-top: 1px; page-break-inside: avoid; break-inside: avoid;">
      <div :style="{ textAlign: 'right', fontSize: titimangsaFontSize, fontWeight: '700', color: '#1e293b', paddingRight: '12px', marginBottom: '1px' }">
        <span>{{ city || 'Bogor' }}, {{ issuedDate || '........................' }}</span>
      </div>

      <table :style="{ width: '100%', textAlign: 'center', fontSize: signatureFontSize, borderCollapse: 'collapse' }">
        <tbody>
          <tr>
            <!-- Wali Kelas -->
            <td style="width: 50%; vertical-align: top; padding: 0 16px;">
              <div>
                <p style="font-weight: 700; color: #334155; margin: 0;">Wali Kelas,</p>
                <p style="font-weight: 700; color: #0f172a; margin: 0;">{{ formatClassName(classInfo?.name) }}</p>
              </div>
              <div :style="{ height: signatureGap }"></div>
              <div>
                <p :style="{ fontWeight: '900', color: '#020617', textDecoration: 'underline', margin: '0', fontSize: signatureNameSize }">
                  {{ classInfo?.homeroom_teacher?.full_name || classInfo?.homeroom_teacher_name || '............................................' }}
                </p>
                <p :style="{ fontSize: signatureNipSize, color: '#475569', margin: '1px 0 0 0', fontFamily: 'monospace' }">
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
              <div :style="{ height: signatureGap }"></div>
              <div>
                <p :style="{ fontWeight: '900', color: '#020617', textDecoration: 'underline', margin: '0', fontSize: signatureNameSize }">
                  {{ schoolSetting?.principal_name || 'H. Umar Usman Ali, S.Pd, S.Pd.I' }}
                </p>
                <p :style="{ fontSize: signatureNipSize, color: '#475569', margin: '1px 0 0 0', fontFamily: 'monospace' }">
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
  paperSize: {
    type: String,
    default: 'f4', // 'f4' | 'a4'
  },
  fitMode: {
    type: String,
    default: 'fit', // 'fit' | 'standard'
  },
});

const isA4 = computed(() => props.paperSize === 'a4');
const studentCount = computed(() => props.students?.length || 0);

// Menentukan apakah mode muat 1 halaman aktif
const isFitToOnePage = computed(() => {
  if (props.fitMode === 'standard') return false;
  return true; // default fit
});

// Dimensi Kop Surat & Logo
const logoSize = computed(() => {
  if (!isFitToOnePage.value) return 52;
  if (studentCount.value > 40) return isA4.value ? 36 : 42;
  if (studentCount.value > 30) return isA4.value ? 42 : 46;
  return 50;
});

const logoTdWidth = computed(() => logoSize.value + 6);
const kopMarginBottom = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '1px' : '2px');
const garisGandaMargin = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '2px' : '3px');

const kopFoundationSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '9px' : '10.5px');
const kopSchoolSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? (isA4.value ? '12px' : '13.5px') : '14.5px');
const kopAddressSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '7.5px' : '8.5px');
const kopPhoneSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '7px' : '8px');

// Dimensi Judul Lembar
const titleMainSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? (isA4.value ? '10px' : '11px') : '12px');
const titleSubSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '8px' : '9.5px');
const titleYearSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '7px' : '8px');
const judulMarginBottom = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '2px' : '4px');

// Dimensi Info Strip Kelas
const infoStripFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '7.5px' : '8.5px');
const infoStripPadding = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '1.5px 5px' : '2.5px 6px');
const infoStripMarginBottom = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '2px' : '4px');

// Dimensi Baris & Font Tabel Matriks
const baseFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? (isA4.value ? '7px' : '7.5px') : '8.5px');

const rowPaddingY = computed(() => {
  if (!isFitToOnePage.value) return '1.5px';
  if (studentCount.value > 42) return isA4.value ? '0.2px' : '0.35px';
  if (studentCount.value > 35) return isA4.value ? '0.5px' : '0.7px';
  if (studentCount.value > 25) return '1px';
  return '1.5px';
});

const thPaddingY = computed(() => {
  if (!isFitToOnePage.value) return '2.5px';
  if (studentCount.value > 40) return '1.5px';
  return '2px';
});

const tableFontSize = computed(() => {
  if (!isFitToOnePage.value) return '8px';
  if (studentCount.value > 42) return isA4.value ? '6.8px' : '7.2px';
  if (studentCount.value > 35) return isA4.value ? '7.2px' : '7.6px';
  return '8px';
});

const tableLineHeight = computed(() => {
  if (!isFitToOnePage.value) return '1.15';
  if (studentCount.value > 42) return '1.02';
  if (studentCount.value > 35) return '1.05';
  return '1.1';
});

// Lebar Kolom Horizontal Responsif A4 vs F4
const noColWidth = computed(() => isA4.value ? 18 : 20);
const nisnColWidth = computed(() => isA4.value ? 50 : 58);
const nameMinWidth = computed(() => isA4.value ? 105 : 125);
const nameMaxWidth = computed(() => isA4.value ? 125 : 150);
const lpColWidth = computed(() => isA4.value ? 14 : 16);
const mapelColMinWidth = computed(() => isA4.value ? 22 : 26);
const totalColWidth = computed(() => isA4.value ? 28 : 32);
const rataColWidth = computed(() => isA4.value ? 26 : 30);
const pktColWidth = computed(() => isA4.value ? 20 : 24);
const attColWidth = computed(() => isA4.value ? 11 : 13);

const mapelCodeFontSize = computed(() => isA4.value ? '7px' : '7.8px');
const kktpFontSize = computed(() => isA4.value ? '6px' : '6.5px');
const nameFontSize = computed(() => isFitToOnePage.value && studentCount.value > 42 ? (isA4.value ? '6.8px' : '7.2px') : '7.8px');
const nisnFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '6.5px' : '7.2px');
const lpFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '6.8px' : '7.2px');
const mapelScoreFontSize = computed(() => tableFontSize.value);
const rankFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? (isA4.value ? '7px' : '7.5px') : '8px');
const attFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '6.5px' : '7.2px');
const footerAvgFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '7px' : '7.8px');

// Dimensi Tanda Tangan
const signatureGap = computed(() => {
  if (!isFitToOnePage.value) return '28px';
  if (studentCount.value > 42) return isA4.value ? '14px' : '17px';
  if (studentCount.value > 35) return isA4.value ? '18px' : '22px';
  return '25px';
});

const titimangsaFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '7.5px' : '8.5px');
const signatureFontSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '7.5px' : '8.5px');
const signatureNameSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '8px' : '9px');
const signatureNipSize = computed(() => isFitToOnePage.value && studentCount.value > 40 ? '6.8px' : '7.5px');

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

const classAverageTotal = computed(() => {
  if (!props.students || props.students.length === 0) return 0;
  const total = props.students.reduce((acc, s) => acc + (Number(s.total_score) || 0), 0);
  return Math.round(total / props.students.length);
});

function getSubjectAverage(subjectId) {
  if (!props.students || !props.students.length) return '-';
  let sum = 0;
  let count = 0;
  for (const st of props.students) {
    const val = st.scores?.[subjectId]?.score;
    if (val !== undefined && val !== null && val !== '') {
      sum += Number(val);
      count++;
    }
  }
  return count > 0 ? (sum / count).toFixed(1) : '-';
}

const totalSick = computed(() => {
  if (!props.students) return 0;
  return props.students.reduce((acc, s) => acc + (Number(s.sick_count) || 0), 0);
});

const totalPermission = computed(() => {
  if (!props.students) return 0;
  return props.students.reduce((acc, s) => acc + (Number(s.permission_count) || 0), 0);
});

const totalUnexcused = computed(() => {
  if (!props.students) return 0;
  return props.students.reduce((acc, s) => acc + (Number(s.unexcused_count) || 0), 0);
});
</script>
