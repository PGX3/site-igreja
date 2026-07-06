<template>
  <Head>
    <title>Pregações</title>
    <meta name="description" content="Assista às pregações da Igreja em Charqueadas. Mensagens bíblicas semanais direto do nosso canal." />
    <meta property="og:title" content="Pregações · Igreja em Charqueadas" />
    <meta property="og:description" content="Assista às pregações da Igreja em Charqueadas. Mensagens bíblicas semanais direto do nosso canal." />
  </Head>
  <MainLayout>
    <div>

      <!-- ── HERO ── -->
      <section class="relative px-6 sm:px-10 md:px-20 pt-40 pb-20 overflow-hidden border-b border-[var(--blue)]/10">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse at 70% 30%, rgba(0,167,255,0.06) 0%, transparent 55%)"></div>
        <div class="relative z-10 max-w-5xl">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-8 h-px bg-[var(--blue)]"></div>
            <p class="section-label !mb-0">A Palavra · Semana a semana</p>
          </div>
          <h1 class="section-title text-[clamp(52px,9vw,120px)] text-white leading-[0.9]">
            Pregações<span class="text-[var(--blue)]">.</span>
          </h1>
          <p class="mt-8 text-white/45 text-[15px] leading-[1.85] max-w-md">
            As mensagens pregadas na nossa comunidade, reunidas aqui para você assistir quando quiser.
          </p>
        </div>
      </section>

      <!-- ── GRADE ── -->
      <section class="px-6 sm:px-10 md:px-20 py-20 md:py-28">

        <div v-if="!pregacoes?.length" class="py-16 text-center">
          <div class="inline-flex flex-col items-center gap-4">
            <div class="w-12 h-12 rounded-full border border-[var(--blue)]/20 flex items-center justify-center">
              <span class="w-1.5 h-1.5 rounded-full bg-[var(--blue)]/50 animate-pulse-blue"></span>
            </div>
            <p class="text-white/30 text-[11px] tracking-[0.3em] uppercase">
              Nenhuma pregação publicada ainda
            </p>
          </div>
        </div>

        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
          <Link v-for="pregacao in pregacoes" :key="pregacao.id"
                :href="`/pregacoes/${pregacao.id}`"
                class="group block border border-[var(--blue)]/15 hover:border-[var(--blue)]/50
                       transition-all duration-500 overflow-hidden">
            <!-- Thumbnail -->
            <div class="relative aspect-video bg-black overflow-hidden">
              <img v-if="thumb(pregacao.youtube_url)" :src="thumb(pregacao.youtube_url)" :alt="pregacao.titulo"
                   class="w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105
                          transition-all duration-700" loading="lazy" />
              <!-- Play overlay -->
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="w-14 h-14 rounded-full bg-black/50 border border-white/30 backdrop-blur-sm
                             flex items-center justify-center text-white
                             group-hover:bg-[var(--blue)] group-hover:border-[var(--blue)] transition-all duration-300">
                  <span class="ml-1 text-lg">▶</span>
                </span>
              </div>
            </div>
            <!-- Info -->
            <div class="p-6">
              <p class="text-[10px] font-bold tracking-[0.3em] uppercase text-[var(--blue)]/70 mb-3"
                 style="font-family:'Barlow Condensed',sans-serif">
                {{ formatarData(pregacao.data_pregacao) }}
              </p>
              <h3 class="text-lg font-bold text-white leading-snug group-hover:text-[var(--blue)] transition-colors line-clamp-2">
                {{ pregacao.titulo }}
              </h3>
              <p v-if="pregacao.pregador" class="text-white/40 text-[13px] mt-2">{{ pregacao.pregador }}</p>
              <p v-if="pregacao.versiculo" class="text-white/30 text-[12px] italic mt-1"
                 style="font-family:'Cormorant Garamond',serif">{{ pregacao.versiculo }}</p>
            </div>
          </Link>
        </div>
      </section>

    </div>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({ pregacoes: Array, meta: Object })

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
  return d.toLocaleDateString('pt-BR', {
    day: '2-digit', month: 'long', year: 'numeric', timeZone: 'UTC',
  })
}
</script>
