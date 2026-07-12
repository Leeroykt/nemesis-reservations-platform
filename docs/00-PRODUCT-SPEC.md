# NEMESIS Reservations Platform — Product Spec
### (codename: Savora Engine — the sellable, deployable core behind the Savora demo)

## 1. What we're actually building

Not a multi-tenant SaaS. Not "one platform, many restaurants sharing infra."

We're building a **golden template repository**: one enterprise-grade, fully
functional restaurant reservation & floor-management system, sitting finished
in git, that gets **cloned per client, configured, and deployed to its own
VM + its own database.**

This matches how the sale actually works:
- Client sees the *generic* demo on our infra → walkthrough in a few hours.
- Client says yes → we spin up **their own VM, their own Postgres instance**,
  clone the repo, run the seeder, point their domain at it. 72 hours, done.
- No shared database between clients. No tenant_id leakage risk. No "which
  restaurant's data is this" bugs. Every client is a clean, isolated install.
- Because it's one clean codebase per client, we can also quietly customize
  deeper (custom feature, different rules) for a bigger client later without
  touching anyone else's instance.

This is the correct architecture for our sales motion. Resist the urge to
build shared multi-tenancy — it adds complexity we don't need and doesn't
match "we deploy a VM per client."

## 2. Non-negotiable goals for this build

1. **Feature-frozen scope.** Section 5 of `02-FEATURE-SPEC.md` is the entire
   v1. If it's not written down there, it does not get built until v1 ships
   and is staged. No mid-dev feature additions — write new ideas into
   `BACKLOG.md`, don't touch running code.
2. **Config-driven white-labeling.** Swapping a client's name, logo, colors,
   address, hours, and rules must never require touching application code —
   only `.env`, one config table, and a logo file.
3. **One command to demo, one script to deploy.** `docker compose up -d`
   gets a fresh reviewer to a working, seeded demo in minutes. A single
   `./deploy.sh client-name` script gets a paying client live in hours, not
   days.
4. **Reuse the Savora UI.** The dashboard, booking page, and login screens
   already designed and built (dark/gold, Bootstrap 5 + vanilla JS + Chart.js)
   are a real asset — a client has already reacted well to them. We are not
   rewriting the frontend in React. We are wiring the existing frontend to a
   real backend and database.

## 3. Tech stack (locked) — v2, revised for SPA + testability

