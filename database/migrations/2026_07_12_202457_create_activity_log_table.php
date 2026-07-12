<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('actor_label', 120);
            $table->string('icon', 40)->default('bi-info-circle');
            $table->enum('tone', ['gold', 'emerald', 'rust', 'slate'])->default('slate');
            $table->string('description', 255);
            $table->string('entity_type', 40)->nullable();
            $table->bigInteger('entity_id')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};