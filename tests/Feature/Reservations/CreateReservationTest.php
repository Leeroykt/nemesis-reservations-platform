<?php

namespace Tests\Feature\Reservations;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateReservationTest extends TestCase
{
    use RefreshDatabase;

    protected Restaurant $restaurant;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->restaurant = Restaurant::first();
        $this->owner = User::where('role', 'owner')->first();
        $this->actingAs($this->owner, 'sanctum');
    }

    #[Test]
    public function can_create_reservation_with_valid_data(): void
    {
        $data = [
            'guest_name' => 'Test Guest',
            'guest_phone' => '+263 77 123 4567',
            'guest_email' => 'test@example.com',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 4,
            'source' => 'Website',
            'notes' => 'Window seat please',
        ];

        $response = $this->postJson('/api/v1/reservations', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.guest_name', 'Test Guest')
            ->assertJsonPath('data.party_size', 4)
            ->assertJsonPath('data.status', 'Upcoming') // ✅ This will now pass
            ->assertJsonPath('data.source', 'Website')
            ->assertJsonStructure(['data' => ['id', 'public_ref', 'guest_name', 'date', 'time', 'party_size', 'status']]);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'Test Guest',
            'restaurant_id' => $this->restaurant->id,
        ]);
    }

    #[Test]
    public function validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/reservations', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_name', 'guest_phone', 'date', 'time', 'party_size']);
    }

    #[Test]
    public function validates_party_size_does_not_exceed_max(): void
    {
        $data = [
            'guest_name' => 'Too Many Guests',
            'guest_phone' => '+263 77 123 4567',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 20,
        ];

        $response = $this->postJson('/api/v1/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['party_size']);
    }

    #[Test]
    public function rejects_past_date(): void
    {
        $data = [
            'guest_name' => 'Past Date',
            'guest_phone' => '+263 77 123 4567',
            'date' => now()->subDay()->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 2,
        ];

        $response = $this->postJson('/api/v1/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    #[Test]
    public function rejects_conflicting_booking_on_same_table(): void
    {
        $firstData = [
            'guest_name' => 'First Guest',
            'guest_phone' => '+263 77 111 1111',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 2,
            'table_id' => Table::first()->id,
        ];
        $this->postJson('/api/v1/reservations', $firstData)->assertStatus(201);

        $secondData = [
            'guest_name' => 'Second Guest',
            'guest_phone' => '+263 77 222 2222',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '19:30',
            'party_size' => 2,
            'table_id' => Table::first()->id,
        ];
        $response = $this->postJson('/api/v1/reservations', $secondData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['table_id']);
    }

    #[Test]
    public function auto_assigns_table_if_none_provided(): void
    {
        $data = [
            'guest_name' => 'Auto Assign',
            'guest_phone' => '+263 77 333 3333',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 4,
        ];

        $response = $this->postJson('/api/v1/reservations', $data);
        $response->assertStatus(201)
            ->assertJsonPath('data.table.id', function ($id) {
                return !is_null($id);
            });
    }

    #[Test]
    public function logs_activity_when_creating_reservation(): void
    {
        $data = [
            'guest_name' => 'Activity Log Test',
            'guest_phone' => '+263 77 444 4444',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 2,
        ];

        $response = $this->postJson('/api/v1/reservations', $data);
        $response->assertStatus(201);

        $publicRef = $response->json('data.public_ref');
        $guestName = $response->json('data.guest_name');

        $this->assertDatabaseHas('activity_log', [
            'description' => "Created reservation {$publicRef} for {$guestName}",
            'entity_type' => 'reservation',
        ]);
    }
}