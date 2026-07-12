# Potential Killers — Technical & Process Risks

Things that will derail you mid-dev if not locked down NOW, before Phase 0 starts.
Think of this as "things that can't be fixed by just adding more code." Some are
architectural (wrong decision early = rewrite later), some are process (no backup
plan = disaster when it breaks).

---

## A. Frontend state management & component sync

**The killer:** You build Overview with one state pattern, Reservations with
another, Tables with a third. Six weeks in, a notification arrives + a
state update + a user action happens at the same time — which one wins?
Nobody knows. Components re-render randomly. You spend three days tracing
why the table list shows stale data after a cancel.

**Lock this down NOW:**
- [ ] Decide on the Inertia state model: Does *every* page refresh data on
      mount? Do some pages poll? Do some pages listen to Inertia's `usePage()`
      hook for "fresh data from the server"? Document it *per page*.
  Example: "Reservations page polls `/api/v1/reservations` on mount + every
  30s. If user takes an action (create/delete), re-fetch immediately, don't
  wait for poll. Optimistic UI updates only on deletions that can't fail."
- [ ] Pick a single, project-wide pattern for "network loading state": the
      spinner, the disabled button, the toast feedback. Every component uses
      the exact same `useLoadingState()` hook if you write one, or the exact
      same Inertia convention if you don't.
- [ ] Form validation: Server-side validation is the source of truth. Decide
      right now: do you show server errors inline as the user types (re-fetch
      on blur), or only after submit? Mixed approaches = users confused why
      their fix didn't work.

**Reference:** Create `resources/js/hooks/usePageState.ts` and document it:
```
/**
 * usePageState — the ONLY way to manage page-level state in Savora.
 * Every page uses this or Inertia's usePage() — no useState() for data from the server.
 * Component-level UI state (modal open? button disabled?) is fine in useState().
 * Server data (reservations, customers, etc.) is NOT.
 */
```

---

## B. API response shape consistency

**The killer:** Controller A returns `{ data: [...], meta: { total: 10 } }`.
Controller B returns `{ reservations: [...], count: 10 }`. Controller C
doesn't return count at all. Frontend has three different parsing patterns.
Someone adds a filter to one endpoint, forgets the shape in another. Three
requests in your test suite start failing.

**Lock this down NOW:**
- [ ] Every list endpoint returns: `{ data: [...], meta: { total, page, perPage, hasMore } }`
- [ ] Every single-resource endpoint returns: `{ data: {...} }`
- [ ] Every error response returns: `{ message: string, errors: { field: [string] } }`
- [ ] Document this in `app/Http/Resources/BaseResource.php` (abstract class all
      resources extend) or in the API documentation before a single controller
      is written.
- [ ] Write a single `fetchApi()` utility in `resources/js/lib/api.ts` that every
      page imports. It enforces the shape. If a controller returns something
      else, the type checker screams.

**Reference:** Create `docs/07a-API-RESPONSE-SHAPES.md` with copy-paste examples.

---

## C. Date, time, timezone hell

**The killer:** Demo assumes Africa/Harare. Seeder creates a reservation for
"today at 19:00". On a client VM in UTC, that's yesterday. On a client VM
in US/Eastern, that's tomorrow. Conflict detection breaks. Reports show
"tomorrow's reservations" for yesterday's actual guests.

**Lock this down NOW:**
- [ ] All dates in the DB are stored as `DATE` (no time component) or
      `TIMESTAMP` (with timezone — always stored as UTC in Postgres).
- [ ] The restaurant's timezone is **always** read from `restaurants.timezone`
      on every request. Never assume the server's timezone or the browser's.
- [ ] Conflict detection: given a reservation's `date` + `time`, convert to
      UTC before querying. Example:
      ```php
      $utcStart = Carbon::createFromFormat('Y-m-d H:i', "$date $time", $restaurant->timezone)->utc();
      ```
- [ ] On the frontend, when displaying "7:30 PM", format it against
      `restaurant.timezone` — use `date-fns` with `tz()` or use a tiny Luxon
      wrapper.
- [ ] QA checklist item: "Create a reservation in one timezone, verify it
      shows at the right time in another timezone's export."

