<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\InvoiceStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Relations principales
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('property_id')
                ->nullable()
                ->constrained('properties')
                ->restrictOnDelete();

            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();

            // Détails financiers
            $table->decimal('amount', 12, 2);
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('due_date')->nullable();
            $table->string('status')->default(InvoiceStatus::UNPAID->value)->index();
            $table->string('pdf_url')->nullable();

            $table->unique(['user_id', 'property_id', 'issued_at'], 'unique_invoice_per_month');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
