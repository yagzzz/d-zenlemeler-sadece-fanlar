<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->boolean('is_most_popular')->default(false)->after('position');
            $table->unsignedBigInteger('yearly_price_atomic')->nullable()->after('price_atomic');
            $table->unsignedSmallInteger('yearly_duration_days')->default(365)->after('yearly_price_atomic');
        });
    }

    public function down(): void
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->dropColumn(['is_most_popular', 'yearly_price_atomic', 'yearly_duration_days']);
        });
    }
};
