📜 09-ENTERPRISE-RULEBOOK.md (LOCKED)
Strict, Non-Negotiable Architectural Decisions for NEMESIS v1.0
1. State Management & Data Fetching (The "Single Source of Truth" Rule)
The Ruling:

Server Data (Reservations, Customers, Tables, KPIs): MUST come exclusively via Inertia's usePage().props. You are FORBIDDEN from using useState to store server-fetched data.

UI State (Modal open/closed, form dirty state, dropdown toggles): MAY use React useState.

Global State: DO NOT install Zustand, Redux, or Context API for server data. Inertia is your global store.

The Implementation (Strict Copy-Paste):

typescript
// ✅ CORRECT - In every Page component
import { usePage } from '@inertiajs/react';

export default function ReservationsIndex() {
  const { reservations, filters } = usePage().props;
  // reservations is a plain array/object from Laravel.
  
  // ✅ CORRECT for UI toggles
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedId, setSelectedId] = useState<number | null>(null);
}
Polling Strategy (Locked):

Overview: Fetch on mount only. No polling. User clicks refresh button if they want new data.

Reservations List: Poll every 30 seconds (via setInterval) ONLY if the browser tab is visible (document.visibilityState === 'visible').

Notifications Bell: Poll every 10 seconds.

2. API Response Shapes (The "Strict Envelope" Rule)
The Ruling:
ALL API responses (whether Inertia render or JSON endpoint) MUST follow these exact envelopes. Zero exceptions.

The Structure (Locked):

json
// 1. LIST (Index) Responses
{
  "data": [ { id: 1, ... }, { id: 2, ... } ],
  "meta": {
    "total": 100,
    "page": 1,
    "perPage": 25,
    "hasMore": true
  }
}

// 2. SINGLE (Show/Store/Update) Responses
{
  "data": { id: 1, name: "Farai", ... }
}

// 3. ERROR Responses (4xx/5xx)
{
  "message": "The guest name field is required.",
  "errors": {
    "guest_name": ["The guest name field is required."]
  }
}
The Implementation (Backend):

MUST extend a base App\Http\Resources\BaseResource that forces the data wrapper.

MUST use Illuminate\Pagination\LengthAwarePaginator for lists so the meta structure is automatic.

The Implementation (Frontend):

MUST use the pre-built resources/js/lib/api.ts client. NEVER use fetch() or axios directly in a page component.

The api.ts client MUST check the response envelope and throw a ValidationException if errors exists.

3. Timezone Handling (The "UTC is King" Rule)
The Ruling:

The database stores date (DATE) and time (TIME) as separate columns. They are interpreted as "Local Restaurant Time".

When querying, ALWAYS convert to UTC using the Restaurant.timezone field before comparing.

The frontend ALWAYS displays time formatted against the Restaurant.timezone.

The Implementation (Strict Code Block):

php
// app/Services/TimezoneService.php (MUST exist)
class TimezoneService {
    public static function toUtc(string $date, string $time, string $restaurantTz): Carbon {
        return Carbon::createFromFormat('Y-m-d H:i', "$date $time", $restaurantTz)->utc();
    }

    public static function fromUtc(Carbon $utc, string $restaurantTz): string {
        return $utc->copy()->tz($restaurantTz)->format('H:i');
    }
}

// In ReservationService->findConflicts():
$utcStart = TimezoneService::toUtc($date, $time, $restaurant->timezone);
$utcEnd = $utcStart->copy()->addMinutes($slotLength);
// Query DB where the local time converted to UTC falls between these.
Frontend:

MUST use resources/js/lib/timezone.ts to format dates.

FORBIDDEN to use new Date().toLocaleString() directly without passing the restaurant TZ.

4. Form Validation (The "Backend is God" Rule)
The Ruling:

The Laravel FormRequest is the SOLE SOURCE OF TRUTH for validation rules.

Frontend validation is FOR UX ONLY (faster feedback). It MUST exactly mirror the backend rules.

The backend WILL reject invalid data even if the frontend says it's valid.

The Implementation:

