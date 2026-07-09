<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-6">
      <Link href="/admin/cursos"
            class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white transition">
        ← Cursos
      </Link>
      <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ curso.titulo }}</h1>
        <div class="flex items-center gap-4">
          <a :href="`/admin/aprender/${curso.id}`" target="_blank" rel="noopener"
             class="text-sm font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
            Ver curso ↗
          </a>
          <a :href="`/admin/aprender/${curso.id}/pdf`" target="_blank" rel="noopener"
             class="text-sm font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
            📄 Livro (PDF)
          </a>
          <Link :href="`/admin/cursos/${curso.id}/edit`"
                class="text-sm font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
            Editar dados do curso
          </Link>
        </div>
      </div>
    </div>

    <!-- LINK DO CURSO -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-4 mb-6">
      <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-2">
        Link compartilhável do curso
      </p>
      <div v-if="curso.share_url" class="flex flex-wrap items-center gap-2">
        <input :value="curso.share_url" readonly
               class="flex-1 min-w-[220px] text-sm border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 bg-gray-50 dark:bg-slate-900 text-gray-700 dark:text-slate-200" />
        <button @click="copiar(curso.share_url)" class="btn-sec">{{ copiado === curso.share_url ? 'Copiado!' : 'Copiar' }}</button>
        <button @click="revogar('cursos', curso.id)" class="btn-danger">Revogar</button>
      </div>
      <div v-else class="flex items-center gap-3">
        <p class="text-sm text-gray-400 dark:text-slate-500">Nenhum link gerado. Crie um para enviar nos grupos.</p>
        <button @click="gerar('cursos', curso.id)" class="btn-primary-sm">Gerar link</button>
      </div>
    </div>

    <!-- MÓDULOS -->
    <div class="space-y-4">
      <div v-for="(modulo, mi) in curso.modulos" :key="modulo.id"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl overflow-hidden">
        <!-- Cabeçalho do módulo -->
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-slate-900/40 border-b border-gray-100 dark:border-slate-700/50">
          <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">Mód. {{ mi + 1 }}</span>
          <p class="flex-1 font-semibold text-gray-900 dark:text-white truncate">{{ modulo.titulo }}</p>
          <a :href="`/admin/aprender/modulos/${modulo.id}/pdf`" target="_blank" rel="noopener" class="btn-icon" title="PDF do módulo">📄</a>
          <button @click="moverModulo(mi, -1)" :disabled="mi === 0" class="btn-icon" title="Subir">↑</button>
          <button @click="moverModulo(mi, 1)" :disabled="mi === curso.modulos.length - 1" class="btn-icon" title="Descer">↓</button>
          <button @click="abrirEdicaoModulo(modulo)" class="btn-icon" title="Editar">✎</button>
          <button @click="excluirModulo(modulo)" class="btn-icon hover:text-red-500" title="Excluir">🗑</button>
        </div>

        <!-- Aulas -->
        <div>
          <div v-for="(aula, ai) in modulo.aulas" :key="aula.id"
               class="flex items-center gap-2 px-4 py-3 border-b border-gray-100 dark:border-slate-700/50 last:border-0">
            <span class="w-6 text-right flex-shrink-0 text-gray-400 dark:text-slate-500 text-xs font-semibold">
              {{ ai + 1 }}
            </span>
            <span class="w-7 h-7 flex-shrink-0 rounded-full flex items-center justify-center" :class="metaTipo(aula.tipo).ring">
              <TipoAulaIcon :tipo="aula.tipo" :size="14" />
            </span>
            <Link :href="`/admin/aulas/${aula.id}/edit`"
                  class="flex-1 text-sm font-medium text-gray-800 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400 truncate">
              {{ aula.titulo }}
              <span v-if="!aula.ativo" class="text-xs text-gray-400 dark:text-slate-500">(oculta)</span>
            </Link>
            <Link :href="`/admin/aulas/${aula.id}/edit`" class="btn-link-sm" title="Editar a aula">editar</Link>
            <a :href="`/admin/aprender/aulas/${aula.id}`" target="_blank" rel="noopener" class="btn-link-sm" title="Pré-visualizar a aula">ver</a>
            <button v-if="aula.share_url" @click="copiar(aula.share_url)" class="btn-link-sm" title="Copiar link da aula">
              {{ copiado === aula.share_url ? 'Copiado!' : '🔗 link' }}
            </button>
            <button v-else @click="gerar('aulas', aula.id)" class="btn-link-sm">gerar link</button>
            <button @click="moverAula(modulo, ai, -1)" :disabled="ai === 0" class="btn-icon" title="Subir">↑</button>
            <button @click="moverAula(modulo, ai, 1)" :disabled="ai === modulo.aulas.length - 1" class="btn-icon" title="Descer">↓</button>
            <button @click="excluirAula(aula)" class="btn-icon hover:text-red-500" title="Excluir">🗑</button>
          </div>

          <!-- Nova aula -->
          <form @submit.prevent="adicionarAula(modulo)" class="flex items-center gap-2 px-4 py-3 bg-gray-50/60 dark:bg-slate-900/30">
            <input v-model="novaAula[modulo.id]" type="text" placeholder="Título da nova aula"
                   class="flex-1 text-sm border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 bg-white dark:bg-slate-700 text-gray-900 dark:text-white" />
            <button type="submit" class="btn-primary-sm">+ Aula</button>
          </form>
        </div>
      </div>
    </div>

    <!-- NOVO MÓDULO -->
    <form @submit.prevent="adicionarModulo" class="flex items-center gap-2 mt-4 max-w-lg">
      <input v-model="novoModulo" type="text" placeholder="Título do novo módulo"
             class="flex-1 text-sm border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 bg-white dark:bg-slate-800 text-gray-900 dark:text-white" />
      <button type="submit" class="btn-primary-sm">+ Módulo</button>
    </form>

    <!-- MODAL: editar módulo -->
    <Modal :model-value="!!moduloEditando" title="Editar módulo"
           @update:model-value="(v) => { if (!v) moduloEditando = null }">
      <form @submit.prevent="salvarModulo" class="flex flex-col gap-4">
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-1.5">Título</label>
          <input v-model="editForm.titulo" type="text"
                 class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
        </div>
        <div>
          <label class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-1.5">Descrição (opcional)</label>
          <input v-model="editForm.descricao" type="text"
                 class="w-full border border-gray-300 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />
        </div>
        <div class="flex items-center justify-end gap-2 pt-2">
          <button type="button" @click="moduloEditando = null" class="btn-sec">Cancelar</button>
          <button type="submit" class="btn-primary-sm">Salvar</button>
        </div>
      </form>
    </Modal>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import TipoAulaIcon from '@/Components/TipoAulaIcon.vue'
