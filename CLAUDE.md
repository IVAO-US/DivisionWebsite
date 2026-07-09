# CLAUDE.md

Guidance for Claude Code (and other AI agents) working in this repository.

## Project overview

**IVAO United States Division Website** — the public-facing site and admin panel for
the IVAO US division (virtual aviation network). It is a **Laravel 13** application
using **Livewire 4** single-file component (SFC) pages, **MaryUI** (daisyUI/Tailwind 4)
for the UI, and **IVAO OpenID/OAuth** for authentication. There is no starter kit —
auth, routing, breadcrumbs, and the admin permission system are all hand-rolled.

Framework was migrated 12 → 13; see `LARAVEL13_MIGRATION.md` for the full record and
the deliberate skeleton deviations (do not "fix" them blindly — read the doc first).

## Tech stack

| Layer | Choice |
|-------|--------|
| PHP | ^8.4 |
| Framework | Laravel ^13.0 |
| UI runtime | Livewire ^4.0 (native SFC page routing — the old Volt package is merged into core) |
| Components | robsontenorio/mary (MaryUI) ^2.9 |
| CSS | Tailwind 4 + daisyUI 5 (two custom themes: `ivao`, `ivao-dark`) |
| Icons | codeat3/blade-phosphor-icons (`phosphor.*`), blade-heroicons |
| Build | Vite 8 + laravel-vite-plugin 3 (`@tailwindcss/vite`) |
| SEO | artesaos/seotools + spatie/laravel-sitemap 8 |
| Tests | Pest 4 / PHPUnit 12 |
| Repl/dev | tinker 3, pail, pint, sail |

## How to run things

All PHP tooling assumes `COMPOSER_ALLOW_SUPERUSER=1` when run as root.

```bash
# Install
composer install
npm install                 # .npmrc sets ignore-scripts=true (supply-chain hardening); npm run is unaffected

# Dev (server + queue + logs + vite, all at once)
composer dev

# Build assets
npm run build               # Vite 8; outputs to public/build

# Tests
php artisan test            # or: composer test  (clears config first)

# Lint / style
./vendor/bin/pint           # PSR-12-ish; note: bootstrap/app.php & config/database.php
                            # have pre-existing style nits — leave unrelated code alone

# Useful checks
php artisan route:list      # ~36 routes
php artisan schedule:list   # 2 tasks: sitemap daily, division_sessions:sync every 15 min
php artisan optimize        # verify config/routes/views are cacheable
```

### Local database

Production runs **MariaDB**; `.env.example` defaults to it (`DB_CONNECTION=mariadb`).
For a quick local/CI run without MariaDB, point `.env` at SQLite:

```bash
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
# set DB_DATABASE to an absolute path to database/database.sqlite, then:
touch database/database.sqlite && php artisan migrate --force
```

`tests/Pest.php` has `RefreshDatabase` **disabled**, so tests run against whatever DB
the connection points to and expect the tables to already exist (the homepage test hits
the DB via `HeadlineService`/`AppSetting`). Migrate before testing.

## Architecture

### Routing (`routes/web.php` — the only route file)

- Pages are wired with **`Route::livewire('/path', 'pages::namespace.name')`**. The
  `pages::` namespace maps to `resources/views/pages/` (see `config/livewire.php`).
- **Breadcrumb conventions are load-bearing** — the header comment in `web.php` documents
  them and `BreadcrumbsServiceProvider` + `BreadcrumbsTrait` depend on them:
  - the index route must be named `home`;
  - a sub-URL root and its name share a base (`/training` → `name('training')`);
  - category roots without a page use `Route::redirect(...)->name(...)`;
  - single-argument pages put the arg last; multi-arg pages use query strings.
- Auth is IVAO OAuth: `/login` redirects into `IvaoController@handleCallback`
  (`/auth/ivao/callback`). Requires `IVAO_CLIENT_ID`/`IVAO_CLIENT_SECRET`/`OPENID_URL`.
- Middleware groups: `throttle`, `auth`, then `admin`, then `admin.permissions:<perm>`.

### Livewire SFC pages (`resources/views/pages/**`)

Each page is a single `.blade.php` file with an inline component class:

