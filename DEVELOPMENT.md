# VibeFlow - Development Guide

## Overview

VibeFlow is a Kanban board application built with Laravel, Vue.js, and PostgreSQL.

## Tech Stack

- **Backend**: Laravel 11 with Laravel Sanctum (API authentication)
- **Frontend**: Vue 3 with Vite, Pinia (state management), Vue Router
- **Database**: PostgreSQL 16
- **Cache/Session**: Redis
- **Containerization**: Docker & Docker Compose
- **Styling**: Tailwind CSS

## Quick Start

### Prerequisites

- Docker & Docker Compose installed

### Installation

1. **Clone repository**
   ```bash
   git clone <repo-url>
   cd VibeFlow
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Start containers**
   ```bash
   docker-compose up -d
   ```

4. **Install dependencies**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app npm install
   ```

5. **Generate application key**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Build frontend**
   ```bash
   docker-compose exec app npm run build
   ```

7. **Access the application**
   - Frontend: http://localhost:8000
   - API: http://localhost:8000/api

## Features to Develop

- [x] User authentication
- [x] Board management
- [x] Kanban columns and tasks
- [ ] Drag-and-drop functionality
- [ ] Real-time updates
- [ ] Team collaboration
- [ ] Task assignments
- [ ] Comments and attachments
- [ ] Notifications
- [ ] Activity history

## API Routes

See `routes/api.php` for all available endpoints.

## License

Apache License 2.0
