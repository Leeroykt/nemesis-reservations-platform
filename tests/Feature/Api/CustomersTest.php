<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
    }

    #[Test]
    public function manager_can_list_customers(): void
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/customers');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'phone', 'visits', 'is_vip'],
                ],
            ]);
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    #[Test]
    public function manager_can_search_customers_by_name(): void
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $customer = Customer::where('restaurant_id', $user->restaurant_id)->first();
        $response = $this->getJson('/api/v1/customers?search='.urlencode(substr($customer->name, 0, 3)));
        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json('data')));
        $this->assertEquals($customer->name, $response->json('data')[0]['name']);
    }

    #[Test]
    public function manager_can_view_single_customer_with_preferences_and_reservations(): void
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $customer = Customer::first();
        $response = $this->getJson("/api/v1/customers/{$customer->id}");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'visits',
                    'is_vip',
                    'preferences',
                    'reservations',
                ],
            ]);
        $this->assertIsArray($response->json('data.preferences'));
        $this->assertIsArray($response->json('data.reservations'));
    }

    #[Test]
    public function host_cannot_access_customers(): void
    {
        $user = User::where('email', 'host@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/customers');
        $response->assertStatus(403);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_customers(): void
    {
        $response = $this->getJson('/api/v1/customers');
        $response->assertStatus(401);
    }

    #[Test]
    public function manager_receives_404_for_non_existent_customer(): void
    {
        $user = User::where('email', 'manager@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/customers/999999');
        $response->assertStatus(404);
    }
}
