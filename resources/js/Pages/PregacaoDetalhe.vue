<template>
  <Head>
    <title>{{ pregacao.titulo }}</title>
    <meta name="description" :content="`${pregacao.titulo}${pregacao.pregador ? ' — ' + pregacao.pregador : ''}. ${pregacao.data_fmt}. ${pregacao.descricao || 'Assista a esta pregação.'}`" />
    <meta property="og:title" :content="`${pregacao.titulo} · Igreja em Charqueadas`" />
    <meta property="og:description" :content="`${pregacao.pregador ? pregacao.pregador + ' · ' : ''}${pregacao.data_fmt}. ${pregacao.descricao || 'Assista a esta pregação.'}`" />
  </Head>
  <MainLayout>
    <div>

      <!-- ── HERO ── -->
      <section class="relative px-6 sm:px-10 md:px-20 pt-40 pb-16 overflow-hidden border-b border-[var(--blue)]/10">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse at 70% 30%, rgba(0,167,255,0.07) 0%, transparent 55%)"></div>

        <div class="relative z-10 w-full max-w-5xl">
          <Link href="/pregacoes"
                class="inline-flex items-center gap-2 text-[10px] font-bold tracking-[0.3em] uppercase
                       text-white/25 hover:text-[var(--blue)] transition-colors mb-10">
            ← Pregações
          </Link>

          <p class="section-label">Pregação</p>
          <h1 class="section-title text-[clamp(38px,6vw,80px)] text-white leading-[0.95]">
            {{ pregacao.titulo }}<span class="text-[var(--blue)]">.</span>
          </h1>

          <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-2">
            <span v-if="pregacao.pregador" class="flex items-center gap-2.5 text-[11px] tracking-[0.25em] uppercase text-white/40">
              <span class="w-1 h-1 rounded-full bg-[var(--blue)]"></span>
              {{ pregacao.pregador }}
            </span>
            <span class="flex items-center gap-2.5 text-[11px] tracking-[0.25em] uppercase text-white/40 capitalize">
              <span class="w-1 h-1 rounded-full bg-[var(--blue)]"></span>
              {{ pregacao.data_fmt }}
            </span>
            <span v-if="pregacao.versiculo" class="flex items-center gap-2.5 text-[11px] tracking-[0.25em] uppercase text-white/40">
              <span class="w-1 h-1 rounded-full bg-[var(--blue)]"></span>
              {{ pregacao.versiculo }}
            </span>
          </div>
        </div>
      </section>

      <!-- ── PLAYER ── -->
      <section class="px-6 sm:px-10 md:px-20 py-16 border-b border-[var(--blue)]/10">
        <div class="max-w-5xl">
          <MediaEmbed :url="pregacao.youtube_url" />
        </div>

        <!-- Descrição -->
        <div v-if="pregacao.descricao" class="max-w-5xl mt-12">
          <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/50 mb-6">Sobre esta mensagem</p>
          <p class="text-white/50 text-[15px] leading-[1.8] max-w-2xl whitespace-pre-line">{{ pregacao.descricao }}</p>
        </div>
      </section>

      <!-- ── CTA ── -->
      <section class="px-6 sm:px-10 md:px-20 py-24 text-center relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(0,167,255,0.04) 0%, transparent 60%)"></div>
        <div class="relative z-10">
          <p class="section-label inline-block">Continue ouvindo</p>
          <h2 class="section-title text-[clamp(38px,6vw,90px)] text-white mt-4">
            Mais pregações<span class="text-[var(--blue)]">.</span>
          </h2>
          <div class="mt-10 flex items-center justify-center gap-5 flex-wrap">
            <Link href="/pregacoes" class="btn-primary">Ver todas →</Link>
            <a href="/#sugestoes"
               class="text-[11px] font-bold tracking-[0.25em] uppercase
                      text-white/30 hover:text-[var(--blue)] transition-colors">
              Fale Conosco
            </a>
          </div>
        </div>
      </section>

    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  pregacao: Object,
  meta: Object,
})
</script>
