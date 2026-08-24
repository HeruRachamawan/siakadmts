<template>
  <Transition
    enter-active-class="transform ease-out duration-300 transition"
    enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
    leave-active-class="transition ease-in duration-100"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div 
      v-if="visible" 
      class="fixed bottom-4 right-4 sm:top-4 sm:bottom-auto z-[99999] max-w-sm w-full bg-white rounded-xl border border-slate-200 shadow-lg p-4 font-inter select-none"
    >
      <div class="flex items-start gap-3">
        <!-- Icon -->
        <div class="flex-shrink-0 mt-0.5">
          <CheckCircle2 v-if="type === 'success'" class="w-5 h-5 text-emerald-600" />
          <AlertCircle v-else-if="type === 'error'" class="w-5 h-5 text-rose-600" />
          <AlertTriangle v-else-if="type === 'warning'" class="w-5 h-5 text-amber-600" />
          <Info v-else class="w-5 h-5 text-teal-600" />
        </div>
        
        <!-- Content -->
        <div class="flex-1 space-y-0.5">
          <p class="text-xs font-semibold text-slate-900 leading-snug">{{ message }}</p>
          <p v-if="description" class="text-[11px] text-slate-500 font-normal">{{ description }}</p>
        </div>

        <!-- Close Button -->
        <button 
          @click="visible = false"
          class="text-slate-400 hover:text-slate-600 transition-colors p-0.5 cursor-pointer -mr-1 -mt-1"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, watch } from 'vue';
import {
  CheckCircle2,
  AlertCircle,
  AlertTriangle,
  Info,
  X
} from 'lucide-vue-next';

const props = defineProps({
  message: { type: String, required: true },
  description: { type: String, default: '' },
  type: { type: String, default: 'info', validator: (v) => ['success', 'error', 'warning', 'info'].includes(v) },
  duration: { type: Number, default: 4000 },
  modelValue: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue']);

const visible = ref(props.modelValue);

watch(() => props.modelValue, (val) => {
  visible.value = val;
  if (val) {
    setTimeout(() => {
      visible.value = false;
      emit('update:modelValue', false);
    }, props.duration);
  }
});
</script>
