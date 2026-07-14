<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This is a secondary defensive layer.
        // While the application logic + lockForUpdate() prevent double-booking,
        // this unique constraint ensures the database won't allow overlapping
        // reservations on the same table even if application logic fails.
        //
        // Note: This is a simplified version. A true business constraint would
        // need to handle time ranges, not just exact date+time+table.
        // For now, we use a partial unique index on (table_id, date, time)
        // as a first-line defense against exact duplicate entries.
        Schema::table('reservations', function (Blueprint $table) {
            $table->unique(['table_id', 'date', 'time'], 'unique_reservation_slot');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropUnique('unique_reservation_slot');
        });
    }
};
