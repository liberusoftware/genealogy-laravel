<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_trees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('identifier')->nullable();
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->uuid('root_person_id')->nullable();
            $table->boolean('is_public')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['team_id', 'name']);
            $table->unique(['team_id', 'identifier']);
            $table->index(['team_id', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_trees');
    }
};
