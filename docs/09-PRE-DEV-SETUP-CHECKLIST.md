# Pre-Development Setup Checklist & Critical Decision Points

You have comprehensive documentation. This file is your "before you write a line of code" checklist.

**Estimated time to complete:** 1–2 hours. **Non-negotiable time investment.**

If you skip this and start coding, you will lose 3+ days mid-dev to refactoring decisions you should have made now.

---

## Phase 0 prerequisites (do these before touching `composer` or `npm`)

### A. Architectural decisions (lock these in writing)

- [ ] **State management:** Read §A in `07-POTENTIAL-KILLERS.md`. Document your approach:
  ```markdown
  State Management Pattern (Savora v1):
  
  - Server-sourced data (reservations, customers, tables):
    Use Inertia's usePage() + refetch on user action.
    No useState() for this.
  
  - Component UI state (modal open? form dirty?):
    Use React useState(). This is fine.
  
  - Cross-page app state (current user, theme):
    Use Inertia's props + React context (or just re-fetch).
  
  - Polling strategy:
    * Overview: refresh on mount, no polling
    * Reservations: poll every 30s if page is active
    * Notifications: poll every 10s (or websocket in v2)
  ```
  Save this to `docs/09a-STATE-MANAGEMENT.md`.

- [ ] **API response shape:** Read §B in `07-POTENTIAL-KILLERS.md`. Lock down:
  ```json
  List endpoints return:
  {
    "data": [...],
    "meta": { "total": 100, "page": 1, "perPage": 25, "hasMore": true }
  }
  
  Single resource endpoints return:
  {
    "data": { "id": 1, ... }
  }
  
  Error responses return:
  {
    "message": "Validation failed",
    "errors": { "email": ["Email is required"] }
  }
  ```
  Save this to `docs/09b-API-CONTRACTS.md`.

- [ ] **Form validation split:** Read §F in `07-POTENTIAL-KILLERS.md`. Decide:
  - [ ] Backend is source of truth (always)
  - [ ] Frontend validation: show as user types (blur) or only after submit?
  - [ ] Create `VALIDATION_RULES` constant in frontend for client-side mirrors
  - [ ] Document in `docs/09c-FORM-VALIDATION.md`

- [ ] **Date/timezone handling:** Read §C in `07-POTENTIAL-KILLERS.md`. Decide:
  - [ ] All DB times stored in UTC
  - [ ] Reservation times are `DATE + TIME` (interpreted vs restaurant timezone)
  - [ ] Conflict detection always converts to UTC before querying
  - [ ] Frontend always formats against `restaurant.timezone`
  - [ ] Document with examples in `docs/09d-TIMEZONE-HANDLING.md`

- [ ] **Soft delete scope:** Read §G in `07-POTENTIAL-KILLERS.md`. Decide:
  - [ ] Which tables get soft deletes? (Reservation, Customer, Table only)
  - [ ] Default behavior: withoutTrashed() or withTrashed()?
  - [ ] Audit log: include soft-deleted records? (probably yes)
  - [ ] Analytics: include or exclude? (decide per query, document)
  - [ ] Document in `docs/09e-SOFT-DELETE-STRATEGY.md`

- [ ] **Role-based access control:** Read §H in `07-POTENTIAL-KILLERS.md`. Lock down:
  - [ ] Every endpoint has a required role
  - [ ] Use Policies + middleware, never just UI checks
  - [ ] Write `RoleAccessTest.php` that verifies every endpoint
  - [ ] Document role matrix in `docs/09f-ROLE-MATRIX.md`

### B. Environment & configuration

- [ ] **`.env.example` is complete:** Read §D in `07-POTENTIAL-KILLERS.md`.
  - [ ] Every variable used in code is listed in `.env.example`
  - [ ] Every value is realistic (not placeholder garbage)
  - [ ] Variables differ per client are marked with `# VARIES PER CLIENT`
  - [ ] Create `app/Http/Middleware/EnforceConfigValidation.php` that fails fast if required vars are missing

- [ ] **Config files instead of env() calls:** Read §D in `07-POTENTIAL-KILLERS.md`.
  - [ ] Create `config/restaurant.php` with all restaurant-specific values
  - [ ] Use `config('restaurant.timezone')` in code, not `env()`
  - [ ] Document in `docs/09g-CONFIGURATION.md`

