<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex items-start justify-between">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 mb-1">
          <Link href="/admin/escalas" class="hover:text-blue-600">Escalas</Link>
          <span class="mx-1">›</span>
          Detalhe
        </p>
        <h1 class="text-3xl font-bold text-gray-900">{{ escala.titulo }}</h1>
        <div class="flex items-center gap-3 mt-2">
          <span :class="statusClass(escala.status)"
                class="text-xs font-bold px-2.5 py-1 rounded-full">
            {{ statusLabel(escala.status) }}
          </span>
          <span class="text-sm text-gray-500">
            {{ formatarData(escala.data) }} · {{ escala.hora_inicio }} – {{ escala.hora_fim }}
          </span>
          <span class="text-sm text-gray-400">{{ escala.grupo?.nome }}</span>
        </div>
      </div>

      <Link :href="`/admin/escalas/${escala.id}/edit`"
            class="border border-gray-300 text-gray-700 hover:bg-gray-100 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
        Editar
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- DESCRIÇÃO -->
    <div v-if="escala.descricao"
         class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm mb-6 text-sm text-gray-600">
      {{ escala.descricao }}
    </div>

    <!-- RESUMO MEMBROS -->
    <div class="grid grid-cols-3 gap-4 mb-8">
      <div v-for="stat in membroStats" :key="stat.label"
           class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm text-center">
        <p class="text-3xl font-bold" :class="stat.color">{{ stat.value }}</p>
        <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider">{{ stat.label }}</p>
      </div>
    </div>

    <!-- LISTA MEMBROS -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <p class="font-semibold text-gray-900">Membros Escalados</p>
        <span class="text-xs text-gray-400">{{ escala.membros?.length ?? 0 }} total</span>
      </div>

      <div v-if="escala.membros?.length">
        <div v-for="m in escala.membros" :key="m.id"
             class="flex items-center gap-4 px-6 py-4 border-b border-gray-50 hover:bg-gray-50 transition">
          <!-- Avatar inicial -->
          <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold text-sm
                      flex items-center justify-center flex-shrink-0">
            {{ m.user?.name?.[0]?.toUpperCase() }}
          </div>

          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 text-sm">{{ m.user?.name }}</p>
            <p class="text-xs text-gray-400">{{ m.user?.email }}</p>
          </div>

          <div class="text-sm text-gray-500">{{ m.funcao ?? '—' }}</div>

          <div class="text-right">
            <span :class="membroStatusClass(m.status)"
                  class="inline-block text-xs font-bold px-2.5 py-1 rounded-full">
              {{ membroStatusLabel(m.status) }}
            </span>
            <p v-if="m.confirmado_em" class="text-[10px] text-gray-400 mt-0.5">{{ m.confirmado_em }}</p>
          </div>
        </div>
      </div>

      <div v-else class="py-10 text-center text-gray-400 text-sm">
        Nenhum membro escalado ainda. <Link :href="`/admin/escalas/${escala.id}/edit`" class="text-blue-600 hover:underline">Editar escala</Link> para adicionar.
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
  const total     = props.escala.membros?.length ?? 0
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
  return { pendente: 'bg-yellow-50 text-yellow-700', confirmada: 'bg-green-50 text-green-700', em_andamento: 'bg-blue-50 text-blue-700', concluida: 'bg-gray-100 text-gray-500', cancelada: 'bg-red-50 text-red-500' }[s] ?? ''
}
function membroStatusLabel(s) {
  return { pendente: 'Pendente', confirmado: 'Confirmado', recusado: 'Recusado', trocado: 'Trocado' }[s] ?? s
}
function membroStatusClass(s) {
  return { pendente: 'bg-yellow-50 text-yellow-700', confirmado: 'bg-green-50 text-green-700', recusado: 'bg-red-50 text-red-500', trocado: 'bg-purple-50 text-purple-600' }[s] ?? ''
}
</script>
