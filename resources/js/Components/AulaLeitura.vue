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

    <!-- Atividade: prazo e pontos -->
    <div v-if="aula.tipo === 'atividade' && (aula.data_entrega || aula.pontos)" class="mt-3 flex flex-wrap items-center gap-2">
      <span v-if="aula.data_entrega"
            class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 px-3 py-1 rounded-full">
        📅 Entregar até {{ aula.data_entrega }}
      </span>
      <span v-if="aula.pontos"
            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 px-3 py-1 rounded-full">
        ⭐ {{ aula.pontos }} pontos
      </span>
    </div>

    <!-- Vídeo (opcional) -->
    <div v-if="aula.youtube_url" class="mt-6">
      <MediaEmbed :url="aula.youtube_url" />
    </div>

    <!-- Conteúdo -->
    <ConteudoAula v-if="aula.conteudo" :html="aula.conteudo"
                  class="mt-8 text-gray-800 dark:text-slate-200" />
    <p v-else class="mt-8 text-gray-400 dark:text-slate-500 italic">Esta aula ainda não tem conteúdo.</p>

    <!-- Materiais / anexos -->
    <div v-if="aula.anexos && aula.anexos.length" class="mt-10">
      <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-3">Materiais</p>
      <div class="space-y-2">
        <a v-for="anexo in aula.anexos" :key="anexo.id" :href="anexo.url" target="_blank" rel="noopener"
           class="flex items-center gap-3 border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 bg-white dark:bg-slate-800 hover:border-blue-300 dark:hover:border-blue-500/50 hover:shadow-sm transition group">
          <span class="w-9 h-9 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-slate-700 flex items-center justify-center text-lg">
            {{ anexo.tipo === 'link' ? '🔗' : '📄' }}
          </span>
          <span class="flex-1 text-sm font-medium text-gray-800 dark:text-slate-100 truncate group-hover:text-blue-700 dark:group-hover:text-blue-400">
            {{ anexo.titulo }}
          </span>
          <span class="text-xs text-gray-400 dark:text-slate-500">{{ anexo.tipo === 'link' ? 'Abrir' : 'Baixar' }}</span>
        </a>
      </div>
    </div>
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
