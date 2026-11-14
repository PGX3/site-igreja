<template>
  <Head :title="roteiro.titulo" />

  <div ref="raiz" class="fixed inset-0 bg-[#05080B] text-slate-100 flex flex-col md:flex-row select-none">

    <!-- TEXTO -->
    <main ref="folha" class="flex-1 overflow-y-auto overscroll-contain">
      <div class="mx-auto px-5 sm:px-8 py-8 sm:py-12 transition-[max-width] duration-200" :style="{ maxWidth: largura + '%' }">
        <p v-if="roteiro.versiculo" class="text-[13px] uppercase tracking-[0.25em] text-sky-400/80 mb-2">{{ roteiro.versiculo }}</p>
        <h1 class="font-bold mb-8" :style="{ fontSize: (fonte * 1.4) + 'px', lineHeight: 1.2 }">{{ roteiro.titulo }}</h1>
        <div v-if="roteiro.conteudo" class="roteiro select-text" :style="{ fontSize: fonte + 'px', lineHeight: 1.5 }" v-html="roteiro.conteudo"></div>
        <p v-else class="text-slate-500">Este roteiro ainda está em branco.</p>
        <div class="h-[40vh]"></div>
      </div>
    </main>

    <!-- LATERAL (embaixo no celular) -->
    <aside class="flex-shrink-0 border-t md:border-t-0 md:border-l border-white/10
                  flex md:flex-col items-center justify-between gap-3 px-4 py-3 md:py-6 md:w-[150px]">

      <div class="text-center">
        <p class="text-2xl md:text-3xl font-light tabular-nums">{{ relogio }}</p>
        <p class="hidden md:block text-[11px] uppercase tracking-widest text-slate-500 mt-1">agora</p>
      </div>

      <!-- Cronômetro: toque inicia/pausa, segurar zera -->
      <button type="button" @click="alternarCronometro" @contextmenu.prevent="zerarCronometro"
              @pointerdown="segurar" @pointerup="soltar" @pointerleave="soltar"
              class="text-center rounded-xl px-3 py-2 transition"
              :class="rodando ? 'bg-sky-500/15 text-sky-300' : 'text-slate-400 hover:bg-white/5'"
              title="Toque para iniciar/pausar. Segure para zerar.">
        <p class="text-xl md:text-2xl tabular-nums">{{ cronometro }}</p>
        <p class="hidden md:block text-[10px] uppercase tracking-widest mt-0.5" :class="rodando ? 'text-sky-400/70' : 'text-slate-600'">
          {{ rodando ? 'pregando' : (decorrido ? 'pausado' : 'cronômetro') }}
        </p>
      </button>

      <div class="flex md:flex-col items-center gap-2">
        <div class="grid grid-cols-2 gap-2">
          <button type="button" @click="mudarFonte(-2)" :class="botao" title="Diminuir letra (−)"><span class="text-sm">A−</span></button>
          <button type="button" @click="mudarFonte(2)" :class="botao" title="Aumentar letra (+)">A+</button>
          <!-- Margens: texto mais estreito / mais largo -->
          <button type="button" @click="mudarLargura(-10)" :disabled="largura <= LARGURA_MIN" :class="botao" title="Margens maiores ([)">
            <svg width="20" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 4v16M21 4v16M6 12h4.5M10.5 12L8 9.5M10.5 12L8 14.5M18 12h-4.5M13.5 12l2.5-2.5M13.5 12l2.5 2.5" />
            </svg>
          </button>
          <button type="button" @click="mudarLargura(10)" :disabled="largura >= 100" :class="botao" title="Margens menores (])">
            <svg width="20" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 4v16M21 4v16M7 12h10M7 12l2.5-2.5M7 12l2.5 2.5M17 12l-2.5-2.5M17 12l-2.5 2.5" />
            </svg>
          </button>
          <button type="button" @click="alternarTelaCheia" :class="botao" :title="telaCheia ? 'Sair da tela cheia (F)' : 'Tela cheia (F)'">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path v-if="!telaCheia" d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5" />
              <path v-else d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5" />
            </svg>
          </button>
          <Link :href="`/admin/pregacoes/${roteiro.id}/edit`" :class="botao" title="Voltar para o editor">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
          </Link>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  roteiro: { type: Object, required: true },
  fonteMin: { type: Number, default: 16 },
  fonteMax: { type: Number, default: 48 },
})

