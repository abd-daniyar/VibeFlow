# VibeFlow — Agent Guide

## Quick start (Docker)

```bash
cp .env.example .env
echo "SESSION_DRIVER=file" >> .env   # no sessions table migration exists
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
npm install && npm run build
```

App at `http://localhost:8000`.

## Key commands

| Purpose | Command |
|---|---|
| SSH into app container | `docker compose exec app bash` |
| Run migrations + seed | `docker compose exec app php artisan migrate --seed` |
| Reset DB | `docker compose exec app php artisan migrate:fresh --seed` |
| Run tests | `docker compose exec app ./vendor/bin/phpunit` |
| Frontend dev | `npm run dev` (port 5173) |
| Frontend prod build | `npm run build` → `public/build/` |

## Architecture

- **Laravel 11** — no base `Controller.php`; controllers use `use AuthorizesRequests` directly
- **API-only** for now — web route (`/`) returns JSON, no Blade views
- **Sanctum** token auth — `auth:sanctum` middleware; all protected endpoints need `Authorization: Bearer <token>` + `Accept: application/json`
- **DB**: PostgreSQL 16, 8 custom migrations + Sanctum `personal_access_tokens`
- **Frontend**: Vue 3 + Pinia + Vue Router + Axios + Tailwind CSS, built by Vite into `public/build/`
- **Real-time**: Laravel Echo + Pusher (events broadcast to `PrivateChannel board.{id}`)
- **Nginx** serves `/app/public` on port 8000, proxies PHP to `app:9000`

## Project structure

```
routes/api.php          — all API routes (nested: board → column → task → comment)
app/Models/             — User, Board, Column, Task, Comment
app/Http/Controllers/Api/ — AuthController, BoardController, ColumnController, TaskController, CommentController
app/Events/             — BoardUpdated, TaskCreated/Updated/Deleted/Moved, CommentCreated/Deleted
database/seeders/       — DatabaseSeeder (2 users, 1 board, 4 columns, 8 tasks)
resources/js/           — Vue SPA (main.js, router/, stores/, pages/, components/, services/)
```

## Data model

```
User ── M:M (board_users) ── Board (roles: owner/editor/viewer)
Board ── hasMany ── Column (ordered, colored)
Column ── hasMany ── Task (ordered, priority, assignee, due_date)
Task ── hasMany ── Comment
```

## API

All protected routes are nested under `{board}/{column?}/{task?}`. See `routes/api.php:22-78` for the exact tree. Auth routes (`register`, `login`) are public; `logout` and `me` are protected.

## Gotchas

- **`composer install` is NOT in Dockerfile** — the volume mount (`./:/app`) overwrites vendor; run it manually after `docker compose up`
- **`SESSION_DRIVER=file`** is required in `.env` — no `sessions` table migration exists. Tests use `SESSION_DRIVER=array` (see `phpunit.xml:30`)
- **`config/database.php`** default is `sqlite`; `.env` overrides to `pgsql`
- **`version: '3.8'`** in docker-compose.yml is obsolete (warning only)
- **`public/build/`** is gitignored — frontend must be rebuilt after pulls
- **`.env`** is gitignored — always copy from `.env.example` and add `SESSION_DRIVER=file`
- **`config/cors.php`** and **`config/broadcasting.php`** are missing — Laravel 11 defaults used
- **composer.json** has 7 advisory audit ignores in `config.audit.ignore`

## Tests

- `tests/Feature/` and `tests/Unit/` — currently placeholder examples only
- phpunit.xml sets `SESSION_DRIVER=array`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`
- DB connection for tests is currently unconfigured (SQLite line commented out)
