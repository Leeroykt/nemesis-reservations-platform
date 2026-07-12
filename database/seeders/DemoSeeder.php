<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Restaurant;
use App\Models\RestaurantHours;
use App\Models\RestaurantRules;
use App\Models\User;
use App\Models\Table;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\EmailTemplate;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Restaurant: Signet & Vine
        $restaurant = Restaurant::create([
            'name' => 'Signet & Vine',
            'tagline' => 'Fine Dining & Wine Bar',
            'email' => 'hello@signetandvine.co.zw',
            'phone' => '+263 24 270 1234',
            'address' => '123 Samora Machel Ave, Harare',
            'timezone' => 'Africa/Harare',
            'currency' => 'USD',
            'seats' => 96,
            'tables_count' => 24,
            'logo_path' => null,
            'primary_color_hex' => '#C9A227'
        ]);

        // 2. Create Restaurant Hours (Monday=0, Sunday=6)
        $hoursData = [
            ['day_of_week' => 0, 'open_time' => '11:00', 'close_time' => '22:00', 'is_closed' => false],
            ['day_of_week' => 1, 'open_time' => '11:00', 'close_time' => '22:00', 'is_closed' => false],
            ['day_of_week' => 2, 'open_time' => '11:00', 'close_time' => '22:00', 'is_closed' => false],
            ['day_of_week' => 3, 'open_time' => '11:00', 'close_time' => '22:00', 'is_closed' => false],
            ['day_of_week' => 4, 'open_time' => '11:00', 'close_time' => '23:00', 'is_closed' => false],
            ['day_of_week' => 5, 'open_time' => '11:00', 'close_time' => '23:00', 'is_closed' => false],
            ['day_of_week' => 6, 'open_time' => '11:00', 'close_time' => '21:00', 'is_closed' => false],
        ];

        foreach ($hoursData as $hour) {
            $restaurant->hours()->create($hour);
        }

        // 3. Create Restaurant Rules
        $restaurant->rules()->create([
            'max_party_size' => 14,
            'slot_length_minutes' => 90,
            'buffer_minutes' => 15,
            'cancellation_window_hours' => 4,
            'deposit_required_above' => 8
        ]);

        // 4. Create Users (Staff)
        $users = [
            [
                'name' => 'Sarah Owner',
                'email' => 'owner@signetandvine.co.zw',
                'password' => Hash::make('Demo123!'),
                'role' => 'owner',
            ],
            [
                'name' => 'Mike Manager',
                'email' => 'manager@signetandvine.co.zw',
                'password' => Hash::make('Demo123!'),
                'role' => 'manager',
            ],
            [
                'name' => 'Tara Host',
                'email' => 'host@signetandvine.co.zw',
                'password' => Hash::make('Demo123!'),
                'role' => 'host',
            ],
        ];

        foreach ($users as $userData) {
            $restaurant->users()->create($userData);
        }

        // 5. Create Tables (Floor Plan)
        $tablesData = [
            ['code' => 'T-01', 'zone' => 'Terrace', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 8, 'pos_y' => 12],
            ['code' => 'T-02', 'zone' => 'Terrace', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 12, 'pos_y' => 12],
            ['code' => 'T-03', 'zone' => 'Terrace', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 18, 'pos_y' => 10],
            ['code' => 'T-04', 'zone' => 'Terrace', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 22, 'pos_y' => 10],
            ['code' => 'T-05', 'zone' => 'Main', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 10, 'pos_y' => 30],
            ['code' => 'T-06', 'zone' => 'Main', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 16, 'pos_y' => 28],
            ['code' => 'T-07', 'zone' => 'Main', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 20, 'pos_y' => 28],
            ['code' => 'T-08', 'zone' => 'Main', 'capacity' => 6, 'shape' => 'rect', 'pos_x' => 30, 'pos_y' => 30],
            ['code' => 'T-09', 'zone' => 'Main', 'capacity' => 6, 'shape' => 'rect', 'pos_x' => 36, 'pos_y' => 30],
            ['code' => 'T-10', 'zone' => 'Bar', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 5, 'pos_y' => 48],
            ['code' => 'T-11', 'zone' => 'Bar', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 10, 'pos_y' => 48],
            ['code' => 'T-12', 'zone' => 'Bar', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 15, 'pos_y' => 48],
        ];

        foreach ($tablesData as $tableData) {
            $restaurant->tables()->create($tableData);
        }

        // 6. Create Customers
        $customers = [
            ['name' => 'Farai Chikono', 'email' => 'farai@example.com', 'phone' => '+263 77 123 4567', 'visits' => 12, 'is_vip' => true, 'lifetime_spend' => 450.00],
            ['name' => 'Tendai Moyo', 'email' => 'tendai@example.com', 'phone' => '+263 78 234 5678', 'visits' => 8, 'is_vip' => false, 'lifetime_spend' => 280.00],
            ['name' => 'Chipo Ndlovu', 'email' => 'chipo@example.com', 'phone' => '+263 71 345 6789', 'visits' => 15, 'is_vip' => true, 'lifetime_spend' => 620.00],
            ['name' => 'Tafadzwa Sibanda', 'email' => 'tafadzwa@example.com', 'phone' => '+263 73 456 7890', 'visits' => 5, 'is_vip' => false, 'lifetime_spend' => 180.00],
            ['name' => 'Rudo Mafukidze', 'email' => 'rudo@example.com', 'phone' => '+263 76 567 8901', 'visits' => 20, 'is_vip' => true, 'lifetime_spend' => 890.00],
            ['name' => 'Simba Makoni', 'email' => 'simba@example.com', 'phone' => '+263 74 678 9012', 'visits' => 3, 'is_vip' => false, 'lifetime_spend' => 95.00],
            ['name' => 'Nyasha Dube', 'email' => 'nyasha@example.com', 'phone' => '+263 79 789 0123', 'visits' => 9, 'is_vip' => false, 'lifetime_spend' => 320.00],
            ['name' => 'Tanaka Chikwanda', 'email' => 'tanaka@example.com', 'phone' => '+263 77 890 1234', 'visits' => 6, 'is_vip' => false, 'lifetime_spend' => 210.00],
        ];

        foreach ($customers as $customerData) {
            $restaurant->customers()->create($customerData);
        }

        // 7. Create Reservations
        $reservations = [
            [
                'public_ref' => 'RB-1001',
                'customer_id' => 1,
                'table_id' => 3,
                'guest_name' => 'Farai Chikono',
                'guest_phone' => '+263 77 123 4567',
                'guest_email' => 'farai@example.com',
                'date' => now()->addDays(2)->format('Y-m-d'),
                'time' => '19:00',
                'party_size' => 4,
                'status' => 'Confirmed',
                'source' => 'App',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-1002',
                'customer_id' => 2,
                'table_id' => 1,
                'guest_name' => 'Tendai Moyo',
                'guest_phone' => '+263 78 234 5678',
                'guest_email' => 'tendai@example.com',
                'date' => now()->addDays(2)->format('Y-m-d'),
                'time' => '18:30',
                'party_size' => 2,
                'status' => 'Upcoming',
                'source' => 'Phone',
                'created_by_user_id' => 3,
            ],
            [
                'public_ref' => 'RB-1003',
                'customer_id' => 3,
                'table_id' => 8,
                'guest_name' => 'Chipo Ndlovu',
                'guest_phone' => '+263 71 345 6789',
                'guest_email' => 'chipo@example.com',
                'date' => now()->addDays(3)->format('Y-m-d'),
                'time' => '20:00',
                'party_size' => 6,
                'status' => 'Confirmed',
                'source' => 'Website',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-1004',
                'customer_id' => 5,
                'table_id' => 6,
                'guest_name' => 'Rudo Mafukidze',
                'guest_phone' => '+263 76 567 8901',
                'guest_email' => 'rudo@example.com',
                'date' => now()->addDays(4)->format('Y-m-d'),
                'time' => '19:30',
                'party_size' => 4,
                'status' => 'Confirmed',
                'source' => 'App',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-1005',
                'customer_id' => 7,
                'table_id' => 2,
                'guest_name' => 'Nyasha Dube',
                'guest_phone' => '+263 79 789 0123',
                'guest_email' => 'nyasha@example.com',
                'date' => now()->addDays(1)->format('Y-m-d'),
                'time' => '18:00',
                'party_size' => 2,
                'status' => 'Confirmed',
                'source' => 'Walk-in',
                'created_by_user_id' => 3,
            ],
            [
                'public_ref' => 'RB-1006',
                'customer_id' => 8,
                'table_id' => 5,
                'guest_name' => 'Tanaka Chikwanda',
                'guest_phone' => '+263 77 890 1234',
                'guest_email' => 'tanaka@example.com',
                'date' => now()->addDays(5)->format('Y-m-d'),
                'time' => '20:30',
                'party_size' => 2,
                'status' => 'Upcoming',
                'source' => 'Website',
                'created_by_user_id' => 2,
            ],
        ];

        foreach ($reservations as $reservationData) {
            $restaurant->reservations()->create($reservationData);
        }

        // 8. Create Activity Log
        $activities = [
            [
                'actor_user_id' => 2,
                'actor_label' => 'Mike Manager',
                'icon' => 'bi-check-circle',
                'tone' => 'emerald',
                'description' => 'Confirmed reservation RB-1001 for Farai Chikono',
                'entity_type' => 'reservation',
                'entity_id' => 1,
            ],
            [
                'actor_user_id' => 3,
                'actor_label' => 'Tara Host',
                'icon' => 'bi-plus-circle',
                'tone' => 'gold',
                'description' => 'Created reservation RB-1002 for Tendai Moyo (phone)',
                'entity_type' => 'reservation',
                'entity_id' => 2,
            ],
            [
                'actor_user_id' => 2,
                'actor_label' => 'Mike Manager',
                'icon' => 'bi-star',
                'tone' => 'gold',
                'description' => 'Marked Chipo Ndlovu as VIP (15 visits)',
                'entity_type' => 'customer',
                'entity_id' => 3,
            ],
            [
                'actor_user_id' => null,
                'actor_label' => 'System',
                'icon' => 'bi-globe',
                'tone' => 'slate',
                'description' => 'New website booking: RB-1003 from Chipo Ndlovu',
                'entity_type' => 'reservation',
                'entity_id' => 3,
            ],
        ];

        foreach ($activities as $activityData) {
            $restaurant->activityLog()->create($activityData);
        }

        // 9. Create Notifications
        $notifications = [
            [
                'title' => 'New Website Booking',
                'message' => 'Chipo Ndlovu booked for 6 guests on ' . now()->addDays(3)->format('M d, Y') . ' at 20:00',
                'is_read' => false,
            ],
            [
                'title' => 'VIP Arrival',
                'message' => 'Rudo Mafukidze (VIP) booked for ' . now()->addDays(4)->format('M d, Y'),
                'is_read' => false,
            ],
            [
                'title' => 'Walk-in Booking',
                'message' => 'Nyasha Dube walked in for 2 guests today at 18:00',
                'is_read' => true,
            ],
        ];

        foreach ($notifications as $notificationData) {
            $restaurant->notifications()->create($notificationData);
        }

        // 10. Create Email Templates
        $templates = [
            [
                'key' => 'confirm',
                'name' => 'Booking Confirmation',
                'subject' => 'Your booking at {{restaurant_name}} is confirmed',
                'body' => "Dear {{guest_name}},\n\nYour booking for {{party_size}} guests on {{date}} at {{time}} has been confirmed.\n\nBooking Reference: {{booking_id}}\n\nWe look forward to welcoming you!\n\n{{restaurant_name}} Team",
            ],
            [
                'key' => 'reminder',
                'name' => 'Booking Reminder',
                'subject' => 'Reminder: Your booking at {{restaurant_name}}',
                'body' => "Dear {{guest_name}},\n\nThis is a reminder for your booking for {{party_size}} guests on {{date}} at {{time}}.\n\nBooking Reference: {{booking_id}}\n\nWe look forward to seeing you!\n\n{{restaurant_name}} Team",
            ],
            [
                'key' => 'cancel',
                'name' => 'Booking Cancellation',
                'subject' => 'Booking cancellation confirmation',
                'body' => "Dear {{guest_name}},\n\nYour booking on {{date}} at {{time}} has been cancelled.\n\nBooking Reference: {{booking_id}}\n\nWe hope to welcome you again soon.\n\n{{restaurant_name}} Team",
            ],
            [
                'key' => 'vip',
                'name' => 'VIP Welcome',
                'subject' => 'Welcome to the VIP Club!',
                'body' => "Dear {{guest_name}},\n\nThank you for being a valued guest at {{restaurant_name}}.\n\nWe have upgraded you to VIP status. As a VIP, you'll receive priority seating and exclusive offers.\n\nWe look forward to your next visit!\n\n{{restaurant_name}} Team",
            ],
        ];

        foreach ($templates as $templateData) {
            $restaurant->emailTemplates()->create($templateData);
        }

        $this->command->info('✅ Demo data seeded successfully!');
    }
}