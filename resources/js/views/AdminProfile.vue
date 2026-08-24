<template>
  <div class="space-y-8 pb-12 font-inter max-w-4xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
      <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
        <!-- Avatar Frame -->
        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white/15 backdrop-blur-md border-2 border-white/30 overflow-hidden shadow-2xl flex items-center justify-center text-4xl font-black text-white">
          {{ (form.name || 'A').charAt(0).toUpperCase() }}
        </div>

        <!-- Name & Badges -->
        <div class="text-center sm:text-left flex-1 space-y-2">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md">
            <ShieldAlert class="w-3.5 h-3.5" />
            <span>Administrator Utama Sistem</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black font-lexend text-white tracking-wide">
            {{ form.name || 'Admin' }}
          </h1>
          <p class="text-emerald-100 text-xs sm:text-sm font-medium">
            Username: <b class="text-white font-mono">{{ form.username }}</b> | Bergabung sejak: {{ adminData?.created_at || '-' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
          <User class="w-4 h-4 text-emerald-600" />
          <span>Informasi Akun Administrator</span>
        </h3>
      </div>

      <form @submit.prevent="saveProfile" class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <!-- Nama Lengkap -->
          <div class="sm:col-span-2 space-y-1.5">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
              Nama Lengkap Administrator <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
              placeholder="Nama administrator"
            />
          </div>

          <!-- Username -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
              Username Login <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.username"
              type="text"
              required
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
              placeholder="Username login"
            />
          </div>

          <!-- Email -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
              Email Administrator <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
              placeholder="admin@sekolah.sch.id"
            />
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
          <button
            type="submit"
            :disabled="saving"
            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 disabled:opacity-50 cursor-pointer"
          >
            <Save v-if="!saving" class="w-4 h-4" />
            <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span>{{ saving ? 'Menyimpan...' : 'Simpan Profil Admin' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import { User, ShieldAlert, Save } from 'lucide-vue-next';

const toast = useToast();
const saving = ref(false);
const adminData = ref(null);

const form = reactive({
  name: '',
  username: '',
  email: '',
});

async function loadProfile() {
  try {
    const res = await api.get('admin/profile');
    const user = res?.data?.user || res?.user || {};
    adminData.value = user;
    form.name = user.name || '';
    form.username = user.username || '';
    form.email = user.email || '';
  } catch (err) {
    console.error('Error loading admin profile:', err);
  }
}

async function saveProfile() {
  if (!form.name?.trim() || !form.username?.trim() || !form.email?.trim()) {
    toast.error('Semua kolom profil admin wajib diisi!');
    return;
  }

  saving.value = true;
  try {
    const res = await api.post('admin/profile', form);
    toast.success(res?.message || 'Profil admin berhasil diperbarui!');
    await loadProfile();
  } catch (err) {
    console.error('Error saving admin profile:', err);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadProfile();
});
</script>
