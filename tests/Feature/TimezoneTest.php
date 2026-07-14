<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\Table;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TimezoneTest extends TestCase
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
    public function conflict_detection_works_with_non_utc_timezone()
    {
        // Change restaurant timezone to something non-UTC
        $this->restaurant->update(['timezone' => 'America/New_York']);

        $date = now()->addDays(2)->format('Y-m-d');

        // First booking at 19:00 EST
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

        // Second booking at 19:30 EST (should conflict)
        $data2 = [
            'guest_name' => 'Second Guest',
            'guest_phone' => '+263 77 222 2222',
            'guest_email' => 'second@example.com',
            'date' => $date,
            'time' => '19:30',
            'party_size' => 2,
            'table_id' => $this->table->id,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        $this->expectException(ValidationException::class);
        ReservationService::createReservation($data2, $this->restaurant);
    }

    #[Test]
    public function conflict_detection_handles_midnight_boundary()
    {
        $this->restaurant->update(['timezone' => 'Africa/Harare']);

        $date = now()->addDays(2)->format('Y-m-d');

        // First booking: 23:00 - 00:30 (crosses midnight)
        $data1 = [
            'guest_name' => 'Late Guest',
            'guest_phone' => '+263 77 111 1111',
            'guest_email' => 'late@example.com',
            'date' => $date,
            'time' => '23:00',
            'party_size' => 2,
            'table_id' => $this->table->id,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        ReservationService::createReservation($data1, $this->restaurant);

        // Second booking: 23:30 - 01:00 (should conflict)
        $data2 = [
            'guest_name' => 'Later Guest',
            'guest_phone' => '+263 77 222 2222',
            'guest_email' => 'later@example.com',
            'date' => $date,
            'time' => '23:30',
            'party_size' => 2,
            'table_id' => $this->table->id,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        $this->expectException(ValidationException::class);
        ReservationService::createReservation($data2, $this->restaurant);
    }
}
