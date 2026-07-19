<template>
  <div ref="root" class="fixed inset-0 z-[60] bg-black text-white flex flex-col overflow-hidden"
       :style="{ height: '100dvh' }"
       @touchstart="onTouchStart" @touchend="onTouchEnd">
    <!-- BARRA SUPERIOR -->
    <div class="flex items-center justify-between gap-2 px-2 py-2 border-b border-white/10 flex-shrink-0">
      <div class="flex items-center gap-2 min-w-0">
        <button v-if="varias" @click="anterior" :disabled="idx === 0"
                class="w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 disabled:opacity-30 text-2xl leading-none flex-shrink-0" title="Anterior">‹</button>
        <div class="min-w-0">
          <p class="font-bold text-lg truncate">{{ atual?.nome }}</p>
          <p class="text-sm">
            <span v-if="atual?.tom" class="text-amber-400 font-semibold">Tom: {{ atual.tom }}</span>
            <span v-if="varias" class="text-white/50">{{ atual?.tom ? ' · ' : '' }}{{ idx + 1 }} de {{ musicas.length }}</span>
          </p>
        </div>
        <button v-if="varias" @click="proxima" :disabled="idx === musicas.length - 1"
                class="w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 disabled:opacity-30 text-2xl leading-none flex-shrink-0" title="Próxima">›</button>
      </div>
      <div class="flex items-center gap-1 flex-shrink-0">
        <button v-if="temPlayer" @click="mostrarPlayer = !mostrarPlayer"
                class="h-10 px-3 rounded-lg text-sm font-bold transition"
                :class="mostrarPlayer ? 'bg-amber-400 text-black' : 'bg-white/10 hover:bg-white/20'" title="Player">▶ Player</button>
        <button @click="menor" class="w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 text-xl font-bold transition" title="Diminuir">A−</button>
        <button @click="maior" class="w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 text-xl font-bold transition" title="Aumentar">A+</button>
        <button @click="toggleFullscreen" class="w-10 h-10 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center transition" title="Tela cheia">
          <span class="text-lg">⛶</span>
        </button>
        <button @click="$emit('close')" class="w-10 h-10 rounded-lg bg-white/10 hover:bg-red-600 text-2xl leading-none transition" title="Fechar">×</button>
      </div>
    </div>

    <!-- CONTEÚDO -->
    <div class="flex-1 overflow-y-auto px-3 py-3" ref="scroll">
      <div class="mx-auto max-w-3xl">
        <div v-if="temPlayer && mostrarPlayer" class="mb-6">
          <MediaEmbed :url="atual.link" />
        </div>
        <div class="letra-apresentacao" :style="{ fontSize: fonte + 'px', lineHeight: 1.5 }" v-html="atual?.letra"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'

const props = defineProps({
  musicas: { type: Array, default: () => [] },
  start:   { type: Number, default: 0 },
})
defineEmits(['close'])

const root   = ref(null)
const scroll = ref(null)
const idx    = ref(props.start)
const fonte  = ref(22)
const mostrarPlayer = ref(false)

const varias = computed(() => props.musicas.length > 1)
const atual  = computed(() => props.musicas[idx.value] ?? null)
const temPlayer = computed(() => {
  const u = (atual.value?.link || '').toLowerCase()
  return u.includes('youtu') || u.includes('spotify')
})

function ir(i) {
  idx.value = Math.max(0, Math.min(i, props.musicas.length - 1))
}
function anterior() { ir(idx.value - 1) }
function proxima()  { ir(idx.value + 1) }

function maior() { fonte.value = Math.min(fonte.value + 2, 72) }
function menor() { fonte.value = Math.max(fonte.value - 2, 12) }

// Ao trocar de música: volta o scroll ao topo e esconde o player.
watch(idx, () => {
  mostrarPlayer.value = false
  if (scroll.value) scroll.value.scrollTop = 0
})

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    root.value?.requestFullscreen?.().catch(() => {})
  } else {
    document.exitFullscreen?.()
  }
}

// Navegação por teclado e por swipe (celular)
function onKey(e) {
  if (e.key === 'ArrowRight') proxima()
  else if (e.key === 'ArrowLeft') anterior()
}
let touchX = 0
function onTouchStart(e) { touchX = e.changedTouches[0].clientX }
function onTouchEnd(e) {
  const dx = e.changedTouches[0].clientX - touchX
  if (Math.abs(dx) > 60) { dx < 0 ? proxima() : anterior() }
}

onMounted(() => {
  document.body.style.overflow = 'hidden'
  window.addEventListener('keydown', onKey)
})
onBeforeUnmount(() => {
  document.body.style.overflow = ''
  window.removeEventListener('keydown', onKey)
  if (document.fullscreenElement) document.exitFullscreen?.().catch(() => {})
})
</script>

<style scoped>
.letra-apresentacao :deep(p) { margin: 0; }
/* Linhas em branco (parágrafos vazios) valem uma linha */
.letra-apresentacao :deep(p:empty)::before { content: "\00a0"; }
.letra-apresentacao :deep(h2) { font-size: 1.25em; font-weight: 800; margin: 0.8em 0 0.3em; color: #fbbf24; }
.letra-apresentacao :deep(h3) { font-size: 1.1em; font-weight: 700; margin: 0.7em 0 0.2em; color: #fbbf24; }
.letra-apresentacao :deep(ul) { list-style: disc; padding-left: 1.5em; }
.letra-apresentacao :deep(strong) { font-weight: 800; }
</style>
