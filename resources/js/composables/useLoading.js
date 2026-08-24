import { ref } from 'vue';

const globalLoadingState = ref({
  show: false,
  message: 'Sedang Menyimpan Data...'
});

let activeCount = 0;

export function useLoading() {
  const startLoading = (message = 'Sedang Menyimpan & Memperbarui Data...') => {
    activeCount++;
    globalLoadingState.value = {
      show: true,
      message
    };
  };

  const stopLoading = () => {
    activeCount = Math.max(0, activeCount - 1);
    if (activeCount === 0) {
      globalLoadingState.value.show = false;
    }
  };

  return {
    globalLoadingState,
    startLoading,
    stopLoading
  };
}

export { globalLoadingState };
