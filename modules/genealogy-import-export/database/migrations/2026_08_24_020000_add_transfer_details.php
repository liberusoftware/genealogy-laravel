<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('genealogy_data_transfers', function (Blueprint $table): void {
            $table->string('format')->nullable()->after('name');
            $table->string('direction')->nullable()->after('format');
            $table->unsignedInteger('records_count')->default(0)->after('direction');
            $table->index(['team_id', 'format', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::table('genealogy_data_transfers', function (Blueprint $table): void {
            $table->dropIndex(['team_id', 'format', 'direction']);
            $table->dropColumn(['format', 'direction', 'records_count']);
        });
    }
};
