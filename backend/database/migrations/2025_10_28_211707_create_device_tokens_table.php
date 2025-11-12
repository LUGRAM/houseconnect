<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('token')->unique(); // Token unique par appareil
            $table->string('platform')->nullable(); // ex: android, ios, web
            $table->string('app_version')->nullable(); // version de l'app mobile
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            // Index pour accélérer les recherches par utilisateur
            $table->index(['user_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
