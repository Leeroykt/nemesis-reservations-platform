<?php

namespace Tests\Feature\Api;

use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TablesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
    }

    #[Test]
    public function host_can_list_tables()
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/tables');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'zone', 'capacity', 'shape', 'status'],
                ],
            ]);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    #[Test]
    public function host_can_update_table_status()
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $table = Table::first();
        $response = $this->patchJson("/api/v1/tables/{$table->id}/status", [
            'status' => 'Reserved',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'Reserved');
        $this->assertDatabaseHas('tables', ['id' => $table->id, 'status' => 'Reserved']);
    }

    #[Test]
    public function table_status_update_fails_with_invalid_status()
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $table = Table::first();
        $response = $this->patchJson("/api/v1/tables/{$table->id}/status", [
            'status' => 'InvalidStatus',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function unauthenticated_user_cannot_list_tables()
    {
        $response = $this->getJson('/api/v1/tables');
        $response->assertStatus(401);
    }

    #[Test]
    public function host_can_update_any_table_in_restaurant()
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $table = Table::where('restaurant_id', $user->restaurant_id)->first();
        $response = $this->patchJson("/api/v1/tables/{$table->id}/status", [
            'status' => 'Cleaning',
        ]);
        $response->assertStatus(200);
    }
}
