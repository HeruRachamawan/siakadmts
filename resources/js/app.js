import './bootstrap';
import 'aos/dist/aos.css';
import AOS from 'aos';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Provide a global event bus for settings updates
const eventBus = {
    listeners: [],
    emit(event, data) {
        this.listeners.forEach(listener => listener(event, data));
    },
    on(callback) {
        this.listeners.push(callback);
    }
};
app.provide('eventBus', eventBus);

try {
  app.mount('#app');
} catch (e) {
  console.error('Mount error:', e);
}

// Initialize animations after mount safely
try {
  if (typeof AOS !== 'undefined') {
    AOS.init({
      once: true,
      offset: 50,
      duration: 800,
      easing: 'ease-out-cubic',
    });
  }
} catch (e) {
  console.warn('AOS animation init skipped:', e);
}
