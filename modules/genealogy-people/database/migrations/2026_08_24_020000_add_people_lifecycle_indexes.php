<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('genealogy_people')) {
            return;
        }

        Schema::table('genealogy_people', function (Blueprint $table): void {
            if (! Schema::hasColumn('genealogy_people', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('death_place');
            }
            $table->index(['team_id', 'is_public']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('genealogy_people')) {
            return;
        }

        Schema::table('genealogy_people', function (Blueprint $table): void {
            $table->dropIndex(['team_id', 'is_public']);
            if (Schema::hasColumn('genealogy_people', 'is_public')) {
                $table->dropColumn('is_public');
            }
        });
    }
};
