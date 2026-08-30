<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_gamification_achievements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('genealogy');
            $table->unsignedInteger('points')->default(0);
            $table->json('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('genealogy_gamification_points', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('activity_type');
            $table->integer('points');
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->nullableUuidMorphs('related');
            $table->timestamps();
            $table->index(['team_id', 'user_id', 'created_at']);
        });

        Schema::create('genealogy_gamification_user_achievements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('achievement_id')->constrained('genealogy_gamification_achievements')->cascadeOnDelete();
            $table->timestamp('unlocked_at')->nullable();
            $table->json('progress_data')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'user_id', 'achievement_id']);
            $table->index(['team_id', 'user_id']);
        });

        Schema::create('genealogy_gamification_user_progress', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('achievement_id')->constrained('genealogy_gamification_achievements')->cascadeOnDelete();
            $table->unsignedInteger('current_progress')->default(0);
            $table->unsignedInteger('target_progress')->default(0);
            $table->json('progress_data')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'user_id', 'achievement_id']);
            $table->index(['team_id', 'user_id', 'last_updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_gamification_user_progress');
        Schema::dropIfExists('genealogy_gamification_user_achievements');
        Schema::dropIfExists('genealogy_gamification_points');
        Schema::dropIfExists('genealogy_gamification_achievements');
    }
};
