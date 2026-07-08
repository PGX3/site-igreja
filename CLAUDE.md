# Igreja em Charqueadas

Aplicação web de gestão de igreja (membros, escalas de voluntários, cultos, eventos, pregações, pedidos de oração) com site público, painel administrativo e API REST para um app mobile.

## Como trabalhar neste projeto

### Antes de implementar qualquer feature nova
- Explore o código existente primeiro. Não proponha a solução antes de entender o que já existe.
- Veja como uma feature parecida já foi feita e siga a mesma estrutura. Use **Pregações** como template de CRUD limpo, **Escala** como referência de feature complexa.
- Reaproveite padrões (controller que mapeia arrays manualmente, `useForm`, layout responsivo cards/tabela) em vez de inventar abordagens novas.

### Idioma
- Domínio em **português**: rotas, tabelas, models, colunas, variáveis, flash messages, labels de UI (`pregacoes`, `escalas`, `titulo`, `anonimo`). Termos do framework ficam em inglês (`index`, `store`, `created_by`).

## Stack

- **Backend:** PHP 8.3 + Laravel 13 (monolito), **Inertia.js** (controllers renderizam páginas Vue, sem API separada para o front web).
- **Frontend:** Vue 3 (`<script setup>`, Composition API, JS puro sem TypeScript) + Vite + Tailwind CSS.
- **Auth:** sessão Laravel no painel, Sanctum (token) na API mobile. Autorização por papel via middleware `role:pastor,lider` (papéis: `pastor`, `lider`, `membro`).
- **Dados:** banco relacional via Eloquent (SQLite em dev, MySQL em prod). Schema todo em `database/migrations/`.
- **Firebase:** usado só para push notifications (FCM) do app mobile, não para persistência de dados.
- **Libs de front:** @tiptap (rich text), vue3-easy-data-table (tabelas), @hcaptcha/vue3-hcaptcha (captcha público).

## Comandos

- `composer dev` — roda tudo em dev (serve + queue + logs + Vite HMR).
- `npm run dev` / `npm run build` — só o front.
- `composer test` — testes (PHPUnit).
- `composer lint` — formata com Pint. `composer lint:test` — checa sem alterar.
- `php artisan migrate` / `php artisan db:seed` — banco.
- `composer setup` — setup inicial completo.

Não há ESLint/Prettier para o JS. Formatação PHP é via Pint (preset Laravel).

## Organização de pastas

- `app/Http/Controllers/Admin/` — painel administrativo (Inertia).
- `app/Http/Controllers/Api/V1/` — endpoints REST do app mobile.
- `app/Http/Controllers/` (raiz) — site público (`HomeController`, `ContatoController`, etc.).
- `app/Models/` — models Eloquent.
- `app/Http/Resources/` — API Resources (usados **só** pela API mobile).
- `app/Http/Requests/Api/` — Form Requests da API.
- `app/Services/` — lógica de domínio (WhatsApp/CallMeBot, confirmação de escala, hCaptcha).
- `app/Http/Middleware/HandleInertiaRequests.php` — shared props (`auth.user`, `flash`, contadores da sidebar).
- `resources/js/Pages/` — páginas Inertia. Públicas na raiz, admin em `Pages/Admin/`, auth em `Pages/Auth/`.
- `resources/js/Components/`, `resources/js/Layouts/` (`AdminLayout.vue`, `MainLayout.vue`), `resources/js/composables/`.
- `routes/web.php` (site + admin), `routes/api.php` (API v1).
- Alias de import JS: `@/` aponta para `resources/js/`.

## Padrão para criar uma feature de admin (CRUD)

Referência: veja Pregações (`Admin/PregacaoController.php`, `Pages/Admin/Pregacoes/Index.vue` e `Form.vue`).

1. **Migration** em `database/migrations/` (tabela em português, plural).
2. **Model** em `app/Models/` com `$fillable`, `$casts`, relacionamentos, e constantes para status quando aplicável (ex.: `PedidoOracao::STATUSES`).
3. **Controller** em `Admin/` renderizando `Inertia::render('Admin/<Feature>/Index'|'Form', [...])`. Padrões:
   - `index` mapeia os dados manualmente para arrays (formata datas para `Y-m-d`). Não usa API Resource (Resource é só para a API mobile).
   - `create` e `edit` renderizam o mesmo `Form` (passando o objeto ou `null`).
   - Validação num método privado `validateData(Request)`.
   - Redireciona com `->with('success', '...')` ou `back()->with(...)` com `preserveScroll` no front.
4. **Rota** `Route::resource(...)` no grupo `admin.` com o `middleware('role:...')` adequado, em `routes/web.php`.
5. **Vue** em `Pages/Admin/<Feature>/Index.vue` + `Form.vue` (ou arquivo único para telas simples):
   - Usa `AdminLayout`, `useForm` do `@inertiajs/vue3`, `defineProps`.
   - `Form.vue`: `useForm` inicializado com `props.item?.campo ?? ''`, `submit()` escolhe `form.put(...)` vs `form.post(...)` conforme edição/criação, erros via `form.errors.campo`.
   - `Index.vue`: layout responsivo (cards `sm:hidden` no mobile, tabela no desktop), empty state, `router.delete` com `confirm()`.
6. **Menu:** adicionar item em `resources/js/Layouts/AdminLayout.vue` (computed `navGroups`), condicionado ao papel quando necessário.
7. **App mobile (se preciso):** controller em `Api/V1/`, Form Request em `Requests/Api/`, API Resource em `Resources/`, rota em `routes/api.php`.

## Convenções de código

- Vue exclusivamente `<script setup>`. Sem TypeScript.
- **Tailwind** inline nas templates (nada de CSS modules/styled-components).
- **Dark mode** onipresente via classes `dark:` (estratégia `class`, composable `useTheme.js`). Sempre estilizar os dois temas.
- Layout responsivo padrão: cards no mobile, tabela/grid no desktop.
- Datas: `toLocaleDateString('pt-BR', ...)` no front, `translatedFormat` no back. Locale `pt_BR`.
- Navegação no front: `<Link href="...">` e `router.get/post/put/patch/delete` do Inertia (hrefs escritos como strings literais).
- PHP: classes `PascalCase`, arrow functions, null-safe (`?->`), tipos de retorno, constantes de classe para enums de status.
