# ZenFocos

Aplicação Laravel para gerenciamento de tarefas com técnica Pomodoro.

## Execução (rápido)

1. Instale dependências PHP e Node:

```bash
composer install
npm install
```

2. Configure ambiente e banco (SQLite padrão):

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
```

3. Compile assets e rode em dev:

```bash
npm run dev   # watch/hot-reload
php artisan serve
```

Para produção:

```bash
npm run build
php artisan migrate --force
```

## Estrutura (essencial)

```
zen-focos-laravel/
├── app/
│   ├── Http/Controllers/
│   └── Models/
├── database/
│   └── migrations/
├── public/
│   └── favicon.svg
├── resources/
│   ├── js/
│   └── views/
│       ├── tasks/
│       └── timer/
├── routes/
│   └── web.php
└── composer.json
```

## Onde ver detalhes

- Modelos, migrações e controllers: `app/` e `database/migrations`.
- Frontend JS: `resources/js/` (`task-cards.js`, `timer-store.js`, `timer.js`).
- Views: `resources/views/` (componentes de task e timer).
- Documentação de implementação e conceitos: `project-model.md`.

## Troubleshooting rápido

- Limpar cache Laravel:

```bash
php artisan cache:clear && php artisan view:clear && php artisan route:clear
```

- Permissões SQLite (se necessário):

```bash
chmod 664 database/database.sqlite
chmod 775 database/
```

---

Para detalhes de arquitetura e conceitos Laravel implementados consulte `project-model.md`.
