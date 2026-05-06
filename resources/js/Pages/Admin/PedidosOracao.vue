<template>
  <AdminLayout>
    <div class="flex items-end justify-between mb-10">
      <div>
        <p class="text-[9px] tracking-[0.35em] uppercase text-[var(--blue)] mb-2">Intercessão</p>
        <h1 style="font-family:'Cormorant Garamond',serif" class="text-4xl font-bold text-white">Pedidos de Oração</h1>
        <p class="text-white/30 text-[12px] mt-1">{{ pedidos.length }} recebido(s) · {{ naoLidos }} pendente(s)</p>
      </div>
      <div class="flex gap-3">
        <button @click="filtro = 'todos'"
                class="text-[10px] font-bold tracking-[0.2em] uppercase px-4 py-2 border transition-colors"
                :class="filtro==='todos' ? 'border-[var(--blue)] text-[var(--blue)]' : 'border-[var(--border)] text-white/30 hover:border-white/20'">
          Todos
        </button>
        <button @click="filtro = 'novos'"
                class="text-[10px] font-bold tracking-[0.2em] uppercase px-4 py-2 border transition-colors"
                :class="filtro==='novos' ? 'border-[var(--blue)] text-[var(--blue)]' : 'border-[var(--border)] text-white/30 hover:border-white/20'">
          Pendentes
        </button>
      </div>
    </div>

    <div v-if="filtrados.length === 0" class="text-center py-20 text-white/20 text-[13px] tracking-wide">
      Nenhum pedido encontrado.
    </div>

    <div class="space-y-px">
      <div v-for="p in filtrados" :key="p.id"
           class="border p-7 transition-all duration-300"
           :class="p.lido ? 'border-[var(--border)] bg-[#0e0e0e] opacity-55 hover:opacity-80' : 'border-[var(--blue)]/20 bg-[#111]'">
        <div class="flex items-start gap-6">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-4">
              <span class="font-bold text-[13px] text-white">{{ p.anonimo ? 'Anônimo' : p.nome }}</span>
              <span v-if="p.anonimo" class="text-[8px] font-black tracking-[0.3em] uppercase text-white/20 bg-white/5 px-2 py-0.5">
                Anônimo
              </span>
              <span v-if="!p.lido" class="text-[8px] font-black tracking-[0.3em] uppercase text-[var(--blue)] bg-[var(--blue)]/10 px-2 py-0.5">
                Pendente
              </span>
            </div>
            <p class="text-white/60 text-[14px] leading-relaxed border-l-2 border-[var(--blue)]/20 pl-4 italic"
               style="font-family:'Cormorant Garamond',serif; font-size:15px">
              "{{ p.pedido }}"
            </p>
            <p class="text-[10px] text-white/20 mt-4 tracking-wide">{{ formatDate(p.created_at) }}</p>
          </div>
          <div class="flex flex-col gap-2 flex-shrink-0">
            <button @click="marcarLido(p)"
                    class="text-[9px] font-bold tracking-[0.2em] uppercase px-3 py-2 border transition-colors"
                    :class="p.lido ? 'border-[var(--border)] text-white/25 hover:border-white/20' : 'border-[var(--blue)]/30 text-[var(--blue)] hover:bg-[var(--blue)]/8'">
              {{ p.lido ? 'Reabrir' : 'Orado ✓' }}
            </button>
            <button @click="excluir(p)"
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

const props = defineProps({ pedidos: Array })
const filtro = ref('todos')

const filtrados = computed(() =>
  filtro.value === 'novos' ? props.pedidos.filter(p => !p.lido) : props.pedidos
)
const naoLidos = computed(() => props.pedidos.filter(p => !p.lido).length)

function formatDate(d) {
  return new Date(d).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' })
}
function marcarLido(p) {
  router.patch(`/admin/pedidos-oracao/${p.id}/lido`, {}, { preserveScroll: true })
}
function excluir(p) {
  if (confirm('Excluir este pedido?')) {
    router.delete(`/admin/pedidos-oracao/${p.id}`, { preserveScroll: true })
  }
}
</script>
