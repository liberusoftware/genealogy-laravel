<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('team_integrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('label');
            $table->json('credentials')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['team_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_integrations');
    }
};
