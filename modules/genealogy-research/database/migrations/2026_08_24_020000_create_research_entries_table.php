<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('research_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('research_project_id')->constrained('research_projects')->cascadeOnDelete();
            $table->string('kind');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('status')->default('open');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'research_project_id', 'kind']);
            $table->index(['team_id', 'status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('research_entries');
    }
};
