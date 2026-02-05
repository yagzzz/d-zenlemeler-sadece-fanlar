<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->uuid('pending_tier_id')->nullable()->after('tier_id');
            $table->timestamp('pending_effective_at')->nullable()->after('pending_tier_id');
            $table->foreign('pending_tier_id')->references('id')->on('tiers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['pending_tier_id']);
            $table->dropColumn(['pending_tier_id', 'pending_effective_at']);
        });
    }
};
