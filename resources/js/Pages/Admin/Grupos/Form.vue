<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/grupos" class="hover:text-blue-600 dark:hover:text-blue-400">Grupos</Link>
        <span class="mx-1">›</span>
        {{ editando ? 'Editar' : 'Novo' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ editando ? 'Editar Grupo' : 'Novo Grupo' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-5xl">

      <!-- COLUNA ESQUERDA: dados básicos -->
      <div class="space-y-6">

        <!-- Nome -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Nome do Grupo *</label>
          <input v-model="form.nome" type="text" placeholder="Ex: Louvor, Recepção, Som..."
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                        bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                        placeholder-gray-400 dark:placeholder-slate-400
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': errors.nome }" />
          <p v-if="errors.nome" class="mt-1 text-xs text-red-500">{{ errors.nome }}</p>
        </div>

        <!-- Descrição -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Descrição</label>
          <textarea v-model="form.descricao" rows="3" placeholder="Descreva o propósito do grupo..."
                    class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                           bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                           placeholder-gray-400 dark:placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
        </div>

        <!-- Músicas -->
        <div>
          <label class="flex items-start gap-3 cursor-pointer select-none">
            <input v-model="form.tem_musicas" type="checkbox"
                   class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500" />
            <div>
              <p class="text-sm font-semibold text-gray-700 dark:text-slate-300">Grupo de louvor (habilita setlist)</p>
              <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
                Permite montar o setlist de músicas (com tom) nas escalas deste grupo.
              </p>
            </div>
          </label>
        </div>

        <!-- Líder -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Líder do Grupo</label>
          <select v-model="form.lider_id"
                  class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                         bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                         focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option :value="null">Sem líder</option>
            <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
          </select>
          <p class="mt-1.5 text-xs text-gray-400 dark:text-slate-500">
            O usuário selecionado terá seu papel alterado para "Líder" e entrará no grupo automaticamente.
          </p>
        </div>

        <!-- WhatsApp do grupo (CallMeBot) -->
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-4">
          <h2 class="text-sm font-bold text-gray-900 dark:text-white">WhatsApp do grupo</h2>
          <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 mb-3">
            Para disparar escalas no grupo do WhatsApp via CallMeBot. Adicione o número do CallMeBot ao grupo,
            faça a autorização e cole aqui a apikey recebida.
          </p>
          <div class="space-y-3">
            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">API Key (CallMeBot)</label>
              <input v-model="form.whatsapp_apikey" type="text" placeholder="Ex: 123456"
                     class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1">Número/destino do grupo <span class="font-normal text-gray-400">(opcional)</span></label>
              <input v-model="form.whatsapp_phone" type="text" placeholder="Só se o CallMeBot pedir um número"
                     class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
              <p class="mt-1 text-[11px] text-gray-400 dark:text-slate-500">Se o CallMeBot te deu só a API Key, pode deixar em branco.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- COLUNA DIREITA: membros -->
      <div>
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">

          <!-- Cabeçalho do bloco -->
          <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
            <div>
              <h2 class="text-sm font-bold text-gray-900 dark:text-white">Membros do Grupo</h2>
              <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
                {{ totalSelecionados }} selecionado(s) de {{ usuarios.length }}
              </p>
            </div>
            <button type="button" @click="limparSelecao"
                    :disabled="!totalSelecionados"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600
                           disabled:opacity-40 disabled:cursor-not-allowed
                           px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
              Limpar
            </button>
          </div>

          <!-- Busca -->
          <div class="px-5 py-3 border-b border-gray-100 dark:border-slate-700">
            <input v-model="busca" type="search" placeholder="Buscar por nome ou email..."
                   class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm
                          bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                          placeholder-gray-400 dark:placeholder-slate-400
                          focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <!-- Lista -->
          <div class="max-h-96 overflow-y-auto">
            <ul v-if="usuariosFiltrados.length" class="divide-y divide-gray-50 dark:divide-slate-700/50">
              <li v-for="u in usuariosFiltrados" :key="u.id">
                <label class="flex items-center gap-3 px-5 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                  <input type="checkbox"
                         :checked="isSelecionado(u.id)"
                         :disabled="u.id === form.lider_id"
                         @change="toggle(u.id)"
                         class="h-4 w-4 rounded border-gray-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ u.name }}</p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ u.email }}</p>
                  </div>
                  <span v-if="u.id === form.lider_id"
                        class="text-[10px] font-bold tracking-wider uppercase text-blue-600 dark:text-blue-400
                               bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-full">
                    Líder
                  </span>
                </label>
              </li>
            </ul>
            <div v-else class="py-10 text-center text-sm text-gray-400 dark:text-slate-500">
              Nenhum usuário encontrado.
            </div>
          </div>
        </div>
        <p class="mt-1.5 text-xs text-gray-400 dark:text-slate-500">
          O líder fica selecionado obrigatoriamente. Para trocá-lo, use o campo "Líder".
        </p>

        <!-- FUNÇÕES DO GRUPO (só ao editar) -->
        <div v-if="editando" class="mt-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">Funções do Grupo</h2>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
              Lista de funções sugeridas ao montar escalas (ex: Vocal, Guitarra, Bateria).
            </p>
          </div>

          <ul v-if="funcoes.length" class="divide-y divide-gray-50 dark:divide-slate-700/50">
            <li v-for="f in funcoes" :key="f.id" class="flex items-center justify-between px-5 py-2.5">
              <span class="text-sm text-gray-800 dark:text-slate-200">{{ f.nome }}</span>
              <button type="button" @click="removerFuncao(f)"
                      class="text-xs font-semibold text-gray-400 dark:text-slate-500 hover:text-red-600 px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                Remover
              </button>
            </li>
          </ul>
          <p v-else class="px-5 py-4 text-xs text-gray-400 dark:text-slate-500">
            Nenhuma função cadastrada ainda.
          </p>

          <form @submit.prevent="adicionarFuncao"
                class="px-5 py-3 border-t border-gray-100 dark:border-slate-700 flex gap-2">
            <input v-model="novaFuncao" type="text" placeholder="Nova função..." maxlength="100"
                   class="flex-1 border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm
                          bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                          placeholder-gray-400 dark:placeholder-slate-400
                          focus:outline-none focus:ring-2 focus:ring-blue-500"
                   :class="{ 'border-red-400': funcaoErro }" />
            <button type="submit" :disabled="!novaFuncao.trim() || salvandoFuncao"
                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
              Adicionar
            </button>
          </form>
          <p v-if="funcaoErro" class="px-5 pb-3 text-xs text-red-500">{{ funcaoErro }}</p>
        </div>
      </div>

      <!-- AÇÕES -->
      <div class="lg:col-span-2 flex gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ editando ? 'Salvar alterações' : 'Criar Grupo' }}
        </button>
        <Link href="/admin/grupos"
              class="px-6 py-2.5 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
          Cancelar
        </Link>
      </div>
    </form>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  grupo:    Object,
  usuarios: Array,
})

