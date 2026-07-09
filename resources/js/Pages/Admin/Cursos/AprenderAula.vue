<template>
  <AdminLayout>
    <div class="max-w-3xl">
      <div v-if="preview" class="mb-5 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/40 px-4 py-2.5 text-sm text-amber-700 dark:text-amber-300">
        Pré-visualização: esta aula está oculta e ainda não aparece para os membros.
      </div>
      <div class="flex justify-end mb-4">
        <a :href="`/admin/aprender/aulas/${aula.id}/pdf`" target="_blank" rel="noopener"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 px-3 py-1.5 rounded-lg transition">
          📄 Baixar PDF
        </a>
      </div>
      <AulaLeitura :aula="aula" :curso-titulo="cursoTitulo" :voltar-url="voltarUrl" />

      <!-- COMENTÁRIOS -->
      <div class="mt-12 border-t border-gray-200 dark:border-slate-700 pt-8">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
          Comentários <span class="text-gray-400 dark:text-slate-500 font-normal">({{ comentarios.length }})</span>
        </h2>

        <!-- Novo comentário -->
        <form @submit.prevent="enviar" class="flex gap-3 mb-6">
          <div class="w-9 h-9 flex-shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white" :style="{ background: '#2563eb' }">
            {{ iniciais }}
          </div>
          <div class="flex-1">
            <textarea v-model="form.corpo" rows="2" placeholder="Escreva um comentário ou dúvida..."
                      class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            <div class="flex items-center gap-2 mt-2">
              <button type="submit" :disabled="!form.corpo.trim() || form.processing"
                      class="text-sm font-semibold bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-1.5 rounded-lg transition">
                Comentar
              </button>
              <p v-if="form.errors.corpo" class="text-xs text-red-500">{{ form.errors.corpo }}</p>
            </div>
          </div>
        </form>

        <!-- Lista -->
        <div v-if="comentarios.length" class="space-y-5">
          <div v-for="c in comentarios" :key="c.id" class="flex gap-3">
            <div class="w-9 h-9 flex-shrink-0 rounded-full bg-gray-200 dark:bg-slate-600 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-slate-200">
              {{ inicialDe(c.autor) }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ c.autor }}</span>
                <span class="text-xs text-gray-400 dark:text-slate-500">{{ c.data }}</span>
                <button v-if="c.pode_excluir" @click="excluir(c.id)"
                        class="ml-auto text-xs text-gray-400 hover:text-red-500 transition">Excluir</button>
              </div>
              <p class="text-sm text-gray-700 dark:text-slate-300 mt-0.5 whitespace-pre-line">{{ c.corpo }}</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400 dark:text-slate-500 italic">Seja o primeiro a comentar.</p>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AulaLeitura from '@/Components/AulaLeitura.vue'

const props = defineProps({
  aula: { type: Object, required: true },
  cursoTitulo: { type: String, default: '' },
  voltarUrl: { type: String, default: null },
  preview: { type: Boolean, default: false },
  comentarios: { type: Array, default: () => [] },
})

const form = useForm({ corpo: '' })

const page = usePage()
const iniciais = computed(() => inicialDe(page.props.auth?.user?.name))

function inicialDe(nome) {
  const partes = (nome ?? '').split(' ').filter(Boolean)
  if (!partes.length) return '?'
  return (partes[0][0] + (partes.length > 1 ? partes[partes.length - 1][0] : '')).toUpperCase()
}

function enviar() {
  form.post(`/admin/aprender/aulas/${props.aula.id}/comentarios`, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}

function excluir(id) {
  if (confirm('Remover este comentário?')) {
    router.delete(`/admin/aprender/comentarios/${id}`, { preserveScroll: true })
  }
}
</script>
