<template>
  <AdminLayout>

    <!-- HEADER -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
      <div>
        <p class="text-xs tracking-widest uppercase text-gray-400 dark:text-slate-500 mb-1">Membros</p>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Aniversários</h1>
        <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">
          Ordenados por proximidade do aniversário
        </p>
      </div>
      <Link href="/admin"
            class="self-start sm:self-auto text-xs font-semibold text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400
                   px-4 py-2 rounded-lg border border-gray-200 dark:border-slate-600 transition-colors">
        ← Voltar ao Painel
      </Link>
    </div>

    <!-- BANNER: ANIVERSARIANTES DA SEMANA -->
    <div v-if="aniversariantesSemana.length"
         class="mb-8 rounded-2xl border border-purple-200 dark:border-purple-800/40 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/15 dark:to-pink-900/10 p-5 sm:p-6 shadow-sm">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xl shadow-sm flex-shrink-0">
            🎉
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">Aniversariantes da semana</h2>
            <p class="text-xs text-gray-500 dark:text-slate-400">
              {{ aniversariantesSemana.length }} {{ aniversariantesSemana.length === 1 ? 'aniversariante' : 'aniversariantes' }} · segunda a domingo
            </p>
          </div>
        </div>
        <button type="button" @click="abrirModal"
                class="self-start sm:self-auto inline-flex items-center gap-2 text-sm font-semibold text-white
                       bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600
                       px-4 py-2.5 rounded-xl shadow-sm transition-colors">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
            <rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
          </svg>
          Mensagem para o grupo
        </button>
      </div>

      <div class="flex flex-wrap gap-2">
        <div v-for="a in aniversariantesSemana" :key="a.id"
             class="flex items-center gap-2.5 bg-white/70 dark:bg-slate-800/60 border border-white dark:border-slate-700 rounded-xl pl-2 pr-3.5 py-2 shadow-sm"
             :class="a.hoje ? 'ring-2 ring-pink-300 dark:ring-pink-700' : ''">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
               :style="{ background: a.color }">
            {{ a.initials }}
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ primeiroNome(a.name) }}</p>
              <span v-if="a.hoje" class="text-[10px]">🎂</span>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-slate-400 capitalize leading-none mt-0.5">
              {{ a.dia_semana }} · dia {{ a.dia }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: MENSAGEM PRONTA -->
    <div v-if="modalAberta"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         @click.self="modalAberta = false">
      <div class="w-full max-w-lg bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-700 flex flex-col max-h-[85vh]">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-slate-700">
          <h3 class="font-bold text-gray-900 dark:text-white">Mensagem para o grupo</h3>
          <button type="button" @click="modalAberta = false"
                  class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><path d="M18 6 6 18M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-5 overflow-y-auto">
          <textarea ref="msgArea" readonly rows="12"
                    class="w-full resize-none rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-900/50
                           text-sm text-gray-700 dark:text-slate-200 p-4 font-mono leading-relaxed focus:outline-none"
                    :value="mensagemSemana"></textarea>
        </div>
        <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-100 dark:border-slate-700">
          <button type="button" @click="modalAberta = false"
                  class="text-sm font-semibold text-gray-500 dark:text-slate-400 px-4 py-2.5 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
            Fechar
          </button>
          <button type="button" @click="copiarMensagem"
                  class="inline-flex items-center gap-2 text-sm font-semibold text-white px-4 py-2.5 rounded-xl shadow-sm transition-colors"
                  :class="copiado ? 'bg-emerald-500' : 'bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600'">
            <svg v-if="!copiado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
              <rect x="9" y="9" width="13" height="13" rx="2" /><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
            </svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M20 6 9 17l-5-5" /></svg>
            {{ copiado ? 'Copiado!' : 'Copiar mensagem' }}
          </button>
        </div>
      </div>
    </div>

    <!-- COM ANIVERSÁRIO -->
    <div v-if="comAniversario.length" class="mb-10">

      <!-- CARDS (mobile) -->
      <div class="sm:hidden space-y-2">
        <div v-for="a in comAniversario" :key="a.id"
             class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl p-4 shadow-sm flex items-center gap-3"
             :class="a.hoje
               ? 'border-pink-200 dark:border-pink-800/50 bg-gradient-to-r from-purple-50/40 to-pink-50/40 dark:from-purple-900/10 dark:to-pink-900/10'
               : a.esta_semana ? 'border-amber-200 dark:border-amber-800/40 bg-amber-50/30 dark:bg-amber-900/5' : ''">
          <!-- Avatar -->
          <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
               :style="{ background: a.color }">
            {{ a.initials }}
          </div>
          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ a.name }}</p>
              <span v-if="a.hoje" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-pink-100 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400">🎉 Hoje!</span>
              <span v-else-if="a.esta_semana" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">Esta semana</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5 capitalize">{{ a.data_fmt }} · {{ a.idade }} anos</p>
          </div>
          <!-- Dias restantes + WhatsApp -->
          <div class="flex items-center gap-2 flex-shrink-0">
            <a v-if="a.hoje && a.telefone"
               :href="whatsappBirthdayUrl(a)" target="_blank" rel="noopener"
               title="Enviar mensagem de aniversário"
               class="w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center transition shadow-sm">
              <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                <path d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.91-7.01zM12.05 20.15h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.22 8.22 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c-.01 4.54-3.71 8.23-8.23 8.23zm4.52-6.17c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.15.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23a7.4 7.4 0 0 1-1.37-1.7c-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.17 0-.43.06-.66.31-.23.25-.86.84-.86 2.05 0 1.21.88 2.37 1 2.54.12.17 1.73 2.64 4.2 3.7.59.25 1.04.4 1.4.52.59.19 1.12.16 1.55.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.08.14-1.18-.06-.1-.22-.16-.47-.28z"/>
              </svg>
            </a>
            <span v-if="a.hoje" class="text-xs font-bold px-2.5 py-1 rounded-full bg-pink-100 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400">Hoje</span>
            <span v-else class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400">{{ a.dias_restantes }}d</span>
          </div>
        </div>
      </div>

      <!-- TABELA (desktop) -->
      <div class="hidden sm:block bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <div class="min-w-[520px]">
            <div class="grid grid-cols-12 px-6 py-3 border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
              <div class="col-span-5 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Membro</div>
              <div class="col-span-3 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Data</div>
              <div class="col-span-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 text-center">Idade</div>
              <div class="col-span-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 text-right">Faltam</div>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-slate-700/50">
              <div v-for="a in comAniversario" :key="a.id"
                   :class="a.hoje
                     ? 'bg-gradient-to-r from-purple-50/60 to-pink-50/60 dark:from-purple-900/10 dark:to-pink-900/10'
                     : a.esta_semana ? 'bg-amber-50/40 dark:bg-amber-900/5' : ''"
                   class="grid grid-cols-12 px-6 py-4 items-center hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                <div class="col-span-5 flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0" :style="{ background: a.color }">{{ a.initials }}</div>
                  <div>
                    <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ a.name }}</p>
                    <span v-if="a.hoje" class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-0.5 bg-pink-100 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400">🎉 Hoje!</span>
                    <span v-else-if="a.esta_semana" class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">Esta semana</span>
                  </div>
                </div>
                <div class="col-span-3"><p class="text-sm text-gray-700 dark:text-slate-300 capitalize">{{ a.data_fmt }}</p></div>
                <div class="col-span-2 text-center"><span class="text-sm font-semibold text-gray-700 dark:text-slate-300">{{ a.idade }} anos</span></div>
                <div class="col-span-2 flex items-center justify-end gap-2">
                  <a v-if="a.hoje && a.telefone"
                     :href="whatsappBirthdayUrl(a)" target="_blank" rel="noopener"
                     title="Enviar mensagem de aniversário"
                     class="w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center transition shadow-sm">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                      <path d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.91-7.01zM12.05 20.15h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.22 8.22 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c-.01 4.54-3.71 8.23-8.23 8.23zm4.52-6.17c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.15.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23a7.4 7.4 0 0 1-1.37-1.7c-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.17 0-.43.06-.66.31-.23.25-.86.84-.86 2.05 0 1.21.88 2.37 1 2.54.12.17 1.73 2.64 4.2 3.7.59.25 1.04.4 1.4.52.59.19 1.12.16 1.55.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.08.14-1.18-.06-.1-.22-.16-.47-.28z"/>
                    </svg>
                  </a>
                  <span v-if="a.hoje" class="inline-block text-xs font-bold px-2.5 py-1 rounded-full bg-pink-100 dark:bg-pink-900/40 text-pink-600 dark:text-pink-400">Hoje</span>
                  <span v-else class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-400">{{ a.dias_restantes }}d</span>
                </div>
              </div>
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
import { ref, computed, nextTick } from 'vue'

