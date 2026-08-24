<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <div class="w-full max-w-2xl">
      <div class="bg-white rounded-2xl shadow-xl p-8 animate-fade-in">
        <div class="mb-8">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v6m-6-6v6m-6-6v6M9 5a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Akun Siswa</h1>
          </div>
          <p class="text-gray-500 text-sm">Isi formulir di bawah untuk mendaftar sebagai siswa baru</p>
        </div>

        <form @submit.prevent="onSubmit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
              <input v-model="form.name" type="text" class="form-input" />
            </div>
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">Username</label>
              <input v-model="form.username" type="text" placeholder="Contoh: siswa001" class="form-input" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input v-model="form.email" type="email" class="form-input" />
            </div>
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">Password</label>
              <input v-model="form.password" type="password" class="form-input" />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
            <input v-model="form.password_confirmation" type="password" class="form-input" />
          </div>

          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
            <select v-model="form.gender" class="form-input">
              <option value="">Pilih...</option>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
              <input v-model="form.birth_place" type="text" class="form-input" />
            </div>
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
              <input v-model="form.birth_date" type="date" class="form-input" />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">Alamat</label>
            <textarea v-model="form.address" rows="3" class="form-input"></textarea>
          </div>

          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">No. HP Orang Tua</label>
            <input v-model="form.parent_phone" type="text" class="form-input" />
          </div>

          <button
            :disabled="loading"
            class="w-full bg-blue-600 text-white font-semibold py-2.5 rounded-lg transition-colors flex items-center justify-center"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" stroke="currentColor" stroke-width="4" d="M4 12a8 8 0 1116 0 8 8 0 01-16 0m8-4v4l3 3m0-7l-3 3"></circle>
            </svg>
            <span v-if="loading">Memproses...</span>
            <span v-else>Daftar Sekarang</span>
          </button>
          <p v-if="error" class="text-red-600 text-sm bg-red-50 p-2 rounded-md">{{ error }}</p>
        </form>

        <p class="text-center mt-6 text-sm text-gray-600">
          Sudah punya akun?
          <RouterLink to="/login" class="text-blue-600 font-medium hover:underline">Masuk di sini</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const auth = useAuthStore();
const loading = ref(false);
const error = ref('');

const form = reactive({
  name: '',
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  full_name: '',
  gender: '',
  birth_place: '',
  birth_date: '',
  address: '',
  parent_phone: '',
});

async function onSubmit() {
  loading.value = true;
  error.value = '';
  try {
    await auth.register(form);
    router.replace('/dashboard');
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mendaftar';
  } finally {
    loading.value = false;
  }
}
</script>
