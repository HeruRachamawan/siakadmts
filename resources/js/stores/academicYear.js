import { defineStore } from 'pinia';
import axios from 'axios';

export const useAcademicYearStore = defineStore('academicYear', {
  state: () => ({
    years: [],
    selectedId: localStorage.getItem('selectedAcademicYearId') 
      ? parseInt(localStorage.getItem('selectedAcademicYearId'), 10) 
      : null,
    selectedLabel: localStorage.getItem('selectedAcademicYearLabel') || null,
    activeId: null,
    activeLabel: null,
    loading: false,
    initialized: false,
  }),

  getters: {
    isViewingArchive: (state) => {
      if (!state.selectedId || !state.activeId) return false;
      return state.selectedId !== state.activeId;
    },
    currentYearObject: (state) => {
      return state.years.find((y) => y.id === state.selectedId) || null;
    },
    displayName: (state) => {
      if (state.selectedLabel) return state.selectedLabel;
      if (state.activeLabel) return state.activeLabel;
      return 'Tahun Ajaran';
    },
  },

  actions: {
    async fetchOptions() {
      if (this.loading) return;
      this.loading = true;
      try {
        // Try public endpoint first, fallback to admin
        let res;
        try {
          res = await axios.get('/public/academic-years');
        } catch {
          res = await axios.get('/admin/academic-years/options');
        }

        const data = res.data?.data;
        if (data) {
          this.years = data.years || [];
          this.activeId = data.active_id || null;
          this.activeLabel = data.active_label || null;

          // If no selectedId in localStorage or not in list, fallback to activeId
          const exists = this.years.some((y) => y.id === this.selectedId);
          if (!this.selectedId || !exists) {
            this.selectedId = this.activeId;
            const activeYear = this.years.find((y) => y.id === this.activeId);
            this.selectedLabel = activeYear ? activeYear.label.replace(' (Aktif)', '') : this.activeLabel;
            if (this.selectedId) {
              localStorage.setItem('selectedAcademicYearId', this.selectedId);
              localStorage.setItem('selectedAcademicYearLabel', this.selectedLabel || '');
            }
          } else {
            const currentYear = this.years.find((y) => y.id === this.selectedId);
            if (currentYear) {
              this.selectedLabel = currentYear.label.replace(' (Aktif)', '');
              localStorage.setItem('selectedAcademicYearLabel', this.selectedLabel);
            }
          }
        }
      } catch (err) {
        console.warn('Gagal memuat daftar tahun ajaran:', err);
      } finally {
        this.loading = false;
        this.initialized = true;
      }
    },

    setAcademicYear(yearId) {
      const year = this.years.find((y) => y.id === yearId);
      if (!year) return;

      this.selectedId = year.id;
      this.selectedLabel = year.label.replace(' (Aktif)', '');
      localStorage.setItem('selectedAcademicYearId', this.selectedId);
      localStorage.setItem('selectedAcademicYearLabel', this.selectedLabel);

      // Trigger global event so active views can react
      window.dispatchEvent(new CustomEvent('academic-year-changed', {
        detail: {
          academic_year_id: this.selectedId,
          label: this.selectedLabel,
          is_active: this.selectedId === this.activeId,
        }
      }));
    },

    resetToActive() {
      if (this.activeId) {
        this.setAcademicYear(this.activeId);
      }
    }
  },
});
