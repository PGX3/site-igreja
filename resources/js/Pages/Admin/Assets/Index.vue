<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Gestão</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Anexos</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">{{ assets.length }} anexo(s) na biblioteca</p>
      </div>
      <Link href="/admin/assets/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Anexo
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- BUSCA -->
    <div class="mb-6 relative max-w-md">
      <input v-model="busca" type="text" placeholder="Buscar anexo..."
             class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500">
        <AppIcon name="search" />
      </span>
    </div>

    <!-- LISTA -->
    <div v-if="!assetsFiltrados.length"
         class="py-20 text-center text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
      <p class="text-4xl mb-3">📎</p>
      <p class="font-medium">Nenhum anexo na biblioteca.</p>
      <Link href="/admin/assets/create" class="mt-4 inline-block text-sm text-blue-600 dark:text-blue-400 hover:underline">
        Adicionar primeiro anexo
      </Link>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <div v-for="a in assetsFiltrados" :key="a.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-3 flex gap-3">
        <a v-if="a.tipo === 'imagem'" :href="`/storage/${a.arquivo_path}`" target="_blank" rel="noopener" class="flex-shrink-0">
          <img :src="`/storage/${a.arquivo_path}`" :alt="a.titulo || a.arquivo_nome"
               class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-slate-600" />
        </a>
        <div v-else class="flex-shrink-0 w-16 h-16 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-2xl">📄</div>

        <div class="flex-1 min-w-0">
          <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ a.titulo || a.arquivo_nome }}</p>
          <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-0.5 truncate">{{ a.created_by?.name }} · {{ a.created_at }}</p>
          <div class="flex items-center gap-2 mt-1.5">
            <a v-if="a.tipo === 'arquivo'" :href="`/admin/assets/${a.id}/download`"
               class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Baixar</a>
            <a v-else :href="`/storage/${a.arquivo_path}`" target="_blank" rel="noopener"
               class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Abrir</a>
            <Link :href="`/admin/assets/${a.id}/edit`"
                  class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400">Editar</Link>
            <button @click="assetParaExcluir = a"
                    class="text-xs font-semibold text-gray-400 dark:text-slate-500 hover:text-red-600">Excluir</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL EXCLUSÃO -->
    <div v-if="assetParaExcluir" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Excluir anexo?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ assetParaExcluir.titulo || assetParaExcluir.arquivo_nome }}</strong>" será removido da biblioteca e de todas as escalas que o usam.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="assetParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/assets/${assetParaExcluir.id}`" method="delete" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
            Excluir
          </Link>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AppIcon from '@/Components/AppIcon.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({ assets: Array })

const busca = ref('')
const assetParaExcluir = ref(null)

const assetsFiltrados = computed(() => {
  const q = busca.value.trim().toLowerCase()
  if (!q) return props.assets
  return props.assets.filter(a =>
    (a.titulo || '').toLowerCase().includes(q) || (a.arquivo_nome || '').toLowerCase().includes(q)
  )
})
</script>
