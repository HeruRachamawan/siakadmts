<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend tracking-wide uppercase">Data Siswa</h1>
          <p class="text-xs text-slate-400 font-medium">{{ totalRecords }} data ditemukan</p>
        </div>
      </div>
    </div>

    <!-- Action Bar (filters + buttons, styled like reference) -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
      <!-- Per Page -->
      <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-600 shadow-sm">
        <select
          v-model.number="selectedPerPage"
          class="bg-transparent border-none p-0 text-slate-700 font-semibold focus:ring-0 cursor-pointer text-sm pr-1"
          @change="onPerPageChange"
        >
          <option :value="10">10 Baris</option>
          <option :value="20">20 Baris</option>
          <option :value="50">50 Baris</option>
          <option :value="-1">Semua</option>
        </select>
        <svg class="w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
      </div>

      <!-- Class Filter -->
      <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-600 shadow-sm">
        <select
          v-model="selectedClass"
          class="bg-transparent border-none p-0 text-slate-700 font-semibold focus:ring-0 cursor-pointer text-sm pr-1"
          @change="load"
        >
          <option value="">Semua Kelas</option>
          <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
        </select>
        <svg class="w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
      </div>

      <!-- Search -->
      <div class="relative flex-1">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari data..."
          class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
          @input="onSearchInput"
        />
        <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
      </div>

      <!-- Right Buttons -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <button
          @click="exportExcelFile('students')"
          :disabled="exportingExcel"
          class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold rounded-xl text-xs hover:bg-emerald-100 disabled:opacity-50 transition-colors shadow-sm cursor-pointer"
        >
          <svg v-if="exportingExcel" class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
          <svg v-else class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span>{{ exportingExcel ? 'Mengekspor...' : 'Export Excel' }}</span>
        </button>
        <button
          @click="showImportModal = true"
          class="flex items-center gap-2 px-4 py-2.5 bg-teal-50 border border-teal-200 text-teal-700 font-bold rounded-xl text-xs hover:bg-teal-100 transition-colors shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
          <span>Import Excel</span>
        </button>
        <button
          @click="showForm = true; editing = null"
          class="flex items-center gap-2 px-5 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-colors shadow-sm cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"></path></svg>
          Tambah
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <!-- Loading -->
      <SkeletonTable v-if="loading" :columns="6" :rows="5" :avatar="true" class="py-4" />

      <!-- Table Body -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="border-b border-slate-100">
              <th class="w-10 px-4 py-4 text-center">
                <input
                  type="checkbox"
                  :checked="isAllSelected"
                  :indeterminate="isPartiallySelected"
                  @change="toggleSelectAll"
                  class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer"
                  title="Pilih Semua Siswa di Halaman Ini"
                />
              </th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA SISWA</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NISN / NIS / NIK</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">JENIS KELAMIN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">KELAS</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr
              v-for="(row, index) in students"
              :key="row.id"
              :class="selectedStudentIds.includes(row.id) ? 'bg-emerald-50/50' : 'hover:bg-slate-50/70'"
              class="transition-colors"
            >
              <!-- Checkbox -->
              <td class="w-10 px-4 py-4 text-center">
                <input
                  type="checkbox"
                  :value="row.id"
                  v-model="selectedStudentIds"
                  class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer"
                />
              </td>

              <!-- NO -->
              <td class="px-6 py-4 text-sm font-bold text-slate-400">
                {{ selectedPerPage === -1 ? index + 1 : (currentPage - 1) * selectedPerPage + index + 1 }}
              </td>

              <!-- Nama + Avatar -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <!-- Avatar / Photo -->
                  <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-200 flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm border border-slate-200">
                    <img v-if="row.photo_url" :src="row.photo_url" alt="Photo" class="w-full h-full object-cover" />
                    <div v-else :class="getAvatarColor(row.gender)" class="w-full h-full flex items-center justify-center">
                      {{ getInitials(row.full_name) }}
                    </div>
                  </div>
                  <div>
                    <span class="text-sm font-bold text-slate-800 block">{{ row.full_name || '-' }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">{{ row.birth_place || '-' }}</span>
                  </div>
                </div>
              </td>

              <!-- Identitas NISN / NIS / NIK -->
              <td class="px-6 py-4">
                <div class="text-xs font-bold text-slate-800 font-mono">{{ row.nisn || '-' }} / {{ row.nis || '-' }}</div>
                <div v-if="row.nik" class="text-[10px] text-emerald-700 font-mono font-medium mt-0.5">NIK: {{ row.nik }}</div>
              </td>

              <!-- Gender Badge -->
              <td class="px-6 py-4">
                <span :class="row.gender === 'L' ? 'text-blue-600' : (row.gender === 'P' ? 'text-pink-600' : 'text-slate-400')" class="text-sm font-medium">
                  {{ formatGender(row.gender) }}
                </span>
              </td>

              <!-- Kelas -->
              <td class="px-6 py-4">
                <span class="px-3 py-1.5 bg-slate-100 text-slate-700 text-[11px] font-bold rounded-lg">
                  {{ row.class_name || row.class_room?.name || row.classRoom?.name || '-' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- Pindah Kelas Siswa (Individual) -->
                  <button
                    @click="openTransferModal([row])"
                    title="Pindahkan Siswa ke Kelas Lain"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-indigo-50 text-indigo-700 hover:bg-indigo-100 hover:border-indigo-300 border border-indigo-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <ArrowRightLeft class="w-3.5 h-3.5" />
                  </button>

                  <!-- Login Sebagai Siswa (Khusus Super Admin) -->
                  <button
                    v-if="isAdminSuper"
                    @click="loginAsStudent(row)"
                    title="Login Sebagai Siswa Ini (Mode Peninjauan)"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-purple-50 text-purple-700 hover:bg-purple-100 hover:border-purple-300 border border-purple-200 transition-all shadow-2xs cursor-pointer"
                  >
                    <LogIn class="w-3.5 h-3.5" />
                  </button>

                  <!-- View -->
                  <button
                    @click="viewDetail(row)"
                    title="Lihat Detail"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Eye class="w-3.5 h-3.5" />
                  </button>

                  <!-- Reset Password -->
                  <button
                    @click="resetPassword(row)"
                    title="Reset Password"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Key class="w-3.5 h-3.5" />
                  </button>

                  <!-- Edit -->
                  <button
                    @click="edit(row)"
                    title="Edit Siswa"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>

                  <!-- Delete -->
                  <button
                    @click="remove(row)"
                    title="Hapus Siswa"
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!students.length">
              <td colspan="7" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                  <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                  </div>
                  <p class="text-sm font-semibold">Tidak ada data siswa ditemukan.</p>
                  <p class="text-xs">Coba ubah filter atau tambahkan data baru.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1 && selectedPerPage !== -1" class="flex items-center justify-between px-2">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">
        Halaman {{ currentPage }} dari {{ totalPages }}
      </p>
      <nav class="flex items-center gap-1">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1 || loading"
          class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 disabled:opacity-30 transition-all"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <template v-for="page in visiblePages" :key="page">
          <button
            v-if="page !== '...'"
            @click="goToPage(page)"
            :class="currentPage === page ? 'bg-[#111827] text-white font-bold border-[#111827]' : 'text-slate-500 hover:bg-white hover:border-slate-200 font-medium'"
            class="w-9 h-9 flex items-center justify-center rounded-xl text-sm transition-all border border-transparent"
          >{{ page }}</button>
          <span v-else class="w-9 h-9 flex items-center justify-center text-slate-400 text-sm">...</span>
        </template>
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages || loading"
          class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 disabled:opacity-30 transition-all"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      </nav>
    </div>

    <!-- Modal Form -->
    <StudentForm
      v-if="showForm"
      :title="editing ? 'Edit Siswa' : 'Tambah Siswa'"
      :model="editing || {}"
      @close="showForm = false"
      @save="save"
    />

    <!-- Detail Modal -->
    <StudentDetailModal
      v-if="showDetail"
      :student="detailStudent"
      @close="showDetail = false"
      @reset-password="resetPassword"
      @impersonate="(s) => { showDetail = false; loginAsStudent(s); }"
    />

    <!-- Excel Import Modal -->
    <ExcelImportModal
      :show="showImportModal"
      type="students"
      title="Siswa"
      @close="showImportModal = false"
      @success="load"
    />

    <!-- Floating Bulk Action Bar -->
    <Transition name="fade">
      <div
        v-if="selectedStudentIds.length > 0"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-slate-900/95 backdrop-blur-md text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700/60 flex items-center gap-4 animate-in fade-in slide-in-from-bottom-4 duration-200 max-w-xl w-[92vw] sm:w-auto"
      >
        <div class="flex items-center gap-2.5">
          <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="text-xs sm:text-sm font-bold tracking-wide">
            <strong>{{ selectedStudentIds.length }}</strong> siswa dipilih
          </span>
        </div>

        <div class="h-4 w-px bg-slate-700"></div>

        <div class="flex items-center gap-2">
          <button
            @click="openTransferModalFromSelection"
            class="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer"
          >
            <ArrowRightLeft class="w-3.5 h-3.5" />
            <span>Pindahkan ke Kelas...</span>
          </button>

          <button
            @click="selectedStudentIds = []"
            class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium rounded-xl text-xs transition-all cursor-pointer"
          >
            Batal
          </button>
        </div>
      </div>
    </Transition>

    <!-- Modal Pindah Kelas Siswa (Single & Bulk) -->
    <Transition name="modal-fade">
      <div v-if="showTransferModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 font-inter">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showTransferModal = false"></div>

        <div class="relative bg-white rounded-3xl p-6 shadow-2xl w-full max-w-lg flex flex-col space-y-5 border border-slate-100 animate-slide-up">
          <!-- Header Modal -->
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-200/80 flex items-center justify-center font-bold">
                <ArrowRightLeft class="w-5 h-5" />
              </div>
              <div>
                <h3 class="font-lexend font-black text-slate-800 text-lg uppercase tracking-wider">Pindahkan Siswa</h3>
                <p class="text-xs text-slate-500 font-medium">Pindahkan siswa terpilih ke rombel/kelas baru</p>
              </div>
            </div>
            <button @click="showTransferModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
          </div>

          <!-- Body Modal -->
          <div class="space-y-4">
            <!-- Siswa yang dipindahkan -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa yang Dipindahkan</span>
                <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">
                  {{ transferStudentsList.length }} Siswa
                </span>
              </div>

              <!-- Jika 1 siswa: tampilkan detail singkat -->
              <div v-if="transferStudentsList.length === 1" class="flex items-center gap-3 pt-1">
                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-700">
                  {{ getInitials(transferStudentsList[0].full_name) }}
                </div>
                <div>
                  <h4 class="text-sm font-bold text-slate-800">{{ transferStudentsList[0].full_name }}</h4>
                  <p class="text-xs text-slate-400">
                    Kelas Sekarang: <strong class="text-slate-600">{{ transferStudentsList[0].class_name || transferStudentsList[0].classRoom?.name || '-' }}</strong>
                  </p>
                </div>
              </div>

              <!-- Jika banyak siswa: pill list ringkas -->
              <div v-else class="max-h-28 overflow-y-auto pr-1 flex flex-wrap gap-1.5 pt-1">
                <span
                  v-for="st in transferStudentsList"
                  :key="st.id"
                  class="inline-flex items-center gap-1 text-[11px] font-semibold bg-white border border-slate-200 text-slate-700 px-2.5 py-1 rounded-lg shadow-2xs"
                >
                  <span>{{ st.full_name }}</span>
                  <span class="text-[10px] text-slate-400">({{ st.class_name || '-' }})</span>
                </span>
              </div>
            </div>

            <!-- Pilih Kelas Tujuan -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Pilih Kelas Tujuan <span class="text-rose-500">*</span>
              </label>
              <div class="relative">
                <select
                  v-model="targetClassId"
                  class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400/30 focus:border-indigo-500 cursor-pointer shadow-xs"
                >
                  <option value="" disabled>-- Pilih Kelas Tujuan --</option>
                  <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                    {{ cls.name }} (Tingkat: {{ cls.grade_level || '-' }} - Wali: {{ cls.homeroom_teacher?.full_name || cls.homeroomTeacher?.full_name || 'Belum ada' }})
                  </option>
                </select>
              </div>
              <p class="text-[11px] text-slate-400">
                Siswa yang dipindahkan akan otomatis terdaftar di rombel kelas baru ini.
              </p>
            </div>
          </div>

          <!-- Footer Buttons -->
          <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              @click="showTransferModal = false"
              class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button
              type="button"
              @click="executeTransfer"
              :disabled="!targetClassId || submittingTransfer"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2 cursor-pointer"
            >
              <svg v-if="submittingTransfer" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle></svg>
              <span>{{ submittingTransfer ? 'Memindahkan...' : 'Konfirmasi Pindahkan' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { api } from '../api';
import StudentForm from '../components/StudentForm.vue';
import StudentDetailModal from '../components/StudentDetailModal.vue';
import SkeletonTable from '../components/SkeletonTable.vue';
import ExcelImportModal from '../components/ExcelImportModal.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import { useAuthStore } from '../stores/auth';
import { Eye, Key, Pencil, Trash2, LogIn, ArrowRightLeft } from 'lucide-vue-next';

const auth = useAuthStore();
const router = useRouter();
const { success, error: showError } = useToast();
const { confirm } = useConfirm();

const isAdminSuper = computed(() => {
  return auth.activeRole === 'admin' || auth.user?.role === 'admin';
});

async function loginAsStudent(row) {
  const studentName = row.full_name || row.user?.name || 'Siswa';
  const isConfirmed = await confirm({
    title: 'Login Sebagai Siswa',
    message: `Apakah Anda ingin login dan masuk ke tampilan Portal Siswa sebagai "${studentName}"? Anda dapat kembali ke akun Super Admin kapan saja.`,
    type: 'primary',
    confirmText: 'Ya, Masuk Sebagai Siswa',
    cancelText: 'Batal',
  });
  if (!isConfirmed) return;

  try {
    const res = await api.post(`admin/students/${row.id}/impersonate`);
    const data = res.data?.data || res.data || res;
    if (data.token) {
      auth.startImpersonation(data.token, data.user);
      success(`Berhasil login sebagai siswa ${studentName}`);
      router.push('/student/dashboard');
    }
  } catch (err) {
    showError(err.response?.data?.message || 'Gagal login sebagai siswa.');
  }
}

const loading = ref(true);
const exportingExcel = ref(false);

const exportExcelFile = async (type) => {
  exportingExcel.value = true;
  try {
    const token = localStorage.getItem('token');
    const response = await fetch(`/api/admin/excel/export/${type}`, {
      headers: {
        'Authorization': token ? `Bearer ${token}` : '',
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/json',
      },
    });
    if (!response.ok) throw new Error('Gagal mengekspor file Excel');
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Data_Export_${type}_YASPIN.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    success('Data Excel berhasil diekspor!');
  } catch (err) {
    console.error(err);
    showError('Gagal mengekspor file Excel');
  } finally {
    exportingExcel.value = false;
  }
};
const showForm = ref(false);
const showDetail = ref(false);
const showImportModal = ref(false);
const editing = ref(null);
const detailStudent = ref(null);
const students = ref([]);
const classes = ref([]);
const fileInput = ref(null);
const importing = ref(false);

const searchQuery = ref('');
const selectedClass = ref('');
const currentPage = ref(1);
const selectedPerPage = ref(10);
const totalPages = ref(1);
const totalRecords = ref(0);

// Checkbox Multi-Selection
const selectedStudentIds = ref([]);
const isAllSelected = computed(() => {
  if (!students.value.length) return false;
  return students.value.every(s => selectedStudentIds.value.includes(s.id));
});
const isPartiallySelected = computed(() => {
  return selectedStudentIds.value.length > 0 && !isAllSelected.value;
});
function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedStudentIds.value = [];
  } else {
    selectedStudentIds.value = students.value.map(s => s.id);
  }
}

// Transfer Class State
const showTransferModal = ref(false);
const transferStudentsList = ref([]);
const targetClassId = ref('');
const submittingTransfer = ref(false);

function openTransferModal(selectedRows) {
  transferStudentsList.value = selectedRows;
  targetClassId.value = '';
  showTransferModal.value = true;
}

function openTransferModalFromSelection() {
  const selected = students.value.filter(s => selectedStudentIds.value.includes(s.id));
  if (selected.length === 0) return;
  openTransferModal(selected);
}

async function executeTransfer() {
  if (!targetClassId.value || transferStudentsList.value.length === 0) return;
  submittingTransfer.value = true;
  try {
    const res = await api.post('admin/students/transfer-class', {
      student_ids: transferStudentsList.value.map(s => s.id),
      target_class_id: targetClassId.value,
    });
    success(res?.message || 'Siswa berhasil dipindahkan ke kelas baru!');
    showTransferModal.value = false;
    selectedStudentIds.value = [];
    await load();
  } catch (err) {
    showError(err.response?.data?.message || 'Gagal memindahkan siswa.');
  } finally {
    submittingTransfer.value = false;
  }
}

const columns = [
  { label: 'Nama Siswa', field: 'full_name' },
  { label: 'NIS', field: 'nis' },
  { label: 'Jenis Kelamin', field: 'gender' },
  { label: 'Kelas', field: 'class_name' },
];

const avatarColors = [
  'bg-blue-400', 'bg-purple-400', 'bg-pink-400', 'bg-amber-400',
  'bg-emerald-400', 'bg-cyan-400', 'bg-indigo-400', 'bg-rose-400',
];

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function getAvatarColor(gender) {
  if (gender === 'L') return 'bg-blue-400';
  if (gender === 'P') return 'bg-pink-400';
  return 'bg-slate-400';
}

function formatGender(g) {
  if (g === 'L') return 'Laki-laki';
  if (g === 'P') return 'Perempuan';
  return '-';
}

onMounted(() => {
  loadClasses();
  load();
});

async function loadClasses() {
  try {
    const res = await api.get('admin/classes');
    const data = res.data?.data || res.data || [];
    classes.value = Array.isArray(data) ? data : data.data || [];
  } catch {
    classes.value = [];
  }
}

async function load() {
  loading.value = true;
  selectedStudentIds.value = [];
  try {
    const params = new URLSearchParams();
    if (selectedPerPage.value !== -1) {
      params.set('page', currentPage.value);
      params.set('per_page', selectedPerPage.value);
    } else {
      params.set('per_page', 9999);
    }
    if (searchQuery.value) params.append('search', searchQuery.value);
    if (selectedClass.value) params.append('class_id', selectedClass.value);

    const res = await api.get(`admin/students?${params.toString()}`);
    const rawData = res.data?.data || res.data || [];
    students.value = Array.isArray(rawData) ? rawData : rawData.data || [];

    const meta = res.data?.meta;
    totalPages.value = meta?.last_page || 1;
    totalRecords.value = meta?.total || students.value.length;
    currentPage.value = meta?.current_page || 1;
  } catch {
    students.value = [];
    totalPages.value = 1;
    totalRecords.value = 0;
  } finally {
    loading.value = false;
  }
}

let searchTimeout = null;
function onSearchInput() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    currentPage.value = 1;
    load();
  }, 400);
}

