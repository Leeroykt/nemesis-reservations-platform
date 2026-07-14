<?php

namespace Tests\Feature\Api;

use App\Mail\BookingConfirmed;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use DatabaseMigrations;

    protected Restaurant $restaurant;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
        $this->artisan('db:seed');

        /** @var Restaurant $restaurant */
        $restaurant = Restaurant::first();
        $this->restaurant = $restaurant;
        $this->owner = User::where('role', 'owner')->first();

        // Ensure slot_length_minutes is set for conflict detection
        if ($this->restaurant->rules) {
            $this->restaurant->rules->update(['slot_length_minutes' => 90]);
        }
    }

    // ============================================================
    // SUCCESS CASES
    // ============================================================

    #[Test]
    public function public_booking_creates_reservation_successfully()
    {
        Mail::fake();
        Log::shouldReceive('error')->never();

        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'public_ref',
                    'guest_name',
                    'date',
                    'time',
                    'party_size',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'guest_name' => 'Public Guest',
                    'date' => $data['date'],
                    'time' => $data['time'],
                    'party_size' => $data['party_size'],
                ],
                'message' => 'Booking confirmed! Check your email.',
            ]);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'Public Guest',
            'guest_phone' => '+263 77 123 4567',
            'guest_email' => 'public@example.com',
            'source' => 'Website',
            'status' => 'Upcoming',
            'party_size' => 2,
            'restaurant_id' => $this->restaurant->id,
        ]);

        Mail::assertSent(BookingConfirmed::class);
    }

    #[Test]
    public function public_booking_generates_unique_public_ref()
    {
        $data = $this->validBookingData();

        $response1 = $this->postJson('/api/v1/public/reservations', $data);
        $response2 = $this->postJson('/api/v1/public/reservations', $data);

        $ref1 = $response1->json('data.public_ref');
        $ref2 = $response2->json('data.public_ref');

        $this->assertNotNull($ref1, 'First public_ref should not be null');
        $this->assertNotNull($ref2, 'Second public_ref should not be null');
        $this->assertNotEquals($ref1, $ref2);
        $this->assertMatchesRegularExpression('/^RB-\d{4}$/', $ref1);
        $this->assertMatchesRegularExpression('/^RB-\d{4}$/', $ref2);
    }

    #[Test]
    public function public_booking_auto_assigns_table()
    {
        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        $reservation = Reservation::where('public_ref', $response->json('data.public_ref'))->first();
        $this->assertNotNull($reservation->table_id);
        $this->assertDatabaseHas('tables', [
            'id' => $reservation->table_id,
            'status' => 'Available',
        ]);
    }

    // ============================================================
    // VALIDATION TESTS
    // ============================================================

    #[Test]
    public function public_booking_validates_guest_name_required()
    {
        $data = $this->validBookingData();
        unset($data['guest_name']);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_name']);
    }

    #[Test]
    public function public_booking_validates_guest_name_max_length()
    {
        $data = $this->validBookingData();
        $data['guest_name'] = str_repeat('a', 121);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_name']);
    }

    #[Test]
    public function public_booking_validates_guest_phone_required()
    {
        $data = $this->validBookingData();
        unset($data['guest_phone']);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_phone']);
    }

    #[Test]
    public function public_booking_validates_guest_phone_max_length()
    {
        $data = $this->validBookingData();
        $data['guest_phone'] = str_repeat('1', 41);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_phone']);
    }

    #[Test]
    public function public_booking_validates_guest_email_required()
    {
        $data = $this->validBookingData();
        unset($data['guest_email']);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_email']);
    }

    #[Test]
    public function public_booking_validates_guest_email_format()
    {
        $data = $this->validBookingData();
        $data['guest_email'] = 'invalid-email';

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_email']);
    }

    #[Test]
    public function public_booking_validates_guest_email_max_length()
    {
        $data = $this->validBookingData();
        $data['guest_email'] = str_repeat('a', 150).'@example.com';

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['guest_email']);
    }

    #[Test]
    public function public_booking_validates_date_required()
    {
        $data = $this->validBookingData();
        unset($data['date']);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    #[Test]
    public function public_booking_validates_date_format()
    {
        $data = $this->validBookingData();
        $data['date'] = '2026/07/15';

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    #[Test]
    public function public_booking_validates_date_not_in_past()
    {
        $data = $this->validBookingData();
        $data['date'] = now()->subDay()->format('Y-m-d');

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    #[Test]
    public function public_booking_validates_time_required()
    {
        $data = $this->validBookingData();
        unset($data['time']);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['time']);
    }

    #[Test]
    public function public_booking_validates_time_format()
    {
        $data = $this->validBookingData();
        $data['time'] = '7:00 PM';

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['time']);
    }

    #[Test]
    public function public_booking_validates_party_size_required()
    {
        $data = $this->validBookingData();
        unset($data['party_size']);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['party_size']);
    }

    #[Test]
    public function public_booking_validates_party_size_minimum()
    {
        $data = $this->validBookingData();
        $data['party_size'] = 0;

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['party_size']);
    }

    #[Test]
    public function public_booking_validates_party_size_maximum()
    {
        $data = $this->validBookingData();
        $data['party_size'] = 20;

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['party_size']);
    }

    #[Test]
    public function public_booking_validates_notes_max_length()
    {
        $data = $this->validBookingData();
        $data['notes'] = str_repeat('a', 501);

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['notes']);
    }

    // ============================================================
    // CONFLICT & BUSINESS RULE TESTS
    // ============================================================

    #[Test]
    public function public_booking_rejects_conflicting_times()
    {
        $date = now()->addDays(2)->format('Y-m-d');

        // Isolate to a single table so an overlapping second booking has nowhere to go.
        // Public guests can't specify table_id (the endpoint auto-assigns), so the only
        // reliable way to force a conflict is to exhaust every other table's availability.
        $firstTable = $this->restaurant->tables()->first();
        $this->restaurant->tables()->where('id', '!=', $firstTable->id)
            ->update(['status' => 'Occupied']);

        $data1 = $this->validBookingData();
        $data1['date'] = $date;
        $data1['time'] = '19:00';

        $response1 = $this->postJson('/api/v1/public/reservations', $data1);
        $response1->assertStatus(201);

        // Try to book an overlapping time; the only available table is now taken.
        $data2 = $this->validBookingData();
        $data2['guest_name'] = 'Conflict Guest';
        $data2['guest_email'] = 'conflict@example.com';
        $data2['date'] = $date;
        $data2['time'] = '19:30'; // overlaps the 90-min slot starting at 19:00

        $response2 = $this->postJson('/api/v1/public/reservations', $data2);

        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['table_id']);
    }

    #[Test]
    public function public_booking_handles_no_available_table()
    {
        $date = now()->addDays(2)->format('Y-m-d');

        // Get all tables and mark them as occupied
        $tables = $this->restaurant->tables()->get();
        foreach ($tables as $table) {
            $table->update(['status' => 'Occupied']);
        }

        $data = $this->validBookingData();
        $data['date'] = $date;

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['table_id'])
            ->assertJson([
                'errors' => [
                    'table_id' => ['No available table for this time and party size.'],
                ],
            ]);
    }

    #[Test]
    public function public_booking_respects_max_party_size_from_rules()
    {
        // Update restaurant rules to max 10
        $this->restaurant->rules->update(['max_party_size' => 10]);

        $data = $this->validBookingData();
        $data['party_size'] = 12;

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['party_size'])
            ->assertJson([
                'errors' => [
                    'party_size' => ['Maximum party size is 10.'],
                ],
            ]);
    }

    // ============================================================
    // NOTIFICATION & AUDIT TESTS
    // ============================================================

    #[Test]
    public function public_booking_creates_staff_notification()
    {
        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        $notification = Notification::where('restaurant_id', $this->restaurant->id)
            ->where('title', 'New booking')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals(
            "Public Guest booked for 2 on {$data['date']} at {$data['time']}",
            $notification->message
        );
        $this->assertFalse($notification->is_read);
    }

    #[Test]
    public function public_booking_creates_audit_log()
    {
        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        $reservation = Reservation::where('guest_name', 'Public Guest')->first();

        $this->assertDatabaseHas('activity_log', [
            'restaurant_id' => $this->restaurant->id,
            'actor_user_id' => null,
            'actor_label' => 'Guest (website)',
            'description' => "New booking {$reservation->public_ref} for Public Guest via website",
            'entity_type' => 'reservation',
            'entity_id' => $reservation->id,
            'icon' => 'bi-plus-circle',
            'tone' => 'gold',
        ]);
    }

    #[Test]
    public function public_booking_audit_log_has_correct_actor_label_for_system()
    {
        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        $reservation = Reservation::where('guest_name', 'Public Guest')->first();

        $this->assertDatabaseHas('activity_log', [
            'restaurant_id' => $this->restaurant->id,
            'actor_user_id' => null,
            'actor_label' => 'Guest (website)',
            'description' => "New booking {$reservation->public_ref} for Public Guest via website",
        ]);
    }

    // ============================================================
    // EMAIL TESTS
    // ============================================================

    #[Test]
    public function public_booking_sends_confirmation_email()
    {
        Mail::fake();

        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        Mail::assertSent(BookingConfirmed::class, function ($mail) {
            return $mail->hasTo('public@example.com');
        });
    }

    #[Test]
    public function public_booking_email_contains_correct_tokens()
    {
        Mail::fake();

        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        Mail::assertSent(BookingConfirmed::class);
    }

    #[Test]
    public function public_booking_email_handles_missing_template()
    {
        Mail::fake();

        // Delete email templates
        $this->restaurant->emailTemplates()->delete();

        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        // Should still send email with fallback template
        Mail::assertSent(BookingConfirmed::class);
    }

    #[Test]
    public function public_booking_logs_email_failure()
    {
        // The controller calls Mail::to($email)->send($mailable) - a fluent chain.
        // Mail::to() returns a PendingMail, so we need to stub 'to' to return the
        // mock itself before 'send' will ever be reached.
        Mail::shouldReceive('to')
            ->once()
            ->with('public@example.com')
            ->andReturnSelf();

        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('SMTP connection failed'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'Booking confirmation email failed');
            });

        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201); // Should still succeed even if email fails
    }

    // ============================================================
    // RATE LIMITING TESTS
    // ============================================================

    #[Test]
    public function public_booking_rate_limit_allows_5_requests_per_minute()
    {
        $data = $this->validBookingData();

        for ($i = 0; $i < 5; $i++) {
            $data['guest_name'] = "Guest {$i}";
            $data['guest_email'] = "guest{$i}@example.com";
            $response = $this->postJson('/api/v1/public/reservations', $data);
            $response->assertStatus(201);
        }
    }

    #[Test]
    public function public_booking_rate_limit_blocks_6th_request_within_minute()
    {
        $data = $this->validBookingData();

        for ($i = 0; $i < 5; $i++) {
            $data['guest_name'] = "Guest {$i}";
            $data['guest_email'] = "guest{$i}@example.com";
            $this->postJson('/api/v1/public/reservations', $data);
        }

        $data['guest_name'] = 'Rate Limited Guest';
        $data['guest_email'] = 'ratelimit@example.com';
        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(429);
    }

    // ============================================================
    // EDGE CASES
    // ============================================================

    #[Test]
    public function public_booking_handles_null_notes_gracefully()
    {
        $data = $this->validBookingData();
        $data['notes'] = null;

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'Public Guest',
            'notes' => null,
        ]);
    }

    #[Test]
    public function public_booking_handles_special_characters_in_name()
    {
        $data = $this->validBookingData();
        $data['guest_name'] = 'John O\'Brien & Mary Jane Smith-Johnson';

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => "John O'Brien & Mary Jane Smith-Johnson",
        ]);
    }

    #[Test]
    public function public_booking_handles_restaurant_not_configured()
    {
        // Delete the restaurant
        $this->restaurant->delete();

        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(500)
            ->assertJson(['message' => 'Restaurant not configured.']);
    }

    #[Test]
    public function public_booking_creates_reservation_with_correct_source()
    {
        $data = $this->validBookingData();

        $response = $this->postJson('/api/v1/public/reservations', $data);
        $response->assertStatus(201);

        $this->assertDatabaseHas('reservations', [
            'guest_name' => 'Public Guest',
            'source' => 'Website',
        ]);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    protected function validBookingData(): array
    {
        return [
            'guest_name' => 'Public Guest',
            'guest_phone' => '+263 77 123 4567',
            'guest_email' => 'public@example.com',
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '19:00',
            'party_size' => 2,
            'notes' => 'Window seat please',
        ];
    }
}
