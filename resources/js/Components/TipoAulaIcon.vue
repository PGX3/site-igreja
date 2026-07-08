<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" stroke="currentColor"
       stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <template v-if="tipo === 'material'">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
      <polyline points="14 2 14 8 20 8" />
    </template>
    <template v-else-if="tipo === 'atividade'">
      <path d="M9 11l3 3L22 4" />
      <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
    </template>
    <template v-else>
      <polygon points="6 3 20 12 6 21 6 3" fill="currentColor" stroke="none" />
    </template>
  </svg>
</template>

<script setup>
defineProps({
  tipo: { type: String, default: 'aula' },
  size: { type: [Number, String], default: 18 },
})
</script>
