<template>
  <MainLayout>
    <div>

      <!-- ══ Nº 01 · HERO ══════════════════════════════════════════════════ -->
      <section id="hero" class="relative px-6 sm:px-10 md:px-20 pt-32 sm:pt-40 pb-20 md:pb-28 overflow-hidden border-b border-[var(--blue)]/10">
        <!-- Atmosférico -->
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse at 70% 30%, rgba(0,167,255,0.06) 0%, transparent 55%)"></div>
        <!-- Padrão de pontos sutil -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
             style="background-image: radial-gradient(circle, rgba(0,167,255,0.7) 0.8px, transparent 0.8px); background-size: 28px 28px"></div>

        <div class="relative z-10 max-w-3xl">
          <SectionMark numero="01" rotulo="Boas-vindas" />

          <h1 class="section-title text-[clamp(46px,12vw,120px)] text-white uppercase leading-[0.9] animate-fade-up"
              style="animation-delay:0.15s">
            Primeira vez<span class="text-[var(--blue)]">?</span>
          </h1>

          <p class="mt-7 sm:mt-9 italic text-[19px] sm:text-[24px] md:text-[28px] text-white/50 leading-[1.4] max-w-xl animate-fade-up"
             style="font-family:'Cormorant Garamond',serif; font-weight:400; animation-delay:0.3s">
            Ir a uma igreja pela primeira vez pode dar um frio na barriga. A gente entende.
            Aqui está tudo o que você precisa saber.
          </p>

          <div class="mt-10 sm:mt-14 flex flex-col sm:flex-row gap-3 sm:gap-4 animate-fade-up"
               style="animation-delay:0.45s">
            <a href="#quando" class="btn-primary text-center">→ Ver Horários</a>
            <a :href="mapsUrl" target="_blank" rel="noopener" class="btn-ghost text-center">→ Como Chegar</a>
          </div>
        </div>
      </section>

      <!-- ══ Nº 02 · QUANDO ════════════════════════════════════════════════ -->
      <section id="quando" class="px-6 sm:px-10 md:px-20 py-20 md:py-32 border-t border-[var(--blue)]/10 scroll-mt-20">
        <div class="reveal mb-12 md:mb-20">
          <SectionMark numero="02" rotulo="Agenda" />
          <p class="section-label">Quando</p>
          <h2 class="section-title text-[clamp(34px,5vw,72px)] text-white">
            Três encontros por <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:500" class="text-white/85">semana</span><span class="text-[var(--blue)]">.</span>
          </h2>
        </div>

        <div class="grid md:grid-cols-3 gap-px bg-[var(--blue)]/10">
          <div v-for="(encontro, i) in encontros" :key="encontro.nome"
               class="reveal bg-[#0a0a0a] p-8 sm:p-10 md:p-12 relative overflow-hidden group transition-colors duration-500 hover:bg-[#0f0f0f]"
               :style="`animation-delay:${i * 0.12}s`">
            <div class="absolute bottom-0 left-0 h-px w-0 bg-gradient-to-r from-[var(--blue)] to-transparent
                        group-hover:w-full transition-all duration-700"></div>

            <p class="text-[10px] font-bold tracking-[0.3em] text-[var(--blue)]/60 mb-8 md:mb-10"
               style="font-family:'Barlow Condensed',sans-serif">0{{ i + 1 }}</p>

            <p class="text-[clamp(30px,6vw,48px)] leading-none italic lowercase text-white/85 mb-4"
               style="font-family:'Cormorant Garamond',serif;font-weight:500">
              {{ encontro.dia }}
            </p>

            <p class="font-mono text-[13px] tracking-[0.2em] text-[var(--blue)] mb-5">{{ encontro.horario }}</p>

            <h3 class="text-lg font-bold tracking-tight text-white">{{ encontro.nome }}</h3>
          </div>
        </div>

        <p class="reveal mt-10 md:mt-14 text-white/45 text-[15px] leading-[1.85] max-w-lg">
          Se puder, chegue uns 10 minutinhos antes, dá tempo de achar um lugar e conhecer alguém.
        </p>
      </section>

      <!-- ══ Nº 03 · COMO É UM CULTO ═══════════════════════════════════════ -->
      <section id="culto" class="relative px-6 sm:px-10 md:px-20 py-20 md:py-32 border-t border-[var(--blue)]/10 overflow-hidden scroll-mt-20">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse 500px 400px at 85% 40%, rgba(0,167,255,0.04) 0%, transparent 60%)"></div>

        <div class="relative grid md:grid-cols-12 gap-10 md:gap-16 items-start">
          <div class="md:col-span-4 reveal">
            <SectionMark numero="03" rotulo="O encontro" />
            <p class="section-label">Como é um culto</p>
            <h2 class="section-title text-[clamp(34px,5vw,68px)] text-white">
              Mais <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:500" class="text-white/85">simples</span> do que parece<span class="text-[var(--blue)]">.</span>
            </h2>
            <div class="blue-line"></div>
            <p class="text-white/40 text-[14px] leading-[1.85] max-w-sm">
              Ninguém vai te pedir para falar, levantar ou se apresentar. Fique à vontade para só assistir.
            </p>
          </div>

          <div class="md:col-span-8 reveal">
            <div v-for="(etapa, i) in etapas" :key="etapa.titulo"
                 class="flex items-baseline gap-4 sm:gap-6 py-5 sm:py-6 border-b border-white/[0.06] group">
              <span class="text-[10px] tracking-[0.35em] text-[var(--blue)]/40 font-bold w-6 sm:w-8 shrink-0
                           group-hover:text-[var(--blue)] transition-colors"
                    style="font-family:'Barlow Condensed',sans-serif">
                {{ String(i + 1).padStart(2, '0') }}
              </span>
              <div class="flex-1">
                <p class="text-[clamp(22px,3.5vw,38px)] italic text-white/75 leading-[1.2]
                          group-hover:text-white transition-colors"
                   style="font-family:'Cormorant Garamond',serif;font-weight:400">
                  {{ etapa.titulo }}
                </p>
                <p v-if="etapa.detalhe" class="mt-2 text-white/35 text-[13px] leading-relaxed">
                  {{ etapa.detalhe }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ══ Nº 04 · PERGUNTAS FREQUENTES ══════════════════════════════════ -->
      <section id="duvidas" class="px-6 sm:px-10 md:px-20 py-20 md:py-32 border-t border-[var(--blue)]/10 scroll-mt-20">
        <div class="reveal mb-12 md:mb-20">
          <SectionMark numero="04" rotulo="Dúvidas" />
          <p class="section-label">Perguntas frequentes</p>
          <h2 class="section-title text-[clamp(34px,5vw,72px)] text-white">
            Pergunte sem <span style="font-family:'Cormorant Garamond',serif;font-style:italic;font-weight:500" class="text-white/85">receio</span><span class="text-[var(--blue)]">.</span>
          </h2>
        </div>

        <div class="reveal max-w-3xl">
          <div v-for="(faq, i) in faqs" :key="faq.pergunta"
               class="border-b border-white/[0.06]">
            <button type="button"
                    class="w-full flex items-start gap-4 sm:gap-6 py-6 text-left group"
                    :aria-expanded="aberta === i ? 'true' : 'false'"
                    :aria-controls="`faq-painel-${i}`"
                    @click="alternar(i)">
              <span class="text-[10px] tracking-[0.35em] text-[var(--blue)]/40 font-bold w-6 sm:w-8 shrink-0 pt-1.5
                           group-hover:text-[var(--blue)] transition-colors"
                    style="font-family:'Barlow Condensed',sans-serif">
                {{ String(i + 1).padStart(2, '0') }}
              </span>

              <span class="flex-1 text-[16px] sm:text-lg font-bold tracking-tight leading-snug transition-colors"
                    :class="aberta === i ? 'text-[var(--blue)]' : 'text-white/85 group-hover:text-white'">
                {{ faq.pergunta }}
              </span>

              <span class="shrink-0 text-[var(--blue)]/50 group-hover:text-[var(--blue)] text-lg leading-none
                           transition-all duration-300 pt-1"
                    :class="aberta === i ? 'rotate-45 text-[var(--blue)]' : ''">
                +
              </span>
            </button>

            <div :id="`faq-painel-${i}`"
                 class="grid transition-all duration-500 ease-out"
                 :class="aberta === i ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
              <div class="overflow-hidden">
                <div class="pb-7 pl-10 sm:pl-14 pr-2">
                  <p class="text-white/45 text-[14px] sm:text-[15px] leading-[1.85]">
                    {{ faq.resposta }}
                  </p>
                  <a v-if="faq.linkInterno"
                     :href="faq.linkInterno"
                     class="mt-4 inline-flex items-center gap-3 text-[10px] tracking-[0.35em] uppercase font-bold
                            text-[var(--blue)] hover:gap-5 transition-all"
                     style="font-family:'Barlow Condensed',sans-serif">
                    <span>{{ faq.linkLabel }}</span>
                    <span class="w-8 h-px bg-[var(--blue)]"></span>
                    <span>→</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ══ Nº 05 · VOCÊ É BEM-VINDO ══════════════════════════════════════ -->
      <section id="bem-vindo"
               class="relative px-6 sm:px-10 md:px-20 py-24 md:py-40 border-t border-[var(--blue)]/10 overflow-hidden scroll-mt-20">
        <div class="absolute inset-0 pointer-events-none"
             style="background: radial-gradient(ellipse at center, rgba(0,167,255,0.05) 0%, transparent 70%)"></div>

        <div class="relative max-w-4xl">
          <SectionMark numero="05" rotulo="Portas abertas" />
          <p class="section-label">Você é bem-vindo</p>

          <div class="reveal">
            <p v-for="linha in convite" :key="linha"
               class="text-[clamp(26px,6vw,56px)] italic text-white/70 leading-[1.25]"
               style="font-family:'Cormorant Garamond',serif;font-weight:400">
              {{ linha }}
            </p>

            <p class="mt-6 sm:mt-8 text-[clamp(30px,8vw,76px)] font-black text-white uppercase leading-[0.95] tracking-tight">
              As portas estão abertas.
              <span class="text-[var(--blue)]">Vem</span><span class="text-[var(--blue)]">.</span>
            </p>
          </div>

          <div class="reveal mt-12 md:mt-16 flex flex-col sm:flex-row gap-3 sm:gap-4">
            <Link href="/sou-novo" class="btn-primary text-center">→ Deixar meu contato</Link>
            <a :href="whatsappUrl" target="_blank" rel="noopener" class="btn-ghost text-center">→ Falar no WhatsApp</a>
            <a :href="mapsUrl" target="_blank" rel="noopener" class="btn-ghost text-center">→ Como Chegar</a>
          </div>

          <p class="reveal mt-6 text-white/30 text-[13px] leading-relaxed max-w-md">
            Já visitou? Deixe seu nome e WhatsApp, leva menos de um minuto, e a equipe
            de acolhimento fala com você.
          </p>

          <p class="mt-10 md:mt-14 text-[10px] sm:text-[11px] tracking-[0.3em] uppercase text-white/25">
            Rua Rui Barbosa, 1433 · Centro · Charqueadas / RS
          </p>
        </div>
      </section>

    </div>
  </MainLayout>
</template>

<script setup>
import { h, ref, onMounted } from 'vue'
import MainLayout from '@/Layouts/MainLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  meta: Object,
  jsonLd: Object,
})

