<template>
  <MainLayout>
    <section class="min-h-[calc(100vh-200px)] flex items-center justify-center px-6 md:px-16 py-24">
      <div class="max-w-xl text-center">

        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-[var(--blue)]/10 border border-[var(--blue)]/30 mb-8">
          <svg class="w-10 h-10 text-[var(--blue)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)] mb-3">
          Recebido
        </p>
        <h1 style="font-family:'Barlow Condensed',sans-serif"
            class="text-5xl md:text-6xl font-black tracking-tight leading-none mb-6">
          Obrigado por se<br />cadastrar.
        </h1>
        <p class="text-white/50 text-sm leading-relaxed mb-10">
          Seus dados foram enviados para a equipe pastoral. Em breve entraremos em contato.
          Que Deus abençoe sua caminhada conosco.
        </p>

        <Link href="/"
              class="inline-block text-[10px] font-bold tracking-[0.3em] uppercase text-white/40
                     hover:text-[var(--blue)] transition-colors">
          ← Voltar ao site
        </Link>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { Link } from '@inertiajs/vue3'
</script>
