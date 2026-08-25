<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_collaboration_proposals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedBigInteger('proposer_id')->nullable();
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('proposed');
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'status']);
            $table->index(['team_id', 'proposer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_collaboration_proposals');
    }
};
