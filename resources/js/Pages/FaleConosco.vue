<template>
  <MainLayout>
    <section class="relative min-h-[90vh] px-6 sm:px-10 md:px-20 pt-32 sm:pt-40 pb-24 overflow-hidden">
      <div class="absolute inset-0 pointer-events-none"
           style="background: radial-gradient(ellipse at 70% 20%, rgba(0,167,255,0.07) 0%, transparent 55%)"></div>

      <div class="relative z-10 max-w-4xl">
        <p class="section-label">Fale com a gente</p>
        <h1 class="section-title text-[clamp(40px,9vw,96px)] text-white uppercase leading-[0.92]">
          Com quem você quer conversar<span class="text-[var(--blue)]">?</span>
        </h1>
        <p class="mt-6 sm:mt-8 italic text-[19px] sm:text-[24px] text-white/50 leading-[1.4] max-w-xl"
           style="font-family:'Cormorant Garamond',serif; font-weight:400">
          Escolha um dos nossos pastores e mande uma mensagem. Pode ser uma dúvida,
          um pedido de oração ou só vontade de conversar antes de vir.
        </p>

        <div v-if="pastores.length" class="mt-12 sm:mt-16 grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-[var(--blue)]/10">
          <a v-for="p in pastores" :key="p.nome" :href="p.link" target="_blank" rel="noopener"
             class="group bg-[#0a0a0a] p-8 sm:p-10 flex flex-col items-start gap-6 transition-colors duration-500 hover:bg-[#0f0f0f] relative overflow-hidden">
            <div class="absolute bottom-0 left-0 h-px w-0 bg-gradient-to-r from-[var(--blue)] to-transparent group-hover:w-full transition-all duration-700"></div>

            <img v-if="p.foto" :src="p.foto" :alt="p.nome"
                 class="w-24 h-24 rounded-full object-cover ring-2 ring-[var(--blue)]/40" />
            <div v-else
                 class="w-24 h-24 rounded-full flex items-center justify-center ring-2 ring-[var(--blue)]/40 text-[var(--blue)] text-3xl font-black">
              {{ p.iniciais }}
            </div>

            <div>
              <p class="text-[10px] tracking-[0.3em] uppercase text-[var(--blue)]/70"
                 style="font-family:'Barlow Condensed',sans-serif">{{ p.papel }}</p>
              <p class="mt-1 text-[28px] sm:text-[32px] font-black text-white leading-tight">{{ p.nome }}</p>
            </div>

            <span class="btn-primary text-center w-full">→ Chamar no WhatsApp</span>
          </a>
        </div>

        <div v-else class="mt-12">
          <a href="https://wa.me/5551992824071" target="_blank" rel="noopener" class="btn-primary">→ Falar no WhatsApp</a>
        </div>

        <div class="mt-12 sm:mt-16 flex flex-col sm:flex-row gap-3 sm:gap-4">
          <Link href="/primeira-vez" class="btn-ghost text-center">→ Primeira vez? Veja o que esperar</Link>
          <Link href="/#oracao" class="btn-ghost text-center">→ Prefiro deixar um pedido de oração</Link>
        </div>

        <p class="mt-12 text-[10px] sm:text-[11px] tracking-[0.3em] uppercase text-white/25">
          Rua Rui Barbosa, 1433 · Centro · Charqueadas / RS
        </p>
      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  pastores: { type: Array, default: () => [] },
  meta: Object,
})
</script>
