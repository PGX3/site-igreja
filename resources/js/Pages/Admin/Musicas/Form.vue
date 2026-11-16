<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/musicas" class="hover:text-blue-600 dark:hover:text-blue-400">Músicas</Link>
        <span class="mx-1">›</span>
        {{ editando ? 'Editar' : 'Nova' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ editando ? 'Editar Música' : 'Nova Música' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="max-w-3xl space-y-6">

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Nome -->
        <div class="sm:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Nome *</label>
          <input v-model="form.nome" type="text" placeholder="Ex: Oceanos"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.nome }" />
          <p v-if="form.errors.nome" class="mt-1 text-xs text-red-500">{{ form.errors.nome }}</p>
        </div>
        <!-- Tom -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Tom</label>
          <select v-model="form.tom"
                  class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">—</option>
            <option v-for="t in tons" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Artista -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Artista / ministério</label>
          <input v-model="form.artista" type="text" placeholder="Ex: Nívea Soares"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.artista }" />
          <p v-if="form.errors.artista" class="mt-1 text-xs text-red-500">{{ form.errors.artista }}</p>
        </div>
        <!-- Categoria -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Categoria</label>
          <select v-model="form.categoria" class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">—</option>
            <option v-for="(rotulo, chave) in categorias" :key="chave" :value="chave">{{ rotulo }}</option>
          </select>
        </div>
        <!-- BPM -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">BPM</label>
          <input v-model="form.bpm" type="number" min="20" max="400" placeholder="Ex: 132"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.bpm }" />
          <p v-if="form.errors.bpm" class="mt-1 text-xs text-red-500">{{ form.errors.bpm }}</p>
        </div>
      </div>

      <!-- Link (YouTube / Spotify) -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Link (YouTube ou Spotify)</label>
        <input v-model="form.link" type="url" placeholder="https://youtube.com/... ou https://open.spotify.com/..."
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.link }" />
        <p v-if="form.errors.link" class="mt-1 text-xs text-red-500">{{ form.errors.link }}</p>
        <div v-if="form.link" class="mt-3 max-w-md">
          <MediaEmbed :url="form.link" />
        </div>
      </div>

      <!-- Letra (editor rico) -->
      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300">Letra dos músicos <span class="font-normal text-gray-400 dark:text-slate-500">(com marcações: refrão, 2x, solo. Opcional)</span></label>
          <button type="button" @click="preview = true"
                  class="text-xs font-semibold text-gray-900 dark:text-white bg-gray-900/5 dark:bg-white/10 hover:bg-gray-900/10 dark:hover:bg-white/20 px-3 py-1.5 rounded transition">
            📱 Ver no celular
          </button>
        </div>
        <RichTextEditor v-model="form.letra" :tem-erro="!!form.errors.letra" marcacoes-musica />
        <p v-if="form.errors.letra" class="mt-1 text-xs text-red-500">{{ form.errors.letra }}</p>
      </div>

      <!-- Versão do público (telão): seções na ordem cantada -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300">Versão do público <span class="font-normal text-gray-400 dark:text-slate-500">(telão: SPresenter ou Holyrics)</span></label>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 mb-2">
          Cada seção é um slide, na ordem em que é cantada. Sem instruções, e com o refrão repetido cada vez que ele volta.
        </p>

        <div class="space-y-2">
          <div v-for="(sec, i) in form.secoes_publico" :key="i"
               class="border border-gray-200 dark:border-slate-600 rounded-lg p-3 bg-gray-50 dark:bg-slate-800/60">
            <div class="flex items-center gap-2 mb-2">
              <span class="text-xs font-bold text-gray-400 dark:text-slate-500 w-5 text-right">{{ i + 1 }}</span>
              <select v-model="sec.tipo"
                      class="border border-gray-200 dark:border-slate-600 rounded-md px-2 py-1 text-xs font-semibold bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option v-for="(rotulo, chave) in tipos_secao" :key="chave" :value="chave">{{ rotulo }}</option>
              </select>
              <div class="ml-auto flex items-center gap-1 text-gray-400 dark:text-slate-500">
                <button type="button" @click="moverSecao(i, -1)" :disabled="i === 0" title="Subir"
                        class="w-7 h-7 rounded hover:bg-gray-200 dark:hover:bg-slate-700 disabled:opacity-30">↑</button>
                <button type="button" @click="moverSecao(i, 1)" :disabled="i === form.secoes_publico.length - 1" title="Descer"
                        class="w-7 h-7 rounded hover:bg-gray-200 dark:hover:bg-slate-700 disabled:opacity-30">↓</button>
                <button type="button" @click="duplicarSecao(i)" title="Duplicar (para repetições)"
                        class="w-7 h-7 rounded hover:bg-gray-200 dark:hover:bg-slate-700">⧉</button>
                <button type="button" @click="form.secoes_publico.splice(i, 1)" title="Remover"
                        class="w-7 h-7 rounded hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600">×</button>
              </div>
            </div>
            <textarea v-model="sec.texto" :rows="Math.max(2, (sec.texto || '').split('\n').length)"
                      class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 leading-relaxed"></textarea>
          </div>
        </div>

        <button type="button" @click="form.secoes_publico.push({ tipo: 'verso', texto: '' })"
                class="mt-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
          + Adicionar seção
        </button>
        <p v-if="form.errors.secoes_publico" class="mt-1 text-xs text-red-500">{{ form.errors.secoes_publico }}</p>
      </div>

      <!-- AÇÕES -->
      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ editando ? 'Salvar alterações' : 'Criar Música' }}
        </button>
        <Link href="/admin/musicas"
              class="px-6 py-2.5 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
          Cancelar
        </Link>
      </div>

    </form>

    <!-- APRESENTAÇÃO -->
    <SetlistApresentacao v-if="preview"
                         :musicas="[{ nome: form.nome || 'Prévia', tom: form.tom, letra: form.letra, link: form.link }]"
                         @close="preview = false" />

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import SetlistApresentacao from '@/Components/SetlistApresentacao.vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  musica: Object,
  categorias: { type: Object, default: () => ({}) },
  tipos_secao: { type: Object, default: () => ({}) },
})

const editando = computed(() => !!props.musica)
const preview = ref(false)

const tons = [
  'Dó', 'Dó#', 'Ré', 'Ré#', 'Mi', 'Fá',
  'Fá#', 'Sol', 'Sol#', 'Lá', 'Lá#', 'Si',
  'Dóm', 'Dó#m', 'Rém', 'Ré#m', 'Mim', 'Fám',
  'Fá#m', 'Solm', 'Sol#m', 'Lám', 'Lá#m', 'Sim',
]

const form = useForm({
  nome:  props.musica?.nome  ?? '',
  artista: props.musica?.artista ?? '',
  tom:   props.musica?.tom   ?? '',
  bpm:   props.musica?.bpm   ?? '',
  categoria: props.musica?.categoria ?? '',
  link:  props.musica?.link  ?? '',
  letra: props.musica?.letra ?? '',
  secoes_publico: (props.musica?.secoes_publico ?? []).map(s => ({ ...s })),
})

function moverSecao(i, direcao) {
  const lista = form.secoes_publico
  const [sec] = lista.splice(i, 1)
  lista.splice(i + direcao, 0, sec)
}

function duplicarSecao(i) {
  form.secoes_publico.splice(i + 1, 0, { ...form.secoes_publico[i] })
}

function submit() {
  if (editando.value) {
    form.put(`/admin/musicas/${props.musica.id}`)
  } else {
    form.post('/admin/musicas')
  }
}
</script>
