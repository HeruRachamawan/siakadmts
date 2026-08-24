<template>
  <div class="overflow-x-auto w-full animate-pulse">
    <table class="w-full text-left">
      <thead>
        <tr class="border-b border-slate-100">
          <th v-for="i in columns" :key="i" class="px-6 py-4">
            <div class="h-3 bg-slate-200 rounded-full w-24"></div>
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        <tr v-for="row in rows" :key="row">
          <td v-for="col in columns" :key="col" class="px-6 py-4">
            <div class="flex items-center gap-3">
              <!-- Randomly show a circular skeleton for avatars on the second column to look more realistic if needed -->
              <div v-if="col === 2 && avatar" class="w-10 h-10 rounded-full bg-slate-200 flex-shrink-0"></div>
              <div class="h-4 bg-slate-100 rounded-md w-full max-w-[150px]"></div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  columns: {
    type: Number,
    default: 5
  },
  rows: {
    type: Number,
    default: 5
  },
  avatar: {
    type: Boolean,
    default: false
  }
})
</script>
