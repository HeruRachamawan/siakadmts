<template>
  <div class="min-h-screen flex font-inter bg-slate-100/80 items-center justify-center p-4 sm:p-6 relative overflow-hidden">
    
    <!-- Animated Subtle Background Grid & Ambient Floating Glowing Orbs -->
    <div class="absolute inset-0 z-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-indigo-400/20 blur-[100px] animate-float-slow"></div>
      <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-emerald-400/20 blur-[100px] animate-float-reverse"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-slate-300/30 blur-[140px] pointer-events-none"></div>
      <div class="absolute inset-0 bg-[radial-gradient(#CBD5E1_1px,transparent_1px)] [background-size:24px_24px] opacity-40"></div>
    </div>
    
    <!-- Main Modal Card (Shadcn Spotlight Double-Panel) -->
    <div 
      ref="cardRef"
      @mousemove="handleMouseMove"
      :class="[isShaking ? 'animate-shake' : '']"
      class="spotlight-card relative z-10 w-full max-w-4xl flex flex-col md:flex-row bg-white rounded-2xl border border-slate-200/90 shadow-2xl overflow-hidden transition-all duration-300"
    >
      <!-- Spotlight Dynamic Overlay -->
      <div 
        class="pointer-events-none absolute -inset-px rounded-2xl opacity-0 transition-opacity duration-300 group-hover:opacity-100"
        :style="{
          background: `radial-gradient(600px circle at ${mousePos.x}px ${mousePos.y}px, rgba(99, 102, 241, 0.08), transparent 40%)`
        }"
      ></div>
      
      <!-- Left Panel: Slate-900 Executive Theme (High Contrast) -->
      <div class="w-full md:w-1/2 bg-slate-900 text-white p-8 sm:p-12 flex flex-col justify-between relative overflow-hidden border-b md:border-b-0 md:border-r border-slate-800">
        <!-- Subtle Pattern & Glowing Accent -->
        <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:20px_20px] opacity-40 pointer-events-none"></div>
        <div class="absolute -right-16 -bottom-16 w-48 h-48 bg-emerald-500/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
          <!-- Logo Frame with Interactive Floating Badge -->
          <div class="inline-block">
            <div v-if="appSettings?.app_logo && typeof appSettings.app_logo === 'string' && appSettings.app_logo.length > 5" class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-800 rounded-xl border border-slate-700 p-2 flex items-center justify-center shadow-inner hover:scale-105 transition-transform duration-300 cursor-pointer">
              <img :src="getImageUrl(appSettings.app_logo)" class="w-full h-full object-contain" alt="Logo" />
            </div>
            <div v-else class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-800 rounded-xl border border-slate-700 p-2 flex items-center justify-center text-white shadow-inner hover:scale-105 transition-transform duration-300 cursor-pointer">
              <BookOpen class="w-8 h-8 text-emerald-400" />
            </div>
          </div>

          <!-- Headline -->
          <div class="space-y-1.5">
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 bg-slate-800 text-slate-300 rounded-md text-[11px] font-medium border border-slate-700">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Portal Terintegrasi</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold uppercase tracking-tight text-white leading-tight">
              <span>{{ appNameWords[0] || 'PORTAL' }}</span> <br />
              <span class="text-emerald-400">{{ appNameWords.slice(1).join(' ') || 'DIGITAL' }}</span>
            </h1>
          </div>

          <!-- Description -->
          <p class="text-slate-400 text-xs sm:text-sm leading-relaxed font-normal max-w-sm">
            Silakan masuk untuk mengakses sistem akademik, presensi GPS guru, nilai siswa, dan manajemen data terpadu.
          </p>

          <!-- Back to Public Home Button -->
          <div class="pt-2">
            <RouterLink 
              to="/" 
              class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700/80 text-white text-xs font-semibold rounded-lg border border-slate-700 transition-all hover:-translate-x-1 cursor-pointer shadow-xs"
            >
              <ArrowLeft class="w-3.5 h-3.5" />
              <span>Kembali ke Beranda</span>
            </RouterLink>
          </div>
        </div>
        
        <!-- Multi-Role Indicators at Bottom -->
        <div class="relative z-10 mt-10 pt-6 border-t border-slate-800 flex items-center gap-3">
          <div class="flex -space-x-2">
            <div class="w-7 h-7 rounded-full bg-slate-800 border-2 border-slate-900 flex items-center justify-center text-emerald-400 text-[10px] font-bold shadow-xs">
              <ShieldCheck class="w-3.5 h-3.5" />
            </div>
            <div class="w-7 h-7 rounded-full bg-slate-800 border-2 border-slate-900 flex items-center justify-center text-indigo-400 text-[10px] font-bold shadow-xs">
              <UserCheck class="w-3.5 h-3.5" />
            </div>
            <div class="w-7 h-7 rounded-full bg-slate-800 border-2 border-slate-900 flex items-center justify-center text-purple-400 text-[10px] font-bold shadow-xs">
              <GraduationCap class="w-3.5 h-3.5" />
            </div>
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-300 tracking-wider uppercase">Multi-Role Access</p>
            <p class="text-[10px] text-slate-500 font-normal">Admin &bull; Guru &bull; Siswa</p>
          </div>
        </div>
      </div>

      <!-- Right Panel: Crisp White Shadcn Login Form with Interactive Tabs -->
      <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center bg-white relative">
        <div class="w-full max-w-sm mx-auto space-y-6">
          
          <!-- Header Title & Subtitle -->
          <div class="space-y-1">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang</h2>
            <p class="text-xs text-slate-500 font-normal">Pilih peran Anda dan masukkan akun untuk melanjutkan.</p>
          </div>

          <!-- Interactive Role Selector Tabs -->
          <div class="p-1 bg-slate-100 rounded-lg flex items-center gap-1 border border-slate-200/80">
            <button
              type="button"
              @click="setDemoRole('admin')"
              :class="[selectedRoleTab === 'admin' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-800 font-medium']"
              class="flex-1 py-1.5 text-xs rounded-md transition-all flex items-center justify-center gap-1.5 cursor-pointer"
            >
              <ShieldCheck class="w-3.5 h-3.5" />
              <span>Admin</span>
            </button>
            <button
              type="button"
              @click="setDemoRole('teacher')"
              :class="[selectedRoleTab === 'teacher' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-800 font-medium']"
              class="flex-1 py-1.5 text-xs rounded-md transition-all flex items-center justify-center gap-1.5 cursor-pointer"
            >
              <UserCheck class="w-3.5 h-3.5" />
              <span>Guru</span>
            </button>
            <button
              type="button"
              @click="setDemoRole('student')"
              :class="[selectedRoleTab === 'student' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-800 font-medium']"
              class="flex-1 py-1.5 text-xs rounded-md transition-all flex items-center justify-center gap-1.5 cursor-pointer"
            >
              <GraduationCap class="w-3.5 h-3.5" />
              <span>Siswa</span>
            </button>
          </div>

          <form @submit.prevent="onSubmit" class="space-y-4">
            <!-- Username Input -->
            <div class="space-y-1.5">
              <label class="block text-xs font-semibold text-slate-700">
                {{ selectedRoleTab === 'student' ? 'NISN Siswa' : (selectedRoleTab === 'teacher' ? 'NIP / Username Guru' : 'Username Administrator') }}
              </label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                  <User class="w-4 h-4" />
                </div>
                <input
                  v-model="form.username"
                  type="text"
                  :placeholder="selectedRoleTab === 'student' ? 'Ketik NISN Anda...' : 'Ketik username akun Anda...'"
                  class="form-input !pl-9 transition-all focus:border-slate-900"
                  required
                />
              </div>
            </div>

            <!-- Password Input -->
            <div class="space-y-1.5">
              <label class="block text-xs font-semibold text-slate-700">Password</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-slate-900 transition-colors">
                  <Lock class="w-4 h-4" />
                </div>
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  class="form-input !pl-9 !pr-10 transition-all focus:border-slate-900"
                  required
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-700 transition-transform active:scale-90 outline-none cursor-pointer"
                >
                  <Eye v-if="!showPassword" class="w-4 h-4" />
                  <EyeOff v-else class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Forgot Password Link -->
            <div class="flex items-center justify-end pt-1">
              <button
                type="button"
                @click="showResetModal = true"
                class="text-xs font-medium text-slate-600 hover:text-slate-900 hover:underline transition-colors flex items-center gap-1.5 cursor-pointer"
              >
                <KeyRound class="w-3.5 h-3.5 text-slate-500" />
                <span>Lupa Password? Minta Reset ke Admin</span>
              </button>
            </div>

            <!-- Error Banner -->
            <div v-if="error" class="flex items-center gap-2 p-3 text-xs font-medium text-rose-700 bg-rose-50 border border-rose-200 rounded-lg animate-shake">
              <AlertCircle class="w-4 h-4 flex-shrink-0 text-rose-600" />
              <span>{{ error }}</span>
            </div>

            <!-- Submit Button (Shadcn Primary Emerald with Arrow Hover Animation) -->
            <button
              type="submit"
              :disabled="loading"
              class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-md shadow-emerald-600/20 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 group active:scale-[0.98]"
            >
              <div v-if="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-1"></div>
              <span>{{ loading ? 'Memproses Masuk...' : 'Masuk Sekarang' }}</span>
              <ArrowRight v-if="!loading" class="w-4 h-4 group-hover:translate-x-1.5 transition-transform duration-200" />
            </button>
          </form>

          <!-- Footer Copyright -->
          <div class="pt-6 border-t border-slate-100 text-center">
            <p class="text-[11px] text-slate-400">
              &copy; {{ new Date().getFullYear() }} {{ appSettings?.app_name || 'MTS AL-HASANAH' }} &bull; Portal Digital
            </p>
          </div>

        </div>
      </div>
    </div>

    <!-- Modal Permohonan Reset Password (Shadcn Dialog Style) -->
    <Transition name="modal-fade">
      <div v-if="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showResetModal = false"></div>
        <div class="relative bg-white rounded-xl p-6 shadow-2xl w-full max-w-md space-y-4 border border-slate-200 animate-slide-up z-10">
          
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center font-bold">
                <KeyRound class="w-4 h-4" />
              </div>
              <h3 class="font-bold text-slate-900 text-sm tracking-tight">Permohonan Reset Password</h3>
            </div>
            <button @click="showResetModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer">&times;</button>
          </div>

          <p class="text-xs text-slate-500 leading-relaxed font-normal">
            Kirimkan NISN (Siswa) atau NIP/Username (Guru) Anda. Admin sekolah akan memeriksa dan menyetujui permohonan Anda.
          </p>

          <form @submit.prevent="submitResetRequest" class="space-y-3.5">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">NISN / NIP / Username Anda</label>
              <input
                v-model="resetForm.identity"
                type="text"
                placeholder="Masukkan Username, NISN, atau NIP..."
                class="form-input text-xs"
                required
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan / Catatan (Opsional)</label>
              <textarea
                v-model="resetForm.reason"
                placeholder="Contoh: Lupa password akun saya..."
                class="form-input text-xs h-20 resize-none"
              ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
              <button type="button" @click="showResetModal = false" class="btn btn-secondary text-xs">Batal</button>
              <button type="submit" :disabled="submittingReset" class="btn btn-primary text-xs">
                <span v-if="submittingReset">Mengirim...</span>
                <span v-else>Kirim ke Admin</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  BookOpen,
  ArrowLeft,
  ArrowRight,
  User,
  Lock,
  Eye,
  EyeOff,
  KeyRound,
  AlertCircle,
  ShieldCheck,
  UserCheck,
  GraduationCap
} from 'lucide-vue-next';

