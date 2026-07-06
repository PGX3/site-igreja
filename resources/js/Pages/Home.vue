<template>
  <MainLayout>
 <div id="app">
    <!-- ══ HERO ══════════════════════════════════════════════════════════ -->
    <section id="hero" class="relative min-h-screen flex flex-col justify-center px-6 sm:px-10 md:px-20 pt-32 md:pt-0 pb-24 md:py-24 overflow-hidden">
      <!-- Radial blue gradient -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                  w-[110vw] md:w-[700px] h-[110vw] md:h-[700px] max-w-[700px] max-h-[700px]
                  rounded-full pointer-events-none"
           style="background: radial-gradient(circle, rgba(0,167,255,0.06) 5%, transparent 70%)"></div>

      <!-- Padrão de pontos sutil -->
      <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
           style="background-image: radial-gradient(circle, rgba(0,167,255,0.7) 0.8px, transparent 0.8px); background-size: 28px 28px"></div>

      <!-- Logo desktop -->
      <div class="hidden md:block absolute left-[55%] top-1/2 -translate-y-1/2
                  pointer-events-none select-none opacity-50 z-0">
        <LogoIcon class="w-[500px] h-[500px]" />
      </div>

      <!-- Linha vertical esquerda -->
      <div class="absolute left-3 sm:left-6 top-28 bottom-28 w-px bg-gradient-to-b from-transparent via-[var(--blue)]/30 to-transparent"></div>

      <!-- Marcador editorial topo (somente desktop) -->
      <div class="hidden md:flex absolute md:left-20 md:top-32 items-center gap-3 z-10 animate-fade-up"
           style="animation-delay:0.05s">
        <span class="text-[var(--blue)] text-[10px] tracking-[0.35em] font-bold"
              style="font-family:'Barlow Condensed',sans-serif">Nº 01</span>
        <span class="w-12 h-px bg-[var(--blue)]/40"></span>
        <span class="text-white/30 text-[9px] tracking-[0.3em] uppercase"
              style="font-family:'Barlow Condensed',sans-serif">Introdução</span>
      </div>

      <!-- Tag vertical à direita (desktop only) -->
      <div class="hidden md:block absolute right-6 top-1/2 -translate-y-1/2 z-10 pointer-events-none select-none">
        <p class="text-[9px] tracking-[0.55em] uppercase text-white/20 font-bold"
           style="font-family:'Barlow Condensed',sans-serif; writing-mode: vertical-rl">
          Fé · Comunidade · Cruz · {{ new Date().getFullYear() }}
        </p>
      </div>

      <!-- Conteúdo principal -->
      <div class="relative z-10 max-w-5xl w-full">
        <div class="flex items-center gap-4 mb-6 sm:mb-8 animate-fade-up" style="animation-delay:0.1s">
          <div class="w-6 sm:w-8 h-px bg-[var(--blue)]"></div>
          <p class="section-label !mb-0 !text-[9px] sm:!text-[10px]">Igreja em Charqueadas · {{ new Date().getFullYear() }}</p>
        </div>

        <h1 class="section-title text-[clamp(56px,16vw,160px)] animate-fade-up text-white uppercase"
            style="animation-delay:0.25s; line-height:0.88">
          {{ t('hero_titulo', 'Igreja.') }}
        </h1>

        <!-- Subtítulo editorial -->
        <p class="mt-6 sm:mt-8 italic text-[20px] sm:text-[26px] md:text-[32px] text-white/50 leading-[1.3] max-w-md animate-fade-up"
           style="font-family:'Cormorant Garamond',serif; font-weight:400; animation-delay:0.4s">
          {{ t('hero_lead', 'Uma igreja simples, vivendo o que Cristo ensinou.') }}
        </p>

        <p class="mt-8 sm:mt-10 text-[10px] sm:text-[11px] tracking-[0.3em] uppercase text-white/30 animate-fade-up"
           style="animation-delay:0.55s">
          {{ t('hero_subtitulo', 'Charqueadas · Rio Grande do Sul · Brasil') }}
        </p>

        <div class="mt-10 sm:mt-14 flex flex-col sm:flex-row gap-3 sm:gap-4 animate-fade-up" style="animation-delay:0.7s">
          <a href="#sobre" class="btn-primary text-center">Conheça a Igreja</a>
          <a href="#agenda" class="btn-ghost text-center">Ver Agenda</a>
        </div>
      </div>

      <!-- Cue de scroll inferior -->
      <div class="absolute bottom-8 md:bottom-12 left-6 sm:left-10 md:left-20 flex items-center gap-3 z-10 animate-fade-up"
           style="animation-delay:0.9s">
        <span class="text-[9px] tracking-[0.4em] uppercase text-white/30 font-bold"
              style="font-family:'Barlow Condensed',sans-serif">↓ Role para conhecer</span>
        <div class="w-12 sm:w-16 h-px bg-gradient-to-r from-[var(--blue)]/60 to-transparent animate-pulse-blue"></div>
      </div>

      <!-- Linha pulsante direita (desktop) -->
      <div class="hidden md:flex absolute bottom-12 right-10 flex-col items-center gap-3">
        <div class="w-px h-16 bg-gradient-to-b from-[var(--blue)]/60 to-transparent animate-pulse-blue"></div>
      </div>
    </section>

    <!-- ══ SOBRE ══════════════════════════════════════════════════════════ -->
    <section id="sobre" class="px-6 sm:px-10 md:px-20 py-24 md:py-32 border-t border-[var(--blue)]/10">
      <div class="grid md:grid-cols-2 gap-24 items-center">
        <div class="reveal">
          <p class="section-label">A Igreja</p>
          <h2 class="section-title text-[clamp(38px,5vw,72px)] text-white">
            {{ t('sobre_titulo', 'Uma comunidade que sabe o que é.') }}
          </h2>
          <div class="blue-line"></div>
          <p class="text-white/45 text-[15px] leading-[1.85] max-w-md">
            {{ t('sobre_texto', 'Uma comunidade evangélica em Charqueadas, conduzida em torno do evangelho de Jesus Cristo. Vivendo o que ele ensinou, sem rodeios e sem pirotecnia.') }}
          </p>
        </div>
        <div class="reveal hidden md:flex justify-center items-center relative h-80">
          <LogoIcon :size="330" />
        </div>
      </div>
    </section>

    <!-- ══ IDENTIDADE ═══════════════════════════════════════════════════ -->
    <section id="identidade" class="px-6 sm:px-10 md:px-20 py-24 md:py-32 border-t border-[var(--blue)]/10">
      <div class="reveal flex flex-col md:flex-row md:justify-between md:items-end mb-20">
        <div>
          <p class="section-label">Identidade</p>
          <h2 class="section-title text-[clamp(38px,5vw,72px)] text-white">
            O que nos define<span class="text-[var(--blue)]">.</span>
          </h2>
        </div>
        <p class="text-white/30 text-[13px] leading-relaxed max-w-xs mt-6 md:mt-0 italic"
           style="font-family:'Cormorant Garamond',serif">
          Três pilares que constroem quem somos como comunidade em Charqueadas.
        </p>
      </div>
      <div class="grid md:grid-cols-3 gap-px bg-[var(--blue)]/10">
        <div v-for="(card, i) in identCards" :key="i"
             class="reveal bg-[#0a0a0a] p-12 relative overflow-hidden group transition-colors duration-500 hover:bg-[#0f0f0f]"
             :style="`animation-delay:${i*0.12}s`">
          <div class="absolute bottom-0 left-0 h-px w-0 bg-gradient-to-r from-[var(--blue)] to-transparent
                      group-hover:w-full transition-all duration-700"></div>
          <p class="text-[10px] font-bold tracking-[0.3em] text-[var(--blue)]/60 mb-10"
             style="font-family:'Barlow Condensed',sans-serif">0{{ i+1 }}</p>
          <h3 class="text-2xl font-bold tracking-tight mb-5 text-white"
              style="font-family:'Cormorant Garamond',serif">{{ card.title }}</h3>
          <p class="text-white/35 text-[13px] leading-relaxed">{{ card.body }}</p>
        </div>
      </div>
    </section>

    <!-- ══ PORTAS ABERTAS ═══════════════════════════════════════════════ -->
    <section id="portas" class="relative px-6 sm:px-10 md:px-20 py-32 border-t border-[var(--blue)]/10 overflow-hidden">
      <!-- Atmosférico -->
      <div class="absolute inset-0 pointer-events-none"
           style="background: radial-gradient(ellipse 500px 400px at 80% 50%, rgba(0,167,255,0.04) 0%, transparent 60%)"></div>

      <div class="relative grid md:grid-cols-12 gap-10 md:gap-16 items-start">
        <div class="md:col-span-4 reveal">
          <div class="flex items-center gap-4 mb-5">
            <div class="w-8 h-px bg-[var(--blue)]"></div>
            <p class="section-label !mb-0">As portas</p>
          </div>
          <h2 class="section-title text-[clamp(38px,5vw,68px)] text-white">
            Estão <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:500" class="text-white/85">abertas</span><span class="text-[var(--blue)]">.</span>
          </h2>
          <div class="blue-line"></div>
          <p class="text-white/40 text-[14px] leading-[1.85] max-w-sm">
            Não importa de onde você vem, nem o que carrega. Não precisa estar pronto. Não precisa entender tudo. Vem.
          </p>
        </div>

        <div class="md:col-span-8 reveal">
          <div v-for="(linha, i) in portasLinhas" :key="i"
               class="flex items-baseline gap-4 sm:gap-6 py-5 sm:py-6 border-b border-white/[0.06] group">
            <span class="text-[10px] tracking-[0.35em] text-[var(--blue)]/40 font-bold w-6 sm:w-8 shrink-0
                         group-hover:text-[var(--blue)] transition-colors"
                  style="font-family:'Barlow Condensed',sans-serif">
              {{ String(i + 1).padStart(2, '0') }}
            </span>
            <p class="text-[clamp(22px,3.5vw,38px)] italic text-white/70 leading-[1.2] flex-1
                      group-hover:text-white/95 transition-colors"
               style="font-family:'Cormorant Garamond',serif;font-weight:400">
              {{ linha }}
            </p>
          </div>

          <p class="mt-10 sm:mt-14 text-[clamp(34px,7vw,76px)] font-black text-white uppercase leading-[0.95] tracking-tight">
            É para <span class="text-[var(--blue)]">todo mundo</span><span class="text-[var(--blue)]">.</span>
          </p>
        </div>
      </div>
    </section>

    <!-- ══ AGENDA ════════════════════════════════════════════════════════ -->
    <section id="agenda" class="relative px-6 sm:px-10 md:px-20 py-24 md:py-32 border-t border-[var(--blue)]/10 overflow-hidden">
      <!-- Marca d'água vertical -->
      <div class="hidden lg:block absolute -right-4 top-1/2 -translate-y-1/2 origin-right
                  pointer-events-none select-none opacity-[0.035] whitespace-nowrap"
           style="transform: rotate(-90deg) translateY(-50%); transform-origin: right top;">
        <span class="text-[9rem] font-black uppercase tracking-[0.4em] text-white"
              style="font-family:'Barlow Condensed',sans-serif">
          Semanalmente · Semanalmente · Semanalmente
        </span>
      </div>

      <div class="reveal mb-20 flex flex-col md:flex-row md:items-end md:justify-between gap-8 relative">
        <div>
          <div class="flex items-center gap-4 mb-5">
            <div class="w-8 h-px bg-[var(--blue)]"></div>
            <p class="section-label !mb-0">Agenda · Toda Semana</p>
          </div>
          <h2 class="section-title text-[clamp(38px,5vw,72px)] text-white">
            O <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:500" class="text-white/85">ritmo</span> da casa<span class="text-[var(--blue)]">.</span>
          </h2>
        </div>
        <div class="text-right hidden md:block">
          <p class="text-[10px] tracking-[0.3em] uppercase text-white/30 max-w-[14rem] ml-auto leading-relaxed">
            Três encontros semanais. <br>Mesma fé, mesma casa.
          </p>
        </div>
      </div>

      <div class="relative">
        <Link v-for="(culto, i) in cultos" :key="culto.id"
              :href="`/cultos/${culto.id}`"
              class="reveal group grid grid-cols-12 items-center gap-3 md:gap-8 py-8 md:py-10
                     border-t border-white/[0.06] hover:border-[var(--blue)]/40
                     transition-all duration-500 cursor-pointer relative">
          <!-- Hover line slide -->
          <span class="absolute left-0 top-0 h-px w-0 bg-gradient-to-r from-[var(--blue)] to-transparent
                       group-hover:w-full transition-all duration-700 ease-out"></span>

          <!-- Index -->
          <span class="col-span-1 text-[10px] font-bold tracking-[0.35em] text-[var(--blue)]/40
                       group-hover:text-[var(--blue)] transition-colors"
                style="font-family:'Barlow Condensed',sans-serif">
            {{ String(i + 1).padStart(2, '0') }}
          </span>

          <!-- Day of week (editorial) -->
          <span class="col-span-5 md:col-span-3 text-[clamp(26px,4.5vw,52px)] leading-none italic lowercase
                       text-white/80 group-hover:text-white transition-colors duration-500
                       group-hover:translate-x-2 transform"
                style="font-family:'Cormorant Garamond',serif;font-weight:500">
            {{ culto.dia_semana }}
          </span>

          <!-- Separator dot pattern -->
          <span class="hidden md:flex col-span-1 items-center justify-center gap-1.5">
            <span class="w-1 h-1 rounded-full bg-white/10 group-hover:bg-[var(--blue)]/60 transition-colors"></span>
            <span class="w-1 h-1 rounded-full bg-white/10 group-hover:bg-[var(--blue)]/40 transition-colors"></span>
            <span class="w-1 h-1 rounded-full bg-white/10 group-hover:bg-[var(--blue)]/20 transition-colors"></span>
          </span>

          <!-- Name + time stacked on mobile, inline on desktop -->
          <span class="col-span-5 md:col-span-5 text-[15px] md:text-xl font-bold tracking-tight
                       text-white/85 group-hover:text-white transition-colors">
            {{ culto.nome }}
          </span>

          <span class="hidden md:flex col-span-1 justify-end font-mono text-[12px] tracking-[0.15em]
                       text-white/40 group-hover:text-[var(--blue)] transition-colors">
            {{ culto.horario }}
          </span>

          <span class="col-span-1 flex justify-end text-[var(--blue)]/0 group-hover:text-[var(--blue)]
                       group-hover:translate-x-1 transition-all duration-300 text-sm">
            →
          </span>
        </Link>

        <!-- Bottom border for last item -->
        <div v-if="cultos?.length" class="border-t border-white/[0.06]"></div>

        <div v-if="!cultos || cultos.length === 0" class="py-12 text-white/25 text-sm tracking-wide text-center">
          Agenda em breve.
        </div>
      </div>
    </section>

    <!-- ══ EVENTOS ═══════════════════════════════════════════════════════ -->
    <section id="eventos" class="relative px-6 sm:px-10 md:px-20 py-24 md:py-32 border-t border-[var(--blue)]/10 overflow-hidden">
      <!-- Atmosférico -->
      <div class="absolute inset-0 pointer-events-none"
           style="background: radial-gradient(ellipse 600px 400px at 15% 30%, rgba(0,167,255,0.05) 0%, transparent 60%)"></div>

      <div class="relative">
        <div class="reveal mb-20 flex flex-col md:flex-row md:items-end md:justify-between gap-8">
          <div>
            <div class="flex items-center gap-4 mb-5">
              <div class="w-8 h-px bg-[var(--blue)]"></div>
              <p class="section-label !mb-0">Calendário · Próximos</p>
            </div>
            <h2 class="section-title text-[clamp(38px,5vw,72px)] text-white">
              O que <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:500" class="text-white/85">vem aí</span><span class="text-[var(--blue)]">.</span>
            </h2>
          </div>
          <div v-if="eventos?.length" class="hidden md:flex items-center gap-4">
            <p class="text-[10px] tracking-[0.3em] uppercase text-white/30">
              {{ eventos.length }} {{ eventos.length === 1 ? 'evento' : 'eventos' }} confirmado{{ eventos.length === 1 ? '' : 's' }}
            </p>
            <span class="w-2 h-2 rounded-full bg-[var(--blue)] animate-pulse-blue"></span>
          </div>
        </div>

        <!-- Featured: próximo evento -->
        <Link v-if="eventos?.length"
              :href="`/eventos/${eventos[0].id}`"
              class="reveal group relative block mb-16 border border-[var(--blue)]/15
                     hover:border-[var(--blue)]/50 transition-all duration-500 overflow-hidden">
          <!-- Próximo tag -->
          <span class="absolute top-6 right-6 z-20 text-[9px] font-bold tracking-[0.35em] uppercase
                       text-[var(--blue)] bg-[var(--blue)]/10 border border-[var(--blue)]/30 px-3 py-1.5
                       backdrop-blur-sm"
                style="font-family:'Barlow Condensed',sans-serif">
            ★ Próximo
          </span>

          <!-- Hover gradient -->
          <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"
               style="background: radial-gradient(ellipse at 30% 50%, rgba(0,167,255,0.06) 0%, transparent 60%)"></div>

          <div class="grid md:grid-cols-12 relative">
            <!-- Date block -->
            <div class="md:col-span-3 p-8 md:p-10 bg-[var(--blue)]/[0.04] md:border-r border-[var(--blue)]/10
                        group-hover:bg-[var(--blue)]/[0.07] transition-colors duration-500
                        flex md:flex-col items-baseline md:items-start justify-start gap-4 md:gap-3">
              <p class="text-[clamp(64px,8vw,112px)] font-black leading-[0.85] text-white
                        group-hover:text-[var(--blue)] transition-colors duration-500"
                 style="font-family:'Cormorant Garamond',serif;font-weight:600">
                {{ eventos[0].dia }}
              </p>
              <p class="text-[11px] font-bold tracking-[0.35em] uppercase text-[var(--blue)]"
                 style="font-family:'Barlow Condensed',sans-serif">
                {{ eventos[0].mes_curto }} · {{ new Date().getFullYear() }}
              </p>
            </div>

            <!-- Content -->
            <div class="md:col-span-9 p-8 md:p-12 flex flex-col justify-center">
              <h3 class="text-[clamp(28px,3.5vw,46px)] font-black leading-[1.05] text-white mb-6 tracking-tight">
                {{ eventos[0].nome }}
              </h3>

              <div v-if="eventos[0].horario || eventos[0].local"
                   class="flex flex-wrap items-center gap-6 mb-6">
                <span v-if="eventos[0].horario"
                      class="flex items-center gap-2.5 text-[11px] tracking-[0.25em] uppercase text-white/40">
                  <span class="w-1 h-1 rounded-full bg-[var(--blue)]"></span>
                  <span class="font-mono">{{ eventos[0].horario }}</span>
                </span>
                <span v-if="eventos[0].local"
                      class="flex items-center gap-2.5 text-[11px] tracking-[0.25em] uppercase text-white/40">
                  <span class="w-1 h-1 rounded-full bg-[var(--blue)]"></span>
                  {{ eventos[0].local }}
                </span>
              </div>

              <p v-if="eventos[0].descricao"
                 class="text-white/50 text-[15px] leading-[1.8] max-w-xl mb-10 line-clamp-3"
                 style="font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:400">
                "{{ eventos[0].descricao }}"
              </p>

              <div class="inline-flex items-center gap-3 text-[10px] tracking-[0.35em] uppercase font-bold text-[var(--blue)]"
                   style="font-family:'Barlow Condensed',sans-serif">
                <span>Ver Detalhes</span>
                <span class="w-8 h-px bg-[var(--blue)] group-hover:w-20 transition-all duration-500"></span>
                <span class="group-hover:translate-x-1 transition-transform">→</span>
              </div>
            </div>
          </div>
        </Link>

        <!-- Timeline: demais eventos -->
        <div v-if="eventos?.length > 1" class="reveal relative pl-8 md:pl-12">
          <!-- Linha vertical da timeline -->
          <div class="absolute left-1.5 md:left-2.5 top-3 bottom-3 w-px
                      bg-gradient-to-b from-[var(--blue)]/40 via-[var(--blue)]/15 to-transparent"></div>

          <p class="text-[9px] font-bold tracking-[0.35em] uppercase text-white/30 mb-8 -ml-8 md:-ml-12"
             style="font-family:'Barlow Condensed',sans-serif">
            E também<span class="text-[var(--blue)]">.</span>
          </p>

          <Link v-for="(evento, i) in eventos.slice(1)" :key="evento.id"
                :href="`/eventos/${evento.id}`"
                class="group relative grid grid-cols-12 items-center gap-4 md:gap-6 py-6
                       border-b border-white/[0.05] hover:border-[var(--blue)]/30 transition-all duration-300">
            <!-- Timeline dot -->
            <span class="absolute -left-[1.85rem] md:-left-[2.6rem] top-1/2 -translate-y-1/2 w-3 h-3 rounded-full
                         bg-[#0a0a0a] border border-[var(--blue)]/40
                         group-hover:border-[var(--blue)] group-hover:scale-125 transition-all duration-300">
              <span class="absolute inset-[3px] rounded-full bg-white/10
                           group-hover:bg-[var(--blue)] transition-colors duration-300"></span>
            </span>

            <!-- Date inline -->
            <div class="col-span-3 md:col-span-2 flex items-baseline gap-2">
              <span class="text-3xl md:text-4xl font-black text-white/85 group-hover:text-[var(--blue)]
                           transition-colors leading-none"
                    style="font-family:'Cormorant Garamond',serif;font-weight:600">{{ evento.dia }}</span>
              <span class="text-[10px] tracking-[0.3em] uppercase text-white/40 font-bold"
                    style="font-family:'Barlow Condensed',sans-serif">{{ evento.mes_curto }}</span>
            </div>

            <!-- Name + info -->
            <div class="col-span-7 md:col-span-8 min-w-0">
              <p class="text-base md:text-lg font-bold text-white/85 group-hover:text-white
                        transition-colors leading-tight truncate">
                {{ evento.nome }}
              </p>
              <p v-if="evento.horario || evento.local"
                 class="text-[11px] tracking-[0.15em] text-white/30 mt-1.5 font-mono truncate">
                <span v-if="evento.horario">{{ evento.horario }}</span>
                <span v-if="evento.horario && evento.local" class="mx-2 text-[var(--blue)]/40">·</span>
                <span v-if="evento.local">{{ evento.local }}</span>
              </p>
            </div>

            <!-- Arrow -->
            <span class="col-span-2 flex justify-end text-[var(--blue)]/0 group-hover:text-[var(--blue)]
                         group-hover:translate-x-1 transition-all duration-300 text-sm">→</span>
          </Link>
        </div>

        <div v-if="!eventos?.length" class="py-16 text-center">
          <div class="inline-flex flex-col items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-[var(--blue)]/20 flex items-center justify-center">
              <span class="w-1.5 h-1.5 rounded-full bg-[var(--blue)]/50 animate-pulse-blue"></span>
            </div>
            <p class="text-white/30 text-[11px] tracking-[0.3em] uppercase">
              Nenhum evento agendado no momento
            </p>
            <p class="text-white/20 text-[12px] italic max-w-xs"
               style="font-family:'Cormorant Garamond',serif">
              Mas todo domingo, quarta e sábado, a casa está aberta.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ══ PREGAÇÕES ═════════════════════════════════════════════════════ -->
    <section v-if="pregacoes?.length" id="pregacoes"
             class="relative px-6 sm:px-10 md:px-20 py-24 md:py-32 border-t border-[var(--blue)]/10 overflow-hidden">
      <!-- Atmosférico -->
      <div class="absolute inset-0 pointer-events-none"
           style="background: radial-gradient(ellipse 600px 400px at 85% 30%, rgba(0,167,255,0.05) 0%, transparent 60%)"></div>

      <div class="relative">
        <div class="reveal mb-16 flex flex-col md:flex-row md:items-end md:justify-between gap-8">
          <div>
            <div class="flex items-center gap-4 mb-5">
              <div class="w-8 h-px bg-[var(--blue)]"></div>
              <p class="section-label !mb-0">A Palavra · Pregações</p>
            </div>
            <h2 class="section-title text-[clamp(38px,5vw,72px)] text-white">
              Ouça a <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:500" class="text-white/85">mensagem</span><span class="text-[var(--blue)]">.</span>
            </h2>
          </div>
          <Link href="/pregacoes"
                class="inline-flex items-center gap-3 text-[10px] tracking-[0.35em] uppercase font-bold text-[var(--blue)] hover:gap-5 transition-all"
                style="font-family:'Barlow Condensed',sans-serif">
            <span>Ver todas</span>
            <span class="w-8 h-px bg-[var(--blue)]"></span>
            <span>→</span>
          </Link>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
          <Link v-for="pregacao in pregacoes" :key="pregacao.id"
                :href="`/pregacoes/${pregacao.id}`"
                class="reveal group block border border-[var(--blue)]/15 hover:border-[var(--blue)]/50
                       transition-all duration-500 overflow-hidden">
            <div class="relative aspect-video bg-black overflow-hidden">
              <img v-if="thumb(pregacao.youtube_url)" :src="thumb(pregacao.youtube_url)" :alt="pregacao.titulo"
                   class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105
                          transition-all duration-700" loading="lazy" />
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="w-14 h-14 rounded-full bg-black/50 border border-white/30 backdrop-blur-sm
                             flex items-center justify-center text-white
                             group-hover:bg-[var(--blue)] group-hover:border-[var(--blue)] transition-all duration-300">
                  <span class="ml-1 text-lg">▶</span>
                </span>
              </div>
            </div>
            <div class="p-6">
              <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/70 mb-3"
                 style="font-family:'Barlow Condensed',sans-serif">
                {{ formatarData(pregacao.data_pregacao) }}
              </p>
              <h3 class="text-lg font-bold text-white leading-snug group-hover:text-[var(--blue)] transition-colors line-clamp-2">
                {{ pregacao.titulo }}
              </h3>
              <p v-if="pregacao.pregador" class="text-white/40 text-[13px] mt-2">{{ pregacao.pregador }}</p>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- ══ SUGESTÕES ═════════════════════════════════════════════════════ -->
    <section id="sugestoes" class="px-6 sm:px-10 md:px-20 py-24 md:py-32 border-t border-[var(--blue)]/10">
      <div class="grid md:grid-cols-2 gap-20 items-start">
        <div class="reveal">
          <p class="section-label">Participe</p>
          <h2 class="section-title text-[clamp(38px,5vw,68px)] text-white">
            Sua voz importa<span class="text-[var(--blue)]">.</span>
          </h2>
          <div class="blue-line"></div>
          <p class="text-white/40 text-[14px] leading-relaxed max-w-sm">
            Tem uma ideia, sugestão ou algo que queira compartilhar com a liderança?
            Escreva abaixo — lemos tudo com atenção.
          </p>
        </div>
        <div class="reveal">
          <Transition name="fade">
            <div v-if="$page.props.flash?.sucesso_sugestao"
                 class="mb-6 px-5 py-4 border-l-2 border-[var(--blue)] bg-[var(--blue)]/5 text-[var(--blue)] text-[13px] tracking-wide">
              {{ $page.props.flash.sucesso_sugestao }}
            </div>
          </Transition>
          <div v-if="$page.props.errors?.mensagem"
               class="mb-6 px-5 py-4 border-l-2 border-red-500/60 bg-red-500/5 text-red-400 text-[13px]">
            {{ $page.props.errors.mensagem }}
          </div>
          <form @submit.prevent="enviarSugestao" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[9px] font-bold tracking-[0.3em] uppercase text-white/30 mb-2">Nome *</label>
                <input v-model="formSugestao.nome" type="text" required class="field" placeholder="Seu nome" />
                <p v-if="errosSugestao.nome" class="text-red-400 text-xs mt-1">{{ errosSugestao.nome }}</p>
              </div>
              <div>
                <label class="block text-[9px] font-bold tracking-[0.3em] uppercase text-white/30 mb-2">E-mail</label>
                <input v-model="formSugestao.email" type="email" class="field" placeholder="Opcional" />
                <p v-if="errosSugestao.email" class="text-red-400 text-xs mt-1">{{ errosSugestao.email }}</p>
              </div>
            </div>
            <div>
              <label class="block text-[9px] font-bold tracking-[0.3em] uppercase text-white/30 mb-2">Mensagem *</label>
              <textarea v-model="formSugestao.mensagem" required rows="5" class="field resize-none"
                        placeholder="Escreva sua sugestão..."></textarea>
              <p v-if="errosSugestao.mensagem" class="text-red-400 text-xs mt-1">{{ errosSugestao.mensagem }}</p>
            </div>

            <!-- hCaptcha -->
            <div>
              <HCaptcha ref="captchaSugestao" :sitekey="hcaptchaSitekey" @verify="tokenSugestao = $event" @expire="tokenSugestao = 'da1a0e90-27ce-4d62-a2aa-54a26506be18'" />
              <p v-if="errosSugestao.captcha" class="text-red-400 text-xs mt-1">{{ errosSugestao.captcha }}</p>
            </div>

            <div class="flex items-center justify-between pt-2">
              <p class="text-[10px] text-white/20 tracking-wide">* campos obrigatórios</p>
              <button type="submit" :disabled="loadingSugestao || !tokenSugestao"
                      class="btn-primary disabled:opacity-40 disabled:cursor-not-allowed">
                {{ loadingSugestao ? 'Enviando...' : '→ Enviar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ══ PEDIDOS DE ORAÇÃO ═════════════════════════════════════════════ -->
    <section id="oracao" class="px-6 sm:px-10 md:px-20 py-24 md:py-32 border-t border-[var(--blue)]/10">
      <div class="grid md:grid-cols-2 gap-20 items-start">
        <div class="reveal">
          <p class="section-label">Oração</p>
          <h2 class="section-title text-[clamp(38px,5vw,68px)] text-white">
            Podemos orar<br>por você<span class="text-[var(--blue)]">.</span>
          </h2>
          <div class="blue-line"></div>
          <p class="text-white/40 text-[14px] leading-relaxed max-w-sm">
            Compartilhe seu pedido de oração conosco. Nossa equipe de intercessores
            estará orando por você. Você pode se identificar ou enviar anonimamente.
          </p>
          <blockquote class="mt-10 pl-5 border-l border-[var(--blue)]/30">
            <p class="text-white/25 italic text-[15px] leading-relaxed"
               style="font-family:'Cormorant Garamond',serif">
              "Orai uns pelos outros, para que sejais curados."
            </p>
            <cite class="text-[10px] tracking-[0.2em] uppercase text-[var(--blue)]/50 mt-2 block">Tiago 5:16</cite>
          </blockquote>
        </div>
        <div class="reveal">
          <Transition name="fade">
            <div v-if="$page.props.flash?.sucesso_oracao"
                 class="mb-6 px-5 py-4 border-l-2 border-[var(--blue)] bg-[var(--blue)]/5 text-[var(--blue)] text-[13px] tracking-wide">
              {{ $page.props.flash.sucesso_oracao }}
            </div>
          </Transition>
          <div v-if="$page.props.errors?.pedido || $page.props.errors?.captcha_oracao"
               class="mb-6 px-5 py-4 border-l-2 border-red-500/60 bg-red-500/5 text-red-400 text-[13px]">
            {{ $page.props.errors.pedido || $page.props.errors.captcha_oracao }}
          </div>
          <form @submit.prevent="enviarPedido" class="space-y-4">
            <div v-if="!formOracao.anonimo">
              <label class="block text-[9px] font-bold tracking-[0.3em] uppercase text-white/30 mb-2">Nome *</label>
              <input v-model="formOracao.nome" type="text" :required="!formOracao.anonimo" class="field" placeholder="Seu nome" />
              <p v-if="errosOracao.nome" class="text-red-400 text-xs mt-1">{{ errosOracao.nome }}</p>
            </div>
            <div>
              <label class="block text-[9px] font-bold tracking-[0.3em] uppercase text-white/30 mb-2">Pedido de Oração *</label>
              <textarea v-model="formOracao.pedido" required rows="5" class="field resize-none"
                        placeholder="Compartilhe seu pedido..."></textarea>
              <p v-if="errosOracao.pedido" class="text-red-400 text-xs mt-1">{{ errosOracao.pedido }}</p>
            </div>
            <button type="button" @click="toggleAnonimo"
                    class="flex items-center gap-3 text-[13px] text-white/40 hover:text-white/70 transition-colors group">
              <span class="w-4 h-4 border flex items-center justify-center flex-shrink-0 transition-colors"
                    :class="formOracao.anonimo ? 'border-[var(--blue)] bg-[var(--blue)]/15' : 'border-white/20 group-hover:border-white/40'">
                <span v-if="formOracao.anonimo" class="text-[var(--blue)] text-[10px] font-bold">✓</span>
              </span>
              Enviar anonimamente
            </button>

            <button type="button" @click="formOracao.compartilhar = !formOracao.compartilhar"
                    class="flex items-start gap-3 text-[13px] text-white/40 hover:text-white/70 transition-colors group text-left">
              <span class="w-4 h-4 border flex items-center justify-center flex-shrink-0 mt-0.5 transition-colors"
                    :class="formOracao.compartilhar ? 'border-[var(--blue)] bg-[var(--blue)]/15' : 'border-white/20 group-hover:border-white/40'">
                <span v-if="formOracao.compartilhar" class="text-[var(--blue)] text-[10px] font-bold">✓</span>
              </span>
              Autorizo compartilhar meu pedido com a comunidade para oração
            </button>

            <!-- hCaptcha -->
            <div>
              <HCaptcha ref="captchaOracao" :sitekey="hcaptchaSitekey" @verify="tokenOracao = $event" @expire="tokenOracao = 'da1a0e90-27ce-4d62-a2aa-54a26506be18'" />
              <p v-if="errosOracao.captcha_oracao" class="text-red-400 text-xs mt-1">{{ errosOracao.captcha_oracao }}</p>
            </div>

            <div class="flex items-center justify-between pt-2">
              <p class="text-[10px] text-white/20 tracking-wide">* campos obrigatórios</p>
              <button type="submit" :disabled="loadingOracao || !tokenOracao"
                      class="btn-primary disabled:opacity-40 disabled:cursor-not-allowed">
                {{ loadingOracao ? 'Enviando...' : '→ Enviar Pedido' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- ══ CTA ═══════════════════════════════════════════════════════════ -->
    <section id="cta" class="px-6 sm:px-10 md:px-20 py-28 md:py-40 border-t border-[var(--blue)]/10 text-center relative overflow-hidden">
      <div class="absolute inset-0 pointer-events-none"
           style="background: radial-gradient(ellipse at center, rgba(0,167,255,0.04) 0%, transparent 70%)"></div>
      <p class="section-label inline-block">Venha nos visitar</p>
      <h2 class="reveal section-title text-[clamp(56px,9vw,130px)] text-white mt-4">
        {{ t('cta_titulo', 'Você é bem-vindo.') }}
      </h2>
      <p class="mt-8 mb-14 text-[11px] tracking-[0.3em] uppercase text-white/25">
        {{ t('endereco', 'Rua Rui Barbosa, 1433 · Charqueadas / RS') }}
      </p>
      <a href="https://www.google.com/maps/place/R.+Rui+Barbosa,+1433+-+Centro,+Charqueadas+-+RS,+96745-000" target="_blank" rel="noopener" class="btn-primary reveal">→ Como Chegar</a>
    </section>

    <WhatsAppPopup />
  </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import MainLayout from '@/Layouts/MainLayout.vue'
import LogoIcon from '@/Components/LogoIcon.vue'
import HCaptcha from '@/Components/HCaptcha.vue'
import WhatsAppPopup from '@/Components/WhatsAppPopup.vue';

const props = defineProps({ cultos: Array, eventos: Array, pregacoes: Array, textos: Object, meta: Object })
const page = usePage()

function youtubeId(url) {
  const m = (url || '').match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([\w-]{11})/i)
  return m ? m[1] : null
}
function thumb(url) {
  const id = youtubeId(url)
  return id ? `https://img.youtube.com/vi/${id}/hqdefault.jpg` : null
}
function formatarData(data) {
  if (!data) return ''
  const d = new Date(data)
  if (isNaN(d)) return ''
  return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric', timeZone: 'UTC' })
}

// hCaptcha sitekey — vem do backend via shared props ou usa a chave de teste
const hcaptchaSitekey = page.props.hcaptchaSitekey || 'da1a0e90-27ce-4d62-a2aa-54a26506be18'

function t(key, fallback = '') { return props.textos?.[key] || fallback }

const portasLinhas = [
  'Pra quem nunca foi.',
  'Pra quem tá voltando.',
  'Pra quem tá em dúvida.',
  'Pra quem chega cansado.',
]

const identCards = [
  { title: 'Cristo no centro',     body: 'Que Cristo seja central. Não somos o sujeito da história, ele é. Tudo o que fazemos parte e volta para ele.' },
  { title: 'Palavra a sério',      body: 'Que a Palavra seja levada a sério. Não como decoração, mas como autoridade, guia e alimento. O que ela diz, dizemos.' },
  { title: 'Cuidado com verdade',  body: 'Que as pessoas sejam cuidadas com verdade. Não acolhimento raso nem dureza fria, mas amor que cura e que diz o que precisa ser dito.' },
]

// ─── Sugestão ───────────────────────────────────────────────────────────────
const formSugestao    = ref({ nome: '', email: '', mensagem: '' })
const errosSugestao   = ref({})
const loadingSugestao = ref(false)
const tokenSugestao   = ref('')
const captchaSugestao = ref(null)

function enviarSugestao() {
  errosSugestao.value = {}
  loadingSugestao.value = true
  router.post('/sugestao', { ...formSugestao.value, 'h-captcha-response': tokenSugestao.value }, {
    onSuccess: () => {
      formSugestao.value = { nome: '', email: '', mensagem: '' }
      tokenSugestao.value = ''
      captchaSugestao.value?.reset()
    },
    onError: (e) => { errosSugestao.value = e },
    onFinish: () => { loadingSugestao.value = false },
    preserveScroll: true,
  })
}

// ─── Pedido de oração ────────────────────────────────────────────────────────
const formOracao    = ref({ nome: '', pedido: '', anonimo: false, compartilhar: false })
const errosOracao   = ref({})
const loadingOracao = ref(false)
const tokenOracao   = ref('')
const captchaOracao = ref(null)

function toggleAnonimo() {
  formOracao.value.anonimo = !formOracao.value.anonimo
  if (formOracao.value.anonimo) formOracao.value.nome = ''
}

function enviarPedido() {
  errosOracao.value = {}
  loadingOracao.value = true
  const payload = { ...formOracao.value, 'h-captcha-response': tokenOracao.value }
  if (payload.anonimo) payload.nome = 'Anônimo'
  router.post('/pedido-oracao', payload, {
    onSuccess: () => {
      formOracao.value = { nome: '', pedido: '', anonimo: false, compartilhar: false }
      tokenOracao.value = ''
      captchaOracao.value?.reset()
    },
    onError: (e) => { errosOracao.value = e },
    onFinish: () => { loadingOracao.value = false },
    preserveScroll: true,
  })
}

// ─── Scroll reveal ───────────────────────────────────────────────────────────
onMounted(() => {
  const obs = new IntersectionObserver(
    entries => entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible') }),
    { threshold: 0.08 }
  )
  document.querySelectorAll('.reveal').forEach(r => obs.observe(r))
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
