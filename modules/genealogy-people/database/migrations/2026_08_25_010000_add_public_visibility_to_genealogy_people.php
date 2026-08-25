<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('genealogy_people') && ! Schema::hasColumn('genealogy_people', 'is_public')) {
            Schema::table('genealogy_people', fn (Blueprint $table) => $table->boolean('is_public')->default(false)->after('death_place'));
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('genealogy_people') && Schema::hasColumn('genealogy_people', 'is_public')) {
            Schema::table('genealogy_people', fn (Blueprint $table) => $table->dropColumn('is_public'));
        }
    }
};
