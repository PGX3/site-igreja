<template>
  <div class="conteudo-rico" v-html="html"></div>
</template>

<script setup>
defineProps({
  html: { type: String, default: '' },
})
</script>

<style scoped>
/* Renderização do HTML gerado pelo editor (TipTap). Mantém legível e responsivo. */
.conteudo-rico {
  font-size: 15px;
  line-height: 1.8;
  word-break: break-word;
}
.conteudo-rico :deep(p) { margin: 0 0 0.9rem; }
.conteudo-rico :deep(p:last-child) { margin-bottom: 0; }
.conteudo-rico :deep(h2) {
  font-size: 1.4rem;
  font-weight: 700;
  margin: 1.6rem 0 0.6rem;
  line-height: 1.3;
}
.conteudo-rico :deep(h1) {
  font-size: 1.7rem;
  font-weight: 800;
  margin: 1.6rem 0 0.6rem;
}
.conteudo-rico :deep(ul) { list-style: disc; padding-left: 1.4rem; margin: 0 0 0.9rem; }
.conteudo-rico :deep(ol) { list-style: decimal; padding-left: 1.4rem; margin: 0 0 0.9rem; }
.conteudo-rico :deep(li) { margin: 0.15rem 0; }
.conteudo-rico :deep(blockquote) {
  border-left: 3px solid currentColor;
  opacity: 0.85;
  padding-left: 1rem;
  margin: 0 0 0.9rem;
  font-style: italic;
}
.conteudo-rico :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 0.75rem;
  margin: 1rem 0;
  display: block;
}
.conteudo-rico :deep(a) {
  color: #2563eb;
  text-decoration: underline;
}
</style>
