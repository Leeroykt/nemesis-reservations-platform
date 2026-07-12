# Savora UI Design System

**Everything you need to build consistent UI without inventing new patterns.**

This document is the canonical reference for every color, component, spacing, and
interaction pattern in the Savora demo. When you're building a new screen and
need to add a button or a badge, you come here first, copy the exact markup/class
combo, and move on.

Nothing in this doc is negotiable for v1. If you want a new component, add it here
*first*, then use it everywhere. If you use a component differently on two pages,
you've already lost consistency.

---

## Color Palette

### Primary Brand Colors

The entire UI is driven by these CSS variables. Change them, the whole theme
changes. This is the white-label magic.

```css
:root {
  --gold: #C9A227;           /* Primary brand color — buttons, accents, warm highlights */
  --gold-soft: #E4C766;      /* Lighter gold — soft backgrounds, hover states */
  --emerald: #3FA672;        /* Success, confirmation, positive actions */
  --rust: #C1503D;           /* Danger, deletion, negative actions */
  --slate: #6C7A89;          /* Secondary/muted, less important actions */
}
```

### Neutral / Base Colors

```css
:root {
  --text: #F0EDE5;           /* Primary text, high contrast on dark background */
  --text-muted: #9AA79E;     /* Secondary text, de-emphasized labels */
  --text-faint: #6B7570;     /* Tertiary text, hints, placeholders, small UI */
  
  --surface: #182620;        /* Primary background (darkest) */
  --surface-1: #1F2E2A;      /* Slightly lighter, for cards/modals */
  --surface-2: #252F2B;      /* One level lighter, for nested containers */
  --surface-3: #2B3630;      /* Lightest surface, for hover/focus states */
  
  --border: rgba(255,255,255,0.08);  /* Dividers, table borders, input borders */
  --border-strong: rgba(255,255,255,0.14);  /* Emphasized borders (focus states) */
}
```

### Status / Semantic Colors

Use these ONLY for their semantic meaning. Never use rust as a decorative color.

```css
:root {
  --emerald: #3FA672;        /* Confirmed, completed, success, active */
  --gold: #C9A227;           /* Upcoming, pending, warning, highlight */
  --slate: #6C7A89;          /* Neutral, secondary, default */
  --rust: #C1503D;           /* Cancelled, deleted, danger, failure */
}
```

### Light Mode (rare, but supported)

When `data-theme="light"` is on the root:

```css
[data-theme="light"] {
  --text: #2C2C2C;
  --text-muted: #666;
  --text-faint: #999;
  
  --surface: #FFFFFF;
  --surface-1: #F8F8F8;
  --surface-2: #F0F0F0;
  --surface-3: #E8E8E8;
  
  --border: rgba(0,0,0,0.06);
  --border-strong: rgba(0,0,0,0.12);
  
  /* Brand colors stay the same */
  --gold: #C9A227;
  --emerald: #3FA672;
  --rust: #C1503D;
  --slate: #6C7A89;
}
```

**How to toggle theme:** 
Every "theme toggle" button calls `window.SavoraTheme.toggle()`. It sets
`data-theme` on the root, saves to localStorage, updates all CSS vars at once.

---

## Typography

### Font Families

Two fonts only. No more.

```css
:root {
  /* Primary font — readable, friendly, everything by default */
  --font-family: 'Raleway', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  
  /* Display font — headings, brand moments, fancy accents */
  --font-family-display: 'Fraunces', Georgia, serif;
}
```

**Import (exact, in HTML `<head>`):**
```html
<link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&family=Fraunces:ital,wght@0,400;1,400;1,500;1,600&display=swap" rel="stylesheet">
```

### Font Sizes & Weights

No arbitrary font sizes. Use these only:

```css
/* Headings */
h1  { font: 700 clamp(2rem, 4vw, 3rem) var(--font-family-display); letter-spacing: -0.02em; }
h2  { font: 700 2rem var(--font-family-display); }
h3  { font: 700 1.5rem var(--font-family-display); }
h4  { font: 700 1.25rem var(--font-family); }
h5  { font: 700 1rem var(--font-family); }
h6  { font: 600 0.875rem var(--font-family); }

/* Body text */
body { font: 400 0.9375rem / 1.5 var(--font-family); }
.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.8125rem; }  /* smallest, for labels/hints */

/* Weights */
.fw-regular { font-weight: 400; }
.fw-semibold { font-weight: 600; }
.fw-bold { font-weight: 700; }
.fw-black { font-weight: 900; }
```

