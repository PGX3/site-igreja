<template>
  <div>
    <!-- BANNER (estilo Classroom) -->
    <div class="relative rounded-2xl overflow-hidden h-44 sm:h-52 flex items-end">
      <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo"
           class="absolute inset-0 w-full h-full object-cover" />
      <div class="absolute inset-0"
           :class="curso.capa_url
             ? 'bg-gradient-to-t from-black/75 via-black/30 to-black/10'
             : 'bg-gradient-to-br from-blue-600 to-indigo-700'"></div>
      <div class="relative p-6 sm:p-8">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70 mb-1">Curso</p>
        <h1 class="text-2xl sm:text-4xl font-bold text-white leading-tight">{{ curso.titulo }}</h1>
        <p class="text-xs text-white/70 mt-2">
          {{ totalAulas }} {{ totalAulas === 1 ? 'aula' : 'aulas' }} · {{ curso.modulos.length }}
          {{ curso.modulos.length === 1 ? 'módulo' : 'módulos' }}
        </p>
      </div>
    </div>

    <!-- SOBRE -->
    <div v-if="curso.descricao"
         class="mt-5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5">
      <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-2">Sobre o curso</p>
      <ConteudoAula :html="curso.descricao" class="text-gray-600 dark:text-slate-300" />
    </div>

    <!-- MÓDULOS (tópicos) -->
    <div class="mt-8 space-y-9">
      <section v-for="(modulo, mi) in curso.modulos" :key="modulo.id">
        <!-- Cabeçalho do tópico -->
        <div class="flex items-baseline justify-between gap-3 pb-2.5 border-b border-gray-200 dark:border-slate-700">
          <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ modulo.titulo }}</h2>
          <span class="text-xs font-semibold text-gray-400 dark:text-slate-500 flex-shrink-0">Módulo {{ mi + 1 }}</span>
        </div>
        <p v-if="modulo.descricao" class="text-sm text-gray-500 dark:text-slate-400 mt-2">{{ modulo.descricao }}</p>

        <!-- Aulas -->
        <div class="mt-4 space-y-2.5">
          <Link v-for="aula in modulo.aulas" :key="aula.id" :href="aula.url"
                class="flex items-center gap-4 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700
                       rounded-xl px-4 py-3.5 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-500/50 transition group">
            <!-- Ícone do tipo -->
            <span class="w-11 h-11 flex-shrink-0 rounded-full flex items-center justify-center" :class="metaTipo(aula.tipo).ring">
              <TipoAulaIcon :tipo="aula.tipo" :size="20" />
            </span>
            <!-- Texto -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[11px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full" :class="metaTipo(aula.tipo).badge">
                  {{ metaTipo(aula.tipo).label }}
                </span>
                <span v-if="aula.tem_video"
                      class="text-[11px] font-semibold text-gray-500 dark:text-slate-400 inline-flex items-center gap-1">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3" /></svg>
                  vídeo
                </span>
                <span v-if="aula.oculta"
                      class="text-[10px] font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">oculta</span>
              </div>
              <p class="font-semibold text-gray-900 dark:text-white truncate mt-0.5 group-hover:text-blue-700 dark:group-hover:text-blue-400">
                {{ aula.titulo }}
              </p>
              <p v-if="aula.resumo" class="text-sm text-gray-500 dark:text-slate-400 truncate mt-0.5">{{ aula.resumo }}</p>
            </div>
            <span class="text-gray-300 dark:text-slate-500 group-hover:text-blue-500 flex-shrink-0 text-lg">→</span>
          </Link>

          <p v-if="!modulo.aulas.length" class="text-sm text-gray-400 dark:text-slate-500 italic px-1">
            Nenhuma aula publicada ainda.
          </p>
        </div>
      </section>
    </div>

    <div v-if="!curso.modulos.length" class="mt-8 text-center py-12 text-gray-400 dark:text-slate-500">
      Este curso ainda não tem conteúdo.
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import ConteudoAula from '@/Components/ConteudoAula.vue'
import TipoAulaIcon from '@/Components/TipoAulaIcon.vue'
import { metaTipo } from '@/tipoAula.js'

const props = defineProps({
  curso: { type: Object, required: true },
})

const totalAulas = computed(() =>
  props.curso.modulos.reduce((n, m) => n + m.aulas.length, 0),
)
</script>
