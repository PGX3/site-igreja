# App Mobile (Flutter) consumindo a API do site-igreja

Documento de referência da decisão e do progresso da v1 do app mobile.

## Visão geral

App em Flutter, em repositório separado, consumindo a API REST deste projeto Laravel via Sanctum (Bearer token). Quem opera o app é a equipe da igreja (membros logados), não o público em geral.

### Funcionalidades da v1

1. **Cadastrar novos membros e visitantes** pelo celular, principalmente na entrada do culto, por qualquer usuário logado da equipe.
2. **Minhas escalas**: ver, confirmar ou recusar.
3. **Calendário de eventos e cultos**: visualizar os próximos.
4. **Criar e editar escalas**: somente pastor (qualquer grupo) e líder (apenas dos seus grupos), via `User::canManageGrupo()`.
5. **Push notifications** (FCM): membro recebe ao ser colocado em escala, etc.

### Decisões já tomadas

- Repositório Flutter separado deste projeto.
- Push via FCM (gratuito, sem limite prático). Já está integrado no backend.
- Quem cadastra pelo app: qualquer usuário autenticado.
- Quem cria escala pelo app: pastor + líder do grupo.
- Acesso ao app: admin gera senha temporária para o membro (não há cadastro self-service no app).

---

## Estado do backend antes desta sessão

Já estava implementado no projeto Laravel:

- Sanctum configurado com `HasApiTokens` em [app/Models/User.php](app/Models/User.php).
- Login/logout/me via Bearer token em [app/Http/Controllers/Api/V1/AuthController.php](app/Http/Controllers/Api/V1/AuthController.php).
- Perfil + troca de senha (com `current_password`) em [app/Http/Controllers/Api/V1/ProfileController.php](app/Http/Controllers/Api/V1/ProfileController.php) e [app/Http/Requests/Api/UpdatePasswordRequest.php](app/Http/Requests/Api/UpdatePasswordRequest.php).
- Cultos e eventos via API: [app/Http/Controllers/Api/V1/CultoController.php](app/Http/Controllers/Api/V1/CultoController.php), [app/Http/Controllers/Api/V1/EventoController.php](app/Http/Controllers/Api/V1/EventoController.php).
- Escalas (listar, confirmar, recusar) em [app/Http/Controllers/Api/V1/EscalaController.php](app/Http/Controllers/Api/V1/EscalaController.php).
- Push FCM completo: [app/Channels/FcmChannel.php](app/Channels/FcmChannel.php), [app/Notifications/EscalaConvite.php](app/Notifications/EscalaConvite.php), [app/Notifications/LembreteCulto.php](app/Notifications/LembreteCulto.php), [app/Notifications/LembreteEvento.php](app/Notifications/LembreteEvento.php).
- Device tokens: [app/Models/DeviceToken.php](app/Models/DeviceToken.php), migration [database/migrations/2026_06_12_185441_create_device_tokens_table.php](database/migrations/2026_06_12_185441_create_device_tokens_table.php), controller [app/Http/Controllers/Api/V1/DeviceTokenController.php](app/Http/Controllers/Api/V1/DeviceTokenController.php), `routeNotificationForFcm()` em `User`.
- Listener `EnviarNotificacaoEscalaConvite` que envia push quando o evento `MembroAdicionadoEscala` é disparado.
- Rate limiters: `api`, `login`, `contato`, `contato-oracao`, `devices`.
- Cadastro **web** (sem senha, sem login) em [app/Http/Controllers/CadastroController.php](app/Http/Controllers/CadastroController.php).
- CRUD admin (web/Inertia) de membros, visitantes, grupos, cultos, eventos, escalas, etc.

---

## O que foi implementado nesta sessão

### 1. Endpoint de cadastro via API (autenticado)

Para cadastrar membros e visitantes pelo app, com qualquer usuário logado.

Arquivos novos:
- [app/Http/Requests/Api/StoreCadastroRequest.php](app/Http/Requests/Api/StoreCadastroRequest.php): valida e normaliza o payload (mesmas regras do cadastro web), com `Cpf::normalize` em `prepareForValidation()`.
- [app/Http/Controllers/Api/V1/CadastroController.php](app/Http/Controllers/Api/V1/CadastroController.php): `store()` cria `Familia` (se houver endereço) e `User` em uma transação, retorna `201` com `UserResource`.

Rate limiter novo em [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php): `cadastro` (20/min por usuário ou IP).

Rota nova em [routes/api.php](routes/api.php):

```
POST /api/v1/cadastro       (auth:sanctum, throttle:cadastro)
```

### 2. CRUD de escalas via API

Estendido o [app/Http/Controllers/Api/V1/EscalaController.php](app/Http/Controllers/Api/V1/EscalaController.php) com `store`, `update`, `destroy`, `addMembro`, `removeMembro`. As ações de listar/show/confirmar/recusar continuam como estavam.

Permissões via `User::canManageGrupo($grupoId)`:
- Pastor pode tudo em qualquer grupo.
- Líder pode tudo apenas nos seus grupos.

