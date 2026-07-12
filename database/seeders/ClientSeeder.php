<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $configPath = base_path('client-config/seed-config.json');

        if (! File::exists($configPath)) {
            $this->command->error('❌ client-config/seed-config.json not found!');
            $this->command->info('Copy seed-config.example.json to seed-config.json and fill in client data.');

            return;
        }

        $config = json_decode(File::get($configPath), true);

        if (! $config) {
            $this->command->error('❌ Invalid JSON in seed-config.json');

            return;
        }

        // 1. Create Restaurant
        $restaurant = Restaurant::create([
            'name' => $config['restaurant']['name'],
            'tagline' => $config['restaurant']['tagline'] ?? null,
            'email' => $config['restaurant']['email'] ?? null,
            'phone' => $config['restaurant']['phone'] ?? null,
            'address' => $config['restaurant']['address'] ?? null,
            'timezone' => $config['restaurant']['timezone'] ?? 'Africa/Harare',
            'currency' => $config['restaurant']['currency'] ?? 'USD',
            'seats' => $config['restaurant']['seats'] ?? 0,
            'tables_count' => $config['restaurant']['tables_count'] ?? 0,
            'logo_path' => $config['restaurant']['logo_path'] ?? null,
            'primary_color_hex' => $config['restaurant']['primary_color_hex'] ?? '#C9A227',
        ]);

        // 2. Create Restaurant Hours
        if (isset($config['hours'])) {
            foreach ($config['hours'] as $hourData) {
                $restaurant->hours()->create($hourData);
            }
        }

        // 3. Create Restaurant Rules
        if (isset($config['rules'])) {
            $restaurant->rules()->create($config['rules']);
        }

        // 4. Create Tables
        if (isset($config['tables'])) {
            foreach ($config['tables'] as $tableData) {
                $restaurant->tables()->create($tableData);
            }
        }

        // 5. Create Owner User
        if (isset($config['owner'])) {
            $restaurant->users()->create([
                'name' => $config['owner']['name'] ?? 'Owner',
                'email' => $config['owner']['email'],
                'password' => Hash::make($config['owner']['password'] ?? 'password123'),
                'role' => 'owner',
            ]);
        }

        $this->command->info('✅ Client seeded successfully from client-config/seed-config.json');
    }
}
