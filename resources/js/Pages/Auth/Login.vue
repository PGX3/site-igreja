<template>
  <div class="min-h-screen bg-[#0a0a0a] flex overflow-hidden">

    <!-- Left panel — deco -->
    <div class="hidden lg:flex w-1/2 flex-col justify-between p-16 border-r border-[var(--border)] relative">
      <div class="absolute inset-0 pointer-events-none"
           style="background: radial-gradient(ellipse at 30% 50%, rgba(0,167,255,0.06) 0%, transparent 60%)"></div>

      <div class="flex items-center gap-3">
        <svg width="24" height="24" viewBox="0 0 32 32" fill="none">
          <path d="M16 2L4 7v10c0 6.6 5.2 12.7 12 14 6.8-1.3 12-7.4 12-14V7L16 2z"
                stroke="var(--blue)" stroke-width="1.2" fill="none"/>
          <line x1="16" y1="8" x2="16" y2="19" stroke="white" stroke-width="1.5"/>
          <line x1="11" y1="13" x2="21" y2="13" stroke="white" stroke-width="1.5"/>
          <circle cx="16" cy="24" r="1.8" fill="var(--blue)"/>
        </svg>
        <span style="font-family:'Barlow Condensed',sans-serif" class="font-black text-sm tracking-[0.25em] uppercase">
          IGREJA<span class="text-[var(--blue)]">.</span>
        </span>
      </div>

      <div>
        <p class="text-[10px] tracking-[0.3em] uppercase text-[var(--blue)] mb-4">Área Restrita</p>
        <h2 style="font-family:'Cormorant Garamond',serif" class="text-5xl font-bold text-white/80 leading-tight">
          Painel<br>Administrativo
        </h2>
        <p class="mt-6 text-white/25 text-[13px] leading-relaxed max-w-xs">
          Gerencie cultos, textos e comunicações da Igreja em Charqueadas.
        </p>
      </div>

      <p class="text-[10px] tracking-[0.2em] uppercase text-white/15">© {{ new Date().getFullYear() }} Igreja em Charqueadas</p>
    </div>

    <!-- Right panel — form -->
    <div class="flex-1 flex flex-col items-center justify-center px-8">
      <div class="w-full max-w-sm">
        <!-- Mobile logo -->
        <div class="lg:hidden mb-10 text-center">
          <div class="flex items-center justify-center gap-2">
            <LogoIcon :size="32" />
            <p style="font-family:'Barlow Condensed',sans-serif" class="font-black text-xl tracking-[0.25em] uppercase">
              IGREJA<span class="text-[var(--blue)]">.</span>
            </p>
          </div>
        </div>

        <p class="text-[10px] tracking-[0.3em] uppercase text-white/30 mb-8">Entrar na conta</p>

        <form @submit.prevent="submit" class="flex flex-col gap-4">
          <div>
            <label class="block text-[9px] font-bold tracking-[0.3em] uppercase text-white/30 mb-2">E-mail</label>
            <input v-model="form.email" type="email" required class="field w-full" placeholder="admin@igreja.com" />
            <p v-if="form.errors.email" class="text-red-400 text-xs mt-1.5">{{ form.errors.email }}</p>
          </div>
          <div>
            <label class="block text-[9px] font-bold tracking-[0.3em] uppercase text-white/30 mb-2">Senha</label>
            <input v-model="form.password" type="password" required class="field w-full" placeholder="••••••••" />
          </div>
          <button type="submit" :disabled="form.processing"
                  class="btn-primary w-full text-center mt-2 disabled:opacity-40 disabled:cursor-not-allowed">
            {{ form.processing ? 'Entrando...' : '→ Entrar' }}
          </button>
        </form>

        <div class="mt-10 pt-8 border-t border-[var(--border)]">
          <Link href="/" class="text-[10px] tracking-[0.25em] uppercase text-white/25 hover:text-[var(--blue)] transition-colors">
            ← Voltar ao site
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import LogoIcon from '@/Components/LogoIcon.vue'
const form = useForm({ email: '', password: '' })
function submit() { form.post('/login') }
</script>
