<template>
  <AdminLayout>
    
    <!-- HEADER -->
    <div class="mb-10">
      <p class="text-xs tracking-widest uppercase text-gray-400 mb-2">
        Visão Geral
      </p>

      <h1 class="text-3xl font-bold text-gray-900">
        Dashboard
      </h1>

      <p class="text-gray-500 text-sm mt-1">
        Igreja em Charqueadas · Painel de Controle
      </p>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition"
      >
        <p class="text-xs uppercase text-gray-400 mb-2">
          {{ stat.label }}
        </p>

        <p class="text-3xl font-bold text-gray-900">
          {{ stat.value }}
        </p>

        <p v-if="stat.sub" class="text-xs text-gray-400 mt-1">
          {{ stat.sub }}
        </p>

        <!-- destaque -->
        <div v-if="stat.highlight"
             class="mt-3 inline-block text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
          Novo
        </div>
      </div>
    </div>

    <!-- AÇÕES -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm mb-8">
      <p class="text-xs uppercase tracking-widest text-gray-400 mb-4">
        Ações rápidas
      </p>

      <div class="flex flex-wrap gap-3">
        <Link href="/admin/cultos/create"
              class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          + Novo Culto
        </Link>

        <Link href="/admin/textos"
              class="border border-gray-300 text-gray-700 hover:bg-gray-100 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
          Editar Textos
        </Link>

        <Link v-if="novasSugestoes > 0"
              href="/admin/sugestoes"
              class="border border-gray-300 text-gray-700 hover:bg-gray-100 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
          {{ novasSugestoes }} nova(s) sugestão(ões)
        </Link>

        <Link v-if="novosPedidos > 0"
              href="/admin/pedidos-oracao"
              class="border border-gray-300 text-gray-700 hover:bg-gray-100 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
          {{ novosPedidos }} pedido(s)
        </Link>
      </div>
    </div>

    <!-- VER SITE -->
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex justify-between items-center">
      <div>
        <p class="text-sm font-semibold text-gray-800">
          Visualizar site
        </p>
        <p class="text-sm text-gray-500">
          Acesse o site da igreja em uma nova aba
        </p>
      </div>

      <Link href="/" target="_blank"
            class="border border-gray-300 px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-100 transition">
        Abrir Site ↗
      </Link>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  totalCultos: Number,
  totalTextos: Number,
  novasSugestoes: Number,
  novosPedidos: Number,
  totalSugestoes: Number,
  totalPedidos: Number,
})

const stats = computed(() => [
  { label: 'Cultos', value: props.totalCultos || 0 },
  { label: 'Textos editáveis', value: props.totalTextos || 0 },
  {
    label: 'Sugestões',
    value: props.novasSugestoes || 0,
    highlight: props.novasSugestoes > 0,
    sub: `${props.totalSugestoes || 0} total`
  },
  {
    label: 'Pedidos de oração',
    value: props.novosPedidos || 0,
    highlight: props.novosPedidos > 0,
    sub: `${props.totalPedidos || 0} total`
  },
])
</script>