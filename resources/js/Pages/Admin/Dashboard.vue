<template>
  <AdminLayout>

    <!-- ── HEADER ── -->
    <div class="mb-8">
      <p class="text-[11px] font-bold tracking-[0.3em] uppercase text-gray-400 dark:text-slate-500 mb-1">Visão Geral</p>
      <div class="flex items-center gap-3">
        <h1 class="text-3xl font-black text-gray-900 dark:text-white">{{ greeting }}, {{ firstName }}</h1>
      </div>
      <div class="flex items-center gap-2 mt-1.5">
        <span class="text-sm text-gray-500 dark:text-slate-400">Igreja em Charqueadas</span>
        <span class="text-gray-300 dark:text-slate-600">·</span>
        <span class="text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400 px-2.5 py-1 rounded-full">
          Painel de Controle
        </span>
      </div>
    </div>

    <!-- ── STAT CARDS ── -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
      <div v-for="card in statCards" :key="card.label"
           class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-5 shadow-sm
                  hover:shadow-md transition-shadow overflow-hidden relative">
        <!-- top row -->
        <div class="flex items-start justify-between mb-4">
          <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500">{{ card.label }}</p>
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
               :style="{ background: card.iconBg }">
            <AppIcon :name="card.icon" size="sm" :style="{ color: card.iconColor }" />
          </div>
        </div>

        <!-- value -->
        <p class="text-4xl font-black text-gray-900 dark:text-white mb-1 leading-none">{{ card.value }}</p>

        <!-- sub -->
        <div class="flex items-center gap-1.5 mb-4">
          <span v-if="card.trend > 0"
                class="flex items-center gap-0.5 text-[11px] font-bold text-emerald-600
                       bg-emerald-50 dark:bg-emerald-900/20 px-1.5 py-0.5 rounded-full">
            <AppIcon name="trending-up" size="xs" />
            +{{ card.trend }}
          </span>
          <span class="text-xs text-gray-400 dark:text-slate-500">{{ card.sub }}</span>
        </div>

        <!-- sparkline -->
        <svg class="w-full h-8" viewBox="0 0 100 30" preserveAspectRatio="none">
          <polyline
            :points="card.spark"
            fill="none"
            :stroke="card.iconColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            opacity="0.6"
          />
        </svg>
      </div>
    </div>

    <!-- ── BOTTOM ROW ── -->
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

      <!-- Próximo Culto -->
      <div class="xl:col-span-3">
        <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-3">
          Próximo Culto
        </p>

        <div v-if="proximoCulto"
             class="rounded-2xl overflow-hidden shadow-md"
             style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #1d4ed8 100%)">
          <div class="p-6 text-white">
            <!-- Date row -->
            <div class="flex items-start gap-5 mb-6">
              <div class="text-center bg-white/10 rounded-xl px-4 py-3 backdrop-blur-sm min-w-[64px]">
                <p class="text-[11px] font-bold uppercase tracking-widest text-blue-300">
                  {{ proximoCulto.dia_semana?.slice(0, 3) }}
                </p>
                <p class="text-4xl font-black leading-none my-1">{{ proximoCulto.dia }}</p>
                <p class="text-[11px] font-bold uppercase tracking-widest text-blue-300">
                  {{ proximoCulto.mes }}
                </p>
              </div>

              <div class="flex-1 pt-1">
                <h2 class="text-xl font-black mb-1">{{ proximoCulto.nome }}</h2>
                <div class="flex items-center gap-2 text-blue-200 text-sm">
                  <span>{{ proximoCulto.horario }}</span>
                  <span class="text-blue-400">·</span>
                  <span>{{ proximoCulto.dia_semana }}s</span>
                  <span v-if="proximoCulto.descricao" class="text-blue-400">·</span>
                  <span v-if="proximoCulto.descricao" class="text-blue-200 text-xs">
                    {{ proximoCulto.descricao }}
                  </span>
                </div>

                <div class="mt-4 flex items-center gap-3 flex-wrap">
                  <Link href="/admin/cultos"
                        class="text-xs font-semibold text-white/70 hover:text-white
                               border border-white/20 hover:border-white/40 rounded-lg px-3 py-1.5
                               transition-colors">
                    Ver Cultos →
                  </Link>
                  <Link href="/admin/escalas/create"
                        class="text-xs font-semibold text-white bg-white/15 hover:bg-white/25
                               rounded-lg px-3 py-1.5 transition-colors">
                    + Criar Escala
                  </Link>
                </div>
              </div>
            </div>

            <!-- Stats row -->
            <div class="grid grid-cols-3 gap-3">
              <div v-for="s in cultoStats" :key="s.label"
                   class="bg-white/10 rounded-xl p-3 text-center backdrop-blur-sm">
                <p class="text-xl font-black">{{ s.value }}</p>
                <p class="text-[10px] text-blue-300 uppercase tracking-wider mt-0.5">{{ s.label }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty culto state -->
        <div v-else
             class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700/50 shadow-sm p-10 text-center">
          <p class="text-3xl mb-2">🎵</p>
          <p class="font-semibold text-gray-600 dark:text-slate-300">Nenhum culto cadastrado ainda.</p>
          <Link href="/admin/cultos/create"
                class="mt-3 inline-block text-sm text-blue-600 dark:text-blue-400 hover:underline">
            Cadastrar primeiro culto
          </Link>
        </div>
      </div>

      <!-- Escala da Semana (pastor/líder) -->
      <div v-if="role !== 'membro'" class="xl:col-span-2">
        <div class="flex items-center justify-between mb-3">
          <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500">
            Escala da Semana
          </p>
          <Link href="/admin/escalas"
                class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 flex items-center gap-0.5">
            Ver tudo <AppIcon name="chevron-right" size="xs" />
          </Link>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 shadow-sm overflow-hidden">
          <div v-if="escalasProximas?.length" class="divide-y divide-gray-50 dark:divide-slate-700/50">
            <Link v-for="e in escalasProximas" :key="e.id"
                  :href="`/admin/escalas/${e.id}`"
                  class="flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
              <div class="text-center min-w-[40px]">
                <p class="text-[10px] font-bold uppercase text-gray-400 dark:text-slate-500 leading-none">{{ e.dia_semana }}</p>
                <p class="text-xl font-black text-gray-900 dark:text-white leading-none mt-0.5">{{ e.dia }}</p>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-gray-900 dark:text-white truncate">{{ e.titulo }}</p>
                <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">{{ e.hora_inicio }}</p>
              </div>
              <div class="flex flex-col items-end gap-1.5">
                <div class="flex -space-x-2">
                  <div v-for="m in e.membros.slice(0, 4)" :key="m.id"
                       class="w-6 h-6 rounded-full text-white text-[9px] font-bold
                              flex items-center justify-center ring-2 ring-white dark:ring-slate-800"
                       :style="{ background: m.color }">{{ m.initials }}</div>
                  <div v-if="e.total_membros > 4"
                       class="w-6 h-6 rounded-full bg-gray-200 dark:bg-slate-600 text-gray-500 dark:text-slate-300 text-[9px]
                              font-bold flex items-center justify-center ring-2 ring-white dark:ring-slate-800">
                    +{{ e.total_membros - 4 }}
                  </div>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 dark:text-slate-400 bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded-full truncate max-w-[80px]">
                  {{ e.grupo?.nome }}
                </span>
              </div>
            </Link>
          </div>
          <div v-else class="py-12 text-center px-4">
            <p class="text-3xl mb-2">📅</p>
            <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Nenhuma escala nos próximos dias.</p>
            <Link href="/admin/escalas/create"
                  class="mt-2 inline-block text-xs text-blue-600 dark:text-blue-400 hover:underline">
              Criar escala
            </Link>
          </div>
        </div>
      </div>

      <!-- Minhas Próximas Escalas (membro) -->
      <div v-if="role === 'membro'" class="xl:col-span-2">
        <div class="flex items-center justify-between mb-3">
          <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500">
            Minhas Próximas Escalas
          </p>
          <Link href="/admin/minhas-escalas"
                class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 flex items-center gap-0.5">
            Ver tudo <AppIcon name="chevron-right" size="xs" />
          </Link>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 shadow-sm overflow-hidden">
          <div v-if="minhasProximas?.length" class="divide-y divide-gray-50 dark:divide-slate-700/50">
            <Link v-for="e in minhasProximas" :key="e.id"
                  :href="`/admin/minhas-escalas`"
                  class="flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
              <div class="text-center min-w-[40px]">
                <p class="text-[10px] font-bold uppercase text-gray-400 dark:text-slate-500 leading-none">{{ e.dia_semana }}</p>
                <p class="text-xl font-black text-gray-900 dark:text-white leading-none mt-0.5">{{ e.dia }}</p>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-gray-900 dark:text-white truncate">{{ e.titulo }}</p>
                <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5">{{ e.hora_inicio }} · {{ e.grupo?.nome }}</p>
              </div>
              <span :class="statusBadge(e.status)"
                    class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0">
                {{ statusLabel(e.status) }}
              </span>
            </Link>
          </div>
          <div v-else class="py-12 text-center px-4">
            <p class="text-3xl mb-2">🎉</p>
            <p class="text-sm font-medium text-gray-500 dark:text-slate-400">Nenhuma escala nos próximos dias.</p>
          </div>
        </div>
      </div>

    </div>

    <!-- ── AÇÕES RÁPIDAS (pastor) ── -->
    <div v-if="role === 'pastor'" class="mt-8 bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 shadow-sm p-5">
      <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-4">Ações Rápidas</p>
      <div class="flex flex-wrap gap-3">
        <Link href="/admin/cultos/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl
                     text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
          <AppIcon name="mic" size="xs" />
          Novo Culto
        </Link>
        <Link href="/admin/escalas/create"
              class="border border-gray-200 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-slate-200
                     px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors
                     flex items-center gap-2">
          <AppIcon name="calendar" size="xs" />
          Nova Escala
        </Link>
        <Link href="/admin/grupos/create"
              class="border border-gray-200 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-slate-200
                     px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors
                     flex items-center gap-2">
          <AppIcon name="users" size="xs" />
          Novo Grupo
        </Link>
        <Link href="/admin/usuarios/create"
              class="border border-gray-200 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-gray-700 dark:text-slate-200
                     px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors
                     flex items-center gap-2">
          <AppIcon name="user" size="xs" />
          Novo Usuário
        </Link>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppIcon from '@/Components/AppIcon.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  totalCultos:     Number,
  novasSugestoes:  Number,
  novosPedidos:    Number,
  totalSugestoes:  Number,
  totalPedidos:    Number,
  proximoCulto:    Object,
  escalasProximas: Array,
  minhasProximas:  Array,
})

