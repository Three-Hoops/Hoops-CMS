# Hoops-CMS Implementation Plan

## Context
Building a reusable CMS starter on Laravel 13 + Vue 3 + Inertia.js v2. The project is a clean scaffold — no domain models, routes, or pages exist yet. The goal is a fully functional admin panel that can be dropped into future projects.

---

## GitHub Issues Index

### Pre-flight (do before Phase 1)
| # | Issue |
|---|-------|
| [#5](https://github.com/Three-Hoops/Hoops-CMS/issues/5) | Add shadcn-vue component library |
| [#7](https://github.com/Three-Hoops/Hoops-CMS/issues/7) | Set up Ziggy for Laravel route helpers in Vue |
| [#8](https://github.com/Three-Hoops/Hoops-CMS/issues/8) | Configure ESLint for TypeScript + Vue |
| [#9](https://github.com/Three-Hoops/Hoops-CMS/issues/9) | Set up GitHub Actions CI pipeline |
| [#10](https://github.com/Three-Hoops/Hoops-CMS/issues/10) | Configure Laravel Pint code style |
| [#11](https://github.com/Three-Hoops/Hoops-CMS/issues/11) | Set up Git branching strategy |

### CMS Phases
| # | Issue |
|---|-------|
| [#1](https://github.com/Three-Hoops/Hoops-CMS/issues/1) | Phase 1: Auth + Admin Shell |
| [#2](https://github.com/Three-Hoops/Hoops-CMS/issues/2) | Phase 2: Core Content (Pages, Posts, Categories, Tags) |
| [#3](https://github.com/Three-Hoops/Hoops-CMS/issues/3) | Phase 3: Media Library |
| [#4](https://github.com/Three-Hoops/Hoops-CMS/issues/4) | Phase 4: User Management + Settings |

### Security
| # | Issue |
|---|-------|
| [#14](https://github.com/Three-Hoops/Hoops-CMS/issues/14) | Configure rate limiting on the login endpoint |
| [#15](https://github.com/Three-Hoops/Hoops-CMS/issues/15) | Add two-factor authentication (2FA) for admin users |
| [#16](https://github.com/Three-Hoops/Hoops-CMS/issues/16) | Add security headers middleware |

### Data Integrity
| # | Issue |
|---|-------|
| [#17](https://github.com/Three-Hoops/Hoops-CMS/issues/17) | Add soft deletes to content models |
| [#18](https://github.com/Three-Hoops/Hoops-CMS/issues/18) | Add audit log with spatie/laravel-activitylog |
| [#19](https://github.com/Three-Hoops/Hoops-CMS/issues/19) | Set up automated database backups with spatie/laravel-backup |

### Performance
| # | Issue |
|---|-------|
| [#20](https://github.com/Three-Hoops/Hoops-CMS/issues/20) | Configure Redis for cache, sessions, and queues |
| [#21](https://github.com/Three-Hoops/Hoops-CMS/issues/21) | Add image optimisation on upload with Intervention Image |
| [#22](https://github.com/Three-Hoops/Hoops-CMS/issues/22) | Install Laravel Debugbar for local development |

### Content Features
| # | Issue |
|---|-------|
| [#23](https://github.com/Three-Hoops/Hoops-CMS/issues/23) | Implement scheduled content publishing |
| [#24](https://github.com/Three-Hoops/Hoops-CMS/issues/24) | Add content revision history for posts and pages |
| [#25](https://github.com/Three-Hoops/Hoops-CMS/issues/25) | Add full-text search with Laravel Scout and Meilisearch |

### Testing
| # | Issue |
|---|-------|
| [#26](https://github.com/Three-Hoops/Hoops-CMS/issues/26) | Write PHP feature tests for all admin controllers |
| [#27](https://github.com/Three-Hoops/Hoops-CMS/issues/27) | Write Cypress E2E tests for critical user flows |

### Infrastructure & Operations
| # | Issue |
|---|-------|
| [#6](https://github.com/Three-Hoops/Hoops-CMS/issues/6) | Set up production database connection |
| [#12](https://github.com/Three-Hoops/Hoops-CMS/issues/12) | Set up local email with Mailtrap |
| [#13](https://github.com/Three-Hoops/Hoops-CMS/issues/13) | Set up error tracking with Flare |
| [#28](https://github.com/Three-Hoops/Hoops-CMS/issues/28) | Set up queue worker for background jobs |
| [#29](https://github.com/Three-Hoops/Hoops-CMS/issues/29) | Set up Laravel Horizon for queue monitoring |
| [#30](https://github.com/Three-Hoops/Hoops-CMS/issues/30) | Configure and document Laravel task scheduler |
| [#31](https://github.com/Three-Hoops/Hoops-CMS/issues/31) | Write deployment guide |
| [#33](https://github.com/Three-Hoops/Hoops-CMS/issues/33) | Document and automate php artisan storage:link |
| [#34](https://github.com/Three-Hoops/Hoops-CMS/issues/34) | Set up Dependabot for automated dependency updates |

### Content Features (additional)
| # | Issue |
|---|-------|
| [#32](https://github.com/Three-Hoops/Hoops-CMS/issues/32) | Add draft preview for posts and pages |
| [#35](https://github.com/Three-Hoops/Hoops-CMS/issues/35) | Add sitemap and robots.txt generation |
| [#36](https://github.com/Three-Hoops/Hoops-CMS/issues/36) | Add content import and export (JSON/CSV) |

---

## Architectural Decisions

- **All CMS routes** live under `/admin/*`, protected by `auth` middleware
- **Roles** stored as an enum column on `users` (`super_admin`, `editor`, `viewer`) — no separate roles table
- **Rich text** via TipTap (`@tiptap/vue-3`) — headless, Vue 3 native, outputs HTML
- **Media storage** via Laravel `Storage` facade, `public` disk, path `media/{year}/{month}/`
- **Inertia shared props**: `auth.user`, `flash.success/error`, `app.name`, `settings` (public only, cached)
- **Page naming**: all Vue pages under `resources/js/Pages/Admin/`
- **Forms**: use `useForm()` from `@inertiajs/vue3` directly — stores hold filter/UI state only

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
