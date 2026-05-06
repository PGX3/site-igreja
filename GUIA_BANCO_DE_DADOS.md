# 🗄️ Guia de Banco de Dados — Igreja em Charqueadas

---

## Opções disponíveis

| Banco       | Recomendado para       | Dificuldade |
|-------------|------------------------|-------------|
| **SQLite**  | Testes / desenvolvimento local | ⭐ Fácil |
| **MySQL**   | Produção na maioria dos hospedeiros | ⭐⭐ Médio |
| **MariaDB** | Produção (Hostinger, Hostgator…) | ⭐⭐ Médio |
| **PostgreSQL** | Produção avançada (Railway, Render…) | ⭐⭐ Médio |

---

## 1. SQLite (mais simples — bom para começar)

O projeto já vem configurado com SQLite. Para usar:

**Arquivo `.env`:**
```
DB_CONNECTION=sqlite
```

O arquivo de banco fica em `database/database.sqlite` (já existe no projeto).

Para criar/recriar as tabelas:
```bash
php artisan migrate
```

> ⚠️ SQLite **não é recomendado para produção em servidor compartilhado** com múltiplos acessos simultâneos. Use MySQL nesse caso.

---

## 2. MySQL / MariaDB (recomendado para hospedagem)

### Passo 1 — Criar o banco no painel da hospedagem

Na maioria dos hospedeiros (cPanel, Plesk, Hostinger, HostGator):

1. Acesse o painel → **Banco de Dados MySQL** (ou "Bancos de Dados")
2. Crie um **novo banco de dados** (ex: `minha_igreja`)
3. Crie um **usuário** com senha forte
4. Dê **todas as permissões** a esse usuário no banco criado
5. Anote: **host, nome do banco, usuário e senha**

### Passo 2 — Configurar o `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1         # ou o host fornecido pela hospedagem
DB_PORT=3306
DB_DATABASE=minha_igreja  # nome do banco que você criou
DB_USERNAME=usuario_banco  # usuário que você criou
DB_PASSWORD=sua_senha_aqui # senha forte
```

> 💡 Em alguns hospedeiros o `DB_HOST` é `localhost`, em outros é um endereço como `mysql.seuhospedeiro.com`. Verifique no painel.

### Passo 3 — Rodar as migrações

```bash
php artisan migrate --force
```

O `--force` é necessário em ambiente de produção (`APP_ENV=production`).

---

## 3. PostgreSQL (Railway, Render, Supabase…)

### Configurar o `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=seu-host.railway.app
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Esses dados são fornecidos pelo painel da plataforma (ex: Railway → seu projeto → variáveis de ambiente).

### Rodar migrações:
```bash
php artisan migrate --force
```

---

## 4. Checklist completo para subir em produção

Execute os comandos abaixo no servidor (via SSH ou terminal da hospedagem):

```bash
# 1. Instalar dependências PHP
composer install --no-dev --optimize-autoloader

# 2. Instalar dependências JS e compilar
npm install
npm run build

# 3. Copiar .env.example para .env
cp .env.example .env

# 4. Editar o .env com os dados do banco e domínio
nano .env

# 5. Gerar a chave da aplicação (OBRIGATÓRIO)
php artisan key:generate

# 6. Rodar as migrações (cria todas as tabelas)
php artisan migrate --force

# 7. Otimizar para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Ajustar permissões das pastas (Linux)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 5. Criar o usuário administrador

Após rodar as migrações, crie o admin pelo terminal:

```bash
php artisan tinker
```

Dentro do Tinker:
```php
use App\Models\User;
User::create([
    'name'     => 'Admin Igreja',
    'email'    => 'admin@suaigreja.com.br',
    'password' => bcrypt('SenhaForteAqui123!'),
]);
exit
```

---

## 6. Variáveis de ambiente importantes (`.env`)

| Variável       | O que é | Exemplo |
|----------------|---------|---------|
| `APP_KEY`      | Chave secreta da app — gerada com `php artisan key:generate` | `base64:abc...` |
| `APP_ENV`      | Ambiente (`local` ou `production`) | `production` |
| `APP_DEBUG`    | Mostrar erros detalhados (`false` em produção!) | `false` |
| `APP_URL`      | URL completa do seu site | `https://suaigreja.com.br` |
| `DB_CONNECTION`| Driver do banco | `mysql` |
| `SESSION_ENCRYPT` | Encriptar sessões (recomendado `true`) | `true` |

---

## 7. Configuração do servidor web

### Apache (`.htaccess` — já incluso em `public/`)
Certifique-se que o `DocumentRoot` aponta para a pasta `public/` do projeto:
```
DocumentRoot /home/usuario/public_html/SiteIgreja/public
```

### Nginx
```nginx
server {
    listen 80;
    server_name suaigreja.com.br www.suaigreja.com.br;
    root /var/www/SiteIgreja/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 8. Troubleshooting comum

| Erro | Causa provável | Solução |
|------|----------------|---------|
| `SQLSTATE[HY000] [2002] Connection refused` | Host do banco errado | Verifique `DB_HOST` |
| `Access denied for user` | Usuário/senha errado | Revise `DB_USERNAME` e `DB_PASSWORD` |
| `Unknown database` | Banco não existe | Crie o banco no painel da hospedagem |
| `No application encryption key` | `APP_KEY` não gerada | Rode `php artisan key:generate` |
| Página em branco / erro 500 | `APP_DEBUG=false` ocultando erros | Temporariamente coloque `APP_DEBUG=true`, veja o erro, depois volte para `false` |
| Assets (CSS/JS) não carregam | Arquivos não compilados | Rode `npm run build` |

---

> 🔒 **Segurança:** nunca versione seu arquivo `.env` no Git. Ele já está no `.gitignore`.
