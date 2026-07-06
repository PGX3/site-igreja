<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-2">
          Intercessão
        </p>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Pedidos de Oração
        </h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          {{ pedidos.length }} recebido(s) · {{ novos }} novo(s)
        </p>
      </div>

      <div class="flex flex-wrap gap-2 self-start sm:self-auto">
        <button type="button" @click="abrirModalNovo"
                class="inline-flex items-center gap-2 text-sm font-semibold
                       text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-800
                       border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700
                       px-4 py-2.5 rounded-xl shadow-sm transition-colors">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
            <path d="M12 5v14M5 12h14" />
          </svg>
          Adicionar pedido
        </button>

        <button type="button" @click="abrirModal"
                class="inline-flex items-center gap-2 text-sm font-semibold text-white
                       bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600
                       px-4 py-2.5 rounded-xl shadow-sm transition-colors">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
            <rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
          </svg>
          Mensagem para o grupo
        </button>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="flex gap-2 flex-wrap mb-6">
      <button
        v-for="f in filtros" :key="f.valor"
        @click="filtro = f.valor"
        class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
        :class="filtro === f.valor
          ? 'bg-blue-600 text-white border-blue-600'
          : 'border-gray-300 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'"
      >
        {{ f.label }}
        <span class="ml-1 opacity-60">{{ contagem(f.valor) }}</span>
      </button>
    </div>

    <!-- EMPTY -->
    <div v-if="filtrados.length === 0"
         class="text-center py-16 text-gray-400 dark:text-slate-500 text-sm">
      Nenhum pedido encontrado.
    </div>

    <!-- LISTA -->
    <div class="space-y-3">
      <div v-for="p in filtrados" :key="p.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">

        <!-- CONTEÚDO -->
        <div class="p-4 sm:p-6">
          <div class="flex items-start justify-between gap-2 mb-3">
            <div class="flex items-center gap-2 flex-wrap min-w-0">
              <span class="font-semibold text-gray-900 dark:text-white text-sm">
                {{ p.anonimo ? 'Anônimo' : p.nome }}
              </span>
              <span v-if="p.anonimo"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded flex-shrink-0">
                Anônimo
              </span>
              <span class="text-xs font-semibold px-2 py-0.5 rounded flex-shrink-0"
                    :class="statusMeta[p.status].badge">
                {{ statusMeta[p.status].label }}
              </span>
              <span v-if="p.compartilhar"
                    class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded flex-shrink-0">
                Autorizou compartilhar
              </span>
            </div>
            <span class="text-xs text-gray-400 dark:text-slate-500 flex-shrink-0">{{ formatDate(p.created_at) }}</span>
          </div>

          <p class="text-gray-700 dark:text-slate-300 text-sm leading-relaxed border-l-4 border-blue-100 dark:border-blue-800 pl-4 italic">
            "{{ p.pedido }}"
          </p>
        </div>

        <!-- AÇÕES -->
        <div class="px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-slate-700/50 flex gap-2 justify-end flex-wrap">
          <button
            v-for="s in statusOpcoes" :key="s.valor"
            @click="mudarStatus(p, s.valor)"
            :disabled="p.status === s.valor"
            class="text-xs font-semibold px-3 py-2 rounded-lg border transition"
            :class="p.status === s.valor
              ? s.ativo
              : 'border-gray-300 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700'"
          >
            {{ s.label }}
          </button>
          <button
            @click="excluir(p)"
            class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:text-red-500 hover:border-red-300 dark:hover:border-red-700 transition"
          >
            Excluir
          </button>
        </div>

      </div>
    </div>

    <!-- MODAL MENSAGEM PARA O GRUPO -->
    <Transition name="fade">
      <div v-if="modalAberta"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
           @click.self="modalAberta = false">
        <div class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Mensagem para o grupo</h2>
            <button type="button" @click="modalAberta = false"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 transition-colors">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M18 6 6 18M6 6l12 12" /></svg>
            </button>
          </div>

          <div class="p-6">
            <p v-if="pedidosCompartilhaveis.length === 0"
               class="text-center py-8 text-gray-400 dark:text-slate-500 text-sm">
              Nenhum pedido ativo autorizado a compartilhar no momento.
            </p>

            <template v-else>
              <p class="text-xs text-gray-500 dark:text-slate-400 mb-3">
                {{ pedidosCompartilhaveis.length }} pedido(s) autorizado(s) e ativo(s).
              </p>
              <textarea ref="msgArea" readonly rows="12"
                        class="w-full resize-none rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-900/50
                               text-sm text-gray-700 dark:text-slate-200 p-4 font-mono leading-relaxed focus:outline-none"
                        :value="mensagemGrupo"></textarea>

              <div class="flex flex-wrap gap-2 justify-end mt-4">
                <button type="button" @click="copiarMensagem"
                        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2.5 rounded-xl border transition-colors"
                        :class="copiado
                          ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                          : 'border-gray-300 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700'">
                  <svg v-if="!copiado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                    <rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                  </svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M20 6 9 17l-5-5" /></svg>
                  {{ copiado ? 'Copiado!' : 'Copiar' }}
                </button>
                <button type="button" @click="compartilharWhatsApp"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-white px-4 py-2.5 rounded-xl shadow-sm
                               bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 transition-colors">
                  <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                    <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21 5.46 0 9.91-4.45 9.91-9.91C21.95 6.45 17.5 2 12.04 2m0 18.15c-1.52 0-3.01-.41-4.31-1.18l-.31-.18-3.12.82.83-3.04-.2-.32a8.2 8.2 0 0 1-1.26-4.36c0-4.54 3.7-8.23 8.24-8.23 4.54 0 8.23 3.69 8.23 8.23 0 4.54-3.69 8.24-8.23 8.24m4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43-.14-.01-.31-.01-.48-.01-.17 0-.43.06-.66.31-.23.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.14-1.18-.06-.11-.22-.17-.47-.29" />
                  </svg>
                  Compartilhar no WhatsApp
                </button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </Transition>

    <!-- MODAL NOVO PEDIDO -->
    <Transition name="fade">
      <div v-if="modalNovoAberta"
           class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
           @click.self="fecharModalNovo">
        <div class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Adicionar pedido de oração</h2>
            <button type="button" @click="fecharModalNovo"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 transition-colors">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M18 6 6 18M6 6l12 12" /></svg>
            </button>
          </div>

          <form @submit.prevent="salvarNovo" class="p-6 flex flex-col gap-5">
            <p class="text-xs text-gray-500 dark:text-slate-400">
              Use para registrar pedidos que chegaram por outros canais (grupo, presencial).
            </p>

            <!-- ANÔNIMO -->
            <label class="flex items-center gap-3 cursor-pointer">
              <input v-model="formNovo.anonimo" type="checkbox"
                     class="w-4 h-4 text-blue-600 border-gray-300 dark:border-slate-500 rounded focus:ring-blue-500" />
              <span class="text-sm text-gray-600 dark:text-slate-300">Anônimo</span>
            </label>

            <!-- NOME -->
            <div v-if="!formNovo.anonimo">
              <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
                Nome
              </label>
              <input v-model="formNovo.nome" type="text"
                     class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm
                            bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
              <p v-if="formNovo.errors.nome" class="text-red-500 text-xs mt-1">{{ formNovo.errors.nome }}</p>
            </div>

            <!-- PEDIDO -->
            <div>
              <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
                Pedido
              </label>
              <textarea v-model="formNovo.pedido" rows="4"
                        class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm
                               bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none" />
              <p v-if="formNovo.errors.pedido" class="text-red-500 text-xs mt-1">{{ formNovo.errors.pedido }}</p>
            </div>

            <!-- COMPARTILHAR -->
            <label class="flex items-start gap-3 cursor-pointer">
              <input v-model="formNovo.compartilhar" type="checkbox"
                     class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 dark:border-slate-500 rounded focus:ring-blue-500" />
              <span class="text-sm text-gray-600 dark:text-slate-300">
                Autorizado a compartilhar com a comunidade (aparece na mensagem para o grupo)
              </span>
            </label>

            <div class="flex items-center justify-end gap-3 pt-2">
              <button type="button" @click="fecharModalNovo"
                      class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
                Cancelar
              </button>
              <button type="submit" :disabled="formNovo.processing"
                      class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
                {{ formNovo.processing ? 'Salvando...' : 'Adicionar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ pedidos: Array })
const filtro = ref('todos')

// ─── Adicionar pedido manualmente ────────────────────────────────────────────
const modalNovoAberta = ref(false)
const formNovo = useForm({ nome: '', pedido: '', anonimo: false, compartilhar: true })

function abrirModalNovo() {
  formNovo.reset()
  formNovo.clearErrors()
  modalNovoAberta.value = true
}

function fecharModalNovo() {
  modalNovoAberta.value = false
}

function salvarNovo() {
  formNovo.post('/admin/pedidos-oracao', {
    preserveScroll: true,
    onSuccess: () => { modalNovoAberta.value = false },
  })
}

const statusMeta = {
  novo:      { label: 'Novo',      badge: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30' },
  em_oracao: { label: 'Em Oração', badge: 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30' },
  inativo:   { label: 'Inativo',   badge: 'text-gray-500 dark:text-slate-400 bg-gray-100 dark:bg-slate-700' },
  concluido: { label: 'Concluído', badge: 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30' },
}

const statusOpcoes = [
  { valor: 'novo',      label: 'Novo',      ativo: 'bg-blue-600 text-white border-blue-600' },
  { valor: 'em_oracao', label: 'Em Oração', ativo: 'bg-amber-500 text-white border-amber-500' },
  { valor: 'inativo',   label: 'Inativo',   ativo: 'bg-gray-500 text-white border-gray-500' },
  { valor: 'concluido', label: 'Concluído', ativo: 'bg-emerald-600 text-white border-emerald-600' },
]

const filtros = [
  { valor: 'todos',     label: 'Todos' },
  { valor: 'novo',      label: 'Novos' },
  { valor: 'em_oracao', label: 'Em Oração' },
  { valor: 'inativo',   label: 'Inativos' },
  { valor: 'concluido', label: 'Concluídos' },
]

const filtrados = computed(() =>
  filtro.value === 'todos'
    ? props.pedidos
    : props.pedidos.filter(p => p.status === filtro.value)
)

const novos = computed(() => props.pedidos.filter(p => p.status === 'novo').length)

function contagem(valor) {
  return valor === 'todos'
    ? props.pedidos.length
    : props.pedidos.filter(p => p.status === valor).length
}

function formatDate(d) {
  return new Date(d).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })
}

function mudarStatus(p, status) {
  router.patch(`/admin/pedidos-oracao/${p.id}/status`, { status }, { preserveScroll: true })
}

function excluir(p) {
  if (confirm('Excluir este pedido?')) {
    router.delete(`/admin/pedidos-oracao/${p.id}`, { preserveScroll: true })
  }
}

// ─── Mensagem para o grupo ───────────────────────────────────────────────────
const modalAberta = ref(false)
const copiado = ref(false)
const msgArea = ref(null)

const pedidosCompartilhaveis = computed(() =>
  props.pedidos.filter(p => p.compartilhar && ['novo', 'em_oracao'].includes(p.status))
)

const mensagemGrupo = computed(() => {
  const linhas = pedidosCompartilhaveis.value.map(p =>
    `- ${p.anonimo ? 'Anônimo' : p.nome}: ${p.pedido}`
  )
  return [
    '*Pedidos de oração*',
    '',
    'Vamos interceder juntos pelos pedidos da nossa comunidade:',
    '',
    ...linhas,
    '',
    'Participe do nosso culto de oração, toda quarta-feira às 20h.',
  ].join('\n')
})

function abrirModal() {
  copiado.value = false
  modalAberta.value = true
}

async function copiarMensagem() {
  try {
    await navigator.clipboard.writeText(mensagemGrupo.value)
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
  const url = `https://wa.me/?text=${encodeURIComponent(mensagemGrupo.value)}`
  window.open(url, '_blank')
}
</script>
