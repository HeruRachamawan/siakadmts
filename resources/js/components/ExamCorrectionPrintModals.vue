<template>
  <div v-if="exam">
    <!-- 1. PRINT PREVIEW MODAL: REKAPITULASI NILAI KOREKSI ASLI -->
    <div v-if="showRecap" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[70] flex flex-col p-2 sm:p-6 overflow-hidden">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl mx-auto flex flex-col h-full max-h-full overflow-hidden border border-slate-200">
        <!-- Modal Toolbar Header -->
        <div class="no-print px-5 sm:px-8 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 flex-shrink-0 z-20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-slate-800 text-white flex items-center justify-center shadow-md shadow-slate-800/20 flex-shrink-0">
              <Printer class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-sm font-black text-slate-800 font-lexend uppercase tracking-wider">
                  Pratinjau Lembar Rekapitulasi Koreksi & Nilai Ujian
                </h3>
                <span
                  :class="selectedPaperSize === 'f4' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-blue-100 text-blue-800 border-blue-300'"
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border flex items-center gap-1"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="selectedPaperSize === 'f4' ? 'bg-emerald-600' : 'bg-blue-600'"></span>
                  {{ selectedPaperSize === 'f4' ? 'Ukuran F4 / Folio (33 × 21.5 cm)' : 'Ukuran A4 (29.7 × 21 cm)' }}
                </span>
              </div>
              <p class="text-xs text-slate-500 font-medium">Format cetak resmi kurikulum & madrasah dengan rincian capaian per bentuk soal.</p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap justify-end">
            <!-- Pilihan Ukuran Kertas (A4 / F4) -->
            <div class="flex items-center bg-slate-200/90 p-1 rounded-xl border border-slate-300 shadow-inner">
              <button
                type="button"
                @click="selectedPaperSize = 'f4'"
                :class="selectedPaperSize === 'f4' ? 'bg-white text-emerald-800 shadow font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                title="Kertas F4 / Folio Lanskap (330 x 215 mm) - Standar madrasah"
              >
                <span>F4 / Folio</span>
                <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 text-[9px] font-black rounded-full uppercase tracking-tighter">Rekomendasi</span>
              </button>
              <button
                type="button"
                @click="selectedPaperSize = 'a4'"
                :class="selectedPaperSize === 'a4' ? 'bg-white text-slate-900 shadow font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                title="Kertas A4 Lanskap (297 x 210 mm)"
              >
                <span>A4</span>
              </button>
            </div>

            <button
              @click="printDocument"
              type="button"
              class="px-4 sm:px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak / Simpan PDF</span>
            </button>

            <button
              @click="exportToWord"
              type="button"
              class="px-4 sm:px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-blue-600/20 flex items-center gap-2 cursor-pointer"
              title="Unduh format dokumen Microsoft Word (.doc)"
            >
              <FileText class="w-4 h-4" />
              <span>Unduh Word (.doc)</span>
            </button>

            <button
              @click="$emit('update:showRecap', false)"
              type="button"
              class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 active:scale-95 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>

        <!-- Canvas Area -->
        <div class="flex-1 overflow-auto bg-slate-200/90 p-4 sm:p-8 flex justify-start xl:justify-center items-start">
          <div
            :id="recapSheetId"
            :class="selectedPaperSize === 'f4' ? 'w-[1240px] min-w-[1240px]' : 'w-[1080px] min-w-[1080px]'"
            class="printable-recap-sheet bg-white p-8 sm:p-10 shadow-2xl border border-slate-300 text-slate-900 rounded-xl space-y-5 my-2 transition-all duration-200"
          >
            <!-- Kop Madrasah -->
            <div class="flex items-center gap-5 border-b-4 border-double border-slate-900 pb-3">
              <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
                <img
                  v-if="schoolProfile?.app_logo_url || schoolProfile?.app_logo"
                  :src="schoolProfile?.app_logo_url || getImageUrl(schoolProfile?.app_logo)"
                  class="w-full h-full object-contain"
                  alt="Logo Madrasah"
                />
                <div v-else class="w-18 h-18 rounded-2xl bg-teal-800 text-white flex items-center justify-center font-black text-xl shadow-md">
                  MTS
                </div>
              </div>
              <div class="text-center flex-1 pr-6 sm:pr-14">
                <div class="text-xs sm:text-sm font-bold tracking-widest text-slate-700 uppercase">
                  {{ schoolProfile?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM AL-HASANAH' }}
                </div>
                <h1 class="text-xl sm:text-2xl font-black font-lexend uppercase tracking-wide text-slate-900 mt-0.5">
                  {{ schoolProfile?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
                </h1>
                <div class="text-[11px] sm:text-xs font-semibold text-slate-600 mt-0.5">
                  {{ schoolProfile?.school_tagline || 'Madrasah Tsanawiyah Al - Hasanah Ciomas' }} • Status: {{ schoolProfile?.school_accreditation || 'TERAKREDITASI A' }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-slate-600 mt-0.5">
                  {{ schoolProfile?.school_address || 'Jl. Ciapus Sukamakmur No.05, Ciomas, Bogor' }}
                </div>
                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                  Telp: {{ schoolProfile?.school_phone || '081617666017' }} • Email: {{ schoolProfile?.school_email || 'mtsalhasanah.ciomas@gmail.com' }}
                </div>
              </div>
            </div>

            <!-- Judul Lembar Rekap -->
            <div class="text-center space-y-1">
              <h2 class="text-sm sm:text-base font-black uppercase tracking-wider text-slate-900 font-lexend underline decoration-slate-900 underline-offset-4">
                LEMBAR REKAPITULASI CAPAIAN NILAI ASESMEN PER BENTUK SOAL
              </h2>
              <p class="text-xs font-bold text-slate-700 uppercase tracking-wide">
                {{ getExamTypeFullName(exam.exam_type) }} • SEMESTER {{ formatSemester(exam.semester || exam.academic_year?.semester) }} • TAHUN PELAJARAN {{ formatAcademicYear(exam) }}
              </p>
            </div>

            <!-- Metadata Asesmen -->
            <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 text-xs font-medium border border-slate-300 rounded-lg p-3 bg-slate-50/70">
              <div class="space-y-1">
                <div class="flex"><span class="w-32 font-bold text-slate-700">Mata Pelajaran</span><span class="mr-2">:</span><strong class="text-slate-900">{{ exam.subject?.name || '-' }}</strong></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Kelas / Rombel</span><span class="mr-2">:</span><strong class="text-slate-900">Kelas {{ exam.class_room?.name || '-' }}</strong></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Guru Pengampu</span><span class="mr-2">:</span><span>{{ exam.teacher?.full_name || exam.teacher?.name || '-' }}</span></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Nama Paket Ujian</span><span class="mr-2">:</span><span>{{ exam.title }}</span></div>
              </div>
              <div class="space-y-1">
                <div class="flex"><span class="w-36 font-bold text-slate-700">Jenis Asesmen</span><span class="mr-2">:</span><strong class="text-slate-900">{{ getExamTypeFullName(exam.exam_type) }}</strong></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">KKM / KKTP</span><span class="mr-2">:</span><strong class="text-teal-900 bg-teal-100/70 px-2 py-0.5 rounded border border-teal-300">{{ exam.kkm }}</strong></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">Bobot Penilaian</span><span class="mr-2">:</span><span>Objektif: {{ exam.pg_weight }}% | Uraian: {{ exam.essay_weight }}%</span></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">Komposisi Soal</span><span class="mr-2">:</span><span class="font-semibold">{{ exam.total_questions }} Butir ({{ activeQuestionTypesList.map(t => `${t.count} ${t.label}`).join(', ') }})</span></div>
              </div>
            </div>

            <!-- Tabel Siswa -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-[11px] border-collapse border border-slate-300 bg-white">
                <thead>
                  <tr class="bg-slate-100 text-slate-800 uppercase font-black tracking-wider text-center text-[10px]">
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-10">No</th>
                    <th rowspan="2" class="border border-slate-300 px-2.5 py-2 w-28">NISN</th>
                    <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left min-w-[180px]">Nama Siswa</th>
                    <th rowspan="2" class="border border-slate-300 px-1.5 py-2 w-10">L/P</th>
                    <th :colspan="activeQuestionTypesList.length" class="border border-slate-300 px-3 py-1.5 bg-slate-200/80 text-teal-950 font-black">
                      Capaian Nilai per Bentuk Soal (Poin / Maks)
                    </th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-18">Nilai Asli</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-18">Nilai Rem.</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-18 bg-slate-150 font-black">Nilai Akhir</th>
                    <th rowspan="2" class="border border-slate-300 px-2 py-2 w-24">Keterangan</th>
                  </tr>
                  <tr class="bg-slate-50 text-slate-700 font-bold text-center text-[10px]">
                    <th
                      v-for="typeObj in activeQuestionTypesList"
                      :key="typeObj.key"
                      class="border border-slate-300 px-2 py-1.5 min-w-[80px]"
                    >
                      <div class="font-bold text-slate-800">{{ typeObj.label }}</div>
                      <div class="text-[9px] text-slate-500 font-normal">({{ typeObj.count }} Soal • Maks {{ typeObj.maxScore }})</div>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(student, idx) in students"
                    :key="student.id"
                    class="hover:bg-slate-50/50"
                  >
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-slate-500">{{ idx + 1 }}</td>
                    <td class="border border-slate-300 px-2.5 py-1.5 text-center font-mono text-slate-600">{{ student.nisn || '-' }}</td>
                    <td class="border border-slate-300 px-3 py-1.5 font-bold text-slate-800">{{ student.name }}</td>
                    <td class="border border-slate-300 px-1.5 py-1.5 text-center font-bold text-slate-600">{{ student.gender || '-' }}</td>

                    <td
                      v-for="typeObj in activeQuestionTypesList"
                      :key="typeObj.key"
                      class="border border-slate-300 px-2 py-1.5 text-center"
                    >
                      <div class="font-bold text-slate-800">
                        {{ calculateStudentTypeScore(student, typeObj).earned }}
                      </div>
                      <div class="text-[9px] text-slate-500">
                        ({{ calculateStudentTypeScore(student, typeObj).percentage }}%)
                      </div>
                    </td>

                    <td class="border border-slate-300 px-2 py-1.5 text-center font-bold" :class="(student.total_score !== null && student.total_score < exam.kkm) ? 'text-rose-600' : 'text-slate-800'">
                      {{ student.total_score !== null ? student.total_score : '-' }}
                    </td>

                    <td class="border border-slate-300 px-2 py-1.5 text-center font-bold text-teal-700">
                      {{ (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? student.remedial_score : '-' }}
                    </td>

                    <td class="border border-slate-300 px-2 py-1.5 text-center font-black text-slate-900 bg-slate-50/70">
                      {{ getStudentFinalGrade(student) }}
                    </td>

                    <td class="border border-slate-300 px-2 py-1.5 text-center font-black text-[10px]">
                      <span v-if="getStudentPrintStatus(student) === 'TUNTAS'" class="text-emerald-700">TUNTAS</span>
                      <span v-else-if="getStudentPrintStatus(student) === 'TUNTAS (REM)'" class="text-teal-700">TUNTAS (REM)</span>
                      <span v-else-if="getStudentPrintStatus(student) === 'REMEDIAL'" class="text-rose-600">REMEDIAL</span>
                      <span v-else class="text-slate-400">BELUM UJIAN</span>
                    </td>
                  </tr>

                  <tr v-if="students.length === 0">
                    <td :colspan="8 + activeQuestionTypesList.length" class="border border-slate-300 px-4 py-6 text-center text-slate-400">
                      Tidak ada data siswa pada kelas ini.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Statistik Klasikal -->
            <div class="space-y-1.5">
              <div class="text-xs font-black text-slate-800 uppercase tracking-wider">Rekapitulasi Ketuntasan Klasikal:</div>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                <div class="p-2 border border-slate-300 rounded bg-slate-50">
                  <span class="text-slate-500 block text-[10px]">Total Siswa Peserta</span>
                  <strong class="text-slate-800 text-sm">{{ printStats.participated }} / {{ printStats.total }} Siswa</strong>
                </div>
                <div class="p-2 border border-slate-300 rounded bg-slate-50">
                  <span class="text-slate-500 block text-[10px]">Tuntas (Murni + Rem)</span>
                  <strong class="text-emerald-700 text-sm">{{ printStats.totalPassed }} Siswa ({{ printStats.passPercentage }}%)</strong>
                </div>
                <div class="p-2 border border-slate-300 rounded bg-slate-50">
                  <span class="text-slate-500 block text-[10px]">Perlu Remedial</span>
                  <strong class="text-rose-600 text-sm">{{ printStats.remedialCount }} Siswa</strong>
                </div>
                <div class="p-2 border border-slate-300 rounded bg-slate-50">
                  <span class="text-slate-500 block text-[10px]">Rata-rata / Tertinggi / Terendah</span>
                  <strong class="text-slate-800 text-sm">{{ printStats.avgScore }} / {{ printStats.maxScore }} / {{ printStats.minScore }}</strong>
                </div>
              </div>
            </div>

            <!-- Tanda Tangan Resmi -->
            <div class="pt-6 text-xs text-slate-800">
              <div class="flex justify-end mb-4">
                <div>Ciomas, {{ getPrintDateFormatted() }}</div>
              </div>
              <div class="grid grid-cols-2 gap-8 text-center">
                <div>
                  <div class="font-bold">Mengetahui,</div>
                  <div>Kepala MTs Al - Hasanah</div>
                  <div class="h-20 flex items-center justify-center"></div>
                  <div class="font-black text-slate-900 underline">{{ schoolProfile?.principal_name || 'Kepala Madrasah' }}</div>
                  <div class="text-[11px] text-slate-600 font-mono">NIP: {{ schoolProfile?.principal_nip || '-' }}</div>
                </div>

                <div>
                  <div class="font-bold">Guru Pengampu,</div>
                  <div>Mata Pelajaran {{ exam.subject?.name || '' }}</div>
                  <div class="h-20 flex items-center justify-center"></div>
                  <div class="font-black text-slate-900 underline">{{ exam.teacher?.full_name || exam.teacher?.name || 'Guru Mata Pelajaran' }}</div>
                  <div class="text-[11px] text-slate-600 font-mono">NIP: {{ exam.teacher?.nip || '-' }}</div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- 2. PRINT PREVIEW MODAL: NILAI JADI (RAPOR BEBAS REMEDIAL) -->
    <div v-if="showAdjusted" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[70] flex flex-col p-2 sm:p-6 overflow-hidden">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl mx-auto flex flex-col h-full max-h-full overflow-hidden border border-slate-200">
        <!-- Header -->
        <div class="no-print px-5 sm:px-8 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 flex-shrink-0 z-20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/20 flex-shrink-0">
              <Award class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-sm font-black text-slate-800 font-lexend uppercase tracking-wider">
                  Pratinjau Lembar Nilai Jadi (Standar Rapor Bebas Remedial)
                </h3>
                <span
                  :class="selectedAdjustedPaperSize === 'f4' ? 'bg-teal-100 text-teal-800 border-teal-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300'"
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border flex items-center gap-1"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="selectedAdjustedPaperSize === 'f4' ? 'bg-teal-600' : 'bg-emerald-600'"></span>
                  {{ (selectedAdjustedPaperSize === 'f4' ? 'Ukuran F4 / Folio (33 × 21.5 cm)' : 'Ukuran A4 (29.7 × 21 cm)') + (selectedAdjustedOrientation === 'portrait' ? ' • Potret' : ' • Lanskap') }}
                </span>
              </div>
              <p class="text-xs text-slate-500 font-medium">Dokumen rekapitulasi nilai akhir siap setor rapor & kurikulum dengan tingkat ketuntasan 100%.</p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap justify-end">
            <!-- Pilihan Ukuran Kertas (F4 / A4) & Orientasi (Potret / Lanskap) -->
            <div class="flex items-center gap-1.5 flex-wrap">
              <div class="flex items-center bg-slate-200/90 p-1 rounded-xl border border-slate-300 shadow-inner">
                <button
                  type="button"
                  @click="selectedAdjustedPaperSize = 'f4'"
                  :class="selectedAdjustedPaperSize === 'f4' ? 'bg-white text-amber-800 shadow font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                  class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                  title="Kertas F4 / Folio (330 x 215 mm) - Rekomendasi standar madrasah"
                >
                  <span>F4 / Folio</span>
                  <span class="px-1.5 py-0.2 bg-amber-100 text-amber-800 text-[9px] font-black rounded-full uppercase tracking-tighter">Rekomendasi</span>
                </button>
                <button
                  type="button"
                  @click="selectedAdjustedPaperSize = 'a4'"
                  :class="selectedAdjustedPaperSize === 'a4' ? 'bg-white text-slate-900 shadow font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                  class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                  title="Kertas A4 (297 x 210 mm)"
                >
                  <span>A4</span>
                </button>
              </div>

              <div class="flex items-center bg-slate-200/90 p-1 rounded-xl border border-slate-300 shadow-inner">
                <button
                  type="button"
                  @click="selectedAdjustedOrientation = 'portrait'"
                  :class="selectedAdjustedOrientation === 'portrait' ? 'bg-white text-amber-800 shadow font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                  class="px-2.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1 cursor-pointer"
                  title="Format Potret (Tegak) - Sangat cocok untuk daftar nilai rapor"
                >
                  <span>Potret (Tegak)</span>
                </button>
                <button
                  type="button"
                  @click="selectedAdjustedOrientation = 'landscape'"
                  :class="selectedAdjustedOrientation === 'landscape' ? 'bg-white text-slate-900 shadow font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                  class="px-2.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1 cursor-pointer"
                  title="Format Lanskap (Mendatar)"
                >
                  <span>Lanskap</span>
                </button>
              </div>
            </div>

            <button
              @click="printAdjustedDocument"
              type="button"
              class="px-4 sm:px-5 py-2.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-amber-600/20 flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak / Simpan PDF</span>
            </button>

            <button
              @click="$emit('update:showAdjusted', false)"
              type="button"
              class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 active:scale-95 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>

        <!-- Canvas Area -->
        <div class="flex-1 overflow-auto bg-slate-200/90 p-4 sm:p-8 flex justify-start xl:justify-center items-start">
          <div
            :id="adjustedSheetId"
            :class="selectedAdjustedOrientation === 'portrait' ? (selectedAdjustedPaperSize === 'f4' ? 'w-[860px] min-w-[860px]' : 'w-[820px] min-w-[820px]') : (selectedAdjustedPaperSize === 'f4' ? 'w-[1240px] min-w-[1240px]' : 'w-[1080px] min-w-[1080px]')"
            class="printable-recap-sheet bg-white p-8 sm:p-10 shadow-2xl border border-slate-300 text-slate-900 rounded-xl space-y-5 my-2 transition-all duration-200"
          >
            <!-- Kop -->
            <div class="flex items-center gap-5 border-b-4 border-double border-slate-900 pb-3">
              <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
                <img
                  v-if="schoolProfile?.app_logo_url || schoolProfile?.app_logo"
                  :src="schoolProfile?.app_logo_url || getImageUrl(schoolProfile?.app_logo)"
                  class="w-full h-full object-contain"
                  alt="Logo Madrasah"
                />
                <div v-else class="w-18 h-18 rounded-2xl bg-teal-800 text-white flex items-center justify-center font-black text-xl shadow-md">
                  MTS
                </div>
              </div>
              <div class="text-center flex-1 pr-6 sm:pr-14">
                <div class="text-xs sm:text-sm font-bold tracking-widest text-slate-700 uppercase">
                  {{ schoolProfile?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM AL-HASANAH' }}
                </div>
                <div class="text-lg sm:text-2xl font-black tracking-wide text-slate-900 uppercase my-0.5">
                  {{ schoolProfile?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
                </div>
                <div class="text-[11px] sm:text-xs font-semibold text-slate-600">
                  {{ schoolProfile?.school_tagline || 'Madrasah Tsanawiyah Al - Hasanah Ciomas' }} • Status: {{ schoolProfile?.school_accreditation || 'TERAKREDITASI A' }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-slate-500">
                  {{ schoolProfile?.school_address || 'Jl. Ciapus Sukamakmur No.05, Ciomas, Bogor' }}
                </div>
                <div class="text-[9px] sm:text-[10px] text-slate-500 font-mono">
                  Telp: {{ schoolProfile?.school_phone || '081617666017' }} • Email: {{ schoolProfile?.school_email || 'mtsalhasanah.ciomas@gmail.com' }}
                </div>
              </div>
            </div>

            <!-- Judul -->
            <div class="text-center space-y-1">
              <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-wide uppercase underline">
                Daftar Rekapitulasi Nilai Asesmen (Standar Rapor Bebas Remedial)
              </h2>
              <p class="text-xs sm:text-sm font-bold text-slate-700 uppercase">
                {{ getExamTypeFullName(exam.exam_type) }} • SEMESTER {{ formatSemester(exam.semester || exam.academic_year?.semester) }} • TAHUN PELAJARAN {{ formatAcademicYear(exam) }}
              </p>
            </div>

            <!-- Metadata -->
            <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 text-xs font-medium border border-slate-300 rounded-lg p-3 bg-slate-50/70">
              <div class="space-y-1">
                <div class="flex"><span class="w-32 font-bold text-slate-700">Mata Pelajaran</span><span class="mr-2">:</span><strong class="text-slate-900">{{ exam.subject?.name || '-' }}</strong></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Kelas / Rombel</span><span class="mr-2">:</span><strong class="text-slate-900">Kelas {{ exam.class_room?.name || '-' }}</strong></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Guru Pengampu</span><span class="mr-2">:</span><span>{{ exam.teacher?.full_name || exam.teacher?.name || '-' }}</span></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Nama Paket Ujian</span><span class="mr-2">:</span><span>{{ exam.title }}</span></div>
              </div>
              <div class="space-y-1">
                <div class="flex"><span class="w-36 font-bold text-slate-700">Jenis Asesmen</span><span class="mr-2">:</span><strong class="text-slate-900">{{ getExamTypeFullName(exam.exam_type) }}</strong></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">KKM / KKTP Madrasah</span><span class="mr-2">:</span><strong class="text-teal-900 bg-teal-100/70 px-2 py-0.5 rounded border border-teal-300">{{ exam.kkm }}</strong></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">Peserta / Rata-rata Rapor</span><span class="mr-2">:</span><span class="font-bold">{{ adjustedStats.completedStudents }} Siswa • Rata-rata: {{ adjustedStats.adjustedAvg }}</span></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">Ketuntasan Rapor</span><span class="mr-2">:</span><span class="font-bold text-emerald-700">{{ adjustedStats.adjustedPassedCount }} Siswa Tuntas ({{ adjustedStats.adjustedPassPct }}%) • Rem: 0 Siswa (Tidak Ada)</span></div>
              </div>
            </div>

            <!-- Tabel Nilai Jadi (Standar Rapor Bebas Remedial Tanpa Nilai Asli) -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-[11px] border-collapse border border-slate-400 bg-white">
                <thead>
                  <tr class="bg-slate-100 text-slate-900 uppercase font-black text-center text-[10px]">
                    <th class="border border-slate-400 px-2 py-2 w-10">No</th>
                    <th class="border border-slate-400 px-3 py-2 w-28">NISN</th>
                    <th class="border border-slate-400 px-3 py-2 text-left">Nama Lengkap Siswa</th>
                    <th class="border border-slate-400 px-2 py-2 w-12">L/P</th>
                    <th class="border border-slate-400 px-3 py-2 w-28 bg-amber-50 text-amber-900 font-black">Nilai Akhir (Rapor)</th>
                    <th class="border border-slate-400 px-2 py-2 w-16">Predikat</th>
                    <th class="border border-slate-400 px-3 py-2 w-24">Status</th>
                    <th class="border border-slate-400 px-3 py-2 text-left">Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(student, idx) in students"
                    :key="student.id"
                    class="border-b border-slate-300 hover:bg-slate-50"
                  >
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-bold">{{ idx + 1 }}</td>
                    <td class="border border-slate-300 px-3 py-1.5 text-center font-mono font-medium">{{ student.nisn || '-' }}</td>
                    <td class="border border-slate-300 px-3 py-1.5 text-left font-bold text-slate-900 uppercase">{{ student.name }}</td>
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-medium">{{ student.gender || '-' }}</td>
                    <td class="border border-slate-400 px-3 py-1.5 text-center font-black text-sm bg-amber-50/50 text-slate-900">
                      {{ (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? student.remedial_score : (student.total_score !== null ? student.total_score : '-') }}
                    </td>
                    <td class="border border-slate-300 px-2 py-1.5 text-center font-bold">
                      <span class="font-black text-slate-800">
                        {{ getGradePredicate((student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? student.remedial_score : student.total_score).pred }}
                      </span>
                    </td>
                    <td class="border border-slate-300 px-3 py-1.5 text-center font-black text-emerald-800">
                      <span
                        v-if="student.has_submitted || student.total_score !== null || student.remedial_score !== null"
                        class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase tracking-wider"
                      >
                        TUNTAS
                      </span>
                      <span v-else class="text-slate-400 text-[10px] font-bold">
                        BELUM UJIAN
                      </span>
                    </td>
                    <td class="border border-slate-300 px-3 py-1.5 text-left text-[10px] text-slate-600">
                      <span v-if="student.has_submitted || student.total_score !== null">
                        Memenuhi KKM ({{ exam.kkm }})
                      </span>
                      <span v-else>-</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Ringkasan Kelas -->
            <div class="border border-slate-300 rounded-lg p-3 bg-slate-50 text-xs space-y-1 break-inside-avoid">
              <div class="font-black text-slate-800 uppercase tracking-wider">Rekapitulasi Ketercapaian Hasil Belajar:</div>
              <div class="grid grid-cols-4 gap-2 text-[11px] pt-1">
                <div class="p-2 border border-slate-200 rounded bg-white text-center">
                  <span class="text-slate-500 block text-[10px]">Total Peserta:</span>
                  <strong class="text-slate-800 text-sm">{{ students.length }} Siswa</strong>
                </div>
                <div class="p-2 border border-slate-200 rounded bg-white text-center">
                  <span class="text-slate-500 block text-[10px]">Rata-rata Nilai Jadi:</span>
                  <strong class="text-teal-800 text-sm">{{ adjustedStats.adjustedAvg }}</strong>
                </div>
                <div class="p-2 border border-slate-200 rounded bg-white text-center">
                  <span class="text-slate-500 block text-[10px]">Siswa Tuntas:</span>
                  <strong class="text-emerald-700 text-sm">{{ adjustedStats.adjustedPassedCount }} Siswa ({{ adjustedStats.adjustedPassedPct }}%)</strong>
                </div>
                <div class="p-2 border border-slate-200 rounded bg-white text-center">
                  <span class="text-slate-500 block text-[10px]">Siswa Remedial:</span>
                  <strong class="text-slate-700 text-sm">0 Siswa (Tidak Ada)</strong>
                </div>
              </div>
            </div>

            <!-- Tanda Tangan -->
            <div class="pt-4 text-xs text-slate-800 break-inside-avoid">
              <div class="flex justify-end mb-4 font-medium">
                Ciomas, {{ getPrintDateFormatted() }}
              </div>

              <div class="grid grid-cols-2 gap-8 text-center">
                <div>
                  <div class="font-bold">Mengetahui,</div>
                  <div>Kepala MTs Al - Hasanah</div>
                  <div class="h-20 flex items-center justify-center"></div>
                  <div class="font-black text-slate-900 underline">{{ schoolProfile?.principal_name || 'Kepala Madrasah' }}</div>
                  <div class="text-[11px] text-slate-600 font-mono">NIP: {{ schoolProfile?.principal_nip || '-' }}</div>
                </div>

                <div>
                  <div class="font-bold">Guru Pengampu,</div>
                  <div>Mata Pelajaran {{ exam.subject?.name || '' }}</div>
                  <div class="h-20 flex items-center justify-center"></div>
                  <div class="font-black text-slate-900 underline">{{ exam.teacher?.full_name || exam.teacher?.name || 'Guru Mata Pelajaran' }}</div>
                  <div class="text-[11px] text-slate-600 font-mono">NIP: {{ exam.teacher?.nip || '-' }}</div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- 3. PRINT PREVIEW MODAL: ANALISIS BUTIR SOAL & GRAFIK -->
    <div v-if="showAnalysis" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[70] flex flex-col p-2 sm:p-6 overflow-hidden">
      <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl mx-auto flex flex-col h-full max-h-full overflow-hidden border border-slate-200">
        <!-- Header -->
        <div class="no-print px-5 sm:px-8 py-3.5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 flex-shrink-0 z-20">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20 flex-shrink-0">
              <BarChart2 class="w-5 h-5" />
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-sm font-black text-slate-800 font-lexend uppercase tracking-wider">
                  Pratinjau Laporan Analisis Butir Soal & Grafik
                </h3>
                <span
                  :class="selectedAnalysisPaperSize === 'f4' ? 'bg-teal-100 text-teal-800 border-teal-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300'"
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border flex items-center gap-1"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="selectedAnalysisPaperSize === 'f4' ? 'bg-teal-600' : 'bg-emerald-600'"></span>
                  {{ selectedAnalysisPaperSize === 'f4' ? 'Ukuran F4 / Folio (33 × 21.5 cm)' : 'Ukuran A4 (29.7 × 21 cm)' }}
                </span>
              </div>
              <p class="text-xs text-slate-500 font-medium">Laporan resmi analisis kesukaran, daya pembeda, dan visual grafik batang daya serap kelas.</p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap justify-end">
            <!-- Pilihan Ukuran Kertas -->
            <div class="flex items-center bg-slate-200/90 p-1 rounded-xl border border-slate-300 shadow-inner">
              <button
                type="button"
                @click="selectedAnalysisPaperSize = 'f4'"
                :class="selectedAnalysisPaperSize === 'f4' ? 'bg-white text-teal-800 shadow font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                title="Kertas F4 / Folio Lanskap (330 x 215 mm)"
              >
                <span>F4 / Folio</span>
                <span class="px-1.5 py-0.2 bg-teal-100 text-teal-800 text-[9px] font-black rounded-full uppercase tracking-tighter">Rekomendasi</span>
              </button>
              <button
                type="button"
                @click="selectedAnalysisPaperSize = 'a4'"
                :class="selectedAnalysisPaperSize === 'a4' ? 'bg-white text-slate-900 shadow font-black' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer"
                title="Kertas A4 Lanskap (297 x 210 mm)"
              >
                <span>A4</span>
              </button>
            </div>

            <button
              @click="printAnalysisDocument"
              type="button"
              class="px-4 sm:px-5 py-2.5 bg-teal-600 hover:bg-teal-700 active:scale-95 text-white font-bold rounded-xl text-xs transition-all shadow-md shadow-teal-600/20 flex items-center gap-2 cursor-pointer"
            >
              <Printer class="w-4 h-4" />
              <span>Cetak / Simpan PDF</span>
            </button>

            <button
              @click="$emit('update:showAnalysis', false)"
              type="button"
              class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 active:scale-95 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Tutup
            </button>
          </div>
        </div>

        <!-- Canvas Area -->
        <div class="flex-1 overflow-auto bg-slate-200/90 p-4 sm:p-8 flex justify-start xl:justify-center items-start">
          <div
            :id="analysisSheetId"
            :class="selectedAnalysisPaperSize === 'f4' ? 'w-[1240px] min-w-[1240px]' : 'w-[1080px] min-w-[1080px]'"
            class="printable-recap-sheet bg-white p-8 sm:p-10 shadow-2xl border border-slate-300 text-slate-900 rounded-xl space-y-5 my-2 transition-all duration-200"
          >
            <!-- Kop -->
            <div class="flex items-center gap-5 border-b-4 border-double border-slate-900 pb-3">
              <div class="w-20 h-20 flex-shrink-0 flex items-center justify-center">
                <img
                  v-if="schoolProfile?.app_logo_url || schoolProfile?.app_logo"
                  :src="schoolProfile?.app_logo_url || getImageUrl(schoolProfile?.app_logo)"
                  class="w-full h-full object-contain"
                  alt="Logo Madrasah"
                />
                <div v-else class="w-18 h-18 rounded-2xl bg-teal-800 text-white flex items-center justify-center font-black text-xl shadow-md">
                  MTS
                </div>
              </div>
              <div class="text-center flex-1 pr-6 sm:pr-14">
                <div class="text-xs sm:text-sm font-bold tracking-widest text-slate-700 uppercase">
                  {{ schoolProfile?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM AL-HASANAH' }}
                </div>
                <div class="text-lg sm:text-2xl font-black tracking-wide text-slate-900 uppercase my-0.5">
                  {{ schoolProfile?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH' }}
                </div>
                <div class="text-[11px] sm:text-xs font-semibold text-slate-600">
                  {{ schoolProfile?.school_tagline || 'Madrasah Tsanawiyah Al - Hasanah Ciomas' }} • Status: {{ schoolProfile?.school_accreditation || 'TERAKREDITASI A' }}
                </div>
                <div class="text-[10px] sm:text-[11px] text-slate-500">
                  {{ schoolProfile?.school_address || 'Jl. Ciapus Sukamakmur No.05, Ciomas, Bogor' }}
                </div>
                <div class="text-[9px] sm:text-[10px] text-slate-500 font-mono">
                  Telp: {{ schoolProfile?.school_phone || '081617666017' }} • Email: {{ schoolProfile?.school_email || 'mtsalhasanah.ciomas@gmail.com' }}
                </div>
              </div>
            </div>

            <!-- Judul -->
            <div class="text-center space-y-1">
              <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-wide uppercase underline">
                Laporan Analisis Butir Soal & Daya Serap Asesmen
              </h2>
              <p class="text-xs sm:text-sm font-bold text-slate-700 uppercase">
                {{ getExamTypeFullName(exam.exam_type) }} • SEMESTER {{ formatSemester(exam.semester || exam.academic_year?.semester) }} • TAHUN PELAJARAN {{ formatAcademicYear(exam) }}
              </p>
            </div>

            <!-- Metadata -->
            <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 text-xs font-medium border border-slate-300 rounded-lg p-3 bg-slate-50/70">
              <div class="space-y-1">
                <div class="flex"><span class="w-32 font-bold text-slate-700">Mata Pelajaran</span><span class="mr-2">:</span><strong class="text-slate-900">{{ exam.subject?.name || '-' }}</strong></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Kelas / Rombel</span><span class="mr-2">:</span><strong class="text-slate-900">Kelas {{ exam.class_room?.name || '-' }}</strong></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Guru Pengampu</span><span class="mr-2">:</span><span>{{ exam.teacher?.full_name || exam.teacher?.name || '-' }}</span></div>
                <div class="flex"><span class="w-32 font-bold text-slate-700">Nama Paket Ujian</span><span class="mr-2">:</span><span>{{ exam.title }}</span></div>
              </div>
              <div class="space-y-1">
                <div class="flex"><span class="w-36 font-bold text-slate-700">Jenis Asesmen</span><span class="mr-2">:</span><strong class="text-slate-900">{{ getExamTypeFullName(exam.exam_type) }}</strong></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">KKM / KKTP</span><span class="mr-2">:</span><strong class="text-teal-900 bg-teal-100/70 px-2 py-0.5 rounded border border-teal-300">{{ exam.kkm }}</strong></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">Peserta / Rata-rata</span><span class="mr-2">:</span><span class="font-bold">{{ analysisData?.summary?.total_students || students.length }} Siswa • Rata-rata: {{ analysisData?.summary?.avg_score || '-' }}</span></div>
                <div class="flex"><span class="w-36 font-bold text-slate-700">Ketuntasan Kelas</span><span class="mr-2">:</span><span class="font-bold text-emerald-700">{{ analysisData?.summary?.passed_count || 0 }} Tuntas ({{ analysisData?.summary?.pass_percentage || 0 }}%) • Rem: {{ analysisData?.summary?.remedial_count || 0 }}</span></div>
              </div>
            </div>

            <!-- Visual Grafik Batang Cetak -->
            <div class="border border-slate-300 rounded-lg p-3.5 bg-white space-y-2 break-inside-avoid">
              <div class="flex justify-between items-center text-xs font-bold text-slate-800 border-b border-slate-200 pb-1.5">
                <span class="uppercase tracking-wider">Visual Grafik Ketercapaian Daya Serap Butir Soal (% Siswa Menjawab Benar):</span>
                <div class="flex items-center gap-3 text-[10px] font-semibold">
                  <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-emerald-500 rounded"></span> Mudah (&ge; 70%)</span>
                  <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-amber-500 rounded"></span> Sedang (30-69%)</span>
                  <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-rose-500 rounded"></span> Sukar (&lt; 30%)</span>
                </div>
              </div>

              <!-- Bar chart container -->
              <div class="pt-5 pb-2 px-1">
                <div class="h-36 flex items-end gap-1 sm:gap-2 border-b-2 border-slate-800 relative">
                  <!-- Guideline 70% -->
                  <div class="absolute inset-x-0 bottom-[70%] border-b border-dashed border-emerald-600 pointer-events-none z-0">
                    <span class="absolute -top-2.5 -left-6 text-[8px] font-mono text-emerald-700 font-bold bg-white px-0.5">70%</span>
                  </div>
                  <!-- Guideline 30% -->
                  <div class="absolute inset-x-0 bottom-[30%] border-b border-dashed border-rose-500 pointer-events-none z-0">
                    <span class="absolute -top-2.5 -left-6 text-[8px] font-mono text-rose-700 font-bold bg-white px-0.5">30%</span>
                  </div>

                  <!-- Bars -->
                  <div
                    v-for="qa in analysisQuestions"
                    :key="qa.question_number"
                    class="flex-1 flex flex-col items-center h-full justify-end z-10 min-w-[20px]"
                  >
                    <!-- Percentage label on top of bar -->
                    <span class="text-[7.5px] sm:text-[8px] font-mono font-bold text-slate-700 mb-0.5">
                      {{ Math.round((qa.difficulty_index || 0) * 100) }}%
                    </span>
                    <div
                      class="w-full rounded-t-sm transition-all"
                      :style="{
                        height: Math.max(4, Math.round((qa.difficulty_index || 0) * 100)) + '%',
                        backgroundColor: qa.difficulty_category === 'Mudah' ? '#10b981' : (qa.difficulty_category === 'Sedang' ? '#f59e0b' : '#ef4444')
                      }"
                    ></div>
                    <!-- Question number and answer key label -->
                    <div class="text-[8px] sm:text-[9px] font-bold text-slate-800 text-center mt-1">
                      #{{ qa.question_number }}
                      <span class="block text-[7.5px] text-teal-800 font-mono font-black">{{ qa.correct_answer || '-' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tabel Analisis Butir Soal Lengkap -->
            <div class="overflow-x-auto">
              <table class="w-full text-left text-[10px] border-collapse border border-slate-400 bg-white">
                <thead>
                  <tr class="bg-slate-100 text-slate-900 uppercase font-black text-center text-[10px]">
                    <th class="border border-slate-400 px-2 py-1.5 w-8">No</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-24">Bentuk Soal</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-14">Kunci</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-16">Benar (B)</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-16">Salah (S)</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-20">Daya Serap</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-20">Indeks (P)</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-24">Tingkat Kesukaran</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-20">Pembeda (D)</th>
                    <th class="border border-slate-400 px-2 py-1.5 w-28">Kategori Pembeda</th>
                    <th class="border border-slate-400 px-2 py-1.5">Rekomendasi Tindak Lanjut</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="qa in analysisQuestions"
                    :key="qa.question_number"
                    class="border-b border-slate-300 hover:bg-slate-50"
                  >
                    <td class="border border-slate-300 px-2 py-1 text-center font-bold">{{ qa.question_number }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-medium">{{ questionTypeLabel(qa.question_type) }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-black text-teal-800 font-mono">{{ qa.correct_answer || '-' }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-bold text-emerald-700">{{ qa.correct_count }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-bold text-rose-700">{{ qa.wrong_count }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-black">
                      {{ Math.round((qa.difficulty_index || 0) * 100) }}%
                    </td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">{{ qa.difficulty_index }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-bold">
                      <span
                        :class="[
                          qa.difficulty_category === 'Mudah' ? 'text-emerald-700' :
                          qa.difficulty_category === 'Sedang' ? 'text-amber-700' : 'text-rose-700'
                        ]"
                      >
                        {{ qa.difficulty_category }}
                      </span>
                    </td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-mono font-bold">{{ qa.discrimination_index }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-center">{{ qa.discrimination_category }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-center font-medium">
                      <span v-if="qa.difficulty_index < 0.30" class="text-rose-700 font-bold">
                        Prioritas Remedial
                      </span>
                      <span v-else-if="qa.discrimination_index < 0.20" class="text-amber-700 font-semibold">
                        Perlu Revisi Butir
                      </span>
                      <span v-else class="text-emerald-700 font-semibold">
                        Diterima Baik
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Kesimpulan & Tindak Lanjut -->
            <div class="border border-slate-300 rounded-lg p-3 bg-slate-50 text-xs space-y-1.5 break-inside-avoid">
              <div class="font-black text-slate-800 uppercase tracking-wider">Kesimpulan Komposisi & Evaluasi Guru:</div>
              <div class="grid grid-cols-3 gap-2 text-[11px]">
                <div class="p-2 border border-slate-200 rounded bg-white">
                  <span class="text-slate-500 block text-[10px]">Soal Kategori Mudah:</span>
                  <strong class="text-emerald-700 text-sm">{{ difficultyDistribution.mudah }} Butir ({{ difficultyDistribution.mudahPct }}%)</strong>
                </div>
                <div class="p-2 border border-slate-200 rounded bg-white">
                  <span class="text-slate-500 block text-[10px]">Soal Kategori Sedang:</span>
                  <strong class="text-amber-700 text-sm">{{ difficultyDistribution.sedang }} Butir ({{ difficultyDistribution.sedangPct }}%)</strong>
                </div>
                <div class="p-2 border border-slate-200 rounded bg-white">
                  <span class="text-slate-500 block text-[10px]">Soal Kategori Sukar:</span>
                  <strong class="text-rose-700 text-sm">{{ difficultyDistribution.sukar }} Butir ({{ difficultyDistribution.sukarPct }}%)</strong>
                </div>
              </div>
              <div v-if="difficultyDistribution.hardQuestions.length > 0" class="text-[11px] text-rose-800 pt-1 font-medium">
                * Catatan: Butir soal nomor <strong>{{ difficultyDistribution.hardQuestions.map(n => '#' + n).join(', ') }}</strong> memiliki tingkat kesalahan tinggi (&gt; 70% salah). Diperlukan pendalaman konsep pada materi terkait dalam remedial klasikal.
              </div>
            </div>

            <!-- Tanda Tangan -->
            <div class="pt-4 text-xs text-slate-800 break-inside-avoid">
              <div class="flex justify-end mb-4 font-medium">
                Ciomas, {{ getPrintDateFormatted() }}
              </div>

              <div class="grid grid-cols-2 gap-8 text-center">
                <div>
                  <div class="font-bold">Mengetahui,</div>
                  <div>Kepala MTs Al - Hasanah</div>
                  <div class="h-20 flex items-center justify-center"></div>
                  <div class="font-black text-slate-900 underline">{{ schoolProfile?.principal_name || 'Kepala Madrasah' }}</div>
                  <div class="text-[11px] text-slate-600 font-mono">NIP: {{ schoolProfile?.principal_nip || '-' }}</div>
                </div>

                <div>
                  <div class="font-bold">Guru Pengampu,</div>
                  <div>Mata Pelajaran {{ exam.subject?.name || '' }}</div>
                  <div class="h-20 flex items-center justify-center"></div>
                  <div class="font-black text-slate-900 underline">{{ exam.teacher?.full_name || exam.teacher?.name || 'Guru Mata Pelajaran' }}</div>
                  <div class="text-[11px] text-slate-600 font-mono">NIP: {{ exam.teacher?.nip || '-' }}</div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useToast } from '../composables/useToast';
import {
  Printer,
  FileText,
  BarChart2,
  Award,
  X
} from 'lucide-vue-next';

const props = defineProps({
  showRecap: {
    type: Boolean,
    default: false
  },
  showAdjusted: {
    type: Boolean,
    default: false
  },
  showAnalysis: {
    type: Boolean,
    default: false
  },
  exam: {
    type: Object,
    default: null
  },
  questions: {
    type: Array,
    default: () => []
  },
  students: {
    type: Array,
    default: () => []
  },
  schoolProfile: {
    type: Object,
    default: null
  },
  analysisData: {
    type: Object,
    default: null
  },
  idPrefix: {
    type: String,
    default: 'admin'
  }
});

defineEmits([
  'update:showRecap',
  'update:showAdjusted',
  'update:showAnalysis'
]);

const toast = useToast();

const selectedPaperSize = ref('f4');
const selectedAdjustedPaperSize = ref('f4');
const selectedAdjustedOrientation = ref('portrait'); // 'portrait' atau 'landscape'
const selectedAnalysisPaperSize = ref('f4');

const recapSheetId = computed(() => `${props.idPrefix}_printableRecapSheet`);
const adjustedSheetId = computed(() => `${props.idPrefix}_printableAdjustedSheet`);
const analysisSheetId = computed(() => `${props.idPrefix}_printableAnalysisSheet`);

const QUESTION_TYPE_LABELS = {
  pg: 'PG Biasa',
  pg_complex: 'PG Kompleks',
  true_false: 'Benar / Salah',
  agree_disagree: 'Setuju / Tidak',
  matching: 'Menjodohkan',
  short_answer: 'Isian Singkat',
  essay: 'Uraian / Essay'
};

function formatAcademicYear(exam) {
  return exam?.academic_year?.year ||
    exam?.academic_year?.name ||
    exam?.academicYear?.year ||
    exam?.academicYear?.name ||
    '2026/2027';
}

function formatSemester(sem) {
  if (!sem) return 'GANJIL';
  const s = String(sem).toLowerCase().trim();
  if (s === 'odd' || s === 'ganjil' || s === '1') return 'GANJIL';
  if (s === 'even' || s === 'genap' || s === '2') return 'GENAP';
  return s.toUpperCase();
}

const activeQuestionTypesList = computed(() => {
  const typeMap = {};
  (props.questions || []).forEach(q => {
    const t = q.question_type || 'pg';
    if (!typeMap[t]) {
      typeMap[t] = {
        key: t,
        label: QUESTION_TYPE_LABELS[t] || t,
        count: 0,
        maxScore: 0,
        questions: []
      };
    }
    typeMap[t].count++;
    typeMap[t].maxScore += Number(q.score_weight || 1);
    typeMap[t].questions.push(q);
  });
  return Object.values(typeMap);
});

function checkAnswerMatch(type, studentAns, correctAns) {
  if (studentAns === null || studentAns === undefined || correctAns === null || correctAns === undefined) return false;
  const cleanStudent = String(studentAns).trim();
  const cleanCorrect = String(correctAns).trim();
  if (!cleanStudent || !cleanCorrect) return false;

  if (type === 'pg' || type === 'true_false' || type === 'agree_disagree') {
    return cleanStudent.toUpperCase() === cleanCorrect.toUpperCase();
  }

  if (type === 'pg_complex') {
    const normalize = (str) => {
      let upper = str.toUpperCase();
      let parts = upper.includes(',') ? upper.split(',').map(s => s.trim()) : upper.replace(/\s+/g, '').split('');
      parts = parts.filter(Boolean);
      parts.sort();
      return Array.from(new Set(parts)).join(',');
    };
    return normalize(cleanStudent) === normalize(cleanCorrect);
  }

  if (type === 'matching') {
    const normalizePairs = (str) => {
      let clean = str.toUpperCase().replace(/\s+/g, '').replace(/[-:;]/g, '');
      let items = clean.split(',').filter(Boolean);
      items.sort();
      return items.join(',');
    };
    return normalizePairs(cleanStudent) === normalizePairs(cleanCorrect);
  }

  if (type === 'short_answer') {
    const studentNorm = cleanStudent.replace(/\s+/g, ' ').toLowerCase();
    const synonyms = cleanCorrect.split(/[|\/]/);
    for (const syn of synonyms) {
      if (studentNorm === syn.replace(/\s+/g, ' ').trim().toLowerCase()) {
        return true;
      }
    }
    return false;
  }

  return cleanStudent.toUpperCase() === cleanCorrect.toUpperCase();
}

function calculateStudentTypeScore(student, typeObj) {
  if (!student.has_submitted && !student.answer_string && Object.keys(student.student_answers || {}).length === 0) {
    return {
      earned: 0,
      max: typeObj.maxScore,
      percentage: 0,
      correctCount: 0,
      totalCount: typeObj.count
    };
  }

  let earned = 0;
  let correctCount = 0;

  typeObj.questions.forEach(q => {
    if (q.question_type === 'essay') {
      const sScore = Number(student.essay_scores?.[String(q.question_number)] || 0);
      const cap = Number(q.score_weight || 10);
      earned += Math.min(Math.max(0, sScore), cap);
    } else {
      const studentAns = student.student_answers?.[String(q.question_number)] ?? (student.answer_string ? student.answer_string[q.question_number - 1] : '');
      if (checkAnswerMatch(q.question_type, studentAns, q.correct_answer)) {
        earned += Number(q.score_weight || 1);
        correctCount++;
      }
    }
  });

  const max = typeObj.maxScore || 1;
  const percentage = Math.round((earned / max) * 100);

  return {
    earned: Math.round(earned * 10) / 10,
    max: Math.round(max * 10) / 10,
    percentage,
    correctCount,
    totalCount: typeObj.count
  };
}

function getStudentFinalGrade(student) {
  if (!student.has_submitted && !student.answer_string && Object.keys(student.student_answers || {}).length === 0 && student.total_score === null) {
    return '-';
  }
  const kkm = Number(props.exam?.kkm || 75);
  const rem = (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? Number(student.remedial_score) : null;
  const initial = Number(student.total_score || 0);

  if (rem !== null && rem >= kkm && initial < kkm) {
    return rem;
  }
  return initial;
}

function getStudentPrintStatus(student) {
  if (!student.has_submitted && !student.answer_string && Object.keys(student.student_answers || {}).length === 0 && student.total_score === null) {
    return 'Belum Ujian';
  }
  const kkm = Number(props.exam?.kkm || 75);
  const rem = (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '') ? Number(student.remedial_score) : null;
  const initial = Number(student.total_score || 0);

  if (rem !== null && rem >= kkm && initial < kkm) {
    return 'TUNTAS (REM)';
  }
  if (initial >= kkm) {
    return 'TUNTAS';
  }
  return 'REMEDIAL';
}

function getPrintDateFormatted() {
  const options = { day: 'numeric', month: 'long', year: 'numeric' };
  return new Date().toLocaleDateString('id-ID', options);
}

function getExamTypeFullName(type) {
  const map = {
    uh: 'Penilaian Harian (UH)',
    sts: 'Sumatif Tengah Semester (STS)',
    sas: 'Sumatif Akhir Semester (SAS)',
    pat: 'Penilaian Akhir Tahun (PAT)',
    am: 'Asesmen Madrasah (AM)',
    quiz: 'Kuis / Latihan Harian'
  };
  return map[type] || 'Asesmen Pembelajaran';
}

function questionTypeLabel(type) {
  return QUESTION_TYPE_LABELS[type] || type || '-';
}

const printStats = computed(() => {
  const students = props.students || [];
  if (students.length === 0) {
    return {
      total: 0,
      participated: 0,
      passedPure: 0,
      passedRemedial: 0,
      totalPassed: 0,
      remedialCount: 0,
      avgScore: 0,
      maxScore: 0,
      minScore: 0,
      passPercentage: 0
    };
  }

  const kkm = Number(props.exam?.kkm || 75);
  let passedPure = 0;
  let passedRemedial = 0;
  let remedialCount = 0;
  let totalScoreSum = 0;
  let maxScore = -1;
  let minScore = 999;
  let countedStudents = 0;

  students.forEach(s => {
    const initialScore = Number(s.total_score || 0);
    const hasRemedial = s.remedial_score !== null && s.remedial_score !== undefined && s.remedial_score !== '';
    const remScore = hasRemedial ? Number(s.remedial_score) : null;
    const effectiveGrade = (remScore !== null && remScore >= kkm) ? remScore : initialScore;

    if (s.has_submitted || s.answer_string || (s.student_answers && Object.keys(s.student_answers).length > 0) || s.total_score !== null) {
      countedStudents++;
      totalScoreSum += effectiveGrade;
      if (effectiveGrade > maxScore) maxScore = effectiveGrade;
      if (effectiveGrade < minScore) minScore = effectiveGrade;

      if (remScore !== null && remScore >= kkm && initialScore < kkm) {
        passedRemedial++;
      } else if (initialScore >= kkm) {
        passedPure++;
      } else {
        remedialCount++;
      }
    }
  });

  const totalPassed = passedPure + passedRemedial;
  const avg = countedStudents > 0 ? (totalScoreSum / countedStudents).toFixed(1) : 0;
  const passPct = countedStudents > 0 ? Math.round((totalPassed / countedStudents) * 100) : 0;

  return {
    total: students.length,
    participated: countedStudents,
    passedPure,
    passedRemedial,
    totalPassed,
    remedialCount,
    avgScore: avg,
    maxScore: maxScore >= 0 ? maxScore : 0,
    minScore: minScore <= 100 && minScore >= 0 ? minScore : 0,
    passPercentage: passPct
  };
});

const adjustedStats = computed(() => {
  const students = props.students || [];
  const kkm = Number(props.exam?.kkm) || 75;
  
  let totalOrigScore = 0;
  let countOrigScored = 0;
  let origPassed = 0;
  
  let totalAdjScore = 0;
  let countAdjScored = 0;
  let adjPassed = 0;

  students.forEach(s => {
    const raw = (s.total_score !== null && s.total_score !== undefined && s.total_score !== '') ? Number(s.total_score) : null;
    const adj = (s.remedial_score !== null && s.remedial_score !== undefined && s.remedial_score !== '') ? Number(s.remedial_score) : raw;

    if (raw !== null && !isNaN(raw)) {
      totalOrigScore += raw;
      countOrigScored++;
      if (raw >= kkm) origPassed++;
    }

    if (adj !== null && !isNaN(adj)) {
      totalAdjScore += adj;
      countAdjScored++;
      if (adj >= kkm) adjPassed++;
    }
  });

  const originalAvg = countOrigScored > 0 ? (totalOrigScore / countOrigScored).toFixed(1) : '0';
  const originalPassPct = countOrigScored > 0 ? Math.round((origPassed / countOrigScored) * 100) : 0;
  const originalRem = countOrigScored - origPassed;

  const adjustedAvg = countAdjScored > 0 ? (totalAdjScore / countAdjScored).toFixed(1) : '0';
  const adjustedPassPct = countAdjScored > 0 ? Math.round((adjPassed / countAdjScored) * 100) : 0;
  const adjustedRem = countAdjScored - adjPassed;

  return {
    totalStudents: students.length,
    completedStudents: countAdjScored,
    counted: countOrigScored || students.length,
    originalAvg: Number(originalAvg),
    originalPassPct,
    originalPassed: origPassed,
    origRemCount: originalRem,
    adjustedAvg: Number(adjustedAvg),
    adjustedPassPct,
    adjustedPassed: adjPassed,
    adjustedPassedCount: adjPassed,
    adjustedRemCount: adjustedRem
  };
});

const analysisQuestions = computed(() => {
  return props.analysisData?.questions_analysis || [];
});

const difficultyDistribution = computed(() => {
  const list = analysisQuestions.value;
  let mudah = 0;
  let sedang = 0;
  let sukar = 0;
  const hardQuestions = [];
  const easyQuestions = [];

  list.forEach(q => {
    const p = Number(q.difficulty_index) || 0;
    if (p >= 0.70) {
      mudah++;
      easyQuestions.push(q.question_number);
    } else if (p < 0.30) {
      sukar++;
      hardQuestions.push(q.question_number);
    } else {
      sedang++;
    }
  });

  const total = list.length || 1;
  return {
    total: list.length,
    mudah,
    sedang,
    sukar,
    mudahPct: Math.round((mudah / total) * 100),
    sedangPct: Math.round((sedang / total) * 100),
    sukarPct: Math.round((sukar / total) * 100),
    hardQuestions,
    easyQuestions
  };
});

function getGradePredicate(score) {
  const s = Number(score) || 0;
  if (s >= 90) return { pred: 'A', label: 'Sangat Baik', color: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
  if (s >= 80) return { pred: 'B', label: 'Baik', color: 'bg-teal-50 text-teal-700 border-teal-200' };
  if (s >= 70) return { pred: 'C', label: 'Cukup', color: 'bg-blue-50 text-blue-700 border-blue-200' };
  if (s >= 60) return { pred: 'D', label: 'Kurang', color: 'bg-amber-50 text-amber-700 border-amber-200' };
  return { pred: 'E', label: 'Perlu Bimbingan', color: 'bg-rose-50 text-rose-700 border-rose-200' };
}

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path;
  return `/storage/${path.replace(/^\/?storage\//, '')}`;
}

function printDocument() {
  const printElem = document.getElementById(recapSheetId.value);
  if (!printElem) {
    window.print();
    return;
  }

  const content = printElem.innerHTML;
  const printWindow = window.open('', '_blank', 'width=1200,height=850');
  if (!printWindow) {
    window.print();
    return;
  }

  const isF4 = selectedPaperSize.value === 'f4';
  const paperCssSize = isF4 ? '330mm 215mm' : '297mm 210mm';
  const paperTitle = isF4 ? 'F4 / Folio' : 'A4';

  printWindow.document.open();
  printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekapitulasi Nilai Asesmen (${paperTitle}) - ${props.exam?.title || 'Ujian'}</title>
  <style>
    @page {
      size: ${paperCssSize};
      margin: 8mm 8mm 8mm 8mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: #ffffff !important;
      color: #0f172a;
      padding: 0;
      margin: 0;
      font-size: ${isF4 ? '10px' : '9px'};
      line-height: 1.35;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .font-semibold { font-weight: 600; }
    .font-black { font-weight: 900; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .uppercase { text-transform: uppercase; }
    .underline { text-decoration: underline; }
    
    .flex { display: flex; }
    .flex-col { flex-direction: column; }
    .justify-between { justify-content: space-between; }
    .justify-center { justify-content: center; }
    .justify-end { justify-content: flex-end; }
    .items-center { align-items: center; }
    .items-start { align-items: flex-start; }
    .flex-1 { flex: 1 1 0%; }
    .flex-shrink-0 { flex-shrink: 0; }
    .gap-1 { gap: 4px; }
    .gap-1\\.5 { gap: 6px; }
    .gap-2 { gap: 8px; }
    .gap-3 { gap: 12px; }
    .gap-4 { gap: 16px; }
    .gap-5 { gap: 20px; }
    .gap-8 { gap: 32px; }
    .gap-x-8 { column-gap: 32px; }
    .gap-y-1\\.5 { row-gap: 6px; }
    .grid { display: grid; }
    .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    
    .w-full { width: 100%; }
    .w-10 { width: 40px; }
    .w-18 { width: 72px; }
    .w-20 { width: 80px; }
    .w-24 { width: 96px; }
    .w-28 { width: 112px; }
    .w-32 { width: 128px; }
    .w-36 { width: 144px; }
    .h-18 { height: 72px; }
    .h-20 { height: 80px; }
    
    .p-2 { padding: 8px; }
    .p-3 { padding: 12px; }
    .p-4 { padding: 16px; }
    .px-1\\.5 { padding-left: 6px; padding-right: 6px; }
    .px-2 { padding-left: 8px; padding-right: 8px; }
    .px-2\\.5 { padding-left: 10px; padding-right: 10px; }
    .px-3 { padding-left: 12px; padding-right: 12px; }
    .py-1\\.5 { padding-top: 6px; padding-bottom: 6px; }
    .py-2 { padding-top: 8px; padding-bottom: 8px; }
    .pb-3 { padding-bottom: 12px; }
    .pt-6 { padding-top: 24px; }
    .mb-4 { margin-bottom: 16px; }
    .pr-6 { padding-right: 24px; }
    .pr-14 { padding-right: 56px; }
    
    .border { border: 1px solid #cbd5e1; }
    .border-b { border-bottom: 1px solid #cbd5e1; }
    .border-b-4 { border-bottom: 4px double #0f172a; }
    .border-double { border-style: double; }
    .border-slate-300 { border-color: #cbd5e1; }
    .border-slate-900 { border-color: #0f172a; }
    .border-teal-300 { border-color: #5eead4; }
    
    .rounded { border-radius: 4px; }
    .rounded-lg { border-radius: 8px; }
    .rounded-xl { border-radius: 12px; }
    .rounded-2xl { border-radius: 16px; }
    
    .bg-white { background-color: #ffffff; }
    .bg-slate-50 { background-color: #f8fafc; }
    .bg-slate-100 { background-color: #f1f5f9; }
    .bg-slate-200\\/80 { background-color: rgba(226, 232, 240, 0.8); }
    .bg-teal-800 { background-color: #115e59; }
    .bg-teal-100\\/70 { background-color: rgba(204, 251, 241, 0.7); }
    
    .text-white { color: #ffffff; }
    .text-slate-500 { color: #64748b; }
    .text-slate-600 { color: #475569; }
    .text-slate-700 { color: #334155; }
    .text-slate-800 { color: #1e293b; }
    .text-slate-900 { color: #0f172a; }
    .text-teal-700 { color: #0f766e; }
    .text-teal-900 { color: #134e4a; }
    .text-teal-950 { color: #042f2e; }
    .text-emerald-700 { color: #047857; }
    .text-rose-600 { color: #e11d48; }
    
    .text-xs { font-size: 9.5px; }
    .text-sm { font-size: 11px; }
    .text-base { font-size: 12px; }
    .text-lg { font-size: 14px; }
    .text-xl { font-size: 16px; }
    .text-2xl { font-size: 18px; }
    .text-\\[9px\\] { font-size: 9px; }
    .text-\\[10px\\] { font-size: 9.5px; }
    .text-\\[11px\\] { font-size: 10px; }
    
    table {
      width: 100% !important;
      border-collapse: collapse;
      margin-top: 6px;
    }
    th, td {
      border: 1px solid #94a3b8;
      padding: 3px 5px;
    }
    thead {
      display: table-header-group;
    }
    tr {
      page-break-inside: avoid;
    }
    img {
      max-width: 100%;
      height: auto;
      object-fit: contain;
    }
    .break-inside-avoid {
      page-break-inside: avoid;
      break-inside: avoid;
    }
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  </style>
</head>
<body>
  <div style="width: 100%; max-width: 100%;">
    ${content}
  </div>
</body>
</html>`);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 400);
}

function printAdjustedDocument() {
  const printElem = document.getElementById(adjustedSheetId.value);
  if (!printElem) {
    window.print();
    return;
  }

  const content = printElem.innerHTML;
  const printWindow = window.open('', '_blank', 'width=1200,height=850');
  if (!printWindow) {
    window.print();
    return;
  }

  const isF4 = selectedAdjustedPaperSize.value === 'f4';
  const isPortrait = selectedAdjustedOrientation.value === 'portrait';
  let paperCssSize = isPortrait ? (isF4 ? '215mm 330mm' : '210mm 297mm') : (isF4 ? '330mm 215mm' : '297mm 210mm');
  const paperTitle = (isF4 ? 'F4 / Folio' : 'A4') + (isPortrait ? ' Potret' : ' Lanskap');

  printWindow.document.open();
  printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Daftar Rekapitulasi Nilai Asesmen (${paperTitle}) - ${props.exam?.title || 'Ujian'}</title>
  <style>
    @page {
      size: ${paperCssSize};
      margin: 8mm 8mm 8mm 8mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: #ffffff !important;
      color: #0f172a;
      padding: 0;
      margin: 0;
      font-size: ${isPortrait ? (isF4 ? '10px' : '9.5px') : (isF4 ? '11px' : '10px')};
      line-height: 1.35;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .font-semibold { font-weight: 600; }
    .font-black { font-weight: 900; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .uppercase { text-transform: uppercase; }
    .underline { text-decoration: underline; }
    
    .flex { display: flex; }
    .flex-col { flex-direction: column; }
    .justify-between { justify-content: space-between; }
    .justify-center { justify-content: center; }
    .justify-end { justify-content: flex-end; }
    .items-center { align-items: center; }
    .items-start { align-items: flex-start; }
    .flex-1 { flex: 1 1 0%; }
    .flex-shrink-0 { flex-shrink: 0; }
    .gap-1 { gap: 4px; }
    .gap-2 { gap: 8px; }
    .gap-3 { gap: 12px; }
    .gap-4 { gap: 16px; }
    .gap-5 { gap: 20px; }
    .gap-8 { gap: 32px; }
    .gap-x-8 { column-gap: 32px; }
    .gap-y-1\\.5 { row-gap: 6px; }
    .grid { display: grid; }
    .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    
    .w-full { width: 100%; }
    .w-10 { width: 40px; }
    .w-12 { width: 48px; }
    .w-14 { width: 56px; }
    .w-18 { width: 72px; }
    .w-20 { width: 80px; }
    .w-24 { width: 96px; }
    .w-28 { width: 112px; }
    .w-32 { width: 128px; }
    .w-36 { width: 144px; }
    .h-18 { height: 72px; }
    .h-20 { height: 80px; }
    
    .p-2 { padding: 8px; }
    .p-3 { padding: 12px; }
    .p-4 { padding: 16px; }
    .px-2 { padding-left: 8px; padding-right: 8px; }
    .px-3 { padding-left: 12px; padding-right: 12px; }
    .px-4 { padding-left: 16px; padding-right: 16px; }
    .py-0\\.5 { padding-top: 2px; padding-bottom: 2px; }
    .py-1 { padding-top: 4px; padding-bottom: 4px; }
    .py-1\\.5 { padding-top: 6px; padding-bottom: 6px; }
    .py-2 { padding-top: 8px; padding-bottom: 8px; }
    .py-3 { padding-top: 12px; padding-bottom: 12px; }
    .pb-3 { padding-bottom: 12px; }
    .pt-1 { padding-top: 4px; }
    .pt-4 { padding-top: 16px; }
    .pt-6 { padding-top: 24px; }
    .mb-4 { margin-bottom: 16px; }
    .pr-6 { padding-right: 24px; }
    .pr-14 { padding-right: 56px; }
    
    .border { border: 1px solid #cbd5e1; }
    .border-b { border-bottom: 1px solid #cbd5e1; }
    .border-b-4 { border-bottom: 4px double #0f172a; }
    .border-double { border-style: double; }
    .border-slate-200 { border-color: #e2e8f0; }
    .border-slate-300 { border-color: #cbd5e1; }
    .border-slate-400 { border-color: #94a3b8; }
    .border-slate-900 { border-color: #0f172a; }
    .border-teal-300 { border-color: #5eead4; }
    .border-emerald-200 { border-color: #a7f3d0; }
    .border-emerald-300 { border-color: #6ee7b7; }
    .border-amber-200 { border-color: #fde68a; }
    
    .rounded { border-radius: 4px; }
    .rounded-lg { border-radius: 8px; }
    .rounded-xl { border-radius: 12px; }
    .rounded-2xl { border-radius: 16px; }
    
    .bg-white { background-color: #ffffff; }
    .bg-slate-50 { background-color: #f8fafc; }
    .bg-slate-100 { background-color: #f1f5f9; }
    .bg-teal-800 { background-color: #115e59; }
    .bg-teal-100 { background-color: #ccfbf1; }
    .bg-emerald-50 { background-color: #ecfdf5; }
    .bg-emerald-100 { background-color: #d1fae5; }
    .bg-amber-50 { background-color: #fffbeb; }
    .bg-amber-50\\/50 { background-color: rgba(255, 251, 235, 0.5); }
    
    .text-white { color: #ffffff; }
    .text-slate-400 { color: #94a3b8; }
    .text-slate-500 { color: #64748b; }
    .text-slate-600 { color: #475569; }
    .text-slate-700 { color: #334155; }
    .text-slate-800 { color: #1e293b; }
    .text-slate-900 { color: #0f172a; }
    .text-teal-700 { color: #0f766e; }
    .text-teal-800 { color: #115e59; }
    .text-teal-900 { color: #134e4a; }
    .text-emerald-700 { color: #047857; }
    .text-emerald-800 { color: #065f46; }
    .text-emerald-900 { color: #064e3b; }
    .text-amber-800 { color: #92400e; }
    .text-amber-900 { color: #78350f; }
    .text-rose-600 { color: #e11d48; }
    
    .text-xs { font-size: 10px; }
    .text-sm { font-size: 11px; }
    .text-base { font-size: 13px; }
    .text-lg { font-size: 15px; }
    .text-xl { font-size: 17px; }
    .text-2xl { font-size: 19px; }
    .text-\\[9px\\] { font-size: 9px; }
    .text-\\[10px\\] { font-size: 10px; }
    .text-\\[11px\\] { font-size: 11px; }
    
    table {
      width: 100% !important;
      border-collapse: collapse;
      margin-top: 8px;
    }
    th, td {
      border: 1px solid #94a3b8;
      padding: 4px 6px;
    }
    thead {
      display: table-header-group;
    }
    tr {
      page-break-inside: avoid;
    }
    img {
      max-width: 100%;
      height: auto;
      object-fit: contain;
    }
    .break-inside-avoid {
      page-break-inside: avoid;
      break-inside: avoid;
    }
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  </style>
</head>
<body>
  <div style="width: 100%; max-width: 100%;">
    ${content}
  </div>
</body>
</html>`);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 400);
}

function printAnalysisDocument() {
  const printElem = document.getElementById(analysisSheetId.value);
  if (!printElem) {
    window.print();
    return;
  }

  const content = printElem.innerHTML;
  const printWindow = window.open('', '_blank', 'width=1200,height=850');
  if (!printWindow) {
    window.print();
    return;
  }

  const isF4 = selectedAnalysisPaperSize.value === 'f4';
  const paperCssSize = isF4 ? '330mm 215mm' : '297mm 210mm';
  const paperTitle = isF4 ? 'F4 / Folio' : 'A4';

  printWindow.document.open();
  printWindow.document.write(`<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Analisis Butir Soal & Daya Serap (${paperTitle}) - ${props.exam?.title || 'Ujian'}</title>
  <style>
    @page {
      size: ${paperCssSize};
      margin: 8mm 8mm 8mm 8mm;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    body {
      background: #ffffff !important;
      color: #0f172a;
      padding: 0;
      margin: 0;
      font-size: ${isF4 ? '10px' : '9.5px'};
      line-height: 1.35;
    }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
    .font-semibold { font-weight: 600; }
    .font-black { font-weight: 900; }
    .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
    .uppercase { text-transform: uppercase; }
    .underline { text-decoration: underline; }
    
    .flex { display: flex; }
    .flex-col { flex-direction: column; }
    .justify-between { justify-content: space-between; }
    .justify-center { justify-content: center; }
    .justify-end { justify-content: flex-end; }
    .items-center { align-items: center; }
    .items-start { align-items: flex-start; }
    .flex-1 { flex: 1 1 0%; }
    .flex-shrink-0 { flex-shrink: 0; }
    .gap-1 { gap: 4px; }
    .gap-1\\.5 { gap: 6px; }
    .gap-2 { gap: 8px; }
    .gap-3 { gap: 12px; }
    .gap-4 { gap: 16px; }
    .gap-5 { gap: 20px; }
    .gap-8 { gap: 32px; }
    .gap-x-8 { column-gap: 32px; }
    .gap-y-1\\.5 { row-gap: 6px; }
    .grid { display: grid; }
    .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
    .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
    .grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
    
    .w-full { width: 100%; }
    .w-8 { width: 32px; }
    .w-14 { width: 56px; }
    .w-16 { width: 64px; }
    .w-18 { width: 72px; }
    .w-20 { width: 80px; }
    .w-24 { width: 96px; }
    .w-28 { width: 112px; }
    .w-32 { width: 128px; }
    .w-36 { width: 144px; }
    .h-14 { height: 56px; }
    .h-16 { height: 64px; }
    .h-18 { height: 72px; }
    .h-20 { height: 80px; }
    .h-36 { height: 144px; }
    .min-w-\\[20px\\] { min-width: 20px; }
    
    .p-2 { padding: 8px; }
    .p-3 { padding: 12px; }
    .p-3\\.5 { padding: 14px; }
    .p-4 { padding: 16px; }
    .px-1 { padding-left: 4px; padding-right: 4px; }
    .px-2 { padding-left: 8px; padding-right: 8px; }
    .px-3 { padding-left: 12px; padding-right: 12px; }
    .py-0\\.5 { padding-top: 2px; padding-bottom: 2px; }
    .py-1 { padding-top: 4px; padding-bottom: 4px; }
    .py-1\\.5 { padding-top: 6px; padding-bottom: 6px; }
    .py-2 { padding-top: 8px; padding-bottom: 8px; }
    .pb-1\\.5 { padding-bottom: 6px; }
    .pb-2 { padding-bottom: 8px; }
    .pb-3 { padding-bottom: 12px; }
    .pt-1 { padding-top: 4px; }
    .pt-4 { padding-top: 16px; }
    .pt-5 { padding-top: 20px; }
    .mb-0\\.5 { margin-bottom: 2px; }
    .mb-4 { margin-bottom: 16px; }
    .mt-1 { margin-top: 4px; }
    .pr-6 { padding-right: 24px; }
    .pr-14 { padding-right: 56px; }
    
    .border { border: 1px solid #cbd5e1; }
    .border-b { border-bottom: 1px solid #cbd5e1; }
    .border-b-2 { border-bottom: 2px solid #0f172a; }
    .border-b-4 { border-bottom: 4px double #0f172a; }
    .border-dashed { border-style: dashed; }
    .border-double { border-style: double; }
    .border-slate-200 { border-color: #e2e8f0; }
    .border-slate-300 { border-color: #cbd5e1; }
    .border-slate-400 { border-color: #94a3b8; }
    .border-slate-800 { border-color: #1e293b; }
    .border-slate-900 { border-color: #0f172a; }
    .border-teal-300 { border-color: #5eead4; }
    .border-emerald-600 { border-color: #059669; }
    .border-rose-500 { border-color: #f43f5e; }
    
    .rounded { border-radius: 4px; }
    .rounded-sm { border-radius: 2px; }
    .rounded-t-sm { border-top-left-radius: 2px; border-top-right-radius: 2px; }
    .rounded-lg { border-radius: 8px; }
    .rounded-xl { border-radius: 12px; }
    .rounded-2xl { border-radius: 16px; }
    
    .bg-white { background-color: #ffffff; }
    .bg-slate-50 { background-color: #f8fafc; }
    .bg-slate-100 { background-color: #f1f5f9; }
    .bg-slate-200\\/90 { background-color: rgba(226, 232, 240, 0.9); }
    .bg-teal-800 { background-color: #115e59; }
    .bg-teal-100 { background-color: #ccfbf1; }
    .bg-teal-100\\/70 { background-color: rgba(204, 251, 241, 0.7); }
    .bg-emerald-500 { background-color: #10b981; }
    .bg-amber-500 { background-color: #f59e0b; }
    .bg-rose-500 { background-color: #ef4444; }
    
    .text-white { color: #ffffff; }
    .text-slate-500 { color: #64748b; }
    .text-slate-600 { color: #475569; }
    .text-slate-700 { color: #334155; }
    .text-slate-800 { color: #1e293b; }
    .text-slate-900 { color: #0f172a; }
    .text-teal-700 { color: #0f766e; }
    .text-teal-800 { color: #115e59; }
    .text-teal-900 { color: #134e4a; }
    .text-emerald-700 { color: #047857; }
    .text-amber-700 { color: #b45309; }
    .text-rose-700 { color: #be123c; }
    .text-rose-800 { color: #9f1239; }
    
    .text-xs { font-size: 10px; }
    .text-sm { font-size: 11px; }
    .text-base { font-size: 12px; }
    .text-lg { font-size: 14px; }
    .text-xl { font-size: 16px; }
    .text-2xl { font-size: 18px; }
    .text-\\[7\\.5px\\] { font-size: 7.5px; }
    .text-\\[8px\\] { font-size: 8px; }
    .text-\\[9px\\] { font-size: 9px; }
    .text-\\[10px\\] { font-size: 10px; }
    .text-\\[11px\\] { font-size: 11px; }
    
    .relative { position: relative; }
    .absolute { position: absolute; }
    .inset-x-0 { left: 0; right: 0; }
    .bottom-\\[70\\%\\] { bottom: 70%; }
    .bottom-\\[30\\%\\] { bottom: 30%; }
    .-left-6 { left: -24px; }
    .-top-2\\.5 { top: -10px; }
    .pointer-events-none { pointer-events: none; }
    
    table {
      width: 100% !important;
      table-layout: auto !important;
      border-collapse: collapse;
      margin-top: 6px;
      page-break-inside: auto;
    }
    tr {
      page-break-inside: avoid;
      page-break-after: auto;
    }
    th, td {
      border: 1px solid #94a3b8;
      padding: 3px 5px;
    }
    thead {
      display: table-header-group;
    }
    tfoot {
      display: table-footer-group;
    }
    img {
      max-width: 100%;
      height: auto;
      object-fit: contain;
    }
    .break-inside-avoid {
      page-break-inside: avoid;
      break-inside: avoid;
    }
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  </style>
</head>
<body>
  <div style="width: 100%; max-width: 100%;">
    ${content}
  </div>
</body>
</html>`);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 400);
}

async function exportToWord() {
  if (!props.exam) {
    toast.error('Data ujian tidak ditemukan.');
    return;
  }

  toast.info('Menyiapkan dokumen Word...');

  let logoData = null;
  try {
    const printImg = document.querySelector(`#${recapSheetId.value} img`);
    if (printImg && printImg.complete && printImg.naturalWidth > 0) {
      const canvas = document.createElement('canvas');
      canvas.width = printImg.naturalWidth;
      canvas.height = printImg.naturalHeight;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(printImg, 0, 0);
      const dataUrl = canvas.toDataURL('image/png');
      const b64 = dataUrl.split(',')[1];
      if (b64 && b64.length > 50) {
        logoData = { base64: b64, mime: 'image/png' };
      }
    }
  } catch (e) {
    console.warn('Canvas DOM capture error:', e);
  }

  if (!logoData) {
    const candidates = [
      props.schoolProfile?.app_logo_url,
      props.schoolProfile?.app_logo ? getImageUrl(props.schoolProfile.app_logo) : null,
      '/logo.png'
    ].filter(Boolean);

    for (const candidate of candidates) {
      try {
        const url = candidate.startsWith('http') ? candidate : (window.location.origin + candidate);
        const res = await fetch(url);
        if (res.ok) {
          const blob = await res.blob();
          const b64 = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onloadend = () => {
              const resStr = String(reader.result || '');
              resolve(resStr.split(',')[1] || '');
            };
            reader.readAsDataURL(blob);
          });
          if (b64 && b64.length > 50) {
            logoData = { base64: b64, mime: blob.type || 'image/png' };
            break;
          }
        }
      } catch (err) {
        console.warn('Fetch logo candidate error:', err);
      }
    }
  }

  let logoImgHtml = '';
  if (logoData) {
    logoImgHtml = `<img width="80" height="80" src="logo_madrasah.png" style="width:80px;height:80px;object-fit:contain;" alt="Logo Madrasah" />`;
  } else {
    logoImgHtml = `<div style="width: 70px; height: 70px; line-height: 70px; text-align: center; background-color: #000000; color: #ffffff; font-weight: 900; font-size: 16pt; border-radius: 6px;">MTS</div>`;
  }

  const qTypes = activeQuestionTypesList.value || [];
  let thQTypes = '';
  let thQTypesSub = '';

  if (qTypes.length > 0) {
    thQTypes = `<th colspan="${qTypes.length}" style="border: 1pt solid #000000; padding: 5pt 3pt; background-color: #cbd5e1; color: #000000; font-weight: 900; font-size: 8.5pt; text-align: center;">CAPAIAN NILAI PER BENTUK SOAL (POIN / MAKS)</th>`;
    thQTypesSub = qTypes.map(t => `
      <th style="border: 1pt solid #000000; padding: 4pt 2.5pt; background-color: #e2e8f0; text-align: center; font-size: 8pt; min-width: 60pt;">
        <div style="font-weight: 900; color: #000000;">${t.label}</div>
        <div style="font-size: 7.5pt; color: #000000; font-weight: normal;">(${t.count} Soal • Maks ${t.maxScore})</div>
      </th>
    `).join('');
  }

  const students = props.students || [];
  const kkm = Number(props.exam?.kkm || 75);

  let rowsHtml = '';
  if (students.length === 0) {
    const totalCols = 8 + qTypes.length;
    rowsHtml = `<tr><td colspan="${totalCols}" align="center" style="border: 1pt solid #000000; padding: 12pt; color: #000000; font-style: italic;">Tidak ada data siswa pada kelas ini.</td></tr>`;
  } else {
    rowsHtml = students.map((student, idx) => {
      const typeScoresHtml = qTypes.map(t => {
        const score = calculateStudentTypeScore(student, t);
        return `
          <td align="center" style="border: 1pt solid #000000; padding: 3.5pt 2pt; font-size: 8.5pt;">
            <div style="font-weight: 900; color: #000000;">${score.earned}</div>
            <div style="font-size: 7.5pt; color: #333333; font-weight: 600;">(${score.percentage}%)</div>
          </td>
        `;
      }).join('');

      const initialScore = student.total_score !== null ? student.total_score : '-';
      const initialScoreHtml = (student.total_score !== null && student.total_score < kkm)
        ? `<b style="color: #dc2626; font-size: 9pt;">${initialScore}</b>`
        : `<b style="color: #000000; font-size: 9pt;">${initialScore}</b>`;

      const remScore = (student.remedial_score !== null && student.remedial_score !== undefined && student.remedial_score !== '')
        ? `<b style="color: #0f766e; font-size: 9pt;">${student.remedial_score}</b>`
        : `<span style="color: #666666; font-weight: bold;">-</span>`;

      const finalGrade = getStudentFinalGrade(student);
      const status = getStudentPrintStatus(student);

      let statusHtml = '';
      if (status === 'TUNTAS') {
        statusHtml = '<b style="color: #15803d; font-size: 8.5pt;">TUNTAS</b>';
      } else if (status === 'TUNTAS (REM)') {
        statusHtml = '<b style="color: #0f766e; font-size: 8.5pt;">TUNTAS (REM)</b>';
      } else if (status === 'REMEDIAL') {
        statusHtml = '<b style="color: #dc2626; font-size: 8.5pt;">REMEDIAL</b>';
      } else {
        statusHtml = '<span style="color: #64748b; font-size: 8pt; font-weight: bold;">BELUM UJIAN</span>';
      }

      return `
        <tr style="background-color: ${idx % 2 === 1 ? '#f8fafc' : '#ffffff'};">
          <td align="center" style="border: 1pt solid #000000; padding: 3.5pt 2pt; font-weight: bold; color: #000000; font-size: 8.5pt;">${idx + 1}</td>
          <td align="center" style="border: 1pt solid #000000; padding: 3.5pt 2pt; font-family: Courier, monospace; color: #000000; font-size: 8.5pt;">${student.nisn || '-'}</td>
          <td style="border: 1pt solid #000000; padding: 3.5pt 4pt; font-weight: bold; color: #000000; font-size: 8.5pt;">${student.name}</td>
          <td align="center" style="border: 1pt solid #000000; padding: 3.5pt 2pt; font-weight: bold; color: #000000; font-size: 8.5pt;">${student.gender || '-'}</td>
          ${typeScoresHtml}
          <td align="center" style="border: 1pt solid #000000; padding: 3.5pt 2pt;">${initialScoreHtml}</td>
          <td align="center" style="border: 1pt solid #000000; padding: 3.5pt 2pt;">${remScore}</td>
          <td align="center" style="border: 1pt solid #000000; padding: 3.5pt 2pt; font-weight: 900; background-color: #f1f5f9; color: #000000; font-size: 9.5pt;">${finalGrade}</td>
          <td align="center" style="border: 1pt solid #000000; padding: 3.5pt 2pt;">${statusHtml}</td>
        </tr>
      `;
    }).join('');
  }

  const isF4 = selectedPaperSize.value === 'f4';
  const paperTag = isF4 ? 'F4' : 'A4';
  const wordPageWidth = isF4 ? '330mm' : '297mm';
  const wordPageHeight = isF4 ? '215mm' : '210mm';

  const wordHtml = `
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="utf-8">
  <title>Rekapitulasi Nilai Asesmen (${paperTag})</title>
  <!--[if gte mso 9]>
  <xml>
    <w:WordDocument>
      <w:View>Print</w:View>
      <w:Zoom>100</w:Zoom>
      <w:DoNotOptimizeForBrowser/>
    </w:WordDocument>
  </xml>
  <![endif]-->
  <style>
    @page Section1 {
      size: ${wordPageWidth} ${wordPageHeight};
      mso-page-orientation: landscape;
      margin: 10mm 10mm 10mm 10mm;
      mso-header-margin: 0mm;
      mso-footer-margin: 0mm;
      mso-paper-source: 0;
    }
    div.Section1 { page: Section1; }
    body {
      font-family: Arial, sans-serif;
      font-size: 8.5pt;
      color: #000000;
      line-height: 1.25;
      background-color: #ffffff;
    }
    table {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
      width: 100%;
    }
    th, td {
      border: 1pt solid #000000;
      color: #000000;
      mso-line-height-rule: exactly;
    }
  </style>
</head>
<body>
  <div class="Section1">
    <table width="100%" style="border-bottom: 3.5pt double #000000; border-top: none; border-left: none; border-right: none; margin-bottom: 8pt; padding-bottom: 4pt;">
      <tr>
        <td width="90" align="center" valign="middle" style="border: none; padding-right: 8pt;">
          ${logoImgHtml}
        </td>
        <td align="center" valign="middle" style="border: none; color: #000000;">
          <div style="font-size: 9pt; font-weight: bold; letter-spacing: 1.5pt; text-transform: uppercase; color: #000000;">
            ${props.schoolProfile?.school_foundation || 'YAYASAN PENDIDIKAN ISLAM AL-HASANAH'}
          </div>
          <div style="font-size: 16pt; font-weight: 900; text-transform: uppercase; margin: 1pt 0; color: #000000;">
            ${props.schoolProfile?.school_name || 'MADRASAH TSANAWIYAH AL - HASANAH'}
          </div>
          <div style="font-size: 8.5pt; font-weight: bold; color: #000000;">
            ${props.schoolProfile?.school_tagline || 'Madrasah Tsanawiyah Al - Hasanah Ciomas'} • Status: ${props.schoolProfile?.school_accreditation || 'TERAKREDITASI A'}
          </div>
          <div style="font-size: 8pt; color: #000000;">
            ${props.schoolProfile?.school_address || 'Jl. Ciapus Sukamakmur No.05, Ciomas, Bogor'}
          </div>
          <div style="font-size: 7.5pt; font-family: Courier, monospace; color: #000000;">
            Telp: ${props.schoolProfile?.school_phone || '081617666017'} • Email: ${props.schoolProfile?.school_email || 'mtsalhasanah.ciomas@gmail.com'}
          </div>
        </td>
      </tr>
    </table>

    <div style="text-align: center; margin: 4pt 0 7pt 0;">
      <div style="font-size: 11pt; font-weight: 900; text-decoration: underline; text-transform: uppercase; color: #000000;">
        LEMBAR REKAPITULASI CAPAIAN NILAI ASESMEN PER BENTUK SOAL
      </div>
      <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; margin-top: 1pt; color: #000000;">
        ${getExamTypeFullName(props.exam.exam_type)} • SEMESTER ${formatSemester(props.exam.semester || props.exam.academic_year?.semester)} • TAHUN PELAJARAN ${formatAcademicYear(props.exam)}
      </div>
    </div>

    <table width="100%" style="border: 1pt solid #000000; background-color: #f8fafc; margin-bottom: 7pt; font-size: 8pt;">
      <tr>
        <td width="50%" style="border: none; padding: 4pt 6pt; vertical-align: top; color: #000000;">
          <table width="100%" style="border: none;">
            <tr><td width="110" style="border:none; font-weight: bold; color: #000000;">Mata Pelajaran</td><td width="10" style="border:none;">:</td><td style="border:none; font-weight: 900; color: #000000;">${props.exam.subject?.name || '-'}</td></tr>
            <tr><td style="border:none; font-weight: bold; color: #000000;">Kelas / Rombel</td><td style="border:none;">:</td><td style="border:none; font-weight: 900; color: #000000;">Kelas ${props.exam.class_room?.name || '-'}</td></tr>
            <tr><td style="border:none; font-weight: bold; color: #000000;">Guru Pengampu</td><td style="border:none;">:</td><td style="border:none; color: #000000;">${props.exam.teacher?.full_name || props.exam.teacher?.name || '-'}</td></tr>
            <tr><td style="border:none; font-weight: bold; color: #000000;">Nama Paket Ujian</td><td style="border:none;">:</td><td style="border:none; color: #000000;">${props.exam.title}</td></tr>
          </table>
        </td>
        <td width="50%" style="border: none; padding: 4pt 6pt; vertical-align: top; color: #000000;">
          <table width="100%" style="border: none;">
            <tr><td width="120" style="border:none; font-weight: bold; color: #000000;">Jenis Asesmen</td><td width="10" style="border:none;">:</td><td style="border:none; font-weight: 900; color: #000000;">${getExamTypeFullName(props.exam.exam_type)}</td></tr>
            <tr><td style="border:none; font-weight: bold; color: #000000;">KKM / KKTP</td><td style="border:none;">:</td><td style="border:none; font-weight: 900; color: #000000;">${props.exam.kkm}</td></tr>
            <tr><td style="border:none; font-weight: bold; color: #000000;">Bobot Penilaian</td><td style="border:none;">:</td><td style="border:none; color: #000000;">Objektif: ${props.exam.pg_weight}% | Uraian: ${props.exam.essay_weight}%</td></tr>
            <tr><td style="border:none; font-weight: bold; color: #000000;">Komposisi Soal</td><td style="border:none;">:</td><td style="border:none; font-weight: bold; color: #000000;">${props.exam.total_questions} Butir (${qTypes.map(t => `${t.count} ${t.label}`).join(', ')})</td></tr>
          </table>
        </td>
      </tr>
    </table>

    <table width="100%" style="border: 1pt solid #000000; margin-bottom: 7pt;">
      <thead>
        <tr style="background-color: #cbd5e1; text-align: center; font-weight: 900; font-size: 8pt; color: #000000;">
          <th rowspan="2" width="24" style="border: 1pt solid #000000; padding: 4pt 2pt;">No</th>
          <th rowspan="2" width="70" style="border: 1pt solid #000000; padding: 4pt 2pt;">NISN</th>
          <th rowspan="2" style="border: 1pt solid #000000; padding: 4pt 4pt; text-align: left;">Nama Lengkap Siswa</th>
          <th rowspan="2" width="24" style="border: 1pt solid #000000; padding: 4pt 2pt;">L/P</th>
          ${thQTypes}
          <th rowspan="2" width="40" style="border: 1pt solid #000000; padding: 4pt 2pt;">Nilai Asli</th>
          <th rowspan="2" width="40" style="border: 1pt solid #000000; padding: 4pt 2pt;">Nilai Rem.</th>
          <th rowspan="2" width="45" style="border: 1pt solid #000000; padding: 4pt 2pt; background-color: #cbd5e1;">Nilai Akhir</th>
          <th rowspan="2" width="55" style="border: 1pt solid #000000; padding: 4pt 2pt;">Keterangan</th>
        </tr>
        <tr style="background-color: #e2e8f0;">
          ${thQTypesSub}
        </tr>
      </thead>
      <tbody>
        ${rowsHtml}
      </tbody>
    </table>

    <div style="font-size: 8pt; font-weight: 900; text-transform: uppercase; margin-bottom: 2pt; color: #000000;">
      REKAPITULASI KETUNTASAN KLASIKAL:
    </div>
    <table width="100%" style="border: 1pt solid #000000; margin-bottom: 10pt; font-size: 8pt;">
      <tr align="center" style="background-color: #f8fafc;">
        <td width="25%" style="border: 1pt solid #000000; padding: 3pt;">
          <span style="font-size: 7.5pt; color: #000000;">Total Siswa Peserta:</span><br>
          <b style="font-size: 9pt; color: #000000;">${printStats.value.participated} / ${printStats.value.total} Siswa</b>
        </td>
        <td width="25%" style="border: 1pt solid #000000; padding: 3pt;">
          <span style="font-size: 7.5pt; color: #000000;">Tuntas (Murni + Rem):</span><br>
          <b style="font-size: 9pt; color: #15803d;">${printStats.value.totalPassed} Siswa (${printStats.value.passPercentage}%)</b>
        </td>
        <td width="25%" style="border: 1pt solid #000000; padding: 3pt;">
          <span style="font-size: 7.5pt; color: #000000;">Perlu Remedial:</span><br>
          <b style="font-size: 9pt; color: #dc2626;">${printStats.value.remedialCount} Siswa</b>
        </td>
        <td width="25%" style="border: 1pt solid #000000; padding: 3pt;">
          <span style="font-size: 7.5pt; color: #000000;">Rata-rata / Tertinggi / Terendah:</span><br>
          <b style="font-size: 9pt; color: #000000;">${printStats.value.avgScore} / ${printStats.value.maxScore} / ${printStats.value.minScore}</b>
        </td>
      </tr>
    </table>

    <table width="100%" style="border: none; margin-top: 10pt;">
      <tr>
        <td width="50%" style="border: none;"></td>
        <td width="50%" align="right" style="border: none; font-size: 8.5pt; color: #000000;">
          Ciomas, ${getPrintDateFormatted()}
        </td>
      </tr>
    </table>
    <table width="100%" style="border: none; margin-top: 4pt;">
      <tr>
        <td width="50%" align="center" valign="top" style="border: none; font-size: 9pt; color: #000000;">
          <b style="color: #000000;">Mengetahui,</b><br>
          <span style="font-weight: bold; color: #000000;">Kepala MTs Al - Hasanah</span><br><br><br><br><br>
          <b style="text-decoration: underline; font-size: 10pt; color: #000000; font-weight: 900;">${props.schoolProfile?.principal_name || 'Kepala Madrasah'}</b><br>
          <div style="font-family: Arial, monospace; font-size: 8.5pt; font-weight: bold; color: #000000; margin-top: 2pt;">NIP: ${props.schoolProfile?.principal_nip || '-'}</div>
        </td>
        <td width="50%" align="center" valign="top" style="border: none; font-size: 9pt; color: #000000;">
          <b style="color: #000000;">Guru Pengampu,</b><br>
          <span style="font-weight: bold; color: #000000;">Mata Pelajaran ${props.exam.subject?.name || ''}</span><br><br><br><br><br>
          <b style="text-decoration: underline; font-size: 10pt; color: #000000; font-weight: 900;">${props.exam.teacher?.full_name || props.exam.teacher?.name || 'Guru Mata Pelajaran'}</b><br>
          <div style="font-family: Arial, monospace; font-size: 8.5pt; font-weight: bold; color: #000000; margin-top: 2pt;">NIP: ${props.exam.teacher?.nip || '-'}</div>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
  `;

  try {
    const boundary = "----=_NextPart_SiakadMts_Document_" + Date.now();
    let mhtml = "";

    mhtml += "MIME-Version: 1.0\r\n";
    mhtml += `Content-Type: multipart/related; boundary="${boundary}"\r\n\r\n`;

    mhtml += `--${boundary}\r\n`;
    mhtml += "Content-Type: text/html; charset=utf-8\r\n";
    mhtml += "Content-Transfer-Encoding: 8bit\r\n\r\n";
    mhtml += wordHtml + "\r\n\r\n";

    if (logoData && logoData.base64) {
      const wrappedB64 = logoData.base64.replace(/(.{76})/g, "$1\r\n");
      mhtml += `--${boundary}\r\n`;
      mhtml += `Content-Type: ${logoData.mime || 'image/png'}\r\n`;
      mhtml += "Content-Transfer-Encoding: base64\r\n";
      mhtml += "Content-Location: logo_madrasah.png\r\n\r\n";
      mhtml += wrappedB64 + "\r\n\r\n";
    }

    mhtml += `--${boundary}--\r\n`;

    const blob = new Blob([mhtml], {
      type: 'application/msword;charset=utf-8'
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    const safeMapel = (props.exam.subject?.name || 'Mapel').replace(/[^a-zA-Z0-9_-]/g, '_');
    const safeKelas = (props.exam.class_room?.name || 'Kelas').replace(/[^a-zA-Z0-9_-]/g, '_');
    a.href = url;
    a.download = `Rekap_Nilai_${safeMapel}_${safeKelas}_${paperTag}.doc`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    toast.success(`Lembar rekap format ${paperTag} berhasil diunduh ke Word (.doc)`);
  } catch (err) {
    console.error('Word export error:', err);
    toast.error('Gagal mengunduh file Word.');
  }
}
</script>
