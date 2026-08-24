import axios from 'axios';
import router from './router';
import { useToast } from './composables/useToast';
import { useLoading } from './composables/useLoading';

axios.defaults.baseURL = '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  // Trigger loading animation for mutating requests (POST, PUT, PATCH, DELETE)
  const method = (config.method || '').toUpperCase();
  if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method) && !config.hideLoading) {
    let msg = 'Sedang Menyimpan & Memperbarui Data...';
    if (method === 'DELETE') msg = 'Sedang Menghapus Data...';
    if (config.url?.includes('login')) msg = 'Memproses Masuk...';
    if (config.url?.includes('import')) msg = 'Mengimpor Data Excel...';
    if (config.url?.includes('password-reset')) msg = 'Memproses Permintaan Reset...';
    
    useLoading().startLoading(msg);
    config._showsLoading = true;
  }

  return config;
});

axios.interceptors.response.use(
  (response) => {
    if (response.config?._showsLoading) {
      useLoading().stopLoading();
    }
    return response;
  },
  (error) => {
    if (error.config?._showsLoading) {
      useLoading().stopLoading();
    }
    const { response } = error;

    if (!response) {
      useToast().error('Tidak dapat terhubung ke server. Pastikan jaringan Anda stabil.');
      console.error('Tidak dapat terhubung ke server.');
      return Promise.reject(error);
    }

    const { status, data } = response;

    if (status === 401) {
      localStorage.removeItem('token');
      delete axios.defaults.headers.common['Authorization'];
      const currentRoute = router.currentRoute.value;
      if (currentRoute.path !== '/login' && currentRoute.meta?.requiresAuth) {
        router.push('/login');
      }
      return Promise.reject(error);
    }

    if (status === 403) {
      useToast().error('Anda tidak memiliki izin untuk mengakses halaman ini.');
      console.error('Anda tidak memiliki izin untuk mengakses halaman ini.');
      return Promise.reject(error);
    }

    if (status === 422) {
      const message = data?.message || 'Validasi gagal';
      const errors = data?.errors || {};

      let toastMsg = message;
      if (errors.image && errors.image[0]) {
        toastMsg = errors.image[0].includes('must not be greater than')
          ? 'Ukuran gambar terlalu besar (Maksimal 10MB)'
          : errors.image[0];
      } else if (errors.photo && errors.photo[0]) {
        toastMsg = errors.photo[0].includes('must not be greater than')
          ? 'Ukuran foto terlalu besar (Maksimal 10MB)'
          : errors.photo[0];
      } else {
        const firstKey = Object.keys(errors)[0];
        if (firstKey && errors[firstKey][0]) {
          toastMsg = errors[firstKey][0];
        }
      }

      useToast().error(toastMsg);
      console.error('Validation errors:', message, errors);
      return Promise.reject(error);
    }

    if (status >= 500) {
      useToast().error('Terjadi kesalahan pada server. Silakan coba lagi.');
      console.error('Terjadi kesalahan pada server.');
      return Promise.reject(error);
    }

    const message = data?.message || 'Terjadi kesalahan tidak diketahui';
    console.error('API Error:', message);
    return Promise.reject(error);
  }
);

export const api = {
  get: (url, params) => axios.get(url, { params }).then((r) => r.data),
  post: (url, data) => axios.post(url, data).then((r) => r.data),
  put: (url, data) => axios.put(url, data).then((r) => r.data),
  del: (url) => axios.delete(url).then((r) => r.data),
  postForm: (url, data) => axios.post(url, data, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data),
  putForm: (url, data) => {
    // Laravel doesn't support PUT multipart, use POST with _method spoofing
    data.append('_method', 'PUT');
    return axios.post(url, data, { headers: { 'Content-Type': 'multipart/form-data' } }).then((r) => r.data);
  },
};

export default api;
