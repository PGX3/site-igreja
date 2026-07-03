<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <Link href="/admin/grupos" class="text-xs text-gray-400 dark:text-slate-500 hover:text-blue-500 dark:hover:text-blue-400 tracking-widest uppercase mb-1 inline-block transition">
          ← Grupos
        </Link>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ grupo.nome }}</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Mural do grupo</p>
      </div>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- ========================== MURAL ========================== -->
    <div>

      <!-- FORM NOVO AVISO (só líder/pastor) -->
      <div v-if="can_manage" class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 mb-6">
        <h2 class="text-sm font-bold text-gray-700 dark:text-slate-300 mb-4 uppercase tracking-wide">Publicar aviso</h2>
        <form @submit.prevent="submitAviso" class="space-y-3">
          <div>
            <input v-model="novoAviso.titulo" type="text" placeholder="Título do aviso"
                   class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <p v-if="errosAviso.titulo" class="text-xs text-red-500 mt-1">{{ errosAviso.titulo }}</p>
          </div>
          <div>
            <textarea v-model="novoAviso.corpo" rows="3" placeholder="Mensagem..."
                      class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            <p v-if="errosAviso.corpo" class="text-xs text-red-500 mt-1">{{ errosAviso.corpo }}</p>
          </div>
          <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="novoAviso.fixado" type="checkbox"
                     class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500" />
              <span class="text-sm text-gray-600 dark:text-slate-400">Fixar no topo</span>
            </label>
            <button type="submit" :disabled="enviandoAviso"
                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
              {{ enviandoAviso ? 'Publicando…' : 'Publicar' }}
            </button>
          </div>
        </form>
      </div>

      <!-- LISTA DE AVISOS -->
      <div v-if="!avisos.length"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl py-16 text-center text-gray-400 dark:text-slate-500">
        <p class="text-3xl mb-2">📋</p>
        <p class="font-medium">Nenhum aviso publicado ainda.</p>
      </div>

      <div v-else class="space-y-3">
        <div v-for="aviso in avisos" :key="aviso.id"
             class="bg-white dark:bg-slate-800 border rounded-xl shadow-sm overflow-hidden"
             :class="aviso.fixado ? 'border-blue-300 dark:border-blue-700' : 'border-gray-200 dark:border-slate-700'">
          <div class="p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-2 flex-wrap">
                <span v-if="aviso.fixado"
                      class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 uppercase tracking-wide">
                  Fixado
                </span>
                <p class="font-bold text-gray-900 dark:text-white text-base">{{ aviso.titulo }}</p>
              </div>
              <button v-if="can_manage" @click="confirmarExclusaoAviso(aviso)"
                      class="text-gray-300 dark:text-slate-600 hover:text-red-500 transition flex-shrink-0 text-lg leading-none mt-0.5">
                ×
              </button>
            </div>
            <p class="text-sm text-gray-600 dark:text-slate-300 mt-2 whitespace-pre-wrap">{{ aviso.corpo }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-3">
              {{ aviso.autor?.name }} · {{ aviso.created_at }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL CONFIRMAR EXCLUSÃO AVISO -->
    <div v-if="avisoParaExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Remover aviso?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ avisoParaExcluir.titulo }}</strong>" será excluído permanentemente.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="avisoParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/grupos/${grupo.id}/avisos/${avisoParaExcluir.id}`" method="delete" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
            Remover
          </Link>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  grupo:      Object,
  avisos:     Array,
  can_manage: Boolean,
})

// ── Avisos ──────────────────────────────────────────────────
const novoAviso     = ref({ titulo: '', corpo: '', fixado: false })
const errosAviso    = ref({})
const enviandoAviso = ref(false)
const avisoParaExcluir = ref(null)

function submitAviso() {
  errosAviso.value = {}
  if (!novoAviso.value.titulo.trim()) { errosAviso.value.titulo = 'Obrigatório'; return }
  if (!novoAviso.value.corpo.trim())  { errosAviso.value.corpo  = 'Obrigatório'; return }

  enviandoAviso.value = true
  router.post(`/admin/grupos/${props.grupo.id}/avisos`, novoAviso.value, {
    onSuccess: () => { novoAviso.value = { titulo: '', corpo: '', fixado: false } },
    onFinish:  () => { enviandoAviso.value = false },
  })
}

function confirmarExclusaoAviso(aviso) { avisoParaExcluir.value = aviso }
</script>
