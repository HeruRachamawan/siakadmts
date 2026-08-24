import { ref } from 'vue';

// Simple Toast Manager
const toastState = ref({
  show: false,
  message: '',
  type: 'info',
  duration: 3000
});

export function useToast() {
  const show = (message, type = 'info', duration = 3000) => {
    toastState.value = { show: true, message, type, duration };
    setTimeout(() => {
      toastState.value.show = false;
    }, duration);
  };

  return {
    toastState,
    success: (msg) => show(msg, 'success'),
    error: (msg) => show(msg, 'error'),
    warning: (msg) => show(msg, 'warning'),
    info: (msg) => show(msg, 'info'),
  };
}

export { toastState };
