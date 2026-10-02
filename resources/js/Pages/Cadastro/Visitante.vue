<template>
  <MainLayout>
    <section class="relative px-6 sm:px-10 md:px-20 pt-32 sm:pt-40 pb-24 md:pb-32 overflow-hidden">
      <!-- Atmosférico, no mesmo tom da /primeira-vez -->
      <div class="absolute inset-0 pointer-events-none"
           style="background: radial-gradient(ellipse at 70% 20%, rgba(0,167,255,0.06) 0%, transparent 55%)"></div>

      <div class="relative z-10 max-w-xl mx-auto">

        <header class="mb-12 md:mb-16">
          <div class="flex items-center gap-3 mb-6">
            <span class="text-[var(--blue)] text-[10px] tracking-[0.35em] font-bold"
                  style="font-family:'Barlow Condensed',sans-serif">Nº 01</span>
            <span class="w-12 h-px bg-[var(--blue)]/40"></span>
            <span class="text-white/30 text-[9px] tracking-[0.3em] uppercase"
                  style="font-family:'Barlow Condensed',sans-serif">Acolhimento</span>
          </div>

          <h1 class="section-title text-[clamp(38px,9vw,76px)] text-white uppercase leading-[0.92]">
            Deixe seu<br />contato<span class="text-[var(--blue)]">.</span>
          </h1>

          <p class="mt-6 sm:mt-8 italic text-[18px] sm:text-[22px] text-white/50 leading-[1.45]"
             style="font-family:'Cormorant Garamond',serif;font-weight:400">
            Quatro campos, menos de um minuto. É só para a gente saber quem você é
            e poder te receber bem na próxima vez.
          </p>
        </header>

        <form @submit.prevent="enviar" class="space-y-7">
          <!-- honeypot anti-spam -->
          <input v-model="form._honey" type="text" name="_honey" tabindex="-1" autocomplete="off"
                 class="absolute opacity-0 pointer-events-none -left-[9999px]" aria-hidden="true" />

          <Campo label="Como você se chama?" :error="erros.name">
            <input v-model="form.name" type="text" class="field" placeholder="Seu nome"
                   autocomplete="name" required />
          </Campo>

          <Campo label="WhatsApp" :error="erros.telefone"
                 ajuda="É por aqui que a gente vai falar com você.">
            <input v-model="form.telefone" type="tel" class="field" placeholder="(51) 99999-9999"
                   autocomplete="tel" inputmode="tel" required />
          </Campo>

          <Campo label="Data de nascimento" :error="erros.data_nascimento"
                 ajuda="Para a gente não esquecer do seu aniversário.">
            <input v-model="form.data_nascimento" type="date" class="field"
                   autocomplete="bday" :max="hoje" required />
          </Campo>

          <Campo label="Como nos conheceu?" :error="erros.como_conheceu">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <button v-for="opcao in comoConheceuOpcoes" :key="opcao"
                      type="button"
                      class="px-4 py-3.5 text-left text-[13px] tracking-wide transition-all duration-200 border"
                      :class="form.como_conheceu === opcao
                        ? 'border-[var(--blue)]/50 bg-[var(--blue)]/[0.08] text-white'
                        : 'border-white/[0.08] bg-white/[0.03] text-white/50 hover:border-white/20 hover:text-white/75'"
                      @click="selecionar(opcao)">
                {{ opcao }}
              </button>
            </div>
          </Campo>

          <Campo v-if="form.como_conheceu === 'Outro'" label="Conta pra gente"
                 :error="erros.como_conheceu_detalhe">
            <input v-model="form.como_conheceu_detalhe" type="text" class="field"
                   placeholder="Como você chegou até aqui?" />
          </Campo>

          <Campo label="E-mail" :error="erros.email" ajuda="Opcional.">
            <input v-model="form.email" type="email" class="field" placeholder="voce@exemplo.com"
                   autocomplete="email" />
          </Campo>

          <p v-if="erros.captcha" class="text-xs text-red-400">{{ erros.captcha }}</p>

          <div class="pt-2">
            <button type="submit" :disabled="enviando"
                    class="btn-primary w-full text-center disabled:opacity-50 disabled:cursor-not-allowed">
              {{ enviando ? 'Enviando...' : '→ Enviar' }}
            </button>
          </div>

          <p class="text-[10px] text-white/20 leading-relaxed" v-html="avisoRecaptcha"></p>

          <p class="text-[11px] text-white/30 leading-relaxed border-t border-white/[0.06] pt-6">
            Seus dados ficam só com a equipe pastoral. Já é membro da igreja?
            <Link href="/cadastro" class="text-[var(--blue)]/70 hover:text-[var(--blue)] underline transition-colors">
              use o cadastro completo
            </Link>.
          </p>
        </form>

      </div>
    </section>
  </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue'
import { useRecaptcha } from '@/composables/useRecaptcha'
import { Link, router, usePage } from '@inertiajs/vue3'
import { h, ref } from 'vue'

defineProps({
  comoConheceuOpcoes: { type: Array, default: () => [] },
})

const page = usePage()
const { execute: executarCaptcha } = useRecaptcha(page.props.recaptchaSitekey || '')

const avisoRecaptcha =
  'Protegido por reCAPTCHA. Aplicam-se a ' +
  '<a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="underline hover:text-white/40">Política de Privacidade</a> e os ' +
  '<a href="https://policies.google.com/terms" target="_blank" rel="noopener" class="underline hover:text-white/40">Termos de Serviço</a> do Google.'

const hoje = new Date().toISOString().slice(0, 10)

const form = ref({
  name: '',
  telefone: '',
  data_nascimento: '',
  como_conheceu: '',
  como_conheceu_detalhe: '',
  email: '',
  _honey: '',
})
const erros = ref({})
const enviando = ref(false)

function selecionar(opcao) {
  form.value.como_conheceu = opcao
  if (opcao !== 'Outro') form.value.como_conheceu_detalhe = ''
}

async function enviar() {
  erros.value = {}
  enviando.value = true

  let token = ''
  try {
    token = await executarCaptcha('cadastro_visitante')
  } catch {
    erros.value = { captcha: 'Não foi possível carregar a verificação. Tente novamente.' }
    enviando.value = false
    return
  }

  router.post('/sou-novo', { ...form.value, 'g-recaptcha-response': token }, {
    onError: (e) => { erros.value = e },
    onFinish: () => { enviando.value = false },
    preserveScroll: true,
  })
}

const Campo = (props, { slots }) => h('div', null, [
  h('label', { class: 'block text-[10px] font-bold tracking-[0.25em] uppercase text-white/40 mb-3' },
    props.label),
  slots.default?.(),
  props.error
    ? h('p', { class: 'mt-2 text-xs text-red-400' }, props.error)
    : props.ajuda
      ? h('p', { class: 'mt-2 text-[11px] text-white/25' }, props.ajuda)
      : null,
])
Campo.props = ['label', 'error', 'ajuda']
</script>
