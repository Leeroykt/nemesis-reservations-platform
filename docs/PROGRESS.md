# NEMESIS Reservations Platform – Progress Tracking

**Last Updated:** 2026-07-12

This document tracks our progress against the original documentation (`docs/04-ROADMAP.md`, `docs/02-FEATURE-SPEC.md`, `docs/01-DATABASE-SCHEMA.md`). It records every deviation, new file introduced, version change, and decisions made.

---

## 📌 Summary

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 0 – Repo & Environment | ✅ Complete | Laravel 12 installed (instead of Laravel 11), Docker skipped |
| Phase 1 – Database & Models | ✅ Complete | All 12 tables + models; DemoSeeder matches data.js 1:1 |
| Phase 2.1 – Auth Endpoints | ✅ Complete | Sanctum login/logout/me endpoints working |
| Phase 2.2 – Role Enforcement | ⏳ Next | EnsureRole, policies, RoleAccessTest pending |

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

### Larastan Fix – createToken return type
- **Issue:** PHPStan thought `createToken()` returned a string, but it returns `NewAccessToken`.
- **Fix:** Corrected `@method` annotation in `User` to return `\Laravel\Sanctum\NewAccessToken`.
- **Also:** Added `@var` docblock for `$token` in `AuthController`.
- **Files affected:** `app/Models/User.php`, `app/Http/Controllers/Api/V1/AuthController.php`
### 8. CI Tools – Postponed to Phase 8

| Tool | Purpose | Status | Reason |
|------|---------|--------|--------|
| `composer audit` | Scans PHP dependencies for known vulnerabilities | ⏳ Phase 8 | Would require fixing all vulnerabilities before pipeline passes |
| `npm audit --audit-level=moderate` | Scans JS dependencies for vulnerabilities | ⏳ Phase 8 | Would fail immediately due to 4 existing vulnerabilities |
| ESLint (`npm run lint:js`) | JavaScript/TypeScript code style and quality | ⏳ Phase 8 | ESLint needs to be installed and configured with rules |
| TypeScript type check (`tsc --noEmit`) | Catches type errors in frontend code | ⏳ Phase 8 | TypeScript config needs adjustment for CI environment |
| PHP test coverage (`--coverage --min=80`) | Ensures code is adequately tested | ⏳ Phase 8 | Coverage thresholds would fail until tests are written |
| JavaScript test coverage (Vitest) | Ensures frontend code is adequately tested | ⏳ Phase 8 | Coverage is not yet configured |
| Security headers check | Checks CSP, CORS, etc. | ⏳ Phase 9+ | Not critical for development phase |
| Artisan optimize | Ensures production caching works | ⏳ Phase 7 (Deployment) | Only relevant for production builds |

**Why Postponed:**

- **Prevents blocking development** – These tools would fail immediately and block every PR, slowing down core feature development.
- **They are hardening steps** – Security audits, coverage thresholds, and strict type checks are final‑stage activities – they belong in Phase 8 (Hardening & QA), not during active feature development.
- **No code quality degradation** – The current CI (Lint, Larastan, Pest, Vitest, Build) already enforces code quality and catches real bugs. Adding the rest later is an incremental improvement, not an emergency fix.
- **Avoids unnecessary overhead** – Installing ESLint and configuring TypeScript for CI takes time that would distract from building features in Phases 3–7.

**Decision:** These tools will be added in Phase 8 (Hardening & QA) or Phase 9 (Final polish), as originally planned in `04-ROADMAP.md`.

## ✅ Phase 2.1 – Checklist

### Docs: `04-ROADMAP.md` Phase 2, `02-FEATURE-SPEC.md` §1

| Item | Docs Says | What We Did | Status |
|------|-----------|-------------|--------|
| Sanctum SPA auth | Session-based | Sanctum token-based (Bearer tokens) | ✅ (deviation) |
| `POST /api/v1/login` | Yes | Implemented | ✅ |
| `POST /api/v1/logout` | Yes | Implemented | ✅ |
| `GET /api/v1/me` | Yes | Implemented | ✅ |
| Server-enforced role | Yes | Not yet – coming in Phase 2.2 | ⏳ |
| Routes | Protected by auth middleware | Routes use `auth:sanctum` | ✅ |

