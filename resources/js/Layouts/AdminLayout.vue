<template>
  <div class="min-h-screen bg-[#080808] text-white flex">
    <aside class="w-60 border-r border-[var(--blue)]/10 flex flex-col fixed h-full" style="background:#0c0c0c">
      <div class="px-6 py-6 border-b border-[var(--blue)]/10">
        <Link href="/" class="flex items-center gap-2.5 group">
          <LogoIcon :size="28" class="transition-transform group-hover:scale-110" />
          <span style="font-family:'Barlow Condensed',sans-serif" class="font-black text-sm tracking-[0.2em] uppercase">
            ADMIN<span class="text-[var(--blue)]">.</span>
          </span>
        </Link>
      </div>

      <nav class="flex-1 px-4 py-6 overflow-y-auto">
        <p class="text-[8px] font-bold tracking-[0.35em] uppercase text-white/20 px-3 mb-3">Menu</p>
        <Link
          v-for="item in navItems" :key="item.href"
          :href="item.href"
          class="flex items-center gap-3 px-3 py-2.5 rounded text-[12px] font-semibold
                 transition-all duration-150 mb-0.5 group"
          :class="isActive(item.href)
            ? 'text-[var(--blue)] bg-[var(--blue)]/8 border-l-2 border-[var(--blue)] pl-[10px]'
            : 'text-white/40 hover:text-white/80 hover:bg-white/4'"
        >
          <span class="text-[11px] w-4 text-center flex-shrink-0"
                :class="isActive(item.href) ? 'text-[var(--blue)]' : 'text-white/25'">
            {{ item.icon }}
          </span>
          <span class="flex-1">{{ item.label }}</span>
          <span v-if="item.badge && item.badge > 0"
                class="text-[9px] font-black px-1.5 py-0.5 rounded-full bg-[var(--blue)] text-white min-w-[18px] text-center">
            {{ item.badge }}
          </span>
        </Link>
      </nav>

      <div class="px-4 py-6 border-t border-[var(--blue)]/10">
        <Link href="/logout" method="post" as="button"
              class="flex items-center gap-3 px-3 py-2.5 w-full text-[12px] font-semibold
                     text-white/25 hover:text-red-400 transition-colors">
          <span class="text-[11px]">✕</span>Sair
        </Link>
      </div>
    </aside>

    <main class="ml-60 flex-1 p-10">
      <Transition name="slide-down">
        <div v-if="$page.props.flash?.success"
             class="mb-8 px-5 py-4 border-l-2 border-[var(--blue)] bg-[var(--blue)]/5
                    text-[var(--blue)] text-[12px] font-semibold tracking-wide">
          {{ $page.props.flash.success }}
        </div>
      </Transition>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LogoIcon from '@/Components/LogoIcon.vue'

const page = usePage()

const navItems = computed(() => [
  { href: '/admin',                label: 'Dashboard',          icon: '◈', badge: 0 },
  { href: '/admin/cultos',         label: 'Cultos',             icon: '◉', badge: 0 },
  { href: '/admin/textos',         label: 'Textos',             icon: '◎', badge: 0 },
  { href: '/admin/sugestoes',      label: 'Sugestões',          icon: '✉', badge: page.props.novasSugestoes || 0 },
  { href: '/admin/pedidos-oracao', label: 'Pedidos de Oração',  icon: '✦', badge: page.props.novosPedidos   || 0 },
  { href: '/',                     label: 'Ver Site',           icon: '↗', badge: 0 },
])

function isActive(href) {
  return page.url.startsWith(href) && (href !== '/admin' || page.url === '/admin' || page.url === '/admin/')
}
</script>

<style scoped>
.slide-down-enter-active { transition: all 0.3s ease; }
.slide-down-enter-from { opacity: 0; transform: translateY(-8px); }
</style>