function onPerPageChange() {
  currentPage.value = 1;
  load();
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  load();
}

const visiblePages = computed(() => {
  if (totalPages.value <= 1) return [];
  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, currentPage.value + 2);
  if (start > 1) pages.push(1, '...');
  for (let i = start; i <= end; i++) pages.push(i);
  if (end < totalPages.value) pages.push('...', totalPages.value);
  return pages;
});

function viewDetail(row) {
  detailStudent.value = row;
  showDetail.value = true;
}

function edit(row) {
  editing.value = { ...row };
  showForm.value = true;
}

async function remove(row) {
  const isConfirmed = await confirm({
    title: 'Hapus Data Siswa',
    message: `Apakah Anda yakin ingin menghapus siswa "${row.full_name}"?`,
    type: 'danger',
    confirmText: 'Ya, Hapus',
  });

  if (isConfirmed) {
    try {
      await api.del('admin/students/' + row.id);
      success('Siswa berhasil dihapus.');
      load();
    } catch {
      showError('Gagal menghapus siswa.');
    }
  }
}

async function resetPassword(row) {
  const isConfirmed = await confirm({
    title: 'Reset Password Siswa',
    message: `Apakah Anda yakin ingin mereset password untuk ${row.full_name} menjadi NIS?`,
    type: 'warning',
    confirmText: 'Ya, Reset Password',
  });

  if (!isConfirmed) return;
  api.post(`/admin/students/${row.id}/reset-credentials`)
    .then(res => {
      success('Password berhasil direset menjadi NIS');
    })
    .catch(err => {
      showError('Gagal mereset password');
    });
}

