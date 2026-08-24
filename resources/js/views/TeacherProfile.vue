<template>
  <div class="space-y-8 pb-12 font-inter max-w-5xl mx-auto">
    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-emerald-600 via-emerald-700 to-teal-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
      <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
        <!-- Photo Frame with Upload Trigger -->
        <div class="relative group">
          <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-white/15 backdrop-blur-md border-2 border-white/30 overflow-hidden shadow-2xl flex items-center justify-center">
            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="Foto Guru" />
            <div v-else class="text-4xl font-black text-white/80">
              {{ (form.full_name || user?.name || 'G').charAt(0).toUpperCase() }}
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
            <span>Akun Guru / Tenaga Pendidik</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black font-lexend text-white tracking-wide">
            {{ form.full_name || 'Nama Guru' }}
          </h1>
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 text-xs font-medium text-emerald-100">
            <span v-if="form.nip" class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">NIP: {{ form.nip }}</span>
            <span v-if="form.nuptk" class="bg-emerald-800/60 px-2.5 py-0.5 rounded-lg border border-emerald-500/30">NUPTK: {{ form.nuptk }}</span>
            <span v-if="form.position" class="bg-teal-800/60 px-2.5 py-0.5 rounded-lg border border-teal-500/30">{{ form.position }}</span>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-2.5">
          <button
            type="button"
            @click="openIdCardModal"
            class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-500/30 flex items-center gap-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
            <span>🪪 ID Card & QR Guru</span>
          </button>

          <button
            v-if="photoPreview && photoFile"
            type="button"
            @click="clearPhoto"
            class="px-3 py-2 bg-rose-500/80 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition-all cursor-pointer backdrop-blur-sm shadow-sm"
          >
            Batal Ganti
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16 bg-white rounded-3xl border border-slate-100 shadow-sm">
      <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-xs font-bold text-slate-500 mt-3">Memuat Biodata Profil Guru...</p>
    </div>

    <!-- Edit Form -->
    <form v-else @submit.prevent="saveProfile" class="space-y-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Col 1 & 2: Biodata & Kontak -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Card: Biodata Pribadi -->
          <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                <User class="w-4 h-4 text-emerald-600" />
                <span>Biodata Pendidik</span>
              </h3>
              <span class="text-[11px] text-slate-400 font-semibold">* Wajib diisi</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- Nama Lengkap & Gelar -->
              <div class="sm:col-span-2 space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
                  Nama Lengkap Beserta Gelar <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.full_name"
                  type="text"
                  required
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="Contoh: Dr. H. Ahmad Dahlan, M.Pd"
                />
              </div>

              <!-- NIP -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">NIP (Nomor Induk Pegawai)</label>
                <input
                  v-model="form.nip"
                  type="text"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="18 digit angka NIP"
                />
              </div>

              <!-- NUPTK -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">NUPTK</label>
                <input
                  v-model="form.nuptk"
                  type="text"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="16 digit NUPTK"
                />
              </div>

              <!-- Jenis Kelamin -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Jenis Kelamin</label>
                <select
                  v-model="form.gender"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                >
                  <option value="">Pilih Jenis Kelamin...</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
              </div>

              <!-- Jabatan / Posisi -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Jabatan / Tugas Tambahan</label>
                <input
                  v-model="form.position"
                  type="text"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="Contoh: Guru Mata Pelajaran / Pembina OSIS"
                />
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
            </div>
          </div>

          <!-- Card: Kontak & Domisili -->
          <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
              <h3 class="text-sm font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                <Phone class="w-4 h-4 text-emerald-600" />
                <span>Kontak & Domisili</span>
              </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <!-- No. HP / WhatsApp -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">No. Handphone / WhatsApp</label>
                <input
                  v-model="form.phone"
                  type="text"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="Contoh: 081234567890"
                />
              </div>

              <!-- Email Akun -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Email Akun</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all"
                  placeholder="email@sekolah.sch.id"
                />
              </div>

              <!-- Alamat Lengkap -->
              <div class="sm:col-span-2 space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">Alamat Tempat Tinggal</label>
                <textarea
                  v-model="form.address"
                  rows="3"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all resize-none"
                  placeholder="Alamat domisili lengkap guru"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Col 3: Informasi Penugasan & Tombol Simpan -->
        <div class="space-y-6">
          <!-- Penugasan Mengajar Card -->
          <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
              <BookOpen class="w-4 h-4 text-emerald-600" />
              <span>Mata Pelajaran Diampu</span>
            </h3>

            <div v-if="teacherData?.subjects && teacherData.subjects.length > 0" class="flex flex-wrap gap-2">
              <span
                v-for="sub in teacherData.subjects"
                :key="sub.id"
                class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold"
              >
                {{ sub.name }}
              </span>
            </div>
            <p v-else class="text-xs text-slate-400 italic">Belum ada mata pelajaran tertaut.</p>
          </div>

          <!-- Penugasan Wali Kelas Card -->
          <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-2">
              <Building2 class="w-4 h-4 text-emerald-600" />
              <span>Tugas Wali Kelas</span>
            </h3>

            <div v-if="teacherData?.classes && teacherData.classes.length > 0" class="space-y-2">
              <div
                v-for="cls in teacherData.classes"
                :key="cls.id"
                class="p-3 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold text-slate-800 flex justify-between items-center"
              >
                <span>Kelas {{ cls.name }}</span>
                <span class="text-[10px] text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md font-bold">Wali Kelas</span>
              </div>
            </div>
            <p v-else class="text-xs text-slate-400 italic">Bukan wali kelas saat ini.</p>
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
              <span>{{ saving ? 'Menyimpan Perubahan...' : 'Simpan Profil Guru' }}</span>
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- Modal ID Card Guru & QR Personal -->
    <div v-if="showIdCardModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-100 animate-slide-up flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="p-6 pb-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/75 flex-shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
            </div>
            <div>
              <h3 class="text-sm font-black text-slate-800 font-lexend">Kartu Identitas Pendidik (ID Card)</h3>
              <p class="text-[11px] text-slate-400 font-medium">Kartu resmi guru dengan QR Code presensi personal</p>
            </div>
          </div>
          <button @click="showIdCardModal = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer transition-colors">✕</button>
        </div>

        <!-- Body: Card Preview (Front & Back) -->
        <div class="p-6 sm:p-8 overflow-y-auto space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center justify-center">
            <!-- Front Side -->
            <div class="w-full max-w-[280px] mx-auto aspect-[1/1.58] bg-gradient-to-br from-emerald-800 via-teal-900 to-slate-900 rounded-3xl p-5 text-white shadow-xl relative overflow-hidden flex flex-col justify-between border border-emerald-500/30">
              <div class="absolute -right-8 -top-8 w-28 h-28 bg-emerald-400/20 rounded-full blur-xl pointer-events-none"></div>
              
              <!-- Card Header -->
              <div class="text-center space-y-1 relative z-10 border-b border-white/15 pb-2">
                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-300">KARTU TANDA PENDIDIK</span>
                <h4 class="text-xs font-black uppercase tracking-tight text-white">{{ schoolName }}</h4>
              </div>

              <!-- Card Photo & Info -->
              <div class="text-center space-y-3 relative z-10 my-auto">
                <div class="w-24 h-24 mx-auto rounded-2xl bg-white/10 p-1 border-2 border-emerald-400/60 shadow-lg overflow-hidden">
                  <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover rounded-xl" alt="Foto Guru" />
                  <div v-else class="w-full h-full flex items-center justify-center text-3xl font-black text-white/80">
                    {{ (form.full_name || 'G').charAt(0).toUpperCase() }}
                  </div>
                </div>

                <div class="space-y-0.5">
                  <h5 class="text-sm font-black font-lexend text-white leading-snug line-clamp-2">
                    {{ form.full_name || 'Nama Guru' }}
                  </h5>
                  <p class="text-[10px] font-mono text-emerald-200 font-bold">NIP: {{ form.nip || '-' }}</p>
                  <p class="text-[10px] text-teal-200 font-medium">{{ form.position || 'Tenaga Pendidik' }}</p>
                </div>
              </div>

              <!-- Card Footer -->
              <div class="text-center pt-2 border-t border-white/15 relative z-10">
                <span class="text-[8px] font-bold uppercase tracking-widest text-emerald-300/80">RESMI SEKOLAH</span>
              </div>
            </div>

            <!-- Back Side -->
            <div class="w-full max-w-[280px] mx-auto aspect-[1/1.58] bg-white rounded-3xl p-5 text-slate-800 shadow-xl relative overflow-hidden flex flex-col justify-between border-2 border-slate-200">
              <!-- Back Header -->
              <div class="text-center space-y-0.5 border-b border-slate-100 pb-2">
                <span class="text-[8px] font-black uppercase tracking-widest text-emerald-700">QR CODE PRESENSI PERSONAL</span>
                <p class="text-[10px] text-slate-500 font-medium">Scan kartu ini pada scanner piket gerbang</p>
              </div>

              <!-- QR Code Frame -->
              <div class="my-auto text-center space-y-2">
                <div class="p-2 bg-emerald-50 rounded-2xl border border-emerald-200 inline-block shadow-inner">
                  <img v-if="idCardQrUrl" :src="idCardQrUrl" class="w-36 h-36 object-contain" alt="QR Card" />
                  <div v-else class="w-36 h-36 flex items-center justify-center text-xs text-slate-400 font-bold">Membuat QR...</div>
                </div>
                <p class="text-[9px] font-mono text-slate-500 font-bold">ID: {{ teacherData?.id }} | {{ form.nip || 'NONIP' }}</p>
              </div>

              <!-- Back Footer & Terms -->
              <div class="text-center border-t border-slate-100 pt-2 space-y-1">
                <p class="text-[7.5px] text-slate-400 leading-tight">
                  Kartu ini adalah milik resmi sekolah. Jika ditemukan harap kembalikan ke {{ schoolName }}.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-6 pt-0 flex gap-3 flex-shrink-0">
          <button
            type="button"
            @click="printIdCard"
            class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Kartu Pegawai (ID Card & QR)</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { api } from '../api';
