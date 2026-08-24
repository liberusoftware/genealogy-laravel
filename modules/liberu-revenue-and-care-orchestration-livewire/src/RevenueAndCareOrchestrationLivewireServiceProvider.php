<?php

declare(strict_types=1);

namespace Liberu\Platform\RevenueAndCareOrchestration\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class RevenueAndCareOrchestrationLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('liberu-revenue-and-care-orchestration-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Liberu RevenueAndCareOrchestration Livewire adapter is available.';
    }
}
