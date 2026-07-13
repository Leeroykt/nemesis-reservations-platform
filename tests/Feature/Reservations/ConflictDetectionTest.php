<?php

namespace Tests\Feature\Reservations;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConflictDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected Restaurant $restaurant;
    protected User $owner;
    protected Table $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->restaurant = Restaurant::first();
        $this->owner = User::where('role', 'owner')->first();
        $this->actingAs($this->owner, 'sanctum');
        $this->table = Table::first();
    }

    #[Test]
    public function allows_two_non_overlapping_reservations_on_same_table(): void
    {
        $first = [
            'guest_name' => 'First',
            'guest_phone' => '+263 77 111 1111',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '17:00',
            'party_size' => 2,
            'table_id' => $this->table->id,
        ];
        $this->postJson('/api/v1/reservations', $first)->assertStatus(201);

        $second = [
            'guest_name' => 'Second',
            'guest_phone' => '+263 77 222 2222',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '20:00',
            'party_size' => 2,
            'table_id' => $this->table->id,
        ];
        $this->postJson('/api/v1/reservations', $second)->assertStatus(201);
    }

    #[Test]
    public function blocks_overlapping_reservation_on_same_table(): void
    {
        $first = [
            'guest_name' => 'First',
            'guest_phone' => '+263 77 111 1111',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '18:30',
            'party_size' => 2,
            'table_id' => $this->table->id,
        ];
        $this->postJson('/api/v1/reservations', $first)->assertStatus(201);

        $second = [
            'guest_name' => 'Second',
            'guest_phone' => '+263 77 222 2222',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 2,
            'table_id' => $this->table->id,
        ];
        $response = $this->postJson('/api/v1/reservations', $second);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['table_id']);
    }

    #[Test]
    public function ignores_cancelled_reservations_when_detecting_conflicts(): void
    {
        $first = [
            'guest_name' => 'First',
            'guest_phone' => '+263 77 111 1111',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '18:30',
            'party_size' => 2,
            'table_id' => $this->table->id,
            'status' => 'Cancelled',
        ];
        $this->postJson('/api/v1/reservations', $first)->assertStatus(201);

        $second = [
            'guest_name' => 'Second',
            'guest_phone' => '+263 77 222 2222',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '18:45',
            'party_size' => 2,
            'table_id' => $this->table->id,
        ];
        $this->postJson('/api/v1/reservations', $second)->assertStatus(201);
    }

    #[Test]
    public function ignores_soft_deleted_reservations_when_detecting_conflicts(): void
    {
        $first = [
            'guest_name' => 'First',
            'guest_phone' => '+263 77 111 1111',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '18:30',
            'party_size' => 2,
            'table_id' => $this->table->id,
        ];
        $response = $this->postJson('/api/v1/reservations', $first);
        $reservationId = $response->json('data.id');
        Reservation::find($reservationId)->delete();

        $second = [
            'guest_name' => 'Second',
            'guest_phone' => '+263 77 222 2222',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '18:45',
            'party_size' => 2,
            'table_id' => $this->table->id,
        ];
        $this->postJson('/api/v1/reservations', $second)->assertStatus(201);
    }
}