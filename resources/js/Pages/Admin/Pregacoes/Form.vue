<template>
  <AdminLayout>

    <!-- TOPO -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <Link href="/admin/pregacoes"
            class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
        ← Pregações
      </Link>
      <div class="flex flex-wrap items-center gap-3">
        <span class="w-full sm:w-auto text-xs flex items-center gap-1.5" :class="statusCor">
          <span class="w-1.5 h-1.5 rounded-full" :class="statusPonto"></span>
          {{ statusTexto }}
        </span>
        <button type="button" @click="previa = true" :class="botaoSecundario">
          Ver como no iPad
        </button>
        <button type="button" @click="abrirPulpito"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
          Modo púlpito
        </button>
      </div>
    </div>

    <!-- DADOS DA PREGAÇÃO -->
    <div class="mb-6">
      <div class="flex items-start gap-3">
        <input v-model="dados.titulo" type="text" maxlength="150" placeholder="Título da pregação"
               class="flex-1 min-w-0 bg-transparent border-0 p-0 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white
                      placeholder-gray-300 dark:placeholder-slate-600 focus:ring-0 outline-none" />
        <span class="mt-2 flex-shrink-0 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" :class="seloStatus.classe">
          {{ seloStatus.texto }}
        </span>
      </div>
      <p v-if="erros.titulo" class="text-red-500 text-xs mt-1">{{ erros.titulo }}</p>

      <div class="mt-4 grid gap-3 sm:grid-cols-[auto_minmax(0,1fr)_minmax(0,1fr)] text-sm">
        <label :class="rotulo">
          <span>Data</span>
          <input v-model="dados.data_pregacao" type="date" :class="campoPequeno" />
        </label>
        <label :class="rotulo">
          <span>Pregador</span>
          <template v-if="ehPastor">
            <select v-model="dados.pregador_id" :class="campoPequeno">
              <option v-for="p in pregadores" :key="p.id" :value="p.id">{{ p.name }}</option>
              <option :value="null">Outro (convidado)…</option>
            </select>
          </template>
          <span v-else class="py-1.5 normal-case tracking-normal font-normal text-sm text-gray-900 dark:text-white">{{ pregacao.pregador }}</span>
        </label>
        <label :class="rotulo">
          <span>Texto base</span>
          <input v-model="dados.versiculo" type="text" maxlength="120" placeholder="Ex.: João 15:1-8" :class="campoPequeno" />
        </label>
        <label v-if="ehPastor && dados.pregador_id === null" :class="rotulo + ' sm:col-start-2'">
          <span>Nome do convidado</span>
          <input v-model="dados.pregador" type="text" maxlength="120" placeholder="Ex.: Pr. Fulano" :class="campoPequeno" />
        </label>
      </div>
    </div>

    <!-- PUBLICAÇÃO NO SITE (pastor) -->
    <details v-if="ehPastor" :open="status !== 'rascunho'"
             class="mb-6 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm group">
      <summary class="cursor-pointer select-none list-none px-5 py-4 flex items-center justify-between gap-3">
        <span>
          <span class="font-semibold text-gray-900 dark:text-white">Publicação no site</span>
          <span class="block text-xs text-gray-500 dark:text-slate-400 mt-0.5">
            {{ status === 'rascunho' ? 'Depois do culto, cole o link do YouTube para a pregação aparecer no site.' : (status === 'publicada' ? 'Aparece no site com o vídeo.' : 'Tem vídeo, mas está escondida do site.') }}
          </span>
        </span>
        <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6" /></svg>
      </summary>
      <div class="px-5 pb-5 grid gap-4 lg:grid-cols-2">
        <div class="space-y-4">
          <label class="block">
            <span :class="rotuloBloco">Link do YouTube</span>
            <input v-model.trim="dados.youtube_url" type="url" placeholder="https://www.youtube.com/watch?v=..." :class="campo" />
            <p v-if="erros.youtube_url" class="text-red-500 text-xs mt-1">{{ erros.youtube_url }}</p>
          </label>
          <label class="block">
            <span :class="rotuloBloco">Descrição (aparece na página da pregação)</span>
            <textarea v-model="dados.descricao" rows="4" :class="campo + ' resize-none'"></textarea>
          </label>
          <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-slate-300">
            <input v-model="dados.ativo" type="checkbox" class="w-4 h-4 rounded border-gray-300 dark:border-slate-500 text-blue-600 focus:ring-blue-500" />
            Exibir no site (quando tiver o vídeo)
          </label>
        </div>
        <div v-if="videoValido"><MediaEmbed :url="dados.youtube_url" /></div>
      </div>
    </details>

    <div class="flex items-end justify-between mb-2">
      <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">Roteiro</p>
      <p class="text-xs text-gray-400 dark:text-slate-500">{{ ehPastor ? 'Só o pregador e os pastores veem.' : 'Só você e o pastor veem.' }} No culto, aparece no iPad do púlpito.</p>
    </div>

    <!-- EDITOR -->
    <div class="rounded-xl overflow-hidden border border-slate-800 shadow-sm bg-[#0B1015]">

      <!-- BARRA DE FERRAMENTAS -->
      <div v-if="editor"
           class="sticky top-0 z-10 flex flex-wrap items-center gap-1 px-2 py-2 border-b border-slate-800 bg-[#10161D]">
        <button type="button" @click="cmd().toggleBold().run()" :class="btn(editor.isActive('bold'))" title="Negrito (Ctrl+B)">
          <span class="font-bold">B</span>
        </button>
        <button type="button" @click="cmd().toggleItalic().run()" :class="btn(editor.isActive('italic'))" title="Itálico (Ctrl+I)">
          <span class="italic font-serif">I</span>
        </button>
        <button type="button" @click="cmd().toggleUnderline().run()" :class="btn(editor.isActive('underline'))" title="Sublinhado (Ctrl+U)">
          <span class="underline">U</span>
        </button>

        <span :class="divisor"></span>

        <button type="button" @click="cmd().toggleHeading({ level: 2 }).run()" :class="btn(editor.isActive('heading', { level: 2 }))" title="Título de tópico">
          <span class="font-bold text-sm">T</span>
        </button>
        <button type="button" @click="cmd().toggleHeading({ level: 3 }).run()" :class="btn(editor.isActive('heading', { level: 3 }))" title="Subtítulo">
          <span class="font-bold text-[11px]">T</span>
        </button>
        <button type="button" @click="cmd().toggleBulletList().run()" :class="btn(editor.isActive('bulletList'))" title="Lista">
          <span class="text-base leading-none">•</span>
        </button>
        <button type="button" @click="cmd().toggleOrderedList().run()" :class="btn(editor.isActive('orderedList'))" title="Lista numerada">
          <span class="text-[11px] font-semibold">1.</span>
        </button>
        <button type="button" @click="cmd().toggleBlockquote().run()" :class="btn(editor.isActive('blockquote'))" title="Versículo / citação">
          <span class="font-serif text-lg leading-none">”</span>
        </button>

        <span :class="divisor"></span>

        <!-- TAMANHO DA LETRA -->
        <div class="flex items-center rounded-md bg-slate-900/60 p-0.5" title="Tamanho da letra">
          <button v-for="t in tamanhos" :key="t.rotulo" type="button" @click="aplicarTamanho(t.valor)"
                  class="h-6 min-w-[26px] px-1.5 rounded text-xs font-semibold transition"
                  :class="tamanhoAtivo(t.valor) ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white'">
            {{ t.rotulo }}
          </button>
        </div>

        <span :class="divisor"></span>

        <!-- MARCA-TEXTO -->
        <span class="text-[10px] uppercase tracking-wider text-slate-500 ml-1 mr-0.5 hidden md:inline">Marcar</span>
        <button v-for="m in marcaTexto" :key="m.cor" type="button" @click="cmd().setBackgroundColor(m.cor).run()"
                class="w-6 h-6 rounded-md border border-slate-700 transition hover:scale-110 flex items-center justify-center"
                :style="{ backgroundColor: m.cor }" :title="`Marca-texto ${m.nome}`">
          <span v-if="editor.isActive('textStyle', { backgroundColor: m.cor })" class="w-1.5 h-1.5 rounded-full bg-white"></span>
        </button>
        <button type="button" @click="cmd().unsetBackgroundColor().run()" :class="btn(false)" title="Tirar marca-texto">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
        </button>

        <span :class="divisor"></span>

        <!-- COR DA LETRA -->
        <span class="text-[10px] uppercase tracking-wider text-slate-500 ml-1 mr-0.5 hidden md:inline">Cor</span>
        <button v-for="c in coresTexto" :key="c.cor" type="button" @click="cmd().setColor(c.cor).run()"
                class="w-6 h-6 rounded-full border border-slate-700 transition hover:scale-110 flex items-center justify-center"
                :style="{ backgroundColor: c.cor }" :title="`Letra ${c.nome}`">
          <span v-if="editor.isActive('textStyle', { color: c.cor })" class="w-1.5 h-1.5 rounded-full bg-black/60"></span>
        </button>
        <button type="button" @click="cmd().unsetColor().run()" title="Letra branca (padrão)"
                class="w-6 h-6 rounded-full border border-slate-600 bg-slate-100 transition hover:scale-110"></button>

        <span class="flex-1"></span>

        <button type="button" @click="cmd().undo().run()" :disabled="!editor.can().undo()" :class="btn(false)" title="Desfazer (Ctrl+Z)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 14L4 9l5-5" /><path d="M4 9h11a5 5 0 010 10h-3" /></svg>
        </button>
        <button type="button" @click="cmd().redo().run()" :disabled="!editor.can().redo()" :class="btn(false)" title="Refazer">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 14l5-5-5-5" /><path d="M20 9H9a5 5 0 000 10h3" /></svg>
        </button>
      </div>

      <!-- FOLHA -->
      <EditorContent :editor="editor" class="roteiro px-5 sm:px-10 py-8 text-slate-100 text-[19px] leading-[1.6]" />
    </div>

    <div class="mt-6 flex justify-between items-center text-xs text-gray-400 dark:text-slate-500">
      <span>Salva sozinho enquanto você escreve.</span>
      <button v-if="ehPastor || status === 'rascunho'" type="button" @click="excluir" class="hover:text-red-500 transition">Excluir pregação</button>
    </div>

    <!-- PRÉVIA DO iPAD -->
    <Teleport to="body">
      <div v-if="previa" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex flex-col items-center justify-center p-4 sm:p-8"
           @click.self="previa = false">
        <div class="w-full max-w-5xl flex flex-wrap items-center justify-between gap-3 mb-4 text-white">
          <div>
            <p class="font-semibold">Como fica no iPad do púlpito</p>
            <p class="text-xs text-slate-400">iPad deitado. No culto, o pregador também pode aumentar com dois dedos.</p>
          </div>
          <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-slate-300">
              Letra
              <input v-model.number="dados.roteiro_fonte" type="range" :min="fonteMin" :max="fonteMax" step="1" class="w-36 accent-sky-400" />
              <span class="w-10 tabular-nums">{{ dados.roteiro_fonte }}</span>
            </label>
            <button type="button" @click="previa = false"
                    class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-white/10 hover:bg-white/20 transition">Fechar</button>
          </div>
        </div>

        <!-- Moldura 1024×768 (pontos do iPad mini) escalada para caber -->
        <div ref="moldura" class="w-full max-w-5xl">
          <div class="relative rounded-[28px] bg-[#1a1a1a] p-[14px] shadow-2xl" :style="{ height: (alturaTela + 28) + 'px' }">
            <div class="origin-top-left overflow-hidden rounded-md" :style="{ width: '1024px', height: '768px', transform: `scale(${escala})` }">
              <div class="w-full h-full flex bg-[#05080B] text-slate-100">
                <!-- texto -->
                <div class="flex-1 overflow-y-auto px-12 py-10">
                  <p class="text-[13px] uppercase tracking-[0.25em] text-sky-400/80 mb-2">{{ dados.versiculo || ' ' }}</p>
                  <h1 class="font-bold mb-6" :style="{ fontSize: (dados.roteiro_fonte * 1.4) + 'px', lineHeight: 1.2 }">{{ dados.titulo }}</h1>
                  <div class="roteiro" :style="{ fontSize: dados.roteiro_fonte + 'px', lineHeight: 1.5 }" v-html="dados.roteiro"></div>
                </div>
                <!-- controles do Spresenter -->
                <div class="w-[150px] flex-shrink-0 border-l border-white/10 flex flex-col items-center justify-between py-6">
                  <div class="text-center">
                    <p class="text-3xl font-light tabular-nums">{{ relogio }}</p>
                    <p class="text-[11px] uppercase tracking-widest text-slate-500 mt-1">agora</p>
                  </div>
                  <div class="flex flex-col items-center gap-4">
                    <div class="w-[110px] h-[110px] rounded-2xl bg-sky-500 flex items-center justify-center text-white text-4xl">▶</div>
                    <p class="text-sm text-slate-400 tabular-nums">slide 3 / 17</p>
                    <div class="w-[110px] h-[70px] rounded-2xl bg-white/10 flex items-center justify-center text-slate-300 text-2xl">◀</div>
                  </div>
                  <p class="text-[10px] uppercase tracking-widest text-slate-600">Spresenter</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import { Link, router } from '@inertiajs/vue3'
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { TextStyle, Color, BackgroundColor, FontSize } from '@tiptap/extension-text-style'

