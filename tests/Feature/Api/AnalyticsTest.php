<?php

namespace Tests\Feature\Api;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
    }

    #[Test]
    public function manager_can_view_peak_hours()
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/analytics/peak-hours');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['labels', 'covers'],
            ]);
        $this->assertIsArray($response->json('data.labels'));
    }

    #[Test]
    public function manager_can_view_popular_tables()
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/analytics/popular-tables');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['labels', 'bookings'],
            ]);
        $this->assertLessThanOrEqual(5, count($response->json('data.labels')));
    }

    #[Test]
    public function manager_can_view_customer_growth()
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/analytics/customer-growth');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['labels', 'newCustomers', 'returning'],
            ]);
        $this->assertIsArray($response->json('data.newCustomers'));
    }

    #[Test]
    public function host_cannot_view_analytics()
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/analytics/peak-hours');
        $response->assertStatus(403);
    }

    #[Test]
    public function unauthenticated_user_cannot_view_analytics()
    {
        $response = $this->getJson('/api/v1/analytics/peak-hours');
        $response->assertStatus(401);
    }

    #[Test]
    public function analytics_returns_empty_arrays_when_no_data()
    {
        // Clear all reservations
        Reservation::truncate();

        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/analytics/peak-hours');
        $response->assertStatus(200)
            ->assertJson(['data' => ['labels' => [], 'covers' => []]]);
    }
}
