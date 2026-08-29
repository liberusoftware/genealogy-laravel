<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_person_associations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained('genealogy_people')->cascadeOnDelete();
            $table->foreignUuid('associated_person_id')->nullable()->constrained('genealogy_people')->nullOnDelete();
            $table->string('associated_external_id')->nullable();
            $table->string('relationship');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'person_id']);
            $table->index(['team_id', 'associated_person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_person_associations');
    }
};
