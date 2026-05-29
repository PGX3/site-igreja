<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cultos</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Gerencie os cultos exibidos no site.
        </p>
      </div>
      <Link href="/admin/cultos/create"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Culto
      </Link>
    </div>

    <!-- TABELA -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">

      <!-- HEAD -->
      <div class="grid grid-cols-4 px-6 py-3 bg-gray-50 dark:bg-slate-700/50 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">
        <span>Nome</span>
        <span>Dia</span>
        <span>Status</span>
        <span class="text-right">Ações</span>
      </div>

      <!-- ROWS -->
      <div v-for="culto in cultos" :key="culto.id"
           class="grid grid-cols-4 items-center px-6 py-4 border-t border-gray-100 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">

        <!-- NOME -->
        <div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ culto.nome }}</p>
          <p class="text-xs text-gray-500 dark:text-slate-400">
            {{ culto.horario }}
          </p>
        </div>

        <!-- DIA -->
        <div class="text-sm text-gray-600 dark:text-slate-300">
          {{ culto.dia_semana }}
        </div>

        <!-- STATUS -->
        <div>
          <span
            class="text-xs font-semibold px-2.5 py-1 rounded-full"
            :class="culto.ativo
              ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400'
              : 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400'"
          >
            {{ culto.ativo ? 'Ativo' : 'Inativo' }}
          </span>
        </div>

        <!-- AÇÕES -->
        <div class="flex justify-end gap-4 text-sm">
          <Link :href="`/admin/cultos/${culto.id}/edit`"
                class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
            Editar
          </Link>
          <button @click="destroy(culto.id)"
                  class="text-gray-400 dark:text-slate-500 hover:text-red-500 transition">
            Excluir
          </button>
        </div>

      </div>

      <!-- EMPTY STATE -->
      <div v-if="cultos.length === 0"
           class="text-center py-12 text-gray-400 dark:text-slate-500 text-sm">
        Nenhum culto cadastrado.
      </div>

    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({ cultos: Array })

function destroy(id) {
  if (confirm('Remover este culto?')) {
    router.delete(`/admin/cultos/${id}`)
  }
}
</script>
