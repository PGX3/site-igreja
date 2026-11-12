<template>
  <AdminLayout>

    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">iPad da recepção</h1>
      <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
        Aparelhos que cadastram visitantes pelo app Recepção. Cada um tem um token próprio e um PIN para o modo diácono.
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
        Coloque este token na configuração do app Recepção. Por segurança ele não será mostrado de novo.
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
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-4">
          <div v-if="editando !== d.id" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
              <p class="font-semibold text-gray-900 dark:text-white">{{ d.nome }}</p>
              <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                {{ d.cadastros }} cadastro(s) · {{ d.ultimo_uso_em ? 'usado ' + quando(d.ultimo_uso_em) : 'nunca usado' }}
                · <span :class="d.tem_pin ? '' : 'text-red-600 dark:text-red-400'">{{ d.tem_pin ? 'PIN definido' : 'sem PIN' }}</span>
              </p>
            </div>
            <div class="flex gap-2 self-start sm:self-auto">
              <button type="button" @click="abrirEdicao(d)" :class="botaoSecundario">Editar</button>
              <button type="button" @click="desligar(d)"
                      class="px-3 py-2 rounded-lg text-sm font-semibold border border-red-200 dark:border-red-900/60 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                Desligar
              </button>
            </div>
          </div>

          <form v-else @submit.prevent="salvarEdicao(d)" class="grid gap-3 sm:grid-cols-3 sm:items-end">
            <div>
              <label :class="label">Nome</label>
              <input v-model="formEdicao.nome" type="text" maxlength="60" :class="campo" />
              <p v-if="formEdicao.errors.nome" :class="erro">{{ formEdicao.errors.nome }}</p>
            </div>
            <div>
              <label :class="label">Novo PIN (opcional)</label>
              <input v-model="formEdicao.pin" type="password" inputmode="numeric" maxlength="8" placeholder="manter o atual" :class="campo" />
              <p v-if="formEdicao.errors.pin" :class="erro">{{ formEdicao.errors.pin }}</p>
            </div>
            <div class="flex gap-2">
              <button type="submit" :disabled="formEdicao.processing" :class="botaoPrimario">Salvar</button>
              <button type="button" @click="editando = null" :class="botaoSecundario">Cancelar</button>
            </div>
          </form>
        </div>
      </div>

      <!-- NOVO APARELHO -->
      <form @submit.prevent="criar"
            class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm p-5 space-y-4 self-start">
        <p class="font-semibold text-gray-900 dark:text-white">Novo aparelho</p>
        <div>
          <label :class="label">Nome</label>
          <input v-model="form.nome" type="text" maxlength="60" placeholder="iPad da recepção" :class="campo" />
          <p v-if="form.errors.nome" :class="erro">{{ form.errors.nome }}</p>
        </div>
        <div>
          <label :class="label">PIN do diácono (4 a 8 números)</label>
          <input v-model="form.pin" type="password" inputmode="numeric" maxlength="8" :class="campo" />
          <p v-if="form.errors.pin" :class="erro">{{ form.errors.pin }}</p>
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

const form = useForm({ nome: "iPad da recepção", pin: "" });
const formEdicao = useForm({ nome: "", pin: "" });
const editando = ref(null);
const copiado = ref(false);

function criar() {
  form.post("/admin/recepcao", {
    preserveScroll: true,
    onSuccess: () => form.reset("pin"),
  });
}

function abrirEdicao(d) {
  editando.value = d.id;
  formEdicao.clearErrors();
  formEdicao.nome = d.nome;
  formEdicao.pin = "";
}

function salvarEdicao(d) {
  formEdicao.put(`/admin/recepcao/${d.id}`, {
    preserveScroll: true,
    onSuccess: () => { editando.value = null; },
  });
}

function desligar(d) {
  if (!confirm(`Desligar "${d.nome}"? O app nesse aparelho para de cadastrar até receber um token novo.`)) return;
  router.delete(`/admin/recepcao/${d.id}`, { preserveScroll: true });
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
