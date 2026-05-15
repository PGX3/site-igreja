<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex items-end justify-between mb-8">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 mb-2">
          Comunicações
        </p>

        <h1 class="text-2xl font-bold text-gray-900">
          Sugestões
        </h1>

        <p class="text-gray-500 text-sm mt-1">
          {{ sugestoes.length }} recebida(s) · {{ naoLidas }} não lida(s)
        </p>
      </div>

      <!-- FILTROS -->
      <div class="flex gap-2">
        <button
          @click="filtro = 'todas'"
          class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
          :class="filtro==='todas'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'border-gray-300 text-gray-600 hover:bg-gray-100'"
        >
          Todas
        </button>

        <button
          @click="filtro = 'novas'"
          class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
          :class="filtro==='novas'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'border-gray-300 text-gray-600 hover:bg-gray-100'"
        >
          Novas
        </button>
      </div>
    </div>

    <!-- EMPTY -->
    <div v-if="filtradas.length === 0"
         class="text-center py-16 text-gray-400 text-sm">
      Nenhuma sugestão encontrada.
    </div>

    <!-- LISTA -->
    <div class="space-y-4">
      <div v-for="s in filtradas" :key="s.id"
           class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition">

        <div class="flex items-start justify-between gap-6">

          <!-- CONTEÚDO -->
          <div class="flex-1">

            <div class="flex items-center gap-3 mb-2">
              <span class="font-semibold text-gray-900 text-sm">
                {{ s.nome }}
              </span>

              <span v-if="!s.lida"
                    class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                Nova
              </span>
            </div>

            <p v-if="s.email"
               class="text-xs text-gray-400 mb-3 font-mono">
              {{ s.email }}
            </p>

            <p class="text-gray-700 text-sm leading-relaxed border-l-4 border-blue-100 pl-4">
              {{ s.mensagem }}
            </p>

            <p class="text-xs text-gray-400 mt-3">
              {{ formatDate(s.created_at) }}
            </p>

          </div>

          <!-- AÇÕES -->
          <div class="flex flex-col gap-2">

            <button
              @click="marcarLida(s)"
              class="text-xs font-semibold px-3 py-2 rounded-lg border transition"
              :class="s.lida
                ? 'border-gray-300 text-gray-500 hover:bg-gray-100'
                : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'"
            >
              {{ s.lida ? 'Reabrir' : 'Marcar lida' }}
            </button>

            <button
              @click="excluir(s)"
              class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 text-gray-500 hover:text-red-500 hover:border-red-300 transition"
            >
              Excluir
            </button>

          </div>

        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ sugestoes: Array })
const filtro = ref('todas')

const filtradas = computed(() =>
  filtro.value === 'novas'
    ? props.sugestoes.filter(s => !s.lida)
    : props.sugestoes
)

const naoLidas = computed(() =>
  props.sugestoes.filter(s => !s.lida).length
)

function formatDate(d) {
  return new Date(d).toLocaleString('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short'
  })
}

function marcarLida(s) {
  router.patch(`/admin/sugestoes/${s.id}/lida`, {}, { preserveScroll: true })
}

function excluir(s) {
  if (confirm('Excluir esta sugestão?')) {
    router.delete(`/admin/sugestoes/${s.id}`, { preserveScroll: true })
  }
}
</script>