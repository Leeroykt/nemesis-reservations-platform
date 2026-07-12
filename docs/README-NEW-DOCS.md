# New Documentation Created — What You Have & Why It Matters

You asked for docs that capture everything needed so you can develop without inventing new patterns mid-sprint. Here's what was created:

---

## The Three New Docs

### 1. **07-POTENTIAL-KILLERS.md** (22 sections)
**What it does:** Identifies 23 architectural & process mistakes that will derail you mid-dev if ignored.

**Problems it prevents:**
- Frontend state management chaos (three different approaches = inconsistent data)
- API response shape inconsistency (different endpoints return different structures)
- Timezone bugs (reservations show at wrong times in different TZs)
- Form validation split-brain (rules in three places, not synced)
- Permission leakage (roles enforced in UI only, not backend)
- Email deliverability issues (confirmation emails never arrive)
- Concurrent edit conflicts (two managers editing same reservation simultaneously)
- Soft delete confusion (deleted records sneaking into reports)
- Session/auth edge cases (user logged in on two tabs, one logs out, other confused)
- Migration failures (migration works locally, breaks in production)
- And 13 more...

**How to use it:** Before you start a feature, read the relevant section (§C for dates, §G for deletes, etc.). Lock down how you're handling it. Document your decision. Move on.

**Why it matters:** Each section has a "lock this down NOW" subsection with concrete decisions to make before Phase 0. Skipping this = learning these lessons the hard way = 3+ days lost mid-sprint.

---

### 2. **08-UI-DESIGN-SYSTEM.md** (comprehensive)
**What it does:** Documents every color, component, pattern, spacing, and interaction from the existing Savora demo in granular detail.

**Covers:**
- Color palette (gold, emerald, rust, slate) with hex values + semantic meaning
- Typography (fonts, sizes, weights — from h1 to .text-xs)
- Spacing system (8px–48px scale, CSS vars)
- Every component: buttons, forms, cards, badges, modals, tables, navigation
- Animations & transitions
- Dark/light mode implementation
- 15 "forbidden practices" (no inline styles, no hardcoded colors, etc.)
- Quick reference for common combos

**Problem it prevents:** 
- You add a button differently than the demo (wrong color, wrong hover state)
- A form input styled inconsistently on two pages
- Colors don't work in light mode
- Icons used as decorations without labels (accessibility fail)
- Spacing chaos (padding that doesn't match any scale)
- Modals layering incorrectly (z-index mess)

**How to use it:** 
- Open in a browser tab while coding
- Before adding any visual element, search the doc
- If it's there, copy the exact markup/class combo
- If it's new, add to the doc *first*, then use everywhere
- Print the "forbidden practices" section and tape it above your monitor

**Why it matters:** The existing demo *already looks great*. You're not redesigning — you're rebuilding the same UI with a real backend. Consistency = no visual regressions = faster QA.

---

### 3. **09-PRE-DEV-SETUP-CHECKLIST.md** (decision framework)
**What it does:** Locks down 9 critical architectural decisions *before* you write code.

**Decisions it captures:**
- State management pattern (when to use Inertia, when to use React state)
- API response shape (list envelopes, error shapes, consistency)
- Form validation split (where backend validates, where frontend mirrors)
- Date/timezone handling (UTC in DB, restaurant TZ on queries)
- Soft delete scope (which tables, which queries)
- Role-based access (every endpoint gated, test proves it)
- Environment config (which vars differ per client)
- TypeScript strategy (auto-generate types or manually sync)

**Sections:**
- **§A–§H:** 8 architectural decisions with lock-down templates
- **Vibe coding gotchas:** 15 anti-patterns with fixes ("I'll handle state differently in this component" = BAD)
- **Final checklist:** 40 items to complete before Phase 0
- **Reading order:** What to read and when (3.5 hours total)

**Problem it prevents:** 
- Phase 3 (reservation engine): Realize halfway through that your state management approach is duplicated state in 5 places
- Phase 4 (wiring UI): Frontend expects `{ data, meta }` but controller returns `{ items, count }`
- Phase 5 (public booking): Email service never tested, confirmation emails go to spam
- Mid-dev refactoring because your form validation rules aren't synced between frontend and backend

**How to use it:**
1. Spend 1-2 hours filling out §A–§H *before* Phase 0
2. Create `09a–09h` decision docs in your repo (each one references the chapter)
3. Refer to those docs when building (§C before writing date queries, §F before form validation, etc.)
4. Print the "15 vibe coding gotchas" and tape it to your desk
5. Before starting a feature, check the relevant decision doc

**Why it matters:** These aren't opinions — they're pre-built solutions to problems that *will* occur. You're not making decisions mid-dev (when you're tired and want to "just get it working"). You're making them fresh, documented, consistent.

