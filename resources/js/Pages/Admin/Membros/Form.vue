<template>
  <AdminLayout>

    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/membros" class="hover:text-blue-600 dark:hover:text-blue-400">Membros</Link>
        <span class="mx-1">›</span>
        {{ readonly ? 'Visualizar' : editando ? 'Editar' : 'Novo' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ readonly ? membro.name : editando ? 'Editar Membro' : 'Novo Membro' }}
      </h1>
      <p v-if="!readonly" class="text-gray-500 dark:text-slate-400 text-sm mt-1">
        Cadastro pastoral, sem acesso ao painel. Para dar login, use a tela de Usuários.
      </p>
      <p v-else class="text-gray-500 dark:text-slate-400 text-sm mt-1">
        Somente visualização — apenas pastores podem editar.
      </p>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl">
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm space-y-6">
        <DadosPessoaisFields :form="form" :readonly="readonly" />

        <div class="pt-5 border-t border-gray-200 dark:border-slate-700 space-y-5">
          <h2 class="text-sm font-bold tracking-widest uppercase text-gray-500 dark:text-slate-400 mb-2">
            Dados pastorais
          </h2>

          <div>
            <label :class="labelClass">Família</label>
            <select v-model="form.familia_id" :class="inputClass" :disabled="readonly">
              <option :value="null">Sem família vinculada</option>
              <option v-for="f in familias" :key="f.id" :value="f.id">{{ f.nome }}</option>
            </select>
          </div>

          <div>
            <label :class="labelClass">Batizado nas águas?</label>
            <select v-model="form.batizado_aguas" :class="inputClass" :disabled="readonly">
              <option :value="null" disabled>Selecione...</option>
              <option :value="true">Sim</option>
              <option :value="false">Não</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex gap-3 pt-5">
        <template v-if="!readonly">
          <button type="submit" :disabled="form.processing"
                  class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
            {{ editando ? 'Salvar alterações' : 'Cadastrar Membro' }}
          </button>
        </template>
        <Link href="/admin/membros"
              class="px-6 py-2.5 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
          {{ readonly ? 'Voltar' : 'Cancelar' }}
        </Link>
      </div>
    </form>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DadosPessoaisFields from '@/Components/DadosPessoaisFields.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  membro:   Object,
  familias: { type: Array, default: () => [] },
  readonly: { type: Boolean, default: false },
})

const editando = computed(() => !!props.membro && !props.readonly)

const form = useForm({
  name:            props.membro?.name            ?? '',
  email:           props.membro?.email           ?? '',
  telefone:        props.membro?.telefone        ?? '',
  data_nascimento: props.membro?.data_nascimento ?? '',
  sexo:            props.membro?.sexo            ?? '',
  estado_civil:    props.membro?.estado_civil    ?? '',
  cpf:             props.membro?.cpf             ?? '',
  batizado_aguas:  props.membro?.batizado_aguas  ?? null,
  familia_id:      props.membro?.familia_id      ?? null,
})

const labelClass = 'block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5'
const inputClass = `w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                    bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                    focus:outline-none focus:ring-2 focus:ring-blue-500
                    disabled:opacity-60 disabled:cursor-not-allowed`

function submit() {
  if (editando.value) {
    form.put(`/admin/membros/${props.membro.id}`)
  } else {
    form.post('/admin/membros')
  }
}
</script>
