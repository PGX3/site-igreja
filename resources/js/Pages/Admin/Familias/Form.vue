<template>
  <AdminLayout>

    <div class="mb-6">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/familias" class="hover:text-blue-600 dark:hover:text-blue-400">Famílias</Link>
        <span class="mx-1">›</span>
        {{ editando ? 'Editar' : 'Nova' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ editando ? 'Editar Família' : 'Nova Família' }}
      </h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
        Cadastre o responsável e os demais integrantes na mesma tela. O sistema cria a família junto.
      </p>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <!-- ESQUERDA: dados da família (sticky) -->
        <aside class="md:col-span-5 lg:col-span-4">
          <div class="md:sticky md:top-6 space-y-4">
            <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm">
              <h2 class="text-xs font-bold tracking-widest uppercase text-gray-500 dark:text-slate-400 mb-5">
                Endereço e contato
              </h2>

              <div class="space-y-4">
                <div>
                  <label :class="labelClass">Endereço *</label>
                  <input v-model="form.endereco" type="text" placeholder="Rua, número, complemento"
                         :class="[inputClass, form.errors.endereco && 'border-red-400']" />
                  <p v-if="form.errors.endereco" class="mt-1 text-xs text-red-500">{{ form.errors.endereco }}</p>
                </div>

                <div class="grid grid-cols-3 gap-3">
                  <div class="col-span-2">
                    <label :class="labelClass">Cidade *</label>
                    <input v-model="form.cidade" type="text"
                           :class="[inputClass, form.errors.cidade && 'border-red-400']" />
                    <p v-if="form.errors.cidade" class="mt-1 text-xs text-red-500">{{ form.errors.cidade }}</p>
                  </div>
                  <div>
                    <label :class="labelClass">UF *</label>
                    <input v-model="form.uf" type="text" maxlength="2"
                           :class="[inputClass, 'uppercase', form.errors.uf && 'border-red-400']" />
                    <p v-if="form.errors.uf" class="mt-1 text-xs text-red-500">{{ form.errors.uf }}</p>
                  </div>
                </div>

                <div>
                  <label :class="labelClass">CEP *</label>
                  <input v-model="form.cep" type="text" placeholder="00000-000"
                         :class="[inputClass, form.errors.cep && 'border-red-400']" />
                  <p v-if="form.errors.cep" class="mt-1 text-xs text-red-500">{{ form.errors.cep }}</p>
                </div>

                <div>
                  <label :class="labelClass">Telefone principal</label>
                  <input v-model="form.telefone_principal" type="text" placeholder="(51) 99999-9999"
                         :class="inputClass" />
                  <p class="mt-1 text-[11px] text-gray-400 dark:text-slate-500">
                    Se vazio, usa o telefone do responsável.
                  </p>
                </div>

                <div>
                  <label :class="labelClass">Observações pastorais</label>
                  <textarea v-model="form.observacoes" rows="3"
                            placeholder="Anotações internas..."
                            :class="inputClass"></textarea>
                </div>
              </div>
            </div>

            <div class="flex gap-3">
              <button type="submit" :disabled="form.processing"
                      class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-5 py-3 rounded-lg text-sm font-semibold shadow-sm transition">
                {{ form.processing
                    ? 'Salvando...'
                    : (editando ? 'Salvar alterações' : `Cadastrar família com ${form.pessoas.length} pessoa(s)`) }}
              </button>
              <Link href="/admin/familias"
                    class="px-5 py-3 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
                Cancelar
              </Link>
            </div>
          </div>
        </aside>

        <!-- DIREITA: pessoas -->
        <section class="md:col-span-7 lg:col-span-8 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold tracking-widest uppercase text-gray-500 dark:text-slate-400">
              Integrantes ({{ form.pessoas.length }})
            </h2>
          </div>

          <PessoaCard v-for="(p, i) in form.pessoas" :key="i"
                      :pessoa="p"
                      :index="i"
                      :errors="errorsForIndex(i)"
                      @remove="removerPessoa(i)"
                      @tornar-responsavel="tornarResponsavel(i)" />

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button type="button" @click="adicionarPessoa"
                    class="border-2 border-dashed border-gray-300 dark:border-slate-600
                           text-gray-500 dark:text-slate-400 hover:border-blue-400 hover:text-blue-600
                           dark:hover:border-blue-500 dark:hover:text-blue-400
                           rounded-xl py-4 text-sm font-semibold transition">
              + Cadastrar nova pessoa
            </button>
            <button type="button" @click="abrirBuscaPessoa"
                    :disabled="pessoasFiltradas.length === 0"
                    class="border-2 border-dashed border-gray-300 dark:border-slate-600
                           text-gray-500 dark:text-slate-400 hover:border-emerald-400 hover:text-emerald-600
                           dark:hover:border-emerald-500 dark:hover:text-emerald-400
                           disabled:opacity-40 disabled:cursor-not-allowed
                           rounded-xl py-4 text-sm font-semibold transition">
              ⇆ Vincular pessoa cadastrada
              <span v-if="pessoasFiltradas.length"
                    class="ml-1 text-[10px] font-normal text-gray-400 dark:text-slate-500">
                ({{ pessoasFiltradas.length }} disponíveis)
              </span>
            </button>
          </div>
        </section>
      </div>
    </form>

    <!-- MODAL: busca de pessoa cadastrada -->
    <div v-if="buscaAberta"
         class="fixed inset-0 bg-black/40 flex items-start justify-center z-50 p-4 overflow-y-auto"
         @click.self="fecharBuscaPessoa">
      <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-2xl w-full mt-12 mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
          <div>
            <h3 class="font-bold text-gray-900 dark:text-white">Vincular pessoa cadastrada</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
              Pessoas sem família vinculada. {{ pessoasFiltradas.length }} disponíveis.
            </p>
          </div>
          <button @click="fecharBuscaPessoa"
                  class="text-gray-400 hover:text-gray-700 dark:hover:text-white w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition">
            ✕
          </button>
        </div>

        <div class="p-6">
          <input v-model="termoBusca" type="text" autofocus
                 placeholder="Buscar por nome ou telefone..."
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                        bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                        placeholder-gray-400 dark:placeholder-slate-400
                        focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4" />

          <div class="max-h-96 overflow-y-auto -mx-2">
            <button v-for="p in pessoasFiltradasComBusca" :key="p.id"
                    type="button" @click="vincularPessoa(p)"
                    class="w-full text-left px-4 py-3 mx-2 my-1 rounded-lg border border-gray-100 dark:border-slate-700
                           hover:border-emerald-400 dark:hover:border-emerald-500
                           hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition group">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">{{ p.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                    {{ p.telefone || 'Sem telefone' }}
                  </p>
                </div>
                <div class="flex items-center gap-2">
                  <span :class="p.tipo === 'membro'
                    ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
                    : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'"
                        class="text-[10px] font-bold px-2 py-0.5 rounded-full">
                    {{ p.tipo === 'membro' ? 'Membro' : 'Visitante' }}
                  </span>
                  <span class="text-emerald-500 dark:text-emerald-400 opacity-0 group-hover:opacity-100 transition">+</span>
                </div>
              </div>
            </button>

            <div v-if="!pessoasFiltradasComBusca.length"
                 class="text-center py-10 text-gray-400 dark:text-slate-500 text-sm">
              Nenhuma pessoa encontrada.
            </div>
          </div>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PessoaCard from '@/Components/PessoaCard.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  familia: Object,
  pessoas_disponiveis: { type: Array, default: () => [] },
})

