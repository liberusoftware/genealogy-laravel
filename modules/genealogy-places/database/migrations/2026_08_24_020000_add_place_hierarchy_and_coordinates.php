<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('genealogy_places', function (Blueprint $table): void {
            $table->foreignUuid('parent_id')->nullable()->after('name')->constrained('genealogy_places')->nullOnDelete();
            $table->json('historical_names')->nullable()->after('parent_id');
            $table->decimal('latitude', 10, 7)->nullable()->after('historical_names');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('jurisdiction')->nullable()->after('longitude');
            $table->boolean('is_current')->default(true)->after('jurisdiction');
            $table->index(['team_id', 'parent_id']);
            $table->index(['team_id', 'jurisdiction']);
        });
    }

    public function down(): void
    {
        Schema::table('genealogy_places', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['team_id', 'parent_id']);
            $table->dropIndex(['team_id', 'jurisdiction']);
            $table->dropColumn(['parent_id', 'historical_names', 'latitude', 'longitude', 'jurisdiction', 'is_current']);
        });
    }
};
