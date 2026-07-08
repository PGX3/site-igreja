<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">
          <Link href="/admin/escalas" class="hover:text-blue-600 dark:hover:text-blue-400">Escalas</Link>
          <span class="mx-1">›</span>
          Detalhe
        </p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ escala.titulo }}</h1>
        <div class="flex items-center gap-2 flex-wrap mt-2">
          <span :class="statusClass(escala.status)"
                class="text-xs font-bold px-2.5 py-1 rounded-full">
            {{ statusLabel(escala.status) }}
          </span>
          <span class="text-sm text-gray-500 dark:text-slate-400">
            {{ formatarData(escala.data) }} · {{ escala.hora_inicio }} – {{ escala.hora_fim }}
          </span>
          <span class="text-sm text-gray-400 dark:text-slate-500">{{ escala.grupo?.nome }}</span>
        </div>
      </div>

      <div class="flex items-center gap-2 self-start flex-wrap">
        <button @click="compartilharEscala"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
          📲 Compartilhar no WhatsApp
        </button>
        <button v-if="can_manage && escala.grupo?.tem_whatsapp" @click="enviarWhatsapp" :disabled="enviandoWhatsapp"
                class="border border-green-300 dark:border-green-800 text-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 disabled:opacity-50 px-4 py-2.5 rounded-lg text-sm font-semibold transition">
          {{ enviandoWhatsapp ? 'Enviando…' : '📲 Enviar no grupo' }}
        </button>
        <Link v-if="can_manage" :href="`/admin/escalas/${escala.id}/edit`"
              class="border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-700 px-5 py-2.5 rounded-lg text-sm font-semibold transition">
          Editar
        </Link>
        <span v-else class="text-xs text-gray-400 dark:text-slate-500 self-center px-2">Somente leitura</span>
      </div>
    </div>

    <!-- FLASH -->
    <div v-if="$page.props.flash?.success"
         class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm font-medium">
      {{ $page.props.flash.success }}
    </div>
    <div v-if="$page.props.flash?.error"
         class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm font-medium">
      {{ $page.props.flash.error }}
    </div>

    <!-- DESCRIÇÃO -->
    <div v-if="escala.descricao"
         class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5 shadow-sm mb-6 text-sm text-gray-600 dark:text-slate-300">
      {{ escala.descricao }}
    </div>

    <!-- VÍNCULO -->
    <div v-if="escala.culto || escala.evento"
         class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-5 shadow-sm mb-6 flex items-center gap-4">
      <div :class="escala.culto
            ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400'
            : 'bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400'"
           class="text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full">
        {{ escala.culto ? 'Culto' : 'Evento' }}
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-semibold text-gray-900 dark:text-white text-sm">
          {{ escala.culto?.nome || escala.evento?.nome }}
        </p>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
          <template v-if="escala.culto">
            Toda {{ escala.culto.dia_semana?.toLowerCase() }} · {{ escala.culto.horario }}
          </template>
          <template v-else-if="escala.evento">
            {{ formatarData(escala.evento.data_evento) }}
            <span v-if="escala.evento.horario"> · {{ escala.evento.horario }}</span>
            <span v-if="escala.evento.local"> · {{ escala.evento.local }}</span>
          </template>
        </p>
      </div>
    </div>

    <!-- RESUMO MEMBROS -->
    <div class="grid grid-cols-3 gap-3 sm:gap-4 mb-8">
      <div v-for="stat in membroStats" :key="stat.label"
           class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-3 sm:p-5 shadow-sm text-center">
        <p class="text-2xl sm:text-3xl font-bold" :class="stat.color">{{ stat.value }}</p>
        <p class="text-[10px] sm:text-xs text-gray-400 dark:text-slate-500 mt-1 uppercase tracking-wider">{{ stat.label }}</p>
      </div>
    </div>

    <!-- LISTA MEMBROS -->
    <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between gap-3">
        <p class="font-semibold text-gray-900 dark:text-white">Membros Escalados</p>
        <div class="flex items-center gap-2">
          <button v-if="can_manage && temAlgumApikey" @click="enviarConvites" :disabled="enviandoConvites"
                  class="text-xs font-semibold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 disabled:opacity-50 px-3 py-1.5 rounded transition"
                  title="Envia automaticamente para quem tem API Key">
            {{ enviandoConvites ? 'Enviando…' : '📨 Enviar convites' }}
          </button>
          <span class="text-xs text-gray-400 dark:text-slate-500">{{ escala.membros?.length ?? 0 }} total</span>
        </div>
      </div>

      <div v-if="escala.membros?.length">
        <div v-for="m in escala.membros" :key="m.id"
             class="flex items-center gap-3 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-50 dark:border-slate-700/50 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
          <!-- Avatar inicial -->
          <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-bold text-sm
                      flex items-center justify-center flex-shrink-0">
            {{ m.user?.name?.[0]?.toUpperCase() }}
          </div>

          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ m.user?.name }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 truncate">
              <span class="hidden sm:inline">{{ m.user?.email }}</span>
              <span class="sm:hidden">{{ m.funcao ?? m.user?.email ?? '' }}</span>
            </p>
          </div>

          <div class="hidden sm:block text-sm text-gray-500 dark:text-slate-400 flex-shrink-0">{{ m.funcao ?? '—' }}</div>

          <a v-if="can_manage && m.telefone" :href="linkWhatsappMembro(m)" target="_blank" rel="noopener"
             class="flex-shrink-0 text-xs font-semibold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 px-2.5 py-1.5 rounded transition"
             title="Enviar convite pelo WhatsApp">📲</a>

          <div class="text-right flex-shrink-0">
            <span :class="membroStatusClass(m.status)"
                  class="inline-block text-xs font-bold px-2.5 py-1 rounded-full">
              {{ membroStatusLabel(m.status) }}
            </span>
            <p v-if="m.confirmado_em" class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">{{ m.confirmado_em }}</p>
          </div>
        </div>
      </div>

      <div v-else class="py-10 text-center text-gray-400 dark:text-slate-500 text-sm">
        Nenhum membro escalado ainda.<template v-if="can_manage"> <Link :href="`/admin/escalas/${escala.id}/edit`" class="text-blue-600 dark:text-blue-400 hover:underline">Editar escala</Link> para adicionar.</template>
      </div>
    </div>

    <!-- ============================ SETLIST ============================ -->
    <div v-if="escala.grupo?.tem_musicas"
         class="mt-8 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between gap-3">
        <p class="font-semibold text-gray-900 dark:text-white">🎵 Setlist</p>
        <div class="flex items-center gap-2">
          <template v-if="escala.setlist?.length">
            <button @click="abrirApresentacao(0)"
                    class="text-xs font-semibold text-gray-900 dark:text-white bg-gray-900/5 dark:bg-white/10 hover:bg-gray-900/10 dark:hover:bg-white/20 px-3 py-1.5 rounded transition">▶ Apresentar</button>
            <button @click="compartilharSetlist"
                    class="text-xs font-semibold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 px-3 py-1.5 rounded transition">Compartilhar</button>
            <a :href="`/admin/escalas/${escala.id}/setlist/imprimir`" target="_blank" rel="noopener"
               class="text-xs font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 px-3 py-1.5 rounded transition">🖨 PDF</a>
          </template>
          <span class="text-xs text-gray-400 dark:text-slate-500">{{ escala.setlist?.length ?? 0 }} música(s)</span>
        </div>
      </div>

      <!-- LISTA -->
      <div v-if="escala.setlist?.length">
        <div v-for="(s, idx) in escala.setlist" :key="s.id"
             class="border-b border-gray-50 dark:border-slate-700/50 last:border-0">
          <div class="flex items-center gap-3 px-4 sm:px-6 py-3">
            <span class="w-6 text-center text-sm font-bold text-gray-400 dark:text-slate-500 flex-shrink-0">{{ idx + 1 }}</span>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ s.musica?.nome ?? '(música removida)' }}</p>
              <p v-if="s.observacao" class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ s.observacao }}</p>
            </div>
            <span v-if="tomExibido(s)"
                  class="bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0">
              {{ tomExibido(s) }}
            </span>
            <div class="flex items-center gap-0.5 flex-shrink-0">
              <button v-if="s.musica" @click="abrirApresentacao(idx)"
                      class="text-xs font-semibold text-gray-900 dark:text-white bg-gray-900/5 dark:bg-white/10 hover:bg-gray-900/10 dark:hover:bg-white/20 px-2.5 py-1.5 rounded transition" title="Ver no celular">📱</button>
              <template v-if="can_manage">
                <button @click="mover(idx, -1)" :disabled="idx === 0"
                        class="w-7 h-7 rounded text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-30 transition" title="Subir">↑</button>
                <button @click="mover(idx, 1)" :disabled="idx === escala.setlist.length - 1"
                        class="w-7 h-7 rounded text-gray-400 hover:text-gray-700 dark:hover:text-slate-200 hover:bg-gray-100 dark:hover:bg-slate-700 disabled:opacity-30 transition" title="Descer">↓</button>
                <button @click="abrirEdicao(s)"
                        class="w-7 h-7 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition" title="Editar">✎</button>
                <button @click="router.delete(`/admin/escalas/${escala.id}/setlist/${s.id}`, { preserveScroll: true })"
                        class="w-7 h-7 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Remover">×</button>
              </template>
            </div>
          </div>

          <!-- EDIÇÃO INLINE -->
          <div v-if="editandoId === s.id" class="px-4 sm:px-6 pb-4 flex flex-wrap items-end gap-3 bg-gray-50 dark:bg-slate-900/40">
            <div>
              <label class="block text-[11px] font-semibold text-gray-500 dark:text-slate-400 mb-1">Tom</label>
              <select v-model="edicao.tom" class="border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tom da música ({{ s.musica?.tom || '—' }})</option>
                <option v-for="t in tons" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>
            <div class="flex-1 min-w-[160px]">
              <label class="block text-[11px] font-semibold text-gray-500 dark:text-slate-400 mb-1">Observação</label>
              <input v-model="edicao.observacao" type="text" placeholder="Ex: entra na 2ª estrofe"
                     class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <button @click="salvarEdicao(s)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Salvar</button>
            <button @click="editandoId = null" class="px-4 py-2 text-sm font-semibold text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition">Cancelar</button>
          </div>
        </div>
      </div>
      <div v-else class="py-8 text-center text-gray-400 dark:text-slate-500 text-sm">
        Nenhuma música no setlist ainda.
      </div>

      <!-- FORM ADICIONAR -->
      <div v-if="can_manage" class="px-4 sm:px-6 py-4 border-t border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/40">
        <div v-if="!musicasDisponiveis.length && !escala.setlist?.length" class="text-sm text-gray-500 dark:text-slate-400">
          Nenhuma música na biblioteca.
          <Link href="/admin/musicas" class="text-blue-600 dark:text-blue-400 hover:underline">Cadastrar músicas</Link> para montar o setlist.
        </div>
        <div v-else class="flex flex-wrap items-end gap-3">
          <div class="flex-1 min-w-[180px]">
            <label class="block text-[11px] font-semibold text-gray-500 dark:text-slate-400 mb-1">Adicionar música</label>
            <select v-model="novaMusica.musica_id" @change="onSelectMusica"
                    class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :class="{ 'border-red-400': erroSetlist }">
              <option value="">Selecione...</option>
              <option v-for="m in musicasDisponiveis" :key="m.id" :value="m.id">{{ m.nome }}{{ m.tom ? ` (${m.tom})` : '' }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-500 dark:text-slate-400 mb-1">Tom</label>
            <select v-model="novaMusica.tom" class="border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">—</option>
              <option v-for="t in tons" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>
          <button @click="adicionarMusica" :disabled="!novaMusica.musica_id"
                  class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Adicionar</button>
        </div>
        <p v-if="erroSetlist" class="text-xs text-red-500 mt-1">{{ erroSetlist }}</p>
      </div>
    </div>

    <!-- ============================ ANEXOS ============================ -->
    <div class="mt-8 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
        <p class="font-semibold text-gray-900 dark:text-white">📎 Anexos</p>
        <span class="text-xs text-gray-400 dark:text-slate-500">{{ escala.assets?.length ?? 0 }} item(ns)</span>
      </div>

      <!-- LISTA -->
      <div v-if="escala.assets?.length" class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div v-for="a in escala.assets" :key="a.id"
             class="border border-gray-100 dark:border-slate-700 rounded-lg p-3 flex gap-3">
          <a v-if="a.tipo === 'imagem'" :href="`/storage/${a.arquivo_path}`" target="_blank" rel="noopener" class="flex-shrink-0">
            <img :src="`/storage/${a.arquivo_path}`" :alt="a.titulo || a.arquivo_nome"
                 class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-slate-600" />
          </a>
          <div v-else class="flex-shrink-0 w-16 h-16 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-2xl">📄</div>

          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ a.titulo || a.arquivo_nome }}</p>
            <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1">{{ a.created_by?.name }}</p>
            <div class="flex items-center gap-2 mt-1.5">
              <a v-if="a.tipo === 'arquivo'" :href="`/admin/assets/${a.id}/download`"
                 class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Baixar</a>
              <a v-else :href="`/storage/${a.arquivo_path}`" target="_blank" rel="noopener"
                 class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">Abrir</a>
              <button v-if="can_manage" @click="router.delete(`/admin/escalas/${escala.id}/assets/${a.id}`, { preserveScroll: true })"
                      class="text-xs font-semibold text-gray-400 dark:text-slate-500 hover:text-red-600 transition">Desvincular</button>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="py-8 text-center text-gray-400 dark:text-slate-500 text-sm">
        Nenhum anexo vinculado ainda.
      </div>

      <!-- GERENCIAR -->
      <div v-if="can_manage" class="px-4 sm:px-6 py-4 border-t border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/40 space-y-3">
        <!-- Anexar da biblioteca -->
        <div>
          <label class="block text-[11px] font-semibold text-gray-500 dark:text-slate-400 mb-1">Anexar da biblioteca</label>
          <div v-if="assetsDisponiveis.length" class="flex flex-wrap items-end gap-3">
            <select v-model="assetSelecionado"
                    class="flex-1 min-w-[180px] border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Selecione um anexo...</option>
              <option v-for="a in assetsDisponiveis" :key="a.id" :value="a.id">
                {{ a.tipo === 'imagem' ? '🖼' : '📄' }} {{ a.titulo || a.arquivo_nome }}
              </option>
            </select>
            <button @click="anexarBiblioteca" :disabled="!assetSelecionado"
                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">Vincular</button>
          </div>
          <p v-else class="text-sm text-gray-500 dark:text-slate-400">
            <template v-if="biblioteca_assets.length">Todos os anexos da biblioteca já estão vinculados.</template>
            <template v-else>
              A <Link href="/admin/assets" class="text-blue-600 dark:text-blue-400 hover:underline">biblioteca de anexos</Link> está vazia. Cadastre lá ou envie um novo abaixo.
            </template>
          </p>
        </div>

        <!-- Upload novo -->
        <details class="group">
          <summary class="cursor-pointer text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline select-none">+ Enviar um anexo novo</summary>
          <div class="mt-3 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
              <button v-for="t in tiposAnexo" :key="t.value" @click="assetForm.tipo = t.value"
                      class="px-3 py-1.5 rounded-full text-xs font-semibold border transition"
                      :class="assetForm.tipo === t.value ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'border-gray-200 dark:border-slate-600 text-gray-500 dark:text-slate-400 hover:bg-white dark:hover:bg-slate-700'">
                {{ t.label }}
              </button>
            </div>
            <input v-model="assetForm.titulo" type="text" placeholder="Título / legenda (opcional)"
                   class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <label class="block w-full border-2 border-dashed border-gray-200 dark:border-slate-600 rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition"
                   :class="{ 'border-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/20': arquivoNome, 'border-red-400': assetForm.errors.arquivo }">
              <input type="file" class="hidden" :accept="assetForm.tipo === 'imagem' ? 'image/*' : '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx'" @change="onArquivoChange" />
              <span class="text-sm text-gray-500 dark:text-slate-400">
                <span v-if="arquivoNome" class="text-blue-600 dark:text-blue-400 font-semibold">{{ arquivoNome }}</span>
                <template v-else>{{ assetForm.tipo === 'imagem' ? 'Selecionar imagem' : 'Selecionar arquivo (PDF, docs...)' }}</template>
              </span>
            </label>
            <p v-if="assetForm.errors.arquivo" class="text-xs text-red-500">{{ assetForm.errors.arquivo }}</p>
            <div class="flex justify-end">
              <button @click="enviarAnexo" :disabled="assetForm.processing"
                      class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                {{ assetForm.processing ? 'Enviando…' : 'Enviar e vincular' }}
              </button>
            </div>
          </div>
        </details>
      </div>
    </div>

    <!-- ============================ NOTAS ============================ -->
    <div class="mt-8 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
        <p class="font-semibold text-gray-900 dark:text-white">📝 Notas</p>
        <span class="text-xs text-gray-400 dark:text-slate-500">{{ escala.notas?.length ?? 0 }} nota(s)</span>
      </div>

      <div v-if="escala.notas?.length" class="p-4 sm:p-6 space-y-3">
        <div v-for="n in escala.notas" :key="n.id"
             class="border border-gray-100 dark:border-slate-700 rounded-lg p-4">
          <div class="flex items-start justify-between gap-3">
            <p v-if="n.titulo" class="font-semibold text-gray-900 dark:text-white text-sm">{{ n.titulo }}</p>
            <button v-if="can_manage" @click="router.delete(`/admin/escalas/${escala.id}/notas/${n.id}`, { preserveScroll: true })"
                    class="text-gray-300 dark:text-slate-600 hover:text-red-500 text-lg leading-none flex-shrink-0">×</button>
          </div>
          <p class="text-sm text-gray-600 dark:text-slate-300 whitespace-pre-wrap" :class="{ 'mt-1': n.titulo }">{{ n.corpo }}</p>
          <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-2">{{ n.created_by?.name }} · {{ n.created_at }}</p>
        </div>
      </div>
      <div v-else class="py-8 text-center text-gray-400 dark:text-slate-500 text-sm">
        Nenhuma nota ainda.
      </div>

      <div v-if="can_manage" class="px-4 sm:px-6 py-4 border-t border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/40 space-y-3">
        <input v-model="notaForm.titulo" type="text" placeholder="Título (opcional)"
               class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <textarea v-model="notaForm.corpo" rows="3" placeholder="Escreva o roteiro, aviso ou observação..."
                  class="w-full border border-gray-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
                  :class="{ 'border-red-400': notaForm.errors.corpo }"></textarea>
        <p v-if="notaForm.errors.corpo" class="text-xs text-red-500">{{ notaForm.errors.corpo }}</p>
        <div class="flex justify-end">
          <button @click="enviarNota" :disabled="notaForm.processing"
                  class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
            {{ notaForm.processing ? 'Salvando…' : 'Adicionar nota' }}
          </button>
        </div>
      </div>
    </div>

    <!-- APRESENTAÇÃO DO SETLIST -->
    <SetlistApresentacao v-if="apresentando" :musicas="setlistMusicas" :start="apresentacaoStart"
                         @close="apresentando = false" />

  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SetlistApresentacao from '@/Components/SetlistApresentacao.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'

