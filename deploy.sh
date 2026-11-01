#!/usr/bin/env bash
#
# Atualiza o servidor com o que esta no GitHub. Rode na pasta do projeto:
#
#   ./deploy.sh            puxa a branch atual e aplica tudo
#   ./deploy.sh --sem-pull aplica o que ja esta na pasta (sem git pull)
#
# Coloca o site em manutencao so durante a migracao e tira no fim, mesmo se
# algo falhar no meio. Nao mexe no .env.
#
# O servidor tambem precisa, fora daqui:
#   - um "php artisan queue:work" continuo (QUEUE_CONNECTION=database): avisos
#     de escala e push do app passam pela fila
#   - cron chamando "php artisan schedule:run" a cada minuto, quando o
#     agendamento em routes/console.php for ativado

set -euo pipefail

cd "$(dirname "$0")"

passo() { printf '\n\033[1;32m==> %s\033[0m\n' "$1"; }

if [[ "${1:-}" != "--sem-pull" ]]; then
    passo "Puxando $(git rev-parse --abbrev-ref HEAD) do GitHub"
    git pull --ff-only
fi

passo "Dependências do PHP"
composer install --no-dev --optimize-autoloader --no-interaction

passo "Telas (site e painel)"
npm ci --no-audit --no-fund
npm run build

passo "Banco"
php artisan down --retry=15
# Se a migracao falhar, o site volta do mesmo jeito.
trap 'php artisan up' EXIT
php artisan migrate --force
php artisan up
trap - EXIT

passo "Caches do Laravel"
php artisan optimize:clear
php artisan optimize

# Fotos, anexos das aulas e imagens do editor ficam em storage/app/public.
if [[ ! -e public/storage ]]; then
    passo "Link do storage"
    php artisan storage:link
fi

passo "Fila (o worker pega o código novo)"
php artisan queue:restart

passo "Pronto: $(git log -1 --format='%h %s')"
