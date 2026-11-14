<template>
  <AdminLayout>

    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">iPad do púlpito</h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
        Aparelhos que mostram os roteiros de pregação no app Pregação. Cada um tem um token próprio.
      </p>
    </div>

    <div v-if="$page.props.flash?.error"
         class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm font-medium">
      {{ $page.props.flash.error }}
    </div>
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>

    <!-- TOKEN RECÉM-CRIADO (aparece uma vez) -->
    <div v-if="tokenNovo"
         class="mb-8 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-5">
      <p class="font-semibold text-amber-900 dark:text-amber-200">Token do aparelho</p>
      <p class="text-sm text-amber-800 dark:text-amber-300 mt-1 mb-3">
        Coloque este token na configuração do app Pregação. Por segurança ele não será mostrado de novo.
      </p>
      <div class="flex flex-col sm:flex-row gap-2">
        <code class="flex-1 break-all rounded-lg bg-white dark:bg-slate-900 border border-amber-200 dark:border-amber-800 px-3 py-2 text-sm text-gray-900 dark:text-slate-100">{{ tokenNovo.token }}</code>
        <button type="button" @click="copiar(tokenNovo.token)" :class="botaoSecundario">
          {{ copiado ? 'Copiado!' : 'Copiar' }}
        </button>
      </div>
      <p class="text-xs text-amber-700 dark:text-amber-400 mt-3">Endereço da API: <span class="font-mono">{{ apiUrl }}</span></p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">

      <!-- LISTA -->
      <div class="lg:col-span-2 space-y-3">
        <div v-if="dispositivos.length === 0"
             class="text-center py-16 text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl">
          <p class="font-medium">Nenhum aparelho cadastrado.</p>
          <p class="text-sm mt-1">Crie o primeiro ao lado.</p>
        </div>

        <div v-for="d in dispositivos" :key="d.id"
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-4
                    flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <p class="font-semibold text-gray-900 dark:text-white">{{ d.nome }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
              {{ d.ultimo_uso_em ? 'usado ' + quando(d.ultimo_uso_em) : 'nunca usado' }}
            </p>
          </div>
          <button type="button" @click="desligar(d)"
                  class="self-start sm:self-auto px-3 py-2 rounded-lg text-sm font-semibold border border-red-200 dark:border-red-900/60 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/30 transition">
            Desligar
          </button>
        </div>
      </div>

      <!-- NOVO APARELHO -->
      <form @submit.prevent="criar"
            class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 space-y-4 self-start">
        <p class="font-semibold text-gray-900 dark:text-white">Novo aparelho</p>
        <div>
          <label :class="label">Nome</label>
          <input v-model="form.nome" type="text" maxlength="60" placeholder="iPad do púlpito" :class="campo" />
          <p v-if="form.errors.nome" :class="erro">{{ form.errors.nome }}</p>
        </div>
        <button type="submit" :disabled="form.processing" :class="botaoPrimario + ' w-full'">Gerar token</button>
      </form>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps({
  dispositivos: { type: Array, default: () => [] },
  tokenNovo: { type: Object, default: null },
  apiUrl: { type: String, default: "" },
});

const label = "block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1";
const campo = "w-full rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500";
const erro = "text-xs text-red-600 dark:text-red-400 mt-1";
const botaoPrimario = "bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition disabled:opacity-50";
const botaoSecundario = "px-3 py-2 rounded-lg text-sm font-semibold border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 transition";

const form = useForm({ nome: "iPad do púlpito" });
const copiado = ref(false);

function criar() {
  form.post("/admin/pulpito", { preserveScroll: true });
}

function desligar(d) {
  if (!confirm(`Desligar "${d.nome}"? O app nesse aparelho para de baixar roteiros até receber um token novo.`)) return;
  router.delete(`/admin/pulpito/${d.id}`, { preserveScroll: true });
}

function copiar(texto) {
  navigator.clipboard?.writeText(texto).then(() => {
    copiado.value = true;
    setTimeout(() => { copiado.value = false; }, 2000);
  });
}

function quando(iso) {
  const d = new Date(iso);
  return d.toLocaleDateString("pt-BR", { day: "2-digit", month: "2-digit" }) + " às " +
    d.toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" });
}
</script>
