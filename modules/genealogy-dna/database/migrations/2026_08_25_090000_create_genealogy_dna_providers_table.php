<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_dna_providers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('status')->default('active');
            $table->string('website')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['team_id', 'slug']);
            $table->index(['team_id', 'status']);
        });

        Schema::table('genealogy_dna_kits', function (Blueprint $table): void {
            $table->foreignUuid('provider_id')->nullable()->after('provider')->constrained('genealogy_dna_providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('genealogy_dna_kits', function (Blueprint $table): void {
            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
        });

        Schema::dropIfExists('genealogy_dna_providers');
    }
};