**Reference:** Create `app/Services/TimezoneService.php` as a static wrapper
around Carbon so every date operation is in one place, not scattered.

---

## D. Environment variable drift & initialization

**The killer:** `.env.example` says `SMTP_FROM=noreply@nemesis.co.zw`, but
the real value should be `support@signetandvine.co.zw` per client. Runbook
doesn't mention it. First client's emails come from the generic address.
Looks broken. No documentation of where this should have been configured.

**Lock this down NOW:**
- [ ] Every single env var used anywhere in the codebase must be in
      `.env.example` with a real, working default.
- [ ] Use `config/` files for anything non-trivial. Example:
      ```php
      // config/restaurant.php
      return [
          'timezone' => env('RESTAURANT_TIMEZONE', 'Africa/Harare'),
          'currency' => env('RESTAURANT_CURRENCY', 'USD'),
          'smtp_from_name' => env('MAIL_FROM_NAME', 'Savora'),
      ];
      ```
      Then reference it as `config('restaurant.timezone')`, not `env()` in models.
- [ ] Create an "env validation" command that runs on app boot:
      ```php
      if (!env('MAIL_FROM_ADDRESS')) {
          throw new \RuntimeException('MAIL_FROM_ADDRESS not set in .env');
      }
      ```
      This fails fast on a misconfigured instance, not silently.
- [ ] Document in `06-CLIENT-ONBOARDING-RUNBOOK.md` the *exact* 12 vars that
      differ per client (DB host, SMTP, logo path, restaurant name). Every
      other var is baked into the repo.

---

## E. Type safety / TypeScript drift

**The killer:** Backend Reservation model has `status: 'Upcoming' | 'Confirmed' |
'Completed' | 'Cancelled'`. Frontend has:
```typescript
type ReservationStatus = 'Upcoming' | 'Confirmed' | 'Done' | 'Abandoned';
```
You add a "Rescheduled" status on the backend. Frontend doesn't know. Somewhere
a component tries to render a Rescheduled status against an exhaustive switch
statement. Dead code, no error caught, ships to production, status renders blank.

**Lock this down NOW:**
- [ ] Either: auto-generate TypeScript types from your Laravel models using a tool
      like `laravel-typescript-transformer`, OR
- [ ] By hand, create `resources/js/types/api.d.ts` with **every** type mirrored
      from the backend. Document in comments where it comes from:
      ```typescript
      /** Mirrors app/Models/Reservation::$casts */
      export type ReservationStatus = 'Upcoming' | 'Confirmed' | 'Completed' | 'Cancelled';
      ```
- [ ] In every Inertia page, use `InertiaProps` and type the props explicitly:
      ```typescript
      export default function ReservationIndex({ reservations }: InertiaProps<{
          reservations: Reservation[];
      }>) { ... }
      ```
- [ ] Any place you write a string literal for a status/role/type, use the
      TypeScript type constant, not a string. Never `if (status === 'Upcoming')`
      directly — use an enum or const union type.

**Gotcha:** Vibe coding without types = you'll add "Rescheduled" status to the
backend and the frontend won't scream until a user hits that code path in
production.

---

## F. Form validation split-brain

**The killer:** Validation rules live in three places: HTML5 `required` + `type`,
Laravel `FormRequest` class, React component `useState` validation. Guest
books a table with a 25-char name. HTML5 allows it. React state allows it.
Backend rejects it (max 120 for a real name, or maybe 50). Frontend shows no
error because the 120 rule isn't enforced client-side. User thinks the book
failed for some other reason.

**Lock this down NOW:**
- [ ] Backend validation rules are the **single source of truth**. Period.
  Example:
  ```php
  class StoreReservationRequest extends FormRequest {
      public function rules() {
          return [
              'guest_name' => 'required|string|max:120',
              'party_size' => 'required|integer|min:1|max:14',
          ];
      }
  }
  ```
- [ ] Frontend validation is **convenience only** — shows errors faster, better UX.
  Import the rules from somewhere shared, or hardcode them in a constant:
  ```typescript
  const VALIDATION_RULES = {
      guestName: { required: true, maxLength: 120 },
      partySize: { required: true, min: 1, max: 14 },
  };
  ```
