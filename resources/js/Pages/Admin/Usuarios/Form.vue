<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/usuarios" class="hover:text-blue-600 dark:hover:text-blue-400">Usuários</Link>
        <span class="mx-1">›</span>
        {{ editando ? 'Editar' : 'Novo' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
        {{ editando ? 'Editar Usuário' : 'Novo Usuário' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="max-w-xl space-y-5">

      <!-- Nome -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Nome *</label>
        <input v-model="form.name" type="text" placeholder="Nome completo"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.name }" />
        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Email *</label>
        <input v-model="form.email" type="email" placeholder="email@exemplo.com"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.email }" />
        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
      </div>

      <!-- Senha (apenas ao criar) -->
      <div v-if="!editando">
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
          Senha *
        </label>
        <input v-model="form.password" type="password" placeholder="Mínimo 6 caracteres"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.password }" />
        <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
      </div>

      <!-- Telefone -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Telefone</label>
        <input v-model="form.telefone" type="text" placeholder="(51) 99999-9999"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <!-- Data de Nascimento -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Data de Nascimento</label>
        <input v-model="form.data_nascimento" type="date"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.data_nascimento }" />
        <p v-if="form.errors.data_nascimento" class="mt-1 text-xs text-red-500">{{ form.errors.data_nascimento }}</p>
      </div>

      <!-- Papel -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Papel *</label>
        <select v-model="form.role_id"
                class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                       bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                       focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-400': form.errors.role_id }">
          <option value="">Selecione...</option>
          <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.display_name }}</option>
        </select>
        <p v-if="form.errors.role_id" class="mt-1 text-xs text-red-500">{{ form.errors.role_id }}</p>
      </div>

      <!-- Grupos (múltiplos) -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Grupos</label>
        <div class="border border-gray-200 dark:border-slate-600 rounded-lg overflow-hidden">
          <label v-for="g in grupos" :key="g.id"
                 class="flex items-center gap-3 px-4 py-2.5 cursor-pointer
                        hover:bg-gray-50 dark:hover:bg-slate-700/50 transition
                        border-b border-gray-100 dark:border-slate-700 last:border-b-0">
            <input type="checkbox" :value="g.id" v-model="form.grupo_ids"
                   class="w-4 h-4 rounded border-gray-300 dark:border-slate-500
                          text-blue-600 focus:ring-blue-500 cursor-pointer" />
            <span class="text-sm text-gray-800 dark:text-slate-200">{{ g.nome }}</span>
          </label>
          <div v-if="!grupos?.length"
               class="px-4 py-3 text-sm text-gray-400 dark:text-slate-500 italic">
            Nenhum grupo cadastrado.
          </div>
        </div>
      </div>

      <!-- AÇÕES -->
      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ editando ? 'Salvar alterações' : 'Criar Usuário' }}
        </button>
        <Link href="/admin/usuarios"
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
import { computed } from 'vue'

const props = defineProps({
  usuario: Object,
  roles:   Array,
  grupos:  Array,
})

const editando = computed(() => !!props.usuario)

const form = useForm({
  name:            props.usuario?.name            ?? '',
  email:           props.usuario?.email           ?? '',
  password:        '',
  telefone:        props.usuario?.telefone        ?? '',
  data_nascimento: props.usuario?.data_nascimento ?? '',
  role_id:         props.usuario?.role_id         ?? '',
  grupo_ids:       props.usuario?.grupo_ids       ?? [],
})

function submit() {
  if (editando.value) {
    form.put(`/admin/usuarios/${props.usuario.id}`)
  } else {
    form.post('/admin/usuarios')
  }
}
</script>
