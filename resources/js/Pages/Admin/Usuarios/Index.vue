<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Gestão</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Usuários</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">{{ usuarios.length }} usuário(s) cadastrado(s)</p>
      </div>
      <Link href="/admin/usuarios/create"
            class="self-start sm:self-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Usuário
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error"
         class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-sm font-medium">
      {{ $page.props.flash.error }}
    </div>

    <!-- EMPTY STATE -->
    <div v-if="!usuarios.length"
         class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl py-20 text-center text-gray-400 dark:text-slate-500">
      <p class="text-4xl mb-3">◈</p>
      <p class="font-medium">Nenhum usuário cadastrado.</p>
    </div>

    <template v-else>

      <!-- CARDS (mobile) -->
      <div class="sm:hidden space-y-3">
        <div v-for="u in usuarios" :key="u.id"
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
          <!-- Nome + papel -->
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="min-w-0">
              <p class="font-semibold text-gray-900 dark:text-white truncate">{{ u.name }}</p>
              <p class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ u.email }}</p>
            </div>
            <span :class="roleBadgeClass(u.role?.name)"
                  class="text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0">
              {{ u.role?.display_name ?? '—' }}
            </span>
          </div>
          <!-- Grupos -->
          <div v-if="u.grupos?.length" class="flex flex-wrap gap-1 mb-3">
            <span v-for="g in u.grupos.slice(0, 3)" :key="g.id"
                  class="text-[10px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap
                         bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
              {{ g.nome }}
            </span>
            <span v-if="u.grupos.length > 3"
                  class="text-[10px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap cursor-default
                         bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400"
                  :title="u.grupos.slice(3).map(g => g.nome).join(', ')">
              +{{ u.grupos.length - 3 }}
            </span>
          </div>
          <!-- Telefone + ações -->
          <div class="flex items-center justify-between gap-2">
            <span class="text-xs text-gray-500 dark:text-slate-400">{{ u.telefone ?? 'Sem telefone' }}</span>
            <div class="flex gap-1">
              <button @click="abrirAlterarSenha(u)"
                      class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 px-2.5 py-1.5 rounded hover:bg-amber-50 dark:hover:bg-amber-900/20 transition">
                Senha
              </button>
              <Link :href="`/admin/usuarios/${u.id}/edit`"
                    class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-2.5 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                Editar
              </Link>
              <button @click="confirmarExclusao(u)"
                      class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-2.5 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                Excluir
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TABELA (desktop) -->
      <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[560px]">
            <thead class="border-b border-gray-100 dark:border-slate-700">
              <tr>
                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Nome</th>
                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Papel</th>
                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Grupo</th>
                <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Telefone</th>
                <th class="px-6 py-4"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in usuarios" :key="u.id"
                  class="border-b border-gray-50 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                <td class="px-6 py-4">
                  <p class="font-semibold text-gray-900 dark:text-white">{{ u.name }}</p>
                  <p class="text-xs text-gray-400 dark:text-slate-500">{{ u.email }}</p>
                </td>
                <td class="px-6 py-4">
                  <span :class="roleBadgeClass(u.role?.name)" class="text-xs font-bold px-2.5 py-1 rounded-full">
                    {{ u.role?.display_name ?? '—' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div v-if="u.grupos?.length" class="flex flex-wrap gap-1 items-center">
                    <span v-for="g in u.grupos.slice(0, 2)" :key="g.id"
                          class="text-[10px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap
                                 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                      {{ g.nome }}
                    </span>
                    <span v-if="u.grupos.length > 2"
                          class="text-[10px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap cursor-default
                                 bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400"
                          :title="u.grupos.slice(2).map(g => g.nome).join(', ')">
                      +{{ u.grupos.length - 2 }}
                    </span>
                  </div>
                  <span v-else class="text-gray-300 dark:text-slate-600 italic text-xs">—</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">{{ u.telefone ?? '—' }}</td>
                <td class="px-6 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="abrirAlterarSenha(u)"
                            class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 px-3 py-1.5 rounded hover:bg-amber-50 dark:hover:bg-amber-900/20 transition">
                      Senha
                    </button>
                    <Link :href="`/admin/usuarios/${u.id}/edit`"
                          class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                      Editar
                    </Link>
                    <button @click="confirmarExclusao(u)"
                            class="text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                      Excluir
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </template>

    <!-- MODAL ALTERAR SENHA -->
    <div v-if="usuarioSenha"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Alterar senha</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-5">
          Definir nova senha para <strong>{{ usuarioSenha.name }}</strong>.
        </p>

        <form @submit.prevent="salvarSenha" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1.5">Nova senha *</label>
            <input v-model="formSenha.password" type="password" placeholder="Mínimo 6 caracteres"
                   class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                          bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                          placeholder-gray-400 dark:placeholder-slate-400
                          focus:outline-none focus:ring-2 focus:ring-blue-500"
                   :class="{ 'border-red-400': formSenha.errors.password }" />
            <p v-if="formSenha.errors.password" class="mt-1 text-xs text-red-500">{{ formSenha.errors.password }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-slate-400 mb-1.5">Confirmar *</label>
            <input v-model="formSenha.password_confirmation" type="password" placeholder="Repita a nova senha"
                   class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-4 py-2.5 text-sm
                          bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                          placeholder-gray-400 dark:placeholder-slate-400
                          focus:outline-none focus:ring-2 focus:ring-blue-500" />
          </div>

          <div class="flex gap-3 justify-end pt-1">
            <button type="button" @click="fecharSenha"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
              Cancelar
            </button>
            <button type="submit" :disabled="formSenha.processing"
                    class="px-4 py-2 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 disabled:opacity-50 rounded-lg transition">
              Salvar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL EXCLUSÃO -->
    <div v-if="usuarioParaExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 dark:text-white mb-2">Excluir usuário?</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-6">
          "<strong>{{ usuarioParaExcluir.name }}</strong>" será removido permanentemente.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="usuarioParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">
            Cancelar
          </button>
          <Link :href="`/admin/usuarios/${usuarioParaExcluir.id}`" method="delete" as="button"
                class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
            Excluir
          </Link>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({ usuarios: Array })

// ── Alterar Senha ──────────────────────────────────────────────────────────────
const usuarioSenha = ref(null)

const formSenha = useForm({
  password:              '',
  password_confirmation: '',
})

function abrirAlterarSenha(u) {
  formSenha.reset()
  usuarioSenha.value = u
}

function fecharSenha() {
  usuarioSenha.value = null
  formSenha.reset()
}

function salvarSenha() {
  formSenha.post(`/admin/usuarios/${usuarioSenha.value.id}/senha`, {
    onSuccess: () => fecharSenha(),
  })
}

// ── Excluir ───────────────────────────────────────────────────────────────────
const usuarioParaExcluir = ref(null)
function confirmarExclusao(u) { usuarioParaExcluir.value = u }

function roleBadgeClass(role) {
  return {
    pastor: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    lider:  'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    membro: 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300',
  }[role] ?? 'bg-gray-100 dark:bg-slate-700 text-gray-400 dark:text-slate-400'
}
</script>
