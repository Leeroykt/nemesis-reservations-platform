<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ReservationService
{
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

        // 🔍 DEBUG: Uncomment to see what's being queried
        // dump([
        //     'date' => $date,
        //     'time' => $time,
        //     'utcStart' => $utcStart->toDateTimeString(),
        //     'utcEnd' => $utcEnd->toDateTimeString(),
        //     'slotMinutes' => $slotMinutes,
        // ]);

        $query = Reservation::where('restaurant_id', $restaurant->id)
            ->where('date', $date)
            ->whereNotIn('status', ['Cancelled'])
            ->whereNull('deleted_at');

        if ($excludeTable) {
            $query->where('table_id', '!=', $excludeTable->id);
        }

        $reservations = $query->get();

        // 🔍 DEBUG: Uncomment to see found reservations
        // dump($reservations->toArray());

        $conflictingTableIds = [];

        foreach ($reservations as $reservation) {
            if (!$reservation->table_id) {
                continue;
            }
            $resUtcStart = TimezoneService::toUtc($reservation->date, $reservation->time, $restaurant->timezone);
            $resUtcEnd = $resUtcStart->copy()->addMinutes($slotMinutes);

            // 🔍 DEBUG: Uncomment to see overlap checks
            // dump([
            //     'reservation_id' => $reservation->id,
            //     'resUtcStart' => $resUtcStart->toDateTimeString(),
            //     'resUtcEnd' => $resUtcEnd->toDateTimeString(),
            //     'overlap' => $utcStart < $resUtcEnd && $utcEnd > $resUtcStart,
            // ]);

            if ($utcStart < $resUtcEnd && $utcEnd > $resUtcStart) {
                $conflictingTableIds[] = $reservation->table_id;
            }
        }

        return array_unique($conflictingTableIds);
    }

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

    public static function validateReservation(array &$data, Restaurant $restaurant): void
    {
        $rules = $restaurant->rules;

        // Validate party size
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

        // Validate date is not in the past
        $today = Carbon::now($restaurant->timezone)->toDateString();
        if ($data['date'] < $today) {
            throw ValidationException::withMessages([
                'date' => 'Date cannot be in the past.',
            ]);
        }

        // If a specific table is provided, check conflicts
        if (!empty($data['table_id'])) {
            $table = Table::find($data['table_id']);
            if (!$table || $table->restaurant_id != $restaurant->id) {
                throw ValidationException::withMessages([
                    'table_id' => 'Invalid table selected.',
                ]);
            }

            // 🔥 CRITICAL: Check conflicts on ALL tables (do NOT exclude the chosen table)
            $conflicts = self::findConflicts(
                $restaurant,
                $data['date'],
                $data['time'],
                $data['party_size'],
                $rules->slot_length_minutes,
                null // <- DO NOT EXCLUDE
            );

            // 🔍 DEBUG: Uncomment to see conflicts
            // dump([
            //     'table_id' => $table->id,
            //     'conflicts' => $conflicts,
            //     'in_array' => in_array($table->id, $conflicts),
            // ]);

            if (in_array($table->id, $conflicts)) {
                throw ValidationException::withMessages([
                    'table_id' => 'This table is already booked at that time.',
                ]);
            }
        } else {
            // Auto-assign a table
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

            $data['auto_assigned_table_id'] = $autoTable->id;
        }
    }

    public static function generatePublicRef(Restaurant $restaurant): string
    {
        do {
            $ref = 'RB-' . str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (Reservation::where('restaurant_id', $restaurant->id)->where('public_ref', $ref)->exists());

        return $ref;
    }
}