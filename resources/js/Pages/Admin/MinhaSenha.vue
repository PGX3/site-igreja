<template>
  <AdminLayout>

    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Conta</p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Alterar Senha</h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Defina uma nova senha para sua conta.</p>
    </div>

    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <form @submit.prevent="submit" class="max-w-md space-y-5">

      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
          Senha atual *
        </label>
        <input v-model="form.senha_atual" type="password" placeholder="Sua senha atual"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.senha_atual }" />
        <p v-if="form.errors.senha_atual" class="mt-1 text-xs text-red-500">{{ form.errors.senha_atual }}</p>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
          Nova senha *
        </label>
        <input v-model="form.nova_senha" type="password" placeholder="Mínimo 6 caracteres"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': form.errors.nova_senha }" />
        <p v-if="form.errors.nova_senha" class="mt-1 text-xs text-red-500">{{ form.errors.nova_senha }}</p>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
          Confirmar nova senha *
        </label>
        <input v-model="form.nova_senha_confirmation" type="password" placeholder="Repita a nova senha"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                      placeholder-gray-400 dark:placeholder-slate-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500" />
      </div>

      <div class="pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ form.processing ? 'Salvando...' : 'Alterar Senha' }}
        </button>
      </div>

    </form>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  senha_atual:             '',
  nova_senha:              '',
  nova_senha_confirmation: '',
})

function submit() {
  form.put('/admin/minha-senha', {
    onSuccess: () => form.reset(),
  })
}
</script>
