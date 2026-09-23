<template>
  <div class="space-y-4 sm:space-y-6 font-inter print-container">
    <!-- TOP HEADER (No Print) -->
    <div class="bg-white rounded-2xl md:rounded-[2rem] p-4 sm:p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
      <div class="flex items-start sm:items-center gap-3">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center shadow-lg shadow-emerald-600/20 flex-shrink-0 mt-0.5 sm:mt-0">
          <BookOpenCheck class="w-5 h-5 sm:w-6 sm:h-6" />
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <h1 class="text-base sm:text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Rapor Tengah Semester (ASTS)</h1>
            <span
              class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide border shadow-2xs"
              :class="activeSemester === 'genap' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200'"
            >
              ASTS {{ activeSemester === 'genap' ? 'Genap' : 'Ganjil' }}
            </span>
          </div>
          <p class="text-[11px] sm:text-xs text-slate-500 mt-0.5 font-medium leading-relaxed">
            Ledger nilai kolektif kelas terintegrasi langsung dengan koreksi ujian, catatan wali kelas, dan cetak rapor resmi.
          </p>
        </div>
      </div>

      <!-- Quick Semester Toggle & Year Badge -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-shrink-0">
        <div class="grid grid-cols-2 p-1 bg-slate-100/90 rounded-xl border border-slate-200/60 shadow-2xs">
          <button
            type="button"
            @click="setSemester('ganjil')"
            :class="activeSemester === 'ganjil' ? 'bg-white text-emerald-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3 py-2 sm:px-3.5 sm:py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <span>📘 ASTS Ganjil</span>
          </button>
          <button
            type="button"
            @click="setSemester('genap')"
            :class="activeSemester === 'genap' ? 'bg-white text-amber-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3 py-2 sm:px-3.5 sm:py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <span>📙 ASTS Genap</span>
          </button>
        </div>

        <div v-if="activeYear" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 font-mono flex items-center justify-center sm:justify-start gap-1.5 flex-shrink-0">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>T.A. {{ activeYear.year || '2026/2027' }}</span>
        </div>
      </div>
    </div>

    <!-- FILTER & SUB-TAB NAVIGATION BAR (No Print) -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
      <!-- Class Selector -->
      <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 flex-1">
        <div class="w-full sm:w-72 space-y-1">
          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">
            {{ isHomeroomOnly ? 'Kelas Binaan (Wali Kelas)' : 'Pilih Kelas' }}
          </label>
          <select
            v-model="selectedClassId"
            @change="fetchLedger"
            :disabled="isHomeroomOnly && classes.length === 1"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer shadow-2xs disabled:opacity-90 disabled:cursor-not-allowed"
          >
            <option v-if="!isHomeroomOnly" value="">-- Pilih Kelas --</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">
              {{ isHomeroomOnly ? '⭐ ' : '' }}Kelas {{ c.name }} (Tingkat {{ c.grade_level }}) — {{ c.students_count || 0 }} Siswa
            </option>
          </select>
        </div>

        <div v-if="ledgerData?.class?.homeroom_teacher" class="text-xs text-slate-500 sm:pt-4 flex items-center gap-1.5">
          <span class="text-slate-400 font-medium">Wali Kelas:</span>
          <strong class="text-slate-800 font-bold bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200/60">{{ ledgerData.class.homeroom_teacher.full_name }}</strong>
        </div>
      </div>

      <!-- Sub-Tabs: Ledger vs Cetak Rapor (Grid on mobile, flex on desktop) -->
      <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl border border-slate-200/80 flex-shrink-0">
        <button
          type="button"
          @click="activeSubTab = 'ledger'"
          :class="activeSubTab === 'ledger' ? 'bg-white text-emerald-700 shadow-xs font-black' : 'text-slate-500 font-bold hover:text-slate-800'"
          class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5 sm:gap-2 cursor-pointer text-center"
        >
          <TableProperties class="w-4 h-4 text-emerald-600 flex-shrink-0" />
          <span class="truncate">Ledger Nilai</span>
        </button>

        <button
          type="button"
          @click="activeSubTab = 'print'"
          :class="activeSubTab === 'print' ? 'bg-white text-emerald-700 shadow-xs font-black' : 'text-slate-500 font-bold hover:text-slate-800'"
          class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg text-xs transition-all flex items-center justify-center gap-1.5 sm:gap-2 cursor-pointer text-center"
        >
          <Printer class="w-4 h-4 text-emerald-600 flex-shrink-0" />
          <span class="truncate">Cetak Rapor</span>
        </button>
      </div>
    </div>

    <!-- LOADING STATE -->
    <div v-if="loading" class="bg-white rounded-[2rem] p-16 text-center text-slate-400 text-xs font-medium border border-slate-100 no-print">
      <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-3"></div>
      Memuat data Rapor ASTS...
    </div>

    <!-- KHUSUS JIKA BUKAN WALI KELAS ATAU BELUM MEMILIKI KELAS BINAAN -->
    <div v-else-if="classes.length === 0" class="bg-white rounded-[2rem] p-12 text-center border border-amber-200/80 shadow-sm space-y-4 no-print max-w-xl mx-auto my-8">
      <div class="w-16 h-16 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto shadow-sm">
        <GraduationCap class="w-8 h-8" />
      </div>
      <div>
        <h3 class="text-lg font-black text-slate-800 font-lexend uppercase tracking-wide">Menu Khusus Wali Kelas</h3>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
          Akun Anda saat ini belum tercatat sebagai Wali Kelas aktif di rombel kelas manapun. Menu Rapor ASTS hanya dapat diakses dan dikelola oleh Guru yang ditugaskan sebagai Wali Kelas.
        </p>
      </div>
      <div class="pt-2">
        <RouterLink
          to="/teacher/dashboard"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-md shadow-emerald-600/20"
        >
          Kembali ke Dashboard
        </RouterLink>
      </div>
    </div>

    <!-- EMPTY SELECT WARNING -->
    <div v-else-if="!selectedClassId" class="bg-white rounded-[2rem] p-16 text-center border border-slate-100 space-y-3 no-print">
      <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto shadow-sm">
        <GraduationCap class="w-7 h-7" />
      </div>
      <h3 class="text-base font-black text-slate-800 font-lexend">Silakan Pilih Kelas Terlebih Dahulu</h3>
      <p class="text-xs text-slate-500 max-w-md mx-auto">
        Pilih kelas pada menu filter di atas untuk melihat ledger nilai ASTS, status setoran nilai guru mapel, atau mencetak lembar rapor siswa.
      </p>
    </div>

    <!-- MAIN CONTENT -->
    <div v-else class="space-y-6">
      <!-- ==================== SUB-TAB 1: LEDGER NILAI KELAS ==================== -->
      <div v-if="activeSubTab === 'ledger'" class="space-y-4 no-print">
        <!-- Action Toolbar: Auto-Pull Scores & Export -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          <div class="flex items-center gap-2 flex-wrap text-xs">
            <span class="font-black text-slate-700">Status Pengumpulan Nilai:</span>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              {{ subjectsWithScoresCount }} / {{ subjectsList.length }} Mapel Terisi
            </span>
            <span class="text-slate-400">&bull; {{ ledgerStudents.length }} Siswa</span>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 lg:flex lg:items-center gap-2 w-full lg:w-auto">
            <!-- Smart Rank Adjuster Button -->
            <button
              type="button"
              @click="openRankModal"
              class="px-3 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer active:scale-95"
              title="Atur peringkat juara siswa atau selaraskan nilai secara otomatis"
            >
              <Trophy class="w-3.5 h-3.5 text-amber-200 flex-shrink-0" />
              <span>Atur Peringkat</span>
            </button>

            <!-- 1-Click Auto Pull Button with Source Selection -->
            <button
              type="button"
              @click="openPullModal"
              :disabled="pullingScores"
              class="px-3.5 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 active:scale-95"
              title="Tarik otomatis nilai dari modul Koreksi Ujian (bisa memilih Nilai Jadi standar rapor atau Nilai Asli)"
            >
              <Sparkles v-if="!pullingScores" class="w-3.5 h-3.5 text-amber-200 flex-shrink-0" />
              <div v-else class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full flex-shrink-0"></div>
              <span>{{ pullingScores ? 'Menarik...' : '⚡ Tarik Nilai' }}</span>
            </button>

            <!-- Reset / Kosongkan Nilai Button -->
            <button
              type="button"
              @click="openResetModal()"
              :disabled="subjectsWithScoresCount === 0"
              class="px-3 py-2 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-2xs active:scale-95"
              title="Kosongkan atau reset nilai rapor (per mata pelajaran atau seluruh kelas)"
            >
              <RotateCcw class="w-3.5 h-3.5 text-rose-600 flex-shrink-0" />
              <span>Reset Nilai</span>
            </button>

            <!-- Export Excel Ledger -->
            <button
              type="button"
              @click="exportLedgerExcel"
              class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
              title="Export ledger nilai ke format file Excel"
            >
              <Download class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" />
              <span>Export Excel</span>
            </button>

            <!-- Cetak Ledger Nilai Lengkap (Matriks Mapel) - Asli -->
            <button
              type="button"
              @click="printLedgerSheet('original')"
              :disabled="!ledgerStudents.length"
              class="px-3 py-2 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 shadow-sm active:scale-95"
              title="Cetak lembar rekap ledger nilai seluruh mata pelajaran (Landscape) berdasarkan nilai asli murni"
            >
              <TableProperties class="w-3.5 h-3.5 text-emerald-200 flex-shrink-0" />
              <span>Cetak Ledger Asli</span>
            </button>

            <!-- Cetak Ledger Nilai Lengkap (Matriks Mapel) - Diatur -->
            <button
              type="button"
              @click="printLedgerSheet('adjusted')"
              :disabled="!ledgerStudents.length"
              class="px-3 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 shadow-sm active:scale-95"
              title="Cetak lembar rekap ledger nilai seluruh mata pelajaran (Landscape) hasil penataan wali kelas"
            >
              <TableProperties class="w-3.5 h-3.5 text-amber-200 flex-shrink-0" />
              <span>Cetak Ledger Diatur</span>
            </button>
          </div>
        </div>

        <!-- Subject Deposit Status Badges (Collapsible on Mobile) -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-2.5">
          <div class="flex items-center justify-between cursor-pointer select-none" @click="showSubjectStatuses = !showSubjectStatuses">
            <div class="flex items-center gap-2">
              <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Status Setor Guru Mapel:</span>
              <span class="text-xs font-bold text-slate-700">
                {{ subjectsWithScoresCount }} dari {{ subjectsList.length }} Mapel Sudah Disetor
              </span>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-600 text-xs flex items-center gap-1 font-bold">
              <span>{{ showSubjectStatuses ? 'Tutup' : 'Lihat Detail Mapel' }}</span>
              <ChevronUp v-if="showSubjectStatuses" class="w-3.5 h-3.5" />
              <ChevronDown v-else class="w-3.5 h-3.5" />
            </button>
          </div>

          <div v-show="showSubjectStatuses" class="flex items-center gap-2 flex-wrap pt-1 border-t border-slate-100">
            <div
              v-for="st in subjectStatuses"
              :key="st.subject_id"
              class="group px-2.5 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5"
              :class="st.has_scores ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200'"
              :title="st.has_scores ? `Sudah disetorkan (${st.synced_count} nilai) • Klik tombol silang untuk reset nilai mapel ini` : 'Belum ada nilai yang disetorkan untuk mapel ini'"
            >
              <span class="w-2 h-2 rounded-full" :class="st.has_scores ? 'bg-emerald-500' : 'bg-slate-300'"></span>
              <span>{{ st.subject_name }}</span>
              <span v-if="st.has_scores" class="text-[10px] text-emerald-600 font-mono">✓</span>
              <span v-else class="text-[10px] text-slate-400 font-mono">⏳</span>

              <button
                v-if="st.has_scores"
                type="button"
                @click.stop="openResetModal(st.subject_id)"
                class="ml-0.5 p-0.5 rounded hover:bg-rose-100 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer"
                title="Kosongkan nilai mapel ini"
              >
                <X class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>

        <!-- View Mode Switcher Toolbar (Card View vs Matrix Table) -->
        <div class="flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-2">
            <h3 class="text-xs font-black text-slate-800 font-lexend uppercase tracking-wider">Data Peserta Didik</h3>
            <span class="text-xs text-slate-400 font-medium">({{ ledgerStudents.length }} Siswa)</span>
          </div>

          <div class="flex p-1 bg-slate-100 rounded-xl border border-slate-200/80 shadow-2xs">
            <button
              type="button"
              @click="ledgerViewMode = 'cards'"
              :class="ledgerViewMode === 'cards' ? 'bg-white text-emerald-800 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800'"
              class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5"
              title="Tampilan Kartu Siswa (Ramah Mobile & HP)"
            >
              <LayoutGrid class="w-3.5 h-3.5" />
              <span>Mode Kartu Siswa</span>
            </button>
            <button
              type="button"
              @click="ledgerViewMode = 'table'"
              :class="ledgerViewMode === 'table' ? 'bg-white text-emerald-800 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800'"
              class="px-3 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5"
              title="Tampilan Tabel Matrix Lengkap"
            >
              <TableProperties class="w-3.5 h-3.5" />
              <span>Mode Tabel Matrix</span>
            </button>
          </div>
        </div>

        <!-- ================= VIEW 1: MOBILE-FRIENDLY CARD VIEW ================= -->
        <div v-if="ledgerViewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5">
          <div
            v-for="(st, idx) in ledgerStudents"
            :key="'card-'+st.student_id"
            class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:shadow-md transition-all space-y-3"
          >
            <!-- Top Row: Rank & Name -->
            <div class="flex items-start justify-between gap-2.5">
              <div class="flex items-center gap-2.5 min-w-0">
                <!-- Rank Avatar -->
                <div
                  class="w-10 h-10 rounded-xl flex items-center justify-center font-lexend font-black text-sm flex-shrink-0 shadow-xs"
                  :class="st.rank === 1 ? 'bg-amber-400 text-white shadow-amber-200' : (st.rank === 2 ? 'bg-slate-300 text-slate-800' : (st.rank === 3 ? 'bg-amber-700 text-white' : 'bg-slate-100 text-slate-700'))"
                >
                  <span v-if="st.rank === 1">🥇</span>
                  <span v-else-if="st.rank === 2">🥈</span>
                  <span v-else-if="st.rank === 3">🥉</span>
                  <span v-else>#{{ st.rank || (idx + 1) }}</span>
                </div>
                <div class="min-w-0">
                  <div class="font-black text-slate-900 font-lexend text-xs sm:text-sm truncate">
                    {{ st.full_name }}
                  </div>
                  <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1.5 mt-0.5">
                    <span>NISN: {{ st.nisn || '-' }}</span>
                    <span>&bull;</span>
                    <span class="font-bold text-slate-600">{{ st.gender === 'L' ? 'Laki-laki' : (st.gender === 'P' ? 'Perempuan' : st.gender) }}</span>
                  </div>
                </div>
              </div>

              <!-- Manual Rank Indicator -->
              <span v-if="st.is_manual_rank" class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-900 border border-amber-300 whitespace-nowrap">
                Rank Manual
              </span>
            </div>

            <!-- Stats Pill: Total & Average & Presensi -->
            <div class="grid grid-cols-3 gap-1.5 bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-center">
              <div>
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Total Nilai</span>
                <span class="text-xs font-black font-mono text-slate-800">{{ Math.round(Number(st.total_score) || 0) }}</span>
              </div>
              <div class="border-x border-slate-200">
                <span class="block text-[9px] font-bold text-emerald-600 uppercase">Rata-Rata</span>
                <span class="text-xs font-black font-mono text-emerald-700">{{ Number(st.average_score || 0).toFixed(2) }}</span>
              </div>
              <div>
                <span class="block text-[9px] font-bold text-indigo-600 uppercase">Presensi (S/I/A)</span>
                <span class="text-[10px] font-bold font-mono text-slate-700">
                  {{ st.sick_count || 0 }} / {{ st.permission_count || 0 }} / {{ st.unexcused_count || 0 }}
                </span>
              </div>
            </div>

            <!-- Catatan Wali Preview -->
            <div class="text-[11px] bg-slate-50/70 p-2.5 rounded-xl border border-slate-200/60 flex items-start justify-between gap-2">
              <div class="min-w-0 flex-1">
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Catatan Wali Kelas:</span>
                <p class="text-slate-700 italic truncate text-[10.5px]">
                  {{ st.homeroom_notes ? `"${st.homeroom_notes}"` : '(Belum ada catatan)' }}
                </p>
              </div>
              <button
                type="button"
                @click="openNotesModal(st)"
                class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 border border-indigo-200 px-2 py-1 rounded-lg flex-shrink-0 cursor-pointer"
              >
                ✏️ Edit
              </button>
            </div>

            <!-- Expandable Subject Scores Breakdown -->
            <div class="pt-0.5 space-y-2">
              <button
                type="button"
                @click="toggleCardDetail(st.student_id)"
                class="w-full text-center py-1.5 text-[11px] font-bold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors flex items-center justify-center gap-1 cursor-pointer border border-slate-100"
              >
                <span>{{ expandedCards[st.student_id] ? 'Tutup Rincian Nilai Mapel' : `Lihat Nilai ${subjectsList.length} Mapel` }}</span>
                <ChevronDown v-if="!expandedCards[st.student_id]" class="w-3.5 h-3.5 text-slate-400" />
                <ChevronUp v-else class="w-3.5 h-3.5 text-slate-400" />
              </button>

              <div v-if="expandedCards[st.student_id]" class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 pt-1 border-t border-slate-100">
                <div
                  v-for="sbj in subjectsList"
                  :key="'st-score-'+st.student_id+'-'+sbj.id"
                  class="p-1.5 rounded-lg border text-[10px] flex items-center justify-between"
                  :class="st.scores?.[sbj.id] ? (st.scores[sbj.id].score >= (st.scores[sbj.id].kkm || 75) ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200') : 'bg-slate-50 border-slate-200'"
                >
                  <span class="truncate font-medium text-slate-700" :title="sbj.name">{{ sbj.code || getSubjectShort(sbj.name) }}</span>
                  <span
                    class="font-black font-mono ml-1 px-1 rounded text-[10px]"
                    :class="st.scores?.[sbj.id] ? (st.scores[sbj.id].score >= (st.scores[sbj.id].kkm || 75) ? 'text-emerald-700' : 'text-rose-700') : 'text-slate-400'"
                  >
                    {{ st.scores?.[sbj.id]?.score ?? '-' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Action button -->
            <button
              type="button"
              @click="previewSingleStudent(st.student_id)"
              class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer active:scale-95"
            >
              <Eye class="w-3.5 h-3.5" />
              <span>Buka & Cetak Rapor Siswa</span>
            </button>
          </div>

          <div v-if="ledgerStudents.length === 0" class="col-span-full py-12 text-center text-slate-400 font-medium bg-white rounded-3xl border border-slate-100">
            Belum ada siswa terdaftar pada kelas ini.
          </div>
        </div>

        <!-- ================= VIEW 2: FULL MATRIX TABLE ================= -->
        <div v-else class="space-y-2">
          <!-- Mobile Scroll Hint -->
          <div class="sm:hidden text-center text-[11px] font-bold text-emerald-800 bg-emerald-50/80 py-2 px-3 rounded-xl border border-emerald-200 flex items-center justify-center gap-1.5">
            <span>👉 Geser tabel ke samping untuk melihat seluruh nilai mapel 👈</span>
          </div>

          <div class="bg-white rounded-2xl md:rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-700 border-collapse">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-200">
                  <tr>
                    <th class="px-3 py-3 w-10 text-center sticky left-0 bg-slate-50 z-10">No</th>
                    <th class="px-4 py-3 min-w-[180px] sticky left-10 bg-slate-50 z-10">Nama Siswa</th>
                    <th class="px-2 py-3 w-10 text-center">L/P</th>

                    <!-- Dynamic Subject Headers -->
                    <th
                      v-for="sbj in subjectsList"
                      :key="'th-'+sbj.id"
                      class="px-2 py-3 text-center min-w-[75px] border-x border-slate-100"
                      :title="sbj.name"
                    >
                      <span class="block truncate font-extrabold text-slate-800">{{ sbj.code || getSubjectShort(sbj.name) }}</span>
                      <span class="block text-[8px] text-slate-400 font-normal">KKTP: {{ sbj.passing_grade || 75 }}</span>
                    </th>

                    <th class="px-3 py-3 text-center w-20 bg-slate-100/60 font-black text-slate-800">Total</th>
                    <th class="px-3 py-3 text-center w-20 bg-emerald-50/70 font-black text-emerald-900">Rata-rata</th>
                    <th class="px-2 py-3 text-center w-20 font-black text-amber-800 bg-amber-50/70">Peringkat</th>
                    <th class="px-3 py-3 text-center w-24">Catatan Wali</th>
                    <th class="px-3 py-3 text-center w-24">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr
                    v-for="(st, idx) in ledgerStudents"
                    :key="st.student_id"
                    class="hover:bg-slate-50/70 transition-colors text-xs"
                  >
                    <td class="px-3 py-3 text-center font-bold text-slate-400 sticky left-0 bg-white group-hover:bg-slate-50">{{ idx + 1 }}</td>
                    <td class="px-4 py-3 sticky left-10 bg-white group-hover:bg-slate-50 z-10">
                      <div class="font-bold text-slate-900 font-lexend truncate max-w-[200px]">{{ st.full_name }}</div>
                      <div class="text-[10px] text-slate-400 font-mono">NISN: {{ st.nisn || '-' }}</div>
                    </td>
                    <td class="px-2 py-3 text-center font-semibold text-slate-500">{{ st.gender }}</td>

                    <!-- Subject Score Cells -->
                    <td
                      v-for="sbj in subjectsList"
                      :key="'sc-'+st.student_id+'-'+sbj.id"
                      class="px-2 py-2 text-center border-x border-slate-100 font-mono"
                    >
                      <template v-if="st.scores?.[sbj.id]">
                        <span
                          class="px-1.5 py-0.5 rounded text-xs font-extrabold"
                          :class="st.scores[sbj.id].score >= (st.scores[sbj.id].kkm || 75) ? 'text-emerald-700 bg-emerald-50/60' : 'text-rose-700 bg-rose-50/60'"
                        >
                          {{ Math.round(Number(st.scores[sbj.id].score)) }}
                        </span>
                      </template>
                      <span v-else class="text-slate-300">-</span>
                    </td>

                    <!-- Total, Average & Rank -->
                    <td class="px-3 py-3 text-center font-bold font-mono bg-slate-50/50 text-slate-800">
                      {{ Math.round(Number(st.total_score) || 0) }}
                    </td>
                    <td class="px-3 py-3 text-center font-black font-mono bg-emerald-50/40 text-emerald-700">
                      {{ Number(st.average_score || 0).toFixed(2) }}
                    </td>
                    <td class="px-2 py-3 text-center font-black font-lexend bg-amber-50/40">
                      <div class="flex items-center justify-center gap-1">
                        <span v-if="st.rank === 1" class="text-xs">🥇</span>
                        <span v-else-if="st.rank === 2" class="text-xs">🥈</span>
                        <span v-else-if="st.rank === 3" class="text-xs">🥉</span>
                        <span class="text-amber-900 font-black">{{ st.rank }}</span>
                        <span v-if="st.is_manual_rank" class="text-[9px] px-1 bg-amber-200/80 text-amber-950 rounded font-normal" title="Peringkat Diatur Manual">m</span>
                      </div>
                    </td>

                    <!-- Notes Status -->
                    <td class="px-3 py-3 text-center">
                      <button
                        type="button"
                        @click="openNotesModal(st)"
                        class="px-2 py-1 rounded-lg text-[10px] font-bold transition-colors cursor-pointer"
                        :class="st.homeroom_notes ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                        :title="st.homeroom_notes ? st.homeroom_notes : 'Klik untuk mengisi catatan wali kelas'"
                      >
                        {{ st.homeroom_notes ? '✓ Ada Catatan' : '+ Isi Catatan' }}
                      </button>
                    </td>

                    <!-- Single Student Action -->
                    <td class="px-3 py-3 text-center">
                      <button
                        type="button"
                        @click="previewSingleStudent(st.student_id)"
                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[10px] transition-colors cursor-pointer flex items-center justify-center gap-1 mx-auto shadow-2xs"
                      >
                        <Eye class="w-3 h-3" />
                        <span>Lihat Rapor</span>
                      </button>
                    </td>
                  </tr>

                  <tr v-if="ledgerStudents.length === 0">
                    <td :colspan="6 + subjectsList.length" class="px-4 py-12 text-center text-slate-400 font-medium">
                      Belum ada siswa terdaftar pada kelas ini.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== SUB-TAB 2: PRATINJAU & CETAK RAPOR RESMI ==================== -->
      <div v-else-if="activeSubTab === 'print'" class="space-y-4">
        <!-- Print Control Bar (No Print) -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 no-print">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 flex-1">
            <!-- Mode Cetak (Full width on mobile) -->
            <div class="w-full sm:w-auto">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Mode Tampilan</label>
              <div class="grid grid-cols-2 sm:flex p-1 bg-slate-100 rounded-xl border border-slate-200 shadow-2xs">
                <button
                  type="button"
                  @click="setPrintMode('single')"
                  :class="printMode === 'single' ? 'bg-white text-emerald-800 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800'"
                  class="px-3 py-1.5 sm:py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 text-center"
                >
                  <Eye class="w-3.5 h-3.5 flex-shrink-0" />
                  <span class="truncate">Review Per Siswa</span>
                </button>
                <button
                  type="button"
                  @click="setPrintMode('batch')"
                  :class="printMode === 'batch' ? 'bg-white text-emerald-800 font-black shadow-xs' : 'text-slate-500 font-bold hover:text-slate-800'"
                  class="px-3 py-1.5 sm:py-1 rounded-lg text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 text-center"
                >
                  <Users class="w-3.5 h-3.5 flex-shrink-0" />
                  <span class="truncate">Cetak 1 Kelas ({{ ledgerStudents.length }})</span>
                </button>
              </div>
            </div>

            <!-- Paper Size -->
            <div class="w-full sm:w-auto">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Format Kertas</label>
              <select v-model="selectedPaperSize" class="w-full sm:w-auto bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2 sm:py-1.5 text-xs font-bold text-emerald-900 focus:outline-none cursor-pointer shadow-2xs">
                <option value="f4">📜 F4 / Folio (215 x 330 mm) - Standar Madrasah</option>
                <option value="a4">📄 A4 (210 x 297 mm)</option>
              </select>
            </div>
          </div>

          <!-- Action Buttons Group (Grid on mobile, flex on desktop) -->
          <div class="grid grid-cols-2 sm:flex sm:flex-wrap lg:items-center gap-2 w-full lg:w-auto">
            <!-- Quick Pull Scores -->
            <button
              type="button"
              @click="openPullModal"
              class="px-3 py-2 bg-teal-50 hover:bg-teal-100 border border-teal-200 text-teal-900 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
              title="Tarik nilai dari modul Koreksi Ujian"
            >
              <Sparkles class="w-3.5 h-3.5 text-teal-600 flex-shrink-0" />
              <span>Tarik Nilai</span>
            </button>

            <!-- Edit Notes Quick Trigger -->
            <button
              type="button"
              @click="openNotesModalForCurrentStudent"
              :disabled="loadingSingleReport || !singleReportData"
              class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-900 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-50"
              title="Edit kehadiran & catatan siswa"
            >
              <Pencil class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0" />
              <span class="truncate">Edit Catatan</span>
            </button>

            <!-- Adjust Rank Quick Trigger -->
            <button
              type="button"
              @click="openRankModal"
              class="px-3 py-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-900 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Trophy class="w-3.5 h-3.5 text-amber-600 flex-shrink-0" />
              <span>Atur Peringkat</span>
            </button>

            <!-- Atur Titimangsa Rapor -->
            <button
              type="button"
              @click="openTitimangsaModal"
              class="px-3 py-2 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-900 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
              title="Atur Tempat dan Tanggal Resmi Penerbitan Rapor ASTS"
            >
              <Calendar class="w-3.5 h-3.5 text-amber-600 flex-shrink-0" />
              <span>Titimangsa</span>
            </button>

            <!-- Tombol Cetak Peringkat Saja (Asli & Diatur) -->
            <button
              type="button"
              @click="printRankingSheet('original')"
              :disabled="!ledgerStudents.length"
              class="px-3 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer disabled:opacity-50 active:scale-95"
              title="Cetak lembar resmi daftar peringkat siswa berdasarkan nilai asli murni"
            >
              <Printer class="w-3.5 h-3.5 text-teal-200 flex-shrink-0" />
              <span>Peringkat Asli</span>
            </button>

            <button
              type="button"
              @click="printRankingSheet('adjusted')"
              :disabled="!ledgerStudents.length"
              class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer disabled:opacity-50 active:scale-95"
              title="Cetak lembar resmi daftar peringkat siswa hasil penataan wali kelas"
            >
              <Trophy class="w-3.5 h-3.5 text-amber-200 flex-shrink-0" />
              <span>Peringkat Diatur</span>
            </button>

            <!-- Trigger Print Rapor Siswa Asli Seperti Semula -->
            <button
              @click="triggerPrint"
              type="button"
              class="col-span-2 sm:col-span-1 px-5 py-2.5 sm:py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-2 shadow-md cursor-pointer active:scale-95"
            >
              <Printer class="w-4 h-4 text-emerald-400 flex-shrink-0" />
              <span>{{ printMode === 'batch' ? `Cetak 1 Kelas (${ledgerStudents.length} Siswa)` : 'Cetak Rapor Siswa Ini' }}</span>
            </button>
          </div>
        </div>

        <!-- BATCH MODE (Full Class Print) -->
        <div v-if="printMode === 'batch'" id="asts-report-batch-area" class="space-y-6">
          <div
            v-for="(rep, rIdx) in batchReportsList"
            :key="'batch-rep-'+rIdx"
            class="bg-white p-4 sm:p-7 rounded-2xl shadow-sm border border-slate-200 text-slate-900 space-y-4 print-page print-sheet overflow-x-auto"
          >
            <AstsReportSheet :report="rep" />
          </div>
          <div v-if="batchReportsList.length === 0" class="text-center py-12 text-slate-400 text-xs font-medium bg-white rounded-3xl border border-slate-100">
            Memuat seluruh lembar rapor siswa kelas {{ ledgerData?.class?.name }}...
          </div>
        </div>

        <!-- SINGLE MODE: LIVE REVIEW INTERACTIVE LAYOUT (SIDE-BY-SIDE ON DESKTOP, QUICK-NAVIGATOR ON MOBILE) -->
        <div v-else class="space-y-4">
          <!-- QUICK STUDENT NAVIGATOR BAR (SUPER MOBILE FRIENDLY) -->
          <div class="bg-white rounded-2xl p-3 sm:p-4 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
            <!-- Navigation Prev / Next & Dropdown -->
            <div class="flex items-center gap-2 flex-1 w-full sm:w-auto">
              <!-- Prev Button -->
              <button
                type="button"
                @click="goToPrevStudent"
                :disabled="currentStudentIndex <= 0"
                class="p-2.5 sm:px-3.5 sm:py-2 bg-slate-100 hover:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl text-xs font-bold text-slate-700 flex items-center gap-1 cursor-pointer transition-all flex-shrink-0 shadow-2xs"
                title="Siswa Sebelumnya"
              >
                <ChevronLeft class="w-4 h-4" />
                <span class="hidden md:inline">Sebelumnya</span>
              </button>

              <!-- Select Dropdown -->
              <div class="flex-1 min-w-0">
                <select
                  v-model="selectedStudentId"
                  @change="fetchSingleReport"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer truncate shadow-2xs font-lexend"
                >
                  <option v-for="(st, sIdx) in ledgerStudents" :key="'opt-st-'+st.student_id" :value="st.student_id">
                    #{{ st.rank || (sIdx + 1) }} — {{ st.full_name }} (Avg: {{ Number(st.average_score || 0).toFixed(2) }})
                  </option>
                </select>
              </div>

              <!-- Next Button -->
              <button
                type="button"
                @click="goToNextStudent"
                :disabled="currentStudentIndex >= ledgerStudents.length - 1"
                class="p-2.5 sm:px-3.5 sm:py-2 bg-slate-100 hover:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed rounded-xl text-xs font-bold text-slate-700 flex items-center gap-1 cursor-pointer transition-all flex-shrink-0 shadow-2xs"
                title="Siswa Berikutnya"
              >
                <span class="hidden md:inline">Berikutnya</span>
                <ChevronRight class="w-4 h-4" />
              </button>
            </div>

            <!-- Mobile Sidebar Toggle -->
            <div class="flex items-center justify-between sm:justify-end gap-2 flex-shrink-0">
              <span class="text-[11px] font-medium text-slate-400">
                Siswa <strong class="text-slate-700">{{ currentStudentIndex + 1 }}</strong> dari <strong class="text-slate-700">{{ ledgerStudents.length }}</strong>
              </span>
              <button
                type="button"
                @click="showMobileStudentList = !showMobileStudentList"
                class="lg:hidden px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold rounded-xl text-xs flex items-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <Users class="w-3.5 h-3.5 text-emerald-600" />
                <span>{{ showMobileStudentList ? 'Tutup Daftar' : 'Daftar Siswa' }}</span>
              </button>
            </div>
          </div>

          <!-- Main Layout Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            <!-- LEFT SIDEBAR: STUDENT LIST SELECTOR (Drawer on Mobile, Sidebar on Desktop) -->
            <div
              :class="showMobileStudentList ? 'block' : 'hidden lg:block'"
              class="lg:col-span-4 bg-white rounded-2xl md:rounded-[2rem] p-4 shadow-sm border border-slate-100 space-y-3 no-print"
            >
              <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                  <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider font-lexend">Pilih Peserta Didik</h3>
                  <p class="text-[10px] text-slate-400">Total {{ ledgerStudents.length }} siswa &bull; Urut Berdasarkan Nilai</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                  Live Review
                </span>
              </div>

              <!-- Student Search/Filter -->
              <div class="relative">
                <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
                <input
                  v-model="studentSearchQuery"
                  type="text"
                  placeholder="Cari nama atau NISN..."
                  class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                />
              </div>

              <!-- Scrollable Students List -->
              <div class="space-y-1.5 max-h-[600px] overflow-y-auto pr-1">
                <div
                  v-for="st in filteredLedgerStudents"
                  :key="'side-st-'+st.student_id"
                  @click="selectLiveStudent(st.student_id); showMobileStudentList = false"
                  class="p-2.5 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-2"
                  :class="selectedStudentId == st.student_id 
                    ? 'bg-emerald-50/80 border-emerald-300 shadow-sm ring-1 ring-emerald-400/50' 
                    : 'bg-white hover:bg-slate-50 border-slate-100'"
                >
                  <div class="flex items-center gap-2.5 min-w-0">
                    <!-- Rank Badge Icon -->
                    <div
                      class="w-8 h-8 rounded-xl flex items-center justify-center font-lexend font-black text-xs flex-shrink-0 shadow-2xs"
                      :class="st.rank === 1 ? 'bg-amber-400 text-white shadow-amber-200' : (st.rank === 2 ? 'bg-slate-300 text-slate-800' : (st.rank === 3 ? 'bg-amber-700 text-white' : 'bg-slate-100 text-slate-600'))"
                    >
                      <span v-if="st.rank === 1">🥇</span>
                      <span v-else-if="st.rank === 2">🥈</span>
                      <span v-else-if="st.rank === 3">🥉</span>
                      <span v-else>{{ st.rank || '-' }}</span>
                    </div>

                    <!-- Student Info -->
                    <div class="min-w-0">
                      <div class="text-xs font-bold text-slate-900 truncate font-lexend" :class="selectedStudentId == st.student_id ? 'text-emerald-950 font-black' : ''">
                        {{ st.full_name }}
                      </div>
                      <div class="text-[10px] text-slate-400 flex items-center gap-1.5 font-mono">
                        <span>NISN: {{ st.nisn || '-' }}</span>
                        <span>&bull;</span>
                        <span class="font-bold text-emerald-700">Avg: {{ Number(st.average_score || 0).toFixed(2) }}</span>
                      </div>
                    </div>
                  </div>

                  <ChevronRight class="w-4 h-4 text-slate-400 flex-shrink-0" :class="selectedStudentId == st.student_id ? 'text-emerald-600 translate-x-0.5' : ''" />
                </div>
              </div>
            </div>

            <!-- RIGHT PREVIEW PANEL: RENDERED RAPOR SHEET -->
            <div :class="showMobileStudentList ? 'hidden lg:flex' : 'flex'" class="lg:col-span-8 flex-col items-center w-full">
              <!-- Preview Status & Mobile Touch Hint -->
              <div class="w-full max-w-[210mm] flex items-center justify-between gap-2 pb-2 text-[11px] text-slate-400 no-print">
                <span class="flex items-center gap-1 text-slate-500 font-medium truncate">
                  <span>📄 Pratinjau Kertas {{ selectedPaperSize.toUpperCase() }}</span>
                  <span v-if="singleReportData?.city" class="hidden sm:inline">&bull; {{ singleReportData.city }}, {{ singleReportData.issued_date }}</span>
                </span>
                <div class="sm:hidden text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200 text-[10px]">
                  Geser ke samping ↔️
                </div>
              </div>

              <!-- Sheet Viewport (overflow-x-auto for smooth horizontal scroll on mobile) -->
              <div class="w-full overflow-x-auto flex justify-center pb-6">
                <div
                  id="asts-report-single-area"
                  class="bg-white p-4 sm:p-7 rounded-2xl shadow-md border border-slate-300 text-slate-900 print-sheet w-full max-w-[210mm] transition-all flex-shrink-0"
                >
                  <div v-if="loadingSingleReport" class="py-24 text-center text-slate-400 space-y-2">
                    <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto"></div>
                    <p class="text-xs font-medium">Memuat pratinjau lembar rapor siswa...</p>
                  </div>
                  <AstsReportSheet 
                    v-else-if="singleReportData" 
                    :report="singleReportData" 
                    :allow-edit="true" 
                    @edit-notes="openNotesModalForCurrentStudent" 
                    @edit-titimangsa="openTitimangsaModal"
                  />
                  <div v-else class="text-center py-24 text-slate-400 text-xs font-medium">
                    Pilih salah satu siswa di navigator atas atau daftar siswa untuk menampilkan rapor.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL ATUR PERINGKAT SISWA (SMART RANK ADJUSTER) -->
    <!-- MODAL ATUR PERINGKAT SISWA (USER-FRIENDLY & ANTI-PERINGKAT KEMBAR) -->
    <div v-if="showRankModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-6 no-print">
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
                  {{ rankEditList.length }} Siswa
                </span>
              </div>
              <p class="text-xs text-slate-500 font-medium mt-0.5">Kelas {{ ledgerData?.class?.name }} • Semester {{ activeSemester === 'genap' ? 'Genap' : 'Ganjil' }}</p>
            </div>
          </div>
          <button @click="showRankModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 cursor-pointer transition-colors">
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
          <div v-if="hasDuplicateRank" class="p-3.5 bg-rose-50 rounded-2xl border border-rose-200 text-xs text-rose-800 flex items-start gap-3 shadow-xs animate-shake">
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
                <span>Rapikan Otomatis (1 s/d {{ rankEditList.length }})</span>
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
                  <th class="p-3 w-28 text-center">Rata-Rata Nilai</th>
                  <th class="p-3 w-28 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="(item, idx) in rankEditList"
                  :key="'rank-item-'+item.student_id"
                  class="transition-colors hover:bg-slate-50/80"
                  :class="{
                    'bg-amber-50/50': item.rank === 1,
                    'bg-slate-50/50': item.rank === 2,
                    'bg-orange-50/30': item.rank === 3,
                    'bg-rose-50/40': duplicateRanks.includes(parseInt(item.rank))
                  }"
                >
                  <!-- Badge & Input Nomor Peringkat -->
                  <td class="p-2.5 text-center">
                    <div class="flex items-center justify-center gap-1">
                      <!-- Medal Emoji for Top 3 -->
                      <span v-if="item.rank === 1" class="text-sm" title="Juara 1">🥇</span>
                      <span v-else-if="item.rank === 2" class="text-sm" title="Juara 2">🥈</span>
                      <span v-else-if="item.rank === 3" class="text-sm" title="Juara 3">🥉</span>
                      
                      <input
                        v-model.number="item.rank"
                        type="number"
                        min="1"
                        :max="rankEditList.length"
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
                        :disabled="idx === rankEditList.length - 1"
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

                  <!-- Rata-rata Nilai -->
                  <td class="p-3 text-center font-bold font-mono text-emerald-700 bg-emerald-50/20">
                    {{ Number(item.average_score || 0).toFixed(2) }}
                  </td>

                  <!-- Status Peringkat -->
                  <td class="p-3 text-center">
                    <span v-if="item.rank !== item.calculated_rank" class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 whitespace-nowrap">
                      Manual (Murni: {{ item.calculated_rank !== '-' ? '#' + item.calculated_rank : '-' }})
                    </span>
                    <span v-else class="text-slate-400 text-[10px] whitespace-nowrap">
                      Sesuai Nilai {{ item.calculated_rank !== '-' ? '(#' + item.calculated_rank + ')' : '' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Opsi Tambahan: Selaraskan Nilai Siswa -->
          <div class="p-4 bg-slate-50/90 rounded-2xl border border-slate-200 flex items-start gap-3">
            <input
              id="adjust-scores-checkbox"
              v-model="adjustScoresWithRank"
              type="checkbox"
              class="mt-1 w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer"
            />
            <label for="adjust-scores-checkbox" class="text-xs cursor-pointer select-none">
              <span class="font-black text-slate-900 block">⚡ Opsional: Selaraskan nilai rata-rata secara wajar & bertahap (kisaran 78 - 81)</span>
              <span class="text-slate-500 text-[11px] block mt-0.5 leading-relaxed">
                Jika dicentang, nilai disesuaikan secara proporsional dan alami mengikuti peringkat (juara teratas berkisar 79–81, tanpa lonjakan ekstrem ke 90-an). Jika tidak dicentang, nilai asli tidak berubah sama sekali.
              </span>
            </label>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 sm:p-5 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 bg-slate-50/50">
          <div class="flex items-center gap-2 flex-wrap">
            <button
              type="button"
              @click="resetRanksToDefault"
              :disabled="savingRanks"
              class="px-3.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5"
              title="Urutkan kembali sesuai rata-rata murni nilai rapor"
            >
              <RotateCcw class="w-3.5 h-3.5" />
              <span>Kembalikan ke Nilai Murni</span>
            </button>
            <button
              type="button"
              @click="autoSequenceRanks"
              :disabled="savingRanks"
              class="px-3 py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-600 font-bold rounded-xl text-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5"
              title="Rapikan urutan nomor 1 sampai selesai tanpa ada nomor loncat"
            >
              <Sparkles class="w-3.5 h-3.5 text-amber-500" />
              <span class="hidden sm:inline">Rapikan Nomor (1 s/d N)</span>
            </button>

            <!-- Cetak Peringkat Langsung dari Modal -->
            <button
              type="button"
              @click="printRankingSheet('adjusted')"
              class="px-3 py-2 bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 font-bold rounded-xl text-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs"
              title="Cetak lembar resmi daftar peringkat hasil atur ini"
            >
              <Printer class="w-3.5 h-3.5 text-amber-700" />
              <span>Cetak Hasil Atur</span>
            </button>
          </div>

          <div class="grid grid-cols-2 sm:flex items-center gap-2">
            <button
              type="button"
              @click="showRankModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer text-center"
            >
              Batal
            </button>
            <button
              type="button"
              @click="saveRanksSubmit"
              :disabled="savingRanks || hasDuplicateRank"
              class="px-5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl text-xs transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 text-center"
            >
              <Check class="w-4 h-4" />
              <span>{{ savingRanks ? 'Menyimpan...' : 'Simpan Peringkat' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL CATATAN WALI KELAS & PRESENSI -->
    <div v-if="showNotesModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-6 no-print">
      <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all">
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend uppercase tracking-wider">Catatan Wali Kelas & Presensi</h2>
            <p class="text-[11px] sm:text-xs text-slate-400 font-medium mt-0.5">{{ editingStudent?.full_name }} • Kelas {{ ledgerData?.class?.name }}</p>
          </div>
          <button @click="showNotesModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="saveNotesSubmit" class="p-4 sm:p-6 space-y-4">
          <!-- Attendance Counts -->
          <div class="space-y-1">
            <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider">Rekapitulasi Ketidakhadiran Tengah Semester</label>
            <div class="grid grid-cols-3 gap-2 sm:gap-3 pt-1">
              <div class="p-2.5 sm:p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <span class="block text-[10px] font-bold text-slate-500 uppercase">Sakit (S)</span>
                <input
                  v-model.number="notesForm.sick_count"
                  type="number"
                  min="0"
                  class="w-full text-center text-sm font-black text-slate-800 bg-white border border-slate-300 rounded-lg py-1 mt-1 focus:ring-2 focus:ring-emerald-400 font-mono"
                />
              </div>
              <div class="p-2.5 sm:p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <span class="block text-[10px] font-bold text-slate-500 uppercase">Izin (I)</span>
                <input
                  v-model.number="notesForm.permission_count"
                  type="number"
                  min="0"
                  class="w-full text-center text-sm font-black text-slate-800 bg-white border border-slate-300 rounded-lg py-1 mt-1 focus:ring-2 focus:ring-emerald-400 font-mono"
                />
              </div>
              <div class="p-2.5 sm:p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <span class="block text-[10px] font-bold text-slate-500 uppercase">Alpa (A)</span>
                <input
                  v-model.number="notesForm.unexcused_count"
                  type="number"
                  min="0"
                  class="w-full text-center text-sm font-black text-slate-800 bg-white border border-slate-300 rounded-lg py-1 mt-1 focus:ring-2 focus:ring-emerald-400 font-mono"
                />
              </div>
            </div>
          </div>

          <!-- Homeroom Note / Motivation -->
          <div class="space-y-1.5 pt-2">
            <div class="flex items-center justify-between">
              <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider">Catatan Perkembangan & Motivasi Siswa</label>
              <span class="text-[10px] text-slate-400 font-medium">Tampil di rapor</span>
            </div>
            <textarea
              v-model="notesForm.homeroom_notes"
              rows="3"
              placeholder="Contoh: Tingkatkan terus ketekunan belajar dan keaktifan dalam ibadah berjamaah."
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-400 focus:bg-white transition-all"
            ></textarea>
            
            <!-- Quick Motivation Suggestions -->
            <div class="space-y-1 pt-1">
              <span class="text-[10px] font-bold text-slate-500 block">Pilihan Kalimat Motivasi Cepat (Klik untuk memilih):</span>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                <button
                  type="button"
                  v-for="(tip, tIdx) in motivationTemplates"
                  :key="'tip-'+tIdx"
                  @click="notesForm.homeroom_notes = tip"
                  class="px-2.5 py-1.5 rounded-xl bg-slate-50 hover:bg-emerald-50 hover:text-emerald-900 hover:border-emerald-300 border border-slate-200 text-[10.5px] font-medium transition-all text-left cursor-pointer flex items-start gap-1.5"
                >
                  <span class="text-emerald-600 font-bold text-xs mt-0.5">💬</span>
                  <span class="leading-tight">{{ tip }}</span>
                </button>
              </div>
            </div>
          </div>

          <div class="pt-3 grid grid-cols-2 sm:flex sm:justify-end gap-2 border-t border-slate-100">
            <button
              type="button"
              @click="showNotesModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer text-center"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="savingNotes"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 text-center"
            >
              <span>{{ savingNotes ? 'Menyimpan...' : 'Simpan Catatan' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL PILIH SUMBER TARIK NILAI (NILAI JADI VS NILAI ASLI) -->
    <div v-if="showPullModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-6 no-print">
      <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all">
        <!-- Header -->
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-teal-500/10 via-emerald-50 to-transparent">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20">
              <Sparkles class="w-5 h-5 text-amber-200" />
            </div>
            <div>
              <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend uppercase tracking-wider">Tarik Nilai dari Koreksi Ujian</h2>
              <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Kelas {{ ledgerData?.class?.name }}</p>
            </div>
          </div>
          <button @click="showPullModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Body: Radio Options -->
        <div class="p-4 sm:p-6 space-y-4">
          <p class="text-xs text-slate-600 leading-relaxed">
            Sistem akan menyinkronkan nilai dari modul <strong>Koreksi Ujian</strong> yang sudah dikoreksi guru mapel. Silakan tentukan opsi nilai yang ingin dimasukkan ke dalam rapor:
          </p>

          <div class="space-y-3">
            <!-- Option 1: Nilai Jadi (Standar Rapor) -->
            <label
              class="p-3.5 sm:p-4 rounded-2xl border-2 transition-all cursor-pointer flex items-start gap-3.5 block"
              :class="selectedScoreSource === 'final' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20 shadow-sm' : 'border-slate-200 hover:bg-slate-50'"
            >
              <input
                type="radio"
                name="score_source"
                value="final"
                v-model="selectedScoreSource"
                class="mt-1 w-4 h-4 text-teal-600 focus:ring-teal-500 border-slate-300 cursor-pointer flex-shrink-0"
              />
              <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-xs font-black text-slate-900 font-lexend">🌟 Nilai Jadi / Standar Rapor</span>
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                    Rekomendasi Rapor
                  </span>
                </div>
                <p class="text-[11px] text-slate-600 leading-normal">
                  Memprioritaskan <strong>Nilai Jadi / Remedial</strong> yang telah diolah guru di modul Koreksi Ujian agar memenuhi KKTP (bebas nilai merah di rapor). Jika siswa belum memiliki nilai jadi, otomatis menggunakan nilai aslinya.
                </p>
              </div>
            </label>

            <!-- Option 2: Nilai Asli (Skor Murni) -->
            <label
              class="p-3.5 sm:p-4 rounded-2xl border-2 transition-all cursor-pointer flex items-start gap-3.5 block"
              :class="selectedScoreSource === 'raw' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20 shadow-sm' : 'border-slate-200 hover:bg-slate-50'"
            >
              <input
                type="radio"
                name="score_source"
                value="raw"
                v-model="selectedScoreSource"
                class="mt-1 w-4 h-4 text-teal-600 focus:ring-teal-500 border-slate-300 cursor-pointer flex-shrink-0"
              />
              <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-xs font-black text-slate-900 font-lexend">📝 Nilai Asli (Skor Murni Ujian)</span>
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-blue-100 text-blue-800 border border-blue-300">
                    Murni CBT
                  </span>
                </div>
                <p class="text-[11px] text-slate-600 leading-normal">
                  Mengambil skor murni pengerjaan CBT/ujian <strong>apa adanya tanpa nilai remedial/katrol</strong>. Cocok untuk evaluasi diagnostik kemampuan riil siswa.
                </p>
              </div>
            </label>
          </div>

          <!-- Clean Sync Notice -->
          <div class="p-3 bg-teal-50/70 border border-teal-200 rounded-xl flex items-start gap-2.5 text-xs text-teal-900">
            <Sparkles class="w-4 h-4 text-teal-600 flex-shrink-0 mt-0.5" />
            <div class="space-y-0.5">
              <p class="font-bold">Sinkronisasi Bersih Otomatis Aktif</p>
              <p class="text-[11px] text-teal-700 leading-normal">Jika lembar koreksi atau nilai suatu mapel telah direset/dikosongkan oleh guru mapel, sisa nilai lama di rapor otomatis ikut dibersihkan.</p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-4 sm:p-5 border-t border-slate-100 grid grid-cols-2 sm:flex sm:items-center sm:justify-between gap-2.5 bg-slate-50/50">
          <button
            type="button"
            @click="showPullModal = false"
            class="px-4 py-2.5 sm:py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer text-center"
          >
            Batal
          </button>
          <button
            type="button"
            @click="executeAutoPullScores"
            :disabled="pullingScores"
            class="col-span-2 sm:col-span-1 px-5 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95 text-center"
          >
            <Sparkles v-if="!pullingScores" class="w-4 h-4 text-amber-200" />
            <div v-else class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
            <span>{{ pullingScores ? 'Menarik...' : 'Tarik Nilai Sekarang' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL RESET / KOSONGKAN NILAI RAPOR (KHUSUS WALI KELAS & ADMIN) -->
    <div v-if="showResetModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-6 no-print">
      <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all">
        <!-- Header -->
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-rose-500/10 via-rose-50 to-transparent">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-md shadow-rose-600/20">
              <RotateCcw class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend uppercase tracking-wider">Kosongkan / Reset Nilai Rapor</h2>
              <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Kelas {{ ledgerData?.class?.name }} • Semester {{ activeSemester === 'ganjil' ? 'Ganjil' : 'Genap' }}</p>
            </div>
          </div>
          <button @click="showResetModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Body -->
        <div class="p-4 sm:p-6 space-y-4">
          <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-2.5 text-xs text-rose-950">
            <AlertTriangle class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" />
            <p class="leading-relaxed">
              Tindakan ini akan mengosongkan nilai yang sudah ditarik ke dalam rapor. Data nilai ujian di akun Guru Mapel tidak akan terpengaruh dan tetap aman.
            </p>
          </div>

          <div class="space-y-3">
            <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Pilih Lingkup Reset Nilai:</label>
            
            <!-- Option A: 1 Mapel Tertentu -->
            <label
              class="p-3.5 rounded-2xl border-2 transition-all cursor-pointer flex items-start gap-3.5 block"
              :class="resetScope === 'single' ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 shadow-sm' : 'border-slate-200 hover:bg-slate-50'"
            >
              <input
                type="radio"
                name="reset_scope"
                value="single"
                v-model="resetScope"
                class="mt-1 w-4 h-4 text-rose-600 focus:ring-rose-500 border-slate-300 cursor-pointer flex-shrink-0"
              />
              <div class="space-y-2 flex-1 min-w-0">
                <div>
                  <span class="text-xs font-black text-slate-900 font-lexend block">Hanya 1 Mata Pelajaran Tertentu</span>
                  <span class="text-[11px] text-slate-500">Pilih mata pelajaran yang nilainya ingin dikosongkan/dibersihkan dari rapor</span>
                </div>

                <select
                  v-if="resetScope === 'single'"
                  v-model="selectedResetSubjectId"
                  class="w-full text-xs font-bold rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 shadow-2xs"
                >
                  <option value="" disabled>-- Pilih Mata Pelajaran --</option>
                  <option
                    v-for="st in subjectStatuses"
                    :key="st.subject_id"
                    :value="st.subject_id"
                  >
                    {{ st.subject_name }} {{ st.has_scores ? `(Sudah ada ${st.synced_count} nilai)` : '(Belum ada nilai)' }}
                  </option>
                </select>
              </div>
            </label>

            <!-- Option B: Semua Mapel Kelas Ini -->
            <label
              class="p-3.5 rounded-2xl border-2 transition-all cursor-pointer flex items-start gap-3.5 block"
              :class="resetScope === 'all' ? 'border-rose-500 bg-rose-50/50 ring-2 ring-rose-500/20 shadow-sm' : 'border-slate-200 hover:bg-slate-50'"
            >
              <input
                type="radio"
                name="reset_scope"
                value="all"
                v-model="resetScope"
                class="mt-1 w-4 h-4 text-rose-600 focus:ring-rose-500 border-slate-300 cursor-pointer flex-shrink-0"
              />
              <div class="space-y-0.5 flex-1 min-w-0">
                <span class="text-xs font-black text-slate-900 font-lexend block">Seluruh Nilai Rapor Kelas Ini (Semua Mapel)</span>
                <span class="text-[11px] text-slate-500">Kosongkan semua nilai tarikan di kelas ini untuk semester ini agar lembaran rapor bersih kembali</span>
              </div>
            </label>
          </div>
        </div>

        <!-- Footer -->
        <div class="p-4 sm:p-5 border-t border-slate-100 grid grid-cols-2 sm:flex sm:items-center sm:justify-between gap-2.5 bg-slate-50/50">
          <button
            type="button"
            @click="showResetModal = false"
            class="px-4 py-2.5 sm:py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer text-center"
          >
            Batal
          </button>
          <button
            type="button"
            @click="executeResetScores"
            :disabled="resettingScores || (resetScope === 'single' && !selectedResetSubjectId)"
            class="col-span-2 sm:col-span-1 px-5 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-bold rounded-xl text-xs transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 active:scale-95 text-center"
          >
            <RotateCcw v-if="!resettingScores" class="w-4 h-4" />
            <div v-else class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
            <span>{{ resettingScores ? 'Mengosongkan...' : 'Ya, Kosongkan Nilai' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL ATUR TITIMANGSA RAPOR (TEMPAT & TANGGAL CETAK) -->
    <div v-if="showTitimangsaModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-6 no-print">
      <div class="bg-white rounded-2xl sm:rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden border border-slate-100 transform transition-all">
        <!-- Header -->
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-amber-500/10 via-amber-50 to-transparent">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/20">
              <Calendar class="w-5 h-5" />
            </div>
            <div>
              <h2 class="text-sm sm:text-base font-black text-slate-800 font-lexend uppercase tracking-wider">Atur Titimangsa Rapor</h2>
              <p class="text-[11px] sm:text-xs text-slate-500 font-medium">Kelas {{ ledgerData?.class?.name }} • Semester {{ activeSemester === 'genap' ? 'Genap' : 'Ganjil' }}</p>
            </div>
          </div>
          <button @click="showTitimangsaModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="saveTitimangsaSubmit" class="p-4 sm:p-6 space-y-4">
          <!-- Input Tempat / Kota -->
          <div class="space-y-1.5">
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Tempat Penerbitan (Kota / Kecamatan)</label>
            <input
              v-model="titimangsaForm.city"
              type="text"
              required
              placeholder="Contoh: Bogor, Ciomas, Kab. Bogor"
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-amber-400 focus:bg-white transition-all"
            />
          </div>

          <!-- Input Tanggal Cetak -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Tanggal Resmi Rapor</label>
              <button
                type="button"
                @click="setTitimangsaToday"
                class="text-[10px] font-bold text-amber-700 hover:underline cursor-pointer"
              >
                Gunakan Hari Ini
              </button>
            </div>
            <input
              v-model="titimangsaForm.issued_date"
              type="date"
              required
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-bold text-slate-800 focus:ring-2 focus:ring-amber-400 focus:bg-white transition-all font-mono"
            />
          </div>

          <!-- Preview Box -->
          <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-2xl text-xs space-y-1 text-amber-950">
            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Format di Lembar Rapor:</div>
            <div class="text-sm font-black font-lexend text-slate-900 bg-white p-2 rounded-xl border border-amber-200 text-right">
              {{ formattedPreviewTitimangsa }}
            </div>
            <p class="text-[10px] text-amber-700 leading-normal">
              *Titimangsa ini akan tercetak seragam pada seluruh lembar rapor siswa kelas ini (baik pratinjau maupun cetak 1 kelas).
            </p>
          </div>

          <div class="pt-3 grid grid-cols-2 sm:flex sm:justify-end gap-2 border-t border-slate-100">
            <button
              type="button"
              @click="showTitimangsaModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer text-center"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="savingTitimangsa"
              class="px-5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl text-xs transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 active:scale-95 text-center"
            >
              <Check class="w-4 h-4" />
              <span>{{ savingTitimangsa ? 'Menyimpan...' : 'Simpan Titimangsa' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- HIDDEN DEDICATED PRINT AREA FOR CLASS RANKING SHEET -->
    <div
      id="asts-ranking-sheet-print-area"
      :class="printTarget === 'ranking' ? 'block print-active-sheet' : 'hidden'"
      class="print-rank-sheet bg-white p-4 sm:p-7 rounded-2xl border border-slate-300"
    >
      <AstsRankSheet
        :students="ledgerStudents"
        :class-info="ledgerData?.class || {}"
        :academic-year="activeYear || {}"
        :semester="activeSemester"
        :school-setting="effectiveSchoolSetting"
        :city="currentCity"
        :issued-date="currentIssuedDate"
        :rank-type="rankingSheetRankType"
      />
    </div>

    <!-- HIDDEN DEDICATED PRINT AREA FOR COMPLETE CLASS LEDGER MATRIX SHEET -->
    <div
      id="asts-ledger-sheet-print-area"
      :class="printTarget === 'ledger' ? 'block print-active-sheet' : 'hidden'"
      class="print-ledger-sheet bg-white p-3 sm:p-6 rounded-2xl border border-slate-300"
    >
      <AstsLedgerSheet
        :students="ledgerStudents"
        :subjects="subjectsList"
        :class-info="ledgerData?.class || {}"
        :academic-year="activeYear || {}"
        :semester="activeSemester"
        :school-setting="effectiveSchoolSetting"
        :city="currentCity"
        :issued-date="currentIssuedDate"
        :rank-type="ledgerSheetRankType"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import AstsReportSheet from '../components/AstsReportSheet.vue';
import AstsRankSheet from '../components/AstsRankSheet.vue';
import AstsLedgerSheet from '../components/AstsLedgerSheet.vue';
import {
  BookOpenCheck,
  GraduationCap,
  TableProperties,
  Printer,
  Sparkles,
  Download,
  X,
  Trophy,
  Search,
  ChevronRight,
  ChevronLeft,
  ChevronDown,
  ChevronUp,
  Eye,
  Users,
  RotateCcw,
  Check,
  Pencil,
  Calendar,
  LayoutGrid,
  List,
  AlertTriangle,
  ArrowUp,
  ArrowDown,
  Info,
} from 'lucide-vue-next';

const toast = useToast();
const loading = ref(false);
const pullingScores = ref(false);
const savingNotes = ref(false);
const savingRanks = ref(false);
const loadingSingleReport = ref(false);

const activeSemester = ref('ganjil'); // 'ganjil' | 'genap'
const activeSubTab = ref('ledger'); // 'ledger' | 'print'
const printMode = ref('single'); // 'single' | 'batch'
const selectedPaperSize = ref('f4'); // 'f4' | 'a4'
const selectedRankType = ref('adjusted'); // 'adjusted' | 'original'

function setRankType(type) {
  selectedRankType.value = type;
  if (activeSubTab.value === 'print') {
    fetchPrintData();
  }
}

// Responsive View Controls
const ledgerViewMode = ref(typeof window !== 'undefined' && window.innerWidth >= 1024 ? 'table' : 'cards');
const showSubjectStatuses = ref(false);
const showMobileStudentList = ref(false);
const expandedCards = reactive({});

function toggleCardDetail(studentId) {
  expandedCards[studentId] = !expandedCards[studentId];
}

const classes = ref([]);
const activeYear = ref(null);
const selectedClassId = ref('');
const ledgerData = ref(null);
const isHomeroomOnly = ref(false);

const selectedStudentId = ref('');
const singleReportData = ref(null);
const batchReportsList = ref([]);
const studentSearchQuery = ref('');

// Modal Notes
const showNotesModal = ref(false);
const editingStudent = ref(null);
const motivationTemplates = [
  'Pertahankan prestasimu dan terus tingkatkan ketekunan belajar di madrasah.',
  'Tingkatkan terus ketekunan belajar, kedisiplinan beribadah, dan keaktifan di kelas.',
  'Perbanyak latihan soal mandiri dan lebih aktif bertanya saat pembelajaran.',
  'Semangat belajar terus ditingkatkan, kurangi waktu bermain, dan jaga kesehatan.',
];
const notesForm = reactive({
  student_id: null,
  semester: 'ganjil',
  academic_year_id: null,
  sick_count: 0,
  permission_count: 0,
  unexcused_count: 0,
  homeroom_notes: '',
});

// Modal Pull Scores
const showPullModal = ref(false);
const selectedScoreSource = ref('final'); // 'final' (Nilai Jadi) | 'raw' (Nilai Asli)

// Modal Reset Scores
const showResetModal = ref(false);
const resettingScores = ref(false);
const resetScope = ref('single'); // 'single' | 'all'
const selectedResetSubjectId = ref('');

// Modal Titimangsa
const showTitimangsaModal = ref(false);
const savingTitimangsa = ref(false);
const titimangsaForm = reactive({
  city: 'Bogor',
  issued_date: '',
});

const formattedPreviewTitimangsa = computed(() => {
  if (!titimangsaForm.issued_date) return `${titimangsaForm.city || 'Bogor'}, (Tanggal belum dipilih)`;
  try {
    const parts = titimangsaForm.issued_date.split('-');
    if (parts.length === 3) {
      const year = parts[0];
      const monthIdx = parseInt(parts[1], 10) - 1;
      const day = parseInt(parts[2], 10);
      const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
      return `${titimangsaForm.city || 'Bogor'}, ${day} ${months[monthIdx] || ''} ${year}`;
    }
  } catch (e) {}
  return `${titimangsaForm.city || 'Bogor'}, ${titimangsaForm.issued_date}`;
});

const currentTitimangsaSummary = computed(() => {
  if (singleReportData.value?.city && singleReportData.value?.issued_date) {
    return `${singleReportData.value.city}, ${singleReportData.value.issued_date}`;
  }
  return formattedPreviewTitimangsa.value || 'Otomatis';
});

// Modal Rank Adjuster
const showRankModal = ref(false);
const adjustScoresWithRank = ref(false);
const rankEditList = ref([]);

const subjectsList = computed(() => ledgerData.value?.subjects || []);
const subjectStatuses = computed(() => ledgerData.value?.subject_statuses || []);
const ledgerStudents = computed(() => ledgerData.value?.students || []);

const filteredLedgerStudents = computed(() => {
  const query = studentSearchQuery.value.trim().toLowerCase();
  if (!query) return ledgerStudents.value;
  return ledgerStudents.value.filter(s => 
    s.full_name?.toLowerCase().includes(query) || 
    s.nisn?.toLowerCase().includes(query) ||
    s.nis?.toLowerCase().includes(query)
  );
});

const subjectsWithScoresCount = computed(() => {
  return subjectStatuses.value.filter(s => s.has_scores).length;
});

function getSubjectShort(name) {
  if (!name) return '';
  const parts = name.split(' ');
  if (parts.length === 1) return name.slice(0, 5);
  return parts.map(p => p[0]).join('').toUpperCase();
}

function setSemester(sem) {
  activeSemester.value = sem;
  if (selectedClassId.value) {
    fetchLedger();
  }
}

function setPrintMode(mode) {
  printMode.value = mode;
  if (mode === 'batch') {
    fetchBatchReports();
  } else {
    fetchSingleReport();
  }
}

function selectLiveStudent(studentId) {
  selectedStudentId.value = studentId;
  fetchSingleReport();
}

const currentStudentIndex = computed(() => {
  if (!ledgerStudents.value || !selectedStudentId.value) return 0;
  const idx = ledgerStudents.value.findIndex(s => s.student_id == selectedStudentId.value);
  return idx >= 0 ? idx : 0;
});

function goToPrevStudent() {
  const idx = currentStudentIndex.value;
  if (idx > 0) {
    selectLiveStudent(ledgerStudents.value[idx - 1].student_id);
  }
}

function goToNextStudent() {
  const idx = currentStudentIndex.value;
  if (idx >= 0 && idx < ledgerStudents.value.length - 1) {
    selectLiveStudent(ledgerStudents.value[idx + 1].student_id);
  }
}

async function fetchOptions() {
  try {
    const res = await api.get('/teacher/asts-reports/options');
    const d = res?.data || res || {};
    classes.value = d.classes || [];
    activeYear.value = d.active_academic_year || null;
    isHomeroomOnly.value = !!d.is_homeroom_only;
    globalSchoolSetting.value = d.school_setting || null;

    if (d.homeroom_class_id) {
      selectedClassId.value = d.homeroom_class_id;
      await fetchLedger();
    } else if (classes.value.length > 0 && !selectedClassId.value) {
      selectedClassId.value = classes.value[0].id;
      await fetchLedger();
    }
  } catch (err) {
    console.error('Error fetching ASTS options:', err);
  }
}

async function fetchLedger() {
  if (!selectedClassId.value) return;
  loading.value = true;
  try {
    const res = await api.get('/teacher/asts-reports/ledger', {
      class_id: selectedClassId.value,
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
    });
    ledgerData.value = res?.data || res || {};

    // If students exist, select the first for single print view
    if (ledgerStudents.value.length > 0) {
      if (!selectedStudentId.value || !ledgerStudents.value.find(s => s.student_id == selectedStudentId.value)) {
        selectedStudentId.value = ledgerStudents.value[0].student_id;
      }
      if (activeSubTab.value === 'print') {
        fetchPrintData();
      }
    }
  } catch (err) {
    toast.error('Gagal memuat ledger nilai ASTS.');
  } finally {
    loading.value = false;
  }
}

function openPullModal() {
  if (!selectedClassId.value) {
    toast.error('Silakan pilih kelas terlebih dahulu.');
    return;
  }
  showPullModal.value = true;
}

async function executeAutoPullScores() {
  if (!selectedClassId.value) return;
  pullingScores.value = true;
  try {
    const res = await api.post('/teacher/asts-reports/auto-pull', {
      class_id: selectedClassId.value,
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
      score_source: selectedScoreSource.value,
      clean_sync: true,
    });
    const msg = res?.message || 'Nilai koreksi berhasil ditarik ke Rapor ASTS!';
    toast.success(msg);
    showPullModal.value = false;
    await fetchLedger();
    if (activeSubTab.value === 'print' || selectedStudentId.value) {
      await fetchPrintData();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menarik nilai koreksi.');
  } finally {
    pullingScores.value = false;
  }
}

function openResetModal(preselectedSubjectId = null) {
  if (!selectedClassId.value) {
    toast.error('Silakan pilih kelas terlebih dahulu.');
    return;
  }
  const filledSubjects = subjectStatuses.value.filter(s => s.has_scores);
  if (preselectedSubjectId) {
    resetScope.value = 'single';
    selectedResetSubjectId.value = preselectedSubjectId;
  } else if (filledSubjects.length > 0) {
    resetScope.value = 'single';
    selectedResetSubjectId.value = filledSubjects[0].subject_id;
  } else {
    resetScope.value = 'all';
    selectedResetSubjectId.value = '';
  }
  showResetModal.value = true;
}

async function executeResetScores() {
  if (!selectedClassId.value) return;
  if (resetScope.value === 'single' && !selectedResetSubjectId.value) {
    toast.error('Silakan pilih mata pelajaran yang ingin dikosongkan.');
    return;
  }

  resettingScores.value = true;
  try {
    const payload = {
      class_id: selectedClassId.value,
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
    };
    if (resetScope.value === 'single') {
      payload.subject_id = selectedResetSubjectId.value;
    }

    const res = await api.post('/teacher/asts-reports/reset-scores', payload);
    toast.success(res?.message || 'Nilai rapor berhasil dikosongkan!');
    showResetModal.value = false;
    await fetchLedger();
    if (activeSubTab.value === 'print' || selectedStudentId.value) {
      await fetchPrintData();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengosongkan nilai rapor.');
  } finally {
    resettingScores.value = false;
  }
}

function openRankModal() {
  // Populate rankEditList ordered by current rank / average
  const sorted = [...ledgerStudents.value].sort((a, b) => {
    const rankA = typeof a.rank === 'number' ? a.rank : 9999;
    const rankB = typeof b.rank === 'number' ? b.rank : 9999;
    if (rankA !== rankB) return rankA - rankB;
    const avgA = Number(a.average_score) || 0;
    const avgB = Number(b.average_score) || 0;
    if (avgB !== avgA) return avgB - avgA;
    return (a.full_name || '').localeCompare(b.full_name || '');
  });

  // Always ensure ranks are sequential 1 to N (1, 2, 3... total siswa)
  rankEditList.value = sorted.map((st, idx) => ({
    student_id: st.student_id,
    full_name: st.full_name,
    nisn: st.nisn,
    average_score: st.average_score,
    calculated_rank: st.calculated_rank || (idx + 1),
    rank: idx + 1,
  }));

  adjustScoresWithRank.value = false;
  showRankModal.value = true;
}

// Move student up in the rank list (swaps position and re-indexes sequentially)
function moveStudentRank(index, direction) {
  const targetIndex = index + direction;
  if (targetIndex < 0 || targetIndex >= rankEditList.value.length) return;

  const currentList = [...rankEditList.value];
  const itemToMove = currentList[index];
  currentList.splice(index, 1);
  currentList.splice(targetIndex, 0, itemToMove);

  // Auto-assign clean sequential ranks (1, 2, 3...) so duplicates are impossible
  currentList.forEach((item, idx) => {
    item.rank = idx + 1;
  });

  rankEditList.value = currentList;
}

// Check for duplicate ranks
const duplicateRanks = computed(() => {
  const counts = {};
  const duplicates = new Set();
  rankEditList.value.forEach(item => {
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

// Auto re-sequence ranks sequentially based on current list order
function autoSequenceRanks() {
  rankEditList.value.forEach((item, idx) => {
    item.rank = idx + 1;
  });
  toast.success('Peringkat berhasil dirapikan berurutan 1 sampai ' + rankEditList.value.length);
}

// Sort list according to the manual rank input values
function sortListByRankInputs() {
  rankEditList.value = [...rankEditList.value].sort((a, b) => {
    const rA = parseInt(a.rank) || 9999;
    const rB = parseInt(b.rank) || 9999;
    return rA - rB;
  });
}

async function saveRanksSubmit() {
  if (!selectedClassId.value) return;
  savingRanks.value = true;
  try {
    const payload = {
      class_id: selectedClassId.value,
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
      ranks: rankEditList.value.map(item => ({
        student_id: item.student_id,
        rank: parseInt(item.rank) || 1,
      })),
      adjust_scores: adjustScoresWithRank.value,
    };

    const res = await api.post('/teacher/asts-reports/adjust-ranks', payload);
    toast.success(res?.message || 'Peringkat siswa berhasil diperbarui!');
    showRankModal.value = false;
    await fetchLedger();
    if (activeSubTab.value === 'print') {
      await fetchPrintData();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan penyesuaian peringkat.');
  } finally {
    savingRanks.value = false;
  }
}

async function resetRanksToDefault() {
  if (!confirm('Apakah Anda yakin ingin mengembalikan urutan peringkat sesuai rata-rata murni tanpa penyesuaian manual?')) {
    return;
  }
  savingRanks.value = true;
  try {
    const payload = {
      class_id: selectedClassId.value,
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
    };
    const res = await api.post('/teacher/asts-reports/reset-ranks', payload);
    toast.success(res?.message || 'Peringkat dikembalikan ke otomatis.');
    showRankModal.value = false;
    await fetchLedger();
    if (activeSubTab.value === 'print') {
      await fetchPrintData();
    }
  } catch (err) {
    toast.error('Gagal mereset peringkat.');
  } finally {
    savingRanks.value = false;
  }
}

function openNotesModal(student) {
  if (!student) return;
  editingStudent.value = student;
  notesForm.student_id = student.student_id || student.id;
  notesForm.semester = activeSemester.value;
  notesForm.academic_year_id = activeYear.value?.id;
  notesForm.sick_count = student.sick_count !== undefined ? student.sick_count : (student.attendance?.sick || 0);
  notesForm.permission_count = student.permission_count !== undefined ? student.permission_count : (student.attendance?.permission || 0);
  notesForm.unexcused_count = student.unexcused_count !== undefined ? student.unexcused_count : (student.attendance?.unexcused || 0);
  notesForm.homeroom_notes = student.homeroom_notes || '';
  showNotesModal.value = true;
}

function openNotesModalForCurrentStudent() {
  if (!selectedStudentId.value && !singleReportData.value) return;
  const sId = selectedStudentId.value || singleReportData.value?.student?.id;
  const found = ledgerStudents.value.find(s => s.student_id == sId);
  if (found) {
    openNotesModal(found);
  } else if (singleReportData.value) {
    const rep = singleReportData.value;
    openNotesModal({
      student_id: rep.student?.id || sId,
      full_name: rep.student?.full_name || 'Siswa',
      sick_count: rep.attendance?.sick || 0,
      permission_count: rep.attendance?.permission || 0,
      unexcused_count: rep.attendance?.unexcused || 0,
      homeroom_notes: rep.homeroom_notes || '',
    });
  }
}

async function saveNotesSubmit() {
  savingNotes.value = true;
  try {
    await api.post('/teacher/asts-reports/save-notes', notesForm);
    toast.success('Catatan wali kelas dan presensi berhasil disimpan!');
    showNotesModal.value = false;
    await fetchLedger();
    if (activeSubTab.value === 'print' || selectedStudentId.value) {
      await fetchSingleReport();
    }
  } catch (err) {
    toast.error('Gagal menyimpan catatan wali kelas.');
  } finally {
    savingNotes.value = false;
  }
}

function openTitimangsaModal() {
  if (singleReportData.value?.city) {
    titimangsaForm.city = singleReportData.value.city;
  }
  if (singleReportData.value?.raw_issued_date) {
    titimangsaForm.issued_date = singleReportData.value.raw_issued_date;
  } else if (!titimangsaForm.issued_date) {
    titimangsaForm.issued_date = new Date().toISOString().split('T')[0];
  }
  showTitimangsaModal.value = true;
}

function setTitimangsaToday() {
  titimangsaForm.issued_date = new Date().toISOString().split('T')[0];
}

async function saveTitimangsaSubmit() {
  if (!selectedClassId.value) {
    toast.error('Silakan pilih kelas terlebih dahulu.');
    return;
  }
  savingTitimangsa.value = true;
  try {
    const res = await api.post('/teacher/asts-reports/save-titimangsa', {
      class_id: selectedClassId.value,
      semester: activeSemester.value,
      city: titimangsaForm.city,
      issued_date: titimangsaForm.issued_date,
    });
    toast.success(res?.message || 'Titimangsa rapor berhasil disimpan!');
    showTitimangsaModal.value = false;
    if (activeSubTab.value === 'print' || selectedStudentId.value) {
      await fetchPrintData();
    }
  } catch (err) {
    toast.error('Gagal menyimpan titimangsa rapor.');
  } finally {
    savingTitimangsa.value = false;
  }
}

function previewSingleStudent(studentId) {
  selectedStudentId.value = studentId;
  printMode.value = 'single';
  activeSubTab.value = 'print';
  fetchSingleReport();
}

async function fetchSingleReport() {
  if (!selectedStudentId.value) return;
  loadingSingleReport.value = true;
  try {
    const res = await api.get(`/teacher/asts-reports/student/${selectedStudentId.value}`, {
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
      class_id: selectedClassId.value,
      rank_type: selectedRankType.value,
    });
    singleReportData.value = res?.data || res || null;
  } catch (err) {
    toast.error('Gagal memuat pratinjau rapor siswa.');
  } finally {
    loadingSingleReport.value = false;
  }
}

async function fetchBatchReports() {
  if (!selectedClassId.value) return;
  try {
    const res = await api.get(`/teacher/asts-reports/batch-class/${selectedClassId.value}`, {
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
      rank_type: selectedRankType.value,
    });
    const d = res?.data || res || {};
    batchReportsList.value = d.reports || [];
  } catch (err) {
    toast.error('Gagal memuat rapor massal 1 kelas.');
  }
}

// Print Target Type: 'report' | 'ranking' | 'ledger'
const printTarget = ref('report');
const rankingSheetRankType = ref('adjusted'); // 'adjusted' | 'original'
const ledgerSheetRankType = ref('adjusted'); // 'adjusted' | 'original'
const globalSchoolSetting = ref(null);

const effectiveSchoolSetting = computed(() => {
  return singleReportData.value?.school_setting ||
    ledgerData.value?.school_setting ||
    globalSchoolSetting.value ||
    {};
});

const currentCity = computed(() => {
  return singleReportData.value?.city || titimangsaForm.city || 'Bogor';
});

const currentIssuedDate = computed(() => {
  return singleReportData.value?.issued_date || formattedPreviewTitimangsa.value || '';
});

function printRankingSheet(type = 'adjusted') {
  if (!ledgerStudents.value.length) {
    toast.error('Belum ada data siswa untuk dicetak peringkatnya.');
    return;
  }
  rankingSheetRankType.value = type;
  printTarget.value = 'ranking';
  document.body.classList.remove('printing-ledger');
  document.body.classList.add('printing-ranking');

  const isA4 = selectedPaperSize.value === 'a4';
  const paperSize = isA4 ? 'A4 portrait' : '215mm 330mm';

  let styleEl = document.getElementById('asts-print-page-style');
  if (!styleEl) {
    styleEl = document.createElement('style');
    styleEl.id = 'asts-print-page-style';
    document.head.appendChild(styleEl);
  }
  styleEl.innerHTML = `
    @media print {
      @page {
        size: ${paperSize};
        margin: 5mm 8mm 5mm 8mm;
      }
    }
  `;

  // Print after DOM update, then reset printTarget to report
  setTimeout(() => {
    window.print();
    setTimeout(() => {
      printTarget.value = 'report';
      document.body.classList.remove('printing-ranking');
    }, 1000);
  }, 150);
}

function printLedgerSheet(type = 'adjusted') {
  if (!ledgerStudents.value.length) {
    toast.error('Belum ada data nilai siswa untuk dicetak ledgernya.');
    return;
  }
  ledgerSheetRankType.value = type;
  printTarget.value = 'ledger';
  document.body.classList.remove('printing-ranking');
  document.body.classList.add('printing-ledger');

  const isA4 = selectedPaperSize.value === 'a4';
  // Ledger selalu dicetak secara Landscape (Folio 330x215mm atau A4 Landscape 297x210mm)
  const paperSize = isA4 ? 'A4 landscape' : '330mm 215mm';

  let styleEl = document.getElementById('asts-print-page-style');
  if (!styleEl) {
    styleEl = document.createElement('style');
    styleEl.id = 'asts-print-page-style';
    document.head.appendChild(styleEl);
  }
  styleEl.innerHTML = `
    @media print {
      @page {
        size: ${paperSize};
        margin: 4mm 6mm 4mm 6mm;
      }
    }
  `;

  // Print after DOM update, then reset printTarget to report
  setTimeout(() => {
    window.print();
    setTimeout(() => {
      printTarget.value = 'report';
      document.body.classList.remove('printing-ledger');
    }, 1000);
  }, 150);
}

function triggerPrint() {
  printTarget.value = 'report';
  document.body.classList.remove('printing-ranking');
  const isA4 = selectedPaperSize.value === 'a4';
  const paperSize = isA4 ? 'A4 portrait' : '215mm 330mm';

  // Inject or update dynamic @page size into document head
  let styleEl = document.getElementById('asts-print-page-style');
  if (!styleEl) {
    styleEl = document.createElement('style');
    styleEl.id = 'asts-print-page-style';
    document.head.appendChild(styleEl);
  }
  styleEl.innerHTML = `
    @media print {
      @page {
        size: ${paperSize};
        margin: 5mm 8mm 5mm 8mm;
      }
    }
  `;

  // Native window.print() guarantees 100% exact WYSIWYG match with Live Review,
  // completely avoiding popup style-loss and layout deformation.
  window.print();
}

function exportLedgerExcel() {
  if (!ledgerData.value || !ledgerStudents.value.length) {
    toast.error('Belum ada data nilai untuk diexport.');
    return;
  }

  const rows = [];
  const header = ['No', 'NISN', 'Nama Siswa', 'L/P'];
  subjectsList.value.forEach(s => header.push(`${s.name} (KKTP ${s.passing_grade || 75})`));
  header.push('Total Nilai', 'Rata-Rata', 'Peringkat', 'Sakit', 'Izin', 'Alpa', 'Catatan Wali Kelas');
  rows.push(header);

  ledgerStudents.value.forEach((st, idx) => {
    const r = [idx + 1, st.nisn || '-', st.full_name, st.gender];
    subjectsList.value.forEach(s => {
      const score = st.scores?.[s.id]?.score;
      r.push(score !== undefined && score !== null ? Math.round(Number(score)) : '-');
    });
    r.push(Math.round(Number(st.total_score) || 0), Number(st.average_score || 0).toFixed(2), st.rank, st.sick_count, st.permission_count, st.unexcused_count, st.homeroom_notes || '-');
    rows.push(r);
  });

  const ws = XLSX.utils.aoa_to_sheet(rows);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, `ASTS_${activeSemester.value.toUpperCase()}`);

  const className = ledgerData.value?.class?.name || 'Kelas';
  XLSX.writeFile(wb, `Ledger_ASTS_${className}_${activeSemester.value}.xlsx`);
  toast.success('File Excel ledger nilai berhasil didownload!');
}

onMounted(() => {
  fetchOptions();
});
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>

<style>
@media print {
  /* 1. Sembunyikan semua elemen navigasi, sidebar, kontrol, modal, tombol, toast */
  nav,
  aside,
  header,
  footer,
  .no-print,
  button,
  input,
  select,
  .toast-container {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    width: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  /* 2. Reset struktur body & app agar 100% full-width tanpa offset */
  html,
  body,
  #app {
    background: #ffffff !important;
    background-image: none !important;
    color: #0f172a !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    min-width: 100% !important;
    height: auto !important;
    overflow: visible !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* 3. Reset flex & grid wrapper di halaman ASTS agar area cetak tidak terjepit di col-span-8 */
  .grid,
  .lg\:grid-cols-12,
  .lg\:col-span-8,
  .lg\:col-span-4,
  .flex,
  .overflow-x-auto,
  main {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: visible !important;
    box-shadow: none !important;
  }

  /* 4. Area rapor (single & batch), Area Peringkat, & Area Ledger memenuhi kertas secara murni */
  #asts-report-single-area,
  #asts-report-batch-area,
  #asts-ranking-sheet-print-area,
  #asts-ledger-sheet-print-area,
  .print-sheet {
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 auto !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    background: #ffffff !important;
  }

  /* Sembunyikan area ranking & ledger saat mencetak rapor biasa */
  body:not(.printing-ranking) #asts-ranking-sheet-print-area {
    display: none !important;
  }
  body:not(.printing-ledger) #asts-ledger-sheet-print-area {
    display: none !important;
  }

  /* Saat mencetak ranking sheet, sembunyikan SELURUH elemen lain di container */
  body.printing-ranking .print-container > *:not(#asts-ranking-sheet-print-area) {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    max-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    overflow: hidden !important;
  }

  body.printing-ranking #asts-report-single-area,
  body.printing-ranking #asts-report-batch-area,
  body.printing-ranking #asts-ledger-sheet-print-area {
    display: none !important;
    height: 0 !important;
  }

  body.printing-ranking #asts-ranking-sheet-print-area {
    display: block !important;
    position: relative !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    page-break-before: auto !important;
    break-before: auto !important;
    page-break-after: auto !important;
    break-after: auto !important;
    page-break-inside: auto !important;
    break-inside: auto !important;
  }

  /* Saat mencetak ledger sheet (Landscape), sembunyikan SELURUH elemen lain di container */
  body.printing-ledger .print-container > *:not(#asts-ledger-sheet-print-area) {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    max-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    overflow: hidden !important;
  }

  body.printing-ledger #asts-report-single-area,
  body.printing-ledger #asts-report-batch-area,
  body.printing-ledger #asts-ranking-sheet-print-area {
    display: none !important;
    height: 0 !important;
  }

  body.printing-ledger #asts-ledger-sheet-print-area {
    display: block !important;
    position: relative !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    box-shadow: none !important;
    page-break-before: auto !important;
    break-before: auto !important;
    page-break-after: auto !important;
    break-after: auto !important;
    page-break-inside: auto !important;
    break-inside: auto !important;
  }

  /* Saat mencetak rapor biasa (single/batch), sembunyikan sisa wrapper non-cetak */
  body:not(.printing-ranking) .print-container > :not([class*="space-y-6"]):not([class*="space-y-4"]) {
    /* pastikan modal atau wrapper di luar print area tidak memakan ruang cetak */
  }

  /* 5. Pagination per halaman: SINGLE MODE HARUS TEPAT 1 LEMBAR */
  #asts-report-single-area {
    page-break-before: avoid !important;
    break-before: avoid !important;
    page-break-after: avoid !important;
    break-after: avoid !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  /* BATCH MODE: Ganti halaman per siswa, kecuali siswa terakhir */
  #asts-report-batch-area .print-page {
    page-break-after: always !important;
    break-after: page !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  #asts-report-batch-area .print-page:last-child {
    page-break-after: auto !important;
    break-after: auto !important;
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  @page {
    margin: 4mm 8mm 4mm 8mm !important;
  }

  /* Pastikan border tabel rapor terlihat tajam */
  table {
    page-break-inside: auto !important;
    break-inside: auto !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  tr {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }
}
</style>
