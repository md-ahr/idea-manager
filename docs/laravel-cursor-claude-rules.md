# Laravel — AI & team rules (architecture, patterns, guardrails)

This document is **project-agnostic**: copy it into a new Laravel repo, fill in [§ Project baseline](#project-baseline), then wire it for **Cursor** (`.cursor/rules/`), **Claude** (Project Instructions), or **`AGENTS.md`**. It encodes a consistent **Laravel-style layered architecture** and how assistants should work without derailing the codebase.

---

## Table of contents

1. [How to use this file](#how-to-use-this-file)
2. [Project baseline](#project-baseline)
3. [Architecture overview](#architecture-overview)
4. [Request lifecycle](#request-lifecycle)
5. [HTTP layer: controllers & routes](#http-layer-controllers--routes)
6. [Actions (application services)](#actions-application-services)
7. [Form requests](#form-requests)
8. [Authorization: policies & gates](#authorization-policies--gates)
9. [Eloquent: models, casts, queries](#eloquent-models-casts-queries)
10. [APIs & Sanctum (when applicable)](#apis--sanctum-when-applicable)
11. [Notifications, mail & queues](#notifications-mail--queues)
12. [Blade, Vite & front-end touchpoints](#blade-vite--front-end-touchpoints)
13. [Testing with Pest](#testing-with-pest)
14. [When to add services, repositories, events, DTOs](#when-to-add-services-repositories-events-dtos)
15. [Security checklist](#security-checklist)
16. [Migrations & data](#migrations--data)
17. [Code style & tooling](#code-style--tooling)
18. [Rules for AI assistants (Cursor, Claude, etc.)](#rules-for-ai-assistants-cursor-claude-etc)
19. [New feature checklist](#new-feature-checklist)
20. [Appendix: Cursor rule frontmatter (optional)](#appendix-cursor-rule-frontmatter-optional)

---

## How to use this file

| Goal | What to do |
|------|------------|
| **Cursor** | Add a rule under `.cursor/rules/` (see [appendix](#appendix-cursor-rule-frontmatter-optional)) pointing at this file or pasting a trimmed section; set `globs: "**/*.{php,blade.php}"` or `alwaysApply: true` per team preference. |
| **Claude / other IDEs** | Paste §§ 3–19 into Project Instructions, or keep this file in `docs/` and say “follow `docs/laravel-cursor-claude-rules.md`”. |
| **Human onboarding** | Same source of truth: architecture + “how we use Laravel here”. |

Update **[§ Project baseline](#project-baseline)** whenever major versions change.

---

## Project baseline

*Fill in when you copy this file into a repository.*

| Item | Value (example) |
|------|-------------------|
| **PHP** | e.g. 8.4 |
| **laravel/framework** | e.g. 12.x (verify in `composer.json`) |
| **Testing** | e.g. Pest 4, PHPUnit 12 |
| **CSS/JS** | e.g. Tailwind v4, Vite |
| **Auth** | e.g. session + Fortify, or Breeze, or API token (Sanctum) |
| **Primary domain** | e.g. e-commerce, SaaS admin, content API — one line |

**Conventions in this document assume:** server-rendered routes or a mixed app with an SPA/API; **Pest** for tests; **Form requests** for non-trivial input; **single-purpose Action classes** for write flows. Adjust names of folders (`app/Actions` vs `app/Services/Actions`) to match the repo, but keep the **same responsibilities**.

---

## Architecture overview

| Layer | Responsibility | Keep it… |
|-------|----------------|----------|
| **Routes** | HTTP verbs, URIs, middleware, **named** route names | Small; no business logic. |
| **Controllers** | Single HTTP concern per action; orchestrate, don’t implement domain rules in full | **Thin**: validate (or delegate to Form request), authorize, call Action / query, return response. |
| **Form requests** | Validation, authorization hook, input normalization | Rich when inputs are nested or dirty. |
| **Actions** | One use case per class: create order, update profile, import rows | **Transactional** where multiple writes must succeed or fail together. |
| **Models** | Persistence, relations, simple query helpers, casts | Not a dumping ground for HTTP or huge reports (extract query objects or services when needed). |
| **Policies** | “Can this user do X on this model?” | Named by **intent** (`update`, `delete`, `manageBilling`) not only by REST verb. |
| **Views / API resources** | Presentation; no DB queries in blind loops in Blade (eager load in the controller) | Components / JSON shape only. |

**Preferred write flow:** `Route` → `FormRequest` (validate + `prepareForValidation`) → `Controller` (authorize) → `Action` (optionally `DB::transaction()`) → `Model` / `Storage` / `Mail`.

**Preferred read flow:** `Route` → `Controller` → Eloquent (with eager loads and scopes) → `view()` or `Resource`.

---

## Request lifecycle

1. **Middleware** (auth, throttle, `verified`, custom).
2. **Route model binding** — implicit `{order}`; use scoped bindings when a model must belong to the current user or tenant.
3. **Form request** — rules run; `authorize()` if used for request-level auth.
4. **Controller** — `authorize('ability', $model)` or `Gate::authorize(...)`; call Action with validated / safe data.
5. **Response** — redirect with flash, JSON, or view.

Do not skip authorization because validation passed.

---

## HTTP layer: controllers & routes

**Controllers**

- **GET** `index` / `show`: may query the database; **eager load** what the view or resource needs; return `view` or `Resource::collection`.
- **POST/PUT/PATCH/DELETE:** prefer **Form request** + **Action**; return `RedirectResponse` or `JsonResponse` with consistent status codes.
- Use `to_route('name', $params)` and `back()`; flash messages with `->with('key', 'message')` or session flash conventions the front end already expects.
- Avoid fat controllers: no multi-step business rules, no ad hoc `DB::` across many tables (that belongs in an Action or domain service).

**Routes**

- **Every** route that is linked, tested, or redirected to should have a **`name()`**.
- Group by middleware: `auth`, `auth:sanctum`, `guest`, `verified`, `throttle:…`.
- Use `Route::resource` or explicit verbs consistently; if you use API versioning, prefix (`/api/v1/...`).

**Binding & multi-tenancy**

- If a resource must only be visible to its owner, use `Route::bind` or scoped `Route::get('orders/{order}', ...)` with `->scopeBindings()` or policy resolution so `404` is returned for other users’ IDs where appropriate.

---

## Actions (application services)

**Naming & placement**

- One class per use case, e.g. `CreateOrder`, `CancelSubscription`, `RegisterUser`.
- Namespace e.g. `App\Actions` (or your agreed folder).

**Shape**

- **Entry point:** `public function handle(...): mixed` (return a model, DTO, or void as needed).
- **Constructor injection** for dependencies (repositories, HTTP clients, other actions).
- **Current user:** Either pass `User` from the controller (`$request->user()`), or use Laravel’s `#[\Illuminate\Container\Attributes\CurrentUser] User $user` in the action constructor **if** your installed framework version documents support—**verify in version-specific docs** before relying on attributes.

**Data**

- Accept **already validated** arrays or small value objects; whitelist columns with `collect($data)->only([...])` before `create` / `update`.
- **Files:** check presence, then `store()` / `storePublicly()` on the configured disk; avoid leaking absolute paths in responses.

**Transactions**

- Use `DB::transaction(function () { ... });` when multiple rows or related models must change atomically.

**Idempotency (APIs & payments)**

- For critical external calls, design keys, idempotency headers, or “already processed” guards as the product requires—don’t double-charge or double-ship in retries.

---

## Form requests

- **`rules(): array`** — use `Rule::enum(BackedEnum::class)` for enums; use nested `*.field` rules for arrays.
- **`prepareForValidation()`** — strip empty lines from repeated fields, normalize booleans, merge defaults—so the database never sees “half-clean” input.
- **`authorize(): bool`** — return `$this->user()->can('update', $this->route('order'))` when policy checks belong here, **or** keep policies in the controller only—**pick one style per app**.
- **Passing to Actions:** use `$this->safe()->all()` or `$request->safe()->all()` in the controller to avoid unvalidated input.

---

## Authorization: policies and gates

- **Register** policies in `AppServiceProvider` (or use auto-discovery if configured).
- **Name methods** after real permissions: `view`, `update`, `delete`, `restore`, or domain terms like `invoice`.
- In controllers, prefer `$this->authorize('update', $order)` (with `AuthorizesRequests` on the base controller) **or** `Gate::authorize('update', $order)` if the project already uses the latter everywhere.
- **API:** use the same policies for token-authenticated users; add abilities to tokens when using first-party clients if you use Sanctum that way.

---

## Eloquent: models, casts, queries

- **`declare(strict_types=1);`** in new PHP files if the project uses strict types consistently.
- **Mass assignment:** `protected $fillable` or `#[Fillable([...])]`; avoid uncontrolled `$guarded = []` in production. If the project uses `Model::unguard()` globally, be **stricter** in Form requests and Actions.
- **Casts:** `protected function casts(): array` (preferred in modern Laravel) for enums, dates, JSON, `array`, `AsArrayObject`, hashed passwords, etc.
- **Enums:** Backed `string` or `int` enums for columns; add helpers like `label()` for UI.
- **N+1:** Eager load in the controller: `$orders->load('items')` or `Order::with('items')`; use `Model::shouldBeStrict()` in development to catch unexpected lazy loads if your team enables it.
- **Scopes:** Local scopes for `active()`, `forUser($user)`; avoid cross-model god-methods on a single `User` model.
- **Chunking** large exports/imports with `chunkById` or cursors, not unbounded `->get()`.

---

## APIs & Sanctum (when applicable)

- **JSON shape:** Eloquent **API Resources** and optional **Resource collections**; version routes if public API consumers exist.
- **Validation:** same Form request classes or dedicated API form requests, shared or duplicated intentionally per surface.
- **Rate limiting:** `Route::middleware('throttle:api')` or custom limiters; stricter for auth and password reset.
- **Sanctum:** SPA cookie vs personal access tokens as documented; don’t mix patterns without understanding CSRF and CORS for SPAs.

---

## Notifications, mail & queues

- **Notifications** for email, Slack, etc.; `Notification::route('mail', $address)` when the recipient is not a `notifiable` model (e.g. old email on profile change).
- **Queues:** set `queue` on notification/job classes; configure workers in production; **failed jobs** and retries policy must match idempotency of handlers.

---

## Blade, Vite & front-end touchpoints

- **Layout:** one root layout that includes `@vite([...])`, CSRF, meta, flash regions, and main nav.
- **Components:** `resources/views/components` for form fields, errors, cards—reuse before copy-paste.
- **Stability for tests:** `data-testid`, `dusk` attributes, or framework-supported aliases—avoid selectors that only match Tailwind class strings.
- If the UI does not show asset changes, run `npm run dev` or `npm run build` (or your `composer` script) before assuming a bug in PHP.

---

## Testing with Pest

- **Database:** `RefreshDatabase` (or `LazilyRefreshDatabase`) in `Pest.php` for folders that need a clean DB; don’t over-apply to pure unit tests that don’t touch the DB.
- **HTTP:** `actingAs($user)`; `get`, `post`, `patch`, `assertSessionHas`, `assertForbidden`, `assertRedirect`.
- **Policies:** `Gate::forUser($user)->denies('update', $order)` in feature tests.
- **Browser (Pest):** happy-path flows; stable selectors; keep tests maintainable, not a duplicate of every CSS tweak.
- **Factories:** `Model::factory()->for($user)->create()`; add **factory states** for common variants.
- **Do not** delete tests silently—update assertions when behavior changes, or get explicit product sign-off to remove.

---

## When to add services, repositories, events, DTOs

| Pattern | When |
|--------|------|
| **Service class** | Several actions share non-trivial logic (pricing, tax, multi-provider billing). Injected into actions. |
| **Repository** | Swapping storage (Eloquent → external API) or a clear persistence boundary. **Not** a thin pass-through to `Model::` for every call. |
| **Domain events & listeners** | Decouple side effects (index search, webhooks) from the main transaction; be clear about **sync vs queued** and failures. |
| **DTOs / `laravel-data`** | Large payloads, nested validation, or shared shapes between API and CLI—when arrays become error-prone. |

Start simple: **Form request + Action + Model** is enough for most features.

---

## Security checklist

- **Input:** Validate everything; use `$request->validate()` or Form requests, never trust raw `$_GET` / `$_POST` for business decisions.
- **Output:** Escape in Blade with `{{ }}` unless you intentionally output HTML (then use reviewed partials and sanitization as needed).
- **CSRF:** `@csrf` on web forms; Sanctum/API clients use tokens as documented.
- **Authorization:** Every state-changing action checks policy or role **after** authentication.
- **Mass assignment:** Never assign request keys directly to models without a fillable/attributes strategy.
- **Secrets:** Only `config/` and `.env`; never commit API keys; use `config:cache` in production.
- **SQL:** Eloquent/parameterized queries; avoid raw `DB::` with string concatenation from user input.
- **Files:** Validate `image`, `mimes`, `max` size; store outside public or behind authorization as required.

---

## Migrations & data

- **Descriptive** migration class names; one logical change per migration when possible.
- **Down migrations** where your team policy requires them.
- **Indexes** for foreign keys and frequent filters; review on large tables.
- **Seeding:** development-only realistic data; **never** run destructive seeders in production without safeguards.

---

## Code style & tooling

- **Artisan:** `php artisan make:controller` / `make:model` / `--help` for flags; use `--no-interaction` in scripts/CI.
- **Pint** (if present): `vendor/bin/pint --dirty` after PHP changes to match the project.
- **Rector** (if present): use only with review and a narrow config.
- **Static analysis** (PHPStan, Larastan): follow project `phpstan.neon` level.

---

## Rules for AI assistants (Cursor, Claude, etc.)

1. **Version-specific behavior:** When unsure about APIs (`CurrentUser`, `bootstrap/app.php`, middleware), **read the installed Laravel version in `composer.lock` / docs search** (or your project’s `search-docs` MCP) before editing.
2. **Match the repo:** Same naming, folder layout, and strictness as sibling files; no new top-level `app` namespaces without team approval.
3. **No surprise dependencies:** Do not add Composer/NPM packages unless the user explicitly approves.
4. **Smallest change:** Fix or implement only what was asked; no drive-by refactors, no unrelated file churn.
5. **Tests:** Add or update tests for behavior changes; use factories; run the project’s test command when feasible.
6. **PHP style:** Curly braces for all control structures; explicit parameter and return types where the codebase does; constructor property promotion when consistent.
7. **Documentation files:** Add or change `docs/*` (and README) only when the user (or your team’s rule) asked for that documentation.
8. **Verification:** Prefer tests over one-off tinker scripts for “does it work?” in shared code.

---

## New feature checklist

- [ ] Route names, HTTP verbs, middleware, and (if needed) **scoped** model binding
- [ ] **Policy** method and registration
- [ ] **Form request** (or inline validation only for trivial cases)
- [ ] **Action** (or justified inline in controller for one-line updates) with `DB::transaction()` if multi-step
- [ ] **Model** fillable/casts/relations; enum for fixed states
- [ ] Eager loading for any list or nested view
- [ ] **Pest** test: happy path + at least one forbidden / validation failure
- [ ] If API: **Resource** + status codes + optional throttle

---

## Appendix: Cursor rule frontmatter (optional)

Create `.cursor/rules/laravel-architecture.mdc` in a new project with content like:

```yaml
---
description: Laravel architecture, layers, and AI guardrails (see docs/laravel-cursor-claude-rules.md)
globs: "**/*.{php,blade.php}"
alwaysApply: false
---
```

Then either duplicate the key sections of this file below the frontmatter, or keep a short pointer: “Follow `docs/laravel-cursor-claude-rules.md` for full detail.”

---

*Maintainers: when Laravel or team conventions change, update [§ Project baseline](#project-baseline) and the relevant sections; keep the table of contents accurate.*
