<template>
  <div class="space-y-6 font-inter">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-11 h-11 bg-slate-900 text-white rounded-xl border border-slate-800 flex items-center justify-center flex-shrink-0 shadow-xs">
          <Settings class="w-5 h-5 text-emerald-400" />
        </div>
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengaturan Aplikasi</h1>
          <p class="text-xs text-slate-500 font-normal mt-0.5">Ubah identitas madrasah, teks landing page, peta lokasi, dan logo aplikasi di sini.</p>
        </div>
      </div>
    </div>

    <form @submit.prevent="saveSettings" class="space-y-6">
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Left Column: Identitas Sekolah & Logo -->
        <div class="xl:col-span-2 space-y-6">
          
          <!-- 1. Identitas Madrasah -->
          <div class="shadcn-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
              <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center">
                <Building class="w-4 h-4" />
              </div>
              <div>
                <h3 class="font-bold text-slate-900 text-sm tracking-tight">Identitas Madrasah</h3>
                <p class="text-[11px] text-slate-500 font-normal">Nama resmi, logo, dan akreditasi madrasah.</p>
              </div>
            </div>
            
            <div class="p-6 space-y-6">
              <!-- Logo Upload -->
              <div class="flex items-center gap-5">
                <div class="relative flex-shrink-0 group">
                  <div class="w-24 h-24 rounded-2xl overflow-hidden bg-slate-50 border border-slate-200 group-hover:border-emerald-500 flex items-center justify-center transition-colors shadow-inner">
                    <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-contain p-2" alt="Logo Aplikasi" />
                    <div v-else class="flex flex-col items-center gap-1 text-slate-400 group-hover:text-emerald-600 transition-colors">
                      <Image class="w-6 h-6" />
                      <span class="text-[9px] font-bold">LOGO</span>
                    </div>
                  </div>
                  <button 
                    type="button" 
                    @click="$refs.logoInput.click()" 
                    class="absolute -bottom-1.5 -right-1.5 w-8 h-8 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 flex items-center justify-center transition-transform hover:scale-110 active:scale-95 cursor-pointer"
                  >
                    <Plus class="w-4 h-4" />
                  </button>
                  <input ref="logoInput" type="file" accept="image/*" class="hidden" @change="onLogoChange" />
                </div>
                <div class="space-y-1">
                  <p class="text-xs font-semibold text-slate-800">Logo Resmi Madrasah</p>
                  <p class="text-[11px] text-slate-500 max-w-sm leading-relaxed">Format JPG, PNG, atau SVG berlatar transparan (Maks. 2MB).</p>
                  <button v-if="logoPreview && isNewLogo" type="button" @click="clearLogo" class="text-[11px] font-semibold text-rose-600 hover:underline pt-1 cursor-pointer">Batal Pilih File</button>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                  <label class="block text-xs font-semibold text-slate-700">Nama Madrasah / Sekolah</label>
                  <input v-model="form.app_name" type="text" placeholder="Contoh: MTS AL - HASANAH" class="form-input" />
                </div>
                
                <div class="space-y-1.5">
                  <label class="block text-xs font-semibold text-slate-700">Akreditasi</label>
                  <input v-model="form.school_accreditation" type="text" placeholder="Contoh: Akreditasi A" class="form-input" />
                </div>

                <div class="space-y-1.5 md:col-span-2">
                  <label class="block text-xs font-semibold text-slate-700">Slogan / Motto Utama</label>
                  <input v-model="form.app_tagline" type="text" placeholder="Contoh: Portal Madrasah Tsanawiyah Al - Hasanah Ciomas" class="form-input" />
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Alamat Lengkap</label>
                <textarea v-model="form.school_address" rows="2" placeholder="Contoh: Jl. Raya Ciomas No. 123, Kabupaten Bogor" class="form-input h-20 resize-none"></textarea>
              </div>
            </div>
          </div>
          
          <!-- 2. Konten Profil Website -->
          <div class="shadcn-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
              <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center">
                <Globe class="w-4 h-4" />
              </div>
              <div>
                <h3 class="font-bold text-slate-900 text-sm tracking-tight">Konten Profil Website (Beranda Publik)</h3>
                <p class="text-[11px] text-slate-500 font-normal">Sambutan kepala sekolah, visi misi, dan deskripsi publik.</p>
              </div>
            </div>

            <div class="p-6 space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                  <label class="block text-xs font-semibold text-slate-700">Nama Kepala Sekolah</label>
                  <select v-model="form.principal_teacher_id" class="form-input cursor-pointer font-normal">
                    <option value="">-- Pilih Kepala Sekolah dari Data Guru --</option>
                    <option v-for="teacher in teachersList" :key="teacher.id" :value="teacher.id">
                      {{ teacher.full_name }} (NIP/NUPTK: {{ teacher.nip }})
                    </option>
                  </select>
                  <p class="text-[10px] text-slate-400">Pilih dari daftar guru yang telah terdaftar di sistem.</p>
                </div>
                <div class="space-y-1.5">
                  <label class="block text-xs font-semibold text-slate-700">Sambutan Singkat Kepala Sekolah</label>
                  <textarea v-model="form.principal_message" rows="2" placeholder="Kutipan penyemangat dari kepsek..." class="form-input h-16 resize-none"></textarea>
                </div>
                
                <div class="space-y-1.5 md:col-span-2">
                  <label class="block text-xs font-semibold text-slate-700">Teks Penjelasan Kepala Sekolah</label>
                  <textarea v-model="form.principal_description" rows="3" placeholder="Selamat datang di website resmi..." class="form-input h-24 resize-none leading-relaxed"></textarea>
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Visi Madrasah</label>
                <textarea v-model="form.school_vision" rows="2" placeholder="Menjadi lembaga pendidikan unggul..." class="form-input h-16 resize-none"></textarea>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 flex justify-between">
                  <span>Misi Madrasah</span>
                  <span class="text-[10px] text-slate-400 normal-case">(Pisahkan dengan baris baru / Enter)</span>
                </label>
                <textarea v-model="form.school_mission" rows="4" placeholder="1. Menyelenggarakan pendidikan...\n2. Menciptakan lingkungan belajar..." class="form-input h-24 resize-none leading-relaxed"></textarea>
              </div>
            </div>
          </div>

          <!-- 3. Pengaturan Peta Lokasi Google Maps & Kontak -->
          <div class="shadcn-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center">
                  <MapPin class="w-4 h-4" />
                </div>
                <div>
                  <h3 class="font-bold text-slate-900 text-sm tracking-tight">Peta Lokasi Google Maps & Kontak</h3>
                  <p class="text-[11px] text-slate-500 font-normal">Pengaturan koordinat peta dan nomor kontak resmi di landing page.</p>
                </div>
              </div>
            </div>

            <div class="p-6 space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                  <label class="block text-xs font-semibold text-slate-700">No. Telepon / WhatsApp</label>
                  <input v-model="form.school_phone" type="text" placeholder="Contoh: 0812-3456-7890" class="form-input" />
                </div>
                <div class="space-y-1.5">
                  <label class="block text-xs font-semibold text-slate-700">Email Resmi</label>
                  <input v-model="form.school_email" type="email" placeholder="Contoh: info@sekolah.sch.id" class="form-input" />
                </div>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 flex justify-between">
                  <span>Link Sematan Google Maps (Embed URL)</span>
                  <span class="text-[10px] text-emerald-700 font-semibold">💡 Buka Google Maps ➔ Bagikan ➔ Sematkan Peta ➔ Salin URL iframe</span>
                </label>
                <textarea v-model="form.google_maps_embed" rows="3" placeholder="Tempelkan URL Google Maps Embed (https://www.google.com/maps/embed?pb=...) atau tag <iframe src='...'>..." class="form-input font-mono text-xs h-20 leading-relaxed"></textarea>
              </div>

              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Link Petunjuk Arah Google Maps (Buka Langsung di HP)</label>
                <input v-model="form.google_maps_link" type="text" placeholder="Contoh: https://maps.app.goo.gl/... atau https://goo.gl/maps/..." class="form-input font-mono text-xs" />
              </div>

              <!-- Live Map Preview Box -->
              <div class="space-y-1.5 pt-2">
                <label class="block text-xs font-semibold text-slate-700">Live Preview Peta Lokasi Sekolah</label>
                <div class="w-full h-56 rounded-xl border border-slate-200 bg-slate-50 overflow-hidden relative shadow-inner">
                  <iframe
                    v-if="cleanMapsUrl"
                    :src="cleanMapsUrl"
                    class="w-full h-full border-0"
                    allowfullscreen=""
                    loading="lazy"
                  ></iframe>
                  <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-400 p-4 text-center space-y-1.5">
                    <MapPin class="w-6 h-6 opacity-40" />
                    <p class="text-xs font-medium">Live Preview Peta akan muncul di sini setelah Link Embed diisi.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Column: Tahun Ajaran Aktif & Quick Actions -->
        <div class="space-y-6">
          
          <!-- Deep Emerald Card: Tahun Ajaran Aktif -->
          <div class="bg-gradient-to-br from-[#062c20] via-emerald-950 to-slate-900 border border-emerald-900/60 rounded-2xl shadow-md p-6 text-white relative overflow-hidden space-y-4">
            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-emerald-900/50 border border-emerald-700/60 rounded-xl flex items-center justify-center flex-shrink-0 text-white shadow-inner">
                <CalendarCheck class="w-5 h-5 text-emerald-400" />
              </div>
              <div>
                <h3 class="font-bold text-white text-sm tracking-tight">Tahun Ajaran Aktif</h3>
                <p class="text-[11px] text-emerald-300/80 font-normal">Sinkronisasi periode akademik.</p>
              </div>
            </div>
            
            <div class="space-y-3 pt-1">
              <p class="text-slate-300 text-xs leading-relaxed font-normal">
                Tentukan tahun ajaran dan semester aktif. Ini akan mengubah sinkronisasi jadwal, nilai, dan absensi di seluruh sistem.
              </p>

              <select v-model="form.academic_year_id" class="w-full bg-white/10 border border-emerald-500/30 text-white rounded-lg px-3.5 py-2 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400 transition-all cursor-pointer">
                <option value="" class="text-slate-900">-- Pilih Tahun Ajaran Aktif --</option>
                <option v-for="ay in academicYears" :key="ay.id" :value="ay.id" class="text-slate-900">
                  {{ ay.year }} - Semester {{ ay.semester === 'odd' ? 'Ganjil' : 'Genap' }} {{ ay.is_active ? '(Aktif Saat Ini)' : '' }}
                </option>
              </select>
              
              <RouterLink to="/admin/academic-years" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition-colors pt-1">
                <span>Kelola Tahun Ajaran</span>
                <ArrowRight class="w-3.5 h-3.5" />
              </RouterLink>
            </div>
          </div>

          <!-- Save Button Card -->
          <div class="shadcn-card p-6 space-y-3">
            <p class="text-xs text-slate-500 font-normal leading-relaxed">
              Pastikan seluruh perubahan data identitas madrasah sudah benar sebelum menyimpan.
            </p>
            <button 
              type="submit" 
              :disabled="loading" 
              class="btn-primary w-full py-3 flex items-center justify-center gap-2 cursor-pointer shadow-md shadow-emerald-600/20"
            >
              <div v-if="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
              <Save v-else class="w-4 h-4" />
              <span>{{ loading ? 'Menyimpan Perubahan...' : 'Simpan Semua Pengaturan' }}</span>
            </button>
          </div>

        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import {
  Settings,
  Building,
  Image,
  Plus,
  Globe,
  MapPin,
  CalendarCheck,
  ArrowRight,
  Save
} from 'lucide-vue-next';

