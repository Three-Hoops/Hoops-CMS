# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Hoops-CMS is a reusable CMS system built with a **Laravel** (PHP) backend and a **Vue.js** frontend, bridged via **Inertia.js**. Package management on the JS side uses **pnpm**.

## Development Commands

### Frontend (JS/Vue)

```bash
pnpm install              # Install dependencies
pnpm dev                  # Start Vite dev server
pnpm build                # Production build
pnpm test                 # Run Vitest unit tests
pnpm test:ui              # Run Vitest with browser UI
pnpm test:e2e             # Run Cypress e2e tests (headless)
pnpm test:e2e:open        # Open Cypress interactive runner
pnpm lint                 # Lint TS/Vue files
```

### Backend (Laravel)

```bash
composer install          # Install PHP dependencies
php artisan migrate       # Run database migrations
php artisan db:seed       # Seed the database
php artisan serve         # Start Laravel dev server (http://localhost:8000)
php artisan test          # Run PHP test suite
php artisan test --filter TestName  # Run a single test
```

### Environment Setup

1. Install PHP and Composer (`brew install php composer` or use [Laravel Herd](https://herd.laravel.com))
2. Scaffold Laravel: `composer create-project laravel/laravel .` (run in project root)
3. Install the Inertia Laravel adapter: `composer require inertiajs/inertia-laravel`
4. Add `\App\Http\Middleware\HandleInertiaRequests::class` to the `web` middleware group in `bootstrap/app.php`
5. Create the root Blade view at `resources/views/app.blade.php` with the `@inertia` directive
6. Copy `.env.example` to `.env` and configure database, then run `php artisan key:generate`
7. Run `pnpm install` for JS dependencies

## Architecture

### Request Flow

Browser → Laravel router → Controller returns `Inertia::render('PageName', $props)` → Inertia serves the Vue component as a full page (first load) or XHR response (subsequent navigation) → Vue renders the page component with props.

### Frontend Structure

```
resources/
  js/
    app.ts          # Entry point — bootstraps Inertia + Pinia + Vue
    Pages/          # Inertia page components (one per route)
    stores/         # Pinia stores (feature-based)
  css/
    app.css         # Global styles
```

- **Routing** is handled by Laravel; Vue Router is not used.
- **State management** uses Pinia. Stores live in `resources/js/stores/`.
- **Page components** in `resources/js/Pages/` map 1-to-1 with Laravel routes via `Inertia::render('Folder/Page', $props)`.
- **Path alias**: `@/` resolves to `resources/js/`.

### Testing

- **Unit/component tests**: Vitest + `@vue/test-utils`, co-located with source or in `resources/js/**/*.spec.ts`
- **E2E tests**: Cypress, specs in `cypress/e2e/`. Targets `http://localhost:8000` (Laravel dev server).
- Config: `vite.config.ts` contains the Vitest config under the `test` key; Cypress config is in `cypress.config.ts`.

### Key Config Files

| File | Purpose |
|------|---------|
| `vite.config.ts` | Vite + laravel-vite-plugin + Vue + Vitest config |
| `tsconfig.json` | TypeScript, includes `@/*` path alias |
| `cypress.config.ts` | Cypress e2e config, baseUrl = localhost:8000 |
| `package.json` | pnpm scripts and JS dependencies |
