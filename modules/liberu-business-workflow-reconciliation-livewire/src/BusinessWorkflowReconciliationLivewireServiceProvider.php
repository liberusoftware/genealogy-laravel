<?php

declare(strict_types=1);

namespace Liberu\Platform\BusinessWorkflowReconciliation\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class BusinessWorkflowReconciliationLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('liberu-business-workflow-reconciliation-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Liberu BusinessWorkflowReconciliation Livewire adapter is available.';
    }
}
