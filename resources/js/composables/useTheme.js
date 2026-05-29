import { ref } from 'vue'

const isDark = ref(localStorage.getItem('admin-theme') === 'dark')

if (isDark.value) document.documentElement.classList.add('dark')

export function useTheme() {
  function toggle() {
    isDark.value = !isDark.value
    document.documentElement.classList.toggle('dark', isDark.value)
    localStorage.setItem('admin-theme', isDark.value ? 'dark' : 'light')
  }
  return { isDark, toggle }
}
