<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('invoice_type', ['subscription', 'ppv']);
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('amount_atomic');
            $table->string('currency', 10)->default('XMR');
            $table->string('address', 255);
            $table->string('payment_reference', 255)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['payer_id', 'status']);
            $table->index(['payee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
