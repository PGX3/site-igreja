<template>
  <div class="border rounded-lg overflow-hidden"
       :class="temErro ? 'border-red-400' : 'border-gray-200 dark:border-slate-600'">
    <!-- TOOLBAR -->
    <div v-if="editor"
         class="flex flex-wrap items-center gap-1 px-2 py-2 border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/40">
      <button type="button" @click="editor.chain().focus().toggleBold().run()"
              :class="btnClass(editor.isActive('bold'))" title="Negrito">
        <span class="font-bold">B</span>
      </button>
      <button type="button" @click="editor.chain().focus().toggleItalic().run()"
              :class="btnClass(editor.isActive('italic'))" title="Itálico">
        <span class="italic font-serif">I</span>
      </button>
      <button type="button" @click="editor.chain().focus().toggleUnderline().run()"
              :class="btnClass(editor.isActive('underline'))" title="Sublinhado">
        <span class="underline">U</span>
      </button>

      <!-- BLOCOS (títulos, listas, alinhamento) -->
      <template v-if="permiteBlocos">
        <span class="w-px h-5 bg-gray-200 dark:bg-slate-600 mx-1"></span>

        <button type="button" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="btnClass(editor.isActive('heading', { level: 2 }))" title="Título">
          <span class="font-bold text-xs">T</span>
        </button>
        <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                :class="btnClass(editor.isActive('bulletList'))" title="Lista">
          <span class="text-base leading-none">•</span>
        </button>
        <button type="button" @click="editor.chain().focus().toggleOrderedList().run()"
                :class="btnClass(editor.isActive('orderedList'))" title="Lista numerada">
          <span class="text-[11px] font-semibold">1.</span>
        </button>
        <button type="button" @click="editor.chain().focus().toggleBlockquote().run()"
                :class="btnClass(editor.isActive('blockquote'))" title="Citação">
          <span class="font-serif text-base leading-none">”</span>
        </button>

        <span class="w-px h-5 bg-gray-200 dark:bg-slate-600 mx-1"></span>

        <button v-for="al in alinhamentos" :key="al.dir" type="button"
                @click="editor.chain().focus().setTextAlign(al.dir).run()"
                :class="btnClass(editor.isActive({ textAlign: al.dir }))" :title="al.title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round">
            <line v-for="(l, i) in al.linhas" :key="i" :x1="l[0]" y1="l[2]" :x2="l[1]" :y2="l[2]" />
          </svg>
        </button>
      </template>

      <span class="w-px h-5 bg-gray-200 dark:bg-slate-600 mx-1"></span>

      <button v-for="c in cores" :key="c" type="button"
              @click="editor.chain().focus().setColor(c).run()"
              class="w-6 h-6 rounded-full border border-gray-300 dark:border-slate-500 flex items-center justify-center transition hover:scale-110"
              :style="{ backgroundColor: c }" :title="`Cor`">
        <span v-if="editor.isActive('textStyle', { color: c })" class="w-1.5 h-1.5 rounded-full bg-white ring-1 ring-black/20"></span>
      </button>
      <button type="button" @click="editor.chain().focus().unsetColor().run()"
              :class="btnClass(false)" title="Cor padrão">
        <span class="w-4 h-4 rounded-full border border-gray-400 dark:border-slate-500 bg-white dark:bg-slate-800"></span>
      </button>

      <!-- IMAGEM -->
      <template v-if="permiteImagem">
        <span class="w-px h-5 bg-gray-200 dark:bg-slate-600 mx-1"></span>
        <button type="button" @click="abrirSeletor" :disabled="enviando"
                :class="btnClass(false)" title="Inserir imagem">
          <svg v-if="!enviando" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
            <circle cx="8.5" cy="8.5" r="1.5" />
            <path d="M21 15l-5-5L5 21" />
          </svg>
          <svg v-else class="animate-spin" width="15" height="15" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-20" />
            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
          </svg>
        </button>
        <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onFileChange" />
      </template>
    </div>

    <!-- EDITOR -->
    <EditorContent :editor="editor"
                   class="px-4 py-3 min-h-[220px] text-sm leading-relaxed
                          bg-white dark:bg-slate-700 text-gray-900 dark:text-white" />

    <p v-if="erroUpload" class="px-4 py-2 text-xs text-red-500 border-t border-gray-100 dark:border-slate-700">
      {{ erroUpload }}
    </p>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { TextStyle, Color } from '@tiptap/extension-text-style'
import TextAlign from '@tiptap/extension-text-align'
import Image from '@tiptap/extension-image'

