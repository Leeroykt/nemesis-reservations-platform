# Database Schema — PostgreSQL 16

Single restaurant per database (one instance per client — see Product Spec
§4). No `tenant_id` columns anywhere; isolation is at the infrastructure
level, not the row level. This keeps every migration and query simpler and
removes an entire class of cross-tenant-leak bugs.

All tables use `id BIGSERIAL PRIMARY KEY` and `created_at` / `updated_at`
timestamps unless noted. Soft deletes (`deleted_at`) are used on
`reservations`, `customers`, and `tables` so nothing a manager deletes is
ever truly gone (audit trail integrity).

---

## restaurants
Single row. Holds everything currently hardcoded in `SAVORA_DATA.restaurant`.

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| name | varchar(120) | e.g. "Signet & Vine" |
| tagline | varchar(160) | |
| email | varchar(160) | |
| phone | varchar(40) | |
| address | varchar(255) | |
| timezone | varchar(60) | default `Africa/Harare` |
| currency | varchar(3) | default `USD` |
| seats | int | |
| tables_count | int | |
| logo_path | varchar(255) | nullable, relative to storage disk |
| primary_color_hex | varchar(7) | default `#C9A227` (drives `--gold`) |
| created_at / updated_at | timestamp | |

## restaurant_hours
7 rows per restaurant (one per weekday).

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| day_of_week | smallint | 0=Mon … 6=Sun |
| open_time | time | nullable if closed |
| close_time | time | nullable if closed |
| is_closed | boolean | default false |

## restaurant_rules
Single row per restaurant. Mirrors `SAVORA_DATA.restaurant.rules`.

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | unique |
| max_party_size | int | default 14 |
| slot_length_minutes | int | default 90 |
| buffer_minutes | int | default 15 |
| cancellation_window_hours | int | default 4 |
| deposit_required_above | int | nullable — party size threshold |

## users
Staff accounts — replaces the fake sessionStorage auth.

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| name | varchar(120) | |
| email | varchar(160) | unique |
| password | varchar(255) | bcrypt hash |
| role | enum(`owner`,`manager`,`host`) | server-enforced, mirrors `ROLE_LEVELS` in current app.js |
| avatar_initials | varchar(4) | generated on save if null |
| last_login_at | timestamp | nullable |
| remember_token | varchar(100) | Laravel standard |
| created_at / updated_at | timestamp | |

## tables
Floor plan. Mirrors `SAVORA_DATA.tables`.

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| code | varchar(10) | e.g. `T-12`, unique per restaurant |
| zone | varchar(60) | e.g. "Terrace" |
| capacity | int | |
| shape | enum(`round`,`square`,`rect`) | for floor-plan rendering |
| pos_x | decimal(5,2) | percentage-based coordinate, matches current floor-plan CSS |
| pos_y | decimal(5,2) | |
| status | enum(`Available`,`Occupied`,`Reserved`,`Cleaning`) | live status, distinct from reservation status |
| deleted_at | timestamp | nullable, soft delete |

## customers
Guest CRM. Mirrors `SAVORA_DATA.customers`.

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| name | varchar(120) | |
| email | varchar(160) | nullable |
| phone | varchar(40) | |
| visits | int | default 0, incremented on each Completed reservation |
| last_visit_at | date | nullable |
| is_vip | boolean | default false |
| lifetime_spend | decimal(10,2) | default 0 |
| deleted_at | timestamp | nullable, soft delete |
| created_at / updated_at | timestamp | |

## customer_preferences
Normalizes the current `preferences: []` array on customers.

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| customer_id | FK → customers | |
| note | varchar(160) | e.g. "No shellfish", "Window seating" |

## reservations
Core entity. Mirrors `SAVORA_DATA.reservations` + creation fields from `book.js`.

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| public_ref | varchar(20) | e.g. `RB-2301`, unique, shown to guests — generated server-side, collision-checked |
| customer_id | FK → customers | nullable (guest may not be a known customer yet) |
| table_id | FK → tables | nullable until assigned |
| guest_name | varchar(120) | denormalized copy at time of booking |
| guest_phone | varchar(40) | |
| guest_email | varchar(160) | nullable |
| date | date | |
| time | time | |
| party_size | int | |
| status | enum(`Upcoming`,`Confirmed`,`Completed`,`Cancelled`) | |
| notes | text | nullable |
| source | enum(`Website`,`Phone`,`App`,`Walk-in`) | |
| created_by_user_id | FK → users | nullable — null when created by a guest via public form |
| deleted_at | timestamp | nullable, soft delete |
| created_at / updated_at | timestamp | |

Indexes: `(restaurant_id, date, table_id)` for conflict-checking queries,
`(restaurant_id, status)` for the reservations-tab filters.

## waitlist

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| name | varchar(120) | |
| phone | varchar(40) | |
| party_size | int | |
| quoted_wait_minutes | int | |
| notes | text | nullable |
| status | enum(`Waiting`,`Seated`,`Left`) | default `Waiting` |
| added_at | timestamp | |

## notifications
In-app bell notifications for staff.

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| title | varchar(160) | |
| message | varchar(255) | |
| is_read | boolean | default false |
| created_at | timestamp | |

## activity_log / audit_log
One table, `is_audit` flag distinguishes the trimmed "recent activity" feed
(12 most recent, shown on Overview) from the full audit trail (Settings →
Audit log, owner-only, unbounded retention).

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| actor_user_id | FK → users | nullable (null = guest/system action) |
| actor_label | varchar(120) | denormalized display string, e.g. "Farai Chikono (guest, website)" |
| icon | varchar(40) | bootstrap-icon class, for feed rendering |
| tone | enum(`gold`,`emerald`,`rust`,`slate`) | |
| description | varchar(255) | |
| entity_type | varchar(40) | nullable, e.g. `reservation` |
| entity_id | bigint | nullable |
| created_at | timestamp | |

## email_templates

| Column | Type | Notes |
|---|---|---|
| id | bigserial PK | |
| restaurant_id | FK → restaurants | |
| key | varchar(40) | `confirm`, `reminder`, `cancel`, `vip` |
| name | varchar(120) | |
| subject | varchar(160) | |
| body | text | supports `{{guest_name}}`, `{{party_size}}`, `{{date}}`, `{{time}}` tokens |

---

## Entity relationship summary

```
restaurants 1──1 restaurant_rules
restaurants 1──7 restaurant_hours
restaurants 1──N users
restaurants 1──N tables
restaurants 1──N customers 1──N customer_preferences
restaurants 1──N reservations N──1 tables
                          reservations N──1 customers (nullable)
restaurants 1──N waitlist
restaurants 1──N notifications
restaurants 1──N activity_log
restaurants 1──N email_templates
```

## Seeding

`database/seeders/DemoSeeder.php` reproduces exactly what's in the current
`data.js` (same names, same sample reservations) — this is what powers the
generic walkthrough VM. `database/seeders/ClientSeeder.php` reads
`client-config/seed-config.json` (see Product Spec §4) and seeds a **blank**
instance with just the client's real restaurant info, hours, and table
layout — no fake reservations/customers — ready for their first real guest.
