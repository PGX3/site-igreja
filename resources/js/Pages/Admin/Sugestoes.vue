<template>
  <AdminLayout>
    <div class="flex items-end justify-between mb-10">
      <div>
        <p class="text-[9px] tracking-[0.35em] uppercase text-[var(--blue)] mb-2">Comunicações</p>
        <h1 style="font-family:'Cormorant Garamond',serif" class="text-4xl font-bold text-white">Sugestões</h1>
        <p class="text-white/30 text-[12px] mt-1">{{ sugestoes.length }} recebida(s) · {{ naoLidas }} não lida(s)</p>
      </div>
      <div class="flex gap-3">
        <button @click="filtro = 'todas'"
                class="text-[10px] font-bold tracking-[0.2em] uppercase px-4 py-2 border transition-colors"
                :class="filtro==='todas' ? 'border-[var(--blue)] text-[var(--blue)]' : 'border-[var(--border)] text-white/30 hover:border-white/20'">
          Todas
        </button>
        <button @click="filtro = 'novas'"
                class="text-[10px] font-bold tracking-[0.2em] uppercase px-4 py-2 border transition-colors"
                :class="filtro==='novas' ? 'border-[var(--blue)] text-[var(--blue)]' : 'border-[var(--border)] text-white/30 hover:border-white/20'">
          Novas
        </button>
      </div>
    </div>

    <div v-if="filtradas.length === 0" class="text-center py-20 text-white/20 text-[13px] tracking-wide">
      Nenhuma sugestão encontrada.
    </div>

    <div class="space-y-px">
      <div v-for="s in filtradas" :key="s.id"
           class="border p-7 transition-all duration-300"
           :class="s.lida ? 'border-[var(--border)] bg-[#0e0e0e] opacity-55 hover:opacity-80' : 'border-[var(--blue)]/20 bg-[#111]'">
        <div class="flex items-start gap-6">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1">
              <span class="font-bold text-[13px] text-white">{{ s.nome }}</span>
              <span v-if="!s.lida" class="text-[8px] font-black tracking-[0.3em] uppercase text-[var(--blue)] bg-[var(--blue)]/10 px-2 py-0.5">
                Nova
              </span>
            </div>
            <p v-if="s.email" class="text-[11px] text-white/25 mb-4 font-mono">{{ s.email }}</p>
            <p class="text-white/60 text-[13px] leading-relaxed border-l border-[var(--border)] pl-4">{{ s.mensagem }}</p>
            <p class="text-[10px] text-white/20 mt-4 tracking-wide">{{ formatDate(s.created_at) }}</p>
          </div>
          <div class="flex flex-col gap-2 flex-shrink-0">
            <button @click="marcarLida(s)"
                    class="text-[9px] font-bold tracking-[0.2em] uppercase px-3 py-2 border transition-colors"
                    :class="s.lida ? 'border-[var(--border)] text-white/25 hover:border-white/20' : 'border-[var(--blue)]/30 text-[var(--blue)] hover:bg-[var(--blue)]/8'">
              {{ s.lida ? 'Reabrir' : 'Marcar lida' }}
            </button>
            <button @click="excluir(s)"
                    class="text-[9px] font-bold tracking-[0.2em] uppercase px-3 py-2 border border-[var(--border)]
                           text-white/20 hover:border-red-500/30 hover:text-red-400 transition-colors">
              Excluir
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ sugestoes: Array })
const filtro = ref('todas')

const filtradas = computed(() =>
  filtro.value === 'novas' ? props.sugestoes.filter(s => !s.lida) : props.sugestoes
)
const naoLidas = computed(() => props.sugestoes.filter(s => !s.lida).length)

function formatDate(d) {
  return new Date(d).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })
}
function marcarLida(s) {
  router.patch(`/admin/sugestoes/${s.id}/lida`, {}, { preserveScroll: true })
}
function excluir(s) {
  if (confirm('Excluir esta sugestão?')) {
    router.delete(`/admin/sugestoes/${s.id}`, { preserveScroll: true })
  }
}
</script>
