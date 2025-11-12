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

            //Liens principaux
            $table->foreignId('payment_id')
                  ->constrained('payments')
                  ->cascadeOnDelete();

            $table->foreignId('requested_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            //Données de remboursement
            $table->decimal('amount', 12, 2);
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'failed'])
                  ->default('pending');

            // Suivi temporel
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->string('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