- [ ] On every form, show "Validating..." while submitting. If backend
  validation fails, display the server error, don't try to guess why.
- [ ] Create a test helper that validates a form submission against
  ReservationRequest rules server-side, then again client-side, verifies they
  match.

---

## G. Soft delete gotchas

**The killer:** A restaurant deleted a customer in 2024. Now in 2025, you run
an analytics query: `Customer::where('visits', '>', 10)->count()`. This
includes the deleted customer. Or it doesn't, if you added `->withoutTrashed()`
somewhere but not everywhere. Guest profile shows a list of past reservations,
but some are missing (the ones linked to a soft-deleted customer). Inconsistent
data appears between two screens.

**Lock this down NOW:**
- [ ] Use Laravel's `SoftDeletes` on Reservation, Customer, Table — no others.
- [ ] Decide on a **global scope rule**: By default, do queries include soft-deleted
      records or exclude them? Example: "Always exclude soft-deleted records by
      default. Audit log only includes non-deleted records. Reports only show
      non-deleted records. Owner-only view → opt-in to include deleted records
      for historical reasons."
- [ ] Document it:
  ```php
  class Reservation extends Model {
      use SoftDeletes;
      // Default: excludes soft-deleted rows (Laravel's normal behavior)
      // To include: Reservation::withTrashed()->get()
      // Deleted only: Reservation::onlyTrashed()->get()
  }
  ```
- [ ] In the analytics service, explicitly `->withoutTrashed()` (or rely on the
      default, but state it clearly):
  ```php
  public function getCustomerGrowth() {
      // Intentionally excludes deleted customers so reports don't show
      // ghost customers that were removed
      return Customer::withoutTrashed()
          ->whereBetween('created_at', [$start, $end])
          ->count();
  }
  ```

---

## H. Permission / authorization leakage

**The killer:** A host account somehow sees owner-only audit logs. A manager
sees a delete button that should only be visible to managers. On closer look,
the backend didn't check. The endpoint is `PATCH /reservations/{id}` and the
auth middleware checks `host+`, but the delete action inside only checks
`manager+`, which somehow got bypassed.

**Lock this down NOW:**
- [ ] Every controller action has explicit role-gating at the start:
  ```php
  public function destroy(Reservation $reservation) {
      $this->authorize('delete', $reservation);
      // OR
      $this->middleware('role:manager');
      // NOT "check in the UI and hope"
  }
  ```
- [ ] Write an automated test suite (`RoleAccessTest.php`) that verifies every
      single endpoint. For each role (host, manager, owner), verify:
      - host can call this endpoint: yes/no
      - manager can call this endpoint: yes/no
      - owner can call this endpoint: yes/no
      This test should fail if you add an endpoint and forget to gate it.
- [ ] Use Laravel Policies (not just middleware) for model operations:
  ```php
  class ReservationPolicy {
      public function delete(User $user, Reservation $res) {
          return $user->role === 'manager' || $user->role === 'owner';
      }
  }
  ```
  Then in the controller: `$this->authorize('delete', $reservation);`
- [ ] On the frontend, role gating is UX-only (hide/disable buttons). The
      backend enforces it. Make this explicit in code comments.

---

## I. Email deliverability & SMTP configuration

**The killer:** Confirmation emails never arrive. Client blames you. You check
the Laravel log — emails were "sent" (no error), but they went to a
misconfigured SMTP server or got flagged as spam. No bounce tracking. No
retry logic. First week of going live, guests think their bookings didn't
work because they never got the email.

**Lock this down NOW:**
- [ ] Document email configuration **exactly** in `06-CLIENT-ONBOARDING-RUNBOOK.md`:
      - Which SMTP provider is the default? (Postmark, Resend, SES, Mailgun?)
      - Does the client provide credentials or do you manage it?
      - Test procedure: send a test email, verify it arrives, check spam folder.
- [ ] Implement bounce/failure handling from day one:
  ```php
  class BookingConfirmed extends Mailable {
      public function build() {
          return $this->markdown('emails.booking-confirmed')
              ->from(config('restaurant.smtp_from'))
              ->replyTo(config('restaurant.email'));
      }
  }
  ```
  And log failures:
  ```php
  \Mail::send(new BookingConfirmed($reservation), function($message) {
      // track bounce if it fails
  });
  ```
