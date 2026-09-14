<template>
  <div class="space-y-6 font-inter print-container">
    <!-- TOP HEADER (No Print) -->
    <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center shadow-lg shadow-emerald-600/20 flex-shrink-0">
          <BookOpenCheck class="w-6 h-6" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">Rapor Tengah Semester (ASTS)</h1>
            <span
              class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wide border shadow-2xs"
              :class="activeSemester === 'genap' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200'"
            >
              ASTS {{ activeSemester === 'genap' ? 'Genap' : 'Ganjil' }}
            </span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5 font-medium">
            Ledger nilai kolektif kelas terintegrasi langsung dengan nilai koreksi ujian, catatan wali kelas, dan pencetakan rapor resmi.
          </p>
        </div>
      </div>

      <!-- Quick Semester Toggle & Year Badge -->
      <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
        <div class="flex p-1 bg-slate-100/90 rounded-xl border border-slate-200/60 shadow-2xs">
          <button
            type="button"
            @click="setSemester('ganjil')"
            :class="activeSemester === 'ganjil' ? 'bg-white text-emerald-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5"
          >
            <span>📘 ASTS Ganjil</span>
          </button>
          <button
            type="button"
            @click="setSemester('genap')"
            :class="activeSemester === 'genap' ? 'bg-white text-amber-800 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-bold'"
            class="px-3.5 py-1.5 rounded-lg text-xs transition-all cursor-pointer flex items-center gap-1.5"
          >
            <span>📙 ASTS Genap</span>
          </button>
        </div>

        <div v-if="activeYear" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 font-mono flex items-center gap-1.5 flex-shrink-0">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>T.A. {{ activeYear.year || '2026/2027' }}</span>
        </div>
      </div>
    </div>

    <!-- FILTER & SUB-TAB NAVIGATION BAR (No Print) -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
      <!-- Class Selector -->
      <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap flex-1">
        <div class="w-full sm:w-64 space-y-1">
          <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider">Pilih Kelas</label>
          <select
            v-model="selectedClassId"
            @change="fetchLedger"
            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400 cursor-pointer"
          >
            <option value="">-- Pilih Kelas --</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">
              Kelas {{ c.name }} (Tingkat {{ c.grade_level }}) — {{ c.students_count || 0 }} Siswa
            </option>
          </select>
        </div>

        <div v-if="ledgerData?.class?.homeroom_teacher" class="text-xs text-slate-500 pt-3.5 sm:pt-4">
          Wali Kelas: <strong class="text-slate-800">{{ ledgerData.class.homeroom_teacher.full_name }}</strong>
        </div>
      </div>

      <!-- Sub-Tabs: Ledger vs Cetak Rapor -->
      <div class="flex p-1 bg-slate-100 rounded-xl border border-slate-200/80 flex-shrink-0">
        <button
          type="button"
          @click="activeSubTab = 'ledger'"
          :class="activeSubTab === 'ledger' ? 'bg-white text-emerald-700 shadow-xs font-black' : 'text-slate-500 font-bold hover:text-slate-800'"
          class="px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
        >
          <TableProperties class="w-4 h-4 text-emerald-600" />
          <span>Ledger Nilai Kelas</span>
        </button>

        <button
          type="button"
          @click="activeSubTab = 'print'"
          :class="activeSubTab === 'print' ? 'bg-white text-emerald-700 shadow-xs font-black' : 'text-slate-500 font-bold hover:text-slate-800'"
          class="px-4 py-2 rounded-lg text-xs transition-all flex items-center gap-2 cursor-pointer"
        >
          <Printer class="w-4 h-4 text-emerald-600" />
          <span>Cetak Lembar Rapor</span>
        </button>
      </div>
    </div>

    <!-- LOADING STATE -->
    <div v-if="loading" class="bg-white rounded-[2rem] p-16 text-center text-slate-400 text-xs font-medium border border-slate-100 no-print">
      <div class="animate-spin h-8 w-8 border-3 border-emerald-500 border-t-transparent rounded-full mx-auto mb-3"></div>
      Memuat data Rapor ASTS...
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
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex items-center gap-2 flex-wrap text-xs">
            <span class="font-black text-slate-700">Status Pengumpulan Nilai:</span>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 flex items-center gap-1">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              {{ subjectsWithScoresCount }} / {{ subjectsList.length }} Mapel Terisi
            </span>
            <span class="text-slate-400">• {{ ledgerStudents.length }} Siswa</span>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- 1-Click Auto Pull Button -->
            <button
              type="button"
              @click="autoPullScores"
              :disabled="pullingScores"
              class="px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm flex items-center gap-1.5 cursor-pointer disabled:opacity-50 active:scale-95"
              title="Tarik otomatis nilai koreksi jadi dari paket ujian STS/ASTS yang sudah dikoreksi guru"
            >
              <Sparkles v-if="!pullingScores" class="w-3.5 h-3.5 text-amber-200" />
              <div v-else class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></div>
              <span>{{ pullingScores ? 'Menarik Nilai...' : '⚡ Tarik Otomatis Nilai Koreksi STS' }}</span>
            </button>

            <!-- Export Excel Ledger -->
            <button
              type="button"
              @click="exportLedgerExcel"
              class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold rounded-xl text-xs transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
              <Download class="w-3.5 h-3.5 text-emerald-600" />
              <span>Export Excel (.xlsx)</span>
            </button>
          </div>
        </div>

        <!-- Subject Deposit Status Badges -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-2">
          <div class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Daftar Mata Pelajaran & Status Setor Nilai:</div>
          <div class="flex items-center gap-2 flex-wrap">
            <div
              v-for="st in subjectStatuses"
              :key="st.subject_id"
              class="px-2.5 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-1.5"
              :class="st.has_scores ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200'"
              :title="st.has_scores ? `Sudah disetorkan (${st.synced_count} nilai) • Terakhir: ${st.last_synced_at || '-'}` : 'Belum ada nilai yang disetorkan untuk mapel ini'"
            >
              <span class="w-2 h-2 rounded-full" :class="st.has_scores ? 'bg-emerald-500' : 'bg-slate-300'"></span>
              <span>{{ st.subject_name }}</span>
              <span v-if="st.has_scores" class="text-[10px] text-emerald-600 font-mono">✓</span>
              <span v-else class="text-[10px] text-slate-400 font-mono">⏳</span>
            </div>
          </div>
        </div>

        <!-- Ledger Table Matrix -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
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
                  <th class="px-2 py-3 text-center w-14 font-black text-amber-800 bg-amber-50/70">Rank</th>
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
                        {{ st.scores[sbj.id].score }}
                      </span>
                    </template>
                    <span v-else class="text-slate-300">-</span>
                  </td>

                  <!-- Total, Average & Rank -->
                  <td class="px-3 py-3 text-center font-bold font-mono bg-slate-50/50 text-slate-800">
                    {{ st.total_score }}
                  </td>
                  <td class="px-3 py-3 text-center font-black font-mono bg-emerald-50/40 text-emerald-700">
                    {{ st.average_score }}
                  </td>
                  <td class="px-2 py-3 text-center font-black font-lexend bg-amber-50/40 text-amber-800">
                    {{ st.rank }}
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
                      <Printer class="w-3 h-3" />
                      <span>Rapor</span>
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

      <!-- ==================== SUB-TAB 2: PRATINJAU & CETAK RAPOR RESMI ==================== -->
      <div v-else-if="activeSubTab === 'print'" class="space-y-6">
        <!-- Print Control Bar (No Print) -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
          <div class="flex flex-wrap items-center gap-4">
            <!-- Mode Cetak -->
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Mode Pencetakan</label>
              <select v-model="printMode" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none cursor-pointer">
                <option value="single">Per Siswa Terpilih</option>
                <option value="batch">Cetak Massal 1 Kelas Sekaligus ({{ ledgerStudents.length }} Siswa)</option>
              </select>
            </div>

            <!-- Student Picker (if single mode) -->
            <div v-if="printMode === 'single'">
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pilih Siswa</label>
              <select v-model="selectedStudentId" @change="fetchSingleReport" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none cursor-pointer min-w-[220px]">
                <option v-for="s in ledgerStudents" :key="'pick-'+s.student_id" :value="s.student_id">
                  {{ s.full_name }} ({{ s.gender }})
                </option>
              </select>
            </div>

            <!-- Paper Size -->
            <div>
              <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ukuran Kertas</label>
              <select v-model="selectedPaperSize" class="bg-emerald-50 border border-emerald-200 rounded-xl px-3 py-2 text-xs font-bold text-emerald-800 focus:outline-none cursor-pointer">
                <option value="f4">📜 F4 / Folio (215mm x 330mm) - Standar Kemenag</option>
                <option value="a4">📄 A4 (210mm x 297mm)</option>
              </select>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="triggerPrint"
              type="button"
              class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-all flex items-center gap-2 shadow-md cursor-pointer active:scale-95"
            >
              <Printer class="w-4 h-4 text-emerald-400" />
              <span>{{ printMode === 'batch' ? `Cetak Rapor 1 Kelas (${ledgerStudents.length} Siswa)` : 'Cetak Rapor Siswa Ini' }}</span>
            </button>
          </div>
        </div>

        <!-- PRINT AREA CONTAINER (Rendered on screen & on print) -->
        <div id="asts-report-printable-area" class="space-y-8">
          <!-- BATCH MODE: LOOP THROUGH ALL STUDENTS -->
          <template v-if="printMode === 'batch'">
            <div
              v-for="(rep, rIdx) in batchReportsList"
              :key="'batch-rep-'+rIdx"
              class="bg-white p-8 sm:p-10 rounded-3xl shadow-sm border border-slate-200 text-slate-900 space-y-5 print-page"
            >
              <AstsReportSheet :report="rep" />
            </div>
          </template>

          <!-- SINGLE MODE: SINGLE STUDENT REPORT -->
          <template v-else>
            <div v-if="singleReportData" class="bg-white p-8 sm:p-10 rounded-3xl shadow-sm border border-slate-200 text-slate-900 space-y-5 print-page">
              <AstsReportSheet :report="singleReportData" />
            </div>
            <div v-else class="text-center py-12 text-slate-400 text-xs font-medium bg-white rounded-3xl border border-slate-100">
              Memuat data lembar rapor siswa...
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- MODAL CATATAN WALI KELAS & PRESENSI -->
    <div v-if="showNotesModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6 no-print">
      <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 transform transition-all">
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <h2 class="text-base font-black text-slate-800 font-lexend uppercase tracking-wider">Catatan Wali Kelas & Presensi</h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">{{ editingStudent?.full_name }} • Kelas {{ ledgerData?.class?.name }}</p>
          </div>
          <button @click="showNotesModal = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-400 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="saveNotesSubmit" class="p-6 space-y-4">
          <!-- Attendance Counts -->
          <div class="space-y-1">
            <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider">Rekapitulasi Ketidakhadiran Tengah Semester</label>
            <div class="grid grid-cols-3 gap-3 pt-1">
              <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <span class="block text-[10px] font-bold text-slate-500 uppercase">Sakit (S)</span>
                <input
                  v-model.number="notesForm.sick_count"
                  type="number"
                  min="0"
                  class="w-full text-center text-sm font-black text-slate-800 bg-white border border-slate-300 rounded-lg py-1 mt-1 focus:ring-2 focus:ring-emerald-400 font-mono"
                />
              </div>
              <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <span class="block text-[10px] font-bold text-slate-500 uppercase">Izin (I)</span>
                <input
                  v-model.number="notesForm.permission_count"
                  type="number"
                  min="0"
                  class="w-full text-center text-sm font-black text-slate-800 bg-white border border-slate-300 rounded-lg py-1 mt-1 focus:ring-2 focus:ring-emerald-400 font-mono"
                />
              </div>
              <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
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
            <label class="block text-[11px] font-black text-slate-700 uppercase tracking-wider">Catatan Perkembangan & Motivasi Siswa</label>
            <textarea
              v-model="notesForm.homeroom_notes"
              rows="3"
              placeholder="Contoh: Tingkatkan terus ketekunan belajar dan keaktifan dalam ibadah berjamaah."
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:ring-2 focus:ring-emerald-400"
            ></textarea>
          </div>

          <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
            <button
              type="button"
              @click="showNotesModal = false"
              class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="savingNotes"
              class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-sm flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <span>{{ savingNotes ? 'Menyimpan...' : 'Simpan Catatan' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import * as XLSX from 'xlsx';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import AstsReportSheet from '../components/AstsReportSheet.vue';
import {
  BookOpenCheck,
  GraduationCap,
  TableProperties,
  Printer,
  Sparkles,
  Download,
  X,
} from 'lucide-vue-next';

const toast = useToast();
const loading = ref(false);
const pullingScores = ref(false);
const savingNotes = ref(false);

const activeSemester = ref('ganjil'); // 'ganjil' | 'genap'
const activeSubTab = ref('ledger'); // 'ledger' | 'print'
const printMode = ref('single'); // 'single' | 'batch'
const selectedPaperSize = ref('f4'); // 'f4' | 'a4'

const classes = ref([]);
const activeYear = ref(null);
const selectedClassId = ref('');
const ledgerData = ref(null);

const selectedStudentId = ref('');
const singleReportData = ref(null);
const batchReportsList = ref([]);

// Modal Notes
const showNotesModal = ref(false);
const editingStudent = ref(null);
const notesForm = reactive({
  student_id: null,
  semester: 'ganjil',
  academic_year_id: null,
  sick_count: 0,
  permission_count: 0,
  unexcused_count: 0,
  homeroom_notes: '',
});

const subjectsList = computed(() => ledgerData.value?.subjects || []);
const subjectStatuses = computed(() => ledgerData.value?.subject_statuses || []);
const ledgerStudents = computed(() => ledgerData.value?.students || []);

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

async function fetchOptions() {
  try {
    const res = await api.get('/teacher/asts-reports/options');
    const d = res?.data || res || {};
    classes.value = d.classes || [];
    activeYear.value = d.active_academic_year || null;

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

async function autoPullScores() {
  if (!selectedClassId.value) return;
  pullingScores.value = true;
  try {
    const res = await api.post('/teacher/asts-reports/auto-pull', {
      class_id: selectedClassId.value,
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
    });
    const msg = res?.message || 'Nilai koreksi berhasil ditarik ke Rapor ASTS!';
    toast.success(msg);
    await fetchLedger();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menarik nilai koreksi.');
  } finally {
    pullingScores.value = false;
  }
}

function openNotesModal(student) {
  editingStudent.value = student;
  notesForm.student_id = student.student_id;
  notesForm.semester = activeSemester.value;
  notesForm.academic_year_id = activeYear.value?.id;
  notesForm.sick_count = student.sick_count || 0;
  notesForm.permission_count = student.permission_count || 0;
  notesForm.unexcused_count = student.unexcused_count || 0;
  notesForm.homeroom_notes = student.homeroom_notes || '';
  showNotesModal.value = true;
}

async function saveNotesSubmit() {
  savingNotes.value = true;
  try {
    await api.post('/teacher/asts-reports/save-notes', notesForm);
    toast.success('Catatan wali kelas berhasil disimpan!');
    showNotesModal.value = false;
    await fetchLedger();
  } catch (err) {
    toast.error('Gagal menyimpan catatan wali kelas.');
  } finally {
    savingNotes.value = false;
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
  try {
    const res = await api.get(`/teacher/asts-reports/student/${selectedStudentId.value}`, {
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
    });
    singleReportData.value = res?.data || res || null;
  } catch (err) {
    toast.error('Gagal memuat pratinjau rapor siswa.');
  }
}

async function fetchBatchReports() {
  if (!selectedClassId.value) return;
  try {
    const res = await api.get(`/teacher/asts-reports/batch-class/${selectedClassId.value}`, {
      semester: activeSemester.value,
      academic_year_id: activeYear.value?.id,
    });
    const d = res?.data || res || {};
    batchReportsList.value = d.reports || [];
  } catch (err) {
    toast.error('Gagal memuat rapor massal 1 kelas.');
  }
}

async function fetchPrintData() {
  if (printMode.value === 'single') {
    await fetchSingleReport();
  } else {
    await fetchBatchReports();
  }
}

function triggerPrint() {
  const isF4 = selectedPaperSize.value === 'f4';
  const paperCss = isF4 ? '215mm 330mm' : '210mm 297mm';

  const style = document.createElement('style');
  style.id = 'dynamic-asts-page-style';
  style.innerHTML = `@page { size: ${paperCss}; margin: 8mm 10mm 8mm 10mm; }`;
  document.head.appendChild(style);

  window.print();

  setTimeout(() => {
    const el = document.getElementById('dynamic-asts-page-style');
    if (el) el.remove();
  }, 1000);
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
      r.push(score !== undefined && score !== null ? score : '-');
    });
    r.push(st.total_score, st.average_score, st.rank, st.sick_count, st.permission_count, st.unexcused_count, st.homeroom_notes || '-');
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

@media print {
  .no-print {
    display: none !important;
  }
  .print-page {
    page-break-after: always !important;
    break-after: page !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 0 20mm 0 !important;
  }
}
</style>
