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

      <!-- ATIVIDADE: prazo e pontos -->
      <div v-if="form.tipo === 'atividade'" class="grid grid-cols-2 gap-4 rounded-lg bg-amber-50 dark:bg-amber-900/15 border border-amber-200 dark:border-amber-800/30 p-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Data de entrega (opcional)</label>
          <input v-model="form.data_entrega" type="date"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Pontos (opcional)</label>
          <input v-model="form.pontos" type="number" min="0" max="1000" placeholder="Ex: 10"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
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
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">{{ conteudoLabel }}</label>
        <RichTextEditor v-model="form.conteudo" permite-imagem permite-blocos upload-url="/admin/editor/imagem" />
        <p class="mt-1.5 text-xs text-gray-400 dark:text-slate-500">
          Formate o texto e insira imagens pelo botão da barra ou colando/arrastando direto no editor.
        </p>
      </div>

      <!-- MATERIAIS / ANEXOS -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Materiais / Anexos</label>
        <p v-if="form.tipo === 'material'" class="text-xs text-gray-500 dark:text-slate-400 -mt-1 mb-2">
          Neste tipo, os arquivos e links abaixo são o foco. O texto acima é só uma descrição.
        </p>

        <!-- Lista -->
        <div v-if="aula.anexos.length" class="space-y-2 mb-3">
          <div v-for="anexo in aula.anexos" :key="anexo.id"
               class="flex items-center gap-3 border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2.5 bg-white dark:bg-slate-700">
            <span class="text-lg">{{ anexo.tipo === 'link' ? '🔗' : '📄' }}</span>
            <a :href="anexo.url" target="_blank" rel="noopener"
               class="flex-1 text-sm text-gray-800 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400 truncate">
              {{ anexo.titulo }}
            </a>
            <button type="button" @click="removerAnexo(anexo.id)"
                    class="text-xs text-gray-400 hover:text-red-500 transition">Remover</button>
          </div>
        </div>

        <!-- Adicionar arquivo / link -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <form @submit.prevent="enviarArquivo" class="border border-dashed border-gray-300 dark:border-slate-600 rounded-lg p-3 space-y-2">
            <p class="text-xs font-semibold text-gray-500 dark:text-slate-400">Enviar arquivo (PDF, doc, imagem...)</p>
            <input type="file" @change="onArquivo" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,image/*"
                   class="block w-full text-xs text-gray-600 dark:text-slate-300 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-900/30 dark:file:text-blue-300 cursor-pointer" />
            <input v-model="fileForm.titulo" type="text" placeholder="Nome de exibição (opcional)"
                   class="w-full border border-gray-200 dark:border-slate-600 rounded px-3 py-1.5 text-xs bg-white dark:bg-slate-700 text-gray-900 dark:text-white" />
            <button type="submit" :disabled="!fileForm.arquivo || fileForm.processing" class="text-xs font-semibold bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-3 py-1.5 rounded transition">
              {{ fileForm.processing ? 'Enviando...' : 'Adicionar arquivo' }}
            </button>
            <p v-if="fileForm.errors.arquivo" class="text-xs text-red-500">{{ fileForm.errors.arquivo }}</p>
          </form>

          <form @submit.prevent="enviarLink" class="border border-dashed border-gray-300 dark:border-slate-600 rounded-lg p-3 space-y-2">
            <p class="text-xs font-semibold text-gray-500 dark:text-slate-400">Adicionar link</p>
            <input v-model="linkForm.url" type="url" placeholder="https://..."
                   class="w-full border border-gray-200 dark:border-slate-600 rounded px-3 py-1.5 text-xs bg-white dark:bg-slate-700 text-gray-900 dark:text-white" />
            <input v-model="linkForm.titulo" type="text" placeholder="Nome de exibição (opcional)"
                   class="w-full border border-gray-200 dark:border-slate-600 rounded px-3 py-1.5 text-xs bg-white dark:bg-slate-700 text-gray-900 dark:text-white" />
            <button type="submit" :disabled="!linkForm.url || linkForm.processing" class="text-xs font-semibold bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-3 py-1.5 rounded transition">
              Adicionar link
            </button>
            <p v-if="linkForm.errors.url" class="text-xs text-red-500">{{ linkForm.errors.url }}</p>
          </form>
        </div>
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
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import { Link, router, useForm } from '@inertiajs/vue3'

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
  data_entrega: props.aula.data_entrega ?? '',
  pontos: props.aula.pontos ?? '',
  ativo: props.aula.ativo ?? true,
})

const conteudoLabel = computed(() => ({
  aula: 'Conteúdo da aula',
  material: 'Descrição (opcional)',
  atividade: 'Instruções da atividade',
}[form.tipo] ?? 'Conteúdo da aula'))

const fileForm = useForm({ tipo: 'arquivo', arquivo: null, titulo: '' })
const linkForm = useForm({ tipo: 'link', url: '', titulo: '' })

// preserveState mantém o conteúdo em edição no editor ao anexar/remover
const anexoOpts = { preserveState: true, preserveScroll: true }

function onArquivo(e) {
  fileForm.arquivo = e.target.files?.[0] ?? null
}

function enviarArquivo() {
  fileForm.post(`/admin/aulas/${props.aula.id}/anexos`, {
    ...anexoOpts,
    onSuccess: () => fileForm.reset(),
  })
}

function enviarLink() {
  linkForm.post(`/admin/aulas/${props.aula.id}/anexos`, {
    ...anexoOpts,
    onSuccess: () => linkForm.reset(),
  })
}

function removerAnexo(id) {
  router.delete(`/admin/anexos/${id}`, anexoOpts)
}

function submit() {
  form.put(`/admin/aulas/${props.aula.id}`)
}
</script>
