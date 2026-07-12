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