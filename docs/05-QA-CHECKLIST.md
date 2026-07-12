# QA Checklist

Run this in full before tagging `v1.0.0`, and the shortened "per-client"
subset (marked ✅) before every handoff.

## Auth & roles
- [ ] Wrong password rejected, right password logs in. ✅
- [ ] Host role cannot reach `/settings`, `/audit`, `/customers`, `/analytics` (403, not just hidden UI). ✅
- [ ] Manager role cannot reach `/settings`, `/audit`.
- [ ] Session expires/logout actually invalidates server-side.

## Reservations
- [ ] Double-booking the same table/overlapping time is rejected. ✅
- [ ] Party size over table capacity is rejected. ✅
- [ ] Party size over `max_party_size` rule is rejected.
- [ ] Cancelled reservations don't block new bookings on that slot.
- [ ] Bulk confirm/cancel/delete only available to correct roles. ✅
- [ ] CSV and PDF export match on-screen filtered data.

## Public booking
- [ ] Submitting with all required fields creates a reservation and shows
      confirmation. ✅
- [ ] Submitting with no available table shows a clear error, no crash.
- [ ] Confirmation email actually arrives (check spam too). ✅
- [ ] Rapid repeated submissions are throttled (no spam-booking a table).
- [ ] XSS payloads in name/phone/notes fields render as inert text
      everywhere they're displayed (dashboard modal, customer profile,
      exports). ✅

## Settings / white-label
- [ ] Changing primary color updates the whole UI, not just one component.
- [ ] Uploading a logo replaces it everywhere (login, dashboard sidebar,
      public booking page). ✅
- [ ] Editing hours/rules takes effect immediately on new bookings.

## Data integrity
- [ ] Deleting a reservation/customer/table soft-deletes, doesn't hard-fail
      any report that referenced it historically.
- [ ] Audit log has an entry for every mutation performed during this test
      pass — spot check at the end.

## Infra
- [ ] Fresh VM → `deploy.sh` → working HTTPS instance, timed. ✅
- [ ] Backups running (check the actual `pg_dump` cron/log, don't assume).
- [ ] `.env` for this client has no leftover values from `.env.example`
      (SMTP, DB creds, app key — regenerated per instance). ✅
- [ ] Error monitoring receiving events (trigger one on purpose to check).

## Cross-browser / device (client-facing pages especially)
- [ ] Public booking page on a real phone (not just devtools responsive
      mode) — Safari iOS and Chrome Android at minimum. ✅
- [ ] Dashboard on the client's actual staff hardware if known (older
      tablets are common in restaurants).
