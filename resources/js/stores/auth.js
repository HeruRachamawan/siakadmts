import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    activeRole: localStorage.getItem('activeRole') || null,
    impersonatorToken: localStorage.getItem('impersonatorToken') || null,
    impersonatorUser: JSON.parse(localStorage.getItem('impersonatorUser') || 'null'),
    loading: true,
    initialized: false,
  }),

  getters: {
    role: (state) => state.activeRole || state.user?.role || null,
    primaryRole: (state) => state.user?.role || null,
    isAuthenticated: (state) => !!state.token && !!state.user,
    isImpersonating: (state) => !!state.impersonatorToken,
    isDualRole: (state) => {
      if (!state.user) return false;
      const hasTeacherProfile = !!(state.user.teacher_id || state.user.teacher);
      const isStaffRole = ['operator', 'kurikulum', 'admin'].includes(state.user.role);
      return hasTeacherProfile && isStaffRole;
    },
    availableRoles: (state) => {
      if (!state.user) return [];
      const roles = [state.user.role];
      if (state.user.teacher_id || state.user.teacher) {
        if (!roles.includes('teacher')) roles.push('teacher');
      }
      return roles;
    }
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

      if (user) {
        const saved = localStorage.getItem('activeRole');
        const validRoles = [user.role];
        if (user.teacher_id || user.teacher) validRoles.push('teacher');
        this.activeRole = saved && validRoles.includes(saved) ? saved : user.role;
        localStorage.setItem('activeRole', this.activeRole);
      } else {
        this.activeRole = null;
        localStorage.removeItem('activeRole');
      }
    },

    switchRole(targetRole) {
      if (!targetRole) return;
      this.activeRole = targetRole;
      localStorage.setItem('activeRole', targetRole);
    },

    startImpersonation(targetUser, targetToken) {
      this.impersonatorToken = this.token;
      this.impersonatorUser = this.user;
      localStorage.setItem('impersonatorToken', this.token);
      localStorage.setItem('impersonatorUser', JSON.stringify(this.user));

      this.setAuth(targetUser, targetToken);
      this.switchRole('teacher');
    },

    stopImpersonation() {
      if (!this.impersonatorToken) return;
      const adminUser = this.impersonatorUser;
      const adminToken = this.impersonatorToken;

      this.impersonatorToken = null;
      this.impersonatorUser = null;
      localStorage.removeItem('impersonatorToken');
      localStorage.removeItem('impersonatorUser');

      this.setAuth(adminUser, adminToken);
      this.switchRole(adminUser?.role || 'admin');
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
        if (this.user) {
          const saved = localStorage.getItem('activeRole');
          const validRoles = [this.user.role];
          if (this.user.teacher_id || this.user.teacher) validRoles.push('teacher');
          this.activeRole = saved && validRoles.includes(saved) ? saved : this.user.role;
          localStorage.setItem('activeRole', this.activeRole);
        }
      } catch (e) {
        this.setAuth(null, null);
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      await axios.post('/logout').catch(() => {});
      localStorage.removeItem('activeRole');
      localStorage.removeItem('impersonatorToken');
      localStorage.removeItem('impersonatorUser');
      this.impersonatorToken = null;
      this.impersonatorUser = null;
      this.setAuth(null, null);
    },
  },
});


