<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PaymentStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('property_id')
                ->nullable()
                ->constrained('properties')
                ->restrictOnDelete();

            $table->foreignId('appointment_id')
                ->nullable()
                ->constrained('appointments')
                ->nullOnDelete();

            // Données principales
            $table->decimal('amount', 12, 2);
            $table->string('type')->default('visit')->index();
            $table->string('status')->default(PaymentStatus::PENDING->value)->index();

            // Détails du prestataire de paiement
            $table->string('provider')->default('cinetpay');
            $table->string('provider_ref')->nullable()->unique()->index();
            $table->string('payment_url')->nullable();
            $table->string('hmac_signature')->nullable();

            // Frais éventuels
            $table->decimal('fees', 12, 2)->default(0.00);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
