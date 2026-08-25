<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_dna_matches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('kit_id')->constrained('genealogy_dna_kits')->cascadeOnDelete();
            $table->string('external_id');
            $table->string('display_name')->nullable();
            $table->string('predicted_relationship')->nullable();
            $table->unsignedTinyInteger('confidence')->nullable();
            $table->decimal('total_cm', 10, 2)->nullable();
            $table->unsignedInteger('shared_segments')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_private')->default(true);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['kit_id', 'external_id']);
            $table->index(['team_id', 'status']);
        });

        Schema::create('genealogy_dna_segments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('match_id')->constrained('genealogy_dna_matches')->cascadeOnDelete();
            $table->unsignedSmallInteger('chromosome');
            $table->unsignedBigInteger('start_position');
            $table->unsignedBigInteger('end_position');
            $table->decimal('centimorgans', 10, 2)->nullable();
            $table->unsignedInteger('snps')->nullable();
            $table->string('side')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'match_id', 'chromosome']);
        });

        Schema::create('genealogy_dna_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['team_id', 'name']);
        });

        Schema::create('genealogy_dna_group_matches', function (Blueprint $table): void {
            $table->foreignUuid('group_id')->constrained('genealogy_dna_groups')->cascadeOnDelete();
            $table->foreignUuid('match_id')->constrained('genealogy_dna_matches')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['group_id', 'match_id']);
        });

        Schema::create('genealogy_dna_consents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('kit_id')->constrained('genealogy_dna_kits')->cascadeOnDelete();
            $table->string('scope');
            $table->boolean('granted');
            $table->string('policy_version')->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'kit_id', 'scope']);
        });

        Schema::create('genealogy_dna_relationships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('match_id')->constrained('genealogy_dna_matches')->cascadeOnDelete();
            $table->uuid('person_id')->nullable();
            $table->string('relationship_type');
            $table->unsignedTinyInteger('confidence')->nullable();
            $table->string('status')->default('proposed');
            $table->text('rationale')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'match_id']);
            $table->index(['team_id', 'person_id']);
        });

        Schema::create('genealogy_dna_notes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('noteable_type');
            $table->uuid('noteable_id');
            $table->text('body');
            $table->timestamps();
            $table->index(['team_id', 'noteable_type', 'noteable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_dna_notes');
        Schema::dropIfExists('genealogy_dna_relationships');
        Schema::dropIfExists('genealogy_dna_consents');
        Schema::dropIfExists('genealogy_dna_group_matches');
        Schema::dropIfExists('genealogy_dna_groups');
        Schema::dropIfExists('genealogy_dna_segments');
        Schema::dropIfExists('genealogy_dna_matches');
    }
};
