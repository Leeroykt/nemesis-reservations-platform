<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('public_ref', 20)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('table_id')->nullable()->constrained()->onDelete('set null');
            $table->string('guest_name', 120);
            $table->string('guest_phone', 40);
            $table->string('guest_email', 160)->nullable();
            $table->date('date');
            $table->time('time');
            $table->integer('party_size');
            $table->enum('status', ['Upcoming', 'Confirmed', 'Completed', 'Cancelled'])->default('Upcoming');
            $table->text('notes')->nullable();
            $table->enum('source', ['Website', 'Phone', 'App', 'Walk-in'])->default('App');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();

            // CRITICAL INDEX: Conflict detection depends on this
            $table->index(['restaurant_id', 'date', 'table_id', 'status']);
            $table->index(['restaurant_id', 'status']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};