<template>
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6" @click.self="$emit('close')">
    <div class="w-full max-w-3xl relative">
      <div class="flex flex-col rounded-[2.5rem] overflow-hidden bg-white shadow-2xl border border-slate-100">
        <!-- Header Dark Section -->
        <div class="bg-[#0f172a] p-8 pb-10 relative z-30 overflow-hidden">
          <!-- Close Button -->
          <button @click="$emit('close')" class="absolute top-6 right-6 w-9 h-9 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors z-50 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>

          <div class="flex flex-col sm:flex-row items-center gap-7 relative z-10">
            <!-- Avatar -->
            <div class="w-32 h-32 rounded-3xl overflow-hidden bg-emerald-700 border-4 border-white/20 shadow-2xl flex-shrink-0 flex items-center justify-center relative group">
              <img v-if="teacher.photo_url" :src="teacher.photo_url" alt="Teacher Photo" class="w-full h-full object-cover" />
              <div v-else class="text-5xl font-black text-white">
                {{ (teacher.full_name || '?').charAt(0).toUpperCase() }}
              </div>
            </div>

            <!-- Info -->
            <div class="text-center sm:text-left flex-1 space-y-2">
              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-extrabold tracking-widest uppercase border border-emerald-500/30">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                  Tenaga Pendidik
                </span>
                <span v-if="teacher.position" class="inline-flex items-center px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 text-[10px] font-bold border border-teal-500/30">
                  {{ teacher.position }}
                </span>
              </div>

              <h2 class="text-2xl sm:text-3xl font-black text-white font-lexend tracking-wide">{{ teacher.full_name }}</h2>

              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-slate-300">
                <span class="font-mono bg-white/10 px-2.5 py-1 rounded-lg">NUPTK / NIP: <strong class="text-white">{{ teacher.nip || '-' }}</strong></span>
                <span class="bg-white/10 px-2.5 py-1 rounded-lg">Gender: <strong class="text-white">{{ teacher.gender === 'L' ? 'Laki-laki' : (teacher.gender === 'P' ? 'Perempuan' : '-') }}</strong></span>
              </div>

              <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 pt-3">
                <button
                  v-if="isAdminSuper"
                  @click="$emit('impersonate', teacher)"
                  class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition-all shadow-lg shadow-purple-600/20 flex items-center gap-1.5 cursor-pointer"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                  <span>Login Sebagai Guru</span>
                </button>
                <button @click="$emit('reset-password', teacher)" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition-all flex items-center gap-1.5 shadow-lg shadow-amber-500/20 cursor-pointer">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                  <span>Reset Password</span>
                </button>
                <button @click="$emit('edit', teacher)" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-lg shadow-emerald-600/20 flex items-center gap-1.5 cursor-pointer">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  <span>Edit Data</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Body Section -->
        <div class="bg-slate-50 p-8 rounded-t-3xl -mt-6 relative z-20 max-h-[60vh] overflow-y-auto custom-scrollbar space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Info (2 Cols) -->
            <div class="md:col-span-2 space-y-5">
              <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2 border-b border-slate-100 pb-3">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                  <span>Informasi Kontak & Akun</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- No WhatsApp -->
                  <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">WhatsApp / No. HP</span>
                    <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                      <span class="text-xs font-bold text-slate-800">{{ teacher.phone ? '+62' + teacher.phone : '-' }}</span>
                      <a
                        v-if="teacher.phone"
                        :href="`https://wa.me/62${teacher.phone.replace(/^0+/, '')}`"
                        target="_blank"
                        class="text-[10px] bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1 rounded-lg transition-colors inline-flex items-center gap-1"
                      >
                        Chat WA
                      </a>
                    </div>
                  </div>

                  <!-- Email / Username Akun -->
                  <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Username Login</span>
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-800">
                      {{ teacher.user?.username || teacher.nip || '-' }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Mata Pelajaran & Kelas yang Diajar -->
              <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-2 border-b border-slate-100 pb-3">
                  <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                  <span>Mata Pelajaran & Kelas yang Diampu</span>
                </h3>

                <div v-if="getSubjectClassGrouped(teacher).length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div v-for="(grp, i) in getSubjectClassGrouped(teacher)" :key="grp.subject_name" class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 flex flex-col justify-between space-y-2">
                    <div class="flex items-center gap-2.5">
                      <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white font-black text-xs flex-shrink-0"
                           :class="subjectColors[i % subjectColors.length]">
                        {{ grp.subject_name.charAt(0) }}
                      </div>
                      <span class="text-xs font-bold text-slate-800">{{ grp.subject_name }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-200/50 flex items-center gap-1.5">
                      <span class="text-[9px] font-bold text-slate-400 uppercase">Kelas:</span>
                      <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200/60">{{ grp.class_names }}</span>
                    </div>
                  </div>
                </div>
                <div v-else class="bg-slate-50 border border-dashed border-slate-200 rounded-2xl p-6 text-center text-slate-400">
                  <p class="text-xs font-semibold">Belum ada mata pelajaran & kelas yang diampu.</p>
                </div>
              </div>
            </div>

            <!-- Right QR Card (1 Col) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-between text-center space-y-4">
              <div class="space-y-1">
                <span class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-widest">QR Code Presensi</span>
                <p class="text-xs text-slate-500 font-medium">Scan untuk presensi guru</p>
              </div>

              <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl shadow-inner">
                <img v-if="qrDataUrl" :src="qrDataUrl" alt="QR Code Kartu Guru" class="w-36 h-36 object-contain" />
                <div v-else class="w-36 h-36 flex items-center justify-center text-slate-400 text-xs font-semibold">
                  Membuat QR...
                </div>
              </div>

              <div class="w-full space-y-2">
                <p class="text-[10px] font-mono text-slate-400 truncate">{{ teacher.nip || teacher.id }}</p>
                <button
                  v-if="qrDataUrl"
                  @click="downloadQrCode"
                  class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                  <span>Unduh QR Code</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-4 bg-white border-t border-slate-100 flex justify-end">
          <button @click="$emit('close')" class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors cursor-pointer">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import QRCode from 'qrcode';

const props = defineProps({
  teacher: {
    type: Object,
    required: true
  }
});

defineEmits(['close', 'reset-password', 'edit', 'impersonate']);

const auth = useAuthStore();
const isAdminSuper = computed(() => {
  return auth.primaryRole === 'admin' || auth.user?.role === 'admin';
});

const qrDataUrl = ref('');

const generateQr = async () => {
  if (!props.teacher) return;
  try {
    const payload = props.teacher.qr_card_payload || `TEACHER-ID|${props.teacher.id}|${props.teacher.nip || 'NONIP'}`;
    qrDataUrl.value = await QRCode.toDataURL(payload, {
      width: 250,
      margin: 1.5,
      color: {
        dark: '#0f172a',
        light: '#ffffff'
      }
    });
  } catch (err) {
    console.error('Failed to generate QR Code:', err);
  }
};

watch(() => props.teacher, generateQr, { immediate: true });
onMounted(generateQr);

const downloadQrCode = () => {
  if (!qrDataUrl.value) return;
  const link = document.createElement('a');
  link.href = qrDataUrl.value;
  link.download = `QR_Guru_${props.teacher.nip || props.teacher.id}_${props.teacher.full_name}.png`;
  link.click();
};

function getSubjectClassGrouped(teacher) {
  if (!teacher) return [];
  const assignments = teacher.subject_classes || teacher.subjectClasses;
  if (assignments && assignments.length > 0) {
    const grouped = {};
    assignments.forEach(sc => {
      const subName = sc.subject?.name || 'Mapel';
      const clsName = sc.class_room?.name || sc.classRoom?.name || '';
      if (!grouped[subName]) grouped[subName] = [];
      if (clsName && !grouped[subName].includes(clsName)) {
        grouped[subName].push(clsName);
      }
    });
    return Object.keys(grouped).map(subName => ({
      subject_name: subName,
      class_names: grouped[subName].length > 0 ? grouped[subName].join(', ') : 'Semua Kelas'
    }));
  }

  if (teacher.subjects && teacher.subjects.length > 0) {
    const clsNames = (teacher.teaching_classes || teacher.teachingClasses || []).map(c => c.name).join(', ') || 'Semua Kelas';
    return teacher.subjects.map(s => ({
      subject_name: s.name,
      class_names: clsNames
    }));
  }

  return [];
}

const subjectColors = [
  'bg-purple-600',
  'bg-blue-600',
  'bg-emerald-600',
  'bg-amber-600',
  'bg-pink-600',
  'bg-teal-600',
  'bg-indigo-600',
  'bg-rose-600',
];
</script>

<style scoped>
.font-lexend { font-family: 'Lexend', system-ui, sans-serif; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>
