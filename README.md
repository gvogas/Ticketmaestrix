# TicketMaestrix

TicketMaestrix is a ticket-purchasing web application for events such as concerts, raffles, comedy shows, festivals, and movies. Attendees can browse, search, and filter upcoming events, manage a session-backed cart, pay with Stripe, and earn or spend loyalty points. Administrators manage events, venues, categories, ticket inventory, users, and orders from a single dashboard.

The app is built on **Slim 4** (PHP 8.4), **Twig 3** templates, **RedBeanPHP 5.7** as a thin ORM, and **PHP-DI 7** for container wiring. Authentication includes a TOTP second factor and a remember-me cookie; payments go through Stripe Checkout with order creation driven by the webhook.

## Features

### Browsing and discovery

- Public event grid on the home page with featured "On Sale" row and full upcoming-events grid
- Map view at `/map` with Google Maps markers, a sidebar event list with a **pinned header** (title + search box + "Use my location" stay in place while cards scroll), and infinite-scroll batching (20 events per page, more load as the user scrolls — duplicate-page guard prevents double-fetches under fast scroll)
- "All on sale" listing at `/events/on-sale` with the same `?q=` / `?category=` / `?venue=` filter parity as `/events` (debounced AJAX, URL updates via `history.replaceState`)
- Browse-by-category at `/events/category/{id}`
- Filter and search on `/events` by query string, category, and venue
- Live AJAX search dropdown at `/api/search` (used by the home page navbar and `/events` filter)
- Numbered pagination (`Prev / 1 2 3 / Next`) on every list page — 30 items per page — backed by a shared partial at `templates/partials/pagination.html.twig`
- Top-of-page progress bar plus an inline spinner on the clicked link as soon as any pagination link is activated

### Cart and checkout

- All prices are denominated in **Canadian dollars (CAD)** — the Stripe Checkout session, line items, and coupons all use `currency: cad`
- Session-backed cart (`$_SESSION['cart']`) with a 5-minute expiry timer surfaced in the navbar; the cart auto-extends to 30 minutes before the redirect to Stripe so it survives the off-site payment step
- 1 point = CAD $0.01 discount, capped at the subtotal so points can zero the bill but never go negative
- Users earn 20 points per CAD $1 of pre-tax, pre-discount subtotal
- 15 % service fee applied to the *post-discount* subtotal — applying points also reduces the tax. The rate lives in one place as `Cart::SERVICE_FEE_RATE` and is consumed by both `HomeController::showCart` and `CartController` so the cart, checkout, and Stripe Session always agree
- Pricing formula: `discount = points × $0.01` → `taxable = max(0, subtotal − discount)` → `service_fee = round(taxable × 0.15, 2)` → `total = taxable + service_fee`
- Sub-CAD-$0.50 orders skip Stripe and complete server-side via `CartController::createOrderDirectly` (Stripe rejects sub-$0.50 charges)
- Stripe Checkout integration; the points discount rides on a one-shot Stripe `Coupon` with `amount_off` in CAD cents, line items stay positive (Stripe rejects negative `unit_amount`)
- Stripe webhook (`/stripe/webhook`) creates the `orders` + `order_items` rows, marks tickets sold, and updates the user's point balance — all inside one transaction with an idempotency check keyed off `stripe_session_id`

### Accounts

