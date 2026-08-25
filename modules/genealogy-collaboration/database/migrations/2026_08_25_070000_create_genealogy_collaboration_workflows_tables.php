<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_collaboration_invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('team_id')->index();
            $table->uuid('space_id')->nullable()->index();
            $table->string('email');
            $table->string('role', 40)->default('contributor');
            $table->string('status', 30)->default('pending');
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['team_id', 'email', 'status']);
        });

        Schema::create('genealogy_collaboration_memberships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('team_id')->index();
            $table->uuid('space_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('role', 40)->default('contributor');
            $table->string('status', 30)->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['space_id', 'user_id']);
        });

        Schema::create('genealogy_collaboration_discussions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('team_id')->index();
            $table->uuid('space_id')->nullable()->index();
            $table->uuid('proposal_id')->nullable()->index();
            $table->unsignedBigInteger('author_id')->nullable()->index();
            $table->text('body');
            $table->string('status', 30)->default('open');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('genealogy_collaboration_watches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('watchable_type');
            $table->string('watchable_id');
            $table->timestamps();
            $table->unique(['team_id', 'user_id', 'watchable_type', 'watchable_id'], 'genealogy_collaboration_watches_unique');
        });

        Schema::create('genealogy_collaboration_attributions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('team_id')->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('attributable_type');
            $table->string('attributable_id');
            $table->string('action', 100);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['team_id', 'attributable_type', 'attributable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_collaboration_attributions');
        Schema::dropIfExists('genealogy_collaboration_watches');
        Schema::dropIfExists('genealogy_collaboration_discussions');
        Schema::dropIfExists('genealogy_collaboration_memberships');
        Schema::dropIfExists('genealogy_collaboration_invitations');
    }
};
