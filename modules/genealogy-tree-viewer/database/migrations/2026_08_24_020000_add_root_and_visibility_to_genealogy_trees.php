<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('genealogy_trees') || Schema::hasColumn('genealogy_trees', 'root_person_id')) {
            return;
        }

        Schema::table('genealogy_trees', function (Blueprint $table): void {
            $table->foreignUuid('root_person_id')->nullable()->after('name')->constrained('genealogy_people')->nullOnDelete();
            $table->boolean('is_public')->default(false)->after('status');
            $table->index(['team_id', 'root_person_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('genealogy_trees') || ! Schema::hasColumn('genealogy_trees', 'root_person_id')) {
            return;
        }

        Schema::table('genealogy_trees', function (Blueprint $table): void {
            $table->dropForeign(['root_person_id']);
            $table->dropIndex(['team_id', 'root_person_id']);
            $table->dropColumn(['root_person_id', 'is_public']);
        });
    }
};
