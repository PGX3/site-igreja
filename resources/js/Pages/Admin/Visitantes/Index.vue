<template>
  <AdminLayout>

    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Pastoral</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Visitantes</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          {{ visitantes.length }} visitante(s) em acompanhamento
        </p>
      </div>
      <Link href="/admin/visitantes/create"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Visitante
      </Link>
    </div>

    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <form @submit.prevent="filtrar" class="mb-6 flex gap-3">
      <input v-model="termo" type="text" placeholder="Buscar por nome ou telefone..."
             class="flex-1 max-w-md border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                    bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                    placeholder-gray-400 dark:placeholder-slate-400
                    focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <button type="submit"
              class="bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-200 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
        Buscar
      </button>
      <Link v-if="busca" href="/admin/visitantes"
            class="px-3 py-2.5 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
        Limpar
      </Link>
    </form>

    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <table v-if="visitantes.length" class="w-full text-sm">
        <thead class="border-b border-gray-100 dark:border-slate-700">
          <tr>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Nome</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Telefone</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Convidado por</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">1ª visita</th>
            <th class="px-6 py-4"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="v in visitantes" :key="v.id"
              class="border-b border-gray-50 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
            <td class="px-6 py-4">
              <p class="font-semibold text-gray-900 dark:text-white">{{ v.name }}</p>
              <p v-if="v.como_conheceu" class="text-xs text-gray-400 dark:text-slate-500">{{ v.como_conheceu }}</p>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ v.telefone || '—' }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ v.convidado_por || '—' }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ formatDate(v.primeira_visita) }}</td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <button @click="paraPromover = v"
                        class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 px-3 py-1.5 rounded transition">
                  Promover
                </button>
                <Link :href="`/admin/visitantes/${v.id}/edit`"
                      class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                  Editar
                </Link>
                <button @click="paraExcluir = v"
                        class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                  Excluir
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="py-20 text-center text-gray-400 dark:text-slate-500">
        <p class="text-4xl mb-3">◈</p>
        <p class="font-medium">Nenhum visitante cadastrado.</p>
      </div>
    </div>

    <div v-if="paraExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Remover visitante?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ paraExcluir.name }}</strong>" será removido permanentemente.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="paraExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/visitantes/${paraExcluir.id}`" method="delete" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
            Remover
          </Link>
        </div>
      </div>
    </div>

    <div v-if="paraPromover"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Promover a membro?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ paraPromover.name }}</strong>" passará a ser membro da igreja. Você poderá completar
          os dados pastorais em seguida.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="paraPromover = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/visitantes/${paraPromover.id}/promover`" method="post" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition">
            Promover
          </Link>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  visitantes: Array,
  busca: String,
})

const termo = ref(props.busca ?? '')
const paraExcluir = ref(null)
const paraPromover = ref(null)

function filtrar() {
  router.get('/admin/visitantes', { busca: termo.value }, { preserveState: true, replace: true })
}

function formatDate(d) {
  if (!d) return '—'
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}
</script>
