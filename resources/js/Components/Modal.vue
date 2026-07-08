<template>
  <Transition name="modal">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="fecharPorFora"></div>
      <div class="relative w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-700 p-6"
           :class="larguraClasse" role="dialog" aria-modal="true">
        <div v-if="title || $slots.header" class="flex items-start justify-between gap-4 mb-5">
          <slot name="header">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ title }}</h2>
          </slot>
          <button type="button" @click="fechar"
                  class="text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-slate-200 transition -mr-1 -mt-1">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </button>
        </div>

        <slot />

        <div v-if="$slots.footer" class="flex items-center justify-end gap-2 pt-5">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: '' },
  largura: { type: String, default: 'md' }, // sm | md | lg | xl
  fecharAoClicarFora: { type: Boolean, default: true },
})
const emit = defineEmits(['update:modelValue'])

const larguraClasse = computed(() => ({
  sm: 'max-w-sm',
  md: 'max-w-md',
  lg: 'max-w-lg',
  xl: 'max-w-2xl',
}[props.largura] ?? 'max-w-md'))

function fechar() {
  emit('update:modelValue', false)
}

function fecharPorFora() {
  if (props.fecharAoClicarFora) fechar()
}

function onKey(e) {
  if (e.key === 'Escape' && props.modelValue) fechar()
}

onMounted(() => window.addEventListener('keydown', onKey))
onBeforeUnmount(() => window.removeEventListener('keydown', onKey))
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.15s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
</style>