### C. Type safety

- [ ] **TypeScript strategy locked:** Read §E in `07-POTENTIAL-KILLERS.md`.
  - [ ] Decision: auto-generate types from backend OR manually sync in `types/api.d.ts`
  - [ ] Every Inertia page has explicit `InertiaProps<T>` typing
  - [ ] Enum equivalents for status/role/tone (no string literals)
  - [ ] Document in `docs/09h-TYPESCRIPT-STRATEGY.md`

### D. Testing & monitoring

- [ ] **Error monitoring plan:** Read §L in `07-POTENTIAL-KILLERS.md`.
  - [ ] Sentry vs Bugsnag vs self-hosted? (decide)
  - [ ] Email send failures logged
  - [ ] Database connection failures logged
  - [ ] Rate limit breaches logged
  - [ ] Document in `.env.example` as optional vars

- [ ] **Logging strategy:** Read §L in `07-POTENTIAL-KILLERS.md`.
  - [ ] Which events are "critical"? (email fail, auth fail, quota hit)
  - [ ] Which are "warning"? (slow query, validation fail)
  - [ ] Activity log captures all mutations
  - [ ] Owner-only "error logs" page shows critical errors from last 7 days

- [ ] **Backup plan:** Read §M in `07-POTENTIAL-KILLERS.md`.
  - [ ] Automated daily `pg_dump` to disk + S3
  - [ ] Restore tested monthly
  - [ ] Runbook documents exact restore procedure
  - [ ] Document in `docker/backup-restore.md`

### E. Database & schema

- [ ] **Schema reviewed:** Read `01-DATABASE-SCHEMA.md` and `schema.sql`.
  - [ ] Every table documented with purpose
  - [ ] Indexes planned (conflict detection needs `(restaurant_id, date, table_id)`)
  - [ ] Soft deletes on Reservation, Customer, Table only
  - [ ] Every migration is reversible (never use destructive operations)

- [ ] **Migration safety rules locked:** Read §P in `07-POTENTIAL-KILLERS.md`.
  - [ ] All migrations idempotent (can run multiple times)
  - [ ] Test every migration on a data clone before shipping
  - [ ] No dropping columns/tables without deprecation period
  - [ ] Schema verification test on CI

### F. Deployment & infrastructure

- [ ] **Docker setup tested locally:** Read §S in `07-POTENTIAL-KILLERS.md`.
  - [ ] `docker-compose.yml` is production-ready (not dev-only)
  - [ ] Dockerfile has pinned versions (no `latest`)
  - [ ] `docker compose up -d` on a clean machine works
  - [ ] Builds on target architecture (Linux AMD64, not ARM Mac)

- [ ] **Deploy script stubbed:** (Phase 7 in roadmap)
  - [ ] `deploy.sh <client-slug>` placeholder created
  - [ ] Documented: what it does, what it requires

### G. UI/Design consistency

