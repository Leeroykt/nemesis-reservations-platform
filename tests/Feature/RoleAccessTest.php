<?php

namespace Tests\Feature;

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
        // Seed the database with the demo data
        $this->artisan('db:seed');
    }

    #[Test]
    public function host_cannot_access_owner_route()
    {
        $host = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($host, 'sanctum');

        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Insufficient permissions. Required: owner']);
    }

    #[Test]
    public function manager_cannot_access_owner_route()
    {
        $manager = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($manager, 'sanctum');

        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Insufficient permissions. Required: owner']);
    }

    #[Test]
    public function owner_can_access_owner_route()
    {
        $owner = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($owner, 'sanctum');

        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Access granted.']);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_protected_route()
    {
        $response = $this->getJson('/api/v1/role-test');
        $response->assertStatus(401);
    }

    #[Test]
    public function host_can_access_me_endpoint()
    {
        $host = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($host, 'sanctum');

        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $host->id]]);
    }

    #[Test]
    public function manager_can_access_me_endpoint()
    {
        $manager = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($manager, 'sanctum');

        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $manager->id]]);
    }

    #[Test]
    public function owner_can_access_me_endpoint()
    {
        $owner = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($owner, 'sanctum');

        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $owner->id]]);
    }
}