### Line Heights

```css
.lh-tight { line-height: 1.25; }
.lh-normal { line-height: 1.5; }
.lh-relaxed { line-height: 1.75; }
```

Use Bootstrap classes (`fw-bold`, `text-sm`, etc.) — don't invent new sizing.

---

## Spacing System

A consistent scale for padding, margins, gaps. No arbitrary spacing.

```css
:root {
  --space-0: 0;
  --space-1: 0.25rem;   /* 4px */
  --space-2: 0.5rem;    /* 8px */
  --space-3: 0.75rem;   /* 12px */
  --space-4: 1rem;      /* 16px — default padding/margin */
  --space-6: 1.5rem;    /* 24px — larger blocks */
  --space-8: 2rem;      /* 32px — section separation */
  --space-12: 3rem;     /* 48px — major section gaps */
}
```

In Bootstrap, use classes:
- `.p-3` = padding 12px
- `.mb-4` = margin-bottom 16px
- `.gap-2` = gap 8px (in flexbox)
- `.px-4` = horizontal padding 16px

Never use inline styles like `style="padding: 17px"` or custom padding classes.

---

## Components

### Buttons

#### Primary action (gold)
For the main thing the user should do right now (submit, confirm, book).

```html
<button class="btn btn-gold btn-lg">
  <span>Request table</span>
  <span class="spinner-border spinner-border-sm d-none" id="spinner"></span>
</button>
```

**Variants:**
- `.btn-gold` — default, filled gold background
- `.btn-gold.btn-sm` — smaller
- `.btn-gold.btn-lg` — larger (use on forms)
- `.btn-gold:disabled` — faded, cursor not-allowed
- `.btn-gold .spinner-border` — loading spinner (hide/show with `.d-none`)

#### Secondary action (outline)
For confirm, save, OK buttons that aren't the primary CTA.

```html
<button class="btn btn-outline-ghost btn-sm">Cancel</button>
<button class="btn btn-dark-ghost btn-sm">Close</button>
```

**Variants:**
- `.btn-outline-ghost` — transparent, light border, light text
- `.btn-dark-ghost` — transparent background, no border, muted text (for secondary buttons)
- `.btn-sm`, `.btn-lg` — sizing

#### Danger (red/rust)
For delete, cancel-booking, destructive actions.

```html
<button class="btn btn-outline-danger btn-sm">
  <i class="bi bi-trash me-1"></i>Delete
</button>
```

**Variants:**
- `.btn-outline-danger` — outlined, rust color
- Always include an icon (trash, X, etc.)
- Always require confirmation (toast "are you sure?" or modal)

#### Icon buttons (no label)
For theme toggle, refresh, notification bell, etc.

```html
<button class="icon-btn" title="Toggle theme">
  <i class="bi bi-moon-stars-fill"></i>
</button>
```

**Styling:**
```css
.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border: none;
  background: transparent;
  border-radius: 0.5rem;
  cursor: pointer;
  color: var(--text-muted);
  transition: color 0.2s, background-color 0.2s;
}
.icon-btn:hover {
  background-color: var(--surface-2);
  color: var(--text);
}
```

**Rules:**
- Square, 40px × 40px
- Always have a `title` attribute (shows on hover for accessibility)
- Hover: subtle background, brighter text
- Icons from Bootstrap Icons (`bi-*` classes)

### Forms & Inputs

#### Text input (single line)

```html
<div class="mb-3">
  <label class="form-label" for="gName">Full name</label>
  <input type="text" class="form-control" id="gName" required placeholder="e.g. Farai Chikono">
  <div class="invalid-feedback d-block mt-2" id="gNameError"></div>
</div>
```

**Styling:**
```css
.form-control {
  border: 1px solid var(--border);
  background-color: var(--surface-2);
  color: var(--text);
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
  font-size: 0.9375rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201,162,39,0.1);
  outline: none;
}
.form-control::placeholder {
  color: var(--text-faint);
}
```

**Pattern:**
- Always wrap in `.mb-3` (margin-bottom)
- `.form-label` above
- `.invalid-feedback` below (shown only on validation error)
- Optional text: `<span class="text-faint">(optional)</span>` after label

#### Textarea

