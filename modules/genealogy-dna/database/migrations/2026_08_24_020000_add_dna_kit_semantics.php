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
            $table->string('provider')->nullable()->after('id');
            $table->string('external_id')->nullable()->after('provider');
            $table->uuid('person_id')->nullable()->after('external_id');
            $table->string('test_type')->nullable()->after('person_id');
            $table->string('consent_status')->default('pending')->after('test_type');
            $table->timestamp('consented_at')->nullable()->after('consent_status');
            $table->timestamp('revoked_at')->nullable()->after('consented_at');
            $table->string('revocation_reason')->nullable()->after('revoked_at');
            $table->index(['team_id', 'provider', 'external_id']);
            $table->index(['team_id', 'person_id']);
            $table->index(['team_id', 'consent_status']);
        });
    }

    public function down(): void
    {
        Schema::table('genealogy_dna_kits', function (Blueprint $table): void {
            $table->dropIndex('genealogy_dna_kits_team_id_provider_external_id_index');
            $table->dropIndex('genealogy_dna_kits_team_id_person_id_index');
            $table->dropIndex('genealogy_dna_kits_team_id_consent_status_index');
            $table->dropColumn(['provider', 'external_id', 'person_id', 'test_type', 'consent_status', 'consented_at', 'revoked_at', 'revocation_reason']);
        });
    }
};
