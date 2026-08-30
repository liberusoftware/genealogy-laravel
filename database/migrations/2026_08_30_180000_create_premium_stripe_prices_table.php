<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('premium_stripe_prices', function (Blueprint $table): void {
            $table->id();
            $table->string('application_key', 128);
            $table->string('interval', 16);
            $table->string('stripe_product_id');
            $table->string('stripe_price_id')->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 3);
            $table->timestamps();
            $table->unique(['application_key', 'interval']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_stripe_prices');
    }
};
