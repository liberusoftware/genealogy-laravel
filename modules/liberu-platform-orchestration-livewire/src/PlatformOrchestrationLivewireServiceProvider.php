<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class PlatformOrchestrationLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'liberu-platform-orchestration-livewire');
        Livewire::component('liberu-platform-orchestration-list', PlatformWorkflowList::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Liberu PlatformOrchestration Livewire adapter is available.';
    }
}