import { metaTipo } from '@/tipoAula.js'
import { Link, router } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'

const props = defineProps({ curso: { type: Object, required: true } })

const novoModulo = ref('')
const novaAula = reactive({})
const copiado = ref(null)
const moduloEditando = ref(null)
const editForm = reactive({ titulo: '', descricao: '' })

const opts = { preserveScroll: true }

function adicionarModulo() {
  if (!novoModulo.value.trim()) return
  router.post(`/admin/cursos/${props.curso.id}/modulos`, { titulo: novoModulo.value }, {
    ...opts,
    onSuccess: () => { novoModulo.value = '' },
  })
}

function abrirEdicaoModulo(modulo) {
  moduloEditando.value = modulo
  editForm.titulo = modulo.titulo
  editForm.descricao = modulo.descricao ?? ''
}

function salvarModulo() {
  if (!editForm.titulo.trim() || !moduloEditando.value) return
  router.put(`/admin/modulos/${moduloEditando.value.id}`, {
    titulo: editForm.titulo,
    descricao: editForm.descricao || null,
  }, {
    ...opts,
    onSuccess: () => { moduloEditando.value = null },
  })
}

function excluirModulo(modulo) {
  if (confirm(`Remover o módulo "${modulo.titulo}" e suas aulas?`)) {
    router.delete(`/admin/modulos/${modulo.id}`, opts)
  }
}

function moverModulo(index, dir) {
  const ids = props.curso.modulos.map((m) => m.id)
  const j = index + dir
  if (j < 0 || j >= ids.length) return
  ;[ids[index], ids[j]] = [ids[j], ids[index]]
  router.patch(`/admin/cursos/${props.curso.id}/modulos/reordenar`, { ids }, opts)
}

function adicionarAula(modulo) {
  const titulo = novaAula[modulo.id]
  if (!titulo || !titulo.trim()) return
  router.post(`/admin/modulos/${modulo.id}/aulas`, { titulo })
}

function excluirAula(aula) {
  if (confirm(`Remover a aula "${aula.titulo}"?`)) {
    router.delete(`/admin/aulas/${aula.id}`, opts)
  }
}

function moverAula(modulo, index, dir) {
  const ids = modulo.aulas.map((a) => a.id)
  const j = index + dir
  if (j < 0 || j >= ids.length) return
  ;[ids[index], ids[j]] = [ids[j], ids[index]]
  router.patch(`/admin/modulos/${modulo.id}/aulas/reordenar`, { ids }, opts)
}

function gerar(tipo, id) {
  router.post(`/admin/${tipo}/${id}/compartilhar`, {}, opts)
}

function revogar(tipo, id) {
  if (confirm('Revogar este link? Quem tiver a URL antiga perde o acesso.')) {
    router.delete(`/admin/${tipo}/${id}/compartilhar`, opts)
  }
}

function copiar(url) {
  navigator.clipboard?.writeText(url)
  copiado.value = url
  setTimeout(() => { copiado.value = null }, 1500)
}
</script>

<style scoped>
.btn-icon {
  @apply w-7 h-7 flex items-center justify-center rounded text-gray-400 dark:text-slate-500 hover:bg-gray-100 dark:hover:bg-slate-700 transition text-sm disabled:opacity-30;
}
.btn-primary-sm {
  @apply bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition;
}
.btn-sec {
  @apply bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-slate-200 px-3 py-2 rounded-lg text-xs font-semibold transition;
}
.btn-danger {
  @apply text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-2 rounded-lg text-xs font-semibold transition;
}
.btn-link-sm {
  @apply text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap px-1;
}
</style>
