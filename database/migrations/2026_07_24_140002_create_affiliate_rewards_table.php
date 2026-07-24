<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_rewards', function (Blueprint $table): void {
            $table->id();
            // The referrer being rewarded.
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // How many qualified referrals this reward consumed (= N at grant time).
            $table->unsignedInteger('referrals_consumed');
            // 'stripe_credit' | 'access_extension' — see AffiliateReward consts.
            $table->string('delivery');
            // One month's price in cents, for the stripe_credit case; null otherwise.
            $table->unsignedInteger('amount_cents')->nullable();
            $table->timestamp('granted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_rewards');
    }
};
