<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Modelos de Documento</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Textos-base com variáveis (ex.: <code>&#123;&#123;destinatario&#125;&#125;</code>) que preenchem os documentos.
        </p>
      </div>
      <Link href="/admin/documento-templates/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Modelo
      </Link>
    </div>

    <!-- EMPTY STATE -->
    <div v-if="templates.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="text-4xl mb-3">📋</p>
      <p class="font-medium">Nenhum modelo cadastrado.</p>
    </div>

    <!-- CARDS (mobile) -->
    <div class="sm:hidden space-y-3">
      <div v-for="t in templates" :key="t.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4">
          <div class="flex items-start justify-between gap-2 mb-1">
            <p class="font-semibold text-gray-900 dark:text-white">{{ t.nome }}</p>
            <span v-if="!t.ativo" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400 flex-shrink-0">
              Inativo
            </span>
          </div>
          <p v-if="t.descricao" class="text-xs text-gray-500 dark:text-slate-400">{{ t.descricao }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">
            {{ t.variaveis.length }} variáveis · {{ t.documentos_count }} documentos
          </p>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-700/50 flex gap-2 justify-end">
          <Link :href="`/admin/documento-templates/${t.id}/edit`"
                class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
            Editar
          </Link>
          <button @click="destroy(t)"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
            Excluir
          </button>
        </div>
      </div>
    </div>

    <!-- TABELA (desktop) -->
    <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <div class="grid grid-cols-4 px-6 py-3 bg-gray-50 dark:bg-slate-700/50 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider min-w-[520px]">
          <span class="col-span-2">Modelo</span>
          <span>Uso</span>
          <span class="text-right">Ações</span>
        </div>
        <div v-for="t in templates" :key="t.id"
             class="grid grid-cols-4 items-center px-6 py-4 border-t border-gray-100 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition min-w-[520px]">
          <div class="col-span-2">
            <p class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              {{ t.nome }}
              <span v-if="!t.ativo" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400">Inativo</span>
            </p>
            <p v-if="t.descricao" class="text-xs text-gray-500 dark:text-slate-400">{{ t.descricao }}</p>
          </div>
          <div class="text-sm text-gray-600 dark:text-slate-300">
            {{ t.variaveis.length }} var. · {{ t.documentos_count }} doc.
          </div>
          <div class="flex justify-end gap-4 text-sm">
            <Link :href="`/admin/documento-templates/${t.id}/edit`"
                  class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
              Editar
            </Link>
            <button @click="destroy(t)" class="text-gray-400 dark:text-slate-500 hover:text-red-500 transition">
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

defineProps({ templates: Array })

function destroy(t) {
  const aviso = t.documentos_count > 0
    ? `Este modelo já gerou ${t.documentos_count} documento(s). Os documentos continuam, mas perdem o vínculo. Remover o modelo?`
    : 'Remover este modelo?'
  if (confirm(aviso)) {
    router.delete(`/admin/documento-templates/${t.id}`)
  }
}
</script>
