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

Panels: `/admin` (Super Admin), `/manage` (Body Corporate Admin). API: `/api/v1/*`, token-authenticated via
`POST /api/v1/auth/login` (email/password → access token) — or `php spark make:apitoken <email>` for local
testing without going through the login flow.

### Hardware & payments

- Lockers belong to a `locker_rack`, each rack has a `hardware_provider`: `mock` (pure-software simulation,
  used everywhere until real hardware is installed) or `hivebox` (stubbed adapter for Hive-Box smart parcel
  lockers — see `app/Libraries/Hardware/HiveBoxHardwareProvider.php`, pending their developer API access).
- Payment gateways (`app/Libraries/Payments/`) are fully implemented for Ozow (hosted EFT page, HashCheck
  signing/verification) and PayFast (hosted card page, MD5 signature), plus a Manual/offline option with a
  proof-of-payment upload and a Body Corporate Admin approval screen (`/manage/payments`). Each gateway is
  independently toggleable globally (`/admin/gateways`, where Super Admin also sets merchant credentials) or
  per property (`/manage/gateways`, enable/disable + manual instructions only — credentials stay platform-wide).
- Pricing (`pricing_rules`): Super Admin sets the platform default per size (`/admin/pricing`); a Body
  Corporate can override any size for their own property (`/manage/pricing`) or leave it blank to keep
  inheriting the default. `PricingRuleModel::forPropertyAndSize()` resolves which one applies.
- Size auto-recommendation: `App\Libraries\LockerSizeRecommender` maps weight/dimensions to S/M/L/XL against
  the same thresholds shown in the size picker (`POST /api/v1/lockers/recommend-size`) — used when a sender
  knows the package's size upfront instead of guessing.

### Tenant management

- Invite one at a time (`/manage/tenants/new`) or in bulk via CSV (`/manage/tenants/import` — columns
  `unit_number,full_name,email,phone`; existing emails are skipped, not overwritten).
- Delegate collection: a tenant can mint a one-time code (`POST /api/v1/parcels/{id}/delegate`) so someone
  else — a family member, a helper — can collect on their behalf without needing the tenant's own PIN or an
  account. Collection itself (`POST /api/v1/parcels/collect`) is intentionally public/unauthenticated: knowing
  the PIN, QR token, or delegate code *is* the authorization, exactly like a physical locker. This is also what
  lets the website's no-login `/collect` page work.
- Household visibility: a unit is a household, not one person. `/api/v1/reservations/mine` and
  `/api/v1/parcels/mine` return everything for everyone sharing the tenant's `unit_id` (see
  `BaseApiController::householdTenantIds()`), each item tagged with `reserved_by` so the app can attribute it.
  Any housemate can also mint a delegate code for a household parcel. Write actions that aren't purely about
  visibility (cancelling a reservation) stay restricted to whoever actually created it. `/api/v1/me` also
  returns `household`: the other tenants' names on the same unit.
- Peer-to-peer send: a resident can reserve a locker *for a neighbour* (`recipient_unit_number` on
  `POST /api/v1/reservations`, resolved via `GET /api/v1/properties/mine/residents?q=`) instead of themselves —
  `reservations.tenant_id` becomes the recipient (so all existing notify/collect logic just works unmodified)
  and `created_by` records who arranged it. Since the sender is physically on-site, they deposit it themselves
  right after reserving via the same public `/api/v1/parcels/deposit` the courier flow uses — no code ever
  needs to be shared. Both sender and recipient see the item in their own `/mine` lists, tagged `reserved_by`
  and `sent_by` respectively; the sender can also cancel it before it's deposited.
- Staff accounts: on-site concierge/security (`/manage/staff`, Body Corporate only) get the `staff` group,
  scoped to the property like a tenant but with no unit. `PropertyOwnerFilter` restricts tenants/staff/payments/
  gateways/pricing/branding to `property_admin`/`superadmin` — staff only reach the dashboard, parcels view and
  maintenance triage (`PropertyAdminFilter`, which still allows staff through to those).
- Pre-alerts (`parcel_prealerts`): a tenant registers a courier tracking number ahead of time
  (`POST /api/v1/pre-alerts`); `php spark parcels:check-prealerts` (run on a schedule) checks each one via
  `App\Libraries\Tracking\CourierTrackingProviderInterface` and auto-reserves a locker once it's out for
  delivery, notifying the tenant. No real courier tracking API is wired up yet (same situation as Hive-Box) —
  `MockCourierTrackingProvider` simulates "out for delivery" a couple of minutes after registration so the
  whole flow is demoable/testable; swapping in a real aggregator (AfterShip, Ship24, or a specific courier's
  API) only means implementing the interface.
- Public-locker sites: a property with `type = public_site` (`/admin/properties/new`) is open to *any*
  signed-in app user, not just its own residents — `GET /api/v1/public-sites` lists them,
  `POST /api/v1/reservations` accepts an optional `property_id` to book at one instead of your own property
  (peer-to-peer sending is disabled for these, since "send to a neighbour" only makes sense at one's own
  complex). Pricing/payment reuse the same per-property engine, so a public site can be configured as
  genuinely pay-per-use.

### Notifications

`app/Libraries/Notifications/` — a `NotificationService` fans out to push (Expo push API), email (CI4's
built-in SMTP service), and SMS (BulkSMS) channels, logging every attempt to `notifications_log` regardless of
whether it actually reached the user (best-effort: a missing SMS/SMTP credential never blocks the underlying
reservation/parcel action). Wired into: parcel deposited (pickup PIN to the tenant), parcel collected, and
tenant invites. Configure real credentials in `.env` (see the `EMAIL`/`SMS` sections in `env`) — without them,
everything still works end-to-end, just with notifications logged as `failed`.