- Email + password signup with mandatory TOTP enrolment — the secret is captured during signup, the QR code is rendered on the `/2fa/setup` page, and the account isn't created until a valid 6-digit code is verified. A `/2fa/setup` replay or stale session redirects to `/login` instead of completing the signup
- TOTP-based two-factor authentication (`robthree/twofactorauth`); the secret is stored on the user record
- Per-device "trust this browser" cookie that skips 2FA for 30 days on the same browser + account, persisted in the `tfatoken` table
- Remember-me cookie (`auth_token`, 2-hour expiry) keeps users logged in across browser restarts inside the window; each login on each device gets its own SHA-256 token row in `authtoken`, so logging in on a new device never invalidates older devices, and logging out only revokes the current device's token
- Login rate limiting (5 failed attempts → 15-minute lockout, tracked in the session)
- Self-service profile editing, avatar upload (JPG / PNG / GIF / WebP, ≤ 5 MB, validated with `getimagesize`, random-hex filename so two uploads in the same second don't collide, old avatar only deleted after the DB update succeeds), and account deletion
- Loyalty point history visible on `/profile` alongside paginated purchase history (orders + their line items collapsed in a single SQL pass to avoid N+1 hydration)

### Admin

- Single dashboard at `/admin` with site-wide stats (revenue, tickets sold, active events, customer count) and two independently-paginated tabs
- Full CRUD for categories, venues, events, tickets, users, orders, and order items
- Role toggle (admin ↔ user) per account
- Server-side geocoding on venue create/update: the Google Geocoding API runs once at save time and the lat/lng pair is cached back to the `venue` row so the map page never blocks on outbound calls

### Operations

- Maintenance mode: `var/maintenance.flag` switches every route to a 503 with a styled "Down for Maintenance" page; delete the file to restore
- Per-request access log appended to `var/app.log` (method, path, status, elapsed ms)
- Security headers middleware (`X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `X-XSS-Protection: 1; mode=block`)
- Uploads directory hardened: an `.htaccess` denies execution of any `.php*` file under `uploads/avatars/`
- CSRF protection on every state-changing route via `CsrfMiddleware`: one random token per session, every POST form embeds it with `{{ csrf_field() }}`, AJAX paths send it as `X-CSRF-Token`. `/stripe/webhook` is the only allow-listed path (Stripe signs the body instead).
- English and French translations (`translations/messages.en.php`, `messages.fr.php`), per-language switching at `/lang/{locale}`

## Tech stack

| Layer | Choice |
|---|---|
| Language | PHP 8.4 |
| Router / middleware | Slim 4 (`slim/slim`, `slim/psr7`) |
| Templates | Twig 3 |
| ORM | RedBeanPHP 5.7 |
| Container | PHP-DI 7 |
| Env loader | `vlucas/phpdotenv` 5.6 |
| i18n | `symfony/translation` 8 |
| TOTP 2FA | `robthree/twofactorauth` 3 |
| Payments | `stripe/stripe-php` 20 |
| Front-end | Bootstrap 5 + Bootstrap Icons (CDN), hand-written CSS in `css/site.css` and `css/forms.css`, plain JavaScript |
| Maps | Google Maps JS API + Google Geocoding API |

## Requirements

- PHP 8.4 with Composer
- MySQL or MariaDB (`utf8mb4` / `utf8mb4_unicode_ci`)
- Apache (WampServer / XAMPP) or another PHP-capable web server with `mod_rewrite`-style routing if you serve from a virtual host
- A Stripe account for real checkout flows (test keys work in development)
- A Google Maps JS API key with the **Maps JavaScript**, **Places**, and **Geocoding** APIs enabled, for the `/map` page and admin venue geocoding

## Setup

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Copy `.env` and fill in your local values:

   ```env
   DB_SERVER=127.0.0.1
   DB_PORT=3306
   DB_USERNAME= DB_USERNAME
   DB_PASSWORD= DB_PASSWORD
   DB_NAME=ticketmaestrix_ecomdb

   APP_BASE_PATH=/Ticketmaestrix
   APP_DEBUG=true
   APP_URL=http://localhost/Ticketmaestrix

   STRIPE_SECRET_KEY=sk_test_xxx
   STRIPE_WEBHOOK_SECRET=whsec_xxx

   GOOGLE_MAPS_API_KEY=......
   ```

3. Create the `ticketmaestrix_ecomdb` database in MySQL, then import the full dump in `database/ticketmaestrix_ecomdb.sql`. The dump contains every table the app needs (`users`, `events`, `ticket`, `orders`, `order_items`, `categories`, `venue`, `points_history`, `authtoken`, `tfatoken`, `stripepending`) plus seed data so the UI has something to browse.

   ```bash
   mysql -u root ticketmaestrix_ecomdb < database/ticketmaestrix_ecomdb.sql
   ```

4. Serve through Apache / WampServer. With the default WampServer subfolder layout:

   ```text
   http://localhost/Ticketmaestrix/
   ```

   If you serve from a virtual host root, leave `APP_BASE_PATH` empty.

5. Point Stripe at your webhook endpoint:

   ```text
   {APP_URL}/stripe/webhook
   ```

   For local development, use [`stripe listen --forward-to`](https://stripe.com/docs/stripe-cli) and copy the printed `whsec_…` into `STRIPE_WEBHOOK_SECRET`.

## Environment variables

| Variable | Purpose |
|---|---|
| `DB_SERVER`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, `DB_NAME` | MySQL connection. Default DB name is `ticketmaestrix_ecomdb`. |
| `APP_BASE_PATH` | `/Ticketmaestrix` when served from a WampServer subfolder; empty when served from a virtual host root. |
| `APP_DEBUG` | `true` → RedBeanPHP fluid mode, auto-creates and alters tables. `false` → frozen schema, safer for production-like environments. |
| `APP_URL` | Public URL used to build Stripe success/cancel redirects. |
| `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET` | Stripe Checkout + webhook signing. |
| `GOOGLE_MAPS_API_KEY` | Used both server-side (venue geocoding) and embedded into `/map` for the JS API loader. |
| `APP_NAME` *(optional)* | Label shown in the TOTP authenticator app. Defaults to `Ticketmaestrix`. |

## Routes overview

All routes live in `index.php`. Controllers are wired into the PHP-DI container in the same file

### Public

| Method | Path | Handler |
|---|---|---|
| GET | `/` | `HomeController::index` — featured row + paginated upcoming grid |
| GET | `/map` | `HomeController::showMap` — initial 20 events; sidebar streams more via `/api/map-events` |
| GET | `/cart` | `HomeController::showCart` (no login required to view) |
| GET | `/events` | `EventController::index` — list + filter + search |
| GET | `/events/on-sale` | `HomeController::showOnSale` |
| GET | `/events/category/{id}` | `EventController::byCategory` |
| GET | `/events/{id}` | `EventController::viewDetails` |
| GET | `/tickets/event/{id}` | `TicketController::byEvent` — seat selection (user-facing) |
| GET | `/api/search` | `EventController::searchJson` |
| GET | `/api/map-events` | `HomeController::mapEventsJson` |
| GET | `/lang/{locale}` | Switch between `en` and `fr` |

### Auth

| Method | Path | Handler |
|---|---|---|
| GET/POST | `/signup`, `/login` | Account creation and login |
| POST | `/logout` | Session + remember-me teardown |
| GET/POST | `/2fa/setup` | QR code + first-time TOTP verification |
| GET/POST | `/2fa/login` | TOTP challenge on login |

### Logged-in user (`AuthMiddleware`)

| Method | Path | Handler |
|---|---|---|
| GET | `/profile` | Profile with stats + paginated purchase history + points ledger |
| GET / POST | `/editprofile` | Edit name, email, password, avatar |
| POST | `/delete-account`, `/profile/delete` | Self-service account deletion |
| POST | `/cart/add`, `/cart/remove/{ticket_id}`, `/cart/clear`, `/cart/expire` | Mutate the session cart |
| GET / POST | `/checkout` | Order summary → Stripe Checkout session |
| GET | `/checkout/success`, `/checkout/cancel` | Stripe return pages |

### Webhook (allow-listed by `CsrfMiddleware`, Stripe-signed)

| Method | Path | Handler |
|---|---|---|
| POST | `/stripe/webhook` | `StripeWebhookController::handle` — the Stripe-Signature header proves it really came from Stripe, so the CSRF check is skipped here only |

### Admin (`AdminMiddleware`)

| Method | Path | Handler |
|---|---|---|
| GET | `/admin` | Dashboard with stats + dual-paginated events/users tabs |
| | `/users/*`, `/categories/*`, `/venues/*`, `/tickets/*`, `/orders/*`, `/order-items/*` | Full CRUD plus role toggle on users |

## Project structure

```text
index.php                Slim bootstrap, container wiring, middleware, and routes
src/Controllers/         Slim route handlers (Admin, Auth, Cart, Category, Event, Home, Order, OrderItem, StripeWebhook, Ticket, User, Venue)
src/Models/              RedBeanPHP data access wrappers (Category, Event, Order, OrderItem, PointsHistory, Ticket, User, Venue)
src/Helpers/             Auth, Cart (session-backed), BeanHelper (type casting)
src/Middleware/          AuthMiddleware, AdminMiddleware, CsrfMiddleware, MaintenanceMiddleware, SecurityHeadersMiddleware
src/Services/            OtpService (TOTP), StripeService (Checkout + Coupon)
templates/               Twig views, layout.html.twig is the shell, partials/ holds the pagination + flash + navbar
translations/            messages.en.php and messages.fr.php (flat key → value arrays)
css/                     site.css (brand + shared components, including .pagination), forms.css (form states)
assets/img/              Static images (logo)
uploads/avatars/         User avatar uploads (PHP execution denied via .htaccess)
database/                Full schema + seed-data dump (ticketmaestrix_ecomdb.sql)
var/                     Runtime data (app.log, maintenance.flag) — git-ignored
```

## Development notes

### General

- All routes are defined in `index.php`. Controllers are manually registered in the PHP-DI container; when adding a new controller, register it there and add its routes before `$app->run()`.
- After `R::freeze(!$debug)`, `index.php` calls `R::setAllowFluidTransactions(true)` — without it `R::begin/commit/rollback` are silent no-ops in fluid mode, which would break the Stripe webhook's all-or-nothing order creation. Do not remove this line.
- Protected controller actions use `Auth::requireLogin()` and `Auth::requireAdmin()` redirect helpers (they return a 302 or `null`).
- Every controller receives `$basePath` and passes it to Twig as `base_path` so links survive both subfolder and virtual-host installs.
- Every state-changing form must include `{{ csrf_field() }}` as the first child. `CsrfMiddleware` rejects POST/PUT/PATCH/DELETE requests without a valid token. JS-driven mutations (e.g. `/cart/expire` from `cart_expiry_flash.html.twig`) send the token via the `X-CSRF-Token` header instead — both paths are accepted.
- Add new translation keys to **both** `translations/messages.en.php` and `messages.fr.php`. The Twig `trans('key')` function resolves against the session locale; `trans_cat(name)` translates category names.
- Use `BeanHelper::castBeanProperties()` (or `castBeanArray`) before handing RedBeanPHP beans to Twig so foreign-key fields render as integers.
- Use transactions for multi-table writes. The Stripe webhook is the canonical example.

### Currency

- All money is **Canadian dollars**. `StripeService` hard-codes `'currency' => 'cad'` for line items, the service-fee line item, and the points-discount `Coupon::create`. The `$` symbol in templates is the standard CAD glyph, so prices render without a code suffix. If you ever need multi-currency, parameterise the value instead of hard-coding a new one.

### Pagination

- Every list page paginates at 30 per page using the shared partial at `templates/partials/pagination.html.twig`. Pass `current_page`, `total_pages`, `query_params`, `base_url`, and optionally `page_key` / `hash`.
- Pagination CSS lives in `css/site.css` (`.pagination .page-link`, `.is-loading`, `.tm-loading-bar`). Do not redefine these per template.
- Models expose paginated/count method pairs alongside their unbounded methods. The unbounded methods (`getAll`, `findAll`, `getUpcoming`, `findByCategory`, etc.) are kept on purpose because dropdowns, the home featured row, `/api/search`, and the live-search sidebar all want everything.

### RedBeanPHP gotchas

- The PK column is always `id`; foreign keys are `<parent>_id`.
- Bean type names are case-sensitive and must match the live table name exactly — `events`, `ticket` (singular), `orders`, `order_items`, `points_history`, `venue`, `categories`, `users`, `authtoken`, `tfatoken`, `stripepending`.
- `R::dispense('order_items')` and `R::dispense('points_history')` throw `RedException: Invalid bean type` because the type contains an underscore. `R::find`, `R::load`, and `R::store` on an already-loaded bean skip the check and work fine; for inserts into those tables, use raw `R::exec('INSERT INTO ...')`. See `OrderItemModel::create` and `PointsHistoryModel::addPoints` as the canonical examples.

## Common tasks

Install or refresh PHP dependencies:

```bash
composer install
```

Refresh Composer autoloading after adding classes:

```bash
composer dump-autoload
```

Enable maintenance mode (Windows / cmd.exe):

```bash
mkdir var
type nul > var\maintenance.flag
```

Disable maintenance mode:

```bash
del var\maintenance.flag
```

## Deployment

The app is a plain PHP project: copy `index.php`, `src/`, `templates/`, `translations/`, `css/`, `assets/`, `uploads/`, `vendor/`, and `composer.*` to your web server's document root. Configure your production `.env` separately on the server — it is intentionally not version-controlled. Make sure `var/` is writable for the request log and the maintenance flag.

## Authors

- George Vogas (2480396)
- Fadwa Shalby (6296112)
- Lucas Coveyduck (2478812)

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

## Acknowledgments

- README structure inspired by [DomPizzie/README-Template](https://gist.github.com/DomPizzie/7a5ff55ffa9081f2de27c315f5018afc)
