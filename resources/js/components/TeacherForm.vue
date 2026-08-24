<template>
  <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl max-h-full flex flex-col overflow-hidden">
      <!-- Modal Header -->
      <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-white z-10">
        <div>
          <h2 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">{{ title }}</h2>
          <p class="text-xs text-slate-400 font-medium mt-0.5">Isi seluruh kolom yang ditandai bintang merah (<span class="text-red-500 font-bold">*</span>) wajib diisi.</p>
        </div>
        <button @click="emit('close')" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <form ref="formRef" @submit.prevent="onSubmit" class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar">
        <!-- Form Header Alert Banner -->
        <div v-if="hasErrors" class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 text-red-800 shadow-sm animate-shake">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <p class="text-xs font-bold uppercase tracking-wider">Formulir Belum Lengkap!</p>
            <p class="text-xs font-medium mt-0.5">Terdapat <span class="font-bold underline">{{ errorCount }} kolom wajib diisi</span> yang masih kosong atau belum valid. Silakan periksa kolom yang ditandai merah di bawah ini.</p>
          </div>
        </div>

        <!-- Photo Upload -->
        <div class="flex items-center gap-6">
          <div class="relative flex-shrink-0">
            <div class="w-24 h-24 rounded-2xl overflow-hidden bg-slate-100 border-2 border-dashed border-slate-200 flex items-center justify-center">
              <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="Foto Guru" />
              <div v-else class="flex flex-col items-center gap-1 text-slate-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="text-[9px] font-bold">FOTO</span>
              </div>
            </div>
            <button type="button" @click="$refs.photoInput.click()" class="absolute -bottom-2 -right-2 w-7 h-7 rounded-full bg-emerald-600 text-white shadow-md flex items-center justify-center hover:bg-emerald-700 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14M5 12h14"/></svg>
            </button>
            <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
          </div>
          <div>
            <p class="text-sm font-bold text-slate-700">Foto Guru</p>
            <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, GIF. Maks 2MB.</p>
            <button v-if="photoPreview" type="button" @click="clearPhoto" class="mt-2 text-[11px] font-bold text-red-500 hover:text-red-600 transition-colors">Hapus Foto</button>
          </div>
        </div>

        <div class="border-t border-slate-100"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <!-- Nama Lengkap -->
          <div class="md:col-span-2 space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
              Nama Lengkap <span class="text-red-500 font-bold">*</span>
            </label>
            <input
              v-model="form.full_name"
              type="text"
              @input="clearError('full_name')"
              :class="[
                errors.full_name ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
              ]"
              placeholder="Masukkan nama lengkap guru beserta gelar"
            />
            <p v-if="errors.full_name" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
              <span>🔴</span> {{ errors.full_name }}
            </p>
          </div>

          <!-- NUPTK / NIP -->
          <div class="space-y-1.5">
            <label class="flex justify-between items-end">
              <span class="text-xs font-bold text-slate-600 uppercase tracking-wide">
                NUPTK / NIP <span class="text-red-500 font-bold">*</span>
              </span>
              <span class="text-[10px] font-semibold text-slate-400">Digunakan untuk Login</span>
            </label>
            <input
              v-model="form.nip"
              type="text"
              maxlength="20"
              @input="clearError('nip')"
              :class="[
                errors.nip ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
              ]"
              placeholder="Contoh: 1987654321"
            />
            <p v-if="errors.nip" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
              <span>🔴</span> {{ errors.nip }}
            </p>
          </div>

          <!-- No. HP -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
              No. WhatsApp / Telepon <span class="text-red-500 font-bold">*</span>
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-bold">+62</span>
              <input
                v-model="form.phone"
                type="text"
                @input="clearError('phone')"
                :class="[
                  errors.phone ? 'border-red-500 bg-red-50/40' : 'border-slate-200 bg-slate-50',
                  'w-full border rounded-xl pl-12 pr-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium'
                ]"
                placeholder="8xx-xxxx-xxxx"
              />
            </div>
            <p v-if="errors.phone" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
              <span>🔴</span> {{ errors.phone }}
            </p>
          </div>

          <!-- Gender -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
              Jenis Kelamin <span class="text-red-500 font-bold">*</span>
            </label>
            <select
              v-model="form.gender"
              @change="clearError('gender')"
              :class="[
                errors.gender ? 'border-red-500 bg-red-50/40 focus:ring-red-300' : 'border-slate-200 bg-slate-50 focus:ring-emerald-400/30 focus:border-emerald-400',
                'w-full border rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 transition-all font-medium'
              ]"
            >
              <option value="">Pilih Jenis Kelamin...</option>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
            <p v-if="errors.gender" class="text-[10px] font-bold text-red-500 mt-1 flex items-center gap-1">
              <span>🔴</span> {{ errors.gender }}
            </p>
          </div>

          <!-- Jabatan / Posisi -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">
              Jabatan / Posisi <span class="text-slate-400 font-normal">(Opsional)</span>
            </label>
            <input
              v-model="form.position"
              type="text"
              class="w-full border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium"
              placeholder="Misal: Wakasek / Wali Kelas 8 / Pembina OSIS"
            />
          </div>

          <!-- Mata Pelajaran & Penugasan Kelas -->
          <div class="space-y-4 md:col-span-2 pt-2 border-t border-slate-100">
            <div class="flex items-center justify-between">
              <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Mata Pelajaran yang Diampu & Kelasnya</label>
              <span class="text-[11px] font-semibold text-slate-400">Centang Mapel, lalu tentukan Kelasnya</span>
            </div>

            <!-- Subject Checklist -->
            <div v-if="subjects.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <label v-for="subject in subjects" :key="subject.id"
                     class="flex items-center gap-2.5 p-3 rounded-xl border transition-all cursor-pointer"
                     :class="isSubjectSelected(subject.id) ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-500/20' : 'bg-slate-50 border-slate-200 hover:bg-white hover:border-slate-300'">
                <input type="checkbox" :checked="isSubjectSelected(subject.id)" @change="toggleSubject(subject.id)" class="w-4 h-4 text-emerald-600 bg-white border-slate-300 rounded focus:ring-emerald-500" />
                <span class="text-xs sm:text-sm font-bold text-slate-800">{{ subject.name }}</span>
              </label>
            </div>
            <p v-else class="text-xs font-medium text-slate-400 italic">Belum ada mata pelajaran di sistem.</p>

            <!-- Class assignment cards per selected subject -->
            <div v-if="selectedSubjectIds.length > 0" class="space-y-3 pt-2">
              <div v-for="subId in selectedSubjectIds" :key="'assign-'+subId" class="p-4 bg-slate-50/80 border border-slate-200 rounded-2xl space-y-3 shadow-sm">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">{{ getSubjectName(subId) }}</h4>
                  </div>
                  <button type="button" @click="toggleAllClassesForSubject(subId)" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 bg-emerald-100/70 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition-colors cursor-pointer">
                    {{ isAllClassesSelected(subId) ? 'Batal Pilih Semua' : '✓ Pilih Semua Kelas' }}
                  </button>
                </div>

                <div v-if="classesList.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                  <label v-for="cls in classesList" :key="cls.id" class="flex items-center gap-2 cursor-pointer p-2 rounded-xl border bg-white hover:border-emerald-300 transition-all text-xs">
                    <input type="checkbox" :value="cls.id" v-model="subjectAssignments[subId]" class="w-3.5 h-3.5 text-emerald-600 bg-slate-50 border-slate-300 rounded focus:ring-emerald-500" />
                    <span class="font-bold text-slate-700">{{ cls.name }}</span>
                  </label>
                </div>
                <p v-else class="text-xs text-slate-400 italic">Belum ada data kelas.</p>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Action Buttons -->
      <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 z-10">
        <button @click="emit('close')" type="button" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors shadow-sm text-sm">
          Batal
        </button>
        <button type="button" @click="onSubmit" :disabled="loading" class="px-6 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2 text-sm disabled:opacity-50">
          <svg v-if="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle>
          </svg>
          <span v-if="loading">Menyimpan...</span>
          <span v-else>Simpan Data Guru</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted } from 'vue';
