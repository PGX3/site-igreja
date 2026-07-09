<template>
  <NodeViewWrapper as="div" class="ivw" :class="{ sel: selected }" :style="wrapStyle">
    <!-- Controles de alinhamento (aparecem ao selecionar) -->
    <div v-if="editor.isEditable && selected" class="bar" contenteditable="false">
      <button type="button" :class="{ on: node.attrs.align === 'left' }" @mousedown.prevent="setAlign('left')" title="Texto à direita">◧</button>
      <button type="button" :class="{ on: node.attrs.align === 'center' }" @mousedown.prevent="setAlign('center')" title="Centralizar">▬</button>
      <button type="button" :class="{ on: node.attrs.align === 'right' }" @mousedown.prevent="setAlign('right')" title="Texto à esquerda">◨</button>
      <span class="sep"></span>
      <button type="button" :class="{ on: !node.attrs.align }" @mousedown.prevent="setAlign(null)" title="Largura total (sem texto ao lado)">▭</button>
    </div>

    <img ref="imgEl" :src="node.attrs.src" :alt="node.attrs.alt || ''" :style="imgStyle" draggable="false" />
    <span v-if="editor.isEditable" class="hdl" @mousedown="iniciar" title="Arraste para redimensionar"></span>
  </NodeViewWrapper>
</template>

<script setup>
import { computed, ref } from 'vue'
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3'

const props = defineProps(nodeViewProps)

const imgEl = ref(null)

const wrapStyle = computed(() => {
  const a = props.node.attrs.align
  if (a === 'left') return { float: 'left', margin: '0 1rem 0.5rem 0' }
  if (a === 'right') return { float: 'right', margin: '0 0 0.5rem 1rem' }
  if (a === 'center') return { display: 'block' }
  return {}
})

const imgStyle = computed(() => {
  const s = { width: props.node.attrs.width || 'auto' }
  if (props.node.attrs.align === 'center') {
    s.marginLeft = 'auto'
    s.marginRight = 'auto'
  }
  return s
})

function setAlign(align) {
  props.updateAttributes({ align })
}

function iniciar(e) {
  e.preventDefault()
  const startX = e.clientX
  const startW = imgEl.value.offsetWidth
  const max = imgEl.value.closest('.ProseMirror')?.offsetWidth || 9999

  const move = (ev) => {
    let w = Math.round(startW + (ev.clientX - startX))
    w = Math.max(60, Math.min(w, max))
    props.updateAttributes({ width: w + 'px' })
  }
  const up = () => {
    window.removeEventListener('mousemove', move)
    window.removeEventListener('mouseup', up)
  }
  window.addEventListener('mousemove', move)
  window.addEventListener('mouseup', up)
}
</script>

<style scoped>
.ivw {
  position: relative;
  display: inline-block;
  max-width: 100%;
  line-height: 0;
  margin: 0.5rem 0;
}
.ivw img {
  max-width: 100%;
  height: auto;
  border-radius: 0.5rem;
  display: block;
}
.ivw.sel img { outline: 2px solid #2563eb; }

.hdl {
  position: absolute;
  right: -6px;
  bottom: -6px;
  width: 14px;
  height: 14px;
  background: #2563eb;
  border: 2px solid #fff;
  border-radius: 3px;
  cursor: nwse-resize;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
  opacity: 0;
  transition: opacity 0.15s;
}
.ivw:hover .hdl,
.ivw.sel .hdl { opacity: 1; }

.bar {
  position: absolute;
  top: 6px;
  left: 6px;
  z-index: 10;
  display: flex;
  align-items: center;
  gap: 2px;
  background: rgba(15, 23, 42, 0.9);
  border-radius: 8px;
  padding: 3px;
  line-height: 1;
}
.bar button {
  width: 26px;
  height: 26px;
  border: 0;
  background: transparent;
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.bar button:hover { background: rgba(255, 255, 255, 0.15); }
.bar button.on { background: #2563eb; }
.bar .sep { width: 1px; height: 16px; background: rgba(255, 255, 255, 0.25); margin: 0 2px; }
</style>
