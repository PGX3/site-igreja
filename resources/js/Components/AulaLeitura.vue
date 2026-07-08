<template>
  <div>
    <Link v-if="voltarUrl" :href="voltarUrl"
          class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition mb-6">
      ← {{ cursoTitulo }}
    </Link>

    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full inline-flex items-center gap-1.5" :class="metaTipo(aula.tipo).badge">
        <TipoAulaIcon :tipo="aula.tipo" :size="13" />
        {{ metaTipo(aula.tipo).label }}
      </span>
      <span v-if="cursoTitulo" class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">
        {{ cursoTitulo }}
      </span>
    </div>
    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-white">{{ aula.titulo }}</h1>

    <!-- Vídeo (opcional) -->
    <div v-if="aula.youtube_url" class="mt-6">
      <MediaEmbed :url="aula.youtube_url" />
    </div>

    <!-- Conteúdo -->
    <ConteudoAula v-if="aula.conteudo" :html="aula.conteudo"
                  class="mt-8 text-gray-800 dark:text-slate-200" />
    <p v-else class="mt-8 text-gray-400 dark:text-slate-500 italic">Esta aula ainda não tem conteúdo.</p>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import ConteudoAula from '@/Components/ConteudoAula.vue'
import TipoAulaIcon from '@/Components/TipoAulaIcon.vue'
import { metaTipo } from '@/tipoAula.js'

defineProps({
  aula: { type: Object, required: true },
  cursoTitulo: { type: String, default: '' },
  voltarUrl: { type: String, default: null },
})
</script>
