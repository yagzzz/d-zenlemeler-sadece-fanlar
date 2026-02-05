<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->uuid('required_tier_id')->nullable()->after('ppv_currency');
            $table->foreign('required_tier_id')->references('id')->on('tiers')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE contents MODIFY visibility ENUM('public','registered_only','subscriber_only','ppv','tier_only') NOT NULL DEFAULT 'public'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE contents MODIFY visibility ENUM('public','registered_only','subscriber_only','ppv') NOT NULL DEFAULT 'public'");
        }

        Schema::table('contents', function (Blueprint $table) {
            $table->dropForeign(['required_tier_id']);
            $table->dropColumn('required_tier_id');
        });
    }
};