- [ ] Email template variables (`{{guest_name}}`) must be escaped/safe:
  ```php
  'guest_name' => htmlspecialchars($reservation->guest_name),
  ```
- [ ] Add a staff page (owner-only): "Email logs" showing sent/failed emails
      from the last 30 days. Invaluable for debugging client complaints.

---

## J. Concurrent edit conflicts

**The killer:** Manager A is editing a reservation. Manager B is also editing
the same reservation. A saves first (sets status to "Confirmed"). B saves
(sets notes). Now the reservation is "Confirmed" but B's change overwrites
A's, or vice versa — it's unclear which one "wins."

**Lock this down NOW:**
- [ ] Add an `updated_at` timestamp to every editable model (Reservation,
      Customer, Table, etc.).
- [ ] On update, check: if the incoming `updated_at` from the form (sent when
      the modal opened) doesn't match the DB version, reject with a 409
      Conflict error. Force the user to reload and re-apply their changes.
  ```php
  public function update(UpdateReservationRequest $request, Reservation $res) {
      if ($res->updated_at->timestamp !== $request->get('_updated_at')) {
          return response()->json([
              'message' => 'This record was modified by someone else. Please refresh and try again.'
          ], 409);
      }
      // safe to update
  }
  ```
- [ ] Frontend: show a toast if you get a 409, reload the modal data from the
      server, tell the user "the reservation changed, your changes weren't saved."

---

## K. Session/auth edge cases

**The killer:** User is logged in on two browser tabs. Admin invalidates the
session. User keeps using the first tab, makes changes, tries to save, gets
401. The second tab is still "logged in" (no request triggered yet). User
refreshes and finds out they're logged out. Confusing.

**Lock this down NOW:**
- [ ] Decide on logout behavior: all sessions end (safest), or current session
      only?
- [ ] Add a 401 handler to the frontend that redirects to login and clears
      localStorage auth state:
  ```typescript
  if (error.status === 401) {
      window.location.href = '/login?redirect=' + window.location.pathname;
  }
  ```
