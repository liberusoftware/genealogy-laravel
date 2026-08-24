<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Platform\BusinessWorkflowReconciliation\Actions\TransitionReconciliationCase;
use Liberu\Platform\BusinessWorkflowReconciliation\Enums\LifecycleStatus as ReconciliationStatus;
use Liberu\Platform\BusinessWorkflowReconciliation\Events\ReconciliationCaseTransitioned;
use Liberu\Platform\BusinessWorkflowReconciliation\Exceptions\InvalidLifecycleTransition as ReconciliationTransitionError;
use Liberu\Platform\BusinessWorkflowReconciliation\Models\ReconciliationCase;
use Liberu\Platform\ExecutiveInsights\Actions\TransitionInsightSnapshot;
use Liberu\Platform\ExecutiveInsights\Enums\LifecycleStatus as InsightStatus;
use Liberu\Platform\ExecutiveInsights\Events\InsightSnapshotTransitioned;
use Liberu\Platform\ExecutiveInsights\Exceptions\InvalidLifecycleTransition as InsightTransitionError;
use Liberu\Platform\ExecutiveInsights\Models\InsightSnapshot;
use Liberu\Platform\PlatformOrchestration\Actions\TransitionPlatformWorkflow;
use Liberu\Platform\PlatformOrchestration\Enums\LifecycleStatus as WorkflowStatus;
use Liberu\Platform\PlatformOrchestration\Events\PlatformWorkflowTransitioned;
use Liberu\Platform\PlatformOrchestration\Exceptions\InvalidLifecycleTransition as WorkflowTransitionError;
use Liberu\Platform\PlatformOrchestration\Models\PlatformWorkflow;
use Liberu\Platform\RevenueAndCareOrchestration\Actions\TransitionCarePlan;
use Liberu\Platform\RevenueAndCareOrchestration\Enums\LifecycleStatus as CareStatus;
use Liberu\Platform\RevenueAndCareOrchestration\Events\CarePlanTransitioned;
use Liberu\Platform\RevenueAndCareOrchestration\Exceptions\InvalidLifecycleTransition as CareTransitionError;
use Liberu\Platform\RevenueAndCareOrchestration\Models\CarePlan;

uses(RefreshDatabase::class);

it('transitions each Liberu aggregate and dispatches its committed event', function (): void {
    Event::fake();

    $records = [
        [new ReconciliationCase(), new TransitionReconciliationCase(), ReconciliationStatus::Active, ReconciliationCaseTransitioned::class],
        [new InsightSnapshot(), new TransitionInsightSnapshot(), InsightStatus::Active, InsightSnapshotTransitioned::class],
        [new PlatformWorkflow(), new TransitionPlatformWorkflow(), WorkflowStatus::Active, PlatformWorkflowTransitioned::class],
        [new CarePlan(), new TransitionCarePlan(), CareStatus::Active, CarePlanTransitioned::class],
    ];

    foreach ($records as [$record, $action, $status, $event]) {
        $record->forceFill(['tenant_id' => 'team-1', 'name' => 'Test', 'status' => 'draft'])->save();
        $action->execute($record, $status);
        expect($record->refresh()->status)->toBe('active');
        Event::assertDispatched($event);
    }
});

it('rejects invalid terminal lifecycle transitions with typed exceptions', function (): void {
    $records = [
        [new ReconciliationCase(), new TransitionReconciliationCase(), ReconciliationStatus::Completed, ReconciliationTransitionError::class],
        [new InsightSnapshot(), new TransitionInsightSnapshot(), InsightStatus::Completed, InsightTransitionError::class],
        [new PlatformWorkflow(), new TransitionPlatformWorkflow(), WorkflowStatus::Completed, WorkflowTransitionError::class],
        [new CarePlan(), new TransitionCarePlan(), CareStatus::Completed, CareTransitionError::class],
    ];

    foreach ($records as [$record, $action, $status, $exception]) {
        $record->forceFill(['tenant_id' => 'team-1', 'name' => 'Test', 'status' => 'completed'])->save();
        expect(fn () => $action->execute($record, $status))->toThrow($exception);
    }
});
