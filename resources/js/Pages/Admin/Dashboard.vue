<template>
  <AdminLayout>
    <div class="mb-10">
      <p class="text-[9px] tracking-[0.35em] uppercase text-[var(--blue)] mb-2">Visão Geral</p>
      <h1 style="font-family:'Cormorant Garamond',serif" class="text-4xl font-bold text-white">Dashboard</h1>
      <p class="text-white/30 text-[12px] mt-1">Igreja em Charqueadas · Painel de Controle</p>
    </div>

    <!-- Stats grid -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-10">
      <div v-for="stat in stats" :key="stat.label"
           class="p-7 border transition-colors duration-300 hover:border-[var(--blue)]/30 group"
           :class="stat.highlight ? 'border-[var(--blue)]/25 bg-[var(--blue)]/4' : 'border-[var(--border)] bg-[#111]'">
        <p class="text-[9px] tracking-[0.3em] uppercase mb-3"
           :class="stat.highlight ? 'text-[var(--blue)]' : 'text-white/30'">{{ stat.label }}</p>
        <p class="text-5xl font-black tracking-tight text-white">{{ stat.value }}</p>
        <p v-if="stat.sub" class="text-[10px] text-white/25 mt-2">{{ stat.sub }}</p>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="border border-[var(--border)] bg-[#111] p-8 mb-6">
      <p class="text-[9px] tracking-[0.35em] uppercase text-white/30 mb-6">Ações Rápidas</p>
      <div class="flex flex-wrap gap-3">
        <Link href="/admin/cultos/create" class="btn-primary text-[11px]">+ Novo Culto</Link>
        <Link href="/admin/textos" class="btn-ghost text-[11px]">Editar Textos</Link>
        <Link href="/admin/sugestoes" class="btn-ghost text-[11px]" v-if="novasSugestoes > 0">
          Ver {{ novasSugestoes }} sugestão(ões) nova(s)
        </Link>
        <Link href="/admin/pedidos-oracao" class="btn-ghost text-[11px]" v-if="novosPedidos > 0">
          Ver {{ novosPedidos }} pedido(s) de oração
        </Link>
      </div>
    </div>

    <Link href="/" target="_blank"
          class="text-[10px] tracking-[0.25em] uppercase text-white/20 hover:text-[var(--blue)] transition-colors">
      ↗ Visualizar site
    </Link>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  totalCultos: Number,
  totalTextos: Number,
  novasSugestoes: Number,
  novosPedidos: Number,
  totalSugestoes: Number,
  totalPedidos: Number,
})

const stats = computed(() => [
  { label: 'Cultos',          value: props.totalCultos    || 0, highlight: false },
  { label: 'Textos editáveis',value: props.totalTextos    || 0, highlight: false },
  { label: 'Novas sugestões', value: props.novasSugestoes || 0, highlight: props.novasSugestoes > 0, sub: `${props.totalSugestoes || 0} total` },
  { label: 'Novos pedidos',   value: props.novosPedidos   || 0, highlight: props.novosPedidos > 0,   sub: `${props.totalPedidos   || 0} total` },
])
</script>
