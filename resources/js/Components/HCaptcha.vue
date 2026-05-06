<template>
  <div>
    <div :id="containerId" class="h-captcha"></div>
    <p v-if="error" class="text-red-400 text-xs mt-1">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  sitekey: { type: String, required: true },
})

const emit = defineEmits(['verify', 'expire', 'error'])

const containerId = `hcaptcha-${Math.random().toString(36).slice(2)}`
const error = ref('')
let widgetId = null

function loadScript() {
  return new Promise((resolve) => {
    if (window.hcaptcha) { resolve(); return }
    const s = document.createElement('script')
    s.src = 'https://js.hcaptcha.com/1/api.js?render=explicit'
    s.async = true
    s.defer = true
    s.onload = resolve
    document.head.appendChild(s)
  })
}

async function mount() {
  await loadScript()
  // Wait for hcaptcha to be ready
  await new Promise(resolve => {
    const check = () => window.hcaptcha ? resolve() : setTimeout(check, 100)
    check()
  })
  widgetId = window.hcaptcha.render(containerId, {
    sitekey: props.sitekey,
    theme: 'dark',
    callback: (token) => { error.value = ''; emit('verify', token) },
    'expired-callback': () => { emit('expire') },
    'error-callback': () => { error.value = 'Erro no captcha. Tente novamente.'; emit('error') },
  })
}

function reset() {
  if (widgetId !== null && window.hcaptcha) {
    window.hcaptcha.reset(widgetId)
  }
}

defineExpose({ reset })

onMounted(mount)
onUnmounted(() => {
  if (widgetId !== null && window.hcaptcha) {
    try { window.hcaptcha.remove(widgetId) } catch {}
  }
})
</script>
