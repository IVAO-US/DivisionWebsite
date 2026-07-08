# Laravel 13 Migration Guide

**Migration**: Laravel 12.x → Laravel 13.x
**Performed on**: 2026-07-08 (framework v12.62.0 → v13.19.0)
**Scope**: This document is the complete reference for upgrading any repository based on this starter template (Laravel-modernStart) to Laravel 13. Every step below was executed and verified on this repository.

> **Template baseline**: the template targets **PHP ^8.4** (Laravel 13 itself only requires 8.3 — see [Step 7](#step-7--php-84-floor-spatie-majors-activitylog-5-sitemap-8) for what the 8.4 floor unlocks). If a derived app must stay on PHP 8.3, skip Step 7 and use the 8.3-compatible constraints noted there.

---

## 📋 Table of Contents

1. [Executive Summary](#executive-summary)
2. [Prerequisites](#prerequisites)
3. [Step 1 — composer.json Dependency Updates](#step-1--composerjson-dependency-updates)
4. [Step 2 — Run Composer Update](#step-2--run-composer-update)
5. [Step 3 — Configuration File Changes (Skeleton Sync)](#step-3--configuration-file-changes-skeleton-sync)
6. [Step 4 — bootstrap/app.php](#step-4--bootstrapappphp)
7. [Step 5 — Frontend: Vite 8, axios Removal, npm Hardening](#step-5--frontend-vite-8-axios-removal-npm-hardening)
8. [Step 6 — Framework Breaking Changes Audit](#step-6--framework-breaking-changes-audit)
9. [Step 7 — PHP 8.4 Floor: Spatie Majors (activitylog 5, sitemap 8)](#step-7--php-84-floor-spatie-majors-activitylog-5-sitemap-8)
10. [Skeleton Changes Deliberately NOT Adopted](#skeleton-changes-deliberately-not-adopted)
11. [Optional Follow-ups](#optional-follow-ups)
12. [Verification Checklist](#verification-checklist)
13. [Deployment Notes](#deployment-notes)
14. [Sources](#sources)

---

## Executive Summary

Laravel 13 (released 2026-03-17, PHP 8.3–8.5, bug fixes until Q3 2027, security fixes until 2028-03-17) is a low-friction upgrade for this template. The official upgrade guide estimates ~10 minutes for a stock app; for this template the work breaks down into:

| Area | Effort | Risk |
|------|--------|------|
| Composer constraints (framework, tinker, pest) | Mechanical | Low |
| Config sync (2 new security keys + comments) | Mechanical | Low — audited, no object caching/session storage in this template |
| Frontend (Vite 8, axios removal) | Mechanical | Low — axios was unused; bundle shrinks 51 kB → 5 kB |
| App code changes | **None required** | The template uses no deprecated/changed framework APIs |

**Key facts:**
- **PHP 8.3 minimum for Laravel 13** (was 8.2); **this template targets ^8.4** (Step 7). Check every target environment (Laragon, Plesk VPS, Steam Deck) *before* upgrading.
- **Pest 4 / PHPUnit 12 required** — Pest 3.x pins PHPUnit 11 and will block `composer update`.
- **All template packages already support Laravel 13** in their current major versions (no Spatie major-version jumps needed).
- **No route, migration, Blade, Livewire, or service-layer changes were needed.** All 51 routes, the dual-group multilingual routing, Livewire 4 SFC pages, 2FA, CAPTCHA, SEO and sitemap generation work unchanged.

---

## Prerequisites

1. **PHP >= 8.4** on every machine that runs the app (dev, CI, production) to follow this template's baseline — Laravel 13 itself supports PHP 8.3–8.5 (stay on the Step 1 "8.3-compatible" constraints if you must run 8.3).
2. A working Laravel 12 baseline: run `php artisan test`, `npm run build`, and smoke-test key pages **before** starting, so you can compare behavior afterwards.
3. Commit or stash all pending work — do the upgrade on a dedicated branch.

---

## Step 1 — composer.json Dependency Updates

### `require`

| Package | Before | After | Why |
|---------|--------|-------|-----|
| `php` | `^8.2` | `^8.4` | Laravel 13 minimum is ^8.3; the template floor is ^8.4 (see Step 7) |
| `laravel/framework` | `^12.0` | `^13.0` | The upgrade itself |
| `laravel/tinker` | `^2.10.1` | `^3.0` | **Major bump required** — Tinker 2.x does not support Laravel 13 |
| `livewire/livewire` | `^4.0@beta` | `^4.0` | Livewire 4 is stable and supports `illuminate/* ^13.0` since well before v4.3.3; the `@beta` flag is obsolete |
| `artesaos/seotools` | `^1.3` | `^1.4` | Laravel 13 support added in v1.4.0 (v1.3.x caps at `^12.0`) |
| `mews/captcha` | `^3.4` | `^3.5` | L13 support since 3.4.8; ^3.5 = clean floor |
| `outhebox/blade-flags` | `^1.5` | `^1.7` | **First L13-compatible release is 1.7.0** |
| `robsontenorio/mary` | `^2.0` | `^2.8` | L13 support since 2.8.1 (floor raised for clarity) |
| `spatie/laravel-activitylog` | `^4.10` | `^5.0` | Requires PHP ^8.4 and a package-level migration — see Step 7 (on PHP 8.3, use `^4.12` instead) |
| `spatie/laravel-permission` | `^6.23` | `^6.25` | L13 support since 6.25.0 — **stay on 6.x** (7.x/8.x are separate majors, not needed) |
| `spatie/laravel-sitemap` | `^7.3` | `^8.0` | Requires PHP ^8.4 — see Step 7 (on PHP 8.3, use `^7.4` instead) |
| `postare/blade-mdi` | `^1.1` | `^1.1` | No illuminate constraint — unchanged |

### `require-dev`

| Package | Before | After | Why |
|---------|--------|-------|-----|
| `pestphp/pest` | `^3.7` | `^4.1` | **Required** — Pest 3 pins PHPUnit 11; Laravel 13 test stack uses PHPUnit 12 (`^12.5.30` via Pest 4) |
| `pestphp/pest-plugin-laravel` | `^3.1` | `^4.1` | v4.1.0 is the first release declaring `laravel/framework ^13.0` |
| `laravel/pail` | `^1.2.2` | `^1.2.5` | First L13-compatible release (also the L13 skeleton floor) |
| `laravel/pint` | `^1.13` | `^1.27` | Framework-agnostic; matches the L13 skeleton floor |
| `laravel/sail` | `^1.41` | `^1.53` | First L13-compatible release. Note: the L13 skeleton no longer ships Sail — we keep it (see below) |
| `fakerphp/faker` | `^1.23` | `^1.23` | Unchanged |
| `laravel-lang/common` | `^6.7` | `^6.7` | Unchanged (6.8.0 resolves fine) |
| `mockery/mockery` | `^1.6` | `^1.6` | Unchanged |
| `nunomaduro/collision` | `^8.6` | `^8.6` | Unchanged — Laravel 13 keeps Collision 8.x |

### `scripts`

Add the Laravel 13 skeleton's `test` script (the `@no_additional_args` directive keeps pass-through args like `composer test -- --filter=Foo` away from `config:clear`):

```json
"test": [
    "@php artisan config:clear --ansi @no_additional_args",
    "@php artisan test"
]
```

> **Note**: `phpunit/phpunit` is not a direct dependency of this template — it arrives transitively via Pest 4, at ^12.5.30. Do not add it manually.

---

## Step 2 — Run Composer Update

```bash
composer update --with-all-dependencies
```

Expected resolution highlights (as locked on this repo):

```
laravel/framework  v13.19.0        pestphp/pest       v4.7.5
laravel/tinker     v3.0.2          phpunit/phpunit    12.5.30
livewire/livewire  v4.3.3          nesbot/carbon      3.13.0
artesaos/seotools  v1.4.1          spatie/laravel-permission 6.25.0
mews/captcha       3.5.0           spatie/laravel-activitylog 4.12.3
robsontenorio/mary 2.9.0           spatie/laravel-sitemap 7.4.0
symfony/polyfill-php85 (new transitive dependency — see Step 6)
```

Then verify boot immediately:

```bash
php artisan --version   # Laravel Framework 13.x
php artisan test        # existing suite must pass — Pest 4 needs NO changes
                        # to tests/Pest.php or the phpunit.xml of this template
```

> **phpunit.xml**: unchanged. The 12.x and 13.x skeleton files are byte-identical; PHPUnit 12 accepts the existing schema.
> **tests/**: unchanged. Pest 4 runs the existing scaffolding as-is.

---

## Step 3 — Configuration File Changes (Skeleton Sync)

Laravel never touches published config files during `composer update` — these must be hand-applied. Between the 12.x and 13.x skeletons, exactly **five** stock config files changed (`auth.php`, `filesystems.php`, `logging.php`\*, `mail.php`, `queue.php` are identical upstream).

### 3.1 `config/session.php` — NEW `serialization` key ⚠️ behavior-relevant

Append before the closing `];`:

```php
/*
|--------------------------------------------------------------------------
| Session Serialization
|--------------------------------------------------------------------------
|
| This value controls the serialization strategy for session data, which
| is JSON by default. Setting this to "php" allows the storage of PHP
| objects in the session but can make an application vulnerable to
| "gadget chain" serialization attacks if the APP_KEY is leaked.
|
| Supported: "json", "php"
|
*/

'serialization' => 'json',
```

**Audit before adopting `json`** (done for this template): grep for `session()->put(`, `Session::put(`, `->session()->put(` — if any call stores a PHP *object* (not scalars/arrays), either refactor it or set `'php'`. This template stores nothing beyond framework scalars (auth id, CSRF token, flash data, intended URL) → `json` is safe.

**Deploy impact**: sessions serialized under the old PHP format are invalidated once → all users are logged out one time. Plan the switch with a deploy that tolerates it.

### 3.2 `config/cache.php` — NEW `serializable_classes` key ⚠️ behavior-relevant

Three changes:

1. Update the supported-drivers docblock: `"array", "database", "file", "memcached", "redis", "dynamodb", "storage", "octane", "session", "failover", "null"`.
2. Add the new `storage` store (flysystem-disk-backed cache) after the `file` store:

```php
'storage' => [
    'driver' => 'storage',
    'disk' => env('CACHE_STORAGE_DISK'),
    'path' => env('CACHE_STORAGE_PATH', 'framework/cache/data'),
],
```

3. Append after `prefix`:

```php
/*
|--------------------------------------------------------------------------
| Serializable Classes
|--------------------------------------------------------------------------
|
| This value determines the classes that can be unserialized from cache
| storage. By default, no PHP classes will be unserialized from your
| cache to prevent gadget chain attacks if your APP_KEY is leaked.
|
*/

'serializable_classes' => false,
```

**Audit before adopting `false`** (done for this template): grep for `Cache::` / `cache()->` and check what each call stores.
- `App\Models\AppSetting` caches **scalars** → safe.
- `spatie/laravel-permission` caches **arrays** → safe (verified by test: role/permission checks work with the hardened cache).
- If a derived app caches PHP objects (models, DTOs, Carbon instances), set an allowlist instead: `'serializable_classes' => [App\Data\SomeDto::class, ...]`.

### 3.3 `config/logging.php` — Slack username fallback

```php
// Before
'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
// After
'username' => env('LOG_SLACK_USERNAME', env('APP_NAME', 'Laravel')),
```

(\* This change landed in late 12.x skeletons; older-published configs like ours still had the old value.)

### 3.4 `config/app.php` — comment only

Maintenance-mode docblock now reads `Supported drivers: "file", "cache", "array"` (a new `array` driver exists in L13).

### 3.5 `config/services.php` — comment only

`"…such as Mailgun, Postmark, AWS and more"` → `"…such as Resend, Postmark, AWS, and more"`.

### Files that need NO changes

`config/auth.php`, `config/filesystems.php`, `config/mail.php`, `config/queue.php`, `.env.example`, `phpunit.xml`, `artisan`, `public/index.php`, `bootstrap/providers.php`, `routes/*`, all migrations, and every package/custom config (`captcha.php`, `livewire.php` — **keep `csp_safe => false`** —, `locales.php`, `seotools.php`, `permission.php`, `activitylog.php`).

---

## Step 4 — bootstrap/app.php

One additive change (Laravel 13 skeleton default, from skeleton v13.8.0): render exceptions as JSON for API routes. Add at the **top** of the `withExceptions()` closure:

```php
// Render exceptions as JSON for API routes (Laravel 13 skeleton default)
$exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());
```

(`Illuminate\Http\Request` is already imported in this template.)

This is harmless for the template (no `api/*` routes exist) but gives derived apps the correct L13 default when they add an API. The three custom exception renderers (Spatie 403→404, auth logging, access-denied 403→404), the `withSchedule()` closures, and the middleware aliases all work **unchanged** on Laravel 13.

> Verified: `php artisan schedule:list` still shows all 3 scheduled tasks — the L13 change that defers `withScheduling()` registration until the `Schedule` is resolved does not affect this template.

---

## Step 5 — Frontend: Vite 8, axios Removal, npm Hardening

### 5.1 `package.json`

| Package | Before | After | Why |
|---------|--------|-------|-----|
| `vite` | `^6.0.11` | `^8.0.0` | L13 skeleton default (via skeleton v13.1.0). No config changes needed |
| `laravel-vite-plugin` | `^1.2.0` | `^3.1` | Major bump paired with Vite 8 |
| `concurrently` | `^9.0.1` | `^10.0.3` | Skeleton parity (used by `composer dev`) |
| `axios` | `^1.8.2` | **removed** | Removed from the L13 skeleton (v13.2.0). **Audited first**: nothing in this template uses `window.axios` or imports axios — Livewire uses its own fetch-based transport |
| `@tailwindcss/vite`, `tailwindcss`, `daisyui`, `tailwind-scrollbar` | unchanged | unchanged | Tailwind 4 works as-is with Vite 8 |

Then:

```bash
rm package-lock.json && npm install && npm run build
```

### 5.2 Remove the axios bootstrap

- Delete `resources/js/bootstrap.js` (its only content was the `window.axios` setup).
- Remove `import './bootstrap';` from `resources/js/app.js`.

**Measured result**: JS bundle **51.31 kB → 5.00 kB** (gzip 19.46 kB → 1.78 kB). Straight page-load performance win, zero functional change.

> **For derived apps**: grep `resources/` for `axios` and `window.axios` first. If you actually use axios, keep the dependency and the bootstrap file — the skeleton change is a default, not an obligation.

### 5.3 `.npmrc` (new file, skeleton v13.2.0 + v13.3.0)

```ini
ignore-scripts=true
audit=true
```

- `ignore-scripts=true` blocks npm lifecycle scripts at install time (supply-chain hardening). All of this template's dependencies install and build correctly with it.
- `audit=true` runs a security audit on every install.
- `npm run dev` / `npm run build` are unaffected (explicit `npm run` is never blocked).

### 5.4 `vite.config.js` — unchanged

The custom `server.hmr` (HTTPS HMR) and `build` blocks work as-is on Vite 8 + laravel-vite-plugin 3.

### 5.5 `resources/css/app.css` — deliberate skeleton deviation ⚠️

Skeleton v13.8.0 removed these two `@source` lines claiming Tailwind auto-detection covers them:

```css
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
```

**Do NOT remove them in this template.** Tailwind 4 auto-detection skips gitignored paths (`vendor/`, `storage/`), and the admin pages (`users`, `roles`, `logs`) use MaryUI tables with `with-pagination`, which render the framework's Tailwind pagination views from `vendor/`. Removing the lines strips ~17 kB of CSS that includes live pagination classes and visually breaks the paginators. A comment now marks this in `app.css`.

### 5.6 `.gitignore`

Added `/.codex` and `/.cursor/` (skeleton v13.2.0/v13.3.0). `.fleet` kept (upstream removed it; harmless either way).

---

## Step 6 — Framework Breaking Changes Audit

Full list from the official upgrade guide, with this template's audit result. **None required a code change here**; derived apps must re-check the items marked 🔍.

| Change (impact) | Audit for this template | Derived apps |
|---|---|---|
| **CSRF middleware renamed** `VerifyCsrfToken` → `PreventRequestForgery`, adds `Sec-Fetch-Site` origin checks (High) | No references to the old class names anywhere (old names remain as deprecated aliases). Livewire POSTs are same-origin → pass the new check. Verified via full Livewire login-flow test | 🔍 grep for `VerifyCsrfToken`/`ValidateCsrfToken` in `bootstrap/app.php`, route definitions, and `withoutMiddleware()` in tests |
| **Cache `serializable_classes`** (Medium) | Adopted `false` — only scalars/arrays cached (AppSetting, spatie/permission). Verified by test | 🔍 audit every `Cache::put/remember` for object payloads; use an allowlist if needed |
| **Session JSON serialization** (Medium, skeleton) | Adopted `json` — no objects in session. Verified login flow + encrypted DB sessions | 🔍 audit `session()->put()` calls; expect one-time session invalidation on deploy |
| **`upsert()` requires non-empty `uniqueBy`** (Medium) | No `upsert()` calls | 🔍 grep `upsert(` |
| Cache prefix / session cookie fallback defaults now hyphenated (Low) | **Not affected** — the published `config/session.php`/`cache.php` keep the underscore formulas in-file, so nothing changes (verified: cookie is still `laravel_session`) | Only affects apps *without* published config values; set `SESSION_COOKIE`/`CACHE_PREFIX` explicitly if relying on framework fallbacks |
| `symfony/polyfill-php85` defines global `array_first()`/`array_last()` on PHP < 8.5 (Low) | `app/Helpers/helpers.php` defines `lroute()`, `current_locale()`, `locale_url()` — no collision | 🔍 grep for global `array_first`/`array_last` helpers (incl. `laravel/helpers`) |
| MySQL DELETE with JOIN + ORDER BY/LIMIT now compiled → may throw (Low) | No such queries | 🔍 audit joined deletes |
| `JobAttempted::$exceptionOccurred` → `$exception`; `QueueBusy::$connection` → `$connectionName` (Low) | No queue event listeners | 🔍 grep listeners |
| Domain routes now match before non-domain routes (Low) | No domain routes (locale prefix routing unaffected) | Review if mixing domain/non-domain routes |
| `Container::call` respects nullable class param defaults (Low) | No such pattern | Review manual `app()->call()` usage |
| Manager `extend()` closures bound to manager instance (Low) | No manager extensions | Review custom driver registrations |
| Str test factories reset between tests; `Js::from` uses `JSON_UNESCAPED_UNICODE`; pagination views renamed to `pagination::bootstrap-3`; polymorphic pivot names pluralized; model instantiation during `boot()` throws; collection serialization restores relations; queued notifications honor `DeleteWhenMissingModels`; default password-reset mail subject now "Reset your password"; contract additions (`Cache Store::touch`, `Bus::dispatchAfterResponse`, `ResponseFactory::eventStream`, `MustVerifyEmail::markEmailAsUnverified`, queue size methods) (Very Low) | All audited — not applicable (framework traits used everywhere, no custom contract implementations, Tailwind pagination, laravel-lang provides mail translations) | Mostly relevant only to custom framework-contract implementations |
| `withScheduling()` registration deferred (Very Low) | Verified: `schedule:list` shows all 3 tasks | Verify `schedule:list` after upgrade |

---

## Step 7 — PHP 8.4 Floor: Spatie Majors (activitylog 5, sitemap 8)

Applied because all target environments run PHP 8.4/8.5. **Skip this step on PHP 8.3** and use the 8.3-compatible constraints from Step 1 (`activitylog ^4.12`, `sitemap ^7.4`, keep `PDO::MYSQL_ATTR_SSL_CA`).

### 7.1 composer.json

```json
"php": "^8.4",
"spatie/laravel-activitylog": "^5.0",
"spatie/laravel-sitemap": "^8.0",
```

Then `composer update spatie/laravel-activitylog spatie/laravel-sitemap --with-all-dependencies` (resolved here: activitylog 5.0.0, sitemap 8.2.0, spatie/crawler 9.3.2; browsershot/dom-crawler are no longer pulled in — a lighter tree).

### 7.2 spatie/laravel-sitemap 7.x → 8.x — drop-in for this template

All v8 breaking changes concern the **crawler** (`shouldCrawl`/`hasCrawled` callback signatures, `Observer` class removed, `CrawlProfile` now an interface, redirects followed by default). This template's `SitemapService` builds the sitemap manually with `Sitemap::create()` / `Url::create()` / `setChangeFrequency()` / `setPriority()` — **zero code changes needed**. Verified: generated `sitemap.xml` byte-identical (1071 B).
New v8 goodies available if needed: `maxTagsPerSitemap()` (auto-splitting), `setStylesheet()`, `sort()`.

### 7.3 spatie/laravel-activitylog 4.x → 5.0 — real migration work

Upstream v5 changes (see the package's UPGRADING.md) and what they required **in this template**:

| v5 change | Action taken here |
|---|---|
| PHP 8.4+ / Laravel 12+ | OK (floor raised) |
| Batch system removed (`LogBatch`) | Removed a dead `use Spatie\Activitylog\Facades\LogBatch;` import in `ActivityLogService` (was never called) |
| Schema: new `attribute_changes` JSON column; `batch_uuid` dropped | New migration `2026_07_08_120000_upgrade_activity_log_table_to_v5.php` (adds the column, drops `batch_uuid` — two separate `Schema::table` calls for SQLite compatibility). Historical migrations untouched |
| Config rewritten: `enabled` env → `ACTIVITYLOG_ENABLED`, `delete_records_older_than_days` → `clean_after_days`, `subject_returns_soft_deleted_models` → `include_soft_deleted_subjects`, new `default_except_attributes` / `buffer` / `actions` keys, `table_name`/`database_connection` keys removed | `config/activitylog.php` replaced with the v5 file, **plus** the two legacy keys `table_name`/`database_connection` re-appended — the template's historical migrations read them (`config('activitylog.table_name')`) and must keep working. Do not remove them |
| API renames: `$activity->changes()` → `attribute_changes`, `getExtraProperty()` → `getProperty()`, model relations `$model->activities`/`->actions` → `activitiesAsSubject`/`activitiesAsCauser` | Audited — this template uses none of the renamed APIs. The fluent chain (`activity()->event()->causedBy()->performedOn()->withProperties()->log()`), `Activity::query()->with('causer')`, and JSON property search all work unchanged |
| `properties` now holds only custom `withProperties()` data; tracked model changes move to `attribute_changes` | No impact — the template only logs custom properties (no `LogsActivity` trait on any model), so nothing to transfer |

> **Derived apps with production data**: if (and only if) you used the `LogsActivity` trait in v4, your tracked changes live under `properties->attributes` / `properties->old`. After adding the `attribute_changes` column, copy them over before relying on the new accessors (one-time UPDATE; see the package UPGRADING.md for the query). Apps that only used `withProperties()` — like this template — have **nothing to migrate**: `properties` keeps its data as-is.

> **GDPR cleanup**: the scheduled purge in `bootstrap/app.php` (`Activity::where('created_at', ...)->delete()`) works unchanged. Alternatively you can now schedule the package's own `activitylog:clean` command, which honors `clean_after_days`.

Verified by test: activity write via the app's exact fluent chains, admin-logs-page query (`with('causer')` + `whereJsonContains('properties->ip_address')`), scheduled cleanup query, and the new schema (`attribute_changes` present, `batch_uuid` gone).

### 7.4 config/database.php — Laravel 13 skeleton form

With PHP 8.4+ guaranteed, the native `Pdo\Mysql` class always exists, so the skeleton form was adopted in both the `mysql` and `mariadb` connections:

```php
use Pdo\Mysql;
// ...
'options' => extension_loaded('pdo_mysql') ? array_filter([
    Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
]) : [],
```

---

## Skeleton Changes Deliberately NOT Adopted

Documented so future skeleton syncs don't "fix" them by accident:

1. **`resources/css/app.css` `@source` lines kept** — see [Step 5.5](#55-resourcescssappcss--deliberate-skeleton-deviation-️). Required by MaryUI pagination.
2. **`laravel/sail` kept in require-dev** — the L13 skeleton dropped it, but this template documents Sail as an optional dev environment.
3. **`laravel/pao` not added** — new dev-tool dependency in skeleton v13.4.0; not needed by this template's workflow. Add it later if desired: `composer require --dev laravel/pao`.
4. **Bunny/Vite font plugin not adopted** (skeleton v13.5.0) — the template loads the Inter font via its own CSS `@import`; the skeleton's `@fonts` directive targets the default Instrument Sans setup.
5. **User model kept `$fillable`/`$hidden` properties** — the L13 skeleton uses the new `#[Fillable]`/`#[Hidden]` PHP attributes. Both styles are fully supported; adopting the attributes is cosmetic (see Optional Follow-ups).
6. **axios removal**: adopted here because it was unused — **not** a blind rule for derived apps (see Step 5.2).

---

## Optional Follow-ups

Safe to do any time after the upgrade; none affect behavior today:

1. **Eloquent attribute syntax** — migrate `protected $fillable`/`$hidden` on `User` (and `$casts` on `TwoFactorCode`) to `#[Fillable]` / `#[Hidden]` attributes / `casts()` method for skeleton parity.
2. **DB alignment migrations** (new installs already match nothing here — these tables come from *your own* migration history):
   - cache: `expiration` `integer` → `bigInteger` on `cache` + `cache_locks` (skeleton 13.x; avoids Y2038 overflow),
   - jobs: `attempts` `unsignedTinyInteger` → `unsignedSmallInteger`; `failed_jobs.connection/queue` `text` → `string` + composite index `['connection', 'queue', 'failed_at']`.
   Create *new* migrations if you want parity — never edit already-run migrations.
3. **New Laravel 13 features to leverage** in derived apps: `Cache::touch()`, `Queue::route()` per-class queue routing, `#[Middleware]`/`#[Authorize]` route attributes, JSON:API resources, the Laravel AI SDK, vector/semantic search (pgvector), the new `storage` and `session` cache drivers.
4. **CI matrices**: test on PHP 8.3/8.4/8.5 (drop 8.2).
5. **Laravel Boost** — `composer require laravel/boost --dev` provides a first-party `/upgrade-laravel-v13` AI-assisted flow for the *next* framework major.

---

## Verification Checklist

Run all of these after the upgrade (all pass on this repository):

```bash
# 1. Framework + dependencies
php artisan --version                 # Laravel Framework 13.x
composer validate                     # composer.json is valid

# 2. Test suite (Pest 4 / PHPUnit 12)
php artisan test                      # all tests pass, no scaffolding changes

# 3. Boot, routes, schedule
php artisan about
php artisan route:list                # expected route count (51 on the template)
php artisan schedule:list             # 3 tasks: sitemap daily, 2FA hourly, activity-log daily@02:00

# 4. Production caches (must not error — proves route/config are cacheable)
php artisan optimize                  # config / events / routes / views / blade-icons
php artisan optimize:clear

# 5. Frontend
npm run build                         # Vite 8 build succeeds

# 6. Code style
./vendor/bin/pint --test --dirty

# 7. HTTP smoke test (compare against your pre-upgrade baseline)
php artisan serve &
curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/                # 200 (fr)
curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/en              # 200 (en)
curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/users/login     # 200
curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/sitemap.xml     # 200
curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/robots.txt      # 200
curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/laravel-health  # 200
```

Functional checks performed on this repo (recommended for derived apps):

- ✅ Livewire login flow authenticates end-to-end (`Livewire::test('pages::users.login')`) — covers CSRF/PreventRequestForgery + Livewire 4 on L13
- ✅ Spatie role/permission checks pass with `serializable_classes => false`
- ✅ Cache roundtrip (scalars + arrays) with the hardened config
- ✅ Registration toggle (AppSetting) still gates `/users/register`
- ✅ Locale detection: FR default, `/en/...` prefix, `Accept-Language` negotiation, `lroute()` helper
- ✅ SitemapService generates the same sitemap.xml
- ✅ Browser check (Chromium): pages render pixel-correct, `window.Livewire` + `window.Alpine` initialize, zero console errors, theme toggle works
- ✅ HTTP responses byte-identical to the Laravel 12 baseline (status codes, sizes, titles, `laravel_session` cookie name)

---

## Deployment Notes

1. **PHP version first**: switch the server to PHP >= 8.4 *before* deploying the upgraded code (Plesk: PHP handler setting; Laragon: PHP version switch).
2. **One-time session invalidation, no durable data loss**: the switch to JSON session serialization makes *pre-upgrade* sessions unreadable → all users are logged out once and re-authenticate; account data is untouched (sessions are ephemeral state). Likewise `serializable_classes => false` only affects the cache: any old entry holding a serialized PHP object simply becomes a cache miss and is regenerated (this template caches only scalars/arrays anyway). Run `php artisan cache:clear` as part of the deploy to start from a clean cache. Deploy in a low-traffic window if the logout matters.
3. `atomic-deploy.sh` needs no changes — `composer install`, `npm ci && npm run build`, `migrate --force`, and the cache rebuild steps all behave the same on Laravel 13.
4. `ext-bcmath` remains required (transitively via `laravel-lang`) — already true on Laravel 12; ensure the PHP 8.4+ runtime has it enabled.
5. **activitylog v5 schema migration**: `php artisan migrate --force` (already in the deploy script) applies the `attribute_changes`/`batch_uuid` migration. For apps that used the `LogsActivity` trait in v4, do the properties→attribute_changes data transfer (Step 7.3) right after.
6. Environment variables: `ACTIVITY_LOGGER_ENABLED` is renamed `ACTIVITYLOG_ENABLED` (activitylog v5) — update `.env` if you had set it. Optional new ones: `CACHE_STORAGE_DISK` / `CACHE_STORAGE_PATH` (only if you use the new `storage` cache store), `ACTIVITYLOG_BUFFER_ENABLED`.

---

## Sources

- Release notes read individually: [v13.0.0](https://github.com/laravel/laravel/releases/tag/v13.0.0) · [v13.1.0](https://github.com/laravel/laravel/releases/tag/v13.1.0) · [v13.2.0](https://github.com/laravel/laravel/releases/tag/v13.2.0) · [v13.3.0](https://github.com/laravel/laravel/releases/tag/v13.3.0) · [v13.4.0](https://github.com/laravel/laravel/releases/tag/v13.4.0) · [v13.5.0](https://github.com/laravel/laravel/releases/tag/v13.5.0) · [v13.6.0](https://github.com/laravel/laravel/releases/tag/v13.6.0) · [v13.7.0](https://github.com/laravel/laravel/releases/tag/v13.7.0) · [v13.8.0](https://github.com/laravel/laravel/releases/tag/v13.8.0)
- [Official Laravel 13 Upgrade Guide](https://laravel.com/docs/13.x/upgrade) — the authoritative breaking-changes list
- [Laravel 13 Release Notes](https://laravel.com/docs/13.x/releases) — new features + support policy
- [laravel/laravel 12.x ↔ 13.x skeleton comparison](https://github.com/laravel/laravel/compare/12.x...13.x) — config/file drift
- Packagist metadata (`repo.packagist.org/p2/*.json`) — per-package Laravel 13 compatibility floors

---

**Migration executed and verified on**: 2026-07-08
**Framework**: v12.62.0 → v13.19.0 · **PHP floor**: ^8.2 → ^8.4 · **Spatie**: activitylog 5.0, sitemap 8.2 · **Result**: zero functional regressions, JS bundle −90%, hardened session/cache defaults
