# Client Onboarding Runbook

The literal steps for every sale from `v1.0.0` onward. Follow this, don't
improvise a new process per client.

## Stage A — The demo walkthrough ("a few hours")
1. Confirm the always-on generic demo VM is healthy (resets nightly from
   `DemoSeeder` — check it reset last night if the meeting is early).
2. Walk the client through: Overview → Reservations → Calendar → Tables →
   Customers → Analytics → the public booking page (have them submit a
   real test booking and watch it land on the dashboard live — this is the
   moment that sells it).
3. Be upfront about what's demo-only vs what ships with their instance:
   the "viewing as Owner/Manager/Host" switcher is a walkthrough
   convenience, not part of their live product (real staff get real
   individual logins).

## Stage B — Close & deposit
1. Deposit received → this triggers Stage C. Don't provision infra before
   this.
2. Collect from the client during/right after the call:
   - Legal restaurant name + tagline
   - Logo file (vector or high-res PNG, transparent background ideal)
   - Brand primary color (hex if they have one, otherwise pick the closest
     match to their logo)
   - Address, phone, contact email
   - Opening hours per day
   - Table count, capacities, and zones (a rough sketch/photo of their
     floor plan is enough — you translate it into `tables` rows)
   - Any house rules that differ from defaults (max party size, slot
     length, cancellation window)
   - Domain they want to use (or subdomain of yours if they don't have one
     yet)

## Stage C — Provision (target: hour 0–4)
1. Spin up client VM.
2. `git clone` the repo at tag `v1.0.0` (never `main` — see Roadmap
   Phase 9).
3. Fill `.env` (fresh `APP_KEY`, DB credentials, SMTP credentials — every
   client gets their own, never reused).
4. Fill `client-config/seed-config.json` from Stage B's collected info.
5. Drop the logo into `client-config/`.
6. `./deploy.sh <client-slug>`.

## Stage D — Verify (target: hour 4–24)
1. Run the ✅-marked subset of `05-QA-CHECKLIST.md` against the live
   instance.
2. Create the client's real staff accounts (owner role for the
   owner/manager you're dealing with, host role for front-of-house if they
   want those set up now).
3. Send yourself a test public booking end-to-end, confirm the email
   arrives from the client's own branded sender, not a NEMESIS address.

## Stage E — Client review (target: hour 24–48)
1. Walkthrough call on their *own* staged instance (not the generic demo
   this time).
2. Collect final tweaks: floor plan corrections, hour corrections, any
   copy changes to email templates.
3. Apply tweaks directly via Settings where possible (proves to the client
   they can self-serve after handoff) — only touch the DB/code directly for
   things Settings doesn't cover yet.

## Stage F — Go live (target: hour 48–72)
1. DNS cutover to the client's domain.
2. Confirm Caddy issued a valid HTTPS cert automatically.
3. Final smoke test on the real domain (not the VM's IP/staging URL).
4. Handoff: short training session for whoever will use the dashboard
   daily, walking through Reservations + Tables at minimum.
5. Confirm backup cron is running against the *real* domain'd instance,
   not just the staging one from Stage C.

## Stage G — Post-launch
- First-week check-in call.
- Log any feature requests in `BACKLOG.md` with the client's name and
  date — don't build anything ad hoc into their instance without deciding
  whether it belongs in the next platform version (`v1.1`) or is truly
  one-off.
