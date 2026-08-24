<?php

declare(strict_types=1);

namespace Liberu\Platform\PlatformOrchestration\Livewire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class PlatformOrchestrationLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('liberu-platform-orchestration-livewire-status', Status::class);
    }
}

final class Status
{
    public function render(): string
    {
        return 'Liberu PlatformOrchestration Livewire adapter is available.';
    }
}