const props = defineProps({
  escala:            Object,
  can_manage:        { type: Boolean, default: false },
  musicas:           { type: Array, default: () => [] },
  biblioteca_assets: { type: Array, default: () => [] },
})

const tons = [
  'Dó', 'Dó#', 'Ré', 'Ré#', 'Mi', 'Fá',
  'Fá#', 'Sol', 'Sol#', 'Lá', 'Lá#', 'Si',
  'Dóm', 'Dó#m', 'Rém', 'Ré#m', 'Mim', 'Fám',
  'Fá#m', 'Solm', 'Sol#m', 'Lám', 'Lá#m', 'Sim',
]

const tiposAnexo = [
  { value: 'arquivo', label: 'Arquivo' },
  { value: 'imagem',  label: 'Imagem' },
]

function tomExibido(s) {
  return s.tom || s.musica?.tom || ''
}

// ── Apresentação do setlist ─────────────────────────────────
const apresentando = ref(false)
const apresentacaoStart = ref(0)

const setlistMusicas = computed(() =>
  (props.escala.setlist ?? [])
    .filter(s => s.musica)
    .map(s => ({ nome: s.musica.nome, tom: tomExibido(s), letra: s.musica.letra, link: s.musica.link }))
)

function abrirApresentacao(idx) {
  // idx é a posição no setlist completo; ajusta para o índice dentro dos que têm música
  const validos = (props.escala.setlist ?? []).slice(0, idx + 1).filter(s => s.musica).length
  apresentacaoStart.value = Math.max(0, validos - 1)
  apresentando.value = true
}

