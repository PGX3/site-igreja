<template>
  <AdminLayout>

    <div class="mb-6">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
        <Link href="/admin/familias" class="hover:text-blue-600 dark:hover:text-blue-400">Famílias</Link>
        <span class="mx-1">›</span>
        {{ familia.nome }}
      </p>
      <div class="flex items-end justify-between flex-wrap gap-3">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ familia.nome }}</h1>
        <div class="flex gap-2">
          <Link :href="`/admin/familias/${familia.id}/edit`"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition">
            Editar família
          </Link>
          <button @click="confirmarExclusao = true"
                  class="px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
            Excluir
          </button>
        </div>
      </div>
    </div>

    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <!-- Card de informações -->
      <aside class="md:col-span-1">
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-6 shadow-sm space-y-4">
          <div>
            <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Endereço</p>
            <p class="text-sm text-gray-800 dark:text-slate-200">{{ familia.endereco }}</p>
            <p class="text-sm text-gray-500 dark:text-slate-400">
              {{ familia.cidade }}<span v-if="familia.uf">/{{ familia.uf }}</span>
              <span v-if="familia.cep"> · CEP {{ familia.cep }}</span>
            </p>
          </div>
          <div v-if="familia.telefone_principal">
            <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Telefone</p>
            <p class="text-sm text-gray-800 dark:text-slate-200">{{ familia.telefone_principal }}</p>
          </div>
          <div v-if="familia.responsavel">
            <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Responsável</p>
            <p class="text-sm text-gray-800 dark:text-slate-200">{{ familia.responsavel }}</p>
          </div>
          <div v-if="familia.observacoes">
            <p class="text-[10px] font-bold tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Observações</p>
            <p class="text-sm text-gray-600 dark:text-slate-300 whitespace-pre-wrap">{{ familia.observacoes }}</p>
          </div>
        </div>
      </aside>

      <!-- Integrantes -->
      <section class="md:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xs font-bold tracking-widest uppercase text-gray-500 dark:text-slate-400">
            Integrantes ({{ familia.membros.length }})
          </h2>
          <Link :href="`/admin/familias/${familia.id}/edit`"
                class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
            + Adicionar pessoa
          </Link>
        </div>

        <div class="space-y-3">
          <div v-for="m in familia.membros" :key="m.id"
               class="bg-white dark:bg-slate-800 border rounded-xl p-4 shadow-sm transition"
               :class="m.is_responsavel
                 ? 'border-blue-400 dark:border-blue-500 ring-1 ring-blue-100 dark:ring-blue-900/30'
                 : 'border-gray-200 dark:border-slate-700'">
            <div class="flex items-center justify-between">
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span v-if="m.is_responsavel"
                        class="text-[10px] font-bold tracking-widest uppercase text-blue-600 dark:text-blue-400">★ Responsável</span>
                  <span :class="tipoBadge(m.tipo)" class="text-[10px] font-bold px-2 py-0.5 rounded-full">
                    {{ m.tipo === 'membro' ? 'Membro' : 'Visitante' }}
                  </span>
                  <span v-if="m.batizado_aguas === true"
                        class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                    Batizado
                  </span>
                </div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ m.name }}</p>
                <p class="text-xs text-gray-400 dark:text-slate-500">
                  {{ m.telefone || 'Sem telefone' }}
                  <span v-if="m.data_nascimento"> · Nasc {{ formatDate(m.data_nascimento) }}</span>
                </p>
              </div>
              <Link :href="m.tipo === 'membro' ? `/admin/membros/${m.id}/edit` : `/admin/visitantes/${m.id}/edit`"
                    class="text-xs font-semibold text-gray-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded transition">
                Ver ficha
              </Link>
            </div>
          </div>

          <div v-if="!familia.membros.length"
               class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl py-12 text-center text-gray-400 dark:text-slate-500">
            <p class="text-sm">Nenhum integrante vinculado.</p>
          </div>
        </div>
      </section>
    </div>

    <!-- Modal de exclusão -->
    <div v-if="confirmarExclusao"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Excluir família?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ familia.nome }}</strong>" será removida. Os {{ familia.membros.length }} integrante(s) continuam
          cadastrados como avulsos.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="confirmarExclusao = false"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/familias/${familia.id}`" method="delete" as="button"
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
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({ familia: Object })

const confirmarExclusao = ref(false)

function tipoBadge(tipo) {
  return tipo === 'membro'
    ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'
    : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'
}

function formatDate(d) {
  if (!d) return '—'
  const [y, m, day] = d.split('-')
  return `${day}/${m}/${y}`
}
</script>
