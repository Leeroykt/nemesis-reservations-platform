<?php

namespace Tests\Feature\Reservations;

use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BulkActionsTest extends TestCase
{
    use RefreshDatabase;

    protected Restaurant $restaurant;
    protected User $owner;
    protected User $manager;
    protected User $host;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->restaurant = Restaurant::first();
        $this->owner = User::where('role', 'owner')->first();
        $this->manager = User::where('role', 'manager')->first();
        $this->host = User::where('role', 'host')->first();
    }

    #[Test]
    public function allows_manager_to_bulk_confirm(): void
    {
        $this->actingAs($this->manager, 'sanctum');
        $reservations = Reservation::take(2)->get();
        $ids = $reservations->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/reservations/bulk', [
            'action' => 'confirm',
            'ids' => $ids,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'confirm completed for 2 reservation(s).']);

        foreach ($reservations as $res) {
            $this->assertDatabaseHas('reservations', [
                'id' => $res->id,
                'status' => 'Confirmed',
            ]);
        }
    }

    #[Test]
    public function allows_manager_to_bulk_cancel(): void
    {
        $this->actingAs($this->manager, 'sanctum');
        $reservations = Reservation::take(2)->get();
        $ids = $reservations->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/reservations/bulk', [
            'action' => 'cancel',
            'ids' => $ids,
        ]);

        $response->assertStatus(200);
        foreach ($reservations as $res) {
            $this->assertDatabaseHas('reservations', [
                'id' => $res->id,
                'status' => 'Cancelled',
            ]);
        }
    }

    #[Test]
    public function allows_manager_to_bulk_delete(): void
    {
        $this->actingAs($this->manager, 'sanctum');
        $reservations = Reservation::take(2)->get();
        $ids = $reservations->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/reservations/bulk', [
            'action' => 'delete',
            'ids' => $ids,
        ]);

        $response->assertStatus(200);
        foreach ($reservations as $res) {
            $this->assertSoftDeleted('reservations', ['id' => $res->id]);
        }
    }

    #[Test]
    public function blocks_host_from_bulk_delete(): void
    {
        $this->actingAs($this->host, 'sanctum');
        $ids = Reservation::take(2)->pluck('id')->toArray();

        $response = $this->postJson('/api/v1/reservations/bulk', [
            'action' => 'delete',
            'ids' => $ids,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function validates_action_is_allowed(): void
    {
        $this->actingAs($this->manager, 'sanctum');
        $response = $this->postJson('/api/v1/reservations/bulk', [
            'action' => 'invalid',
            'ids' => [1, 2],
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action']);
    }

    #[Test]
    public function validates_ids_are_provided(): void
    {
        $this->actingAs($this->manager, 'sanctum');
        $response = $this->postJson('/api/v1/reservations/bulk', [
            'action' => 'confirm',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    }
}