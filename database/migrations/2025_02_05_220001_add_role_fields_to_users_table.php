<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'creator', 'admin'])->default('user')->after('password');
            $table->timestamp('creator_applied_at')->nullable()->after('role');
            $table->timestamp('creator_approved_at')->nullable()->after('creator_applied_at');
            $table->timestamp('creator_rejected_at')->nullable()->after('creator_approved_at');
            $table->text('creator_rejection_reason')->nullable()->after('creator_rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'creator_applied_at',
                'creator_approved_at',
                'creator_rejected_at',
                'creator_rejection_reason',
            ]);
        });
    }
};