const editando = computed(() => !!props.familia)

function pessoaVazia(isResp = false) {
  return {
    id: null,
    name: '',
    telefone: '',
    email: '',
    data_nascimento: '',
    sexo: '',
    estado_civil: '',
    cpf: '',
    tipo: 'membro',
    batizado_aguas: null,
    is_responsavel: isResp,
  }
}

const form = useForm({
  endereco:           props.familia?.endereco           ?? '',
  cidade:             props.familia?.cidade             ?? '',
  uf:                 props.familia?.uf                 ?? '',
  cep:                props.familia?.cep                ?? '',
  telefone_principal: props.familia?.telefone_principal ?? '',
  observacoes:        props.familia?.observacoes        ?? '',
  pessoas: props.familia?.pessoas?.length
    ? props.familia.pessoas.map(p => ({ ...pessoaVazia(), ...p }))
    : [pessoaVazia(true)],
})

const labelClass = 'block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1.5'
const inputClass = `w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm
                    bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                    placeholder-gray-400 dark:placeholder-slate-400
                    focus:outline-none focus:ring-2 focus:ring-blue-500`

function adicionarPessoa() {
  form.pessoas.push(pessoaVazia(false))
}

const buscaAberta = ref(false)
const termoBusca = ref('')

const pessoasFiltradas = computed(() => {
  const idsJaNaForm = new Set(form.pessoas.map(p => p.id).filter(Boolean))
  return props.pessoas_disponiveis.filter(p => !idsJaNaForm.has(p.id))
})

const pessoasFiltradasComBusca = computed(() => {
  const t = termoBusca.value.trim().toLowerCase()
  if (!t) return pessoasFiltradas.value
  return pessoasFiltradas.value.filter(p =>
    (p.name || '').toLowerCase().includes(t) ||
    (p.telefone || '').toLowerCase().includes(t)
  )
})

function abrirBuscaPessoa() {
  termoBusca.value = ''
  buscaAberta.value = true
}

function fecharBuscaPessoa() {
  buscaAberta.value = false
}

function vincularPessoa(p) {
  form.pessoas.push({
    id:               p.id,
    name:             p.name             ?? '',
    telefone:         p.telefone         ?? '',
    email:            p.email            ?? '',
    data_nascimento:  p.data_nascimento  ?? '',
    sexo:             p.sexo             ?? '',
    estado_civil:     p.estado_civil     ?? '',
    cpf:              p.cpf              ?? '',
    tipo:             p.tipo             ?? 'membro',
    batizado_aguas:   p.batizado_aguas   ?? null,
    is_responsavel:   false,
  })
  fecharBuscaPessoa()
}

function removerPessoa(i) {
  if (form.pessoas[i].is_responsavel) return
  form.pessoas.splice(i, 1)
}

function tornarResponsavel(i) {
  form.pessoas.forEach((p, idx) => { p.is_responsavel = idx === i })
}

function errorsForIndex(i) {
  const prefix = `pessoas.${i}.`
  const out = {}
  for (const [key, msg] of Object.entries(form.errors)) {
    if (key.startsWith(prefix)) {
      out[key.slice(prefix.length)] = msg
    }
  }
  return out
}

function submit() {
  if (!form.telefone_principal) {
    const resp = form.pessoas.find(p => p.is_responsavel)
    if (resp?.telefone) form.telefone_principal = resp.telefone
  }
  if (editando.value) {
    form.put(`/admin/familias/${props.familia.id}`)
  } else {
    form.post('/admin/familias')
  }
}
</script>