---

## What these three docs prevent

### Without them:
- **Week 1:** You build auth + overview. State management seems fine.
- **Week 2:** You build reservations + forms. You realize state management approach doesn't work for concurrent updates. Spend 2 days refactoring.
- **Week 3:** API responses are inconsistent (3 different shapes). Frontend has 3 different parsing functions. Add a filter to one endpoint, 2 others break.
- **Week 4:** Timezone bug surfaces. Client's "today" shows "yesterday's reservations". Spend 1 day tracing, another fixing.
- **Week 4.5:** Form validation rules live in 4 places. Add a max-length rule to the backend, frontend doesn't check it, users confused.
- **Week 5:** Email confirmation isn't working (EmailJS key exposed client-side). Client says "bookings don't work." Scramble to swap to Laravel Mail.
- **Week 6:** Permission leakage found in QA. Host account can somehow delete reservations. Spend a day patching + audit every endpoint.
- **Week 6.5:** Soft delete confusion in analytics. Deleted customers still counted in reports.
- **Week 7:** First client deploy. Backup strategy never tested. Database corrupts. No restore path.

### With them:
- **Before Phase 0:** Lock down state management pattern, API shapes, form validation split, timezone handling, soft delete scope, permissions, email strategy.
- **Phase 1:** Build auth + DB. State management tested, no surprises.
- **Phase 2:** Build API. Response shapes consistent, tested.
- **Phase 3:** Build reservations. Form validation pattern known, no rewrites.
- **Phase 4:** Wire UI. Timezone handling proven, no bugs.
- **Phase 5:** Build public booking + email. Email strategy locked (Laravel Mail, not EmailJS), tested.
- **Phase 6:** Settings + roles. Permissions tested (RoleAccessTest.php passes, backend enforces).
- **Phase 7:** Deploy. Backups tested, restore procedure documented.

**Net difference: No 3-day refactors. No surprise bugs. Ship in 2 weeks vs. 3+.**

---

## How the docs work together

```
00-PRODUCT-SPEC.md (what we're building)
        ↓
02-FEATURE-SPEC.md (exactly what ships in v1)
        ↓
04-ROADMAP.md (phase breakdown)
        ↓
03-PROJECT-STRUCTURE.md (folder layout)
        ↓
01-DATABASE-SCHEMA.md (data model)
        ↓
        ├─→ 07-POTENTIAL-KILLERS.md (things that break dev)
        │   ├─→ 09a-STATE-MANAGEMENT.md (state pattern)
        │   ├─→ 09b-API-CONTRACTS.md (API shapes)
        │   ├─→ 09c-FORM-VALIDATION.md (validation split)
        │   ├─→ 09d-TIMEZONE-HANDLING.md (date handling)
        │   ├─→ 09e-SOFT-DELETE-STRATEGY.md (soft deletes)
        │   ├─→ 09f-ROLE-MATRIX.md (permissions)
        │   ├─→ 09g-CONFIGURATION.md (env vars)
        │   └─→ 09h-TYPESCRIPT-STRATEGY.md (types)
        │
        ├─→ 08-UI-DESIGN-SYSTEM.md (colors, components, patterns)
        │   └─→ reference constantly while coding
        │
        └─→ 09-PRE-DEV-SETUP-CHECKLIST.md (lock down decisions)
            └─→ creates 09a–09h docs above

        ↓
05-QA-CHECKLIST.md (how to know you're done)
        ↓
06-CLIENT-ONBOARDING-RUNBOOK.md (how to deploy)
```

---

## What happens at each phase

### Phase 0 (repo setup)
**Before:** Read all docs above, create 09a–09h decision docs, lock down 40 checklist items.
**During:** Bootstrap Laravel, Docker, GitHub Actions.
**After:** `docker compose up -d` works cleanly.

### Phase 1 (database)
**Before:** Review schema, check 09d-TIMEZONE-HANDLING.md.
**During:** Write migrations matching schema.sql exactly.
**After:** `php artisan migrate:fresh --seed` produces data matching `data.js`.

### Phase 2 (auth)
**Before:** Check 09f-ROLE-MATRIX.md, plan RoleAccessTest.php.
**During:** Build auth, policies, role gating.
**After:** RoleAccessTest.php passes — every endpoint role-gated server-side.

