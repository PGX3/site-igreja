<template>
  <AdminLayout>
    <h1 class="text-3xl font-black tracking-tight mb-2">Textos do Site</h1>
    <p class="text-gray-500 text-sm mb-10">Edite os textos que aparecem nas seções do site.</p>

    <div class="flex flex-col gap-0.5">
      <div v-for="texto in textos" :key="texto.id"
           class="bg-[#1a1a1a] flex items-start gap-6 px-8 py-6">
        <div class="flex-1">
          <p class="text-[10px] font-bold tracking-[0.2em] uppercase text-[#29b6f6] mb-1">{{ texto.label }}</p>
          <p class="text-white/60 text-sm leading-relaxed line-clamp-2">{{ texto.conteudo }}</p>
        </div>
        <Link :href="`/admin/textos/${texto.id}/edit`"
              class="text-xs text-gray-500 hover:text-white transition-colors tracking-wide shrink-0 mt-1">
          Editar
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
defineProps({ textos: Array })
</script>
