<template>
  <div class="min-h-screen bg-[#0a0a0a] text-white">

    <!-- NAV -->
    <nav
      class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-10 md:px-16 py-5 transition-all duration-500"
      :class="scrolled ? 'bg-[#0a0a0a]/96 backdrop-blur-md border-b border-[var(--blue)]/10' : ''"
    >
      <Link href="/" class="flex items-center gap-3 group">
        <LogoIcon :size="34" class="transition-transform duration-300 group-hover:scale-110" />
        <span style="font-family:'Barlow Condensed',sans-serif"
              class="font-black text-base tracking-[0.2em] uppercase hidden sm:block">
          IGREJA<span class="text-[var(--blue)]">.</span>
        </span>
      </Link>

      <!-- Desktop nav -->
      <ul class="hidden md:flex items-center gap-10 list-none">
        <li v-for="link in links" :key="link.href">
          <a :href="link.href"
             class="text-[10px] font-semibold tracking-[0.25em] uppercase text-white/40
                    hover:text-[var(--blue)] transition-colors duration-300 relative group">
            {{ link.label }}
            <span class="absolute -bottom-0.5 left-0 w-0 h-px bg-[var(--blue)] group-hover:w-full transition-all duration-300"></span>
          </a>
        </li>
      </ul>

      <a href="#sugestoes" class="btn-primary hidden md:inline-block text-xs">Fale Conosco</a>

      <!-- Mobile menu btn -->
      <button class="md:hidden flex flex-col gap-1.5 p-2" @click="mobileOpen = !mobileOpen">
        <span class="block w-5 h-px bg-white/60 transition-all duration-300" :class="mobileOpen ? 'rotate-45 translate-y-2' : ''"></span>
        <span class="block w-5 h-px bg-white/60 transition-all duration-300" :class="mobileOpen ? 'opacity-0' : ''"></span>
        <span class="block w-5 h-px bg-white/60 transition-all duration-300" :class="mobileOpen ? '-rotate-45 -translate-y-2' : ''"></span>
      </button>
    </nav>

    <!-- Mobile menu -->
    <Transition name="mobile-menu">
      <div v-if="mobileOpen"
           class="fixed inset-0 z-40 bg-[#0a0a0a]/98 flex flex-col items-center justify-center gap-8">
        <LogoIcon :size="52" class="mb-4 opacity-60" />
        <a v-for="link in links" :key="link.href" :href="link.href"
           @click="mobileOpen = false"
           class="text-3xl font-black tracking-widest uppercase text-white/60 hover:text-[var(--blue)] transition-colors"
           style="font-family:'Barlow Condensed',sans-serif">
          {{ link.label }}
        </a>
        <a href="#sugestoes" @click="mobileOpen = false" class="btn-primary mt-4">Fale Conosco</a>
      </div>
    </Transition>

    <slot />

    <!-- FOOTER -->
    <footer class="px-10 md:px-16 py-16 border-t border-[var(--blue)]/10">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
        <div class="flex items-center gap-3">
          <LogoIcon :size="28" />
          <div>
            <p style="font-family:'Barlow Condensed',sans-serif" class="font-black text-sm tracking-[0.25em] uppercase text-white/40">
              IGREJA<span class="text-[var(--blue)]">.</span> EM CHARQUEADAS
            </p>
            <p class="text-[10px] tracking-[0.2em] uppercase text-white/20 mt-0.5">
              Charqueadas · Rio Grande do Sul · Brasil
            </p>
          </div>
        </div>
        <div class="flex flex-wrap gap-6 md:gap-10">
          <a v-for="link in links" :key="link.href" :href="link.href"
             class="text-[10px] tracking-[0.2em] uppercase text-white/25 hover:text-[var(--blue)] transition-colors">
            {{ link.label }}
          </a>
        </div>
        <p class="text-[10px] tracking-[0.15em] uppercase text-white/15">
          © {{ new Date().getFullYear() }} · Todos os direitos reservados
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import LogoIcon from '@/Components/LogoIcon.vue'

const scrolled   = ref(false)
const mobileOpen = ref(false)

function onScroll() { scrolled.value = window.scrollY > 60 }
onMounted(() => window.addEventListener('scroll', onScroll))
onUnmounted(() => window.removeEventListener('scroll', onScroll))

const links = [
  { href: '#sobre',      label: 'A Igreja' },
  { href: '#identidade', label: 'Identidade' },
  { href: '#agenda',     label: 'Agenda' },
  { href: '#sugestoes',  label: 'Contato' },
  { href: '#oracao',     label: 'Oração' },
]
</script>

<style scoped>
.mobile-menu-enter-active, .mobile-menu-leave-active { transition: opacity 0.3s; }
.mobile-menu-enter-from, .mobile-menu-leave-to { opacity: 0; }
</style>