const botao = 'w-11 h-11 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 font-semibold flex items-center justify-center transition disabled:opacity-30'

// Tamanho da letra: começa no do roteiro; o ajuste fica salvo neste aparelho.
const chaveFonte = `roteiro-fonte-${props.roteiro.id}`
const fonte = ref(Number(localStorage.getItem(chaveFonte)) || props.roteiro.tamanho_fonte)
function mudarFonte(delta) {
  fonte.value = Math.min(props.fonteMax + 16, Math.max(props.fonteMin, fonte.value + delta))
  localStorage.setItem(chaveFonte, String(fonte.value))
}

// Largura do texto (margens): fica salva neste aparelho, vale para todos os roteiros.
const LARGURA_MIN = 50
const largura = ref(Number(localStorage.getItem('pulpito-largura')) || 100)
function mudarLargura(delta) {
  largura.value = Math.min(100, Math.max(LARGURA_MIN, largura.value + delta))
  localStorage.setItem('pulpito-largura', String(largura.value))
}

// Relógio
const relogio = ref('')
const tic = () => { relogio.value = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }

// Cronômetro
const rodando = ref(false)
const decorrido = ref(0) // ms acumulados
let inicio = 0
const agora = ref(Date.now())
const total = computed(() => decorrido.value + (rodando.value ? agora.value - inicio : 0))
const cronometro = computed(() => {
  const s = Math.floor(total.value / 1000)
  return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`
})
let segurando = null
let zerou = false
function alternarCronometro() {
  if (zerou) { zerou = false; return }
  if (rodando.value) { decorrido.value += Date.now() - inicio; rodando.value = false }
  else { inicio = Date.now(); rodando.value = true }
}
function zerarCronometro() { rodando.value = false; decorrido.value = 0 }
function segurar() { segurando = setTimeout(() => { zerarCronometro(); zerou = true }, 800) }
function soltar() { clearTimeout(segurando) }

// Tela cheia + manter a tela acesa
const raiz = ref(null)
const folha = ref(null)
const telaCheia = ref(false)
function alternarTelaCheia() {
  if (document.fullscreenElement) document.exitFullscreen?.()
  else raiz.value?.requestFullscreen?.()
}
const aoMudarTelaCheia = () => { telaCheia.value = !!document.fullscreenElement }

let travaTela = null
async function manterAcesa() {
  try { travaTela = await navigator.wakeLock?.request('screen') } catch { /* navegador sem suporte */ }
}
const aoVoltarAba = () => { if (document.visibilityState === 'visible') manterAcesa() }

// Teclado: setas/espaço rolam, +/- letra, F tela cheia
function teclas(e) {
  const passo = (folha.value?.clientHeight ?? 600) * 0.6
  if (['ArrowDown', 'PageDown', ' '].includes(e.key)) { e.preventDefault(); folha.value?.scrollBy({ top: passo, behavior: 'smooth' }) }
  else if (['ArrowUp', 'PageUp'].includes(e.key)) { e.preventDefault(); folha.value?.scrollBy({ top: -passo, behavior: 'smooth' }) }
  else if (e.key === '+' || e.key === '=') mudarFonte(2)
  else if (e.key === '-') mudarFonte(-2)
  else if (e.key === 'f' || e.key === 'F') alternarTelaCheia()
  else if (e.key === '[') mudarLargura(-10)
  else if (e.key === ']') mudarLargura(10)
}

let timers = []
onMounted(() => {
  tic()
  timers = [setInterval(tic, 10000), setInterval(() => { agora.value = Date.now() }, 500)]
  document.addEventListener('fullscreenchange', aoMudarTelaCheia)
  document.addEventListener('visibilitychange', aoVoltarAba)
  window.addEventListener('keydown', teclas)
  manterAcesa()
})
onBeforeUnmount(() => {
  timers.forEach(clearInterval)
  document.removeEventListener('fullscreenchange', aoMudarTelaCheia)
  document.removeEventListener('visibilitychange', aoVoltarAba)
  window.removeEventListener('keydown', teclas)
  travaTela?.release?.()
  if (document.fullscreenElement) document.exitFullscreen?.()
})
</script>