const props = defineProps({
  modelValue:    { type: String, default: '' },
  temErro:       { type: Boolean, default: false },
  permiteImagem: { type: Boolean, default: false },
  permiteBlocos: { type: Boolean, default: false },
  uploadUrl:     { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue'])

const cores = ['#000000', '#dc2626', '#2563eb', '#16a34a', '#9333ea', '#ea580c']

// Cada botão de alinhamento desenha 3 linhas: [x1, x2, y]
const alinhamentos = [
  { dir: 'left',   title: 'Alinhar à esquerda', linhas: [[3, 21, 6], [3, 13, 12], [3, 21, 18]] },
  { dir: 'center', title: 'Centralizar',        linhas: [[3, 21, 6], [7, 17, 12], [3, 21, 18]] },
  { dir: 'right',  title: 'Alinhar à direita',  linhas: [[3, 21, 6], [11, 21, 12], [3, 21, 18]] },
]

const fileInput = ref(null)
const enviando = ref(false)
const erroUpload = ref('')

const extensions = [StarterKit, TextStyle, Color]
if (props.permiteBlocos) {
  extensions.push(TextAlign.configure({ types: ['heading', 'paragraph'] }))
}
if (props.permiteImagem) {
  extensions.push(Image.configure({ inline: false, allowBase64: false }))
}

const editor = useEditor({
  content: props.modelValue,
  extensions,
  editorProps: props.permiteImagem ? {
    handlePaste: (view, event) => tratarArquivos(Array.from(event.clipboardData?.files || [])),
    handleDrop: (view, event) => tratarArquivos(Array.from(event.dataTransfer?.files || [])),
  } : {},
  onUpdate: ({ editor }) => {
    const html = editor.getHTML()
    emit('update:modelValue', html === '<p></p>' ? '' : html)
  },
})

function tratarArquivos(arquivos) {
  const imagens = arquivos.filter((f) => f.type.startsWith('image/'))
  if (!imagens.length) return false
  imagens.forEach((f) => enviarImagem(f))
  return true // impede o comportamento padrão (colar como base64/link)
}

function abrirSeletor() {
  erroUpload.value = ''
  fileInput.value?.click()
}

function onFileChange(e) {
  const file = e.target.files?.[0]
  if (file) enviarImagem(file)
  e.target.value = '' // permite reenviar o mesmo arquivo
}

function csrfToken() {
  const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)
  return match ? decodeURIComponent(match[1]) : ''
}

async function enviarImagem(file) {
  if (!props.uploadUrl) return
  erroUpload.value = ''
  enviando.value = true
  try {
    const fd = new FormData()
    fd.append('imagem', file)
    const res = await fetch(props.uploadUrl, {
      method: 'POST',
      headers: { 'X-XSRF-TOKEN': csrfToken(), Accept: 'application/json' },
      credentials: 'same-origin',
      body: fd,
    })
    if (!res.ok) {
      const data = await res.json().catch(() => ({}))
      throw new Error(data.message || 'Falha ao enviar a imagem.')
    }
    const { url } = await res.json()
    editor.value.chain().focus().setImage({ src: url }).run()
  } catch (err) {
    erroUpload.value = err.message || 'Não foi possível enviar a imagem.'
  } finally {
    enviando.value = false
  }
}

function btnClass(active) {
  return [
    'w-7 h-7 flex items-center justify-center rounded text-gray-600 dark:text-slate-300 transition disabled:opacity-40',
    active
      ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300'
      : 'hover:bg-gray-200 dark:hover:bg-slate-700',
  ]
}

watch(() => props.modelValue, (val) => {
  if (editor.value && val !== editor.value.getHTML() && !(val === '' && editor.value.getHTML() === '<p></p>')) {
    editor.value.commands.setContent(val || '', false)
  }
})

onBeforeUnmount(() => editor.value?.destroy())
</script>

<style scoped>
/* Remove a borda/contorno azul de foco do contenteditable */
:deep(.ProseMirror) {
  outline: none;
  min-height: 200px;
}
/* Cada linha é um parágrafo: sem margem extra, para que linhas em branco
   valham exatamente uma linha (igual ao que se digita). */
:deep(.ProseMirror p) {
  margin: 0;
}
:deep(.ProseMirror img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  margin: 0.5rem 0;
}
:deep(.ProseMirror img.ProseMirror-selectednode) {
  outline: 2px solid #2563eb;
}
:deep(.ProseMirror h2) {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0.5rem 0 0.25rem;
}
:deep(.ProseMirror ul) { list-style: disc; padding-left: 1.25rem; }
:deep(.ProseMirror ol) { list-style: decimal; padding-left: 1.25rem; }
:deep(.ProseMirror blockquote) {
  border-left: 3px solid #cbd5e1;
  padding-left: 0.75rem;
  opacity: 0.8;
}
</style>
