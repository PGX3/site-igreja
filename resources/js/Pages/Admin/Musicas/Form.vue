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
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300">Letra *</label>
          <button type="button" @click="preview = true"
                  class="text-xs font-semibold text-gray-900 dark:text-white bg-gray-900/5 dark:bg-white/10 hover:bg-gray-900/10 dark:hover:bg-white/20 px-3 py-1.5 rounded transition">
            📱 Ver no celular
          </button>
        </div>
        <RichTextEditor v-model="form.letra" :tem-erro="!!form.errors.letra" />
        <p v-if="form.errors.letra" class="mt-1 text-xs text-red-500">{{ form.errors.letra }}</p>
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

const props = defineProps({ musica: Object })

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
  tom:   props.musica?.tom   ?? '',
  link:  props.musica?.link  ?? '',
  letra: props.musica?.letra ?? '',
})

function submit() {
  if (editando.value) {
    form.put(`/admin/musicas/${props.musica.id}`)
  } else {
    form.post('/admin/musicas')
  }
}
</script>