function compartilharSetlist() {
  const e = props.escala
  const linhas = [`*Setlist — ${e.titulo}*`]
  if (e.data) linhas.push(formatarData(e.data))
  linhas.push('')
  ;(e.setlist ?? []).forEach((s, i) => {
    if (!s.musica) return
    const tom = tomExibido(s)
    linhas.push(`${i + 1}. ${s.musica.nome}${tom ? ` — Tom: ${tom}` : ''}`)
    if (s.observacao) linhas.push(`   (${s.observacao})`)
    if (s.musica.link) linhas.push(`   ${s.musica.link}`)
  })
  window.open(`https://wa.me/?text=${encodeURIComponent(linhas.join('\n'))}`, '_blank', 'noopener')
}

// ── Compartilhar escala completa (WhatsApp / grupo) ─────────
function compartilharEscala() {
  const e = props.escala
  const origin = window.location.origin
  const linhas = []

  // Cabeçalho
  linhas.push(`*${e.titulo}*`)
  linhas.push(`_${statusLabel(e.status)}_`)
  if (e.data) linhas.push(`📅 ${formatarData(e.data)}`)
  if (e.hora_inicio || e.hora_fim) linhas.push(`⏰ ${e.hora_inicio || ''}${e.hora_fim ? ` - ${e.hora_fim}` : ''}`)
  if (e.grupo?.nome) linhas.push(`👥 ${e.grupo.nome}`)

  // Vínculo (culto / evento)
  if (e.culto) {
    let l = `📌 Culto: ${e.culto.nome}`
    if (e.culto.dia_semana) l += ` · Toda ${e.culto.dia_semana.toLowerCase()}`
    if (e.culto.horario) l += ` · ${e.culto.horario}`
    linhas.push(l)
  } else if (e.evento) {
    let l = `📌 Evento: ${e.evento.nome}`
    if (e.evento.data_evento) l += ` · ${formatarData(e.evento.data_evento)}`
    if (e.evento.horario) l += ` · ${e.evento.horario}`
    if (e.evento.local) l += ` · ${e.evento.local}`
    linhas.push(l)
  }

  // Descrição
  if (e.descricao) {
    linhas.push('')
    linhas.push(e.descricao)
  }

  // Equipe
  if (e.membros?.length) {
    linhas.push('')
    linhas.push('*👥 Equipe*')
    e.membros.forEach(m => {
      let l = `• ${m.user?.name || ''}`
      if (m.funcao) l += ` — ${m.funcao}`
      l += ` (${membroStatusLabel(m.status)})`
      linhas.push(l)
      if (m.observacao) linhas.push(`   _${m.observacao}_`)
    })
  }

  // Setlist
  const setlist = (e.setlist ?? []).filter(s => s.musica)
  if (setlist.length) {
    linhas.push('')
    linhas.push('*🎵 Setlist*')
    setlist.forEach((s, i) => {
      const tom = tomExibido(s)
      linhas.push(`${i + 1}. ${s.musica.nome}${tom ? ` — Tom: ${tom}` : ''}`)
      if (s.observacao) linhas.push(`   (${s.observacao})`)
      if (s.musica.link) linhas.push(`   ${s.musica.link}`)
    })
  }

  // Anexos
  if (e.assets?.length) {
    linhas.push('')
    linhas.push('*📎 Anexos*')
    e.assets.forEach(a => {
      linhas.push(`• ${a.titulo || a.arquivo_nome}`)
      if (a.arquivo_path) linhas.push(`   ${origin}/storage/${a.arquivo_path}`)
    })
  }

  // Notas
  if (e.notas?.length) {
    linhas.push('')
    linhas.push('*📝 Notas*')
    e.notas.forEach(n => {
      if (n.titulo) linhas.push(`▸ ${n.titulo}`)
      if (n.corpo) linhas.push(n.corpo)
    })
  }

  window.open(`https://wa.me/?text=${encodeURIComponent(linhas.join('\n'))}`, '_blank', 'noopener')
}

