<template>
  <AdminLayout>
    <div class="mb-10">
      <Link href="/admin/textos" class="text-xs text-gray-500 hover:text-white transition-colors tracking-widest uppercase">
        ← Voltar
      </Link>
      <h1 class="text-3xl font-black tracking-tight mt-4">Editar Texto</h1>
      <p class="text-[#29b6f6] text-xs tracking-widest uppercase mt-1">{{ texto.label }}</p>
    </div>

    <form @submit.prevent="submit" class="max-w-lg flex flex-col gap-5">
      <div>
        <label class="text-[10px] tracking-[0.2em] uppercase text-gray-500 block mb-2">Conteúdo</label>
        <textarea
          v-model="form.conteudo" rows="6"
          class="w-full bg-[#1a1a1a] border border-white/10 px-5 py-4 text-sm text-white
                 outline-none focus:border-[#29b6f6] transition-colors resize-none"
        />
        <p v-if="form.errors.conteudo" class="text-red-400 text-xs mt-1">{{ form.errors.conteudo }}</p>
      </div>

      <button type="submit" :disabled="form.processing" class="btn-primary w-fit">
        {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
      </button>
    </form>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ texto: Object })

const form = useForm({ conteudo: props.texto.conteudo })

function submit() {
  form.put(`/admin/textos/${props.texto.id}`)
}
</script>
