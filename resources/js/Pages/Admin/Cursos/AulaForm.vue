<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link :href="`/admin/cursos/${curso.id}/conteudo`" class="hover:text-blue-600 dark:hover:text-blue-400">{{ curso.titulo }}</Link>
        <span class="mx-1">›</span>
        {{ modulo.titulo }}
      </p>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Aula</h1>
    </div>

    <form @submit.prevent="submit" class="max-w-3xl space-y-6">

      <!-- TÍTULO + TIPO -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Título *</label>
          <input v-model="form.titulo" type="text"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.titulo }" />
          <p v-if="form.errors.titulo" class="mt-1 text-xs text-red-500">{{ form.errors.titulo }}</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Tipo</label>
          <select v-model="form.tipo"
                  class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="aula">Aula</option>
            <option value="material">Material</option>
            <option value="atividade">Atividade</option>
          </select>
        </div>
      </div>

      <!-- VÍDEO -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Vídeo (YouTube, opcional)</label>
        <input v-model="form.youtube_url" type="url" placeholder="https://www.youtube.com/watch?v=..."
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.youtube_url }" />
        <p v-if="form.errors.youtube_url" class="mt-1 text-xs text-red-500">{{ form.errors.youtube_url }}</p>
        <div v-if="form.youtube_url" class="mt-3 max-w-md">
          <MediaEmbed :url="form.youtube_url" />
        </div>
      </div>

      <!-- CONTEÚDO (editor rico com imagem) -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Conteúdo da aula</label>
        <RichTextEditor v-model="form.conteudo" permite-imagem permite-blocos upload-url="/admin/editor/imagem" />
        <p class="mt-1.5 text-xs text-gray-400 dark:text-slate-500">
          Formate o texto e insira imagens pelo botão da barra ou colando/arrastando direto no editor.
        </p>
      </div>

      <!-- ATIVO -->
      <div class="flex items-center gap-3">
        <input v-model="form.ativo" type="checkbox" id="ativo"
               class="w-4 h-4 text-blue-600 border-gray-300 dark:border-slate-500 rounded focus:ring-blue-500" />
        <label for="ativo" class="text-sm text-gray-600 dark:text-slate-300">
          Aula publicada (visível no curso)
        </label>
      </div>

      <!-- AÇÕES -->
      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ form.processing ? 'Salvando...' : 'Salvar aula' }}
        </button>
        <Link :href="`/admin/cursos/${curso.id}/conteudo`"
              class="px-6 py-2.5 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
          Cancelar
        </Link>
      </div>

    </form>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  aula: { type: Object, required: true },
  curso: { type: Object, required: true },
  modulo: { type: Object, required: true },
})

const form = useForm({
  titulo: props.aula.titulo ?? '',
  tipo: props.aula.tipo ?? 'aula',
  conteudo: props.aula.conteudo ?? '',
  youtube_url: props.aula.youtube_url ?? '',
  ativo: props.aula.ativo ?? true,
})

function submit() {
  form.put(`/admin/aulas/${props.aula.id}`)
}
</script>
