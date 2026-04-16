# Hoops-CMS

CMS System that can be used for future projects, built with a Laravel backend and Vue.js frontend, bridged via Inertia.js.

## Stack

- **Backend**: Laravel 13 (PHP)
- **Frontend**: Vue 3 + TypeScript
- **Bridge**: Inertia.js v2
- **State management**: Pinia
- **Styling**: Tailwind CSS v4
- **Build tool**: Vite 8 (via pnpm)
- **Unit/component testing**: Vitest + Vue Test Utils
- **E2E testing**: Cypress

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

Always open **`localhost:8000`** in the browser. Vite runs in the background for hot module replacement only.

## How the frontend connects to the backend

Inertia replaces the traditional API + fetch pattern. There is no JSON API needed for page rendering. Instead, Laravel routes return a Vue component name and props directly:

```
Browser visits localhost:8000
       ↓
Laravel route returns Inertia::render('Dashboard', ['user' => $user])
       ↓
Inertia serves resources/js/Pages/Dashboard.vue
with $user available as a Vue prop
```

### Example: Passing data from Laravel to Vue

**Route** (`routes/web.php`):
```php
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'user' => Auth::user(),
        'stats' => ['total' => 42],
    ]);
});
```

**Page component** (`resources/js/Pages/Dashboard.vue`):
```vue
<script setup lang="ts">
defineProps<{
    user: { name: string; email: string }
    stats: { total: number }
}>()
</script>

<template>
    <h1>Welcome, {{ user.name }}</h1>
    <p>Total items: {{ stats.total }}</p>
</template>
```

Props flow directly from PHP into your Vue component — no `fetch`, no REST endpoint needed for page data.

### Navigation

Use Inertia's `<Link>` instead of `<a>` tags for SPA-style navigation without full page reloads:

```vue
<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <Link href="/dashboard">Go to dashboard</Link>
</template>
```

### Sending data back (forms)

Use `useForm` from Inertia instead of `fetch`/`axios`:

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const form = useForm({ title: '', body: '' })

function submit() {
    form.post('/posts') // hits a Laravel POST route
}
</script>

<template>
    <form @submit.prevent="submit">
        <input v-model="form.title" />
        <span v-if="form.errors.title">{{ form.errors.title }}</span>
        <button :disabled="form.processing">Save</button>
    </form>
</template>
```

Laravel validation errors automatically appear in `form.errors`.

### When to use a traditional API

Use `routes/api.php` + `fetch`/`axios` only for interactions that happen after the page has loaded — real-time search, infinite scroll, reactive data fetching. Standard page loads and form submissions go through Inertia and need no API.

## Setup

Requirements: PHP 8.2+, Composer, Node.js 22.12+, pnpm.

```bash
cd laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
pnpm install
```
