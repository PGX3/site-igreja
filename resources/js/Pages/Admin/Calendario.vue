<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-6 flex items-center justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Gestão</p>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">{{ label }}</h1>
      </div>
      <div class="flex items-center gap-1.5">
        <Link :href="`/admin/calendario?mes=${prev}`" preserve-scroll
              class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition text-lg">‹</Link>
        <Link href="/admin/calendario" preserve-scroll
              class="px-3 h-9 flex items-center rounded-lg border border-gray-200 dark:border-slate-600 text-xs font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition">Hoje</Link>
        <Link :href="`/admin/calendario?mes=${next}`" preserve-scroll
              class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 transition text-lg">›</Link>
      </div>
    </div>

    <!-- LEGENDA -->
    <div class="flex flex-wrap gap-3 mb-4 text-xs">
      <span v-for="t in tipos" :key="t.tipo" class="flex items-center gap-1.5 text-gray-500 dark:text-slate-400">
        <span class="w-2.5 h-2.5 rounded-full" :class="t.dot"></span>{{ t.label }}
      </span>
    </div>

    <!-- GRADE -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <!-- Cabeçalho dias da semana -->
      <div class="grid grid-cols-7 border-b border-gray-100 dark:border-slate-700">
        <div v-for="d in semana" :key="d" class="py-2 text-center text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
          {{ d }}
        </div>
      </div>
      <!-- Dias -->
      <div class="grid grid-cols-7">
        <div v-for="(cel, i) in celulas" :key="i"
             class="min-h-[92px] border-b border-r border-gray-50 dark:border-slate-700/50 p-1.5 align-top"
             :class="[
               (i % 7 === 6) ? 'border-r-0' : '',
               cel.date ? '' : 'bg-gray-50/50 dark:bg-slate-900/20',
             ]">
          <template v-if="cel.date">
            <div class="text-right">
              <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full"
                    :class="cel.date === hoje ? 'bg-blue-600 text-white' : 'text-gray-500 dark:text-slate-400'">
                {{ cel.dia }}
              </span>
            </div>
            <div class="mt-0.5 space-y-1">
              <component :is="ev.href ? 'a' : 'div'" v-for="(ev, j) in cel.eventos" :key="j"
                         :href="ev.href"
                         class="block text-left text-[10px] leading-tight px-1.5 py-1 rounded truncate transition"
                         :class="corTipo(ev.tipo)"
                         :title="`${ev.hora ? ev.hora + ' · ' : ''}${ev.titulo}`">
                <span v-if="ev.hora" class="font-semibold">{{ ev.hora }}</span> {{ ev.titulo }}
              </component>
            </div>
          </template>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  mes:     String,   // 'YYYY-MM'
  label:   String,
  prev:    String,
  next:    String,
  hoje:    String,
  eventos: { type: Array, default: () => [] },
})

const semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']

const tipos = [
  { tipo: 'culto',  label: 'Cultos',  dot: 'bg-indigo-500' },
  { tipo: 'escala', label: 'Escalas', dot: 'bg-blue-500' },
  { tipo: 'evento', label: 'Eventos', dot: 'bg-purple-500' },
]

function corTipo(tipo) {
  return {
    culto:  'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40',
    escala: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40',
    evento: 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/40',
  }[tipo] ?? 'bg-gray-100 text-gray-600'
}

// Agrupa eventos por data
const porData = computed(() => {
  const map = {}
  for (const e of props.eventos) {
    (map[e.date] ??= []).push(e)
  }
  return map
})

// Monta as células do mês (com preenchimento antes do dia 1)
const celulas = computed(() => {
  const [ano, mes] = props.mes.split('-').map(Number)
  const primeiro = new Date(ano, mes - 1, 1)
  const diasNoMes = new Date(ano, mes, 0).getDate()
  const inicioSemana = primeiro.getDay() // 0 = domingo

  const cels = []
  for (let i = 0; i < inicioSemana; i++) cels.push({ date: null })
  for (let d = 1; d <= diasNoMes; d++) {
    const date = `${ano}-${String(mes).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    cels.push({ date, dia: d, eventos: porData.value[date] ?? [] })
  }
  while (cels.length % 7 !== 0) cels.push({ date: null })
  return cels
})
</script>
