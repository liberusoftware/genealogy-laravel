<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_evidence_sources', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url', 2048)->nullable();
            $table->json('archive_metadata')->nullable();
            $table->string('record_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'record_type']);
        });

        Schema::create('genealogy_evidence_repositories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('source_id')->nullable()->constrained('genealogy_evidence_sources')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'source_id']);
        });

        Schema::create('genealogy_evidence_citations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('source_id')->constrained('genealogy_evidence_sources')->cascadeOnDelete();
            $table->foreignUuid('repository_id')->nullable()->constrained('genealogy_evidence_repositories')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('volume')->nullable();
            $table->string('page')->nullable();
            $table->text('text')->nullable();
            $table->unsignedTinyInteger('confidence')->default(0);
            $table->date('event_date')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'source_id']);
        });

        Schema::create('genealogy_evidence_extracts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('citation_id')->constrained('genealogy_evidence_citations')->cascadeOnDelete();
            $table->longText('content');
            $table->longText('transcription')->nullable();
            $table->string('page')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'citation_id']);
        });

        Schema::create('genealogy_evidence_assertions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('subject_person_id')->nullable()->constrained('genealogy_people')->nullOnDelete();
            $table->foreignUuid('citation_id')->nullable()->constrained('genealogy_evidence_citations')->nullOnDelete();
            $table->foreignUuid('extract_id')->nullable()->constrained('genealogy_evidence_extracts')->nullOnDelete();
            $table->text('statement');
            $table->unsignedTinyInteger('confidence')->default(0);
            $table->string('status')->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'subject_person_id']);
        });

        Schema::create('genealogy_evidence_proof_conclusions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('assertion_id')->unique()->constrained('genealogy_evidence_assertions')->cascadeOnDelete();
            $table->text('conclusion');
            $table->unsignedTinyInteger('confidence')->default(0);
            $table->string('status')->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_evidence_proof_conclusions');
        Schema::dropIfExists('genealogy_evidence_assertions');
        Schema::dropIfExists('genealogy_evidence_extracts');
        Schema::dropIfExists('genealogy_evidence_citations');
        Schema::dropIfExists('genealogy_evidence_repositories');
        Schema::dropIfExists('genealogy_evidence_sources');
    }
};
