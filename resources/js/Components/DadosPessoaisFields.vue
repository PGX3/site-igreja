<template>
  <div class="space-y-5">
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

      <div v-if="showEmail">
        <label :class="labelClass">Email</label>
        <input v-model="form.email" type="email" placeholder="email@exemplo.com"
               :class="[inputClass, form.errors.email && 'border-red-400']" />
        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div v-if="showDataNascimento">
        <label :class="labelClass">Data de Nascimento</label>
        <input v-model="form.data_nascimento" type="date"
               :class="[inputClass, form.errors.data_nascimento && 'border-red-400']" />
        <p v-if="form.errors.data_nascimento" class="mt-1 text-xs text-red-500">{{ form.errors.data_nascimento }}</p>
      </div>

      <div>
        <label :class="labelClass">Sexo</label>
        <select v-model="form.sexo" :class="inputClass">
          <option value="">Selecione...</option>
          <option value="M">Masculino</option>
          <option value="F">Feminino</option>
        </select>
      </div>

      <div>
        <label :class="labelClass">Estado Civil</label>
        <select v-model="form.estado_civil" :class="inputClass">
          <option value="">Selecione...</option>
          <option value="solteiro">Solteiro(a)</option>
          <option value="casado">Casado(a)</option>
          <option value="divorciado">Divorciado(a)</option>
          <option value="viuvo">Viúvo(a)</option>
          <option value="uniao_estavel">União estável</option>
        </select>
      </div>
    </div>

    <div>
      <label :class="labelClass">CPF</label>
      <input v-model="form.cpf" type="text" placeholder="000.000.000-00"
             :class="[inputClass, form.errors.cpf && 'border-red-400']" />
      <p v-if="form.errors.cpf" class="mt-1 text-xs text-red-500">{{ form.errors.cpf }}</p>
    </div>

    <div>
      <label :class="labelClass">Endereço</label>
      <input v-model="form.endereco" type="text" placeholder="Rua, número, complemento" :class="inputClass" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
      <div class="sm:col-span-2">
        <label :class="labelClass">Cidade</label>
        <input v-model="form.cidade" type="text" :class="inputClass" />
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label :class="labelClass">UF</label>
          <input v-model="form.uf" type="text" maxlength="2"
                 :class="[inputClass, 'uppercase']" />
        </div>
        <div>
          <label :class="labelClass">CEP</label>
          <input v-model="form.cep" type="text" placeholder="00000-000" :class="inputClass" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  form: { type: Object, required: true },
  showEmail: { type: Boolean, default: true },
  showDataNascimento: { type: Boolean, default: true },
  dark: { type: Boolean, default: false },
})

const labelClass = 'block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5'
const inputClass = `w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                    bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                    placeholder-gray-400 dark:placeholder-slate-400
                    focus:outline-none focus:ring-2 focus:ring-blue-500`
</script>
