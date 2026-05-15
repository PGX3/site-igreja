<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="flex items-end justify-between mb-8">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 mb-2">
          Intercessão
        </p>

        <h1 class="text-2xl font-bold text-gray-900">
          Pedidos de Oração
        </h1>

        <p class="text-gray-500 text-sm mt-1">
          {{ pedidos.length }} recebido(s) · {{ naoLidos }} pendente(s)
        </p>
      </div>

      <!-- FILTROS -->
      <div class="flex gap-2">
        <button
          @click="filtro = 'todos'"
          class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
          :class="filtro==='todos'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'border-gray-300 text-gray-600 hover:bg-gray-100'"
        >
          Todos
        </button>

        <button
          @click="filtro = 'novos'"
          class="px-4 py-2 text-sm font-semibold rounded-lg border transition"
          :class="filtro==='novos'
            ? 'bg-blue-600 text-white border-blue-600'
            : 'border-gray-300 text-gray-600 hover:bg-gray-100'"
        >
          Pendentes
        </button>
      </div>
    </div>

    <!-- EMPTY -->
    <div v-if="filtrados.length === 0"
         class="text-center py-16 text-gray-400 text-sm">
      Nenhum pedido encontrado.
    </div>

    <!-- LISTA -->
    <div class="space-y-4">
      <div v-for="p in filtrados" :key="p.id"
           class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition">

        <div class="flex items-start justify-between gap-6">

          <!-- CONTEÚDO -->
          <div class="flex-1">

            <div class="flex items-center gap-3 mb-3">
              <span class="font-semibold text-gray-900 text-sm">
                {{ p.anonimo ? 'Anônimo' : p.nome }}
              </span>

              <span v-if="p.anonimo"
                    class="text-xs font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                Anônimo
              </span>

              <span v-if="!p.lido"
                    class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                Pendente
              </span>
            </div>

            <p class="text-gray-700 text-sm leading-relaxed border-l-4 border-blue-100 pl-4 italic">
              "{{ p.pedido }}"
            </p>

            <p class="text-xs text-gray-400 mt-3">
              {{ formatDate(p.created_at) }}
            </p>

          </div>

          <!-- AÇÕES -->
          <div class="flex flex-col gap-2">

            <button
              @click="marcarLido(p)"
              class="text-xs font-semibold px-3 py-2 rounded-lg border transition"
              :class="p.lido
                ? 'border-gray-300 text-gray-500 hover:bg-gray-100'
                : 'bg-blue-600 text-white border-blue-600 hover:bg-blue-700'"
            >
              {{ p.lido ? 'Reabrir' : 'Orado ✓' }}
            </button>

            <button
              @click="excluir(p)"
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

const props = defineProps({ pedidos: Array })
const filtro = ref('todos')

const filtrados = computed(() =>
  filtro.value === 'novos'
    ? props.pedidos.filter(p => !p.lido)
    : props.pedidos
)

const naoLidos = computed(() =>
  props.pedidos.filter(p => !p.lido).length
)

function formatDate(d) {
  return new Date(d).toLocaleString('pt-BR', {
    dateStyle: 'short',
    timeStyle: 'short'
  })
}

function marcarLido(p) {
  router.patch(`/admin/pedidos-oracao/${p.id}/lido`, {}, { preserveScroll: true })
}

function excluir(p) {
  if (confirm('Excluir este pedido?')) {
    router.delete(`/admin/pedidos-oracao/${p.id}`, { preserveScroll: true })
  }
}
</script>