// ── Setlist ─────────────────────────────────────────────────
const novaMusica = reactive({ musica_id: '', tom: '' })
const erroSetlist = ref('')

const musicasDisponiveis = computed(() => {
  const usados = new Set((props.escala.setlist ?? []).map(s => s.musica?.id).filter(Boolean))
  return props.musicas.filter(m => !usados.has(m.id))
})

function onSelectMusica() {
  const m = props.musicas.find(m => m.id === Number(novaMusica.musica_id))
  novaMusica.tom = m?.tom ?? ''
}

function adicionarMusica() {
  erroSetlist.value = ''
  if (!novaMusica.musica_id) return
  router.post(`/admin/escalas/${props.escala.id}/setlist`, { ...novaMusica }, {
    preserveScroll: true,
    onSuccess: () => { novaMusica.musica_id = ''; novaMusica.tom = '' },
    onError: (e) => { erroSetlist.value = e.musica_id || 'Erro ao adicionar.' },
  })
}

const editandoId = ref(null)
const edicao = reactive({ tom: '', observacao: '' })
function abrirEdicao(s) {
  editandoId.value = s.id
  edicao.tom = s.tom ?? ''
  edicao.observacao = s.observacao ?? ''
}
function salvarEdicao(s) {
  router.patch(`/admin/escalas/${props.escala.id}/setlist/${s.id}`, { ...edicao }, {
    preserveScroll: true,
    onSuccess: () => { editandoId.value = null },
  })
}

