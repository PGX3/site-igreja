<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-2">
          Comunicações
        </p>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Sugestões
        </h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          {{ sugestoes.length }} recebida(s) · {{ naoLidas }} não lida(s)
        </p>
      </div>

      <!-- FILTROS -->
      <div class="flex gap-2 flex-wrap">
        <button
          @click="filtro = 'todas'"
          class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
          :class="filtro==='todas'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'border-gray-300 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
        >
          Todas
        </button>
        <button
          @click="filtro = 'novas'"
          class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
          :class="filtro==='novas'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'border-gray-300 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
        >
          Novas
        </button>
      </div>
    </div>

    <!-- EMPTY -->
    <div v-if="filtradas.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 text-sm">
      Nenhuma sugestão encontrada.
    </div>

    <!-- LISTA -->
    <div class="space-y-3">
      <div v-for="s in filtradas" :key="s.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">

        <!-- CONTEÚDO -->
        <div class="p-4 sm:p-6">
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="flex items-center gap-2 flex-wrap min-w-0">
              <span class="font-semibold text-gray-900 dark:text-white text-sm">{{ s.nome }}</span>
              <span v-if="!s.lida"
                    class="text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded flex-shrink-0">
                Nova
              </span>
            </div>
            <span class="text-xs text-gray-400 dark:text-slate-500 flex-shrink-0">{{ formatDate(s.created_at) }}</span>
          </div>

          <p v-if="s.email" class="text-xs text-gray-400 dark:text-slate-500 mb-3 font-mono">{{ s.email }}</p>

          <p class="text-gray-700 dark:text-slate-300 text-sm leading-relaxed border-l-4 border-blue-100 dark:border-blue-800 pl-4">
            {{ s.mensagem }}
          </p>
        </div>

        <!-- AÇÕES -->
        <div class="px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-slate-700/50 flex gap-2 justify-end">
          <button
            @click="marcarLida(s)"
            class="text-xs font-semibold px-3 py-2 rounded-lg border transition"
            :class="s.lida
              ? 'border-gray-300 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700'
              : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'"
          >
            {{ s.lida ? 'Reabrir' : 'Marcar lida' }}
          </button>
          <button
            @click="excluir(s)"
            class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:text-red-500 hover:border-red-300 dark:hover:border-red-700 transition"
          >
            Excluir
          </button>
        </div>

      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ sugestoes: Array })
const filtro = ref('todas')

const filtradas = computed(() =>
  filtro.value === 'novas'
    ? props.sugestoes.filter(s => !s.lida)
    : props.sugestoes
)

const naoLidas = computed(() =>
  props.sugestoes.filter(s => !s.lida).length
)

function formatDate(d) {
  return new Date(d).toLocaleString('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short'
  })
}

function marcarLida(s) {
  router.patch(`/admin/sugestoes/${s.id}/lida`, {}, { preserveScroll: true })
}

function excluir(s) {
  if (confirm('Excluir esta sugestão?')) {
    router.delete(`/admin/sugestoes/${s.id}`, { preserveScroll: true })
  }
}
</script>
