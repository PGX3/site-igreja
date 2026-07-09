<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Documentos</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Documentos oficiais da igreja, prontos para imprimir ou salvar em PDF.
        </p>
      </div>
      <Link href="/admin/documentos/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Documento
      </Link>
    </div>

    <!-- EMPTY STATE -->
    <div v-if="documentos.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="text-4xl mb-3">📄</p>
      <p class="font-medium">Nenhum documento criado.</p>
    </div>

    <!-- CARDS (mobile) -->
    <div class="sm:hidden space-y-3">
      <div v-for="d in documentos" :key="d.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4">
          <p class="font-semibold text-gray-900 dark:text-white">{{ d.titulo }}</p>
          <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
            {{ formatarData(d.created_at) }}<span v-if="d.template"> · {{ d.template.nome }}</span>
          </p>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-700/50 flex gap-2 justify-end">
          <a :href="`/admin/documentos/${d.id}/imprimir`" target="_blank"
             class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
            Imprimir
          </a>
          <Link :href="`/admin/documentos/${d.id}/edit`"
                class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
            Editar
          </Link>
          <button @click="destroy(d.id)"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
            Excluir
          </button>
        </div>
      </div>
    </div>

    <!-- TABELA (desktop) -->
    <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <div class="grid grid-cols-4 px-6 py-3 bg-gray-50 dark:bg-slate-700/50 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider min-w-[560px]">
          <span>Título</span>
          <span>Modelo</span>
          <span>Data</span>
          <span class="text-right">Ações</span>
        </div>
        <div v-for="d in documentos" :key="d.id"
             class="grid grid-cols-4 items-center px-6 py-4 border-t border-gray-100 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition min-w-[560px]">
          <div>
            <p class="font-semibold text-gray-900 dark:text-white">{{ d.titulo }}</p>
            <p v-if="d.created_by" class="text-xs text-gray-500 dark:text-slate-400">{{ d.created_by.name }}</p>
          </div>
          <div class="text-sm text-gray-600 dark:text-slate-300">{{ d.template?.nome ?? '—' }}</div>
          <div class="text-sm text-gray-600 dark:text-slate-300">{{ formatarData(d.created_at) }}</div>
          <div class="flex justify-end gap-4 text-sm">
            <a :href="`/admin/documentos/${d.id}/imprimir`" target="_blank"
               class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
              Imprimir
            </a>
            <Link :href="`/admin/documentos/${d.id}/edit`"
                  class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
              Editar
            </Link>
            <button @click="destroy(d.id)" class="text-gray-400 dark:text-slate-500 hover:text-red-500 transition">
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

defineProps({ documentos: Array })

function formatarData(data) {
  if (!data) return ''
  const d = new Date(data)
  if (isNaN(d)) return ''
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' })
}

function destroy(id) {
  if (confirm('Remover este documento?')) {
    router.delete(`/admin/documentos/${id}`)
  }
}
</script>