Quando um membro é adicionado a uma escala (no `store` com membros ou no `addMembro`), o evento `MembroAdicionadoEscala` é disparado e o listener envia o push FCM via `EscalaConvite`.

Form Requests novos:
- [app/Http/Requests/Api/StoreEscalaRequest.php](app/Http/Requests/Api/StoreEscalaRequest.php): autoriza via `canManageGrupo($grupo_id)`. Valida `culto_id` ou `evento_id`, não os dois.
- [app/Http/Requests/Api/UpdateEscalaRequest.php](app/Http/Requests/Api/UpdateEscalaRequest.php): autoriza no grupo atual da escala e no novo (se mudou). Aceita campos parciais.
- [app/Http/Requests/Api/AddMembroEscalaRequest.php](app/Http/Requests/Api/AddMembroEscalaRequest.php): autoriza no grupo da escala.

Rotas novas em [routes/api.php](routes/api.php) (todas em `auth:sanctum`):

```
POST    /api/v1/escalas
PATCH   /api/v1/escalas/{escala}
DELETE  /api/v1/escalas/{escala}
POST    /api/v1/escalas/{escala}/membros
DELETE  /api/v1/escalas/{escala}/membros/{escalaMembro}
```

### 3. Endpoints auxiliares para selects do app

Para popular dropdowns na tela de criar/editar escala.

Arquivos novos:
- [app/Http/Controllers/Api/V1/GrupoController.php](app/Http/Controllers/Api/V1/GrupoController.php): retorna grupos que o usuário pode gerenciar (todos se pastor, só os seus se líder, vazio se membro comum).
- [app/Http/Resources/GrupoResource.php](app/Http/Resources/GrupoResource.php).
- [app/Http/Controllers/Api/V1/MembroController.php](app/Http/Controllers/Api/V1/MembroController.php): lista users com filtros `grupo_id`, `tipo`, `q` (busca por nome/email/telefone). Limita a 50 resultados.

Rotas novas em [routes/api.php](routes/api.php):

```
GET /api/v1/grupos
GET /api/v1/membros?grupo_id=&q=&tipo=
```

---

## O que ainda falta no backend

### Gerar senha temporária para membro (admin web)

Para o membro conseguir logar no app, o admin precisa setar uma senha. Hoje só pastor consegue, via `PUT /admin/usuarios/{usuario}/senha` (rota em [routes/web.php](routes/web.php)).

Sugestão pendente (não implementada):
- Action `gerarSenha(User $membro)` em [app/Http/Controllers/Admin/MembroController.php](app/Http/Controllers/Admin/MembroController.php) que:
  - gera senha aleatória de 8 caracteres em alfabeto sem caracteres ambíguos (sem 0, O, 1, I, l),
  - salva `Hash::make($senha)` no membro,
  - invalida tokens com `$membro->tokens()->delete()`,
  - retorna a senha em texto plano via `back()->with('senhaGerada', ...)` para exibição modal.
- Rota web: `POST /admin/membros/{membro}/gerar-senha` (no grupo `role:pastor,lider`).
- Botão "Senha" em [resources/js/Pages/Admin/Membros/Index.vue](resources/js/Pages/Admin/Membros/Index.vue) abrindo modal de confirmação + modal de exibição com botão de copiar.

Decisão tomada: adiar para depois do MVP do app.

---

## Endpoints disponíveis para o app Flutter (resumo)

Auth:
- `POST /api/v1/auth/login` (`email`, `password`, `device_name`)
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/logout-all`
- `GET  /api/v1/auth/me`

Perfil:
- `GET   /api/v1/profile`
- `PATCH /api/v1/profile`
- `PATCH /api/v1/profile/disponibilidade`
- `PATCH /api/v1/profile/senha` (requer `current_password`, `password`, `password_confirmation`)

Cadastro (app):
- `POST /api/v1/cadastro`

Cultos e eventos:
- `GET /api/v1/cultos`, `GET /api/v1/cultos/{id}`
- `GET /api/v1/eventos`, `GET /api/v1/eventos/{id}` (filtro `?incluir_passados=true`)

Escalas:
- `GET    /api/v1/escalas` (minhas escalas, filtros `status`, `from`, `to`)
- `GET    /api/v1/escalas/{id}`
- `POST   /api/v1/escalas` (pastor ou líder do grupo)
- `PATCH  /api/v1/escalas/{id}`
- `DELETE /api/v1/escalas/{id}`
- `POST   /api/v1/escalas/{id}/membros`
- `DELETE /api/v1/escalas/{id}/membros/{escalaMembroId}`
- `PATCH  /api/v1/escalas/membros/{escalaMembroId}/confirmar`
- `PATCH  /api/v1/escalas/membros/{escalaMembroId}/recusar`

Selects auxiliares:
- `GET /api/v1/grupos`
- `GET /api/v1/membros?grupo_id=&q=&tipo=`

Push:
- `POST   /api/v1/devices` (`token`, `platform`, `app_version`)
- `DELETE /api/v1/devices/{token}`

Contato (já existia):
- `POST /api/v1/contato/sugestao`
- `POST /api/v1/contato/pedido-oracao`

---

## App Flutter (repositório separado)

### Setup

Repositório novo (ex: `igreja-app`). `flutter create igreja_app --org com.suaigreja`.

Pacotes (`pubspec.yaml`):
- `dio`: HTTP client com interceptor de token.
- `flutter_secure_storage`: armazena o token Sanctum.
- `flutter_riverpod`: estado.
- `go_router`: navegação.
- `firebase_core` + `firebase_messaging`: push.
- `intl`: datas em pt_BR.
- `flutter_dotenv`: `API_BASE_URL` por ambiente.

### Estrutura sugerida

```
lib/
  core/
    api/        Dio client + interceptor + error handler
    storage/    secure_storage wrapper
    config/     env loader
  data/
    models/        User, Culto, Evento, Escala, EscalaMembro, Grupo
    repositories/  AuthRepo, CadastroRepo, CultoEventoRepo, EscalaRepo, ProfileRepo, DeviceTokenRepo
  presentation/
    screens/
      splash/
      login/
      home/                  tabs: Calendário, Escalas, Cadastrar, Perfil
      calendario/
      culto_detalhe/
      evento_detalhe/
      escalas/               minhas + gerenciadas (se pastor/líder)
      escala_detalhe/
      escala_form/           criar/editar (pastor/líder)
      cadastrar_pessoa/
      perfil/
      trocar_senha/
    widgets/
    providers/
  main.dart