const editando = computed(() => !!props.grupo)

const funcoes = ref([...(props.grupo?.funcoes ?? [])])
const novaFuncao = ref('')
const funcaoErro = ref('')
const salvandoFuncao = ref(false)

watch(() => props.grupo?.funcoes, (novas) => {
  if (novas) funcoes.value = [...novas]
})

function adicionarFuncao() {
  const nome = novaFuncao.value.trim()
  if (!nome || !props.grupo) return
  salvandoFuncao.value = true
  funcaoErro.value = ''
  router.post(`/admin/grupos/${props.grupo.id}/funcoes`,
    { nome },
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => { novaFuncao.value = '' },
      onError: (errors) => { funcaoErro.value = errors.nome ?? 'Erro ao adicionar função.' },
      onFinish: () => { salvandoFuncao.value = false },
    },
  )
}

function removerFuncao(f) {
  if (!props.grupo) return
  router.delete(`/admin/grupos/${props.grupo.id}/funcoes/${f.id}`, {
    preserveScroll: true,
    preserveState: true,
  })
}

const form = useForm({
  nome:            props.grupo?.nome            ?? '',
  descricao:       props.grupo?.descricao       ?? '',
  tem_musicas:     props.grupo?.tem_musicas     ?? false,
  whatsapp_apikey: props.grupo?.whatsapp_apikey ?? '',
  whatsapp_phone:  props.grupo?.whatsapp_phone  ?? '',
  lider_id:        props.grupo?.lider?.id       ?? null,
  membros_ids:     props.grupo?.membros_ids     ?? [],
})

const errors = computed(() => form.errors)

const busca = ref('')
const usuariosFiltrados = computed(() => {
  const termo = busca.value.trim().toLowerCase()
  if (!termo) return props.usuarios
  return props.usuarios.filter(u =>
    u.name.toLowerCase().includes(termo) || u.email.toLowerCase().includes(termo)
  )
})

const totalSelecionados = computed(() => form.membros_ids.length)

function isSelecionado(id) {
  return form.membros_ids.includes(id)
}

function toggle(id) {
  const idx = form.membros_ids.indexOf(id)
  if (idx >= 0) form.membros_ids.splice(idx, 1)
  else form.membros_ids.push(id)
}

function limparSelecao() {
  form.membros_ids = form.lider_id ? [form.lider_id] : []
}

watch(() => form.lider_id, (novo, antigo) => {
  if (novo && !form.membros_ids.includes(novo)) {
    form.membros_ids.push(novo)
  }
})

function submit() {
  if (editando.value) {
    form.put(`/admin/grupos/${props.grupo.id}`)
  } else {
    form.post('/admin/grupos')
  }
}
</script>
