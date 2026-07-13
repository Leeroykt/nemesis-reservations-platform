<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $host;

    private User $manager;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test restaurant
        $restaurant = Restaurant::create([
            'name' => 'Test Restaurant',
            'timezone' => 'Africa/Harare',
            'currency' => 'USD',
        ]);

        // Create users for each role
        $this->host = User::create([
            'name' => 'Host User',
            'email' => 'host@test.com',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'role' => 'host',
        ]);

        $this->manager = User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'role' => 'manager',
        ]);

        $this->owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'restaurant_id' => $restaurant->id,
            'role' => 'owner',
        ]);
    }

    #[Test]
    public function host_cannot_access_owner_route(): void
    {
        $this->actingAs($this->host, 'sanctum');
        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Insufficient permissions. Required: owner']);
    }

    #[Test]
    public function manager_cannot_access_owner_route(): void
    {
        $this->actingAs($this->manager, 'sanctum');
        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Insufficient permissions. Required: owner']);
    }

    #[Test]
    public function owner_can_access_owner_route(): void
    {
        $this->actingAs($this->owner, 'sanctum');
        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Access granted.']);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(401);
    }

    #[Test]
    public function host_can_access_me_endpoint(): void
    {
        $this->actingAs($this->host, 'sanctum');
        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $this->host->id]]);
    }

    #[Test]
    public function manager_can_access_me_endpoint(): void
    {
        $this->actingAs($this->manager, 'sanctum');
        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $this->manager->id]]);
    }

    #[Test]
    public function owner_can_access_me_endpoint(): void
    {
        $this->actingAs($this->owner, 'sanctum');
        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $this->owner->id]]);
    }
}
