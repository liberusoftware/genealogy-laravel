<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_smart_matches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->string('external_tree_id')->nullable();
            $table->string('external_person_id')->nullable();
            $table->string('match_source');
            $table->string('record_category')->nullable();
            $table->json('match_data');
            $table->json('search_criteria')->nullable();
            $table->unsignedTinyInteger('confidence_score')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'person_id', 'confidence_score']);
            $table->index(['team_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_smart_matches');
    }
};
