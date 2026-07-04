<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/escalas" class="hover:text-blue-600 dark:hover:text-blue-400">Escalas</Link>
        <span class="mx-1">›</span>
        {{ editando ? 'Editar' : 'Nova' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ editando ? 'Editar Escala' : 'Nova Escala' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl space-y-6">

      <!-- Título -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Título *</label>
        <input v-model="form.titulo" type="text" placeholder="Ex: Culto de Domingo - Louvor"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.titulo }" />
        <p v-if="form.errors.titulo" class="mt-1 text-xs text-red-500">{{ form.errors.titulo }}</p>
      </div>

      <!-- Descrição -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Descrição</label>
        <textarea v-model="form.descricao" rows="2" placeholder="Informações adicionais..."
                  class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                         bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                         placeholder-gray-400 dark:placeholder-slate-400
                         focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
      </div>

      <!-- Grupo -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Grupo *</label>
        <select v-model="form.grupo_id"
                class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                       bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-400': form.errors.grupo_id }">
          <option value="">Selecione o grupo...</option>
          <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nome }}</option>
        </select>
        <p v-if="form.errors.grupo_id" class="mt-1 text-xs text-red-500">{{ form.errors.grupo_id }}</p>
      </div>

      <!-- Vínculo (opcional) -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
          Vínculo <span class="text-gray-400 dark:text-slate-500 font-normal">(opcional)</span>
        </label>

        <div class="inline-flex rounded-lg border border-gray-200 dark:border-slate-600 overflow-hidden mb-2 text-sm">
          <button type="button" @click="setTipoVinculo('nenhum')"
                  :class="['px-4 py-2 transition', tipoVinculo === 'nenhum'
                    ? 'bg-blue-600 text-white'
                    : 'bg-white dark:bg-slate-700 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600']">
            Nenhum
          </button>
          <button type="button" @click="setTipoVinculo('culto')"
                  :class="['px-4 py-2 border-l border-gray-200 dark:border-slate-600 transition', tipoVinculo === 'culto'
                    ? 'bg-blue-600 text-white'
                    : 'bg-white dark:bg-slate-700 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600']">
            Culto
          </button>
          <button type="button" @click="setTipoVinculo('evento')"
                  :class="['px-4 py-2 border-l border-gray-200 dark:border-slate-600 transition', tipoVinculo === 'evento'
                    ? 'bg-blue-600 text-white'
                    : 'bg-white dark:bg-slate-700 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-600']">
            Evento
          </button>
        </div>

        <select v-if="tipoVinculo === 'culto'" v-model="form.culto_id"
                class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                       bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-400': form.errors.culto_id }">
          <option value="">Selecione o culto...</option>
          <option v-for="c in cultos" :key="c.id" :value="c.id">{{ c.label }}</option>
        </select>

        <select v-if="tipoVinculo === 'evento'" v-model="form.evento_id"
                class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                       bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-400': form.errors.evento_id }">
          <option value="">Selecione o evento...</option>
          <option v-for="e in eventos" :key="e.id" :value="e.id">{{ e.label }}</option>
        </select>

        <p v-if="form.errors.culto_id" class="mt-1 text-xs text-red-500">{{ form.errors.culto_id }}</p>
        <p v-if="form.errors.evento_id" class="mt-1 text-xs text-red-500">{{ form.errors.evento_id }}</p>
      </div>

      <!-- Data e Horários -->
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Data *</label>
          <input v-model="form.escala_data" type="date"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                        bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.data }" />
          <p v-if="form.errors.data" class="mt-1 text-xs text-red-500">{{ form.errors.data }}</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Início *</label>
          <input v-model="form.hora_inicio" type="time"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                        bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.hora_inicio }" />
          <p v-if="form.errors.hora_inicio" class="mt-1 text-xs text-red-500">{{ form.errors.hora_inicio }}</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Fim *</label>
          <input v-model="form.hora_fim" type="time"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                        bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.hora_fim }" />
          <p v-if="form.errors.hora_fim" class="mt-1 text-xs text-red-500">{{ form.errors.hora_fim }}</p>
        </div>
      </div>

      <!-- Status (apenas ao editar) -->
      <div v-if="editando">
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Status</label>
        <select v-model="form.status"
                class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                       bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="pendente">Pendente</option>
          <option value="confirmada">Confirmada</option>
          <option value="em_andamento">Em andamento</option>
          <option value="concluida">Concluída</option>
          <option value="cancelada">Cancelada</option>
        </select>
      </div>

      <!-- MEMBROS -->
      <div v-if="form.grupo_id" class="border border-gray-200 dark:border-slate-600 rounded-xl overflow-hidden">
        <div class="bg-gray-50 dark:bg-slate-700 px-5 py-3 border-b border-gray-200 dark:border-slate-600 flex items-center justify-between">
          <p class="text-sm font-semibold text-gray-700 dark:text-slate-300">Membros do Grupo</p>
          <p class="text-xs text-gray-400 dark:text-slate-500">{{ membrosDoGrupo.length }} disponíveis</p>
        </div>

        <div v-if="membrosDoGrupo.length" class="divide-y divide-gray-50 dark:divide-slate-700">
          <div v-for="membro in membrosDoGrupo" :key="membro.id"
               class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
            <input :id="`m-${membro.id}`" type="checkbox"
                   :checked="isEscalado(membro.id)"
                   @change="toggleMembro(membro.id)"
                   class="w-4 h-4 rounded border-gray-300 dark:border-slate-500 text-blue-600 focus:ring-blue-500 cursor-pointer" />
            <label :for="`m-${membro.id}`" class="flex-1 cursor-pointer">
              <span class="text-sm font-medium text-gray-800 dark:text-slate-200">{{ membro.name }}</span>
            </label>
            <template v-if="isEscalado(membro.id)">
              <input v-model="funcaoDoMembro(membro.id).funcao"
                     :list="`funcoes-grupo-${form.grupo_id}`"
                     type="text" placeholder="Função..." maxlength="100"
                     class="border border-gray-200 dark:border-slate-600 rounded px-3 py-1.5 text-xs w-40
                            bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                            placeholder-gray-400 dark:placeholder-slate-400
                            focus:outline-none focus:ring-1 focus:ring-blue-500" />
              <button type="button" @click="abrirNovaFuncao"
                      :title="`Adicionar nova função ao grupo`"
                      class="text-xs font-bold text-gray-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 w-7 h-7 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                +
              </button>
            </template>
          </div>
        </div>

        <!-- DATALIST global de funções do grupo atual -->
        <datalist v-if="form.grupo_id" :id="`funcoes-grupo-${form.grupo_id}`">
          <option v-for="f in funcoesDoGrupoAtual" :key="f.id" :value="f.nome" />
        </datalist>

        <div v-else class="px-5 py-6 text-sm text-gray-400 dark:text-slate-500 text-center">
          Nenhum membro associado a este grupo ainda.
        </div>
      </div>

      <!-- AÇÕES -->
      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ editando ? 'Salvar alterações' : 'Criar Escala' }}
        </button>
        <Link href="/admin/escalas"
              class="px-6 py-2.5 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
          Cancelar
        </Link>
      </div>

    </form>

    <!-- MODAL: nova função do grupo -->
    <div v-if="modalNovaFuncao" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Nova função do grupo</h3>
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">
          Fica salva no grupo e aparece como sugestão nas próximas escalas.
        </p>
        <input v-model="novaFuncaoNome" type="text" placeholder="Ex: Vocal, Guitarra, Bateria..." maxlength="100"
               @keyup.enter="salvarNovaFuncao"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm mb-2
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': novaFuncaoErro }" />
        <p v-if="novaFuncaoErro" class="text-xs text-red-500 mb-2">{{ novaFuncaoErro }}</p>
        <div class="flex gap-3 justify-end mt-4">
          <button type="button" @click="fecharNovaFuncao"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <button type="button" @click="salvarNovaFuncao"
                  :disabled="!novaFuncaoNome.trim() || salvandoNovaFuncao"
                  class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 rounded-lg transition">
            Adicionar
          </button>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, onMounted } from 'vue'

