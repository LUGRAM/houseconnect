<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Identité
            $table->string('name')->index();

            // Coordonnées
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->unique()->nullable();

            // Authentification
            $table->string('password', 255);
            $table->rememberToken();

            // Vérifications
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
