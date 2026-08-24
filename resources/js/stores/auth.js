import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: true,
    initialized: false,
  }),

  getters: {
    role: (state) => state.user?.role || null,
    isAuthenticated: (state) => !!state.token && !!state.user,
  },

  actions: {
    setAuth(user, token) {
      this.user = user;
      this.token = token;

      if (token) {
        localStorage.setItem('token', token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      } else {
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
      }
    },

    async login(username, password) {
      const { data } = await axios.post('/login', { username, password });
      this.setAuth(data.data.user, data.data.token);
      return data;
    },

    async register(payload) {
      const { data } = await axios.post('/register', payload);
      this.setAuth(data.data.user, data.data.token);
      return data;
    },

    async fetchMe() {
      if (this.initialized) return;
      this.initialized = true;

      if (!this.token) {
        this.loading = false;
        return;
      }

      try {
        const { data } = await axios.get('/user');
        this.user = data.data;
      } catch (e) {
        this.setAuth(null, null);
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      await axios.post('/logout').catch(() => {});
      this.setAuth(null, null);
    },
  },
});
