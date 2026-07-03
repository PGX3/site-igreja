<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Gestão</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Escalas</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">{{ escalas.length }} escala(s) encontrada(s)</p>
      </div>
      <Link href="/admin/escalas/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Nova Escala
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- FILTRO STATUS -->
    <div class="flex gap-2 mb-6 flex-wrap">
      <button v-for="s in statusOptions" :key="s.value"
              @click="filtroStatus = filtroStatus === s.value ? null : s.value"
              class="px-3 py-1.5 rounded-full text-xs font-semibold border transition"
              :class="filtroStatus === s.value ? s.activeClass : 'border-gray-200 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700'">
        {{ s.label }}
      </button>
    </div>

    <!-- LISTA -->
    <div class="space-y-3">

      <!-- VAZIO -->
      <div v-if="!escalasFiltered.length" class="py-20 text-center text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
        <p class="text-4xl mb-3">◱</p>
        <p class="font-medium">Nenhuma escala encontrada.</p>
        <Link href="/admin/escalas/create"
              class="mt-4 inline-block text-sm text-blue-600 dark:text-blue-400 hover:underline">
          Criar primeira escala
        </Link>
      </div>

      <div v-for="e in escalasFiltered" :key="e.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">

        <!-- MOBILE -->
        <div class="sm:hidden">
          <div class="p-4">
            <!-- Data + título + status -->
            <div class="flex items-start gap-3 mb-3">
              <div class="text-center min-w-[42px] flex-shrink-0">
                <p class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{ diaDoMes(e.data) }}</p>
                <p class="text-[10px] font-bold uppercase text-gray-400 dark:text-slate-500">{{ mesAbrev(e.data) }}</p>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <p class="font-bold text-gray-900 dark:text-white">{{ e.titulo }}</p>
                  <span :class="statusClass(e.status)"
                        class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0">
                    {{ statusLabel(e.status) }}
                  </span>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
                  {{ e.grupo?.nome }} · {{ e.hora_inicio }} – {{ e.hora_fim }}
                </p>
                <span v-if="e.vinculo"
                      :class="e.vinculo.tipo === 'culto'
                        ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800'
                        : 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800'"
                      class="inline-block mt-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border">
                  {{ e.vinculo.tipo === 'culto' ? 'Culto' : 'Evento' }}: {{ e.vinculo.nome }}
                </span>
              </div>
              <!-- Confirmados -->
              <div class="text-center flex-shrink-0 ml-auto">
                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ e.confirmados }}/{{ e.total_membros }}</p>
                <p class="text-[10px] text-gray-400 dark:text-slate-500">confirm.</p>
              </div>
            </div>
          </div>
          <!-- Ações -->
          <div class="px-4 py-3 border-t border-gray-100 dark:border-slate-700/50 flex gap-1 flex-wrap">
            <Link :href="`/admin/escalas/${e.id}`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Ver
            </Link>
            <Link :href="`/admin/escalas/${e.id}/edit`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Editar
            </Link>
            <button @click="abrirCompartilhar(e)"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-green-600 dark:hover:text-green-400 px-3 py-1.5 rounded hover:bg-green-50 dark:hover:bg-green-900/20 transition">
              Compartilhar
            </button>
            <button @click="duplicar(e)"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Duplicar
            </button>
            <button @click="confirmarExclusao(e)"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
              Excluir
            </button>
          </div>
        </div>

        <!-- DESKTOP -->
        <div class="hidden sm:flex items-center gap-5 p-5">
          <!-- Data -->
          <div class="text-center min-w-[54px]">
            <p class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{ diaDoMes(e.data) }}</p>
            <p class="text-[11px] font-bold uppercase text-gray-400 dark:text-slate-500">{{ mesAbrev(e.data) }}</p>
          </div>
          <!-- Divider -->
          <div class="w-px h-10 bg-gray-100 dark:bg-slate-700"></div>
          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-0.5">
              <p class="font-bold text-gray-900 dark:text-white truncate">{{ e.titulo }}</p>
              <span :class="statusClass(e.status)"
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0">
                {{ statusLabel(e.status) }}
              </span>
            </div>
            <p class="text-xs text-gray-400 dark:text-slate-500 flex items-center gap-2 flex-wrap">
              <span>{{ e.grupo?.nome }} · {{ e.hora_inicio }} – {{ e.hora_fim }}</span>
              <span v-if="e.vinculo"
                    :class="e.vinculo.tipo === 'culto'
                      ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800'
                      : 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800'"
                    class="text-[10px] font-semibold px-2 py-0.5 rounded-full border">
                {{ e.vinculo.tipo === 'culto' ? 'Culto' : 'Evento' }}: {{ e.vinculo.nome }}
              </span>
            </p>
          </div>
          <!-- Confirmados -->
          <div class="text-center px-4">
            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ e.confirmados }}/{{ e.total_membros }}</p>
            <p class="text-[10px] text-gray-400 dark:text-slate-500">confirmados</p>
          </div>
          <!-- Ações -->
          <div class="flex gap-2 flex-shrink-0">
            <Link :href="`/admin/escalas/${e.id}`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Ver
            </Link>
            <Link :href="`/admin/escalas/${e.id}/edit`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Editar
            </Link>
            <button @click="abrirCompartilhar(e)"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-green-600 dark:hover:text-green-400 px-3 py-1.5 rounded hover:bg-green-50 dark:hover:bg-green-900/20 transition">
              Compartilhar
            </button>
            <button @click="duplicar(e)"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Duplicar
            </button>
            <button @click="confirmarExclusao(e)"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
              Excluir
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- MODAL COMPARTILHAR -->
    <div v-if="escalaCompartilhar"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
         @click.self="fecharCompartilhar">
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-lg w-full max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
          <h3 class="font-bold text-gray-900 dark:text-white">Compartilhar escala</h3>
          <button @click="fecharCompartilhar"
                  class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 text-xl leading-none">×</button>
        </div>

        <div class="px-6 py-4 overflow-y-auto">
          <p class="text-xs text-gray-500 dark:text-slate-400 mb-2">
            Texto pronto para colar no WhatsApp:
          </p>
          <textarea ref="textoRef" :value="textoCompartilhar" readonly
                    class="w-full h-72 text-sm font-mono bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg p-3 text-gray-700 dark:text-slate-200 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700 flex flex-wrap gap-3 justify-end">
          <button @click="fecharCompartilhar"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Fechar
          </button>
          <button @click="copiarTexto"
                  class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
            {{ copiado ? 'Copiado!' : 'Copiar texto' }}
          </button>
          <a :href="linkWhatsApp" target="_blank" rel="noopener"
             class="px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
            Abrir WhatsApp
          </a>
        </div>
      </div>
    </div>

    <!-- MODAL EXCLUSÃO -->
    <div v-if="escalaParaExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Excluir escala?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ escalaParaExcluir.titulo }}</strong>" e todos os membros escalados serão removidos.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="escalaParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/escalas/${escalaParaExcluir.id}`" method="delete" as="button"
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
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({ escalas: Array })