function mover(idx, dir) {
  const lista = [...props.escala.setlist]
  const alvo = idx + dir
  if (alvo < 0 || alvo >= lista.length) return
  const [item] = lista.splice(idx, 1)
  lista.splice(alvo, 0, item)
  router.patch(`/admin/escalas/${props.escala.id}/setlist/reorder`,
    { itens: lista.map(s => s.id) },
    { preserveScroll: true })
}

// ── Anexos (biblioteca) ─────────────────────────────────────
const assetSelecionado = ref('')

const assetsDisponiveis = computed(() => {
  const usados = new Set((props.escala.assets ?? []).map(a => a.id))
  return props.biblioteca_assets.filter(a => !usados.has(a.id))
})

function anexarBiblioteca() {
  if (!assetSelecionado.value) return
  router.post(`/admin/escalas/${props.escala.id}/assets/attach`,
    { asset_ids: [assetSelecionado.value] },
    { preserveScroll: true, onSuccess: () => { assetSelecionado.value = '' } })
}

const assetForm = useForm({ tipo: 'arquivo', titulo: '', arquivo: null })
const arquivoNome = ref('')

function onArquivoChange(e) {
  const file = e.target.files[0]
  assetForm.arquivo = file || null
  arquivoNome.value = file ? file.name : ''
}

