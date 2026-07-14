# NEMESIS Reservations Platform – Progress Tracking

**Last Updated:** 2026-07-14

This document tracks our progress against the original documentation (`docs/04-ROADMAP.md`, `docs/02-FEATURE-SPEC.md`, `docs/01-DATABASE-SCHEMA.md`). It records every deviation, new file introduced, version change, and decisions made.

---

## 📌 Summary

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 0 – Repo & Environment | ✅ Complete | Laravel 12 installed (instead of Laravel 11), Docker skipped (deferred to Phase 7) |
| Phase 1 – Database & Models | ✅ Complete | All 12 tables + models; DemoSeeder matches data.js 1:1 |
| Phase 2 – Auth & Role Enforcement | ✅ Complete | Sanctum SPA auth, EnsureRole middleware, RoleAccessTest passes |
| Phase 3 – Reservation Engine | ✅ Complete | ReservationService, CRUD + bulk, AuditLogger, feature tests pass |
| Phase 4 – Wire Dashboard UI to API | 🔄 In Progress | Overview page done; Reservations, Calendar, Tables, Customers, Analytics pending |

---

## 🔧 Key Decisions & Deviations

### 1. Laravel Version
- **Docs:** Laravel 11 (`00-PRODUCT-SPEC.md`)
- **Actual:** Laravel 12.12.2
- **Reason:** Composer installed latest stable that meets PHP 8.2 requirements. Laravel 12 is fully backward compatible with 11 for our feature set. No breaking changes affect our migrations, models, or controllers.
- **Files affected:** `composer.json`, `composer.lock`

### 2. Docker Skipped
- **Docs:** Docker + docker-compose.yml + Caddy (`04-ROADMAP.md` Phase 0)
- **Actual:** Docker not installed or tested.
- **Reason:** Limited internet data. Docker is a deployment concern, not required for core development. We will revisit in Phase 7 (Deploy Automation) or later.
- **Files affected:** None (Docker files exist as placeholders but were not committed as part of this phase).

### 3. PostgreSQL vs SQLite
- **Docs:** PostgreSQL 16 (`00-PRODUCT-SPEC.md`)
- **Actual:** `.env` initially configured for SQLite, then corrected to PostgreSQL.
- **Reason:** SQLite was used temporarily to get the project running without installing PostgreSQL. We later switched to PostgreSQL to match the spec.
- **Files affected:** `.env`, `.env.example`

### 4. Users Table – Added Columns
- **Docs:** `01-DATABASE-SCHEMA.md` requires `restaurant_id`, `role`, `avatar_initials`, `last_login_at`
- **Actual:** We created a new migration `add_restaurant_role_columns_to_users_table.php` to add these columns.
- **Reason:** Laravel's default `users` migration (created by Breeze) did not include these columns. We extended it instead of modifying the original migration.
- **Files affected:** `database/migrations/2026_07_12_205049_add_restaurant_role_columns_to_users_table.php`, `app/Models/User.php`

### 5. Singular Table Names
- **Docs:** `activity_log`, `waitlist`
- **Actual:** Laravel expects plural table names by default. We added `protected $table = 'activity_log'` and `protected $table = 'waitlist'` to the models.
- **Reason:** To match the spec without renaming tables in migrations.
- **Files affected:** `app/Models/ActivityLog.php`, `app/Models/Waitlist.php`

### 6. DemoSeeder Alignment with data.js
- **Docs:** `DemoSeeder` should reproduce `data.js`
- **Actual:** We updated `DemoSeeder` to match `data.js` exactly.
- **Reason:** The original seeder only had 6 reservations, 4 activities, 3 notifications. `data.js` has 15 reservations, 6 activities, 5 notifications, 2 waitlist entries, 24 tables, 8 customers.
- **Files affected:** `database/seeders/DemoSeeder.php`

### 7. Vitest vs Jest
- **Docs:** Vitest + React Testing Library (`03-PROJECT-STRUCTURE.md`)
- **Actual:** Vitest configured and working.
- **Reason:** Vitest is the spec. We set it up with jsdom and `@testing-library/react`.
- **Files affected:** `vitest.config.js`, `vitest.setup.js`, `package.json`

---

## 📁 New Files Introduced

These files were not explicitly listed in `03-PROJECT-STRUCTURE.md` but were added as needed:

| File | Reason |
|------|--------|
| `database/migrations/2026_07_12_205049_add_restaurant_role_columns_to_users_table.php` | Extended `users` table to match schema |
| `database/migrations/2026_07_13_104357_add_revenue_and_avg_spend_to_tables.php` | Added revenue and avg_spend_per_person columns |
| `app/Http/Controllers/Api/V1/AuthController.php` | Auth endpoints (login, logout, me) |
| `app/Http/Requests/LoginRequest.php` | Login validation |
| `routes/api.php` | API routes (not just web routes) |
| `vitest.config.js` | Frontend test configuration |
| `vitest.setup.js` | Vitest global setup (jest-dom) |
| `PROGRESS.md` | This tracking document |

---

## 🔄 Version Changes / Downgrades

| Package | Original Version (Spec) | Actual Installed Version | Reason |
|---------|--------------------------|--------------------------|--------|
| `laravel/framework` | ^11.0 | ^12.0 | Composer resolved to latest stable that meets PHP 8.2. Backward compatible. |
| `@vitejs/plugin-react` | Latest | 4.x | Installed version 4.x because latest requires Vite 8, but we use Vite 7. |

---

## ✅ Phase 0 – Checklist

