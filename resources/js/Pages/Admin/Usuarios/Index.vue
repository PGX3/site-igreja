<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 mb-1">Gestão</p>
        <h1 class="text-3xl font-bold text-gray-900">Usuários</h1>
        <p class="text-gray-500 text-sm mt-1">{{ usuarios.length }} usuário(s) cadastrado(s)</p>
      </div>
      <Link href="/admin/usuarios/create"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition">
        + Novo Usuário
      </Link>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error"
         class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-red-600 text-sm font-medium">
      {{ $page.props.flash.error }}
    </div>

    <!-- TABELA -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
      <table v-if="usuarios.length" class="w-full text-sm">
        <thead class="border-b border-gray-100">
          <tr>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Nome</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Papel</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Grupo</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Telefone</th>
            <th class="px-6 py-4"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in usuarios" :key="u.id"
              class="border-b border-gray-50 hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <p class="font-semibold text-gray-900">{{ u.name }}</p>
              <p class="text-xs text-gray-400">{{ u.email }}</p>
            </td>
            <td class="px-6 py-4">
              <span :class="roleBadgeClass(u.role?.name)"
                    class="text-xs font-bold px-2.5 py-1 rounded-full">
                {{ u.role?.display_name ?? '—' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <span v-if="u.grupo" class="text-gray-700 text-sm">{{ u.grupo.nome }}</span>
              <span v-else class="text-gray-300 italic text-xs">—</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              {{ u.telefone ?? '—' }}
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex justify-end gap-2">
                <Link :href="`/admin/usuarios/${u.id}/edit`"
                      class="text-xs font-semibold text-gray-500 hover:text-blue-600 px-3 py-1.5 rounded hover:bg-blue-50 transition">
                  Editar
                </Link>
                <button @click="confirmarExclusao(u)"
                        class="text-xs font-semibold text-gray-500 hover:text-red-600 px-3 py-1.5 rounded hover:bg-red-50 transition">
                  Excluir
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="py-20 text-center text-gray-400">
        <p class="text-4xl mb-3">◈</p>
        <p class="font-medium">Nenhum usuário cadastrado.</p>
      </div>
    </div>

    <!-- MODAL EXCLUSÃO -->
    <div v-if="usuarioParaExcluir"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 shadow-xl max-w-sm w-full mx-4">
        <h3 class="font-bold text-gray-900 mb-2">Excluir usuário?</h3>
        <p class="text-sm text-gray-500 mb-6">
          "<strong>{{ usuarioParaExcluir.name }}</strong>" será removido permanentemente.
        </p>
        <div class="flex gap-3 justify-end">
          <button @click="usuarioParaExcluir = null"
                  class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition">
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
import { Link } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({ usuarios: Array })

const usuarioParaExcluir = ref(null)
function confirmarExclusao(u) { usuarioParaExcluir.value = u }

function roleBadgeClass(role) {
  return {
    pastor: 'bg-purple-100 text-purple-700',
    lider:  'bg-blue-100 text-blue-700',
    membro: 'bg-gray-100 text-gray-600',
  }[role] ?? 'bg-gray-100 text-gray-400'
}
</script>
