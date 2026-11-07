<template>
  <Transition name="pwa-slide">
    <div v-if="mostrar"
         class="fixed inset-x-3 z-40 sm:inset-x-auto sm:right-4 sm:w-[22rem]"
         :style="{ bottom: 'calc(0.75rem + env(safe-area-inset-bottom))' }">
      <div class="rounded-2xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900
                  shadow-xl shadow-black/10 dark:shadow-black/40 p-4 flex gap-3">
        <img src="/android-chrome-192x192.png" alt="" class="w-11 h-11 rounded-xl flex-shrink-0" />

        <!-- Atualização pronta -->
        <div v-if="atualizacaoDisponivel" class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-gray-900 dark:text-white">Nova versão disponível</p>
          <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Atualize para usar as melhorias mais recentes.</p>
          <div class="mt-3 flex gap-2">
            <button type="button" @click="atualizar"
                    class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-colors">
              Atualizar
            </button>
            <button type="button" @click="ocultarAtualizacao = true"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 dark:text-slate-400
                           hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
              Depois
            </button>
          </div>
        </div>

        <!-- Instalar (Android / desktop) -->
        <div v-else-if="instalavel" class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-gray-900 dark:text-white">Instalar o app da Igreja</p>
          <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Acesso rápido na tela inicial, sem passar pela loja.</p>
          <div class="mt-3 flex gap-2">
            <button type="button" @click="instalar" :disabled="instalando"
                    class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-semibold transition-colors">
              {{ instalando ? 'Instalando...' : 'Instalar' }}
            </button>
            <button type="button" @click="dispensarInstalacao"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 dark:text-slate-400
                           hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
              Agora não
            </button>
          </div>
        </div>

        <!-- iPhone/iPad: só dá para instalar pelo menu do Safari -->
        <div v-else-if="podeSugerirIos && !iosDispensado" class="min-w-0 flex-1">
          <p class="text-sm font-semibold text-gray-900 dark:text-white">Adicione à tela de início</p>
          <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5 leading-relaxed">
            No Safari, toque em <span class="font-semibold text-gray-700 dark:text-slate-200">Compartilhar</span>
            <AppIcon name="share" size="sm" class="inline-block align-text-bottom mx-0.5" />
            e depois em <span class="font-semibold text-gray-700 dark:text-slate-200">Adicionar à Tela de Início</span>.
          </p>
          <div class="mt-3">
            <button type="button" @click="dispensarIos"
                    class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-colors">
              Entendi
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, ref } from 'vue'
import AppIcon from '@/Components/AppIcon.vue'
import { usePwa } from '@/composables/usePwa.js'

const props = defineProps({
  // Nos layouts públicos só interessa o aviso de atualização.
  sugerirInstalacao: { type: Boolean, default: true },
})

const {
  instalavel, instalando, atualizacaoDisponivel, podeSugerirIos,
  instalar, dispensarInstalacao, atualizar,
} = usePwa()

const ocultarAtualizacao = ref(false)
const iosDispensado = ref(false)

function dispensarIos() {
  iosDispensado.value = true
  dispensarInstalacao()
}

const mostrar = computed(() => {
  if (atualizacaoDisponivel.value && !ocultarAtualizacao.value) return true
  if (!props.sugerirInstalacao) return false
  return instalavel.value || (podeSugerirIos && !iosDispensado.value)
})
</script>

<style scoped>
.pwa-slide-enter-active, .pwa-slide-leave-active { transition: opacity .25s ease, transform .25s ease; }
.pwa-slide-enter-from, .pwa-slide-leave-to { opacity: 0; transform: translateY(12px); }
</style>
