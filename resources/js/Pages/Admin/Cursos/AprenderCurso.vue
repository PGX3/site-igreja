<template>
  <AdminLayout>
    <div class="max-w-3xl">
      <div class="flex items-center justify-between gap-3 mb-6">
        <Link href="/admin/aprender"
              class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
          ← Meus Cursos
        </Link>
        <a :href="`/admin/aprender/${curso.id}/pdf`" target="_blank" rel="noopener"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 px-3 py-1.5 rounded-lg transition">
          📄 Baixar livro (PDF)
        </a>
      </div>
      <div v-if="preview" class="mb-5 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 px-4 py-2.5 text-sm text-amber-700 dark:text-amber-300">
        Pré-visualização: este curso está inativo e ainda não aparece para os membros.
      </div>
      <CursoLeitura :curso="curso" />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import CursoLeitura from '@/Components/CursoLeitura.vue'

defineProps({
  curso: { type: Object, required: true },
  preview: { type: Boolean, default: false },
})
</script>
