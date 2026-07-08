<template>
  <AdminLayout>
    <div class="max-w-3xl">
      <div v-if="preview" class="mb-5 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 px-4 py-2.5 text-sm text-amber-700 dark:text-amber-300">
        Pré-visualização: esta aula está oculta e ainda não aparece para os membros.
      </div>
      <div class="flex justify-end mb-4">
        <a :href="`/admin/aprender/aulas/${aula.id}/pdf`" target="_blank" rel="noopener"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 px-3 py-1.5 rounded-lg transition">
          📄 Baixar PDF
        </a>
      </div>
      <AulaLeitura :aula="aula" :curso-titulo="cursoTitulo" :voltar-url="voltarUrl" />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AulaLeitura from '@/Components/AulaLeitura.vue'

defineProps({
  aula: { type: Object, required: true },
  cursoTitulo: { type: String, default: '' },
  voltarUrl: { type: String, default: null },
  preview: { type: Boolean, default: false },
})
</script>
