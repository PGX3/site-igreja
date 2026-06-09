<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Membros</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Aniversários</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Ordenados por proximidade do aniversário
        </p>
      </div>
      <Link href="/admin"
            class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400
                   px-4 py-2 rounded-lg border border-gray-200 dark:border-slate-600 transition-colors">
        ← Voltar ao Painel
      </Link>
    </div>

    <!-- COM ANIVERSÁRIO -->
    <div v-if="comAniversario.length" class="mb-10">
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">

        <!-- Cabeçalho da tabela -->
        <div class="grid grid-cols-12 px-6 py-3 border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
          <div class="col-span-5 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Membro</div>
          <div class="col-span-3 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Data</div>
          <div class="col-span-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 text-center">Idade</div>
          <div class="col-span-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 text-right">Faltam</div>
        </div>

        <!-- Linhas -->
        <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
          <div v-for="a in comAniversario" :key="a.id"
               :class="a.hoje
                 ? 'bg-gradient-to-r from-purple-50/60 to-pink-50/60 dark:from-purple-900/10 dark:to-pink-900/10'
                 : a.esta_semana
                   ? 'bg-amber-50/40 dark:bg-amber-900/5'
                   : ''"
               class="grid grid-cols-12 px-6 py-4 items-center hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">

            <!-- Membro -->
            <div class="col-span-5 flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                   :style="{ background: a.color }">
                {{ a.initials }}
              </div>
              <div>
                <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ a.name }}</p>
                <span v-if="a.hoje"
                      class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-0.5
                             bg-pink-100 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400">
                  🎉 Hoje!
                </span>
                <span v-else-if="a.esta_semana"
                      class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-0.5
                             bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                  Esta semana
                </span>
              </div>
            </div>

            <!-- Data -->
            <div class="col-span-3">
              <p class="text-sm text-gray-700 dark:text-slate-300 capitalize">{{ a.data_fmt }}</p>
            </div>

            <!-- Idade -->
            <div class="col-span-2 text-center">
              <span class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ a.idade }} anos</span>
            </div>

            <!-- Faltam -->
            <div class="col-span-2 text-right">
              <span v-if="a.hoje"
                    class="inline-block text-xs font-bold px-2.5 py-1 rounded-full
                           bg-pink-100 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400">
                Hoje
              </span>
              <span v-else
                    class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full
                           bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400">
                {{ a.dias_restantes }}d
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SEM DATA DE NASCIMENTO -->
    <div v-if="semAniversario.length">
      <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500 mb-3">
        Sem data de nascimento cadastrada
      </p>
      <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
          <div v-for="u in semAniversario" :key="u.id"
               class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                 :style="{ background: u.color }">
              {{ u.initials }}
            </div>
            <p class="text-sm text-gray-500 dark:text-slate-400">{{ u.name }}</p>
            <Link :href="`/admin/usuarios/${u.id}/edit`"
                  class="ml-auto text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
              Completar →
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- VAZIO -->
    <div v-if="!comAniversario.length && !semAniversario.length"
         class="py-20 text-center text-gray-400 dark:text-slate-500">
      <p class="text-4xl mb-3">🎂</p>
      <p class="font-medium">Nenhum membro cadastrado ainda.</p>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  comAniversario: Array,
  semAniversario: Array,
})
</script>
