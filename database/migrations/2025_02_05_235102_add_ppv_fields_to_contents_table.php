<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->unsignedBigInteger('ppv_price_atomic')->nullable()->after('visibility');
            $table->string('ppv_currency', 10)->default('XMR')->after('ppv_price_atomic');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['ppv_price_atomic', 'ppv_currency']);
        });
    }
};
