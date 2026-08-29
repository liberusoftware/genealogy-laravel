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
            $table->json('generated_output')->nullable()->after('metadata');
            $table->timestamp('generated_at')->nullable()->after('generated_output');
        });
    }

    public function down(): void
    {
        Schema::table('genealogy_reports', function (Blueprint $table): void {
            $table->dropColumn(['generated_output', 'generated_at']);
        });
    }
};
