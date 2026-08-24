<template>
  <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl max-h-full flex flex-col overflow-hidden">
      <!-- Modal Header -->
      <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-white z-10">
        <h2 class="text-xl font-black text-slate-800 font-lexend uppercase tracking-wider">{{ title }}</h2>
        <button @click="emit('close')" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <form @submit.prevent="onSubmit" class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Nama Kelas</label>
            <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium" placeholder="contoh: Kelas 1A" />
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Tingkat</label>
            <input v-model="form.grade_level" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium" placeholder="contoh: 1, 2, 3" />
          </div>

          <div class="md:col-span-2 space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Tahun Ajaran</label>
            <select v-model="form.academic_year_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium">
              <option value="">Pilih Tahun Ajaran...</option>
              <option v-for="year in academicYears" :key="year.id" :value="year.id">
                {{ year.year }} - Semester {{ year.semester === 'odd' ? 'Ganjil' : 'Genap' }} {{ year.is_active ? '(Aktif Saat Ini)' : '' }}
              </option>
            </select>
          </div>

          <div class="md:col-span-2 p-5 bg-slate-50 border border-slate-100 rounded-2xl space-y-3">
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" v-model="hasHomeroom" class="w-4 h-4 rounded text-emerald-500 focus:ring-emerald-400 focus:ring-offset-0 bg-white border-slate-300" />
              <span class="text-sm font-bold text-slate-700">Pilih Wali Kelas</span>
            </label>

            <div v-if="hasHomeroom" class="space-y-1.5 pl-7 mt-2">
              <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Wali Kelas</label>
              <select v-model="form.homeroom_teacher_id" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium">
                <option value="">Kosongkan (opsional)</option>
                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                  {{ teacher.full_name || teacher.user?.name }}
                </option>
              </select>
            </div>
            
            <div v-else class="pl-7 mt-2">
              <p class="text-[11px] font-semibold text-slate-400">Kelas ini tidak memiliki wali kelas.</p>
            </div>
          </div>
        </div>
      </form>

      <!-- Action Buttons -->
      <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 z-10">
        <button @click="emit('close')" type="button" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors shadow-sm text-sm">
          Batal
        </button>
        <button type="button" @click="onSubmit" :disabled="loading || !formValid" class="px-6 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
          <svg v-if="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle>
          </svg>
          <span v-if="loading">Menyimpan...</span>
          <span v-else>Simpan Data Kelas</span>
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
const academicYears = ref([]);
const teachers = ref([]);
const hasHomeroom = ref(true);

const form = reactive({
  name: '',
  grade_level: '',
  academic_year_id: '',
  homeroom_teacher_id: '',
});

const formValid = computed(() => {
  return form.name.trim().length > 0 && form.grade_level.trim().length > 0 && form.academic_year_id;
});

watch(
  () => props.model,
  (val) => {
    form.name = val.name || '';
    form.grade_level = val.grade_level || '';
    form.academic_year_id = val.academic_year?.id || val.academic_year_id || '';
    form.homeroom_teacher_id = val.homeroom_teacher_id || '';
    hasHomeroom.value = !!val.homeroom_teacher_id;
  },
  { immediate: true }
);

onMounted(async () => {
  try {
    const res = await api.get('admin/academic-years?per_page=999');
    const rawYears = res.data?.data || res.data || [];
    academicYears.value = Array.isArray(rawYears) ? rawYears : rawYears.data || [];
    
    // Auto select active academic year if creating new class
    if (!form.academic_year_id && academicYears.value.length) {
      const activeYear = academicYears.value.find(y => y.is_active);
      if (activeYear) {
        form.academic_year_id = activeYear.id;
      }
    }
  } catch {
    academicYears.value = [];
  }

  try {
    const res = await api.get('admin/teachers');
    teachers.value = res.data?.data || res.data || [];
  } catch {
    teachers.value = [];
  }
});

function onSubmit() {
  if (!formValid.value) return;
  loading.value = true;

  const payload = {
    name: form.name,
    grade_level: form.grade_level,
    academic_year_id: form.academic_year_id,
    homeroom_teacher_id: hasHomeroom.value ? form.homeroom_teacher_id : null,
  };

  emit('save', payload);
  loading.value = false;
}
</script>