const props = defineProps({
  grupos:  Array,
  cultos:  { type: Array, default: () => [] },
  eventos: { type: Array, default: () => [] },
  escala:  Object,
})

const editando = computed(() => !!props.escala)

const form = useForm({
  titulo:       props.escala?.titulo      ?? '',
  descricao:    props.escala?.descricao   ?? '',
  escala_data:  props.escala?.data        ?? '',
  hora_inicio:  props.escala?.hora_inicio ?? '',
  hora_fim:     props.escala?.hora_fim    ?? '',
  status:       props.escala?.status      ?? 'pendente',
  grupo_id:     props.escala?.grupo_id    ?? '',
  culto_id:     props.escala?.culto_id    ?? '',
  evento_id:    props.escala?.evento_id   ?? '',
  membros:      props.escala?.membros     ?? [],
})

const tipoVinculo = ref(
  props.escala?.culto_id ? 'culto'
    : props.escala?.evento_id ? 'evento'
    : 'nenhum'
)

function setTipoVinculo(tipo) {
  tipoVinculo.value = tipo
  if (tipo !== 'culto')  form.culto_id  = ''
  if (tipo !== 'evento') form.evento_id = ''
}

const membrosLocal = reactive(
  (props.escala?.membros ?? []).map(m => ({ user_id: m.user_id, funcao: m.funcao ?? '' }))
)

