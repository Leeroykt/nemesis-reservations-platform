<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
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

        // Ensure rules are set
        $this->restaurant->rules->update(['slot_length_minutes' => 90]);
    }

    #[Test]
    public function lock_for_update_prevents_double_booking()
    {
        $date = now()->addDays(2)->format('Y-m-d');
        $time = '19:00';

        $bookingData = [
            'guest_name' => 'Race Condition Test',
            'guest_phone' => '+263 77 123 4567',
            'guest_email' => 'race@example.com',
            'date' => $date,
            'time' => $time,
            'party_size' => 2,
            'table_id' => $this->table->id,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        $successCount = 0;
        $errorCount = 0;
        $errorMessage = '';

        DB::beginTransaction();

        try {
            // First request - should succeed
            try {
                $result1 = ReservationService::createReservation($bookingData, $this->restaurant);
                $successCount++;
            } catch (ValidationException $e) {
                $errorCount++;
                $errorMessage = json_encode($e->errors());
            }

            // Second request - should fail due to conflict
            try {
                $bookingData2 = $bookingData;
                $bookingData2['guest_name'] = 'Second Request';
                $bookingData2['guest_email'] = 'second@example.com';
                $result2 = ReservationService::createReservation($bookingData2, $this->restaurant);
                $successCount++;
            } catch (ValidationException $e) {
                $errorCount++;
                $errorMessage = json_encode($e->errors());
            }

            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $this->assertEquals(1, $successCount, 'Only one reservation should succeed');
        $this->assertEquals(1, $errorCount, 'One request should fail');
        $this->assertStringContainsString('table_id', $errorMessage, 'Failure should be due to table conflict');
    }

    #[Test]
    public function lock_for_update_prevents_double_booking_auto_assign()
    {
        $date = now()->addDays(2)->format('Y-m-d');
        $time = '19:00';

        // Isolate to a single available table
        $this->restaurant->tables()->where('id', '!=', $this->table->id)
            ->update(['status' => 'Occupied']);

        $bookingData = [
            'guest_name' => 'Auto Assign Race',
            'guest_phone' => '+263 77 123 4567',
            'guest_email' => 'auto@example.com',
            'date' => $date,
            'time' => $time,
            'party_size' => 2,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        $successCount = 0;
        $errorCount = 0;
        $errorMessage = '';

        DB::beginTransaction();

        try {
            // First request - should succeed
            try {
                $result1 = ReservationService::createReservation($bookingData, $this->restaurant);
                $successCount++;
            } catch (ValidationException $e) {
                $errorCount++;
                $errorMessage = json_encode($e->errors());
            }

            // Second request - should fail (no available tables)
            try {
                $bookingData2 = $bookingData;
                $bookingData2['guest_name'] = 'Second Auto Request';
                $bookingData2['guest_email'] = 'secondauto@example.com';
                $result2 = ReservationService::createReservation($bookingData2, $this->restaurant);
                $successCount++;
            } catch (ValidationException $e) {
                $errorCount++;
                $errorMessage = json_encode($e->errors());
            }

            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $this->assertEquals(1, $successCount, 'Only one reservation should succeed');
        $this->assertEquals(1, $errorCount, 'One request should fail');
        $this->assertStringContainsString('No available table', $errorMessage, 'Failure should be due to no available table');
    }

    #[Test]
    public function concurrency_test_with_actual_parallel_requests()
    {
        $date = now()->addDays(2)->format('Y-m-d');
        $time = '19:00';

        // Isolate to a single available table so the first booking uses it
        $this->restaurant->tables()->where('id', '!=', $this->table->id)
            ->update(['status' => 'Occupied']);

        $bookingData = [
            'guest_name' => 'Parallel Guest',
            'guest_phone' => '+263 77 123 4567',
            'guest_email' => 'parallel@example.com',
            'date' => $date,
            'time' => $time,
            'party_size' => 2,
            'source' => 'Website',
            'status' => 'Upcoming',
        ];

        // First request should succeed - the only available table gets booked
        $response1 = $this->postJson('/api/v1/public/reservations', $bookingData);
        $response1->assertStatus(201);
        $response1->assertJsonStructure(['data' => ['public_ref', 'guest_name', 'date', 'time', 'party_size']]);

        // Second request should fail - no available tables left
        $bookingData2 = $bookingData;
        $bookingData2['guest_name'] = 'Second Parallel Guest';
        $bookingData2['guest_email'] = 'parallel2@example.com';

        $response2 = $this->postJson('/api/v1/public/reservations', $bookingData2);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['table_id']);

        // Verify the error message
        $response2->assertJson([
            'errors' => [
                'table_id' => ['No available table for this time and party size.'],
            ],
        ]);

        // Verify only one reservation was created
        $count = Reservation::where('date', $date)
            ->where('time', '19:00')
            ->count();

        $this->assertEquals(1, $count, 'Only one reservation should exist in the database');
    }
}
