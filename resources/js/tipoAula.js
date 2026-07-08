// Metadados visuais dos tipos de aula (estilo Google Classroom).
// Classes completas (não dinâmicas) para o Tailwind não descartar no build.
export const tipoAulaMeta = {
  aula: {
    label: 'Aula',
    badge: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    ring: 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300',
  },
  material: {
    label: 'Material',
    badge: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    ring: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300',
  },
  atividade: {
    label: 'Atividade',
    badge: 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    ring: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300',
  },
}

export function metaTipo(tipo) {
  return tipoAulaMeta[tipo] ?? tipoAulaMeta.aula
}
