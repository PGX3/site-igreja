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

      <!-- ── CORTES (Shorts desta pregação) ── -->
      <section v-if="cortes.length" class="px-6 sm:px-10 md:px-20 py-16 border-b border-[var(--blue)]/10">
        <div class="max-w-5xl">
          <div class="flex items-end justify-between gap-4 mb-8">
            <div>
              <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/50 mb-3">Cortes desta mensagem</p>
              <p class="text-white/40 text-[15px]">Trechos curtos para assistir e compartilhar.</p>
            </div>
            <span class="hidden sm:block text-[11px] tracking-[0.25em] uppercase text-white/25">
              {{ cortes.length }} {{ cortes.length === 1 ? 'corte' : 'cortes' }}
            </span>
          </div>

          <div class="flex gap-4 overflow-x-auto snap-x snap-mandatory pb-4 -mx-6 px-6 sm:mx-0 sm:px-0 [scrollbar-width:thin]">
            <button v-for="(c, i) in cortes" :key="c.id" type="button" @click="abrir(i)"
                    class="group snap-start shrink-0 w-[44%] sm:w-[200px] text-left focus:outline-none">
              <div class="relative aspect-[9/16] rounded-lg overflow-hidden bg-white/5 border border-white/5
                          group-hover:border-[var(--blue)]/40 group-focus-visible:border-[var(--blue)] transition-colors">
                <img :src="c.thumb" @error="(e) => { if (e.target.src !== c.thumb_reserva) e.target.src = c.thumb_reserva }"
                     :alt="tituloCurto(c)" loading="lazy"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/10 to-transparent"></div>
                <span class="absolute top-3 left-3 w-9 h-9 rounded-full bg-black/40 backdrop-blur-sm border border-white/15
                             flex items-center justify-center text-white group-hover:bg-[var(--blue)] group-hover:border-[var(--blue)] transition-colors">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z" /></svg>
                </span>
                <p class="absolute bottom-0 inset-x-0 p-3.5 text-[13px] leading-snug font-medium text-white line-clamp-3">{{ tituloCurto(c) }}</p>
              </div>
            </button>
          </div>
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

    <!-- Player do corte -->
    <Teleport to="body">
      <div v-if="aberto !== null" class="fixed inset-0 z-[60] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
           @click.self="fechar">
        <button type="button" @click="fechar" aria-label="Fechar"
                class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12" /></svg>
        </button>
        <button v-if="cortes.length > 1" type="button" @click="passar(-1)" aria-label="Corte anterior"
                class="absolute left-2 sm:left-8 w-11 h-11 rounded-full bg-white/10 hover:bg-[var(--blue)] text-white flex items-center justify-center transition">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6" /></svg>
        </button>
        <div class="flex flex-col items-center gap-3">
          <div class="relative rounded-xl overflow-hidden bg-black shadow-2xl"
               style="height: min(82vh, 760px); aspect-ratio: 9 / 16; max-width: calc(100vw - 7rem);">
            <iframe :key="cortes[aberto].youtube_id"
                    :src="`https://www.youtube.com/embed/${cortes[aberto].youtube_id}?autoplay=1&rel=0&playsinline=1`"
                    class="absolute inset-0 w-full h-full" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
          </div>
          <p class="text-[11px] tracking-[0.25em] uppercase text-white/40 tabular-nums">{{ aberto + 1 }} / {{ cortes.length }}</p>
        </div>
        <button v-if="cortes.length > 1" type="button" @click="passar(1)" aria-label="Próximo corte"
                class="absolute right-2 sm:right-8 w-11 h-11 rounded-full bg-white/10 hover:bg-[var(--blue)] text-white flex items-center justify-center transition">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6" /></svg>
        </button>
      </div>
    </Teleport>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import MediaEmbed from '@/Components/MediaEmbed.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  pregacao: Object,
  meta: Object,
})

// ─── Cortes (Shorts) ───
const cortes = computed(() => props.pregacao.cortes ?? [])
const aberto = ref(null)
// "O Rei está aqui | Queremos um rei": dentro da pregação, o tema depois da barra é redundante.
const tituloCurto = (c) => c.titulo.split('|')[0].trim() || c.titulo
function abrir(i) { aberto.value = i; document.body.style.overflow = 'hidden' }
function fechar() { aberto.value = null; document.body.style.overflow = '' }
function passar(d) { aberto.value = (aberto.value + d + cortes.value.length) % cortes.value.length }
function teclas(e) {
  if (aberto.value === null) return
  if (e.key === 'Escape') fechar()
  if (e.key === 'ArrowRight') passar(1)
  if (e.key === 'ArrowLeft') passar(-1)
}
onMounted(() => window.addEventListener('keydown', teclas))
onBeforeUnmount(() => { window.removeEventListener('keydown', teclas); document.body.style.overflow = '' })
</script>
