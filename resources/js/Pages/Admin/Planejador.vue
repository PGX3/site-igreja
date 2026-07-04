<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-6 flex flex-col lg:flex-row lg:items-end justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Gestão</p>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Planejador de Escalas</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Monte o mês inteiro numa grade. Cada célula preenchida vira uma escala.</p>
        <span v-if="!editavel" class="inline-block mt-2 text-xs font-semibold text-gray-500 dark:text-slate-400 bg-gray-100 dark:bg-slate-700 px-2.5 py-1 rounded-full">👁 Somente leitura, você não lidera este grupo</span>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <select :value="grupo_id" @change="trocar({ grupo_id: $event.target.value })"
                class="rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nome }}</option>
        </select>
        <div class="flex items-center gap-1">
          <button @click="trocar({ mes: prev })" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 text-lg">‹</button>
          <span class="px-2 text-sm font-semibold text-gray-700 dark:text-slate-200 min-w-[120px] text-center">{{ label }}</span>
          <button @click="trocar({ mes: next })" class="w-9 h-9 rounded-lg border border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 text-lg">›</button>
        </div>
      </div>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <div v-if="!funcoes.length"
         class="p-6 rounded-xl bg-amber-50 dark:bg-amber-900/40 border border-amber-300 dark:border-amber-700 text-sm text-amber-800 dark:text-amber-100">
      Este grupo não tem funções cadastradas. Adicione funções em
      <Link :href="`/admin/grupos/${grupo_id}/edit`" class="underline font-bold text-amber-900 dark:text-white">Editar grupo</Link>
      (ex: Transmissão, Câmera, Telão) para montar as colunas.
    </div>

    <template v-else>
      <!-- GRADE (desktop) -->
      <div class="hidden lg:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm border-collapse min-w-[720px]">
          <thead>
            <tr class="bg-gray-50 dark:bg-slate-900/40 text-left">
              <th class="px-3 py-2.5 font-semibold text-gray-600 dark:text-slate-300 sticky left-0 bg-gray-50 dark:bg-slate-900/40 z-10 whitespace-nowrap">Dia</th>
              <th v-for="f in funcoes" :key="f" class="px-3 py-2.5 font-semibold text-gray-600 dark:text-slate-300 whitespace-nowrap">{{ f }}</th>
              <th class="px-3 py-2.5 font-semibold text-gray-600 dark:text-slate-300 whitespace-nowrap">Não pode</th>
              <th class="px-2 py-2.5"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, ri) in linhas" :key="ri"
                class="border-t border-gray-100 dark:border-slate-700/50">
              <!-- Dia -->
              <td class="px-3 py-2 sticky left-0 bg-white dark:bg-slate-800 z-10 whitespace-nowrap">
                <span class="font-semibold text-gray-900 dark:text-white">{{ row.dia }}</span>
                <span class="text-xs text-gray-400 dark:text-slate-500 ml-1 capitalize">{{ row.semana }}</span>
                <span v-if="row.vinculo" class="block text-[10px] text-gray-400 dark:text-slate-500 truncate max-w-[130px]">{{ row.vinculo }}</span>
              </td>
              <!-- Funções -->
              <td v-for="f in funcoes" :key="f" class="px-1.5 py-1.5">
                <select :value="row.cells[f] ?? ''" @change="setCell(row, f, $event.target.value)"
                        :disabled="!editavel"
                        class="w-full min-w-[120px] rounded-md text-xs font-semibold px-2 py-1.5 border transition focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="[
                          editavel ? 'cursor-pointer' : 'cursor-default',
                          row.cells[f]
                            ? 'border-transparent'
                            : 'border-dashed border-gray-300 dark:border-slate-500 text-gray-400 dark:text-slate-400 bg-gray-50 dark:bg-slate-700/40' + (editavel ? ' hover:border-blue-400 hover:text-blue-500' : ''),
                        ]"
                        :style="cellStyle(row.cells[f])">
                  <option value="">+ Escalar</option>
                  <option v-for="m in membrosDisponiveis(row, f)" :key="m.id" :value="m.id">
                    {{ m.nome }}{{ conflito(row, m.id) ? ` · já em ${conflito(row, m.id)}` : '' }}
                  </option>
                </select>
                <p v-if="row.cells[f] && conflito(row, row.cells[f])" class="text-[10px] text-red-500 mt-0.5 px-1">
                  ⚠ também em {{ conflito(row, row.cells[f]) }}
                </p>
              </td>
              <!-- Não pode -->
              <td class="px-1.5 py-1.5">
                <input :value="row.restricoes" @input="row.restricoes = $event.target.value"
                       type="text" placeholder="—" :disabled="!editavel"
                       class="w-full min-w-[140px] rounded-md border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-700 dark:text-slate-200 text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-70" />
              </td>
              <!-- Remover linha manual -->
              <td class="px-2 py-1.5 text-center">
                <button v-if="editavel && !row.escala_id" @click="linhas.splice(ri, 1)"
                        class="text-gray-300 dark:text-slate-600 hover:text-red-500 text-lg leading-none" title="Remover linha">×</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- GRADE (mobile) -->
      <div class="lg:hidden space-y-3">
        <div v-for="(row, ri) in linhas" :key="ri"
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-4">
          <div class="flex items-start justify-between gap-2 mb-3">
            <div>
              <span class="font-bold text-gray-900 dark:text-white">{{ row.dia }}</span>
              <span class="text-xs text-gray-400 dark:text-slate-500 ml-1 capitalize">{{ row.semana }}</span>
              <span v-if="row.vinculo" class="block text-[11px] text-gray-400 dark:text-slate-500">{{ row.vinculo }}</span>
            </div>
            <button v-if="editavel && !row.escala_id" @click="linhas.splice(ri, 1)"
                    class="text-gray-300 dark:text-slate-600 hover:text-red-500 text-xl leading-none" title="Remover linha">×</button>
          </div>

          <div class="space-y-2.5">
            <div v-for="f in funcoes" :key="f">
              <label class="block text-[11px] font-semibold text-gray-500 dark:text-slate-400 mb-1">{{ f }}</label>
              <select :value="row.cells[f] ?? ''" @change="setCell(row, f, $event.target.value)" :disabled="!editavel"
                      class="w-full rounded-md text-sm font-semibold px-3 py-2 border focus:outline-none focus:ring-2 focus:ring-blue-500"
                      :class="row.cells[f] ? 'border-transparent' : 'border-dashed border-gray-300 dark:border-slate-500 text-gray-400 dark:text-slate-400 bg-gray-50 dark:bg-slate-700/40'"
                      :style="cellStyle(row.cells[f])">
                <option value="">+ Escalar</option>
                <option v-for="m in membrosDisponiveis(row, f)" :key="m.id" :value="m.id">
                  {{ m.nome }}{{ conflito(row, m.id) ? ` · já em ${conflito(row, m.id)}` : '' }}
                </option>
              </select>
              <p v-if="row.cells[f] && conflito(row, row.cells[f])" class="text-[10px] text-red-500 mt-0.5">
                ⚠ também em {{ conflito(row, row.cells[f]) }}
              </p>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-gray-500 dark:text-slate-400 mb-1">Não pode</label>
              <input :value="row.restricoes" @input="row.restricoes = $event.target.value"
                     type="text" placeholder="—" :disabled="!editavel"
                     class="w-full rounded-md border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-700 dark:text-slate-200 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-70" />
            </div>
          </div>
        </div>
      </div>

      <!-- AÇÕES -->
      <div v-if="editavel" class="mt-4 flex flex-wrap items-center gap-3">
        <button @click="salvar" :disabled="salvando"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ salvando ? 'Salvando…' : 'Salvar planejamento' }}
        </button>

        <div class="flex items-center gap-2">
          <input v-model="novaData" type="date"
                 class="rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <button @click="adicionarData" :disabled="!novaData"
                  class="border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-50 px-4 py-2 rounded-lg text-sm font-semibold transition">
            + Adicionar data
          </button>
        </div>
      </div>
    </template>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
  grupos:   { type: Array, default: () => [] },
  grupo_id: [Number, String],
  mes:      String,
  label:    String,
  prev:     String,
  next:     String,
  funcoes:  { type: Array, default: () => [] },
  membros:  { type: Array, default: () => [] },
  rows:     { type: Array, default: () => [] },
  ocupados: { type: Object, default: () => ({}) },
  editavel: { type: Boolean, default: false },
})

