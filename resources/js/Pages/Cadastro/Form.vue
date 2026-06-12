<template>
  <MainLayout>
    <section class="pt-32 pb-24 px-6 md:px-16 max-w-3xl mx-auto">

      <header class="mb-12">
        <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)] mb-3">
          Cadastro
        </p>
        <h1 style="font-family:'Barlow Condensed',sans-serif"
            class="text-5xl md:text-6xl font-black tracking-tight leading-none mb-4">
          Faça parte da<br />nossa família.
        </h1>
        <p class="text-white/50 text-sm leading-relaxed max-w-xl">
          Preencha o formulário abaixo e nossa equipe pastoral entrará em contato.
          Não é necessário criar conta nem senha.
        </p>
      </header>

      <form @submit.prevent="submit" class="bg-white/[0.02] border border-white/10 rounded-2xl p-6 md:p-8">

        <!-- honeypot anti-spam -->
        <input v-model="form._honey" type="text" name="_honey" tabindex="-1" autocomplete="off"
               class="absolute opacity-0 pointer-events-none -left-[9999px]" aria-hidden="true" />

        <div class="space-y-5">
          <Campo label="Nome completo" required :error="form.errors.name">
            <input v-model="form.name" type="text" :class="inputClass" />
          </Campo>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <Campo label="Telefone" required :error="form.errors.telefone">
              <input v-model="form.telefone" type="text" placeholder="(51) 99999-9999" :class="inputClass" />
            </Campo>
            <Campo label="Data de nascimento" required :error="form.errors.data_nascimento">
              <input v-model="form.data_nascimento" type="date" :class="inputClass" />
            </Campo>
          </div>

          <Campo label="E-mail" :error="form.errors.email">
            <input v-model="form.email" type="email" placeholder="voce@exemplo.com" :class="inputClass" />
          </Campo>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <Campo label="Sexo">
              <select v-model="form.sexo" :class="inputClass">
                <option value="">Prefiro não informar</option>
                <option value="M">Masculino</option>
                <option value="F">Feminino</option>
              </select>
            </Campo>
            <Campo label="Estado civil">
              <select v-model="form.estado_civil" :class="inputClass">
                <option value="">Selecione...</option>
                <option value="solteiro">Solteiro(a)</option>
                <option value="casado">Casado(a)</option>
                <option value="divorciado">Divorciado(a)</option>
                <option value="viuvo">Viúvo(a)</option>
                <option value="uniao_estavel">União estável</option>
              </select>
            </Campo>
          </div>

          <Campo label="CPF" :error="form.errors.cpf">
            <input v-model="form.cpf" type="text" placeholder="000.000.000-00" :class="inputClass" />
          </Campo>

          <Campo label="Endereço">
            <input v-model="form.endereco" type="text" placeholder="Rua, número, complemento" :class="inputClass" />
          </Campo>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="sm:col-span-2">
              <Campo label="Cidade">
                <input v-model="form.cidade" type="text" :class="inputClass" />
              </Campo>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <Campo label="UF">
                <input v-model="form.uf" type="text" maxlength="2" :class="[inputClass, 'uppercase']" />
              </Campo>
              <Campo label="CEP">
                <input v-model="form.cep" type="text" placeholder="00000-000" :class="inputClass" />
              </Campo>
            </div>
          </div>

          <div class="pt-2 border-t border-white/10">
            <Campo label="Como conheceu a igreja?">
              <input v-model="form.como_conheceu" type="text"
                     placeholder="Amigo, rede social, evento..." :class="inputClass" />
            </Campo>
          </div>

          <Campo label="Primeira visita" :error="form.errors.primeira_visita">
            <input v-model="form.primeira_visita" type="date" :class="inputClass" />
          </Campo>

          <div class="pt-2 border-t border-white/10 space-y-5">
            <Campo label="Como você se declara?" required :error="form.errors.tipo">
              <select v-model="form.tipo" :class="inputClass">
                <option value="">Selecione...</option>
                <option value="membro">Membro da IEC</option>
                <option value="visitante">Simpatizante</option>
              </select>
            </Campo>

            <Campo label="Você é batizado nas águas?" required :error="form.errors.batizado_aguas">
              <select v-model="form.batizado_aguas" :class="inputClass">
                <option :value="null" disabled>Selecione...</option>
                <option :value="true">Sim</option>
                <option :value="false">Não</option>
              </select>
            </Campo>
          </div>
        </div>

        <button type="submit" :disabled="form.processing"
                class="btn-primary w-full mt-8 py-4 text-sm tracking-widest disabled:opacity-50">
          {{ form.processing ? 'ENVIANDO...' : 'ENVIAR CADASTRO' }}
        </button>

        <p class="text-[10px] tracking-widest uppercase text-white/25 text-center mt-4">
          Seus dados são tratados com confidencialidade pela equipe pastoral.
        </p>
      </form>
    </section>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { useForm } from '@inertiajs/vue3'
import { h } from 'vue'

const form = useForm({
  name: '',
  email: '',
  telefone: '',
  data_nascimento: '',
  sexo: '',
  estado_civil: '',
  cpf: '',
  endereco: '',
  cidade: '',
  uf: '',
  cep: '',
  como_conheceu: '',
  primeira_visita: '',
  tipo: '',
  batizado_aguas: null,
  _honey: '',
})

const inputClass = `w-full bg-white/[0.03] border border-white/10 rounded-lg px-4 py-3 text-sm text-white
                    placeholder-white/30 focus:outline-none focus:border-[var(--blue)] focus:bg-white/[0.05] transition`

function submit() {
  form.post('/cadastro')
}

const Campo = (props, { slots }) => h('div', null, [
  h('label', { class: 'block text-[10px] font-bold tracking-[0.25em] uppercase text-white/40 mb-2' },
    props.label + (props.required ? ' *' : '')),
  slots.default?.(),
  props.error
    ? h('p', { class: 'mt-1.5 text-xs text-red-400' }, props.error)
    : null,
])
Campo.props = ['label', 'required', 'error']
</script>