function enviarAnexo() {
  assetForm.post(`/admin/escalas/${props.escala.id}/assets`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      assetForm.reset('titulo', 'arquivo')
      arquivoNome.value = ''
    },
  })
}

// ── Convites (confirmação) ──────────────────────────────────
const enviandoConvites = ref(false)
const temAlgumApikey = computed(() => (props.escala.membros ?? []).some(m => m.tem_apikey))

function enviarConvites() {
  if (!confirm('Enviar convite automático para quem tem WhatsApp configurado?')) return
  enviandoConvites.value = true
  router.post(`/admin/escalas/${props.escala.id}/convites`, {}, {
    preserveScroll: true,
    onFinish: () => { enviandoConvites.value = false },
  })
}

function foneDigitos(tel) {
  const d = (tel || '').replace(/\D/g, '')
  if (d.length === 10 || d.length === 11) return '55' + d
  return d
}

function linkWhatsappMembro(m) {
  const e = props.escala
  const linhas = [
    `Olá, ${m.user?.name || ''}! Você foi escalado(a):`,
    `*${e.titulo}*`,
    `📅 ${formatarData(e.data)}`,
    `⏰ ${e.hora_inicio} - ${e.hora_fim}`,
  ]
  if (e.grupo?.nome) linhas.push(`👥 ${e.grupo.nome}`)
  if (m.funcao) linhas.push(`🎯 Função: ${m.funcao}`)
  linhas.push('', 'Confirme sua presença:', m.convite_url)
  return `https://wa.me/${foneDigitos(m.telefone)}?text=${encodeURIComponent(linhas.join('\n'))}`
}

