<template>
  <AdminLayout>
    <div class="flex items-center justify-between mb-10">
      <div>
        <h1 class="text-3xl font-black tracking-tight">Cultos</h1>
        <p class="text-gray-500 text-sm mt-1">Gerencie os cultos exibidos no site.</p>
      </div>
      <Link href="/admin/cultos/create" class="btn-primary text-xs">+ Novo Culto</Link>
    </div>

    <div class="flex flex-col gap-0.5">
      <div v-for="culto in cultos" :key="culto.id"
           class="bg-[#1a1a1a] flex items-center gap-6 px-8 py-6">
        <div class="flex-1">
          <p class="font-bold text-base">{{ culto.nome }}</p>
          <p class="text-gray-500 text-xs tracking-wide mt-0.5">
            {{ culto.dia_semana }} · {{ culto.horario }}
          </p>
        </div>
        <span
          class="text-[9px] font-bold tracking-widest uppercase px-3 py-1"
          :class="culto.ativo ? 'text-[#29b6f6] border border-[#29b6f6]/30' : 'text-white/25 border border-white/10'"
        >
          {{ culto.ativo ? 'Ativo' : 'Inativo' }}
        </span>
        <Link :href="`/admin/cultos/${culto.id}/edit`"
              class="text-xs text-gray-500 hover:text-white transition-colors tracking-wide">
          Editar
        </Link>
        <button @click="destroy(culto.id)"
                class="text-xs text-gray-600 hover:text-red-400 transition-colors tracking-wide">
          Excluir
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({ cultos: Array })

function destroy(id) {
  if (confirm('Remover este culto?')) {
    router.delete(`/admin/cultos/${id}`)
  }
}
</script>
