<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_records', function (Blueprint $table): void {
            $table->string('kind')->default('source')->after('name');
            $table->string('repository')->nullable()->after('kind');
            $table->text('citation')->nullable()->after('repository');
            $table->text('extract')->nullable()->after('citation');
            $table->text('assertion')->nullable()->after('extract');
            $table->text('proof_conclusion')->nullable()->after('assertion');
            $table->unsignedTinyInteger('confidence')->default(0)->after('proof_conclusion');
            $table->string('source_url')->nullable()->after('confidence');
            $table->date('event_date')->nullable()->after('source_url');
            $table->foreignUuid('subject_person_id')->nullable()->after('event_date')->constrained('genealogy_people')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('subject_person_id');
            $table->index(['team_id', 'kind', 'confidence']);
            $table->index(['team_id', 'subject_person_id']);
        });
    }

    public function down(): void
    {
        Schema::table('evidence_records', function (Blueprint $table): void {
            $table->dropForeign(['subject_person_id']);
            $table->dropIndex(['team_id', 'kind', 'confidence']);
            $table->dropIndex(['team_id', 'subject_person_id']);
            $table->dropColumn(['kind', 'repository', 'citation', 'extract', 'assertion', 'proof_conclusion', 'confidence', 'source_url', 'event_date', 'subject_person_id', 'reviewed_at']);
        });
    }
};