const mapsUrl = 'https://www.google.com/maps/place/R.+Rui+Barbosa,+1433+-+Centro,+Charqueadas+-+RS,+96745-000'
const whatsappUrl = 'https://wa.me/5551992824071?text=Ol%C3%A1!%20Vou%20visitar%20a%20igreja%20pela%20primeira%20vez.'

// Marcador editorial "Nº 0X · Rótulo", igual ao usado no topo da home.
const SectionMark = (props) =>
  h('div', { class: 'flex items-center gap-3 mb-6' }, [
    h('span', {
      class: 'text-[var(--blue)] text-[10px] tracking-[0.35em] font-bold',
      style: "font-family:'Barlow Condensed',sans-serif",
    }, `Nº ${props.numero}`),
    h('span', { class: 'w-12 h-px bg-[var(--blue)]/40' }),
    h('span', {
      class: 'text-white/30 text-[9px] tracking-[0.3em] uppercase',
      style: "font-family:'Barlow Condensed',sans-serif",
    }, props.rotulo),
  ])
SectionMark.props = ['numero', 'rotulo']

const encontros = [
  { dia: 'domingo', horario: '19h',    nome: 'Culto de Domingo' },
  { dia: 'quarta',  horario: '20h',    nome: 'Culto de Oração' },
  { dia: 'sábado',  horario: '19h30',  nome: 'Culto Jovem' },
]