// Retorna o nome do outro grupo em que a pessoa já está escalada naquela data (ou null)
function conflito(row, memberId) {
  return props.ocupados?.[row.data]?.[Number(memberId)] ?? null
}

// Cópia editável das linhas (cells como objeto reativo)
const linhas = ref(clonar(props.rows))
watch(() => props.rows, (r) => { linhas.value = clonar(r) })

function clonar(rows) {
  return rows.map(r => ({ ...r, cells: { ...(r.cells || {}) }, restricoes: r.restricoes || '' }))
}

const salvando = ref(false)
const novaData = ref('')

// Paleta de cores estável por membro
const palette = [
  ['#fee2e2', '#991b1b'], ['#dbeafe', '#1e40af'], ['#dcfce7', '#166534'],
  ['#fef9c3', '#854d0e'], ['#f3e8ff', '#6b21a8'], ['#ffedd5', '#9a3412'],
  ['#cffafe', '#155e75'], ['#fce7f3', '#9d174d'], ['#e0e7ff', '#3730a3'],
  ['#d1fae5', '#065f46'],
]
function corMembro(id) {
  const idx = props.membros.findIndex(m => m.id === Number(id))
  if (idx < 0) return null
  return palette[idx % palette.length]
}
function cellStyle(id) {
  const c = id ? corMembro(id) : null
  if (!c) return { background: 'transparent', color: 'inherit' }
  return { background: c[0], color: c[1] }
}

