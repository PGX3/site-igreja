<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
      <div class="min-w-0">
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
          <Link href="/admin/musicas" class="hover:text-blue-600 dark:hover:text-blue-400">Músicas</Link>
          <span class="mx-1">›</span>
          Detalhe
        </p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ musica.nome }}</h1>
        <p v-if="musica.artista" class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">{{ musica.artista }}</p>
        <div class="flex items-center gap-2 flex-wrap mt-2">
          <span v-if="musica.tom" class="bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs font-bold px-2.5 py-1 rounded-full">
            Tom: {{ musica.tom }}
          </span>
          <span v-if="musica.bpm" class="bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300 text-xs font-bold px-2.5 py-1 rounded-full">
            {{ musica.bpm }} BPM
          </span>
          <span v-if="musica.categoria" class="bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300 text-xs font-bold px-2.5 py-1 rounded-full">
            {{ musica.categoria }}
          </span>
          <span class="text-xs text-gray-400 dark:text-slate-500">
            <template v-if="musica.vezes">Tocada {{ musica.vezes }}x · última: {{ musica.ultima }}</template>
            <template v-else>Nunca tocada</template>
          </span>
        </div>
      </div>

      <div class="flex items-center gap-2 flex-shrink-0">
        <button @click="apresentar = true"
                class="text-sm font-semibold text-gray-900 dark:text-white bg-gray-900/5 dark:bg-white/10 hover:bg-gray-900/10 dark:hover:bg-white/20 px-4 py-2.5 rounded-lg transition">
          📱 Ver no celular
        </button>
        <a :href="`/admin/musicas/${musica.id}/imprimir`" target="_blank" rel="noopener"
           class="border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-4 py-2.5 rounded-lg text-sm font-semibold transition">
          🖨 PDF
        </a>
        <Link :href="`/admin/musicas/${musica.id}/edit`"
              class="border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-4 py-2.5 rounded-lg text-sm font-semibold transition">
          Editar
        </Link>
      </div>
    </div>

    <!-- PLAYER -->
    <div v-if="musica.link" class="mb-6 max-w-xl">
      <MediaEmbed :url="musica.link" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- LETRA: músicos (com marcações) e público (telão) -->
      <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
          <div class="inline-flex rounded-lg bg-gray-100 dark:bg-slate-700 p-0.5 text-xs font-semibold">
            <button v-for="v in versoes" :key="v.id" type="button" @click="versao = v.id"
                    class="px-3 py-1.5 rounded-md transition"
                    :class="versao === v.id
                      ? 'bg-white dark:bg-slate-900 text-gray-900 dark:text-white shadow-sm'
                      : 'text-gray-500 dark:text-slate-400 hover:text-gray-800 dark:hover:text-white'">
              {{ v.rotulo }}
            </button>
          </div>
          <div v-if="textoDaVersao" class="flex items-center gap-2">
            <button type="button" @click="copiar"
                    class="border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
              {{ copiado ? 'Copiado!' : 'Copiar' }}
            </button>
            <button v-if="versao === 'publico'" type="button" @click="baixarPublico"
                    class="border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
              Baixar .txt
            </button>
          </div>
        </div>

        <!-- Músicos: com tags de refrão e instruções da banda -->
        <template v-if="versao === 'musicos'">
          <div v-if="musica.letra_crua" class="letra text-gray-800 dark:text-slate-200 leading-relaxed" v-html="musica.letra"></div>
          <p v-else class="text-sm text-gray-400 dark:text-slate-500">Sem letra cadastrada ainda.</p>
        </template>

        <!-- Crua: só o texto, calculada da letra dos músicos -->
        <template v-else-if="versao === 'crua'">
          <pre v-if="musica.letra_crua" class="whitespace-pre-wrap font-sans text-gray-800 dark:text-slate-200 leading-relaxed">{{ musica.letra_crua }}</pre>
          <p v-else class="text-sm text-gray-400 dark:text-slate-500">Sem letra cadastrada ainda.</p>
        </template>

        <!-- Público: seções do telão, na ordem cantada -->
        <template v-else>
          <div v-if="musica.secoes_publico.length" class="space-y-4">
            <div v-for="(sec, i) in musica.secoes_publico" :key="i">
              <p class="text-[10px] font-bold uppercase tracking-widest mb-1"
                 :class="sec.tipo === 'refrao' ? 'text-green-700 dark:text-green-400' : 'text-gray-400 dark:text-slate-500'">
                {{ i + 1 }} · {{ sec.rotulo }}
              </p>
              <pre class="whitespace-pre-wrap font-sans text-gray-800 dark:text-slate-200 leading-relaxed">{{ sec.texto }}</pre>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 dark:text-slate-500">
            Sem versão do público ainda. Ela é a letra do telão (SPresenter ou Holyrics):
            uma seção por slide, na ordem cantada, sem instruções e com o refrão repetido onde ele volta.
          </p>
        </template>
      </div>

      <!-- HISTÓRICO -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden self-start">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
          <p class="font-semibold text-gray-900 dark:text-white text-sm">Histórico</p>
          <span class="text-xs text-gray-400 dark:text-slate-500">{{ historico.length }}x</span>
        </div>
        <div v-if="historico.length" class="divide-y divide-gray-50 dark:divide-slate-700/50 max-h-[420px] overflow-y-auto">
          <Link v-for="(h, i) in historico" :key="i" :href="`/admin/escalas/${h.escala_id}`"
                class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ h.titulo }}</p>
              <p class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ h.data }}<span v-if="h.grupo"> · {{ h.grupo }}</span></p>
            </div>
            <span v-if="h.tom" class="flex-shrink-0 bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-[11px] font-bold px-2 py-0.5 rounded-full">{{ h.tom }}</span>
          </Link>
        </div>
        <div v-else class="py-10 text-center text-gray-400 dark:text-slate-500 text-sm px-4">
          Ainda não foi tocada em nenhuma escala.
        </div>
      </div>
    </div>

    <!-- APRESENTAÇÃO -->
    <SetlistApresentacao v-if="apresentar"
                         :musicas="[{ nome: musica.nome, tom: musica.tom, letra: musica.letra, link: musica.link }]"
                         @close="apresentar = false" />

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import SetlistApresentacao from '@/Components/SetlistApresentacao.vue'
import { Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  musica: Object,
  historico: { type: Array, default: () => [] },
})

