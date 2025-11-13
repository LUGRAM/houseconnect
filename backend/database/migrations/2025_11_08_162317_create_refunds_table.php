<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            // Paiement d'origine
            $table->foreignId('payment_id')
                  ->constrained('payments')
                  ->cascadeOnDelete();

            // Utilisateur ayant demandé le remboursement (client ou bailleur)
            $table->foreignId('requested_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Admin ayant approuvé ou rejeté
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Données de remboursement
            |--------------------------------------------------------------------------
            */

            // Montant du remboursement
            $table->decimal('amount', 12, 2);

            // Raison fournie (ex: annulation tardive, erreur, impossibilité technique)
            $table->string('reason')->nullable();

            // pending = en attente
            // approved = approuvé/admin OK
            // rejected = refusé
            // failed   = tentative échouée (ex: gateway)
            $table->enum('status', ['pending', 'approved', 'rejected', 'failed'])
                  ->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Suivi temporel
            |--------------------------------------------------------------------------
            */

            // Quand le client a demandé le remboursement
            $table->timestamp('requested_at')->useCurrent();

            // Quand l'admin l'a approuvé
            $table->timestamp('approved_at')->nullable();

            // Message d'erreur (ex: retour API du prestataire)
            $table->string('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
