<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;

it('creates and hydrates an evidence record through its domain action', function (): void {
    $database = new Capsule();
    $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $database->setAsGlobal();
    $database->bootEloquent();
    $database->schema()->create('evidence_records', function ($table): void {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('status');
        $table->text('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $record = (new CreateEvidenceRecord())->execute([
        'name' => 'Parish register',
        'status' => 'active',
        'metadata' => ['repository' => 'archive.example.test'],
    ]);

    expect($record)->toBeInstanceOf(EvidenceRecord::class)
        ->and($record->exists)->toBeTrue()
        ->and($record->metadata)->toBe(['repository' => 'archive.example.test']);
});