const apresentar = ref(false)

const versoes = [
  { id: 'musicos', rotulo: 'Músicos' },
  { id: 'crua', rotulo: 'Só a letra' },
  { id: 'publico', rotulo: 'Público (telão)' },
]
const versao = ref('musicos')
const copiado = ref(false)

// Texto que os botões copiam: a versão dos músicos é formatada, então copia-se a crua
const textoDaVersao = computed(() =>
  versao.value === 'publico' ? props.musica.letra_publico : props.musica.letra_crua)

async function copiar() {
  try {
    await navigator.clipboard.writeText(textoDaVersao.value)
    copiado.value = true
    setTimeout(() => { copiado.value = false }, 2000)
  } catch {
    alert('Não deu para copiar o texto.')
  }
}

// O Holyrics importa .txt e quebra os slides nas linhas em branco.
function baixarPublico() {
  const nome = [props.musica.nome, props.musica.artista].filter(Boolean).join(' - ')
  const blob = new Blob([props.musica.letra_publico], { type: 'text/plain;charset=utf-8' })
  const a = document.createElement('a')
  a.href = URL.createObjectURL(blob)
  a.download = `${nome.replace(/[\\/:*?"<>|]/g, '')}.txt`
  a.click()
  URL.revokeObjectURL(a.href)
}
</script>

<style scoped>
.letra :deep(p) { margin: 0; }
.letra :deep(p:empty)::before { content: "\00a0"; }
.letra :deep(strong) { font-weight: 800; }
</style>
