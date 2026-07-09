<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dados da Igreja</h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
        Usados no cabeçalho e rodapé dos documentos oficiais.
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 sm:p-8 max-w-xl">

      <form @submit.prevent="submit" class="flex flex-col gap-6">

        <!-- LOGO -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Logo
          </label>
          <div class="flex items-center gap-4">
            <div class="w-20 h-20 rounded-lg border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-700 flex items-center justify-center overflow-hidden flex-shrink-0">
              <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="max-w-full max-h-full object-contain" />
              <span v-else class="text-2xl text-gray-300 dark:text-slate-500">⛪</span>
            </div>
            <div class="flex flex-col gap-2">
              <input ref="logoInput" type="file" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                     class="hidden" @change="onLogoChange" />
              <button type="button" @click="logoInput.click()"
                      class="text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline text-left">
                {{ logoPreview ? 'Trocar logo' : 'Enviar logo' }}
              </button>
              <button v-if="logoPreview" type="button" @click="removerLogo"
                      class="text-xs text-gray-400 hover:text-red-500 text-left transition">
                Remover
              </button>
            </div>
          </div>
          <p v-if="form.errors.logo" class="text-red-500 text-xs mt-1">{{ form.errors.logo }}</p>
        </div>

        <!-- NOME -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Nome da igreja
          </label>
          <input v-model="form.nome" type="text" :class="inputClass" />
          <p v-if="form.errors.nome" class="text-red-500 text-xs mt-1">{{ form.errors.nome }}</p>
        </div>

        <!-- CNPJ + CIDADE -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
              CNPJ
            </label>
            <input v-model="form.cnpj" type="text" placeholder="00.000.000/0000-00" :class="inputClass" />
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
              Cidade / UF
            </label>
            <input v-model="form.cidade" type="text" placeholder="Charqueadas / RS" :class="inputClass" />
          </div>
        </div>

        <!-- ENDEREÇO -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Endereço
          </label>
          <input v-model="form.endereco" type="text" placeholder="Rua Rui Barbosa, 1433" :class="inputClass" />
        </div>

        <!-- CONTATO -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
              Telefone
            </label>
            <input v-model="form.telefone" type="text" placeholder="(51) 90000-0000" :class="inputClass" />
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
              E-mail
            </label>
            <input v-model="form.email" type="email" placeholder="contato@igreja.org" :class="inputClass" />
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
          </div>
        </div>

        <!-- SITE -->
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
            Site
          </label>
          <input v-model="form.site" type="text" placeholder="www.igreja.org" :class="inputClass" />
        </div>

        <!-- BOTÕES -->
        <div class="flex items-center gap-3 pt-4">
          <button type="submit" :disabled="form.processing"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50">
            {{ form.processing ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>

      </form>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ igreja: Object })

const inputClass =
  'w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-3 text-sm ' +
  'bg-white dark:bg-slate-700 text-gray-900 dark:text-white ' +
  'placeholder-gray-400 dark:placeholder-slate-400 ' +
  'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition'

const logoInput = ref(null)
const logoPreview = ref(props.igreja?.logo_url ?? null)

const form = useForm({
  nome:     props.igreja?.nome     ?? '',
  cnpj:     props.igreja?.cnpj     ?? '',
  endereco: props.igreja?.endereco ?? '',
  cidade:   props.igreja?.cidade   ?? '',
  telefone: props.igreja?.telefone ?? '',
  email:    props.igreja?.email    ?? '',
  site:     props.igreja?.site     ?? '',
  logo:     null,
  remover_logo: false,
})

function onLogoChange(e) {
  const file = e.target.files?.[0]
  if (!file) return
  form.logo = file
  form.remover_logo = false
  logoPreview.value = URL.createObjectURL(file)
}

function removerLogo() {
  form.logo = null
  form.remover_logo = true
  logoPreview.value = null
  if (logoInput.value) logoInput.value.value = ''
}

function submit() {
  form.transform((data) => ({ ...data, _method: 'put' }))
    .post('/admin/igreja', { forceFormData: true, preserveScroll: true })
}
</script>