- [ ] Show a "Your session has expired" toast, not a cryptic 401 error.
- [ ] Test: login on two tabs, logout from one, verify the other is prompted
      to re-login within 10s (don't wait for a request to fail).

---

## L. Logging & error monitoring strategy

**The killer:** A client's instance silently fails for an hour before anyone
notices. No logs. No monitoring. No error tracking. They report issues
post-hoc: "reservations from this morning didn't work." You have no data.

**Lock this down NOW:**
- [ ] Laravel's default logging goes to `storage/logs/laravel.log`. That's good
      for local dev. For production:
  - [ ] Set up Sentry (free tier, generous) or Bugsnag to catch exceptions and
        log errors automatically.
  - [ ] Log every failed email send, every failed database transaction, every
        rate-limit breach.
  - [ ] In the runbook: "First client gets Sentry free account linked to their
        instance. No setup needed — it just works if you use Laravel Log."
- [ ] Document what constitutes a "critical" error vs a "warning":
  - Critical: email send fails, database down, auth token invalid, hard quota
    limits hit.
  - Warning: slow query, deprecated API, minor validation error.
- [ ] Create a staff-visible page (owner-only): "Error logs" showing critical
      errors from the last 7 days. Not Sentry, but a local activity_log filtered
      to errors. Lets them know something went wrong.

---

## M. Backup & disaster recovery

**The killer:** A client's hard drive fails. No backups. Data gone. Actually,
you thought backups were running, but you never tested a restore. The backup
job has been failing silently for 3 months.

**Lock this down NOW:**
- [ ] Automated daily `pg_dump` via cron, stored to a second disk or S3.
  - Cron job runs daily at 2 AM, gzips the dump, stores locally + to S3.
  - Logs the result so you can see if it failed.
- [ ] Test restore procedure monthly: actually restore a 3-day-old backup to a
      separate instance, verify data is intact.
- [ ] Document in `06-CLIENT-ONBOARDING-RUNBOOK.md`:
  ```
  - [ ] Backup cron configured to run daily at 02:00 AM local time.
  - [ ] Verified backup lands in /backups/restaurant_*.sql.gz.
  - [ ] Tested restore: 'docker exec app pg_restore < /backups/restaurant_latest.sql.gz'
  ```

---

## N. Scaling & performance gotchas

**The killer:** A large client's restaurant has 1000+ past reservations. The
"Reservations" page loads, but takes 30 seconds. Pagination not implemented.
No indexes. The popular-tables report queries every single reservation ever
made and groups in PHP instead of the DB.

**Lock this down NOW:**
- [ ] Every list endpoint is paginated by default:
  ```php
  public function index(Request $request) {
      return ReservationResource::collection(
          Reservation::where('restaurant_id', auth()->user()->restaurant_id)
              ->orderByDesc('created_at')
              ->paginate(25)
      );
  }
  ```
- [ ] Indexes are planned with the schema (see `schema.sql` — note the composite
      indexes on `(restaurant_id, date, table_id)` for conflict detection).
- [ ] Aggregations (analytics) happen in the database, not in PHP:
  ```php
  // Right:
  $peak = DB::table('reservations')
      ->selectRaw('EXTRACT(HOUR FROM time) as hour, COUNT(*) as count')
      ->groupBy('hour')
      ->get();
  // Wrong:
  $peak = Reservation::all()->groupBy(fn($r) => $r->time->hour);
  ```
- [ ] Test endpoint performance with 1000+ records using a seed command:
  ```bash
  php artisan db:seed --class=LargeDatasetSeeder
  # Then benchmark key endpoints
  ```

---

## O. Rate limiting & abuse prevention

**The killer:** The public booking endpoint has no rate limiting. A competitor's
bot hammers it, creating fake reservations. SMTP quota gets consumed. Your app
grinds to a halt. Legitimate guests can't book.

**Lock this down NOW:**
- [ ] Rate limit the public booking endpoint:
  ```php
  Route::post('/public/reservations', [PublicReservationController::class, 'store'])
      ->middleware('throttle:5,1'); // 5 requests per minute per IP
  ```
- [ ] Rate limit login (prevent password brute-force):
  ```php
  Route::post('/login', [AuthController::class, 'login'])
      ->middleware('throttle:5,15'); // 5 attempts per 15 minutes
  ```
- [ ] Monitor rate-limit breaches in activity logs so you know if someone's
      abusing it.

---

## P. Migration & schema change safety

**The killer:** You write a migration that works locally but fails in production
because the data is different. Or you drop a column without checking if any
code still references it. Or two migrations try to create the same index, one
fails silently.

**Lock this down NOW:**
- [ ] Migrations are idempotent and can run multiple times safely:
  ```php
  Schema::table('reservations', function (Blueprint $table) {
      $table->index(['restaurant_id', 'date'])->change();
      // Use ->change() to modify, not recreate
  });
  ```
- [ ] Before a migration touches a production database, test on a clone:
  ```bash
  # Locally: dump production data
  # Then migrate, verify data integrity
  ```
- [ ] No dropping columns or tables without a deprecation period (at least one
      release where the code stops using it, then the next release drops it).
- [ ] Create a `verifySchema()` test that runs on CI — checks that the DB schema
      actually matches what the code expects.

---

## Q. Multi-timezone client data integrity

**The killer:** Client in Harare (UTC+2) books a table for "tomorrow 19:00".
You incorrectly store it as UTC 19:00 (which is actually next-day 21:00 in
Harare). A different client in UTC-5 looks at "all reservations for tomorrow"
and sees a reservation that's actually today for them.

**Lock this down NOW:**
- [ ] Design choice (document it): "All timestamps in the database are UTC.
      Restaurant times (opening hours, reservation times) are stored as
      `TIME` type (no date), and interpreted against the restaurant's timezone
      on every query."
- [ ] Example:
  ```php
  // Storing a reservation
  $reservation->date = '2026-07-11'; // just a date, no tz info
  $reservation->time = '19:30'; // just a time, no tz info
  // Interpretation: "in the context of this restaurant's timezone"
  
  // Querying for today's reservations in a client VM
  $today = now('Africa/Harare')->toDateString(); // always use restaurant tz
  $reservations = Reservation::where('date', $today)->get();
  ```
- [ ] Test: create a reservation in a non-UTC timezone, verify it shows
      correctly when the app is running in UTC.

---

## R. Accessibility (WCAG 2.1 AA)

**The killer:** Months after launch, someone tells you their staff member
is colorblind and can't tell "Confirmed" (gold) from "Upcoming" (lighter
gold). Or the dashboard is only accessible via mouse, no keyboard navigation.
Or screen reader users can't use the modal.

