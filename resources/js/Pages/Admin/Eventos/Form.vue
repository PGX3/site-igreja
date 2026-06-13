<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <Link href="/admin/eventos"
            class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
        ← Voltar
      </Link>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">
        {{ evento ? 'Editar Evento' : 'Novo Evento' }}
      </h1>
    </div>

    <!-- FORM CARD -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8 max-w-xl">

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

        <!-- DATA + HORÁRIO -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
              Data
            </label>
            <input v-model="form.data_evento" type="date"
                   class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm
                          bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
            <p v-if="form.errors.data_evento" class="text-red-500 text-xs mt-1">
              {{ form.errors.data_evento }}
            </p>
          </div>
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
        </div>

        <!-- LOCAL -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Local (opcional)
          </label>
          <input v-model="form.local" type="text" placeholder="Ex: Templo principal"
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
          <textarea v-model="form.descricao" rows="4"
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
            {{ form.processing ? 'Salvando...' : (evento ? 'Salvar Alterações' : 'Criar Evento') }}
          </button>
          <Link href="/admin/eventos"
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

const props = defineProps({ evento: Object })

const form = useForm({
  nome:        props.evento?.nome        ?? '',
  data_evento: props.evento?.data_evento ?? '',
  horario:     props.evento?.horario     ?? '',
  local:       props.evento?.local       ?? '',
  descricao:   props.evento?.descricao   ?? '',
  ativo:       props.evento?.ativo       ?? true,
})

function submit() {
  if (props.evento) {
    form.put(`/admin/eventos/${props.evento.id}`)
  } else {
    form.post('/admin/eventos')
  }
}
</script>
