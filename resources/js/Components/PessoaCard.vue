<template>
  <div
    class="rounded-xl border bg-white dark:bg-slate-800 shadow-sm transition"
    :class="pessoa.is_responsavel
      ? 'border-blue-400 dark:border-blue-500 ring-2 ring-blue-100 dark:ring-blue-900/30'
      : 'border-gray-200 dark:border-slate-700'"
  >
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-slate-700">
      <div class="flex items-center gap-2 min-w-0">
        <span v-if="pessoa.is_responsavel"
              class="text-[10px] font-bold tracking-widest uppercase text-blue-600 dark:text-blue-400 flex items-center gap-1.5 flex-shrink-0">
          <span>★</span> Responsável
        </span>
        <span v-else class="text-[10px] font-bold tracking-widest uppercase text-gray-400 dark:text-slate-500 flex-shrink-0">
          Pessoa {{ index + 1 }}
        </span>
        <span v-if="pessoa.id"
              class="text-[10px] font-bold tracking-widest uppercase text-emerald-600 dark:text-emerald-400
                     bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full flex-shrink-0"
              title="Pessoa já cadastrada no sistema, foi vinculada à família">
          ⇆ Vinculada
        </span>
        <span v-if="pessoa.name"
              class="text-sm font-semibold text-gray-700 dark:text-slate-200 ml-2 truncate">
          {{ pessoa.name }}
        </span>
      </div>
      <div class="flex items-center gap-1">
        <button v-if="!pessoa.is_responsavel" type="button"
                @click="$emit('tornar-responsavel')"
                class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 px-2 py-1 rounded transition">
          Definir como responsável
        </button>
        <button type="button" @click="open = !open"
                class="text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 w-7 h-7 rounded transition flex items-center justify-center">
          <span class="text-xs">{{ open ? '▾' : '▸' }}</span>
        </button>
        <button v-if="!pessoa.is_responsavel" type="button" @click="$emit('remove')"
                class="text-gray-400 dark:text-slate-500 hover:text-red-500 w-7 h-7 rounded transition flex items-center justify-center"
                title="Remover">
          <span class="text-sm">✕</span>
        </button>
      </div>
    </div>

    <div v-show="open" class="p-5 space-y-4">
      <div>
        <label :class="labelClass">Nome *</label>
        <input v-model="pessoa.name" type="text" placeholder="Nome completo"
               :class="[inputClass, errors.name && 'border-red-400']" />
        <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">Telefone *</label>
          <input v-model="pessoa.telefone" type="text" placeholder="(51) 99999-9999"
                 :class="[inputClass, errors.telefone && 'border-red-400']" />
          <p v-if="errors.telefone" class="mt-1 text-xs text-red-500">{{ errors.telefone }}</p>
        </div>
        <div>
          <label :class="labelClass">Email</label>
          <input v-model="pessoa.email" type="email" placeholder="email@exemplo.com"
                 :class="[inputClass, errors.email && 'border-red-400']" />
          <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label :class="labelClass">Data de nascimento</label>
          <input v-model="pessoa.data_nascimento" type="date"
                 :class="[inputClass, errors.data_nascimento && 'border-red-400']" />
          <p v-if="errors.data_nascimento" class="mt-1 text-xs text-red-500">{{ errors.data_nascimento }}</p>
        </div>
        <div>
          <label :class="labelClass">Sexo</label>
          <select v-model="pessoa.sexo" :class="inputClass">
            <option value="">Selecione...</option>
            <option value="M">Masculino</option>
            <option value="F">Feminino</option>
          </select>
        </div>
        <div>
          <label :class="labelClass">Estado civil</label>
          <select v-model="pessoa.estado_civil" :class="inputClass">
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
        <input v-model="pessoa.cpf" type="text" placeholder="000.000.000-00"
               :class="[inputClass, errors.cpf && 'border-red-400']" />
        <p v-if="errors.cpf" class="mt-1 text-xs text-red-500">{{ errors.cpf }}</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label :class="labelClass">Classificação</label>
          <div class="flex gap-2">
            <label class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-lg border cursor-pointer text-sm transition"
                   :class="pessoa.tipo === 'membro'
                     ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-semibold'
                     : 'border-gray-200 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700'">
              <input type="radio" value="membro" v-model="pessoa.tipo" class="sr-only" />
              Membro
            </label>
            <label class="flex-1 flex items-center justify-center gap-2 px-3 py-2 rounded-lg border cursor-pointer text-sm transition"
                   :class="pessoa.tipo === 'visitante'
                     ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-semibold'
                     : 'border-gray-200 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700'">
              <input type="radio" value="visitante" v-model="pessoa.tipo" class="sr-only" />
              Visitante
            </label>
          </div>
        </div>
        <div>
          <label :class="labelClass">Batizado nas águas?</label>
          <select v-model="pessoa.batizado_aguas" :class="inputClass">
            <option :value="null">Não informado</option>
            <option :value="true">Sim</option>
            <option :value="false">Não</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  pessoa: { type: Object, required: true },
  index: { type: Number, required: true },
  errors: { type: Object, default: () => ({}) },
})

defineEmits(['remove', 'tornar-responsavel'])

const open = ref(true)

const labelClass = 'block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1.5'
const inputClass = `w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm
                    bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                    placeholder-gray-400 dark:placeholder-slate-400
                    focus:outline-none focus:ring-2 focus:ring-blue-500`
</script>
