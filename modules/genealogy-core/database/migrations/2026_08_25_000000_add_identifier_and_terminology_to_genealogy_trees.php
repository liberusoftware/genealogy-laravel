<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('genealogy_trees')) {
            return;
        }

        Schema::table('genealogy_trees', function (Blueprint $table): void {
            if (! Schema::hasColumn('genealogy_trees', 'identifier')) {
                $table->string('identifier')->nullable()->after('name');
                $table->unique(['team_id', 'identifier']);
            }
            if (! Schema::hasColumn('genealogy_trees', 'terminology')) {
                $table->json('terminology')->nullable()->after('metadata');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('genealogy_trees')) {
            return;
        }

        Schema::table('genealogy_trees', function (Blueprint $table): void {
            if (Schema::hasColumn('genealogy_trees', 'identifier')) {
                $table->dropUnique(['team_id', 'identifier']);
                $table->dropColumn('identifier');
            }
            if (Schema::hasColumn('genealogy_trees', 'terminology')) {
                $table->dropColumn('terminology');
            }
        });
    }
};