```

### Telas v1

1. **Splash**: lê token, valida `GET /auth/me`, navega para Home ou Login.
2. **Login**: email + senha + device_name. Sucesso: salva token, registra FCM em `POST /devices`, vai pra Home.
3. **Home** com 4 tabs:
   - **Calendário**: lista combinada cultos (usar `proxima_data` do `CultoResource`) + eventos futuros, ordenada por data.
   - **Escalas**: "Minhas escalas". Se `user.role` é pastor ou líder, mostra também botão "Nova escala" e seção "Escalas que gerencio".
   - **Cadastrar**: formulário com todos os campos do `CadastroController` (nome, telefone, data nascimento, sexo, estado civil, CPF, endereço, cidade, UF, CEP, como conheceu, primeira visita, tipo, batizado). POST em `/cadastro`, snackbar "Cadastrado".
   - **Perfil**: dados, "Trocar senha", "Sair".
4. **Culto/Evento detalhe**: dados do Resource.
5. **Escala detalhe**: dados + lista de membros com status. Confirmar/Recusar se está na escala. Adicionar membro/Editar/Excluir se gerencia o grupo.
6. **Escala form**: select de grupo (`GET /grupos`), select de culto OU evento (não os dois), data, horas, observações. "Adicionar membro" abre busca em `GET /membros?grupo_id=X`.
7. **Trocar senha**: senha atual + nova + confirmação. PATCH em `/profile/senha`.

### Push notifications no app

- No `main.dart`: `firebase_messaging` pede permissão, obtém token, registra em `POST /devices` (somente após login).
- Foreground: snackbar ou banner.
- Tap na notificação: lê `data['type']` e `data['escala_id']` (ou similar), navega para a tela correspondente.
- No logout: `DELETE /devices/{token}` antes de limpar o storage.

---

## Como testar end-to-end

1. **Backend**: rodar `php artisan migrate` se houver migrations novas. Rodar `php artisan test`.
2. **Manual via Insomnia/Postman**: importar a collection de endpoints acima, testar:
   - Pastor cria senha para um membro (depois que essa feature for implementada).
   - Login no app via `POST /auth/login`.
   - Listar cultos e eventos.
   - Cadastrar um visitante.
   - Como líder, criar escala e adicionar membro. Confirmar que o membro recebeu push.
   - Como membro, confirmar/recusar a participação.
3. **Flutter**: rodar em emulador Android e iPhone real contra `php artisan serve` exposto via `ngrok` ou IP da rede local na `API_BASE_URL`.
4. **Push**: enviar notificação de teste do console Firebase para um device token registrado. Validar foreground, background e deep link.

---

## Ordem sugerida de execução restante

1. (Backend) Implementar "gerar senha" do admin para membros.
2. (Flutter) Setup do projeto + Splash + Login + Home com tabs Calendário e Escalas (consome o que já existe).
3. (Flutter) Cadastrar pessoa + Perfil + Trocar senha.
4. (Flutter) Criar/editar escala.
5. (Flutter) Firebase Messaging + registro de device token.
6. (Flutter) Build de release Android e iOS.

---

## Fora do escopo da v1

- Cadastro autoatendimento público no app.
- Fluxo de "primeiro acesso" por email ou troca obrigatória de senha.
- Reset de senha self-service.
- Confirmação de presença em eventos pelo app.
- Notificações segmentadas (todos os pushes vão pra todos os tokens do destinatário).
- Versão web do app (PWA).
