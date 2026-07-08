<template>
  <AdminLayout>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Meus Cursos</h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Cursos e estudos disponíveis.</p>
    </div>

    <div v-if="cursos.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="text-4xl mb-3">📚</p>
      <p class="font-medium">Nenhum curso disponível ainda.</p>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Link v-for="curso in cursos" :key="curso.id" :href="`/admin/aprender/${curso.id}`"
            class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden hover:shadow-md hover:border-blue-300 dark:hover:border-blue-500/50 transition">
        <div class="aspect-video bg-gray-100 dark:bg-slate-700 overflow-hidden">
          <img v-if="curso.capa_url" :src="curso.capa_url" :alt="curso.titulo" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full flex items-center justify-center text-4xl">📖</div>
        </div>
        <div class="p-4">
          <p class="font-semibold text-gray-900 dark:text-white">{{ curso.titulo }}</p>
          <p v-if="curso.descricao" class="text-xs text-gray-500 dark:text-slate-400 mt-1 line-clamp-2">
            {{ curso.descricao }}
          </p>
        </div>
      </Link>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({ cursos: Array })
</script>