const toast = useToast();
const loading = ref(false);

const academicYears = ref([]);
const teachersList = ref([]);
const form = reactive({
  app_name: '',
  app_tagline: '',
  school_address: '',
  school_accreditation: '',
  principal_teacher_id: '',
  principal_message: '',
  principal_description: '',
  school_vision: '',
  school_mission: '',
  academic_year_id: '',
  hero_title: '',
  google_maps_embed: '',
  google_maps_link: '',
  school_phone: '',
  school_email: '',
  app_logo: null,
  hero_background: null,
});

const logoPreview = ref(null);
const initialLogoUrl = ref(null);

const isNewLogo = computed(() => !!form.app_logo);

const cleanMapsUrl = computed(() => {
  if (!form.google_maps_embed) return '';
  const match = form.google_maps_embed.match(/src=["']([^"']+)["']/);
  if (match) return match[1];
  if (form.google_maps_embed.startsWith('http')) return form.google_maps_embed;
  return '';
});

const loadAcademicYears = async () => {
  try {
    const res = await api.get('admin/academic-years');
    academicYears.value = res.data?.data || res.data || [];
  } catch (err) {
    console.error('Failed to load academic years', err);
  }
};

const loadTeachers = async () => {
  try {
    const res = await api.get('admin/teachers');
    teachersList.value = res.data?.data || res.data || [];
  } catch (err) {
    console.error('Failed to load teachers', err);
  }
};