const props = defineProps({
  comAniversario: Array,
  semAniversario: Array,
  aniversariantesSemana: { type: Array, default: () => [] },
})

const modalAberta = ref(false)
const copiado = ref(false)
const msgArea = ref(null)

const mensagemSemana = computed(() => {
  const linhas = props.aniversariantesSemana.map(a => {
    const dia = a.dia_semana.charAt(0).toUpperCase() + a.dia_semana.slice(1)
    return `🎂 ${a.name} - ${dia} (dia ${a.dia})`
  })
  return [
    '🎉 *Aniversariantes da semana* 🎉',
    '',
    'Vamos celebrar e orar por nossos irmãos que fazem aniversário nesta semana:',
    '',
    ...linhas,
    '',
    'Que Deus abençoe grandemente a vida de cada um! 🙏🥳',
  ].join('\n')
})

function abrirModal() {
  copiado.value = false
  modalAberta.value = true
}

async function copiarMensagem() {
  const texto = mensagemSemana.value
  try {
    await navigator.clipboard.writeText(texto)
  } catch {
    if (msgArea.value) {
      msgArea.value.select()
      document.execCommand('copy')
    }
  }
  copiado.value = true
  await nextTick()
  setTimeout(() => { copiado.value = false }, 2000)
}

const BIRTHDAY_MESSAGES = [
  '🎉 Feliz aniversário, {nome}! Que Deus continue te abençoando neste novo ciclo de vida. Um forte abraço da família Igreja!',
  '🎂 Parabéns, {nome}! Que esse novo ano traga muita saúde, paz e alegrias. A gente da igreja torce muito por você!',
  '🎈 Hoje é o seu dia, {nome}! Que o Senhor derrame bênçãos sobre sua vida e sua família. Feliz aniversário!',
  '🥳 Feliz aniversário, {nome}! Que o Senhor renove suas forças e te conduza por novos caminhos neste novo ano de vida.',
  '🎁 Parabéns pelo seu dia, {nome}! Que essa nova etapa seja repleta de propósito, fé e muita alegria. Abraço carinhoso!',
  '🙌 Hoje é dia de festa, {nome}! Parabéns pelo seu aniversário. Que Deus te conceda mais um ano de conquistas e muitas bênçãos!',
]

function primeiroNome(nome) {
  return (nome || '').trim().split(/\s+/)[0] || ''
}

function whatsappBirthdayUrl(pessoa) {
  let d = (pessoa.telefone || '').replace(/\D/g, '')
  if (!d) return null
  if (d.startsWith('0')) d = d.slice(1)
  if (d.length <= 11) d = '55' + d
  const msg = BIRTHDAY_MESSAGES[Math.floor(Math.random() * BIRTHDAY_MESSAGES.length)]
    .replace('{nome}', primeiroNome(pessoa.name))
  return `https://wa.me/${d}?text=${encodeURIComponent(msg)}`
}
</script>
