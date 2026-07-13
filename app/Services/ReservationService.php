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
        ?Table $excludeTable = null,
        ?int $tableId = null
    ): array {
        $utcStart = TimezoneService::toUtc($date, $time, $restaurant->timezone);
        $utcEnd = $utcStart->copy()->addMinutes($slotMinutes);

        $query = Reservation::where('restaurant_id', $restaurant->id)
            ->where('date', $date)
            ->whereNotIn('status', ['Cancelled'])
            ->whereNull('deleted_at');

        if ($tableId !== null) {
            $query->where('table_id', $tableId);
        }

        if ($excludeTable) {
            $query->where('table_id', '!=', $excludeTable->id);
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Reservation> $reservations */
        $reservations = $query->get();
        $conflictingTableIds = [];

        foreach ($reservations as $reservation) {
            if (!$reservation->table_id) {
                continue;
            }
            $resUtcStart = TimezoneService::toUtc($reservation->date, $reservation->time, $restaurant->timezone);
            $resUtcEnd = $resUtcStart->copy()->addMinutes($slotMinutes);

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
        /** @var \Illuminate\Database\Eloquent\Collection<int, Table> $tables */
        $tables = Table::where('restaurant_id', $restaurant->id)
            ->where('capacity', '>=', $partySize)
            ->where('status', 'Available')
            ->get();

        $conflictingIds = self::findConflicts($restaurant, $date, $time, $partySize, $slotMinutes, null, null);

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

        $today = Carbon::now($restaurant->timezone)->toDateString();
        if ($data['date'] < $today) {
            throw ValidationException::withMessages([
                'date' => 'Date cannot be in the past.',
            ]);
        }

        if (!empty($data['table_id'])) {
            /** @var Table|null $table */
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
                null,
                $table->id
            );

            if (in_array($table->id, $conflicts)) {
                throw ValidationException::withMessages([
                    'table_id' => 'This table is already booked at that time.',
                ]);
            }
        } else {
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
            $ref = 'RB-' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (Reservation::where('restaurant_id', $restaurant->id)->where('public_ref', $ref)->exists());

        return $ref;
    }
}