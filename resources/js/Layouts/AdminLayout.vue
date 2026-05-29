<template>
  <div class="flex h-screen bg-[#f8fafc] overflow-hidden">

    <!-- ─────────────────── SIDEBAR ─────────────────── -->
    <aside class="w-[260px] bg-white border-r border-gray-100 flex flex-col fixed inset-y-0 left-0 z-20">

      <!-- Logo -->
      <div class="h-16 px-5 flex items-center gap-3 border-b border-gray-100 flex-shrink-0">
        <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm">
          <LogoIcon :size="18" class="text-white" />
        </div>
        <span style="font-family: Inter, Arial, sans-serif"
              class="font-black text-sm tracking-[0.22em] uppercase text-gray-900 select-none">
          IGREJA<span class="text-blue-600">.</span>
        </span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <p class="px-3 mb-2 text-[10px] font-bold tracking-[0.3em] uppercase text-gray-400 select-none">
          Menu
        </p>

        <Link
          v-for="item in navItems"
          :key="item.href"
          :href="item.href"
          class="flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] font-medium
                 transition-all duration-150 mb-0.5"
          :class="isActive(item.href)
            ? 'bg-blue-50 text-blue-700 font-semibold'
            : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'"
        >
          <span class="flex-shrink-0"
                :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-400'">
            <AppIcon :name="item.icon" size="sm" />
          </span>

          <span class="flex-1 leading-none">{{ item.label }}</span>

          <span v-if="item.badge && item.badge > 0"
                class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                       bg-blue-600 text-white min-w-[18px] text-center leading-tight">
            {{ item.badge }}
          </span>
        </Link>
      </nav>

      <!-- User + Logout -->
      <div class="flex-shrink-0 border-t border-gray-100 p-3 space-y-1">
        <div class="flex items-center gap-3 px-2 py-2">
          <div class="w-8 h-8 rounded-full flex items-center justify-center
                      text-xs font-bold text-white flex-shrink-0"
               :style="{ background: avatarColor }">
            {{ initials }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[13px] font-semibold text-gray-800 truncate leading-tight">{{ user?.name }}</p>
            <p class="text-[11px] text-gray-400 leading-tight mt-0.5">{{ roleLabel }}</p>
          </div>
        </div>

        <Link href="/logout" method="post" as="button"
              class="flex items-center gap-2.5 w-full px-3 py-2 rounded-xl text-[13px] font-medium
                     text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
          <AppIcon name="logout" size="sm" />
          Sair
        </Link>
      </div>
    </aside>

    <!-- ─────────────────── RIGHT SIDE ─────────────────── -->
    <div class="flex-1 ml-[260px] flex flex-col min-h-0">

      <!-- Top bar -->
      <header class="h-14 bg-white border-b border-gray-100 flex items-center
                     px-6 gap-4 flex-shrink-0 z-10">

        <!-- Search -->
        <div class="flex-1 max-w-sm">
          <div class="flex items-center gap-2 bg-gray-50 border border-gray-100
                      rounded-xl px-3 py-2 text-sm text-gray-400 cursor-default
                      hover:border-gray-200 transition-colors">
            <AppIcon name="search" size="xs" class="text-gray-300 flex-shrink-0" />
            <span class="flex-1 text-[13px] select-none">Buscar membros, cultos, textos...</span>
            <kbd class="text-[10px] font-mono bg-gray-100 border border-gray-200
                        text-gray-400 px-1.5 py-0.5 rounded-md select-none">⌘K</kbd>
          </div>
        </div>

        <!-- Actions -->
        <div class="ml-auto flex items-center gap-1">
          <button class="relative w-9 h-9 rounded-xl flex items-center justify-center
                         text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <AppIcon name="bell" size="md" />
            <span v-if="totalBadges > 0"
                  class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white" />
          </button>
          <button class="w-9 h-9 rounded-xl flex items-center justify-center
                         text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <AppIcon name="moon" size="md" />
          </button>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-8">
        <slot />
      </main>
    </div>

  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import LogoIcon from '@/Components/LogoIcon.vue'
import AppIcon from '@/Components/AppIcon.vue'

const page = usePage()

const user  = computed(() => page.props.auth?.user)
const role  = computed(() => user.value?.role)

const roleLabel = computed(() => ({
  pastor: 'Administrador',
  lider:  'Líder',
  membro: 'Membro',
}[role.value] ?? role.value ?? ''))

const initials = computed(() => {
  const parts = (user.value?.name ?? '').split(' ').filter(Boolean)
  if (!parts.length) return '?'
  return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase()
})

const avatarColor = computed(() => {
  const colors = ['#f97316','#3b82f6','#8b5cf6','#10b981','#ef4444','#f59e0b']
  const code = (user.value?.name ?? 'A').charCodeAt(0)
  return colors[code % colors.length]
})

const totalBadges = computed(() =>
  (page.props.novasSugestoes || 0) + (page.props.novosPedidos || 0)
)

const navItems = computed(() => {
  const items = []
  items.push({ href: '/admin',              label: 'Dashboard',         icon: 'dashboard' })
  items.push({ href: '/admin/minhas-escalas', label: 'Minhas Escalas',  icon: 'clock' })

  if (role.value === 'pastor' || role.value === 'lider') {
    items.push({ href: '/admin/escalas', label: 'Escalas', icon: 'calendar' })
  }

  if (role.value === 'pastor') {
    items.push({ href: '/admin/grupos',         label: 'Grupos',            icon: 'users' })
    items.push({ href: '/admin/usuarios',       label: 'Usuários',          icon: 'user' })
    items.push({ href: '/admin/cultos',         label: 'Cultos',            icon: 'mic' })
    items.push({ href: '/admin/sugestoes',      label: 'Sugestões',         icon: 'lightbulb',
                 badge: page.props.novasSugestoes || 0 })
    items.push({ href: '/admin/pedidos-oracao', label: 'Pedidos de Oração', icon: 'heart',
                 badge: page.props.novosPedidos || 0 })
  }

  items.push({ href: '/', label: 'Ver Site', icon: 'external-link' })
  return items
})

function isActive(href) {
  if (href === '/admin') return page.url === '/admin' || page.url === '/admin/'
  return page.url.startsWith(href)
}
</script>
