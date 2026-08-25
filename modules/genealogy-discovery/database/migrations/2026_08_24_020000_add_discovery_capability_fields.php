<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('genealogy_discovery_matches', function (Blueprint $table): void {
            $table->string('kind')->default('hint')->after('id');
            $table->uuid('subject_id')->nullable()->after('kind');
            $table->uuid('related_id')->nullable()->after('subject_id');
            $table->unsignedTinyInteger('confidence')->nullable()->after('related_id');
            $table->text('rationale')->nullable()->after('confidence');
            $table->string('source_type')->nullable()->after('rationale');
            $table->timestamp('detected_at')->nullable()->after('source_type');
            $table->timestamp('reviewed_at')->nullable()->after('detected_at');
            $table->index(['team_id', 'kind', 'status']);
            $table->index(['team_id', 'subject_id', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::table('genealogy_discovery_matches', function (Blueprint $table): void {
            $table->dropIndex('genealogy_discovery_matches_team_id_kind_status_index');
            $table->dropIndex('genealogy_discovery_matches_team_id_subject_id_related_id_index');
            $table->dropColumn(['kind', 'subject_id', 'related_id', 'confidence', 'rationale', 'source_type', 'detected_at', 'reviewed_at']);
        });
    }
};
