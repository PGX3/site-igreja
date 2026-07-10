// reCAPTCHA v3 (invisível). Carrega o script sob demanda e roda execute(action)
// no momento do submit, devolvendo o token para o backend verificar o score.
let loadPromise = null

function loadScript(sitekey) {
  if (loadPromise) return loadPromise
  loadPromise = new Promise((resolve) => {
    if (window.grecaptcha) { resolve(); return }
    const s = document.createElement('script')
    s.src = `https://www.google.com/recaptcha/api.js?render=${sitekey}`
    s.async = true
    s.defer = true
    s.onload = resolve
    document.head.appendChild(s)
  })
  return loadPromise
}

export function useRecaptcha(sitekey) {
  async function execute(action) {
    // Sem sitekey (dev sem chaves): backend libera em ambiente local.
    if (!sitekey) return ''

    await loadScript(sitekey)
    await new Promise((resolve) => window.grecaptcha.ready(resolve))

    return window.grecaptcha.execute(sitekey, { action })
  }

  return { execute }
}
