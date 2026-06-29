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

    <!-- TABS -->
    <div class="flex gap-1 mb-6 border-b border-gray-200 dark:border-slate-700">
      <button v-for="tab in tabs" :key="tab.key"
              @click="abaAtiva = tab.key"
              :class="[
                'px-4 py-2.5 text-sm font-semibold transition rounded-t-lg',
                abaAtiva === tab.key
                  ? 'text-blue-600 dark:text-blue-400 border-b-2 border-blue-600 dark:border-blue-400'
                  : 'text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-300'
              ]">
        {{ tab.label }}
        <span v-if="tab.count !== undefined"
              class="ml-1.5 text-xs font-bold px-1.5 py-0.5 rounded-full"
              :class="abaAtiva === tab.key ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400' : 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400'">
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- ========================== ABA MURAL ========================== -->
    <div v-if="abaAtiva === 'mural'">

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

    <!-- ========================== ABA PDF ========================== -->
    <div v-if="abaAtiva === 'pdf'">

      <!-- FORM UPLOAD PDF (só líder/pastor) -->
      <div v-if="can_manage" class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 mb-6">
        <h2 class="text-sm font-bold text-gray-700 dark:text-slate-300 mb-4 uppercase tracking-wide">Adicionar PDF</h2>
        <form @submit.prevent="submitPDF" enctype="multipart/form-data" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
              <input v-model="novoPDF.nome" type="text" placeholder="Nome do arquivo / pasta"
                     class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                     :class="{ 'border-red-400': errosPDF.nome }" />
              <p v-if="errosPDF.nome" class="text-xs text-red-500 mt-1">{{ errosPDF.nome }}</p>
            </div>
            <div>
              <select v-model="novoPDF.tom"
                      class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tom (opcional)</option>
                <option v-for="tom in tons" :key="tom" :value="tom">{{ tom }}</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block w-full border-2 border-dashed border-gray-200 dark:border-slate-600 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition"
                   :class="pdfSelecionado ? 'border-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20' : ''">
              <input type="file" accept=".pdf" class="hidden" @change="onPDFChange" />
              <p class="text-sm text-gray-500 dark:text-slate-400">
                <span v-if="pdfSelecionado" class="text-blue-600 dark:text-blue-400 font-semibold">{{ pdfSelecionado }}</span>
                <template v-else>
                  <span class="block text-base mb-1">📄</span>
                  <span>Clique para selecionar o PDF · máx. 100 MB</span>
                </template>
              </p>
            </label>
            <p v-if="errosPDF.arquivo" class="text-xs text-red-500 mt-1">{{ errosPDF.arquivo }}</p>
          </div>
          <div class="flex justify-end">
            <button type="submit" :disabled="enviandoPDF"
                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
              {{ enviandoPDF ? 'Enviando…' : 'Adicionar PDF' }}
            </button>
          </div>
        </form>
      </div>

      <!-- LISTA PDFs -->
      <div v-if="!musicasPDF.length"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl py-16 text-center text-gray-400 dark:text-slate-500">
        <p class="text-3xl mb-2">📄</p>
        <p class="font-medium">Nenhum PDF adicionado ainda.</p>
      </div>

      <div v-else class="space-y-2">
        <div v-for="musica in musicasPDF" :key="musica.id"
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm flex items-center gap-3 px-5 py-3.5">
          <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-sm">📄</div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ musica.nome }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ musica.uploaded_by?.name }} · {{ musica.created_at }}</p>
          </div>
          <span v-if="musica.tom"
                class="hidden sm:inline-block bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0">
            {{ musica.tom }}
          </span>
          <div class="flex items-center gap-1 flex-shrink-0">
            <a :href="`/admin/grupos/${grupo.id}/musicas/${musica.id}/download`"
               class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
              Baixar
            </a>
            <button v-if="can_manage" @click="confirmarExclusaoMusica(musica)"
                    class="text-xs font-semibold text-gray-400 dark:text-slate-500 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
              Remover
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================== ABA MÚSICAS (manual) ========================== -->
    <div v-if="abaAtiva === 'musicas'">

      <!-- FORM ADICIONAR LETRA (só líder/pastor) -->
      <div v-if="can_manage" class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 mb-6">
        <h2 class="text-sm font-bold text-gray-700 dark:text-slate-300 mb-4 uppercase tracking-wide">Adicionar música</h2>
        <form @submit.prevent="submitMusica" class="space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
              <input v-model="novaMusica.nome" type="text" placeholder="Nome da música"
                     class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                     :class="{ 'border-red-400': errosMusica.nome }" />
              <p v-if="errosMusica.nome" class="text-xs text-red-500 mt-1">{{ errosMusica.nome }}</p>
            </div>
            <div>
              <select v-model="novaMusica.tom"
                      class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tom (opcional)</option>
                <option v-for="tom in tons" :key="tom" :value="tom">{{ tom }}</option>
              </select>
            </div>
          </div>
          <div>
            <textarea v-model="novaMusica.letra" rows="10"
                      placeholder="Cole ou digite a letra da música aqui..."
                      class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y font-mono"
                      :class="{ 'border-red-400': errosMusica.letra }"></textarea>
            <p v-if="errosMusica.letra" class="text-xs text-red-500 mt-1">{{ errosMusica.letra }}</p>
          </div>
          <div class="flex justify-end">
            <button type="submit" :disabled="enviandoMusica"
                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
              {{ enviandoMusica ? 'Salvando…' : 'Adicionar' }}
            </button>
          </div>
        </form>
      </div>

      <!-- LISTA MÚSICAS MANUAIS -->
      <div v-if="!musicasManual.length"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl py-16 text-center text-gray-400 dark:text-slate-500">
        <p class="text-3xl mb-2">🎵</p>
        <p class="font-medium">Nenhuma música adicionada ainda.</p>
      </div>

      <div v-else class="space-y-2">
        <div v-for="musica in musicasManual" :key="musica.id"
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
          <div class="flex items-center gap-3 px-5 py-3.5">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-sm">✍️</div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ musica.nome }}</p>
              <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">{{ musica.uploaded_by?.name }} · {{ musica.created_at }}</p>
            </div>
            <span v-if="musica.tom"
                  class="hidden sm:inline-block bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0">
              {{ musica.tom }}
            </span>
            <div class="flex items-center gap-1 flex-shrink-0">
              <button @click="toggleLetra(musica.id)"
                      class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                {{ letraAberta === musica.id ? 'Fechar' : 'Ver letra' }}
              </button>
              <button v-if="can_manage" @click="confirmarExclusaoMusica(musica)"
                      class="text-xs font-semibold text-gray-400 dark:text-slate-500 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                Remover
              </button>
            </div>
          </div>
          <div v-if="letraAberta === musica.id"
               class="border-t border-gray-100 dark:border-slate-700 px-5 py-4 bg-gray-50 dark:bg-slate-900/40">
            <pre class="text-sm text-gray-700 dark:text-slate-300 whitespace-pre-wrap font-sans leading-relaxed">{{ musica.letra }}</pre>
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

    <!-- MODAL CONFIRMAR EXCLUSÃO MÚSICA -->
    <div v-if="musicaParaExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Remover música?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ musicaParaExcluir.nome }}</strong>" será removida permanentemente.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="musicaParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/grupos/${grupo.id}/musicas/${musicaParaExcluir.id}`" method="delete" as="button"
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
  musicas:    Array,
  can_manage: Boolean,
})