const filtroStatus = ref(null)
const escalaParaExcluir = ref(null)
const escalaCompartilhar = ref(null)
const copiado = ref(false)
const textoRef = ref(null)

const statusOptions = [
  { value: 'pendente',     label: 'Pendente',      activeClass: 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400' },
  { value: 'confirmada',   label: 'Confirmada',    activeClass: 'border-green-400 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' },
  { value: 'em_andamento', label: 'Em andamento',  activeClass: 'border-blue-400 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' },
  { value: 'concluida',    label: 'Concluída',     activeClass: 'border-gray-400 bg-gray-50 dark:bg-slate-700 text-gray-700 dark:text-slate-300' },
  { value: 'cancelada',    label: 'Cancelada',     activeClass: 'border-red-400 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' },
]

const escalasFiltered = computed(() =>
  filtroStatus.value
    ? props.escalas.filter(e => e.status === filtroStatus.value)
    : props.escalas
)

function diaDoMes(data) { return data ? new Date(data + 'T12:00:00').getDate() : '—' }
function mesAbrev(data) {
  if (!data) return ''
  return new Date(data + 'T12:00:00').toLocaleString('pt-BR', { month: 'short' }).replace('.', '')
}

function statusLabel(s) {
  return { pendente: 'Pendente', confirmada: 'Confirmada', em_andamento: 'Em andamento', concluida: 'Concluída', cancelada: 'Cancelada' }[s] ?? s
}
function statusClass(s) {
  return {
    pendente:     'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400',
    confirmada:   'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400',
    em_andamento: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400',
    concluida:    'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-300',
    cancelada:    'bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400',
  }[s] ?? 'bg-gray-50 dark:bg-slate-700 text-gray-400 dark:text-slate-400'
}

function confirmarExclusao(e) { escalaParaExcluir.value = e }

function duplicar(e) {
  router.post(`/admin/escalas/${e.id}/duplicar`, {}, { preserveScroll: true })
}

function abrirCompartilhar(e) {
  escalaCompartilhar.value = e
  copiado.value = false
}
function fecharCompartilhar() {
  escalaCompartilhar.value = null
  copiado.value = false
}

function formatarDataExt(data) {
  if (!data) return ''
  return new Date(data + 'T12:00:00')
    .toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}
function formatarHora(h) {
  return h ? String(h).slice(0, 5) : ''
}

const textoCompartilhar = computed(() => {
  const e = escalaCompartilhar.value
  if (!e) return ''

  const linhas = []
  linhas.push(`*${e.titulo}*`)
  linhas.push(formatarDataExt(e.data))

  const hi = formatarHora(e.hora_inicio)
  const hf = formatarHora(e.hora_fim)
  if (hi || hf) linhas.push(`${hi}${hf ? ' às ' + hf : ''}`)

  if (e.vinculo) {
    const tipo = e.vinculo.tipo === 'culto' ? 'Culto' : 'Evento'
    linhas.push(`${tipo}: ${e.vinculo.nome}`)
  }
  if (e.grupo?.nome) linhas.push(`Grupo: ${e.grupo.nome}`)

  linhas.push('')
  linhas.push('*Participantes:*')

  if (e.membros?.length) {
    e.membros.forEach(m => {
      const nome = m.nome ?? 'Sem nome'
      linhas.push(m.funcao ? `- ${nome}: ${m.funcao}` : `- ${nome}`)
    })
  } else {
    linhas.push('Nenhum participante escalado.')
  }

  return linhas.join('\n')
})

const linkWhatsApp = computed(() =>
  `https://wa.me/?text=${encodeURIComponent(textoCompartilhar.value)}`
)

async function copiarTexto() {
  const texto = textoCompartilhar.value
  try {
    await navigator.clipboard.writeText(texto)
  } catch {
    if (textoRef.value) {
      textoRef.value.focus()
      textoRef.value.select()
      document.execCommand('copy')
    }
  }
  copiado.value = true
  setTimeout(() => { copiado.value = false }, 2000)
}
</script>
