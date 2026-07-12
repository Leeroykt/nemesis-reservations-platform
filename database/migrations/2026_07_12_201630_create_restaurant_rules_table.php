<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->unique()->onDelete('cascade');
            $table->integer('max_party_size')->default(14);
            $table->integer('slot_length_minutes')->default(90);
            $table->integer('buffer_minutes')->default(15);
            $table->integer('cancellation_window_hours')->default(4);
            $table->integer('deposit_required_above')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_rules');
    }
};