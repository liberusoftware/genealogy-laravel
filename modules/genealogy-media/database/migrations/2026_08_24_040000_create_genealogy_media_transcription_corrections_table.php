<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_media_transcription_corrections', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('media_asset_id')->constrained('genealogy_media_assets')->cascadeOnDelete();
            $table->longText('original_text');
            $table->longText('corrected_text');
            $table->string('actor_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'media_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_media_transcription_corrections');
    }
};
