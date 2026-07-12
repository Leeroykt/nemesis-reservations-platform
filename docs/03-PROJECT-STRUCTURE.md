# Project & Folder Structure

Repository: `nemesis-reservations-platform` (private git repo — this is the
one golden repo referenced in the Product Spec, cloned per client).

```
nemesis-reservations-platform/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       ├── AuthController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── ReservationController.php
│   │   │       ├── TableController.php
│   │   │       ├── CustomerController.php
│   │   │       ├── AnalyticsController.php
│   │   │       ├── SettingsController.php
│   │   │       ├── UserController.php
│   │   │       ├── AuditLogController.php
│   │   │       ├── NotificationController.php
│   │   │       └── PublicReservationController.php   # unauthenticated
│   │   ├── Requests/                                   # Form Request validation classes
│   │   │   ├── StoreReservationRequest.php
│   │   │   ├── UpdateReservationRequest.php
│   │   │   └── ...
│   │   └── Middleware/
│   │       └── EnsureRole.php                          # server-side role gate
│   ├── Models/
│   │   ├── Restaurant.php
│   │   ├── RestaurantHours.php
│   │   ├── RestaurantRules.php
│   │   ├── User.php
│   │   ├── Table.php
│   │   ├── Customer.php
│   │   ├── CustomerPreference.php
│   │   ├── Reservation.php
│   │   ├── Waitlist.php
│   │   ├── Notification.php
│   │   ├── ActivityLog.php
│   │   └── EmailTemplate.php
│   ├── Services/
│   │   ├── ReservationService.php    # conflict-checking, validation — the ONE source of truth
│   │   ├── AnalyticsService.php
│   │   └── AuditLogger.php
│   ├── Mail/
│   │   ├── BookingConfirmed.php
│   │   ├── BookingCancelled.php
│   │   └── VipWelcome.php
│   └── Jobs/
│       └── FlagVipCustomers.php      # scheduled job
│
├── database/
│   ├── migrations/                   # one file per table in 01-DATABASE-SCHEMA.md
│   ├── seeders/
│   │   ├── DemoSeeder.php            # fake data — powers the generic walkthrough VM
│   │   ├── ClientSeeder.php          # reads client-config/seed-config.json — real client onboarding
│   │   └── DatabaseSeeder.php
│   └── factories/                    # for automated tests
│
├── routes/
│   ├── api.php                       # /api/v1/* routes
│   └── web.php                       # serves the SPA shell + public pages
│
├── resources/
│   ├── css/
│   │   └── app.css                   # imports style.css's variables/theme, unchanged design tokens
│   ├── js/
│   │   ├── app.tsx                   # Inertia entrypoint (from Breeze scaffold)
│   │   ├── ssr.tsx                   # unused — SSR disabled for v1, kept for future toggle
│   │   ├── Pages/                    # ONE FOLDER PER MODULE — this is the testable-module boundary
│   │   │   ├── Auth/
│   │   │   │   └── Login.tsx
│   │   │   ├── Public/
│   │   │   │   └── BookingForm.tsx   # the public, unauthenticated booking page
│   │   │   ├── Overview/
│   │   │   │   └── Index.tsx
│   │   │   ├── Reservations/
│   │   │   │   ├── Index.tsx
│   │   │   │   ├── ReservationModal.tsx
│   │   │   │   └── NewReservationModal.tsx
│   │   │   ├── Calendar/
│   │   │   │   ├── MonthView.tsx
│   │   │   │   └── WeekView.tsx
│   │   │   ├── Tables/
│   │   │   │   └── FloorPlan.tsx
│   │   │   ├── Customers/
│   │   │   │   ├── Index.tsx
│   │   │   │   └── CustomerProfile.tsx
│   │   │   ├── Analytics/
│   │   │   │   └── Index.tsx
│   │   │   ├── Settings/
│   │   │   │   ├── Restaurant.tsx
│   │   │   │   ├── Hours.tsx
│   │   │   │   ├── Rules.tsx
│   │   │   │   ├── Branding.tsx
│   │   │   │   ├── Users.tsx
│   │   │   │   └── EmailTemplates.tsx
│   │   │   └── AuditLog/
│   │   │       └── Index.tsx
│   │   ├── Components/               # shared, reused across modules
│   │   │   ├── Layout/
│   │   │   │   ├── Sidebar.tsx
│   │   │   │   ├── Topbar.tsx
│   │   │   │   └── DashboardLayout.tsx
│   │   │   ├── Charts/
│   │   │   │   ├── RevenueChart.tsx  # react-chartjs-2 wrapper, ported from charts.js
│   │   │   │   ├── StatusDoughnut.tsx
│   │   │   │   └── ...
│   │   │   ├── KpiCard.tsx
│   │   │   ├── StatusBadge.tsx
│   │   │   ├── Toast.tsx
│   │   │   └── RoleGate.tsx          # client-side convenience only — server enforces the real gate
│   │   ├── hooks/
│   │   │   ├── useTheme.ts           # port of theme.js
│   │   │   └── useAuth.ts
│   │   └── types/
│   │       ├── reservation.d.ts      # TypeScript types mirroring the API/Eloquent resource shapes
│   │       ├── customer.d.ts
│   │       └── ...
│   └── views/
│       └── app.blade.php             # single Inertia root view (from Breeze scaffold)
│
├── public/
│   ├── build/                        # Vite build output, gitignored
│   └── img/
│       └── (client logo lands here at deploy time, see client-config/)
│
├── client-config/                    # NOT committed with real client data — .gitignored per-instance
│   ├── seed-config.example.json      # template committed to repo
│   └── logo.example.png
│
├── docker/
│   ├── Dockerfile
│   ├── docker-compose.yml
│   ├── Caddyfile
│   └── php.ini
│
├── docs/                             # this folder — lives in the repo, always current
│   ├── 00-PRODUCT-SPEC.md
│   ├── 01-DATABASE-SCHEMA.md
│   ├── 02-FEATURE-SPEC.md
│   ├── 03-PROJECT-STRUCTURE.md
│   ├── 04-ROADMAP.md
│   ├── 05-QA-CHECKLIST.md
│   ├── 06-CLIENT-ONBOARDING-RUNBOOK.md
│   └── BACKLOG.md
│
├── tests/                            # Pest — backend, one file per module, ships WITH the module
│   ├── Feature/
│   │   ├── Auth/
│   │   │   └── LoginTest.php
│   │   ├── Reservations/
│   │   │   ├── CreateReservationTest.php
│   │   │   ├── ConflictDetectionTest.php
│   │   │   └── BulkActionsTest.php
│   │   ├── Public/
│   │   │   └── PublicBookingTest.php
│   │   ├── RoleAccessTest.php        # the test that guarantees host can't hit owner routes
│   │   └── ...                       # one folder per module in 02-FEATURE-SPEC.md, no exceptions
│   └── Unit/
│       └── Services/
│           └── ReservationServiceTest.php
│
├── resources/js/                     # Vitest + React Testing Library — colocated with the component
│   └── Pages/Reservations/
│       ├── Index.tsx
│       └── Index.test.tsx            # every Page/Component ships with its own .test.tsx, same folder
│
├── deploy.sh                         # one client, one command — see roadmap Phase 7
├── .env.example
├── .github/workflows/ci.yml
└── README.md                         # points here, to docs/, first
```

## Naming & conventions
- API responses: consistent JSON envelope `{ data: ..., meta: ... }` for
  lists, `{ data: ... }` for single resources — no ad-hoc shapes per
  endpoint.
- All money values in cents in the DB (`lifetime_spend` etc.), formatted to
  currency only at the presentation layer — avoids float rounding bugs.
- All dates stored as `date`/`time`/`timestamp` types, never strings —
  timezone-aware via `restaurants.timezone`, formatted client-side.
- Every mutating endpoint writes an `activity_log` row via `AuditLogger` —
  this is enforced in the service layer, not left to each controller to
  remember.