```html
<div class="mb-3">
  <label class="form-label" for="gNotes">Notes</label>
  <textarea class="form-control" id="gNotes" rows="3" placeholder="..."></textarea>
</div>
```

Same styling as text input, just `<textarea>` tag. Always set `rows` attribute.

#### Select dropdown

```html
<div class="mb-3">
  <label class="form-label" for="partySize">Party size</label>
  <select class="form-select" id="partySize" required>
    <option value="">— Choose —</option>
    <option value="1">1 guest</option>
    <option value="2">2 guests</option>
  </select>
</div>
```

**Styling:**
```css
.form-select {
  border: 1px solid var(--border);
  background-color: var(--surface-2);
  color: var(--text);
  border-radius: 0.5rem;
  padding: 0.75rem 1rem;
  padding-right: 2.5rem; /* space for dropdown arrow */
  background-image: url("data:image/svg+xml,..."); /* custom arrow, gold color */
}
```

**Pattern:**
- Empty option first: `<option value="">— Choose —</option>`
- Never pre-select unless there's a strong default

#### Checkbox

```html
<div class="form-check">
  <input class="form-check-input" type="checkbox" id="remember" checked>
  <label class="form-check-label" for="remember">Keep me signed in</label>
</div>
```

#### Date input

```html
<div class="mb-3">
  <label class="form-label" for="gDate">Date</label>
  <input type="date" class="form-control" id="gDate" required>
</div>
```

Set `min` attribute in JavaScript to prevent past dates:
```javascript
const today = new Date().toISOString().split('T')[0];
document.getElementById('gDate').min = today;
```

#### Time input

```html
<div class="mb-3">
  <label class="form-label" for="gTime">Time</label>
  <input type="time" class="form-control" id="gTime" required value="19:00">
</div>
```

#### Email input

```html
<input type="email" class="form-control" placeholder="you@example.com">
```

#### Phone input

```html
<input type="tel" class="form-control" placeholder="+263 7X XXX XXXX">
```

No special phone mask in v1 — just a text input with `type="tel"`. Backend validates.

### Cards & Containers

#### Credentials card (login, booking form)
```html
<div class="credentials-card">
  <!-- content -->
</div>
```

**Styling:**
```css
.credentials-card {
  background-color: var(--surface-1);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 2rem;
}
```

Used for: login form, public booking form, settings cards.

#### Info card (light background)
```html
<div class="info-card p-3 rounded-3 mb-4" style="background: var(--surface-2);">
  <div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-info-circle text-gold"></i>
    <span class="small fw-semibold">This is a demo instance</span>
  </div>
  <div class="small text-muted-soft">Additional info here...</div>
</div>
```

Used for: tips, notices, non-critical alerts.

#### Glass card (semi-transparent)
```html
<div class="glass rounded-4 p-4">
  <!-- content inside a semi-transparent glass container -->
</div>
```

**Styling:**
```css
.glass {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border);
}
```

Used sparingly, mainly on login visual side.

### Status Badges & Indicators

#### Status dot (colored circle)
```html
<span class="status-dot confirmed"></span>
```

**Variants (colors):**
```css
.status-dot {
  display: inline-block;
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 50%;
  margin-right: 0.5rem;
}
.status-dot.confirmed { background-color: var(--emerald); }
.status-dot.upcoming { background-color: var(--gold); }
.status-dot.cancelled { background-color: var(--rust); }
.status-dot.completed { background-color: var(--slate); }
.status-dot.pulse { animation: pulse 2s infinite; }
```

**Usage:**
```html
<span class="status-dot upcoming pulse"></span>
<span>Interactive Demo</span>
```

The `.pulse` class animates a gentle fade-in/out for "live" status.

#### Status badge (pill with text)
```html
<span class="status-badge confirmed">Confirmed</span>
```

**Styling:**
```css
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.8125rem;
  font-weight: 600;
}
.status-badge.confirmed {
  background-color: rgba(63, 166, 114, 0.15);
  color: var(--emerald);
}
.status-badge.upcoming {
  background-color: rgba(201, 162, 39, 0.15);
  color: var(--gold);
}
.status-badge.cancelled {
  background-color: rgba(193, 80, 61, 0.15);
  color: var(--rust);
}
.status-badge.completed {
  background-color: rgba(108, 122, 137, 0.15);
  color: var(--slate);
}
```

**Use when:**
- Displaying reservation status in lists/tables
- One badge per item maximum
- Text always matches the color name (no surprises)

