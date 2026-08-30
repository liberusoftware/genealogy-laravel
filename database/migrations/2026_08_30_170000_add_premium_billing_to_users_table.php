<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->timestamp('premium_started_at')->nullable();
            $table->timestamp('premium_cancelled_at')->nullable();
            $table->unsignedInteger('dna_uploads_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at',
                'is_premium', 'premium_started_at', 'premium_cancelled_at',
                'dna_uploads_count',
            ]);
        });
    }
};