```php
new #[Layout('layouts.homepage')] class extends Component {
    use Toast, HasSEO;   // MaryUI toasts + custom SEO trait
    public function mount(): void { $this->setSEOWithBreadcrumbs(...); }
}
```

Layouts live in `resources/views/components/layouts/` (`layouts::app` is the default,
set in `config/livewire.php`). Reusable Blade components are in `resources/views/components/`.

### Admin permission system (custom, not spatie/permission)

- `App\Enums\AdminPermission` — hierarchical enum: `*` (super admin) > global category
  (e.g. `app`) > granular (`app_headline`). See the class docblock for the full model.
- `App\Models\Admin` stores admin records keyed by IVAO VID; `Admin::isAdmin($vid)`.
- Middleware `CheckAdmin` (`admin`) gates the admin area; `CheckAdminPermission`
  (`admin.permissions:<perm>`) gates individual admin pages. Both flash a
  `session_toast` array and redirect on failure.

### Two databases

- **Default** (`sqlite`/`mariadb`): app data — users, admins, division sessions, tours,
  virtual airlines, app settings, GDPR logs.
- **`awards_db`** (read-only secondary MySQL, `AWARDS_DB_*` env): the `division_sessions:sync`
  command (`SyncDivisionSessions`) pulls session data from its `cms_logs` table into the
  local `division_sessions` table every 15 minutes.

### Services (`app/Services/`)

- `HeadlineService` — decides the site "headline" banner by priority (active division
  session → online day → SpecOps online day → MOTD). Cached 60s under `current_headline`.
  Returns **plain scalar arrays only** (important for the hardened cache — see below).
- `SitemapService` — builds `sitemap.xml` manually via spatie `Sitemap::create()` /
  `Url::create()`. Served live at `/sitemap.xml` and regenerated daily to `public/`.
  `public/sitemap.xml` is a generated artifact — do not commit a dev copy.
- `SeoService`, `RecurringEventService`, plus traits `HasSEO`, `BreadcrumbsTrait`.

### Scheduled tasks & middleware (`bootstrap/app.php`)

- Schedule: regenerate sitemap daily; `division_sessions:sync --forever` every 15 min
  (`withoutOverlapping`, `onOneServer`, `runInBackground`).
- Global middleware: `App\Http\Middleware\SecurityHeaders` (appended to the web stack).
- Health endpoint at `/laravel-health`.

## Conventions & gotchas

- **Security-hardened config defaults (Laravel 13):**
  `config/session.php` → `serialization => 'json'` and `config/cache.php` →
  `serializable_classes => false`. This app only stores **scalars/scalar-arrays** in the
  session and cache, so both are safe. If you cache or session-store a PHP **object**
  (model, Carbon, DTO), you must either refactor to scalars or add an allowlist —
  otherwise it will silently fail to unserialize.
- **`resources/css/app.css` keeps two `@source` lines** for `vendor/.../Pagination` and
  `storage/framework/views` (Tailwind auto-detection skips gitignored paths). Removing
  them breaks MaryUI table paginators. This is intentional — see `LARAVEL13_MIGRATION.md` §5.5.
- **daisyUI themes** are defined inline in `app.css`. If you rename a theme, also update
  `resources/js/theme-store.js` and `resources/views/partials/theme-init-script.blade.php`.
- **Icons** use the `phosphor.*` prefix (blade-phosphor-icons), e.g. `phosphor.shield-warning`.
- No `api/*` routes exist yet; `bootstrap/app.php` still registers the L13 JSON-exception
  default so any future API renders JSON errors.
- `atomic-deploy.sh` is the production deploy (composer install --no-dev, `npm ci`,
  `npm run build`, `artisan down`, `migrate --force`, cache warm, `artisan up`). It needs
  PHP ≥ 8.4 and MariaDB on the target.

## When changing dependencies

- The app targets PHP ^8.4; spatie sitemap 8 and the `Pdo\Mysql` SSL constant in
  `config/database.php` rely on that floor.
- Livewire is stable `^4.0` (no longer `@beta`). MaryUI 2.9 changed the tabs API and
  daisyUI 5.6 changed tab heights — `app.css` has explicit overrides for both; keep them.
- Run the full verification list in `LARAVEL13_MIGRATION.md` (Verification Checklist)
  after any framework/dependency bump.
