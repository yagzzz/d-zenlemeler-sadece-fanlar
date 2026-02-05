<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->foreignId('last_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'creator_id']);
            $table->index(['creator_id', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