| Layer | Choice | Why |
|---|---|---|
| Backend framework | **Laravel 11** | Already deep expertise here (PONDAE, #SaveAMan). Fast to ship, huge ecosystem, Sanctum handles auth cleanly. |
| Frontend/SPA layer | **Inertia.js + React + TypeScript**, scaffolded via **Laravel Breeze's `react --typescript --pest` stack** | Real SPA feel (client-side routing, no full reloads) without hand-building or maintaining a fully decoupled REST frontend. Laravel controllers return Inertia responses straight from Eloquent — one deployable app, not two. Matches your real production experience on PONDAE (Laravel 11 + Inertia + React). |
| Auth | **Laravel Sanctum**, cookie/session based (SPA auth pattern), scaffolded by Breeze | Real hashed passwords, real sessions, real role checks server-side (not the current client-side-only role toggle). |
| Database | **PostgreSQL 16** | Matches PONDAE, strong for relational integrity + future geo/analytics needs. |
| Admin/back-office | Custom Inertia/React dashboard (rebuilding the existing Savora UI as components), **not Filament** for this product | Filament is great for PONDAE's internal admin, but Savora's dashboard is client-facing polish — keep the bespoke design, rebuild the behavior layer as testable components. |
| UI/styling | Bootstrap 5.3 CSS (existing `style.css`, unchanged, still var()-driven for white-label theming) rendered through React components/JSX instead of `innerHTML` template strings | **No design work is lost** — same markup, same classes, same look. What changes is state management and re-rendering, not the visual product the client already reacted well to. |
| Charts | Chart.js via `react-chartjs-2` wrapper | Keeps the exact chart configs already built in `charts.js`, now as a reusable typed component instead of a global registry object. |
| PDF/CSV export | `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` (CSV/XLSX) — server-generated | Server-side export is more reliable and secure than client-side jsPDF against data the browser may not fully have (e.g. filtered audit log). |
| Testing | **Pest** (backend feature/unit tests) + **Vitest + React Testing Library** (frontend component tests) | This is what makes "test module by module" real — each Inertia page/component ships with its own test file from day one, not bolted on at the end. |
| Email | Laravel Mail (SMTP, e.g. via a transactional provider — Resend / Postmark / SES) | Replaces the client-side EmailJS hack. Real server-side email, no exposed public keys, real deliverability. |
| Realtime (v2, not v1) | Laravel Reverb (WebSockets) for live notification push | Deferred — see roadmap Phase 7+. v1 uses polling/refresh. |
| Code quality | **Laravel Pint** (formatting) + **Larastan** (static analysis) on the backend; ESLint + TypeScript compiler on the frontend | Non-negotiable for anything called "enterprise grade" — catches whole classes of bugs before they reach a client VM. |
| Containerization | Docker + Docker Compose, **Caddy** as reverse proxy (matches PONDAE pattern) | Repeatable per-client deployment, automatic HTTPS via Caddy. |
| CI | GitHub Actions — Pint, Larastan, Pest, Vitest, build, on every push | Catch breakage before it reaches a client VM. |

**Where a traditional REST API is still used:** the public booking endpoint
(`/api/v1/public/reservations`, unauthenticated, rate-limited) is a real
JSON API — it needs to be callable independent of the Inertia dashboard,
and is the natural seam for any future integration (a client's own website
widget, a future mobile app). Everything *inside* the authenticated
dashboard talks Inertia, not a hand-maintained REST layer.

## 4. White-label strategy — how "72 hours to customize" actually works

Every client instance is driven by:

1. **`.env`** — `APP_NAME`, `RESTAURANT_TIMEZONE`, `RESTAURANT_CURRENCY`,
   mail credentials, DB credentials, `PRIMARY_COLOR_HEX`, `LOGO_PATH`.
2. **`restaurants` table, single row per instance** — name, tagline, email,
   phone, address, seats, tables count. Editable from Settings in the
   dashboard once live (owner role only) — so after we hand off, the client
   can update their own hours/rules without calling us.
3. **`client-config/` folder** (see `03-PROJECT-STRUCTURE.md`) — holds the
   client's logo asset, an optional custom favicon, and a `seed-config.json`
   that the database seeder reads on first deploy to pre-populate
   restaurant info, opening hours, table layout, and rule defaults.
4. **One CSS custom-property block** (`--gold`, `--emerald`, etc. already
   exist in `style.css`) — repoint `--gold` to the client's brand color and
   the whole UI re-themes. No component-level color hardcoding is allowed
   in v1 CSS — this is a hard rule for whoever touches `style.css`.

**The 72-hour clock, concretely:**
- Hour 0–4: deposit received, spin up VM, `git clone`, fill `.env` +
  `seed-config.json` with the client's real info, drop in their logo.
- Hour 4–24: `./deploy.sh`, migrate + seed, smoke test against the
  `05-QA-CHECKLIST.md`.
- Hour 24–48: client walkthrough on their own staged instance, collect
  final tweaks (table layout, house rules, hours).
- Hour 48–72: DNS cutover, SSL live via Caddy, handoff + training.

## 5. Explicit non-goals for v1 (freeze scope here — see `BACKLOG.md` for later)

Do **not** build these until v1 is sold, deployed at least once, and stable:

- Online payments / deposits
- SMS reminders
- Multi-location support (one restaurant per instance is the model)
- POS / kitchen display integration
- Native mobile app
- Drag-and-drop floor plan editor (v1 floor plan is view-only, matches demo)
- Multi-language UI
- Public API for third parties
- Realtime websocket push (polling is fine for v1)

Anything a client asks for outside this list during a pitch: "noted, that's
a fast follow-on customization after launch" — do not let it block the
staged repo.
