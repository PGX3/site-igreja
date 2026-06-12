<template>
  <Head>
    <title>{{ culto.nome }}</title>
    <meta name="description" :content="`${culto.nome} — toda ${culto.dia_semana.toLowerCase()} às ${culto.horario} na Igreja em Charqueadas. ${culto.descricao || 'Você é bem-vindo.'}`" />
    <meta property="og:title" :content="`${culto.nome} · Igreja em Charqueadas`" />
    <meta property="og:description" :content="`Toda ${culto.dia_semana.toLowerCase()} às ${culto.horario}. ${culto.descricao || 'Você é bem-vindo.'}`" />
  </Head>
  <MainLayout>
    <div>

      <!-- ── HERO ── -->
      <section class="relative min-h-[60vh] flex items-end pb-20 px-10 md:px-20 pt-40 overflow-hidden border-b border-[var(--blue)]/10">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse at 70% 30%, rgba(0,167,255,0.07) 0%, transparent 55%)"></div>

        <div class="absolute left-6 top-1/4 bottom-1/4 w-px bg-gradient-to-b from-transparent via-[var(--blue)]/20 to-transparent hidden md:block"></div>

        <div class="relative z-10 w-full max-w-5xl">
          <Link href="/#agenda"
                class="inline-flex items-center gap-2 text-[10px] font-bold tracking-[0.3em] uppercase
                       text-white/25 hover:text-[var(--blue)] transition-colors mb-10">
            ← Agenda
          </Link>

          <div class="flex flex-col md:flex-row md:items-end gap-10">
            <!-- Nome -->
            <div class="flex-1">
              <p class="section-label">Culto</p>
              <h1 class="section-title text-[clamp(52px,9vw,120px)] text-white leading-[0.88]">
                {{ culto.nome }}<span class="text-[var(--blue)]">.</span>
              </h1>
            </div>

            <!-- Data destaque -->
            <div class="flex-shrink-0 bg-white/5 border border-[var(--blue)]/20 rounded-2xl px-8 py-6 text-center min-w-[160px]">
              <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/70 mb-1">
                {{ culto.dia_semana }}
              </p>
              <p class="text-7xl font-black text-white leading-none">{{ culto.dia }}</p>
              <p class="text-[11px] font-bold tracking-[0.2em] uppercase text-[var(--blue)]/70 mt-1">
                {{ culto.mes }} · {{ culto.ano }}
              </p>
              <div class="mt-4 pt-4 border-t border-white/10">
                <p class="text-[10px] uppercase tracking-widest text-white/30 mb-0.5">Horário</p>
                <p class="text-2xl font-black text-white font-mono">{{ culto.horario }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── DETALHES ── -->
      <section class="px-10 md:px-20 py-24 border-b border-[var(--blue)]/10">
        <div class="max-w-5xl grid md:grid-cols-3 gap-px bg-[var(--blue)]/10">

          <div class="bg-[#0a0a0a] p-10 group hover:bg-[#0d0d0d] transition-colors">
            <div class="absolute bottom-0 left-0 h-px w-0 bg-[var(--blue)] group-hover:w-full transition-all duration-700"></div>
            <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/50 mb-6">Próxima data</p>
            <p class="text-white/80 text-[15px] leading-relaxed capitalize">{{ culto.data_fmt }}</p>
          </div>

          <div class="bg-[#0a0a0a] p-10 group hover:bg-[#0d0d0d] transition-colors">
            <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/50 mb-6">Recorrência</p>
            <p class="text-white/80 text-[15px]">Toda {{ culto.dia_semana.toLowerCase() }}</p>
            <p class="text-white/30 text-[13px] mt-1 font-mono">{{ culto.horario }}</p>
          </div>

          <div class="bg-[#0a0a0a] p-10 group hover:bg-[#0d0d0d] transition-colors">
            <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/50 mb-6">Local</p>
            <p class="text-white/80 text-[15px]">Igreja em Charqueadas</p>
            <p class="text-white/30 text-[13px] mt-1">Rio Grande do Sul · Brasil</p>
          </div>

        </div>

        <!-- Descrição -->
        <div v-if="culto.descricao" class="max-w-5xl mt-px bg-[var(--blue)]/10">
          <div class="bg-[#0a0a0a] p-10">
            <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/50 mb-6">Sobre este culto</p>
            <p class="text-white/50 text-[15px] leading-[1.8] max-w-2xl">{{ culto.descricao }}</p>
          </div>
        </div>
      </section>

      <!-- ── CTA ── -->
      <section class="px-10 md:px-20 py-32 text-center relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(0,167,255,0.04) 0%, transparent 60%)"></div>
        <div class="relative z-10">
          <p class="section-label inline-block">Venha nos visitar</p>
          <h2 class="section-title text-[clamp(42px,7vw,100px)] text-white mt-4">
            Você é bem<span class="text-[var(--blue)]">-</span>vindo<span class="text-[var(--blue)]">.</span>
          </h2>
          <p class="mt-8 text-[11px] tracking-[0.3em] uppercase text-white/20 mb-12">
            Charqueadas · Rio Grande do Sul
          </p>
          <div class="flex items-center justify-center gap-5 flex-wrap">
            <a href="/#sugestoes" class="btn-primary">Fale Conosco →</a>
            <Link href="/#agenda"
                  class="text-[11px] font-bold tracking-[0.25em] uppercase
                         text-white/30 hover:text-[var(--blue)] transition-colors">
              ← Ver toda a agenda
            </Link>
          </div>
        </div>
      </section>

    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  culto: Object,
})
</script>
