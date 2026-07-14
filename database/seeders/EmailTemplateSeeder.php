<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::first();
        if (! $restaurant) {
            return;
        }

        EmailTemplate::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'key' => 'confirm'],
            [
                'name' => 'Booking Confirmation',
                'subject' => 'Your booking at {{restaurant_name}} is confirmed',
                'body' => "Dear {{guest_name}},\n\nYour booking for {{party_size}} guests on {{date}} at {{time}} has been confirmed. Your reference is {{public_ref}}.\n\nWe look forward to serving you.",
            ]
        );
    }
}
