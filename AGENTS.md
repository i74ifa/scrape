# AGENTS.md

Talabye (طلبي) — Laravel 12 proxy-shopping / dropshipping platform. Scrapes foreign
stores, converts currencies, computes shipping by weight, and orchestrates multi-
platform orders. PHP 8.2+, React 19, Tailwind v4.

## Three independent frontends (do not cross-wire)

| Surface | Routes | Source | Build output |
|---|---|---|---|
| Customer web SPA | `routes/web.php` catch-all `/{any}` | `resources/js` (React Router, TanStack Query) | `public/build/theme` |
| Admin panel (Inertia) | `/admin`, `/central` | `themes/admin/src` (HeroUI v3, Inertia v3, i18next, framer-motion) | `public/build/admin` |
| Image classifier | — (HTTP daemon) | `model/` (`@tensorflow/tfjs-node`) | runs in place |

Each has its **own** `package.json`, `vite.config`, and React instance. The admin
vite config deliberately `dedupe: ["react","react-dom"]` — do not remove it or
HeroUI/react-aria hooks crash with a null-dispatcher error. Admin build uses
`hotFile: public/hot-admin` (distinct from the SPA's `public/hot`).

## Commands

```bash
composer dev          # concurrently: artisan serve + queue:listen + pail + vite (root SPA only)
npm run dev           # root SPA only — does NOT watch the admin panel
cd themes/admin && npm run dev   # admin panel (separate terminal)
npm run build:all     # builds root SPA + admin panel together

composer test         # runs `artisan config:clear` THEN `artisan test` — config cache is wiped
php artisan test --filter=CartTest          # single test file
php artisan test tests/Feature/CartTest.php # by path

vendor/bin/pint       # PHP formatter (Laravel Pint)
```

No `lint`/`typecheck` npm script exists. TS is checked implicitly by Vite during
`build`; run `npx tsc --noEmit -p tsconfig.json` if you need a standalone check.

## Testing

PHPUnit (not Pest, despite the composer plugin allow-list). `phpunit.xml` forces
SQLite in-memory, `QUEUE_CONNECTION=sync`, array caches. Suite is thin and mostly
Feature tests — many paths have no test coverage, so rely on `php -l` and manual
verification for controller/form-request changes.

### Every new feature MUST ship with a test

When implementing any feature, bug fix, or behavior change, **write a test for it
before considering the work done.** Default to a Feature test that exercises the
new behavior through the HTTP / service boundary the user actually hits.

- New controller endpoint / route → Feature test that asserts status, payload,
  and side effects (DB rows, jobs dispatched, etc.).
- New service / value object / helper → a focused test under `tests/Unit/` (or a
  Feature test that calls through it). Pure logic (parsing, money math, currency
  conversion) belongs in `tests/Unit/`.
- New admin CRUD resource → Feature test covering store + update + validation
  failures, mirroring the existing `tests/Feature/CartTest.php` style.
- New scraper / platform script → at minimum a `Scraper::toFloat()`-style unit
  test for the price parsing path; currency-mismatch must assert the throw of
  `CurrencyNotSameInScraperException`.
- Bug fix → add a regression test that fails before the fix and passes after.

Place tests alongside the existing ones (`tests/Feature/`, `tests/Unit/`), name
the class after the thing under test (e.g. `CurrencyConversionTest`,
`CatalogProductStoreTest`), and run `php artisan test --filter=NewTestName` to
confirm it passes before finishing. If a feature is genuinely untestable (pure
UI markup), say so explicitly and verify manually instead.

## Package manager gotcha

Root has **both** `bun.lock` and `pnpm-lock.yaml` committed. `composer setup`
and the scripts use plain **npm** — prefer `npm install` for the root and
`themes/admin`. `model/` uses `package-lock.json` (npm only). Do not mix managers
inside one package or lockfiles drift.

## Admin CRUD convention (the catalog pattern)

When adding/modifying anything under `app/Http/Controllers/Admin/` or admin pages,
follow the existing **Catalog** resource end-to-end:

- Controller: `app/Http/Controllers/Admin/Catalog/{Resource}Controller.php`
- Form requests: `app/Http/Requests/Catalog/{Store,Update}{Resource}Request.php`
- Inertia page: `themes/admin/src/Pages/Admin/Catalog/{Resources}/Index.jsx`
- Routes registered inside the `admin.catalog.` group in `routes/web.php`
- Pages default-export a component wrapped in `<AdminLayout title="…">` (Arabic)
- Table views are server-paginated + async-searched; lazy data (tree, pickers)
  loads over a separate `lookup` JSON endpoint to avoid N+1.

### Two admin layouts

- `AdminLayout` → tenant admin at `/admin` (platform orders, scraped products, catalog).
- `CentralAdminLayout` → SaaS central at `/central` (tenants, plans, subscriptions,
  reports). Served on a separate central domain passed via the `central_domain`
  Inertia prop. Central routes are not in `routes/web.php` — confirm where they
  mount before editing.

`inertia.panel` middleware alias (`app/Http/Middleware/HandleInertiaRequests.php`)
sets root view `panel.blade.php` and shares `auth.user`, `locale`, `csrf_token`,
and `flash.{success,error}`. Session flash keys are `success` / `error`.

### Multipart PUT gotcha (Inertia v3 + PHP)

`useForm().put()` with a `File` loses the file in PHP. The catalog pages spoof
the method: `form.transform(d => ({...d, _method:"put"}))` then `form.post(...)`.
Use the same pattern for any form containing a file upload.

## Scrapers (proxy-shopping core)

- `App\Modules\Scraper` assembles per-platform JS that is **injected into the
  external store's page** inside the app webview (add-to-cart, etc.).
- Per-platform scripts live in `resources/views/scrapers-scripts/platforms/*.blade.php`.
- Scaffold a new platform with `php artisan platform:generate {name}` — it prompts
  for domain + selectors from a stub.
- `Scraper::toFloat()` parses localized price strings; currency mismatch between
  detected symbol and the platform's configured currency throws
  `CurrencyNotSameInScraperException`. Don't silently swallow it.

## Currency model (do not break this)

- All stored monetary amounts are in **SAR** (the pivot/default currency).
- `App\Services\Currency::convert($amount)` returns a `Money` value object;
  the `money($amount)` global helper (in `app/helpers.php`) is the normal entrypoint.
- Active display currency is per-request (set via `Currency::setCurrency()` /
  `AppCustomizationMiddleware`); default user currency is `YER`.
- Hard-coded rates in `Currency::getExchangeRate()` for `SAR`/`YER`/`USD`;
  everything else reads `CurrencyExchangeRate` (rate column is units per USD).

## Image classifier (`model/`)

Teachable Machine model used to estimate parcel shipping weight from a product
image. `App\Services\ImageClassifier` prefers the persistent daemon
(`model/server.js`, default `127.0.0.1:8765` via `MODEL_CLASSIFIER_URL`) and falls
back to spawning `node model/cli.js` (pays ~700ms cold start per call).

- Start the daemon for local dev: `cd model && npm run serve`
- Label→grams map lives in `config/services.php` → `classifier.weights`; keys
  **must** match `model/metadata.json` labels exactly.
- Storefronts serve WebP, which tfjs-node can't decode — the service transcodes
  to JPEG via GD first. If classification silently fails, check `gd`/WebP support.

## Auth & API

- `routes/api.php` is Sanctum-guarded; default guard name is `sanctum`.
- `user()` helper returns `auth('sanctum')->user()`.
- Social login (Google/Apple/Telegram) and OTP phone login both live under `api/auth`.
- `bootstrap/app.php` enforces a JSON error envelope for **all** `api/*` requests
  even without an `Accept: application/json` header — don't add manual JSON checks
  in controllers.

## Octane / serving

Production runs Laravel Octane (Swoole in `Dockerfile`, port 1215; a `frankenphp`
binary is committed at repo root). Local dev uses plain `php artisan serve` via
`composer dev`. Treat request state as stateless in Octane — never stash anything
in static/service properties across requests.

## Other notes

- `_ide_helper.php` is committed on purpose (laravel-ide-helper); don't delete it.
- UI copy and admin labels are **Arabic, RTL**. Match surrounding strings; don't
  translate to English unless asked.
- Commit style: conventional `feat:`/`fix:` prefixes when present, but many
  commits are informal. Default branch is `main`; feature branches use
  `feature/*`.