async function downloadTemplate() {
  try {
    const response = await axios.get('/admin/students/template', {
      responseType: 'blob'
    });
    
    // Create a URL for the blob
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'template_siswa.xlsx');
    document.body.appendChild(link);
    link.click();
    
    // Cleanup
    link.parentNode.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (err) {
    showError('Gagal mengunduh template');
  }
}

async function handleImport(event) {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.name.endsWith('.csv') && !file.name.endsWith('.xlsx') && !file.name.endsWith('.xls')) {
    showError('File harus berupa Excel (.xlsx/.xls) atau CSV');
    event.target.value = '';
    return;
  }

  const formData = new FormData();
  formData.append('file', file);

  importing.value = true;
  try {
    const response = await api.postForm('/admin/students/import', formData);
    success(response.data.message || 'Data siswa berhasil diimpor');
    load();
  } catch (err) {
    showError(err.response?.data?.message || 'Gagal mengimpor data siswa');
  } finally {
    importing.value = false;
    event.target.value = ''; // Reset input
  }
}

function save({ payload, isFormData }) {
  const isEditing = !!editing.value;
  const url = isEditing ? `admin/students/${editing.value.id}` : 'admin/students';
  
  // Decide which API method to use based on FormData flag
  const fn = isFormData 
    ? (isEditing ? api.putForm : api.postForm) 
    : (isEditing ? api.put : api.post);
    
  fn(url, payload).then(() => {
    showForm.value = false;
    editing.value = null;
    load();
    success(isEditing ? 'Data siswa berhasil diperbarui.' : 'Siswa baru berhasil ditambahkan.');
  }).catch((err) => {
    console.error('Save failed:', err);
    showError(err.response?.data?.message || (isEditing ? 'Gagal memperbarui data siswa.' : 'Gagal menyimpan siswa baru.'));
  });
}
</script>

<style scoped>
.font-inter { font-family: 'Inter', system-ui, sans-serif; }
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
