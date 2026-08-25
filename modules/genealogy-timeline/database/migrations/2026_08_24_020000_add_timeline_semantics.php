<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('timeline_events', function (Blueprint $table): void {
            $table->string('kind')->default('life_event')->after('id');
            $table->uuid('subject_person_id')->nullable()->after('kind');
            $table->string('family_key')->nullable()->after('subject_person_id');
            $table->date('event_date')->nullable()->after('family_key');
            $table->date('date_start')->nullable()->after('event_date');
            $table->date('date_end')->nullable()->after('date_start');
            $table->string('date_precision')->default('exact')->after('date_end');
            $table->uuid('place_id')->nullable()->after('date_precision');
            $table->text('description')->nullable()->after('place_id');
            $table->text('historical_context')->nullable()->after('description');
            $table->string('conflict_group')->nullable()->after('historical_context');
            $table->unsignedTinyInteger('confidence')->nullable()->after('conflict_group');
            $table->string('source_reference')->nullable()->after('confidence');
            $table->boolean('is_private')->default(false)->after('source_reference');
            $table->index(['team_id', 'subject_person_id', 'event_date']);
            $table->index(['team_id', 'family_key', 'event_date']);
            $table->index(['team_id', 'conflict_group']);
        });
    }

    public function down(): void
    {
        Schema::table('timeline_events', function (Blueprint $table): void {
            $table->dropIndex('timeline_events_team_id_subject_person_id_event_date_index');
            $table->dropIndex('timeline_events_team_id_family_key_event_date_index');
            $table->dropIndex('timeline_events_team_id_conflict_group_index');
            $table->dropColumn(['kind', 'subject_person_id', 'family_key', 'event_date', 'date_start', 'date_end', 'date_precision', 'place_id', 'description', 'historical_context', 'conflict_group', 'confidence', 'source_reference', 'is_private']);
        });
    }
};
