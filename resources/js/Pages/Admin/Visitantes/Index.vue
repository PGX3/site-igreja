<template>
  <AdminLayout>

    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Pastoral</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Visitantes</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          {{ visitantes.length }} visitante(s) em acompanhamento
        </p>
      </div>
      <div class="self-start sm:self-auto flex flex-wrap gap-2">
        <button type="button" @click="abrirExportar"
                class="inline-flex items-center gap-2 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400
                       hover:bg-emerald-50 dark:hover:bg-emerald-900/20 px-4 py-2.5 rounded-lg text-sm font-semibold transition">
          <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
            <path :d="WHATSAPP_PATH" />
          </svg>
          Exportar para WhatsApp
        </button>
        <Link v-if="isPastor" href="/admin/visitantes/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          + Novo Visitante
        </Link>
      </div>
    </div>

    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- BUSCA -->
    <div class="mb-6 flex flex-col sm:flex-row gap-3">
      <input v-model="termo" type="text" placeholder="Buscar por nome, telefone, convidado por..."
             class="w-full sm:flex-1 sm:max-w-md border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                    bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                    placeholder-gray-400 dark:placeholder-slate-400
                    focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <button v-if="termo" type="button" @click="termo = ''"
              class="self-start sm:self-auto px-3 py-2.5 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
        Limpar
      </button>
    </div>

    <!-- CARDS (mobile) -->
    <div class="sm:hidden space-y-3">
      <div v-if="!visitantesFiltrados.length"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl py-16 text-center text-gray-400 dark:text-slate-500">
        <p class="text-4xl mb-3">◈</p>
        <p class="font-medium">Nenhum visitante encontrado.</p>
      </div>
      <div v-for="v in visitantesFiltrados" :key="v.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">

        <!-- Nome -->
        <div class="min-w-0 mb-2">
          <p class="font-semibold text-gray-900 dark:text-white break-words">{{ v.name }}</p>
          <p v-if="v.email" class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ v.email }}</p>
          <p v-if="v.como_conheceu" class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ v.como_conheceu }}</p>
          <span v-if="v.autoriza_contato === false"
                class="inline-block mt-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400"
                title="Pediu para não ser contatado pelo WhatsApp (LGPD)">Não autorizou contato</span>
        </div>

        <!-- Info: telefone, convidado por, data -->
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-slate-400 mt-2">
          <div v-if="v.telefone" class="flex items-center gap-1.5">
            <span>{{ v.telefone }}</span>
            <a :href="whatsappUrl(v.telefone)" target="_blank" rel="noopener" @click.stop
               class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 transition">
              <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                <path d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.91-7.01zM12.05 20.15h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.22 8.22 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c-.01 4.54-3.71 8.23-8.23 8.23zm4.52-6.17c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.15.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23a7.4 7.4 0 0 1-1.37-1.7c-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.17 0-.43.06-.66.31-.23.25-.86.84-.86 2.05 0 1.21.88 2.37 1 2.54.12.17 1.73 2.64 4.2 3.7.59.25 1.04.4 1.4.52.59.19 1.12.16 1.55.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.08.14-1.18-.06-.1-.22-.16-.47-.28z"/>
              </svg>
            </a>
          </div>
          <span v-if="!v.telefone">Sem telefone</span>
          <span v-if="v.convidado_por">Por {{ v.convidado_por }}</span>
          <span v-if="v.primeira_visita">{{ formatDate(v.primeira_visita) }}</span>
        </div>

        <!-- Ações -->
        <div class="flex justify-end gap-1 mt-3 -mb-1.5 -mr-2.5">
            <button @click="paraPromover = v"
                    class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 px-2.5 py-1.5 rounded transition">
              Promover
            </button>
            <Link v-if="isPastor" :href="`/admin/visitantes/${v.id}/edit`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-2.5 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Editar
            </Link>
            <Link v-else :href="`/admin/visitantes/${v.id}`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-2.5 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Visualizar
            </Link>
            <button v-if="isPastor" @click="paraExcluir = v"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-2.5 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
              Excluir
            </button>
        </div>
      </div>
    </div>

    <!-- TABELA (desktop) -->
    <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-x-clip">
      <EasyDataTable
        table-class-name="customize-table"
        :headers="headers"
        :items="visitantes"
        :search-value="termo"
        :rows-per-page="15"
        :rows-items="[10, 15, 25, 50, 100]"
        buttons-pagination
        empty-message="Nenhum visitante cadastrado."
        rows-per-page-message="Linhas por página"
        rows-of-page-separator-message="de"
      >
        <template #item-name="v">
          <p class="font-semibold text-gray-900 dark:text-white">{{ v.name }}</p>
          <p v-if="v.email" class="text-xs text-gray-400 dark:text-slate-500">{{ v.email }}</p>
          <p v-if="v.data_nascimento" class="text-xs text-gray-400 dark:text-slate-500">
            Nasc. {{ formatDate(v.data_nascimento) }}
          </p>
          <p v-if="v.como_conheceu" class="text-xs text-gray-400 dark:text-slate-500">{{ v.como_conheceu }}</p>
          <span v-if="v.autoriza_contato === false"
                class="inline-block mt-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400"
                title="Pediu para não ser contatado pelo WhatsApp (LGPD)">Não autorizou contato</span>
        </template>

        <template #item-telefone="{ name, telefone }">
          <div class="flex items-center gap-2 whitespace-nowrap">
            <span>{{ telefone || '—' }}</span>
            <a v-if="telefone" :href="whatsappUrl(telefone)" target="_blank" rel="noopener"
               :title="`Abrir WhatsApp de ${name}`" @click.stop
               class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-emerald-600 dark:text-emerald-400
                      hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition">
              <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                <path d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.91-7.01zM12.05 20.15h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.22 8.22 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c-.01 4.54-3.71 8.23-8.23 8.23zm4.52-6.17c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.15.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23a7.4 7.4 0 0 1-1.37-1.7c-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.17 0-.43.06-.66.31-.23.25-.86.84-.86 2.05 0 1.21.88 2.37 1 2.54.12.17 1.73 2.64 4.2 3.7.59.25 1.04.4 1.4.52.59.19 1.12.16 1.55.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.08.14-1.18-.06-.1-.22-.16-.47-.28z"/>
              </svg>
            </a>
          </div>
        </template>

        <template #item-convidado_por="{ convidado_por }">
          {{ convidado_por || '—' }}
        </template>

        <template #item-primeira_visita="{ primeira_visita }">
          {{ formatDate(primeira_visita) }}
        </template>

        <template #item-actions="v">
          <div class="flex justify-end gap-2">
            <button v-if="isPastor" @click="paraPromover = v"
                    class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 px-3 py-1.5 rounded transition">
              Promover
            </button>
            <Link v-if="isPastor" :href="`/admin/visitantes/${v.id}/edit`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Editar
            </Link>
            <Link v-else :href="`/admin/visitantes/${v.id}`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Visualizar
            </Link>
            <button v-if="isPastor" @click="paraExcluir = v"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
              Excluir
            </button>
          </div>
        </template>
      </EasyDataTable>
    </div>

    <!-- MODAL EXPORTAR -->
    <div v-if="exportarAberto"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         @click.self="exportarAberto = false">
      <div class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-700 flex flex-col max-h-[85vh]">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-700">
          <h3 class="font-bold text-gray-900 dark:text-white">Lista para o grupo da liderança</h3>
          <button type="button" @click="exportarAberto = false"
                  class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M18 6 6 18M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-5 overflow-y-auto space-y-4">
          <div class="flex flex-wrap gap-2">
            <button v-for="p in PERIODOS" :key="p.dias ?? 'todos'" type="button" @click="periodo = p.dias"
                    class="text-xs font-semibold px-3 py-1.5 rounded-full border transition"
                    :class="periodo === p.dias
                      ? 'bg-emerald-600 border-emerald-600 text-white'
                      : 'border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'">
              {{ p.label }}
            </button>
          </div>
          <p class="text-xs text-gray-500 dark:text-slate-400">
            {{ paraExportar.length }} visitante(s) na lista.
            <span v-if="semAutorizacao">
              {{ semAutorizacao }} pediu(ram) para não ser contatado(s) e ficou(aram) de fora.
            </span>
          </p>
          <textarea ref="msgArea" readonly rows="12"
                    class="w-full resize-none rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-900/50
                           text-sm text-gray-700 dark:text-slate-200 p-4 font-mono leading-relaxed focus:outline-none"
                    :value="mensagemExportar"></textarea>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2 px-5 py-4 border-t border-gray-100 dark:border-slate-700">
          <button type="button" @click="exportarAberto = false"
                  class="text-sm font-semibold text-gray-500 dark:text-slate-400 px-4 py-2.5 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
            Fechar
          </button>
          <button type="button" @click="copiarMensagem" :disabled="!paraExportar.length"
                  class="inline-flex items-center gap-2 text-sm font-semibold text-white px-4 py-2.5 rounded-xl shadow-sm transition-colors disabled:opacity-50"
                  :class="copiado ? 'bg-emerald-500' : 'bg-blue-600 hover:bg-blue-700'">
            <svg v-if="!copiado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
              <rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
            </svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M20 6 9 17l-5-5" /></svg>
            {{ copiado ? 'Copiado!' : 'Copiar' }}
          </button>
          <button type="button" @click="compartilharWhatsApp" :disabled="!paraExportar.length"
                  class="inline-flex items-center gap-2 text-sm font-semibold text-white px-4 py-2.5 rounded-xl shadow-sm
                         bg-emerald-600 hover:bg-emerald-700 transition-colors disabled:opacity-50">
            <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
              <path :d="WHATSAPP_PATH" />
            </svg>
            Enviar no WhatsApp
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL EXCLUIR -->
    <div v-if="paraExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Remover visitante?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ paraExcluir.name }}</strong>" será removido permanentemente.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="paraExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/visitantes/${paraExcluir.id}`" method="delete" as="button" @success="paraExcluir = null"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
            Remover
          </Link>
        </div>
      </div>
    </div>

    <!-- MODAL PROMOVER -->
    <div v-if="paraPromover"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Promover a membro?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ paraPromover.name }}</strong>" passará a ser membro da igreja. Você poderá completar
          os dados pastorais em seguida.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="paraPromover = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/visitantes/${paraPromover.id}/promover`" method="post" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
            Promover
          </Link>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ref, computed, nextTick } from 'vue'

