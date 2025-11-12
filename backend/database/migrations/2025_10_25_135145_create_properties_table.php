<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            // Relation : propriétaire du bien
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Informations principales
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');

            // Valeurs financières
            $table->decimal('price', 12, 2);
            $table->decimal('visit_price', 12, 2)->default(5000);
            $table->decimal('monthly_rent', 12, 2)->default(0);

            // Statuts
            $table->boolean('is_validated')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
