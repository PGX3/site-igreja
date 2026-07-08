<template>
  <Head>
    <title>{{ aula.titulo }}</title>
  </Head>
  <div class="min-h-screen bg-gray-50 text-gray-900">
    <header class="border-b border-gray-200 bg-white">
      <div class="max-w-3xl mx-auto px-5 h-14 flex items-center">
        <span class="font-black text-sm tracking-[0.22em] uppercase text-gray-900 select-none">
          IGREJA<span class="text-blue-600">.</span>
        </span>
        <span class="ml-3 text-xs text-gray-400 uppercase tracking-widest">Cursos</span>
      </div>
    </header>

    <main class="max-w-3xl mx-auto px-5 py-10">
      <AulaLeitura :aula="aula" :curso-titulo="cursoTitulo" :voltar-url="voltarUrl" />
    </main>

    <footer class="max-w-3xl mx-auto px-5 py-10 text-center">
      <a href="/" class="text-sm text-gray-400 hover:text-blue-600 transition">Igreja em Charqueadas</a>
    </footer>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AulaLeitura from '@/Components/AulaLeitura.vue'

defineProps({
  aula: { type: Object, required: true },
  cursoTitulo: { type: String, default: '' },
  voltarUrl: { type: String, default: null },
  meta: Object,
})
</script>
