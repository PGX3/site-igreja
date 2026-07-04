<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
      <div class="min-w-0">
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
          <Link href="/admin/musicas" class="hover:text-blue-600 dark:hover:text-blue-400">Músicas</Link>
          <span class="mx-1">›</span>
          Detalhe
        </p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ musica.nome }}</h1>
        <div class="flex items-center gap-2 flex-wrap mt-2">
          <span v-if="musica.tom" class="bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs font-bold px-2.5 py-1 rounded-full">
            Tom: {{ musica.tom }}
          </span>
          <span class="text-xs text-gray-400 dark:text-slate-500">
            <template v-if="musica.vezes">Tocada {{ musica.vezes }}x · última: {{ musica.ultima }}</template>
            <template v-else>Nunca tocada</template>
          </span>
        </div>
      </div>

      <div class="flex items-center gap-2 flex-shrink-0">
        <button @click="apresentar = true"
                class="text-sm font-semibold text-gray-900 dark:text-white bg-gray-900/5 dark:bg-white/10 hover:bg-gray-900/10 dark:hover:bg-white/20 px-4 py-2.5 rounded-lg transition">
          📱 Ver no celular
        </button>
        <a :href="`/admin/musicas/${musica.id}/imprimir`" target="_blank" rel="noopener"
           class="border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-4 py-2.5 rounded-lg text-sm font-semibold transition">
          🖨 PDF
        </a>
        <Link :href="`/admin/musicas/${musica.id}/edit`"
              class="border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-4 py-2.5 rounded-lg text-sm font-semibold transition">
          Editar
        </Link>
      </div>
    </div>

    <!-- PLAYER -->
    <div v-if="musica.link" class="mb-6 max-w-xl">
      <MediaEmbed :url="musica.link" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- LETRA -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-6">
        <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-3">Letra</p>
        <div class="letra text-gray-800 dark:text-slate-200 leading-relaxed" v-html="musica.letra"></div>
      </div>

      <!-- HISTÓRICO -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden self-start">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
          <p class="font-semibold text-gray-900 dark:text-white text-sm">Histórico</p>
          <span class="text-xs text-gray-400 dark:text-slate-500">{{ historico.length }}x</span>
        </div>
        <div v-if="historico.length" class="divide-y divide-gray-50 dark:divide-slate-700/50 max-h-[420px] overflow-y-auto">
          <Link v-for="(h, i) in historico" :key="i" :href="`/admin/escalas/${h.escala_id}`"
                class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ h.titulo }}</p>
              <p class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ h.data }}<span v-if="h.grupo"> · {{ h.grupo }}</span></p>
            </div>
            <span v-if="h.tom" class="flex-shrink-0 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ h.tom }}</span>
          </Link>
        </div>
        <div v-else class="py-10 text-center text-gray-400 dark:text-slate-500 text-sm px-4">
          Ainda não foi tocada em nenhuma escala.
        </div>
      </div>
    </div>

    <!-- APRESENTAÇÃO -->
    <SetlistApresentacao v-if="apresentar"
                         :musicas="[{ nome: musica.nome, tom: musica.tom, letra: musica.letra, link: musica.link }]"
                         @close="apresentar = false" />

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import SetlistApresentacao from '@/Components/SetlistApresentacao.vue'
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  musica: Object,
  historico: { type: Array, default: () => [] },
})

const apresentar = ref(false)
</script>

<style scoped>
.letra :deep(p) { margin: 0; }
.letra :deep(p:empty)::before { content: "\00a0"; }
.letra :deep(strong) { font-weight: 800; }
</style>
