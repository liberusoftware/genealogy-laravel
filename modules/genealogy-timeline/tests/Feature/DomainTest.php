<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Genealogy\Timeline\Actions\CreateTimelineEvent;
use Liberu\Genealogy\Timeline\Models\TimelineEvent;

it('creates and hydrates its aggregate through the domain action', function (): void {
    $database = new Capsule();
    $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $database->setAsGlobal();
    $database->bootEloquent();
    $database->schema()->create('timeline_events', function ($table): void {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('status');
        $table->json('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $record = (new CreateTimelineEvent())->execute([
        'name' => 'Sample record',
        'status' => 'active',
        'metadata' => ['source' => 'archive'],
    ]);

    expect($record)->toBeInstanceOf(TimelineEvent::class)
        ->and($record->exists)->toBeTrue()
        ->and($record->name)->toBe('Sample record')
        ->and($record->status)->toBe('active');
});