const isPastor = computed(() => usePage().props.auth?.user?.role === 'pastor')

const props = defineProps({
  visitantes: Array,
  busca: String,
})

const termo = ref(props.busca ?? '')
const paraExcluir = ref(null)
const paraPromover = ref(null)

const visitantesFiltrados = computed(() => {
  if (!termo.value) return props.visitantes
  const q = termo.value.toLowerCase()
  return props.visitantes.filter(v =>
    v.name?.toLowerCase().includes(q) ||
    v.telefone?.includes(q) ||
    v.convidado_por?.toLowerCase().includes(q) ||
    v.email?.toLowerCase().includes(q)
  )
})

const headers = [
  { text: 'Nome', value: 'name', sortable: true },
  { text: 'Telefone', value: 'telefone' },
  { text: 'Convidado por', value: 'convidado_por', sortable: true },
  { text: '1ª visita', value: 'primeira_visita', sortable: true },
  { text: '', value: 'actions', width: 240 },
]

function formatDate(d) {
  if (!d) return '—'
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}

function whatsappUrl(telefone) {
  let d = (telefone || '').replace(/\D/g, '')
  if (d.startsWith('0')) d = d.slice(1)
  if (d.length <= 11) d = '55' + d
  return `https://wa.me/${d}`
}

// Exportar lista para o grupo da liderança
const WHATSAPP_PATH = 'M19.05 4.91A9.82 9.82 0 0 0 12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.91-7.01zM12.05 20.15h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.22 8.22 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c-.01 4.54-3.71 8.23-8.23 8.23zm4.52-6.17c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.15.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23a7.4 7.4 0 0 1-1.37-1.7c-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.17 0-.43.06-.66.31-.23.25-.86.84-.86 2.05 0 1.21.88 2.37 1 2.54.12.17 1.73 2.64 4.2 3.7.59.25 1.04.4 1.4.52.59.19 1.12.16 1.55.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.08.14-1.18-.06-.1-.22-.16-.47-.28z'