// Auto-seleciona o grupo quando há apenas uma opção (ex: líder com um único grupo)
onMounted(() => {
  if (!editando.value && !form.grupo_id && props.grupos?.length === 1) {
    form.grupo_id = props.grupos[0].id
  }
})

const grupoAtual = computed(() =>
  props.grupos?.find(g => g.id === Number(form.grupo_id)) ?? null,
)

const membrosDoGrupo = computed(() => grupoAtual.value?.membros ?? [])

const funcoesDoGrupoAtual = computed(() => grupoAtual.value?.funcoes ?? [])

const modalNovaFuncao = ref(false)
const novaFuncaoNome = ref('')
const novaFuncaoErro = ref('')
const salvandoNovaFuncao = ref(false)

function abrirNovaFuncao() {
  if (!form.grupo_id) return
  novaFuncaoNome.value = ''
  novaFuncaoErro.value = ''
  modalNovaFuncao.value = true
}

function fecharNovaFuncao() {
  modalNovaFuncao.value = false
  novaFuncaoNome.value = ''
  novaFuncaoErro.value = ''
}

function salvarNovaFuncao() {
  const nome = novaFuncaoNome.value.trim()
  if (!nome || !form.grupo_id) return
  salvandoNovaFuncao.value = true
  novaFuncaoErro.value = ''
  router.post(`/admin/grupos/${form.grupo_id}/funcoes`,
    { nome },
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => { fecharNovaFuncao() },
      onError: (errors) => { novaFuncaoErro.value = errors.nome ?? 'Erro ao adicionar função.' },
      onFinish: () => { salvandoNovaFuncao.value = false },
    },
  )
}

function isEscalado(userId) {
  return membrosLocal.some(m => m.user_id === userId)
}

function funcaoDoMembro(userId) {
  return membrosLocal.find(m => m.user_id === userId) ?? {}
}

function toggleMembro(userId) {
  const idx = membrosLocal.findIndex(m => m.user_id === userId)
  if (idx >= 0) {
    membrosLocal.splice(idx, 1)
  } else {
    membrosLocal.push({ user_id: userId, funcao: '' })
  }
}

function submit() {
  form.membros = membrosLocal.map(m => ({ user_id: m.user_id, funcao: m.funcao || null }))
  form.transform(d => ({
    titulo:      d.titulo,
    descricao:   d.descricao,
    data:        d.escala_data,
    hora_inicio: d.hora_inicio,
    hora_fim:    d.hora_fim,
    status:      d.status,
    grupo_id:    d.grupo_id,
    culto_id:    d.culto_id  || null,
    evento_id:   d.evento_id || null,
    membros:     d.membros,
  }))
  if (editando.value) {
    form.put(`/admin/escalas/${props.escala.id}`)
  } else {
    form.post('/admin/escalas')
  }
}
</script>
