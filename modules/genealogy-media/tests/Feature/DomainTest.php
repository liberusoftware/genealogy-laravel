<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Genealogy\Media\Actions\AnalyzeMediaFaces;
use Liberu\Genealogy\Media\Actions\CreateMediaAsset;
use Liberu\Genealogy\Media\Actions\TranscribeMediaAsset;
use Liberu\Genealogy\Media\Models\MediaAsset;

it('creates and hydrates its aggregate through the domain action', function (): void {
    $database = new Capsule();
    $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $database->setAsGlobal();
    $database->bootEloquent();
    $database->schema()->create('genealogy_media_assets', function ($table): void {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('status');
        $table->json('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $record = (new CreateMediaAsset())->execute([
        'name' => 'Sample record',
        'status' => 'active',
        'metadata' => ['source' => 'archive'],
    ]);

    expect($record)->toBeInstanceOf(MediaAsset::class)
        ->and($record->exists)->toBeTrue()
        ->and($record->name)->toBe('Sample record')
        ->and($record->status)->toBe('active');
});

it('fails closed when facial recognition has no explicitly configured provider', function (): void {
    $result = (new AnalyzeMediaFaces())->execute(new MediaAsset(['name' => 'Archive photograph']));

    expect($result)->toMatchArray([
        'available' => false,
        'success' => false,
        'faces_detected' => 0,
        'tags_created' => 0,
    ])->and($result['error'])->toContain('not configured');
});

it('does not fabricate transcription when no provider is configured', function (): void {
    $asset = new MediaAsset(['name' => 'Handwritten document']);
    $result = (new TranscribeMediaAsset())->execute($asset);

    expect($result)->toMatchArray([
        'available' => false,
        'success' => false,
        'status' => 'not_started',
        'text' => null,
        'confidence' => null,
    ])->and($result['error'])->toContain('not configured');
});
