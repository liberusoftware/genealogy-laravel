<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_evidence_citation_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('citation_id')->constrained('genealogy_evidence_citations')->cascadeOnDelete();
            $table->foreignUuid('subject_person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->string('group')->default('indi');
            $table->string('page')->nullable();
            $table->string('quality')->nullable();
            $table->text('text')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['team_id', 'citation_id', 'subject_person_id', 'group'], 'citation_links_unique');
            $table->index(['team_id', 'subject_person_id', 'group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_evidence_citation_links');
    }
};