### Modals & Overlays

#### Basic modal
```html
<div class="modal fade" id="reservationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Reservation details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="reservationModalBody">
        <!-- content injected here -->
      </div>
      <div class="modal-footer flex-wrap gap-2">
        <button type="button" class="btn btn-dark-ghost btn-sm me-auto">Delete</button>
        <button type="button" class="btn btn-gold btn-sm">Confirm</button>
        <button type="button" class="btn btn-dark-ghost btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
```

**Rules:**
- Title in `.modal-header`
- Content in `.modal-body` (inject dynamically with `.innerHTML`)
- Actions in `.modal-footer`
- Always include a "Close" button (class `.btn-dismiss`)
- Destructive action on the left, positive action on the right
- Use `.modal-dialog-centered` to center vertically
- Use `.modal-lg` for wider modals (e.g., customer profile)

**Opening a modal from JavaScript:**
```javascript
const modal = new bootstrap.Modal(document.getElementById('reservationModal'));
modal.show();
```

#### Dismissible info bar
```html
<div class="alert alert-info alert-dismissible fade show" role="alert">
  <i class="bi bi-info-circle me-2"></i>
  <strong>Note:</strong> This is a demo instance.
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

### Dropdown menus

```html
<div class="dropdown">
  <button class="btn btn-dark-ghost btn-sm dropdown-toggle" data-bs-toggle="dropdown">
    Options
  </button>
  <ul class="dropdown-menu shadow">
    <li><a class="dropdown-item" href="#action1">Action 1</a></li>
    <li><a class="dropdown-item" href="#action2">Action 2</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item text-danger" href="#delete">Delete</a></li>
  </ul>
</div>
```

**Styling:**
```css
.dropdown-menu {
  background-color: var(--surface-1);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
}
.dropdown-item {
  color: var(--text);
  padding: 0.5rem 1rem;
}
.dropdown-item:hover {
  background-color: var(--surface-2);
  color: var(--text);
}
.dropdown-item.text-danger {
  color: var(--rust);
}
```

**Patterns:**
- Always add `.shadow` to dropdowns (subtle depth)
- Group related items, separate with `<hr class="dropdown-divider">`
- Destructive items at the bottom, marked `.text-danger`

### Tooltips

```html
<button class="btn" data-bs-toggle="tooltip" title="Refresh data">
  <i class="bi bi-arrow-counterclockwise"></i>
</button>
```

Initialize in JavaScript:
```javascript
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
```

**Rules:**
- Use only for icon buttons (text buttons are self-explanatory)
- `title` attribute is the tooltip text
- Dark background, light text (Bootstrap default)
- Initialize on page load

### Spinners & Loading States

#### Button with spinner
```html
<button type="submit" class="btn btn-gold" id="submitBtn">
  <span id="submitText">Submit</span>
  <span class="spinner-border spinner-border-sm d-none" id="spinner"></span>
</button>
```

**JavaScript:**
```javascript
const btn = document.getElementById('submitBtn');
const text = document.getElementById('submitText');
const spinner = document.getElementById('spinner');

btn.disabled = true;
text.textContent = 'Submitting...';
spinner.classList.remove('d-none');

// After request completes:
btn.disabled = false;
text.textContent = 'Submit';
spinner.classList.add('d-none');
```

#### Skeleton loader (for lists)
```html
<div class="placeholder-wave">
  <div class="placeholder col-12 mb-2"></div>
  <div class="placeholder col-8 mb-2"></div>
  <div class="placeholder col-6"></div>
</div>
```

#### Page-level spinner
```html
<div class="d-flex justify-content-center align-items-center" style="height: 400px;">
  <div class="spinner-border" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>
```

### Toasts (notifications)

```html
<div class="toast-container position-fixed bottom-0 end-0 p-4">
  <div class="toast" id="appToast" role="status">
    <div class="d-flex align-items-center p-3">
      <i class="bi bi-check-circle-fill text-emerald me-2" id="toastIcon"></i>
      <div class="me-auto small fw-semibold" id="toastMsg">Done</div>
      <button type="button" class="btn-close ms-2" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
