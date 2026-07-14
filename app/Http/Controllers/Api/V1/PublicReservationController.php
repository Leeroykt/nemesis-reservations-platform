<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicReservationRequest;
use App\Mail\BookingConfirmed;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Services\AuditLogger;
use App\Services\ReservationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PublicReservationController extends Controller
{
    public function store(StorePublicReservationRequest $request)
    {
        /** @var Restaurant|null $restaurant */
        $restaurant = Restaurant::first();

        if (! $restaurant) {
            return response()->json([
                'message' => 'Restaurant not configured.',
            ], 500);
        }

        $data = $request->validated();
        $data['restaurant_id'] = $restaurant->id;
        $data['source'] = 'Website';
        $data['status'] = 'Upcoming';
        $data['created_by_user_id'] = null;

        /** @var Reservation $reservation */
        $reservation = ReservationService::createReservation($data, $restaurant);

        // Audit log – guest action with custom actor_label
        AuditLogger::log(
            actor: null,
            description: "New booking {$reservation->public_ref} for {$reservation->guest_name} via website",
            entityType: 'reservation',
            entityId: $reservation->id,
            icon: 'bi-plus-circle',
            tone: 'gold',
            actorLabel: 'Guest (website)'
        );

        // Staff notification
        Notification::create([
            'restaurant_id' => $restaurant->id,
            'title' => 'New booking',
            'message' => "{$reservation->guest_name} booked for {$reservation->party_size} on {$reservation->date} at {$reservation->time}",
            'is_read' => false,
        ]);

        // Send email – log failure but don't block response
        if (! empty($reservation->guest_email)) {
            try {
                Mail::to($reservation->guest_email)
                    ->send(new BookingConfirmed($reservation));
            } catch (\Exception $e) {
                Log::error('Booking confirmation email failed', [
                    'reservation_id' => $reservation->id,
                    'email' => $reservation->guest_email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'data' => [
                'public_ref' => $reservation->public_ref,
                'guest_name' => $reservation->guest_name,
                'date' => $reservation->date,
                'time' => $reservation->time,
                'party_size' => $reservation->party_size,
            ],
            'message' => 'Booking confirmed! Check your email.',
        ], 201);
    }
}
