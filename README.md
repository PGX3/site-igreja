# Igreja em Charqueadas — Laravel + Inertia.js + Vue 3

## Stack
- Laravel 11
- Inertia.js v2
- Vue 3 (Composition API)
- Vite
- Tailwind CSS v3
- SQLite (ou MySQL)

---

## 1. Criar o projeto do zero

```bash
composer create-project laravel/laravel igreja-charqueadas
cd igreja-charqueadas
```

## 2. Instalar dependências Laravel

```bash
composer require inertiajs/inertia-laravel
composer require tightenco/ziggy
```

## 3. Instalar dependências Node

```bash
npm install vue@3 @inertiajs/vue3 @vitejs/plugin-vue
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

## 4. Copiar os arquivos deste projeto

Substitua / crie cada arquivo conforme a estrutura abaixo:

```
app/
  Http/
    Controllers/
      HomeController.php
      Admin/
        DashboardController.php
        CultoController.php
        TextoController.php
  Models/
    Culto.php
    Texto.php
database/
  migrations/
    xxxx_create_cultos_table.php
    xxxx_create_textos_table.php
  seeders/
    DatabaseSeeder.php
resources/
  js/
    app.js
    Pages/
      Home.vue
      Admin/
        Dashboard.vue
        Cultos/
          Index.vue
          Form.vue
        Textos/
          Index.vue
    Layouts/
      MainLayout.vue
      AdminLayout.vue
    Components/
      ShieldIcon.vue
      NavBar.vue
  css/
    app.css
  views/
    app.blade.php
routes/
  web.php
vite.config.js
tailwind.config.js
```

## 5. Configurar banco de dados

No `.env`:
```
DB_CONNECTION=sqlite
```

Depois:
```bash
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
```

## 6. Criar usuário admin

```bash
php artisan tinker
>>> \App\Models\User::create(['name'=>'Admin','email'=>'admin@igreja.com','password'=>bcrypt('senha123')]);
```

## 7. Rodar o projeto

```bash
npm run dev
php artisan serve
```

Acesse: http://localhost:8000  
Admin: http://localhost:8000/admin

---

## Credenciais padrão (após seeder)
- Email: admin@igreja.com
- Senha: senha123
# site-igreja