const PERIODOS = [
  { dias: 7, label: 'Últimos 7 dias' },
  { dias: 30, label: 'Últimos 30 dias' },
  { dias: 90, label: 'Últimos 3 meses' },
  { dias: null, label: 'Todos' },
]

const exportarAberto = ref(false)
const periodo = ref(7)
const copiado = ref(false)
const msgArea = ref(null)

function hojeMenos(dias) {
  const d = new Date()
  d.setDate(d.getDate() - dias)
  const pad = n => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

const doPeriodo = computed(() => {
  if (periodo.value === null) return props.visitantes
  const desde = hojeMenos(periodo.value)
  return props.visitantes.filter(v => v.primeira_visita && v.primeira_visita >= desde)
})

const paraExportar = computed(() => doPeriodo.value.filter(v => v.autoriza_contato !== false))
const semAutorizacao = computed(() => doPeriodo.value.length - paraExportar.value.length)

const mensagemExportar = computed(() => {
  const rotulo = PERIODOS.find(p => p.dias === periodo.value)?.label.toLowerCase()
  const blocos = paraExportar.value.map(v => {
    const detalhes = [
      v.primeira_visita ? `1ª visita ${formatDate(v.primeira_visita)}` : null,
      v.convidado_por ? `convidado(a) por ${v.convidado_por}` : null,
    ].filter(Boolean).join(', ')
    return [
      `👤 *${v.name}*`,
      detalhes || null,
      v.telefone ? whatsappUrl(v.telefone) : 'Sem telefone',
    ].filter(Boolean).join('\n')
  })
  return [
    `🙌 *Visitantes da igreja* (${rotulo})`,
    '',
    'Vamos acolher quem nos visitou! Mandem uma mensagem de boas-vindas pelo link de cada um:',
    '',
    blocos.join('\n\n') || 'Nenhum visitante no período.',
  ].join('\n')
})

function abrirExportar() {
  copiado.value = false
  exportarAberto.value = true
}

async function copiarMensagem() {
  try {
    await navigator.clipboard.writeText(mensagemExportar.value)
  } catch {
    if (msgArea.value) {
      msgArea.value.select()
      document.execCommand('copy')
    }
  }
  copiado.value = true
  await nextTick()
  setTimeout(() => { copiado.value = false }, 2000)
}

function compartilharWhatsApp() {
  window.open(`https://wa.me/?text=${encodeURIComponent(mensagemExportar.value)}`, '_blank', 'noopener')
}
</script>
