<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Services\AuditLogger;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    /**
     * List reservations (paginated, filterable).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $restaurant = Restaurant::find($user->restaurant_id);

        $query = Reservation::where('restaurant_id', $restaurant->id)
            ->with(['customer', 'table', 'createdBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        if ($request->has('search')) {
            $query->where('guest_name', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->get('per_page', 25);
        $reservations = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->paginate($perPage);

        return ReservationResource::collection($reservations);
    }

    /**
     * Create a reservation.
     */
    public function store(StoreReservationRequest $request)
    {
        $user = Auth::user();
        $restaurant = Restaurant::find($user->restaurant_id);

        $data = $request->validated();
        $data['created_by_user_id'] = $user->id;
        $data['restaurant_id'] = $restaurant->id;

        // Validate and auto-assign via service
        ReservationService::validateReservation($data, $restaurant);

        if (isset($data['auto_assigned_table_id'])) {
            $data['table_id'] = $data['auto_assigned_table_id'];
            unset($data['auto_assigned_table_id']);
        }

        $data['public_ref'] = ReservationService::generatePublicRef($restaurant);

        $reservation = $restaurant->reservations()->create($data);

        AuditLogger::log(
            $user,
            "Created reservation {$reservation->public_ref} for {$reservation->guest_name}",
            'reservation',
            $reservation->id,
            'bi-plus-circle',
            'gold'
        );

        return new ReservationResource($reservation->load(['customer', 'table', 'createdBy']));
    }

    /**
     * Show a single reservation.
     */
    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        return new ReservationResource($reservation->load(['customer', 'table', 'createdBy']));
    }

    /**
     * Update a reservation.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        $user = Auth::user();
        $restaurant = Restaurant::find($user->restaurant_id);

        $data = $request->validated();

        if (isset($data['date']) || isset($data['time']) || isset($data['party_size']) || isset($data['table_id'])) {
            $validateData = array_merge($reservation->toArray(), $data);
            ReservationService::validateReservation($validateData, $restaurant);
        }

        $reservation->update($data);

        AuditLogger::log(
            $user,
            "Updated reservation {$reservation->public_ref}",
            'reservation',
            $reservation->id,
            'bi-pencil',
            'slate'
        );

        return new ReservationResource($reservation->load(['customer', 'table', 'createdBy']));
    }

    /**
     * Delete a reservation (soft-delete) – manager+ only.
     */
    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);

        $user = Auth::user();
        $reservation->delete();

        AuditLogger::log(
            $user,
            "Deleted reservation {$reservation->public_ref}",
            'reservation',
            $reservation->id,
            'bi-trash',
            'rust'
        );

        return response()->json(['message' => 'Reservation deleted.']);
    }

    /**
     * Bulk actions: confirm, cancel, delete (manager+).
     */
    public function bulk(Request $request)
    {
        $user = Auth::user();
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (!in_array($action, ['confirm', 'cancel', 'delete'])) {
            throw ValidationException::withMessages([
                'action' => 'Invalid action. Allowed: confirm, cancel, delete.',
            ]);
        }

        if (empty($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'No reservations selected.',
            ]);
        }

        $reservations = Reservation::whereIn('id', $ids)->get();

        foreach ($reservations as $reservation) {
            if ($action === 'delete') {
                $this->authorize('delete', $reservation);
            } else {
                $this->authorize('update', $reservation);
            }

            switch ($action) {
                case 'confirm':
                    $reservation->update(['status' => 'Confirmed']);
                    break;
                case 'cancel':
                    $reservation->update(['status' => 'Cancelled']);
                    break;
                case 'delete':
                    $reservation->delete();
                    break;
            }
        }

        AuditLogger::log(
            $user,
            "Bulk $action on " . count($ids) . " reservation(s)",
            'reservation',
            null,
            'bi-layers',
            'slate'
        );

        return response()->json(['message' => "$action completed for " . count($ids) . " reservation(s)."]);
    }
}