// ── WhatsApp ────────────────────────────────────────────────
const enviandoWhatsapp = ref(false)
function enviarWhatsapp() {
  if (!confirm('Enviar esta escala no grupo do WhatsApp?')) return
  enviandoWhatsapp.value = true
  router.post(`/admin/escalas/${props.escala.id}/whatsapp`, {}, {
    preserveScroll: true,
    onFinish: () => { enviandoWhatsapp.value = false },
  })
}

// ── Notas ───────────────────────────────────────────────────
const notaForm = useForm({ titulo: '', corpo: '' })

function enviarNota() {
  notaForm.post(`/admin/escalas/${props.escala.id}/notas`, {
    preserveScroll: true,
    onSuccess: () => notaForm.reset('titulo', 'corpo'),
  })
}

const membroStats = computed(() => {
  const total       = props.escala.membros?.length ?? 0
  const confirmados = props.escala.membros?.filter(m => m.status === 'confirmado').length ?? 0
  const recusados   = props.escala.membros?.filter(m => m.status === 'recusado').length ?? 0
  const pendentes   = total - confirmados - recusados
  return [
    { label: 'Confirmados', value: confirmados, color: 'text-green-600' },
    { label: 'Pendentes',   value: pendentes,   color: 'text-yellow-600' },
    { label: 'Recusados',   value: recusados,   color: 'text-red-500' },
  ]
})

function formatarData(data) {
  if (!data) return ''
  return new Date(data + 'T12:00:00').toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
}

function statusLabel(s) {
  return { pendente: 'Pendente', confirmada: 'Confirmada', em_andamento: 'Em andamento', concluida: 'Concluída', cancelada: 'Cancelada' }[s] ?? s
}
function statusClass(s) {
  return { pendente: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400', confirmada: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400', em_andamento: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400', concluida: 'bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-300', cancelada: 'bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400' }[s] ?? ''
}
function membroStatusLabel(s) {
  return { pendente: 'Pendente', confirmado: 'Confirmado', recusado: 'Recusado', trocado: 'Trocado' }[s] ?? s
}
function membroStatusClass(s) {
  return { pendente: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400', confirmado: 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400', recusado: 'bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400', trocado: 'bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400' }[s] ?? ''
}
</script>
