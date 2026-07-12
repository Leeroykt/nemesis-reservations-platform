# Development Roadmap

One direction only: each phase is a hard prerequisite for the next. No
phase starts before the previous one passes its "done" checklist. If you
find yourself wanting to jump ahead ("let me just add X while I'm in this
file") — write it in `BACKLOG.md` and keep moving.

Estimates assume solo dev (you), working focused sessions — treat them as
ballpark, not deadlines.

---

## Phase 0 — Repo & environment (½ day)
- [ ] Fresh Laravel 11 project, PostgreSQL configured locally via Docker.
- [ ] `docker-compose.yml` (app + postgres + caddy) boots clean.
- [ ] `.env.example` filled with every variable the app will ever need
      (even ones not used yet) so future-you never has to guess.
- [ ] CI pipeline stub (GitHub Actions): runs `php artisan test` on push.
- [ ] `docs/` folder committed (this set of documents) — first commit.

**Done when:** `docker compose up -d` on a clean machine gives you a
running Laravel welcome page against a real Postgres container.

## Phase 1 — Database & models (1–1.5 days)
- [ ] Every migration from `01-DATABASE-SCHEMA.md`, in dependency order.
- [ ] Every Eloquent model + relationships, no business logic in models yet.
- [ ] `DemoSeeder` reproduces the exact dataset currently in `data.js`
      (same names, same reservations) — this is your regression reference:
      if the seeded dashboard doesn't visually match the current static
      demo, something's wrong.

**Done when:** `php artisan migrate:fresh --seed` gives you a database that,
if you queried it by hand, matches `data.js` 1:1.

## Phase 2 — Auth & role enforcement (½–1 day)
- [ ] Sanctum SPA auth, login/logout/me endpoints.
- [ ] `EnsureRole` middleware, applied per-route per `02-FEATURE-SPEC.md`'s
      role column.
- [ ] `RoleAccessTest.php` — automated test that a `host` token gets 403 on
      every `manager`/`owner` route, and a `manager` token gets 403 on every
      `owner` route. This test is the thing that makes role gating real
      instead of decorative.

**Done when:** the test suite proves role gating server-side, not just in
the UI.

## Phase 3 — Reservation engine (core, 2–3 days)
- [ ] `ReservationService`: conflict detection, party-size/rule validation
      — ported from `app.js`'s `findConflict`/`validateReservation`, now
      the single source of truth used by *both* the dashboard and the
      public booking endpoint.
- [ ] Reservation CRUD + bulk actions API.
- [ ] `AuditLogger` wired so every mutation writes an activity/audit row.
- [ ] Feature tests covering: double-booking rejected, over-capacity
      rejected, over-max-party rejected, cancel/delete/bulk actions.

**Done when:** you can drive the entire reservations lifecycle through
API calls (Postman/curl) with no frontend involved yet, and the test suite
proves the business rules can't be bypassed.

## Phase 4 — Wire the existing dashboard UI to the API (2–3 days)
- [ ] Build `api.js` — replaces `SAVORA_DATA`/`SavoraStore` calls throughout
      `app.js` with `fetch()` calls to `/api/v1/...`.
- [ ] Go screen by screen against `02-FEATURE-SPEC.md`: Overview,
      Reservations, Calendar, Tables, Customers, Analytics — confirming
      each one renders from real API data instead of static objects.
- [ ] `guard.js` checks a real session (`/api/v1/me`) instead of
      `sessionStorage`.
- [ ] `auth.js` posts to `/api/v1/login` instead of comparing to a hardcoded
      constant.

**Done when:** the dashboard looks and behaves identically to the current
demo, but every number on screen came from Postgres, not `data.js`.

## Phase 5 — Public booking + real email (1–1.5 days)
- [ ] `PublicReservationController`, throttled, using `ReservationService`.
- [ ] `book.js` posts to the real endpoint.
- [ ] Laravel Mail replaces EmailJS entirely — `BookingConfirmed` mailable,
      rendered from the `email_templates` table so Settings' template
      editor actually controls the copy.
- [ ] Notification row created server-side on new booking (replaces
      `notifyManager()` client function).

**Done when:** submitting the public form creates a real DB row, sends a
real email via SMTP, and shows up in the dashboard notification bell
without a page refresh (poll on load is fine for v1).

## Phase 6 — Settings, roles admin, audit log (1.5–2 days)
- [ ] Settings screens (restaurant info, hours, rules, branding, email
      templates) wired to their endpoints.
- [ ] User management screen (owner-only) — invite/create/deactivate staff.
- [ ] Audit log screen — real, filterable, unbounded.
- [ ] The role-preview dropdown gets locked down per the note in
      `02-FEATURE-SPEC.md` §9 — either removed or owner-only debug toggle.

**Done when:** an owner account can fully configure a fresh instance
without touching code or the database directly.

## Phase 7 — White-label & deployment automation (1.5–2 days)
- [ ] `ClientSeeder` reads `client-config/seed-config.json`.
- [ ] Branding upload in Settings writes `logo_path` + `primary_color_hex`
      and the frontend reads them into CSS variables on load.
- [ ] `deploy.sh <client-slug>`: provisions env file, runs migrations,
      runs `ClientSeeder`, restarts containers. One command, idempotent.
- [ ] Caddyfile template auto-configures HTTPS for the client's domain.
- [ ] Dry run: spin up a *fake* second client end-to-end using this
      pipeline, timing yourself against the 72-hour promise in the Product
      Spec.

**Done when:** you've proven, with a timer running, that a second
"client" can go from empty VM to fully branded, seeded, HTTPS-live
instance inside a working day — not 72 hours of you improvising each time.

## Phase 8 — Hardening & QA pass (1–1.5 days)
- [ ] Full pass of `05-QA-CHECKLIST.md`.
- [ ] Rate limiting on all public + auth endpoints.
- [ ] Input sanitization audit (this is where the XSS-type issues found in
      the static demo get closed for real, server-side, not just patched
      in one file).
- [ ] Backups: confirm automated `pg_dump` cron on the deploy template.
- [ ] Error monitoring hooked up (e.g. Sentry free tier) so a client's
      first week doesn't surface bugs to you via a phone call.

**Done when:** you'd be comfortable putting a real client's real guest
data through this without supervising it.

## Phase 9 — Freeze, stage, sell (ongoing)
- [ ] Tag `v1.0.0` in git. This tag is what "the generic version, ready and
      staged" means going forward — every new client clones from this tag,
      not from `main`'s latest commit.
- [ ] Keep a permanently-running "generic demo" VM seeded from
      `DemoSeeder`, reset nightly via cron, for the "spin up a few hours"
      walkthrough promise — no rebuild needed, it's already up.
- [ ] `06-CLIENT-ONBOARDING-RUNBOOK.md` is the literal script you follow
      for every sale from here on. Improve the runbook, not the product,
      unless a client pays for a real v1.1.

**Done when:** selling this to restaurant #2 takes hours of configuration,
zero hours of coding.

---

## Rough total: ~12–17 focused working days to a sellable v1.

This is deliberately sequential — resist parallelizing phases or
"improving as you go." The entire point of this roadmap is that dev only
moves forward.
