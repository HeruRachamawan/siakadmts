<template>
  <div class="space-y-8 pb-12 font-inter max-w-5xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
      <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
        <!-- Photo Frame with Upload Trigger -->
        <div class="relative group">
          <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white/15 backdrop-blur-md border-2 border-white/30 overflow-hidden shadow-2xl flex items-center justify-center">
            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="Foto Siswa" />
            <div v-else class="text-4xl font-black text-white/80">
              {{ (form.full_name || user?.name || 'S').charAt(0).toUpperCase() }}
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
            <GraduationCap class="w-3.5 h-3.5" />
            <span>Profil Pelajar Siswa</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black font-lexend text-white tracking-wide">
            {{ form.full_name || 'Nama Siswa' }}
          </h1>
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs font-medium text-emerald-100">
            <span class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">Kelas: {{ studentData?.class_name || studentData?.class_room?.name || studentData?.classRoom?.name || '-' }}</span>
            <span class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">NISN: {{ studentData?.nisn || '-' }}</span>
            <span class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">NIS: {{ studentData?.nis || '-' }}</span>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <button
            v-if="photoPreview && photoFile"
            type="button"
            @click="clearPhoto"
            class="px-3 py-1.5 bg-rose-500/80 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer backdrop-blur-sm shadow-sm"
          >
            Batal Ganti Foto
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-3xl border border-slate-100 shadow-sm">
      <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-xs font-bold text-slate-500 mt-3">Memuat Biodata Profil Siswa...</p>
    </div>

    <!-- Edit Form -->
    <form v-else @submit.prevent="saveProfile" class="space-y-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1 & 2: Biodata Pribadi, Orang Tua & Wali -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Card: Biodata Pribadi Siswa -->
          <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                <User class="w-4 h-4 text-emerald-600" />
                <span>Biodata Pribadi Pelajar</span>
              </h3>
              <span class="text-[11px] text-slate-400 font-semibold">* Wajib diisi</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Nama Lengkap -->
              <div class="sm:col-span-2 space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                  Nama Lengkap Siswa <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.full_name"
                  type="text"
                  required
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="Nama lengkap siswa"
                />
              </div>

              <!-- NIK Siswa -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                  NIK Siswa (KTP / KK) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.nik"
                  type="text"
                  inputmode="numeric"
                  maxlength="16"
                  required
                  @input="form.nik = form.nik.replace(/[^0-9]/g, '')"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="16 digit NIK"
                />
              </div>

              <!-- Jenis Kelamin -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                  Jenis Kelamin <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.gender"
                  required
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                >
                  <option value="">Pilih Jenis Kelamin...</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </div>

              <!-- Tempat Lahir -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Tempat Lahir</label>
                <input
                  v-model="form.birth_place"
                  type="text"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="Kota / Kabupaten Lahir"
                />
              </div>

              <!-- Tanggal Lahir -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Tanggal Lahir</label>
                <input
                  v-model="form.birth_date"
                  type="date"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                />
              </div>

              <!-- No. HP / WhatsApp Orang Tua -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">No. HP / WA Orang Tua</label>
                <input
                  v-model="form.parent_phone"
                  type="text"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="Contoh: 081234567890"
                />
              </div>

              <!-- Sekolah Asal -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Sekolah Asal</label>
                <input
                  v-model="form.previous_school"
                  type="text"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="SMPN 1..."
                />
              </div>

              <!-- Alamat Lengkap -->
              <div class="sm:col-span-2 space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Alamat Lengkap Tempat Tinggal</label>
                <textarea
                  v-model="form.address"
                  rows="3"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all resize-none"
                  placeholder="Alamat domisili lengkap siswa"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Card: Orang Tua & Wali -->
          <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                <Users class="w-4 h-4 text-emerald-600" />
                <span>Informasi Orang Tua & Wali</span>
              </h3>
            </div>

            <!-- Ayah & Ibu -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-5 rounded-2xl border border-slate-100">
              <!-- Ayah -->
              <div class="space-y-3">
                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider pb-1 border-b border-slate-200">👨 Data Ayah</h4>
                <div class="space-y-1">
                  <label class="text-[11px] font-bold text-slate-500 uppercase">Nama Ayah</label>
                  <input v-model="form.father_name" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium" />
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase">Status</label>
                    <select v-model="form.father_status" class="w-full bg-white border border-slate-200 rounded-xl px-2 py-2 text-xs font-medium">
                      <option value="hidup">Hidup</option>
                      <option value="meninggal">Meninggal</option>
                      <option value="tidak_diketahui">Tidak Diketahui</option>
                      <option value="pisah">Pisah</option>
                    </select>
                  </div>
                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase">NIK Ayah</label>
                    <input v-model="form.father_nik" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-2 py-2 text-xs font-mono" maxlength="16" />
                  </div>
                </div>
              </div>

              <!-- Ibu -->
              <div class="space-y-3">
                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider pb-1 border-b border-slate-200">👩 Data Ibu</h4>
                <div class="space-y-1">
                  <label class="text-[11px] font-bold text-slate-500 uppercase">Nama Ibu</label>
                  <input v-model="form.mother_name" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium" />
                </div>
                <div class="grid grid-cols-2 gap-2">
                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase">Status</label>
                    <select v-model="form.mother_status" class="w-full bg-white border border-slate-200 rounded-xl px-2 py-2 text-xs font-medium">
                      <option value="hidup">Hidup</option>
                      <option value="meninggal">Meninggal</option>
                      <option value="tidak_diketahui">Tidak Diketahui</option>
                      <option value="pisah">Pisah</option>
                    </select>
                  </div>
                  <div class="space-y-1">
                    <label class="text-[11px] font-bold text-slate-500 uppercase">NIK Ibu</label>
                    <input v-model="form.mother_nik" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-2 py-2 text-xs font-mono" maxlength="16" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Data Wali -->
            <div class="bg-emerald-50/40 p-5 rounded-2xl border border-emerald-100 space-y-4">
              <h4 class="text-xs font-black text-emerald-900 uppercase tracking-wider flex items-center gap-1.5">
                <ShieldCheck class="w-4 h-4 text-emerald-600" />
                <span>Data Wali Resmi Siswa</span>
              </h4>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="space-y-1">
                  <label class="font-bold text-slate-600 uppercase text-[11px]">Nama Wali</label>
                  <input v-model="form.guardian_name" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium" />
                </div>
                <div class="space-y-1">
                  <label class="font-bold text-slate-600 uppercase text-[11px]">Hubungan dg Siswa</label>
                  <select v-model="form.guardian_relation" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium">
                    <option value="">Pilih Hubungan...</option>
                    <option value="Ayah Kandung">Ayah Kandung</option>
                    <option value="Ibu Kandung">Ibu Kandung</option>
                    <option value="Kakek">Kakek</option>
                    <option value="Nenek">Nenek</option>
                    <option value="Paman">Paman</option>
                    <option value="Bibi">Bibi</option>
                    <option value="Kakak Kandung">Kakak Kandung</option>
                    <option value="Lainnya">Lainnya</option>
                  </select>
                </div>
                <div class="space-y-1">
                  <label class="font-bold text-slate-600 uppercase text-[11px]">NIK Wali</label>
                  <input v-model="form.guardian_nik" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono" maxlength="16" />
                </div>
                <div class="space-y-1">
                  <label class="font-bold text-slate-600 uppercase text-[11px]">No. HP / WA Wali</label>
                  <input v-model="form.guardian_phone" type="text" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Col 3: Protected Info & Save -->
        <div class="space-y-6">
          <!-- Status Akademik (Read-Only) -->
          <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
              <Lock class="w-3.5 h-3.5 text-slate-400" />
              <span>Status Akademik (Resmi)</span>
            </h3>

            <div class="space-y-3 text-xs">
              <div class="p-3 bg-slate-50 rounded-xl space-y-0.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase">NISN Siswa</span>
                <p class="font-bold text-slate-800 font-mono">{{ studentData?.nisn || '-' }}</p>
              </div>
              <div class="p-3 bg-slate-50 rounded-xl space-y-0.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase">NIS Siswa</span>
                <p class="font-bold text-slate-800 font-mono">{{ studentData?.nis || '-' }}</p>
              </div>
              <div class="p-3 bg-slate-50 rounded-xl space-y-0.5">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Ruang Kelas</span>
                <p class="font-bold text-emerald-700">Kelas {{ studentData?.class_name || studentData?.class_room?.name || studentData?.classRoom?.name || '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Save Button Card -->
          <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-3">
            <button
              type="submit"
              :disabled="saving"
              class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
            >
              <Save v-if="!saving" class="w-4 h-4" />
              <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <span>{{ saving ? 'Menyimpan Biodata...' : 'Simpan Profil Siswa' }}</span>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  User,
  GraduationCap,
  Camera,
  Users,
  ShieldCheck,
  Lock,
  Save,
} from 'lucide-vue-next';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const user = ref(null);
const studentData = ref(null);

const form = reactive({
  full_name: '',
  nik: '',
  gender: '',
  birth_place: '',
  birth_date: '',
  address: '',
  parent_phone: '',
  previous_school: '',
  mother_name: '',
  mother_status: 'hidup',
  mother_nik: '',
  father_name: '',
  father_status: 'hidup',
  father_nik: '',
  guardian_name: '',
  guardian_relation: '',
  guardian_nik: '',
  guardian_phone: '',
});

const photoFile = ref(null);
const photoPreview = ref(null);

function onPhotoChange(e) {
  const file = e.target.files[0];
  if (!file) return;

  photoFile.value = file;
  photoPreview.value = URL.createObjectURL(file);
}

function clearPhoto() {
  photoFile.value = null;
  photoPreview.value = studentData.value?.photo_url || null;
  if (document.querySelector('input[type="file"]')) {
    document.querySelector('input[type="file"]').value = '';
  }
}

async function loadProfile() {
  loading.value = true;
  try {
    const res = await api.get('student/profile');
    const data = res?.data || res || {};
    user.value = data.user || {};
    studentData.value = data.student || {};

    const s = studentData.value;
    form.full_name = s.full_name || user.value.name || '';
    form.nik = s.nik || '';
    form.gender = s.gender || '';
    form.birth_place = s.birth_place || '';
    form.birth_date = s.birth_date ? String(s.birth_date).substring(0, 10) : '';
    form.address = s.address || '';
    form.parent_phone = s.parent_phone || '';
    form.previous_school = s.previous_school || '';
    form.mother_name = s.mother_name || '';
    form.mother_status = s.mother_status || 'hidup';
    form.mother_nik = s.mother_nik || '';
    form.father_name = s.father_name || '';
    form.father_status = s.father_status || 'hidup';
    form.father_nik = s.father_nik || '';
    form.guardian_name = s.guardian_name || '';
    form.guardian_relation = s.guardian_relation || '';
    form.guardian_nik = s.guardian_nik || '';
    form.guardian_phone = s.guardian_phone || '';

    photoPreview.value = s.photo_url || null;
    photoFile.value = null;
  } catch (err) {
    console.error('Error loading student profile:', err);
    toast.error('Gagal memuat profil siswa.');
  } finally {
    loading.value = false;
  }
}

async function saveProfile() {
  if (!form.full_name?.trim()) {
    toast.error('Nama lengkap siswa wajib diisi!');
    return;
  }

  saving.value = true;
  try {
    const formData = new FormData();
    Object.keys(form).forEach((key) => {
      if (form[key] !== null && form[key] !== undefined) {
        formData.append(key, form[key]);
      }
    });

    if (photoFile.value) {
      formData.append('photo', photoFile.value);
    }

    const res = await api.postForm('student/profile', formData);
    toast.success(res?.message || 'Profil berhasil diperbarui!');
    await loadProfile();
  } catch (err) {
    console.error('Error saving student profile:', err);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadProfile();
});
</script>
