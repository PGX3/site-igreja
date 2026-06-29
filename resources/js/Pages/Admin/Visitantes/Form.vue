<template>
  <AdminLayout>

    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/visitantes" class="hover:text-blue-600 dark:hover:text-blue-400">Visitantes</Link>
        <span class="mx-1">›</span>
        {{ readonly ? 'Visualizar' : editando ? 'Editar' : 'Novo' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ readonly ? visitante.name : editando ? 'Editar Visitante' : 'Novo Visitante' }}
      </h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
        {{ readonly ? 'Somente visualização — apenas pastores podem editar.' : 'Cadastro rápido para acompanhamento pastoral.' }}
      </p>
    </div>

    <form @submit.prevent="submit" class="max-w-xl">
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm space-y-5">

        <div>
          <label :class="labelClass">Nome</label>
          <input v-model="form.name" type="text" :class="inputClass" :disabled="readonly" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label :class="labelClass">Telefone</label>
            <input v-model="form.telefone" type="text" :class="inputClass" :disabled="readonly" />
          </div>
          <div>
            <label :class="labelClass">Data de nascimento</label>
            <input v-model="form.data_nascimento" type="date" :class="inputClass" :disabled="readonly" />
          </div>
        </div>

        <div>
          <label :class="labelClass">E-mail</label>
          <input v-model="form.email" type="email" :class="inputClass" :disabled="readonly" />
        </div>

        <div>
          <label :class="labelClass">Como conheceu a igreja?</label>
          <input v-model="form.como_conheceu" type="text" :class="inputClass" :disabled="readonly" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label :class="labelClass">Convidado por</label>
            <select v-model="form.convidado_por_id" :class="inputClass" :disabled="readonly">
              <option :value="null">Ninguém / Não informado</option>
              <option v-for="m in convidadores" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
          </div>
          <div>
            <label :class="labelClass">Primeira visita</label>
            <input v-model="form.primeira_visita" type="date" :class="inputClass" :disabled="readonly" />
          </div>
        </div>

        <div>
          <label :class="labelClass">Observações pastorais</label>
          <textarea v-model="form.observacoes_pastorais" rows="4" :class="inputClass" :disabled="readonly"></textarea>
        </div>

        <div>
          <label :class="labelClass">Batizado nas águas?</label>
          <select v-model="form.batizado_aguas" :class="inputClass" :disabled="readonly">
            <option :value="null">Não informado</option>
            <option :value="true">Sim</option>
            <option :value="false">Não</option>
          </select>
        </div>

        <div>
          <label :class="labelClass">Família</label>
          <select v-model="form.familia_id" :class="inputClass" :disabled="readonly">
            <option :value="null">Sem família vinculada</option>
            <option v-for="f in familias" :key="f.id" :value="f.id">{{ f.nome }}</option>
          </select>
        </div>
      </div>

      <div class="flex gap-3 pt-5">
        <template v-if="!readonly">
          <button type="submit" :disabled="form.processing"
                  class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
            {{ editando ? 'Salvar alterações' : 'Cadastrar Visitante' }}
          </button>
        </template>
        <Link href="/admin/visitantes"
              class="px-6 py-2.5 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
          {{ readonly ? 'Voltar' : 'Cancelar' }}
        </Link>
      </div>
    </form>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  visitante:    Object,
  convidadores: { type: Array, default: () => [] },
  familias:     { type: Array, default: () => [] },
  readonly:     { type: Boolean, default: false },
})

const editando = computed(() => !!props.visitante && !props.readonly)

const form = useForm({
  name:                  props.visitante?.name                  ?? '',
  email:                 props.visitante?.email                 ?? '',
  telefone:              props.visitante?.telefone              ?? '',
  data_nascimento:       props.visitante?.data_nascimento       ?? '',
  como_conheceu:         props.visitante?.como_conheceu         ?? '',
  convidado_por_id:      props.visitante?.convidado_por_id      ?? null,
  primeira_visita:       props.visitante?.primeira_visita       ?? '',
  observacoes_pastorais: props.visitante?.observacoes_pastorais ?? '',
  batizado_aguas:        props.visitante?.batizado_aguas        ?? null,
  familia_id:            props.visitante?.familia_id            ?? null,
})

const labelClass = 'block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5'
const inputClass = `w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                    bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                    placeholder-gray-400 dark:placeholder-slate-400
                    focus:outline-none focus:ring-2 focus:ring-blue-500
                    disabled:opacity-60 disabled:cursor-not-allowed`

function submit() {
  if (editando.value) {
    form.put(`/admin/visitantes/${props.visitante.id}`)
  } else {
    form.post('/admin/visitantes')
  }
}
</script>
