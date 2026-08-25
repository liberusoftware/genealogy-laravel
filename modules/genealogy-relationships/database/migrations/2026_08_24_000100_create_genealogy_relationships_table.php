<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_relationships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->foreignUuid('related_person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->string('type');
            $table->unsignedSmallInteger('confidence')->default(100);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'person_id', 'related_person_id', 'type']);
            $table->index(['team_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_relationships');
    }
};
