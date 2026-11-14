<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Roteiros de pregação</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Escreva e formate sua pregação aqui. No culto, ela aparece no iPad do púlpito.
        </p>
      </div>
      <button type="button" @click="novo" :disabled="criando"
              class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
        + Novo roteiro
      </button>
    </div>

    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- EMPTY STATE -->
    <div v-if="roteiros.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="font-medium">Nenhum roteiro ainda.</p>
      <p class="text-sm mt-1">Clique em “Novo roteiro” para começar.</p>
    </div>

    <template v-else>
      <!-- CARDS (mobile) -->
      <div class="sm:hidden space-y-3">
        <Link v-for="r in roteiros" :key="r.id" :href="`/admin/roteiros/${r.id}/edit`"
              class="block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-4">
          <div class="flex items-start justify-between gap-2">
            <p class="font-semibold text-gray-900 dark:text-white">{{ r.titulo }}</p>
            <span v-if="ehProximo(r.data)" :class="selo">Próximo</span>
          </div>
          <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
            {{ formatarData(r.data) }}<span v-if="r.versiculo"> · {{ r.versiculo }}</span>
          </p>
          <p v-if="verTodos" class="text-xs text-gray-400 dark:text-slate-500 mt-1">{{ r.pregador }}</p>
        </Link>
      </div>

      <!-- TABELA (desktop) -->
      <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="grid px-6 py-3 bg-gray-50 dark:bg-slate-700/50 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider"
             :class="colunas">
          <span>Pregação</span>
          <span>Data</span>
          <span v-if="verTodos">Pregador</span>
          <span>Editado</span>
          <span class="text-right">Ações</span>
        </div>
        <div v-for="r in roteiros" :key="r.id"
             class="grid items-center px-6 py-4 border-t border-gray-100 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition"
             :class="colunas">
          <div class="min-w-0">
            <Link :href="`/admin/roteiros/${r.id}/edit`" class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
              {{ r.titulo }}
            </Link>
            <p v-if="r.versiculo" class="text-xs text-gray-500 dark:text-slate-400 truncate">{{ r.versiculo }}</p>
          </div>
          <div class="text-sm text-gray-600 dark:text-slate-300 flex items-center gap-2">
            {{ formatarData(r.data) }}
            <span v-if="ehProximo(r.data)" :class="selo">Próximo</span>
          </div>
          <div v-if="verTodos" class="text-sm text-gray-600 dark:text-slate-300">{{ r.pregador }}</div>
          <div class="text-sm text-gray-500 dark:text-slate-400">{{ quando(r.atualizado_em) }}</div>
          <div class="flex justify-end gap-4 text-sm">
            <Link :href="`/admin/roteiros/${r.id}/pulpito`"
                  class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
              Púlpito
            </Link>
            <Link :href="`/admin/roteiros/${r.id}/edit`"
                  class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
              Abrir
            </Link>
            <button @click="excluir(r)" class="text-gray-400 dark:text-slate-500 hover:text-red-500 transition">
              Excluir
            </button>
          </div>
        </div>
      </div>
    </template>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  roteiros: { type: Array, default: () => [] },
  verTodos: { type: Boolean, default: false },
})

const criando = ref(false)
const selo = 'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 flex-shrink-0'
const colunas = computed(() => props.verTodos ? 'grid-cols-[2fr_1.2fr_1fr_1fr_190px] gap-4' : 'grid-cols-[2fr_1.2fr_1fr_190px] gap-4')

const hoje = new Date().toLocaleDateString('sv-SE') // AAAA-MM-DD no fuso local
// O próximo roteiro (data de hoje em diante, a mais próxima) ganha o selo.
const proximaData = computed(() => props.roteiros.map((r) => r.data).filter((d) => d >= hoje).sort()[0])
const ehProximo = (data) => data === proximaData.value

function novo() {
  criando.value = true
  router.post('/admin/roteiros', {}, { onFinish: () => { criando.value = false } })
}

function excluir(r) {
  if (confirm(`Excluir o roteiro "${r.titulo}"? Não dá para desfazer.`)) {
    router.delete(`/admin/roteiros/${r.id}`, { preserveScroll: true })
  }
}

function formatarData(data) {
  return new Date(data + 'T12:00:00').toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: 'short' })
}

function quando(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }) + ' ' +
    d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}
</script>