const abaAtiva = ref('mural')

const musicasPDF    = computed(() => props.musicas.filter(m => m.tem_arquivo))
const musicasManual = computed(() => props.musicas.filter(m => m.letra))

const tabs = computed(() => {
  const list = [{ key: 'mural', label: 'Mural', count: props.avisos.length }]
  if (props.grupo.tem_musicas) {
    list.push({ key: 'pdf',    label: 'PDF',     count: musicasPDF.value.length })
    list.push({ key: 'musicas', label: 'Músicas', count: musicasManual.value.length })
  }
  return list
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

// ── PDF ──────────────────────────────────────────────────────
const novoPDF      = ref({ nome: '', tom: '' })
const pdfFile      = ref(null)
const pdfSelecionado = ref('')
const errosPDF     = ref({})
const enviandoPDF  = ref(false)

const tons = [
  'Dó', 'Dó#', 'Ré', 'Ré#', 'Mi', 'Fá',
  'Fá#', 'Sol', 'Sol#', 'Lá', 'Lá#', 'Si',
  'Dóm', 'Dó#m', 'Rém', 'Ré#m', 'Mim', 'Fám',
  'Fá#m', 'Solm', 'Sol#m', 'Lám', 'Lá#m', 'Sim',
]

function onPDFChange(e) {
  const file = e.target.files[0]
  pdfFile.value = file || null
  pdfSelecionado.value = file ? file.name : ''
  if (file && !novoPDF.value.nome.trim()) {
    novoPDF.value.nome = file.name.replace(/\.pdf$/i, '')
  }
}

function submitPDF() {
  errosPDF.value = {}
  if (!novoPDF.value.nome.trim()) { errosPDF.value.nome    = 'Obrigatório'; return }
  if (!pdfFile.value)             { errosPDF.value.arquivo = 'Selecione um PDF'; return }

  enviandoPDF.value = true
  const form = new FormData()
  form.append('nome', novoPDF.value.nome)
  if (novoPDF.value.tom) form.append('tom', novoPDF.value.tom)
  form.append('arquivo', pdfFile.value)

  router.post(`/admin/grupos/${props.grupo.id}/musicas`, form, {
    onSuccess: () => { novoPDF.value = { nome: '', tom: '' }; pdfFile.value = null; pdfSelecionado.value = '' },
    onFinish:  () => { enviandoPDF.value = false },
  })
}

// ── Músicas (manual) ─────────────────────────────────────────
const novaMusica        = ref({ nome: '', tom: '', letra: '' })
const errosMusica       = ref({})
const enviandoMusica    = ref(false)
const musicaParaExcluir = ref(null)
const letraAberta       = ref(null)

function toggleLetra(id) {
  letraAberta.value = letraAberta.value === id ? null : id
}

function submitMusica() {
  errosMusica.value = {}
  if (!novaMusica.value.nome.trim())  { errosMusica.value.nome  = 'Obrigatório'; return }
  if (!novaMusica.value.letra.trim()) { errosMusica.value.letra = 'Digite a letra da música'; return }

  enviandoMusica.value = true
  const form = new FormData()
  form.append('nome',  novaMusica.value.nome)
  form.append('letra', novaMusica.value.letra)
  if (novaMusica.value.tom) form.append('tom', novaMusica.value.tom)

  router.post(`/admin/grupos/${props.grupo.id}/musicas`, form, {
    onSuccess: () => { novaMusica.value = { nome: '', tom: '', letra: '' } },
    onFinish:  () => { enviandoMusica.value = false },
  })
}

function confirmarExclusaoMusica(musica) { musicaParaExcluir.value = musica }
</script>
