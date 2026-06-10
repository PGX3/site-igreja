<template>
  <AdminLayout>

    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Pastoral</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Famílias</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">{{ familias.length }} família(s) cadastrada(s)</p>
      </div>
      <Link href="/admin/familias/create"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Nova Família
      </Link>
    </div>

    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <form @submit.prevent="filtrar" class="mb-6 flex gap-3">
      <input v-model="termo" type="text" placeholder="Buscar por cidade ou nome de integrante..."
             class="flex-1 max-w-md border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                    bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                    placeholder-gray-400 dark:placeholder-slate-400
                    focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <button type="submit"
              class="bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-200 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
        Buscar
      </button>
      <Link v-if="busca" href="/admin/familias"
            class="px-3 py-2.5 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
        Limpar
      </Link>
    </form>

    <div v-if="familias.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Link v-for="f in familias" :key="f.id"
            :href="`/admin/familias/${f.id}`"
            class="group bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5 shadow-sm hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md transition">
        <div class="flex items-start justify-between mb-3">
          <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
            {{ f.nome }}
          </h3>
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
            {{ f.membros_count }} pessoa{{ f.membros_count === 1 ? '' : 's' }}
          </span>
        </div>
        <p v-if="f.responsavel" class="text-xs text-gray-500 dark:text-slate-400 mb-2">
          <span class="font-semibold">Responsável:</span> {{ f.responsavel }}
        </p>
        <p v-if="f.cidade" class="text-xs text-gray-400 dark:text-slate-500">
          {{ f.cidade }}<span v-if="f.uf">/{{ f.uf }}</span>
        </p>
        <p v-if="f.telefone_principal" class="text-xs text-gray-400 dark:text-slate-500 mt-1">
          {{ f.telefone_principal }}
        </p>
      </Link>
    </div>

    <div v-else class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl py-20 text-center text-gray-400 dark:text-slate-500">
      <p class="text-4xl mb-3">◈</p>
      <p class="font-medium">Nenhuma família encontrada.</p>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  familias: Array,
  busca: String,
})

const termo = ref(props.busca ?? '')

function filtrar() {
  router.get('/admin/familias', { busca: termo.value }, { preserveState: true, replace: true })
}
</script>