```

**JavaScript:**
```javascript
function showToast(message, type = 'success') {
  const toast = document.getElementById('appToast');
  const icon = document.getElementById('toastIcon');
  const msg = document.getElementById('toastMsg');
  
  icon.className = type === 'success' 
    ? 'bi bi-check-circle-fill text-emerald me-2'
    : type === 'error'
    ? 'bi bi-exclamation-circle-fill text-rust me-2'
    : 'bi bi-info-circle-fill text-gold me-2';
  
  msg.textContent = message;
  new bootstrap.Toast(toast).show();
}
```

**Usage:**
```javascript
showToast('Reservation confirmed!', 'success');
showToast('Something went wrong', 'error');
showToast('Please check your email', 'info');
```

**Rules:**
- Bottom-right corner (fixed position)
- Auto-dismiss after 5 seconds
- Different icons/colors per type (success/error/info)
- Never stack more than 2 toasts

### Badges & Labels

#### Text badge
```html
<span class="badge bg-gold text-dark">Demo</span>
```

#### Notification dot
```html
<span class="dot-badge" id="notifDot"></span>
```

**Styling:**
```css
.dot-badge {
  display: inline-block;
  width: 0.625rem;
  height: 0.625rem;
  border-radius: 50%;
  background-color: var(--rust);
  position: absolute;
  top: 0.25rem;
  right: 0.25rem;
}
```

---

## Layout Patterns

### Dashboard Shell (sidebar + main)

```html
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <!-- sidebar content -->
  </aside>
  
  <div class="sidebar-scrim" id="sidebarScrim"></div>
  
  <div class="main-col">
    <header class="topbar">
      <!-- topbar content -->
    </header>
    
    <main class="page-wrap" id="pageContent">
      <!-- page content -->
    </main>
  </div>
</div>
```

**Styling:**
```css
.app-shell {
  display: flex;
  height: 100vh;
  background-color: var(--surface);
}

.sidebar {
  width: 280px;
  background-color: var(--surface-1);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
}

.sidebar.collapsed {
  width: 80px; /* Icon-only mode */
}

.main-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.topbar {
  height: 60px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 1.5rem;
  background-color: var(--surface);
}

.page-wrap {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
}
```

### Responsive sidebar (mobile)

On mobile (< 992px), the sidebar is hidden off-screen:

```javascript
// Toggle sidebar on mobile
document.getElementById('mobileMenuBtn').addEventListener('click', function() {
  document.getElementById('sidebar').classList.add('show'); // visible
  document.getElementById('sidebarScrim').classList.remove('d-none'); // overlay
});

document.getElementById('sidebarScrim').addEventListener('click', function() {
  document.getElementById('sidebar').classList.remove('show');
  document.getElementById('sidebarScrim').classList.add('d-none');
});
```

**CSS:**
```css
@media (max-width: 991px) {
  .sidebar {
    position: fixed;
    left: -280px;
    top: 0;
    height: 100vh;
    z-index: 1000;
    transition: left 0.3s ease;
  }
  
  .sidebar.show {
    left: 0;
  }
  
  .sidebar-scrim {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 900;
  }
}
```

### Page grid layout

For pages with multiple columns (e.g., KPI cards + chart):

```html
<div class="container-fluid">
  <div class="row g-4">
    <div class="col-md-6 col-lg-3">
      <div class="kpi-card">...</div>
    </div>
  </div>
</div>
```

**Rules:**
- Use Bootstrap grid (`.row`, `.col-*`)
- `.g-4` for gutters (16px gap)
- Responsive breakpoints: `col-12` (full), `col-md-6` (half @ 768px), `col-lg-3` (quarter @ 992px)

### Empty state

```html
<div class="text-center py-8">
  <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
  <h3 class="fw-bold">No reservations</h3>
  <p class="text-muted-soft">Try adjusting your filters or check back later.</p>
</div>
```

---

## Data Tables & Lists

### Basic table

```html
<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Guest</th>
        <th>Date</th>
        <th>Party</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Farai Chikono</td>
        <td>Jul 11, 2026</td>
        <td>4</td>
        <td><span class="status-badge confirmed">Confirmed</span></td>
      </tr>
    </tbody>
  </table>
</div>
```

**Styling:**
```css
.table {
  color: var(--text);
  border-color: var(--border);
}

.table thead th {
  background-color: var(--surface-2);
  color: var(--text-muted);
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border: none;
  padding: 0.75rem 1rem;
}

.table tbody tr:hover {
  background-color: var(--surface-2);
  cursor: pointer;
}

