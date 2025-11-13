<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;

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
                ->constrained('properties')
                ->restrictOnDelete();

            // Données principales
            $table->decimal('amount', 10, 2);
            $table->decimal('fees', 10, 2)->default(0);
            $table->string('provider')->nullable();      // ex: 'CinetPay', 'Flutterwave'
            $table->string('provider_ref')->nullable();  // référence externe du prestataire
            $table->string('hmac_signature')->nullable(); // sécurité / signature
            $table->string('payment_url')->nullable();    // URL vers la page de paiement

            // Enums : statut et type
            $table->string('status')
                ->default(PaymentStatus::PENDING->value)
                ->index();

            $table->string('type')
                ->default(PaymentType::VISIT->value)
                ->index();

            // Timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
