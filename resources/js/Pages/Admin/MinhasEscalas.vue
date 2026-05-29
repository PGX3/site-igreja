<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Minha Agenda</p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Minhas Escalas</h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Suas próximas participações</p>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-3 gap-4 mb-8">
      <div v-for="stat in stats" :key="stat.label"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5 shadow-sm text-center">
        <p class="text-2xl font-bold" :class="stat.color">{{ stat.value }}</p>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1 uppercase tracking-wider">{{ stat.label }}</p>
      </div>
    </div>

    <!-- PRÓXIMAS (pendente) -->
    <div v-if="proximas.length" class="mb-8">
      <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-3">
        Aguardando sua confirmação
      </p>
      <div class="space-y-3">
        <div v-for="e in proximas" :key="e.id"
             class="bg-white dark:bg-slate-800 border border-yellow-200 dark:border-yellow-800/50 rounded-xl p-5 shadow-sm">
          <div class="flex items-start gap-4">
            <!-- Data -->
            <div class="text-center bg-yellow-50 dark:bg-yellow-900/20 rounded-lg px-3 py-2 min-w-[52px]">
              <p class="text-xl font-black text-yellow-700 dark:text-yellow-400 leading-none">{{ diaDoMes(e.data) }}</p>
              <p class="text-[10px] font-bold uppercase text-yellow-500 dark:text-yellow-600">{{ mesAbrev(e.data) }}</p>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <p class="font-bold text-gray-900 dark:text-white">{{ e.titulo }}</p>
              <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                {{ e.grupo?.nome }} · {{ e.hora_inicio }} – {{ e.hora_fim }}
              </p>
              <p v-if="e.funcao" class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-1">
                Função: {{ e.funcao }}
              </p>
            </div>

            <!-- Ações -->
            <div class="flex gap-2 flex-shrink-0">
              <Link :href="`/admin/escala-membros/${e.pivot_id}/confirmar`"
                    method="patch" as="button" preserve-scroll
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition">
                Confirmar ✓
              </Link>
              <Link :href="`/admin/escala-membros/${e.pivot_id}/recusar`"
                    method="patch" as="button" preserve-scroll
                    class="border border-red-200 dark:border-red-800 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 px-4 py-2 rounded-lg text-xs font-bold transition">
                Recusar ✗
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CONFIRMADAS -->
    <div v-if="confirmadas.length" class="mb-8">
      <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-3">Confirmadas</p>
      <div class="space-y-2">
        <div v-for="e in confirmadas" :key="e.id"
             class="bg-white dark:bg-slate-800 border border-green-100 dark:border-green-900/50 rounded-xl p-4 shadow-sm flex items-center gap-4">
          <div class="text-center min-w-[44px]">
            <p class="text-lg font-black text-green-700 dark:text-green-400">{{ diaDoMes(e.data) }}</p>
            <p class="text-[10px] font-bold uppercase text-green-400 dark:text-green-600">{{ mesAbrev(e.data) }}</p>
          </div>
          <div class="flex-1">
            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ e.titulo }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500">{{ e.grupo?.nome }} · {{ e.hora_inicio }} – {{ e.hora_fim }}</p>
          </div>
          <span class="text-xs font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2.5 py-1 rounded-full">Confirmado</span>
          <p v-if="e.confirmado_em" class="text-[10px] text-gray-400 dark:text-slate-500 hidden sm:block">{{ e.confirmado_em }}</p>
        </div>
      </div>
    </div>

    <!-- RECUSADAS -->
    <div v-if="recusadas.length" class="mb-8">
      <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-3">Recusadas</p>
      <div class="space-y-2">
        <div v-for="e in recusadas" :key="e.id"
             class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700/50 rounded-xl p-4 shadow-sm flex items-center gap-4 opacity-60">
          <div class="text-center min-w-[44px]">
            <p class="text-lg font-black text-gray-400 dark:text-slate-500">{{ diaDoMes(e.data) }}</p>
            <p class="text-[10px] font-bold uppercase text-gray-300 dark:text-slate-600">{{ mesAbrev(e.data) }}</p>
          </div>
          <div class="flex-1">
            <p class="font-semibold text-gray-500 dark:text-slate-400 text-sm line-through">{{ e.titulo }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500">{{ e.grupo?.nome }}</p>
          </div>
          <span class="text-xs font-bold text-red-400 bg-red-50 dark:bg-red-900/20 px-2.5 py-1 rounded-full">Recusado</span>
        </div>
      </div>
    </div>

    <!-- VAZIO -->
    <div v-if="!escalas.length"
         class="py-20 text-center bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="text-4xl mb-3">◷</p>
      <p class="font-medium text-gray-600 dark:text-slate-400">Você não está em nenhuma escala ainda.</p>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({ escalas: Array })

const proximas   = computed(() => props.escalas.filter(e => e.status_membro === 'pendente'))
const confirmadas = computed(() => props.escalas.filter(e => e.status_membro === 'confirmado'))
const recusadas   = computed(() => props.escalas.filter(e => e.status_membro === 'recusado'))

const stats = computed(() => [
  { label: 'Total',       value: props.escalas.length,      color: 'text-gray-900 dark:text-white' },
  { label: 'Confirmadas', value: confirmadas.value.length,  color: 'text-green-600' },
  { label: 'Pendentes',   value: proximas.value.length,     color: 'text-yellow-600' },
])

function diaDoMes(data) { return data ? new Date(data + 'T12:00:00').getDate() : '—' }
function mesAbrev(data) {
  if (!data) return ''
  return new Date(data + 'T12:00:00').toLocaleString('pt-BR', { month: 'short' }).replace('.', '')
}
</script>
