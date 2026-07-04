<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8">
      <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Conta</p>
      <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Meu Perfil</h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Atualize seus dados e as notificações no WhatsApp.</p>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <form @submit.prevent="submit" class="max-w-lg space-y-8">

      <!-- DADOS PESSOAIS -->
      <div class="space-y-5">
        <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500">Dados pessoais</h2>

        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Nome *</label>
          <input v-model="form.name" type="text"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.name }" />
          <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">E-mail *</label>
          <input v-model="form.email" type="email"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                 :class="{ 'border-red-400': form.errors.email }" />
          <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Telefone</label>
            <input v-model="form.telefone" type="text" placeholder="(51) 99999-9999"
                   class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Aniversário</label>
            <input v-model="form.data_nascimento" type="date"
                   class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>
        </div>
      </div>

      <!-- WHATSAPP -->
      <div class="space-y-5 border-t border-gray-100 dark:border-slate-700 pt-8">
        <div>
          <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-slate-500">Notificações no WhatsApp</h2>
          <p class="text-gray-500 dark:text-slate-400 text-sm mt-2">
            Para receber avisos e escalas no seu WhatsApp. É rápido: ative uma vez e cole a API Key aqui.
          </p>
        </div>

        <!-- PASSO A PASSO -->
        <div class="rounded-xl border border-green-200 dark:border-green-800/60 bg-green-50/60 dark:bg-green-900/10 p-4">
          <ol class="text-sm text-gray-700 dark:text-slate-300 space-y-2 list-decimal list-inside">
            <li>Toque no botão abaixo para abrir o WhatsApp com a mensagem de ativação já pronta e <strong>envie</strong>.</li>
            <li>O robô responde com <strong>"API Activated... Your APIKEY is 123123"</strong>.</li>
            <li>Copie esse número e cole no campo <strong>API Key</strong> aqui embaixo, depois salve.</li>
          </ol>
          <a :href="linkAtivacao" target="_blank" rel="noopener"
             class="mt-3 inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition">
            📲 Ativar no WhatsApp
          </a>
          <p class="mt-2 text-[11px] text-gray-400 dark:text-slate-500">
            Abre uma conversa com o robô (+34 644 86 70 49) e a mensagem "I allow callmebot to send me messages".
          </p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">API Key (CallMeBot)</label>
          <input v-model="form.callmebot_apikey" type="text" placeholder="Ex: 123456"
                 class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p class="mt-1 text-xs text-gray-400 dark:text-slate-500">Deixe em branco para não receber por WhatsApp.</p>
        </div>
      </div>

      <!-- AÇÕES -->
      <div class="flex items-center gap-3 pt-2">
        <button type="submit" :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
          {{ form.processing ? 'Salvando...' : 'Salvar alterações' }}
        </button>
        <Link href="/admin/minha-senha"
              class="text-sm font-semibold text-gray-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 px-4 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition">
          Alterar senha
        </Link>
      </div>

    </form>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ perfil: Object })

const form = useForm({
  name:             props.perfil?.name ?? '',
  email:            props.perfil?.email ?? '',
  telefone:         props.perfil?.telefone ?? '',
  data_nascimento:  props.perfil?.data_nascimento ?? '',
  callmebot_apikey: props.perfil?.callmebot_apikey ?? '',
})

// Número do robô CallMeBot + mensagem de ativação pronta
const linkAtivacao = `https://wa.me/34644867049?text=${encodeURIComponent('I allow callmebot to send me messages')}`

function submit() {
  form.put('/admin/perfil', { preserveScroll: true })
}
</script>
