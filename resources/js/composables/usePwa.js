import { ref } from 'vue'

// Estado compartilhado da PWA (instalação e atualização). Um único registro
// do service worker por página, iniciado em app.js via iniciarPwa().

const DISPENSA_KEY = 'pwa-instalar-dispensado-em'
const DISPENSA_DIAS = 14

const instalavel = ref(false)
const atualizacaoDisponivel = ref(false)
const instalando = ref(false)

let promptInstalacao = null
let workerEsperando = null
let iniciado = false

const ua = navigator.userAgent
// iPadOS se apresenta como Mac, mas com tela de toque.
const isIos = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
const isSafari = /safari/i.test(ua) && !/crios|fxios|chrome|android/i.test(ua)

function emStandalone() {
  return window.matchMedia?.('(display-mode: standalone)').matches || navigator.standalone === true
}

function dispensadoRecentemente() {
  try {
    const em = Number(localStorage.getItem(DISPENSA_KEY) || 0)
    return em && Date.now() - em < DISPENSA_DIAS * 24 * 60 * 60 * 1000
  } catch {
    return false
  }
}

export function iniciarPwa() {
  if (iniciado) return
  iniciado = true

  // Chrome/Edge/Android avisam quando dá para instalar. iOS nunca dispara
  // este evento: lá mostramos a instrução manual (ver podeSugerirIos).
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    promptInstalacao = e
    instalavel.value = !emStandalone() && !dispensadoRecentemente()
  })

  window.addEventListener('appinstalled', () => {
    instalavel.value = false
    promptInstalacao = null
  })

  if (!import.meta.env.PROD || !('serviceWorker' in navigator)) return

  window.addEventListener('load', async () => {
    try {
      const reg = await navigator.serviceWorker.register('/sw.js')

      if (reg.waiting && navigator.serviceWorker.controller) marcarAtualizacao(reg.waiting)

      reg.addEventListener('updatefound', () => {
        const novo = reg.installing
        novo?.addEventListener('statechange', () => {
          if (novo.state === 'installed' && navigator.serviceWorker.controller) marcarAtualizacao(novo)
        })
      })

      // Procura versão nova ao voltar para a app (comum em PWA que fica aberta dias).
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') reg.update().catch(() => {})
      })
    } catch (e) {
      console.warn('Service worker não registrado:', e)
    }
  })

  let recarregando = false
  navigator.serviceWorker.addEventListener('controllerchange', () => {
    if (recarregando) return
    recarregando = true
    window.location.reload()
  })
}

function marcarAtualizacao(worker) {
  workerEsperando = worker
  atualizacaoDisponivel.value = true
}

export function usePwa() {
  async function instalar() {
    if (!promptInstalacao) return
    instalando.value = true
    try {
      promptInstalacao.prompt()
      await promptInstalacao.userChoice
    } finally {
      // O prompt só pode ser usado uma vez: aceito ou não, o banner some.
      instalando.value = false
      promptInstalacao = null
      instalavel.value = false
    }
  }

  function dispensarInstalacao() {
    instalavel.value = false
    try {
      localStorage.setItem(DISPENSA_KEY, String(Date.now()))
    } catch {}
  }

  function atualizar() {
    if (!workerEsperando) {
      window.location.reload()
      return
    }
    workerEsperando.postMessage({ type: 'SKIP_WAITING' })
  }

  // iPhone/iPad no Safari, fora da tela inicial, sem ter dispensado o aviso.
  const podeSugerirIos = isIos && isSafari && !emStandalone() && !dispensadoRecentemente()

  return {
    instalavel,
    instalando,
    atualizacaoDisponivel,
    podeSugerirIos,
    emStandalone: emStandalone(),
    instalar,
    dispensarInstalacao,
    atualizar,
  }
}