const loadSettings = async () => {
  try {
    const res = await api.get('admin/settings');
    const data = res.data?.data || res.data || {};
    
    Object.keys(form).forEach(key => {
      if (data[key] !== undefined && key !== 'app_logo' && key !== 'hero_background') {
        form[key] = data[key];
      }
    });

    const logo = data.app_logo_url || data.app_logo;
    if (logo) {
      initialLogoUrl.value = logo.startsWith('http') 
        ? logo 
        : `/storage/${logo.replace(/^\/?storage\//, '')}`;
      logoPreview.value = initialLogoUrl.value;
    }
  } catch (err) {
    toast.error('Gagal memuat pengaturan aplikasi');
  }
};

function onLogoChange(event) {
  const file = event.target.files[0];
  if (file) {
    form.app_logo = file;
    logoPreview.value = URL.createObjectURL(file);
  }
}

function clearLogo() {
  form.app_logo = null;
  logoPreview.value = initialLogoUrl.value;
}

async function saveSettings() {
  loading.value = true;
  const formData = new FormData();
  
  Object.keys(form).forEach(key => {
    if (form[key] !== null && form[key] !== undefined) {
      formData.append(key, form[key]);
    }
  });

  try {
    await api.post('admin/settings', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    toast.success('Pengaturan aplikasi berhasil disimpan!');
    await loadSettings();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan pengaturan');
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  await Promise.all([
    loadSettings(),
    loadAcademicYears(),
    loadTeachers()
  ]);
});
</script>
