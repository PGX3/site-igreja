<template>
  <AdminLayout>

    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/visitantes" class="hover:text-blue-600 dark:hover:text-blue-400">Visitantes</Link>
        <span class="mx-1">›</span>
        {{ editando ? 'Editar' : 'Novo' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ editando ? 'Editar Visitante' : 'Novo Visitante' }}
      </h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
        Cadastro rápido para acompanhamento pastoral. Mais dados podem ser preenchidos depois.
      </p>
    </div>

    <form @submit.prevent="submit" class="max-w-xl">
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm space-y-5">

        <div>
          <label :class="labelClass">Nome *</label>
          <input v-model="form.name" type="text" placeholder="Nome completo"
                 :class="[inputClass, form.errors.name && 'border-red-400']" />
          <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label :class="labelClass">Telefone *</label>
            <input v-model="form.telefone" type="text" placeholder="(51) 99999-9999"
                   :class="[inputClass, form.errors.telefone && 'border-red-400']" />
            <p v-if="form.errors.telefone" class="mt-1 text-xs text-red-500">{{ form.errors.telefone }}</p>
          </div>
          <div>
            <label :class="labelClass">Data de nascimento *</label>
            <input v-model="form.data_nascimento" type="date"
                   :class="[inputClass, form.errors.data_nascimento && 'border-red-400']" />
            <p v-if="form.errors.data_nascimento" class="mt-1 text-xs text-red-500">{{ form.errors.data_nascimento }}</p>
          </div>
        </div>

        <div>
          <label :class="labelClass">E-mail</label>
          <input v-model="form.email" type="email" placeholder="voce@exemplo.com"
                 :class="[inputClass, form.errors.email && 'border-red-400']" />
          <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
        </div>

        <div>
          <label :class="labelClass">Como conheceu a igreja?</label>
          <input v-model="form.como_conheceu" type="text" placeholder="Ex: amigo, redes sociais, evento..."
                 :class="inputClass" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label :class="labelClass">Convidado por</label>
            <select v-model="form.convidado_por_id" :class="inputClass">
              <option :value="null">Ninguém / Não informado</option>
              <option v-for="m in convidadores" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
          </div>

          <div>
            <label :class="labelClass">Primeira visita</label>
            <input v-model="form.primeira_visita" type="date"
                   :class="[inputClass, form.errors.primeira_visita && 'border-red-400']" />
            <p v-if="form.errors.primeira_visita" class="mt-1 text-xs text-red-500">{{ form.errors.primeira_visita }}</p>
          </div>
        </div>

        <div>
          <label :class="labelClass">Observações pastorais</label>
          <textarea v-model="form.observacoes_pastorais" rows="4"
                    placeholder="Anotações para acompanhamento (visível apenas internamente)..."
                    :class="inputClass"></textarea>
        </div>

        <div>
          <label :class="labelClass">Batizado nas águas?</label>
          <select v-model="form.batizado_aguas" :class="inputClass">
            <option :value="null">Não informado</option>
            <option :value="true">Sim</option>
            <option :value="false">Não</option>
          </select>
        </div>

        <div>
          <label :class="labelClass">Família</label>
          <select v-model="form.familia_id" :class="inputClass">
            <option :value="null">Sem família vinculada</option>
            <option v-for="f in familias" :key="f.id" :value="f.id">{{ f.nome }}</option>
          </select>
          <p class="mt-1 text-xs text-gray-400 dark:text-slate-500">
            Para criar uma nova família, use a tela
            <Link href="/admin/familias/create" class="text-blue-600 dark:text-blue-400 hover:underline">Nova Família</Link>.
          </p>
        </div>
      </div>

      <div class="flex gap-3 pt-5">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ editando ? 'Salvar alterações' : 'Cadastrar Visitante' }}
        </button>
        <Link href="/admin/visitantes"
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
import { computed } from 'vue'

const props = defineProps({
  visitante: Object,
  convidadores: { type: Array, default: () => [] },
  familias: { type: Array, default: () => [] },
})

const editando = computed(() => !!props.visitante)

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
                    focus:outline-none focus:ring-2 focus:ring-blue-500`

function submit() {
  if (editando.value) {
    form.put(`/admin/visitantes/${props.visitante.id}`)
  } else {
    form.post('/admin/visitantes')
  }
}
</script>
