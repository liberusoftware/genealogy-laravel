<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Liberu\Platform\BusinessWorkflowReconciliation\Actions\CreateReconciliationCase;
use Liberu\Platform\BusinessWorkflowReconciliation\Models\ReconciliationCase;

it('creates and hydrates its aggregate through the domain action', function (): void {
    $database = new Capsule();
    $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
    $database->setAsGlobal();
    $database->bootEloquent();
    $database->schema()->create('liberu_reconciliation_cases', function ($table): void {
        $table->uuid('id')->primary();
        $table->string('tenant_id');
        $table->string('idempotency_key')->nullable();
        $table->string('name');
        $table->string('status');
        $table->json('metadata')->nullable();
        $table->timestamps();
        $table->softDeletes();
    });

    $record = (new CreateReconciliationCase())->execute([
        'tenant_id' => 'team-1',
        'idempotency_key' => 'request-1',
        'name' => 'Sample record',
        'status' => 'active',
        'metadata' => ['source' => 'archive'],
    ]);

    expect($record)->toBeInstanceOf(ReconciliationCase::class)
        ->and($record->exists)->toBeTrue()
        ->and($record->tenant_id)->toBe('team-1')
        ->and($record->name)->toBe('Sample record')
        ->and($record->status)->toBe('active');
});
