# Dropa

Dropa is a digital letterbox for complexes, townhouses and estates: residents reserve a smart locker when
they're expecting a delivery, a courier drops it off with a code (no account needed), and the locker frees
itself the moment it's collected.

This is a monorepo with three apps sharing one backend/API:

```
/backend   CodeIgniter 4 app — REST API, Super Admin panel (/admin), Body Corporate Admin panel (/manage)
/mobile    React Native (Expo) app — tenants (reserve/collect) and senders (drop-off)
/web       Next.js website — marketing, property onboarding, no-login courier drop-off
/docs      the original click-through prototype, kept for design reference
```

See [`docs/dropa-prototype.html`](docs/dropa-prototype.html) for the original UX prototype this product is based on.

## Backend (`/backend`)

CodeIgniter 4 + [Shield](https://codeigniter4.github.io/shield/) for auth, roles: `superadmin`, `property_admin`,
`staff`, `tenant`. Local dev uses SQLite (zero setup); production should switch `database.default.DBDriver` to
`MySQLi` in `.env`.

```
cd backend
composer install
cp env .env            # already done in this repo; edit as needed
php spark migrate --all
php spark db:seed DemoSeeder   # creates a super admin, a demo property, a body corporate admin, a tenant and mock lockers
php spark serve
```

Demo logins (from `DemoSeeder`):

| Role | Email | Password |
|---|---|---|
| Super Admin | `super@dropa.app` | `DropaSuper123!` |
| Body Corporate Admin | `bodycorp@dropa.app` | `DropaAdmin123!` |
| Tenant | `tenant@dropa.app` | `DropaTenant123!` |

Panels: `/admin` (Super Admin), `/manage` (Body Corporate Admin). API: `/api/v1/*`, token-authenticated
(`php spark make:apitoken <email>` mints a personal access token for local testing).

### Hardware & payments

- Lockers belong to a `locker_rack`, each rack has a `hardware_provider`: `mock` (pure-software simulation,
  used everywhere until real hardware is installed) or `hivebox` (stubbed adapter for Hive-Box smart parcel
  lockers — see `app/Libraries/Hardware/HiveBoxHardwareProvider.php`, pending their developer API access).
- Payment gateways (`app/Libraries/Payments/`) are pluggable per the same pattern: Ozow, PayFast, and a Manual
  offline option, each independently toggleable globally (`/admin/gateways`) or per property.

## Mobile (`/mobile`)

Expo-managed React Native app. `npm install && npx expo start`. First launch shows onboarding (how reserving,
sharing a code, and collecting works), then a splash screen and welcome screen carrying the Dropa brand mark
(`assets/brand/*.svg`, rasterized via `node scripts/build-brand-assets.js`).

## Web (`/web`)

Next.js (App Router + Tailwind). `npm install && npm run dev`. Copy `.env.local.example` to `.env.local` to
point it at the backend API.
