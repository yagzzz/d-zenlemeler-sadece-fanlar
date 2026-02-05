<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('content_id')->nullable()->constrained('contents')->nullOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedBigInteger('amount_atomic');
            $table->string('currency', 10)->default('XMR');
            $table->string('message', 280)->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->timestamps();

            $table->unique('invoice_id');
            $table->index(['creator_id', 'created_at']);
            $table->index(['content_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
