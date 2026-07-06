<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pregações</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Publique as pregações do YouTube exibidas no site.
        </p>
      </div>
      <Link href="/admin/pregacoes/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
        + Nova Pregação
      </Link>
    </div>

    <!-- EMPTY STATE -->
    <div v-if="pregacoes.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="text-4xl mb-3">🎥</p>
      <p class="font-medium">Nenhuma pregação cadastrada.</p>
    </div>

    <!-- CARDS (mobile) -->
    <div class="sm:hidden space-y-3">
      <div v-for="pregacao in pregacoes" :key="pregacao.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4">
          <div class="flex items-start justify-between gap-2 mb-1">
            <p class="font-semibold text-gray-900 dark:text-white">{{ pregacao.titulo }}</p>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0"
                  :class="pregacao.ativo
                    ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                    : 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400'">
              {{ pregacao.ativo ? 'Ativa' : 'Inativa' }}
            </span>
          </div>
          <p class="text-xs text-gray-500 dark:text-slate-400">
            {{ formatarData(pregacao.data_pregacao) }}<span v-if="pregacao.pregador"> · {{ pregacao.pregador }}</span>
          </p>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-700/50 flex gap-2 justify-end">
          <Link :href="`/admin/pregacoes/${pregacao.id}/edit`"
                class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
            Editar
          </Link>
          <button @click="destroy(pregacao.id)"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
            Excluir
          </button>
        </div>
      </div>
    </div>

    <!-- TABELA (desktop) -->
    <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <!-- HEAD -->
        <div class="grid grid-cols-4 px-6 py-3 bg-gray-50 dark:bg-slate-700/50 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider min-w-[480px]">
          <span>Título</span>
          <span>Data</span>
          <span>Status</span>
          <span class="text-right">Ações</span>
        </div>
        <!-- ROWS -->
        <div v-for="pregacao in pregacoes" :key="pregacao.id"
             class="grid grid-cols-4 items-center px-6 py-4 border-t border-gray-100 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition min-w-[480px]">
          <div>
            <p class="font-semibold text-gray-900 dark:text-white">{{ pregacao.titulo }}</p>
            <p v-if="pregacao.pregador" class="text-xs text-gray-500 dark:text-slate-400">{{ pregacao.pregador }}</p>
          </div>
          <div class="text-sm text-gray-600 dark:text-slate-300">{{ formatarData(pregacao.data_pregacao) }}</div>
          <div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                  :class="pregacao.ativo
                    ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                    : 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400'">
              {{ pregacao.ativo ? 'Ativa' : 'Inativa' }}
            </span>
          </div>
          <div class="flex justify-end gap-4 text-sm">
            <Link :href="`/admin/pregacoes/${pregacao.id}/edit`"
                  class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
              Editar
            </Link>
            <button @click="destroy(pregacao.id)"
                    class="text-gray-400 dark:text-slate-500 hover:text-red-500 transition">
              Excluir
            </button>
          </div>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({ pregacoes: Array })

function formatarData(data) {
  if (!data) return ''
  const d = new Date(data)
  if (isNaN(d)) return ''
  return d.toLocaleDateString('pt-BR', {
    day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC',
  })
}

function destroy(id) {
  if (confirm('Remover esta pregação?')) {
    router.delete(`/admin/pregacoes/${id}`)
  }
}
</script>