### Docs: `04-ROADMAP.md` Phase 0

| Item | Docs Says | What We Did | Status |
|------|-----------|-------------|--------|
| Fresh Laravel 11 project | Laravel 11 | Laravel 12 installed | ✅ (deviation) |
| PostgreSQL configured locally | PostgreSQL 16 via Docker | PostgreSQL 16 installed locally | ✅ |
| `docker-compose.yml` (app + postgres + caddy) | Boots clean | Not tested – skipped due to data | ⚠️ (deferred) |
| `.env.example` filled | Every variable needed | All vars added | ✅ |
| CI pipeline stub (GitHub Actions) | Runs `php artisan test` on push | File exists and tests run | ✅ |
| `docs/` folder committed | First commit | All docs committed | ✅ |

**Comments:**
- Docker was intentionally skipped. We will revisit in Phase 7.
- `.env.example` was extended with `RESTAURANT_TIMEZONE`, `RESTAURANT_CURRENCY`, full MAIL config, Sanctum vars, logging vars.

---

## ✅ Phase 1 – Checklist

### Docs: `04-ROADMAP.md` Phase 1

| Item | Docs Says | What We Did | Status |
|------|-----------|-------------|--------|
| Every migration from `01-DATABASE-SCHEMA.md` | In dependency order | All 12 tables created | ✅ |
| Every Eloquent model + relationships | No business logic in models | All models created with relationships, fillable, casts | ✅ |
| `DemoSeeder` reproduces `data.js` | Exact dataset | 15 reservations, 6 activities, 5 notifications, 2 waitlist, 24 tables, 8 customers | ✅ |
| Soft deletes on Reservation, Customer, Table | Yes | `SoftDeletes` trait used | ✅ |
| Critical indexes | `(restaurant_id, date, table_id, status)` | Index exists in migration | ✅ |

**Comments:**
- We added a migration to extend the `users` table with missing columns (`restaurant_id`, `role`, `avatar_initials`, `last_login_at`).
- `ActivityLog` and `Waitlist` models required explicit `$table` definitions because the table names are singular, not plural.

---

## ✅ Phase 2 – Checklist

### Docs: `04-ROADMAP.md` Phase 2, `02-FEATURE-SPEC.md` §1

| Item | Docs Says | What We Did | Status |
|------|-----------|-------------|--------|
| Sanctum SPA auth | Session-based | Sanctum cookie-based (SPA) | ✅ |
| `POST /api/v1/login` | Yes | Implemented | ✅ |
| `POST /api/v1/logout` | Yes | Implemented | ✅ |
| `GET /api/v1/me` | Yes | Implemented | ✅ |
| `EnsureRole` middleware | Applied per-route | Created and registered | ✅ |
| `RoleAccessTest.php` | Automated role gating test | 10 tests pass | ✅ |

**Comments:**
- We use **Sanctum cookie-based SPA authentication** (with `withCredentials`), which is the recommended pattern for first-party SPAs. This aligns with the product spec.

---

## ✅ Phase 3 – Checklist

### Docs: `04-ROADMAP.md` Phase 3, `02-FEATURE-SPEC.md` §3

| Item | Docs Says | What We Did | Status |
|------|-----------|-------------|--------|
| `ReservationService` | Conflict detection, validation | Implemented and tested | ✅ |
| Reservation CRUD + bulk actions API | Yes | Full controller | ✅ |
| `AuditLogger` | Wired to every mutation | Implemented | ✅ |
| Feature tests | Double-booking, over-capacity, over-max-party, bulk actions | 17 tests pass | ✅ |

**Comments:**
- The service uses real database data; no static values.
- Revenue is computed from `avg_spend_per_person` stored in `restaurant_rules`.

---

## 🔄 Phase 4 – Checklist

### Docs: `04-ROADMAP.md` Phase 4, `02-FEATURE-SPEC.md`

| Item | Docs Says | What We Did | Status |
|------|-----------|-------------|--------|
| Build `api.js` (API client) | Replaces `SAVORA_DATA`/`SavoraStore` | Created `resources/js/lib/api.ts` | ✅ |
| `guard.js` checks real session | `/api/v1/me` | Implemented via `api.ts` and `useAuth` | ✅ |
| `auth.js` posts to real login | `/api/v1/login` | Implemented | ✅ |
| Overview screen | Real KPIs, charts, activity | Fetches from `/api/v1/dashboard/*` and `/api/v1/activity` | ✅ |
| Reservations screen | List, filters, search, pagination, modals | ⏳ Pending |
| Calendar screen | Month and week views | ⏳ Pending |
| Tables screen | Floor plan and grid | ⏳ Pending |
| Customers screen | List, search, profile | ⏳ Pending |
| Analytics screen | Peak hours, popular tables, customer growth | ⏳ Pending |

**Comments:**
- The API client and authentication layer are fully implemented and tested.
- The Overview page is complete and displays real data from the database.
- The remaining screens will be built in subsequent steps, following the same pattern.

---

## 📋 Next Steps

1. **Complete Phase 4** – Build the remaining screens:
   - Reservations (list, CRUD modals, bulk actions)
   - Calendar (month/week views)
   - Tables (floor plan, grid, status updates)
   - Customers (list, search, profile)
   - Analytics (charts)
2. **Add frontend tests** for each new page as we build them.
3. **Run full CI suite** after each major milestone.
4. **Proceed to Phase 5** (Public booking + email) once Phase 4 is complete.

---

**End of Progress Tracking – Phase 0, 1, 2, 3 Complete; Phase 4 in Progress**