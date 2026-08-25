<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Models\Person;

it('creates and hydrates its aggregate through the domain action', function (): void {
    $database = new Capsule();
    $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $database->setAsGlobal();
    $database->bootEloquent();
    $database->schema()->create('genealogy_people', function ($table): void {
        $table->uuid('id')->primary();
        $table->string('given_name');
        $table->string('family_name')->nullable();
        $table->string('display_name')->nullable();
        $table->date('birth_date')->nullable();
        $table->date('death_date')->nullable();
        $table->string('birth_place')->nullable();
        $table->string('death_place')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $record = (new CreatePerson())->execute([
        'given_name' => 'Ada',
        'family_name' => 'Lovelace',
        'birth_date' => '1815-12-10',
        'metadata' => ['source' => 'archive'],
    ]);

    expect($record)->toBeInstanceOf(Person::class)
        ->and($record->exists)->toBeTrue()
        ->and($record->display_name)->toBe('Ada Lovelace')
        ->and($record->isLiving())->toBeTrue();
});
