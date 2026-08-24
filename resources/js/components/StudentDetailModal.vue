<template>
  <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6" @click.self="$emit('close')">
    <div class="w-full max-w-3xl max-h-full flex flex-col relative overflow-hidden rounded-[2rem] bg-white shadow-2xl">
      <!-- Header Dark Section -->
      <div class="bg-gradient-to-r from-emerald-800 via-teal-900 to-slate-900 p-8 pb-10 relative z-30 overflow-hidden flex-shrink-0 text-white">
        <!-- Close Button -->
        <button @click="$emit('close')" class="absolute top-6 right-6 w-9 h-9 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors z-50 cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <div class="flex flex-col sm:flex-row items-center gap-6 sm:gap-8 relative z-10">
          <!-- Avatar -->
          <div class="w-32 h-32 sm:w-36 sm:h-36 rounded-3xl bg-slate-200 overflow-hidden border-4 border-white/30 shadow-xl flex-shrink-0 flex items-center justify-center relative backdrop-blur-md">
            <img v-if="student.photo_url" :src="student.photo_url" alt="Student Photo" class="w-full h-full object-cover" />
            <div v-else class="text-5xl font-black text-slate-400">
              {{ (student.full_name || '?').charAt(0) }}
            </div>
          </div>

          <!-- Info -->
          <div class="text-center sm:text-left flex-1">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold tracking-widest uppercase mb-2 border border-emerald-500/30 backdrop-blur-md">
              <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
              Pelajar Aktif
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-white font-lexend uppercase tracking-wide mb-1">{{ student.full_name }}</h2>
            <p class="text-emerald-200 font-semibold tracking-wider text-xs sm:text-sm uppercase">
              Kelas {{ student.classRoom?.name || student.class_name || '-' }}
            </p>

            <div class="flex items-center justify-center sm:justify-start gap-3 mt-4">
              <button @click="$emit('reset-password', student)" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition-all flex items-center gap-2 shadow-lg shadow-emerald-500/30 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                Ganti Password
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Body Section (Scrollable) -->
      <div class="bg-slate-50 p-6 sm:p-8 rounded-t-3xl -mt-6 relative z-20 overflow-y-auto flex-1 custom-scrollbar space-y-6">
        <!-- 1. Identitas Utama Siswa -->
        <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs space-y-4">
          <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Identitas Utama Pelajar
          </h4>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
            <div class="p-3 bg-slate-50 rounded-xl space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NIK Siswa (KTP / KK)</p>
              <p class="font-bold text-slate-800 font-mono text-sm">{{ student.nik || '-' }}</p>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NISN / NIS</p>
              <p class="font-bold text-slate-800 font-mono">{{ student.nisn || '-' }} / {{ student.nis || '-' }}</p>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</p>
              <p class="font-bold text-slate-800">{{ student.gender === 'L' ? 'Laki-laki' : (student.gender === 'P' ? 'Perempuan' : '-') }}</p>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tempat, Tanggal Lahir</p>
              <p class="font-bold text-slate-800">{{ student.birth_place || '-' }}, {{ student.birth_date || '-' }}</p>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sekolah Asal</p>
              <p class="font-semibold text-slate-700">{{ student.previous_school || '-' }}</p>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. Kontak Orang Tua</p>
              <p class="font-bold text-emerald-700 font-mono">{{ student.parent_phone ? '+62 ' + student.parent_phone : '-' }}</p>
            </div>

            <div class="sm:col-span-2 md:col-span-3 p-3 bg-slate-50 rounded-xl space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</p>
              <p class="font-medium text-slate-700 leading-relaxed">{{ student.address || '-' }}</p>
            </div>
          </div>
        </div>

        <!-- 2. Data Orang Tua (Ayah & Ibu) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Ayah -->
          <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
              <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span>👨</span> Data Ayah Kandung
              </h5>
              <span :class="[
                student.father_status === 'meninggal' ? 'bg-rose-100 text-rose-800' :
                student.father_status === 'tidak_diketahui' ? 'bg-slate-200 text-slate-800' :
                'bg-emerald-100 text-emerald-800',
                'px-2 py-0.5 rounded-md text-[10px] font-bold uppercase'
              ]">
                {{ student.father_status || 'Hidup' }}
              </span>
            </div>

            <div class="space-y-2 text-xs">
              <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Nama:</span>
                <span class="font-bold text-slate-800">{{ student.father_name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400 font-medium">NIK Ayah:</span>
                <span class="font-bold text-slate-700 font-mono">{{ student.father_nik || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Pekerjaan:</span>
                <span class="font-semibold text-slate-700">{{ student.father_job || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Penghasilan:</span>
                <span class="font-semibold text-slate-700">{{ student.father_income || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- Ibu -->
          <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
              <h5 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <span>👩</span> Data Ibu Kandung
              </h5>
              <span :class="[
                student.mother_status === 'meninggal' ? 'bg-rose-100 text-rose-800' :
                student.mother_status === 'tidak_diketahui' ? 'bg-slate-200 text-slate-800' :
                'bg-emerald-100 text-emerald-800',
                'px-2 py-0.5 rounded-md text-[10px] font-bold uppercase'
              ]">
                {{ student.mother_status || 'Hidup' }}
              </span>
            </div>

            <div class="space-y-2 text-xs">
              <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Nama:</span>
                <span class="font-bold text-slate-800">{{ student.mother_name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400 font-medium">NIK Ibu:</span>
                <span class="font-bold text-slate-700 font-mono">{{ student.mother_nik || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Pekerjaan:</span>
                <span class="font-semibold text-slate-700">{{ student.mother_job || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400 font-medium">Penghasilan:</span>
                <span class="font-semibold text-slate-700">{{ student.mother_income || '-' }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Data Wali Siswa -->
        <div class="bg-emerald-50/50 border border-emerald-200/80 rounded-2xl p-5 shadow-xs space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-emerald-200/60">
            <h4 class="text-xs font-black text-emerald-900 uppercase tracking-widest flex items-center gap-2">
              <span>🛡️</span> Data Wali Resmi Siswa
            </h4>
            <span class="px-2.5 py-0.5 bg-emerald-600 text-white rounded-full text-[10px] font-bold uppercase tracking-wider">
              {{ student.guardian_relation || 'Wali' }}
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
            <div class="p-3 bg-white rounded-xl border border-emerald-100 space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap Wali</p>
              <p class="font-bold text-slate-800 text-sm">{{ student.guardian_name || '-' }}</p>
            </div>

            <div class="p-3 bg-white rounded-xl border border-emerald-100 space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hubungan dg Siswa</p>
              <p class="font-bold text-emerald-700">{{ student.guardian_relation || '-' }}</p>
            </div>

            <div class="p-3 bg-white rounded-xl border border-emerald-100 space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NIK Wali</p>
              <p class="font-bold text-slate-700 font-mono">{{ student.guardian_nik || '-' }}</p>
            </div>

            <div class="p-3 bg-white rounded-xl border border-emerald-100 space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pekerjaan Wali</p>
              <p class="font-semibold text-slate-700">{{ student.guardian_job || '-' }}</p>
            </div>

            <div class="p-3 bg-white rounded-xl border border-emerald-100 space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. HP / WA Wali</p>
              <p class="font-bold text-slate-800 font-mono">{{ student.guardian_phone || student.parent_phone || '-' }}</p>
            </div>

            <div class="p-3 bg-white rounded-xl border border-emerald-100 space-y-1">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Penghasilan Wali</p>
              <p class="font-semibold text-slate-700">{{ student.guardian_income || '-' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  student: {
    type: Object,
    required: true
  }
});

defineEmits(['close', 'reset-password']);
</script>

<style scoped>
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
</style>
