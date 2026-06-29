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
              <p class="text-sm font-semibold text-gray-700 dark:text-slate-300">Habilitar aba de músicas</p>
              <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
                Permite ao líder fazer upload de PDFs de músicas para os membros do grupo.
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
import { Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  grupo:    Object,
  usuarios: Array,
})

const editando = computed(() => !!props.grupo)

const form = useForm({
  nome:        props.grupo?.nome        ?? '',
  descricao:   props.grupo?.descricao   ?? '',
  tem_musicas: props.grupo?.tem_musicas ?? false,
  lider_id:    props.grupo?.lider?.id   ?? null,
  membros_ids: props.grupo?.membros_ids ?? [],
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
