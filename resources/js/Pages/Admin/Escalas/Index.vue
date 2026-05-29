<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 mb-1">Gestão</p>
        <h1 class="text-3xl font-bold text-gray-900">Escalas</h1>
        <p class="text-gray-500 text-sm mt-1">{{ escalas.length }} escala(s) encontrada(s)</p>
      </div>
      <Link href="/admin/escalas/create"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Nova Escala
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- FILTRO STATUS -->
    <div class="flex gap-2 mb-6 flex-wrap">
      <button v-for="s in statusOptions" :key="s.value"
              @click="filtroStatus = filtroStatus === s.value ? null : s.value"
              class="px-3 py-1.5 rounded-full text-xs font-semibold border transition"
              :class="filtroStatus === s.value ? s.activeClass : 'border-gray-200 text-gray-500 hover:bg-gray-50'">
        {{ s.label }}
      </button>
    </div>

    <!-- LISTA -->
    <div class="space-y-3">
      <div v-for="e in escalasFiltered" :key="e.id"
           class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition flex items-center gap-5">

        <!-- DATA -->
        <div class="text-center min-w-[54px]">
          <p class="text-2xl font-black text-gray-900 leading-none">{{ diaDoMes(e.data) }}</p>
          <p class="text-[11px] font-bold uppercase text-gray-400">{{ mesAbrev(e.data) }}</p>
        </div>

        <!-- DIVIDER -->
        <div class="w-px h-10 bg-gray-100"></div>

        <!-- INFO -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-0.5">
            <p class="font-bold text-gray-900 truncate">{{ e.titulo }}</p>
            <span :class="statusClass(e.status)"
                  class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0">
              {{ statusLabel(e.status) }}
            </span>
          </div>
          <p class="text-xs text-gray-400">
            {{ e.grupo?.nome }} · {{ e.hora_inicio }} – {{ e.hora_fim }}
          </p>
        </div>

        <!-- MEMBROS -->
        <div class="text-center px-4">
          <p class="text-sm font-bold text-gray-900">{{ e.confirmados }}/{{ e.total_membros }}</p>
          <p class="text-[10px] text-gray-400">confirmados</p>
        </div>

        <!-- AÇÕES -->
        <div class="flex gap-2 flex-shrink-0">
          <Link :href="`/admin/escalas/${e.id}`"
                class="text-xs font-semibold text-gray-500 hover:text-blue-600 px-3 py-1.5 rounded hover:bg-blue-50 transition">
            Ver
          </Link>
          <Link :href="`/admin/escalas/${e.id}/edit`"
                class="text-xs font-semibold text-gray-500 hover:text-blue-600 px-3 py-1.5 rounded hover:bg-blue-50 transition">
            Editar
          </Link>
          <button @click="confirmarExclusao(e)"
                  class="text-xs font-semibold text-gray-500 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 transition">
            Excluir
          </button>
        </div>
      </div>

      <div v-if="!escalasFiltered.length" class="py-20 text-center text-gray-400 bg-white border border-gray-200 rounded-xl">
        <p class="text-4xl mb-3">◱</p>
        <p class="font-medium">Nenhuma escala encontrada.</p>
        <Link href="/admin/escalas/create"
              class="mt-4 inline-block text-sm text-blue-600 hover:underline">
          Criar primeira escala
        </Link>
      </div>
    </div>

    <!-- MODAL EXCLUSÃO -->
    <div v-if="escalaParaExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 mb-2">Excluir escala?</h3>
        <p class="text-sm text-gray-500 mb-6">
          "<strong>{{ escalaParaExcluir.titulo }}</strong>" e todos os membros escalados serão removidos.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="escalaParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/escalas/${escalaParaExcluir.id}`" method="delete" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
            Excluir
          </Link>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({ escalas: Array })

const filtroStatus = ref(null)
const escalaParaExcluir = ref(null)

const statusOptions = [
  { value: 'pendente',     label: 'Pendente',      activeClass: 'border-yellow-400 bg-yellow-50 text-yellow-700' },
  { value: 'confirmada',   label: 'Confirmada',    activeClass: 'border-green-400 bg-green-50 text-green-700' },
  { value: 'em_andamento', label: 'Em andamento',  activeClass: 'border-blue-400 bg-blue-50 text-blue-700' },
  { value: 'concluida',    label: 'Concluída',     activeClass: 'border-gray-400 bg-gray-50 text-gray-700' },
  { value: 'cancelada',    label: 'Cancelada',     activeClass: 'border-red-400 bg-red-50 text-red-700' },
]

const escalasFiltered = computed(() =>
  filtroStatus.value
    ? props.escalas.filter(e => e.status === filtroStatus.value)
    : props.escalas
)

function diaDoMes(data) { return data ? new Date(data + 'T12:00:00').getDate() : '—' }
function mesAbrev(data) {
  if (!data) return ''
  return new Date(data + 'T12:00:00').toLocaleString('pt-BR', { month: 'short' }).replace('.', '')
}

function statusLabel(s) {
  return { pendente: 'Pendente', confirmada: 'Confirmada', em_andamento: 'Em andamento', concluida: 'Concluída', cancelada: 'Cancelada' }[s] ?? s
}
function statusClass(s) {
  return {
    pendente:     'bg-yellow-50 text-yellow-700',
    confirmada:   'bg-green-50 text-green-700',
    em_andamento: 'bg-blue-50 text-blue-700',
    concluida:    'bg-gray-100 text-gray-500',
    cancelada:    'bg-red-50 text-red-500',
  }[s] ?? 'bg-gray-50 text-gray-400'
}

function confirmarExclusao(e) { escalaParaExcluir.value = e }
</script>
