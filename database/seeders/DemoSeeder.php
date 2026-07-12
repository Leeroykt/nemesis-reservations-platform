<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'primary_color_hex' => '#C9A227',
        ]);

        // 2. Create Restaurant Hours (Monday=0, Sunday=6)
        $hoursData = [
            ['day_of_week' => 0, 'open_time' => '11:00', 'close_time' => '22:00', 'is_closed' => false],
            ['day_of_week' => 1, 'open_time' => '11:00', 'close_time' => '22:00', 'is_closed' => false],
            ['day_of_week' => 2, 'open_time' => '11:00', 'close_time' => '22:00', 'is_closed' => false],
            ['day_of_week' => 3, 'open_time' => '11:00', 'close_time' => '23:00', 'is_closed' => false],
            ['day_of_week' => 4, 'open_time' => '11:00', 'close_time' => '23:59', 'is_closed' => false],
            ['day_of_week' => 5, 'open_time' => '09:00', 'close_time' => '23:59', 'is_closed' => false],
            ['day_of_week' => 6, 'open_time' => '09:00', 'close_time' => '21:00', 'is_closed' => false],
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
            'deposit_required_above' => 8,
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
            // Demo user from data.js
            [
                'name' => 'Nyasha Ngirazi',
                'email' => 'demo@nemesis.co.zw',
                'password' => Hash::make('Demo123!'),
                'role' => 'owner',  // General Manager = owner level
                'avatar_initials' => 'NN',
            ],
        ];

        foreach ($users as $userData) {
            $restaurant->users()->create($userData);
        }

        // 5. Create Tables (Floor Plan)
        $tablesData = [
            // Bar zone
            ['code' => 'T-01', 'zone' => 'Bar', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 8, 'pos_y' => 12, 'status' => 'Occupied'],
            ['code' => 'T-02', 'zone' => 'Bar', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 18, 'pos_y' => 12, 'status' => 'Available'],
            ['code' => 'T-03', 'zone' => 'Bar', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 28, 'pos_y' => 12, 'status' => 'Reserved'],
            // Window zone
            ['code' => 'T-04', 'zone' => 'Window', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 8, 'pos_y' => 30, 'status' => 'Occupied'],
            ['code' => 'T-05', 'zone' => 'Window', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 18, 'pos_y' => 30, 'status' => 'Available'],
            ['code' => 'T-06', 'zone' => 'Window', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 28, 'pos_y' => 30, 'status' => 'Cleaning'],
            // Main Floor zone
            ['code' => 'T-07', 'zone' => 'Main Floor', 'capacity' => 6, 'shape' => 'rect', 'pos_x' => 45, 'pos_y' => 12, 'status' => 'Occupied'],
            ['code' => 'T-08', 'zone' => 'Main Floor', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 58, 'pos_y' => 12, 'status' => 'Available'],
            ['code' => 'T-09', 'zone' => 'Main Floor', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 70, 'pos_y' => 12, 'status' => 'Reserved'],
            ['code' => 'T-10', 'zone' => 'Main Floor', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 82, 'pos_y' => 12, 'status' => 'Available'],
            ['code' => 'T-11', 'zone' => 'Main Floor', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 45, 'pos_y' => 30, 'status' => 'Occupied'],
            ['code' => 'T-12', 'zone' => 'Terrace', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 58, 'pos_y' => 30, 'status' => 'Reserved'],
            // Terrace zone (continued)
            ['code' => 'T-13', 'zone' => 'Terrace', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 70, 'pos_y' => 30, 'status' => 'Available'],
            ['code' => 'T-14', 'zone' => 'Terrace', 'capacity' => 6, 'shape' => 'rect', 'pos_x' => 82, 'pos_y' => 30, 'status' => 'Occupied'],
            // Booth zone
            ['code' => 'T-15', 'zone' => 'Booth', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 8, 'pos_y' => 50, 'status' => 'Available'],
            ['code' => 'T-16', 'zone' => 'Booth', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 18, 'pos_y' => 50, 'status' => 'Occupied'],
            ['code' => 'T-17', 'zone' => 'Booth', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 28, 'pos_y' => 50, 'status' => 'Cleaning'],
            ['code' => 'T-18', 'zone' => 'Booth', 'capacity' => 6, 'shape' => 'rect', 'pos_x' => 45, 'pos_y' => 50, 'status' => 'Reserved'],
            // Garden zone
            ['code' => 'T-19', 'zone' => 'Garden', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 58, 'pos_y' => 50, 'status' => 'Available'],
            ['code' => 'T-20', 'zone' => 'Garden', 'capacity' => 8, 'shape' => 'rect', 'pos_x' => 74, 'pos_y' => 50, 'status' => 'Reserved'],
            ['code' => 'T-21', 'zone' => 'Garden', 'capacity' => 4, 'shape' => 'square', 'pos_x' => 8, 'pos_y' => 68, 'status' => 'Occupied'],
            ['code' => 'T-22', 'zone' => 'Garden', 'capacity' => 2, 'shape' => 'round', 'pos_x' => 18, 'pos_y' => 68, 'status' => 'Available'],
            // Private zone
            ['code' => 'T-23', 'zone' => 'Private', 'capacity' => 10, 'shape' => 'rect', 'pos_x' => 45, 'pos_y' => 68, 'status' => 'Reserved'],
            ['code' => 'T-24', 'zone' => 'Private', 'capacity' => 12, 'shape' => 'rect', 'pos_x' => 68, 'pos_y' => 68, 'status' => 'Available'],
        ];

        foreach ($tablesData as $tableData) {
            $restaurant->tables()->create($tableData);
        }

        // 6. Create Customers
        $customers = [
            [
                'name' => 'Farai Chikono',
                'email' => 'farai.chikono@gmail.com',
                'phone' => '+263 77 220 1145',
                'visits' => 14,
                'last_visit_at' => '2026-07-06',
                'is_vip' => true,
                'lifetime_spend' => 2140.00,
                'preferences' => ['Window seating', 'No shellfish', 'Still water'],
            ],
            [
                'name' => 'Rutendo Chirwa',
                'email' => 'rutendo.c@outlook.com',
                'phone' => '+263 71 883 4420',
                'visits' => 2,
                'last_visit_at' => '2026-07-09',
                'is_vip' => false,
                'lifetime_spend' => 186.00,
                'preferences' => ['Vegetarian'],
            ],
            [
                'name' => 'Tanaka Moyo',
                'email' => 'tanaka.moyo@yahoo.com',
                'phone' => '+263 78 552 6301',
                'visits' => 6,
                'last_visit_at' => '2026-07-10',
                'is_vip' => false,
                'lifetime_spend' => 640.00,
                'preferences' => ['Booth seating', 'Birthday — bring candle'],
            ],
            [
                'name' => 'Chiedza Mutasa',
                'email' => 'chiedza.mutasa@gmail.com',
                'phone' => '+263 77 990 2214',
                'visits' => 9,
                'last_visit_at' => '2026-06-28',
                'is_vip' => true,
                'lifetime_spend' => 1580.00,
                'preferences' => ['Quiet table', 'Red wine pairing'],
            ],
            [
                'name' => 'Blessing Ndlovu',
                'email' => 'blessing.nd@gmail.com',
                'phone' => '+263 73 441 7789',
                'visits' => 1,
                'last_visit_at' => '2026-06-14',
                'is_vip' => false,
                'lifetime_spend' => 78.00,
                'preferences' => [],
            ],
            [
                'name' => 'Kudzai Marimo',
                'email' => 'kudzai.marimo@proton.me',
                'phone' => '+263 71 205 6634',
                'visits' => 21,
                'last_visit_at' => '2026-07-05',
                'is_vip' => true,
                'lifetime_spend' => 3320.00,
                'preferences' => ['Terrace', 'Whisky list', 'Regular Fri 7pm'],
            ],
            [
                'name' => 'Nyasha Gumbo',
                'email' => 'nyasha.gumbo@gmail.com',
                'phone' => '+263 78 663 1290',
                'visits' => 4,
                'last_visit_at' => '2026-06-30',
                'is_vip' => false,
                'lifetime_spend' => 410.00,
                'preferences' => ['Gluten free'],
            ],
            [
                'name' => 'Panashe Zivai',
                'email' => 'panashe.z@hotmail.com',
                'phone' => '+263 77 334 8850',
                'visits' => 3,
                'last_visit_at' => '2026-06-22',
                'is_vip' => false,
                'lifetime_spend' => 265.00,
                'preferences' => ['High chair for toddler'],
            ],
        ];
        foreach ($customers as $customerData) {
            $preferences = $customerData['preferences'] ?? [];
            unset($customerData['preferences']);

            $customer = $restaurant->customers()->create($customerData);

            foreach ($preferences as $note) {
                $customer->preferences()->create(['note' => $note]);
            }
        }
        // 7. Create Reservations
        // 7. Create Reservations (matching data.js)
        // First, we need to map customer emails and table codes to IDs
        $customerIds = $restaurant->customers()->pluck('id', 'email')->toArray();
        $tableIds = $restaurant->tables()->pluck('id', 'code')->toArray();

        $reservations = [
            [
                'public_ref' => 'RB-2301',
                'customer_id' => $customerIds['farai.chikono@gmail.com'],
                'table_id' => $tableIds['T-12'],
                'guest_name' => 'Farai Chikono',
                'guest_phone' => '+263 77 220 1145',
                'guest_email' => 'farai.chikono@gmail.com',
                'date' => '2026-07-11',
                'time' => '19:30',
                'party_size' => 4,
                'status' => 'Confirmed',
                'notes' => 'VIP guest — window preferred, celebrating anniversary.',
                'source' => 'Website',
                'created_by_user_id' => 2, // Manager
            ],
            [
                'public_ref' => 'RB-2302',
                'customer_id' => $customerIds['rutendo.c@outlook.com'],
                'table_id' => $tableIds['T-05'],
                'guest_name' => 'Rutendo Chirwa',
                'guest_phone' => '+263 71 883 4420',
                'guest_email' => 'rutendo.c@outlook.com',
                'date' => '2026-07-11',
                'time' => '18:00',
                'party_size' => 2,
                'status' => 'Upcoming',
                'notes' => 'Vegetarian menu requested.',
                'source' => 'Phone',
                'created_by_user_id' => 3, // Host
            ],
            [
                'public_ref' => 'RB-2303',
                'customer_id' => $customerIds['tanaka.moyo@yahoo.com'],
                'table_id' => $tableIds['T-18'],
                'guest_name' => 'Tanaka Moyo',
                'guest_phone' => '+263 78 552 6301',
                'guest_email' => 'tanaka.moyo@yahoo.com',
                'date' => '2026-07-11',
                'time' => '20:00',
                'party_size' => 6,
                'status' => 'Confirmed',
                'notes' => 'Birthday — bring a candle at dessert.',
                'source' => 'Website',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-2304',
                'customer_id' => $customerIds['chiedza.mutasa@gmail.com'],
                'table_id' => $tableIds['T-13'],
                'guest_name' => 'Chiedza Mutasa',
                'guest_phone' => '+263 77 990 2214',
                'guest_email' => 'chiedza.mutasa@gmail.com',
                'date' => '2026-07-11',
                'time' => '20:00',
                'party_size' => 2,
                'status' => 'Cancelled',
                'notes' => 'Guest cancelled — rescheduling next week.',
                'source' => 'App',
                'created_by_user_id' => 3,
            ],
            [
                'public_ref' => 'RB-2305',
                'customer_id' => $customerIds['kudzai.marimo@proton.me'],
                'table_id' => $tableIds['T-09'],
                'guest_name' => 'Kudzai Marimo',
                'guest_phone' => '+263 71 205 6634',
                'guest_email' => 'kudzai.marimo@proton.me',
                'date' => '2026-07-11',
                'time' => '19:00',
                'party_size' => 4,
                'status' => 'Confirmed',
                'notes' => 'Regular Friday guest, whisky pairing.',
                'source' => 'Phone',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-2306',
                'customer_id' => $customerIds['nyasha.gumbo@gmail.com'],
                'table_id' => $tableIds['T-08'],
                'guest_name' => 'Nyasha Gumbo',
                'guest_phone' => '+263 78 663 1290',
                'guest_email' => 'nyasha.gumbo@gmail.com',
                'date' => '2026-07-10',
                'time' => '13:00',
                'party_size' => 3,
                'status' => 'Completed',
                'notes' => 'Gluten-free menu served.',
                'source' => 'Website',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-2307',
                'customer_id' => $customerIds['panashe.z@hotmail.com'],
                'table_id' => $tableIds['T-19'],
                'guest_name' => 'Panashe Zivai',
                'guest_phone' => '+263 77 334 8850',
                'guest_email' => 'panashe.z@hotmail.com',
                'date' => '2026-07-10',
                'time' => '12:30',
                'party_size' => 4,
                'status' => 'Completed',
                'notes' => 'Requested high chair.',
                'source' => 'App',
                'created_by_user_id' => 3,
            ],
            [
                'public_ref' => 'RB-2308',
                'customer_id' => $customerIds['blessing.nd@gmail.com'],
                'table_id' => $tableIds['T-02'],
                'guest_name' => 'Blessing Ndlovu',
                'guest_phone' => '+263 73 441 7789',
                'guest_email' => 'blessing.nd@gmail.com',
                'date' => '2026-07-12',
                'time' => '19:00',
                'party_size' => 2,
                'status' => 'Upcoming',
                'notes' => 'First-time guest.',
                'source' => 'Website',
                'created_by_user_id' => 3,
            ],
            [
                'public_ref' => 'RB-2309',
                'customer_id' => $customerIds['farai.chikono@gmail.com'],
                'table_id' => $tableIds['T-12'],
                'guest_name' => 'Farai Chikono',
                'guest_phone' => '+263 77 220 1145',
                'guest_email' => 'farai.chikono@gmail.com',
                'date' => '2026-07-14',
                'time' => '20:30',
                'party_size' => 2,
                'status' => 'Upcoming',
                'notes' => 'Anniversary dinner, still water only.',
                'source' => 'App',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-2310',
                'customer_id' => $customerIds['kudzai.marimo@proton.me'],
                'table_id' => $tableIds['T-09'],
                'guest_name' => 'Kudzai Marimo',
                'guest_phone' => '+263 71 205 6634',
                'guest_email' => 'kudzai.marimo@proton.me',
                'date' => '2026-07-18',
                'time' => '19:00',
                'party_size' => 4,
                'status' => 'Upcoming',
                'notes' => 'Weekly regular booking.',
                'source' => 'Phone',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-2311',
                'customer_id' => $customerIds['tanaka.moyo@yahoo.com'],
                'table_id' => $tableIds['T-18'],
                'guest_name' => 'Tanaka Moyo',
                'guest_phone' => '+263 78 552 6301',
                'guest_email' => 'tanaka.moyo@yahoo.com',
                'date' => '2026-07-09',
                'time' => '19:00',
                'party_size' => 6,
                'status' => 'Completed',
                'notes' => 'Group dinner, no issues.',
                'source' => 'Website',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-2312',
                'customer_id' => $customerIds['chiedza.mutasa@gmail.com'],
                'table_id' => $tableIds['T-13'],
                'guest_name' => 'Chiedza Mutasa',
                'guest_phone' => '+263 77 990 2214',
                'guest_email' => 'chiedza.mutasa@gmail.com',
                'date' => '2026-07-08',
                'time' => '20:00',
                'party_size' => 2,
                'status' => 'Cancelled',
                'notes' => 'No-show, courtesy call made.',
                'source' => 'Phone',
                'created_by_user_id' => 3,
            ],
            [
                'public_ref' => 'RB-2313',
                'customer_id' => $customerIds['rutendo.c@outlook.com'],
                'table_id' => $tableIds['T-05'],
                'guest_name' => 'Rutendo Chirwa',
                'guest_phone' => '+263 71 883 4420',
                'guest_email' => 'rutendo.c@outlook.com',
                'date' => '2026-07-19',
                'time' => '18:30',
                'party_size' => 2,
                'status' => 'Upcoming',
                'notes' => 'Vegetarian menu again.',
                'source' => 'Website',
                'created_by_user_id' => 3,
            ],
            [
                'public_ref' => 'RB-2314',
                'customer_id' => $customerIds['blessing.nd@gmail.com'],
                'table_id' => $tableIds['T-02'],
                'guest_name' => 'Blessing Ndlovu',
                'guest_phone' => '+263 73 441 7789',
                'guest_email' => 'blessing.nd@gmail.com',
                'date' => '2026-07-06',
                'time' => '13:00',
                'party_size' => 2,
                'status' => 'Completed',
                'notes' => 'Enjoyed the tasting menu.',
                'source' => 'Website',
                'created_by_user_id' => 2,
            ],
            [
                'public_ref' => 'RB-2315',
                'customer_id' => $customerIds['nyasha.gumbo@gmail.com'],
                'table_id' => $tableIds['T-08'],
                'guest_name' => 'Nyasha Gumbo',
                'guest_phone' => '+263 78 663 1290',
                'guest_email' => 'nyasha.gumbo@gmail.com',
                'date' => '2026-07-21',
                'time' => '19:30',
                'party_size' => 3,
                'status' => 'Upcoming',
                'notes' => 'Gluten-free again, same table preferred.',
                'source' => 'App',
                'created_by_user_id' => 2,
            ],
        ];

        // Insert reservations
        foreach ($reservations as $reservationData) {
            $restaurant->reservations()->create($reservationData);
        }

        // 8. Create Activity Log (matching data.js)
        $reservationIds = $restaurant->reservations()->pluck('id', 'public_ref')->toArray();

        $activities = [
            [
                'actor_user_id' => 2,
                'actor_label' => 'Mike Manager',
                'icon' => 'bi-calendar-check',
                'tone' => 'emerald',
                'description' => 'Tanaka Moyo confirmed a table for 4, tonight 7:30pm',
                'entity_type' => 'reservation',
                'entity_id' => $reservationIds['RB-2303'] ?? null,
                'created_at' => now()->subMinutes(3),
            ],
            [
                'actor_user_id' => 3,
                'actor_label' => 'Tara Host',
                'icon' => 'bi-person-plus',
                'tone' => 'gold',
                'description' => 'New customer profile created — Rutendo Chirwa',
                'entity_type' => 'customer',
                'entity_id' => $customerIds['rutendo.c@outlook.com'] ?? null,
                'created_at' => now()->subMinutes(18),
            ],
            [
                'actor_user_id' => 3,
                'actor_label' => 'Tara Host',
                'icon' => 'bi-x-circle',
                'tone' => 'rust',
                'description' => 'Reservation #RB-2304 cancelled by guest',
                'entity_type' => 'reservation',
                'entity_id' => $reservationIds['RB-2304'] ?? null,
                'created_at' => now()->subMinutes(42),
            ],
            [
                'actor_user_id' => 2,
                'actor_label' => 'Mike Manager',
                'icon' => 'bi-door-open',
                'tone' => 'slate',
                'description' => 'Table T-08 marked as walk-in, party of 2',
                'entity_type' => 'table',
                'entity_id' => $tableIds['T-08'] ?? null,
                'created_at' => now()->subHour(),
            ],
            [
                'actor_user_id' => 2,
                'actor_label' => 'Mike Manager',
                'icon' => 'bi-star',
                'tone' => 'gold',
                'description' => 'Farai Chikono flagged as VIP after 10th visit',
                'entity_type' => 'customer',
                'entity_id' => $customerIds['farai.chikono@gmail.com'] ?? null,
                'created_at' => now()->subHours(2),
            ],
            [
                'actor_user_id' => null,
                'actor_label' => 'System',
                'icon' => 'bi-calendar-plus',
                'tone' => 'emerald',
                'description' => 'Group booking of 12 placed for Sat, 19 Jul',
                'entity_type' => 'reservation',
                'entity_id' => null, // not linked to a specific reservation in data.js
                'created_at' => now()->subHours(3),
            ],
        ];

        // Insert activities
        foreach ($activities as $activityData) {
            $restaurant->activityLog()->create($activityData);
        }

        // 9. Create Notifications (matching data.js)
        $notifications = [
            [
                'title' => 'Large party incoming',
                'message' => 'Party of 12 arrives in 40 minutes — table T-20 assigned.',
                'is_read' => false,
                'created_at' => now()->subMinutes(12),
            ],
            [
                'title' => 'Reservation cancelled',
                'message' => 'Chiedza Mutasa cancelled 8:00pm booking for 2.',
                'is_read' => false,
                'created_at' => now()->subMinutes(45),
            ],
            [
                'title' => 'VIP guest tonight',
                'message' => 'Farai Chikono (VIP) is booked for 7:30pm, Table T-12.',
                'is_read' => false,
                'created_at' => now()->subHour(),
            ],
            [
                'title' => 'Deposit received',
                'message' => 'Deposit of $60 received for booking RB-2305.',
                'is_read' => true,
                'created_at' => now()->subHours(3),
            ],
            [
                'title' => 'Weekly summary ready',
                'message' => 'Your analytics report for last week is ready to view.',
                'is_read' => true,
                'created_at' => now()->subDay(),
            ],
        ];

        // Insert notifications
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

        // 11. Create Waitlist (matching data.js)
        $waitlistData = [
            [
                'name' => 'Tafadzwa Museka',
                'phone' => '+263 77 660 4432',
                'party_size' => 3,
                'quoted_wait_minutes' => 20,
                'notes' => 'Prefers outdoor seating',
                'added_at' => now()->setTime(19, 12, 0),
                'status' => 'Waiting',
            ],
            [
                'name' => 'Ropafadzo Dube',
                'phone' => '+263 71 908 2217',
                'party_size' => 5,
                'quoted_wait_minutes' => 35,
                'notes' => '',
                'added_at' => now()->setTime(19, 20, 0),
                'status' => 'Waiting',
            ],
        ];

        foreach ($waitlistData as $waitlistItem) {
            $restaurant->waitlist()->create($waitlistItem);
        }

        $this->command->info('✅ Demo data seeded successfully!');
    }
}
