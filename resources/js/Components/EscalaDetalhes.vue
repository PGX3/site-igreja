<template>
  <div v-if="temExtras" class="mt-3 pt-3 border-t border-gray-100 dark:border-slate-700/50">
    <div class="flex flex-wrap gap-2">
      <button v-if="musicas.length" @click="apresentando = true"
              class="text-xs font-semibold text-gray-900 dark:text-white bg-gray-900/5 dark:bg-white/10 hover:bg-gray-900/10 dark:hover:bg-white/20 px-3 py-1.5 rounded transition">
        ▶ Músicas ({{ musicas.length }})
      </button>
      <button v-if="musicas.length" @click="compartilhar"
              class="text-xs font-semibold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 px-3 py-1.5 rounded transition">
        Compartilhar
      </button>
      <button v-if="escala.anexos?.length || escala.notas?.length" @click="aberto = !aberto"
              class="text-xs font-semibold text-gray-500 dark:text-slate-400 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 px-3 py-1.5 rounded transition">
        📎 Anexos e notas
      </button>
    </div>

    <div v-if="aberto" class="mt-3 space-y-3">
      <!-- Anexos -->
      <div v-if="escala.anexos?.length" class="space-y-1.5">
        <a v-for="a in escala.anexos" :key="a.id"
           :href="a.tipo === 'arquivo' ? `/admin/assets/${a.id}/download` : `/storage/${a.arquivo_path}`"
           :target="a.tipo === 'arquivo' ? '_self' : '_blank'" rel="noopener"
           class="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400">
          <span>{{ a.tipo === 'imagem' ? '🖼' : '📄' }}</span>
          <span class="truncate">{{ a.titulo || a.arquivo_nome }}</span>
        </a>
      </div>
      <!-- Notas -->
      <div v-for="n in escala.notas" :key="n.id"
           class="bg-gray-50 dark:bg-slate-900/40 rounded-lg p-3">
        <p v-if="n.titulo" class="font-semibold text-gray-800 dark:text-slate-200 text-sm">{{ n.titulo }}</p>
        <p class="text-sm text-gray-600 dark:text-slate-300 whitespace-pre-wrap" :class="{ 'mt-1': n.titulo }">{{ n.corpo }}</p>
      </div>
    </div>

    <SetlistApresentacao v-if="apresentando" :musicas="musicas" @close="apresentando = false" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import SetlistApresentacao from '@/Components/SetlistApresentacao.vue'

const props = defineProps({ escala: Object })

const aberto = ref(false)
const apresentando = ref(false)

const musicas = computed(() => props.escala.setlist ?? [])
const temExtras = computed(() =>
  (props.escala.setlist?.length || 0) + (props.escala.anexos?.length || 0) + (props.escala.notas?.length || 0) > 0
)

function formatarData(data) {
  if (!data) return ''
  return new Date(data + 'T12:00:00').toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

function compartilhar() {
  const e = props.escala
  const linhas = [`*Setlist — ${e.titulo}*`]
  if (e.data) linhas.push(formatarData(e.data))
  linhas.push('')
  musicas.value.forEach((s, i) => {
    linhas.push(`${i + 1}. ${s.nome}${s.tom ? ` — Tom: ${s.tom}` : ''}`)
    if (s.observacao) linhas.push(`   (${s.observacao})`)
    if (s.link) linhas.push(`   ${s.link}`)
  })
  window.open(`https://wa.me/?text=${encodeURIComponent(linhas.join('\n'))}`, '_blank', 'noopener')
}
</script>
