<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('genealogy_reports', function (Blueprint $table): void {
            $table->string('type')->nullable()->after('name');
            $table->index(['team_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('genealogy_reports', function (Blueprint $table): void {
            $table->dropIndex(['team_id', 'type', 'status']);
            $table->dropColumn('type');
        });
    }
};
