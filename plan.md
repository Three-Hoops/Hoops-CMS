# Hoops-CMS Implementation Plan

## Context
Building a reusable CMS starter on Laravel 13 + Vue 3 + Inertia.js v2. The project is a clean scaffold — no domain models, routes, or pages exist yet. The goal is a fully functional admin panel that can be dropped into future projects.

---

## GitHub Issues Index

Issues are ordered by implementation sequence. Complete each stage before moving to the next.

---

### Stage 0 — Pre-flight `pre-flight`
> Set up tooling and conventions before writing any domain code.

| Status | # | Issue | Labels |
|--------|---|-------|--------|
| ✅ | [#11](https://github.com/Three-Hoops/Hoops-CMS/issues/11) | Set up Git branching strategy | `documentation` |
| ✅ | [#10](https://github.com/Three-Hoops/Hoops-CMS/issues/10) | Configure Laravel Pint code style | `developer-experience` |
| ✅ | [#8](https://github.com/Three-Hoops/Hoops-CMS/issues/8) | Configure ESLint for TypeScript + Vue | `developer-experience` |
| ✅ | [#83](https://github.com/Three-Hoops/Hoops-CMS/issues/83) | Add commit hooks with Husky and lint-staged | `developer-experience` |
| ✅ | [#9](https://github.com/Three-Hoops/Hoops-CMS/issues/9) | Set up GitHub Actions CI pipeline | `infrastructure` |
| ✅ | [#104](https://github.com/Three-Hoops/Hoops-CMS/issues/104) | Add Larastan (PHPStan) for PHP static analysis | `developer-experience` |
| ✅ | [#90](https://github.com/Three-Hoops/Hoops-CMS/issues/90) | Add dependency vulnerability scanning to CI pipeline | `security`, `infrastructure` |
| ✅ | [#34](https://github.com/Three-Hoops/Hoops-CMS/issues/34) | Set up Dependabot for automated dependency updates | `infrastructure` |
| ✅ | [#48](https://github.com/Three-Hoops/Hoops-CMS/issues/48) | Add .env.testing for the test environment | `testing` |
| ✅ | [#5](https://github.com/Three-Hoops/Hoops-CMS/issues/5) | Add shadcn-vue component library | `developer-experience` |
| ✅ | [#7](https://github.com/Three-Hoops/Hoops-CMS/issues/7) | Set up Ziggy for Laravel route helpers in Vue | `developer-experience` |
| ✅ | [#59](https://github.com/Three-Hoops/Hoops-CMS/issues/59) | Add laravel-ide-helper for IDE autocompletion | `developer-experience` |
| ✅ | [#96](https://github.com/Three-Hoops/Hoops-CMS/issues/96) | Enable Model::preventLazyLoading() to catch N+1 queries | `performance`, `developer-experience` |
| ✅ | [#102](https://github.com/Three-Hoops/Hoops-CMS/issues/102) | Add Vite bundle analyser | `performance`, `developer-experience` |
| ⬜ | [#52](https://github.com/Three-Hoops/Hoops-CMS/issues/52) | Create model factories for all custom models *(deferred — implement alongside each model in phase 1+)* | `testing`, `developer-experience` |
| ⬜ | [#94](https://github.com/Three-Hoops/Hoops-CMS/issues/94) | Add demo content seeder for new project onboarding *(deferred — needs models from phase 2)* | `developer-experience`, `testing` |
| ⬜ | [#67](https://github.com/Three-Hoops/Hoops-CMS/issues/67) | Add Docker Compose for local development (PHP, MySQL, Redis) | `infrastructure`, `developer-experience` |
| ✅ | [#22](https://github.com/Three-Hoops/Hoops-CMS/issues/22) | Install Laravel Debugbar for local development | `infrastructure`, `developer-experience` |
| ✅ | [#12](https://github.com/Three-Hoops/Hoops-CMS/issues/12) | Set up local email with Mailtrap | `infrastructure` |

---

### Stage 1 — Auth + Admin Shell `phase-1`
> Working login/logout and the persistent admin layout. Everything else builds inside this shell.

| # | Issue | Labels |
|---|-------|--------|
| [#1](https://github.com/Three-Hoops/Hoops-CMS/issues/1) | Phase 1: Auth + Admin Shell | `auth`, `enhancement` |
| [#71](https://github.com/Three-Hoops/Hoops-CMS/issues/71) | Add locale column to users table for editor language preference | `auth`, `enhancement` |
| [#103](https://github.com/Three-Hoops/Hoops-CMS/issues/103) | Add timezone preference to users for correct scheduled publishing | `auth`, `enhancement` |
| [#77](https://github.com/Three-Hoops/Hoops-CMS/issues/77) | Track last_login_at on users | `auth`, `security` |
| [#85](https://github.com/Three-Hoops/Hoops-CMS/issues/85) | Add is_active flag to users for account suspension | `auth`, `security` |
| [#106](https://github.com/Three-Hoops/Hoops-CMS/issues/106) | Define and enforce viewer role capabilities | `auth`, `security` |
| [#84](https://github.com/Three-Hoops/Hoops-CMS/issues/84) | Add remember me to login form | `auth`, `enhancement` |
| [#109](https://github.com/Three-Hoops/Hoops-CMS/issues/109) | Add branded transactional email templates | `auth`, `enhancement` |
| [#89](https://github.com/Three-Hoops/Hoops-CMS/issues/89) | Implement Content Security Policy (CSP) for admin panel | `security` |
| [#82](https://github.com/Three-Hoops/Hoops-CMS/issues/82) | Make admin layout mobile-responsive | `enhancement` |
| [#14](https://github.com/Three-Hoops/Hoops-CMS/issues/14) | Configure rate limiting on the login endpoint | `security` |
| [#16](https://github.com/Three-Hoops/Hoops-CMS/issues/16) | Add security headers middleware | `security` |
| [#42](https://github.com/Three-Hoops/Hoops-CMS/issues/42) | Configure session security and timeout | `security` |
| [#46](https://github.com/Three-Hoops/Hoops-CMS/issues/46) | Add noindex meta tag to all admin pages | `security` |
| [#49](https://github.com/Three-Hoops/Hoops-CMS/issues/49) | Enforce password complexity policy for admin accounts | `security`, `auth` |
| [#37](https://github.com/Three-Hoops/Hoops-CMS/issues/37) | Add password reset (forgot password) flow | `auth`, `enhancement` |
| [#41](https://github.com/Three-Hoops/Hoops-CMS/issues/41) | Add custom Inertia error pages (404, 419, 500) | `enhancement` |
| [#60](https://github.com/Three-Hoops/Hoops-CMS/issues/60) | Admin dark/light mode toggle (per-user preference) | `theming`, `enhancement` |

---

### Stage 2 — Core Content `phase-2`
> Full CRUD for pages, posts, categories, and tags.

| # | Issue | Labels |
|---|-------|--------|
| [#70](https://github.com/Three-Hoops/Hoops-CMS/issues/70) | Add multilingual content support with spatie/laravel-translatable | `content`, `enhancement` |
| [#2](https://github.com/Three-Hoops/Hoops-CMS/issues/2) | Phase 2: Core Content (Pages, Posts, Categories, Tags) | `content`, `enhancement` |
| [#76](https://github.com/Three-Hoops/Hoops-CMS/issues/76) | Add parent_id to pages table for hierarchical page structure | `content`, `enhancement` |
| [#86](https://github.com/Three-Hoops/Hoops-CMS/issues/86) | Add duplicate action for posts and pages | `content`, `enhancement` |
| [#65](https://github.com/Three-Hoops/Hoops-CMS/issues/65) | Add per-post and per-page Open Graph / social meta fields | `content`, `enhancement` |
| [#69](https://github.com/Three-Hoops/Hoops-CMS/issues/69) | Add featured/pinned flag to posts | `content`, `enhancement` |
| [#17](https://github.com/Three-Hoops/Hoops-CMS/issues/17) | Add soft deletes to content models | `content` |
| [#40](https://github.com/Three-Hoops/Hoops-CMS/issues/40) | Add database indexes to content migrations | `performance` |
| [#43](https://github.com/Three-Hoops/Hoops-CMS/issues/43) | Handle slug collisions with auto-increment | `content` |
| [#45](https://github.com/Three-Hoops/Hoops-CMS/issues/45) | Wrap multi-step writes in database transactions | `content` |
| [#38](https://github.com/Three-Hoops/Hoops-CMS/issues/38) | Sanitise TipTap HTML output to prevent XSS | `security`, `content` |
| [#68](https://github.com/Three-Hoops/Hoops-CMS/issues/68) | Add autosave for rich text editor to prevent data loss | `content`, `enhancement` |
| [#110](https://github.com/Three-Hoops/Hoops-CMS/issues/110) | Warn users about unsaved changes before navigating away | `content`, `enhancement` |
| [#58](https://github.com/Three-Hoops/Hoops-CMS/issues/58) | Bulk actions on content lists (publish, draft, delete) | `content`, `enhancement` |
| [#75](https://github.com/Three-Hoops/Hoops-CMS/issues/75) | Add trash view and restore for soft-deleted content | `content`, `enhancement` |
| [#32](https://github.com/Three-Hoops/Hoops-CMS/issues/32) | Add draft preview for posts and pages | `content`, `enhancement` |
| [#72](https://github.com/Three-Hoops/Hoops-CMS/issues/72) | Add language switcher UI to post and page edit forms | `content`, `enhancement` |
| [#66](https://github.com/Three-Hoops/Hoops-CMS/issues/66) | Write Vue component tests for admin UI components | `testing` |

---

### Stage 3 — Media Library `phase-3`
> Upload, browse, and attach files; TipTap inline image uploads.

| # | Issue | Labels |
|---|-------|--------|
| [#3](https://github.com/Three-Hoops/Hoops-CMS/issues/3) | Phase 3: Media Library | `content`, `enhancement` |
| [#107](https://github.com/Three-Hoops/Hoops-CMS/issues/107) | Add soft deletes to media table | `content`, `enhancement` |
| [#73](https://github.com/Three-Hoops/Hoops-CMS/issues/73) | Make media alt text translatable (json column) | `content`, `enhancement` |
| [#87](https://github.com/Three-Hoops/Hoops-CMS/issues/87) | Track media usage and warn before deletion | `content`, `enhancement` |
| [#78](https://github.com/Three-Hoops/Hoops-CMS/issues/78) | Add media folder organisation | `content`, `enhancement` |
| [#79](https://github.com/Three-Hoops/Hoops-CMS/issues/79) | Handle non-image file types in media library UI | `content`, `enhancement` |
| [#21](https://github.com/Three-Hoops/Hoops-CMS/issues/21) | Add image optimisation on upload with Intervention Image | `content`, `performance` |
| [#56](https://github.com/Three-Hoops/Hoops-CMS/issues/56) | Configure PHP upload limits for media library | `infrastructure` |
| [#33](https://github.com/Three-Hoops/Hoops-CMS/issues/33) | Document and automate php artisan storage:link | `infrastructure`, `documentation` |

---

### Stage 4 — Users, Settings & Theming `phase-4`
> Super-admin manages users; site settings + public theme stored in DB.

| # | Issue | Labels |
|---|-------|--------|
| [#4](https://github.com/Three-Hoops/Hoops-CMS/issues/4) | Phase 4: User Management + Settings | `auth`, `enhancement` |
| [#108](https://github.com/Three-Hoops/Hoops-CMS/issues/108) | Make settings values translatable for multilingual sites | `content`, `enhancement` |
| [#39](https://github.com/Three-Hoops/Hoops-CMS/issues/39) | Add user profile page for all authenticated users | `enhancement` |
| [#95](https://github.com/Three-Hoops/Hoops-CMS/issues/95) | Add user avatar via media library | `auth`, `enhancement` |
| [#93](https://github.com/Three-Hoops/Hoops-CMS/issues/93) | Add API token management UI for Sanctum personal access tokens | `auth`, `enhancement` |
| [#97](https://github.com/Three-Hoops/Hoops-CMS/issues/97) | Add global search / command palette (Cmd+K) | `content`, `enhancement` |
| [#98](https://github.com/Three-Hoops/Hoops-CMS/issues/98) | Schedule periodic cleanup tasks | `infrastructure` |
| [#53](https://github.com/Three-Hoops/Hoops-CMS/issues/53) | Implement user invitation flow | `auth`, `enhancement` |
| [#15](https://github.com/Three-Hoops/Hoops-CMS/issues/15) | Add two-factor authentication (2FA) for admin users | `security`, `auth` |
| [#61](https://github.com/Three-Hoops/Hoops-CMS/issues/61) | Public theme CSS variables stored in settings (colors, fonts, border radius) | `theming` |
| [#62](https://github.com/Three-Hoops/Hoops-CMS/issues/62) | Theme presets with visual preset picker in Settings | `theming`, `enhancement` |
| [#63](https://github.com/Three-Hoops/Hoops-CMS/issues/63) | Live theme preview before saving | `theming`, `enhancement` |

---

### Stage 5 — Testing & Quality `testing`
> Cover all controllers and critical flows with tests.

| # | Issue | Labels |
|---|-------|--------|
| [#26](https://github.com/Three-Hoops/Hoops-CMS/issues/26) | Write PHP feature tests for all admin controllers | `testing` |
| [#27](https://github.com/Three-Hoops/Hoops-CMS/issues/27) | Write Cypress E2E tests for critical user flows | `testing` |
| [#92](https://github.com/Three-Hoops/Hoops-CMS/issues/92) | Accessibility (a11y) audit and remediation for admin panel | `testing`, `enhancement` |

---

### Stage 6 — Infrastructure & Ops `infrastructure`
> Queues, scheduler, deployment, monitoring. Can be parallelised with Stage 5.

| # | Issue | Labels |
|---|-------|--------|
| [#20](https://github.com/Three-Hoops/Hoops-CMS/issues/20) | Configure Redis for cache, sessions, and queues | `infrastructure`, `performance` |
| [#28](https://github.com/Three-Hoops/Hoops-CMS/issues/28) | Set up queue worker for background jobs | `infrastructure` |
| [#29](https://github.com/Three-Hoops/Hoops-CMS/issues/29) | Set up Laravel Horizon for queue monitoring | `infrastructure` |
| [#30](https://github.com/Three-Hoops/Hoops-CMS/issues/30) | Configure and document Laravel task scheduler | `infrastructure` |
| [#44](https://github.com/Three-Hoops/Hoops-CMS/issues/44) | Define and implement cache invalidation strategy | `performance` |
| [#6](https://github.com/Three-Hoops/Hoops-CMS/issues/6) | Set up production database connection | `infrastructure` |
| [#47](https://github.com/Three-Hoops/Hoops-CMS/issues/47) | Enforce HTTPS in production | `infrastructure`, `security` |
| [#54](https://github.com/Three-Hoops/Hoops-CMS/issues/54) | Configure log management and rotation | `infrastructure` |
| [#55](https://github.com/Three-Hoops/Hoops-CMS/issues/55) | Add maintenance mode to deployment script | `infrastructure` |
| [#57](https://github.com/Three-Hoops/Hoops-CMS/issues/57) | Extend health check endpoint with system diagnostics | `infrastructure` |
| [#19](https://github.com/Three-Hoops/Hoops-CMS/issues/19) | Set up automated database backups with spatie/laravel-backup | `infrastructure` |
| [#101](https://github.com/Three-Hoops/Hoops-CMS/issues/101) | Add automated backup restore verification | `infrastructure` |
| [#13](https://github.com/Three-Hoops/Hoops-CMS/issues/13) | Set up error tracking with Flare | `infrastructure` |
| [#31](https://github.com/Three-Hoops/Hoops-CMS/issues/31) | Write deployment guide | `infrastructure`, `documentation` |
| [#91](https://github.com/Three-Hoops/Hoops-CMS/issues/91) | Document and implement zero-downtime deployment strategy | `infrastructure`, `documentation` |

---

### Stage 7 — Content Enhancements `content`
> Advanced content features built on top of the core. Implement in priority order.

| # | Issue | Labels |
|---|-------|--------|
| [#99](https://github.com/Three-Hoops/Hoops-CMS/issues/99) | Add custom URL redirect management | `content`, `enhancement`, `performance` |
| [#100](https://github.com/Three-Hoops/Hoops-CMS/issues/100) | Add RSS/Atom feed for published posts | `content`, `enhancement` |
| [#105](https://github.com/Three-Hoops/Hoops-CMS/issues/105) | Add JSON-LD / Schema.org structured data for posts and pages | `content`, `enhancement` |
| [#51](https://github.com/Three-Hoops/Hoops-CMS/issues/51) | Add navigation and menu management | `content`, `enhancement` |
| [#18](https://github.com/Three-Hoops/Hoops-CMS/issues/18) | Add audit log with spatie/laravel-activitylog | `content`, `security` |
| [#23](https://github.com/Three-Hoops/Hoops-CMS/issues/23) | Implement scheduled content publishing | `content`, `enhancement` |
| [#24](https://github.com/Three-Hoops/Hoops-CMS/issues/24) | Add content revision history for posts and pages | `content`, `enhancement` |
| [#35](https://github.com/Three-Hoops/Hoops-CMS/issues/35) | Add sitemap and robots.txt generation | `content`, `enhancement` |
| [#25](https://github.com/Three-Hoops/Hoops-CMS/issues/25) | Add full-text search with Laravel Scout and Meilisearch | `content`, `performance`, `enhancement` |
| [#36](https://github.com/Three-Hoops/Hoops-CMS/issues/36) | Add content import and export (JSON/CSV) | `content`, `enhancement` |
| [#50](https://github.com/Three-Hoops/Hoops-CMS/issues/50) | Add GDPR data retention policy for audit log | `security` |

---

### Stage 8 — Headless / Public API `infrastructure` `content`
> Expose published content for consumption by external frontends.

| # | Issue | Labels |
|---|-------|--------|
| [#64](https://github.com/Three-Hoops/Hoops-CMS/issues/64) | Add public read-only API for headless content consumption | `infrastructure`, `content`, `enhancement` |
| [#74](https://github.com/Three-Hoops/Hoops-CMS/issues/74) | Add locale parameter handling to the public API | `infrastructure`, `content`, `enhancement` |
| [#80](https://github.com/Three-Hoops/Hoops-CMS/issues/80) | Add rate limiting to public API endpoints | `security`, `infrastructure` |
| [#88](https://github.com/Three-Hoops/Hoops-CMS/issues/88) | Add HTTP cache headers to public API responses | `performance`, `infrastructure` |
| [#81](https://github.com/Three-Hoops/Hoops-CMS/issues/81) | Configure queue failure handling and alerting | `infrastructure` |

---

## Architectural Decisions

- **All CMS routes** live under `/admin/*`, protected by `auth` middleware
- **Roles** stored as an enum column on `users` (`super_admin`, `editor`, `viewer`) — no separate roles table
- **Rich text** via TipTap (`@tiptap/vue-3`) — headless, Vue 3 native, outputs HTML
- **Media storage** via Laravel `Storage` facade, `public` disk, path `media/{year}/{month}/`
- **Inertia shared props**: `auth.user`, `flash.success/error`, `app.name`, `settings` (public only, cached)
- **Page naming**: all Vue pages under `resources/js/Pages/Admin/`
- **Forms**: use `useForm()` from `@inertiajs/vue3` directly — stores hold filter/UI state only
- **Multi-tenancy**: explicitly out of scope — each deployment is a single-site installation. No `site_id` columns needed.
- **i18n**: supported via `spatie/laravel-translatable` — translatable fields stored as JSON columns, no separate translation tables. See [#70](https://github.com/Three-Hoops/Hoops-CMS/issues/70). Slug remains a plain string; locale-prefixed routing is a per-project concern.

---

## Phase 1 — Auth + Admin Shell
> GitHub issue: [#1](https://github.com/Three-Hoops/Hoops-CMS/issues/1)

**Goal:** Working login/logout and a persistent admin layout with sidebar. Every later phase builds inside this shell.

### Migrations
- Edit existing `0001_01_01_000000_create_users_table.php` — add `role` enum (`super_admin`, `editor`, `viewer`) default `editor`

### PHP Files
- `app/Enums/UserRole.php` — backed enum with `canEdit()` and `canManageUsers()` helpers
- `app/Models/User.php` — add `role` to fillable, cast to `UserRole`
- `app/Http/Controllers/Admin/AuthController.php` — `showLogin()`, `login()`, `logout()`
- `app/Http/Controllers/Admin/DashboardController.php` — `index()` returning empty stats for now
- `app/Http/Requests/Admin/LoginRequest.php` — validates + calls `Auth::attempt()`
- `app/Http/Middleware/EnsureUserHasRole.php` — usage: `->middleware('role:super_admin,editor')`
- `app/Http/Middleware/HandleInertiaRequests.php` — share `auth`, `flash` (closures), `app.name`
- `bootstrap/app.php` — register `role` middleware alias; set `redirectGuestsTo('/admin/login')`
- `database/seeders/DatabaseSeeder.php` — seed a `super_admin` user
- `routes/web.php`:
  ```
  guest  → GET  /admin/login, POST /admin/login
  auth   → POST /admin/logout, GET /admin → DashboardController
  ```

### Vue Files
- `resources/js/types/models.ts` — `AuthUser`, `UserRole`, `SharedProps` interfaces
- `resources/js/stores/useAuthStore.ts` — reads `usePage().props.auth`, exposes `can(roles[])`
- `resources/js/Layouts/AdminLayout.vue` — sidebar nav, top bar, flash banner slot
- `resources/js/Components/Admin/FlashBanner.vue` — watches `flash`, auto-dismisses after 4s
- `resources/js/Pages/Admin/Auth/Login.vue` — standalone (no layout), `useForm()` for submission
- `resources/js/Pages/Admin/Dashboard.vue` — uses `AdminLayout`, placeholder stat cards

---

## Phase 2 — Core Content (Pages, Posts, Categories, Tags)
> GitHub issue: [#2](https://github.com/Three-Hoops/Hoops-CMS/issues/2)

**Goal:** Full CRUD for all four content types. Dashboard stats live.

### Migrations (in dependency order)
1. `create_categories_table` — `id, name, slug(unique), description(nullable), parent_id(nullable FK→self), timestamps`
2. `create_tags_table` — `id, name, slug(unique), timestamps`
3. `create_pages_table` — `id, title, slug(unique), content(longText nullable), content_json(json nullable), excerpt(text nullable), status(enum: draft/published default draft), meta_title, meta_description, meta_keywords, user_id(FK), published_at(nullable), timestamps`
4. `create_posts_table` — same as pages + `featured_image(string nullable), category_id(nullable FK→categories)`
5. `create_post_tag_table` — pivot: `post_id, tag_id`, composite primary key

### PHP Files
- `app/Enums/ContentStatus.php` — `Draft`, `Published`
- `app/Models/Category.php` — `parent()`, `children()`, `posts()` relationships
- `app/Models/Tag.php` — `posts()` belongsToMany
- `app/Models/Page.php` — `author()` belongsTo User, `status` cast, `scopePublished()`
- `app/Models/Post.php` — same as Page + `category()`, `tags()` belongsToMany, `featuredMedia()`
- `app/Http/Controllers/Admin/PageController.php` — resource (no `show`), paginated index
- `app/Http/Controllers/Admin/PostController.php` — resource (no `show`), eager-loads category+tags
- `app/Http/Controllers/Admin/CategoryController.php` — resource (no `show`)
- `app/Http/Controllers/Admin/TagController.php` — index, store, update, destroy only
- Form requests: `StorePage`, `UpdatePage`, `StorePost`, `UpdatePost` — slug uniqueness uses `Rule::unique()->ignore()`
- Policies: `PagePolicy`, `PostPolicy`, `CategoryPolicy`, `TagPolicy` — auto-discovered by Laravel convention
- `DashboardController` — populate stats (counts per type, recent posts)
- `routes/web.php` — add inside protected `admin` group:
  ```
  Route::resource('pages', PageController::class)->except('show')
  Route::resource('posts', PostController::class)->except('show')
  Route::resource('categories', CategoryController::class)->except('show')
  Route::resource('tags', TagController::class)->only(['index','store','update','destroy'])
  ```

### Vue Files
- `resources/js/Components/Admin/TipTapEditor.vue` — `v-model` HTML, toolbar: bold/italic/headings/lists/link/image
- `resources/js/Components/Admin/StatusBadge.vue` — draft (grey) / published (green) pill
- `resources/js/Components/Admin/DataTable.vue` — generic columns + row slot + empty state
- `resources/js/Components/Admin/ConfirmModal.vue` — delete confirmation
- `resources/js/Components/Admin/SlugInput.vue` — auto-generates from title (debounced)
- `resources/js/Components/Admin/Pagination.vue` — reads `links` from Inertia paginator
- `resources/js/Pages/Admin/Pages/Index.vue`, `Create.vue`, `Edit.vue`
- `resources/js/Pages/Admin/Posts/Index.vue`, `Create.vue`, `Edit.vue`
- `resources/js/Pages/Admin/Categories/Index.vue`, `Create.vue`, `Edit.vue`
- `resources/js/Pages/Admin/Tags/Index.vue` — combined list + inline create
- `resources/js/stores/usePostStore.ts` — filter state (status, category, search); `setFilter()` calls `router.get()` with `{ preserveState: true, only: ['posts'] }`
- `resources/js/stores/usePageStore.ts` — same shape, status + search only
- Update `types/models.ts` — add `Category`, `Tag`, `Page`, `Post` interfaces

### New npm packages
```
@tiptap/vue-3  @tiptap/pm  @tiptap/starter-kit
@tiptap/extension-image  @tiptap/extension-link
@tiptap/extension-placeholder  @tiptap/extension-underline
```

---

## Phase 3 — Media Library
> GitHub issue: [#3](https://github.com/Three-Hoops/Hoops-CMS/issues/3)

**Goal:** Upload + browse files; attach to posts (featured image); TipTap inline image uploads.

### Migrations
1. `create_media_table` — `id, disk(default public), path, filename, mime_type, size(bigInt), width(nullable), height(nullable), alt_text(nullable), user_id(FK), timestamps`
2. `add_featured_image_id_to_posts` — drop `featured_image` string column, add `featured_image_id(nullable FK→media set null)`

### PHP Files
- `app/Models/Media.php` — `url` accessor (`Storage::disk()->url()`), `isImage` accessor, `uploader()` belongsTo
- `app/Models/Post.php` — add `featuredMedia()` belongsTo Media
- `app/Http/Controllers/Admin/MediaController.php`:
  - `index()` — paginated, filterable by search + type
  - `store()` — validates file (max 10MB), stores via `Storage::putFile()`, captures dimensions via `getimagesize()`, returns JSON when `$request->expectsJson()` (for TipTap) else redirect
  - `update()` — alt_text only
  - `destroy()` — delete from disk + record
- `routes/web.php` — `Route::resource('media', ...)->only([index,store,update,destroy])`

### Vue Files
- `resources/js/Pages/Admin/Media/Index.vue` — grid/list toggle, dropzone upload, filter bar, alt text inline edit
- `resources/js/Components/Admin/MediaPicker.vue` — modal, emits `select(media)`, used by TipTap + post/page edit forms
- `resources/js/stores/useMediaStore.ts` — `items[]`, `upload(file)` with progress tracking via axios `onUploadProgress`
- Update `TipTapEditor.vue` — wire image extension upload handler to `admin.media.store` via axios

---

## Phase 4 — Users + Settings
> GitHub issue: [#4](https://github.com/Three-Hoops/Hoops-CMS/issues/4)

**Goal:** Super-admin manages users; site settings stored in DB, grouped by tab, cached in shared props.

### Migrations
1. `create_settings_table` — `id, key(unique), value(text nullable), type(enum: text/textarea/boolean/url/email/image), group(nullable default general), label, description(nullable), is_public(bool default false), timestamps`

### PHP Files
- `app/Models/Setting.php` — static `get(key, default)` and `set(key, value)` helpers; `set()` busts `Cache::forget('public_settings')`
- `app/Http/Controllers/Admin/UserController.php` — resource (no show), guarded by `role:super_admin`; prevent self-deletion in `destroy()`
- `app/Http/Controllers/Admin/SettingsController.php` — `index()` groups settings by `group`; `update()` loops submitted pairs calling `Setting::set()`
- Form requests: `StoreUserRequest`, `UpdateUserRequest` (password optional on update)
- `database/seeders/SettingsSeeder.php` — seeds default keys: `site_name, site_tagline, site_logo, favicon, meta_description, og_image, twitter_handle, facebook_url, instagram_url`
- `HandleInertiaRequests.php` — add `settings` key: `Cache::remember('public_settings', 3600, fn() => Setting::where('is_public',true)->pluck('value','key'))`
- `routes/web.php`:
  ```
  Route::resource('users', UserController::class)->except('show')->middleware('role:super_admin')
  Route::get/put('settings', SettingsController::class)->middleware('role:super_admin')
  ```

### Vue Files
- `resources/js/Pages/Admin/Users/Index.vue`, `Create.vue`, `Edit.vue`
- `resources/js/Pages/Admin/Settings/Index.vue` — tabs per group, dynamic field rendering by `type`, uses `MediaPicker` for image-type settings
- Update `types/models.ts` — add `User`, `Setting`, `SettingType`, `SettingGroup`

---

## Critical Files

| File | Phase |
|------|-------|
| `laravel/database/migrations/0001_01_01_000000_create_users_table.php` | 1 |
| `laravel/bootstrap/app.php` | 1 |
| `laravel/routes/web.php` | 1–4 |
| `laravel/app/Http/Middleware/HandleInertiaRequests.php` | 1, 4 |
| `laravel/resources/js/Layouts/AdminLayout.vue` | 1 |
| `laravel/resources/js/types/models.ts` | 1–4 |

---

## Verification

After each phase:

1. **Phase 1** — `php artisan serve` + `pnpm dev`, visit `localhost:8000/admin/login`, log in with seeded admin, confirm dashboard loads with sidebar and nav links, log out
2. **Phase 2** — Create a page and a post via the admin, confirm TipTap editor loads, save and verify records in DB via `php artisan tinker`; confirm filter/search changes URL without full reload
3. **Phase 3** — Upload an image via the media library, confirm file appears in `storage/app/public/media/`, insert image into a post via TipTap, verify URL resolves in browser
4. **Phase 4** — Create a new editor user, log in as that user and confirm settings/users nav is hidden; update a site setting and confirm it appears in `usePage().props.settings`
