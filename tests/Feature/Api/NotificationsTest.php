<?php

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');
    }

    #[Test]
    public function authenticated_user_can_list_notifications_paginated(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'message',
                        'is_read',
                        'created_at',
                    ],
                ],
                'meta' => [
                    'total',
                    'page',
                    'perPage',
                    'hasMore',
                ],
            ]);

        $this->assertLessThanOrEqual(15, count($response->json('data')));
        $this->assertIsInt($response->json('meta.total'));
        $this->assertIsInt($response->json('meta.page'));
        $this->assertIsInt($response->json('meta.perPage'));
        $this->assertIsBool($response->json('meta.hasMore'));
    }

    #[Test]
    public function authenticated_user_can_filter_notifications_by_read_status(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        // Mark one notification as read
        $notification = Notification::where('restaurant_id', $user->restaurant_id)->first();
        $notification->update(['is_read' => true]);

        // Filter for read notifications
        $response = $this->getJson('/api/v1/notifications?read=true');
        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
        $this->assertTrue($response->json('data')[0]['is_read']);

        // Filter for unread notifications
        $response2 = $this->getJson('/api/v1/notifications?read=false');
        $response2->assertStatus(200);
        $this->assertNotEmpty($response2->json('data'));
        $this->assertFalse($response2->json('data')[0]['is_read']);
    }

    #[Test]
    public function authenticated_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        // Ensure all notifications are unread (reset)
        Notification::where('restaurant_id', $user->restaurant_id)->update(['is_read' => false]);

        $response = $this->patchJson('/api/v1/notifications/mark-all-read');
        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $unreadCount = Notification::where('restaurant_id', $user->restaurant_id)
            ->where('is_read', false)
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    #[Test]
    public function authenticated_user_can_mark_single_notification_as_read(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $notification = Notification::where('restaurant_id', $user->restaurant_id)->first();
        $notification->update(['is_read' => false]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}");
        $response->assertStatus(200)
            ->assertJson(['message' => 'Notification marked as read.']);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    #[Test]
    public function authenticated_user_gets_404_for_non_existent_notification(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $response = $this->patchJson('/api/v1/notifications/999999');
        $response->assertStatus(404);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_notifications(): void
    {
        $response = $this->getJson('/api/v1/notifications');
        $response->assertStatus(401);
    }

    #[Test]
    public function unauthenticated_user_cannot_mark_notifications_as_read(): void
    {
        $response = $this->patchJson('/api/v1/notifications/mark-all-read');
        $response->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_mark_notification_as_read_using_patch_with_valid_id(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $notification = Notification::where('restaurant_id', $user->restaurant_id)->first();
        $notification->update(['is_read' => false]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}");
        $response->assertStatus(200)
            ->assertJson(['message' => 'Notification marked as read.']);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    #[Test]
    public function authenticated_user_can_mark_notification_as_read_when_already_read(): void
    {
        $user = User::where('email', 'owner@signetandvine.co.zw')->first();
        $this->actingAs($user, 'sanctum');

        $notification = Notification::where('restaurant_id', $user->restaurant_id)->first();
        $notification->update(['is_read' => true]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}");
        $response->assertStatus(200)
            ->assertJson(['message' => 'Notification marked as read.']);
    }
}
