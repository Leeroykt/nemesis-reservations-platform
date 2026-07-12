<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add restaurant_id foreign key
            $table->foreignId('restaurant_id')
                ->after('id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            // Add role column
            $table->enum('role', ['owner', 'manager', 'host'])
                ->after('password')
                ->default('host');

            // Add avatar_initials
            $table->string('avatar_initials', 4)->nullable()->after('role');

            // Add last_login_at
            $table->timestamp('last_login_at')->nullable()->after('avatar_initials');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn(['restaurant_id', 'role', 'avatar_initials', 'last_login_at']);
        });
    }
};
