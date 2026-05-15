<template>
  <div class="min-h-screen bg-[#f6f8fb] text-gray-800 flex">

    <!-- SIDEBAR -->
    <aside class="w-60 bg-white border-r border-gray-200 flex flex-col fixed h-full">

      <!-- LOGO -->
      <div class="px-6 py-6 border-b border-gray-200">
        <Link href="/" class="flex items-center gap-2.5 group">
          <LogoIcon :size="28" class="transition-transform group-hover:scale-110" />
          <span style="font-family: Inter, Arial, sans-serif"
                class="font-black text-sm tracking-[0.2em] uppercase text-gray-900">
            ADMIN<span class="text-blue-600">.</span>
          </span>
        </Link>
      </div>

      <!-- MENU -->
      <nav class="flex-1 px-4 py-6 overflow-y-auto">
        <p class="text-[10px] font-bold tracking-[0.35em] uppercase text-gray-400 px-3 mb-3">
          Menu
        </p>

        <Link
          v-for="item in navItems"
          :key="item.href"
          :href="item.href"
          class="flex items-center gap-3 px-3 py-2.5 rounded text-[13px] font-semibold
                 transition-all duration-150 mb-1 group"
          :class="isActive(item.href)
            ? 'text-blue-600 bg-blue-50 border-l-2 border-blue-600 pl-[10px]'
            : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100'"
        >
          <span class="text-[12px] w-4 text-center flex-shrink-0"
                :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-400'">
            {{ item.icon }}
          </span>

          <span class="flex-1">{{ item.label }}</span>

          <span v-if="item.badge && item.badge > 0"
                class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-600 text-white min-w-[20px] text-center">
            {{ item.badge }}
          </span>
        </Link>
      </nav>

      <!-- LOGOUT -->
      <div class="px-4 py-6 border-t border-gray-200">
        <Link href="/logout" method="post" as="button"
              class="flex items-center gap-3 px-3 py-2.5 w-full text-[13px] font-semibold
                     text-gray-400 hover:text-red-500 transition-colors">
          <span class="text-[12px]">✕</span>
          Sair
        </Link>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 ml-60 p-8"> 

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
  { href: '/admin/pedidos-oracao', label: 'Pedidos de Oração',  icon: '✦', badge: page.props.novosPedidos || 0 },
  { href: '/',                     label: 'Ver Site',           icon: '↗', badge: 0 },
])

function isActive(href) {
  return page.url.startsWith(href) &&
    (href !== '/admin' || page.url === '/admin' || page.url === '/admin/')
}
</script>

<style scoped>
.slide-down-enter-active {
  transition: all 0.3s ease;
}
.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}
</style>