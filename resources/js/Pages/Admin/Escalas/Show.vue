<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
          <Link href="/admin/escalas" class="hover:text-blue-600 dark:hover:text-blue-400">Escalas</Link>
          <span class="mx-1">›</span>
          Detalhe
        </p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ escala.titulo }}</h1>
        <div class="flex items-center gap-2 flex-wrap mt-2">
          <span :class="statusClass(escala.status)"
                class="text-xs font-bold px-2.5 py-1 rounded-full">
            {{ statusLabel(escala.status) }}
          </span>
          <span class="text-sm text-gray-500 dark:text-slate-400">
            {{ formatarData(escala.data) }} · {{ escala.hora_inicio }} – {{ escala.hora_fim }}
          </span>
          <span class="text-sm text-gray-400 dark:text-slate-500">{{ escala.grupo?.nome }}</span>
        </div>
      </div>

      <Link :href="`/admin/escalas/${escala.id}/edit`"
            class="self-start border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
        Editar
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- DESCRIÇÃO -->
    <div v-if="escala.descricao"
         class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5 shadow-sm mb-6 text-sm text-gray-600 dark:text-slate-300">
      {{ escala.descricao }}
    </div>

    <!-- VÍNCULO -->
    <div v-if="escala.culto || escala.evento"
         class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5 shadow-sm mb-6 flex items-center gap-4">
      <div :class="escala.culto
            ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400'
            : 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400'"
           class="text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full">
        {{ escala.culto ? 'Culto' : 'Evento' }}
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-semibold text-gray-900 dark:text-white text-sm">
          {{ escala.culto?.nome || escala.evento?.nome }}
        </p>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
          <template v-if="escala.culto">
            Toda {{ escala.culto.dia_semana?.toLowerCase() }} · {{ escala.culto.horario }}
          </template>
          <template v-else-if="escala.evento">
            {{ formatarData(escala.evento.data_evento) }}
            <span v-if="escala.evento.horario"> · {{ escala.evento.horario }}</span>
            <span v-if="escala.evento.local"> · {{ escala.evento.local }}</span>
          </template>
        </p>
      </div>
    </div>

    <!-- RESUMO MEMBROS -->
    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-8">
      <div v-for="stat in membroStats" :key="stat.label"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-3 sm:p-5 shadow-sm text-center">
        <p class="text-2xl sm:text-3xl font-bold" :class="stat.color">{{ stat.value }}</p>
        <p class="text-[10px] sm:text-xs text-gray-400 dark:text-slate-500 mt-1 uppercase tracking-wider">{{ stat.label }}</p>
      </div>
    </div>

    <!-- LISTA MEMBROS -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
        <p class="font-semibold text-gray-900 dark:text-white">Membros Escalados</p>
        <span class="text-xs text-gray-400 dark:text-slate-500">{{ escala.membros?.length ?? 0 }} total</span>
      </div>

      <div v-if="escala.membros?.length">
        <div v-for="m in escala.membros" :key="m.id"
             class="flex items-center gap-3 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-50 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
          <!-- Avatar inicial -->
          <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-bold text-sm
                      flex items-center justify-center flex-shrink-0">
            {{ m.user?.name?.[0]?.toUpperCase() }}
          </div>

          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ m.user?.name }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 truncate">
              <span class="hidden sm:inline">{{ m.user?.email }}</span>
              <span class="sm:hidden">{{ m.funcao ?? m.user?.email ?? '' }}</span>
            </p>
          </div>

          <div class="hidden sm:block text-sm text-gray-500 dark:text-slate-400 flex-shrink-0">{{ m.funcao ?? '—' }}</div>

          <div class="text-right flex-shrink-0">
            <span :class="membroStatusClass(m.status)"
                  class="inline-block text-xs font-bold px-2.5 py-1 rounded-full">
              {{ membroStatusLabel(m.status) }}
            </span>
            <p v-if="m.confirmado_em" class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">{{ m.confirmado_em }}</p>
          </div>
        </div>
      </div>

      <div v-else class="py-10 text-center text-gray-400 dark:text-slate-500 text-sm">
        Nenhum membro escalado ainda. <Link :href="`/admin/escalas/${escala.id}/edit`" class="text-blue-600 dark:text-blue-400 hover:underline">Editar escala</Link> para adicionar.
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({ escala: Object })

const membroStats = computed(() => {
  const total       = props.escala.membros?.length ?? 0
  const confirmados = props.escala.membros?.filter(m => m.status === 'confirmado').length ?? 0
  const recusados   = props.escala.membros?.filter(m => m.status === 'recusado').length ?? 0
  const pendentes   = total - confirmados - recusados
  return [
    { label: 'Confirmados', value: confirmados, color: 'text-green-600' },
    { label: 'Pendentes',   value: pendentes,   color: 'text-yellow-600' },
    { label: 'Recusados',   value: recusados,   color: 'text-red-500' },
  ]
})

function formatarData(data) {
  if (!data) return ''
  return new Date(data + 'T12:00:00').toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

function statusLabel(s) {
  return { pendente: 'Pendente', confirmada: 'Confirmada', em_andamento: 'Em andamento', concluida: 'Concluída', cancelada: 'Cancelada' }[s] ?? s
}
function statusClass(s) {
  return { pendente: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400', confirmada: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400', em_andamento: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400', concluida: 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-300', cancelada: 'bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400' }[s] ?? ''
}
function membroStatusLabel(s) {
  return { pendente: 'Pendente', confirmado: 'Confirmado', recusado: 'Recusado', trocado: 'Trocado' }[s] ?? s
}
function membroStatusClass(s) {
  return { pendente: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400', confirmado: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400', recusado: 'bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400', trocado: 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400' }[s] ?? ''
}
</script>
