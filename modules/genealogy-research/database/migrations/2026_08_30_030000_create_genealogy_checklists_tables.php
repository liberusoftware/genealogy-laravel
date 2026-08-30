<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_checklist_templates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->boolean('is_public')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('tags')->nullable();
            $table->string('difficulty_level')->default('beginner');
            $table->unsignedInteger('estimated_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'category', 'is_public']);
        });

        Schema::create('genealogy_checklist_template_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('checklist_template_id')->constrained('genealogy_checklist_templates')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('category')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('estimated_time')->nullable();
            $table->json('resources')->nullable();
            $table->json('tips')->nullable();
            $table->timestamps();
            $table->index(['checklist_template_id', 'sort_order']);
        });

        Schema::create('genealogy_checklists', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('checklist_template_id')->nullable()->constrained('genealogy_checklist_templates')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->string('status')->default('not_started');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('priority')->default('medium');
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'user_id', 'status']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('genealogy_checklist_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('checklist_id')->constrained('genealogy_checklists')->cascadeOnDelete();
            $table->foreignUuid('template_item_id')->nullable()->constrained('genealogy_checklist_template_items')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('estimated_time')->nullable();
            $table->unsignedInteger('actual_time')->nullable();
            $table->json('resources')->nullable();
            $table->json('tips')->nullable();
            $table->timestamps();
            $table->index(['checklist_id', 'sort_order']);
            $table->index(['checklist_id', 'is_completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_checklist_items');
        Schema::dropIfExists('genealogy_checklists');
        Schema::dropIfExists('genealogy_checklist_template_items');
        Schema::dropIfExists('genealogy_checklist_templates');
    }
};
