<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Media\Actions\CreateMediaAsset;
use Liberu\Genealogy\Media\Actions\CreateMediaLink;
use Liberu\Genealogy\Media\Actions\StoreMediaUpload;
use Liberu\Genealogy\Media\Actions\UpdateMediaAsset;
use Liberu\Genealogy\Media\Events\MediaAssetCreated;
use Liberu\Genealogy\Media\Models\MediaAsset;

uses(RefreshDatabase::class);

it('persists media semantics through tenant-scoped domain actions', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $asset = (new CreateMediaAsset())->execute([
        'kind' => 'photograph', 'name' => 'Family portrait', 'mime_type' => 'image/jpeg',
        'rights_status' => 'owned', 'transcription_status' => 'not_started',
    ]);
    (new UpdateMediaAsset())->execute($asset, ['transcription' => 'Identified relatives', 'transcription_status' => 'completed']);
    $link = (new CreateMediaLink())->execute(['media_asset_id' => $asset->id, 'linkable_type' => 'person', 'linkable_id' => $asset->id, 'role' => 'portrait']);

    expect($asset->refresh()->transcription_status)->toBe('completed')
        ->and($link->media_asset_id)->toBe($asset->id)
        ->and((string) $asset->team_id)->toBe((string) $team->id);
});

it('rejects unsupported media semantics', function (): void {
    expect(fn () => (new CreateMediaAsset())->execute(['name' => 'Bad', 'kind' => 'spreadsheet']))
        ->toThrow(ValidationException::class);
});

it('stores uploaded media with preservation metadata and a checksum', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    Storage::fake('local');
    $file = UploadedFile::fake()->createWithContent('portrait.jpg', 'image data');

    $asset = app(StoreMediaUpload::class)->execute($file, ['kind' => 'photograph']);

    expect($asset->storage_disk)->toBe('local')
        ->and($asset->storage_path)->not->toBeEmpty()
        ->and($asset->byte_size)->toBe(strlen('image data'))
        ->and($asset->checksum)->toBe(hash('sha256', 'image data'))
        ->and($asset->preservation_metadata['original_name'])->toBe('portrait.jpg');
    Storage::disk('local')->assertExists($asset->storage_path);
});

it('dispatches creation events after the media transaction commits', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    Event::listen(MediaAssetCreated::class, static function (): void {
        throw new RuntimeException('The post-commit listener failed.');
    });

    expect(fn () => (new CreateMediaAsset())->execute([
        'kind' => 'document',
        'name' => 'Committed document',
    ]))->toThrow(RuntimeException::class);

    expect(MediaAsset::query()->where('name', 'Committed document')->exists())->toBeTrue();
});