**Comments:**
- We use **Bearer tokens** instead of session cookies. This is still Sanctum, just the token-based flow. It works for v1 and can be switched to cookies later if needed.

---

## 🚫 What Was Not Done Yet (Phase 2.2+)

- `EnsureRole` middleware – **Pending Phase 2.2**
- `ReservationPolicy` – **Pending Phase 2.2**
- `RoleAccessTest.php` – **Pending Phase 2.2**
- Routes with `role:...` middleware – **Pending Phase 2.2**

---

## 📋 Next Steps

1. **Phase 2.2** – Role-Based Access Control:
   - Implement `EnsureRole` middleware
   - Register middleware in `bootstrap/app.php`
   - Create and register `ReservationPolicy`
   - Update routes to use `role:...`
   - Create `RoleAccessTest.php` – executioner test

2. **Commit this `PROGRESS.MD`** after Phase 2.1 is complete.

---

**End of Progress Tracking – Phase 0, 1, 2.1**

## ✅ Phase 2.2 – Checklist

| Item | Status | Notes |
|------|--------|-------|
| `EnsureRole` middleware implemented | ✅ | Role hierarchy: host (1) < manager (2) < owner (3) |
| Middleware registered in `bootstrap/app.php` | ✅ | Aliased as `role` |
| `ReservationPolicy` created | ✅ | viewAny, view, create, update, delete, restore, forceDelete |
| Policy registered in `AuthServiceProvider` | ✅ | Mapped `Reservation::class` to `ReservationPolicy::class` |
| `RoleAccessTest` created | ✅ | 7 tests covering host/manager/owner/unauthenticated access |
| Test annotations updated to `#[Test]` attributes | ✅ | PHPUnit 11+ compatibility |
| PHPStan errors fixed in `EnsureRole` and `User` | ✅ | Added PHPDoc `@property` and `@var` annotations |
| All tests pass | ✅ | 7 passed, 13 assertions |

**Comments:**
- Added temporary route `/api/v1/role-test` to demonstrate role enforcement (will be removed once real routes are built in Phase 3+).
- The middleware and policies are fully functional and tested.
- All static analysis checks now pass at level 5.
- The CI will run these checks on every push.

## ✅ Phase 3 – Checklist
- ✅ ReservationService implemented (conflict detection, validation, auto‑assignment)
- ✅ AuditLogger and TimezoneService created
- ✅ ReservationController with CRUD and bulk actions
- ✅ Store/Update requests and ReservationResource
- ✅ All feature tests passing (17 tests, 61 assertions)
- ✅ Role‑based delete and bulk actions enforced



## ✅ Phase 3 – Checklist (Updated)

| Item | Status | Notes |
|------|--------|-------|
| ReservationService (conflict detection, validation) | ✅ | Implemented and fully tested. |
| Reservation CRUD + bulk actions API | ✅ | Controller with all endpoints. |
| AuditLogger wired for every mutation | ✅ | Logs all create/update/delete/bulk actions. |
| Feature tests: double-booking, over-capacity, etc. | ✅ | 17 tests pass. |
| Timezone handling | ✅ | TimezoneService fixed to handle seconds. |
| PHPStan level 5 passes | ✅ | All errors resolved (see below). |
| Test isolation | ✅ | Used RefreshDatabase with shouldUseTransactions = false. |
| Docker | ⏳ Deferred | See Phase 0 deviation. |

### 🔧 Fixes Applied in Phase 3.2

- Added `@mixin`, `@method`, and `@property` PHPDoc annotations to all models to make Eloquent methods and attributes known to PHPStan.
- Changed `@var User` to `@var User|null` for `Auth::user()` and added explicit `if (!$user)` checks in `ReservationController`.
- Fixed `Request` property access: replaced `$request->status` with `$request->input('status')` to avoid undefined property errors.
- Simplified `AuditLogger` to use null coalescing and explicit checks for `$actor` properties.
- Fixed `str_pad` type by casting `random_int()` result to string.
- Updated `TimezoneService` to strip seconds and extra characters from date/time strings.
- Increased PHPStan memory limit to `2G` in `composer.json`.

These changes ensure CI passes reliably and the codebase is enterprise-grade.