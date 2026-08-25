<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Genealogy\Dna\Actions\CreateDnaKit;
use Liberu\Genealogy\Dna\Models\DnaKit;
use Liberu\Genealogy\Dna\Services\AnalyzeDnaMatch;
use Liberu\Genealogy\Dna\Services\RelationshipEstimator;
use Liberu\Genealogy\Dna\Services\SegmentMatcher;

it('creates and hydrates its aggregate through the domain action', function (): void {
    $database = new Capsule();
    $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $database->setAsGlobal();
    $database->bootEloquent();
    $database->schema()->create('genealogy_dna_kits', function ($table): void {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('status');
        $table->json('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $record = (new CreateDnaKit())->execute([
        'name' => 'Sample record',
        'status' => 'active',
        'metadata' => ['source' => 'archive'],
    ]);

    expect($record)->toBeInstanceOf(DnaKit::class)
        ->and($record->exists)->toBeTrue()
        ->and($record->name)->toBe('Sample record')
        ->and($record->status)->toBe('active');
});

it('matches autosomal segments and estimates a relationship without a provider SDK', function (): void {
    $kitA = ['1' => []];
    $kitB = ['1' => []];
    for ($index = 0; $index < SegmentMatcher::MIN_SNPS; $index++) {
        $position = 1_000_000 + ($index * 50_000);
        $kitA['1'][$position] = 'AG';
        $kitB['1'][$position] = 'AA';
    }

    $result = (new AnalyzeDnaMatch(new SegmentMatcher(), new RelationshipEstimator()))->analyze($kitA, $kitB);

    expect($result['shared_segments_count'])->toBe(1)
        ->and($result['shared_segments'][0]['snps'])->toBe(SegmentMatcher::MIN_SNPS)
        ->and($result['total_shared_cm'])->toBeGreaterThanOrEqual(7.0)
        ->and($result['predicted_relationship'])->toBe('Distant Cousin')
        ->and(RelationshipEstimator::labels())->toContain('Parent/Child');
});
