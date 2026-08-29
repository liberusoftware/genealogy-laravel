<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('genealogy_trees')) {
            return;
        }

        Schema::create('genealogy_trees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });
    }

    public function down(): void
    {
        // The table is owned by genealogy-core when that package is installed.
        // A standalone tree-viewer installation may still roll this migration
        // back, but never remove a table created by another module.
        if (! Schema::hasTable('genealogy_trees')) {
            return;
        }

        if (Schema::hasColumn('genealogy_trees', 'user_id')) {
            return;
        }

        Schema::dropIfExists('genealogy_trees');
    }
};
