<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <Link href="/admin/documentos"
            class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
        ← Voltar
      </Link>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">
        {{ documento ? 'Editar Documento' : 'Novo Documento' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6 max-w-3xl">

      <!-- DADOS + MODELO -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8 flex flex-col gap-6">
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Título (uso interno)
          </label>
          <input v-model="form.titulo" type="text" placeholder="Ex: Convite Seminário XYZ - Férias 2026" :class="inputClass" />
          <p v-if="form.errors.titulo" class="text-red-500 text-xs mt-1">{{ form.errors.titulo }}</p>
        </div>

        <div v-if="!documento">
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Modelo (opcional)
          </label>
          <select v-model="form.documento_template_id" @change="aoTrocarTemplate" :class="inputClass">
            <option :value="null">Documento em branco</option>
            <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.nome }}</option>
          </select>
        </div>
      </div>

      <!-- VARIÁVEIS DO MODELO -->
      <div v-if="templateAtual && templateAtual.variaveis.length"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8">
        <div class="flex items-center justify-between mb-4 gap-4">
          <div>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Preencher variáveis</h2>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
              Preencha e clique em gerar para montar o texto a partir do modelo.
            </p>
          </div>
          <button type="button" @click="gerarTexto"
                  class="flex-shrink-0 bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900 px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-90 transition">
            Gerar texto ↓
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="v in templateAtual.variaveis" :key="v.chave"
               :class="v.tipo === 'multilinha' ? 'sm:col-span-2' : ''">
            <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
              {{ v.label }}
            </label>
            <textarea v-if="v.tipo === 'multilinha'" v-model="form.valores[v.chave]" rows="3"
                      :class="inputClass + ' resize-none'" />
            <input v-else v-model="form.valores[v.chave]" :type="v.tipo === 'data' ? 'date' : 'text'"
                   :class="inputClass" />
          </div>
        </div>
      </div>

      <!-- CORPO -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8">
        <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
          Texto do documento
        </label>
        <RichTextEditor v-model="form.corpo" :permite-blocos="true" :permite-imagem="true"
                        upload-url="/admin/editor/imagem" />
        <p v-if="form.errors.corpo" class="text-red-500 text-xs mt-1">{{ form.errors.corpo }}</p>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-2">
          O cabeçalho e o rodapé (logo, endereço, CNPJ, contato) são adicionados automaticamente ao imprimir.
        </p>
      </div>

      <!-- BOTÕES -->
      <div class="flex items-center gap-3">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
          {{ form.processing ? 'Salvando...' : (documento ? 'Salvar Alterações' : 'Criar Documento') }}
        </button>
        <Link href="/admin/documentos"
              class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
          Cancelar
        </Link>
      </div>

    </form>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Components/RichTextEditor.vue'
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  documento: Object,
  templates: { type: Array, default: () => [] },
})

const inputClass =
  'w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm ' +
  'bg-white dark:bg-slate-700 text-gray-900 dark:text-white ' +
  'placeholder-gray-400 dark:placeholder-slate-400 ' +
  'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition'

const form = useForm({
  titulo:                props.documento?.titulo                ?? '',
  corpo:                 props.documento?.corpo                 ?? '',
  documento_template_id: props.documento?.documento_template_id ?? null,
  valores:               props.documento?.valores               ?? {},
})

const templateAtual = computed(() =>
  props.templates.find((t) => t.id === form.documento_template_id) ?? null,
)

function aoTrocarTemplate() {
  const t = templateAtual.value
  const valores = {}
  if (t) {
    t.variaveis.forEach((v) => { valores[v.chave] = form.valores[v.chave] ?? '' })
    if (!form.titulo) form.titulo = t.nome
  }
  form.valores = valores
}

function formatarValor(v) {
  const valor = form.valores[v.chave] ?? ''
  if (v.tipo === 'data' && valor) {
    const d = new Date(valor)
    if (!isNaN(d)) {
      return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric', timeZone: 'UTC' })
    }
  }
  return valor
}

function gerarTexto() {
  const t = templateAtual.value
  if (!t) return
  let corpo = t.corpo || ''
  t.variaveis.forEach((v) => {
    const re = new RegExp('\\{\\{\\s*' + v.chave.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*\\}\\}', 'g')
    corpo = corpo.replace(re, formatarValor(v) || `{{${v.chave}}}`)
  })
  form.corpo = corpo
}

function submit() {
  if (props.documento) {
    form.put(`/admin/documentos/${props.documento.id}`)
  } else {
    form.post('/admin/documentos')
  }
}
</script>
