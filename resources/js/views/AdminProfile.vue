<template>
  <div class="space-y-8 pb-12 font-inter max-w-5xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
      
      <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
        <!-- Photo Frame with Upload Trigger -->
        <div class="relative group">
          <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white/15 backdrop-blur-md border-2 border-white/30 overflow-hidden shadow-2xl flex items-center justify-center">
            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="Foto Profil" />
            <div v-else class="text-4xl font-black text-white/90">
              {{ (form.name || user?.name || 'S').charAt(0).toUpperCase() }}
            </div>
          </div>
          <button
            type="button"
            @click="$refs.photoInput.click()"
            class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-400 text-white shadow-lg flex items-center justify-center transition-all cursor-pointer border-2 border-white"
            title="Ganti Foto Profil"
          >
            <Camera class="w-4 h-4" />
          </button>
          <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
        </div>

        <!-- Name & Badges -->
        <div class="text-center sm:text-left flex-1 space-y-2">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 text-white rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-md">
            <component :is="roleIcon" class="w-3.5 h-3.5" />
            <span>{{ roleLabel }}</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black font-lexend text-white tracking-wide">
            {{ form.name || 'Nama Staf' }}
          </h1>
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs font-medium text-emerald-100">
            <span v-if="form.nip" class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">NIP: {{ form.nip }}</span>
            <span v-if="form.position" class="bg-teal-800/60 px-2.5 py-0.5 rounded-lg border border-teal-500/30">{{ form.position }}</span>
            <span class="bg-emerald-800/40 px-2.5 py-0.5 rounded-lg border border-emerald-500/20">Username: <b class="font-mono">{{ form.username }}</b></span>
          </div>
        </div>

        <div v-if="photoPreview && photoFile">
          <button
            type="button"
            @click="clearPhoto"
            class="px-3 py-2 bg-rose-500/80 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer backdrop-blur-sm shadow-sm"
          >
            Batal Ganti Foto
          </button>
        </div>
      </div>
    </div>

    <!-- Edit Form Container -->
    <div v-if="loading" class="bg-white rounded-3xl p-12 border border-slate-100 shadow-sm text-center">
      <div class="inline-flex w-10 h-10 border-4 border-slate-100 border-t-emerald-500 rounded-full animate-spin mb-4"></div>
      <p class="text-xs font-bold text-slate-400">Memuat biodata profil...</p>
    </div>

    <form v-else @submit.prevent="saveProfile" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Col 1 & 2: Biodata Diri & Akun -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Biodata Pegawai Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
              <User class="w-4 h-4 text-emerald-600" />
              <span>Biodata Diri & Kepegawaian</span>
            </h3>
            <span class="text-[10px] text-slate-400 font-medium">Lengkapi identitas resmi</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
            <!-- Nama Lengkap -->
            <div class="sm:col-span-2 space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Nama Lengkap & Gelar <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                placeholder="Contoh: Drs. H. Ahmad Dahlan, M.Pd"
              />
            </div>

            <!-- NIP / NUPTK -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                NIP / NUPTK / No. Pegawai
              </label>
              <input
                v-model="form.nip"
                type="text"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                placeholder="198001012005011001"
              />
            </div>

            <!-- Jenis Kelamin -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Jenis Kelamin
              </label>
              <select
                v-model="form.gender"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
              >
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L">Laki-laki (L)</option>
                <option value="P">Perempuan (P)</option>
              </select>
            </div>

            <!-- No. WhatsApp / Telp -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Nomor WhatsApp / HP
              </label>
              <input
                v-model="form.phone"
                type="tel"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                placeholder="08123456789"
              />
            </div>

            <!-- Jabatan / Posisi -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Jabatan / Posisi
              </label>
              <input
                v-model="form.position"
                type="text"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                placeholder="Contoh: Waka Kurikulum / Staf Tata Usaha"
              />
            </div>
          </div>
        </div>

        <!-- Akun & Keamanan Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
              <Key class="w-4 h-4 text-amber-500" />
              <span>Akses Login & Keamanan Akun</span>
            </h3>
            <span class="text-[10px] text-slate-400 font-medium">Kredensial sistem</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
            <!-- Username -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Username Login <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.username"
                type="text"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                placeholder="Username login"
              />
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Alamat Email <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                placeholder="email@sekolah.sch.id"
              />
            </div>

            <!-- Password Baru (Opsional) -->
            <div class="sm:col-span-2 space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                Password Baru (Kosongkan jika tidak ingin diubah)
              </label>
              <input
                v-model="form.password"
                type="password"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                placeholder="Minimal 6 karakter"
              />
              <p class="text-[11px] text-slate-400">Hanya isi kolom ini jika Anda ingin mengubah kata sandi login Anda.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Col 3: Side Information & Save Button -->
      <div class="space-y-6">
        <!-- Info Penugasan Mengajar (Jika Ada) -->
        <div v-if="teacherData?.subjects && teacherData.subjects.length > 0" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
            <BookOpen class="w-4 h-4 text-emerald-600" />
            <span>Mata Pelajaran Diampu</span>
          </h3>

          <div class="flex flex-wrap gap-2">
            <span
              v-for="sub in teacherData.subjects"
              :key="sub.id"
              class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold"
            >
              {{ sub.name }}
            </span>
          </div>
        </div>

        <!-- Info Wali Kelas (Jika Ada) -->
        <div v-if="teacherData?.classes && teacherData.classes.length > 0" class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
          <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
            <Building2 class="w-4 h-4 text-emerald-600" />
            <span>Tugas Wali Kelas</span>
          </h3>

          <div class="space-y-2">
            <div
              v-for="cls in teacherData.classes"
              :key="cls.id"
              class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold text-slate-800 flex justify-between items-center"
            >
              <span>Kelas {{ cls.name }}</span>
              <span class="text-[10px] text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md font-bold">Wali Kelas</span>
            </div>
          </div>
        </div>

        <!-- Save Button Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-3">
          <button
            type="submit"
            :disabled="saving"
            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-bold uppercase tracking-wider transition-all shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <Save v-if="!saving" class="w-4 h-4" />
            <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span>{{ saving ? 'Menyimpan...' : 'Simpan Biodata Profil' }}</span>
          </button>
          <p class="text-[11px] text-slate-400 text-center leading-relaxed">
            Pastikan data yang Anda masukkan telah sesuai sebelum menekan tombol simpan.
          </p>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { api, getImageUrl } from '../api';
