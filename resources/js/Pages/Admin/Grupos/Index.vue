<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Gestão</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Grupos</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">{{ grupos.length }} grupo(s) cadastrado(s)</p>
      </div>
      <Link href="/admin/grupos/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Grupo
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- EMPTY STATE -->
    <div v-if="!grupos.length"
         class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl py-20 text-center text-gray-400 dark:text-slate-500">
      <p class="text-4xl mb-3">◉</p>
      <p class="font-medium">Nenhum grupo cadastrado ainda.</p>
      <Link href="/admin/grupos/create" class="mt-4 inline-block text-sm text-blue-600 dark:text-blue-400 hover:underline">
        Criar primeiro grupo
      </Link>
    </div>

    <template v-else>

      <!-- CARDS (mobile) -->
      <div class="sm:hidden space-y-3">
        <div v-for="g in grupos" :key="g.id"
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
          <!-- Info -->
          <div class="p-4">
            <p class="font-semibold text-gray-900 dark:text-white">{{ g.nome }}</p>
            <p v-if="g.descricao" class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ g.descricao }}</p>
            <!-- Líder + contadores -->
            <div class="flex items-center justify-between mt-3">
              <span class="text-xs text-gray-500 dark:text-slate-400">
                <span v-if="g.lider">{{ g.lider.name }}</span>
                <span v-else class="italic text-gray-300 dark:text-slate-600">Sem líder</span>
              </span>
              <div class="flex gap-2">
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300">
                  {{ g.total_membros }} membro{{ g.total_membros !== 1 ? 's' : '' }}
                </span>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                  {{ g.total_escalas }} escala{{ g.total_escalas !== 1 ? 's' : '' }}
                </span>
              </div>
            </div>
          </div>
          <!-- Ações -->
          <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-700/50 flex gap-2 justify-end">
            <Link :href="`/admin/grupos/${g.id}/edit`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Editar
            </Link>
            <button @click="confirmarExclusao(g)"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
              Excluir
            </button>
          </div>
        </div>
      </div>

      <!-- TABELA (desktop) -->
      <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[540px]">
            <thead class="border-b border-gray-100 dark:border-slate-700">
              <tr>
                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Nome</th>
                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Líder</th>
                <th class="text-center px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Membros</th>
                <th class="text-center px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Escalas</th>
                <th class="px-6 py-4"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="g in grupos" :key="g.id"
                  class="border-b border-gray-50 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                <td class="px-6 py-4">
                  <p class="font-semibold text-gray-900 dark:text-white">{{ g.nome }}</p>
                  <p v-if="g.descricao" class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 truncate max-w-[200px]">{{ g.descricao }}</p>
                </td>
                <td class="px-6 py-4">
                  <span v-if="g.lider" class="text-gray-700 dark:text-slate-300">{{ g.lider.name }}</span>
                  <span v-else class="text-gray-300 dark:text-slate-600 italic text-xs">Sem líder</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-block bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300 text-xs font-semibold px-2.5 py-1 rounded-full">{{ g.total_membros }}</span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-block bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-semibold px-2.5 py-1 rounded-full">{{ g.total_escalas }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <Link :href="`/admin/grupos/${g.id}/edit`"
                          class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                      Editar
                    </Link>
                    <button @click="confirmarExclusao(g)"
                            class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                      Excluir
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </template>

    <!-- MODAL CONFIRMAR EXCLUSÃO -->
    <div v-if="grupoParaExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Excluir grupo?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ grupoParaExcluir.nome }}</strong>" será excluído permanentemente.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="grupoParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/grupos/${grupoParaExcluir.id}`" method="delete" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
            Excluir
          </Link>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({ grupos: Array })

const grupoParaExcluir = ref(null)
function confirmarExclusao(g) { grupoParaExcluir.value = g }
</script>
