<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation;

use Illuminate\Support\ServiceProvider;

final class BusinessWorkflowReconciliationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->app->singleton(Capability::class, fn (): Capability => new Capability(
            'liberu-business-workflow-reconciliation',
            'Liberu Business Workflow Reconciliation',
            ['liberu.business-workflow-reconciliation', 'liberu.business-workflow-reconciliation.lifecycle'],
        ));
    }
}
