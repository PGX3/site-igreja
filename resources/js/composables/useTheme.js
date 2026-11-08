import { ref } from 'vue'

const isDark = ref(localStorage.getItem('admin-theme') === 'dark')

if (isDark.value) document.documentElement.classList.add('dark')

// Cor que o navegador usa na barra de status (PWA no iOS/Android) e na aba.
export const COR_SITE = '#0a0a0a'
export const COR_PAINEL_CLARO = '#ffffff'
export const COR_PAINEL_ESCURO = '#0f172a'

export function definirCorTema(cor) {
  let meta = document.querySelector('meta[name="theme-color"]')
  if (!meta) {
    meta = document.createElement('meta')
    meta.name = 'theme-color'
    document.head.appendChild(meta)
  }
  meta.content = cor
}

export function useTheme() {
  function toggle() {
    isDark.value = !isDark.value
    document.documentElement.classList.toggle('dark', isDark.value)
    localStorage.setItem('admin-theme', isDark.value ? 'dark' : 'light')
  }
  return { isDark, toggle }
}
