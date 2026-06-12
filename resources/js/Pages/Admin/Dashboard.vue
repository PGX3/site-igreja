<template>
  <AdminLayout>

    <!-- ── HEADER ── -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
      <div>
        <p class="text-[11px] font-bold tracking-[0.3em] uppercase text-gray-400 dark:text-slate-500 mb-1">Visão Geral</p>
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white">{{ greeting }}, {{ firstName }}</h1>
        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
          <span class="text-sm text-gray-500 dark:text-slate-400">Igreja em Charqueadas</span>
          <span class="text-gray-300 dark:text-slate-600">·</span>
          <span class="text-xs font-semibold text-blue-600 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-400 px-2.5 py-1 rounded-full">
            Painel de Controle
          </span>
        </div>
      </div>

      <!-- Ações Rápidas (pastor) -->
      <div v-if="role === 'pastor'" class="flex flex-wrap gap-2">
        <Link href="/admin/membros/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-xl
                     text-sm font-semibold shadow-sm transition-colors flex items-center gap-1.5">
          <AppIcon name="user" size="xs" />
          <span class="hidden xs:inline sm:inline">+ Membro</span>
          <span class="sm:hidden">+</span>
        </Link>
        <Link href="/admin/cultos/create"
              class="border border-gray-200 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700
                     text-gray-700 dark:text-slate-200 px-3 sm:px-4 py-2 rounded-xl text-sm font-semibold
                     transition-colors flex items-center gap-1.5">
          <AppIcon name="mic" size="xs" />
          <span class="hidden sm:inline">+ Culto</span>
          <span class="sm:hidden">+</span>
        </Link>
        <Link href="/admin/escalas/create"
              class="border border-gray-200 dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-700
                     text-gray-700 dark:text-slate-200 px-3 sm:px-4 py-2 rounded-xl text-sm font-semibold
                     transition-colors flex items-center gap-1.5">
          <AppIcon name="calendar" size="xs" />
          <span class="hidden sm:inline">+ Escala</span>
          <span class="sm:hidden">+</span>
        </Link>
      </div>
      <div v-else-if="role === 'lider'">
        <Link href="/admin/escalas/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl
                     text-sm font-semibold shadow-sm transition-colors flex items-center gap-1.5">
          <AppIcon name="calendar" size="xs" />
          + Escala
        </Link>
      </div>
    </div>

    <!-- ── STATS PESSOAS (pastor/líder) ── -->
    <div v-if="role !== 'membro'" class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
      <Link href="/admin/membros"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-3 sm:p-5
                   shadow-sm hover:shadow-md transition-shadow flex items-center gap-3 sm:gap-4 cursor-pointer">
        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
          <AppIcon name="users" size="sm" class="text-blue-600 dark:text-blue-400" />
        </div>
        <div class="min-w-0">
          <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-none">{{ totalMembros }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Membros</p>
        </div>
        <div v-if="novosMembrosMes > 0" class="ml-auto hidden sm:block">
          <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-full whitespace-nowrap">
            +{{ novosMembrosMes }} este mês
          </span>
        </div>
      </Link>

      <Link href="/admin/visitantes"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-3 sm:p-5
                   shadow-sm hover:shadow-md transition-shadow flex items-center gap-3 sm:gap-4 cursor-pointer">
        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center flex-shrink-0">
          <AppIcon name="user" size="sm" class="text-amber-500 dark:text-amber-400" />
        </div>
        <div class="min-w-0">
          <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-none">{{ totalVisitantes }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Visitantes</p>
        </div>
        <div v-if="novosVisitantesMes > 0" class="ml-auto hidden sm:block">
          <span class="text-[11px] font-bold text-amber-600 bg-amber-50 dark:bg-amber-900/20 px-2 py-0.5 rounded-full whitespace-nowrap">
            +{{ novosVisitantesMes }} este mês
          </span>
        </div>
      </Link>

      <Link href="/admin/aniversarios"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-3 sm:p-5
                   shadow-sm hover:shadow-md transition-shadow flex items-center gap-3 sm:gap-4 cursor-pointer">
        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center flex-shrink-0">
          <AppIcon name="gift" size="sm" class="text-purple-500 dark:text-purple-400" />
        </div>
        <div class="min-w-0">
          <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-none">{{ anivCount }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Aniversários</p>
        </div>
        <div v-if="anivCount > 0" class="ml-auto hidden sm:block">
          <span class="text-[11px] font-bold text-purple-600 bg-purple-50 dark:bg-purple-900/20 px-2 py-0.5 rounded-full">
            30 dias
          </span>
        </div>
      </Link>

      <Link v-if="role === 'pastor'" href="/admin/cultos"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-3 sm:p-5
                   shadow-sm hover:shadow-md transition-shadow flex items-center gap-3 sm:gap-4 cursor-pointer">
        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center flex-shrink-0">
          <AppIcon name="mic" size="sm" class="text-indigo-500 dark:text-indigo-400" />
        </div>
        <div class="min-w-0">
          <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-none">{{ totalCultos ?? 0 }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Cultos</p>
        </div>
      </Link>

      <!-- Para líder: 4° card é Escalas -->
      <Link v-if="role === 'lider'" href="/admin/escalas"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-3 sm:p-5
                   shadow-sm hover:shadow-md transition-shadow flex items-center gap-3 sm:gap-4 cursor-pointer">
        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center flex-shrink-0">
          <AppIcon name="calendar" size="sm" class="text-indigo-500 dark:text-indigo-400" />
        </div>
        <div class="min-w-0">
          <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-none">{{ escalasProximas?.length ?? 0 }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Escalas próximas</p>
        </div>
      </Link>
    </div>

    <!-- ── STAT CARDS PASTOR: Sugestões + Pedidos ── -->
    <div v-if="role === 'pastor'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-8">
      <Link href="/admin/sugestoes"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-5
                   shadow-sm hover:shadow-md transition-shadow flex items-center gap-4 cursor-pointer"
            :class="novasSugestoes > 0 ? 'border-yellow-200 dark:border-yellow-800/50' : ''">
        <div class="w-11 h-11 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 flex items-center justify-center flex-shrink-0">
          <AppIcon name="lightbulb" size="sm" class="text-yellow-500 dark:text-yellow-400" />
        </div>
        <div class="flex-1">
          <p class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{ novasSugestoes ?? 0 }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
            {{ (novasSugestoes ?? 0) > 0 ? 'Sugestões aguardando' : 'Sugestões — tudo em dia' }}
          </p>
        </div>
        <span v-if="novasSugestoes > 0"
              class="flex-shrink-0 text-[11px] font-bold text-yellow-700 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400 px-2.5 py-1 rounded-full">
          Ver
        </span>
      </Link>

      <Link href="/admin/pedidos-oracao"
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-5
                   shadow-sm hover:shadow-md transition-shadow flex items-center gap-4 cursor-pointer"
            :class="novosPedidos > 0 ? 'border-pink-200 dark:border-pink-800/50' : ''">
        <div class="w-11 h-11 rounded-xl bg-pink-50 dark:bg-pink-900/20 flex items-center justify-center flex-shrink-0">
          <AppIcon name="heart" size="sm" class="text-pink-500 dark:text-pink-400" />
        </div>
        <div class="flex-1">
          <p class="text-2xl font-black text-gray-900 dark:text-white leading-none">{{ novosPedidos ?? 0 }}</p>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
            {{ (novosPedidos ?? 0) > 0 ? 'Pedidos de oração' : 'Pedidos de oração — em dia' }}
          </p>
        </div>
        <span v-if="novosPedidos > 0"
              class="flex-shrink-0 text-[11px] font-bold text-pink-700 bg-pink-100 dark:bg-pink-900/30 dark:text-pink-400 px-2.5 py-1 rounded-full">
          Ver
        </span>
      </Link>
    </div>

    <!-- ── STAT CARDS MEMBRO ── -->
    <div v-if="role === 'membro'" class="grid grid-cols-2 gap-3 sm:gap-4 mb-8">
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-4 sm:p-5 shadow-sm">
        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center mb-3">
          <AppIcon name="calendar" size="sm" class="text-indigo-500 dark:text-indigo-400" />
        </div>
        <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-none">{{ minhasProximas?.length ?? 0 }}</p>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Escalas próximas</p>
      </div>
      <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 p-4 sm:p-5 shadow-sm">
        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center mb-3">
          <AppIcon name="mic" size="sm" class="text-blue-500 dark:text-blue-400" />
        </div>
        <p class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-none">{{ totalCultos ?? 0 }}</p>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Cultos cadastrados</p>
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
          <div class="p-4 sm:p-6 text-white">
            <!-- Date row -->
            <div class="flex items-start gap-3 sm:gap-5 mb-4 sm:mb-6">
              <div class="text-center bg-white/10 rounded-xl px-3 sm:px-4 py-2 sm:py-3 backdrop-blur-sm min-w-[54px] sm:min-w-[64px]">
                <p class="text-[10px] font-bold uppercase tracking-widest text-blue-300">
                  {{ proximoCulto.dia_semana?.slice(0, 3) }}
                </p>
                <p class="text-3xl sm:text-4xl font-black leading-none my-1">{{ proximoCulto.dia }}</p>
                <p class="text-[10px] font-bold uppercase tracking-widest text-blue-300">
                  {{ proximoCulto.mes }}
                </p>
              </div>

              <div class="flex-1 pt-1 min-w-0">
                <h2 class="text-lg sm:text-xl font-black mb-1 truncate">{{ proximoCulto.nome }}</h2>
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-blue-200 text-sm">
                  <span>{{ proximoCulto.horario }}</span>
                  <span class="text-blue-400">·</span>
                  <span>{{ proximoCulto.dia_semana }}s</span>
                </div>

                <div class="mt-3 sm:mt-4 flex items-center gap-2 sm:gap-3 flex-wrap">
                  <Link v-if="role === 'pastor'" href="/admin/cultos"
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
            <div class="grid grid-cols-3 gap-2 sm:gap-3">
              <div v-for="s in cultoStats" :key="s.label"
                   class="bg-white/10 rounded-xl p-2 sm:p-3 text-center backdrop-blur-sm">
                <p class="text-lg sm:text-xl font-black">{{ s.value }}</p>
                <p class="text-[9px] sm:text-[10px] text-blue-300 uppercase tracking-wider mt-0.5">{{ s.label }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty culto state -->
        <div v-else
             class="rounded-2xl bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700/50 shadow-sm p-10 text-center">
          <p class="text-3xl mb-2">🎵</p>
          <p class="font-semibold text-gray-600 dark:text-slate-300">Nenhum culto cadastrado ainda.</p>
          <Link v-if="role === 'pastor'" href="/admin/cultos/create"
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
                  href="/admin/minhas-escalas"
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

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppIcon from '@/Components/AppIcon.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  totalCultos:         Number,
  novasSugestoes:      Number,
  novosPedidos:        Number,
  totalSugestoes:      Number,
  totalPedidos:        Number,
  totalMembros:        Number,
  totalVisitantes:     Number,
  novosMembrosMes:     Number,
  novosVisitantesMes:  Number,
  proximoCulto:        Object,
  escalasProximas:     Array,
  minhasProximas:      Array,
  aniversariantes:     Array,
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

const anivCount      = computed(() => props.aniversariantes?.length ?? 0)
const totalMembros   = computed(() => props.totalMembros ?? 0)
const totalVisitantes = computed(() => props.totalVisitantes ?? 0)
const novosMembrosMes = computed(() => props.novosMembrosMes ?? 0)
const novosVisitantesMes = computed(() => props.novosVisitantesMes ?? 0)
const novasSugestoes = computed(() => props.novasSugestoes ?? 0)
const novosPedidos   = computed(() => props.novosPedidos ?? 0)

const cultoStats = computed(() => [
  { label: 'Cultos',   value: props.totalCultos ?? 0 },
  { label: 'Membros',  value: totalMembros.value },
  { label: 'Escalas',  value: props.escalasProximas?.length ?? 0 },
])
</script>
