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

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database to ensure users exist
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');

        // Ensure test users exist even if seeding fails
        $this->ensureUser('owner@signetandvine.co.zw', 'owner');
        $this->ensureUser('manager@signetandvine.co.zw', 'manager');
        $this->ensureUser('host@signetandvine.co.zw', 'host');
    }

    protected function ensureUser(string $email, string $role): void
    {
        if (! User::where('email', $email)->exists()) {
            $restaurant = Restaurant::first();
            User::create([
                'name' => ucfirst($role).' User',
                'email' => $email,
                'password' => bcrypt('password'),
                'role' => $role,
                'restaurant_id' => $restaurant->id,
            ]);
        }
    }

    #[Test]
    public function host_cannot_access_owner_route(): void
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(403)
            ->assertJson(['message' => 'Insufficient permissions. Required: owner']);
    }

    #[Test]
    public function manager_cannot_access_owner_route(): void
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(403)
            ->assertJson(['message' => 'Insufficient permissions. Required: owner']);
    }

    #[Test]
    public function owner_can_access_owner_route(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(200)
            ->assertJson(['message' => 'Access granted.']);
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
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $user->id]]);
    }

    #[Test]
    public function manager_can_access_me_endpoint(): void
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $user->id]]);
    }

    #[Test]
    public function owner_can_access_me_endpoint(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $user->id]]);
    }

    // ---- New phase 4 role tests ----

    #[Test]
    public function host_cannot_access_customers_endpoint(): void
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/customers');
        $response->assertStatus(403);
    }

    #[Test]
    public function manager_can_access_analytics_endpoints(): void
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/analytics/peak-hours');
        $response->assertStatus(200);
    }

    #[Test]
    public function host_cannot_access_analytics_endpoints(): void
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/analytics/peak-hours');
        $response->assertStatus(403);
    }
}
