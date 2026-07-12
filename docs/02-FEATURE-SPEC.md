# Feature Spec — v1 (frozen scope)

Every module below is a 1:1 port of what already works in the Savora demo,
now backed by real auth, a real database, and real email — not new product
thinking. **This document is the entire v1.** If a feature isn't listed
here, it's `BACKLOG.md`, not this sprint.

Each module lists: what it does, the API endpoints it needs, and the role
required to use it (`host` < `manager` < `owner`, same hierarchy as today).

---

## 1. Auth
- Session-based login (email + password), real bcrypt hashing.
- Logout, "remember me".
- Server-enforced role on every protected route (not client-side toggle —
  the current role-switcher becomes a **dev/demo-only** feature, see §9).

`POST /api/v1/login` · `POST /api/v1/logout` · `GET /api/v1/me`

## 2. Overview (dashboard home) — role: host+
- KPI cards: today's reservations, upcoming, tables available/occupied,
  walk-ins, today's revenue — computed from real `reservations`/`tables`
  rows, not static numbers.
- Revenue trend chart (last 7 days, real data).
- Reservations-by-status doughnut (real data).
- Today's reservation list, recent activity feed (last 12).

`GET /api/v1/dashboard/kpis` · `GET /api/v1/dashboard/revenue-trend` ·
`GET /api/v1/dashboard/status-breakdown` · `GET /api/v1/activity?limit=12`

## 3. Reservations — role: host+ (delete: manager+)
- List with filters (status tabs), search (guest name/table), pagination.
- Bulk select → confirm / cancel / delete (delete gated manager+).
- Create, view, edit, cancel, delete single reservation.
- Table-conflict + party-size validation server-side (port of
  `findConflict`/`validateReservation` logic from `app.js` into a
  `ReservationService` class — same rules, now unbypassable from devtools).
- CSV export, PDF export (server-generates via `barryvdh/laravel-dompdf`
  or keep client-side jsPDF against the fetched page of data — decide at
  build time, either is fine).

`GET /api/v1/reservations` · `POST /api/v1/reservations` ·
`GET /api/v1/reservations/{id}` · `PATCH /api/v1/reservations/{id}` ·
`DELETE /api/v1/reservations/{id}` (manager+) ·
`POST /api/v1/reservations/bulk` (`action: confirm|cancel|delete`)

## 4. Calendar — role: host+
- Month view (chips per day, click to see day detail).
- Week view (hourly grid).
- Both driven by the same `/reservations` query, filtered by date range —
  no separate endpoint needed, just date-ranged fetches.

## 5. Tables / floor plan — role: host+
- Visual floor plan (view-only in v1 — no drag rearranging, matches
  current demo).
- Click table → see its today's reservations, quick "new reservation for
  this table" shortcut.
- Table status (`Available`/`Occupied`/`Reserved`/`Cleaning`) editable by
  host+ directly from the floor plan (this is a *live floor status* flag,
  independent of reservation status).

`GET /api/v1/tables` · `PATCH /api/v1/tables/{id}/status`

## 6. Customers (guest CRM) — role: manager+
- Searchable list, VIP flag, visit count, lifetime spend, preferences.
- Profile modal: full history of that guest's reservations.
- VIP auto-flagging rule (e.g. 10+ completed visits) runs as a scheduled
  job, not inline on every request.

`GET /api/v1/customers` · `GET /api/v1/customers/{id}`

## 7. Analytics — role: manager+
- Peak hours, popular tables, customer growth (new vs returning),
  reservations-by-status — same charts as the demo, now querying real
  aggregated data (use DB `GROUP BY`, not in-PHP aggregation, once volume
  grows).

`GET /api/v1/analytics/peak-hours` · `GET /api/v1/analytics/popular-tables`
· `GET /api/v1/analytics/customer-growth`

## 8. Settings — role: owner only
- Restaurant info (name, tagline, contact, address) — edits `restaurants`.
- Opening hours editor — edits `restaurant_hours`.
- Booking rules (max party, slot length, buffer, cancellation window,
  deposit threshold) — edits `restaurant_rules`.
- Email template editor (subject/body per template key).
- Branding: logo upload, primary color picker (writes `logo_path`,
  `primary_color_hex` on `restaurants`; frontend re-reads these into the
  `--gold` CSS variable on load — this is the in-product half of the
  white-label story from `00-PRODUCT-SPEC.md`).

`GET/PATCH /api/v1/settings/restaurant` · `GET/PATCH /api/v1/settings/hours`
· `GET/PATCH /api/v1/settings/rules` · `GET/PATCH /api/v1/settings/branding`
· `GET/PATCH /api/v1/settings/email-templates/{key}`

## 9. Roles & access — role: owner only
- User management: invite/create staff accounts, assign role, deactivate.
- **Note:** the current "viewing as Owner/Manager/Host" dropdown in the
  topbar is a demo convenience for *sales walkthroughs only*. In a live
  client instance it is either removed entirely or gated behind a
  `owner`-only "preview as role" debug toggle — it must never be a way to
  escalate your own real permissions.

`GET /api/v1/users` · `POST /api/v1/users` · `PATCH /api/v1/users/{id}` ·
`DELETE /api/v1/users/{id}`

## 10. Audit log — role: owner only
- Full, unbounded, append-only log of every write action, who did it, when.
- Filter by date range / actor / entity type.

`GET /api/v1/audit-log`

## 11. Public booking page (no auth)
- Guest-facing form (name, phone, date, time, party, optional email/notes).
- Server-side validation identical to the dashboard's reservation rules
  (single source of truth — `ReservationService`, not duplicated logic).
- Auto table assignment (first available table that fits, no conflict).
- Confirmation screen with booking reference.
- Confirmation email sent server-side via Laravel Mail (replaces EmailJS).
- Rate-limited (throttle middleware) to prevent spam bookings.

`POST /api/v1/public/reservations`

## 12. Notifications (staff bell) — role: host+
- New website bookings, cancellations, VIP arrivals push a notification
  row. v1 = polled on page load + every 60s. Realtime push is a
  post-v1 upgrade (Reverb), not required for launch.

`GET /api/v1/notifications` · `PATCH /api/v1/notifications/mark-all-read`

## 13. Theming
- Dark/light toggle — client-side only, unchanged from current `theme.js`.

---

## Explicitly not in this document
See `00-PRODUCT-SPEC.md` §5 for the non-goals list. If a client asks for
something not covered above during a demo, log it in `BACKLOG.md` with the
client's name — don't reshape v1 around one request.
