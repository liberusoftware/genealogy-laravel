<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_person_names', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->string('type')->default('birth');
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'person_id']);
        });

        Schema::create('genealogy_person_identities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->string('type');
            $table->string('value');
            $table->string('label')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'type', 'value']);
            $table->index(['team_id', 'person_id']);
        });

        Schema::create('genealogy_person_life_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->string('type');
            $table->date('date')->nullable();
            $table->string('place')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'person_id', 'type']);
        });

        Schema::create('genealogy_merge_candidates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->foreignUuid('candidate_person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->decimal('score', 8, 4)->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'person_id', 'candidate_person_id']);
            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_merge_candidates');
        Schema::dropIfExists('genealogy_person_life_events');
        Schema::dropIfExists('genealogy_person_identities');
        Schema::dropIfExists('genealogy_person_names');
    }
};