.table tbody td {
  padding: 1rem;
  border-color: var(--border);
  vertical-align: middle;
}
```

### Sortable column headers

```html
<th>
  <a href="#" onclick="sortBy('date')" class="text-muted">
    Date
    <i class="bi bi-arrow-up-down text-faint ms-1"></i>
  </a>
</th>
```

### Table actions (row context menu)

```html
<td>
  <div class="dropdown">
    <button class="icon-btn" data-bs-toggle="dropdown">
      <i class="bi bi-three-dots-vertical"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="#edit">Edit</a></li>
      <li><a class="dropdown-item" href="#confirm">Confirm</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="#cancel">Cancel</a></li>
    </ul>
  </div>
</td>
```

---

## Navigation

### Sidebar navigation

```html
<nav class="sidebar-nav">
  <div class="sidebar-section-title">Main</div>
  <a href="#/overview" class="nav-link-app" data-route="overview">
    <i class="bi bi-grid-1x2"></i>
    <span class="sidebar-label">Overview</span>
  </a>
  <a href="#/reservations" class="nav-link-app" data-route="reservations">
    <i class="bi bi-calendar-check"></i>
    <span class="sidebar-label">Reservations</span>
  </a>
</nav>
```

**Styling:**
```css
.sidebar-section-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-faint);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 1.5rem 1rem 0.75rem;
  margin-top: 1rem;
}

.nav-link-app {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: var(--text-muted);
  text-decoration: none;
  border-left: 3px solid transparent;
  transition: all 0.2s ease;
}

.nav-link-app:hover {
  background-color: var(--surface-2);
  color: var(--text);
}

.nav-link-app.active {
  background-color: var(--surface-2);
  border-left-color: var(--gold);
  color: var(--text);
}

.nav-link-app i {
  font-size: 1.25rem;
}
```

### Top navigation (search, actions)

```html
<header class="topbar">
  <i class="bi bi-list d-lg-none" id="mobileMenuBtn"></i>
  
  <div class="topbar-search d-none d-sm-block">
    <i class="bi bi-search"></i>
    <input type="text" placeholder="Search reservations..." id="globalSearch">
  </div>
  
  <div class="ms-auto d-flex align-items-center gap-2">
    <!-- buttons, dropdowns, user menu go here -->
  </div>
</header>
```

**Styling:**
```css
.topbar-search {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: var(--surface-2);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 0.5rem 1rem;
  flex: 1;
  max-width: 300px;
  margin: 0 2rem;
}

.topbar-search input {
  border: none;
  background: transparent;
  color: var(--text);
  outline: none;
  flex: 1;
}

.topbar-search input::placeholder {
  color: var(--text-faint);
}
```

---

## Animations & Transitions

### Page transitions (fade-in)

```html
<main class="page-wrap fade-in">
  <!-- content -->
</main>
```

**Styling:**
```css
.fade-in {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
```

### Reveal-on-scroll (landing page)

```html
<div class="reveal">
  <!-- content that fades in as you scroll -->
</div>
```

**JavaScript:**
```javascript
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.15 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
```

**Styling:**
```css
.reveal {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.reveal.is-visible {
  opacity: 1;
  transform: translateY(0);
}
```

### Pulse animation (live indicator)

```html
<span class="status-dot upcoming pulse"></span>
```

**Styling:**
```css
.pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
```

### Smooth scroll

```css
html {
  scroll-behavior: smooth;
}
```

---

## Dark/Light Mode Implementation

### CSS structure

```css
/* Default (dark mode) */
:root {
  --gold: #C9A227;
  --text: #F0EDE5;
  --surface: #182620;
  /* ... all other vars */
}

/* Light mode */
[data-theme="light"] {
  --gold: #C9A227; /* brand color unchanged */
  --text: #2C2C2C;
  --surface: #FFFFFF;
  /* ... override all other vars */
}
```

### JavaScript toggle

```javascript
window.SavoraTheme = {
  get() {
    return document.documentElement.getAttribute('data-theme') || 'dark';
  },
  set(mode) {
    document.documentElement.setAttribute('data-theme', mode);
    localStorage.setItem('savora-theme', mode);
    
    // Update icon
    document.querySelectorAll('[data-theme-thumb]').forEach(el => {
      el.innerHTML = mode === 'light'
        ? '<i class="bi bi-sun-fill"></i>'
        : '<i class="bi bi-moon-stars-fill"></i>';
    });
  },
  toggle() {
    this.set(this.get() === 'light' ? 'dark' : 'light');
  }
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  const saved = localStorage.getItem('savora-theme') || 'dark';
  window.SavoraTheme.set(saved);
  
  document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
    btn.addEventListener('click', () => window.SavoraTheme.toggle());
  });
});
```

**Button markup:**
```html
<button class="icon-btn" data-theme-toggle title="Toggle theme">
  <span data-theme-thumb><i class="bi bi-moon-stars-fill"></i></span>
