# Hoops-CMS

[![PHP Analysis](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/php-analysis.yml/badge.svg)](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/php-analysis.yml)
[![PHP Tests](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/php-tests.yml/badge.svg)](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/php-tests.yml)
[![Vue Tests](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/vue-tests.yml/badge.svg)](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/vue-tests.yml)
[![Frontend](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/frontend.yml/badge.svg)](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/frontend.yml)
[![CodeQL](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/codeql.yml/badge.svg)](https://github.com/Three-Hoops/Hoops-CMS/actions/workflows/codeql.yml)
[![codecov](https://codecov.io/gh/Three-Hoops/Hoops-CMS/graph/badge.svg)](https://codecov.io/gh/Three-Hoops/Hoops-CMS)

A fully-featured, reusable CMS starter built on **Laravel 13**, **Vue 3**, and **Inertia.js v3**. Drop it into a new project and get a production-ready admin panel with content management, media library, user management, multilingual support, theming, and a headless API — without writing any of the boilerplate yourself.

---

## What Hoops-CMS does

### Content management

- Create and manage **posts**, **pages**, **categories**, and **tags** through a rich admin interface
- Write content with a **TipTap rich text editor** (headings, lists, links, inline images, formatting)
- Hierarchical **page structure** with parent/child relationships
- **Draft / published** workflow with **scheduled publishing**
- **Content revision history** — restore any previous version of a post or page
- **Bulk actions** — publish, draft, or delete multiple items at once
- **Draft preview** — see how content looks before publishing
- **Duplicate** posts and pages to a new draft in one click
- **Soft deletes** with a recoverable trash view for posts, pages, and media
- **Custom URL redirects** — automatically created when slugs change, preventing broken links
- **RSS and Atom feeds** auto-generated for posts
- **Sitemap** and **robots.txt** generation

### Multilingual content (i18n)

- All content fields (title, body, excerpt, meta, OG) stored as **per-locale JSON** via `spatie/laravel-translatable`
- Per-editor **language and timezone preferences**
- **Language switcher** in edit forms with per-locale completion indicators
- Public API serves content in the requested locale with a configurable fallback chain

### SEO

- Per-content **meta title, description, keywords**
- Per-content **Open Graph fields** (title, description, image, Twitter card, canonical URL) with a smart fallback chain
- **JSON-LD / Schema.org** structured data for posts (Article) and site (WebSite)
- Character count indicators and live social card preview in edit forms

### Media library

- **Upload, browse, and organise** files into folders
- **Image optimisation** on upload via Intervention Image
- **Alt text** per file, translatable per locale
- **Soft deletes** with a recoverable media trash
- **Used-by tracking** — warns before deleting a file that is referenced in content
- Supports images, PDFs, videos, and documents with appropriate previews
- **Inline image upload** from within the TipTap editor

### User management

- **Role-based access control** — `super_admin`, `editor`, `viewer`
- Defined capability matrix per role, enforced by Laravel Policies
- **User invitation** flow with branded email
- **Two-factor authentication (2FA)** for all admin users
- **Account suspension** without data loss (`is_active` flag)
- Per-user **avatar**, **locale**, and **timezone** preferences
- **API token management** — generate and revoke Sanctum personal access tokens
- **Last login tracking** for security auditing

### Theming

- **Admin dark / light / system mode** toggle, stored per user
- **Public site theme** — primary colour, secondary colour, fonts, and border radius stored in the database as CSS custom properties, applied without a redeploy
- **Theme presets** — one-click preset picker with visual swatches
- **Live theme preview** — see changes in a sandboxed iframe before saving

### Settings

- Site name, tagline, logo, favicon, social handles
- Translatable values for site-level strings (tagline, meta description)
- All public settings shared to every page via Inertia shared props and cached in Redis

### Navigation & menus

- Manage site navigation and menu structures from the admin

### Headless / public API

- Versioned read-only REST API at `/api/v1/` for external frontends (Nuxt, Next.js, mobile apps)
- Endpoints: posts, pages, categories, tags, settings
- Locale resolution via `Accept-Language` header or `?locale=` query param
- Rate limiting, HTTP cache headers (`ETag`, `Cache-Control`), and Sanctum token auth for private endpoints

### Audit & compliance

- **Activity log** via `spatie/laravel-activitylog` — tracks all content and user changes
- **GDPR-compliant** audit log retention with configurable pruning schedule
- **Scheduled cleanup** — expired tokens, stale autosaves, old soft-deleted records pruned automatically

---

## Tech stack

| Layer                     | Technology                  |
| ------------------------- | --------------------------- |
| Backend                   | Laravel 13 (PHP 8.4)        |
| Frontend                  | Vue 3 + TypeScript          |
| Bridge                    | Inertia.js v2               |
| State management          | Pinia                       |
| Styling                   | Tailwind CSS v4             |
| Rich text editor          | TipTap                      |
| Build tool                | Vite 8 (via pnpm)           |
| Unit / component tests    | Vitest + Vue Test Utils     |
| E2E tests                 | Cypress + cypress-axe       |
| PHP static analysis       | Larastan (PHPStan)          |
| Queue monitoring          | Laravel Horizon             |
| Search                    | Laravel Scout + Meilisearch |
| Cache / sessions / queues | Redis                       |
| Media processing          | Intervention Image          |
| Activity log              | spatie/laravel-activitylog  |
| Backups                   | spatie/laravel-backup       |
| i18n                      | spatie/laravel-translatable |
| API auth                  | Laravel Sanctum             |
| Error tracking            | Flare                       |

---

## Architecture

```
laravel/
  app/
    Enums/          UserRole, ContentStatus
    Http/
      Controllers/
        Admin/      All admin panel controllers
        Api/V1/     Headless API controllers
      Middleware/   Auth, role checks, Inertia requests, redirects, API locale
      Requests/     Form request validation
    Models/         User, Post, Page, Category, Tag, Media, Setting, Redirect
    Policies/       One policy per model, auto-discovered
    Support/        JsonLd, ThemePresets helpers
  resources/
    js/
      Pages/Admin/  One Vue page per admin route
      Components/   Reusable admin components (DataTable, TipTap, MediaPicker…)
      Layouts/      AdminLayout.vue
      stores/       Pinia stores for filter/UI state
      composables/  useAutosave, useUnsavedChanges, useThemeMode…
      types/        TypeScript interfaces for all models
  routes/
    web.php         Admin routes under /admin/*
    api.php         Public API routes under /api/v1/*
```

**Request flow:**

```
Browser → Laravel routes/web.php
        → Controller → Inertia::render('Admin/Posts/Index', $props)
        → First visit: Blade shell + full Vue app
        → Subsequent: XHR JSON → Vue page swap (SPA feel, no full reload)

API consumer → GET /api/v1/posts?locale=nl
             → ApiController → PostResource → JSON response with cache headers
```

**Key conventions:**

- All admin routes live under `/admin/*`, protected by `auth` + role middleware
- No Vue Router — all routing is Laravel-only; Inertia handles SPA navigation
- Forms use `useForm()` from Inertia directly; Pinia stores hold filter and UI state only
- Translatable model fields are `json` columns resolved to strings by `spatie/laravel-translatable`
- Multi-tenancy is explicitly out of scope — each deployment is a single-site installation

---

## Running the dev environment

You need two terminals, both in the `laravel/` directory.

**Terminal 1 — Laravel backend:**

```bash
cd laravel
php artisan serve
# → http://localhost:8000
```

**Terminal 2 — Vite frontend:**

```bash
cd laravel
pnpm dev
```

Always open **`localhost:8000`** in the browser. Vite runs in the background for HMR only.

### Docker (alternative)

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
# then run pnpm dev on the host for HMR
```

Update your `laravel/.env` to use the Docker service hostnames:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=hoops
DB_USERNAME=hoops
DB_PASSWORD=secret

REDIS_HOST=redis
```

The app is served at **`localhost:8000`** via Nginx. Vite still runs on the host for HMR.

---

## Setup

Requirements: PHP 8.4+, Composer, Node.js 22.12+, pnpm.

```bash
cd laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
pnpm install
```

With nvm: `nvm install 22 && nvm use 22` (Node 22.12+ required for Vite 8).

### Demo content

To seed sample posts, pages, categories, and media for local development:

```bash
php artisan db:seed --class=DemoSeeder
```

---

## Running tests

```bash
# PHP feature tests
php artisan test

# Vue unit / component tests
pnpm test

# E2E tests (requires php artisan serve running)
pnpm test:e2e

# PHP static analysis
composer analyse
```

## Analysing the bundle

```bash
pnpm build:analyse
```

Builds for production and opens an interactive treemap (`bundle-stats.html`) showing the composition and gzip/brotli sizes of every module in the JS bundle. Use this before releases to catch oversized dependencies or tree-shaking regressions.

---

## Implementation plan

The full implementation plan — 110 GitHub issues across 9 stages — is tracked in [`plan.md`](./plan.md) and on the [GitHub Issues page](https://github.com/Three-Hoops/Hoops-CMS/issues).

| Stage                         | Focus                              |
| ----------------------------- | ---------------------------------- |
| 0 — Pre-flight                | Tooling, DX, CI                    |
| 1 — Auth + Admin Shell        | Login, roles, layout, security     |
| 2 — Core Content              | Posts, pages, categories, tags     |
| 3 — Media Library             | Upload, folders, optimisation      |
| 4 — Users, Settings & Theming | User management, theme system      |
| 5 — Testing & Quality         | Feature tests, E2E, a11y           |
| 6 — Infrastructure & Ops      | Redis, queues, deployment          |
| 7 — Content Enhancements      | Navigation, revisions, search, RSS |
| 8 — Headless API              | Public REST API, locale, caching   |

© 2026 Alex Verbraecken / 3Hoops — licensed under BUSL 1.1. Free for non-commercial use; commercial use requires a license.
