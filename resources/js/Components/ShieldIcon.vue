<template>
  <svg :width="size" viewBox="0 0 300 340" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M150 8L16 52v110c0 75 56 143 134 160 78-17 134-85 134-160V52L150 8z"
      stroke="white" stroke-width="10" fill="none" stroke-linejoin="round"
    />
    <line x1="150" y1="75" x2="150" y2="200" stroke="white" stroke-width="18"/>
    <line x1="105" y1="125" x2="195" y2="125" stroke="white" stroke-width="18"/>
    <path d="M100 200 L150 240 L200 200" stroke="white" stroke-width="10" fill="none" stroke-linejoin="round"/>
    <circle cx="150" cy="264" r="22" fill="#29b6f6"/>
  </svg>
</template>

<script setup>
defineProps({
  size: { type: [Number, String], default: 200 },
})
</script>
