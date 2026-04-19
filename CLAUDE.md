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
pnpm build:analyse        # Build + open bundle analyser (bundle-stats.html)
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
- **Testing**: Vitest + `@vue/test-utils` for unit/component tests (all in `resources/js/tests/`, mirroring the source structure); Cypress for e2e (requires `php artisan serve` running). **Always write tests when implementing new functionality** — see Testing Rules below.
- **Lazy loading**: `Model::preventLazyLoading()` is active in all non-production environments. If you see a `LazyLoadingViolationException`, fix it by eager-loading the relationship in the controller: `Post::with(['category', 'tags'])->paginate()`.
- **Vite manifest in PHP tests**: The CI PHP test job has no built frontend assets. Any feature test that hits an Inertia route (i.e. renders a Vue page) must call `$this->withoutVite()` at the start of the test, otherwise CI will fail with `Vite manifest not found`.
- **Vue SFC order**: always `<script setup lang="ts">` first, then `<template>`, then `<style>` (if needed). Never use Options API.

## Testing Rules

Every PR that adds or changes behaviour must ship with tests. No exceptions.

### PHP / Laravel (Pest)

| What you're adding | Test type | Location |
|--------------------|-----------|----------|
| Controller / route | Feature test (`tests/Feature/`) | Assert HTTP status, Inertia component, returned props |
| Service / action class | Unit test (`tests/Unit/`) | Pure logic, no HTTP layer |
| Model scope / relationship | Unit test | Use the in-memory SQLite DB from `.env.testing` |
| Form Request validation | Feature test | Test valid + invalid payloads |
| Artisan command | Feature test | `$this->artisan(...)` assertions |

Run: `php artisan test` (uses `.env.testing` automatically)

### Vue / TypeScript (Vitest)

| What you're adding | Test type | Location |
|--------------------|-----------|----------|
| Component with logic | Component test (`resources/js/tests/**/*.test.ts`) | Mount with `@vue/test-utils`, assert rendered output + emits |
| Pinia store | Unit test (`resources/js/tests/stores/*.test.ts`) | Test actions/getters in isolation |
| Composable | Unit test (`resources/js/tests/composables/*.test.ts`) | Call and assert return values |
| Full user flow | Cypress e2e (`cypress/e2e/`) | Only for critical happy paths |

Run: `pnpm test` (Vitest) or `pnpm test:e2e` (Cypress)

### Test structure

All tests must follow the **Arrange / Act / Assert** pattern with inline comments:

```ts
it('does something', () => {
    // Arrange
    const wrapper = mount(MyComponent)

    // Act
    await wrapper.find('button').trigger('click')

    // Assert
    expect(wrapper.find('.result').text()).toBe('done')
})
```

### When NOT to write a test

- Pure config files (vite.config.ts, phpstan.neon, tailwind theme)
- Build tooling / CI workflow changes
- Migrations (covered by the test suite running `migrate:fresh`)

## Working Approach

Before making any code changes, always explain what changes you plan to make and wait for the user to confirm before implementing. This applies to all tasks — bug fixes, new features, refactors, and configuration changes.

## Tracking Progress

Just before opening a pull request for an issue, update `plan.md` by changing the status indicator for that issue from ⬜ to ✅ and include that change in the same commit.

Every PR body must start with `Closes #<issue-number>` so GitHub automatically closes the issue on merge.

Every new GitHub issue must also be added to `plan.md` under the appropriate stage and phase before any implementation begins.

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
