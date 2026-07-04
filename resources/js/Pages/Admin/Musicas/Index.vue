<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Gestão</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Músicas</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">{{ musicas.length }} música(s) no repertório</p>
      </div>
      <Link href="/admin/musicas/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Nova Música
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- BUSCA + ORDENAÇÃO -->
    <div class="mb-6 flex flex-col sm:flex-row gap-3 sm:items-center">
      <div class="relative flex-1 max-w-md">
        <input v-model="busca" type="text" placeholder="Buscar música..."
               class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500">
          <AppIcon name="search" />
        </span>
      </div>
      <select v-model="ordenacao"
              class="rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="nome">Ordenar: A-Z</option>
        <option value="mais">Mais tocadas</option>
        <option value="descanso">Não tocadas há mais tempo</option>
      </select>
    </div>

    <!-- LISTA -->
    <div v-if="!musicasFiltradas.length"
         class="py-20 text-center text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="text-4xl mb-3">🎵</p>
      <p class="font-medium">Nenhuma música encontrada.</p>
      <Link href="/admin/musicas/create" class="mt-4 inline-block text-sm text-blue-600 dark:text-blue-400 hover:underline">
        Cadastrar primeira música
      </Link>
    </div>

    <div v-else class="space-y-2">
      <div v-for="m in musicasFiltradas" :key="m.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm flex items-center gap-3 px-4 sm:px-5 py-3.5">
        <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
          <AppIcon name="mic" size="md" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ m.nome }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 truncate">
            <template v-if="m.vezes">Tocada {{ m.vezes }}x · última: {{ m.ultima }}</template>
            <template v-else>Nunca tocada</template>
          </p>
        </div>
        <span v-if="m.tom"
              class="hidden sm:inline-block bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0">
          {{ m.tom }}
        </span>
        <div class="flex items-center gap-1 flex-shrink-0">
          <a v-if="m.link" :href="m.link" target="_blank" rel="noopener"
             class="text-xs font-semibold px-2.5 py-1.5 rounded transition"
             :class="linkClasse(m.link)" :title="m.link">
            {{ linkLabel(m.link) }}
          </a>
          <button @click="verNoCelular(m)"
                  class="text-xs font-semibold text-gray-900 dark:text-white bg-gray-900/5 dark:bg-white/10 hover:bg-gray-900/10 dark:hover:bg-white/20 px-2.5 py-1.5 rounded transition" title="Ver no celular">
            📱
          </button>
          <Link :href="`/admin/musicas/${m.id}`"
                class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
            Ver
          </Link>
          <Link :href="`/admin/musicas/${m.id}/edit`"
                class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
            Editar
          </Link>
          <button @click="musicaParaExcluir = m"
                  class="text-xs font-semibold text-gray-400 dark:text-slate-500 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
            Excluir
          </button>
        </div>
      </div>
    </div>

    <!-- APRESENTAÇÃO (ver no celular) -->
    <SetlistApresentacao v-if="musicaAtiva" :musicas="[musicaAtiva]" @close="musicaAtiva = null" />

    <!-- MODAL EXCLUSÃO -->
    <div v-if="musicaParaExcluir" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Excluir música?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ musicaParaExcluir.nome }}</strong>" será removida do repertório.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="musicaParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/musicas/${musicaParaExcluir.id}`" method="delete" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
            Excluir
          </Link>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppIcon from '@/Components/AppIcon.vue'
import SetlistApresentacao from '@/Components/SetlistApresentacao.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({ musicas: Array })

const busca = ref('')
const ordenacao = ref('nome')
const musicaParaExcluir = ref(null)
const musicaAtiva = ref(null)

// converte "dd/mm/yyyy" em número comparável (0 = nunca tocada)
function ultimaValor(m) {
  if (!m.ultima) return 0
  const [d, mes, a] = m.ultima.split('/')
  return Number(`${a}${mes}${d}`)
}

const musicasFiltradas = computed(() => {
  const q = busca.value.trim().toLowerCase()
  let lista = q ? props.musicas.filter(m => m.nome.toLowerCase().includes(q)) : [...props.musicas]

  if (ordenacao.value === 'mais') {
    lista.sort((a, b) => (b.vezes ?? 0) - (a.vezes ?? 0))
  } else if (ordenacao.value === 'descanso') {
    // nunca tocadas primeiro, depois as tocadas há mais tempo
    lista.sort((a, b) => ultimaValor(a) - ultimaValor(b))
  } else {
    lista.sort((a, b) => a.nome.localeCompare(b.nome))
  }
  return lista
})

function verNoCelular(m) {
  musicaAtiva.value = { nome: m.nome, tom: m.tom, letra: m.letra, link: m.link }
}

function linkLabel(url) {
  const u = url.toLowerCase()
  if (u.includes('spotify')) return '♫ Spotify'
  if (u.includes('youtu')) return '▶ YouTube'
  return '🔗 Link'
}
function linkClasse(url) {
  const u = url.toLowerCase()
  if (u.includes('spotify')) return 'text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30'
  if (u.includes('youtu')) return 'text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30'
  return 'text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30'
}
</script>
