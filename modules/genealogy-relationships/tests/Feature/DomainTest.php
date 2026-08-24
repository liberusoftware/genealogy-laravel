<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Models\Relationship;

it('creates and hydrates its aggregate through the domain action', function (): void {
    $database = new Capsule();
    $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $database->setAsGlobal();
    $database->bootEloquent();
    $database->schema()->create('genealogy_relationships', function ($table): void {
        $table->uuid('id')->primary();
        $table->uuid('person_id');
        $table->uuid('related_person_id');
        $table->string('type');
        $table->unsignedSmallInteger('confidence')->default(100);
        $table->json('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $record = (new CreateRelationship())->execute([
        'person_id' => 'person-a',
        'related_person_id' => 'person-b',
        'type' => 'parent',
        'confidence' => 90,
        'metadata' => ['source' => 'document'],
    ]);

    expect($record)->toBeInstanceOf(Relationship::class)
        ->and($record->exists)->toBeTrue()
        ->and($record->type)->toBe('parent')
        ->and($record->confidence)->toBe(90);
});

it('rejects self relationships and out-of-range confidence', function (): void {
    expect(fn () => (new CreateRelationship())->execute([
        'person_id' => 'person-a',
        'related_person_id' => 'person-a',
        'type' => 'parent',
    ]))->toThrow(InvalidArgumentException::class);

    expect(fn () => (new CreateRelationship())->execute([
        'person_id' => 'person-a',
        'related_person_id' => 'person-b',
        'type' => 'parent',
        'confidence' => 101,
    ]))->toThrow(InvalidArgumentException::class);
});
