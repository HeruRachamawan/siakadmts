<template>
  <Transition name="fade">
    <div v-if="confirmState.show" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[100] flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 relative overflow-hidden border border-slate-200 font-inter transform transition-all animate-slide-up">
        
        <!-- Top Icon Indicator & Content -->
        <div class="flex items-start gap-4">
          <div
            :class="[
              confirmState.type === 'danger' ? 'bg-rose-50 text-rose-600 border-rose-100' : 
              confirmState.type === 'warning' ? 'bg-amber-50 text-amber-600 border-amber-100' : 
              'bg-emerald-50 text-emerald-600 border-emerald-100',
              'w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 border shadow-2xs'
            ]"
          >
            <!-- Danger Icon -->
            <Trash2 v-if="confirmState.type === 'danger'" class="w-5 h-5" />
            <!-- Warning Icon -->
            <AlertTriangle v-else-if="confirmState.type === 'warning'" class="w-5 h-5" />
            <!-- Info Icon -->
            <Info v-else class="w-5 h-5" />
          </div>

          <div class="space-y-1">
            <h3 class="text-base font-bold text-slate-900 tracking-tight">
              {{ confirmState.title }}
            </h3>
            <p class="text-xs text-slate-500 font-normal leading-relaxed">
              {{ confirmState.message }}
            </p>
          </div>
        </div>

        <!-- Action Buttons (Shadcn Button Variants) -->
        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
          <button
            @click="handleCancel"
            type="button"
            class="btn btn-secondary text-xs cursor-pointer"
          >
            {{ confirmState.cancelText }}
          </button>
          <button
            @click="handleConfirm"
            type="button"
            :class="[
              confirmState.type === 'danger' ? 'btn-danger' : 
              confirmState.type === 'warning' ? 'btn bg-amber-600 hover:bg-amber-700 text-white shadow-xs' : 
              'btn-accent'
            ]"
            class="text-xs cursor-pointer"
          >
            {{ confirmState.confirmText }}
          </button>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useConfirm } from '../composables/useConfirm';
import {
  Trash2,
  AlertTriangle,
  Info
} from 'lucide-vue-next';

const { confirmState, handleConfirm, handleCancel } = useConfirm();
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
