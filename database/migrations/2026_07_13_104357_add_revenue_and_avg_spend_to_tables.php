<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('revenue', 10, 2)->nullable()->after('party_size');
        });
        Schema::table('restaurant_rules', function (Blueprint $table) {
            $table->decimal('avg_spend_per_person', 10, 2)->default(25)->after('deposit_required_above');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('revenue');
        });
        Schema::table('restaurant_rules', function (Blueprint $table) {
            $table->dropColumn('avg_spend_per_person');
        });
    }
};
