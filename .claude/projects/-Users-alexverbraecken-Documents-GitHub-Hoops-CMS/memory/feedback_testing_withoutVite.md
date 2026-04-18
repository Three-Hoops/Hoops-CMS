---
name: Always use withoutVite() in feature tests that render full pages
description: Feature tests that make GET requests rendering Inertia pages must call $this->withoutVite() to avoid CI failures caused by missing Vite manifest
type: feedback
---

Always call `$this->withoutVite()` in any feature test that makes a GET request that renders a full Inertia page (i.e. calls `assertInertia()`).

**Why:** The PHP CI job does not run `pnpm build`, so no Vite manifest exists. The `@vite` directive in `app.blade.php` throws when the manifest is missing, causing the response to fail with "Not a valid Inertia response." Tests that only POST (redirects) are unaffected — only tests that render a full page need this.

**How to apply:** Add `$this->withoutVite();` as the first line of any test that calls `->assertInertia()`.
