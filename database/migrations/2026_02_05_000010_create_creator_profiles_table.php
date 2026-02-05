<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('tagline', 160)->nullable();
            $table->foreignId('avatar_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('banner_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->json('categories')->default('[]');
            $table->json('tags')->default('[]');
            $table->json('social_links')->default('{}');
            $table->string('wallet_xmr_address', 200)->nullable();
            $table->unsignedBigInteger('default_subscription_price_atomic')->nullable();
            $table->string('currency', 10)->default('XMR');
            $table->boolean('allow_free_content')->default(true);
            $table->boolean('allow_registered_only')->default(true);
            $table->boolean('allow_ppv')->default(true);
            $table->boolean('allow_tips')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_profiles');
    }
};