function statusLabel(s) {
  return { pendente: 'Pendente', confirmado: 'Confirmado', recusado: 'Recusado', trocado: 'Trocado' }[s] ?? s
}

function statusBadge(s) {
  return {
    pendente:   'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400',
    confirmado: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400',
    recusado:   'bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400',
    trocado:    'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400',
  }[s] ?? ''
}

const page      = usePage()
const user      = computed(() => page.props.auth?.user)
const role      = computed(() => user.value?.role)
const firstName = computed(() => (user.value?.name ?? '').split(' ')[0])

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Bom dia'
  if (h < 18) return 'Boa tarde'
  return 'Boa noite'
})

function spark(values) {
  const w = 100, h = 28
  const max = Math.max(...values), min = Math.min(...values)
  const range = (max - min) || 1
  return values.map((v, i) => {
    const x = (i / (values.length - 1)) * w
    const y = h - 2 - ((v - min) / range) * (h - 4)
    return `${x.toFixed(1)},${y.toFixed(1)}`
  }).join(' ')
}

const statCards = computed(() => [
  {
    label:     'Cultos',
    value:     props.totalCultos   ?? 0,
    trend:     0,
    sub:       'cadastrados',
    icon:      'mic',
    iconBg:    '#eff6ff',
    iconColor: '#3b82f6',
    spark:     spark([5, 6, 5, 7, 8, 9, props.totalCultos ?? 0]),
  },
  {
    label:     'Sugestões',
    value:     props.novasSugestoes ?? 0,
    trend:     props.novasSugestoes ?? 0,
    sub:       props.novasSugestoes > 0 ? 'aguardando' : 'tudo em dia',
    icon:      'lightbulb',
    iconBg:    '#fefce8',
    iconColor: '#eab308',
    spark:     spark([1, 2, 1, 3, 2, 3, props.novasSugestoes ?? 0]),
  },
  {
    label:     'Pedidos de Oração',
    value:     props.novosPedidos  ?? 0,
    trend:     props.novosPedidos  ?? 0,
    sub:       props.novosPedidos > 0 ? 'esta semana' : 'tudo em dia',
    icon:      'heart',
    iconBg:    '#fdf2f8',
    iconColor: '#ec4899',
    spark:     spark([2, 3, 2, 4, 3, 4, props.novosPedidos ?? 0]),
  },
])

const cultoStats = computed(() => [
  { label: 'Cultos',  value: props.totalCultos   ?? 0 },
  { label: 'Escalas', value: props.escalasProximas?.length ?? 0 },
  { label: 'Grupos',  value: '—' },
])
</script>
