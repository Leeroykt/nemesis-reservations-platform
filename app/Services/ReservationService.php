<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /**
     * Find conflicting tables for a given date/time/party.
     *
     * @return array<int> – table IDs that conflict
     */
    public static function findConflicts(
        Restaurant $restaurant,
        string $date,
        string $time,
        int $partySize,
        int $slotMinutes,
        ?Table $excludeTable = null
    ): array {
        $utcStart = TimezoneService::toUtc($date, $time, $restaurant->timezone);
        $utcEnd = $utcStart->copy()->addMinutes($slotMinutes);

        // Get all reservations on that date that are not cancelled/soft-deleted
        $query = Reservation::where('restaurant_id', $restaurant->id)
            ->where('date', $date)
            ->whereNotIn('status', ['Cancelled'])
            ->whereNull('deleted_at');

        if ($excludeTable) {
            $query->where('table_id', '!=', $excludeTable->id);
        }

        $reservations = $query->get();

        $conflictingTableIds = [];

        foreach ($reservations as $reservation) {
            $resUtcStart = TimezoneService::toUtc($reservation->date, $reservation->time, $restaurant->timezone);
            $resUtcEnd = $resUtcStart->copy()->addMinutes($slotMinutes);

            // Check overlap
            if ($utcStart < $resUtcEnd && $utcEnd > $resUtcStart) {
                if ($reservation->table_id) {
                    $conflictingTableIds[] = $reservation->table_id;
                }
            }
        }

        return array_unique($conflictingTableIds);
    }

    /**
     * Find an available table that fits the party.
     */
    public static function findAvailableTable(
        Restaurant $restaurant,
        int $partySize,
        string $date,
        string $time,
        int $slotMinutes
    ): ?Table {
        $tables = Table::where('restaurant_id', $restaurant->id)
            ->where('capacity', '>=', $partySize)
            ->where('status', 'Available')
            ->get();

        $conflictingIds = self::findConflicts($restaurant, $date, $time, $partySize, $slotMinutes);

        foreach ($tables as $table) {
            if (!in_array($table->id, $conflictingIds)) {
                return $table;
            }
        }

        return null;
    }

    /**
     * Validate a reservation request.
     *
     * @throws ValidationException
     */
    public static function validateReservation(array $data, Restaurant $restaurant): void
    {
        $rules = $restaurant->rules;

        // Party size
        if ($data['party_size'] > $rules->max_party_size) {
            throw ValidationException::withMessages([
                'party_size' => "Maximum party size is {$rules->max_party_size}.",
            ]);
        }

        if ($data['party_size'] < 1) {
            throw ValidationException::withMessages([
                'party_size' => 'Party size must be at least 1.',
            ]);
        }

        // Date must be today or future
        $today = Carbon::now($restaurant->timezone)->toDateString();
        if ($data['date'] < $today) {
            throw ValidationException::withMessages([
                'date' => 'Date cannot be in the past.',
            ]);
        }

        // Time must be within opening hours (simplified – we could check hours later)
        // We'll skip hour validation for now to keep it simple.

        // Check if table is assigned and conflict-free
        if (!empty($data['table_id'])) {
            $table = Table::find($data['table_id']);
            if (!$table || $table->restaurant_id != $restaurant->id) {
                throw ValidationException::withMessages([
                    'table_id' => 'Invalid table selected.',
                ]);
            }

            $conflicts = self::findConflicts(
                $restaurant,
                $data['date'],
                $data['time'],
                $data['party_size'],
                $rules->slot_length_minutes,
                $table
            );

            if (!empty($conflicts)) {
                throw ValidationException::withMessages([
                    'table_id' => 'This table is already booked at that time.',
                ]);
            }
        } else {
            // Auto-assign if no table provided
            $autoTable = self::findAvailableTable(
                $restaurant,
                $data['party_size'],
                $data['date'],
                $data['time'],
                $rules->slot_length_minutes
            );

            if (!$autoTable) {
                throw ValidationException::withMessages([
                    'table_id' => 'No available table for this time and party size.',
                ]);
            }

            // We'll set it later in the controller.
            $data['auto_assigned_table_id'] = $autoTable->id;
        }
    }

    /**
     * Create a reservation with validation.
     */
    public static function createReservation(array $data, Restaurant $restaurant): Reservation
    {
        self::validateReservation($data, $restaurant);

        // If auto-assigned, set table_id
        if (empty($data['table_id']) && isset($data['auto_assigned_table_id'])) {
            $data['table_id'] = $data['auto_assigned_table_id'];
        }

        // Generate public_ref
        $data['public_ref'] = self::generatePublicRef($restaurant);

        // Set source if not provided
        if (empty($data['source'])) {
            $data['source'] = 'App';
        }

        // Set status if not provided (default Upcoming)
        if (empty($data['status'])) {
            $data['status'] = 'Upcoming';
        }

        return $restaurant->reservations()->create($data);
    }

    /**
     * Generate a unique public reference.
     */
    private static function generatePublicRef(Restaurant $restaurant): string
    {
        do {
            $ref = 'RB-' . str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (Reservation::where('restaurant_id', $restaurant->id)->where('public_ref', $ref)->exists());

        return $ref;
    }
}