Scheduled jobs (run via cron / Windows Task Scheduler):
```
php spark reservations:expire       # releases lockers whose hold window passed without a deposit
php spark parcels:remind            # reminds a tenant (at most once/day) about an uncollected parcel
php spark parcels:check-prealerts   # auto-reserves a locker once a tracked courier is out for delivery
php spark billing:generate-invoices # generates this month's subscription invoice per property (also has an "Generate" button in /admin/billing)
```

### Security & operations

- Rate limiting (`app/Filters/ThrottleFilter.php`, CI4's built-in token-bucket `Throttler`): login (10/5min),
  deposit (20/min) and collect (10/min) are all IP-throttled. This matters specifically because deposit/collect
  are intentionally public/unauthenticated (see above) — a 6-digit PIN is only 900k combinations, so without
  this a single IP could brute-force one in minutes.
- Audit log (`/admin/audit-log`): every meaningful write in either admin panel — property/pricing/gateway
  changes, tenant invites and removals, payment approvals, maintenance status changes, branding updates — is
  recorded via `BaseController::audit()` with who did it and when. Never throws (a logging failure can't break
  the action it's recording).
- Maintenance ticketing: a tenant can report a problem (optionally tied to the locker behind a specific
  parcel) from the app; a Body Corporate Admin triages it at `/manage/maintenance`. Starting work on a
  locker-linked ticket takes that locker `out_of_service` (so it's skipped when reserving); resolving it
  returns the locker to `available`.
- White-label branding: a Body Corporate sets their own logo URL and accent colour at `/manage/branding` —
  both are returned from `/api/v1/me` and shown in the tenant app's Home screen.
- Hardware fleet monitoring (`/admin/hardware`): every locker rack across every property — provider, online/
  offline status, and a live available/occupied/reserved/out-of-service breakdown — plus the last inbound
  webhook timestamp per provider (Hive-Box/Ozow/PayFast), so a Super Admin can see whether an integration has
  gone quiet.
- Billing (`/admin/billing`): a per-property monthly subscription fee, invoice generation (button or
  `billing:generate-invoices`, idempotent per property/month), and mark-as-paid.
- Broadcast (`/admin/broadcast` platform-wide or filtered to one property; `/manage/broadcast` for a Body
  Corporate's own residents only): sends a push + email to every matching tenant through the same
  `NotificationService` as parcel updates. This is the "broadcast" half of the plan's CMS feature — managing
  the marketing website's own content is not built.

## Mobile (`/mobile`)

Expo-managed React Native app, fully wired to the live API. `npm install`, copy `.env.example` to `.env` (point
`EXPO_PUBLIC_API_URL` at your backend — use your machine's LAN IP, not `localhost`, when testing on a physical
device), then `npx expo start`.

First launch: onboarding (how reserving, sharing a code, and collecting works) → splash screen → welcome/login.
Sign in with any of the demo accounts above (e.g. `tenant@dropa.app` / `DropaTenant123!`).

- **Home** — property/unit auto-detected on login, quick actions, parcels awaiting collection
- **Reserve** — for yourself, or search-and-pick a neighbour to send to instead; pick a size (live per-size
  availability). For yourself: get a deposit code to share (native share sheet/copy) and pay if the property
  charges a booking fee (opens Ozow/PayFast in the browser, or shows manual instructions with a photo-library
  proof-of-payment upload). Sending to a neighbour: no code to share — tap "I've dropped it off" right in the
  app once you've placed it in the locker
- **Parcels** — household-wide history, attributed as "· for {name}" (a housemate's item) or "· sent to {name}"
  (something you arranged for someone else); tap an active one for its PIN + a real scannable QR code
  (`react-native-qrcode-svg`), or to generate and share a delegate collection code
- **Collect** — PIN keypad or QR camera scan (`expo-camera`), either resolves through the same API endpoint
- **Profile** — account details, who else shares your unit, a large-text mode toggle (bigger PINs/codes/keypad
  at the locker — `src/context/AccessibilityContext.tsx`), change password, log out
- A parcel's detail screen also has "Report a problem with this locker", filing a maintenance ticket the Body
  Corporate can see and act on
- Home shows the property's own logo/accent colour when the Body Corporate has set one, and links to **Track
  a delivery** (register a courier tracking number, watch it move from "watching" to "locker reserved") and
  **Public lockers** (browse pay-per-use sites and reserve one, regardless of which property you actually live at)
- Reserve screen: an optional "know the package's size?" section calls the size-recommendation endpoint and
  pre-selects a locker size from weight/dimensions
- Push notifications: registers the device's Expo push token with the backend on login
  (`app/Libraries/Notifications/PushChannel.php` sends to it)

Auth token lives in `expo-secure-store`; design tokens ported from the prototype live in `src/theme/tokens.ts`;
the app icon/splash brand mark is in `assets/brand/*.svg`, rasterized via `node scripts/build-brand-assets.js`.

## Web (`/web`)

Next.js (App Router + Tailwind). `npm install && npm run dev`. Copy `.env.local.example` to `.env.local` to
point it at the backend API. Pages: marketing home, `/onboard` (property lead form), `/drop-off` (no-login
courier deposit), `/collect` (no-login collection — PIN, QR value, or delegate code).
