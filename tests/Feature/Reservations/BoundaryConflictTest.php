<?php

namespace Tests\Feature\Reservations;

use App\Models\Restaurant;
use App\Models\Table;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BoundaryConflictTest extends TestCase
{
    use DatabaseMigrations;

    protected Restaurant $restaurant;

    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');

        /** @var Restaurant $restaurant */
        $restaurant = Restaurant::first();
        $this->restaurant = $restaurant;
        $this->table = $this->restaurant->tables()->first();

        $this->restaurant->rules->update(['slot_length_minutes' => 90]);
    }

    #[Test]
    public function booking_ending_exactly_when_another_starts_should_not_conflict()
    {
        $date = now()->addDays(2)->format('Y-m-d');

        // First booking: 19:00 - 20:30 (90 min slot)
        $data1 = [
            'guest_name' => 'First Guest',
            'guest_phone' => '+263 77 111 1111',
            'guest_email' => 'first@example.com',
            'date' => $date,
            'time' => '19:00',
            'party_size' => 2,
            'table_id' => $this->table->id,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        ReservationService::createReservation($data1, $this->restaurant);

        // Second booking: 20:30 - 22:00 (starts exactly when first ends)
        $data2 = [
            'guest_name' => 'Second Guest',
            'guest_phone' => '+263 77 222 2222',
            'guest_email' => 'second@example.com',
            'date' => $date,
            'time' => '20:30',
            'party_size' => 2,
            'table_id' => $this->table->id,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        // Should NOT throw an exception (back-to-back is allowed)
        $result = ReservationService::createReservation($data2, $this->restaurant);
        $this->assertNotNull($result);
    }

    #[Test]
    public function booking_ending_one_minute_after_another_starts_should_conflict()
    {
        $date = now()->addDays(2)->format('Y-m-d');

        // First booking: 19:00 - 20:30
        $data1 = [
            'guest_name' => 'First Guest',
            'guest_phone' => '+263 77 111 1111',
            'guest_email' => 'first@example.com',
            'date' => $date,
            'time' => '19:00',
            'party_size' => 2,
            'table_id' => $this->table->id,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        ReservationService::createReservation($data1, $this->restaurant);

        // Second booking: 20:29 - 21:59 (overlaps by 1 minute)
        $data2 = [
            'guest_name' => 'Second Guest',
            'guest_phone' => '+263 77 222 2222',
            'guest_email' => 'second@example.com',
            'date' => $date,
            'time' => '20:29',
            'party_size' => 2,
            'table_id' => $this->table->id,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        // Should throw ValidationException (overlap)
        $this->expectException(ValidationException::class);
        ReservationService::createReservation($data2, $this->restaurant);
    }
}