</button>
```

---

## Common Patterns

### Notification badge

```html
<div class="dropdown" id="notifBtn">
  <button class="icon-btn" data-bs-toggle="dropdown">
    <i class="bi bi-bell"></i>
    <span class="dot-badge" id="notifDot"></span>
  </button>
  <div class="dropdown-menu dropdown-menu-end p-0" style="width: 340px;">
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
      <span class="fw-semibold">Notifications</span>
      <span class="small text-gold cursor-pointer" id="markAllRead">Mark all read</span>
    </div>
    <div id="notifList" style="max-height: 360px; overflow-y: auto;"></div>
  </div>
</div>
```

### User profile menu (top right)

```html
<div class="dropdown d-none d-sm-block">
  <div class="avatar-circle" data-bs-toggle="dropdown">NN</div>
  <ul class="dropdown-menu dropdown-menu-end shadow">
    <li><a class="dropdown-item" href="#/settings"><i class="bi bi-person me-2"></i>Profile</a></li>
    <li><a class="dropdown-item" href="#/settings"><i class="bi bi-gear me-2"></i>Settings</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item text-danger" href="#" id="logoutBtn"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
  </ul>
</div>
```

**Styling:**
```css
.avatar-circle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background-color: var(--gold);
  color: #000;
  font-weight: 700;
  font-size: 0.875rem;
  cursor: pointer;
  user-select: none;
}
```

### KPI card

```html
<div class="kpi-card">
  <div class="kpi-label">Today's reservations</div>
  <div class="kpi-value">38</div>
  <div class="kpi-delta positive">↑ 12.4%</div>
</div>
```

**Styling:**
```css
.kpi-card {
  background-color: var(--surface-1);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1.5rem;
}

.kpi-label {
  font-size: 0.875rem;
  color: var(--text-muted);
  margin-bottom: 0.5rem;
}

.kpi-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.5rem;
}

.kpi-delta {
  font-size: 0.875rem;
  font-weight: 600;
}

.kpi-delta.positive {
  color: var(--emerald);
}

.kpi-delta.negative {
  color: var(--rust);
}
```

### Horizontal rule / divider

```html
<hr class="border-top" style="border-color: var(--border);">
```

Or use Bootstrap's `.divider` (rarely needed, often just `<hr>`).

---

## Utilities & helpers

### Text truncation

```html
<div class="text-truncate">Very long text that gets cut off...</div>
```

### Flex spacing

```html
<div class="d-flex justify-content-between align-items-center">
  <span>Label</span>
  <span>Value</span>
</div>
```

### Responsive display

```html
<span class="d-none d-sm-inline">Visible on small screens and up</span>
<span class="d-lg-none">Hidden on large screens and up</span>
```

### Opacity

```css
.opacity-50 { opacity: 0.5; }
.opacity-75 { opacity: 0.75; }
```

### Cursor

```css
.cursor-pointer { cursor: pointer; }
.cursor-not-allowed { cursor: not-allowed; }
```

---

## Bootstrap 5 classes you'll use

These are from Bootstrap 5.3, already in your `style.css`:

**Display & positioning:**
- `.d-flex`, `.d-none`, `.d-block`, `.d-inline`, `.d-inline-flex`
- `.flex-grow-1`, `.flex-shrink-1`
- `.gap-2`, `.gap-3`, `.gap-4` (for flexbox/grid gap)
- `.position-absolute`, `.position-relative`, `.position-fixed`
- `.top-0`, `.start-0`, `.end-0`, `.bottom-0` (for positioning)
- `.overflow-hidden`, `.overflow-auto`

**Spacing:**
- `.p-3`, `.px-4`, `.py-2` (padding)
- `.m-3`, `.mx-auto`, `.mb-2` (margin)

**Text:**
- `.text-center`, `.text-start`, `.text-end`
- `.fw-bold`, `.fw-semibold`, `.fw-normal`
- `.fs-5` (font size, 5 = smaller)
- `.text-uppercase`, `.text-lowercase`
- `.text-decoration-none`, `.text-truncate`

**Sizing:**
- `.w-100`, `.h-100` (width/height 100%)
- `.w-auto`, `.h-auto`

**Border & shadow:**
- `.border`, `.border-top`, `.border-bottom`
- `.rounded`, `.rounded-3`, `.rounded-circle`
- `.shadow`, `.shadow-sm`, `.shadow-lg`

**Backgrounds & colors:**
- `.bg-dark`, `.bg-light` (use sparingly, prefer CSS vars)
- `.text-muted`, `.text-muted-soft` (custom, from `style.css`)

**Grid:**
- `.container`, `.container-fluid`
- `.row`, `.col-12`, `.col-md-6`, `.col-lg-4`

---

## Quick reference: common component combos

### Success toast
```html
<i class="bi bi-check-circle-fill text-emerald me-2"></i>
<span class="fw-semibold">Reservation confirmed!</span>
```

### Error toast
```html
<i class="bi bi-exclamation-circle-fill text-rust me-2"></i>
<span class="fw-semibold">Something went wrong</span>
```

### Loading state
```html
<button class="btn btn-gold" disabled>
  <span class="spinner-border spinner-border-sm me-2"></span>
  Loading...
