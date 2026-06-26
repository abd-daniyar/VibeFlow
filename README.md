# VibeFlow

A real-time Kanban board application for organizing workflows and boosting team productivity.

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2)
- **Frontend**: Vue 3 + Vite + Pinia
- **Database**: PostgreSQL 16
- **Cache**: Redis
- **Web Server**: Nginx + PHP-FPM
- **Real-time**: Laravel Echo + WebSockets
- **Auth**: Laravel Sanctum (SPA)

## Prerequisites

- Docker & Docker Compose
- Node.js 20+ (for frontend dev)
- Composer (for PHP deps)

## Quick Start

```bash
# Copy environment file
cp .env.example .env

# Start containers
docker compose up -d

# Install PHP dependencies
docker compose exec app composer install

# Generate app key
docker compose exec app php artisan key:generate

# Run migrations and seed
docker compose exec app php artisan migrate --seed

# Install frontend dependencies
npm install

# Start Vite dev server
npm run dev
```

The app is available at `http://localhost:8000`.

## Demo Credentials

| Email | Password |
|---|---|
| john@example.com | password123 |
| jane@example.com | password123 |

A demo board "Project Alpha" with columns and sample tasks is seeded automatically.

## API Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/login` | No | Login, returns Sanctum token |
| POST | `/api/auth/logout` | Yes | Logout |
| GET | `/api/auth/user` | Yes | Get authenticated user |
| GET/POST | `/api/boards` | Yes | List / Create boards |
| GET/PUT/DELETE | `/api/boards/{board}` | Yes | Show / Update / Delete board |
| GET/POST | `/api/boards/{board}/columns` | Yes | List / Create columns |
| PUT/DELETE | `/api/boards/{board}/columns/{column}` | Yes | Update / Delete column |
| PUT | `/api/boards/{board}/columns/reorder` | Yes | Reorder columns |
| GET/POST | `/api/boards/{board}/columns/{column}/tasks` | Yes | List / Create tasks |
| PUT/DELETE | `/api/boards/{board}/columns/{column}/tasks/{task}` | Yes | Update / Delete task |
| PUT | `/api/boards/{board}/columns/{column}/tasks/{task}/move` | Yes | Move task between columns |
| PUT | `/api/boards/{board}/columns/{column}/tasks/reorder` | Yes | Reorder tasks |
| GET/POST | `/api/boards/{board}/columns/{column}/tasks/{task}/comments` | Yes | List / Create comments |
| PUT/DELETE | `/api/comments/{comment}` | Yes | Update / Delete comment |

All authenticated endpoints require `Authorization: Bearer <token>` header and `Accept: application/json`.

## Development

### Docker Services

```bash
docker compose up -d          # Start all services
docker compose down           # Stop all services
docker compose exec app bash  # SSH into the app container
```

### Frontend

```bash
npm run dev      # Start Vite dev server (port 5173)
npm run build    # Production build to public/build/
npm run lint     # Run ESLint
```

### Backend

```bash
php artisan make:model ModelName -m    # Create model with migration
php artisan make:controller Api/NameController  # Create API controller
php artisan migrate:fresh --seed        # Reset database and re-seed
```

## Project Structure

```
├── app/
│   ├── Events/          # Real-time events (BoardUpdated, TaskMoved, etc.)
│   ├── Http/
│   │   └── Controllers/Api/  # API controllers
│   ├── Models/          # Eloquent models
│   └── Providers/       # Service providers
├── database/
│   └── migrations/      # Database migrations
├── docker/
│   └── nginx/           # Nginx config
├── resources/
│   ├── js/              # Vue 3 components & stores
│   └── views/           # Blade views
├── routes/
│   └── api.php          # API route definitions
├── docker-compose.yml
├── Dockerfile
└── vite.config.js
```
