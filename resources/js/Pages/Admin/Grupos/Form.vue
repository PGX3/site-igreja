<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 mb-1">
        <Link href="/admin/grupos" class="hover:text-blue-600">Grupos</Link>
        <span class="mx-1">›</span>
        {{ editando ? 'Editar' : 'Novo' }}
      </p>
      <h1 class="text-3xl font-bold text-gray-900">
        {{ editando ? 'Editar Grupo' : 'Novo Grupo' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="max-w-xl space-y-6">

      <!-- Nome -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nome do Grupo *</label>
        <input v-model="form.nome" type="text" placeholder="Ex: Louvor, Recepção, Som..."
               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               :class="{ 'border-red-400': errors.nome }" />
        <p v-if="errors.nome" class="mt-1 text-xs text-red-500">{{ errors.nome }}</p>
      </div>

      <!-- Descrição -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Descrição</label>
        <textarea v-model="form.descricao" rows="3" placeholder="Descreva o propósito do grupo..."
                  class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
      </div>

      <!-- Líder -->
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Líder do Grupo</label>
        <select v-model="form.lider_id"
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
          <option :value="null">Sem líder</option>
          <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
        </select>
        <p class="mt-1.5 text-xs text-gray-400">
          O usuário selecionado terá seu papel alterado para "Líder" automaticamente.
        </p>
      </div>

      <!-- AÇÕES -->
      <div class="flex gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ editando ? 'Salvar alterações' : 'Criar Grupo' }}
        </button>
        <Link href="/admin/grupos"
              class="px-6 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition">
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
  grupo:    Object,
  usuarios: Array,
})

const editando = computed(() => !!props.grupo)

const form = useForm({
  nome:      props.grupo?.nome      ?? '',
  descricao: props.grupo?.descricao ?? '',
  lider_id:  props.grupo?.lider?.id ?? null,
})

const errors = computed(() => form.errors)

function submit() {
  if (editando.value) {
    form.put(`/admin/grupos/${props.grupo.id}`)
  } else {
    form.post('/admin/grupos')
  }
}
</script>
