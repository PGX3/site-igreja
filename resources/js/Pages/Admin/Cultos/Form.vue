<template>
  <AdminLayout>
    <div class="mb-10">
      <Link href="/admin/cultos" class="text-xs text-gray-500 hover:text-white transition-colors tracking-widest uppercase">
        ← Voltar
      </Link>
      <h1 class="text-3xl font-black tracking-tight mt-4">
        {{ culto ? 'Editar Culto' : 'Novo Culto' }}
      </h1>
    </div>

    <form @submit.prevent="submit" class="max-w-lg flex flex-col gap-5">
      <div>
        <label class="text-[10px] tracking-[0.2em] uppercase text-gray-500 block mb-2">Nome</label>
        <input v-model="form.nome" type="text"
               class="w-full bg-[#1a1a1a] border border-white/10 px-5 py-4 text-sm text-white
                      outline-none focus:border-[#29b6f6] transition-colors" />
        <p v-if="form.errors.nome" class="text-red-400 text-xs mt-1">{{ form.errors.nome }}</p>
      </div>

      <div>
        <label class="text-[10px] tracking-[0.2em] uppercase text-gray-500 block mb-2">Dia da Semana</label>
        <select v-model="form.dia_semana"
                class="w-full bg-[#1a1a1a] border border-white/10 px-5 py-4 text-sm text-white
                       outline-none focus:border-[#29b6f6] transition-colors">
          <option v-for="d in dias" :key="d" :value="d">{{ d }}</option>
        </select>
      </div>

      <div>
        <label class="text-[10px] tracking-[0.2em] uppercase text-gray-500 block mb-2">Horário</label>
        <input v-model="form.horario" type="text" placeholder="ex: 19h30"
               class="w-full bg-[#1a1a1a] border border-white/10 px-5 py-4 text-sm text-white
                      outline-none focus:border-[#29b6f6] transition-colors" />
      </div>

      <div>
        <label class="text-[10px] tracking-[0.2em] uppercase text-gray-500 block mb-2">Descrição (opcional)</label>
        <textarea v-model="form.descricao" rows="3"
                  class="w-full bg-[#1a1a1a] border border-white/10 px-5 py-4 text-sm text-white
                         outline-none focus:border-[#29b6f6] transition-colors resize-none" />
      </div>

      <div class="flex items-center gap-3">
        <input v-model="form.ativo" type="checkbox" id="ativo"
               class="accent-[#29b6f6] w-4 h-4" />
        <label for="ativo" class="text-sm text-gray-400">Exibir no site</label>
      </div>

      <div class="pt-2">
        <button type="submit" :disabled="form.processing" class="btn-primary">
          {{ form.processing ? 'Salvando...' : (culto ? 'Salvar Alterações' : 'Criar Culto') }}
        </button>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ culto: Object })

const dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado']

const form = useForm({
  nome:       props.culto?.nome       ?? '',
  dia_semana: props.culto?.dia_semana ?? 'Domingo',
  horario:    props.culto?.horario    ?? '',
  descricao:  props.culto?.descricao  ?? '',
  ativo:      props.culto?.ativo      ?? true,
})

function submit() {
  if (props.culto) {
    form.put(`/admin/cultos/${props.culto.id}`)
  } else {
    form.post('/admin/cultos')
  }
}
</script>
