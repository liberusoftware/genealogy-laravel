<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;

it('creates and hydrates its aggregate through the domain action', function (): void {
    $database = new Capsule();
    $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $database->setAsGlobal();
    $database->bootEloquent();
    $database->schema()->create('genealogy_discovery_matches', function ($table): void {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('status');
        $table->json('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $record = (new CreateDiscoveryMatch())->execute([
        'name' => 'Sample record',
        'status' => 'active',
        'metadata' => ['source' => 'archive'],
    ]);

    expect($record)->toBeInstanceOf(DiscoveryMatch::class)
        ->and($record->exists)->toBeTrue()
        ->and($record->name)->toBe('Sample record')
        ->and($record->status)->toBe('active');
});
