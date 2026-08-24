<?php

declare(strict_types=1);

namespace Liberu\Platform\ExecutiveInsights\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class ExecutiveInsightsLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'liberu-executive-insights-livewire');
        Livewire::component('liberu-executive-insights-list', InsightSnapshotList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Liberu ExecutiveInsights Livewire adapter is available.';
    }
}
