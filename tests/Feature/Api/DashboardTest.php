<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
    }

    #[Test]
    public function authenticated_user_can_view_kpis()
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/dashboard/kpis');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'todayReservations',
                    'todayReservationsDelta',
                    'upcomingReservations',
                    'upcomingReservationsDelta',
                    'tablesAvailable',
                    'tablesOccupied',
                    'walkIns',
                    'walkInsDelta',
                    'revenueToday',
                    'revenueDelta',
                    'avgPartySize',
                    'noShowRate',
                ],
            ]);
        $this->assertIsNumeric($response->json('data.todayReservations'));
    }

    #[Test]
    public function authenticated_user_can_view_revenue_trend()
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/dashboard/revenue-trend');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['labels', 'thisWeek', 'lastWeek'],
            ]);
        $this->assertCount(7, $response->json('data.thisWeek'));
        $this->assertCount(7, $response->json('data.lastWeek'));
    }

    #[Test]
    public function authenticated_user_can_view_status_breakdown()
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/dashboard/status-breakdown');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['labels', 'values'],
            ]);
        $this->assertCount(4, $response->json('data.labels'));
        $this->assertCount(4, $response->json('data.values'));
    }

    #[Test]
    public function authenticated_user_can_view_activity_feed()
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/activity');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'icon', 'tone', 'text', 'time'],
                ],
            ]);
        $this->assertLessThanOrEqual(12, count($response->json('data')));
    }

    #[Test]
    public function unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/v1/dashboard/kpis');
        $response->assertStatus(401);
    }

    #[Test]
    public function host_can_access_dashboard()
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/dashboard/kpis');
        $response->assertStatus(200);
    }
}