### Phase 3 (reservations)
**Before:** Check 09a-STATE-MANAGEMENT.md, 09c-FORM-VALIDATION.md, 09d-TIMEZONE-HANDLING.md, 09e-SOFT-DELETE-STRATEGY.md.
**During:** Build ReservationService, CRUD API, conflict detection.
**After:** Feature tests pass, conflict detection proven, business rules enforced.

### Phase 4 (wire UI)
**Before:** Check 08-UI-DESIGN-SYSTEM.md (have it open).
**During:** Replace `data.js` calls with API calls, one screen at a time.
**After:** Dashboard visually matches demo, all data from API.

### Phase 5 (public booking + email)
**Before:** Check email configuration decision (09g-CONFIGURATION.md), test mail setup.
**During:** Build public endpoint, Laravel Mail, test email delivery.
**After:** Booking form → reservation in DB + email in inbox.

### Phase 6 (settings + roles + audit)
**Before:** Check 09f-ROLE-MATRIX.md, 09g-CONFIGURATION.md.
**During:** Build settings screens, user management, audit log.
**After:** Owner can configure everything without touching code.

### Phase 7 (deploy automation)
**Before:** Check 09g-CONFIGURATION.md, review deploy script needs.
**During:** Write `deploy.sh`, test on a fake client VM.
**After:** Prove 72-hour onboarding promise (timed).

### Phase 8 (QA)
**Before:** Print 05-QA-CHECKLIST.md.
**During:** Walk through every item, cross off.
**After:** All items checked, v1.0.0 tagged.

---

## The three gotchas to avoid

### 1. "I'll refer to the docs later"
**Reality:** You won't. You'll vibe code, invent a pattern, use it in two places, realize in week 3 it's wrong, refactor.
**Solution:** Read 09-PRE-DEV-SETUP-CHECKLIST.md § "vibe coding gotchas" *before* you start. Print it. Tape it above your monitor. When you feel tempted to invent something, re-read it.

### 2. "These docs are too detailed"
**Reality:** They're exactly detailed enough. Each section is solving a specific problem that derailed similar projects.
**Solution:** Don't read them all at once (3.5 hours is a lot). Read them phase-by-phase (§C before date work, §F before forms, etc.). They're reference docs, not novels.

### 3. "I'll update the docs as I go"
**Reality:** You won't. You'll code, docs stay static, drift happens.
**Solution:** Docs are locked before Phase 0. New ideas go in `BACKLOG.md`, not in the docs. After v1 ships and you revisit, *then* update docs for v1.1.

---

## Reading timeline

**Before Phase 0 (1–2 hours):**
- Re-read `00-PRODUCT-SPEC.md` (overview)
- Read `02-FEATURE-SPEC.md` (scope)
- Read `04-ROADMAP.md` (phases)
- Read `07-POTENTIAL-KILLERS.md` (gotchas)
- Read `08-UI-DESIGN-SYSTEM.md` (design)
- Work through `09-PRE-DEV-SETUP-CHECKLIST.md` (decisions)
- Create `09a–09h` docs

**During each phase (~10 min at start):**
- Check the relevant 09x doc (state management before phase 4, timezone before phase 3, etc.)
- Reference `08-UI-DESIGN-SYSTEM.md` constantly (open in tab)
- Check `07-POTENTIAL-KILLERS.md` section if stuck

**Before Phase 8 QA (30 min):**
- Print `05-QA-CHECKLIST.md`
- Walk through every item

**Before client handoff (1 hour):**
- Print `06-CLIENT-ONBOARDING-RUNBOOK.md`
- Follow it exactly

---

## Summary

You now have:

1. **07-POTENTIAL-KILLERS.md** — 23 sections covering things that break dev
2. **08-UI-DESIGN-SYSTEM.md** — complete design reference
3. **09-PRE-DEV-SETUP-CHECKLIST.md** — decision framework + 40-item checklist + vibe-coding anti-patterns
4. **09a–09h templates** (created from checklist) — decision docs for each major system

**What this prevents:**
- State management refactors (week 2)
- API response inconsistency (week 3)
- Form validation mess (week 3–4)
- Timezone bugs (week 4)
- Permission leakage (week 6)
- Email failures (week 5)
- Soft delete confusion (week 6)
- Backup/restore panic (week 7)

**Net result:** 2-week dev sprint vs. 3+ weeks of firefighting.

**Next step:** Print this README, read the timeline, start with 09-PRE-DEV-SETUP-CHECKLIST.md section "Phase 0 prerequisites."

Good luck. You've got a solid plan. Stick to it.
