<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('genealogy_dna_kits', function (Blueprint $table): void {
            $table->string('file_path')->nullable()->after('metadata');
            $table->string('file_hash', 64)->nullable()->after('file_path');
            $table->string('file_format')->nullable()->after('file_hash');
            $table->unsignedInteger('snp_count')->nullable()->after('file_format');
        });
    }

    public function down(): void
    {
        Schema::table('genealogy_dna_kits', function (Blueprint $table): void {
            $table->dropColumn(['file_path', 'file_hash', 'file_format', 'snp_count']);
        });
    }
};
