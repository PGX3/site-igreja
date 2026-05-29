<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <Link href="/admin/cultos"
            class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
        ← Voltar
      </Link>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">
        {{ culto ? 'Editar Culto' : 'Novo Culto' }}
      </h1>
    </div>

    <!-- FORM CARD -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-8 max-w-xl">

      <form @submit.prevent="submit" class="flex flex-col gap-6">

        <!-- NOME -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Nome
          </label>
          <input v-model="form.nome" type="text"
                 class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm
                        bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
          <p v-if="form.errors.nome" class="text-red-500 text-xs mt-1">
            {{ form.errors.nome }}
          </p>
        </div>

        <!-- DIA -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Dia da Semana
          </label>
          <select v-model="form.dia_semana"
                  class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm
                         bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                         focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            <option v-for="d in dias" :key="d" :value="d">
              {{ d }}
            </option>
          </select>
        </div>

        <!-- HORÁRIO -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Horário
          </label>
          <input v-model="form.horario" type="text" placeholder="Ex: 19h30"
                 class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm
                        bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                        placeholder-gray-400 dark:placeholder-slate-400
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
        </div>

        <!-- DESCRIÇÃO -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Descrição (opcional)
          </label>
          <textarea v-model="form.descricao" rows="3"
                    class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm
                           bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition resize-none" />
        </div>

        <!-- ATIVO -->
        <div class="flex items-center gap-3">
          <input v-model="form.ativo" type="checkbox" id="ativo"
                 class="w-4 h-4 text-blue-600 border-gray-300 dark:border-slate-500 rounded focus:ring-blue-500" />
          <label for="ativo" class="text-sm text-gray-600 dark:text-slate-300">
            Exibir no site
          </label>
        </div>

        <!-- BOTÕES -->
        <div class="flex items-center gap-3 pt-4">
          <button type="submit"
                  :disabled="form.processing"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
            {{ form.processing ? 'Salvando...' : (culto ? 'Salvar Alterações' : 'Criar Culto') }}
          </button>
          <Link href="/admin/cultos"
                class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
            Cancelar
          </Link>
        </div>

      </form>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ culto: Object })

const dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado']

const form = useForm({
  nome:       props.culto?.nome       ?? '',
  dia_semana: props.culto?.dia_semana ?? 'Domingo',
  horario:    props.culto?.horario    ?? '',
  descricao:  props.culto?.descricao  ?? '',
  ativo:      props.culto?.ativo      ?? false,
})

function submit() {
  if (props.culto) {
    form.put(`/admin/cultos/${props.culto.id}`)
  } else {
    form.post('/admin/cultos')
  }
}
</script>
