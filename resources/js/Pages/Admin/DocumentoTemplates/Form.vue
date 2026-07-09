<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <Link href="/admin/documento-templates"
            class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
        ← Voltar
      </Link>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">
        {{ template ? 'Editar Modelo' : 'Novo Modelo' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6 max-w-3xl">

      <!-- DADOS -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8 flex flex-col gap-6">
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Nome do modelo
          </label>
          <input v-model="form.nome" type="text" placeholder="Ex: Carta-convite seminário" :class="inputClass" />
          <p v-if="form.errors.nome" class="text-red-500 text-xs mt-1">{{ form.errors.nome }}</p>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Descrição (opcional)
          </label>
          <input v-model="form.descricao" type="text" :class="inputClass" />
        </div>

        <div class="flex items-center gap-3">
          <input v-model="form.ativo" type="checkbox" id="ativo"
                 class="w-4 h-4 text-blue-600 border-gray-300 dark:border-slate-500 rounded focus:ring-blue-500" />
          <label for="ativo" class="text-sm text-gray-600 dark:text-slate-300">
            Ativo (disponível ao criar documentos)
          </label>
        </div>
      </div>

      <!-- VARIÁVEIS -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Variáveis</h2>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
              Use no texto como <code>&#123;&#123;chave&#125;&#125;</code>. Ao criar um documento, cada variável vira um campo.
            </p>
          </div>
          <button type="button" @click="addVariavel"
                  class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline flex-shrink-0">
            + Variável
          </button>
        </div>

        <div v-if="form.variaveis.length === 0" class="text-sm text-gray-400 dark:text-slate-500 py-2">
          Nenhuma variável. O modelo será um texto fixo.
        </div>

        <div v-for="(v, i) in form.variaveis" :key="i"
             class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_140px_auto] gap-2 items-start mb-3">
          <input v-model="v.chave" type="text" placeholder="chave (ex: destinatario)"
                 @input="v.chave = normalizar(v.chave)" :class="inputClass" />
          <input v-model="v.label" type="text" placeholder="Rótulo (ex: Destinatário)" :class="inputClass" />
          <select v-model="v.tipo" :class="inputClass">
            <option value="texto">Texto</option>
            <option value="multilinha">Texto longo</option>
            <option value="data">Data</option>
          </select>
          <button type="button" @click="removeVariavel(i)"
                  class="text-gray-400 hover:text-red-500 px-3 py-3 transition" title="Remover">
            ✕
          </button>
        </div>
        <p v-if="form.errors.variaveis" class="text-red-500 text-xs mt-1">{{ form.errors.variaveis }}</p>
      </div>

      <!-- CORPO -->
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8">
        <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
          Corpo do documento
        </label>
        <div v-if="form.variaveis.length" class="flex flex-wrap gap-1.5 mb-3">
          <button v-for="v in form.variaveis" :key="v.chave" type="button" @click="copiar(v.chave)"
                  class="text-xs font-mono px-2 py-1 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                  :title="'Copiar ' + marcador(v.chave)">
            {{ marcador(v.chave) }}
          </button>
        </div>
        <RichTextEditor v-model="form.corpo" :permite-blocos="true" :permite-imagem="true"
                        upload-url="/admin/editor/imagem" />
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-2">
          Escreva os marcadores manualmente (ex.: <code>&#123;&#123;destinatario&#125;&#125;</code>) onde o dado deve entrar.
        </p>
      </div>

      <!-- BOTÕES -->
      <div class="flex items-center gap-3">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
          {{ form.processing ? 'Salvando...' : (template ? 'Salvar Alterações' : 'Criar Modelo') }}
        </button>
        <Link href="/admin/documento-templates"
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
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ template: Object })

const inputClass =
  'w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm ' +
  'bg-white dark:bg-slate-700 text-gray-900 dark:text-white ' +
  'placeholder-gray-400 dark:placeholder-slate-400 ' +
  'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition'

const form = useForm({
  nome:      props.template?.nome      ?? '',
  descricao: props.template?.descricao ?? '',
  corpo:     props.template?.corpo     ?? '',
  variaveis: (props.template?.variaveis ?? []).map((v) => ({ ...v })),
  ativo:     props.template?.ativo     ?? true,
})

function normalizar(valor) {
  return (valor || '')
    .toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9_]/g, '_')
}

function addVariavel() {
  form.variaveis.push({ chave: '', label: '', tipo: 'texto' })
}

function removeVariavel(i) {
  form.variaveis.splice(i, 1)
}

function marcador(chave) {
  return '{{' + chave + '}}'
}

function copiar(chave) {
  navigator.clipboard?.writeText(marcador(chave))
}

function submit() {
  if (props.template) {
    form.put(`/admin/documento-templates/${props.template.id}`)
  } else {
    form.post('/admin/documento-templates')
  }
}
</script>
