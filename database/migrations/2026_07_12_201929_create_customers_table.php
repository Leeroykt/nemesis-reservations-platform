<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('name', 120);
            $table->string('email', 160)->nullable();
            $table->string('phone', 40);
            $table->integer('visits')->default(0);
            $table->date('last_visit_at')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->decimal('lifetime_spend', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['restaurant_id', 'name', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};