- [ ] **Design System docs reviewed:** Read `08-UI-DESIGN-SYSTEM.md` in full.
  - [ ] Printed out or open in a tab (you'll reference it constantly)
  - [ ] Color palette memorized (`--gold`, `--emerald`, `--rust`, `--slate`)
  - [ ] Component patterns noted (buttons, forms, modals, badges)
  - [ ] "Forbidden practices" list printed and on desk
  - [ ] Created `resources/css/app.css` that imports `style.css` unchanged

- [ ] **Brand colors CSS lock:** Read §A in `08-UI-DESIGN-SYSTEM.md`.
  - [ ] CSS variables defined in `:root`
  - [ ] Light mode overrides in `[data-theme="light"]`
  - [ ] No hardcoded colors anywhere in code
  - [ ] Color names used in semantically (rust = danger, not rust = pretty)

### H. Documentation

- [ ] **Docs folder structure created:**
  ```
  docs/
  ├── 00-PRODUCT-SPEC.md (given)
  ├── 01-DATABASE-SCHEMA.md (given)
  ├── 02-FEATURE-SPEC.md (given)
  ├── 03-PROJECT-STRUCTURE.md (given)
  ├── 04-ROADMAP.md (given)
  ├── 05-QA-CHECKLIST.md (given)
  ├── 06-CLIENT-ONBOARDING-RUNBOOK.md (given)
  ├── 07-POTENTIAL-KILLERS.md (NEW)
  ├── 08-UI-DESIGN-SYSTEM.md (NEW)
  ├── 09-PRE-DEV-SETUP-CHECKLIST.md (NEW — this file)
  ├── 09a-STATE-MANAGEMENT.md (create now)
  ├── 09b-API-CONTRACTS.md (create now)
  ├── 09c-FORM-VALIDATION.md (create now)
  ├── 09d-TIMEZONE-HANDLING.md (create now)
  ├── 09e-SOFT-DELETE-STRATEGY.md (create now)
  ├── 09f-ROLE-MATRIX.md (create now)
  ├── 09g-CONFIGURATION.md (create now)
  ├── 09h-TYPESCRIPT-STRATEGY.md (create now)
  └── BACKLOG.md (given)
  ```

- [ ] **README.md updated:** Points to `docs/` folder, reading order: `00 → 02 → 05 → 09`

### I. Git & CI/CD

- [ ] **`.gitignore` locked:**
  - [ ] `/node_modules`, `/vendor`, `/storage`
  - [ ] `.env` (committed: `.env.example` only)
  - [ ] `/public/build` (Vite output)
  - [ ] `client-config/` (per-instance, not committed)

- [ ] **GitHub Actions CI stubbed:** (Phase 0)
  - [ ] Linting: `laravel-pint` (backend), ESLint (frontend)
  - [ ] Static analysis: `larastan` (backend)
  - [ ] Tests: `pest` (backend), `vitest` (frontend)
  - [ ] Build: verify `npm run build` passes
  - [ ] On every `push` to `main`, run all checks

- [ ] **Branch strategy decided:**
  - [ ] `main` = production-ready, tests must pass
  - [ ] `develop` = staging, merge from feature branches
  - [ ] Feature branches: `feature/auth`, `feature/reservations`, etc.

---

## Vibe coding gotchas (anti-patterns to recognize and stop)

Before you start building, print this out. If you catch yourself doing these, stop and check the docs.

### 1. State management drift
**Symptom:** "I'll handle this component's data one way, and that component's data another way."
**Fix:** Read `09a-STATE-MANAGEMENT.md` right before you build the first page.

### 2. Validation rules scattered
**Symptom:** Form validation logic in the component, different rules in the backend, different again in a validator service.
**Fix:** Create `VALIDATION_RULES` constant, import everywhere. Backend has the real rules in FormRequest. Frontend mirrors them.

### 3. API response shape inconsistency
**Symptom:** Controller A returns `{ data, meta }`, Controller B returns `{ items, count }`. Frontend has two parsing patterns.
**Fix:** Create an abstract `BaseResource` class, every controller uses it. One shape, one parser.

### 4. Timezone goof
**Symptom:** Reservation says "tomorrow 19:00" in the UI but the DB shows "tomorrow 21:00". Conflict detection is wrong.
**Fix:** Read `09d-TIMEZONE-HANDLING.md` before you touch date queries.

### 5. Soft delete confusion
**Symptom:** Analytics query includes deleted customers. Audit log is missing events. Dropdowns show deleted options.
**Fix:** Document which tables get soft deletes, which queries use `withoutTrashed()` by default.

### 6. Permission bypass via UI
**Symptom:** "I'll hide the delete button if role is host, that's good enough."
**Fix:** No. Backend enforces permissions. UI hide is convenience. Write `RoleAccessTest.php` that proves it.

### 7. Button states inconsistent
**Symptom:** Some buttons disable while loading, others don't. Users double-click, data duplicates.
**Fix:** Build one `<LoadingButton>` component, all forms use it.

### 8. Color hardcoding
**Symptom:** `.error-red { color: #FF0000; }` in a component. Doesn't respect theme toggle.
**Fix:** Never hardcode colors. Use CSS variables: `color: var(--rust);`

### 9. Font sizes everywhere
**Symptom:** `.hero-heading { font-size: 47px; }`, `.section-title { font-size: 39px; }`, `.card-title { font-size: 20px; }`. No system.
**Fix:** Use only: h1–h6 and body/text-sm/text-xs. No arbitrary sizes.

### 10. Spacing chaos
**Symptom:** `padding: 17px 23px 12px 8px`. Doesn't match any scale.
**Fix:** Use Bootstrap spacing: `.p-3`, `.mb-4`. Pick from the scale, don't invent.

### 11. Notification/polling spam
**Symptom:** All pages poll every 5 seconds. 20 users × 12 polls/minute = 240 req/min killing the DB.
**Fix:** Lock down polling strategy before you build. Most pages don't need live updates.

### 12. Testing skipped
**Symptom:** "I'll write tests after the feature works." Feature works, never write tests.
**Fix:** Write tests as you build. Or at least *plan* them before you code.

### 13. Logging/monitoring forgotten
**Symptom:** Email send fails silently. No error logged. Client complains a week later.
**Fix:** Log every error from day one. Wire up Sentry on day one.

### 14. Migration mismanagement
**Symptom:** You write a migration that works locally but fails in production because the data is different.
**Fix:** Migrations are idempotent. Test on a data clone.

### 15. Docker mismatch
**Symptom:** "Works on my machine." Deploy to production, whole thing breaks.
**Fix:** Test `docker-compose.yml` and Dockerfile end-to-end on target hardware before Phase 0 ends.

---

## Final checklist (day before Phase 0 starts)

- [ ] All 09a–09h docs created and reviewed
- [ ] `.env.example` filled in completely
- [ ] `config/` structure planned (which vars in `.env` vs config files)
- [ ] Database schema (`schema.sql`) understood
- [ ] `08-UI-DESIGN-SYSTEM.md` printed or open in a tab
- [ ] Git structure and GitHub Actions CI skeleton ready
- [ ] Docker/Dockerfile tested locally
- [ ] Decided: state management, API response shape, form validation, timezone handling, soft deletes, roles
- [ ] Decided: error monitoring, logging, backups
- [ ] Decided: TypeScript strategy, testing approach
- [ ] Runbook (`06-CLIENT-ONBOARDING-RUNBOOK.md`) read and understood
- [ ] Roadmap (`04-ROADMAP.md`) phases understood — you're starting Phase 0
- [ ] 15 "vibe coding gotchas" understood — printed out
- [ ] Timezone test case written (create res in one TZ, verify in another)
- [ ] Role access test template written (proof that backend enforces, not UI)

---

## Reading order (if starting fresh)

1. `00-PRODUCT-SPEC.md` (15 min) — understand the business
2. `02-FEATURE-SPEC.md` (15 min) — understand v1 scope
3. `04-ROADMAP.md` (10 min) — understand phase structure
4. `03-PROJECT-STRUCTURE.md` (15 min) — understand repo layout
5. `01-DATABASE-SCHEMA.md` (20 min) — understand data model
6. `05-QA-CHECKLIST.md` (10 min) — understand what passing looks like
7. `07-POTENTIAL-KILLERS.md` (30 min) — understand what breaks dev
8. `08-UI-DESIGN-SYSTEM.md` (45 min) — internalize UI patterns
9. `09-PRE-DEV-SETUP-CHECKLIST.md` (this file, 30 min) — make decisions
10. Create `09a–09h` decision docs (1 hour)
11. Start Phase 0 ✅

**Total: ~3.5 hours. Worth every minute.**

---

## How to use these docs while coding

- `08-UI-DESIGN-SYSTEM.md` — open in a browser tab, reference constantly
- `07-POTENTIAL-KILLERS.md` — read the relevant section before touching a new feature
- `09a–09h` decision docs — refer when unsure about pattern
- `04-ROADMAP.md` — check off phases as you complete them
- `02-FEATURE-SPEC.md` — verify you're building exactly what's listed
- `05-QA-CHECKLIST.md` — run before declaring a phase done

---

## When you're tempted to vibe code

**Ask yourself:**

1. Is this in `02-FEATURE-SPEC.md`? If no, add to `BACKLOG.md`, don't build it.
2. Is there a pattern for this in `08-UI-DESIGN-SYSTEM.md`? If no, add it to the doc first, then use it everywhere.
3. Does this touch state management? Check `09a-STATE-MANAGEMENT.md`.
4. Does this touch permissions? Check `09f-ROLE-MATRIX.md`.
5. Does this touch dates? Check `09d-TIMEZONE-HANDLING.md`.
6. Is this a "I'll do it differently to be clever"? Stop. Use the documented pattern.

**If unsure, re-read the relevant doc. Docs are faster than refactoring.**

---

**Good luck. You've got this. The plan is solid. Follow it, and you ship in ~2 weeks.**