const props = defineProps({
  pregacao: { type: Object, required: true },
  ehPastor: { type: Boolean, default: false },
  pregadores: { type: Array, default: () => [] },
  fonteMin: { type: Number, default: 16 },
  fonteMax: { type: Number, default: 48 },
})

// Cores pensadas para fundo escuro (púlpito): o marca-texto é translúcido, então a letra branca continua legível.
const marcaTexto = [
  { nome: 'amarelo', cor: 'rgba(250, 204, 21, 0.38)' },
  { nome: 'verde',   cor: 'rgba(74, 222, 128, 0.32)' },
  { nome: 'azul',    cor: 'rgba(0, 167, 255, 0.38)' },
  { nome: 'rosa',    cor: 'rgba(244, 114, 182, 0.38)' },
]
const coresTexto = [
  { nome: 'amarela', cor: '#FACC15' },
  { nome: 'azul',    cor: '#38BDF8' },
  { nome: 'vermelha', cor: '#F87171' },
  { nome: 'verde',   cor: '#4ADE80' },
]
// Tamanhos relativos (em): no iPad escalam junto com o tamanho base escolhido.
const tamanhos = [
  { rotulo: 'P',  valor: '0.85em' },
  { rotulo: 'M',  valor: null },
  { rotulo: 'G',  valor: '1.3em' },
  { rotulo: 'GG', valor: '1.7em' },
]

