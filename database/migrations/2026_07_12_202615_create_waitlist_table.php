<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name', 120);
            $table->string('phone', 40);
            $table->integer('party_size');
            $table->integer('quoted_wait_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Waiting', 'Seated', 'Left'])->default('Waiting');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist');
    }
};