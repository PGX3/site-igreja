<template>
    <div class="flex h-screen bg-[#f8fafc] dark:bg-slate-950 overflow-hidden transition-colors duration-200">

        <!-- ─────────────────── BACKDROP MOBILE ─────────────────── -->
        <Transition name="fade">
            <div v-if="sidebarOpen"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 bg-black/50 z-20 lg:hidden" />
        </Transition>

        <!-- ─────────────────── SIDEBAR ─────────────────── -->
        <aside
            class="w-[260px] bg-white dark:bg-slate-900 border-r border-gray-100 dark:border-slate-700/50
                   flex flex-col fixed inset-y-0 left-0 z-30 transition-all duration-300"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <!-- Logo -->
            <div class="h-16 px-5 flex items-center gap-3 border-b border-gray-100 dark:border-slate-700/50 flex-shrink-0">
                <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm">
                    <LogoIcon :size="18" class="text-white" />
                </div>
                <span style="font-family: Inter, Arial, sans-serif"
                      class="font-black text-sm tracking-[0.22em] uppercase text-gray-900 dark:text-white select-none">
                    IGREJA<span class="text-blue-600">.</span>
                </span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 overflow-y-auto">
                <template v-for="(grupo, gIdx) in navGroups" :key="grupo.label || `g-${gIdx}`">
                    <p v-if="grupo.label"
                       class="px-3 mb-2 text-[10px] font-bold tracking-[0.3em] uppercase text-gray-400 dark:text-slate-500 select-none"
                       :class="gIdx > 0 ? 'mt-5' : ''">
                        {{ grupo.label }}
                    </p>
                    <div v-else-if="gIdx > 0"
                         class="my-3 mx-3 border-t border-gray-100 dark:border-slate-700/50"></div>

                    <Link v-for="item in grupo.items" :key="item.href"
                          :href="item.href"
                          @click="sidebarOpen = false"
                          class="flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] font-medium transition-all duration-150 mb-0.5"
                          :class="isActive(item.href)
                              ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-semibold'
                              : 'text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-slate-800'">
                        <span class="flex-shrink-0"
                              :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-400 dark:text-slate-500'">
                            <AppIcon :name="item.icon" size="sm" />
                        </span>
                        <span class="flex-1 leading-none">{{ item.label }}</span>
                        <span v-if="item.badge && item.badge > 0"
                              class="text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center leading-tight"
                              :class="item.badgeColor ?? 'bg-blue-600 text-white'">
                            {{ item.badge }}
                        </span>
                    </Link>
                </template>
            </nav>

            <!-- User + Logout -->
            <div class="flex-shrink-0 border-t border-gray-100 dark:border-slate-700/50 p-3 space-y-1">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                         :style="{ background: avatarColor }">
                        {{ initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-gray-800 dark:text-slate-100 truncate leading-tight">
                            {{ user?.name }}
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-slate-500 leading-tight mt-0.5">
                            {{ roleLabel }}
                        </p>
                    </div>
                </div>

                <Link href="/admin/minha-senha"
                      @click="sidebarOpen = false"
                      class="flex items-center gap-2.5 w-full px-3 py-2 rounded-xl text-[13px] font-medium transition-colors"
                      :class="page.url.startsWith('/admin/minha-senha')
                          ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-semibold'
                          : 'text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800'">
                    <AppIcon name="lock" size="sm" />
                    Alterar Senha
                </Link>

                <Link href="/logout"
                      method="post"
                      as="button"
                      @click="sidebarOpen = false"
                      class="flex items-center gap-2.5 w-full px-3 py-2 rounded-xl text-[13px] font-medium
                             text-gray-400 dark:text-slate-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <AppIcon name="logout" size="sm" />
                    Sair
                </Link>
            </div>
        </aside>

        <!-- ─────────────────── CONTEÚDO ─────────────────── -->
        <div class="flex-1 lg:ml-[260px] flex flex-col min-h-0">

            <!-- Top bar -->
            <header class="h-14 bg-white dark:bg-slate-900 border-b border-gray-100 dark:border-slate-700/50
                           flex items-center px-4 lg:px-6 gap-3 flex-shrink-0 z-10 transition-colors duration-200">

                <!-- Hamburger (mobile) -->
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center
                               text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors flex-shrink-0">
                    <svg width="18" height="14" viewBox="0 0 18 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="18" height="2" rx="1" fill="currentColor"/>
                        <rect y="6" width="18" height="2" rx="1" fill="currentColor"/>
                        <rect y="12" width="18" height="2" rx="1" fill="currentColor"/>
                    </svg>
                </button>

                <!-- Breadcrumb -->
                <div class="flex items-center gap-1.5 text-sm min-w-0 flex-1">
                    <Link href="/admin"
                          class="text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-slate-300 transition-colors flex-shrink-0"
                          :class="pageTitle ? '' : 'font-semibold text-gray-700 dark:text-slate-200'">
                        Painel
                    </Link>
                    <template v-if="pageTitle">
                        <span class="text-gray-300 dark:text-slate-600 flex-shrink-0">/</span>
                        <span class="font-semibold text-gray-800 dark:text-slate-100 truncate">{{ pageTitle }}</span>
                    </template>
                </div>

                <!-- Tema -->
                <div class="flex items-center gap-1">
                    <button @click="toggleTheme"
                            class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 dark:text-slate-400
                                   hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
                            :title="isDark ? 'Modo claro' : 'Modo escuro'">
                        <AppIcon :name="isDark ? 'sun' : 'moon'" size="md" />
                    </button>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import LogoIcon from "@/Components/LogoIcon.vue";
import AppIcon from "@/Components/AppIcon.vue";
import { useTheme } from "@/composables/useTheme.js";

const { isDark, toggle: toggleTheme } = useTheme();
const sidebarOpen = ref(false);

const page = usePage();
const user = computed(() => page.props.auth?.user);
const role = computed(() => user.value?.role);

const roleLabel = computed(
    () =>
        ({
            pastor: "Administrador",
            lider: "Líder",
            membro: "Membro",
        })[role.value] ??
        role.value ??
        "",
);

const initials = computed(() => {
    const parts = (user.value?.name ?? "").split(" ").filter(Boolean);
    if (!parts.length) return "?";
    return (
        parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : "")
    ).toUpperCase();
});

const avatarColor = computed(() => {
    const colors = ["#f97316", "#3b82f6", "#8b5cf6", "#10b981", "#ef4444", "#f59e0b"];
    const code = (user.value?.name ?? "A").charCodeAt(0);
    return colors[code % colors.length];
});

const routeTitles = {
    "/admin": null,
    "/admin/membros": "Membros",
    "/admin/visitantes": "Visitantes",
    "/admin/aniversarios": "Aniversários",
    "/admin/escalas": "Escalas",
    "/admin/musicas": "Músicas",
    "/admin/assets": "Anexos",
    "/admin/grupos": "Grupos",
    "/admin/cultos": "Cultos",
    "/admin/eventos": "Eventos",
    "/admin/usuarios": "Usuários do Sistema",
    "/admin/sugestoes": "Sugestões",
    "/admin/pedidos-oracao": "Pedidos de Oração",
    "/admin/minhas-escalas": "Minhas Escalas",
    "/admin/minha-senha": "Alterar Senha",
    "/admin/familias": "Famílias",
};

const pageTitle = computed(() => {
    const url = page.url.split("?")[0].replace(/\/$/, "");
    if (url === "/admin" || url === "/admin/") return null;
    if (routeTitles[url] !== undefined) return routeTitles[url];
    const match = Object.keys(routeTitles)
        .filter((k) => k !== "/admin" && url.startsWith(k))
        .sort((a, b) => b.length - a.length)[0];
    return match ? routeTitles[match] : null;
});

const navGroups = computed(() => {
    const isPastor = role.value === "pastor";
    const isLider  = role.value === "lider";
    const groups   = [];

    groups.push({
        label: "Início",
        items: [
            { href: "/admin",             label: "Painel",         icon: "dashboard" },
            { href: "/admin/minhas-escalas", label: "Minhas Escalas", icon: "clock" },
        ],
    });

    if (isPastor || isLider) {
        const anivBadge = page.props.aniversariantesHoje || 0;
        groups.push({
            label: "Pastoral",
            items: [
                { href: "/admin/familias",   label: "Famílias",    icon: "users" },
                { href: "/admin/membros",    label: "Membros",     icon: "user" },
                {
                    href: "/admin/visitantes", label: "Visitantes", icon: "user",
                    badge: page.props.novosVisitantesMes || 0,
                    badgeColor: "bg-amber-500 text-white",
                },
                {
                    href: "/admin/aniversarios", label: "Aniversários", icon: "gift",
                    badge: anivBadge > 0 ? anivBadge : 0,
                    badgeColor: "bg-purple-500 text-white",
                },
            ],
        });

        const gestao = [
            { href: "/admin/escalas", label: "Escalas", icon: "calendar" },
            { href: "/admin/musicas", label: "Músicas", icon: "mic" },
            { href: "/admin/assets", label: "Anexos", icon: "file-text" },
        ];
        if (isPastor) {
            gestao.push({ href: "/admin/grupos",   label: "Grupos",   icon: "users" });
            gestao.push({ href: "/admin/cultos",   label: "Cultos",   icon: "mic" });
            gestao.push({ href: "/admin/eventos",  label: "Eventos",  icon: "calendar" });
        }
        groups.push({ label: "Gestão", items: gestao });
    } else {
        // Membros: apenas seus grupos
        groups.push({
            label: "Grupos",
            items: [{ href: "/admin/grupos", label: "Meus Grupos", icon: "users" }],
        });
    }

    if (isPastor) {
        groups.push({
            label: "Administração",
            items: [
                { href: "/admin/usuarios", label: "Usuários do Sistema", icon: "user" },
                {
                    href: "/admin/sugestoes", label: "Sugestões", icon: "lightbulb",
                    badge: page.props.novasSugestoes || 0,
                },
                {
                    href: "/admin/pedidos-oracao", label: "Pedidos de Oração", icon: "heart",
                    badge: page.props.novosPedidos || 0,
                },
            ],
        });
    }

    groups.push({
        label: null,
        items: [{ href: "/", label: "Ver Site", icon: "external-link" }],
    });

    return groups;
});

function isActive(href) {
    if (href === "/admin") return page.url === "/admin" || page.url === "/admin/";
    return page.url.startsWith(href);
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