Backend: Define rules in app/Http/Requests/*Request.php.

Frontend: Create resources/js/lib/validators.ts.

Action: Copy the rules from the PHP FormRequest into the TS file.

typescript
// resources/js/lib/validators.ts
export const VALIDATION_RULES = {
    guest_name: { required: true, max: 120 },
    party_size: { required: true, min: 1, max: 14 },
    // MUST match StoreReservationRequest.php EXACTLY
};
Submission Rule: Always submit to the backend. If you get a 422 response, display response.data.errors exactly as returned. Never "guess" why it failed.

5. Permission & Role Enforcement (The "No UI Trust" Rule)
The Ruling:

Authentication is handled by Laravel Sanctum (SPA cookies).

Authorization is handled EXCLUSIVELY server-side using Middleware and Policies.

UI buttons are hidden/disabled ONLY for user experience. If the UI fails to hide a button, the server MUST return a 403 Forbidden.

The Implementation (Strict):

php
// 1. Middleware (app/Http/Middleware/EnsureRole.php)
class EnsureRole {
    public function handle($request, $next, $role) {
        $user = $request->user();
        $levels = ['host' => 1, 'manager' => 2, 'owner' => 3];
        if (!isset($levels[$user->role]) || $levels[$user->role] < $levels[$role]) {
            abort(403, 'Insufficient permissions.');
        }
        return $next($request);
    }
}

// 2. Routes (routes/api.php) - MUST gate every route
Route::middleware(['auth:sanctum', 'role:host'])->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']); // host+
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy'])->middleware('role:manager'); // override for delete
    Route::get('/settings', [SettingsController::class, 'index'])->middleware('role:owner'); // owner only
});

// 3. The "Executioner" Test (tests/Feature/RoleAccessTest.php)
// MUST loop through all routes and assert:
// - Host gets 403 on manager/owner routes
// - Manager gets 403 on owner routes
// This test MUST pass before any Phase is considered "Done".
6. Email Deliverability (The "Server-Side Only" Rule)
The Ruling:

Email is sent EXCLUSIVELY via Laravel's Mail Facade (SMTP/Postmark/SES).

FORBIDDEN to use client-side EmailJS, SendGrid Web API from the browser, or any third-party that exposes API keys in the frontend bundle.

The Implementation (Locked):

Use php artisan make:mail BookingConfirmed.

Queue emails (defer to Horizon/Redis if set up, or sync for v1).

Testing: In .env (development), set MAIL_MAILER=log.

Production: Client provides SMTP credentials; they are injected into .env.

php
// app/Http/Controllers/Api/V1/PublicReservationController.php
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingConfirmed;

public function store(StorePublicReservationRequest $request) {
    // ... create reservation ...
    Mail::to($reservation->guest_email)->send(new BookingConfirmed($reservation));
    // If mail fails, LOG THE ERROR and return a 500. DO NOT silently fail.
}
7. Soft Delete Strategy (The "Historical Integrity" Rule)
The Ruling:

Soft deletes are ONLY enabled on: Reservation, Customer, and Table models.

Default Global Scope: EXCLUDE soft-deleted records (->withoutTrashed() is the default in Laravel, so this is automatic).

Explicit Rule: When querying historical data (Audit Log, Analytics "Customer Growth"), you MUST include them using ->withTrashed() if they were deleted to maintain historical accuracy.

The Implementation (Locked):

php
// In App\Models\Reservation.php (and Customer, Table)
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model {
    use SoftDeletes;
    // No global scope overrides. Default is "exclude trashed".
}

// AnalyticsService.php - FOR HISTORICAL COUNTS:
public function getTotalCustomersEver(): int {
    return Customer::withTrashed()->count(); // Include deleted for historical total.
}

// Active List - EXCLUDE DELETED (default):
public function getActiveReservations(): Collection {
    return Reservation::where('date', '>=', now())->get(); // Automatically excludes deleted.
}
8. Testing Strategy (The "No Code without a Net" Rule)
The Ruling:

Backend (Pest): For every single Controller method (index, store, update, delete), there MUST be a corresponding test file in tests/Feature/.

Frontend (Vitest): For every complex React Component (ReservationModal, TableList, etc.), there MUST be a Component.test.tsx file.

The Golden Rule: You CANNOT push a feature branch to the remote repository unless the test suite passes 100%.

The Implementation (Locked Workflow):

bash
# Before you commit, you MUST run:
composer run lint      # PHP syntax/style
composer run larastan  # PHP static analysis
php artisan test       # PHP Feature/Unit tests

npm run test           # JS unit tests
npm run build          # JS compilation

# If any of these fail, the commit is REJECTED.
CI Enforcement:

The .github/workflows/ci.yml file MUST run all of the above on every Pull Request.

If the RoleAccessTest.php fails, the PR cannot be merged.

9. How to Use This Rulebook During Development
Before you write a single line of code for a feature: Re-read the relevant section above.

Writing a reservation form? Read Section 4 (Validation) and Section 1 (State).

Building a settings page? Read Section 5 (Permissions) and Section 2 (API shapes).

If you feel the urge to "wing it": Stop. The rulebook already has the answer.

If a rule genuinely doesn't fit (extremely rare): You must write a proposal in the Pull Request explaining why. The PR will be rejected without this justification.