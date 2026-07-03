<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/assets" class="hover:text-blue-600 dark:hover:text-blue-400">Anexos</Link>
        <span class="mx-1">›</span>
        {{ editando ? 'Editar' : 'Novo' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ editando ? 'Editar Anexo' : 'Novo Anexo' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="max-w-2xl space-y-6">

      <!-- Tipo -->
      <div class="flex flex-wrap items-center gap-2">
        <button v-for="t in tipos" :key="t.value" type="button" @click="form.tipo = t.value"
                class="px-4 py-2 rounded-full text-sm font-semibold border transition"
                :class="form.tipo === t.value ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'border-gray-200 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700'">
          {{ t.label }}
        </button>
      </div>

      <!-- Título -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Título / descrição</label>
        <input v-model="form.titulo" type="text" placeholder="Ex: Partitura Oceanos, Foto do palco..."
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <!-- Arquivo -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
          {{ editando ? 'Substituir arquivo (opcional)' : 'Arquivo *' }}
        </label>
        <label class="block w-full border-2 border-dashed border-gray-200 dark:border-slate-600 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition"
               :class="{ 'border-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20': arquivoNome, 'border-red-400': form.errors.arquivo }">
          <input type="file" class="hidden" :accept="form.tipo === 'imagem' ? 'image/*' : '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx'" @change="onArquivoChange" />
          <p class="text-sm text-gray-500 dark:text-slate-400">
            <span v-if="arquivoNome" class="text-blue-600 dark:text-blue-400 font-semibold">{{ arquivoNome }}</span>
            <template v-else-if="editando && asset?.arquivo_nome">Atual: {{ asset.arquivo_nome }} · clique para trocar</template>
            <template v-else>{{ form.tipo === 'imagem' ? 'Selecionar imagem (jpg, png, webp)' : 'Selecionar arquivo (PDF, docs, planilhas...)' }}</template>
          </p>
        </label>
        <p v-if="form.errors.arquivo" class="mt-1 text-xs text-red-500">{{ form.errors.arquivo }}</p>
      </div>

      <!-- AÇÕES -->
      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ editando ? 'Salvar alterações' : 'Adicionar Anexo' }}
        </button>
        <Link href="/admin/assets"
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
import { computed, ref } from 'vue'

const props = defineProps({ asset: Object })

const editando = computed(() => !!props.asset)
const arquivoNome = ref('')

const tipos = [
  { value: 'arquivo', label: 'Arquivo' },
  { value: 'imagem',  label: 'Imagem' },
]

const form = useForm({
  tipo:    props.asset?.tipo ?? 'arquivo',
  titulo:  props.asset?.titulo ?? '',
  arquivo: null,
})

function onArquivoChange(e) {
  const file = e.target.files[0]
  form.arquivo = file || null
  arquivoNome.value = file ? file.name : ''
}

function submit() {
  if (editando.value) {
    form.transform(d => ({ ...d, _method: 'put' }))
      .post(`/admin/assets/${props.asset.id}`, { forceFormData: true })
  } else {
    form.post('/admin/assets', { forceFormData: true })
  }
}
</script>
