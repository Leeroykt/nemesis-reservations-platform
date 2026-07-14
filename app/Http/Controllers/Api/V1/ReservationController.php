<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ReservationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        /** @var Restaurant|null $restaurant */
        $restaurant = Restaurant::find($user->restaurant_id);

        if (! $restaurant) {
            abort(404, 'Restaurant not found.');
        }

        $query = Reservation::where('restaurant_id', $restaurant->id)
            ->with(['table', 'createdBy']);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }
        if ($request->has('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        if ($request->has('search')) {
            $query->where('guest_name', 'like', '%'.$request->input('search').'%');
        }

        $perPage = $request->input('per_page', 25);
        $reservations = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->paginate($perPage);

        return ReservationResource::collection($reservations);
    }

    public function store(StoreReservationRequest $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        /** @var Restaurant|null $restaurant */
        $restaurant = Restaurant::find($user->restaurant_id);

        if (! $restaurant) {
            abort(404, 'Restaurant not found.');
        }

        $data = $request->validated();
        $data['created_by_user_id'] = $user->id;
        $data['restaurant_id'] = $restaurant->id;

        ReservationService::validateReservation($data, $restaurant);

        if (isset($data['auto_assigned_table_id'])) {
            $data['table_id'] = $data['auto_assigned_table_id'];
            unset($data['auto_assigned_table_id']);
        }

        if (empty($data['status'])) {
            $data['status'] = 'Upcoming';
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

        return new ReservationResource($reservation->load(['table', 'createdBy']));
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        return new ReservationResource($reservation->load(['table', 'createdBy']));
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('update', $reservation);

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        /** @var Restaurant|null $restaurant */
        $restaurant = Restaurant::find($user->restaurant_id);

        if (! $restaurant) {
            abort(404, 'Restaurant not found.');
        }

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

        return new ReservationResource($reservation->load(['table', 'createdBy']));
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('delete', $reservation);

        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

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

    public function bulk(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (! in_array($action, ['confirm', 'cancel', 'delete'])) {
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
            "Bulk $action on ".count($ids).' reservation(s)',
            'reservation',
            null,
            'bi-layers',
            'slate'
        );

        return response()->json(['message' => "$action completed for ".count($ids).' reservation(s).']);
    }
}
