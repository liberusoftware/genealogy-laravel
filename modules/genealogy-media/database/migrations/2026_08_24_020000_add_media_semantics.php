<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('genealogy_media_assets', function (Blueprint $table): void {
            $table->string('kind')->default('document')->after('id');
            $table->string('storage_disk')->nullable()->after('kind');
            $table->string('storage_path')->nullable()->after('storage_disk');
            $table->string('mime_type')->nullable()->after('storage_path');
            $table->unsignedBigInteger('byte_size')->nullable()->after('mime_type');
            $table->string('checksum', 128)->nullable()->after('byte_size');
            $table->timestamp('captured_at')->nullable()->after('checksum');
            $table->uuid('captured_place_id')->nullable()->after('captured_at');
            $table->longText('transcription')->nullable()->after('captured_place_id');
            $table->string('transcription_status')->default('not_started')->after('transcription');
            $table->string('transcription_language', 16)->nullable()->after('transcription_status');
            $table->string('rights_holder')->nullable()->after('transcription_language');
            $table->string('rights_status')->nullable()->after('rights_holder');
            $table->string('license_url')->nullable()->after('rights_status');
            $table->date('rights_expires_at')->nullable()->after('license_url');
            $table->boolean('is_public')->default(false)->after('rights_expires_at');
            $table->json('preservation_metadata')->nullable()->after('is_public');
            $table->index(['team_id', 'kind', 'status']);
            $table->index(['team_id', 'checksum']);
            $table->index(['team_id', 'captured_at']);
        });

        Schema::create('genealogy_media_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('media_asset_id')->constrained('genealogy_media_assets')->cascadeOnDelete();
            $table->string('linkable_type');
            $table->uuid('linkable_id');
            $table->string('role')->default('attachment');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['media_asset_id', 'linkable_type', 'linkable_id', 'role'], 'genealogy_media_links_unique_target');
            $table->index(['linkable_type', 'linkable_id']);
            $table->index(['team_id', 'media_asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_media_links');
        Schema::table('genealogy_media_assets', function (Blueprint $table): void {
            $table->dropIndex('genealogy_media_assets_team_id_kind_status_index');
            $table->dropIndex('genealogy_media_assets_team_id_checksum_index');
            $table->dropIndex('genealogy_media_assets_team_id_captured_at_index');
            $table->dropColumn(['kind', 'storage_disk', 'storage_path', 'mime_type', 'byte_size', 'checksum', 'captured_at', 'captured_place_id', 'transcription', 'transcription_status', 'transcription_language', 'rights_holder', 'rights_status', 'license_url', 'rights_expires_at', 'is_public', 'preservation_metadata']);
        });
    }
};