const btn = (ativo) => [
  'w-7 h-7 flex items-center justify-center rounded text-slate-300 transition disabled:opacity-30',
  ativo ? 'bg-sky-500/25 text-sky-300' : 'hover:bg-slate-800',
]
const divisor = 'w-px h-5 bg-slate-700 mx-1'
const rotulo = 'flex flex-col gap-1 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400'
const rotuloBloco = 'block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400 mb-1.5'
const campo = 'w-full border border-gray-300 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none'
const campoPequeno = 'normal-case tracking-normal font-normal border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-sm bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none'
const botaoSecundario = 'px-3 py-2 rounded-lg text-sm font-semibold border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition'

const p = props.pregacao
const dados = reactive({
  titulo: p.titulo,
  data_pregacao: p.data_pregacao,
  versiculo: p.versiculo ?? '',
  roteiro: p.roteiro ?? '',
  roteiro_fonte: p.roteiro_fonte,
  // Só o pastor publica e escolhe o pregador (o servidor ignora estes campos para os demais).
  ...(props.ehPastor ? {
    youtube_url: p.youtube_url ?? '',
    descricao: p.descricao ?? '',
    ativo: !!p.ativo,
    pregador_id: p.pregador_id ?? null,
    pregador: p.pregador ?? '',
  } : {}),
})
const status = ref(p.status)
const videoValido = computed(() => /^https?:\/\/\S+/.test(dados.youtube_url || ''))
const seloStatus = computed(() => ({
  rascunho:  { texto: 'Rascunho',  classe: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300' },
  publicada: { texto: 'No site',   classe: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' },
  oculta:    { texto: 'Oculta',    classe: 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400' },
}[status.value]))
const erros = reactive({})

const editor = useEditor({
  content: dados.roteiro,
  extensions: [StarterKit, TextStyle, Color, BackgroundColor, FontSize],
  onUpdate: ({ editor }) => {
    const html = editor.getHTML()
    dados.roteiro = html === '<p></p>' ? '' : html
  },
})
const cmd = () => editor.value.chain().focus()

function aplicarTamanho(valor) {
  valor ? cmd().setFontSize(valor).run() : cmd().unsetFontSize().run()
}
function tamanhoAtivo(valor) {
  const atual = editor.value?.getAttributes('textStyle')?.fontSize ?? null
  return (atual || null) === valor
}

// ─── Salvamento automático ───
const estado = ref('salvo') // salvo | pendente | salvando | erro
const salvoEm = ref(p.atualizado_em)
let timer = null
let emVoo = null

watch(dados, () => {
  estado.value = 'pendente'
  clearTimeout(timer)
  timer = setTimeout(salvar, 1200)
}, { deep: true })

function xsrf() {
  const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)
  return m ? decodeURIComponent(m[1]) : ''
}

async function salvar({ keepalive = false } = {}) {
  clearTimeout(timer)
  if (emVoo) { await emVoo } // nunca dois PUTs ao mesmo tempo (a ordem importa)
  if (estado.value === 'salvo') return
  estado.value = 'salvando'
  const corpo = JSON.stringify({ ...dados })
  emVoo = fetch(`/admin/pregacoes/${p.id}`, {
    method: 'PUT',
    keepalive,
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-XSRF-TOKEN': xsrf() },
    body: corpo,
  }).then(async (res) => {
    const json = await res.json().catch(() => ({}))
    Object.keys(erros).forEach((k) => delete erros[k])
    if (res.status === 422) {
      Object.entries(json.errors ?? {}).forEach(([k, v]) => { erros[k] = v[0] })
      estado.value = 'erro'
      return
    }
    if (!res.ok) throw new Error()
    salvoEm.value = json.atualizado_em
    if (json.status) status.value = json.status
    // Se o texto mudou enquanto salvava, o watcher já marcou como pendente de novo.
    if (JSON.stringify({ ...dados }) === corpo) estado.value = 'salvo'
  }).catch(() => {
    estado.value = 'erro'
    timer = setTimeout(salvar, 5000) // sem internet: tenta de novo
  }).finally(() => { emVoo = null })
  await emVoo
}

const statusTexto = computed(() => ({
  salvo: salvoEm.value ? `Salvo às ${new Date(salvoEm.value).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}` : 'Salvo',
  pendente: 'Alterado…',
  salvando: 'Salvando…',
  erro: Object.keys(erros).length ? 'Não salvou: confira os campos' : 'Sem conexão, tentando de novo…',
}[estado.value]))
const statusCor = computed(() => estado.value === 'erro' ? 'text-red-500' : 'text-gray-400 dark:text-slate-500')
const statusPonto = computed(() => ({
  salvo: 'bg-emerald-500', pendente: 'bg-amber-400', salvando: 'bg-amber-400 animate-pulse', erro: 'bg-red-500',
}[estado.value]))

// Não perde texto ao sair da página.
const pendente = () => estado.value !== 'salvo'
function antesDeFechar(e) {
  if (!pendente()) return
  salvar({ keepalive: true })
  e.preventDefault()
  e.returnValue = ''
}
const tirarGuarda = router.on('before', (event) => {
  if (event.detail.visit.method === 'get' && pendente()) salvar({ keepalive: true })
})

async function abrirPulpito() {
  await salvar()
  router.visit(`/admin/pregacoes/${p.id}/pulpito`)
}

function excluir() {
  if (!confirm(`Excluir a pregação "${dados.titulo}" (com o roteiro)? Não dá para desfazer.`)) return
  clearTimeout(timer)
  estado.value = 'salvo'
  router.delete(`/admin/pregacoes/${p.id}`)
}

// ─── Prévia do iPad ───
const previa = ref(false)
const moldura = ref(null)
const escala = ref(0.8)
const alturaTela = computed(() => 768 * escala.value)
const relogio = ref('')
function medir() {
  const largura = (moldura.value?.clientWidth ?? 1024) - 28
  const alturaMax = window.innerHeight - 160
  escala.value = Math.min(largura / 1024, alturaMax / 768, 1)
}
function tic() { relogio.value = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }
let relogioTimer = null
watch(previa, async (aberta) => {
  if (aberta) { await nextTick(); medir(); tic(); relogioTimer = setInterval(tic, 10000) }
  else clearInterval(relogioTimer)
})
function teclas(e) { if (e.key === 'Escape') previa.value = false }

onMounted(() => {
  window.addEventListener('beforeunload', antesDeFechar)
  window.addEventListener('resize', medir)
  window.addEventListener('keydown', teclas)
})
onBeforeUnmount(() => {
  window.removeEventListener('beforeunload', antesDeFechar)
  window.removeEventListener('resize', medir)
  window.removeEventListener('keydown', teclas)
  tirarGuarda()
  clearInterval(relogioTimer)
  editor.value?.destroy()
})
</script>