This isn't v1 scope per the spec, but ignoring it entirely early means
retrofitting is 10x harder.

**Lock this down NOW:**
- [ ] Document the accessibility debt: "v1 does not meet WCAG 2.1 AA. Future
      versions will add keyboard navigation, screen reader support, and
      higher-contrast color schemes. Current limitations..."
- [ ] At least:
  - [ ] Forms have `<label>` elements with `for` attributes.
  - [ ] Buttons have text labels (not just icons).
  - [ ] Color is never the only way to distinguish status (add text: "✓ Confirmed").
  - [ ] Links and buttons are keyboard-focusable.
  - [ ] Modal has `role="dialog"` and `aria-labelledby`.
- [ ] Add a Lighthouse CI check to the GitHub Actions pipeline — fails if
      accessibility score drops below 80.

---

## S. Docker & deployment reproducibility

**The killer:** Your local docker-compose.yml works great. You push to the
staging VM, the Dockerfile is slightly different, a dependency installs a
different version, and the app won't start. Or you build locally on an ARM
Mac and push to an AMD Linux server — different architecture, binary
incompatibility.

**Lock this down NOW:**
- [ ] Test your Docker setup end-to-end on the actual VM hardware before
      client deployment.
- [ ] Dockerfile explicitly pins all dependency versions:
  ```dockerfile
  FROM php:8.3-fpm-alpine
  # not "latest" or "8.3" — exactly "8.3-fpm-alpine3.19"
  RUN apk add --no-cache postgresql-client=15.5-r0
  ```
- [ ] `docker-compose.yml` in the repo is the staging/production setup, not a
      dev-only file. Dev uses the same setup locally.
- [ ] Build script checks that docker-compose.yml and Dockerfile are
      synchronized (version mismatches).

---

## T. Notification / polling strategy

**The killer:** A manager books a guest. It appears on the dashboard for them.
But another manager is staring at the screen and sees nothing new for 30
seconds (your poll interval). They think it didn't work, try again, double-books.

Or: realtime polling hits the API too hard. 20 staff members, each polling
every 10 seconds = 120 requests/minute. Your database connection pool is
exhausted.

**Lock this down NOW:**
- [ ] Document the exact polling/notification strategy before touching code:
  ```
  - Overview dashboard: poll every 60 seconds (or on-demand full-page refresh).
  - Reservations list: poll every 30 seconds if page is active (stop if tab is
    not focused).
  - Notifications bell: poll every 10 seconds OR websocket push (v2).
  - Manual refresh button always available (user-initiated, no throttle).
  ```
- [ ] Implement `requestIdleCallback()` or `useVisibility()` hook to pause
      polling when the tab is not focused — saves 90% of polling on large
      deployments.
- [ ] Add a "refresh" button to every page, clearly visible, no hidden polling
      surprise.

---

## U. Vendor lock-in gotchas

**The killer:** You bake in Stripe for payment processing. A client wants to
use their own payment processor. You've got Stripe IDs all over the
reservations table. Now you're refactoring.

Or: you use a third-party email service that goes down. No fallback SMTP.
Emails don't send for 8 hours.

