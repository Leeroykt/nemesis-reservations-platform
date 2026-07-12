<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('tagline', 160)->nullable();
            $table->string('email', 160)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('timezone', 60)->default('Africa/Harare');
            $table->string('currency', 3)->default('USD');
            $table->integer('seats')->default(0);
            $table->integer('tables_count')->default(0);
            $table->string('logo_path', 255)->nullable();
            $table->string('primary_color_hex', 7)->default('#C9A227');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
