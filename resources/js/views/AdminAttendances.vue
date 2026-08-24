<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Rekap Kehadiran</h1>
    <div v-if="loading" class="text-center py-8">Memuat...</div>
    <table v-else class="w-full border-collapse">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 text-left">Siswa</th>
          <th class="p-2 text-left">Kelas</th>
          <th class="p-2 text-left">Tanggal</th>
          <th class="p-2 text-left">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="att in attendances" :key="att.id" class="border-b">
          <td class="p-2">{{ att.student?.full_name || att.student_id }}</td>
          <td class="p-2">{{ att.class?.name || att.class_id }}</td>
          <td class="p-2">{{ att.date }}</td>
          <td class="p-2">{{ att.status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { api } from '../api';

const loading = ref(true);
const attendances = ref([]);

onMounted(async () => {
  try {
    const res = await api.get('admin/attendances');
    attendances.value = res.data || [];
  } catch {
    attendances.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
