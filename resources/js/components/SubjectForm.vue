<template>
  <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-xl max-h-full flex flex-col overflow-hidden">
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
        <div class="space-y-6">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Nama Mata Pelajaran</label>
            <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium" />
            <p v-if="form.name && !isValidName" class="text-[10px] font-bold text-red-500 mt-1">Nama mata pelajaran wajib diisi</p>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Kode</label>
            <div class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-600 font-mono select-none">
              {{ generatedCode || 'Otomatis dari nama' }}
            </div>
          </div>

          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide">Deskripsi <span class="text-slate-400 font-semibold normal-case">(Opsional)</span></label>
            <textarea v-model="form.description" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-400/30 focus:border-emerald-400 transition-all font-medium resize-none"></textarea>
          </div>
        </div>
      </form>

      <!-- Action Buttons -->
      <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex justify-end gap-3 z-10">
        <button @click="emit('close')" type="button" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors shadow-sm text-sm">
          Batal
        </button>
        <button type="button" @click="onSubmit" :disabled="loading || !isValidName" class="px-6 py-2.5 bg-[#111827] hover:bg-slate-800 text-white font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
          <svg v-if="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle>
          </svg>
          <span v-if="loading">Menyimpan...</span>
          <span v-else>Simpan Data Mapel</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  model: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close', 'save']);

const loading = ref(false);
const form = reactive({
  name: '',
  code: '',
  description: '',
});

const isValidName = computed(() => form.name.trim().length > 0);

const generatedCode = computed(() => {
  if (!form.name.trim()) return '';
  
  const words = form.name.trim().split(/\s+/).filter(Boolean);
  if (words.length === 0) return '';
  
  let initial = '';
  for (let i = 0; i < Math.min(words.length, 3); i++) {
    initial += words[i][0].toUpperCase();
  }
  
  if (!initial) return '';
  
  return `${initial}001`;
});

watch(
  () => props.model,
  (val) => {
    Object.assign(form, val);
  },
  { immediate: true }
);

function onSubmit() {
  if (!isValidName.value) return;
  loading.value = true;
  const payload = { ...form };
  delete payload.id;
  delete payload.code;
  emit('save', payload);
  loading.value = false;
}
</script>
