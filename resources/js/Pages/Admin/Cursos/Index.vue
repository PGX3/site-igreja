<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Cursos</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Escola bíblica: crie cursos, módulos e aulas com texto e imagens.
        </p>
      </div>
      <Link href="/admin/cursos/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Curso
      </Link>
    </div>

    <!-- EMPTY STATE -->
    <div v-if="cursos.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="text-4xl mb-3">📚</p>
      <p class="font-medium">Nenhum curso cadastrado.</p>
    </div>

    <!-- LISTA -->
    <div v-else class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div v-for="curso in cursos" :key="curso.id"
           class="flex items-center gap-4 px-5 py-4 border-b border-gray-100 dark:border-slate-700/50 last:border-0 hover:bg-gray-50 dark:hover:bg-slate-700/40 transition">
        <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-slate-700 overflow-hidden flex-shrink-0 flex items-center justify-center">
          <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="w-full h-full object-cover" />
          <span v-else class="text-xl">📖</span>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-900 dark:text-white truncate">{{ curso.titulo }}</p>
          <p class="text-xs text-gray-500 dark:text-slate-400">
            {{ curso.modulos_count }} {{ curso.modulos_count === 1 ? 'módulo' : 'módulos' }}
            <span v-if="curso.compartilhado" class="ml-2 text-green-600 dark:text-green-400">· link ativo</span>
          </p>
        </div>
        <span class="hidden sm:inline text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0"
              :class="curso.ativo
                ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                : 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400'">
          {{ curso.ativo ? 'Ativo' : 'Inativo' }}
        </span>
        <div class="flex items-center gap-3 text-sm flex-shrink-0">
          <a :href="`/admin/aprender/${curso.id}`" target="_blank" rel="noopener"
             class="text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
            Ver
          </a>
          <Link :href="`/admin/cursos/${curso.id}/conteudo`"
                class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
            Conteúdo
          </Link>
          <Link :href="`/admin/cursos/${curso.id}/edit`"
                class="text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
            Editar
          </Link>
          <button @click="destroy(curso)"
                  class="text-gray-400 dark:text-slate-500 hover:text-red-500 transition">
            Excluir
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({ cursos: Array })

function destroy(curso) {
  if (confirm(`Remover o curso "${curso.titulo}"? Os módulos e aulas serão apagados.`)) {
    router.delete(`/admin/cursos/${curso.id}`)
  }
}
</script>
