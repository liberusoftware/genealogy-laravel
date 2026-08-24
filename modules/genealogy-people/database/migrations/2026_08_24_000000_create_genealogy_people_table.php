<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_people', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('given_name');
            $table->string('family_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('sex', 1)->nullable();
            $table->json('aliases')->nullable();
            $table->json('attributes')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('death_place')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['family_name', 'given_name']);
            $table->index(['team_id', 'family_name', 'given_name']);
            $table->index(['team_id', 'sex']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_people');
    }
};