</button>
```

### Empty state (no data)
```html
<div class="text-center py-8">
  <i class="bi bi-inbox" style="font-size: 2rem; color: var(--text-muted);"></i>
  <h5 class="mt-3 fw-bold">No results</h5>
  <p class="text-muted-soft">Try adjusting your filters</p>
</div>
```

### Confirmation modal
```html
<div class="modal" id="confirmModal">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Are you sure?</h5>
      </div>
      <div class="modal-body">
        This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-dark-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-outline-danger">Delete</button>
      </div>
    </div>
  </div>
</div>
```

---

## Forbidden practices (do not do these)

1. **Inline styles:** Never `style="color: red"` — use CSS classes or variables.
2. **Hardcoded colors:** Never `#FF0000` in code — use `var(--rust)`.
3. **Arbitrary spacing:** Never `padding: 13px` — use Bootstrap spacing.
4. **New button styles:** Never invent a fifth button type — use documented variants.
5. **Multiple fonts:** Only Raleway + Fraunces. No new fonts.
6. **Icon-only buttons without title:** Icon buttons always have `title` for accessibility.
7. **Tables without headings:** Tables always have `<thead>` and `<tbody>`.
8. **Orphaned `<input>` elements:** Every input has a `<label>` with `for` attribute.
9. **Magic numbers:** Never `width: 340px` — use rem/em/% or define a CSS var.
10. **Nested modals:** Bootstrap doesn't handle them well. Use a single modal, swap content.
11. **Event handlers inline:** Never `onclick="handleClick()"` in HTML — use JavaScript event listeners.
12. **Undocumented component variants:** If it looks different from the demo, check this doc first.

---

## When adding new components

Before you build a new button, badge, card, or any visual element:

1. **Check this doc.** It's probably already defined.
2. **If not found,** ask: "Is this really a new component, or can I compose existing ones?"
3. **If truly new,** add it to this doc *before* using it anywhere else. Write the class, the variants, the usage example.
4. **Never use it in just one place.** If it's in the docs, it's reusable everywhere.
5. **Test in light & dark mode.** Colors must be readable in both.

---

## Design tokens summary (quick copy-paste)

```css
/* Colors */
--gold: #C9A227;
--gold-soft: #E4C766;
--emerald: #3FA672;
--rust: #C1503D;
--slate: #6C7A89;

/* Text */
--text: #F0EDE5;
--text-muted: #9AA79E;
--text-faint: #6B7570;

/* Surface */
--surface: #182620;
--surface-1: #1F2E2A;
--surface-2: #252F2B;
--surface-3: #2B3630;

/* Border */
--border: rgba(255,255,255,0.08);
--border-strong: rgba(255,255,255,0.14);

/* Spacing scale */
--space-2: 0.5rem;
--space-3: 0.75rem;
--space-4: 1rem;
--space-6: 1.5rem;
--space-8: 2rem;

/* Fonts */
--font-family: 'Raleway', sans-serif;
--font-family-display: 'Fraunces', serif;
```

---

**This doc is the source of truth. Bookmark it. Reference it before adding anything new.**
