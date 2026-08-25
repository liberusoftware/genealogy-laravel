<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Genealogy\Discovery\Actions\CreateDiscoveryMatch;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Liberu\Genealogy\Discovery\Services\ExternalRecordMatcher;

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

it('scores external candidates without a provider SDK and preserves unavailable state', function (): void {
    $matcher = new ExternalRecordMatcher();
    $scored = $matcher->scoreCandidates(
        ['first_name' => 'John', 'last_name' => 'Doe', 'birth_year' => 1879, 'birth_place' => 'County X'],
        [
            ['id' => 'strong', 'first_name' => 'John', 'last_name' => 'Doe', 'birth_year' => 1879, 'birth_place' => 'County X'],
            ['id' => 'weak', 'first_name' => 'Jane', 'last_name' => 'Smith', 'birth_year' => 1900, 'birth_place' => 'County Y'],
        ],
    );

    expect($scored[0]['candidate']['id'])->toBe('strong')
        ->and($scored[0]['score'])->toBeGreaterThan($scored[1]['score'])
        ->and($matcher->execute([])['available'])->toBeFalse();
});
