<template>
  <Transition name="loading-fade">
    <div
      v-if="globalLoadingState.show"
      class="fixed inset-0 z-[99999] bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 select-none font-inter"
    >
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xl p-6 sm:p-7 max-w-xs w-full flex flex-col items-center text-center space-y-4 relative overflow-hidden animate-slide-up">
        
        <!-- Clean Circular Spinner -->
        <div class="relative w-12 h-12 flex items-center justify-center">
          <div class="w-10 h-10 rounded-full border-3 border-slate-100 border-t-emerald-600 border-r-emerald-600 animate-spin"></div>
        </div>

        <!-- Clean Title Message Only -->
        <div class="space-y-1">
          <h3 class="text-sm font-bold text-slate-900 tracking-tight">
            {{ globalLoadingState.message || 'Memproses Data...' }}
          </h3>
          <p class="text-xs text-slate-500 font-normal">
            Mohon tunggu sebentar...
          </p>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup>
import { globalLoadingState } from '../composables/useLoading';
</script>

<style scoped>
.loading-fade-enter-active,
.loading-fade-leave-active {
  transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.loading-fade-enter-from,
.loading-fade-leave-to {
  opacity: 0;
}
</style>