import { useToast } from '../composables/useToast';
import QRCode from 'qrcode';
import {
  User,
  GraduationCap,
  Camera,
  Phone,
  BookOpen,
  Building2,
  Save,
} from 'lucide-vue-next';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const user = ref(null);
const teacherData = ref(null);
const showIdCardModal = ref(false);
const idCardQrUrl = ref('');
const schoolName = ref('MTs Al-Hasanah');

const form = reactive({
  full_name: '',
  nuptk: '',
  nip: '',
  gender: '',
  birth_place: '',
  birth_date: '',
  phone: '',
  address: '',
  position: '',
  email: '',
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
  photoPreview.value = teacherData.value?.photo_url || null;
  if (document.querySelector('input[type="file"]')) {
    document.querySelector('input[type="file"]').value = '';
  }
}

async function openIdCardModal() {
  showIdCardModal.value = true;
  if (teacherData.value?.qr_card_payload) {
    try {
      idCardQrUrl.value = await QRCode.toDataURL(teacherData.value.qr_card_payload, {
        width: 300,
        margin: 1,
        color: {
          dark: '#064E3B',
          light: '#FFFFFF'
        }
      });
    } catch (err) {
      console.error(err);
    }
  }
}

function printIdCard() {
  if (!idCardQrUrl.value) return;
  const printWin = window.open('', '_blank', 'width=850,height=900');
  if (!printWin) {
    toast.error('Gagal membuka jendela cetak. Pastikan pop-up diizinkan.');
    return;
  }

  const name = form.full_name || 'Guru';
  const nip = form.nip || '-';
  const position = form.position || 'Pendidik';
  const photo = photoPreview.value || '';
  const sName = schoolName.value;

  printWin.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Cetak ID Card Guru - ${name}</title>
        <style>
          @page { size: A4 portrait; margin: 15mm; }
          body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; padding: 20px; text-align: center; }
          .sheet-title { font-size: 16px; font-weight: 800; text-transform: uppercase; color: #0f172a; margin-bottom: 20px; }
          .cards-container { display: flex; gap: 24px; justify-content: center; align-items: center; margin: 0 auto; }
          
          /* CR80 Standard ID Card Dimensions (54mm x 85.6mm) scaled */
          .card {
            width: 54mm;
            height: 85.6mm;
            border-radius: 4mm;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
          }
          
          /* Front Card */
          .card-front {
            background: linear-gradient(145deg, #065f46, #0f766e, #0f172a);
            color: white;
            padding: 4mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
          }
          .card-front .header { font-size: 7pt; font-weight: 900; letter-spacing: 0.5px; color: #6ee7b7; border-bottom: 0.5px solid rgba(255,255,255,0.2); padding-bottom: 2mm; }
          .card-front .school { font-size: 8pt; font-weight: 900; color: white; margin-top: 1mm; text-transform: uppercase; }
          .card-front .photo-box { width: 22mm; height: 22mm; margin: 2mm auto; border-radius: 3mm; border: 1.5px solid #34d399; overflow: hidden; background: #042f2e; }
          .card-front .photo-box img { width: 100%; height: 100%; object-fit: cover; }
          .card-front .name { font-size: 9pt; font-weight: 900; color: white; line-height: 1.2; }
          .card-front .nip { font-size: 7pt; font-family: monospace; color: #a7f3d0; margin-top: 1mm; font-weight: bold; }
          .card-front .role { font-size: 7pt; color: #ccfbf1; }
          .card-front .footer { font-size: 6pt; font-weight: bold; color: #6ee7b7; border-top: 0.5px solid rgba(255,255,255,0.2); padding-top: 1.5mm; text-transform: uppercase; }

          /* Back Card */
          .card-back {
            background: white;
            color: #1e293b;
            padding: 4mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
            border: 1px solid #cbd5e1;
          }
          .card-back .header { font-size: 6.5pt; font-weight: 900; color: #065f46; border-bottom: 0.5px solid #e2e8f0; padding-bottom: 1.5mm; text-transform: uppercase; }
          .card-back .qr-box { margin: auto; padding: 1.5mm; background: #f0fdf4; border: 1px dashed #059669; border-radius: 3mm; display: inline-block; }
          .card-back .qr-box img { width: 32mm; height: 32mm; display: block; }
          .card-back .id-text { font-size: 6.5pt; font-family: monospace; color: #64748b; font-weight: bold; margin-top: 1mm; }
          .card-back .terms { font-size: 5.5pt; color: #94a3b8; line-height: 1.3; border-top: 0.5px solid #e2e8f0; padding-top: 1.5mm; }
          
          .instructions { margin-top: 24px; font-size: 11px; color: #475569; max-width: 450px; margin-left: auto; margin-right: auto; }
        </style>
      </head>
      <body>
        <div class="sheet-title">KARTU IDENTITAS RESMI GURU / TENAGA PENDIDIK</div>
        
        <div class="cards-container">
          <!-- Front -->
          <div class="card card-front">
            <div>
              <div class="header">KARTU TANDA PENDIDIK</div>
              <div class="school">${sName}</div>
            </div>
            <div>
              <div class="photo-box">
                ${photo ? `<img src="${photo}" />` : `<div style="padding-top:6mm;font-size:16pt;font-weight:900;color:white;">${name.charAt(0)}</div>`}
              </div>
              <div class="name">${name}</div>
              <div class="nip">NIP: ${nip}</div>
              <div class="role">${position}</div>
            </div>
            <div class="footer">RESMI SEKOLAH</div>
          </div>

          <!-- Back -->
          <div class="card card-back">
            <div class="header">QR CODE PRESENSI RESMI</div>
            <div>
              <div class="qr-box">
                <img src="${idCardQrUrl.value}" />
              </div>
              <div class="id-text">ID: ${teacherData.value?.id} | ${nip}</div>
            </div>
            <div class="terms">
              Kartu ini adalah tanda pengenal resmi pendidik di ${sName}. Gunakan QR ini untuk presensi di meja piket / gerbang sekolah.
            </div>
          </div>
        </div>

        <div class="instructions">
          <strong>PETUNJUK CETAK:</strong><br>
          Gunakan kertas tebal (Kertas Foto / Glossy / PVC Card), cetak dengan skala 100%, lalu gunting dan masukkan ke dalam plastik ID Card atau tempelkan bolak-balik.
        </div>
      </body>
    </html>
  `);

  printWin.document.close();
  setTimeout(() => {
    printWin.focus();
    printWin.print();
  }, 500);
}

async function loadProfile() {
  loading.value = true;
  try {
    const res = await api.get('teacher/profile');
    const data = res?.data || res || {};
    user.value = data.user || {};
    teacherData.value = data.teacher || {};

    const t = teacherData.value;
    form.full_name = t.full_name || user.value.name || '';
    form.nuptk = t.nuptk || '';
    form.nip = t.nip || '';
    form.gender = t.gender || '';
    form.birth_place = t.birth_place || '';
    form.birth_date = t.birth_date ? String(t.birth_date).substring(0, 10) : '';
    form.phone = t.phone || '';
    form.address = t.address || '';
    form.position = t.position || '';
    form.email = user.value.email || '';

    photoPreview.value = t.photo_url || null;
    photoFile.value = null;
  } catch (err) {
    console.error('Error loading teacher profile:', err);
    toast.error('Gagal memuat profil guru.');
  } finally {
    loading.value = false;
  }
}

async function saveProfile() {
  if (!form.full_name?.trim()) {
    toast.error('Nama lengkap guru wajib diisi!');
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

    const res = await api.postForm('teacher/profile', formData);
    toast.success(res?.message || 'Profil berhasil diperbarui!');
    await loadProfile();
  } catch (err) {
    console.error('Error saving teacher profile:', err);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadProfile();
});
</script>