const etapas = [
  { titulo: 'Louvor',                 detalhe: 'Cantamos juntos, de 4 a 5 músicas.' },
  { titulo: 'Palavra',                detalhe: 'Uma mensagem bíblica, explicada de forma clara.' },
  { titulo: 'Avisos e oração final',  detalhe: '' },
  { titulo: 'Ceia',                   detalhe: 'Em alguns cultos.' },
]

const faqs = [
  {
    pergunta: 'Preciso pagar alguma coisa?',
    resposta: 'Não. A visita é gratuita. A contribuição é voluntária e só para quem quiser.',
  },
  {
    pergunta: 'Posso levar crianças?',
    resposta: 'Sim! Durante a mensagem as crianças vão para o cultinho com as professoras. Ao final do culto, busque seu filho na sala, temos uma tolerância de 5 minutos.',
  },
  {
    pergunta: 'Onde estaciono?',
    resposta: 'Na rua e no estacionamento compartilhado com a igreja em frente.',
  },
  {
    pergunta: 'Posso ir sozinho?',
    resposta: 'Pode, sim. Você vai ser bem recebido.',
  },
  {
    pergunta: 'Quanto tempo dura?',
    resposta: 'No máximo 1h50.',
  },
  {
    pergunta: 'Posso assistir online antes?',
    resposta: 'Sim, as pregações ficam no nosso YouTube.',
    linkInterno: '/pregacoes',
    linkLabel: 'Ver pregações',
  },
  {
    pergunta: 'Posso pedir oração?',
    resposta: 'Claro. Use o formulário de oração e a gente ora por você.',
    linkInterno: '/#oracao',
    linkLabel: 'Pedir oração',
  },
]

const convite = [
  'Pra quem nunca foi.',
  'Pra quem tá voltando.',
  'Pra quem tá em dúvida.',
  'Pra quem chega cansado.',
]

const aberta = ref(0)
function alternar(i) {
  aberta.value = aberta.value === i ? null : i
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
