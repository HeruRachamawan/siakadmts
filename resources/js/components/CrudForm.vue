<template>
  <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4 sm:p-6 font-inter" @click.self="emit('close')">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-xl max-h-[90vh] flex flex-col overflow-hidden animate-slide-up" @click.stop>
      <!-- Modal Header -->
      <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white z-10">
        <div>
          <h2 class="text-base font-bold text-slate-900 tracking-tight">{{ title }}</h2>
          <p class="text-xs text-slate-500 font-normal mt-0.5">Lengkapi formulir di bawah ini dengan benar.</p>
        </div>
        <button @click="emit('close')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
          <X class="w-4 h-4" />
        </button>
      </div>

      <form @submit.prevent="onSubmit" class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
        <div
          v-for="field in fields"
          :key="field.name"
          class="space-y-1.5"
        >
          <label class="block text-xs font-semibold text-slate-700">{{ field.label }}</label>

          <select
            v-if="field.type === 'select'"
            v-model="form[field.name]"
            class="form-input font-normal"
          >
            <option value="">Pilih...</option>
            <option v-for="opt in field.options" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>

          <select
            v-else-if="field.type === 'select-endpoint'"
            v-model="form[field.name]"
            class="form-input font-normal"
          >
            <option value="">Pilih...</option>
            <option v-for="opt in endpointOptions(field.endpoint, field.valueField, field.labelField)" :key="opt.id" :value="opt.id">
              {{ opt[field.labelField] }}
            </option>
          </select>

          <textarea
            v-else-if="field.type === 'textarea'"
            v-model="form[field.name]"
            class="form-input h-24 resize-none leading-relaxed"
            rows="3"
          ></textarea>

          <input
            v-else-if="field.type === 'checkbox'"
            type="checkbox"
            v-model="form[field.name]"
            class="w-4 h-4 text-slate-900 bg-white border-slate-300 rounded focus:ring-slate-900 cursor-pointer"
          />

          <div v-else-if="field.type === 'file'" class="space-y-2">
            <div class="flex items-center gap-3">
              <input
                :ref="el => setFileInputRef(el, field.name)"
                type="file"
                class="hidden"
                :accept="field.accept || '*'"
                @change="onFileChange(field.name, $event)"
              />
              <button
                type="button"
                @click="fileInputRefs[field.name]?.click()"
                class="btn btn-outline text-xs cursor-pointer"
              >
                <Upload class="w-3.5 h-3.5 text-slate-600" />
                <span>Pilih File</span>
              </button>
              <span v-if="fileFiles[field.name]" class="text-xs text-slate-600 truncate max-w-[200px]">
                {{ fileFiles[field.name].name }}
              </span>
            </div>
            
            <div v-if="filePreviews[field.name] || form[field.name]" class="w-16 h-16 rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
              <img :src="filePreviews[field.name] || form[field.name]" class="w-full h-full object-cover" />
            </div>
          </div>

          <input
            v-else
            v-model="form[field.name]"
            :type="field.type || 'text'"
            class="form-input"
          />
        </div>
      </form>

      <!-- Action Buttons -->
      <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/75 flex justify-end gap-2.5 z-10">
        <button @click="emit('close')" type="button" :disabled="saving" class="btn btn-secondary text-xs cursor-pointer">
          Batal
        </button>
        <button type="button" :disabled="saving" @click="onSubmit" class="btn-primary text-xs cursor-pointer flex items-center gap-2">
          <div v-if="saving" class="animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></div>
          <span>{{ saving ? 'Menyimpan...' : 'Simpan Data' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { api } from '../api';
import { X, Upload } from 'lucide-vue-next';

const props = defineProps({
  title: { type: String, required: true },
  fields: { type: Array, required: true },
  model: { type: Object, default: () => {} },
  saving: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'save']);

const form = reactive({});
const fileFiles = ref({});
const filePreviews = ref({});
const fileInputRefs = ref({});
const endpointCache = {};

function setFileInputRef(el, fieldName) {
  if (el) {
    fileInputRefs.value[fieldName] = el;
  }
}

Object.keys(props.model).forEach((k) => {
  form[k] = props.model[k];
});

function onFileChange(fieldName, event) {
  const file = event.target.files[0];
  if (file) {
    fileFiles.value[fieldName] = file;
    filePreviews.value[fieldName] = URL.createObjectURL(file);
  }
}

const endpointData = reactive({});

function endpointOptions(endpoint, valueField, labelField) {
  if (!endpointData[endpoint]) {
    endpointData[endpoint] = [];
    api.get(endpoint).then((res) => {
      endpointData[endpoint] = res.data?.data || res.data || [];
    });
  }
  return endpointData[endpoint];
}

function onSubmit() {
  const hasFiles = Object.keys(fileFiles.value).length > 0;
  if (hasFiles) {
    const dataToSend = new FormData();
    Object.keys(form).forEach((k) => {
      if (form[k] !== undefined && form[k] !== null) {
        dataToSend.append(k, form[k]);
      }
    });
    Object.keys(fileFiles.value).forEach((k) => {
      dataToSend.append(k, fileFiles.value[k]);
    });
    emit('save', dataToSend, true);
  } else {
    emit('save', form, false);
  }
}
</script>
