<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ ehPastor ? 'Pregações' : 'Minhas pregações' }}</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Escreva o roteiro antes do culto (aparece no iPad do púlpito). Depois, com o link do YouTube, ela vai para o site.
        </p>
      </div>
      <div class="flex flex-wrap gap-2 self-start sm:self-auto">
        <button v-if="ehPastor" type="button" @click="importarYoutube" :disabled="importando"
                class="border border-red-200 dark:border-red-900/60 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-900/30 text-red-700 dark:text-red-300 px-4 py-2 rounded-lg text-sm font-semibold transition disabled:opacity-50">
          {{ importando ? 'Importando...' : '↻ Importar do YouTube' }}
        </button>
        <button type="button" @click="nova" :disabled="criando"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
          + Nova pregação
        </button>
      </div>
    </div>

    <div v-if="$page.props.flash?.error"
         class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm font-medium">
      {{ $page.props.flash.error }}
    </div>
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <div v-if="ehPastor && !youtubeConfigurado" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-200">
      Cadastre o canal do YouTube em <Link href="/admin/igreja" class="font-semibold underline">Dados da Igreja</Link> para habilitar a importação.
    </div>

    <!-- FILTRO -->
    <div v-if="pregacoes.length" class="flex gap-1 mb-4 p-1 rounded-lg bg-gray-100 dark:bg-slate-800 w-fit">
      <button v-for="f in filtros" :key="f.valor" type="button" @click="filtro = f.valor"
              class="px-3 py-1.5 rounded-md text-xs font-semibold transition"
              :class="filtro === f.valor ? 'bg-white dark:bg-slate-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-slate-400 hover:text-gray-800 dark:hover:text-white'">
        {{ f.rotulo }} <span class="opacity-60">{{ contagem[f.valor] }}</span>
      </button>
    </div>

    <!-- EMPTY STATE -->
    <div v-if="lista.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="font-medium">{{ pregacoes.length ? 'Nada neste filtro.' : 'Nenhuma pregação ainda.' }}</p>
      <p v-if="!pregacoes.length" class="text-sm mt-1">Clique em “Nova pregação” para escrever o roteiro.</p>
    </div>

    <template v-else>
      <!-- CARDS (mobile) -->
      <div class="sm:hidden space-y-3">
        <div v-for="p in lista" :key="p.id"
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
          <Link :href="`/admin/pregacoes/${p.id}/edit`" class="block p-4">
            <div class="flex items-start justify-between gap-2 mb-1">
              <p class="font-semibold text-gray-900 dark:text-white">{{ p.titulo }}</p>
              <span :class="selo(p.status)">{{ rotuloStatus[p.status] }}</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-slate-400">
              {{ formatarData(p.data_pregacao) }}<span v-if="p.pregador"> · {{ p.pregador }}</span>
            </p>
          </Link>
          <div class="px-4 py-2.5 border-t border-gray-100 dark:border-slate-700/50 flex gap-2 justify-end text-xs font-semibold">
            <Link v-if="p.tem_roteiro" :href="`/admin/pregacoes/${p.id}/pulpito`" class="px-3 py-1.5 rounded text-gray-500 dark:text-slate-400 hover:text-blue-600">Púlpito</Link>
            <Link :href="`/admin/pregacoes/${p.id}/edit`" class="px-3 py-1.5 rounded text-gray-500 dark:text-slate-400 hover:text-blue-600">Editar</Link>
          </div>
        </div>
      </div>

      <!-- TABELA (desktop) -->
      <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)_110px_90px_170px] gap-4 px-6 py-3 bg-gray-50 dark:bg-slate-700/50 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">
          <span>Pregação</span>
          <span>Data</span>
          <span>Status</span>
          <span>Roteiro</span>
          <span class="text-right">Ações</span>
        </div>
        <div v-for="p in lista" :key="p.id"
             class="grid grid-cols-[minmax(0,2.2fr)_minmax(0,1fr)_110px_90px_170px] gap-4 items-center px-6 py-4 border-t border-gray-100 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
          <div class="min-w-0">
            <Link :href="`/admin/pregacoes/${p.id}/edit`" class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 truncate block">
              {{ p.titulo }}
            </Link>
            <p class="text-xs text-gray-500 dark:text-slate-400 truncate">
              {{ [p.pregador, p.versiculo].filter(Boolean).join(' · ') }}
            </p>
          </div>
          <div class="text-sm text-gray-600 dark:text-slate-300">{{ formatarData(p.data_pregacao) }}</div>
          <div><span :class="selo(p.status)">{{ rotuloStatus[p.status] }}</span></div>
          <div class="text-sm" :class="p.tem_roteiro ? 'text-gray-600 dark:text-slate-300' : 'text-gray-300 dark:text-slate-600'">
            {{ p.tem_roteiro ? 'Escrito' : '—' }}
          </div>
          <div class="flex justify-end gap-4 text-sm">
            <Link v-if="p.tem_roteiro" :href="`/admin/pregacoes/${p.id}/pulpito`"
                  class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">Púlpito</Link>
            <Link :href="`/admin/pregacoes/${p.id}/edit`"
                  class="text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">Editar</Link>
            <button v-if="ehPastor || p.status === 'rascunho'" @click="destroy(p)"
                    class="text-gray-400 dark:text-slate-500 hover:text-red-500 transition">Excluir</button>
          </div>
        </div>
      </div>
    </template>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  pregacoes: { type: Array, default: () => [] },
  ehPastor: { type: Boolean, default: false },
  youtubeConfigurado: { type: Boolean, default: false },
})

const importando = ref(false)
const criando = ref(false)

const rotuloStatus = { rascunho: 'Rascunho', publicada: 'No site', oculta: 'Oculta' }
const filtros = [
  { valor: 'todas', rotulo: 'Todas' },
  { valor: 'rascunho', rotulo: 'Rascunhos' },
  { valor: 'publicada', rotulo: 'No site' },
]
const filtro = ref('todas')
const lista = computed(() => filtro.value === 'todas' ? props.pregacoes : props.pregacoes.filter((p) => p.status === filtro.value))
const contagem = computed(() => ({
  todas: props.pregacoes.length,
  rascunho: props.pregacoes.filter((p) => p.status === 'rascunho').length,
  publicada: props.pregacoes.filter((p) => p.status === 'publicada').length,
}))

function selo(status) {
  return ['text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0 whitespace-nowrap', {
    rascunho: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
    publicada: 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400',
    oculta: 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400',
  }[status]]
}

function formatarData(data) {
  if (!data) return ''
  return new Date(data + 'T12:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })
}

function nova() {
  criando.value = true
  router.post('/admin/pregacoes', {}, { onFinish: () => { criando.value = false } })
}

function destroy(p) {
  if (confirm(`Remover a pregação "${p.titulo}"${p.tem_roteiro ? ' e o roteiro dela' : ''}?`)) {
    router.delete(`/admin/pregacoes/${p.id}`, { preserveScroll: true })
  }
}

function importarYoutube() {
  if (!props.youtubeConfigurado) {
    alert('Cadastre o canal do YouTube nos Dados da Igreja antes de importar.')
    return
  }
  if (!confirm('Importar os vídeos novos do canal? Se houver um rascunho da mesma semana, o vídeo entra nele.')) return
  importando.value = true
  router.post('/admin/pregacoes/importar-youtube', {}, {
    preserveScroll: true,
    onFinish: () => { importando.value = false },
  })
}
</script>