function setCell(row, funcao, val) {
  if (val) row.cells[funcao] = Number(val)
  else delete row.cells[funcao]
}

// Impede a mesma pessoa em duas funções na mesma data
function membrosDisponiveis(row, funcao) {
  const usados = Object.entries(row.cells)
    .filter(([f, v]) => f !== funcao && v)
    .map(([, v]) => Number(v))
  return props.membros.filter(m => !usados.includes(m.id) || row.cells[funcao] === m.id)
}

function trocar(params) {
  router.get('/admin/planejador', {
    grupo_id: params.grupo_id ?? props.grupo_id,
    mes: params.mes ?? props.mes,
  }, { preserveState: false, preserveScroll: true })
}

function adicionarData() {
  if (!novaData.value) return
  const d = new Date(novaData.value + 'T12:00:00')
  linhas.value.push({
    escala_id: null,
    data: novaData.value,
    dia: d.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }),
    semana: d.toLocaleDateString('pt-BR', { weekday: 'short' }).replace('.', ''),
    culto_id: null, evento_id: null, vinculo: null,
    restricoes: '', cells: {},
  })
  linhas.value.sort((a, b) => a.data.localeCompare(b.data))
  novaData.value = ''
}

function salvar() {
  salvando.value = true
  router.post('/admin/planejador', {
    grupo_id: props.grupo_id,
    rows: linhas.value.map(r => ({
      escala_id: r.escala_id,
      data: r.data,
      culto_id: r.culto_id,
      evento_id: r.evento_id,
      vinculo: r.vinculo,
      restricoes: r.restricoes,
      cells: r.cells,
    })),
  }, {
    preserveScroll: true,
    onFinish: () => { salvando.value = false },
  })
}
</script>
