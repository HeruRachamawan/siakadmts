<template>
  <div class="space-y-6 font-inter">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20 flex-shrink-0">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-black text-slate-800 font-lexend tracking-wide uppercase">Manajemen Pengguna & Jabatan</h1>
          <p class="text-xs text-slate-400 font-medium">Kelola akun staf, administrator, operator TU, kurikulum, dan bendahara sekolah.</p>
        </div>
      </div>

      <!-- Add User Button -->
      <button 
        @click="openCreateModal" 
        class="flex items-center gap-2 px-5 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition-all shadow-md active:scale-95 cursor-pointer whitespace-nowrap"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>+ Tambah Pengguna Jabatan</span>
      </button>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Staf</p>
        <p class="text-2xl font-black text-slate-800 font-lexend">{{ summary.total_staff || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-purple-600 uppercase tracking-wider">Super Admin</p>
        <p class="text-2xl font-black text-purple-600 font-lexend">{{ summary.admin || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Kepala Madrasah</p>
        <p class="text-2xl font-black text-indigo-600 font-lexend">{{ summary.kepala_sekolah || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Operator TU</p>
        <p class="text-2xl font-black text-emerald-600 font-lexend">{{ summary.operator || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Waka Kurikulum</p>
        <p class="text-2xl font-black text-blue-600 font-lexend">{{ summary.kurikulum || 0 }}</p>
      </div>

      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-1">
        <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Bendahara</p>
        <p class="text-2xl font-black text-amber-600 font-lexend">{{ summary.bendahara || 0 }}</p>
      </div>
    </div>

    <!-- Filter & Scope Bar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-4">
      <!-- Tabs Scope -->
      <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2">
          <button
            @click="setScope('staff')"
            :class="[activeScope === 'staff' ? 'bg-emerald-600 text-white font-bold shadow-md shadow-emerald-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold', 'px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2']"
          >
            <Building class="w-4 h-4" />
            <span>Khusus Jabatan & Staf</span>
            <span class="px-1.5 py-0.2 bg-white/20 rounded-md text-[10px] font-mono">{{ summary.total_staff || 0 }}</span>
          </button>
          <button
            @click="setScope('all')"
            :class="[activeScope === 'all' ? 'bg-slate-900 text-white font-bold shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold', 'px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2']"
          >
            <Globe class="w-4 h-4" />
            <span>Semua Akun Sistem</span>
            <span class="px-1.5 py-0.2 bg-white/20 rounded-md text-[10px] font-mono">{{ summary.all_users || 0 }}</span>
          </button>
        </div>

        <span class="text-xs text-slate-400 font-medium">
          Ditemukan <b>{{ totalRecords }}</b> pengguna
        </span>
      </div>

      <!-- Search & Role Filter Grid -->
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <!-- Per Page -->
        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5 w-full sm:w-auto">
          <select v-model.number="selectedPerPage" class="bg-transparent border-none p-0 text-slate-700 font-bold focus:ring-0 cursor-pointer text-xs pr-1" @change="onFilterChange">
            <option :value="10">10 Baris</option>
            <option :value="20">20 Baris</option>
            <option :value="50">50 Baris</option>
            <option :value="-1">Semua</option>
          </select>
        </div>

        <!-- Role Selector -->
        <div class="w-full sm:w-48">
          <select v-model="selectedRole" @change="onFilterChange" class="w-full bg-slate-50 border border-slate-200/80 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-emerald-400/30">
            <option value="all">Semua Peran</option>
            <option value="admin">Super Admin</option>
            <option value="kepala_sekolah">Kepala Madrasah</option>
            <option value="operator">Operator TU</option>
            <option value="kurikulum">Waka Kurikulum</option>
            <option value="bendahara">Bendahara</option>
            <option v-if="activeScope === 'all'" value="teacher">Guru</option>
            <option v-if="activeScope === 'all'" value="student">Siswa</option>
          </select>
        </div>

        <!-- Search Bar -->
        <div class="relative flex-1 w-full">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama, username, atau email pengguna..."
            class="w-full bg-slate-50 border border-slate-200/80 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
            @input="onSearchInput"
          />
          <svg class="absolute left-3.5 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-[2rem] shadow-[0_4px_24px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
      <div v-if="loading" class="text-center py-16">
        <div class="inline-flex w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
        <p class="text-sm font-semibold text-slate-400">Memuat data pengguna...</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50/75">
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NO</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">NAMA PENGGUNA</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">USERNAME LOGIN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">JABATAN / PERAN</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">TAUTAN PROFIL</th>
              <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(row, idx) in users" :key="row.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-6 py-4 font-mono font-bold text-slate-400">
                {{ selectedPerPage !== -1 ? (currentPage - 1) * selectedPerPage + idx + 1 : idx + 1 }}
              </td>

              <!-- Nama & Email -->
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-black text-sm uppercase flex-shrink-0 border border-slate-200">
                    {{ (row.name || '?').charAt(0) }}
                  </div>
                  <div>
                    <p class="font-bold text-slate-800 text-xs">{{ row.name }}</p>
                    <p class="text-[10px] text-slate-400 font-medium">{{ row.email || '-' }}</p>
                  </div>
                </div>
              </td>

              <!-- Username Login -->
              <td class="px-6 py-4">
                <span class="font-mono font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200/80">
                  {{ row.username }}
                </span>
              </td>

              <!-- Role Badge -->
              <td class="px-6 py-4">
                <span :class="[
                  row.role === 'admin' ? 'bg-purple-100 text-purple-800 border-purple-200' :
                  row.role === 'kepala_sekolah' ? 'bg-indigo-100 text-indigo-800 border-indigo-200' :
                  row.role === 'operator' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' :
                  row.role === 'kurikulum' ? 'bg-blue-100 text-blue-800 border-blue-200' :
                  row.role === 'bendahara' ? 'bg-amber-100 text-amber-800 border-amber-200' :
                  row.role === 'teacher' ? 'bg-teal-100 text-teal-800 border-teal-200' :
                  'bg-slate-100 text-slate-700 border-slate-200',
                  'px-3 py-1 rounded-full font-bold text-[10px] uppercase tracking-wider border inline-flex items-center gap-1.5'
                ]">
                  <template v-if="row.role === 'admin'">
                    <ShieldCheck class="w-3.5 h-3.5 text-purple-600" />
                    <span>Super Admin</span>
                  </template>
                  <template v-else-if="row.role === 'kepala_sekolah'">
                    <ShieldCheck class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Kepala Madrasah</span>
                  </template>
                  <template v-else-if="row.role === 'operator'">
                    <Building class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Operator TU</span>
                  </template>
                  <template v-else-if="row.role === 'kurikulum'">
                    <BookOpen class="w-3.5 h-3.5 text-blue-600" />
                    <span>Waka Kurikulum</span>
                  </template>
                  <template v-else-if="row.role === 'bendahara'">
                    <Wallet class="w-3.5 h-3.5 text-amber-600" />
                    <span>Bendahara</span>
                  </template>
                  <template v-else-if="row.role === 'teacher'">
                    <GraduationCap class="w-3.5 h-3.5 text-teal-600" />
                    <span>Guru</span>
                  </template>
                  <template v-else-if="row.role === 'student'">
                    <User class="w-3.5 h-3.5 text-slate-600" />
                    <span>Siswa</span>
                  </template>
                  <template v-else>
                    <UserCheck class="w-3.5 h-3.5 text-slate-600" />
                    <span>{{ row.role }}</span>
                  </template>
                </span>
              </td>

              <!-- Tautan Profil Guru / Siswa (Dual-Role) -->
              <td class="px-6 py-4">
                <div v-if="row.teacher" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-[11px] font-bold">
                  <GraduationCap class="w-3.5 h-3.5 text-teal-600 flex-shrink-0" />
                  <span>Guru: {{ row.teacher.full_name }}</span>
                </div>
                <div v-else-if="row.student" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-800 text-[11px] font-bold">
                  <User class="w-3.5 h-3.5 text-indigo-600 flex-shrink-0" />
                  <span>Siswa: {{ row.student.full_name }}</span>
                </div>
                <span v-else class="text-slate-400 text-xs italic">- Khusus Staf -</span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-1.5">
                  <button 
                    @click="openEditModal(row)" 
                    title="Edit Pengguna" 
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer"
                  >
                    <Pencil class="w-3.5 h-3.5" />
                  </button>

                  <button 
                    @click="removeUser(row)" 
                    :disabled="row.id === auth.user?.id"
                    :title="row.id === auth.user?.id ? 'Tidak dapat menghapus akun Anda sendiri' : 'Hapus Pengguna'" 
                    class="w-8 h-8 rounded-xl flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-slate-200/80 transition-all shadow-2xs cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!users.length">
              <td colspan="6" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-2 text-slate-400">
                  <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-2">
                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                  </div>
                  <p class="text-sm font-semibold">Tidak ada pengguna jabatan ditemukan.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1 && selectedPerPage !== -1" class="flex items-center justify-between px-2">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Halaman {{ currentPage }} dari {{ totalPages }}</p>
      <nav class="flex items-center gap-1">
        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1 || loading" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 disabled:opacity-30 transition-all cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <template v-for="page in visiblePages" :key="page">
          <button v-if="page !== '...'" @click="goToPage(page)" :class="currentPage === page ? 'bg-[#111827] text-white font-bold border-[#111827]' : 'text-slate-500 hover:bg-white hover:border-slate-200 font-medium'" class="w-9 h-9 flex items-center justify-center rounded-xl text-sm transition-all border border-transparent cursor-pointer">{{ page }}</button>
          <span v-else class="w-9 h-9 flex items-center justify-center text-slate-400 text-sm">...</span>
        </template>
        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages || loading" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-800 hover:bg-white border border-transparent hover:border-slate-200 disabled:opacity-30 transition-all cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      </nav>
    </div>

    <!-- Modal Form Tambah / Edit Pengguna -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-fade-in">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 animate-slide-up flex flex-col">
        <!-- Modal Header -->
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/80">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
              <h3 class="text-base font-black text-slate-800 font-lexend">{{ isEditing ? 'Edit Akun Pengguna' : 'Tambah Akun Jabatan Baru' }}</h3>
              <p class="text-xs text-slate-400 font-medium">{{ isEditing ? 'Perbarui informasi dan hak akses akun' : 'Buat kredensial login staf atau jabatan' }}</p>
            </div>
          </div>
          <button @click="showModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer transition-colors">✕</button>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="saveUser" class="p-6 space-y-4">
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700">Nama Lengkap Pengguna *</label>
            <input 
              v-model="userForm.name" 
              type="text" 
              placeholder="Contoh: Bapak Ahmad Fauzi, S.Pd" 
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
              required 
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700">Username Login *</label>
              <input 
                v-model="userForm.username" 
                type="text" 
                placeholder="Contoh: operator_tu" 
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-mono font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
                required 
              />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-bold text-slate-700">Email (Opsional)</label>
              <input 
                v-model="userForm.email" 
                type="email" 
                placeholder="email@sekolah.sch.id" 
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
              />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700">
              {{ isEditing ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password Login *' }}
            </label>
            <input 
              v-model="userForm.password" 
              type="password" 
              placeholder="Minimal 6 karakter" 
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
              :required="!isEditing" 
            />
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700">Jabatan / Peran Akun *</label>
            <select 
              v-model="userForm.role" 
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
              required
            >
              <option value="admin">Administrator (Superadmin)</option>
              <option value="kepala_sekolah">Kepala Madrasah / Kepala Sekolah</option>
              <option value="operator">Operator / Tata Usaha (TU)</option>
              <option value="kurikulum">Waka Kurikulum</option>
              <option value="bendahara">Bendahara / Keuangan</option>
            </select>
          </div>

          <!-- Tautkan dengan Guru (Dual-Role) -->
          <div class="space-y-1">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-700">Tautkan ke Data Guru (Dual-Role)</label>
              <span class="text-[10px] text-slate-400">Opsional</span>
            </div>
            <select 
              v-model="userForm.teacher_id" 
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400"
            >
              <option :value="null">-- Tidak Terhubung ke Guru (Hanya Staf) --</option>
              <option v-for="t in teacherList" :key="t.id" :value="t.id">
                {{ t.full_name }} (NIP: {{ t.nip || '-' }})
              </option>
            </select>
            <p class="text-[11px] text-slate-400 leading-relaxed">
              Jika akun staf ini juga seorang guru pengajar, tautkan ke nama gurunya agar dapat menggunakan tombol switch peran.
            </p>
          </div>

          <!-- Footer Buttons -->
          <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
            <button 
              type="button" 
              @click="showModal = false" 
              class="px-4.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="saving" 
              class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-all shadow-md active:scale-95 cursor-pointer flex items-center gap-2"
            >
              <span>{{ saving ? 'Menyimpan...' : (isEditing ? 'Perbarui Pengguna' : 'Simpan Pengguna') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import { useAuthStore } from '../stores/auth';
import { 
  Pencil, 
  Trash2, 
  ShieldCheck, 
  Building, 
  BookOpen, 
  Wallet, 
  GraduationCap, 
  User, 
  Globe, 
  UserCheck 
} from 'lucide-vue-next';

const toast = useToast();
const { confirm } = useConfirm();
const auth = useAuthStore();

const loading = ref(true);
const saving = ref(false);
const users = ref([]);
const teacherList = ref([]);
const totalRecords = ref(0);
const totalPages = ref(1);
const currentPage = ref(1);
const selectedPerPage = ref(10);
const searchQuery = ref('');
const selectedRole = ref('all');
const activeScope = ref('staff'); // 'staff' | 'all'

const summary = ref({
  total_staff: 0,
  admin: 0,
  kepala_sekolah: 0,
  operator: 0,
  kurikulum: 0,
  bendahara: 0,
  all_users: 0,
});

const showModal = ref(false);
const isEditing = ref(false);
const editingUserId = ref(null);

const userForm = reactive({
  name: '',
  username: '',
  email: '',
  password: '',
  role: 'operator',
  teacher_id: null,
});

let searchDebounce = null;
function onSearchInput() {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    currentPage.value = 1;
    loadUsers();
  }, 350);
}

function onFilterChange() {
  currentPage.value = 1;
  loadUsers();
}

function setScope(scope) {
  activeScope.value = scope;
  selectedRole.value = 'all';
  currentPage.value = 1;
  loadUsers();
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  loadUsers();
}

const visiblePages = computed(() => {
  const current = currentPage.value;
  const total = totalPages.value;
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  if (current <= 4) return [1, 2, 3, 4, 5, '...', total];
  if (current >= total - 3) return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
  return [1, '...', current - 1, current, current + 1, '...', total];
});

async function loadTeachers() {
  try {
    const res = await api.get('admin/teachers?per_page=999');
    const raw = res.data?.data || res.data || [];
    teacherList.value = Array.isArray(raw) ? raw : raw.data || [];
  } catch (err) {
    console.error('Failed to load teacher list', err);
  }
}

async function loadUsers() {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    params.set('scope', activeScope.value);
    if (selectedPerPage.value !== -1) {
      params.set('page', currentPage.value);
      params.set('per_page', selectedPerPage.value);
    } else {
      params.set('per_page', 9999);
    }

    if (searchQuery.value.trim()) {
      params.set('search', searchQuery.value.trim());
    }

    if (selectedRole.value !== 'all') {
      params.set('role', selectedRole.value);
    }

    const res = await api.get(`admin/users?${params.toString()}`);
    const data = res.data || res;
    users.value = data.data || [];
    totalRecords.value = data.total || users.value.length;
    totalPages.value = data.last_page || 1;
    currentPage.value = data.current_page || 1;

    if (data.summary) {
      summary.value = data.summary;
    }
  } catch (err) {
    console.error('Failed to load users', err);
    toast.error('Gagal memuat daftar pengguna.');
    users.value = [];
  } finally {
    loading.value = false;
  }
}

function openCreateModal() {
  isEditing.value = false;
  editingUserId.value = null;
  userForm.name = '';
  userForm.username = '';
  userForm.email = '';
  userForm.password = '';
  userForm.role = 'operator';
  userForm.teacher_id = null;
  showModal.value = true;
}

function openEditModal(row) {
  isEditing.value = true;
  editingUserId.value = row.id;
  userForm.name = row.name || '';
  userForm.username = row.username || '';
  userForm.email = row.email || '';
  userForm.password = '';
  userForm.role = row.role || 'operator';
  userForm.teacher_id = row.teacher?.id || row.teacher_id || null;
  showModal.value = true;
}

async function saveUser() {
  saving.value = true;
  try {
    const payload = {
      name: userForm.name,
      username: userForm.username,
      email: userForm.email,
      role: userForm.role,
      teacher_id: userForm.teacher_id,
    };

    if (userForm.password) {
      payload.password = userForm.password;
    }

    if (isEditing.value) {
      const res = await api.put(`admin/users/${editingUserId.value}`, payload);
      toast.success(res.message || 'Data pengguna berhasil diperbarui!');
    } else {
      const res = await api.post('admin/users', payload);
      toast.success(res.message || 'Pengguna baru berhasil ditambahkan!');
    }

    showModal.value = false;
    await loadUsers();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal menyimpan data pengguna.');
  } finally {
    saving.value = false;
  }
}

async function removeUser(row) {
  if (row.id === auth.user?.id) {
    toast.error('Anda tidak dapat menghapus akun Anda sendiri.');
    return;
  }

  const ok = await confirm({
    title: 'Hapus Pengguna',
    message: `Apakah Anda yakin ingin menghapus akun "${row.name}" (${row.username})?`,
    type: 'danger',
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal',
  });
  if (!ok) return;

  try {
    const res = await api.delete(`admin/users/${row.id}`);
    toast.success(res.message || 'Pengguna berhasil dihapus.');
    await loadUsers();
  } catch (err) {
    console.error(err);
    toast.error(err.response?.data?.message || 'Gagal menghapus pengguna.');
  }
}

onMounted(() => {
  loadUsers();
  loadTeachers();
});
</script>
