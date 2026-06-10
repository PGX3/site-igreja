<template>
  <MainLayout>
 <div id="app">
    <!-- ══ HERO ══════════════════════════════════════════════════════════ -->
    <section id="hero" class="relative h-screen flex flex-col justify-center px-10 md:px-20 overflow-hidden">
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[700px]
                  rounded-full pointer-events-none uppercase"
           style="background: radial-gradient(circle, rgba(0,167,255,0.06) 5%, transparent 70%)"></div>

      <div class="absolute left-[55%] top-1/2 -translate-y-1/2 
        pointer-events-none select-none opacity-50 z-0">
        <LogoIcon class="w-[500px] h-[500px]" />
      </div>

      <div class="absolute left-6 top-1/4 bottom-1/4 w-px bg-gradient-to-b from-transparent via-[var(--blue)]/30 to-transparent hidden md:block"></div>

      <div class="relative z-10 max-w-5xl">
        <div class="flex items-center gap-4 mb-8 animate-fade-up" style="animation-delay:0.1s">
          <div class="w-8 h-px bg-[var(--blue)]"></div>
          <p class="section-label !mb-0">Igreja em Charqueadas · {{ new Date().getFullYear() }}</p>
        </div>
        <h1 class=" section-title text-[clamp(64px,10vw,160px)] animate-fade-up text-white uppercase"
            style="animation-delay:0.25s; line-height:0.88">
          {{ t('hero_titulo', 'Igreja.') }}
        </h1>
        <p class="mt-10 text-[11px] tracking-[0.3em] uppercase text-white/30 animate-fade-up"
           style="animation-delay:0.45s">
          {{ t('hero_subtitulo', 'Charqueadas · Rio Grande do Sul · Brasil') }}
        </p>
        <div class="mt-14 flex gap-4 animate-fade-up" style="animation-delay:0.6s">
          <a href="#sobre" class="btn-primary">Conheça a Igreja</a>
          <a href="#agenda" class="btn-ghost">Ver Agenda</a>
        </div>
      </div>

      <div class="absolute bottom-12 right-10 flex flex-col items-center gap-3">
        <div class="w-px h-16 bg-gradient-to-b from-[var(--blue)]/60 to-transparent animate-pulse-blue"></div>
      </div>
    </section>

    <!-- ══ SOBRE ══════════════════════════════════════════════════════════ -->
    <section id="sobre" class="px-10 md:px-20 py-32 border-t border-[var(--blue)]/10">
      <div class="grid md:grid-cols-2 gap-24 items-center">
        <div class="reveal">
          <p class="section-label">A Igreja</p>
          <h2 class="section-title text-[clamp(38px,5vw,72px)] text-white">
            {{ t('sobre_titulo', 'Uma comunidade que sabe o que é.') }}
          </h2>
          <div class="blue-line"></div>
          <p class="text-white/45 text-[15px] leading-[1.85] max-w-md">
            {{ t('sobre_texto', 'Somos uma comunidade de fé enraizada em Charqueadas, comprometida com o evangelho e com as pessoas desta cidade. Nossa missão é clara: ser Igreja.') }}
          </p>
        </div>
        <div class="reveal hidden md:flex justify-center items-center relative h-80">
          <LogoIcon :size="330" />
        </div>
      </div>
    </section>

    <!-- ══ IDENTIDADE ═══════════════════════════════════════════════════ -->
    <section id="identidade" class="px-10 md:px-20 py-32 border-t border-[var(--blue)]/10">
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

    <!-- ══ AGENDA ════════════════════════════════════════════════════════ -->
    <section id="agenda" class="px-10 md:px-20 py-32 border-t border-[var(--blue)]/10">
      <div class="reveal mb-16">
        <p class="section-label">Agenda</p>
        <h2 class="section-title text-[clamp(38px,5vw,72px)] text-white">
          Nos encontre<span class="text-[var(--blue)]">.</span>
        </h2>
      </div>
      <div>
        <Link v-for="culto in cultos" :key="culto.id"
              :href="`/cultos/${culto.id}`"
              class="reveal flex items-center gap-8 md:gap-14 py-7 border-b border-[var(--blue)]/10
                     transition-all duration-300 hover:pl-3 group cursor-pointer">
          <span class="text-[10px] font-bold tracking-[0.25em] uppercase text-[var(--blue)] w-20 shrink-0"
                style="font-family:'Barlow Condensed',sans-serif">{{ culto.dia_semana }}</span>
          <span class="text-lg font-bold flex-1 text-white/85 group-hover:text-white transition-colors">{{ culto.nome }}</span>
          <span class="text-[13px] text-white/30 tracking-wider hidden md:block font-mono">{{ culto.horario }}</span>
          <span class="text-[var(--blue)]/0 group-hover:text-[var(--blue)] transition-colors text-sm">→</span>
        </Link>
        <div v-if="!cultos || cultos.length === 0" class="py-12 text-white/25 text-sm tracking-wide text-center">
          Agenda em breve.
        </div>
      </div>
    </section>

    <!-- ══ EVENTOS ═══════════════════════════════════════════════════════ -->
    <section id="eventos" class="px-10 md:px-20 py-32 border-t border-[var(--blue)]/10">
      <div class="reveal mb-16">
        <p class="section-label">Eventos</p>
        <h2 class="section-title text-[clamp(38px,5vw,72px)] text-white">
          O que vem aí<span class="text-[var(--blue)]">.</span>
        </h2>
      </div>

      <div v-if="eventos && eventos.length > 0" class="grid md:grid-cols-2 gap-px bg-[var(--blue)]/10">
        <Link v-for="evento in eventos" :key="evento.id"
              :href="`/eventos/${evento.id}`"
              class="reveal bg-[#0a0a0a] hover:bg-[#0d0d0d] transition-colors p-10 group flex items-start gap-6 cursor-pointer">
          <!-- Bloco de data -->
          <div class="flex-shrink-0 text-center border border-[var(--blue)]/20 rounded-xl px-4 py-3 min-w-[72px]
                      group-hover:border-[var(--blue)]/60 transition-colors">
            <p class="text-[9px] font-bold tracking-[0.25em] uppercase text-[var(--blue)]/70">
              {{ evento.mes_curto }}
            </p>
            <p class="text-3xl font-black text-white leading-none mt-1">{{ evento.dia }}</p>
          </div>
          <!-- Texto -->
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-bold text-white/85 group-hover:text-white transition-colors truncate">
              {{ evento.nome }}
            </h3>
            <p v-if="evento.horario || evento.local"
               class="text-[12px] text-white/30 tracking-wide mt-1 font-mono truncate">
              <span v-if="evento.horario">{{ evento.horario }}</span>
              <span v-if="evento.horario && evento.local" class="mx-2">·</span>
              <span v-if="evento.local">{{ evento.local }}</span>
            </p>
            <p v-if="evento.descricao"
               class="text-[13px] text-white/40 leading-relaxed mt-3 line-clamp-2">
              {{ evento.descricao }}
            </p>
          </div>
          <span class="text-[var(--blue)]/0 group-hover:text-[var(--blue)] transition-colors text-sm self-center">→</span>
        </Link>
      </div>

      <div v-else class="py-12 text-white/25 text-sm tracking-wide text-center">
        Nenhum evento agendado no momento.
      </div>
    </section>

    <!-- ══ SUGESTÕES ═════════════════════════════════════════════════════ -->
    <section id="sugestoes" class="px-10 md:px-20 py-32 border-t border-[var(--blue)]/10">
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
            <div class="grid grid-cols-2 gap-4">
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
    <section id="oracao" class="px-10 md:px-20 py-32 border-t border-[var(--blue)]/10">
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
    <section id="cta" class="px-10 md:px-20 py-40 border-t border-[var(--blue)]/10 text-center relative overflow-hidden">
      <div class="absolute inset-0 pointer-events-none"
           style="background: radial-gradient(ellipse at center, rgba(0,167,255,0.04) 0%, transparent 70%)"></div>
      <p class="section-label inline-block">Venha nos visitar</p>
      <h2 class="reveal section-title text-[clamp(56px,9vw,130px)] text-white mt-4">
        {{ t('cta_titulo', 'Você é bem-vindo.') }}
      </h2>
      <p class="mt-8 mb-14 text-[11px] tracking-[0.3em] uppercase text-white/25">
        {{ t('endereco', 'Charqueadas · Rio Grande do Sul') }}
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

const props = defineProps({ cultos: Array, eventos: Array, textos: Object })
const page = usePage()

// hCaptcha sitekey — vem do backend via shared props ou usa a chave de teste
const hcaptchaSitekey = page.props.hcaptchaSitekey || 'da1a0e90-27ce-4d62-a2aa-54a26506be18'

function t(key, fallback = '') { return props.textos?.[key] || fallback }

const identCards = [
  { title: 'O Brasão',  body: 'Uma referência direta à cidade de Charqueadas — carregamos o lugar onde fomos plantados. Somos daqui.' },
  { title: 'A Cruz',    body: 'Fundamento e fé. A cruz não é decoração — ela define o centro do que acreditamos e de quem somos.' },
  { title: 'O Ponto.', body: 'Uma declaração completa e fechada. Não é "igreja de algo". É Igreja. Ponto. Uma afirmação de identidade.' },
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
const formOracao    = ref({ nome: '', pedido: '', anonimo: false })
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
      formOracao.value = { nome: '', pedido: '', anonimo: false }
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
