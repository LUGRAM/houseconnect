<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AppointmentStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('property_id')
                ->constrained('properties')
                ->restrictOnDelete();

            // Données principales
            $table->timestamp('scheduled_at');
            $table->string('status')->default(AppointmentStatus::PENDING->value)->index();
            
            $table->index(['user_id', 'property_id']);
            $table->enum('cancel_reason', ['client', 'bailleur', 'system'])->nullable();

            // Suivi des rappels
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
