<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('tier_level');
            $table->unsignedBigInteger('price_atomic');
            $table->string('currency', 10)->default('XMR');
            $table->unsignedSmallInteger('duration_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['creator_id', 'tier_level']);
            $table->index(['creator_id', 'is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