const toast = useToast();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);
const error = ref('');
const showPassword = ref(false);
const isShaking = ref(false);

const cardRef = ref(null);
const mousePos = reactive({ x: 0, y: 0 });

function handleMouseMove(e) {
  if (!cardRef.value) return;
  const rect = cardRef.value.getBoundingClientRect();
  mousePos.x = e.clientX - rect.left;
  mousePos.y = e.clientY - rect.top;
}

const selectedRoleTab = ref('admin');

function setDemoRole(role) {
  selectedRoleTab.value = role;
  form.username = '';
  form.password = '';
  error.value = '';
}

const showResetModal = ref(false);
const submittingReset = ref(false);
const resetForm = reactive({
  identity: '',
  reason: '',
});

async function submitResetRequest() {
  if (!resetForm.identity) return;
  submittingReset.value = true;
  try {
    const res = await api.post('/password-reset-request', resetForm);
    toast.success(res.data?.message || 'Permintaan reset password telah dikirim ke Admin!');
    showResetModal.value = false;
    resetForm.identity = '';
    resetForm.reason = '';
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengirim permohonan reset password.');
  } finally {
    submittingReset.value = false;
  }
}

const appSettings = ref({});

const getImageUrl = (path) => {
  if (!path) return '';
  if (typeof path !== 'string') return '';
  if (path.startsWith('http') || path.startsWith('data:')) return path;
  if (path.startsWith('/storage/')) return path;
  if (path.startsWith('storage/')) return '/' + path;
  return '/storage/' + path.replace(/^\//, '');
};

const appNameWords = computed(() => {
  if (!appSettings.value?.app_name) return [];
  return appSettings.value.app_name.split(' ');
});

onMounted(async () => {
  try {
    const res = await api.get('public');
    appSettings.value = res?.data?.settings || res?.settings || {};
  } catch (err) {
    console.error('Failed to load public settings', err);
    appSettings.value = {};
  }
});

const form = reactive({
  username: '',
  password: '',
});

async function onSubmit() {
  loading.value = true;
  error.value = '';
  isShaking.value = false;

  try {
    const res = await authStore.login(form.username, form.password);
    
    const role = res.data?.user?.role || res.data?.role;
    let redirect = '/admin/dashboard';
    
    if (role === 'admin') redirect = '/admin/dashboard';
    else if (role === 'teacher') redirect = '/teacher/dashboard';
    else if (role === 'student') redirect = '/student/dashboard';

    let target = redirect;
    if (route.query.redirect && route.query.redirect !== '/login' && route.query.redirect !== '/') {
      target = route.query.redirect;
    }

    toast.success('Login berhasil! Mengalihkan ke dashboard...');
    await router.replace(target);
  } catch (e) {
    isShaking.value = true;
    setTimeout(() => { isShaking.value = false; }, 600);
    error.value = e.response?.data?.message || 'Username atau Password yang Anda masukkan salah!';
    toast.error(error.value);
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
@keyframes floatSlow {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-20px) scale(1.05); }
}

@keyframes floatReverse {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(20px) scale(0.95); }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-6px); }
  40%, 80% { transform: translateX(6px); }
}

.animate-float-slow {
  animation: floatSlow 8s ease-in-out infinite;
}

.animate-float-reverse {
  animation: floatReverse 10s ease-in-out infinite;
}

.animate-shake {
  animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}
</style>
