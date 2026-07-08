<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <Link href="/admin/cursos"
            class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
        ← Voltar
      </Link>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">
        {{ curso ? 'Editar Curso' : 'Novo Curso' }}
      </h1>
    </div>

    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8 max-w-2xl">
      <form @submit.prevent="submit" class="flex flex-col gap-6">

        <!-- TÍTULO -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Título
          </label>
          <input v-model="form.titulo" type="text" placeholder="Ex: Discipulado I"
                 class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm
                        bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
          <p v-if="form.errors.titulo" class="text-red-500 text-xs mt-1">{{ form.errors.titulo }}</p>
        </div>

        <!-- CAPA -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Capa (opcional)
          </label>
          <div class="flex items-center gap-4">
            <div class="w-24 h-24 rounded-lg bg-gray-100 dark:bg-slate-700 overflow-hidden flex-shrink-0 flex items-center justify-center">
              <img v-if="capaPreview" :src="capaPreview" class="w-full h-full object-cover" />
              <span v-else class="text-2xl">📖</span>
            </div>
            <input type="file" accept="image/jpeg,image/png,image/webp" @change="onCapa"
                   class="text-sm text-gray-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg
                          file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                          dark:file:bg-blue-900/30 dark:file:text-blue-300 hover:file:bg-blue-100 cursor-pointer" />
          </div>
          <p v-if="form.errors.capa" class="text-red-500 text-xs mt-1">{{ form.errors.capa }}</p>
        </div>

        <!-- DESCRIÇÃO -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Descrição (opcional)
          </label>
          <RichTextEditor v-model="form.descricao" permite-blocos />
        </div>

        <!-- ATIVO -->
        <div class="flex items-center gap-3">
          <input v-model="form.ativo" type="checkbox" id="ativo"
                 class="w-4 h-4 text-blue-600 border-gray-300 dark:border-slate-500 rounded focus:ring-blue-500" />
          <label for="ativo" class="text-sm text-gray-600 dark:text-slate-300">
            Curso ativo (visível para membros e pelo link)
          </label>
        </div>

        <!-- BOTÕES -->
        <div class="flex items-center gap-3 pt-2">
          <button type="submit" :disabled="form.processing"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
            {{ form.processing ? 'Salvando...' : (curso ? 'Salvar Alterações' : 'Criar e adicionar conteúdo') }}
          </button>
          <Link href="/admin/cursos"
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
import RichTextEditor from '@/Components/RichTextEditor.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ curso: Object })

const form = useForm({
  titulo: props.curso?.titulo ?? '',
  descricao: props.curso?.descricao ?? '',
  ativo: props.curso?.ativo ?? true,
  capa: null,
})

const capaPreview = ref(props.curso?.capa_url ?? null)

function onCapa(e) {
  const file = e.target.files?.[0]
  form.capa = file ?? null
  capaPreview.value = file ? URL.createObjectURL(file) : (props.curso?.capa_url ?? null)
}

function submit() {
  if (props.curso) {
    form.transform((d) => ({ ...d, _method: 'put' })).post(`/admin/cursos/${props.curso.id}`)
  } else {
    form.post('/admin/cursos')
  }
}
</script>
