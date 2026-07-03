<template>
  <div v-if="embed">
    <!-- YouTube (16:9) -->
    <div v-if="embed.tipo === 'youtube'" class="relative w-full rounded-lg overflow-hidden bg-black" style="aspect-ratio: 16 / 9;">
      <iframe :src="embed.src" class="absolute inset-0 w-full h-full" frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </div>
    <!-- Spotify -->
    <iframe v-else-if="embed.tipo === 'spotify'" :src="embed.src"
            class="w-full rounded-xl" :height="embed.altura" frameborder="0"
            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
            loading="lazy"></iframe>
  </div>
  <a v-else-if="url" :href="url" target="_blank" rel="noopener"
     class="text-sm text-blue-600 dark:text-blue-400 hover:underline break-all">{{ url }}</a>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  url: { type: String, default: '' },
})

const embed = computed(() => {
  const url = (props.url || '').trim()
  if (!url) return null

  // YouTube
  const yt = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([\w-]{11})/i)
  if (yt) {
    return { tipo: 'youtube', src: `https://www.youtube.com/embed/${yt[1]}` }
  }

  // Spotify
  const sp = url.match(/open\.spotify\.com\/(?:intl-[a-z]+\/)?(track|album|playlist|episode|show|artist)\/([\w]+)/i)
  if (sp) {
    const tipo = sp[1].toLowerCase()
    const altura = tipo === 'track' || tipo === 'episode' ? 152 : 352
    return { tipo: 'spotify', src: `https://open.spotify.com/embed/${tipo}/${sp[2]}`, altura }
  }

  return null
})
</script>