**Lock this down NOW:**
- [ ] For v1, avoid external vendor dependencies for critical paths.
  - Email: own SMTP provider (Postmark, SES, or client's own) — no third-party
    SDK, just `config/mail.php`.
  - Payments: not in v1. When it comes, design so you could swap providers
    without touching the Reservation model.
  - Authentication: Laravel Sanctum, no external IdP.
- [ ] If a vendor is needed (e.g., SMS), create an abstraction layer:
  ```php
  // app/Services/SmsService.php
  interface SmsProvider {
      public function send(string $phone, string $message): bool;
  }
  class TwilioSmsProvider implements SmsProvider { ... }
  // Later: swap Twilio for another vendor without changing reservation code
  ```

---

## V. Documentation maintenance & drift

**The killer:** A feature ships. Runbook doesn't mention it. First client asks
"how do I configure X?" and the answer is "it's in Settings" but it's not
documented. You explain it 5 times, then give up and add it to the runbook
retroactively. Next client hits the same issue because they didn't ask.

**Lock this down NOW:**
- [ ] Every feature has an entry in the docs *before* merge.
  - What does it do?
  - Where in the UI is it?
  - Who can access it (role)?
  - If it's owner-configurable, which Settings page?
- [ ] Runbook is gospel: if you think a client needs to know it, it's in the
      runbook. If you explain something in a call, add it to the runbook after
      the call.
- [ ] Docs folder lives in git. No external wikis. Docs are reviewed before
      deploy like code.
- [ ] When you make a feature change, update the relevant docs *in the same
      PR*, not after.

---

## W. Common "vibe coding" mistakes to avoid

These are sneaky because they seem fine at first but compound:

1. **Button disabled states inconsistent**: Some buttons disable during
   loading, others don't. Users double-click, data duplicates.
   → Enforce a single `<LoadingButton>` component, all forms use it.

2. **Error messages scattered**: Some show in a toast, some in a modal,
   some inline in the form. User misses error, thinks action worked.
   → All form errors render inline at the top of the form.

3. **Date formatting inconsistent**: "07/11/2026" in one place, "11 Jul"
   in another, "2026-07-11" in the API response. Confusing.
   → Create a `formatDate(date, format)` utility, use everywhere.

4. **API error handling duplicated**: Copy-paste error handling in 5 places,
   one has a typo, doesn't retry. Others do. Inconsistent behavior.
   → Single `fetchApi()` wrapper, all error handling there.

5. **Validation rules hardcoded in components**: Guest name max 120, then
   somewhere you type 100, somewhere else 150. Breaks.
   → Validation rules in one place, imported everywhere.

6. **Icons used inconsistently**: Checkmark for delete, X for confirm.
   Users confused.
   → Icon convention doc: "delete = trash icon, confirm = checkmark,
      cancel = X".

7. **Color usage inconsistent**: Red for error in some places, pink in
   others. Emerald for success, teal for success. Confuses UX.
   → CSS variable convention doc (see Section X below).

8. **Modal close behavior inconsistent**: Some modals unsaved changes
   prompt, others silently close. User loses data.
   → All modals with forms show "unsaved changes" prompt on close.

9. **API versioning forgotten**: Current code is `/api/v1/...`. Someone
   adds `/api/reservations` endpoint because they forgot. Two URLs for
   same resource. Client confused.
   → All endpoints under `/api/v1/`, period. Enforced in routes/api.php.

10. **Testing skipped**: "I'll write tests after the feature works."
    Feature works, never write tests, merge anyway. Regression later.
    → Tests written before or during feature, not after. TDD-ish mindset.

---

## Summary: your pre-dev checklist

Before Phase 0 starts, you should have:
- [ ] State management strategy documented
- [ ] API response shape spec doc
- [ ] Timezone handling documented
- [ ] `.env.example` complete
- [ ] Type safety rules documented
- [ ] Form validation split decided
- [ ] Soft delete scoping rules documented
- [ ] Permission testing strategy documented
- [ ] Email configuration plan
- [ ] Concurrent edit conflict handling
- [ ] Auth edge cases thought through
- [ ] Error monitoring + logging plan
- [ ] Backup/restore tested
- [ ] Performance gotchas (indexing, pagination) documented
- [ ] Rate limiting plan
- [ ] Migration safety rules
- [ ] Timezone integrity tests defined
- [ ] Docker reproducibility tested
- [ ] Notification/polling strategy locked
- [ ] Vibe-coding mistakes list printed out and on your desk
- [ ] UI Design System documented (see next doc)

**If you skip this and just start coding, you'll be 4 weeks in, realize
the state management is a mess, and spend a week refactoring.**