import { api } from '../api';

const props = defineProps({
  title: { type: String, required: true },
  model: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close', 'save']);
const loading = ref(false);
const subjects = ref([]);
const classesList = ref([]);
const errors = reactive({});
const formRef = ref(null);

const errorCount = computed(() => Object.keys(errors).length);
const hasErrors = computed(() => errorCount.value > 0);

function clearError(field) {
  if (errors[field]) {
    delete errors[field];
  }
}

const form = reactive({
  nip: '',
  full_name: '',
  phone: '',
  position: '',
  gender: '',
});

const subjectAssignments = reactive({});

const selectedSubjectIds = computed(() => {
  return Object.keys(subjectAssignments).map(Number).filter(id => Array.isArray(subjectAssignments[id]));
});

function isSubjectSelected(subjectId) {
  return Array.isArray(subjectAssignments[subjectId]);
}

function toggleSubject(subjectId) {
  if (isSubjectSelected(subjectId)) {
    delete subjectAssignments[subjectId];
  } else {
    subjectAssignments[subjectId] = classesList.value.map(c => c.id);
  }
}

function getSubjectName(subId) {
  return subjects.value.find(s => s.id == subId)?.name || 'Mata Pelajaran';
}

function isAllClassesSelected(subId) {
  const current = subjectAssignments[subId] || [];
  return classesList.value.length > 0 && current.length === classesList.value.length;
}

function toggleAllClassesForSubject(subId) {
  if (isAllClassesSelected(subId)) {
    subjectAssignments[subId] = [];
  } else {
    subjectAssignments[subId] = classesList.value.map(c => c.id);
  }
}

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
  photoPreview.value = null;
}

function validateForm() {
  Object.keys(errors).forEach(key => delete errors[key]);

  if (!form.full_name?.trim()) errors.full_name = 'Nama lengkap guru wajib diisi';
  if (!form.nip?.trim()) errors.nip = 'NUPTK / NIP wajib diisi';
  if (!form.phone?.trim()) errors.phone = 'No. Telepon / WhatsApp wajib diisi';
  if (!form.gender) errors.gender = 'Jenis kelamin wajib dipilih';

  return Object.keys(errors).length === 0;
}

watch(
  () => props.model,
  (val) => {
    Object.keys(errors).forEach(key => delete errors[key]);

    form.nip = val.nip || '';
    form.full_name = val.full_name || '';
    form.phone = val.phone || '';
    form.position = val.position || '';
    form.gender = val.gender || '';

    // Clear subjectAssignments
    Object.keys(subjectAssignments).forEach(k => delete subjectAssignments[k]);

    if (val.subject_classes?.length || val.subjectClasses?.length) {
      const list = val.subject_classes || val.subjectClasses || [];
      list.forEach(sc => {
        const subId = sc.subject_id;
        const clsId = sc.class_id;
        if (!subjectAssignments[subId]) subjectAssignments[subId] = [];
        if (clsId && !subjectAssignments[subId].includes(clsId)) {
          subjectAssignments[subId].push(clsId);
        }
      });
    } else if (val.subjects?.length) {
      val.subjects.forEach(s => {
        const allCls = (val.teaching_classes || val.teachingClasses || []).map(c => c.id);
        subjectAssignments[s.id] = [...allCls];
      });
    }

    if (val.photo_url) {
      photoPreview.value = val.photo_url;
    } else {
      photoPreview.value = null;
    }
    photoFile.value = null;
  },
  { immediate: true }
);

onMounted(async () => {
  try {
    const [subRes, classRes] = await Promise.all([
      api.get('admin/subjects'),
      api.get('admin/classes?per_page=999')
    ]);
    subjects.value = subRes.data?.data || subRes.data || [];
    classesList.value = classRes.data?.data || classRes.data || [];
  } catch {
    subjects.value = [];
    classesList.value = [];
  }
});

function onSubmit() {
  const isValid = validateForm();

  if (!isValid) {
    if (formRef.value) {
      formRef.value.scrollTop = 0;
    }
    return;
  }

  loading.value = true;

  const useFormData = !!photoFile.value;
  const payload = useFormData ? new FormData() : {};

  const appendData = (key, value) => {
    if (useFormData) {
      if (value !== null && value !== undefined) payload.append(key, value);
    } else {
      payload[key] = value;
    }
  };

  appendData('nip', form.nip);
  appendData('full_name', form.full_name);
  appendData('phone', form.phone);
  appendData('position', form.position);
  appendData('gender', form.gender);

  const assignmentsArray = Object.keys(subjectAssignments).map(subId => ({
    subject_id: Number(subId),
    class_ids: subjectAssignments[subId] || []
  }));

  if (useFormData) {
    payload.append('subject_assignments', JSON.stringify(assignmentsArray));
    payload.append('photo', photoFile.value);
  } else {
    payload.subject_assignments = assignmentsArray;
  }

  emit('save', { payload, isFormData: useFormData });
  loading.value = false;
}
</script>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-5px); }
  40%, 80% { transform: translateX(5px); }
}
.animate-shake {
  animation: shake 0.4s ease-in-out;
}
</style>