import { useToast } from '../composables/useToast';
import { useAuthStore } from '../stores/auth';
import {
  User,
  ShieldAlert,
  ShieldCheck,
  Building,
  Building2,
  BookOpen,
  Wallet,
  Camera,
  Key,
  Save,
} from 'lucide-vue-next';

const toast = useToast();
const auth = useAuthStore();
const loading = ref(true);
const saving = ref(false);

const user = ref(null);
const teacherData = ref(null);

const form = reactive({
  name: '',
  username: '',
  email: '',
  password: '',
  nip: '',
  gender: '',
  phone: '',
  position: '',
});

const photoFile = ref(null);
const photoPreview = ref(null);

const roleLabel = computed(() => {
  const r = auth.role;
  if (r === 'admin') return 'Super Administrator';
  if (r === 'kurikulum') return 'Waka Kurikulum';
  if (r === 'operator') return 'Operator / Tata Usaha';
  if (r === 'kepala_sekolah') return 'Kepala Madrasah';
  if (r === 'bendahara') return 'Bendahara Keuangan';
  if (r === 'teacher') return 'Dewan Guru Pengajar';
  return 'Staf Madrasah';
});

const roleIcon = computed(() => {
  const r = auth.role;
  if (r === 'admin') return ShieldCheck;
  if (r === 'kurikulum') return BookOpen;
  if (r === 'operator') return Building;
  if (r === 'kepala_sekolah') return ShieldCheck;
  if (r === 'bendahara') return Wallet;
  return User;
});

function onPhotoChange(e) {
  const file = e.target.files[0];
  if (!file) return;

  if (file.size > 10 * 1024 * 1024) {
    toast.error('Ukuran foto maksimal 10MB!');
    return;
  }

  photoFile.value = file;
  photoPreview.value = URL.createObjectURL(file);
}

function clearPhoto() {
  photoFile.value = null;
  photoPreview.value = teacherData.value?.photo_url || (teacherData.value?.photo ? getImageUrl(teacherData.value.photo) : null);
  if (document.querySelector('input[type="file"]')) {
    document.querySelector('input[type="file"]').value = '';
  }
}

async function loadProfile() {
  loading.value = true;
  try {
    const res = await api.get('admin/profile');
    const u = res?.data?.user || res?.user || {};
    const t = res?.data?.teacher || res?.teacher || {};

    user.value = u;
    teacherData.value = t;

    form.name = u.name || t.full_name || '';
    form.username = u.username || '';
    form.email = u.email || '';
    form.nip = t.nip || '';
    form.gender = t.gender || '';
    form.phone = t.phone || '';
    form.position = t.position || (u.role === 'kurikulum' ? 'Waka Kurikulum' : (u.role === 'operator' ? 'Operator TU' : ''));
    form.password = '';

    photoPreview.value = t.photo_url || (t.photo ? getImageUrl(t.photo) : null);
    photoFile.value = null;
  } catch (err) {
    console.error('Error loading admin/staff profile:', err);
    toast.error('Gagal memuat profil staf.');
  } finally {
    loading.value = false;
  }
}

async function saveProfile() {
  if (!form.name?.trim() || !form.username?.trim() || !form.email?.trim()) {
    toast.error('Nama, username, dan email wajib diisi!');
    return;
  }

  saving.value = true;
  try {
    const formData = new FormData();
    Object.keys(form).forEach((key) => {
      if (form[key] !== null && form[key] !== undefined && form[key] !== '') {
        formData.append(key, form[key]);
      }
    });

    if (photoFile.value) {
      formData.append('photo', photoFile.value);
    }

    const res = await api.postForm('admin/profile', formData);
    toast.success(res?.message || 'Profil berhasil diperbarui!');

    // Update user in auth store so header updates name immediately
    if (res?.data?.user) {
      auth.user = { ...auth.user, ...res.data.user };
    }

    await loadProfile();
  } catch (err) {
    console.error('Error saving staff profile:', err);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadProfile();
});
</script>
