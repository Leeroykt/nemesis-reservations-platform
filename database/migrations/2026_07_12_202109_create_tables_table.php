<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->string('code', 10);
            $table->string('zone', 60)->nullable();
            $table->integer('capacity');
            $table->enum('shape', ['round', 'square', 'rect'])->default('square');
            $table->decimal('pos_x', 5, 2)->default(0);
            $table->decimal('pos_y', 5, 2)->default(0);
            $table->enum('status', ['Available', 'Occupied', 'Reserved', 'Cleaning'])->default('Available');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['restaurant_id', 'code']);
            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};