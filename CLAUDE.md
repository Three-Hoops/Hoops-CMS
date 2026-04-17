# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Hoops-CMS is a reusable CMS system built with a **Laravel 13** backend and a **Vue 3** frontend, bridged via **Inertia.js v2**. The Laravel application lives in the `laravel/` subdirectory. All frontend tooling is managed with **pnpm** from within `laravel/`.

## Development Commands

All frontend commands must be run from the `laravel/` directory.

### Frontend (run from `laravel/`)

```bash
pnpm install              # Install JS dependencies
pnpm dev                  # Start Vite dev server (HMR)
pnpm build                # Production asset build
pnpm test                 # Run Vitest unit/component tests
pnpm test:ui              # Vitest with browser UI
pnpm test:e2e             # Cypress e2e tests (headless, needs Laravel running)
pnpm test:e2e:open        # Open Cypress interactive runner
```

### Backend (run from `laravel/`)

```bash
composer install          # Install PHP dependencies
php artisan migrate       # Run database migrations
php artisan db:seed       # Seed the database
php artisan serve         # Start dev server at http://localhost:8000
php artisan test          # Run PHP/Pest tests
php artisan test --filter TestName  # Run a single test
```

### Environment Setup

```bash
cp laravel/.env.example laravel/.env
cd laravel && php artisan key:generate
cd laravel && php artisan migrate
cd laravel && pnpm install
```

Node.js 22.12+ is required (Vite 8 requirement). With nvm: `nvm install 22 && nvm use 22`.

## Architecture

### Project Layout

```
laravel/                        # Laravel application (work here)
  app/Http/
    Controllers/                # Inertia controllers
    Middleware/
      HandleInertiaRequests.php # Shares global props to all pages
  bootstrap/app.php             # Middleware registration (Laravel 13 style)
  resources/
    js/
      app.ts                    # Entry: bootstraps Inertia + Pinia + Vue
      Pages/                    # Inertia page components (1-to-1 with routes)
      stores/                   # Pinia stores (feature-based)
    css/app.css                 # Global styles (Tailwind v4)
    views/app.blade.php         # Single Blade shell loaded by Inertia
  routes/web.php                # Laravel routes returning Inertia::render()
  vite.config.ts                # Vite + laravel-vite-plugin + Vue + Vitest
  tsconfig.json                 # TypeScript, @/* alias → resources/js/
  cypress.config.ts             # Cypress e2e, baseUrl = localhost:8000
  cypress/e2e/                  # Cypress specs
```

### Request Flow

Browser → Laravel `routes/web.php` → Controller calls `Inertia::render('Folder/Page', $props)` → First visit: Blade shell + full Vue app is served → Subsequent navigation: XHR JSON response → Vue page component renders with props.

### Key Conventions

- **Routing**: Laravel-only. Vue Router is not used; Inertia handles SPA navigation.
- **State**: Pinia stores in `resources/js/stores/`. Global shared state (auth user, flash messages) goes through `HandleInertiaRequests::share()`.
- **Styling**: Tailwind CSS v4 via `@tailwindcss/vite` plugin.
- **Path alias**: `@/` resolves to `resources/js/` (configured in both `tsconfig.json` and `vite.config.ts`).
- **Testing**: Vitest + `@vue/test-utils` for unit/component tests (co-located as `*.spec.ts`); Cypress for e2e (requires `php artisan serve` running).

## Tracking Progress

Just before opening a pull request for an issue, update `plan.md` by changing the status indicator for that issue from ⬜ to ✅ and include that change in the same commit.

## Git Workflow

This project follows **GitHub Flow**: one branch per feature/fix, PR into `main`, merge after CI passes.

### Branch naming

| Type | Pattern | Example |
|------|---------|---------|
| Feature / phase | `feature/<issue>-description` | `feature/1-auth-shell` |
| Bug fix | `fix/<issue>-description` | `fix/12-login-redirect` |
| Chore / config | `chore/description` | `chore/eslint-setup` |

### Rules

- `main` is always deployable — never commit directly to it.
- Open a PR for every change; CI must pass before merging.
- Branch protection on `main` is enabled: PR + 1 approval required, force pushes blocked. Required status checks will be added once CI is configured (issue #9).
