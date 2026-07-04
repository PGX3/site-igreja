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
    </div>

    <!-- EDITOR -->
    <EditorContent :editor="editor"
                   class="px-4 py-3 min-h-[220px] text-sm leading-relaxed
                          bg-white dark:bg-slate-700 text-gray-900 dark:text-white" />
  </div>
</template>

<script setup>
import { watch, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import { TextStyle, Color } from '@tiptap/extension-text-style'

const props = defineProps({
  modelValue: { type: String, default: '' },
  temErro:    { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

const cores = ['#000000', '#dc2626', '#2563eb', '#16a34a', '#9333ea', '#ea580c']

const editor = useEditor({
  content: props.modelValue,
  extensions: [StarterKit, TextStyle, Color],
  onUpdate: ({ editor }) => {
    const html = editor.getHTML()
    emit('update:modelValue', html === '<p></p>' ? '' : html)
  },
})

function btnClass(active) {
  return [
    'w-7 h-7 flex items-center justify-center rounded text-gray-600 dark:text-slate-300 transition',
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
</style>
